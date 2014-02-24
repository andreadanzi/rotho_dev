<?php

include_once 'AccRatingClass.php';
include_once 'AccRatingClassCARP.php';
function do_populate_accrating($time_start) {
	$accRating = new AccRatingClass();
	$import_result = $accRating->populateNow();
	// danzi.tn@20140224 - GESTIONE RC / CARP per Sudamerica - RICORDARSI DELETE MANUALE
	$accRatingCARP = new AccRatingClassCARP();
	$import_result_carp = $accRatingCARP->populateNow();
	$result = array_merge($import_result, $import_result_carp);
	return $result;
}

?>