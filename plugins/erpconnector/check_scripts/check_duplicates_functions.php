<?php
include_once 'include/Zend/Json.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'include/utils/VtlibUtils.php';
include_once 'include/Webservices/Create.php';
include_once 'include/QueryGenerator/QueryGenerator.php';
require_once('modules/Users/Users.php');
require_once('include/utils/utils.php');
require_once('modules/Emails/mail.php');
require_once('modules/Accounts/Accounts.php');

function do_check_duplicates($time_start) {
	global $log_active, $adb, $days_detail, $days_summary, $to , $subject,$cc;
	$import_result = array();
	$import_result['records_created']=0;
	$import_result['records_updated']=0;
	$message = "<html><body>";
	// CHECK AZIENDE CHE HANNO STESSO NOME E STESSO EXTERNAL CODE 
	$query = "SELECT min(vtiger_account.accountid) as min_accountid, count(*) as count_dupl,
					vtiger_account.accountname,
					vtiger_account.external_code
					FROM
					vtiger_account
					JOIN vtiger_crmentity 				on vtiger_crmentity.crmid = vtiger_account.accountid and vtiger_crmentity.deleted=0
					JOIN vtiger_account duplaccount ON
																duplaccount.external_code = vtiger_account.external_code 
																AND duplaccount.accountid > vtiger_account.accountid
																AND (duplaccount.account_to_be_deleted <> 'DELETE_NO' OR  duplaccount.account_to_be_deleted IS NULL)
																AND (duplaccount.account_to_be_deleted <> 'DELETE_YES' OR  duplaccount.account_to_be_deleted IS NULL)
					JOIN vtiger_crmentity duplentity on duplentity.crmid = duplaccount.accountid and duplentity.deleted=0
					WHERE 
					vtiger_account.external_code IS NOT NULL AND vtiger_account.external_code <> ''
					GROUP BY 
					vtiger_account.accountname,
					vtiger_account.external_code
					ORDER BY count_dupl desc, vtiger_account.accountname ASC";
	if($log_active) echo "do_check_duplicates detail first query= ".$query." \n";
	$message .= "\r\n<h1>FOLLOWING ACCOUNTS HAVE DUPLICATES</h1>\r\n";
	$rs_message = "";
	$del_message = "";
	$result = $adb->query($query);
	array_map('unlink', glob("plugins/erpconnector/check_duplicates/aziende_duplicate_file_*.csv"));
	$fp = fopen("plugins/erpconnector/check_duplicates/aziende_duplicate_file_".date('Y-m-d_H:i:s').".csv", "w");
	$out_row = array();
	$out_row["id"] = "ID Azienda Principale";
	$out_row["cod"] = "Codice Azienda Principale";
	$out_row["external_code"] = "Codice Semiramis Azienda Principale";
	$out_row["accountid"] = "ID Azienda Duplicata";
	$out_row["account_no"] = "Codice Azienda Duplicata";
	$out_row["accountname"] = "Nome Azienda";
	$out_row["user_name"] = "Utente Assegnato a Azienda";
	$out_row["email1"] = "Email Utente Assegnato a";
	fputcsv($fp, $out_row, ";");
	while($row=$adb->fetchByAssoc($result))
	{
		
		$rs_message .= $row["min_accountid"].";".$row["count_dupl"].";".$row["accountname"]."<br/>\r\n";
		$focus = CRMEntity::getInstance("Accounts");
		$focus->mode = "edit";
		$focus->id = $row["min_accountid"];
		// danzi.tn@test $focus->save($module);
		$del_message .= _process_duplicates($focus, $fp);
		$import_result['records_updated']++;
	}
	// CHECK AZIENDE CHE HANNO STESSO NOME MA UNA HA EXTERNAL CODE VALIDO E L'ALTRA CE L'HA VUOTO
	$query_2 = "SELECT min(vtiger_account.accountid) as min_accountid, count(*) as count_dupl,
					vtiger_account.accountname
					-- ,vtiger_account.external_code
					FROM
					vtiger_account
					JOIN vtiger_crmentity 				on vtiger_crmentity.crmid = vtiger_account.accountid and vtiger_crmentity.deleted=0
					JOIN vtiger_account duplaccount 	on duplaccount.accountname = vtiger_account.accountname 
																AND (duplaccount.external_code IS NULL OR duplaccount.external_code = '')
																AND duplaccount.accountid <> vtiger_account.accountid
																AND (duplaccount.account_to_be_deleted <> 'DELETE_NO' OR duplaccount.account_to_be_deleted IS NULL)
																AND (duplaccount.account_to_be_deleted <> 'DELETE_YES' OR  duplaccount.account_to_be_deleted IS NULL)
					JOIN vtiger_crmentity duplentity on duplentity.crmid = duplaccount.accountid and duplentity.deleted=0
					WHERE 
					vtiger_account.external_code IS NOT NULL AND vtiger_account.external_code <> ''
					AND  (vtiger_account.account_to_be_deleted <> 'DELETE_YES' OR vtiger_account.account_to_be_deleted IS NULL)
					GROUP BY 
					vtiger_account.accountname
					-- ,vtiger_account.external_code
					ORDER BY count_dupl desc, vtiger_account.accountname ASC, min_accountid ASC";
	if($log_active) echo "do_check_duplicates detail second query = ".$query_2." \n";
	$result_2 = $adb->query($query_2);
	while($row=$adb->fetchByAssoc($result_2))
	{
		$rs_message .= $row["min_accountid"].";".$row["count_dupl"].";".$row["accountname"]."<br/>\r\n";
		$focus = CRMEntity::getInstance("Accounts");
		$focus->mode = "edit";
		$focus->id = $row["min_accountid"];
		// danzi.tn@test $focus->save($module);
		$del_message .= _process_duplicates_2($focus, $fp);
		$import_result['records_updated']++;
	}
	/*
	// CHECK AZIENDE CHE HANNO STESSO NOME, STESSO STATO, STESSA CITTA ED EXTERNAL CODE VUOTO
	$query_3 = "SELECT min(vtiger_account.accountid) as min_accountid, count(*) as count_dupl,
					vtiger_account.accountname,
					vtiger_accountbillads.bill_country,
					vtiger_accountbillads.bill_city
					FROM
					vtiger_account
					JOIN vtiger_crmentity 			ON vtiger_crmentity.crmid = vtiger_account.accountid and vtiger_crmentity.deleted=0
					JOIN vtiger_accountbillads 		ON vtiger_accountbillads.accountaddressid = vtiger_account.accountid
					JOIN vtiger_account duplaccount ON duplaccount.accountname = vtiger_account.accountname 
																AND (duplaccount.external_code IS NULL OR duplaccount.external_code = '')
																AND duplaccount.accountid <> vtiger_account.accountid
																AND (duplaccount.account_to_be_deleted <> 'DELETE_NO' OR duplaccount.account_to_be_deleted IS NULL)
																AND (duplaccount.account_to_be_deleted <> 'DELETE_YES' OR  duplaccount.account_to_be_deleted IS NULL)
					JOIN vtiger_crmentity duplentity on duplentity.crmid = duplaccount.accountid and duplentity.deleted=0
					JOIN vtiger_accountbillads duplaccbillads	ON duplaccbillads.accountaddressid = duplaccount.accountid
																					AND duplaccbillads.bill_country = vtiger_accountbillads.bill_country
																					AND duplaccbillads.bill_city = vtiger_accountbillads.bill_city
					WHERE 
					(vtiger_account.external_code IS NULL OR vtiger_account.external_code = '')
					AND  (vtiger_account.account_to_be_deleted <> 'DELETE_YES' OR vtiger_account.account_to_be_deleted IS NULL)
					GROUP BY 
					vtiger_account.accountname,
					vtiger_accountbillads.bill_country,
					vtiger_accountbillads.bill_city
					ORDER BY count_dupl desc, vtiger_account.accountname ASC, min_accountid ASC";
	if($log_active) echo "do_check_duplicates detail third query= ".$query_3." \n";
	$result_3 = $adb->query($query_3);
	while($row=$adb->fetchByAssoc($result_3))
	{
		$rs_message .= $row["min_accountid"].";".$row["count_dupl"].";".$row["accountname"]."<br/>\r\n";
		$focus = CRMEntity::getInstance("Accounts");
		$focus->mode = "edit";
		$focus->id = $row["min_accountid"];
		// danzi.tn@test $focus->save($module);
		$del_message .= _process_duplicates_3($focus, $fp);
		$import_result['records_updated']++;
	}
	*/
	fclose($fp);
	if(empty($rs_message))  {
		$rs_message = "<p>Running Scripts NONE</p>\r\n";
		$subject .= " - NO DUPLICATE FOUND";
	} else {
		$subject .= " - FOUND DUPLICATES FOR ".$import_result['records_updated']. " ACCOUNTS";
	}
	$message .= $rs_message;
	$message .= "\r\n";
	$message .= "<h1>THE FOLLOWING ACCOUNTS MUST BE DELETED ============</h1>\r\n";
	$message .= $del_message;
	// if($log_active) echo $message;
	$message .= "</body></html>";
	send_mail('Emails',$to ,'ROTHO BLAAS','laura@rothoblaas.com',$subject,$message,$cc,'');
	// _delete_account_tobe_deleted();
	return $import_result;
}

function _process_duplicates($acc_focus, $fp) {
	global $log_active, $adb;
	$query = "SELECT 
				vtiger_account.accountid,
				vtiger_account.accountname,
				vtiger_account.account_no,
				vtiger_account.external_code,
				vtiger_account.account_line,
				vtiger_account.sem_importflag,
				vtiger_account.sem_importdate,
				vtiger_crmentity.smcreatorid,
				vtiger_crmentity.smownerid,
				vtiger_users.first_name,
				vtiger_users.last_name,
				vtiger_users.user_name,
				vtiger_users.email1,
				vtiger_crmentity.createdtime,
				vtiger_crmentity.modifiedtime,
				duplaccount.accountid as dupl_id,
				duplaccount.account_no as dupl_cod,
				duplentity.smcreatorid as dupl_smcreatorid,
				duplentity.createdtime as dupl_createdtime
				FROM
				vtiger_account
				JOIN vtiger_crmentity 				on vtiger_crmentity.crmid = vtiger_account.accountid and vtiger_crmentity.deleted=0
				JOIN vtiger_account duplaccount 	on  
															 duplaccount.external_code = vtiger_account.external_code 
															AND duplaccount.accountid < vtiger_account.accountid
															AND duplaccount.accountid = ?
				JOIN vtiger_crmentity duplentity on duplentity.crmid = duplaccount.accountid and duplentity.deleted=0
				LEFT JOIN vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
				WHERE (vtiger_account.account_to_be_deleted <> 'DELETE_NO' OR vtiger_account.account_to_be_deleted IS NULL)
				ORDER BY vtiger_account.accountid ASC";
	$result = $adb->pquery($query,array($acc_focus->id));
	// if($log_active) echo "For ".$acc_focus->id." \n";
	// if($log_active) echo "_process_duplicates delete query= ".$query." \n";
	$del_value = array();
	$rs_message = "";
	while($row=$adb->fetchByAssoc($result))
	{
		$del_value[] = $row["accountid"];
		$out_row = array();
		$rs_message .= $acc_focus->id.";".$row["dupl_cod"].";".$row["external_code"].";".$row["accountid"].";".$row["account_no"].";".$row["accountname"].";".$row["user_name"].";".$row["email1"]."<br/>\r\n";
		$out_row["id"] = $acc_focus->id;
		$out_row["cod"] = $row["dupl_cod"];
		$out_row["external_code"] = $row["external_code"];
		$out_row["accountid"] = $row["accountid"];
		$out_row["account_no"] = $row["account_no"];
		$out_row["accountname"] = $row["accountname"];
		$out_row["user_name"] = $row["user_name"];
		$out_row["email1"] = $row["email1"];
		
		_set_account_tobe_deleted($row["accountid"],  $acc_focus->id);
		fputcsv($fp, $out_row, ";");
	}
	// danzi.tn@test $acc_focus->transferRelatedRecords("Accounts",$del_value,$acc_focus->id);
	// Delete the records by id specified in the list
	// danzi.tn@test foreach($del_value as $value)
	// danzi.tn@test {
	// danzi.tn@test 	$acc_focus->trash("Account",$value );
	// danzi.tn@test }
	return $rs_message;
}


function _process_duplicates_2($acc_focus, $fp) {
	global $log_active, $adb;
	$query = "SELECT 
				vtiger_account.accountid,
				vtiger_account.accountname,
				vtiger_account.account_no,
				vtiger_account.account_line,
				vtiger_account.sem_importflag,
				vtiger_account.sem_importdate,
				vtiger_account.email1,
				vtiger_account.account_to_be_deleted,
				vtiger_crmentity.smcreatorid,
				vtiger_crmentity.smownerid,
				vtiger_users.first_name,
				vtiger_users.last_name,
				vtiger_users.user_name,
				vtiger_users.email1,
				vtiger_crmentity.createdtime,
				vtiger_crmentity.modifiedtime,
				duplaccount.accountid as dupl_id,
				duplaccount.account_no as dupl_cod,
				duplaccount.external_code,
				duplentity.smcreatorid as dupl_smcreatorid,
				duplentity.createdtime as dupl_createdtime,
				vtiger_account.account_to_be_deleted
				FROM
				vtiger_account
				JOIN vtiger_crmentity 				on vtiger_crmentity.crmid = vtiger_account.accountid and vtiger_crmentity.deleted=0
				JOIN vtiger_account duplaccount 	on duplaccount.accountname = vtiger_account.accountname 
															AND duplaccount.accountid <> vtiger_account.accountid
															AND duplaccount.accountid = ?
				JOIN vtiger_crmentity duplentity on duplentity.crmid = duplaccount.accountid and duplentity.deleted=0
				LEFT JOIN vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
				WHERE ( vtiger_account.account_to_be_deleted <> 'DELETE_NO' OR vtiger_account.account_to_be_deleted <> 'DELETE_YES' OR vtiger_account.account_to_be_deleted IS NULL )
				AND (vtiger_account.external_code IS NULL OR vtiger_account.external_code = '') 
				ORDER BY vtiger_account.accountid ASC";
	$result = $adb->pquery($query,array($acc_focus->id));
	// if($log_active) echo "For ".$acc_focus->id." \n";
	// if($log_active) echo "_process_duplicates_2 delete query= ".$query." \n";
	$del_value = array();
	$rs_message = "";
	while($row=$adb->fetchByAssoc($result))
	{
		$del_value[] = $row["accountid"];
		$out_row = array();
		$rs_message .= $acc_focus->id.";".$row["dupl_cod"].";".$row["external_code"].";".$row["accountid"].";".$row["account_no"].";".$row["accountname"].";".$row["user_name"].";".$row["email1"]."<br/>\r\n";
		$out_row["id"] = $acc_focus->id;
		$out_row["cod"] = $row["dupl_cod"];
		$out_row["external_code"] = $row["external_code"];
		$out_row["accountid"] = $row["accountid"];
		$out_row["account_no"] = $row["account_no"];
		$out_row["accountname"] = $row["accountname"];
		$out_row["user_name"] = $row["user_name"];
		$out_row["email1"] = $row["email1"];
		_set_account_tobe_deleted($row["accountid"],  $acc_focus->id);
		fputcsv($fp, $out_row, ";");
	}
	return $rs_message;
}



function _process_duplicates_3($acc_focus, $fp) {
	global $log_active, $adb;
	$query = "SELECT 
				vtiger_account.accountid,
				vtiger_account.accountname,
				vtiger_account.account_no,
				vtiger_account.external_code,
				vtiger_accountbillads.bill_city,
				vtiger_account.email1,
				vtiger_account.account_line,
				vtiger_account.sem_importflag,
				vtiger_account.sem_importdate,
				vtiger_crmentity.smcreatorid,
				vtiger_crmentity.smownerid,
				vtiger_users.first_name,
				vtiger_users.last_name,
				vtiger_users.user_name,
				vtiger_users.email1,
				vtiger_crmentity.createdtime,
				vtiger_crmentity.modifiedtime,
				duplaccount.accountid as dupl_id,
				duplaccount.account_no as dupl_cod,
				duplentity.smcreatorid as dupl_smcreatorid,
				duplentity.createdtime as dupl_createdtime
				FROM
				vtiger_account
				JOIN vtiger_crmentity 				on vtiger_crmentity.crmid = vtiger_account.accountid and vtiger_crmentity.deleted=0
				JOIN vtiger_accountbillads 		ON vtiger_accountbillads.accountaddressid = vtiger_account.accountid
				JOIN vtiger_account duplaccount 	on duplaccount.accountname = vtiger_account.accountname
															AND duplaccount.accountid <> vtiger_account.accountid
															AND duplaccount.accountid = ?
				JOIN vtiger_crmentity duplentity on duplentity.crmid = duplaccount.accountid and duplentity.deleted=0
				JOIN vtiger_accountbillads duplaccbillads	ON duplaccbillads.accountaddressid = duplaccount.accountid
																			AND duplaccbillads.bill_country = vtiger_accountbillads.bill_country
																			AND duplaccbillads.bill_city = vtiger_accountbillads.bill_city
				LEFT JOIN vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
				WHERE (vtiger_account.account_to_be_deleted <> 'DELETE_NO' OR vtiger_account.account_to_be_deleted IS NULL)
				ORDER BY vtiger_account.accountid ASC";
	$result = $adb->pquery($query,array($acc_focus->id));
	// if($log_active) echo "For ".$acc_focus->id." \n";
	// if($log_active) echo "_process_duplicates_2 delete query= ".$query." \n";
	$del_value = array();
	$rs_message = "";
	while($row=$adb->fetchByAssoc($result))
	{
		$del_value[] = $row["accountid"];
		$out_row = array();
		$rs_message .= $acc_focus->id.";".$row["dupl_cod"].";".$row["external_code"].";".$row["accountid"].";".$row["account_no"].";".$row["accountname"].";".$row["user_name"].";".$row["email1"]."<br/>\r\n";
		$out_row["id"] = $acc_focus->id;
		$out_row["cod"] = $row["dupl_cod"];
		$out_row["external_code"] = $row["external_code"];
		$out_row["accountid"] = $row["accountid"];
		$out_row["account_no"] = $row["account_no"];
		$out_row["accountname"] = $row["accountname"];
		$out_row["user_name"] = $row["user_name"];
		$out_row["email1"] = $row["email1"];
		// da verificare se processarli o meno  _set_account_tobe_deleted($row["accountid"],  $acc_focus->id);
		fputcsv($fp, $out_row, ";");
	}
	return $rs_message;
}

function _set_account_tobe_deleted($del_id, $dupl_id) {
	global $log_active, $adb;
	if($log_active) echo $dupl_id . " => ". $del_id. "\r\n";
	$query = "UPDATE vtiger_account SET vtiger_account.account_to_be_deleted = 'DELETE_YES' 
					, vtiger_account.duplicated_delete_date = CONVERT( varchar , DATEADD(d, 2, GETDATE()), 120)
					, vtiger_account.duplicated_accountid = ?
				WHERE vtiger_account.accountid = ?";
	$result = $adb->pquery($query,array($dupl_id,$del_id ));
}

/*
UPDATE vtiger_crmentity set deleted=1,modifiedtime=GETDATE(),modifiedby=1, description = convert(text, CONVERT ( varchar, case when vtiger_crmentity.description is null then '' else vtiger_crmentity.description end ) +  ' [AZIENDA DUPLICATA - ' + vtiger_account.duplicated_accountid  + ']' )
-- SELECT vtiger_crmentity.description, convert(text, CONVERT ( varchar, case when vtiger_crmentity.description is null then '' else vtiger_crmentity.description end ) +  ' [AZIENDA DUPLICATA - ' + vtiger_account.duplicated_accountid  + ']' )
FROM				vtiger_crmentity
				JOIN  vtiger_account on vtiger_account.accountid = vtiger_crmentity.crmid AND vtiger_account.account_to_be_deleted = 'DELETE_YES' 
				JOIN vtiger_crmentity duplentity on duplentity.crmid = vtiger_account.duplicated_accountid and duplentity.deleted=0
				WHERE  vtiger_crmentity.deleted=0 
			SELECT 
				vtiger_account.accountid,
				vtiger_account.accountname,
				vtiger_account.account_no,
				vtiger_account.account_line,
				vtiger_account.duplicated_delete_date,
				vtiger_account.duplicated_accountid
				FROM
				vtiger_crmentity
				JOIN vtiger_account on vtiger_account.accountid = vtiger_crmentity.crmid
				JOIN vtiger_crmentity duplentity on duplentity.crmid = vtiger_account.duplicated_accountid and duplentity.deleted=0
				WHERE  vtiger_crmentity.deleted=0 AND vtiger_account.account_to_be_deleted = 'DELETE_YES' 
 				AND vtiger_account.duplicated_delete_date < CONVERT(varchar, GETDATE(), 120)
*/				
function _delete_account_tobe_deleted() {
	global $log_active, $adb,$table_prefix;;
	$query = "SELECT 
				vtiger_account.accountid,
				vtiger_account.accountname,
				vtiger_account.account_no,
				vtiger_account.account_line,
				vtiger_account.duplicated_delete_date,
				vtiger_account.duplicated_accountid
				FROM
				vtiger_crmentity
				JOIN vtiger_account on vtiger_account.accountid = vtiger_crmentity.crmid AND vtiger_account.account_to_be_deleted = 'DELETE_YES' 
				JOIN vtiger_crmentity duplentity on duplentity.crmid = vtiger_account.duplicated_accountid and duplentity.deleted=0
				WHERE  vtiger_crmentity.deleted=0 
 				AND vtiger_account.duplicated_delete_date < CONVERT(varchar, GETDATE(), 120)
				ORDER BY vtiger_account.accountname, vtiger_account.accountid";
	$result = $adb->query($query);
	/*
	
	$date_var = date('Y-m-d H:i:s');
	$query = "UPDATE ".$table_prefix."_crmentity set deleted=?,modifiedtime=?,modifiedby=?
				FROM
				".$table_prefix."_crmentity
				JOIN  ".$table_prefix."_account on ".$table_prefix."_account.accountid = ".$table_prefix."_crmentity.crmid AND ".$table_prefix."_account.account_to_be_deleted = 'DELETE_YES' 
				JOIN ".$table_prefix."_crmentity duplentity on duplentity.crmid = ".$table_prefix."_account.duplicated_accountid and duplentity.deleted=0
				WHERE  ".$table_prefix."_crmentity.deleted=0 
 				AND ".$table_prefix."_account.duplicated_delete_date < CONVERT(varchar, GETDATE(), 120)
				";
	$adb->pquery($query, array('1',$adb->formatDate($date_var, true),'1'), true,"Error marking record deleted: ");
	
	
	$del_value = array();
	while($row=$adb->fetchByAssoc($result))
	{
		$del_value[ $row["duplicated_accountid"] ][] = $row["accountid"];
	}	
	foreach ( $del_value as $key => $account_array )
	{
		$focus = CRMEntity::getInstance("Accounts");
		$focus->mode = "edit";
		$focus->id = $key;
		$focus->transferRelatedRecords("Accounts",$account_array,$key);
		if($log_active) echo "Delete account same of ". $key. "\r\n";
		foreach ( $account_array as $account_id)
		{
			if($log_active) echo "\t =>". $account_id. "\r\n";
			$focus->trash("Account",$account_id );
		}
	}
	*/
}


?>
