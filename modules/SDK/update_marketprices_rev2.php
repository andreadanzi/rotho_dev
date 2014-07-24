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

// Get module instance
$module = Vtiger_Module::getInstance('Marketprices');

$block1 = Vtiger_Block::getInstance('LBL_MARKETPRICES_INFORMATION',$module); 

/** Collegamento a Report Visita */
$field3 = new Vtiger_Field();
$field3->name = 'visitreport';
$field3->table = $module->basetable;
$field3->label= 'Visit Report';
$field3->column = 'visitreport';
$field3->uitype = 10;
$field3->columntype = 'INT(19)';
$field3->typeofdata = 'I~O';
$field3->displaytype= 1;
$field3->quickcreate = 0;
$block1->addField($field3);
$field3->setRelatedModules(Array('Visitreport'));

SDK::setLanguageEntry('Marketprices','it_it', 'Visit Report' , 'Report visita');
SDK::setLanguageEntry('Marketprices','en_us', 'Visit Report' , 'Visit report');
SDK::setLanguageEntry('Marketprices','de_de', 'Visit Report' , 'Visit report');

//relazione 1 a n visitreport
$visitreport = Vtiger_Module::getInstance('Visitreport');
$visitreport->setRelatedList($module, 'Marketprices', Array('ADD','SELECT'), 'get_dependents_list');

Vtiger_Link::addLink($module->id, 'HEADERSCRIPT', 'VisitreportToMarketprices', 'modules/Marketprices/VisitreportToMarketprices.js');
SDK::setExtraSrc('Marketprices', 'modules/Marketprices/VisitreportToMarketprices.js');
SDK::setPopupReturnFunction('Marketprices', 'visitreport', 'modules/Marketprices/VisitreportToMarketprices.php');

?>
