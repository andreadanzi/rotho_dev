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

$module = Vtiger_Module::getInstance('HelpDesk');

$block1 = Vtiger_Block::getInstance('LBL_TICKET_INFORMATION',$module); 

// danzi.tn@20140307 nuovo flag per assegazione a marketing

$field21 = new Vtiger_Field();
$field21->name = 'ticketismarketing';
$field21->column = 'ismarketing';
$field21->table = $module->basetable;
$field21->label= 'Marketing';
$field21->columntype = 'VARCHAR(3)';
$field21->uitype = 56;
$field21->typeofdata = 'C~O';
$block1->addField($field21);

SDK::setLanguageEntry('HelpDesk','it_it', 'Marketing' , 'Marketing');
SDK::setLanguageEntry('HelpDesk','en_us', 'Marketing' , 'Marketing');
SDK::setLanguageEntry('HelpDesk','de_de', 'Marketing' , 'Marketing');

// $module->setRelatedList(Vtiger_Module::getInstance('Products'), 'Products', Array('SELECT'));

?>