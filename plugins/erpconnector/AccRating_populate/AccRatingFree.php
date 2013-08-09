<?php
global $adb,$adbext,$module,$current_user,$mapping,$external_code,$table,$where,$fields_auto_create,$fields_auto_update;

echo "aa\n";
require('AccRating_config.php');
echo "a\n";

echo "a\n";
//scrivo sul db lo stato dello script
$time_start = time();
echo "b\n";
$records=do_populate_accrating(date("Y-m-d H:i:s",$time_start));
$time_end = time();

echo "c\n";
//scrivo sul db i dati sull'esecuzione dello script
$duration = $time_end - $time_start;
$duration_min = intval ($duration / 60);
$duration_sec = $duration - $duration_min * 60;
$duration_string = $duration_min."m:".$duration_sec."s";



?>