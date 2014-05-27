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

$module = Vtiger_Module::getInstance('Products');

$block1 = Vtiger_Block::getInstance('LBL_PRODUCT_INFORMATION',$module); 

/** danzi.tn@20140411 Categoria corrispondente Rothoblaas - sostituisce */
$field20 = new Vtiger_Field();
$field20->name = 'product_cat';
$field20->table = $module->basetable;
$field20->label= 'Rothoblaas Category';
$field20->columntype = 'VARCHAR(255)';
$field20->uitype = 2001;
$field20->typeofdata = 'V~O';
$field20->quickcreate = 0;
$block1->addField($field20);

/** Descrizione Categoria corrispondente Rothoblaas */
$field21 = new Vtiger_Field();
$field21->name = 'product_cat_descr';
$field21->column  ='prod_category_desc';
$field21->table = $module->basetable;
$field21->label= 'Rothoblaas Category Description';
$field21->columntype = 'VARCHAR(255)';
$field21->uitype = 2002;
$field21->typeofdata = 'V~O';
$field21->quickcreate = 0;
$block1->addField($field21);

SDK::setLanguageEntry('Products','it_it', 'Rothoblaas Category' , 'Categoria Prodotto');
SDK::setLanguageEntry('Products','en_us', 'Rothoblaas Category' , 'Product Category');
SDK::setLanguageEntry('Products','de_de', 'Rothoblaas Category' , 'Product Category');

SDK::setLanguageEntry('Products','it_it', 'Rothoblaas Category Description' , 'Descrizione Categoria Prodotto');
SDK::setLanguageEntry('Products','en_us', 'Rothoblaas Category Description' , 'Category Description');
SDK::setLanguageEntry('Products','de_de', 'Rothoblaas Category Description' , 'Category Description');




?>
