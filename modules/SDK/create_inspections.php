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
$module->name = 'Inspections';
$module->save();

// Initialize all the tables required
$module->initTables();
/**
* Creates the following table:
* vte_inspections (installedbaseid INTEGER)
* vte_inspectionscf(installedbaseid INTEGER PRIMARY KEY)
*/

// Add the module to the Menu (entry point from UI)
$menu = Vtiger_Menu::getInstance('Inventory');
$menu->addModule($module);

// Add the basic module block
$block1 = new Vtiger_Block();
$block1->label = 'LBL_INSPECTION_INFORMATION';
$module->addBlock($block1);

// Add custom block (required to support Custom Fields)
$block2 = new Vtiger_Block();
$block2->label = 'LBL_ORDER_INFORMATION';
$module->addBlock($block2);

// Add custom block (required to support Custom Fields)
$block3 = new Vtiger_Block();
$block3->label = 'LBL_CUSTOM_INFORMATION';
$module->addBlock($block3);

// Add description block (required to support Description)
$block4 = new Vtiger_Block();
$block4->label = 'LBL_DESCRIPTION_INFORMATION';
$module->addBlock($block4);

/** Create required fields and add to the block */
$field1 = new Vtiger_Field();
$field1->name = 'inspection_name';
$field1->table = $module->basetable;
$field1->label= 'Inspection Name';
$field1->columntype = 'VARCHAR(255)';
$field1->uitype = 1;
$field1->typeofdata = 'V~M';
$field1->quickcreate = 1;
$block1->addField($field1); 
// $field1->setPicklistValues( Array ('Revisione Periodica', 'Revisione Una Tantum', 'Revisione Ordinaria', 'Altro') );

// Set at-least one field to identifier of module record
$module->setEntityIdentifier($field1);

$field2 = new Vtiger_Field();
$field2->name = 'description';
$field2->table = $table_prefix.'_crmentity';
$field2->label = 'Description';
$field2->uitype = 19;
$field2->typeofdata = 'V~O';// Varchar~Optional
$block4->addField($field2); /** table and column are automatically set */
//$field2->setPicklistValues( Array ('Employee', 'Trainee') );

$field3 = new Vtiger_Field();
$field3->name = 'inspection_no';
$field3->table = $module->basetable;
$field3->label = 'Inspection Number';
$field3->uitype = 4;
$field3->columntype = 'VARCHAR(100)';
$field3->typeofdata = 'V~O'; //Varchar~Optional
$block1->addField($field3); 

$field23 = new Vtiger_Field();
$field23->name = 'accountid';
$field23->table = $module->basetable;
$field23->label= 'Account Id';
$field23->column = 'accountid';
$field23->uitype = 10;
$field23->columntype = 'INT(19)';
$field23->typeofdata = 'I~O';
$field23->helpinfo = 'Relate to an existing Account';
$field23->quickcreate = 0;
$block1->addField($field23);
$field23->setRelatedModules(Array('Accounts'));

$field36 = new Vtiger_Field();
$field36->name = 'account_cat';
$field36->label= 'Account Category';
$field36->table = $module->basetable;
$field36->uitype = 1;
$field36->typeofdata = 'V~O';
$field36->columntype = 'VARCHAR(100)';
$block1->addField($field36);


$field362 = new Vtiger_Field();
$field362->name = 'account_email';
$field362->label= 'Email';
$field362->table = $module->basetable;
$field362->uitype = 13;
$field362->typeofdata = 'E~O';
$field362->columntype = 'VARCHAR(100)';
$block1->addField($field362);

$field24 = new Vtiger_Field();
$field24->name = 'salesorderid';
$field24->table = $module->basetable;
$field24->label= 'Sales Order Id';
$field24->column = 'salesorderid';
$field24->uitype = 10;
$field24->columntype = 'INT(19)';
$field24->typeofdata = 'I~O';
$field24->helpinfo = 'Relate to an existing Sales Order';
$field24->quickcreate = 0;
$block2->addField($field24);
$field24->setRelatedModules(Array('SalesOrder'));

$field39 = new Vtiger_Field();
$field39->name = 'salesorder_date';
$field39->label= 'Sales Order Date';
$field39->table = $module->basetable;
$field39->uitype = 5;
$field39->typeofdata = 'D~O';
$block2->addField($field39);

$field4 = new Vtiger_Field();
$field4->name = 'productid';
$field4->table = $module->basetable;
$field4->label= 'Product Id';
$field4->column = 'productid';
$field4->uitype = 10;
$field4->columntype = 'INT(19)';
$field4->typeofdata = 'I~O';
$field4->helpinfo = 'Relate to an existing product';
$field4->quickcreate = 0;
$block2->addField($field4);
$field4->setRelatedModules(Array('Products'));

/** table, column, label, set to default values */
$field5 = new Vtiger_Field();
$field5->name = 'product_serialno';
$field5->label= 'Product Serial No';
$field5->table = $module->basetable;
$field5->column = 'product_serialno';
$field5->columntype = 'VARCHAR(255)';
$field5->uitype = 1;
$field5->displaytype = 1;
$field5->typeofdata = 'V~O';
$field5->quickcreate = 0;
$block2->addField($field5);
//$field4->setRelatedModules(Array('Contacts'));

$prod_progfield = new Vtiger_Field();
$prod_progfield->name = 'inspection_prog';//vte_installation_state
$prod_progfield->table = $module->basetable;
$prod_progfield->label = 'Inspection Prog';
$prod_progfield->uitype = 7;
$prod_progfield->columntype = 'INT(19)';
$prod_progfield->typeofdata = 'N~O~10,0';// Varchar~Optional'N~O~10,0'
$block2->addField($prod_progfield); /** table and column are automatically set */

$prod_qtyfield = new Vtiger_Field();
$prod_qtyfield->name = 'inspection_qty';//vte_installation_state
$prod_qtyfield->table = $module->basetable;
$prod_qtyfield->label = 'Inspection Qty';
$prod_qtyfield->uitype = 7;
$prod_qtyfield->columntype = 'INT(19)';
$prod_qtyfield->typeofdata = 'N~O~10,0';// Varchar~Optional'N~O~10,0'
$block2->addField($prod_qtyfield); /** table and column are automatically set */

/** table, column, label, set to default values */
$field6 = new Vtiger_Field();
$field6->name = 'product_category';
$field6->label= 'Product Category';
$field6->table = $module->basetable;
$field6->column = 'product_category';
$field6->columntype = 'VARCHAR(255)';
$field6->uitype = 1;
$field6->displaytype= 1;
$field6->typeofdata = 'V~O';
$field6->quickcreate = 0;
$block2->addField($field6);

$prod_field = new Vtiger_Field();
$prod_field->name = 'inspection_frequency';//vte_installation_state
$prod_field->table = $module->basetable;
$prod_field->label = 'Inspection Frequency';
$prod_field->uitype = 15;
$prod_field->columntype = 'VARCHAR(255)';
$prod_field->typeofdata = 'V~O';// Varchar~Optional
$block2->addField($prod_field); /** table and column are automatically set */
$prod_field->setPicklistValues( Array ('lbl_nd', 'lbl_6m','lbl_12m','lbl_18m','lbl_24m') );


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
$block2->addField($field11);
$field11->setRelatedModules(Array('Vendors'));

$field19 = new Vtiger_Field();
$field19->name = 'due_date';
$field19->label= 'Due Date';
$field19->table = $module->basetable;
$field19->uitype = 5;
$field19->typeofdata = 'D~O';
$block1->addField($field19);

$field7 = new Vtiger_Field();
$field7->name = 'inspection_state';//vte_installation_state
$field7->table = $module->basetable;
$field7->label = 'Inspection State';
$field7->uitype = 15;
$field7->columntype = 'VARCHAR(255)';
$field7->typeofdata = 'V~O';// Varchar~Optional
$block1->addField($field7); /** table and column are automatically set */
$field7->setPicklistValues( Array ('Aperta', 'In Scadenza','Scaduta','In corso','Chiusa OK', 'Sospesa','Chiusa KO') );

$field20 = new Vtiger_Field();
$field20->name = 'inspection_date';
$field20->label= 'Inspection Date';
$field20->table = $module->basetable;
$field20->uitype = 6;
$field20->typeofdata = 'D~O';
$block1->addField($field20);

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
$filter1->addField($field1,1)->addField($field4,2)->addField($field23,3)->addField($field19,4)->addField($field7,5)->addField($field20,6);

/** Associate other modules to this module */
//get_dependents_list -> 1 -> N
//get_related_list -> N -> N

//relazione n a n
$module->setRelatedList(Vtiger_Module::getInstance('HelpDesk'), 'Help Desk', Array('SELECT'));
$module->setRelatedList(Vtiger_Module::getInstance('Documents'), 'Documents',Array('ADD','SELECT'),'get_attachments');
$module->setRelatedList(Vtiger_Module::getInstance('Calendar'), 'Activities',Array('ADD'),'get_activities');
$module->setRelatedList(Vtiger_Module::getInstance('Calendar'), 'Activity History',Array('ADD'),'get_history');

$helpdesk = Vtiger_Module::getInstance('HelpDesk');
$helpdesk->setRelatedList(Vtiger_Module::getInstance('Inspections'), 'Inspections', Array('ADD','SELECT'));

//relazione 1 a n Products
$products = Vtiger_Module::getInstance('Products');
$products->setRelatedList(Vtiger_Module::getInstance('Inspections'), 'Inspections', Array('SELECT'), 'get_dependents_list');
$block_prod = Vtiger_Block::getInstance('LBL_PRODUCT_INFORMATION',$products);
$prodp_field = new Vtiger_Field();
$prodp_field->name = 'inspection_frequency';//vte_installation_state
$prodp_field->table = $products->basetable;
$prodp_field->label = 'Inspection Frequency';
$prodp_field->uitype = 15;
$prodp_field->columntype = 'VARCHAR(255)';
$prodp_field->typeofdata = 'V~O';// Varchar~Optional
$block_prod->addField($prodp_field); /** table and column are automatically set */
$prodp_field->setPicklistValues( Array ('lbl_nd', 'lbl_6m','lbl_12m','lbl_18m','lbl_24m') );
SDK::setLanguageEntry('Products','it_it','Inspection Frequency' , 'Frequenza Revisioni');
SDK::setLanguageEntry('Products','en_us', 'Inspection Frequency' , 'Inspection Frequency');
SDK::setLanguageEntry('Products','it_it','lbl_nd' , 'Nessuna Revisione');
SDK::setLanguageEntry('Products','en_us', 'lbl_nd' , 'No Inspection');
SDK::setLanguageEntry('Products','it_it','lbl_6m' , '6 Mesi');
SDK::setLanguageEntry('Products','en_us', 'lbl_6m' , '6 Months');
SDK::setLanguageEntry('Products','it_it','lbl_12m' , '12 Mesi');
SDK::setLanguageEntry('Products','en_us', 'lbl_12m' , '12 Months');
SDK::setLanguageEntry('Products','it_it','lbl_18m' , '18 Mesi');
SDK::setLanguageEntry('Products','en_us', 'lbl_18m' , '18 Months');
SDK::setLanguageEntry('Products','it_it','lbl_24m' , '24 Mesi');
SDK::setLanguageEntry('Products','en_us', 'lbl_24m' , '24 Months');
//relazione 1 a n Vendors
$vendors = Vtiger_Module::getInstance('Vendors');
$vendors->setRelatedList(Vtiger_Module::getInstance('Inspections'), 'Inspections', Array('SELECT'), 'get_dependents_list');
//relazione 1 a n Accounts
$accounts = Vtiger_Module::getInstance('Accounts');
$accounts->setRelatedList(Vtiger_Module::getInstance('Inspections'), 'Inspections', Array('SELECT'), 'get_dependents_list');
//relazione 1 a n Sales Order
$salesorder = Vtiger_Module::getInstance('SalesOrder');
$salesorder->setRelatedList(Vtiger_Module::getInstance('Inspections'), 'Inspections', Array('SELECT'), 'get_dependents_list');

Vtiger_Event::register($module ,'vtiger.entity.aftersave','InspectionsHandler','modules/Inspections/InspectionsHandler.php');
Vtiger_Event::register($module ,'vtiger.entity.beforesave','InspectionsHandler','modules/Inspections/InspectionsHandler.php');

require_once 'modules/ModComments/ModComments.php';
$detailviewblock_nc = ModComments::addWidgetTo('Inspections');

/****** DA COMMENTARE  ***********
// Gestione filtri sulle popup
$module = Vtiger_Module::getInstance('Inspections');
$module->setRelatedList($module, 'Related Inspections', Array('SELECT'),'get_related_inspections');

SDK::setLanguageEntry('Inspections','it_it','Related Inspections' , 'Revisioni collegate');
SDK::setLanguageEntry('Inspections','en_us', 'Related Inspections' , 'Related Inspections');

$hidden_accountid = array('accountid'=>'getObj("accountid").value');
$hidden_salesorderid = array('salesorderid'=>'getObj("salesorderid").value');
SDK::setPopupQuery('field', 'Inspections', 'salesorderid', 'modules/Inspections/SalesOrderQuery.php',$hidden_accountid);
SDK::setPopupQuery('field', 'Inspections', 'productid', 'modules/Inspections/ProductQuery.php',$hidden_salesorderid);
SDK::setPopupQuery('field', 'Inspections', 'accountid', 'modules/Inspections/AccountQuery.php',$hidden_salesorderid);

*/

// Gestione popup con setPopupReturnFunction per il popup Aziende
Vtiger_Link::addLink($moduleInstance->id, 'HEADERSCRIPT', 'CategoryToInspections', 'modules/Inspections/CategoryToInspections.js');
SDK::setExtraSrc('Inspections', 'modules/Inspections/CategoryToInspections.js');
SDK::setPopupReturnFunction('Inspections', 'accountid', 'modules/Inspections/CategoryToInspections.php');

// Gestione popup con setPopupReturnFunction per il popup Prodotti
Vtiger_Link::addLink($moduleInstance->id, 'HEADERSCRIPT', 'ProductToInspections', 'modules/Inspections/ProductToInspections.js');
SDK::setExtraSrc('Inspections', 'modules/Inspections/ProductToInspections.js');
SDK::setPopupReturnFunction('Inspections', 'productid', 'modules/Inspections/ProductToInspections.php');

/** Set sharing access of this module */
$module->setDefaultSharing('Private');

/** Enable and Disable available tools */
$module->enableTools(Array('Import', 'Export'));
$module->disableTools('Merge'); 

// LINGUE
SDK::setLanguageEntry('Inspections','it_it','Inspections' , 'Revisioni');
SDK::setLanguageEntry('Inspections','en_us', 'Inspections' , 'Inspections');

SDK::setLanguageEntry('Inspections','it_it', 'SINGLE_Inspections' , 'Revisione');
SDK::setLanguageEntry('Inspections','en_us', 'SINGLE_Inspections' , 'Inspection');

SDK::setLanguageEntry('Inspections','it_it','LBL_INSPECTION_INFORMATION' , 'Informazioni Revisione');
SDK::setLanguageEntry('Inspections','en_us', 'LBL_INSPECTION_INFORMATION' , 'Inspection Information');

SDK::setLanguageEntry('Inspections','it_it','LBL_ORDER_INFORMATION' , 'Informazioni Ordine di Vendita');
SDK::setLanguageEntry('Inspections','en_us', 'LBL_ORDER_INFORMATION' , 'Sales Order Information');

SDK::setLanguageEntry('Inspections','it_it','Inspection Name' , 'Nome Revisione');
SDK::setLanguageEntry('Inspections','en_us', 'Inspection Name' , 'Inspection Name');

SDK::setLanguageEntry('Inspections','it_it','Inspection Number' , 'Numero Revisione');
SDK::setLanguageEntry('Inspections','en_us', 'Inspection Number' , 'Inspection Number');

SDK::setLanguageEntry('Inspections','it_it','Inspection State' , 'Stato Revisione');
SDK::setLanguageEntry('Inspections','en_us', 'Inspection State' , 'Inspection State');

SDK::setLanguageEntry('Inspections','it_it','Inspection Date' , 'Data Revisione');
SDK::setLanguageEntry('Inspections','en_us', 'Inspection Date' , 'Inspection Date');

SDK::setLanguageEntry('Inspections','it_it','Due Date' , 'Scadenza Revisione');
SDK::setLanguageEntry('Inspections','en_us', 'Due Date' , 'Inspection Due Date');

SDK::setLanguageEntry('Inspections','it_it','Sales Order Date' , 'Data Ordine Vendita');
SDK::setLanguageEntry('Inspections','en_us', 'Sales Order Date' , 'Sales Order Date');

SDK::setLanguageEntry('Inspections','it_it','Product Category' , 'Categoria Prodotto');
SDK::setLanguageEntry('Inspections','en_us','Product Category' , 'Product Category');

SDK::setLanguageEntry('Inspections','it_it','Sales Order Id' , 'Ordine di vendita');
SDK::setLanguageEntry('Inspections','en_us','Sales Order Id' , 'Ordine di vendita');

SDK::setLanguageEntry('Inspections','it_it','Sales Order Subject' , 'Ordine di vendita');
SDK::setLanguageEntry('Inspections','en_us','Sales Order Subject' , 'Ordine di vendita');

SDK::setLanguageEntry('Inspections','it_it','Product Id' , 'Prodotto');
SDK::setLanguageEntry('Inspections','en_us','Product Id' , 'Product');

SDK::setLanguageEntry('Inspections','it_it','Account Id' , 'Azienda');
SDK::setLanguageEntry('Inspections','en_us','Account Id' , 'Account');

SDK::setLanguageEntry('Inspections','it_it','Vendor Id' , 'Fornitore');
SDK::setLanguageEntry('Inspections','en_us','Vendor Id' , 'Vendor');

SDK::setLanguageEntry('Inspections','it_it','Account Category' , 'Categoria Azienda');
SDK::setLanguageEntry('Inspections','en_us','Account Category' , 'Account Category');

SDK::setLanguageEntry('Inspections','it_it','Inspection Frequency' , 'Frequenza Revisioni');
SDK::setLanguageEntry('Inspections','en_us', 'Inspection Frequency' , 'Inspection Frequency');
SDK::setLanguageEntry('Inspections','it_it','lbl_nd' , 'Nessuna Revisione');
SDK::setLanguageEntry('Inspections','en_us', 'lbl_nd' , 'No Inspection');
SDK::setLanguageEntry('Inspections','it_it','lbl_6m' , '6 Mesi');
SDK::setLanguageEntry('Inspections','en_us', 'lbl_6m' , '6 Months');
SDK::setLanguageEntry('Inspections','it_it','lbl_12m' , '12 Mesi');
SDK::setLanguageEntry('Inspections','en_us', 'lbl_12m' , '12 Months');
SDK::setLanguageEntry('Inspections','it_it','lbl_18m' , '18 Mesi');
SDK::setLanguageEntry('Inspections','en_us', 'lbl_18m' , '18 Months');
SDK::setLanguageEntry('Inspections','it_it','lbl_24m' , '24 Mesi');
SDK::setLanguageEntry('Inspections','en_us', 'lbl_24m' , '24 Months');
SDK::setLanguageEntry('Inspections','it_it','Inspection Qty' , 'Q.ta Prod. da Ordine');
SDK::setLanguageEntry('Inspections','en_us', 'Inspection Qty' , 'Order Q.ty');

SDK::setLanguageEntry('Inspections','it_it','Product Serial No' , 'Numero di matricola');
SDK::setLanguageEntry('Inspections','en_us', 'Product Serial No' , 'Serial number');

SDK::setLanguageEntry('Inspections','it_it','Inspection Prog' , 'Num. Prog.');
SDK::setLanguageEntry('Inspections','en_us', 'Inspection Prog' , 'Prog. No');

// per aggiungere il supporto ai webservices
$module->initWebservice();

?>