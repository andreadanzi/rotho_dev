<? 
require_once('modules/HelpDesk/HelpDesk.php');
class HelpDeskRotho extends HelpDesk {
	
	var $list_fields = Array(
					//Module Sequence Numbering
					//'Ticket ID'=>Array('crmentity'=>'crmid'),
					'Created Time'=>Array('crmentity'=>'createdtime'),
					'Subject'=>Array('troubletickets'=>'title'),
					'Ticket No'=>Array('troubletickets'=>'ticket_no'),
					'Description'=>Array('crmentity'=>'description'),
					'Status'=>Array('troubletickets'=>'status'),
					'Assigned To'=>Array('crmentity'=>'smownerid')
				);

	var $list_fields_name = Array(
					//'Ticket ID'=>'',
					'Created Time'=>'createdtime',
					'Subject'=>'ticket_title',
					'Ticket No'=>'ticket_no',					
					'Description'=>'description',	  			
					'Status'=>'ticketstatus',					
					'Assigned To'=>'assigned_user_id'
				     );
	
	var $default_order_by = 'createdtime';
    var $default_sort_order = 'DESC';

}
?>