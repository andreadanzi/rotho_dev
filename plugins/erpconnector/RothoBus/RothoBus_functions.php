<?php

include_once 'RothoBusClass.php';
function do_rotho_bus($time_start) {
	$rothoBusClass = new RothoBus();
	$import_result = $rothoBusClass->populateNow();
	return $import_result;
}

?>