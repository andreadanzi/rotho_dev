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


//danzi.tn@20151214 Bottone custom su DetailView e ListView per visualizzare in Mappa

$srcdir = 'modules/SDK/src/';
$module = Vtiger_Module::getInstance('Accounts');
// Vtiger_Link::addLink($module->id,'HEADERSCRIPT','AccountDetailOnMap',$srcdir.'modules/Accounts/AccountDetailOnMap.js');

// SDK::setMenuButton("contestual", "LBL_ACC_LISTVIEWBYPRODUCT_MAP", "return showDetailInMap(this);", 'themes/rothosofted/images/mapsicon.png', 'Accounts','DetailView');


SDK::setMenuButton("contestual", "LBL_ACC_LISTVIEWBYPRODUCT_MAP", "return showSelectedItemsInMap(this);", 'themes/rothosofted/images/mapsicon.png', 'Accounts','ListView');
SDK::setMenuButton("contestual", "LBL_ACC_LISTVIEWBYPRODUCT_MAP", "return showSelectedItemsInMap(this);", 'themes/rothosofted/images/mapsicon.png', 'Accounts','index');
?>