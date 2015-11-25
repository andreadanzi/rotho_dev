<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix,$default_charset;
session_start();
// html_entity_decode($description, ENT_NOQUOTES, $default_charset);
// htmlentities( , ENT_NOQUOTES, $default_charset);
//Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');
//danzi.tn@20150608 traduzioni classificazione ||
$row = 1;
if (($handle = fopen("/var/www/modules/SDK/traduzioni_numero_pt_br.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $num = count($data);
        SDK::setLanguageEntry("Accounts","pt_br",$data[0],$data[2]);
        SDK::setLanguageEntry("Contacts","pt_br",$data[0],$data[2]);
        SDK::setLanguageEntry("APP_STRINGS","pt_br",$data[0],$data[2]);
        $row++;

    }
    fclose($handle);
}
?>
