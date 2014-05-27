<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix;
session_start();

// danzi.tn@20140412 nuovo campo certificazioni e collegamento a Documenti
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');

$module = Vtiger_Module::getInstance('Vendors');

$block_info = Vtiger_Block::getInstance('LBL_VENDOR_INFORMATION',$module); 


$field22 = new Vtiger_Field();
$field22->name = 'vendor_certification';
$field22->table = $module->basetable;
$field22->label= 'Certifications';
$field22->columntype = 'VARCHAR(100)';
$field22->uitype = 33;
$field22->typeofdata = 'V~O';
$block_info->addField($field22);
$field22->setPicklistValues( Array ('None', 'ISO9001', 'Other') );



SDK::setLanguageEntry('Vendors','it_it', 'Certifications' , 'Certificazioni');
SDK::setLanguageEntry('Vendors','en_us', 'Certifications' , 'Certifications');
SDK::setLanguageEntry('Vendors','de_de', 'Certifications' , 'Certifications');

SDK::setLanguageEntry('Vendors','it_it', 'None' , '-- Nessuna --');
SDK::setLanguageEntry('Vendors','en_us', 'None' , '-- None --');
SDK::setLanguageEntry('Vendors','de_de', 'None' , '-- None --');

SDK::setLanguageEntry('Vendors','it_it', 'ISO9001' , 'ISO 9001');
SDK::setLanguageEntry('Vendors','en_us', 'ISO9001' , 'ISO 9001');
SDK::setLanguageEntry('Vendors','de_de', 'ISO9001' , 'ISO 9001');

SDK::setLanguageEntry('Vendors','it_it', 'Other' , 'Altro');
SDK::setLanguageEntry('Vendors','en_us', 'Other' , 'Other');
SDK::setLanguageEntry('Vendors','de_de', 'Other' , 'Other');

$module->setRelatedList(Vtiger_Module::getInstance('Documents'), 'Documents',Array('ADD','SELECT'),'get_attachments');


?>