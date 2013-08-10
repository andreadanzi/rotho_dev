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
// Retrieve module instance 
$srcdir = 'modules/SDK/src/';
$module = Vtiger_Module::getInstance('Accounts');
Vtiger_Link::addLink($module->id,'HEADERSCRIPT','uitype616jQueryScript',$srcdir.'uitypejQuery/616_functions.js');
Vtiger_Link::addLink($module->id,'HEADERCSS','uitype616jQueryCSS',$srcdir.'uitypejQuery/616.css');
SDK::setExtraSrc($module, $srcdir.'uitypejQuery/616_functions.js');
SDK::setLanguageEntry('Accounts','it_it','Download' , 'Download');
SDK::setLanguageEntry('Accounts','en_us', 'Download' , 'Download');
SDK::setLanguageEntry('Accounts','it_it','Corsi' , 'Corsi');
SDK::setLanguageEntry('Accounts','en_us', 'Corsi' , 'Courses');
SDK::setLanguageEntry('Accounts','it_it','Consulenze' , 'Consulenze');
SDK::setLanguageEntry('Accounts','en_us', 'Consulenze' , 'Technicalm Advices');
SDK::setLanguageEntry('Accounts','it_it','Affiliazione' , 'Affiliazioni');
SDK::setLanguageEntry('Accounts','en_us', 'Affiliazione' , 'Affiliation');
SDK::setLanguageEntry('Accounts','it_it','Opportunita' , 'Opportunit&agrave;');
SDK::setLanguageEntry('Accounts','en_us', 'Opportunita' , 'Potentials');


?>
