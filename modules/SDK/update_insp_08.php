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


SDK::setLanguageEntry('Inspections','it_it','salesorder_ref' , 'Rif. Ordine');
SDK::setLanguageEntry('Inspections','en_us', 'salesorder_ref' , 'Sales Order Ref.');
SDK::setLanguageEntry('Inspections','it_it','note' , 'Nota');
SDK::setLanguageEntry('Inspections','en_us', 'note' , 'Note');//
SDK::setLanguageEntry('Inspections','it_it','MODULO REVISIONE DPI' , 'MODULO REVISIONE DPI');
SDK::setLanguageEntry('Inspections','en_us', 'MODULO REVISIONE DPI' , 'INSPECTION TEMPLATE');



?>
