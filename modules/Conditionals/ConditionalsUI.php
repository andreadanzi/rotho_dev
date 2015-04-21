<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is crmvillage.biz.
 * Portions created by crmvillage.biz are Copyright (C) crmvillage.biz.
 * All Rights Reserved.
 *******************************************************************************/
require_once('include/utils/utils.php');
//danzi.tn@20150421 aggiunto il criterio n 7 not includes
function getTransitionConditionalWorkflowModulesList() {
    global $adb;
    foreach (com_vtGetModules($adb) as $key=>$value){
    	$modules_list[] = Array($key,$key);
    }
    return $modules_list;
}
function we_checkUserRoleGrp($userobj,$roleGrpCheck) {
	if($roleGrpCheck == 'ALL') return true;
	$conditions = split("::",$roleGrpCheck);
	switch($conditions[0]) {
		case 'roles':
			return ($userobj->roleid == $conditions[1]);
			break;
		case 'rs':
			//crmv@18354			
			$subordinates=getRoleAndSubordinatesInformation($userobj->roleid);
			$parent_role=$subordinates[$userobj->roleid][1];
			$parent_rol_arr=explode('::',$parent_role);
			if(in_array($conditions[1],$parent_rol_arr)) return true;
			//crmv@18354e
			break;		
		case 'groups':
			require('user_privileges/user_privileges_'.$userobj->id.'.php');        
			
			if(sizeof($current_user_groups) > 0)
			{
	        	foreach ($current_user_groups as $grpid)
	        	{
	        		if($grpid == $conditions[1]) return true;
	        	}
			}
			return false;
			break;		
		default:
			//@todo - gestione errori
			return true;
	}
	return true;
}
 //danzi.tn@20150421 aggiunto il criterio n 7 not includes
function we_checkCriteria($criteriaID,$moduleFieldValue,$criteriaFieldValue,$roleGrpCheck="ALL") {
	global $current_user;
	$criteriaPassed = false;

	switch ($criteriaID)
	{
			case 0:
				// <=
				$criteriaPassed = ($moduleFieldValue <= $criteriaFieldValue) && we_checkUserRoleGrp($current_user,$roleGrpCheck);
				break;
			case 1:
				// <
				$criteriaPassed = ($moduleFieldValue < $criteriaFieldValue) && we_checkUserRoleGrp($current_user,$roleGrpCheck);
				break;
			case 2:
				// >=
				$criteriaPassed = ($moduleFieldValue >= $criteriaFieldValue) && we_checkUserRoleGrp($current_user,$roleGrpCheck);
				break;
			case 3:
				// >
				$criteriaPassed = ($moduleFieldValue > $criteriaFieldValue) && we_checkUserRoleGrp($current_user,$roleGrpCheck);
				break;
			case 4:
				// ==
				$criteriaPassed = ($moduleFieldValue == $criteriaFieldValue) && we_checkUserRoleGrp($current_user,$roleGrpCheck);
				break;
			case 5:
				// !=
				$criteriaPassed = ($moduleFieldValue != $criteriaFieldValue) && we_checkUserRoleGrp($current_user,$roleGrpCheck);
				break;
			case 6:
				// includes
				$criteriaPassed = (stristr($moduleFieldValue, $criteriaFieldValue) !== false) && we_checkUserRoleGrp($current_user,$roleGrpCheck);
				break;
			case 7:
				// not includes
				$criteriaPassed = (stristr($moduleFieldValue, $criteriaFieldValue) === false) && we_checkUserRoleGrp($current_user,$roleGrpCheck);
				break;
	}
	return $criteriaPassed;
}

function _wui_check_rules($result,$fieldid,$module,$column_fields) {
	global $adb;
	
	if($result && $adb->num_rows($result)>0) {
		$num_rows = $adb->num_rows($result);
		for ($k = 0; $k < $num_rows; $k++) {
			$chk_fieldname = $adb->query_result($result, $k, 'chk_fieldname');
			$chk_criteria_id = $adb->query_result($result, $k, 'chk_criteria_id');
			$chk_field_value = $adb->query_result($result, $k, 'chk_field_value');
			$chk_role_grp = $adb->query_result($result, $k, 'role_grp_check');
			if(array_key_exists($chk_fieldname,$column_fields)) {
				$moduleFieldValue = $column_fields["$chk_fieldname"];
				//crmv@9960
				$moduleFieldValue = getTranslatedString($moduleFieldValue);
				$chk_field_value = getTranslatedString($chk_field_value);
				//crmv@9960e
				if(we_checkCriteria($chk_criteria_id,$moduleFieldValue,$chk_field_value,$chk_role_grp)) {
//					if ($fieldid == '804')
//						echo "CONTINUO IL CICLO: $chk_fieldname $chk_criteria_id $chk_field_value<br />";
					continue;
				}
				else {
//					if ($fieldid == '804')
//						echo "ESCO: $chk_fieldname $chk_criteria_id $chk_field_value<br />";
					return null;
				}
			}
		}
		$read_perm  = $adb->query_result($result, 0, 'read_perm');
		$write_perm = $adb->query_result($result, 0, 'write_perm');
		$mandatory_perm = $adb->query_result($result, 0, 'mandatory');
		if($write_perm == 1) $read_perm = 1;
		
//		if ($fieldid == '825')
//			print_r(Array('f2fp_visible'=>$read_perm,'f2fp_editable'=>$write_perm,'f2fp_mandatory'=>$mandatory_perm));
		
		return Array('f2fp_visible'=>$read_perm,'f2fp_editable'=>$write_perm,'f2fp_mandatory'=>$mandatory_perm);
	}
	return null; // no rules defined - calles need to check null value
}

function wui_get_FieldPermissionsOnFieldValue($fieldid,$module,$column_fields) {
	global $adb,$current_user,$table_prefix;

	require('user_privileges/user_privileges_'.$current_user->id.'.php');        
	require('user_privileges/sharing_privileges_'.$current_user->id.'.php');
	
	$q = "SELECT DISTINCT ruleid FROM tbl_s_conditionals
			INNER JOIN ".$table_prefix."_field ON ".$table_prefix."_field.fieldid = tbl_s_conditionals.fieldid
			INNER JOIN ".$table_prefix."_tab ON ".$table_prefix."_field.tabid = ".$table_prefix."_tab.tabid
			WHERE ".$table_prefix."_tab.name = '$module'";
	$res = $adb->query($q);
	$rules_returned = array();
	if ($res && $adb->num_rows($res) > 0)
	while($row=$adb->fetchByAssoc($res,-1,false)){
	    $fix_query = "SELECT chk_fieldname,chk_criteria_id,chk_field_value,read_perm,write_perm,mandatory,role_grp_check
	    				FROM tbl_s_conditionals
	    				LEFT JOIN tbl_s_conditionals_rules ON tbl_s_conditionals.ruleid = tbl_s_conditionals_rules.ruleid
	    				WHERE tbl_s_conditionals.ruleid = ".$row['ruleid'];
		
		// check rules fro roleandsubordinates
		$parnet_role_array = split("::",$current_user_parent_role_seq);
		for($r=0;$r<count($parnet_role_array);$r++) {
			$query = $fix_query." and active = 1 and fieldid = $fieldid and role_grp_check = 'rs::".$parnet_role_array[$r]."' order by sequence asc";
			$result = $adb->query($query);
			$rules = _wui_check_rules($result,$fieldid,$module,$column_fields);	
			if($rules != null) break;
		}
		if($rules != null) {
			if ($rules['f2fp_visible'] == 1)
				$rules_returned['f2fp_visible'] = 1;
			elseif ($rules_returned['f2fp_visible'] != 1)
				$rules_returned['f2fp_visible'] = 0;
				
			if ($rules['f2fp_editable'] == 1)
				$rules_returned['f2fp_editable'] = 1;
			elseif ($rules_returned['f2fp_editable'] != 1)
				$rules_returned['f2fp_editable'] = 0;
				
			if ($rules['f2fp_mandatory'] == 1)
				$rules_returned['f2fp_mandatory'] = 1;
			elseif ($rules_returned['f2fp_mandatory'] != 1)
				$rules_returned['f2fp_mandatory'] = 0;
		}		
		
		// no rules then check for role
		$query = $fix_query." and active = 1 and fieldid = $fieldid and role_grp_check = 'roles::".$current_user->roleid."' order by sequence asc";
		$result = $adb->query($query);
		$rules = _wui_check_rules($result,$fieldid,$module,$column_fields);	
		if($rules != null) {
			if ($rules['f2fp_visible'] == 1)
				$rules_returned['f2fp_visible'] = 1;
			elseif ($rules_returned['f2fp_visible'] != 1)
				$rules_returned['f2fp_visible'] = 0;
				
			if ($rules['f2fp_editable'] == 1)
				$rules_returned['f2fp_editable'] = 1;
			elseif ($rules_returned['f2fp_editable'] != 1)
				$rules_returned['f2fp_editable'] = 0;
				
			if ($rules['f2fp_mandatory'] == 1)
				$rules_returned['f2fp_mandatory'] = 1;
			elseif ($rules_returned['f2fp_mandatory'] != 1)
				$rules_returned['f2fp_mandatory'] = 0;
		}		
		// no rules then check for groups
	    $user_groups = new GetUserGroups();
	    $user_groups->getAllUserGroups($current_user->id);	
		for($g=0;$g<count($user_groups->user_groups);$g++) {
			$query = $fix_query." and active = 1 and fieldid = $fieldid and role_grp_check = 'groups::". $user_groups->user_groups[$g]."' order by sequence asc";
			$result = $adb->query($query);
			$rules = _wui_check_rules($result,$fieldid,$module,$column_fields);	
			if($rules != null) break;
		}
		if($rules != null) {
			if ($rules['f2fp_visible'] == 1)
				$rules_returned['f2fp_visible'] = 1;
			elseif ($rules_returned['f2fp_visible'] != 1)
				$rules_returned['f2fp_visible'] = 0;
				
			if ($rules['f2fp_editable'] == 1)
				$rules_returned['f2fp_editable'] = 1;
			elseif ($rules_returned['f2fp_editable'] != 1)
				$rules_returned['f2fp_editable'] = 0;
				
			if ($rules['f2fp_mandatory'] == 1)
				$rules_returned['f2fp_mandatory'] = 1;
			elseif ($rules_returned['f2fp_mandatory'] != 1)
				$rules_returned['f2fp_mandatory'] = 0;
		}		
		// no rules -> check rules for all ------------------------------------------------------------------------------------------
		if($rules == null) {	
			$query = $fix_query." and active = 1 and fieldid = $fieldid and role_grp_check = 'ALL' order by sequence asc";
			$result = $adb->query($query);
			$rules = _wui_check_rules($result,$fieldid,$module,$column_fields);
		}
		if($rules != null) {
			if ($rules['f2fp_visible'] == 1)
				$rules_returned['f2fp_visible'] = 1;
			elseif ($rules_returned['f2fp_visible'] != 1)
				$rules_returned['f2fp_visible'] = 0;
				
			if ($rules['f2fp_editable'] == 1)
				$rules_returned['f2fp_editable'] = 1;
			elseif ($rules_returned['f2fp_editable'] != 1)
				$rules_returned['f2fp_editable'] = 0;
				
			if ($rules['f2fp_mandatory'] == 1)
				$rules_returned['f2fp_mandatory'] = 1;
			elseif ($rules_returned['f2fp_mandatory'] != 1)
				$rules_returned['f2fp_mandatory'] = 0;
		}
	}
	return $rules_returned;
}

//------------------------------------------------------------------
function wui_getFpofvListViewHeader() {
	global $mod_strings;
		
	$header = Array("","LBL_FPOFV_RULE_NAME","Module","","","","","","","","FpofvChkRoleGroup","LBL_ACTION");
	
	for($i=0;$i<count($header);$i++) {
		$translated = $mod_strings[$header[$i]];
		if($translated != "") {
			$header[$i] = $mod_strings[$header[$i]];
		}
			
	}
	
	return $header; 	
}

//------------------------------------------------------------------
function wui_getFpofvListViewEntries($fields_columnnames) {
	global $adb,$mod_strings,$app_strings,$table_prefix;
	
	$roleDetails=getAllRoleDetails();
	unset($roleDetails['H1']);
	$grpDetails=getAllGroupName();	
	$query = "select
			    distinct
				ruleid, 
				tablabel,
				description,
				role_grp_check 
				from tbl_s_conditionals 
				inner join ".$table_prefix."_field on ".$table_prefix."_field.fieldid = tbl_s_conditionals.fieldid
				inner join ".$table_prefix."_tab on ".$table_prefix."_field.tabid = ".$table_prefix."_tab.tabid
				group by ruleid, tablabel,description,role_grp_check 
				order by description";
	
	$result = $adb->query($query);
	$ret_val = Array();
	if($result && $adb->num_rows($result)>0) {
		$num_rows = $adb->num_rows($result);
		for ($k = 0; $k < $num_rows; $k++) {
			
			$ret_val[$k][1] = $adb->query_result($result, $k, 'description');
			$ret_val[$k][2] = $app_strings[$adb->query_result($result, $k, 'tablabel')];
			
			$role_grp_check = $adb->query_result($result, $k, 'role_grp_check');
			if($role_grp_check == "ALL")
				$role_grp_string = $mod_strings['NO_CONDITIONS'];
			$rolefound = false;
			foreach($roleDetails as $roleid=>$rolename)
			{
				if('roles::'.$roleid == $role_grp_check) {
					$role_grp_string = $mod_strings['LBL_ROLES']."::".$rolename[0];
			 	 	$rolefound = true;
				 	break;
				}
			}
			if(!$rolefound)
				foreach($roleDetails as $roleid=>$rolename)
				{
					if('rs::'.$roleid == $role_grp_check) {
						$role_grp_string = $mod_strings['LBL_ROLES_SUBORDINATES']."::".$rolename[0];
				 	 	$rolefound = true;
					 	break;
					}
				}
			if(!$rolefound)	
				foreach($grpDetails as $groupid=>$groupname)
				{
					if('groups::'.$groupid == $role_grp_check) {
						$role_grp_string = $mod_strings['LBL_GROUP']."::".$groupname;
				 	 	$rolefound = true;
					 	break;
					}				
				}
				
			$ret_val[$k][12] = $role_grp_string;
			
			$ruleid = $adb->query_result($result, $k, 'ruleid');
			$edit_lnk = "<a href='index.php?module=Conditionals&action=EditView&ruleid=$ruleid&parenttab=Settings'>".$app_strings['LNK_EDIT']."</a>";
			$del_lnk = "<a href='index.php?module=Conditionals&action=Delete&ruleid=$ruleid&parenttab=Settings'>".$app_strings['LNK_DELETE']."</a>";
			$ret_val[$k][13] = $edit_lnk."&nbsp;|&nbsp;".$del_lnk;
		}
	}
	return $ret_val;
}

function getRulesInfo($ruleid) {
	global $adb,$table_prefix;
	$info = array();
	
	$res = $adb->query("SELECT 
						tbl_s_conditionals.*,
						tablabel
						FROM tbl_s_conditionals 
						INNER JOIN ".$table_prefix."_field ON ".$table_prefix."_field.fieldid = tbl_s_conditionals.fieldid
						INNER JOIN ".$table_prefix."_tab ON ".$table_prefix."_field.tabid = ".$table_prefix."_tab.tabid
						where ruleid = $ruleid");
	$info = $adb->fetchByAssoc($res,-1,false);
	
	$res = $adb->query("select chk_fieldname,chk_criteria_id,chk_field_value
	    				from tbl_s_conditionals_rules
	    				where ruleid = $ruleid");
	while($row=$adb->fetchByAssoc($res,-1,false)) {
		$info['rules'][] = $row;
	}
	return $info;
}

function wui_getFpofvData($ruleid='',$module) {	
	global $adb,$mod_strings,$table_prefix;
	
	if ($ruleid == '') $ruleid = 0;
	$tabid = getTabid($module);
	$query = "select 
				tbl2.fieldid, 
				tbl2.fieldname, 
				tbl2.tablabel as module, 
				tbl2.fieldlabel, 
				 ".$adb->database->IfNull('read_perm',0)." as read_perm, 
				 ".$adb->database->IfNull('write_perm',0)." as write_perm, 
				 ".$adb->database->IfNull('mandatory',0)." as mandatory, 
				tbl2.sequence,  
				1 as active, 
				 ".$adb->database->IfNull('managed',0)." as managed,
				".$table_prefix."_blocks.blocklabel as blocklabel
				
				 from (
					select 
					tbl_s_conditionals.* , 
					".$table_prefix."_tab.tablabel, 
					".$table_prefix."_field.fieldlabel, 
					".$table_prefix."_field.fieldname, 
					1 as managed 
					from tbl_s_conditionals
						inner join ".$table_prefix."_field on tbl_s_conditionals.fieldid = ".$table_prefix."_field.fieldid
						inner join ".$table_prefix."_tab on ".$table_prefix."_field .tabid = ".$table_prefix."_tab.tabid 
					where
						ruleid = $ruleid
				) tbl1 
				right outer join ( 
					select ".$table_prefix."_field.*,  ".$table_prefix."_tab.tablabel from ".$table_prefix."_field inner join ".$table_prefix."_tab on ".$table_prefix."_field .tabid = ".$table_prefix."_tab.tabid where ".$table_prefix."_field.tabid = $tabid
				) tbl2 on tbl1.fieldid = tbl2.fieldid
				inner join ".$table_prefix."_blocks on tbl2.block = ".$table_prefix."_blocks.blockid
				order by ".$table_prefix."_blocks.sequence, tbl2.sequence";
		
	$result = $adb->query($query);
	$ret_val = Array();
	if($result) {
		for($i=0;$i<$adb->num_rows($result);$i++) {
			$ret_val[$i][FpofvFieldid] = $adb->query_result($result, $i, 'fieldid');
			$ret_val[$i][ModuleField] = $adb->query_result($result, $i, 'chk_fieldname');
			$ret_val[$i][Module] = $adb->query_result($result, $i, 'module');
			$ret_val[$i][FpovReadPermission] = $adb->query_result($result, $i, 'read_perm');
			$ret_val[$i][FpovWritePermission] = $adb->query_result($result, $i, 'write_perm');
			$ret_val[$i][FpovManaged] = $adb->query_result($result, $i, 'managed');
			$ret_val[$i][FpovMandatoryPermission] = $adb->query_result($result, $i, 'mandatory');
			$ret_val[$i][FpofvSequence] = $adb->query_result($result, $i, 'sequence');
			$ret_val[$i][FpofvActive] = $adb->query_result($result, $i, 'active');
			$ret_val[$i][FpofvBlockLabel] = $adb->query_result($result, $i, 'blocklabel');				
			$ret_val[$i][FpofvChkFieldLabel] = $adb->query_result($result, $i, 'fieldlabel');
			$ret_val[$i][FpofvChkFieldName] = $adb->query_result($result, $i, 'fieldname');		
		}
		return $ret_val;
	}
	return null;
}

//danzi.tn@20150421 aggiunto il criterio n 7 not includes
function wui_getCriteriaLabel($criteriaID) {
	global $mod_strings;
	switch ($criteriaID)
	{
		case 0:
			return $mod_strings['LBL_CRITERIA_VALUE_LESS_EQUAL'];
			// <=
			break;
		case 1:
			// <
			return $mod_strings['LBL_CRITERIA_VALUE_LESS_THAN'];				
			break;
		case 2:
			// >=
			return $mod_strings['LBL_CRITERIA_VALUE_MORE_EQUAL'];
			break;
		case 3:
			// >
			return $mod_strings['LBL_CRITERIA_VALUE_MORE_THAN'];
			break;
		case 4:
			// ==
			return $mod_strings['LBL_CRITERIA_VALUE_EQUAL'];
			break;
		case 5:
			// !=
		return $mod_strings['LBL_CRITERIA_VALUE_NOT_EQUAL'];
			break;
		case 6:
			// includes
			return $mod_strings['LBL_CRITERIA_VALUE_INCLUDES'];
			break;
		case 7:
			// not includes
			return $mod_strings['LBL_CRITERIA_VALUE_NOT_INCLUDES'];
			break;
	}
	return $criteriaID;
}

//------------------------------------------------------------------------------------------------
function wui_sql_restric_status_on_mandatory_fields($vtigerobj,$module,$fieldname,$status) {
	global $adb,$table_prefix;
	$ret_val[0] = false;
	$tabid = getTabid($module);
	$status = getTranslatedString($status,$module);	//crmv@9960		//crmv@17935
	$query = "SELECT ".$table_prefix."_field.fieldname AS module_fieldname,
			  ".$table_prefix."_field.fieldlabel     AS module_fieldlabel,
			  ".$table_prefix."_field.uitype         AS field_uitype,
			  ".$table_prefix."_field.typeofdata     AS field_typeofdata,
			  tbl_s_conditionals_rules.*
			FROM tbl_s_conditionals
			INNER JOIN ".$table_prefix."_field ON ".$table_prefix."_field.fieldid = tbl_s_conditionals.fieldid
			INNER JOIN tbl_s_conditionals_rules on tbl_s_conditionals_rules.ruleid = tbl_s_conditionals.ruleid
			WHERE chk_fieldname = '".$fieldname."' and chk_field_value = '".$status."' and mandatory = 1";
			//@todo - vincolare la query al profilo
	$index = 1;
	$result = $adb->query($query);
	if($result && $adb->num_rows($result)>0) {
		$num_rows = $adb->num_rows($result);
		for ($k = 0; $k < $num_rows; $k++) {
			$module_fieldname = $adb->query_result($result, $k, 'module_fieldname');
			$module_fieldlabel = $adb->query_result($result, $k, 'module_fieldlabel');
			$chk_fieldname = $adb->query_result($result, $k, 'chk_fieldname');
			$chk_criteria_id = $adb->query_result($result, $k, 'chk_criteria_id');
			$chk_field_value = $adb->query_result($result, $k, 'chk_field_value');
			$chk_role_grp = $adb->query_result($result, $k, 'role_grp_check');
			$field_uitype = $adb->query_result($result, $k, 'field_uitype');
			$field_typeofdata = $adb->query_result($result, $k, 'field_typeofdata');
			if(array_key_exists($chk_fieldname,$vtigerobj->column_fields)) {
				$moduleFieldValue = $vtigerobj->column_fields["$chk_fieldname"];
				//crmv@9960		//crmv@17935
				$moduleFieldValue = getTranslatedString($moduleFieldValue,$module);
				$chk_field_value = getTranslatedString($chk_field_value,$module);
				//crmv@9960e	//crmv@17935e
				if(we_checkCriteria($chk_criteria_id,$moduleFieldValue,$chk_field_value,$chk_role_grp)) {
					if (check_value_field($vtigerobj->column_fields[$module_fieldname],$field_typeofdata,$field_uitype)) {}	//crmv@17935
					else {
						$ret_val[0] = true;
						$ret_val[$index] = Array($module_fieldname,$module_fieldlabel);
						$index++;
					}
				}
			}
		}
	}
	return $ret_val;
}

function check_value_field($value,$typeofdata,$uitype){
	$type_arr = split("~",$typeofdata);
	$typeofdata = $type_arr[0];
	//crmv@17935
	if (in_array($typeofdata,Array('N','I')))
		if (ceil($value) == 0) return false;
	if (in_array($uitype,Array(15,16,111)))
		if (in_array(trim($value),array('--Nessuno--','--None--','--nd--'))) return false;
	if ($value == '')
		return false;
	//crmv@17935e
	return true;
}

//performance_conditiona_listview - i
function wui_get_FieldPermissionsOnFieldValueFields($module,$column_fields,$conditional_fieldid) {
	$rules = Array();
	foreach($conditional_fieldid as $fieldid) {
		$rules[$fieldid] = wui_get_FieldPermissionsOnFieldValue($fieldid,$module,$column_fields);
	}
	return $rules;
}

function getConditionalFields($module) {
	global $adb,$table_prefix;
	//crmv@18039
	$query = "SELECT
			  ".$table_prefix."_field.tablename,
			  ".$table_prefix."_field.columnname,
			  ".$table_prefix."_field.fieldname,
			  ".$table_prefix."_field.fieldlabel
			  FROM ".$table_prefix."_field 
			  INNER JOIN tbl_s_conditionals_rules ON ".$table_prefix."_field.fieldname = tbl_s_conditionals_rules.chk_fieldname
			  INNER JOIN ".$table_prefix."_tab
			    ON ".$table_prefix."_field.tabid = ".$table_prefix."_tab.tabid
			WHERE ".$table_prefix."_tab.name = '$module'";
	$result = $adb->query($query);
	$ret_arr = false;
	//crmv@18039 end
	while($row=$adb->fetchByAssoc($result,-1,false)){
		$ret_arr[] = $row;
	}
	return $ret_arr;
}
//performance_conditiona_listview - e
?>