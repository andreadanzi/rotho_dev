<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix;
session_start();

// Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');

// Create module instance and save it first

SDK::setLanguageEntry('Inspections','it_it','Inspection Name' , 'Nome Revisione');
SDK::setLanguageEntry('Inspections','en_us', 'Inspection Name' , 'Inspection Name');
SDK::setLanguageEntry('Inspections','it_it','Inspection State' , 'Stato Revisione');
SDK::setLanguageEntry('Inspections','en_us', 'Inspection State' , 'Inspection State');



?>
