<?
include_once('modules/SalesOrder/SalesOrder.php');
class SalesOrderRotho extends SalesOrder {
	
	var $list_fields = Array(
				// Module Sequence Numbering
				//'Order No'=>Array('crmentity'=>'crmid'),
				'Data Ordine'=>Array('salesorder','data_ordine_ven'),
				'Subject'=>Array('salesorder','subject'),
				'Status'=>Array('salesorder','sostatus'),
				'Total'=>Array('salesorder','total'),
				'Assigned To'=>Array('crmentity'=>'smownerid')
				);
	
	var $list_fields_name = Array(
				'Data Ordine'=>'data_ordine_ven',
				'Subject'=>'subject',
				'Status'=>'sostatus',
				'Total'=>'hdnGrandTotal',
				'Assigned To'=>'assigned_user_id'
				);
				
	var $default_order_by = 'data_ordine_ven';
	var $default_sort_order = 'DESC';
								
}
?>