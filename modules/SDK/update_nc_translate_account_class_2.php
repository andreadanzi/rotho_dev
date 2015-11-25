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
//danzi.tn@20150527 traduzioni classificazione
$row = 1;
SDK::setLanguageEntry('Accounts' , 'de_de' , 'RIVENDITORE' , 'Händler');
SDK::setLanguageEntry('APP_STRINGS' , 'de_de' , 'RIVENDITORE' , 'Händler');
SDK::setLanguageEntry('Visitreport' , 'de_de' , 'RIVENDITORE' , 'Händler');
SDK::setLanguageEntry('Leads' , 'de_de' , 'RIVENDITORE' , 'Händler');
?>
