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
$module = Vtiger_Module::getInstance('Inspections');
$block1 = Vtiger_Block::getInstance('LBL_INSPECTION_INFORMATION',$module);


$field19 = new Vtiger_Field();
$field19->name = 'first_time_use';
$field19->label= 'First-time Use';
$field19->table = $module->basetable;
$field19->uitype = 5;
$field19->typeofdata = 'D~O';
$block1->addField($field19);

SDK::setLanguageEntry('Inspections','it_it','First-time Use' , 'Data primo utilizzo');
SDK::setLanguageEntry('Inspections','en_us', 'First-time Use' , 'First-time Use');
SDK::setLanguageEntry('Inspections','de_de', 'First-time Use' , 'Erstmalige Verwendung');



?>
