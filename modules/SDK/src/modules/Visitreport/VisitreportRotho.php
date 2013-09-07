<?
require_once('modules/Visitreport/Visitreport.php');
class VisitreportRotho extends Visitreport {
	
	var $list_fields = Array (
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'visitdate'=> Array('visitreport', 'visitdate'),
		'visitreportname'=> Array('visitreport', 'visitreportname'),
		'scopovisit' => Array('visitreport', 'scopovisit'),
		'visitnote' => Array('visitreport', 'visitnote'),
		'Assigned To' => Array('crmentity','smownerid')
	);
	var $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'visitdate'=> 'visitdate',
		'visitreportname' => 'visitreportname',
		'scopovisit' => 'scopovisit',
		'visitnote' => 'visitnote',
		'Assigned To' => 'assigned_user_id'
	);
	
	var $default_order_by = 'visitdate';
	var $default_sort_order='DESC';
}
?>