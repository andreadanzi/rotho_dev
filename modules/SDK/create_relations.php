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
$module->name = 'Relations';
$module->save();

// Initialize all the tables required
$module->initTables();
/**
* Creates the following table:
* vte_nonconformities (installedbaseid INTEGER)
* vte_nonconformitiescf(installedbaseid INTEGER PRIMARY KEY)
*/

// Add the module to the Menu (entry point from UI)
$menu = Vtiger_Menu::getInstance('Marketing');
$menu->addModule($module);

// Add the basic module block
$block1 = new Vtiger_Block();
$block1->label = 'LBL_RELATION_INFORMATION';
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
$field1->name = 'relation_name';//vte_installation_state
$field1->table = $module->basetable;
$field1->label = 'Relation Name';
$field1->uitype = 1015;
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$block1->addField($field1); /** table and column are automatically set */
$field1->setPicklistValues( Array ('lbl_rel_none', 'lbl_rel_same_family', 'lbl_rel_same_company', 'lbl_rel_b2b', 'lbl_rel_association', 'lbl_rel_affiliation', 'lbl_rel_subcontracting_to','lbl_rel_subcontracting_from','lbl_rel_connected_to','lbl_rel_owned_to','lbl_rel_owned_from','lbl_rel_supply_to','lbl_rel_supply_from' ,'lbl_rel_other') );

// Set at-least one field to identifier of module record
$module->setEntityIdentifier($field1);

/** Create required fields and add to the block */
$field13 = new Vtiger_Field();
$field13->name = 'group_name';
$field13->table = $module->basetable;
$field13->label= 'Group Name';
$field13->columntype = 'VARCHAR(255)';
$field13->uitype = 2;
$field13->typeofdata = 'V~O';
$field13->quickcreate = 1;
$block1->addField($field13); 

$field2 = new Vtiger_Field();
$field2->name = 'description';
$field2->table = $table_prefix.'_crmentity';
$field2->label = 'Description';
$field2->uitype = 19;
$field2->typeofdata = 'V~O';// Varchar~Optional
$block3->addField($field2); /** table and column are automatically set */
//$field2->setPicklistValues( Array ('Employee', 'Trainee') );

$field4 = new Vtiger_Field();
$field4->name = 'link_from';
$field4->table = $module->basetable;
$field4->label= 'Link From';
$field4->column = 'link_from';
$field4->uitype = 10;
$field4->columntype = 'INT(19)';
$field4->typeofdata = 'I~O';
$field4->displaytype= 1;
$field4->helpinfo = 'Relate from an existing Account';
$field4->quickcreate = 0;
$block1->addField($field4);
$field4->setRelatedModules(Array('Accounts'));

/** table, column, label, set to default values */
$field5 = new Vtiger_Field();
$field5->name = 'link_from_category';
$field5->label= 'Link From Category';
$field5->table = $module->basetable;
$field5->column = 'link_from_category';
$field5->columntype = 'VARCHAR(255)';
$field5->uitype = 3;
$field5->displaytype= 1;
$field5->typeofdata = 'V~O';
$field5->quickcreate = 0;
$block1->addField($field5);
//$field4->setRelatedModules(Array('Contacts'));

$field6 = new Vtiger_Field();
$field6->name = 'link_to';
$field6->table = $module->basetable;
$field6->label= 'Link To';
$field6->column = 'link_to';
$field6->uitype = 10;
$field6->columntype = 'INT(19)';
$field6->typeofdata = 'I~O';
$field6->displaytype= 1;
$field6->helpinfo = 'Relate to an existing Account';
$field6->quickcreate = 0;
$block1->addField($field6);
$field6->setRelatedModules(Array('Accounts'));

/** table, column, label, set to default values */
$field11 = new Vtiger_Field();
$field11->name = 'link_to_category';
$field11->label= 'Link To Category';
$field11->table = $module->basetable;
$field11->column = 'link_to_category';
$field11->columntype = 'VARCHAR(255)';
$field11->uitype = 3;
$field11->displaytype= 1;
$field11->typeofdata = 'V~O';
$field11->quickcreate = 0;
$block1->addField($field11);
//$field4->setRelatedModules(Array('Contacts'));

$field3 = new Vtiger_Field();
$field3->name = 'relation_no';
$field3->table = $module->basetable;
$field3->label = 'Relation Number';
$field3->uitype = 4;
$field3->columntype = 'VARCHAR(100)';
$field3->typeofdata = 'V~O'; //Varchar~Optional
$block1->addField($field3); 

$field7 = new Vtiger_Field();
$field7->name = 'relation_state';//vte_installation_state
$field7->table = $module->basetable;
$field7->label = 'Relation State';
$field7->uitype = 1015;
$field7->columntype = 'VARCHAR(255)';
$field7->typeofdata = 'V~O';// Varchar~Optional
$block1->addField($field7); /** table and column are automatically set */
$field7->setPicklistValues( Array ( 'lbl_rel_active', 'lbl_rel_inactive', 'lbl_rel_suspended', 'lbl_rel_to-be-verified', 'lbl_rel_other') );

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
$filter1->addField($field1,1)->addField($field4,2)->addField($field6,4)->addField($field7,5)->addField($field13,6);

/** Associate other modules to this module */
//get_dependents_list -> 1 -> N
//get_related_list -> N -> N

//relazione n a n
$module->setRelatedList(Vtiger_Module::getInstance('HelpDesk'), 'Help Desk', Array('SELECT'));
$module->setRelatedList(Vtiger_Module::getInstance('Documents'), 'Documents',Array('ADD','SELECT'),'get_attachments');
$module->setRelatedList(Vtiger_Module::getInstance('Calendar'), 'Activities',Array('ADD'),'get_activities');
$module->setRelatedList(Vtiger_Module::getInstance('Calendar'), 'Activity History',Array('ADD'),'get_history');

$helpdesk = Vtiger_Module::getInstance('HelpDesk');
$helpdesk->setRelatedList(Vtiger_Module::getInstance('Relations'), 'Non Conformities', Array('ADD','SELECT'));

//relazione 1 a n Accounts
$accounts = Vtiger_Module::getInstance('Accounts');
$accounts->setRelatedList(Vtiger_Module::getInstance('Relations'), 'Relations', Array('ADD','SELECT'), 'get_dependents_list');

// Gestione popup con setPopupReturnFunction
Vtiger_Link::addLink($moduleInstance->id, 'HEADERSCRIPT', 'CategoryToRelations', 'modules/Relations/CategoryToRelations.js');
SDK::setExtraSrc('Relations', 'modules/Relations/CategoryToRelations.js');
Vtiger_Link::addLink($moduleInstance->id, 'HEADERSCRIPT', 'CategoryFromRelations', 'modules/Relations/CategoryFromRelations.js');
SDK::setExtraSrc('Relations', 'modules/Relations/CategoryFromRelations.js');
SDK::setPopupReturnFunction('Relations', 'link_from', 'modules/Relations/CategoryFromRelations.php');
SDK::setPopupReturnFunction('Relations', 'link_to', 'modules/Relations/CategoryToRelations.php');
Vtiger_Event::register($module ,'vtiger.entity.aftersave','RelationsHandler','modules/Relations/RelationsHandler.php');
Vtiger_Event::register($module ,'vtiger.entity.beforesave','RelationsHandler','modules/Relations/RelationsHandler.php');

/** Set sharing access of this module */
$module->setDefaultSharing('Private');

/** Enable and Disable available tools */
$module->enableTools(Array('Import', 'Export'));
$module->disableTools('Merge'); 

// per aggiungere il supporto ai webservices
$module->initWebservice();

?>
