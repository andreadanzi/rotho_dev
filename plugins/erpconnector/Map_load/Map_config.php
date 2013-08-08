<?
include_once("Map_functions.php");
include_once("../config.php");
require('modules/Map/lib/GeoCoder.inc.php');
$log_active = true;
$module = 'Map';
//modulo da cui selezionare gli indirizzi
$main_module = 'Accounts';
// numero di righe da processare ogni giorno (attenzione geocoder di Google accetta max 2500/giorno
$geocoder_max_num_rows = 400;
// Delay iniziale tra una richiesta a geocoder e l'altra
$geocoder_delay = 500000;
$csv_file_location = 'modules/Map/data/missing_locations.csv';

global $dbconfig; 
define("DB_HOST",$dbconfig['db_server']);
define("DB_USER",$dbconfig['db_username']);
define("DB_PASS",$dbconfig['db_password']);
define("DB_NAME",$dbconfig['db_name']);
define("DB_PORT",$dbconfig['db_port']);
?>
