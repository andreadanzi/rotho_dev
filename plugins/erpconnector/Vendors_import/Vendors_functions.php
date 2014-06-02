<?php
function do_import_vendors($time_start) {
	global $adb,$seq_log,$current_user,$mapping,$root_directory,$external_code,$module,$table,$fields_auto_create,$fields_auto_update,$where;	
	$import = new importer($module,$mapping,$external_code,$time_start,$fields_auto_create,$fields_auto_update);
	
	$array_key=array_keys($mapping);
	$key = array_search('SUPPLIER_DESCR', $array_key);
	$array_key[$key]="'IMPORTED DATE ' +INSERTDATE+ ' - ' + SUPPLIER_NUMBER+' / '+SUPPLIER_NAME as SUPPLIER_DESCR";
	// danzi.tn@20140602 DEFAULT VENDOR_TYPE = Merce conto vendita e VENDOR_STATUS = Attivo
	$key = array_search('VENDOR_TYPE', $array_key);
	$array_key[$key]="'1' AS VENDOR_TYPE";
	$key = array_search('VENDOR_STATUS', $array_key);
	$array_key[$key]="'1' AS VENDOR_STATUS";
	$sql="select ".implode(",",$array_key)." from $table $where";
	$import_info = $import->go($sql);
	foreach($import_info['external_code_rows'] as $ext_cod){
		import_vendors_info($ext_cod);
	}
	
	return $import_info;
}

function import_vendors_info($ext_cod){
	global $adb,$table_info,$external_code_info;	
	$q="select * from $table_info where $external_code_info = ?";
	$res=$adb->pquery($q,array($ext_cod));
	if($res && $adb->num_rows($res) > 0){
		while($row=$adb->fetchByAssoc($res,-1,false)){
			switch($row['contact_media']){
				case 'TELEFONO':
					if($row['contact_type'] == 'Telefon, dienstlich')
						$field='phone';
					else
						$field='phone';
				break;
				case 'FAX':
					$field='fax';
				break;
				case 'WWW':
					$field='website';
				break;
				case 'EMAIL':
					if($row['contact_type'] == 'E-Mail, dienstlich')
						$field='email';
					else
						$field='email';
				break;
				
			}
			$updt_query="update vtiger_vendor 
						set $field=?
						from vtiger_vendor
						inner join vtiger_crmentity 
						on vtiger_vendor.vendorid=vtiger_crmentity.crmid and deleted=0
						inner join vtiger_vendorcf
						on vtiger_vendor.vendorid=vtiger_vendorcf.vendorid and vtiger_vendorcf.cf_1115=?";
			$adb->pquery($updt_query,array($row['contact_commdata'],$ext_cod));
		}
	}
}

?>