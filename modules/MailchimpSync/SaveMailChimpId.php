<?php
/*********************************************************************************
 * The contents of this file are copyright to Target Integration Ltd and are governed
 * by the license provided with the application. You may not use this file except in 
 * compliance with the License.
 * For support please visit www.targetintegration.com 
 * or email support@targetintegration.com
 * All Rights Reserved.
 *********************************************************************************/
global $adb, $table_prefix;

if(isset($_REQUEST['apikey']))
{
	$apikey = $_REQUEST['apikey'];
	$listid = $_REQUEST['listid'];
	$newsubscribertype = $_REQUEST['newsubscriber'];
        
        $sql = "DELETE FROM ".$table_prefix."_mailchimp_settings WHERE id=1";
        $adb->pquery($sql, Array());
        
        $sql = "INSERT INTO  ".$table_prefix."_mailchimp_settings (id,apikey,listid,newsubscribertype) VALUES (1,'$apikey','$listid','$newsubscribertype')";
        $adb->pquery($sql, Array());
        
        
        
	$readhandle = @fopen('modules/MailchimpSync/SyncWithMailChimpUtils.php', "r+");

	if($readhandle)
	{
		$buffer = '';
		$new_buffer = '';
		while(!feof($readhandle)) 
		{
			$buffer = fgets($readhandle, 5200);
			list($starter, $tmp) = explode(" = ", $buffer);

			if($starter == '$MailChimpAPIKey')
			{
				$new_buffer .= "\$MailChimpAPIKey = '".$apikey."';\n";
			}
			else if($starter == '$MailChimpListId')
			{
				$new_buffer .= "\$MailChimpListId = '".$listid."';\n";
			}
			else if($starter == '$NewSubscriberType')
			{
				$new_buffer .= "\$NewSubscriberType = '".$newsubscribertype."';\n";
			}
			else
				$new_buffer .= $buffer;

		}
		fclose($readhandle);
	}

	$handle = fopen('modules/MailchimpSync/SyncWithMailChimpUtils.php', "w");
	fputs($handle, $new_buffer);
	fclose($handle);

}
?>