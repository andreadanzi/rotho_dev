<?
function testQuery($time_start=null)
{
	$query="
select condizioni_prezzo 
			from vte40_387.dbo.vtiger_condizioni_prezzo 
			inner join	vte40_387.dbo.vtiger_role2picklist on vte40_387.dbo.vtiger_role2picklist.picklistvalueid = vte40_387.dbo.vtiger_condizioni_prezzo.picklist_valueid
			and roleid = 'H2'  
			order by condizioni_prezzo asc
			";
	global $adb;
	$time1 = time();
	$result = $adb->query($query);
	$time2 = time();
	$diff = $time2 - $time1;
	echo "diff1 = ".$diff."\n";
	$time3 = time();
	$ilocal=0;
	while ($row = $adb->fetchByAssoc($result)) {
		$ilocal=$ilocal+1;
	}
	$time4 = time();
	$diff = $time4 - $time3;
	echo "diff2 = ".$diff."\n";
	echo "RecordSelected= ".$ilocal."\n";
	return array('records_selected'=>$ilocal);
}

function populateMapTable($time_start=null)
{
	global $adb,$geocoder_max_num_rows,$geocoder_delay,$main_module,$sDB_HOST,$sDB_PORT, $sDB_USER, $sDB_PASS,$sDB_NAME,$csv_file_location;
	$ids = null;
	$gc = new GeoCoder();
	$gc->setDelay($geocoder_delay);
	$gc->log_level = 1;
	$gc->file_csv = fopen($csv_file_location, 'w');
	$location_header = array("id",'state','city','code','street','country','error_code','mapped');
	fputcsv($gc->file_csv,  $location_header);
	
	$query = "SELECT TOP " .$geocoder_max_num_rows." accountaddressid as id, bill_state as state, bill_city as city, bill_code as code, bill_street as street, bill_country as country, 1 as error_code ,0 as mapped FROM vtiger_accountbillads  LEFT JOIN vtiger_map on accountaddressid = mapid LEFT JOIN vtiger_crmentity on accountaddressid = crmid WHERE mapid IS NULL AND deleted=0 ";
	
	$query .= " ORDER BY accountaddressid DESC";
	// echo $query ."<br/>";
	$result = $adb->query($query);
	$locations = array();
	$ilocal=0;
	while ($row = $adb->fetchByAssoc($result)) {
		$stripped_street = clean_text($row['street']); 
	    $locations[$ilocal] =array(strtolower($row['id']),strtolower($row['state']),strtolower($row['city']),strtolower($row['code']),$stripped_street,strtolower($row['country']),0);
		$ilocal=$ilocal+1;
		if($ilocal>=$geocoder_max_num_rows) break;
	}
	echo "Array lengh= ".$ilocal."\n";
	
	$recordInserted = $gc->populateCache($locations);
	echo "RecordInserted= ".$recordInserted."\n";;
	$ilocal = $ilocal - $recordInserted;
	fclose($gc->file_csv);
	// $records['records_created'] $records['records_updated']
	return array('records_created'=>$recordInserted,'records_updated'=>$ilocal);
}


function clean_text($text)
{
	$text=strtolower($text);
	$code_entities_match = array('--','&quot;','!','@','#','$','%','^','&','*','(',')','_','+','{','}','|',':','"','<','>','?','[',']','\\',';',"'",',','.','/','*','+','~','`','=','°','¦','¯','Ô');
	$code_entities_replace = array('-','','','','','','','','','','','','','','','','','','',' ','','','','','','','','ss','');
	$text = str_replace($code_entities_match, $code_entities_replace, $text);
	return $text;
} 


?>
