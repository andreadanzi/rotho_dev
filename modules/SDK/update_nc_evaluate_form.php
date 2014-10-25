<?php
// danzi.tn@20141011 prime modifiche valutazione non conformità
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

// Create module instance and save it first
$module = Vtiger_Module::getInstance('Nonconformities');

// Add the evaluation block
$block_eval = new Vtiger_Block();
$block_eval->label = 'LBL_NONCONFORMITY_EVALUATION';
$module->addBlock($block_eval);
// $block_eval = Vtiger_Block::getInstance('LBL_NONCONFORMITY_EVALUATION',$module);


$field = new Vtiger_Field();
$field->name = 'rilavorazione';
$field->table = $module->basetable;
$field->label= 'Rilavorazione';
$field->columntype = 'NUMERIC(10,2)';
$field->uitype = 71;
$field->helpinfo = "Etichette
Bit
Scatole
Test esterni
Costi rilavorazione (intrerni ed esterno IP serv.)
cernita..";
$field->typeofdata = 'NN~O~10,2';
$field->typeofdata = 'NN~O~10,2';
$field->quickcreate = 1;
$block_eval->addField($field);

$field = new Vtiger_Field();
$field->name = 'logistica';
$field->table = $module->basetable;
$field->label= 'Logistica';
$field->columntype = 'NUMERIC(10,2)';
$field->uitype = 71;
$field->helpinfo = "Spedizioni entrata
Spedizion uscita
Spostamenti per rilavorazioni
Richiamo materiale difettato...";
$field->typeofdata = 'NN~O~10,2';
$field->quickcreate = 1;
$block_eval->addField($field);

$field = new Vtiger_Field();
$field->name = 'magazzino';
$field->table = $module->basetable;
$field->label= 'Magazzino';
$field->columntype = 'NUMERIC(10,2)';
$field->uitype = 71;
$field->helpinfo = "da decidere";
$field->typeofdata = 'NN~O~10,2';
$field->quickcreate = 1;
$block_eval->addField($field);


$field = new Vtiger_Field();
$field->name = 'acquisto';
$field->table = $module->basetable;
$field->label= 'Acquisto';
$field->columntype = 'NUMERIC(10,2)';
$field->uitype = 71;
$field->helpinfo = "Smaltimento
Costi aggiuntivi riordino...";
$field->typeofdata = 'NN~O~10,2';
$field->quickcreate = 1;
$block_eval->addField($field);

/* danzi.tn@20141023 Gestione picklist con valori */
$field = new Vtiger_Field();
$field->name = 'gestione';
$field->table = $module->basetable;
$field->label= 'Gestione';
$field->columntype = 'NUMERIC(10,2)';
$field->uitype = 71;
$field->helpinfo = "Smaltimento
Costi aggiuntivi riordino...";
$field->typeofdata = 'NN~O~10,2';
$field->quickcreate = 1;
$block_eval->addField($field);

$field = new Vtiger_Field();
$field->name = 'danno_comm';
$field->table = $module->basetable;
$field->label= 'Danno Commerciale';
$field->columntype = 'NUMERIC(10,2)';
$field->uitype = 71;
$field->helpinfo = "Distinta";
$field->typeofdata = 'NN~O~10,2';
$field->quickcreate = 1;
$block_eval->addField($field);

// DISTINTA Perdita ordine, Entrata concorrenza, Perdita margine, Perdita cliente, Perdita fatturato prodotto, Danni immagine x probl grave, Varie
$field = new Vtiger_Field();
$field->name = 'danno_comm_perd_ord';
$field->table = $module->basetable;
$field->label= 'Perdita ordine';
$field->columntype = 'NUMERIC(10,2)';
$field->uitype = 71;
//$field->helpinfo = "Distinta";
$field->typeofdata = 'NN~O~10,2';
$field->quickcreate = 1;
$block_eval->addField($field);

$field = new Vtiger_Field();
$field->name = 'danno_comm_entr_conc';
$field->table = $module->basetable;
$field->label= 'Entrata concorrenza';
$field->columntype = 'NUMERIC(10,2)';
$field->uitype = 71;
//$field->helpinfo = "Distinta";
$field->typeofdata = 'NN~O~10,2';
$field->quickcreate = 1;
$block_eval->addField($field);

$field = new Vtiger_Field();
$field->name = 'danno_comm_perd_mar';
$field->table = $module->basetable;
$field->label= 'Perdita margine';
$field->columntype = 'NUMERIC(10,2)';
$field->uitype = 71;
//$field->helpinfo = "Distinta";
$field->typeofdata = 'NN~O~10,2';
$field->quickcreate = 1;
$block_eval->addField($field);

$field = new Vtiger_Field();
$field->name = 'danno_comm_perd_cli';
$field->table = $module->basetable;
$field->label= 'Perdita cliente';
$field->columntype = 'NUMERIC(10,2)';
$field->uitype = 71;
//$field->helpinfo = "Distinta";
$field->typeofdata = 'NN~O~10,2';
$field->quickcreate = 1;
$block_eval->addField($field);

$field = new Vtiger_Field();
$field->name = 'danno_comm_perd_fatt';
$field->table = $module->basetable;
$field->label= 'Perdita fatturato prodotto';
$field->columntype = 'NUMERIC(10,2)';
$field->uitype = 71;
//$field->helpinfo = "Distinta";
$field->typeofdata = 'NN~O~10,2';
$field->quickcreate = 1;
$block_eval->addField($field);

$field = new Vtiger_Field();
$field->name = 'danno_comm_dann_imm';
$field->table = $module->basetable;
$field->label= 'Danni immagine x probl grave';
$field->columntype = 'NUMERIC(10,2)';
$field->uitype = 71;
//$field->helpinfo = "Distinta";
$field->typeofdata = 'NN~O~10,2';
$field->quickcreate = 1;
$block_eval->addField($field);

$field = new Vtiger_Field();
$field->name = 'danno_comm_varie';
$field->table = $module->basetable;
$field->label= 'Varie';
$field->columntype = 'NUMERIC(10,2)';
$field->uitype = 71;
//$field->helpinfo = "Distinta";
$field->typeofdata = 'NN~O~10,2';
$field->quickcreate = 1;
$block_eval->addField($field);

$field = new Vtiger_Field();
$field->name = 'dati_comm';
$field->table = $module->basetable;
$field->label= 'Dati commerciali';
$field->columntype = 'NUMERIC(10,2)';
$field->uitype = 71;
$field->helpinfo = "Distinta";
$field->typeofdata = 'NN~O~10,2';
$field->quickcreate = 1;
$block_eval->addField($field);

// DISTINTA Fatture danni, Note di accredito, Fatture fermo cantiere, omaggio 
$field = new Vtiger_Field();
$field->name = 'dati_comm_fatt_dann';
$field->table = $module->basetable;
$field->label= 'Fatture danni';
$field->columntype = 'NUMERIC(10,2)';
$field->uitype = 71;
//$field->helpinfo = "Distinta";
$field->typeofdata = 'NN~O~10,2';
$field->quickcreate = 1;
$block_eval->addField($field);

$field = new Vtiger_Field();
$field->name = 'dati_comm_note_acc';
$field->table = $module->basetable;
$field->label= 'Note di accredito';
$field->columntype = 'NUMERIC(10,2)';
$field->uitype = 71;
//$field->helpinfo = "Distinta";
$field->typeofdata = 'NN~O~10,2';
$field->quickcreate = 1;
$block_eval->addField($field);

$field = new Vtiger_Field();
$field->name = 'dati_comm_fermo_can';
$field->table = $module->basetable;
$field->label= 'Fatture fermo cantiere';
$field->columntype = 'NUMERIC(10,2)';
$field->uitype = 71;
//$field->helpinfo = "Distinta";
$field->typeofdata = 'NN~O~10,2';
$field->quickcreate = 1;
$block_eval->addField($field);

$field = new Vtiger_Field();
$field->name = 'dati_comm_omaggio';
$field->table = $module->basetable;
$field->label= 'Omaggio';
$field->columntype = 'NUMERIC(10,2)';
$field->uitype = 71;
//$field->helpinfo = "Distinta";
$field->typeofdata = 'NN~O~10,2';
$field->quickcreate = 1;
$block_eval->addField($field);


$field = new Vtiger_Field();
$field->name = 'totale_valutazione';
$field->table = $module->basetable;
$field->label= 'Totale';
$field->columntype = 'NUMERIC(10,2)';
$field->uitype = 71;
//$field->helpinfo = "Distinta";
$field->typeofdata = 'NN~O~10,2';
$field->quickcreate = 1;
$block_eval->addField($field);

// Get module instance
SDK::setLanguageEntry('Nonconformities','it_it', 'LBL_NONCONFORMITY_EVALUATION' , 'Valutazione');
SDK::setLanguageEntry('Nonconformities','en_us', 'LBL_NONCONFORMITY_EVALUATION' , 'Evaluation');
SDK::setLanguageEntry('Nonconformities','de_de', 'LBL_NONCONFORMITY_EVALUATION' , 'Evaluation');

$module = Vtiger_Module::getInstance('Nonconformities');
//danzi.tn@20141023 gestione custom valutazione
Vtiger_Event::register($module ,'vtiger.entity.beforesave','NonconformitiesHandler','modules/Nonconformities/NonconformitiesHandler.php');


?>