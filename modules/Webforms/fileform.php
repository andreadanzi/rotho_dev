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
require_once('include/PHPExcel/PHPExcel/IOFactory.php');

class File_Form_Import {
	var $adminuser;
	var $filepath = '';
	var $sheetname = 'CONTACTS';
	var $mainDescr = '';
	var $log_entries = Array();
	
	function setDescription($mainDescr) {
		$this->mainDescr = $mainDescr;
	}
	
	function addEvent($description,$subject,$assigned_user_id,$parent_id, $contact_id, $moduleName) {
		global $log, $adb;
		$acttime = strtotime("now");
		$fields = array(
					'activitytype'=> 'Contatto - Fiera',
					'description'=> $description,
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


	function importNow($filepath,$docId) {
		global $log, $adb, $table_prefix;
		$this->adminuser = CRMEntity::getInstance('Users');
		$this->adminuser->id=$this->adminuser->getActiveAdminId();
		$this->adminuser->retrieve_entity_info($this->adminuser->id, 'Users');	
		$log->debug("Entering File_Form_Import.importNow(".$filepath.") method ...");
		$this->filepath = $filepath;
		try {		
			$inputFileType = PHPExcel_IOFactory::identify($this->filepath);
			$log->debug("inputFileType is ".$inputFileType);
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setReadDataOnly(true);
			$objPHPExcel = $objReader->load($this->filepath);
			$objWorksheet = $objPHPExcel->getSheetByName($this->sheetname);
			$highestRow = $objWorksheet->getHighestRow();
			$highestColumn = $objWorksheet->getHighestColumn();
			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
			$account_dict['ND'] = "0x0";
			for ($row = 2; $row <= $highestRow; ++$row) {
				$accountName = "";
				$contactName = "";
				$cell = $objWorksheet->getCell('A'.$row);
				$con_parameters['leadsource'] = trim($cell->getValue());
				// $acc_parameters['cf_770'] = $con_parameters['leadsource'];
				if( empty($con_parameters['leadsource']) ) {
					$this->log_entries['skipped'][$row]='A'.$row;
					continue;  // log skipped rows
				} else {
					$ls_sql = "SELECT  leadsourceid,  leadsource
								FROM ".$table_prefix."_leadsource
								WHERE leadsource ='".$con_parameters['leadsource']."'";
					$ls_result = $adb->query($ls_sql);
					if($ls_result && $adb->num_rows($ls_result) > 0) {
						$log->debug('A'.$row." is OK!");
					}	else {
						$log->debug('A'.$row." Skipped!");
						$this->log_entries['skipped'][$row]='A'.$row;
						continue;  // log skipped rows
					}
				}
				$cell = $objWorksheet->getCell('B'.$row); // Descrizione Utente - non ci serve
				$cell = $objWorksheet->getCell('C'.$row);
				$acc_parameters['cf_762'] = trim($cell->getValue()); // CATEGORIA dev=>cf_799 rotho_prod=>cf_762
				$cell = $objWorksheet->getCell('D'.$row);
				$acc_parameters['accountname'] = trim($cell->getValue());
				if( empty($acc_parameters['accountname']) ) {
					$this->log_entries['skipped'][$row]='D'.$row;
					$log->debug('D'.$row." Skipped!");
					continue;  // log skipped rows
				}
				$cell = $objWorksheet->getCell('E'.$row);
				$con_parameters['salutationtype'] = trim($cell->getValue());
				$cell = $objWorksheet->getCell('F'.$row);
				$con_parameters['firstname'] = trim($cell->getValue());
				$cell = $objWorksheet->getCell('G'.$row);
				$con_parameters['lastname'] = trim($cell->getValue());
				$cell = $objWorksheet->getCell('H'.$row);
				$con_parameters['mobile'] = trim($cell->getValue());
				$cell = $objWorksheet->getCell('I'.$row);
				$acc_parameters['website'] = trim($cell->getValue());
				$cell = $objWorksheet->getCell('J'.$row);
				$acc_parameters['phone'] = trim($cell->getValue());
				$cell = $objWorksheet->getCell('K'.$row);
				$acc_parameters['email1'] = trim($cell->getValue());
				$cell = $objWorksheet->getCell('L'.$row);
				$acc_parameters['bill_street'] = trim($cell->getValue());
				$cell = $objWorksheet->getCell('M'.$row);
				$acc_parameters['bill_city'] = trim($cell->getValue());
				$cell = $objWorksheet->getCell('N'.$row);
				$acc_parameters['bill_state'] = trim($cell->getValue());
				$cell = $objWorksheet->getCell('O'.$row);
				$acc_parameters['bill_code'] = trim($cell->getValue());
				$cell = $objWorksheet->getCell('P'.$row); // Descrizione Stato - non ci serve
				$cell = $objWorksheet->getCell('Q'.$row);
				$acc_parameters['bill_country'] = trim($cell->getCalculatedValue()); // dev=>bill_country_iso3166 prod=>bill_country
				$cell = $objWorksheet->getCell('R'.$row); // Descrizione
				$acc_parameters['description'] = trim($cell->getValue());
				$cell = $objWorksheet->getCell('S'.$row); // Urgenza
				$urgenza = trim($cell->getValue());
				if( !empty( $urgenza) ) {
					$acc_parameters['description'] = $acc_parameters['description'] . ' Urgenza:' . $urgenza;
				}
				$cell = $objWorksheet->getCell('T'.$row);
				$acc_parameters['account_no'] = trim($cell->getValue());
				$account_no = trim($cell->getValue());
				$cell = $objWorksheet->getCell('U'.$row);
				$username = trim($cell->getCalculatedValue());
				// Retrieve user information
				$user = CRMEntity::getInstance('Users');
				$user->id = $user->retrieve_user_id($username);
				$acc_parameters['assigned_user_id'] = vtws_getWebserviceEntityId('Users' ,$user->id );
				$user->retrieve_entity_info($user->id, 'Users');
				$wsConId = "";
				$wsAccId = "";
				if(empty($account_no)) {
					$log->debug("account_no is empty, we need a new account!");
					if(!empty($acc_parameters['accountname']) && !empty($acc_parameters['email1']) && !empty($acc_parameters['cf_762']) && !empty($acc_parameters['bill_country'])) {
						if(!isset($account_dict[$acc_parameters['accountname']])) {
							$account_dict[$acc_parameters['accountname']] = 1;
							$acc_record = vtws_create('Accounts' , $acc_parameters, $this->adminuser);
							$this->log_entries['created'][$row] = $acc_parameters['accountname'];
							$accountName = $acc_record['accountname'];
							$log->debug("account created with id =".$acc_record['id']);
							$wsAccId = $acc_record['id'];
							$account_dict[$acc_parameters['accountname']] = $wsAccId;
						} else {
							$wsAccId = $account_dict[$acc_parameters['accountname']];
							$this->log_entries['updated'][$row] = $acc_parameters['accountname'];
						}
						$con_parameters['account_id'] = $wsAccId; 
						$con_parameters['assigned_user_id'] = $acc_parameters['assigned_user_id'];
						if( !empty($con_parameters['lastname']) ) {
							$con_record = vtws_create('Contacts' , $con_parameters, $this->adminuser);
							$wsConId = $con_record["id"];
							$log->debug("contact created with id =".$con_record['id']);
							$contactName = $con_record['firstname'] . " " . $con_record['lastname'];
						}
					}
				} else {
					$sql = "SELECT ".$table_prefix."_account.accountname, ".$table_prefix."_account.accountid, ".$table_prefix."_contactdetails.contactid, ".$table_prefix."_contactdetails.firstname, ".$table_prefix."_contactdetails.lastname
						FROM 
						".$table_prefix."_account 
						JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_account.accountid 
							 AND ".$table_prefix."_crmentity.deleted = 0 
						LEFT JOIN ".$table_prefix."_contactdetails ON ".$table_prefix."_contactdetails.accountid = ".$table_prefix."_account.accountid 
								AND ".$table_prefix."_contactdetails.firstname LIKE '%".$con_parameters['firstname']."%' 
								AND ".$table_prefix."_contactdetails.lastname LIKE '%".$con_parameters['lastname']."%'
						LEFT JOIN ".$table_prefix."_crmentity AS contacts_crmentity ON contacts_crmentity.crmid = ".$table_prefix."_contactdetails.contactid AND contacts_crmentity.deleted = 0
						WHERE ".$table_prefix."_account.account_no = '".$account_no."'";
					$result = $adb->query($sql);
					$bNotFound = true;
					while($sqlrow=$adb->fetchByAssoc($result))
					{
						$bNotFound = false;
						$log->debug("account_no is ".$account_no." with id = ".$sqlrow['accountid']);
						$accountName = $sqlrow['accountname'];
						$wsAccId = vtws_getWebserviceEntityId('Accounts' , $sqlrow['accountid'] );
						if( empty($sqlrow['contactid']) ) {
							$con_parameters['account_id'] = $wsAccId; // $accId;
							$con_parameters['assigned_user_id'] = $acc_parameters['assigned_user_id'];
							if( !empty($con_parameters['lastname']) ) {
								$con_record = vtws_create('Contacts' , $con_parameters, $this->adminuser);
								$wsConId = $con_record["id"];
								$log->debug("Account Contact created with id =".$con_record['id']);
								$contactName = $con_record['firstname'] . " " . $con_record['lastname'];
								$this->log_entries['created'][$row] = $acc_parameters['accountname'] ." - " . $contactName;
							} else {
								$this->log_entries['skipped'][$row] = 'G'.$row;
							}
						} else {
							$wsConId = vtws_getWebserviceEntityId('Contacts' , $sqlrow['contactid'] );
							$contactName = $sqlrow['firstname'] . " " . $sqlrow['lastname'];
							$this->log_entries['updated'][$row] = $acc_parameters['accountname'] ." - " . $contactName;
						}
					}
					if($bNotFound) {
						$this->log_entries['skipped'][$row]='D'.$row;
						continue;  // log skipped rows
					}
				}
				if(!empty($wsAccId)) {
					$accIDs = vtws_getIdComponents($wsAccId);
					if(!empty($wsConId))
					{
						$conIDs = vtws_getIdComponents($wsConId);
						$this->addEvent($this->mainDescr . " " . $acc_parameters['description'] . " - " .$contactName,$con_parameters['leadsource']. " - " .$contactName,$acc_parameters['assigned_user_id'],$wsAccId, $wsConId, "Contacts");
						vtws_insertWebserviceRelatedNotes($conIDs[1], $docId);
						vtws_insertWebserviceRelatedNotes($accIDs[1], $docId);
					} else { // nel caso non ci siano contatti corrispondenti
						$this->addEvent($this->mainDescr . " " . $acc_parameters['description'] . " - " .$accountName,$con_parameters['leadsource']. " - " .$accountName,$acc_parameters['assigned_user_id'],$wsAccId, $wsConId , "Accounts");
						vtws_insertWebserviceRelatedNotes($accIDs[1], $docId);
					}
				}
			}
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
			
			$returnURL = $webform->getReturnUrl();

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
				// _test $doc_parameters['folderid']='22x27';
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
					
					$fileFormObj = new File_Form_Import();
					$fileFormObj->setDescription($parameters['description']);
					$fileFormObj->importNow($filepath,$docId);
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