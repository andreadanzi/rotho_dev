<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
 // danzi.tn@20150108 nuova classificazione 
include_once('config.php');
require_once('include/logging.php');
require_once('include/database/PearDatabase.php');
require_once('data/SugarBean.php');
require_once('data/CRMEntity.php');
require_once('modules/Contacts/Contacts.php');
require_once('modules/Accounts/Accounts.php');
class AccountsHandler extends VTEventHandler {

	function handleEvent($eventName, $data) {
		global $adb, $current_user,$log;
		global $table_prefix;
		// check irs a timcard we're saving.
		if (!($data->focus instanceof Accounts)) {
			return;
		}
		$id = $data->getId();
		$module = $data->getModuleName();
		$focus = $data->focus;
		
		if($eventName == 'vtiger.entity.beforesave') {
			// Entity is about to be saved, take required action
			$log->debug("handleEvent vtiger.entity.beforesave entered");
			if($data->isNew())
			{
				$log->debug("handleEvent vtiger.entity.beforesave this is an insert");
				$assigned_user_id = $focus->column_fields['assigned_user_id'];
				$smownerid = $focus->column_fields['smownerid'];
				$rets = $this->_get_default_user_data($assigned_user_id);
				$focus->column_fields['agent_number'] = $rets["erp_code"];
				$focus->column_fields['ref_vendite_int'] = $rets["referente_nome"];
				$focus->column_fields['codice_vendite_int'] = $rets["referente_codice"];
				$focus->column_fields['area_mng_no'] = $rets["agent_cod_capoarea"];
				$focus->column_fields['area_mng_name'] = $rets["capoarea_first_name"] . " ".$rets["capoarea_last_name"]  ;
				$focus->column_fields['account_line'] = $rets["user_line"];
				// smownerid o assigned_user_id
			} else {
				$log->debug("handleEvent vtiger.entity.beforesave this is an update");
				if(empty( $focus->column_fields['account_line']) || $focus->column_fields['account_line'] == '' || $focus->column_fields['account_line'] == '---' ) {
					$assigned_user_id = $focus->column_fields['assigned_user_id'];
					$smownerid = $focus->column_fields['smownerid'];
					$rets = $this->_get_default_user_data($assigned_user_id);
					$focus->column_fields['agent_number'] = $rets["erp_code"];
					$focus->column_fields['ref_vendite_int'] = $rets["referente_nome"];
					$focus->column_fields['codice_vendite_int'] = $rets["referente_codice"];
					$focus->column_fields['area_mng_no'] = $rets["agent_cod_capoarea"];
					$focus->column_fields['area_mng_name'] = $rets["capoarea_first_name"] . " ".$rets["capoarea_last_name"]  ;
					$focus->column_fields['account_line'] = $rets["user_line"];
				}
			}
			$log->debug("handleEvent vtiger.entity.beforesave treminated");
		}

		if($eventName == 'vtiger.entity.aftersave') {
			$log->debug("handleEvent vtiger.entity.aftersave entered");
			
			if($data->isNew())
			{
				$log->debug("handleEvent vtiger.entity.aftersave this is an insert");
			} else {
				$log->debug("handleEvent vtiger.entity.aftersave this is an update");
			}
			$log->debug("handleEvent vtiger.entity.aftersave terminated");
		}
	}
	
	//danzi.tn@20150107
	function _get_default_user_data($user_id) {
		global $adb,  $table_prefix;
		$result1 = $adb->pquery("SELECT	{$table_prefix}_users.user_name,
										{$table_prefix}_users.erp_code,
										{$table_prefix}_users.agent_cod_capoarea,
										capoarea.id AS capoarea_id,
										capoarea.user_name AS capoarea_user_name,
										capoarea.first_name AS capoarea_first_name,
										capoarea.last_name AS capoarea_last_name,
										{$table_prefix}_users.referente_codice,
										{$table_prefix}_users.referente_nome,
										CASE WHEN {$table_prefix}_users.user_line IS NULL THEN '---' ELSE {$table_prefix}_users.user_line END AS user_line
										FROM {$table_prefix}_users
										LEFT JOIN {$table_prefix}_users capoarea ON capoarea.erp_code =  {$table_prefix}_users.agent_cod_capoarea
										WHERE
										{$table_prefix}_users.id = ? AND 
										{$table_prefix}_users.status = ?",array($user_id,'Active'));
		$capoarea_id = '';
		$erp_code = '';
		$referente_nome = '';
		$referente_codice = '';
		$agent_cod_nome = '';
		$agent_cod_capoarea = ''; // area_mng_no
		$capoarea_user_name = '';
		$capoarea_first_name = '';
		$capoarea_last_name = '';
		$user_line = '';
		if ($result1 && $adb->num_rows($result1)) {
			$capoarea_id = $adb->query_result($result1,0,'capoarea_id'); //capoarea_id
			$erp_code = $adb->query_result($result1,0,'erp_code'); //va in agent_number
			$referente_nome = $adb->query_result($result1,0,'referente_nome'); //va in ref_vendite_int
			$referente_codice = $adb->query_result($result1,0,'referente_codice'); //va in codice_vendite_int
			$agent_cod_nome = $adb->query_result($result1,0,'agent_cod_nome'); //agent_cod_nome
			$agent_cod_capoarea = $adb->query_result($result1,0,'agent_cod_capoarea'); //va in area_mng_no
			$capoarea_user_name = $adb->query_result($result1,0,'capoarea_user_name'); //capoarea_user_name
			$capoarea_first_name = $adb->query_result($result1,0,'capoarea_first_name'); //va in area_mng_name
			$capoarea_last_name = $adb->query_result($result1,0,'capoarea_last_name'); //va in area_mng_name
			$user_line = $adb->query_result($result1,0,'user_line'); //user_line
		}
		return array('capoarea_id'=>$capoarea_id,
					 'referente_nome'=>$referente_nome,
					 'erp_code'=>$erp_code,
					 'referente_codice'=>$referente_codice,
					 'agent_cod_nome'=>$agent_cod_nome,
					 'agent_cod_capoarea'=>$agent_cod_capoarea,
					 'capoarea_user_name'=>$capoarea_user_name,
					 'capoarea_first_name'=>$capoarea_first_name,
					 'capoarea_last_name'=>$capoarea_last_name,
					 'user_line'=>$user_line
					 );
	}
	//danzi.tn@20150107e
	
	
	
}
?>
