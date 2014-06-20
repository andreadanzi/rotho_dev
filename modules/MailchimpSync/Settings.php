<?php
/*********************************************************************************
 * The contents of this file are copyright to Target Integration Ltd and are governed
 * by the license provided with the application. You may not use this file except in 
 * compliance with the License.
 * For support please visit www.targetintegration.com 
 * or email support@targetintegration.com
 * All Rights Reserved.
 *********************************************************************************/

require_once('Smarty_setup.php');
require_once("include/utils/utils.php");
require_once("modules/com_vtiger_workflow/VTWorkflowUtils.php");

global $mod_strings, $app_strings, $theme, $adb, $table_prefix;
$smarty = new vtigerCRM_Smarty;
$smarty->assign("MOD",$mod_strings);
$smarty->assign("APP",$app_strings);
$smarty->assign("THEME", "$theme");
$smarty->assign("IMAGE_PATH", "themes/$theme/images/");

// Operation to be restricted for non-admin users.
global $current_user;
if(!is_admin($current_user)) {	
	$smarty->display(vtlib_getModuleTemplate('Vtiger','OperationNotPermitted.tpl'));	
} else {
	$module = vtlib_purify($_REQUEST['formodule']);

	$menu_array = Array();
	
	//if(layout editor is permitted)
	$menu_array['LayoutEditor']['location'] = 'index.php?module=Settings&action=LayoutBlockList&parenttab=Settings&formodule='.$module;
	$menu_array['LayoutEditor']['image_src'] = vtiger_imageurl('orgshar.gif',$theme);
	$menu_array['LayoutEditor']['desc'] = getTranslatedString('LBL_LAYOUT_EDITOR_DESCRIPTION');
	$menu_array['LayoutEditor']['label'] = getTranslatedString('LBL_LAYOUT_EDITOR');
	
	if(vtlib_isModuleActive('FieldFormulas')) {
		$modules = com_vtGetModules($adb);
		if(in_array(getTranslatedString($module),$modules)) {
			$sql_result = $adb->pquery("select * from ".$table_prefix."_settings_field where name = ? and active=0",array('LBL_FIELDFORMULAS'));
			if($adb->num_rows($sql_result) > 0) {
				$menu_array['FieldFormulas']['location'] = $adb->query_result($sql_result, 0, 'linkto').'&formodule='.$module;
				$menu_array['FieldFormulas']['image_src'] = $adb->query_result($sql_result, 0, 'iconpath');
				$menu_array['FieldFormulas']['desc'] = getTranslatedString($adb->query_result($sql_result, 0, 'description'),'FieldFormulas');
				$menu_array['FieldFormulas']['label'] = getTranslatedString($adb->query_result($sql_result, 0, 'name'),'FieldFormulas');
			}
		}
	}
	
	if(vtlib_isModuleActive('Tooltip')){
		$sql_result = $adb->pquery("select * from ".$table_prefix."_settings_field where name = ? and active=0",array('LBL_TOOLTIP_MANAGEMENT'));
		if($adb->num_rows($sql_result) > 0) {
			$menu_array['Tooltip']['location'] = $adb->query_result($sql_result, 0, 'linkto').'&formodule='.$module;
			$menu_array['Tooltip']['image_src'] = vtiger_imageurl($adb->query_result($sql_result, 0, 'iconpath'), $theme);
			$menu_array['Tooltip']['desc'] = getTranslatedString($adb->query_result($sql_result, 0, 'description'),'Tooltip');
			$menu_array['Tooltip']['label'] = getTranslatedString($adb->query_result($sql_result, 0, 'name'),'Tooltip');
		}
	}
	
	if(VTWorkflowUtils::checkModuleWorkflow($module)){
		$sql_result = $adb->pquery("SELECT * FROM ".$table_prefix."_settings_field WHERE name = ? AND active=0",array('LBL_WORKFLOW_LIST'));
			if($adb->num_rows($sql_result) > 0) {
				$menu_array['Workflow']['location'] = $adb->query_result($sql_result, 0, 'linkto').'&list_module='.$module;
				$menu_array['Workflow']['image_src'] = vtiger_imageurl($adb->query_result($sql_result, 0, 'iconpath'), $theme);
				$menu_array['Workflow']['desc'] = getTranslatedString($adb->query_result($sql_result, 0, 'description'),'com_vtiger_workflow');
				$menu_array['Workflow']['label'] = getTranslatedString($adb->query_result($sql_result, 0, 'name'),'com_vtiger_workflow');
			}
	}	
	
	
	$menu_array['MailChimp']['location'] = 'index.php?module=MailchimpSync&action=MailchimpSyncSettings&parenttab=Settings&formodule='.$module;
	$menu_array['MailChimp']['image_src'] = vtiger_imageurl('custom.gif', $theme);
	$menu_array['MailChimp']['desc'] = getTranslatedString('Set up your module to enable the synchronization with MailChimp');
	$menu_array['MailChimp']['label'] = getTranslatedString('MailChimp');
	
	//add blanks for 3-column layout
	$count = count($menu_array)%3;
	if($count>0) {
		for($i=0;$i<3-$count;$i++) {
			$menu_array[] = array();
		}
	}
	
	$smarty->assign('MODULE',$module);
	$smarty->assign('MODULE_LBL',getTranslatedString($module));
	$smarty->assign('MENU_ARRAY', $menu_array);

	$smarty->display(vtlib_getModuleTemplate('Vtiger','Settings.tpl'));
}

?>