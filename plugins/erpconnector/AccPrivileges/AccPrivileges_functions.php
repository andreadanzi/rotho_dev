<?php
// danzi.tn@20141212 nuova classificazione cf_762 sostituito con vtiger_account.account_line
function do_job($time_start) {
	global $adb;
	$query="select
			vtiger_users.id,
			vtiger_users.user_name
			 from 
			vtiger_users
			WHERE vtiger_users.user_name in ('PERESON', 'GARCES', 'LUGARINI', 'YANNPCA', 'LUISG', 'MARCOB', 'INDIA', 'TURCHIA', 'AlessioG', 'AM0038', 'FernandoM','VITOCCA','PIERGIORCA','DANIELERCA','GIANLUCACA','KonradF','GiuseppeT','PIERGIORCA ','CarloA','AlessioG','ANGELOFCA ','AM0014','BALLA','PETERICA','DANIELRCA','JANISRCA')";
	$result = $adb->query($query);
	$count_cat = array();
	while($row=$adb->fetchByAssoc($result)) {
		$user_name = $row['user_name'];
		$user_id = $row['id'];
		$owners_id = array();
		$owners_id[] = $user_id;
		$adb->pquery("DELETE FROM temp_acc_users WHERE userid=?",array($user_id));
		require('user_privileges/user_privileges_'.$user_id.'.php');		
		$acc_sql = "SELECT 
					vtiger_account.accountid,
					vtiger_account.account_no,
					vtiger_account.account_line as categoria,
					vtiger_crmentity.smownerid
					FROM 
					vtiger_account
					JOIN 
					vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid and vtiger_crmentity.deleted = 0
					JOIN vtiger_accountscf on vtiger_accountscf.accountid = vtiger_account.accountid";
		$acc_res = $adb->query($acc_sql);	
		while($acc_row=$adb->fetchByAssoc($acc_res) ) {
			$recOwnId = $acc_row['smownerid'];			
			$accountid = $acc_row['accountid'];
			$accountname = $acc_row['account_no'];
			$categoria = $acc_row['categoria'];
			$is_group = true;
			$sql_result = $adb->pquery("select count(*) as count from vtiger_users where id = ?",array($recOwnId));
			if($adb->query_result($sql_result,0,'count') > 0) $is_group = false;
			if( $is_group ) {
				if(in_array($recOwnId,$current_user_groups))
				{
					foreach(  $current_user_groups as $user_group) 
					{
						$owners_id[] = $user_group;
					}
					// echo $user_id."|GROUP|".$user_name."|".$accountid."|".$accountname."|".$categoria."\n";
					insert_temp_table($user_id, 'GROUP', $user_name,$accountid,$accountname,$categoria);
					$count_cat[$user_name][$categoria] += 1;
				}	 
			} else {
				if( $user_id == $recOwnId){
					// echo $user_id."|OWNER|".$user_name."|".$accountid."|".$accountname."|".$categoria."\n";
					insert_temp_table($user_id, 'OWNER', $user_name,$accountid,$accountname,$categoria);
					$count_cat[$user_name][$categoria] += 1;
				} else {
					if(sizeof($subordinate_roles_users) > 0)
					{
						$b_found=false;
						foreach($subordinate_roles_users as $roleid=>$userids)
						{
							if(in_array($recOwnId,$userids))
							{
								foreach(  $userids as $userid) 
								{
									$owners_id[] = $userid;
								}
								// echo $user_id."|MANAGER|".$user_name."|".$accountid."|".$accountname."|".$categoria."\n";
								insert_temp_table($user_id, 'MANAGER', $user_name,$accountid,$accountname,$categoria);
								$count_cat[$user_name][$categoria] += 1;
								$b_found=true;
							}
							if($b_found) break;
						}
					} 
				}
			}
		} // END ACCOUNTS	
	} // END USERS
	//$import_result = $accRating->populateNow();
	return $import_result;
}

function insert_temp_table( $user_id, $role, $user_name,$accountid,$accountname,$categoria) {
	global $adb;
	$sql_result = $adb->pquery("INSERT INTO temp_acc_users (userid,role,username,accountid,account_no,account_cat) VALUES (?,?,?,?,?,?)",array($user_id, $role, $user_name,$accountid,$accountname,$categoria));
}

?>
