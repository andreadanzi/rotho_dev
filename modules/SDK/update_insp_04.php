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
$block1 = Vtiger_Block::getInstance('LBL_ORDER_INFORMATION',$module);
$uidfield = new Vtiger_Field();
$uidfield->name = 'product_description';
$uidfield->table = $module->basetable;
$uidfield->label= 'Product Description';
$uidfield->columntype = 'VARCHAR(255)';
$uidfield->uitype = 1;
$uidfield->typeofdata = 'V~O';
$uidfield->quickcreate = 0;
$block1->addField($uidfield); 
SDK::setLanguageEntry('Inspections','it_it','Product Description' , 'Descrizione Prodotto');
SDK::setLanguageEntry('Inspections','en_us', 'Product Description' , 'Product Description');



?>
