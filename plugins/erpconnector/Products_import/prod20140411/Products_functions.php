<?php
function do_import_accounts($time_start) {
	global $adb,$adbext,$log_active;
	global $seq_log,$current_user,$mapping,$root_directory,$external_code,$module,$table,$fields_auto_create,$fields_auto_update,$where;	
	
	$import = new importer($module,$mapping,$external_code,$time_start,$fields_auto_create,$fields_auto_update);
	
	$array_key=array_keys($mapping);
	$key = array_search('BASE_DELETED', $array_key);
	$array_key[$key]="CASE(BASE_DELETED) WHEN 1 THEN 0
			ELSE 1 END BASE_DELETED";
	
	$key = array_search('BASE_NO', $array_key);
	$array_key[$key]="BASE_NUMBER AS BASE_NO";
	
	$sql = "select ".implode(",",$array_key)." from $table $where";
	//echo $sql;die;
	$num_rows = $adb->num_rows($adb->query($sql));
	if ($num_rows == 0) {
		echo "WARNING: no rows\n";
	}
	if($log_active) echo "Total rows nunmber = ".$num_rows."\n";
	$interval = 10000;
	if($interval < $num_rows){
		if($log_active) echo "Rows are more than 10000\n";
		try {
		$num=0;
		while($num<=$num_rows){
			$sql1=$sql;//" limit $num,$interval";
			try {
				$tmp_result=$import->go($sql1);
			} catch (Exception $e) {
				echo 'import->go raised an exception on sql'.$sql1.' , message is: ',  $e->getMessage(), "\n";
			}
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
			if($log_active) echo $num." successfully imported rows, still ".($num_rows-$num)." remaining \n";
		}
		} catch (Exception $e) {
                	echo 'importer raised an exception at  '.$num.' imported rows, error message is:'.  $e->getMessage(), "\n";
       		}
		  
	}
	else{
		$import_result = $import->go($sql);
		foreach($import_result['external_code_rows'] as $ext_cod){
			//migrate_crmentity_data_accounts($ext_cod);
		}
	}
	if($log_active) echo "Trying update_product vendor!\n";
	try {
		update_product_vendor();
	} catch (Exception $e) {
		echo 'update_product_vendor raised an exception: ',  $e->getMessage(), "\n";
	}	
	return $import_result;
}

// $ext_cod = vecchio id
// $adbext = vecchio db
// $adb = nuovo db
function migrate_crmentity_data_accounts($ext_cod){
	global $adb,$adbext;
	

	$q = "select * from vtiger_crmentity where crmid = ?";
	$res = $adbext->pquery($q, array($ext_cod));
	if ($res && $adbext->num_rows($res) > 0) {
		$row=$adbext->fetchByAssoc($res);
		$updt_query="update vtiger_products
						inner join vtiger_crmentity 
						on vtiger_products.productid = vtiger_crmentity.crmid and deleted=0
						set smcreatorid=?, modifiedby=?, createdtime=?, modifiedtime=?
						 where vtiger_products.productid_old = ?";
		$adb->pquery($updt_query, array($row['smcreatorid'], $row['modifiedby'], $row['createdtime'], $row['modifiedtime'], $ext_cod));
//		echo $adb->convert2Sql($updt_query,$adb->flatten_array(array($row['smcreatorid'],$row['modifiedby'],$row['createdtime'],$row['modifiedtime'],$ext_cod)))."\n";
	}
	
}

function update_product_vendor()
{
	global $adb,$adbext;
	$sQuery= "UPDATE vtiger_products
				SET vtiger_products.vendor_id = vtiger_vendorcf.vendorid
				FROM 
				vtiger_products
				INNER JOIN  vtiger_productcf
				ON vtiger_products.productid=vtiger_productcf.productid
				INNER JOIN vtiger_vendorcf
				ON vtiger_productcf.cf_1116 = vtiger_vendorcf.cf_1115 and vtiger_productcf.cf_1116 is not null and  vtiger_productcf.cf_1116 <>''
				INNER JOIN vtiger_vendor
				ON vtiger_vendor.vendorid = vtiger_vendorcf.vendorid
				INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid =  vtiger_products.productid and vtiger_crmentity.deleted = 0
				INNER JOIN vtiger_crmentity AS crme2
				ON crme2.crmid =  vtiger_vendor.vendorid and crme2.deleted = 0
				WHERE vtiger_products.vendor_id IS NULL OR vtiger_products.vendor_id=0";
	$adb->pquery($sQuery, array());
}

?>
