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

// danzi.tn@20140307 eliminato il criterio sullo stato di fatturazione e modificata la label

SDK::setLanguageEntry('Accounts','it_it','Points' , 'Punteggio PROG CARP');
SDK::setLanguageEntry('Accounts','en_us', 'Points' , 'Score PROG CARP');
SDK::setLanguageEntry('Accounts','de_de', 'Points' , 'Score PROG CARP');
SDK::setLanguageEntry('Accounts','es_es', 'Points' , 'Score PROG CARP');
SDK::setLanguageEntry('Accounts','pt_br', 'Points' , 'Score PROG CARP');
SDK::setLanguageEntry('Accounts','fr_fr', 'Points' , 'Score PROG CARP');
SDK::setLanguageEntry('Accounts','ru_ru', 'Points' , 'Score PROG CARP');


?>
