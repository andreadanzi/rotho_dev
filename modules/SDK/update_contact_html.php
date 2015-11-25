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
//danzi.tn@20151111 HTML entities nei contatti

$sql = "select c.contactid, c.firstname, c.lastname, c.contact_no, c.ext_code  from
vtiger_contactdetails c
where c.ext_code = '";
$row = 1;
// file csv 0=Partner|1=Nome completo|2=Nome|3=Cognome
if (($handle = fopen("/var/www/modules/SDK/contatti_html.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, "|")) !== FALSE) {
        $num = count($data);
        $ext_code = $data[0];
        $nome = $data[2];
        $cognome = $data[3];
        // echo "<p> $num - $ext_code - ".utf8_decode (html_entity_decode($nome, ENT_COMPAT , 'UTF-8'))." ".utf8_decode (html_entity_decode($cognome, ENT_COMPAT , 'UTF-8')) ."</p>\n";
        $row++;
        $res = $adb->query("UPDATE vtiger_contactdetails SET 
                            firstname='".html_entity_decode($nome, ENT_COMPAT , 'UTF-8')."', 
                            lastname='". html_entity_decode($cognome, ENT_COMPAT , 'UTF-8') ."' 
                            WHERE ext_code = '" . $ext_code ."'" );
        $res = $adb->query("UPDATE vtiger_crmentity
                            SET 
                            vtiger_crmentity.modifiedtime = GETDATE() ,
                            vtiger_crmentity.modifiedby = 132902
                            from vtiger_crmentity
                            JOIN vtiger_contactdetails on vtiger_contactdetails.contactid = vtiger_crmentity.crmid 
                            AND vtiger_contactdetails.ext_code = '" . $ext_code ."'");

        echo $row."\n";
    }
    fclose($handle);
}
?>
