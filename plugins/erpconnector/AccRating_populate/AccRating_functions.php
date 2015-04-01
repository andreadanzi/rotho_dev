<?php

include_once 'AccRatingClassALL.php';
function do_populate_accrating($time_start) {
    // danzi.tn@20150330 gestione unificata
	$accRating = new AccRatingClassALL();
	$result = $accRating->populateNow();
	return $result;
}

?>