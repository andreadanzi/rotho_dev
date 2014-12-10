<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix;
session_start();
// danzi.tn@20141126 nuova classificazione
// Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');

// Get module instance
$module = Vtiger_Module::getInstance('Accounts');

$block1 = Vtiger_Block::getInstance('Classificazione livello 1',$module); 

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
$field1->setPicklistValues( Array ('---','AMIANTO','AMMINISTRCOND','ARCH.','CARPENTERIA','CARPMET','CONCIATETTO','PROGANTICAD','ENTEPUBB','FACCIATE E RIVESTIMENTI','FALEGNAME/SERRAMENTISTA','FERRAMENTA E ATTREZZATURE','GDO','GENERAL CONTRACTOR / IMMOBILIARE','GEOM.','GROSSISTA','IMPEDILE','IMPERM','IMPIANTISTI','IMPRESA EDILE','INDEXTRALEGNO','ING.','LATTONERIA','LATTONIERE','MONTANTICAD','ORDINI CATEGORIA/ASSOCIAZIONI','PICCOLE STRUTTURE','RIVEMATED','RIVENDITA LEGNAME / SEGHERIA (NON FA LAVORI) ','RIVENDITA LEGNAME / SEGHERIA CHE FA LAVORI ','RIVENDITA SPECIALIZZATA SETTORE CARPENTERIA','RIVENDITE SISTEMI ANTICADUTA','SCUOLEPROF','UNIVERSITA') );
$block1->addField($field1); 



?>
