<?php
// danzi.tn@20140820 aggiornamento annualrevenue sulla base degli ordini dell'ultimo anno
// danzi.tn@20141218 aggiornamento rating attuale degli ultimi 2 anni
// danzi.tn@20150408 introduzione fatturato anno precendente e fatturato anno precedente -1
function do_import_accounts($time_start) {
	global $adb;
	global $seq_log,$current_user,$mapping,$root_directory,$external_code,$module,$table,$fields_auto_create,$fields_auto_update,$where;	
	
	$import = new importer($module,$mapping,$external_code,$time_start,$fields_auto_create,$fields_auto_update);
	
	$array_key=array_keys($mapping);
	$key = array_search('ORDINE_NUMERO', $array_key);
	$array_key[$key]="DISTINCT ORDINE_NUMERO";
	$key = array_search('CLIENTE_FATT_AZ', $array_key);
	$array_key[$key]="CLIENTE_FATTURAZIONE as CLIENTE_FATT_AZ";
	$key = array_search('CLIENTE_FATT_ASS', $array_key);
	$array_key[$key]="CLIENTE_FATTURAZIONE as CLIENTE_FATT_ASS";
	$key = array_search('ORDINE_NUMERO_SOGG', $array_key);
	$array_key[$key]="ORDINE_NUMERO as ORDINE_NUMERO_SOGG";
	$key = array_search('ORDINE_NUMERO_KEY', $array_key);
	$array_key[$key]="ORDINE_NUMERO as ORDINE_NUMERO_KEY";
	
	$key = array_search('TOTAL', $array_key);
	$array_key[$key]="NULL as TOTAL";
	$key = array_search('SUBTOTAL', $array_key);
	$array_key[$key]="NULL as SUBTOTAL";
	$key = array_search('STATO', $array_key);
	$array_key[$key]="NULL as STATO";
	$at1 = microtime(True);
	$sql = "select ".implode(",",$array_key)." from $table $where";
	//echo $sql;die;
	$num_rows = $adb->num_rows($adb->query($sql));
	if ($num_rows == 0) {
		echo "WARNING: no rows";
	}
    $at2 = microtime(True);
    echo sprintf("After num_rows:  %f\n", $at2-$at1);
	$interval = 10000;
	if($interval < $num_rows){
        echo sprintf("More than %d rows (%d)\n", $interval,$num_rows );
		$num=0;
		while($num<=$num_rows){
			$sql1=$sql;//" limit $num,$interval";
            $at1 = microtime(True);
			$tmp_result=$import->go($sql1);
            $at2 = microtime(True);
            echo sprintf("After go:  %f\n", $at2-$at1);
			
			$num += $interval;
			/*
			foreach($tmp_result['external_code_rows'] as $ext_cod){
				//migrate_crmentity_data_accounts($ext_cod);
			}
            */
			
			$import_result['records_created']+=$tmp_result['records_created'];
			$import_result['records_updated']+=$tmp_result['records_updated'];
			//free resurces
			unset($import);
			//new instance
            $at1 = microtime(True);
			$import = new importer($module,$mapping,$external_code,$time_start,$fields_auto_create,$fields_auto_update);
            $at2 = microtime(True);
            echo sprintf("After importer (num=%d):  %f\n", $num , $at2-$at1);
		}
	}
	else{
		$import_result = $import->go($sql);
        /*
		foreach($import_result['external_code_rows'] as $ext_cod){
			//migrate_crmentity_data_accounts($ext_cod);
		}
        */
	}
    // danzi.tn@20150408
    // update_account_annual_revenue();
    update_account_annual_revenue("1", "last_annual_revenue");
    update_account_annual_revenue("2", "pre_last_annual_revenue");
    // danzi.tn@20150408e
	
	// danzi.tn@20141218
	update_rating_attuale();
	// danzi.tn@20141218e
	return $import_result;
}

// $ext_cod = vecchio id
// $adb = vecchio db
// $adb = nuovo db
function migrate_crmentity_data_accounts($ext_cod){
	global $adb;
	

	$q = "select * from vtiger_crmentity where crmid = ?";
	$res = $adb->pquery($q, array($ext_cod));
	if ($res && $adb->num_rows($res) > 0) {
		$row=$adb->fetchByAssoc($res);
		$updt_query="update vtiger_salesorder
						inner join vtiger_crmentity 
						on vtiger_salesorder.salesorderid = vtiger_crmentity.crmid and deleted=0
						set smcreatorid=?, modifiedby=?, createdtime=?, modifiedtime=?
						 where vtiger_salesorder.no_order_key = ?";
		$adb->pquery($updt_query, array($row['smcreatorid'], $row['modifiedby'], $row['createdtime'], $row['modifiedtime'], $ext_cod));
//		echo $adb->convert2Sql($updt_query,$adb->flatten_array(array($row['smcreatorid'],$row['modifiedby'],$row['createdtime'],$row['modifiedtime'],$ext_cod)))."\n";
	}
	
}
// danzi.tn@20140820 aggiornamento annualrevenue sulla base degli ordini dell'ultimo anno
// danzi.tn@20150408 introduzione fatturato anno precendente e fatturato anno precedente -1
// danzi.tn@20150421 calcolo eseguito sul vtiger_salesorder.total e non su pressi unitari
function update_account_annual_revenue($past_years = "1", $field_to_update="last_annual_revenue") {
    global $adb;
    $q = "UPDATE
            VTACC
            SET
            VTACC.".$field_to_update." = VTTOTALS.TotalSales
            FROM vtiger_account AS VTACC INNER JOIN
            (SELECT 
            vtiger_account.accountid,
            sum( vtiger_salesorder.total) as TotalSales
            from vtiger_account
            JOIN vtiger_crmentity as accent on accent.crmid = vtiger_account.accountid and accent.deleted = 0
            JOIN vtiger_salesorder ON vtiger_salesorder.accountid  = vtiger_account.accountid  
            JOIN vtiger_crmentity as salent ON vtiger_salesorder.salesorderid = salent.crmid  AND salent.deleted =0            
            WHERE  YEAR(vtiger_salesorder.data_ordine_ven) = YEAR(DATEADD(year,-".$past_years.",GETDATE()))
            GROUP BY vtiger_account.accountid) VTTOTALS
            ON VTTOTALS.accountid = VTACC.accountid";
    $res = $adb->query($q);
}
// danzi.tn@20140820e



// danzi.tn@20141218 aggiornamento rating attuale
function update_rating_attuale() {
    global $adb;
    $q = "UPDATE vtiger_accountscf
			SET vtiger_accountscf.cf_927 = 
			CASE 
				WHEN vtiger_salesorder.salesorderid IS NOT NULL THEN
					CASE 
						WHEN vtiger_accountscf.cf_927 IS NULL THEN '1'
						WHEN vtiger_accountscf.cf_927 = '' THEN '1'
						WHEN vtiger_accountscf.cf_927 = '1' THEN '1'
						WHEN vtiger_accountscf.cf_927 = '10' THEN '1'
						WHEN vtiger_accountscf.cf_927 = '20' THEN '1'
						WHEN vtiger_accountscf.cf_927 = '30' THEN '30'
						WHEN vtiger_accountscf.cf_927 = '31' THEN '1'
						WHEN vtiger_accountscf.cf_927 = '32' THEN '1'
						WHEN vtiger_accountscf.cf_927 = '33' THEN '1'
						WHEN vtiger_accountscf.cf_927 = '40' THEN '1'
						WHEN vtiger_accountscf.cf_927 = 'Riattivato' THEN '1' 
					END
				ELSE
					CASE 
						WHEN vtiger_accountscf.cf_927 IS NULL THEN '33'
						WHEN vtiger_accountscf.cf_927 = '' THEN '33'
						WHEN vtiger_accountscf.cf_927 = '1' THEN '33'
						WHEN vtiger_accountscf.cf_927 = '10' THEN '10'
						WHEN vtiger_accountscf.cf_927 = '20' THEN '20'
						WHEN vtiger_accountscf.cf_927 = '30' THEN '30'
						WHEN vtiger_accountscf.cf_927 = '31' THEN '31'
						WHEN vtiger_accountscf.cf_927 = '32' THEN '32'
						WHEN vtiger_accountscf.cf_927 = '33' THEN '33'
						WHEN vtiger_accountscf.cf_927 = '40' THEN '33'
						WHEN vtiger_accountscf.cf_927 = 'Riattivato' THEN '33' 
					END
			END
			FROM vtiger_accountscf
			JOIN vtiger_crmentity accent on vtiger_accountscf.accountid = accent.crmid AND accent.deleted = 0 
			JOIN vtiger_account on vtiger_account.accountid = vtiger_accountscf.accountid 
			LEFT JOIN vtiger_salesorder on vtiger_account.accountid = vtiger_salesorder.accountid 
			LEFT JOIN vtiger_crmentity salent on vtiger_salesorder.salesorderid = salent.crmid 
			WHERE 
			vtiger_account.external_code <> '' -- Codice Cleinte Valorizzato
			AND vtiger_account.external_code IS NOT NULL -- Codice Cleinte Valorizzato
			AND vtiger_salesorder.data_ordine_ven BETWEEN  DATEADD(MONTH,-24,GETDATE() ) AND GETDATE()
            AND salent.deleted = 0";
    $res = $adb->query($q);
}
// danzi.tn@20141218

?>