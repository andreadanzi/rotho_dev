<?php
function do_import_accounts($time_start) {
	global $adb;
	global $seq_log,$current_user,$mapping,$root_directory,$external_code,$module,$table,$fields_auto_create,$fields_auto_update,$where;	
	
	$import = new importer($module,$mapping,$external_code,$time_start,$fields_auto_create,$fields_auto_update);
	
	$array_key=array_keys($mapping);
	$key = array_search('PROPOSAL_NUMBER', $array_key);
	$array_key[$key]="DISTINCT PROPOSAL_NUMBER";
	$key = array_search('TOTAL', $array_key);
	$array_key[$key]="NULL as TOTAL";
	$key = array_search('SUBTOTAL', $array_key);
	$array_key[$key]="NULL as SUBTOTAL";
//	$key = array_search('CLIENTE_FATT_AZ', $array_key);
//	$array_key[$key]="CLIENTE_FATTURAZIONE as CLIENTE_FATT_AZ";
//	$key = array_search('CLIENTE_FATT_ASS', $array_key);
//	$array_key[$key]="CLIENTE_FATTURAZIONE as CLIENTE_FATT_ASS";
//	$key = array_search('ORDINE_NUMERO_SOGG', $array_key);
//	$array_key[$key]="ORDINE_NUMERO as ORDINE_NUMERO_SOGG";
//	$key = array_search('ORDINE_NUMERO_KEY', $array_key);
//	$array_key[$key]="ORDINE_NUMERO as ORDINE_NUMERO_KEY";
		 
	$sql = "select ".implode(",",$array_key)." from $table $where ORDER BY PROPOSAL_NUMBER";
//	echo $sql;die;  
	$num_rows = $adb->num_rows($adb->query($sql));
	if ($num_rows == 0) {
		echo "WARNING: no rows";
	}

	$interval = 10000;
	if($interval < $num_rows){
		$num=0;
		while($num<=$num_rows){
			$sql1=$sql;//" limit $num,$interval";
			$tmp_result=$import->go($sql1);
			
			$num += $interval;
			
			foreach($tmp_result['external_code_rows'] as $ext_cod){
				//migrate_crmentity_data_accounts($ext_cod);
			}
			
			$import_result['records_created']+=$tmp_result['records_created'];
			$import_result['records_updated']+=$tmp_result['records_updated'];
			//free resurces
			unset($import);
			//new instance
			$import = new importer($module,$mapping,$external_code,$time_start,$fields_auto_create,$fields_auto_update);
		}
	}
	else{
		$import_result = $import->go($sql);
		foreach($import_result['external_code_rows'] as $ext_cod){
			//migrate_crmentity_data_accounts($ext_cod);
		}
	}
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
		$updt_query="update vtiger_quotes
						inner join vtiger_crmentity 
						on vtiger_quotes.quoteid = vtiger_crmentity.crmid and deleted=0
						set smcreatorid=?, modifiedby=?, createdtime=?, modifiedtime=?
						 where vtiger_quotes.keyid_quote = ?";
		$adb->pquery($updt_query, array($row['smcreatorid'], $row['modifiedby'], $row['createdtime'], $row['modifiedtime'], $ext_cod));
//		echo $adb->convert2Sql($updt_query,$adb->flatten_array(array($row['smcreatorid'],$row['modifiedby'],$row['createdtime'],$row['modifiedtime'],$ext_cod)))."\n";
	}
	
}

?>