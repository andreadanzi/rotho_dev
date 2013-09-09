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

$rum = Vtiger_Module::getInstance('Rumors');
require_once 'modules/ModComments/ModComments.php';
$detailviewblock_nc = ModComments::addWidgetTo('Rumors');

?>
