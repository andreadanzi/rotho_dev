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
	'name'=>'points',
	'label'=>'Points',
	'uitype'=>7,
	'sdk_uitype'=>616,
	'columntype'=>'INT(19)',
	'typeofdata'=>'N~O~10,0',
);
include('modules/SDK/examples/fieldCreate.php');
/* register it */
$srcdir = 'modules/SDK/src/';
SDK::setUitype(616,$srcdir.'uitypejQuery/616.php',$srcdir.'uitypejQuery/616.tpl',$srcdir.'uitypejQuery/616.js');
// Vtiger_Link::addLink($moduleInstance->id,'HEADERSCRIPT','uitypejQueryScript',$srcdir.'uitypejQuery/search_engine_script/jquery.bgiframe.pack.js');
// SDK::setExtraSrc($module, $srcdir.'uitypejQuery/');
SDK::setLanguageEntry('Accounts','it_it','Points' , 'Punteggio RP/PROG');
SDK::setLanguageEntry('Accounts','en_us', 'Points' , 'RP/PROG Rating');


?>
