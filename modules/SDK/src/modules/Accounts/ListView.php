<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
 ************************************************************************************/

global $app_strings, $mod_strings, $current_language, $currentModule, $theme, $table_prefix, $list_max_entries_per_page;

require_once('Smarty_setup.php');
require_once('include/ListView/ListView.php');
require_once('modules/CustomView/CustomView.php');
require_once('include/DatabaseUtil.php');
require_once('modules/SDK/src/modules/Accounts/treeUtils.php');

if ($_REQUEST['calc_nav'] == 'true'){
	//Retreive the List View Table Header
	$customView = new CustomView($currentModule);
	$viewid = $customView->getViewId($currentModule);
	if($viewid !='')
	$url_string .= "&viewname=".$viewid;
	if ($_REQUEST['get_all_ids'] == 'true'){
		echo '&#&#&#';
		echo get_allids($_SESSION[$currentModule.'_listquery'],$_REQUEST['ids_to_jump']);
	}
	else{
		echo '&#&#&#';
		echo get_navigation_values($_SESSION[$currentModule.'_listquery'],$url_string,$currentModule,'',false,$viewid);
	}
	die();
}
$category = getParentTab();
$tool_buttons = Button_Check($currentModule);
$list_buttons = Array();
$smarty = new vtigerCRM_Smarty();

// crmv@30967
if (in_array($currentModule,array('Documents','Charts','Reports'))){
	$list_buttons['back'] = $app_strings['LBL_GO_BACK'];
}
// crmv@30967e

if(isPermitted($currentModule,'Delete','') == 'yes')
	$list_buttons['del'] = $app_strings[LBL_MASS_DELETE];
if($currentModule !='Sms'){
if(isPermitted($currentModule,'EditView','') == 'yes')
	$list_buttons['mass_edit'] = $app_strings[LBL_MASS_EDIT];
}
//custom code start
if(in_array($currentModule,array('Leads','Accounts','Contacts'))){
	if(isPermitted('Emails','EditView','') == 'yes')
		$list_buttons['s_mail'] = $app_strings[LBL_SEND_MAIL_BUTTON];
	if(isPermitted('Fax','EditView','') == 'yes')
		$list_buttons['s_fax'] = $app_strings[LBL_SEND_FAX_BUTTON];
	if(isPermitted("Sms","EditView",'') == 'yes' && vtlib_isModuleActive('Sms'))	//crmv@16703
		$list_buttons['s_sms'] = $app_strings[LBL_SEND_SMS_BUTTON];
}
//custom code end
// danzi.tn@20130530
if(in_array($currentModule,array('Inspections'))){
	if(isPermitted('Emails','EditView','') == 'yes')
		$list_buttons['s_mail'] = $app_strings[LBL_SEND_MAIL_BUTTON];
}
// danzi.tn@20130530 e
if(isPermitted($currentModule,"Merge") == 'yes') {
    $wordTemplateResult = fetchWordTemplateList($currentModule);
    $tempCount = $adb->num_rows($wordTemplateResult);
    $tempVal = $adb->fetch_array($wordTemplateResult);
    for($templateCount=0;$templateCount<$tempCount;$templateCount++)
    {
        $optionString .="<option value=\"".$tempVal["templateid"]."\">" .$tempVal["filename"] ."</option>";
        $tempVal = $adb->fetch_array($wordTemplateResult);
    }
    if($tempCount > 0)
    {
        $smarty->assign("WORDTEMPLATEOPTIONS","<td>".$app_strings['LBL_SELECT_TEMPLATE_TO_MAIL_MERGE']."</td><td style=\"padding-left:5px;padding-right:5px\"><select class=\"small\" name=\"mergefile\">".$optionString."</select></td>");

        $smarty->assign("MERGEBUTTON","<td><input title=\"$app_strings[LBL_MERGE_BUTTON_TITLE]\" accessKey=\"$app_strings[LBL_MERGE_BUTTON_KEY]\" class=\"crmbutton small create\" onclick=\"return massMerge('$currentModule')\" type=\"submit\" name=\"Merge\" value=\" $app_strings[LBL_MERGE_BUTTON_LABEL]\"></td>");
    }
    else
        {
        global $current_user;
                require("user_privileges/user_privileges_".$current_user->id.".php");
                if($is_admin == true)
                {
            $smarty->assign("MERGEBUTTON",'<td><a href=index.php?module=Settings&action=upload&tempModule='.$currentModule.'&parenttab=Settings>'. $app_strings["LBL_CREATE_MERGE_TEMPLATE"].'</td>');
                }
        }
}
//crmv@18592
$view_script = "<script language='javascript'>
    function set_selected()
    {
    	obj=getObj('viewname');
        len=obj.length;
        for(i=0;i<len;i++)
        {
            if(obj[i].value == '$viewid')
                obj[i].selected = true;
        }
    }
    set_selected();
    </script>";
//crmv@18592e
// mailer_export
if (isset($list_buttons['mailer_exp'])){
  $view_script .= "<script language='javascript'>
    function mailer_export()
    {
    document.massdelete.action.value=\"MailerExport\";
    document.massdelete.step.value=\"ask\";
    window.locate=\"index.php?module=$currentModule&action=MailerExport&from=$currentModule&step=ask\";
    }
    </script>";
}
// end of mailer export
$focus = CRMEntity::getInstance($currentModule);
$focus->initSortbyField($currentModule);
// Custom View
$customView = new CustomView($currentModule);
$viewid = $customView->getViewId($currentModule);
$customview_html = $customView->getCustomViewCombo($viewid);
$viewinfo = $customView->getCustomViewByCvid($viewid);

// Feature available from 5.1
if(method_exists($customView, 'isPermittedChangeStatus')) {
	// Approving or Denying status-public by the admin in CustomView
	$statusdetails = $customView->isPermittedChangeStatus($viewinfo['status']);

	// To check if a user is able to edit/delete a CustomView
	$edit_permit = $customView->isPermittedCustomView($viewid,'EditView',$currentModule);
	$delete_permit = $customView->isPermittedCustomView($viewid,'Delete',$currentModule);

	$smarty->assign("CUSTOMVIEW_PERMISSION",$statusdetails);
	$smarty->assign("CV_EDIT_PERMIT",$edit_permit);
	$smarty->assign("CV_DELETE_PERMIT",$delete_permit);
}
// END
global $current_user;
$queryGenerator = QueryGenerator::getInstance($currentModule, $current_user);
if ($viewid != "0") {
	$queryGenerator->initForCustomViewById($viewid);
} else {
	$queryGenerator->initForDefaultCustomView();
}

$controller = ListViewController::getInstance($adb, $current_user, $queryGenerator);

// Enabling Module Search
if($_REQUEST['query'] == 'true') {
	$listview_header_search = $controller->getBasicSearchFieldInfoList();
	$_REQUEST['search_fields'] = $listview_header_search;
	$queryGenerator->addUserSearchConditions($_REQUEST);
	$ustring = getSearchURL($_REQUEST);
	$url_string .= "&query=true$ustring";
	$smarty->assign('SEARCH_URL', $url_string);
}
//<<<<<<< sort ordering >>>>>>>>>>>>>
list($focus->customview_order_by,$focus->customview_sort_order) = $customView->getOrderByFilterSQL($viewid);
$sorder = $focus->getSortOrder();
$order_by = $focus->getOrderBy();
if(!$_SESSION['lvs'][$currentModule])
{
	unset($_SESSION['lvs']);
	$modObj = new ListViewSession();
	$modObj->sorder = $sorder;
	$modObj->sortby = $order_by;
	$_SESSION['lvs'][$currentModule] = get_object_vars($modObj);
}
$_SESSION[$currentModule.'_ORDER_BY'] = $order_by;
$_SESSION[$currentModule.'_SORT_ORDER'] = $sorder;
//<<<<<<< sort ordering >>>>>>>>>>>>>
$list_query = $queryGenerator->getQuery();
$where = $queryGenerator->getConditionalWhere();
//crmv@7634
if(isset($_REQUEST['lv_user_id'])) {
	$_SESSION['lv_user_id'] = $_REQUEST['lv_user_id'];
} else {
	$_REQUEST['lv_user_id'] = $_SESSION['lv_user_id'];
}
$smarty->assign("LV_USER_PICKLIST",getUserTreeOptionsHTML($_REQUEST['lv_user_id'],$currentModule,""));

if( $_REQUEST['lv_user_id'] == "all" || $_REQUEST['lv_user_id'] == "") { // all event (normal rule)

} else if ( $_REQUEST['lv_user_id'] == "mine") { // only assigned to me
	$list_where .= " and {$table_prefix}_crmentity.smownerid = ".$current_user->id." ";
} else if ( $_REQUEST['lv_user_id'] == "others") { // only assigneto others
	$list_where .= " and {$table_prefix}_crmentity.smownerid <> ".$current_user->id." ";
} else { // a selected userid
	$list_where .= " and {$table_prefix}_crmentity.smownerid = ".$_REQUEST['lv_user_id']." ";
}

// danzi.tn@20150922 filtro per stato danzi.tn@20150825 come per lv_user_id, anche selected_agent_ids deve essere gestito lato js in ListView.js showDefaultCustomView
if(isset($_REQUEST['selected_agent_ids'])) {
	$_SESSION['selected_agent_ids'] = $_REQUEST['selected_agent_ids'];
} else {
	$_REQUEST['selected_agent_ids'] = $_SESSION['selected_agent_ids'];
}


if(isset($_REQUEST['selected_country'])) {
	$_SESSION['selected_country'] = $_REQUEST['selected_country'];
} else {
	$_REQUEST['selected_country'] = $_SESSION['selected_country'];
}

$ret_array = getUserTreeAndCountryListHTML($_REQUEST['selected_agent_ids'],$_REQUEST['selected_country'],$currentModule,"");
// danzi.tn@20151126
$smarty->assign("SELECTED_AGENT_IDS",$_REQUEST['selected_agent_ids']);
$smarty->assign("SELECTED_COUNTRY",$_REQUEST['selected_country']);
$smarty->assign("SELECTED_AGENT_IDS_DISPLAY",getDisplaySelectedUser($_REQUEST['selected_agent_ids'],$currentModule,""));
$smarty->assign("LV_COUNTRIES",$ret_array["countries"]);
$smarty->assign("LV_USER_TREE",$ret_array["users"]);


if( $_REQUEST['selected_agent_ids'] == "") { // all event (normal rule)

} else { // a selected branch of user hierarchy
	$list_where .= " and {$table_prefix}_crmentity.smownerid in ( ".$_REQUEST['selected_agent_ids']." ) ";
}

if( $_REQUEST['selected_country'] == "") { // all event (normal rule)

} else if( $currentModule == 'Accounts' ) { // a selected branch of user hierarchy
    $list_where .= " and {$table_prefix}_accountbillads.bill_country = '".$_REQUEST['selected_country']."' ";
}
// danzi.tn@20150825e

$list_query.=$list_where;
$where.=$list_where;
//crmv@7634e
if(isset($where) && $where != '') {
	$_SESSION['export_where'] = $where;
} else {
	unset($_SESSION['export_where']);
}
if(!empty($order_by) && $order_by != '' && $order_by != null) {
	if($order_by == 'smownerid') $list_query .= ' ORDER BY user_name '.$sorder;
	else {
		$list_query .= $focus->getFixedOrderBy($currentModule,$order_by,$sorder); //crmv@25403
	}
}
//crmv@11597
$smarty->assign("CUSTOMCOUNTS_OPTION", get_selection_options($noofrows));//ds@3s pulldown selection
$list_max_entries_per_page=get_selection_options($noofrows,'list');
if ($_REQUEST['ajax'] == 'true'
	&& $_REQUEST['search']!= 'true'
	&& $_REQUEST['changecount']!= 'true'
	&& $_REQUEST['changecustomview']!= 'true'){
	if ($_REQUEST['noofrows'] != '')
		$noofrows = $_REQUEST['noofrows'];
	elseif ($_SESSION["lvs"][$currentModule][$viewid]["noofrows"] != ''){
		$noofrows = $_SESSION["lvs"][$currentModule][$viewid]["noofrows"];
	}
	if ($noofrows > 0){
		$list_max_entries_per_page=get_selection_options($noofrows,'list');
		$queryMode = (isset($_REQUEST['query']) && $_REQUEST['query'] == 'true');
		$start = ListViewSession::getRequestCurrentPage($currentModule, $list_query, $viewid, $queryMode);
		$navigation_array = VT_getSimpleNavigationValues($start,$list_max_entries_per_page,$noofrows);
		$limit_start_rec = ($start-1) * $list_max_entries_per_page;
		$record_string = getRecordRangeMessage($list_max_entries_per_page, $limit_start_rec,$noofrows);
		$navigationOutput = getTableHeaderSimpleNavigation($navigation_array, $url_string,$currentModule,$type,$viewid);
		$smarty->assign("RECORD_COUNTS", $record_string);
		if ($noofrows >  $list_max_entries_per_page)
			$smarty->assign("NAVIGATION", $navigationOutput);
		$smarty->assign("AJAX", 'true');
	}
}
else {
	$queryMode = (isset($_REQUEST['query']) && $_REQUEST['query'] == 'true');
	$start = ListViewSession::getRequestCurrentPage($currentModule, $list_query, $viewid, $queryMode);
	//crmv@15530
	if ($_REQUEST['ajax'] == 'delete'){
		$res = $adb->query(replaceSelectQuery($list_query,'count(*) as cnt'));
		if ($res){
			$noofrows = $adb->query_result($res,0,'cnt');
			$_SESSION["lvs"][$currentModule][$viewid]["noofrows"] = $noofrows;
			$_REQUEST['noofrows'] = $noofrows;
			if ($start > ceil($noofrows/$list_max_entries_per_page)){
				$start-=1;
			}
		}
	}
	$limit_start_rec = ($start-1) * $list_max_entries_per_page;
	$_SESSION['lvs'][$currentModule][$viewid]['start'] = $start;
	$navigation_array['current'] = $start;
	$navigation_array['start'] = $start;
	$_REQUEST['start'] = $start;
	//crmv@15530 end
}
$list_result = $adb->limitQuery($list_query,$limit_start_rec,$list_max_entries_per_page);

if (isset($_REQUEST["selected_ids"]))
{
  $smarty->assign("SELECTED_IDS_ARRAY", explode(";",$_REQUEST["selected_ids"]));
  $smarty->assign("SELECTED_IDS", $_REQUEST["selected_ids"]);
}
if (isset($_REQUEST["all_ids"]))
{
  $smarty->assign("ALL_IDS", $_REQUEST["all_ids"]);
}
//crmv@11597 e
//crmv@10759
$smarty->assign("DATEFORMAT",$current_user->date_format);
$smarty->assign("OWNED_BY",getTabOwnedBy($currentModule));
//crmv@10759 e

$listview_header = $controller->getListViewHeader($focus,$currentModule,$url_string,$sorder, $order_by);
$listview_entries = $controller->getListViewEntries($focus,$currentModule,$list_result, $navigation_array);

// Convert field value to DetailView Link
if(isset($focus->detailview_links) && count($focus->detailview_links)) {
	foreach($listview_entries as $listview_recid=>$listview_row) {
		foreach($listview_row as $listview_key=>$listview_val) {
			$listview_key_header = $listview_header[$listview_key];
			preg_match('/(<[^>]+>)([^<]+)(<[^>]+>)/', $listview_key_header, $matches);
			$linktext = array_search(trim($matches[2], ' &nbsp;\t\r\n'), $mod_strings);
			if(in_array($linktext, $focus->detailview_links)) {
				$listview_row[$listview_key] =
					"<a href='index.php?action=DetailView&module=$currentModule&record=$listview_recid&parenttab=$category'>".$listview_val."</a>";
			}
		}
		$listview_entries[$listview_recid] = $listview_row;
	}
}
$fieldnames = $controller->getAdvancedSearchOptionString();
$criteria = getcriteria_options();
global $theme;
// Identify this module as custom module.
if (isset($focus->IsCustomModule)){
	$custom_module = true;
}else{
	$custom_module = false;
}
$smarty->assign('CUSTOM_MODULE', $custom_module);
if($viewinfo['viewname'] == 'All') $smarty->assign('ALL', 'All');
$smarty->assign("VIEWID", $viewid);
$smarty->assign('MOD', $mod_strings);
$smarty->assign('APP', $app_strings);
$smarty->assign('MODULE', $currentModule);
$smarty->assign('SINGLE_MOD', getTranslatedString('SINGLE_'.$currentModule));
$smarty->assign('CATEGORY', $category);
$smarty->assign('BUTTONS', $list_buttons);
$smarty->assign('CHECK', $tool_buttons);
$smarty->assign("THEME", $theme);
$smarty->assign('IMAGE_PATH', "themes/$theme/images/");
$smarty->assign("LISTHEADER", $listview_header);
$smarty->assign("LISTENTITY", $listview_entries);
$smarty->assign("SELECT_SCRIPT", $view_script);
$smarty->assign("AVALABLE_FIELDS", getMergeFields($currentModule,"available_fields"));
$smarty->assign("FIELDS_TO_MERGE", getMergeFields($currentModule,"fileds_to_merge"));
$smarty->assign("CRITERIA", $criteria);
$smarty->assign("FIELDNAMES", $fieldnames);
$smarty->assign("CUSTOMVIEW_OPTION",$customview_html);
// crmv@30967
$folderid = intval($_REQUEST['folderid']);
$smarty->assign("FOLDERID", $folderid);
if ($folderid > 0) {
	$folderinfo = getEntityFolder($folderid);
	$smarty->assign("FOLDERINFO", $folderinfo);
}
if ($currentModule == 'Charts') {
	$smarty->assign('HIDE_BUTTON_CREATE', true);
}
// crmv@30967e
$_SESSION[$currentModule.'_listquery'] = $list_query;
// Gather the custom link information to display
include_once('vtlib/Vtiger/Link.php');
$customlink_params = Array('MODULE'=>$currentModule, 'ACTION'=>vtlib_purify($_REQUEST['action']), 'CATEGORY'=> $category);
$smarty->assign('CUSTOM_LINKS', Vtiger_Link::getAllByType(getTabid($currentModule), Array('LISTVIEWBASIC','LISTVIEW'), $customlink_params));
// END
if(isset($_REQUEST['ajax']) && $_REQUEST['ajax'] != '')
	$smarty->display("ListViewEntries.tpl");
else
	$smarty->display('ListView.tpl');
?>
