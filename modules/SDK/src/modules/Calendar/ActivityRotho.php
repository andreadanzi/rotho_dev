<?
require_once('modules/Calendar/Activity.php');
class ActivityRotho extends Activity {
	
	var $list_fields = Array(
       
       'Start Date'=>Array('activity'=>'date_start'),
       'Start Time'=>Array('activity'=>'time_start'),
       'End Date'=>Array('activity'=>'due_date'),
       'End Time'=>Array('activity'=>'time_end'),	   
	   'Activity Type'=>Array('activity'=>'activitytype'),
	   'Subject'=>Array('activity'=>'subject'),
       'Assigned To'=>Array('crmentity'=>'smownerid'),
       
       );
       
       var $list_fields_name = Array(
       
       'Start Date & Time'=>'date_start',
       'End Date & Time'=>'due_date',	   
	   'Activity Type'=>'activitytype',
	   'Subject'=>'subject',
       'Assigned To'=>'assigned_user_id');
	   
	   	var $default_order_by = 'date_start';
		var $default_sort_order = 'DESC';
}
?>