<?php
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 ********************************************************************************/


require_once('include/utils/UserInfoUtil.php');
require_once("include/utils/utils.php");
require_once("include/ListView/ListViewSession.php");
require_once("include/ListView/RelatedListViewSession.php");
require_once("include/DatabaseUtil.php");

if(!function_exists('GetRelatedList')) {
	function GetRelatedList($module,$relatedmodule,$focus,$query,$button,$returnset,$id='',$edit_val='',$del_val='') {
		//crmv@29579
		if($relatedmodule == 'ChangeLog'){
			return GetChangeLogList($module, $relatedmodule, $focus, $query, $button, $returnset, $id, $edit_val, $del_val);
		} else {
		//crmv@29579e
			return GetRelatedListBase($module, $relatedmodule, $focus, $query, $button, $returnset, $id, $edit_val, $del_val);
		}
	}
}

/** Function to get related list entries in detailed array format
  * @param $module -- modulename:: Type string
  * @param $relatedmodule -- relatedmodule:: Type string
  * @param $focus -- focus:: Type object
  * @param $query -- query:: Type string
  * @param $button -- buttons:: Type string
  * @param $returnset -- returnset:: Type string
  * @param $id -- id:: Type string
  * @param $edit_val -- edit value:: Type string
  * @param $del_val -- delete value:: Type string
  * @returns $related_entries -- related entires:: Type string array
  *
  */

function GetRelatedListBase($module,$relatedmodule,$focus,$query,$button,$returnset,$id='',$edit_val='',$del_val='')
{
	$log = LoggerManager::getLogger('account_list');
	$log->debug("Entering GetRelatedList(".$module.",".$relatedmodule.",focus,".$query.",".$button.",".$returnset.",".$edit_val.",".$del_val.") method ...");

	require_once('Smarty_setup.php');
	require_once("data/Tracker.php");
	require_once('include/database/PearDatabase.php');

	global $adb;
	global $app_strings;
	global $current_language;
	global $table_prefix;

	$current_module_strings = return_module_language($current_language, $module);

	global $list_max_entries_per_page;
	global $urlPrefix;


	global $currentModule;
	global $theme;
	global $theme_path;
	global $theme_path;
	global $mod_strings;
	// focus_list is the means of passing data to a ListView.
	global $focus_list;
	$smarty = new vtigerCRM_Smarty;
	if (!isset($where)) $where = "";
	
	
	$button = '<table cellspacing=0 cellpadding=2><tr><td>'.$button.'</td></tr></table>';

	// Added to have Purchase Order as form Title
	$theme_path="themes/".$theme."/";
	$image_path=$theme_path."images/";
	$smarty->assign("MOD", $mod_strings);
	$smarty->assign("APP", $app_strings);
	$smarty->assign("THEME", $theme);
	$smarty->assign("IMAGE_PATH",$image_path);
	$smarty->assign("MODULE",$relatedmodule);

	// We do not have RelatedListView in Detail View mode of Calendar module. So need to skip it.
 	if ($module!= 'Calendar') {
 		$focus->initSortByField($relatedmodule);
 	}
	//Retreive the list from Database
	//Appending the security parameter Security fix by Don
	if($relatedmodule != 'Products' && $relatedmodule != 'Faq' && $relatedmodule != 'PriceBook'
			&& $relatedmodule != 'Vendors' && $relatedmodule != 'Users') {
		global $current_user;
		$secQuery = getNonAdminAccessControlQuery($relatedmodule, $current_user);
		if(strlen($secQuery) > 1) {
			$query = appendFromClauseToQuery($query, $secQuery);
		}
	}	
//crmv@fix select in relatedlist
//TODO: fix related list with advanced sharing rules,disabled for now
//if ($relatedmodule != 'Calendar')
	$query = replaceSelectQueryFromList($relatedmodule,$focus,$query);	//crmv@19370
	//crmv@24715
	$query = $focus->listQueryNonAdminChange($query, $relatedmodule);
	//crmv@24715e
//crmv@fix select in relatedlist end
	if($relatedmodule == 'Leads') {
		$query .= " AND ".$table_prefix."_leaddetails.converted = 0";
	}
	
	if(isset($where) && $where != '')
	{
		$query .= ' and '.$where;
	}
	
	if(!$_SESSION['rlvs'][$module][$relatedmodule])
	{
		$modObj = new ListViewSession();
		$modObj->sortby = $focus->default_order_by;
		$modObj->sorder = $focus->default_sort_order;
		$_SESSION['rlvs'][$module][$relatedmodule] = get_object_vars($modObj);
	}
	
	if(!empty($_REQUEST['order_by'])) {
		if(method_exists($focus,getSortOrder))
		$sorder = $focus->getSortOrder();
		if(method_exists($focus,getOrderBy))
		$order_by = $focus->getOrderBy();

		if(isset($order_by) && $order_by != '') {
			$_SESSION['rlvs'][$module][$relatedmodule]['sorder'] = $sorder;
			$_SESSION['rlvs'][$module][$relatedmodule]['sortby'] = $order_by;
		}

	} elseif($_SESSION['rlvs'][$module][$relatedmodule]) {
		$sorder = $_SESSION['rlvs'][$module][$relatedmodule]['sorder'];
		$order_by = $_SESSION['rlvs'][$module][$relatedmodule]['sortby'];
	} else {
		$order_by = $focus->default_order_by;
		$sorder = $focus->default_sort_order;
	}
	
		//Added by Don for AssignedTo ordering issue in Related Lists
	$query_order_by = $order_by;
	if($order_by == 'smownerid') {
		$query_order_by = "case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end ";
	} elseif($order_by != 'crmid' && !empty($order_by)) {
		$tabname = getTableNameForField($relatedmodule, $order_by);
		if($tabname !== '' and $tabname != NULL)
			$query_order_by = $tabname.".".$query_order_by;
	}
	if(!empty($query_order_by)){
		$query .= ' ORDER BY '.$query_order_by.' '.$sorder;
	}
		
	if($relatedmodule == 'Calendar')
		$mod_listquery = "activity_listquery";
	else 
		$mod_listquery = strtolower($relatedmodule)."_listquery";
	$_SESSION[$mod_listquery] = $query;
	//crmv@22700
	global $onlyquery;
	if ($onlyquery) {
		return;
	}
	//crmv@22700e	
	$url_qry .="&order_by=".$order_by."&sorder=".$sorder;
//	$computeCount = $_REQUEST['withCount'];
//	if(PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false) === true ||
//			(boolean) $computeCount == true){
		//Retreiving the no of rows
		if($relatedmodule == "Calendar") {
			//for calendar related list, count will increase when we have multiple contacts
			//relationship for single activity
			$count_query = mkCountQuery($query);
			$count_result = $adb->query($count_query);
			$noofrows =$adb->query_result($count_result,0,"count");
		} else {
			$count_query = mkCountQuery($query);
			$count_result = $adb->query($count_query);

			if($adb->num_rows($count_result) > 0)
				$noofrows =$adb->query_result($count_result,0,"count");
			else
				$noofrows = $adb->num_rows($count_result);
		}
//	}
//	else{
//		$noofrows = null;
//	}

	//crmv@25809
	if ($_REQUEST['onlycount'] == 'true'){
		return Array('count'=>$noofrows);
	}
	//crmv@25809e

	//Setting Listview session object while sorting/pagination
	if(isset($_REQUEST['relmodule']) && $_REQUEST['relmodule']!='' && $_REQUEST['relmodule'] == $relatedmodule)
	{
		$relmodule = vtlib_purify($_REQUEST['relmodule']);
		if($_SESSION['rlvs'][$module][$relmodule])
		{
			setSessionVar($_SESSION['rlvs'][$module][$relmodule],$noofrows,$list_max_entries_per_page,$module,$relmodule);
		}
	}
	global $relationId;
	$start = RelatedListViewSession::getRequestCurrentPage($relationId, $query);
	$navigation_array =  VT_getSimpleNavigationValues($start, $list_max_entries_per_page,
			$noofrows);
	
	$limit_start_rec = ($start-1) * $list_max_entries_per_page;

	$list_result = $adb->limitQuery($query,$limit_start_rec,$list_max_entries_per_page);

	//Retreive the List View Table Header
	$id = vtlib_purify($_REQUEST['record']);
	$listview_header = getListViewHeader($focus,$relatedmodule,'',$sorder,$order_by,$id,'',$module);//"Accounts");
	if ($noofrows > 15) {
		$smarty->assign('SCROLLSTART','<div style="overflow:auto;height:315px;width:100%;">');
		$smarty->assign('SCROLLSTOP','</div>');
	}
	$smarty->assign("LISTHEADER", $listview_header);

	if($module == 'PriceBook' && $relatedmodule == 'Products') {
		$listview_entries = getListViewEntries($focus,$relatedmodule,$list_result,$navigation_array,'relatedlist',$returnset,$edit_val,$del_val);
	}
	if($module == 'Products' && $relatedmodule == 'PriceBook') {
		$listview_entries = getListViewEntries($focus,$relatedmodule,$list_result,$navigation_array,'relatedlist',$returnset,'EditListPrice','DeletePriceBookProductRel');
	} elseif($relatedmodule == 'SalesOrder') {
		$listview_entries = getListViewEntries($focus,$relatedmodule,$list_result,$navigation_array,'relatedlist',$returnset,'SalesOrderEditView','DeleteSalesOrder');
	}else {
		$listview_entries = getListViewEntries($focus,$relatedmodule,$list_result,$navigation_array,'relatedlist',$returnset);
	}

	$navigationOutput = Array();
	if ($noofrows > 0){
		$navigationOutput[] =  getRecordRangeMessage($list_max_entries_per_page, $limit_start_rec,$noofrows);
		if(empty($id) && !empty($_REQUEST['record'])) $id = vtlib_purify($_REQUEST['record']);
		$navigationOutput[] = getRelatedTableHeaderNavigation($navigation_array, $url_qry,$module,$relatedmodule,$id);
	}
	$related_entries = array('header'=>$listview_header,'entries'=>$listview_entries,'navigation'=>$navigationOutput,'count'=>$noofrows);	//crmv@25809

	$log->debug("Exiting GetRelatedList method ...");
	return $related_entries;	
}

/** Function to get related list entries in detailed array format
  * @param $parentmodule -- parentmodulename:: Type string
  * @param $query -- query:: Type string
  * @param $id -- id:: Type string
  * @returns $entries_list -- entries list:: Type string array
  *
  */

function getAttachmentsAndNotes($parentmodule,$query,$id,$sid='')
{
	global $log;
	$log->debug("Entering getAttachmentsAndNotes(".$parentmodule.",".$query.",".$id.",".$sid.") method ...");
	global $theme;

	$list = '<script>
		function confirmdelete(url)
		{
			if(confirm("'.$app_strings['ARE_YOU_SURE'].'"))
			{
				document.location.href=url;
			}
		}
	</script>';

	$theme_path="themes/".$theme."/";
	$image_path=$theme_path."images/";

	global $adb,$current_user;
	global $mod_strings;
	global $app_strings, $listview_max_textlength, $table_prefix;;
	
	$result=$adb->query($query);
	$noofrows = $adb->num_rows($result);

	$_SESSION['Documents_listquery'] = $query;
	$header[] = $app_strings['LBL_TITLE'];
	$header[] = $app_strings['LBL_DESCRIPTION'];
	$header[] = $app_strings['LBL_ATTACHMENTS'];
	$header[] = $app_strings['LBL_ASSIGNED_TO'];		
	$header[] = $app_strings['LBL_ACTION'];	
	
	if($result)
	{
		while($row = $adb->fetch_array($result))
		{
			if($row['activitytype'] == 'Attachments') {
				$query1="select setype,createdtime from ".$table_prefix."_crmentity where crmid=?";
				$params1 = array($row['attachmentsid']);
			} else {
				$query1="select setype,createdtime from ".$table_prefix."_crmentity where crmid=?";
				$params1 = array($row['crmid']);
			}
	
			$query1 .=" order by createdtime desc";
			$res=$adb->pquery($query1, $params1);
			$num_rows = $adb->num_rows($res);
			for($i=0; $i<$num_rows; $i++)
			{
				$setype = $adb->query_result($res,$i,'setype');
				$createdtime = $adb->query_result($res,$i,'createdtime');
			}
	
			if(($setype != "Products Image") && ($setype != "Contacts Image")) 
			{
				$entries = Array();
				if(trim($row['activitytype']) == 'Documents')
				{
					$module = 'Documents';
					$editaction = 'EditView';
					$deleteaction = 'Delete';
				}
				elseif($row['activitytype'] == 'Attachments')
				{
					$module = 'uploads';
					$editaction = 'upload';
					$deleteaction = 'deleteattachments';
				}
				if($module == 'Documents')
				{
					$entries[] = '<a href="index.php?module='.$module.'&action=DetailView&return_module='.$parentmodule.'&return_action='.$return_action.'&record='.$row["crmid"].'&filename='.$row['filename'].'&fileid='.$row['attachmentsid'].'&return_id='.vtlib_purify($_REQUEST["record"]).'&parenttab='.vtlib_purify($_REQUEST["parenttab"]).'">'.textlength_check($row['title']).'</a>';
				}
				elseif($module == 'uploads')
				{
					$entries[] = $row['title'];
				}
				if((getFieldVisibilityPermission('Documents', $current_user->id, 'notecontent') == '0') || $row['activitytype'] == 'Documents')
				{
					$row['description'] = preg_replace("/(<\/?)(\w+)([^>]*>)/i","",$row['description']);
					if($listview_max_textlength && (strlen($row['description']) > $listview_max_textlength))
					{
						$row['description'] = substr($row['description'],0,$listview_max_textlength).'...';
					}
					$entries[] = nl2br($row['description']); 
				}
				else
					$entries[] = " <font color ='red' >" .$app_strings['LBL_NOT_ACCESSIBLE']."</font>";
	
				$attachmentname = $row['filename'];//explode('_',$row['filename'],2);
	
				if((getFieldVisibilityPermission('Documents', $current_user->id, 'filename') == 0))
				{
					global $adb;
					
					$prof_id = fetchUserProfileId($current_user->id);
					$modulepermissionQuery = "select permissions from ".$table_prefix."_profile2tab where tabid=8 and profileid= ?";
					$modulepermissionresult = $adb->pquery($modulepermissionQuery,array($prof_id));
					$moduleviewpermission = $adb->query_result($modulepermissionresult,0,'permissions');
					
					$folderQuery = 'select folderid,filelocationtype,filestatus,filename from '.$table_prefix.'_notes where notesid = ?';
					$folderresult = $adb->pquery($folderQuery,array($row["crmid"]));
					$folder_id = $adb->query_result($folderresult,0,'folderid');
					$download_type = $adb->query_result($folderresult,0,'filelocationtype');
					$filestatus = $adb->query_result($folderresult,0,'filestatus');
					$filename = $adb->query_result($folderresult,0,'filename');
					
					$fileQuery = $adb->pquery("select attachmentsid from ".$table_prefix."_seattachmentsrel where crmid = ?",array($row['crmid']));
					$fileid = $adb->query_result($fileQuery,0,'attachmentsid');
					if($moduleviewpermission == 0)
					{
						if($download_type == 'I' )
						{
							if($filestatus == 1 )
								$entries[] = '<a href="index.php?module=Documents&action=DownloadFile&fileid='.$fileid.'&folderid='.$folder_id.'">'.textlength_check($attachmentname).'</a>';
							elseif(isset($attachmentname) && $attachmentname != '')
								$entries[] = textlength_check($attachmentname);
							else
								$entries[] = ' --';
						}
						elseif($download_type == 'E' )
						{
							if($filestatus == 1)
								$entries[] = '<a target="_blank" href="'.$filename.'" onClick="javascript:dldCntIncrease('.$row['crmid'].');">'.textlength_check($attachmentname).'</a>';
							elseif(isset($attachmentname) && $attachmentname != '')
								$entries[] = textlength_check($attachmentname);
							else
								$entries[] = ' --';
						}
						else{
								$entries[] = ' --';	
						}
					}
					else
					{
						if(isset($attachmentname))
							$entries[] = textlength_check($attachmentname);
						else
							$entries[] = ' --';
					}
				}
				else
					$entries[]='';			
				
				$assignedToQuery = $adb->pquery('SELECT smownerid FROM '.$table_prefix.'_crmentity WHERE crmid = ?',array($row['crmid']));
				$assignedTo = $adb->query_result($assignedToQuery,0,'smownerid');
				if($assignedTo != '' ){
					$entries[] = $assignedTo;
				}
				$del_param = 'index.php?module='.$module.'&action='.$deleteaction.'&return_module='.$parentmodule.'&return_action='.vtlib_purify($_REQUEST['action']).'&record='.$row["crmid"].'&return_id='.vtlib_purify($_REQUEST["record"]).'&parenttab='.vtlib_purify($_REQUEST["parenttab"]);
	
				if($module == 'Documents')
				{
					$edit_param = 'index.php?module='.$module.'&action='.$editaction.'&return_module='.$parentmodule.'&return_action='.vtlib_purify($_REQUEST['action']).'&record='.$row["crmid"].'&filename='.$row['filename'].'&fileid='.$row['attachmentsid'].'&return_id='.vtlib_purify($_REQUEST["record"]).'&parenttab='.vtlib_purify($_REQUEST["parenttab"]);
	
					$entries[] .= '<a href="'.$edit_param.'">'.$app_strings['LNK_EDIT'].'</a> | <a href=\'javascript:confirmdelete("'.$del_param.'")\'>'.$app_strings['LNK_DELETE'].'</a>';
				}
				else
				{
					$entries[] = '<a href=\'javascript:confirmdelete("'.$del_param.'")\'>'.$app_strings['LNK_DELETE'].'</a>';
				}
				$entries_list[] = $entries;
			}
		}
	}

	if($entries_list != '')
		$return_data = array('header'=>$header,'entries'=>$entries_list);
	$log->debug("Exiting getAttachmentsAndNotes method ...");
	return $return_data;

}

/** Function to get related list entries in detailed array format
  * @param $parentmodule -- parentmodulename:: Type string
  * @param $query -- query:: Type string
  * @param $id -- id:: Type string
  * @returns $return_data -- return data:: Type string array
  *
  */

function getHistory($parentmodule,$query,$id)
{
	global $log;
	$log->debug("Entering getHistory(".$parentmodule.",".$query.",".$id.") method ...");
	$parentaction = $_REQUEST['action'];
	global $theme;
	$theme_path="themes/".$theme."/";
	$image_path=$theme_path."images/";

	global $adb, $table_prefix;
	global $mod_strings;
	global $app_strings;

	//Appending the security parameter
	global $current_user;
	$rel_tab_id = getTabid("Calendar");
	//crmv@7230
	$used_status_field = getUsedStatusField("Calendar");
	//crmv@7230e
	global $current_user;
	require('user_privileges/user_privileges_'.$current_user->id.'.php');
	require('user_privileges/sharing_privileges_'.$current_user->id.'.php');
	$tab_id=getTabid('Calendar');
	if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[$tab_id] == 3)
	{
		$sec_parameter=getListViewSecurityParameter('Calendar');
		$query .= ' '.$sec_parameter;

	}
	//crmv@26596
	if (stripos($query, "order by") === false && (stripos($query, $table_prefix."_activity.date_start") !== false && stripos($query, $table_prefix."_activity.time_start") !== false)) {
		$query .= ' order by '.$table_prefix.'_activity.date_start desc, '.$table_prefix.'_activity.time_start desc';
	}
	//crmv@26596e
	$result=$adb->query($query);
	$noofrows = $adb->num_rows($result);
	
	//crmv@25809
	if ($_REQUEST['onlycount'] == 'true'){
		return Array('count'=>$noofrows);
	}
	//crmv@25809e

	if($noofrows == 0)
	{
		//There is no entries for history
		//crmv@25809
		$return_data = array('count'=>$noofrows);
		return $return_data;
		//crmv@25809e
	}
	else
	{
		//Form the header columns
		//mycrmv@rotho
		/*$header[] = $app_strings['LBL_TYPE'];
		$header[] = $app_strings['LBL_SUBJECT'];
		$header[] = $app_strings['LBL_RELATED_TO'];
		$header[] = $app_strings['LBL_START_DATE']." & ".$app_strings['LBL_TIME'];
		$header[] = $app_strings['LBL_END_DATE']." & ".$app_strings['LBL_TIME'];
		$header[] = $app_strings['LBL_DESCRIPTION'];	//crmv@21092
		$header[] = $app_strings['LBL_STATUS'];
		$header[] = $app_strings['LBL_ASSIGNED_TO'];*/
		$header[] = $app_strings['LBL_START_DATE']." & ".$app_strings['LBL_TIME'];
		$header[] = $app_strings['LBL_END_DATE']." & ".$app_strings['LBL_TIME'];
		$header[] = $app_strings['LBL_TYPE'];
		$header[] = $app_strings['LBL_SUBJECT'];
		$header[] = $app_strings['LBL_RELATED_TO'];
		
		$i=1;
		while($row = $adb->fetch_array($result))
		{
			$entries = Array();
			if($row['activitytype'] == 'Task')
			{
				$activitymode = 'Task';
				$icon = 'Tasks.gif';
				$status = $row['status'];
				//crmv@12035
				$status = getTranslatedString($status,'Calendar');
				//crmv@12035 end
			}
			else
			{
				$activitymode = 'Events';
				$icon = 'Activities.gif';
				$status = $row['eventstatus'];
				//crmv@12035
				$status = getTranslatedString($status,'Calendar');
				//crmv@12035 end
			}
			
			//crmv@sdk-26594
			$readonly = 100;
			$sdk_files = SDK::getViews('Calendar','related');
			if (!empty($sdk_files)) {
				$recordId = $row['activityid'];
				foreach($sdk_files as $sdk_file) {
					$success = false;
					$readonly_old = $readonly;
					include($sdk_file['src']);
					SDK::checkReadonly($readonly_old,$readonly,$sdk_file['mode']);
					if ($success && $sdk_file['on_success'] == 'stop') {
						break;
					}
				}
			}
			//crmv@sdk-26594-e
	
            $typeofactivity = $row['activitytype'];
			$typeofactivity = getTranslatedString($typeofactivity,'Calendar'); //crmv@29228
			//mycrmv@rotho
			/*
			$entries[] = $typeofactivity;

			//crmv@17001 : Private Permissions
			$visibility = $adb->query_result($adb->query('select visibility from '.$table_prefix.'_activity where activityid = '.$row['activityid']),0,'visibility');
            if (!is_admin($current_user) && getUserId($row['activityid']) != $current_user->id && $visibility == 'Private' && $readonly == 100) {	//crmv@sdk-26594
				$entries[] = getTranslatedString('Private Event','Calendar');
				$entries[] = "<font color='red'>".getTranslatedString('LBL_NOT_ACCESSIBLE')."</font>";
            }
			else {
				$activity = '<a href="index.php?module=Calendar&action=DetailView&return_module='.$parentmodule.'&return_action=DetailView&record='.$row["activityid"] .'&activity_mode='.$activitymode.'&return_id='.$_REQUEST['record'].'&parenttab='.$_REQUEST['parenttab'].'">'.$row['subject'].'</a></td>';
				$entries[] = $activity;
				$parentname = getRelatedTo('Calendar',$result,$i-1);
				$entries[] = $parentname;
			}
			//crmv@17001e
			
			$entries[] = getDisplayDate(substr($row['date_start'],0,10))."   ".$row['time_start'];
			$entries[] = getDisplayDate(substr($row['due_date'],0,10))."   ".$row['time_end'];         
		
			//crmv@21092	crmv@23734
			if ($row['description'] != '') {
				global $listview_max_textlength,$default_charset;
				$tmp_val = preg_replace("/(<\/?)(\w+)([^>]*>)/i","",$row['description']);
				$tmp_val = trim(html_entity_decode($tmp_val, ENT_QUOTES, $default_charset));
				$value = textlength_check($row['description']);
				if ($tmp_val != '' && strlen($tmp_val) > $listview_max_textlength) {
					$value .= '&nbsp;<a href="javascript:;"><img onmouseout="getObj(\'content_description_'.$row["activityid"].'\').hide();" onmouseover="getObj(\'content_description_'.$row["activityid"].'\').show();" src="themes/softed/images/readmore.png" border="0"></a>';
					$value .= '<div id="content_description_'.$row["activityid"].'" class="layerPopup" style="width:300px;z-index:10000001;display:none;position:absolute;" onmouseout="getObj(\'content_description_'.$row["activityid"].'\').hide();" onmouseover="getObj(\'content_description_'.$row["activityid"].'\').show();">
						        <table style="background-color:#F2F2F2;" align="center" border="0" cellpadding="5" cellspacing="0" width="100%">
						        <tr><td class="small">'.$tmp_val.'</td></tr>
						        </table></div>';
				}
				$entries[] = $value;
			}
			else
				$entries[] = '';
			//crmv@21092e	crmv@23734e

			//crmv@17001 : Private Permissions
            if (!is_admin($current_user) && getUserId($row['activityid']) != $current_user->id && $visibility == 'Private' && $readonly == 100)	//crmv@sdk-26594
				$entries[] = "<font color='red'>".getTranslatedString('LBL_NOT_ACCESSIBLE')."</font>";
			else
				$entries[] = $status;
			//crmv@17001e

			if($row['user_name']==NULL && $row['groupname']!=NULL)
			{
				$entries[] = $row['groupname'];
			}
			else
			{
 				$entries[] = $row['user_name'];
				
			}
			*/
			$entries[] = getDisplayDate(substr($row['date_start'],0,10))."   ".$row['time_start'];
			$entries[] = getDisplayDate(substr($row['due_date'],0,10))."   ".$row['time_end'];         
			$entries[] = $typeofactivity;

			//crmv@17001 : Private Permissions
			$visibility = $adb->query_result($adb->query('select visibility from '.$table_prefix.'_activity where activityid = '.$row['activityid']),0,'visibility');
            if (!is_admin($current_user) && getUserId($row['activityid']) != $current_user->id && $visibility == 'Private' && $readonly == 100) {	//crmv@sdk-26594
				$entries[] = getTranslatedString('Private Event','Calendar');
				$entries[] = "<font color='red'>".getTranslatedString('LBL_NOT_ACCESSIBLE')."</font>";
            }
			else {
				$activity = '<a href="index.php?module=Calendar&action=DetailView&return_module='.$parentmodule.'&return_action=DetailView&record='.$row["activityid"] .'&activity_mode='.$activitymode.'&return_id='.$_REQUEST['record'].'&parenttab='.$_REQUEST['parenttab'].'">'.$row['subject'].'</a></td>';
				$entries[] = $activity;
				$parentname = getRelatedTo('Calendar',$result,$i-1);
				$entries[] = $parentname;
			}
			//crmv@17001e			
			
		
			//crmv@7230e / crmv@10445e 			
			$i++;
			$entries_list[] = $entries;
		}
		$return_data = array('header'=>$header,'entries'=>$entries_list,'count'=>$noofrows);	//crmv@25809
		$log->debug("Exiting getHistory method ...");
		return $return_data; 
	}
}

/**	Function to display the Products which are related to the PriceBook
 *	@param string $query - query to get the list of products which are related to the current PriceBook
 *	@param object $focus - PriceBook object which contains all the information of the current PriceBook
 *	@param string $returnset - return_module, return_action and return_id which are sequenced with & to pass to the URL which is optional
 *	return array $return_data which will be formed like array('header'=>$header,'entries'=>$entries_list) where as $header contains all the header columns and $entries_list will contain all the Product entries
 */
function getPriceBookRelatedProducts($query,$focus,$returnset='')
{
	global $log;
	$log->debug("Entering getPriceBookRelatedProducts(".$query.",focus,".$returnset.") method ...");

	global $adb;
	global $app_strings;
	global $mod_strings;
	global $current_language,$current_user;
	$current_module_strings = return_module_language($current_language, 'PriceBook');

	global $list_max_entries_per_page;
	global $urlPrefix;

	global $theme;
	$pricebook_id = vtlib_purify($_REQUEST['record']);
	$theme_path="themes/".$theme."/";
	$image_path=$theme_path."images/";

//	$computeCount = $_REQUEST['withCount'];
//	if(PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false) === true ||
//			((boolean) $computeCount) == true){
		$noofrows = $adb->query_result($adb->query(mkCountQuery($query)),0,'count');
//	}else{
//		$noofrows = null;
//	}

	//crmv@25809
	if ($_REQUEST['onlycount'] == 'true'){
		return Array('count'=>$noofrows);
	}
	//crmv@25809e
	
	$module = 'PriceBooks';
	$relatedmodule = 'Products';
	if(!$_SESSION['rlvs'][$module][$relatedmodule])
	{
		$modObj = new ListViewSession();
		$modObj->sortby = $focus->default_order_by;
		$modObj->sorder = $focus->default_sort_order;
		$_SESSION['rlvs'][$module][$relatedmodule] = get_object_vars($modObj);
	}

	
	if(isset($_REQUEST['relmodule']) && $_REQUEST['relmodule']!='' && $_REQUEST['relmodule'] == $relatedmodule) {
		$relmodule = vtlib_purify($_REQUEST['relmodule']);
		if($_SESSION['rlvs'][$module][$relmodule]) {
			setSessionVar($_SESSION['rlvs'][$module][$relmodule],$noofrows,$list_max_entries_per_page,$module,$relmodule);
		}
	}
	global $relationId;
	$start = RelatedListViewSession::getRequestCurrentPage($relationId, $query);
	$navigation_array =  VT_getSimpleNavigationValues($start, $list_max_entries_per_page,
			$noofrows);

	$limit_start_rec = ($start-1) * $list_max_entries_per_page;

	$list_result = $adb->limitQuery($query,$limit_start_rec,$list_max_entries_per_page);

	$header=array();
	$header[]=$mod_strings['LBL_LIST_PRODUCT_NAME'];
	if(getFieldVisibilityPermission('Products', $current_user->id, 'productcode') == '0')
		$header[]=$mod_strings['LBL_PRODUCT_CODE'];
	if(getFieldVisibilityPermission('Products', $current_user->id, 'unit_price') == '0')
		$header[]=$mod_strings['LBL_PRODUCT_UNIT_PRICE'];
	$header[]=$mod_strings['LBL_PB_LIST_PRICE'];
	if(isPermitted("PriceBooks","EditView","") == 'yes' || isPermitted("PriceBooks","Delete","") == 'yes')
		$header[]=$mod_strings['LBL_ACTION'];
	
	$currency_id = $focus->column_fields['currency_id'];
	$numRows = $adb->num_rows($list_result);
	for($i=0; $i<$numRows; $i++) {
		$entity_id = $adb->query_result($list_result,$i,"crmid");
		$unit_price = 	$adb->query_result($list_result,$i,"unit_price");
		if($currency_id != null) {
			$prod_prices = getPricesForProducts($currency_id, array($entity_id));
			$unit_price = $prod_prices[$entity_id];
		}
		$listprice = $adb->query_result($list_result,$i,"listprice");
		$field_name=$entity_id."_listprice";
		
		$entries = Array();
		$entries[] = textlength_check($adb->query_result($list_result,$i,"productname"));
		if(getFieldVisibilityPermission('Products', $current_user->id, 'productcode') == '0')
			$entries[] = $adb->query_result($list_result,$i,"productcode");
		if(getFieldVisibilityPermission('Products', $current_user->id, 'unit_price') == '0')
			$entries[] = $unit_price;

		$entries[] = $listprice;
		$action = "";
		if(isPermitted("PriceBooks","EditView","") == 'yes')
			$action .= '<img style="cursor:pointer;" src="'. vtiger_imageurl('editfield.gif', $theme).'" border="0" onClick="fnvshobj(this,\'editlistprice\'),editProductListPrice(\''.$entity_id.'\',\''.$pricebook_id.'\',\''.$listprice.'\')" alt="'.$app_strings["LBL_EDIT_BUTTON"].'" title="'.$app_strings["LBL_EDIT_BUTTON"].'"/>';
		if(isPermitted("PriceBooks","Delete","") == 'yes')
		{		
			if($action != "")
				$action .= '&nbsp;|&nbsp;';
			$action .= '<img src="'. vtiger_imageurl('delete.gif', $theme).'" onclick="if(confirm(\''.$app_strings['ARE_YOU_SURE'].'\')) deletePriceBookProductRel('.$entity_id.','.$pricebook_id.');" alt="'.$app_strings["LBL_DELETE"].'" title="'.$app_strings["LBL_DELETE"].'" style="cursor:pointer;" border="0">';	
		}
		if($action != "")		
			$entries[] = $action;
		$entries_list[] = $entries;
	}
	$navigationOutput[] =  getRecordRangeMessage($list_result, $limit_start_rec,$noofrows);
	$navigationOutput[] = getRelatedTableHeaderNavigation($navigation_array, '',$module,
			$relatedmodule,$focus->id);
	$return_data = array('header'=>$header,'entries'=>$entries_list,'navigation'=>$navigationOutput,'count'=>$noofrows);	//crmv@25809

	$log->debug("Exiting getPriceBookRelatedProducts method ...");
	return $return_data;
}

function CheckFieldPermission($fieldname,$module)
{
	global $current_user,$adb, $table_prefix;

 require('user_privileges/user_privileges_'.$current_user->id.'.php');
 if($fieldname == '' || $module == '')
	 return "false";

	$tab_id = getTabid($module);
	if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 )
	{
	 $profileList = getCurrentUserProfileList();
	 $params = array(getTabid($module), $fieldname);
	 $sql1= "SELECT fieldname 
	 FROM ".$table_prefix."_field 
	 INNER JOIN ".$table_prefix."_def_org_field ON ".$table_prefix."_def_org_field.fieldid=".$table_prefix."_field.fieldid 
	 WHERE ".$table_prefix."_field.tabid=? AND fieldname=? AND ".$table_prefix."_field.displaytype IN (1,2,3,4) 
	 AND ".$table_prefix."_def_org_field.visible=0";
	  $sql1.=" AND EXISTS(SELECT * FROM ".$table_prefix."_profile2field WHERE ".$table_prefix."_profile2field.fieldid = ".$table_prefix."_field.fieldid AND ".$table_prefix."_profile2field.visible = 0";
	 if (count($profileList) > 0) {
		 $sql.=" AND ".$table_prefix."_profile2field.profileid IN (". generateQuestionMarks($profileList) .") ";
		 array_push($params, $profileList);
	 } 
	 $sql1.=" )";  
	 $sql1." ORDER BY block,sequence";
	$result1= $adb->pquery($sql1,$params);
	$permission = ($adb->num_rows($result1) > 0) ? "true" : "false";
 }else
 {
	$permission = "true";
 }
 return $permission;
}

function CheckColumnPermission($tablename, $columnname, $module)
{
	global $adb, $table_prefix;
	
	$res = $adb->pquery("select fieldname from ".$table_prefix."_field where tablename=? and columnname=?", array($tablename, $columnname));
	$fieldname = $adb->query_result($res, 0, 'fieldname');
	return CheckFieldPermission($fieldname, $module);
}

//crmv@29579
function GetChangeLogList($currentModule, $related_module, $other, $query, $button, $returnset)
{
	global $log;
	$log->debug("Entering GetChangeLogList(".$query.",focus,".$returnset.") method ...");

	global $adb;
	global $app_strings;
	global $mod_strings;
	global $current_language,$current_user;
	global $urlPrefix, $theme;
	//echo $query;
	$current_module_strings = return_module_language($current_language, $related_module);

	$theme_path="themes/".$theme."/";
	$image_path=$theme_path."images/";

	$noofrows = $adb->query_result($adb->query(mkCountQuery($query)),0,'count');

	//crmv@25809
	if ($_REQUEST['onlycount'] == 'true'){
		return Array('count'=>$noofrows);
	}
	//crmv@25809e
	$module = $currentModule;
	if(!$_SESSION['rlvs'][$module][$related_module])
	{
		$modObj = new ListViewSession();
		$modObj->sortby = $focus->default_order_by;
		$modObj->sorder = $focus->default_sort_order;
		$_SESSION['rlvs'][$module][$related_module] = get_object_vars($modObj);
	}
	
	if(isset($_REQUEST['related_module']) && $_REQUEST['related_module']!='' && $_REQUEST['related_module'] == $related_module) {
		$related_module = vtlib_purify($_REQUEST['related_module']);
		if($_SESSION['rlvs'][$module][$related_module]) {
			setSessionVar($_SESSION['rlvs'][$module][$related_module],$noofrows, 1,$module,$related_module);
		}
	}
	global $relationId;
	$start = RelatedListViewSession::getRequestCurrentPage($relationId, $query);
	$navigation_array =  VT_getSimpleNavigationValues($start, 1, $noofrows);

	$limit_start_rec = ($start-1) * 1;

	$query .= ' ORDER BY crmid DESC ';

	$list_result = $adb->limitQuery($query,$limit_start_rec, 1);

	$header=array();
	if(getFieldVisibilityPermission($related_module, $current_user->id, 'audit_no') == '0')
		$header[]=getTranslatedString('Audit No',$related_module);
	if(getFieldVisibilityPermission($related_module, $current_user->id, 'user_name') == '0')
		$header[]=getTranslatedString('Modified by',$related_module);
	if(getFieldVisibilityPermission($related_module, $current_user->id, 'modified_date') == '0')
		$header[]=getTranslatedString('Modified Time',$related_module);
	if(getFieldVisibilityPermission($related_module, $current_user->id, 'description') == '0')
		$header[]=getTranslatedString('Modified fields',$related_module);
		
	$numRows = $adb->num_rows($list_result);
	for($i=0; $i<$numRows; $i++) {
		$entity_id = $adb->query_result($list_result,$i,"crmid");

		$nr_revision = $adb->query_result($list_result,$i,"audit_no");
		if(getFieldVisibilityPermission($related_module, $current_user->id, 'audit_no') == '0') {
			//$entries[] = '<a href="index.php?module=ChangeLog&action=DetailView&record='.$entity_id.'">'.$nr_revision.'</a>';
			$entries[] = $nr_revision;
		}
			
		$user_name = $adb->query_result($list_result,$i,"user_name");
		$entries[] = $user_name;

		$modified_date = $adb->query_result($list_result,$i,"modified_date");
		if ($modified_date != '') {
			if (vtlib_isModuleActive('ModNotifications')) {
				require_once('modules/ModNotifications/models/Comments.php');
				$monHotificationFocus = new ModNotifications_CommentsModel(array('createdtime'=>$modified_date));
				$entries[] = '<a href="javascript:;" title="'.$monHotificationFocus->timestamp().'">'.$monHotificationFocus->timestampAgo().'</a>';
			} else {
				$entries[] = getDisplayDate($modified_date);
			}
		} else {
			$entries[] = '';
		}

		$description = $adb->query_result_no_html($list_result,$i,"description");
		if($description !=''){
			$html = ChangeLog::getFieldsTable($description, $currentModule);
		}else{
			$html = '';
		}
		$entries[] = $html;
		
		$entries_list[] = $entries;
	}
	$navigationOutput = Array();
	if ($noofrows > 0){
		$navigationOutput[] =  getRecordRangeMessage(1, $limit_start_rec,$noofrows);
		if(empty($id) && !empty($_REQUEST['record'])) $id = vtlib_purify($_REQUEST['record']);
		$navigationOutput[] = getRelatedTableHeaderNavigation($navigation_array, '',$module,$related_module,$id);
	}
	$return_data = array('header'=>$header,'entries'=>$entries_list,'navigation'=>$navigationOutput,'count'=>$noofrows);	//crmv@25809
	$log->debug("Exiting GetChangeLogList method ...");
	return $return_data;
}
//crmv@29579e
?>