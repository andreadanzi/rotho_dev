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
Linea
*/
$field1 = new Vtiger_Field();
$field1->name = 'account_line';
$field1->table = $module->basetable;
$field1->uitype = 15;
$field1->label= 'Linea';
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$field1->setPicklistValues( Array ('---', 'RC / CARP', 'RD / DIST', 'RS / SAFE', 'RR / DIREZ', 'GD / GDO') );
$block1->addField($field1); 

/**
Tipo Cliente
*/
$field1 = new Vtiger_Field();
$field1->name = 'account_client_type';
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
$field1->name = 'account_main_activity';
$field1->table = $module->basetable;
$field1->uitype = 15;
$field1->label= 'Attivita Principale';
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$field1->setPicklistValues( Array ('---','AMIANTO','AMMINISTRCOND','ARCH.','CARPENTERIA','CARPMET','CONCIATETTO','PROGANTICAD','ENTEPUBB','FACCIATE E RIVESTIMENTI','FALEGNAME/SERRAMENTISTA','FERRAMENTA E ATTREZZATURE','GDO','GENERAL CONTRACTOR / IMMOBILIARE','GEOM.','GROSSISTA','IMPEDILE','IMPERM','IMPIANTISTI','INDEXTRALEGNO','ING.','LATTONERIA','LATTONIERE','MONTANTICAD','ORDINI CATEGORIA/ASSOCIAZIONI','PICCOLE STRUTTURE','RIVEMATED','RIVENDITA LEGNAME / SEGHERIA (NON FA LAVORI) ','RIVENDITA LEGNAME / SEGHERIA CHE FA LAVORI ','RIVENDITA SPECIALIZZATA SETTORE CARPENTERIA','RIVENDITE SISTEMI ANTICADUTA','SCUOLEPROF','UNIVERSITA') );
$block1->addField($field1); 



/**
Attività Secondaria
*/
$field1 = new Vtiger_Field();
$field1->name = 'account_sec_activity';
$field1->table = $module->basetable;
$field1->uitype = 15;
$field1->label= 'Attivita Secondaria';
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$field1->setPicklistValues( Array ('---','AMIANTO','AMMINISTRCOND','ARCH.','CARPENTERIA','CARPMET','CONCIATETTO','PROGANTICAD','ENTEPUBB','FACCIATE E RIVESTIMENTI','FALEGNAME/SERRAMENTISTA','FERRAMENTA E ATTREZZATURE','GDO','GENERAL CONTRACTOR / IMMOBILIARE','GEOM.','GROSSISTA','IMPEDILE','IMPERM','IMPIANTISTI','INDEXTRALEGNO','ING.','LATTONERIA','LATTONIERE','MONTANTICAD','ORDINI CATEGORIA/ASSOCIAZIONI','PICCOLE STRUTTURE','RIVEMATED','RIVENDITA LEGNAME / SEGHERIA (NON FA LAVORI) ','RIVENDITA LEGNAME / SEGHERIA CHE FA LAVORI ','RIVENDITA SPECIALIZZATA SETTORE CARPENTERIA','RIVENDITE SISTEMI ANTICADUTA','SCUOLEPROF','UNIVERSITA') );
$block1->addField($field1); 


/**
Marchio
*/
$field1 = new Vtiger_Field();
$field1->name = 'account_brand';
$field1->table = $module->basetable;
$field1->uitype = 15;
$field1->label= 'Marchio';
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$field1->setPicklistValues( Array ('---', 'ROTHOBLAAS', 'HOLZTECHNIC', 'INTEGO', 'MAFELL', 'DUSS', 'TENAS') );
$block1->addField($field1); 



?>
