<?php

include_once 'PopulateInsp.php';
function do_populate_inspections($time_start) {
	$inspectionPopulate = new Inspections_Populate();
	$import_result = $inspectionPopulate->populateNow();
	return $import_result;
}

?>