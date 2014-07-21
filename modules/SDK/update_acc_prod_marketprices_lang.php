
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

// Get module instance
SDK::setLanguageEntry('Products','it_it', 'SINGLE_Marketprices' , 'Market Price');
SDK::setLanguageEntry('Products','en_us', 'SINGLE_Marketprices' , 'Market Price');
SDK::setLanguageEntry('Products','de_de', 'SINGLE_Marketprices' , 'Market Price');

SDK::setLanguageEntry('Accounts','it_it', 'SINGLE_Marketprices' , 'Market Price');
SDK::setLanguageEntry('Accounts','en_us', 'SINGLE_Marketprices' , 'Market Price');
SDK::setLanguageEntry('Accounts','de_de', 'SINGLE_Marketprices' , 'Market Price');

?>
