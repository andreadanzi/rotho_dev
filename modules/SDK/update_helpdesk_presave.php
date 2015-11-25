<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix;
session_start();
// danzi.tn@20151019 gestione Tipologia per NC - SDK::setPreSave($module, $src)
// Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');

SDK::setPreSave('HelpDesk', "modules/SDK/src/modules/HelpDesk/PresaveHelpDesk.php");



?>
