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
$module = Vtiger_Module::getInstance('Vendors');
$block1 = Vtiger_Block::getInstance('LBL_VENDOR_INFORMATION',$module);

$field7 = new Vtiger_Field();
$field7->name = 'vendor_rating';//vte_installation_state
$field7->table = $module->basetable;
$field7->label = 'Vendor Rating';
$field7->uitype = 15;
$field7->columntype = 'VARCHAR(255)';
$field7->typeofdata = 'V~O';// Varchar~Optional
$block1->addField($field7); /** table and column are automatically set */
$field7->setPicklistValues( Array ( 'A', 'B', 'C', 'D', '-- NA --') );
SDK::setLanguageEntry('Vendors','it_it','Vendor Rating' , 'Rating Fornitore');
SDK::setLanguageEntry('Vendors','en_us', 'Vendor Rating' , 'Vendor Rating');

$field8 = new Vtiger_Field();
$field8->name = 'purchase_user_id';
$field8->label = 'Purchase User';
$field8->uitype = 53;
$field8->typeofdata = 'V~M';
$field8->quickcreate = 0;
$block1->addField($field8);
SDK::setLanguageEntry('Vendors','it_it','Purchase User' , 'Responsabile Acquisto');
SDK::setLanguageEntry('Vendors','en_us', 'Purchase User' , 'Purchase Manager');

$field9 = new Vtiger_Field();
$field9->name = 'product_user_id';
$field9->label = 'Product User';
$field9->uitype = 53;
$field9->typeofdata = 'V~M';
$field9->quickcreate = 0;
$block1->addField($field9);
SDK::setLanguageEntry('Vendors','it_it','Product User' , 'Responsabile Prodotto');
SDK::setLanguageEntry('Vendors','en_us', 'Product User' , 'Product Manager');


?>
