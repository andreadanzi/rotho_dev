<?php
include_once 'include/Zend/Json.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'include/utils/VtlibUtils.php';
include_once 'include/Webservices/Create.php';
include_once 'include/QueryGenerator/QueryGenerator.php';
require_once('modules/Emails/mail.php');

function do_check_scripts($time_start) {
	global $log_active, $adb, $days_detail, $days_summary, $to , $subject,$cc;
	$import_result = array();
	$import_result['records_created']=0;
	$import_result['records_updated']=0;
	$message = "<html><body>";
	$query = "SELECT 
				log_script_content.id,
				log_script_content.type,
				log_script_content.total_records,
				log_script_content.records_created,
				log_script_content.records_updated,
				log_script_content.date_start,
				DATEDIFF(hh,log_script_content.date_start, GETDATE()) as hours_from_start
				FROM log_script_content 
				WHERE 
				log_script_content.date_start BETWEEN DATEADD(day, $days_detail, GETDATE()) AND GETDATE()
				AND log_script_content.date_end IS NULL
				ORDER BY id ";
	if($log_active) echo "do_check_scripts detail query= ".$query." \n";
	$message .= "\r\n<h1>---------------------Running Scripts </h1>\r\n";
	$rs_message = "";
	$result = $adb->query($query);
	while($row=$adb->fetchByAssoc($result))
	{
		$rs_message .= "<p>Script for ".$row["type"]." is running from ".$row["hours_from_start"]." hours, started at ".$row["date_start"]."</p>\r\n";
		$import_result['records_created']++;
	}
	if(empty($rs_message))  {
		$rs_message = "<p>Running Scripts NONE</p>\r\n";
		$subject .= " - RUNNING_SCRIPTS=NONE";
	} else {
		$subject .= " - RUNNING_SCRIPTS=".$import_result['records_created'];
	}
	$message .= $rs_message;
	$query = "SELECT 
				log_script_content.type, 
				count(*) as tot_count_runs ,
				sum ( case when log_script_content.date_end IS NULL THEN  0 else 1 end) as ended_ok,
				sum ( case when log_script_content.date_end IS NULL THEN  1 else 0 end) as ended_ko
				FROM log_script_content 
				WHERE
				log_script_content.date_start BETWEEN DATEADD(day, $days_summary, GETDATE()) AND GETDATE()
				GROUP BY log_script_content.type 
				ORDER BY ended_ko DESC ";
	if($log_active) echo "do_check_scripts summary query= ".$query." \n";
	$message .= "\r\n<h1>---------------------Last 7 days summary </h1>\r\n";
	$result = $adb->query($query);
	while($row=$adb->fetchByAssoc($result))
	{
		$message .="<p>".$row["type"]." has run ".$row["tot_count_runs"]." times, ".$row["ended_ok"]." ended OK, ".$row["ended_ko"]." ended KO </p> \r\n";
		$import_result['records_updated']++;
	}
	if($log_active) echo $message;
	$message .= "</body></html>";
	send_mail('Emails',$to ,'ROTHO BLAAS','laura@rothoblaas.com',$subject,$message,$cc,'');
	return $import_result;
}

?>
