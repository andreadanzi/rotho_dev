<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix;
session_start();
// danzi.tn@20141126 nuova classificazione aggiornato
// Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');

// Get module instance
$module = Vtiger_Module::getInstance('Leads');

$block1 = Vtiger_Block::getInstance('Informazioni Corsi',$module); 


/**
Overnight
*/
$field1 = new Vtiger_Field();
$field1->name = 'overnight_option';
$field1->table = $module->basetable;
$field1->uitype = 15;
$field1->label= 'Scelta pernotto';
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$field1->setPicklistValues( Array ('---', 'Notte precedente al corso', 'Notte del primo giorno di corso','Altro') );
$block1->addField($field1); 
?>
