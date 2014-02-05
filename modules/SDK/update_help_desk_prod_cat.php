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

/** Categoria corrispondente Rothoblaas - sostituisce */
$field20 = new Vtiger_Field();
$field20->name = 'product_cat';
$field21->column  ='prod_category';
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
$field21->column  ='prod_category_desc';
$field21->table = $module->basetable;
$field21->label= 'Rothoblaas Category Description';
$field21->columntype = 'VARCHAR(255)';
$field21->uitype = 2002;
$field21->typeofdata = 'V~O';
$field21->quickcreate = 0;
$block1->addField($field21);

//Register popup return function
Vtiger_Link::addLink($module->id, 'HEADERSCRIPT', 'ProductToHelpDesk', 'modules/SDK/src/modules/HelpDesk/ProductToHelpDesk.js');
SDK::setExtraSrc('HelpDesk', 'modules/SDK/src/modules/HelpDesk/ProductToHelpDesk.js');
// SDK::setPopupReturnFunction('HelpDesk', 'product_id', 'modules/SDK/src/modules/HelpDesk/ProductToHelpDesk.php');


?>
