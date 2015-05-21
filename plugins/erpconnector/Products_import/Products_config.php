<?php
require("../config.php");
require_once("Products_functions.php");
$log_active = true;
// danzi.tn@20150429 nuovo campo Responsabile strategico di prodotto
//$limit = " LIMIT 500";

//modulo da importare:
$module = 'Products';

//array mappaggio campi: nome campo tabella di appoggio => fieldname di vtiger
$mapping = Array(
'BASE_NUMBER'=>'productname',
'BASE_NO'=>'base_no',
'BASE_DESCRIPTION'=>'description',
'SALES_ACTIVE'=>'flag_erp',
'SALES_DATEFROM'=>'sales_start_date',
'SALES_DATEUNTIL'=>'sales_end_date',
'SALES_CLASSIFICATION'=>'cf_803',
'BASE_DELETED'=>'discontinued',
'BASE_UOM'=>'base_uom',
'SUPPLIER_NUMBER'=>'cf_1116',
'ABC_CLASSIFICATION'=>'cf_1166',
'RESPONSIBLE_NUMBER'=>'product_resp_no',
'RESPONSIBLE_NAME'=>'product_resp_name',

);
//campo nella tabella di appoggio per identificare il codice esterno (sul quale l'import effettuerà la creazione/aggiornamento dei dati)
$external_code = 'BASE_NUMBER';

//tabella di appoggio
$table = "erp_temp_crm_articoli";
//			INNER JOIN vtiger_crmentity
//				ON vtiger_products.productid = vtiger_crmentity.crmid
//			INNER JOIN vtiger_productcf
//				ON vtiger_products.productid = vtiger_productcf.productid
//			LEFT JOIN vtiger_vendor
//				ON vtiger_vendor.vendorid = vtiger_products.vendor_id
//			LEFT JOIN vtiger_users
//				ON vtiger_users.id = vtiger_products.handler";

//condizioni sulla tabella di appoggio
//$where = 'WHERE deleted = 0'.$limit;
//campi di default in creazione
//$fields_auto_create['vtiger_crmentity']['smownerid'] = 1;
//$fields_auto_create['vtiger_crmentity']['modifiedby'] = 1;
//$fields_auto_create['vtiger_crmentity']['smcreatorid'] = 1;	
//campi di default in aggiornamento
//$fields_auto_update['vtiger_crmentity']['modifiedby'] = 1;


?>
