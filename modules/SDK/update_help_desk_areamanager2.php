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



SDK::setLanguageEntry('HelpDesk','it_it', 'Area Manager Name' , 'Nome Area Manager');

// $module->setRelatedList(Vtiger_Module::getInstance('Products'), 'Products', Array('SELECT'));

?>