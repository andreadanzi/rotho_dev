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
$uidfield = new Vtiger_Field();
$uidfield->name = 'inspection_uid';
$uidfield->table = $module->basetable;
$uidfield->label= 'Inspection UID';
$uidfield->columntype = 'VARCHAR(255)';
$uidfield->uitype = 1;
$uidfield->typeofdata = 'V~O';
$uidfield->quickcreate = 1;
$block1->addField($uidfield); 
SDK::setLanguageEntry('Products','it_it','Inspection UID' , 'UID Revisione');
SDK::setLanguageEntry('Products','en_us', 'Inspection UID' , 'Inspection UID');

$progfield = new Vtiger_Field();
$progfield->name = 'inspection_sequence';
$progfield->table = $module->basetable;
$progfield->label= 'Inspection Sequence';
$progfield->uitype = 7;
$progfield->columntype = 'INT(19)';
$progfield->typeofdata = 'N~O~10,0';// Var
$block1->addField($progfield); 
SDK::setLanguageEntry('Products','it_it','Inspection Sequence' , 'Sequenza Revisione');
SDK::setLanguageEntry('Products','en_us', 'Inspection Sequence' , 'Inspection Sequence');


?>
