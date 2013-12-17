<?
function do_import_contacts($time_start) {
	global $adb,$seq_log,$current_user,$mapping,$root_directory,$external_code,$module,$table,$fields_auto_create,$fields_auto_update,$where;	
	$import = new importer($module,$mapping,$external_code,$time_start,$fields_auto_create,$fields_auto_update);
//	$sql="select ".implode(",",array_keys($mapping))." from $table $where";
	
	$array_key=array_keys($mapping);
	$key = array_search('PERSON_PARENT1', $array_key);
	$array_key[$key]="PERSON_PARENT as PERSON_PARENT1";
	
	$sql="select ".implode(",",$array_key)." from $table $where";
        echo $sql;	
	$import_info = $import->go($sql);
	
	foreach($import_info['external_code_rows'] as $ext_cod){
		import_contacts_info($ext_cod);
	}
	
	return $import_info;
}

function import_contacts_info($ext_cod){
	global $adb,$table_info,$external_code_info;
	
	$q="select * from $table_info where $external_code_info = ?";
	$res=$adb->pquery($q,array($ext_cod));
	if($res && $adb->num_rows($res) > 0){
		while($row=$adb->fetchByAssoc($res,-1,false)){
			$field="";
			$sec_q=false;
			switch($row['contact_media']){
				case 'TELEFONO':
					if($row['contact_typecode'] == '100') {
						$field='phone';
					}
					elseif($row['contact_typecode'] == '110')  {
						$field='homephone';
						$sec_q=true;
					}
					elseif($row['contact_typecode'] == '120')  {
						$field='mobile';
					}
					else  {
						$field='otherphone';
						$sec_q=true;
					}
				break;
				case 'FAX':
					if($row['contact_typecode'] == '200') $field='fax';
				break;
				case 'EMAIL':
					if($row['contact_typecode'] == '300') $field='email';
				break;
				
			}
			if( empty($field) ) continue;
			if($sec_q){
				$updt_query="update vtiger_contactsubdetails 
							set $field=?
							from vtiger_contactsubdetails
							inner join vtiger_contactdetails 
							on vtiger_contactdetails.contactid=vtiger_contactsubdetails.contactsubscriptionid 
							inner join vtiger_crmentity 
							on vtiger_contactdetails.contactid=vtiger_crmentity.crmid and deleted=0 
							 where ext_code=?";
			}
			else{
				$updt_query="update vtiger_contactdetails 
							set $field=?
							from vtiger_contactdetails
							inner join vtiger_crmentity 
							on vtiger_contactdetails.contactid=vtiger_crmentity.crmid and deleted=0
							 where ext_code=?";
			}
			
			$adb->pquery($updt_query,array($row['contact_commdata'],$ext_cod));
		}
	}
}
?>
