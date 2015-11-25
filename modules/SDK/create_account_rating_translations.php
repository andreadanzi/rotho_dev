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


SDK::setLanguageEntry('Accounts','it_it', 'Attivita Cessata' , 'Bloccata');
SDK::setLanguageEntry('Accounts','de_de', 'Attivita Cessata' , 'Locked');
SDK::setLanguageEntry('Accounts','en_us', 'Attivita Cessata' , 'Locked');
SDK::setLanguageEntry('Accounts','es_es', 'Attivita Cessata' , 'Bloqueada');
SDK::setLanguageEntry('Accounts','pt_br', 'Attivita Cessata' , 'Locked');
SDK::setLanguageEntry('Accounts','fr_fr', 'Attivita Cessata' , 'Fermé');
SDK::setLanguageEntry('Accounts','ru_ru', 'Attivita Cessata' , 'Locked');
?>