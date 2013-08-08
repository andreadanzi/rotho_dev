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
$module_relations = Vtiger_Module::getInstance('Relations');

Vtiger_Link::addLink($module_relations->id, 'HEADERSCRIPT', 'RelationsCategoryToJS', 'modules/Relations/CategoryToRelations.js');
Vtiger_Link::addLink($module_relations->id, 'HEADERSCRIPT', 'RelationsCategoryFromJS', 'modules/Relations/CategoryFromRelations.js');


?>
