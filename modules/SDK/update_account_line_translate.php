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
// danzi.tn@20141212 nova classificazione cf_762 sostituito con vtiger_account.account_line
SDK::setLanguageEntry('Inspections','it_it','Account Category' , 'Tipo Cliente');
SDK::setLanguageEntry('Inspections','en_us','Account Category' , 'Account Type');
SDK::setLanguageEntry('Marketprices','it_it','Account Category' , 'Tipo Cliente');
SDK::setLanguageEntry('Marketprices','en_us','Account Category' , 'Account Type');
SDK::setLanguageEntry('Marketprices','it_it','Category' , 'Tipo Cliente');
SDK::setLanguageEntry('Marketprices','en_us','Category' , 'Account Type');
SDK::setLanguageEntry('Relations','it_it','Link From Category' , 'Tipo Cliente Da');
SDK::setLanguageEntry('Relations','en_us','Link From Category' , 'Account Type From');
SDK::setLanguageEntry('Relations','it_it','Link To Category' , 'Tipo Cliente A');
SDK::setLanguageEntry('Relations','en_us','Link To Category' , 'Account Type To');




?>