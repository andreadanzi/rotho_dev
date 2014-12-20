<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix;
session_start();
// danzi.tn@20141217 nuova classificazione da report visite
// Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');

// Get module instance
$module = Vtiger_Module::getInstance('Leads');

$block1 = Vtiger_Block::getInstance('LBL_LEAD_INFORMATION',$module); 
// danzi.tn@20141217 nuova classificazione
/**
Linea
*/
$field1 = new Vtiger_Field();
$field1->name = 'leads_client_type';
$field1->table = $module->basetable;
$field1->uitype = 15;
$field1->label= 'Tipo Cliente';
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$field1->setPicklistValues( Array ('---', 'UTILIZZATORE', 'RIVENDITORE', 'PROGETTISTA', 'INFLUENZATORE') );
$block1->addField($field1); 

?>
