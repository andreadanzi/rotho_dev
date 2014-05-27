<?php
/*+*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
//crmv@30447

// danzi.tn@20140326 assegnazione automatica a cartella Rumors per documento proveniente da modulo Rumors
if(isset($_REQUEST['parent_id']) && isset($_REQUEST['return_module']) && $_REQUEST['return_module']=="Rumors") {
		$_REQUEST['folderid'] = 31;
}
// danzi.tn@20140326e

require_once 'modules/VteCore/EditView.php';
global $table_prefix;
if($_REQUEST['upload_error'] == true)
{
	echo '<br><b><font color="red"> '.$mod_strings['FILE_HAS_NO_DATA'].'.</font></b><br>';
}

if($focus->mode != 'edit')
{
	if(isset($_REQUEST['parent_id']) && isset($_REQUEST['return_module']))
	{
		$owner = getRecordOwnerId($_REQUEST['parent_id']);
		if(isset($owner['Users']) && $owner['Users'] != '') {
			$permitted_users = get_user_array('true', 'Active',$current_user->id);
			if(!in_array($owner['Users'],$permitted_users)){
				$owner['Users'] = $current_user->id;
			}
			$focus->column_fields['assigntype'] = 'U';
			$focus->column_fields['assigned_user_id'] = $owner['Users'];
		} elseif(isset($owner['Groups']) && $owner['Groups'] != '') {
			$focus->column_fields['assigntype'] = 'T';
			$focus->column_fields['assigned_user_id'] = $owner['Groups'];
		}
	}
}
			
if(isset($_REQUEST['parent_id']) && $focus->mode != 'edit') {
	$smarty->assign("PARENTID",vtlib_purify($_REQUEST['parent_id']));
}

$dbQuery="select filename from ".$table_prefix."_notes where notesid = ?";
$result=$adb->pquery($dbQuery,array($focus->id));
$filename=$adb->query_result($result,0,'filename');
if(is_null($filename) || $filename == '')
{
	$smarty->assign("FILE_EXIST","no");
}
else 
{
	$smarty->assign("FILE_NAME",$filename);
	$smarty->assign("FILE_EXIST","yes");
}

//setting default flag value so due date and time not required
if (!isset($focus->id)) $focus->date_due_flag = 'on';

//needed when creating a new case with default values passed in
if (isset($_REQUEST['contact_name']) && is_null($focus->contact_name)) {
	$focus->contact_name = $_REQUEST['contact_name'];
}
if (isset($_REQUEST['contact_id']) /* && is_null($focus->contact_id) */ ) {
	$focus->contact_id = $_REQUEST['contact_id'];
}
if (isset($_REQUEST['parent_name']) && is_null($focus->parent_name)) {
	$focus->parent_name = $_REQUEST['parent_name'];
}
if (isset($_REQUEST['parent_id']) /* && is_null($focus->parent_id) */ ) {
	$focus->parent_id = $_REQUEST['parent_id'];
}
if (isset($_REQUEST['parent_type'])) {
	$focus->parent_type = $_REQUEST['parent_type'];
}
elseif (!isset($focus->parent_type)) {
	$focus->parent_type = $app_list_strings['record_type_default_key'];
}

//Display the FCKEditor or not? -- configure $FCKEDITOR_DISPLAY in config.php
if(getFieldVisibilityPermission('Documents',$current_user->id,'notecontent') != '0')
        $FCKEDITOR_DISPLAY = false;
$smarty->assign("FCKEDITOR_DISPLAY",$FCKEDITOR_DISPLAY);
	
if (isset($_REQUEST['email_id']))
$smarty->assign("EMAILID", vtlib_purify($_REQUEST['email_id']));
if (isset($_REQUEST['ticket_id'])) $smarty->assign("TICKETID", vtlib_purify($_REQUEST['ticket_id']));
if (isset($_REQUEST['fileid']))
$smarty->assign("FILEID", vtlib_purify($_REQUEST['fileid']));
if (isset($_REQUEST['record']))
{
	$smarty->assign("CANCELACTION", "DetailView");
}
else
{
	$smarty->assign("CANCELACTION", "index");
}
$smarty->assign("OLD_ID", $old_id );

if ( empty($focus->filename))
{
	$smarty->assign("FILENAME_TEXT", "");
	$smarty->assign("FILENAME", "");
}
else
{
	$smarty->assign("FILENAME_TEXT", "(".$focus->filename.")");
	$smarty->assign("FILENAME", $focus->filename);
}

if (isset($focus->parent_type) && $focus->parent_type != "") {
	$change_parent_button = "<input title='".$app_strings['LBL_CHANGE_BUTTON_TITLE']."' accessKey='".$app_strings['LBL_CHANGE_BUTTON_KEY']."' vtiger_tabindex='3' type='button' class='button' value='".$app_strings['LBL_CHANGE_BUTTON_LABEL']."' name='button' LANGUAGE=javascript onclick='return window.open(\"index.php?module=\"+ document.EditView.parent_type.value + \"&action=Popup&html=Popup_picker&form=TasksEditView\",\"test\",\"width=600,height=400,resizable=1,scrollbars=1\");'>";
	$smarty->assign("CHANGE_PARENT_BUTTON", $change_parent_button);
}
if ($focus->parent_type == "Account") $smarty->assign("DEFAULT_SEARCH", "&query=true&account_id=$focus->parent_id&account_name=".urlencode($focus->parent_name));

//crmv@31069
if($focus->mode == 'edit') {
    $smarty->assign("MODE", $focus->mode);
} else {
	$smarty->assign("MODE",'create');
}
//crmv@31069e
 
if($focus->mode == 'edit')
	$smarty->display("salesEditView.tpl");
else
	$smarty->display("CreateView.tpl");
?>