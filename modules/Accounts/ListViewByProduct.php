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
require_once 'include/ListView/ListViewByProductController.php';
require_once('modules/CustomView/CustomView.php');
require_once('include/DatabaseUtil.php');

$cf_category = 'cf_803';
$filter_type = $_REQUEST['filter_type'];
$filter_value = $_REQUEST['filter_value'];
$startdate = $_REQUEST['startdate'];
$enddate = $_REQUEST['enddate'];
$amountrange = $_REQUEST['amountrange'];

// if(isset($filter_type) && $filter_type!="" ) echo "<!-- parametri= $filter_type $filter_value $startdate $enddate $amountrange -->";


if ($_REQUEST['calc_nav'] == 'true'){
	//Retreive the List View Table Header
	$customView = new CustomView($currentModule);
	$viewid = $customView->getViewId($currentModule);
	if($viewid !='')
	$url_string .= "&viewname=".$viewid;
	if ($_REQUEST['get_all_ids'] == 'true'){
		echo '&#&#&#';
		echo get_allids_by_product($_SESSION[$currentModule.'_listquery'],$_REQUEST['ids_to_jump']);
	}
	else{
		echo '&#&#&#';
		echo get_navigation_values_by_product($_SESSION[$currentModule.'_listquery'],$url_string,$currentModule,'',false,$viewid);
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

$controller = ListViewByProductController::getInstance($adb, $current_user, $queryGenerator);

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

//danzi.tn@20130207
$smarty->assign("JS_DATEFORMAT",parse_calendardate($app_strings['NTC_DATE_FORMAT']));
$calendar_format = parse_calendardate($app_strings['NTC_DATE_FORMAT']);
$extra_where_clause =" AND {$table_prefix}_crmentity_sales.deleted=0";

if(isset($filter_type) && $filter_type!="" )
{
	$stdvaluefiltershtml = array(
				'nd'=> array('selected'=>($filter_type=='nd'?'selected':''),'value'=>'nd','text'=>$mod_strings['LBL_ND']),
				'cat'=>array('selected'=>($filter_type=='cat'?'selected':''),'value'=>'cat','text'=>$mod_strings['LBL_CAT']),
				'prod'=>array('selected'=>($filter_type=='prod'?'selected':''),'value'=>'prod','text'=>$mod_strings['LBL_PROD'])
				);
}
else
{
	$stdvaluefiltershtml = array('nd'=> array('selected'=>'selected','value'=>'nd','text'=>$mod_strings['LBL_ND']),"cat"=>array('selected'=>'','value'=>'cat','text'=>$mod_strings['LBL_CAT']),'prod'=>array('selected'=>'','value'=>'prod','text'=>$mod_strings['LBL_PROD']));
}
$valueIdValue = '';
if(isset($filter_value) && $filter_value!="" && $filter_value!="ND" )
{
	$valueIdValue = $filter_value;
	if( isset($filter_type) && $filter_type!="" )	{
		if( $filter_type=='prod' ) {
			$extra_where_clause .= " AND {$table_prefix}_products.base_no LIKE '$filter_value%'";
		} else {
			$extra_where_clause .= " AND {$table_prefix}_productcf.{$cf_category} LIKE '$filter_value%'";
		}
	}
}
$smarty->assign("STDVALUEFILTERS",$stdvaluefiltershtml);
$smarty->assign("valueIdValue",$valueIdValue);

if(isset($startdate) && $startdate!="" && isset($enddate) && $enddate!="" )
{
	$db = PearDatabase::getInstance();
	$smarty->assign("STARTDATE",$startdate);
	$smarty->assign("ENDDATE",$enddate);
	$extra_where_clause .= " AND data_ordine_ven BETWEEN ".$db->quote(getDBInsertDateValue($startdate))." AND ".$db->quote(getDBInsertDateValue($enddate));
}
if(isset($amountrange) && $amountrange!="" )
{
	$smarty->assign("amountrangevalue",$amountrange);
	$amountrange_splitted = explode("-",$amountrange);
	if( count($amountrange_splitted) > 1 ){
		$amount_where_clause = " sum({$table_prefix}_inventoryproductrel.listprice*{$table_prefix}_inventoryproductrel.quantity) BETWEEN 1000*". $amountrange_splitted[0] . " AND 1000*". $amountrange_splitted[1];
	} else {
		$amount_where_clause = " sum({$table_prefix}_inventoryproductrel.listprice*{$table_prefix}_inventoryproductrel.quantity) BETWEEN  0 AND 1000*". $amountrange_splitted[1];
	}
}

$extra_clause_columns = ", sum({$table_prefix}_inventoryproductrel.listprice*{$table_prefix}_inventoryproductrel.quantity) as cf_1078";
$select_clause_columns = $queryGenerator->getSelectClauseColumnSQL();
$extra_clause_columns = $select_clause_columns  . $extra_clause_columns ;

$extra_from_clause = "
INNER JOIN {$table_prefix}_salesorder ON {$table_prefix}_account.accountid = {$table_prefix}_salesorder.accountid
INNER JOIN {$table_prefix}_crmentity AS {$table_prefix}_crmentity_sales ON {$table_prefix}_salesorder.salesorderid = {$table_prefix}_crmentity_sales.crmid  
LEFT JOIN {$table_prefix}_inventoryproductrel on {$table_prefix}_salesorder.salesorderid = {$table_prefix}_inventoryproductrel.id  
LEFT JOIN {$table_prefix}_products on {$table_prefix}_products.productid = {$table_prefix}_inventoryproductrel.productid ";
if(isset($filter_value) && $filter_value!="" && $filter_value!="ND"  && isset($filter_type) && $filter_type=='cat'){
	$extra_from_clause .= " LEFT JOIN {$table_prefix}_productcf on {$table_prefix}_productcf.productid = {$table_prefix}_inventoryproductrel.productid";
}
$select_from_clause = $queryGenerator->getFromClause();
$extra_from_clause = $select_from_clause . $extra_from_clause;


$extra_group_by_clause =" GROUP BY " .$select_clause_columns;

//danzi.tn@20130207 e



$where = $queryGenerator->getConditionalWhere();
//crmv@7634
if(isset($_REQUEST['lv_user_id'])) {
	$_SESSION['lv_user_id'] = $_REQUEST['lv_user_id'];
} else {
	$_REQUEST['lv_user_id'] = $_SESSION['lv_user_id'];
}
$smarty->assign("LV_USER_PICKLIST",getUserOptionsHTML($_REQUEST['lv_user_id'],$currentModule,""));
$smarty->assign("PRODUCT_CATEGORY_TREE",getProductCategoryTree($cf_category));
// $smarty->assign("PRODUCT_CATEGORY_TREE","<p>pippo</p>");
if( $_REQUEST['lv_user_id'] == "all" || $_REQUEST['lv_user_id'] == "") { // all event (normal rule)

} else if ( $_REQUEST['lv_user_id'] == "mine") { // only assigned to me
	$list_where .= " and {$table_prefix}_crmentity.smownerid = ".$current_user->id." ";
} else if ( $_REQUEST['lv_user_id'] == "others") { // only assigneto others
	$list_where .= " and {$table_prefix}_crmentity.smownerid <> ".$current_user->id." ";
} else { // a selected userid
	$list_where .= " and {$table_prefix}_crmentity.smownerid = ".$_REQUEST['lv_user_id']." ";
}
$list_query.=$list_where . $extra_where_clause;
$where.=$list_where;
//crmv@7634e

$list_query = str_replace($select_clause_columns,$extra_clause_columns,$list_query );
$list_query = str_replace($select_from_clause,$extra_from_clause,$list_query );
$list_query .= $extra_group_by_clause;

if(isset($where) && $where != '') {
	$_SESSION['export_where'] = $where;
} else {
	unset($_SESSION['export_where']);
}
if(!empty($order_by) && $order_by != '' && $order_by != null) {
	if($order_by == 'smownerid') $new_order_by = ' ORDER BY user_name '.$sorder;
	else {
		$new_order_by = $focus->getFixedOrderBy($currentModule,$order_by,$sorder); //crmv@25403
	}
}
//crmv@11597

if(isset($amount_where_clause) && $amount_where_clause!="")
{
	$list_query .= " HAVING " . $amount_where_clause;
}
$list_query_without_order_by = $list_query;
if(isset($new_order_by) && $new_order_by != "")
{
	$list_query .= $new_order_by;
}
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
		$res = $adb->query(replaceSelectQueryByProduct($list_query,'count(*) as cnt'));
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
$smarty->assign('CUSTOM_MODULE', false);
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
// crmv@30967e $extra_field_sum
//danzi.tn@20130212
echo "<!-- LISTQUERY ".$list_query." -->";
//danzi.tn@20130212 e
$_SESSION[$currentModule.'_listquery'] = $list_query_without_order_by;
// Gather the custom link information to display
include_once('vtlib/Vtiger/Link.php');
$customlink_params = Array('MODULE'=>$currentModule, 'ACTION'=>vtlib_purify($_REQUEST['action']), 'CATEGORY'=> $category);
$smarty->assign('CUSTOM_LINKS', Vtiger_Link::getAllByType(getTabid($currentModule), Array('LISTVIEWBASIC','LISTVIEW'), $customlink_params));
// END
if(isset($_REQUEST['ajax']) && $_REQUEST['ajax'] != '')
{
	echo "<code>ajax</code>";
	$smarty->display("ListViewEntriesByProduct.tpl");
}
else
{
	$smarty->display('ListViewByProduct.tpl');
}

function replaceSelectQueryByProduct($list_query_count,$parameter)

{
	return "SELECT " . $parameter . " FROM (".$list_query_count.") AS TOTSALES";
}

function get_allids_by_product($list_query_count,$ids_to_jump = false){
	require_once('include/ListView/ListView.php');
	global $adb,$app_strings,$list_max_entries_per_page,$currentModule,$current_user, $table_prefix;	//crmv@27096
	$parameter = $table_prefix."_crmentity.crmid";
	if (!$list_query_count)
		return Zend_Json::encode(Array('all_ids'=>false));
	//crmv@27096
	$mod_obj = CRMEntity::getInstance($currentModule);
	$mod_obj->getNonAdminAccessControlQuery($currentModule,$current_user);
	//crmv@27096e
	$query = str_replace("SELECT","SELECT ".$parameter. ",",$list_query_count );
	$query = str_replace("GROUP BY","GROUP BY ".$parameter. ",",$query );
	if ($ids_to_jump){
		$ids_to_jump = array_filter(explode(",",$ids_to_jump));
		$query.=" and crmid not in (".implode(",",$ids_to_jump).")";
	}
	$res = $adb->query($query);
	//crmv@27096
	$all_ids = array();
	if ($res){
		while($row = $adb->fetchByAssoc($res)){
			$all_ids[] = $row['crmid'];
		}
	}
	saveListViewCheck($currentModule,$all_ids);
	return Zend_Json::encode(Array('all_ids'=>implode(';',$all_ids).';'));
	//crmv@27096e
}

function get_navigation_values_by_product($list_query_count,$url_string,$currentModule,$type='',$forusers=false,$viewid = ''){	
	//crmv@17613
	global $adb,$app_strings,$list_max_entries_per_page,$current_user;
	$parameter = 'count(*) as cnt';
	if (!$list_query_count)
		return Zend_Json::encode(Array('nav_array'=>Array(),'rec_string'=>''));
	if (!$forusers){
		$mod_obj = CRMEntity::getInstance($currentModule);
		$mod_obj->getNonAdminAccessControlQuery($currentModule,$current_user);
	}
	//crmv@17613 end
	$new_query = replaceSelectQueryByProduct($list_query_count,$parameter); // DA RIVEDERE
	//echo "<!-- NEWQUERY ".$new_query." -->";
	$res = $adb->query($new_query);
	if ($res){
		$noofrows = $adb->query_result($res,0,'cnt');
	}
	//crmv@29617
	if ($viewid != '') {
		$reload_notification_count = checkListNotificationCount($list_query_count,$current_user->id,$viewid,$noofrows);
	}
	//crmv@29617e
	$_REQUEST['noofrows'] = $noofrows;
	$_SESSION["lvs"][$currentModule][$viewid]["noofrows"] = $noofrows;
	if(isPermitted($currentModule,'EditView','') == 'yes')
		$permitted = true;
	else
		$permitted = false;
	if ($noofrows == 0)
		return Zend_Json::encode(Array('nav_array'=>Array(),'rec_string'=>'','permitted'=>$permitted));
	$list_max_entries_per_page=get_selection_options($noofrows,'list');
	$queryMode = (isset($_REQUEST['query']) && $_REQUEST['query'] == 'true');
	$start = ListViewSession::getRequestCurrentPage($currentModule, $list_query_count, $viewid, $queryMode);
	//crmv@15530
	if ($start > ceil($noofrows/$list_max_entries_per_page)){
		$start-=1;
	}
	//crmv@15530 end
	$navigation_array = VT_getSimpleNavigationValues($start,$list_max_entries_per_page,$noofrows);
	$limit_start_rec = ($start-1) * $list_max_entries_per_page;
	$record_string = getRecordRangeMessage($list_max_entries_per_page, $limit_start_rec,$noofrows);
	if ($noofrows >  $list_max_entries_per_page)
		$navigationOutput = getTableHeaderSimpleNavigation($navigation_array,$url_string,$currentModule,$type,$viewid);
	else
		$navigationOutput = Array();
	return Zend_Json::encode(Array('nav_array'=>$navigationOutput,'rec_string'=>$record_string,'permitted'=>$permitted,'reload_notification_count'=>$reload_notification_count));	//crmv@29617
}

function getProductCategoryTree($cf_category)
{
	global $adb, $table_prefix;
	$tree_string="";
	$query = "SELECT DISTINCT class3 as categorycode, class1 as parentlevel1, class2 as parentlevel2, class_desc3 as categorydescr, class_desc1, class_desc2 
	FROM erp_temp_crm_classificazioni , {$table_prefix}_productcf
	WHERE erp_temp_crm_classificazioni.class3 = LEFT({$table_prefix}_productcf.{$cf_category},8)
	ORDER BY parentlevel1 ASC, parentlevel2 ASC, categorycode ASC ";
	
	$result = $adb->query($query);
	$i_count = 0;
	$i_count1 = 0;
	$i_count2 = 0;
	$i_count3 = 0;
	$s_level1 = "x96x";
	$s_level2 = "x96x";
	$s_level3 = "x96x";
	while($row=$adb->fetchByAssoc($result))
	{
		if($i_count1==0) $tree_string.="<ul>\n";
		if($row['parentlevel1']!=$s_level1)
		{
			if($i_count1>0) $tree_string.="\t\t\t</ul>\n\t\t</li>\n\t</ul>\n\t</li>\n";
			$i_count2=0;
			$s_level1=$row['parentlevel1'];
			$s_desclevel1=$row['class_desc1'];
			$tree_string.="\t<li title=\"".$s_desclevel1."\"  id=\"".$s_level1."\"><a title=\"".$s_desclevel1."\" href=\"#\">".$s_level1." (".$s_desclevel1.")</a>\n";
			$i_count1++;
		}
		if($i_count2==0) $tree_string.="\t<ul>\n";
		if($row['parentlevel2']!=$s_level2)
		{
			if($i_count2>0) $tree_string.="\t\t\t</ul>\n\t\t</li>\n";
			$i_count3=0;
			$s_level2=$row['parentlevel2'];
			$s_desclevel2=$row['class_desc2'];
			$tree_string.="\t\t<li title=\"".$s_desclevel2."\" id=\"".$s_level2."\"><a title=\"".$s_desclevel2."\"  href=\"#\">".$s_level2." (".$s_desclevel2.")</a>\n";
			$i_count2++;
		}
		if($i_count3==0) $tree_string.="\t\t\t<ul>\n";
		$tree_string.="\t\t\t\t<li title=\"".$row['categorydescr']."\" id=\"".$row['categorycode']."\"><a title=\"".$row['categorydescr']."\" href=\"#\">".$row['categorycode']." (".$row['categorydescr'].")</a></li>\n";
		$i_count3++;
	}
	 $tree_string.="\t\t\t</ul>\n\t\t</li>\n\t</ul>\n\t</li>\n";
	 $tree_string.="</ul>\n	";
	return $tree_string;
}

?>
