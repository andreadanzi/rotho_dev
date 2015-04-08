<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix;
session_start();
// danzi.tn@20150408 introduzione Fatturato anno precendente e Fatturato anno precedente -1
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
$field13->name = 'last_annual_revenue';
$field13->table = $module->basetable;
$field13->label= 'Fatturato anno precedente';
$field13->columntype = 'VARCHAR(100)';
$field13->uitype = 71;
$field13->readonly = 99; //99 readonly 100 invisibile
$field13->typeofdata = 'I~O';
$block1->addField($field13); 

/** Fatturato anno precedente - 1
 */
$field20 = new Vtiger_Field();
$field20->name = 'pre_last_annual_revenue';
$field20->label= 'Fatturato anno precedente - 1';
$field20->columntype = 'VARCHAR(100)';
$field20->table = $module->basetable;
$field20->uitype = 71;
$field20->readonly = 99; //99 readonly 100 invisibile
$field20->typeofdata = 'I~O';
$block1->addField($field20);


SDK::setLanguageEntry('Accounts','it_it', 'Fatturato anno precedente' , 'Fatturato anno precedente');
SDK::setLanguageEntry('Accounts','en_us', 'Fatturato anno precedente' , 'Last Annual Revenue');
SDK::setLanguageEntry('Accounts','de_de', 'Fatturato anno precedente' , 'Last Annual Revenue');

SDK::setLanguageEntry('Accounts','it_it', 'Fatturato anno precedente - 1' , 'Fatturato anno precedente - 1');
SDK::setLanguageEntry('Accounts','en_us', 'Fatturato anno precedente - 1' , 'Last Annual Revenue -1');
SDK::setLanguageEntry('Accounts','de_de', 'Fatturato anno precedente - 1' , 'Last Annual Revenue -1');
?>
