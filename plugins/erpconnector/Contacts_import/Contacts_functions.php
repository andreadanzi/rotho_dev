<?
// danzi.tn@20150408 aggiornare smownerid del contatto sulla base di quello dell'azienda collegata
function do_import_contacts($time_start) {
	global $adb,$seq_log,$current_user,$mapping,$root_directory,$external_code,$module,$table,$fields_auto_create,$fields_auto_update,$where;	
	$import = new importer($module,$mapping,$external_code,$time_start,$fields_auto_create,$fields_auto_update);
//	$sql="select ".implode(",",array_keys($mapping))." from $table $where";
	
	$array_key=array_keys($mapping);
	$key = array_search('PERSON_PARENT1', $array_key);
	$array_key[$key]="PERSON_PARENT as PERSON_PARENT1";
	// danzi.tn@20140213 IMPORTFLAG Per distinguere i record modificati/creati dalla procedura di import
	$key = array_search('IMPORTFLAG', $array_key);
	$array_key[$key]="'XXX' AS IMPORTFLAG";
	
	$sql="select ".implode(",",$array_key)." from $table $where";
    echo $sql;	
	echo "\n";
	$import_info = $import->go($sql);
	echo "Go terminated!\n";
	$ii=0;
	$hash_table = array();
	foreach($import_info['external_code_rows'] as $ext_cod){
		if(array_key_exists($ext_cod,$hash_table)) { 
			// echo $ext_cod . " already imported\n";
			continue;
		}
		import_contacts_info($ext_cod);
		$ii++;
		$hash_table[$ext_cod]=$ii;
		// echo "(".$ii."|".$ext_cod."),";
	}
	echo "import_contacts_info terminated!\n";
	update_vendor_id();
    update_smownerid();
	echo "update_vendor_id terminated!\n";
	return $import_info;
}

// danzi.tn@20140412 ORIG_PERSON_PARENT Per salvarsi il person_parent del contatto che va in cf_1249
function update_vendor_id() {
	global $adb,$table_info,$external_code_info;
	$sql = "UPDATE
		vtiger_contactdetails
		SET 
		vtiger_contactdetails.vendor_id = vtiger_vendorcf.vendorid
		from vtiger_contactdetails 
		JOIN vtiger_contactscf ON  vtiger_contactscf.contactid = vtiger_contactdetails.contactid
		JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid and deleted = 0
		JOIN vtiger_vendorcf ON vtiger_vendorcf.cf_1115 = vtiger_contactscf.cf_1249 
		WHERE vtiger_vendorcf.cf_1115 IS NOT NULL AND vtiger_vendorcf.cf_1115 <>''";
	$adb->query($sql);	
}

// danzi.tn@20150408 aggiornare smownerid del contatto sulla base di quello dell'azienda collegata
function update_smownerid() {
    global $adb,$table_info,$external_code_info;
	$sql = "UPDATE 
            vtiger_crmentity
            SET
            vtiger_crmentity.smownerid = 
            CASE 
                WHEN accentity.smownerid IS NULL THEN vtiger_crmentity.smownerid
                ELSE accentity.smownerid
            END	
            from vtiger_crmentity
            join vtiger_contactdetails on vtiger_crmentity.crmid = vtiger_contactdetails.contactid 
            join vtiger_account on vtiger_account.accountid = vtiger_contactdetails.accountid
            join vtiger_crmentity accentity on accentity.crmid = vtiger_account.accountid and accentity.deleted = 0
            where 
            vtiger_crmentity.deleted = 0
            AND accentity.smownerid <> vtiger_crmentity.smownerid";
	$adb->query($sql);	
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
