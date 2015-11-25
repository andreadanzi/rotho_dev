<?php
include_once("SalesOrder_functions.php");
include_once("../config.php");
$log_active = true;
//modulo da importare:
$module = 'SalesOrder';
//array mappaggio campi: nome campo tabella di appoggio => fieldname di vtiger
$mapping = Array(
'ORDINE_NUMERO'=>'salesorder_no', //da replicare x3
'MESE'=>'mese_ordine',//ok
'CLIENTE_DESTINAZIONE'=>'cliente_dest_order',//ok
'CLIENTE_FATTURAZIONE'=>'customerno', //ok
'CLIENTE_FATT_AZ'=>'account_id', //ok danzi.tn@20150715 deve essere account_id, uguale al nome del campo e non al nome della colonna!! Per SalesOrder il nome colonna è accountid
'CLIENTE_FATT_ASS'=>'assigned_user_id', //ok
'ORDINE_DATA'=>'data_ordine_ven',//ok
'ORDINE_TIPO'=>'tipo_ordine',//ok
'ORDINE_NUMERO_SOGG'=>'subject', //da replicare x3
'ORDINE_NUMERO_KEY'=>'no_order_key', //da replicare x3
'DATA_NOTA'=>'data_nota',
//campi non esistenti ma da importare o impostare
'TOTAL'=>'hdnGrandTotal',
'SUBTOTAL'=>'hdnSubTotal',
'STATO'=>'sostatus',
//////////////////////////////////////////////
// per i prodotti
//'ORDINE_RIGA'=>'rating',order by
//'ARTICOLO_CODE'=>'productid',
//'QUANTITA'=>'quantity',
//'FATTURATO_NETTO'=>'listprice',
);
//campo nella tabella di appoggio per identificare il codice esterno (sul quale l'import effettuerà la creazione/aggiornamento dei dati)
$external_code = 'ORDINE_NUMERO_KEY';
//tabella di appoggio
$table = "erp_temp_crm_ordini";
//condizioni sulla tabella di appoggio
$where = " where MESE LIKE '2015%' ";
#$where = " WHERE mese  >= CONVERT(varchar,YEAR(GETDATE()))+right('00'+convert(varchar,MONTH(DATEADD(month,-6,GETDATE()))),2) ";

//campi di default in creazione
//$fields_auto_create['vtiger_crmentity']['smownerid'] = 1;
//$fields_auto_create['vtiger_salesorders']['sostatus'] = 'Consegnato';
$fields_auto_create['vtiger_crmentity']['modifiedby'] = 1;
$fields_auto_create['vtiger_crmentity']['smcreatorid'] = 1;	
//campi di default in aggiornamento
$fields_auto_update['vtiger_crmentity']['modifiedby'] = 1;
?>
