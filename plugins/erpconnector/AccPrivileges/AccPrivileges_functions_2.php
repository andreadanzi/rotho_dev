<?php

function do_job($time_start) {
	global $adb;
	$query="select
			vtiger_users.id,
			vtiger_users.user_name
			 from 
			vtiger_users
			WHERE vtiger_users.user_name in ('PERESON', 'GARCES', 'LUGARINI', 'YANNPCA', 'LUISG', 'MARCOB', 'INDIA', 'TURCHIA', 'AlessioG', 'AM0038', 'FernandoM')";
	$result = $adb->query($query);
	$count_cat = array();
	while($row=$adb->fetchByAssoc($result)) {
		$user_name = $row['user_name'];
		$user_id = $row['id'];
		$owners_id = array();
		$owners_id[] = $user_id;
		require('user_privileges/user_privileges_'.$user_id.'.php');		
		if(sizeof($current_user_groups) > 0)
		{
			foreach(  $current_user_groups as $user_group) 
			{
				$owners_id[] = $user_group;
			}
		}	 
		if(sizeof($subordinate_roles_users) > 0)
		{
			foreach($subordinate_roles_users as $roleid=>$userids)
			{
				if(in_array($recOwnId,$userids))
				{
					foreach(  $userids as $userid) 
					{
						$owners_id[] = $userid;
					}
				}
			}
		} 
		$acc_sql = "SELECT 
					vtiger_account.accountid,
					vtiger_account.account_no,
					vtiger_accountscf.cf_762 as categoria,
					vtiger_crmentity.smownerid
					FROM 
					vtiger_account
					JOIN 
					vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid and vtiger_crmentity.deleted = 0
					JOIN vtiger_accountscf on vtiger_accountscf.accountid = vtiger_account.accountid
					WHERE vtiger_crmentity.smownerid in (".implode(", ",$owners_id).") ";
		$acc_res = $adb->query($acc_sql);	
		while($acc_row=$adb->fetchByAssoc($acc_res) ) {
			$recOwnId = $acc_row['smownerid'];			
			$accountid = $acc_row['accountid'];
			$accountname = $acc_row['account_no'];
			$categoria = $acc_row['categoria'];	
			echo $user_id."|MANAGER|".$user_name."|".$accountid."|".$accountname."|".$categoria."\n";			
		} // END ACCOUNTS	
	} // END USERS
	//$import_result = $accRating->populateNow();
	return $import_result;
}

?>
