<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix;
session_start();
// danzi.tn@20150220 modifiche classificazione-2
// danzi.tn@20150421 aggiunto traduzione per il criterio n 7 not includes
// Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');

// Connessione Internet 
SDK::setLanguageEntry('HelpDesk' ,'it_it' ,'Connessione Internet' ,'Connessione Internet');
SDK::setLanguageEntry('HelpDesk' ,'de_de' ,'Connessione Internet' ,'Internet Verbindung');
SDK::setLanguageEntry('HelpDesk' ,'fr_fr' ,'Connessione Internet' ,'Connexion Internet');
SDK::setLanguageEntry('HelpDesk' ,'en_us' ,'Connessione Internet' ,'Internet Connection');
SDK::setLanguageEntry('HelpDesk' ,'es_es' ,'Connessione Internet' ,'Conexion Internet');
SDK::setLanguageEntry('HelpDesk' ,'pt_br' ,'Connessione Internet' ,'Internet Connection');
SDK::setLanguageEntry('HelpDesk' ,'ru_ru' ,'Connessione Internet' ,'Internet Connection');


SDK::setLanguageEntry('APP_STRINGS' ,'it_it' ,'Connessione Internet' ,'Connessione Internet');
SDK::setLanguageEntry('APP_STRINGS' ,'de_de' ,'Connessione Internet' ,'Internet Verbindung');
SDK::setLanguageEntry('APP_STRINGS' ,'fr_fr' ,'Connessione Internet' ,'Connexion Internet');
SDK::setLanguageEntry('APP_STRINGS' ,'en_us' ,'Connessione Internet' ,'Internet Connection');
SDK::setLanguageEntry('APP_STRINGS' ,'es_es' ,'Connessione Internet' ,'Conexion Internet');
SDK::setLanguageEntry('APP_STRINGS' ,'pt_br' ,'Connessione Internet' ,'Internet Connection');
SDK::setLanguageEntry('APP_STRINGS' ,'ru_ru' ,'Connessione Internet' ,'Internet Connection');





?>
