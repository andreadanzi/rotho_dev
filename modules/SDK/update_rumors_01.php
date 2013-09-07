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

$rum = Vtiger_Module::getInstance('Rumors');

//relazione 1 a n Accounts (for Customer and Competitor)
$accounts = Vtiger_Module::getInstance('Accounts');
$accounts->setRelatedList($rum, 'Rumors', Array('ADD','SELECT'), 'get_rumors');

//relazione 1 a n Products
$products = Vtiger_Module::getInstance('Products');
$products->setRelatedList($rum, 'Rumors', Array('ADD','SELECT'), 'get_dependents_list');

//Register popup return function
Vtiger_Link::addLink($rum->id, 'HEADERSCRIPT', 'ProductToRumors', 'modules/Rumors/ProductToRumors.js');
SDK::setExtraSrc('Rumors', 'modules/Rumors/ProductToRumors.js');
SDK::setPopupReturnFunction('Rumors', 'product_code', 'modules/Rumors/ProductToRumors.php');

//Caricamento Concorrenti
SDK::setPopupQuery('field','Rumors','competitor','modules/SDK/src/modules/Rumors/QueryCompetitors.php');
?>
