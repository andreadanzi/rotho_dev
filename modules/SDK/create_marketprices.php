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

//danzi.tn@20140717 creazione nuovo modulo Marketprices
// Create module instance and save it first
$module = new Vtiger_Module();
$module->name = 'Marketprices';
$module->save();

// Initialize all the tables required
$module->initTables();

// Add the module to the Menu (entry point from UI)
$menu = Vtiger_Menu::getInstance('Marketing');
$menu->addModule($module);

// Add the basic module block
$block1 = new Vtiger_Block();
$block1->label = 'LBL_MARKETPRICES_INFORMATION';
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
$field1->name = 'marketprice_name';
$field1->table = $module->basetable;
$field1->label = 'Market Price Name';
$field1->uitype = 15;
$field1->columntype = 'VARCHAR(255)';
$field1->typeofdata = 'V~O';// Varchar~Optional
$block1->addField($field1); /** table and column are automatically set */
$field1->setPicklistValues( Array ('lbl_mkp_none', 'lbl_mkp_news', 'lbl_mkp_price', 'lbl_mkp_compet', 'lbl_mkp_other') );


$field5 = new Vtiger_Field();
$field5->name = 'marketprice_no';
$field5->table = $module->basetable;
$field5->label = 'Market Price Number';
$field5->uitype = 4;
$field5->columntype = 'VARCHAR(100)';
$field5->typeofdata = 'V~O'; //Varchar~Optional
$block1->addField($field5); 
// Set at-least one field to identifier of module record
$module->setEntityIdentifier($field5);


/** Oggetto */
$field13 = new Vtiger_Field();
$field13->name = 'marketprice_subject';
$field13->table = $module->basetable;
$field13->label= 'Subject';
$field13->columntype = 'VARCHAR(255)';
$field13->uitype = 2;
$field13->typeofdata = 'V~O';
$field13->quickcreate = 1;
$block1->addField($field13); 


/** Collegamento a Account-Concorrente */
$field3 = new Vtiger_Field();
$field3->name = 'competitor';
$field3->table = $module->basetable;
$field3->label= 'Competitor';
$field3->column = 'competitor';
$field3->uitype = 10;
$field3->columntype = 'INT(19)';
$field3->typeofdata = 'I~O';
$field3->displaytype= 1;
$field3->helpinfo = 'Relate to an existing Competitor';
$field3->quickcreate = 0;
$block1->addField($field3);
$field3->setRelatedModules(Array('Accounts'));

/** Codice prodotto concorrenza */
$field13 = new Vtiger_Field();
$field13->name = 'competitor_product_code';
$field13->table = $module->basetable;
$field13->label= 'Competitor Product Code';
$field13->columntype = 'VARCHAR(255)';
$field13->uitype = 2;
$field13->typeofdata = 'V~O';
$field13->quickcreate = 1;
$block1->addField($field13); 

/** Nome prodotto concorrenza */
$field14 = new Vtiger_Field();
$field14->name = 'competitor_product_desc';
$field14->table = $module->basetable;
$field14->label= 'Competitor Product Description';
$field14->columntype = 'VARCHAR(255)';
$field14->uitype = 2;
$field14->typeofdata = 'V~O';
$field14->quickcreate = 1;
$block1->addField($field14); 

/** Prodotto corrispondente Rothoblaas */
$field18 = new Vtiger_Field();
$field18->name = 'product_code';
$field18->table = $module->basetable;
$field18->label= 'Rothoblaas Product code';
$field18->columntype = 'VARCHAR(10)';
$field18->column = 'product_code';
$field18->uitype = 10;
$field18->columntype = 'INT(19)';
$field18->typeofdata = 'I~O';
$field18->helpinfo = 'Relate to an existing Product';
$field18->quickcreate = 0;
$block1->addField($field18);
$field18->setRelatedModules(Array('Products'));

/** Descrizione Prodotto corrispondente Rothoblaas */
$field19 = new Vtiger_Field();
$field19->name = 'product_desc';
$field19->table = $module->basetable;
$field19->label= 'Rothoblaas Product Description';
$field19->columntype = 'VARCHAR(255)';
$field19->uitype = 2;
$field19->typeofdata = 'V~O';
$field19->quickcreate = 0;
$block1->addField($field19);

/** Categoria corrispondente Rothoblaas */
$field20 = new Vtiger_Field();
$field20->name = 'product_cat';
$field20->table = $module->basetable;
$field20->label= 'Rothoblaas Category';
$field20->columntype = 'VARCHAR(255)';
$field20->uitype = 2001;
$field20->typeofdata = 'V~O';
$field20->quickcreate = 0;
$block1->addField($field20);

/** Descrizione Categoria corrispondente Rothoblaas */
$field21 = new Vtiger_Field();
$field21->name = 'product_cat_descr';
$field21->table = $module->basetable;
$field21->label= 'Rothoblaas Category Description';
$field21->columntype = 'VARCHAR(255)';
$field21->uitype = 2002;
$field21->typeofdata = 'V~O';
$field21->quickcreate = 0;
$block1->addField($field21);

/** Quantità venduta */
$field15 = new Vtiger_Field();
$field15->name = 'marketprice_quantity';
$field15->table = $module->basetable;
$field15->label= 'Quantity';
$field15->columntype = 'DECIMAL(11,0)';
$field15->uitype = 7;
$field15->typeofdata = 'NN~O~10,0';
$field15->quickcreate = 1;
$block1->addField($field15); 

/** UM di riferimento */
$field17 = new Vtiger_Field();
$field17->name = 'unit_of_measure';
$field17->table = $module->basetable;
$field17->label= 'Unit of measure';
$field17->columntype = 'VARCHAR(10)';
$field17->uitype = 15;
$field17->typeofdata = 'V~O';
$field17->quickcreate = 1;
$block1->addField($field17);
$field17->setPicklistValues( Array ('PZ', 'CO', 'm', 'mq', 'kg', 'Pallet') );

/** Prezzo di vendita */
$field16 = new Vtiger_Field();
$field16->name = 'marketprice_price';
$field16->table = $module->basetable;
$field16->label= 'Net Price';
$field16->columntype = 'NUMERIC(25,2)';
$field16->uitype = 7;
$field16->typeofdata = 'NN~O~10,2';
$field16->quickcreate = 1;
$block1->addField($field16);

$field30 = new Vtiger_Field();
$field30->name = 'currency_id';
$field30->label = 'Currency';
$field30->table = $module->basetable;
$field30->column = 'currency_id';
$field16->columntype = 'INT(19)';
$field30->uitype = 117;
$field30->typeofdata = 'I~O';
$field30->displaytype = 3;
$block1->addField($field30);

/** Collegamento a Account-Cliente */
$field4 = new Vtiger_Field();
$field4->name = 'accounts_customer';
$field4->table = $module->basetable;
$field4->label= 'Customer';
$field4->column = 'accounts_customer';
$field4->uitype = 10;
$field4->columntype = 'INT(19)';
$field4->typeofdata = 'I~O';
$field4->displaytype= 1;
$field4->helpinfo = 'Relate from an existing Account';
$field4->quickcreate = 0;
$block1->addField($field4);
$field4->setRelatedModules(Array('Accounts'));

$field21 = new Vtiger_Field();
$field21->name = 'customer_cat';
$field21->table = $module->basetable;
$field21->label= 'Category';
$field21->columntype = 'VARCHAR(100)';
$field21->uitype = 1;
$field21->readonly = 99;
$field21->typeofdata = 'V~O';
$block1->addField($field21);

$field21 = new Vtiger_Field();
$field21->name = 'country';
$field21->table = $module->basetable;
$field21->label= 'Country';
$field21->columntype = 'VARCHAR(100)';
$field21->uitype = 15;
$field21->readonly = 99;
$field21->typeofdata = 'V~O';
$block1->addField($field21);

$field21 = new Vtiger_Field();
$field21->name = 'area_mng_no';
$field21->table = $module->basetable;
$field21->label= 'Area Manager No';
$field21->columntype = 'VARCHAR(100)';
$field21->uitype = 1;
$field21->readonly = 99;
$field21->typeofdata = 'V~O';
$block1->addField($field21);

$field21 = new Vtiger_Field();
$field21->name = 'area_mng_name';
$field21->table = $module->basetable;
$field21->label= 'Area Manager Name';
$field21->columntype = 'VARCHAR(100)';
$field21->uitype = 1;
$field21->readonly = 99;
$field21->typeofdata = 'V~O';
$block1->addField($field21);

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

$field2 = new Vtiger_Field();
$field2->name = 'description';
$field2->table = $table_prefix.'_crmentity';
$field2->label = 'Description';
$field2->uitype = 19;
$field2->typeofdata = 'V~O';// Varchar~Optional
$block3->addField($field2); /** table and column are automatically set */

/** END */

// Create default custom filter (mandatory)
$filter1 = new Vtiger_Filter();
$filter1->name = 'All';
$filter1->isdefault = true;
$module->addFilter($filter1);

// Add fields to the filter created
$filter1->addField($field1,1)->addField($field20,2)->addField($field21,4)->addField($field3,5)->addField($field8,6);

/** Associate other modules to this module */
//relazione n a n
$module->setRelatedList(Vtiger_Module::getInstance('Products'), 'Other Products', Array('SELECT')); // ricordarsi che bisogna sistemare la query su prodotti get_marketprices
$module->setRelatedList(Vtiger_Module::getInstance('Documents'), 'Documents',Array('ADD','SELECT'),'get_attachments');
$module->setRelatedList(Vtiger_Module::getInstance('Calendar'), 'Activities',Array('ADD'),'get_activities');
$module->setRelatedList(Vtiger_Module::getInstance('Calendar'), 'Activity History',Array('ADD'),'get_history');


//relazione 1 a n Accounts (for Customer and Competitor)
$accounts = Vtiger_Module::getInstance('Accounts');
$accounts->setRelatedList($module, 'Marketprices', Array('ADD','SELECT'), 'get_marketprices'); // vedere come fa get_rumors

//relazione 1 a n Products
$products = Vtiger_Module::getInstance('Products');
$products->setRelatedList($module, 'Marketprices', Array('ADD','SELECT'), 'get_marketprices'); // vedere come fa su AccountsRotho

//Register popup return function
Vtiger_Link::addLink($module->id, 'HEADERSCRIPT', 'ProductToMarketprices', 'modules/Marketprices/ProductToMarketprices.js');
SDK::setExtraSrc('Marketprices', 'modules/Marketprices/ProductToMarketprices.js');
SDK::setPopupReturnFunction('Marketprices', 'product_code', 'modules/Marketprices/ProductToMarketprices.php');

//Caricamento Concorrenti
SDK::setPopupQuery('field','Marketprices','competitor','modules/Marketprices/QueryCompetitors.php');

//Caricamento clienti
Vtiger_Link::addLink($module->id, 'HEADERSCRIPT', 'AccountToMarketprices', 'modules/Marketprices/AccountToMarketprices.js');
SDK::setExtraSrc('Marketprices', 'modules/Marketprices/AccountToMarketprices.js');
SDK::setPopupReturnFunction('Marketprices', 'accounts_customer', 'modules/Marketprices/AccountToMarketprices.php');

require_once 'modules/ModComments/ModComments.php';
$detailviewblock_nc = ModComments::addWidgetTo('Marketprices');



?>
