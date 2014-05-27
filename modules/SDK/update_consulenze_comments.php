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
// danzi.tn@20140424 modificare la NC aggiungendo il flag Interna o Fornitore
// danzi.tn@20140424 in NC aggiungere i commenti
// danzi.tn@20140424 nel Fornitore cambiare il campo “Categoria” con la popup della classificazione articoli
$module = Vtiger_Module::getInstance('Nonconformities');
$block_info = Vtiger_Block::getInstance('LBL_NONCONFORMITY_INFORMATION',$module); 

$field12 = new Vtiger_Field();
$field12->name = 'nc_source';
$field12->table = $module->basetable;
$field12->label= 'Source';
$field12->columntype = 'VARCHAR(255)';
$field12->uitype = 15;
$field12->typeofdata = 'V~O';
$block_info->addField($field12);
$field12->setPicklistValues( Array ('ND','Internal', 'Vendor', 'Other') );

SDK::setLanguageEntry('Nonconformities','it_it', 'Source' , 'Fonte');
SDK::setLanguageEntry('Nonconformities','en_us', 'Source' , 'Source');
SDK::setLanguageEntry('Nonconformities','de_de', 'Source' , 'Source');

SDK::setLanguageEntry('Nonconformities','it_it', 'ND' , 'Nessuna');
SDK::setLanguageEntry('Nonconformities','en_us', 'ND' , 'None');
SDK::setLanguageEntry('Nonconformities','de_de', 'ND' , 'None');

SDK::setLanguageEntry('Nonconformities','it_it', 'Internal' , 'Interna');
SDK::setLanguageEntry('Nonconformities','en_us', 'Internal' , 'Internal');
SDK::setLanguageEntry('Nonconformities','de_de', 'Internal' , 'Internal');

SDK::setLanguageEntry('Nonconformities','it_it', 'Vendor' , 'Fornitore');
SDK::setLanguageEntry('Nonconformities','en_us', 'Vendor' , 'Vendor');
SDK::setLanguageEntry('Nonconformities','de_de', 'Vendor' , 'Vendor');

SDK::setLanguageEntry('Nonconformities','it_it', 'Other' , 'Altro');
SDK::setLanguageEntry('Nonconformities','en_us', 'Other' , 'Other');
SDK::setLanguageEntry('Nonconformities','de_de', 'Other' , 'Other');

$module2 = Vtiger_Module::getInstance('Vendors');

$block21 = Vtiger_Block::getInstance('LBL_VENDOR_INFORMATION',$module2); 

/** danzi.tn@20140424 Categoria corrispondente Rothoblaas - sostituisce Category */
$field20 = new Vtiger_Field();
$field20->name = 'product_cat';
$field20->table = $module2->basetable;
$field20->label= 'Rothoblaas Category';
$field20->columntype = 'VARCHAR(255)';
$field20->uitype = 2001;
$field20->typeofdata = 'V~O';
$field20->quickcreate = 0;
$block21->addField($field20);

/** Descrizione Categoria corrispondente Rothoblaas */
$field21 = new Vtiger_Field();
$field21->name = 'product_cat_descr';
$field21->column  ='prod_category_desc';
$field21->table = $module2->basetable;
$field21->label= 'Rothoblaas Category Description';
$field21->columntype = 'VARCHAR(255)';
$field21->uitype = 2002;
$field21->typeofdata = 'V~O';
$field21->quickcreate = 0;
$block21->addField($field21);

SDK::setLanguageEntry('Vendors','it_it', 'Rothoblaas Category' , 'Categoria Prodotto');
SDK::setLanguageEntry('Vendors','en_us', 'Rothoblaas Category' , 'Product Category');
SDK::setLanguageEntry('Vendors','de_de', 'Rothoblaas Category' , 'Product Category');

SDK::setLanguageEntry('Vendors','it_it', 'Rothoblaas Category Description' , 'Descrizione Categoria Prodotto');
SDK::setLanguageEntry('Vendors','en_us', 'Rothoblaas Category Description' , 'Category Description');
SDK::setLanguageEntry('Vendors','de_de', 'Rothoblaas Category Description' , 'Category Description');

require_once 'modules/ModComments/ModComments.php';
$detailviewblock_nc = ModComments::addWidgetTo('Consulenza');

?>
