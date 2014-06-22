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

$module->setRelatedList(Vtiger_Module::getInstance('Accounts'), 'Accounts', Array('SELECT'));
$accounts = Vtiger_Module::getInstance('Accounts');
$accounts->setRelatedList(Vtiger_Module::getInstance('MailchimpSync'), 'MailchimpSync', Array('ADD','SELECT'));


$module->setRelatedList(Vtiger_Module::getInstance('Leads'), 'Leads', Array('SELECT'));
$leads = Vtiger_Module::getInstance('Leads');
$leads->setRelatedList(Vtiger_Module::getInstance('MailchimpSync'), 'MailchimpSync', Array('ADD','SELECT'));



$module->setRelatedList(Vtiger_Module::getInstance('Contacts'), 'Contacts', Array('SELECT'));
$contacts = Vtiger_Module::getInstance('Contacts');
$contacts->setRelatedList(Vtiger_Module::getInstance('MailchimpSync'), 'MailchimpSync', Array('ADD','SELECT'));

?>