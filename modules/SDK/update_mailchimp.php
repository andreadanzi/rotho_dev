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



$module = Vtiger_Module::getInstance('MailchimpSync');
$module->initWebservice();

$module->addLink('DETAILVIEW', 'Synchronize with MailChimp', 'index.php?module=MailchimpSync&action=SyncWithMailChimp&src_module=$MODULE$&src_record=$RECORD$');

$targets = Vtiger_Module::getInstance('Targets');
$targets->addLink('DETAILVIEW', 'Synchronize with MailChimp', 'index.php?module=MailchimpSync&action=SyncWithMailChimp&src_module=$MODULE$&src_record=$RECORD$');
?>