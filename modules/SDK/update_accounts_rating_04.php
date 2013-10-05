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
// Retrieve module instance 
$module = Vtiger_Module::getInstance('Accounts');
/* get block */
$block1 = Vtiger_Block::getInstance('LBL_ACCOUNT_INFORMATION',$module);
/* create the new field Return time */
$field1 = new Vtiger_Field();
$field1->name = 'return_time';
$field1->table = $module->basetable;
$field1->label = 'Return Time';
$field1->uitype = 15;
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$block1->addField($field1); /** table and column are automatically set */
$field1->setPicklistValues( Array ('1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12') );

// Ricordarsi settare valore di default a 4 per tutti

SDK::setLanguageEntry('Accounts','it_it','Return Time' , 'Tempo di ritorno (mesi)');
SDK::setLanguageEntry('Accounts','en_us', 'Return Time' , 'Return Time (months)');





?>
