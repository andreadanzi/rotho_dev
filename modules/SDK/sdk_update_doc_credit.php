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


//danzi.tn@20140324 Bottone custom per accedere a doc credit
SDK::setMenuButton("contestual", "LBL_ACC_DCLINK_BTN", "openAccDocCredit(this);", 'themes/rothosofted/images/tbarAccDCLINK.png', 'Accounts','DetailView');
//danzi.tn@20140324 funzione javascript per accedere a doc credit
Vtiger_Link::addLink(31, 'HEADERSCRIPT', 'OpenDocCredit', 'modules/SDK/src/js/OpenDocCredit.js');
// SDK::setExtraSrc('SDK', 'modules/SDK/src/js/OpenDocCredit.js');

SDK::setLanguageEntry('APP_STRINGS','it_it', 'LBL_ACC_DCLINK_BTN' , 'Dettaglio Doc Credit Azienda');
SDK::setLanguageEntry('APP_STRINGS','en_us', 'LBL_ACC_DCLINK_BTN' , 'Open Account in Doc Credit');
SDK::setLanguageEntry('APP_STRINGS','de_de', 'LBL_ACC_DCLINK_BTN' , 'Open Account in Doc Credit');

?>