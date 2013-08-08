<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
class RelationsHandler extends VTEventHandler {

	function handleEvent($eventName, $data) {
		global $adb, $current_user,$log;
		global $table_prefix;
		// check irs a timcard we're saving.
		if (!($data->focus instanceof Relations)) {
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
			$log->debug("handleEvent vtiger.entity.aftersave relation_name = ".$focus->column_fields['relation_name']);
			$log->debug("handleEvent vtiger.entity.aftersave group_name = ".$focus->column_fields['group_name']);
			$log->debug("handleEvent vtiger.entity.aftersave link_to = ".$focus->column_fields['link_to']);
			$log->debug("handleEvent vtiger.entity.aftersave link_from = ".$focus->column_fields['link_from']);
			$log->debug("handleEvent vtiger.entity.aftersave smownerid = ".$focus->column_fields['smownerid']);
			$log->debug("handleEvent vtiger.entity.aftersave assigned_user_id = ".$focus->column_fields['assigned_user_id']);
			if($data->isNew())
			{
				$log->debug("handleEvent vtiger.entity.aftersave this is an insert");
				$newRel = CRMEntity::getInstance('Relations'); //$adb->getUniqueID($table_prefix."_crmentity");??
				vtlib_setup_modulevars($module,$newRel);
				$newRel->column_fields['group_name']=$focus->column_fields['group_name'];
				$newRel->column_fields['description']=$focus->column_fields['description'] . " - (R Auto-generated)";
				$newRel->column_fields['link_from']=$focus->column_fields['link_to'];
				$newRel->column_fields['link_to']=$focus->column_fields['link_from'];
				$newRel->column_fields['link_from_category']=$focus->column_fields['link_to_category'];
				$newRel->column_fields['link_to_category']=$focus->column_fields['link_from_category'];
				$newRel->column_fields['relation_state']=$focus->column_fields['relation_state'];
				if($focus->column_fields['relation_name'] == 'lbl_rel_same_family') {
						$newRel->column_fields['relation_name']='lbl_rel_same_family';
				} 
				elseif($focus->column_fields['relation_name'] == 'lbl_rel_same_company') {
						$newRel->column_fields['relation_name']='lbl_rel_same_company';
				}
				elseif($focus->column_fields['relation_name'] == 'lbl_rel_b2b') {
						$newRel->column_fields['relation_name']='lbl_rel_b2b';
				}
				elseif($focus->column_fields['relation_name'] == 'lbl_rel_association') {
						$newRel->column_fields['relation_name']='lbl_rel_association';
				}
				elseif($focus->column_fields['relation_name'] == 'lbl_rel_other') {
						$newRel->column_fields['relation_name']='lbl_rel_other';
				}
				elseif($focus->column_fields['relation_name'] == 'lbl_rel_affiliation') {
						$newRel->column_fields['relation_name']='lbl_rel_affiliation';
				}
				elseif($focus->column_fields['relation_name'] == 'lbl_rel_supply_from') {
						$newRel->column_fields['relation_name']='lbl_rel_supply_to';
				}
				elseif($focus->column_fields['relation_name'] == 'lbl_rel_supply_to') {
						$newRel->column_fields['relation_name']='lbl_rel_supply_from';
				}
				elseif($focus->column_fields['relation_name'] == 'lbl_rel_supplyfromco') {
						$newRel->column_fields['relation_name']='lbl_rel_supplytocomp';
				}
				elseif($focus->column_fields['relation_name'] == 'lbl_rel_supplytocomp') {
						$newRel->column_fields['relation_name']='lbl_rel_supplyfromco';
				}
				elseif($focus->column_fields['relation_name'] == 'lbl_rel_owned_to') {
						$newRel->column_fields['relation_name']='lbl_rel_owned_from';
				}
				elseif($focus->column_fields['relation_name'] == 'lbl_rel_owned_from') {
						$newRel->column_fields['relation_name']='lbl_rel_owned_to';
				}
				elseif($focus->column_fields['relation_name'] == 'lbl_rel_connected_to') {
						$newRel->column_fields['relation_name']='lbl_rel_connected_to';
				}
				elseif($focus->column_fields['relation_name'] == 'lbl_rel_subcontracting_from') {
						$newRel->column_fields['relation_name']='lbl_rel_subcontracting_to';
				}
				elseif($focus->column_fields['relation_name'] == 'lbl_rel_member_of') {
						$newRel->column_fields['relation_name']='lbl_rel_has_mem';
				}
				elseif($focus->column_fields['relation_name'] == 'lbl_rel_has_mem') {
						$newRel->column_fields['relation_name']='lbl_rel_member_of';
				}
				elseif($focus->column_fields['relation_name'] == 'lbl_rel_pres_of') {
						$newRel->column_fields['relation_name']='lbl_rel_has_pres';
				}
				elseif($focus->column_fields['relation_name'] == 'lbl_rel_has_pres') {
						$newRel->column_fields['relation_name']='lbl_rel_pres_of';
				}
				else {
					$newRel->column_fields['relation_name']=$focus->column_fields['relation_name'];
				}
				$newRel->column_fields['smownerid']=$focus->column_fields['assigned_user_id'];
				$newRel->column_fields['assigned_user_id']=$focus->column_fields['assigned_user_id'];
				$newRel->column_fields['createdtime']=$focus->column_fields['createdtime'];
				$newRel->column_fields['modifiedtime']=$focus->column_fields['modifiedtime'];
				$newRel->save($module_name='Relations',$longdesc=false,$offline_update=false,$triggerEvent=false);
			} else {
				$log->debug("handleEvent vtiger.entity.aftersave this is an update");
			}
			$log->debug("handleEvent vtiger.entity.aftersave terminated");
		}
	}
}
?>
