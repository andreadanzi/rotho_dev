<?php
include_once("lib/utils.inc.php");
require("lib/GeoCoder.inc.php");
global $dbconfig;
global $app_strings;
define("DB_HOST",$dbconfig['db_server']);
define("DB_USER",$dbconfig['db_username']);
define("DB_PASS",$dbconfig['db_password']);
define("DB_NAME",$dbconfig['db_name']);
$connection = mysql_connect(DB_HOST, DB_USER, DB_PASS) or die("Error on database connection: ".mysql_error()."<br/>");
$db_selected = mysql_select_db(DB_NAME, $connection) or die("Error on database selection: ".mysql_error()."<br/>");
$result = mysql_query("delete from vtiger_map where mapid = ".$_REQUEST['id']);
$gc = new GeoCoder();
getResults($_REQUEST['show'],$_REQUEST['id']);

header("Location: index.php?module=Map&action=index&show=".$_REQUEST['show']);
?>
