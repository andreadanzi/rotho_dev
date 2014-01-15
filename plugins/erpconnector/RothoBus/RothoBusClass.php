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
	var $log_active = false;
	var $add_existing_courses = true;
	var $import_result = Array();
	var $ids_safe_tt_address_array_lead_insert = Array();
	var $ids_tt_address_array_lead_insert = Array();
	var $ids_fe_user_array_lead_insert = Array();
	var $uids_array_fe_users_insert = Array();
	var $uids_array_fe_users_update = Array();
	var $uids_array_fe_users_skipped = Array();
	var $uids_array_tt_address_insert = Array();
	var $uids_array_tt_address_update = Array();
	var $uids_array_tt_address_skipped = Array();
	var $uids_array_safe_tt_address_insert = Array();
	var $uids_array_safe_tt_address_update = Array();
	var $uids_array_safe_tt_address_skipped = Array();
	var $mapping = Array();
	var $group_mapping = Array();
	var $pids_list_corso = Array(308,309,314,315,316,317,328,329,330,331,332,333,377,433,392,440,444,456);
	var $pids_list_download = Array(137,139,141,143,200,205);
	var $pids_list_newsletter = Array(124);
	
	function __construct() { 
		// set mapping between columns of temp tables and vtiger fieldnames
		$this->_set_mappings();
	}
		
	function setLog($log_active) {
		$this->log_active = $log_active;
	}
	
	function setExistingCourses($existing_courses) {
		$this->add_existing_courses = $existing_courses;
	}
	
	// danzi.tn@20131209 Processing results coming from Webform Fiere
	function populateFiere($crmid=0) {
		global $adb,$table_prefix;
		$sql_form_fiere = $this->_get_webform_fiere($crmid);
		// echo "<pre>sql=".$sql_form_fiere."</pre>";
		$wsresult = $adb->query($sql_form_fiere);
		while($form_fiere = $adb->fetchByAssoc($wsresult)) {
			$this->_process_form_fiere($form_fiere);
		}
	}
	
	// danzi.tn@20131209 Processing results coming from Webform Consulenze
	function populateConsulenze() {
		global $adb,$table_prefix;
		$sql_form_consulenze = $this->_get_webform_consulenze();
		$wsresult = $adb->query($sql_form_consulenze);
		while($form_consulenze = $adb->fetchByAssoc($wsresult)) {
			$this->_process_form_consulenze($form_consulenze);
		}
	}
	
	// populates vtiger entities from temp tables
	function populateNow() {
		global $adb,$table_prefix;
		$generated_corso_ids = Array();
		$generated_download_ids = Array();
		$generated_safe_ids = Array();
		$generated_safe_tt_address_ids = Array();
		// associative array for outputting the number of items inserted or updated
		$this->import_result['records_created']=0;
		$this->import_result['records_updated']=0;
		// Processing results coming from Webform Consulenze
		$sql_form_consulenze = $this->_get_webform_consulenze();
		$wsresult = $adb->query($sql_form_consulenze);
		while($form_consulenze = $adb->fetchByAssoc($wsresult)) {
			$this->_process_form_consulenze($form_consulenze);
		}
		// $this->populateConsulenze();
		// Processing results coming from Webform Fiere
		// $this->populateFiere();
		
		// Processing results coming from fe_user (RothoSafe)		
		$sql_fe_users = $this->_get_web_temp_fe_users();
		$wsresult = $adb->query($sql_fe_users);
		while($fe_user = $adb->fetchByAssoc($wsresult)) {
			$this->_process_fe_users($fe_user);
			// generated_ids keeps the identifiers (string) built from the pids of fe_user
			$generated_safe_ids[$fe_user["idtarget"]]=1;
		}
		// Processing results coming from tt_address (Corsi , Downloads)
		// _get_web_temp_tt_address returns the query
		$sql_tt_address = $this->_get_web_temp_tt_address();
		$wsresult = $adb->query($sql_tt_address);
		while($tt_address = $adb->fetchByAssoc($wsresult)) {
			$idtarget = $this->_process_tt_address($tt_address);
			// generated_ids keeps the identifiers (string) built from the pids of fe_user
			if( !empty($idtarget) && $tt_address["leadsource"] == 'Download' ) {
				$generated_download_ids[$idtarget]=1;
			} 
			if(  !empty($idtarget) && $tt_address["leadsource"] == 'Corso' ) {
				$generated_corso_ids[$idtarget]=1;
			}
		}
		$sql_safe_tt_address = $this->_get_web_temp_safe_tt_address();
		$wsresult = $adb->query($sql_safe_tt_address);
		while($safe_tt_address = $adb->fetchByAssoc($wsresult)) {
			$idtarget = $this->_process_safe_tt_address($safe_tt_address);
			// generated_ids keeps the identifiers (string) built from the pids of fe_user
			$generated_safe_tt_address_ids[$safe_tt_address["idtarget"]]=1;
		}
		// Once the Leads are created, missing Campaigns, Targets and relations should be inserted 
		if(count($generated_safe_ids)) {
			$this->_process_campaigns($generated_safe_ids,"Rotho Safe - Web","Attivo",167);
			$this->_process_targets($generated_safe_ids,"Rotho Safe - Web","Pronto",167);
		}
		if(count($generated_download_ids)) {
			$this->_process_campaigns($generated_download_ids,"Download","Attivo",167);
			$this->_process_targets($generated_download_ids,"Download","Pronto",167);
		}
		if(count($generated_corso_ids)) {
			$this->_process_campaigns($generated_corso_ids,"Corso","Pianificato",167);
			$this->_process_targets($generated_corso_ids,"Iscrizione Corso","Pronto",167);
		}
		if(count($generated_safe_tt_address_ids)) {
			$this->_process_campaigns($generated_safe_tt_address_ids,"Newsletter Safe","Attivo",167);
			$this->_process_targets($generated_safe_tt_address_ids,"Newsletter Safe","Pronto",167);
		}
		$generated_ids = array_merge($generated_corso_ids,$generated_safe_ids,$generated_download_ids,$generated_safe_tt_address_ids);
		if(count($generated_ids)) {
			$this->_process_relations($generated_ids);
		}
		$this->_update_web_temp();
		$this->_update_leaddetails();
		return $this->import_result;
	}
	
	// Process requests coming from Webforms (Fiere & Consulenze)
	// form fields are: leadsource
	function checkExistingEntityWebForm($params, $createdtime, $page_title, $ret_id_targets, $entityid) {
		$ret_val = 0;
		$input_email=$parameters['email'];
		$retval=$this->_find_entities_by_email($input_email);
		$entity_ids = $retval[0];
		$entity_objects = $retval[1];
		// VALORIZZARE LE TIPOLOGIE DI ATTIVITA' E DISTINGUERE TRA CONSULENZE E FIERE
		$activitysubject = "Registrazione: ".$params['firstname']." " .$params['lastname']." - ".$params['email'];
		$activitytype = "Registrazione - Safe";
		$activitydescr = "Registrazione ". $page_title ." ".$params['firstname']." " .$params['lastname'];
		$activitydescr .= ", Azienda: ".$params['company'];		
		$activitydescr .= ", Email: ".$params['email'];
		$activitydescr .= ", Indirizzo: ".$params['lane']." ".$params['code']." ".$params['city']; 
		$activitydatetime = $createdtime;
		$retval=$this->_find_entities_by_email($parameters['email']);
		$entity_ids = $retval[0];
		$entity_objects = $retval[1];			
		if(count($entity_ids)==0) { // NOT FOUND, WE HAVE A PROBLEM!!
			$this->import_result['records_created']++;
		} else {
			// FIRST UPDATE EXISTING
			foreach($entity_objects as $crmkey=>$entities) // this will be cycled only once
			{
				foreach($entities as $entitykey=>$entity) {
					if($entityid == $entities->id) continue;
					$entity->mode = 'edit';
					$ret_campaigns = $this->_process_campaigns($ret_id_targets,"Richiesta Consulenze (Form)","Attivo",167);
					$ret_targets = $this->_process_targets($ret_id_targets,"Richiesta Consulenze (Form)","Pronto",167);
					$this->_insert_relations_for_entity($ret_targets,$ret_campaigns,$entity);
					$this->_create_event($entity,$activitysubject,$activitytype,$activitydescr,$activitydatetime);
					$this->import_result['records_updated']++;
				}
			}
		}		
		return $ret_val;
	}
	
	// danzi.tn@20131213 
	function check_web_form($email, $parms, $form_type='Form Fiere') {
		$bFound = false;
		$activitysubject = "Contatto " .$parms['leadsource'].  ": ".$parms['firstname']." " .$parms['lastname']." - ".$parms['email'];
		$activitytype = "Contatto - Fiera";
		$activitydescr = $parms['description'];
		$activitydescr .= "\n********************\nNominativo: ".$parms['firstname']." " .$parms['lastname'];
		$activitydescr .= "\nAzienda: ".$parms['company'];
		$activitydescr .= "\nEmail: ".$parms['email'];
		$activitydescr .= "\nIndirizzo: ".$parms['lane']." ".$parms['city']." ".$parms['state'];
		$retval = $this->_find_entities_by_email($email);
		$entity_ids = $retval[0];
		$entity_objects = $retval[1];
		if(count($entity_ids)==0) { // NOT FOUND
			$bFound = false;
		} else {
			foreach($entity_objects as $crmkey=>$entities) // this will be cycled only once
			{
				foreach($entities as $entitykey=>$entity) {
					$bFound = true;
					$entity->mode = 'edit';
					$idtarget = strtolower (str_replace(' ', '_',$parms['leadsource']));
					$ret_id_targets = array(
						$idtarget => 1
					);
					$ret_campaigns = $this->_process_campaigns($ret_id_targets,$form_type,"Attivo",167);
					$ret_targets = $this->_process_targets($ret_id_targets,$form_type,"Pronto",167);
					$this->_insert_relations_for_entity($ret_targets,$ret_campaigns,$entity);
					$this->_create_event($entity,$activitysubject,$activitytype,$activitydescr,"now");
				}
			}
		}
		return $bFound;
	}
	// danzi.tn@20131213e 
	
	private function _process_form_consulenze($form_consulenze) {
		if($this->log_active) echo "Processing form consulenze uid = ".$form_consulenze['uid']." with email ".$form_consulenze['email'] ."\n";
		if(!filter_var($form_consulenze['email'], FILTER_VALIDATE_EMAIL)) {
			if($this->log_active) echo "Skipping uid = ".$form_consulenze['uid']." due to bad email ".$form_consulenze['email']."\n";
			return;
		}
		$activitysubject = "Richiesta consulenza web: ".$form_consulenze['first_name']." " .$form_consulenze['last_name']." - ".$form_consulenze['email'];
		$activitytype = "Consulenza - Web";
		$activitydescr = $form_consulenze['page_title'] ." ".$form_consulenze['first_name']." " .$form_consulenze['last_name'];
		$activitydescr .= ", Azienda: ".$form_consulenze['company'];
		$activitydescr .= ", Email: ".$form_consulenze['email'];
		$activitydescr .= ", Indirizzo: ".$form_consulenze['address']." ".$form_consulenze['city']." ".$form_consulenze['region'];
		$activitydescr .= ", Richiesta: ". $form_consulenze['description'];
		$activitydatetime = $form_consulenze['insertdate'];
		$retval=$this->_find_entities_by_email($form_consulenze['email']);
		$entity_ids = $retval[0];
		$entity_objects = $retval[1];
		if(count($entity_ids)==0) { // NOT FOUND, we have a problem
			if($this->log_active) echo "We have a problem, missing entity with email = ".$form_consulenze['email']."\n";
		} else {
			// FIRST UPDATE EXISTING EXCEPT THE CURRENT 
			foreach($entity_objects as $crmkey=>$entities) // this will be cycled only once
			{
				if($this->log_active) echo "In entity_objects found key=".$crmkey. " count=".count($entities)." \n";
				foreach($entities as $entitykey=>$entity) {
					if($this->log_active) echo " Add Event to id=". $entity->id. "\n";
					$entity->mode = 'edit';
					$ret_id_targets = array(
						$form_consulenze['idtarget'] => 1
					);
					if($form_consulenze['uid'] == $entity->id) // nel caso del lead originario, bisogna settare cf_747
					{
						global $adb, $table_prefix;
						$sqlupdate = "UPDATE ".$table_prefix."_leadscf SET 
							".$table_prefix."_leadscf.cf_747 = ? ,
							".$table_prefix."_leadscf.cf_726 = ? ,
							".$table_prefix."_leadscf.cf_728 = 'ND' ,
							".$table_prefix."_leadscf.cf_733 = 'ND',
							".$table_prefix."_leadscf.cf_756 = 'ND'
							WHERE ".$table_prefix."_leadscf.cf_808 = 0 AND ".$table_prefix."_leadscf.leadid = ?";
						if($this->log_active) echo " UPDATE leadscf QUERY = ". $sqlupdate."\n";
						$adb->pquery($sqlupdate,array($form_consulenze['idtarget'], $form_consulenze['page_title'],	$form_consulenze['uid']));
					}
					$ret_campaigns = $this->_process_campaigns($ret_id_targets,$form_consulenze['leadsource'],"Attivo",167);
					$ret_targets = $this->_process_targets($ret_id_targets,$form_consulenze['leadsource'],"Pronto",167);
					$this->_insert_relations_for_entity($ret_targets,$ret_campaigns,$entity);
					$this->_create_event($entity,$activitysubject,$activitytype,$activitydescr,$activitydatetime);
					$this->import_result['records_updated']++;
				}
			}
			global $adb, $table_prefix;
			$sqlupdate = "UPDATE ".$table_prefix."_leadscf SET ".$table_prefix."_leadscf.cf_808 = ? WHERE ".$table_prefix."_leadscf.cf_808 = 0 AND ".$table_prefix."_leadscf.leadid = ?";
			if($this->log_active) echo " UPDATE leadscf QUERY = ". $sqlupdate."\n";
			$adb->pquery($sqlupdate,array($form_consulenze['tstamp'], 	$form_consulenze['uid']));
		}
	}
	
	// danzi.tn@20131209 
	private function _process_form_fiere($form_fiere) {
		if($this->log_active) echo "Processing form fiere uid = ".$form_fiere['uid']." with email ".$form_fiere['email'] ."\n";
		if(!filter_var($form_fiere['email'], FILTER_VALIDATE_EMAIL)) {
			if($this->log_active) echo "Skipping uid = ".$form_fiere['uid']." due to bad email ".$form_fiere['email']."\n";
			return;
		}
		$activitysubject = "Contatto " .$form_fiere['leadsource'].  ": ".$form_fiere['first_name']." " .$form_fiere['last_name']." - ".$form_fiere['email'];
		$activitytype = "Contatto - Fiera";
		$activitydescr = $form_fiere['page_title'] ." ".$form_fiere['first_name']." " .$form_fiere['last_name'];
		$activitydescr .= ", Azienda: ".$form_fiere['company'];
		$activitydescr .= ", Email: ".$form_fiere['email'];
		$activitydescr .= ", Indirizzo: ".$form_fiere['address']." ".$form_fiere['city']." ".$form_fiere['region'];
		$activitydescr .= ", Note: ". $form_fiere['description'];
		$activitydatetime = $form_fiere['insertdate'];
		$retval = $this->_find_entities_by_email($form_fiere['email']);
		$entity_ids = $retval[0];
		$entity_objects = $retval[1];
		if(count($entity_ids)==0) { // NOT FOUND, we have a problem
			if($this->log_active) echo "We have a problem, missing entity with email = ".$form_fiere['email']."\n";
		} else {
			// FIRST UPDATE EXISTING EXCEPT THE CURRENT 
			foreach($entity_objects as $crmkey=>$entities) // this will be cycled only once
			{
				if($this->log_active) echo "In entity_objects found key=".$crmkey. " count=".count($entities)." \n";
				foreach($entities as $entitykey=>$entity) {
					if($this->log_active) echo " Add Event to id=". $entity->id. "\n";
					$entity->mode = 'edit';
					$idtarget = $form_fiere['tmp_idtarget'];
					$ret_id_targets = array(
						$idtarget => 1
					);
					if($form_fiere['uid'] == $entity->id) // nel caso del lead originario, bisogna settare cf_747
					{
						global $adb, $table_prefix;
						$sqlupdate = "UPDATE ".$table_prefix."_leadscf SET 
							".$table_prefix."_leadscf.cf_747 = ? ,
							".$table_prefix."_leadscf.cf_726 = ? ,
							".$table_prefix."_leadscf.cf_728 = 'ND' ,
							".$table_prefix."_leadscf.cf_733 = 'ND',
							".$table_prefix."_leadscf.cf_756 = 'ND'
							WHERE ".$table_prefix."_leadscf.cf_808 = 0 AND ".$table_prefix."_leadscf.leadid = ?";
						if($this->log_active) echo " UPDATE leadscf QUERY = ". $sqlupdate."\n";
						$adb->pquery($sqlupdate,array($idtarget, $form_fiere['page_title'],	$form_fiere['uid']));
					}
					$ret_campaigns = $this->_process_campaigns($ret_id_targets,'Form Fiere',"Attivo",167);
					$ret_targets = $this->_process_targets($ret_id_targets,'Form Fiere',"Pronto",167);
					$this->_insert_relations_for_entity($ret_targets,$ret_campaigns,$entity);
					$this->_create_event($entity,$activitysubject,$activitytype,$activitydescr,$activitydatetime);
					$this->import_result['records_updated']++;
				}
			}
			global $adb, $table_prefix;
			$sqlupdate = "UPDATE ".$table_prefix."_leadscf SET ".$table_prefix."_leadscf.cf_808 = ? WHERE ".$table_prefix."_leadscf.cf_808 = 0 AND ".$table_prefix."_leadscf.leadid = ?";
			if($this->log_active) echo " UPDATE leadscf QUERY = ". $sqlupdate."\n";
			$adb->pquery($sqlupdate,array($form_fiere['tstamp'], 	$form_fiere['uid']));
		}
	}
	
	// process the row coming from fe_users table
	private function _process_fe_users($fe_user) {
		if($this->log_active) echo "Processing uid = ".$fe_user['uid']." with email ".$fe_user['email'] ."\n";
		if(!filter_var($fe_user['email'], FILTER_VALIDATE_EMAIL)) {
			if($this->log_active) echo "Skipping uid = ".$fe_user['uid']." due to bad email ".$fe_user['email']."\n";
			$this->uids_array_fe_users_skipped[]=$fe_user['uid'];
			return;
		}
		foreach($this->group_mapping as $key=>$value) {
			$usergroup_array = explode(",",$fe_user['usergroup']);
			if( in_array($key,$usergroup_array) ) {
				if($this->log_active) echo "The string '$key' was found in the string '".$fe_user['usergroup']."'\n";
				$fe_user['cf_761'] = $value;
			}
		}
		$activitysubject = "Registrazione: ".$fe_user['first_name']." " .$fe_user['last_name']." - ".$fe_user['email'];
		$activitytype = "Registrazione - Safe";
		$activitydescr = "Registrazione ". $fe_user['page_title'] ." ".$fe_user['first_name']." " .$fe_user['last_name'];
		$activitydescr .= ", Azienda: ".$fe_user['company'];
		$fe_user['descr'] = $activitydescr . " , Categoria: " .$fe_user['usergroup_descr'];
		$activitydescr .= ", Email: ".$fe_user['email'];
		$activitydescr .= ", Indirizzo: ".$fe_user['address']." ".$fe_user['city']." ".$fe_user['region'];
		$activitydescr .= ", Categoria: ".$fe_user['usergroup_descr'];
		$activitydatetime = $fe_user['insertdate'];
		$fe_user['formula'] = "ND";
		$fe_user['codfatt'] = "ND";
		$fe_user['costo'] = "ND";
		$fe_user['date'] = "ND";
		$retval=$this->_find_entities_by_email($fe_user['email']);
		$entity_ids = $retval[0];
		$entity_objects = $retval[1];			
		if(count($entity_ids)==0) { // NOT FOUND
			if($this->log_active) echo "Trying inserting Lead with email = ".$fe_user['email']."\n";
			// INSERT LEAD AND ADD CALENDAR ENTRY
			$newLead = CRMEntity::getInstance('Leads');
			vtlib_setup_modulevars('Leads',$newLead);
			// MAPPING
			foreach($this->mapping['Leads'] as $key=>$value) {
				$newLead->column_fields[$key] = $fe_user[$value];
				if($this->log_active) echo "Setting ".$key."=".$fe_user[$value] . " \n";
			}
			$newLead->save($module_name='Leads',$longdesc=false);
			$this->ids_fe_user_array_lead_insert[] = array('id'=> $newLead->id , 'uid'=>$fe_user['uid']);
			$this->_create_event($newLead,$activitysubject,$activitytype,$activitydescr,$activitydatetime);
			$this->import_result['records_created']++;
			$this->uids_array_fe_users_insert[]=$fe_user['uid'];
		} else {
			// FIRST UPDATE EXISTING
			foreach($entity_objects as $crmkey=>$entities) // this will be cycled only once
			{
				if($this->log_active) echo "In entity_objects found key=".$crmkey. " count=".count($entities)." \n";
				foreach($entities as $entitykey=>$entity) {
					if($this->log_active) echo " Add Event to id=". $entity->id. "\n";
					$entity->mode = 'edit';
					$ret_id_targets = array(
						$fe_user['idtarget'] => 1
					);
					$ret_campaigns = $this->_process_campaigns($ret_id_targets,"Rotho Safe - Web","Attivo",167);
					$ret_targets = $this->_process_targets($ret_id_targets,"Rotho Safe - Web","Pronto",167);
					$this->_insert_relations_for_entity($ret_targets,$ret_campaigns,$entity);
					$this->_create_event($entity,$activitysubject,$activitytype,$activitydescr,$activitydatetime);
					$this->import_result['records_updated']++;
				}
			}
			$this->uids_array_fe_users_update[]=$fe_user['uid'];
		}
		// QUI PROCESS TARGETS E CAMPAGNE
	}
	
	
	private function _insert_relations_for_entity($ret_targets,$ret_campaigns,$entity) {
		global $table_prefix, $adb;
		if (is_array($ret_targets))
		{
			foreach( $ret_targets as $ret_target )
			{
				$sqlToCheck = "select crmid, relcrmid from ".$table_prefix."_crmentityrel where crmid=? and relcrmid=?";
				$resRel = $adb->pquery($sqlToCheck,array($ret_target->id,$entity->id));
				if( $adb->num_rows($resRel) == 0)
				{
					$sqlToInsert = "INSERT INTO ".$table_prefix."_crmentityrel 
									(crmid,module,relcrmid,relmodule) 
									VALUES
									(".$ret_target->id.",'Targets',".$entity->id.",'".$entity->column_fields['record_module']."')";
					$adb->query($sqlToInsert);
				}
			}
		}
		if (is_array($ret_campaigns))
		{
			foreach( $ret_campaigns as $ret_campaign )
			{
				$sqlToCheck = "select crmid, relcrmid from ".$table_prefix."_crmentityrel where crmid=? and relcrmid=?";
				$resRel = $adb->pquery($sqlToCheck,array($ret_campaign->id,$entity->id));
				if( $adb->num_rows($resRel) == 0)
				{
					$sqlToInsert = "INSERT INTO ".$table_prefix."_crmentityrel 
									(crmid,module,relcrmid,relmodule) 
									VALUES
									(".$ret_campaign->id.",'Campaigns',".$entity->id.",'".$entity->column_fields['record_module']."')";
					$adb->query($sqlToInsert);
				}
			}
		}
		$targetstocampaigns = "INSERT INTO ".$table_prefix."_crmentityrel
								(crmid,module,relcrmid,relmodule)
								select
								distinct
								".$table_prefix."_crmentityrel.crmid as crmid,
								'Targets' as module,
								rel".$table_prefix."_crmentityrel.crmid as relcrmid,
								'Campaigns' as relmodule
								from ".$table_prefix."_crmentityrel
								join ".$table_prefix."_crmentityrel as rel".$table_prefix."_crmentityrel ON rel".$table_prefix."_crmentityrel.relcrmid = ".$table_prefix."_crmentityrel.relcrmid
								join ".$table_prefix."_targets on ".$table_prefix."_targets.targetsid = ".$table_prefix."_crmentityrel.crmid
								join ".$table_prefix."_campaign on ".$table_prefix."_campaign.campaignid = rel".$table_prefix."_crmentityrel.crmid
								join ".$table_prefix."_campaignscf on ".$table_prefix."_campaignscf.campaignid = rel".$table_prefix."_crmentityrel.crmid
								join ".$table_prefix."_targetscf on ".$table_prefix."_targetscf.targetsid = ".$table_prefix."_crmentityrel.crmid
								left join ".$table_prefix."_crmentityrel as compare_crmentityrel on compare_crmentityrel.crmid = ".$table_prefix."_crmentityrel.crmid and compare_crmentityrel.relcrmid = rel".$table_prefix."_crmentityrel.crmid
								WHERE
								".$table_prefix."_crmentityrel.module = 'Targets'
								AND rel".$table_prefix."_crmentityrel.module = 'Campaigns'
								AND ".$table_prefix."_campaignscf.cf_742 IS NOT NULL
								AND ".$table_prefix."_campaignscf.cf_742 <>''
								AND ".$table_prefix."_campaignscf.cf_742 = ".$table_prefix."_targetscf.cf_1006
								AND compare_crmentityrel.crmid is null";
		$adb->query($targetstocampaigns);
	}
	
	// process the row coming from tt_address table
	private function _process_tt_address($tt_address) {
		$ret_id_target = "";
		if($this->log_active) echo "Processing uid = ".$tt_address['uid']." with email ".$tt_address['email'] ."\n";
		if(!filter_var($tt_address['email'], FILTER_VALIDATE_EMAIL)) {
			if($this->log_active) echo "Skipping uid = ".$tt_address['uid']." due to bad email ".$tt_address['email']."\n";
			$this->uids_array_tt_address_skipped[]=$tt_address['uid'];
			return $ret_id_target;
		}
		$tt_description = $tt_address['description'];
		$order   = array("\r\n", "\n", "\r");
		$tt_descr = str_replace($order,'|',$tt_description);
		$arr = explode('|',$tt_descr);
		$tmp_page_title = $tt_address['tmp_page_title'];
		$idtarget = $tt_address['tmp_idtarget'];
		$formula = "--Nessuno--";
		if((count($arr)>0 && in_array(substr($arr[0],3),$this->pids_list_download)) || in_array($tt_address['pid'],$this->pids_list_download) ) {
			foreach( $arr as $item) {
				if($this->log_active) echo "\nDownload item =".$item."\n";
				/* GESTIRE DOWNLOAD
				id 139
				richiesta Software download rothofixing
				software Solai collaboranti legno-cemento
				0 id 139
				1 richiesta Software download rothofixing EN
				2 software Solai collaboranti legno-cemento
				*/
				if( substr($item,0,2) == "id" ) {
					$pid_download = substr($item,3);
					if($this->log_active) echo "pid_download item =".$pid_download."\n";
				}
				if( substr($item,0,9) == "richiesta" ) {
					$richiesta_download = substr($item,10);
					if($this->log_active) echo "richiesta_download item =".$richiesta_download."\n";
				}
				if( substr($item,0,8) == "software" ) {
					$software_download = substr($item,9);
					if($this->log_active) echo "software_download item =".$software_download."\n";
					$tt_address['page_title'] = $tmp_page_title . " " . $software_download;
					$ret_id_target = $idtarget.$software_download;
					if($this->log_active) echo "Download ret_id_target = ".$ret_id_target."\n";
					$tt_address['idtarget'] = strtolower($ret_id_target);
				}
			}
			if(empty($tt_address['page_title']) ) $tt_address['page_title'] = $tmp_page_title;
			if(empty($ret_id_target)) {
				$ret_id_target = strtolower($idtarget.$tmp_page_title);
				$tt_address['idtarget'] = strtolower($ret_id_target);
			}
			$tt_address['formula'] = $formula;
			$tt_address['codfatt'] = "ND";
			$tt_address['costo'] = "ND";
			$tt_address['date'] = "ND";
			$tt_address['descr'] = $tt_address['idtarget'] . " date: ". $tt_address['insertdate'] . " uid=" .$tt_address['uid']." pid=".$tt_address['pid'];
			$activitysubject = "Download " .$software_download ." per ".$tt_address['first_name']." " .$tt_address['last_name']." - ".$tt_address['email'];
			$activitytype = "Download - Web";
			$activitydescr = $tt_address['descr'] . " per ".$tt_address['first_name']." " .$tt_address['last_name']." - ".$tt_address['email'];
		}
		if((count($arr)>0 && in_array(substr($arr[0],3),$this->pids_list_corso)) || in_array($tt_address['pid'],$this->pids_list_corso) ) {
			$iban = "No Iban";
			foreach( $arr as $item ) {
				if($this->log_active) echo "\nCourse item =".$item."\n";
				/* GESTIRE CORSI
				0 id 330
				1 vat 03367940172
				2 tax BTTCRL69C28B157X
				3 companyphone 030/985192
				4 companymail info@ingegneriabettoni.it
				5 date 20 nov 2013
				6 formula IT Solo corso / costo 250,00
				7 code RFCACN
				*/
				if( substr($item,0,2) == "id" )
					$pid_corso = trim(substr($item,3));
				if( substr($item,0,4) == "iban" )
					$iban = trim(substr($item,5));
				if( substr($item,0,3) == "vat" )
					$vat_corso = trim(substr($item,4));
				if( substr($item,0,3) == "tax" )
					$tax_corso = trim(substr($item,4));
				if( substr($item,0,12) == "companyphone" )
					$companyphone_corso = trim(substr($item,13));
				if( substr($item,0,11) == "companymail" )
					$companymail_corso = trim(substr($item,12));
				if( substr($item,0,4) == "date" )
					$date_corso = trim(substr($item,5));
				if( substr($item,0,7) == "formula" ) {
					$formula_corso = trim(substr($item,8));
					$costo_corso = substr($formula_corso,strrpos($formula_corso,'costo ')+6);
					if( preg_match("/\b(corso|kurs|curso|incontro formativo)\b/i",$formula_corso) )	{
						$formula = "Corso";
					}
					if( preg_match("/\b(cena|abendessen|cenas)\b/i",$formula_corso) )	{
						$formula .= " + cena";
					}
					if( preg_match("/\b(2 cene|2 abendessen|2 cenas)\b/i",$formula_corso) )	{
						$formula .= " + 2 cene";
					}
					if( preg_match("/\b(3 cene|3 abendessen|3 cenas)\b/i",$formula_corso) )	{
						$formula .= " + 3 cene";
					}
					// danzi.tn@20131029 gestione errore web form con 2 pernotto
					if( preg_match("/\b(3 pernottamenti|3 pernotto|3 pernotti|3 noches|3 übernachtungen)\b/i",$formula_corso) )	{
						$formula .= " + 3 pernottamenti";
					} elseif( preg_match("/\b(2 pernottamenti|2 pernotto|2 pernotti|2 noches|2 übernachtungen)\b/i",$formula_corso) )	{
						$formula .= " + 2 pernottamenti";
					} elseif( preg_match("/\b(pernottamento|pernotto|übernachtung|noches)\b/i",$formula_corso) )	{
						$formula .= " + pernottamento";
					}
					// danzi.tn@20131029 e
				}
				if( substr($item,0,4) == "code" )
					$code_corso = trim(substr($item,5));
			}
			$idtarget = $tt_address['tmp_idtarget'];	
			$tmp_page_title = $tt_address['tmp_page_title'];
			$tt_address['page_title'] = $tmp_page_title . " del " . $date_corso;
			$tt_address['codfatt'] = $code_corso;
			$tt_address['costo'] = $costo_corso;
			$tt_address['date'] = $date_corso;
			$tt_address['companymail'] = $companymail_corso;
			$tt_address['iban'] = $iban;
			$tt_address['agencyphone'] = $companyphone_corso;
			$tt_address['vat'] = $vat_corso;
			$tt_address['formula'] = $formula;
			$tt_address['taxid'] = $tax_corso;
			$ret_id_target = $idtarget.$tt_address['date'];
			if($this->log_active) echo "Course ret_id_target = ".$ret_id_target."\n";
			$tt_address['idtarget'] = strtolower($ret_id_target);
			$tt_address['descr'] = $tt_address['idtarget'] . " date: ". $tt_address['insertdate'] . " uid=" .$tt_address['uid']." pid=".$tt_address['pid'];
			$activitysubject = "Corso " .$tt_address['page_title']. " per ".$tt_address['first_name']." " .$tt_address['last_name']." - ".$tt_address['email'];
			$activitytype = "Iscrizione Corso - Web";
			$activitydescr = $activitysubject . " - " . $code_corso . " | " . $tt_address['descr'];
		}
		$activitydatetime = $tt_address['insertdate'];
		$retval=$this->_find_entities_by_email($tt_address['email']);
		$entity_ids = $retval[0];
		$entity_objects = $retval[1];			
		if(count($entity_ids)==0) { // NOT FOUND
			if($this->log_active) echo "Trying inserting Lead with email = ".$tt_address['email']."\n";
			// INSERT LEAD AND ADD CALENDAR ENTRY
			$newLead = CRMEntity::getInstance('Leads');
			vtlib_setup_modulevars('Leads',$newLead);
			// MAPPING
			foreach($this->mapping['Leads'] as $key=>$value) {
				$newLead->column_fields[$key] = $tt_address[$value];
				if($this->log_active) echo "Setting lead(1) field ".$key."=".$tt_address[$value] . " \n";
			}
			$newLead->save($module_name='Leads',$longdesc=false);
			$this->ids_tt_address_array_lead_insert[] = array('id'=> $newLead->id , 'uid'=>$tt_address['uid']);
			$this->_create_event($newLead,$activitysubject,$activitytype,$activitydescr,$activitydatetime);
			$this->import_result['records_created']++;
			$this->uids_array_tt_address_insert[]=$tt_address['uid'];
		} else {
			// FIRST UPDATE EXISTING
			foreach($entity_objects as $crmkey=>$entities) // this will be cycled only once
			{
				if($this->log_active) echo "In entity_objects found key=".$crmkey. " count=".count($entities)." \n";
				foreach($entities as $entitykey=>$entity) {
					if($this->log_active) echo " Add Event(update) to id=". $entity->id ."\n";
					$entity->mode = 'edit';
					$ret_id_targets = array(
						$ret_id_target => 1
					);
					if( $tt_address["leadsource"] == 'Download' ) {
						$ret_campaigns = $this->_process_campaigns($ret_id_targets,"Download","Attivo",167);
						$ret_targets = $this->_process_targets($ret_id_targets,"Download","Pronto",167);
					} 
					/* da email del 20130807 non attaccare i target e le campagne alle aziende/contatti esistenti - mettere solo le attività
					if( $tt_address["leadsource"] == 'Corso' ) {
						$ret_campaigns = $this->_process_campaigns($ret_id_targets,"Corso","Pianificato",167);
						$ret_targets = $this->_process_targets($ret_id_targets,"Iscrizione Corso","Pronto",167);
					} */
					$this->_insert_relations_for_entity($ret_targets,$ret_campaigns,$entity);
					$this->_create_event($entity,$activitysubject,$activitytype,$activitydescr,$activitydatetime);
					$this->import_result['records_updated']++;
				}
			}
			// THE INSERT LEAD IF CORSI
			if(count($arr)>0 && in_array(substr($arr[0],3),$this->pids_list_corso) && $this->add_existing_courses  ) {
				$newLead = CRMEntity::getInstance('Leads');
				vtlib_setup_modulevars('Leads',$newLead);
				// MAPPING
				foreach($this->mapping['Leads'] as $key=>$value) {
					$newLead->column_fields[$key] = $tt_address[$value];
					if($this->log_active) echo "Setting lead(2) field ".$key."=".$tt_address[$value] . " \n";
				}
				$newLead->save($module_name='Leads',$longdesc=false);
				$this->ids_tt_address_array_lead_insert[] = array('id'=> $newLead->id , 'uid'=>$tt_address['uid']);
				$this->_create_event($newLead,$activitysubject,$activitytype,$activitydescr,$activitydatetime);
				$this->import_result['records_created']++;
				$this->uids_array_tt_address_insert[]=$tt_address['uid'];
			} else {
				$this->uids_array_tt_address_update[]=$tt_address['uid'];
			}
		}
		// QUI PROCESS TARGETS E CAMPAGNE
		return $ret_id_target;
	}
		
	// process the row coming from tt_address table
	private function _process_safe_tt_address($tt_address) {
		$ret_id_target = "";
		if($this->log_active) echo "Processing uid = ".$tt_address['uid']." with email ".$tt_address['email'] ."\n";
		if(!filter_var($tt_address['email'], FILTER_VALIDATE_EMAIL)) {
			if($this->log_active) echo "Skipping uid = ".$tt_address['uid']." due to bad email ".$tt_address['email']."\n";
			$this->uids_array_safe_tt_address_skipped[]=$tt_address['uid'];
			return $ret_id_target;
		}		
		if( in_array($tt_address['pid'],$this->pids_list_newsletter) ) {
			$tmp_page_title = $tt_address['tmp_page_title'];
			$tt_address['page_title'] = $tmp_page_title;
			$tt_address['descr'] = $tt_address['idtarget'] . " date: ". $tt_address['insertdate'] . " uid=" .$tt_address['uid']." pid=".$tt_address['pid'];
			$activitysubject = "Download " .$software_download ." per ".$tt_address['first_name']." " .$tt_address['last_name']." - ".$tt_address['email'];
			$activitytype = "Download - Web";
			$activitydescr = $tt_address['descr'] . " per ".$tt_address['first_name']." " .$tt_address['last_name']." - ".$tt_address['email'];
			$ret_id_target = $tt_address['idtarget'];
		}	
		$tt_address['formula'] = "ND";
		$tt_address['codfatt'] = "ND";
		$tt_address['costo'] = "ND";
		$tt_address['date'] = "ND";
		$activitydatetime = $tt_address['insertdate'];
		$retval=$this->_find_entities_by_email($tt_address['email']);
		$entity_ids = $retval[0];
		$entity_objects = $retval[1];			
		if(count($entity_ids)==0) { // NOT FOUND
			if($this->log_active) echo "Trying inserting Lead with email = ".$tt_address['email']."\n";
			// INSERT LEAD AND ADD CALENDAR ENTRY
			$newLead = CRMEntity::getInstance('Leads');
			vtlib_setup_modulevars('Leads',$newLead);
			// MAPPING
			foreach($this->mapping['Leads'] as $key=>$value) {
				$newLead->column_fields[$key] = $tt_address[$value];
				if($this->log_active) echo "Setting lead(1) field ".$key."=".$tt_address[$value] . " \n";
			}
			$newLead->save($module_name='Leads',$longdesc=false);
			$this->ids_safe_tt_address_array_lead_insert[] = array('id'=> $newLead->id , 'uid' => $tt_address['uid']);
			$this->_create_event($newLead,$activitysubject,$activitytype,$activitydescr,$activitydatetime);
			$this->import_result['records_created']++;
			$this->uids_array_safe_tt_address_insert[]=$tt_address['uid'];
		} else {
			// FIRST UPDATE EXISTING
			foreach($entity_objects as $crmkey=>$entities) // this will be cycled only once
			{
				if($this->log_active) echo "In entity_objects found key=".$crmkey. " count=".count($entities)." \n";
				foreach($entities as $entitykey=>$entity) {
					if($this->log_active) echo " Add Event(update) to id=". $entity->id ."\n";
					$entity->mode = 'edit';
					$ret_id_targets = array(
						$ret_id_target => 1
					);
					/* da email del 20130807 non attaccare i target e campagne alle newsetter rothosafe
					if( $tt_address["leadsource"] == 'Newsletter Safe' ) {
						$ret_campaigns = $this->_process_campaigns($ret_id_targets,"Newsletter Safe","Attivo",167);
						$ret_targets = $this->_process_targets($ret_id_targets,"Newsletter Safe","Pronto",167);
					}
					$this->_insert_relations_for_entity($ret_targets,$ret_campaigns,$entity); */
					$this->_create_event($entity,$activitysubject,$activitytype,$activitydescr,$activitydatetime);
					$this->import_result['records_updated']++;
				}
			}
			// THE INSERT LEAD IF NEWSLETTER
			if( in_array($tt_address['pid'],$this->pids_list_newsletter) ) {
				$newLead = CRMEntity::getInstance('Leads');
				vtlib_setup_modulevars('Leads',$newLead);
				// MAPPING
				foreach($this->mapping['Leads'] as $key=>$value) {
					$newLead->column_fields[$key] = $tt_address[$value];
					if($this->log_active) echo "Setting lead(2) field ".$key."=".$tt_address[$value] . " \n";
				}
				$newLead->save($module_name='Leads',$longdesc=false);
				$this->ids_safe_tt_address_array_lead_insert[] = array('id'=> $newLead->id , 'uid'=>$tt_address['uid']);
				$this->_create_event($newLead,$activitysubject,$activitytype,$activitydescr,$activitydatetime);
				$this->import_result['records_created']++;
				$this->uids_array_safe_tt_address_insert[]=$tt_address['uid'];
			} else {
				$this->uids_array_safe_tt_address_update[]=$tt_address['uid'];
			}
		}
		// QUI PROCESS TARGETS E CAMPAGNE
		return $ret_id_target;
	}
	
	/*
	Creates the event for the specified entity
	
	$insp_activitytype 
		Contatto - Fiera
		Consulenza - Web
		Iscrizione Corso - Web
		Download - Web
		Registrazione - Safe
	*/
	private function _create_event($focus,$activitysubject,$activitytype,$activitydescr,$activitydatetime)  {
		if($this->log_active) echo "creating event for id=".$focus->id." with activitysubject = ". $activitysubject ."\n";
		$acttime = strtotime($activitydatetime);
		$acttime = ceil($acttime/300)*300;
		$newEvent = CRMEntity::getInstance('Events');
		vtlib_setup_modulevars('Events',$newEvent);
		$newEvent->column_fields['subject'] = $activitysubject;
		$newEvent->column_fields['smownerid'] = $focus->column_fields["assigned_user_id"];
		$newEvent->column_fields['assigned_user_id'] = $focus->column_fields["assigned_user_id"];
		$newEvent->column_fields['createdtime'] = $focus->column_fields["modifiedtime"];
		$newEvent->column_fields['modifiedtime'] = $focus->column_fields["modifiedtime"];
		if($focus instanceof Contacts) {
			$newEvent->column_fields['parent_id'] = $focus->column_fields["account_id"];
			$newEvent->column_fields['contact_id'] = $focus->id;
		} else {
			$newEvent->column_fields['parent_id'] = $focus->id;
		}
		$newEvent->column_fields['date_start'] = date('Y-m-d',$acttime);// 2013-05-27 
		$newEvent->column_fields['time_start'] = '00:00';
		$newEvent->column_fields['due_date'] =  date('Y-m-d',$acttime); // 2013-05-27
		$newEvent->column_fields['time_end'] = '00:00';// 15:55
		$newEvent->column_fields['duration_hours'] = 23;// 2
		$newEvent->column_fields['duration_minutes'] = 59;// 2
		$newEvent->column_fields['activitytype'] = $activitytype; //$insp_activitytype;
		$newEvent->column_fields['is_all_day_event'] = 1;
		$newEvent->column_fields['eventstatus'] = 'Held';// $insp_eventstatus 
		$newEvent->column_fields['description'] = $activitydescr;
		$newEvent->save($module_name='Events',$longdesc=false);
		if($this->log_active) echo "created event with id=".$newEvent->id."\n";
	}
	
	
	// provides the sql query string for retrieving data from web_temp_tt_address
	private function _get_web_temp_tt_address() {
		$wsquery = "select uid, pid, name, first_name, last_name, email, phone, mobile, www, address, company, city, zip, region, country, description, fax,  'web' as type 
					, CASE WHEN pid in (".implode(", ",$this->pids_list_corso).") THEN 'Corso' WHEN pid in (".implode(", ",$this->pids_list_download).") THEN 'Download' END as leadsource
					, CASE WHEN pid in (".implode(", ",$this->pids_list_corso).") THEN 'Qualified' WHEN pid in (".implode(", ",$this->pids_list_download).") THEN 'Held' END as leadstatus
					, '167' as assigned_user_id
					, insertdate 
					, STR(pid) + '_' as tmp_idtarget
					, tstamp
					, pagetitle as tmp_page_title
					, 'ND' as location 
					, room  as codfatt
					, '1' AS cf_807 
					, 'Web' as cf_757 
					, 'ND' as cf_737 
					, web_temp_tt_address.title as title
					from 
					web_temp_tt_address 
					where imported is NULL and email <>'' 
					AND email IS NOT NULL
					AND pid in (".implode(", ",array_merge($this->pids_list_corso,$this->pids_list_download)).")";
		return $wsquery;
	}
	
	private function _get_web_temp_safe_tt_address() {
			$wsquery = "select uid, pid, name, first_name, last_name, email, phone, mobile, www, address, company, city, zip, region, country, description, fax,  'web' as type 
					, CASE WHEN pid in (".implode(", ",$this->pids_list_newsletter).") THEN 'Newsletter Safe' ELSE 'ND' END as leadsource
					, CASE WHEN pid in (".implode(", ",$this->pids_list_newsletter).") THEN 'Qualified' ELSE 'ND' END as leadstatus
					, '167' as assigned_user_id
					, insertdate 
					, STR(pid) + '_' + pagetitle as idtarget
					, tstamp
					, pagetitle as tmp_page_title
					, 'ND' as location 
					, room  as codfatt
					, '1' AS cf_807 
					, 'Web' as cf_757 
					, 'ND' as cf_737 
					, '' as usergroup
					, '' as usergroup_descr 
					, web_temp_safe_tt_address.title as title
					from 
					web_temp_safe_tt_address 
					where imported is NULL and email <>'' 
					AND email IS NOT NULL
					AND pid in (".implode(", ",$this->pids_list_newsletter).")";
		return $wsquery;
	}
	
	// updates web_temp_tt_address rows that were previously imported
	private function _update_web_temp_tt_address($uids_array, $val) {
		global $adb,$table_prefix;
		$wsquery = "UPDATE  
					web_temp_tt_address 
					SET imported = ".$val." 
					WHERE imported is NULL and uid in (".implode(", ",$uids_array).")";
		$adb->query($wsquery);
	}
	
	private function _update_web_temp_safe_tt_address($uids_array, $val) {
		global $adb,$table_prefix;
		$wsquery = "UPDATE  
					web_temp_safe_tt_address 
					SET imported = ".$val." 
					WHERE imported is NULL and uid in (".implode(", ",$uids_array).")";
		$adb->query($wsquery);
	}
	
	private function _get_webform_consulenze() {
		global $table_prefix;
		$wsquery = "select 
					DATEDIFF(s, '1970-01-01 00:00:00', ".$table_prefix."_crmentity.createdtime ) as tstamp,
					".$table_prefix."_leaddetails.leadid as uid,
					".$table_prefix."_leaddetails.firstname + '  ' + ".$table_prefix."_leaddetails.lastname as name,
					".$table_prefix."_leaddetails.firstname as first_name,
					".$table_prefix."_leaddetails.lastname as last_name,
					".$table_prefix."_leaddetails.email,
					".$table_prefix."_leadaddress.phone,
					".$table_prefix."_leadaddress.mobile,
					".$table_prefix."_leadsubdetails.website as www,
					".$table_prefix."_leadaddress.lane as address,
					".$table_prefix."_leaddetails.company,
					".$table_prefix."_leadaddress.city,
					".$table_prefix."_leadaddress.code as zip,
					".$table_prefix."_leadaddress.state as region,
					".$table_prefix."_leadaddress.country,
					".$table_prefix."_crmentity.description,
					".$table_prefix."_leadaddress.fax,
					'Webform' as type 
					, 'Richiesta Consulenze (Form)' AS leadsource 
					, 'Held' as leadstatus
					, '167' as assigned_user_id
					, ".$table_prefix."_crmentity.createdtime as  insertdate 
					, 'form_consulenza_web' as idtarget
					, 'Formulario richiesta consulenza: Soluzioni per strutture in legno - rothoblaas' as page_title 
					, 'ND' as location 
					, 'ND' as codfatt 
					, '1' AS cf_807 
					, 'Web' as cf_757 
					, 'ND' as cf_737
					, ".$table_prefix."_leadscf.cf_758 as title 
					from ".$table_prefix."_leaddetails
					join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_leaddetails.leadid and ".$table_prefix."_crmentity.deleted =0 AND ".$table_prefix."_leaddetails.converted = 0 
					join ".$table_prefix."_leadscf on ".$table_prefix."_leadscf.leadid = ".$table_prefix."_leaddetails.leadid
					join ".$table_prefix."_leadaddress on ".$table_prefix."_leadaddress.leadaddressid = ".$table_prefix."_leaddetails.leadid
					join ".$table_prefix."_leadsubdetails on ".$table_prefix."_leadsubdetails.leadsubscriptionid = ".$table_prefix."_leaddetails.leadid
					WHERE ".$table_prefix."_leaddetails.leadsource = 'Richiesta Consulenze (Form)'
					AND ".$table_prefix."_leadscf.cf_808 = 0";
		return $wsquery;
	}
	
	// danzi.tn@20131209 
	private function _get_webform_fiere($crmid=0) {
		global $table_prefix;
		$wsquery = "select 
					DATEDIFF(s, '1970-01-01 00:00:00', ".$table_prefix."_crmentity.createdtime ) as tstamp,
					".$table_prefix."_leaddetails.leadid as uid,
					".$table_prefix."_leaddetails.firstname + '  ' + ".$table_prefix."_leaddetails.lastname as name,
					".$table_prefix."_leaddetails.firstname as first_name,
					".$table_prefix."_leaddetails.lastname as last_name,
					".$table_prefix."_leaddetails.email,
					".$table_prefix."_leadaddress.phone,
					".$table_prefix."_leadaddress.mobile,
					".$table_prefix."_leadsubdetails.website as www,
					".$table_prefix."_leadaddress.lane as address,
					".$table_prefix."_leaddetails.company,
					".$table_prefix."_leadaddress.city,
					".$table_prefix."_leadaddress.code as zip,
					".$table_prefix."_leadaddress.state as region,
					".$table_prefix."_leadaddress.country,
					".$table_prefix."_crmentity.description,
					".$table_prefix."_leadaddress.fax,
					'Webform Fiere' as type 
					, ".$table_prefix."_leaddetails.leadsource 
					, 'Held' as leadstatus
					, '167' as assigned_user_id
					, ".$table_prefix."_crmentity.createdtime as  insertdate 
					, LOWER(REPLACE(".$table_prefix."_leaddetails.leadsource,' ','_') ) as tmp_idtarget
					, 'Formulario contatti Fiere:' + ".$table_prefix."_leaddetails.leadsource as page_title 
					, 'ND' as location 
					, 'ND' as codfatt 
					, '1' AS cf_807 
					, 'Web' as cf_757  -- Origine Iscrizione
					, 'ND' as cf_737
					, ".$table_prefix."_leadscf.cf_758 as title 
					from ".$table_prefix."_leaddetails
					join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_leaddetails.leadid and ".$table_prefix."_crmentity.deleted =0 AND ".$table_prefix."_leaddetails.converted = 0 
					join ".$table_prefix."_leadscf on ".$table_prefix."_leadscf.leadid = ".$table_prefix."_leaddetails.leadid
					join ".$table_prefix."_leadaddress on ".$table_prefix."_leadaddress.leadaddressid = ".$table_prefix."_leaddetails.leadid
					join ".$table_prefix."_leadsubdetails on ".$table_prefix."_leadsubdetails.leadsubscriptionid = ".$table_prefix."_leaddetails.leadid
					WHERE ".$table_prefix."_leaddetails.leadsource like 'Fiera%'
					AND ".$table_prefix."_leadscf.cf_808 = 0
					" .($crmid > 0? " AND ".$table_prefix."_crmentity.crmid =".$crmid: "");
		return $wsquery;
	}
	
	
	// provides the sql query string for retrieving data from web_temp_fe_users
	private function _get_web_temp_fe_users() {
		$wsquery = "select uid, pid, name, first_name, last_name, email, phone, mobile, www, address, company, city, zip, region, country, description, fax,  'safe' as type 
					, 'RothoSafe' AS leadsource 
					, 'Held' as leadstatus
					, '167' as assigned_user_id
					, insertdate 
					, STR(pid) + '_safe' as idtarget
					, tstamp
					, pagetitle as tmp_page_title 
					, 'ND' as location 
					, 'ND' as codfatt 
					, '1' AS cf_807 
					, 'Web' as cf_757 
					, 'ND' as cf_737 
					, usergroup 
					, usergroup_descr 
					, web_temp_fe_users.title as title
					from 
					web_temp_fe_users 
					where imported is NULL and email <>'' and email IS NOT NULL";
		return $wsquery;
	}
	
	// updates web_temp_fe_users rows that were previously imported 
	private function _update_web_temp_fe_users($uids_array, $val) {
		global $adb,$table_prefix;
		$wsquery = "UPDATE  
					web_temp_fe_users
					SET imported = ".$val."
					WHERE imported is NULL and uid in (".implode(", ",$uids_array).")";
		$adb->query($wsquery);
	}
	
	// aggregates al the updates of the temp tables
	private function _update_web_temp() {
		if(count($this->uids_array_fe_users_update))
			$this->_update_web_temp_fe_users($this->uids_array_fe_users_update,0);
		if(count($this->uids_array_fe_users_insert))
			$this->_update_web_temp_fe_users($this->uids_array_fe_users_insert,1);
		if(count($this->uids_array_fe_users_skipped))
			$this->_update_web_temp_fe_users($this->uids_array_fe_users_skipped,-1);
		if(count($this->uids_array_tt_address_insert))
			$this->_update_web_temp_tt_address($this->uids_array_tt_address_insert,1);
		if(count($this->uids_array_tt_address_update))
			$this->_update_web_temp_tt_address($this->uids_array_tt_address_update,0);
		if(count($this->uids_array_tt_address_skipped))
			$this->_update_web_temp_tt_address($this->uids_array_tt_address_skipped,-1);
		if(count($this->uids_array_safe_tt_address_insert))
			$this->_update_web_temp_safe_tt_address($this->uids_array_safe_tt_address_insert,1);
		if(count($this->uids_array_safe_tt_address_update))
			$this->_update_web_temp_safe_tt_address($this->uids_array_safe_tt_address_update,0);
		if(count($this->uids_array_safe_tt_address_skipped))
			$this->_update_web_temp_safe_tt_address($this->uids_array_safe_tt_address_skipped,-1);
	}
		
	private function _process_targets($target_ids,$target_type,$target_state,$assigned_user_id) {
		global $adb,$table_prefix;		
		$ret_targets = array();
		if($this->log_active) echo "\n----------------------_process_targets STARTS with ".$target_type."------------------------\n";
		// danzi.tn@20140115 gestione codice fatturazione corso e data corso 
		$wsquery = "SELECT
					".$table_prefix."_leadscf.cf_747 as target_id,
					max(".$table_prefix."_leadscf.cf_726) as target_title,
					max(".$table_prefix."_leadscf.cf_756) as target_cod_fatt,
					max(".$table_prefix."_leadscf.cf_733) as target_date,
					max(".$table_prefix."_leadscf.cf_728) as target_localita,
					count(".$table_prefix."_leadscf.leadid) as totleads 
					FROM ".$table_prefix."_leadscf
					JOIN ".$table_prefix."_leaddetails ON ".$table_prefix."_leaddetails.leadid = ".$table_prefix."_leadscf.leadid AND ".$table_prefix."_leaddetails.converted = 0 
					JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_leadscf.leadid AND ".$table_prefix."_crmentity.deleted = 0
					LEFT JOIN ".$table_prefix."_targetscf ON ".$table_prefix."_targetscf.cf_1006 = ".$table_prefix."_leadscf.cf_747
					LEFT JOIN ".$table_prefix."_crmentity crmentitycamp ON crmentitycamp.crmid = ".$table_prefix."_targetscf.targetsid AND crmentitycamp.deleted = 0
					WHERE ".$table_prefix."_targetscf.targetsid is null and ".$table_prefix."_leadscf.cf_747 IS NOT NULL 
					AND ".$table_prefix."_leadscf.cf_747  in ('".implode("', '",array_keys($target_ids))."') GROUP BY ".$table_prefix."_leadscf.cf_747"; // array_keys($array)
		if($this->log_active) echo "_process_targets query = ".$wsquery."\n";
		$wsresult = $adb->query($wsquery);
		if ($wsresult && $adb->num_rows($wsresult) > 0){
			while($row = $adb->fetchByAssoc($wsresult)){
				$target_id = $row['target_id'];
				if($this->log_active) echo "Now adds target with cf_1006 = ".$target_id."\n";
				$target_title = $row['target_title'];
				$target_localita = $row['target_localita'];
				$target_cod_fatt = $row['target_cod_fatt'];
				$target_date = $row['target_date'];
				$target_entity = CRMEntity::getInstance('Targets');
				vtlib_setup_modulevars('Targets',$target_entity);
				$target_entity->column_fields['assigned_user_id']= $assigned_user_id;
				$target_entity->column_fields['targetname'] = $target_title;
				$target_entity->column_fields['target_type'] = $target_type;
				$target_entity->column_fields['target_state'] = $target_state;
				$target_entity->column_fields['cf_1006'] = $target_id;
				$target_entity->column_fields['cf_1225'] = $target_cod_fatt;
				$target_entity->column_fields['cf_1226'] = $target_date;
				$target_entity->save($module_name='Targets',$longdesc=false);
				$ret_targets[] = $target_entity;
			}
		} else {
			if($this->log_active) echo "_process_targets NONE missing!\n";
			$wsquery = "SELECT ".$table_prefix."_targets.targetsid  
						from ".$table_prefix."_targets
						JOIN ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_targets.targetsid  and ".$table_prefix."_crmentity.deleted = 0
						JOIN ".$table_prefix."_targetscf on ".$table_prefix."_targetscf.targetsid = ".$table_prefix."_targets.targetsid  
						WHERE ".$table_prefix."_targetscf.cf_1006 in ('".implode("', '",array_keys($target_ids))."')";
			if($this->log_active) echo "_process_targets select targets query = ".$wsquery."\n";
			$wsresult = $adb->query($wsquery);
			while($row = $adb->fetchByAssoc($wsresult)){
				$target_entity = CRMEntity::getInstance('Targets');
				$target_entity->id = $row['targetsid'];
				$target_entity->retrieve_entity_info($row['targetsid'],'Targets');
				if($this->log_active) echo "_process_targets found existing one id=>".$target_entity->id."\n";
				$ret_targets[] = $target_entity;
			}
		}
		return $ret_targets;
	}
		
	private function _process_campaigns($campaign_ids,$campaign_type,$campaign_state,$assigned_user_id) {
		global $adb,$table_prefix;		
		$ret_campaigns = array();
		if($this->log_active) echo "\n----------------------_process_campaigns STARTS------------------------\n";
		$wsquery = "SELECT
					".$table_prefix."_leadscf.cf_747 as campaign_id,
					max(".$table_prefix."_leadscf.cf_726) as campaign_title,
					max(".$table_prefix."_leadscf.cf_756) as campaign_cod_fatt,
					max(".$table_prefix."_leadscf.cf_733) as campaign_date,
					max(".$table_prefix."_leadscf.cf_728) as campaign_localita,
					count(".$table_prefix."_leadscf.leadid) as totleads 
					FROM ".$table_prefix."_leadscf
					JOIN ".$table_prefix."_leaddetails ON ".$table_prefix."_leaddetails.leadid = ".$table_prefix."_leadscf.leadid AND ".$table_prefix."_leaddetails.converted = 0 
					JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_leadscf.leadid AND ".$table_prefix."_crmentity.deleted = 0
					LEFT JOIN ".$table_prefix."_campaignscf ON ".$table_prefix."_campaignscf.cf_742 = ".$table_prefix."_leadscf.cf_747
					LEFT JOIN ".$table_prefix."_crmentity crmentitycamp ON crmentitycamp.crmid = ".$table_prefix."_campaignscf.campaignid AND crmentitycamp.deleted = 0
					WHERE ".$table_prefix."_campaignscf.campaignid is null and ".$table_prefix."_leadscf.cf_747 IS NOT NULL
					AND ".$table_prefix."_leadscf.cf_747 in ('".implode("', '",array_keys($campaign_ids))."') GROUP BY ".$table_prefix."_leadscf.cf_747";
		$wsresult = $adb->query($wsquery);
		if($this->log_active) echo "_process_campaigns query = ".$wsquery."\n";
		if ($wsresult && $adb->num_rows($wsresult) > 0){
			while($row = $adb->fetchByAssoc($wsresult)){
				$campaign_id = $row['campaign_id'];
				if($this->log_active) echo "Now adds campaingn with cf_742 = ".$campaign_id."\n";
				$campaign_title = $row['campaign_title'];
				$campaign_date = $row['campaign_date'];
				$campaign_localita = $row['campaign_localita'];
				$campaign_cod_fatt = $row['campaign_cod_fatt'];
				$campaign_entity = CRMEntity::getInstance('Campaigns');
				vtlib_setup_modulevars('Campaigns',$campaign_entity);
				$campaign_entity->column_fields['assigned_user_id']= $assigned_user_id;
				$campaign_entity->column_fields['campaignname'] = $campaign_title;
				$campaign_entity->column_fields['campaigntype'] = $campaign_type;
				$campaign_entity->column_fields['campaignstatus'] = $campaign_state;
				$campaign_entity->column_fields['cf_742'] = $campaign_id;
				$campaign_entity->column_fields['cf_743'] = $campaign_title;
				$campaign_entity->column_fields['cf_745'] = $campaign_date;
				$campaign_entity->column_fields['cf_746'] = $campaign_localita;
				$campaign_entity->column_fields['cf_759'] = $campaign_cod_fatt;
				$campaign_entity->save($module_name='Campaigns',$longdesc=false);
				$ret_campaigns[] = $campaign_entity;
			}
		} else {
			if($this->log_active) echo "_process_campaigns NONE missing!\n";
			$wsquery = "SELECT ".$table_prefix."_campaign.campaignid  
						from ".$table_prefix."_campaign
						JOIN ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_campaign.campaignid  and ".$table_prefix."_crmentity.deleted = 0
						JOIN ".$table_prefix."_campaignscf on ".$table_prefix."_campaignscf.campaignid = ".$table_prefix."_campaign.campaignid  
						WHERE ".$table_prefix."_campaignscf.cf_742 in ('".implode("', '",array_keys($campaign_ids))."')";
			if($this->log_active) echo "_process_campaigns select campaigns query = ".$wsquery."\n";
			$wsresult = $adb->query($wsquery);
			while($row = $adb->fetchByAssoc($wsresult)){
				$campaign_entity = CRMEntity::getInstance('Campaigns');
				$campaign_entity->id = $row['campaignid'];
				$campaign_entity->retrieve_entity_info($row['campaignid'],'Campaigns');
				if($this->log_active) echo "_process_campaigns found existing one id=>".$campaign_entity->id."\n";
				$ret_campaigns[] = $campaign_entity;
			}
		}
		return $ret_campaigns;
	}
	
	private function _process_relations($generated_ids) {
		global $adb,$table_prefix;		
		if($this->log_active) echo "\n----------------------_process_relations STARTS------------------------\n";
		$adb->query($wsquery);
		$wsquery = "INSERT INTO 
					".$table_prefix."_crmentityrel 
					(crmid,module,relcrmid,relmodule)
					SELECT 
					".$table_prefix."_targetscf.targetsid,
					'Targets' as trg,
					".$table_prefix."_leadscf.leadid,
					'Leads' as ld
					from ".$table_prefix."_leadscf
					join ".$table_prefix."_leaddetails on ".$table_prefix."_leaddetails.leadid = ".$table_prefix."_leadscf.leadid AND ".$table_prefix."_leaddetails.converted = 0 
					join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_leadscf.leadid AND ".$table_prefix."_crmentity.deleted = 0
					left join ".$table_prefix."_targetscf on ".$table_prefix."_targetscf.cf_1006 = ".$table_prefix."_leadscf.cf_747
					left join ".$table_prefix."_crmentity crmentitytarg on crmentitytarg.crmid = ".$table_prefix."_targetscf.targetsid AND crmentitytarg.deleted = 0
					left join ".$table_prefix."_crmentityrel on ".$table_prefix."_crmentityrel.crmid = crmentitytarg.crmid AND ".$table_prefix."_crmentityrel.relcrmid = ".$table_prefix."_crmentity.crmid
					where ".$table_prefix."_targetscf.targetsid IS NOT NULL AND ".$table_prefix."_crmentityrel.crmid IS NULL
					AND ".$table_prefix."_leadscf.cf_747 in ('".implode("', '",array_keys($generated_ids))."')	";
		$adb->query($wsquery);
		$wsquery = "INSERT INTO 
					".$table_prefix."_crmentityrel 
					(crmid,module,relcrmid,relmodule)
					SELECT 
					".$table_prefix."_targetscf.targetsid,
					'Targets' as trg,
					".$table_prefix."_campaignscf.campaignid,
					'Campaigns' as ld
					from ".$table_prefix."_campaignscf
					join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_campaignscf.campaignid AND ".$table_prefix."_crmentity.deleted = 0
					left join ".$table_prefix."_targetscf on ".$table_prefix."_targetscf.cf_1006 = ".$table_prefix."_campaignscf.cf_742 AND  ".$table_prefix."_campaignscf.cf_742 IS NOT NULL AND  ".$table_prefix."_campaignscf.cf_742 <>''
					left join ".$table_prefix."_crmentity crmentitytarg on crmentitytarg.crmid = ".$table_prefix."_targetscf.targetsid AND crmentitytarg.deleted = 0
					left join ".$table_prefix."_crmentityrel on ".$table_prefix."_crmentityrel.crmid = crmentitytarg.crmid AND ".$table_prefix."_crmentityrel.relcrmid = ".$table_prefix."_crmentity.crmid
					where ".$table_prefix."_targetscf.targetsid IS NOT NULL AND ".$table_prefix."_crmentityrel.crmid IS NULL
					AND ".$table_prefix."_targetscf.cf_1006 in ('".implode("', '",array_keys($generated_ids))."')	";
		$adb->query($wsquery);
		if($this->log_active) echo "\n----------------------_process_relations TERMINATED------------------------\n";
	}
	
	private function _find_entities_by_email($input_email) {
		global $adb,$table_prefix;		
		if($this->log_active) echo "\n----------------------_find_entities_by_email STARTS------------------------\n";
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
		if ($wsresult && $adb->num_rows($wsresult) > 0){
			while($row = $adb->fetchByAssoc($wsresult)){
				if($this->log_active) echo "found contactid=".$row['contactid']."\n";
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
		if ($wsresult && $adb->num_rows($wsresult) > 0){
			while($row = $adb->fetchByAssoc($wsresult)){
				if($this->log_active) echo "found accountid=".$row['crmid']."\n";
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
				if($this->log_active) echo "found vendorid=".$row['crmid']."\n";
				$entity_ids[$row['crmid']] =1;
				$vendor_entity = CRMEntity::getInstance('Vendors');
				$vendor_entity->id = $row['crmid'];
				$vendor_entity->retrieve_entity_info($row['crmid'],'Vendors');
				$entity_objects['Vendors'][$row['crmid']]=$vendor_entity;
			}
		}
		*/
		$wsquery = "SELECT crmid FROM ".$table_prefix."_crmentity LEFT JOIN ".$table_prefix."_leaddetails ON leadid=crmid WHERE deleted=0 AND converted=0 AND email='".$input_email."'"; // tabid=6
		$wsresult = $adb->query($wsquery);
		if ($wsresult && $adb->num_rows($wsresult) > 0){
			while($row = $adb->fetchByAssoc($wsresult)){
				if($this->log_active) echo "found leadid=".$row['crmid']."\n";
				$entity_ids[$row['crmid']] =1;
				$lead_entity = CRMEntity::getInstance('Leads');
				$lead_entity->id = $row['crmid'];
				$lead_entity->retrieve_entity_info($row['crmid'],'Leads');
				$entity_objects['Leads'][$row['crmid']]=$lead_entity;
			}
		}
		if($this->log_active) echo "----------------------_find_entities_by_email ENDS------------------------\n";
		return array($entity_ids,$entity_objects);
	}
	
	private function _attach_doc($description,$assigned_user_id) {
		$doc_id=0;
		if($_FILES["filename"]["name"] != "")  
		{
			$_REQUEST['filename_hidden'] = $_FILES['filename']['name'];
			$uploadfile = $_FILES['filename']['name'];
			
			$newDoc = CRMEntity::getInstance('Documents');
			vtlib_setup_modulevars('Documents',$newDoc);
			$newDoc->column_fields['notes_title'] = $array_file[0];
			$newDoc->column_fields['notecontent'] = 'Web Form Request: '.$description;
			$newDoc->column_fields['filestatus'] = 1;
			$newDoc->column_fields['filelocationtype'] = 'I';
			$newDoc->column_fields['assigned_user_id']= $assigned_user_id;
			$newDoc->column_fields['folderid']= '26'; // o 22x26
			$newDoc->save($module_name='Documents',$longdesc=false);
			$doc_id = $newDoc->id;
		}
		return $doc_id;
	}
	
	private function _set_mappings() {
		$this->mapping['Leads']['firstname'] = 'first_name'; // manage also the lead source
		$this->mapping['Leads']['lastname'] = 'last_name';
		$this->mapping['Leads']['email'] = 'email';
		$this->mapping['Leads']['phone'] = 'phone'; // 'agencyphone'
		$this->mapping['Leads']['mobile'] = 'mobile';
		$this->mapping['Leads']['website'] = 'www';
		$this->mapping['Leads']['lane'] = 'address';
		$this->mapping['Leads']['company'] = 'company';
		$this->mapping['Leads']['city'] = 'city';
		$this->mapping['Leads']['code'] = 'zip';
		$this->mapping['Leads']['state'] = 'region';
		$this->mapping['Leads']['country'] = 'country';
		$this->mapping['Leads']['description'] = 'descr'; // verificare 
		$this->mapping['Leads']['fax'] = 'fax';
		$this->mapping['Leads']['leadstatus'] = 'leadstatus';
		$this->mapping['Leads']['leadsource'] = 'leadsource';
		$this->mapping['Leads']['assigned_user_id'] = 'assigned_user_id';
		$this->mapping['Leads']['smownerid'] = 'assigned_user_id';		
		$this->mapping['Leads']['cf_726'] = 'page_title';
		$this->mapping['Leads']['cf_728'] = 'location';
		$this->mapping['Leads']['cf_732'] = 'formula';
		$this->mapping['Leads']['cf_733'] = 'date';
		$this->mapping['Leads']['cf_734'] = 'iban';
		$this->mapping['Leads']['cf_735'] = 'vat';
		$this->mapping['Leads']['cf_736'] = 'taxid';
		$this->mapping['Leads']['cf_737'] = 'cf_737';
		$this->mapping['Leads']['cf_744'] = 'uid';
		$this->mapping['Leads']['cf_747'] = 'idtarget';
		$this->mapping['Leads']['cf_756'] = 'codfatt';
		$this->mapping['Leads']['cf_757'] = 'cf_757';
		$this->mapping['Leads']['cf_758'] = 'title';
		$this->mapping['Leads']['cf_768'] = 'costo';
		$this->mapping['Leads']['cf_808'] = 'tstamp';
		$this->mapping['Leads']['cf_807'] = 'cf_807';
		$this->mapping['Leads']['cf_806'] = 'companymail';
		$this->mapping['Leads']['cf_761'] = 'cf_761';
		/* USERGROUP */
		$this->group_mapping["3"]='RC / CARP';
		$this->group_mapping["4"]='RS / SAFE';
		$this->group_mapping["5"]='RC / CARP';
		$this->group_mapping["6"]='RC / CARP';
		$this->group_mapping["7"]='RS / SAFE';
		$this->group_mapping["8"]='RS / SAFE';
		$this->group_mapping["9"]='RS / SAFE';
		$this->group_mapping["10"]='RS / SAFE';
		$this->group_mapping["11"]='RS / SAFE';
		$this->group_mapping["12"]='RS / SAFE';
		$this->group_mapping["13"]='RS / SAFE';
		$this->group_mapping["14"]='RS / SAFE';
		$this->group_mapping["15"]='RS / SAFE';
		$this->group_mapping["16"]='RS / SAFE';
		$this->group_mapping["17"]='RS / SAFE';
		$this->group_mapping["18"]='RS / SAFE';
		$this->group_mapping["19"]='RS / SAFE';
		$this->group_mapping["20"]='RS / SAFE';
		$this->group_mapping["21"]='RS / SAFE';
		$this->group_mapping["22"]='RS / SAFE';
		$this->mapping['Accounts']['key'] = 'value';
		$this->mapping['Vendors']['key'] = 'value';
		$this->mapping['Contacts']['key'] = 'value';
	}
	
	// danzi.tn@20140115 _update_targetscf copiare cCodice Fatturazione Corso e Data Corso da Campagne a Target
	private function _update_targetscf() {
		global $adb, $table_prefix;
		$sql = "UPDATE 
				".$table_prefix."_targetscf
				set
				".$table_prefix."_targetscf.cf_1225 = ".$table_prefix."_campaignscf.cf_759, 
				".$table_prefix."_targetscf.cf_1226 = ".$table_prefix."_campaignscf.cf_745
				from ".$table_prefix."_targetscf
				JOIN ".$table_prefix."_crmentity as targetcf_entity on targetcf_entity.crmid = ".$table_prefix."_targetscf.targetsid and targetcf_entity.deleted=0
				JOIN ".$table_prefix."_crmentityrel on ".$table_prefix."_crmentityrel.crmid = ".$table_prefix."_targetscf.targetsid and ".$table_prefix."_crmentityrel.relmodule = 'Campaigns'
				JOIN ".$table_prefix."_campaignscf on ".$table_prefix."_campaignscf.campaignid =  ".$table_prefix."_crmentityrel.relcrmid
				JOIN ".$table_prefix."_crmentity as campaignscf_entity on  campaignscf_entity.crmid = ".$table_prefix."_campaignscf.campaignid and campaignscf_entity.deleted=0";
		$adb->query($sql);
	}
	
	private function _update_leaddetails() {
		global $adb, $table_prefix;
		if($this->log_active) echo "_update_leaddetails starting\n";
		$sql_deta = $this->_get_update_leaddetails("web_temp_safe_tt_address");
		$sql_addr = $this->_get_update_leadaddress("web_temp_safe_tt_address");
		foreach($this->ids_safe_tt_address_array_lead_insert as $item) {
			if($this->log_active) echo "safe id=".$item['id']." uid=".$item['uid']."\n";
			if($this->log_active) echo "sql_deta=".$sql_deta."\n";
			$adb->pquery($sql_deta,array($item['id'],$item['uid']));
			if($this->log_active) echo "sql_addr=".$sql_addr."\n";
			$adb->pquery($sql_addr,array($item['id'],$item['uid']));
		}
		$sql_deta = $this->_get_update_leaddetails("web_temp_tt_address");
		$sql_addr = $this->_get_update_leadaddress("web_temp_tt_address");
		foreach($this->ids_tt_address_array_lead_insert as $item) {
			if($this->log_active) echo "tt id=".$item['id']." uid=".$item['uid']."\n";
			if($this->log_active) echo "sql_deta=".$sql_deta."\n";
			$adb->pquery($sql_deta,array($item['id'],$item['uid']));
			if($this->log_active) echo "sql_addr=".$sql_addr."\n";
			$adb->pquery($sql_addr,array($item['id'],$item['uid']));
		}
		$sql_deta = $this->_get_update_leaddetails("web_temp_fe_users");
		$sql_addr = $this->_get_update_leadaddress("web_temp_fe_users");
		foreach($this->ids_fe_user_array_lead_insert as $item) {
			if($this->log_active) echo "fe id=".$item['id']." uid=".$item['uid']."\n";
			if($this->log_active) echo "sql_deta=".$sql_deta."\n";
			$adb->pquery($sql_deta,array($item['id'],$item['uid']));
			if($this->log_active) echo "sql_addr=".$sql_addr."\n";
			$adb->pquery($sql_addr,array($item['id'],$item['uid']));
		}	
		if($this->log_active) echo "_update_leaddetails ending\n";
	}
	
	private function _get_update_leadaddress($temp_table) {
		global $table_prefix;
		$sql= "update 
			".$table_prefix."_leadaddress
			SET
			".$table_prefix."_leadaddress.city = ".$temp_table.".city,
			".$table_prefix."_leadaddress.lane = ".$temp_table.".address,
			".$table_prefix."_leadaddress.state = ".$temp_table.".region
			FROM 
			".$table_prefix."_leadaddress
			JOIN ".$table_prefix."_leaddetails ON ".$table_prefix."_leaddetails.leadid = ".$table_prefix."_leadaddress.leadaddressid
			JOIN ".$temp_table." ON ".$temp_table.".email = ".$table_prefix."_leaddetails.email
			WHERE ".$table_prefix."_leadaddress.leadaddressid = ? AND ".$temp_table.".uid = ?
			";
		return $sql;
	}
	
	private function _get_update_leaddetails($temp_table) {
		global $table_prefix;
		$sql= "update 
				".$table_prefix."_leaddetails 
				SET
				".$table_prefix."_leaddetails.lastname = ".$temp_table.".last_name,
				".$table_prefix."_leaddetails.firstname = ".$temp_table.".first_name,
				".$table_prefix."_leaddetails.company = ".$temp_table.".company
				FROM 
				".$table_prefix."_leaddetails
				JOIN ".$temp_table." ON ".$temp_table.".email = ".$table_prefix."_leaddetails.email
				WHERE ".$table_prefix."_leaddetails.leadid = ? AND ".$temp_table.".uid = ?
				";
		return $sql;
	}
	
	
}

?>
