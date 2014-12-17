<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix;
session_start();
// danzi.tn@20141217 nuova classificazione da report visite
// Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');

// Get module instance
$module = Vtiger_Module::getInstance('Visitreport');

$block1 = Vtiger_Block::getInstance('LBL_VISITREPORT_INFORMATION',$module); 

/**
Linea
*/
$field1 = new Vtiger_Field();
$field1->name = 'vr_account_line';
$field1->table = $module->basetable;
$field1->uitype = 1;
$field1->readonly = 99;
$field1->label= 'Linea';
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$block1->addField($field1); 

/**
Tipo Cliente
*/
$field1 = new Vtiger_Field();
$field1->name = 'vr_account_client_type';
$field1->table = $module->basetable;
$field1->uitype = 15;
$field1->label= 'Tipo Cliente';
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$field1->setPicklistValues( Array ('---', 'UTILIZZATORE', 'RIVENDITORE', 'PROGETTISTA', 'INFLUENZATORE') );
$block1->addField($field1); 


/**
Attività Principale
*/
$field1 = new Vtiger_Field();
$field1->name = 'vr_account_main_activity';
$field1->table = $module->basetable;
$field1->uitype = 15;
$field1->label= 'Attivita Principale';
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$field1->setPicklistValues( Array ('---','AMIANTO','AMMINISTRCOND','ARCH.','CARPENTERIA','CARPMET','CONCIATETTO','PROGANTICAD','ENTEPUBB','FACCIATE E RIVESTIMENTI','FALEGNAME/SERRAMENTISTA','FERRAMENTA E ATTREZZATURE','GDO','GENERAL CONTRACTOR / IMMOBILIARE','GEOM.','GROSSISTA','IMPEDILE','IMPERM','IMPIANTISTI','INDEXTRALEGNO','ING.','LATTONERIA','MONTANTICAD','ORDINI CATEGORIA/ASSOCIAZIONI','PICCOLE STRUTTURE','RIVEMATED','RIVENDITA LEGNAME / SEGHERIA (NON FA LAVORI) ','RIVENDITA LEGNAME / SEGHERIA CHE FA LAVORI ','RIVENDITA SPECIALIZZATA SETTORE CARPENTERIA','RIVENDITE SISTEMI ANTICADUTA','SCUOLEPROF','UNIVERSITA') );
$block1->addField($field1); 



/**
Attività Secondaria
*/
$field1 = new Vtiger_Field();
$field1->name = 'vr_account_sec_activity';
$field1->table = $module->basetable;
$field1->uitype = 15;
$field1->label= 'Attivita Secondaria';
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$field1->setPicklistValues( Array ('---','AMIANTO','AMMINISTRCOND','ARCH.','CARPENTERIA','CARPMET','CONCIATETTO','PROGANTICAD','ENTEPUBB','FACCIATE E RIVESTIMENTI','FALEGNAME/SERRAMENTISTA','FERRAMENTA E ATTREZZATURE','GDO','GENERAL CONTRACTOR / IMMOBILIARE','GEOM.','GROSSISTA','IMPEDILE','IMPERM','IMPIANTISTI','INDEXTRALEGNO','ING.','LATTONERIA','MONTANTICAD','ORDINI CATEGORIA/ASSOCIAZIONI','PICCOLE STRUTTURE','RIVEMATED','RIVENDITA LEGNAME / SEGHERIA (NON FA LAVORI) ','RIVENDITA LEGNAME / SEGHERIA CHE FA LAVORI ','RIVENDITA SPECIALIZZATA SETTORE CARPENTERIA','RIVENDITE SISTEMI ANTICADUTA','SCUOLEPROF','UNIVERSITA') );
$block1->addField($field1); 


/**
Marchio
*/
$field1 = new Vtiger_Field();
$field1->name = 'vr_account_brand';
$field1->table = $module->basetable;
$field1->uitype = 15;
$field1->label= 'Marchio';
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$field1->setPicklistValues( Array ('---', 'ROTHOBLAAS', 'HOLZTECHNIC', 'INTEGO', 'MAFELL', 'DUSS', 'TENAS') );
$block1->addField($field1); 


/**
Area di Intervento
*/
$field1 = new Vtiger_Field();
$field1->name = 'vr_area_intervento';
$field1->table = $module->basetable;
$field1->uitype = 15;
$field1->label= 'Area di intervento';
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$field1->setPicklistValues( Array ('---', 'Locale', 'Nazionale', 'Internazionale') );
$block1->addField($field1); 

/**
Potenziale Annuo  */
$field1 = new Vtiger_Field();
$field1->name = 'vr_account_yearly_pot';
$field1->table = $module->basetable;
$field1->uitype = 15;
$field1->label= 'Potenziale &euro;/Anno';
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$field1->setPicklistValues( Array ('---', '<5000', '5000-20000', '20000-50000', '>50000') );
$block1->addField($field1); 

// Gestione popup con setPopupReturnFunction per il popup Aziende
Vtiger_Link::addLink($module->id, 'HEADERSCRIPT', 'AccountToVisitreport', 'modules/Visitreport/AccountToVisitreport.js');
SDK::setExtraSrc('Visitreport', 'modules/Visitreport/AccountToVisitreport.js');
SDK::setPopupReturnFunction('Visitreport', 'accountid', 'modules/Visitreport/AccountToVisitreport.php');

Vtiger_Event::register($module ,'vtiger.entity.aftersave','VisitreportHandler','modules/Visitreport/VisitreportHandler.php');
Vtiger_Event::register($module ,'vtiger.entity.beforesave','VisitreportHandler','modules/Visitreport/VisitreportHandler.php');


$module = Vtiger_Module::getInstance('Accounts');

$block1 = Vtiger_Block::getInstance('Classificazione livello 1',$module); 

$field1 = new Vtiger_Field();
$field1->name = 'area_intervento';
$field1->table = $module->basetable;
$field1->uitype = 15;
$field1->label= 'Area di intervento';
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$field1->setPicklistValues( Array ('---', 'Locale', 'Nazionale', 'Internazionale') );
$block1->addField($field1); 

/**
Potenziale Annuo  */
$field1 = new Vtiger_Field();
$field1->name = 'account_yearly_pot';
$field1->table = $module->basetable;
$field1->uitype = 15;
$field1->label= 'Potenziale &euro;/Anno';
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$field1->setPicklistValues( Array ('---', '<5000', '5000-20000', '20000-50000', '>50000') );
$block1->addField($field1); 


?>
