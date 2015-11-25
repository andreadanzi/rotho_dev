<?php
include_once 'include/Zend/Json.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'include/utils/VtlibUtils.php';
include_once 'include/Webservices/Create.php';
include_once 'include/QueryGenerator/QueryGenerator.php';
require_once('modules/Users/Users.php');
require_once('include/utils/utils.php');
require_once('modules/Emails/mail.php');
require_once('modules/Accounts/Accounts.php');

function do_check_emails($time_start) {
	global $log_active, $adb, $days_detail, $days_summary, $from , $to , $subject, $cc;
    send_mail('Emails',$to ,'ROTHO BLAAS',$from ,$subject,"Questo è un messaggio di prova inviato in automatico dal CRM per conto di ".$from." con destinatari ".$to. " e in copia a ". $cc.". ",$cc,'');
}


?>
