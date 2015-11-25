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

// SERRAMENTI 
SDK::setLanguageEntry('Accounts' ,'it_it' ,'SERRAMENTI' ,'serramenti');
SDK::setLanguageEntry('Accounts' ,'de_de' ,'SERRAMENTI' ,'fenstersysteme');
SDK::setLanguageEntry('Accounts' ,'fr_fr' ,'SERRAMENTI' ,'menuiseries');
SDK::setLanguageEntry('Accounts' ,'en_us' ,'SERRAMENTI' ,'windows-door');
SDK::setLanguageEntry('Accounts' ,'es_es' ,'SERRAMENTI' ,'cerramientos');
SDK::setLanguageEntry('Accounts' ,'pt_br' ,'SERRAMENTI' ,'janelas');
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'SERRAMENTI' ,'windows-door');


SDK::setLanguageEntry('APP_STRINGS' ,'it_it' ,'SERRAMENTI' ,'serramenti');
SDK::setLanguageEntry('APP_STRINGS' ,'de_de' ,'SERRAMENTI' ,'fenstersysteme');
SDK::setLanguageEntry('APP_STRINGS' ,'fr_fr' ,'SERRAMENTI' ,'menuiseries');
SDK::setLanguageEntry('APP_STRINGS' ,'en_us' ,'SERRAMENTI' ,'windows-door');
SDK::setLanguageEntry('APP_STRINGS' ,'es_es' ,'SERRAMENTI' ,'cerramientos');
SDK::setLanguageEntry('APP_STRINGS' ,'pt_br' ,'SERRAMENTI' ,'janelas');
SDK::setLanguageEntry('APP_STRINGS' ,'ru_ru' ,'SERRAMENTI' ,'windows-door');

SDK::setLanguageEntry('Conditionals' ,'it_it' ,'SERRAMENTI' ,'serramenti');
SDK::setLanguageEntry('Conditionals' ,'de_de' ,'SERRAMENTI' ,'fenstersysteme');
SDK::setLanguageEntry('Conditionals' ,'fr_fr' ,'SERRAMENTI' ,'menuiseries');
SDK::setLanguageEntry('Conditionals' ,'en_us' ,'SERRAMENTI' ,'windows-door');
SDK::setLanguageEntry('Conditionals' ,'es_es' ,'SERRAMENTI' ,'cerramientos');
SDK::setLanguageEntry('Conditionals' ,'pt_br' ,'SERRAMENTI' ,'janelas');
SDK::setLanguageEntry('Conditionals' ,'ru_ru' ,'SERRAMENTI' ,'windows-door');

SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'PRODUTTORE SERRAMENTI' ,'Windows-door producer');
SDK::setLanguageEntry('Leads' ,'ru_ru' ,'PRODUTTORE SERRAMENTI' ,'Windows-door producer');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'PRODUTTORE SERRAMENTI' ,'Windows-door producer');
SDK::setLanguageEntry('APP_STRINGS' ,'ru_ru' ,'PRODUTTORE SERRAMENTI' ,'Windows-door producer');
SDK::setLanguageEntry('Conditionals' ,'ru_ru' ,'PRODUTTORE SERRAMENTI' ,'Windows-door producer');

SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'DISTRIBUTORE SERRAMENTI' ,'Windows-door Distributor');
SDK::setLanguageEntry('Leads' ,'ru_ru' ,'DISTRIBUTORE SERRAMENTI' ,'Windows-door Distributor');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'DISTRIBUTORE SERRAMENTI' ,'Windows-door Distributor');
SDK::setLanguageEntry('APP_STRINGS' ,'ru_ru' ,'DISTRIBUTORE SERRAMENTI' ,'Windows-door Distributor');
SDK::setLanguageEntry('Conditionals' ,'ru_ru' ,'DISTRIBUTORE SERRAMENTI' ,'Windows-door Distributor');

// FALEGNAME/SERRAMENTISTA
SDK::setLanguageEntry('Accounts' ,'it_it' ,'FALEGNAME/SERRAMENTISTA' ,'Falegname/Porte e Finestre');
SDK::setLanguageEntry('Leads' ,'it_it' ,'FALEGNAME/SERRAMENTISTA' ,'Falegname/Porte e Finestre');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'FALEGNAME/SERRAMENTISTA' ,'Falegname/Porte e Finestre');
SDK::setLanguageEntry('APP_STRINGS' ,'it_it' ,'FALEGNAME/SERRAMENTISTA' ,'Falegname/Porte e Finestre');
SDK::setLanguageEntry('Conditionals' ,'it_it' ,'FALEGNAME/SERRAMENTISTA' ,'Falegname/Porte e Finestre');

SDK::setLanguageEntry('Accounts' ,'en_us' ,'FALEGNAME/SERRAMENTISTA' ,'Joiner/Wooden Components');
SDK::setLanguageEntry('Leads' ,'en_us' ,'FALEGNAME/SERRAMENTISTA' ,'Joiner/Wooden Components');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'FALEGNAME/SERRAMENTISTA' ,'Joiner/Wooden Components');
SDK::setLanguageEntry('APP_STRINGS' ,'en_us' ,'FALEGNAME/SERRAMENTISTA' ,'Joiner/Wooden Components');
SDK::setLanguageEntry('Conditionals' ,'en_us' ,'FALEGNAME/SERRAMENTISTA' ,'Joiner/Wooden Components');

SDK::setLanguageEntry('Accounts' ,'es_es' ,'FALEGNAME/SERRAMENTISTA' ,'Ebanisteria');
SDK::setLanguageEntry('Leads' ,'es_es' ,'FALEGNAME/SERRAMENTISTA' ,'Ebanisteria');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'FALEGNAME/SERRAMENTISTA' ,'Ebanisteria');
SDK::setLanguageEntry('APP_STRINGS' ,'es_es' ,'FALEGNAME/SERRAMENTISTA' ,'Ebanisteria');
SDK::setLanguageEntry('Conditionals' ,'es_es' ,'FALEGNAME/SERRAMENTISTA' ,'Ebanisteria');




?>
