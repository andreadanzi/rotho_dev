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
SDK::setMenuButton("fixed", "LBL_DCLINK_BTN", "var ret=openDocCredit(this);window.open(ret,'_blank');", 'themes/rothosofted/images/tbarDCLINK.png');
//danzi.tn@20140324 funzione javascript per accedere a doc credit
Vtiger_Link::addLink(31, 'HEADERSCRIPT', 'OpenDocCredit', 'modules/SDK/src/js/OpenDocCredit.js');
// SDK::setExtraSrc('SDK', 'modules/SDK/src/js/OpenDocCredit.js');

SDK::setLanguageEntry('APP_STRINGS','it_it', 'LBL_DCLINK_BTN' , 'Vai a Doc Credit');
SDK::setLanguageEntry('APP_STRINGS','en_us', 'LBL_DCLINK_BTN' , 'Open Doc Credit');
SDK::setLanguageEntry('APP_STRINGS','de_de', 'LBL_DCLINK_BTN' , 'Open Doc Credit');

SDK::setLanguageEntry('ALERT_ARR','it_it', 'ALERT_DCLINK_ERR' , 'Errore di connessione a Doc Credit');
SDK::setLanguageEntry('ALERT_ARR','en_us', 'ALERT_DCLINK_ERR' , 'Doc Credit Connection Error');
SDK::setLanguageEntry('ALERT_ARR','de_de', 'ALERT_DCLINK_ERR' , 'Doc Credit Connection Error');

SDK::setLanguageEntry('ALERT_ARR','it_it', 'ALERT_DCLINK_INFO' , 'Tentativo di connessione a Doc Credit, clicca su OK e attendi');
SDK::setLanguageEntry('ALERT_ARR','en_us', 'ALERT_DCLINK_INFO' , 'Connetion to Doc Credit, click OK and wait');
SDK::setLanguageEntry('ALERT_ARR','de_de', 'ALERT_DCLINK_INFO' , 'Connetion to Doc Credit, click OK and wait');

SDK::setLanguageEntry('ALERT_ARR','it_it', 'ALERT_DCLINK_NOUSER' , "Non sono state impostate le tue credenziali per l'accesso a DocCredit, vai su Preferenze");
SDK::setLanguageEntry('ALERT_ARR','en_us', 'ALERT_DCLINK_NOUSER' , "You have to configure username and password for DocCredit, go to My Preferencies");
SDK::setLanguageEntry('ALERT_ARR','de_de', 'ALERT_DCLINK_NOUSER' , "You have to configure username and password for DocCredit, go to My Preferencies");

$module = Vtiger_Module::getInstance('Users');

$block1 = Vtiger_Block::getInstance('LBL_MORE_INFORMATION',$module); 

$field21 = new Vtiger_Field();
$field21->name = 'doccredituser';
$field21->table = $module->basetable;
$field21->label= 'DocCredit User';
$field21->columntype = 'VARCHAR(100)';
$field21->uitype = 1;
$field21->typeofdata = 'V~O';
$block1->addField($field21);

$field22 = new Vtiger_Field();
$field22->name = 'doccreditpwd';
$field22->table = $module->basetable;
$field22->label= 'DocCredit Password';
$field22->columntype = 'VARCHAR(100)';
$field22->uitype = 1;
$field22->typeofdata = 'V~O';
$block1->addField($field22);

SDK::setLanguageEntry('Users','it_it', 'DocCredit User' , 'Utente DocCredit');
SDK::setLanguageEntry('Users','en_us', 'DocCredit User' , 'DocCredit User');
SDK::setLanguageEntry('Users','de_de', 'DocCredit User' , 'DocCredit User');

SDK::setLanguageEntry('Users','it_it', 'DocCredit Password' , 'Password DocCredit');
SDK::setLanguageEntry('Users','en_us', 'DocCredit Password' , 'DocCredit Password');
SDK::setLanguageEntry('Users','de_de', 'DocCredit Password' , 'DocCredit Password');


?>