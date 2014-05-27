<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
/*********************************************************************************
 * $Header: /cvsroot/vtigercrm/vtiger_crm/include/utils/ListViewUtils.php,v 1.32 2006/02/03 06:53:08 mangai Exp $
 * Description:  Includes generic helper functions used throughout the application.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/database/PearDatabase.php');
require_once('include/ComboUtil.php'); //new
require_once('include/utils/CommonUtils.php'); //new
require_once('user_privileges/default_module_view.php'); //new
include_once("include/utils/crmv_utils.php"); //crmv@7230s
require_once('include/utils/UserInfoUtil.php');
require_once('include/Zend/Json.php');

/**This function is used to get the list view header values in a list view
*Param $focus - module object
*Param $module - module name
*Param $sort_qry - sort by value
*Param $sorder - sorting order (asc/desc)
*Param $order_by - order by
*Param $relatedlist - flag to check whether the header is for listvie or related list
*Param $oCv - Custom view object
*Returns the listview header values in an array
*/
function getListViewHeader($focus, $module,$sort_qry='',$sorder='',$order_by='',$relatedlist='',$oCv='',$relatedmodule='',$skipActions=false, $nohtml = false) // crmv@31780
{
    global $log, $singlepane_view;
	$log->debug("Entering getListViewHeader(". $module.",".$sort_qry.",".$sorder.",".$order_by.",".$relatedlist.",".(is_object($oCv)? get_class($oCv) : $oCv).") method ..."); //crmv@31429
    global $adb, $table_prefix;
    global $theme;
    global $app_strings;
    global $mod_strings;
    global $current_language;
    //crmv@7216
    if ($relatedlist !=''){
    	$mod_rel_strings = return_specified_module_language($current_language,$module);
    }
	 //crmv@7216e
    $arrow='';
    $qry = getURLstring($focus);
    $theme_path="themes/".$theme."/";
    $image_path=$theme_path."images/";
    $list_header = Array();

    //Get the vtiger_tabid of the module
    $tabid = getTabid($module);
    $tabname = getParentTab();
    global $current_user;
    //added for vtiger_customview 27/5
    if($oCv)
    {
        if(isset($oCv->list_fields))
        {
            $focus->list_fields = $oCv->list_fields;
        }
    }

   	// Remove fields which are made inactive
	$focus->filterInactiveFields($module);

    //Added to reduce the no. of queries logging for non-admin user -- by Minnie-start
    $field_list = array();
    $j=0;
    require('user_privileges/user_privileges_'.$current_user->id.'.php');

    //vtc
    if ($module == 'Products' && $relatedmodule == 'Potentials') {

    	$list_fields = Array(
			'Product Name'=>Array('products'=>'productname'),
			'Part Number'=>Array('products'=>'productcode'),
			'Support Start Date'=>Array('products'=>'start_date'),
			'Support Expiry Date'=>Array('products'=>'expiry_date'),);

		$list_fields_name = Array(
			'Product Name'=>'productname',
            'Part Number'=>'productcode',
			'Support Start Date'=>'start_date',
            'Support Expiry Date'=>'expiry_date',);

		$focus->list_fields = $list_fields;
		$focus->list_fields_name = $list_fields_name;
    }
    //vtc e

    foreach($focus->list_fields as $name=>$tableinfo)
    {
        $fieldname = $focus->list_fields_name[$name];
        if($oCv)
        {
            if(isset($oCv->list_fields_name))
            {
                $fieldname = $oCv->list_fields_name[$name];
            }
        }
        if($fieldname == 'accountname' && $module != 'Accounts')
        {
            $fieldname = 'account_id';
        }
        if($fieldname == 'lastname' && ($module == 'Documents' || $module == 'SalesOrder'|| $module == 'PurchaseOrder' || $module == 'Invoice' || $module == 'Quotes'||$module == 'Calendar' ))
        {
                  $fieldname = 'contact_id';
        }
        if($fieldname == 'productname' && $module != 'Products')
        {
             $fieldname = 'product_id';
        }
        array_push($field_list, $fieldname);
        $j++;
    }
    $field=Array();
    if($is_admin==false)
    {
    	//crmv@7216
        if($module == 'Emails' || $module == 'Fax' || $module == 'Sms')
        {
            $query  = "SELECT fieldname FROM ".$table_prefix."_field WHERE tabid = ?";
            $params = array($tabid);
        }
      //crmv@7216e
        else
        {
            $profileList = getCurrentUserProfileList();
            $params = array();
            $query  = "SELECT ".$table_prefix."_field.fieldname
                FROM ".$table_prefix."_field
                INNER JOIN ".$table_prefix."_def_org_field
                    ON ".$table_prefix."_def_org_field.fieldid = ".$table_prefix."_field.fieldid";
                if($module == "Calendar") {
                    $query .=" WHERE ".$table_prefix."_field.tabid in (9,16)";
                } else {
                    $query .=" WHERE ".$table_prefix."_field.tabid = ?";
                    array_push($params, $tabid);
                }

            $query.=" AND ".$table_prefix."_def_org_field.visible = 0
                AND ".$table_prefix."_field.fieldname IN (". generateQuestionMarks($field_list) .")";

            array_push($params,  $field_list);
			$query.=" AND EXISTS(SELECT * FROM ".$table_prefix."_profile2field WHERE ".$table_prefix."_profile2field.fieldid = ".$table_prefix."_field.fieldid AND ".$table_prefix."_profile2field.visible = 0";
			if (count($profileList) > 0) {
				 $query.=" AND ".$table_prefix."_profile2field.profileid IN (". generateQuestionMarks($profileList) .") ";
				 array_push($params, $profileList);
			}
			$query.=" )";
        }
//        echo $adb->convert2Sql($query,$adb->flatten_array($params));
        $result = $adb->pquery($query, $params);
        for($k=0;$k < $adb->num_rows($result);$k++)
        {
            $field[]=$adb->query_result($result,$k,"fieldname");
        }
    }
    //end

    //crmv@18744
	//Added for Action - edit and delete link header in listview
    //ds@8 project tool
    if ($module =="Projects" AND $viewnamedesc['viewname']=="All" AND (isProjectLeader() OR isProjectAdmin() ))
  	{
    	$list_header[] = $mod_strings["LBL_ADD_WORKERS"];
  	}
	if(!$skipActions && (isPermitted($module,"EditView","") == 'yes' || isPermitted($module,"Delete","") == 'yes') || (method_exists($focus, 'isViewed') && PerformancePrefs::getBoolean('LISTVIEW_RECORD_CHANGE_INDICATOR', true)))	//crmv@23685
	{
	    if ($module =="Projects" AND isProjectLeader() AND !isProjectAdmin() )
	    {

	    }
	    else {
	        $list_header[] = $app_strings["LBL_ACTION"];
	    }
    }
    //ds@8e
    //crmv@7214
    elseif ($module == 'HelpDesk'){
    	$list_header[] = $app_strings["LBL_ACTION"];
    }
	//crmv@7214 e
	//crmv@18744e

    //modified for vtiger_customview 27/5 - $app_strings change to $mod_strings
    $list_header_raw = array(); // crmv@31780
    foreach($focus->list_fields as $name=>$tableinfo)
    {
        //added for vtiger_customview 27/5
        if($oCv)
        {
            if(isset($oCv->list_fields_name))
            {
                $fieldname = $oCv->list_fields_name[$name];
                if($fieldname == 'accountname' &&  $module != 'Accounts')
                        {
                                    $fieldname = 'account_id';
                        }
                if($fieldname == 'lastname' && ($module == 'Documents' || $module == 'SalesOrder'|| $module == 'PurchaseOrder' || $module == 'Invoice' || $module == 'Quotes'|| $module == 'Calendar') )
                {
                                        $fieldname = 'contact_id';
                }
                if($fieldname == 'productname' && $module != 'Products')
                        {
                                 $fieldname = 'product_id';
                           }
            }else
            {
                $fieldname = $focus->list_fields_name[$name];
            }
        }else
        {
            $fieldname = $focus->list_fields_name[$name];
            if($fieldname == 'accountname' &&  $module != 'Accounts')
            {
                $fieldname = 'account_id';
            }
            if($fieldname == 'lastname' && ($module == 'Documents' || $module == 'SalesOrder'|| $module == 'PurchaseOrder' || $module == 'Invoice' || $module == 'Quotes'|| $module == 'Calendar'))
            {
                $fieldname = 'contact_id';
            }
            if($fieldname == 'productname' && $module != 'Products')
                    {
                             $fieldname = 'product_id';
                    }
			if (empty($fieldname) && $module == 'Calendar' && !empty($tableinfo['activity'])) $fieldname = $tableinfo['activity']; // crmv@31780

        }
        if($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] ==0 || in_array($fieldname,$field) || $fieldname == '' || $name == 'Close')
        {
            if(isset($focus->sortby_fields) && $focus->sortby_fields !='')
            {
                //Added on 14-12-2005 to avoid if and else check for every list vtiger_field for arrow image and change order
                $change_sorder = array('ASC'=>'DESC','DESC'=>'ASC');
                $arrow_gif = array('ASC'=>'arrow_down.gif','DESC'=>'arrow_up.gif');
                foreach($focus->list_fields[$name] as $tab=>$col)
                {
                    if(in_array($col,$focus->sortby_fields))
                    {
                        if($order_by == $col)
                        {
                            $temp_sorder = $change_sorder[$sorder];
                            $arrow = "&nbsp;<img src ='".vtiger_imageurl($arrow_gif[$sorder], $theme)."' border='0'>";
                        }
                        else
                        {
                            $temp_sorder = 'ASC';
                        }
                        	//crmv@7216
                           if($mod_rel_strings[$name])
                            {
                                $lbl_name = $mod_rel_strings[$name];
                            }
                    			elseif($mod_strings[$name])
                            {
                                $lbl_name = $mod_strings[$name];
                            }
                            elseif($app_strings[$name])
                            {
                                $lbl_name = $app_strings[$name];
                            }
                            //crmv@7216e
                            else
                            {
                                $lbl_name = $name;
                            }
    						//added to display vtiger_currency symbol in listview header
							if($lbl_name =='Amount')
							{
								$lbl_name .=' ('.$app_strings['LBL_IN'].' '.$user_info['currency_symbol'].')';
							}
							if($relatedlist !='' && $relatedlist != 'global'){
								$relationURL = '';
								if(!empty($_REQUEST['relation_id'])){
									$relationURL = '&relation_id='.vtlib_purify(
											$_REQUEST['relation_id']);
								}
								$actionsURL = '';
								if(!empty($_REQUEST['actions'])){
									$actionsURL = '&actions='.vtlib_purify($_REQUEST['actions']);
								}
								if(empty($_REQUEST['header'])){
									$moduleLabel = getTranslatedString($module,$module);
								}else{
									$moduleLabel = $_REQUEST['header'];
								}
								$moduleLabel = str_replace(' ','',$moduleLabel);
								$name = "<a href='javascript:void(0);' onClick='loadRelatedListBlock".
								"(\"module=$relatedmodule&action=".$relatedmodule."Ajax&".
								"file=DetailViewAjax&ajxaction=LOADRELATEDLIST&header=".$moduleLabel.
								"&order_by=$col&record=$relatedlist&sorder=$temp_sorder$relationURL".
								"$actionsURL\",\"tbl_".$relatedmodule."_$moduleLabel\",".
								"\"$relatedmodule"."_$moduleLabel\");' class='listFormHeaderLinks'>".$lbl_name."".$arrow."</a>";
							} elseif($module == 'Users' && $name == 'User Name')
								$name = "<a href='javascript:;' onClick='getListViewEntries_js(\"".$module."\",\"parenttab=".$tabname."&order_by=".$col."&start=1&sorder=".$temp_sorder."".$sort_qry."\");' class='listFormHeaderLinks'>".getTranslatedString('LBL_LIST_USER_NAME_ROLE',$module)."".$arrow."</a>";
							elseif($relatedlist == "global")
							        $name = $lbl_name;
							else
								$name = "<a href='javascript:;' onClick='getListViewEntries_js(\"".$module."\",\"parenttab=".$tabname."&order_by=".$col."&start=1&sorder=".$temp_sorder."".$sort_qry."\");' class='listFormHeaderLinks'>".$lbl_name."".$arrow."</a>";
							$arrow = '';
                    }
                    else
                    {
//crmv@7216
                        if(stripos($col, 'cf_') === 0) {
							$tablenameArray = array_keys($tableinfo,$col);
							$tablename = $tablenameArray[0];
							$cf_columns = $adb->getColumnNames($tablename);
							//crmv@481398+25058
							if ($adb->table_exist($tablename)){
								$cf_columns = $adb->getColumnNames($tablename);
							}
							elseif ($adb->table_exist($table_prefix."_".$tablename)){
								$cf_columns = $adb->getColumnNames($table_prefix."_".$tablename);
							}
							else{
								$cf_columns = Array();
							}
							if (array_search($col, $cf_columns) !== false) {
							//crmv@481398+25058e
								$pquery = "select fieldlabel,typeofdata from ".$table_prefix."_field where tablename = ? and fieldname = ? and ".$table_prefix."_field.presence in (0,2)";
								$cf_res = $adb->pquery($pquery, array($tablename, $col));
								if (count($cf_res) > 0){
									$cf_fld_label = $adb->query_result($cf_res, 0, "fieldlabel");
									$typeofdata = explode("~",$adb->query_result($cf_res, 0, "typeofdata"));
									$new_field_label = $tablename. ":" . $col .":". $col .":". $module . "_" . str_replace(" ","_",$cf_fld_label) .":". $typeofdata[0];
									$name = $cf_fld_label;

									// Update the existing field name in the database with new field name.
									$upd_query = "update ".$table_prefix."_cvcolumnlist set columnname = ? where columnname like '" .$tablename. ":" . $col .":". $col ."%'";
									$upd_params = array($new_field_label);
									$adb->pquery($upd_query, $upd_params);

								}
							}
						} else {
                    			$old_name=$name;
                           if($mod_rel_strings[$name])
                            {
                                $name = $mod_rel_strings[$name];
                            }
                    			elseif($mod_strings[$name])
                            {
                                $name = $mod_strings[$name];
                            }
                            elseif($app_strings[$name])
                            {
                                $name = $app_strings[$name];
                            }
                            $original_name[$name]=$old_name;
						}
                    }

                }
            }
            //added to display vtiger_currency symbol in related listview header
        if($original_name[$name] =='Amount' && $relatedlist !='' )
        {
           $name .=' ('.$app_strings['LBL_IN'].' '.$user_info['currency_symbol'].')';
        }
            if($module == "Calendar" && $original_name[$name] == 'Close')
            {
                if(isPermitted("Calendar","EditView") == 'yes')
                {
                    if((getFieldVisibilityPermission('Events',$current_user->id,'eventstatus') == '0') || (getFieldVisibilityPermission('Calendar',$current_user->id,'taskstatus') == '0'))
                    {
                        array_push($list_header,$name);
                    }
                }
            }
            else
            {
                $list_header[]=$name;
            }

            $list_header_raw[] = array('fieldname'=>$fieldname, 'label'=>$name); // crmv@31780
    }
     }

    if ($nohtml) $list_header = $list_header_raw; // crmv@31780
    return $list_header;
}

/**This function is used to get the list view header in popup
*Param $focus - module object
*Param $module - module name
*Param $sort_qry - sort by value
*Param $sorder - sorting order (asc/desc)
*Param $order_by - order by
*Returns the listview header values in an array
*/

function getSearchListViewHeader($focus, $module,$sort_qry='',$sorder='',$order_by='',$oCv='')
{
	global $log;
	$log->debug("Entering getSearchListViewHeader(focus,". $module.",".$sort_qry.",".$sorder.",".$order_by.") method ...");
	global $adb, $table_prefix;
	global $theme;
	global $app_strings;
        global $mod_strings,$current_user;
        $arrow='';
	$list_header = Array();
	$tabid = getTabid($module);
	if(isset($_REQUEST['task_relmod_id']))
	{
		$task_relmod_id=vtlib_purify($_REQUEST['task_relmod_id']);
		$pass_url .="&task_relmod_id=".$task_relmod_id;
	}
	if(isset($_REQUEST['relmod_id']))
	{
		$relmod_id=vtlib_purify($_REQUEST['relmod_id']);
		$pass_url .="&relmod_id=".$relmod_id;
	}
	if(isset($_REQUEST['task_parent_module']))
	{
		$task_parent_module=vtlib_purify($_REQUEST['task_parent_module']);
		$pass_url .="&task_parent_module=".$task_parent_module;
	}
	if(isset($_REQUEST['parent_module']))
	{
		$parent_module=vtlib_purify($_REQUEST['parent_module']);
		$pass_url .="&parent_module=".$parent_module;
	}
	if(isset($_REQUEST['fromPotential']) && (isset($_REQUEST['acc_id']) && $_REQUEST['acc_id']!= ''))
	{
		$pass_url .="&parent_module=Accounts&relmod_id=".vtlib_purify($_REQUEST['acc_id']);
	}

	// vtlib Customization : For uitype 10 popup during paging
	if($_REQUEST['form'] == 'vtlibPopupView') {
		$pass_url .= '&form=vtlibPopupView&forfield='.vtlib_purify($_REQUEST['forfield']).'&srcmodule='.vtlib_purify($_REQUEST['srcmodule']).'&forrecord='.vtlib_purify($_REQUEST['forrecord']);
	}
	// END
    //Added to reduce the no. of queries logging for non-admin user -- by Minnie-start
    $field_list = array();
    $j=0;
    require('user_privileges/user_privileges_'.$current_user->id.'.php');

    if($oCv && isset($oCv->list_fields))
    	$focus->search_fields = $oCv->list_fields;

    foreach($focus->search_fields as $name=>$tableinfo)
    {
        $fieldname = $focus->search_fields_name[$name];
    	if($oCv && isset($oCv->list_fields_name))
			$fieldname = $oCv->list_fields_name[$name];
        array_push($field_list, $fieldname);
        $j++;
    }
    $field=Array();
    if($is_admin==false && $module != 'Users')
    {
    	//crmv@7216
        if($module == 'Emails' || $module == 'Fax' || $module == 'Sms')
        {
            $query  = "SELECT fieldname FROM ".$table_prefix."_field WHERE tabid = ?";
            $params = array($tabid);
        }
      //crmv@7216e
        else
        {
            $profileList = getCurrentUserProfileList();
            $query  = "SELECT ".$table_prefix."_field.fieldname
                FROM ".$table_prefix."_field
                INNER JOIN ".$table_prefix."_def_org_field
                    ON ".$table_prefix."_def_org_field.fieldid = ".$table_prefix."_field.fieldid
                WHERE ".$table_prefix."_field.tabid = ?
                AND ".$table_prefix."_def_org_field.visible=0
                AND ".$table_prefix."_field.fieldname IN (". generateQuestionMarks($field_list) .")";
            $params = array($tabid, $field_list);
			$query.=" AND EXISTS(SELECT * FROM ".$table_prefix."_profile2field WHERE ".$table_prefix."_profile2field.fieldid = ".$table_prefix."_field.fieldid AND ".$table_prefix."_profile2field.visible=0";
			if (count($profileList) > 0) {
				 $query.=" AND ".$table_prefix."_profile2field.profileid IN (". generateQuestionMarks($profileList) .") ";
				 array_push($params, $profileList);
			}
			$query.=" )";
        }
        $result = $adb->pquery($query, $params);
        for($k=0;$k < $adb->num_rows($result);$k++)
        {
            $field[]=$adb->query_result($result,$k,"fieldname");
        }
    }
    //end
    $theme_path="themes/".$theme."/";
    $image_path=$theme_path."images/";
    $focus->filterInactiveFields($module);
    foreach($focus->search_fields as $name=>$tableinfo)
    {
        $fieldname = $focus->search_fields_name[$name];
       	if($oCv && isset($oCv->list_fields_name))
			$fieldname = $oCv->list_fields_name[$name];
        $tabid = getTabid($module);

        global $current_user;
                require('user_privileges/user_privileges_'.$current_user->id.'.php');

                if($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] ==0 || in_array($fieldname,$field) || $module == 'Users')
                {

            if(isset($focus->sortby_fields) && $focus->sortby_fields !='')
                        {
                                foreach($focus->search_fields[$name] as $tab=>$col)
                                {
                                        if(in_array($col,$focus->sortby_fields))
                                        {
                                                if($order_by == $col)
                                                {
                                                        if($sorder == 'ASC')
                                                        {
                                                                $sorder = "DESC";
                                                                $arrow = "<img src ='".$image_path."arrow_down.gif' border='0'>";
                                                         }
                                                        else
                                                        {
                                                                $sorder = 'ASC';
                                                                $arrow = "<img src ='".$image_path."arrow_up.gif' border='0'>";
                                                        }
                                                }
                                                //crmv@9808
                                                $name = "<a href='javascript:;' onClick=\"getListViewSorted_js('".$module."','".$sort_qry.$pass_url."&order_by=".$col."&sorder=".$sorder."')\" class='listFormHeaderLinks'>".getTranslatedString($name,$module)."&nbsp;".$arrow."</a>";
                                                $arrow = '';
                                        }
                                        else
                                                $name = getTranslatedString($name,$module);
                                                //crmv@9808e
                                }
                        }
            $list_header[]=$name;
        }
    }
    $log->debug("Exiting getSearchListViewHeader method ...");
    return $list_header;

}

/**This function generates the navigation array in a listview
*Param $display - start value of the navigation
*Param $noofrows - no of records
*Param $limit - no of entries per page
*Returns an array type
*/

//code contributed by raju for improved pagination
function getNavigationValues($display, $noofrows, $limit)
{
	global $log;
	$log->debug("Entering getNavigationValues(".$display.",".$noofrows.",".$limit.") method ...");
	$navigation_array = Array();
	global $limitpage_navigation;
	if(isset($_REQUEST['allflag']) && $_REQUEST['allflag'] == 'All'){
		$navigation_array['start'] =1;
		$navigation_array['first'] = 1;
		$navigation_array['end'] = 1;
		$navigation_array['prev'] =0;
		$navigation_array['next'] =0;
		$navigation_array['end_val'] =$noofrows;
		$navigation_array['current'] =1;
		$navigation_array['allflag'] ='Normal';
		$navigation_array['verylast'] =1;
		$log->debug("Exiting getNavigationValues method ...");
		return $navigation_array;
	}
	 if($noofrows != 0)
        {
                if(((($display * $limit)-$limit)+1) > $noofrows)
                {
                        $display =floor($noofrows / $limit);
                }
                $start = ((($display * $limit) - $limit)+1);
        }
        else
        {
                $start = 0;
        }

	$end = $start + ($limit-1);
	if($end > $noofrows)
	{
		$end = $noofrows;
	}
	$paging = ceil ($noofrows / $limit);
	// Display the navigation
	if ($display > 1) {
		$previous = $display - 1;
	}
	else {
		$previous=0;
	}
	if($noofrows < $limit)
	{
		$first = '';
	}
	elseif ($noofrows != $limit) {
		$last = $paging;
		$first = 1;
		if ($paging > $limitpage_navigation) {
			$first = $display-floor(($limitpage_navigation/2));
			if ($first<1) $first=1;
			$last = ($limitpage_navigation - 1) + $first;
		}
		if ($last > $paging ) {
			$first = $paging - ($limitpage_navigation - 1);
			$last = $paging;
		}
	}
	if ($display < $paging) {
		$next = $display + 1;
	}
	else {
		$next=0;
	}
	$navigation_array['start'] = $start;
	$navigation_array['first'] = $first;
	$navigation_array['end'] = $last;
	$navigation_array['prev'] = $previous;
	$navigation_array['next'] = $next;
	$navigation_array['end_val'] = $end;
	$navigation_array['current'] = $display;
	$navigation_array['allflag'] ='All';
	$navigation_array['verylast'] =$paging;
	$log->debug("Exiting getNavigationValues method ...");
	return $navigation_array;

}


//End of code contributed by raju for improved pagination

/**This function generates the List view entries in a list view
*Param $focus - module object
*Param $list_result - resultset of a listview query
*Param $navigation_array - navigation values in an array
*Param $relatedlist - check for related list flag
*Param $returnset - list query parameters in url string
*Param $edit_action - Edit action value
*Param $del_action - delete action value
*Param $oCv - vtiger_customview object
*Returns an array type
*/

//parameter added for vtiger_customview $oCv 27/5
function getListViewEntries($focus, $module,$list_result,$navigation_array,$relatedlist='',$returnset='',$edit_action='EditView',$del_action='Delete',$oCv='',$page='',$selectedfields='',$contRelatedfields='',$skipActions=false)
{
	global $log;
	global $mod_strings;
	$log->debug("Entering getListViewEntries(".(is_object($focus)? get_class($focus) : $focus).",". $module.",".$list_result.",".$navigation_array.",".$relatedlist.",".$returnset.",".$edit_action.",".$del_action.",".(is_object($focus)? get_class($focus) : $focus).") method ..."); //crmv@31429
	$tabname = getParentTab();
	global $adb,$current_user, $table_prefix;
	global $app_strings;
	$noofrows = $adb->num_rows($list_result);
	$list_block = Array();
	global $theme;
	$evt_status = '';
	$theme_path="themes/".$theme."/";
	$image_path=$theme_path."images/";
	//getting the vtiger_fieldtable entries from database
	$tabid = getTabid($module);

	//crmv@7230
	$used_status_field = getUsedStatusField($module);
	//crmv@7230e

	//added for vtiger_customview 27/5
	if($oCv)
	{
		if(isset($oCv->list_fields))
		{
			$focus->list_fields = $oCv->list_fields;
		}
	}
	if(is_array($selectedfields) && $selectedfields != '')
	{
		$focus->list_fields = $selectedfields;
	}

	// Remove fields which are made inactive
	$focus->filterInactiveFields($module);

	//Added to reduce the no. of queries logging for non-admin user -- by minnie-start
	$field_list = array();
	$j=0;
	require('user_privileges/user_privileges_'.$current_user->id.'.php');
	foreach($focus->list_fields as $name=>$tableinfo)
	{
		$fieldname = $focus->list_fields_name[$name];
		if($oCv)
		{
			if(isset($oCv->list_fields_name))
			{
				$fieldname = $oCv->list_fields_name[$name];
			}
		}
		if ($fieldname == ''){
			foreach ($tableinfo as $nameinfo){
				if (in_array($nameinfo,$focus->list_fields_name))
					$fieldname = $nameinfo;
			}

		}
		if($fieldname == 'accountname' && $module != 'Accounts')
		{
			$fieldname = 'account_id';
		}
		if($fieldname == 'lastname' &&($module == 'SalesOrder'|| $module == 'PurchaseOrder' || $module == 'Invoice' || $module == 'Quotes'||$module == 'Calendar'))
			$fieldname = 'contact_id';

		if($fieldname == 'productname' && $module != 'Products')
		{
			 $fieldname = 'product_id';
		}

		array_push($field_list, $fieldname);
		$j++;
	}
	if ($module == 'Calendar'){
		array_push($field_list,'taskstatus');
	}
	$field=Array();
	if($is_admin==false)
	{
		if($module == 'Emails' || $module == 'Fax' || $module == 'Sms')
		{
			$query  = "SELECT fieldname FROM ".$table_prefix."_field WHERE tabid = ? and ".$table_prefix."_field.presence in (0,2)";
			$params = array($tabid);
		}
		else
		{
			$profileList = getCurrentUserProfileList();
			$params = array();
			//crmv@9433
			$query  = "SELECT ".$table_prefix."_field.fieldname,".$table_prefix."_field.fieldid
				FROM ".$table_prefix."_field
				INNER JOIN ".$table_prefix."_def_org_field
					ON ".$table_prefix."_def_org_field.fieldid = ".$table_prefix."_field.fieldid";

			if($module == "Calendar")
				$query .=" WHERE ".$table_prefix."_field.tabid in (9,16) and ".$table_prefix."_field.presence in (0,2)";
			else {
				$query .=" WHERE ".$table_prefix."_field.tabid = ? and ".$table_prefix."_field.presence in (0,2)";
				array_push($params, $tabid);
			}

			$query .="AND ".$table_prefix."_def_org_field.visible = 0
					AND ".$table_prefix."_field.fieldname IN (". generateQuestionMarks($field_list) .")";
			array_push($params, $field_list);
			$query.=" AND EXISTS(SELECT * FROM ".$table_prefix."_profile2field WHERE ".$table_prefix."_profile2field.fieldid = ".$table_prefix."_field.fieldid AND ".$table_prefix."_profile2field.visible = 0";
			if (count($profileList) > 0) {
				 $query.=" AND ".$table_prefix."_profile2field.profileid IN (". generateQuestionMarks($profileList) .") ";
				 array_push($params, $profileList);
			}
			$query.=")";
		}

		$result = $adb->pquery($query, $params);
		for($k=0;$k < $adb->num_rows($result);$k++)
		{
			$field[]=$adb->query_result($result,$k,"fieldname");
            $conditional_fieldid[$adb->query_result($result,$k,"fieldname")] = $adb->query_result($result,$k,"fieldid");
		}
		//crmv@9433 end
	}
    //crmv@9433
    if (vtlib_isModuleActive('Conditionals') && !is_admin($current_user)){
    	include_once('modules/Conditionals/ConditionalsUI.php');	//crmv@16877
    	$conditional_fields_arr = getConditionalFields($module);
    	if (is_array($conditional_fields_arr)){
    		foreach ($conditional_fields_arr as $cond_fields_add){
    			$conditional_fields[$cond_fields_add[fieldname]] = $cond_fields_add[columnname];
    		}
    	}
	}
	//crmv@9433 end
	//constructing the uitype and columnname array
	$ui_col_array=Array();
	$readonly_array = array();	//crmv@sdk-18508

	$params = array();
	$query = "SELECT uitype, columnname, fieldname, fieldid, readonly FROM ".$table_prefix."_field ";	//crmv@sdk-18508

	if($module == "Calendar")
		$query .=" WHERE ".$table_prefix."_field.tabid in (9,16) and ".$table_prefix."_field.presence in (0,2)";
	else {
		$query .=" WHERE ".$table_prefix."_field.tabid = ? and ".$table_prefix."_field.presence in (0,2)";
		array_push($params, $tabid);
	}
	$query .= " AND fieldname IN (". generateQuestionMarks($field_list).") ";
	array_push($params, $field_list);
	$result = $adb->pquery($query, $params);
	$num_rows=$adb->num_rows($result);
	for($i=0;$i<$num_rows;$i++)
	{
		$tempArr=array();
		$uitype=$adb->query_result($result,$i,'uitype');
		$columnname=$adb->query_result($result,$i,'columnname');
		$field_name=$adb->query_result($result,$i,'fieldname');
		$tempArr[$uitype]=$columnname;
		$ui_col_array[$field_name]=$tempArr;
		//crmv@sdk-18508
        $readonly=$adb->query_result($result,$i,'readonly');
        $readonly_array[$field_name]=$readonly;
        //crmv@sdk-18508 e
	}
	//end
	if($navigation_array['start'] !=0)
	for ($i=1; $i<=$noofrows; $i++)
	{
		$list_header =Array();
		//Getting the entityid
		if($module != 'Users')
		{
			$entity_id = $adb->query_result($list_result,$i-1,"crmid");
			//crmv@23687
			if ($entity_id == '' && $focus->table_index != '') {
				$entity_id = $adb->query_result($list_result,$i-1,$focus->table_index);
			}
			//crmv@23687e
			$owner_id = $adb->query_result($list_result,$i-1,"smownerid");
		}else
		{
			$entity_id = $adb->query_result($list_result,$i-1,"id");
		}
		//crmv@17001 : Private Permissions
		if($module == 'Calendar')
			$visibility = $adb->query_result($list_result,$i-1,"visibility");
		//crmv@17001e
		//crmv@21618
		$varreturnset = '';
		if($returnset=='')
			$varreturnset = '&return_module='.$module.'&return_action=index';
		else
			$varreturnset = $returnset;
		if($module == 'Calendar')
		{
			$actvity_type = $adb->query_result($list_result,$i-1,'activitytype');
			if($actvity_type == 'Task')
				$varreturnset .= '&activity_mode=Task';
			else
				$varreturnset .= '&activity_mode=Events';
		}
		//crmv@21618 e
        //crmv@9433
		if (vtlib_isModuleActive('Conditionals') && !is_admin($current_user) && is_array($conditional_fields)){
			foreach ($conditional_fields as $field_cond=>$column_cond){
				$focus->column_fields[$field_cond] = $adb->query_result($list_result,$i-1,$column_cond);
			}
			include_once('modules/Conditionals/Conditionals.php');
			$conditionals_obj = new Conditionals($module,$tabid,$focus->column_fields);
			$conditional_rules = $conditionals_obj->permissions;
		}
		//crmv@9433 end

		//crmv@18744
		//Added for Actions ie., edit and delete links in listview
		$links_info = "";
		//sk@2
	    if ($module =="Projects" && isProjectLeader() && !isProjectAdmin())
	    {

	    }
	    elseif ($module =="Projects" && isProjectAdmin() )
		{
  			$edit_link = getListViewEditLink($module,$entity_id,$relatedlist,$varreturnset,$list_result,$list_result_count);
  			if(isset($_REQUEST['start']) && $_REQUEST['start'] > 1)
			   	$links_info .= "<a href=\"$edit_link&start=".$_REQUEST['start']."\"><img src='".vtiger_imageurl('small_edit.png',$theme)."' title='".getTranslatedString("LBL_EDIT",$module)."' border=0 /></a> ";
		  	else
			   	$links_info .= "<a href=\"$edit_link\"><img src='".vtiger_imageurl('small_edit.png',$theme)."' title='".getTranslatedString("LBL_EDIT",$module)."' border=0 /></a> ";

				$links_info .=  "&nbsp;";
  			$del_link = getListViewDeleteLink($module,$entity_id,$relatedlist,$varreturnset);
  			$links_info .=	"<a href='javascript:confirmdelete(\"".addslashes(urlencode($del_link))."\",\"$module\")'><img src='".vtiger_imageurl('small_delete.png',$theme)."' title='".getTranslatedString("LBL_DELETE",$module)."' border=0 /></a>";
	    }
	    else
	    {
			if(!(is_array($selectedfields) && $selectedfields != ''))
			{
				if(isPermitted($module,"EditView","") == 'yes'){
					$edit_link = getListViewEditLink($module,$entity_id,$relatedlist,$varreturnset,$list_result,$list_result_count);
					if(isset($_REQUEST['start']) && $_REQUEST['start'] > 1 && $module != 'Emails')
						$links_info .= "<a href=\"$edit_link&start=".vtlib_purify($_REQUEST['start'])."\"><img src='".vtiger_imageurl('small_edit.png',$theme)."' title='".getTranslatedString("LBL_EDIT",$module)."' border=0 /></a> ";
					else
						$links_info .= "<a href=\"$edit_link\"><img src='".vtiger_imageurl('small_edit.png',$theme)."' title='".getTranslatedString("LBL_EDIT",$module)."' border=0 /></a> ";
				}

				if(isPermitted($module,"Delete","") == 'yes'){
					$del_link = getListViewDeleteLink($module,$entity_id,$relatedlist,$varreturnset);
					if($links_info != "" && $del_link != "")
						$links_info .=  "&nbsp;";
					if($del_link != "")
						$links_info .=	"<a href='javascript:confirmdelete(\"".addslashes(urlencode($del_link))."\",\"$module\")'><img src='".vtiger_imageurl('small_delete.png',$theme)."' title='".getTranslatedString("LBL_DELETE",$module)."' border=0 /></a>";
				}
				if(isPermitted($module,"EditView","") == 'yes' and $module == 'Timecards' and !empty($relatedlist)) {
	                $url  = "index.php?module=Timecards&action=TimecardsAjax&file=TimeCardMvUp&record=$entity_id&parenttab=Support";	//crmv@fix
	                $ltc  = '<a href="'.$url.'"><img src="modules/Timecards/images/sortup.png" border=0 alt="'.getTranslatedString('LBL_TCMoveUp','Timecards').'" title="'.getTranslatedString('LBL_TCMoveUp','Timecards').'"></a>&nbsp;'; // Sort up
	                $url  = "index.php?module=Timecards&action=TimecardsAjax&file=TimeCardMvDown&record=$entity_id&parenttab=Support";	//crmv@fix
	                $ltc .= '<a href="'.$url.'"><img src="modules/Timecards/images/sortdown.png" border=0 alt="'.getTranslatedString('LBL_TCMoveDown','Timecards').'" title="'.getTranslatedString('LBL_TCMoveDown','Timecards').'"></a>'; // Sort down
					$links_info .=	"&nbsp;$ltc";
				}
			}
		}
 		//sk@2e

		// Record Change Notification
		//crmv@23685
		$change_indic = PerformancePrefs::getBoolean('LISTVIEW_RECORD_CHANGE_INDICATOR', true);
		if(method_exists($focus, 'isViewed') && $change_indic) {
		//crmv@23685e
			if(!$focus->isViewed($entity_id)) {
				$links_info .= "&nbsp;<img src='" . vtiger_imageurl('important1.gif', $theme) . "' border=0>";
			}
		}
		// END
		if(!$skipActions && ($change_indic || $links_info != "")) //crmv@23685
			$list_header[] = $links_info;
		//crmv@18744e

		foreach($focus->list_fields as $name=>$tableinfo)
		{
			$fieldname = $focus->list_fields_name[$name];
			$column_name = ''; // crmv@25610

			//added for vtiger_customview 27/5
			if($oCv) {
				if(isset($oCv->list_fields_name)) {
					$fieldname = $oCv->list_fields_name[$name];
					if($fieldname == 'accountname' && $module != 'Accounts') {
						$fieldname = 'account_id';
					}
					if($fieldname == 'lastname' &&($module == 'SalesOrder'|| $module == 'PurchaseOrder' || $module == 'Invoice' || $module == 'Quotes'||$module == 'Calendar' )) {
						$fieldname = 'contact_id';
					}
					if($fieldname == 'productname' && $module != 'Products') {
						$fieldname = 'product_id';
					}
				} else {
					$fieldname = $focus->list_fields_name[$name];
				}
			} else {
				$fieldname = $focus->list_fields_name[$name];
				if($fieldname == 'accountname' && $module != 'Accounts') {
					$fieldname = 'account_id';
				}
				if($fieldname == 'lastname' && ($module == 'SalesOrder'|| $module == 'PurchaseOrder' || $module == 'Invoice' || $module == 'Quotes'|| $module == 'Calendar')) {
					$fieldname = 'contact_id';
				}
				if($fieldname == 'productname' && $module != 'Products') {
					$fieldname = 'product_id';
				}
			}
			//crmv@9433
			if (vtlib_isModuleActive('Conditionals')){
				include_once('modules/Conditionals/ConditionalsUI.php');
				$conditional_permissions = null;
	            if(!is_admin($current_user) && $fieldname != "") {
         			$conditional_permissions = $conditional_rules[$conditional_fieldid[$fieldname]];
            	}
			}
            //crmv@9433 end
			if($is_admin==true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] ==0 || in_array($fieldname,$field) || $fieldname == '' || ($name=='Close' && $module=='Calendar')) {
            	//crmv@9433		crmv@sdk-18508
				$readonly = $readonly_array[$fieldname];
            	if(vtlib_isModuleActive('Conditionals') && $conditional_permissions != null && $conditional_permissions['f2fp_visible'] == "0") {
					$readonly = 100;
            	}
				$sdk_files = SDK::getViews($module,'related');
				if (!empty($sdk_files)) {
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
            	if ($readonly == 100) {
	            	$value = "<font color='red'>".getTranslatedString('LBL_NOT_ACCESSIBLE')."</font>";
            	}
            	//crmv@sdk-18508e
            	//crmv@17001 : Private Permissions
            	elseif ($module == 'Calendar' && !is_admin($current_user) && $owner_id != $current_user->id && $visibility == 'Private' && $fieldname != '' && !in_array($fieldname,array('assigned_user_id','date_start','time_start','time_end','due_date','activitytype','visibility','duration_hours','duration_minutes'))) {
            		if ($fieldname == 'subject')
            			$value = getTranslatedString('Private Event','Calendar');
            		else
            			$value = "<font color='red'>".getTranslatedString('LBL_NOT_ACCESSIBLE')."</font>";
            	}
            	//crmv@17001e
				elseif($fieldname == '') {
				//crmv@9433 end
					$table_name = '';
					$column_name = '';
					foreach($tableinfo as $tablename=>$colname) {
						$table_name=$tablename;
						$column_name = $colname;
					}
					$value = $adb->query_result($list_result,$i-1,$colname);
				}
				else {
					if($module == 'Calendar') {
						$act_id = $adb->query_result($list_result,$i-1,"activityid");

						$cal_sql = "select activitytype from ".$table_prefix."_activity where activityid=?";
						$cal_res = $adb->pquery($cal_sql,array($act_id));
						if($adb->num_rows($cal_res)>=0)
							$activitytype = $adb->query_result($cal_res,0,"activitytype");
					}
					if(($module == 'Calendar' || $module == 'Emails' || $module == 'Fax' || $module == 'Sms' || $module == 'HelpDesk' || $module == 'Invoice' || $module == 'Leads' || $module == 'Contacts') && (($fieldname=='parent_id') || ($name=='Contact Name') || ($name=='Close') || ($fieldname == 'firstname'))) {
						if($module == 'Calendar'){
							if($fieldname=='status'){
								if($activitytype == 'Task'){
									$fieldname='taskstatus';
								} else {
									$fieldname='eventstatus';
								}
							}
							if($activitytype == 'Task' ) {
								if(getFieldVisibilityPermission('Calendar',$current_user->id,$fieldname) == '0'){
									$has_permission = 'yes';
								} else {
									$has_permission = 'no';
								}
							} else {
								if(getFieldVisibilityPermission('Events',$current_user->id,$fieldname) == '0'){
									$has_permission = 'yes';
								} else {
									$has_permission = 'no';
								}
							}
						}
						//crmv@23515
						if($module == 'Calendar' && $fieldname == 'parent_id') {
							$value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$i-1,"list","",$returnset,$oCv->setdefaultviewid);
						//crmv@23515e
						} elseif($module != 'Calendar' || ($module == 'Calendar' && $has_permission == 'yes')) {
							if ($fieldname=='parent_id') {
								$value=getRelatedTo($module,$list_result,$i-1);
							}
							if($name=='Contact Name') {
								$contact_id = $adb->query_result($list_result,$i-1,"contactid");
								$contact_name = getFullNameFromQResult($list_result,$i-1,"Contacts");
								$value="";
								//Added to get the contactname for activities custom view - t=2190
								if($contact_id != '' && $contact_name == '') {
									$contact_name = getContactName($contact_id);
								}
								if(($contact_name != "") && ($contact_id !='NULL')) {
									// Fredy Klammsteiner, 4.8.2005: changes from 4.0.1 migrated to 4.2
									$value =  "<a href='index.php?module=Contacts&action=DetailView&parenttab=".$tabname."&record=".$contact_id."' style='".$P_FONT_COLOR."'>".$contact_name."</a>"; // Armando Lüscher 05.07.2005 -> §priority -> Desc: inserted style="$P_FONT_COLOR"
								}
							}
							if($fieldname == "firstname") {
								$first_name = textlength_check($adb->query_result($list_result,$i-1,"firstname"));

								$value = '<a href="index.php?action=DetailView&module='.$module.'&parenttab='.$tabname.'&record='.$entity_id.'">'.$first_name.'</a>';
							}

							if ($name == 'Close') {
								$status = $adb->query_result($list_result,$i-1,"status");
								$activityid = $adb->query_result($list_result,$i-1,"activityid");
								if(empty($activityid)){
									$activityid = $adb->query_result($list_result, $i-1, "tmp_activity_id");
								}
								$activitytype = $adb->query_result($list_result,$i-1,"activitytype");
								// TODO - Picking activitytype when it is not present in the Custom View.
								// Going forward, this column should be added to the select list if not already present as a performance improvement.
								if (empty($activitytype)) {
									$activitytypeRes = $adb->pquery('SELECT activitytype FROM ".$table_prefix."_activity WHERE activityid=?', array($activityid));
									if ($adb->num_rows($activitytypeRes) > 0) {
										$activitytype = $adb->query_result($activitytypeRes, 0, 'activitytype');
									}
								}
								if ($activitytype != 'Task' && $activitytype != 'Emails' && $activitytype != 'Fax' && $activitytype != 'Sms') {
									$eventstatus = $adb->query_result($list_result,$i-1,"eventstatus");
									if(isset($eventstatus)) {
										$status = $eventstatus;
									}
								}
								if($status =='Deferred' || $status == 'Completed' || $status == 'Held' || $status == '') {
									$value="";
								} else {
									if($activitytype=='Task')
										$evt_status='&status=Completed';
									else
										$evt_status='&eventstatus=Held';
									if(isPermitted("Calendar",'EditView',$activityid) == 'yes') {
										if ($returnset == '') {
											$returnset = '&return_module=Calendar&return_action=ListView&return_id='.$activityid.'&return_viewname='.$oCv->setdefaultviewid;
										}
										// Fredy Klammsteiner, 4.8.2005: changes from 4.0.1 migrated to 4.2
										$value = "<a href='index.php?action=Save&module=Calendar&record=".$activityid."&parenttab=".$tabname."&change_status=true".$returnset.$evt_status."&start=".$navigation_array['current']."' style='".$P_FONT_COLOR."'>X</a>"; // Armando Lüscher 05.07.2005 -> §priority -> Desc: inserted style="$P_FONT_COLOR"
									} else {
											$value = "";
									}
								}
							}

						} else {
							$value = "";
						}
					} elseif($module == "Documents" && ($fieldname == 'filelocationtype' || $fieldname == 'filename' || $fieldname == 'filesize' || $fieldname == 'filestatus' || $fieldname == 'filetype')) {
						$value = $adb->query_result($list_result,$i-1,$fieldname);
						if($fieldname == 'filelocationtype') {
							if($value == 'I')
								$value = getTranslatedString('LBL_INTERNAL',$module);
							elseif($value == 'E')
								$value = getTranslatedString('LBL_EXTERNAL',$module);
							else
								$value = ' --';
						}
						if($fieldname == 'filename') {
							$downloadtype = $adb->query_result($list_result,$i-1,'filelocationtype');
							if($downloadtype == 'I') {
								$fld_value = $value;
								$ext_pos = strrpos($fld_value, ".");
								$ext =substr($fld_value, $ext_pos + 1);
								$ext = strtolower($ext);
								if($value != ''){
								if($ext == 'bin' || $ext == 'exe' || $ext == 'rpm')
									$fileicon="<img src='" . vtiger_imageurl('fExeBin.gif', $theme) . "' hspace='3' align='absmiddle' border='0'>";
								elseif($ext == 'jpg' || $ext == 'gif' || $ext == 'bmp')
									$fileicon="<img src='" . vtiger_imageurl('fbImageFile.gif', $theme) . "' hspace='3' align='absmiddle' border='0'>";
								elseif($ext == 'txt' || $ext == 'doc' || $ext == 'xls')
									$fileicon="<img src='" . vtiger_imageurl('fbTextFile.gif', $theme) . "' hspace='3' align='absmiddle' border='0'>";
								elseif($ext == 'zip' || $ext == 'gz' || $ext == 'rar')
									$fileicon="<img src='" . vtiger_imageurl('fbZipFile.gif', $theme) . "' hspace='3' align='absmiddle'	border='0'>";
								else
									$fileicon="<img src='" . vtiger_imageurl('fbUnknownFile.gif', $theme) . "' hspace='3' align='absmiddle' border='0'>";
								}
							} elseif($downloadtype == 'E') {
								if(trim($value) != '' ) {
									$fld_value = $value;
									$fileicon = "<img src='" . vtiger_imageurl('fbLink.gif', $theme) . "' alt='".getTranslatedString('LBL_EXTERNAL_LNK',$module)."' title='".getTranslatedString('LBL_EXTERNAL_LNK',$module)."' hspace='3' align='absmiddle' border='0'>";
								}
								else {
									$fld_value = '--';
									$fileicon = '';
								}
							} else {
								$fld_value = ' --';
								$fileicon = '';
							}

							$file_name = $adb->query_result($list_result,$i-1,'filename');
							$notes_id = $adb->query_result($list_result,$i-1,'crmid');
							$folder_id = $adb->query_result($list_result,$i-1,'folderid');
							$download_type = $adb->query_result($list_result,$i-1,'filelocationtype');
							$file_status = $adb->query_result($list_result,$i-1,'filestatus');
							$fileidQuery = "select attachmentsid from ".$table_prefix."_seattachmentsrel where crmid=?";
							$fileidres = $adb->pquery($fileidQuery,array($notes_id));
							$fileid = $adb->query_result($fileidres,0,'attachmentsid');
							if($file_name != '' && $file_status == 1) {
								if($download_type == 'I' ) {
									$fld_value = "<a href='index.php?module=uploads&action=downloadfile&entityid=$notes_id&fileid=$fileid' title='".getTranslatedString("LBL_DOWNLOAD_FILE",$module)."' onclick='javascript:dldCntIncrease($notes_id);'>".$fld_value."</a>";
								} elseif($download_type == 'E') {
									$fld_value = "<a target='_blank' href='$file_name' onclick='javascript:dldCntIncrease($notes_id);' title='".getTranslatedString("LBL_DOWNLOAD_FILE",$module)."'>".$fld_value."</a>";
								} else {
									$fld_value = ' --';
								}
							}
							$value = $fileicon.$fld_value;
						}
						if($fieldname == 'filesize') {
							$downloadtype = $adb->query_result($list_result,$i-1,'filelocationtype');
							if($downloadtype == 'I') {
								$filesize = $value;
								if($filesize < 1024)
									$value=$filesize.' B';
								elseif($filesize > 1024 && $filesize < 1048576)
									$value=round($filesize/1024,2).' KB';
								else if($filesize > 1048576)
									$value=round($filesize/(1024*1024),2).' MB';
							} else {
								$value = ' --';
							}
						}
						if($fieldname == 'filestatus') {
							$filestatus = $value;
							if($filestatus == 1)
								$value=getTranslatedString('yes',$module);
							elseif($filestatus == 0)
								$value=getTranslatedString('no',$module);
							else
								$value=' --';
						}
						if($fieldname == 'filetype') {
							$downloadtype = $adb->query_result($list_result,$i-1,'filelocationtype');
							$filetype = $adb->query_result($list_result,$i-1,'filetype');
							if($downloadtype == 'E' || $downloadtype != 'I') {
								$value = ' --';
							} else
								$value = $filetype;
						}
						if($fieldname == 'notecontent') {
							$value = decode_html($value);
							$value = textlength_check($value);
						}
					} elseif($module == "Products" && $name == "Related to") {
						$value=getRelatedTo($module,$list_result,$i-1);
					} elseif($name=='Contact Name' && ($module =='SalesOrder' || $module == 'Quotes' || $module == 'PurchaseOrder')) {
                        if($name == 'Contact Name') {
                            $contact_id = $adb->query_result($list_result,$i-1,"contactid");
							$contact_name = getFullNameFromQResult($list_result, $i-1,"Contacts");
                            $value="";
                            if(($contact_name != "") && ($contact_id !='NULL'))
                                  $value ="<a href='index.php?module=Contacts&action=DetailView&parenttab=".$tabname."&record=".$contact_id."' style='".$P_FONT_COLOR."'>".$contact_name."</a>";
                        }
                    } elseif($name == 'Product') {
						$product_id = textlength_check($adb->query_result($list_result,$i-1,"productname"));
						$value =  $product_id;
					} elseif($name=='Account Name' && $module == 'Accounts') {	//crmv@22814
						//modified for vtiger_customview 27/5
						if($module == 'Accounts') {
							$account_id = $adb->query_result($list_result,$i-1,"crmid");
							//$account_name = getAccountName($account_id);
							$account_name = textlength_check($adb->query_result($list_result,$i-1,"accountname"));
							// Fredy Klammsteiner, 4.8.2005: changes from 4.0.1 migrated to 4.2
							$value = '<a href="index.php?module=Accounts&action=DetailView&record='.$account_id.'&parenttab='.$tabname.'" style="'.$P_FONT_COLOR.'">'.$account_name.'</a>'; // Armando Lüscher 05.07.2005 -> §priority -> Desc: inserted style="$P_FONT_COLOR"
						}
						/*
						//crmv@12035
						elseif($module == 'Potentials' || $module == 'Contacts' || $module == 'Invoice' || $module == 'SalesOrder' || $module == 'Quotes') { //Potential,Contacts,Invoice,SalesOrder & Quotes  records   sort by Account Name
							$accountname = textlength_check($adb->query_result($list_result,$i-1,"accountname"));
							$accountid = $adb->query_result($list_result,$i-1,"accountid");
							$value = '<a href="index.php?module=Accounts&action=DetailView&record='.$accountid.'&parenttab='.$tabname.'" style="'.$P_FONT_COLOR.'">'.$accountname.'</a>';
						}
						//crmv@12035 end
						*/
						elseif($module == 'Projects') {
							$account_id = $adb->query_result($list_result,$i-1,"accountid");
							$accountname = $adb->query_result($list_result,$i-1,"accountname");
							$value = '<a href="index.php?module=Accounts&action=DetailView&record='.$account_id.'&parenttab='.$tabname.'">'.$accountname.'</a>'; // Armando L�scher 05.07.2005 -> $priority -> Desc: inserted style="$P_FONT_COLOR"
						} else {
							$account_id = $adb->query_result($list_result,$i-1,"accountid");
							$account_name = getAccountName($account_id);
							$acc_name = textlength_check($account_name);
							// Fredy Klammsteiner, 4.8.2005: changes from 4.0.1 migrated to 4.2
							$value = '<a href="index.php?module=Accounts&action=DetailView&record='.$account_id.'&parenttab='.$tabname.'" style="'.$P_FONT_COLOR.'">'.$acc_name.'</a>'; // Armando L�scher 05.07.2005 -> $priority -> Desc: inserted style="$P_FONT_COLOR"
						}
					} elseif(( $module == 'HelpDesk' || $module == 'Timecards' || $module == 'PriceBook' || $module == 'Quotes' || $module == 'PurchaseOrder' || $module == 'Faq') && $name == 'Product Name') {
					    if($module == 'HelpDesk' || $module == 'Faq' || $module == 'Timecards')
							$product_id = $adb->query_result($list_result,$i-1,"product_id");
						else
							$product_id = $adb->query_result($list_result,$i-1,"productid");

						if($product_id != '')
							$product_name = getProductName($product_id);
						else
							$product_name = '';

						$value = '<a href="index.php?module=Products&action=DetailView&parenttab='.$tabname.'&record='.$product_id.'">'.textlength_check($product_name).'</a>';
					}
                    //crmv@7214
                    elseif (($module == 'Products' or $module == 'HelpDesk') && $name == 'Support Expiry Date') {
                        $expiry_date = $adb->query_result($list_result,$i-1,"expiry_date");
                        if ($module == 'HelpDesk'){
                            $sql = "select product_id from ".$table_prefix."_troubletickets where ticketid=?";
                            $result = $adb->pquery($sql, array($adb->query_result($list_result,$i-1,"crmid")));
                            $product_id = $adb->query_result($result,0,"product_id");
                            if($product_id != '') {
                                $sql = "select expiry_date from ".$table_prefix."_products where productid=?";
                                $result = $adb->pquery($sql, array($product_id));
                                $expiry_date = $adb->query_result($result,0,"expiry_date");
                            }
                        }
                        $today = date("Y-m-d");
				        if (trim($expiry_date) == ''){

				        }
				        elseif ( $expiry_date >= $today){
				        	$secid = 'ok';
				        	$expiry_date = date("Y-m-d",strtotime($expiry_date));
				            $value = "<font color='green'>".$expiry_date." </font><img src=themes/images/ok.gif>";
				        }
				        elseif ( $expiry_date < $today){
				        	$expiry_date = date("Y-m-d",strtotime($expiry_date));
				            $value = "<font color='red'>".$expiry_date." </font><img src=themes/images/no.gif></font>";
				        }
                    }
                    //crmv@7214e
					elseif(($module == 'Quotes' && $name == 'Potential Name') || ($module == 'SalesOrder' && $name == 'Potential Name')) {
						$potential_id = $adb->query_result($list_result,$i-1,"potentialid");
						$potential_name = getPotentialName($potential_id);
						$value = '<a href="index.php?module=Potentials&action=DetailView&parenttab='.$tabname.'&record='.$potential_id.'">'.textlength_check($potential_name).'</a>';
					} elseif($module =='Emails' && $relatedlist != '' && ($name=='Subject' || $name=='Date Sent' || $name == 'To')) {
						$list_result_count = $i-1;
						$tmp_value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"list","",$returnset,$oCv->setdefaultviewid);
						$value = '<a href="javascript:;" onClick="ShowEmail(\''.$entity_id.'\');">'.textlength_check($tmp_value).'</a>';
						if($name == 'Date Sent') {
							$sql="select email_flag from ".$table_prefix."_emaildetails where emailid=?";
							$result=$adb->pquery($sql, array($entity_id));
							$email_flag=$adb->query_result($result,0,"email_flag");
							if($email_flag != 'SAVED')
								$value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"list","",$returnset,$oCv->setdefaultviewid);
							else
								$value = '';
						}
					} elseif($module == 'Calendar' && ($fieldname!='taskstatus' && $fieldname!='eventstatus')) {
						if($activitytype == 'Task' ) {
							if(getFieldVisibilityPermission('Calendar',$current_user->id,$fieldname) == '0'){
								$list_result_count = $i-1;
								$value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"list","",$returnset,$oCv->setdefaultviewid);
							} else {
								$value = '';
							}
						} else {
							if(getFieldVisibilityPermission('Events',$current_user->id,$fieldname) == '0'){
								$list_result_count = $i-1;
								$value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"list","",$returnset,$oCv->setdefaultviewid);
							} else {
								$value = '';
							}
						}
					}
                    //crmv@7216
                    elseif($module =='Fax' && $relatedlist != '' && ($name=='Subject' || $name=='Date Sent'))
                    {
                        $list_result_count = $i-1;
                        $tmp_value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"list","",$returnset,$oCv->setdefaultviewid);
                        $value = '<a href="javascript:;" onClick="ShowFax(\''.$entity_id.'\');">'.textlength_check($tmp_value).'</a>';
                        if($name == 'Date Sent')
                        {
                            $sql="select fax_flag from ".$table_prefix."_faxdetails where faxid=?";
                            $result=$adb->pquery($sql, array($entity_id));
                            $fax_flag=$adb->query_result($result,0,"fax_flag");
                            if($fax_flag == 'SENT')
                                $value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"list","",$returnset,$oCv->setdefaultviewid);
                            else
                                $value = '';
                        }
                    }
                    //crmv@7216e
                    //crmv@7217
                    elseif($module =='Sms' && $relatedlist != '' && ($name=='Subject' || $name=='Date Sent'))
                    {
                        $list_result_count = $i-1;
                        $tmp_value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"list","",$returnset,$oCv->setdefaultviewid);
                        $value = '<a href="index.php?module=Sms&action=DetailView&parenttab='.$tabname.'&record='.$entity_id.'"">'.textlength_check($tmp_value).'</a>';	//crmv@16703
                        if($name == 'Date Sent')
                        {
							$sql="select sms_flag from ".$table_prefix."_smsdetails where smsid=?";
							$result=$adb->pquery($sql, array($entity_id));
							$sms_flag=$adb->query_result($result,0,"sms_flag");
							if($sms_flag == 'SENT')
								$value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"list","",$returnset,$oCv->setdefaultviewid);
							else
								$value = '';
                        }
                    }
					elseif ($module == 'Timecards' && $fieldname == 'description') { //timecards fix
							$value = decode_html($adb->query_result($list_result,$i-1,"description"));	//crmv@22862
							$value = textlength_check($value);
					}
                    //crmv@7217e
					else {
						$list_result_count = $i-1;
						$value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"list","",$returnset,$oCv->setdefaultviewid);
					}
				}

				// crmv@25610
				if ($module == 'Calendar' && !empty($value)){
					if (in_array($column_name,array('time_start','time_end'))) {
						$value = adjustTimezone("2010-01-01 ".trim($value), $current_user->timezonediff);
						$value = substr($value,11,5);
					} elseif (in_array($column_name,array('date_start','due_date'))) {
						switch ($column_name) {
							case 'date_start': $value .= ' '.$adb->query_result($list_result,$i-1,"time_start"); break;
							case 'due_date': $value .= ' '.$adb->query_result($list_result,$i-1,"time_end"); break;
						}
						$value = adjustTimezone($value, $current_user->timezonediff);
						$value = substr($value,0,10);
					}
				}
				// crmv@25610e

				// vtlib customization: For listview javascript triggers
				$value = "$value <span type='vtlib_metainfo' vtrecordid='{$entity_id}' vtfieldname='{$fieldname}' vtmodule='$module' style='display:none;'></span>";
				// END
				if ($module == 'Calendar' && in_array($colname,array('date_start','due_date')) && trim($value)!=''){
					$value = substr($value,0,10);
				}
				if($module == "Calendar" && $name == $app_strings['Close'])
				{
					if(isPermitted("Calendar","EditView") == 'yes')
					{
						if((getFieldVisibilityPermission('Events',$current_user->id,'eventstatus') == '0') || (getFieldVisibilityPermission('Calendar',$current_user->id,'taskstatus') == '0'))
						{
							array_push($list_header,$value);
						}
					}
				}
				else
					$list_header[] = $value;

			}

		}
		//sk@2
	    if ($module =="Projects" && $viewnamedesc['viewname']=="All" && (isProjectLeader() || isProjectAdmin()))
	    {
	      if(isset($Project_Workers[$entity_id])){
	        $list_header[] = implode(", ",$Project_Workers[$entity_id]);
	      } else {
	        $list_header[] = "";
	      }
	    }
	    //sk@2e

		//crmv@7230 / crmv@10445
		if($used_status_field != "") {
			$excolor=getEntityColor($tabid,getEntityStatus($tabid,$module,$used_status_field,$entity_id));
			$color = color_blend_by_opacity($excolor,50);
			$list_header['clv_color'] = $color;
		}
		//crmv@7230e / crmv@10445e
		$list_block[$entity_id] = $list_header;

	}
	$log->debug("Exiting getListViewEntries method ...");
	return $list_block;

}

/**This function generates the List view entries in a popup list view
*Param $focus - module object
*Param $list_result - resultset of a listview query
*Param $navigation_array - navigation values in an array
*Param $relatedlist - check for related list flag
*Param $returnset - list query parameters in url string
*Param $edit_action - Edit action value
*Param $del_action - delete action value
*Param $oCv - vtiger_customview object
*Returns an array type
*/


function getSearchListViewEntries($focus, $module,$list_result,$navigation_array,$form='',$oCv='')
{
    global $log;
    $log->debug("Entering getSearchListViewEntries(".(is_object($focus)? get_class($focus) : $focus).",". $module.",".$list_result.",".$navigation_array.") method ..."); //crmv@31429

    global $adb,$theme,$current_user,$list_max_entries_per_page, $table_prefix;
    $noofrows = $adb->num_rows($list_result);
    //crmv@7230
	$used_status_field = getUsedStatusField($module);
	//crmv@7230e
    $list_header = '';
    $theme_path="themes/".$theme."/";
    $image_path=$theme_path."images/";
    $list_block = Array();

    //getting the vtiger_fieldtable entries from database
    $tabid = getTabid($module);
    require('user_privileges/user_privileges_'.$current_user->id.'.php');

	if($oCv)
	{
		if(isset($oCv->list_fields))
		{
			$focus->search_fields = $oCv->list_fields;
		}
	}
    //Added to reduce the no. of queries logging for non-admin user -- by Minnie-start
    $field_list = array();
    $j=0;
    foreach($focus->search_fields as $name=>$arr)
    {
    	foreach ($arr as $table=>$fieldname){
        	array_push($field_list, $fieldname);
        	$j++;
    	}
    }
    $field=Array();
    if($is_admin==false && $module != 'Users')
    {
    	//crmv@7216+7217
        if($module == 'Emails' || $module == 'Fax' || $module == 'Sms' )
        {
            $query  = "SELECT fieldname FROM ".$table_prefix."_field WHERE tabid = ?";
            $params = array($tabid);
        }
        //crmv@7216e
        else
        {
        	//crmv@9433
            $profileList = getCurrentUserProfileList();
            $params = Array();
            $query  = "SELECT ".$table_prefix."_field.fieldname,".$table_prefix."_field.fieldid
                FROM ".$table_prefix."_field
                INNER JOIN ".$table_prefix."_def_org_field
                    ON ".$table_prefix."_def_org_field.fieldid = ".$table_prefix."_field.fieldid
                WHERE ".$table_prefix."_field.tabid = ?
                AND ".$table_prefix."_def_org_field.visible = 0
                AND (".$table_prefix."_field.fieldname IN (". generateQuestionMarks($field_list) .") or ".$table_prefix."_field.columnname IN (". generateQuestionMarks($field_list) ."))";
            array_push($params, $tabid);
            array_push($params, $field_list);
            array_push($params, $field_list);
            $query.=" AND EXISTS(SELECT * FROM ".$table_prefix."_profile2field WHERE ".$table_prefix."_profile2field.fieldid = ".$table_prefix."_field.fieldid AND ".$table_prefix."_profile2field.visible = 0";
	        if (count($profileList) > 0) {
			  	 $query.=" AND ".$table_prefix."_profile2field.profileid IN (". generateQuestionMarks($profileList) .") ";
			  	 array_push($params, $profileList);
			}
			$query.=")";
        }
        $result = $adb->pquery($query, $params);

        for($k=0;$k < $adb->num_rows($result);$k++)
        {
            $field[]=$adb->query_result($result,$k,"fieldname");
            $conditional_fieldid[$adb->query_result($result,$k,"fieldname")] = $adb->query_result($result,$k,"fieldid");
		}
		//crmv@9433 end
    }
    //crmv@9433
    if (vtlib_isModuleActive('Conditionals') && !is_admin($current_user)){
    	include_once('modules/Conditionals/ConditionalsUI.php');	//crmv@18917
    	$conditional_fields_arr = getConditionalFields($module);
    	if (is_array($conditional_fields_arr)){
    		foreach ($conditional_fields_arr as $cond_fields_add){
    			$conditional_fields[$cond_fields_add[fieldname]] = $cond_fields_add[columnname];
    		}
    	}
	}
	//crmv@9433 end
    //constructing the uitype and columnname array
    $ui_col_array=Array();
    $readonly_array = array();	//crmv@sdk-18508

    $query = "SELECT uitype, columnname, fieldname, readonly
        FROM ".$table_prefix."_field
        WHERE tabid=?
        AND columnname IN (". generateQuestionMarks($field_list) .")";
//		echo $adb->convert2Sql($query,$adb->flatten_array(array($tabid, $field_list)));
    $result = $adb->pquery($query, array($tabid, $field_list));
    $num_rows=$adb->num_rows($result);
    for($i=0;$i<$num_rows;$i++)
    {
        $tempArr=array();
        $uitype=$adb->query_result($result,$i,'uitype');
        $columnname=$adb->query_result($result,$i,'columnname');
        $field_name=$adb->query_result($result,$i,'fieldname');
        $tempArr[$uitype]=$columnname;
        $map_fieldname[$columnname] = $field_name;
        $ui_col_array[$field_name]=$tempArr;
        //crmv@sdk-18508
        $readonly=$adb->query_result($result,$i,'readonly');
        $readonly_array[$field_name]=$readonly;
        //crmv@sdk-18508 e
    }
    //end
    if($navigation_array['end_val'] > 0 || $noofrows > 0)
    {
        for ($i=1; $i<=$noofrows; $i++)
        {

            //Getting the entityid
            //crmv@26265
            if($module == 'Calendar') {
                $entity_id = $adb->query_result($list_result,$i-1,"activityid");
            } elseif ($module != 'Users') {
                $entity_id = $adb->query_result($list_result,$i-1,"crmid");
            } else {
                $entity_id = $adb->query_result($list_result,$i-1,"id");
            }
            //crmv@26265e
            //crmv@9433
			if (vtlib_isModuleActive('Conditionals') && !is_admin($current_user) && is_array($conditional_fields)){
				foreach ($conditional_fields as $field_cond=>$column_cond){
					$focus->column_fields[$field_cond] = $adb->query_result($list_result,$i-1,$column_cond);
				}
				include_once('modules/Conditionals/Conditionals.php');
				$conditionals_obj = new Conditionals($module,$tabid,$focus->column_fields);
				$conditional_rules = $conditionals_obj->permissions;
			}
			//crmv@9433 end
            $list_header=Array();
            foreach($focus->search_fields as $name=>$tableinfo)
            {
            	foreach ($tableinfo as $tbl=>$fldname){
            		$fieldname = $map_fieldname[$fldname];
            	}
				//crmv@9433
				if (vtlib_isModuleActive('Conditionals')){
					include_once('modules/Conditionals/ConditionalsUI.php');
					$conditional_permissions = null;
		            if(!is_admin($current_user) && $fieldname != "") {
	         			$conditional_permissions = $conditional_rules[$conditional_fieldid[$fieldname]];
	            	}
				}
	            //crmv@9433 end
                if($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] ==0 || in_array($fieldname,$field) || $module == 'Users')
                {
					//crmv@9433		crmv@sdk-18508
					$readonly = $readonly_array[$fieldname];
	            	if(vtlib_isModuleActive('Conditionals') && $conditional_permissions != null && $conditional_permissions['f2fp_visible'] == "0") {
						$readonly = 100;
	            	}
					$sdk_files = SDK::getViews($module,'popup');
					if (!empty($sdk_files)) {
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
	            	if ($readonly == 100) {
	            		$value = "<font color='red'>".getTranslatedString('LBL_NOT_ACCESSIBLE')."</font>";
	            	}
					elseif($fieldname == '') {
					//crmv@9433 end		crmv@sdk-18508 e
                        $table_name = '';
                        $column_name = '';
                        foreach($tableinfo as $tablename=>$colname)
                        {
                            $table_name=$tablename;
                            $column_name = $colname;
                        }
                        $value = $adb->query_result($list_result,$i-1,$colname);
                    }
                    else
                    {
                    	//crmv@7216+7217
                        if(($module == 'Calls' || $module == 'Tasks' || $module == 'Meetings' || $module == 'Emails' || $module == 'Fax' || $module == 'Sms') && (($name=='Related to') || ($name=='Contact Name')))
                      //crmv@7216e
                        {
                            if ($name=='Related to')
                                $value=getRelatedTo($module,$list_result,$i-1);
                            if($name=='Contact Name')
                            {
                                $contact_id = $adb->query_result($list_result,$i-1,"contactid");
                                $contact_name = getFullNameFromQResult($list_result,$i-1,"Contacts");
                                $value="";
                                if(($contact_name != "") && ($contact_id !='NULL'))
                                    $value =  "<a href='javascript=void(0);' onclick=\"parent.document.location.href='index.php?module=Contacts&action=DetailView&record=".$contact_id."'\">".$contact_name."</a>";//crmv@21048m
                            }
                        }
                        elseif(($module == 'Faq' || $module == 'Documents') && $name=='Related to')
                        {
                            $value=getRelatedToEntity($module,$list_result,$i-1);
                        }
                        elseif($name=='Account Name' && ($module == 'Potentials' || $module == 'SalesOrder' || $module == 'Quotes'))
                        {
                            $account_id = $adb->query_result($list_result,$i-1,"accountid");
                            $account_name = getAccountName($account_id);
                            $value = textlength_check($account_name);
                        }
                        elseif($name=='Quote Name' && $module == 'SalesOrder')
                        {
                            $quote_id = $adb->query_result($list_result,$i-1,"quoteid");
                            $quotename = getQuoteName($quote_id);
                            $value = textlength_check($quotename);
                        }
                        elseif($name == 'Account Name' && $module=='Contacts' )
                        {
                            $account_id = $adb->query_result($list_result,$i-1,"accountid");
                            $account_name = getAccountName($account_id);
                            $value = textlength_check($account_name);
                        }
                    	elseif(($module == 'Quotes' && $name == 'Potential Name') || ($module == 'SalesOrder' && $name == 'Potential Name')) {
							$potential_id = $adb->query_result($list_result,$i-1,"potentialid");
							$potential_name = getPotentialName($potential_id);
														$value = '<a href=\'javascript=void(0);\' onclick=\'parent.document.location.href="index.php?module=Potentials&action=DetailView&parenttab='.$tabname.'&record='.$potential_id.'"\'>'.textlength_check($potential_name).'</a>';//crmv@21048m
                    	}
                    	//crmv@sdk-24276
                    	elseif (isset($focus->popup_fields) && in_array($fieldname, $focus->popup_fields) && SDK::isPopupReturnFunction($_REQUEST['srcmodule'],$_REQUEST['forfield'])) {
                    		$sdk_file = SDK::getPopupReturnFunctionFile($_REQUEST['srcmodule'],$_REQUEST['forfield']);
							if ($sdk_file != '' && Vtiger_Utils::checkFileAccess($sdk_file)) {
								include($sdk_file);
							}
                    	}
                    	//crmv@sdk-24276 e
                    	//crmv@19387
                   		elseif(isset($focus->popup_fields) && in_array($fieldname, $focus->popup_fields) && $module == 'Services' && $_REQUEST['srcmodule'] == 'ServiceContracts') {
							global $default_charset;
							$forfield = htmlspecialchars($_REQUEST['forfield'], ENT_QUOTES, $default_charset);
							$list_result_count = $i-1;
							$value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"search",$focus->popup_type);
							if(isset($forfield) && $forfield != '' && $focus->popup_type != 'detailview') {
								$value1 = strip_tags($value);
								$value = htmlspecialchars(addslashes(html_entity_decode(strip_tags($value), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset); // Remove any previous html conversion
								$res = $adb->query("select service_usageunit as tracking_unit, qty_per_unit as total_units from ".$table_prefix."_service where serviceid = $entity_id");
								$tracking_unit = $adb->query_result($res,0,'tracking_unit');
								$total_units = $adb->query_result($res,0,'total_units');
								//crmv@29190
								global $autocomplete_return_function;
								$autocomplete_return_function[$entity_id] = "set_service_in_servicecontracts($entity_id, \"$value\", \"$forfield\", \"$tracking_unit\", \"$total_units\");";
								$value = "<a href='javascript:void(0);' onclick='{$autocomplete_return_function[$entity_id]}closePopup();'>$value1</a>"; //crmv@21048m
								//crmv@29190e
							}
                   		}
                   		//crmv@19387e
						// vtlib customization: Generic popup handling
						elseif(isset($focus->popup_fields) && in_array($fieldname, $focus->popup_fields)) {
							global $default_charset;
							$forfield = htmlspecialchars($_REQUEST['forfield'], ENT_QUOTES, $default_charset);
							$list_result_count = $i-1;
							$value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"search",$focus->popup_type);
							if(isset($forfield) && $forfield != '' && $focus->popup_type != 'detailview') {
								//crmv@16312
								$value1 = strip_tags($value);
								$value = htmlspecialchars(addslashes(html_entity_decode(strip_tags($value), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset); // Remove any previous html conversion
								//crmv@29190
								global $autocomplete_return_function;
								$autocomplete_return_function[$entity_id] = "vtlib_setvalue_from_popup($entity_id, \"$value\", \"$forfield\");";
								$value = "<a href='javascript:void(0);' onclick='{$autocomplete_return_function[$entity_id]}closePopup();'>$value1</a>"; //crmv@21048m
								//crmv@16312 end
								//crmv@29190e
							}
						}
						// END
						else
						{
							$list_result_count = $i-1;
							$value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"search",$focus->popup_type, $form);
						}

					}
					$list_header[]=$value;
				}
			}

			if($module=='Products' && ($focus->popup_type=='inventory_prod' || $focus->popup_type=='inventory_prod_po'))
			{
					global $default_charset;
					require('user_privileges/user_privileges_'.$current_user->id.'.php');
					$row_id = $_REQUEST['curr_row'];

					//To get all the tax types and values and pass it to product details
					$tax_str = '';
					$tax_details = getAllTaxes();
					for($tax_count=0;$tax_count<count($tax_details);$tax_count++)
					{
						$tax_str .= $tax_details[$tax_count]['taxname'].'='.$tax_details[$tax_count]['percentage'].',';
					}
					$tax_str = trim($tax_str,',');
					$rate = $user_info['conv_rate'];
					if(getFieldVisibilityPermission($module,$current_user->id,'unit_price') == '0') {
						$unitprice=$adb->query_result($list_result,$list_result_count,'unit_price');
						if($_REQUEST['currencyid'] != null) {
							$prod_prices = getPricesForProducts($_REQUEST['currencyid'], array($entity_id));
							$unitprice = $prod_prices[$entity_id];
						}
					} else {
						$unit_price = '';
					}
					$sub_products = '';
					$sub_prod = '';
					$sub_prod_query = $adb->pquery("SELECT ".$table_prefix."_products.productid,".$table_prefix."_products.productname from ".$table_prefix."_products INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_products.productid INNER JOIN ".$table_prefix."_seproductsrel on ".$table_prefix."_seproductsrel.crmid=".$table_prefix."_products.productid WHERE ".$table_prefix."_seproductsrel.productid=? and ".$table_prefix."_seproductsrel.setype='Products'",array($entity_id));
					for($k=0;$k<$adb->num_rows($sub_prod_query);$k++){
						//$sub_prod=array();
						$id = $adb->query_result($sub_prod_query,$k,"productid");
						$str_sep='';
						if($k>0) $str_sep = ":";
						$sub_products .= $str_sep.$id;
						$sub_prod .= $str_sep." - ".$adb->query_result($sub_prod_query,$k,"productname");
					}

					$sub_det = $sub_products."::".str_replace(":","<br>",$sub_prod);
					$qty_stock=$adb->query_result($list_result,$list_result_count,'qtyinstock');

					$slashes_temp_val = popup_from_html(getProductName($entity_id));
					$slashes_temp_val = htmlspecialchars($slashes_temp_val,ENT_QUOTES,$default_charset);
					$description=$adb->query_result($list_result,$list_result_count,'description');
					$slashes_desc = htmlspecialchars($description,ENT_QUOTES,$default_charset);

					$sub_products_link = '<a href=\'index.php?module=Products&action=Popup&html=Popup_picker&return_module='.vtlib_purify($_REQUEST['return_module']).'&record_id='.vtlib_purify($entity_id).'&form=HelpDeskEditView&select=enable&popuptype='.$focus->popup_type.'&curr_row='.vtlib_purify($row_id).'&currencyid='.vtlib_purify($_REQUEST['currencyid']).'"\' > '.getTranslatedString('LBL_SUB_PRODUCTS',$module).'</a>'; //crmv@21048m	//crmv@25897

					if(!isset($_REQUEST['record_id'])){
						$sub_products_query = $adb->pquery("SELECT * from ".$table_prefix."_seproductsrel WHERE productid=? AND setype='Products'",array($entity_id));
						if($adb->num_rows($sub_products_query)>0)
							$list_header[]=$sub_products_link;
						else
							$list_header[]= $app_strings['LBL_NO_SUB_PRODUCTS'];
					}
			}

			if($module=='Services' && $focus->popup_type=='inventory_service')
			{
					global $default_charset;
					require('user_privileges/user_privileges_'.$current_user->id.'.php');
					$row_id = $_REQUEST['curr_row'];

					//To get all the tax types and values and pass it to product details
					$tax_str = '';
					$tax_details = getAllTaxes();
					for($tax_count=0;$tax_count<count($tax_details);$tax_count++)
					{
						$tax_str .= $tax_details[$tax_count]['taxname'].'='.$tax_details[$tax_count]['percentage'].',';
					}
					$tax_str = trim($tax_str,',');
					$rate = $user_info['conv_rate'];
					if(getFieldVisibilityPermission($module,$current_user->id,'unit_price') == '0') {
						$unitprice=$adb->query_result($list_result,$list_result_count,'unit_price');
						if($_REQUEST['currencyid'] != null) {
							$prod_prices = getPricesForProducts($_REQUEST['currencyid'], array($entity_id), $module);
							$unitprice = $prod_prices[$entity_id];
						}
					} else {
						$unit_price = '';
					}

					$slashes_temp_val = popup_from_html($adb->query_result($list_result,$list_result_count,'servicename'));
					$slashes_temp_val = htmlspecialchars($slashes_temp_val,ENT_QUOTES,$default_charset);
					$description=$adb->query_result($list_result,$list_result_count,'description');
					$slashes_desc = htmlspecialchars($description,ENT_QUOTES,$default_charset);

			}
            //crmv@7230 / crmv@10445
			if($used_status_field != "") {
				$excolor=getEntityColor($tabid,getEntityStatus($tabid,$module,$used_status_field,$entity_id));
				$color = color_blend_by_opacity($excolor,50);
				$list_header['clv_color'] = $color;
			}
			//crmv@7230e / crmv@10445e
            $list_block[$entity_id]=$list_header;
        }
    }
    $log->debug("Exiting getSearchListViewEntries method ...");
    return $list_block;
}


/**This function generates the value for a given vtiger_field namee
*Param $field_result - vtiger_field result in array
*Param $list_result - resultset of a listview query
*Param $fieldname - vtiger_field name
*Param $focus - module object
*Param $module - module name
*Param $entity_id - entity id
*Param $list_result_count - list result count
*Param $mode - mode type
*Param $popuptype - popup type
*Param $returnset - list query parameters in url string
*Param $viewid - custom view id
*Returns an string value
*/


function getValue($field_result, $list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,$mode,$popuptype,$returnset='',$viewid='')
{
    global $log, $listview_max_textlength, $app_strings, $current_language,$current_user;
    $log->debug("Entering getValue(".$field_result.",". $list_result.",".$fieldname.",".(is_object($focus)? get_class($focus) : $focus).",".$module.",".$entity_id.",".$list_result_count.",".$mode.",".$popuptype.",".$returnset.",".$viewid.") method ..."); //crmv@31429
    global $adb, $current_user, $default_charset, $table_prefix;
    require('user_privileges/user_privileges_'.$current_user->id.'.php');
    $tabname = getParentTab();
    $tabid = getTabid($module);
    $current_module_strings = return_module_language($current_language, $module);
    $uicolarr=$field_result[$fieldname];
    foreach($uicolarr as $key=>$value)
    {
        $uitype = $key;
        $colname = $value;
    }
    //added for getting event status in Custom view - Jaguar
    //crmv@18228
    if($module == 'Calendar' && ($colname == "status" || $colname == "eventstatus" || $colname == 'activitystatus'))
    {
        $colname="activitystatus";
    }
    //crmv@18228 end

    //Ends
	//mycrmv@rotho
	$field_val = $adb->query_result($list_result,$list_result_count,$colname);
	//mycrmv@rotho e
	if(stristr(html_entity_decode($field_val), "<a href") === false && $uitype != 8){
		$temp_val = textlength_check($field_val);
	}elseif($uitype != 8){
		$temp_val = html_entity_decode($field_val,ENT_QUOTES);
	}else{
		$temp_val = $field_val;
	}

	//crmv@sdk-18509
	if(SDK::isUitype($uitype))
	{
		$sdk_file = SDK::getUitypeFile('php','relatedlist',$uitype);
		$sdk_value = $temp_val;
		if ($sdk_file != '') {
			include($sdk_file);
		}
	}
	//crmv@sdk-18509 e
	// vtlib customization: New uitype to handle relation between modules
	elseif($uitype == '10'){
		$parent_id = $field_val;
		if(!empty($parent_id)) {
			$parent_module = getSalesEntityType($parent_id);
			$valueTitle=$parent_module;
			if($app_strings[$valueTitle]) $valueTitle = $app_strings[$valueTitle];

			$displayValueArray = getEntityName($parent_module, $parent_id);
			if(!empty($displayValueArray)){
				foreach($displayValueArray as $key=>$value){
					$displayValue = $value;
				}
			}
			$value = "<a href='index.php?module=$parent_module&action=DetailView&record=$parent_id' title='$valueTitle'>$displayValue</a>";
		} else {
			$value = '';
		}
	} // END
	//crmv@21092	//crmv@23734
	elseif (in_array($uitype,array(19,20,21,24))) {
		$tmp_val = preg_replace("/(<\/?)(\w+)([^>]*>)/i","",$field_val);
		$tmp_val = trim(html_entity_decode($tmp_val, ENT_QUOTES, $default_charset));
		$value = $temp_val;
		if ($field_val != '' && strlen($tmp_val) > $listview_max_textlength) {
			$value .= '&nbsp;<a href="javascript:;"><img onmouseout="getObj(\'content_'.$fieldname.'_'.$entity_id.'\').hide();" onmouseover="getObj(\'content_'.$fieldname.'_'.$entity_id.'\').show();" src="themes/softed/images/readmore.png"></a>';
			$value .= '<div id="content_'.$fieldname.'_'.$entity_id.'" class="layerPopup" style="width:300px;z-index:10000001;display:none;position:absolute;" onmouseout="getObj(\'content_'.$fieldname.'_'.$entity_id.'\').hide();" onmouseover="getObj(\'content_'.$fieldname.'_'.$entity_id.'\').show();">
      <table style="background-color:#F2F2F2;" align="center" border="0" cellpadding="5" cellspacing="0" width="100%">
      <tr><td class="small">'.$tmp_val.'</td></tr>
      </table></div>';
		}
	}
	//crmv@21092e	//crmv@23734e
    elseif($uitype == 53)
    {
        $value = textlength_check($adb->query_result($list_result,$list_result_count,'user_name'));
    	// When Assigned To field is used in Popup window
		if($value == '' ) {
			$user_id = $adb->query_result($list_result,$list_result_count,'smownerid');
			if ($user_id != null && $user_id != '') {
				$value = getOwnerName($user_id);
			}
		}
    }
	//mycrmv@rotho
	elseif($uitype == 77)
    {    
    	$user_id = $adb->query_result($list_result,$list_result_count,$fieldname);
		if ($user_id != null && $user_id != '') {
				$value = getUserName($user_id);
		}
    }
	// danzi.tn@20140220 aggiunto uitype 1077 con default vuoto
	elseif($uitype == 1077)
    {    
    	$user_id = $adb->query_result($list_result,$list_result_count,$fieldname);
		if ($user_id != null && $user_id != '') {
				$value = getUserName($user_id);
		}
    }
	// danzi.tn@20140220 aggiunto uitype 1077 con default vuoto end
    elseif($uitype == 52)
    {
        $value = getUserName($adb->query_result($list_result,$list_result_count,'handler'));
    }
	elseif($uitype == 51)//Accounts - Member Of
	{
		//crmv@23864
		$parentid = $adb->query_result($list_result,$list_result_count,$colname);
		$parent_module = getSalesEntityType($parentid);
		$displayValueArray = getEntityName($parent_module, $parentid);
		if (!empty($displayValueArray)) {
			foreach($displayValueArray as $key=>$value){
				$entity_name = textlength_check($value);
			}
		}
		//crmv@23864e
		$value = '<a href="index.php?module='.$parent_module.'&action=DetailView&record='.$parentid.'&parenttab='.$tabname.'" style="'.$P_FONT_COLOR.'">'.$entity_name.'</a>';

	}
	// ds@8 to show accountname instead of accountid
  	elseif($uitype == 73)
    {
    	//crmv@22814
    	$parentid = $adb->query_result($list_result,$list_result_count,$colname);
    	$value = '<a href="index.php?module=Accounts&action=DetailView&record='.$parentid.'">'.getAccountName($parentid).'</a>';
    	//crmv@22814e
  	}
  	elseif($uitype == 101 )
    {
        //$value = getUserFullName($temp_val);
        $value = getUserName($temp_val);
    }
	// ds@8e
    elseif($uitype == 77)
    {
        $value = getUserName($adb->query_result($list_result,$list_result_count,'inventorymanager'));
    }
    //crmv@18338
    elseif($uitype == 5 || $uitype == 6 || $uitype == 23 || $uitype == 70 || $uitype == 1021)
    //crmv@18338 end
    {
        if($temp_val != '' && $temp_val != '0000-00-00')
        {
        	if ($uitype == 5 || $uitype == 6 || $uitype == 23)
        		$temp_val = substr($temp_val,0,10);
            $value = getDisplayDate($temp_val);
        }
        elseif($temp_val == '0000-00-00')
        {
            $value = '';
        }
        else
        {
            $value = $temp_val;
        }
        $value = adjustTimezone($value, $current_user->timezonediff); // crmv@25610-timezone
    }
    elseif(($uitype == 15 && $fieldname != 'consulenzaname') || $uitype == 111 ||  $uitype == 16 || ($uitype == 55 && $fieldname =="salutationtype"))
	{
		global $current_user,$adb;
		$roleid=$current_user->roleid;
		$values_arr = getAssignedPicklistValues($fieldname, $roleid, $adb,$module);
		//crmv@18228
		if ($module == 'Calendar' && $colname == 'activitystatus'){
			$values_arr = array_merge($values_arr,getAssignedPicklistValues('eventstatus', $roleid, $adb,$module));
		}
		//crmv@18228 end
		$value = $adb->query_result($list_result,$list_result_count,$colname);
		$value_decoded = decode_html($value);
		$pickcount = count($values_arr);
		//crmv@fix activitytype
		if (!($module == 'Calendar' && $fieldname == 'activitytype' && $value == 'Task')){
			if ($pickcount > 0){
				if (!in_array($value_decoded,array_keys($values_arr)) && $value_decoded != '')
					$value = "<font color='red'>".$app_strings['LBL_NOT_ACCESSIBLE']."</font>";
				else
					$value = textlength_check($values_arr[$value_decoded]);
			}
			elseif($pickcount == 0 && count($value))
			{
				$value = "<font color='red'>".$app_strings['LBL_NOT_ACCESSIBLE']."</font>";
			}
		}
		else{
			$value = getTranslatedString($value,$module);
		}
		//crmv@fix activitytype end
	}
    //crmv@8982
    elseif($uitype == 1015 && ($_REQUEST['action'] != 'Popup')) //mycrmv@42218 
    {
        $temp_val = decode_html($adb->query_result($list_result,$list_result_count,$colname));
        $value = textlength_check(to_html(PickListMulti::getTranslatedPicklist($temp_val,$colname)));
    }
    //crmv@8982e
    elseif($uitype == 71 || $uitype == 72)
    {
    	if($temp_val != '')
		{
			if($fieldname == 'unit_price') {
				$currency_id = getProductBaseCurrency($entity_id,$module);
				$cursym_convrate = getCurrencySymbolandCRate($currency_id);
				$value = "<font style='color:grey;'>".$cursym_convrate['symbol']."</font> ". $temp_val;
			} else {
				$rate = $user_info['conv_rate'];
				//changes made to remove vtiger_currency symbol infront of each vtiger_potential amount
        		if ($temp_val != 0) $value = convertFromDollar($temp_val,$rate);
        		else $value = $temp_val;
			}
		}
		else
		{
			$value = '';
		}

    }
    elseif($uitype == 17)
    {
        $value = '<a href="http://'.$field_val.'" target="_blank">'.$temp_val.'</a>';
    }
//crmv@7216
    elseif($uitype == 1013  && ($_REQUEST['action'] != 'Popup' && $_REQUEST['file'] != 'Popup'))
    {
        if(isPermitted("Fax","EditView") == 'yes')
        {
            //check added for fax link in user detailview
            $querystr="SELECT fieldid FROM ".$table_prefix."_field WHERE tabid=? and fieldname=?";
            $queryres = $adb->pquery($querystr, array(getTabid($module), $fieldname));
            $fieldid = $adb->query_result($queryres,0,'fieldid');
            $value = '<a href="javascript:InternalFax('.$entity_id.','.$fieldid.',\''.$fieldname.'\',\''.$module.'\',\'record_id\');">'.$temp_val.'</a>';
        }
        else
            $value = $temp_val;

        }
//crmv@7216e
    elseif($uitype == 13 || $uitype == 104 && ($_REQUEST['action'] != 'Popup' && $_REQUEST['file'] != 'Popup'))
    {
        if($_SESSION['internal_mailer'] == 1)
        {
            //check added for email link in user detailview
            $querystr="SELECT fieldid FROM ".$table_prefix."_field WHERE tabid=? and fieldname=?";
            $queryres = $adb->pquery($querystr, array(getTabid($module), $fieldname));
            //Change this index 0 - to get the vtiger_fieldid based on email1 or email2
            $fieldid = $adb->query_result($queryres,0,'fieldid');
            $value = '<a href="javascript:InternalMailer('.$entity_id.','.$fieldid.',\''.$fieldname.'\',\''.$module.'\',\'record_id\');">'.$temp_val.'</a>';
        }
        else
            $value = '<a href="mailto:'.$field_val.'">'.$temp_val.'</a>';

        }
    elseif($uitype == 56)
    {
        if($temp_val == 1)
        {
            $value = $app_strings['yes'];
        }
        else
        {
            $value = $app_strings['no'];
        }
    }
    elseif($uitype == 57)
    {
        if($temp_val != '')
                {
            $sql="SELECT * FROM ".$table_prefix."_contactdetails WHERE contactid=?";
            $result=$adb->pquery($sql, array($temp_val));
            $name=getFullNameFromQResult($result,0,"Contacts");

            $value= '<a href=index.php?module=Contacts&action=DetailView&record='.$temp_val.'>'.$name.'</a>';
        }
        else
            $value='';
    }
    //Added by Minnie to get Campaign Source
    elseif($uitype == 58)
    {
        if($temp_val != '')
        {
            $sql="SELECT * FROM ".$table_prefix."_campaign WHERE campaignid=?";
            $result=$adb->pquery($sql, array($temp_val));
            $campaignname=$adb->query_result($result,0,"campaignname");
            $value= '<a href=index.php?module=Campaigns&action=DetailView&record='.$temp_val.'>'.$campaignname.'</a>';
        }
        else
            $value='';
    }
    //End
    //Added By *Raj* for the Issue ProductName not displayed in CustomView of HelpDesk
    elseif($uitype == 59)
    {
        if($temp_val != '')
        {
            $value = getProductName($temp_val);
        }
        else
        {
            $value = '';
        }
    }
    //End
    elseif($uitype == 61)
    {
    	//crmv@7216
		if ($module == 'Fax') {
			$res_fax=$adb->pquery("SELECT * FROM ".$table_prefix."_seattachmentsrel WHERE crmid = ?",array($entity_id));
			$n_fax=$adb->num_rows($res_fax);
			for ($i=0;$i<$n_fax;$i++){
				$attachmentid[]=	$adb->query_result($res_fax,$i,'attachmentsid');
			}
			$value="";
			$cnt_fax=count($attachmentid);
			$cnt2_fax=0;
			if ($cnt_fax>0){
				foreach ($attachmentid as $att){
				$attachmentname=$adb->query_result($adb->pquery("SELECT name FROM ".$table_prefix."_attachments WHERE attachmentsid = ?",array($att)),0,'name');
				$value .='<a href = "index.php?module=uploads&action=downloadfile&return_module='.$module.'&fileid='.$att.'&filename='.$attachmentname.'">'.$attachmentname.'</a>';
								$cnt2_fax++;
				if ($cnt2_fax < $cnt_fax ) $value.=", ";
				}
			}
		}
		//crmv@7216e
		else {
        	$attachmentid=$adb->query_result($adb->pquery("SELECT * FROM ".$table_prefix."_seattachmentsrel WHERE crmid = ?", array($entity_id)),0,'attachmentsid');
        	$value = '<a href = "index.php?module=uploads&action=downloadfile&return_module='.$module.'&fileid='.$attachmentid.'&filename='.$temp_val.'">'.$temp_val.'</a>';
		}
    }
    elseif($uitype == 62)
    {
        $parentid = $adb->query_result($list_result,$list_result_count,"parent_id");
        $parenttype = $adb->query_result($list_result,$list_result_count,"parent_type");

        if($parenttype == "Leads")
        {
            $tablename = $table_prefix."_leaddetails";    $fieldname = "lastname";    $idname="leadid";
        }
        if($parenttype == "Accounts")
        {
            $tablename = $table_prefix."_account";        $fieldname = "accountname";     $idname="accountid";
        }
        if($parenttype == "Products")
        {
            $tablename = $table_prefix."_products";    $fieldname = "productname";     $idname="productid";
        }
        if($parenttype == "HelpDesk")
        {
            $tablename = $table_prefix."_troubletickets";    $fieldname = "title";            $idname="ticketid";
        }
        if($parenttype == "Invoice")
        {
            $tablename = $table_prefix."_invoice";    $fieldname = "subject";     $idname="invoiceid";
        }


        if($parentid != '')
                {
            $sql="SELECT * FROM $tablename WHERE $idname = ?";
            $fieldvalue=$adb->query_result($adb->pquery($sql, array($parentid)),0,$fieldname);

            $value='<a href=index.php?module='.$parenttype.'&action=DetailView&record='.$parentid.'&parenttab='.$tabname.'>'.$fieldvalue.'</a>';
        }
        else
            $value='';
    }
	elseif($uitype == 66)
    {
    	//crmv@23515
        $parent_id = $adb->query_result($list_result,$list_result_count,"parent_id");
        $parent_module = getSalesEntityType($parent_id);
        if($parent_id != '')
		{
			$relatedto = getCalendarRelatedToModules();
			if (in_array($parent_module,$relatedto)) {
				$focus = CRMEntity::getInstance($parent_module);
				$entitynamefields = vtws_getEntityNameFields($parent_module);
				$parent_name = array();
				foreach($entitynamefields as $entitynamefield) {
					$focus->retrieve_entity_info($parent_id,$parent_module);
					$parent_name[] = $focus->column_fields[$entitynamefield];
				}
				$parent_name = implode(' ',$parent_name);
				$value = '<a href="index.php?module='.$parent_module.'&action=DetailView&record='.$parent_id.'">'.$parent_name.'</a>';
			}
		} else {
			$value = '';
		}
		//crmv@23515e
    }
    elseif($uitype == 67)
    {
        $parentid = $adb->query_result($list_result,$list_result_count,"parent_id");
        $parenttype = $adb->query_result($list_result,$list_result_count,"parent_type");

        if($parenttype == "Leads")
        {
            $tablename = $table_prefix."_leaddetails";    $fieldname = "lastname";    $idname="leadid";
        }
        if($parenttype == "Contacts")
        {
            $tablename = $table_prefix."_contactdetails";        $fieldname = "contactname";     $idname="contactid";
        }
        if($parentid != '')
                {
            $sql="SELECT * FROM $tablename WHERE $idname = ?";
            $fieldvalue=$adb->query_result($adb->pquery($sql, array($parentid)),0,$fieldname);

            $value='<a href=index.php?module='.$parenttype.'&action=DetailView&record='.$parentid.'&parenttab='.$tabname.'>'.$fieldvalue.'</a>';
        }
        else
            $value='';
    }
    elseif($uitype == 68)
    {
        $parentid = $adb->query_result($list_result,$list_result_count,"parent_id");
        $parenttype = $adb->query_result($list_result,$list_result_count,"parent_type");

        if($parenttype == '' && $parentid != '')
                        $parenttype = getSalesEntityType($parentid);

        if($parenttype == "Contacts")
        {
            $tablename = $table_prefix."_contactdetails";        $fieldname = "lastname";    $idname="contactid";	//crmv@16373
        }
        if($parenttype == "Accounts")
        {
            $tablename = $table_prefix."_account";    $fieldname = "accountname";    $idname="accountid";
        }
        if($parentid != '' && $parentid != 0 && $parenttype!= '')	//crmv@18170	//crmv@25047
		{
            $sql="SELECT * FROM $tablename WHERE $idname = ?";
            //crmv@16373
            $res = $adb->pquery($sql, array($parentid));
            if ($res) {
            	$fieldvalue=$adb->query_result($res,0,$fieldname);
	            $value='<a href="javascript:void(0);" onclick="parent.document.location.href=\'index.php?module='.$parenttype.'&action=DetailView&record='.$parentid.'&parenttab='.$tabname.'\'">'.$fieldvalue.'</a>';//crmv@21048m
            }
            else
            	$value = '';
            //crmv@16373e
        }
        else
            $value='';
    }
    elseif($uitype == 78)
        {
        if($temp_val != '')
                {

                        $quote_name = getQuoteName($temp_val);
            $value= '<a href=index.php?module=Quotes&action=DetailView&record='.$temp_val.'&parenttab='.$tabname.'>'.textlength_check($quote_name).'</a>';
        }
        else
            $value='';
        }
    elseif($uitype == 79)
        {
        if($temp_val != '')
                {

                        $purchaseorder_name = getPoName($temp_val);
            $value= '<a href=index.php?module=PurchaseOrder&action=DetailView&record='.$temp_val.'&parenttab='.$tabname.'>'.textlength_check($purchaseorder_name).'</a>';
        }
        else
            $value='';
        }
    elseif($uitype == 80)
        {
        if($temp_val != '')
                {

                        $salesorder_name = getSoName($temp_val);
            $value= '<a href=index.php?module=SalesOrder&action=DetailView&record='.$temp_val.'&parenttab='.$tabname.'>'.textlength_check($salesorder_name).'</a>';
        }
        else
            $value='';
        }
    elseif($uitype == 75 || $uitype == 81)
        {

        if($temp_val != '')
                {

                        $vendor_name = getVendorName($temp_val);
            $value= '<a href=index.php?module=Vendors&action=DetailView&record='.$temp_val.'&parenttab='.$tabname.'>'.textlength_check($vendor_name).'</a>';
        }
        else
            $value='';
        }
    elseif($uitype == 98)
    {
        $value = '<a href="index.php?action=RoleDetailView&module=Settings&parenttab=Settings&roleid='.$temp_val.'">'.textlength_check(getRoleName($temp_val)).'</a>';
    }
    //crmv@7220
    //crmv@17471
	elseif(($uitype == 11 || $uitype == 1014) && get_use_asterisk($current_user->id) == 'true') {
		$value = "<a href='javascript:;' onclick='startCall(&quot;$temp_val&quot;, &quot;$entity_id&quot;)'>".$temp_val."</a>";
	}
	//crmv@17471 end
    elseif($uitype == 33)
    {
        $value = ($temp_val != "") ? str_ireplace(' |##| ',', ',$temp_val) : "";
        if(!$is_admin && $value != '')
        {
            $value = ($field_val != "") ? str_ireplace(' |##| ',', ',$field_val) : "";
            if($value != '')
            {
                $value_arr=explode(',',trim($value));
                $roleid=$current_user->roleid;
                $subrole = getRoleSubordinates($roleid);
                if(count($subrole)> 0)
                {
                    $roleids = $subrole;
                    array_push($roleids, $roleid);
                }
                else
                {
                    $roleids = $roleid;
                }
            	//crmv@19371
            	//se la picklist supporta il nuovo metodo
				if (in_array('picklist_valueid',$adb->database->MetaColumnNames($table_prefix."_$fieldname")) && $fieldname != 'product_lines'){
					$order_by = "sortid,$fieldname";
					$pick_query="select $fieldname from ".$table_prefix."_$fieldname where exists (select * from ".$table_prefix."_role2picklist where ".$table_prefix."_role2picklist.picklistvalueid = ".$table_prefix."_$fieldname.picklist_valueid and roleid in (". generateQuestionMarks($roleids) .")";
					$pick_query.=" order by $order_by asc)";
					$params = array($roleids);
				}
				//altrimenti uso il vecchio
				else {
					if (in_array('sortorderid',$adb->database->MetaColumnNames($table_prefix."_$fieldname")))
						$order_by = "sortorderid,$fieldname";
					else
						$order_by = $fieldname;
					$pick_query="select $fieldname from ".$table_prefix."_$fieldname";
					if ($fieldname == 'product_lines')
						$pick_query .= ' where presence = 1';
					//vtc e
					$pick_query.=" order by $order_by asc";
					$params = array();
				}
				//crmv@19371e
                $pickListResult = $adb->pquery($pick_query, $params);
                $picklistval = Array();
                for($i=0;$i<$adb->num_rows($pickListResult);$i++)
                {
                    $picklistarr[]=$adb->query_result($pickListResult,$i,$fieldname);
                }
                $value_temp = Array();
                $string_temp = '';
                $str_c = 0;
                foreach($value_arr as $ind => $val)
                {
                    $notaccess = '<font color="red">'.$app_strings['LBL_NOT_ACCESSIBLE']."</font>";
                    if(!(strlen(preg_replace("/(<\/?)(\w+)([^>]*>)/i","",$string_temp)) > $listview_max_textlength))
                    {
                        $value_temp1 = (in_array(trim($val),$picklistarr))?$val:$notaccess;
                        if($str_c!=0)
                            $string_temp .= ' , ';
                        $string_temp .= $value_temp1;
                        $str_c++;
                    }
                    else
                        $string_temp .='...';

                }
                $value=$string_temp;
            }
        }
    }
    elseif($uitype == 85)
    {
        $value = ($temp_val != "") ? "<a href='skype:{$temp_val}?call'>{$temp_val}</a>" : "";
    }
	elseif($uitype == 116)
	{
		$value = ($temp_val != "") ? getCurrencyName($temp_val) : "";
	}
	elseif($uitype == 117)
	{
		// NOTE: Without symbol the value could be used for filtering/lookup hence avoiding the translation
		$value = ($temp_val != "") ? getCurrencyName($temp_val,false) : "";
	}
    //vtc
	elseif($uitype == 26){
		// crmv@30967
		$sql ="select foldername from ".$table_prefix."_crmentityfolder where tabid = ? and folderid = ?";
		$res = $adb->pquery($sql,array(getTabId($module), $temp_val));
		// crmv@30967e
		$foldername = $adb->query_result($res,0,'foldername');
		$value = $foldername;
	}
    //vtc e
	//Added for email status tracking
	//crmv@26639
	elseif($uitype == 25)
	{
		$entityid = $_REQUEST['record'];
		$emailid = $adb->query_result($list_result,$list_result_count,"crmid");
		$result = $adb->pquery("SELECT access_count FROM ".$table_prefix."_email_track WHERE crmid=? AND mailid=?", array($entityid,$emailid));
		$access_count = '';
		if ($result && $adb->num_rows($result) > 0) {
			$access_count = $adb->query_result($result,0,"access_count");
		}
		$result1 = $adb->pquery("SELECT send_mode FROM ".$table_prefix."_emaildetails WHERE emailid = ?", array($emailid));
		if ($result1 && $adb->num_rows($result1) > 0) {
			$send_mode = $adb->query_result($result1,0,"send_mode");
		}
		$value = $access_count;
		if (!$value) {
			if ($send_mode == 'multiple') {
				$value = 0;
			} else {
				$value = '-';
			}
		}
	}
	//crmv@26639e
	//asterisk changes end here
	elseif($uitype == 8){
		if(!empty($temp_val)){
			$temp_val = html_entity_decode($temp_val,ENT_QUOTES,$default_charset);
			$json = new Zend_Json();
			$value = vt_suppressHTMLTags(implode(',',$json->decode($temp_val)));
		}
	}
	//crmv@18338
	elseif($uitype == 1020){
		$value=time_duration(abs($temp_val));
		if (strpos($fieldname,"remaining")!==false || strpos($fieldname,"_out_")!==false){
			if (strpos($fieldname,"remaining")!==false){
				if ($temp_val<=0)
					$color = "red";
				else
					$color = "green";
			}
			if (strpos($fieldname,"_out_")!==false){
				if ($temp_val>0)
					$color = "red";
				else
					$color = "green";
			}
			$value = "<font color=$color>$value</font>";
		}
	}
	//crmv@18338 end
	//end email status tracking
	else
	{
		if($fieldname == $focus->list_link_field)
		{
			if($mode == "search")
			{
				//crmv@29190
				global $autocomplete_return_function;
				if($popuptype == "specific" || $popuptype=="toDospecific")
				{
					// Added for get the first name of contact in Popup window
					if($colname == "lastname" && $module == 'Contacts')
					{
						$temp_val = getFullNameFromQResult($list_result,$list_result_count,"Contacts");
					}

					$slashes_temp_val = popup_from_html($temp_val);
					$slashes_temp_val = htmlspecialchars($slashes_temp_val,ENT_QUOTES,$default_charset);

					//Added to avoid the error when select SO from Invoice through AjaxEdit
					if($module == 'SalesOrder') {
						$autocomplete_return_function[$entity_id] = 'set_return_specific("'.$entity_id.'", "'.nl2br(decode_html($slashes_temp_val)).'","'.$_REQUEST['form'].'");';
						$value = '<a href="javascript:void(0);" onclick=\''.$autocomplete_return_function[$entity_id].'closePopup();\'>'.$temp_val.'</a>'; //crmv@21048m
					} elseif($module =='Contacts') {
						$cntct_focus = CRMEntity::getInstance('Contacts');
						$cntct_focus->retrieve_entity_info($entity_id,"Contacts");
						$slashes_temp_val = popup_from_html($temp_val);
						//ADDED TO CHECK THE FIELD PERMISSIONS FOR
						$xyz=array('mailingstreet','mailingcity','mailingzip','mailingpobox','mailingcountry','mailingstate','otherstreet','othercity','otherzip','otherpobox','othercountry','otherstate');
						for($i=0;$i<12;$i++){
							if (getFieldVisibilityPermission($module, $current_user->id,$xyz[$i]) == '0'){
								$cntct_focus->column_fields[$xyz[$i]] = $cntct_focus->column_fields[$xyz[$i]];
							}
							else
							$cntct_focus->column_fields[$xyz[$i]] = '';
						}
						// For ToDo creation the underlying form is not named as EditView
						$form = !empty($_REQUEST['form']) ? $_REQUEST['form'] : '';
						if(!empty($form)) $form = htmlspecialchars($form,ENT_QUOTES,$default_charset);
						$mailing_street = str_replace(array("\r","\n"),array('\r','\n'), popup_decode_html($cntct_focus->column_fields['mailingstreet']));
						$other_street = str_replace(array("\r","\n"),array('\r','\n'), popup_decode_html($cntct_focus->column_fields['otherstreet']));
						$autocomplete_return_function[$entity_id] = 'set_return_contact_address("'.$entity_id.'", "'.nl2br(decode_html($slashes_temp_val)).'", "'.$mailing_street.'", "'.$other_street.'", "'.popup_decode_html($cntct_focus->column_fields['mailingcity']).'", "'.popup_decode_html($cntct_focus->column_fields['othercity']).'", "'.popup_decode_html($cntct_focus->column_fields['mailingstate']).'", "'.popup_decode_html($cntct_focus->column_fields['otherstate']).'", "'.popup_decode_html($cntct_focus->column_fields['mailingzip']).'", "'.popup_decode_html($cntct_focus->column_fields['otherzip']).'", "'.popup_decode_html($cntct_focus->column_fields['mailingcountry']).'", "'.popup_decode_html($cntct_focus->column_fields['othercountry']).'","'.popup_decode_html($cntct_focus->column_fields['mailingpobox']).'", "'.popup_decode_html($cntct_focus->column_fields['otherpobox']).'","'.$form.'");';
						$value = '<a href="javascript:void(0);" onclick=\''.$autocomplete_return_function[$entity_id].'closePopup();\'>'.$temp_val.'</a>'; //crmv@21048m
					} elseif($popuptype=='toDospecific') {
						$autocomplete_return_function[$entity_id] = 'set_return_toDospecific("'.$entity_id.'", "'.nl2br(decode_html($slashes_temp_val)).'");';
						$value = '<a href="javascript:void(0);" onclick=\''.$autocomplete_return_function[$entity_id].'closePopup();\'>'.$temp_val.'</a>'; //crmv@21048m
					} else {
						$autocomplete_return_function[$entity_id] = 'set_return_specific("'.$entity_id.'", "'.nl2br(decode_html($slashes_temp_val)).'");';
						$value = '<a href="javascript:void(0);" onclick=\''.$autocomplete_return_function[$entity_id].'closePopup();\'>'.$temp_val.'</a>'; //crmv@21048m
					}
				}
				elseif($popuptype == "detailview")
				{
					if($colname == "lastname" && ($module == 'Contacts' || $module == 'Leads')) {
						$temp_val = getFullNameFromQResult($list_result,$list_result_count,$module);
					}

					$slashes_temp_val = popup_from_html($temp_val);
					$slashes_temp_val = htmlspecialchars($slashes_temp_val,ENT_QUOTES,$default_charset);

					$focus->record_id = $_REQUEST['recordid'];
					if($_REQUEST['return_module'] == "Calendar" && $module == 'Contacts')	//crmv@17001
					{
						$autocomplete_return_function[$entity_id] = 'add_data_to_relatedlist_incal("'.$entity_id.'","'.decode_html($slashes_temp_val).'");';
						$value = '<a href="javascript:void(0);" id="calendarCont'.$entity_id.'" LANGUAGE=javascript onclick=\''.$autocomplete_return_function[$entity_id].'\'>'.$temp_val.'</a>';
					} else {
						$autocomplete_return_function[$entity_id] = 'add_data_to_relatedlist("'.$entity_id.'","'.$focus->record_id.'","'.$module.'");';
						$value = '<a href="javascript:void(0);" onclick=\''.$autocomplete_return_function[$entity_id].'closePopup();\'>'.$temp_val.'</a>'; //crmv@21048m
					}
				}
				elseif($popuptype == "inventory_prod")
				{
					$row_id = $_REQUEST['curr_row'];

					//To get all the tax types and values and pass it to product details
					$tax_str = '';
					$tax_details = getAllTaxes();
					for($tax_count=0;$tax_count<count($tax_details);$tax_count++)
					{
						$tax_str .= $tax_details[$tax_count]['taxname'].'='.$tax_details[$tax_count]['percentage'].',';
					}
					$tax_str = trim($tax_str,',');
					$rate = $user_info['conv_rate'];
					if(getFieldVisibilityPermission('Products',$current_user->id,'unit_price') == '0') {
						$unitprice=$adb->query_result($list_result,$list_result_count,'unit_price');
						if($_REQUEST['currencyid'] != null) {
							$prod_prices = getPricesForProducts($_REQUEST['currencyid'], array($entity_id));
							//crmv@29924
							if(array_key_exists($entity_id,$prod_prices)) {
								$unitprice = $prod_prices[$entity_id];
							}
							//crmv@29924e
						}
					} else {
						$unit_price = '';
					}
					$sub_products = '';
					$sub_prod = '';
					$sub_prod_query = $adb->pquery("SELECT ".$table_prefix."_products.productid,".$table_prefix."_products.productname,".$table_prefix."_products.qtyinstock,".$table_prefix."_crmentity.description from ".$table_prefix."_products INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_products.productid INNER JOIN ".$table_prefix."_seproductsrel on ".$table_prefix."_seproductsrel.crmid=".$table_prefix."_products.productid WHERE ".$table_prefix."_seproductsrel.productid=? and ".$table_prefix."_seproductsrel.setype='Products'",array($entity_id));
					for($i=0;$i<$adb->num_rows($sub_prod_query);$i++){
						//$sub_prod=array();
						$id = $adb->query_result($sub_prod_query,$i,"productid");
						$str_sep='';
						if($i>0) $str_sep = ":";
						$sub_products .= $str_sep.$id;
						$sub_prod .= $str_sep." - ".$adb->query_result($sub_prod_query,$i,"productname");
					}

					$sub_det = $sub_products."::".str_replace(":","<br>",$sub_prod);
					$qty_stock=$adb->query_result($list_result,$list_result_count,'qtyinstock');

					//crmv@16267
					$slashes_temp_val = popup_from_html($field_val);
					$slashes_temp_val = htmlspecialchars($slashes_temp_val,ENT_QUOTES,$default_charset);
					$description = popup_from_html($adb->query_result($list_result,$list_result_count,'description'));
					$slashes_temp_desc = decode_html(nl2br(htmlspecialchars($description,ENT_QUOTES,$default_charset)));
					$slashes_desc = stripslashes($slashes_temp_desc);
					$slashes_desc = str_replace(array("\r","\n"),array('\r','\n'), $slashes_desc);

					$order_code = $adb->query_result($list_result,$list_result_count,'productcode');

					$tmp_arr = array("entityid"=>$entity_id,"prodname"=>"".stripslashes(decode_html(nl2br($slashes_temp_val)))."","unitprice" => "$unitprice", "qtyinstk"=>"$qty_stock","taxstring"=>"$tax_str","rowid"=>"$row_id","desc"=>"".strip_tags($slashes_desc)."","subprod_ids"=>"$sub_det","prod_code"=>"$order_code");
					require_once('include/Zend/Json.php');
					$prod_arr = Zend_Json::encode($tmp_arr);
					$autocomplete_return_function[$entity_id] = 'set_return_inventory("'.$entity_id.'", "'.decode_html(nl2br($slashes_temp_val)).'", "'.$unitprice.'", "'.$qty_stock.'","'.$tax_str.'","'.$row_id.'","'.strip_tags($slashes_desc).'","'.$sub_det.'","'.$order_code.'");';
					$value = '<a href="javascript:void(0);" id=\'popup_product_'.$entity_id.'\' title="'.decode_html(nl2br($slashes_temp_val)).'" onclick=\''.$autocomplete_return_function[$entity_id].'closePopup();\' vt_prod_arr=\''.$prod_arr.'\' >'.$temp_val.'</a>'; //crmv@21048m	//crmv@25989
					//crmv@16267e
				}
				elseif($popuptype == "inventory_prod_po")
				{
					$row_id = $_REQUEST['curr_row'];

					//To get all the tax types and values and pass it to product details
					$tax_str = '';
					$tax_details = getAllTaxes();
					for($tax_count=0;$tax_count<count($tax_details);$tax_count++)
					{
						$tax_str .= $tax_details[$tax_count]['taxname'].'='.$tax_details[$tax_count]['percentage'].',';
					}
					$tax_str = trim($tax_str,',');
					$rate = $user_info['conv_rate'];

					if(getFieldVisibilityPermission($module,$current_user->id,'unit_price') == '0') {
						$unitprice=$adb->query_result($list_result,$list_result_count,'unit_price');
						if($_REQUEST['currencyid'] != null) {
							$prod_prices = getPricesForProducts($_REQUEST['currencyid'], array($entity_id), $module);
							$unitprice = $prod_prices[$entity_id];
						}
					} else {
						$unit_price = '';
					}
					$sub_products = '';
					$sub_prod = '';
					$sub_prod_query = $adb->pquery("SELECT ".$table_prefix."_products.productid,".$table_prefix."_products.productname,".$table_prefix."_products.qtyinstock,".$table_prefix."_crmentity.description from ".$table_prefix."_products INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_products.productid INNER JOIN ".$table_prefix."_seproductsrel on ".$table_prefix."_seproductsrel.crmid=".$table_prefix."_products.productid WHERE ".$table_prefix."_seproductsrel.productid=? and ".$table_prefix."_seproductsrel.setype='Products'",array($entity_id));
					for($i=0;$i<$adb->num_rows($sub_prod_query);$i++){
						//$sub_prod=array();
						$id = $adb->query_result($sub_prod_query,$i,"productid");
						$str_sep='';
						if($i>0) $str_sep = ":";
						$sub_products .= $str_sep.$id;
						$sub_prod .= $str_sep." - $id.".$adb->query_result($sub_prod_query,$i,"productname");
					}

					$sub_det = $sub_products."::".str_replace(":","<br>",$sub_prod);

					//crmv@16267
					$slashes_temp_val = popup_from_html($field_val);
					$slashes_temp_val = htmlspecialchars($slashes_temp_val,ENT_QUOTES,$default_charset);
					$description = popup_from_html($adb->query_result($list_result,$list_result_count,'description'));
					$slashes_temp_desc = decode_html(nl2br(htmlspecialchars($description,ENT_QUOTES,$default_charset)));
					$slashes_desc = stripslashes($slashes_temp_desc);
					$slashes_desc = str_replace(array("\r","\n"),array('\r','\n'), $slashes_desc);

					$order_code = $adb->query_result($list_result,$list_result_count,'productcode');

					$tmp_arr = array("entityid"=>$entity_id,"prodname"=>"".stripslashes(decode_html(nl2br($slashes_temp_val)))."","unitprice" => "$unitprice", "qtyinstk"=>"$qty_stock","taxstring"=>"$tax_str","rowid"=>"$row_id","desc"=>"".strip_tags($slashes_desc)."","subprod_ids"=>"$sub_det","prod_code"=>"$order_code");
					require_once('include/Zend/Json.php');
					$prod_arr = Zend_Json::encode($tmp_arr);
					$autocomplete_return_function[$entity_id] = 'set_return_inventory_po("'.$entity_id.'", "'.decode_html(nl2br($slashes_temp_val)).'", "'.$unitprice.'", "'.$tax_str.'","'.$row_id.'","'.strip_tags($slashes_desc).'","'.$sub_det.'","'.$order_code.'");';
					$value = '<a href="javascript:void(0);" id=\'popup_product_'.$entity_id.'\' onclick=\''.$autocomplete_return_function[$entity_id].'closePopup();\'  vt_prod_arr=\''.$prod_arr.'\' >'.$temp_val.'</a>'; //crmv@21048m
					//crmv@16267e
				}
				elseif($popuptype == "inventory_service")
				{
					$row_id = $_REQUEST['curr_row'];

					//To get all the tax types and values and pass it to product details
					$tax_str = '';
					$tax_details = getAllTaxes();
					for($tax_count=0;$tax_count<count($tax_details);$tax_count++)
					{
						$tax_str .= $tax_details[$tax_count]['taxname'].'='.$tax_details[$tax_count]['percentage'].',';
					}
					$tax_str = trim($tax_str,',');
					$rate = $user_info['conv_rate'];
					if(getFieldVisibilityPermission('Services',$current_user->id,'unit_price') == '0') {
						$unitprice=$adb->query_result($list_result,$list_result_count,'unit_price');
						if($_REQUEST['currencyid'] != null) {
							$prod_prices = getPricesForProducts($_REQUEST['currencyid'], array($entity_id), $module);
							$unitprice = $prod_prices[$entity_id];
						}
					} else {
						$unit_price = '';
					}

					//crmv@16267
					$slashes_temp_val = popup_from_html($field_val);
					$slashes_temp_val = htmlspecialchars($slashes_temp_val,ENT_QUOTES,$default_charset);
					$description = popup_from_html($adb->query_result($list_result,$list_result_count,'description'));
					$slashes_temp_desc = decode_html(nl2br(htmlspecialchars($description,ENT_QUOTES,$default_charset)));
					$slashes_desc = stripslashes($slashes_temp_desc);
					$slashes_desc = str_replace(array("\r","\n"),array('\r','\n'), $slashes_desc);

					$order_code = $adb->query_result($list_result,$list_result_count,'service_no');

					$tmp_arr = array("entityid"=>$entity_id,"prodname"=>"".stripslashes(decode_html(nl2br($slashes_temp_val)))."","unitprice" => "$unitprice","taxstring"=>"$tax_str","rowid"=>"$row_id","desc"=>"".strip_tags($slashes_desc)."","prod_code"=>"$order_code");
					require_once('include/Zend/Json.php');
					$prod_arr = Zend_Json::encode($tmp_arr);
					$autocomplete_return_function[$entity_id] = 'set_return_inventory("'.$entity_id.'", "'.decode_html(nl2br($slashes_temp_val)).'", "'.$unitprice.'", "'.$tax_str.'","'.$row_id.'","'.strip_tags($slashes_desc).'","'.$order_code.'");';
					$value = '<a href="javascript:void(0);" id=\'popup_product_'.$entity_id.'\' onclick=\''.$autocomplete_return_function[$entity_id].'closePopup();\'  vt_prod_arr=\''.$prod_arr.'\' >'.$temp_val.'</a>'; //crmv@21048m
					//crmv@16267e
				}
				elseif($popuptype == "inventory_pb")
				{

					$prod_id = $_REQUEST['productid'];
					$flname =  $_REQUEST['fldname'];
					$listprice=getListPrice($prod_id,$entity_id);

					$temp_val = popup_from_html($temp_val);
					$autocomplete_return_function[$entity_id] = 'set_return_inventory_pb("'.$listprice.'", "'.$flname.'");';
					$value = '<a href="javascript:void(0);" onclick=\''.$autocomplete_return_function[$entity_id].'closePopup();\'>'.$temp_val.'</a>'; //crmv@21048m
				}
				elseif($popuptype == "specific_account_address")
				{
					$acct_focus = CRMEntity::getInstance('Accounts');
					$acct_focus->retrieve_entity_info($entity_id,"Accounts");
					$slashes_temp_val = popup_from_html($temp_val);
					$slashes_temp_val = htmlspecialchars($slashes_temp_val,ENT_QUOTES,$default_charset);
					$xyz=array('bill_street','bill_city','bill_code','bill_pobox','bill_country','bill_state','ship_street','ship_city','ship_code','ship_pobox','ship_country','ship_state');
					for($i=0;$i<12;$i++){
						if (getFieldVisibilityPermission($module, $current_user->id,$xyz[$i]) == '0'){
							$acct_focus->column_fields[$xyz[$i]] = $acct_focus->column_fields[$xyz[$i]];
						} else {
							$acct_focus->column_fields[$xyz[$i]] = '';
						}
					}
					$bill_street = str_replace(array("\r","\n"),array('\r','\n'), popup_decode_html($acct_focus->column_fields['bill_street']));
					$ship_street = str_replace(array("\r","\n"),array('\r','\n'), popup_decode_html($acct_focus->column_fields['ship_street']));
					$autocomplete_return_function[$entity_id] = 'set_return_address("'.$entity_id.'", "'.nl2br(decode_html($slashes_temp_val)).'", "'.$bill_street.'", "'.$ship_street.'", "'.popup_decode_html($acct_focus->column_fields['bill_city']).'", "'.popup_decode_html($acct_focus->column_fields['ship_city']).'", "'.popup_decode_html($acct_focus->column_fields['bill_state']).'", "'.popup_decode_html($acct_focus->column_fields['ship_state']).'", "'.popup_decode_html($acct_focus->column_fields['bill_code']).'", "'.popup_decode_html($acct_focus->column_fields['ship_code']).'", "'.popup_decode_html($acct_focus->column_fields['bill_country']).'", "'.popup_decode_html($acct_focus->column_fields['ship_country']).'","'.popup_decode_html($acct_focus->column_fields['bill_pobox']).'", "'.popup_decode_html($acct_focus->column_fields['ship_pobox']).'");';
					$value = '<a href="javascript:void(0);" onclick=\''.$autocomplete_return_function[$entity_id].'closePopup();\'>'.$temp_val.'</a>'; //crmv@21048m

				}
				elseif($popuptype == "specific_contact_account_address")
				{
					$acct_focus = CRMEntity::getInstance('Accounts');
					$acct_focus->retrieve_entity_info($entity_id,"Accounts");

					$slashes_temp_val = popup_from_html($temp_val);
					$slashes_temp_val = htmlspecialchars($slashes_temp_val,ENT_QUOTES,$default_charset);

					$bill_street = str_replace(array("\r","\n"),array('\r','\n'), popup_decode_html($acct_focus->column_fields['bill_street']));
					$ship_street = str_replace(array("\r","\n"),array('\r','\n'), popup_decode_html($acct_focus->column_fields['ship_street']));
					$autocomplete_return_function[$entity_id] = 'set_return_contact_address("'.$entity_id.'", "'.nl2br(decode_html($slashes_temp_val)).'", "'.$bill_street.'", "'.$ship_street.'", "'.popup_decode_html($acct_focus->column_fields['bill_city']).'", "'.popup_decode_html($acct_focus->column_fields['ship_city']).'", "'.popup_decode_html($acct_focus->column_fields['bill_state']).'", "'.popup_decode_html($acct_focus->column_fields['ship_state']).'", "'.popup_decode_html($acct_focus->column_fields['bill_code']).'", "'.popup_decode_html($acct_focus->column_fields['ship_code']).'", "'.popup_decode_html($acct_focus->column_fields['bill_country']).'", "'.popup_decode_html($acct_focus->column_fields['ship_country']).'","'.popup_decode_html($acct_focus->column_fields['bill_pobox']).'", "'.popup_decode_html($acct_focus->column_fields['ship_pobox']).'");';
					$value = '<a href="javascript:void(0);" onclick=\''.$autocomplete_return_function[$entity_id] .'closePopup();\'>'.$temp_val.'</a>'; //crmv@21048m

				}
				//crmv-14536
				elseif($popuptype == "specific_account_noaddress")
				{
					$acct_focus = CRMEntity::getInstance('Accounts');
					$acct_focus->retrieve_entity_info($entity_id,"Accounts");
					$slashes_temp_val = popup_from_html($temp_val);
					$slashes_temp_val = htmlspecialchars($slashes_temp_val,ENT_QUOTES,$default_charset);
					$autocomplete_return_function[$entity_id]='set_return_account("'.$entity_id.'","'.popup_decode_html($acct_focus->column_fields[accountname]).'");';
					$value = '<a href="javascript:void(0);" onclick=\''.$autocomplete_return_function[$entity_id] .'closePopup();\'>'.$temp_val.'</a>'; //crmv@21048m

				}
				//crmv-14536e
				elseif($popuptype == "specific_potential_account_address")
				{
					$slashes_temp_val = popup_from_html($temp_val);
					$slashes_temp_val = htmlspecialchars($slashes_temp_val,ENT_QUOTES,$default_charset);

					// For B2C support, Potential was enabled to be linked to Contacts also.
					// Hence we need case handling for it.
					$relatedid = $adb->query_result($list_result,$list_result_count,"related_to");
					$relatedentity = getSalesEntityType($relatedid);
					if($relatedentity == 'Accounts') {
						$acct_focus = CRMEntity::getInstance('Accounts');
						$acct_focus->retrieve_entity_info($relatedid,"Accounts");
						$account_name = getAccountName($relatedid);

						$slashes_account_name = popup_from_html($account_name);
						$slashes_account_name = htmlspecialchars($slashes_account_name,ENT_QUOTES,$default_charset);

						$xyz=array('bill_street','bill_city','bill_code','bill_pobox','bill_country','bill_state','ship_street','ship_city','ship_code','ship_pobox','ship_country','ship_state');
						for($i=0;$i<12;$i++){
							if (getFieldVisibilityPermission('Accounts', $current_user->id,$xyz[$i]) == '0'){
								$acct_focus->column_fields[$xyz[$i]] = $acct_focus->column_fields[$xyz[$i]];
							} else {
								$acct_focus->column_fields[$xyz[$i]] = '';
							}
						}
						$bill_street = str_replace(array("\r","\n"),array('\r','\n'), popup_decode_html($acct_focus->column_fields['bill_street']));
						$ship_street = str_replace(array("\r","\n"),array('\r','\n'), popup_decode_html($acct_focus->column_fields['ship_street']));
						$autocomplete_return_function[$entity_id] = 'set_return_address("'.$entity_id.'", "'.nl2br(decode_html($slashes_temp_val)).'", "'.$relatedid.'", "'.nl2br(decode_html($slashes_account_name)).'", "'.$bill_street.'", "'.$ship_street.'", "'.popup_decode_html($acct_focus->column_fields['bill_city']).'", "'.popup_decode_html($acct_focus->column_fields['ship_city']).'", "'.popup_decode_html($acct_focus->column_fields['bill_state']).'", "'.popup_decode_html($acct_focus->column_fields['ship_state']).'", "'.popup_decode_html($acct_focus->column_fields['bill_code']).'", "'.popup_decode_html($acct_focus->column_fields['ship_code']).'", "'.popup_decode_html($acct_focus->column_fields['bill_country']).'", "'.popup_decode_html($acct_focus->column_fields['ship_country']).'","'.popup_decode_html($acct_focus->column_fields['bill_pobox']).'", "'.popup_decode_html($acct_focus->column_fields['ship_pobox']).'");';
						$value = '<a href="javascript:void(0);" onclick=\''.$autocomplete_return_function[$entity_id].'closePopup();\'>'.$temp_val.'</a>'; //crmv@21048m
					} else if($relatedentity == 'Contacts') {

						require_once('modules/Contacts/Contacts.php');
						$contact_name = getContactName($relatedid);

						$slashes_contact_name = popup_from_html($contact_name);
						$slashes_contact_name = htmlspecialchars($slashes_contact_name,ENT_QUOTES,$default_charset);
						$autocomplete_return_function[$entity_id] = 'set_return_contact("'.$entity_id.'", "'.nl2br(decode_html($slashes_temp_val)).'", "'.$relatedid.'", "'.nl2br(decode_html($slashes_contact_name)).'");';
						$value = '<a href="javascript:void(0);" onclick=\''.$autocomplete_return_function[$entity_id].'closePopup();\'>'.$temp_val.'</a>'; //crmv@21048m

					} else {
						$value = $temp_val;
					}
				}
				//added by rdhital/Raju for better emails
				//crmv@25356
				elseif(in_array($popuptype,array('set_return_emails','set_return_emails_cc','set_return_emails_bcc')))
				{
					if ($module=='Accounts')
					{
						$name = $adb->query_result($list_result,$list_result_count,'accountname');
						$accid =$adb->query_result($list_result,$list_result_count,'accountid');
						if(CheckFieldPermission('email1',$module) == "true")
						{
							$emailaddress=$adb->query_result($list_result,$list_result_count,"email1");
							$email_check = 1;
						} else {
							$email_check = 0;
						}
						if($emailaddress == '')
						{
							if(CheckFieldPermission('email2',$module) == 'true')
							{
								$emailaddress2=$adb->query_result($list_result,$list_result_count,"email2");
								$email_check = 2;
							}
							else
							{
								if($email_check == 1) {
									$email_check = 4;
								} else {
									$email_check = 3;
								}
							}
						}
						$querystr="SELECT fieldid,fieldlabel,columnname FROM ".$table_prefix."_field WHERE tabid=? and uitype=13 and ".$table_prefix."_field.presence in (0,2)";
						$queryres = $adb->pquery($querystr, array(getTabid($module)));
						//Change this index 0 - to get the vtiger_fieldid based on email1 or email2
						$fieldid = $adb->query_result($queryres,0,'fieldid');

						$slashes_name = popup_from_html($name);
						$slashes_name = htmlspecialchars($slashes_name,ENT_QUOTES,$default_charset);
						$autocomplete_return_function[$entity_id] = 'return '.$popuptype.'('.$entity_id.','.$fieldid.',"'.decode_html($slashes_name).'","'.$emailaddress.'","'.$emailaddress2.'","'.$email_check.'");';
						$value = '<a href="javascript:void(0);" onclick=\''.$autocomplete_return_function[$entity_id].'closePopup();\'>'.textlength_check($name).'</a>'; //crmv@21048m

					}elseif ($module=='Vendors')
					{
						$name = $adb->query_result($list_result,$list_result_count,'vendorname');
						$venid =$adb->query_result($list_result,$list_result_count,'vendorid');
						if(CheckFieldPermission('email',$module) == "true")
						{
							$emailaddress=$adb->query_result($list_result,$list_result_count,"email");
							$email_check = 1;
						} else {
							$email_check = 0;
						}
						$querystr="SELECT fieldid,fieldlabel,columnname FROM ".$table_prefix."_field WHERE tabid=? and uitype=13 and ".$table_prefix."_field.presence in (0,2)";
						$queryres = $adb->pquery($querystr, array(getTabid($module)));
						//Change this index 0 - to get the vtiger_fieldid based on email1 or email2
						$fieldid = $adb->query_result($queryres,0,'fieldid');

						$slashes_name = popup_from_html($name);
						$slashes_name = htmlspecialchars($slashes_name,ENT_QUOTES,$default_charset);
						$autocomplete_return_function[$entity_id] ='$autocomplete_return_function[$entity_id]';
						$value = '<a href="javascript:void(0);" onclick=\''.$autocomplete_return_function[$entity_id].'closePopup();\'>'.textlength_check($name).'</a>'; //crmv@21048m

					}elseif ($module=='Contacts' || $module=='Leads')
					{
						$name=getFullNameFromQResult($list_result,$list_result_count,$module);
						if(CheckFieldPermission('email',$module) == "true")
						{
							$emailaddress=$adb->query_result($list_result,$list_result_count,"email");
							$email_check = 1;
						} else {
							$email_check = 0;
						}
						if($emailaddress == '')
						{
							if(CheckFieldPermission('yahooid',$module) == 'true')
							{
								$emailaddress2=$adb->query_result($list_result,$list_result_count,"yahooid");
								$email_check = 2;
							}
							else{
								if($email_check == 1) {
									$email_check = 4;
								} else {
									$email_check = 3;
								}
							}
						}

						$querystr="SELECT fieldid,fieldlabel,columnname FROM ".$table_prefix."_field WHERE tabid=? and uitype=13 and ".$table_prefix."_field.presence in (0,2)";
						$queryres = $adb->pquery($querystr, array(getTabid($module)));
						//Change this index 0 - to get the vtiger_fieldid based on email or yahooid
						$fieldid = $adb->query_result($queryres,0,'fieldid');

						$slashes_name = popup_from_html($name);
						$slashes_name = htmlspecialchars($slashes_name,ENT_QUOTES,$default_charset);
						$autocomplete_return_function[$entity_id] = 'return '.$popuptype.'('.$entity_id.','.$fieldid.',"'.decode_html($slashes_name).'","'.$emailaddress.'","'.$emailaddress2.'","'.$email_check.'");';
						$value = '<a href="javascript:void(0);" onclick=\''.$autocomplete_return_function[$entity_id].'closePopup();\'>'.$name.'</a>'; //crmv@21048m

					}else
					{
						$firstname=$adb->query_result($list_result,$list_result_count,"first_name");
						$lastname=$adb->query_result($list_result,$list_result_count,"last_name");
						$name=$lastname.' '.$firstname;
						$emailaddress=$adb->query_result($list_result,$list_result_count,"email1");

						$slashes_name = popup_from_html($name);
						$slashes_name = htmlspecialchars($slashes_name,ENT_QUOTES,$default_charset);
						$email_check = 1;
						$autocomplete_return_function[$entity_id] = 'return '.$popuptype.'('.$entity_id.',-1,"'.decode_html($slashes_name).'","'.$emailaddress.'","'.$emailaddress2.'","'.$email_check.'");';
						$value = '<a href="javascript:void(0);" onclick=\''.$autocomplete_return_function[$entity_id].'closePopup();\'>'.textlength_check($name)	.'</a>'; //crmv@21048m

					}
				}
				//crmv@25356e
				elseif($popuptype == "specific_vendor_address")
				{
					$acct_focus = CRMEntity::getInstance('Vendors');
					$acct_focus->retrieve_entity_info($entity_id,"Vendors");

					$slashes_temp_val = popup_from_html($temp_val);
					$slashes_temp_val = htmlspecialchars($slashes_temp_val,ENT_QUOTES,$default_charset);
					$xyz=array('street','city','postalcode','pobox','country','state');
					for($i=0;$i<6;$i++){
						if (getFieldVisibilityPermission($module, $current_user->id,$xyz[$i]) == '0'){
							$acct_focus->column_fields[$xyz[$i]] = $acct_focus->column_fields[$xyz[$i]];
						} else {
							$acct_focus->column_fields[$xyz[$i]] = '';
						}
					}
					$bill_street = str_replace(array("\r","\n"),array('\r','\n'), popup_decode_html($acct_focus->column_fields['street']));
					$autocomplete_return_function[$entity_id]='set_return_address("'.$entity_id.'", "'.nl2br(decode_html($slashes_temp_val)).'", "'.$bill_street.'", "'.popup_decode_html($acct_focus->column_fields['city']).'", "'.popup_decode_html($acct_focus->column_fields['state']).'", "'.popup_decode_html($acct_focus->column_fields['postalcode']).'", "'.popup_decode_html($acct_focus->column_fields['country']).'","'.popup_decode_html($acct_focus->column_fields['pobox']).'");';
					$value = '<a href="javascript:void(0);" onclick=\''.$autocomplete_return_function[$entity_id].'closePopup();\'>'.$temp_val.'</a>'; //crmv@21048m

				}
				elseif($popuptype == "specific_campaign")
				{
					$slashes_temp_val = popup_from_html($temp_val);
					$slashes_temp_val = htmlspecialchars($slashes_temp_val,ENT_QUOTES,$default_charset);
					$autocomplete_return_function[$entity_id] = 'set_return_specific_campaign("'.$entity_id.'", "'.nl2br(decode_html($slashes_temp_val)).'");';
					$value = '<a href="javascript:void(0);" onclick=\''.$autocomplete_return_function[$entity_id].'closePopup();\'>'.$temp_val.'</a>'; //crmv@21048m
				}
				//crmv@15771
				elseif($popuptype == "select_worker")
				{
					$slashes_temp_val = popup_from_html($temp_val);
					$slashes_temp_val = htmlspecialchars($slashes_temp_val,ENT_QUOTES);
					$autocomplete_return_function[$entity_id] = 'set_return_worker("'.$entity_id.'", "'.nl2br($slashes_temp_val).'", "Workername_'.$_REQUEST["inputid"].'" , "Workerid_'.$_REQUEST["inputid"].'");';
					$value = '<a href="javascript:void(0);" onclick=\''.$autocomplete_return_function[$entity_id].'closePopup();\'>'.$temp_val.'</a>'; //crmv@21048m
				}
				//crmv@15771 end
				//crmv@16265	//crmv@26265
				elseif($popuptype == "squirrel_mail")
				{
					$squirrelvalues = urlencode($_REQUEST['squirrelvalues']);

					$querystr="SELECT fieldid,fieldlabel,columnname FROM ".$table_prefix."_field WHERE tabid=? and uitype=13 and ".$table_prefix."_field.presence in (0,2)";
					$queryres = $adb->pquery($querystr, array(getTabid($module)));
					//Change this index 0 - to get the vtiger_fieldid based on email or yahooid
					$fieldid = $adb->query_result($queryres,0,'fieldid');

					$parent_id = $entity_id.'@'.$fieldid.'|';
					//crmv@26510
					if ($_REQUEST['mass_link']) {
						$mass_link = '&mass_link='.$_REQUEST['mass_link'];
					} else {
						unset($mass_link);
					}
					$value = '<a href="index.php?module=Emails&action=EmailsAjax&file=LinkSquirrel&parent_id='.$parent_id.'&squirrelvalues='.$squirrelvalues.'&recordid='.$_REQUEST['recordid'].$mass_link.'">'.$temp_val.'</a>';
					//crmv@26510e
				}
				//crmv@16265e	//crmv@26265e
				else
				{
					if($colname == "lastname")
					$temp_val = getFullNameFromQResult($list_result,$list_result_count,$module);
					if ($uitype == 1015) {
						
						$temp_val = textlength_check(to_html(PickListMulti::getTranslatedPicklist($temp_val,$colname)));
					}
					$slashes_temp_val = popup_from_html($temp_val);
					$slashes_temp_val = htmlspecialchars($slashes_temp_val,ENT_QUOTES,$default_charset);
					
					$log->debug("Exiting getValue method ...");
					if($_REQUEST['maintab'] == 'Calendar'){
						$autocomplete_return_function[$entity_id] = 'set_return_todo("'.$entity_id.'", "'.nl2br(decode_html($slashes_temp_val)).'");';
						$value = '<a href="javascript:void(0);" onclick=\''.$autocomplete_return_function[$entity_id].'closePopup();\'>'.$temp_val.'</a>'; //crmv@21048m
					}
						//crmv@26807
					elseif ($_REQUEST['fromCalendar'] == 'fromCalendar') {
						$value = '<a href="javascript:void(0);"';
						$value .= ' onclick="';

						//crmv@26961
						$autocomplete_return_function[$entity_id] = '';
						if ($_REQUEST['fromCalendar'] == 'fromEditViewCalendar') {
							$autocomplete_return_function[$entity_id] .= ' linkInviteesTableEditView(\''.$entity_id.'\',\''.nl2br(decode_html($slashes_temp_val)).'\',\''.$_REQUEST['parentId'].'\',\''.$module.'\');';
						} else if ($_REQUEST['parentId'] == 'contacts_div' || $_REQUEST['parentId'] == 'selectedTable') {
							$autocomplete_return_function[$entity_id] .= ' top.wdCalendar.linkContactsTable(\''.$entity_id.'\',\''.nl2br(decode_html($slashes_temp_val)).'\',\''.$_REQUEST['parentId'].'\',\''.$module.'\');';
						} else {
							//crmv@26961e
							switch ($_REQUEST['parentId']) {
								case 'parent_type_link':
									$parent_id_link = 'parent_id_link';
									$selectparent_link = 'selectparent_link';
									break;
								case 'parent_type_link_singleContact':
									$parent_id_link = 'parent_id_link_singleContact';
									$selectparent_link = 'selectparent_link_singleContact';
									break;
							}
							$autocomplete_return_function[$entity_id] .= ' top.wdCalendar.jQuery(\'#'.$selectparent_link.'\').val(\''.nl2br(decode_html($slashes_temp_val)).'\');';
							$autocomplete_return_function[$entity_id] .= ' top.wdCalendar.jQuery(\'#'.$parent_id_link.'\').val(\''.$entity_id.'\');';
						}
						if ($selectparent_link != '') {
							$autocomplete_return_function[$entity_id] .= 'top.wdCalendar.disableReferenceField(top.wdCalendar.jQuery(\'#'.$selectparent_link.'\'));';
						}
						$value .= $autocomplete_return_function[$entity_id] .' closePopup();">'.$temp_val.'</a>';
					}
					//crmv@26807e
					else {
						$autocomplete_return_function[$entity_id] = 'set_return("'.$entity_id.'", "'.nl2br(decode_html($slashes_temp_val)).'");';
						$value = '<a href="javascript:void(0);" onclick=\''.$autocomplete_return_function[$entity_id].'closePopup();\'>'.$temp_val.'</a>'; //crmv@21048m
					}
				}
				//crmv@29190e
			}
			else
			{
				if(($module == "Leads" && $colname == "lastname") || ($module == "Contacts" && $colname == "lastname"))
				{
					$value = '<a href="index.php?action=DetailView&module='.$module.'&record='.$entity_id.'&parenttab='.$tabname.'">'.$temp_val.'</a>';
				}
				elseif($module == "Calendar")
				{
					$actvity_type = $adb->query_result($list_result,$list_result_count,'activitytype');
					$actvity_type = ($actvity_type != '') ? $actvity_type : $adb->query_result($list_result,$list_result_count,'type');
					if($actvity_type == "Task")
					{
						$value = '<a href="index.php?action=DetailView&module='.$module.'&record='.$entity_id.'&activity_mode=Task&parenttab='.$tabname.'">'.$temp_val.'</a>';
					}
					else
					{
						$value = '<a href="index.php?action=DetailView&module='.$module.'&record='.$entity_id.'&activity_mode=Events&parenttab='.$tabname.'">'.$temp_val.'</a>';
					}
				}
				elseif($module == "Vendors")
				{

					$value = '<a href="index.php?action=DetailView&module=Vendors&record='.$entity_id.'&parenttab='.$tabname.'">'.$temp_val.'</a>';
				}
				elseif($module == "PriceBooks")
				{

					$value = '<a href="index.php?action=DetailView&module=PriceBooks&record='.$entity_id.'&parenttab='.$tabname.'">'.$temp_val.'</a>';
				}
				elseif($module == "SalesOrder")
				{

					$value = '<a href="index.php?action=DetailView&module=SalesOrder&record='.$entity_id.'&parenttab='.$tabname.'">'.$temp_val.'</a>';
				}
				elseif($module == 'Emails')
				{
					$value = $temp_val;
				}
				else
				{
					$value = '<a href="index.php?action=DetailView&module='.$module.'&record='.$entity_id.'&parenttab='.$tabname.'">'.$temp_val.'</a>';
				}
			}
		}
		elseif($fieldname == 'expectedroi' || $fieldname == 'actualroi' || $fieldname == 'actualcost' || $fieldname == 'budgetcost' || $fieldname == 'expectedrevenue')
		{
			$rate = $user_info['conv_rate'];
			$value = convertFromDollar($temp_val,$rate);
		}
		elseif(($module == 'Invoice' || $module == 'Quotes' || $module == 'PurchaseOrder' || $module == 'SalesOrder')
				&& ($fieldname == 'hdnGrandTotal' || $fieldname == 'hdnSubTotal' || $fieldname == 'txtAdjustment'
						|| $fieldname == 'hdnDiscountAmount' || $fieldname == 'hdnS_H_Amount'))
		{
			$currency_info = getInventoryCurrencyInfo($module, $entity_id);
			$currency_id = $currency_info['currency_id'];
			$currency_symbol = $currency_info['currency_symbol'];
			$value = $currency_symbol.$temp_val;
		}
		else
		{
			$value = $temp_val;
		}
	}

	// Mike Crowe Mod --------------------------------------------------------Make right justified and vtiger_currency value
	if ( in_array($uitype,array(71,72,7,9,90)) )
	{
		$value = '<span align="right">'.$value.'</div>';
	}
	//crmv@29079+33985
	if ($fieldname == $focus->list_link_field) {
		$value = '<a href="index.php?action=DetailView&module='.$module.'&record='.$entity_id.'&parenttab='.$tabname.'">'.$value.'</a>';
	}
	//crmv@29079+33985e	
	$log->debug("Exiting getValue method ...");
	return $value;
}

/** Function to get the list query for a module
  * @param $module -- module name:: Type string
  * @param $where -- where:: Type string
  * @returns $query -- query:: Type query
  */
function getListQuery($module,$where='')
{
    global $log, $table_prefix;
    $log->debug("Entering getListQuery(".$module.",".$where.") method ...");

    global $current_user;
    require('user_privileges/user_privileges_'.$current_user->id.'.php');
    require('user_privileges/sharing_privileges_'.$current_user->id.'.php');
    $tab_id = getTabid($module);
    $focus = CRMEntity::getInstance($module);
    
	//crmv@31775
    $reportFilterJoin = '';
	$viewId = $_SESSION['lvs'][$module]['viewname'];
	if (isset($_REQUEST['viewname']) && $_REQUEST['viewname'] != '') {
		$viewId = $_REQUEST['viewname'];
	}
	if ($viewId != '') {
	    $oCustomView = new CustomView($module);
		$reportFilter = $oCustomView->getReportFilter($viewId);
		if ($reportFilter) {
			$tableNameTmp = $oCustomView->getReportFilterTableName($reportFilter,$current_user->id);
			$reportFilterJoin = " INNER JOIN $tableNameTmp ON $tableNameTmp.id = {$table_prefix}_crmentity.crmid";
		}
	}
	//crmv@31775e

    // crmv@30014
    $extraJoin = '';
    if ($focus && method_exists($focus, 'getQueryExtraJoin')) {
    	$extraJoin = $focus->getQueryExtraJoin();
    }
    // crmv@30014e

    switch($module)
    {

    	Case "HelpDesk":
		$query = "SELECT ".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.smownerid,
			".$table_prefix."_troubletickets.title, ".$table_prefix."_troubletickets.status,
			".$table_prefix."_troubletickets.priority, ".$table_prefix."_troubletickets.parent_id,
			".$table_prefix."_contactdetails.contactid, ".$table_prefix."_contactdetails.firstname,
			".$table_prefix."_contactdetails.lastname, ".$table_prefix."_account.accountid,
			".$table_prefix."_account.accountname, ".$table_prefix."_ticketcf.*, ".$table_prefix."_troubletickets.ticket_no
			FROM ".$table_prefix."_troubletickets
			INNER JOIN ".$table_prefix."_ticketcf
				ON ".$table_prefix."_ticketcf.ticketid = ".$table_prefix."_troubletickets.ticketid
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_troubletickets.ticketid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_contactdetails
				ON ".$table_prefix."_troubletickets.parent_id = ".$table_prefix."_contactdetails.contactid
			LEFT JOIN ".$table_prefix."_account
				ON ".$table_prefix."_account.accountid = ".$table_prefix."_troubletickets.parent_id
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_crmentity.smownerid = ".$table_prefix."_users.id
			LEFT JOIN ".$table_prefix."_products
				ON ".$table_prefix."_products.productid = ".$table_prefix."_troubletickets.product_id
			$reportFilterJoin $extraJoin";
		$query .= ' '.getNonAdminAccessControlQuery($module,$current_user);
		$query .= "WHERE ".$table_prefix."_crmentity.deleted = 0 ".$where;
			break;

	Case "Accounts":
		//Query modified to sort by assigned to
		$query = "SELECT ".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.smownerid,
			".$table_prefix."_account.accountname, ".$table_prefix."_account.email1,
			".$table_prefix."_account.email2, ".$table_prefix."_account.website, ".$table_prefix."_account.phone,
			".$table_prefix."_accountbillads.bill_city,
			".$table_prefix."_accountscf.*
			FROM ".$table_prefix."_account
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_account.accountid
			INNER JOIN ".$table_prefix."_accountbillads
				ON ".$table_prefix."_account.accountid = ".$table_prefix."_accountbillads.accountaddressid
			INNER JOIN ".$table_prefix."_accountshipads
				ON ".$table_prefix."_account.accountid = ".$table_prefix."_accountshipads.accountaddressid
			INNER JOIN ".$table_prefix."_accountscf
				ON ".$table_prefix."_account.accountid = ".$table_prefix."_accountscf.accountid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_account ".$table_prefix."_account2
				ON ".$table_prefix."_account.parentid = ".$table_prefix."_account2.accountid
			$reportFilterJoin $extraJoin";
		$query .= getNonAdminAccessControlQuery($module,$current_user);
		$query .= "WHERE ".$table_prefix."_crmentity.deleted = 0 ".$where;
			break;

	Case "Potentials":
		//Query modified to sort by assigned to
		$query = "SELECT ".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.smownerid,
			".$table_prefix."_account.accountname,
			".$table_prefix."_potential.related_to, ".$table_prefix."_potential.potentialname,
			".$table_prefix."_potential.sales_stage, ".$table_prefix."_potential.amount,
			".$table_prefix."_potential.currency, ".$table_prefix."_potential.closingdate,
			".$table_prefix."_potential.typeofrevenue,
			".$table_prefix."_potentialscf.*
			FROM ".$table_prefix."_potential
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_potential.potentialid
			INNER JOIN ".$table_prefix."_potentialscf
				ON ".$table_prefix."_potentialscf.potentialid = ".$table_prefix."_potential.potentialid
			LEFT JOIN ".$table_prefix."_account
				ON ".$table_prefix."_potential.related_to = ".$table_prefix."_account.accountid
			LEFT JOIN ".$table_prefix."_contactdetails
				ON ".$table_prefix."_potential.related_to = ".$table_prefix."_contactdetails.contactid
			LEFT JOIN ".$table_prefix."_campaign
				ON ".$table_prefix."_campaign.campaignid = ".$table_prefix."_potential.campaignid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			$reportFilterJoin $extraJoin";
		$query .= getNonAdminAccessControlQuery($module,$current_user);
		$query .= "WHERE ".$table_prefix."_crmentity.deleted = 0 ".$where;
			break;

	Case "Leads":
		$query = "SELECT ".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.smownerid,
			".$table_prefix."_leaddetails.firstname, ".$table_prefix."_leaddetails.lastname,
			".$table_prefix."_leaddetails.company, ".$table_prefix."_leadaddress.phone,
			".$table_prefix."_leadsubdetails.website, ".$table_prefix."_leaddetails.email,
			".$table_prefix."_leadscf.*
			FROM ".$table_prefix."_leaddetails
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_leaddetails.leadid
			INNER JOIN ".$table_prefix."_leadsubdetails
				ON ".$table_prefix."_leadsubdetails.leadsubscriptionid = ".$table_prefix."_leaddetails.leadid
			INNER JOIN ".$table_prefix."_leadaddress
				ON ".$table_prefix."_leadaddress.leadaddressid = ".$table_prefix."_leadsubdetails.leadsubscriptionid
			INNER JOIN ".$table_prefix."_leadscf
				ON ".$table_prefix."_leaddetails.leadid = ".$table_prefix."_leadscf.leadid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			$reportFilterJoin $extraJoin";
		$query .= getNonAdminAccessControlQuery($module,$current_user);
		$query .= "WHERE ".$table_prefix."_crmentity.deleted = 0 AND ".$table_prefix."_leaddetails.converted = 0 ".
			$where;
			break;
	Case "Products":
		$query = "SELECT ".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.description, ".$table_prefix."_products.*, ".$table_prefix."_productcf.*
			FROM ".$table_prefix."_products
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_products.productid
			INNER JOIN ".$table_prefix."_productcf
				ON ".$table_prefix."_products.productid = ".$table_prefix."_productcf.productid
			LEFT JOIN ".$table_prefix."_vendor
				ON ".$table_prefix."_vendor.vendorid = ".$table_prefix."_products.vendor_id
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_users.id = ".$table_prefix."_products.handler
			$reportFilterJoin $extraJoin";
		if((isset($_REQUEST["from_dashboard"]) && $_REQUEST["from_dashboard"] == true) && (isset($_REQUEST["type"]) && $_REQUEST["type"] =="dbrd"))
                        $query .= " INNER JOIN ".$table_prefix."_inventoryproductrel on ".$table_prefix."_inventoryproductrel.productid = ".$table_prefix."_products.productid";
                $query .= " WHERE ".$table_prefix."_crmentity.deleted = 0 ".$where;
			break;
	Case "Documents":
		// crmv@30967
		$query = "SELECT case when (".$table_prefix."_users.user_name not like '') then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name,".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.modifiedtime,
			".$table_prefix."_crmentity.smownerid,".$table_prefix."_crmentityfolder.*,".$table_prefix."_notes.*
			FROM ".$table_prefix."_notes
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_notes.notesid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_crmentityfolder
				ON ".$table_prefix."_notes.folderid = ".$table_prefix."_crmentityfolder.folderid
			$reportFilterJoin $extraJoin";
		// crmv@30967e
		$query .= getNonAdminAccessControlQuery($module,$current_user);
		$query .= "WHERE ".$table_prefix."_crmentity.deleted = 0 ".$where;
			break;
	Case "Contacts":
		//Query modified to sort by assigned to
		$query = "SELECT ".$table_prefix."_contactdetails.firstname, ".$table_prefix."_contactdetails.lastname,
			".$table_prefix."_contactdetails.title, ".$table_prefix."_contactdetails.accountid,
			".$table_prefix."_contactdetails.email, ".$table_prefix."_contactdetails.phone,
			".$table_prefix."_crmentity.smownerid, ".$table_prefix."_crmentity.crmid
			FROM ".$table_prefix."_contactdetails
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_contactdetails.contactid
			INNER JOIN ".$table_prefix."_contactaddress
				ON ".$table_prefix."_contactaddress.contactaddressid = ".$table_prefix."_contactdetails.contactid
			INNER JOIN ".$table_prefix."_contactsubdetails
				ON ".$table_prefix."_contactsubdetails.contactsubscriptionid = ".$table_prefix."_contactdetails.contactid
			INNER JOIN ".$table_prefix."_contactscf
				ON ".$table_prefix."_contactscf.contactid = ".$table_prefix."_contactdetails.contactid
			LEFT JOIN ".$table_prefix."_account
				ON ".$table_prefix."_account.accountid = ".$table_prefix."_contactdetails.accountid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_contactdetails ".$table_prefix."_contactdetails2
				ON ".$table_prefix."_contactdetails.reportsto = ".$table_prefix."_contactdetails2.contactid
			LEFT JOIN ".$table_prefix."_customerdetails
				ON ".$table_prefix."_customerdetails.customerid = ".$table_prefix."_contactdetails.contactid
			$reportFilterJoin $extraJoin";
		if((isset($_REQUEST["from_dashboard"]) && $_REQUEST["from_dashboard"] == true) &&
				(isset($_REQUEST["type"]) && $_REQUEST["type"] =="dbrd")) {
			$query .= " INNER JOIN ".$table_prefix."_campaigncontrel on ".$table_prefix."_campaigncontrel.contactid = ".
			$table_prefix."_contactdetails.contactid";
		}
		$query .= getNonAdminAccessControlQuery($module,$current_user);
		$query .= "WHERE ".$table_prefix."_crmentity.deleted = 0 ".$where;
			break;
	Case "Calendar":
		//crmv@17986
		$query="SELECT
		case when (".$table_prefix."_users.user_name not like '') then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name,
		".$table_prefix."_activity.activityid as act_id,".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.smownerid, ".$table_prefix."_crmentity.setype,
		".$table_prefix."_activity.*,
		".$table_prefix."_contactdetails.lastname, ".$table_prefix."_contactdetails.firstname,
		".$table_prefix."_contactdetails.contactid,
		".$table_prefix."_account.accountid, ".$table_prefix."_account.accountname,".$table_prefix."_crmentity.description
		FROM ".$table_prefix."_activity
		LEFT JOIN ".$table_prefix."_activitycf
			ON ".$table_prefix."_activitycf.activityid = ".$table_prefix."_activity.activityid
		LEFT JOIN ".$table_prefix."_cntactivityrel
			ON ".$table_prefix."_cntactivityrel.activityid = ".$table_prefix."_activity.activityid
		LEFT JOIN ".$table_prefix."_contactdetails
			ON ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_cntactivityrel.contactid
		LEFT JOIN ".$table_prefix."_seactivityrel
			ON ".$table_prefix."_seactivityrel.activityid = ".$table_prefix."_activity.activityid
		LEFT OUTER JOIN ".$table_prefix."_activity_reminder
			ON ".$table_prefix."_activity_reminder.activity_id = ".$table_prefix."_activity.activityid
		LEFT JOIN ".$table_prefix."_crmentity
			ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_activity.activityid
		LEFT JOIN ".$table_prefix."_users
			ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
		LEFT JOIN ".$table_prefix."_groups
			ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
		LEFT OUTER JOIN ".$table_prefix."_account
			ON ".$table_prefix."_account.accountid = ".$table_prefix."_contactdetails.accountid
		LEFT OUTER JOIN ".$table_prefix."_leaddetails
	       		ON ".$table_prefix."_leaddetails.leadid = ".$table_prefix."_seactivityrel.crmid
		LEFT OUTER JOIN ".$table_prefix."_account ".$table_prefix."_account2
	        	ON ".$table_prefix."_account2.accountid = ".$table_prefix."_seactivityrel.crmid
		LEFT OUTER JOIN ".$table_prefix."_potential
	       		ON ".$table_prefix."_potential.potentialid = ".$table_prefix."_seactivityrel.crmid
		LEFT OUTER JOIN ".$table_prefix."_troubletickets
	       		ON ".$table_prefix."_troubletickets.ticketid = ".$table_prefix."_seactivityrel.crmid
		LEFT OUTER JOIN ".$table_prefix."_salesorder
			ON ".$table_prefix."_salesorder.salesorderid = ".$table_prefix."_seactivityrel.crmid
		LEFT OUTER JOIN ".$table_prefix."_purchaseorder
			ON ".$table_prefix."_purchaseorder.purchaseorderid = ".$table_prefix."_seactivityrel.crmid
		LEFT OUTER JOIN ".$table_prefix."_quotes
			ON ".$table_prefix."_quotes.quoteid = ".$table_prefix."_seactivityrel.crmid
		LEFT OUTER JOIN ".$table_prefix."_invoice
	                ON ".$table_prefix."_invoice.invoiceid = ".$table_prefix."_seactivityrel.crmid
		LEFT OUTER JOIN ".$table_prefix."_campaign
		ON ".$table_prefix."_campaign.campaignid = ".$table_prefix."_seactivityrel.crmid
		$reportFilterJoin $extraJoin";
		//crmv@17986 end
		//added to fix #5135
		if(isset($_REQUEST['from_homepage']) && ($_REQUEST['from_homepage'] ==
				"upcoming_activities" || $_REQUEST['from_homepage'] == "pending_activities")) {
			$query.=" LEFT OUTER JOIN ".$table_prefix."_recurringevents
			             ON ".$table_prefix."_recurringevents.activityid=".$table_prefix."_activity.activityid";
		}
		//end

		$query .= getNonAdminAccessControlQuery($module,$current_user);
		//crmv@17997
		$query.=" WHERE ".$table_prefix."_crmentity.deleted = 0 AND activitytype not in ('Emails','Fax','Sms') ".$where;
		//crmv@17997 end
			break;
	Case "Emails":
		$query = "SELECT DISTINCT ".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.smownerid,
			".$table_prefix."_activity.activityid, ".$table_prefix."_activity.subject,
			".$table_prefix."_activity.date_start,
			".$table_prefix."_contactdetails.lastname, ".$table_prefix."_contactdetails.firstname,
			".$table_prefix."_contactdetails.contactid
			FROM ".$table_prefix."_activity
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_activity.activityid
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_seactivityrel
				ON ".$table_prefix."_seactivityrel.activityid = ".$table_prefix."_activity.activityid
			LEFT JOIN ".$table_prefix."_contactdetails
				ON ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_seactivityrel.crmid
			LEFT JOIN ".$table_prefix."_cntactivityrel
				ON ".$table_prefix."_cntactivityrel.activityid = ".$table_prefix."_activity.activityid
				AND ".$table_prefix."_cntactivityrel.contactid = ".$table_prefix."_cntactivityrel.contactid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_salesmanactivityrel
				ON ".$table_prefix."_salesmanactivityrel.activityid = ".$table_prefix."_activity.activityid
			LEFT JOIN ".$table_prefix."_emaildetails
				ON ".$table_prefix."_emaildetails.emailid = ".$table_prefix."_activity.activityid
			$reportFilterJoin $extraJoin
			WHERE ".$table_prefix."_activity.activitytype = 'Emails'";
		$query .= getNonAdminAccessControlQuery($module,$current_user);
		$query .= "AND ".$table_prefix."_crmentity.deleted = 0 ".$where;
			break;
	Case "Faq":
		$query = "SELECT ".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.createdtime, ".$table_prefix."_crmentity.modifiedtime,
			".$table_prefix."_faq.*
			FROM ".$table_prefix."_faq
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_faq.id
			LEFT JOIN ".$table_prefix."_products
				ON ".$table_prefix."_faq.product_id = ".$table_prefix."_products.productid
			$reportFilterJoin $extraJoin";
		$query .= getNonAdminAccessControlQuery($module,$current_user);
		$query .= "WHERE ".$table_prefix."_crmentity.deleted = 0 ".$where;
			break;

	Case "Vendors":
		$query = "SELECT ".$table_prefix."_crmentity.crmid, ".$table_prefix."_vendor.*
			FROM ".$table_prefix."_vendor
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_vendor.vendorid
			INNER JOIN ".$table_prefix."_vendorcf
				ON ".$table_prefix."_vendor.vendorid = ".$table_prefix."_vendorcf.vendorid
			$reportFilterJoin $extraJoin
			WHERE ".$table_prefix."_crmentity.deleted = 0 ".$where;
			break;
	Case "PriceBooks":
		$query = "SELECT ".$table_prefix."_crmentity.crmid, ".$table_prefix."_pricebook.*, ".$table_prefix."_currency_info.currency_name
			FROM ".$table_prefix."_pricebook
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_pricebook.pricebookid
			INNER JOIN ".$table_prefix."_pricebookcf
				ON ".$table_prefix."_pricebook.pricebookid = ".$table_prefix."_pricebookcf.pricebookid
			LEFT JOIN ".$table_prefix."_currency_info
				ON ".$table_prefix."_pricebook.currency_id = ".$table_prefix."_currency_info.id
			$reportFilterJoin $extraJoin
			WHERE ".$table_prefix."_crmentity.deleted = 0 ".$where;
			break;
	Case "Quotes":
		//Query modified to sort by assigned to
		$query = "SELECT ".$table_prefix."_crmentity.*,
			".$table_prefix."_quotes.*,
			".$table_prefix."_quotesbillads.*,
			".$table_prefix."_quotesshipads.*,
			".$table_prefix."_potential.potentialname,
			".$table_prefix."_account.accountname,
			".$table_prefix."_currency_info.currency_name
			FROM ".$table_prefix."_quotes
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_quotes.quoteid
			INNER JOIN ".$table_prefix."_quotesbillads
				ON ".$table_prefix."_quotes.quoteid = ".$table_prefix."_quotesbillads.quotebilladdressid
			INNER JOIN ".$table_prefix."_quotesshipads
				ON ".$table_prefix."_quotes.quoteid = ".$table_prefix."_quotesshipads.quoteshipaddressid
			LEFT JOIN ".$table_prefix."_quotescf
				ON ".$table_prefix."_quotes.quoteid = ".$table_prefix."_quotescf.quoteid
			LEFT JOIN ".$table_prefix."_currency_info
				ON ".$table_prefix."_quotes.currency_id = ".$table_prefix."_currency_info.id
			LEFT OUTER JOIN ".$table_prefix."_account
				ON ".$table_prefix."_account.accountid = ".$table_prefix."_quotes.accountid
			LEFT OUTER JOIN ".$table_prefix."_potential
				ON ".$table_prefix."_potential.potentialid = ".$table_prefix."_quotes.potentialid
			LEFT JOIN ".$table_prefix."_contactdetails
				ON ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_quotes.contactid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_users ".$table_prefix."_usersQuotes
				ON ".$table_prefix."_usersQuotes.id = ".$table_prefix."_quotes.inventorymanager
			$reportFilterJoin $extraJoin";
		$query .= getNonAdminAccessControlQuery($module,$current_user);
		$query .= "WHERE ".$table_prefix."_crmentity.deleted = 0 ".$where;
			break;
	Case "PurchaseOrder":
		//Query modified to sort by assigned to
                $query = "SELECT ".$table_prefix."_crmentity.*,
			".$table_prefix."_purchaseorder.*,
			".$table_prefix."_pobillads.*,
			".$table_prefix."_poshipads.*,
			".$table_prefix."_vendor.vendorname,
			".$table_prefix."_currency_info.currency_name
			FROM ".$table_prefix."_purchaseorder
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_purchaseorder.purchaseorderid
			LEFT OUTER JOIN ".$table_prefix."_vendor
				ON ".$table_prefix."_purchaseorder.vendorid = ".$table_prefix."_vendor.vendorid
			LEFT JOIN ".$table_prefix."_contactdetails
				ON ".$table_prefix."_purchaseorder.contactid = ".$table_prefix."_contactdetails.contactid
			INNER JOIN ".$table_prefix."_pobillads
				ON ".$table_prefix."_purchaseorder.purchaseorderid = ".$table_prefix."_pobillads.pobilladdressid
			INNER JOIN ".$table_prefix."_poshipads
				ON ".$table_prefix."_purchaseorder.purchaseorderid = ".$table_prefix."_poshipads.poshipaddressid
			LEFT JOIN ".$table_prefix."_purchaseordercf
				ON ".$table_prefix."_purchaseordercf.purchaseorderid = ".$table_prefix."_purchaseorder.purchaseorderid
			LEFT JOIN ".$table_prefix."_currency_info
				ON ".$table_prefix."_purchaseorder.currency_id = ".$table_prefix."_currency_info.id
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			$reportFilterJoin $extraJoin";
		$query .= getNonAdminAccessControlQuery($module,$current_user);
		$query .= "WHERE ".$table_prefix."_crmentity.deleted = 0 ".$where;
			break;
	Case "SalesOrder":
		//Query modified to sort by assigned to
                $query = "SELECT ".$table_prefix."_crmentity.*,
			".$table_prefix."_salesorder.*,
			".$table_prefix."_sobillads.*,
			".$table_prefix."_soshipads.*,
			".$table_prefix."_quotes.subject AS quotename,
			".$table_prefix."_account.accountname,
			".$table_prefix."_currency_info.currency_name
			FROM ".$table_prefix."_salesorder
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_salesorder.salesorderid
			INNER JOIN ".$table_prefix."_sobillads
				ON ".$table_prefix."_salesorder.salesorderid = ".$table_prefix."_sobillads.sobilladdressid
			INNER JOIN ".$table_prefix."_soshipads
				ON ".$table_prefix."_salesorder.salesorderid = ".$table_prefix."_soshipads.soshipaddressid
			LEFT JOIN ".$table_prefix."_salesordercf
				ON ".$table_prefix."_salesordercf.salesorderid = ".$table_prefix."_salesorder.salesorderid
			LEFT JOIN ".$table_prefix."_currency_info
				ON ".$table_prefix."_salesorder.currency_id = ".$table_prefix."_currency_info.id
			LEFT OUTER JOIN ".$table_prefix."_quotes
				ON ".$table_prefix."_quotes.quoteid = ".$table_prefix."_salesorder.quoteid
			LEFT OUTER JOIN ".$table_prefix."_account
				ON ".$table_prefix."_account.accountid = ".$table_prefix."_salesorder.accountid
			LEFT JOIN ".$table_prefix."_contactdetails
				ON ".$table_prefix."_salesorder.contactid = ".$table_prefix."_contactdetails.contactid
			LEFT JOIN ".$table_prefix."_potential
				ON ".$table_prefix."_potential.potentialid = ".$table_prefix."_salesorder.potentialid
			LEFT JOIN ".$table_prefix."_invoice_recurring_info
				ON ".$table_prefix."_invoice_recurring_info.salesorderid = ".$table_prefix."_salesorder.salesorderid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			$reportFilterJoin $extraJoin";
		$query .= getNonAdminAccessControlQuery($module,$current_user);
		$query .= "WHERE ".$table_prefix."_crmentity.deleted = 0 ".$where;
			break;
	Case "Invoice":
		//Query modified to sort by assigned to
		//query modified -Code contribute by Geoff(http://forums.".$table_prefix.".com/viewtopic.php?t=3376)
		$query = "SELECT ".$table_prefix."_crmentity.*,
			".$table_prefix."_invoice.*,
			".$table_prefix."_invoicebillads.*,
			".$table_prefix."_invoiceshipads.*,
			".$table_prefix."_salesorder.subject AS salessubject,
			".$table_prefix."_account.accountname,
			".$table_prefix."_currency_info.currency_name
			FROM ".$table_prefix."_invoice
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_invoice.invoiceid
			INNER JOIN ".$table_prefix."_invoicebillads
				ON ".$table_prefix."_invoice.invoiceid = ".$table_prefix."_invoicebillads.invoicebilladdressid
			INNER JOIN ".$table_prefix."_invoiceshipads
				ON ".$table_prefix."_invoice.invoiceid = ".$table_prefix."_invoiceshipads.invoiceshipaddressid
			LEFT JOIN ".$table_prefix."_currency_info
				ON ".$table_prefix."_invoice.currency_id = ".$table_prefix."_currency_info.id
			LEFT OUTER JOIN ".$table_prefix."_salesorder
				ON ".$table_prefix."_salesorder.salesorderid = ".$table_prefix."_invoice.salesorderid
			LEFT OUTER JOIN ".$table_prefix."_account
			        ON ".$table_prefix."_account.accountid = ".$table_prefix."_invoice.accountid
			LEFT JOIN ".$table_prefix."_contactdetails
				ON ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_invoice.contactid
			INNER JOIN ".$table_prefix."_invoicecf
				ON ".$table_prefix."_invoice.invoiceid = ".$table_prefix."_invoicecf.invoiceid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			$reportFilterJoin $extraJoin";
		$query .= getNonAdminAccessControlQuery($module,$current_user);
		$query .= "WHERE ".$table_prefix."_crmentity.deleted = 0 ".$where;
			break;
	Case "Campaigns":
		//Query modified to sort by assigned to
		//query modified -Code contribute by Geoff(http://forums.".$table_prefix.".com/viewtopic.php?t=3376)
		$query = "SELECT ".$table_prefix."_crmentity.*,
			".$table_prefix."_campaign.*
			FROM ".$table_prefix."_campaign
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_campaign.campaignid
			INNER JOIN ".$table_prefix."_campaignscf
			        ON ".$table_prefix."_campaign.campaignid = ".$table_prefix."_campaignscf.campaignid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_products
				ON ".$table_prefix."_products.productid = ".$table_prefix."_campaign.product_id
			$reportFilterJoin $extraJoin";
		$query .= getNonAdminAccessControlQuery($module,$current_user);
		$query .= "WHERE ".$table_prefix."_crmentity.deleted = 0 ".$where;
			break;
	Case "Users":
		$query = "SELECT id,user_name,first_name,last_name,email1,phone_mobile,phone_work,is_admin,status,
					".$table_prefix."_user2role.roleid as roleid,".$table_prefix."_role.depth as depth
				 	FROM ".$table_prefix."_users
				 	INNER JOIN ".$table_prefix."_user2role ON ".$table_prefix."_users.id = ".$table_prefix."_user2role.userid
				 	INNER JOIN ".$table_prefix."_role ON ".$table_prefix."_user2role.roleid = ".$table_prefix."_role.roleid
				 	$reportFilterJoin $extraJoin
					WHERE deleted=0 ".$where ;
			break;
	default:
		$query = $focus->getListQuery($module, $where);
		$default = true;
	}
	if (!$default){
		if($module != 'Users') {
			$query = $focus->listQueryNonAdminChange($query, $module);
		}
	}
    $log->debug("Exiting getListQuery method ...");
    return $query;
}

/**Function returns the list of records which an user is entiled to view
*Param $module - module name
*Returns a database query - type string
*/

function getReadEntityIds($module)
{
    global $log;
    $log->debug("Entering getReadEntityIds(".$module.") method ...");
    global $current_user;
    require('user_privileges/user_privileges_'.$current_user->id.'.php');
    require('user_privileges/sharing_privileges_'.$current_user->id.'.php');
    $tab_id = getTabid($module);
	$listquery = getListQuery($module);
	$listviewquery = substr($listquery, strpos($listquery,'FROM'),strlen($listquery));
	$query = "SELECT {$table_prefix}_crmentity.crmid ".$listviewquery;
    $log->debug("Exiting getReadEntityIds method ...");
    return $query;

}

/** Function to get alphabetical search links
*Param $module - module name
*Param $action - action
*Param $fieldname - vtiger_field name
*Param $query - query
*Param $type - search type
*Param $popuptype - popup type
*Param $recordid - record id
*Param $return_module - return module
*Param $append_url - url string to be appended
*Param $viewid - custom view id
*Param $groupid - group id
*Returns an string value
 */
function AlphabeticalSearch($module,$action,$fieldname,$query,$type,$popuptype='',$recordid='',$return_module='',$append_url='',$viewid='',$groupid='')
{
	global $log;
	$log->debug("Entering AlphabeticalSearch(".$module.",".$action.",".$fieldname.",".$query.",".$type.",".$popuptype.",".$recordid.",".$return_module.",".$append_url.",".$viewid.",".$groupid.") method ...");
	if($type=='advanced')
		$flag='&advanced=true';

	if($popuptype != '')
		$popuptypevalue = "&popuptype=".$popuptype;

        if($recordid != '')
                $returnvalue = '&recordid='.$recordid;
        if($return_module != '')
                $returnvalue .= '&return_module='.$return_module;

	// vtlib Customization : For uitype 10 popup during paging
	if($_REQUEST['form'] == 'vtlibPopupView') {
		$returnvalue .= '&form=vtlibPopupView&forfield='.vtlib_purify($_REQUEST['forfield']).'&srcmodule='.vtlib_purify($_REQUEST['srcmodule']).'&forrecord='.vtlib_purify($_REQUEST['forrecord']);
	}
	// END

	for($var='A',$i =1;$i<=26;$i++,$var++)
	// Mike Crowe Mod --------------------------------------------------------added groupid to url
		$list .= '<td class="searchAlph" id="alpha_'.$i.'" align="center" onClick=\'alphabetic("'.$module.'","gname='.$groupid.'&query='.$query.'&search_field='.$fieldname.'&searchtype=BasicSearch&operator=s&type=alpbt&search_text='.$var.$flag.$popuptypevalue.$returnvalue.$append_url.'","alpha_'.$i.'", "'.intval($_REQUEST['folderid']).'")\'>'.$var.'</td>'; // crmv@30967

	$log->debug("Exiting AlphabeticalSearch method ...");
	return $list;
}

/**Function to get parent name for a given parent id
*Param $module - module name
*Param $list_result- result set
*Param $rset - result set index
*Returns an string value
*/
function getRelatedToEntity($module,$list_result,$rset)
{
    global $log;
    $log->debug("Entering getRelatedToEntity(".$module.",".$list_result.",".$rset.") method ...");

    global $adb, $table_prefix;
    $seid = $adb->query_result($list_result,$rset,"relatedto");
    $action = "DetailView";

    if(isset($seid) && $seid != '')
    {
        $parent_module = $parent_module = getSalesEntityType($seid);
        if($parent_module == 'Accounts')
        {
        $numrows= $adb->num_rows($evt_result);

        $parent_module = $adb->query_result($evt_result,0,'setype');
        $parent_id = $adb->query_result($evt_result,0,'crmid');

        if ($numrows>1){
        $parent_module ='Multiple';
        $parent_name=$app_strings['LBL_MULTIPLE'];
        }
        //Raju -- Ends
            $parent_query = "SELECT accountname FROM ".$table_prefix."_account WHERE accountid=?";
            $parent_result = $adb->pquery($parent_query, array($seid));
            $parent_name = $adb->query_result($parent_result,0,"accountname");
        }
        if($parent_module == 'Leads')
        {
            $parent_query = "SELECT firstname,lastname FROM ".$table_prefix."_leaddetails WHERE leadid=?";
            $parent_result = $adb->pquery($parent_query, array($seid));
            $parent_name = getFullNameFromQResult($parent_result,0,"Leads");
        }
        if($parent_module == 'Potentials')
        {
            $parent_query = "SELECT potentialname FROM ".$table_prefix."_potential WHERE potentialid=?";
            $parent_result = $adb->pquery($parent_query, array($seid));
            $parent_name = $adb->query_result($parent_result,0,"potentialname");
        }
        if($parent_module == 'Products')
        {
            $parent_query = "SELECT productname FROM ".$table_prefix."_products WHERE productid=?";
            $parent_result = $adb->pquery($parent_query, array($seid));
            $parent_name = $adb->query_result($parent_result,0,"productname");
        }
        if($parent_module == 'PurchaseOrder')
        {
            $parent_query = "SELECT subject FROM ".$table_prefix."_purchaseorder WHERE purchaseorderid=?";
            $parent_result = $adb->pquery($parent_query, array($seid));
            $parent_name = $adb->query_result($parent_result,0,"subject");
        }
        if($parent_module == 'SalesOrder')
        {
            $parent_query = "SELECT subject FROM ".$table_prefix."_salesorder WHERE salesorderid=?";
            $parent_result = $adb->pquery($parent_query, array($seid));
            $parent_name = $adb->query_result($parent_result,0,"subject");
        }
        if($parent_module == 'Invoice')
        {
            $parent_query = "SELECT subject FROM ".$table_prefix."_invoice WHERE invoiceid=?";
            $parent_result = $adb->pquery($parent_query, array($seid));
            $parent_name = $adb->query_result($parent_result,0,"subject");
        }
        if($parent_module == 'Vendors')
        {
            $parent_query = "SELECT vendorname FROM ".$table_prefix."_vendor WHERE vendorid=?";
            $parent_result = $adb->pquery($parent_query, array($seid));
            $parent_name = $adb->query_result($parent_result,0,"vendorname");
        }

        $parent_value = "<a href='index.php?module=".$parent_module."&action=".$action."&record=".$seid."'>".$parent_name."</a>";
    }
    else
    {
        $parent_value = '';
    }
    $log->debug("Exiting getRelatedToEntity method ...");
    return $parent_value;

}

/**Function to get parent name for a given parent id
*Param $module - module name
*Param $list_result- result set
*Param $rset - result set index
*Returns an string value
*/

//used in home page listTop vtiger_files
function getRelatedTo($module,$list_result,$rset)
{
    global $adb,$log,$app_strings, $table_prefix;
    $log->debug("Entering getRelatedTo(".$module.",".$list_result.",".$rset.") method ...");
    $tabname = getParentTab();
    if($module == "Documents")
    {
            $notesid = $adb->query_result($list_result,$rset,"notesid");
            $action = "DetailView";
            $evt_query="SELECT ".$table_prefix."_senotesrel.crmid, ".$table_prefix."_crmentity.setype
                    FROM ".$table_prefix."_senotesrel
                    INNER JOIN ".$table_prefix."_crmentity
                    ON  ".$table_prefix."_senotesrel.crmid = ".$table_prefix."_crmentity.crmid
                WHERE ".$table_prefix."_senotesrel.notesid = ?";
            $params = array($notesid);
    }else if($module == "Products")
    {
            $productid = $adb->query_result($list_result,$rset,"productid");
            $action = "DetailView";
            $evt_query="SELECT ".$table_prefix."_seproductsrel.crmid, ".$table_prefix."_crmentity.setype
                    FROM ".$table_prefix."_seproductsrel
                    INNER JOIN ".$table_prefix."_crmentity
                    ON ".$table_prefix."_seproductsrel.crmid = ".$table_prefix."_crmentity.crmid
                    WHERE ".$table_prefix."_seproductsrel.productid =?";
            $params = array($productid);
    }else
    {
        $activity_id = $adb->query_result($list_result,$rset,"crmid");
        $action = "DetailView";
        $evt_query="SELECT ".$table_prefix."_seactivityrel.crmid, ".$table_prefix."_crmentity.setype
            FROM ".$table_prefix."_seactivityrel
            INNER JOIN ".$table_prefix."_crmentity
                ON  ".$table_prefix."_seactivityrel.crmid = ".$table_prefix."_crmentity.crmid
            WHERE ".$table_prefix."_seactivityrel.activityid=?";
            $params = array($activity_id);

        if($module == 'HelpDesk')
        {
            $activity_id = $adb->query_result($list_result,$rset,"parent_id");
            if($activity_id != '')
                $evt_query = "SELECT * FROM ".$table_prefix."_crmentity WHERE crmid=?";
                $params = array($activity_id);
        }
    }
    //added by raju to change the related to in emails inot multiple if email is for more than one contact
        $evt_result = $adb->pquery($evt_query, $params);
        $numrows= $adb->num_rows($evt_result);

        $parent_module = $adb->query_result($evt_result,0,'setype');
        $parent_id = $adb->query_result($evt_result,0,'crmid');



        if ($numrows>1){
        $parent_module ='Multiple';
        $parent_name=$app_strings['LBL_MULTIPLE'];
        }
        //Raju -- Ends
    if($module == 'HelpDesk' && ($parent_module == 'Accounts' || $parent_module == 'Contacts'))
        {
                global $theme;
                $module_icon = '<img src="themes/'.$theme.'/images/'.$parent_module.'.gif" alt="'.$app_strings[$parent_module].'" title="'.$app_strings[$parent_module].'" border=0 align=center> ';
        }

    $action = "DetailView";
        if($parent_module == 'Accounts')
        {
                $parent_query = "SELECT accountname FROM ".$table_prefix."_account WHERE accountid=?";
                $parent_result = $adb->pquery($parent_query, array($parent_id));
                $parent_name = textlength_check($adb->query_result($parent_result,0,"accountname"));
        }
        if($parent_module == 'Leads')
        {
                $parent_query = "SELECT firstname,lastname FROM ".$table_prefix."_leaddetails WHERE leadid=?";
                $parent_result = $adb->pquery($parent_query, array($parent_id));
                $parent_name = getFullNameFromQResult($parent_result,0,"Leads");
        }
        if($parent_module == 'Potentials')
        {
                $parent_query = "SELECT potentialname FROM ".$table_prefix."_potential WHERE potentialid=?";
                $parent_result = $adb->pquery($parent_query, array($parent_id));
                $parent_name = textlength_check($adb->query_result($parent_result,0,"potentialname"));
        }
        if($parent_module == 'Products')
        {
                $parent_query = "SELECT productname FROM ".$table_prefix."_products WHERE productid=?";
                $parent_result = $adb->pquery($parent_query, array($parent_id));
                $parent_name = $adb->query_result($parent_result,0,"productname");
        }
    if($parent_module == 'Quotes')
        {
                $parent_query = "SELECT subject FROM ".$table_prefix."_quotes WHERE quoteid=?";
                $parent_result = $adb->pquery($parent_query, array($parent_id));
                $parent_name = $adb->query_result($parent_result,0,"subject");
        }
    if($parent_module == 'PurchaseOrder')
        {
                $parent_query = "SELECT subject FROM ".$table_prefix."_purchaseorder WHERE purchaseorderid=?";
                $parent_result = $adb->pquery($parent_query, array($parent_id));
                $parent_name = $adb->query_result($parent_result,0,"subject");
        }
    if($parent_module == 'Invoice')
        {
                $parent_query = "SELECT subject FROM ".$table_prefix."_invoice WHERE invoiceid=?";
                $parent_result = $adb->pquery($parent_query, array($parent_id));
                $parent_name = $adb->query_result($parent_result,0,"subject");
        }
        if($parent_module == 'SalesOrder')
        {
                $parent_query = "SELECT subject FROM ".$table_prefix."_salesorder WHERE salesorderid=?";
                $parent_result = $adb->pquery($parent_query, array($parent_id));
                $parent_name = $adb->query_result($parent_result,0,"subject");
        }
        //crmv@7216
    if($parent_module == 'Contacts' && ($module == 'Emails' || $module == 'Fax' || $module =='Sms' || $module == 'HelpDesk'))
        {
        	//crmv@7216e
                $parent_query = "SELECT firstname,lastname FROM ".$table_prefix."_contactdetails WHERE contactid=?";
                $parent_result = $adb->pquery($parent_query, array($parent_id));
                $parent_name = getFullNameFromQResult($parent_result,0,"Contacts");
        }
    if($parent_module == 'HelpDesk')
    {
        $parent_query = "SELECT title FROM ".$table_prefix."_troubletickets WHERE ticketid=?";
        $parent_result = $adb->pquery($parent_query, array($parent_id));
        $parent_name = $adb->query_result($parent_result,0,"title");
        //if(strlen($parent_name) > 25)
        //{
            $parent_name = textlength_check($parent_name);
        //}
    }
    if($parent_module == 'Campaigns')
    {
        $parent_query = "SELECT campaignname FROM ".$table_prefix."_campaign WHERE campaignid=?";
        $parent_result = $adb->pquery($parent_query, array($parent_id));
        $parent_name = $adb->query_result($parent_result,0,"campaignname");
        //if(strlen($parent_name) > 25)
        //{
            $parent_name = textlength_check($parent_name);
        //}
    }
    if($parent_module == 'Vendors')
    {
        $parent_query = "SELECT vendorname FROM ".$table_prefix."_vendor WHERE vendorid=?";
        $parent_result = $adb->pquery($parent_query, array($parent_id));
        $parent_name = $adb->query_result($parent_result,0,"vendorname");
        //if(strlen($parent_name) > 25)
        //{
            $parent_name = textlength_check($parent_name);
        //}
    }
    //crmv@vistreport
    if($parent_module == 'Visitreport')
    {
        $parent_query = "SELECT visitreportname FROM ".$table_prefix."_visitreport WHERE visitreportid=?";
        $parent_result = $adb->pquery($parent_query, array($parent_id));
        $parent_name = $adb->query_result($parent_result,0,"visitreportname");
        //if(strlen($parent_name) > 25)
        //{
            $parent_name = textlength_check($parent_name);
        //}
    }
    //crmv@vistreport e
    //added by rdhital for better emails - Raju
    if ($parent_module == 'Multiple')
    {
        $parent_value = $parent_name;
    }
    else
    {
        $parent_value = $module_icon."<a href='index.php?module=".$parent_module."&action=".$action."&record=".$parent_id."&parenttab=".$tabname."'>".textlength_check($parent_name)."</a>";
    }
    //code added by raju ends
    $log->debug("Exiting getRelatedTo method ...");
        return $parent_value;



}

/**Function to get the table headers for a listview
*Param $navigation_arrray - navigation values in array
*Param $url_qry - url string
*Param $module - module name
*Param $action- action file name
*Param $viewid - view id
*Returns an string value
*/


function getTableHeaderNavigation($navigation_array, $url_qry,$module='',$action_val='index',$viewid='')
{
    global $log,$app_strings;
    $log->debug("Entering getTableHeaderNavigation(".$navigation_array.",". $url_qry.",".$module.",".$action_val.",".$viewid.") method ...");
    global $theme,$current_user;
    $theme_path="themes/".$theme."/";
    $image_path=$theme_path."images/";

    //vtc
    if($module != 'Documents')
		$output = '<td align="right" style="padding="5px;">';
	else
		$output = '';
    //vtc e

    $tabname = getParentTab();

    //echo '<pre>';print_r($_REQUEST);echo '</pre>';
    /*    //commented due to usablity conflict -- Philip
    $output .= '<a href="index.php?module='.$module.'&action='.$action_val.$url_qry.'&start=1&viewname='.$viewid.'&allflag='.$navigation_array['allflag'].'" >'.$navigation_array['allflag'].'</a>&nbsp;';
     */

    	// vtlib Customization : For uitype 10 popup during paging
	if($_REQUEST['form'] == 'vtlibPopupView') {
		$url_string .= '&form=vtlibPopupView&forfield='.vtlib_purify($_REQUEST['forfield']).'&srcmodule='.vtlib_purify($_REQUEST['srcmodule']).'&forrecord='.vtlib_purify($_REQUEST['forrecord']);
	}
	// END

        $url_string = '';
    if($module == 'Calendar' && $action_val == 'index')
    {
        if($_REQUEST['view'] == ''){
            if($current_user->activity_view == "This Year"){
                $mysel = 'year';
            }else if($current_user->activity_view == "This Month"){
                $mysel = 'month';
            }else if($current_user->activity_view == "This Week"){
                $mysel = 'week';
            }else{
                $mysel = 'day';
            }
        }
        $data_value=date('Y-m-d H:i:s');
        preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/',$data_value,$value);
        $date_data = Array(
            'day'=>$value[3],
            'month'=>$value[2],
            'year'=>$value[1],
            'hour'=>$value[4],
            'min'=>$value[5],
        );
        $tab_type = ($_REQUEST['subtab'] == '')?'event':$_REQUEST['subtab'];
        $url_string .= isset($_REQUEST['view'])?"&view=".$_REQUEST['view']:"&view=".$mysel;
        $url_string .= isset($_REQUEST['subtab'])?"&subtab=".$_REQUEST['subtab']:'';
        $url_string .= isset($_REQUEST['viewOption'])?"&viewOption=".$_REQUEST['viewOption']:'&viewOption=listview';
        $url_string .= isset($_REQUEST['day'])?"&day=".$_REQUEST['day']:'&day='.$date_data['day'];
        $url_string .= isset($_REQUEST['week'])?"&week=".$_REQUEST['week']:'';
        $url_string .= isset($_REQUEST['month'])?"&month=".$_REQUEST['month']:'&month='.$date_data['month'];
        $url_string .= isset($_REQUEST['year'])?"&year=".$_REQUEST['year']:"&year=".$date_data['year'];
        $url_string .= isset($_REQUEST['n_type'])?"&n_type=".$_REQUEST['n_type']:'';
        $url_string .= isset($_REQUEST['search_option'])?"&search_option=".$_REQUEST['search_option']:'';
    }
    if($module == 'Calendar' && $action_val != 'index') //added for the All link from the homepage -- ticket 5211
        $url_string .= isset($_REQUEST['from_homepage'])?"&from_homepage=".$_REQUEST['from_homepage']:'';

    if(($navigation_array['prev']) != 0)
    {
        if($module == 'Calendar' && $action_val == 'index')
        {
            //$output .= '<a href="index.php?module=Calendar&action=index&start=1'.$url_string.'" alt="'.$app_strings['LBL_FIRST'].'" title="'.$app_strings['LBL_FIRST'].'"><img src="'.$image_path.'start.gif" border="0" align="absmiddle"></a>&nbsp;';
            $output .= '<a href="javascript:;" onClick="cal_navigation(\''.$tab_type.'\',\''.$url_string.'\',\'&start=1\');" alt="'.$app_strings['LBL_FIRST'].'" title="'.$app_strings['LBL_FIRST'].'"><img src="'.$image_path.'start.gif" border="0" align="absmiddle"></a>&nbsp;';
            //$output .= '<a href="index.php?module=Calendar&action=index&start='.$navigation_array['prev'].$url_string.'" alt="'.$app_strings['LNK_LIST_PREVIOUS'].'"title="'.$app_strings['LNK_LIST_PREVIOUS'].'"><img src="'.$image_path.'previous.gif" border="0" align="absmiddle"></a>&nbsp;';
            $output .= '<a href="javascript:;" onClick="cal_navigation(\''.$tab_type.'\',\''.$url_string.'\',\'&start='.$navigation_array['prev'].'\');" alt="'.$app_strings['LBL_FIRST'].'" title="'.$app_strings['LBL_FIRST'].'"><img src="'.$image_path.'start.gif" border="0" align="absmiddle"></a>&nbsp;';
        }
        //crmv@8719
    		else if($action_val == "FindDuplicate")
			{
				$output .= '<a href="javascript:;" onClick="getDuplicateListViewEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start=1'.$url_string.'\');" alt="'.$app_strings['LBL_FIRST'].'" title="'.$app_strings['LBL_FIRST'].'"><img src="' . vtiger_imageurl('start.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
				$output .= '<a href="javascript:;" onClick="getDuplicateListViewEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start='.$navigation_array['prev'].$url_string.'\');" alt="'.$app_strings['LNK_LIST_PREVIOUS'].'"title="'.$app_strings['LNK_LIST_PREVIOUS'].'"><img src="' . vtiger_imageurl('previous.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
			}
		//crmv@8719e
		//vtc
		elseif($module == 'Documents')
		{
			$output .= '<a href="javascript:;" onClick="getListViewEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start=1'.$url_string.'\');" alt="'.$app_strings['LBL_FIRST'].'" title="'.$app_strings['LBL_FIRST'].'"><img src="'.$image_path.'start.gif" border="0" align="absmiddle"></a> ';
			$output .= '<a href="javascript:;" onClick="getListViewEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start='.$navigation_array['prev'].$url_string.'&folderid='.$action_val.'\');" alt="'.$app_strings['LNK_LIST_PREVIOUS'].'"title="'.$app_strings['LNK_LIST_PREVIOUS'].'"><img src="'.$image_path.'previous.gif" border="0" align="absmiddle"></a> ';
		}
		//vtc e
        else{
            $output .= '<a href="javascript:;" onClick="getListViewEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start=1'.$url_string.'\');" alt="'.$app_strings['LBL_FIRST'].'" title="'.$app_strings['LBL_FIRST'].'"><img src="'.$image_path.'start.gif" border="0" align="absmiddle"></a>&nbsp;';
            $output .= '<a href="javascript:;" onClick="getListViewEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start='.$navigation_array['prev'].$url_string.'\');" alt="'.$app_strings['LNK_LIST_PREVIOUS'].'"title="'.$app_strings['LNK_LIST_PREVIOUS'].'"><img src="'.$image_path.'previous.gif" border="0" align="absmiddle"></a>&nbsp;';
        }
    }
    else
    {
        $output .= '<img src="'.$image_path.'start_disabled.gif" border="0" align="absmiddle">&nbsp;';
        $output .= '<img src="'.$image_path.'previous_disabled.gif" border="0" align="absmiddle">&nbsp;';
    }
    for ($i=$navigation_array['first'];$i<=$navigation_array['end'];$i++){
        if ($navigation_array['current']==$i){
            $output .='<b>'.$i.'</b>&nbsp;';
        }
        else{
            if($module == 'Calendar' && $action_val == 'index')
            {
                //$output .= '<a href="index.php?module=Calendar&action=index&start='.$i.$url_string.'">'.$i.'</a>&nbsp;';
                $output .= '<a href="javascript:;" onClick="cal_navigation(\''.$tab_type.'\',\''.$url_string.'\',\'&start='.$i.'\');" >'.$i.'</a>&nbsp;';
            }
            //crmv@8719
            else if($action_val == "FindDuplicate")
					$output .= '<a href="javascript:;" onClick="getDuplicateListViewEntries_js(\''.$module.'\',\'start='.$i.$url_string.'\');" >'.$i.'</a>&nbsp;';
				//crmv@8719e
			//vtc
			elseif($module == 'Documents')
			{
				$output .= '<a href="javascript:;" onClick="getListViewEntries_js(\''.$module.'\',\'start='.$i.$url_string.'&folderid='.$action_val.'\');" >'.$i.'</a> ';
			}
			//vtc e
            else
                $output .= '<a href="javascript:;" onClick="getListViewEntries_js(\''.$module.'\',\'start='.$i.$url_string.'\');" >'.$i.'</a>&nbsp;';
        }
    }
    if(($navigation_array['next']) !=0)
    {
        if($module == 'Calendar' && $action_val == 'index')
                {
            //$output .= '<a href="index.php?module=Calendar&action=index&start='.$navigation_array['next'].$url_string.'" alt="'.$app_strings['LNK_LIST_NEXT'].'" title="'.$app_strings['LNK_LIST_NEXT'].'"><img src="'.$image_path.'next.gif" border="0" align="absmiddle"></a>&nbsp;';
            $output .= '<a href="javascript:;" onClick="cal_navigation(\''.$tab_type.'\',\''.$url_string.'\',\'&start='.$navigation_array['next'].'\');" alt="'.$app_strings['LNK_LIST_NEXT'].'" title="'.$app_strings['LNK_LIST_NEXT'].'"><img src="'.$image_path.'next.gif" border="0" align="absmiddle"></a>&nbsp;';
            //$output .= '<a href="index.php?module=Calendar&action=index&start='.$navigation_array['verylast'].$url_string.'" alt="'.$app_strings['LBL_LAST'].'" title="'.$app_strings['LBL_LAST'].'"><img src="'.$image_path.'end.gif" border="0" align="absmiddle"></a>&nbsp;';
            $output .= '<a href="javascript:;" onClick="cal_navigation(\''.$tab_type.'\',\''.$url_string.'\',\'&start='.$navigation_array['verylast'].'\');" alt="'.$app_strings['LBL_LAST'].'" title="'.$app_strings['LBL_LAST'].'"><img src="'.$image_path.'end.gif" border="0" align="absmiddle"></a>&nbsp;';
        }
        //crmv@8719
    		else if($action_val == "FindDuplicate")
			{
				$output .= '<a href="javascript:;" onClick="getDuplicateListViewEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start='.$navigation_array['next'].$url_string.'\');" alt="'.$app_strings['LNK_LIST_NEXT'].'" title="'.$app_strings['LNK_LIST_NEXT'].'"><img src="' . vtiger_imageurl('next.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
				$output .= '<a href="javascript:;" onClick="getDuplicateListViewEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start='.$navigation_array['verylast'].$url_string.'\');" alt="'.$app_strings['LBL_LAST'].'" title="'.$app_strings['LBL_LAST'].'"><img src="' . vtiger_imageurl('end.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
			}
		//crmv@8719e
		//vtc
		elseif($module == 'Documents')
		{
			$output .= '<a href="javascript:;" onClick="getListViewEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start='.$navigation_array['next'].$url_string.'&folderid='.$action_val.'\');" alt="'.$app_strings['LNK_LIST_NEXT'].'" title="'.$app_strings['LNK_LIST_NEXT'].'"><img src="'.$image_path.'next.gif" border="0" align="absmiddle"></a> ';
			$output .= '<a href="javascript:;" onClick="getListViewEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start='.$navigation_array['verylast'].$url_string.'&folderid='.$action_val.'\');" alt="'.$app_strings['LBL_LAST'].'" title="'.$app_strings['LBL_LAST'].'"><img src="'.$image_path.'end.gif" border="0" align="absmiddle"></a> ';
		}
		//vtc e
        else
        {
            $output .= '<a href="javascript:;" onClick="getListViewEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start='.$navigation_array['next'].$url_string.'\');" alt="'.$app_strings['LNK_LIST_NEXT'].'" title="'.$app_strings['LNK_LIST_NEXT'].'"><img src="'.$image_path.'next.gif" border="0" align="absmiddle"></a>&nbsp;';
            $output .= '<a href="javascript:;" onClick="getListViewEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start='.$navigation_array['verylast'].$url_string.'\');" alt="'.$app_strings['LBL_LAST'].'" title="'.$app_strings['LBL_LAST'].'"><img src="'.$image_path.'end.gif" border="0" align="absmiddle"></a>&nbsp;';
        }
    }
    else
    {
        $output .= '<img src="'.$image_path.'next_disabled.gif" border="0" align="absmiddle">&nbsp;';
        $output .= '<img src="'.$image_path.'end_disabled.gif" border="0" align="absmiddle">&nbsp;';
    }
    //vtc
    if($module != 'Documents')
		$output .= '</td>';
    //vtc e
    $log->debug("Exiting getTableHeaderNavigation method ...");
    if($navigation_array['first']=='')
    return '';
    else
    return $output;
}

function getPopupCheckquery($current_module,$relmodule,$relmod_recordid)
{
    global $log,$adb, $table_prefix;
    $log->debug("Entering getPopupCheckquery(".$currentmodule.",".$relmodule.",".$relmod_recordid.") method ...");
    if($current_module == "Contacts")
    {
        if($relmodule == "Accounts" && $relmod_recordid != '')
            $condition = "and ".$table_prefix."_account.accountid= ".$relmod_recordid;

        elseif($relmodule == "Potentials")
        {
            $query = "select contactid from ".$table_prefix."_contpotentialrel where potentialid=?";
            $result = $adb->pquery($query, array($relmod_recordid));
                    $contact_id = $adb->query_result($result,0,"contactid");
            if($contact_id != '' && $contact_id != 0)
                $condition = "and ".$table_prefix."_contactdetails.contactid= ".$contact_id;
            else
            {
            	$query = "select related_to from ".$table_prefix."_potential where potentialid=?";
				$result = $adb->pquery($query, array($relmod_recordid));
				$acc_id = $adb->query_result($result,0,"related_to");
				if($acc_id != ''){
					$condition = "and ".$table_prefix."_contactdetails.accountid= ".$acc_id;
				}
            }
        }
        elseif($relmodule == "Quotes")
        {

            $query = "select accountid,contactid from ".$table_prefix."_quotes where quoteid=?";
            $result = $adb->pquery($query, array($relmod_recordid));
            $contactid = $adb->query_result($result,0,"contactid");
            if($contactid != '' && $contactid != 0)
                $condition = "and ".$table_prefix."_contactdetails.contactid= ".$contactid;
            else
            {
                $account_id = $adb->query_result($result,0,"accountid");
                if($account_id != '')
                    $condition = "and ".$table_prefix."_contactdetails.accountid= ".$account_id;
            }
        }
        elseif($relmodule == "PurchaseOrder")
        {
            $query = "select contactid from ".$table_prefix."_purchaseorder where purchaseorderid=?";
            $result = $adb->pquery($query, array($relmod_recordid));
            $contact_id = $adb->query_result($result,0,"contactid");
            if($contact_id != '')
                $condition = "and ".$table_prefix."_contactdetails.contactid= ".$contact_id;
            else
                $condition = "and ".$table_prefix."_contactdetails.contactid= 0";
        }
        elseif($relmodule == "SalesOrder")
        {
            $query = "select accountid,contactid from ".$table_prefix."_salesorder where salesorderid=?";
            $result = $adb->pquery($query, array($relmod_recordid));
            $contact_id = $adb->query_result($result,0,"contactid");
            if($contact_id != 0 && $contact_id != '')
                $condition =  "and ".$table_prefix."_contactdetails.contactid=".$contact_id;
            else
            {
                $account_id = $adb->query_result($result,0,"accountid");
                if($account_id != '')
                    $condition = "and ".$table_prefix."_contactdetails.accountid= ".$account_id;
            }
        }
        elseif($relmodule == "Invoice")
        {
            $query = "select accountid,contactid from ".$table_prefix."_invoice where invoiceid=?";
            $result = $adb->pquery($query, array($relmod_recordid));
            $contact_id = $adb->query_result($result,0,"contactid");
            if($contact_id != '' && $contact_id != 0)
                $condition =  " and ".$table_prefix."_contactdetails.contactid=".$contact_id;
            else
            {
                $account_id = $adb->query_result($result,0,"accountid");
                if($account_id != '')
                    $condition =  " and ".$table_prefix."_contactdetails.accountid=".$account_id;
            }
        }
        elseif($relmodule == "Campaigns")
        {
            $query = "select contactid from ".$table_prefix."_campaigncontrel where campaignid =?";
            $result = $adb->pquery($query, array($relmod_recordid));
            $rows = $adb->num_rows($result);
            if($rows != 0)
            {
                $j = 0;
                $contactid_comma = "(";
                for($k=0; $k < $rows; $k++)
                {
                    $contactid = $adb->query_result($result,$k,'contactid');
                    $contactid_comma.=$contactid;
                    if($k < ($rows-1))
                        $contactid_comma.=', ';
                }
                $contactid_comma.= ")";
            }
            else
                $contactid_comma = "(0)";
            $condition = "and ".$table_prefix."_contactdetails.contactid in ".$contactid_comma;
        }
        elseif($relmodule == "Products")
        {
            $query = "select crmid from ".$table_prefix."_seproductsrel where productid=? and setype=?";
            $result = $adb->pquery($query, array($relmod_recordid,"Contacts"));
            $rows = $adb->num_rows($result);
            if($rows != 0)
            {
                $j = 0;
                $contactid_comma = "(";
                for($k=0; $k < $rows; $k++)
                {
                    $contactid = $adb->query_result($result,$k,'crmid');
                    $contactid_comma.=$contactid;
                    if($k < ($rows-1))
                        $contactid_comma.=', ';
                }
                $contactid_comma.= ")";
            }
            else
                $contactid_comma = "(0)";
            $condition = "and ".$table_prefix."_contactdetails.contactid in ".$contactid_comma;
        }
        elseif($relmodule == "HelpDesk" || $relmodule == "Trouble Tickets")
        {
            $query = "select parent_id from ".$table_prefix."_troubletickets where ticketid =?";
            $result = $adb->pquery($query, array($relmod_recordid));
            $parent_id = $adb->query_result($result,0,"parent_id");
            if($parent_id != ""){
                $crmquery = "select setype from ".$table_prefix."_crmentity where crmid=?";
                $parentmodule_id = $adb->pquery($crmquery, array($parent_id));
                $parent_modname = $adb->query_result($parentmodule_id,0,"setype");
                if($parent_modname == "Accounts")
                    $condition = "and ".$table_prefix."_contactdetails.accountid= ".$parent_id;
                if($parent_modname == "Contacts")
                    $condition = "and ".$table_prefix."_contactdetails.contactid= ".$parent_id;
            }
            else
                $condition = " and ".$table_prefix."_contactdetails.contactid=0";

        }
    }
    elseif($current_module == "Potentials")
    {
        if($relmodule == 'Accounts')
        {
            $pot_query = "select ".$table_prefix."_crmentity.crmid,".$table_prefix."_account.accountid,".$table_prefix."_potential.potentialid from ".$table_prefix."_potential inner join ".$table_prefix."_account on ".$table_prefix."_account.accountid=".$table_prefix."_potential.related_to inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=".$table_prefix."_account.accountid where ".$table_prefix."_crmentity.deleted=0 and ".$table_prefix."_potential.related_to=?";	//crmv@fix
            $pot_result = $result = $adb->pquery($pot_query, array($relmod_recordid));
            $rows = $adb->num_rows($pot_result);
            $potids_comma = "";
            if($rows != 0)
            {
                $j = 0;
                $potids_comma .= "(";
                for($k=0; $k < $rows; $k++)
                {
                    $potential_ids = $adb->query_result($pot_result,$k,'potentialid');
                    $potids_comma.=$potential_ids;
                    if($k < ($rows-1))
                        $potids_comma.=',';
                }
                $potids_comma.= ")";
            }
            else
                $potids_comma = "(0)";
            $condition ="and ".$table_prefix."_potential.potentialid in ".$potids_comma;
        }

    }
    else if($current_module == "Products")
    {
        if($relmodule == 'Accounts')
        {
            $pro_query = "select productid from ".$table_prefix."_seproductsrel where setype='Accounts' and crmid=?";
            $pro_result = $result = $adb->pquery($pro_query, array($relmod_recordid));
            $rows = $adb->num_rows($pro_result);
            if($rows != 0)
            {
                $proids_comma = "(";
                for($k=0; $k < $rows; $k++)
                {
                    $product_ids = $adb->query_result($pro_result,$k,'productid');
                    $proids_comma .= $product_ids;
                    if($k < ($rows-1))
                        $proids_comma.=',';
                }
                $proids_comma.= ")";
            }
            else
                $proids_comma = "(0)";
            $condition ="and ".$table_prefix."_products.productid in ".$proids_comma;
        }
    }
    else if($current_module == 'Quotes')
    {
        if($relmodule == 'Accounts')
        {
            $quote_query = "select quoteid from ".$table_prefix."_quotes where accountid=?";
            $quote_result = $result = $adb->pquery($quote_query, array($relmod_recordid));
            $rows = $adb->num_rows($quote_result);
            if($rows != 0)
            {
                $j = 0;
                $qtids_comma = "(";
                for($k=0; $k < $rows; $k++)
                {
                    $quote_ids = $adb->query_result($quote_result,$k,'quoteid');
                    $qtids_comma.=$quote_ids;
                    if($k < ($rows-1))
                        $qtids_comma.=',';
                }
                $qtids_comma.= ")";
            }
            else
                $qtids_comma = "(0)";
            $condition ="and ".$table_prefix."_quotes.quoteid in ".$qtids_comma;
        }

    }
    else if($current_module == 'SalesOrder')
    {
        if($relmodule == 'Accounts')
        {
            $SO_query = "select salesorderid from ".$table_prefix."_salesorder where accountid=?";
            $SO_result = $result = $adb->pquery($SO_query, array($relmod_recordid));
            $rows = $adb->num_rows($SO_result);
            if($rows != 0)
            {
                $SOids_comma = "(";
                for($k=0; $k < $rows; $k++)
                {
                    $SO_ids = $adb->query_result($SO_result,$k,'salesorderid');
                    $SOids_comma.=$SO_ids;
                    if($k < ($rows-1))
                        $SOids_comma.=',';
                }
                $SOids_comma.= ")";
            }
            else
                $SOids_comma = "(0)";
            $condition ="and ".$table_prefix."_salesorder.salesorderid in ".$SOids_comma;
        }
	//crmv@22700
	} elseif ($current_module == 'Targets' && $relmodule == 'Targets') {
		$target = CRMEntity::getInstance($relmodule);
		$target->id = $relmod_recordid;
		$target->retrieve_entity_info($relmod_recordid, $relmodule);
		$fathers = $target->getFathers(true);
		if (!empty($fathers)) {
			$condition .=" and ".$table_prefix."_targets.targetsid not in (".implode(',',$fathers).")";
		}
		$children = $target->getChildren();
		if (!empty($children)) {
			$condition .=" and ".$table_prefix."_targets.targetsid not in (".implode(',',$children).")";	//tolgo anche gli elementi gi� collegati
		}
    //crmv@22700e
    }
    else
        $condition = '';
    $where = $condition;
    $log->debug("Exiting getPopupCheckquery method ...");
    return $where;


}

/**This function return the entity ids that need to be excluded in popup listview for a given record
Param $currentmodule - modulename of the entity to be selected
Param $returnmodule - modulename for which the entity is assingned
Param $recordid - the record id for which the entity is assigned
Return type string.
*/

function getRelCheckquery($currentmodule,$returnmodule,$recordid)
{
    global $log,$adb, $table_prefix;
    $log->debug("Entering getRelCheckquery(".$currentmodule.",".$returnmodule.",".$recordid.") method ...");
    $skip_id = Array();
    $where_relquery = "";
    $params = array();
    if($currentmodule=="Contacts" && $returnmodule == "Potentials")
    {
        $reltable = $table_prefix.'_contpotentialrel';
        $condition = 'WHERE potentialid = ?';
        array_push($params, $recordid);
        $field = $selectfield = 'contactid';
        $table = $table_prefix.'_contactdetails';
    }
    elseif($currentmodule=="Contacts" && $returnmodule == "Vendors")
    {
        $reltable = $table_prefix.'_vendorcontactrel';
        $condition = 'WHERE vendorid = ?';
        array_push($params, $recordid);
        $field = $selectfield = 'contactid';
        $table = $table_prefix.'_contactdetails';
    }
    elseif($currentmodule=="Contacts" && $returnmodule == "Campaigns")
    {
        $reltable = $table_prefix.'_campaigncontrel';
        $condition = 'WHERE campaignid = ?';
        array_push($params, $recordid);
        $field = $selectfield = 'contactid';
        $table = $table_prefix.'_contactdetails';
    }
    elseif($currentmodule=="Contacts" && $returnmodule == "Calendar")
    {
        $reltable = $table_prefix.'_cntactivityrel';
        $condition = 'WHERE activityid = ?';
        array_push($params, $recordid);
        $field = $selectfield = 'contactid';
        $table = $table_prefix.'_contactdetails';
    }
    elseif($currentmodule=="Leads" && $returnmodule == "Campaigns")
    {
        $reltable = $table_prefix.'_campaignleadrel';
        $condition = 'WHERE campaignid = ?';
        array_push($params, $recordid);
        $field = $selectfield = 'leadid';
        $table = $table_prefix.'_leaddetails';
    }
    elseif($currentmodule=="Accounts" && $returnmodule == "Campaigns")
    {
        $reltable = $table_prefix.'_campaignaccountrel';
        $condition = 'WHERE campaignid = ?';
        array_push($params, $recordid);
        $field = $selectfield = 'accountid';
        $table = $table_prefix.'_account';
    }
    elseif($currentmodule=="Users" && $returnmodule == "Calendar")
    {
        $reltable = $table_prefix.'_salesmanactivityrel';
        $condition = 'WHERE activityid = ?';
        array_push($params, $recordid);
        $selectfield = 'smid';
        $field = 'id';
        $table = $table_prefix.'_users';
    }
    elseif($currentmodule=="Campaigns" && $returnmodule == "Leads")
    {
        $reltable = $table_prefix.'_campaignleadrel';
        $condition = 'WHERE leadid = ?';
        array_push($params, $recordid);
        $field = $selectfield = 'campaignid';
        $table = $table_prefix.'_campaign';
    }
    elseif($currentmodule=="Campaigns" && $returnmodule == "Contacts")
    {
        $reltable = $table_prefix.'_campaigncontrel';
        $condition = 'WHERE contactid = ?';
        array_push($params, $recordid);
        $field = $selectfield = 'campaignid';
        $table = $table_prefix.'_campaign';
    }
    elseif($currentmodule=="Campaigns" && $returnmodule == "Accounts")
    {
        $reltable = $table_prefix.'_campaignaccountrel';
        $condition = 'WHERE accountid = ?';
        array_push($params, $recordid);
        $field = $selectfield = 'campaignid';
        $table = $table_prefix.'_campaign';
    }
    elseif($currentmodule == "Products" && ($returnmodule == "Potentials" || $returnmodule == "Accounts" || $returnmodule == "Contacts" || $returnmodule == "Leads"))
    {
        $reltable = $table_prefix.'_seproductsrel';
        $condition = 'WHERE crmid = ? and setype = ?';
        array_push($params, $recordid, $returnmodule);
        $field = $selectfield ='productid';
        $table = $table_prefix.'_products';
    }
    elseif(($currentmodule == "Leads" || $currentmodule == "Accounts" || $currentmodule == "Potentials" || $currentmodule == "Contacts") && $returnmodule == "Products")//added to fix the issues(ticket 4001,4002 and 4003)
    {
        $reltable = $table_prefix.'_seproductsrel';
        $condition = 'WHERE productid = ? and setype = ?';
        array_push($params, $recordid, $currentmodule);
        $selectfield ='crmid';
        if($currentmodule == "Leads")
        {
            $field = 'leadid';
            $table = $table_prefix.'_leaddetails';
        }
        elseif($currentmodule == "Accounts")
        {
            $field = 'accountid';
            $table = $table_prefix.'_account';
        }
        elseif($currentmodule == "Contacts")
        {
            $field = 'contactid';
            $table = $table_prefix.'_contactdetails';
        }
        elseif($currentmodule == "Potentials")
        {
            $field = 'potentialid';
            $table = $table_prefix.'_potential';
        }
    }
    elseif($currentmodule == "Products" && $returnmodule =="Vendors")
    {
        $reltable = $table_prefix.'_products';
        $condition = 'WHERE vendor_id = ?';
        array_push($params, $recordid);
        $field = $selectfield ='productid';
        $table = $table_prefix.'_products';
    }
	elseif($currentmodule == "Documents")
	{
		$reltable = $table_prefix."_senotesrel";
		$selectfield = "notesid";
		$condition = "where crmid = ?";
		array_push($params, $recordid);
		$table = $table_prefix."_notes";
		$field = "notesid";
	}
	//end
	if($reltable != null) {
		$query = "SELECT ".$selectfield." FROM ".$reltable." ".$condition;
	} elseif($currentmodule != $returnmodule && $returnmodule!="") { // If none of the above relation matches, then the relation is assumed to be stored in vtiger_crmentityrel
		$query = "SELECT relcrmid AS relatedid FROM ".$table_prefix."_crmentityrel WHERE  crmid = ? and module = ? and relmodule = ?
					UNION SELECT crmid AS relatedid FROM ".$table_prefix."_crmentityrel WHERE relcrmid = ? and relmodule = ? and module = ?";
		array_push($params, $recordid, $returnmodule, $currentmodule, $recordid, $returnmodule, $currentmodule);

		$focus_obj = CRMEntity::getInstance($currentmodule);
		$field = $focus_obj->table_index;
		$table = $focus_obj->table_name;
		$selectfield = 'relatedid';
	}

    if($query !='')
    {
        $result = $adb->pquery($query, $params);
        if($adb->num_rows($result)!=0)
        {
            for($k=0;$k < $adb->num_rows($result);$k++)
            {
                $skip_id[]=$adb->query_result($result,$k,$selectfield);
            }
            $skipids = implode(",", constructList($skip_id,'INTEGER'));
            $where_relquery = "and ".$table.".".$field." not in (". $skipids .")";
        }
    }
    $log->debug("Exiting getRelCheckquery method ...");
    return $where_relquery;
}

/**This function stores the variables in session sent in list view url string.
*Param $lv_array - list view session array
*Param $noofrows - no of rows
*Param $max_ent - maximum entires
*Param $module - module name
*Param $related - related module
*Return type void.
*/

function setSessionVar($lv_array,$noofrows,$max_ent,$module='',$related='')
{
    $start = '';
    if($noofrows>=1)
    {
        $lv_array['start']=1;
        $start = 1;
    }
    elseif($related!='' && $noofrows == 0)
    {
            $lv_array['start']=1;
            $start = 1;
    }
    else
    {
        $lv_array['start']=0;
        $start = 0;
    }

    if(isset($_REQUEST['start']) && $_REQUEST['start'] !='')
    {
        $lv_array['start']=$_REQUEST['start'];
        $start = $_REQUEST['start'];
    }elseif($_SESSION['rlvs'][$module][$related]['start'] != '')
    {

        if($related!='')
        {
            $lv_array['start']=$_SESSION['rlvs'][$module][$related]['start'];
            $start = $_SESSION['rlvs'][$module][$related]['start'];
        }
    }
    if(isset($_REQUEST['viewname']) && $_REQUEST['viewname'] !='')
        $lv_array['viewname']=$_REQUEST['viewname'];

    if($related=='')
        $_SESSION['lvs'][$_REQUEST['module']]=$lv_array;
    else
        $_SESSION['rlvs'][$module][$related] = $lv_array;

    if ($start < ceil ($noofrows / $max_ent) && $start !='')
    {
        $start = ceil ($noofrows / $max_ent);
        if($related=='')
            $_SESSION['lvs'][$currentModule]['start'] = $start;
    }
}

/**Function to get the table headers for related listview
*Param $navigation_arrray - navigation values in array
*Param $url_qry - url string
*Param $module - module name
*Param $action- action file name
*Param $viewid - view id
*Returns an string value
*/

//Temp function to be be deleted
function getRelatedTableHeaderNavigation($navigation_array, $url_qry,$module,$related_module,
		$recordid) {
	global $log, $app_strings, $adb, $table_prefix;
	$log->debug("Entering getTableHeaderNavigation(".$navigation_array.",". $url_qry.",".$module.",".$action_val.",".$viewid.") method ...");
	global $theme;
	$relatedTabId = getTabid($related_module);
	$tabid = getTabid($module);

	$relatedListResult = $adb->pquery('SELECT * FROM '.$table_prefix.'_relatedlists WHERE tabid=? AND related_tabid=?', array($tabid,$relatedTabId));
	//crmv@30219
	global $relationId;
	if ($relatedListResult && $adb->num_rows($relatedListResult) > 1) {
		$relatedListResult = $adb->pquery('SELECT * FROM '.$table_prefix.'_relatedlists WHERE tabid=? AND related_tabid=? AND relation_id=?', array($tabid,$relatedTabId,$relationId));
	}
	//crmv@30219e
	if(empty($relatedListResult)) return;
	$relatedListRow = $adb->fetch_row($relatedListResult);
	$header = $relatedListRow['label'];
	$actions = $relatedListRow['actions'];
	$functionName = $relatedListRow['name'];

	$urldata = "module=$module&action={$module}Ajax&file=DetailViewAjax&record={$recordid}&".
	"ajxaction=LOADRELATEDLIST&header={$header}&relation_id={$relatedListRow['relation_id']}".
	"&actions={$actions}&{$url_qry}";

	$formattedHeader = str_replace(' ','',$header);
	$target = 'tbl_'.$module.'_'.$formattedHeader;
	$imagesuffix = $module.'_'.$formattedHeader;

//	$output = '<td align="right" style="padding="5px;">';
	if(($navigation_array['prev']) != 0) {
		$output .= '<a href="javascript:;" onClick="loadRelatedListBlock(\''. $urldata.'&start=1\',\''. $target.'\',\''. $imagesuffix.'\');" alt="'.$app_strings['LBL_FIRST'].'" title="'.$app_strings['LBL_FIRST'].'"><img src="' . vtiger_imageurl('start.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
		$output .= '<a href="javascript:;" onClick="loadRelatedListBlock(\''. $urldata.'&start='.$navigation_array['prev'].'\',\''. $target.'\',\''. $imagesuffix.'\');" alt="'.$app_strings['LNK_LIST_PREVIOUS'].'"title="'.$app_strings['LNK_LIST_PREVIOUS'].'"><img src="' . vtiger_imageurl('previous.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
	} else {
		$output .= '<img src="' . vtiger_imageurl('start_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
		$output .= '<img src="' . vtiger_imageurl('previous_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
	}

	$jsHandler = "return VT_disableFormSubmit(event);";
	$output .= "<input class='small' name='pagenum' type='text' value='{$navigation_array['current']}'
		style='width: 3em;margin-right: 0.7em;' onchange=\"loadRelatedListBlock('{$urldata}&start='+this.value+'','{$target}','{$imagesuffix}');\"
		onkeypress=\"$jsHandler\">";
	$output .= "<span name='listViewCountContainerName' class='small' style='white-space: nowrap;'>";
	$computeCount = $_REQUEST['withCount'];
//	if(PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false) === true
//			|| ((boolean) $computeCount) == true){
		$output .= $app_strings['LBL_LIST_OF'].' '.$navigation_array['verylast'];
//	}else{
//		$output .= "<img src='".vtiger_imageurl('windowRefresh.gif',$theme)."' alt='".$app_strings['LBL_HOME_COUNT']."'
//			onclick=\"loadRelatedListBlock('{$urldata}&withCount=true&start={$navigation_array['current']}','{$target}','{$imagesuffix}');\"
//			align='absmiddle' name='".$module."_listViewCountRefreshIcon'/>
//			<img name='".$module."_listViewCountContainerBusy' src='".vtiger_imageurl('vtbusy.gif',$theme)."' style='display: none;'
//			align='absmiddle' alt='".$app_strings['LBL_LOADING']."'>";
//	}
	$output .= '</span>';

	if(($navigation_array['next']) !=0) {
			$output .= '<a href="javascript:;" onClick="loadRelatedListBlock(\''. $urldata.'&start='.$navigation_array['next'].'\',\''. $target.'\',\''. $imagesuffix.'\');"><img src="' . vtiger_imageurl('next.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
			$output .= '<a href="javascript:;" onClick="loadRelatedListBlock(\''. $urldata.'&start='.$navigation_array['verylast'].'\',\''. $target.'\',\''. $imagesuffix.'\');"><img src="' . vtiger_imageurl('end.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
	} else {
		$output .= '<img src="' . vtiger_imageurl('next_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
		$output .= '<img src="' . vtiger_imageurl('end_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
	}
//	$output .= '</td>';
		$log->debug("Exiting getTableHeaderNavigation method ...");
		if($navigation_array['first']=='')
		return;
		else
		return $output;
}

/**    Function to get the Edit link details for ListView and RelatedListView
 *    @param string     $module     - module name
 *    @param int     $entity_id     - record id
 *    @param string     $relatedlist     - string "relatedlist" or may be empty. if empty means ListView else relatedlist
 *    @param string     $returnset     - may be empty in case of ListView. For relatedlists, return_module, return_action and return_id values will be passed like &return_module=Accounts&return_action=CallRelatedList&return_id=10
 *    return string    $edit_link    - url string which cotains the editlink details (module, action, record, etc.,) like index.php?module=Accounts&action=EditView&record=10
 */
function getListViewEditLink($module,$entity_id,$relatedlist,$returnset,$result,$count)
{
    global $adb;
    $return_action = "index";
    $edit_link = "index.php?module=$module&action=EditView&record=$entity_id";
    $tabname = getParentTab();
    //Added to fix 4600
    $url = getBasic_Advance_SearchURL();

    //This is relatedlist listview
    if($relatedlist == 'relatedlist')
    {
        $edit_link .= $returnset;
    }
    else
    {
        if($module == 'Calendar')
        {
            $return_action = "ListView";
            $actvity_type = $adb->query_result($result,$count,'type');
            if($actvity_type == 'Task')
                $edit_link .= '&activity_mode=Task';
            else
                $edit_link .= '&activity_mode=Events';
        }
        $edit_link .= "&return_module=$module&return_action=$return_action";
    }

    $edit_link .= "&parenttab=".$tabname.$url;
    //Appending view name while editing from ListView
    $edit_link .= "&return_viewname=".$_SESSION['lvs'][$module]["viewname"];
    if($module == 'Emails')
            $edit_link = 'javascript:;" onclick="OpenCompose(\''.$entity_id.'\',\'edit\');';
    //crmv@7216
    if($module == 'Fax')
            $edit_link = 'javascript:;" onclick="OpenComposeFax(\''.$entity_id.'\',\'edit\');';
    //crmv@7216e
    //crmv@7217
    if($module == 'Sms')
            $edit_link = 'javascript:;" onclick="OpenComposeSms(\''.$entity_id.'\',\'edit\');';
    //crmv@7217e
    return $edit_link;
}

/**    Function to get the Del link details for ListView and RelatedListView
 *    @param string     $module     - module name
 *    @param int     $entity_id     - record id
 *    @param string     $relatedlist     - string "relatedlist" or may be empty. if empty means ListView else relatedlist
 *    @param string     $returnset     - may be empty in case of ListView. For relatedlists, return_module, return_action and return_id values will be passed like &return_module=Accounts&return_action=CallRelatedList&return_id=10
 *    return string    $del_link    - url string which cotains the editlink details (module, action, record, etc.,) like index.php?module=Accounts&action=Delete&record=10
 */
function getListViewDeleteLink($module,$entity_id,$relatedlist,$returnset)
{
	$tabname = getParentTab();
	$current_module = vtlib_purify($_REQUEST['module']);
	$viewname = $_SESSION['lvs'][$current_module]['viewname'];

	//Added to fix 4600
	$url = getBasic_Advance_SearchURL();

	if($module == "Calendar")
		$return_action = "ListView";
	else
		$return_action = "index";

	//This is added to avoid the del link in Product related list for the following modules
	$avoid_del_links = Array("PurchaseOrder","SalesOrder","Quotes","Invoice");

	if(($current_module == 'Products' || $current_module == 'Services') && in_array($module,$avoid_del_links))
	{
		return '';
	}

	$del_link = "index.php?module=$module&action=Delete&record=$entity_id";

	//This is added for relatedlist listview
	if($relatedlist == 'relatedlist')
	{
		$del_link .= $returnset;
	}
	else
	{
		$del_link .= "&return_module=$module&return_action=$return_action";
	}

	$del_link .= "&parenttab=".$tabname."&return_viewname=".$viewname.$url;

	// vtlib customization: override default delete link for custom modules
	$requestModule = vtlib_purify($_REQUEST['module']);
	$requestRecord = vtlib_purify($_REQUEST['record']);
	$requestAction = vtlib_purify($_REQUEST['action']);
	$parenttab = vtlib_purify($_REQUEST['parenttab']);
	$isCustomModule = vtlib_isCustomModule($requestModule);
	if($requestAction==$requestModule."Ajax"){
		$requestAction=vtlib_purify($_REQUEST['file']);
	}
	if($isCustomModule && !in_array($requestAction, Array('index','ListView'))) {
		$del_link = "index.php?module=$requestModule&action=updateRelations&parentid=$requestRecord";
		$del_link .= "&destination_module=$module&idlist=$entity_id&mode=delete&parenttab=$parenttab";
	}
	// END

	return $del_link;
}

/**    function used to get the account id for the given input account name
 *     @param string $account_name - account name to which we want the id
 *     return int $accountid - accountid for the given account name will be returned
 */
function getAccountId($account_name)
{
    global $log;
    $log->info("in getAccountId ".$account_name);
    global $adb, $table_prefix;
    if($account_name != '')
    {
        // for avoid single quotes error
        //slashes_account_name = popup_from_html($account_name); /* Commented by Asha. Need to see if this is required as Prepared statements is used here*/
        $sql = "select accountid from ".$table_prefix."_account INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_account.accountid where ".$table_prefix."_crmentity.deleted = 0 and ".$table_prefix."_account.accountname=?";
        $result = $adb->pquery($sql, array($account_name));
        $accountid = $adb->query_result($result,0,"accountid");
    }
    return $accountid;
}
function decode_html($str)
{
    global $default_charset;
    if($_REQUEST['action'] == 'Popup' || $_REQUEST['file'] == 'Popup')
        return html_entity_decode($str);
    else
        return html_entity_decode($str,ENT_QUOTES,$default_charset);
}

/**
 * Alternative decoding function which coverts irrespective of $_REQUEST values.
 * Useful incase of Popup (Listview etc...) where if decode_html will not work as expected
 */
function decode_html_force($str) {
	global $default_charset;
	return html_entity_decode($str,ENT_QUOTES,$default_charset);
}

function popup_decode_html($str)
{
    global $default_charset;
    $slashes_str = popup_from_html($str);
    $slashes_str = htmlspecialchars($slashes_str,ENT_QUOTES,$default_charset);
    $slashes_str = str_replace("\n",'\n',$slashes_str);	//crmv@28482
    return decode_html(br2nl($slashes_str));
}

//function added to check the text length in the listview.
function textlength_check($field_val)
{
    global $listview_max_textlength,$default_charset;
     $temp_val = preg_replace("/(<\/?)(\w+)([^>]*>)/i","",$field_val);
	$temp_val = trim(html_entity_decode($temp_val, ENT_QUOTES, $default_charset));
        if(strlen($temp_val) > $listview_max_textlength)
        {
			for ($i=0;$i<strlen($temp_val);$i++){
				if ($i > $listview_max_textlength) break;
				$string .=$temp_val[$i];
			}
			$string.="...";
			$temp_val = htmlspecialchars($string, ENT_QUOTES);
        }
    return $temp_val;
}

//crmv@16208
/**
 * this function accepts a modulename and a fieldname and returns the first related module for it
 * it expects the uitype of the field to be 10
 * @param string $module - the modulename
 * @param string $fieldname - the field name
 * @return string $data - the first related module
 */
function getFirstModule($module, $fieldname){
	global $adb, $table_prefix;
	$sql = "select fieldid, uitype from ".$table_prefix."_field where tabid=? and fieldname=?";
	$result = $adb->pquery($sql, array(getTabid($module), $fieldname));

	if($adb->num_rows($result)>0){
		$uitype = $adb->query_result($result, 0, "uitype");

		if($uitype == 10){
			$fieldid = $adb->query_result($result, 0, "fieldid");
			$sql = "select * from ".$table_prefix."_fieldmodulerel where fieldid=?";
			$result = $adb->pquery($sql, array($fieldid));
			$count = $adb->num_rows($result);

			if($count > 0){
				$data = $adb->query_result($result, 0, "relmodule");
			}
		}
	}
	return $data;
}
//crmv@16208 end
function VT_getSimpleNavigationValues($start,$size,$total){
	$prev = $start -1;
	if($prev < 0){
		$prev = 0;
	}
	if($total === null){
		return array('start'=>$start,'first'=>$start,'current'=>$start,'end'=>$start,'end_val'=>$size,'allflag'=>'All',
			'prev'=>$prev,'next'=>$start+1,'verylast'=>'last');
	}
	if(empty($total)){
		$lastPage = 1;
	}else{
		$lastPage = ceil($total/$size);
	}

	$next = $start+1;
	if($next > $lastPage){
		$next = 0;
	}
	return array('start'=>$start,'first'=>$start,'current'=>$start,'end'=>$start,'end_val'=>$size,'allflag'=>'All',
		'prev'=>$prev,'next'=>$next,'verylast'=>$lastPage);
}

/**Function to get the simplified table headers for a listview
*Param $navigation_arrray - navigation values in array
*Param $url_qry - url string
*Param $module - module name
*Param $action- action file name
*Param $viewid - view id
*Returns an string value
*/

function getTableHeaderSimpleNavigation($navigation_array, $url_qry,$module='',$action_val='index',$viewid=''){
	global $log,$app_strings;
	global $theme,$current_user;
	$theme_path="themes/".$theme."/";
	$image_path=$theme_path."images/";
	if($module == 'Documents') {
		$output = '<td class="mailSubHeader" width="100%" align="center">';
	} else {
//		$output = '<td align="right" style="padding: 5px;">';
	}
	$tabname = getParentTab();

	$url_string = '';
	$url_string.="&last=".$navigation_array['verylast'];
	$url_string.="&noofrows=".$_REQUEST['noofrows'];

	// vtlib Customization : For uitype 10 popup during paging
	if($_REQUEST['form'] == 'vtlibPopupView') {
		$url_string .= '&form=vtlibPopupView&forfield='.vtlib_purify($_REQUEST['forfield']).'&srcmodule='.vtlib_purify($_REQUEST['srcmodule']).'&forrecord='.vtlib_purify($_REQUEST['forrecord']);
	}
	// END

	if($module == 'Calendar' && $action_val == 'index')
	{
		if($_REQUEST['view'] == ''){
			if($current_user->activity_view == "This Year"){
				$mysel = 'year';
			}else if($current_user->activity_view == "This Month"){
				$mysel = 'month';
			}else if($current_user->activity_view == "This Week"){
				$mysel = 'week';
			}else{
				$mysel = 'day';
			}
		}
		$data_value=date('Y-m-d H:i:s');
		preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/',$data_value,$value);
		$date_data = Array(
			'day'=>$value[3],
			'month'=>$value[2],
			'year'=>$value[1],
			'hour'=>$value[4],
			'min'=>$value[5],
		);
		$tab_type = ($_REQUEST['subtab'] == '')?'event':vtlib_purify($_REQUEST['subtab']);
		$url_string .= isset($_REQUEST['view'])?"&view=".vtlib_purify($_REQUEST['view']):"&view=".$mysel;
		$url_string .= isset($_REQUEST['subtab'])?"&subtab=".vtlib_purify($_REQUEST['subtab']):'';
		$url_string .= isset($_REQUEST['viewOption'])?"&viewOption=".vtlib_purify($_REQUEST['viewOption']):'&viewOption=listview';
		$url_string .= isset($_REQUEST['day'])?"&day=".vtlib_purify($_REQUEST['day']):'&day='.$date_data['day'];
		$url_string .= isset($_REQUEST['week'])?"&week=".vtlib_purify($_REQUEST['week']):'';
		$url_string .= isset($_REQUEST['month'])?"&month=".vtlib_purify($_REQUEST['month']):'&month='.$date_data['month'];
		$url_string .= isset($_REQUEST['year'])?"&year=".vtlib_purify($_REQUEST['year']):"&year=".$date_data['year'];
		$url_string .= isset($_REQUEST['n_type'])?"&n_type=".vtlib_purify($_REQUEST['n_type']):'';
		$url_string .= isset($_REQUEST['search_option'])?"&search_option=".vtlib_purify($_REQUEST['search_option']):'';
	}
	if($module == 'Calendar' && $action_val != 'index') //added for the All link from the homepage -- ticket 5211
		$url_string .= isset($_REQUEST['from_homepage'])?"&from_homepage=".vtlib_purify($_REQUEST['from_homepage']):'';

	if(($navigation_array['prev']) != 0){
		if($module == 'Calendar' && $action_val == 'index'){
			$output .= '<a href="javascript:;" onClick="cal_navigation(\''.$tab_type.'\',\''.$url_string.'\',\'&start=1\');" alt="'.$app_strings['LBL_FIRST'].'" title="'.$app_strings['LBL_FIRST'].'"><img src="' . vtiger_imageurl('start.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
			$output .= '<a href="javascript:;" onClick="cal_navigation(\''.$tab_type.'\',\''.$url_string.'\',\'&start='.$navigation_array['prev'].'\');" alt="'.$app_strings['LBL_FIRST'].'" title="'.$app_strings['LBL_FIRST'].'"><img src="' . vtiger_imageurl('start.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
		}else if($action_val == "FindDuplicate"){
			$output .= '<a href="javascript:;" onClick="getDuplicateListViewEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start=1'.$url_string.'\');" alt="'.$app_strings['LBL_FIRST'].'" title="'.$app_strings['LBL_FIRST'].'"><img src="' . vtiger_imageurl('start.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
			$output .= '<a href="javascript:;" onClick="getDuplicateListViewEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start='.$navigation_array['prev'].$url_string.'\');" alt="'.$app_strings['LNK_LIST_PREVIOUS'].'"title="'.$app_strings['LNK_LIST_PREVIOUS'].'"><img src="' . vtiger_imageurl('previous.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
		}elseif($action_val == 'UnifiedSearch'){
			$output .= '<a href="javascript:;" onClick="getUnifiedSearchEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start=1'.$url_string.'\');" alt="'.$app_strings['LBL_FIRST'].'" title="'.$app_strings['LBL_FIRST'].'"><img src="' . vtiger_imageurl('start.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
			$output .= '<a href="javascript:;" onClick="getUnifiedSearchEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start='.$navigation_array['prev'].$url_string.'\');" alt="'.$app_strings['LNK_LIST_PREVIOUS'].'"title="'.$app_strings['LNK_LIST_PREVIOUS'].'"><img src="' . vtiger_imageurl('previous.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
		}elseif($module == 'Documents'){
			$output .= '<a href="javascript:;" onClick="getListViewEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start=1'.$url_string.'\');" alt="'.$app_strings['LBL_FIRST'].'" title="'.$app_strings['LBL_FIRST'].'"><img src="' . vtiger_imageurl('start.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
			$output .= '<a href="javascript:;" onClick="getListViewEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start='.$navigation_array['prev'].$url_string.'&folderid='.$action_val.'\');" alt="'.$app_strings['LNK_LIST_PREVIOUS'].'"title="'.$app_strings['LNK_LIST_PREVIOUS'].'"><img src="' . vtiger_imageurl('previous.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
		}else{
			$output .= '<a href="javascript:;" onClick="getListViewEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start=1'.$url_string.'\');" alt="'.$app_strings['LBL_FIRST'].'" title="'.$app_strings['LBL_FIRST'].'"><img src="' . vtiger_imageurl('start.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
			$output .= '<a href="javascript:;" onClick="getListViewEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start='.$navigation_array['prev'].$url_string.'\');" alt="'.$app_strings['LNK_LIST_PREVIOUS'].'"title="'.$app_strings['LNK_LIST_PREVIOUS'].'"><img src="' . vtiger_imageurl('previous.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
		}
	}else{
		$output .= '<img src="' . vtiger_imageurl('start_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
		$output .= '<img src="' . vtiger_imageurl('previous_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
	}
	if($module == 'Calendar' && $action_val == 'index'){
		$jsNavigate = "cal_navigation('$tab_type','$url_string','&start='+this.value);";
	}else if($action_val == "FindDuplicate"){
		$jsNavigate = "getDuplicateListViewEntries_js('$module','parenttab=$tabname&start='+this.value+'$url_string');";
	}elseif($action_val == 'UnifiedSearch'){
		$jsNavigate = "getUnifiedSearchEntries_js('$module','parenttab=$tabname&start='+this.value+'$url_string');";
	}elseif($module == 'Documents'){
		$jsNavigate = "getListViewEntries_js('$module','parenttab=$tabname&start='+this.value+'$url_string&folderid=$action_val');";
	}else{
		$jsNavigate = "getListViewEntries_js('$module','parenttab=$tabname&start='+this.value+'$url_string');";
	}
	if($module == 'Documents' && $action_val != 'UnifiedSearch'){
		$url = '&folderid='.$action_val;
	}else{
		$url = '';
	}
	$jsHandler = "return VT_disableFormSubmit(event);";
	$output .= getTranslatedString('Page', 'APP_STRINGS')." "; // crmv@31245
	$output .= "<input class='small' name='pagenum' type='text' value='{$navigation_array['current']}'
		style='width: 3em;margin-right: 0.7em;' onchange=\"$jsNavigate\"
		onkeypress=\"$jsHandler\">";
	$output .= "<span name='".$module."_listViewCountContainerName' class='small' style='white-space: nowrap;'>";
//	if(PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false) === true){
	$output .= $app_strings['LBL_LIST_OF'].' '.$navigation_array['verylast'];
//	}else{
//		$output .= "<img src='".vtiger_imageurl('windowRefresh.gif',$theme)."' alt='".$app_strings['LBL_HOME_COUNT']."'
//			onclick='getListViewCount(\"".$module."\",this,this.parentNode,\"".$url."\")'
//			align='absmiddle' name='".$module."_listViewCountRefreshIcon'/>
//			<img name='".$module."_listViewCountContainerBusy' src='".vtiger_imageurl('vtbusy.gif',$theme)."' style='display: none;'
//			align='absmiddle' alt='".$app_strings['LBL_LOADING']."'>";
//	}
	$output .='</span>';

	if(($navigation_array['next']) !=0){
		if($module == 'Calendar' && $action_val == 'index'){
			$output .= '<a href="javascript:;" onClick="cal_navigation(\''.$tab_type.'\',\''.$url_string.'\',\'&start='.$navigation_array['next'].'\');" alt="'.$app_strings['LNK_LIST_NEXT'].'" title="'.$app_strings['LNK_LIST_NEXT'].'"><img src="' . vtiger_imageurl('next.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
			$output .= '<a href="javascript:;" onClick="cal_navigation(\''.$tab_type.'\',\''.$url_string.'\',\'&start='.$navigation_array['verylast'].'\');" alt="'.$app_strings['LBL_LAST'].'" title="'.$app_strings['LBL_LAST'].'"><img src="' . vtiger_imageurl('end.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
		}else if($action_val == "FindDuplicate"){
			$output .= '<a href="javascript:;" onClick="getDuplicateListViewEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start='.$navigation_array['next'].$url_string.'\');" alt="'.$app_strings['LNK_LIST_NEXT'].'" title="'.$app_strings['LNK_LIST_NEXT'].'"><img src="' . vtiger_imageurl('next.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
			$output .= '<a href="javascript:;" onClick="getDuplicateListViewEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start='.$navigation_array['verylast'].$url_string.'\');" alt="'.$app_strings['LBL_LAST'].'" title="'.$app_strings['LBL_LAST'].'"><img src="' . vtiger_imageurl('end.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
		}elseif($action_val == 'UnifiedSearch'){
			$output .= '<a href="javascript:;" onClick="getUnifiedSearchEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start='.$navigation_array['next'].$url_string.'\');" alt="'.$app_strings['LNK_LIST_NEXT'].'" title="'.$app_strings['LNK_LIST_NEXT'].'"><img src="' . vtiger_imageurl('next.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
			$output .= '<a href="javascript:;" onClick="getUnifiedSearchEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start='.$navigation_array['verylast'].$url_string.'\');" alt="'.$app_strings['LBL_LAST'].'" title="'.$app_strings['LBL_LAST'].'"><img src="' . vtiger_imageurl('end.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
		}elseif($module == 'Documents'){
			$output .= '<a href="javascript:;" onClick="getListViewEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start='.$navigation_array['next'].$url_string.'&folderid='.$action_val.'\');" alt="'.$app_strings['LNK_LIST_NEXT'].'" title="'.$app_strings['LNK_LIST_NEXT'].'"><img src="' . vtiger_imageurl('next.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
			$output .= '<a href="javascript:;" onClick="getListViewEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start='.$navigation_array['verylast'].$url_string.'&folderid='.$action_val.'\');" alt="'.$app_strings['LBL_LAST'].'" title="'.$app_strings['LBL_LAST'].'"><img src="' . vtiger_imageurl('end.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
		}else{
			$output .= '<a href="javascript:;" onClick="getListViewEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start='.$navigation_array['next'].$url_string.'\');" alt="'.$app_strings['LNK_LIST_NEXT'].'" title="'.$app_strings['LNK_LIST_NEXT'].'"><img src="' . vtiger_imageurl('next.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
			$output .= '<a href="javascript:;" onClick="getListViewEntries_js(\''.$module.'\',\'parenttab='.$tabname.'&start='.$navigation_array['verylast'].$url_string.'\');" alt="'.$app_strings['LBL_LAST'].'" title="'.$app_strings['LBL_LAST'].'"><img src="' . vtiger_imageurl('end.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
		}
	}else{
		$output .= '<img src="' . vtiger_imageurl('next_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
		$output .= '<img src="' . vtiger_imageurl('end_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
	}
	if($module == 'Documents')
		$output .= '</td>';
	if($navigation_array['first']=='')
		return;
	else
		return $output;
}
//crmv@31245
function getRecordRangeMessage($numRows, $limitStartRecord,$noofrows = '') {
	global $adb, $app_strings;
	$recordListRangeMsg = '';
	if ($noofrows){
		if ($numRows > 0) {
			$recordListRangeMsg = $app_strings['LBL_SHOWING'].' '.$app_strings['LBL_FROM'].
			' '.($limitStartRecord+1).' '.$app_strings['LBL_A_AT'].' ';
			if (($limitStartRecord+$numRows) > $noofrows)
				$recordListRangeMsg .=$noofrows;
			else
				$recordListRangeMsg .=($limitStartRecord+$numRows);
		}
			$recordListRangeMsg .=" ".$app_strings['LBL_LIST_OF']." ".$noofrows.' '.$app_strings['LBL_RECORDS'];
	}
	else {
		if ($numRows > 0) {
			$recordListRangeMsg = $app_strings['LBL_SHOWING'].' '.$app_strings['LBL_FROM'].
			' '.($limitStartRecord+1).' '.$app_strings['LBL_A_AT'].' '.($limitStartRecord+$numRows);
		}
	}
	return $recordListRangeMsg;
}
//crmv@31245e

function listQueryNonAdminChange($query, $module, $scope='') {
	$instance = CRMEntity::getInstance($module);
	return $instance->listQueryNonAdminChange($query,$module,$scope);
}
function listQueryNonAdminChange_parent($query, $module, $scope='') {
	$instance = CRMEntity::getInstance($module);
	return $instance->listQueryNonAdminChange_parent($query,$module,$scope);
}
//crmv@31126
function getEntityId($module, $entityName) {
	global $log, $adb,$table_prefix;
	$log->info("in getEntityId " . $entityName);

	$query = "select fieldname,tablename,entityidfield from {$table_prefix}_entityname where modulename = ?";
	$result = $adb->pquery($query, array($module));
	$fieldsname = $adb->query_result($result, 0, 'fieldname');
	$tablename = $adb->query_result($result, 0, 'tablename');
	$entityidfield = $adb->query_result($result, 0, 'entityidfield');
	if (!(strpos($fieldsname, ',') === false)) {
		$fieldlists = explode(',', $fieldsname);
		$fieldsname = "concat(";
		$fieldsname = $fieldsname . implode(",' ',", $fieldlists);
		$fieldsname = $fieldsname . ")";
	}

	if ($entityName != '') {
		$sql = "select $entityidfield from $tablename INNER JOIN {$table_prefix}_crmentity ON {$table_prefix}_crmentity.crmid = $tablename.$entityidfield " .
				" WHERE {$table_prefix}_crmentity.deleted = 0 and $fieldsname=?";
		$result = $adb->pquery($sql, array($entityName));
		if ($adb->num_rows($result) > 0) {
			$entityId = $adb->query_result($result, 0, $entityidfield);
		}
	}
	if (!empty($entityId))
		return $entityId;
	else
		return 0;
}
//crmv@31126e
?>