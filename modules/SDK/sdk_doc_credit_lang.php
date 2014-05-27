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



?>