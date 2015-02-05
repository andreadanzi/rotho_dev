<?php
/*********************************************************************************
 ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
 ********************************************************************************/
 
 // danzi.tn@20140207 Creazione ticket interno
 // danzi.tn@20140407 Creazione nuovo record in modulo Rumors
 // danzi.tn@20140721 Creazione nuovo record in modulo Market Price
 // danzi.tn@20150113 associazione attachment a Rumors e Market Price

require_once('modules/Users/Users.php');

/**
 * Mail Scanner Action
 */	
class Vtiger_MailScannerAction {
	// actionid for this instance
	var $actionid  = false;	
	// scanner to which this action is associated
	var $scannerid = false;
	// type of mailscanner action
	var $actiontype= false;
	// text representation of action
	var $actiontext= false;
	// target module for action
	var $module    = false;
	// lookup information while taking action
	var $lookup    = false;

	// Storage folder to use
	var $STORAGE_FOLDER = 'storage/mailscanner/';

	/** DEBUG functionality */
	var $debug     = false;
	function log($message) {
		global $log;
		if($log && $this->debug) { $log->debug($message); }
		if($this->debug) echo "$message\n"; //mycrmv@3147m
	}

	/**
	 * Constructor.
	 */
	function __construct($foractionid) {
		$this->initialize($foractionid);		
	}

	/**
	 * Initialize this instance.
	 */
	function initialize($foractionid) {
		global $adb,$table_prefix;
		$result = $adb->pquery("SELECT * FROM ".$table_prefix."_mailscanner_actions WHERE actionid=? ORDER BY sequence", Array($foractionid));

		if($adb->num_rows($result)) {
			$this->actionid   = $adb->query_result($result, 0, 'actionid');
			$this->scannerid  = $adb->query_result($result, 0, 'scannerid');
			$this->actiontype = $adb->query_result($result, 0, 'actiontype');
			$this->module     = $adb->query_result($result, 0, 'module');
			$this->lookup     = $adb->query_result($result, 0, 'lookup');
			$this->actiontext = "$this->actiontype,$this->module,$this->lookup";
		}
	}

	/**
	 * Create/Update the information of Action into database.
	 */
	function update($ruleid, $actiontext) {
		global $adb,$table_prefix;

		$inputparts = explode(',', $actiontext);
		$this->actiontype = $inputparts[0]; // LINK, CREATE
		$this->module     = $inputparts[1]; // Module name
		$this->lookup     = $inputparts[2]; // FROM, TO

		$this->actiontext = $actiontext;

		if($this->actionid) {
			$adb->pquery("UPDATE ".$table_prefix."_mailscanner_actions SET scannerid=?, actiontype=?, module=?, lookup=? WHERE actionid=?",
				Array($this->scannerid, $this->actiontype, $this->module, $this->lookup, $this->actionid));
		} else {
			$this->sequence = $this->__nextsequence();
			//crmv@16212
			$this->actionid = $adb->getUniqueID($table_prefix.'_mailscanner_actions');
			$adb->pquery("INSERT INTO ".$table_prefix."_mailscanner_actions(actionid,scannerid, actiontype, module, lookup, sequence) VALUES(?,?,?,?,?,?)",
				Array($this->actionid,$this->scannerid, $this->actiontype, $this->module, $this->lookup, $this->sequence));
			//crmv@16212 end
		}
		$checkmapping = $adb->pquery("SELECT COUNT(*) AS ruleaction_count FROM ".$table_prefix."_mailscanner_ruleactions 
			WHERE ruleid=? AND actionid=?", Array($ruleid, $this->actionid));
		if($adb->num_rows($checkmapping) && !$adb->query_result($checkmapping, 0, 'ruleaction_count')) {
			$adb->pquery("INSERT INTO ".$table_prefix."_mailscanner_ruleactions(ruleid, actionid) VALUES(?,?)", 
				Array($ruleid, $this->actionid));
		}
	}

	/**
	 * Delete the actions from tables.
	 */
	function delete() {
		global $adb,$table_prefix;
		if($this->actionid) {
			$adb->pquery("DELETE FROM ".$table_prefix."_mailscanner_actions WHERE actionid=?", Array($this->actionid));
			$adb->pquery("DELETE FROM ".$table_prefix."_mailscanner_ruleactions WHERE actionid=?", Array($this->actionid));
		}
	}

	/**
	 * Get next sequence of Action to use.
	 */
	function __nextsequence() {
		global $adb,$table_prefix;
		$seqres = $adb->pquery("SELECT max(sequence) AS max_sequence FROM ".$table_prefix."_mailscanner_actions", Array());
		$maxsequence = 0;
		if($adb->num_rows($seqres)) {
			$maxsequence = $adb->query_result($seqres, 0, 'max_sequence');
		}
		++$maxsequence;
		return $maxsequence;
	}

	/**
	 * Apply the action on the mail record.
	 */
	function apply($mailscanner, $mailrecord, $mailscannerrule, $matchresult) {
		$returnid = false;
		$this->log("process apply for action type ".$this->actiontype." and module ". $this->module);
		if($this->actiontype == 'CREATE') {
			if($this->module == 'HelpDesk') {
				$returnid = $this->__CreateTicket($mailscanner, $mailrecord); 
			}
		} /*mycrmv@3147*/elseif($this->actiontype == 'CREATEUSR') {
			if($this->module == 'HelpDesk') {
				$returnid = $this->__CreateTicketUser($mailscanner, $mailrecord); 
			}
		} /*mycrmv@3147e */
		  /*danzi.tn@20140207*/		
		elseif($this->actiontype == 'CREATEINT') {
			if($this->module == 'HelpDesk') {
				$returnid = $this->__CreateTicketInterno($mailscanner, $mailrecord); 
			}
		} /*danzi.tn@20140207e*/ /*danzi.tn@20140407 aggancio a RUMORS*/		
		elseif($this->actiontype == 'CREATERUMO') {
			if($this->module == 'Rumors') {
				$returnid = $this->__CreateRumor($mailscanner, $mailrecord); 
			}
		} /*danzi.tn@20140407e*//*danzi.tn@20140721 aggancio a Market Prices*/		
		elseif($this->actiontype == 'CREATEMKPC') {
			if($this->module == 'Marketprices') {
				$returnid = $this->__CreateMarketPrice($mailscanner, $mailrecord); 
			}
		} /*danzi.tn@20140721e*/
		else if($this->actiontype == 'LINK') {
			$returnid = $this->__LinkToRecord($mailscanner, $mailrecord);
		} else if($this->actiontype == 'UPDATE') {
			if($this->module == 'HelpDesk') {
				$returnid = $this->__UpdateTicket($mailscanner, $mailrecord, 
					$mailscannerrule->hasRegexMatch($matchresult));
			}
		//crmv@27618
		} else if($this->actiontype == 'DO_NOTHING') {
			$returnid = 0; //mycrmv@3147m
		//crmv@27618e
		}
		return $returnid;
	}

	/**
	 * Update ticket action.
	 */
	function __UpdateTicket($mailscanner, $mailrecord, $regexMatchInfo) {
		global $adb,$table_prefix;
		$returnid = false;

		$usesubject = false;
		if($this->lookup == 'SUBJECT') {
			// If regex match was performed on subject use the matched group
			// to lookup the ticket record
			if($regexMatchInfo) $usesubject = $regexMatchInfo['matches'];
			else $usesubject = $mailrecord->_subject;

			// Get the ticket record that was created by SENDER earlier
			$fromemail = $mailrecord->_from[0];

			$linkfocus = $mailscanner->GetTicketRecord($usesubject, $fromemail);
			$relatedid = $linkfocus->column_fields[parent_id];
			
			// If matching ticket is found, update comment, attach email
			if($linkfocus) {
				$timestamp = $adb->formatDate(date('Y-m-d H:i:s'), true);
				$comid = $adb->getUniqueID($table_prefix.'_ticketcomments');
				$adb->pquery("INSERT INTO ".$table_prefix."_ticketcomments(commentid,ticketid, comments, ownerid, ownertype, createdtime) VALUES(?,?,?,?,?,?)",	//crmv@fix
					Array($comid,$linkfocus->id, html_entity_decode($mailrecord->getBodyText(),ENT_COMPAT,'UTF-8'), $relatedid, 'customer', $timestamp));
				//crmv@2043m
				if ($linkfocus->answeredByCustomerStatus != '') {
					$ticket_status = $linkfocus->answeredByCustomerStatus;
				} else {
					$ticket_status = 'Open';
				}
				// Set the ticket status to Open if its Closed
				$adb->pquery("UPDATE ".$table_prefix."_troubletickets set status=? WHERE ticketid=?", Array($ticket_status, $linkfocus->id));
				/*
				if ($linkfocus->answeredByCustomerStatus != '') {
					$linkfocus->retrieve_entity_info($linkfocus->id, 'HelpDesk');
					$linkfocus->mode = 'edit';
					$linkfocus->column_fields['ticketstatus'] = $linkfocus->answeredByCustomerStatus;
					$linkfocus->save('HelpDesk');
				}
				*/
				//crmv@2043me
				$returnid = $this->__CreateNewEmail($mailrecord, $this->module, $linkfocus);

			} else {
				// TODO If matching ticket was not found, create ticket?
				// $returnid = $this->__CreateTicket($mailscanner, $mailrecord);
			}
		}
		return $returnid;
	}

	/**
	 * Create ticket action.
	 */
	function __CreateTicket($mailscanner, $mailrecord) {
		// Prepare data to create trouble ticket
		$usetitle = $mailrecord->_subject;
		$description = $mailrecord->getBodyText();
		//crmv@2043m
		$matches = preg_match('/<body[^>]*>(.*)/ims',$description,$tmp);
		if ($matches) {
			$description = $tmp[1];
		}
		if (strpos($description,'</body>') !== false) {
			$description = substr($description,0,strpos($description,'</body>'));
		}
		//crmv@2043me

		// There will be only on FROM address to email, so pick the first one
		$fromemail = $mailrecord->_from[0];	
		$linktoid = $mailscanner->LookupContact($fromemail);
		if(!$linktoid) $linktoid = $mailscanner->LookupAccount($fromemail);
		//crmv@2043m
		global $adb,$table_prefix;
		require_once('include/Webservices/WebserviceField.php');
		$fieldInstance = WebserviceField::fromQueryResult($adb,$adb->query("SELECT * FROM ".$table_prefix."_field WHERE tabid = 13 AND fieldname = 'parent_id'"),0);
		$referenceList = $fieldInstance->getReferenceList();
		if (in_array('Leads',$referenceList)) {
			if(!$linktoid) $linktoid = $mailscanner->LookupLead($fromemail);
			if(!$linktoid) $linktoid = $mailscanner->CreateLead($fromemail);
		}
		//crmv@2043me
		
		/** Now Create Ticket **/
		global $current_user;
		if(!$current_user) $current_user = new Users();
		$current_user->id = 1;

		// Create trouble ticket record
		$ticket = CRMEntity::getInstance('HelpDesk');
		$ticket->column_fields['ticket_title'] = $usetitle;
		$ticket->column_fields['description'] = $description;
		$ticket->column_fields['ticketstatus'] = 'Open';
		//mycrmv@3147m
		$ticket->column_fields['email_mittente'] = $fromemail; 
//		$ticket->column_fields['assigned_user_id'] = $current_user->id;
		$ticket->column_fields['assigned_user_id'] = 26; //group "Vendite e Marketing"
		$ticket->column_fields['ticketpriorities'] = 'Normal';
		$ticket->column_fields['cf_1061'] = 'ESTERNO';
		//mycrmv@3147me	
		if($linktoid) $ticket->column_fields['parent_id'] = $linktoid;
		//crmv@2043m
		if (isset($ticket->column_fields['email_from'])) {
			$ticket->column_fields['email_from'] = $mailrecord->_from;
		}
		if (isset($ticket->column_fields['email_to'])) {
			if (is_array($mailrecord->_to)) {
				$email_to = implode(',',$mailrecord->_to);
			} else {
				$email_to = $mailrecord->_to;
			}
			$ticket->column_fields['email_to'] = $email_to;
		}
		if (isset($ticket->column_fields['email_cc'])) {
			if (is_array($mailrecord->_cc)) {
				$email_cc = implode(',',$mailrecord->_cc);
			} else {
				$email_cc = $mailrecord->_cc;
			}
			$ticket->column_fields['email_cc'] = $email_cc;
		}
		if (isset($ticket->column_fields['email_bcc'])) {
			if (is_array($mailrecord->_bcc)) {
				$email_bcc = implode(',',$mailrecord->_bcc);
			} else {
				$email_bcc = $mailrecord->_bcc;
			}
			$ticket->column_fields['email_bcc'] = $email_bcc;
		}
		//crmv@2043me
		//crmv@27618
		if (isset($ticket->column_fields['mailscanner_action']) && $this->actionid !== false) {
			$ticket->column_fields['mailscanner_action'] = $this->actionid;
		}
		//crmv@27618e
		$ticket->save('HelpDesk');
		//crmv@2043m
		if (isset($ticket->column_fields['email_date'])) {
			$adb->pquery('update '.$table_prefix.'_troubletickets set email_date = ? where ticketid = ?',array(date('Y-m-d H:i:s', $mailrecord->_date), $ticket->id));
		}
		//crmv@2043me

		// Associate any attachement of the email to ticket
		$this->__SaveAttachements($mailrecord, 'HelpDesk', $ticket, $ticket);	//crmv@27657
		
		//crmv@2043m
		$mailrecord->_subject .= ' - Ticket Id: '.$ticket->id;
		$this->__CreateNewEmail($mailrecord, $this->module, $ticket);
		//crmv@2043me
		
		//mycrmv@rothoblas
		//Invio di una mail di ritorno
		/*
		if ($linktoid){
			include_once('include/utils/utils.php');
	//		include_once('modules/HelpDesk/HelpDeskHandler.php');
			$linktoid_module=getSalesEntityType($linktoid);
	//		if(!HelpDeskHandler::isParentMailBlocked($linktoid_module,$linktoid))
				$this->_Answer2Sender($mailscanner,$fromemail,$usetitle,$ticket->id,13); //13- template id (al momento lo metto fisso)
			//mycrmv@rothoblaas e
		}
		*/
		return $ticket->id;
	}
	
	//mycrmv@rothoblaas
	function _Answer2Sender($mailscanner,$toemail,$usetitle,$ticket_id,$templatemail_id){
		global $adb;
		include_once('modules/Emails/mail.php');

		$template=getTemplateDetails($templatemail_id);
		if(!is_array($template)) return;
		
		$sql = $adb->pquery("SELECT ticket_no FROM vtiger_troubletickets
					inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_troubletickets.ticketid
					where vtiger_crmentity.deleted = 0 and ticketid = ?", Array($ticket_id));
		$ticket_no = $adb->query_result($sql, 0, 'ticket_no');

		$subject=str_replace('#TICKET_NO#',$ticket_no,$template[2]);
		$subject=str_replace('#TICKET_ID#','ID: '.$ticket_id,$subject);
		$subject=str_replace('#MAIL_SUBJECT#',$usetitle,$subject);
		$description=str_replace('#MAIL_SUBJECT#',$usetitle,$template[1]);
		$description=str_replace('#TICKET_ID#',$usetitle,$description);
		$subject=getMergedDescription($subject,$ticket_id,'HelpDesk');
		$description=getMergedDescription($description,$ticket_id,'HelpDesk');

		send_mail('Emails',$toemail,'Ticket CRM',$mailscanner->_scannerinfo->username,$subject,$description);
	}
	//mycrmv@rothoblaas e

	/**
	 * Add email to CRM record like Contacts/Accounts
	 */
	function __LinkToRecord($mailscanner, $mailrecord) {
		$linkfocus = false;

		$useemail  = false;
		if($this->lookup == 'FROM') $useemail = $mailrecord->_from;
		else if($this->lookup == 'TO') $useemail = $mailrecord->_to;

		if($this->module == 'Contacts') {
			foreach($useemail as $email) {
				$linkfocus = $mailscanner->GetContactRecord($email);
				if($linkfocus) break;
			}
		} else if($this->module == 'Accounts') {
			foreach($useemail as $email) {			
				$linkfocus = $mailscanner->GetAccountRecord($email);
				if($linkfocus) break;
			}
		//crmv@2043m
		} else if($this->module == 'Leads') {
			foreach($useemail as $email) {			
				$linkfocus = $mailscanner->GetLeadRecord($email);
				if($linkfocus) break;
			}
		//crmv@2043me
		//crmv@27657
		} else if($this->module == 'Vendors') {
			foreach($useemail as $email) {			
				$linkfocus = $mailscanner->GetVendorRecord($email);
				if($linkfocus) break;
			}
		//crmv@27657e
		}

		$returnid = false;
		if($linkfocus) {
			$returnid = $this->__CreateNewEmail($mailrecord, $this->module, $linkfocus);
		}
		return $returnid;
	}

	/**
	 * Create new Email record (and link to given record) including attachements
	 */
	function __CreateNewEmail($mailrecord, $module, $linkfocus) {	
		global $current_user, $adb,$table_prefix;
		if(!$current_user) $current_user = new Users();
		$current_user->id = 1;
		
		//crmv@2043m
		$fieldid = '-1';
		$result = $adb->pquery('SELECT fieldid FROM '.$table_prefix.'_field WHERE tabid = ? AND (fieldname = ? OR fieldname = ? OR fieldname = ?)',array(getTabid($module),'email','email1','email2'));
		if ($result && $adb->num_rows($result) > 0) {
			$fieldid = $adb->query_result($result,0,'fieldid');
		} else {
			if ($module == 'HelpDesk') {
				$fieldid = '';
			}
		}
		//crmv@2043me

		$focus = CRMEntity::getInstance('Emails');
		$focus->column_fields['parent_type'] = $module;
		$focus->column_fields['activitytype'] = 'Emails';
		$focus->column_fields['parent_id'] = "$linkfocus->id@$fieldid|";	//crmv@2043m
		$focus->column_fields['subject'] = $mailrecord->_subject;

		$focus->column_fields['description'] = $mailrecord->getBodyHTML();
		$focus->column_fields['assigned_user_id'] = $linkfocus->column_fields['assigned_user_id'];
		$focus->column_fields["date_start"]= date('Y-m-d', $mailrecord->_date);
		$focus->column_fields["email_flag"] = 'SAVED';
		
		$from=$mailrecord->_from[0];
		$to = $mailrecord->_to[0];
		$cc = (!empty($mailrecord->_cc))? implode(',', $mailrecord->_cc) : '';
		$bcc= (!empty($mailrecord->_bcc))? implode(',', $mailrecord->_bcc) : '';
		$flag=''; // 'SENT'/'SAVED'
		//emails field were restructured and to,bcc and cc field are JSON arrays
		$focus->column_fields['from_email'] = $from;
		$focus->column_fields['saved_toid'] = $to;
		$focus->column_fields['ccmail'] = $cc;
		$focus->column_fields['bccmail'] = $bcc;
		$focus->save('Emails');

		$emailid = $focus->id;
		$this->log("Created [$focus->id]: $mailrecord->_subject linked it to " . $linkfocus->id);

		$this->__SaveAttachements($mailrecord, 'Emails', $focus, $linkfocus);	//crmv@27657

		return $emailid;
	}

	/**
	 * Save attachments from the email and add it to the module record.
	 */
	function __SaveAttachements($mailrecord, $basemodule, $basefocus, $modulefocus) {	//crmv@27657
		global $adb,$table_prefix;

		// If there is no attachments return
		if(!$mailrecord->_attachments) return;

		$userid = $basefocus->column_fields['assigned_user_id'];
		$setype = "$basemodule Attachment";

		$date_var = date('Y-m-d H:i:s');	//crmv@18341

		foreach($mailrecord->_attachments as $filename=>$filecontent) {
			$attachid = $adb->getUniqueId($table_prefix.'_crmentity');
			$description = $filename;
			$usetime = $adb->formatDate($date_var, true);

			$adb->pquery("INSERT INTO ".$table_prefix."_crmentity(crmid, smcreatorid, smownerid, 
				modifiedby, setype, description, createdtime, modifiedtime, presence, deleted)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 
				Array($attachid, $userid, $userid, $userid, $setype, $description, $usetime, $usetime, 1, 0));

			$issaved = $this->__SaveAttachmentFile($attachid, $filename, $filecontent);
			if($issaved) {
				// Create document record
				$document = CRMEntity::getInstance('Documents');
				$document->column_fields['notes_title']      = $filename;
				$document->column_fields['filename']         = $filename;
				$document->column_fields['filestatus']       = 1;
				$document->column_fields['filelocationtype'] = 'I';
				if($basemodule == 'HelpDesk')
					$document->column_fields['folderid'] = 3; // Default Folder //mycrmv@43045 Folder changed to Reclami
				if($basemodule == 'Rumors')
					$document->column_fields['folderid'] = 31; // Default Folder //mycrmv@43045 Folder changed to Reclami
				if($basemodule == 'Marketprices')
					$document->column_fields['folderid'] = 42; // Default Folder //mycrmv@43045 Folder changed to Reclami
				$document->column_fields['assigned_user_id'] = $userid;
				$document->column_fields['filesize'] = 0;	//crmv@18341
				$document->save('Documents');
				//crmv@27657
				// Link file attached to document
				$adb->pquery("INSERT INTO ".$table_prefix."_seattachmentsrel(crmid, attachmentsid) VALUES(?,?)",Array($document->id, $attachid));

				//Link file attached to email	
				$adb->pquery("INSERT INTO ".$table_prefix."_seattachmentsrel(crmid, attachmentsid) VALUES(?,?)",Array($basefocus->id, $attachid));
				
				if ($basemodule != 'Emails') {
					// Link document to base record
					$adb->pquery("INSERT INTO ".$table_prefix."_senotesrel(crmid, notesid) VALUES(?,?)",Array($modulefocus->id, $document->id));
				}
				//crmv@27657e
			}
		}	
	}

	/**
	 * Save the attachment to the file
	 */
	function __SaveAttachmentFile($attachid, $filename, $filecontent) {
		global $adb,$table_prefix;

		$dirname = $this->STORAGE_FOLDER;
		if(!is_dir($dirname)) mkdir($dirname);

		$description = $filename;
		$filename = str_replace(' ', '-', $filename);
		$saveasfile = "$dirname$attachid" . "_$filename";
		if(!file_exists($saveasfile)) {
			
			$this->log("Saved attachement as $saveasfile\n");

			$fh = fopen($saveasfile, 'wb');
			fwrite($fh, $filecontent);
			fclose($fh);
		}

		$mimetype = MailAttachmentMIME::detect($saveasfile);
		
		//crmv@18341
		$adb->pquery("INSERT INTO ".$table_prefix."_attachments (attachmentsid, name, description, type, path) VALUES(?,?,?,?,?)",
			Array($attachid, $filename, $description, $mimetype, $dirname));	
		//crmv@18341e 
		
		return true;
	}
	//mycrmv@3147m
	/**
	 * Create ticket action.
	 */
	function __CreateTicketUser($mailscanner, $mailrecord) {
		// Prepare data to create trouble ticket
		$usetitle = $mailrecord->_subject;
		$description = $mailrecord->getBodyText();
		//crmv@2043m
		$matches = preg_match('/<body[^>]*>(.*)/ims',$description,$tmp);
		if ($matches) {
			$description = $tmp[1];
		}
		if (strpos($description,'</body>') !== false) {
			$description = substr($description,0,strpos($description,'</body>'));
		}
		//crmv@2043me

		// There will be only on FROM address to email, so pick the first one
		$fromemail = $mailrecord->_from[0];	
		$linktoid = $mailscanner->LookupUser($fromemail);
		if (!$linktoid){
			return false;
		}
		/** Now Create Ticket **/
		global $current_user;
		if(!$current_user) $current_user = new Users();
		$current_user->id = 1;

		// Create trouble ticket record
		$ticket = CRMEntity::getInstance('HelpDesk');
		$ticket->column_fields['ticket_title'] = $usetitle;
		$ticket->column_fields['description'] = $description;
		$ticket->column_fields['ticketstatus'] = 'Open';
		$ticket->column_fields['ticketpriorities'] = 'Normal';
		$ticket->column_fields['cf_1061'] = 'ESTERNO';
		//mycrmv@43058
		$ticket->column_fields['assigned_user_id'] = crmv_assign_ticket_mailscanner($linktoid);
		//mycrmv@43058e
		$ticket->column_fields['email_mittente'] = $fromemail; //mycrmv@3147m
		if($linktoid) $ticket->column_fields['agente_riferimento_rec'] = $linktoid;
		//crmv@2043m
		if (isset($ticket->column_fields['email_from'])) {
			$ticket->column_fields['email_from'] = $mailrecord->_from;
		}
		if (isset($ticket->column_fields['email_to'])) {
			if (is_array($mailrecord->_to)) {
				$email_to = implode(',',$mailrecord->_to);
			} else {
				$email_to = $mailrecord->_to;
			}
			$ticket->column_fields['email_to'] = $email_to;
		}
		if (isset($ticket->column_fields['email_cc'])) {
			if (is_array($mailrecord->_cc)) {
				$email_cc = implode(',',$mailrecord->_cc);
			} else {
				$email_cc = $mailrecord->_cc;
			}
			$ticket->column_fields['email_cc'] = $email_cc;
		}
		if (isset($ticket->column_fields['email_bcc'])) {
			if (is_array($mailrecord->_bcc)) {
				$email_bcc = implode(',',$mailrecord->_bcc);
			} else {
				$email_bcc = $mailrecord->_bcc;
			}
			$ticket->column_fields['email_bcc'] = $email_bcc;
		}
		//crmv@2043me
		//crmv@27618
		if (isset($ticket->column_fields['mailscanner_action']) && $this->actionid !== false) {
			$ticket->column_fields['mailscanner_action'] = $this->actionid;
		}
		//crmv@27618e
		$ticket->save('HelpDesk');
		//crmv@2043m
		if (isset($ticket->column_fields['email_date'])) {
			$adb->pquery('update '.$table_prefix.'_troubletickets set email_date = ? where ticketid = ?',array(date('Y-m-d H:i:s', $mailrecord->_date), $ticket->id));
		}
		//crmv@2043me

		// Associate any attachement of the email to ticket
		$this->__SaveAttachements($mailrecord, 'HelpDesk', $ticket, $ticket);	//crmv@27657
		
		//crmv@2043m
		$mailrecord->_subject .= ' - Ticket Id: '.$ticket->id;
		$this->__CreateNewEmail($mailrecord, $this->module, $ticket);
		//crmv@2043me
		/*
		//mycrmv@rothoblas
		//Invio di una mail di ritorno
		include_once('include/utils/utils.php');
//		include_once('modules/HelpDesk/HelpDeskHandler.php');
		$linktoid_module=getSalesEntityType($linktoid);
//		if(!HelpDeskHandler::isParentMailBlocked($linktoid_module,$linktoid))
			$this->_Answer2Sender($mailscanner,$fromemail,$usetitle,$ticket->id,13); //13- template id (al momento lo metto fisso)
		//mycrmv@rothoblaas e
		*/
		return $ticket->id;
	}
	//mycrmv@3147me		
	
	
	// danzi.tn@20140207 Creazione ticket interno
	/**
	 * Create ticket action.
	 */
	function __CreateTicketInterno($mailscanner, $mailrecord) {
		// Prepare data to create trouble ticket
		$usetitle = $mailrecord->_subject;
		$description = $mailrecord->getBodyText();
		//crmv@2043m
		$matches = preg_match('/<body[^>]*>(.*)/ims',$description,$tmp);
		if ($matches) {
			$description = $tmp[1];
		}
		if (strpos($description,'</body>') !== false) {
			$description = substr($description,0,strpos($description,'</body>'));
		}
		//crmv@2043me

		// There will be only on FROM address to email, so pick the first one
		$fromemail = $mailrecord->_from[0];	
		
		/** Now Create Ticket **/
		global $current_user;
		if(!$current_user) $current_user = new Users();
		$current_user->id = 1;

		// Create trouble ticket record
		$ticket = CRMEntity::getInstance('HelpDesk');
		$ticket->column_fields['ticket_title'] = $usetitle;
		$ticket->column_fields['description'] = $description;
		$ticket->column_fields['ticketstatus'] = 'Open';
		$ticket->column_fields['ticketpriorities'] = 'Normal';
		$ticket->column_fields['ticketcategories'] = 'Altro';
		$ticket->column_fields['cf_1061'] = 'INTERNO';
		$ticket->column_fields['assigned_user_id'] = 26; // ASSEGNATO a Gruppo Vendite e Marketing
		$ticket->column_fields['parent_id'] = 1306471; // ASSEGNATO a azienda Ticket Rothoblaas
		$ticket->column_fields['email_mittente'] = $fromemail;
		//crmv@2043m
		if (isset($ticket->column_fields['email_from'])) {
			$ticket->column_fields['email_from'] = $mailrecord->_from;
		}
		if (isset($ticket->column_fields['email_to'])) {
			if (is_array($mailrecord->_to)) {
				$email_to = implode(',',$mailrecord->_to);
			} else {
				$email_to = $mailrecord->_to;
			}
			$ticket->column_fields['email_to'] = $email_to;
		}
		if (isset($ticket->column_fields['email_cc'])) {
			if (is_array($mailrecord->_cc)) {
				$email_cc = implode(',',$mailrecord->_cc);
			} else {
				$email_cc = $mailrecord->_cc;
			}
			$ticket->column_fields['email_cc'] = $email_cc;
		}
		if (isset($ticket->column_fields['email_bcc'])) {
			if (is_array($mailrecord->_bcc)) {
				$email_bcc = implode(',',$mailrecord->_bcc);
			} else {
				$email_bcc = $mailrecord->_bcc;
			}
			$ticket->column_fields['email_bcc'] = $email_bcc;
		}
		//crmv@2043me
		//crmv@27618
		if (isset($ticket->column_fields['mailscanner_action']) && $this->actionid !== false) {
			$ticket->column_fields['mailscanner_action'] = $this->actionid;
		}
		//crmv@27618e
		$ticket->save('HelpDesk');
		//crmv@2043m
		if (isset($ticket->column_fields['email_date'])) {
			$adb->pquery('update '.$table_prefix.'_troubletickets set email_date = ? where ticketid = ?',array(date('Y-m-d H:i:s', $mailrecord->_date), $ticket->id));
		}
		//crmv@2043me

		// Associate any attachement of the email to ticket
		$this->__SaveAttachements($mailrecord, 'HelpDesk', $ticket, $ticket);	//crmv@27657
		
		//crmv@2043m
		$mailrecord->_subject .= ' - Ticket Id: '.$ticket->id;
		$this->__CreateNewEmail($mailrecord, $this->module, $ticket);
		//crmv@2043me
		return $ticket->id;
	}
	// danzi.tn@20140207 e
	
	// danzi.tn@20140407 Creazione nuovo record in modulo Rumors
	/**
	 * Create rumor action.
	 */
	function __CreateRumor($mailscanner, $mailrecord) {
		// Prepare data to create rumor
		$usetitle = $mailrecord->_subject;
		$description = $mailrecord->getBodyText();
		//crmv@2043m
		$matches = preg_match('/<body[^>]*>(.*)/ims',$description,$tmp);
		if ($matches) {
			$description = $tmp[1];
		}
		if (strpos($description,'</body>') !== false) {
			$description = substr($description,0,strpos($description,'</body>'));
		}
		//crmv@2043me

		// There will be only on FROM address to email, so pick the first one
		$fromemail = $mailrecord->_from[0];	
		
		/** Now Create Rumor **/
		global $current_user;
		if(!$current_user) $current_user = new Users();
		$current_user->id = 1;
		$this->log("process __CreateRumor for subject ".$usetitle." from ". $fromemail);
		// Create trouble rumor record
		$rumor = CRMEntity::getInstance('Rumors');
		$rumor->column_fields['rumor_name'] = "Other"; //$usetitle;
		$rumor->column_fields['description'] = $description;
		$rumor->column_fields['cf_1215'] = $usetitle;
		$rumor->column_fields['infosender'] = $fromemail;
		$rumor->column_fields['product_cat'] = '06';
		$rumor->column_fields['product_cat_descr'] = 'ALTRO';
		$rumor->column_fields['assigned_user_id'] = 133013; // ASSEGNATO a Gruppo Product Manager
		$rumor->save('Rumors');
		// danzi.tn@20150113 associazione attachment a rumor
		$this->__SaveAttachements($mailrecord, 'Rumors', $rumor, $rumor);
		// danzi.tn@20150113e
		//crmv@2043m
		$mailrecord->_subject .= ' - Rumor Id: '.$rumor->id;
		$this->__CreateNewEmail($mailrecord, $this->module, $rumor);
		//crmv@2043me
		return $rumor->id;
	}
	// danzi.tn@20140407 e
	
	// danzi.tn@20140721 Creazione nuovo record in modulo Marketprices
	/**
	 * Create market price action.
	 */
	function __CreateMarketPrice($mailscanner, $mailrecord) {
		// Prepare data to create rumor
		$usetitle = $mailrecord->_subject;
		$description = $mailrecord->getBodyText();
		//crmv@2043m
		$matches = preg_match('/<body[^>]*>(.*)/ims',$description,$tmp);
		if ($matches) {
			$description = $tmp[1];
		}
		if (strpos($description,'</body>') !== false) {
			$description = substr($description,0,strpos($description,'</body>'));
		}
		//crmv@2043me

		// There will be only on FROM address to email, so pick the first one
		$fromemail = $mailrecord->_from[0];	
		$linktoid = $mailscanner->LookupUser($fromemail);
		if (!$linktoid){
			return false;
		}
		/** Now Create Marketprice **/
		global $current_user;
		if(!$current_user) $current_user = new Users();
		$current_user->id = 1;
		$this->log("process __CreateMarketPrice for subject ".$usetitle." from ". $fromemail);
		// Create trouble marketprice record
		$marketprice = CRMEntity::getInstance('Marketprices');
		$marketprice->column_fields['marketprice_name'] = "lbl_mkp_price"; //$usetitle;
		$marketprice->column_fields['description'] = $description;
		$marketprice->column_fields['marketprice_subject'] = $usetitle;
		$marketprice->column_fields['infosender'] = $fromemail;
		$marketprice->column_fields['product_cat'] = '06';
		$marketprice->column_fields['product_cat_descr'] = 'ALTRO';
		$marketprice->column_fields['assigned_user_id'] = $linktoid; //132947; // ASSEGNATO a Benchmarking
		$retVals = $this->__get_am_for_user($linktoid);
		$marketprice->column_fields['area_mng_name'] = $retVals['area_mng_name'];
		$marketprice->column_fields['area_mng_no'] = $retVals['area_mng_no'];
		$marketprice->save('Marketprices');
		// Associate any attachement of the email to rumor
		// danzi.tn@20150113 associazione attachment a marketprice
		$this->__SaveAttachements($mailrecord, 'Marketprices', $marketprice, $marketprice);
		// danzi.tn@20150113e
		//crmv@2043m
		$mailrecord->_subject .= ' - Market Price Id: '.$marketprice->id;
		$this->__CreateNewEmail($mailrecord, $this->module, $marketprice);
		//crmv@2043me
		return $marketprice->id;
	}
	
	function __get_am_for_user($user_id) {
		global $adb,$table_prefix;
		$agent_cod_capoarea = "";
		$agent_name_capoarea = "";
		$query = "SELECT  vtiger_users.agent_cod_capoarea,
							amuser.first_name + ' '+ amuser.last_name as agent_name_capoarea
							from  {$table_prefix}_users 
							LEFT JOIN {$table_prefix}_users as amuser on amuser.erp_code = {$table_prefix}_users.agent_cod_capoarea
							WHERE  {$table_prefix}_users.agent_cod_capoarea <> '' AND {$table_prefix}_users.id = ?";
		$result = $adb->pquery($query,array($user_id));
		if ($result && $adb->num_rows($result)>0) {
			$agent_cod_capoarea = $adb->query_result($result,0,'agent_cod_capoarea');
			$agent_name_capoarea = $adb->query_result($result,0,'agent_name_capoarea');
		}
		return array('area_mng_name'=>$agent_name_capoarea,'area_mng_no'=>$agent_cod_capoarea);
	}
	// danzi.tn@20140721 e
	
	
}
?>