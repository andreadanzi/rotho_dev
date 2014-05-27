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

Vtiger_Link::addLink(6, 'HEADERSCRIPT', 'OpenDocCredit', 'modules/SDK/src/js/OpenDocCredit.js');
SDK::setExtraSrc('SDK', 'modules/SDK/src/js/OpenDocCredit.js');

?>