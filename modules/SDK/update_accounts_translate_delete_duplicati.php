<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix;
session_start();
// danzi.tn@20150126 gestione duplicati
// Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');

// Get module instance

SDK::setLanguageEntry('Accounts' ,'it_it' ,'DELETE_YES' ,'Si');
SDK::setLanguageEntry('Accounts' ,'en_us' ,'DELETE_YES' ,'Yes');
SDK::setLanguageEntry('Accounts' ,'de_de' ,'DELETE_YES' ,'Yes');
SDK::setLanguageEntry('Accounts' ,'fr_fr' ,'DELETE_YES' ,'Yes');
SDK::setLanguageEntry('Accounts' ,'pt_pt' ,'DELETE_YES' ,'Yes');
SDK::setLanguageEntry('Accounts' ,'es_es' ,'DELETE_YES' ,'Yes');
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'DELETE_YES' ,'Yes');

SDK::setLanguageEntry('Accounts' ,'it_it' ,'DELETE_NO' ,'No');
SDK::setLanguageEntry('Accounts' ,'en_us' ,'DELETE_NO' ,'No');
SDK::setLanguageEntry('Accounts' ,'de_de' ,'DELETE_NO' ,'No');
SDK::setLanguageEntry('Accounts' ,'fr_fr' ,'DELETE_NO' ,'No');
SDK::setLanguageEntry('Accounts' ,'pt_pt' ,'DELETE_NO' ,'No');
SDK::setLanguageEntry('Accounts' ,'es_es' ,'DELETE_NO' ,'No');
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'DELETE_NO' ,'No');
?>
