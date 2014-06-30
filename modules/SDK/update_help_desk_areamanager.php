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

// danzi.tn@20140307 nuovo flag per assegazione a marketing

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

SDK::setLanguageEntry('HelpDesk','it_it', 'Area Manager No' , 'Nr. Area Manager');
SDK::setLanguageEntry('HelpDesk','en_us', 'Area Manager No' , 'Area Manager No');
SDK::setLanguageEntry('HelpDesk','de_de', 'Area Manager No' , 'Area Manager N.');


SDK::setLanguageEntry('HelpDesk','it_it', 'Area Manager Name' , 'Nome Area Manager');
SDK::setLanguageEntry('HelpDesk','en_us', 'Area Manager Name' , 'Area Manager Name');
SDK::setLanguageEntry('HelpDesk','de_de', 'Area Manager Name' , 'Area Manager Name');

// $module->setRelatedList(Vtiger_Module::getInstance('Products'), 'Products', Array('SELECT'));

?>