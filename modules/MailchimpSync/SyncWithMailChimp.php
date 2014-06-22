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

global $record;
global $module_name;
global $list_id;

$module_nameurl = $_GET['module'];
require_once('modules/'.$module_nameurl.'/SyncWithMailChimpUtils.php');
$module_name = strtolower($module_nameurl);
$list_id = $MailChimpListId; //getListId();

$record_array = array();
$src_record = $_GET['src_record'];
$src_module = $_GET['src_module'];
if( $src_module == "MailchimpSync"  )
{
	$record_array = getTargetsToSync($src_record);
} else {
	$record_array[] = $src_record;
}

foreach($record_array as $record_item) {
	$record = $record_item;
	echo "<h2> &nbsp; &nbsp; Starting Synchronization for $src_module $record </h2><blockquote>";

	
	// Add subscribed contact to mailchimp since last sync
	syncTargetsWithMailChimp();
	
	// Remove from mailchimp since last sync
	syncUnsubscribedWithMailChimp();
	
	// Add subscribers from Mailchimp to vTiger
	// $dump = getListMembers('updated');
	
	// Remove subscribers from Mailchimp to vTiger
	// getListMembers('unsubscribed', $dump);
	
	// Set New sync date and update diff table
	// setLastSyncDate();
	updateVtigerSyncDiffTable();
	syncCampaings();
	// https://admin.mailchimp.com/campaigns/show?id=c_web_id
	// https://admin.mailchimp.com/lists/members/view?id=m_web_id
}
echo "<br /><br /> &nbsp; &nbsp; &nbsp; <a href=\"index.php?action=DetailView&module=$src_module&record=$record&parenttab=Marketing\">Click Here to Return to the $src_module Page</a>";


?>