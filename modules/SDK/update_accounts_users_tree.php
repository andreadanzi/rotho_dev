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
// Retrieve module instance
$srcdir = 'modules/SDK/src/';
$tmpldir = 'Smarty/templates/';
$module = Vtiger_Module::getInstance('Accounts');
// danzi.tn@20150825 tree on user array (for listview)
Vtiger_Link::deleteLink($module->id,'HEADERSCRIPT','USER_TREE_JS');
Vtiger_Link::addLink($module->id,'HEADERSCRIPT','USER_TREE_JS',$srcdir.'js/user_tree.js');
Vtiger_Link::deleteLink($module->id,'HEADERCSS','USER_TREE_CSS');
Vtiger_Link::addLink($module->id,'HEADERCSS','USER_TREE_CSS',$srcdir.'css/user_tree.css');

SDK::unsetSmartyTemplate(array( 'module'=>'Accounts', 'action'=>'ListView'));
SDK::unsetSmartyTemplate(array( 'module'=>'Accounts', 'action'=>'index'));
SDK::setSmartyTemplate(array( 'module'=>'Accounts', 'action'=>'ListView'),'RothoListView.tpl');
SDK::setSmartyTemplate(array( 'module'=>'Accounts', 'action'=>'index'),'RothoListView.tpl');

SDK::unsetFile('Accounts', 'ListView');
SDK::unsetFile('Accounts', 'index');
SDK::setFile('Accounts', 'ListView', 'RothoListView');
SDK::setFile('Accounts', 'index', 'RothoListView');

?>
