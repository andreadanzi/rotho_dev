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
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
// danzi.tn@20151005 getEntityName may return NULL

global $app_strings,$table_prefix;
global $currentModule,$image_path,$theme,$adb, $current_user;

require_once('Smarty_setup.php');
require_once("data/Tracker.php");
require_once('modules/VteCore/layout_utils.php');	//crmv@30447
require_once('include/utils/utils.php');
require_once('modules/Calendar/Activity.php');

$cur_time = time();
$_SESSION['last_reminder_check_time'] = $cur_time;
$_SESSION['next_reminder_interval'] = 60;
if($_SESSION['next_reminder_time'] == 'None') {
	return;
} elseif(isset($_SESSION['next_reminder_interval']) && (($_SESSION['next_reminder_time'] -
		$_SESSION['next_reminder_interval']) > $cur_time)) {
	echo "<script type='text/javascript' id='_vtiger_activityreminder_callback_interval_'>".
		($_SESSION['next_reminder_interval'] * 1000)."</script>";
	return;
}

$log = LoggerManager::getLogger('Activity_Reminder');
$smarty = new vtigerCRM_Smarty;
if(isPermitted('Calendar','index') == 'yes'){
	$active = $adb->pquery("select * from ".$table_prefix."_users where id=?",array($current_user->id));
	$active_res = $adb->query_result($active,0,'reminder_interval');
	if($active_res == 'None') {
		$_SESSION['next_reminder_time'] = 'None';
	}
	if($active_res!='None'){
		$interval=$adb->query_result($active,0,"reminder_interval");
		$intervalInMinutes = ConvertToMinutes($interval);
		// check for reminders every minute
		$time = time();
		$_SESSION['next_reminder_time'] = $time + ($intervalInMinutes * 60);
		$date = date('Y-m-d', strtotime("+$intervalInMinutes minutes", $time));
		$time = date('H:i',   strtotime("+$intervalInMinutes minutes", $time));
		//crmv@19691
		$callback_query =
		"SELECT ".$table_prefix."_act_reminder_popup.* FROM ".$table_prefix."_act_reminder_popup inner join ".$table_prefix."_crmentity on ".$table_prefix."_act_reminder_popup.recordid = ".$table_prefix."_crmentity.crmid where " .
		$table_prefix."_act_reminder_popup.status = 0 and " .
		$table_prefix."_crmentity.smownerid = ".$current_user->id." and ".$table_prefix."_crmentity.deleted = 0 " .
		" and ((".$adb->database->SQLDate('Y-m-d',$table_prefix.'_act_reminder_popup.date_start')." < '" . $date . "')" .
		" OR ((".$adb->database->SQLDate('Y-m-d',$table_prefix.'_act_reminder_popup.date_start')." = '" . $date . "')" .
		" AND ".$table_prefix."_act_reminder_popup.time_start <= '" . $time . "'))";
		//crmv@19691e
		$result = $adb->query($callback_query);

		$cbrows = $adb->num_rows($result);
		if($cbrows > 0) {
			for($index = 0; $index < $cbrows; ++$index) {
				$reminderid = $adb->query_result($result, $index, "reminderid");
				$cbrecord = $adb->query_result($result, $index, "recordid");
				$cbmodule = $adb->query_result($result, $index, "semodule");

				$focus = CRMEntity::getInstance($cbmodule);
				if (!isRecordExists($cbrecord)) {
					$del_qry = "delete from ".$table_prefix."_act_reminder_popup where reminderid = ?";
					$adb->pquery($del_qry,Array($reminderid));
					continue;
				}
								
				if($cbmodule == 'Calendar') {
					$focus->retrieve_entity_info($cbrecord,$cbmodule);
					$cbsubject = $focus->column_fields['subject'];
					$cbactivitytype   = $focus->column_fields['activitytype'];
					$cbdate   = $focus->column_fields["date_start"];
					$cbtime   = $focus->column_fields["time_start"];
				} else {
					// For non-calendar records.
                    // danzi.tn@20151005
                    $cbsubject = "";
                    $entity_names = getEntityName($cbmodule, $cbrecord);
                    if(isset($entity_names) && count($entity_names) > 0) {
                        $cbsubject      = array_values($entity_names);
                        $cbsubject      = $cbsubject[0];
                    }
                    // danzi.tn@20151005e
					$cbactivitytype = getTranslatedString($cbmodule, $cbmodule);
					$cbdate         = $adb->query_result($result, $index, 'date_start');
					$cbtime         = $adb->query_result($result, $index, 'time_start');
				}

				if($cbactivitytype=='Task')
					$cbstatus   = $focus->column_fields["taskstatus"];
				else
					$cbstatus   = $focus->column_fields["eventstatus"];
				// Appending recordid we can get unique callback dom id for that record.
				$popupid = "ActivityReminder_$cbrecord";
				if($cbdate <= date('Y-m-d')){
					if(substr($cbdate,0,10) == date('Y-m-d') && $cbtime > date('H:i')) $cbcolor = '';
					else $cbcolor= '#FF1515';
				}
				$smarty->assign("THEME", $theme);
				$smarty->assign("popupid", $popupid);
				$smarty->assign("APP", $app_strings);
				$smarty->assign("cbreminderid", $reminderid);
				$smarty->assign("cbdate", getDisplayDate($cbdate));
				$smarty->assign("cbtime", $cbtime);
				$smarty->assign("cbsubject", $cbsubject);
				$smarty->assign("cbmodule", $cbmodule);
				$smarty->assign("cbrecord", $cbrecord);
				$smarty->assign("cbstatus", $cbstatus);
				$smarty->assign("cbcolor", $cbcolor);
				$smarty->assign("cblinkdtl", $cblinkdtl);
				$smarty->assign("activitytype", $cbactivitytype);
				$smarty->display("ActivityReminderCallback.tpl");

				$mark_reminder_as_read = "UPDATE ".$table_prefix."_act_reminder_popup set status = 1 where reminderid = ?";
				$adb->pquery($mark_reminder_as_read, array($reminderid));
				echo "<script type='text/javascript'>window.top.document.title= '".
					$app_strings['LBL_NEW_BUTTON_LABEL'].$app_strings['LBL_Reminder']."';</script>";
			}
		} else {
			//crmv@19691
			$callback_query =
			"SELECT ".$table_prefix."_act_reminder_popup.* FROM ".$table_prefix."_act_reminder_popup inner join ".$table_prefix."_crmentity on ".$table_prefix."_act_reminder_popup.recordid = ".$table_prefix."_crmentity.crmid where " .
			" ".$table_prefix."_act_reminder_popup.status = 0 and " .
			" ".$table_prefix."_crmentity.smownerid = ".$current_user->id." and ".$table_prefix."_crmentity.deleted = 0 ".
			"AND ".$table_prefix."_act_reminder_popup.reminderid > 0 ORDER BY date_start DESC , ".
			"".$table_prefix."_act_reminder_popup.time_start DESC";
			//crmv@19691e
			$result = $adb->limitQuery($callback_query,0,1);
			$it = new SqlResultIterator($adb, $result);
			$nextReminderTime = null;
			foreach ($it as $row) {
				$nextReminderTime = strtotime($row->date_start.' '.$row->time_start);
			}
			$_SESSION['next_reminder_time'] = $nextReminderTime - ($intervalInMinutes * 60);
		}
		echo "<script type='text/javascript' id='_vtiger_activityreminder_callback_interval_'>".
				($_SESSION['next_reminder_interval'] * 1000)."</script>";
	}
}

?>