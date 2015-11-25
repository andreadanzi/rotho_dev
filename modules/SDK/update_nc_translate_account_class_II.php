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
if (($handle = fopen("/var/www/__test/modules/SDK/traduzioni_class_II_2050608.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $num = count($data);
        SDK::setLanguageEntry("Accounts","de_de",$data[0],$data[2]);
        SDK::setLanguageEntry("Accounts","en_us",$data[0],$data[3]);
        SDK::setLanguageEntry("Accounts","fr_fr",$data[0],$data[4]);
        SDK::setLanguageEntry("Accounts","es_es",$data[0],$data[5]);
        SDK::setLanguageEntry("Accounts","pt_br",$data[0],$data[6]);
        SDK::setLanguageEntry("Accounts","ru_ru",$data[0],$data[3]);

        SDK::setLanguageEntry("APP_STRINGS","de_de",$data[0],$data[2]);
        SDK::setLanguageEntry("APP_STRINGS","en_us",$data[0],$data[3]);
        SDK::setLanguageEntry("APP_STRINGS","fr_fr",$data[0],$data[4]);
        SDK::setLanguageEntry("APP_STRINGS","es_es",$data[0],$data[5]);
        SDK::setLanguageEntry("APP_STRINGS","pt_br",$data[0],$data[6]);
        SDK::setLanguageEntry("APP_STRINGS","ru_ru",$data[0],$data[3]);
        $row++;

    }
    fclose($handle);
}
?>
