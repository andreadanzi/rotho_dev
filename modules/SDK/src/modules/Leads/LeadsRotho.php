<?
require_once('modules/Leads/Leads.php');
class LeadsRotho extends Leads {
	
	var $list_fields = Array(
		'Created Time'=>Array('crmentity'=>'createdtime'),
		'Last Name'=>Array('leaddetails'=>'lastname'),
		'First Name'=>Array('leaddetails'=>'firstname'),
		'Company'=>Array('leaddetails'=>'company'),
		'Lead Source'=>Array('leaddetails'=>'leadsource'),
		'Assigned To'=>Array('crmentity'=>'smownerid'),
		'Inviata eMail conferma'=>Array('leadscf'=>'cf_809')
	);
	var $list_fields_name = Array(
		'Created Time' => 'createdtime',
		'Last Name'=>'lastname',
		'First Name'=>'firstname',
		'Company'=>'company',
		'Lead Source'=>'leadsource',
		'Assigned To'=>'assigned_user_id',
		'Inviata eMail conferma'=>'cf_809'		
	);
	
	var $default_order_by = 'createdtime';
	var $default_sort_order = 'DESC';

}
?>