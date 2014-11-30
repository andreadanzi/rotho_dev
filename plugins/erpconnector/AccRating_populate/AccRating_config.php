<?php
require("../config.php");
require_once("AccRating_functions.php");
$log_active = false;

/* modulo da importare:
$module = 'Account Ratings';
$ratingField = 'cf_891'; // dev=>cf_891 rotho_prod=>cf_927
$codiceCorsoTargetField = 'cf_887'; // dev=>cf_887 rotho_prod=>cf_1006
$codiceCorsoCampagnaField = 'cf_886'; // dev=>cf_886 rotho_prod=>cf_742
$codiceFatturazioneCorsoField = 'cf_892'; // dev=>cf_892 rotho_prod=>cf_759
$codiceCategoriaField = 'cf_799'; // dev=>cf_799 rotho_prod=>cf_762
$tipoAffiliazioneField = 'cf_893'; // dev=>cf_893 rotho_prod=>cf_1178
*/
$temp_table = "temp_acc_ratings";
$ratingField = 'cf_927'; // dev=>cf_891 rotho_prod=>cf_927
$codiceCorsoTargetField = 'cf_1006'; // dev=>cf_887 rotho_prod=>cf_1006
$codiceCorsoCampagnaField = 'cf_742'; // dev=>cf_886 rotho_prod=>cf_742
$dataCorsoCampagnaField = 'cf_745'; // dev=>cf_886 rotho_prod=>cf_742
$codiceFatturazioneCorsoField = 'cf_759'; // dev=>cf_892 rotho_prod=>cf_759
//danzi.tn@20141126 nuova classificazione
// $codiceCategoriaField = 'cf_762'; // dev=>cf_799 rotho_prod=>cf_762 
//danzi.tn@20141126 nuova classificazione
$tipoAffiliazioneField = 'cf_1178'; // dev=>cf_893 rotho_prod=>cf_1178

$map_corsi=array();
$map_corsi["RFCBC"] = "Corso base di carpenteria";
$map_corsi["RFCAC"] = "Corso avanzato di carpenteria";
$map_corsi["RFCACN"] = "Corso avanzato di progettazione delle connessioni per strutture di legno";
$map_corsi["RFCAPC"] = "Corso avanzato di progettazione per edificidi legno: statica, sismica e cantiere";
$map_corsi["RSCAP"] = "Corso di progettazione di sistemi anticaduta";
$map_corsi["RSCA"] = "Corso per installatori qualificati di sistemi anticaduta";
$map_corsi["RSCBDPI"] = "Corso per l'utilizzo di dispositivi di protezione individuale contro le cadute dall'alto e sistemi di salvataggio";
$map_corsi["RSCB"] = "Corso per l'utilizzo di dispositivi di protezione individuale contro le cadute dall'alto e sistemi di salvataggio";
$map_corsi["RHCB"] = "Corso base per applicatori";
$map_corsi["RHCA"] = "Corso per progettisti";
$map_corsi["RHCT"] = "Corso per tecnici di impresa e direttori lavori";
$map_corsi["RBFCACM"] = "Curso avanzado de conexiones en madiera";
$map_corsi["RHCI"] = "Corso di formazione teoricao/pratica Intego";
$map_corsi["ND"] = "Download";

?>
