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
$module->name = 'Nonconformities';
$module->save();

// Initialize all the tables required
$module->initTables();
/**
* Creates the following table:
* vte_nonconformities (installedbaseid INTEGER)
* vte_nonconformitiescf(installedbaseid INTEGER PRIMARY KEY)
*/

// Add the module to the Menu (entry point from UI)
$menu = Vtiger_Menu::getInstance('Inventory');
$menu->addModule($module);

// Add the basic module block
$block1 = new Vtiger_Block();
$block1->label = 'LBL_NONCONFORMITY_INFORMATION';
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
$field1->name = 'nonconformity_name';
$field1->table = $module->basetable;
$field1->label= 'Non Conformity Name';
$field1->columntype = 'VARCHAR(255)';
$field1->uitype = 2;
$field1->typeofdata = 'V~M';
$field1->quickcreate = 1;
$block1->addField($field1); 

// Set at-least one field to identifier of module record
$module->setEntityIdentifier($field1);

$field2 = new Vtiger_Field();
$field2->name = 'description';
$field2->table = $table_prefix.'_crmentity';
$field2->label = 'Description';
$field2->uitype = 19;
$field2->typeofdata = 'V~O';// Varchar~Optional
$block3->addField($field2); /** table and column are automatically set */
//$field2->setPicklistValues( Array ('Employee', 'Trainee') );

$field3 = new Vtiger_Field();
$field3->name = 'nonconformity_no';
$field3->table = $module->basetable;
$field3->label = 'Non Conformity Number';
$field3->uitype = 4;
$field3->columntype = 'VARCHAR(100)';
$field3->typeofdata = 'V~O'; //Varchar~Optional
$block1->addField($field3); 

$field4 = new Vtiger_Field();
$field4->name = 'product_id';
$field4->table = $module->basetable;
$field4->label= 'Product Id';
$field4->column = 'productid';
$field4->uitype = 10;
$field4->columntype = 'INT(19)';
$field4->typeofdata = 'I~O';
$field4->displaytype= 1;
$field4->helpinfo = 'Relate to an existing product';
$field4->quickcreate = 0;
$block1->addField($field4);
$field4->setRelatedModules(Array('Products'));


/** table, column, label, set to default values */
$field5 = new Vtiger_Field();
$field5->name = 'product_description';
$field5->label= 'Product Description';
$field5->table = $module->basetable;
$field5->column = 'product_description';
$field5->columntype = 'VARCHAR(255)';
$field5->uitype = 2;
$field11->displaytype= 1;
$field5->typeofdata = 'V~O';
$field5->quickcreate = 0;
$block1->addField($field5);
//$field4->setRelatedModules(Array('Contacts'));

/** table, column, label, set to default values */
$field6 = new Vtiger_Field();
$field6->name = 'product_category';
$field6->label= 'Product Category';
$field6->table = $module->basetable;
$field6->column = 'product_category';
$field6->columntype = 'VARCHAR(255)';
$field6->uitype = 2;
$field11->displaytype= 1;
$field6->typeofdata = 'V~O';
$field6->quickcreate = 0;
$block1->addField($field6);


$field11 = new Vtiger_Field();
$field11->name = 'vendor_id';
$field11->table = $module->basetable;
$field11->label= 'Vendor Id';
$field11->column = 'vendorid';
$field11->uitype = 10;
$field11->columntype = 'INT(19)';
$field11->typeofdata = 'I~O';
$field11->displaytype= 1;
$field11->helpinfo = 'Relate to an existing vendor';
$field11->quickcreate = 0;
$block1->addField($field11);
$field11->setRelatedModules(Array('Vendors'));


$field7 = new Vtiger_Field();
$field7->name = 'nonconformity_state';//vte_installation_state
$field7->table = $module->basetable;
$field7->label = 'Non Conformity State';
$field7->uitype = 15;
$field7->columntype = 'VARCHAR(255)';
$field7->typeofdata = 'V~O';// Varchar~Optional
$block1->addField($field7); /** table and column are automatically set */
$field7->setPicklistValues( Array ('-- Nessuno -- ', 'Aperta', 'Chiusa', 'Sospesa', 'In attesa di risposta', 'Soluzione economica sospesa') );

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
$filter1->addField($field1,1)->addField($field3,2)->addField($field4,3)->addField($field5,4)->addField($field6,5)->addField($field7,6);

/** Associate other modules to this module */
//get_dependents_list -> 1 -> N
//get_related_list -> N -> N

//relazione n a n
$module->setRelatedList(Vtiger_Module::getInstance('HelpDesk'), 'Help Desk', Array('SELECT'));
$module->setRelatedList(Vtiger_Module::getInstance('Documents'), 'Documents',Array('ADD','SELECT'),'get_attachments');
$module->setRelatedList(Vtiger_Module::getInstance('Calendar'), 'Activities',Array('ADD'),'get_activities');
$module->setRelatedList(Vtiger_Module::getInstance('Calendar'), 'Activity History',Array('ADD'),'get_history');

$helpdesk = Vtiger_Module::getInstance('HelpDesk');
$helpdesk->setRelatedList(Vtiger_Module::getInstance('Nonconformities'), 'Non Conformities', Array('ADD','SELECT'));

//relazione 1 a n Products
$products = Vtiger_Module::getInstance('Products');
$products->setRelatedList(Vtiger_Module::getInstance('Nonconformities'), 'Non Conformities', Array('ADD','SELECT'), 'get_dependents_list');
//relazione 1 a n Vendors
$vendors = Vtiger_Module::getInstance('Vendors');
$vendors->setRelatedList(Vtiger_Module::getInstance('Nonconformities'), 'Non Conformities', Array('ADD','SELECT'), 'get_dependents_list');

// Gestione popup con setPopupReturnFunction
Vtiger_Link::addLink($moduleInstance->id, 'HEADERSCRIPT', 'CategoryToNonconformities', 'modules/Nonconformities/CategoryToNonconformities.js');
SDK::setExtraSrc('Nonconformities', 'modules/Nonconformities/CategoryToNonconformities.js');
SDK::setPopupReturnFunction('Nonconformities', 'product_id', 'modules/Nonconformities/CategoryToNonconformities.php');

/** Set sharing access of this module */
$module->setDefaultSharing('Private');

/** Enable and Disable available tools */
$module->enableTools(Array('Import', 'Export'));
$module->disableTools('Merge'); 

// per aggiungere il supporto ai webservices
$module->initWebservice();

?>