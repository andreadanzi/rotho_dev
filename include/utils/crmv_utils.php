<?php
/***************************************************************************************
 * The contents of this file are subject to the CRMVILLAGE.BIZ VTECRM License Agreement
 * ("licenza.txt"); You may not use this file except in compliance with the License
 * The Original Code is:  CRMVILLAGE.BIZ VTECRM
 * The Initial Developer of the Original Code is CRMVILLAGE.BIZ.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
 ***************************************************************************************/

require_once('include/database/PearDatabase.php');
require_once('include/ComboUtil.php'); //new
require_once('include/utils/ListViewUtils.php');
require_once('include/utils/EditViewUtils.php');
require_once('include/utils/DetailViewUtils.php');
require_once('include/utils/CommonUtils.php');
require_once('include/utils/InventoryUtils.php');
require_once('include/utils/SearchUtils.php');
require_once('include/FormValidationUtil.php');
require_once('include/Zend/Json.php');	//crmv@9183
require_once('modules/Picklistmulti/Picklistmulti_class.php');	//crmv@8982
if (file_exists('modules/Morphsuit/utils/MorphsuitUtils.php')) {
	require_once('modules/Morphsuit/utils/MorphsuitUtils.php');
}
require_once('modules/SDK/SDK.php');	//crmv@sdk
require_once('include/utils/db_utils.php');	//crmv@26666

//crmv@10445
// retrieve "possible" values for fieldname on type uitype
function getStatusFieldValues($module,$fieldname,$uitype) {
	global $adb, $table_prefix;
	switch($uitype) {
		case 56:
			$query = "select fieldvalue,color from tbl_s_lvcolors where fieldname = ? and tabid = ?";
			$res = $adb->pquery($query,array($fieldname,getTabid($module)));
			if ($res){
				while ($row = $adb->fetch_array($res)){
					if ($row['fieldvalue'] == '1')
						$color[0] = $row['color'];
					elseif ($row['fieldvalue'] == '0')
						$color[1] = $row['color'];
				}
			}
			return Array(Array("id"=>"0",'value'=>'yes','value_display'=>'yes','color'=>$color[0]),Array('id'=>"1",'value'=>'no','value_display'=>'no','color'=>$color[1]));
			break;
		case in_array($uitype,array(15,16, 111,115,55,1115)):
			$tablename = "".$table_prefix."_".$fieldname;
				$query = "select
							$tablename.*,
							tbl_s_lvcolors.color
							from ".$tablename."
							left join tbl_s_lvcolors on $tablename.$fieldname = tbl_s_lvcolors.fieldvalue
							";
			$result = $adb->query($query);
			if($result && $adb->num_rows($result)>0) {
				$retval = Array();
				$numrows = $adb->num_rows($result);
				for($i=0;$i<$numrows;$i++) {
						$retval[] = Array('id'=> $adb->query_result($result,$i,0),"value" => $adb->query_result($result,$i,$fieldname),"value_display" => $adb->query_result($result,$i,$fieldname),"color"=>$adb->query_result($result,$i,"color"));
				}
				return $retval;
			}
			break;
		case 1015:
			$tablename = 'tbl_s_picklist_language';

			$query = "select
						$tablename.code,$tablename.value,
						tbl_s_lvcolors.color
						from ".$tablename."
						left join tbl_s_lvcolors on $tablename.code = tbl_s_lvcolors.fieldvalue
						";
			$query.=" where $tablename.field = ".$adb->quote($fieldname);
			global $current_language;
			$query.=" and $tablename.language = ".$adb->quote($current_language);
			$result = $adb->query($query);
			if($result && $adb->num_rows($result)>0) {
				$retval = Array();
				$numrows = $adb->num_rows($result);
				for($i=0;$i<$numrows;$i++) {
						$retval[] = Array('id'=> $adb->query_result($result,$i,0),"value_display" => $adb->query_result($result,$i,'value'),"value" => $adb->query_result($result,$i,'code'),"color"=>$adb->query_result($result,$i,"color"));
				}
				return $retval;
			}
			break;
		default:
	}
	return null;
}


 // retrieve fields defining a "status" on entity of type $module
 // - picklists
 // - checkboxes
function getStatusFields($module) {
	global $adb, $table_prefix;
	global $mod_strings,$app_strings;

	$tabid = getTabid($module);
	//crmv@29752
	$query = "select
				".$table_prefix."_field.fieldid,
				".$table_prefix."_field.fieldname,
				".$table_prefix."_field.fieldlabel,
				uitype
				from ".$table_prefix."_field
				inner join ".$table_prefix."_tab on ".$table_prefix."_tab.tabid=".$table_prefix."_field.tabid
				where
				uitype IN (15,16, 111,115,55,56,1115,1015)
				and ".$table_prefix."_field.tabid = ".$tabid."
				AND ".$table_prefix."_field.fieldname NOT IN ('hdnTaxType')
				order by ".$table_prefix."_field.fieldid ASC";
	$result = $adb->query($query);
	//crmv@29752e
	if($result && $adb->num_rows($result)>0) {
		$retval = Array();
		$numrows = $adb->num_rows($result);
		for($i=0;$i<$numrows;$i++) {
			if ($adb->query_result($result,$i,"uitype") == 55){
				if ($adb->query_result($result,$i,"fieldname") != 'salutationtype')
				 	continue;
			}
			$values = getStatusFieldValues($module,$adb->query_result($result,$i,"fieldname"),$adb->query_result($result,$i,"uitype"));
			$retval[$adb->query_result($result,$i,"fieldid")] = Array( 'fieldname' => $adb->query_result($result,$i,"fieldname"),
																		'fieldlabel' => $adb->query_result($result,$i,"fieldlabel"),
																		'uitype' => $adb->query_result($result,$i,"uitype"),
																		'values' => $values);
		}
		return $retval;
	} else return null;
}
// returns (if exists) the current used status field
function getUsedStatusField($module) {
	global $adb, $table_prefix;
	$tabid = getTabid($module);
	$query = "select
			".$table_prefix."_field.fieldname
			from ".$table_prefix."_field
			inner join ".$table_prefix."_tab on ".$table_prefix."_tab.tabid=".$table_prefix."_field.tabid
			inner join tbl_s_lvcolors on ".$table_prefix."_tab.tabid =  tbl_s_lvcolors.tabid and ".$table_prefix."_field.fieldname = tbl_s_lvcolors.fieldname
			where
			uitype IN (15,16, 111,115,55,56,1115,1015)
			and ".$table_prefix."_field.tabid != 29
			and ".$table_prefix."_field.tabid = ".$tabid."
			order by ".$table_prefix."_field.tabid ASC";
	$result = $adb->query($query);
	if($result && $adb->num_rows($result)>0) {
		return $adb->query_result($result,0,"fieldname");
	} else return null;
}

// return current status
function getEntityColor($tabid,$fieldvalue) {
	global $adb;
	if($fieldvalue != "") {
		$query = "select color from tbl_s_lvcolors where tabid = $tabid and fieldvalue = '$fieldvalue'";
		$result = $adb->query($query);
		if($result && $adb->num_rows($result)>0) {
			return $adb->query_result($result,0,'color');
		}
	}
	return null;
}

// return current status
function getEntityStatus($tabid,$module,$used_status_field,$crmid) {
	if($used_status_field != "") {
		global $adb, $table_prefix;
		$query = "select tablename,columnname from ".$table_prefix."_field where tabid = $tabid and fieldname = '$used_status_field' ";
		$result = $adb->query($query);
		if($result && $adb->num_rows($result)>0) {
			$tablename = $adb->query_result($result,0,"tablename");
			$columnname = $adb->query_result($result,0,"columnname");
			if($tablename != "") {
				$obj = CRMEntity::getInstance($module);
				$key = $obj->tab_name_index[$tablename];
				if ($key){
					$query = " select $columnname from $tablename where $key = ".$crmid."";
					$result = $adb->query($query);
					if($result && $adb->num_rows($result)>0)
						return $adb->query_result_no_html($result,0,$columnname);
				}
			}
		}
	}
	return null;
}
//crmv@10445e

//crm@7634
// return picklist on user array (for listview)
function getUserOptionsHTML($selected_user_id,$module_name,$parenttab) {

	global $current_user,$app_strings;
	$is_admin = is_admin($current_user);
	$tab_id = getTabid($module_name);
	require('user_privileges/sharing_privileges_'.$current_user->id.'.php');
	require('user_privileges/user_privileges_'.$current_user->id.'.php');
	//crmv@28496
	if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[$tab_id] == 3)
	{
		$users_array = get_user_array(FALSE, "Active", $current_user->id,'private','Read');
	}
	else
	{
		$users_array = get_user_array(FALSE, "Active", $current_user->id,'','Read');
	}
	//crmv@28496e
	//crmv@18592
	$htmlStr = "<select name='lv_user_id' class='small' id='lv_user_id' onChange='showDefaultCustomView(null,\"$module_name\",\"$parenttab\", \"{$_REQUEST['folderid']}\");'>"; // crmv@30967
	//crmv@18592e
	if($selected_user_id == all)
		$htmlStr .= "<option value='all' selected>".$app_strings['LBL_ASSIGNED_TO_ALL']."</option>'";
	else    $htmlStr .= "<option value='all' >".$app_strings['LBL_ASSIGNED_TO_ALL']."</option>";

	if($selected_user_id == mine)
		$htmlStr .= "<option value='mine' selected>".$app_strings['LBL_ASSIGNED_TO_ME']."</option>";
	else    $htmlStr .= "<option value='mine' >".$app_strings['LBL_ASSIGNED_TO_ME']."</option>";

	if($selected_user_id == others)
		$htmlStr .= "<option value='others' selected>".$app_strings['LBL_ASSIGNED_TO_OTHERS']."</option>";
	else	$htmlStr .= "<option value='others' >".$app_strings['LBL_ASSIGNED_TO_OTHERS']."</option>";

	foreach($users_array as $id=>$username)
	{
		if($id == $selected_user_id)
			$htmlStr .= "<option value='".$id."' selected>".$username."</option>";
		else
			$htmlStr .= "<option value='".$id."' >".$username."</option>";
	}

	$htmlStr .= "</select>";
	return $htmlStr;
}
//crm@7634e

//crmv@7221
/** Function to get the Advanced Sharing rule Info
 *  @param $shareId -- Sharing Rule Id
 *  @returns Sharing Rule Information Array in the following format:
 *    $shareRuleInfoArr=Array($shareId, $module_name, $type, $title, $desciption, $conditions);
 */
function getAdvSharingRuleInfo($shareId)
{
	global $log;
	$log->debug("Entering getAdvSharingRuleInfo(".$shareId.") method ...");
	global $adb;
	$shareRuleInfoArr=Array();
	$query = "select tbl_s_advancedrule.* from tbl_s_advancedrule
		 where tbl_s_advancedrule.advrule_id=?";
	$result=$adb->pquery($query, array($shareId));
	//Retreving the Sharing Tabid
	$module_name=$adb->query_result($result,0,'module_name');
	$title=$adb->query_result($result,0,'title');
	$desciption=$adb->query_result($result,0,'description');

	//Constructing the Array
	$shareRuleInfoArr['shareid']=$shareId;
	$shareRuleInfoArr['module']=$module_name;
	$shareRuleInfoArr['title']=$title;
	$shareRuleInfoArr['description']=$desciption;

	$log->debug("Exiting getAdvSharingRuleInfo method ...");
	return $shareRuleInfoArr;



}

      /** to get the Advanced filter criteria
	* @param $selected :: Type String (optional)
	* @returns  $AdvCriteria Array in the following format
	* $AdvCriteria = Array( 0 => array('value'=>$tablename:$colname:$fieldname:$fieldlabel,'text'=>$mod_strings[$field label],'selected'=>$selected),
	* 		     1 => array('value'=>$$tablename1:$colname1:$fieldname1:$fieldlabel1,'text'=>$mod_strings[$field label1],'selected'=>$selected),
	*		                             		|
	* 		     n => array('value'=>$$tablenamen:$colnamen:$fieldnamen:$fieldlabeln,'text'=>$mod_strings[$field labeln],'selected'=>$selected))
	*/
function getAdvRuleCriteriaHTML($selected="")
{
	global $app_list_strings;
	$adv_filter_options = CustomView::getAdvFilterOptions();	//crmv@26161
	$AdvCriteria = array();
	foreach($adv_filter_options as $key=>$value)
	{
		if($selected == $key)
		{
			$advfilter_criteria['value'] = $key;
			$advfilter_criteria['text'] = $value;
			$advfilter_criteria['selected'] = "selected";
		}else
		{
			$advfilter_criteria['value'] = $key;
			$advfilter_criteria['text'] = $value;
			$advfilter_criteria['selected'] = "";
		}
		$AdvCriteria[] = $advfilter_criteria;
	}

	return $AdvCriteria;
}

	/** to get the Advanced filter for the given customview Id
	  * @param $cvid :: Type Integer
	  * @returns  $stdfilterlist Array in the following format
	  * $stdfilterlist = Array( 0=>Array('columnname' =>  $tablename:$columnname:$fieldname:$module_$fieldlabel,'comparator'=>$comparator,'value'=>$value),
	  *			    1=>Array('columnname' =>  $tablename1:$columnname1:$fieldname1:$module_$fieldlabel1,'comparator'=>$comparator1,'value'=>$value1),
	  *		   			|
	  *			    4=>Array('columnname' =>  $tablename4:$columnname4:$fieldname4:$module_$fieldlabel4,'comparator'=>$comparatorn,'value'=>$valuen),
	  */
function getAdvRuleFilterByRuleid($id,$only_columns = false)
	{
		global $adb;
		global $modules;

		$sSQL = "select tbl_s_advancedrulefilters.* from tbl_s_advancedrulefilters inner join tbl_s_advancedrule on tbl_s_advancedrulefilters.advrule_id = tbl_s_advancedrule.advrule_id";
		$sSQL .= " where tbl_s_advancedrulefilters.advrule_id=?";
		$result = $adb->pquery($sSQL, array($id));

		while($advfilterrow = $adb->fetch_array($result))
		{
			if ($only_columns){
				if ($advfilterrow["columnname"] != null){
					$advfilterlist[] = $advfilterrow["columnname"];
				}
			}
			else {
				$advft["columnname"] = $advfilterrow["columnname"];
				$advft["comparator"] = $advfilterrow["comparator"];
				$advft["value"] = $advfilterrow["value"];
				$advfilterlist[] = $advft;
			}
		}
		return $advfilterlist;
	}

	/** to get the custom columns for the given module and columnlist
  * @param $module (modulename):: type String
  * @param $columnslist (Module columns list):: type Array
  * @param $selected (selected or not):: type String (Optional)
  * @returns  $advfilter_out array in the following format
  *	$advfilter_out = Array ('BLOCK1 NAME'=>
  * 					Array(0=>
  *						Array('value'=>$tablename:$colname:$fieldname:$fieldlabel:$typeofdata,
  *						      'text'=>$fieldlabel,
  *					      	      'selected'=><selected or ''>),
  *			      		      1=>
  *						Array('value'=>$tablename1:$colname1:$fieldname1:$fieldlabel1:$typeofdata1,
  *						      'text'=>$fieldlabel1,
  *					      	      'selected'=><selected or ''>)
  *					      ),
  *								|
  *								|
  *					      n=>
  *						Array('value'=>$tablenamen:$colnamen:$fieldnamen:$fieldlabeln:$typeofdatan,
  *						      'text'=>$fieldlabeln,
  *					      	      'selected'=><selected or ''>)
  *					      ),
  *				'BLOCK2 NAME'=>
  * 					Array(0=>
  *						Array('value'=>$tablename:$colname:$fieldname:$fieldlabel:$typeofdata,
  *						      'text'=>$fieldlabel,
  *					      	      'selected'=><selected or ''>),
  *			      		      1=>
  *						Array('value'=>$tablename1:$colname1:$fieldname1:$fieldlabel1:$typeofdata1,
  *						      'text'=>$fieldlabel1,
  *					      	      'selected'=><selected or ''>)
  *					      )
  *								|
  *								|
  *					      n=>
  *						Array('value'=>$tablenamen:$colnamen:$fieldnamen:$fieldlabeln:$typeofdatan,
  *						      'text'=>$fieldlabeln,
  *					      	      'selected'=><selected or ''>)
  *					      ),
  *
  *					||
  *					||
  *				'BLOCK_N NAME'=>
  * 					Array(0=>
  *						Array('value'=>$tablename:$colname:$fieldname:$fieldlabel:$typeofdata,
  *						      'text'=>$fieldlabel,
  *					      	      'selected'=><selected or ''>),
  *			      		      1=>
  *						Array('value'=>$tablename1:$colname1:$fieldname1:$fieldlabel1:$typeofdata1,
  *						      'text'=>$fieldlabel1,
  *					      	      'selected'=><selected or ''>)
  *					      )
  *								|
  *								|
  *					      n=>
  *						Array('value'=>$tablenamen:$colnamen:$fieldnamen:$fieldlabeln:$typeofdatan,
  *						      'text'=>$fieldlabeln,
  *					      	      'selected'=><selected or ''>)
  *					      ),

  *
  */

function getByModudddle_ColumnsHTML($module,$columnslist,$selected="")
{
	global $oCustomView, $current_language;
	global $app_list_strings;
	$advfilter = array();
	$mod_strings = return_specified_module_language($current_language,$module);

	$check_dup = Array();
	foreach($oCustomView->module_list[$module] as $key=>$value)
	{
		$advfilter = array();
		$label = $key;
		if(isset($columnslist[$module][$key]))
		{
			foreach($columnslist[$module][$key] as $field=>$fieldlabel)
			{
				if(!in_array($fieldlabel,$check_dup))
				{
					if(isset($mod_strings[$fieldlabel]))
					{
						if($selected == $field)
						{
							$advfilter_option['value'] = $field;
							$advfilter_option['text'] = $mod_strings[$fieldlabel];
							$advfilter_option['selected'] = "selected";
						}else
						{
							$advfilter_option['value'] = $field;
							$advfilter_option['text'] = $mod_strings[$fieldlabel];
							$advfilter_option['selected'] = "";
						}
					}else
					{
						if($selected == $field)
						{
							$advfilter_option['value'] = $field;
							$advfilter_option['text'] = $fieldlabel;
							$advfilter_option['selected'] = "selected";
						}else
						{
							$advfilter_option['value'] = $field;
							$advfilter_option['text'] = $fieldlabel;
							$advfilter_option['selected'] = "";
						}
					}
					$advfilter[] = $advfilter_option;
					$check_dup [] = $fieldlabel;
				}
			}
			$advfilter_out[$label]= $advfilter;
		}
	}

	$finalfield = Array();
	foreach($advfilter_out as $header=>$value)
	{
		if($header == $mod_strings['LBL_TASK_INFORMATION'])
		{
			$newLabel = $mod_strings['LBL_CALENDAR_INFORMATION'];
		    	$finalfield[$newLabel] = $advfilter_out[$header];

		}
		elseif($header == $mod_strings['LBL_EVENT_INFORMATION'])
		{
			$index = count($finalfield[$newLabel]);
			foreach($value as $key=>$result)
			{
				$finalfield[$newLabel][$index]=$result;
				$index++;
			}
		}
		else
		{
			$finalfield = $advfilter_out;
		}

		$advfilter_out=$finalfield;
	}
	return $advfilter_out;
}


	/** to get the customview AdvancedFilter Query for the given customview Id
  * @param $cvid :: Type Integer
  * @returns  $advfiltersql as a string
  * This function will return the advanced filter criteria for the given customfield
  *
  */
function getAdvRuleFilterSQL($cvid,$cv,$user)
{
	global $current_user, $table_prefix;
	$advfilter = getAdvRuleFilterByRuleid($cvid);
	if(isset($advfilter))
	{
		foreach($advfilter as $key=>$advfltrow)
		{
			if(isset($advfltrow))
			{
				$columns = explode(":",$advfltrow["columnname"]);
				$datatype = (isset($columns[4])) ? $columns[4] : "";
				if($advfltrow["columnname"] != "" && $advfltrow["comparator"] != "")
				{

					$valuearray = explode(",",trim($advfltrow["value"]));
					if(isset($valuearray) && count($valuearray) > 1)
					{
						$advorsql = "";
						for($n=0;$n<count($valuearray);$n++)
						{
							if (!strncasecmp($valuearray[$n],'$current_user->',14)){
								//sto usando un parametro dell'utente, quindi lo estraggo
								$val = substr($valuearray[$n], 18);
								$valuearray[$n] = $user->$val;
							}
							$advorsql[] = $cv->getRealValues($columns[0],$columns[1],$advfltrow["comparator"],trim($valuearray[$n]),$datatype);
						}
						//If negative logic filter ('not equal to', 'does not contain') is used, 'and' condition should be applied instead of 'or'
						if($advfltrow["comparator"] == 'n' || $advfltrow["comparator"] == 'k')
							$advorsqls = implode(" and ",$advorsql);
						else
							$advorsqls = implode(" or ",$advorsql);
						$advfiltersql[] = " (".$advorsqls.") ";
					}else
					{
						if (!strncasecmp($advfltrow["value"],'$current_user->',14)){
							//sto usando un parametro dell'utente, quindi lo estraggo
							$val = substr($advfltrow["value"], 18);
							$advfltrow["value"] = $user->$val;
						}
						//Added for getting vtiger_activity Status -Jaguar
						if($cv->customviewmodule == "Calendar" && ($columns[1] == "status" || $columns[1] == "eventstatus"))
						{
							if(getFieldVisibilityPermission("Calendar", $current_user->id,'taskstatus') == '0')
							{
								$advfiltersql[] = "case when (".$table_prefix."_activity.status not like '') then ".$table_prefix."_activity.status else ".$table_prefix."_activity.eventstatus end".$cv->getAdvComparator($advfltrow["comparator"],trim($advfltrow["value"]),$datatype);
							}
							else
								$advfiltersql[] = $table_prefix."_activity.eventstatus".$cv->getAdvComparator($advfltrow["comparator"],trim($advfltrow["value"]),$datatype);
						}
						else
						{
							$advfiltersql[] = $cv->getRealValues($columns[0],$columns[1],$advfltrow["comparator"],trim($advfltrow["value"]),$datatype);
						}
					}
				}
			}
		}
	}
	if(isset($advfiltersql))
	{
		$advfsql = implode(" and ",$advfiltersql);
	}
	return $advfsql;
}

/** This function is to delete the organisation level sharing rule
  * It takes the following input parameters:
  *     $shareid -- Id of the Sharing Rule to be updated
  */
function deleteAdvSharingRule($shareid)
{
	global $log;
	$log->debug("Entering deleteAdvSharingRule(".$shareid.") method ...");
	global $adb;
	$query3="delete from tbl_s_advancedrule where advrule_id=?";
	$adb->pquery($query3, array($shareid));
	$query4="delete from tbl_s_advancedrulefilters where advrule_id=?";
	$adb->pquery($query4, array($shareid));
	$log->debug("Exiting deleteAdvSharingRule method ...");

}

/** returns the list of sharing rules for the specified module
  * @param $module -- Module Name:: Type varchar
  * @returns $access_permission -- sharing rules list info array:: Type array
  *
 */
function getAdvSharingRuleList($module)
{
	global $adb,$mod_strings;
		$query = "select tbl_s_advancedrule.* from tbl_s_advancedrule
		 where tbl_s_advancedrule.module_name=?";
		$result=$adb->pquery($query, array($module));
		$num_rows=$adb->num_rows($result);
		for($j=0;$j<$num_rows;$j++)
		{
			$advrule_id=$adb->query_result($result,$j,"advrule_id");
			$title=$adb->query_result($result,$j,"title");
			$description=$adb->query_result($result,$j,"description");
			$permission=$adb->query_result($result,$j,"permission");

			$access_permission [] = $advrule_id;
			$access_permission [] = $title;
			$access_permission [] = $description;
			$access_permission [] = $permission;
		}

	if(is_array($access_permission))
		$access_permission = array_chunk($access_permission,4);
	return $access_permission;
}


/** returns the list of sharing rules for the specified module
  * @param $module -- Module Name:: Type varchar
  * @returns $access_permission -- sharing rules list info array:: Type array
  *
 */
function getAdvSharingRulePerm($advrule_id,$entity_type,$id)
{
	global $adb,$mod_strings;
		$query = "select tbl_s_advancedrule.title,tbl_s_advancedrule.module_name,tbl_s_advancedrule.title,tbl_s_advancedrule.description,
		tbl_s_advancedrule_rel.permission from tbl_s_advancedrule
		inner join tbl_s_advancedrule_rel on tbl_s_advancedrule_rel.advrule_id = tbl_s_advancedrule.advrule_id
		 where tbl_s_advancedrule.advrule_id = ? and entity_type=? and id =?";
		$result=$adb->pquery($query, array($advrule_id,$entity_type,$id));
		$num_rows=$adb->num_rows($result);
		if ($num_rows == 1){
			$title=$adb->query_result($result,$j,"title");
			$module=$adb->query_result($result,$j,"module_name");
			$description=$adb->query_result($result,$j,"description");
			$permission=$adb->query_result($result,$j,"permission");

			$access_permission [] = $advrule_id;
			$access_permission [] = $module;
			$access_permission [] = $title;
			$access_permission [] = $description;
			$access_permission [] = $permission;
		}
	return $access_permission;
}

/** returns the list of sharing rules for the specified module
  * @param $module -- Module Name:: Type varchar
  * @returns $access_permission -- sharing rules list info array:: Type array
  *
 */
function getAllAdvSharingRulePerm($module,$id)
{
	global $adb,$mod_strings;
		$query = "select tbl_s_advancedrule.advrule_id,tbl_s_advancedrule.title,tbl_s_advancedrule.description,
		tbl_s_advancedrule_rel.permission from tbl_s_advancedrule
		left join tbl_s_advancedrule_rel on tbl_s_advancedrule_rel.advrule_id = tbl_s_advancedrule.advrule_id
		 where tbl_s_advancedrule.module_name=?  and (id is null or id <> ?)";
		$result=$adb->pquery($query, array($module,$id));
		$num_rows=$adb->num_rows($result);
		for($j=0;$j<$num_rows;$j++)
		{
			$advrule_id=$adb->query_result($result,$j,"advrule_id");
			$title=$adb->query_result($result,$j,"title");
			$description=$adb->query_result($result,$j,"description");
			$permission=$adb->query_result($result,$j,"permission");

			$access_permission [] = $advrule_id;
			$access_permission [] = $title;
			$access_permission [] = $description;
			$access_permission [] = $permission;
		}

	if(is_array($access_permission))
		$access_permission = array_chunk($access_permission,4);
	return $access_permission;
}

/** returns the list of sharing rules for the specified module
  * @param $module -- Module Name:: Type varchar
  * @returns $access_permission -- sharing rules list info array:: Type array
  *
 */
function getAdvSharingRule($module,$id)
{
	global $adb,$mod_strings;
		$query = "select tbl_s_advancedrule.*,tbl_s_advancedrule_rel.* from tbl_s_advancedrule
		inner join tbl_s_advancedrule_rel on tbl_s_advancedrule_rel.advrule_id = tbl_s_advancedrule.advrule_id
		where tbl_s_advancedrule.module_name=? and id = ?";
		$result=$adb->pquery($query, array($module,$id));
		$num_rows=$adb->num_rows($result);
		for($j=0;$j<$num_rows;$j++)
		{
			$advrule_id=$adb->query_result($result,$j,"advrule_id");
			$title=$adb->query_result($result,$j,"title");
			$description=$adb->query_result($result,$j,"description");
			$permission=$adb->query_result($result,$j,"permission");
			if ($permission == 0) $permission=$mod_strings["Read Only "];
			else $permission=$mod_strings["Read/Write"];

			$access_permission [] = $advrule_id;
			$access_permission [] = $title;
			$access_permission [] = $description;
			$access_permission [] = $permission;
		}

	if(is_array($access_permission))
		$access_permission = array_chunk($access_permission,4);
	return $access_permission;
}

/** This function is to update the organisation level sharing rule
  * It takes the following input parameters:
  *     $shareid -- Id of the Sharing Rule to be updated
  * 	$module -- Module name - Datatype::varchar
  * 	$sharePermisson -- This can have the following values:
  *                       0 - Read Only
  *                       1 - Read/Write
  * 	$entityid -- id of the entity - Datatype::Varchar
  * 	$entity -- The Entity Type may be vtiger_groups,roles,rs and vtiger_users - Datatype::String
  * This function will return the shareid as output
  */
function updateAdvSharingRulePerm($shareid,$module,$sharePermission,$entityid,$entity)
{
	global $log;
	$log->debug("Entering updateAdvSharingRulePerm(".$shareid.",".$module.",".$sharePermission.",".$entityid.",".$entity.") method ...");
	global $adb;
	$query1="update tbl_s_advancedrule_rel
	set permission = ? where advrule_id=? and entity_type = ? and id = ?";
	$adb->pquery($query1, array($sharePermission,$shareid,$entity,$entityid));
	$log->debug("Exiting updateAdvSharingRulePerm method ...");
	return $shareid;
}

/** This function is to update the organisation level sharing rule
  * It takes the following input parameters:
  *     $shareid -- Id of the Sharing Rule to be updated
  * 	$tabid -- Tabid of module - Datatype::integer
  * 	$sharePermisson -- This can have the following values:
  *                       0 - Read Only
  *                       1 - Read/Write
  * 	$entityid -- id of the entity - Datatype::Varchar
  * 	$entity -- The Entity Type may be vtiger_groups,roles,rs and vtiger_users - Datatype::String
  * This function will return the shareid as output
  */
function updateRelatedModuleAdvSharingRulePerm($shareid,$tabid,$sharePermission,$entityid,$entity)
{
	global $log;
	$log->debug("Entering updateRelatedModuleAdvSharingRulePerm(".$shareid.",".$tabid.",".$sharePermission.",".$entityid.",".$entity.") method ...");
	global $adb;
	$query1="update tbl_s_advrule_relmod
	set rel_permission = ? where advrule_id=? and entity_type = ? and id = ? and rel_tabid = ?";
	$adb->pquery($query1, array($sharePermission,$shareid,$entity,$entityid,$tabid));
	$log->debug("Exiting updateRelatedModuleAdvSharingRulePerm method ...");
	return $shareid;
}

/** This function is to update the organisation level sharing rule
  * It takes the following input parameters:
  *     $shareid -- Id of the Sharing Rule to be updated
  * 	$module -- Module name - Datatype::varchar
  * 	$sharePermisson -- This can have the following values:
  *                       0 - Read Only
  *                       1 - Read/Write
  * 	$entityid -- id of the entity - Datatype::Varchar
  * 	$entity -- The Entity Type may be vtiger_groups,roles,rs and vtiger_users - Datatype::String
  * This function will return the shareid as output
  */
function addAdvSharingRulePerm($shareid,$module,$sharePermission,$entityid,$entity)
{
	global $log;
	$log->debug("Entering addAdvSharingRulePerm(".$shareid.",".$module.",".$sharePermission.",".$entityid.",".$entity.") method ...");
	global $adb;
	$query1="insert into tbl_s_advancedrule_rel (advrule_id,entity_type,id,permission)
	values (?,?,?,?)";
	$adb->pquery($query1, array($shareid,$entity,$entityid,$sharePermission));
	$log->debug("Exiting addAdvSharingRulePerm method ...");
	return $shareid;
}

/** This function is to update the organisation level sharing rule
  * It takes the following input parameters:
  *     $shareid -- Id of the Sharing Rule to be updated
  * 	$tabid -- Tabid of module - Datatype::integer
  * 	$sharePermisson -- This can have the following values:
  *                       0 - Read Only
  *                       1 - Read/Write
  * 	$entityid -- id of the entity - Datatype::Varchar
  * 	$entity -- The Entity Type may be vtiger_groups,roles,rs and vtiger_users - Datatype::String
  * This function will return the shareid as output
  */
function addRelatedModuleAdvSharingPerm($shareid,$tabid,$sharePermission,$entityid,$entity)
{
	global $log;
	$log->debug("Entering addRelatedModuleAdvSharingPerm(".$shareid.",".$tabid.",".$sharePermission.",".$entityid.",".$entity.") method ...");
	global $adb;
	$query1="insert into tbl_s_advrule_relmod (advrule_id,entity_type,id,rel_tabid,rel_permission)
	values (?,?,?,?,?)";
	$adb->pquery($query1, array($shareid,$entity,$entityid,$tabid,$sharePermission));
	$log->debug("Exiting addRelatedModuleAdvSharingPerm method ...");
	return $shareid;
}

/** This function is to retreive the Related Module Sharing Permissions for the specified Sharing Rule
  * It takes the following input parameters:
  *     $shareid -- The Sharing Rule Id:: Type Integer
  *This function will return the Related Module Sharing permissions in an Array in the following format:
  *     $PermissionArray=($relatedTabid1=>$sharingPermission1,
  *			  $relatedTabid2=>$sharingPermission2,
  *					|
  *                                     |
  *                       $relatedTabid-n=>$sharingPermission-n)
  */
function getRelatedModuleAdvSharingPerm($shareid,$entity,$entityid)
{
	global $log;
	$log->debug("Entering getRelatedModuleAdvSharingPerm(".$shareid.") method ...");
	global $adb;
	$relatedSharingModulePermissionArray=Array();
	$query="select tbl_s_advrule_relmod.* from tbl_s_advrule_relmod
	where tbl_s_advrule_relmod.advrule_id=? and entity_type = ? and id = ?";
	$result=$adb->pquery($query, array($shareid,$entity,$entityid));
	$num_rows=$adb->num_rows($result);
	for($i=0;$i<$num_rows;$i++)
	{
		$relatedto_tabid=$adb->query_result($result,$i,'rel_tabid');
		$permission=$adb->query_result($result,$i,'rel_permission');
		$relatedSharingModulePermissionArray[$relatedto_tabid]=$permission;


	}
	$log->debug("Exiting getRelatedModuleAdvSharingPerm method ...");
	return $relatedSharingModulePermissionArray;

}

/** This function is to delete the organisation level sharing rule
  * It takes the following input parameters:
  *     $shareid -- Id of the Sharing Rule to be updated
  */
function deleteAdvSharingRulePerm($shareid,$id)
{
	global $log;
	$log->debug("Entering deleteAdvSharingRulePerm(".$shareid.") method ...");
	global $adb;
	$query3="delete from tbl_s_advancedrule_rel where advrule_id=? and id = ?";
	$adb->pquery($query3, array($shareid,$id));
	$query4="delete from tbl_s_advrule_relmod where advrule_id=? and id = ?";
	$adb->pquery($query4, array($shareid,$id));
	$log->debug("Exiting deleteAdvSharingRulePerm method ...");

}
function getAdvSharingRules($module,$entityid){
	global $log;
	$log->debug("Entering getAdvSharingRules(".$module.",".$entityid.") method ...");
	global $adb;
	$query="select tbl_s_advancedrule.advrule_id,permission from tbl_s_advancedrule
	inner join tbl_s_advancedrule_rel on tbl_s_advancedrule_rel.advrule_id = tbl_s_advancedrule.advrule_id
	where id = ? and module_name =?";
	$result=$adb->pquery($query, array($entityid,$module));
	$num_rows=$adb->num_rows($result);
	$ret_array=null;
	for($i=0;$i<$num_rows;$i++)
	{
		$ruleid=$adb->query_result($result,$i,'advrule_id');
		$permission=$adb->query_result($result,$i,'permission');
		$ret_array[$ruleid]=$permission;
	}
	$log->debug("Exiting getAdvSharingRules method ...");
	return $ret_array;
}

function getAdvRelatedSharingRules($module,$relmodule,$entityid){
	global $log;
	$log->debug("Entering getAdvSharingRules(".$module.",".$entityid.") method ...");
	global $adb;
	$reltabid = getTabid($relmodule);
	$query="select tbl_s_advancedrule.advrule_id,tbl_s_advrule_relmod.rel_permission
			from tbl_s_advancedrule
			inner join tbl_s_advrule_relmod on tbl_s_advrule_relmod.advrule_id = tbl_s_advancedrule.advrule_id
			where id =? and module_name = ? and rel_tabid = ?";
	$result=$adb->pquery($query, array($entityid,$module,$reltabid));
	$num_rows=$adb->num_rows($result);
	$ret_array=null;
	for($i=0;$i<$num_rows;$i++)
	{
		$ruleid=$adb->query_result($result,$i,'advrule_id');
		$permission=$adb->query_result($result,$i,'rel_permission');
		$ret_array[$ruleid]=$permission;
	}
	$log->debug("Exiting getAdvSharingRules method ...");
	return $ret_array;
}

function get_advanced_query($adv_rule_arr,$cv,$user){
	if (is_array($adv_rule_arr) && $adv_rule_arr != null){
		$res["listview_before"]="'(";
		$res["read_before"]="' and (";
		$res["write_before"]="' and (";
		$res["listview_after"]=")'";
		$res["read_after"]=")'";
		$res["write_after"]=")'";
		$columns = array();		//crmv@22638
		foreach ($adv_rule_arr as $ruleid => $permission){
			$result=addslashes(getAdvRuleFilterSQL($ruleid,$cv,$user));
			//crmv@22638
			$columns_tmp = getAdvRuleFilterByRuleid($ruleid,true);
			foreach ($columns_tmp as $column_tmp) {
				$columns[] = $column_tmp;
			}
			//crmv@22638e
			$res["listview"].=$result." or ";
			if ($permission == 1){
				$w=true;
				$res["read"].=$result." or ";
				$res["write"].=$result." or ";
			}
			else {
				$res["read"].=$result." or ";
			}
		}
		$res["columns"]= Zend_Json::encode($columns);	//crmv@22638
		$res["listview"]=substr($res["listview"], 0, -4);
		$res["listview"] = $res["listview_before"].$res["listview"].$res["listview_after"];
		if ($w){
			$res["read"]=substr($res["read"], 0, -4);
			$res["read"] = $res["read_before"].$res["read"].$res["read_after"];
			$res["write"]=substr($res["write"], 0, -4);
			$res["write"] = $res["write_before"].$res["write"].$res["write_after"];
		}
		else {
			$res["read"]=substr($res["read"], 0, -4);
			$res["read"] = $res["read_before"].$res["read"].$res["read_after"];
			$res["write"]="''";
		}
	}
	else {
		$res["listview"]="''";
		$res["read"]="''";
		$res["write"]="''";
	}
	return $res;
}

/** This function is to retreive the list of related sharing modules for the specifed module
  * It takes the following input parameters:
  *     $tabid -- The module tabid:: Type Integer
  */

function getRelatedAdvSharingModules($tabid)
{
	global $log;
	$log->debug("Entering getRelatedAdvSharingModules(".$tabid.") method ...");
	global $adb;
	$relatedSharingModuleArray=Array();
	$query="select * from tbl_s_advrule_relmodlist where tabid=?";
	$result=$adb->pquery($query, array($tabid));
	$num_rows=$adb->num_rows($result);
	for($i=0;$i<$num_rows;$i++)
	{
		$ds_relmod_id=$adb->query_result($result,$i,'datashare_relatedmodule_id');
		$rel_tabid=$adb->query_result($result,$i,'relatedto_tabid');
		$relatedSharingModuleArray[$rel_tabid]=$ds_relmod_id;

	}
	$log->debug("Exiting getRelatedAdvSharingModules method ...");
	return $relatedSharingModuleArray;

}
//crmv@7221e

//crmv@8398
function getCalendarType($type,$history=''){
	global $adb,$current_user,$is_admin;
	if ($history == 'history') $history=1;
	else $history = 0;
	$config['event'] = Array('field'=>'activitytype','status_field'=>'eventstatus');
	$config['todo'] = Array('field'=>'activitytype','status_field'=>'taskstatus');
	$fieldnames=$config[$type];
	$roleid = $current_user->roleid;
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
	foreach ($fieldnames as $type=>$fieldname){
		global $table_prefix;
		$pick_query="select $fieldname from ".$table_prefix."_$fieldname inner join ".$table_prefix."_role2picklist on ".$table_prefix."_role2picklist.picklistvalueid = ".$table_prefix."_$fieldname.picklist_valueid and roleid = ? ";
		$params = array($roleid);
		if (($history == 0 || $history == 1) && $type !='field') {
			$pick_query.=" where history = ?";
			$params[] = $history;
		}
		$pickListResult = $adb->pquery($pick_query, $params);
		$noofpickrows = $adb->num_rows($pickListResult);
		$ret_arr[$type]=$fieldname;
		$pickListValue=null;
		for($j = 0; $j < $noofpickrows; $j++)
		{
			$pickListValue[]=$adb->query_result($pickListResult,$j,strtolower($fieldname));
		}
			$ret_arr[$type.'_value'] = $pickListValue;

	}
	return $ret_arr;
}

function getActivityTypeValues($type,$mode,$param=''){
	global $adb,$current_user, $table_prefix;
	require('user_privileges/user_privileges_'.$current_user->id.'.php');
	require('user_privileges/sharing_privileges_'.$current_user->id.'.php');
	$fieldname = 'activitytype';
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
	$pick_query="select $fieldname from ".$table_prefix."_$fieldname inner join ".$table_prefix."_role2picklist on ".$table_prefix."_role2picklist.picklistvalueid = ".$table_prefix."_$fieldname.picklist_valueid and roleid = ? ";
	$params = array($roleid);
	$pick_query.=" order by sortid asc "; //crmv@32334
	$pickListResult = $adb->pquery($pick_query, $params);
	$noofpickrows = $adb->num_rows($pickListResult);
	$pickListValue=null;
	for($j = 0; $j < $noofpickrows; $j++)
	{
		$pickListValue[]=$adb->query_result($pickListResult,$j,strtolower($fieldname));
	}
	if ($type == 'todo') $pickListValue=Array('Task');
	elseif ($type == 'event') unset($pickListValue['Task']);
	switch($mode){
		case "array":
			return $pickListValue;
			break;
		case "string_separated_by":
			return implode($param,$pickListValue);
			break;
		case "format_sql":
			$pickListValue_comma = "(";
		   $noofpickrows=count($pickListValue);
		   if ($noofpickrows!=0){
			   for($k=0; $k < $noofpickrows; $k++)
			   {
			      $pickListSingleVal = $pickListValue[$k];
			      $pickListValue_comma.="'".$pickListSingleVal."'";
			      if($k < ($noofpickrows-1))
			        	$pickListValue_comma.=',';
			   }
			   $pickListValue_comma.= ")";
		   }
			else  $pickListValue_comma = "('')";
			return $pickListValue_comma;
			break;
		case "default":
			return $pickListValue;
			break;
	}
}

function getCalendarCondition($caltype,$mode=''){
	global $table_prefix;
	$table= $table_prefix.'_activity';
	$config['event'] = Array('field'=>'activitytype','status_field'=>'eventstatus');
	$config['todo'] = Array('field'=>'activitytype','status_field'=>'status');
	$fieldnames=$config[$caltype];
	$arr=getCalendarType($caltype,$mode);
	$condition = " in ";
	if ($arr) {

	   foreach 	($fieldnames as $type=>$fieldname){
	   	if ($type == 'field') $conn == '';
	   	else $conn = 'and';
	   	$query.=" $conn $table.$fieldname $condition ";
	   	if ($caltype == 'todo' && $type == 'field') {
				$query.= "('Task')";
				continue;
			}
	   	$pickListValue_comma = "(";
		   $noofpickrows=count($arr[$type.'_value']);
		   if ($noofpickrows!=0){
			   for($k=0; $k < $noofpickrows; $k++)
			   {
			      $pickListValue = $arr[$type.'_value'][$k];
			      $pickListValue_comma.="'".$pickListValue."'";
			      if($k < ($noofpickrows-1))
			        	$pickListValue_comma.=',';
			   }
			   $pickListValue_comma.= ")";
		   }
			else  $pickListValue_comma = "('')";
			$query.=$pickListValue_comma;
	   }
	   return $query;
   }
	return null;
}
function getCalendarSql($mode=''){
	global $table_prefix;
	return " and ((".getCalendarCondition('todo',$mode).") or (".getCalendarCondition('event',$mode)."))";
}
function getCalendarSqlNoCondition(){
	global $table_prefix;
	return " and ".$table_prefix."_activity.activitytype in ".getActivityTypeValues('all','format_sql');
}
function getCalendarSqlCondition($mode){
	global $table_prefix;
	return " and ".$table_prefix."_activity.activitytype in ".getActivityTypeValues($mode,'format_sql');
}
//crmv@8398e

function enable_asterisk($id){
	global $adb, $table_prefix;
	$sql="select server,inc_call from ".$table_prefix."_systems where server_type = ?";
	$result = $adb->pquery($sql,Array('asterisk'));
	$server = $adb->query_result($result,0,'server');
	$inc_call = $adb->query_result($result,0,'inc_call');
	$sql="select extension from ".$table_prefix."_users where id = ?";
	$result = $adb->pquery($sql,Array($id));
	$extension = $adb->query_result($result,0,'extension');
	if ($server != '' && trim($extension) != '')
		$_SESSION['asterisk_'.$id]="true";
	else
		$_SESSION['asterisk_'.$id]="false";
	if ($inc_call == 1) $_SESSION['asterisk_inc_call']="true";
	else $_SESSION['asterisk_inc_call']="false";
}

//crmv@8719
/** Function to get permitted fields of current user of a particular module to find duplicate records --Pavani*/
function getMergeFields($module,$str){
	global $adb,$current_user, $table_prefix;
	$tabid = getTabid($module);
	if($str == "available_fields"){
		$result = getFieldsResultForMerge($tabid);
	}
	else { //if($str == fileds_to_merge)
		$sql="select * from ".$table_prefix."_user2mergefields where tabid=? and userid=? and visible=1";
		$result = $adb->pquery($sql, array($tabid,$current_user->id));
	}

	$num_rows=$adb->num_rows($result);

	$user_profileid = fetchUserProfileId($current_user->id);
	$permitted_list = getProfile2FieldPermissionList($module, $user_profileid);

	$sql_def_org="select fieldid from ".$table_prefix."_def_org_field where tabid=? and visible=0";
	$result_def_org=$adb->pquery($sql_def_org,array($tabid));
	$num_rows_org=$adb->num_rows($result_def_org);
	$permitted_org_list = Array();
	for($i=0; $i<$num_rows_org; $i++)
		$permitted_org_list[$i] = $adb->query_result($result_def_org,$i,"fieldid");

	require('user_privileges/user_privileges_'.$current_user->id.'.php');
	for($i=0; $i<$num_rows;$i++)
	{
		$field_id = $adb->query_result($result,$i,"fieldid");
		foreach($permitted_list as $field=>$data)
			if($data[4] == $field_id and $data[1] == 0)
			{
				if($is_admin == 'true' || (in_array($field_id,$permitted_org_list)))
				{
					$field="<option value=\"".$field_id."\">".getTranslatedString($data[0],$module)."</option>";
					$fields.=$field;
						break;
				}
			}
	}
	return $fields;
}
/** Function to get a to find duplicates in a particular module*/
function getDuplicateQuery($module,$field_values,$ui_type_arr)
{
	global $current_user, $table_prefix;
	$tbl_col_fld = explode(",", $field_values);
	$i=0;
	foreach($tbl_col_fld as $val) {
		list($tbl[$i], $cols[$i], $fields[$i]) = explode(".", $val);
		$tbl_cols[$i] = $tbl[$i]. "." . $cols[$i];
		$i++;
	}
	$table_cols = implode(",",$tbl_cols);
	$sec_parameter = getSecParameterforMerge($module);
	if( stristr($_REQUEST['action'],'ImportStep') || ($_REQUEST['action'] == $_REQUEST['module'].'Ajax' && $_REQUEST['current_action'] == 'ImportSteplast'))
	{
		if($module == 'Contacts')
		{
			$ret_arr = get_special_on_clause($table_cols);
			$select_clause = $ret_arr['sel_clause'];
			$on_clause = $ret_arr['on_clause'];
			$nquery="select ".$table_prefix."_contactdetails.contactid as recordid,".$table_prefix."_users_last_import.deleted,$table_cols
					FROM ".$table_prefix."_contactdetails
					INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_contactdetails.contactid
					INNER JOIN ".$table_prefix."_contactaddress ON ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_contactaddress.contactaddressid
					INNER JOIN ".$table_prefix."_contactsubdetails ON ".$table_prefix."_contactaddress.contactaddressid = ".$table_prefix."_contactsubdetails.contactsubscriptionid
					LEFT JOIN ".$table_prefix."_contactscf ON ".$table_prefix."_contactscf.contactid = ".$table_prefix."_contactdetails.contactid
					LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_contactdetails.contactid
					LEFT JOIN ".$table_prefix."_account ON ".$table_prefix."_account.accountid=".$table_prefix."_contactdetails.accountid
					LEFT JOIN ".$table_prefix."_customerdetails ON ".$table_prefix."_customerdetails.customerid=".$table_prefix."_contactdetails.contactid
					LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
					LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
					INNER JOIN (select $select_clause from ".$table_prefix."_contactdetails t
							INNER JOIN ".$table_prefix."_crmentity crm ON crm.crmid=t.contactid
							INNER JOIN ".$table_prefix."_contactaddress addr ON t.contactid = addr.contactaddressid
							INNER JOIN ".$table_prefix."_contactsubdetails subd ON addr.contactaddressid = subd.contactsubscriptionid
							LEFT JOIN ".$table_prefix."_contactscf tcf ON t.contactid = tcf.contactid
    						LEFT JOIN ".$table_prefix."_account acc ON acc.accountid=t.accountid
							LEFT JOIN ".$table_prefix."_customerdetails custd ON custd.customerid=t.contactid
							WHERE crm.deleted=0 group by $select_clause  HAVING COUNT(*)>1) temp
						ON ".get_on_clause($field_values,$ui_type_arr,$module)."
					WHERE ".$table_prefix."_crmentity.deleted=0 $sec_parameter ORDER BY $table_cols,".$table_prefix."_contactdetails.contactid ASC";

		}

	else if($module == 'Accounts')
		{
			$ret_arr = get_special_on_clause($field_values);
			$select_clause = $ret_arr['sel_clause'];
			$on_clause = $ret_arr['on_clause'];
			$nquery="SELECT ".$table_prefix."_account.accountid AS recordid,".$table_prefix."_users_last_import.deleted,".$table_cols."
				FROM ".$table_prefix."_account
				INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_account.accountid
				INNER JOIN ".$table_prefix."_accountbillads ON ".$table_prefix."_account.accountid = ".$table_prefix."_accountbillads.accountaddressid
				INNER JOIN ".$table_prefix."_accountshipads ON ".$table_prefix."_account.accountid = ".$table_prefix."_accountshipads.accountaddressid
				LEFT JOIN ".$table_prefix."_accountscf ON ".$table_prefix."_account.accountid=".$table_prefix."_accountscf.accountid
				LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_account.accountid
				LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
				INNER JOIN (select $select_clause from ".$table_prefix."_account t
							INNER JOIN ".$table_prefix."_crmentity crm ON crm.crmid=t.accountid
							INNER JOIN ".$table_prefix."_accountbillads badd ON t.accountid = badd.accountaddressid
							INNER JOIN ".$table_prefix."_accountshipads sadd ON t.accountid = sadd.accountaddressid
							LEFT JOIN ".$table_prefix."_accountscf tcf ON t.accountid = tcf.accountid
							WHERE crm.deleted=0 group by $select_clause HAVING COUNT(*)>1) temp
					ON ".get_on_clause($field_values,$ui_type_arr,$module)."
				WHERE ".$table_prefix."_crmentity.deleted=0 $sec_parameter ORDER BY $table_cols,".$table_prefix."_account.accountid ASC";

		}
	else if($module == 'Leads')
		{
			$ret_arr = get_special_on_clause($field_values);
			$select_clause = $ret_arr['sel_clause'];
			$on_clause = $ret_arr['on_clause'];
			$nquery="select ".$table_prefix."_leaddetails.leadid as recordid, ".$table_prefix."_users_last_import.deleted,$table_cols
					FROM ".$table_prefix."_leaddetails
					INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_leaddetails.leadid
					INNER JOIN ".$table_prefix."_leadsubdetails ON ".$table_prefix."_leadsubdetails.leadsubscriptionid = ".$table_prefix."_leaddetails.leadid
					INNER JOIN ".$table_prefix."_leadaddress ON ".$table_prefix."_leadaddress.leadaddressid = ".$table_prefix."_leadsubdetails.leadsubscriptionid
					LEFT JOIN ".$table_prefix."_leadscf ON ".$table_prefix."_leadscf.leadid=".$table_prefix."_leaddetails.leadid
					LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
					LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
					LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_leaddetails.leadid
					INNER JOIN (select $select_clause from ".$table_prefix."_leaddetails t
							INNER JOIN ".$table_prefix."_crmentity crm ON crm.crmid=t.leadid
							INNER JOIN ".$table_prefix."_leadsubdetails subd ON subd.leadsubscriptionid = t.leadid
							INNER JOIN ".$table_prefix."_leadaddress addr ON addr.leadaddressid = subd.leadsubscriptionid
							LEFT JOIN ".$table_prefix."_leadscf tcf ON tcf.leadid=t.leadid
							WHERE crm.deleted=0 and t.converted = 0 group by $select_clause HAVING COUNT(*)>1) temp
						ON ".get_on_clause($field_values,$ui_type_arr,$module)."
				WHERE ".$table_prefix."_crmentity.deleted=0 AND ".$table_prefix."_leaddetails.converted = 0 $sec_parameter ORDER BY $table_cols,".$table_prefix."_leaddetails.leadid ASC";

		}
	else if($module == 'Products')
		{
			$ret_arr = get_special_on_clause($field_values);
			$select_clause = $ret_arr['sel_clause'];
			$on_clause = $ret_arr['on_clause'];

			$nquery="SELECT ".$table_prefix."_products.productid AS recordid,".$table_prefix."_users_last_import.deleted,".$table_cols."
				FROM ".$table_prefix."_products
				INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_products.productid
				LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_products.productid
				LEFT JOIN ".$table_prefix."_productcf ON ".$table_prefix."_productcf.productid = ".$table_prefix."_products.productid
				INNER JOIN (select $select_clause from ".$table_prefix."_products t
						INNER JOIN ".$table_prefix."_crmentity crm ON crm.crmid=t.productid
						LEFT JOIN ".$table_prefix."_productcf tcf ON tcf.productid=t.productid
						WHERE crm.deleted=0 group by $select_clause HAVING COUNT(*)>1) temp
					ON ".get_on_clause($field_values,$ui_type_arr,$module)."
				WHERE ".$table_prefix."_crmentity.deleted=0 ORDER BY $table_cols,".$table_prefix."_products.productid ASC";

		}
		else if($module == 'HelpDesk')
		{
			$ret_arr = get_special_on_clause($field_values);
			$select_clause = $ret_arr['sel_clause'];
			$on_clause = $ret_arr['on_clause'];
			$nquery="SELECT ".$table_prefix."_troubletickets.ticketid AS recordid,".$table_prefix."_users_last_import.deleted,".$table_cols."
				FROM ".$table_prefix."_troubletickets
				INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_troubletickets.ticketid
				LEFT JOIN ".$table_prefix."_account ON ".$table_prefix."_account.accountid = ".$table_prefix."_troubletickets.parent_id
				LEFT JOIN ".$table_prefix."_contactdetails ON ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_troubletickets.parent_id
				LEFT JOIN ".$table_prefix."_ticketcf ON ".$table_prefix."_ticketcf.ticketid = ".$table_prefix."_troubletickets.ticketid
				LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_crmentity.smownerid = ".$table_prefix."_users.id
				LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_troubletickets.ticketid
				LEFT JOIN ".$table_prefix."_attachments ON ".$table_prefix."_attachments.attachmentsid=".$table_prefix."_crmentity.crmid
				LEFT JOIN ".$table_prefix."_ticketcomments ON ".$table_prefix."_ticketcomments.ticketid = ".$table_prefix."_crmentity.crmid
				INNER JOIN (select $select_clause from ".$table_prefix."_troubletickets t
						INNER JOIN ".$table_prefix."_crmentity crm ON crm.crmid=t.ticketid
						LEFT JOIN ".$table_prefix."_account acc ON acc.accountid = t.parent_id
						LEFT JOIN ".$table_prefix."_contactdetails contd ON contd.contactid = t.parent_id
						LEFT JOIN ".$table_prefix."_ticketcf tcf ON tcf.ticketid = t.ticketid
						WHERE crm.deleted=0 group by $select_clause HAVING COUNT(*)>1) temp
					ON ".get_on_clause($field_values,$ui_type_arr,$module)."
				WHERE ".$table_prefix."_crmentity.deleted=0". $sec_parameter ." ORDER BY $table_cols,".$table_prefix."_troubletickets.ticketid ASC";

		}
		else if($module == 'Potentials')
		{
			$ret_arr = get_special_on_clause($field_values);
			$select_clause = $ret_arr['sel_clause'];
			$on_clause = $ret_arr['on_clause'];
			$nquery="SELECT ".$table_prefix."_potential.potentialid AS recordid,
				".$table_prefix."_users_last_import.deleted,".$table_cols."
				FROM ".$table_prefix."_potential
				INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_potential.potentialid
				LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
				LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_potential.potentialid
				LEFT JOIN ".$table_prefix."_potentialscf ON ".$table_prefix."_potentialscf.potentialid = ".$table_prefix."_potential.potentialid
				INNER JOIN (select $select_clause from ".$table_prefix."_potential t
						INNER JOIN ".$table_prefix."_crmentity crm ON crm.crmid=t.potentialid
						LEFT JOIN ".$table_prefix."_potentialscf tcf ON tcf.potentialid=t.potentialid
						WHERE crm.deleted=0 group by $select_clause HAVING COUNT(*)>1) temp
					ON ".get_on_clause($field_values,$ui_type_arr,$module)."
				WHERE ".$table_prefix."_crmentity.deleted=0 $sec_parameter ORDER BY $table_cols,".$table_prefix."_potential.potentialid ASC";

		}
		else if($module == 'Vendors')
		{
			$ret_arr = get_special_on_clause($field_values);
			$select_clause = $ret_arr['sel_clause'];
			$on_clause = $ret_arr['on_clause'];
			$nquery="SELECT ".$table_prefix."_vendor.vendorid AS recordid,
				".$table_prefix."_users_last_import.deleted,".$table_cols."
				FROM ".$table_prefix."_vendor
				INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_vendor.vendorid
				LEFT JOIN ".$table_prefix."_vendorcf ON ".$table_prefix."_vendorcf.vendorid=".$table_prefix."_vendor.vendorid
				LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_vendor.vendorid
				INNER JOIN (select $select_clause from ".$table_prefix."_vendor t
						INNER JOIN ".$table_prefix."_crmentity crm ON crm.crmid=t.vendorid
						LEFT JOIN ".$table_prefix."_vendorcf tcf ON tcf.vendorid=t.vendorid
						WHERE crm.deleted=0 group by $select_clause HAVING COUNT(*)>1) temp
					ON ".get_on_clause($field_values,$ui_type_arr,$module)."
				WHERE ".$table_prefix."_crmentity.deleted=0 ORDER BY $table_cols,".$table_prefix."_vendor.vendorid ASC";

		} else {
			$ret_arr = get_special_on_clause($field_values);
			$select_clause = $ret_arr['sel_clause'];
			$on_clause = $ret_arr['on_clause'];
			$modObj = CRMEntity::getInstance($module);
			if ($modObj != null && method_exists($modObj, 'getDuplicatesQuery')) {
				$nquery = $modObj->getDuplicatesQuery($module,$table_cols,$field_values,$ui_type_arr,$select_clause);
			}
		}
	}
	else
	{

		if($module == 'Contacts')
		{
			$nquery = "SELECT ".$table_prefix."_contactdetails.contactid AS recordid,
					".$table_prefix."_users_last_import.deleted,".$table_cols."
					FROM ".$table_prefix."_contactdetails
					INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_contactdetails.contactid
					INNER JOIN ".$table_prefix."_contactaddress ON ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_contactaddress.contactaddressid
					INNER JOIN ".$table_prefix."_contactsubdetails ON ".$table_prefix."_contactaddress.contactaddressid = ".$table_prefix."_contactsubdetails.contactsubscriptionid
					LEFT JOIN ".$table_prefix."_contactscf ON ".$table_prefix."_contactscf.contactid = ".$table_prefix."_contactdetails.contactid
					LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_contactdetails.contactid
					LEFT JOIN ".$table_prefix."_account ON ".$table_prefix."_account.accountid=".$table_prefix."_contactdetails.accountid
					LEFT JOIN ".$table_prefix."_customerdetails ON ".$table_prefix."_customerdetails.customerid=".$table_prefix."_contactdetails.contactid
					LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
					LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
					INNER JOIN (SELECT $table_cols
							FROM ".$table_prefix."_contactdetails
							INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_contactdetails.contactid
							INNER JOIN ".$table_prefix."_contactaddress ON ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_contactaddress.contactaddressid
							INNER JOIN ".$table_prefix."_contactsubdetails ON ".$table_prefix."_contactaddress.contactaddressid = ".$table_prefix."_contactsubdetails.contactsubscriptionid
							LEFT JOIN ".$table_prefix."_contactscf ON ".$table_prefix."_contactscf.contactid = ".$table_prefix."_contactdetails.contactid
							LEFT JOIN ".$table_prefix."_account ON ".$table_prefix."_account.accountid=".$table_prefix."_contactdetails.accountid
							LEFT JOIN ".$table_prefix."_customerdetails ON ".$table_prefix."_customerdetails.customerid=".$table_prefix."_contactdetails.contactid
							LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
							LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
							WHERE ".$table_prefix."_crmentity.deleted=0 $sec_parameter
							GROUP BY ".$table_cols." HAVING COUNT(*)>1) temp
						ON ".get_on_clause($field_values,$ui_type_arr,$module) ."
	                                WHERE ".$table_prefix."_crmentity.deleted=0 $sec_parameter ORDER BY $table_cols,".$table_prefix."_contactdetails.contactid ASC";

		}
		else if($module == 'Accounts')
		{
			$nquery="SELECT ".$table_prefix."_account.accountid AS recordid,
				".$table_prefix."_users_last_import.deleted,".$table_cols."
				FROM ".$table_prefix."_account
				INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_account.accountid
				INNER JOIN ".$table_prefix."_accountbillads ON ".$table_prefix."_account.accountid = ".$table_prefix."_accountbillads.accountaddressid
				INNER JOIN ".$table_prefix."_accountshipads ON ".$table_prefix."_account.accountid = ".$table_prefix."_accountshipads.accountaddressid
				LEFT JOIN ".$table_prefix."_accountscf ON ".$table_prefix."_account.accountid=".$table_prefix."_accountscf.accountid
				LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_account.accountid
				LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
				INNER JOIN (SELECT $table_cols
					FROM ".$table_prefix."_account
					INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_account.accountid
					INNER JOIN ".$table_prefix."_accountbillads ON ".$table_prefix."_account.accountid = ".$table_prefix."_accountbillads.accountaddressid
					INNER JOIN ".$table_prefix."_accountshipads ON ".$table_prefix."_account.accountid = ".$table_prefix."_accountshipads.accountaddressid
					LEFT JOIN ".$table_prefix."_accountscf ON ".$table_prefix."_account.accountid=".$table_prefix."_accountscf.accountid
					LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
					LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
					WHERE ".$table_prefix."_crmentity.deleted=0 $sec_parameter
					GROUP BY ".$table_cols." HAVING COUNT(*)>1) temp
				ON ".get_on_clause($field_values,$ui_type_arr,$module) ."
                                WHERE ".$table_prefix."_crmentity.deleted=0 $sec_parameter ORDER BY $table_cols,".$table_prefix."_account.accountid ASC";
		}
		else if($module == 'Leads')
		{
			$nquery = "SELECT ".$table_prefix."_leaddetails.leadid AS recordid, ".$table_prefix."_users_last_import.deleted,$table_cols
					FROM ".$table_prefix."_leaddetails
					INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_leaddetails.leadid
					INNER JOIN ".$table_prefix."_leadsubdetails ON ".$table_prefix."_leadsubdetails.leadsubscriptionid = ".$table_prefix."_leaddetails.leadid
					INNER JOIN ".$table_prefix."_leadaddress ON ".$table_prefix."_leadaddress.leadaddressid = ".$table_prefix."_leadsubdetails.leadsubscriptionid
					LEFT JOIN ".$table_prefix."_leadscf ON ".$table_prefix."_leadscf.leadid=".$table_prefix."_leaddetails.leadid
					LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
					LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
					LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_leaddetails.leadid
					INNER JOIN (SELECT $table_cols
							FROM ".$table_prefix."_leaddetails
							INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_leaddetails.leadid
							INNER JOIN ".$table_prefix."_leadsubdetails ON ".$table_prefix."_leadsubdetails.leadsubscriptionid = ".$table_prefix."_leaddetails.leadid
							INNER JOIN ".$table_prefix."_leadaddress ON ".$table_prefix."_leadaddress.leadaddressid = ".$table_prefix."_leadsubdetails.leadsubscriptionid
							LEFT JOIN ".$table_prefix."_leadscf ON ".$table_prefix."_leadscf.leadid=".$table_prefix."_leaddetails.leadid
							LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
							LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
							WHERE ".$table_prefix."_crmentity.deleted=0 AND ".$table_prefix."_leaddetails.converted = 0 $sec_parameter
							GROUP BY $table_cols HAVING COUNT(*)>1) temp
					ON ".get_on_clause($field_values,$ui_type_arr,$module) ."
					WHERE ".$table_prefix."_crmentity.deleted=0  AND ".$table_prefix."_leaddetails.converted = 0 $sec_parameter ORDER BY $table_cols,".$table_prefix."_leaddetails.leadid ASC";

		}
		else if($module == 'Products')
		{
			$nquery = "SELECT ".$table_prefix."_products.productid AS recordid,
				".$table_prefix."_users_last_import.deleted,".$table_cols."
				FROM ".$table_prefix."_products
				INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_products.productid
				LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_products.productid
				LEFT JOIN ".$table_prefix."_productcf ON ".$table_prefix."_productcf.productid = ".$table_prefix."_products.productid
				INNER JOIN (SELECT $table_cols
							FROM ".$table_prefix."_products
							INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_products.productid
							LEFT JOIN ".$table_prefix."_productcf ON ".$table_prefix."_productcf.productid = ".$table_prefix."_products.productid
							WHERE ".$table_prefix."_crmentity.deleted=0
							GROUP BY ".$table_cols." HAVING COUNT(*)>1) temp
				ON ".get_on_clause($field_values,$ui_type_arr,$module) ."
                                WHERE ".$table_prefix."_crmentity.deleted=0  ORDER BY $table_cols,".$table_prefix."_products.productid ASC";
		}
		else if($module == "HelpDesk")
		{
			$nquery = "SELECT ".$table_prefix."_troubletickets.ticketid AS recordid,
				".$table_prefix."_users_last_import.deleted,".$table_cols."
				FROM ".$table_prefix."_troubletickets
				INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_troubletickets.ticketid
				LEFT JOIN ".$table_prefix."_ticketcf ON ".$table_prefix."_ticketcf.ticketid = ".$table_prefix."_troubletickets.ticketid
				LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_troubletickets.ticketid
				LEFT JOIN ".$table_prefix."_attachments ON ".$table_prefix."_attachments.attachmentsid=".$table_prefix."_crmentity.crmid
				LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				LEFT JOIN ".$table_prefix."_contactdetails ON ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_troubletickets.parent_id
				LEFT JOIN ".$table_prefix."_ticketcomments ON ".$table_prefix."_ticketcomments.ticketid = ".$table_prefix."_crmentity.crmid
				INNER JOIN (SELECT $table_cols FROM ".$table_prefix."_troubletickets
							INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_troubletickets.ticketid
							LEFT JOIN ".$table_prefix."_ticketcf ON ".$table_prefix."_ticketcf.ticketid = ".$table_prefix."_troubletickets.ticketid
							LEFT JOIN ".$table_prefix."_attachments ON ".$table_prefix."_attachments.attachmentsid=".$table_prefix."_crmentity.crmid
							LEFT JOIN ".$table_prefix."_contactdetails ON ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_troubletickets.parent_id
							LEFT JOIN ".$table_prefix."_ticketcomments ON ".$table_prefix."_ticketcomments.ticketid = ".$table_prefix."_crmentity.crmid
							LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
							LEFT JOIN ".$table_prefix."_contactdetails contd ON contd.contactid = ".$table_prefix."_troubletickets.parent_id
				WHERE ".$table_prefix."_crmentity.deleted=0 $sec_parameter
							GROUP BY ".$table_cols." HAVING COUNT(*)>1) temp
				ON ".get_on_clause($field_values,$ui_type_arr,$module) ."
                                WHERE ".$table_prefix."_crmentity.deleted=0 $sec_parameter ORDER BY $table_cols,".$table_prefix."_troubletickets.ticketid ASC";
		}
		else if($module == "Potentials")
		{
			$nquery = "SELECT ".$table_prefix."_potential.potentialid AS recordid,
				".$table_prefix."_users_last_import.deleted,".$table_cols."
				FROM ".$table_prefix."_potential
				INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_potential.potentialid
				LEFT JOIN ".$table_prefix."_potentialscf ON ".$table_prefix."_potentialscf.potentialid = ".$table_prefix."_potential.potentialid
				LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_potential.potentialid
				LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
				INNER JOIN (SELECT $table_cols
							FROM ".$table_prefix."_potential
							INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_potential.potentialid
							LEFT JOIN ".$table_prefix."_potentialscf ON ".$table_prefix."_potentialscf.potentialid = ".$table_prefix."_potential.potentialid
							LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
							LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
							WHERE ".$table_prefix."_crmentity.deleted=0 $sec_parameter
							GROUP BY ".$table_cols." HAVING COUNT(*)>1) temp
				ON ".get_on_clause($field_values,$ui_type_arr,$module) ."
                                WHERE ".$table_prefix."_crmentity.deleted=0 $sec_parameter ORDER BY $table_cols,".$table_prefix."_potential.potentialid ASC";
		}
		else if($module == "Vendors")
		{
			$nquery = "SELECT ".$table_prefix."_vendor.vendorid AS recordid,
				".$table_prefix."_users_last_import.deleted,".$table_cols."
				FROM ".$table_prefix."_vendor
				INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_vendor.vendorid
				LEFT JOIN ".$table_prefix."_vendorcf ON ".$table_prefix."_vendorcf.vendorid=".$table_prefix."_vendor.vendorid
				LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_vendor.vendorid
				INNER JOIN (SELECT $table_cols
							FROM ".$table_prefix."_vendor
							INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_vendor.vendorid
							LEFT JOIN ".$table_prefix."_vendorcf ON ".$table_prefix."_vendorcf.vendorid=".$table_prefix."_vendor.vendorid
							WHERE ".$table_prefix."_crmentity.deleted=0
							GROUP BY ".$table_cols." HAVING COUNT(*)>1) temp
				ON ".get_on_clause($field_values,$ui_type_arr,$module) ."
                                WHERE ".$table_prefix."_crmentity.deleted=0  ORDER BY $table_cols,".$table_prefix."_vendor.vendorid ASC";
		} else {
			$modObj = CRMEntity::getInstance($module);
			if ($modObj != null && method_exists($modObj, 'getDuplicatesQuery')) {
				$nquery = $modObj->getDuplicatesQuery($module,$table_cols,$field_values,$ui_type_arr);
			}
		}
	}
	return $nquery;
}

/** Function to return the duplicate records data as a formatted array */
function getDuplicateRecordsArr($module)
{
	global $adb,$app_strings,$list_max_entries_per_page,$theme;
	$field_values_array=getFieldValues($module);
	$field_values=$field_values_array['fieldnames_list'];
	$fld_arr=$field_values_array['fieldnames_array'];
	$col_arr=$field_values_array['columnnames_array'];
	$fld_labl_arr=$field_values_array['fieldlabels_array'];
	$ui_type=$field_values_array['fieldname_uitype'];

	//mycrmv@2707m
	$dupl_col_arr = $col_arr;
	$dupl_col_arr_keys = array_flip($dupl_col_arr);
	require_once('modules/SDK/src/DuplicateRecordUtils.php');
	$select_fields_array=getDuplicateRecordSelectedFields($module);
	if (!empty($select_fields_array)) {
		$select_fields=$select_fields_array['fieldnames_list'];
		$fld_arr=$select_fields_array['fieldnames_array'];
		$col_arr=$select_fields_array['columnnames_array'];
		$fld_labl_arr=$select_fields_array['fieldlabels_array'];
		$ui_type=$select_fields_array['fieldname_uitype'];
	}
	//$dup_query = getDuplicateQuery($module,$field_values,$ui_type);
	$dup_query = getDuplicateQueryRotho($module,$field_values,$ui_type,$select_fields);
	//mycrmv@2707me
	// added for page navigation
	$dup_count_query = mkCountQuery($dup_query);
	$count_res = $adb->query($dup_count_query);
	$no_of_rows = $adb->query_result($count_res,0,"count");

	if($no_of_rows <= $list_max_entries_per_page)
		$_SESSION['dup_nav_start'.$module] = 1;
	else if(isset($_REQUEST["start"]) && $_REQUEST["start"] != "" && $_SESSION['dup_nav_start'.$module] != $_REQUEST["start"])
		$_SESSION['dup_nav_start'.$module] = ListViewSession::getRequestStartPage();
	$start = ($_SESSION['dup_nav_start'.$module] != "")?$_SESSION['dup_nav_start'.$module]:1;
	$navigation_array = getNavigationValues($start, $no_of_rows, $list_max_entries_per_page);
	$start_rec = $navigation_array['start'];
	$end_rec = $navigation_array['end_val'];
	$navigationOutput = getTableHeaderNavigation($navigation_array, "",$module,"FindDuplicate","");
	if ($start_rec == 0)
		$limit_start_rec = 0;
	else
		$limit_start_rec = $start_rec -1;

	//ends

	$nresult = $adb->limitQuery($dup_query,$limit_start_rec,$list_max_entries_per_page);
	$no_rows=$adb->num_rows($nresult);
	require_once('modules/VteCore/layout_utils.php');	//crmv@30447
	if($no_rows == 0)
	{
		if ($_REQUEST['action'] == 'FindDuplicateRecords')
		{
			//echo "<br><br><center>".$app_strings['LBL_NO_DUPLICATE']." <a href='javascript:window.history.back()'>".$app_strings['LBL_GO_BACK'].".</a></center>";
			//die;
			echo "<link rel='stylesheet' type='text/css' href='themes/$theme/style.css'>";
			echo "<table border='0' cellpadding='5' cellspacing='0' width='100%' height='450px'><tr><td align='center'>";
			echo "<div style='border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 55%; position: relative; z-index: 10000000;'>

				<table border='0' cellpadding='5' cellspacing='0' width='98%'>
				<tbody><tr>
				<td rowspan='2' width='11%'><img src='" . vtiger_imageurl('empty.jpg', $theme) . "' ></td>
				<td style='border-bottom: 1px solid rgb(204, 204, 204);' nowrap='nowrap' width='70%'><span class='genHeaderSmall'>$app_strings[LBL_NO_DUPLICATE]</span></td>
				</tr>
				<tr>
				<td class='small' align='right' nowrap='nowrap'>
				<a href='javascript:window.history.back();'>$app_strings[LBL_GO_BACK]</a><br>     </td>
				</tr>
				</tbody></table>
				</div>";
			echo "</td></tr></table>";
			exit();
		}
		else
		{
			echo "<br><br><table align='center' class='reportCreateBottom big' width='95%'><tr><td align='center'>".$app_strings['LBL_NO_DUPLICATE']."</td></tr></table>";
			die;
		}
	}
	$rec_cnt = 0;
	$temp = Array();
	$sl_arr = Array();
	$grp = "group0";
	$gcnt = 0;
	$ii = 0; //ii'th record in group
	while ( $rec_cnt < $no_rows )
	{
		$result = $adb->fetchByAssoc($nresult);
		//echo '<pre>';print_r($result);echo '</pre>';
		if($rec_cnt != 0)
		{
			$sl_arr = array_slice($result,2);
			$sl_arr = array_intersect_key($result,$dupl_col_arr_keys);	//mycrmv@2707m
			array_walk($temp,'lower_array');
			array_walk($sl_arr,'lower_array');
			$arr_diff = array_diff($temp,$sl_arr);
			if(count($arr_diff) > 0)
			{
				$gcnt++;
				$temp = $sl_arr;
				$ii = 0;
			}
			$grp = "group".$gcnt;
		//mycrmv@2707m
		} else {
			$sl_arr = array_slice($result,2);
			$sl_arr = array_intersect_key($result,$dupl_col_arr_keys);
			$temp = $sl_arr;
		//mycrmv@2707me
		}
		$fld_values[$grp][$ii]['recordid'] = $result['recordid'];
		for($k=0;$k<count($col_arr);$k++)
		{
			//mycrmv@2707m
			/*
			if($rec_cnt == 0)
			{
				$temp[$fld_labl_arr[$k]] = $result[$col_arr[$k]];
			}
			*/
			//mycrmv@2707me
			if($ui_type[$fld_arr[$k]] == 56)
			{
				if($result[$col_arr[$k]] == 0)
				{
					$result[$col_arr[$k]]=$app_strings['no'];
				}
				else
					$result[$col_arr[$k]]=$app_strings['yes'];
			}
			if($ui_type[$fld_arr[$k]] ==75 || $ui_type[$fld_arr[$k]] ==81)
			{
				$vendor_id=$result[$col_arr[$k]];
				if($vendor_id != '')
					{
						$vendor_name=getVendorName($vendor_id);
					}
				$result[$col_arr[$k]]=$vendor_name;
			}
			if($ui_type[$fld_arr[$k]] ==57)
			{
				$contact_id= $result[$col_arr[$k]];
				if($contact_id != '')
				{
					$contactname=getContactName($contact_id);
				}

				$result[$col_arr[$k]]=$contactname;
			}
			if($ui_type[$fld_arr[$k]] ==68)
			{
				$parent_id= $result[$col_arr[$k]];
				if($parent_id != '')
				{
					$parentname=getParentName($parent_id);
				}

				$result[$col_arr[$k]]=$parentname;
			}
			if($ui_type[$fld_arr[$k]] ==53 || $ui_type[$fld_arr[$k]] ==52)
			{
				if($result[$col_arr[$k]] != '')
				{
					$owner=getOwnerName($result[$col_arr[$k]]);
				}
				$result[$col_arr[$k]]=$owner;
			}
			if($ui_type[$fld_arr[$k]] ==50 or $ui_type[$fld_arr[$k]] ==51)
			{
				if($module!='Products') {
					$entity_name=getAccountName($result[$col_arr[$k]]);
				} else {
					$entity_name=getProductName($result[$col_arr[$k]]);
				}
				if($entity_name != '') {
					$result[$col_arr[$k]]=$entity_name;
				} else {
					$result[$col_arr[$k]]='';
				}
			}
			if($ui_type[$fld_arr[$k]] ==58)
			{
				$campaign_name=getCampaignName($result[$col_arr[$k]]);
				if($campaign_name != '')
					$result[$col_arr[$k]]=$campaign_name;
				else $result[$col_arr[$k]]='';
			}
			if($ui_type[$fld_arr[$k]] == 59)
			{
				$product_name=getProductName($result[$col_arr[$k]]);
				if($product_name != '')
					$result[$col_arr[$k]]=$product_name;
				else $result[$col_arr[$k]]='';
			}
			/*uitype 10 handling*/
			if($ui_type[$fld_arr[$k]] == 10){
				$result[$col_arr[$k]] = getRecordInfoFromID($result[$col_arr[$k]]);
			}

			$fld_values[$grp][$ii][$fld_labl_arr[$k]] = $result[$col_arr[$k]];

		}
		$fld_values[$grp][$ii]['Entity Type'] = $result['deleted'];
		$ii++;
		$rec_cnt++;
	}

	$gro="group";
	for($i=0;$i<$no_rows;$i++)
	{
		$ii=0;
		$dis_group[]=$fld_values[$gro.$i][$ii];
		$count_group[$i]=count($fld_values[$gro.$i]);
		$ii++;
		$new_group[]=$dis_group[$i];
	}
	$fld_nam=$new_group[0];
	$ret_arr[0]=$fld_values;
	$ret_arr[1]=$fld_nam;
	$ret_arr[2]=$ui_type;
	$ret_arr["navigation"]=$navigationOutput;
	return $ret_arr;
}

/** Function to get on clause criteria for sub tables like address tables to construct duplicate check query */
function get_special_on_clause($field_list)
{
	global $table_prefix;
	$field_array = explode(",",$field_list);
	$ret_str = '';
	$sel_clause = '';
	$i=1;
	$cnt = count($field_array);
	$spl_chk = ($_REQUEST['modulename'] != '')?$_REQUEST['modulename']:$_REQUEST['module'];
	foreach($field_array as $fld)
	{
		$sub_arr = explode(".",$fld);
		$tbl_name = $sub_arr[0];
		$col_name = $sub_arr[1];
		$fld_name = $sub_arr[2];

		//need to handle aditional conditions with sub tables for further modules of duplicate check
		if($tbl_name == $table_prefix.'_leadsubdetails' || $tbl_name == $table_prefix.'_contactsubdetails')
			$tbl_alias = "subd";
		else if($tbl_name == $table_prefix.'_leadaddress' || $tbl_name == $table_prefix.'_contactaddress')
			$tbl_alias = "addr";
		else if($tbl_name == $table_prefix.'_account' && $spl_chk == 'Contacts')
			$tbl_alias = "acc";
		else if($tbl_name == $table_prefix.'_accountbillads')
			$tbl_alias = "badd";
		else if($tbl_name == $table_prefix.'_accountshipads')
			$tbl_alias = "sadd";
		else if($tbl_name == $table_prefix.'_crmentity')
			$tbl_alias = "crm";
		else if($tbl_name == $table_prefix.'_customerdetails')
			$tbl_alias = "custd";
		else if($tbl_name == $table_prefix.'_contactdetails' && spl_chk == 'HelpDesk')
			$tbl_alias = "contd";
		else if(stripos($tbl_name, 'cf') === (strlen($tbl_name) - strlen('cf')))
			$tbl_alias = "tcf"; // Custom Field Table Prefix to use in subqueries
		else
			$tbl_alias = "t";

		$sel_clause .= $tbl_alias.".".$col_name.",";
		$ret_str .= " $tbl_name.$col_name = $tbl_alias.$col_name";
		if ($cnt != $i) $ret_str .= " and ";
		$i++;
	}
	$ret_arr['on_clause'] = $ret_str;
	$ret_arr['sel_clause'] = trim($sel_clause,",");
	return $ret_arr;
}

/** Function to get on clause criteria for duplicate check queries */
function get_on_clause($field_list,$uitype_arr,$module)
{
	global $adb;
	$field_array = explode(",",$field_list);
	$ret_str = '';
	$i=1;
	foreach($field_array as $fld)
	{
		$sub_arr = explode(".",$fld);
		$tbl_name = $sub_arr[0];
		$col_name = $sub_arr[1];
		$fld_name = $sub_arr[2];
		$ret_str .= " ".$adb->database->IfNull($tbl_name.".".$col_name,'null')." = ".$adb->database->IfNull('temp.'.$col_name,'null')." ";

		if (count($field_array) != $i) $ret_str .= " and ";
		$i++;
	}
	return $ret_str;
}

/** call back function to change the array values in to lower case */
function lower_array(&$string){
	    $string = strtolower(trim($string));
}

/** Function to get recordids for subquery where condition */
// TODO - Need to check if this method is used anywhere?
function get_subquery_recordids($sub_query)
{
	global $adb;
	//need to update this module whenever duplicate check tool added for new modules
	$module_id_array = Array("Accounts"=>"accountid","Contacts"=>"contactid","Leads"=>"leadid","Products"=>"productid","HelpDesk"=>"ticketid","Potentials"=>"potentialid","Vendors"=>"vendorid");
	$id = ($module_id_array[$_REQUEST['modulename']] != '')?$module_id_array[$_REQUEST['modulename']]:$module_id_array[$_REQUEST['module']];
	$sub_res = '';
	$sub_result = $adb->query($sub_query);
	$row_count = $adb->num_rows($sub_result);
	$sub_res = '';
	if($row_count > 0)
	{
		while($rows = $adb->fetchByAssoc($sub_result))
		{
			$sub_res .= $rows[$id].",";
		}
		$sub_res = trim($sub_res,",");
	}
	else
		$sub_res .= "''";
	return $sub_res;
}

/** Function to get tablename, columnname, fieldname, fieldlabel and uitypes of fields of merge criteria for a particular module*/
function getFieldValues($module)
{
	global $adb,$current_user,$table_prefix;

	//In future if we want to change a id mapping to name or other string then we can add that elements in this array.
	//$fld_table_arr = Array("vtiger_contactdetails.account_id"=>"vtiger_account.accountname");
	//$special_fld_arr = Array("account_id"=>"accountname");

	$fld_table_arr = Array();
	$special_fld_arr = Array();
	$tabid = getTabid($module);

	$fieldname_query="select fieldname,fieldlabel,uitype,tablename,columnname from ".$table_prefix."_field where fieldid in
			(select fieldid from ".$table_prefix."_user2mergefields WHERE tabid=? AND userid=? AND visible = ?) and ".$table_prefix."_field.presence in (0,2)";
	$fieldname_result = $adb->pquery($fieldname_query, array($tabid, $current_user->id, 1));

	$field_num_rows = $adb->num_rows($fieldname_result);

	$fld_arr = array();
	$col_arr = array();
	for($j=0;$j< $field_num_rows;$j ++)
	{
		$tablename = $adb->query_result($fieldname_result,$j,'tablename');
		$column_name = $adb->query_result($fieldname_result,$j,'columnname');
		$field_name = $adb->query_result($fieldname_result,$j,'fieldname');
		$field_lbl = $adb->query_result($fieldname_result,$j,'fieldlabel');
		$ui_type = $adb->query_result($fieldname_result,$j,'uitype');
		$table_col = $tablename.".".$column_name;
		if(getFieldVisibilityPermission($module,$current_user->id,$field_name) == 0)
		{
			$fld_name = ($special_fld_arr[$field_name] != '')?$special_fld_arr[$field_name]:$field_name;

			$fld_arr[] = $fld_name;
			$col_arr[] = $column_name;
			if($fld_table_arr[$table_col] != '')
				$table_col = $fld_table_arr[$table_col];

			$field_values_array['fieldnames_list'][] = $table_col . "." . $fld_name;
			$fld_labl_arr[]=$field_lbl;
			$uitype[$field_name]=$ui_type;
		}
	}
	$field_values_array['fieldnames_list']=implode(",",$field_values_array['fieldnames_list']);
	$field_values=implode(",",$fld_arr);
	$field_values_array['fieldnames']=$field_values;
	$field_values_array["fieldnames_array"]=$fld_arr;
	$field_values_array["columnnames_array"]=$col_arr;
	$field_values_array['fieldlabels_array']=$fld_labl_arr;
	$field_values_array['fieldname_uitype']=$uitype;

	return $field_values_array;
}

function getSecParameterforMerge($module)
{
	global $current_user;
	$tab_id = getTabid($module);
	$sec_parameter="";
	require('user_privileges/user_privileges_'.$current_user->id.'.php');
	require('user_privileges/sharing_privileges_'.$current_user->id.'.php');
	if($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[$tab_id] == 3)
	{
		if($module == "Products" || $module == "Vendors") {
			$sec_parameter = "";
		} else {
			$sec_parameter=getListViewSecurityParameter($module);
		}
	}
	return $sec_parameter;
}

//added to find duplicates
/** To get the converted record values which have to be display in duplicates merging tpl*/
function getRecordValues($id_array,$module) {
	global $adb,$current_user;
	global $app_strings,$table_prefix;
	$tabid=getTabid($module);
	$query="select fieldname,fieldlabel,uitype from ".$table_prefix."_field where tabid=? and fieldname  not in ('createdtime','modifiedtime') and ".$table_prefix."_field.presence in (0,2) and uitype not in('4')";
	$result=$adb->pquery($query, array($tabid));
	$no_rows=$adb->num_rows($result);

	$focus = CRMEntity::getInstance($module);
	if(isset($id_array) && $id_array !='') {
		foreach($id_array as $value) {
			$focus->id=$value;
			$focus->retrieve_entity_info($value,$module);
			$field_values[]=$focus->column_fields;
		}
	}
	$labl_array=array();
	$value_pair = array();
	$c = 0;
	for($i=0;$i<$no_rows;$i++) {
		$fld_name=$adb->query_result($result,$i,"fieldname");
		$fld_label=$adb->query_result($result,$i,"fieldlabel");
		$ui_type=$adb->query_result($result,$i,"uitype");

		if(getFieldVisibilityPermission($module,$current_user->id,$fld_name) == '0') {
			$fld_array []= $fld_name;
			$record_values[$c][$fld_label] = Array();
			$ui_value[]=$ui_type;
			for($j=0;$j < count($field_values);$j++) {

				if($ui_type ==56) {
					if($field_values[$j][$fld_name] == 0)
						$value_pair['disp_value']=$app_strings['no'];
					else
						$value_pair['disp_value']=$app_strings['yes'];
				} elseif($ui_type == 51 || $ui_type == 50) {
					$entity_id=$field_values[$j][$fld_name];
					if($module !='Products')
						$entity_name=getAccountName($entity_id);
					else
						$entity_name=getProductName($entity_id);
					$value_pair['disp_value']=$entity_name;
				} elseif($ui_type == 53) {
					$owner_id=$field_values[$j][$fld_name];
					$ownername=getOwnerName($owner_id);
					$value_pair['disp_value']=$ownername;
				} elseif($ui_type ==57) {
					$contact_id= $field_values[$j][$fld_name];
					if($contact_id != '') {
						$contactname=getContactName($contact_id);
					}
					$value_pair['disp_value']=$contactname;
				} elseif($ui_type == 75 || $ui_type ==81) {
					$vendor_id=$field_values[$j][$fld_name];
					if($vendor_id != '') {
						$vendor_name=getVendorName($vendor_id);
					}
					$value_pair['disp_value']=$vendor_name;
				} elseif($ui_type == 52) {
					$user_id = $field_values[$j][$fld_name];
					$user_name=getUserName($user_id);
					$value_pair['disp_value']=$user_name;
				} elseif($ui_type ==68) {
					$parent_id = $field_values[$j][$fld_name];
					$value_pair['disp_value'] = getAccountName($parent_id);
					if($value_pair['disp_value'] == '' || $value_pair['disp_value'] == NULL)
						$value_pair['disp_value'] = getContactName($parent_id);
				} elseif($ui_type ==59) {
					$product_name=getProductName($field_values[$j][$fld_name]);
					if($product_name != '')
						$value_pair['disp_value']=$product_name;
					else $value_pair['disp_value']='';
				} elseif($ui_type==58) {
					$campaign_name=getCampaignName($field_values[$j][$fld_name]);
					if($campaign_name != '')
						$value_pair['disp_value']=$campaign_name;
					else $value_pair['disp_value']='';
				} elseif($ui_type == 10) {
					$value_pair['disp_value'] = getRecordInfoFromID($field_values[$j][$fld_name]);
				} else {
					$value_pair['disp_value']=$field_values[$j][$fld_name];
				}
				$value_pair['org_value'] = $field_values[$j][$fld_name];

				array_push($record_values[$c][$fld_label],$value_pair);
			}
			$c++;
		}

	}
	$parent_array[0]=$record_values;
	$parent_array[1]=$fld_array;
	$parent_array[2]=$fld_array;
	return $parent_array;
}
//crmv@8719e

//functions for settings page
/**
 * this function returns the blocks for the settings page
 */
function getSettingsBlocks(){
	global $adb,$table_prefix;
	$sql = "select * from ".$table_prefix."_settings_blocks order by sequence";
	$result = $adb->query($sql);
	$count = $adb->num_rows($result);
	$blocks = array();

	if($count>0){
		for($i=0;$i<$count;$i++){
			$blockid = $adb->query_result($result, $i, "blockid");
			$label = $adb->query_result($result, $i, "label");
			$blocks[$blockid] = $label;
		}
	}
	return $blocks;
}

/**
 * this function returns the fields for the settings page
 */
function getSettingsFields(){
	global $adb,$table_prefix;
	$sql = "select * from ".$table_prefix."_settings_field where blockid!=".getSettingsBlockId('LBL_MODULE_MANAGER')." and active=0 order by blockid,sequence";
	$result = $adb->query($sql);
	$count = $adb->num_rows($result);
	$fields = array();

	if($count>0){
		for($i=0;$i<$count;$i++){
			$blockid = $adb->query_result($result, $i, "blockid");
			$iconpath = $adb->query_result($result, $i, "iconpath");
			$description = $adb->query_result($result, $i, "description");
			$linkto = $adb->query_result($result, $i, "linkto");
			$action = getPropertiesFromURL($linkto, "action");
			$module = getPropertiesFromURL($linkto, "module");
			$name = $adb->query_result($result, $i, "name");
			//crmv@22660
			$formodule = getPropertiesFromURL($linkto, "formodule");
			$fields[$blockid][] = array("icon"=>$iconpath, "description"=>$description, "link"=>$linkto, "name"=>$name, "action"=>$action, "module"=>$module, "formodule"=>$formodule);
			//crmv@22660e
		}

		//add blanks for 4-column layout
		foreach($fields as $blockid=>&$field){
			if(count($field)>0 && count($field)<4){
				for($i=count($field);$i<4;$i++){
					$field[$i] = array();
				}
			}
		}
	}
	return $fields;
}

/**
 * this function takes an url and returns the module name from it
 */
function getPropertiesFromURL($url, $action){
	$result = array();
	preg_match("/$action=([^&]+)/",$url,$result);
	return $result[1];
}

//functions for settings page end


//vtc
function duplicateProduct($productid) {
	global $log, $table_prefix;
	if($productid != "") {
		global $adb;
		$product = CRMEntity::getInstance('Products');
		$product->retrieve_entity_info($productid,"Products");
		$product->mode = "";
		$product->id = "";
		$product->Save("Products");
		$adb->query("update ".$table_prefix."_products set associated = 1 where productid = ".$product->id);

		$log->debug("crmvillage : "."update ".$table_prefix."_products set associated = 1 where productid = ".$product->id);

		return $product->id;
	} else return "";
}

function associateProduct($productid,$crmid,$related_module) {
	if($productid != "" && $crmid != "" && $related_module != "") {
		global $adb,$log, $table_prefix;
		$adb->query("insert into ".$table_prefix."_seproductsrel values (".$crmid.",".$productid.",'".$related_module."')");
		$log->debug("crmvillage : "."insert into ".$table_prefix."_seproductsrel values (".$crmid.",".$productid.",'".$related_module."')");
	}
}

function duplicateAndAssociateProduct($productid,$crmid,$related_module) {
	if($productid != "" && $crmid != "" && $related_module != "") {
		$newproductid = duplicateProduct($productid);
		if($newproductid != "") {
			associateProduct($newproductid,$crmid,$related_module);
		}
	}
}

function GetControllante($accountid) {
	global $adb, $table_prefix;
	if($accountid != "") {
		$result = $adb->limitQuery("
			select
				".$table_prefix."_account_parent.accountid,
				".$table_prefix."_account_parent.accountname,
				".$table_prefix."_account.account_type
			from ".$table_prefix."_account
			inner join ".$table_prefix."_account ".$table_prefix."_account_parent on  ".$table_prefix."_account_parent.accountid = ".$table_prefix."_account.parentid
			where ".$table_prefix."_account.accountid = ".$accountid,0,1);
		if($result) {
			if($row = $adb->fetchByAssoc($result)) {
				return $row;
			} else return null;
		} else return null;
	} else return null;
}

function GetControllati($accountid) {
	if($accountid != "") {
		global $adb, $table_prefix;
		$result = $adb->query("SELECT
							".$table_prefix."_account.accountid,
							".$table_prefix."_account.accountname,
							".$table_prefix."_account.account_type
							FROM ".$table_prefix."_account
							INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_account.accountid
							INNER JOIN ".$table_prefix."_accountbillads ON ".$table_prefix."_accountbillads.accountaddressid = ".$table_prefix."_account.accountid
							LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
							LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
							WHERE
							".$table_prefix."_crmentity.deleted = 0
							AND ".$table_prefix."_account.parentid = ".$accountid);
		if($result) {
			$retval = Array();
			while($row = $adb->fetchByAssoc($result)) {
				$retval[] = $row;
			}
			return $retval;
		} else return null;
	} else return null;
}

function GetHierarchy($accountid) {
	global $app_strings;
	$controllante = GetControllante($accountid);
	if($controllante) {
		$detail_url = "<a href=\"index.php?module=Accounts&action=DetailView&record=".$controllante['accountid']."&parenttab=Sales\"> ".$controllante['accountname']."</a>";
		$html  = "<ul class=\"uil\"><li>$detail_url</li>" ;
	} else {
		$html  = "<ul class=\"uil\"><li>Nessuna controllante</li>" ;
	}

	$html .= "<ul class=\"uil\"><li>".getAccountName($accountid)."<ul>";

	$controllati = GetControllati($accountid);
	for($i=0;$i<count($controllati);$i++) {
		$detail_url = "<a href=\"index.php?module=Accounts&action=DetailView&record=".$controllati[$i]['accountid']."&parenttab=Sales\"> ".$controllati[$i]['accountname']."</a>";
		$html .= "<li>$detail_url</li>";
	}

	$html .= "</ul></ul></ul>";
	return $html;
}
//vtc e

//crmv@8820
function getOwnerId($crmid)
{
    global $log, $table_prefix;
    $log->debug("Entering getOwnerId(".$crmid.") method ...");
    $log->info("in getOwnerId ".$crmid);
    global $adb;
    if($crmid != '')
    {
        $sql = "select smownerid from ".$table_prefix."_crmentity where crmid=?";
        $result = $adb->pquery($sql, array($crmid));
        $smownerid = $adb->query_result($result,0,"smownerid");
    }
    $log->debug("Exiting getOwnerId method ...");
    return $smownerid;
}
function getLastName($userid)
{
    global $log, $table_prefix;
    $log->debug("Entering getLastName(".$userid.") method ...");
    $log->info("in getLastName ".$userid);
    global $adb;
    if($userid != '')
    {
        $sql = "select last_name from ".$table_prefix."_users where id=?";
        $result = $adb->pquery($sql, array($userid));
        $last_name = $adb->query_result($result,0,"last_name");
    }
    $log->debug("Exiting getLastName method ...");
    return $last_name;
}
//crmv@8820e

//crmv@9194
function get_rel_permissions($tabid){
	global $adb, $table_prefix;
	$sql = "select related_tabid,label from ".$table_prefix."_relatedlists where tabid = ?";
	$res = $adb->pquery($sql,Array($tabid));
	while($row = $adb->fetchByAssoc($res)){
		if(isPermitted(getTabModuleName($row['related_tabid']),'EditView','') == 'yes') {
			$ret_arr[$row['label']] = 1;
		}
		else $ret_arr[$row['label']] = 0;
	}
	return $ret_arr;
}
//crmv@9194e
//crmv@10445
/**
 * Find the resulting colour by blending 2 colours
 * and setting an opacity level for the foreground colour.
 *
 * @author J de Silva
 * @link http://www.gidnetwork.com/b-135.html
 * @param string $foreground Hexadecimal colour value of the foreground colour.
 * @param integer $opacity Opacity percentage (of foreground colour). A number between 0 and 100.
 * @param string $background Optional. Hexadecimal colour value of the background colour. Default is: <code>FFFFFF</code> aka white.
 * @return string Hexadecimal colour value. <code>false</code> on errors.
 */
function color_blend_by_opacity( $foreground, $opacity, $background=null )
{
	$foreground = substr($foreground, 1);
    static $colors_rgb=array(); // stores colour values already passed through the hexdec() functions below.

    if( is_null($background) )
        $background = 'FFFFFF'; // default background.

    $pattern = '~^[a-f0-9]{6,6}$~i'; // accept only valid hexadecimal colour values.
    if( !@preg_match($pattern, $foreground)  or  !@preg_match($pattern, $background) )
    {
//        trigger_error( "Invalid hexadecimal colour value(s) found", E_USER_WARNING );
        return false;
    }

    $opacity = intval( $opacity ); // validate opacity data/number.
    if( $opacity>100  || $opacity<0 )
    {
//        trigger_error( "Opacity percentage error, valid numbers are between 0 - 100", E_USER_WARNING );
        return false;
    }

    if( $opacity==100 )    // $transparency == 0
        return strtoupper( $foreground );
    if( $opacity==0 )    // $transparency == 100
        return strtoupper( $background );
    // calculate $transparency value.
    $transparency = 100-$opacity;

    if( !isset($colors_rgb[$foreground]) )
    { // do this only ONCE per script, for each unique colour.
        $f = array(  'r'=>hexdec($foreground[0].$foreground[1]),
                     'g'=>hexdec($foreground[2].$foreground[3]),
                     'b'=>hexdec($foreground[4].$foreground[5])    );
        $colors_rgb[$foreground] = $f;
    }
    else
    { // if this function is used 100 times in a script, this block is run 99 times.  Efficient.
        $f = $colors_rgb[$foreground];
    }

    if( !isset($colors_rgb[$background]) )
    { // do this only ONCE per script, for each unique colour.
        $b = array(  'r'=>hexdec($background[0].$background[1]),
                     'g'=>hexdec($background[2].$background[3]),
                     'b'=>hexdec($background[4].$background[5])    );
        $colors_rgb[$background] = $b;
    }
    else
    { // if this FUNCTION is used 100 times in a SCRIPT, this block will run 99 times.  Efficient.
        $b = $colors_rgb[$background];
    }

    $add = array(    'r'=>( $b['r']-$f['r'] ) / 100,
                     'g'=>( $b['g']-$f['g'] ) / 100,
                     'b'=>( $b['b']-$f['b'] ) / 100    );

    $f['r'] += intval( $add['r'] * $transparency );
    $f['g'] += intval( $add['g'] * $transparency );
    $f['b'] += intval( $add['b'] * $transparency );

    return "#".sprintf( '%02X%02X%02X', $f['r'], $f['g'], $f['b'] );
}
//crmv@10445e
//crmv@10488
function check_notification_scheduler($id){
	global $adb, $table_prefix;
	$sql ="select active from ".$table_prefix."_notifyscheduler where schedulednotificationid = ?";
	$res = $adb->pquery($sql,array($id));
	if ($res){
		$active = $adb->query_result($res,0,'active');
	}
	return $active;
}
//crmv@10488 e
//crmv@10759
function get_navigation_values($list_query_count,$url_string,$currentModule,$type='',$forusers=false,$viewid = ''){
	require_once('include/ListView/ListView.php');
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
	$res = $adb->query(replaceSelectQuery($list_query_count,$parameter));
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
function get_allids($list_query_count,$ids_to_jump = false){
	require_once('include/ListView/ListView.php');
	global $adb,$app_strings,$list_max_entries_per_page,$currentModule,$current_user, $table_prefix;	//crmv@27096
	if ($forusers)
		$parameter = $table_prefix.'_users.id as crmid';
	else
		$parameter = $table_prefix."_crmentity.crmid";
	if (!$list_query_count)
		return Zend_Json::encode(Array('all_ids'=>false));
	//crmv@27096
	$mod_obj = CRMEntity::getInstance($currentModule);
	$mod_obj->getNonAdminAccessControlQuery($currentModule,$current_user);
	//crmv@27096e
	$query = replaceSelectQuery($list_query_count,$parameter);
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
//crmv@10759
//Make a count query
function replaceSelectQuery($query,$replace = "count(*) AS count",$group_by=false)
{
	// Remove all the \n, \r and white spaces to keep the space between the words consistent.
	// This is required for proper pattern matching for words like ' FROM ', 'ORDER BY', 'GROUP BY' as they depend on the spaces between the words.
	$query = preg_replace("/[\n\r\t]+/"," ",$query); //crmv@20049

	//Strip of the current SELECT fields and replace them by "select count(*) as count"
	// Space across FROM has to be retained here so that we do not have a clash with string "from" found in select clause
	//crmv@26753
	if (preg_match('/SELECT\s+distinct\s+/i', $query) && !preg_match('/ distinct /i', $replace)) { // there's a distinct
		if (preg_match('/count\(\*\)/i', $replace)) { // is a count query
			// get select arguments
			$args = array();
			preg_match('/^\s*select\s+distinct\s+(.*) from/i', $query, $args);
			if (count($args) > 1 && !empty($args[1])) {
				$listargs = explode(',', trim($args[1]));
				foreach ($listargs as $k=>$arg) {
					// search for a crmid
					if (stripos($arg, 'crmid') !== false) {
						$replace = "COUNT(DISTINCT $arg)";
						break;
					}
				}
			}
		} else { // not a count query
			$replace = "DISTINCT $replace";
		}
	}
	//crmv@26753e
	$query = "SELECT $replace ".substr($query, stripos($query,' FROM '),strlen($query));

	//Strip of any "GROUP BY" clause
	//    if ($group_by){
	//    	if(stripos($query,'GROUP BY') > 0)
	//		$query = substr($query, 0, stripos($query,'GROUP BY'));
	//	}
	//Strip of any "ORDER BY" clause
	if(strripos($query,'ORDER BY') > 0)
	$query = substr($query, 0, strripos($query,'ORDER BY'));

	//That's it
	return( $query);
}
//crmv@10759 e

//crmv@9587
function get_hidden_parenttab_array(){
	global $adb,$current_user,$table_prefix;
	require('user_privileges/user_privileges_'.$current_user->id.'.php');
	//ds@23
	$sql = 'SELECT hidden,parenttabid FROM '.$table_prefix.'_parenttab';
	$res = $adb->query($sql);
	while($row = $adb->fetch_array($res)) {
		$id[$row['parenttabid']] = $row['hidden'];
	}
	//ds@23e
	return $id;
}
//crmv@9587 e
function getMemoryUsage($bytestoadd)
{
	return round((memory_get_usage() + $bytestoadd) / (1024*1024), 1);
}
function array_size($a){
    $size = 0;
    while(list($k, $v) = each($a)){
        $size += is_array($v) ? array_size($v) : strlen($v);
    }
    return $size;
}
function array_search_recursive($needle, $haystack){
    $path=array();
    foreach($haystack as $id => $val)
    {

         if($val === $needle)
              $path[]=$id;
         else if(is_array($val)){
             $found=array_search_recursive($needle, $val);
              if(count($found)>0){
                  $path[$id]=$found;
              }
          }
      }
      return $path;
}
function get_logo($mode){
	include_once('vtigerversion.php');
	global $enterprise_mode,$enterprise_project;
	$logo_path = 'themes/logos/';
	if ($mode == 'favicon')
		$extension = 'ico';
	else
		$extension = 'png';
	if ($mode == 'project')
		$logo_path.=$enterprise_project.".".$extension;
	else
		$logo_path.=$enterprise_mode."_".$mode.".".$extension;
	return $logo_path;
}
function reflect_logo($mode){
	include_once('config.inc.php');
	global $reflection_logo;
	switch ($mode){
		case "rowspan":{
			return "3";
			break;
		}
		case "reflect":{
			if ($reflection_logo)
				return 'jQuery("#logo").reflect(1);';
			break;
		}
	}
}

//crmv@23984
function get_merge_user_fields($module,$ajax=false){
	if (!$ajax){
		return Array();
	}
	global $adb,$current_user, $table_prefix;
	if (isPermitted($module,'DuplicatesHandling','') != 'yes') return Array();
	$module_tabid = getTabid($module);
	if($module_tabid =='' || $current_user->id =='')
		return Array();

	$sql="SELECT
			  ".$table_prefix."_field.fieldid,
			  ".$table_prefix."_field.tablename,
			  ".$table_prefix."_field.columnname,
			  ".$table_prefix."_field.fieldname,
			  ".$table_prefix."_field.fieldlabel,
			  ".$table_prefix."_field.uitype
			FROM ".$table_prefix."_user2mergefields
			  INNER JOIN ".$table_prefix."_field
			    ON ".$table_prefix."_field.fieldid = ".$table_prefix."_user2mergefields.fieldid
			WHERE ".$table_prefix."_user2mergefields.tabid = ?
			    AND ".$table_prefix."_user2mergefields.userid = ?
			    AND ".$table_prefix."_user2mergefields.visible = 1";
	$params=array($module_tabid,$current_user->id);
	$res=$adb->pquery($sql,$params);
	$num_rows = $adb->num_rows($res);
	$user_profileid = fetchUserProfileId($current_user->id);
	$permitted_list = getProfile2FieldPermissionList($module, $user_profileid);
	$sql_def_org="select fieldid from ".$table_prefix."_def_org_field where tabid=? and visible=0";
	$result_def_org=$adb->pquery($sql_def_org,array($module_tabid));
	$num_rows_org=$adb->num_rows($result_def_org);
	$permitted_org_list = Array();
	for($i=0; $i<$num_rows_org; $i++){
		$permitted_org_list[$i] = $adb->query_result_no_html($result_def_org,$i,"fieldid");
	}
	require('user_privileges/user_privileges_'.$current_user->id.'.php');
	$fieldname = Array();
	for($i=0; $i<$num_rows;$i++)
	{
		$field_id = $adb->query_result_no_html($res,$i,"fieldid");
		$field_colname = $adb->query_result_no_html($res,$i,"columnname");
		$field_fieldname = $adb->query_result_no_html($res,$i,"fieldname");
		$field_tablename = $adb->query_result_no_html($res,$i,"tablename");
		$field_fieldlabel = $adb->query_result_no_html($res,$i,"fieldlabel");
		$field_uitype= $adb->query_result_no_html($res,$i,"uitype");
		foreach($permitted_list as $field=>$data)
			if($data[4] == $field_id and $data[1] == 0)
			{
				if($is_admin == 'true' || (in_array($field_id,$permitted_org_list)))
				{
					$fieldname[] = Array(
						'fieldname' => $field_fieldname,
						'columnname' => $field_colname,
						'tablename' => $field_tablename,
						'fieldlabel' => $field_fieldlabel,
						'uitype' => $field_uitype,
					);
				}
			}
	}
	return $fieldname;
}
function check_duplicate($module,$fieldvalues,$record=''){
	require_once("include/Zend/Json.php");
	global $adb;
	$obj = CRMEntity::getInstance($module);
	$query = getListQuery($module);
	$query = preg_replace("/[\n\r\t]+/"," ",$query); //crmv@20049
	$params=array();
	$data = Array();
	$fieldvalues = Zend_Json::decode($fieldvalues);
	foreach ($fieldvalues as $arr){
		if (stripos($query," join {$arr['tablename']} ")!==false || stripos($query," from {$arr['tablename']} ")!==false){
			$query.=" AND {$arr['tablename']}.{$arr['columnname']}=?";
			array_push($params, $arr['value']);
			$data[getTranslatedString($arr['fieldlabel'],$module)] = $arr['value'];
		}
	}
	if ($record != ''){
		$query.=" and {$obj->table_name}.{$obj->table_index} <> ?";
		array_push($params,$record);
	}
	$result = $adb->pquery($query, $params);
    if($adb->num_rows($result) == 0){
		$data = Array();
	}
	return $data;
}
//crmv@23984e
//crmv@27811
function add_brakets(&$key,&$item){
	if(in_array($key,getMsSQLReservedWords())) {
		$key = '["'.$key.'"]';
	} else {
		$key = '['.$key.']';
	}
}
//crmv@27811e
//crmv@24791
function add_doublequotes(&$key,&$item){
//	if(in_array($key,Array('comment','session','uid')))
	if(in_array($key,getOracleReservedWords())) {
		$key = '"'.$key.'"';
	}
}
//crmv@24791e
//crmv@26687
function add_backtick(&$key,&$item){
	$key = "`{$key}`";
}
//crmv@26687e
function getIP() {
	if (getenv("HTTP_CLIENT_IP"))
		$ip = getenv("HTTP_CLIENT_IP");
	else if(getenv("HTTP_X_FORWARDED_FOR"))
		$ip = getenv("HTTP_X_FORWARDED_FOR");
	else if(getenv("REMOTE_ADDR"))
		$ip = getenv("REMOTE_ADDR");
	else
		$ip = "UNKNOWN";
	return $ip;
}
function convert_to_old_condition($cond){
	switch($cond){
		case 'e':
			return 'is';
		case 'n':
			return 'isn';
		case 's':
			return 'bwt';
		case 'ew':
			return 'ewt';
		case 'c':
			return 'cts';
		case 'k':
			return 'dcts';
		case 'l':
			return 'lst';
		case 'g':
			return 'grt';
		case 'm':
			return 'lsteq';
		case 'h':
			return 'grteq';
	}
	return $cond;
}
//crmv@19370
function replaceSelectQueryFromList($module,$instance,$query){
	global $adb,$current_user,$currentModule, $table_prefix;	//crmv@16532
//crmv@19370e
	$queryGenerator = QueryGenerator::getInstance($module, $current_user);
	$fields_to_jump =  Array('access_count','filename','idlists');
	$moduleFields = $queryGenerator->getModuleFields();
	$fields = array_values(array_diff($instance->list_fields_name,$fields_to_jump));
	foreach ($moduleFields as $name){
		$fname = $name->getFieldName();
		if (in_array($fname,$fields) && strpos($query,$name->getTableName())!==false && !($module == 'Calendar' && ($name->getTableName() == $table_prefix.'_activity'))){
			$field_list[] = $fname;
		}
	}
	$queryGenerator->setFields($field_list);
	$columns = $queryGenerator->getQuery(true);
	//crmv@18124
	if ($module == 'Calendar'){
		//crmv@33982 
		if (in_array($table_prefix.'_seactivityrel.crmid',$columns)){
			unset($columns[array_search($table_prefix.'_seactivityrel.crmid',$columns)]);
		}
		//crmv@33982e		
		if (!in_array($table_prefix.'_activity.*',$columns)){
			foreach($columns as $key=>$value){
				if(strpos($value,$table_prefix.'_activity') !== false){
					unset($columns[$key]);
				}
			}
			$columns[] = $table_prefix.'_activity.*';
		}
		//crmv@31420
		if (!in_array($table_prefix.'_contactdetails.lastname',$columns) && strpos($table_prefix.'_contactdetails',$query)!==false){
			$columns[] = $table_prefix.'_contactdetails.lastname';
		}	
		if (!in_array($table_prefix.'_contactdetails.firstname',$columns) && strpos($table_prefix.'_contactdetails',$query)!==false){
			$columns[] = $table_prefix.'_contactdetails.firstname';
		}
		//crmv@31420e	
//		$columns.=",vtiger_activity.*,CASE
//		    WHEN (vtiger_users.user_name IS NOT NULL)
//		    THEN vtiger_users.user_name
//		    ELSE vtiger_groups.groupname
//		  END AS user_name";
	}
	//crmv@16532
	if ($currentModule == 'Campaigns' && in_array($module,array('Accounts','Contacts','Leads'))){
		$columns []= $table_prefix.'_campaignrelstatus.*';
	}
	//crmv@16532e
	//crmv@22863
	if($module == 'Timecards'){
		$columns []= $table_prefix.'_timecards.product_id';
	}
	//crmv@22863e
	//crmv@18001
	if ($module == 'Documents') {
		if (!in_array($table_prefix.'_notes.filename',$columns))
			$columns []= $table_prefix.'_notes.filename';
		if (!in_array($table_prefix.'_notes.folderid',$columns))
			$columns []= $table_prefix.'_notes.folderid';
		if (!in_array($table_prefix.'_notes.filelocationtype',$columns))
			$columns []= $table_prefix.'_notes.filelocationtype';
		if (!in_array($table_prefix.'_notes.filestatus',$columns))
			$columns []= $table_prefix.'_notes.filestatus';
	}
	//crmv@18001e
	if (!in_array($table_prefix.'_crmentity.crmid',$columns))
		$columns[] = $table_prefix.'_crmentity.crmid';
	if (in_array($table_prefix.'_crmentity.description',$columns)) {
		$i=0;
		foreach( $columns as $col) {
			if($col==$table_prefix.'_crmentity.description') {
				$columns[$i] = "convert(varchar(255),".$table_prefix."_crmentity.description) as description";
			}
			$i++;
		}
	}
	return replaceSelectQuery($query,implode(",",$columns));
	//crmv@18124 end
}

//crmv@17001
function getOccupation($user,$recordid,$date_start,$due_date,$start_hour,$end_hour,$specific_time='') {

	if ($recordid == '' && $specific_time == '') return false;	//create mode

	if ($user == '') {
		$tmp = getRecordOwnerId($recordid);
		$user = $tmp['Users'];
	}
	if ($user == '') return false;
	//crmv@23833
	$date_start = substr($date_start,0,10);
	$due_date = substr($due_date,0,10);
	if (strtotime($due_date) < strtotime($date_start) ){
		return Array();
	}
	//crmv@23833 end
	$date = $date_start;
	$occupation = array();
	while ($date != $due_date) {
		$occupation[] = array('label'=>getDisplayDate($date),'occupation'=>getOccupationDay($user,$recordid,$date,$start_hour,$end_hour,$specific_time));
		$date = date('Y-m-d', strtotime("+1 day",strtotime($date)));
	}
	if ($date == $due_date) {
		$occupation[] = array('label'=>getDisplayDate($date),'occupation'=>getOccupationDay($user,$recordid,$date,$start_hour,$end_hour,$specific_time));
	}
	return $occupation;
}

function getOccupationDay($user,$recordid,$date,$start_hour,$end_hour,$specific_time='') {
	global $adb,$current_user,$mod_strings,$table_prefix;
	require_once('modules/Calendar/Date.php');

	$start_hour_time = mktime($start_hour,0,0,0,0,0);
	$start_hour = date('H:i', $start_hour_time);

	$end_hour_time = mktime($end_hour,0,0,0,0,0);
	$end_hour = date('H:i', $end_hour_time);

	$focus = CRMEntity::getInstance('Users');
	$focus->retrieve_entity_info($current_user->id,'Users');
	$focus->id = $current_user->id;

	$queryGenerator = QueryGenerator::getInstance('Calendar', $focus);
	$queryGenerator->initForDefaultCustomView();
	$list_query = $queryGenerator->getQuery();
	$list_query .= " AND ((".$table_prefix."_activity.date_start between ? AND ?)
		                OR (".$table_prefix."_activity.date_start < ? AND ".$table_prefix."_activity.due_date > ?)
		                OR (".$table_prefix."_activity.due_date between ? AND ?))";
	$phpTime = strtotime($date);
	$from_datetime = mktime(0, 0, 0, date("m", $phpTime), date("d", $phpTime), date("Y", $phpTime));
	$from_datetime = array('ts'=>$from_datetime);
	$from_datetime = new vt_DateTime($from_datetime,true);
    $to_datetime = mktime(0, 0, -1, date("m", $phpTime), date("d", $phpTime)+1, date("Y", $phpTime));
    $to_datetime = array('ts'=>$to_datetime);
    $to_datetime = new vt_DateTime($to_datetime,true);
	$params = array(
					$adb->formatDate($from_datetime->get_formatted_date(), true),
					$adb->formatDate($to_datetime->get_formatted_date(), true),
					$adb->formatDate($from_datetime->get_formatted_date(), true),
					$adb->formatDate($to_datetime->get_formatted_date(), true),
					$adb->formatDate($from_datetime->get_formatted_date(), true),
					$adb->formatDate($to_datetime->get_formatted_date(), true)
					);
	$list_query .= " AND ".$table_prefix."_activity.activitytype <> 'Task'";
	$list_query .= " AND (
						".$table_prefix."_crmentity.smownerid = $user
						OR ".$table_prefix."_activity.activityid IN(SELECT activityid FROM ".$table_prefix."_invitees WHERE inviteeid = $user)";
	//crmv@31422
	/*					
	if ($user == $current_user->id){
		$list_query .= " OR ".$table_prefix."_activity.visibility = 'Public'";
	}
	*/
	//crmv@31422e	
	$list_query .= ")";
//	echo $adb->convert2Sql($list_query,$adb->flatten_array($params));
	$result = $adb->pquery($list_query,$params);
	$activities = array();
	while($row=$adb->fetchByAssoc($result)) {
		$activities[] = $row['activityid'];
	}
	if ($recordid != '') $activities[] = $recordid;
	$activities = implode(',',$activities);

	$i = 0;
	$previous_time_end[$i] = $start_hour;
	$tr = array();
	if ($activities != '') {
		$result = $adb->query("select * from ".$table_prefix."_activity
								inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_activity.activityid
								where activityid in ($activities) order by time_start");
		while($row=$adb->fetchByAssoc($result)) {
			getOccupationActivity($recordid,$start_hour,$row,$date,$specific_time,$i,$tr,$previous_time_end);
		}
	}
	//create mode - i
	if ($recordid == '' && $specific_time != '') {
		$row = $specific_time;
		$row['activityid'] = 'new';
		getOccupationActivity('',$start_hour,$row,$date,$specific_time,$i,$tr,$previous_time_end);
	}
	//create mode - e
	$tr_tmp = $tr;
	$tr = array();
	foreach ($tr_tmp as $r => $activities) {
		foreach ($activities as $activityid => $other_info) {

			if ($recordid == $activityid || $activityid == 'new')
				$class = 'current_activity';
			else
				$class = 'busy';

			$info = array();
			if ($recordid == $activityid || $activityid == 'new') {
				$info['subject'] = $mod_strings['Current Event'];
				$info['date'] = '';
				$info['other_info'] = '';
			}
			elseif ($other_info['info']['visibility'] == 'Private') {
				$info['subject'] = $mod_strings['Private Event'];
				$info['date'] = $other_info['info']['date_start'].' '.$other_info['info']['time_start'].' - '.$other_info['info']['due_date'].' '.$other_info['info']['time_end'];
				$info['other_info'][] = 'Tipo evento: '.$mod_strings[$other_info['info']['activitytype']];
			}
			else {
				$info['subject'] = $other_info['info']['subject'];
				$info['date'] = $other_info['info']['date_start'].' '.$other_info['info']['time_start'].' - '.$other_info['info']['due_date'].' '.$other_info['info']['time_end'];
				$info['other_info'][] = 'Tipo evento: '.$mod_strings[$other_info['info']['activitytype']];
				$info['other_info'][] = 'Descrizione: '.$other_info['info']['description'];
				$info['other_info'][] = 'Luogo: '.$other_info['info']['location'];
			}

			$tr[$r][] = array('type'=>'free','colspan'=>($other_info['gap']*2),'info'=>Zend_Json::encode(Array())); //crmv@32837
			$tr[$r][] = array('type'=>$class,'colspan'=>($other_info['duration']*2),'info'=>Zend_Json::encode($info));
		}
	}
	return $tr;
}

function getDurationHours($start,$end) {

	$start = explode(':',$start);
	$start = mktime($start[0],$start[1],0,0,0,0);
	$end = explode(':',$end);
	if ($end[0] == 0) $end[0] = 24;
	$end = mktime($end[0],$end[1],0,0,0,0);
	//echo (($end - $start)/60/60).'<br />';
	return ($end - $start)/60/60;
}

function getOccupationActivity($recordid,$start_hour,$row,$date,$specific_time,&$i,&$tr,&$previous_time_end) {

	$time_start = $row['time_start'];
	$time_end = $row['time_end'];
	$date_start = $row['date_start'];
	$due_date = $row['due_date'];

	if ($row['is_all_day_event'] == 1) {
		$time_start = '00:00';
		$time_end = '00:00';
	}
	if ($specific_time != '' && $recordid == $row['activityid']) {
		$time_start = $specific_time['time_start'];
		$time_end = $specific_time['time_end'];
		$date_start = $specific_time['date_start'];
		$due_date = $specific_time['due_date'];
	}
	if (strtotime($date_start) < strtotime($date))
		$time_start = '00:00';
	if (strtotime($due_date) > strtotime($date))
		$time_end = '00:00';

	for ($j=0;$j<=$i;$j++) {
		$gap = getDurationHours($previous_time_end[$j],$time_start);
		//echo $row['activityid'].' '.$j.' '.$i.' "'.$previous_time_end[$j].'" "'.$time_start.'" '.$gap.'<br />';
		if ($time_start == '00:00') {
			$gap = 0;
			if ($time_end == '00:00') {
				$i++;
				$duration = 24;
				$previous_time_end[$i] = '24:00';
				$tr[$i][$row['activityid']] = array('gap'=>$gap,'duration'=>$duration,'info'=>$row);
			}
			else {
				if (strtotime($time_start) < strtotime($previous_time_end[$j])) {
					$i++;
					$k = $i++;
				}
				else
					$k = $j;
				$duration = getDurationHours($time_start,$time_end);
				if ($time_end == '00:00') $previous_time_end[$k] = '24:00';
				else $previous_time_end[$k] = $time_end;
				$tr[$k][$row['activityid']] = array('gap'=>$gap,'duration'=>$duration,'info'=>$row);
			}
			break;
		}
		elseif ($gap > 0) {
			$duration = getDurationHours($time_start,$time_end);
			$tr[$j][$row['activityid']] = array('gap'=>$gap,'duration'=>$duration,'info'=>$row);

			if ($time_end == '00:00') $previous_time_end[$j] = '24:00';
			else $previous_time_end[$j] = $time_end;
			break;
		}
		elseif ($j == $i) {
			$i++;
			$duration = getDurationHours($time_start,$time_end);
			$gap = getDurationHours($start_hour,$time_start);
			$tr[$i][$row['activityid']] = array('gap'=>$gap,'duration'=>$duration,'info'=>$row);

			if ($time_end == '00:00') $previous_time_end[$i] = '24:00';
			else $previous_time_end[$i] = $time_end;
			break;
		}
	}
}
//crmv@17001e

//crmv@16265	//crmv@26265
function getLinkMenuSquirrel($language,$passed_id,$mailbox,$startMessage,$passed_ent_id,$smaction,$t='',$fromsearch='') {//crmv@21994 //crmv@22154
	global $adb,$table_prefix;
	$result = getModulesToEmail();
	$html = '<div onmouseover="fnShowDrop(\'Link_sub'.$t.'\')" onmouseout="fnHideDrop(\'Link_sub'.$t.'\')" id="Link_sub'.$t.'" class="drop_mnu" style="left: 387px; top: 68px; display: none;">';//crmv@21994
	$html .= '<table cellspacing="0" cellpadding="0" border="0" width="100%">';
	while ($row = $adb->fetchByAssoc($result,-1,false)) {
		$link = ''; //crmv@26510
		if ($fromsearch != 'fromsearch') {
			$link .= 'include/squirrelmail/src/';
		}
		if ($row['name'] == 'Calendar') {
			$result1 = $adb->query("SELECT cvid, viewname FROM ".$table_prefix."_customview WHERE entitytype = 'Calendar' AND viewname IN ('Events','Tasks') AND status = 0");
			while($row1=$adb->fetchByAssoc($result1)) {
				$link .= 'compose.php?vte_type=link&vte_module='.$row['name'].'&passed_id='.$passed_id.'&mailbox='.$mailbox.'&startMessage='.$startMessage.'&passed_ent_id='.$passed_ent_id.'&smaction='.$smaction.'&viewname='.$row1['cvid'];//crmv@21048m
				$moduleName = getTranslatedStringSquirrel('LBL_'.strtoupper(substr($row1['viewname'],0,-1)),$row['name']);
				$html .= '<tr><td><a class="drop_down" href="javascript:void(0);" onclick="openPopUpSquirrel(\'xLinkEmail\',this,\''.$link.'\',\'createemailWin\',830,662,\'menubar=no,toolbar=no,location=no,status=no,resizable=yes,scrollbars=yes\',\''.$fromsearch.'\')">'.$moduleName.'</a></td></tr>';//crmv@22154
			}
		} else {
			$link .= 'compose.php?vte_type=link&vte_module='.$row['name'].'&passed_id='.$passed_id.'&mailbox='.$mailbox.'&startMessage='.$startMessage.'&passed_ent_id='.$passed_ent_id.'&smaction='.$smaction;//crmv@21048m
			$moduleName = getSingleModuleName($row['name']);
			$html .= '<tr><td><a class="drop_down" href="javascript:void(0);" onclick="openPopUpSquirrel(\'xLinkEmail\',this,\''.$link.'\',\'createemailWin\',830,662,\'menubar=no,toolbar=no,location=no,status=no,resizable=yes,scrollbars=yes\',\''.$fromsearch.'\')">'.$moduleName.'</a></td></tr>';//crmv@22154
		}
	}
	$html .= '</table></div>';
	return $html;
}
function getModulesToEmail($modules=array()) {	//crmv@29506
	global $adb, $table_prefix;
	$query = 'SELECT '.$table_prefix.'_tab.tabid, '.$table_prefix.'_tab.name
				FROM '.$table_prefix.'_relatedlists
				INNER JOIN '.$table_prefix.'_tab ON '.$table_prefix.'_tab.tabid = '.$table_prefix.'_relatedlists.tabid
				WHERE related_tabid = 10 AND '.$table_prefix.'_tab.presence = 0';	//crmv@23804
	//crmv@29506
	if (!empty($modules)) {
		$query .= ' AND '.$table_prefix.'_tab.name IN ('.generateQuestionMarks($modules).')';
	}
	$query .= ' ORDER BY '.$table_prefix.'_tab.name';
	return $adb->pquery($query,$modules);
	//crmv@29506e
}
function getRelationEmailInfo($passed_id,$draft=false,$mailid='',$folder='') {	//crmv@32535

	global $adb, $current_user, $table_prefix;

	//crmv@30909	//crmv@32535
	if ($folder == '') {
		$folder = $_REQUEST['mailbox'];
	}
	//crmv@30909e	//crmv@32535e
	
	if ($current_user->id != '')
		$user = $current_user->id;
	elseif ($_SESSION['vte']['current_user']['record_id'] != '')
		$user = $_SESSION['vte']['current_user']['record_id'];

	$tmp = array();
	if ($passed_id != '') {
		global $adb;

		$list = false;
		if (is_array($passed_id) && !empty($passed_id)) {
			$list = true;
			$passed_id = array_filter($passed_id);
			$passed_id = implode(',',$passed_id);
		}

		$query = '';
		if ($list)
			$query .= "SELECT crmv_squirrelmailrel.imap_id";
		else
			$query .= "SELECT ".$table_prefix."_seactivityrel.crmid, crmEntity.setype";
		$query .= "	FROM crmv_squirrelmailrel
					INNER JOIN ".$table_prefix."_activity ON ".$table_prefix."_activity.activityid = crmv_squirrelmailrel.mail_id AND ".$table_prefix."_activity.activitytype = 'Emails'
					INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_activity.activityid
					INNER JOIN ".$table_prefix."_seactivityrel ON ".$table_prefix."_seactivityrel.activityid = ".$table_prefix."_activity.activityid
					INNER JOIN ".$table_prefix."_crmentity crmEntity ON crmEntity.crmid = ".$table_prefix."_seactivityrel.crmid
					WHERE crmv_squirrelmailrel.user_id = ".$user;
		if ($list)
			$query .= " AND crmv_squirrelmailrel.imap_id in ($passed_id)";
		else
			$query .= " AND crmv_squirrelmailrel.imap_id = $passed_id";

		$query .= "		AND ".$table_prefix."_crmentity.deleted = 0
						AND crmEntity.deleted = 0";
//		$query .= "		AND crmEntity.setype = '".$module."'";
		if ($draft)
			$query .= " AND crmv_squirrelmailrel.type = 'draft'";
		//crmv@30909
		$query .= " AND crmv_squirrelmailrel.folder = '$folder' ";
		//crmv@30909e
		if ($list)
			$query .= " GROUP BY crmv_squirrelmailrel.imap_id";
		else
			$query .= " ORDER BY crmEntity.setype";

		$result = $adb->query($query);
		if ($result && $adb->num_rows($result)) {
			while($row=$adb->fetchByAssoc($result)) {
				if ($list) {
					$tmp[] = $row['imap_id'];
				} else {
					$tmp[$row['setype']][] = $row['crmid'];
				}
			}
		}
	}
	if ($mailid != '') {
		$query = "SELECT
				  ".$table_prefix."_seactivityrel.crmid,
				  crmEntity.setype
				FROM ".$table_prefix."_seactivityrel
				  INNER JOIN ".$table_prefix."_activity
				    ON ".$table_prefix."_activity.activityid = ".$table_prefix."_seactivityrel.activityid
				      AND ".$table_prefix."_activity.activitytype = 'Emails'
				  INNER JOIN ".$table_prefix."_crmentity
				    ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_activity.activityid
				  INNER JOIN ".$table_prefix."_crmentity crmEntity
				    ON crmEntity.crmid = ".$table_prefix."_seactivityrel.crmid
				WHERE ".$table_prefix."_seactivityrel.activityid = $mailid
				    AND ".$table_prefix."_crmentity.deleted = 0
				    AND crmEntity.deleted = 0
				ORDER BY crmEntity.setype";
		$result = $adb->query($query);
		if ($result && $adb->num_rows($result)) {
			while($row=$adb->fetchByAssoc($result)) {
				if (empty($tmp[$row['setype']]) || !in_array($row['crmid'],$tmp[$row['setype']])) {
					$tmp[$row['setype']][] = $row['crmid'];
				}
			}
		}
	}
	return $tmp;
}
function getLinkMenuWebmail($mailid) {
	global $adb,$table_prefix;
	$result = getModulesToEmail();
	$html = '<div onmouseover="fnShowDrop(\'Link_sub\')" onmouseout="fnHideDrop(\'Link_sub\')" id="Link_sub" class="drop_mnu" style="left: 387px; top: 68px; display: none;">';//crmv@21994
	$html .= '<table cellspacing="0" cellpadding="0" border="0" width="100%">';
	while ($row = $adb->fetchByAssoc($result,-1,false)) {

//		$querystr="SELECT fieldid,fieldname,columnname,tablename,fieldlabel FROM vtiger_field WHERE tabid=? and uitype=13 and vtiger_field.presence in (0,2)";
//		$queryres = $adb->pquery($querystr, array(getTabid($row['name'])));
//		if ($queryres && $adb->num_rows($queryres) > 0) {
//			$fieldname = $adb->query_result($queryres,0,'fieldname');
//		}
//		$focusEmail = CRMEntity::getInstance('Emails');
//		$focusEmail->retrieve_entity_info($mailid,"Emails");
//		$from_email = $focusEmail->column_fields['from_email'];
//		$search_str = '&query=true&search_field='.$fieldname.'&&searchtype=BasicSearch&search_text='.$from_email;

		if ($row['name'] == 'Calendar') {
			$result1 = $adb->query("SELECT cvid, viewname FROM ".$table_prefix."_customview WHERE entitytype = 'Calendar' AND viewname IN ('Events','Tasks') AND status = 0");
			while($row1=$adb->fetchByAssoc($result1)) {
				$link = 'openPopup(\'index.php?module='.$row['name'].'&action=Popup&popuptype=squirrel_mail&return_module=Webmails&recordid='.$mailid.$search_str.'&viewname='.$row1['cvid'].'\');';
				$moduleName = getTranslatedString('LBL_'.strtoupper(substr($row1['viewname'],0,-1)),$row['name']);
				$html .= '<tr><td><a class="drop_down" href="javascript:void(0);" onclick="'.$link.'">'.$moduleName.'</a></td></tr>';//crmv@22154
			}
		} else {
			$link = 'openPopup(\'index.php?module='.$row['name'].'&action=Popup&popuptype=squirrel_mail&recordid='.$mailid.$search_str.'\');';
			$moduleName = getTranslatedString('SINGLE_'.$row['name']);
			if ($moduleName == '') {
				$moduleName = getTranslatedString($row['name'],$row['name']);
			}
			$html .= '<tr><td><a class="drop_down" href="javascript:void(0);" onclick="'.$link.'">'.$moduleName.'</a></td></tr>';//crmv@22154
		}
	}
	$html .= '</table></div>';
	return $html;
}
function getEmailLinks($passed_id,$fromSquirrel=true,$mailid='') {
	global $site_URL, $show_more, $show_more_cc, $show_more_bcc;	//crmv@26512
	$string = '';
	$res = getRelationEmailInfo($passed_id,false,$mailid);
	if(!empty($res)) {
		foreach($res as $module => $ids) {
			if ($fromSquirrel) {
				$style = 'class="mailHeader"';
				if (!$show_more && !$show_more_cc && !$show_more_bcc) {
					$style .= ' style="display: none;"';
				}
			}
			$string .= '<tr bgcolor="'.$color[17].'" '.$style.'><td width="10%"></td>'; //crmv@26510
			if (function_exists('getTranslatedStringSquirrel')) {
				$module_name = getTranslatedStringSquirrel($module,$module);
				if ($module_name == '') {
					$module_name = getTranslatedStringSquirrel($module,'APP_STRINGS');
				}
			} else {
				$module_name = getTranslatedString($module,$module);
			}
			$string .= '<td width="20%" align="right">';
			if (!$fromSquirrel) {
				$string .= '<img border="0" src="themes/images/fbLink.gif" align="absmiddle">';
			} else {
				$string .= '<img border="0" src="../../../themes/images/fbLink.gif" align="absmiddle">';
			}
			$string .= '<b>'.$module_name.':&nbsp;&nbsp;</b>
					</td>
					<td width="70%">'; //crmv@26510
			$links = array();
			foreach($ids as $id) {
				$tmp = getEntityName($module,array($id));
				$entity_name = $tmp[$id];
				if (!$fromSquirrel) {
					$links[] = '<a href="'.$site_URL.'/index.php?module='.$module.'&action=DetailView&record='.$id.'" onclick="parent.location.href=\''.$site_URL.'/index.php?module='.$module.'&action=DetailView&record='.$id.'\'">'.$entity_name.'</a>';
				} else {
					$links[] = '<a href="../../../index.php?module='.$module.'&action=DetailView&record='.$id.'" onclick="parent.parent.location.href=\'../../../index.php?module='.$module.'&action=DetailView&record='.$id.'\'">'.$entity_name.'</a>';
				}
			}
			$string .= implode(' | ',$links);
			$string .= '</td></tr>';
		}
	}
	return $string;
}
//crmv@16265e	//crmv@26265e
//crmv@18338
/**
 * A function for making time periods readable
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     2.0.0
 * @link        http://aidanlister.com/2004/04/making-time-periods-readable/
 * @param       int     number of seconds elapsed
 * @param       string  which time periods to display
 * @param       bool    whether to show zero time periods
 */
function time_duration($seconds, $use = null, $zeros = false,$short=true)
{
    // Define time periods
    if (!$short){
    	$periods = array (
        'years'     => 31556926,
        'Months'    => 2629743,
        'weeks'     => 604800,
        'days'      => 86400,
        'hours'     => 3600,
        'minutes'   => 60,
        'seconds'   => 1
        );
        $space = " ";
        if ($seconds <= 0) return "0 seconds";
    }
    else {
    $periods = array (
        'Y'     => 31556926,
        'M'    => 2629743,
        'w'     => 604800,
        'd'      => 86400,
        'h'     => 3600,
        'm'   => 60,
        's'   => 1
        );
        $space = "";
        if ($seconds <= 0) return "0s";
    }
    // Break into periods
    $seconds = (float) $seconds;
    foreach ($periods as $period => $value) {
        if ($use && strpos($use, $period[0]) === false) {
            continue;
        }
        $count = floor($seconds / $value);
        if ($count == 0 && !$zeros) {
            continue;
        }
        $segments[strtolower($period)] = $count;
        $seconds = $seconds % $value;
    }
    // Build the string
    foreach ($segments as $key => $value) {
    	if (!$short){
        	$segment_name = substr($key, 0, -1);
    	}
        else {
        	$segment_name = $key;
        }
        $segment = $value.$space.$segment_name;
        if (!$short){
	        if ($value != 1) {
	            $segment .= 's';
	        }
        }
        $array[] = $segment;
    }

    $str = implode(' ', $array);
    return $str;
}
//crmv@18338 end
//crmv@18592
function getParentTabs()
{
    global $adb,$table_prefix;
    $sql = 'SELECT * FROM '.$table_prefix.'_parenttab ORDER BY sequence';
    $result = $adb->query($sql);
    while($row = $adb->fetch_array($result))
        $return[$row['parenttabid']] = array('parenttab_label'=>$row['parenttab_label'],'hidden'=>$row['hidden']);

    return $return;
}
function getMenuLayout() {
	global $adb;
	//crmv@33465
	if (!$adb->table_exist('tbl_s_menu')){
		return Array();
	}
	//crmv@33465e	
	$sql = "select * from tbl_s_menu";
	$result = $adb->query($sql);
	if ($result) {
    	$return['type'] = $adb->query_result($result,0,'type');
	}
    return $return;
}
function getMenuModuleList() {

	global $current_user,$adb,$table_prefix;
	require('user_privileges/user_privileges_'.$current_user->id.'.php');

	$module_list = array();
	$sql = 'SELECT '.$table_prefix.'_tab.tabid,'.$table_prefix.'_tab.name,tbl_s_menu_modules.fast,tbl_s_menu_modules.sequence FROM '.$table_prefix.'_tab
			INNER JOIN (SELECT DISTINCT tabid FROM '.$table_prefix.'_parenttabrel) parenttabrel ON parenttabrel.tabid = '.$table_prefix.'_tab.tabid
			LEFT JOIN tbl_s_menu_modules ON '.$table_prefix.'_tab.tabid = tbl_s_menu_modules.tabid
			WHERE '.$table_prefix.'_tab.presence = 0
			ORDER BY tbl_s_menu_modules.fast, tbl_s_menu_modules.sequence';
	$res = $adb->query($sql);
	while($row=$adb->fetchByAssoc($res)) {
		if($profileGlobalPermission[2]==0 ||$profileGlobalPermission[1]==0 || $profileTabsPermission[$row['tabid']]==0) {
			$module_list[$row['tabid']] = $row;
		}
	}
	//crmv@30356	//crmv@32217
	$max_menu = 0;
	$module_list_fast = array();
	$module_list_other = array();
	foreach($module_list as $id => $info) {
		if (isMobile()){
			if ($info['fast'] == 1 && $max_menu < 5) {
				$module_list_fast[] = $info;
				$max_menu ++;
			} else {
				//crmv@31250
				if (getTranslatedString($info['name'], 'APP_STRINGS') === $info['name'] || $info['name'] === 'PBXManager')
					$info['translabel'] = getTranslatedString($info['name'],$info['name']);
				else 
					$info['translabel'] = getTranslatedString($info['name'], 'APP_STRINGS');
				$module_list_other[] = $info;
				//crmv@31250e
			}
		} else {
			if ($info['fast'] == 1) {
				$module_list_fast[] = $info;
			} else {
				//crmv@31250
				if (getTranslatedString($info['name'], 'APP_STRINGS') === $info['name'] || $info['name'] === 'PBXManager')
					$info['translabel'] = getTranslatedString($info['name'],$info['name']);
				else 
					$info['translabel'] = getTranslatedString($info['name'], 'APP_STRINGS');
				$module_list_other[] = $info;
				//crmv@31250e
			}
		}

	}
	//crmv@30356e	//crmv@32217e
	usort($module_list_other, create_function('$a, $b','return ($a[\'translabel\'] > $b[\'translabel\']);'));	//crmv@31250
	return array($module_list_fast,$module_list_other);
}
//crmv@18592e
//crmv@20211
function calculateCalColor() {
	global $adb,$table_prefix;
	$query = 'SELECT color, COUNT(color) AS conteggio
				FROM tbl_s_cal_color
				INNER JOIN '.$table_prefix.'_users ON tbl_s_cal_color.color = '.$table_prefix.'_users.cal_color
				GROUP BY tbl_s_cal_color.color
				UNION
				SELECT color, 0 AS conteggio
				FROM tbl_s_cal_color
				WHERE color NOT IN( SELECT color
									FROM tbl_s_cal_color
									INNER JOIN '.$table_prefix.'_users ON tbl_s_cal_color.color = '.$table_prefix.'_users.cal_color
									GROUP BY tbl_s_cal_color.color)
				ORDER BY conteggio,color';
	$result = $adb->limitQuery($query,0,1);
	if ($result)
		return $adb->query_result($result,0,'color');
}
//crmv@20211e
//crmv@23515
function getCalendarRelatedToModules() {
	global $adb,$table_prefix;
	$moduleInstance = Vtiger_Module::getInstance('Calendar');
	$query = "SELECT ".$table_prefix."_tab.name FROM ".$table_prefix."_relatedlists
				INNER JOIN ".$table_prefix."_tab ON ".$table_prefix."_relatedlists.tabid = ".$table_prefix."_tab.tabid
				WHERE related_tabid = ".$moduleInstance->id." AND ".$table_prefix."_relatedlists.name IN ('get_activities','get_history') AND ".$table_prefix."_tab.name <> 'Contacts'
				GROUP BY ".$table_prefix."_tab.name";
	$result = $adb->query($query);
	$modules = array();
	while($row=$adb->fetchByAssoc($result)) {
		$modules[] = $row['name'];
	}
	return $modules;
}
//crmv@23515e
//crmv@22700	//crmv@27624
function isModuleInstalled($module) {
	global $adb,$table_prefix;
	if (!isset($_SESSION['installed_modules'][$module])) {
		if (empty($adb) || !Vtiger_Utils::CheckTable($table_prefix.'_tab')) return false;	//crmv@25671
		$result = $adb->query("SELECT * FROM ".$table_prefix."_tab WHERE name = '$module'");
		if ($result && $adb->num_rows($result) > 0) {
			$_SESSION['installed_modules'][$module] = true;
		} else {
			$_SESSION['installed_modules'][$module] = false;
		}
	}
	return $_SESSION['installed_modules'][$module];
}
//crmv@22700e	//crmv@27624e
//crmv@24568
function get_short_language(){
	$langs = explode('_',$_SESSION['authenticated_user_language']);
	return $langs[0];
}
//crmv@24568e
//crmv@24715
function fix_query_advanced_filters($module,&$query,$columns_to_check=false){
	global $adb,$table_prefix;
	if ($columns_to_check === false){
		$columns_to_check = Zend_Json::decode(getAdvancedresList($module,'columns'));
	}
	$query = preg_replace("/[\n\r\t]+/"," ",$query); //crmv@20049
	if (is_array($columns_to_check)) {
		foreach ($columns_to_check as $_to_split){
			$splitted=explode(":",$_to_split);
			$tablename = trim($splitted[0]);
			$columnname = trim($splitted[1]);
			$fieldname = trim($splitted[2]); //crmv@31423
			if (stripos($query," join $tablename ")===false && stripos($query," from $tablename ")===false){
				$obj = CRMEntity::getInstance($module);
				$module_table=$obj->table_name;
				$module_pk=$obj->tab_name_index[$obj->table_name];
				//la tabella è del modulo corrente
				if (in_array($tablename,array_keys($obj->tab_name_index))){
					$join_check = " left join $tablename on $tablename.{$obj->tab_name_index[$tablename]} = $module_table.$module_pk ";
					$query = preg_replace('/\swhere\s/i', " $join_check where ", $query, 1);
				}
				else{
					//cerco il modulo collegato
					$sql = "SELECT ".$table_prefix."_tab.name FROM ".$table_prefix."_field
									INNER JOIN ".$table_prefix."_tab ON ".$table_prefix."_tab.tabid = ".$table_prefix."_field.tabid
									WHERE tablename=? and fieldname=?";
					$params = Array($tablename,$fieldname);
					$result = $adb->pquery($sql,$params);
					if ($result && $adb->num_rows($result)==1){
						$rel_module = $adb->query_result($result,0,'name');
						$rel_obj = CRMEntity::getInstance($rel_module);
						$relmodule_table=$obj->table_name;
						$relmodule_pk=$obj->tab_name_index[$obj->table_name];
						$tables=Array();
						$fields=Array();
						$reltables = getRelationTables($module,$rel_module);
						//							echo "<pre>";
						//							print_r($reltables);die;
						foreach($reltables as $key=>$value){
							$tables[]=$key;
							$fields[] = $value;
						}
						$relation_table = $tables[0];
						$relation_table1 = $tables[1];
						$prifieldname = $fields[0][0];
						$secfieldname = $fields[0][1];
						$relation_table1_key = $fields[1];
						if ($relation_table1){ //relazione n a n
							//TODO:gestire la tabella vtiger_crmentityrel!
							if (stripos($query," join $relation_table ")===false){
								$join_check = " left join $relation_table on $relation_table.$prifieldname = $module_table.$module_pk ";
								$query = preg_replace('/\swhere\s/i', " $join_check where ", $query, 1);
							}
							$join_check = " left join $tablename on $tablename.{$rel_obj->tab_name_index[$tablename]} = $relation_table.$secfieldname ";
							$query = preg_replace('/\swhere\s/i', " $join_check where ", $query, 1);
						}
						else{ //relazione 1 a n
							if (stripos($query," join $relation_table ")===false && stripos($query," from $relation_table ")===false){
								$join_check = " left join $relation_table on $relation_table.$prifieldname = $relmodule_table.$relmodule_pk ";
								$query = preg_replace('/\swhere\s/i', " $join_check where ", $query, 1);
							}
							if ($tablename != $relmodule_table){
								$join_check = " left join $tablename on $tablename.{$rel_obj->tab_name_index[$tablename]} = $relation_table.$secfieldname ";
								$query = preg_replace('/\swhere\s/i', " $join_check where ", $query, 1);
							}
						}
					}
				}

			}
		}
	}
}
//crmv@24715e
//crmv@25351	//crmv@31263
function getSendMailBox($username,$default) {
	$filename = "include/squirrelmail/data/$username.pref";
	if (file_exists($filename) && $file = @fopen($filename, 'r')) {
		while (!feof($file)) {
			$pref = '';
			/* keep reading a pref until we reach an eol (\n (or \r for macs)) */
			while($read = fgets($file, 1024))
			{
				$pref .= $read;
				if(strpos($read,"\n") || strpos($read,"\r"))
				break;
			}
			$pref = trim($pref);
			$equalsAt = strpos($pref, '=');
			if ($equalsAt > 0) {
				$key = substr($pref, 0, $equalsAt);
				$value = substr($pref, $equalsAt + 1);
				if ($key == 'sent_folder' && $value != '') {
					return $value;
				}
			}
		}
    	fclose($file);
	}
	include('include/squirrelmail/config/config.php');
	if ($sent_folder != '') {
		return $sent_folder;
	}
	return $default;
}
function getDraftMailBox($username,$default) {
	$filename = "include/squirrelmail/data/$username.pref";
	if (file_exists($filename) && $file = @fopen($filename, 'r')) {
		while (!feof($file)) {
			$pref = '';
			/* keep reading a pref until we reach an eol (\n (or \r for macs)) */
			while($read = fgets($file, 1024))
			{
				$pref .= $read;
				if(strpos($read,"\n") || strpos($read,"\r"))
				break;
			}
			$pref = trim($pref);
			$equalsAt = strpos($pref, '=');
			if ($equalsAt > 0) {
				$key = substr($pref, 0, $equalsAt);
				$value = substr($pref, $equalsAt + 1);
				if ($key == 'draft_folder' && $value != '') {
					return $value;
				}
			}
		}
    	fclose($file);
	}
	include('include/squirrelmail/config/config.php');
	if ($draft_folder != '') {
		return $draft_folder;
	}
	return $default;
}
//crmv@25351e	//crmv@31263e
//crmv@27096
function saveListViewCheck($module,$ids) {
	global $adb, $current_user;
	$moduleInstance = Vtiger_Module::getInstance($module);
	$adb->pquery('delete from vte_listview_check where userid = ? and tabid = ?',array($current_user->id,$moduleInstance->id));
	if (!is_array($ids)) {
		if (strpos($ids,';') !== false) {
			$ids = explode(';',$ids);
		} elseif (strpos($ids,',') !== false) {
			$ids = explode(',',$ids);
		} else {
			$ids = array($ids);
		}
	}
	if (is_array($ids)) {
		$ids = array_filter($ids);
	}
	if (!empty($ids)) {
		foreach($ids as $id) {
			$adb->pquery('insert into vte_listview_check (userid,tabid,crmid) values (?,?,?)',array($current_user->id,$moduleInstance->id,$id));
		}
	}
}
function getListViewCheck($module) {
	global $adb, $current_user;
	$moduleInstance = Vtiger_Module::getInstance($module);
	$result = $adb->pquery('SELECT crmid FROM vte_listview_check WHERE userid = ? AND tabid = ?',array($current_user->id,$moduleInstance->id));
	$ids = array();
	if ($result && $adb->num_rows($result) > 0) {
		while($row=$adb->fetchByAssoc($result)) {
			$ids[] = $row['crmid'];
		}
	}
	return $ids;
}
//crmv@27096e
//crmv@2043m
// returns field value from fieldid
function getFieldValue($fieldid, $crmid) {
	global $adb,$table_prefix;
	$res = $adb->pquery(
	  "select
	   ".$table_prefix."_tab.name as modulename,
	   fieldid, fieldname, tablename, columnname
	  from ".$table_prefix."_field
	   inner join ".$table_prefix."_tab on ".$table_prefix."_tab.tabid = ".$table_prefix."_field.tabid
	  where fieldid=? and ".$table_prefix."_field.presence in (0,2)",
	array($fieldid)
	);
	if ($res && $adb->num_rows($res) > 0) {

		$row = $adb->FetchByAssoc($res, -1, false);
		$focus = CRMEntity::getInstance($row['modulename']);
		if (empty($focus)) return null;

		$indexname = $focus->tab_name_index[$row['tablename']];
		if (empty($indexname)) return null;

		if ($row['tablename'] != $table_prefix.'_crmentity') {
			$join = "inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = {$row['tablename']}.$indexname";
		} else {
			$join = "";
		}
		$res2 = $adb->query("select {$row['columnname']} as fieldval from {$row['tablename']} $join where ".$table_prefix."_crmentity.deleted = 0 and $indexname = $crmid");
		if ($res2) {
			$value = $adb->query_result($res2, 0, 'fieldval');
			return $value;
		}
	}
	return null;
}
//crmv@2043me
//crmv@27711
function getHideTab($mode='all') {
	global $adb;
	$result = $adb->query('select * from vte_hide_tab');
	$return = array();
	if ($result && $adb->num_rows($result) > 0) {
		while($row=$adb->fetchByAssoc($result)) {
			$tabid = $row['tabid'];
			$hide_module_manager = $row['hide_module_manager'];
			if ($hide_module_manager == '1') {
				$return['hide_module_manager'][] = $tabid;
			}
			$hide_profile = $row['hide_profile'];
			if ($hide_profile == '1') {
				$return['hide_profile'][] = $tabid;
			}
			$hide_report = $row['hide_report'];
			if ($hide_report == '1') {
				$return['hide_report'][] = $tabid;
			}
		}
	}
	if (!empty($return)) {
		if ($mode != 'all') {
			$return = $return[$mode];
		}
	}
	return $return;
}
//crmv@27711e
//crmv@2051m
function getFromEmailList($from_email,$from_mailbox='') {
	global $adb, $current_user;
	$list = array();
	$list[$current_user->column_fields['email1']] = array('email'=>$current_user->column_fields['email1'],'name'=>trim(getUserFullName($current_user->id)),'selected'=>'');

	$filename = 'include/squirrelmail/data/'.$current_user->column_fields['webmail_username'].'.pref';
	$file = @fopen($filename, 'r');
	if ($file) {
		while (! feof($file)) {
	        $pref = '';
	        /* keep reading a pref until we reach an eol (\n (or \r for macs)) */
	        while($read = fgets($file, 1024))
	        {
	                $pref .= $read;
	                if(strpos($read,"\n") || strpos($read,"\r"))
	                        break;
	        }
	        $pref = trim($pref);
	        $equalsAt = strpos($pref, '=');
	        if ($equalsAt > 0) {
	            $key = substr($pref, 0, $equalsAt);
	            $value = substr($pref, $equalsAt + 1);
	            if ($value != '') {
	                $prefs_cache[$key] = $value;
	            }
	        }
	    }
	    fclose($file);
	    if (isset($prefs_cache['mailfetch_server_number']) && $prefs_cache['mailfetch_server_number'] > 0) {
	    	for ($i=0;$i<$prefs_cache['mailfetch_server_number'];$i++) {
	    		$mailfetch_alias = $prefs_cache["mailfetch_alias_$i"];
	    		$mailfetch_user = $prefs_cache["mailfetch_user_$i"];
	    		$mailfetch_subfolder = $prefs_cache["mailfetch_subfolder_$i"];
	    		if (!array_key_exists($mailfetch_user, $list)) {
	    			$list[$mailfetch_user] = array('email'=>$mailfetch_user,'name'=>$mailfetch_alias,'subfolder'=>$mailfetch_subfolder,'selected'=>'');
	    		}
	    	}
	    }
	}
	//crmv@2043m
	if ($_REQUEST['reply_mail_user'] == 'mailconverter' && isset($_REQUEST['reply_mail_converter']) && $_REQUEST['reply_mail_converter'] != '') {
		$HelpDeskFocus = CRMEntity::getInstance('HelpDesk');
		$HelpDeskFocus->retrieve_entity_info_no_html($_REQUEST['reply_mail_converter_record'], 'HelpDesk');
		if ($HelpDeskFocus->column_fields['helpdesk_from'] != '') {
			if (!array_key_exists($HelpDeskFocus->column_fields['helpdesk_from'], $list)) {
				$list[$HelpDeskFocus->column_fields['helpdesk_from']] = array('email'=>$HelpDeskFocus->column_fields['helpdesk_from'],'name'=>$HelpDeskFocus->column_fields['helpdesk_from_name'],'selected'=>'');
			}
		}
	}
	//crmv@2043me
	foreach ($list as $i => $info) {
		$selected = '';
		if (isset($info['subfolder']) && $from_mailbox == $info['subfolder']) {
			$selected = 'selected';
		} elseif ($from_email == $info['email']) {
			$selected = 'selected';
		}
		if ($selected != '') {
			$list[$i]['selected'] = $selected;
			break;
		}
	}
	return $list;
}
function getFromEmailName($from_email) {
	$list = getFromEmailList($from_email);
	foreach($list as $info) {
		if ($info['email'] == $from_email) {
			return $info['name'];
		}
	}
	return $from_email;
}
//crmv@2051me
//crmv@29079
function getUserAvatar($id) {
	global $current_user, $theme, $table_prefix;
	$avatar = '';
	if ($id == $current_user->id) {
		$avatar = $current_user->column_fields['avatar'];
	} elseif ($id != '') {
		global $adb;
		$result = $adb->pquery('SELECT avatar FROM '.$table_prefix.'_users WHERE id = ?',array($id));
		if ($result && $adb->num_rows($result) > 0) {
			$avatar = $adb->query_result($result,0,'avatar');
		}
	}
	if ($avatar == '') {
		$avatar = vtiger_imageurl('no_avatar.png',$theme);
	}
	return $avatar;
}
function getSingleModuleName($module,$record='') {
	if ($module == 'Calendar' && $record != '') {
		global $adb,$table_prefix;
		$result = $adb->pquery('SELECT activitytype FROM '.$table_prefix.'_activity WHERE activityid = ?',array($record));
		if ($result && $adb->num_rows($result) > 0) {
			$activitytype = getTranslatedString($adb->query_result($result,0,'activitytype'),$module);
		}
		if ($activitytype != '') {
			return $activitytype;
		}
	}
	$single_module = getTranslatedString('SINGLE_'.$module,$module);
	if (in_array($single_module,array('','SINGLE_'.$module))) {
		$single_module = getTranslatedString($module,$module);
	}
	return $single_module;
}
//crmv@29079e
//crmv@29506
function getConvertMenuSquirrel($language,$passed_id,$mailbox,$startMessage,$passed_ent_id,$smaction,$t='',$fromsearch='') {
	global $adb;
	$result = getModulesToEmail(array('Calendar','Events','HelpDesk','Potentials','ProjectPlan','ProjectTask'));
	$html = '<div onmouseover="fnShowDrop(\'Convert_sub'.$t.'\')" onmouseout="fnHideDrop(\'Convert_sub'.$t.'\')" id="Convert_sub'.$t.'" class="drop_mnu" style="left: 387px; top: 68px; display: none;">';//crmv@21994
	$html .= '<table cellspacing="0" cellpadding="0" border="0" width="100%">';
	while ($row = $adb->fetchByAssoc($result,-1,false)) {
		$link = '';
//		if ($fromsearch != 'fromsearch') {
//			$link .= 'include/squirrelmail/src/';
//		}
		$link .= 'compose.php?vte_type=convert&vte_module='.$row['name'].'&passed_id='.$passed_id.'&mailbox='.$mailbox.'&startMessage='.$startMessage.'&passed_ent_id='.$passed_ent_id.'&smaction='.$smaction;
		if ($row['name'] == 'Calendar') {
			$moduleName = getTranslatedStringSquirrel('LBL_'.strtoupper(substr('Events',0,-1)),$row['name']);
			$html .= '<tr><td><a class="drop_down" href="javascript:void(0);" onclick="Convert(\'Events\',\''.$link.'\');">'.$moduleName.'</a></td></tr>';
			$moduleName = getTranslatedStringSquirrel('LBL_'.strtoupper(substr('Tasks',0,-1)),$row['name']);
			$html .= '<tr><td><a class="drop_down" href="javascript:void(0);" onclick="Convert(\'Tasks\',\''.$link.'\');">'.$moduleName.'</a></td></tr>';
		} else {
			$moduleName = getSingleModuleName($row['name']);
			$html .= '<tr><td><a class="drop_down" href="javascript:void(0);" onclick="Convert(\''.$row['name'].'\',\''.$link.'\');">'.$moduleName.'</a></td></tr>';
		}
	}
	$html .= '</table></div>';
	return $html;
}
//crmv@29506e
//crmv@29615
function getDisplayFieldName($uitype,$fieldname) {
	//uitype 83, 63, 55, 255, 6 non implementati totalmente
	if ($uitype == '10')
		$fieldname = $fieldname.'_display';
	elseif ($uitype == '52')
		$fieldname = 'assigned_user_id';
	elseif ($uitype == '77')
		$fieldname = 'assigned_user_id1';
	elseif (in_array($uitype,array('51','50','73')))
		$fieldname = 'account_name';
	elseif (in_array($uitype,array('75','81')))
		$fieldname = 'vendor_name';
	elseif ($uitype == '57')
		$fieldname = 'contact_name';
	elseif ($uitype == '58')
		$fieldname = 'campaignname';
	elseif ($uitype == '80')
		$fieldname = 'salesorder_name';
	elseif ($uitype == '78')
		$fieldname = 'quote_name';
	elseif ($uitype == '76')
		$fieldname = 'potential_name';
	elseif (in_array($uitype,array('68','66','62','357')))
		$fieldname = 'parent_name';
	elseif ($uitype == '59')
		$fieldname = 'product_name';
	elseif ($uitype == '98')
		$fieldname = 'role_name';
	elseif ($uitype == '101')
		$fieldname = 'reports_to_name';
	elseif ($uitype == '30')
		$fieldname = 'set_reminder';
	return $fieldname;
}
//crmv@29615e
//crmv@30356
function isMobile() {
	if (!isset($_SESSION['isMobileClient'])) {
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$mobile_agents = Array(
			"acer",
			"alcatel",
			"android",
			"applewebkit/525",
			"applewebkit/532",
			"asus",
			"blackberry",
			"hitachi",
			"htc",
			"huawei",
			"ipad",
			"ipaq",
			"ipod",
			"lg",
			"nintendo",
			"nokia",
			"panasonic",
			"philips",
			"phone",
			"playstation",
			"sanyo",
			"samsung",
			"sharp",
			"siemens",
			"sony",
			"symbian",
			"tablet",
			"toshiba",
		);
		$is_mobile = false;
		foreach ($mobile_agents as $device) {
			if (stristr($user_agent, $device)) {
				$is_mobile = true;
				break;
			}
		}
		/*
		if(array_search ($user_agent,$mobile_agents)){
			$is_mobile = true;
		}
		*/
		$_SESSION['isMobileClient'] = $is_mobile;
	}
	return $_SESSION['isMobileClient'];
}
//crmv@30356e
//crmv@17001
function getCalendarColors() {
	global $adb,$current_user,$table_prefix;
	$arr = array();
	$i = 0;
	$arr[$current_user->id] = $i;
	//$res = $adb->query("SELECT id FROM vtiger_users WHERE id <> ".$current_user->id);
	$res = $adb->query("SELECT id FROM ".$table_prefix."_users ");

	if ($res && $adb->num_rows($res)>0) {
		while($row = $adb->fetchByAssoc($res)) {
			//$i++; //crm@20211
			$arr[$row['id']] = $i++;
		}
	}
	return $arr;
}
function getUserColor($id) {
	global $calendar_colors;
	if ($calendar_colors == '') $calendar_colors = getCalendarColors();
	return $calendar_colors[$id];
}
function getUserColorDb($ownerId,$activityid='') {
	global $adb,$current_user,$table_prefix;
	if ($activityid != '') {
		$isInvited = isCalendarInvited($current_user->id,$activityid);
		if ($isInvited[0] == 'yes')
			$ownerId = $current_user->id;
	}
	$res = $adb->query("SELECT cal_color FROM ".$table_prefix."_users WHERE id = ".$ownerId);
	if ($res) {
		return $adb->query_result($res,0,'cal_color');
	}
}
//crmv@17001e
//crmv@30967
function getEntityFolder($folderid) {
	global $adb, $table_prefix;

	$res = $adb->pquery("select * from {$table_prefix}_crmentityfolder where folderid = ?", array($folderid));

	if ($res !== false) {
		return $adb->fetchByAssoc($res);
	} else {
		return false;
	}
}

function getEntityFoldersByName($foldername = null, $module = null) {
	global $adb, $table_prefix;

	$params = array();
	$conds = array();
	$sql = "select * from {$table_prefix}_crmentityfolder";

	if (!is_null($foldername) || !is_null($module)) {
		$sql .= ' where ';
	}

	if (!is_null($foldername)) {
		$conds[] = ' foldername = ? ';
		$params[] = $foldername;
	}

	if (!is_null($module)) {
		$conds[] = ' tabid = ? ';
		$params[] = getTabId($module);
	}

	$sql .= implode(' and ', $conds);
	$sql .= ' order by foldername';

	$res = $adb->pquery($sql, $params);

	if ($res !== false) {
		$ret = array();
		while ($row = $adb->fetchByAssoc($res)) $ret[] = $row;
		return $ret;
	} else {
		return false;
	}
}

function addEntityFolder($module, $foldername, $description = '', $creator = 1, $state = '', $sequence = 0) {
	global $adb, $table_prefix;

	$tabid = getTabid($module);
	// try to do a direct query
	if (empty($tabid)) {
		$res = $adb->pquery("select tabid from {$table_prefix}_tab where name = ?", array($module));
		if ($res && $adb->num_rows($res) > 0) $tabid = $adb->query_result($res, 0, 'tabid');
	}
	if (empty($tabid)) return false;
	
	$folderid = $adb->getUniqueID($table_prefix."_crmentityfolder");

	$params = array($folderid, $tabid, $creator, $foldername, $description, $state, $sequence);
	$res = $adb->pquery("insert into {$table_prefix}_crmentityfolder (folderid, tabid, createdby, foldername, description, state, sequence) values (".generateQuestionMarks($params).")", $params);
	if ($res !== false)
		return $folderid;
	else
		return false;
}

function editEntityFolder($folderid, $foldername, $description = null, $state = null) {
	global $adb, $table_prefix;

	$params = array($foldername);
	$sql = "update {$table_prefix}_crmentityfolder set foldername = ?";

	if (!is_null($description)) {
		$sql .= ', description = ?';
		$params[] = $description;
	}

	if (!is_null($state)) {
		$sql .= ', state = ?';
		$params[] = $state;
	}

	$sql .= " where folderid = ?";
	$params[] = $folderid;

	$res = $adb->pquery($sql, $params);

	if ($res !== false) return $folderid; else return false;
}

function deleteEntityFolder($folderid) {
	global $adb, $table_prefix;

	$res = $adb->pquery("delete from {$table_prefix}_crmentityfolder where folderid = ?", array($folderid));
	if ($res !== false)
		return $folderid;
	else
		return false;
}
//crmv@30967e
?>