<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix,$default_charset;
session_start();
// html_entity_decode($description, ENT_NOQUOTES, $default_charset);
// htmlentities( , ENT_NOQUOTES, $default_charset);
//Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');
//danzi.tn@20150521 traduzioni DE

// LBL_NONCONFORMITY_QUALITY
SDK::setLanguageEntry("Nonconformities","it_it","LBL_NONCONFORMITY_QUALITY","Controllo qualit&agrave;");
SDK::setLanguageEntry("Nonconformities","en_us","LBL_NONCONFORMITY_QUALITY","Quality Control");
SDK::setLanguageEntry("APP_STRINGS","it_it","LBL_NONCONFORMITY_QUALITY","Controllo qualit&agrave;");
SDK::setLanguageEntry("APP_STRINGS","en_us","LBL_NONCONFORMITY_QUALITY","Quality Control");
?>
