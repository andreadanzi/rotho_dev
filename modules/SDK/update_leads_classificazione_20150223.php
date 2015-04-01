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
$module = Vtiger_Module::getInstance('Leads');

$block1 = Vtiger_Block::getInstance('LBL_LEAD_INFORMATION',$module); 
// danzi.tn@20141217 nuova classificazione

/**
Attività Principale
*/
$field1 = new Vtiger_Field();
$field1->name = 'leads_main_activity';
$field1->table = $module->basetable;
$field1->uitype = 15;
$field1->label= 'Attivita Principale';
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$field1->setPicklistValues( Array ('---','AMIANTO','AMMINISTRCOND','ARCH.','CARPENTERIA','CARPMET','CONCIATETTO','PROGANTICAD','ENTEPUBB','FACCIATE E RIVESTIMENTI','FALEGNAME/SERRAMENTISTA','FERRAMENTA E ATTREZZATURE','GDO','GENERAL CONTRACTOR / IMMOBILIARE','GEOM.','GROSSISTA','IMPEDILE','IMPERM','IMPIANTISTI','INDEXTRALEGNO','ING.','LATTONERIA','MONTANTICAD','PAVIMENTISTA','ORDINI CATEGORIA/ASSOCIAZIONI','PICCOLE STRUTTURE','GRANDI STRUTTURE','CASE IN LEGNO','EDIFICI IN LEGNO','PRODUTTORI LAMELLARE','RIVEMATED','RIVENDITA LEGNAME / SEGHERIA (NON FA LAVORI) ','RIVENDITA LEGNAME / SEGHERIA CHE FA LAVORI ','RIVENDITA SPECIALIZZATA SETTORE CARPENTERIA','RIVENDITE SISTEMI ANTICADUTA','SCUOLEPROF','UNIVERSITA') );
$block1->addField($field1); 

?>
