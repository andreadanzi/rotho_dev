<?php
include_once('config.inc.php');
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
//danzi.tn@20151109 verifica traduzioni
// QUERY DI SELEZIONE SU CRM_ACCOUNT_MAIN_ACTIVITY_QLIK PER TEDESCO SU ALCUNI TERMINI CHE HANNO CARATTERI TEDESCHI
$query = "SELECT  MA.account_main_activity, MA.language, MA.trans_label  
            FROM CRM_ACCOUNT_MAIN_ACTIVITY_QLIK MA 
            WHERE 1=1
            AND MA.language ='de_de' 
            AND MA.account_main_activity IN ('ARCH.','CASE IN LEGNO','DISTRIBUTORE SERRAMENTI','EDIFICI IN LEGNO','GROSSISTA','GDO') ";
$res = $adb->query($query);
// STAMPO IN TABELLA VALORI GREZZI CODIFICATI SECONDO UTF-8
echo "<H1>VALORI GREZZI UTF8</H1>";
echo "<table>";
// INTESTAZIONE
echo "<tr><th>VALORE</th><th>LINGUA</th><th>TRADUZIONE GREZZA</th></tr>";
if($res && $adb->num_rows($res) > 0){
			while($row=$adb->fetchByAssoc($res,-1,false)){
                echo "<tr><td>".$row['account_main_activity']."</td><td>".$row['language']."</td><td>".$row['trans_label']."</td></tr>";
            }
}
echo "</table>";
$adb->query("DELETE FROM erp_tmp_account_main_activity_translations" );
// ESEGUO LA STESSA QUERY DI SELEZIONE DI PRIMA MA MOSTRO LE TRADUZIONI IN ISO-8859-1 APPLICANDO LA DECODIFICA (VEDI utf8_decode)
$res = $adb->query($query);
echo "<H1>TRADUZIONI DA UTF-8 A single-byte ISO-8859-1</H1>";
echo "<table>";
// INTESTAZIONE
echo "<tr><th>VALORE</th><th>LINGUA</th><th>TRADUZIONE ENCODED</th></tr>";
if($res && $adb->num_rows($res) > 0){
			while($row=$adb->fetchByAssoc($res,-1,false)){
                echo "<tr><td>".$row['account_main_activity']."</td><td>".$row['language']."</td><td>".utf8_decode($row['trans_label'])."</td></tr>";
                $sInsert = "INSERT INTO erp_tmp_account_main_activity_translations (account_main_activity,language,trans_label) VALUES ('".$row['account_main_activity']."','".$row['language']."','".utf8_decode($row['trans_label'])."')";
                $adb->query($sInsert);
            }
}
echo "</table>";

?>
