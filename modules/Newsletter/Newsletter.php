<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once('data/CRMEntity.php');
require_once('data/Tracker.php');

class Newsletter extends CRMEntity {
	var $db, $log; // Used in class functions of CRMEntity

	var $table_name;
	var $table_index= 'newsletterid';
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
		'Newsletter Name'=> Array('newsletter', 'newslettername'),
		'Date scheduled'=> Array('newsletter', 'date_scheduled'),
		'Time scheduled'=> Array('newsletter', 'time_scheduled'),
		'Assigned To' => Array('crmentity','smownerid'),
		'Scheduled' => Array('newsletter','scheduled'),
	);
	var $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'Newsletter Name'=> 'newslettername',
		'Date scheduled'=> 'date_scheduled',
		'Time scheduled'=> 'time_scheduled',
		'Assigned To' => 'assigned_user_id',
		'Scheduled' => 'scheduled',
	);

	// Make the field link to detail view from list view (Fieldname)
	var $list_link_field = 'newslettername';

	// For Popup listview and UI type support
	var $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Newsletter Name'=> Array('newsletter', 'newslettername')
	);
	var $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'Newsletter Name'=> 'newslettername'
	);

	// For Popup window record selection
	var $popup_fields = Array('newslettername');

	// Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
	var $sortby_fields = Array();

	// For Alphabetical search
	var $def_basicsearch_col = 'newslettername';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'newslettername';

	// Required Information for enabling Import feature
	var $required_fields = Array('newslettername'=>1);

	// Callback function list during Importing
	var $special_functions = Array('set_import_assigned_user');

	var $default_order_by = 'newslettername';
	var $default_sort_order='ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('createdtime', 'modifiedtime', 'newslettername');
	//crmv@10759
	var $search_base_field = 'newslettername';
	//crmv@10759 e
	
	//Newsletter & Campaigns params - i
	var $email_fields = array();
	var $url_tracklink_file;
	var $url_trackuser_file;
	var $track_userhistory_systeminfo = array(
		'HTTP_USER_AGENT',
		'HTTP_REFERER',
		'REMOTE_ADDR'
	);
	var $max_attempts_permitted = 5;					//numero di tentativi possibili di spedizione di una mail in coda
	var $no_email_processed_by_schedule = 70;			//numero di mail processate per schedulazione
	var $interval_between_email_delivery = 0;			//(seconds) intervallo tra la spedizione delle singole email
	var $interval_between_blocks_email_delivery = 120;	//(seconds) intervallo tra le schedulazioni
	//Newsletter & Campaigns params - e
	
	function __construct() {
		global $log, $currentModule, $site_URL, $table_prefix;
		$this->db = PearDatabase::getInstance();
		$this->log = $log;
		$this->table_name = $table_prefix.'_newsletter';
		$this->customFieldTable = Array($table_prefix.'_newslettercf', 'newsletterid');
		$this->tab_name = Array($table_prefix.'_crmentity', $table_prefix.'_newsletter', $table_prefix.'_newslettercf');
		$this->tab_name_index = Array(
			$table_prefix.'_crmentity' => 'crmid',
			$table_prefix.'_newsletter'   => 'newsletterid',
		    $table_prefix.'_newslettercf' => 'newsletterid'
		);
		$this->column_fields = getColumnFields($currentModule);
		$this->email_fields = array(
			'Accounts'=>array('fieldname'=>'email1','tablename'=>$table_prefix.'_account','columnname'=>'email1'),
			'Contacts'=>array('fieldname'=>'email','tablename'=>$table_prefix.'_contactdetails','columnname'=>'email'),
			'Leads'=>array('fieldname'=>'email','tablename'=>$table_prefix.'_leaddetails','columnname'=>'email')
		);
		$this->url_tracklink_file = $site_URL.'/modules/Newsletter/TrackLink.php';
		$this->url_trackuser_file = $site_URL.'/modules/Newsletter/TrackUser.php';
		$this->url_unsubscription_file = $site_URL.'/modules/Newsletter/Unsubscription.php';
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
		global $current_user;
		global $table_prefix;
		$thismodule = $_REQUEST['module'];
		
		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery($thismodule, "detail_view");
		
		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT $fields_list, $table_prefix.'_users.user_name AS user_name 
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
		global $current_user;
		global$table_prefix;
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
		global $table_prefix;
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
		global $current_user, $adb;
		global $table_prefix;
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
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	function vtlib_handler($modulename, $event_type) {
		if($event_type == 'module.postinstall') {
			
			global $adb;
			global $table_prefix;
			$adb->pquery('UPDATE '.$table_prefix.'_tab SET customized=0 WHERE name=?', array($modulename));
			
			$newsletterModule = Vtiger_Module::getInstance($modulename);
			$campaignsModule = Vtiger_Module::getInstance('Campaigns');
			$campaignsModule->setRelatedList($newsletterModule, 'Newsletter', Array('ADD'), 'get_newsletter');
			
			$i=2;
			$adb->query("UPDATE ".$table_prefix."_relatedlists SET sequence = $i WHERE tabid = 26 AND label = 'Newsletter'");
			$res = $adb->query("SELECT * FROM ".$table_prefix."_relatedlists WHERE tabid = 26 AND label NOT IN ('Newsletter','Targets') ORDER BY sequence");
			while($row=$adb->fetchByAssoc($res)) {
				$i++;
				$adb->pquery("UPDATE ".$table_prefix."_relatedlists SET sequence = $i WHERE relation_id = ?",array($row['relation_id']));
			}
			
			$em = new VTEventsManager($adb);
			$em->registerHandler('vtiger.entity.beforesave','modules/Newsletter/NewsletterHandler.php','NewsletterHandler');
			
			require_once('modules/Newsletter/InstallCampaignStatistics.php');
			installCampaignStatistics();
			
			$schema_tables = array(
				'tbl_s_newsletter_queue'=>
					'<schema version="0.3">
					  <table name="tbl_s_newsletter_queue">
					  <opt platform="mysql">ENGINE=InnoDB</opt>
						<field name="newsletterid" type="R" size="19">
						  <KEY/>
						</field>
						<field name="crmid" type="R" size="19">
						  <KEY/>
						</field>
						<field name="status" type="C" size="255"/>
						<field name="attempts" type="I" size="19"/>
						<field name="date_scheduled" type="T"/>
						<field name="last_attempt" type="T"/>
						<field name="date_sent" type="T"/>
						<field name="first_view" type="T"/>
						<field name="last_view" type="T"/>
						<field name="num_views" type="I" size="19"/>
						<index name="NewIndex1">
						  <col>newsletterid</col>
						</index>
						<index name="NewIndex2">
						  <col>crmid</col>
						</index>
						<index name="NewIndex3">
						  <col>status</col>
						</index>
						<index name="NewIndex4">
						  <col>attempts</col>
						</index>
					  </table>
					</schema>',
				'tbl_s_newsletter_tl'=>
					'<schema version="0.3">
					  <table name="tbl_s_newsletter_tl">
					  <opt platform="mysql">ENGINE=InnoDB</opt>
						<field name="linkid" type="R" size="19">
						  <KEY/>
						</field>
						<field name="newsletterid" type="I" size="19"/>
						<field name="crmid" type="I" size="19"/>
						<field name="url" type="C" size="255"/>
						<field name="forward" type="X"/>
						<field name="firstclick" type="T"/>
						<field name="latestclick" type="T"/>
						<field name="clicked" type="I" size="19">
						  <DEFAULT value="0"/>
						</field>
						<index name="midindex">
						  <col>newsletterid</col>
						</index>
						<index name="uidindex">
						  <col>crmid</col>
						</index>
						<index name="urlindex">
						  <col>url</col>
						</index>
						<index name="miduidindex">
						  <col>newsletterid</col>
						  <col>crmid</col>
						</index>
						<index name="miduidurlindex">
						  <col>newsletterid</col>
						  <col>crmid</col>
						  <col>url</col>
						</index>
					  </table>
					</schema>',
				'tbl_s_newsletter_tl_click'=>
					'<schema version="0.3">
					  <table name="tbl_s_newsletter_tl_click">
					  <opt platform="mysql">ENGINE=InnoDB</opt>
						<field name="linkid" type="I" size="19"/>
						<field name="crmid" type="I" size="19"/>
						<field name="newsletterid" type="I" size="19"/>
						<field name="name" type="C" size="255"/>
						<field name="data" type="X"/>
						<field name="date" type="T"/>
						<index name="linkindex">
						  <col>linkid</col>
						</index>
						<index name="uidindex">
						  <col>crmid</col>
						</index>
						<index name="midindex">
						  <col>newsletterid</col>
						</index>
						<index name="linkuserindex">
						  <col>linkid</col>
						  <col>crmid</col>
						</index>
						<index name="linkusermessageindex">
						  <col>linkid</col>
						  <col>crmid</col>
						  <col>newsletterid</col>
						</index>
					  </table>
					</schema>',
				'tbl_s_newsletter_unsub'=>
					'<schema version="0.3">
					  <table name="tbl_s_newsletter_unsub">
					  <opt platform="mysql">ENGINE=InnoDB</opt>
						<field name="newsletterid" type="R" size="19">
						  <KEY/>
						</field>
						<field name="email" type="C" size="100">
						  <KEY/>
						</field>
						<field name="type" type="C" size="100"/>
					  </table>
					</schema>',
				'tbl_s_newsletter_bounce'=>
					'<schema version="0.3">
					  <table name="tbl_s_newsletter_bounce">
					  <opt platform="mysql">ENGINE=InnoDB</opt>
						<field name="id" type="R" size="19">
						  <KEY/>
						</field>
						<field name="date" type="T"/>
						<field name="header" type="X"/>
						<field name="data" type="B"/>
						<field name="status" type="C" size="255"/>
						<field name="comment" type="X"/>
						<index name="dateindex">
						  <col>date</col>
						</index>
					  </table>
					</schema>',
				'tbl_s_newsletter_bounce_rel'=>
					'<schema version="0.3">
					  <table name="tbl_s_newsletter_bounce_rel">
					  <opt platform="mysql">ENGINE=InnoDB</opt>
						<field name="id" type="R" size="19">
						  <KEY/>
						</field>
						<field name="crmid" type="I" size="19"/>
						<field name="newsletterid" type="I" size="19"/>
						<field name="bounce" type="I" size="19"/>
						<field name="time" type="T"/>
						<index name="umbindex">
						  <col>crmid</col>
						  <col>newsletterid</col>
						  <col>bounce</col>
						</index>
						<index name="useridx">
						  <col>crmid</col>
						</index>
						<index name="msgidx">
						  <col>newsletterid</col>
						</index>
						<index name="bounceidx">
						  <col>bounce</col>
						</index>
					  </table>
					</schema>',
				//crmv@25872
				'tbl_s_newsletter_failed'=>
					'<schema version="0.3">
					  <table name="tbl_s_newsletter_failed">
					  <opt platform="mysql">ENGINE=InnoDB</opt>
						<field name="newsletterid" type="R" size="19">
						  <KEY/>
						</field>
						<field name="crmid" type="R" size="19">
						  <KEY/>
						</field>
						<field name="note" type="C" size="255"/>
						<index name="NewIndex1">
						  <col>newsletterid</col>
						</index>
						<index name="NewIndex2">
						  <col>crmid</col>
						</index>
						<index name="NewIndex3">
						  <col>note</col>
						</index>
					  </table>
					</schema>',
				//crmv@25872e
			);
			foreach($schema_tables as $table_name => $schema_table) {
				if(!Vtiger_Utils::CheckTable($table_name)) {
					$schema_obj = new adoSchema($adb->database);
					$schema_obj->ExecuteSchema($schema_obj->ParseSchemaString($schema_table));
				}
			}
			
			$adb->query("UPDATE ".$table_prefix."_relatedlists SET actions = '' WHERE related_tabid = 26 AND tabid IN (4,6,7)");
			$adb->query("UPDATE ".$table_prefix."_relatedlists SET name = 'get_campaigns_newsletter' WHERE related_tabid = 26 AND tabid IN (4,6,7)");
			
			create_tab_data_file();
			
			$this->setModuleSeqNumber('configure', 'Newsletter', 'NWS-', 1);
			
			require_once('modules/Newsletter/MigrateRelatedToTarget.php');
			migrateRelatedToTarget();
			
		} else if($event_type == 'module.disabled') {
			// TODO Handle actions when this module is disabled.
		} else if($event_type == 'module.enabled') {
			// TODO Handle actions when this module is enabled.
		} else if($event_type == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
		} else if($event_type == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} else if($event_type == 'module.postupdate') {
			//aggiorno il file cron/modules/Newsletter/Newsletter.service.php che altrimenti non sarebbe aggiornato con il normale update
			$tmp_dir = "packages/vte/mandatory/tmp1";
			mkdir($tmp_dir);
			$unzip = new Vtiger_Unzip('packages/vte/mandatory/Newsletters.zip');
			$unzip->unzipAllEx($tmp_dir);
			if($unzip) $unzip->close();

			$tmp_dir1 = "$tmp_dir/Newsletter";
			mkdir($tmp_dir1);
			$unzip1 = new Vtiger_Unzip('packages/vte/mandatory/tmp1/Newsletter.zip');
			$unzip1->unzipAllEx($tmp_dir1);
			if($unzip1) $unzip1->close();
			copy("$tmp_dir1/cron/Newsletter.service.php",'cron/modules/Newsletter/Newsletter.service.php');

			if ($handle = opendir($tmp_dir)) {
				require_once('modules/SDK/src/Utils.php');
				folderDetete($tmp_dir);
			}
			//end
		}
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

	function getTargetList($check_duplicates=true,$remove_unsubscripted=true) {
		global $adb;
		$return = array();
		$return_contacts = array();
		$return_accounts = array();
		$return_leads = array();
		$focus_campaign = CRMEntity::getInstance('Campaigns');
		$focus_campaign->retrieve_entity_info($this->column_fields['campaignid'],"Campaigns");
		$focus_campaign->get_related_list($this->column_fields['campaignid'],getTabid('Campaigns'),getTabid('Targets'));
		$result_targets = $adb->query($_SESSION['targets_listquery']);
		while($targets=$adb->fetchByAssoc($result_targets)) {
			$this->getTargetElements($return,$return_contacts,$return_accounts,$return_leads,$targets['crmid']);
		}
		if ($check_duplicates) {	//Controllo duplicati sul campo email. La priorità è Contatto, Azienda, Lead
			$emails_array = array();
			/*
			$contacts_ids = array_keys($return_contacts);
			$accounts_ids = array_keys($return_accounts);
			$leds_ids = array_keys($return_leads);
			foreach($return as $crmid => $email) {
				$ids = array_keys($return,$email);
				//echo "<pre>$id: $email: ";print_r($ids);echo '</pre>';
				$weight = array();
				foreach($ids as $id) {
					if (in_array($id,$contacts_ids)) {
						$weight[$id] = 2;
					} elseif (in_array($id,$accounts_ids)) {
						$weight[$id] = 1;
					} elseif (in_array($id,$leds_ids)) {
						$weight[$id] = 0;
					}
				}
				//echo "<pre>";print_r($weight);echo '</pre>';
				$winner = array_search(max($weight),$weight);
				//echo $winner;
				unset($weight[$winner]);
				foreach($weight as $id => $w) {
					unset($return[$id]);
				}
			}
			*/
			foreach($return_leads as $crmid => $email) {
				$emails_array[$email] = $crmid;
			}
			foreach($return_accounts as $crmid => $email) {
				$emails_array[$email] = $crmid;
			}
			foreach($return_contacts as $crmid => $email) {
				$emails_array[$email] = $crmid;
			}
			$return = array();
			foreach($emails_array as $email => $crmid) {
				$return[$crmid] = $email;
			}
		}	
		//crmv@25085
		$unsubscripted = $this->getUnsubscriptedList();
		$email_return=array();
		$email_skipped=array();
		if ($remove_unsubscripted && !empty($unsubscripted)) {	//Rimuovo i disiscritti dalla Newsletter
			foreach($return as $keycrmid => $valemail) {
				$bFound = false;
				foreach( $unsubscripted as $unsub_email) {
					if($valemail == $unsub_email){
						$email_skipped[$keycrmid] = $valemail;
						$bFound = true;
						break;
					} 
				}
				if(!$bFound) {
					$email_return[$keycrmid] = $valemail;
				}
				// danzi.tn@20140628 sta roba sotto fa casino perchèsegue unset su un array mentre si sta iterando sullo stesso
				//if (in_array($email,$unsubscripted)) {
				//	$ids = array_keys($return,$email);
				//	foreach($ids as $id) {
				//		unset($return[$id]);
				//	}
				//}
			}
		}
		//crmv@25085e
		//echo "<pre>"; echo count($return); echo "</pre>";
		//echo "<pre>"; echo count($email_skipped); echo "</pre>";
		//echo "<pre>"; echo count($email_return); echo "</pre>";
		$ret_array = array_keys($email_return);
		//echo "<pre>"; echo print_r($ret_array); echo "</pre>";
		//Die("Count");
		return $ret_array;
	}
	
	function getTargetElements(&$return,&$return_contacts,&$return_accounts,&$return_leads,$targetid) {
		
		global $adb,$onlyquery;
		global $table_prefix;
		$onlyquery = true;
		
		$focus_target = CRMEntity::getInstance('Targets');
		
		$focus_target->get_related_list_target($targetid, getTabid('Targets'), getTabid('Leads'));
		$result_leads = $adb->query(replaceSelectQuery($_SESSION['leads_listquery'],$table_prefix.'_crmentity.crmid,'.$this->email_fields['Leads']['tablename'].'.'.$this->email_fields['Leads']['columnname']));
		while($leads=$adb->fetchByAssoc($result_leads)) {
			if ($leads[$this->email_fields['Leads']['columnname']] != '') {
				$return[$leads['crmid']] = $leads[$this->email_fields['Leads']['columnname']];
				$return_leads[$leads['crmid']] = $leads[$this->email_fields['Leads']['columnname']];
			}
		}

		$focus_target->get_related_list_target($targetid, getTabid('Targets'), getTabid('Accounts'));
		$result_accounts = $adb->query(replaceSelectQuery($_SESSION['accounts_listquery'],$table_prefix.'_crmentity.crmid,'.$this->email_fields['Accounts']['tablename'].'.'.$this->email_fields['Accounts']['columnname']));
		while($accounts=$adb->fetchByAssoc($result_accounts)) {
			if ($accounts[$this->email_fields['Accounts']['columnname']] != '') {
				$return[$accounts['crmid']] = $accounts[$this->email_fields['Accounts']['columnname']];
				$return_accounts[$accounts['crmid']] = $accounts[$this->email_fields['Accounts']['columnname']];
			}
		}
		
		$focus_target->get_related_list_target($targetid, getTabid('Targets'), getTabid('Contacts'));
		$result_contacts = $adb->query(replaceSelectQuery($_SESSION['contacts_listquery'],$table_prefix.'_crmentity.crmid,'.$this->email_fields['Contacts']['tablename'].'.'.$this->email_fields['Contacts']['columnname']));
		while($contacts=$adb->fetchByAssoc($result_contacts)) {
			if ($contacts[$this->email_fields['Contacts']['columnname']]) {
				$return[$contacts['crmid']] = $contacts[$this->email_fields['Contacts']['columnname']];
				$return_contacts[$contacts['crmid']] = $contacts[$this->email_fields['Contacts']['columnname']];
			}
		}
		
		$focus_target->get_targets($targetid, getTabid('Targets'), getTabid('Targets'));
		$result_targets_targets = $adb->query($_SESSION['targets_listquery']);
		while($targets_targets=$adb->fetchByAssoc($result_targets_targets)) {
			$this->getTargetElements($return,$return_contacts,$return_accounts,$return_leads,$targets_targets['crmid']); 
		}
		
		$onlyquery = false;
	}

	function getTargetTree() {
		global $adb;
		$return = array();
		$focus_campaign = CRMEntity::getInstance('Campaigns');
		$focus_campaign->retrieve_entity_info($this->column_fields['campaignid'],"Campaigns");
		$focus_campaign->get_related_list($this->column_fields['campaignid'],getTabid('Campaigns'),getTabid('Targets'));
		$result_targets = $adb->query($_SESSION['targets_listquery']);
		while($targets=$adb->fetchByAssoc($result_targets)) {
			$return[$targets['crmid']] = $this->getTargetBranches($targets['crmid']);
		}
		return $return;
	}
	
	function getTargetBranches($targetid) {
		
		global $adb;

		$focus_target = CRMEntity::getInstance('Targets');
		
		$focus_target->get_related_list_target($targetid, getTabid('Targets'), getTabid('Leads'));
		$result_leads = $adb->query($_SESSION['leads_listquery']);
		while($leads=$adb->fetchByAssoc($result_leads)) {
			$return['Leads'][] = $leads['crmid'];
		}
		
		$focus_target->get_related_list_target($targetid, getTabid('Targets'), getTabid('Accounts'));
		$result_accounts = $adb->query($_SESSION['accounts_listquery']);
		while($accounts=$adb->fetchByAssoc($result_accounts)) {
			$return['Accounts'][] = $accounts['crmid'];
		}
		
		$focus_target->get_related_list_target($targetid, getTabid('Targets'), getTabid('Contacts'));
		$result_contacts = $adb->query($_SESSION['contacts_listquery']);
		while($contacts=$adb->fetchByAssoc($result_contacts)) {
			$return['Contacts'][] = $contacts['crmid'];
		}
		
		$focus_target->get_targets($targetid, getTabid('Targets'), getTabid('Targets'));
		$result_targets_targets = $adb->query($_SESSION['targets_listquery']);
		while($targets_targets=$adb->fetchByAssoc($result_targets_targets)) {
			$return['Targets'][$targets_targets['crmid']] = $this->getTargetBranches($targets_targets['crmid']); 
		}
		
		return $return;
	}
	
	function sendNewsletter($crmid='',$mode='',$to_address='') {
		require_once('modules/Emails/mail.php');
		global $adb;
		global $table_prefix;
		$module = getSalesEntityType($crmid);
		//crmv@25872
		if ($to_address == '' && $crmid != '') {
			$focus = CRMEntity::getInstance($module);
			$error = $focus->retrieve_entity_info($crmid,$module,false);
			if ($error != '') {
				return $error;
			}
			$to_address = $focus->column_fields[$this->email_fields[$module]['fieldname']];
		}
		//crmv@25872e
		//crmv@28170
		//mycrmv
		//static $emailtemplates = array();
		//if (!isset($emailtemplates['templateemailid'])) {
			$result = $adb->query('select subject,body from '.$table_prefix.'_emailtemplates where templateid = '.$this->column_fields['templateemailid']);
			 $description = $adb->query_result($result,0,'body');
			 $subject = $adb->query_result_no_html($result,0,'subject');	//crmv@25243
		//} else {
		//	$description = $emailtemplates['templateemailid']['description'];
		//	$subject = $emailtemplates['templateemailid']['subject'];
	//	}
	//mycrmv e
		//crmv@28170e
		if ($mode != 'test') {
			$description = getMergedDescription($description,$crmid,$module,$this->id,$this->column_fields['templateemailid']);
			$description_saved = html_entity_decode($description);	//nella mail che salvo e associo al Contatto/Azienda/Lead non metto i track
			$description = $this->setTrackLinks($description,$crmid);
			$subject = getMergedDescription($subject,$crmid,$module,$this->id,$this->column_fields['templateemailid']);
		}
		//logo - i
		if (is_array($description)) {
			foreach($description as $type => $descr) {
				if (strpos($description[$type], '$logo$') !== false)
				{
					$description[$type] = str_replace('$logo$','<img src="cid:logo" />',$description[$type]);
					$logo=1;
				}
			}
		} else {
			if (strpos($description, '$logo$') !== false)
			{
				$description = str_replace('$logo$','<img src="cid:logo" />',$description);
				$logo=1;
			}
		}
		if (strpos($description_saved, '$logo$') !== false)
		{
			$description_saved = str_replace('$logo$','<img src="cid:logo" />',$description_saved);
		}
		//logo - e
		$from_name = $this->column_fields['from_name'];
		$from_address = $this->column_fields['from_address'];
		if ($mode != 'test') {
			include('modules/Campaigns/ProcessBounces.config.php');
			$newsletter_params = array(
				'sender'=>$message_envelope,
				'newsletterid'=>$this->id,
				'crmid'=>$crmid,
			);
		}
		$mail_status = send_mail('Emails',$to_address,$from_name,$from_address,$subject,$description,'','','all',$this->id,$logo,$newsletter_params);
		//$mail_status_debug = send_mail('Emails','test.crmvillage@gmail.com',$from_name,$from_address,$subject,$description,'','','all',$this->id,$logo,$newsletter_params);
		//collego la mail al Contatto/Azienda/Lead - i
		if ($mode != 'test' && $mail_status == 1) {
			require_once('modules/Emails/Emails.php');
			$focus = new Emails();
			$focus->mode = '';
			$focus->column_fields['subject'] = $subject;
			$focus->column_fields['description'] = $description_saved;
			$focus->column_fields['parent_id'] = "$this->id@|$crmid@|";
			$focus->column_fields['parent_type'] = $module;
			$focus->column_fields['activitytype'] = 'Emails';
			$focus->column_fields['email_flag'] = 'SENT';
			$focus->column_fields['date_start']= date(getNewDisplayDate());
			$focus->column_fields['time_start'] = date('H:i:s');
			$assigned_user_id = getRecordOwnerId($crmid);
			if ($assigned_user_id['Users']) {
				$assigned_user_id = $assigned_user_id['Users'];
			} else {
				$assigned_user_id = $assigned_user_id['Groups'];
			}
			$focus->column_fields['assigned_user_id'] = $assigned_user_id;
			$focus->column_fields['from_email'] = $from_address;
			$focus->column_fields['saved_toid'] = $to_address;
			$focus->column_fields['ccmail'] = '';
			$focus->column_fields['bccmail'] = '';
			$focus->column_fields['access_count'] = '';			
			$focus->save("Emails",true);
		}		
		//collego la mail al Contatto/Azienda/Lead - e
		return $mail_status;
	}
	
	function setTrackLinks($description,$crmid) {
		global $adb,$site_URL;
		
		$htmlmessage = $textmessage = html_entity_decode($description);

		preg_match_all('/<a(.*)href=["\'](.*)["\']([^>]*)>(.*)<\/a>/Umis',$htmlmessage,$links);
		for($i=0; $i<count($links[2]); $i++){
			$link = $this->cleanTrackUrl($links[2][$i]);
			$link = str_replace('"','',$link);
			if (preg_match('/\.$/',$link)) {
				$link = substr($link,0,-1);
			}
			$linkid = 0;
			//echo "LINK: $link<br/>";
			if ((preg_match('/^http|ftp/',$link) || preg_match('/^http|ftp/',$urlbase)) && !strpos($link,$this->url_tracklink_file)) {
				
				$url = $this->cleanTrackUrl($link,array('PHPSESSID','uid'));
				
				$linkid = $adb->getUniqueID('tbl_s_newsletter_tl');
				$adb->pquery('insert into tbl_s_newsletter_tl (linkid,newsletterid,crmid,url,forward) values (?,?,?,?,?)',array($linkid,$this->id,$crmid,$url,addslashes($link)));
				
				$masked = "H|$linkid|$this->id|$crmid";
				//$masked = $masked ^ XORmask;
				$masked = urlencode(base64_encode($masked));
				$newlink = sprintf('<a%shref="%s?id=%s" %s>%s</a>',$links[1][$i],$this->url_tracklink_file,$masked,$links[3][$i],$links[4][$i]);
				$htmlmessage = str_replace($links[0][$i], $newlink, $htmlmessage);

				$masked_t = "T|$linkid|$this->id|$crmid";
				//$masked_t = $masked_t ^ XORmask;
				$masked_t = urlencode(base64_encode($masked_t));
				$newlink_t = sprintf('%s?id=%s',$this->url_tracklink_file,$masked_t);
        		$textmessage = str_replace($links[0][$i], '#link#'.$newlink_t.'#link-e#', $textmessage);
			}
		}
		
		$track_user = '<img src="'.$this->url_trackuser_file.'?c='.$crmid.'&n='.$this->id.'" width="1" height="1" border="0">';
		$htmlmessage = $track_user.$htmlmessage;
		
		$textmessage = strip_tags(preg_replace(array("/<p>/i","/<br>/i","/<br \/>/i"),array("\n","\n","\n"),$textmessage));
		$textmessage = str_replace('#link#','<',$textmessage);
		$textmessage = str_replace('#link-e#','>',$textmessage);
		
		return array('html'=>$htmlmessage,'text'=>$textmessage);
	}
	
	function cleanTrackUrl($url,$disallowed_params = array('PHPSESSID')) {
		$parsed = @parse_url($url);
		$params = array();

		if (empty($parsed['query'])) {
			$parsed['query'] = '';
		}
		# hmm parse_str should take the delimiters as a parameter
		if (strpos($parsed['query'],'&amp;')) {
			$pairs = explode('&amp;',$parsed['query']);
			foreach ($pairs as $pair) {
				list($key,$val) = explode('=',$pair);
				$params[$key] = $val;
			}
		} else {
			parse_str($parsed['query'],$params);
		}
		$uri = !empty($parsed['scheme']) ? $parsed['scheme'].':'.((strtolower($parsed['scheme']) == 'mailto') ? '':'//'): '';
		$uri .= !empty($parsed['user']) ? $parsed['user'].(!empty($parsed['pass'])? ':'.$parsed['pass']:'').'@':'';
		$uri .= !empty($parsed['host']) ? $parsed['host'] : '';
		$uri .= !empty($parsed['port']) ? ':'.$parsed['port'] : '';
		$uri .= !empty($parsed['path']) ? $parsed['path'] : '';
		#  $uri .= $parsed['query'] ? '?'.$parsed['query'] : '';
		$query = '';
		foreach ($params as $key => $val) {
			if (!in_array($key,$disallowed_params)) {
				//0008980: Link Conversion for Click Tracking. no = will be added if key is empty.
				$query .= $key . ( $val ? '=' . $val . '&' : '&' );
			}
		}
		$query = substr($query,0,-1);
		$uri .= $query ? '?'.$query : '';
		#  if (!empty($params['p'])) {
		#    $uri .= '?p='.$params['p'];
		#  }
		$uri .= !empty($parsed['fragment']) ? '#'.$parsed['fragment'] : '';
		return $uri;
	}
	
	function unsubscribe($crmid) {
		//il controllo lo faccio sul campo email perchè se modifico il target e aggiungo un lead con 
		//la stessa email di un contatto che si è già disiscritto non devo comunque mandargli la mail
		/*
		 * return: 1	done
		 * return: 2	already done
		 * return: 3	problems
		 */
		global $adb;
		$module = getSalesEntityType($crmid);
		$focus = CRMEntity::getInstance($module);
		$focus->retrieve_entity_info($crmid,$module);
		$email = $focus->column_fields[$this->email_fields[$module]['fieldname']];
		
		$result = $adb->pquery('select * from tbl_s_newsletter_unsub where newsletterid = ? and email = ?',array($this->id,$email));
		if ($result && $adb->num_rows($result)>0) {
			return 2;
		} else {
			$adb->pquery('insert into tbl_s_newsletter_unsub (newsletterid,email,type) values (?,?,?)',array($this->id,$email,'User unsubscription from email'));
			$result = $adb->pquery('select * from tbl_s_newsletter_unsub where newsletterid = ? and email = ?',array($this->id,$email));
			if ($result && $adb->num_rows($result)>0) {
				return 1;
			}
		}
		return 3;
	}
	
	function getUnsubscriptedList() {
		global $adb;
		global $table_prefix;
		$newsletterid = array();
		if ($this->column_fields['campaignid'] != '') {
			$result = $adb->query('SELECT newsletterid FROM '.$table_prefix.'_newsletter 
									INNER JOIN '.$table_prefix.'_crmentity ON '.$table_prefix.'_crmentity.crmid = '.$table_prefix.'_newsletter.newsletterid
									WHERE deleted = 0 AND campaignid = '.$this->column_fields['campaignid']);
			if ($result && $adb->num_rows($result)>0) {
				while($row=$adb->fetchByAssoc($result)) {
					$newsletterid[] = $row['newsletterid'];
				}
			}
		} else {
			$newsletterid[] = $this->id;
		}
		$unsubscripted = array();
		$result = $adb->query('select email from tbl_s_newsletter_unsub where newsletterid in ('.implode(',',$newsletterid).')');
		if ($result && $adb->num_rows($result)>0) {
			while($row=$adb->fetchByAssoc($result)) {
				$unsubscripted[] = $row['email'];
			}
		}
		return $unsubscripted;
	}
	
	function getNoEmailProcessedBySchedule() {
		return $this->no_email_processed_by_schedule;
	}
	
	function getIntervalBetweenEmailDelivery() {
		return $this->interval_between_email_delivery;
	}

	function getIntervalBetweenBlocksEmailDelivery() {
		return $this->interval_between_blocks_email_delivery;
	}
}
?>
