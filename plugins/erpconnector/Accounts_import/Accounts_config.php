<?php
include_once("Accounts_functions.php");
include_once("../config.php");
$log_active = true;
//modulo da importare:
$module = 'Accounts';
//array mappaggio campi: nome campo tabella di appoggio => fieldname di vtiger
$mapping = Array(
'BASE_NAME'=>'accountname',
'CUSTOMER_GROUP'=>'account_id',
'CUSTOMER_STATUS'=>'rating',
'AGENT_NUMBER'=>'assigned_user_id',
'BASE_NUMBER'=>'external_code',
'CUSTOMER_BLOCCOAVVOCATO'=>'blocco_avvocato',
'FINANCE_TAXIDCEE'=>'cf_753',
'FINANCE_LOCALTAXID'=>'cf_751',
'FINANCE_SUPPLTAXID'=>'cf_750',
'CUSTOMER_ZONE__DESC'=>'zone',
'CUSTOMER_TYPEDESC'=>'accounttype',
//'CUSTOMER_CATEGORY__DESC'=>'categoria_cli',
'CUSTOMER_CATEGORY__DESC'=>'cf_762',
'CUSTOMER_PRICE__DESC'=>'condizioni_prezzo',
'FINANCE_BLOCCORATING'=>'blocco_rating',
'FINANCE_PAYMENTTERMS__DESC'=>'condizioni_pag',
'BASE_CITY'=>'bill_city',
'BASE_POSTACODE'=>'bill_code',
'BASE_COUNTRY'=>'bill_country',
'BASE_REGION'=>'bill_state',
'BASE_STREET'=>'bill_street',
'AGENT_NUMBER1'=>'agent_number',
'AGENT_NAME'=>'agent_name',
'AREAMANAGER_NUMBER'=>'area_mng_no',
'AREAMANAGER_NAME'=>'area_mng_name',
'FINANCE_RATINGCLIENTE'=>'cf_833', //campo ghost
'AGENT2_NUMBER'=>'cf_1013', //Secondo agente  danzi.tn
'FINANCE_BIC'=>'cf_1062', //BIC CODE  danzi.tn@20130516 SE NONT NULL SOVRASCRIVERE
'FINANCE_IBAN'=>'cf_752', //IBAN CODE  danzi.tn@20130516 SE NONT NULL SOVRASCRIVERE
'BASE_LANGUAGE'=>'cf_1113', //Lingua base danzi.tn@20130516 SE DESTINAZIONE NON c'è, allora sovrascrivi
//
'BASE_RESPONSIBLE_NUMBER' => 'codice_vendite_int',
'BASE_RESPONSIBLE_NAME' => 'ref_vendite_int',
'FINANCE_UNPAIDVALUE' => 'cf_1171',
'FINANCE_EXPIREDUNPAIDVALUE'=>'cf_1172',
'BASE_CRMNUMBER'=>'cf_1180',
);
//campo nella tabella di appoggio per identificare il codice esterno (sul quale l'import effettuerà la creazione/aggiornamento dei dati)
$external_code = 'BASE_NUMBER';
//tabella di appoggio
$table = "erp_temp_crm_aziende";
//condizioni sulla tabella di appoggio
$where = 'order by customer_type,customer_group';

//extra info
$table_info = "erp_temp_crm_recapiti_aziende";
//extra info key
$external_code_info = 'contact_parent';

//campi di default in creazione
$fields_auto_create['vtiger_crmentity']['smownerid'] = 1;
$fields_auto_create['vtiger_crmentity']['modifiedby'] = 1;
$fields_auto_create['vtiger_crmentity']['smcreatorid'] = 1;	
//campi di default in aggiornamento
$fields_auto_update['vtiger_crmentity']['modifiedby'] = 1;
?>
