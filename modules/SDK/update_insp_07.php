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

SDK::setLanguageEntry('Inspections','it_it','LBL_EXPORT_TYPE' , 'Come esportare queste revisioni?');
SDK::setLanguageEntry('Inspections','en_us', 'LBL_EXPORT_TYPE' , 'How do you want to export these Inspections?');

SDK::setLanguageEntry('Inspections','it_it','LBL_STANDARD' , 'Modo standard (csv)');
SDK::setLanguageEntry('Inspections','en_us', 'LBL_STANDARD' , 'The standard way (csv)');

SDK::setLanguageEntry('Inspections','it_it','LBL_4PRINT' , 'Per la stampa delle revisioni');
SDK::setLanguageEntry('Inspections','en_us', 'LBL_4PRINT' , 'For printing purposes');


SDK::setLanguageEntry('Inspections','it_it','inspection_no' , 'Numero Revisione');
SDK::setLanguageEntry('Inspections','en_us', 'inspection_no' , 'Inspection Number');
SDK::setLanguageEntry('Inspections','it_it','inspection_name' , 'Nome Revisione');
SDK::setLanguageEntry('Inspections','en_us', 'inspection_name' , 'Inspection Name');
SDK::setLanguageEntry('Inspections','it_it','accountname' , 'Azienda');
SDK::setLanguageEntry('Inspections','en_us', 'accountname' , 'Account Name');
SDK::setLanguageEntry('Inspections','it_it','subject' , 'Oggetto');
SDK::setLanguageEntry('Inspections','en_us', 'subject' , 'Subject');
SDK::setLanguageEntry('Inspections','it_it','productname' , 'Codice Articolo');
SDK::setLanguageEntry('Inspections','en_us', 'productname' , 'Product');
SDK::setLanguageEntry('Inspections','it_it','vendorname' , 'Marca');
SDK::setLanguageEntry('Inspections','en_us', 'vendorname' , 'Brand');
SDK::setLanguageEntry('Inspections','it_it','product_serialno' , 'Nr. Matricola');
SDK::setLanguageEntry('Inspections','en_us', 'product_serialno' , 'Serial No.');
SDK::setLanguageEntry('Inspections','it_it','user_name' , 'Nome Utente');
SDK::setLanguageEntry('Inspections','en_us', 'user_name' , 'User Name');
SDK::setLanguageEntry('Inspections','it_it','salesdate' , 'Data di Acquisto');
SDK::setLanguageEntry('Inspections','en_us', 'salesdate' , 'Sales Date');
SDK::setLanguageEntry('Inspections','it_it','nextinspdate' , 'Data Prossima Revisione');
SDK::setLanguageEntry('Inspections','en_us', 'nextinspdate' , 'Next Inspection');
SDK::setLanguageEntry('Inspections','it_it','inspection_state' , 'Esito');
SDK::setLanguageEntry('Inspections','en_us', 'inspection_state' , 'Esito');



?>
