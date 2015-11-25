<?php
require("../config.php");
require_once("check_duplicates_functions.php");
$log_active = true;

/* modulo da importare: */
$module = "Check Account Duplicates";
$days_detail = -1;
$days_summary = -7;

$from = "crm@rothoblaas.com";
$to = "elisabeth.pojer@rothoblaas.com";
$cc = "help@danzi.tn.it,laura@rothoblaas.com,";
$subject = "CHECK CLIENTS+ Duplicated Accounts";

?>
