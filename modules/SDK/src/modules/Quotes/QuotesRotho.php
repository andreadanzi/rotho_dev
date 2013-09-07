<?
require_once('modules/Quotes/Quotes.php');
class QuotesRotho extends Quotes {
	var $list_fields = Array(
				//'Quote No'=>Array('crmentity'=>'crmid'),
				// Module Sequence Numbering
				'Data creazione'=>Array('quotes'=>'date_create_quote'),
				'Subject'=>Array('quotes'=>'subject'),
				'Quote Stage'=>Array('quotes'=>'quotestage'), 
				'Tipo Offerta'=>Array('quotes'=>'quote_type'), 
				'Stato preventivi'=>Array('quotes'=>'quote_status'), 
				'Total'=>Array('quotes'=>'total'),
				'Assigned To'=>Array('crmentity'=>'smownerid')				
				);
	
	var $list_fields_name = Array(
				'Data creazione'=>'date_create_quote',					
		        'Subject'=>'subject',
		        'Quote Stage'=>'quotestage',
				'Tipo Offerta'=>'quote_type',
				'Stato preventivi'=>'quote_status',
				'Total'=>'hdnGrandTotal',
				'Assigned To'=>'assigned_user_id'	        
				      );
	var $default_order_by = 'date_create_quote';
	var $default_sort_order = 'DESC';
}
?>