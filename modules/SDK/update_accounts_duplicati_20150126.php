<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix;
session_start();
// danzi.tn@20150126 gestione duplicati
// Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');

// Get module instance
$module = Vtiger_Module::getInstance('Accounts');

$block1 = Vtiger_Block::getInstance('LBL_ACCOUNT_INFORMATION',$module); 


// VERIFICA DUPLICATI
/*
$field1 = new Vtiger_Field();
$field1->name = 'account_to_be_deleted';
$field1->table = $module->basetable;
$field1->uitype = 15;
$field1->readonly = 1;
$field1->label= 'Duplicate - Must be deleted';
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$field1->setPicklistValues( Array ('---', 'DELETE_YES', 'DELETE_NO') );
$block1->addField($field1); */

$field13 = new Vtiger_Field();
$field13->name = 'duplicated_accountid';
$field13->table = $module->basetable;
$field13->label= 'Duplicated Account ID';
$field13->columntype = 'VARCHAR(100)';
$field13->uitype = 1;
$field13->readonly = 99; //99 readonly 100 invisibile
$field13->typeofdata = 'V~O';
$block1->addField($field13); 

$field20 = new Vtiger_Field();
$field20->name = 'duplicated_delete_date';
$field20->label= 'Data Cancellazione Prevista';
$field20->columntype = 'VARCHAR(100)';
$field20->table = $module->basetable;
$field20->uitype = 1;
$field20->readonly = 99; //99 readonly 100 invisibile
$field20->typeofdata = 'V~O';
$block1->addField($field20);

SDK::setLanguageEntry('Accounts' ,'it_it' ,'Duplicate - Must be deleted' ,'Duplicata - Deve essere cancellata');
SDK::setLanguageEntry('Accounts' ,'en_us' ,'Duplicate - Must be deleted' ,'Duplicate - Must be deleted');
SDK::setLanguageEntry('Accounts' ,'de_de' ,'Duplicate - Must be deleted' ,'Duplicate - Must be deleted');
SDK::setLanguageEntry('Accounts' ,'fr_fr' ,'Duplicate - Must be deleted' ,'Duplicate - Must be deleted');
SDK::setLanguageEntry('Accounts' ,'pt_pt' ,'Duplicate - Must be deleted' ,'Duplicate - Must be deleted');
SDK::setLanguageEntry('Accounts' ,'es_es' ,'Duplicate - Must be deleted' ,'Duplicate - Must be deleted');
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'Duplicate - Must be deleted' ,'Duplicate - Must be deleted');

SDK::setLanguageEntry('Accounts' ,'it_it' ,'Duplicated Account ID' ,'ID Azienda Duplicata');
SDK::setLanguageEntry('Accounts' ,'en_us' ,'Duplicated Account ID' ,'Duplicated Account ID');
SDK::setLanguageEntry('Accounts' ,'de_de' ,'Duplicated Account ID' ,'Duplicated Account ID');
SDK::setLanguageEntry('Accounts' ,'fr_fr' ,'Duplicated Account ID' ,'Duplicated Account ID');
SDK::setLanguageEntry('Accounts' ,'pt_pt' ,'Duplicated Account ID' ,'Duplicated Account ID');
SDK::setLanguageEntry('Accounts' ,'es_es' ,'Duplicated Account ID' ,'Duplicated Account ID');
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'Duplicated Account ID' ,'Duplicated Account ID');
?>
