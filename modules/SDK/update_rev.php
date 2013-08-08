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

// Create module instance and save it first
// $module = Vtiger_Module::getInstance('Inspections');
// $module->setRelatedList(Vtiger_Module::getInstance('ModComments'), 'Comments',Array('ADD'));

// $commentsModule = Vtiger_Module::getInstance('ModComments');
// $fieldInstance = Vtiger_Field::getInstance('related_to', $commentsModule);
// $fieldInstance->setRelatedModules(array('Inspections'));


require_once 'modules/ModComments/ModComments.php';
$detailviewblock_nc = ModComments::addWidgetTo('Nonconformities');
$detailviewblock_rel = ModComments::addWidgetTo('Relations');

?>
