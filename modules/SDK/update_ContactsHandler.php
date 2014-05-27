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

// danzi.tn@20140213 registrazione Handler per IMPORTFLAG necessario a distinguere i record modificati/creati dalla procedura di import
$module = Vtiger_Module::getInstance('Contacts');
Vtiger_Event::register($module ,'vtiger.entity.beforesave','ContactsHandler','modules/SDK/src/modules/Contacts/ContactsHandler.php');
?>