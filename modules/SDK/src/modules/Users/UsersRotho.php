<?php
include_once('modules/Users/Users.php');
class UsersRotho extends Users{
	function __construct() {
		global $crmv;
		if ($crmv){
			$this->time_to_change_password = 0;
		}
		parent::__construct();
	}	
	function doLogin($user_password){
		global $crmv;
		if ($crmv){
			$query = "SELECT * from $this->table_name where user_name=?";
			$result = $this->db->requirePsSingleResult($query, array($this->column_fields["user_name"]), false);
			if (empty($result)) {
				return false;
			} else {
				return true;
			}				
		}
		else{
			return parent::doLogin($user_password);		
		}		
	}
}
?>