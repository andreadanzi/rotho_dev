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
SDK::setLanguageEntry('Relations','it_it', 'lbl_rel_supplyfromco' , 'Cliente del Concorrente');
SDK::setLanguageEntry('Relations','it_it', 'lbl_rel_supplytocomp' , 'Customer of Competitor');
SDK::setLanguageEntry('Relations','en_us','lbl_rel_supplyfromco' , 'Fornitore (Concorrente) di');
SDK::setLanguageEntry('Relations','en_us','lbl_rel_supplytocomp' , 'Supplier (Competitor) of');


?>
