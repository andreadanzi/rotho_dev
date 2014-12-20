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

$module = Vtiger_Module::getInstance('Users');

$block1 = Vtiger_Block::getInstance('LBL_MORE_INFORMATION',$module); 

// danzi.tn@20141217 nuova classificazione
/**
Linea
*/
$field1 = new Vtiger_Field();
$field1->name = 'user_line';
$field1->table = $module->basetable;
$field1->uitype = 15;
$field1->label= 'Linea';
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$field1->setPicklistValues( Array ('---', 'RC / CARP', 'RD / DIST', 'RS / SAFE', 'RR / DIREZ', 'GD / GDO') );
$block1->addField($field1); 

SDK::setLanguageEntry('Users','it_it', 'Linea' , 'Linea di vendita');
SDK::setLanguageEntry('Users','en_us', 'Linea' , 'Sales line');
SDK::setLanguageEntry('Users','de_de', 'Linea' , 'Sales line');


?>