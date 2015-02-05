<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix;
session_start();

//Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');
//danzi.tn@20150119 nuova classificazione, attivtà principale e secondaria
SDK::setLanguageEntry('Accounts' ,'it_it' ,'PAVIMENTISTA' ,'Pavimentista');
SDK::setLanguageEntry('Accounts' ,'de_de' ,'PAVIMENTISTA' ,'Bodenleger');
SDK::setLanguageEntry('Accounts' ,'fr_fr' ,'PAVIMENTISTA' ,'Paviour');
SDK::setLanguageEntry('Accounts' ,'en_us' ,'PAVIMENTISTA' ,'Floorer');
SDK::setLanguageEntry('Accounts' ,'es_es' ,'PAVIMENTISTA' ,'Pavimentador');
SDK::setLanguageEntry('Accounts' ,'pt_pt' ,'PAVIMENTISTA' ,'Pavimentador');
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'PAVIMENTISTA' ,'Этаж слой');

SDK::setLanguageEntry('Visitreport' ,'it_it' ,'PAVIMENTISTA' ,'Pavimentista');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'PAVIMENTISTA' ,'Bodenleger');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'PAVIMENTISTA' ,'Paviour');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'PAVIMENTISTA' ,'Floorer');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'PAVIMENTISTA' ,'Pavimentador');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'PAVIMENTISTA' ,'Pavimentador');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'PAVIMENTISTA' ,'Этаж слой');

SDK::setLanguageEntry('APP_STRINGS' ,'it_it' ,'PAVIMENTISTA' ,'Pavimentista');
SDK::setLanguageEntry('APP_STRINGS' ,'de_de' ,'PAVIMENTISTA' ,'Bodenleger');
SDK::setLanguageEntry('APP_STRINGS' ,'fr_fr' ,'PAVIMENTISTA' ,'Paviour');
SDK::setLanguageEntry('APP_STRINGS' ,'en_us' ,'PAVIMENTISTA' ,'Floorer');
SDK::setLanguageEntry('APP_STRINGS' ,'es_es' ,'PAVIMENTISTA' ,'Pavimentador');
SDK::setLanguageEntry('APP_STRINGS' ,'pt_pt' ,'PAVIMENTISTA' ,'Pavimentador');
SDK::setLanguageEntry('APP_STRINGS' ,'ru_ru' ,'PAVIMENTISTA' ,'Этаж слой');


?>