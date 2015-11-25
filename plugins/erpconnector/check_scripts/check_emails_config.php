<?php
require("../config.php");
require_once("check_emails_functions.php");
$log_active = true;

/* modulo da importare: */
$module = "Check Emails";
$days_detail = -1;
$days_summary = -7;

$from = "laura@rothoblaas.com";
$to = "manuel.barbetta@rothoblaas.com,elisabeth.pojer@rothoblaas.com";
$cc = "help@danzi.tn.it";
$subject = "ROTHO - Email di Prova";

?>
