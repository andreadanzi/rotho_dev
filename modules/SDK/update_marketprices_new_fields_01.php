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

/** Mittente segnalazione */
$field13 = new Vtiger_Field();
$field13->name = 'infosender';
$field13->table = $module->basetable;
$field13->label= 'Mittente segnalazione';
$field13->columntype = 'VARCHAR(255)';
$field13->uitype = 1;
$field13->typeofdata = 'V~O';
$block1->addField($field13); 

SDK::setLanguageEntry('Marketprices','it_it', 'Mittente segnalazione' , 'Mittente segnalazione');
SDK::setLanguageEntry('Marketprices','en_us', 'Mittente segnalazione' , 'Sender');
SDK::setLanguageEntry('Marketprices','de_de', 'Mittente segnalazione' , 'Sender');

?>
