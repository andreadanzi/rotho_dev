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

$module->setRelatedList(Vtiger_Module::getInstance('Products'), 'Other Products', Array('SELECT'));


SDK::setLanguageEntry('HelpDesk','it_it', 'Other Products' , 'Altri Prodotti Collegati');
SDK::setLanguageEntry('HelpDesk','en_us', 'Other Products' , 'Other Related Products');
SDK::setLanguageEntry('HelpDesk','de_de', 'Other Products' , 'Other Related Products');

?>