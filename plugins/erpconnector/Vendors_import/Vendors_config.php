<?php
require_once("Vendors_functions.php");
require("../config.php");
$log_active = true;
//modulo da importare:
$module = 'Vendors';
//array mappaggio campi: nome campo tabella di appoggio => fieldname di vtiger
$mapping = Array(
'SUPPLIER_NAME'=>'vendorname',
'SUPPLIER_DESCR'=>'description',
'SUPPLIER_NUMBER'=>'cf_1115',
'BASE_STREET'=>'street',
'BASE_POSTACODE'=>'postalcode',
'BASE_CITY'=>'city',
'BASE_COUNTRY'=>'country',
'BASE_REGION'=>'state',
'FINANCE_TAXIDCEE'=>'vendor_vat_code',
'FINANCE_SUPPLTAXID'=>'vendor_fiscal_code',
// danzi.tn@20140602 DEFAULT VENDOR_TYPE = Merce conto vendita e VENDOR_STATUS = Attivo
'VENDOR_TYPE'=>'cf_1118',
'VENDOR_STATUS'=>'cf_1117',
// 'FINANCE_PAYMENTTERMS'=>'',
// 'FINANCE_PAYMENTTERMSDESC'=>'',
// 'FINANCE_RATINGFORNITORE'=>'vendor_rating'
);
//campo nella tabella di appoggio per identificare il codice esterno (sul quale l'import effettuerà la creazione/aggiornamento dei dati)
$external_code = 'SUPPLIER_NUMBER';
//tabella di appoggio
$table = "erp_temp_crm_fornitori";
$where = 'order by SUPPLIER_NUMBER,SUPPLIER_NAME';

//extra info
$table_info = "erp_temp_crm_recapiti_fornitori";
//extra info key
$external_code_info = 'contact_parent';
//campi di default in creazione
$fields_auto_create['vtiger_crmentity']['smownerid'] = 1;
$fields_auto_create['vtiger_crmentity']['modifiedby'] = 1;
$fields_auto_create['vtiger_crmentity']['smcreatorid'] = 1;	
//campi di default in aggiornamento
$fields_auto_update['vtiger_crmentity']['modifiedby'] = 1;

?>
