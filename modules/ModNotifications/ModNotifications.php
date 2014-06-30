<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
//crmv@start
require_once('data/CRMEntity.php');
require_once('data/Tracker.php');

class ModNotifications extends CRMEntity {
	var $db, $log; // Used in class functions of CRMEntity

	var $table_name;
	var $table_index= 'modnotificationsid';
	var $column_fields = Array();

	/** Indicator if this is a custom module or standard module */
	var $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array();

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array();

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array();

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array (
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Notification No'=> Array('modnotifications', 'notification_no'),
		'Assigned To' => Array('crmentity','smownerid')
	);
	var $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'Notification No'=> 'notification_no',
		'Assigned To' => 'assigned_user_id'
	);

	// Make the field link to detail view from list view (Fieldname)
	var $list_link_field = 'notification_no';

	// For Popup listview and UI type support
	var $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Notification No'=> Array('modnotifications', 'notification_no')
	);
	var $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'Notification No'=> 'notification_no'
	);

	// For Popup window record selection
	var $popup_fields = Array('notification_no');

	// Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
	var $sortby_fields = Array();

	// For Alphabetical search
	var $def_basicsearch_col = 'notification_no';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'notification_no';

	// Required Information for enabling Import feature
	var $required_fields = Array('notification_no'=>1);

	// Callback function list during Importing
	var $special_functions = Array('set_import_assigned_user');

	var $default_order_by = 'notification_no';
	var $default_sort_order='ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('createdtime', 'modifiedtime', 'notification_no');
	//crmv@10759
	var $search_base_field = 'notification_no';
	//crmv@10759 e
	
	var $skip_modules = array('Webmails','Emails','Fax','Sms','Events','ModComments','ChangeLog','ModNotifications');
	
	var $notification_types = array(
		'Changed followed record'=>array('action'=>'has changed'),
		'Changed record'=>array('action'=>'has changed'),
		'Created record'=>array('action'=>'has created and assigned to you'),
		'Ticket changed'=>array('action'=>'has changed'),
		'Ticket created'=>array('action'=>'has created and assigned to you'),
		'Ticket portal replied'=>array('action'=>'responded to'),
		'Ticket portal created'=>array('action'=>'has created'),
		'Product stock level'=>array('action'=>'MSG_STOCK_LEVEL'),
		'Calendar invitation'=>array('action'=>'has invited you to'),
		'Calendar invitation edit'=>array('action'=>'has changed your invitation to'),
		'Calendar invitation answer yes'=>array('action'=>'will attend'),
		'Calendar invitation answer no'=>array('action'=>'did not attend'),
		'Calendar invitation answer yes contact'=>array('action'=>'will attend'),
		'Calendar invitation answer no contact'=>array('action'=>'did not attend'),
		'Reminder calendar'=>array('action'=>'reminder activity'),
		'Relation'=>array('action'=>'has related'),
		'ListView changed'=>array('action'=>'Has been changed'),
		'Import Completed'=>array('action'=>'Import Completed'), //crmv@31126
	);
	
	var $notification_summary_values = array(
		'Every week'=>'-1 week',
		'Every 2 days'=>'-2 days',
		'Every day'=>'-1 day',
		'Every 4 hours'=>'-4 hours',
		'Every 2 hours'=>'-2 hours',
		'Hourly'=>'-1 hour',
	);
	
	function __construct() {
		global $log, $currentModule,$table_prefix;
		$this->table_name = $table_prefix.'_modnotifications';
		$this->customFieldTable = Array($table_prefix.'_modnotificationscf', 'modnotificationsid');
		$this->tab_name = Array($table_prefix.'_crmentity', $table_prefix.'_modnotifications', $table_prefix.'_modnotificationscf');
		$this->tab_name_index = Array(
				$table_prefix.'_crmentity' => 'crmid',
				$table_prefix.'_modnotifications'   => 'modnotificationsid',
			    $table_prefix.'_modnotificationscf' => 'modnotificationsid');
		$this->tab_name_index = Array(
			$table_prefix.'_crmentity' => 'crmid',
			$table_prefix.'_modnotifications'   => 'modnotificationsid',
	    	$table_prefix.'_modnotificationscf' => 'modnotificationsid');
			    
		$this->column_fields = getColumnFields($currentModule);
		$this->db = PearDatabase::getInstance();
		$this->log = $log;
	}

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
	

	function save_module($module) {
	}

	/**
	 * Return query to use based on given modulename, fieldname
	 * Useful to handle specific case handling for Popup
	 */
	function getQueryByModuleField($module, $fieldname, $srcrecord) {
		// $srcrecord could be empty
	}

	/**
	 * Create query to export the records.
	 */
	function create_export_query($where,$oCustomView,$viewId)	//crmv@31775
	{
		global $current_user,$table_prefix;
		$thismodule = $_REQUEST['module'];
		
		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery($thismodule, "detail_view");
		
		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT $fields_list, ".$table_prefix."_users.user_name AS user_name 
					FROM ".$table_prefix."_crmentity INNER JOIN $this->table_name ON ".$table_prefix."_crmentity.crmid=$this->table_name.$this->table_index";

		if(!empty($this->customFieldTable)) {
			$query .= " INNER JOIN ".$this->customFieldTable[0]." ON ".$this->customFieldTable[0].'.'.$this->customFieldTable[1] .
				      " = $this->table_name.$this->table_index"; 
		}

		$query .= " LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid";
		$query .= " LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_crmentity.smownerid = ".$table_prefix."_users.id and ".$table_prefix."_users.status='Active'";
		
		$linkedModulesQuery = $this->db->pquery("SELECT distinct fieldname, columnname, relmodule FROM ".$table_prefix."_field" .
				" INNER JOIN ".$table_prefix."_fieldmodulerel ON ".$table_prefix."_fieldmodulerel.fieldid = ".$table_prefix."_field.fieldid" .
				" WHERE uitype='10' AND ".$table_prefix."_fieldmodulerel.module=?", array($thismodule));
		$linkedFieldsCount = $this->db->num_rows($linkedModulesQuery);

		for($i=0; $i<$linkedFieldsCount; $i++) {
			$related_module = $this->db->query_result($linkedModulesQuery, $i, 'relmodule');
			$fieldname = $this->db->query_result($linkedModulesQuery, $i, 'fieldname');
			$columnname = $this->db->query_result($linkedModulesQuery, $i, 'columnname');
			
			$other = CRMEntity::getInstance($related_module);
			vtlib_setup_modulevars($related_module, $other);
			
			$query .= " LEFT JOIN $other->table_name ON $other->table_name.$other->table_index = $this->table_name.$columnname";
		}
		
		//crmv@31775
		$reportFilter = $oCustomView->getReportFilter($viewId);
		if ($reportFilter) {
			$tableNameTmp = $oCustomView->getReportFilterTableName($reportFilter,$current_user->id);
			$query .= " INNER JOIN $tableNameTmp ON $tableNameTmp.id = {$table_prefix}_crmentity.crmid";
		}
		//crmv@31775e

		$where_auto = " ".$table_prefix."_crmentity.deleted=0";

		if($where != '') $query .= " WHERE ($where) AND $where_auto";
		else $query .= " WHERE $where_auto";

		require('user_privileges/user_privileges_'.$current_user->id.'.php');
		require('user_privileges/sharing_privileges_'.$current_user->id.'.php');

		// Security Check for Field Access
		if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[7] == 3)
		{
			//Added security check to get the permitted records only
			$query = $query." ".getListViewSecurityParameter($thismodule);
		}
		return $query;
	}

	/**
	 * Initialize this instance for importing.
	 */
	function initImport($module) {
		$this->db = PearDatabase::getInstance();
		$this->initImportableFields($module);
	}

	/**
	 * Create list query to be shown at the last step of the import.
	 * Called From: modules/Import/UserLastImport.php
	 */
	function create_import_query($module) {
		global $current_user,$table_prefix;
		$query = "SELECT ".$table_prefix."_crmentity.crmid, case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name, $this->table_name.* FROM $this->table_name
			INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = $this->table_name.$this->table_index
			LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_crmentity.crmid
			LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			WHERE ".$table_prefix."_users_last_import.assigned_user_id='$current_user->id'
			AND ".$table_prefix."_users_last_import.bean_type='$module'
			AND ".$table_prefix."_users_last_import.deleted=0";
		return $query;
	}

	/**
	 * Delete the last imported records.
	 */
	function undo_import($module, $user_id) {
		global $adb;
		$count = 0;
		$query1 = "select bean_id from ".$table_prefix."_users_last_import where assigned_user_id=? AND bean_type='$module' AND deleted=0";
		$result1 = $adb->pquery($query1, array($user_id)) or die("Error getting last import for undo: ".mysql_error()); 
		while ( $row1 = $adb->fetchByAssoc($result1))
		{
			$query2 = "update ".$table_prefix."_crmentity set deleted=1 where crmid=?";
			$result2 = $adb->pquery($query2, array($row1['bean_id'])) or die("Error undoing last import: ".mysql_error()); 
			$count++;			
		}
		return $count;
	}
	
	/**
	 * Transform the value while exporting
	 */
	function transform_export_value($key, $value) {
		return parent::transform_export_value($key, $value);
	}

	/**
	 * Function which will set the assigned user id for import record.
	 */
	function set_import_assigned_user()
	{
		global $current_user, $adb,$table_prefix;
		$record_user = $this->column_fields["assigned_user_id"];
		
		if($record_user != $current_user->id){
			$sqlresult = $adb->pquery("select id from ".$table_prefix."_users where id = ? union select groupid as id from ".$table_prefix."_groups where groupid = ?", array($record_user, $record_user));
			if($this->db->num_rows($sqlresult)!= 1) {
				$this->column_fields["assigned_user_id"] = $current_user->id;
			} else {			
				$row = $adb->fetchByAssoc($sqlresult, -1, false);
				if (isset($row['id']) && $row['id'] != -1) {
					$this->column_fields["assigned_user_id"] = $row['id'];
				} else {
					$this->column_fields["assigned_user_id"] = $current_user->id;
				}
			}
		}
	}
	
	/** 
	 * Function which will give the basic query to find duplicates
	 */
	function getDuplicatesQuery($module,$table_cols,$field_values,$ui_type_arr,$select_cols='') {
		global $table_prefix;
		$select_clause = "SELECT ". $this->table_name .".".$this->table_index ." AS recordid, ".$table_prefix."_users_last_import.deleted,".$table_cols;

		// Select Custom Field Table Columns if present
		if(isset($this->customFieldTable)) $query .= ", " . $this->customFieldTable[0] . ".* ";

		$from_clause = " FROM $this->table_name";

		$from_clause .= "	INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = $this->table_name.$this->table_index";

		// Consider custom table join as well.
		if(isset($this->customFieldTable)) {
			$from_clause .= " INNER JOIN ".$this->customFieldTable[0]." ON ".$this->customFieldTable[0].'.'.$this->customFieldTable[1] .
				      " = $this->table_name.$this->table_index"; 
		}
		$from_clause .= " LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
						LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid";
		
		$where_clause = "	WHERE ".$table_prefix."_crmentity.deleted = 0";
		$where_clause .= $this->getListViewSecurityParameter($module);
					
		if (isset($select_cols) && trim($select_cols) != '') {
			$sub_query = "SELECT $select_cols FROM  $this->table_name AS t " .
				" INNER JOIN ".$table_prefix."_crmentity AS crm ON crm.crmid = t.".$this->table_index;
			// Consider custom table join as well.
			if(isset($this->customFieldTable)) {
				$sub_query .= " INNER JOIN ".$this->customFieldTable[0]." tcf ON tcf.".$this->customFieldTable[1]." = t.$this->table_index";
			}
			$sub_query .= " WHERE crm.deleted=0 GROUP BY $select_cols HAVING COUNT(*)>1";	
		} else {
			$sub_query = "SELECT $table_cols $from_clause $where_clause GROUP BY $table_cols HAVING COUNT(*)>1";
		}
		
		$query = $select_clause . $from_clause .
					" LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=" . $this->table_name .".".$this->table_index .
					" INNER JOIN (" . $sub_query . ") temp ON ".get_on_clause($field_values,$ui_type_arr,$module) .
					$where_clause .
					" ORDER BY $table_cols,". $this->table_name .".".$this->table_index ." ASC";
					
		return $query;		
	}

	/** 
	 * Handle saving related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	// function save_related_module($module, $crmid, $with_module, $with_crmid) { }
	
	/**
	 * Handle deleting related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//function delete_related_module($module, $crmid, $with_module, $with_crmid) { }

	/**
	 * Handle getting related list information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//function get_related_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }

	/**
	 * Handle getting dependents list information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//function get_dependents_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }
	
	function listQueryNonAdminChange($query,$module,$scope='') {
		global $current_user,$table_prefix;
		$query = parent::listQueryNonAdminChange($query,$module,$scope);
		$query .= ' and '.$table_prefix.'_crmentity.smownerid = '.$current_user->id;
		return $query;
	}
	static function getWidget($name) {
		if ($name == 'DetailViewBlockCommentWidget' &&
				isPermitted('ModNotifications', 'DetailView') == 'yes') {
			require_once dirname(__FILE__) . '/widgets/DetailViewBlockComment.php';
			return (new ModNotifications_DetailViewBlockCommentWidget());
		}
		return false;
	}
	function addWidgetToAll() {
		global $adb,$table_prefix;
		$result = $adb->pquery('SELECT name FROM '.$table_prefix.'_tab WHERE isentitytype = 1 AND name NOT IN ('.generateQuestionMarks($this->skip_modules).')',$this->skip_modules);
		if ($result && $adb->num_rows($result) > 0) {
			$modules = array();
			while($row=$adb->fetchByAssoc($result)) {
				$modules[] = $row['name'];
			}
			$this->addWidgetTo($modules);
		}
	}
	function addWidgetTo($moduleNames) {
		global $adb,$table_prefix;
		unset($_SESSION['ModNotificationsModules']);
		$instance = Vtiger_Module::getInstance('ModNotifications');
		$result = $adb->pquery('SELECT fieldid FROM '.$table_prefix.'_field WHERE tabid = ? AND fieldname = ?',array($instance->id,'related_to'));
		if ($result && $adb->num_rows($result) > 0) {
			$fieldid = $adb->query_result($result,0,'fieldid');
			if(!is_array($moduleNames)) $moduleNames = array($moduleNames);
			foreach($moduleNames as $module){
				$result = $adb->pquery('SELECT relmodule FROM '.$table_prefix.'_fieldmodulerel WHERE fieldid = ? AND module = ? AND relmodule = ?',array($fieldid,'ModNotifications',$module));
				if ($result && $adb->num_rows($result) > 0) {
					//continue
				} else {
					$fieldInstance = Vtiger_Field::getInstance('related_to', $instance);
					$fieldInstance->setRelatedModules(array($module));
				}
			}
		}
	}
	function getNotificationTypes() {
		return $this->notification_types;
	}
	function setNotificationTypes($moduleInstance) {
		$field = Vtiger_Field::getInstance('mod_not_type',$moduleInstance);
		$field->setPicklistValues(array_keys($this->getNotificationTypes()));
	}
	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	function vtlib_handler($moduleName, $eventType) {
		require_once('include/utils/utils.php');			
		global $adb,$table_prefix;
		
 		if($eventType == 'module.postinstall') {
		
			$ModNotificationsCommonInstance = Vtiger_Module::getInstance($moduleName);
 			
 			$adb->pquery('UPDATE '.$table_prefix.'_tab SET customized=0 WHERE name=?', array($moduleName));
			$ModNotificationsCommonInstance->hide(array('hide_module_manager'=>1,'hide_profile'=>1,'hide_report'=>1));
			
			SDK::setUtil('modules/ModNotifications/ModNotificationsCommon.php');
			
			self::addWidgetToAll();
			self::saveDefaultModuleSettings();
			self::setModuleSeqNumber('configure', 'ModNotifications', 'NOT-', 1);
			self::setNotificationTypes($ModNotificationsCommonInstance);
			
			$em = new VTEventsManager($adb);
			$em->registerHandler('vtiger.entity.aftersave', 'modules/ModNotifications/ModNotificationsHandler.php', 'ModNotificationsHandler');
			$em->registerHandler('vtiger.entity.beforesave', 'modules/ModNotifications/ModNotificationsHandler.php', 'ModNotificationsHandler');
			
			$columns = array_keys($adb->datadict->MetaColumns($table_prefix.'_activity'));
			if (in_array(strtoupper('sendnotification'),$columns)) {
				$result = $adb->query('SELECT smownerid, crmid FROM '.$table_prefix.'_activity
										INNER JOIN '.$table_prefix.'_crmentity ON activityid = crmid
										WHERE deleted = 0 AND sendnotification = 1');
				if ($result && $adb->num_rows($result) > 0) {
					while($row=$adb->fetchByAssoc($result)) {
						$this->toggleFollowFlag($row['smownerid'],$row['crmid']);
					}
				}
				$sqlarray = $adb->datadict->DropColumnSQL($table_prefix.'_activity','sendnotification');
				$adb->datadict->ExecuteSQLArray($sqlarray);
				$adb->query("delete from ".$table_prefix."_field where fieldname = 'sendnotification' and tabid in (9,16)");
			}
			
			$columns = array_keys($adb->datadict->MetaColumns($table_prefix.'_account'));
			if (in_array(strtoupper('notify_owner'),$columns)) {
				$result = $adb->query('SELECT smownerid, crmid FROM '.$table_prefix.'_account
										INNER JOIN '.$table_prefix.'_crmentity ON accountid = crmid
										WHERE deleted = 0 AND notify_owner = 1');
				if ($result && $adb->num_rows($result) > 0) {
					while($row=$adb->fetchByAssoc($result)) {
						$this->toggleFollowFlag($row['smownerid'],$row['crmid']);
					}
				}
				$sqlarray = $adb->datadict->DropColumnSQL($table_prefix.'_account','notify_owner');
				$adb->datadict->ExecuteSQLArray($sqlarray);
				$adb->query("delete from ".$table_prefix."_field where fieldname = 'notify_owner' and tabid = 6");
			}

			$columns = array_keys($adb->datadict->MetaColumns($table_prefix.'_contactdetails'));
			if (in_array(strtoupper('notify_owner'),$columns)) {
				$result = $adb->query('SELECT smownerid, crmid FROM '.$table_prefix.'_contactdetails
										INNER JOIN '.$table_prefix.'_crmentity ON contactid = crmid
										WHERE deleted = 0 AND notify_owner = 1');
				if ($result && $adb->num_rows($result) > 0) {
					while($row=$adb->fetchByAssoc($result)) {
						$this->toggleFollowFlag($row['smownerid'],$row['crmid']);
					}
				}
				$sqlarray = $adb->datadict->DropColumnSQL($table_prefix.'_contactdetails','notify_owner');
				$adb->datadict->ExecuteSQLArray($sqlarray);
				$adb->query("delete from ".$table_prefix."_field where fieldname = 'notify_owner' and tabid = 4");
			}
			
		} else if($event_type == 'module.disabled') {
			// TODO Handle actions when this module is disabled.
		} else if($event_type == 'module.enabled') {
			// TODO Handle actions when this module is enabled.
		} else if($eventType == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
		} else if($eventType == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} else if($eventType == 'module.postupdate') {
			$tmp_dir = 'packages/vte/mandatory/tmp1';
			mkdir($tmp_dir);
			$unzip = new Vtiger_Unzip("packages/vte/mandatory/$moduleName.zip");
			$unzip->unzipAllEx($tmp_dir);
			if($unzip) $unzip->close();
			copy("$tmp_dir/cron/$moduleName.service.php","cron/modules/$moduleName/$moduleName.service.php");
			if ($handle = opendir($tmp_dir)) {
				folderDetete($tmp_dir);
			}
		}
 	}
	function getFollowedRecords($user,$type='') {
		global $adb;
		$records = array();
		if ($type == 'customview') {
			$result = $adb->pquery('select cvid as record from vte_modnot_follow_cv where userid = ?',array($user));
		} else {
			$result = $adb->pquery('select record from vte_modnotifications_follow where userid = ?',array($user));
		}
		if ($result && $adb->num_rows($result) > 0) {
			while($row=$adb->fetchByAssoc($result)) {
				$records[] = $row['record'];
			}
		}
		return $records;
	}
	function getFollowingUsers($record) {
		global $adb;
		$users = array();
		$result = $adb->pquery('select * from vte_modnotifications_follow where record = ?',array($record));
		if ($result && $adb->num_rows($result) > 0) {
			while($row=$adb->fetchByAssoc($result)) {
				$users[] = $row['userid'];
			}
		}
		return $users;
	}
	function toggleFollowFlag($user,$record,$type='') {
		global $adb;
		if ($type == 'customview') {
			$result = $adb->pquery('select * from vte_modnot_follow_cv where userid = ? and cvid = ?',array($user,$record));
		} else {
			$result = $adb->pquery('select * from vte_modnotifications_follow where userid = ? and record = ?',array($user,$record));
		}
		if ($result && $adb->num_rows($result) > 0) {
			$this->unsetFollowFlag($user,$record,$type);
		} else {
			$this->setFollowFlag($user,$record,$type);
		}
	}
	function setFollowFlag($user,$record,$type='') {
		global $adb,$table_prefix;
		if ($type == 'customview') {
			$adb->pquery('insert into vte_modnot_follow_cv (cvid,userid,count) values(?,?,-1)',array($record,$user));
			
			$result = $adb->pquery('SELECT entitytype FROM '.$table_prefix.'_customview WHERE cvid = ?',array($record));
			if ($result && $adb->num_rows($result) > 0) {
				$module = $adb->query_result($result,0,'entitytype');
				$list_query_count = $_SESSION[$module.'_listquery'];
				$list_query_result = $adb->query($list_query_count);
				checkListNotificationCount($list_query_count,$user,$record,$adb->num_rows($list_query_result));
			}
		} else {
			$adb->pquery('insert into vte_modnotifications_follow (userid,record) values (?,?)',array($user,$record));
		}
	}
	function unsetFollowFlag($user,$record,$type='') {
		global $adb;
		if ($type == 'customview') {
			$adb->pquery('delete from vte_modnot_follow_cv where userid = ? and cvid = ?',array($user,$record));
		} else {
			$adb->pquery('delete from vte_modnotifications_follow where userid = ? and record = ?',array($user,$record));
		}
	}
	function isEnabled($module,$record='',$user='') {
		if ($module == 'Activity') $module = 'Calendar';
		$modules = array_keys($this->getEnableModuleSettings());
		if (in_array($module,$modules)) {
			if ($record != '' && $user != '') {
				return $this->isPermitted($module,$record,$user);
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
	function isPermitted($module,$record,$user) {
		if ($module == 'Activity') $module = 'Calendar';
		global $current_user;
		$tmp_current_user = $current_user;
		$current_user = CRMEntity::getInstance('Users');
		$current_user->retrieve_entity_info($user,'Users');
		$return = false;
		if (isPermitted($module,'DetailView',$record) != 'no') {
			$return = true;
		}
		$current_user = $tmp_current_user;
		return $return;
	}
	function getModuleSettings($record) {
		global $adb;
		$info = $this->getEnableModuleSettings();
		if ($record == '') {
			$default_module_settings = $this->getDefaultModuleSettings();
			if (!empty($default_module_settings)) {
				foreach($default_module_settings as $module => $row) {
					$info[$module] = array('create'=>$row['create'],'edit'=>$row['edit']);
				}
			}
		} else {
			$result = $adb->pquery('select * from vte_modnotifications_modules where userid = ? and (notify_create <> 0 or notify_edit <> 0) order by module',array($record));
			if ($result && $adb->num_rows($result) > 0) {
				while($row=$adb->fetchByAssoc($result)) {
					$info[$row['module']] = array('create'=>$row['notify_create'],'edit'=>$row['notify_edit']);
				}
			}
		}
		return $info;
	}
	function getDefaultModuleSettings() {
		return array(
			'Potentials'=>array('create'=>1,'edit'=>1),
			'Calendar'=>array('create'=>1,'edit'=>1),
			'HelpDesk'=>array('create'=>1,'edit'=>1),
			'ProjectMilestone'=>array('create'=>1,'edit'=>1),
			'ProjectTask'=>array('create'=>1,'edit'=>1),
			'ProjectPlan'=>array('create'=>1,'edit'=>1),
		);
	}
	function getEnableModuleSettings() {
		global $adb,$table_prefix;
		if(empty($_SESSION['ModNotificationsModules'])) {
			$info = array();
			$result = $adb->pquery('SELECT fieldid FROM '.$table_prefix.'_field WHERE tabid = ? AND fieldname = ?',array(getTabid('ModNotifications'),'related_to'));
			if ($result && $adb->num_rows($result) > 0) {
				$result = $adb->pquery('SELECT relmodule FROM '.$table_prefix.'_fieldmodulerel WHERE fieldid = ? AND module = ?',array($adb->query_result($result,0,'fieldid'),'ModNotifications'));
				if ($result && $adb->num_rows($result) > 0) {
					while($row=$adb->fetchByAssoc($result)) {
						$info[$row['relmodule']] = array('create'=>0,'edit'=>0);
					}
				}
			}
			$_SESSION['ModNotificationsModules'] = $info;
		}
		return $_SESSION['ModNotificationsModules'];
	}
	function saveModuleSettings($record,$request) {
		global $adb;
		
		$adb->pquery('delete from vte_modnotifications_modules where userid = ?',array($record));
		
		$info = array();
		foreach($request as $key => $value) {
			if (strpos($key,'_notify_create')) {
				$tmp = explode('_notify_create',$key);
				$module = $tmp[0];
				if ($value == 'on') {
					$value = 1;
				} elseif ($value == 'off') {
					$value = 1;
				}
				$info[$module]['create'] = $value;
			} elseif (strpos($key,'_notify_edit')) {
				$tmp = explode('_notify_edit',$key);
				$module = $tmp[0];
				if ($value == 'on') {
					$value = 1;
				} elseif ($value == 'off') {
					$value = 1;
				}
				$info[$module]['edit'] = $value;
			}
		}
		if (!empty($info)) {
			foreach($info as $module => $flags) {
				if (!isset($info[$module]['create'])) {
					$info[$module]['create'] = 0;
				}
				if (!isset($info[$module]['edit'])) {
					$info[$module]['edit'] = 0;
				}
				$adb->pquery('insert into vte_modnotifications_modules (userid,module,notify_create,notify_edit) values (?,?,?,?)',array($record,$module,$info[$module]['create'],$info[$module]['edit']));
			}
		}
	}
	function saveDefaultModuleSettings() {
		global $adb,$table_prefix;
		$result = $adb->query('SELECT id FROM '.$table_prefix.'_users');
		if ($result && $adb->num_rows($result) > 0) {
			while($row=$adb->fetchByAssoc($result)) {
				$adb->pquery('delete from vte_modnotifications_modules where userid = ?',array($row['id']));
				$default_module_settings = $this->getDefaultModuleSettings();
				if (!empty($default_module_settings)) {
					foreach($default_module_settings as $module => $info) {
						$adb->pquery('insert into vte_modnotifications_modules (userid,module,notify_create,notify_edit) values (?,?,?,?)',array($row['id'],$module,$info['create'],$info['edit']));
					}
				}
			}
		}
	}
	function getInterestedToModuleUsers($mode,$module) {
		if ($module == 'Activity') $module = 'Calendar';
		global $adb;
		$users = array();
		$result = $adb->pquery("SELECT userid FROM vte_modnotifications_modules WHERE module = ? AND notify_{$mode} = ?",array($module,1));
		if ($result && $adb->num_rows($result) > 0) {
			while($row=$adb->fetchByAssoc($result)) {
				$users[] = $row['userid'];
			}
		}
		return $users;
	}
	function setRecordSeen($record) {
		if (strpos($record,',') !== false) {
			$ids = array_filter(explode(',',$record));
		} else {
			$ids = array($record);
		}
		foreach ($ids as $id) {
			$this->retrieve_entity_info($id, 'ModNotifications');
			$this->mode = 'edit';
			$this->id = $id;
			$this->column_fields['seen'] = 1;
			$this->save('ModNotifications',true);
		}
	}
	function translateNotificationType($type,$mode) {
		$label = $type;
		$notification_types = $this->getNotificationTypes();
		if (isset($notification_types[$type][$mode])) {
			$label = $notification_types[$type][$mode];
		}
		return getTranslatedString($label,'ModNotifications');
	}
	function saveFastNotification($column_fields,$manage_group=true) {
		global $table_prefix;
		$notified_users = array();
		if (!(isset($_SESSION["app_unique_key"]) && $_SESSION["app_unique_key"] != '')) {	//crmv@32334
			return $notified_users;
		}
		if (!$manage_group) {	//qui sono sicuro che mi arriva un utente e non un gruppo

			$obj = CRMEntity::getInstance('ModNotifications');
			foreach ($column_fields as $key => $value) {
				$obj->column_fields[$key] = $value;
			}
			$obj->save('ModNotifications',true);
			$notified_users[] = $column_fields['assigned_user_id'];
			
		} else {	//controllo se l'assegnatario è un gruppo e in quel caso notifico tutti i partecipanti
			global $adb, $current_user;
			$result = $adb->pquery('SELECT * FROM '.$table_prefix.'_users WHERE id = ? AND deleted = 0 AND status = ?', array($column_fields['assigned_user_id'],'Active'));
			if($result && $adb->num_rows($result) > 0) {
				
				$obj = CRMEntity::getInstance('ModNotifications');
				foreach ($column_fields as $key => $value) {
					$obj->column_fields[$key] = $value;
				}
				$obj->save('ModNotifications',true);
				$notified_users[] = $column_fields['assigned_user_id'];
				
			} else {
				$result = $adb->pquery('SELECT groupid FROM '.$table_prefix.'_groups WHERE groupid = ?', array($column_fields['assigned_user_id']));
				if($result && $adb->num_rows($result) > 0) {
					$groupid = $adb->query_result($result,0,'groupid');
					require_once('include/utils/GetGroupUsers.php');
					$focus = new GetGroupUsers();
	        		$focus->getAllUsersInGroup($groupid);
	        		$group_users = $focus->group_users;
	        		if (!empty($group_users)) {
	        			$group_users_str = implode(',',$group_users);
	        			$query  = 'select id from '.$table_prefix.'_users where id in ('.$group_users_str.') and deleted = 0 and status = ?';
	        			$params = array('Active');
	        			if ($current_user) {
	        				$query .= ' and id <> ?';
	        				$params[] = $current_user->id;
	        			}
	        			$result = $adb->pquery($query,$params);
	        			if($result && $adb->num_rows($result) > 0) {
	        				while($row=$adb->fetchByAssoc($result)) {
	        					
	        					$obj = CRMEntity::getInstance('ModNotifications');
	        					foreach ($column_fields as $key => $value) {
	        						$obj->column_fields[$key] = $value;
	        					}
	        					$obj->column_fields['assigned_user_id'] = $row['id'];
	        					$obj->save('ModNotifications',true);
	        					$notified_users[] = $row['id'];
	        					
	        				}
	        			}
	        		}
				}
			}
		}
		
		return $notified_users;
	}
	function saveRelatedModuleNotification($crmid, $module, $relcrmid, $with_module) {
		$skip_modules = array('ChangeLog','ModNotifications','Targets');
		if (in_array($module,$skip_modules) || in_array($with_module,$skip_modules)) {
			return;
		}
		require_once('modules/ChangeLog/ChangeLogHandler.php');
		global $adb, $current_user,$table_prefix;
		$already_notified_users = array();
		
		$modified_date = date('Y-m-d H:i:s');
		$recordName = getEntityName($module,$crmid);
		$entityType = getSingleModuleName($module,$crmid); //crmv@32334
		$rel_recordName = getEntityName($with_module,$relcrmid);
		$rel_entityType = getSingleModuleName($with_module,$relcrmid); //crmv@32334

		//notifico la relazione come modifica del record $crmid - i
		$following_users = $this->getFollowingUsers($crmid);
		if (!empty($following_users)) {
			foreach($following_users as $following_user) {
				if ($current_user->id != $following_user) {
					if ($this->isEnabled($module,$crmid,$following_user) && $this->isEnabled($with_module,$relcrmid,$following_user)) {
						$notified_users = $this->saveFastNotification(
							array(
								'assigned_user_id' => $following_user,
								'related_to' => $crmid,
								'mod_not_type' => 'Relation',
								'description' => $relcrmid,
							)
						);
						if(!empty($notified_users)) {
							foreach($notified_users as $notified_user) {
								$already_notified_users[] = $notified_user;
							}
						}
					}
				}
			}
		}
		$focusCrmid = CRMEntity::getInstance($module);
		$focusCrmid->retrieve_entity_info($crmid,$module);
		$interested_users = $this->getInterestedToModuleUsers('edit',$module);
		$users = array();
		$result = $adb->pquery('SELECT id FROM '.$table_prefix.'_users WHERE id = ? AND deleted = 0 AND status = ?', array($focusCrmid->column_fields['assigned_user_id'],'Active'));
		if ($result && $adb->num_rows($result) > 0) {
			$users[] = $focusCrmid->column_fields['assigned_user_id'];
		} else {
			$result = $adb->pquery('SELECT groupid FROM '.$table_prefix.'_groups WHERE groupid = ?', array($focusCrmid->column_fields['assigned_user_id']));
			if($result && $adb->num_rows($result) > 0) {
				$groupid = $adb->query_result($result,0,'groupid');
				require_once('include/utils/GetGroupUsers.php');
				$focus = new GetGroupUsers();
        		$focus->getAllUsersInGroup($groupid);
        		$group_users = $focus->group_users;
        		if (!empty($group_users)) {
        			$group_users_str = implode(',',$group_users);
        			$result = $adb->pquery('select id from '.$table_prefix.'_users where id in ('.$group_users_str.') and deleted = 0 and status = ?',array('Active'));
        			if($result && $adb->num_rows($result) > 0) {
        				while($row=$adb->fetchByAssoc($result)) {
        					$users[] = $row['id'];
        				}
        			}
        		}
			}
		}
		foreach($interested_users as $interested_user) {
			if (in_array($interested_user,$already_notified_users)) {
				continue;
			}
			if (in_array($interested_user,$users) && $interested_user != $current_user->id) {
				$notified_users = $this->saveFastNotification(
					array(
						'assigned_user_id' => $interested_user,
						'related_to' => $crmid,
						'mod_not_type' => 'Relation',
						'description' => $relcrmid,
					),false
				);
				if(!empty($notified_users)) {
					foreach($notified_users as $notified_user) {
						$already_notified_users[] = $notified_user;
					}
				}
			}
		}
		$obj = CRMEntity::getInstance('ChangeLog');
		$obj->column_fields['modified_date'] = $modified_date;
		$obj->column_fields['audit_no'] = ChangeLogHandler::get_revision_id($crmid);
		$obj->column_fields['assigned_user_id'] = $current_user->id;
		$obj->column_fields['parent_id'] = $crmid;
		$obj->column_fields['user_name'] = $current_user->column_fields['user_name'];
		$obj->column_fields['description'] = Zend_Json::encode(array('ModNotification_Relation',"<a href='index.php?module=$with_module&action=DetailView&record=$relcrmid'>$rel_recordName[$relcrmid]</a> ($rel_entityType) LBL_LINKED_TO <a href='index.php?module=$module&action=DetailView&record=$crmid'>$recordName[$crmid]</a> ($entityType)"));
		$obj->save('ChangeLog');
		//notifico la relazione come modifica del record $crmid - e

		//notifico la relazione come modifica del record $relcrmid - i
		$following_users = $this->getFollowingUsers($relcrmid);
		if (!empty($following_users)) {
			foreach($following_users as $following_user) {
				if ($current_user->id != $following_user && !in_array($following_user,$already_notified_users)) {
					if ($this->isEnabled($module,$crmid,$following_user) && $this->isEnabled($with_module,$relcrmid,$following_user)) {
						$notified_users = $this->saveFastNotification(
							array(
								'assigned_user_id' => $following_user,
								'related_to' => $relcrmid,
								'mod_not_type' => 'Relation',
								'description' => $crmid,
							)
						);
						if(!empty($notified_users)) {
							foreach($notified_users as $notified_user) {
								$already_notified_users[] = $notified_user;
							}
						}
					}
				}
			}
		}
		$focusRelcrmid = CRMEntity::getInstance($with_module);
		$focusRelcrmid->retrieve_entity_info($relcrmid,$with_module);
		$interested_users = $this->getInterestedToModuleUsers('edit',$with_module);
		$users = array();
		$result = $adb->pquery('SELECT id FROM '.$table_prefix.'_users WHERE id = ? AND deleted = 0 AND status = ?', array($focusRelcrmid->column_fields['assigned_user_id'],'Active'));
		if ($result && $adb->num_rows($result) > 0) {
			$users[] = $focusRelcrmid->column_fields['assigned_user_id'];
		} else {
			$result = $adb->pquery('SELECT groupid FROM '.$table_prefix.'_groups WHERE groupid = ?', array($focusRelcrmid->column_fields['assigned_user_id']));
			if($result && $adb->num_rows($result) > 0) {
				$groupid = $adb->query_result($result,0,'groupid');
				require_once('include/utils/GetGroupUsers.php');
				$focus = new GetGroupUsers();
        		$focus->getAllUsersInGroup($groupid);
        		$group_users = $focus->group_users;
        		if (!empty($group_users)) {
        			$group_users_str = implode(',',$group_users);
        			$result = $adb->pquery('select id from '.$table_prefix.'_users where id in ('.$group_users_str.') and deleted = 0 and status = ?',array('Active'));
        			if($result && $adb->num_rows($result) > 0) {
        				while($row=$adb->fetchByAssoc($result)) {
        					$users[] = $row['id'];
        				}
        			}
        		}
			}
		}
		foreach($interested_users as $interested_user) {
			if (in_array($interested_user,$already_notified_users)) {
				continue;
			}
			if (in_array($interested_user,$users) && $interested_user != $current_user->id) {
				$notified_users = $this->saveFastNotification(
					array(
						'assigned_user_id' => $interested_user,
						'related_to' => $relcrmid,
						'mod_not_type' => 'Relation',
						'description' => $crmid,
					),false
				);
				if(!empty($notified_users)) {
					foreach($notified_users as $notified_user) {
						$already_notified_users[] = $notified_user;
					}
				}
			}
		}
		$obj = CRMEntity::getInstance('ChangeLog');
		$obj->column_fields['modified_date'] = $modified_date;
		$obj->column_fields['audit_no'] = ChangeLogHandler::get_revision_id($relcrmid);
		$obj->column_fields['assigned_user_id'] = $current_user->id;
		$obj->column_fields['parent_id'] = $relcrmid;
		$obj->column_fields['user_name'] = $current_user->column_fields['user_name'];
		$obj->column_fields['description'] = Zend_Json::encode(array('ModNotification_Relation',"<a href='index.php?module=$module&action=DetailView&record=$crmid'>$recordName[$crmid]</a> ($entityType) LBL_LINKED_TO <a href='index.php?module=$with_module&action=DetailView&record=$relcrmid'>$rel_recordName[$relcrmid]</a> ($rel_entityType)"));
		$obj->save('ChangeLog');
		//notifico la relazione come modifica del record $relcrmid - e
	}
	function getBodyNotification($id,$column_fields,$signature,$only_content=false) {
		global $site_URL,$adb,$current_user,$current_language,$default_language,$table_prefix;
		$default_language_tmp = $default_language;
		$current_language_tmp = $current_language;
		
		$body = '';
		$user = CRMEntity::getInstance('Users');
		$user->retrieve_entity_info($column_fields['assigned_user_id'],'Users');
		$current_language = $default_language = $user->column_fields['default_language'];
		$related_to = $column_fields['related_to'];
		$type = $column_fields['mod_not_type'];
		$related_module = getSalesEntityType($related_to);
		
		if (!$only_content) {
			$body = getTranslatedString('MSG_DEAR','ModNotifications').' '.getUserFullName($user->id).',<br />';
		}
		
		$recordName = getEntityName($related_module,$related_to);
		$entityType = getSingleModuleName($related_module,$related_to); //crmv@32334
		if ($only_content) {
			require_once('modules/ModNotifications/models/Comments.php');
			$model = new ModNotifications_CommentsModel($column_fields);
			$body .= $model->timestampAgo().' ';
		}
		$body .= getUserFullName($current_user->id).' '.strtolower($this->translateNotificationType($type,'action'));
		if ($type == 'Relation') {
			$relation_parent_id = $column_fields['description'];
			$relation_parent_module = getSalesEntityType($relation_parent_id);
			$relation_recordName = getEntityName($relation_parent_module,$relation_parent_id);
			$relation_entityType = getSingleModuleName($relation_parent_module,$relation_parent_id);
			$body .= " <a href='{$site_URL}/index.php?module=$relation_parent_module&action=DetailView&record=$relation_parent_id' title='$relation_entityType'>".$relation_recordName[$relation_parent_id]."</a> ($relation_entityType) ";
			$body .= getTranslatedString('LBL_TO','ModComments');
		}
		$body .= " <a href='{$site_URL}/index.php?module=$related_module&action=DetailView&record=$related_to' title='$entityType'>".$recordName[$related_to]."</a> ($entityType).";
		
		$body .= '<br /><br />'.getTranslatedString('MSG_DETAILS_OF','ModNotifications').' <b>'.$recordName[$related_to].'</b> '.getTranslatedString('MSG_DETAILS_ARE','ModNotifications').'<br />';
		if(in_array($column_fields['mod_not_type'],array('Changed followed record', 'Changed record'))){

			$q = "SELECT * FROM {$table_prefix}_changelog ch INNER JOIN {$table_prefix}_crmentity c ON ch.changelogid = c.crmid
						WHERE c.deleted = 0 AND parent_id = ? ORDER BY changelogid DESC ";
			$ress = $adb->pquery($q,array($related_to));
			
			$changelogid = $adb->query_result_no_html($ress,0,"changelogid");
			$description = $adb->query_result_no_html($ress,0,"description");
			$description_elements = Zend_Json::decode($description);
			$ChangeLogFocus = CRMEntity::getInstance('ChangeLog');
			
			$body .= $ChangeLogFocus->getFieldsTable($description, $related_module);
			
		}else{
			$focus = CRMEntity::getInstance($related_module);
			if(!isRecordExists($related_to)) return ''; //crmv@33364
			$focus->retrieve_entity_info($related_to,$related_module);
			$qcreate_array = QuickCreate($related_module);
			$query = "select fieldname from {$table_prefix}_entityname where modulename = ?";
			$result = $adb->pquery($query, array($related_module));
			if ($result && $adb->num_rows($result) > 0) {
				if(strpos($adb->query_result($result,0,'fieldname'),',') !== false) {
					$fieldlists = explode(',',$adb->query_result($result,0,'fieldname'));
				} else {
					$fieldlists = array($adb->query_result($result,0,'fieldname'));
				}
				foreach($fieldlists as $field) {
					unset($qcreate_array['data'][$field]);
				}
			}
			$fieldnames = array_keys($qcreate_array['data']);			
			//danzi.tn@20140630 aggiungere numero ticket al body anche se non fa barte dei quickcreate
			if($related_module=="HelpDesk") {
				$fieldnames[] = "ticket_no";
			}
			if (!empty($fieldnames)) {
				$result = $adb->pquery('select * from '.$table_prefix.'_field where tabid = ? and fieldname in ('.generateQuestionMarks($fieldnames).')',array(getTabid($related_module),$fieldnames));
				if ($result && $adb->num_rows($result) > 0) {
					while($row=$adb->fetchByAssoc($result)) {
						$info = getDetailViewOutputHtml($row['uitype'],$row['fieldname'],$row['fieldlabel'],$focus->column_fields,$row['generatedtype'],$row['tabid'],$related_module);
						if ($info[1] != '') {
							$body .= '<b>'.$info[0].'</b>: '.strip_tags($info[1]).'<br />';
						}
					}
				}
			}
		}
		
		$body .= "<a href='{$site_URL}/index.php?module=$related_module&action=DetailView&record=$related_to' title='".$recordName[$relation_parent_id]." ($entityType)'>".getTranslatedString('MSG_OTHER_INFO','ModNotifications')."</a>";
		
		if ($type == 'Relation') {
			$relation_focus = CRMEntity::getInstance($relation_parent_module);
			if(!isRecordExists($relation_parent_id)) return ''; //crmv@33364
			$relation_focus->retrieve_entity_info($relation_parent_id,$relation_parent_module);
			
			$qcreate_array = QuickCreate($relation_parent_module);
			$query = "select fieldname from {$table_prefix}_entityname where modulename = ?";
			$result = $adb->pquery($query, array($relation_parent_module));
			if ($result && $adb->num_rows($result) > 0) {
				if(strpos($adb->query_result($result,0,'fieldname'),',') !== false) {
					$fieldlists = explode(',',$adb->query_result($result,0,'fieldname'));
				} else {
					$fieldlists = array($adb->query_result($result,0,'fieldname'));
				}
				foreach($fieldlists as $field) {
					unset($qcreate_array['data'][$field]);
				}
			}
			$fieldnames = array_keys($qcreate_array['data']);
			//danzi.tn@20140630 aggiungere numero ticket al body anche se non fa barte dei quickcreate
			if($relation_parent_module=="HelpDesk") {
				$fieldnames[] = "ticket_no";
			}
			if (!empty($fieldnames)) {
				$body .= '<br /><br />'.getTranslatedString('MSG_DETAILS_OF','ModNotifications').' <b>'.$relation_recordName[$relation_parent_id].'</b> '.getTranslatedString('MSG_DETAILS_ARE','ModNotifications').'<br />';
				
				$result = $adb->pquery('select * from '.$table_prefix.'_field where tabid = ? and fieldname in ('.generateQuestionMarks($fieldnames).')',array(getTabid($relation_parent_module),$fieldnames));
				if ($result && $adb->num_rows($result) > 0) {
					while($row=$adb->fetchByAssoc($result)) {
						$info = getDetailViewOutputHtml($row['uitype'],$row['fieldname'],$row['fieldlabel'],$relation_focus->column_fields,$row['generatedtype'],$row['tabid'],$relation_parent_module);
						if ($info[1] != '') {
							$body .= '<b>'.$info[0].'</b>: '.strip_tags($info[1]).'<br />';
						}
					}
				}
				
				$body .= "<a href='{$site_URL}/index.php?module=$relation_parent_module&action=DetailView&record=$relation_parent_id' title='".$relation_recordName[$relation_parent_id]." ($relation_entityType)'>".getTranslatedString('MSG_OTHER_INFO','ModNotifications')."</a>";
			}
		}
		if (!$only_content) {
			$body .= '<br /><br />'.getTranslatedString('LBL_REGARDS','HelpDesk').',<br />'.$signature;
		}
		
		$default_language = $default_language_tmp;
		$current_language = $current_language_tmp;
		return $body;
	}
	function getBodyNotificationCV($id,$column_fields,$signature,$only_content=false) {
		global $site_URL,$adb,$current_user,$current_language,$default_language,$table_prefix;
		$default_language_tmp = $default_language;
		$current_language_tmp = $current_language;
		
		$body = '';
		$user = CRMEntity::getInstance('Users');
		$user->retrieve_entity_info($column_fields['assigned_user_id'],'Users');
		$current_language = $default_language = $user->column_fields['default_language'];
		$related_to = $column_fields['related_to'];
		$type = $column_fields['mod_not_type'];
		
		if (!$only_content) {
			$body = getTranslatedString('MSG_DEAR','ModNotifications').' '.getUserFullName($user->id).',<br />';
		}
		
		if ($only_content) {
			require_once('modules/ModNotifications/models/Comments.php');
			$model = new ModNotifications_CommentsModel($column_fields);
			$body .= $model->timestampAgo().' ';
		}
		$body .= strtolower($this->translateNotificationType($type,'action'));
		
		$result = $adb->query('SELECT * FROM '.$table_prefix.'_customview WHERE cvid = '.$related_to);
		if ($result) {
			$related_module = $adb->query_result($result,0,'entitytype');
			$entityType = getTranslatedString($related_module,$related_module);
			$recordName = $adb->query_result($result,0,'viewname');
			if ($recordName == 'All') {
				$recordName = getTranslatedString('COMBO_ALL');
			} elseif($this->parent_module == 'Calendar' && in_array($recordName,array('Events','Tasks'))) {
				$recordName = getTranslatedString($recordName);
			}
			$body .= " <a href='{$site_URL}/index.php?module=$related_module&action=index&viewname=$related_to' title='$entityType' target='_parent'>$recordName</a> ($entityType)";
		}
		
		$body .= '&nbsp;:<br />';
		$changes = array_filter(explode(',',$column_fields['description']));
		$body_changes = '';
		if (!empty($changes)) {
			foreach($changes as $change_id) {
				$change_module = getSalesEntityType($change_id);	
				$displayValueArray = getEntityName($change_module,$change_id);
				if(!empty($displayValueArray)){
					foreach($displayValueArray as $key=>$value){
						$displayValue = $value;
					}
				}
				$body_changes[] = "<a href='{$site_URL}/index.php?module=$change_module&action=DetailView&record=$change_id' target='_parent'>$displayValue</a>";
			}
			$body .= implode(', ',$body_changes);
		}

		if (!$only_content) {
			$body .= '<br /><br />'.getTranslatedString('LBL_REGARDS','HelpDesk').',<br />'.$signature;
		}
		
		$default_language = $default_language_tmp;
		$current_language = $current_language_tmp;
		return $body;
	}
	//crmv@33364
	function sendNotificationSummary($userid) {
		global $current_user, $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $current_language, $default_language;
		$current_user_tmp = $current_user;
		$current_user = CRMEntity::getInstance('Users');
		$current_user->retrieve_entity_info($userid,'Users');
		$current_language = $default_language = $current_user->column_fields['default_language'];
		
		$widgetInstance = $this->getWidget('DetailViewBlockCommentWidget');
		$unseen = $widgetInstance->getUnseenComments('',array('ID'=>''));
		if (!empty($unseen)) {
			$send_mail = false;
			$notification_details = array();
			$notification_sents = array();
			foreach($unseen as $not_id) {
				$not_focus = CRMEntity::getInstance('ModNotifications');
				$not_focus->retrieve_entity_info($not_id,'ModNotifications');
				$not_focus->column_fields['smcreatorid'] = $not_focus->column_fields['creator'];
				$limit_time = $this->notification_summary_values[$current_user->column_fields['notify_summary']]; //mycrmv@34901
				//echo $limit_time.' '.$not_id.': '.strtotime($not_focus->column_fields['createdtime']).' <= '.strtotime($limit_time).' : '.date('Y-m-d H:i:s',strtotime($not_focus->column_fields['createdtime'])).' <= '.date('Y-m-d H:i:s',strtotime($limit_time));
				if ($not_focus->column_fields['sent_summary_not'] != 1) {	//controllo se non ho già inviato la notifica
					if (strtotime($not_focus->column_fields['createdtime']) <= strtotime($limit_time)) {	//mi basta che ci sia una notifica non letta da più di X tempo per notificare via mail tutte le notifiche non lette
						$send_mail = true;
					}
					if ($not_focus->column_fields['mod_not_type'] == 'ListView changed') {
						$notification_details[] = $not_focus->getBodyNotificationCV($not_id,$not_focus->column_fields,$HELPDESK_SUPPORT_NAME,true).'<br />';
					} else {
						$notification_details[] = $not_focus->getBodyNotification($not_id,$not_focus->column_fields,$HELPDESK_SUPPORT_NAME,true).'<br />';
					}
					$notification_sents[] = $not_id;
				}
			}
			if ($send_mail && !empty($notification_details)) {
				$unseen_count = count($notification_sents);
				require_once('modules/Emails/mail.php');
				$subject = getTranslatedString('ModNotifications','Users').' '.getTranslatedString('Notification Summary','Users').' '.getTranslatedString('unseen','ModNotifications').' ('.$unseen_count.')';
				$body = getTranslatedString('MSG_DEAR','ModNotifications').' '.getUserFullName($current_user->id).',<br />';
				if ($unseen_count == 1) {
					$body .= getTranslatedString('MSG_1_NOTIFICATION_UNSEEN','ModNotifications').'.';
				} else {
					$body .= sprintf(getTranslatedString('MSG_NOTIFICATIONS_UNSEEN','ModNotifications'),$unseen_count).'.';
				}
				$body .= implode('<br /><br />',array_filter($notification_details));
				$body .= '<br /><br />'.getTranslatedString('LBL_REGARDS','HelpDesk').',<br />'.$HELPDESK_SUPPORT_NAME;
				$mail_status = send_mail('ModNotifications',$current_user->column_fields['email1'],$HELPDESK_SUPPORT_NAME,$HELPDESK_SUPPORT_EMAIL_ID,$subject,$body);
			}
			if ($mail_status == 1) {
				if (!empty($notification_sents)) {
					foreach ($notification_sents as $notification_sent) {
						$focus = CRMEntity::getInstance('ModNotifications');
						$focus->retrieve_entity_info($notification_sent,'ModNotifications');
						$focus->mode = 'edit';
						$focus->id = $notification_sent;
						$focus->column_fields['sent_summary_not'] = 1;
						$focus->save('ModNotifications',true);
					}
				}
			}
		}
		$current_user = $current_user_tmp;
	}
	//crmv@33364e

	/**
	 * Get list view query (send more WHERE clause condition if required)
	 */
	//crmv@32429
	function getListQuery($module, $where='', $skip_parent_join=false) {

		if (!$skip_parent_join) {
			return parent::getListQuery($module, $where);
		}

		global $current_user, $table_prefix;

		$query = "SELECT ".$table_prefix."_crmentity.*, $this->table_name.*";

		// Select Custom Field Table Columns if present
		if(!empty($this->customFieldTable)) $query .= ", " . $this->customFieldTable[0] . ".* ";

		$query .= " FROM $this->table_name";

		$query .= "	INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = $this->table_name.$this->table_index";

		// Consider custom table join as well.
		if(!empty($this->customFieldTable)) {
			$query .= " INNER JOIN ".$this->customFieldTable[0]." ON ".$this->customFieldTable[0].'.'.$this->customFieldTable[1] .
			" = $this->table_name.$this->table_index";
		}

		$query .= $this->getNonAdminAccessControlQuery($module,$current_user);
		$query .= "	WHERE ".$table_prefix."_crmentity.deleted = 0 ".$where;
		$query = $this->listQueryNonAdminChange($query, $module);
		return $query;
	}
	//crmv@32429e
}
//crmv@end
?>