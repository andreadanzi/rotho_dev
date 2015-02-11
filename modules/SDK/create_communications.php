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

// Create module instance and save it first
$module = new Vtiger_Module();
$module->name = 'Communications';
$module->save();

// Initialize all the tables required
$module->initTables();

// Add the module to the Menu (entry point from UI)
$menu = Vtiger_Menu::getInstance('Communications');
$menu->addModule($module);

// Add the basic module block
$block1 = new Vtiger_Block();
$block1->label = 'LBL_COMMUNICATIONS_INFORMATION';
$module->addBlock($block1);

// Add custom block (required to support Custom Fields)
$block2 = new Vtiger_Block();
$block2->label = 'LBL_CUSTOM_INFORMATION';
$module->addBlock($block2);

// Add description block (required to support Description)
$block3 = new Vtiger_Block();
$block3->label = 'LBL_DESCRIPTION_INFORMATION';
$module->addBlock($block3);

/** Create required fields and add to the block */
$field1 = new Vtiger_Field();
$field1->name = 'communication_name';
$field1->table = $module->basetable;
$field1->label = 'Communication Name';
$field1->uitype = 2;
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$block1->addField($field1); /** table and column are automatically set */

$field5 = new Vtiger_Field();
$field5->name = 'communication_no';
$field5->table = $module->basetable;
$field5->label = 'Communication Number';
$field5->uitype = 4;
$field5->columntype = 'VARCHAR(100)';
$field5->typeofdata = 'V~O'; //Varchar~Optional
$block1->addField($field5); 
// Set at-least one field to identifier of module record
$module->setEntityIdentifier($field5);


/** Collegamento a Account-Cliente */
$field4 = new Vtiger_Field();
$field4->name = 'communication_parentid';
$field4->table = $module->basetable;
$field4->label= 'Collegato a';
$field4->column = 'communication_parentid';
$field4->uitype = 10;
$field4->columntype = 'INT(19)';
$field4->typeofdata = 'I~O';
$field4->displaytype= 1;
$field4->helpinfo = 'Relate from an existing Account, Contact or Lead';
$field4->quickcreate = 0;
$block1->addField($field4);
$field4->setRelatedModules(Array('Accounts','Contacts','Leads'));


/** Create required fields and add to the block */
$field3 = new Vtiger_Field();
$field3->name = 'communication_type';
$field3->table = $module->basetable;
$field3->label = 'Communication Type';
$field3->uitype = 15;
$field3->columntype = 'VARCHAR(255)';
$field3->typeofdata = 'V~O';// Varchar~Optional
$block1->addField($field3); /** table and column are automatically set */
$field3->setPicklistValues( Array ('---', 'Cambio Agente', 'Cambio Area Manager', 'Cambio Referente Vendita', 'Attivazione nuovo Cliente', 'Altro') );

$field20 = new Vtiger_Field();
$field20->name = 'communication_date';
$field20->label= 'Communication Date';
$field20->table = $module->basetable;
$field20->uitype = 6;
$field20->typeofdata = 'D~O';
$block1->addField($field20);


/** Create required fields and add to the block */
$field21 = new Vtiger_Field();
$field21->name = 'communication_status';
$field21->table = $module->basetable;
$field21->label = 'Communication Status';
$field21->uitype = 15;
$field21->columntype = 'VARCHAR(255)';
$field21->typeofdata = 'V~O';// Varchar~Optional
$block1->addField($field21); /** table and column are automatically set */
$field21->setPicklistValues( Array ('---', 'Presenta Errori', 'Da approvare', 'Approvata', 'Inviata', 'Bloccata') );

/** Create required fields and add to the block */
$field22 = new Vtiger_Field();
$field22->name = 'communication_template';
$field22->table = $module->basetable;
$field22->label = 'Template';
$field22->uitype = 1;
$field22->columntype = 'VARCHAR(255)';
$field22->typeofdata = 'V~O';// Varchar~Optional
$block1->addField($field22); /** table and column are automatically set */

$field2 = new Vtiger_Field();
$field2->name = 'description';
$field2->table = $table_prefix.'_crmentity';
$field2->label = 'Description';
$field2->uitype = 19;
$field2->typeofdata = 'V~O';// Varchar~Optional
$block3->addField($field2); /** table and column are automatically set */

/** Common fields that should be in every module, linked to vtiger CRM core table */
$field8 = new Vtiger_Field();
$field8->name = 'assigned_user_id';
$field8->label = 'Assigned To';
$field8->table = $table_prefix.'_crmentity';
$field8->column = 'smownerid';
$field8->uitype = 53;
$field8->typeofdata = 'V~M';
$field8->quickcreate = 0;
$block1->addField($field8);

$field9 = new Vtiger_Field();
$field9->name = 'createdtime';
$field9->label= 'Created Time';
$field9->table = $table_prefix.'_crmentity';
$field9->column = 'createdtime';
$field9->uitype = 70;
$field9->typeofdata = 'T~O';
$field9->displaytype= 2;
$block1->addField($field9);

$field10 = new Vtiger_Field();
$field10->name = 'modifiedtime';
$field10->label= 'Modified Time';
$field10->table = $table_prefix.'_crmentity';
$field10->column = 'modifiedtime';
$field10->uitype = 70;
$field10->typeofdata = 'T~O';
$field10->displaytype= 2;
$block1->addField($field10);

/** END */

// Create default custom filter (mandatory)
$filter1 = new Vtiger_Filter();
$filter1->name = 'All';
$filter1->isdefault = true;
$module->addFilter($filter1);

// Add fields to the filter created
$filter1->addField($field1,1)->addField($field5,2)->addField($field4,4)->addField($field3,5)->addField($field20,6)->addField($field21,6);

//relazione 1 a n Accounts
$accounts = Vtiger_Module::getInstance('Accounts');
$accounts->setRelatedList($module, 'Communications', Array('ADD','SELECT'), 'get_dependents_list');

SDK::setLanguageEntry( 'Communications','it_it','Communication Name' , 'Nome Comunicazione' );
SDK::setLanguageEntry( 'Communications','en_us', 'Communication Name' , 'Communication Name');
SDK::setLanguageEntry( 'Communications','ru_ru', 'Communication Name', 'Communication Name');
SDK::setLanguageEntry( 'Communications','de_de', 'Communication Name' ,'Communication Name');
SDK::setLanguageEntry( 'Communications','es_es','Communication Name' ,'Communication Name');
SDK::setLanguageEntry( 'Communications','fr_fr','Communication Name' ,'Communication Name');
SDK::setLanguageEntry( 'Communications','pt_br','Communication Name' , 'Communication Name');

SDK::setLanguageEntry( 'Communications','it_it','Communication Number' , 'Codice Comunicazione' );
SDK::setLanguageEntry( 'Communications','en_us', 'Communication Number' , 'Communication Number');
SDK::setLanguageEntry( 'Communications','ru_ru', 'Communication Number', 'Communication Number');
SDK::setLanguageEntry( 'Communications','de_de', 'Communication Number' ,'Communication Number');
SDK::setLanguageEntry( 'Communications','es_es','Communication Number' ,'Communication Number');
SDK::setLanguageEntry( 'Communications','fr_fr','Communication Number' ,'Communication Number');
SDK::setLanguageEntry( 'Communications','pt_br','Communication Number' , 'Communication Number');

SDK::setLanguageEntry( 'Communications','it_it','Communication Date' , 'Data Comunicazione' );
SDK::setLanguageEntry( 'Communications','en_us', 'Communication Date' , 'Communication Date');
SDK::setLanguageEntry( 'Communications','ru_ru', 'Communication Date', 'Communication Date');
SDK::setLanguageEntry( 'Communications','de_de', 'Communication Date' ,'Communication Date');
SDK::setLanguageEntry( 'Communications','es_es','Communication Date' ,'Communication Date');
SDK::setLanguageEntry( 'Communications','fr_fr','Communication Date' ,'Communication Date');
SDK::setLanguageEntry( 'Communications','pt_br','Communication Date' , 'Communication Date');

SDK::setLanguageEntry( 'Communications','it_it','Communication Type' , 'Tipo Comunicazione' );
SDK::setLanguageEntry( 'Communications','en_us', 'Communication Type' , 'Communication Type');
SDK::setLanguageEntry( 'Communications','ru_ru', 'Communication Type', 'Communication Type');
SDK::setLanguageEntry( 'Communications','de_de', 'Communication Type' ,'Communication Type');
SDK::setLanguageEntry( 'Communications','es_es','Communication Type' ,'Communication Type');
SDK::setLanguageEntry( 'Communications','fr_fr','Communication Type' ,'Communication Type');
SDK::setLanguageEntry( 'Communications','pt_br','Communication Type' , 'Communication Type');

SDK::setLanguageEntry( 'Communications','it_it','Communication Status' , 'Stato Comunicazione' );
SDK::setLanguageEntry( 'Communications','en_us', 'Communication Status' , 'Communication Status');
SDK::setLanguageEntry( 'Communications','ru_ru', 'Communication Status', 'Communication Status');
SDK::setLanguageEntry( 'Communications','de_de', 'Communication Status' ,'Communication Status');
SDK::setLanguageEntry( 'Communications','es_es','Communication Status' ,'Communication Status');
SDK::setLanguageEntry( 'Communications','fr_fr','Communication Status' ,'Communication Status');
SDK::setLanguageEntry( 'Communications','pt_br','Communication Status' , 'Communication Status');


SDK::setLanguageEntry( 'Communications','it_it','LBL_COMMUNICATIONS_INFORMATION' , 'Comunicazione' );
SDK::setLanguageEntry( 'Communications','en_us', 'LBL_COMMUNICATIONS_INFORMATION' , 'Communication');
SDK::setLanguageEntry( 'Communications','ru_ru', 'LBL_COMMUNICATIONS_INFORMATION', 'Communication');
SDK::setLanguageEntry( 'Communications','de_de', 'LBL_COMMUNICATIONS_INFORMATION' ,'Communication');
SDK::setLanguageEntry( 'Communications','es_es','LBL_COMMUNICATIONS_INFORMATION' ,'Communication');
SDK::setLanguageEntry( 'Communications','fr_fr','LBL_COMMUNICATIONS_INFORMATION' ,'Communication');
SDK::setLanguageEntry( 'Communications','pt_br','LBL_COMMUNICATIONS_INFORMATION' , 'Communication');




/** Set sharing access of this module */
$module->setDefaultSharing('Public');

/** Enable and Disable available tools */
$module->enableTools(Array('Import', 'Export'));
$module->disableTools('Merge'); 

// per aggiungere il supporto ai webservices
$module->initWebservice();

?>
