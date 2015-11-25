<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
 // danzi.tn@20141217 nuova classificazione da report visite
include_once('config.php');
require_once('include/logging.php');
require_once('include/database/PearDatabase.php');
require_once('data/SugarBean.php');
require_once('data/CRMEntity.php');
require_once('modules/Contacts/Contacts.php');
require_once('modules/Accounts/Accounts.php');
class VisitreportHandler extends VTEventHandler {

	function handleEvent($eventName, $data) {
		global $adb, $current_user,$log;
		global $table_prefix;
		// check irs a timcard we're saving.
		if (!($data->focus instanceof Visitreport)) {
			return;
		}
		
		if($eventName == 'vtiger.entity.beforesave') {
			// Entity is about to be saved, take required action
			$log->debug("handleEvent vtiger.entity.beforesave entered");
			$log->debug("handleEvent vtiger.entity.beforesave treminated");
		}

		if($eventName == 'vtiger.entity.aftersave') {
			$log->debug("handleEvent vtiger.entity.aftersave entered");
			$id = $data->getId();
			$module = $data->getModuleName();
			$focus = $data->focus;
			$log->debug("handleEvent vtiger.entity.aftersave accountid = ".$focus->column_fields['accountid']);
			$log->debug("handleEvent vtiger.entity.aftersave vr_account_line = ".$focus->column_fields['vr_account_line']);
			$log->debug("handleEvent vtiger.entity.aftersave vr_account_client_type = ".$focus->column_fields['vr_account_client_type']);
			$log->debug("handleEvent vtiger.entity.aftersave vr_account_main_activity = ".$focus->column_fields['vr_account_main_activity']);
			$log->debug("handleEvent vtiger.entity.aftersave vr_account_sec_activity = ".$focus->column_fields['vr_account_sec_activity']);
			$log->debug("handleEvent vtiger.entity.aftersave vr_account_brand = ".$focus->column_fields['vr_account_brand']);
			$log->debug("handleEvent vtiger.entity.aftersave vr_account_yearly_pot = ".$focus->column_fields['vr_account_yearly_pot']);
			$log->debug("handleEvent vtiger.entity.aftersave vr_area_intervento = ".$focus->column_fields['vr_area_intervento']);
			
			$AccountFocus = CRMEntity::getInstance('Accounts');
			$AccountFocus->retrieve_entity_info_no_html($focus->column_fields['accountid'], 'Accounts');
			$AccountFocus->id = $focus->column_fields['accountid'];
			$AccountFocus->mode = 'edit';
			// $AccountFocus->column_fields['account_line'] = $focus->column_fields['vr_account_line'];
			$AccountFocus->column_fields['account_client_type'] = $focus->column_fields['vr_account_client_type'];
			$AccountFocus->column_fields['account_main_activity'] = $focus->column_fields['vr_account_main_activity'];
			$AccountFocus->column_fields['account_sec_activity'] = $focus->column_fields['vr_account_sec_activity'];
			$AccountFocus->column_fields['account_brand'] = $focus->column_fields['vr_account_brand'];
			$AccountFocus->column_fields['area_intervento'] = $focus->column_fields['vr_area_intervento'];
			$AccountFocus->column_fields['account_yearly_pot'] = $focus->column_fields['vr_account_yearly_pot'];
			$AccountFocus->save('Accounts');
			
			
			if($data->isNew())
			{
				$log->debug("handleEvent vtiger.entity.aftersave this is an insert");
			} else {
				$log->debug("handleEvent vtiger.entity.aftersave this is an update");
			}
			$log->debug("handleEvent vtiger.entity.aftersave terminated");
		}
	}
}
?>
