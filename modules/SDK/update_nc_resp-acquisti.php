<?php
// danzi.tn@20141011 prime modifiche valutazione non conformità
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix;
session_start();
// danzi.tn@20150316 added purchase_user_id
// Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');

// Create module instance and save it first
$module = Vtiger_Module::getInstance('Nonconformities');

// Add the evaluation block
$block1 = Vtiger_Block::getInstance('LBL_NONCONFORMITY_INFORMATION',$module);

$field8 = new Vtiger_Field();
$field8->name = 'purchase_user_id';
$field8->label = 'Purchase User';
$field8->uitype = 53;
$field8->typeofdata = 'V~M';
$field8->quickcreate = 0;
$block1->addField($field8);
SDK::setLanguageEntry('Nonconformities','it_it','Purchase User' , 'Responsabile Acquisto');
SDK::setLanguageEntry('Nonconformities','en_us', 'Purchase User' , 'Purchase Manager');
SDK::setLanguageEntry('Nonconformities','de_de', 'Purchase User' , 'Purchase Manager');




?>