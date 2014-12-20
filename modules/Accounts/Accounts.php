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
 * $Header: /advent/projects/wesat/vtiger_crm/sugarcrm/modules/Accounts/Accounts.php,v 1.53 2005/04/28 08:06:45 rank Exp $
 * Description:  Defines the Account SugarBean Account entity with the necessary
 * methods and variables.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('data/CRMEntity.php');
require_once('data/Tracker.php');
// Account is used to store vtiger_account information.
class Accounts extends CRMEntity {
	var $log;
	var $db;
	var $table_name;
	var $table_index= 'accountid';	
	var $tab_name = Array();
	var $tab_name_index = Array();
	
	var $entity_table;

	var $column_fields = Array();

	var $sortby_fields = Array('accountname','bill_city','website','phone','smownerid');		

	// This is the list of vtiger_fields that are in the lists.
	var $list_fields = Array();

	var $list_fields_name = Array(
			'Account Name'=>'accountname',
			'City'=>'bill_city',
			'Website'=>'website',
			'Phone'=>'phone',
			'Assigned To'=>'assigned_user_id'
			);
	var $list_link_field= 'accountname';

	var $search_fields = Array();

	var $search_fields_name = Array(
			'Account Name'=>'accountname',
			'City'=>'bill_city',
			);
	// This is the list of vtiger_fields that are required
	var $required_fields =  array("accountname"=>1);

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'accountname';
	var $default_sort_order = 'ASC';
	
	var $customFieldTable = Array(); //vtc
	
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('assigned_user_id', 'createdtime', 'modifiedtime', 'accountname');
	
	//Default Fields for Email Templates -- Pavani
	var $emailTemplate_defaultFields = array('accountname','account_type','industry','annualrevenue','phone','email1','rating','website','fax');
	
	//crmv@10759
	var $search_base_field = 'accountname';
	//crmv@10759 e
	function Accounts() {
		global $log;
		global $table_prefix;
		$this->table_name = $table_prefix."_account";
		$this->tab_name = Array($table_prefix.'_crmentity',$table_prefix.'_account',$table_prefix.'_accountbillads',$table_prefix.'_accountshipads',$table_prefix.'_accountscf');
		$this->tab_name_index = Array($table_prefix.'_crmentity'=>'crmid',$table_prefix.'_account'=>'accountid',$table_prefix.'_accountbillads'=>'accountaddressid',$table_prefix.'_accountshipads'=>'accountaddressid',$table_prefix.'_accountscf'=>'accountid');
	    $this->list_fields = Array(
			'Account Name'=>Array($table_prefix.'_account'=>'accountname'),
			'City'=>Array($table_prefix.'_accountbillads'=>'bill_city'), 
			'Website'=>Array($table_prefix.'_account'=>'website'),
			'Phone'=>Array($table_prefix.'_account'=> 'phone'),
			'Assigned To'=>Array($table_prefix.'_crmentity'=>'smownerid')
			);	
		$this->entity_table = $table_prefix."_crmentity";
		$this->search_fields = Array(
			'Account Name'=>Array($table_prefix.'_account'=>'accountname'),
			'City'=>Array($table_prefix.'_accountbillads'=>'bill_city'), 
			);
		$this->customFieldTable = Array($table_prefix.'_accountscf', 'accountid');
		$this->column_fields = getColumnFields('Accounts');
		$this->db = PearDatabase::getInstance();
		$this->log = $log;
	}

	/** Function to handle module specific operations when saving a entity 
	*/
	function save_module($module)
	{
	}

	/**
	 * Function to get sort order
	 * return string  $sorder    - sortorder string either 'ASC' or 'DESC'
	 */
	function getSortOrder()
	{
		global $log,$currentModule;
		$log->debug("Entering getSortOrder() method ...");
		$use_default_order_by = '';
		//default listview sorting
		if(PerformancePrefs::getBoolean('LISTVIEW_DEFAULT_SORTING', true)) {
				$use_default_sort_order = $this->default_sort_order;
		}
		//crmv default listview customview sorting
		if ($this->customview_sort_order != '' && $use_default_sort_order != $this->customview_sort_order)
				$use_default_sort_order = $this->customview_sort_order;		
		if(isset($_REQUEST['sorder']))
			$sorder = $this->db->sql_escape_string($_REQUEST['sorder']);
		elseif ($_REQUEST['override_orderby'] == 'true')
			$sorder = $use_default_sort_order;
		else
			$sorder = (($_SESSION[$currentModule.'_SORT_ORDER'] != '')?($_SESSION[$currentModule.'_SORT_ORDER']):($use_default_sort_order));

		$log->debug("Exiting getSortOrder method ...");
		return $sorder;
	}

	/**
	 * Function to get order by
	 * return string  $order_by    - fieldname(eg: 'campaignname')
	 */
	function getOrderBy()
	{
		global $log,$currentModule;
		$log->debug("Entering getOrderBy() method ...");
		$use_default_order_by = '';
		//default listview sorting
		if(PerformancePrefs::getBoolean('LISTVIEW_DEFAULT_SORTING', true)) {
				$use_default_order_by = $this->default_order_by;
		}
		//crmv default listview customview sorting
		if ($this->customview_order_by != '' && $use_default_order_by != $this->customview_order_by)
				$use_default_order_by = $this->customview_order_by;
		if (isset($_REQUEST['order_by']))
			$order_by = $this->db->sql_escape_string($_REQUEST['order_by']);
		elseif ($_REQUEST['override_orderby'] == 'true')
			$order_by = $use_default_order_by;
		else	
			$order_by = (($_SESSION[$currentModule.'_ORDER_BY'] != '')?($_SESSION[$currentModule.'_ORDER_BY']):($use_default_order_by));

		$log->debug("Exiting getOrderBy method ...");
		return $order_by;
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

		$query = "SELECT ".$table_prefix."_contactdetails.*,
			".$table_prefix."_crmentity.crmid,
                        ".$table_prefix."_crmentity.smownerid,
			".$table_prefix."_account.accountname,
			case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name
			FROM ".$table_prefix."_contactdetails
			INNER JOIN ".$table_prefix."_contactscf
				ON ".$table_prefix."_contactscf.contactid = ".$table_prefix."_contactdetails.contactid
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
					
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 
		
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_contacts method ...");		
		return $return_value;
	}

	/** Returns a list of the associated opportunities
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	 */
	function get_opportunities($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user,$table_prefix;
		$log->debug("Entering get_opportunities(".$id.") method ...");
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
				$button .= "<input title='".getTranslatedString('LBL_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		} 

		$query = "SELECT ".$table_prefix."_potential.potentialid, ".$table_prefix."_potential.related_to,
			".$table_prefix."_potential.potentialname, ".$table_prefix."_potential.sales_stage,
			".$table_prefix."_potential.potentialtype, ".$table_prefix."_potential.amount,
			".$table_prefix."_potential.closingdate, ".$table_prefix."_account.accountname,
			case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name,".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.smownerid
			FROM ".$table_prefix."_potential
			INNER JOIN ".$table_prefix."_potentialscf
				ON ".$table_prefix."_potentialscf.potentialid = ".$table_prefix."_potential.potentialid
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_potential.potentialid
			LEFT JOIN ".$table_prefix."_account
				ON ".$table_prefix."_account.accountid = ".$table_prefix."_potential.related_to
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_crmentity.smownerid = ".$table_prefix."_users.id
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			WHERE ".$table_prefix."_crmentity.deleted = 0
			AND ".$table_prefix."_potential.related_to = ".$id;
					
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 
		
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_opportunities method ...");		
		return $return_value;
	}

	/** Returns a list of the associated tasks
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	 */
	function get_activities($id, $cur_tab_id, $rel_tab_id, $actions=false) {
			global $log, $singlepane_view,$currentModule,$current_user,$table_prefix;
		$log->debug("Entering get_activities(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance('Activity');
        vtlib_setup_modulevars($related_module, $other);		
		$singular_modname = vtlib_toSingular($related_module);
		
		$parenttab = getParentTab();
		
		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;
		
		$button = '';
				
		$button .= '<input type="hidden" name="activity_mode">';
		
		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_NEW'). " ". getTranslatedString('LBL_TODO', $related_module) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";this.form.return_module.value=\"$this_module\";this.form.activity_mode.value=\"Task\";' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString('LBL_TODO', $related_module) ."'>&nbsp;";
				$button .= "<input title='".getTranslatedString('LBL_NEW'). " ". getTranslatedString('LBL_EVENT', $related_module) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";this.form.return_module.value=\"$this_module\";this.form.activity_mode.value=\"Events\";' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString('LBL_EVENT', $related_module) ."'>";
			}
		}
		
		$query = "SELECT ".$table_prefix."_activity.*, 
			".$table_prefix."_contactdetails.lastname,
			".$table_prefix."_contactdetails.firstname,
			".$table_prefix."_crmentity.crmid, 
			".$table_prefix."_crmentity.smownerid,
			".$table_prefix."_crmentity.modifiedtime,
			case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name
			FROM ".$table_prefix."_activity
			INNER JOIN ".$table_prefix."_activitycf
				ON ".$table_prefix."_activitycf.activityid = ".$table_prefix."_activity.activityid
			INNER JOIN ".$table_prefix."_seactivityrel
				ON ".$table_prefix."_seactivityrel.activityid = ".$table_prefix."_activity.activityid
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_activity.activityid
			LEFT JOIN ".$table_prefix."_cntactivityrel
				ON ".$table_prefix."_cntactivityrel.activityid = ".$table_prefix."_activity.activityid
			LEFT JOIN ".$table_prefix."_contactdetails
		       		ON ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_cntactivityrel.contactid
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			LEFT OUTER JOIN ".$table_prefix."_recurringevents
				ON ".$table_prefix."_recurringevents.activityid = ".$table_prefix."_activity.activityid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			WHERE ".$table_prefix."_seactivityrel.crmid = ".$id."
			AND ".$table_prefix."_crmentity.deleted = 0 ";
//crmv@8398 		
			$query.=getCalendarSql();
//crmv@8398e
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 
		
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_activities method ...");		
		return $return_value;
	}
	/**
	 * Function to get Account related Task & Event which have activity type Held, Completed or Deferred.
 	 * @param  integer   $id      - accountid
 	 * returns related Task or Event record in array format
 	 */
	function get_history($id)
	{
		global $log;
		global $table_prefix;
                $log->debug("Entering get_history(".$id.") method ...");
		$query = "SELECT ".$table_prefix."_crmentity.crmid,".$table_prefix."_activity.activityid, ".$table_prefix."_activity.subject,
			".$table_prefix."_activity.status, ".$table_prefix."_activity.eventstatus,
			".$table_prefix."_activity.activitytype, ".$table_prefix."_activity.date_start, ".$table_prefix."_activity.due_date,
			".$table_prefix."_activity.time_start, ".$table_prefix."_activity.time_end,
			".$table_prefix."_crmentity.modifiedtime, ".$table_prefix."_crmentity.createdtime,
			".$table_prefix."_crmentity.description,case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name
			FROM ".$table_prefix."_activity
			INNER JOIN ".$table_prefix."_activitycf
				ON ".$table_prefix."_activitycf.activityid = ".$table_prefix."_activity.activityid
			INNER JOIN ".$table_prefix."_seactivityrel
				ON ".$table_prefix."_seactivityrel.activityid = ".$table_prefix."_activity.activityid
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_activity.activityid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_users.id=".$table_prefix."_crmentity.smownerid
			WHERE ".$table_prefix."_seactivityrel.crmid = ".$id."
			AND ".$table_prefix."_crmentity.deleted = 0";
//crmv@8398 			
		$query.=getCalendarSql('history');
//crmv@8398e	
		//Don't add order by, because, for security, one more condition will be added with this query in include/RelatedListView.php
		$log->debug("Exiting get_history method ...");
		return getHistory('Accounts',$query,$id);
	}

	/** Returns a list of the associated emails
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	*/
	function get_emails($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user,$table_prefix;
		$log->debug("Entering get_emails(".$id.") method ...");
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
		
		$button .= '<input type="hidden" name="email_directing_module"><input type="hidden" name="record">';	
                    
		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='". getTranslatedString('LBL_ADD_NEW')." ". getTranslatedString($singular_modname)."' accessyKey='F' class='crmbutton small create' onclick='fnvshobj(this,\"sendmail_cont\");sendmail(\"$this_module\",$id);' type='button' name='button' value='". getTranslatedString('LBL_ADD_NEW')." ". getTranslatedString($singular_modname)."'></td>";
			}
		} 

		//crmv@16265	//crmv@18039	//crmv@31263
		$query = "SELECT case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name,
			".$table_prefix."_activity.activityid, ".$table_prefix."_activity.subject,
			".$table_prefix."_activity.activitytype, ".$table_prefix."_crmentity.modifiedtime,
			".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.smownerid, ".$table_prefix."_activity.date_start, ".$table_prefix."_seactivityrel.crmid as parent_id 
			FROM ".$table_prefix."_activity
			INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_activity.activityid
			INNER JOIN ".$table_prefix."_seactivityrel ON ".$table_prefix."_seactivityrel.activityid = ".$table_prefix."_activity.activityid
			LEFT JOIN ".$table_prefix."_emaildetails ON ".$table_prefix."_emaildetails.emailid = ".$table_prefix."_activity.activityid
			LEFT JOIN ".$table_prefix."_account ON ".$table_prefix."_account.accountid = ".$table_prefix."_seactivityrel.crmid
			LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			WHERE ".$table_prefix."_crmentity.deleted = 0 AND ".$table_prefix."_activity.activitytype='Emails' AND ".$table_prefix."_emaildetails.email_flag <> 'DRAFT'
			AND ".$table_prefix."_account.accountid = ".$id;
		//crmv@16265e	//crmv@18039e	//crmv@31263e
					
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 
		
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_emails method ...");		
		return $return_value;
	}	

		/**
	* Function to get Account related Quotes
	* @param  integer   $id      - accountid
	* returns related Quotes record in array format
	*/
	function get_quotes($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user,$table_prefix;
		$log->debug("Entering get_quotes(".$id.") method ...");
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

		$query = "SELECT case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name,
			".$table_prefix."_crmentity.*,
			".$table_prefix."_quotes.*,
			".$table_prefix."_potential.potentialname,
			".$table_prefix."_account.accountname
			FROM ".$table_prefix."_quotes
			INNER JOIN ".$table_prefix."_quotescf
				ON ".$table_prefix."_quotescf.quoteid = ".$table_prefix."_quotes.quoteid
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_quotes.quoteid
			LEFT OUTER JOIN ".$table_prefix."_account
				ON ".$table_prefix."_account.accountid = ".$table_prefix."_quotes.accountid
			LEFT OUTER JOIN ".$table_prefix."_potential
				ON ".$table_prefix."_potential.potentialid = ".$table_prefix."_quotes.potentialid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_crmentity.smownerid = ".$table_prefix."_users.id
			WHERE ".$table_prefix."_crmentity.deleted = 0
			AND ".$table_prefix."_account.accountid = ".$id;
					
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 
		
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_quotes method ...");		
		return $return_value;
	}
		/**
	* Function to get Account related Invoices 
	* @param  integer   $id      - accountid
	* returns related Invoices record in array format
	*/
	function get_invoices($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user,$table_prefix;
		$log->debug("Entering get_invoices(".$id.") method ...");
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

		$query = "SELECT case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name,
			".$table_prefix."_crmentity.*,
			".$table_prefix."_invoice.*,
			".$table_prefix."_account.accountname,
			".$table_prefix."_salesorder.subject AS salessubject
			FROM ".$table_prefix."_invoice
			INNER JOIN ".$table_prefix."_invoicecf
				ON ".$table_prefix."_invoicecf.invoiceid = ".$table_prefix."_invoice.invoiceid
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_invoice.invoiceid
			LEFT OUTER JOIN ".$table_prefix."_account
				ON ".$table_prefix."_account.accountid = ".$table_prefix."_invoice.accountid
			LEFT OUTER JOIN ".$table_prefix."_salesorder
				ON ".$table_prefix."_salesorder.salesorderid = ".$table_prefix."_invoice.salesorderid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_crmentity.smownerid = ".$table_prefix."_users.id
			WHERE ".$table_prefix."_crmentity.deleted = 0
			AND ".$table_prefix."_account.accountid = ".$id;
					
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 
		
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_invoices method ...");		
		return $return_value;
	}

	/**
	* Function to get Account related SalesOrder 
	* @param  integer   $id      - accountid
	* returns related SalesOrder record in array format
	*/
	function get_salesorder($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user,$table_prefix;
		$log->debug("Entering get_salesorder(".$id.") method ...");
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

		$query = "SELECT ".$table_prefix."_crmentity.*,
			".$table_prefix."_salesorder.*,
			".$table_prefix."_quotes.subject AS quotename,
			".$table_prefix."_account.accountname,
			case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name
			FROM ".$table_prefix."_salesorder
			INNER JOIN ".$table_prefix."_salesordercf
				ON ".$table_prefix."_salesordercf.salesorderid = ".$table_prefix."_salesorder.salesorderid
			INNER JOIN ".$table_prefix."_sobillads
				ON ".$table_prefix."_sobillads.sobilladdressid = ".$table_prefix."_salesorder.salesorderid 
			INNER JOIN ".$table_prefix."_soshipads
				ON ".$table_prefix."_soshipads.soshipaddressid = ".$table_prefix."_salesorder.salesorderid 
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_salesorder.salesorderid
			LEFT OUTER JOIN ".$table_prefix."_quotes
				ON ".$table_prefix."_quotes.quoteid = ".$table_prefix."_salesorder.quoteid
			LEFT OUTER JOIN ".$table_prefix."_account
				ON ".$table_prefix."_account.accountid = ".$table_prefix."_salesorder.accountid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_crmentity.smownerid = ".$table_prefix."_users.id
			WHERE ".$table_prefix."_crmentity.deleted = 0
			AND ".$table_prefix."_salesorder.accountid = ".$id;	
					
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 
		
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_salesorder method ...count(return_value) = " .count($return_value));		
		return $return_value;
	}
	
	/**
	* Function to get Account related Tickets
	* @param  integer   $id      - accountid
	* returns related Ticket record in array format
	*/
	function get_tickets($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user,$table_prefix;
		$log->debug("Entering get_tickets(".$id.") method ...");
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
				
		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'parent_id') == '0') {
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

		$query = "SELECT case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name, ".$table_prefix."_users.id,
			".$table_prefix."_troubletickets.title, ".$table_prefix."_troubletickets.ticketid AS crmid,
			".$table_prefix."_troubletickets.status, ".$table_prefix."_troubletickets.priority,
			".$table_prefix."_troubletickets.parent_id, ".$table_prefix."_troubletickets.ticket_no,
			".$table_prefix."_crmentity.smownerid, ".$table_prefix."_crmentity.modifiedtime
			FROM ".$table_prefix."_troubletickets
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_troubletickets.ticketid
			INNER JOIN ".$table_prefix."_ticketcf
				ON ".$table_prefix."_ticketcf.ticketid = ".$table_prefix."_troubletickets.ticketid
			LEFT JOIN ".$table_prefix."_account
				ON ".$table_prefix."_account.accountid = ".$table_prefix."_troubletickets.parent_id
			LEFT JOIN ".$table_prefix."_contactdetails
			        ON ".$table_prefix."_contactdetails.contactid=".$table_prefix."_troubletickets.parent_id
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_users.id=".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			WHERE  ".$table_prefix."_crmentity.deleted = 0 and ".$table_prefix."_troubletickets.parent_id=$id" ;
		//crmv@16643
		$query .= " or ".$table_prefix."_troubletickets.parent_id in(";
		$query_contacts = "
			SELECT ".$table_prefix."_contactdetails.contactid
			FROM ".$table_prefix."_contactdetails
			INNER JOIN ".$table_prefix."_crmentity ".$table_prefix."_crmentityCont
				ON ".$table_prefix."_crmentityCont.crmid = ".$table_prefix."_contactdetails.contactid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentityCont.smownerid
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_crmentityCont.smownerid = ".$table_prefix."_users.id
			WHERE ".$table_prefix."_crmentityCont.deleted = 0
			AND ".$table_prefix."_contactdetails.accountid = ".$id;
			$secQuery = getNonAdminAccessControlQuery('Contacts', $current_user, 'Cont');
		if(strlen($secQuery) > 1) {
			$query_contacts = appendFromClauseToQuery($query_contacts, $secQuery);
		}
		$query.="$query_contacts)";
		//crmv@16643e
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 
		
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_tickets method ...");		
		return $return_value;
	}
	
	/**
	* Function to get Account related Products 
	* @param  integer   $id      - accountid
	* returns related Products record in array format
	*/
	function get_products($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user,$table_prefix;
		$log->debug("Entering get_products(".$id.") method ...");
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

		$query = "SELECT ".$table_prefix."_products.productid, ".$table_prefix."_products.productname,
			".$table_prefix."_products.productcode, ".$table_prefix."_products.commissionrate,
			".$table_prefix."_products.qty_per_unit, ".$table_prefix."_products.unit_price,
			".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.smownerid
			FROM ".$table_prefix."_products
			INNER JOIN ".$table_prefix."_productcf ON ".$table_prefix."_productcf.productid = ".$table_prefix."_products.productid
			INNER JOIN ".$table_prefix."_seproductsrel ON ".$table_prefix."_products.productid = ".$table_prefix."_seproductsrel.productid and ".$table_prefix."_seproductsrel.setype='Accounts'
			INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_products.productid
			INNER JOIN ".$table_prefix."_account ON ".$table_prefix."_account.accountid = ".$table_prefix."_seproductsrel.crmid
			WHERE ".$table_prefix."_crmentity.deleted = 0 AND ".$table_prefix."_account.accountid = $id";
					
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);
		
		if($return_value == null) $return_value = Array();
		//crmv@16644
		else $return_value = $this->add_ordered_quantity($return_value,$id,$related_module);
		//crmv@16644e
			
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_products method ...");		
		return $return_value;
	}

	//ds@8 project tool
	function get_projects($id, $cur_tab_id, $rel_tab_id, $actions=false)
  	{
		global $singlepane_view,$currentModule,$current_user,$table_prefix;
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

		$query = "SELECT ".$table_prefix."_projects.*,
			".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.smownerid,
			".$table_prefix."_account.accountname,
			case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name
			FROM ".$table_prefix."_projects
			INNER JOIN ".$table_prefix."_projectscf
				ON ".$table_prefix."_projectscf.projectid = ".$table_prefix."_projects.projectid
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_projects.projectid
			LEFT JOIN ".$table_prefix."_account
				ON ".$table_prefix."_account.accountid = ".$table_prefix."_projects.accountid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_crmentity.smownerid = ".$table_prefix."_users.id
			WHERE ".$table_prefix."_crmentity.deleted = 0
			AND ".$table_prefix."_projects.accountid = ".$id;
					
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 
		
		if($return_value == null) 
     		$return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		return $return_value;
  	}
	//ds@8e

	/** Function to export the account records in CSV Format
	* @param reference variable - where condition is passed when the query is executed
	* Returns Export Accounts Query.
	*/
	function create_export_query($where,$oCustomView,$viewId)	//crmv@31775
	{
		global $log;
		global $current_user;
		global $table_prefix;
                $log->debug("Entering create_export_query(".$where.") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Accounts", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT $fields_list,case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name 
	       			FROM ".$this->entity_table."
				INNER JOIN ".$table_prefix."_account
					ON ".$table_prefix."_account.accountid = ".$table_prefix."_crmentity.crmid
				LEFT JOIN ".$table_prefix."_accountbillads
					ON ".$table_prefix."_accountbillads.accountaddressid = ".$table_prefix."_account.accountid
				LEFT JOIN ".$table_prefix."_accountshipads
					ON ".$table_prefix."_accountshipads.accountaddressid = ".$table_prefix."_account.accountid
				LEFT JOIN ".$table_prefix."_accountscf
					ON ".$table_prefix."_accountscf.accountid = ".$table_prefix."_account.accountid
	                        LEFT JOIN ".$table_prefix."_groups
                        	        ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				LEFT JOIN ".$table_prefix."_users
					ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid and ".$table_prefix."_users.status = 'Active'
				LEFT JOIN ".$table_prefix."_account ".$table_prefix."_account2 
					ON ".$table_prefix."_account2.accountid = ".$table_prefix."_account.parentid
				";//vtiger_account2 is added to get the Member of account
		
		//crmv@31775
		$reportFilter = $oCustomView->getReportFilter($viewId);
		if ($reportFilter) {
			$tableNameTmp = $oCustomView->getReportFilterTableName($reportFilter,$current_user->id);
			$query .= " INNER JOIN $tableNameTmp ON $tableNameTmp.id = {$table_prefix}_crmentity.crmid";
		}
		//crmv@31775e

		$query .= $this->getNonAdminAccessControlQuery('Accounts',$current_user);
		$where_auto = " ".$table_prefix."_crmentity.deleted = 0 ";

		if($where != "")
			$query .= " WHERE ($where) AND ".$where_auto;
		else
			$query .= " WHERE ".$where_auto;
		$query = $this->listQueryNonAdminChange($query, 'Accounts');
		$log->debug("Exiting create_export_query method ...");
		return $query;
	}

	/** Function to get the Columnnames of the Account Record
	* Used By vtigerCRM Word Plugin
	* Returns the Merge Fields for Word Plugin
	*/
	function getColumnNames_Acnt()
	{
		global $log,$current_user,$table_prefix;
		$log->debug("Entering getColumnNames_Acnt() method ...");
		require('user_privileges/user_privileges_'.$current_user->id.'.php');
		if($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0)
		{
			$sql1 = "SELECT fieldlabel FROM ".$table_prefix."_field WHERE tabid = 6";
			$params1 = array();
		}else
		{
			$profileList = getCurrentUserProfileList();
			$sql1 = "select ".$table_prefix."_field.fieldid,fieldlabel from ".$table_prefix."_field inner join ".$table_prefix."_def_org_field on ".$table_prefix."_def_org_field.fieldid=".$table_prefix."_field.fieldid where ".$table_prefix."_field.tabid=6 and ".$table_prefix."_field.displaytype in (1,2,4) and ".$table_prefix."_def_org_field.visible=0";
			$params1 = array();
		    $sql1.=" AND EXISTS(SELECT * FROM ".$table_prefix."_profile2field WHERE ".$table_prefix."_profile2field.fieldid = ".$table_prefix."_field.fieldid ";
		        if (count($profileList) > 0) {
			  	 	$sql1.=" AND ".$table_prefix."_profile2field.profileid IN (". generateQuestionMarks($profileList) .") ";
			  	 	array_push($params1, $profileList);
			} 			  
		    $sql1.=" AND ".$table_prefix."_profile2field.visible = 0) ";
		} 
		$result = $this->db->pquery($sql1, $params1);
		$numRows = $this->db->num_rows($result);
		for($i=0; $i < $numRows;$i++)
		{
			$custom_fields[$i] = $this->db->query_result($result,$i,"fieldlabel");
			$custom_fields[$i] = ereg_replace(" ","",$custom_fields[$i]);
			$custom_fields[$i] = strtoupper($custom_fields[$i]);
		}
		$mergeflds = $custom_fields;
		$log->debug("Exiting getColumnNames_Acnt method ...");
		return $mergeflds;
	}

	//crmv@7216
	/** Returns a list of the associated faxes
	*/
	function get_faxes($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user,$table_prefix;
		$log->debug("Entering get_faxes(".$id.") method ...");
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
		
		$button .= '<input type="hidden" name="fax_directing_module"><input type="hidden" name="record">';	
                    
		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='". getTranslatedString('LBL_ADD_NEW')." ". getTranslatedString($singular_modname)."' accessyKey='F' class='crmbutton small create' onclick='fnvshobj(this,\"sendfax_cont\");sendfax(\"$this_module\",$id);' type='button' name='button' value='". getTranslatedString('LBL_ADD_NEW')." ". getTranslatedString($singular_modname)."'>&nbsp;";
			}
		}

		$query = "SELECT case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name,
			".$table_prefix."_activity.activityid, ".$table_prefix."_activity.subject,
			".$table_prefix."_activity.activitytype, ".$table_prefix."_crmentity.modifiedtime,
			".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.smownerid, ".$table_prefix."_activity.date_start, ".$table_prefix."_seactivityrel.crmid as parent_id 
			FROM ".$table_prefix."_activity, ".$table_prefix."_seactivityrel, ".$table_prefix."_account, ".$table_prefix."_users, ".$table_prefix."_crmentity
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid=".$table_prefix."_crmentity.smownerid
			WHERE ".$table_prefix."_seactivityrel.activityid = ".$table_prefix."_activity.activityid
				AND ".$table_prefix."_account.accountid = ".$table_prefix."_seactivityrel.crmid
				AND ".$table_prefix."_users.id=".$table_prefix."_crmentity.smownerid
				AND ".$table_prefix."_crmentity.crmid = ".$table_prefix."_activity.activityid
				AND ".$table_prefix."_account.accountid = ".$id."
				AND ".$table_prefix."_activity.activitytype='Fax'
				AND ".$table_prefix."_crmentity.deleted = 0";
					
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 
		
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_faxes method ...");		
		return $return_value;
	}		
	//crmv@7216e
	
	//vtc
	function get_members($id, $cur_tab_id, $rel_tab_id, $actions=false)
	{	
		global $log, $singlepane_view,$currentModule,$current_user,$table_prefix;
		$log->debug("Entering get_visitreport(".$id.") method ...");
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
		$query = "SELECT 
					".$table_prefix."_account.accountid, 
					".$table_prefix."_crmentity.crmid, 
					".$table_prefix."_account.accountname, 
					".$table_prefix."_accountbillads.bill_city, 
					".$table_prefix."_account.website, 
					".$table_prefix."_account.phone, 
					case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name, 
					".$table_prefix."_crmentity.smownerid
					FROM ".$table_prefix."_account
					INNER JOIN ".$table_prefix."_accountscf
		                ON ".$table_prefix."_accountscf.accountid = ".$table_prefix."_account.accountid
		            INNER JOIN ".$table_prefix."_crmentity
		                ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_account.accountid
		            INNER JOIN ".$table_prefix."_accountbillads
		                ON ".$table_prefix."_account.accountid = ".$table_prefix."_accountbillads.accountaddressid
		            INNER JOIN ".$table_prefix."_accountshipads
		                ON ".$table_prefix."_account.accountid = ".$table_prefix."_accountshipads.accountaddressid
		            LEFT JOIN ".$table_prefix."_groups
		                ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
		            LEFT JOIN ".$table_prefix."_users
		                ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
		            LEFT JOIN ".$table_prefix."_account ".$table_prefix."_account2
		                ON ".$table_prefix."_account.parentid = ".$table_prefix."_account2.accountid   
		            WHERE ".$table_prefix."_crmentity.deleted = 0 
					AND ".$table_prefix."_account.parentid = ".$id;
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 
		
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_faxes method ...");		
		return $return_value;
	}
	//vtc e
	
	/** Returns a list of the associated Campaigns
	  * @param $id -- campaign id :: Type Integer
	  * @returns list of campaigns in array format
	  */
	      
	function get_campaigns($id, $cur_tab_id, $rel_tab_id, $actions=false)
	{
		global $log, $singlepane_view,$currentModule,$current_user,$table_prefix;
		$log->debug("Entering get_visitreport(".$id.") method ...");
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
		$query = " SELECT
			CASE WHEN (".$table_prefix."_users.user_name is not null) THEN ".$table_prefix."_users.user_name ELSE ".$table_prefix."_groups.groupname END AS user_name, 
			".$table_prefix."_campaign.campaignid, 
			".$table_prefix."_campaign.campaignname, 
			".$table_prefix."_campaign.campaigntype, 
			  ".$table_prefix."_campaign.campaignstatus,
			  ".$table_prefix."_campaign.expectedrevenue,
			  ".$table_prefix."_campaign.closingdate,
			  ".$table_prefix."_crmentity.crmid,
			  ".$table_prefix."_crmentity.smownerid,
			  ".$table_prefix."_crmentity.modifiedtime
			FROM ".$table_prefix."_campaign
			  INNER JOIN ".$table_prefix."_campaignscf
			    ON ".$table_prefix."_campaignscf.campaignid = ".$table_prefix."_campaign.campaignid
			  INNER JOIN ".$table_prefix."_campaignaccountrel
			    ON ".$table_prefix."_campaignaccountrel.campaignid = ".$table_prefix."_campaign.campaignid
			  INNER JOIN ".$table_prefix."_crmentity
			    ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_campaign.campaignid
			  LEFT JOIN ".$table_prefix."_groups
			    ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			  LEFT JOIN ".$table_prefix."_users
			    ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			WHERE ".$table_prefix."_campaignaccountrel.accountid = ".$id."
			    AND ".$table_prefix."_crmentity.deleted = 0";
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 
		
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_faxes method ...");		
		return $return_value;

	}
	
	//crmv@16644
	function get_services($id, $cur_tab_id, $rel_tab_id, $actions=false) {

		global $currentModule, $app_strings, $singlepane_view,$table_prefix;

		$parenttab = getParentTab();

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);
		
		// Some standard module class doesn't have required variables
		// that are used in the query, they are defined in this generic API
		vtlib_setup_modulevars($currentModule, $this);
		vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$button = '';
		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module,$related_module). "' class='crmbutton small edit' " .
						" type='button' onclick=\"openPopup('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\"" . // crmv@21048m
						" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module,$related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname,$related_module) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname,$related_module) ."'>&nbsp;";
			}
		}

		// To make the edit or del link actions to return back to same view.
		if($singlepane_view == 'true') $returnset = "&return_module=$currentModule&return_action=DetailView&return_id=$id";
		else $returnset = "&return_module=$currentModule&return_action=CallRelatedList&return_id=$id";

		$query = "SELECT ".$table_prefix."_crmentity.crmid";
		//crmv@fix query
		foreach ($other->list_fields as $label=>$arr){
			foreach ($arr as $table=>$field){
				if ($table != 'crmentity' && !is_numeric($table) && $field){
					if (strpos($table,$table_prefix.'_') !== false)
						$query.=",$table.$field";
					else
						$query.=",".$table_prefix."_$table.$field";
				}	
			}
		}
		//crmv@fix query end
		$query .= ", CASE WHEN (".$table_prefix."_users.user_name is not null) THEN ".$table_prefix."_users.user_name ELSE ".$table_prefix."_groups.groupname END AS user_name";
		
		$more_relation = '';
		if(!empty($other->related_tables)) {
			foreach($other->related_tables as $tname=>$relmap) {
				// Setup the default JOIN conditions if not specified
				if(empty($relmap[1])) $relmap[1] = $other->table_name;
				if(empty($relmap[2])) $relmap[2] = $relmap[0];
				$more_relation .= " LEFT JOIN $tname ON $tname.$relmap[0] = $relmap[1].$relmap[2]";
			}
		}

		$query .= " FROM $other->table_name";
		$query .= " INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = $other->table_name.$other->table_index";
		if ($related_module == 'Products'){
			$query .= " INNER JOIN ".$table_prefix."_seproductsrel ON (".$table_prefix."_seproductsrel.crmid = ".$table_prefix."_crmentity.crmid OR ".$table_prefix."_seproductsrel.productid = ".$table_prefix."_crmentity.crmid)";
		}
		elseif ($related_module == 'Documents'){
			$query .= " INNER JOIN ".$table_prefix."_senotesrel ON (".$table_prefix."_senotesrel.notesid = ".$table_prefix."_crmentity.crmid OR ".$table_prefix."_senotesrel.crmid = ".$table_prefix."_crmentity.crmid)";
		}
		else
			$query .= " INNER JOIN ".$table_prefix."_crmentityrel ON (".$table_prefix."_crmentityrel.relcrmid = ".$table_prefix."_crmentity.crmid OR ".$table_prefix."_crmentityrel.crmid = ".$table_prefix."_crmentity.crmid)";
		$query .= " LEFT  JOIN $this->table_name   ON $this->table_name.$this->table_index = $other->table_name.$other->table_index";
		$query .= $more_relation;
		$query .= " LEFT  JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid";
		$query .= " LEFT  JOIN ".$table_prefix."_groups       ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid";	
		if ($related_module == 'Products'){
			$query .= " WHERE ".$table_prefix."_crmentity.deleted = 0 AND (".$table_prefix."_seproductsrel.crmid = $id OR ".$table_prefix."_seproductsrel.productid = $id)";
		}
		elseif ($related_module == 'Documents'){
			$query .= " WHERE ".$table_prefix."_crmentity.deleted = 0 AND (".$table_prefix."_senotesrel.crmid = $id OR ".$table_prefix."_senotesrel.notesid = $id)";
		}
		else					
			$query .= " WHERE ".$table_prefix."_crmentity.deleted = 0 AND (".$table_prefix."_crmentityrel.crmid = $id OR ".$table_prefix."_crmentityrel.relcrmid = $id)";

		$return_value = GetRelatedList($currentModule, $related_module, $other, $query, $button, $returnset);	

		if($return_value == null) $return_value = Array();
		else
			$return_value = $this->add_ordered_quantity($return_value,$id,$related_module);

		$return_value['CUSTOM_BUTTON'] = $button;

		return $return_value;
	}
	
	function add_ordered_quantity($related_list,$id,$module)
	{
		global $adb,$mod_strings,$table_prefix;
		
		$fieldPos = count($related_list['header'])-1;
		$related_list['header'][] = $related_list['header'][$fieldPos];
		$related_list['header'][$fieldPos] = $mod_strings['LBL_SOLD_QUANTITY'];
		
		$result = $adb->query("SELECT ".$table_prefix."_inventoryproductrel.productid,SUM(".$table_prefix."_inventoryproductrel.quantity) AS quantity FROM crmv_inventorytoacc
								INNER JOIN ".$table_prefix."_crmentity crmentityServices ON crmentityServices.crmid = crmv_inventorytoacc.id
								INNER JOIN ".$table_prefix."_crmentity crmentityOrders ON crmentityOrders.crmid = crmv_inventorytoacc.sorderid
								INNER JOIN ".$table_prefix."_inventoryproductrel ON ".$table_prefix."_inventoryproductrel.id = crmv_inventorytoacc.sorderid AND ".$table_prefix."_inventoryproductrel.productid = crmv_inventorytoacc.id
								WHERE crmv_inventorytoacc.type = '$module' AND crmentityOrders.deleted = 0 AND crmentityServices.deleted = 0 AND crmv_inventorytoacc.accountid = $id
								GROUP BY productid");
		$ordered_quantity = array();
		while($row=$adb->fetchByAssoc($result)) {
			$ordered_quantity[$row['productid']] = $row['quantity'];
		}
		
		if (!empty($related_list['entries'])) {	//crmv@26896
			foreach($related_list['entries'] as $key => &$entry)
			{
				$entry[] = $entry[$fieldPos];
				$entry[$fieldPos] = $ordered_quantity[$key];
			}
		}	//crmv@26896
		return $related_list;
	}	
	//crmv@16644e
	
	// Function to unlink the dependent records of the given record by id
	function unlinkDependencies($module, $id) {
		global $log,$table_prefix;
		//Deleting Account related Potentials.
		$pot_q = 'SELECT '.$table_prefix.'_crmentity.crmid FROM '.$table_prefix.'_crmentity 
			INNER JOIN '.$table_prefix.'_potential ON '.$table_prefix.'_crmentity.crmid='.$table_prefix.'_potential.potentialid  
			LEFT JOIN '.$table_prefix.'_account ON '.$table_prefix.'_account.accountid='.$table_prefix.'_potential.related_to 
			WHERE '.$table_prefix.'_crmentity.deleted=0 AND '.$table_prefix.'_potential.related_to=?';	
		$pot_res = $this->db->pquery($pot_q, array($id));
		$pot_ids_list = array();
		for($k=0;$k < $this->db->num_rows($pot_res);$k++)
		{
			$pot_id = $this->db->query_result($pot_res,$k,"crmid");
			$pot_ids_list[] = $pot_id;
			$sql = 'UPDATE '.$table_prefix.'_crmentity SET deleted = 1 WHERE crmid = ?';
			$this->db->pquery($sql, array($pot_id));
		}
		//Backup deleted Account related Potentials.
		$params = array($id, RB_RECORD_UPDATED, $table_prefix.'_crmentity', 'deleted', 'crmid', implode(",", $pot_ids_list));
		$this->db->pquery('INSERT INTO '.$table_prefix.'_relatedlists_rb VALUES(?,?,?,?,?,?)', $params);
		
		//Deleting Account related Quotes.
		$quo_q = 'SELECT '.$table_prefix.'_crmentity.crmid FROM '.$table_prefix.'_crmentity 
			INNER JOIN '.$table_prefix.'_quotes ON '.$table_prefix.'_crmentity.crmid='.$table_prefix.'_quotes.quoteid 
			INNER JOIN '.$table_prefix.'_account ON '.$table_prefix.'_account.accountid='.$table_prefix.'_quotes.accountid 
			WHERE '.$table_prefix.'_crmentity.deleted=0 AND '.$table_prefix.'_quotes.accountid=?';
		$quo_res = $this->db->pquery($quo_q, array($id));
		$quo_ids_list = array();
		for($k=0;$k < $this->db->num_rows($quo_res);$k++)
		{
			$quo_id = $this->db->query_result($quo_res,$k,"crmid");
			$quo_ids_list[] = $quo_id;
			$sql = 'UPDATE '.$table_prefix.'_crmentity SET deleted = 1 WHERE crmid = ?';
			$this->db->pquery($sql, array($quo_id));
		}
		//Backup deleted Account related Quotes.
		$params = array($id, RB_RECORD_UPDATED, $table_prefix.'_crmentity', 'deleted', 'crmid', implode(",", $quo_ids_list));
		$this->db->pquery('INSERT INTO '.$table_prefix.'_relatedlists_rb VALUES(?,?,?,?,?,?)', $params);
		
		//Backup Contact-Account Relation
		$con_q = 'SELECT contactid FROM '.$table_prefix.'_contactdetails WHERE accountid = ?';
		$con_res = $this->db->pquery($con_q, array($id));
		if ($this->db->num_rows($con_res) > 0) {
			$con_ids_list = array();
			for($k=0;$k < $this->db->num_rows($con_res);$k++)
			{
				$con_ids_list[] = $this->db->query_result($con_res,$k,"contactid");
			}
			$params = array($id, RB_RECORD_UPDATED, $table_prefix.'_contactdetails', 'accountid', 'contactid', implode(",", $con_ids_list));
			$this->db->pquery('INSERT INTO '.$table_prefix.'_relatedlists_rb VALUES(?,?,?,?,?,?)', $params);
		}
		//Deleting Contact-Account Relation.
		$con_q = 'UPDATE '.$table_prefix.'_contactdetails SET accountid = 0 WHERE accountid = ?';
		$this->db->pquery($con_q, array($id));
	
		//Backup Trouble Tickets-Account Relation
		$tkt_q = 'SELECT ticketid FROM '.$table_prefix.'_troubletickets WHERE parent_id = ?';
		$tkt_res = $this->db->pquery($tkt_q, array($id));
		if ($this->db->num_rows($tkt_res) > 0) {
			$tkt_ids_list = array();
			for($k=0;$k < $this->db->num_rows($tkt_res);$k++)
			{
				$tkt_ids_list[] = $this->db->query_result($tkt_res,$k,"ticketid");
			}
			$params = array($id, RB_RECORD_UPDATED, $table_prefix.'_troubletickets', 'parent_id', 'ticketid', implode(",", $tkt_ids_list));
			$this->db->pquery('INSERT INTO '.$table_prefix.'_relatedlists_rb VALUES(?,?,?,?,?,?)', $params);
		}
		
		//Deleting Trouble Tickets-Account Relation.
		$tt_q = 'UPDATE '.$table_prefix.'_troubletickets SET parent_id = 0 WHERE parent_id = ?';
		$this->db->pquery($tt_q, array($id));
	    
		parent::unlinkDependencies($module, $id);
	}
	
	// Function to unlink an entity with given Id from another entity 
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		if(empty($return_module) || empty($return_id)) return;	
		//crmv@15157	
		if($return_module == 'Campaigns') {
			$sql = 'DELETE FROM '.$table_prefix.'_campaignaccountrel WHERE accountid=? AND campaignid=?';
			$this->db->pquery($sql, array($id, $return_id));
		} 		
		//crmv@15157 end
		elseif($return_module == 'Products') {
			$sql = 'DELETE FROM '.$table_prefix.'_seproductsrel WHERE crmid=? AND productid=?';
			$this->db->pquery($sql, array($id, $return_id));
		} else {
			$sql = 'DELETE FROM '.$table_prefix.'_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
			$params = array($id, $return_module, $return_id, $id, $return_module, $return_id);
			$this->db->pquery($sql, $params);
		}
	}
	/**
	 * Move the related records of the specified list of id's to the given record.
	 * @param String This module name
	 * @param Array List of Entity Id's from which related records need to be transfered 
	 * @param Integer Id of the the Record to which the related records are to be moved
	 */
	function transferRelatedRecords($module, $transferEntityIds, $entityId) {
		global $adb,$log,$table_prefix;
		$log->debug("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");
		
		$rel_table_arr = Array("Contacts"=>$table_prefix."_contactdetails","Potentials"=>$table_prefix."_potential","Quotes"=>$table_prefix."_quotes",
					"SalesOrder"=>$table_prefix."_salesorder","Invoice"=>$table_prefix."_invoice","Activities"=>$table_prefix."_seactivityrel",
					"Documents"=>$table_prefix."_senotesrel","Attachments"=>$table_prefix."_seattachmentsrel","HelpDesk"=>$table_prefix."_troubletickets",
					"Products"=>$table_prefix."_seproductsrel");
		
		$tbl_field_arr = Array($table_prefix."_contactdetails"=>"contactid",$table_prefix."_potential"=>"potentialid",$table_prefix."_quotes"=>"quoteid",
					$table_prefix."_salesorder"=>"salesorderid",$table_prefix."_invoice"=>"invoiceid",$table_prefix."_seactivityrel"=>"activityid",
					$table_prefix."_senotesrel"=>"notesid",$table_prefix."_seattachmentsrel"=>"attachmentsid",$table_prefix."_troubletickets"=>"ticketid",
					$table_prefix."_seproductsrel"=>"productid");	
		
		$entity_tbl_field_arr = Array($table_prefix."_contactdetails"=>"accountid",$table_prefix."_potential"=>"related_to",$table_prefix."_quotes"=>"accountid",
					$table_prefix."_salesorder"=>"accountid",$table_prefix."_invoice"=>"accountid",$table_prefix."_seactivityrel"=>"crmid",
					$table_prefix."_senotesrel"=>"crmid",$table_prefix."_seattachmentsrel"=>"crmid",$table_prefix."_troubletickets"=>"parent_id",
					$table_prefix."_seproductsrel"=>"crmid");	
		
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
		$log->debug("Exiting transferRelatedRecords...");
	}
	
	/*
	 * Function to get the relation tables for related modules 
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */
	function setRelationTables($secmodule){
		global $table_prefix;
		$rel_tables =  array (
			"Contacts" => array($table_prefix."_contactdetails"=>array("accountid","contactid"),$table_prefix."_account"=>"accountid"),
			"Potentials" => array($table_prefix."_potential"=>array("related_to","potentialid"),$table_prefix."_account"=>"accountid"),
			"Quotes" => array($table_prefix."_quotes"=>array("accountid","quoteid"),$table_prefix."_account"=>"accountid"),
			"SalesOrder" => array($table_prefix."_salesorder"=>array("accountid","salesorderid"),$table_prefix."_account"=>"accountid"),
			"Invoice" => array($table_prefix."_invoice"=>array("accountid","invoiceid"),$table_prefix."_account"=>"accountid"),
			"Calendar" => array($table_prefix."_seactivityrel"=>array("crmid","activityid"),$table_prefix."_account"=>"accountid"),
			"HelpDesk" => array($table_prefix."_troubletickets"=>array("parent_id","ticketid"),$table_prefix."_account"=>"accountid"),
			"Products" => array($table_prefix."_seproductsrel"=>array("crmid","productid"),$table_prefix."_account"=>"accountid"),
			"Documents" => array($table_prefix."_senotesrel"=>array("crmid","notesid"),$table_prefix."_account"=>"accountid"),
			"Campaigns" => array($table_prefix."_campaignaccountrel"=>array("accountid","campaignid"),$table_prefix."_account"=>"accountid"),
		);
		return $rel_tables[$secmodule];
	}
	
	/*
	 * Function to get the secondary query part of a report 
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	function generateReportsSecQuery($module,$secmodule){
		global $table_prefix;
		$query = $this->getRelationQuery($module,$secmodule,$table_prefix."_account","accountid");
		$query .= " left join ".$table_prefix."_crmentity ".$table_prefix."_crmentityAccounts on ".$table_prefix."_crmentityAccounts.crmid=".$table_prefix."_account.accountid and ".$table_prefix."_crmentityAccounts.deleted=0
			left join ".$table_prefix."_accountbillads on ".$table_prefix."_account.accountid=".$table_prefix."_accountbillads.accountaddressid
			left join ".$table_prefix."_accountshipads on ".$table_prefix."_account.accountid=".$table_prefix."_accountshipads.accountaddressid
			left join ".$table_prefix."_accountscf on ".$table_prefix."_account.accountid = ".$table_prefix."_accountscf.accountid
			left join ".$table_prefix."_account ".$table_prefix."_accountAccounts on ".$table_prefix."_accountAccounts.accountid = ".$table_prefix."_account.parentid
			left join ".$table_prefix."_groups ".$table_prefix."_groupsAccounts on ".$table_prefix."_groupsAccounts.groupid = ".$table_prefix."_crmentityAccounts.smownerid
			left join ".$table_prefix."_users ".$table_prefix."_usersAccounts on ".$table_prefix."_usersAccounts.id = ".$table_prefix."_crmentityAccounts.smownerid ";
		return $query;
	}

	function getRelationQuery($module,$secmodule,$table_name,$column_name){
		global $table_prefix;
		$tab = getRelationTables($module,$secmodule);
		foreach($tab as $key=>$value){
			$tables[]=$key;
			$fields[] = $value;
		}
		//crmv@38798
		$tabname = $tables[0];
		$prifieldname = $fields[0][0];
		$secfieldname = $fields[0][1];
		$primodname = $fields[0][2];
		$secmodname = $fields[0][3];

		$tmpname = substr($tabname."tmp".$secmodule, 0, 29); //crmv@oracle fix object name > 30 characters
		$crmentitySec = substr("{$table_prefix}_crmentity{$secmodule}", 0, 29);

		$condition = "";
		if(!empty($tables[1]) && !empty($fields[1])){
			$condvalue = $tables[1].".".$fields[1];
		} else {
			$condvalue = $tabname.".".$prifieldname;
		}

		if (empty($secmodname)) {
			$condrelmodname = '';
			$condrelmodname_rev = '';
		} else {
			$condrelmodname = " AND {$tmpname}.{$secmodname} = '$secmodule'";
			$condrelmodname_rev = " AND {$tmpname}.{$secmodname} = '$module'";
			if (!empty($primodname)) {
				$condrelmodname .= " AND {$tmpname}.{$primodname} = '$module'";
				$condrelmodname_rev .= " AND {$tmpname}.{$primodname} = '$secmodule'";
			}
		}

		$condition = "{$tmpname}.{$prifieldname} = {$condvalue} {$condrelmodname}";
		if ($condrelmodname_rev) {
			$condition_rev = "{$tmpname}.{$secfieldname} = {$condvalue} {$condrelmodname_rev}";
		} else {
			$condition_rev = '';
		}

		// 1. join with relation table
		// 2. join with crmentity (to filter out deleted records)
		// 3. join with main table

		if ($tabname == $table_prefix.'_crmentityrel' ) { //crmv@18829
			// TODO: this OR make everythong slower, fix it!
			if ($condition_rev) {
				$condition = "(($condition) OR ({$condition_rev}))";
			} else {
				$condition = "($condition)";
			}

			$condition_secmod_table_pri = "{$crmentitySec}.crmid = {$tmpname}.{$secfieldname} {$condrelmodname}";

			if ($condrelmodname_rev) {
				$condition_secmod_table_rev = "{$crmentitySec}.crmid = {$tmpname}.{$prifieldname} {$condrelmodname_rev}";
				$condition_crmentity = "(({$condition_secmod_table_pri}) OR ({$condition_secmod_table_rev}))";
			} else {
				$condition_crmentity = "({$condition_secmod_table_pri})";
			}

		} else {
			$condition_crmentity = "{$crmentitySec}.crmid = {$tmpname}.{$secfieldname}";
		}

		$query = " LEFT JOIN {$tabname} {$tmpname} ON {$condition}";
		$query .= " LEFT JOIN {$table_prefix}_crmentity {$crmentitySec} ON {$condition_crmentity} AND {$crmentitySec}.deleted = 0";
		$query .= " LEFT JOIN {$table_name} ON {$crmentitySec}.crmid = {$table_name}.{$column_name}";

		//crmv@38798e
		return $query;
	}
	
	//crmv@22700
	function get_campaigns_newsletter($id, $cur_tab_id, $rel_tab_id, $actions=false)
	{
		global $log, $singlepane_view,$currentModule,$current_user,$table_prefix;
		$log->debug("Entering get_visitreport(".$id.") method ...");
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

		global $adb;
		$targetsModule = Vtiger_Module::getInstance('Targets');
		CRMEntity::get_related_list($id, $cur_tab_id, $targetsModule->id);
		$result = $adb->query($_SESSION['targets_listquery']);
		//TODO: trovare anche i Target inclusi in questi Target
		$campaigns = array();
		if ($result && $adb->num_rows($result)>0) {
			while($row=$adb->fetchByAssoc($result)) {
				CRMEntity::get_related_list($row['crmid'], $targetsModule->id, 26);
				$result1 = $adb->query($_SESSION['campaigns_listquery']);
				if ($result1 && $adb->num_rows($result1)>0) {
					while($row1=$adb->fetchByAssoc($result1)) {
						$campaigns[$row1['crmid']] = '';
					}
				}
			}
		}
		$campaigns = array_keys($campaigns);
		if (!empty($campaigns)) {
			$query = " SELECT
				CASE WHEN (".$table_prefix."_users.user_name is not null) THEN ".$table_prefix."_users.user_name ELSE ".$table_prefix."_groups.groupname END AS user_name, 
				".$table_prefix."_campaign.campaignid, 
				".$table_prefix."_campaign.campaignname, 
				".$table_prefix."_campaign.campaigntype, 
				  ".$table_prefix."_campaign.campaignstatus,
				  ".$table_prefix."_campaign.expectedrevenue,
				  ".$table_prefix."_campaign.closingdate,
				  ".$table_prefix."_crmentity.crmid,
				  ".$table_prefix."_crmentity.smownerid,
				  ".$table_prefix."_crmentity.modifiedtime
				FROM ".$table_prefix."_campaign
				  INNER JOIN ".$table_prefix."_campaignscf
				    ON ".$table_prefix."_campaignscf.campaignid = ".$table_prefix."_campaign.campaignid
				  INNER JOIN ".$table_prefix."_crmentity
				    ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_campaign.campaignid
				  LEFT JOIN ".$table_prefix."_groups
				    ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				  LEFT JOIN ".$table_prefix."_users
				    ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
				WHERE ".$table_prefix."_campaign.campaignid in (".implode(',',$campaigns).")
				    AND ".$table_prefix."_crmentity.deleted = 0";
			$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);
		}
		if($return_value == null) $return_value = Array();
		else {
			unset($return_value['header'][0]);
			if(is_array($return_value['entries'])){
				foreach ($return_value['entries'] as $id => $info) {
					unset($return_value['entries'][$id][0]);
				}
			}
		}
		$log->debug("Exiting get_faxes method ...");		
		return $return_value;
	}
	//crmv@22700e
}
?>