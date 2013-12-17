<?php
include_once("Quotes_functions.php");
include_once("../config.php");
$log_active = true;
//modulo da importare:
$module = 'Quotes';
//array mappaggio campi: nome campo tabella di appoggio => fieldname di vtiger
$mapping = Array(
'PROPOSAL_NUMBER'=>'subject',
'PROPOSAL_STATUS'=>'quote_status',
'PROPOSAL_DATE'=>'date_create_quote',
'AGENT_NUMBER'=>'assigned_user_id', 
//'PROPOSAL_TYPE'=>'quote_type', 
'CUSTOMER_NUMBER'=>'account_id',
'CLASSIFICATION1_PATH'=>'classification1_path',

'TOTAL'=>'hdnGrandTotal', 
'SUBTOTAL'=>'hdnSubTotal',
//danzi.tn 1.12.2012:
'DELIVERY_ADDRESS'=>'ship_street',
'DELIVERY_POSTALCODE'=>'ship_code',
'DELIVERY_CITY'=>'ship_city',
'DELIVERY_REGION'=>'ship_state',
'DELIVERY_COUNTRY'=>'ship_country',
'RESPONSIBLE_NAME'=>'cf_872',
'RESPONSIBLE_NUMBER'=>'cf_915',
'DELIVERY_NAME'=>'cf_863',
'CLASSIFICATION1_DESCRIPTION'=>'cf_874',
//dazni.tn fine
'PROPOSAL_TYPE'=>'cf_866', 
// PER I PRODOTTI -- COMMENTATO XK VIENE FATTO NELLA CLASSES
//'DETAIL_NUMBER'=>'sequence_no',
//'DETAIL_ITEM'=>'productid',
//'DETAIL_QUANTITY'=>'quantity',
//'DETAIL_NETPRICE'=>'listprice',
);
//campo nella tabella di appoggio per identificare il codice esterno (sul quale l'import effettuerà la creazione/aggiornamento dei dati)
$external_code = 'PROPOSAL_NUMBER';
//tabella di appoggio - per test:
$table = "erp_temp_crm_customerproposal"; 
//condizioni sulla tabella di appoggio
//$where = 'where PROPOSAL_NUMBER=\'OS12002088\'';

//campi di default in creazione
//$fields_auto_create['vtiger_crmentity']['smownerid'] = 1;
$fields_auto_create['vtiger_crmentity']['modifiedby'] = 1;
$fields_auto_create['vtiger_crmentity']['smcreatorid'] = 1;	
//campi di default in aggiornamento
$fields_auto_update['vtiger_crmentity']['modifiedby'] = 1;
?>