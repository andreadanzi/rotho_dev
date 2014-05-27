<?php
//danzi.tn@20140310 CreateVisitReport modulo PHP per le creazione di report visita da evento di calendario
require_once('include/utils/utils.php');
require_once('include/DatabaseUtil.php');

global $mod_strings,$app_strings,$theme,$currentModule,$current_user,$adb, $table_prefix;
$return_id = 0;
$focus = CRMEntity::getInstance($currentModule);
$focus->mode = '';
$action = $_REQUEST['action'];
$calendar_recordid = $_REQUEST['record'];
$ajxaction = $_REQUEST['ajxaction'];
$sql =	"SELECT 
		".$table_prefix."_activity.activityid ,
		".$table_prefix."_activity.subject,
		".$table_prefix."_activity.activitytype,
		".$table_prefix."_activity.date_start,
		".$table_prefix."_activity.due_date,
		".$table_prefix."_crmentity.description,
		".$table_prefix."_crmentity.smownerid AS event_assigned_id,
		relatedentity.crmid as entity_id,
		relatedentity.smownerid as entity_assigned_id,
		relatedentity.setype
		FROM ".$table_prefix."_activity 
		JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_activity.activityid AND deleted = 0
		JOIN ".$table_prefix."_seactivityrel ON ".$table_prefix."_seactivityrel.activityid = ".$table_prefix."_activity.activityid 
		JOIN ".$table_prefix."_crmentity AS relatedentity ON relatedentity.crmid = ".$table_prefix."_seactivityrel.crmid
		WHERE 
		".$table_prefix."_activity.activityid = " . $calendar_recordid;
$result = $adb->query($sql);
while($row=$adb->fetchByAssoc($result))
{
	if($row['setype'] == 'Accounts' &&  $row['activitytype'] == 'Visita') {
		$visit_event = CRMEntity::getInstance('Events');
		$visit_event->id = $row['activityid'];
		$visit_event->retrieve_entity_info($row['activityid'],'Events');
		// CREATE VISIT REPORT
		$visit_report = CRMEntity::getInstance('Visitreport');
		vtlib_setup_modulevars('Visitreport',$visit_report);
		$visit_report->column_fields['visitreportname'] = "0";
		$visit_report->column_fields['description'] = $visit_event->column_fields['subject'] . " - ".$visit_event->column_fields['description'];
		$visit_report->column_fields['visitnote'] = $visit_event->column_fields['description'];// . "-[" . $row['account_no'] . "]";
		$visit_report->column_fields['assigned_user_id'] = $current_user->id;
		$visit_report->column_fields['accountid'] = $visit_event->column_fields["parent_id"];
		$visit_report->column_fields['visitdate'] = $visit_event->column_fields["date_start"];	
		// $visit_report->column_fields['creator_id'] = 
		$visit_report->save($module_name='Visitreport');
		$return_id=$visit_report->id;
	} else {
		$return_id = -1;
	}
	break;
}

echo $return_id;

?>