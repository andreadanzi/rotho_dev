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
include_once 'modules/Webforms/model/WebformsModel.php';
include_once 'modules/Webforms/model/WebformsFieldModel.php';
include_once 'include/QueryGenerator/QueryGenerator.php';
include_once 'include/QueryGenerator/QueryGenerator.php';
include_once 'plugins/erpconnector/RothoBus/RothoBusClass.php';

class Webform_Capture {
	
	function captureNow($request) {
		$returnURL = false;
		try {

			if(!vtlib_isModuleActive('Webforms')) throw new Exception('webforms is not active');
			
			$webform = Webforms_Model::retrieveWithPublicId(vtlib_purify($request['publicid']));
			if (empty($webform)) throw new Exception("Webform not found.");
			if(isset($request['data_prescelta'])) {
				$data_prescelta = vtlib_purify($request['data_prescelta']);
			}
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
				//crmv@32257e
				if($webformField->getRequired()){
					if(empty($parameters[$webformField->getFieldName()]))  throw new Exception("Required fields not filled");
				}
			}
			if(isset($data_prescelta)) {
				$parameters['data_prescelta'] = $data_prescelta;
				$parameters['description'] = "Data prescelta: " . $data_prescelta . " \nArgomento: ".$parameters['description'];
			}
			// danzi.tn@20130327 $parameters['assigned_user_id'] = vtws_getWebserviceEntityId('Users', $webform->getOwnerId());
			// danzi.tn@20130327 -- modifica per recepire Owner Id dalla Form
			if( isset($request['assigned_user_id']) && !empty($request['assigned_user_id'])) {
				$parameters['assigned_user_id'] = vtws_getWebserviceEntityId('Users', $request['assigned_user_id']);
			} else {
				$parameters['assigned_user_id'] = vtws_getWebserviceEntityId('Users', $webform->getOwnerId());
			}
			// danzi.tn@20130327 e
			// Create the record
			
			// danzi.tn@20131209 gestione Webforms
			$rothoBusClass = new RothoBus();
			$rothoBusClass->setLog(false);
			$rothoBusClass->setExistingCourses(true);
			$campaign_id = strtolower (str_replace(' ', '_',$parameters['leadsource']));
			$parameters['cf_747'] = $campaign_id; // campaign_id
			$parameters['cf_726'] = "Form " . $parameters['leadsource']; // campaign_title
			$parameters['cf_728'] = 'ND';
			$parameters['cf_733'] = 'ND';
			$parameters['cf_756'] = 'ND';
			$bFound = $rothoBusClass->check_web_form($parameters['email'], $parameters, 'Form Fiere');
			if(!$bFound)
			{				
				$record = vtws_create($webform->getTargetModule(), $parameters, $user);
				$entity_id = $record['id'];
				$leadIdComponents = vtws_getIdComponents($entity_id);
				$leadId = $leadIdComponents[1];
				if( isset($leadId) && $leadId > 0 )
				{
					$bFound = $rothoBusClass->check_web_form($parameters['email'], $parameters, 'Form Fiere');
				}
			}
			// danzi.tn@20131209 e
			
		
			
			$this->sendResponse($returnURL, 'ok');
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