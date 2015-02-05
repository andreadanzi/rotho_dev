<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix;
session_start();
// danzi.tn@20141126 nuova classificazione aggiornato
// Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');

// Get module instance
$module = Vtiger_Module::getInstance('Accounts');

$block1 = Vtiger_Block::getInstance('Classificazione livello 1',$module); 


/**
Marchio
*/
$field1 = new Vtiger_Field();
$field1->name = 'account_which_faculty';
$field1->table = $module->basetable;
$field1->uitype = 15;
$field1->label= 'Faculty';
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$field1->setPicklistValues( Array ('---', 'STRUCTURAL_ENGINEERING', 'CIVIL_ENGINEERING', 'ARCHITECTURE', 'OTHER') );
$block1->addField($field1); 

// LABORATORY PRESENCE
$field1 = new Vtiger_Field();
$field1->name = 'account_has_a_lab';
$field1->table = $module->basetable;
$field1->uitype = 15;
$field1->label= 'With a Laboratory';
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$field1->setPicklistValues( Array ('---', 'YES', 'NO') );
$block1->addField($field1); 

SDK::setLanguageEntry('APP_STRINGS' ,'it_it' ,'Faculty' ,'Facolt&agrave;');
SDK::setLanguageEntry('APP_STRINGS' ,'it_it' ,'With a Laboratory' ,'Con Laboratorio');
SDK::setLanguageEntry('APP_STRINGS' ,'it_it' ,'STRUCTURAL_ENGINEERING' ,'Ingegneria Strutturale');
SDK::setLanguageEntry('APP_STRINGS' ,'it_it' ,'CIVIL_ENGINEERING' ,'Ingegneria Civile');
SDK::setLanguageEntry('APP_STRINGS' ,'it_it' ,'ARCHITECTURE' ,'Architettura');
SDK::setLanguageEntry('APP_STRINGS' ,'it_it' ,'OTHER' ,'Altro');
SDK::setLanguageEntry('APP_STRINGS' ,'it_it' ,'YES' ,'Si');
SDK::setLanguageEntry('APP_STRINGS' ,'it_it' ,'NO' ,'No');
SDK::setLanguageEntry('Accounts' ,'it_it' ,'Faculty' ,'Facolt&agrave;');
SDK::setLanguageEntry('Accounts' ,'it_it' ,'With a Laboratory' ,'Con Laboratorio');
SDK::setLanguageEntry('Accounts' ,'it_it' ,'STRUCTURAL_ENGINEERING' ,'Ingegneria Strutturale');
SDK::setLanguageEntry('Accounts' ,'it_it' ,'CIVIL_ENGINEERING' ,'Ingegneria Civile');
SDK::setLanguageEntry('Accounts' ,'it_it' ,'ARCHITECTURE' ,'Architettura');
SDK::setLanguageEntry('Accounts' ,'it_it' ,'OTHER' ,'Altro');
SDK::setLanguageEntry('Accounts' ,'it_it' ,'YES' ,'Si');
SDK::setLanguageEntry('Accounts' ,'it_it' ,'NO' ,'No');


SDK::setLanguageEntry('APP_STRINGS' ,'en_us' ,'Faculty' ,'Faculty');
SDK::setLanguageEntry('APP_STRINGS' ,'en_us' ,'With a Laboratory' ,'With a Laboratory');
SDK::setLanguageEntry('APP_STRINGS' ,'en_us' ,'STRUCTURAL_ENGINEERING' ,'Structural Engineering');
SDK::setLanguageEntry('APP_STRINGS' ,'en_us' ,'CIVIL_ENGINEERING' ,'Civil Engineering');
SDK::setLanguageEntry('APP_STRINGS' ,'en_us' ,'ARCHITECTURE' ,'Architecture');
SDK::setLanguageEntry('APP_STRINGS' ,'en_us' ,'OTHER' ,'Other');
SDK::setLanguageEntry('APP_STRINGS' ,'en_us' ,'YES' ,'Yes');
SDK::setLanguageEntry('APP_STRINGS' ,'en_us' ,'NO' ,'No');
SDK::setLanguageEntry('Accounts' ,'en_us' ,'Faculty' ,'Faculty');
SDK::setLanguageEntry('Accounts' ,'en_us' ,'With a Laboratory' ,'With a Laboratory');
SDK::setLanguageEntry('Accounts' ,'en_us' ,'STRUCTURAL_ENGINEERING' ,'Structural Engineering');
SDK::setLanguageEntry('Accounts' ,'en_us' ,'CIVIL_ENGINEERING' ,'Civil Engineering');
SDK::setLanguageEntry('Accounts' ,'en_us' ,'ARCHITECTURE' ,'Architecture');
SDK::setLanguageEntry('Accounts' ,'en_us' ,'OTHER' ,'Other');
SDK::setLanguageEntry('Accounts' ,'en_us' ,'YES' ,'Yes');
SDK::setLanguageEntry('Accounts' ,'en_us' ,'NO' ,'No');
?>
