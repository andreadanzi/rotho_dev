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
SDK::setLanguageEntry('Relations','it_it', 'lbl_rel_active' , 'Attiva');
SDK::setLanguageEntry('Relations','en_us', 'lbl_rel_active' , 'Active');
SDK::setLanguageEntry('Relations','it_it','lbl_rel_inactive' , 'Inattiva');
SDK::setLanguageEntry('Relations','en_us','lbl_rel_inactive' , 'Inactive');
SDK::setLanguageEntry('Relations','it_it','lbl_rel_suspended' , 'Sospesa');
SDK::setLanguageEntry('Relations','en_us','lbl_rel_suspended' , 'Suspended');
SDK::setLanguageEntry('Relations','it_it','lbl_rel_to-be-verified' , 'Da Verificare');
SDK::setLanguageEntry('Relations','en_us','lbl_rel_to-be-verified' , 'To Be Verified');
SDK::setLanguageEntry('Relations','it_it','lbl_rel_other' , 'Altro');
SDK::setLanguageEntry('Relations','en_us','lbl_rel_other' , 'Other');



?>
