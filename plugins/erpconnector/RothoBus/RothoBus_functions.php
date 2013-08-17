<?php

include_once 'WebsiteClass.php';
include_once 'RothoBusClass.php';
function do_rotho_bus($time_start) {
        $rothoWebsiteClass = new WebsiteClass();
        $rothoWebsiteClass->setLog(false);
        $rothoWebsiteClass->populateNow();
	$rothoBusClass = new RothoBus();
	$rothoBusClass->setLog(false);
	$rothoBusClass->setExistingCourses(true);
	$import_result = $rothoBusClass->populateNow();
	return $import_result;
}

?>
