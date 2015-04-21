<?php 
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is crmvillage.biz.
 * Portions created by crmvillage.biz are Copyright (C) crmvillage.biz.
 * All Rights Reserved.
 *******************************************************************************/
 //danzi.tn@20150421 aggiunto il criterio n 7 not includes
class Conditionals {
	function Conditionals($module='',$tabid='',$column_fields='')	{
		if ($module == '' && $tabid == '' && $column_fields == '' )
			return;
		global $current_language,$adb,$current_user,$table_prefix;
		/*$this->log = LoggerManager::getLogger('Conditionals');
		$this->log->debug("Entering Conditionals() method ...");
		$this->log->debug("Exiting Conditionals method ..."); */
		//costruisco le condizioni in base a ruolo, ruolo e subordinati,gruppi.
		//ruolo:
		$conditions[] = "roles::".$current_user->roleid;
		//ruoli e subordinati:
		$subordinates=getRoleAndSubordinatesInformation($current_user->roleid);
		$parent_role=$subordinates[$current_user->roleid][1];
		if (!is_array($parent_role)){
			$parent_role = explode('::',$parent_role);
			foreach ($parent_role as $parent_role_value){
				$conditions[] = "rs::".$parent_role_value;
			}
		}
		//gruppi:
		require('user_privileges/user_privileges_'.$current_user->id.'.php');
		if (is_array($current_user_groups)){
			foreach ($current_user_groups as $current_user_groups_value){
				$conditions[] = "groups::".$current_user_groups_value;
			}
		}
		//tutti:
		$conditions[] = 'ALL';
		$sql = "SELECT tbl_s_conditionals_rules.ruleid,
			tbl_s_conditionals_rules.chk_fieldname,
			tbl_s_conditionals_rules.chk_criteria_id,
			tbl_s_conditionals_rules.chk_field_value
			FROM tbl_s_conditionals 
			LEFT JOIN tbl_s_conditionals_rules ON tbl_s_conditionals.ruleid = tbl_s_conditionals_rules.ruleid 
			left join ".$table_prefix."_field ON (".$table_prefix."_field.fieldid = tbl_s_conditionals.fieldid OR tbl_s_conditionals_rules.chk_fieldname = ".$table_prefix."_field.fieldname)
			WHERE tbl_s_conditionals.active = 1 
			and ".$table_prefix."_field.tabid = ?
			and ".$table_prefix."_field.fieldname in (".generateQuestionMarks($column_fields).")
			and tbl_s_conditionals.role_grp_check in (".generateQuestionMarks($conditions).")
			group by tbl_s_conditionals_rules.ruleid,
			tbl_s_conditionals_rules.chk_fieldname,
			tbl_s_conditionals_rules.chk_criteria_id,
			tbl_s_conditionals_rules.chk_field_value order by tbl_s_conditionals_rules.ruleid";
		$params[] = $tabid;
		$params[] = array_keys($column_fields);
		$params[] = $conditions;
		$res = $adb->pquery($sql,$params);
		$rule_check = false;
		$rule_success = true;
		if ($res && $adb->num_rows($res)>0){
			//per ogni regola controllo se le condizioni sono TUTTE soddisfatte
			while ($row = $adb->fetchByAssoc($res,-1,false)){
				if ($rule_check && $rule_check != $row['ruleid']){
					if ($rule_success){
						$rules[] = $rule_check;
					}
					$rule_success = true;
				}
				$rule_check = $row['ruleid'];
				$moduleFieldValue = getTranslatedString($column_fields[$row['chk_fieldname']],$module);
				$chk_field_value = getTranslatedString($row['chk_field_value'],$module);
				if (!$this->check_rule($row['chk_criteria_id'],$moduleFieldValue,$chk_field_value)){
					$rule_success = false;
				}
			}
			if ($rule_success){
				$rules[] = $rule_check;
			}
		}
		if (is_array($rules)){
			$sql_permissions = "select fieldid,min(read_perm) as read_perm,min(write_perm) as write_perm,min(mandatory) as mandatory 
					from tbl_s_conditionals where ruleid in (".generateQuestionMarks($rules).") group by fieldid";
			$res_permissions = $adb->pquery($sql_permissions,$rules);
			if ($res_permissions && $adb->num_rows($res_permissions)>0){
				while ($row_permissions = $adb->fetchByAssoc($res_permissions,-1,false)){
					$this->permissions[$row_permissions['fieldid']] = Array(
						'f2fp_visible'=>$row_permissions['read_perm'],
						'f2fp_editable'=>$row_permissions['write_perm'],
						'f2fp_mandatory'=>$row_permissions['mandatory'],
					);
				}
			}
		}
	}
    //danzi.tn@20150421 aggiunto il criterio n 7 not includes
	function check_rule($criteriaID,$moduleFieldValue,$criteriaFieldValue){
		$criteriaPassed = false;
		switch ($criteriaID){
			case 0:
				// <=
				$criteriaPassed = ($moduleFieldValue <= $criteriaFieldValue);
				break;
			case 1:
				// <
				$criteriaPassed = ($moduleFieldValue < $criteriaFieldValue);
				break;
			case 2:
				// >=
				$criteriaPassed = ($moduleFieldValue >= $criteriaFieldValue);
				break;
			case 3:
				// >
				$criteriaPassed = ($moduleFieldValue > $criteriaFieldValue);
				break;
			case 4:
				// ==
				$criteriaPassed = ($moduleFieldValue == $criteriaFieldValue);
				break;
			case 5:
				// !=
				$criteriaPassed = ($moduleFieldValue != $criteriaFieldValue);
				break;
			case 6:
				// includes
				$criteriaPassed = (stristr($moduleFieldValue, $criteriaFieldValue) !== false);
				break;
			case 7:
				// not includes
				$criteriaPassed = (stristr($moduleFieldValue, $criteriaFieldValue) === FALSE);
				break;
		}
		return $criteriaPassed;
	}	
 	/**
	* Invoked when special actions are performed on the module.
	* @param String Module name
	* @param String Event Type
	*/
	function vtlib_handler($moduleName, $eventType) {
 					
		require_once('include/utils/utils.php');			
		global $adb,$mod_strings,$table_prefix;
 		
 		if($eventType == 'module.postinstall') {			
			// Mark the module as Standard module
			$adb->pquery('UPDATE '.$table_prefix.'_tab SET customized=0 WHERE name=?', array($moduleName));
			
			$fieldid = $adb->getUniqueID($table_prefix.'_settings_field');
			$blockid = getSettingsBlockId('LBL_STUDIO');
			$seq_res = $adb->pquery("SELECT max(sequence) AS max_seq FROM ".$table_prefix."_settings_field WHERE blockid = ?", array($blockid));
			if ($adb->num_rows($seq_res) > 0) {
				$cur_seq = $adb->query_result($seq_res, 0, 'max_seq');
				if ($cur_seq != null)	$seq = $cur_seq + 1;
			}
			
			$adb->pquery('INSERT INTO '.$table_prefix.'_settings_field(fieldid, blockid, name, iconpath, description, linkto, sequence) 
				VALUES (?,?,?,?,?,?,?)', array($fieldid, $blockid, 'LBL_COND_MANAGER', 'workflow.gif', 'LBL_COND_MANAGER_DESCRIPTION', 'index.php?module=Conditionals&action=index&parenttab=Settings', $seq));
					
			
		} else if($eventType == 'module.disabled') {
		// TODO Handle actions when this module is disabled.
		} else if($eventType == 'module.enabled') {
		// TODO Handle actions when this module is enabled.
		} else if($eventType == 'module.preuninstall') {
		// TODO Handle actions when this module is about to be deleted.
		} else if($eventType == 'module.preupdate') {
		// TODO Handle actions before this module is updated.
		} else if($eventType == 'module.postupdate') {
		// TODO Handle actions after this module is updated.
		}
 	}	
}
?>