<?php
include_once 'include/Zend/Json.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'include/utils/VtlibUtils.php';
include_once 'include/Webservices/Create.php';
include_once 'include/QueryGenerator/QueryGenerator.php';

function do_users4entities($time_start) {
	global $log_active, $adb;
	echo "\n==========================================================\n";
	$import_result = array();
	$modifiedby_id = 1;
	$import_result['records_created']=0;
	$import_result['records_updated']=0;
	// LEADS
	$sql = get_sql4leads();
	$result = $adb->query($sql);
	while($row=$adb->fetchByAssoc($result)) {
		echo "Processing lead ".$row['lead_no']." with id ".$row['crmid']."\n";
		$user_id = get_agent4location(trim($row['state']),trim($row['code']),trim($row['city']), $row['smownerid'] );
		update_user4entity($row['crmid'],$row['smownerid'] , $user_id, $modifiedby_id);
		$import_result['records_updated']++;
	}
	// ACCOUNTS
	$sql = get_sql4accounts();
	$result = $adb->query($sql);
	while($row=$adb->fetchByAssoc($result)) {
		echo "Processing account ".$row['account_no']." with id ".$row['crmid']."\n";
		$user_id = get_agent4location(trim($row['bill_state']),trim($row['bill_code']),trim($row['bill_city']), $row['smownerid'] );
		update_user4entity($row['crmid'],$row['smownerid'] , $user_id, $modifiedby_id);
		$import_result['records_updated']++;
	}
	// CONTACTS 
	$sql = get_sql4contacts();
	$result = $adb->query($sql);
	while($row=$adb->fetchByAssoc($result)) {
		echo "Processing contact ".$row['contact_no']." with id ".$row['crmid']."\n";
		$user_id = get_agent4location(trim($row['bill_state']),trim($row['bill_code']),trim($row['bill_city']), $row['smownerid'] );
		update_user4entity($row['crmid'],$row['smownerid'] , $user_id, $modifiedby_id);
		$import_result['records_updated']++;
	}
	return $import_result;
}

function update_user4entity($entity_id,$previous_owner_id, $user_id,$modifiedby_id) {
	global $adb,$table_prefix;
	$sql = "UPDATE
			".$table_prefix."_crmentity
			SET 
			".$table_prefix."_crmentity.smownerid = ".$user_id." ,
			".$table_prefix."_crmentity.modifiedby = ".$modifiedby_id." ,
			".$table_prefix."_crmentity.modifiedtime = GETDATE() 
			WHERE 
			".$table_prefix."_crmentity.crmid = ".$entity_id." AND
			".$table_prefix."_crmentity.smownerid = ".$previous_owner_id."

			";
	$result = $adb->query($sql);
}

function get_sql4leads() {
	global $adb,$table_prefix;
	$sql = "SELECT 
			".$table_prefix."_crmentity.crmid, 
			".$table_prefix."_crmentity.setype,
			".$table_prefix."_crmentity.smownerid, 
			".$table_prefix."_crmentity.smcreatorid, 
			".$table_prefix."_crmentity.createdtime, 
			".$table_prefix."_crmentity.description,
			".$table_prefix."_leaddetails.company,
			".$table_prefix."_leaddetails.email,
			".$table_prefix."_leaddetails.firstname,
			".$table_prefix."_leaddetails.lastname,
			".$table_prefix."_leaddetails.lead_no,
			".$table_prefix."_leadaddress.city,
			".$table_prefix."_leadaddress.code,
			".$table_prefix."_leadaddress.country,
			".$table_prefix."_leadaddress.state
			FROM ".$table_prefix."_crmentity 
			JOIN ".$table_prefix."_leaddetails on ".$table_prefix."_leaddetails.leadid = ".$table_prefix."_crmentity.crmid and ".$table_prefix."_leaddetails.converted = 0
			JOIN  ".$table_prefix."_leadaddress on ".$table_prefix."_leadaddress.leadaddressid = ".$table_prefix."_crmentity.crmid AND ".$table_prefix."_leadaddress.country like 'IT%'
			WHERE 
			".$table_prefix."_crmentity.deleted = 0 and
			".$table_prefix."_crmentity.setype = 'Leads' and
			".$table_prefix."_crmentity.smownerid in (0,9,167)";
	return $sql;
}

function get_sql4accounts() {
	global $adb,$table_prefix;
	$sql = "SELECT 
			".$table_prefix."_crmentity.crmid, 
			".$table_prefix."_crmentity.setype,
			".$table_prefix."_crmentity.smownerid, 
			".$table_prefix."_crmentity.smcreatorid, 
			".$table_prefix."_crmentity.createdtime, 
			".$table_prefix."_crmentity.description,
			".$table_prefix."_account.accountname,
			".$table_prefix."_account.account_no,
			".$table_prefix."_account.email1,
			".$table_prefix."_accountbillads.bill_country,
			".$table_prefix."_accountbillads.bill_state,
			".$table_prefix."_accountbillads.bill_code,
			".$table_prefix."_accountbillads.bill_city
			FROM ".$table_prefix."_crmentity 
			JOIN ".$table_prefix."_account on ".$table_prefix."_account.accountid = ".$table_prefix."_crmentity.crmid
			JOIN ".$table_prefix."_accountbillads on ".$table_prefix."_accountbillads.accountaddressid = ".$table_prefix."_crmentity.crmid AND ".$table_prefix."_accountbillads.bill_country like 'IT%'
			WHERE ".$table_prefix."_crmentity.deleted = 0 AND
			".$table_prefix."_crmentity.setype = 'Accounts' AND
			".$table_prefix."_crmentity.smownerid in (0,9,167)";
	return $sql;
}


function get_sql4contacts() {
	global $adb,$table_prefix;
	$sql = "SELECT 
			".$table_prefix."_crmentity.crmid, 
			".$table_prefix."_crmentity.setype,
			".$table_prefix."_crmentity.smcreatorid, 
			".$table_prefix."_crmentity.smownerid, 
			".$table_prefix."_crmentity.createdtime, 
			".$table_prefix."_crmentity.description,
			".$table_prefix."_contactdetails.firstname,
			".$table_prefix."_contactdetails.lastname,
			".$table_prefix."_contactdetails.contact_no,
			".$table_prefix."_contactdetails.email,
			".$table_prefix."_contactdetails.accountid,
			".$table_prefix."_accountbillads.bill_country,
			".$table_prefix."_accountbillads.bill_state,
			".$table_prefix."_accountbillads.bill_code,
			".$table_prefix."_accountbillads.bill_city
			FROM ".$table_prefix."_crmentity 
			JOIN ".$table_prefix."_contactdetails on ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_crmentity.crmid
			JOIN ".$table_prefix."_accountbillads on ".$table_prefix."_accountbillads.accountaddressid = ".$table_prefix."_contactdetails.accountid AND ".$table_prefix."_accountbillads.bill_country like 'IT%'
			JOIN ".$table_prefix."_crmentity as acc_entity on acc_entity.crmid = ".$table_prefix."_accountbillads.accountaddressid and acc_entity.deleted = 0
			WHERE ".$table_prefix."_crmentity.deleted = 0 and
			".$table_prefix."_crmentity.setype = 'Contacts' and
			".$table_prefix."_crmentity.smownerid in (0,9,167)";
	return $sql;
}


function get_agent4location($state,$code,$city, $previous_owner_id ) {
	global $adb,$table_prefix, $log_active;
	$agent_id = $previous_owner_id;
	$b_Found = false;
	if(!empty($city) && !$b_Found) {
		$sql = "SELECT Agente,  ".$table_prefix."_users.id ,  COUNT(Agente) 
				FROM tmp_assegnazione_agenti 
				JOIN ".$table_prefix."_users ON ".$table_prefix."_users.user_name = Agente
				WHERE Comune like '".$city."%' 
				GROUP BY Agente, ".$table_prefix."_users.id";		
		$result = $adb->query($sql);
		while($row=$adb->fetchByAssoc($result)) {
			$b_Found = true;
			$agent_id = $row['id'];
			echo "\tFound ".$agent_id." for city=".$city."\n";
			break;
		}
	}
	if(!empty($code) && !$b_Found) {
		$sql = "SELECT Agente,  ".$table_prefix."_users.id ,  COUNT(Agente) 
				FROM tmp_assegnazione_agenti 
				JOIN ".$table_prefix."_users ON ".$table_prefix."_users.user_name = Agente
				WHERE Cap = '".$code."' 
				GROUP BY Agente, ".$table_prefix."_users.id";
		$result = $adb->query($sql);
		while($row=$adb->fetchByAssoc($result)) {
			$b_Found = true;
			$agent_id = $row['id'];
			echo "\tFound ".$agent_id." for code=".$code."\n";
			break;
		}
	}
	if(!empty($state) && !$b_Found) {
		$sql = "SELECT Agente,  ".$table_prefix."_users.id ,  COUNT(Agente) 
				FROM tmp_assegnazione_agenti 
				JOIN ".$table_prefix."_users ON ".$table_prefix."_users.user_name = Agente
				WHERE Provincia = '".$state."' 
				GROUP BY Agente, ".$table_prefix."_users.id";
		$result = $adb->query($sql);
		while($row=$adb->fetchByAssoc($result)) {
			$b_Found = true;
			$agent_id = $row['id'];
			echo "\tFound ".$agent_id." for state=".$state."\n";
			break;
		}
	}
	if(!$b_Found) echo "\tNothing found  for ".$city." - ".$code." (".$state.") , I'll keep owner with id = ".$agent_id."\n";
	return $agent_id;
	
}

?>
