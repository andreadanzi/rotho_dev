<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix;
session_start();
// danzi.tn@20130905
// Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');
// Retrieve module instance 
$srcdir = 'modules/SDK/src/';
$consulenza = Vtiger_Module::getInstance('Consulenza');

$potentials = Vtiger_Module::getInstance('Potentials');
$potentials->setRelatedList($consulenza, 'Consulenza', Array('ADD','SELECT'));

SDK::setLanguageEntry('Potentials','en_us', 'Consulenze' , 'Technicalm Advices');
SDK::setLanguageEntry('Potentials','it_it','Consulenze' , 'Consulenze');
// danzi.tn@20130905

?>
