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
$module->name = 'MailchimpSync';
$module->basetable = $table_prefix.'_mailchimpsync';
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
$block1->label = 'LBL_MAILCHIMP_INFORMATION';
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
$field1->name = 'mailchimp_name';
$field1->table = $module->basetable;
$field1->label= 'Mailchimp Name';
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
$field3->name = 'mailchimp_no';
$field3->table = $module->basetable;
$field3->label = 'Campaign No';
$field3->uitype = 4;
$field3->columntype = 'VARCHAR(100)';
$field3->typeofdata = 'V~O'; //Varchar~Optional
$block1->addField($field3); 

$field4 = new Vtiger_Field();
$field4->name = 'mailchimp_type';//vte_installation_state
$field4->table = $module->basetable;
$field4->label = 'Campaign Type';
$field4->uitype = 15;
$field4->columntype = 'VARCHAR(255)';
$field4->typeofdata = 'V~O';// Varchar~Optional
$block1->addField($field4); /** table and column are automatically set */
$field4->setPicklistValues( Array ('--None--', 'regular', 'plaintext','absplit', 'rss', 'auto' ) );

$field7 = new Vtiger_Field();
$field7->name = 'mailchimp_state';//vte_installation_state
$field7->table = $module->basetable;
$field7->label = 'Campaign State';
$field7->uitype = 15;
$field7->columntype = 'VARCHAR(255)';
$field7->typeofdata = 'V~O';// Varchar~Optional
$block1->addField($field7); /** table and column are automatically set */
$field7->setPicklistValues( Array ('--None--', 'sent', 'save', 'paused', 'schedule', 'sending') );

$field5 = new Vtiger_Field();
$field5->name = 'lastsynchronization';
$field5->label= 'Last Synchronization';
$field5->table = $module->basetable;
$field5->uitype = 6;
$field5->typeofdata = 'T~O';
$field5->displaytype = 2;
$block1->addField($field5);

$field16 = new Vtiger_Field();
$field16->name = 'mailchimp_uid';
$field16->label= 'MailChimp Campaign UID';
$field16->table = $module->basetable;
$field16->columntype = 'VARCHAR(255)';
$field16->uitype = 1;
$field16->typeofdata = 'V~O';
$field16->quickcreate = 1;
$block1->addField($field16);


$field26 = new Vtiger_Field();
$field26->name = 'mailchimp_link';
$field26->label= 'MailChimp Campaign Link';
$field26->table = $module->basetable;
$field26->columntype = 'VARCHAR(255)';
$field26->uitype = 17;
$field26->typeofdata = 'V~O';
$field26->quickcreate = 1;
$block1->addField($field26);

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
$filter1->addField($field1,1)->addField($field3,2)->addField($field4,3)->addField($field5,4)->addField($field7,5);

/** Associate other modules to this module */
//get_dependents_list -> 1 -> N
//get_related_list -> N -> N

//relazione n a n
$module->setRelatedList(Vtiger_Module::getInstance('Targets'), 'Targets', Array('SELECT'));
$module->setRelatedList(Vtiger_Module::getInstance('Accounts'), 'Accounts', Array('SELECT'));
$module->setRelatedList(Vtiger_Module::getInstance('Calendar'), 'Activities',Array('ADD'),'get_activities');
$module->setRelatedList(Vtiger_Module::getInstance('Calendar'), 'Activity History',Array('ADD'),'get_history');
$module->setRelatedList(Vtiger_Module::getInstance('Contacts'), 'Contacts', Array('SELECT'));
$module->setRelatedList(Vtiger_Module::getInstance('Leads'), 'Leads', Array('SELECT'));

$leads = Vtiger_Module::getInstance('Leads');
$leads->setRelatedList(Vtiger_Module::getInstance('MailchimpSync'), 'MailchimpSync', Array('ADD','SELECT'));
$contacts = Vtiger_Module::getInstance('Contacts');
$contacts->setRelatedList(Vtiger_Module::getInstance('MailchimpSync'), 'MailchimpSync', Array('ADD','SELECT'));
$targets = Vtiger_Module::getInstance('Targets');
$targets->setRelatedList(Vtiger_Module::getInstance('MailchimpSync'), 'MailchimpSync', Array('ADD','SELECT'));
$accounts = Vtiger_Module::getInstance('Accounts');
$accounts->setRelatedList(Vtiger_Module::getInstance('MailchimpSync'), 'MailchimpSync', Array('ADD','SELECT'));

/** Set sharing access of this module */
$module->setDefaultSharing('Private');

/** Enable and Disable available tools */
$module->enableTools(Array('Import', 'Export'));
$module->disableTools('Merge'); 

?>