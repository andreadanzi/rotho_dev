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

// Get module instance
$module = Vtiger_Module::getInstance('Accounts');

$block1 = Vtiger_Block::getInstance('Classificazione livello 2',$module); 

/**
Potenziale con nostri prodotti
*/
$field1 = new Vtiger_Field();
$field1->name = 'pot_nostri_prodotti';
$field1->table = $module->basetable;
$field1->uitype = 71;
$field1->label= 'Potenziale con nostri prodotti';
$field1->columntype = 'VARCHAR(100)';
$field1->typeofdata = 'NN~O~10,2';// Varchar~Optional
$block1->addField($field1); 

/**
Prodotti di interesse
*/
$field1 = new Vtiger_Field();
$field1->name = 'product_cat';
$field1->table = $module->basetable;
$field1->uitype = 2001;
$field1->label= 'Codice Prodotti di interesse';
$field1->columntype = 'VARCHAR(100)';
$field1->quickcreate = 0;
$field1->typeofdata = 'V~O';// Varchar~Optional
$block1->addField($field1); 


/** Descrizione Prodotti di interesse */
$field21 = new Vtiger_Field();
$field21->name = 'product_cat_descr';
$field21->table = $module->basetable;
$field21->label= 'Descrizione Prodotti di interesse';
$field21->columntype = 'VARCHAR(255)';
$field21->uitype = 2002;
$field21->typeofdata = 'V~O';
$field21->quickcreate = 0;
$block1->addField($field21);


/**
Competitor presenti
*/
$field1 = new Vtiger_Field();
$field1->name = 'actual_competitors';
$field1->table = $module->basetable;
$field1->uitype = 15;
$field1->label= 'Competitor presenti';
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$field1->setPicklistValues( Array ('---','WURTH','UNIFIX','MUNGO','RIWEGA','SIGA','ISO CHEMIE','ALTRO') );
$block1->addField($field1); 



/**
Materiale finestre
*/
$field1 = new Vtiger_Field();
$field1->name = 'materiale_finestre';
$field1->table = $module->basetable;
$field1->uitype = 15;
$field1->label= 'Materiale finestre';
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$field1->setPicklistValues( Array ('---','LEGNO','ALU','PVC','ALTRO') );
$block1->addField($field1); 


/**
Finestre pz/anno
*/
$field1 = new Vtiger_Field();
$field1->name = 'finestre_anno';
$field1->table = $module->basetable;
$field1->uitype = 1;
$field1->label= 'Finestre pz/anno';
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$block1->addField($field1); 

SDK::setLanguageEntry('Accounts' ,'it_it' ,'PRODUTTORE SERRAMENTI' ,'Produttore Serramenti');
SDK::setLanguageEntry('Accounts' ,'de_de' ,'PRODUTTORE SERRAMENTI' ,'Windows Producer');
SDK::setLanguageEntry('Accounts' ,'fr_fr' ,'PRODUTTORE SERRAMENTI' ,'Windows Producer');
SDK::setLanguageEntry('Accounts' ,'en_us' ,'PRODUTTORE SERRAMENTI' ,'Windows Producer');
SDK::setLanguageEntry('Accounts' ,'es_es' ,'PRODUTTORE SERRAMENTI' ,'Windows Producer');
SDK::setLanguageEntry('Accounts' ,'pt_pt' ,'PRODUTTORE SERRAMENTI' ,'Windows Producer');
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'PRODUTTORE SERRAMENTI' ,'Windows Producer');

SDK::setLanguageEntry('Visitreport' ,'it_it' ,'PRODUTTORE SERRAMENTI' ,'Produttore Serramenti');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'PRODUTTORE SERRAMENTI' ,'Windows Producer');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'PRODUTTORE SERRAMENTI' ,'Windows Producer');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'PRODUTTORE SERRAMENTI' ,'Windows Producer');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'PRODUTTORE SERRAMENTI' ,'Windows Producer');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'PRODUTTORE SERRAMENTI' ,'Windows Producer');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'PRODUTTORE SERRAMENTI' ,'Windows Producer');


SDK::setLanguageEntry('Leads' ,'it_it' ,'PRODUTTORE SERRAMENTI' ,'Produttore Serramenti');
SDK::setLanguageEntry('Leads' ,'de_de' ,'PRODUTTORE SERRAMENTI' ,'Windows Producer');
SDK::setLanguageEntry('Leads' ,'fr_fr' ,'PRODUTTORE SERRAMENTI' ,'Windows Producer');
SDK::setLanguageEntry('Leads' ,'en_us' ,'PRODUTTORE SERRAMENTI' ,'Windows Producer');
SDK::setLanguageEntry('Leads' ,'es_es' ,'PRODUTTORE SERRAMENTI' ,'Windows Producer');
SDK::setLanguageEntry('Leads' ,'pt_pt' ,'PRODUTTORE SERRAMENTI' ,'Windows Producer');
SDK::setLanguageEntry('Leads' ,'ru_ru' ,'PRODUTTORE SERRAMENTI' ,'Windows Producer');

SDK::setLanguageEntry('APP_STRINGS' ,'it_it' ,'PRODUTTORE SERRAMENTI' ,'Produttore Serramenti');
SDK::setLanguageEntry('APP_STRINGS' ,'de_de' ,'PRODUTTORE SERRAMENTI' ,'Windows Producer');
SDK::setLanguageEntry('APP_STRINGS' ,'fr_fr' ,'PRODUTTORE SERRAMENTI' ,'Windows Producer');
SDK::setLanguageEntry('APP_STRINGS' ,'en_us' ,'PRODUTTORE SERRAMENTI' ,'Windows Producer');
SDK::setLanguageEntry('APP_STRINGS' ,'es_es' ,'PRODUTTORE SERRAMENTI' ,'Windows Producer');
SDK::setLanguageEntry('APP_STRINGS' ,'pt_pt' ,'PRODUTTORE SERRAMENTI' ,'Windows Producer');
SDK::setLanguageEntry('APP_STRINGS' ,'ru_ru' ,'PRODUTTORE SERRAMENTI' ,'Windows Producer');

SDK::setLanguageEntry('Accounts' ,'it_it' ,'DISTRIBUTORE SERRAMENTI' ,'Distributore Serramenti');
SDK::setLanguageEntry('Accounts' ,'de_de' ,'DISTRIBUTORE SERRAMENTI' ,'Windows Retailer');
SDK::setLanguageEntry('Accounts' ,'fr_fr' ,'DISTRIBUTORE SERRAMENTI' ,'Windows Retailer');
SDK::setLanguageEntry('Accounts' ,'en_us' ,'DISTRIBUTORE SERRAMENTI' ,'Windows Retailer');
SDK::setLanguageEntry('Accounts' ,'es_es' ,'DISTRIBUTORE SERRAMENTI' ,'Windows Retailer');
SDK::setLanguageEntry('Accounts' ,'pt_pt' ,'DISTRIBUTORE SERRAMENTI' ,'Windows Retailer');
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'DISTRIBUTORE SERRAMENTI' ,'Windows Retailer');

SDK::setLanguageEntry('Visitreport' ,'it_it' ,'DISTRIBUTORE SERRAMENTI' ,'Distributore Serramenti');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'DISTRIBUTORE SERRAMENTI' ,'Windows Retailer');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'DISTRIBUTORE SERRAMENTI' ,'Windows Retailer');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'DISTRIBUTORE SERRAMENTI' ,'Windows Retailer');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'DISTRIBUTORE SERRAMENTI' ,'Windows Retailer');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'DISTRIBUTORE SERRAMENTI' ,'Windows Retailer');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'DISTRIBUTORE SERRAMENTI' ,'Windows Retailer');


SDK::setLanguageEntry('Leads' ,'it_it' ,'DISTRIBUTORE SERRAMENTI' ,'Distributore Serramenti');
SDK::setLanguageEntry('Leads' ,'de_de' ,'DISTRIBUTORE SERRAMENTI' ,'Windows Retailer');
SDK::setLanguageEntry('Leads' ,'fr_fr' ,'DISTRIBUTORE SERRAMENTI' ,'Windows Retailer');
SDK::setLanguageEntry('Leads' ,'en_us' ,'DISTRIBUTORE SERRAMENTI' ,'Windows Retailer');
SDK::setLanguageEntry('Leads' ,'es_es' ,'DISTRIBUTORE SERRAMENTI' ,'Windows Retailer');
SDK::setLanguageEntry('Leads' ,'pt_pt' ,'DISTRIBUTORE SERRAMENTI' ,'Windows Retailer');
SDK::setLanguageEntry('Leads' ,'ru_ru' ,'DISTRIBUTORE SERRAMENTI' ,'Windows Retailer');

SDK::setLanguageEntry('APP_STRINGS' ,'it_it' ,'DISTRIBUTORE SERRAMENTI' ,'Distributore Serramenti');
SDK::setLanguageEntry('APP_STRINGS' ,'de_de' ,'DISTRIBUTORE SERRAMENTI' ,'Windows Retailer');
SDK::setLanguageEntry('APP_STRINGS' ,'fr_fr' ,'DISTRIBUTORE SERRAMENTI' ,'Windows Retailer');
SDK::setLanguageEntry('APP_STRINGS' ,'en_us' ,'DISTRIBUTORE SERRAMENTI' ,'Windows Retailer');
SDK::setLanguageEntry('APP_STRINGS' ,'es_es' ,'DISTRIBUTORE SERRAMENTI' ,'Windows Retailer');
SDK::setLanguageEntry('APP_STRINGS' ,'pt_pt' ,'DISTRIBUTORE SERRAMENTI' ,'Windows Retailer');
SDK::setLanguageEntry('APP_STRINGS' ,'ru_ru' ,'DISTRIBUTORE SERRAMENTI' ,'Windows Retailer');


SDK::setLanguageEntry('Conditionals' ,'it_it' ,'LBL_CRITERIA_VALUE_NOT_INCLUDES' ,'Non include');
SDK::setLanguageEntry('Conditionals' ,'de_de' ,'LBL_CRITERIA_VALUE_NOT_INCLUDES' ,'Nicht umfasst');
SDK::setLanguageEntry('Conditionals' ,'fr_fr' ,'LBL_CRITERIA_VALUE_NOT_INCLUDES' ,'Non inclus');
SDK::setLanguageEntry('Conditionals' ,'en_us' ,'LBL_CRITERIA_VALUE_NOT_INCLUDES' ,'Not includes');
SDK::setLanguageEntry('Conditionals' ,'es_es' ,'LBL_CRITERIA_VALUE_NOT_INCLUDES' ,'not includes');
SDK::setLanguageEntry('Conditionals' ,'pt_pt' ,'LBL_CRITERIA_VALUE_NOT_INCLUDES' ,'not includes');
SDK::setLanguageEntry('Conditionals' ,'ru_ru' ,'LBL_CRITERIA_VALUE_NOT_INCLUDES' ,'not includes');



?>
