<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
 * ********************************************************************************** */
// Switch the working directory to base
// chdir(dirname(__FILE__) . '/../..');

include_once 'include/Zend/Json.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'include/utils/VtlibUtils.php';
include_once 'include/Webservices/Create.php';
include_once 'data/CRMEntity.php';

class RothoBus {
	
	var $import_result = Array();
	var $uids_array_fe_users_insert = Array();
	var $uids_array_fe_users_update = Array();
	var $uids_array_fe_users_skipped = Array();
	var $uids_array_tt_address_insert = Array();
	var $uids_array_tt_address_update = Array();
	var $uids_array_tt_address_skipped = Array();
	var $mapping = Array();
	var $group_mapping = Array();
	var $pids_list_corso = Array(308,309,314,315,316,317,328,329,330,331,332,333,377,392,440,444);
	var $pids_list_download = Array(137,139,141,143,200,205);
	
	function __construct() { 
		$this->mapping['Leads']['firstname'] = 'first_name'; // manage also the lead source
		$this->mapping['Leads']['lastname'] = 'last_name';
		$this->mapping['Leads']['email'] = 'email';
		$this->mapping['Leads']['phone'] = 'phone';
		$this->mapping['Leads']['mobile'] = 'mobile';
		$this->mapping['Leads']['website'] = 'www';
		$this->mapping['Leads']['lane'] = 'address';
		$this->mapping['Leads']['company'] = 'company';
		$this->mapping['Leads']['city'] = 'city';
		$this->mapping['Leads']['code'] = 'zip';
		$this->mapping['Leads']['state'] = 'region';
		$this->mapping['Leads']['country'] = 'country';
		$this->mapping['Leads']['description'] = 'descr';
		$this->mapping['Leads']['fax'] = 'fax';
		$this->mapping['Leads']['leadstatus'] = 'leadstatus';
		$this->mapping['Leads']['leadsource'] = 'leadsource';
		$this->mapping['Leads']['assigned_user_id'] = 'assigned_user_id';
		$this->mapping['Leads']['smownerid'] = 'assigned_user_id';
		$this->mapping['Leads']['cf_747'] = 'idtarget';
		$this->mapping['Leads']['cf_808'] = 'tstamp';
		$this->mapping['Leads']['cf_761'] = 'cf_761';
		/* USERGROUP */
		$this->group_mapping['3']='RC / CARP';
		$this->group_mapping['4']='RS / SAFE';
		$this->group_mapping['5']='RC / CARP';
		$this->group_mapping['6']='RC / CARP';
		$this->group_mapping['7']='RS / SAFE';
		$this->group_mapping['8']='RS / SAFE';
		$this->group_mapping['9']='RS / SAFE';
		$this->group_mapping['10']='RS / SAFE';
		$this->group_mapping['11']='RS / SAFE';
		$this->group_mapping['12']='RS / SAFE';
		$this->group_mapping['13']='RS / SAFE';
		$this->group_mapping['14']='RS / SAFE';
		$this->group_mapping['15']='RS / SAFE';
		$this->group_mapping['16']='RS / SAFE';
		$this->group_mapping['17']='RS / SAFE';
		$this->group_mapping['18']='RS / SAFE';
		$this->group_mapping['19']='RS / SAFE';
		$this->group_mapping['20']='RS / SAFE';
		$this->group_mapping['21']='RS / SAFE';
		$this->group_mapping['22']='RS / SAFE';
		$this->mapping['Accounts']['key'] = 'value';
		$this->mapping['Vendors']['key'] = 'value';
		$this->mapping['Contacts']['key'] = 'value';
	}
	
	function populateNow() {
		global $adb,$table_prefix;
		$this->import_result['records_created']=0;
		$this->import_result['records_updated']=0;
		$sql_fe_users = $this->_get_web_temp_fe_users();
		$wsresult = $adb->query($sql_fe_users);
		while($fe_user = $adb->fetchByAssoc($wsresult)) {
			$this->_process_fe_users($fe_user);
		}
		// Processing tt_address
		$sql_tt_address = $this->_get_web_temp_tt_address();
		$wsresult = $adb->query($sql_tt_address);
		while($tt_address = $adb->fetchByAssoc($wsresult)) {
			$this->_process_tt_address($tt_address);
		}
		if(count($this->uids_array_fe_users_update))
			$this->_update_web_temp_fe_users($this->uids_array_fe_users_update,0);
		if(count($this->uids_array_fe_users_insert))
			$this->_update_web_temp_fe_users($this->uids_array_fe_users_insert,1);
		if(count($this->uids_array_fe_users_skipped))
			$this->_update_web_temp_fe_users($this->uids_array_fe_users_skipped,-1);
		/*
		if(count($this->uids_array_tt_address_insert))
			$this->_update_web_temp_tt_address($this->uids_array_tt_address_insert,0);
		if(count($this->uids_array_tt_address_update))
			$this->_update_web_temp_tt_address($this->uids_array_tt_address_update,1);
		if(count($this->uids_array_tt_address_skipped))
			$this->_update_web_temp_tt_address($this->uids_array_tt_address_skipped,-1);
			*/
		return $this->import_result;
	}
	
	private function _process_fe_users($fe_user) {
		echo "Processing uid = ".$fe_user['uid']." with email ".$fe_user['email'] ."\n";
		if(!filter_var($fe_user['email'], FILTER_VALIDATE_EMAIL)) {
			echo "Skipping uid = ".$fe_user['uid']." due to bad email ".$fe_user['email']."\n";
			$this->uids_array_fe_users_skipped[]=$fe_user['uid'];
			return;
		}
		foreach($this->group_mapping as $key=>$value) {
			$usergroup_array = explode(",",$fe_user['usergroup']);
			if( in_array($key,$usergroup_array) ) {
				$fe_user['cf_761'] = $value;
			}
		}
		$activitysubject = "Registrazione: ".$fe_user['first_name']." " .$fe_user['last_name']." - ".$fe_user['email'];
		$activitytype = "Registrazione - Safe";
		$activitydescr = "Registrazione proveniente dal sito tematico Safe di ".$fe_user['first_name']." " .$fe_user['last_name'];
		$activitydescr .= ", Azienda: ".$fe_user['company'];
		$fe_user['descr'] = $activitydescr . " , Categoria: " .$fe_user['usergroup_descr'];
		$activitydescr .= ", Email: ".$fe_user['email'];
		$activitydescr .= ", Indirizzo: ".$fe_user['address']." ".$fe_user['city']." ".$fe_user['region'];
		$activitydescr .= ", Categoria: " .$fe_user['usergroup_descr'];
		$activitydatetime = $fe_user['insertdate'];
		$retval=$this->_find_entities_by_email($fe_user['email']);
		$entity_ids = $retval[0];
		$entity_objects = $retval[1];			
		if(count($entity_ids)==0) { // NOT FOUND
			echo "Trying inserting Lead with email = ".$fe_user['email'];
			// INSERT LEAD AND ADD CALENDAR ENTRY
			$newLead = CRMEntity::getInstance('Leads');
			vtlib_setup_modulevars('Leads',$newLead);
			// MAPPING
			foreach($this->mapping['Leads'] as $key=>$value) {
				$newLead->column_fields[$key] = $fe_user[$value];
				echo "Setting ".$key."=".$fe_user[$value] . " \n";
			}
			$newLead->save($module_name='Leads',$longdesc=false);
			$this->_create_event($newLead,$activitysubject,$activitytype,$activitydescr,$activitydatetime);
			$this->import_result['records_created']++;
			$this->uids_array_fe_users_insert[]=$fe_user['uid'];
		} else {
			// FIRST UPDATE EXISTING
			foreach($entity_objects as $crmkey=>$entities) // this will be cycled only once
			{
				echo "In entity_objects found key=".$crmkey. " count=".count($entities)." \n";
				foreach($entities as $entitykey=>$entity) {
					echo " Add Event to id=". $entity->id;
					echo " Add Event to crmid=". $entity->column_fields["crmid"];
					$entity->mode = 'edit';
					// $entity->saveentity($module_name=$crmkey,$entity->id,$longdesc=false);
					$this->_create_event($entity,$activitysubject,$activitytype,$activitydescr,$activitydatetime);
					$this->import_result['records_updated']++;
				}
			}
			$this->uids_array_fe_users_update[]=$fe_user['uid'];
			// THE INSERT LEAD IF CORSI: how to identify lead corsi? (PID)
			// $import_result['records_created']++;
			// UPDATE ENTITY IN ANY CASE AND ADD CALENDAR ENTRY
			// MAPPING
			// foreach($this->mapping['Leads'] as $key=>$value) {
			// 	$existingLead->column_fields[$key] = $parms[$value];
			// }
			// IN ANY CASE ADD CALENDAR
		}
	}
	
	private function _process_tt_address($tt_address) {
		echo "Processing uid = ".$tt_address['uid']." with email ".$tt_address['email'] ."\n";
		if(!filter_var($tt_address['email'], FILTER_VALIDATE_EMAIL)) {
			echo "Skipping uid = ".$tt_address['uid']." due to bad email ".$tt_address['email']."\n";
			$this->uids_array_tt_address_skipped[]=$tt_address['uid'];
			continue;
		}
		$activitysubject = "Registrazione: ".$tt_address['first_name']." " .$tt_address['last_name']." - ".$tt_address['email'];
		$activitytype = "Registrazione - Safe";
		$activitydescr = "Registrazione proveniente dal sito tematico Safe di ".$tt_address['first_name']." " .$tt_address['last_name'];
		$activitydescr .= ", Azienda: ".$tt_address['company'];
		$activitydescr .= ", Email: ".$tt_address['email'];
		$activitydescr .= ", Indirizzo: ".$tt_address['address']." ".$tt_address['city']." ".$tt_address['region'];
		$activitydatetime = $tt_address['insertdate'];
		$retval=$this->_find_entities_by_email($tt_address['email']);
		$entity_ids = $retval[0];
		$entity_objects = $retval[1];			
		if(count($entity_ids)==0) { // NOT FOUND
			echo "Trying inserting Lead with email = ".$tt_address['email'];
			// INSERT LEAD AND ADD CALENDAR ENTRY
			$newLead = CRMEntity::getInstance('Leads');
			vtlib_setup_modulevars('Leads',$newLead);
			// MAPPING
			foreach($this->mapping['Leads'] as $key=>$value) {
				$newLead->column_fields[$key] = $tt_address[$value];
				echo "Setting ".$key."=".$tt_address[$value] . " \n";
			}
			// $newLead->save($module_name='Leads',$longdesc=false);
			// $this->_create_event($newLead,$activitysubject,$activitytype,$activitydescr,$activitydatetime);
			$this->import_result['records_created']++;
			$this->uids_array_tt_address_insert[]=$tt_address['uid'];
		} else {
			// FIRST UPDATE EXISTING
			foreach($entity_objects as $crmkey=>$entities) // this will be cycled only once
			{
				echo "In entity_objects found key=".$crmkey. " count=".count($entities)." \n";
				foreach($entities as $entitykey=>$entity) {
					echo " Add Event to id=". $entity->id;
					echo " Add Event to crmid=". $entity->column_fields["crmid"];
					$entity->mode = 'edit';
					// $entity->saveentity($module_name=$crmkey,$entity->id,$longdesc=false);
					// $this->_create_event($entity,$activitysubject,$activitytype,$activitydescr,$activitydatetime);
					$this->import_result['records_updated']++;
				}
			}
			$this->uids_array_tt_address_update[]=$tt_address['uid'];
			// THE INSERT LEAD IF CORSI: how to identify lead corsi? (PID)
			// $this->import_result['records_created']++;
			// UPDATE ENTITY IN ANY CASE AND ADD CALENDAR ENTRY
			// MAPPING
			// foreach($this->mapping['Leads'] as $key=>$value) {
			// 	$existingLead->column_fields[$key] = $parms[$value];
			// }
			// IN ANY CASE ADD CALENDAR
		}
	}
	
	/*
	$insp_activitytype 
		Contatto - Fiera
		Consulenza - Web
		Iscrizione Corso - Web
		Download - Web
		Registrazione - Safe
	*/
	private function _create_event($focus,$activitysubject,$activitytype,$activitydescr,$activitydatetime)  {
		echo "creating event for id=".$focus->id." with activitysubject = ". $activitysubject ;
		$acttime = strtotime($activitydatetime);
		$acttime = ceil($acttime/300)*300;
		$newEvent = CRMEntity::getInstance('Events');
		vtlib_setup_modulevars('Events',$newEvent);
		$newEvent->column_fields['subject'] = $activitysubject;
		$newEvent->column_fields['smownerid'] = $focus->column_fields["assigned_user_id"];
		$newEvent->column_fields['assigned_user_id'] = $focus->column_fields["assigned_user_id"];
		$newEvent->column_fields['createdtime'] = $focus->column_fields["modifiedtime"];
		$newEvent->column_fields['modifiedtime'] = $focus->column_fields["modifiedtime"];
		$newEvent->column_fields['parent_id'] = $focus->id;
		$newEvent->column_fields['date_start'] = date('Y-m-d',$acttime);// 2013-05-27 
		$newEvent->column_fields['time_start'] = date('H:i',$acttime);
		$newEvent->column_fields['due_date'] =  date('Y-m-d',$acttime); // 2013-05-27
		$newEvent->column_fields['time_end'] = date('H:i',$acttime+300);// 15:55
		// $newEvent->column_fields['duration_hours'] = 23;// 2
		// $newEvent->column_fields['duration_minutes'] = 59;// 2
		$newEvent->column_fields['activitytype'] = $activitytype; //$insp_activitytype;
		$newEvent->column_fields['is_all_day_event'] = '0';
		$newEvent->column_fields['eventstatus'] = 'Held';// $insp_eventstatus 
		$newEvent->column_fields['description'] = $activitydescr;
		$newEvent->save($module_name='Events',$longdesc=false);
		echo "created event with id=".$newEvent->id;
	}
	
	
	// row1.idcampaign + " date: " + row1.jobinsertdate + " exportkey=" +row1.exportkey  + " pid=" + row1.pid 
	private function _get_web_temp_tt_address() {
		$wsquery = "select uid, pid, name, first_name, last_name, email, phone, mobile, www, address, company, city, zip, region, country, description, fax, pagetitle, 'web' as type 
					, CASE WHEN pid in (".implode(", ",$this->pids_list_corso).") THEN 'Corso' WHEN pid in (".implode(", ",$this->pids_list_download).") THEN 'Download' END as leadsource
					, CASE WHEN pid in (".implode(", ",$this->pids_list_corso).") THEN 'Qualified' WHEN pid in (".implode(", ",$this->pids_list_download).") THEN 'Held' END as leadstatus
					, '167' as assigned_user_id
					, insertdate 
					, STR(pid) + '_' as idtarget
					, tstamp
					, '' as usergroup
					, '' as usergroup_descr
					from 
					web_temp_tt_address 
					where imported is NULL and email <>'' and email IS NOT NULL";
		return $wsquery;
	}
	
	private function _update_web_temp_tt_address($uids_array, $val) {
		global $adb,$table_prefix;
		$wsquery = "UPDATE  
					web_temp_tt_address 
					SET imported = ".$val." 
					WHERE imported is NULL and uid in (".implode(", ",$uids_array).")";
		$adb->query($wsquery);
	}
	
	private function _get_web_temp_fe_users() {
		$wsquery = "select uid, pid, name, first_name, last_name, email, phone, mobile, www, address, company, city, zip, region, country, description, fax, pagetitle, 'safe' as type 
					, 'RothoSafe' AS leadsource 
					, 'Held' as leadstatus
					, '167' as assigned_user_id
					, insertdate 
					, STR(pid) + '_safe' as idtarget
					, tstamp
					, usergroup 
					, usergroup_descr
					from 
					web_temp_fe_users 
					where imported is NULL and email <>'' and email IS NOT NULL";
		return $wsquery;
	}
	
	private function _update_web_temp_fe_users($uids_array, $val) {
		global $adb,$table_prefix;
		$wsquery = "UPDATE  
					web_temp_fe_users
					SET imported = ".$val."
					WHERE imported is NULL and uid in (".implode(", ",$uids_array).")";
		$adb->query($wsquery);
	}
	
	private function _find_entities_by_email($input_email) {
		global $adb,$table_prefix;		
		echo "\n----------------------_find_entities_by_email STARTS------------------------\n";
		$entity_ids = array();
		$entity_objects = array();
		// $wsquery = "SELECT crmid FROM ".$table_prefix."_crmentity LEFT JOIN ".$table_prefix."_contactdetails ON contactid=crmid WHERE email='".$input_email."'"; // tabid=4
		$wsquery = "SELECT ".$table_prefix."_crmentity.crmid, contactid  
							FROM ".$table_prefix."_crmentity 
							JOIN ".$table_prefix."_account ON ".$table_prefix."_account.accountid=".$table_prefix."_crmentity.crmid 
							JOIN ".$table_prefix."_contactdetails ON ".$table_prefix."_contactdetails.accountid = ".$table_prefix."_account.accountid
							JOIN ".$table_prefix."_crmentity as contactentity ON contactentity.crmid = ".$table_prefix."_contactdetails.contactid  
							WHERE ".$table_prefix."_crmentity.deleted=0 AND contactentity.deleted = 0 AND email1 <> email AND email2 <> email AND ".$table_prefix."_contactdetails.email='".$input_email."'"; // tabid=4
		$wsresult = $adb->query($wsquery);
		if ($wsresult){
			while($row = $adb->fetchByAssoc($wsresult)){
				echo "found contactid=".$row['contactid']."\n";
				$entity_ids[$row['crmid']] =1;
				$entity_ids[$row['contactid']] =1;
				$contact_entity = CRMEntity::getInstance('Contacts');
				$contact_entity->id = $row['contactid'];
				$contact_entity->retrieve_entity_info($row['contactid'],'Contacts');
				$entity_objects['Contacts'][$row['contactid']]=$contact_entity;
				$account_entity = CRMEntity::getInstance('Accounts');
				$account_entity->id = $row['crmid'];
				$account_entity->retrieve_entity_info($row['crmid'],'Accounts');
				$entity_objects['Accounts'][$row['crmid']]=$account_entity;
			}
		}
		$wsquery = "SELECT crmid FROM ".$table_prefix."_crmentity LEFT JOIN ".$table_prefix."_account ON accountid=crmid WHERE deleted=0 AND (email1='".$input_email."' OR email2='".$input_email."')"; // tabid=6
		$wsresult = $adb->query($wsquery);
		if ($wsresult){
			while($row = $adb->fetchByAssoc($wsresult)){
				echo "found accountid=".$row['crmid']."\n";
				$entity_ids[$row['crmid']] =1;
				$account_entity = CRMEntity::getInstance('Accounts');
				$account_entity->id = $row['crmid'];
				$account_entity->retrieve_entity_info($row['crmid'],'Accounts');
				$entity_objects['Accounts'][$row['crmid']]=$account_entity;
			}
		}		
		/* skip Vendors
		$wsquery = "SELECT crmid FROM ".$table_prefix."_crmentity LEFT JOIN ".$table_prefix."_vendor ON vendorid=crmid WHERE deleted=0 AND email='".$input_email."'"; // tabid=6
		$wsresult = $adb->query($wsquery);
		if ($wsresult){
			while($row = $adb->fetchByAssoc($wsresult)){
				echo "found vendorid=".$row['crmid']."\n";
				$entity_ids[$row['crmid']] =1;
				$vendor_entity = CRMEntity::getInstance('Vendors',$row['crmid']);
				$vendor_entity->retrieve_entity_info($row['crmid'],'Vendors');
				$entity_objects['Vendors'][$row['crmid']]=$vendor_entity;
			}
		}
		*/
		$wsquery = "SELECT crmid FROM ".$table_prefix."_crmentity LEFT JOIN ".$table_prefix."_leaddetails ON leadid=crmid WHERE deleted=0 AND converted=0 AND email='".$input_email."'"; // tabid=6
		$wsresult = $adb->query($wsquery);
		if ($wsresult){
			while($row = $adb->fetchByAssoc($wsresult)){
				echo "found leadid=".$row['crmid']."\n";
				$entity_ids[$row['crmid']] =1;
				$lead_entity = CRMEntity::getInstance('Leads');
				$lead_entity->id = $row['crmid'];
				$lead_entity->retrieve_entity_info($row['crmid'],'Leads');
				$entity_objects['Leads'][$row['crmid']]=$lead_entity;
			}
		}
		echo "----------------------_find_entities_by_email ENDS------------------------\n";
		return array($entity_ids,$entity_objects);
	}
}

?>