<?php
global $adb,$adbext,$module,$current_user,$mapping,$external_code,$table,$where,$fields_auto_create,$fields_auto_update;

require('Inspections_config.php');
set_time_limit(0);

//create log object (total and partial)
$global_log=new log("\n");

//start the total logger
$global_log->start();

//scrivo sul db lo stato dello script
$time_start = time();
$working_id = $adb->getUniqueID("log_script_content");
$res = $adb->pquery("select count(*) as count from log_script_state where type = ?",Array($module));
if ($res){
	if ($adb->query_result($res,0,'count') == 0){
		$adb->pquery('insert into log_script_state (type,state,working_id) values (?,?,?)',Array($module,0,0));
	}
}
$adb->pquery('update log_script_state set state = 1,working_id = ? where type = ?',Array($working_id,$module));

//scrivo sul db la data di partenza dello script
$res = Array($working_id,$module,date("Y-m-d H:i:s",$time_start));
$adb->pquery('insert into log_script_content (id,type,date_start) values ('.generateQuestionMarks($res).')',$res);
$records=Array('records_created'=>0,'records_updated'=>0);
$records = do_populate_inspections(date("Y-m-d H:i:s",$time_start));
$time_end = time();

//scrivo sul db i dati sull'esecuzione dello script
$duration = $time_end - $time_start;
$duration_min = intval ($duration / 60);
$duration_sec = $duration - $duration_min * 60;
$duration_string = $duration_min."m:".$duration_sec."s";
$res = array(date("Y-m-d H:i:s",$time_end),$records['records_created'],$records['records_updated'],($records['records_created']+$records['records_updated']),$duration_string,$working_id);
if ($log_active){
    print_r($res);
}
$tots = $records['records_created']+$records['records_updated'];
$updateQuery = "update log_script_state set state = 0 , working_id = NULL where type = '".$module."'";
if ($log_active){
    print_r($qret);
    echo $updateQuery."\n\n qret=";
}

$updateQuery = "update log_script_content SET 
                        date_end = '".date("Y-m-d H:i:s",$time_end)."', 
                        records_created = ".$records['records_created'].", 
                        records_updated = ".$records['records_updated'].", 
                        total_records = ".$tots.", 
                        duration = '".$duration_string."' 
                WHERE id = ".$working_id;
if ($log_active){
    echo $updateQuery."\n qret=";
}
$qret = $adb->query(updateQuery);

//scrivo sul db lo stato dello script
$qret = $adb->query($updateQuery);
if ($log_active){
    print_r($qret); 
}
$global_log->stop('import');
if ($log_active){
	echo "\n----------------------TOTAL TIME------------------------\n";
	echo $global_log->get_content();
	echo "----------------------TOTAL TIME------------------------\n";
}

?>