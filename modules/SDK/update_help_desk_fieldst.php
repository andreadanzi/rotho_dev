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

$module = Vtiger_Module::getInstance('HelpDesk');

$block1 = Vtiger_Block::getInstance('LBL_TICKET_INFORMATION',$module); 

$vtiger_ticketcategories = array(
	array( // row #0
		'ticketcategories_id' => 1,
		'ticketcategories' => 'Difetto prodotto',
		'PRESENCE' => 1,
		'picklist_valueid' => 179,
		'sub_of' => 'Prodotto',
	),
	array( // row #1
		'ticketcategories_id' => 2,
		'ticketcategories' => 'Destinazione errata del materiale',
		'PRESENCE' => 1,
		'picklist_valueid' => 180,
		'sub_of' => '',
	),
	array( // row #2
		'ticketcategories_id' => 3,
		'ticketcategories' => 'Prodotto incompleto',
		'PRESENCE' => 1,
		'picklist_valueid' => 181,
		'sub_of' => '',
	),
	array( // row #3
		'ticketcategories_id' => 4,
		'ticketcategories' => 'Materiale danneggiato - confezione',
		'PRESENCE' => 1,
		'picklist_valueid' => 450,
		'sub_of' => '',
	),
	array( // row #4
		'ticketcategories_id' => 5,
		'ticketcategories' => 'Quantita` errata',
		'PRESENCE' => 1,
		'picklist_valueid' => 451,
		'sub_of' => '',
	),
	array( // row #5
		'ticketcategories_id' => 6,
		'ticketcategories' => 'Articolo sbagliato',
		'PRESENCE' => 1,
		'picklist_valueid' => 452,
		'sub_of' => '',
	),
	array( // row #6
		'ticketcategories_id' => 7,
		'ticketcategories' => 'Prezzo netto o sconto errato',
		'PRESENCE' => 1,
		'picklist_valueid' => 453,
		'sub_of' => '',
	),
	array( // row #7
		'ticketcategories_id' => 8,
		'ticketcategories' => 'Fattura con condizioni di pagamento errate',
		'PRESENCE' => 1,
		'picklist_valueid' => 454,
		'sub_of' => '',
	),
	array( // row #8
		'ticketcategories_id' => 9,
		'ticketcategories' => 'Consegna in ritardo',
		'PRESENCE' => 1,
		'picklist_valueid' => 455,
		'sub_of' => '',
	),
	array( // row #9
		'ticketcategories_id' => 10,
		'ticketcategories' => 'Altro',
		'PRESENCE' => 1,
		'picklist_valueid' => 1006,
		'sub_of' => '',
	),
	array( // row #10
		'ticketcategories_id' => 12,
		'ticketcategories' => '***Errore magazzino',
		'PRESENCE' => 1,
		'picklist_valueid' => 19052,
		'sub_of' => '',
	),
	array( // row #11
		'ticketcategories_id' => 13,
		'ticketcategories' => 'Riparazione Officina',
		'PRESENCE' => 1,
		'picklist_valueid' => 19125,
		'sub_of' => '',
	),
	array( // row #12
		'ticketcategories_id' => 14,
		'ticketcategories' => '***Vendita persa',
		'PRESENCE' => 1,
		'picklist_valueid' => 85507,
		'sub_of' => '',
	),
	array( // row #13
		'ticketcategories_id' => 15,
		'ticketcategories' => 'Causa contabile',
		'PRESENCE' => 1,
		'picklist_valueid' => 1295358,
		'sub_of' => '',
	),
	array( // row #14
		'ticketcategories_id' => 16,
		'ticketcategories' => 'Codice cliente errato',
		'PRESENCE' => 1,
		'picklist_valueid' => 1295359,
		'sub_of' => '',
	),
	array( // row #15
		'ticketcategories_id' => 17,
		'ticketcategories' => 'Errata entrata merci',
		'PRESENCE' => 1,
		'picklist_valueid' => 1295360,
		'sub_of' => '',
	),
	array( // row #16
		'ticketcategories_id' => 18,
		'ticketcategories' => 'Errato addebito spese di trasporto',
		'PRESENCE' => 1,
		'picklist_valueid' => 1295361,
		'sub_of' => '',
	),
	array( // row #17
		'ticketcategories_id' => 19,
		'ticketcategories' => 'Errore catalogo',
		'PRESENCE' => 1,
		'picklist_valueid' => 1295362,
		'sub_of' => '',
	),
	array( // row #18
		'ticketcategories_id' => 20,
		'ticketcategories' => 'Errore per doppio inserimento ordine',
		'PRESENCE' => 1,
		'picklist_valueid' => 1295363,
		'sub_of' => '',
	),
	array( // row #19
		'ticketcategories_id' => 21,
		'ticketcategories' => 'Fallimento - insoluto cliente',
		'PRESENCE' => 1,
		'picklist_valueid' => 1295364,
		'sub_of' => '',
	),
	array( // row #20
		'ticketcategories_id' => 22,
		'ticketcategories' => 'Premio fine anno',
		'PRESENCE' => 1,
		'picklist_valueid' => 1295365,
		'sub_of' => '',
	),
	array( // row #21
		'ticketcategories_id' => 23,
		'ticketcategories' => 'Recapito smarrito',
		'PRESENCE' => 1,
		'picklist_valueid' => 1295366,
		'sub_of' => '',
	),
	array( // row #22
		'ticketcategories_id' => 24,
		'ticketcategories' => 'MAG. + merce',
		'PRESENCE' => 1,
		'picklist_valueid' => 1295367,
		'sub_of' => '',
	),
	array( // row #23
		'ticketcategories_id' => 25,
		'ticketcategories' => 'MAG. - merce',
		'PRESENCE' => 1,
		'picklist_valueid' => 1295368,
		'sub_of' => '',
	),
	array( // row #24
		'ticketcategories_id' => 26,
		'ticketcategories' => 'Smarrito',
		'PRESENCE' => 1,
		'picklist_valueid' => 1295375,
		'sub_of' => '',
	),
	array( // row #25
		'ticketcategories_id' => 27,
		'ticketcategories' => '-- Nessuno --',
		'PRESENCE' => 1,
		'picklist_valueid' => 1295701,
		'sub_of' => '',
	),
	array( // row #26
		'ticketcategories_id' => 28,
		'ticketcategories' => 'Modifica Prodotto',
		'PRESENCE' => 1,
		'picklist_valueid' => 1295743,
		'sub_of' => '',
	),
	array( // row #27
		'ticketcategories_id' => 29,
		'ticketcategories' => 'Errore Listino',
		'PRESENCE' => 1,
		'picklist_valueid' => 1295744,
		'sub_of' => '',
	),
);


$picklist_array = Array();
foreach($vtiger_ticketcategories as $item) {
	$picklist_array[] = $item['ticketcategories'];
}



/** Descrizione Categoria corrispondente Rothoblaas 
$field21 = new Vtiger_Field();
$field21->name = 'ticketsubcategories';
$field21->column = 'subcategories';
$field21->table = $module->basetable;
$field21->label= 'Ticket Sub Category';
$field21->columntype = 'VARCHAR(255)';
$field21->uitype = 15;
$field21->typeofdata = 'V~O';
$block1->addField($field21);
$field21->setPicklistValues($picklist_array ); 

SDK::setLanguageEntry('HelpDesk','it_it', 'Ticket Sub Category' , 'Sotto Categoria');
SDK::setLanguageEntry('HelpDesk','en_us', 'Ticket Sub Category' , 'Sub Category');
SDK::setLanguageEntry('HelpDesk','de_de', 'Ticket Sub Category' , 'Sub Category');
*/

$cat_field = Vtiger_Field::getInstance('ticketcategories',$module);
$cat_field->unsetPicklistValues($picklist_array);
$cat_field->setPicklistValues( array('Prodotto','Servizio','Listino','Catalogo','Altro') );

/* Poi bisogna aggiornare 
$sql = "update vtiger_troubletickets
		set vtiger_troubletickets.subcategories = vtiger_troubletickets.category
		from vtiger_troubletickets
		join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_troubletickets.ticketid and deleted=0";
$adb->execute($sql);
*/

foreach($vtiger_ticketcategories as $item) {
	$sql = "UPDATE vtiger_troubletickets SET vtiger_troubletickets.category = '".$item['sub_of']."' 
			FROM vtiger_troubletickets
			JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_troubletickets.ticketid and deleted=0
			WHERE vtiger_troubletickets.category = '".$item['ticketcategories']."'";
	$adb->execute($sql);
}


?>