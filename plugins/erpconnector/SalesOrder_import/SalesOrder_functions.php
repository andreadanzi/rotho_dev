<?php
// danzi.tn@20140820 aggiornamento annualrevenue sulla base degli ordini dell'ultimo anno
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
	
	$sql = "select ".implode(",",$array_key)." from $table $where";
	//echo $sql;die;
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
    // danzi.tn@20140820
    update_account_annual_revenue();
    // danzi.tn@20140820e
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
function update_account_annual_revenue() {
    global $adb;
    $q = "UPDATE
            VTACC
            SET
            VTACC.annualrevenue = VTTOTALS.TotalSales
            FROM vtiger_account AS VTACC INNER JOIN
            (SELECT 
            vtiger_account.accountid,
            sum( 
            case when vtiger_inventoryproductrel.listprice is NULL then 0 
            when vtiger_inventoryproductrel.quantity is null then 0 
            else vtiger_inventoryproductrel.listprice*vtiger_inventoryproductrel.quantity 
            END) as TotalSales
            from vtiger_account
            JOIN vtiger_crmentity as accent on accent.crmid = vtiger_account.accountid and accent.deleted = 0
            JOIN vtiger_salesorder ON vtiger_salesorder.accountid  = vtiger_account.accountid  
            JOIN vtiger_crmentity as salent ON vtiger_salesorder.salesorderid = salent.crmid  AND salent.deleted =0
            LEFT JOIN vtiger_inventoryproductrel on  vtiger_inventoryproductrel.id  = vtiger_salesorder.salesorderid
            GROUP BY vtiger_account.accountid) VTTOTALS
            ON VTTOTALS.accountid = VTACC.accountid";
    $res = $adb->query($q);
}
// danzi.tn@20140820e

?>