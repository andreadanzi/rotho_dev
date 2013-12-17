<?
include_once("Contacts_functions.php");
include_once("../config.php");
$log_active = true;
//modulo da importare:
$module = 'Contacts';
//array mappaggio campi: nome campo tabella di appoggio => fieldname di vtiger
$mapping = Array(
'PERSON_PARENT'=>'account_id',
'PERSON_PARENT1'=>'assigned_user_id', //campo ghost
'PERSON_NUMBER'=>'ext_code',
'PERSON_FULLNAME'=>'lastname',
'PERSON_JOBDESC'=>'title',
'PERSON_CITY'=>'mailingcity',
'PERSON_POSTALCODE'=>'mailingcode',
'PERSON_COUNTRY'=>'mailingcountry',
'PERSON_REGION'=>'mailingstate',
'PERSON_STREET'=>'mailingstreet',
'PERSON_EMAILCUSTOMERINVOICE'=>'cf_1014',
'PERSON_EMAILCONFIRMATIONORDER'=>'cf_1015',
'PERSON_EMAILDELIVERYSLIP'=>'cf_1016',
'PERSON_EMAILDUNNING'=>'cf_1017',
'PERSON_EMAILTRACKING'=>'cf_1018',
'INSERTDATE'=>'cf_1170',
);
//campo nella tabella di appoggio per identificare il codice esterno (sul quale l'import effettuerà la creazione/aggiornamento dei dati)
$external_code = 'PERSON_NUMBER';
//tabella di appoggio
$table = "erp_temp_crm_contatti";
//condizioni sulla tabella di appoggio
$where = '';

//extra info
$table_info = "erp_temp_crm_recapiti_contatti";
//extra info key
$external_code_info = 'contact_parent';
//campi di default in creazione
$fields_auto_create['vtiger_crmentity']['smownerid'] = 1;
$fields_auto_create['vtiger_crmentity']['modifiedby'] = 1;
$fields_auto_create['vtiger_crmentity']['smcreatorid'] = 1;
//campi di default in aggiornamento
$fields_auto_update['vtiger_crmentity']['modifiedby'] = 1;
?>
