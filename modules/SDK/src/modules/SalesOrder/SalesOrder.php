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
 * $Header$
 * Description:  Defines the Account SugarBean Account entity with the necessary
 * methods and variables.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

include_once('config.php');
require_once('include/logging.php');
require_once('include/database/PearDatabase.php');
require_once('data/SugarBean.php');
require_once('include/utils/utils.php');
require_once('user_privileges/default_module_view.php');

// Account is used to store vtiger_account information.
class SalesOrder extends CRMEntity {
	var $log;
	var $db;

	var $table_name ;
	var $table_index= 'salesorderid';
	var $tab_name = Array();
	var $tab_name_index = Array();
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array();
	var $entity_table;
	
	var $billadr_table;

	var $object_name = "SalesOrder";

	var $new_schema = true;
	
	var $update_product_array = Array();

	var $column_fields = Array();

	var $sortby_fields = Array('subject','smownerid','accountname','lastname');		

	// This is used to retrieve related vtiger_fields from form posts.
	var $additional_column_fields = Array('assigned_user_name', 'smownerid', 'opportunity_id', 'case_id', 'contact_id', 'task_id', 'note_id', 'meeting_id', 'call_id', 'email_id', 'parent_name', 'member_id' );

	// This is the list of vtiger_fields that are in the lists.
	var $list_fields = Array(
				// Module Sequence Numbering
				//'Order No'=>Array('crmentity'=>'crmid'),
				'Order No'=>Array('salesorder','salesorder_no'),
				// END
				'Subject'=>Array('salesorder'=>'subject'),
				'Account Name'=>Array('account'=>'accountid'), 
				'Quote Name'=>Array('quotes'=>'quoteid'), 
				'Total'=>Array('salesorder'=>'total'),
				'Assigned To'=>Array('crmentity'=>'smownerid')
				);
	
	var $list_fields_name = Array(
				        'Order No'=>'salesorder_no',
				        'Subject'=>'subject',
				        'Account Name'=>'account_id',
				        'Quote Name'=>'quote_id',
					'Total'=>'hdnGrandTotal',
				        'Assigned To'=>'assigned_user_id'
				      );
	var $list_link_field= 'subject';

	var $search_fields = Array(
				'Order No'=>Array('salesorder'=>'salesorder_no'),
				'Subject'=>Array('salesorder'=>'subject'),
				'Account Name'=>Array('account'=>'accountid'),
				'Quote Name'=>Array('salesorder'=>'quoteid') 
				);
	
	var $search_fields_name = Array(
					'Order No'=>'salesorder_no',
				        'Subject'=>'subject',
				        'Account Name'=>'account_id',
				        'Quote Name'=>'quote_id'
				      );

	// This is the list of vtiger_fields that are required.
	var $required_fields =  array("accountname"=>1);

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'subject';
	var $default_sort_order = 'ASC';
	//var $groupTable = Array('vtiger_sogrouprelation','salesorderid');

	var $mandatory_fields = Array('subject','createdtime' ,'modifiedtime');
	//crmv@10759
	var $search_base_field = 'subject';
	//crmv@10759 e	
	/** Constructor Function for SalesOrder class
	 *  This function creates an instance of LoggerManager class using getLogger method
	 *  creates an instance for PearDatabase class and get values for column_fields array of SalesOrder class.
	 */
	function SalesOrder() {
		global $table_prefix;
		$this->table_name = $table_prefix."_salesorder";
		$this->tab_name = Array($table_prefix.'_crmentity',$table_prefix.'_salesorder',$table_prefix.'_sobillads',$table_prefix.'_soshipads',$table_prefix.'_salesordercf',$table_prefix.'_invoice_recurring_info');
		$this->tab_name_index = Array($table_prefix.'_crmentity'=>'crmid',$table_prefix.'_salesorder'=>'salesorderid',$table_prefix.'_sobillads'=>'sobilladdressid',$table_prefix.'_soshipads'=>'soshipaddressid',$table_prefix.'_salesordercf'=>'salesorderid',$table_prefix.'_invoice_recurring_info'=>'salesorderid');
		$this->customFieldTable = Array($table_prefix.'_salesordercf', 'salesorderid');
		$this->entity_table = $table_prefix."_crmentity";
		$this->billadr_table = $table_prefix."_sobillads";
		$this->log =LoggerManager::getLogger('SalesOrder');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('SalesOrder');
	}

	function save_module($module)
	{
		global $table_prefix;

		//in ajax save we should not call this function, because this will delete all the existing product values
		if($_REQUEST['action'] != 'SalesOrderAjax' && $_REQUEST['ajxaction'] != 'DETAILVIEW' && $_REQUEST['action'] != 'MassEditSave')
		{
			//Based on the total Number of rows we will save the product relationship with this entity
			saveInventoryProductDetails($this, 'SalesOrder');	
		}
		
		// Update the currency id and the conversion rate for the sales order
		$update_query = "update ".$table_prefix."_salesorder set currency_id=?, conversion_rate=? where salesorderid=?";
		$update_params = array($this->column_fields['currency_id'], $this->column_fields['conversion_rate'], $this->id); 
		$this->db->pquery($update_query, $update_params);
		
		$this->recalculateAccToServices($this->id); //crmv@16644
		$this->recalculateAccToProducts($this->id); //crmv@16644
	}
	
	//crmv@16644
	function mark_deleted($id)
	{
		CRMEntity::mark_deleted($id);
		$this->recalculateAccToServices($id);
		$this->recalculateAccToProducts($id);
	}
	
	function recalculateAccToServices($salesorderid) {
		global $table_prefix;
		$result = $this->db->query("SELECT accountid FROM ".$table_prefix."_salesorder WHERE salesorderid = $salesorderid");
		if ($result) $accountid = $this->db->query_result($result,0,'accountid');
		
		if ($accountid == '')	return;
		
		$this->db->query("DELETE FROM ".$table_prefix."_crmentityrel WHERE crmid = $accountid AND relcrmid IN (SELECT id FROM crmv_inventorytoacc WHERE accountid = $accountid AND type = 'Services') AND module = 'Accounts' AND relmodule = 'Services'");
		$result = $this->db->query("SELECT productid,id FROM ".$table_prefix."_inventoryproductrel
									INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_inventoryproductrel.productid AND ".$table_prefix."_crmentity.setype = 'Services'
									WHERE ".$table_prefix."_crmentity.deleted = 0 AND id IN (
										SELECT salesorderid FROM ".$table_prefix."_salesorder
										INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_salesorder.salesorderid
										WHERE ".$table_prefix."_crmentity.deleted = 0 AND ".$table_prefix."_salesorder.accountid = $accountid AND ".$table_prefix."_salesorder.sostatus <> 'Cancelled'
									)");
		$this->db->pquery("DELETE FROM crmv_inventorytoacc where accountid = ? and type = 'Services'",array($accountid));
		while($row=$this->db->fetchByAssoc($result)) {
			
			$result_tmp1 = $this->db->pquery("SELECT * FROM crmv_inventorytoacc WHERE accountid = ? AND sorderid = ? AND id = ?",array($accountid,$row['id'],$row['productid']));
			if ($result_tmp1 && $this->db->num_rows($result_tmp1)>0) {
				//do nothing
			}
			else $this->db->pquery("INSERT INTO crmv_inventorytoacc (accountid,sorderid,id,type) VALUES (?,?,?,?)",array($accountid,$row['id'],$row['productid'],'Services'));
			
			$result_tmp2 = $this->db->pquery("SELECT * FROM ".$table_prefix."_crmentityrel WHERE crmid = ? AND relcrmid = ?",array($accountid,$row['productid']));
			if ($result_tmp2 && $this->db->num_rows($result_tmp2)>0) {
				//do nothing
			}
			else $this->db->pquery("INSERT INTO ".$table_prefix."_crmentityrel (crmid,module,relcrmid,relmodule) VALUES (?,?,?,?)",array($accountid,'Accounts',$row['productid'],'Services'));
		}
	}

	function recalculateAccToProducts($salesorderid) {
		global $table_prefix;
		$result = $this->db->query("SELECT accountid FROM ".$table_prefix."_salesorder WHERE salesorderid = $salesorderid");
		if ($result) $accountid = $this->db->query_result($result,0,'accountid');
		
		if ($accountid == '')	return;
		
		$this->db->query("DELETE FROM ".$table_prefix."_seproductsrel WHERE crmid = $accountid AND productid IN (SELECT id FROM crmv_inventorytoacc WHERE accountid = $accountid AND type = 'Products') AND setype = 'Accounts'");
		$result = $this->db->query("SELECT productid,id FROM ".$table_prefix."_inventoryproductrel
									INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_inventoryproductrel.productid AND ".$table_prefix."_crmentity.setype = 'Products'
									WHERE ".$table_prefix."_crmentity.deleted = 0 AND id IN (
										SELECT salesorderid FROM ".$table_prefix."_salesorder
										INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_salesorder.salesorderid
										WHERE ".$table_prefix."_crmentity.deleted = 0 AND ".$table_prefix."_salesorder.accountid = $accountid AND ".$table_prefix."_salesorder.sostatus <> 'Cancelled'
									)");
		$this->db->pquery("DELETE FROM crmv_inventorytoacc where accountid = ? and type = 'Products'",array($accountid));
		while($row=$this->db->fetchByAssoc($result)) {
			
			$result_tmp1 = $this->db->pquery("SELECT * FROM crmv_inventorytoacc WHERE accountid = ? AND sorderid = ? AND id = ?",array($accountid,$row['id'],$row['productid']));
			if ($result_tmp1 && $this->db->num_rows($result_tmp1)>0) {
				//do nothing
			}
			else $this->db->pquery("INSERT INTO crmv_inventorytoacc (accountid,sorderid,id,type) VALUES (?,?,?,?)",array($accountid,$row['id'],$row['productid'],'Products'));
			
			$result_tmp = $this->db->pquery("SELECT * FROM ".$table_prefix."_seproductsrel WHERE crmid = ? AND productid = ?",array($accountid,$row['productid']));
			if ($result_tmp && $this->db->num_rows($result_tmp)>0) {
				//do nothing
			}
			else $this->db->pquery("INSERT INTO ".$table_prefix."_seproductsrel (crmid,productid,setype) VALUES (?,?,?)",array($accountid,$row['productid'],'Accounts'));
		}
	}
	//crmv@16644
	
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

	/** Function to get activities associated with the Sales Order
	 *  This function accepts the id as arguments and execute the MySQL query using the id
	 *  and sends the query and the id as arguments to renderRelatedActivities() method
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
			}
		}

		$query = "SELECT case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name,".$table_prefix."_contactdetails.lastname, ".$table_prefix."_contactdetails.firstname, ".$table_prefix."_contactdetails.contactid, ".$table_prefix."_activity.*,".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.smownerid, ".$table_prefix."_crmentity.modifiedtime
			from ".$table_prefix."_activity
			inner join ".$table_prefix."_activitycf on ".$table_prefix."_activitycf.activityid = ".$table_prefix."_activity.activityid
			inner join ".$table_prefix."_seactivityrel on ".$table_prefix."_seactivityrel.activityid=".$table_prefix."_activity.activityid 
			inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=".$table_prefix."_activity.activityid 
			left join ".$table_prefix."_cntactivityrel on ".$table_prefix."_cntactivityrel.activityid= ".$table_prefix."_activity.activityid 
			left join ".$table_prefix."_contactdetails on ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_cntactivityrel.contactid 
			left join ".$table_prefix."_users on ".$table_prefix."_users.id=".$table_prefix."_crmentity.smownerid 
			left join ".$table_prefix."_groups on ".$table_prefix."_groups.groupid=".$table_prefix."_crmentity.smownerid 
			where ".$table_prefix."_seactivityrel.crmid=".$id." and activitytype='Task' and ".$table_prefix."_crmentity.deleted=0";
//crmv@8398 		
		$query.=getCalendarSql();
//crmv@8398e		
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset); 
		
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_activities method ...");		
		return $return_value;
	}

	/** Function to get the activities history associated with the Sales Order
	 *  This function accepts the id as arguments and execute the MySQL query using the id
	 *  and sends the query and the id as arguments to renderRelatedHistory() method
	 */
	function get_history($id)
	{
		global $log,$table_prefix;
		$log->debug("Entering get_history(".$id.") method ...");
		$query = "SELECT ".$table_prefix."_contactdetails.lastname, ".$table_prefix."_contactdetails.firstname, ".$table_prefix."_contactdetails.contactid,".$table_prefix."_activity.*,  ".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.smownerid, ".$table_prefix."_crmentity.modifiedtime,	".$table_prefix."_crmentity.createdtime, ".$table_prefix."_crmentity.description, case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name
			from ".$table_prefix."_activity
			inner join ".$table_prefix."_activitycf on ".$table_prefix."_activitycf.activityid = ".$table_prefix."_activity.activityid	
			inner join ".$table_prefix."_seactivityrel on ".$table_prefix."_seactivityrel.activityid=".$table_prefix."_activity.activityid
				inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=".$table_prefix."_activity.activityid
				left join ".$table_prefix."_cntactivityrel on ".$table_prefix."_cntactivityrel.activityid= ".$table_prefix."_activity.activityid
				left join ".$table_prefix."_contactdetails on ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_cntactivityrel.contactid
                                left join ".$table_prefix."_groups on ".$table_prefix."_groups.groupid=".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_users on ".$table_prefix."_users.id=".$table_prefix."_crmentity.smownerid
			where ".$table_prefix."_seactivityrel.crmid=".$id."
                                and ".$table_prefix."_crmentity.deleted = 0";
		//Don't add order by, because, for security, one more condition will be added with this query in include/RelatedListView.php
//crmv@8398 			
		$query.=getCalendarSql('history');
//crmv@8398e	 
		$log->debug("Exiting get_history method ...");
		return getHistory('SalesOrder',$query,$id);
	}



	/** Function to get the invoices associated with the Sales Order
	 *  This function accepts the id as arguments and execute the MySQL query using the id
	 *  and sends the query and the id as arguments to renderRelatedInvoices() method.
	 */
	function get_invoices($id)
	{
		global $log,$singlepane_view,$table_prefix;	
		$log->debug("Entering get_invoices(".$id.") method ...");
		require_once('modules/Invoice/Invoice.php');

		$focus = CRMEntity::getInstance('Invoice');
	
		$button = '';
		if($singlepane_view == 'true')
			$returnset = '&return_module=SalesOrder&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module=SalesOrder&return_action=CallRelatedList&return_id='.$id;

			$query = "select ".$table_prefix."_crmentity.*, ".$table_prefix."_invoice.*, ".$table_prefix."_account.accountname, ".$table_prefix."_salesorder.subject as salessubject, case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name
				from ".$table_prefix."_invoice 
				inner join ".$table_prefix."_invoicecf on ".$table_prefix."_invoicecf.invoiceid = ".$table_prefix."_invoice.invoiceid
				inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=".$table_prefix."_invoice.invoiceid 
				left outer join ".$table_prefix."_account on ".$table_prefix."_account.accountid=".$table_prefix."_invoice.accountid 
				inner join ".$table_prefix."_salesorder on ".$table_prefix."_salesorder.salesorderid=".$table_prefix."_invoice.salesorderid 
				left join ".$table_prefix."_users on ".$table_prefix."_users.id=".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_groups on ".$table_prefix."_groups.groupid=".$table_prefix."_crmentity.smownerid 
				where ".$table_prefix."_crmentity.deleted=0 and ".$table_prefix."_salesorder.salesorderid=".$id;

		$log->debug("Exiting get_invoices method ...");
		return GetRelatedList('SalesOrder','Invoice',$focus,$query,$button,$returnset);
	
	}

	/**	Function used to get the Status history of the Sales Order
	 *	@param $id - salesorder id
	 *	@return $return_data - array with header and the entries in format Array('header'=>$header,'entries'=>$entries_list) where as $header and $entries_list are arrays which contains header values and all column values of all entries
	 */
	function get_sostatushistory($id)
	{	
		global $log,$table_prefix;
		$log->debug("Entering get_sostatushistory(".$id.") method ...");

		global $adb;
		global $mod_strings;
		global $app_strings;

		$query = 'select '.$table_prefix.'_sostatushistory.*, '.$table_prefix.'_salesorder.salesorder_no from '.$table_prefix.'_sostatushistory inner join '.$table_prefix.'_salesorder on '.$table_prefix.'_salesorder.salesorderid = '.$table_prefix.'_sostatushistory.salesorderid inner join '.$table_prefix.'_crmentity on '.$table_prefix.'_crmentity.crmid = '.$table_prefix.'_salesorder.salesorderid where '.$table_prefix.'_crmentity.deleted = 0 and '.$table_prefix.'_salesorder.salesorderid = ?';
		$result=$adb->pquery($query, array($id));
		$noofrows = $adb->num_rows($result);

		$header[] = $app_strings['Order No'];
		$header[] = $app_strings['LBL_ACCOUNT_NAME'];
		$header[] = $app_strings['LBL_AMOUNT'];
		$header[] = $app_strings['LBL_SO_STATUS'];
		$header[] = $app_strings['LBL_LAST_MODIFIED'];

		//Getting the field permission for the current user. 1 - Not Accessible, 0 - Accessible
		//Account Name , Total are mandatory fields. So no need to do security check to these fields.
		global $current_user;

		//If field is accessible then getFieldVisibilityPermission function will return 0 else return 1
		$sostatus_access = (getFieldVisibilityPermission('SalesOrder', $current_user->id, 'sostatus') != '0')? 1 : 0;
		$picklistarray = getAccessPickListValues('SalesOrder');

		$sostatus_array = ($sostatus_access != 1)? $picklistarray['sostatus']: array();
		//- ==> picklist field is not permitted in profile
		//Not Accessible - picklist is permitted in profile but picklist value is not permitted
		$error_msg = ($sostatus_access != 1)? 'Not Accessible': '-';

		while($row = $adb->fetch_array($result))
		{
			$entries = Array();

			// Module Sequence Numbering			
			//$entries[] = $row['salesorderid'];
			$entries[] = $row['salesorder_no'];
			// END
			$entries[] = $row['accountname'];
			$entries[] = $row['total'];
			$entries[] = (in_array($row['sostatus'], $sostatus_array))? getTranslatedString($row['sostatus'],'SalesOrder'): $error_msg;
			$entries[] = getDisplayDate($row['lastmodified']);

			$entries_list[] = $entries;
		}

		$return_data = Array('header'=>$header,'entries'=>$entries_list,'count'=>$noofrows);	//crmv@25809

	 	$log->debug("Exiting get_sostatushistory method ...");

		return $return_data;
	}

	/*
	 * Function to get the secondary query part of a report 
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	function generateReportsSecQuery($module,$secmodule, $reporttype){
		global $table_prefix;
		if ($reporttype != 'COLUMNSTOTOTAL') {
			$productjoins = " left join ".$table_prefix."_inventoryproductrel ".substr($table_prefix.'_inventoryproductrelSalesOrder',0,29)." on ".$table_prefix."_salesorder.salesorderid = ".substr($table_prefix.'_inventoryproductrelSalesOrder',0,29).".id
							left join ".$table_prefix."_products ".$table_prefix."_productsSalesOrder on ".$table_prefix."_productsSalesOrder.productid = ".substr($table_prefix.'_inventoryproductrelSalesOrder',0,29).".productid
							left join ".$table_prefix."_service ".$table_prefix."_serviceSalesOrder on ".$table_prefix."_serviceSalesOrder.serviceid = ".substr($table_prefix.'_inventoryproductrelSalesOrder',0,29).".productid ";
		}
		$query = $this->getRelationQuery($module,$secmodule,$table_prefix."_salesorder","salesorderid");
		$query .= " left join ".$table_prefix."_crmentity ".$table_prefix."_crmentitySalesOrder on ".$table_prefix."_crmentitySalesOrder.crmid=".$table_prefix."_salesorder.salesorderid and ".$table_prefix."_crmentitySalesOrder.deleted=0
			left join ".$table_prefix."_salesordercf on ".$table_prefix."_salesorder.salesorderid = ".$table_prefix."_salesordercf.salesorderid 
			left join ".$table_prefix."_sobillads on ".$table_prefix."_salesorder.salesorderid=".$table_prefix."_sobillads.sobilladdressid
			left join ".$table_prefix."_soshipads on ".$table_prefix."_salesorder.salesorderid=".$table_prefix."_soshipads.soshipaddressid
			$productjoins
			left join ".$table_prefix."_groups ".$table_prefix."_groupsSalesOrder on ".$table_prefix."_groupsSalesOrder.groupid = ".$table_prefix."_crmentitySalesOrder.smownerid
			left join ".$table_prefix."_users ".$table_prefix."_usersSalesOrder on ".$table_prefix."_usersSalesOrder.id = ".$table_prefix."_crmentitySalesOrder.smownerid
			left join ".$table_prefix."_potential ".$table_prefix."_potentialRelSalesOrder on ".$table_prefix."_potentialRelSalesOrder.potentialid = ".$table_prefix."_salesorder.potentialid
			left join ".$table_prefix."_contactdetails ".substr($table_prefix.'_contactdetailsSalesOrder',0,29)." on ".$table_prefix."_salesorder.contactid = ".substr($table_prefix.'_contactdetailsSalesOrder',0,29).".contactid
			left join ".$table_prefix."_invoice_recurring_info on ".$table_prefix."_salesorder.salesorderid = ".$table_prefix."_invoice_recurring_info.salesorderid
			left join ".$table_prefix."_quotes ".$table_prefix."_quotesSalesOrder on ".$table_prefix."_salesorder.quoteid = ".$table_prefix."_quotesSalesOrder.quoteid
			left join ".$table_prefix."_account ".$table_prefix."_accountSalesOrder on ".$table_prefix."_accountSalesOrder.accountid = ".$table_prefix."_salesorder.accountid ";

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
			"Calendar" =>array($table_prefix."_seactivityrel"=>array("crmid","activityid"),$table_prefix."_salesorder"=>"salesorderid"),
			"Invoice" =>array($table_prefix."_invoice"=>array("salesorderid","invoiceid"),$table_prefix."_salesorder"=>"salesorderid"),
			"Documents" => array($table_prefix."_senotesrel"=>array("crmid","notesid"),$table_prefix."_salesorder"=>"salesorderid"),
		);
		return $rel_tables[$secmodule];
	}
	
	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log,$table_prefix;
		if(empty($return_module) || empty($return_id)) return;
		
		if($return_module == 'Accounts') {
			$this->trash('SalesOrder',$id);
		}
		elseif($return_module == 'Quotes') {
			$relation_query = 'UPDATE '.$table_prefix.'_salesorder SET quoteid=0 WHERE salesorderid=?';
			$this->db->pquery($relation_query, array($id));
		}
		elseif($return_module == 'Potentials') {
			$relation_query = 'UPDATE '.$table_prefix.'_salesorder SET potentialid=0 WHERE salesorderid=?';
			$this->db->pquery($relation_query, array($id));
		}
		elseif($return_module == 'Contacts') {
			$relation_query = 'UPDATE '.$table_prefix.'_salesorder SET contactid=0 WHERE salesorderid=?';
			$this->db->pquery($relation_query, array($id));
		} else {
			$sql = 'DELETE FROM '.$table_prefix.'_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
			$params = array($id, $return_module, $return_id, $id, $return_module, $return_id);
			$this->db->pquery($sql, $params);
		}
	}
	public function getJoinClause($tableName) {
		if ($tableName == $table_prefix.'_invoice_recurring_info') {
			return 'LEFT JOIN';
		}
		return parent::getJoinClause($tableName);
	}	
}

?>