<?php

include_once 'PopulateInsp.php';
function do_populate_inspections($time_start) {
    global $log_active, $adb, $days_detail, $days_summary, $to , $subject,$cc;
    $import_result = array();
	$import_result['records_created']=0;
	$import_result['records_updated']=0;
	$inspectionPopulate = new Inspections_Populate();
	$import_result = $inspectionPopulate->populateNow();
	return $import_result;
}

?>