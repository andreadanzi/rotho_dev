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


$module = Vtiger_Module::getInstance('Targets');
Vtiger_Link::addLink($module->id, 'HEADERSCRIPT', 'TargetRelatedContacts', 'modules/SDK/src/modules/Targets/TargetRelatedContacts.js');
SDK::setExtraSrc('Targets', 'modules/SDK/src/modules/Targets/TargetRelatedContacts.js');

//danzi.tn@20150630 Bottone custom per attaccare i contatti delle aziende di un target
SDK::setMenuButton("contestual", "LBL_TARGET_ADD_CONTACTS", "return addRelatedContacts(this);", 'themes/rothosofted/images/btnL3Contact.png', 'Targets','DetailView');
// SDK::setMenuButton("contestual", "LBL_TARGET_ADD_CONTACTS", "return exportCurrentListViewData(this);", 'themes/rothosofted/images/tbarExport.png', 'Accounts','ListViewByProduct');
// var ret = openAccDocCredit(this);window.open(ret,'_blank');

SDK::setLanguageEntry('APP_STRINGS','it_it', 'LBL_TARGET_ADD_CONTACTS' , 'Aggiungi i Contatti delle Aziende');
SDK::setLanguageEntry('APP_STRINGS','en_us', 'LBL_TARGET_ADD_CONTACTS' , 'Add related Contacts');
SDK::setLanguageEntry('APP_STRINGS','de_de', 'LBL_TARGET_ADD_CONTACTS' , 'Add related Contacts');

?>