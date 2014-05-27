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
chdir(dirname(__FILE__) . '/../..');

include_once 'include/Zend/Json.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'include/utils/VtlibUtils.php';
include_once 'include/Webservices/Create.php';
include_once 'include/Webservices/Query.php';
include_once 'include/Webservices/Utils.php';
include_once 'modules/Webforms/model/WebformsModel.php';
include_once 'modules/Webforms/model/WebformsFieldModel.php';
include_once 'include/QueryGenerator/QueryGenerator.php';
include_once 'plugins/erpconnector/RothoBus/RothoBusClass.php';
require_once('include/PHPExcel/PHPExcel/IOFactory.php');

class Xlsx_File_Form_Import {
	public $adminuser;
	var $filepath = '';
	var $sheetname = 'CONTACTS';
	var $mainDescr = '';
	var $log_entries = Array();
	
	function setDescription($mainDescr) {
		$this->mainDescr = $mainDescr;
	}
	

	
	
	function getTarget($target_name) {
		global $log, $adb,$table_prefix;
		$ids = array();
		$sql="SELECT 
				".$table_prefix."_targets.targetsid,
				".$table_prefix."_targets.target_no,
				".$table_prefix."_targets.target_type
				FROM 
				".$table_prefix."_targets 
				JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_targets.targetsid AND ".$table_prefix."_crmentity.deleted = 0
				WHERE ".$table_prefix."_targets.targetname = '".$target_name."'";
		$wsresult = $adb->query($sql);
		if ($wsresult && $adb->num_rows($wsresult) > 0){
			while($row = $adb->fetchByAssoc($wsresult)){
				$ids[] = $row['targetsid'];
			}
		}
		return $ids;
	}
	
	function processTargetRelations($entity_rel) {
		global $log, $adb,$table_prefix;
		foreach($entity_rel as $entity_type=>$target_rel) {
			foreach($target_rel as $key=>$targetids) {
				foreach($targetids as $targetid) {
					$sql="INSERT INTO ".$table_prefix."_crmentityrel 
							(crmid,module,relcrmid,relmodule)
							VALUES
							(?,'Targets',?,?)";
					 $adb->pquery($sql,array($targetid,$key,$entity_type));
				}
			}
		}
	}
	
	
	function addEvent($description,$subject,$assigned_user_id,$parent_id, $contact_id, $moduleName,$taskpriority='Medium') {
		global $log, $adb;
		$acttime = strtotime("now");
		$fields = array(
					'activitytype'=> 'Contatto - Fiera',
					'description'=> $description,
					'taskpriority'=> $taskpriority,
					'subject'=> $subject,
					'assigned_user_id' => $assigned_user_id,
					'time_start'=> date('H:i',$acttime),
					'date_start'=> getDisplayDate(date('Y-m-d',$acttime)),
					'due_date'=> getDisplayDate(date('Y-m-d',$acttime+300)),
					'time_end'=> date('H:i',$acttime+300),
					'eventstatus'=> 'Held',
					'is_all_day_event' => false,
					'duration_hours' => 0,
					'duration_minutes' => 5
					);
		if($moduleName=='Contacts'){
			$fields['contact_id'] = $contact_id;
			$fields['parent_id'] = $parent_id;
		} else {
			$fields['parent_id'] = $parent_id;
		}
		try {
			$eventCreated = vtws_create('Events', $fields, $this->adminuser);
		} catch (Exception $e) {
			$log->debug("Error on addEvent is ".$e->getMessage());
		}
	}


	function importNow($filepath,$docId,$sheetName='CONTACTS') {
		global $log, $adb, $table_prefix;
		$this->sheetname = $sheetName;
		// danzi.tn@20131009
		$entity_rel = array();
		// Salvo la posizione del file
		$log->debug("Entering File_Form_Import.importNow(".$filepath.") method ...");
		$this->filepath = $filepath;
		try {		
		
			$rothoBusClass = new RothoBus();
			$rothoBusClass->setLog(false);
			$rothoBusClass->setExistingCourses(true);
			// VErifico al tipologia del file (XLS,XLSX, etc)
			$inputFileType = PHPExcel_IOFactory::identify($this->filepath);
			$log->debug("inputFileType is ".$inputFileType);
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setReadDataOnly(true);
			$objPHPExcel = $objReader->load($this->filepath);
			// Recupero lo sheet denomminato come da parametro
			$objWorksheet = $objPHPExcel->getSheetByName($this->sheetname);
			// Calcolo inizio e fine dei dati in termini di celle
			$highestRow = $objWorksheet->getHighestRow();
			$highestColumn = $objWorksheet->getHighestColumn();
			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
			$account_dict['ND'] = "0x0";
			// Ciclo su tutte le righe, eccetto la prima
			for ($row = 2; $row <= $highestRow; ++$row) {
				$accountName = "";
				$contactName = "";
				// danzi.tn@20131009
				$targetids = array();
				// prima colonna è leadsource
				$cell = $objWorksheet->getCell('A'.$row);
				$con_parameters['leadsource'] = trim($cell->getValue());
				$parameters['leadsource'] = trim($cell->getValue());
				// $acc_parameters['cf_770'] = $con_parameters['leadsource'];
				if( empty($con_parameters['leadsource']) ) {
					// se leadsource è vuoto salto
					$this->log_entries['skipped'][$row]='A'.$row;
					continue;  // log skipped rows
				} else {
					$ls_sql = "SELECT  leadsourceid,  leadsource
								FROM ".$table_prefix."_leadsource
								WHERE leadsource ='".$con_parameters['leadsource']."'";
					$ls_result = $adb->query($ls_sql);
					if($ls_result && $adb->num_rows($ls_result) > 0) {
						// danzi.tn@20131009
						$targetids = $this->getTarget($con_parameters['leadsource']);
						$log->debug('A'.$row." is OK!");
					}	else {
						// se leadsource non è presente nelle picklist salto
						$log->debug('A'.$row." Skipped!");
						$this->log_entries['skipped'][$row]='A'.$row;
						continue;  // log skipped rows
					}
				}
				$cell = $objWorksheet->getCell('K'.$row);
				$acc_parameters['accountname'] = trim($cell->getValue());
				$parameters['company'] = $acc_parameters['accountname'];
				if( empty($acc_parameters['accountname']) ) {
					$this->log_entries['skipped'][$row]='D'.$row;
					$log->debug('D'.$row." Skipped!");
					continue;  // log skipped rows
				}
				$cell = $objWorksheet->getCell('B'.$row);
				$con_parameters['salutationtype'] = trim($cell->getValue());
				$cell = $objWorksheet->getCell('C'.$row);
				$con_parameters['firstname'] = substr(trim($cell->getValue()),0,20);
				$parameters['firstname'] = $con_parameters['firstname'];
				$cell = $objWorksheet->getCell('D'.$row);
				$con_parameters['lastname'] = substr(trim($cell->getValue()),0,30);
				$parameters['lastname'] = $con_parameters['lastname'];
				$cell = $objWorksheet->getCell('J'.$row);
				$acc_parameters['phone'] = trim($cell->getValue());
				$parameters['phone'] = $acc_parameters['phone'];
				$con_parameters['phone'] = $acc_parameters['phone'];
				$cell = $objWorksheet->getCell('L'.$row);
				$acc_parameters['email1'] = trim($cell->getValue());
				$parameters['email'] = $acc_parameters['email1'];
				$cell = $objWorksheet->getCell('E'.$row);
				$acc_parameters['bill_street'] = trim($cell->getValue());
				$parameters['lane'] = $acc_parameters['bill_street'];
				$cell = $objWorksheet->getCell('G'.$row);
				$acc_parameters['bill_city'] = trim($cell->getValue());
				$parameters['city'] = $acc_parameters['bill_city'];
				$cell = $objWorksheet->getCell('H'.$row);
				$acc_parameters['bill_state'] = trim($cell->getValue());
				$parameters['state'] = $acc_parameters['bill_state'];
				$cell = $objWorksheet->getCell('F'.$row);
				$acc_parameters['bill_code'] = trim($cell->getValue());
				$parameters['code'] = $acc_parameters['bill_code'];
				$cell = $objWorksheet->getCell('I'.$row);
				$acc_parameters['bill_country'] = trim($cell->getValue()); // dev=>bill_country_iso3166 prod=>bill_country
				$parameters['country'] = $acc_parameters['bill_country'];
				$cell = $objWorksheet->getCell('P'.$row);
				$acc_parameters['website'] = trim($cell->getValue());
				$cell = $objWorksheet->getCell('M'.$row);
				$acc_parameters['cf_762'] = trim($cell->getValue()); // CATEGORIA dev=>cf_799 rotho_prod=>cf_762
				$cell = $objWorksheet->getCell('N'.$row);
				$account_no = trim($cell->getValue());
				$cell = $objWorksheet->getCell('O'.$row);
				$username = trim($cell->getValue());
				$cell = $objWorksheet->getCell('Q'.$row); // danzi.tn@20140129 aggiunti tre nuovi parametri otherphone mobile description
				$acc_parameters['otherphone'] = trim($cell->getValue());
				$con_parameters['mobile'] = trim($cell->getValue());
				$cell = $objWorksheet->getCell('R'.$row);
				$acc_parameters['description'] = trim($cell->getValue());
				$con_parameters['description'] = trim($cell->getValue()); // danzi.tn@20140129 aggiunti tre  nuovi parametri
				// Retrieve user information
				$user = CRMEntity::getInstance('Users');
				$user->id = $user->retrieve_user_id($username);
				if( empty($user->id ) || $user->id ==0 || $user->id =="0") 
				{
					continue;
				}
				$acc_parameters['assigned_user_id'] = vtws_getWebserviceEntityId('Users' ,$user->id );
				$parameters['assigned_user_id'] = $acc_parameters['assigned_user_id'];
				$con_parameters['assigned_user_id'] = $acc_parameters['assigned_user_id'];
				$user->retrieve_entity_info($user->id, 'Users');
				// danzi.tn@20131209 gestione Webforms
				$campaign_id = strtolower (str_replace(' ', '_',$parameters['leadsource']));
				$parameters['cf_747'] = $campaign_id; // campaign_id
				$parameters['cf_726'] = "Form " . $parameters['leadsource']; // campaign_title
				$parameters['cf_728'] = 'ND';
				$parameters['cf_733'] = 'ND';
				$parameters['cf_756'] = 'ND';
				$bFound = $rothoBusClass->check_web_form($parameters['email'], $parameters, 'Form Fiere',$account_no);
				if(!$bFound)
				{	
					$acc_record = vtws_create('Accounts', $acc_parameters, $this->adminuser);
					$accIdComponents = vtws_getIdComponents($acc_record['id']);
					$accId = $accIdComponents[1];
					if( isset($accId) && $accId > 0 )
					{
						vtws_insertWebserviceRelatedNotes($accId, $docId);
						if(empty($con_parameters['lastname'])) $con_parameters['lastname'] = $acc_parameters['accountname'];
						if(empty($con_parameters['mobile'])) $con_parameters['mobile'] = "+39";
						if(empty($con_parameters['phone'])) $con_parameters['phone'] = $con_parameters['mobile'];
						$con_parameters['account_id'] = $acc_record['id']; 
						$con_parameters['email'] = $acc_parameters['email1'];
						$con_record = vtws_create('Contacts' , $con_parameters, $this->adminuser);
						$conIdComponents = vtws_getIdComponents($con_record['id']);
						$conId = $conIdComponents[1];
						if( isset($conId) && $conId > 0 )
						{
							vtws_insertWebserviceRelatedNotes($conId, $docId);
						}							
						$bFound = $rothoBusClass->check_web_form($parameters['email'], $parameters, 'Form Fiere');
					}
				} 
				// danzi.tn@20131209 e
			}
			// danzi.tn@20131009 e
			$log->debug("File_Form_Import.importNow log_entries Created = ".count($this->log_entries['created']).", Updated = ".count($this->log_entries['updated']).", Skipped = ".count($this->log_entries['skipped']));
		} catch (PHPExcel_Reader_Exception $e) {
			$log->debug($e->getMessage());
		}
		$log->debug("Exiting File_Form_Import.importNow(".$filepath.") method ...");
	}
}

class Webform_Capture {
	
	function captureNow($request) {
		$returnURL = false;
		try {

			if(!vtlib_isModuleActive('Webforms')) throw new Exception('webforms is not active');
			
			$webform = Webforms_Model::retrieveWithPublicId(vtlib_purify($request['publicid']));
			if (empty($webform)) throw new Exception("Webform not found.");
					
			if(isset($request['rdrct'])) {
				$rdrct = vtlib_purify($request['rdrct']);
				$returnURL = "crm.rothoblaas.com/".$rdrct.".php";
			} else {
				$returnURL = $webform->getReturnUrl();
			}

			// Retrieve user information
			$user = CRMEntity::getInstance('Users');
			$user->id=$user->getActiveAdminId();
			$user->retrieve_entity_info($user->id, 'Users');

			// Prepare the parametets
			$parameters = array();
			$webformFields = $webform->getFields();
			foreach ($webformFields as $webformField) {
				//crmv@32257
				if(is_array(vtlib_purify($request[$webformField->getNeutralizedField()]))){
					$fieldData=implode(" |##| ",vtlib_purify($request[$webformField->getNeutralizedField()]));
				}
				else{
					$fieldData=vtlib_purify($request[$webformField->getNeutralizedField()]);
				}
				$parameters[$webformField->getFieldName()] = stripslashes($fieldData);
				if(in_array($parameters[$webformField->getFieldName()],array('','--None--')) && $webformField->getDefaultValue() != null){
					$parameters[$webformField->getFieldName()] = decode_html($webformField->getDefaultValue());
				}
			}
			$parameters['assigned_user_id'] = vtws_getWebserviceEntityId('Users', $webform->getOwnerId());
			//*** Attachment ***//  
			if($_FILES["filename"]["name"] != "")  
			{
				$_REQUEST['filename_hidden'] = $_FILES['filename']['name'];
				$uploadfile = $_FILES['filename']['name'];
				$doc_parameters=array();
				$array_file = explode('.',$uploadfile);
				$doc_parameters['notes_title']= $array_file[0];
				$doc_parameters['notecontent'] = 'Note File Fiere: '.$parameters['description'];
				$doc_parameters['filestatus'] = 1;
				$doc_parameters['filelocationtype']='I';
				$doc_parameters['assigned_user_id']=$parameters['assigned_user_id'];
				$doc_parameters['folderid']='22x33';
				//dev $doc_parameters['folderid']='23x18'; //dev
				$doc_record = vtws_create('Documents' , $doc_parameters, $user);
				$doc_id = $doc_record['id'];
				$docIdComponents = vtws_getIdComponents($doc_id);
				$docId = $docIdComponents[1];
				// Per gestire attach usare ".$table_prefix."_seattachmentsrel 
			}			
			if( !empty($docId)  ) {
				global $adb, $table_prefix;
				$sql = "SELECT 
						".$table_prefix."_attachments.attachmentsid,
						".$table_prefix."_attachments.name,
						".$table_prefix."_attachments.path
						FROM 
						".$table_prefix."_seattachmentsrel
						JOIN ".$table_prefix."_attachments on ".$table_prefix."_attachments.attachmentsid = ".$table_prefix."_seattachmentsrel.attachmentsid 
						where ".$table_prefix."_seattachmentsrel.crmid=".$docId;
				$result = $adb->query($sql);
				$filepaths = array();
				while($row=$adb->fetchByAssoc($result))
				{
					$filepaths[] = $row['path'].$row['attachmentsid'].'_'.$row['name'];
				}
				foreach($filepaths as $filepath) {
					
					$fileFormObj = new Xlsx_File_Form_Import();
					$fileFormObj->setDescription($parameters['description']);
					$fileFormObj->adminuser = $user;
					$fileFormObj->importNow($filepath,$docId,'Tabelle1');
				}
				$this->sendResponse($returnURL, 'ok');
			} else {
				$this->sendResponse($returnURL, 'Documento non caricato');
			}
			return;

		} catch (Exception $e) {
			$this->sendResponse($returnURL, false, $e->getMessage());
			return;
		}
	}

	protected function sendResponse($url, $success=false, $failure=false) {
		if (empty($url)) {
			if ($success) $response = Zend_Json::encode(array('success' => true, 'result' => $success));
			else $response = Zend_Json::encode(array('success' => false, 'error' => array('message' => $failure)));

			// Support JSONP
			if (!empty($_REQUEST['callback'])) {
				$callback = vtlib_purify($_REQUEST['callback']);
				echo sprintf("%s(%s)", $callback, $response);
			} else {
				echo $response;
			}
		} else {
			header(sprintf("Location: http://%s?%s=%s", $url, ($success? 'success' : 'error'), ($success? $success: $failure)));
		}
	}
}

// NOTE: Take care of stripping slashes...
$webformCapture = new Webform_Capture();
$webformCapture->captureNow($_REQUEST);
?>