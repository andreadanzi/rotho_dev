<?php

include_once 'AccRatingClass.php';
function do_populate_accrating($time_start) {
	$accRating = new AccRatingClass();
	$import_result = $accRating->populateNow();
	return $import_result;
}

?>