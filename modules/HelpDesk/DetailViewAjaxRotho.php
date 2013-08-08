<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/

require_once('include/logging.php');
require_once('include/database/PearDatabase.php');
global $adb;
global $table_prefix;
$local_log =& LoggerManager::getLogger('HelpDeskAjax');
global $currentModule;
$modObj = CRMEntity::getInstance($currentModule);

$ajaxaction = $_REQUEST["ajxaction"];
if($ajaxaction == "DETAILVIEW")
{
	$crmid = $_REQUEST["recordid"];
	$tablename = $_REQUEST["tableName"];
	$fieldname = $_REQUEST["fldName"];
	$fieldvalue = utf8RawUrlDecode($_REQUEST["fieldValue"]);

	if($crmid != ""){
		$modObj->retrieve_entity_info($crmid,"HelpDesk");
		
		//Added to avoid the comment save, when we edit other fields through ajax edit
		if($fieldname != 'comments')
			$modObj->column_fields['comments'] = '';

		$modObj->column_fields[$fieldname] = $fieldvalue;
		$modObj->id = $crmid;
		$modObj->mode = "edit";

		//Added to construct the update log for Ticket history
		$assigned_group_name = getGroupName($_REQUEST['assigned_group_id']);
		$assigntype = $_REQUEST['assigntype'];

		$fldvalue = $modObj->constructUpdateLog($modObj, $modObj->mode, $assigned_group_name, $assigntype);
		$fldvalue = from_html($fldvalue,($modObj->mode == 'edit')?true:false);
		
		$modObj->save("HelpDesk");
		global $mod_strings;
		//danzi.tn 3 apr 2012 commented
		//if($fieldname == "solution" || $fieldname == "comments" || $fieldname =="assigned_user_id" ||($fieldname == "ticketstatus" && $fieldvalue == $mod_strings['Closed']))
		if($fieldname =="assigned_user_id" ||($fieldname == "ticketstatus" && $fieldvalue == $mod_strings['Closed']))
		{
			require_once('modules/Emails/mail.php');
			$user_emailid = getUserEmailId('id',$modObj->column_fields['assigned_user_id']);
			
			$subject = $modObj->column_fields['ticket_no'] . ' [ '.$mod_strings['LBL_TICKET_ID'].' : '.$modObj->id.' ] Re : '.$modObj->column_fields['ticket_title'];
			$parent_id = $modObj->column_fields['parent_id'];
			//crmv@26552  crmv@26821
			if(!empty($parent_id) && $parent_id!=0){
				$parent_module = getSalesEntityType($parent_id);
				if($parent_module == 'Contacts') {
					$result = $adb->pquery("select * from ".$table_prefix."_contactdetails where contactid=?", array($parent_id));
					$emailoptout = $adb->query_result($result,0,'emailoptout');
					$contactname = $adb->query_result($result,0,'firstname').' '.$adb->query_result($result,0,'lastname');
					$parentname = $contactname;
					$contact_mailid = $adb->query_result($result,0,'email');
				}
				if($parent_module == 'Accounts') {
					$result = $adb->pquery("select * from ".$table_prefix."_account where accountid=?", array($parent_id));
					$emailoptout = $adb->query_result($result,0,'emailoptout');
					$parentname = $adb->query_result($result,0,'accountname');
					$isactive = 1;
				}
				if($contact_mailid != '') {
					$sql = "select * from ".$table_prefix."_portalinfo where user_name=?";
					$isactive = $adb->query_result($adb->pquery($sql, array($contact_mailid)),0,'isactive');
				}
			}
			$url = "<a href='".$PORTAL_URL."/index.php?module=HelpDesk&action=index&ticketid=".$modObj->id."&fun=detail'>Ticket Details</a>";
			$email_body_portal = $subject.'<br><br>'.getPortalInfo_Ticket($modObj->id,$sub,$contactname,$url,"edit");

			//if($emailoptout == 0 && $isactive == 1 && !empty($parent_id) && $fieldname != "assigned_user_id") { commented 3.apr 2012 danzi.tn
				//send mail to parent
				//$parent_email = getParentMailId($parent_module,$parent_id);
				//$mail_status = send_mail('HelpDesk',$parent_email,$HELPDESK_SUPPORT_NAME,$HELPDESK_SUPPORT_EMAIL_ID,$subject,$email_body_portal);
			//}
			//crmv@26552e crmv@26821e
		}
		//update the log information for ticket history
		$adb->pquery("update ".$table_prefix."_troubletickets set update_log=? where ticketid=?", array($fldvalue, $modObj->id));
		
		if($modObj->id != ""){
			if($fieldname == "comments"){
				$comments = $modObj->getCommentInformation($modObj->id);
				echo ":#:SUCCESS".$comments;
			}else{
				echo ":#:SUCCESS";
			}
		}else{
			echo ":#:FAILURE";
		}   
	}else{
		echo ":#:FAILURE";
	}
} elseif($ajaxaction == "LOADRELATEDLIST" || $ajaxaction == "DISABLEMODULE"){
	require_once 'include/ListView/RelatedListViewContents.php';
}
?>

