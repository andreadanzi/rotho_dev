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

// Create module instance and save it first
SDK::setLanguageEntry('APP_STRINGS','it_it', 'KLIMAHOUSE 2014 BZ' , 'Fiera KLIMAHOUSE 2014 Bolzano');
SDK::setLanguageEntry('APP_STRINGS','en_us', 'KLIMAHOUSE 2014 BZ' , 'Fiera KLIMAHOUSE 2014 Bolzano');
SDK::setLanguageEntry('APP_STRINGS','de_de', 'KLIMAHOUSE 2014 BZ' , 'KLIMAHOUSE 2014 Bozen');
SDK::setLanguageEntry('APP_STRINGS','it_it', 'Fiera KLIMAHOUSE 2014' , 'Porte Aperte KLIMAHOUSE 2014');
SDK::setLanguageEntry('APP_STRINGS','en_us', 'Fiera KLIMAHOUSE 2014' , 'Open Doors KLIMAHOUSE 2014');
SDK::setLanguageEntry('APP_STRINGS','de_de', 'Fiera KLIMAHOUSE 2014' , 'Open Doors KLIMAHOUSE 2014');


?>
