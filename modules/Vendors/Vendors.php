<?php
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/

include_once('config.php');
require_once('include/logging.php');
require_once('data/SugarBean.php');
require_once('include/utils/utils.php');
require_once('include/RelatedListView.php');
require_once('user_privileges/default_module_view.php');

class Vendors extends CRMEntity {
	var $log;
	var $db;
	var $table_name;
	var $table_index= 'vendorid';
	var $tab_name = Array();
	var $tab_name_index = Array();
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array();
	var $column_fields = Array();

        //Pavani: Assign value to entity_table
        var $entity_table;
        var $sortby_fields = Array();

        // This is the list of vtiger_fields that are in the lists.
	var $list_fields = Array(
                                'Vendor Name'=>Array('vendor'=>'vendorname'),
                                'Phone'=>Array('vendor'=>'phone'),
                                'Email'=>Array('vendor'=>'email'),
                                'Category'=>Array('vendor'=>'category')
                                );
        var $list_fields_name = Array(
                                        'Vendor Name'=>'vendorname',
                                        'Phone'=>'phone',
                                        'Email'=>'email',
                                        'Category'=>'category'
                                     );
        var $list_link_field= 'vendorname';

	var $search_fields = Array(
                                'Vendor Name'=>Array('vendor'=>'vendorname'),
                                'Phone'=>Array('vendor'=>'phone'),
                                'Fax'=>Array('vendor'=>'fax'),
                                );
        var $search_fields_name = Array(
                                        'Vendor Name'=>'vendorname',
                                        'Phone'=>'phone',
                                        'Fax'=>'fax',
                                     );
	//Specifying required fields for vendors
        var $required_fields =  array();
		
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('createdtime', 'modifiedtime', 'vendorname');

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'vendorname';
	var $default_sort_order = 'ASC';
	//crmv@10759
	var $search_base_field = 'vendorname';
	//crmv@10759 e
	/**	Constructor which will set the column_fields in this object
	 */
	function Vendors() {
		global $table_prefix;
		$this->table_name = $table_prefix."_vendor";
		$this->tab_name = Array($table_prefix.'_crmentity',$table_prefix.'_vendor',$table_prefix.'_vendorcf');
		$this->tab_name_index = Array($table_prefix.'_crmentity'=>'crmid',$table_prefix.'_vendor'=>'vendorid',$table_prefix.'_vendorcf'=>'vendorid');
		$this->entity_table = $table_prefix."_crmentity";
		$this->customFieldTable = Array($table_prefix.'_vendorcf', 'vendorid');	//crmv@33507
		$this->log =LoggerManager::getLogger('vendor');
		$this->log->debug("Entering Vendors() method ...");
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Vendors');
		$this->log->debug("Exiting Vendor method ...");
	}

	function save_module($module)
	{
	}	

	/**	function used to get the list of products which are related to the vendor
	 *	@param int $id - vendor id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_products($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		global $table_prefix;
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
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";this.form.parent_id.value=\"\";' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>";
			}
		} 

		$query = "SELECT ".$table_prefix."_products.productid, ".$table_prefix."_products.productname, ".$table_prefix."_products.productcode, 
					".$table_prefix."_products.commissionrate, ".$table_prefix."_products.qty_per_unit, ".$table_prefix."_products.unit_price, 
					".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.smownerid,".$table_prefix."_vendor.vendorname 
			  		FROM ".$table_prefix."_products 
			  		INNER JOIN ".$table_prefix."_productcf ON ".$table_prefix."_productcf.productid = ".$table_prefix."_products.productid
			  		INNER JOIN ".$table_prefix."_vendor ON ".$table_prefix."_vendor.vendorid = ".$table_prefix."_products.vendor_id 
			  		INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_products.productid 
			  		WHERE ".$table_prefix."_crmentity.deleted = 0 AND ".$table_prefix."_vendor.vendorid = $id";
					
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 
		
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_products method ...");		
		return $return_value;
	}

	/**	function used to get the list of purchase orders which are related to the vendor
	 *	@param int $id - vendor id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_purchase_orders($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user,$table_prefix;
		$log->debug("Entering get_purchase_orders(".$id.") method ...");
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
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>";
			}
		} 

		$query = "select case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name,".$table_prefix."_crmentity.*, ".$table_prefix."_purchaseorder.*,".$table_prefix."_vendor.vendorname
			from ".$table_prefix."_purchaseorder 
			inner join ".$table_prefix."_purchaseordercf on ".$table_prefix."_purchaseordercf.purchaseorderid = ".$table_prefix."_purchaseorder.purchaseorderid
			inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=".$table_prefix."_purchaseorder.purchaseorderid 
			left outer join ".$table_prefix."_vendor on ".$table_prefix."_purchaseorder.vendorid=".$table_prefix."_vendor.vendorid 
			left join ".$table_prefix."_groups on ".$table_prefix."_groups.groupid=".$table_prefix."_crmentity.smownerid 
			left join ".$table_prefix."_users on ".$table_prefix."_users.id=".$table_prefix."_crmentity.smownerid 
			where ".$table_prefix."_crmentity.deleted=0 and ".$table_prefix."_purchaseorder.vendorid=".$id;
							
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 
		
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_purchase_orders method ...");		
		return $return_value;
	}
	//Pavani: Function to create, export query for vendors module
	/** Function to export the vendors in CSV Format
	 * @param reference variable - where condition is passed when the query is executed
	 * Returns Export Vendors Query.
	 */
	function create_export_query($where,$oCustomView,$viewId)	//crmv@31775
	{
		global $log;
		global $current_user;
		global $table_prefix;
		$log->debug("Entering create_export_query(".$where.") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Vendors", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT $fields_list FROM ".$this->entity_table."
                                INNER JOIN ".$table_prefix."_vendor
                                        ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_vendor.vendorid
                                LEFT JOIN ".$table_prefix."_vendorcf
                                        ON ".$table_prefix."_vendorcf.vendorid=".$table_prefix."_vendor.vendorid
                                LEFT JOIN ".$table_prefix."_seattachmentsrel
                                        ON ".$table_prefix."_vendor.vendorid=".$table_prefix."_seattachmentsrel.crmid
                                LEFT JOIN ".$table_prefix."_attachments
                                ON ".$table_prefix."_seattachmentsrel.attachmentsid = ".$table_prefix."_attachments.attachmentsid
                                LEFT JOIN ".$table_prefix."_users
                                        ON ".$table_prefix."_crmentity.smownerid = ".$table_prefix."_users.id and ".$table_prefix."_users.status='Active'
                                ";
		//crmv@31775
		$reportFilter = $oCustomView->getReportFilter($viewId);
		if ($reportFilter) {
			$tableNameTmp = $oCustomView->getReportFilterTableName($reportFilter,$current_user->id);
			$query .= " INNER JOIN $tableNameTmp ON $tableNameTmp.id = {$table_prefix}_crmentity.crmid";
		}
		//crmv@31775e		
		$query .= $this->getNonAdminAccessControlQuery($module,$current_user);
		$query .= "WHERE ".$table_prefix."_crmentity.deleted = 0 ".$where;
		$query = $this->listQueryNonAdminChange($query, $module);
		return $query;
	}

	/**	function used to get the list of contacts which are related to the vendor
	 *	@param int $id - vendor id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_contacts($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		global $table_prefix;
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

		$query = "SELECT case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name,".$table_prefix."_contactdetails.*, ".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.smownerid,".$table_prefix."_vendorcontactrel.vendorid,".$table_prefix."_account.accountname
			from ".$table_prefix."_contactdetails
			inner join ".$table_prefix."_contactscf on ".$table_prefix."_contactscf.contactid = ".$table_prefix."_contactdetails.contactid
			inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_contactdetails.contactid 
			inner join ".$table_prefix."_vendorcontactrel on ".$table_prefix."_vendorcontactrel.contactid=".$table_prefix."_contactdetails.contactid 
			left join ".$table_prefix."_groups on ".$table_prefix."_groups.groupid=".$table_prefix."_crmentity.smownerid 
			left join ".$table_prefix."_account on ".$table_prefix."_account.accountid = ".$table_prefix."_contactdetails.accountid 
			left join ".$table_prefix."_users on ".$table_prefix."_users.id=".$table_prefix."_crmentity.smownerid 
			where ".$table_prefix."_crmentity.deleted=0 and ".$table_prefix."_vendorcontactrel.vendorid = ".$id;
							
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 
		
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_contacts method ...");		
		return $return_value;
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
	
	/**
	 * Move the related records of the specified list of id's to the given record.
	 * @param String This module name
	 * @param Array List of Entity Id's from which related records need to be transfered 
	 * @param Integer Id of the the Record to which the related records are to be moved
	 */
	function transferRelatedRecords($module, $transferEntityIds, $entityId) {
		global $adb,$log;
		global $table_prefix;
		$log->debug("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");
		
		$rel_table_arr = Array("Products"=>$table_prefix."_products","PurchaseOrder"=>$table_prefix."_purchaseorder","Contacts"=>$table_prefix."_vendorcontactrel");
		
		$tbl_field_arr = Array($table_prefix."_products"=>"productid",$table_prefix."_vendorcontactrel"=>"contactid",$table_prefix."_purchaseorder"=>"purchaseorderid");	
		
		$entity_tbl_field_arr = Array($table_prefix."_products"=>"vendor_id",$table_prefix."_vendorcontactrel"=>"vendorid",$table_prefix."_purchaseorder"=>"vendorid");
		
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

	/** Returns a list of the associated emails
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	*/
	function get_emails($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		global $table_prefix;
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
			LEFT JOIN ".$table_prefix."_vendor ON ".$table_prefix."_vendor.vendorid = ".$table_prefix."_seactivityrel.crmid
			LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			WHERE ".$table_prefix."_crmentity.deleted = 0 AND ".$table_prefix."_activity.activitytype = 'Emails' AND ".$table_prefix."_emaildetails.email_flag <> 'DRAFT'
			AND ".$table_prefix."_vendor.vendorid = ".$id;
		//crmv@16265e	//crmv@18039e	//crmv@31263e
					
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 
		
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_emails method ...");		
		return $return_value;
	}	
	//crmv@7216
	/** Returns a list of the associated faxes
	*/
	function get_faxes($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		global $table_prefix;
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
			FROM ".$table_prefix."_activity, ".$table_prefix."_seactivityrel, ".$table_prefix."_vendor, ".$table_prefix."_users, ".$table_prefix."_crmentity
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid=".$table_prefix."_crmentity.smownerid
			WHERE ".$table_prefix."_seactivityrel.activityid = ".$table_prefix."_activity.activityid
				AND ".$table_prefix."_vendor.vendorid = ".$table_prefix."_seactivityrel.crmid
				AND ".$table_prefix."_users.id=".$table_prefix."_crmentity.smownerid
				AND ".$table_prefix."_crmentity.crmid = ".$table_prefix."_activity.activityid
				AND ".$table_prefix."_vendor.vendorid = ".$id."
				AND ".$table_prefix."_activity.activitytype='Fax'
				AND ".$table_prefix."_crmentity.deleted = 0";
		
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 
		
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_faxes method ...");		
		return $return_value;
	}		
	//crmv@7216e	
	/*
	 * Function to get the primary query part of a report 
	 * @param - $module Primary module name
	 * returns the query string formed on fetching the related data for report for primary module
	 */
	function generateReportsQuery($module){
		global $table_prefix;
	 			$moduletable = $this->table_name;
	 			$moduleindex = $this->table_index;
	 			$modulecftable = $this->tab_name[2];
	 			$modulecfindex = $this->tab_name_index[$modulecftable];
	 			//crmv@21249
	 			$query = "from $moduletable
			        inner join $modulecftable $modulecftable on $modulecftable.$modulecfindex=$moduletable.$moduleindex   
					inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=$moduletable.$moduleindex
					left join ".$table_prefix."_users ".substr($table_prefix."_users$module",0,29)." on ".substr($table_prefix."_users$module",0,29).".id = ".$table_prefix."_crmentity.smownerid
					left join ".$table_prefix."_users on ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid";
				//crmv@21249e
	            return $query;
	}
	
	/*
	 * Function to get the secondary query part of a report 
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	function generateReportsSecQuery($module,$secmodule){
		global $table_prefix;
		$query = $this->getRelationQuery($module,$secmodule,$table_prefix."_vendor","vendorid");
		//crmv@21249
		$query .=" left join ".$table_prefix."_crmentity ".$table_prefix."_crmentityVendors on ".$table_prefix."_crmentityVendors.crmid=".$table_prefix."_vendor.vendorid and ".$table_prefix."_crmentityVendors.deleted=0 
				left join ".$table_prefix."_vendorcf on ".$table_prefix."_vendorcf.vendorid = ".$table_prefix."_crmentityVendors.crmid 
				left join ".$table_prefix."_users ".$table_prefix."_usersVendors on ".$table_prefix."_usersVendors.id = ".$table_prefix."_crmentityVendors.smownerid"; 
		//crmv@21249e
		return $query;
	}

	/*
	 * Function to get the relation tables for related modules 
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */
	function setRelationTables($secmodule){
		global $table_prefix;
		$rel_tables = array (
			"Products" =>array($table_prefix."_products"=>array("vendor_id","productid"),$table_prefix."_vendor"=>"vendorid"),
			"PurchaseOrder" =>array($table_prefix."_purchaseorder"=>array("vendorid","purchaseorderid"),$table_prefix."_vendor"=>"vendorid"),
			"Contacts" =>array($table_prefix."_vendorcontactrel"=>array("vendorid","contactid"),$table_prefix."_vendor"=>"vendorid"),
		);
		return $rel_tables[$secmodule];
	}
	
	// Function to unlink all the dependent entities of the given Entity by Id
	function unlinkDependencies($module, $id) {
		global $log;
		global $table_prefix;
		//Deleting Vendor related PO.
		$po_q = 'SELECT '.$table_prefix.'_crmentity.crmid FROM '.$table_prefix.'_crmentity 
			INNER JOIN '.$table_prefix.'_purchaseorder ON '.$table_prefix.'_crmentity.crmid='.$table_prefix.'_purchaseorder.purchaseorderid 
			INNER JOIN '.$table_prefix.'_vendor ON '.$table_prefix.'_vendor.vendorid='.$table_prefix.'_purchaseorder.vendorid 
			WHERE '.$table_prefix.'_crmentity.deleted=0 AND '.$table_prefix.'_purchaseorder.vendorid=?';
		$po_res = $this->db->pquery($po_q, array($id));
		$po_ids_list = array();
		for($k=0;$k < $this->db->num_rows($po_res);$k++)
		{
			$po_id = $this->db->query_result($po_res,$k,"crmid");
			$po_ids_list[] = $po_id;
			$sql = 'UPDATE '.$table_prefix.'_crmentity SET deleted = 1 WHERE crmid = ?';
			$this->db->pquery($sql, array($po_id));
		}
		//Backup deleted Vendors related Potentials.
		$params = array($id, RB_RECORD_UPDATED, $table_prefix.'_crmentity', 'deleted', 'crmid', implode(",", $po_ids_list));
		$this->db->pquery('INSERT INTO '.$table_prefix.'_relatedlists_rb VALUES (?,?,?,?,?,?)', $params);
	
		//Backup Product-Vendor Relation
		$pro_q = 'SELECT productid FROM '.$table_prefix.'_products WHERE vendor_id=?';
		$pro_res = $this->db->pquery($pro_q, array($id));
		if ($this->db->num_rows($pro_res) > 0) {
			$pro_ids_list = array();
			for($k=0;$k < $this->db->num_rows($pro_res);$k++)
			{
				$pro_ids_list[] = $this->db->query_result($pro_res,$k,"productid");
			}
			$params = array($id, RB_RECORD_UPDATED, $table_prefix.'_products', 'vendor_id', 'productid', implode(",", $pro_ids_list));
			$this->db->pquery('INSERT INTO '.$table_prefix.'_relatedlists_rb VALUES (?,?,?,?,?,?)', $params);
		}
		//Deleting Product-Vendor Relation.
		$pro_q = 'UPDATE '.$table_prefix.'_products SET vendor_id = 0 WHERE vendor_id = ?';
		$this->db->pquery($pro_q, array($id));
	
		/*//Backup Contact-Vendor Relaton
		$con_q = 'SELECT contactid FROM vtiger_vendorcontactrel WHERE vendorid = ?';
		$con_res = $this->db->pquery($con_q, array($id));
		if ($this->db->num_rows($con_res) > 0) {
			for($k=0;$k < $this->db->num_rows($con_res);$k++)
			{
				$con_id = $this->db->query_result($con_res,$k,"contactid");
				$params = array($id, RB_RECORD_DELETED, 'vtiger_vendorcontactrel', 'vendorid', 'contactid', $con_id);
				$this->db->pquery('INSERT INTO vtiger_relatedlists_rb VALUES (?,?,?,?,?,?)', $params);
			}
		}	
		//Deleting Contact-Vendor Relaton
		$vc_sql = 'DELETE FROM vtiger_vendorcontactrel WHERE vendorid=?';
		$this->db->pquery($vc_sql, array($id));*/
		
		parent::unlinkDependencies($module, $id);
	}
	
	
	
	/** Returns a list of the associated tasks
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): danzi.tn@20140217 collegare abbastanza urgentemente il calendario ai fornitori!.
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
	 * Function to get Vendors related Task & Event which have activity type Held, Completed or Deferred.
 	 * @param  integer   $id      - accountid
 	 * returns related Task or Event record in array format
	 * danzi.tn@20140217 collegare abbastanza urgentemente il calendario ai fornitori!.
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
		return getHistory('Vendors',$query,$id);
	}
	

}
?>
