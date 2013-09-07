<?php
require_once('modules/Accounts/Accounts.php');

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
}
?>