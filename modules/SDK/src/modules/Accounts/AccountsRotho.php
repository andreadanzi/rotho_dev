<?php
require_once('modules/Accounts/Accounts.php');
require_once('modules/Emails/mail.php');
// danzi.tn@20140717 creazione nuovo modulo Marketprices => get_marketprices
// danzi.tn@20150205 per la gestione delle notifiche
class AccountsRotho extends Accounts {
	
	var $list_fields_name = Array(
		'Account Name'=>'accountname',
		'City'=>'bill_city',
		'Billing State'=>'bill_state', 
		'External Code'=>'external_code', 
		'Assigned To'=>'assigned_user_id',
		'Origine Lead'=>'cf_770'			
	);
	
	function Accounts() {
		parent::Accounts();
		$this->list_fields = Array(
			'Account Name'=>Array($table_prefix.'_account'=>'accountname'),
			'City'=>Array($table_prefix.'_accountbillads'=>'bill_city'), 
			'Billing State'=>Array($table_prefix.'_accountbillads'=>'bill_state'), 
			'External Code'=>Array($table_prefix.'_account'=>'external_code'), 
			'Assigned To'=>Array($table_prefix.'_crmentity'=>'smownerid'),
			'Origine Lead'=>Array($table_prefix.'_accountscf'=>'cf_770')
		);
	}
	
	function get_contacts($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user,$table_prefix;
		$log->debug("Entering get_contacts(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);
        vtlib_setup_modulevars($related_module, $other);		
		$singular_modname = vtlib_toSingular($related_module);
		
		$parenttab = getParentTab();
		
		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;
		
		$button = '';
				
		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"openPopup('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;"; // crmv@21048m
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		} 

		//mycrmv@2707m
		$query = $this->get_related_contacts_query($id);
		//mycrmv@2707me
		
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 
		
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_contacts method ...");		
		return $return_value;
	}
	
	//mycrmv@2707m
	function get_related_contacts_query($id) {
		global $table_prefix;
		$query = "SELECT ".$table_prefix."_contactdetails.*,
				".$table_prefix."_crmentity.crmid,
				".$table_prefix."_crmentity.smownerid,
				".$table_prefix."_account.accountname,
				".$table_prefix."_contactsubdetails.*,
				case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name
				FROM ".$table_prefix."_contactdetails
				INNER JOIN ".$table_prefix."_contactscf
					ON ".$table_prefix."_contactscf.contactid = ".$table_prefix."_contactdetails.contactid
				INNER JOIN ".$table_prefix."_contactsubdetails
					ON ".$table_prefix."_contactsubdetails.contactsubscriptionid = ".$table_prefix."_contactdetails.contactid
				INNER JOIN ".$table_prefix."_crmentity
					ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_contactdetails.contactid
				LEFT JOIN ".$table_prefix."_account
					ON ".$table_prefix."_account.accountid = ".$table_prefix."_contactdetails.accountid
				LEFT JOIN ".$table_prefix."_groups
					ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				LEFT JOIN ".$table_prefix."_users
					ON ".$table_prefix."_crmentity.smownerid = ".$table_prefix."_users.id
				WHERE ".$table_prefix."_crmentity.deleted = 0
				AND ".$table_prefix."_contactdetails.accountid = ".$id;
		return $query;
	}
	function save_module($module) {
		parent::save_module($module);
		
		if (!empty($this->column_fields['assigned_user_id'])) {
			global $adb, $table_prefix;
			$contacts = $this->get_related_contacts_query($this->id);
			$contacts_list = array();
			$result = $adb->query($contacts);
			if ($result && $adb->num_rows($result) > 0) {
				while($row=$adb->fetchByAssoc($result)) {
					if (!empty($row['contactid'])) {
						$contacts_list[] = $row['contactid'];
						/*
						$focusContacts = CRMEntity::getInstance('Contacts');
						$focusContacts->retrieve_entity_info($row['contactid'],'Contacts');
						$focusContacts->mode = 'edit';
						$focusContacts->id = $row['contactid'];
						$focusContacts->column_fields['assigned_user_id'] = $this->column_fields['assigned_user_id'];
						$focusContacts->save('Contacts');
						*/
					}
				}
			}
			if (!empty($contacts_list)) {
				$adb->pquery("update {$table_prefix}_crmentity set smownerid = ? where crmid in (".generateQuestionMarks($contacts_list).")",array($this->column_fields['assigned_user_id'],$contacts_list));
			}
		}
	}
	//mycrmv@2707me
	
	//danzi.tn@20130813s
	function get_rumors($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user,$table_prefix;
		$log->debug("Entering get_rumors(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);
        vtlib_setup_modulevars($related_module, $other);		
		$singular_modname = vtlib_toSingular($related_module);
		
		$parenttab = getParentTab();
		
		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;
		
		$button = '';
				
		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"openPopup('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;"; // crmv@21048m
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		} 

		$query = "SELECT ".$table_prefix."_rumors.*,
			".$table_prefix."_crmentity.crmid,
                        ".$table_prefix."_crmentity.smownerid,
			".$table_prefix."_account.accountname,
			case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name
			FROM ".$table_prefix."_rumors
			INNER JOIN ".$table_prefix."_rumorscf
				ON ".$table_prefix."_rumorscf.rumorsid = ".$table_prefix."_rumors.rumorsid
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_rumors.rumorsid
			LEFT JOIN ".$table_prefix."_account as comp
				ON comp.accountid = ".$table_prefix."_rumors.competitor 
			LEFT JOIN ".$table_prefix."_account as cust
				ON cust.accountid = ".$table_prefix."_rumors.accounts_customer 
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_crmentity.smownerid = ".$table_prefix."_users.id
			WHERE ".$table_prefix."_crmentity.deleted = 0
			AND (comp.accountid = ".$id." OR cust.accountid = ".$id.")";
	
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 
		
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_rumors method ...");		
		return $return_value;

	}
	//danzi.tn@20130813e
	
	//danzi.tn@20140717 creazione nuovo modulo Marketprices
	function get_marketprices($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user,$table_prefix;
		$log->debug("Entering get_marketprices(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);
        vtlib_setup_modulevars($related_module, $other);		
		$singular_modname = vtlib_toSingular($related_module);
		
		$parenttab = getParentTab();
		
		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;
		
		$button = '';
				
		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"openPopup('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;"; // crmv@21048m
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		} 

		$query = "SELECT ".$table_prefix."_marketprices.*,
			".$table_prefix."_crmentity.crmid,
                        ".$table_prefix."_crmentity.smownerid,
			".$table_prefix."_account.accountname,
			case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name
			FROM ".$table_prefix."_marketprices
			INNER JOIN ".$table_prefix."_marketpricescf
				ON ".$table_prefix."_marketpricescf.marketpricesid = ".$table_prefix."_marketprices.marketpricesid
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_marketprices.marketpricesid
			LEFT JOIN ".$table_prefix."_account as comp
				ON comp.accountid = ".$table_prefix."_marketprices.competitor 
			LEFT JOIN ".$table_prefix."_account as cust
				ON cust.accountid = ".$table_prefix."_marketprices.accounts_customer 
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_crmentity.smownerid = ".$table_prefix."_users.id
			WHERE ".$table_prefix."_crmentity.deleted = 0
			AND (comp.accountid = ".$id." OR cust.accountid = ".$id.")";
	
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 
		
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_marketprices method ...");		
		return $return_value;

	}
	//danzi.tn@20140717e
	
	//danzi.tn@20150130 per il trasferimento dei related record bisogna considerare anche Visitreport, Consulenze e Revisioni
	/**
	 * Move the related records of the specified list of id's to the given record.
	 * @param String This module name
	 * @param Array List of Entity Id's from which related records need to be transfered 
	 * @param Integer Id of the the Record to which the related records are to be moved
	 */
	function transferRelatedRecords($module, $transferEntityIds, $entityId) {
		global $adb,$log,$table_prefix;
		$log->debug("Entering function AccountsRotho::transferRelatedRecords ($module, $transferEntityIds, $entityId)");
		$rel_table_arr = Array( "Visitreport"=>$table_prefix."_visitreport", "Consulenza"=>$table_prefix."_consulenza", "Inspections"=>$table_prefix."_inspections");
		$tbl_field_arr = Array($table_prefix."_visitreport"=>"visitreportid", $table_prefix."_consulenza"=>"consulenzaid", $table_prefix."_inspections"=>"inspectionsid");	
		$entity_tbl_field_arr = Array($table_prefix."_visitreport"=>"accountid", $table_prefix."_consulenza"=>"parent", $table_prefix."_inspections"=>"accountid");	
		
		foreach($transferEntityIds as $transferId) {
			foreach($rel_table_arr as $rel_module=>$rel_table) {
				$id_field = $tbl_field_arr[$rel_table];
				$entity_id_field = $entity_tbl_field_arr[$rel_table];
				// IN clause to avoid duplicate entries
				$sel_result =  $adb->pquery("select $id_field from $rel_table where $entity_id_field=? " .
						" and $id_field not in (select $id_field from $rel_table where $entity_id_field=?)",
						array($transferId,$entityId));
				$res_cnt = $adb->num_rows($sel_result);
				if($res_cnt > 0) {
					for($i=0;$i<$res_cnt;$i++) {
						$id_field_value = $adb->query_result($sel_result,$i,$id_field);
						$adb->pquery("update $rel_table set $entity_id_field=? where $entity_id_field=? and $id_field=?", 
							array($entityId,$transferId,$id_field_value));	
					}
				}				
			}
		}
		//crmv@15526
		parent::transferRelatedRecords($module, $transferEntityIds, $entityId);
		//crmv@15526 end	
		$log->debug("Exiting AccountsRotho::transferRelatedRecords...");
	}
	//danzi.tn@20150130e
	
	
	
	//danzi.tn@20150205 per la gestione delle notifiche
	function notify_first_activation($event_name) {
		$base_language = strtoupper($this->column_fields['cf_1113']); // Lingua Base
		// Cerco template di tipo 'Notifiche Clienti' sulla base della lingua
		$templateName = trim($event_name." ".trim($base_language));
		$retTemplate = searchTemplate('Notifiche Clienti',$templateName);
		$template_id = 0;
		if(empty($retTemplate)) {
			$templateName = trim($event_name);
			$retTemplate = searchTemplate('Notifiche Clienti',$templateName);
			if(empty($retTemplate)) {
				$templateName = $event_name;
				$log->debug("function AccountsRotho::check_first_activation for ".$this->id." template ". $templateName. " not found!");
			} else {				
				$template_id = $retTemplate[0];
				$log->debug("function AccountsRotho::check_first_activation for ".$this->id." assigned template ". $templateName. " with id = ".$template_id);
			}
		} else {
			$template_id = $retTemplate[0];
			$log->debug("function AccountsRotho::check_first_activation for ".$this->id." assigned template ". $templateName. " with id = ".$template_id);
		}	
		schedule_client_notification($template_id, $templateName, $this->id, $this->column_fields['email1'], $this->column_fields['email2'], $this->column_fields["assigned_user_id"],$this->column_fields["createdtime"], $this->column_fields["modifiedtime"],"ND");
	}
	
	
	// check_current_reference_users verifica 
	function check_current_reference_users($template_map) {
		global $log;
		$log->debug("Entering function AccountsRotho::check_current_reference_users");
		$retFields = array();
		// funzione get_account_reference_users restituisce l'agente, l'area manager e il referente interno dell'azienda, è implementata in CommonUtils.php
		$retvals = get_account_reference_users($this->id);
		foreach($template_map as $key=>$val) {
			if($this->column_fields[$key] != '' && $this->column_fields[$key] != $retvals[$key] )
			{
				$templateId = 0;
				$base_language = strtoupper($this->column_fields['cf_1113']); // Lingua Base
				// Cerco template di tipo 'Notifiche Clienti' sulla base della lingua
				$templateName = trim($val." ".trim($base_language));
				$retTemplate = searchTemplate('Notifiche Clienti',$templateName);
				if(empty($retTemplate)) {
					$templateName = trim($val);
					$retTemplate = searchTemplate('Notifiche Clienti',$templateName);
					if(empty($retTemplate)) {
						$log->debug("function AccountsRotho::check_current_reference_users for ".$this->id." template ". $templateName. " not found!");
					} else {				
						$templateId = $retTemplate[0];
						$log->debug("function AccountsRotho::check_current_reference_users for ".$this->id." assigned template ". $templateName. " with id = ".$templateId);
					}
				} else {
					$templateId = $retTemplate[0];
					$log->debug("function AccountsRotho::check_current_reference_users ".$key." differs from actual value, assigned template ". $templateName. " with id = ".$templateId);
				}
				$retFields[$templateName] = $templateId;
				schedule_client_notification($templateId, $templateName, $this->id, $this->column_fields['email1'], $this->column_fields['email2'], $this->column_fields["assigned_user_id"],$this->column_fields["createdtime"], $this->column_fields["modifiedtime"],$retvals[$key]);
			}
		}
		$log->debug("Exiting function AccountsRotho::check_current_reference_users");
		return $retFields;
	}
			
	function send_client_communication($templateid) {
		global $adb,$log,$table_prefix,$default_charset;
		global $HELPDESK_SUPPORT_EMAIL_ID,$HELPDESK_SUPPORT_NAME;	
		$log->debug("Entering function AccountsRotho::send_client_communication ($templateid) for Account with id = ". $this->id);
		$templatedetails = getTemplateDetails($templateid);
		if(!empty($templatedetails) && count($templatedetails) > 1) {
			$body = $templatedetails[1];
			$subject = $templatedetails[2];
			$log->debug("AccountsRotho::send_client_communication mail subject after getTemplateDetails is ".$subject);
			$log->debug("AccountsRotho::send_client_communication mail body after getTemplateDetails is ".$body);
			$subject = getMergedDescription($subject,$this->id,"Accounts");
			$body = getMergedDescription($body,$this->id,"Accounts");
			// $body = getMergedDescription($body,$this->id,"Users");
			// $body = htmlentities($body, ENT_NOQUOTES, $default_charset);
			$body = html_entity_decode($body, ENT_NOQUOTES, $default_charset);
			$log->debug("AccountsRotho::send_client_communication body after getMergedDescription is ".$body);
			$account_email = "";
			$fieldid = 0;
			if (!empty($this->column_fields['email1']) && filter_var($this->column_fields['email1'], FILTER_VALIDATE_EMAIL) ) {
				$account_email = $this->column_fields['email1'];
				$fieldid = 9;
			} elseif (!empty($this->column_fields['email2']) && filter_var($this->column_fields['email2'], FILTER_VALIDATE_EMAIL) ) {
				$account_email = $this->column_fields['email2'];
				$fieldid = 11;
			}			
			if (!empty($account_email)) {
				$log->debug("AccountsRotho::send_client_communication account_email is " . $account_email);
				$status = send_mail("Accounts",$account_email,$HELPDESK_SUPPORT_NAME,$HELPDESK_SUPPORT_EMAIL_ID,$subject,$body,'',$HELPDESK_SUPPORT_EMAIL_ID);
				$log->debug("AccountsRotho::send_client_communication mail status is " . $status);
				if( $status == 1 ) {
					$focus = CRMEntity::getInstance('Emails');
					$focus->column_fields['parent_type'] = "Accounts";
					$focus->column_fields['activitytype'] = "Emails";
					$focus->column_fields['parent_id'] = "$this->id@$fieldid|";
					$focus->column_fields['subject'] = $subject;
					$focus->column_fields['description'] = $body;
					$focus->column_fields['assigned_user_id'] = $this->column_fields['assigned_user_id'];
					$focus->column_fields["date_start"]= date('Y-m-d');
					$focus->column_fields["email_flag"] = 'SAVED';
					$focus->column_fields['from_email'] = $HELPDESK_SUPPORT_EMAIL_ID;
					$focus->column_fields['saved_toid'] = $account_email;
					$focus->save('Emails');
					$log->debug("AccountsRotho::send_client_communication Emails entity saved, id is " . $focus->id);
				}
			} else {
				$log->debug("AccountsRotho::send_client_communication account_email is empty");
			}
		} else {
			$log->debug("AccountsRotho::send_client_communication, input template with id ".$templateid. " does not exists");
		}
		$log->debug("Exiting AccountsRotho::send_client_communication...");
	}
	//danzi.tn@20150205e
	
	/*
	
	
	
	*/
}
?>