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
$module = Vtiger_Module::getInstance('Inspections');
$block1 = Vtiger_Block::getInstance('LBL_INSPECTION_INFORMATION',$module);

$langfield = new Vtiger_Field();
$langfield->name = 'account_baselang';
$langfield->table = $module->basetable;
$langfield->label= 'Account Base Language';
$langfield->columntype = 'VARCHAR(255)';
$langfield->uitype = 1;
$langfield->typeofdata = 'V~O';
$langfield->quickcreate = 1;
$block1->addField($langfield); 
SDK::setLanguageEntry('Inspections','it_it','Account Base Language' , 'Lingua Base Cliente');
SDK::setLanguageEntry('Inspections','en_us', 'Account Base Language' , 'Account Base Language');

$insptypefield = new Vtiger_Field();
$insptypefield->name = 'inspection_type';
$insptypefield->table = $module->basetable;
$insptypefield->label= 'Inspection Type';
$insptypefield->columntype = 'VARCHAR(255)';
$insptypefield->uitype = 15;
$insptypefield->typeofdata = 'V~O';
$insptypefield->quickcreate = 1;
$block1->addField($insptypefield); 
$insptypefield->setPicklistValues( Array ('lbl_type_nd', 'lbl_type_1','lbl_type_2','lbl_type_3','lbl_type_4') );

SDK::setLanguageEntry('Inspections','it_it','Inspection Type' , 'Tipo Revisione');
SDK::setLanguageEntry('Inspections','en_us', 'Inspection Type' , 'Inspection Type');
SDK::setLanguageEntry('Inspections','it_it','Inspection UID' , 'UID Revisione');
SDK::setLanguageEntry('Inspections','en_us', 'Inspection UID' , 'Inspection UID');

SDK::setLanguageEntry('Inspections','it_it','lbl_type_nd' , 'Non definito');
SDK::setLanguageEntry('Inspections','en_us','lbl_type_nd' , 'Not available');
SDK::setLanguageEntry('Inspections','it_it','lbl_type_1' , 'Tipo 1');
SDK::setLanguageEntry('Inspections','en_us','lbl_type_1' , 'Type 1');
SDK::setLanguageEntry('Inspections','it_it','lbl_type_2' , 'Tipo 2');
SDK::setLanguageEntry('Inspections','en_us','lbl_type_2' , 'Type 2');
SDK::setLanguageEntry('Inspections','it_it','lbl_type_3' , 'Tipo 3');
SDK::setLanguageEntry('Inspections','en_us','lbl_type_3' , 'Type 3');
SDK::setLanguageEntry('Inspections','it_it','lbl_type_4' , 'Tipo 4');
SDK::setLanguageEntry('Inspections','en_us','lbl_type_4' , 'Type 4');

$inspuserfield = new Vtiger_Field();
$inspuserfield->name = 'inspection_user_id';
$inspuserfield->table = $module->basetable;
$inspuserfield->label= 'Inspection User';
$inspuserfield->uitype = 53;
$inspuserfield->typeofdata = 'V~O';
$block1->addField($inspuserfield); 
SDK::setLanguageEntry('Inspections','it_it','Inspection User' , 'Responsabile Revisione');
SDK::setLanguageEntry('Inspections','en_us', 'Inspection User' , 'Inspection User');
SDK::setLanguageEntry('Inspections','it_it','Inspection Sequence' , 'Sequenza Revisione');
SDK::setLanguageEntry('Inspections','en_us', 'Inspection Sequence' , 'Inspection Sequence');

?>
