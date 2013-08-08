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

SDK::setLanguageEntry('Nonconformities','it_it','Nonconformities' , 'Non Conformità');
SDK::setLanguageEntry('Nonconformities','it_it', 'SINGLE_Nonconformities' , 'Non Conformità');

SDK::setLanguageEntry('Nonconformities','it_it', 'LBL_NONCONFORMITY_INFORMATION' , 'Informazioni Non Conformità');
SDK::setLanguageEntry('Nonconformities','it_it', 'LBL_CUSTOM_INFORMATION' , 'Informazioni Personalizzate');
SDK::setLanguageEntry('Nonconformities','it_it', 'LBL_DESCRIPTION_INFORMATION' , 'Informazioni Descrizione');

SDK::setLanguageEntry('Nonconformities','it_it', 'Non Conformity Name' , 'Nome');
SDK::setLanguageEntry('Nonconformities','it_it', 'Description' , 'Descrizione');
SDK::setLanguageEntry('Nonconformities','it_it', 'Non Conformity Number' , 'Numero');
SDK::setLanguageEntry('Nonconformities','it_it', 'Product Id' , 'Prodotto');
SDK::setLanguageEntry('Nonconformities','it_it', 'Product Description' , 'Descrizione Prodotto');
SDK::setLanguageEntry('Nonconformities','it_it', 'Product Category' , 'Categoria Prodotto');
SDK::setLanguageEntry('Nonconformities','it_it', 'Non Conformity State' , 'Stato');
SDK::setLanguageEntry('Nonconformities','it_it', 'Assigned To' , 'Assegnato a');
SDK::setLanguageEntry('Nonconformities','it_it', 'Created Time' , 'Creato il');
SDK::setLanguageEntry('Nonconformities','it_it', 'Modified Time' , 'Modificato il');
SDK::setLanguageEntry('Nonconformities','it_it', 'Help Desk' , 'Help Desk');
SDK::setLanguageEntry('Nonconformities','it_it', 'Vendor Id' , 'Fornitore');
SDK::setLanguageEntry('Nonconformities','it_it', 'FIELDLABEL' , 'TRANS');

SDK::setLanguageEntry('Nonconformities','en_us','Nonconformities' , 'Non Conformities');
SDK::setLanguageEntry('Nonconformities','en_us','SINGLE_Nonconformities' , 'Non Conformity');

SDK::setLanguageEntry('Nonconformities','en_us','LBL_NONCONFORMITY_INFORMATION' , 'Non Conformity Information');
SDK::setLanguageEntry('Nonconformities','en_us','LBL_CUSTOM_INFORMATION' , 'Custom Information');
SDK::setLanguageEntry('Nonconformities','en_us','LBL_DESCRIPTION_INFORMATION' , 'Description Information');

SDK::setLanguageEntry('Nonconformities','en_us','Non Conformity Name' , 'Non Conformity Name');
SDK::setLanguageEntry('Nonconformities','en_us','Description' , 'Description');
SDK::setLanguageEntry('Nonconformities','en_us','Non Conformity Number' , 'Non Conformity Number');
SDK::setLanguageEntry('Nonconformities','en_us','Product Id' , 'Product Id');
SDK::setLanguageEntry('Nonconformities','en_us','Product Description' , 'Product Description');
SDK::setLanguageEntry('Nonconformities','en_us','Product Category' , 'Product Category');
SDK::setLanguageEntry('Nonconformities','en_us','Non Conformity State' , 'Non Conformity State');
SDK::setLanguageEntry('Nonconformities','en_us','Assigned To' , 'Assigned To');
SDK::setLanguageEntry('Nonconformities','en_us','Created Time' , 'Created Time');
SDK::setLanguageEntry('Nonconformities','en_us','Modified Time' , 'Modified Time');
SDK::setLanguageEntry('Nonconformities','en_us','Help Desk' , 'Help Desk');
SDK::setLanguageEntry('Nonconformities','en_us','Vendor Id' , 'Vendor Id');
SDK::setLanguageEntry('Nonconformities','en_us','FIELDLABEL' , 'TRANS');

?>