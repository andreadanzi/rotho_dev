<?
require_once('modules/Contacts/Contacts.php');
class ContactsRotho extends Contacts {
/*
	var $list_fields = Array(
	'Last Name' => Array('contactdetails'=>'lastname'),
	'First Name' => Array('contactdetails'=>'firstname'),
	'Title' => Array('contactdetails'=>'title'),
	'Account Name' => Array('account'=>'accountid'),
	'Email' => Array('contactdetails'=>'email'),
	'Office Phone' => Array('contactdetails'=>'phone'),
	'Lead Source' => Array('contactsubdetails'=>'leadsource'),
	'Assigned To' => Array('crmentity'=>'smownerid')
	);
	
	var $list_fields_name = Array(
	'Last Name' => 'lastname',
	'First Name' => 'firstname',
	'Title' => 'title',
	'Account Name' => 'account_id',
	'Email' => 'email',
	'Office Phone' => 'phone',
	'Lead Source' => 'leadsource',
	'Assigned To' => 'assigned_user_id'
	);
*/
// danzi.tn@20130718
	var $list_fields = Array(
	'Last Name' => Array('contactdetails'=>'lastname'),
	'First Name' => Array('contactdetails'=>'firstname'),
	'Title' => Array('contactdetails'=>'title'),
	'Email' => Array('contactdetails'=>'email'),
	'Fattura' => Array('vtiger_contactscf'=>'cf_1014'),
	'Ordine' => Array('vtiger_contactscf'=>'cf_1015'),
	'DDT' => Array('vtiger_contactscf'=>'cf_1016'),
	'Riceve sollecito' => Array('vtiger_contactscf'=>'cf_1017'),
	'Tracking Number' => Array('vtiger_contactscf'=>'cf_1018')
	);
	
	var $list_fields_name = Array(
	'Last Name' => 'lastname',
	'First Name' => 'firstname',
	'Title' => 'title',
	'Email' => 'email',
	'Fattura' => 'cf_1014',
	'Ordine' => 'cf_1015',
	'DDT' => 'cf_1016',
	'Riceve sollecito' => 'cf_1017',
	'Tracking Number' => 'cf_1018'
	);
}
?>