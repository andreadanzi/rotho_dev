<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix;
session_start();
// danzi.tn@20150521 added filled_by_id e cq_user_id
// Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');

// Create module instance and save it first
$module = Vtiger_Module::getInstance('Nonconformities');

// Add the evaluation block
$block1 = Vtiger_Block::getInstance('LBL_NONCONFORMITY_INFORMATION',$module);

$field8 = new Vtiger_Field();
$field8->name = 'filled_by_id';
$field8->label = 'Compilato da';
$field8->uitype = 52;
$field8->typeofdata = 'V~M';
$field8->quickcreate = 0;
$block1->addField($field8);

$block2 = Vtiger_Block::getInstance('LBL_NONCONFORMITY_QUALITY',$module);

$field8 = new Vtiger_Field();
$field8->name = 'cq_user_id';
$field8->label = 'Operatore CQ';
$field8->uitype = 1077;
$field8->typeofdata = 'V~O';
$field8->quickcreate = 0;
$block2->addField($field8);

?>