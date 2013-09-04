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
/* set up uitypejQuery */
/* create the new field Points */
$fields = array();
$fields[] = array(
	'module'=>'Accounts',
	'block'=>'LBL_ACCOUNT_INFORMATION',
	'name'=>'input_points',
	'label'=>'Input Points',
	'uitype'=>7,
	'columntype'=>'INT(19)',
	'typeofdata'=>'NN~O~10,0',
);
include('modules/SDK/examples/fieldCreate.php');
/* register it */
SDK::setLanguageEntry('Accounts','it_it','Input Points' , 'Valore base RP/PROG');
SDK::setLanguageEntry('Accounts','en_us', 'Input Points' , 'RP/PROG base value');

// Vtiger_Event::register($module ,'vtiger.entity.beforesave','AccountsHandler','modules/SDK/src/modules/Accounts/AccountsHandler.php');
// Vtiger_Event::register($module ,'vtiger.entity.aftersave','RelationsHandler','modules/SDK/src/modules/Accounts/AccountsHandler.php');

?>
