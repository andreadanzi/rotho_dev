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

$module = Vtiger_Module::getInstance('Nonconformities');

$block1 = Vtiger_Block::getInstance('LBL_NONCONFORMITY_INFORMATION',$module); 

// danzi.tn@20150505 nuovo campo Responsabile strategico di prodotto

/** Numero responsabile prodotto **/
$field20 = new Vtiger_Field();
$field20->name = 'product_resp_no';
$field20->table = $module->basetable;
$field20->label= 'RESPONSIBLE NUMBER';
$field20->columntype = 'VARCHAR(255)';
$field20->uitype = 1;
$field20->readonly = 99;
$field20->typeofdata = 'V~O';
$field20->quickcreate = 0;
$field20->masseditable = 0;
$block1->addField($field20);

/** Nome responsabile prodotto */
$field21 = new Vtiger_Field();
$field21->name = 'product_resp_name';
$field21->table = $module->basetable;
$field21->label= 'RESPONSIBLE NAME';
$field21->columntype = 'VARCHAR(255)';
$field21->uitype = 1;
$field21->readonly = 99;
$field21->typeofdata = 'V~O';
$field21->quickcreate = 0;
$field21->masseditable = 0;
$block1->addField($field21);


$block2 = Vtiger_Block::getInstance('Acquisti',$module); 

$field8 = new Vtiger_Field();
$field8->name = 'purchase_operator_id';
$field8->label = 'Purchase Operator';
$field8->uitype = 1077;
$field8->typeofdata = 'V~O~LE~50';
$field8->quickcreate = 0;
$field21->masseditable = 0;
$block2->addField($field8);
SDK::setLanguageEntry('Nonconformities','it_it','Purchase Operator' , 'Operatore Acq.');
SDK::setLanguageEntry('Nonconformities','en_us', 'Purchase Operator' , 'Purchase Operator');
SDK::setLanguageEntry('Nonconformities','de_de', 'Purchase Operator' , 'Purchase Operator');


SDK::setLanguageEntry('Nonconformities','it_it','Nome operatore' , 'Operatore P.E.');
SDK::setLanguageEntry('Nonconformities','en_us', 'Nome operatore' , 'P.E. Operator');
SDK::setLanguageEntry('Nonconformities','de_de', 'Nome operatore' , 'P.E. Operator');


SDK::setLanguageEntry('Nonconformities','it_it', 'RESPONSIBLE NUMBER' , 'No Responsabile Strategico Prodotto');
SDK::setLanguageEntry('Nonconformities','en_us', 'RESPONSIBLE NUMBER' , 'Responsible number');
SDK::setLanguageEntry('Nonconformities','de_de', 'RESPONSIBLE NUMBER' , 'Responsible number');

SDK::setLanguageEntry('Nonconformities','it_it', 'RESPONSIBLE NAME' , 'Nome Responsabile Strategico Prodotto');
SDK::setLanguageEntry('Nonconformities','en_us', 'RESPONSIBLE NAME' , 'Responsible name');
SDK::setLanguageEntry('Nonconformities','de_de', 'RESPONSIBLE NAME' , 'Responsible name');




?>
