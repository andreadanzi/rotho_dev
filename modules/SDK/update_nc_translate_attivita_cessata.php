<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix,$default_charset;
session_start();
// html_entity_decode($description, ENT_NOQUOTES, $default_charset);
// htmlentities( , ENT_NOQUOTES, $default_charset);
//Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');
//danzi.tn@201500821 attivita cessata => bloccata
SDK::setLanguageEntry("Accounts","it_it",'Attivita Cessata','Cliente Bloccato');
SDK::setLanguageEntry("APP_STRINGS","it_it",'Attivita Cessata','Cliente Bloccato');
SDK::setLanguageEntry("Accounts","en_us",'Attivita Cessata','Blocked Account');
SDK::setLanguageEntry("APP_STRINGS","en_us",'Attivita Cessata','Blocked Account');
SDK::setLanguageEntry("Accounts","de_de",'Attivita Cessata','Blocked');
SDK::setLanguageEntry("APP_STRINGS","de_de",'Attivita Cessata','Blocked');
SDK::setLanguageEntry("Accounts","fr_fr",'Attivita Cessata','Blocked');
SDK::setLanguageEntry("APP_STRINGS","fr_fr",'Attivita Cessata','Blocked');
SDK::setLanguageEntry("Accounts","es_es",'Attivita Cessata','Blocked');
SDK::setLanguageEntry("APP_STRINGS","es_es",'Attivita Cessata','Blocked');
SDK::setLanguageEntry("Accounts","pt_br",'Attivita Cessata','Blocked');
SDK::setLanguageEntry("APP_STRINGS","pt_br",'Attivita Cessata','Blocked');

?>
