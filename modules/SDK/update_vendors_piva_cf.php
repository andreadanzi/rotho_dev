<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix;
session_start();
// danzi.tn@20140520 partita iva e cf per Fornitore
// Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');

// Create module instance and save it first
$module = Vtiger_Module::getInstance('Vendors');
$block1 = Vtiger_Block::getInstance('LBL_VENDOR_INFORMATION',$module);

$field6 = new Vtiger_Field();
$field6->name = 'vendor_vat_code';
$field6->label= 'VAT Code';
$field6->table = $module->basetable;
$field6->columntype = 'VARCHAR(255)';
$field6->uitype = 1;
$field6->typeofdata = 'V~O';
$field6->quickcreate = 1;
$block1->addField($field6);


$field6 = new Vtiger_Field();
$field6->name = 'vendor_fiscal_code';
$field6->label= 'Fiscal Code';
$field6->table = $module->basetable;
$field6->columntype = 'VARCHAR(255)';
$field6->uitype = 1;
$field6->typeofdata = 'V~O';
$field6->quickcreate = 1;
$block1->addField($field6);

SDK::setLanguageEntry('Vendors','en_us', 'VAT Code' , 'VAT Code');
SDK::setLanguageEntry('Vendors','it_it','VAT Code' , 'Partita IVA');

SDK::setLanguageEntry('Vendors','en_us', 'Fiscal Code' , 'Fiscal Code');
SDK::setLanguageEntry('Vendors','it_it','Fiscal Code' , 'Codice Fiscale');
// danzi.tn@20140520e

?>
