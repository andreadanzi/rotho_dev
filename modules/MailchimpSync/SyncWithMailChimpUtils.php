<?php
/*********************************************************************************
 * The contents of this file are copyright to Target Integration Ltd and are governed
 * by the license provided with the application. You may not use this file except in 
 * compliance with the License.
 * For support please visit www.targetintegration.com 
 * or email support@targetintegration.com
 * All Rights Reserved.
 *********************************************************************************/
require_once('include/DatabaseUtil.php');
require_once('include/database/PearDatabase.php');
require_once('modules/MailchimpSync/MCAPI.class.php');

require_once('modules/Contacts/Contacts.php');
require_once('config.php');
require_once('include/logging.php');
require_once('include/nusoap/nusoap.php');

global $MailChimpAPIKey;
global $MailChimpListId;
global $NewSubscriberType;

//Please paste the MailChimp API Key here
$MailChimpAPIKey = '8d5345ab7229842f47d6dac505fff3c4-us8';
$MailChimpListId = '82a7e51b5d';
$NewSubscriberType = 'contact';

//echo "<pre>";
//ini_set("display_errors", 1);

function getTargetsToSync($mailchimpid) {
	global $record;
	global $module_name;
	global $list_id;
	global $MailChimpAPIKey;
	global $table_prefix;
	$api = new MCAPI($MailChimpAPIKey);
	$record_array = array();
	$db = PearDatabase::getInstance();
	$sql = "SELECT 
			".$table_prefix."_targets.targetsid
			,".$table_prefix."_targets.targetname
			,".$table_prefix."_targets.target_type
			FROM ".$table_prefix."_targets
			JOIN ".$table_prefix."_crmentity target_entity ON target_entity.crmid = ".$table_prefix."_targets.targetsid AND target_entity.deleted = 0
			JOIN ".$table_prefix."_crmentityrel ON ".$table_prefix."_crmentityrel.relcrmid = ".$table_prefix."_targets.targetsid AND ".$table_prefix."_crmentityrel.relmodule = 'Targets' 
			WHERE ".$table_prefix."_targets.target_type = 'MailChimp'
			AND ".$table_prefix."_crmentityrel.crmid = ?";
	$result = $db->pquery($sql,array($mailchimpid));
	while($row = $db->fetch_row($result))
	{
		$record_array[] = $row['targetsid'];
	}
	return $record_array;
}


function syncTargetsWithMailChimp() {
	global $record;
	global $module_name;
	global $list_id;
	global $MailChimpAPIKey;
	global $table_prefix;
	$api = new MCAPI($MailChimpAPIKey);

	$db = PearDatabase::getInstance();

	$currentDate = date('Y-m-d H:i:s');
	$lastSyncDate = ''; 
	$result = $db->query("SELECT lastsyncdate FROM ".$table_prefix."_mailchimp_settings");
	while($datetime = $db->fetch_row($result)) $lastSyncDate = $datetime['lastsyncdate'];
	$db->query("UPDATE ".$table_prefix."_mailchimp_settings SET lastsyncdate = '$currentDate'");
	$sql = "SELECT ".$table_prefix."_targets.targetsid 
			, ".$table_prefix."_targets.targetname
			, ".$table_prefix."_targets.target_type
			, ".$table_prefix."_crmentityrel.module
			, ".$table_prefix."_crmentityrel.relmodule
			, ".$table_prefix."_leaddetails.leadid AS entity_id
			, ".$table_prefix."_leaddetails.lead_no AS entity_no
			, ".$table_prefix."_leaddetails.firstname AS entity_firstname
			, ".$table_prefix."_leaddetails.lastname AS entity_lastname
			, ".$table_prefix."_leaddetails.company AS entity_company
			, ".$table_prefix."_leaddetails.email AS entity_email1
			, '' AS entity_email2
			, 'default' AS entity_cat
			, 'default' AS entity_subcat
			, lead_entity.setype as entity_type
			 from ".$table_prefix."_targets
			 JOIN ".$table_prefix."_crmentity target_entity ON target_entity.crmid = ".$table_prefix."_targets.targetsid AND target_entity.deleted = 0
			 JOIN ".$table_prefix."_crmentityrel ON ".$table_prefix."_crmentityrel.crmid = ".$table_prefix."_targets.targetsid AND ".$table_prefix."_crmentityrel.module = 'Targets'
			 JOIN ".$table_prefix."_leaddetails ON ".$table_prefix."_leaddetails.leadid = ".$table_prefix."_crmentityrel.relcrmid
			 JOIN ".$table_prefix."_crmentity lead_entity ON lead_entity.crmid = ".$table_prefix."_leaddetails.leadid AND lead_entity.deleted = 0
			 WHERE 
			 ".$table_prefix."_targets.target_type = 'MailChimp'
			 AND ".$table_prefix."_targets.targetsid=".$record ." 
			 %s
			 UNION 
			 SELECT ".$table_prefix."_targets.targetsid 
			, ".$table_prefix."_targets.targetname
			, ".$table_prefix."_targets.target_type
			, ".$table_prefix."_crmentityrel.module
			, ".$table_prefix."_crmentityrel.relmodule
			, ".$table_prefix."_account.accountid  AS entity_id
			, ".$table_prefix."_account.account_no AS entity_no
			, '' AS entity_firstname
			, ".$table_prefix."_account.accountname AS entity_lastname
			, ".$table_prefix."_account.accountname  AS entity_company
			, ".$table_prefix."_account.email1 AS entity_email1
			, ".$table_prefix."_account.email2 AS entity_email2
			, CONVERT( varchar(255), ".$table_prefix."_accountscf.cf_762 ) AS entity_cat
			, CONVERT( varchar(255), ".$table_prefix."_accountscf.cf_894 ) AS entity_subcat
			, account_entity.setype as entity_type
			 from ".$table_prefix."_targets
			 JOIN ".$table_prefix."_crmentity target_entity ON target_entity.crmid = ".$table_prefix."_targets.targetsid AND target_entity.deleted = 0
			 JOIN ".$table_prefix."_crmentityrel ON ".$table_prefix."_crmentityrel.crmid = ".$table_prefix."_targets.targetsid AND ".$table_prefix."_crmentityrel.module = 'Targets'
			 JOIN ".$table_prefix."_account ON ".$table_prefix."_account.accountid = ".$table_prefix."_crmentityrel.relcrmid
			 JOIN ".$table_prefix."_crmentity account_entity ON account_entity.crmid = ".$table_prefix."_account.accountid AND account_entity.deleted = 0
			 JOIN ".$table_prefix."_accountscf ON ".$table_prefix."_accountscf.accountid = ".$table_prefix."_account.accountid
			 WHERE 
			 ".$table_prefix."_targets.target_type = 'MailChimp'
			 AND ".$table_prefix."_targets.targetsid=".$record ."
			 %s
			 UNION
			 SELECT ".$table_prefix."_targets.targetsid 
			, ".$table_prefix."_targets.targetname
			, ".$table_prefix."_targets.target_type
			, ".$table_prefix."_crmentityrel.module
			, ".$table_prefix."_crmentityrel.relmodule
			, ".$table_prefix."_contactdetails.contactid AS entity_id
			, ".$table_prefix."_contactdetails.contact_no AS entity_no
			, ".$table_prefix."_contactdetails.firstname AS entity_firstname
			, ".$table_prefix."_contactdetails.lastname AS entity_lastname
			, ".$table_prefix."_account.accountname AS entity_company
			, ".$table_prefix."_contactdetails.email AS entity_email1
			, ".$table_prefix."_account.email1 AS entity_email2
			, CONVERT( varchar(255), ".$table_prefix."_accountscf.cf_762 ) AS entity_cat
			, CONVERT( varchar(255), ".$table_prefix."_accountscf.cf_894 ) AS entity_subcat
			, contact_entity.setype as entity_type
			 from ".$table_prefix."_targets
			 JOIN ".$table_prefix."_crmentity target_entity ON target_entity.crmid = ".$table_prefix."_targets.targetsid AND target_entity.deleted = 0
			 JOIN ".$table_prefix."_crmentityrel ON ".$table_prefix."_crmentityrel.crmid = ".$table_prefix."_targets.targetsid AND ".$table_prefix."_crmentityrel.module = 'Targets'
			 JOIN ".$table_prefix."_contactdetails ON ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_crmentityrel.relcrmid
			 JOIN ".$table_prefix."_crmentity contact_entity ON contact_entity.crmid = ".$table_prefix."_contactdetails.contactid AND contact_entity.deleted = 0
			 LEFT OUTER JOIN ".$table_prefix."_account ON ".$table_prefix."_contactdetails.accountid = ".$table_prefix."_account.accountid
			 LEFT OUTER JOIN ".$table_prefix."_accountscf ON ".$table_prefix."_accountscf.accountid = ".$table_prefix."_account.accountid
			 WHERE 
			 ".$table_prefix."_targets.target_type = 'MailChimp'
			 AND ".$table_prefix."_targets.targetsid=".$record ."
			 %s
	";
	if($lastSyncDate == '') {
		$sql_query = sprintf($sql,"","","");
	} else {
		$sql_where_lead =  "AND lead_entity.modifiedtime BETWEEN '".$lastSyncDate."' AND '".$currentDate."'";
		$sql_where_account =  "AND account_entity.modifiedtime BETWEEN '".$lastSyncDate."' AND '".$currentDate."'";
		$sql_where_contact =  "AND contact_entity.modifiedtime BETWEEN '".$lastSyncDate."' AND '".$currentDate."'";
		$sql_query = sprintf($sql,$sql_where_lead,$sql_where_account,$sql_where_contact);
	}
	echo "<a href=\"javascript:$('query_2').toggle()\"> - Show / Hide Query</a>";
	echo "<pre id=\"query_2\">";
	echo preg_replace("/\t/", "", $sql_query);
	echo "</pre><br /><br />";
	echo "<script type=\"text/javascript\">$('query_2').toggle(false)</script>";
	
	$result = $db->query($sql_query);
	// Per le aziende, fare la classificazione su categoria e sottocategoria
	// bill_country
	// cf_762  categoria
	// cf_894 sottocategoria
	echo "Adding to batch ...<br /><br />";
	$tot_groupings_array = array();
	while($donnee = $db->fetch_row($result))
	{
		$groupings_array = array();
		$sql_groups = "SELECT ".$table_prefix."_targets.targetname, ".$table_prefix."_targets.target_type
						 FROM ".$table_prefix."_targets
						 JOIN ".$table_prefix."_crmentity target_entity ON target_entity.crmid = ".$table_prefix."_targets.targetsid AND target_entity.deleted = 0
						 JOIN ".$table_prefix."_crmentityrel ON ".$table_prefix."_crmentityrel.crmid = ".$table_prefix."_targets.targetsid AND ".$table_prefix."_crmentityrel.module = 'Targets' 
						 WHERE	".$table_prefix."_crmentityrel.relcrmid = ?	AND ".$table_prefix."_crmentityrel.crmid <> ?";
		$grp_result = $db->pquery($sql_groups,array($donnee['entity_id'],$donnee['targetsid']));
		$groupings_array[] = array('name'=>'Clients+ Targets', 'groups'=>$donnee['targetname']);
		$groupings_array[] = array('name'=>'Clients+ Entity Type', 'groups'=>$donnee['entity_type']);
		$groupings_array[] = array('name'=>'Clients+ Category', 'groups'=>$donnee['entity_cat']);
		$groupings_array[] = array('name'=>'Clients+ Sub Category', 'groups'=>$donnee['entity_subcat']);  // danzi.tn@20140622 da gestire split sottocategorie
		while($grprow = $db->fetch_row($grp_result)) {
			$groupings_array[] = array('name'=>'Clients+ Targets', 'groups'=>$grprow['targetname']);
		}
		$batch[] = array('RELID'=>$donnee['entity_id'], 'EMAIL'=>$donnee['entity_email1'], 'FNAME'=>$donnee['entity_firstname'], 'LNAME'=>$donnee['entity_lastname'], 'COMPANY'=>$donnee['entity_company'], 'GROUPINGS' => $groupings_array);
		$tot_groupings_array = array_merge($groupings_array,$tot_groupings_array);
	}
	
	
	echo "<a href=\"javascript:$('results_1').toggle()\"> - Show / Hide Results</a>";
	echo "<pre id=\"results_1\">";
	echo "<b>Results:</b>";
	print_r($batch);
	echo "</pre><br /><br />";
	echo "<script type=\"text/javascript\">$('results_1').toggle(false)</script>";
	
	$sql2 = "SELECT ".$table_prefix."_targets.targetsid 
			, ".$table_prefix."_targets.targetname
			, ".$table_prefix."_targets.target_type
			, ".$table_prefix."_crmentityrel.module
			, ".$table_prefix."_crmentityrel.relmodule
			, ".$table_prefix."_leaddetails.leadid AS entity_id
			, ".$table_prefix."_leaddetails.lead_no AS entity_no
			, ".$table_prefix."_leaddetails.firstname AS entity_firstname
			, ".$table_prefix."_leaddetails.lastname AS entity_lastname
			, ".$table_prefix."_leaddetails.company AS entity_company
			, ".$table_prefix."_leaddetails.email AS entity_email1
			, '' AS entity_email2
			, 'default' AS entity_cat
			, 'default' AS entity_subcat
			, lead_entity.setype as entity_type
			 from ".$table_prefix."_targets
			 JOIN ".$table_prefix."_crmentity target_entity ON target_entity.crmid = ".$table_prefix."_targets.targetsid AND target_entity.deleted = 0
			 JOIN ".$table_prefix."_crmentityrel ON ".$table_prefix."_crmentityrel.crmid = ".$table_prefix."_targets.targetsid AND ".$table_prefix."_crmentityrel.module = 'Targets'
			 JOIN ".$table_prefix."_leaddetails ON ".$table_prefix."_leaddetails.leadid = ".$table_prefix."_crmentityrel.relcrmid
			 JOIN ".$table_prefix."_crmentity lead_entity ON lead_entity.crmid = ".$table_prefix."_leaddetails.leadid AND lead_entity.deleted = 0
			 LEFT JOIN ".$table_prefix."_mailchimpsyncdiff ON ".$table_prefix."_mailchimpsyncdiff.crmid = ".$table_prefix."_targets.targetsid AND ".$table_prefix."_mailchimpsyncdiff.relcrmid = ".$table_prefix."_leaddetails.leadid
			 WHERE 
			 ".$table_prefix."_targets.target_type = 'MailChimp'
			 AND ".$table_prefix."_mailchimpsyncdiff.crmid IS NULL
			 AND ".$table_prefix."_targets.targetsid=".$record ." 
			 UNION 
			 SELECT ".$table_prefix."_targets.targetsid 
			, ".$table_prefix."_targets.targetname
			, ".$table_prefix."_targets.target_type
			, ".$table_prefix."_crmentityrel.module
			, ".$table_prefix."_crmentityrel.relmodule
			, ".$table_prefix."_account.accountid  AS entity_id
			, ".$table_prefix."_account.account_no AS entity_no
			, '' AS entity_firstname
			, ".$table_prefix."_account.accountname AS entity_lastname
			, ".$table_prefix."_account.accountname  AS entity_company
			, ".$table_prefix."_account.email1 AS entity_email1
			, ".$table_prefix."_account.email2 AS entity_email2
			, CONVERT( varchar(255), ".$table_prefix."_accountscf.cf_762 ) AS entity_cat
			, CONVERT( varchar(255), ".$table_prefix."_accountscf.cf_894 ) AS entity_subcat
			, account_entity.setype as entity_type
			 from ".$table_prefix."_targets
			 JOIN ".$table_prefix."_crmentity target_entity ON target_entity.crmid = ".$table_prefix."_targets.targetsid AND target_entity.deleted = 0
			 JOIN ".$table_prefix."_crmentityrel ON ".$table_prefix."_crmentityrel.crmid = ".$table_prefix."_targets.targetsid AND ".$table_prefix."_crmentityrel.module = 'Targets'
			 JOIN ".$table_prefix."_account ON ".$table_prefix."_account.accountid = ".$table_prefix."_crmentityrel.relcrmid
			 JOIN ".$table_prefix."_crmentity account_entity ON account_entity.crmid = ".$table_prefix."_account.accountid AND account_entity.deleted = 0
			 JOIN ".$table_prefix."_accountscf ON ".$table_prefix."_accountscf.accountid = ".$table_prefix."_account.accountid
			 LEFT JOIN ".$table_prefix."_mailchimpsyncdiff ON ".$table_prefix."_mailchimpsyncdiff.crmid = ".$table_prefix."_targets.targetsid AND ".$table_prefix."_mailchimpsyncdiff.relcrmid = ".$table_prefix."_account.accountid
			 WHERE 
			 ".$table_prefix."_targets.target_type = 'MailChimp'
			 AND ".$table_prefix."_mailchimpsyncdiff.crmid IS NULL
			 AND ".$table_prefix."_targets.targetsid=".$record ."
			 UNION
			 SELECT ".$table_prefix."_targets.targetsid 
			, ".$table_prefix."_targets.targetname
			, ".$table_prefix."_targets.target_type
			, ".$table_prefix."_crmentityrel.module
			, ".$table_prefix."_crmentityrel.relmodule
			, ".$table_prefix."_contactdetails.contactid AS entity_id
			, ".$table_prefix."_contactdetails.contact_no AS entity_no
			, ".$table_prefix."_contactdetails.firstname AS entity_firstname
			, ".$table_prefix."_contactdetails.lastname AS entity_lastname
			, ".$table_prefix."_account.accountname AS entity_company
			, ".$table_prefix."_contactdetails.email AS entity_email1
			, ".$table_prefix."_account.email1 AS entity_email2
			, CONVERT( varchar(255), ".$table_prefix."_accountscf.cf_762 ) AS entity_cat
			, CONVERT( varchar(255), ".$table_prefix."_accountscf.cf_894 ) AS entity_subcat
			, contact_entity.setype as entity_type
			 from ".$table_prefix."_targets
			 JOIN ".$table_prefix."_crmentity target_entity ON target_entity.crmid = ".$table_prefix."_targets.targetsid AND target_entity.deleted = 0
			 JOIN ".$table_prefix."_crmentityrel ON ".$table_prefix."_crmentityrel.crmid = ".$table_prefix."_targets.targetsid AND ".$table_prefix."_crmentityrel.module = 'Targets'
			 JOIN ".$table_prefix."_contactdetails ON ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_crmentityrel.relcrmid
			 JOIN ".$table_prefix."_crmentity contact_entity ON contact_entity.crmid = ".$table_prefix."_contactdetails.contactid AND contact_entity.deleted = 0
			 LEFT OUTER JOIN ".$table_prefix."_account ON ".$table_prefix."_contactdetails.accountid = ".$table_prefix."_account.accountid
			 LEFT OUTER JOIN ".$table_prefix."_accountscf ON ".$table_prefix."_accountscf.accountid = ".$table_prefix."_account.accountid
			 LEFT JOIN ".$table_prefix."_mailchimpsyncdiff ON ".$table_prefix."_mailchimpsyncdiff.crmid = ".$table_prefix."_targets.targetsid AND ".$table_prefix."_mailchimpsyncdiff.relcrmid = ".$table_prefix."_contactdetails.contactid
			 WHERE 
			 ".$table_prefix."_targets.target_type = 'MailChimp'
			 AND ".$table_prefix."_mailchimpsyncdiff.crmid IS NULL
			 AND ".$table_prefix."_targets.targetsid=".$record;
	echo "<a href=\"javascript:$('query_sql2').toggle()\"> - Show / Hide Query</a>";
	echo "<pre id=\"query_sql2\">";
	echo preg_replace("/\t/", "", $sql2);
	echo "</pre><br /><br />";
	echo "<script type=\"text/javascript\">$('query_sql2').toggle(false)</script>";
	
	$result2 = $db->query($sql2);
	

	echo "Adding to batch ...<br /><br />";
	
	while($donnee = $db->fetch_row($result2))
	{
		$groupings_array = array();
		$sql_groups = "SELECT ".$table_prefix."_targets.targetname, ".$table_prefix."_targets.target_type
						 FROM ".$table_prefix."_targets
						 JOIN ".$table_prefix."_crmentity target_entity ON target_entity.crmid = ".$table_prefix."_targets.targetsid AND target_entity.deleted = 0
						 JOIN ".$table_prefix."_crmentityrel ON ".$table_prefix."_crmentityrel.crmid = ".$table_prefix."_targets.targetsid AND ".$table_prefix."_crmentityrel.module = 'Targets' 
						 WHERE	".$table_prefix."_crmentityrel.relcrmid = ?	AND ".$table_prefix."_crmentityrel.crmid <> ?";
		$grp_result = $db->pquery($sql_groups,array($donnee['entity_id'],$donnee['targetsid']));
		$groupings_array[] = array('name'=>'Clients+ Targets', 'groups'=>$donnee['targetname']);
		$groupings_array[] = array('name'=>'Clients+ Entity Type', 'groups'=>$donnee['entity_type']);
		$groupings_array[] = array('name'=>'Clients+ Category', 'groups'=>$donnee['entity_cat']);
		$groupings_array[] = array('name'=>'Clients+ Sub Category', 'groups'=>$donnee['entity_subcat']); // danzi.tn@20140622 da gestire split sottocategorie
		while($grprow = $db->fetch_row($grp_result)) {
			$groupings_array[] = array('name'=>'Clients+ Targets', 'groups'=>$grprow['targetname']);
		}
		$batch[] = array('RELID'=>$donnee['entity_id'], 'EMAIL'=>$donnee['entity_email1'], 'FNAME'=>$donnee['entity_firstname'], 'LNAME'=>$donnee['entity_lastname'], 'COMPANY'=>$donnee['entity_company'], 'GROUPINGS' => $groupings_array);
		$tot_groupings_array = array_merge($groupings_array,$tot_groupings_array);
	}
	
	echo "<a href=\"javascript:$('results_12').toggle()\"> - Show / Hide Results</a>";
	echo "<pre id=\"results_12\">";
	echo "<b>Results:</b>";
	print_r($batch);
	echo "</pre><br /><br />";
	echo "<script type=\"text/javascript\">$('results_12').toggle(false)</script>";
	
	echo "Searching groupings ...<br /><br />";

	echo "<a href=\"javascript:$('results_g').toggle()\"> - Show / Hide Results</a>";
	echo "<pre id=\"results_g\">";
	echo "<b>Results:</b><br />";
	search_grouping($api, $list_id, $tot_groupings_array);
	echo "</pre><br /><br />";
	echo "<script type=\"text/javascript\">$('results_g').toggle(false)</script>";
	
	
	
	echo "Removing any duplicates in the batch<br /><br />";
	
	if(!empty($batch)){
		$batch = uniqueArray($batch);
	}
	
	/* Now we can synchronize contacts and accounts to MailChimp */
	if(sizeof($batch) != 0){
		
		echo "<b style=\"color: red\">Sending to Mailchimp ...</b><br /><br />";
		
		$optin = false; //yes, send optin emails
		$up_exist = true; // yes, update currently subscribed users
		$replace_int = true; // yes, replace interest
		
		$vals = $api->listBatchSubscribe($list_id,$batch,$optin, $up_exist, $replace_int);

		if ($api->errorCode){
			echo "Batch Subscribe failed!<br/>";
			echo "code:".$api->errorCode."<br/>";
			echo "msg :".$api->errorMessage."<br/>";
		} else {
			echo "successfully added:".$vals['add_count']."<br/>";
			echo "successfully updated:".$vals['update_count']."<br/>";
			echo "errors:".$vals['error_count']."<br/>";
			foreach($vals['errors'] as $val){
				echo $val['email_address']. " failed<br/>";
				echo "code:".$val['code']."<br/>";
				echo "msg :".$val['message']."<br/>";
			}
		}
		echo '<br/>';
	}	
}


function search_grouping($api, $list_id, $groupings_array) {
	$list_groupings = $api->listInterestGroupings($list_id);
	foreach( $groupings_array as $group_array) {
		echo "check for grouping ".$group_array['name']."<br/>";
		$bFoundGrouping = false;
		foreach($list_groupings as $grouping) {
			if($grouping['name'] ==  $group_array['name'] ) {
				$bFoundGrouping = true;
				echo "Grouping " . $group_array['name'] . " Found with id = ".$grouping['id']."!<br/>";
				$bFoundGroup = false;
				foreach($grouping['groups'] as $grouping_group) {
					echo "         check for group ".$group_array['groups']."<br/>";
					if($grouping_group['name'] == $group_array['groups'])
					{
						echo "         group ". $group_array['groups'] . " Found!<br/>";
						$bFoundGroup = true;
					}
				}
				if( ! $bFoundGroup ) {
					echo "         group ". $group_array['groups'] . " not Found!<br/>";
					$bRes = $api->listInterestGroupAdd($list_id,$group_array['groups'],$grouping['id']);
					echo $bRes ."<br/>";
				}
			}
		}
		if( ! $bFoundGrouping ) {
			echo "Grouping " . $group_array['name'] . " not Found!<br/>";
			$iRes = $api->listInterestGroupingAdd($list_id, $group_array['name'],  "checkboxes", array("default"));
			echo $iRes ."<br/>";
			$api->listInterestGroupAdd($list_id,$group_array['groups'],$iRes);
		}
	}
}

/**
* Function to delete from MailChimp contacts and accounts that have been deleted to the Mail Campaign since the last synchronization
*/
function syncUnsubscribedWithMailChimp(){

	global $record;
	global $module_name;
	global $list_id;
	global $table_prefix;
	global $MailChimpAPIKey;
	$api = new MCAPI($MailChimpAPIKey);
	$db = PearDatabase::getInstance();
	
    //get members that have been deleted since the last synchronization

	echo "Getting members that have been deleted since the last synchronization	<br /><br />";
	
	$sql = "SELECT ".$table_prefix."_targets.targetsid 
			, ".$table_prefix."_targets.targetname
			, ".$table_prefix."_targets.target_type
			, ".$table_prefix."_leaddetails.leadid AS entity_id
			, ".$table_prefix."_leaddetails.lead_no AS entity_no
			, ".$table_prefix."_leaddetails.firstname AS entity_firstname
			, ".$table_prefix."_leaddetails.lastname AS entity_lastname
			, ".$table_prefix."_leaddetails.company AS entity_company
			, ".$table_prefix."_leaddetails.email AS entity_email1
			, '' AS entity_email2
			, ".$table_prefix."_crmentityrel.crmid
			 from ".$table_prefix."_targets
			 JOIN ".$table_prefix."_crmentity target_entity ON target_entity.crmid = ".$table_prefix."_targets.targetsid AND target_entity.deleted = 0
			 JOIN ".$table_prefix."_mailchimpsyncdiff ON ".$table_prefix."_mailchimpsyncdiff.crmid = ".$table_prefix."_targets.targetsid AND ".$table_prefix."_mailchimpsyncdiff.module = 'Targets'
			 JOIN ".$table_prefix."_leaddetails ON ".$table_prefix."_leaddetails.leadid = ".$table_prefix."_mailchimpsyncdiff.relcrmid
			 JOIN ".$table_prefix."_crmentity lead_entity ON lead_entity.crmid = ".$table_prefix."_leaddetails.leadid 
			 LEFT JOIN ".$table_prefix."_crmentityrel ON ".$table_prefix."_crmentityrel.crmid = ".$table_prefix."_targets.targetsid AND ".$table_prefix."_crmentityrel.relcrmid = ".$table_prefix."_leaddetails.leadid AND ".$table_prefix."_crmentityrel.module = 'Targets'
			 WHERE 
			 ".$table_prefix."_targets.target_type = 'MailChimp'
			 AND (".$table_prefix."_crmentityrel.crmid IS NULL OR lead_entity.deleted = 1)
			 AND ".$table_prefix."_targets.targetsid=".$record ."
			 UNION 
			 SELECT ".$table_prefix."_targets.targetsid 
			, ".$table_prefix."_targets.targetname
			, ".$table_prefix."_targets.target_type
			, ".$table_prefix."_account.accountid  AS entity_id
			, ".$table_prefix."_account.account_no AS entity_no
			, '' AS entity_firstname
			, ".$table_prefix."_account.accountname AS entity_lastname
			, ".$table_prefix."_account.accountname  AS entity_company
			, ".$table_prefix."_account.email1 AS entity_email1
			, ".$table_prefix."_account.email2 AS entity_email2
			, ".$table_prefix."_crmentityrel.crmid
			 from ".$table_prefix."_targets
			 JOIN ".$table_prefix."_crmentity target_entity ON target_entity.crmid = ".$table_prefix."_targets.targetsid AND target_entity.deleted = 0
			 JOIN ".$table_prefix."_mailchimpsyncdiff ON ".$table_prefix."_mailchimpsyncdiff.crmid = ".$table_prefix."_targets.targetsid AND ".$table_prefix."_mailchimpsyncdiff.module = 'Targets'
			 JOIN ".$table_prefix."_account ON ".$table_prefix."_account.accountid = ".$table_prefix."_mailchimpsyncdiff.relcrmid
			 JOIN ".$table_prefix."_crmentity account_entity ON account_entity.crmid = ".$table_prefix."_account.accountid  
			 LEFT JOIN ".$table_prefix."_crmentityrel ON ".$table_prefix."_crmentityrel.crmid = ".$table_prefix."_targets.targetsid AND ".$table_prefix."_crmentityrel.relcrmid = ".$table_prefix."_account.accountid AND ".$table_prefix."_crmentityrel.module = 'Targets'
			 WHERE 
			 ".$table_prefix."_targets.target_type = 'MailChimp'
			 AND (".$table_prefix."_crmentityrel.crmid IS NULL OR account_entity.deleted = 1)
			 AND ".$table_prefix."_targets.targetsid=".$record ."
			 UNION
			 SELECT ".$table_prefix."_targets.targetsid 
			, ".$table_prefix."_targets.targetname
			, ".$table_prefix."_targets.target_type
			, ".$table_prefix."_contactdetails.contactid AS entity_id
			, ".$table_prefix."_contactdetails.contact_no AS entity_no
			, ".$table_prefix."_contactdetails.firstname AS entity_firstname
			, ".$table_prefix."_contactdetails.lastname AS entity_lastname
			, ".$table_prefix."_account.accountname AS entity_company
			, ".$table_prefix."_contactdetails.email AS entity_email1
			, ".$table_prefix."_account.email1 AS entity_email2
			, ".$table_prefix."_crmentityrel.crmid
			 from ".$table_prefix."_targets
			 JOIN ".$table_prefix."_crmentity target_entity ON target_entity.crmid = ".$table_prefix."_targets.targetsid AND target_entity.deleted = 0
			 JOIN ".$table_prefix."_mailchimpsyncdiff ON ".$table_prefix."_mailchimpsyncdiff.crmid = ".$table_prefix."_targets.targetsid AND ".$table_prefix."_mailchimpsyncdiff.module = 'Targets'
			 JOIN ".$table_prefix."_contactdetails ON ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_mailchimpsyncdiff.relcrmid
			 JOIN ".$table_prefix."_crmentity contact_entity ON contact_entity.crmid = ".$table_prefix."_contactdetails.contactid 
			 LEFT OUTER JOIN ".$table_prefix."_account ON ".$table_prefix."_contactdetails.accountid = ".$table_prefix."_account.accountid
			 LEFT JOIN ".$table_prefix."_crmentityrel ON ".$table_prefix."_crmentityrel.crmid = ".$table_prefix."_targets.targetsid AND ".$table_prefix."_crmentityrel.relcrmid = ".$table_prefix."_contactdetails.contactid AND ".$table_prefix."_crmentityrel.module = 'Targets'
			 WHERE 
			 ".$table_prefix."_targets.target_type = 'MailChimp'
			 AND (".$table_prefix."_crmentityrel.crmid IS NULL OR contact_entity.deleted = 1)
			 AND ".$table_prefix."_targets.targetsid=".$record ."
	";
	
	
	echo "<a href=\"javascript:$('query_4').toggle()\"> - Show / Hide Query</a>";
	echo "<pre id=\"query_4\">";
	echo preg_replace("/\t/", "", $sql);
	echo "</pre><br /><br />";
	echo "<script type=\"text/javascript\">$('query_4').toggle(false)</script>";
					
	$result = $db->query($sql);
	
	echo "Adding to batch ...<br /><br />";
	
	//We only get emails because it is a primary id for MailChimp, all we need to delete members from the MailChimp List
	while($donnee = $db->fetch_row($result))
	{
		$emails[] = $donnee['entity_email1'];
	}
	
	if(sizeof($emails) != 0){
	
		$delete = true; // Yes, we want members to be deleted, not unsubscribed
		$bye = false; // no, don't send a goodbye email
		$notify = false; // no, don't tell me I did this
		
		echo "Removing from Mailchimp ...<br /><br />";
		
		$vals = $api->listBatchUnsubscribe($list_id, $emails, $delete, $bye, $notify);

		if ($api->errorCode){
			echo "code:".$api->errorCode."<br/>";
			echo "msg :".$api->errorMessage."<br/>";
		} else {
			echo "successfully removed:".$vals['success_count']."<br/>";
			echo "errors:".$vals['error_count']."<br/>";
			foreach($vals['errors'] as $val){
				echo "\t*".$val['email']. " failed<br/>";
				echo "\tcode:".$val['code']."<br/>";
				echo "\tmsg :".$val['message']."<br/><br/>";
			}
		}
		echo '<br/>';
	}
}

/**
* Get the Mail Campaign name because it is used to match the Mail Campaign to the MailChimp list 
*/
function getMailCampaignInfo(){

	global $record;
	global $table_prefix;
	global $module_name;
	$db = PearDatabase::getInstance();
	
	echo "Getting name of Mailchimp Campaign ";
	
	$result = $db->query("SELECT mailchimpname 
							from ".$table_prefix."_mailchimpsync
							where ".$table_prefix."_mailchimpsync.mailchimpsyncid = ".$record."
								");
	$donnee = $db->fetch_row($result);
	
	echo "<b>".$donnee['mailchimpname']."</b><br /><br />";
	
	return $donnee;
}

/**
* Get the members of the matching MailChimp list 
* $status : subscribed, unsubscribed, clean or updated, the status of a member of a MailChimp list
* Depending of the status we are looking for, we will remove a contact/account from the Mail Campaign or we will add them to the Mail Campaign
*/
function getListMembers($status, $dump = null){
	
	global $list_id;
	global $MailChimpAPIKey;
	global $record;
	global $MailChimpListId;
	global $table_prefix;
	//$lastSyncDate = getLastSyncDate();
	
	$db = PearDatabase::getInstance();
	
	//TODO:  add since parameter
	//TODO: cache this for the second run
	
	echo "<h2>$status</h2>";
	
	if (!$dump) echo "<b style=\"color: red\">Querying Mailchimp export API</b><br /><br />";
	
	$result = $db->query("SELECT lastsyncdate FROM ".$table_prefix."_mailchimp_settings");
	while($datetime = $db->fetch_row($result)) $lastSyncDate = $datetime['lastsyncdate'];

	$url = 'http://'.substr($MailChimpAPIKey, -3).'.api.mailchimp.com/export/1.0/list?apikey='.$MailChimpAPIKey.'&id='.$MailChimpListId.'&since='.urlencode($lastSyncDate);
	
	echo $url."\n\n";
	
	$dump = ($dump) ? $dump : explode("\n", file_get_contents($url));
	$dumpcache = $dump;
	
	echo "<a href=\"javascript:$('results_3_$status').toggle()\"> - Show / Hide Results</a>";
	echo "<pre id=\"results_3_$status\">";
	echo "<b>Results:</b>";
	print_r($dump);
	echo "</pre><br /><br />";
	echo "<script type=\"text/javascript\">$('results_3_$status').toggle(false)</script>";
	
	echo "Extracting headers ...<br /><br />";
	
	$keys = json_decode(array_shift($dump));
	
	$key_count = count($keys) - 13; // we don't want to extra information at the end
	
	echo "Getting the name of this vTiger Mailchimp Campaign .... <b>";
	
	$queryResult = $db->query("SELECT mailchimpname FROM ".$table_prefix."_mailchimp WHERE mailchimpid = ".$record);

	while($campaign = $db->fetch_row($queryResult)) $campaign_name = $campaign['mailchimpname'];
	
	echo $campaign_name."</b><br /><br />";
	
	echo "Extracting only the members of this vTiger Mailchimp Campaign<br /><br />";
	
	$remove_keys = array('MEMBER_RATING', 'OPTIN_TIME', 'OPTIN_IP', 'CONFIRM_TIME', 'CONFIRM_IP', 'LATITUDE', 'LONGITUDE', 'GMTOFF', 'DSTOFF', 'TIMEZONE', 'CC', 'REGION', 'LAST_CHANGED');
	
	
	foreach ($dump as $dumprow) { // loop through every row in dump from Mailchimp
		
		$item = json_decode($dumprow);
		
		//print_r($item);
		
		$buffer = array();
		$include_flag = false;
			
		for ($i=0; $i<$key_count; $i++) { // loop through each field in row
			
			$buffer[$keys[$i]] = $item[$i];
			
			
			
			//if ($keys[$i] == "Email Address") echo "<span style=\"color: red\">".$item[$i]."</span> : <br  />";
			//else echo "<b>".$keys[$i]."</b> ------> <span style=\"color: lightblue\">".$item[$i]."</span><br />";
			
			
			
			if ($keys[$i] == $campaign_name && $item[$i] == "default") {
				$include_flag = true;
				//echo "<span style=\"color: green\">Including this contact!</span><br />";
			}
			
		}
			
		$l_name = explode('@',$buffer['Email Address']);
		if ($include_flag) $data[] = array('EMAIL'=>$buffer['Email Address'], 'FNAME'=>$buffer['First Name'], 'LNAME'=>$buffer['Last Name']?$buffer['Last Name']:$l_name[0], 'COMPANY'=>'');
			
	}
	
	echo "</pre>";
	
	echo "<a href=\"javascript:$('results_4_$status').toggle()\"> - Show / Hide Results</a>";
	echo "<pre id=\"results_4_$status\">";
	echo "<b>Results: </b>";
	print_r($data);
	echo "</pre><br /><br />";
	echo "<script type=\"text/javascript\">$('results_4_$status').toggle(false)</script>";
	
	
	echo "Members returned: ". sizeof($data). "\n<br/>";
	
	if($status == 'unsubscribed'){
		if(sizeof($data)!=0){
			removeContactsFromMailCampaign($data);
		}
	}
	else if($status == 'updated'){
		if(sizeof($data)!=0){
			addContacts($data);
		}
	}
	
	
	return $dumpcache;
}

/**
* Get Mailchimp member's information : email, first name, last name and company
* $members : array of email addresses and timestamps
*/
function getMembersDetails($members){
	
	global $list_id;
	global $record;
	global $MailChimpAPIKey;
	global $table_prefix;
	$api = new MCAPI($MailChimpAPIKey);
	
	
	
	foreach($members as $member){
		
		$email = $member['email'];
		$result = $api->ListMemberInfo($list_id, $email);
		if ($api->errorCode){
			//echo "Unable to load listMemberInfo()!\n";
			//echo "\tCode=".$api->errorCode."\n";
			//echo "\tMsg=".$api->errorMessage."\n";
		} else {
			$result = $result['data'];
			foreach ($result as $records)
			{
				$donnee = $records['merges'];
				$l_name = explode('@',$donnee['EMAIL']);
				//echo '<br/>Last update time from mailchim for this contact : '.$result['info_changed'];
				$batch[] = array('RELID'=>$donnee['RELID'] , 'EMAIL'=>$donnee['EMAIL'], 'FNAME'=>$donnee['FNAME'], 'LNAME'=>$donnee['LNAME']?$donnee['LNAME']:$l_name, 'COMPANY'=>$donnee['COMPANY'], 'GROUPS' => $member['group_ids']);
			}			
		}
		
	}
	//echo '<h3>Datas from mailchimp we want to add/update to vtiger</h3>';
	
	
		
	return $batch;
}


function syncCampaings() {
	global $list_id;
	global $record;
	global $MailChimpAPIKey;
	global $table_prefix;
	$db = PearDatabase::getInstance();
	$mc_campaigns = getAllCamapigns();
	foreach($mc_campaigns as $item) {
		$sql = "SELECT ".$table_prefix."_mailchimpsync.mailchimpsyncid
				,".$table_prefix."_mailchimpsync.mailchimp_name
				FROM ".$table_prefix."_mailchimpsync
				JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_mailchimpsync.mailchimpsyncid AND ".$table_prefix."_crmentity.deleted = 0
				WHERE
				".$table_prefix."_mailchimpsync.mailchimp_name = ? OR ".$table_prefix."_mailchimpsync.mailchimp_uid = ? ";
		$bCFound = false;
		$result = $db->pquery($sql, array($item['title'],$item['id']));
		$id_array = array();
		$mailchimpsyncid = 0;
		foreach($item['members'] as $member) {
			$id_array[] = $member['crmid'];
		}
		//We only get emails because it is a primary id for MailChimp, all we need to delete members from the MailChimp List
		while($row = $db->fetch_row($result))
		{
			$mailchimpsyncid = $row['mailchimpsyncid'];
			$bCFound = true;
		}
		if(!$bCFound) {
			//echo "Nothing Found....let's create a new campaign! <br/>"; QUI BISOGNA CREARE LA CAMPAGNA
			$newMCSync = CRMEntity::getInstance('MailchimpSync');
			vtlib_setup_modulevars('MailchimpSync',$newMCSync);
			$newMCSync->column_fields['mailchimp_name'] = $item['title'];
			$newMCSync->column_fields['mailchimp_type'] = $item['type'];
			$newMCSync->column_fields['mailchimp_state'] = $item['status'];
			$newMCSync->column_fields['mailchimp_uid'] = $item['id'];
			$newMCSync->column_fields['mailchimp_link'] = "admin.mailchimp.com/campaigns/show?id=".$item['c_web_id'];
			$newMCSync->save($module_name='MailchimpSync',$longdesc=false);
			$mailchimpsyncid = $newMCSync->id;
		}
		// Relations with stuff in Accounts, Contacts and Leads
		$sqlDelete = "DELETE FROM vtiger_crmentityrel WHERE
				crmid =  ".$mailchimpsyncid."
				AND module= 'MailchimpSync'
				AND relcrmid in (".implode(",",$id_array).")";
		//echo $sqlDelete . "<br/>";
		$db->query($sqlDelete);
		$sqlInsert = "INSERT INTO
				vtiger_crmentityrel
				SELECT  ".$mailchimpsyncid.", 'MailchimpSync', vtiger_crmentity.crmid, vtiger_crmentity.setype from 
				vtiger_crmentity
				where vtiger_crmentity.deleted = 0 AND
				vtiger_crmentity.crmid in (".implode(",",$id_array).")
				";
		//echo $sqlInsert . "<br/>";
		$db->query($sqlInsert);
	}
}

/**
* getAllCamapigns
*/
function getAllCamapigns(){
	
	global $list_id;
	global $record;
	global $MailChimpAPIKey;
	global $table_prefix;
	$api = new MCAPI($MailChimpAPIKey);
	
	
	$batch = array();
	$result = $api->campaigns(array("list_id"=>$list_id));
	
	if ($api->errorCode){
		echo "campaigns retrieve failed!<br/>";
		echo "code:".$api->errorCode."<br/>";
		echo "msg :".$api->errorMessage."<br/>";
	} else {
		foreach($result['data'] as $item){
			$campaign_item = array();
			$campaign_item["id"] = $item['id'];
			$campaign_item["title"] = $item['title'];
			$campaign_item["type"] = $item['type'];
			$campaign_item["status"] = $item['status'];
			$campaign_item["subject"] = $item['subject'];
			$campaign_item["create_time"] = $item['create_time'];
			$campaign_item["send_time"] = $item['send_time'];
			$campaign_item["emails_sent"] = $item['emails_sent'];
			$campaign_item["c_web_id"] = $item['web_id'];
			$retmembers = $api->campaignMembers($item['id']);
			if ($api->errorCode){
				echo "campaignMembers retrieve failed!<br/>";
				echo "code:".$api->errorCode."<br/>";
				echo "msg :".$api->errorMessage."<br/>";
			} else {
				$tot_members = $retmembers['total'];
				$campaign_item["total_members"] = $retmembers['total'];
				$members = $retmembers['data'];
				foreach($members as $member){
					$memberdata = array();
					$email = $member['email'];
					$memberdata['email'] = $member['email'];
					$memberdata['c_status'] = $member['status'];
					$retmember = $api->listMemberInfo($list_id, $email);
					if ($api->errorCode){
						echo "listMemberInfo failed for ".$email."!<br/>";
						echo "code:".$api->errorCode."<br/>";
						echo "msg :".$api->errorMessage."<br/>";
					} else {
						$member_data = $retmember['data'];
						foreach ($member_data as $mdata)
						{
							$memberdata['m_status'] = $mdata['status'];
							$memberdata['m_web_id'] = $mdata['web_id'];
							$merges = $mdata['merges'];
							$memberdata['first_name'] = $merges['FNAME'];
							$memberdata['last_name'] = $merges['LNAME'];
							$memberdata['crmid'] = $merges['RELID'];
							$memberdata['company'] = $merges['COMPANY'];
						}
					}
					$campaign_item["members"][] = $memberdata;
				}
			}
			$batch[] = $campaign_item;
		}
	}			
	echo "<a href=\"javascript:$('results_members').toggle()\"> - Show / Hide Results</a>";
	echo "<pre id=\"results_members\">";
	echo "<b>Results: </b>";
	print_r($batch);
	echo "</pre><br /><br />";
	echo "<script type=\"text/javascript\">$('results_members').toggle(false)</script>";	
	
	return $batch;
}


/**
* Remove contacts and accounts from the Mail Campaign
* $members : array of email addresses and timestamps
*/
function removeContactsFromMailCampaign($members){
	
	
	
	
	echo "<br /><b>Removing contacts and accounts from the Mail Campaign</b><br /><br />";
	
	global $record;
	global $table_prefix;
	$db = PearDatabase::getInstance();
	
	echo "Getting all the members of this vTiger Mailchimp campaign<br /><br />";
	
	$sql = "SELECT DISTINCT relcrmid FROM ".$table_prefix."_crmentityrel WHERE crmid = ".$record;
	
	echo "<a href=\"javascript:$('query_7').toggle()\"> - Show / Hide Query</a>";
	echo "<pre id=\"query_7\">";
	echo $sql;
	echo "</pre><br /><br />";
	echo "<script type=\"text/javascript\">$('query_7').toggle(false)</script>";
	
	$result = $db->query($sql);
	
	// TODO: what if query returns blank?
	
	$campaignMemberIds = array();
	
	while($row = $db->fetch_row($result)) {
		$campaignMemberIds[] = $row['relcrmid'];
	}
	
	echo "<a href=\"javascript:$('results_9').toggle()\"> - Show / Hide Results</a>";
	echo "<pre id=\"results_9\">";
	echo "<b>Results: </b>";
	print_r($campaignMemberIds);
	echo "</pre><br /><br />";
	echo "<script type=\"text/javascript\">$('results_9').toggle(false)</script>";
	
	$mailchimpMembersIds = array();
	
	echo "Getting vTiger entity IDs of mailchimp subscribers<br /><br />";
	
	foreach ($members as $entity) {
		$query = "SELECT contactid as id FROM ".$table_prefix."_contactdetails WHERE email = '{$entity['EMAIL']}'
				  UNION SELECT leadid as id FROM ".$table_prefix."_leaddetails WHERE email = '{$entity['EMAIL']}'
				  UNION SELECT accountid as id FROM ".$table_prefix."_account WHERE email1 = '{$entity['EMAIL']}'";
		
		$returned = $db->query($query);
		
		while($row = $db->fetch_row($returned)) {
			$mailchimpMembersIds[] = $row['id'];
		}
	}
	
	echo "<a href=\"javascript:$('results_10').toggle()\"> - Show / Hide Results</a>";
	echo "<pre id=\"results_10\">";
	echo "<b>Results: </b>";
	print_r($members);
	print_r($mailchimpMembersIds);
	echo "</pre><br /><br />";
	echo "<script type=\"text/javascript\">$('results_10').toggle(false)</script>";
	
	// 3. find those members that are in this vTiger Mailchimp campaign but NOT in the corresponding Mailchimp Interest Grouping
	
	$mailchimpMembersIds = array_unique($mailchimpMembersIds);
	
	
	sort($campaignMemberIds);
	sort($mailchimpMembersIds);
	
	$missingEntities = array_diff($campaignMemberIds, $mailchimpMembersIds);
	
	
	echo "<table>";
	echo "<tr><td><b>Mailchimp list Subscribers: </b></td><td>".implode(", ", $mailchimpMembersIds)."</td></tr>";
	echo "<tr><td><b>Members of vTiger Campaign: </b></td><td>".implode(", ", $campaignMemberIds)."</td></tr>";
	echo "<tr><td><b>Entities to Remove from vTiger Campaign: </b></td><td>".implode(", ", $missingEntities)."</td></tr>";
	echo "</table><br /><br />";
	
	
	// 4. if those members ARE in the syncdiff table then they have been removed from Mailchimp grouping, and must be removed from vTiger campaign
	
	$entitiesToRemove = array();
	
	foreach ($missingEntities as $entity) {
		$sqlquery = "SELECT DISTINCT relcrmid FROM ".$table_prefix."_mailchimpsyncdiff WHERE crmid = ".$record;
		$sqlresult = $db->query($sqlquery);
		
		while($row = $db->fetch_row($sqlresult)) {
			$entitiesToRemove[] = $row['relcrmid'];
		}
	}
	
	//print_r($entitiesToRemove);
	
	$queries_string = '';
	
	foreach ($entitiesToRemove as $remove_id) {
		$sql = "DELETE FROM ".$table_prefix."_crmentityrel WHERE relcrmid = $remove_id AND crmid = $record";
		$queries_string .= $sql.";<br />";
	}
	
	echo "<a href=\"javascript:$('query_8').toggle()\"> - Show / Hide Queries</a>";
	echo "<pre id=\"query_8\">";
	echo $queries_string;
	echo "</pre><br /><br />";
	echo "<script type=\"text/javascript\">$('query_8').toggle(false)</script>";

	$db->query($queries_string);
}

/**
* Set the last synchronization date 
*/
function setLastSyncDate(){
	global $record;
	global $table_prefix;
	$db = PearDatabase::getInstance();
	$currentDate = date('Y-m-d H:i:s', time()-60*60*1); //lastsyncdate minus 2hours
	//echo $currentDate;
	$query = 'UPDATE  '.$table_prefix.'_mailchimpsync SET  '.$table_prefix.'_mailchimpsync.lastsynchronization = "'.$currentDate.'" 
				WHERE  '.$table_prefix.'_mailchimpsync.mailchimpsyncid = '.$record;
	//echo '<br>'.$query.'<br/>';
	
	$db->query($query);
}

/**
* Delete the last synchronization date in order to make a full synchronization
*/
function deleteLastSyncDate(){
	
	echo "Deleting the last synchronization date in order to make a full synchronization<br /><br />";
	
	global $record;
	global $table_prefix;
	global $module_name;
	$db = PearDatabase::getInstance();
	$currentDate = date('Y-m-d H:i:s');
	//echo $currentDate;
	$query = 'UPDATE '.$table_prefix.'_mailchimpsync SET '.$table_prefix.'_mailchimpsync.lastsynchronization = "" 
				WHERE '.$table_prefix.'_mailchimpsync.mailchimpsyncid = '.$record;
	//echo '<br>'.$query.'<br/>';
	$db->query($query);
}

/**
* Get the last synchronization date 
*/
function getLastSyncDate(){
	
	echo "Getting last synchronization date<br /><br />";
	global $table_prefix;
	global $record;
	global $module_name;
	$db = PearDatabase::getInstance();
	$query = 'SELECT * FROM '.$table_prefix.'_mailchimpsync WHERE '.$table_prefix.'_mailchimpsync.mailchimpsyncid = '.$record;
	$result = $db->query($query);
	
	while($donnee = $db->fetch_row($result))
	{
		return $donnee['lastsynchronization'];
	}
}

/**
* Add contact or account to a Mail Campaign, if the contact does not exist, we create it in vtiger. (The email address is considered as a primary key)
* $batch : Multidimensional array or email addresse, first name, last name and company
*/
function addContacts($batch) {
	
	echo "<br /><b>Starting process of adding contact, account or lead to a Mail Campaign, if it doesn't exist, we create it in vtiger....</b><br /><br />";
	global $table_prefix;
	global $adb;
	global $current_user;
	global $record;
	global $module_name;
	global $NewSubscriberType;
	require_once('modules/Users/Users.php');
	require_once('modules/Contacts/Contacts.php');
	require_once('modules/Leads/Leads.php');
	$user_name = 'admin';
	$result_email = $list_email = array();
	
	$db = PearDatabase::getInstance();
	
	$seed_user = new Users();
	$user_id = $seed_user->retrieve_user_id($user_name);
	$current_user = $seed_user;
	$current_user->retrieve_entity_info($user_id,"Users");
	require('user_privileges/user_privileges_'.$current_user->id.'.php');
	require('user_privileges/sharing_privileges_'.$current_user->id.'.php');

	if($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
		$sql1 = "select fieldname,columnname from ".$table_prefix."_field where tabid=4 and block <> 75 and block <> 6 and block <> 5 and ".$table_prefix."_field.presence in (0,2)";
		$params1 = array();
	} else {
		$profileList = getCurrentUserProfileList();
		$sql1 = "select fieldname,columnname from ".$table_prefix."_field inner join ".$table_prefix."_profile2field on ".$table_prefix."_profile2field.fieldid=".$table_prefix."_field.fieldid inner join ".$table_prefix."_def_org_field on ".$table_prefix."_def_org_field.fieldid=".$table_prefix."_field.fieldid where ".$table_prefix."_field.tabid=4 and ".$table_prefix."_field.block <> 75 and ".$table_prefix."_field.block <> 6 and ".$table_prefix."_field.block <> 5 and ".$table_prefix."_field.displaytype in (1,2,4) and ".$table_prefix."_profile2field.visible=0 and ".$table_prefix."_def_org_field.visible=0 and ".$table_prefix."_field.presence in (0,2)";
		$params1 = array();
		if (count($profileList) > 0) {
			$sql1 .= " and ".$table_prefix."_profile2field.profileid in (". generateQuestionMarks($profileList) .")";
			array_push($params1, $profileList);
		}
	}
	$result1 = $adb->pquery($sql1, $params1);

	for($i=0;$i < $adb->num_rows($result1);$i++)
	{
		$permitted_lists[] = $adb->query_result($result1,$i,'fieldname');
	}

	/* We do a list of each email adresses */
	
	echo "Creating a list of email addresses from Batch <br /><br />";
	
	foreach($batch as $member){
		$string_email .= '"'.$member['EMAIL'].'",';
		$list_email[] = $member['EMAIL'];
	}
	$string_email = rtrim($string_email, ",");
	
	echo "<a href=\"javascript:$('results_5').toggle()\"> - Show / Hide Results</a>";
	echo "<pre id=\"results_5\">";
	echo "<b>Results: </b>";
	print_r($list_email);
	echo $string_email."<br /><br />";
	echo "</pre><br /><br />";
	echo "<script type=\"text/javascript\">$('results_5').toggle(false)</script>";
	
	
	echo "Checking if any of the email addresses already exist in the database<br /><br />";
	
	/*
	 *  SELECT crmid FROM ".$table_prefix."_crmentity
		LEFT JOIN ".$table_prefix."_contactdetails ON contactdetails.contactid = ".$table_prefix."_crmentity.crmid
		LEFT JOIN ".$table_prefix."_account ON ".$table_prefix."_account.accountid = ".$table_prefix."_crmentity.crmid
		LEFT JOIN ".$table_prefix."_leaddetails ON ".$table_prefix."_leaddetails.leadid = ".$table_prefix."_crmentity.crmid
		WHERE ".$table_prefix."_crmentityrel.crmid = $record
		AND (".$table_prefix."_contactdetails.email = '$email' OR ".$table_prefix."_account.email1 = '$email' OR ".$table_prefix."_leaddetails.email = '')
	 */

	$query = "SELECT 'Contacts' as type, ".$table_prefix."_contactdetails.contactid as id, ".$table_prefix."_contactdetails.email as email 
				FROM ".$table_prefix."_crmentity, ".$table_prefix."_contactdetails 
				LEFT OUTER JOIN ".$table_prefix."_crmentityrel
				ON ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_crmentityrel.relcrmid
				AND ".$table_prefix."_crmentityrel.crmid = ".$record."
				WHERE ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_crmentity.crmid
				AND ".$table_prefix."_crmentity.deleted = 0
				AND ".$table_prefix."_contactdetails.email IN (".$string_email.")
			UNION
			  SELECT 'Accounts' as type, ".$table_prefix."_account.accountid as id, ".$table_prefix."_account.email1 as email
				FROM ".$table_prefix."_crmentity, ".$table_prefix."_account
				LEFT OUTER JOIN ".$table_prefix."_crmentityrel
				ON ".$table_prefix."_account.accountid = ".$table_prefix."_crmentityrel.relcrmid
				AND ".$table_prefix."_crmentityrel.crmid = ".$record."
				WHERE ".$table_prefix."_account.accountid = ".$table_prefix."_crmentity.crmid
				AND ".$table_prefix."_crmentity.deleted = 0
				AND ".$table_prefix."_account.email1 IN (".$string_email.")
			UNION
			  SELECT 'Leads' as type, ".$table_prefix."_leaddetails.leadid as id, ".$table_prefix."_leaddetails.email as email
				FROM ".$table_prefix."_crmentity, ".$table_prefix."_leaddetails
				LEFT OUTER JOIN ".$table_prefix."_crmentityrel
				ON ".$table_prefix."_leaddetails.leadid = ".$table_prefix."_crmentityrel.relcrmid
				AND ".$table_prefix."_crmentityrel.crmid = ".$record."
				WHERE ".$table_prefix."_leaddetails.leadid = ".$table_prefix."_crmentity.crmid
				AND ".$table_prefix."_crmentity.deleted = 0
				AND ".$table_prefix."_leaddetails.email IN (".$string_email.")";

	echo "<a href=\"javascript:$('query_6').toggle()\"> - Show / Hide Query</a>";
	echo "<pre id=\"query_6\">";
	echo preg_replace("/\t/", "", $query);
	echo "</pre><br /><br />";
	echo "<script type=\"text/javascript\">$('query_6').toggle(false)</script>";
	
	$result = $db->query($query);
	
	if (!$result) return false;
	
	echo "Looping through matches, if the email is in the database, but is not subscribed to the Mail Campaign, we need to add the matching contact/account/lead to a list for later adding to the vTiger Mailchimp Campaign<br /><br />";
	
	while($donnee = $db->fetch_row($result)){
		$result_email[] = $donnee['email'];
		// If the email is in the database, but is not subscribed to the Mail Campaign, we need to add the matching contact/account to the Mail Campaign
		if(empty($donnee['relcrmid'])){
			$subcribe_to_mailcampaign[] = array('type' => $donnee['type'], 'id' => $donnee['id'], 'email' => $donnee['email']);
		}
	}
	
	
	echo "<a href=\"javascript:$('results_6').toggle()\"> - Show / Hide Results</a>";
	echo "<pre id=\"results_6\">";
	echo "<b>Results: </b>";
	print_r($subcribe_to_mailcampaign);
	echo "</pre><br /><br />";
	echo "<script type=\"text/javascript\">$('results_6').toggle(false)</script>";
	
	
	// We add the account/contact to the Mail Campaign
	if(!empty($subcribe_to_mailcampaign)){
		
		echo "Loop through the list and enter the relation between the contact/account/lead and the vTiger Mailchimp Campaign<br /><br />";
		
		$query_strings = '';
		
		foreach($subcribe_to_mailcampaign as $members){
			
			// first make sure that an entity with this email address does alredy exist on this list (in case two or more entities share the same email address)
			
			$verify = "SELECT crmid FROM ".$table_prefix."_crmentityrel
						LEFT JOIN ".$table_prefix."_contactdetails ON ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_crmentityrel.relcrmid
						LEFT JOIN ".$table_prefix."_account ON ".$table_prefix."_account.accountid = ".$table_prefix."_crmentityrel.relcrmid
						LEFT JOIN ".$table_prefix."_leaddetails ON ".$table_prefix."_leaddetails.leadid = ".$table_prefix."_crmentityrel.relcrmid
						WHERE ".$table_prefix."_crmentityrel.crmid = $record
						AND (".$table_prefix."_contactdetails.email = '{$members['email']}' OR ".$table_prefix."_account.email1 = '{$members['email']}' OR ".$table_prefix."_leaddetails.email = '{$members['email']}')";
			
			$entityExists = $db->query($verify);
			
			while($donnee = $db->fetch_row($entityExists)) $entityData = $donnee['crmid'];
			
			if ($entityData) continue;
				
			$query2 = "INSERT INTO ".$table_prefix."_crmentityrel VALUES ('".$record."',  '".$module_name."',  '".$members['id']."',  '".$members['type']."')";
			
			$query_strings .= $query2.';<br />';
			$db->query($query2);
		}
		
		echo "<a href=\"javascript:$('results_7').toggle()\"> - Show / Hide Queries</a>";
		echo "<pre id=\"results_7\">";
		echo "<b>Results: <br /></b>";
		print_r($query_strings);
		echo "</pre><br /><br />";
		echo "<script type=\"text/javascript\">$('results_7').toggle(false)</script>";
		
	}
	
	echo "Creating a list of contacts for each email that is not in the database, for adding this contact to the vTiger Mailchimp Campaign<br /><br />";
	
	/* We create a contact for each email that is not in the database, and we add this contact to the Mail Campaign*/
	$emails_to_add = array_diff($list_email, $result_email);
	
	//echo '<h4>contacts to create</h4>';

	if(!empty($emails_to_add)){	
		
		$query_string = '';
		
		echo "Iterate through batch, if the record's email address is in the list of emails to add to vTiger Mailchimp Campaign, then create a new contact or lead (based on user settings), and add the new contact / lead to this vTiger Mailchimp Campaigns<br /><br />";
	
		foreach($batch as $member){
			
			if(in_array($member['EMAIL'], $emails_to_add)){
			
				$first_name = $member['FNAME'];
				$last_name = (is_array($member['LNAME'])) ? implode(' ', $member['LNAME']) : $member['LNAME'];
				$email_address = $member['EMAIL'];
				$company = $member['COMPANY'];
				
				// If the email is related to a company, either we create an account for this contact, or we assign the existing account to the new contact, using the account's id
				if($company != ''){
					$account_id = retrieve_account_id($company, $user_id);
				}
				
				// check whether or not user has specified whether subscriber should be added as contact or lead
				
				if ($NewSubscriberType == 'contact') { 
					$query_string .= "Saving $first_name $last_name ($email_address) as new CONTACT<br />";
					$contact = new Contacts();
					$contact->column_fields[firstname]=in_array('firstname',$permitted_lists) ? $first_name : "";
					$contact->column_fields[lastname]=in_array('lastname',$permitted_lists) ? $last_name : "";	
					$contact->column_fields[email]=in_array('email',$permitted_lists) ? $email_address : "";
					$contact->column_fields[account_id]=in_array('account_id',$permitted_lists) ? $account_id : "";
					$contact->column_fields[assigned_user_id]=in_array('assigned_user_id',$permitted_lists) ? $user_id : "";
					$contact->save("Contacts");
					$id = $contact->id;
				}
				else {
					$query_string .= "Saving $first_name $last_name ($email_address) as new LEAD<br />";
					$lead = new Leads();
					$lead->column_fields[firstname]=in_array('firstname',$permitted_lists) ? $first_name : "";
					$lead->column_fields[lastname]=in_array('lastname',$permitted_lists) ? $last_name : "";	
					$lead->column_fields[email]=in_array('email',$permitted_lists) ? $email_address : "";
					$lead->column_fields[account_id]=in_array('account_id',$permitted_lists) ? $account_id : "";
					$lead->column_fields[assigned_user_id]=in_array('assigned_user_id',$permitted_lists) ? $user_id : "";
					$lead->save("Leads");
					$id = $lead->id;
				}
				
				
					
				// first make sure that this entry isn't already in the database
				$tempsql = "SELECT FROM ".$table_prefix."_crmentityrel WHERE crmid = $record AND relcrmid = ".$id;
				$tempresult = $db->query($tempsql);
				
				$temp = false;
				if ($tempresult) {
					while($mailcheck = $db->fetch_row($tempresult)){
						$temp = $mailcheck;
					}
				}

			
				if (!$temp) {
					$query3 = 'INSERT INTO '.$table_prefix.'_crmentityrel VALUES ("'.$record.'",  "'.$module_name.'",  "'.$id.'",  "Contacts")';
					$query_string .= $query3.'<br/><br />';
					$db->query($query3);
				}
				
				
			}
		}
		
		echo "<a href=\"javascript:$('results_8').toggle()\"> - Show / Hide Queries</a>";
		echo "<pre id=\"results_8\">";
		echo "<b>Results: <br /></b>";
		print_r($query_string);
		echo "</pre><br /><br />";
		echo "<script type=\"text/javascript\">$('results_8').toggle(false)</script>";
		
	}		
}

/**
* Get the id of the account whose name is in the parameters, if this account does not exist, we create it
*/
function retrieve_account_id($account_name,$user_id){
	
	echo "<b>Retrieving account id</b><br /><br />";
	global $table_prefix;
	if($account_name=="")
	{
		return null;
	}

	$db = PearDatabase::getInstance();
	
	$query = "select ".$table_prefix."_account.accountname accountname,".$table_prefix."_account.accountid accountid from ".$table_prefix."_account inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=".$table_prefix."_account.accountid where ".$table_prefix."_crmentity.deleted=0 and ".$table_prefix."_account.accountname=?";
	$result=  $db->pquery($query, array($account_name)) or die ("Not able to execute insert");

	$rows_count =  $db->getRowCount($result);
	if($rows_count==0)
	{
		require_once('modules/Accounts/Accounts.php');
		$account = new Accounts();
		$account->column_fields[accountname] = $account_name;
		$account->column_fields[assigned_user_id]=$user_id;
		//$account->saveentity("Accounts");
		$account->save("Accounts");
		//mysql_close();
		return $account->id;
	}
	else if ($rows_count==1)
	{
		$row = $db->fetchByAssoc($result, 0);
		//mysql_close();
		return $row["accountid"];
	}
	else
	{
		$row = $db->fetchByAssoc($result, 0);
		//mysql_close();
		return $row["accountid"];
	}
}

/**
* Remove duplicates from a multidimensional array
*/
function uniqueArray($sync_array) {
	
	//echo "Remove duplicates from a multidimensional array<br /><br />";
        $rslt_array = array();
        $known_email = array();

        foreach ($sync_array as $entry) {
				$email = $entry["EMAIL"];
				$bool = in_array($email, $known_email);
                if(!$bool){
                        $rslt_array[] = $entry;
                        $known_email[] = $entry["EMAIL"];
                }
        }
        return $rslt_array;
}

/**
* Update the difftable of the Mail Campaign at the end of the synchronization
*/
function updateVtigerDiffTable(){
	
	echo "Updating the difftable of the Mail Campaign at the end of the synchronization<br /><br />";
	global $table_prefix;
	global $module_name;
	global $record;
	$db = PearDatabase::getInstance();
	
	$query = 'DELETE FROM '.$table_prefix.'_mailchimpsyncdiffWHERE crmid='.$record;
	$db->query($query);
	
	$query2 = 'INSERT INTO '.$table_prefix.'_mailchimpsyncdiffSELECT * FROM '.$table_prefix.'_crmentityrel WHERE '.$table_prefix.'_crmentityrel.crmid ='.$record;
	$db->query($query2);
	
	echo "</blockquote>";
}


/**
* Update the difftable of the Mail Campaign at the end of the synchronization
*/
function updateVtigerSyncDiffTable(){
	
	echo "Updating the difftable of the Mailchimp Target at the end of the synchronization<br /><br />";
	global $table_prefix;
	global $module_name;
	global $record;
	$db = PearDatabase::getInstance();
	
	$query = "DELETE FROM ".$table_prefix."_mailchimpsyncdiff WHERE crmid=".$record;
	$db->query($query);
	
	$query2 = "INSERT INTO ".$table_prefix."_mailchimpsyncdiff 
				SELECT ".$table_prefix."_crmentityrel.* FROM ".$table_prefix."_crmentityrel 
				JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_crmentityrel.relcrmid AND ".$table_prefix."_crmentity.deleted = 0
				WHERE ".$table_prefix."_crmentityrel.relmodule IN ('Accounts','Contacts','Leads') AND  ".$table_prefix."_crmentityrel.crmid = ?";
	$db->pquery($query2,array($record));
	
	echo "</blockquote>";
}

?>
