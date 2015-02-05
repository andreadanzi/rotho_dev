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


//danzi.tn@20150202 Bottone custom per esportare i dati dalla vista ListViewByProduct e visualizza in Mappa
SDK::setMenuButton("contestual", "LBL_ACC_LISTVIEWBYPRODUCT_EXP", "return exportCurrentListViewData(this);", 'themes/rothosofted/images/tbarExport.png', 'Accounts','ListViewByProduct');
SDK::setMenuButton("contestual", "LBL_ACC_LISTVIEWBYPRODUCT_MAP", "return showInMapListViewData(this);", 'themes/rothosofted/images/mapsicon.png', 'Accounts','ListViewByProduct');
// var ret = openAccDocCredit(this);window.open(ret,'_blank');

SDK::setLanguageEntry('APP_STRINGS','it_it', 'LBL_ACC_LISTVIEWBYPRODUCT_EXP' , 'Esporta i dati correnti');
SDK::setLanguageEntry('APP_STRINGS','en_us', 'LBL_ACC_LISTVIEWBYPRODUCT_EXP' , 'Export current data');
SDK::setLanguageEntry('APP_STRINGS','de_de', 'LBL_ACC_LISTVIEWBYPRODUCT_EXP' , 'Export current data');


SDK::setLanguageEntry('APP_STRINGS','it_it', 'LBL_ACC_LISTVIEWBYPRODUCT_MAP' , 'Visualizza in Mappa');
SDK::setLanguageEntry('APP_STRINGS','en_us', 'LBL_ACC_LISTVIEWBYPRODUCT_MAP' , 'View in Map');
SDK::setLanguageEntry('APP_STRINGS','de_de', 'LBL_ACC_LISTVIEWBYPRODUCT_MAP' , 'View in Map');

?>