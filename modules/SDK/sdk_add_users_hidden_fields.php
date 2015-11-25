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
// danzi.tn@20150924 gestione agenti sempre attivi e modifiche puntuali - nuovi campi per gestire importazione semiramis

$module = Vtiger_Module::getInstance('Users');
$block1 = Vtiger_Block::getInstance('LBL_MORE_INFORMATION',$module); 

$field21 = new Vtiger_Field();
$field21->name = 'sem_importdate';
$field21->table = $module->basetable;
$field21->label= 'Semiramis first import date';
$field21->columntype = 'VARCHAR(100)';
$field21->uitype = 1;
$field21->typeofdata = 'V~O';
$block1->addField($field21);

$field21 = new Vtiger_Field();
$field21->name = 'sem_updatedate';
$field21->table = $module->basetable;
$field21->label= 'Last modify date from Semiramis';
$field21->columntype = 'VARCHAR(100)';
$field21->uitype = 1;
$field21->typeofdata = 'V~O';
$block1->addField($field21);

$field21 = new Vtiger_Field();
$field21->name = 'sem_rules';
$field21->table = $module->basetable;
$field21->label= 'Import rules';
$field21->columntype = 'VARCHAR(255)';
$field21->uitype = 1;
$field21->typeofdata = 'V~O';
$block1->addField($field21);




?>