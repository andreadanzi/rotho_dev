<?php
// danzi.tn@20140417 telefono (Account_phone) per infowindows aziende
// danzi.tn@20141212 nova classificazione cf_762 sostituito con vtiger_account.account_client_type
// danzi.tn@20150213 aggiornamento slider MAP conforme all'elenco Aziende
// danzi.tn@20150331 modifica allo slider, per step da 500 euro
// danzi.tn@20150408 selezione multipla su categoria e codice prodotto
// danzi.tn@20150522 gestione di crmentity_sales.deleted=0 all'interno del SUM(CASE
// danzi.tn@20150618 aggiunto addslashes
// danzi.tn@20151214 parametro fromAction per controllare se saltare la cache o meno
// danzi.tn@20160104 passaggio in produzione albero utenti
require_once('include/utils/CommonUtils.php');
function getSkippedAccounts($ids)
{
	global $adb;
	$ret = array();
	$sQuery = "SELECT
		accountaddressid as id,
		accountname as name,
		account_no as accnum,
		bill_state as state,
		bill_city as city,
		bill_code as code,
		bill_street as street,
		bill_country as country
		FROM vtiger_accountbillads
		LEFT JOIN vtiger_map on accountaddressid = mapid
		LEFT JOIN vtiger_crmentity on accountaddressid = crmid
		LEFT JOIN vtiger_account on accountaddressid = accountid
		WHERE
		mapid IS NULL AND
		deleted=0  AND
		accountaddressid in ($ids)
		ORDER BY accountaddressid DESC";
	// echo "<!--skippedquery  ".$sQuery."-->";
	$vowels = array("\n", "\t", "\r", "\0", "\x0B");
	$result = $adb->query($sQuery);
	while($row=$adb->fetchByAssoc($result))
	{
		$state = $row['state']?" (".strtoupper($row['state']).")":"";
		$ret[$row['id']] = array(
			"name" => addslashes($row['name']),
			"accid" => addslashes($row['id']),
			"accnum" => addslashes($row['accnum']),
			"city" => addslashes(ucwords(strtolower($row['city']))),
			"street" => addslashes(ucwords(strtolower(str_replace($vowels, "",$row['street'])))),
			"state" => addslashes(ucwords(strtolower($row['state']))),
			"code" => addslashes(ucwords(strtolower($row['code']))),
			"extra" => addslashes(ucwords(strtolower($row['street']."<br/>".$row['code']." ".$row['city'])).$state."<br/>".$row['account_phone']),
			"country" => addslashes($row['country'])
		);
	}
	return $ret;
}

function getResult($gc,$query,$fromAction="")
{
	global $adb;
	$ret = array();
	$time1 = time();
	$result = $adb->query($query);
	$time2 = time();
	$time1 = $time2 - $time1;
	$gc->setDelay(500000);
	$iFound=0;
	$vowels = array("\n", "\t", "\r", "\0", "\x0B");
	$not_found=array();
    $bSearchInCache = true;
    if($fromAction == "DetailView" ) $bSearchInCache = false;

	while($row=$adb->fetchByAssoc($result))
	{
		//in vtiger e' scambiato country con state
		$stripped_street =  clean_text($row['street']);
		$coord = $gc->getGeoCode($row['id'],strtolower($row['state']),strtolower($row['city']),$row['code'],$stripped_street,strtolower($row['country']),$bSearchInCache);
		$approx = "";
		if($coord) //add item to final result
		{
			$iFound++;
			$state = $row['state']?" (".strtoupper($row['state']).")":"";
			if($coord->approx)
				$approx = "<br/><span style='color: grey; font-size: smaller'>La posizione sulla mappa è approssimativa</span></br>";

			$ret[$row['id']] = array(
						"name" => addslashes($row['name']),
						"type" => addslashes($row['type']), // Andrea Danzi aggiunto type - 24.03.2012
						"account_line" => addslashes($row['account_line']), // danzi.tn@20150414 aggiunto account_line - 14.04.2015
						"account_main_activity" => addslashes($row['account_main_activity']), // danzi.tn@20150414 aggiunto account_main_activity - 14.04.2015
						"account_sec_activity" => addslashes($row['account_sec_activity']), // danzi.tn@20150414 aggiunto account_sec_activity - 14.04.2015
                        "user_name" => addslashes($row['user_name']), // danzi.tn@20150618 aggiunto user_name
						"last_name" => addslashes($row['last_name']), // danzi.tn@20150618 aggiunto last_name
						"first_name" => addslashes($row['first_name']), // danzi.tn@20150618 aggiunto first_name
						"map_value" => addslashes($row['map_value']), // Andrea Danzi aggiunto map_value - 26.03.2012
						"city" => addslashes(ucwords(strtolower($row['city']))),
						"extra" => addslashes(ucwords(strtolower($row['street']."<br/>".$row['code']." ".$row['city'])).$state.$approx."<br/>".$row['account_phone']),
						"map_aurea" => addslashes($row['map_aurea']), // Andrea Danzi aggiunto map_value - 26.03.2012
						"lat" => $coord->latitude,
						"lng" => $coord->longitude,
						"street" => addslashes(ucwords(strtolower(str_replace($vowels, "",$row['street'])))),
						"state" => $row['state'],
						"account_phone" => $row['account_phone'],
						"code" => $row['code'],
						"country" => $row['country']
					);
		}
		else
		{
			$not_found[$row['id']] = array( "name"=> addslashes($row['name']),
						"type"=> addslashes($row['type']), // Andrea Danzi aggiunto type - 24.03.2012
						"account_line" => addslashes($row['account_line']), // danzi.tn@20150414 aggiunto account_line - 14.04.2015
						"account_main_activity" => addslashes($row['account_main_activity']), // danzi.tn@20150414 aggiunto account_main_activity - 14.04.2015
						"account_sec_activity" => addslashes($row['account_sec_activity']), // danzi.tn@20150414 aggiunto account_sec_activity - 14.04.2015
                        "user_name" => addslashes($row['user_name']), // danzi.tn@20150618 aggiunto user_name
						"last_name" => addslashes($row['last_name']), // danzi.tn@20150618 aggiunto last_name
						"first_name" => addslashes($row['first_name']), // danzi.tn@20150618 aggiunto first_name
						"map_value" => addslashes($row['map_value']), // Andrea Danzi aggiunto map_value - 26.03.2012
						"city" => addslashes(ucwords(strtolower($row['city']))),
						"extra" => addslashes(ucwords(strtolower($row['street']." - ".$row['code']." ".$row['city'])).$state.$approx),
						"map_aurea" => addslashes($row['map_aurea']), // Andrea Danzi aggiunto map_value - 26.03.2012
						"lat" => 0,
						"lng" => 0 ,
						"street" => addslashes(ucwords(strtolower(str_replace($vowels, "",$row['street'])))),
						"state" => $row['state'],
						"code" => $row['code'],
						"account_phone" => $row['account_phone'],
						"country" => $row['country']
                                        );
		if($gc->needsDelay()) usleep($gc->getDelay());
		}
	}
	$time1 = time();
	$time2 = $time1 - $time2;
	// echo '<p>Step 3 = '. $time2. ' </p>';
	$retVal = array("results"=>$ret, "query"=>$query, "count_found"=>$iFound, "not_found"=>$not_found);
	return $retVal;
}

// danzi.tn@20150204 gestione parametri passati da ListViewByProduct
// danzi.tn@20150408 selezione multipla su categoria e codice prodotto
function getResults($type,$ids,$extra_ids=null,$prod_id=null,$mindate=null,$maxdate=null,$amountrange, $user_uid, $fromAction="")
{
	$extra_from_clause = "";
	if(isset($mindate) && $mindate!="" && isset($maxdate) && $maxdate!="" )
	{
		$db = PearDatabase::getInstance();
		$extra_from_clause .= " AND data_ordine_ven BETWEEN CONVERT(datetime,".$db->quote(getDBInsertDateValue($mindate)).",120) AND CONVERT(datetime,".$db->quote(getDBInsertDateValue($maxdate)).",120) ";
	}
	$gc = new GeoCoder();
	switch($type)
	{
		case "Potentials":
			$query = "select vtiger_account.accountid as id,accountname as name, bill_code as code, bill_city as city,bill_country as country,bill_state as state,bill_street as street, vtiger_account.account_client_type as type, sum( amount ) AS map_value ,CASE external_code WHEN NULL THEN 'ND' WHEN '' THEN 'ND' ELSE 'OK' END AS map_aurea, vtiger_account.phone as account_phone from vtiger_potential join vtiger_accountbillads on vtiger_potential.related_to=vtiger_accountbillads.accountaddressid join vtiger_account on vtiger_account.accountid=vtiger_potential.related_to join vtiger_accountscf on vtiger_accountscf.accountid=vtiger_potential.related_to WHERE bill_code IS NOT NULL AND bill_city IS NOT NULL "; // Andrea Danzi aggiunto type - 24.03.2012
			if($ids)
				$query .= "AND potentialid in ($ids) ";
			$query .= "GROUP BY vtiger_account.accountid, accountname, bill_code, bill_city, bill_country, bill_state, bill_street, potentialtype, external_code, vtiger_account.phone ORDER BY map_value ASC"; //
		break;
		case "HelpDesk":
			$query = "select vtiger_account.accountid as id,accountname as name, bill_code as code, bill_city as city,bill_country as country,bill_state as state,bill_street as street, vtiger_account.account_client_type as type,  CASE priority
WHEN \"Urgent\" THEN 10000000
WHEN \"High\" THEN 100000
WHEN \"Normal\" THEN 10000
WHEN \"Low\" THEN 1000 END as map_value, CASE external_code WHEN NULL THEN 'ND' WHEN '' THEN 'ND' ELSE 'OK' END AS map_aurea, vtiger_account.phone as account_phone from vtiger_troubletickets join vtiger_account on parent_id=vtiger_account.accountid  join vtiger_accountbillads on vtiger_account.accountid=accountaddressid join vtiger_accountscf on parent_id=vtiger_accountscf.accountid WHERE ticketid in ($ids) AND bill_code IS NOT NULL AND bill_city IS NOT NULL
			UNION
			select contactid as id,concat(firstname,' ',lastname) as name, mailingzip as code, mailingcity as city,mailingcountry as country,mailingstate as state,mailingstreet as street, vtiger_account.account_client_type as type, CASE priority
WHEN \"Urgent\" THEN 10000000
WHEN \"High\" THEN 100000
WHEN \"Normal\" THEN 10000
WHEN \"Low\" THEN 1000 END as map_value , 'ND' as map_aurea, vtiger_contactdetails.phone as account_phone from vtiger_troubletickets join vtiger_contactdetails on parent_id=contactid join vtiger_contactaddress on contactid=contactaddressid join vtiger_accountscf on vtiger_contactdetails.accountid =vtiger_accountscf.accountid WHERE  mailingzip IS NOT NULL AND mailingcity IS NOT NULL "; // Andrea Danzi aggiunto type - 24.03.2012
			if($ids)
                        	$query .= " AND ticketid in ($ids) ";
		break;
		case "Accounts":
			$groupby = false;
			/* questa è quella vecchia $query = "select accountname as name, accountaddressid as id, bill_city as city, bill_code as code, bill_country as country, bill_state as state, bill_street as street,  vtiger_account.account_client_type as type, annualrevenue as map_value, CASE external_code WHEN NULL THEN 'ND' WHEN '' THEN 'ND' ELSE 'OK' END AS map_aurea, vtiger_account.phone as account_phone from vtiger_accountbillads INNER JOIN vtiger_crmentity ON accountaddressid=vtiger_crmentity.crmid  join vtiger_account on accountaddressid= vtiger_account.accountid join vtiger_accountscf on accountaddressid=vtiger_accountscf.accountid"; */
			// Andrea Danzi aggiunto vtiger_account.account_client_type (dovrà essere modificato per RB in  vtiger_account.account_client_type) as type - 24.03.2012
			$query = "SELECT vtiger_account.accountid as id,
							accountname as name,
							bill_code as code,
							bill_city as city,
							bill_country as country,
							bill_state as state,
							bill_street as street,
                            u.user_name, u.last_name, u.first_name ,
							vtiger_account.account_client_type as type ,
                            vtiger_account.account_line,
                            vtiger_account.account_main_activity,
                            vtiger_account.account_sec_activity,
							sum(CASE WHEN vtiger_salesorder.salesorderid IS NULL THEN 0 WHEN vtiger_crmentity_sales.deleted = 1 THEN 0 ELSE vtiger_inventoryproductrel.listprice*vtiger_inventoryproductrel.quantity END) as map_value,
							CASE external_code WHEN NULL THEN 'ND' WHEN '' THEN 'ND' ELSE 'OK' END AS map_aurea,
							vtiger_account.phone as account_phone
			FROM vtiger_account
			JOIN vtiger_accountbillads on vtiger_account.accountid=vtiger_accountbillads.accountaddressid
			JOIN vtiger_accountscf on vtiger_accountscf.accountid=vtiger_account.accountid
            JOIN vtiger_crmentity accent on accent.crmid = vtiger_account.accountid and accent.deleted = 0
            JOIN vtiger_users u on u.id = accent.smownerid
			LEFT JOIN vtiger_salesorder on vtiger_salesorder.accountid  = vtiger_account.accountid ".$extra_from_clause."
			LEFT JOIN vtiger_crmentity as vtiger_crmentity_sales on vtiger_crmentity_sales.crmid  = vtiger_salesorder.salesorderid
			LEFT JOIN vtiger_inventoryproductrel on vtiger_salesorder.salesorderid = vtiger_inventoryproductrel.id
			LEFT JOIN vtiger_products on vtiger_products.productid = vtiger_inventoryproductrel.productid";
			if($ids)
			{
			 	$query .= " JOIN dnz_temp_account ON dnz_temp_account.accountid = vtiger_account.accountid AND useruid='".$user_uid."'";
			}
			$query .= " WHERE bill_code IS NOT NULL AND bill_city IS NOT NULL ";
            // danzi.tn@20150408 selezione multipla su categoria e codice prodotto
			if($extra_ids)
			{
                // $query .= " AND vtiger_products.product_cat LIKE '$extra_ids%' ";
                $keywords = preg_split("/[\s,]+/", $extra_ids);
                $whereclause = array();
                foreach($keywords as $kw) {
                    $whereclause[] = " vtiger_products.product_cat LIKE '$kw%'";
                }
                $query .= " AND (";
                $query .= implode(" OR ",$whereclause);
                $query .= ") ";
			}
			if($prod_id)
			{
				// $query .= " AND vtiger_products.base_no LIKE '$prod_id%' ";
                $keywords = preg_split("/[\s,]+/", $prod_id);
                $whereclause = array();
                foreach($keywords as $kw) {
                    $whereclause[] = " vtiger_products.base_no LIKE '$kw%'";
                }
                $query .= " AND (";
                $query .= implode(" OR ",$whereclause);
                $query .= ") ";
			}
            // danzi.tn@20150408e
			// danzi.tn@20150204 gestione parametri passati da ListViewByProduct
			$query .= " GROUP BY
                            vtiger_account.accountid,
                            accountname,
                            bill_code,
                            bill_city,
                            bill_country,
                            bill_state ,
                            bill_street,
                            u.user_name, u.last_name, u.first_name ,
                            vtiger_account.account_client_type,
                            vtiger_account.account_line,
                            vtiger_account.account_main_activity,
                            vtiger_account.account_sec_activity,
                            vtiger_account.external_code,
                            vtiger_account.phone"; //
			if(isset($amountrange) && $amountrange!="" )
			{
				$amount_where_clause = "";
				$amountrange_splitted = explode("-",$amountrange);
                $maxVal = $amountrange_splitted[1];
                if( $amountrange_splitted[1]=="250000" ) {
                     $maxVal = "999000000";
                }
				if( count($amountrange_splitted) > 1 ){
					$amount_where_clause = " sum( CASE WHEN vtiger_salesorder.salesorderid IS NULL THEN 0 WHEN vtiger_crmentity_sales.deleted = 1 THEN 0 ELSE vtiger_inventoryproductrel.listprice*vtiger_inventoryproductrel.quantity END) BETWEEN ". $amountrange_splitted[0] . " AND ". $maxVal. " ";
				} else {
					$amount_where_clause = " sum( CASE WHEN vtiger_salesorder.salesorderid IS NULL THEN 0 WHEN vtiger_crmentity_sales.deleted = 1 THEN 0 ELSE vtiger_inventoryproductrel.listprice*vtiger_inventoryproductrel.quantity END) BETWEEN  0 AND ". $maxVal. " ";
				}
				$query .= " HAVING " . $amount_where_clause;
			}
			$query .= " ORDER BY map_value ASC";
		break;
		case "SalesOrder":
			$query = "select vtiger_account.accountid as id,accountname as name, bill_code as code, bill_city as city,bill_country as country,bill_state as state,bill_street as street, vtiger_account.account_client_type as type ,sum(total) as map_value, CASE external_code WHEN NULL THEN 'ND' WHEN '' THEN 'ND' ELSE 'OK' END AS map_aurea , vtiger_account.phone as account_phone from vtiger_salesorder join vtiger_accountbillads on vtiger_salesorder.accountid=vtiger_accountbillads.accountaddressid join vtiger_account on vtiger_account.accountid=vtiger_salesorder.accountid join vtiger_accountscf on vtiger_accountscf.accountid=vtiger_salesorder.accountid WHERE bill_code IS NOT NULL AND bill_city IS NOT NULL "; // Andrea Danzi aggiunto map_value - 26.03.2012 sum(total) oppure anche sum(subtotal)
			if($ids)
				$query .= "AND salesorderid in ($ids) ";
			$query .= "GROUP BY vtiger_account.accountid, accountname, bill_code,bill_city, bill_country,  bill_state , bill_street, vtiger_account.external_code, vtiger_account.phone"; //
			$query .= " ORDER BY map_value ASC";
			//$query .= " ORDER BY map_value ASC"; Andrea Danzi aggiunto Group By - 26.03.2012
		break;
		case "ProductCategory":
			$query = "select vtiger_account.accountid as id,accountname as name, bill_code as code, bill_city as city,bill_country as country,bill_state as state,bill_street as street, vtiger_products.product_cat as type ,
sum(CASE WHEN vtiger_salesorder.salesorderid IS NULL THEN 0 WHEN vtiger_crmentity_sales.deleted = 1 THEN 0 ELSE vtiger_inventoryproductrel.listprice*vtiger_inventoryproductrel.quantity END) as map_value , CASE external_code WHEN NULL THEN 'ND' WHEN '' THEN 'ND' ELSE 'OK' END AS map_aurea, vtiger_account.phone as account_phone
from vtiger_account
JOIN vtiger_crmentity accentity ON accentity.crmid  = vtiger_account.accountid AND accentity.deleted = 0
join vtiger_accountbillads on vtiger_account.accountid=vtiger_accountbillads.accountaddressid
join vtiger_accountscf on vtiger_accountscf.accountid=vtiger_account.accountid
left join vtiger_salesorder on vtiger_salesorder.accountid  = vtiger_account.accountid ".$extra_from_clause."
left join vtiger_crmentity as vtiger_crmentity_sales on vtiger_crmentity_sales.crmid  = vtiger_salesorder.salesorderid and vtiger_crmentity_sales.deleted = 0
left join vtiger_inventoryproductrel on vtiger_salesorder.salesorderid = vtiger_inventoryproductrel.id
left join vtiger_products on vtiger_products.productid = vtiger_inventoryproductrel.productid
WHERE AND bill_code IS NOT NULL AND bill_city IS NOT NULL "; // Andrea Danzi aggiunto - 11.04.2012
            // danzi.tn@20150408 selezione multipla su categoria
			if($ids) {
				// $query .= "AND vtiger_products.product_cat LIKE '$ids%'";
                $keywords = preg_split("/[\s,]+/", $ids);
                $whereclause = array();
                foreach($keywords as $kw) {
                    $whereclause[] = " vtiger_products.product_cat LIKE '$kw%'";
                }
                $query .= " AND (";
                $query .= implode(" OR ",$whereclause);
                $query .= ") ";
            }
            // danzi.tn@20150408e
			$query .= "GROUP BY vtiger_account.accountid, accountname, bill_code,bill_city, bill_country,  bill_state , bill_street, vtiger_account.external_code, vtiger_account.phone"; //
			// danzi.tn@20150204 gestione parametri passati da ListViewByProduct
			if(isset($amountrange) && $amountrange!="" )
			{
				$amount_where_clause = "";
				$amountrange_splitted = explode("-",$amountrange);
                $maxVal = $amountrange_splitted[1];
                if( $amountrange_splitted[1]=="250000" ) {
                    $maxVal = "990000000";
                }
				if( count($amountrange_splitted) > 1 ){
					$amount_where_clause = " sum( CASE WHEN vtiger_salesorder.salesorderid IS NULL THEN 0 WHEN vtiger_crmentity_sales.deleted = 1 THEN 0 ELSE vtiger_inventoryproductrel.listprice*vtiger_inventoryproductrel.quantity END) BETWEEN ". $amountrange_splitted[0] . " AND ". $maxVal. " ";
				} else {
					$amount_where_clause = " sum( CASE WHEN vtiger_salesorder.salesorderid IS NULL THEN 0 WHEN vtiger_crmentity_sales.deleted = 1 THEN 0 ELSE vtiger_inventoryproductrel.listprice*vtiger_inventoryproductrel.quantity END) BETWEEN  0 AND ". $maxVal. " ";
				}
				$query .= " HAVING " . $amount_where_clause;
			}
			$query .= " ORDER BY map_value ASC";
		break;
		case "Leads":
			 $query = "select concat(firstname,' ',lastname,' - ', company) as name, leadaddressid as id, city, code, country, state, lane as street, leadsource as type, annualrevenue as map_value, 'ND' as map_aurea from vtiger_leaddetails join vtiger_leadaddress on leadaddressid=leadid "; // Andrea Danzi aggiunto type - 24.03.2012
			if($ids)
                                $query .= " WHERE leadaddressid in ($ids)";
			$query .= " ORDER BY map_value ASC";
		break;

		default:
			return array();
	}
	return getResult($gc,$query,$fromAction);
}

function getValueSelectorOptions($type,$option_selected=null)
{
	$retValue = "<option " . ($option_selected==null? "selected": "") . " value=\"default\">Default</option>";
	switch($type)
	{
		case "Potentials":
			$retValue = "<option " . ($option_selected==null? "selected": "") . " value=\"ammontare\">Ammontare Opportunità</option>";
		break;
		case "Accounts":
			$retValue = "<option " . ($option_selected==null? "selected": "") . " value=\"fatturato\">Fatturato Azienda</option>";
			$retValue .="<option " . ($option_selected=="productcat"? "selected": "") . " value=\"productcat\">Ordini per Categorie Prodotto</option>";
		break;
		case "HelpDesk":
			$retValue = "<option " . ($option_selected==null? "selected": "") . " value=\"priority\">Priorità Ticket</option>";
		break;
		case "SalesOrder":
			$retValue = "<option " . ($option_selected==null? "selected": "") . " value=\"defaultSO\">Default</option>";
		break;
		case "Leads":
			$retValue = "<option " . ($option_selected==null? "selected": "") . " value=\"defaultLEA\">Default</option>";
		break;
	}
	return $retValue;
}

// 18052012 Andrea
/* danzi.tn@20151218 risulta duplicata con quanto presente su modules/SDK/src/modules/Accounts/treeUtils.php
// danzi.tn@20140411 update product category
function getProductCategoryTree()
{
	global $adb;
	$tree_string="";
	$query = "SELECT DISTINCT class3 as categorycode, class1 as parentlevel1, class2 as parentlevel2, class_desc3 as categorydescr, class_desc1, class_desc2
	FROM erp_temp_crm_classificazioni
	JOIN vtiger_products ON vtiger_products.product_cat = erp_temp_crm_classificazioni.class3
	JOIN vtiger_crmentity ON vtiger_crmentity.crmid  = vtiger_products.productid AND vtiger_crmentity.deleted = 0
	ORDER BY parentlevel1 ASC, parentlevel2 ASC, categorycode ASC ";
	$result = $adb->query($query);
	$i_count = 0;
	$i_count1 = 0;
	$i_count2 = 0;
	$i_count3 = 0;
	$s_level1 = "x96x";
	$s_level2 = "x96x";
	$s_level3 = "x96x";
	while($row=$adb->fetchByAssoc($result))
	{
		if($i_count1==0) $tree_string.="<ul>\n";
		if($row['parentlevel1']!=$s_level1)
		{
			if($i_count1>0) $tree_string.="\t\t\t</ul>\n\t\t</li>\n\t</ul>\n\t</li>\n";
			$i_count2=0;
			$s_level1=$row['parentlevel1'];
			$s_desclevel1=$row['class_desc1'];
			$tree_string.="\t<li title=\"".$s_desclevel1."\"  id=\"".$s_level1."\"><a title=\"".$s_desclevel1."\" href=\"#\">".$s_level1." (".$s_desclevel1.")</a>\n";
			$i_count1++;
		}
		if($i_count2==0) $tree_string.="\t<ul>\n";
		if($row['parentlevel2']!=$s_level2)
		{
			if($i_count2>0) $tree_string.="\t\t\t</ul>\n\t\t</li>\n";
			$i_count3=0;
			$s_level2=$row['parentlevel2'];
			$s_desclevel2=$row['class_desc2'];
			$tree_string.="\t\t<li title=\"".$s_desclevel2."\" id=\"".$s_level2."\"><a title=\"".$s_desclevel2."\"  href=\"#\">".$s_level2." (".$s_desclevel2.")</a>\n";
			$i_count2++;
		}
		if($i_count3==0) $tree_string.="\t\t\t<ul>\n";
		$tree_string.="\t\t\t\t<li title=\"".$row['categorydescr']."\" id=\"".$row['categorycode']."\"><a title=\"".$row['categorydescr']."\" href=\"#\">".$row['categorycode']." (".$row['categorydescr'].")</a></li>\n";
		$i_count3++;
	}
	 $tree_string.="\t\t\t</ul>\n\t\t</li>\n\t</ul>\n\t</li>\n";
	 $tree_string.="</ul>\n	";
	return $tree_string;
}
*/
/*
getTranslatedString($str,$module='')
 vtiger_account.account_line,
                            vtiger_account.account_main_activity,
                            vtiger_account.account_sec_activity,

                            */
function printResultLayer($results,$skippedAccs=null)
{
	echo "var resultLayer = {\n";
	foreach($results as $key=>$result)
	{
        $res_name = $result['name'];
        $res_name = str_replace(array("\r", "\n"), '', $res_name); // danzi.tn@201509004 rimuovere newline
        $res_name = addslashes(trim($res_name));
        $street = addslashes($result['street']);
        $city = addslashes($result['city']);
		echo "\t'{$key}': \n";
		echo "\t{\n";
		// echo "\t'name': '{$result['name']}', \n";
		echo "\t'name': '{$res_name}', \n";
		echo "\t'type': '{$result['type']}', \n"; // Andrea Danzi aggiunto type - 24.03.2012
        echo "\t'type_trans': '".addslashes(getTranslatedString($result['type'],'Accounts'))."', \n"; // danzi.tn@20150414 aggiunto type_trans
        echo "\t'account_line': '{$result['account_line']}', \n"; // danzi.tn@20150414 aggiunto account_line
        echo "\t'account_main_activity': '".addslashes(getTranslatedString($result['account_main_activity'],'Accounts'))."', \n"; // danzi.tn@20150414 aggiunto account_main_activity
        echo "\t'account_sec_activity': '".addslashes(getTranslatedString($result['account_sec_activity'],'Accounts'))."', \n"; // danzi.tn@20150414 aggiunto account_sec_activity
        echo "\t'user_name': '{$result['user_name']}', \n"; //  danzi.tn@20150618 aggiunto user_name
        echo "\t'last_name': '{$result['last_name']}', \n"; //  danzi.tn@20150618 aggiunto last_name
        echo "\t'first_name': '{$result['first_name']}', \n"; //  danzi.tn@20150618 aggiunto first_name
		echo "\t'map_value': '{$result['map_value']}', \n"; // Andrea Danzi aggiunto map_value - 26.03.2012
		echo "\t'city': '{$city}', \n";
		echo "\t'code': '{$result['code']}', \n";
		echo "\t'street': '{$street}', \n";
		echo "\t'country': '{$result['country']}', \n";
		echo "\t'state': '{$result['state']}', \n";
		echo "\t'account_phone': '{$result['account_phone']}', \n";
		echo "\t'record_id': '{$key}', \n";
		echo "\t'extra': '".addslashes( str_replace(array("\r", "\r\n", "\n"), '',$result['extra']))."', \n";
		echo "\t'pos': [{$result['lat']},{$result['lng']}], \n";
		echo "\t'map_aurea': '{$result['map_aurea']}', \n";
		echo "\t},\n";
	}

	echo "	};\n";
	if(isset($skippedAccs) && !empty($skippedAccs)) {
		echo "var skippedLayer = {\n";
		foreach($skippedAccs as $nkey=>$nresult)
		{
	       	        $res_name = $nresult['name'];
                    $res_name = str_replace(array("\r", "\n"), '', $res_name); // danzi.tn@201509004 rimuovere newline
	       	        $res_name = trim($res_name);
			echo "\t'{$nkey}': \n";
			echo "\t{\n";
			// echo "\t'name': '{$result['name']}', \n";
			echo "\t'name': '{$res_name}', \n";
			echo "\t'type': '{$nresult['type']}', \n"; // Andrea Danzi aggiunto type - 24.03.2012
			echo "\t'accid': '{$nkey}', \n"; // Andrea Danzi aggiunto map_value - 26.03.2012
			echo "\t'map_value': '{$nresult['map_value']}', \n"; // Andrea Danzi aggiunto map_value - 26.03.2012
			echo "\t'city': '{$nresult['city']}', \n";
			echo "\t'code': '{$nresult['code']}', \n";
			echo "\t'street': '{$nresult['street']}', \n";
			echo "\t'country': '{$nresult['country']}', \n";
			echo "\t'state': '{$nresult['state']}', \n";
			echo "\t'account_phone': '{$nresult['account_phone']}', \n";
			echo "\t'extra': '".str_replace(array("\r", "\r\n", "\n"), '', $nresult['extra'])."', \n";
			echo "\t},\n";
		}
		echo "	};\n";
	}else{
		echo "var skippedLayer = null;";
	}
}

function clean_text($text)
{
	$text=strtolower($text);
	$code_entities_match = array('--','&quot;','!','@','#','$','%','^','&','*','(',')','_','+','{','}','|',':','"','<','>','?','[',']','\\',';',"'",',','.','/','*','+','~','`','=','°');
	$code_entities_replace = array('-','','','','','','','','','','','','','','','','','','',' ','','','','','','');
	$text = str_replace($code_entities_match, $code_entities_replace, $text);
	return $text;
}


?>
