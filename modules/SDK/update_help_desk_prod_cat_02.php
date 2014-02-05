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


SDK::setLanguageEntry('HelpDesk','it_it', 'Rothoblaas Category' , 'Categoria Prodotto');
SDK::setLanguageEntry('HelpDesk','en_us', 'Rothoblaas Category' , 'Product Category');
SDK::setLanguageEntry('HelpDesk','de_de', 'Rothoblaas Category' , 'Product Category');

SDK::setLanguageEntry('HelpDesk','it_it', 'Rothoblaas Category Description' , 'Descrizione categoria');
SDK::setLanguageEntry('HelpDesk','en_us', 'Rothoblaas Category Description' , 'Category Description');
SDK::setLanguageEntry('HelpDesk','de_de', 'Rothoblaas Category Description' , 'Category Description');

?>
