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
$module = Vtiger_Module::getInstance('Accounts');

$block1 = Vtiger_Block::getInstance('LBL_ACCOUNT_INFORMATION',$module); 

/** Flag Importazione Semiramis 
Deve essere vuoto di base, poi se un field viene
*/
$field13 = new Vtiger_Field();
$field13->name = 'sem_importflag';
$field13->table = $module->basetable;
$field13->label= 'Stato Importazione Semiramis';
$field13->columntype = 'VARCHAR(255)';
$field13->uitype = 1;
$field20->readonly = 99; //99 readonly 100 invisibile
$field13->typeofdata = 'V~O';
$block1->addField($field13); 

/** Data Importazione Semiramis
Al primo salvataggio di una azienda impostare la Data Attivazione a “today”, 
campo in sola lettura, solo la prima volta importflag a false (può già essere nel DB).
 */
$field20 = new Vtiger_Field();
$field20->name = 'sem_importdate';
$field20->label= 'Data Importazione Semiramis';
$field13->columntype = 'VARCHAR(100)';
$field20->table = $module->basetable;
$field20->uitype = 1;
$field20->readonly = 99; //99 readonly 100 invisibile
$field20->typeofdata = 'V~O';
$block1->addField($field20);


SDK::setLanguageEntry('Accounts','it_it', 'Stato Importazione Semiramis' , 'Stato Importazione Semiramis');
SDK::setLanguageEntry('Accounts','en_us', 'Stato Importazione Semiramis' , 'Semiramis Import Status');
SDK::setLanguageEntry('Accounts','de_de', 'Stato Importazione Semiramis' , 'Semiramis Import Status');

SDK::setLanguageEntry('Accounts','it_it', 'Data Importazione Semiramis' , 'Data Importazione Semiramis');
SDK::setLanguageEntry('Accounts','en_us', 'Data Importazione Semiramis' , 'Semiramis Import Date');
SDK::setLanguageEntry('Accounts','de_de', 'Data Importazione Semiramis' , 'Semiramis Import Date');

?>
