<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix;
session_start();
// danzi.tn@20140505 Numero Consulenza Garanzia Zenit
// Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');

$module = Vtiger_Module::getInstance('Consulenza');

$block1 = Vtiger_Block::getInstance('LBL_CONSULENZA_INFORMATION',$module); 

$field6 = new Vtiger_Field();
$field6->name = 'warranty_serial_no';
$field6->label= 'Warranty Serial No';
$field6->table = $module->basetable;
$field6->columntype = 'VARCHAR(255)';
$field6->uitype = 1;
$field6->readonly = 99;
$field6->typeofdata = 'V~O';
$field6->quickcreate = 0;
$block1->addField($field6);

SDK::setLanguageEntry('Consulenza','en_us', 'Warranty Serial No' , 'Warranty Serial No');
SDK::setLanguageEntry('Consulenza','it_it','Warranty Serial No' , 'Numero Garanzia');
// danzi.tn@20140505e Consulenza Garanzia Zenit

?>
