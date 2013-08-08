<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the 
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
/*********************************************************************************
 * $Header: /advent/projects/wesat/vtiger_crm/sugarcrm/modules/Emails/Emails.php,v 1.41 2005/04/28 08:11:21 rank Exp $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

include_once('config.php');
require_once('include/logging.php');
require_once('include/database/PearDatabase.php');
require_once('data/SugarBean.php');
require_once('data/CRMEntity.php');
require_once('modules/Contacts/Contacts.php');
require_once('modules/Accounts/Accounts.php');
require_once('modules/Potentials/Potentials.php');
require_once('modules/Users/Users.php');

// Email is used to store customer information.
class Emails extends CRMEntity {
	var $log;
	var $db;
	var $table_name;
	var $table_index= 'activityid';
	// Stored vtiger_fields
  	// added to check email save from plugin or not
	var $plugin_save = false;

	var $rel_users_table ;
	var $rel_contacts_table;
	var $rel_serel_table;

	var $tab_name = Array();
	var $tab_name_index = Array();

	// This is the list of vtiger_fields that are in the lists.
	var $list_fields = Array(
		'LBL_FROM'=>array('emaildetails'=>'from_email'), //crmv@30521
		'Subject'=>Array('activity'=>'subject'),
		'Related to'=>Array('seactivityrel'=>'parent_id'),
		'Date Sent'=>Array('activity'=>'date_start'),
		'Assigned To'=>Array('crmentity','smownerid'),
		'Access Count'=>Array('email_track','access_count')
	);

	var $list_fields_name = Array(
		'LBL_FROM'=>'from_email', //crmv@30521
		'Subject'=>'subject',
		'Related to'=>'parent_id',
		'Date Sent'=>'date_start',
		'Assigned To'=>'assigned_user_id',
		'Access Count'=>'access_count'
	);

	var $list_link_field= 'subject';

	var $column_fields = Array();

	var $sortby_fields = Array('subject','date_start','saved_toid');

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'date_start';
	var $default_sort_order = 'ASC';

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('subject','assigned_user_id');
	
	//crmv@32079
	var $default_account = array(
		'smtp' => array(
			'Gmail' => array(
				'server'=>'ssl://smtp.gmail.com',
				'server_port'=>'465',
				'server_username'=>'username@gmail.com',
				'server_password'=>'required',
				'smtp_auth'=>'checked',
				'note'=>'LBL_GMAIL_SMTP_INFO',
			),
			'Hotmail' => array(
				'server'=>'smtp.live.com',
				'server_port'=>'587',
				'server_username'=>'username@hotmail.com',
				'server_password'=>'required',
				'smtp_auth'=>'checked',
			),
			'Yahoo!' => array(
				'server'=>'smtp.mail.yahoo.com',
				'server_port'=>'25',
				'server_username'=>'username@yahoo.com',
				'server_password'=>'required',
				'smtp_auth'=>'checked',
				'note'=>'LBL_YAHOO_SMTP_INFO',
			),
			'Exchange' => array(
				'server'=>'mail.example.com',
				'server_port'=>'25',
				'server_username'=>'username@example.com',
				'server_password'=>'required',
				'smtp_auth'=>'checked',
			),
			'Other' => array(
				'server'=>'smtp.example.com',
				'server_port'=>'25',
				'server_username'=>'',
				'smtp_auth'=>'',
			),
		),
		'imap' => array(
			'Gmail' => array(
				'server'=>'imap.gmail.com',
				'server_port'=>'993',
				'ssl_tls'=>'checked',
			),
			'Yahoo!' => array(
				'server'=>'imap-ssl.mail.yahoo.com',
				'server_port'=>'993',
				'ssl_tls'=>'checked',
			),
			'Exchange' => array(
				'server'=>'mail.example.com',
				'server_port'=>'993',
				'ssl_tls'=>'checked',
				'domain'=>'example.com',
			),
			'Other' => array(
				'server'=>'imap.example.com',
				'server_port'=>'143',
				'ssl_tls'=>'',
			),
		),
	);
	//crmv@32079e
	
	/** This function will set the columnfields for Email module 
	*/

	function Emails() {
		global $table_prefix;
		$this->table_name = $table_prefix."_activity";
		$this->rel_users_table = $table_prefix."_salesmanactivityrel";
		$this->rel_contacts_table = $table_prefix."_cntactivityrel";
		$this->rel_serel_table = $table_prefix."_seactivityrel";
		$this->tab_name = Array($table_prefix.'_crmentity',$table_prefix.'_activity',$table_prefix.'_emaildetails');
		$this->tab_name_index = Array($table_prefix.'_crmentity'=>'crmid',$table_prefix.'_activity'=>'activityid',
				$table_prefix.'_seactivityrel'=>'activityid',$table_prefix.'_cntactivityrel'=>'activityid',$table_prefix.'_email_track'=>'mailid',$table_prefix.'_emaildetails'=>'emailid');
		$this->log = LoggerManager::getLogger('email');
		$this->log->debug("Entering Emails() method ...");
		$this->log = LoggerManager::getLogger('email');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Emails');
		$this->log->debug("Exiting Email method ...");
	}


	function save_module($module)
	{
		global $adb,$table_prefix;
		//Inserting into seactivityrel

		//modified by Richie as raju's implementation broke the feature for addition of webmail to vtiger_crmentity.need to be more careful in future while integrating code
		if($_REQUEST['module']=="Emails" && $_REQUEST['smodule']!='webmails' && (!$this->plugin_save))
		{
			if($_REQUEST['currentid']!='')
			{
				$actid=$_REQUEST['currentid'];
			}
			else
			{
				$actid=$_REQUEST['record'];
			}
			$parentid=$_REQUEST['parent_id'];
			if($_REQUEST['module'] != 'Emails' && $_REQUEST['module'] != 'Webmails')
			{
				if(!$parentid) {
					$parentid = $adb->getUniqueID($table_prefix.'_seactivityrel');
				}
				$mysql='insert into '.$table_prefix.'_seactivityrel values(?,?)';
				$adb->pquery($mysql, array($parentid, $actid));
			}
			else
			{
				$myids=explode("|",$parentid);  //2@71|
				for ($i=0;$i<(count($myids)-1);$i++)
				{
					//crmv@25472
					if ($myids[$i] == '') {
						continue;
					}
					//crmv@25472e
					$realid=explode("@",$myids[$i]);
					$mycrmid=$realid[0];
					//added to handle the relationship of emails with vtiger_users
					if($realid[1] == -1)
					{
						$del_q = 'delete from '.$table_prefix.'_salesmanactivityrel where smid=? and activityid=?';
						$adb->pquery($del_q,array($mycrmid, $actid));
						$mysql='insert into '.$table_prefix.'_salesmanactivityrel values(?,?)';
					}
					else
					{
						$del_q = 'delete from '.$table_prefix.'_seactivityrel where crmid=? and activityid=?';
						$adb->pquery($del_q,array($mycrmid, $actid));
						$mysql='insert into '.$table_prefix.'_seactivityrel values(?,?)';
					}
					$params = array($mycrmid, $actid);
					$adb->pquery($mysql, $params);
				}
			}
		}
		else
		{
			if(isset($this->column_fields['parent_id']) && $this->column_fields['parent_id'] != '')
			{
				//crmv@fix emails ws
				$parentid = $this->column_fields['parent_id'];
				$myids=explode("|",$parentid);  //2@71|
				for ($i=0;$i<(count($myids)-1);$i++)
				{
					//crmv@25472
					if ($myids[$i] == '') {
						continue;
					}
					//crmv@25472e
					$realid=explode("@",$myids[$i]);
					$mycrmid=$realid[0];
					//added to handle the relationship of emails with vtiger_users
					if($realid[1] == -1)
					{
						$del_q = 'delete from '.$table_prefix.'_salesmanactivityrel where smid=? and activityid=?';
						$adb->pquery($del_q,array($mycrmid, $actid));
						$mysql='insert into '.$table_prefix.'_salesmanactivityrel values(?,?)';
					}
					else
					{
						$del_q = 'delete from '.$table_prefix.'_seactivityrel where crmid=? and activityid=?';
						$adb->pquery($del_q,array($mycrmid, $this->id));
						$mysql='insert into '.$table_prefix.'_seactivityrel values(?,?)';
					}
					$params = array($mycrmid, $this->id);
					$adb->pquery($mysql, $params);
				}				
				//crmv@fix emails ws
			}
			elseif($this->column_fields['parent_id']=='' && $insertion_mode=="edit")
			{
				$this->deleteRelation($table_prefix.'_seactivityrel');
			}
		}


		//Insert into cntactivity rel

		if(isset($this->column_fields['contact_id']) && $this->column_fields['contact_id'] != '')
		{
			$this->insertIntoEntityTable(''.$table_prefix.'_cntactivityrel', $module);
		}
		elseif($this->column_fields['contact_id'] =='' && $insertion_mode=="edit")
		{
			$this->deleteRelation($table_prefix.'_cntactivityrel');
		}
			
		//Inserting into attachment
			
		$this->insertIntoAttachment($this->id,$module);

		//crmv@16265
		if ($_REQUEST['squirrelmail'] == 'true') {
			global $current_user;
			$squirrelvalues = Zend_Json::decode(urldecode($_REQUEST['squirrelvalues']));
			$folder = $squirrelvalues['mailbox'];	//crmv@30909
			$passed_id = $squirrelvalues['passed_id'];
			if ($passed_id != '') {
				//crmv@26721
				$passed_id = $squirrelvalues['passed_id'];
				if ($squirrelvalues['mass_link']) {
					$records_list = explode(',',$squirrelvalues['passed_id']);
					$current_passed_id = $records_list[$_REQUEST['current_passed_id']];
				}
				else {
					$current_passed_id = $passed_id;
				}
				//crmv@31109
				if (!in_array($squirrelvalues['action'],array('reply','reply_all','forward'))) {
					$adb->pquery('insert into crmv_squirrelmailrel values(?,?,?,?,?)',array($current_user->id,$current_passed_id,$this->id,$squirrelvalues['action'],$folder));
				}
				//crmv@31109e
				//crmv@26721e
				//crmv@20947
				/*
				if (in_array($squirrelvalues['action'],array('reply','reply_all'))) {
					$res = $adb->query("SELECT ".$table_prefix."_seactivityrel.crmid FROM ".$table_prefix."_seactivityrel
					INNER JOIN crmv_squirrelmailrel ON ".$table_prefix."_seactivityrel.activityid = crmv_squirrelmailrel.mail_id
					WHERE crmv_squirrelmailrel.imap_id = $current_passed_id");	//crmv@26721
					while($row=$adb->fetchByAssoc($res)) {
						$del_q = 'delete from '.$table_prefix.'_seactivityrel where crmid=? and activityid=?';
						$adb->pquery($del_q,array($row['crmid'], $this->id));
						$mysql='insert into '.$table_prefix.'_seactivityrel values(?,?)';
						$adb->pquery($mysql,array($row['crmid'], $this->id));
					}
				}
				*/
				//crmv@20947e
			}
		}
		//crmv@16265e
		
		//crmv@2043m
		if(isset($_REQUEST['reply_mail_converter']) && $_REQUEST['reply_mail_converter'] != '') {
			global $current_user;
			
			$del_q = 'delete from '.$table_prefix.'_seactivityrel where crmid=? and activityid=?';
			$adb->pquery($del_q,array($_REQUEST['reply_mail_converter_record'], $this->id));
			$mysql='insert into '.$table_prefix.'_seactivityrel values(?,?)';
			$adb->pquery($mysql,array($_REQUEST['reply_mail_converter_record'], $this->id));
			
			$HelpDeskFocus = CRMEntity::getInstance('HelpDesk');
			$HelpDeskFocus->retrieve_entity_info_no_html($_REQUEST['reply_mail_converter_record'], 'HelpDesk');
			$HelpDeskFocus->id = $_REQUEST['reply_mail_converter_record'];
			$HelpDeskFocus->mode = 'edit';
			if ($HelpDeskFocus->waitForResponseStatus != '') {
				$HelpDeskFocus->column_fields['ticketstatus'] = $HelpDeskFocus->waitForResponseStatus;
			}
			$HelpDeskFocus->column_fields['comments'] = strip_tags(html_entity_decode($this->column_fields['description'],ENT_COMPAT,'UTF-8'));
			$HelpDeskFocus->save('HelpDesk');
		}
		//crmv@2043me
	}


	function insertIntoAttachment($id,$module)
	{
		global $log, $adb, $current_user,$table_prefix;
		$log->debug("Entering into insertIntoAttachment($id,$module) method.");
		
		$file_saved = false;
		
		//Send document attachment
		if(isset($_REQUEST['pdf_attachment']) && $_REQUEST['pdf_attachment'] !='')
		{
			$file_saved = pdfAttach($this,$module,$_REQUEST['pdf_attachment'],$id);
		}

		//This is to added to store the existing attachment id of the contact where we should delete this when we give new image
		foreach($_FILES as $fileindex => $files)
		{
			if($files['name'] != '' && $files['size'] > 0)
			{
				$files['original_name'] = vtlib_purify($_REQUEST[$fileindex.'_hidden']);
				$file_saved = $this->uploadAndSaveFile($id,$module,$files);
			}
		}
		//crmv@22123
		$targetDir = 'storage/uploads_emails_'.$current_user->id;
		for($count_att=0;;$count_att++) {
			if (empty($_REQUEST['uploader_'.$count_att.'_tmpname'])) break;
			$files['name'] = $_REQUEST['uploader_'.$count_att.'_name'];
			$files['tmp_name'] = $targetDir."/".$_REQUEST['uploader_'.$count_att.'_tmpname'];
			$file_saved = $this->uploadAndSaveFile($id,$module,$files,true);
			//crmv@31456
			if(is_file($files['tmp_name']) && !isset($_REQUEST['save_in_draft'])){
				unlink($files['tmp_name']);
			}
			//crmv@31456e
		}
		//crmv@22123e
		if($module == 'Emails' && isset($_REQUEST['att_id_list']) && $_REQUEST['att_id_list'] != '')
		{
			$att_lists = explode(";",$_REQUEST['att_id_list'],-1);
			$id_cnt = count($att_lists);
			if($id_cnt != 0)
			{
				for($i=0;$i<$id_cnt;$i++)
				{
					$sql_rel='insert into '.$table_prefix.'_seattachmentsrel values(?,?)';
					$adb->pquery($sql_rel, array($id, $att_lists[$i]));
				}
			}			
		}
		if($_REQUEST['att_module'] == 'Webmails')
		{
			//crmv@16265
			if ($_REQUEST['squirrelmail'] == 'true') {
				
				$squirrelvalues = Zend_Json::decode(urldecode($_REQUEST['squirrelvalues']));

				global $color, $squirrelmail_language, $onetimepad, $use_imap_tls, $imap_auth_mech, $uid_support;
				$color = $squirrelvalues['color'];
				$squirrelmail_language = $squirrelvalues['squirrelmail_language'];
				$onetimepad = $squirrelvalues['onetimepad'];
				$use_imap_tls = $squirrelvalues['use_imap_tls'];
				$imap_auth_mech = $squirrelvalues['imap_auth_mech'];
				$uid_support = $squirrelvalues['uid_support'];
				
				//crmv@32484
				if ($_REQUEST['mass_link']) {
					$passed_id = $_REQUEST['record'];
				}else{
					$passed_id = $squirrelvalues['passed_id'];
				}
				//crmv@32484e
				$username = $squirrelvalues['username'];
				$key = $squirrelvalues['key'];
				$imapServerAddress = $squirrelvalues['imapServerAddress'];
				$imapPort = $squirrelvalues['imapPort'];
				$passed_ent_id = $squirrelvalues['passed_ent_id'];
				$action = $squirrelvalues['action'];
				$mailbox = $squirrelvalues['mailbox'];
	
				if (!in_array($action,array('forward','draft')))	return '';

				global $root_directory;
				chdir($root_directory.'include/squirrelmail/functions');
				define('SM_PATH','../');
				$tmp_theme = $theme;
				unset($theme);
				require_once('imap_general.php');
				if ($passed_id) {
					$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
			        sqimap_mailbox_select($imapConnection, $mailbox);
			        $message = sqimap_get_message($imapConnection, $passed_id, $mailbox);
					if ($show_html_default == 1) {
					    $ent_ar = $message->findDisplayEntity(array());
					} else {
					    $ent_ar = $message->findDisplayEntity(array(), array('text/plain'));
					}
					$att_ar = $message->getAttachments($ent_ar);
				    $attachments = '';
				    $urlMailbox = urlencode($mailbox);
				    foreach ($att_ar as $att) {
				    	
				    	$ent = $att->entity_id;
				        $header = $att->header;
			            
			            global $upload_badext;
			            $filename = decodeHeader($header->getParameter('name'));
			            if ($filename == '') continue;
           
			            $binFile = sanitizeUploadFileName($filename, $upload_badext);
						$filename = ltrim(basename(" ".$binFile)); //allowed filename like UTF-8 characters 
						$filetype = strtolower($header->type0).'/'.strtolower($header->type1);
						$filesize = $header->getParameter('size');
						$filepart = $header->getParameter('lines');
						$transfer = $header->getParameter('encoding');
						
						global $adb, $current_user;
						$current_id = $adb->getUniqueID($table_prefix."_crmentity");
						$date_var = date('Y-m-d H:i:s');
						//to get the owner id
						$ownerid = $this->column_fields['assigned_user_id'];
						if(!isset($ownerid) || $ownerid=='')
							$ownerid = $current_user->id;
						
						chdir($root_directory);
						$upload_file_path = decideFilePath();
						$fp = fopen($root_directory.$upload_file_path.$current_id."_".$filename, 'wb');
						chdir($root_directory.'include/squirrelmail/functions');
			            $att->initAttachment($att->type0.'/'.$att->type1,$filename,'');
			            mime_print_body_lines ($imapConnection, $passed_id, $att->entity_id, $att->header->encoding, $fp);
			            fclose ($fp);
			            
			            chdir($root_directory);
			            $sql1 = "insert into ".$table_prefix."_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?,?,?,?,?,?,?)";
                        $params1 = array($current_id, $current_user->id, $ownerid, $module." Attachment", $this->column_fields['description'], $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
						$adb->pquery($sql1, $params1);
						
                        $sql2="insert into ".$table_prefix."_attachments(attachmentsid, name, description, type, path) values(?,?,?,?,?)";
                        $params2 = array($current_id, $filename, $this->column_fields['description'], $filetype, $upload_file_path);
						$result=$adb->pquery($sql2, $params2);
						
                        if($_REQUEST['mode'] == 'edit')
                        {
                        	if($id != '' && $_REQUEST['fileid'] != '')
                            {
                                $delquery = 'delete from '.$table_prefix.'_seattachmentsrel where crmid = ? and attachmentsid = ?';
		                        $adb->pquery($delquery, array($id, $_REQUEST['fileid']));
			        		}
						}
                        $sql3='insert into '.$table_prefix.'_seattachmentsrel values(?,?)';
                        $adb->pquery($sql3, array($id, $current_id));
                        
                        //crmv@29506
					    if ($_REQUEST['mode'] != 'edit' && $_REQUEST['parent_id'] != '') {
					    	
					    	$recordid = substr($_REQUEST['parent_id'],0,strpos($_REQUEST['parent_id'],'@'));
					    	$related_module = getSalesEntityType($recordid);
					    	
					    	$DocumentsInstance = Vtiger_Module::getInstance('Documents');
					    	$RelatedInstance = Vtiger_Module::getInstance($related_module);
					    	$result = $adb->pquery('SELECT * FROM '.$table_prefix.'_relatedlists WHERE tabid = 2 AND related_tabid = 8',array($RelatedInstance->id,$DocumentsInstance->id));
					    	if ($result && $adb->num_rows($result) > 0) {
								// Create document record
								$document = CRMEntity::getInstance('Documents');
								$document->column_fields['notes_title']      = $filename;
								$document->column_fields['filename']         = $filename;
								$document->column_fields['filestatus']       = 1;
								$document->column_fields['filelocationtype'] = 'I';
								$document->column_fields['folderid']         = 1; // Default Folder 
								$document->column_fields['assigned_user_id'] = $ownerid;
								$document->column_fields['filesize'] = 0;
								$document->save('Documents');
	
								// Link file attached to document
								$adb->pquery("INSERT INTO ".$table_prefix."_seattachmentsrel(crmid, attachmentsid) VALUES(?,?)",Array($document->id, $current_id));
				
								// Link document to base record
								$adb->pquery("INSERT INTO ".$table_prefix."_senotesrel(crmid, notesid) VALUES(?,?)",Array($recordid, $document->id));
					    	}
						}
						//crmv@29506e
				    }
				}
				chdir($root_directory);
				$theme = $tmp_theme;
			}
			else {
			//crmv@16265e
				require_once("modules/Webmails/Webmails.php");
		        require_once("modules/Webmails/MailParse.php");
		        require_once('modules/Webmails/MailBox.php');
		        //$mailInfo = getMailServerInfo($current_user);
				//$temprow = $adb->fetch_array($mailInfo);
	
		        $MailBox = new MailBox($_REQUEST["mailbox"]);
		        $mbox = $MailBox->mbox;
		        $webmail = new Webmails($mbox,$_REQUEST['mailid']);
		        $array_tab = Array();
		        $webmail->loadMail($array_tab);
				if(isset($webmail->att_details)){
					foreach($webmail->att_details as $fileindex => $files)
					{
						if($files['name'] != '' && $files['size'] > 0)
						{
							//print_r($files);
							$file_saved = $this->saveForwardAttachments($id,$module,$files);
						}
					}
				}
			//crmv@16265
			}
			//crmv@16265e
		}
		$log->debug("Exiting from insertIntoAttachment($id,$module) method.");
	}	
	
	function saveForwardAttachments($id,$module,$file_details)
	{
		global $log,$table_prefix;
		$log->debug("Entering into saveForwardAttachments($id,$module,$file_details) method.");
		global $adb, $current_user;
		global $upload_badext;
		require_once('modules/Webmails/MailBox.php');
		$mailbox=$_REQUEST["mailbox"];
		$MailBox = new MailBox($mailbox);
		$mail = $MailBox->mbox;
		$binFile = sanitizeUploadFileName($file_details['name'], $upload_badext);
		$filename = ltrim(basename(" ".$binFile)); //allowed filename like UTF-8 characters 
		$filetype= $file_details['type'];
		$filesize = $file_details['size'];
		$filepart = $file_details['part'];
		$transfer = $file_details['transfer'];
		$file = imap_fetchbody($mail,$_REQUEST['mailid'],$filepart);
		if ($transfer == 'BASE64')
			$file = imap_base64($file);
		elseif($transfer == 'QUOTED-PRINTABLE')
			$file = imap_qprint($file);
		$current_id = $adb->getUniqueID($table_prefix."_crmentity");
		$date_var = date('Y-m-d H:i:s');
		//to get the owner id
		$ownerid = $this->column_fields['assigned_user_id'];
		if(!isset($ownerid) || $ownerid=='')
			$ownerid = $current_user->id;
		$upload_file_path = decideFilePath();
		file_put_contents ($upload_file_path.$current_id."_".$filename,$file);
		
		$sql1 = "insert into ".$table_prefix."_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?,?,?,?,?,?,?)";
		$params1 = array($current_id, $current_user->id, $ownerid, $module." Attachment", $this->column_fields['description'], $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
		$adb->pquery($sql1, $params1);

		$sql2="insert into ".$table_prefix."_attachments(attachmentsid, name, description, type, path) values(?,?,?,?,?)";
		$params2 = array($current_id, $filename, $this->column_fields['description'], $filetype, $upload_file_path);
		$result=$adb->pquery($sql2, $params2);

		if($_REQUEST['mode'] == 'edit')
		{
			if($id != '' && $_REQUEST['fileid'] != '')
			{
				$delquery = 'delete from '.$table_prefix.'_seattachmentsrel where crmid = ? and attachmentsid = ?';
				$adb->pquery($delquery, array($id, $_REQUEST['fileid']));
			}
		}
		$sql3='insert into '.$table_prefix.'_seattachmentsrel values(?,?)';
		$adb->pquery($sql3, array($id, $current_id));
		return true;
		$log->debug("exiting from  saveforwardattachment function.");
	}
	/** Returns a list of the associated contacts
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	*/
	function get_contacts($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user,$table_prefix;
		$log->debug("Entering get_contacts(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);
        vtlib_setup_modulevars($related_module, $other);		
		$singular_modname = vtlib_toSingular($related_module);
		
		$parenttab = getParentTab();
		
		$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		
		$button = '';
				
		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
			if(in_array('BULKMAIL', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_BULK_MAILS')."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"sendmail\";this.form.module.value=\"$this_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_BULK_MAILS')."'>";
			}
		}
				
		$query = 'select '.$table_prefix.'_contactdetails.accountid, '.$table_prefix.'_contactdetails.contactid, '.$table_prefix.'_contactdetails.firstname,'.$table_prefix.'_contactdetails.lastname, '.$table_prefix.'_contactdetails.department, '.$table_prefix.'_contactdetails.title, '.$table_prefix.'_contactdetails.email, '.$table_prefix.'_contactdetails.phone, '.$table_prefix.'_contactdetails.emailoptout, '.$table_prefix.'_crmentity.crmid, '.$table_prefix.'_crmentity.smownerid, '.$table_prefix.'_crmentity.modifiedtime from '.$table_prefix.'_contactdetails inner join '.$table_prefix.'_cntactivityrel on '.$table_prefix.'_cntactivityrel.contactid='.$table_prefix.'_contactdetails.contactid inner join '.$table_prefix.'_crmentity on '.$table_prefix.'_crmentity.crmid = '.$table_prefix.'_contactdetails.contactid left join '.$table_prefix.'_groups on '.$table_prefix.'_groups.groupid='.$table_prefix.'_crmentity.smownerid where '.$table_prefix.'_cntactivityrel.activityid='.$adb->quote($id).' and '.$table_prefix.'_crmentity.deleted=0';
				
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 
		
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_contacts method ...");		
		return $return_value;
	}
	
	/** Returns the column name that needs to be sorted
	 * Portions created by vtigerCRM are Copyright (C) vtigerCRM.
	 * All Rights Reserved..
	 * Contributor(s): Mike Crowe
	*/

	function getSortOrder()
	{	
		global $log;
		$log->debug("Entering getSortOrder() method ...");
		if(isset($_REQUEST['sorder'])) 
			$sorder = $this->db->sql_escape_string($_REQUEST['sorder']);
		else
			$sorder = (($_SESSION['EMAILS_SORT_ORDER'] != '')?($_SESSION['EMAILS_SORT_ORDER']):($this->default_sort_order));

		$log->debug("Exiting getSortOrder method ...");
		return $sorder;
	}

	/** Returns the order in which the records need to be sorted
	 * Portions created by vtigerCRM are Copyright (C) vtigerCRM.
	 * All Rights Reserved..
	 * Contributor(s): Mike Crowe
	*/

	function getOrderBy()
	{
		global $log;
		$log->debug("Entering getOrderBy() method ...");
		
		$use_default_order_by = '';		
		if(PerformancePrefs::getBoolean('LISTVIEW_DEFAULT_SORTING', true)) {
			$use_default_order_by = $this->default_order_by;
		}
		
		if (isset($_REQUEST['order_by'])) 
			$order_by = $this->db->sql_escape_string($_REQUEST['order_by']);
		else
			$order_by = (($_SESSION['EMAILS_ORDER_BY'] != '')?($_SESSION['EMAILS_ORDER_BY']):($use_default_order_by));

		$log->debug("Exiting getOrderBy method ...");
		return $order_by;
	}	
	// Mike Crowe Mod --------------------------------------------------------

	/** Returns a list of the associated vtiger_users
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	*/
	function get_users($id)
	{
		global $log;
		$log->debug("Entering get_users(".$id.") method ...");
		global $adb,$table_prefix;
		global $mod_strings;
		global $app_strings;

		$id = $_REQUEST['record'];

		$button = '<input title="'.getTranslatedString('LBL_BULK_MAILS').'" accessykey="F" class="crmbutton small create" 
				onclick="this.form.action.value=\"sendmail\";this.form.return_action.value=\"DetailView\";this.form.module.value=\"Emails\";this.form.return_module.value=\"Emails\";" 
				name="button" value="'.getTranslatedString('LBL_BULK_MAILS').'" type="submit">&nbsp;
				<input title="'.getTranslatedString('LBL_BULK_MAILS').'" accesskey="" tabindex="2" class="crmbutton small edit" 
				value="'.getTranslatedString('LBL_SELECT_USER_BUTTON_LABEL').'" name="Button" language="javascript" 
				onclick=\"return window.open("index.php?module=Users&return_module=Emails&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=true&return_id='.$id.'&recordid='.$id.'","test","width=640,height=520,resizable=0,scrollbars=0");\"
				type="button">';                  

		$query = 'SELECT '.$table_prefix.'_users.id, '.$table_prefix.'_users.first_name,'.$table_prefix.'_users.last_name, '.$table_prefix.'_users.user_name, '.$table_prefix.'_users.email1, '.$table_prefix.'_users.email2, '.$table_prefix.'_users.yahoo_id, '.$table_prefix.'_users.phone_home, '.$table_prefix.'_users.phone_work, '.$table_prefix.'_users.phone_mobile, '.$table_prefix.'_users.phone_other, '.$table_prefix.'_users.phone_fax from '.$table_prefix.'_users inner join '.$table_prefix.'_salesmanactivityrel on '.$table_prefix.'_salesmanactivityrel.smid='.$table_prefix.'_users.id and '.$table_prefix.'_salesmanactivityrel.activityid=?';
		$result=$adb->pquery($query, array($id));   

		$noofrows = $adb->num_rows($result);
		$header [] = $app_strings['LBL_LIST_NAME'];

		$header []= $app_strings['LBL_LIST_USER_NAME'];

		$header []= $app_strings['LBL_EMAIL'];

		$header []= $app_strings['LBL_PHONE'];
		while($row = $adb->fetch_array($result))
		{

			global $current_user;

			$entries = Array();

			if(is_admin($current_user))
			{
				$entries[] = $row['last_name'].' '.$row['first_name'];
			}
			else
			{
				$entries[] = $row['last_name'].' '.$row['first_name'];
			}		

			$entries[] = $row['user_name'];
			$entries[] = $row['email1'];
			if($email == '')        $email = $row['email2'];
			if($email == '')        $email = $row['yahoo_id'];

			$entries[] = $row['phone_home'];
			if($phone == '')        $phone = $row['phone_work'];
			if($phone == '')        $phone = $row['phone_mobile'];
			if($phone == '')        $phone = $row['phone_other'];
			if($phone == '')        $phone = $row['phone_fax'];

			//Adding Security Check for User

			$entries_list[] = $entries;
		}

		if($entries_list != '')
			$return_data = array("header"=>$header, "entries"=>$entries);
		
		if($return_data == null) $return_data = Array();
		$return_data['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_users method ..."); 
		return $return_data;
	}

	
	/**
	* Used to releate email and contacts -- Outlook Plugin
	*/  
	function set_emails_contact_invitee_relationship($email_id, $contact_id)
	{
		global $log;
		$log->debug("Entering set_emails_contact_invitee_relationship(".$email_id.",". $contact_id.") method ...");
		$query = "insert into $this->rel_contacts_table (contactid,activityid) values(?,?)";
		$this->db->pquery($query, array($contact_id, $email_id), true,"Error setting email to contact relationship: "."<BR>$query");
		$log->debug("Exiting set_emails_contact_invitee_relationship method ...");
	}
     
	/**
	* Used to releate email and salesentity -- Outlook Plugin
	*/
	function set_emails_se_invitee_relationship($email_id, $contact_id)
	{
		global $log;
		$log->debug("Entering set_emails_se_invitee_relationship(".$email_id.",". $contact_id.") method ...");
		$query = "insert into $this->rel_serel_table (crmid,activityid) values(?,?)";
		$this->db->pquery($query, array($contact_id, $email_id), true,"Error setting email to contact relationship: "."<BR>$query");
		$log->debug("Exiting set_emails_se_invitee_relationship method ...");
	}
     
	/**
	* Used to releate email and Users -- Outlook Plugin
	*/    
	function set_emails_user_invitee_relationship($email_id, $user_id)
	{
		global $log;
		$log->debug("Entering set_emails_user_invitee_relationship(".$email_id.",". $user_id.") method ...");
		$query = "insert into $this->rel_users_table (smid,activityid) values (?,?)";
		$this->db->pquery($query, array($user_id, $email_id), true,"Error setting email to user relationship: "."<BR>$query");
		$log->debug("Exiting set_emails_user_invitee_relationship method ...");
	}
	
	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log,$table_prefix;
		
		//crmv@26265
		$sql='DELETE FROM '.$table_prefix.'_seactivityrel WHERE activityid=? AND crmid=?';
		$this->db->pquery($sql, array($id, $return_id));
		//crmv@26265e
			
		$sql = 'DELETE FROM '.$table_prefix.'_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
		$params = array($id, $return_module, $return_id, $id, $return_module, $return_id);
		$this->db->pquery($sql, $params);
	}
	
	//crmv@26165
	function getCrmvForwardHeader() {
		global $adb,$table_prefix;
		$editor_size = 76;

		$query = 'select idlists,from_email,to_email,cc_email,bcc_email from '.$table_prefix.'_emaildetails where emailid =?';
		$result = $adb->pquery($query, array($this->id));
		$from_email = $adb->query_result($result,0,'from_email');
		$to_email_array = Zend_Json::decode($adb->query_result($result,0,'to_email'));
		$to_email = implode(',',$to_email_array);
		$cc_add = implode(',',Zend_Json::decode($adb->query_result($result,0,'cc_email')));

		$display = array(getTranslatedString('Subject','Webmails') => strlen(getTranslatedString("Subject",'Webmails')),
						getTranslatedString("From",'Webmails') => strlen(getTranslatedString("From",'Webmails')),
						getTranslatedString("Date",'Webmails') => strlen(getTranslatedString("Date",'Webmails')),
						getTranslatedString("To",'Webmails') => strlen(getTranslatedString("To",'Webmails')),
						getTranslatedString("Cc",'Webmails') => strlen(getTranslatedString("Cc",'Webmails'))
		);
		$maxsize = max($display);
		$indent = str_pad('',$maxsize+2);
		foreach($display as $key => $val) {
			$display[$key] = $key .': '. str_pad('', $maxsize - $val);
		}
		$from = $from_email;
		$from = str_replace('&nbsp;',' ',$from);
		$from = str_replace('<','&lt;',$from);
		$from = str_replace('>','&gt;',$from);
		$to = $to_email;
		$to = str_replace('&nbsp;',' ',$to);
		$to = str_replace('<','&lt;',$to);
		$to = str_replace('>','&gt;',$to);
		$subject = $this->column_fields['subject'];
		$subject = str_replace('&nbsp;',' ',$subject);
		$bodyTop =  str_pad(' '.getTranslatedString("Original Message",'Webmails').' ', $editor_size-2, '-', STR_PAD_BOTH) .
        			"<br />". $display[getTranslatedString("Subject",'Webmails')] . $subject . "<br />" .
		$display[getTranslatedString("From",'Webmails')] . htmlentities($from) . "<br />" .
		$display[getTranslatedString("Date",'Webmails')] . getDisplayDate($this->column_fields['date_start']) . "<br />" .
		$display[getTranslatedString("To",'Webmails')] . htmlentities($to) . "<br />";
		if ($cc_add != array() && $cc_add !='') {
			$cc = $cc_add;
			$cc = str_replace('&nbsp;',' ',$cc);
			$cc = str_replace('<','&lt;',$cc);
			$cc = str_replace('>','&gt;',$cc);
			$bodyTop .= $display[getTranslatedString("Cc",'Webmails')] . htmlentities($cc) . "<br />";
		}
		$bodyTop .= str_pad('', $editor_size-2+9, '-')."<br /><br />";
		return '<br />'.$bodyTop;
	}
	//crmv@26165e
	//crmv@2043m
	function getCrmvReplyHeader() {
		$orig_from = $this->column_fields['from_email'];
		$orig_date = $this->column_fields['date_start'];
		$full_reply_citation = sprintf(getTranslatedString("On %s, %s wrote:",'Webmails'), getDisplayDate($orig_date), $orig_from);
		return $full_reply_citation."\n";
	}
	//crmv@2043me
}
/** Function to get the emailids for the given ids form the request parameters 
 *  It returns an array which contains the mailids and the parentidlists
*/

function get_to_emailids($module)
{
	global $adb,$table_prefix;
	if(isset($_REQUEST["field_lists"]) && $_REQUEST["field_lists"] != "")
	{
		$field_lists = $_REQUEST["field_lists"];
		if($module=='Inspections') $field_lists = 8; // metterci field id degli account che corrisponde con l'indirizzo e-mail
		if (is_string($field_lists)) $field_lists = explode(":", $field_lists);
		$query = 'select columnname,fieldid from '.$table_prefix.'_field where fieldid in ('. generateQuestionMarks($field_lists) .') and '.$table_prefix.'_field.presence in (0,2)';
		$result = $adb->pquery($query, array($field_lists));
		$columns = Array();
		$idlists = '';
		$mailids = '';
		while($row = $adb->fetch_array($result))
    		{
			$columns[]=$row['columnname'];
			$fieldid[]=$row['fieldid'];
		}
		$columnlists = implode(',',$columns);
		//crmv@27096	//crmv@27917
		$idarray = getListViewCheck($module);
		if (empty($idarray)) {
			$idstring = $_REQUEST['idlist'];
		} else {
			$idstring = implode(':',$idarray);
		}
		//crmv@27096e	//crmv@27917e
		$single_record = false;
		if(!strpos($idstring,':'))
		{
			$single_record = true;
		}
		$crmids = ereg_replace(':',',',$idstring);
		$crmids = explode(",", $crmids);
		switch($module)
		{
			case 'Leads':
				$query = 'select crmid,'.$adb->sql_concat(Array('firstname',"' '",'lastname')).' as entityname,'.$columnlists.' from '.$table_prefix.'_leaddetails inner join '.$table_prefix.'_crmentity on '.$table_prefix.'_crmentity.crmid='.$table_prefix.'_leaddetails.leadid left join '.$table_prefix.'_leadscf on '.$table_prefix.'_leadscf.leadid = '.$table_prefix.'_leaddetails.leadid where '.$table_prefix.'_crmentity.deleted=0 and ((ltrim('.$table_prefix.'_leaddetails.email) is not null) or (ltrim('.$table_prefix.'_leaddetails.yahooid) is not null)) and '.$table_prefix.'_crmentity.crmid in ('. generateQuestionMarks($crmids) .')';
				break;
			case 'Contacts':
				//email opt out funtionality works only when we do mass mailing.
				if(!$single_record)
				$concat_qry = '(((ltrim('.$table_prefix.'_contactdetails.email) is not null)  or (ltrim('.$table_prefix.'_contactdetails.yahooid) is not null)) and ('.$table_prefix.'_contactdetails.emailoptout != 1)) and ';
				else
				$concat_qry = '((ltrim('.$table_prefix.'_contactdetails.email) is not null)  or (ltrim('.$table_prefix.'_contactdetails.yahooid) is not null)) and ';
				$query = 'select crmid,'.$adb->sql_concat(Array('firstname',"' '",'lastname')).' as entityname,'.$columnlists.' from '.$table_prefix.'_contactdetails inner join '.$table_prefix.'_crmentity on '.$table_prefix.'_crmentity.crmid='.$table_prefix.'_contactdetails.contactid left join '.$table_prefix.'_contactscf on '.$table_prefix.'_contactscf.contactid = '.$table_prefix.'_contactdetails.contactid where '.$table_prefix.'_crmentity.deleted=0 and '.$concat_qry.'  '.$table_prefix.'_crmentity.crmid in ('. generateQuestionMarks($crmids) .')';
				break;
			case 'Accounts':
				//added to work out email opt out functionality.
				if(!$single_record)
					$concat_qry = '(((ltrim('.$table_prefix.'_account.email1) is not null) or (ltrim('.$table_prefix.'_account.email2) is not null)) and ('.$table_prefix.'_account.emailoptout != 1)) and ';
				else
					$concat_qry = '((ltrim('.$table_prefix.'_account.email1) is not null) or (ltrim('.$table_prefix.'_account.email2) is not null)) and ';
					
				$query = 'select crmid,accountname as entityname,'.$columnlists.' from '.$table_prefix.'_account inner join '.$table_prefix.'_crmentity on '.$table_prefix.'_crmentity.crmid='.$table_prefix.'_account.accountid left join '.$table_prefix.'_accountscf on '.$table_prefix.'_accountscf.accountid = '.$table_prefix.'_account.accountid where '.$table_prefix.'_crmentity.deleted=0 and '.$concat_qry.' '.$table_prefix.'_crmentity.crmid in ('. generateQuestionMarks($crmids) .')';
				break;
			case 'Vendors':
				$query = 'select crmid,vendorname as entityname,'.$columnlists.' from '.$table_prefix.'_vendor inner join '.$table_prefix.'_crmentity on '.$table_prefix.'_crmentity.crmid='.$table_prefix.'_vendor.vendorid left join '.$table_prefix.'_vendorcf on '.$table_prefix.'_vendorcf.vendorid = '.$table_prefix.'_vendor.vendorid where '.$table_prefix.'_crmentity.deleted=0 and '.$table_prefix.'_crmentity.crmid in ('. generateQuestionMarks($crmids) .')';
				break;
			//mycrmv@38883
			case 'HelpDesk':
				$query = 'select crmid,\'\' as entityname,'.$columnlists.' from '.$table_prefix.'_troubletickets inner join '.$table_prefix.'_crmentity on '.$table_prefix.'_crmentity.crmid='.$table_prefix.'_troubletickets.ticketid left join '.$table_prefix.'_ticketcf on '.$table_prefix.'_ticketcf.ticketid = '.$table_prefix.'_troubletickets.ticketid where '.$table_prefix.'_crmentity.deleted=0 and '.$table_prefix.'_crmentity.crmid in ('. generateQuestionMarks($crmids) .')';
				break;
			//mycrmv@38883e
			//danzi.tn@20130530
			case 'Inspections': 
				// $query = 'select crmid,accountname as entityname,'.$columnlists.' from '.$table_prefix.'_inspections inner join '.$table_prefix.'_crmentity on '.$table_prefix.'_crmentity.crmid='.$table_prefix.'_inspections.inspectionsid left join '.$table_prefix.'_inspectionscf on '.$table_prefix.'_inspectionscf.inspectionsid = '.$table_prefix.'_inspections.inspectionsid left join '.$table_prefix.'_account on '.$table_prefix.'_inspections.accountid = '.$table_prefix.'_account.accountid where '.$table_prefix.'_crmentity.deleted=0 and accountname IS NOT NULL AND (ltrim('.$table_prefix.'_inspections.account_email) is not null) AND  '.$table_prefix.'_crmentity.crmid in ('. generateQuestionMarks($crmids) .')';
				$query = 'select distinct '.$table_prefix.'_inspections.accountid as crmid,accountname as entityname, '.$columnlists.'  from '.$table_prefix.'_inspections inner join '.$table_prefix.'_crmentity on '.$table_prefix.'_crmentity.crmid='.$table_prefix.'_inspections.inspectionsid inner join '.$table_prefix.'_inspectionscf on '.$table_prefix.'_inspectionscf.inspectionsid = '.$table_prefix.'_inspections.inspectionsid inner join '.$table_prefix.'_account on '.$table_prefix.'_inspections.accountid = '.$table_prefix.'_account.accountid where '.$table_prefix.'_crmentity.deleted=0 and accountname IS NOT NULL AND (ltrim('.$table_prefix.'_account.email1) is not null) AND  '.$table_prefix.'_crmentity.crmid in ('. generateQuestionMarks($crmids) .')';
				break;
			//danzi.tn@20130530e
		}
		$result = $adb->pquery($query, array($crmids));
		while($row = $adb->fetch_array($result))
		{
			$name = $row['entityname'];
			for($i=0;$i<count($columns);$i++)
			{
				if($row[$columns[$i]] != NULL && $row[$columns[$i]] !='')
				{
					$idlists .= $row['crmid'].'@'.$fieldid[$i].'|'; 
					$mailids .= $name.'<'.$row[$columns[$i]].'>,';	
				}
			}
		}

		$return_data = Array('idlists'=>$idlists,'mailds'=>$mailids);
	}else
	{
		$return_data = Array('idlists'=>"",'mailds'=>"");
	}	
	return $return_data;
		
}

//added for attach the generated pdf with email
function pdfAttach($obj,$module,$file_name,$id)
{
	global $log;
	$log->debug("Entering into pdfAttach() method.");

	global $adb, $current_user,$table_prefix;
	global $upload_badext;
	$date_var = date('Y-m-d H:i:s');

	$ownerid = $obj->column_fields['assigned_user_id'];
	if(!isset($ownerid) || $ownerid=='')
		$ownerid = $current_user->id;

	$current_id = $adb->getUniqueID($table_prefix."_crmentity");

	$upload_file_path = decideFilePath();
	
	//crmv@31456
	if (isset($_REQUEST['draft_id']) && !in_array($_REQUEST['draft_id'],array('','undefined'))) {
		$res = $adb->pquery("SELECT
							  {$table_prefix}_attachments.attachmentsid
							FROM {$table_prefix}_attachments
							  INNER JOIN {$table_prefix}_seattachmentsrel
							    ON {$table_prefix}_attachments.attachmentsid = {$table_prefix}_seattachmentsrel.attachmentsid
							  INNER JOIN {$table_prefix}_crmentity
							    ON {$table_prefix}_crmentity.crmid = {$table_prefix}_attachments.attachmentsid
							WHERE {$table_prefix}_crmentity.deleted = 0
							    AND {$table_prefix}_seattachmentsrel.crmid = ? AND {$table_prefix}_attachments.name = ?",array($_REQUEST['draft_id'],$file_name));
		if ($res && $adb->num_rows($res) > 0) {
			$query = "insert into {$table_prefix}_seattachmentsrel values(?,?)";
			$adb->pquery($query, array($id, $adb->query_result($res,0,'attachmentsid')));
		}
		return true;
	}
	//crmv@31456

	//Copy the file from temporary directory into storage directory for upload
	$source_file_path = "storage/".$file_name;
	if (!is_file($source_file_path)) {
		return false;
	}
	$status = copy($source_file_path, $upload_file_path.$current_id."_".$file_name);
	//Check wheather the copy process is completed successfully or not. if failed no need to put entry in attachment table
	if($status)
	{
		$query1 = "insert into ".$table_prefix."_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?,?,?,?,?,?,?)";
		$params1 = array($current_id, $current_user->id, $ownerid, $module." Attachment", $obj->column_fields['description'], $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
		$adb->pquery($query1, $params1);

		$query2="insert into ".$table_prefix."_attachments(attachmentsid, name, description, type, path) values(?,?,?,?,?)";
		$params2 = array($current_id, $file_name, $obj->column_fields['description'], 'pdf', $upload_file_path);
		$adb->pquery($query2, $params2);

		$query3='insert into '.$table_prefix.'_seattachmentsrel values(?,?)';
		$adb->pquery($query3, array($id, $current_id));

		// Delete the file that was copied
		unlink($source_file_path);

		return true;
	}
	else
	{
		$log->debug("pdf not attached");
		return false;
	}
}
//this function check email fields profile permission as well as field access permission
function emails_checkFieldVisiblityPermission($fieldname) {
	global $current_user;
	$ret = getFieldVisibilityPermission('Emails',$current_user->id,$fieldname);
	return $ret;
}

//crmv@25356
function setAddressInfo($idlist, $to_email_array=Array(), $cleanAdv=false) {
	$tmp = explode('|',$idlist);
	$autosuggest = '';
	array_walk($to_email_array,'addressClean');
	if ($cleanAdv) {
		array_walk($to_email_array,'addressCleanAdv');
	}
	$to_email_array = array_filter($to_email_array);
	$to_email_array_tmp = $to_email_array;
	if (!empty($tmp)) {
		foreach($tmp as $k => $t) {
			if ($t == '') {
				continue;
			}
			$id = explode('@',$t);
			$crmid = $id[0];
			$fieldid = $id[1];
			//crmv@2043m
			if ($crmid == '' || $fieldid == '') {
				continue;
			}
			//crmv@2043me
			//crmv@30434
			if ($fieldid == -1){
				$mod = 'Users';
				$name = array($crmid => getUserFullName($crmid));
				$em = getUserEmail($crmid);

			} //crmv@30434e
			else {
				$mod = getSalesEntityType($crmid);
				$name = getEntityName($mod,array($crmid));
				$em = getEmailFromIdlist($mod,$crmid,$fieldid);
			}
			if (in_array($em,$to_email_array)) {
				unset($to_email_array[array_search($em,$to_email_array)]);
			}

			$autosuggest .= '<span id="to_'.$t.'" class="addrBubble">'.$name[$crmid]
			.'<div id="to_'.$t.'_parent_id" style="display:none;">'.$t.'</div>'
			.'<div id="to_'.$t.'_parent_name" style="display:none;">'.$name[$crmid].'</div>'
			.'<div id="to_'.$t.'_hidden_toid" style="display:none;">'.$em.'</div>'
			.'<div id="to_'.$t.'_remove" class="ImgBubbleDelete" onClick="removeAddress(\'to\',\''.$t.'\');"></div>'
			.'</span>';
		}
	}
	return array('autosuggest'=>$autosuggest,'to_mail'=>implode(', ',array_diff($to_email_array_tmp,$to_email_array)),'other_to_mail'=>implode(', ',$to_email_array));
}
function addressClean(&$to_email_array) {
	$to_email_array = trim($to_email_array);
}
function addressCleanAdv(&$to_email_array) {
	$to_email_array = substr($to_email_array,strpos($to_email_array,'<')+1,(strpos($to_email_array,'>')-strpos($to_email_array,'<')-1));
}
function getEmailFromIdlist($module,$crmid,$fieldid) {
	global $adb,$table_prefix;
	if ($fieldid != '') {
		$email = '';
		$result = $adb->pquery('select columnname, tablename from '.$table_prefix.'_field where fieldid = ?',array($fieldid));
		$columnname = $adb->query_result($result,0,'columnname');
		$tablename = $adb->query_result($result,0,'tablename');
		$moduleInstance = CRMEntity::getInstance($module);
		$result = $adb->pquery('select '.$columnname.' from '.$tablename.' where '.$moduleInstance->tab_name_index[$tablename].' = ?',array($crmid));
		if ($result && $adb->num_rows($result)>0) {
			$email = $adb->query_result($result,0,$columnname);
		}
		return $email;
	}
}
//crmv@25356e
//crmv@2043m
function getIdListReplyMailConverter($record, $email_list) {
	global $adb,$table_prefix;
	$module = getSalesEntityType($record);
	$focus = CRMEntity::getInstance($module);
	$query = "SELECT fieldid,tablename,columnname FROM ".$table_prefix."_field WHERE tabid=? and uitype=13";
	$result = $adb->pquery($query, array(getTabid($module)));
	if ($result && $adb->num_rows($result) > 0) {
		while($row=$adb->fetchByAssoc($result)) {
			foreach($email_list as $email) {
				$query1 = 'select '.$row['columnname'].' from '.$row['tablename'].' where '.$focus->tab_name_index[$row['tablename']].' = ? and '.$row['columnname'].' = ?';
				$result1 = $adb->pquery($query1,array($record, $email));
				if ($result1 && $adb->num_rows($result1) > 0) {
					return "$record@".$row['fieldid'].'|';
				}
			}
		}
	}
	return '';
}
function getFieldList($module) {
	global $adb,$table_prefix;
	$ids = array();
	$query = "SELECT fieldid FROM ".$table_prefix."_field WHERE tabid=? and uitype=13 and presence IN (0,2)";
	$result = $adb->pquery($query, array(getTabid($module)));
	if ($result && $adb->num_rows($result) > 0) {
		while($row=$adb->fetchByAssoc($result)) {
			$ids[] = $row['fieldid'];
		}
	}
	return $ids;
}
//crmv@2043me
?>
