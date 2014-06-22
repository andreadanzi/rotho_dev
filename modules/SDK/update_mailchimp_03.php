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

$block1 = Vtiger_Block::getInstance('LBL_MAILCHIMP_INFORMATION',$module);

$field16 = new Vtiger_Field();
$field16->name = 'mailchimp_uid';
$field16->label= 'MailChimp Campaign UID';
$field16->table = $module->basetable;
$field16->columntype = 'VARCHAR(255)';
$field16->uitype = 1;
$field16->typeofdata = 'V~O';
$field16->quickcreate = 1;
$block1->addField($field16);


$field26 = new Vtiger_Field();
$field26->name = 'mailchimp_link';
$field26->label= 'MailChimp Campaign Link';
$field26->table = $module->basetable;
$field26->columntype = 'VARCHAR(255)';
$field26->uitype = 17;
$field26->typeofdata = 'V~O';
$field26->quickcreate = 1;
$block1->addField($field26);

?>