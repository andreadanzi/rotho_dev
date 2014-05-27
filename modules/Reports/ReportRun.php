<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
global $calpath;
global $app_strings,$mod_strings;
global $theme;
global $log;
global $table_prefix;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";
require_once('include/database/PearDatabase.php');
require_once('data/CRMEntity.php');
require_once("modules/Reports/Reports.php");
require_once('modules/Reports/ReportUtils.php'); //crmv@21198

class ReportRun extends CRMEntity
{

	var $primarymodule;
	var $secondarymodule;
	var $orderbylistsql;
	var $orderbylistcolumns;

	var $selectcolumns;
	var $groupbylist;
	var $reporttype;
	var $reportname;
	var $totallist;

	var $_groupinglist  = false;
	var $_columnslist    = false;
	var	$_stdfilterlist = false;
	var	$_columnstotallist = false;
	var	$_columnssumlist = false; // crmv@29686
	var	$_advfiltersql = false;

	var $convert_currency = array('Potentials_Amount', 'Accounts_Annual_Revenue', 'Leads_Annual_Revenue', 'Campaigns_Budget_Cost',
									'Campaigns_Actual_Cost', 'Campaigns_Expected_Revenue', 'Campaigns_Actual_ROI', 'Campaigns_Expected_ROI');
	//var $add_currency_sym_in_headers = array('Amount', 'Unit_Price', 'Total', 'Sub_Total', 'S&H_Amount', 'Discount_Amount', 'Adjustment');
	var $append_currency_symbol_to_value = array('Products_Unit_Price','Services_Price',
						'Invoice_Total', 'Invoice_Sub_Total', 'Invoice_S&H_Amount', 'Invoice_Discount_Amount', 'Invoice_Adjustment',
						'Quotes_Total', 'Quotes_Sub_Total', 'Quotes_S&H_Amount', 'Quotes_Discount_Amount', 'Quotes_Adjustment',
						'SalesOrder_Total', 'SalesOrder_Sub_Total', 'SalesOrder_S&H_Amount', 'SalesOrder_Discount_Amount', 'SalesOrder_Adjustment',
						'PurchaseOrder_Total', 'PurchaseOrder_Sub_Total', 'PurchaseOrder_S&H_Amount', 'PurchaseOrder_Discount_Amount', 'PurchaseOrder_Adjustment'
						);
	var $ui10_fields = array();
	var $multipicklist_fields = array();	//crmv@21249
	var $picklist_fields = array();	//crmv@20630
	var $report_module;	//crmv@31775

	/** Function to set reportid,primarymodule,secondarymodule,reporttype,reportname, for given reportid
	 *  This function accepts the $reportid as argument
	 *  It sets reportid,primarymodule,secondarymodule,reporttype,reportname for the given reportid
	 */
	function ReportRun($reportid)
	{
		$oReport = new Reports($reportid);
		$this->reportid = $reportid;
		$this->primarymodule = $oReport->primodule;
		$this->secondarymodule = $oReport->secmodule;
		$this->reporttype = $oReport->reporttype;
		$this->reportname = $oReport->reportname;
	}

	/** Function to get the columns for the reportid
	 *  This function accepts the $reportid and $outputformat (optional)
	 *  This function returns  $columnslist Array($tablename:$columnname:$fieldlabel:$fieldname:$typeofdata=>$tablename.$columnname As Header value,
	 *					      $tablename1:$columnname1:$fieldlabel1:$fieldname1:$typeofdata1=>$tablename1.$columnname1 As Header value,
	 *					      					|
 	 *					      $tablenamen:$columnnamen:$fieldlabeln:$fieldnamen:$typeofdatan=>$tablenamen.$columnnamen As Header value
	 *				      	     )
	 *
	 */
	function getQueryColumnsList($reportid,$outputformat='')
	{
		global $table_prefix;
		// Have we initialized information already?
		if($this->_columnslist !== false) {
			return $this->_columnslist;
		}
		
		//crmv@31775
		if ($outputformat == 'CV_RPRT') {
			$modcl = CRMEntity::getInstance($this->report_module);
			$columnslist[$modcl->table_name.':'.$modcl->table_index.":id:".$modcl->table_index.':I'] = $modcl->table_name.'.'.$modcl->table_index.' AS "id"';
			$this->_columnslist = $columnslist;
			return $columnslist;
		}
		//crmv@31775e

		//crmv@29686
		if($outputformat == 'COUNT' || $outputformat== 'NAV' || $outputformat == 'COUNTXLS' || $outputformat == 'XLS'){
			$outputformat = 'HTML';
		}
		//crmv@29686e

		global $adb;
		global $modules;
		global $log,$current_user,$current_language;
		$ssql = "select ".$table_prefix."_selectcolumn.* from ".$table_prefix."_report inner join ".$table_prefix."_selectquery on ".$table_prefix."_selectquery.queryid = ".$table_prefix."_report.queryid";
		$ssql .= " left join ".$table_prefix."_selectcolumn on ".$table_prefix."_selectcolumn.queryid = ".$table_prefix."_selectquery.queryid";
		$ssql .= " where ".$table_prefix."_report.reportid = ?";
		$ssql .= " order by ".$table_prefix."_selectcolumn.columnindex";
		$result = $adb->pquery($ssql, array($reportid));
		$permitted_fields = Array();

		while($columnslistrow = $adb->fetch_array($result))
		{
			$fieldname ="";
			$fieldcolname = $columnslistrow["columnname"];
			list($tablename,$colname,$module_field,$fieldname,$single) = explode(":",$fieldcolname);
			list($module,$field) = explode("_",$module_field);
			$inventory_fields = array('quantity','listprice','serviceid','productid','discount','comment');
			$inventory_modules = array('SalesOrder','Quotes','PurchaseOrder','Invoice');
			require('user_privileges/user_privileges_'.$current_user->id.'.php');
			if(sizeof($permitted_fields) == 0 && $is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1)
			{
				list($module,$field) = explode("_",$module_field);
				$permitted_fields = $this->getaccesfield($module);
			}
			if(in_array($module,$inventory_modules)){
				$permitted_fields = array_merge($permitted_fields,$inventory_fields);
			}
			$selectedfields = explode(":",$fieldcolname);
			$querycolumns = $this->getEscapedColumns($selectedfields);

			$mod_strings = return_module_language($current_language,$module);
			$fieldlabel = trim(str_replace($module," ",$selectedfields[2]));
			$mod_arr=explode('_',$fieldlabel);
			$mod = ($mod_arr[0] == '')?$module:$mod_arr[0];
			$fieldlabel = trim(str_replace("_"," ",$fieldlabel));
			//modified code to support i18n issue
			$fld_arr = explode(" ",$fieldlabel);
			$mod_lbl = getTranslatedString($fld_arr[0],$module); //module
			array_shift($fld_arr);
			$fld_lbl_str = implode(" ",$fld_arr);
			$fld_lbl = getTranslatedString($fld_lbl_str,$module); //fieldlabel
			$fieldlabel = $mod_lbl." ".$fld_lbl;
			if((CheckFieldPermission($fieldname,$mod) != 'true' && $colname!="crmid" && (!in_array($fieldname,$inventory_fields) && in_array($module,$inventory_modules))) || empty($fieldname))
			{
				continue;
			}
			else
			{
				$header_label = $selectedfields[2]; // Header label to be displayed in the reports table
				// To check if the field in the report is a custom field
				// and if yes, get the label of this custom field freshly from the vtiger_field as it would have been changed.
				// Asha - Reference ticket : #4906
				//crmv@fix names > 30 chars
				$header_label = $selectedfields[2] = substr($selectedfields[2],0,29);
				if($querycolumns == "")
				{
					if($selectedfields[4] == 'C')
					{
						$field_label_data = explode("_",$selectedfields[2]);
						$module= $field_label_data[0];
						if($module!=$this->primarymodule)
							$columnslist[$fieldcolname] = "case when (".$selectedfields[0].".".$selectedfields[1]."='1')then 'yes' else case when (".substr($table_prefix."_crmentity$module",0,29).".crmid is not null ) then 'no' else '-' end end as \"$selectedfields[2]\" ";
						else
							$columnslist[$fieldcolname] = "case when (".$selectedfields[0].".".$selectedfields[1]."='1')then 'yes' else case when ({$table_prefix}_crmentity.crmid is not null ) then 'no' else '-' end end as \"$selectedfields[2]\" ";
					}
					elseif($selectedfields[0] == $table_prefix.'_activity' && ($selectedfields[1] == 'status' || $selectedfields[1] == 'eventstatus'))	//crmv@21249
					{
						$columnslist[$fieldcolname] = " case when (".$table_prefix."_activity.status is not null) then ".$table_prefix."_activity.status else ".$table_prefix."_activity.eventstatus end as \"Calendar_Status\" ";
					}
					elseif($selectedfields[0] == $table_prefix.'_activity' && $selectedfields[1] == 'date_start')
					{
						$columnslist[$fieldcolname] = " ".$adb->sql_concat(Array($table_prefix.'_activity.date_start',"' '",$table_prefix.'_activity.time_start'))." as \"Calendar_Start_Date_and_Time\" ";
					}
					elseif(stristr($selectedfields[0],$table_prefix."_users") && ($selectedfields[1] == 'user_name') && $module_field != 'Products_Handler' && $module_field!='Services_Owner')
					{
						$temp_module_from_tablename = str_replace($table_prefix."_users","",$selectedfields[0]);
						if($module!=$this->primarymodule){
							$condition = "and ".substr($table_prefix."_crmentity$module",0,29).".crmid is not null ";
						} else {
							$condition = "and ".$table_prefix."_crmentity.crmid is not null ";
						}
						if($temp_module_from_tablename == $module)
							$columnslist[$fieldcolname] = " case when (".$selectedfields[0].".user_name is not null $condition) then ".$selectedfields[0].".user_name else ".$table_prefix."_groups".$module.".groupname end as \"".$module."_Assigned_To\" ";
						else//Some Fields can't assigned to groups so case avoided (fields like inventory manager)
							$columnslist[$fieldcolname] = $selectedfields[0].".user_name as \"".$header_label."\"";

					}
					elseif(stristr($selectedfields[0],$table_prefix."_users") && ($selectedfields[1] == 'user_name') && $module_field == 'Products_Handler')//Products cannot be assiged to group only to handler so group is not included
					{
						$columnslist[$fieldcolname] = $selectedfields[0].".user_name as \"".$module."_Handler\" ";
					}
					elseif($selectedfields[0] == $table_prefix."_crmentity".$this->primarymodule)
					{
						$columnslist[$fieldcolname] = substr($table_prefix."_crmentity.".$selectedfields[1],0,29)." AS \"".$header_label."\"";
					}
				    elseif($selectedfields[0] == $table_prefix.'_invoice' && $selectedfields[1] == 'salesorderid')//handled for salesorder fields in Invoice Module Reports
					{
						$columnslist[$fieldcolname] = $table_prefix."_salesorderInvoice.subject	AS \"".$selectedfields[2]."\"";
					}
					elseif($selectedfields[0] == $table_prefix.'_campaign' && $selectedfields[1] == 'product_id')//handled for product fields in Campaigns Module Reports
					{
						$columnslist[$fieldcolname] = $table_prefix."_productsCampaigns.productname AS \"".$header_label."\"";
					}
					elseif($selectedfields[0] == $table_prefix.'_products' && $selectedfields[1] == 'unit_price')//handled for product fields in Campaigns Module Reports
					{
					$columnslist[$fieldcolname] = $adb->sql_concat(Array($selectedfields[0].".currency_id","'::'",'innerProduct.actual_unit_price'))." as \"". $header_label ."\"";
					}
					elseif(in_array($selectedfields[2], $this->append_currency_symbol_to_value)) {
						$columnslist[$fieldcolname] = $adb->sql_concat(Array($selectedfields[0].".currency_id","'::'",$selectedfields[0].".".$selectedfields[1]))." as \"" . $header_label ."\"";
					}
					elseif($selectedfields[0] == $table_prefix.'_notes' && ($selectedfields[1] == 'filelocationtype' || $selectedfields[1] == 'filesize' || $selectedfields[1] == 'folderid' || $selectedfields[1]=='filestatus'))//handled for product fields in Campaigns Module Reports
					{
						if($selectedfields[1] == 'filelocationtype'){
							$columnslist[$fieldcolname] = "case ".$selectedfields[0].".".$selectedfields[1]." when 'I' then 'Internal' when 'E' then 'External' else '-' end as \"$selectedfields[2]\"";
						} else if($selectedfields[1] == 'folderid'){
							$columnslist[$fieldcolname] = $table_prefix."_crmentityfolder.foldername as \"$selectedfields[2]\""; // crmv@30967
						} elseif($selectedfields[1] == 'filestatus'){
							$columnslist[$fieldcolname] = "case ".$selectedfields[0].".".$selectedfields[1]." when '1' then 'yes' when '0' then 'no' else '-' end as \"$selectedfields[2]\"";
						} elseif($selectedfields[1] == 'filesize'){
							//crmv@18541
							$columnslist[$fieldcolname] = "case ".$selectedfields[0].".".$selectedfields[1]." when '' then '-' else ".$adb->sql_concat(Array($selectedfields[0].".".$selectedfields[1]."/1024","'  '","'KB'"))." end as \"$selectedfields[2]\"";
							//crmv@18541 end
						}
					}
					elseif($selectedfields[0] == $table_prefix.'_inventoryproductrel')//handled for product fields in Campaigns Module Reports
					{
						if($selectedfields[1] == 'discount'){
							$columnslist[$fieldcolname] = " case when (".substr($table_prefix.'_inventoryproductrel'.$module,0,29).".discount_amount is not null ) then ".substr($table_prefix.'_inventoryproductrel'.$module,0,29).".discount_amount else ROUND((".substr($table_prefix.'_inventoryproductrel'.$module,0,29).".listprice * ".substr($table_prefix.'_inventoryproductrel'.$module,0,29).".quantity * (".substr($table_prefix.'_inventoryproductrel'.$module,0,29).".discount_percent/100)),3) end as \"" . $header_label ."\"";
						} else if($selectedfields[1] == 'productid'){
							$columnslist[$fieldcolname] = $table_prefix."_products{$module}.productname as \"" . $header_label ."\"";
						} else if($selectedfields[1] == 'serviceid'){
							$columnslist[$fieldcolname] = $table_prefix."_service{$module}.servicename as \"" . $header_label ."\"";
						} else {
							$columnslist[$fieldcolname] = substr($selectedfields[0].$module,0,29).".".$selectedfields[1]." as \"".$header_label."\"";
						}
					}
					elseif(stristr($selectedfields[1],'cf_')==true && stripos($selectedfields[1],'cf_')==0)
					{
						$columnslist[$fieldcolname] = $selectedfields[0].".".$selectedfields[1]." AS \"".$adb->sql_escape_string(decode_html($header_label))."\"";
					}
					else
					{
						$columnslist[$fieldcolname] = substr($selectedfields[0],0,29).".".$selectedfields[1]." AS \"".$header_label."\"";
					}
				}
				else
				{
					$columnslist[$fieldcolname] = $querycolumns;
				}
			}
		}
		//crmv@17001
		if (in_array($outputformat,array("HTML","PDF","PRINT"))) { //crmv@29686
			if ($outputformat != 'PDF')
				$columnslist[$table_prefix.'_crmentity:crmid:LBL_ACTION:crmid:I'] = $table_prefix.'_crmentity.crmid AS "LBL_ACTION"' ;
			//crmv@29686
			// aggiungo crmid per ogni modulo
			$modlist = array($this->primarymodule);
			if ($this->secondarymodule) $modlist = array_merge($modlist, explode(':', $this->secondarymodule));
			foreach ($modlist as $mod) {
				$modcl = CRMEntity::getInstance($mod);
				if ($modcl) {
					$columnslist[$modcl->table_name.':'.$modcl->table_index.":HIDDEN_{$mod}_crmid:".$modcl->table_index.':I'] = $modcl->table_name.'.'.$modcl->table_index.' AS "HIDDEN_'.$mod.'_crmid"';
					//crmv@31775@TODO
					/*
					if ($mod != $this->primarymodule) {
						$moduleInstance = Vtiger_Module::getInstance($mod);
						$columnslist["vt_tmp_u{$current_user->id}_t{$moduleInstance->id}{$mod}:id:{$mod}Perm:id:I"] = "COALESCE(vt_tmp_u{$current_user->id}_t{$moduleInstance->id}{$mod}.id,0) AS \"{$mod}Perm\"";
					}
					*/
					//crmv@31775@TODOe
				}
			}
			//crmv@29686e
		}
		//crmv@17001e

		// Save the information
		$this->_columnslist = $columnslist;
		$log->info("ReportRun :: Successfully returned getQueryColumnsList".$reportid);
		return $columnslist;
	}

	/** Function to get field columns based on profile
	 *  @ param $module : Type string
	 *  returns permitted fields in array format
	 */
	function getaccesfield($module)
	{
		global $current_user;
		global $adb,$table_prefix;
		$access_fields = Array();

		$profileList = getCurrentUserProfileList();
		$query = "select ".$table_prefix."_field.fieldname from ".$table_prefix."_field inner join ".$table_prefix."_def_org_field on ".$table_prefix."_def_org_field.fieldid=".$table_prefix."_field.fieldid where";
		$params = array();
		if($module == "Calendar")
		{
				$query .= " ".$table_prefix."_field.tabid in (9,16) and ".$table_prefix."_field.displaytype in (1,2,3)  and ".$table_prefix."_def_org_field.visible=0";
		}
		else
		{
			$query .= " ".$table_prefix."_field.tabid in (select tabid from ".$table_prefix."_tab where ".$table_prefix."_tab.name in (?,?)) and ".$table_prefix."_field.displaytype in (1,2,3) and ".$table_prefix."_def_org_field.visible=0";
			array_push($params, $this->primarymodule, $this->secondarymodule);
		}
		$query.=" AND EXISTS(SELECT * FROM ".$table_prefix."_profile2field WHERE ".$table_prefix."_profile2field.fieldid = ".$table_prefix."_field.fieldid ";
		if (count($profileList) > 0) {
			 $query.=" AND ".$table_prefix."_profile2field.profileid IN (". generateQuestionMarks($profileList) .") ";
			 array_push($params, $profileList);
		}
		$query.=" and ".$table_prefix."_profile2field.visible=0 )";
		$query.=" order by block,sequence";
		$result = $adb->pquery($query, $params);

		while($collistrow = $adb->fetch_array($result))
		{
			$access_fields[] = $collistrow["fieldname"];
		}
		//added to include ticketid for Reports module in select columnlist for all users
		//sk@2
    	if($module == "HelpDesk"){
			$access_fields[] = "ticketid";
			$access_fields[] = "Internal_Project_Nummer";
			$access_fields[] = "External_Project_Nummer";
		}
		//sk@2e
		return $access_fields;
	}

	/** Function to get Escapedcolumns for the field in case of multiple parents
	 *  @ param $selectedfields : Type Array
	 *  returns the case query for the escaped columns
	 */
	function getEscapedColumns($selectedfields)
	{
		global $current_user,$adb,$table_prefix;
		$fieldname = $selectedfields[3];
		$tmp = explode("_",$selectedfields[2]);
		$module = $tmp[0];

		if($fieldname == "parent_id" && ($module == "HelpDesk" || $module == "Calendar"))
		{
			if($module == "HelpDesk" && $selectedfields[0] == $table_prefix."_crmentityRelHelpDesk")
			{
				$querycolumn = "case ".$table_prefix."_crmentityRelHelpDesk.setype when 'Accounts' then ".$table_prefix."_accountRelHelpDesk.accountname when 'Contacts' then ".$adb->sql_concat(Array(substr($table_prefix.'_contactdetailsRelHelpDesk',0,29).'.lastname',"' '",substr($table_prefix.'_contactdetailsRelHelpDesk',0,29).'.firstname'))." End"." '".$selectedfields[2]."', ".$table_prefix."_crmentityRelHelpDesk.setype 'Entity_type'";
				return $querycolumn;
			}
			if($module == "Calendar") {
				$querycolumn = "case ".$table_prefix."_crmentityRelCalendar.setype when 'Accounts' then ".$table_prefix."_accountRelCalendar.accountname when 'Leads' then ".$adb->sql_concat(Array($table_prefix.'_leaddetailsRelCalendar.lastname',"' '",$table_prefix.'_leaddetailsRelCalendar.firstname'))." when 'Potentials' then ".$table_prefix."_potentialRelCalendar.potentialname when 'Quotes' then ".$table_prefix."_quotesRelCalendar.subject when 'PurchaseOrder' then ".substr($table_prefix.'_purchaseorderRelCalendar',0,29).".subject when 'Invoice' then ".$table_prefix."_invoiceRelCalendar.subject when 'SalesOrder' then ".$table_prefix."_salesorderRelCalendar.subject when 'HelpDesk' then ".substr($table_prefix.'_troubleticketsRelCalendar',0,29).".title when 'Campaigns' then ".$table_prefix."_campaignRelCalendar.campaignname End as \"".$selectedfields[2]."\", ".$table_prefix."_crmentityRelCalendar.setype as \"Entity_type\"";	//crmv@21249
			}
		} elseif($fieldname == "contact_id" && strpos($selectedfields[2],"Contact_Name")) {
			if(($this->primarymodule == 'PurchaseOrder' || $this->primarymodule == 'SalesOrder' || $this->primarymodule == 'Quotes' || $this->primarymodule == 'Invoice' || $this->primarymodule == 'Calendar') && $module==$this->primarymodule) {
				if (getFieldVisibilityPermission("Contacts", $current_user->id, "firstname") == '0')
					$querycolumn = " case when ".$table_prefix."_crmentity.crmid is not null  then ".$adb->sql_concat(Array(substr($table_prefix.'_contactdetails'.$this->primarymodule,0,29).".lastname","' '",substr($table_prefix.'_contactdetails'.$this->primarymodule,0,29).".firstname"))." else '-' end as \"".$selectedfields[2]."\"";
				else
					$querycolumn = " case when ".$table_prefix."_crmentity.crmid is not null  then ".substr($table_prefix.'_contactdetails'.$this->primarymodule,0,29).".lastname else '-' end as \"".$selectedfields[2]."\"";	//crmv@21249
			}
			if(stristr($this->secondarymodule,$module) && ($module== 'Quotes' || $module== 'SalesOrder' || $module== 'PurchaseOrder' ||$module== 'Calendar' || $module == 'Invoice')) {
				if (getFieldVisibilityPermission("Contacts", $current_user->id, "firstname") == '0')
					$querycolumn = " case when ".$table_prefix."_crmentity".$module.".crmid is not null  then ".$adb->sql_concat(Array(substr($table_prefix."_contactdetails".$module,0,29).".lastname","' '",substr($table_prefix."_contactdetails".$module,0,29).".firstname"))." else '-' end as \"".$selectedfields[2]."\"";
				else
					$querycolumn = " case when ".$table_prefix."_crmentity".$module.".crmid is not null  then ".$table_prefix."_contactdetails".$module.".lastname else '-' end as \"".$selectedfields[2]."\"";
			}
		}
		else{
 			if(stristr($selectedfields[0],$table_prefix."_crmentityRel")){
 				$module = str_replace($table_prefix."_crmentityRel","",$selectedfields[0]);
				$fields_query = $adb->pquery("SELECT ".$table_prefix."_field.fieldname,".$table_prefix."_field.tablename,".$table_prefix."_field.fieldid from ".$table_prefix."_field INNER JOIN ".$table_prefix."_tab on ".$table_prefix."_tab.name = ? WHERE ".$table_prefix."_tab.tabid=".$table_prefix."_field.tabid and ".$table_prefix."_field.fieldname=?",array($module,$selectedfields[3]));

		        if($adb->num_rows($fields_query)>0){
			        for($i=0;$i<$adb->num_rows($fields_query);$i++){
			        	$field_name = $selectedfields[3];
			        	$field_id = $adb->query_result($fields_query,$i,'fieldid');
				        $tab_name = $selectedfields[1];
				        $ui10_modules_query = $adb->pquery("SELECT relmodule FROM ".$table_prefix."_fieldmodulerel WHERE fieldid=?",array($field_id));

				       if($adb->num_rows($ui10_modules_query)>0){
				       	//crmv@16312
				       		$name_crmentityrel = substr($table_prefix."_crmentityRel".$module[0]."$field_id",0,29);	//crmv@16818
				       		$name_link = substr("Rel$module"."_via_field_".$field_id,0,29);
					        $querycolumn = " case $name_crmentityrel.setype";
					        for($j=0;$j<$adb->num_rows($ui10_modules_query);$j++){
					        	$rel_mod = $adb->query_result($ui10_modules_query,$j,'relmodule');
					        	$rel_obj = CRMEntity::getInstance($rel_mod);
					        	vtlib_setup_modulevars($rel_mod, $rel_obj);

								$rel_tab_name = $rel_obj->table_name;
								$link_field = $rel_tab_name."Rel".$module.".".$rel_obj->list_link_field;

								if($rel_mod=="Contacts" || $rel_mod=="Leads"){
									if(getFieldVisibilityPermission($rel_mod,$current_user->id,'firstname')==0){
										$link_field = $adb->sql_concat(Array($link_field,' ',$rel_tab_name.$name_link."firstname"));
									}
								}
								$querycolumn.= " when '$rel_mod' then $link_field ";
					        }
					        $querycolumn .= "end as \"".$selectedfields[2]."\", $name_crmentityrel.setype as \"Entity_type\"" ;
					   //crmv@16312 end
				       }
			        }
		        }

			}
			if($fieldname == 'creator'){
				$querycolumn .= "case when (".$table_prefix."_usersModComments.user_name is not null and ".$table_prefix."_crmentity.crmid is not null ) then ".$table_prefix."_usersModComments.user_name end as \"ModComments_Creator\"";
			}
		}
		return $querycolumn;
	}

	/** Function to get selectedcolumns for the given reportid
	 *  @ param $reportid : Type Integer
	 *  returns the query of columnlist for the selected columns
	 */
	function getSelectedColumnsList($reportid)
	{

		global $adb;
		global $modules;
		global $log;
		global $table_prefix;

		$ssql = "select ".$table_prefix."_selectcolumn.* from ".$table_prefix."_report inner join ".$table_prefix."_selectquery on ".$table_prefix."_selectquery.queryid = ".$table_prefix."_report.queryid";
		$ssql .= " left join ".$table_prefix."_selectcolumn on ".$table_prefix."_selectcolumn.queryid = ".$table_prefix."_selectquery.queryid where ".$table_prefix."_report.reportid = ? ";
		$ssql .= " order by ".$table_prefix."_selectcolumn.columnindex";

		$result = $adb->pquery($ssql, array($reportid));
		$noofrows = $adb->num_rows($result);

		if ($this->orderbylistsql != "")
		{
			$sSQL .= $this->orderbylistsql.", ";
		}

		for($i=0; $i<$noofrows; $i++)
		{
			$fieldcolname = $adb->query_result($result,$i,"columnname");
			$ordercolumnsequal = true;
			if($fieldcolname != "")
			{
				for($j=0;$j<count($this->orderbylistcolumns);$j++)
				{
					if($this->orderbylistcolumns[$j] == $fieldcolname)
					{
						$ordercolumnsequal = false;
						break;
					}else
					{
						$ordercolumnsequal = true;
					}
				}
				if($ordercolumnsequal)
				{
					$selectedfields = explode(":",$fieldcolname);
					if($selectedfields[0] == $table_prefix."_crmentity".$this->primarymodule)
						$selectedfields[0] = $table_prefix."_crmentity";
					$sSQLList[] = $selectedfields[0].".".$selectedfields[1]." '".$selectedfields[2]."'";
				}
			}
		}
		$sSQL .= implode(",",$sSQLList);

		$log->info("ReportRun :: Successfully returned getSelectedColumnsList".$reportid);
		return $sSQL;
	}

	/** Function to get advanced comparator in query form for the given Comparator and value
	 *  @ param $comparator : Type String
	 *  @ param $value : Type String
	 *  returns the check query for the comparator
	 */
	function getAdvComparator($comparator,$value,$datatype="")
	{

		global $log,$adb,$default_charset,$ogReport;
		$value=html_entity_decode(trim($value),ENT_QUOTES,$default_charset);
		$value_len = strlen($value);
		$is_field = false;
		if($value[0]=='$' && $value[$value_len-1]=='$'){
			$temp = str_replace('$','',$value);
			$is_field = true;
		}
		if($datatype=='C'){
			$value = str_replace("yes","1",str_replace("no","0",$value));
		}

		if($is_field==true){
			$value = $this->getFilterComparedField($temp);
		}
		if($comparator == "e")
		{
			if(trim($value) == "NULL")
			{
				$rtvalue = " is NULL";
			}elseif(trim($value) != "")
			{
				$rtvalue = " = ".$adb->quote($value);
			}elseif(trim($value) == "" && $datatype == "V")
			{
				$rtvalue = " = ".$adb->quote($value);
			}else
			{
				$rtvalue = " = ''"; //crmv@33466
			}
		}
		if($comparator == "n")
		{
			if(trim($value) == "NULL")
			{
				$rtvalue = " is NOT NULL";
			}elseif(trim($value) != "")
			{
				$rtvalue = " <> ".$adb->quote($value);
			}elseif(trim($value) == "" && $datatype == "V")
			{
				$rtvalue = " <> ".$adb->quote($value);
			}else
			{
				$rtvalue = " <> ''"; //crmv@33466
			}
		}
		if($comparator == "s")
		{
			$rtvalue = " like '". formatForSqlLike($value, 2,$is_field) ."'";
		}
		if($comparator == "ew")
		{
			$rtvalue = " like '". formatForSqlLike($value, 1,$is_field) ."'";
		}
		if($comparator == "c")
		{
			$rtvalue = " like '". formatForSqlLike($value,0,$is_field) ."'";
		}
		if($comparator == "k")
		{
			$rtvalue = " not like '". formatForSqlLike($value,0,$is_field) ."'";
		}
		if($comparator == "l")
		{
			$rtvalue = " < ".$adb->quote($value);
		}
		if($comparator == "g")
		{
			$rtvalue = " > ".$adb->quote($value);
		}
		if($comparator == "m")
		{
			$rtvalue = " <= ".$adb->quote($value);
		}
		if($comparator == "h")
		{
			$rtvalue = " >= ".$adb->quote($value);
		}
		if($comparator == "b") {
			$rtvalue = " < ".$adb->quote($value);
		}
		if($comparator == "a") {
			$rtvalue = " > ".$adb->quote($value);
		}
		if($is_field==true){
			$rtvalue = str_replace("'","",$rtvalue);
			$rtvalue = str_replace("\\","",$rtvalue);
		}
		$log->info("ReportRun :: Successfully returned getAdvComparator");
		return $rtvalue;
	}

	/** Function to get field that is to be compared in query form for the given Comparator and field
	 *  @ param $field : field
	 *  returns the value for the comparator
	 */
	function getFilterComparedField($field){
		global $adb,$ogReport,$table_prefix;
			$field = explode('#',$field);
			$module = $field[0];
			$fieldname = trim($field[1]);
			$tabid = getTabId($module);
			$field_query = $adb->pquery("SELECT tablename,columnname,typeofdata,fieldname,uitype FROM ".$table_prefix."_field WHERE tabid = ? AND fieldname= ?",array($tabid,$fieldname));
			$fieldtablename = $adb->query_result($field_query,0,'tablename');
			$fieldcolname = $adb->query_result($field_query,0,'columnname');
			$typeofdata = $adb->query_result($field_query,0,'typeofdata');
			$fieldtypeofdata=ChangeTypeOfData_Filter($fieldtablename,$fieldcolname,$typeofdata[0]);
			$uitype = $adb->query_result($field_query,0,'uitype');
			/*if($tr[0]==$ogReport->primodule)
				$value = $adb->query_result($field_query,0,'tablename').".".$adb->query_result($field_query,0,'columnname');
			else
				$value = $adb->query_result($field_query,0,'tablename').$tr[0].".".$adb->query_result($field_query,0,'columnname');
			*/
			if($uitype == 68 || $uitype == 59)
			{
				$fieldtypeofdata = 'V';
			}
			if($fieldtablename == $table_prefix."_crmentity")
			{
				$fieldtablename = $fieldtablename.$module;
			}
			if($fieldname == "assigned_user_id")
			{
				$fieldtablename = $table_prefix."_users".$module;
				$fieldcolname = "user_name";
			}
			if($fieldname == "account_id")
			{
				$fieldtablename = $table_prefix."_account".$module;
				$fieldcolname = "accountname";
			}
			if($fieldname == "contact_id")
			{
				$fieldtablename = $table_prefix."_contactdetails".$module;
				$fieldcolname = "lastname";
			}
			if($fieldname == "parent_id")
			{
				$fieldtablename = $table_prefix."_crmentityRel".$module;
				$fieldcolname = "setype";
			}
			if($fieldname == "vendor_id")
			{
				$fieldtablename = $table_prefix."_vendorRel".$module;
				$fieldcolname = "vendorname";
			}
			if($fieldname == "potential_id")
			{
				$fieldtablename = $table_prefix."_potentialRel".$module;
				$fieldcolname = "potentialname";
			}
			if($fieldname == "assigned_user_id1")
			{
				$fieldtablename = $table_prefix."_usersRel1";
				$fieldcolname = "user_name";
			}
			if($fieldname == 'quote_id')
			{
				$fieldtablename = $table_prefix."_quotes".$module;
				$fieldcolname = "subject";
			}
			if($fieldname == 'product_id' && $fieldtablename == $table_prefix.'_troubletickets')
			{
				$fieldtablename = $table_prefix."_productsRel";
				$fieldcolname = "productname";
			}
			if($fieldname == 'product_id' && $fieldtablename == $table_prefix.'_campaign')
			{
				$fieldtablename = $table_prefix."_productsCampaigns";
				$fieldcolname = "productname";
			}
			if($fieldname == 'product_id' && $fieldtablename == $table_prefix.'_products')
			{
				$fieldtablename = $table_prefix."_productsProducts";
				$fieldcolname = "productname";
			}
			if($fieldname == 'campaignid' && $module=='Potentials')
			{
				$fieldtablename = $table_prefix."_campaign".$module;
				$fieldcolname = "campaignname";
			}
			$value = $fieldtablename.".".$fieldcolname;
		return $value;
	}
	/** Function to get the advanced filter columns for the reportid
	 *  This function accepts the $reportid
	 *  This function returns  $columnslist Array($columnname => $tablename:$columnname:$fieldlabel:$fieldname:$typeofdata=>$tablename.$columnname filtercriteria,
	 *					      $tablename1:$columnname1:$fieldlabel1:$fieldname1:$typeofdata1=>$tablename1.$columnname1 filtercriteria,
	 *					      					|
 	 *					      $tablenamen:$columnnamen:$fieldlabeln:$fieldnamen:$typeofdatan=>$tablenamen.$columnnamen filtercriteria
	 *				      	     )
	 *
	 */


	function getAdvFilterSql($reportid)
	{
		// Have we initialized information already?
		if($this->_advfiltersql !== false) {
			return $this->_advfiltersql;
		}

		global $adb;
		global $modules;
		global $log;
		global $table_prefix;
		$advfiltersql = "";

		$advfiltergroupssql = "SELECT * FROM ".$table_prefix."_relcriteria_grouping WHERE queryid = ? ORDER BY groupid";
		$advfiltergroups = $adb->pquery($advfiltergroupssql, array($reportid));
		$numgrouprows = $adb->num_rows($advfiltergroups);
		$groupctr =0;
		while($advfiltergroup = $adb->fetch_array($advfiltergroups)) {
			$groupctr++;
			$groupid = $advfiltergroup["groupid"];
			$groupcondition = $advfiltergroup["group_condition"];

			$advfiltercolumnssql =  "select ".$table_prefix."_relcriteria.* from ".$table_prefix."_report";
			$advfiltercolumnssql .= " inner join ".$table_prefix."_selectquery on ".$table_prefix."_selectquery.queryid = ".$table_prefix."_report.queryid";
			$advfiltercolumnssql .= " left join ".$table_prefix."_relcriteria on ".$table_prefix."_relcriteria.queryid = ".$table_prefix."_selectquery.queryid";
			$advfiltercolumnssql .= " where ".$table_prefix."_report.reportid = ? AND ".$table_prefix."_relcriteria.groupid = ?";
			$advfiltercolumnssql .= " order by ".$table_prefix."_relcriteria.columnindex";

			$result = $adb->pquery($advfiltercolumnssql, array($reportid, $groupid));
			$noofrows = $adb->num_rows($result);

			if($noofrows > 0) {

				$advfiltergroupsql = "";
				$columnctr = 0;
				while($advfilterrow = $adb->fetch_array($result)) {
					$columnctr++;
					$fieldcolname = $advfilterrow["columnname"];
					$comparator = $advfilterrow["comparator"];
					$value = $advfilterrow["value"];
					$columncondition = $advfilterrow["column_condition"];

					if($fieldcolname != "" && $comparator != "") {
						//crmv@23716 resetto l'array dei valori poich� andando avanti se li porta dietro!!!
						$advcolsql = Array();
						//crmv@23716 end
						$selectedfields = explode(":",$fieldcolname);

						//crmv@21198
						$moduleFieldLabel = $selectedfields[2];
						list($moduleName, $fieldLabel) = explode('_', $moduleFieldLabel, 2);
						$fieldInfo = getFieldByReportLabel($moduleName, $fieldLabel);
						//crmv@21198e

						//Added to handle yes or no for checkbox  field in reports advance filters. -shahul
						if($selectedfields[4] == 'C') {
							if(strcasecmp(trim($value),"yes")==0)
								$value="1";
							if(strcasecmp(trim($value),"no")==0)
								$value="0";
						}
						//crmv@20889
						if (in_array($selectedfields[1],$this->multipicklist_fields) && !empty($value)) {
							if(is_string($value)) {
								$mp_valueArray = explode(',' , $value);
								foreach($mp_valueArray as $mp_key => $mp_val) {
									$mp_valueArray[$mp_key] = trim($mp_val);
								}
							} elseif(is_array($value)) {
								$mp_valueArray = $value;
							}else{
								$mp_valueArray = array($value);
							}
							$value = Picklistmulti::get_search_values($selectedfields[1],$mp_valueArray,$comparator);
							$value = implode(',',$value[0]);
						}
						//crmv@20889e
						//crmv@20630
						if (in_array($selectedfields[1],$this->picklist_fields) && !empty($value)) {
							if(is_string($value)) {
								$value = explode(",",trim($value));
							}
							global $current_user;
							$queryGenerator = QueryGenerator::getInstance($moduleName, $current_user); // crmv@31205
							foreach ($value as $val){
								$val_trans = $queryGenerator->getReverseTranslate($val,$comparator);
								if ($val_trans != $val)
									$value[] = $val_trans;
							}
							$value = implode(',',$value);
						}
						//crmv@20630e
						$valuearray = explode(",",trim($value));
						$datatype = (isset($selectedfields[4])) ? $selectedfields[4] : "";
						if(isset($valuearray) && count($valuearray) > 1 && $comparator != 'bw') {

							$advcolumnsql = "";
							for($n=0;$n<count($valuearray);$n++) {

		                		if($selectedfields[0] == $table_prefix.'_crmentityRelHelpDesk' && $selectedfields[1]=='setype') {
									$advcolsql[] = "(case ".$table_prefix."_crmentityRelHelpDesk.setype when 'Accounts' then ".$table_prefix."_accountRelHelpDesk.accountname else ".$adb->sql_concat(Array(substr($table_prefix.'_contactdetailsRelHelpDesk',0,29).'.lastname',"' '",substr($table_prefix.'_contactdetailsRelHelpDesk',0,29).'.firstname'))." end) ". $this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype); // crmv@30925
		                        } elseif($selectedfields[0] == $table_prefix.'_crmentityRelCalendar' && $selectedfields[1]=='setype') {
									$advcolsql[] = "(case ".$table_prefix."_crmentityRelHelpDesk.setype when 'Accounts' then ".$table_prefix."_accountRelHelpDesk.accountname else ".$adb->sql_concat(Array(substr($table_prefix.'_contactdetailsRelHelpDesk',0,29).'.lastname',"' '",substr($table_prefix.'_contactdetailsRelHelpDesk',0,29).'.firstname'))." end) ". $this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype);
		                        } elseif(($selectedfields[0] == $table_prefix."_users".$this->primarymodule || $selectedfields[0] == $table_prefix."_users".$this->secondarymodule) && $selectedfields[1] == 'user_name') {
									$module_from_tablename = str_replace($table_prefix."_users","",$selectedfields[0]);
									if($this->primarymodule == 'Products') {
										$advcolsql[] = ($selectedfields[0].".user_name ".$this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype));
									} else {
										$advcolsql[] = " ".$selectedfields[0].".user_name".$this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype)." or ".$table_prefix."_groups".$module_from_tablename.".groupname ".$this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype);
									}
								} elseif($selectedfields[1] == 'status') {//when you use comma seperated values.
									if($selectedfields[2] == 'Calendar_Status')
									$advcolsql[] = "(case when (".$table_prefix."_activity.status is not null) then ".$table_prefix."_activity.status else ".$table_prefix."_activity.eventstatus end)".$this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype);
									elseif($selectedfields[2] == 'HelpDesk_Status')
									$advcolsql[] = $table_prefix."_troubletickets.status".$this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype);
								} elseif($selectedfields[1] == 'description') {//when you use comma seperated values.
									if($selectedfields[0]==$table_prefix.'_crmentity'.$this->primarymodule)
										$advcolsql[] = $table_prefix."_crmentity.description".$this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype);
									else
										$advcolsql[] = $selectedfields[0].".".$selectedfields[1].$this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype);
								} else {
									$advcolsql[] = $selectedfields[0].".".$selectedfields[1].$this->getAdvComparator($comparator,trim($valuearray[$n]),$datatype);
								}
							}
							//If negative logic filter ('not equal to', 'does not contain') is used, 'and' condition should be applied instead of 'or'
							if($comparator == 'n' || $comparator == 'k')
								$advcolumnsql = implode(" and ",$advcolsql);
							else
								$advcolumnsql = implode(" or ",$advcolsql);
							$fieldvalue = " (".$advcolumnsql.") ";
						} elseif(($selectedfields[0] == $table_prefix."_users".$this->primarymodule || $selectedfields[0] == $table_prefix."_users".$this->secondarymodule) && $selectedfields[1] == 'user_name') {
							$module_from_tablename = str_replace($table_prefix."_users","",$selectedfields[0]);
							if($this->primarymodule == 'Products') {
								$fieldvalue = ($selectedfields[0].".user_name ".$this->getAdvComparator($comparator,trim($value),$datatype));
							} else {
								$fieldvalue = " (".$selectedfields[0].".user_name ".$this->getAdvComparator($comparator,trim($value),$datatype)." OR ".$table_prefix."_groups".$module_from_tablename.".groupname ".$this->getAdvComparator($comparator,trim($value),$datatype).") ";	//crmv@21249
							}
						} elseif($selectedfields[0] == $table_prefix."_crmentity".$this->primarymodule) {
							$fieldvalue = $table_prefix."_crmentity.".$selectedfields[1]." ".$this->getAdvComparator($comparator,trim($value),$datatype);
						} elseif($selectedfields[0] == $table_prefix.'_crmentityRelHelpDesk' && $selectedfields[1]=='setype') {
							$fieldvalue = "(".$table_prefix."_accountRelHelpDesk.accountname ".$this->getAdvComparator($comparator,trim($value),$datatype)." or ".substr($table_prefix.'_contactdetailsRelHelpDesk',0,29).".lastname ".$this->getAdvComparator($comparator,trim($value),$datatype)." or ".substr($table_prefix.'_contactdetailsRelHelpDesk',0,29).".firstname ".$this->getAdvComparator($comparator,trim($value),$datatype).")";
						} elseif($selectedfields[0] == $table_prefix.'_crmentityRelCalendar' && $selectedfields[1]=='setype') {
							$fieldvalue = "(".$table_prefix."_accountRelCalendar.accountname ".$this->getAdvComparator($comparator,trim($value),$datatype)." or ".$adb->sql_concat(Array($table_prefix.'_leaddetailsRelCalendar.lastname',"' '",$table_prefix.'_leaddetailsRelCalendar.firstname'))." ".$this->getAdvComparator($comparator,trim($value),$datatype)." or ".$table_prefix."_potentialRelCalendar.potentialname ".$this->getAdvComparator($comparator,trim($value),$datatype)." or ".$table_prefix."_invoiceRelCalendar.subject ".$this->getAdvComparator($comparator,trim($value),$datatype)." or ".$table_prefix."_quotesRelCalendar.subject ".$this->getAdvComparator($comparator,trim($value),$datatype)." or ".substr($table_prefix.'_purchaseorderRelCalendar',0,29).".subject ".$this->getAdvComparator($comparator,trim($value),$datatype)." or ".$table_prefix."_salesorderRelCalendar.subject ".$this->getAdvComparator($comparator,trim($value),$datatype)." or ".substr($table_prefix.'_troubleticketsRelCalendar',0,29).".title ".$this->getAdvComparator($comparator,trim($value),$datatype)." or ".$table_prefix."_campaignRelCalendar.campaignname ".$this->getAdvComparator($comparator,trim($value),$datatype).")";
						//crmv@21249
						} elseif($selectedfields[0] == $table_prefix."_activity" && ($selectedfields[1] == 'status' || $selectedfields[1] == 'eventstatus')) {
							$fieldvalue = " (".$selectedfields[0].".status ".$this->getAdvComparator($comparator,trim($value),$datatype)." OR ".$selectedfields[0].".eventstatus ".$this->getAdvComparator($comparator,trim($value),$datatype).") ";
						//crmv@21249e
						} elseif($selectedfields[3] == "contact_id" && strpos($selectedfields[2],"Contact_Name")) {
							if($this->primarymodule == 'PurchaseOrder' || $this->primarymodule == 'SalesOrder' || $this->primarymodule == 'Quotes' || $this->primarymodule == 'Invoice' || $this->primarymodule == 'Calendar')
								$fieldvalue = $adb->sql_concat(Array($table_prefix.'_contactdetails'.$this->primarymodule.'.lastname',"' '",$table_prefix.'_contactdetails'.$this->primarymodule.'.firstname')).$this->getAdvComparator($comparator,trim($value),$datatype);
							if($this->secondarymodule == 'Quotes' || $this->secondarymodule == 'Invoice')
								$fieldvalue = $adb->sql_concat(Array($table_prefix.'_contactdetails'.$this->secondarymodule.'.lastname',"' '",$table_prefix.'_contactdetails'.$this->secondarymodule.'.firstname')).$this->getAdvComparator($comparator,trim($value),$datatype);
						} elseif($comparator == 'e' && (trim($value) == "NULL" || trim($value) == '')) {
							$fieldvalue = "(".$selectedfields[0].".".$selectedfields[1]." IS NULL OR ".$selectedfields[0].".".$selectedfields[1]." = '')";
						} elseif($comparator == 'bw' && count($valuearray) == 2) {
							$fieldvalue = "(".$selectedfields[0].".".$selectedfields[1]." between '".trim($valuearray[0])."' and '".trim($valuearray[1])."')";
						//crmv@21198
						} elseif($fieldInfo['uitype'] == '10' || $fieldInfo['is_reference'] === true) {
							$comparatorValue = $this->getAdvComparator($comparator,trim($value),$datatype);
							$fieldSqls = array();
							$fieldSqlColumns = $this->getReferenceFieldColumnList($moduleName, $fieldInfo);
							foreach($fieldSqlColumns as $columnSql) {
								$fieldSqls[] = $columnSql.$comparatorValue;
							}
							$fieldvalue = ' ('. implode(' OR ', $fieldSqls).') ';
						//crmv@21198e
						} else {
							$fieldvalue = $selectedfields[0].".".$selectedfields[1].$this->getAdvComparator($comparator,trim($value),$datatype);
						}

						$advfiltergroupsql .= $fieldvalue;
						if($columncondition != NULL && $columncondition != '' && $noofrows > $columnctr ) {
							$advfiltergroupsql .= ' '.$columncondition.' ';
						}
					}

				}

				if (trim($advfiltergroupsql) != "") {
					$advfiltergroupsql =  "( $advfiltergroupsql ) ";
					if($groupcondition != NULL && $groupcondition != '' && $numgrouprows > $groupctr) {
						$advfiltergroupsql .= ' '. $groupcondition . ' ';
					}

					$advfiltersql .= $advfiltergroupsql;
				}
			}
		}
		if (trim($advfiltersql) != "") $advfiltersql = '('.$advfiltersql.')';
		// Save the information
		$this->_advfiltersql = $advfiltersql;

		$log->info("ReportRun :: Successfully returned getAdvFilterSql".$reportid);
		return $advfiltersql;
	}

	/** Function to get the Standard filter columns for the reportid
	 *  This function accepts the $reportid datatype Integer
	 *  This function returns  $stdfilterlist Array($columnname => $tablename:$columnname:$fieldlabel:$fieldname:$typeofdata=>$tablename.$columnname filtercriteria,
	 *					      $tablename1:$columnname1:$fieldlabel1:$fieldname1:$typeofdata1=>$tablename1.$columnname1 filtercriteria,
	 *				      	     )
	 *
	 */
	function getStdFilterList($reportid)
	{
		// Have we initialized information already?
		if($this->_stdfilterlist !== false) {
			return $this->_stdfilterlist;
		}

		global $adb;
		global $modules;
		global $log;
		global $table_prefix;

		$stdfiltersql = "select ".$table_prefix."_reportdatefilter.* from ".$table_prefix."_report";
		$stdfiltersql .= " inner join ".$table_prefix."_reportdatefilter on ".$table_prefix."_report.reportid = ".$table_prefix."_reportdatefilter.datefilterid";
		$stdfiltersql .= " where ".$table_prefix."_report.reportid = ?";

		$result = $adb->pquery($stdfiltersql, array($reportid));
		$stdfilterrow = $adb->fetch_array($result);
		if(isset($stdfilterrow))
		{
			$fieldcolname = $stdfilterrow["datecolumnname"];
			$datefilter = $stdfilterrow["datefilter"];
			$startdate = $stdfilterrow["startdate"];
			$enddate = $stdfilterrow["enddate"];

			if($fieldcolname != "none")
			{
				$selectedfields = explode(":",$fieldcolname);
				if($selectedfields[0] == $table_prefix."_crmentity".$this->primarymodule)
					$selectedfields[0] = $table_prefix."_crmentity";
				if($datefilter == "custom")
				{
					//crmv@fix date
					if($startdate != null && $enddate != null && $startdate != "0000-00-00" && $enddate != "0000-00-00" && $startdate != "0000-00-00 00:00:00" && $enddate != "0000-00-00 00:00:00" && $selectedfields[0] != "" && $selectedfields[1] != "")
					{
						$stdfilterlist[$fieldcolname] = $selectedfields[0].".".$selectedfields[1]." between '".$startdate." 00:00:00' and '".$enddate." 23:59:59'";
					}
					//crmv@fix date end
				}else
				{
					$startenddate = $this->getStandarFiltersStartAndEndDate($datefilter);
					if($startenddate[0] != "" && $startenddate[1] != "" && $selectedfields[0] != "" && $selectedfields[1] != "")
					{
						$stdfilterlist[$fieldcolname] = $selectedfields[0].".".$selectedfields[1]." between '".$startenddate[0]." 00:00:00' and '".$startenddate[1]." 23:59:59'";
					}
				}

			}
		}
		// Save the information
		$this->_stdfilterlist = $stdfilterlist;

		$log->info("ReportRun :: Successfully returned getStdFilterList".$reportid);
		return $stdfilterlist;
	}

	/** Function to get the RunTime filter columns for the given $filtercolumn,$filter,$startdate,$enddate
	 *  @ param $filtercolumn : Type String
	 *  @ param $filter : Type String
	 *  @ param $startdate: Type String
	 *  @ param $enddate : Type String
	 *  This function returns  $stdfilterlist Array($columnname => $tablename:$columnname:$fieldlabel=>$tablename.$columnname 'between' $startdate 'and' $enddate)
	 *
	 */
	function RunTimeFilter($filtercolumn,$filter,$startdate,$enddate)
	{
		if($filtercolumn != "none")
		{
			$selectedfields = explode(":",$filtercolumn);
			if($selectedfields[0] == $table_prefix."_crmentity".$this->primarymodule)
				$selectedfields[0] = $table_prefix."_crmentity";
			if($filter == "custom")
			{
				if($startdate != "" && $enddate != "" && $selectedfields[0] != "" && $selectedfields[1] != "")
				{
					$stdfilterlist[$filtercolumn] = $selectedfields[0].".".$selectedfields[1]." between '".$startdate." 00:00:00' and '".$enddate." 23:59:00'";
				}
			}else
			{
				if($startdate != "" && $enddate != "")
				{
					$startenddate = $this->getStandarFiltersStartAndEndDate($filter);
					if($startenddate[0] != "" && $startenddate[1] != "" && $selectedfields[0] != "" && $selectedfields[1] != "")
					{
						$stdfilterlist[$filtercolumn] = $selectedfields[0].".".$selectedfields[1]." between '".$startenddate[0]." 00:00:00' and '".$startenddate[1]." 23:59:00'";
					}
				}
			}

		}
		return $stdfilterlist;

	}

	/** Function to get standardfilter for the given reportid
	 *  @ param $reportid : Type Integer
	 *  returns the query of columnlist for the selected columns
	 */

	function getStandardCriterialSql($reportid)
	{
		global $adb;
		global $modules;
		global $log;
		global $table_prefix;
		$sreportstdfiltersql = "select ".$table_prefix."_reportdatefilter.* from ".$table_prefix."_report";
		$sreportstdfiltersql .= " inner join ".$table_prefix."_reportdatefilter on ".$table_prefix."_report.reportid = ".$table_prefix."_reportdatefilter.datefilterid";
		$sreportstdfiltersql .= " where ".$table_prefix."_report.reportid = ?";

		$result = $adb->pquery($sreportstdfiltersql, array($reportid));
		$noofrows = $adb->num_rows($result);

		for($i=0; $i<$noofrows; $i++)
		{
			$fieldcolname = $adb->query_result($result,$i,"datecolumnname");
			$datefilter = $adb->query_result($result,$i,"datefilter");
			$startdate = $adb->query_result($result,$i,"startdate");
			$enddate = $adb->query_result($result,$i,"enddate");

			if($fieldcolname != "none")
			{
				$selectedfields = explode(":",$fieldcolname);
				if($selectedfields[0] == $table_prefix."_crmentity".$this->primarymodule)
					$selectedfields[0] = $table_prefix."_crmentity";
				if($datefilter == "custom")
				{
					if($startdate != "0000-00-00" && $enddate != "0000-00-00" && $selectedfields[0] != "" && $selectedfields[1] != "")
					{
						$sSQL .= $selectedfields[0].".".$selectedfields[1]." between '".$startdate."' and '".$enddate."'";
					}
				}else
				{
					$startenddate = $this->getStandarFiltersStartAndEndDate($datefilter);
					if($startenddate[0] != "" && $startenddate[1] != "" && $selectedfields[0] != "" && $selectedfields[1] != "")
					{
						$sSQL .= $selectedfields[0].".".$selectedfields[1]." between '".$startenddate[0]."' and '".$startenddate[1]."'";
					}
				}
			}
		}
		$log->info("ReportRun :: Successfully returned getStandardCriterialSql".$reportid);
		return $sSQL;
	}

	/** Function to get standardfilter startdate and enddate for the given type
	 *  @ param $type : Type String
	 *  returns the $datevalue Array in the given format
	 * 		$datevalue = Array(0=>$startdate,1=>$enddate)
	 */


	function getStandarFiltersStartAndEndDate($type)
	{
		$today = date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
		$tomorrow  = date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d")+1, date("Y")));
		$yesterday  = date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d")-1, date("Y")));

		$currentmonth0 = date("Y-m-d",mktime(0, 0, 0, date("m"), "01",   date("Y")));
		$currentmonth1 = date("Y-m-t");
		$lastmonth0 = date("Y-m-d",mktime(0, 0, 0, date("m")-1, "01",   date("Y")));
		$lastmonth1 = date("Y-m-t", strtotime("-1 Month"));
		$nextmonth0 = date("Y-m-d",mktime(0, 0, 0, date("m")+1, "01",   date("Y")));
		$nextmonth1 = date("Y-m-t", strtotime("+1 Month"));

		$lastweek0 = date("Y-m-d",strtotime("-2 week Sunday"));
		$lastweek1 = date("Y-m-d",strtotime("-1 week Saturday"));

		$thisweek0 = date("Y-m-d",strtotime("-1 week Sunday"));
		$thisweek1 = date("Y-m-d",strtotime("this Saturday"));

		$nextweek0 = date("Y-m-d",strtotime("this Sunday"));
		$nextweek1 = date("Y-m-d",strtotime("+1 week Saturday"));

		$next7days = date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d")+6, date("Y")));
		$next30days = date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d")+29, date("Y")));
		$next60days = date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d")+59, date("Y")));
		$next90days = date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d")+89, date("Y")));
		$next120days = date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d")+119, date("Y")));

		$last7days = date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d")-6, date("Y")));
		$last30days = date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d")-29, date("Y")));
		$last60days = date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d")-59, date("Y")));
		$last90days = date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d")-89, date("Y")));
		$last120days = date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d")-119, date("Y")));

		$currentFY0 = date("Y-m-d",mktime(0, 0, 0, "01", "01",   date("Y")));
		$currentFY1 = date("Y-m-t",mktime(0, 0, 0, "12", date("d"),   date("Y")));
		$lastFY0 = date("Y-m-d",mktime(0, 0, 0, "01", "01",   date("Y")-1));
		$lastFY1 = date("Y-m-t", mktime(0, 0, 0, "12", date("d"), date("Y")-1));
		$nextFY0 = date("Y-m-d",mktime(0, 0, 0, "01", "01",   date("Y")+1));
		$nextFY1 = date("Y-m-t", mktime(0, 0, 0, "12", date("d"), date("Y")+1));

		if(date("m") <= 3)
		{
			$cFq = date("Y-m-d",mktime(0, 0, 0, "01","01",date("Y")));
			$cFq1 = date("Y-m-d",mktime(0, 0, 0, "03","31",date("Y")));
			$nFq = date("Y-m-d",mktime(0, 0, 0, "04","01",date("Y")));
			$nFq1 = date("Y-m-d",mktime(0, 0, 0, "06","30",date("Y")));
			$pFq = date("Y-m-d",mktime(0, 0, 0, "10","01",date("Y")-1));
			$pFq1 = date("Y-m-d",mktime(0, 0, 0, "12","31",date("Y")-1));
		}else if(date("m") > 3 and date("m") <= 6)
		{
			$pFq = date("Y-m-d",mktime(0, 0, 0, "01","01",date("Y")));
			$pFq1 = date("Y-m-d",mktime(0, 0, 0, "03","31",date("Y")));
			$cFq = date("Y-m-d",mktime(0, 0, 0, "04","01",date("Y")));
			$cFq1 = date("Y-m-d",mktime(0, 0, 0, "06","30",date("Y")));
			$nFq = date("Y-m-d",mktime(0, 0, 0, "07","01",date("Y")));
			$nFq1 = date("Y-m-d",mktime(0, 0, 0, "09","30",date("Y")));

		}else if(date("m") > 6 and date("m") <= 9)
		{
			$nFq = date("Y-m-d",mktime(0, 0, 0, "10","01",date("Y")));
			$nFq1 = date("Y-m-d",mktime(0, 0, 0, "12","31",date("Y")));
			$pFq = date("Y-m-d",mktime(0, 0, 0, "04","01",date("Y")));
			$pFq1 = date("Y-m-d",mktime(0, 0, 0, "06","30",date("Y")));
			$cFq = date("Y-m-d",mktime(0, 0, 0, "07","01",date("Y")));
			$cFq1 = date("Y-m-d",mktime(0, 0, 0, "09","30",date("Y")));
		}
		else if(date("m") > 9 and date("m") <= 12)
		{
			$nFq = date("Y-m-d",mktime(0, 0, 0, "01","01",date("Y")+1));
			$nFq1 = date("Y-m-d",mktime(0, 0, 0, "03","31",date("Y")+1));
			$pFq = date("Y-m-d",mktime(0, 0, 0, "07","01",date("Y")));
			$pFq1 = date("Y-m-d",mktime(0, 0, 0, "09","30",date("Y")));
			$cFq = date("Y-m-d",mktime(0, 0, 0, "10","01",date("Y")));
			$cFq1 = date("Y-m-d",mktime(0, 0, 0, "12","31",date("Y")));

		}

		if($type == "today" )
		{

			$datevalue[0] = $today;
			$datevalue[1] = $today;
		}
		elseif($type == "yesterday" )
		{

			$datevalue[0] = $yesterday;
			$datevalue[1] = $yesterday;
		}
		elseif($type == "tomorrow" )
		{

			$datevalue[0] = $tomorrow;
			$datevalue[1] = $tomorrow;
		}
		elseif($type == "thisweek" )
		{

			$datevalue[0] = $thisweek0;
			$datevalue[1] = $thisweek1;
		}
		elseif($type == "lastweek" )
		{

			$datevalue[0] = $lastweek0;
			$datevalue[1] = $lastweek1;
		}
		elseif($type == "nextweek" )
		{

			$datevalue[0] = $nextweek0;
			$datevalue[1] = $nextweek1;
		}
		elseif($type == "thismonth" )
		{

			$datevalue[0] =$currentmonth0;
			$datevalue[1] = $currentmonth1;
		}

		elseif($type == "lastmonth" )
		{

			$datevalue[0] = $lastmonth0;
			$datevalue[1] = $lastmonth1;
		}
		elseif($type == "nextmonth" )
		{

			$datevalue[0] = $nextmonth0;
			$datevalue[1] = $nextmonth1;
		}
		elseif($type == "next7days" )
		{

			$datevalue[0] = $today;
			$datevalue[1] = $next7days;
		}
		elseif($type == "next30days" )
		{

			$datevalue[0] =$today;
			$datevalue[1] =$next30days;
		}
		elseif($type == "next60days" )
		{

			$datevalue[0] = $today;
			$datevalue[1] = $next60days;
		}
		elseif($type == "next90days" )
		{

			$datevalue[0] = $today;
			$datevalue[1] = $next90days;
		}
		elseif($type == "next120days" )
		{

			$datevalue[0] = $today;
			$datevalue[1] = $next120days;
		}
		elseif($type == "last7days" )
		{

			$datevalue[0] = $last7days;
			$datevalue[1] = $today;
		}
		elseif($type == "last30days" )
		{

			$datevalue[0] = $last30days;
			$datevalue[1] =  $today;
		}
		elseif($type == "last60days" )
		{

			$datevalue[0] = $last60days;
			$datevalue[1] = $today;
		}
		else if($type == "last90days" )
		{

			$datevalue[0] = $last90days;
			$datevalue[1] = $today;
		}
		elseif($type == "last120days" )
		{

			$datevalue[0] = $last120days;
			$datevalue[1] = $today;
		}
		elseif($type == "thisfy" )
		{

			$datevalue[0] = $currentFY0;
			$datevalue[1] = $currentFY1;
		}
		elseif($type == "prevfy" )
		{

			$datevalue[0] = $lastFY0;
			$datevalue[1] = $lastFY1;
		}
		elseif($type == "nextfy" )
		{

			$datevalue[0] = $nextFY0;
			$datevalue[1] = $nextFY1;
		}
		elseif($type == "nextfq" )
		{

			$datevalue[0] = $nFq;
			$datevalue[1] = $nFq1;
		}
		elseif($type == "prevfq" )
		{

			$datevalue[0] = $pFq;
			$datevalue[1] = $pFq1;
		}
		elseif($type == "thisfq" )
		{
			$datevalue[0] = $cFq;
			$datevalue[1] = $cFq1;
		}
		else
		{
			$datevalue[0] = "";
			$datevalue[1] = "";
		}
		return $datevalue;
	}

	/** Function to get getGroupingList for the given reportid
	 *  @ param $reportid : Type Integer
	 *  returns the $grouplist Array in the following format
	 *  		$grouplist = Array($tablename:$columnname:$fieldlabel:fieldname:typeofdata=>$tablename:$columnname $sorder,
	 *				   $tablename1:$columnname1:$fieldlabel1:fieldname1:typeofdata1=>$tablename1:$columnname1 $sorder,
	 *				   $tablename2:$columnname2:$fieldlabel2:fieldname2:typeofdata2=>$tablename2:$columnname2 $sorder)
	 * This function also sets the return value in the class variable $this->groupbylist
	 */


	function getGroupingList($reportid)
	{
		global $adb;
		global $modules;
		global $log;
		global $table_prefix;

		// Have we initialized information already?
		if($this->_groupinglist !== false) {
			return $this->_groupinglist;
		}

		$sreportsortsql = "select ".$table_prefix."_reportsortcol.* from ".$table_prefix."_report";
		$sreportsortsql .= " inner join ".$table_prefix."_reportsortcol on ".$table_prefix."_report.reportid = ".$table_prefix."_reportsortcol.reportid";
		$sreportsortsql .= " where ".$table_prefix."_report.reportid =? AND ".$table_prefix."_reportsortcol.columnname IN (SELECT columnname from ".$table_prefix."_selectcolumn WHERE queryid=?) order by ".$table_prefix."_reportsortcol.sortcolid";

		$result = $adb->pquery($sreportsortsql, array($reportid,$reportid));

		while($reportsortrow = $adb->fetch_array($result))
		{
			$fieldcolname = $reportsortrow["columnname"];
			list($tablename,$colname,$module_field,$fieldname,$single) = explode(":",$fieldcolname);
			$sortorder = $reportsortrow["sortorder"];

			if($sortorder == "Ascending")
			{
				$sortorder = "ASC";

			}elseif($sortorder == "Descending")
			{
				$sortorder = "DESC";
			}

			if($fieldcolname != "none")
			{
				$selectedfields = explode(":",$fieldcolname);
				if($selectedfields[0] == $table_prefix."_crmentity".$this->primarymodule)
					$selectedfields[0] = $table_prefix."_crmentity";
				if(stripos($selectedfields[1],'cf_')==0 && stristr($selectedfields[1],'cf_')==true){
					$sqlvalue = $adb->sql_escape_string(substr(decode_html($selectedfields[2]),0,29)).' '.$sortorder; // crmv@29686 crmv@30385
				} else {
					//crmv@24567	//crmv@26440
					$sqlvalue = $adb->sql_escape_string(substr(str_replace('&','and',decode_html($selectedfields[2])),0,29)).' '.$sortorder; // crmv@29686 crmv@30385
					//crmv@24567e	//crmv@26440e
				}
				$grouplist[$fieldcolname] = $sqlvalue;
				$temp = explode("_",$selectedfields[2],2);
				$module = $temp[0];
				if(CheckFieldPermission($fieldname,$module) == 'true')
				{
					$this->groupbylist[$fieldcolname] = $selectedfields[0].".".$selectedfields[1]." ".$selectedfields[2];
				}
			}
		}

		if(in_array($this->primarymodule, array('Invoice', 'Quotes', 'SalesOrder', 'PurchaseOrder')) ) {
			$instance = CRMEntity::getInstance($this->primarymodule);
			$grouplist[$instance->table_index] = $instance->table_name.'.'.$instance->table_index;
			$grouplist['subject'] = $instance->table_name.'.subject';
			$this->groupbylist[$fieldcolname] = $instance->table_name.'.'.$instance->table_index;
			$this->groupbylist['subject'] = $instance->table_name.'.subject';
		}

		// Save the information
		$this->_groupinglist = $grouplist;

		$log->info("ReportRun :: Successfully returned getGroupingList".$reportid);
		return $grouplist;
	}

	/** function to replace special characters
	 *  @ param $selectedfield : type string
	 *  this returns the string for grouplist
	 */

	function replaceSpecialChar($selectedfield){
		$selectedfield = decode_html(decode_html($selectedfield));
		preg_match('/&/', $selectedfield, $matches);
		if(!empty($matches)){
			$selectedfield = str_replace('&', 'and',($selectedfield));
		}
		return $selectedfield;
		}

	/** function to get the selectedorderbylist for the given reportid
	 *  @ param $reportid : type integer
	 *  this returns the columns query for the sortorder columns
	 *  this function also sets the return value in the class variable $this->orderbylistsql
	 */


	function getSelectedOrderbyList($reportid)
	{

		global $adb;
		global $modules;
		global $log;
		global $table_prefix;

		$sreportsortsql = "select ".$table_prefix."_reportsortcol.* from ".$table_prefix."_report";
		$sreportsortsql .= " inner join ".$table_prefix."_reportsortcol on ".$table_prefix."_report.reportid = ".$table_prefix."_reportsortcol.reportid";
		$sreportsortsql .= " where ".$table_prefix."_report.reportid =? order by ".$table_prefix."_reportsortcol.sortcolid";

		$result = $adb->pquery($sreportsortsql, array($reportid));
		$noofrows = $adb->num_rows($result);

		for($i=0; $i<$noofrows; $i++)
		{
			$fieldcolname = $adb->query_result($result,$i,"columnname");
			$sortorder = $adb->query_result($result,$i,"sortorder");

			if($sortorder == "Ascending")
			{
				$sortorder = "ASC";
			}
			elseif($sortorder == "Descending")
			{
				$sortorder = "DESC";
			}

			if($fieldcolname != "none")
			{
				$this->orderbylistcolumns[] = $fieldcolname;
				$n = $n + 1;
				$selectedfields = explode(":",$fieldcolname);
				if($n > 1)
				{
					$sSQL .= ", ";
					$this->orderbylistsql .= ", ";
				}
				if($selectedfields[0] == $table_prefix."_crmentity".$this->primarymodule)
					$selectedfields[0] = $table_prefix."_crmentity";
				$sSQL .= $selectedfields[0].".".$selectedfields[1]." ".$sortorder;
				$this->orderbylistsql .= $selectedfields[0].".".$selectedfields[1]." ".$selectedfields[2];
			}
		}
		$log->info("ReportRun :: Successfully returned getSelectedOrderbyList".$reportid);
		return $sSQL;
	}

	/** function to get secondary Module for the given Primary module and secondary module
	 *  @ param $module : type String
	 *  @ param $secmodule : type String
	 *  this returns join query for the given secondary module
	 */

	function getRelatedModulesQuery($module,$secmodule,$report_type='')
	{
		global $log,$current_user;
		$query = '';
		if($secmodule!=''){
			$secondarymodule = explode(":",$secmodule);
			foreach($secondarymodule as $key=>$value) {
					$foc = CRMEntity::getInstance($value);
					$query .= $foc->generateReportsSecQuery($module,$value,$report_type);
					$query .= getNonAdminAccessControlQuery($value,$current_user,$value);
					//$query .= getNonAdminAccessControlQuery($value,$current_user,$value,'LEFT');	//crmv@31775@TODO
			}
		}
		$log->info("ReportRun :: Successfully returned getRelatedModulesQuery".$secmodule);
		return $query;
	}
	/** function to get report query for the given module
	 *  @ param $module : type String
	 *  this returns join query for the given module
	 */

	function getReportsQuery($module, $type='')
	{
		global $log, $current_user,$table_prefix;
		$secondary_module ="'";
		$secondary_module .= str_replace(":","','",$this->secondarymodule);
		$secondary_module .="'";

		if($module == "Leads")
		{
			$query = "from ".$table_prefix."_leaddetails
				inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=".$table_prefix."_leaddetails.leadid
				inner join ".$table_prefix."_leadsubdetails on ".$table_prefix."_leadsubdetails.leadsubscriptionid=".$table_prefix."_leaddetails.leadid
				inner join ".$table_prefix."_leadaddress on ".$table_prefix."_leadaddress.leadaddressid=".$table_prefix."_leadsubdetails.leadsubscriptionid
				inner join ".$table_prefix."_leadscf on ".$table_prefix."_leaddetails.leadid = ".$table_prefix."_leadscf.leadid
				left join ".$table_prefix."_groups ".$table_prefix."_groupsLeads on ".$table_prefix."_groupsLeads.groupid = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_users ".$table_prefix."_usersLeads on ".$table_prefix."_usersLeads.id = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_groups on ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_users on ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
				".$this->getRelatedModulesQuery($module,$this->secondarymodule).
						getNonAdminAccessControlQuery($this->primarymodule,$current_user)."
				where ".$table_prefix."_crmentity.deleted=0 and ".$table_prefix."_leaddetails.converted=0";
		}
		else if($module == "Accounts")
		{
			$query = "from ".$table_prefix."_account
				inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=".$table_prefix."_account.accountid
				inner join ".$table_prefix."_accountbillads on ".$table_prefix."_account.accountid=".$table_prefix."_accountbillads.accountaddressid
				inner join ".$table_prefix."_accountshipads on ".$table_prefix."_account.accountid=".$table_prefix."_accountshipads.accountaddressid
				inner join ".$table_prefix."_accountscf on ".$table_prefix."_account.accountid = ".$table_prefix."_accountscf.accountid
				left join ".$table_prefix."_groups ".$table_prefix."_groupsAccounts on ".$table_prefix."_groupsAccounts.groupid = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_account ".$table_prefix."_accountAccounts on ".$table_prefix."_accountAccounts.accountid = ".$table_prefix."_account.parentid
				left join ".$table_prefix."_users ".$table_prefix."_usersAccounts on ".$table_prefix."_usersAccounts.id = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_groups on ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_users on ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
				".$this->getRelatedModulesQuery($module,$this->secondarymodule,$type).
						getNonAdminAccessControlQuery($this->primarymodule,$current_user)."
				where ".$table_prefix."_crmentity.deleted=0 ";
		}

		else if($module == "Contacts")
		{
			$query = "from ".$table_prefix."_contactdetails
				inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_contactdetails.contactid
				inner join ".$table_prefix."_contactaddress on ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_contactaddress.contactaddressid
				inner join ".$table_prefix."_customerdetails on ".$table_prefix."_customerdetails.customerid = ".$table_prefix."_contactdetails.contactid
				inner join ".$table_prefix."_contactsubdetails on ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_contactsubdetails.contactsubscriptionid
				inner join ".$table_prefix."_contactscf on ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_contactscf.contactid
				left join ".$table_prefix."_groups ".$table_prefix."_groupsContacts on ".$table_prefix."_groupsContacts.groupid = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_contactdetails ".$table_prefix."_contactdetailsContacts on ".$table_prefix."_contactdetailsContacts.contactid = ".$table_prefix."_contactdetails.reportsto
				left join ".$table_prefix."_account ".$table_prefix."_accountContacts on ".$table_prefix."_accountContacts.accountid = ".$table_prefix."_contactdetails.accountid
				left join ".$table_prefix."_users ".$table_prefix."_usersContacts on ".$table_prefix."_usersContacts.id = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_users on ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_groups on ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				".$this->getRelatedModulesQuery($module,$this->secondarymodule).
						getNonAdminAccessControlQuery($this->primarymodule,$current_user)."
				where ".$table_prefix."_crmentity.deleted=0";
		}

		else if($module == "Potentials")
		{
			$query = "from ".$table_prefix."_potential
				inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=".$table_prefix."_potential.potentialid
				inner join ".$table_prefix."_potentialscf on ".$table_prefix."_potentialscf.potentialid = ".$table_prefix."_potential.potentialid
				left join ".$table_prefix."_account ".$table_prefix."_accountPotentials on ".$table_prefix."_potential.related_to = ".$table_prefix."_accountPotentials.accountid
				left join ".$table_prefix."_contactdetails ".substr($table_prefix.'_contactdetailsPotentials',0,29)." on ".$table_prefix."_potential.related_to = ".substr($table_prefix.'_contactdetailsPotentials',0,29).".contactid
				left join ".$table_prefix."_campaign ".$table_prefix."_campaignPotentials on ".$table_prefix."_potential.campaignid = ".$table_prefix."_campaignPotentials.campaignid
				left join ".$table_prefix."_groups ".$table_prefix."_groupsPotentials on ".$table_prefix."_groupsPotentials.groupid = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_users ".$table_prefix."_usersPotentials on ".$table_prefix."_usersPotentials.id = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_groups on ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_users on ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
				".$this->getRelatedModulesQuery($module,$this->secondarymodule).
						getNonAdminAccessControlQuery($this->primarymodule,$current_user)."
				where ".$table_prefix."_crmentity.deleted=0 ";
		}
		//For this Product - we can related Accounts, Contacts (Also Leads, Potentials)
		else if($module == "Products")
		{
			//crmv@25158 : strtoupper x oracle
			//crmv@33990
			global $adb;
			$tmptable = 'tmp_innerProduct'.$this->reportid;
			if (!$adb->table_exist($tmptable,true)){
				Vtiger_Utils::CreateTable($tmptable,"productid I(11) NOTNULL PRIMARY,\"ACTUAL_UNIT_PRICE\" N(25,2)",true,true);
			}
			else{
				$sql = "truncate table $tmptable";
				$adb->query($sql);
			}
			$sql_insert = "insert into $tmptable SELECT ".$table_prefix."_products.productid,
								(CASE WHEN (".$table_prefix."_products.currency_id = 1 ) THEN ".$table_prefix."_products.unit_price
									ELSE (".$table_prefix."_products.unit_price / ".$table_prefix."_currency_info.conversion_rate) END
								) AS \"".strtoupper('actual_unit_price')."\"
						FROM ".$table_prefix."_products
						INNER JOIN ".$table_prefix."_currency_info ON ".$table_prefix."_products.currency_id = ".$table_prefix."_currency_info.id
						INNER JOIN ".$table_prefix."_productcurrencyrel ON ".$table_prefix."_products.productid = ".$table_prefix."_productcurrencyrel.productid
						AND ".$table_prefix."_productcurrencyrel.currencyid = ". $current_user->currency_id;
			$adb->query($sql_insert);
			$query = "from ".$table_prefix."_products
				inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=".$table_prefix."_products.productid
				left join ".$table_prefix."_productcf on ".$table_prefix."_products.productid = ".$table_prefix."_productcf.productid
				left join ".$table_prefix."_users ".$table_prefix."_usersProducts on ".$table_prefix."_usersProducts.id = ".$table_prefix."_products.handler
				left join ".$table_prefix."_vendor ".$table_prefix."_vendorRelProducts on ".$table_prefix."_vendorRelProducts.vendorid = ".$table_prefix."_products.vendor_id
				LEFT JOIN $tmptable on  $tmptable.productid = ".$table_prefix."_products.productid
				".$this->getRelatedModulesQuery($module,$this->secondarymodule).
						getNonAdminAccessControlQuery($this->primarymodule,$current_user)."
				where ".$table_prefix."_crmentity.deleted=0";
			//crmv@33990e
		}

		else if($module == "HelpDesk")
		{
			$query = "from ".$table_prefix."_troubletickets
				inner join ".$table_prefix."_crmentity
				on ".$table_prefix."_crmentity.crmid=".$table_prefix."_troubletickets.ticketid
				inner join ".$table_prefix."_ticketcf on ".$table_prefix."_ticketcf.ticketid = ".$table_prefix."_troubletickets.ticketid
				left join ".$table_prefix."_crmentity ".$table_prefix."_crmentityRelHelpDesk on ".$table_prefix."_crmentityRelHelpDesk.crmid = ".$table_prefix."_troubletickets.parent_id
				left join ".$table_prefix."_account ".$table_prefix."_accountRelHelpDesk on ".$table_prefix."_accountRelHelpDesk.accountid=".$table_prefix."_crmentityRelHelpDesk.crmid
				left join ".$table_prefix."_contactdetails ".substr($table_prefix.'_contactdetailsRelHelpDesk',0,29)." on ".substr($table_prefix.'_contactdetailsRelHelpDesk',0,29).".contactid= ".$table_prefix."_crmentityRelHelpDesk.crmid
				left join ".$table_prefix."_products ".$table_prefix."_productsRel on ".$table_prefix."_productsRel.productid = ".$table_prefix."_troubletickets.product_id
				left join ".$table_prefix."_groups ".$table_prefix."_groupsHelpDesk on ".$table_prefix."_groupsHelpDesk.groupid = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_users ".$table_prefix."_usersHelpDesk on ".$table_prefix."_crmentity.smownerid=".$table_prefix."_usersHelpDesk.id
				left join ".$table_prefix."_groups on ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_users on ".$table_prefix."_crmentity.smownerid=".$table_prefix."_users.id
				".$this->getRelatedModulesQuery($module,$this->secondarymodule).
						getNonAdminAccessControlQuery($this->primarymodule,$current_user)."
				where ".$table_prefix."_crmentity.deleted=0 ";
		}

		else if($module == "Calendar")
		{
			//crmv@17001
			$query = "from ".$table_prefix."_activity
				inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=".$table_prefix."_activity.activityid
				left join ".$table_prefix."_activitycf on ".$table_prefix."_activitycf.activityid = ".$table_prefix."_crmentity.crmid
				left join ".$table_prefix."_cntactivityrel on ".$table_prefix."_cntactivityrel.activityid= ".$table_prefix."_activity.activityid
				left join ".$table_prefix."_contactdetails ".$table_prefix."_contactdetailsCalendar on ".$table_prefix."_contactdetailsCalendar.contactid= ".$table_prefix."_cntactivityrel.contactid
				left join ".$table_prefix."_groups ".$table_prefix."_groupsCalendar on ".$table_prefix."_groupsCalendar.groupid = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_users ".$table_prefix."_usersCalendar on ".$table_prefix."_usersCalendar.id = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_groups on ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_users on ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_seactivityrel on ".$table_prefix."_seactivityrel.activityid = ".$table_prefix."_activity.activityid
				left join ".$table_prefix."_activity_reminder on ".$table_prefix."_activity_reminder.activity_id = ".$table_prefix."_activity.activityid
				left join ".$table_prefix."_recurringevents on ".$table_prefix."_recurringevents.activityid = ".$table_prefix."_activity.activityid
				left join ".$table_prefix."_crmentity ".$table_prefix."_crmentityRelCalendar on ".$table_prefix."_crmentityRelCalendar.crmid = ".$table_prefix."_seactivityrel.crmid
				left join ".$table_prefix."_account ".$table_prefix."_accountRelCalendar on ".$table_prefix."_accountRelCalendar.accountid=".$table_prefix."_crmentityRelCalendar.crmid
				left join ".$table_prefix."_leaddetails ".$table_prefix."_leaddetailsRelCalendar on ".$table_prefix."_leaddetailsRelCalendar.leadid = ".$table_prefix."_crmentityRelCalendar.crmid
				left join ".$table_prefix."_potential ".$table_prefix."_potentialRelCalendar on ".$table_prefix."_potentialRelCalendar.potentialid = ".$table_prefix."_crmentityRelCalendar.crmid
				left join ".$table_prefix."_quotes ".$table_prefix."_quotesRelCalendar on ".$table_prefix."_quotesRelCalendar.quoteid = ".$table_prefix."_crmentityRelCalendar.crmid
				left join ".$table_prefix."_purchaseorder ".substr($table_prefix.'_purchaseorderRelCalendar',0,29)." on ".substr($table_prefix.'_purchaseorderRelCalendar',0,29).".purchaseorderid = ".$table_prefix."_crmentityRelCalendar.crmid
				left join ".$table_prefix."_invoice ".$table_prefix."_invoiceRelCalendar on ".$table_prefix."_invoiceRelCalendar.invoiceid = ".$table_prefix."_crmentityRelCalendar.crmid
				left join ".$table_prefix."_salesorder ".$table_prefix."_salesorderRelCalendar on ".$table_prefix."_salesorderRelCalendar.salesorderid = ".$table_prefix."_crmentityRelCalendar.crmid
				left join ".$table_prefix."_troubletickets ".substr($table_prefix.'_troubleticketsRelCalendar',0,29)." on ".substr($table_prefix.'_troubleticketsRelCalendar',0,29).".ticketid = ".$table_prefix."_crmentityRelCalendar.crmid
				left join ".$table_prefix."_campaign ".$table_prefix."_campaignRelCalendar on ".$table_prefix."_campaignRelCalendar.campaignid = ".$table_prefix."_crmentityRelCalendar.crmid
				".$this->getRelatedModulesQuery($module,$this->secondarymodule).
						getNonAdminAccessControlQuery($this->primarymodule,$current_user)."
				WHERE ".$table_prefix."_crmentity.deleted=0 and (".$table_prefix."_activity.activitytype != 'Emails')";
			//crmv@17001e
		}

		else if($module == "Quotes")
		{
			$query = "from ".$table_prefix."_quotes
				inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=".$table_prefix."_quotes.quoteid
				inner join ".$table_prefix."_quotesbillads on ".$table_prefix."_quotes.quoteid=".$table_prefix."_quotesbillads.quotebilladdressid
				inner join ".$table_prefix."_quotesshipads on ".$table_prefix."_quotes.quoteid=".$table_prefix."_quotesshipads.quoteshipaddressid";
			// crmv@29686
			if($type !== 'COLUMNSTOTOTAL') {
				$query .= " left join ".$table_prefix."_inventoryproductrel ".substr($table_prefix.'_inventoryproductrelQuotes',0,29)." on ".$table_prefix."_quotes.quoteid = ".substr($table_prefix.'_inventoryproductrelQuotes',0,29).".id
				left join ".$table_prefix."_products ".$table_prefix."_productsQuotes on ".$table_prefix."_productsQuotes.productid = ".substr($table_prefix.'_inventoryproductrelQuotes',0,29).".productid
				left join ".$table_prefix."_products on ".$table_prefix."_products.productid = ".substr($table_prefix.'_inventoryproductrelQuotes',0,29).".productid
				left join ".$table_prefix."_service ".$table_prefix."_serviceQuotes on ".$table_prefix."_serviceQuotes.serviceid = ".substr($table_prefix.'_inventoryproductrelQuotes',0,29).".productid";
			}
			// crmv@29686e
			$query .= " left join ".$table_prefix."_quotescf on ".$table_prefix."_quotes.quoteid = ".$table_prefix."_quotescf.quoteid
				left join ".$table_prefix."_groups ".$table_prefix."_groupsQuotes on ".$table_prefix."_groupsQuotes.groupid = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_users ".$table_prefix."_usersQuotes on ".$table_prefix."_usersQuotes.id = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_groups on ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_users on ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_users ".$table_prefix."_usersRel1 on ".$table_prefix."_usersRel1.id = ".$table_prefix."_quotes.inventorymanager
				left join ".$table_prefix."_potential ".$table_prefix."_potentialRelQuotes on ".$table_prefix."_potentialRelQuotes.potentialid = ".$table_prefix."_quotes.potentialid
				left join ".$table_prefix."_contactdetails ".$table_prefix."_contactdetailsQuotes on ".$table_prefix."_contactdetailsQuotes.contactid = ".$table_prefix."_quotes.contactid
				left join ".$table_prefix."_account ".$table_prefix."_accountQuotes on ".$table_prefix."_accountQuotes.accountid = ".$table_prefix."_quotes.accountid
				".$this->getRelatedModulesQuery($module,$this->secondarymodule).
						getNonAdminAccessControlQuery($this->primarymodule,$current_user)."
				where ".$table_prefix."_crmentity.deleted=0";
		}

		else if($module == "PurchaseOrder")
		{
			$query = "from ".$table_prefix."_purchaseorder
				inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=".$table_prefix."_purchaseorder.purchaseorderid
				inner join ".$table_prefix."_pobillads on ".$table_prefix."_purchaseorder.purchaseorderid=".$table_prefix."_pobillads.pobilladdressid
				inner join ".$table_prefix."_poshipads on ".$table_prefix."_purchaseorder.purchaseorderid=".$table_prefix."_poshipads.poshipaddressid";
			// crmv@29686
			if($type !== 'COLUMNSTOTOTAL') {
				$query .= " left join ".$table_prefix."_inventoryproductrel ".substr($table_prefix.'_inventoryproductrelPurchaseOrder',0,29)." on ".$table_prefix."_purchaseorder.purchaseorderid = ".substr($table_prefix.'_inventoryproductrelPurchaseOrder',0,29).".id
				left join ".$table_prefix."_products ".$table_prefix."_productsPurchaseOrder on ".$table_prefix."_productsPurchaseOrder.productid = ".substr($table_prefix.'_inventoryproductrelPurchaseOrder',0,29).".productid
				left join ".$table_prefix."_products on ".$table_prefix."_products.productid = ".substr($table_prefix.'_inventoryproductrelPurchaseOrder',0,29).".productid
				left join ".$table_prefix."_service ".$table_prefix."_servicePurchaseOrder on ".$table_prefix."_servicePurchaseOrder.serviceid = ".substr($table_prefix.'_inventoryproductrelPurchaseOrder',0,29).".productid";
			}
			// crmv@29686e
			$query .= " left join ".$table_prefix."_purchaseordercf on ".$table_prefix."_purchaseorder.purchaseorderid = ".$table_prefix."_purchaseordercf.purchaseorderid
				left join ".$table_prefix."_groups ".$table_prefix."_groupsPurchaseOrder on ".$table_prefix."_groupsPurchaseOrder.groupid = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_users ".$table_prefix."_usersPurchaseOrder on ".$table_prefix."_usersPurchaseOrder.id = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_groups on ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_users on ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_vendor ".$table_prefix."_vendorRelPurchaseOrder on ".$table_prefix."_vendorRelPurchaseOrder.vendorid = ".$table_prefix."_purchaseorder.vendorid
				left join ".$table_prefix."_contactdetails ".substr($table_prefix.'_contactdetailsPurchaseOrder',0,29)." on ".substr($table_prefix.'_contactdetailsPurchaseOrder',0,29).".contactid = ".$table_prefix."_purchaseorder.contactid
				".$this->getRelatedModulesQuery($module,$this->secondarymodule).
						getNonAdminAccessControlQuery($this->primarymodule,$current_user)."
				where ".$table_prefix."_crmentity.deleted=0";
		}

		else if($module == "Invoice")
		{
			$query = "from ".$table_prefix."_invoice
				inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=".$table_prefix."_invoice.invoiceid
				inner join ".$table_prefix."_invoicebillads on ".$table_prefix."_invoice.invoiceid=".$table_prefix."_invoicebillads.invoicebilladdressid
				inner join ".$table_prefix."_invoiceshipads on ".$table_prefix."_invoice.invoiceid=".$table_prefix."_invoiceshipads.invoiceshipaddressid";
			// crmv@29686
			if($type !== 'COLUMNSTOTOTAL') {
				$query .=" left join ".$table_prefix."_inventoryproductrel ".substr($table_prefix.'_inventoryproductrelInvoice',0,29)." on ".$table_prefix."_invoice.invoiceid = ".substr($table_prefix.'_inventoryproductrelInvoice',0,29).".id
					left join ".$table_prefix."_products ".$table_prefix."_productsInvoice on ".$table_prefix."_productsInvoice.productid = ".substr($table_prefix.'_inventoryproductrelInvoice',0,29).".productid
					left join ".$table_prefix."_products on ".$table_prefix."_products.productid = ".substr($table_prefix.'_inventoryproductrelInvoice',0,29).".productid
					left join ".$table_prefix."_service ".$table_prefix."_serviceInvoice on ".$table_prefix."_serviceInvoice.serviceid = ".substr($table_prefix.'_inventoryproductrelInvoice',0,29).".productid";
			}
			// crmv@29686e
			$query .= " left join ".$table_prefix."_salesorder ".$table_prefix."_salesorderInvoice on ".$table_prefix."_salesorderInvoice.salesorderid=".$table_prefix."_invoice.salesorderid
				left join ".$table_prefix."_invoicecf on ".$table_prefix."_invoice.invoiceid = ".$table_prefix."_invoicecf.invoiceid
				left join ".$table_prefix."_groups ".$table_prefix."_groupsInvoice on ".$table_prefix."_groupsInvoice.groupid = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_users ".$table_prefix."_usersInvoice on ".$table_prefix."_usersInvoice.id = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_groups on ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_users on ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_account ".$table_prefix."_accountInvoice on ".$table_prefix."_accountInvoice.accountid = ".$table_prefix."_invoice.accountid
				left join ".$table_prefix."_contactdetails ".$table_prefix."_contactdetailsInvoice on ".$table_prefix."_contactdetailsInvoice.contactid = ".$table_prefix."_invoice.contactid
				".$this->getRelatedModulesQuery($module,$this->secondarymodule).
						getNonAdminAccessControlQuery($this->primarymodule,$current_user)."
				where ".$table_prefix."_crmentity.deleted=0";
		}
		else if($module == "SalesOrder")
		{
			$query = "from ".$table_prefix."_salesorder
				inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=".$table_prefix."_salesorder.salesorderid
				inner join ".$table_prefix."_sobillads on ".$table_prefix."_salesorder.salesorderid=".$table_prefix."_sobillads.sobilladdressid
				inner join ".$table_prefix."_soshipads on ".$table_prefix."_salesorder.salesorderid=".$table_prefix."_soshipads.soshipaddressid";
			// crmv@29686
			if($type !== 'COLUMNSTOTOTAL') {
				$query .= " left join ".$table_prefix."_inventoryproductrel ".substr($table_prefix.'_inventoryproductrelSalesOrder',0,29)." on ".$table_prefix."_salesorder.salesorderid = ".substr($table_prefix.'_inventoryproductrelSalesOrder',0,29).".id
				left join ".$table_prefix."_products ".$table_prefix."_productsSalesOrder on ".$table_prefix."_productsSalesOrder.productid = ".substr($table_prefix.'_inventoryproductrelSalesOrder',0,29).".productid
				left join ".$table_prefix."_products on ".$table_prefix."_products.productid = ".substr($table_prefix.'_inventoryproductrelSalesOrder',0,29).".productid
				left join ".$table_prefix."_service ".$table_prefix."_serviceSalesOrder on ".$table_prefix."_serviceSalesOrder.serviceid = ".substr($table_prefix.'_inventoryproductrelSalesOrder',0,29).".productid";
			}
			// crmv@29686e
			$query .=" left join ".$table_prefix."_contactdetails ".substr($table_prefix.'_contactdetailsSalesOrder',0,29)." on ".substr($table_prefix.'_contactdetailsSalesOrder',0,29).".contactid = ".$table_prefix."_salesorder.contactid
				left join ".$table_prefix."_quotes ".$table_prefix."_quotesSalesOrder on ".$table_prefix."_quotesSalesOrder.quoteid = ".$table_prefix."_salesorder.quoteid
				left join ".$table_prefix."_account ".$table_prefix."_accountSalesOrder on ".$table_prefix."_accountSalesOrder.accountid = ".$table_prefix."_salesorder.accountid
				left join ".$table_prefix."_potential ".$table_prefix."_potentialRelSalesOrder on ".$table_prefix."_potentialRelSalesOrder.potentialid = ".$table_prefix."_salesorder.potentialid
				left join ".$table_prefix."_invoice_recurring_info on ".$table_prefix."_invoice_recurring_info.salesorderid = ".$table_prefix."_salesorder.salesorderid
				left join ".$table_prefix."_groups ".$table_prefix."_groupsSalesOrder on ".$table_prefix."_groupsSalesOrder.groupid = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_users ".$table_prefix."_usersSalesOrder on ".$table_prefix."_usersSalesOrder.id = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_groups on ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_users on ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
				".$this->getRelatedModulesQuery($module,$this->secondarymodule).
						getNonAdminAccessControlQuery($this->primarymodule,$current_user)."
				where ".$table_prefix."_crmentity.deleted=0";


		}
		else if($module == "Campaigns")
		{
		 $query = "from ".$table_prefix."_campaign
			        inner join ".$table_prefix."_campaignscf ".$table_prefix."_campaignscf on ".$table_prefix."_campaignscf.campaignid=".$table_prefix."_campaign.campaignid
				inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=".$table_prefix."_campaign.campaignid
				left join ".$table_prefix."_products ".$table_prefix."_productsCampaigns on ".$table_prefix."_productsCampaigns.productid = ".$table_prefix."_campaign.product_id
				left join ".$table_prefix."_groups ".$table_prefix."_groupsCampaigns on ".$table_prefix."_groupsCampaigns.groupid = ".$table_prefix."_crmentity.smownerid
		                left join ".$table_prefix."_users ".$table_prefix."_usersCampaigns on ".$table_prefix."_usersCampaigns.id = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_groups on ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
		                left join ".$table_prefix."_users on ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
                                ".$this->getRelatedModulesQuery($module,$this->secondarymodule).
						getNonAdminAccessControlQuery($this->primarymodule,$current_user)."
				where ".$table_prefix."_crmentity.deleted=0";
		}
		//sk@2
		elseif($module == 'Projects')
		{
	      $query = "from ".$table_prefix."_projects
	          inner join ".$table_prefix."_projectscf ".$table_prefix."_projectscf on ".$table_prefix."_projectscf.projectid=".$table_prefix."_projects.projectid
	          inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=".$table_prefix."_projects.projectid
	          left join ".$table_prefix."_groups ".$table_prefix."_groupsProjects on ".$table_prefix."_groupsProjects.groupid = ".$table_prefix."_crmentity.smownerid
	          left join ".$table_prefix."_users ".$table_prefix."_usersProjects on ".$table_prefix."_usersProjects.id = ".$table_prefix."_crmentity.smownerid
	          left join ".$table_prefix."_groups on ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
	          left join ".$table_prefix."_users on ".$table_prefix."_users.id = ".$table_prefix."_projects.reports_to_id
	          left join ".$table_prefix."_account ".$table_prefix."_accountProjects on ".$table_prefix."_projects.accountid=".$table_prefix."_accountProjects.accountid
	          left join ".$table_prefix."_projects_tickets on ".$table_prefix."_projects_tickets.project_id = ".$table_prefix."_projects.projectid
	          left join ".$table_prefix."_troubletickets on ".$table_prefix."_troubletickets.ticketid = ".$table_prefix."_projects_tickets.ticket_id
	          left join ".$table_prefix."_crmentity ".$table_prefix."_crmentityHelpDesk on ".$table_prefix."_crmentityHelpDesk.crmid=".$table_prefix."_troubletickets.ticketid and ".$table_prefix."_crmentityHelpDesk.deleted=0
	          left join ".$table_prefix."_contactdetails ".$table_prefix."_contactdetailsProjects on ".$table_prefix."_contactdetailsProjects.contactid= ".$table_prefix."_projects.contactid
	          left join ".$table_prefix."_projectworkers ".$table_prefix."_projectworkers on ".$table_prefix."_projectworkers.projectid = ".$table_prefix."_projects.projectid
	          left join ".$table_prefix."_users ".$table_prefix."_usersworkers on ".$table_prefix."_usersworkers.id=".$table_prefix."_projectworkers.worker_userid
	          ".$this->getRelatedModulesQuery($module,$this->secondarymodule)."
	        WHERE ".$table_prefix."_crmentity.deleted=0";
    	}
		//sk@2e
		else {
	 			if($module!=''){
	 				$focus = CRMEntity::getInstance($module);
					$query = $focus->generateReportsQuery($module)
								.$this->getRelatedModulesQuery($module,$this->secondarymodule)
								.getNonAdminAccessControlQuery($this->primarymodule,$current_user).
							" WHERE ".$table_prefix."_crmentity.deleted=0";
	 			}
			}
		$log->info("ReportRun :: Successfully returned getReportsQuery".$module);
		return $query;
	}


	/** function to get query for the given reportid,filterlist,type
	 *  @ param $reportid : Type integer
	 *  @ param $filterlist : Type Array
	 *  @ param $module : Type String
	 *  this returns join query for the report
	 */

	function sGetSQLforReport($reportid,$filterlist,$type='')
	{
		global $log;

		$columnlist = $this->getQueryColumnsList($reportid,$type);
		$groupslist = $this->getGroupingList($reportid);
		$stdfilterlist = $this->getStdFilterList($reportid);
		$columnstotallist = $this->getColumnsTotal($reportid);
		$advfiltersql = $this->getAdvFilterSql($reportid);

		$this->totallist = $columnstotallist;
		global $current_user;
		$tab_id = getTabid($this->primarymodule);
		//Fix for ticket #4915.
		$selectlist = $columnlist;
		//columns list
		if(isset($selectlist))
		{
			$selectedcolumns =  implode(", ",$selectlist);
		}
		//groups list
		//mycrmv@39026
		global $adb;
			if(
				$adb->isMssql() &&
				strpos($selectedcolumns, 'vtiger_products') === false && // c'è almeno una colonna dei prodotti
				(
					in_array($this->primarymodule, array('Invoice', 'Quotes', 'SalesOrder', 'PurchaseOrder')) ||
					in_array($this->secondarymodule, array('Invoice', 'Quotes', 'SalesOrder', 'PurchaseOrder'))
				)
			) {
			$groupslist = array_intersect_key($groupslist,$selectlist);
		}
		//mycrmv@39026e
		if(isset($groupslist))
		{
			$groupsquery = implode(", ",$groupslist);
		}

		//standard list
		if(isset($stdfilterlist))
		{
			$stdfiltersql = implode(", ",$stdfilterlist);
		}
		if(isset($filterlist))
		{
			$stdfiltersql = implode(", ",$filterlist);
		}
		//columns to total list
		if(isset($columnstotallist))
		{
			$columnstotalsql = implode(", ",$columnstotallist);
		}
		if($stdfiltersql != "")
		{
			$wheresql = " and ".$stdfiltersql;
		}
		if($advfiltersql != "")
		{
			$wheresql .= " and ".$advfiltersql;
		}

		$reportquery = $this->getReportsQuery($this->primarymodule, $type);

		// If we don't have access to any columns, let us select one column and limit result to shown we have not results
                // Fix for: http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/4758 - Prasad
		$allColumnsRestricted = false;

		if($type == 'COLUMNSTOTOTAL')
		{
			if($columnstotalsql != '')
			{
				$reportquery = "select ".$columnstotalsql." ".$reportquery." ".$wheresql;
			}
		}else
		{
			if($selectedcolumns == '') {
				// Fix for: http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/4758 - Prasad

				$selectedcolumns = "''"; // "''" to get blank column name
				$allColumnsRestricted = true;
			}
			//crmv@25158 crmv@30385
			global $adb;
			if(
				!$adb->isOracle() &&
				strpos($selectedcolumns, 'vtiger_products') === false && // c'è almeno una colonna dei prodotti
				(
					in_array($this->primarymodule, array('Invoice', 'Quotes', 'SalesOrder', 'PurchaseOrder')) ||
					in_array($this->secondarymodule, array('Invoice', 'Quotes', 'SalesOrder', 'PurchaseOrder'))
				)
			) {
			//crmv@25158e crmv@30385e
				$selectedcolumns = ' distinct '. $selectedcolumns;
			}
			$reportquery = "select ".$selectedcolumns." ".$reportquery." ".$wheresql;
		}
		$reportquery = listQueryNonAdminChange($reportquery, $this->primarymodule);
		//crmv@add adv list sharing
		if(!empty($this->secondarymodule)){
			$sec_modules = explode(":",$this->secondarymodule);
			for($i=0;$i<count($sec_modules);$i++){
				$reportquery = listQueryNonAdminChange_parent($reportquery,$sec_modules[$i],$sec_modules[$i]);
			}
		}
		//crmv@add adv list sharing end
		
		//crmv@31775
		if ($type == 'CV_RPRT') {
			$modcl = CRMEntity::getInstance($this->report_module);
			$reportquery .= ' and '.$modcl->table_name.'.'.$modcl->table_index.' is not null';
			$reportquery .= " group by id";
		}
		//crmv@31775e
		
		if(trim($groupsquery) != "" && !empty($type) && $type !== 'COLUMNSTOTOTAL' && $type != 'CV_RPRT')	//crmv@24453	//crmv@31775
		{
			$reportquery .= " order by ".$groupsquery;
		}
		global $crmv;
		if ($crmv) {
		//echo $reportquery;die;
		}
		// Prasad: No columns selected so limit the number of rows directly.
		if($allColumnsRestricted) {
//			$reportquery .= " limit 0";
		}
		if ($reportid == 195) {
		echo $reportquery;
		}
		$log->info("ReportRun :: Successfully returned sGetSQLforReport".$reportid);
		return $reportquery;

	}


	//crmv@29686
	function hasSummary() {
		global $adb,$table_prefix;
		$res = $adb->pquery("SELECT view_count_lvl FROM ".$table_prefix."_reportsortcol WHERE reportid = ? and view_count_lvl = 1", array($this->reportid));
		if ($res && $adb->num_rows($res) > 0) return true;
		return false;
	}

	function hasTotals() {
		$this->getColumnsTotal($this->reportid);
		return ($this->_columnstotallist !== false && count($this->_columnstotallist) > 0);
	}

	//crmv@29686e



	/** function to get the report output in HTML,PDF,TOTAL,PRINT,PRINTTOTAL formats depends on the argument $outputformat
	 *  @ param $outputformat : Type String (valid parameters HTML,PDF,TOTAL,PRINT,PRINT_TOTAL)
	 *  @ param $filterlist : Type Array
	 *  This returns HTML Report if $outputformat is HTML
         *  		Array for PDF if  $outputformat is PDF
	 *		HTML strings for TOTAL if $outputformat is TOTAL
	 *		Array for PRINT if $outputformat is PRINT
	 *		HTML strings for TOTAL fields  if $outputformat is PRINTTOTAL
	 *		HTML strings for
	 */

	// Performance Optimization: Added parameter directOutput to avoid building big-string!
	function GenerateReport($outputformat,$filterlist, $directOutput=false)
	{
		global $adb,$current_user,$php_max_execution_time;
		global $modules,$app_strings;
		global $mod_strings,$current_language;
		global $table_prefix;
		require('user_privileges/user_privileges_'.$current_user->id.'.php');
		$modules_selected = array();
		$modules_selected[] = $this->primarymodule;
		if(!empty($this->secondarymodule)){
			$sec_modules = explode(":",$this->secondarymodule);
			for($i=0;$i<count($sec_modules);$i++){
				$modules_selected[] = $sec_modules[$i];
			}
		}

		// Update Currency Field list
		$currencyfieldres = $adb->pquery("SELECT tabid, fieldlabel, uitype from ".$table_prefix."_field WHERE uitype in (71,72,10)", array());
		if($currencyfieldres) {
			foreach($currencyfieldres as $currencyfieldrow) {
				$modprefixedlabel = getTabModuleName($currencyfieldrow['tabid']).' '.$currencyfieldrow['fieldlabel'];
				$modprefixedlabel = str_replace(' ','_',$modprefixedlabel);
				if($currencyfieldrow['uitype']!=10){
					if(!in_array($modprefixedlabel, $this->convert_currency) && !in_array($modprefixedlabel, $this->append_currency_symbol_to_value)) {
						$this->convert_currency[] = $modprefixedlabel;
					}
				} else {
					if(!in_array($modprefixedlabel, $this->ui10_fields)) {
						$this->ui10_fields[] = $modprefixedlabel;
					}
				}
			}
		}
		//crmv@21249
		$multipicklistres = $adb->pquery("SELECT tabid, fieldlabel, uitype,fieldname from ".$table_prefix."_field WHERE uitype in (1015,1115)", array());
		if($multipicklistres) {
			while ($multipicklistrow = $adb->fetchByAssoc($multipicklistres, -1, false)) {	//crmv@27048
				$modprefixedlabel = getTabModuleName($multipicklistrow['tabid']).' '.$multipicklistrow['fieldlabel'];
				$modprefixedlabel = str_replace(' ','_',$modprefixedlabel);
				if($this->multipicklist_fields[$modprefixedlabel] == '') {
					$this->multipicklist_fields[$modprefixedlabel] = $multipicklistrow['fieldname'];
				}
			}
		}
		//crmv@21249e
		//crmv@20630
		$picklistres = $adb->pquery("SELECT tabid, fieldlabel, uitype,fieldname from ".$table_prefix."_field WHERE uitype in (15,33,55,300)", array()); // crmv@30528
		if($picklistres) {
			foreach($picklistres as $picklistrow) {
				$modprefixedlabel = getTabModuleName($picklistrow['tabid']).' '.$picklistrow['fieldlabel'];
				$modprefixedlabel = str_replace(' ','_',$modprefixedlabel);
				if($this->picklist_fields[$modprefixedlabel] == '') {
					$this->picklist_fields[$modprefixedlabel] = $picklistrow['fieldname'];
				}
			}
		}
		//crmv@20630e
		//mycrmv@24524
		//danzi.tn@20140423 uitype 1077 per mostrare nome utente
		$users_res = $adb->pquery("SELECT tabid, fieldlabel, uitype,fieldname from ".$table_prefix."_field WHERE uitype in (52,77,1077)", array());
		if($users_res) {
			foreach($users_res as $userres) {
				$modprefixedlabel = getTabModuleName($userres['tabid']).' '.$userres['fieldlabel'];
				$modprefixedlabel = str_replace(' ','_',$modprefixedlabel);					
				if($this->users_fields[$modprefixedlabel] == '') {					
					$this->users_fields[$modprefixedlabel] = $userres['fieldname'];
				}
			}
		}
		//mycrmv@24524e

		//crmv@29686
		$sql_group = $adb->pquery("SELECT sortcolid, view_count_lvl FROM ".$table_prefix."_reportsortcol WHERE reportid = ? ", array($this->reportid));
		if($sql_group) {
			$array_lvl_count = array();
			while($row = $adb->fetchByAssoc($sql_group)){
				$array_lvl_count[$row['sortcolid']] = $row['view_count_lvl'];
			}
		}
		$limit_row4pag = 200;  /*
		                      * portare fuori dagli if
		                      * $sSQL = $this->sGetSQLforReport($this->reportid,$filterlist,$outputformat);
								$result = $adb->query($sSQL);
								per i casi NAV COUNT E HTML
		                      */
		if($outputformat == "NAV"){

			if(isset($this->total_count) && $this->total_count != ''){
				$noofrows = $this->total_count;
			}else{
				$sSQL = $this->sGetSQLforReport($this->reportid,$filterlist,$outputformat);
				$result = $adb->query($sSQL);
				$noofrows = $adb->num_rows($result);
			}
			$filter_json = str_replace('"','|#|',(str_replace("'","|$|",Zend_Json::encode($filterlist))));
			$noofpag = ceil($noofrows/$limit_row4pag);

			$start = 1;  //pag da cambiare

			//Visualizzando Record x - y di z
			$navigation_array = VT_getSimpleNavigationValues($start,$limit_row4pag,$noofrows);
			$limit_start_rec = ($start-1) * $limit_row4pag;
			$record_string = getRecordRangeMessage($limit_row4pag, $limit_start_rec,$noofrows);

			$navigationOutput = getTableHeaderSimpleNavigation($navigation_array, $url_string,$currentModule,$type,$viewid);


			$coltotalhtml .= "<table width='100%'>";
			$coltotalhtml .= "<tr>
				<td id='rec_string' class='small' width='45%' nowrap='' align=left'>".$record_string."</td>
				<td id='nav_buttons' width='15%' align='left' style='padding:5px;'>
				<a title='Primo' alt='Primo' onclick=\"RecordNavigation('".$noofpag."','first','".$start."','".$limit_row4pag."','".$noofrows."','".$filter_json."','".$this->reportid."');Loading_rep();\" href='javascript:;'>
					<img border='0' align='absmiddle' src='themes/softed/images/start_disabled.gif'>
				</a>
				<a title='Precedente' alt='Precedente' onclick=\"RecordNavigation('".$noofpag."','prev','".$start."','".$limit_row4pag."','".$noofrows."','".$filter_json."','".$this->reportid."');Loading_rep();\" href='javascript:;'>
					<img border='0' align='absmiddle' src='themes/softed/images/previous_disabled.gif'>
				</a>
				<input class='small' type='text' onchange=\"RecordNavigation('".$noofpag."','current','".$start."','".$limit_row4pag."','".$noofrows."','".$filter_json."','".$this->reportid."');Loading_rep();\" style='width: 3em;margin-right: 0.7em;' value='".$start."' id='pagenum' name='pagenum'>
				<span class='small' style='white-space: nowrap;' name='Contacts_listViewCountContainerName'>di ".$noofpag."</span>
				<a title='Prossimo' alt='Prossimo' onclick=\"RecordNavigation('".$noofpag."','next','".$start."','".$limit_row4pag."','".$noofrows."','".$filter_json."','".$this->reportid."');Loading_rep();\" href='javascript:;'>
					<img border='0' align='absmiddle' src='themes/softed/images/next.gif'>
				</a>
				<a title='Ultimo' alt='Ultimo' onclick=\"RecordNavigation('".$noofpag."','last','".$start."','".$limit_row4pag."','".$noofrows."','".$filter_json."','".$this->reportid."');Loading_rep();\" href='javascript:;'>
					<img border='0' align='absmiddle' src='themes/softed/images/end.gif'>
				</a>
				<td id='nav_buttons' width='40%' align='left' style='padding:5px;'>
				<span id='loading_img' valign='bottom' align='left' style='display: none;'>
					<img border='0' src='themes/images/vtbusy.gif'>
				</span>
				</td>
				</td>
			</tr>";

			$coltotalhtml .= "</table>";
			if($directOutput) {
				echo $coltotalhtml;
				$coltotalhtml = '';
			}
			return $coltotalhtml;

		}elseif($outputformat == "COUNT" || $outputformat == "COUNTXLS"){

			$operations = array('SUM', 'AVG', 'MIN', 'MAX');

			$sSQL = $this->sGetSQLforReport($this->reportid,$filterlist,$outputformat);
			$result = $adb->query($sSQL);
			$this->total_count = $adb->num_rows($result);
			$counttotals = array();
			if (is_array($this->_columnssumlist) && count($this->_columnssumlist) > 0) {
				// suddivido i campi dei totali per tipo
				foreach ($this->_columnssumlist as $colspec=>$sqlstr) {
					$colfields = explode(':', $colspec);
					$oper = $colfields[3];
					$oper = substr($oper, strrpos($oper, '_')+1);
					if (in_array($oper, $operations)) {
						// find module for this field
						if (preg_match('/"([^_]+)_.*'.$oper.'"/', $sqlstr, $matches) !== false) {
							if (getTabid($matches[1]) > 0) {
								// genero query
								$totmod = CRMEntity::getInstance($matches[1]);
								list($xx, $tab, $col) = explode(':', $colspec);
								$tabidx = $totmod->tab_name_index[$tab];
								if (empty($tabidx) && substr($tab, 0, 26) == $table_prefix.'_inventoryproductrel') { // caso inventory
									$tab = $table_prefix.'_inventoryproductrel';
									$tabidx = 'productid';
									$qr = "select $tab.$col as counttotal from $tab where $tabidx = ? and id = ?";
								} else { // caso campo standard
									$qr = "select $tab.$col as counttotal from $tab where $tabidx = ?";
								}

								$counttotals[$oper][] = array('colspec'=>$colspec, 'sqlstr'=>$sqlstr, 'module'=>$matches[1], 'query'=>$qr);
							}
						}
					}
				}
			}

			//$this->result_query_complete = $result;
			/////////////////////////////////////////
			///tabelle di conteggio
			$lev1_reportid = $this->reportid;
			$tot_group = $this->groupbylist;
			if (!empty($tot_group) && $array_lvl_count[1] != 0) {
			foreach ($tot_group as $key => $val){
				$lev_group[] = explode(" ", $val);
			}

			$real_groupcount = count($tot_group);
			if(in_array($this->primarymodule, array('Invoice', 'Quotes', 'SalesOrder', 'PurchaseOrder')) ) {
				$real_groupcount -= 2; // tolgo i 2 campi aggiunti per moduli con prodotti
			}

			//crmv@30976
			$transform1 = null;
			$transform2 = null;
			$transform3 = null;
			if ($real_groupcount == 1){
				$liv1_group = $lev_group[0][0];
				$module1 = substr($lev_group[0][1], 0, strpos($lev_group[0][1], '_'));

				$liv1_groupname = strtolower(trim($lev_group[0][1]));
			}elseif($real_groupcount == 2){
				$liv1_group = $lev_group[0][0];
				$liv2_group = $lev_group[1][0];
				$module1 = substr($lev_group[0][1], 0, strpos($lev_group[0][1], '_'));
				$module2 = substr($lev_group[1][1], 0, strpos($lev_group[1][1], '_'));

				$liv1_groupname = strtolower(trim($lev_group[0][1]));
				$liv2_groupname = strtolower(trim($lev_group[1][1]));
			}elseif($real_groupcount >= 3){
				$liv1_group = $lev_group[0][0];
				$liv2_group = $lev_group[1][0];
				$liv3_group = $lev_group[2][0];
				$module1 = substr($lev_group[0][1], 0, strpos($lev_group[0][1], '_'));
				$module2 = substr($lev_group[1][1], 0, strpos($lev_group[1][1], '_'));
				$module3 = substr($lev_group[2][1], 0, strpos($lev_group[2][1], '_'));

				$liv1_groupname = strtolower(trim($lev_group[0][1]));
				$liv2_groupname = strtolower(trim($lev_group[1][1]));
				$liv3_groupname = strtolower(trim($lev_group[2][1]));
			}
			if (array_key_exists($lev_group[0][1], $this->picklist_fields)) {
				$transform1 = create_function('$v', 'return getTranslatedString($v, "'.$module1.'");');
			}elseif (in_array($lev_group[0][1], $this->ui10_fields)) {
				$transform1 = create_function('$v', '$xx = getEntityName(getSalesEntityType($v), $v); if (is_array($xx)) return $xx[$v]; else return $v ;');
			}elseif (array_key_exists($lev_group[0][1], $this->multipicklist_fields)) {
				$transform1 = create_function('$v', 'return PicklistMulti::getTranslatedPicklist($v, "'.$this->multipicklist_fields[$lev_group[0][1]].'");');
			}
			
			
			if (array_key_exists($lev_group[1][1], $this->picklist_fields)) {
				$transform2 = create_function('$v', 'return getTranslatedString($v, "'.$module2.'");');
			}elseif (in_array($lev_group[1][1], $this->ui10_fields)) {
				$transform2 = create_function('$v', '$xx = getEntityName(getSalesEntityType($v), $v); if (is_array($xx)) return $xx[$v]; else return $v ;');
			}elseif (array_key_exists($lev_group[1][1], $this->multipicklist_fields)) {
				$transform2 = create_function('$v', 'return PicklistMulti::getTranslatedPicklist($v, "'.$this->multipicklist_fields[$lev_group[1][1]].'");');
			}
			
			if (array_key_exists($lev_group[2][1], $this->picklist_fields))  {
				$transform3 = create_function('$v', 'return getTranslatedString($v, "'.$module3.'");');
			}elseif (in_array($lev_group[2][1], $this->ui10_fields)) {
				$transform3 = create_function('$v', '$xx = getEntityName(getSalesEntityType($v), $v); if (is_array($xx)) return $xx[$v]; else return $v ;');
			}elseif (array_key_exists($lev_group[2][1], $this->multipicklist_fields)) {
				$transform3 = create_function('$v', 'return PicklistMulti::getTranslatedPicklist($v, "'.$this->multipicklist_fields[$lev_group[2][1]].'");');
			}
			//crmv@30976e
			// crmv@30385
			$liv1_groupname = substr($liv1_groupname, 0, 29);
			$liv2_groupname = substr($liv2_groupname, 0, 29);
			$liv3_groupname = substr($liv3_groupname, 0, 29);
			// crmv@30385e

			if(isset($lev_group[0][1]))
			$modulename1 = substr($lev_group[0][1],0,strpos($lev_group[0][1],"_",1));
			if(isset($lev_group[1][1]))
			$modulename2 = substr($lev_group[1][1],0,strpos($lev_group[1][1],"_",1));
			if(isset($lev_group[2][1]))
			$modulename3 = substr($lev_group[2][1],0,strpos($lev_group[2][1],"_",1));

			$intestaz1=str_replace('_',' ',str_replace($modulename1.'_', '', $lev_group[0][1]));
			$intestaz2=str_replace('_',' ',str_replace($modulename2.'_', '', $lev_group[1][1]));
			$intestaz3=str_replace('_',' ',str_replace($modulename2.'_', '', $lev_group[2][1]));

			$array1 = array();
			$array2 = array();
			$array3 = array();
			$ids_arr1 = array();
			$ids_arr2 = array();
			$livello1 = array();
			$livello2 = array();
			$livello3 = array();
			$cont_lv1 = 0;
			$cont_lv2 = 0;
			$cont_lv3 = 0;

			while($row = $adb->fetchByAssoc($result, -1, false)){ // crmv@31188
				//crmv@30976
				$lvl1_val = $row[$liv1_groupname];
				$lvl2_val = $row[$liv2_groupname];
				$lvl3_val = $row[$liv3_groupname];
				if (is_callable($transform1)) $lvl1_val = $transform1($lvl1_val);
				if (is_callable($transform2)) $lvl2_val = $transform2($lvl2_val);
				if (is_callable($transform3)) $lvl3_val = $transform3($lvl3_val);
				//crmv@30976e

				// livello 1
				if (isset($livello1['COUNT'][$lvl1_val])) {
					$livello1['COUNT'][$lvl1_val] += 1;
				}else{
					$livello1['COUNT'][$lvl1_val] = 1;
					$ids_arr1[$lvl1_val] = $adb->getUniqueID("vte_rep_count_liv1");
					$cont_lv1++;
				}
				// somme 1
				if (count($counttotals) > 0) {
					foreach ($operations as $oper) {
						if (!isset($livello1[$oper][$lvl1_val])) $livello1[$oper][$lvl1_val] = 0.0;
						$mod1 = $counttotals[$oper][0]['module'];
						$mod2 = $this->primarymodule; // altro modulo per prelevare l'ID (utile nel caso pesco da inventoryproductrel)
						$qr = $counttotals[$oper][0]['query'];
						if (empty($qr)) continue;
						$qrid1 = $row[strtolower('HIDDEN_'.$mod1.'_crmid')];
						$qrid2 = $row[strtolower('HIDDEN_'.$mod2.'_crmid')];
						$rr = $adb->pquery($qr, array($qrid1, $qrid2));
						if ($rr && $adb->num_rows($rr) > 0) {
							$val = $adb->query_result($rr, 0, 'counttotal');
							if ($val != '') {
								switch ($oper) {
									case 'SUM':	$livello1[$oper][$lvl1_val] += $val; break;
									case 'MIN':	$livello1[$oper][$lvl1_val] = min($livello1[$oper][$lvl1_val], $val); break;
									case 'MAX':	$livello1[$oper][$lvl1_val] = max($livello1[$oper][$lvl1_val], $val); break;
								}
							}
						}
					}
				}

				// livello 2
				if (isset($livello2[$lvl1_val][$ids_arr1[$lvl1_val]]['COUNT'][$lvl2_val])) {
					$livello2[$lvl1_val][$ids_arr1[$lvl1_val]]['COUNT'][$lvl2_val] += 1;
				}else{
					$livello2[$lvl1_val][$ids_arr1[$lvl1_val]]['COUNT'][$lvl2_val] = 1;
					$ids_arr2[$lvl1_val][$lvl2_val] = $adb->getUniqueID("vte_rep_count_liv2");
					$cont_lv2++;
				}
				// somme 2
				if (count($counttotals) > 0) {
					foreach ($operations as $oper) {
						if (!isset($livello2[$lvl1_val][$ids_arr1[$lvl1_val]][$oper][$lvl2_val])) $livello2[$lvl1_val][$ids_arr1[$lvl1_val]][$oper][$lvl2_val] = 0.0;
						$mod1 = $counttotals[$oper][0]['module'];
						$mod2 = $this->primarymodule; // altro modulo per prelevare l'ID
						$qr = $counttotals[$oper][0]['query'];
						if (empty($qr)) continue;
						$qrid1 = $row[strtolower('HIDDEN_'.$mod1.'_crmid')];
						$qrid2 = $row[strtolower('HIDDEN_'.$mod2.'_crmid')];
						$rr = $adb->pquery($qr, array($qrid1, $qrid2));
						if ($rr && $adb->num_rows($rr) > 0) {
							$val = $adb->query_result($rr, 0, 'counttotal');
							if ($val != '') {
								switch ($oper) {
									case 'SUM':	$livello2[$lvl1_val][$ids_arr1[$lvl1_val]][$oper][$lvl2_val] += $val; break;
									case 'MIN':	$livello2[$lvl1_val][$ids_arr1[$lvl1_val]][$oper][$lvl2_val] = min($livello2[$lvl1_val][$ids_arr1[$lvl1_val]][$oper][$lvl2_val], $val); break;
									case 'MAX':	$livello2[$lvl1_val][$ids_arr1[$lvl1_val]][$oper][$lvl2_val] = max($livello2[$lvl1_val][$ids_arr1[$lvl1_val]][$oper][$lvl2_val], $val); break;
								}
							}
						}
					}
				}

				// livello 3
				if (isset($livello3[$lvl1_val][$lvl2_val][$ids_arr2[$lvl1_val][$lvl2_val]]['COUNT'][$lvl3_val])) {
					$livello3[$lvl1_val][$lvl2_val][$ids_arr2[$lvl1_val][$lvl2_val]]['COUNT'][$lvl3_val] += 1;
				}else{
					$livello3[$lvl1_val][$lvl2_val][$ids_arr2[$lvl1_val][$lvl2_val]]['COUNT'][$lvl3_val] = 1;
					$cont_lv3++;
				}
				// somme 3
				if (count($counttotals) > 0) {
					foreach ($operations as $oper) {
						if (!isset($livello3[$lvl1_val][$lvl2_val][$ids_arr2[$lvl1_val][$lvl2_val]][$oper][$lvl3_val])) $livello3[$lvl1_val][$lvl2_val][$ids_arr2[$lvl1_val][$lvl2_val]][$oper][$lvl3_val] = 0.0;
						$mod1 = $counttotals[$oper][0]['module'];
						$mod2 = $this->primarymodule; // altro modulo per prelevare l'ID
						$qr = $counttotals[$oper][0]['query'];
						if (empty($qr)) continue;
						$qrid1 = $row[strtolower('HIDDEN_'.$mod1.'_crmid')];
						$qrid2 = $row[strtolower('HIDDEN_'.$mod2.'_crmid')];
						$rr = $adb->pquery($qr, array($qrid1, $qrid2));
						if ($rr && $adb->num_rows($rr) > 0) {
							$val = $adb->query_result($rr, 0, 'counttotal');
							if ($val != '') {
								$livello3[$lvl1_val][$lvl2_val][$ids_arr2[$lvl1_val][$lvl2_val]][$oper][$lvl3_val] += $val; // sum
								switch ($oper) {
									case 'SUM':	$livello3[$lvl1_val][$lvl2_val][$ids_arr2[$lvl1_val][$lvl2_val]][$oper][$lvl3_val] += $val; break;
									// AVG:  done at the end
									case 'MIN':	$livello3[$lvl1_val][$lvl2_val][$ids_arr2[$lvl1_val][$lvl2_val]][$oper][$lvl3_val] = min($livello3[$lvl1_val][$lvl2_val][$ids_arr2[$lvl1_val][$lvl2_val]][$oper][$lvl3_val], $val); break;
									case 'MAX':	$livello3[$lvl1_val][$lvl2_val][$ids_arr2[$lvl1_val][$lvl2_val]][$oper][$lvl3_val] = max($livello3[$lvl1_val][$lvl2_val][$ids_arr2[$lvl1_val][$lvl2_val]][$oper][$lvl3_val], $val); break;
								}
							}
						}
					}
				}

				//array finali pronti per l'insert
				$array1[$ids_arr1[$lvl1_val]] = array(
					'id_liv1' => $ids_arr1[$lvl1_val],
					'reportid' => $lev1_reportid,
					'value_liv1' => $lvl1_val,
					'count_liv1' => $livello1['COUNT'][$lvl1_val],
					'sum_liv1' => $livello1['SUM'][$lvl1_val],
					'avg_liv1' => ($livello1['COUNT'][$lvl1_val] > 0) ? ($livello1['SUM'][$lvl1_val] / $livello1['COUNT'][$lvl1_val]) : 0.0,
					'min_liv1' => $livello1['MIN'][$lvl1_val],
					'max_liv1' => $livello1['MAX'][$lvl1_val],
				);

				$array2[$ids_arr2[$lvl1_val][$lvl2_val]] = array(
					'id_liv1' => $ids_arr1[$lvl1_val],
					'id_liv2' => $ids_arr2[$lvl1_val][$lvl2_val],
					'value_liv2' => $lvl2_val,
					'count_liv2' => $livello2[$lvl1_val][$ids_arr1[$lvl1_val]]['COUNT'][$lvl2_val],
					'sum_liv2' => $livello2[$lvl1_val][$ids_arr1[$lvl1_val]]['SUM'][$lvl2_val],
					'avg_liv2' => ($array2[$cont_lv2]['count_liv2'] > 0) ? ($array2[$cont_lv2]['sum_liv2'] / $array2[$cont_lv2]['count_liv2']) : 0.0,
					'min_liv2' => $livello2[$lvl1_val][$ids_arr1[$lvl1_val]]['MIN'][$lvl2_val],
					'max_liv2' => $livello2[$lvl1_val][$ids_arr1[$lvl1_val]]['MAX'][$lvl2_val],
				);

				if ($lvl3_val == null) $lvl3_val = '';

				$array3[$ids_arr2[$lvl1_val][$lvl2_val]] = array(
					'id_liv1' => $ids_arr1[$lvl1_val],
					'id_liv2' => $ids_arr2[$lvl1_val][$lvl2_val],
					'value_liv3' => $lvl3_val,
					'count_liv3' => $livello3[$lvl1_val][$lvl2_val][$ids_arr2[$lvl1_val][$lvl2_val]]['COUNT'][$lvl3_val],
					'sum_liv3' => $livello3[$lvl1_val][$lvl2_val][$ids_arr2[$lvl1_val][$lvl2_val]]['SUM'][$lvl3_val],
					'avg_liv3' => ($array3[$ids_arr2[$lvl1_val][$lvl2_val]]['count_liv3'] > 0) ? ($array3[$ids_arr2[$lvl1_val][$lvl2_val]]['sum_liv3'] / $array3[$ids_arr2[$lvl1_val][$lvl2_val]]['count_liv3']) : 0.0,
					'min_liv3' => $livello3[$lvl1_val][$lvl2_val][$ids_arr2[$lvl1_val][$lvl2_val]]['MIN'][$lvl3_val],
					'max_liv3' => $livello3[$lvl1_val][$lvl2_val][$ids_arr2[$lvl1_val][$lvl2_val]]['MAX'][$lvl3_val],
				);

			}

			// TODO: se la subselect ha più di 500 valori oracle esplode
			$delete_lvl3 = "DELETE FROM vte_rep_count_liv3 WHERE id_liv1 IN (SELECT id_liv1 FROM vte_rep_count_liv1 WHERE reportid = ?)";
			$deleteresult_lvl3 = $adb->pquery($delete_lvl3, array($lev1_reportid));
			$delete_lvl2 = "DELETE FROM vte_rep_count_liv2 WHERE id_liv1 IN (SELECT id_liv1 FROM vte_rep_count_liv1 WHERE reportid = ?)";
			$deleteresult_lvl2 = $adb->pquery($delete_lvl2, array($lev1_reportid));
			$delete_lvl1 = "DELETE FROM vte_rep_count_liv1 WHERE reportid = ?";
			$deleteresult_lvl1 = $adb->pquery($delete_lvl1, array($lev1_reportid));

			foreach($array1 as $lv1=>$val1){
				$insert_test_lvl1 = "insert into vte_rep_count_liv1 (id_liv1,reportid,value_liv1,count_liv1,formula1_sum,formula1_avg,formula1_min,formula1_max) values (?,?,?,?,?,?,?,?)";
				$insertresult_lvl1 = $adb->pquery($insert_test_lvl1, array($val1['id_liv1'],$val1['reportid'],$val1['value_liv1'],$val1['count_liv1'], $val1['sum_liv1'],$val1['avg_liv1'], $val1['min_liv1'], $val1['max_liv1']));
			}

			foreach($array2 as $lv2=>$val2){
				$insert_test_lvl2 = "insert into vte_rep_count_liv2 (id_liv1,id_liv2,value_liv2,count_liv2,formula2_sum,formula2_avg,formula2_min,formula2_max) values (?,?,?,?,?,?,?,?)";
				$insertresult_lvl2 = $adb->pquery($insert_test_lvl2, array($val2['id_liv1'],$val2['id_liv2'],$val2['value_liv2'],$val2['count_liv2'],$val2['sum_liv2'],$val2['avg_liv2'],$val2['min_liv2'],$val2['max_liv2']));
			}

			foreach($array3 as $lv3=>$val3){
				$insert_test_lvl3 = "insert into vte_rep_count_liv3 (id_liv1,id_liv2,value_liv3,count_liv3,formula3_sum,formula3_avg,formula3_min,formula3_max) values (?,?,?,?,?,?,?,?)";
				$insertresult_lvl3 = $adb->pquery($insert_test_lvl3, array($val3['id_liv1'],$val3['id_liv2'],$val3['value_liv3'],$val3['count_liv3'],$val3['sum_liv3'],$val3['avg_liv3'],$val3['min_liv3'],$val3['max_liv3']));
			}

			// aggiorno la tabella con i conti se non ci sono filtri
			if (empty($filterlist)){

				$delete_tot_tab = "DELETE FROM vte_rep_count_levels WHERE reportid = ?";
				$deleteresult_tot = $adb->pquery($delete_tot_tab, array($this->reportid));

				$sql_value4tottable = $adb->pquery(
					"SELECT *
					FROM vte_rep_count_liv1
						INNER JOIN vte_rep_count_liv2
							ON vte_rep_count_liv1.id_liv1 = vte_rep_count_liv2.id_liv1
						INNER JOIN vte_rep_count_liv3
							ON vte_rep_count_liv2.id_liv2 = vte_rep_count_liv3.id_liv2 AND vte_rep_count_liv2.id_liv1 = vte_rep_count_liv3.id_liv1
					WHERE vte_rep_count_liv1.reportid = ?
					ORDER BY 1",
					array($this->reportid)
				);

				if($sql_value4tottable) {
					while($row = $adb->fetchByAssoc($sql_value4tottable)){
						$insert_tot_table =
							"insert into vte_rep_count_levels
								(id_liv1,reportid,value_liv1,count_liv1,id_liv2,value_liv2,count_liv2,value_liv3,count_liv3,
								formula1_sum,formula1_avg,formula1_min,formula1_max,
								formula2_sum,formula2_avg,formula2_min,formula2_max,
								formula3_sum,formula3_avg,formula3_min,formula3_max)
								values (?,?,?,?,?,?,?,?,?, ?,?,?,?,?,?,?,?,?,?,?,?)";
						$insertresult_tot = $adb->pquery($insert_tot_table,
							array($row["id_liv1"], $row["reportid"], $row["value_liv1"], $row["count_liv1"], $row["id_liv2"], $row["value_liv2"], $row["count_liv2"], $row["value_liv3"], $row["count_liv3"],
								$row['formula1_sum'],$row['formula1_avg'],$row['formula1_min'],$row['formula1_max'],
								$row['formula2_sum'],$row['formula2_avg'],$row['formula2_min'],$row['formula2_max'],
								$row['formula3_sum'],$row['formula3_avg'],$row['formula3_min'],$row['formula3_max'],
							)
						);
					}
				}
			}

			$result->MoveFirst();
			////////////////////////////////

			//intestazione tabella
			$coltotalxls = array();
			$coltotalxls_keys = array();
			$coltotalhtml .= "<table align='center' width='60%' cellpadding='3' cellspacing='0' border='0' class='rptTable printReport'><thead><tr class=\"reportRowTitle\">";
			if($array_lvl_count[1] == 1){
				$coltotalhtml .= "<th class='rptCellLabel'><b>".getTranslatedString($intestaz1, $modulename1)."</b></th>";  //creare label
				$coltotalxls_keys[] = getTranslatedString($intestaz1, $modulename1);
			}
			if($array_lvl_count[2] == 1){
				$coltotalhtml .= "<th class='rptCellLabel'><b>".getTranslatedString($intestaz2, $modulename2)."</b></th>"; //creare label
				$coltotalxls_keys[] = getTranslatedString($intestaz2, $modulename2);
			}
			if($array_lvl_count[3] == 1){
				$coltotalhtml .= "<th class='rptCellLabel'><b>".getTranslatedString($intestaz3, $modulename3)."</b></th>"; //creare label
				$coltotalxls_keys[] = getTranslatedString($intestaz3, $modulename3);
			}
			$coltotalhtml .= "<th class='rptCellLabel'><b>".getTranslatedString('LBL_HOME_COUNT', 'APP_STRINGS')."</b></th>";
			$coltotalxls_keys[] = getTranslatedString('LBL_HOME_COUNT', 'APP_STRINGS');
			// colonne con calcoli
			if (count($counttotals) > 0) {
				foreach ($counttotals as $oper=>$operdata) {
					// retrieve field name
					list($xx, $xx, $xx, $fieldlabel) = explode(':',$operdata[0]['colspec']);
					$labelmodule = $operdata[0]['module'];
					$fieldlabel = substr($fieldlabel, 0, strrpos($fieldlabel, '_'.$oper));
					$coltotalhtml .= "<th class='rptCellLabel'><b>".getTranslatedString($oper, 'Reports')." (".getTranslatedString($fieldlabel, $labelmodule).")</b></th>";
					$coltotalxls_keys[] = getTranslatedString($oper, 'Reports')." (".getTranslatedString($fieldlabel, $labelmodule).")";
				}
			}
			$coltotalhtml  .= '</tr></thead><tbody>';

			//$no_of_tr = self::get_no_of_tr($array_lvl_count);

			// TODO: perchè rileggo da db?
			$sql_value4table = $adb->pquery("SELECT * FROM vte_rep_count_liv1
									INNER JOIN vte_rep_count_liv2 ON vte_rep_count_liv1.id_liv1 = vte_rep_count_liv2.id_liv1
									INNER JOIN vte_rep_count_liv3 ON vte_rep_count_liv2.id_liv2 = vte_rep_count_liv3.id_liv2
									AND vte_rep_count_liv2.id_liv1 = vte_rep_count_liv3.id_liv1
									WHERE vte_rep_count_liv1.reportid = ? ORDER BY vte_rep_count_liv1.value_liv1, vte_rep_count_liv2.value_liv2", array($this->reportid));
			if($sql_value4table) {
				$array_levels = array();
				while($row = $adb->fetchByAssoc($sql_value4table)){
					if($row['value_liv2']=='') {
						$row['value_liv2']='-';
					}
					if($row['value_liv1']=='') {
						$row['value_liv1']='-';
					}
					if($row['value_liv3']=='') {
						$row['value_liv3']='-';
					}

					$array_levels[$row['value_liv1']]['calculation']['count'] = $row['count_liv1'];
					$array_levels[$row['value_liv1']]['calculation']['sum'] = $row['formula1_sum'];
					$array_levels[$row['value_liv1']]['calculation']['avg'] = $row['formula1_avg'];
					$array_levels[$row['value_liv1']]['calculation']['min'] = $row['formula1_min'];
					$array_levels[$row['value_liv1']]['calculation']['max'] = $row['formula1_max'];
					$array_levels[$row['value_liv1']]['level2'][$row['value_liv2']]['calculation']['count']=$row['count_liv2'];
					$array_levels[$row['value_liv1']]['level2'][$row['value_liv2']]['calculation']['sum']=$row['formula2_sum'];
					$array_levels[$row['value_liv1']]['level2'][$row['value_liv2']]['calculation']['avg']=$row['formula2_avg'];
					$array_levels[$row['value_liv1']]['level2'][$row['value_liv2']]['calculation']['min']=$row['formula2_min'];
					$array_levels[$row['value_liv1']]['level2'][$row['value_liv2']]['calculation']['max']=$row['formula2_max'];
					$array_levels[$row['value_liv1']]['level2'][$row['value_liv2']]['level3'][$row['value_liv3']]['calculation']['count']=$row['count_liv3'];
					$array_levels[$row['value_liv1']]['level2'][$row['value_liv2']]['level3'][$row['value_liv3']]['calculation']['sum']=$row['formula3_sum'];
					$array_levels[$row['value_liv1']]['level2'][$row['value_liv2']]['level3'][$row['value_liv3']]['calculation']['avg']=$row['formula3_avg'];
					$array_levels[$row['value_liv1']]['level2'][$row['value_liv2']]['level3'][$row['value_liv3']]['calculation']['min']=$row['formula3_min'];
					$array_levels[$row['value_liv1']]['level2'][$row['value_liv2']]['level3'][$row['value_liv3']]['calculation']['max']=$row['formula3_max'];
				}
			}

			$count1 = $count2 = $count3 = 0;
			$rowcount = 0;
			foreach($array_levels as $val => $lvl1) {
				$coltotalhtml .= '<tr class="reportRow'.($rowcount % 2).'">';
				$coltotalxls_row = array();
				//IF PRIMO ELEMENTO DEL LIVELLO 1 NON STAMPARE LA LABEL
				if($count1 == 0) {
					if ($array_lvl_count[2] != 0) {
						$bracket_count = ' (<b>'.$lvl1['calculation']['count'].'</b>)';
						if (count($counttotals) > 0) {
							foreach ($counttotals as $oper=>$operdata) {
								$colname = strtolower('formula1_'.$oper);
								$operval = $lvl1['calculation'][strtolower($oper)];
								$bracket_count .= "<br />\n".getTranslatedString($oper, 'Reports').': '.number_format($operval, 2, ',', '.');
							}
						}
					} else {
						$bracket_count = '';
					}

					$coltotalhtml .= '<td class="rptTotal">'.getTranslatedString($val, $modulename1).$bracket_count.'</td>';
					$coltotalxls_row[] = getTranslatedString($val, $modulename1).strip_tags($bracket_count);
				}

				$count1++;
				if($array_lvl_count[2] == 0 && $array_lvl_count[3]==0){
					$coltotalhtml .= '<td class="rptTotal">'.$lvl1['calculation']['count'].'</td>';
					$coltotalxls_row[] = $lvl1['calculation']['count'];
					if (count($counttotals) > 0) {
						foreach ($counttotals as $oper=>$operdata) {
							$colname = strtolower('formula1_'.$oper);
							$operval = $lvl1['calculation'][strtolower($oper)];
							$coltotalhtml .= '<td class="rptTotal">'.number_format($operval, 2, ',', '.').'</td>';
							$coltotalxls_row[] = $operval;
						}
					}
					$coltotalhtml .= '</tr>';

					$coltotalxls[] = array_combine($coltotalxls_keys, $coltotalxls_row);
					$coltotalxls_row = array();
					++$rowcount;
					$count1 = 0;
				} else {
					foreach($lvl1['level2'] as $val2 => $lvl2 ){

						//IF PRIMO ELEMENTO LEVEL2 NON AGGIUNGERE IL TR
						if($count2==0 && $count1==0){

						}elseif($count2 != 0){
							$coltotalhtml .= '<tr class="reportRow'.($rowcount % 2).'"><td class="rptTotal">&nbsp;</td>';
							$coltotalxls_row[] = '';
						}else{

						}

						if($count1 == 0 && ($array_lvl_count[1] != 0 && $array_lvl_count[2]!=0)){  // disegna il primo td nel secondo foreach
							$coltotalhtml .= '<tr class="reportRow'.($rowcount % 2).'"><td class="rptTotal">&nbsp;</td>';
							$coltotalxls_row[] = '';
						}

						$count2++;
						if ($array_lvl_count[2] == 0) {

						} else {
							if ($array_lvl_count[3] != 0) {
								$bracket_count = ' (<b>'.$lvl2['calculation']['count'].'</b>)';
								if (count($counttotals) > 0) {
									foreach ($counttotals as $oper=>$operdata) {
										$colname = strtolower('formula2_'.$oper);
										$operval = $lvl2['calculation'][strtolower($oper)];
										$bracket_count .= "<br />\n".getTranslatedString($oper, 'Reports').': '.number_format($operval, 2, ',', '.');
									}
								}
							} else {
								$bracket_count = '';
							}
							$coltotalhtml .= '<td class="rptTotal">'.getTranslatedString($val2, $modulename2).$bracket_count.'</td>';
							$coltotalxls_row[] = getTranslatedString($val2, $modulename2).strip_tags($bracket_count);
						}

						if(sizeof($lvl1['level2']) == $count2){
							$count2 = 0;
							$count1 = 0;
						}

						if($array_lvl_count[3] == 0){
							$coltotalhtml .= '<td class="rptTotal">'.$lvl2['calculation']['count'].'</td>';
							$coltotalxls_row[] = $lvl2['calculation']['count'];
							if (count($counttotals) > 0) {
								foreach ($counttotals as $oper=>$operdata) {
									$colname = strtolower('formula2_'.$oper);
									$operval = $lvl2['calculation'][strtolower($oper)];
									$coltotalhtml .= '<td class="rptTotal">'.number_format($operval, 2, ',', '.').'</td>';
									$coltotalxls_row[] = $operval;
								}
							}
							$coltotalhtml .= '</tr>';
							$coltotalxls[] = array_combine($coltotalxls_keys, $coltotalxls_row);
							$coltotalxls_row = array();
							++$rowcount;
							$count2 = 0;
							$count1 = 0;
						} else {
							foreach($lvl2['level3'] as $val3 => $lvl3 ){

							//IF PRIMO ELEMENTO LEVEL3 NON AGGIUNGERE IL TR
								if($count3 != 0){
									$coltotalhtml .= '<tr class="reportRow'.($rowcount % 2).'"><td class="rptTotal">&nbsp;</td><td class="rptTotal">&nbsp;</td>';
									$coltotalxls_row[] = '';
									$coltotalxls_row[] = '';
								}
								$count3++;
								$coltotalhtml .= '<td class="rptTotal">'.getTranslatedString($val3, $modulename3).'</td>';
								$coltotalhtml .= '<td class="rptTotal">'.$lvl3['calculation']['count'].'</td>';
								$coltotalxls_row[] = getTranslatedString($val3, $modulename3);
								$coltotalxls_row[] = $lvl3['calculation']['count'];

								if (count($counttotals) > 0) {
									foreach ($counttotals as $oper=>$operdata) {
										$colname = strtolower('formula3_'.$oper);
										$operval = $lvl3['calculation'][strtolower($oper)];
										$coltotalhtml .= '<td class="rptTotal">'.number_format($operval, 2, ',', '.').'</td>';
										$coltotalxls_row[] = $operval;
									}
								}
								$coltotalhtml .= "</tr>\n";

								$coltotalxls[] = array_combine($coltotalxls_keys, $coltotalxls_row);
								$coltotalxls_row = array();
								++$rowcount;

								if(sizeof($lvl2['level3']) == $count3){
									$count3 = 0;
								}
							}
						}
					}
				}

			}

			$coltotalhtml .= "</tbody></table>\n";
			}
			// Performation Optimization: If Direct output is desired
			if($directOutput) {
				echo $coltotalhtml;
				$coltotalhtml = '';
			}
			if ($outputformat == 'COUNTXLS')
				return $coltotalxls;
			else
				return $coltotalhtml;
		//crmv@31775
		}elseif($outputformat == 'CV_RPRT')
		{
			$this->report_module = $filterlist['module'];
			$sSQL = $this->sGetSQLforReport($this->reportid,array(),$outputformat);
			$customView = new CustomView($this->report_module);
			$customView->createReportFilterTable($this->reportid,$current_user->id,$sSQL);
		//crmv@31775e
		}elseif($outputformat == "HTML" || $outputformat == "PDF")//crmv@29686
		{
			//crmv@29686

			$sSQL = $this->sGetSQLforReport($this->reportid,$filterlist,$outputformat);
			if ($_REQUEST['limit_string'] == 'ALL') {
				$result = $adb->query($sSQL);
			} elseif (!empty($_REQUEST['limit_string'])){
				list($qstart, $qend) = explode(',', $_REQUEST['limit_string']);
				$result = $adb->limitQuery($sSQL, $qstart, $qend);
			} else {
				$result = $adb->limitQuery($sSQL, 0, $limit_row4pag);
			}

			//crmv@29686e
			$error_msg = $adb->database->ErrorMsg();
			if(!$result && $error_msg!=''){
				// Performance Optimization: If direct output is requried
				if($directOutput) {
					echo getTranslatedString('LBL_REPORT_GENERATION_FAILED', $currentModule) . "<br>" . $error_msg;
					$error_msg = false;
				}
				// END
				return $error_msg;
			}
			// Performance Optimization: If direct output is required
			if($directOutput) {
				echo '<table cellpadding="5" cellspacing="0" align="center" class="rptTable"><thead><tr class="reportRowTitle">'; // crmv@29686
			}
			// END

			if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1)
				$picklistarray = $this->getAccessPickListValues();
			if($result)
			{
				$y=$adb->num_fields($result);
				$arrayHeaders = Array();
				for ($x=0; $x<$y; $x++)
				{
					$fld = $adb->field_name($result, $x);
					if (substr($fld->name, 0, 6) == 'HIDDEN') continue; //crmv@29686
					if(in_array($this->getLstringforReportHeaders($fld->name, false), $arrayHeaders))	//crmv@27624
					{
						$headerLabel = str_replace("_"," ",$fld->name);
						$arrayHeaders[] = $headerLabel;
					}
					else
					{
						$headerLabel = str_replace($modules," ",$this->getLstringforReportHeaders($fld->name));
						$headerLabel = str_replace("_"," ",$this->getLstringforReportHeaders($fld->name));
						$arrayHeaders[] = $headerLabel;
					}
					/*STRING TRANSLATION starts */
					$mod_name = explode(' ',$headerLabel,2);
					$module ='';
					if(in_array($mod_name[0],$modules_selected)){
						$module = getTranslatedString($mod_name[0],$mod_name[0]);
					}

					if(!empty($this->secondarymodule)){
						if($module!=''){
							$headerLabel_tmp = $module." ".getTranslatedString($mod_name[1],$mod_name[0]);
						} else {
							$headerLabel_tmp = getTranslatedString($mod_name[0]." ".$mod_name[1]);
						}
					} else {
						if($module!=''){
							$headerLabel_tmp = getTranslatedString($mod_name[1],$mod_name[0]);
						} else {
							$headerLabel_tmp = getTranslatedString($mod_name[0]." ".$mod_name[1]);
						}
					}
					if($headerLabel == $headerLabel_tmp) $headerLabel = getTranslatedString($headerLabel_tmp);
					else $headerLabel = $headerLabel_tmp;
					//crmv@21249
					if(in_array($fld->name, $this->convert_currency)) {
			        	$headerLabel.=" (".$app_strings['LBL_IN']." ".$current_user->currency_symbol.")";
					}
					//crmv@21249e
					/*STRING TRANSLATION ends */
					$header .= "<th class='rptCellLabel'>".$headerLabel."</th>"; //crmv@29686

					// Performance Optimization: If direct output is required
					if($directOutput) {
						echo $header;
						$header = '';
					}
					// END
				}

				// Performance Optimization: If direct output is required
				if($directOutput) {
					echo '</tr></thead><tbody><tr>'; //crmv@29686
				}
				// END


				$noofrows = $adb->num_rows($result);
				$custom_field_values = $adb->fetch_array($result);
				$groupslist = $this->getGroupingList($this->reportid);

				$column_definitions = $adb->getFieldsDefinition($result);
				//crmv@29686
				$count_limit = 0;

				$real_groupcount = count($groupslist);
				if(in_array($this->primarymodule, array('Invoice', 'Quotes', 'SalesOrder', 'PurchaseOrder')) ) {
					$real_groupcount -= 2; // tolgo i 2 campi aggiunti per moduli con prodotti
				}
				$rowcount = 0;
				$valtemplate = '';
				//crmv@29686e

				do
				{
					$arraylists = Array();
					//crmv@29686
					if($real_groupcount == 1)
					{
						$newvalue = $custom_field_values[0];
					}elseif($real_groupcount == 2)
					{
						$newvalue = $custom_field_values[0];
						$snewvalue = $custom_field_values[1];
					}elseif($real_groupcount >= 3)
					{
						$newvalue = $custom_field_values[0];
						$snewvalue = $custom_field_values[1];
						$tnewvalue = $custom_field_values[2];
					}
					//crmv@29686e
					if($newvalue == "") $newvalue = "-";

					if($snewvalue == "") $snewvalue = "-";

					if($tnewvalue == "") $tnewvalue = "-";

					$valtemplate .= "<tr class=\"reportRow".(($rowcount++) % 2)."\">"; // crmv@29686

					// Performance Optimization
					if($directOutput) {
						echo $valtemplate;
						$valtemplate = '';
					}
					// END
					for ($i=0; $i<$y; $i++)
					{
						$fld = $adb->field_name($result, $i);

						//crmv@29686
						if (substr($fld->name, 0, 6) == 'HIDDEN') continue;
						$fld->module = substr($fld->name, 0, strpos($fld->name, '_'));
						//crmv@29686e

						$fld_type = $column_definitions[$i]->type;

						$cell_align = 'left'; // crmv@29686
						if ($fld_type == 'int' || $fld_type == 'real') $cell_align = 'right';

						//crmv@17001 : Private Permissions
						$fieldname = strtolower(str_replace($this->primarymodule.'_','',$fld->name));
						if ($this->primarymodule == 'Calendar' && $custom_field_values['lbl_action'] != '') {
							$ownerId = getUserId($custom_field_values['lbl_action']);
							$visibility = $adb->query_result($adb->query('select visibility from '.$table_prefix.'_activity where activityid = '.$custom_field_values['lbl_action']),0,'visibility');
						}
						//crmv@17001e
						if (in_array($fld->name, $this->convert_currency)) {
							if($custom_field_values[$i]!='')
								$fieldvalue = convertFromMasterCurrency($custom_field_values[$i],$current_user->conv_rate);
							else
								$fieldvalue = getTranslatedString($custom_field_values[$i]);
						} elseif(in_array($fld->name, $this->append_currency_symbol_to_value)) {
							$curid_value = explode("::", $custom_field_values[$i]);
							$currency_id = $curid_value[0];
							$currency_value = $curid_value[1];
							$cur_sym_rate = getCurrencySymbolandCRate($currency_id);
							if($custom_field_values[$i]!='')
								$fieldvalue = $cur_sym_rate['symbol']." ".$currency_value;
							else
								$fieldvalue = getTranslatedString($custom_field_values[$i]);
							$cell_align = 'right'; // crmv@29686
						}elseif ($fld->name == "PurchaseOrder_Currency" || $fld->name == "SalesOrder_Currency"
									|| $fld->name == "Invoice_Currency" || $fld->name == "Quotes_Currency") {
							if($custom_field_values[$i]!='')
								$fieldvalue = getCurrencyName($custom_field_values[$i]);
							else
								$fieldvalue =getTranslatedString($custom_field_values[$i]);
						}elseif (in_array($fld->name,$this->ui10_fields) && !empty($custom_field_values[$i])) {
							$type = getSalesEntityType($custom_field_values[$i]);
							$tmp =getEntityName($type,$custom_field_values[$i]);
							if (is_array($tmp)) { // crmv@29686
								foreach($tmp as $key=>$val){
									$fieldvalue = $val;
									break;
								}
							} else {
								$fieldvalue = $custom_field_values[$i];
							}
						//crmv@21249
						}elseif ($this->multipicklist_fields[$fld->name] != '' && !empty($custom_field_values[$i])) {
							$fieldvalue = Picklistmulti::getTranslatedPicklist($custom_field_values[$i],$this->multipicklist_fields[$fld->name]);
						}
						//crmv@21249e
						//mycrmv@24524
						elseif ($this->users_fields[$fld->name] != '' && !empty($custom_field_values[$i])) {
							$fieldvalue = getUserName($custom_field_values[$i]);
						}
						//crmv@21249e
						//crmv@18544
						elseif($fld->name == 'Documents_Note'){
							$fieldvalue = strip_tags(htmlspecialchars_decode($custom_field_values[$i]));
						}
						//crmv@18544 end
						else {
							if($custom_field_values[$i]!='')
								$fieldvalue = getTranslatedString($custom_field_values[$i]);
							else
								$fieldvalue = getTranslatedString($custom_field_values[$i]);
						}
						$fieldvalue = str_replace("<", "&lt;", $fieldvalue);
						$fieldvalue = str_replace(">", "&gt;", $fieldvalue);

					//check for Roll based pick list
						$temp_val= $fld->name;
						if(is_array($picklistarray))
							if(array_key_exists($temp_val,$picklistarray))
							{
								if(!in_array($custom_field_values[$i],$picklistarray[$fld->name]) && $custom_field_values[$i] != '')
									$fieldvalue =$app_strings['LBL_NOT_ACCESSIBLE'];

							}
						if(is_array($picklistarray[1]))
							if(array_key_exists($temp_val,$picklistarray[1]))
							{
								$temp =explode(",",str_ireplace(' |##| ',',',$fieldvalue));
								$temp_val = Array();
								foreach($temp as $key =>$val)
								{
										if(!in_array(trim($val),$picklistarray[1][$fld->name]) && trim($val) != '')
										{
											$temp_val[]=$app_strings['LBL_NOT_ACCESSIBLE'];
										}
										else
											$temp_val[]=$val;
								}
								$fieldvalue =(is_array($temp_val))?implode(", ",$temp_val):'';
							}

						//crmv@17001 : Private Permissions
						if ($this->primarymodule == 'Calendar' && !is_admin($current_user) && $ownerId != $current_user->id && $visibility == 'Private' && !in_array($fieldname,array('assigned_to','start_date_and_time','time_start','time_end','end_date','activity_type','visibility','duration','duration_minutes'))) {
							if ($fieldname == 'subject')
								$fieldvalue = getTranslatedString('Private Event','Calendar');
							else
								$fieldvalue = $app_strings['LBL_NOT_ACCESSIBLE'];
						}
						//crmv@17001e
						//crmv@30970
						if ($this->picklist_fields[$fld->name] != '') {
							$fieldvalue = getTranslatedString($fieldvalue,$fld->module);
						}
						//crmv@30970e
						if($fieldvalue == "" )
						{
							$fieldvalue = "-";
						}
						else if($fld->name == 'LBL_ACTION')
						{
							$fieldvalue = "<a href='index.php?module={$this->primarymodule}&action=DetailView&record={$fieldvalue}' target='_blank'>".getTranslatedString('LBL_VIEW_DETAILS')."</a>";
						}
						else if(stristr($fieldvalue,"|##|"))
						{
							$fieldvalue = str_ireplace(' |##| ',', ',$fieldvalue);
						}
						else if($fld_type == "date" || $fld_type == "datetime") {
							//crmv@fix date
							//TODO: can't check if date or datetime, get field id to take the right parameter of the field
							if ($fld_type == 'date' || strpos($fieldvalue,'00:00:00') !==false){
								$fieldvalue = substr($fieldvalue,0,10);
							}
							//crmv@fix date	end
							$fieldvalue = getDisplayDate($fieldvalue);
						}

						if(($lastvalue == $custom_field_values[$i]) && $this->reporttype == "summary") // crmv@30385
						{
							if($this->reporttype == "summary")
							{
								$valtemplate .= "<td class='rptEmptyGrp' align=\"$cell_align\">&nbsp;</td>";
							}else
							{
								$valtemplate .= "<td class='rptData' align=\"$cell_align\">".$fieldvalue."</td>";
							}
						}else if(($secondvalue === $custom_field_values[$i]) && $this->reporttype == "summary") // crmv@30385
						{
							if($lastvalue === $newvalue)
							{
								$valtemplate .= "<td class='rptEmptyGrp' align=\"$cell_align\">&nbsp;</td>";
							}else
							{
								$valtemplate .= "<td class='rptGrpHead' align=\"$cell_align\">".$fieldvalue."</td>";
							}
						}
						else if(($thirdvalue === $custom_field_values[$i]) && $this->reporttype == "summary") // crmv@30385
						{
							if($secondvalue === $snewvalue)
							{
								$valtemplate .= "<td class='rptEmptyGrp' align=\"$cell_align\">&nbsp;</td>";
							}else
							{
								$valtemplate .= "<td class='rptGrpHead' align=\"$cell_align\">".$fieldvalue."</td>";
							}
						}
						else
						{
							if($this->reporttype == "tabular")
							{
								$valtemplate .= "<td class='rptData' align=\"$cell_align\">".$fieldvalue."</td>";
							}else
							{
								$valtemplate .= "<td class='rptGrpHead' align=\"$cell_align\">".$fieldvalue."</td>";
							}
						}

						// Performance Optimization: If direct output is required
						if($directOutput) {
							echo $valtemplate;
							$valtemplate = '';
						}
						// END
					}

					$valtemplate .= "</tr>";

					// Performance Optimization: If direct output is required
					if($directOutput) {
						echo $valtemplate;
						$valtemplate = '';
					}
					// END
					$lastvalue = $newvalue;
					$secondvalue = $snewvalue;
					$thirdvalue = $tnewvalue;
					$arr_val[] = $arraylists;
					set_time_limit($php_max_execution_time);
					//crmv@29686
					$count_limit++;
					if ($count_limit >= $limit_row4pag && $_REQUEST['limit_string'] != 'ALL') {
						break;
					}
					//crmv@29686e
				}while($custom_field_values = $adb->fetch_array($result));

				// Performance Optimization
				//crmv@29686
				if($directOutput) {
					echo "</tr></tbody></table>";
					echo "<script type='text/javascript' id='__reportrun_directoutput_recordcount_script'>
						if($('_reportrun_total')) $('_reportrun_total').innerHTML=$noofrows;</script>";
				} else {
					$sHTML ='<table cellpadding="5" cellspacing="0" align="center" class="rptTable"><thead>
					<tr>'.$header.'</thead><!-- BEGIN values --><tbody>'.$valtemplate.'</tbody></table>';
				}
				//crmv@29686e
				//<<<<<<<<construct HTML>>>>>>>>>>>>
				$return_data[] = $sHTML;
				$return_data[] = $noofrows;
				$return_data[] = $sSQL;
				$return_data[] = $y; // number of columns - crmv@29686
				return $return_data;
			}
		}elseif($outputformat == "DEPRECATED_PDF" || $outputformat == "XLS")
		{

			$sSQL = $this->sGetSQLforReport($this->reportid,$filterlist,$outputformat);	//crmv@17001
			$result = $adb->query($sSQL);
			if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1)
			$picklistarray = $this->getAccessPickListValues();

			if($result)
			{
				$y=$adb->num_fields($result);
				$noofrows = $adb->num_rows($result);
				$custom_field_values = $adb->fetch_array($result);
				$column_definitions = $adb->getFieldsDefinition($result);

				do
				{
					$arraylists = Array();
					for ($i=0; $i<$y; $i++)
					{
						$fld = $adb->field_name($result, $i);
						// crmv@29686
						if ($fld->name == 'LBL_ACTION' || substr($fld->name, 0, 6) == 'HIDDEN') continue;
						$fld->module = substr($fld->name, 0, strpos($fld->name, '_'));
						// crmv@29686e
						$fld_type = $column_definitions[$i]->type;
						//crmv@17001 : Private Permissions
						$fieldname = strtolower(str_replace($this->primarymodule.'_','',$fld->name));
						if ($this->primarymodule == 'Calendar' && $custom_field_values['lbl_action'] != '') {
							$ownerId = getUserId($custom_field_values['lbl_action']);
							$visibility = $adb->query_result($adb->query('select visibility from '.$table_prefix.'_activity where activityid = '.$custom_field_values['lbl_action']),0,'visibility');
						}
						if ($i == ($y-1)) {
							unset($custom_field_values['lbl_action']);
							unset($custom_field_values[$i]);
							break;
						}
						//crmv@17001e
						if (in_array($fld->name, $this->convert_currency)) {
							$fieldvalue = convertFromMasterCurrency($custom_field_values[$i],$current_user->conv_rate);
						} elseif(in_array($fld->name, $this->append_currency_symbol_to_value)) {
							$curid_value = explode("::", $custom_field_values[$i]);
							$currency_id = $curid_value[0];
							$currency_value = $curid_value[1];
							$cur_sym_rate = getCurrencySymbolandCRate($currency_id);
							$fieldvalue = $cur_sym_rate['symbol']." ".$currency_value;
						}elseif ($fld->name == "PurchaseOrder_Currency" || $fld->name == "SalesOrder_Currency"
									|| $fld->name == "Invoice_Currency" || $fld->name == "Quotes_Currency") {
							$fieldvalue = getCurrencyName($custom_field_values[$i]);
						}elseif (in_array($fld->name,$this->ui10_fields) && !empty($custom_field_values[$i])) {
							$type = getSalesEntityType($custom_field_values[$i]);
							$tmp =getEntityName($type,$custom_field_values[$i]);
							if (is_array($tmp)) { // crmv@29686
								foreach($tmp as $key=>$val){
									$fieldvalue = $val;
									break;
								}
							} else {
								$fieldvalue = $custom_field_values[$i];
							}
						}
						//crmv@21249
						elseif ($this->multipicklist_fields[$fld->name] != '' && !empty($custom_field_values[$i])) {
							$fieldvalue = Picklistmulti::getTranslatedPicklist($custom_field_values[$i],$this->multipicklist_fields[$fld->name]);
						}
						//crmv@21249e
						//mycrmv@24524
						elseif ($this->users_fields[$fld->name] != '' && !empty($custom_field_values[$i])) {
							$fieldvalue = getUserName($custom_field_values[$i]);
						}
						//mycrmv@24524e
						//crmv@18544						
						elseif($fld->name == 'Documents_Note'){
							$fieldvalue = strip_tags(htmlspecialchars_decode($custom_field_values[$i]));
						}
						//crmv@18544 end
						else {
							$fieldvalue = getTranslatedString($custom_field_values[$i]);
						}
						$append_cur = str_replace($fld->name,"",decode_html($this->getLstringforReportHeaders($fld->name)));
						$headerLabel = str_replace("_"," ",$fld->name);
						/*STRING TRANSLATION starts */
						$mod_name = explode(' ',$headerLabel,2);
						$module ='';
						if(in_array($mod_name[0],$modules_selected))
							$module = getTranslatedString($mod_name[0],$mod_name[0]);

						if(!empty($this->secondarymodule)){
							if($module!=''){
								$headerLabel_tmp = $module." ".getTranslatedString($mod_name[1],$mod_name[0]);
							} else {
								$headerLabel_tmp = getTranslatedString($mod_name[0]." ".$mod_name[1]);
							}
						} else {
							if($module!=''){
								$headerLabel_tmp = getTranslatedString($mod_name[1],$mod_name[0]);
							} else {
								$headerLabel_tmp = getTranslatedString($mod_name[0]." ".$mod_name[1]);
							}
						}
						if($headerLabel == $headerLabel_tmp) $headerLabel = getTranslatedString($headerLabel_tmp);
						else $headerLabel = $headerLabel_tmp;
						//crmv@21249
						if(in_array($fld->name, $this->convert_currency)) {
				        	$headerLabel.=" (".$app_strings['LBL_IN']." ".utf8_decode("�").")";
						}
						//crmv@21249e
						/*STRING TRANSLATION starts */
						if(trim($append_cur)!="") $headerLabel .= $append_cur;

						$fieldvalue = str_replace("<", "&lt;", $fieldvalue);
						$fieldvalue = str_replace(">", "&gt;", $fieldvalue);

						// Check for role based pick list
						$temp_val= $fld->name;
						if(is_array($picklistarray))
							if(array_key_exists($temp_val,$picklistarray))
							{
								if(!in_array($custom_field_values[$i],$picklistarray[$fld->name]) && $custom_field_values[$i] != '')
								{
									$fieldvalue =$app_strings['LBL_NOT_ACCESSIBLE'];
								}
							}
						if(is_array($picklistarray[1]))
							if(array_key_exists($temp_val,$picklistarray[1]))
							{
								$temp =explode(",",str_ireplace(' |##| ',',',$fieldvalue));
								$temp_val = Array();
								foreach($temp as $key =>$val)
								{
										if(!in_array(trim($val),$picklistarray[1][$fld->name]) && trim($val) != '')
										{
											$temp_val[]=$app_strings['LBL_NOT_ACCESSIBLE'];
										}
										else
											$temp_val[]=$val;
								}
								$fieldvalue =(is_array($temp_val))?implode(", ",$temp_val):'';
							}

						//crmv@17001 : Private Permissions
						if ($this->primarymodule == 'Calendar' && !is_admin($current_user) && $ownerId != $current_user->id && $visibility == 'Private' && !in_array($fieldname,array('assigned_to','start_date_and_time','time_start','time_end','end_date','activity_type','visibility','duration','duration_minutes'))) {
							if ($fieldname == 'subject')
								$fieldvalue = getTranslatedString('Private Event','Calendar');
							else
								$fieldvalue = $app_strings['LBL_NOT_ACCESSIBLE'];
						}
						//crmv@17001e
						//crmv@30970
						if ($this->picklist_fields[$fld->name] != '') {
							$fieldvalue = getTranslatedString($fieldvalue,$fld->module);
						}
						
						//crmv@30970e
						if($fieldvalue == "" )
						{
							$fieldvalue = "-";
						}
						else if(stristr($fieldvalue,"|##|"))
						{
							$fieldvalue = str_ireplace(' |##| ',', ',$fieldvalue);
						}
						else if($fld_type == "date" || $fld_type == "datetime") {
							$fieldvalue = getDisplayDate($fieldvalue);
						}
						if(array_key_exists($this->getLstringforReportHeaders($fld->name), $arraylists))
							$arraylists[$headerLabel] = $fieldvalue;
						else
							$arraylists[$headerLabel] = $fieldvalue;
					}
					$arr_val[] = $arraylists;
					set_time_limit($php_max_execution_time);
				}while($custom_field_values = $adb->fetch_array($result));

				return $arr_val;
			}
		}elseif($outputformat == "TOTALXLS")
		{
				$escapedchars = Array('_SUM','_AVG','_MIN','_MAX');
				$totalpdf=array();
				$sSQL = $this->sGetSQLforReport($this->reportid,$filterlist,"COLUMNSTOTOTAL");
				if(isset($this->totallist))
				{
						if($sSQL != "")
						{
								$result = $adb->query($sSQL);
								$y=$adb->num_fields($result);
								$custom_field_values = $adb->fetch_array($result);

								foreach($this->totallist as $key=>$value)
								{
										$fieldlist = explode(":",$key);
										$mod_query = $adb->pquery("SELECT distinct(tabid) as tabid, uitype as uitype from ".$table_prefix."_field where tablename = ? and columnname=?",array($fieldlist[1],$fieldlist[2]));
										if($adb->num_rows($mod_query)>0){
												$module_name = getTabName($adb->query_result($mod_query,0,'tabid'));
												$fieldlabel = trim(str_replace($escapedchars," ",$fieldlist[3]));
												$fieldlabel = str_replace("_", " ", $fieldlabel);
												if($module_name){
														$field = getTranslatedString($module_name)." ".getTranslatedString($fieldlabel,$module_name);
												} else {
													$field = getTranslatedString($module_name)." ".getTranslatedString($fieldlabel);
												}
										}
										$uitype_arr[str_replace($escapedchars," ",$module_name."_".$fieldlist[3])] = $adb->query_result($mod_query,0,"uitype");
										$totclmnflds[str_replace($escapedchars," ",$module_name."_".$fieldlist[3])] = $field;
								}
								for($i =0;$i<$y;$i++)
								{
										$fld = $adb->field_name($result, $i);
										$keyhdr[$fld->name] = $custom_field_values[$i];
								}

								$rowcount=0;
								foreach($totclmnflds as $key=>$value)
								{
										$col_header = trim(str_replace($modules," ",$value));
										$fld_name_1 = $this->primarymodule . "_" . trim($value);
										$fld_name_2 = $this->secondarymodule . "_" . trim($value);
										if($uitype_arr[$value]==71 || in_array($fld_name_1,$this->convert_currency) || in_array($fld_name_1,$this->append_currency_symbol_to_value)
														|| in_array($fld_name_2,$this->convert_currency) || in_array($fld_name_2,$this->append_currency_symbol_to_value)) {
												$col_header .= " (".$app_strings['LBL_IN']." ".$current_user->currency_symbol.")";
												$convert_price = true;
										} else{
												$convert_price = false;
										}
										$value = trim($key);
										$arraykey = $value.'_SUM';
										if(isset($keyhdr[$arraykey]))
										{
												if($convert_price)
														$conv_value = convertFromMasterCurrency($keyhdr[$arraykey],$current_user->conv_rate);
												else
														$conv_value = $keyhdr[$arraykey];
												$totalpdf[$rowcount][$arraykey] = $conv_value;
										}else
										{
												$totalpdf[$rowcount][$arraykey] = '';
										}

										$arraykey = $value.'_AVG';
										if(isset($keyhdr[$arraykey]))
										{
												if($convert_price)
														$conv_value = convertFromMasterCurrency($keyhdr[$arraykey],$current_user->conv_rate);
												else
														$conv_value = $keyhdr[$arraykey];
												$totalpdf[$rowcount][$arraykey] = $conv_value;
										}else
										{
												$totalpdf[$rowcount][$arraykey] = '';
										}

										$arraykey = $value.'_MIN';
										if(isset($keyhdr[$arraykey]))
										{
												if($convert_price)
														$conv_value = convertFromMasterCurrency($keyhdr[$arraykey],$current_user->conv_rate);
												else
														$conv_value = $keyhdr[$arraykey];
												$totalpdf[$rowcount][$arraykey] = $conv_value;
										}else
										{
												$totalpdf[$rowcount][$arraykey] = '';
										}

										$arraykey = $value.'_MAX';
										if(isset($keyhdr[$arraykey]))
										{
												if($convert_price)
														$conv_value = convertFromMasterCurrency($keyhdr[$arraykey],$current_user->conv_rate);
												else
														$conv_value = $keyhdr[$arraykey];
												$totalpdf[$rowcount][$arraykey] = $conv_value;
										}else
										{
												$totalpdf[$rowcount][$arraykey] = '';
										}
										$rowcount++;
								}
						}
				}
				return $totalpdf;
		}elseif($outputformat == "TOTALHTML")
		{
			$escapedchars = Array('_SUM','_AVG','_MIN','_MAX');
			$sSQL = $this->sGetSQLforReport($this->reportid,$filterlist,"COLUMNSTOTOTAL");
			if(isset($this->totallist))
			{
				if($sSQL != "")
				{
					$result = $adb->query($sSQL);
					$y=$adb->num_fields($result);
					$custom_field_values = $adb->fetch_array($result);
					$coltotalhtml .= "<table align='center' width='60%' cellpadding='3' cellspacing='0' border='0' class='rptTable'><thead><tr class=\"reportRowTitle\"><td class='rptCellLabel'>".$mod_strings[Totals]."</td><td class='rptCellLabel'>".$mod_strings[SUM]."</td><td class='rptCellLabel'>".$mod_strings[AVG]."</td><td class='rptCellLabel'>".$mod_strings[MIN]."</td><td class='rptCellLabel'>".$mod_strings[MAX]."</td></tr></thead><tbody>"; // crmv@29686

					// Performation Optimization: If Direct output is desired
					if($directOutput) {
						echo $coltotalhtml;
						$coltotalhtml = '';
					}
					// END

					foreach($this->totallist as $key=>$value)
					{
						$fieldlist = explode(":",$key);
						$mod_query = $adb->pquery("SELECT distinct(tabid) as tabid, uitype as uitype from ".$table_prefix."_field where tablename = ? and columnname=?",array($fieldlist[1],$fieldlist[2]));
						if($adb->num_rows($mod_query)>0){
							$module_name = getTabName($adb->query_result($mod_query,0,'tabid'));
							$fieldlabel = trim(str_replace($escapedchars," ",$fieldlist[3]));
							$fieldlabel = str_replace("_", " ", $fieldlabel);
							if($module_name){
								$field = getTranslatedString($module_name)." ".getTranslatedString($fieldlabel,$module_name);
							} else {
								$field = getTranslatedString($module_name)." ".getTranslatedString($fieldlabel);
							}
						}
						$uitype_arr[str_replace($escapedchars," ",$module_name."_".$fieldlist[3])] = $adb->query_result($mod_query,0,"uitype");
						$totclmnflds[str_replace($escapedchars," ",$module_name."_".$fieldlist[3])] = $field;
					}
					for($i =0;$i<$y;$i++)
					{
						$fld = $adb->field_name($result, $i);
						$keyhdr[$fld->name] = $custom_field_values[$i];
					}

					$rowcount = 0; // crmv@29686
					foreach($totclmnflds as $key=>$value)
					{
						$coltotalhtml .= '<tr class="rptGrpHead reportRow'.(($rowcount++) % 2).'" valign=top>'; // crmv@29686
						$col_header = trim(str_replace($modules," ",$value));
						$fld_name_1 = $this->primarymodule . "_" . trim($value);
						$fld_name_2 = $this->secondarymodule . "_" . trim($value);
						if($uitype_arr[$value]==71 || in_array($fld_name_1,$this->convert_currency) || in_array($fld_name_1,$this->append_currency_symbol_to_value)
								|| in_array($fld_name_2,$this->convert_currency) || in_array($fld_name_2,$this->append_currency_symbol_to_value)) {
							$col_header .= " (".$app_strings['LBL_IN']." ".$current_user->currency_symbol.")";
							$convert_price = true;
						} else{
							$convert_price = false;
						}
						$coltotalhtml .= '<td class="rptData">'. $col_header .'</td>';
						$value = trim($key);
						$arraykey = $this->shortenLabel($value.'_SUM'); // crmv@30385
						if(isset($keyhdr[$arraykey]))
						{
							if($convert_price)
								$conv_value = convertFromMasterCurrency($keyhdr[$arraykey],$current_user->conv_rate);
							else
								$conv_value = $keyhdr[$arraykey];
							$coltotalhtml .= '<td class="rptTotal">'.number_format($conv_value,2, ',', '.').'</td>'; // crmv@29686
						}else
						{
							$coltotalhtml .= '<td class="rptTotal">&nbsp;</td>';
						}

						$arraykey = $this->shortenLabel($value.'_AVG'); // crmv@30385
						if(isset($keyhdr[$arraykey]))
						{
							if($convert_price)
								$conv_value = convertFromMasterCurrency($keyhdr[$arraykey],$current_user->conv_rate);
							else
								$conv_value = $keyhdr[$arraykey];
							$coltotalhtml .= '<td class="rptTotal">'.number_format($conv_value,2, ',', '.').'</td>'; // crmv@29686
						}else
						{
							$coltotalhtml .= '<td class="rptTotal">&nbsp;</td>';
						}

						$arraykey = $this->shortenLabel($value.'_MIN'); // crmv@30385
						if(isset($keyhdr[$arraykey]))
						{
							if($convert_price)
								$conv_value = convertFromMasterCurrency($keyhdr[$arraykey],$current_user->conv_rate);
							else
								$conv_value = $keyhdr[$arraykey];
							$coltotalhtml .= '<td class="rptTotal">'.number_format($conv_value,2, ',', '.').'</td>'; // crmv@29686
						}else
						{
							$coltotalhtml .= '<td class="rptTotal">&nbsp;</td>';
						}

						$arraykey = $this->shortenLabel($value.'_MAX'); // crmv@30385
						if(isset($keyhdr[$arraykey]))
						{
							if($convert_price)
								$conv_value = convertFromMasterCurrency($keyhdr[$arraykey],$current_user->conv_rate);
							else
								$conv_value = $keyhdr[$arraykey];
							$coltotalhtml .= '<td class="rptTotal">'.number_format($conv_value,2, ',', '.').'</td>'; // crmv@29686
						}else
						{
							$coltotalhtml .= '<td class="rptTotal">&nbsp;</td>';
						}

						$coltotalhtml .= '</tr>'; // crmv@29686

						// Performation Optimization: If Direct output is desired
						if($directOutput) {
							echo $coltotalhtml;
							$coltotalhtml = '';
						}
						// END
					}

					$coltotalhtml .= "</tbody></table>";

					// Performation Optimization: If Direct output is desired
					if($directOutput) {
						echo $coltotalhtml;
						$coltotalhtml = '';
					}
					// END
				}
			}
			return $coltotalhtml;
		}elseif($outputformat == "PRINT")
		{
			$sSQL = $this->sGetSQLforReport($this->reportid,$filterlist,$outputformat);	//crmv@17001
			$result = $adb->query($sSQL);
			if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1)
			$picklistarray = $this->getAccessPickListValues();

			if($result)
			{
				$y=$adb->num_fields($result);
				$arrayHeaders = Array();
				for ($x=0; $x<$y; $x++)
				{
					$fld = $adb->field_name($result, $x);

					if ($fld->name == 'LBL_ACTION' || substr($fld->name, 0, 6) == 'HIDDEN') continue; //crmv@27624 crmv@29686

					if(in_array($this->getLstringforReportHeaders($fld->name, false), $arrayHeaders))	//crmv@27624
					{
						$headerLabel = str_replace("_"," ",$fld->name);
						$arrayHeaders[] = $headerLabel;
					}
					else
					{
						$headerLabel = str_replace($modules," ",$this->getLstringforReportHeaders($fld->name));
						$headerLabel = str_replace("_"," ",$this->getLstringforReportHeaders($fld->name)); //crmv@26002
						$arrayHeaders[] = $headerLabel;
					}
					/*STRING TRANSLATION starts */
					$mod_name = explode(' ',$headerLabel,2);
					$module ='';
					if(in_array($mod_name[0],$modules_selected)){
						$module = getTranslatedString($mod_name[0],$mod_name[0]);
					}

					if(!empty($this->secondarymodule)){
						if($module!=''){
							$headerLabel_tmp = $module." ".getTranslatedString($mod_name[1],$mod_name[0]);
						} else {
							$headerLabel_tmp = getTranslatedString($mod_name[0]." ".$mod_name[1]);
						}
					} else {
						if($module!=''){
							$headerLabel_tmp = getTranslatedString($mod_name[1],$mod_name[0]);
						} else {
							$headerLabel_tmp = getTranslatedString($mod_name[0]." ".$mod_name[1]);
						}
					}
					if($headerLabel == $headerLabel_tmp) $headerLabel = getTranslatedString($headerLabel_tmp);
					else $headerLabel = $headerLabel_tmp;
					/*STRING TRANSLATION ends */
					$header .= "<th>".$headerLabel."</th>";
				}
				$noofrows = $adb->num_rows($result);
				$custom_field_values = $adb->fetch_array($result);
				$groupslist = $this->getGroupingList($this->reportid);
				$column_definitions = $adb->getFieldsDefinition($result);

				//crmv@29686
				$real_groupcount = count($groupslist);
				if(in_array($this->primarymodule, array('Invoice', 'Quotes', 'SalesOrder', 'PurchaseOrder')) ) {
					$real_groupcount -= 2; // tolgo i 2 campi aggiunti
				}
				$countrow = 0;
				//crmv@29686e


				do
				{
					$arraylists = Array();
					//crmv@29686
					if($real_groupcount == 1)
					{
						$newvalue = $custom_field_values[0];
					}elseif($real_groupcount == 2)
					{
						$newvalue = $custom_field_values[0];
						$snewvalue = $custom_field_values[1];
					}elseif($real_groupcount >= 3)
					{
						$newvalue = $custom_field_values[0];
						$tnewvalue = $custom_field_values[2];
					}
					//crmv@29686

					if($newvalue == "") $newvalue = "-";

					if($snewvalue == "") $snewvalue = "-";

					if($tnewvalue == "") $tnewvalue = "-";

					$valtemplate .= "<tr class=\"reportRow".(($countrow++)%2)."\">"; // crmv@29686

					for ($i=0; $i<$y; $i++)
					{
						$fld = $adb->field_name($result, $i);
						// crmv@29686
						if ($fld->name == 'LBL_ACTION' || substr($fld->name, 0, 6) == 'HIDDEN') continue;
						$fld->module = substr($fld->name, 0, strpos($fld->name, '_'));
						// crmv@29686e
						$fld_type = $column_definitions[$i]->type;

						$cell_align = 'left'; // crmv@29686
						if ($fld_type == 'int' || $fld_type == 'real') $cell_align = 'right';

						//crmv@17001 : Private Permissions
						$fieldname = strtolower(str_replace($this->primarymodule.'_','',$fld->name));
						if ($this->primarymodule == 'Calendar' && $custom_field_values['lbl_action'] != '') {
							$ownerId = getUserId($custom_field_values['lbl_action']);
							$visibility = $adb->query_result($adb->query('select visibility from '.$table_prefix.'_activity where activityid = '.$custom_field_values['lbl_action']),0,'visibility');
						}
						if ($i == ($y-1)) {
							unset($custom_field_values['lbl_action']);
							unset($custom_field_values[$i]);
							break;
						}
						//crmv@17001e
						if (in_array($fld->name, $this->convert_currency)) {
							$fieldvalue = convertFromMasterCurrency($custom_field_values[$i],$current_user->conv_rate);
							$cell_align = 'right'; // crmv@29686
						} elseif(in_array($fld->name, $this->append_currency_symbol_to_value)) {
							$curid_value = explode("::", $custom_field_values[$i]);
							$currency_id = $curid_value[0];
							$currency_value = $curid_value[1];
							$cur_sym_rate = getCurrencySymbolandCRate($currency_id);
							$fieldvalue = $cur_sym_rate['symbol']." ".$currency_value;
							$cell_align = 'right'; // crmv@29686
						}elseif ($fld->name == "PurchaseOrder_Currency" || $fld->name == "SalesOrder_Currency"
									|| $fld->name == "Invoice_Currency" || $fld->name == "Quotes_Currency") {
							$fieldvalue = getCurrencyName($custom_field_values[$i]);
						}elseif (in_array($fld->name,$this->ui10_fields) && !empty($custom_field_values[$i])) {
								$type = getSalesEntityType($custom_field_values[$i]);
								$tmp =getEntityName($type,$custom_field_values[$i]);
								foreach($tmp as $key=>$val){
									$fieldvalue = $val;
									break;
								}
						}
						//crmv@21249
						elseif ($this->multipicklist_fields[$fld->name] != '' && !empty($custom_field_values[$i])) {
							$fieldvalue = Picklistmulti::getTranslatedPicklist($custom_field_values[$i],$this->multipicklist_fields[$fld->name]);
						}
						//crmv@21249e
						//crmv@18544
						elseif($fld->name == 'Documents_Note'){
							$fieldvalue = strip_tags(htmlspecialchars_decode($custom_field_values[$i]));
						}
						//crmv@18544 end
						else {
							$fieldvalue = getTranslatedString($custom_field_values[$i]);
						}

						$fieldvalue = str_replace("<", "&lt;", $fieldvalue);
						$fieldvalue = str_replace(">", "&gt;", $fieldvalue);

						//Check For Role based pick list
						$temp_val= $fld->name;
						if(is_array($picklistarray))
							if(array_key_exists($temp_val,$picklistarray))
							{
								if(!in_array($custom_field_values[$i],$picklistarray[$fld->name]) && $custom_field_values[$i] != '')
								{
									$fieldvalue =$app_strings['LBL_NOT_ACCESSIBLE'];
								}
							}
						if(is_array($picklistarray[1]))
							if(array_key_exists($temp_val,$picklistarray[1]))
							{

								$temp =explode(",",str_ireplace(' |##| ',',',$fieldvalue));
								$temp_val = Array();
								foreach($temp as $key =>$val)
								{
										if(!in_array(trim($val),$picklistarray[1][$fld->name]) && trim($val) != '')
										{
											$temp_val[]=$app_strings['LBL_NOT_ACCESSIBLE'];
										}
										else
											$temp_val[]=$val;
								}
								$fieldvalue =(is_array($temp_val))?implode(", ",$temp_val):'';
							}

						//crmv@17001 : Private Permissions
						if ($this->primarymodule == 'Calendar' && !is_admin($current_user) && $ownerId != $current_user->id && $visibility == 'Private' && !in_array($fieldname,array('assigned_to','start_date_and_time','time_start','time_end','end_date','activity_type','visibility','duration','duration_minutes'))) {
							if ($fieldname == 'subject')
								$fieldvalue = getTranslatedString('Private Event','Calendar');
							else
								$fieldvalue = $app_strings['LBL_NOT_ACCESSIBLE'];
						}
						//crmv@17001e
						//crmv@30970
						if ($this->picklist_fields[$fld->name] != '') {
							$fieldvalue = getTranslatedString($fieldvalue,$fld->module);
						}
						//crmv@30970e
						if($fieldvalue == "" )
						{
							$fieldvalue = "-";
						}
						else if(stristr($fieldvalue,"|##|"))
						{
							$fieldvalue = str_ireplace(' |##| ',', ',$fieldvalue);
						}
						else if($fld_type == "date" || $fld_type == "datetime") {
							$fieldvalue = getDisplayDate($fieldvalue);
						}
						if(($lastvalue == $fieldvalue) && $this->reporttype == "summary")
						{
							if($this->reporttype == "summary")
							{
								$valtemplate .= "<td align=\"$cell_align\" style='border-top:1px dotted #FFFFFF;'>&nbsp;</td>";
							}else
							{
								$valtemplate .= "<td align=\"$cell_align\">".$fieldvalue."</td>";
							}
						}else if(($secondvalue == $fieldvalue) && $this->reporttype == "summary")
						{
							if($lastvalue == $newvalue)
							{
								$valtemplate .= "<td align=\"$cell_align\" style='border-top:1px dotted #FFFFFF;'>&nbsp;</td>";
							}else
							{
								$valtemplate .= "<td align=\"$cell_align\">".$fieldvalue."</td>";
							}
						}
						else if(($thirdvalue == $fieldvalue) && $this->reporttype == "summary")
						{
							if($secondvalue == $snewvalue)
							{
								$valtemplate .= "<td align=\"$cell_align\" style='border-top:1px dotted #FFFFFF;'>&nbsp;</td>";
							}else
							{
								$valtemplate .= "<td align=\"$cell_align\">".$fieldvalue."</td>";
							}
						}
						else
						{
							if($this->reporttype == "tabular")
							{
								$valtemplate .= "<td align=\"$cell_align\">".$fieldvalue."</td>";
							}else
							{
								$valtemplate .= "<td align=\"$cell_align\">".$fieldvalue."</td>";
							}
						}
					  }
					 $valtemplate .= "</tr>";
					 $lastvalue = $newvalue;
					 $secondvalue = $snewvalue;
					 $thirdvalue = $tnewvalue;
					 $arr_val[] = $arraylists;
					 set_time_limit($php_max_execution_time);
				}while($custom_field_values = $adb->fetch_array($result));

				$sHTML = '<tr class="reportRowTitle">'.$header.'</tr>'.$valtemplate; // crmv@29686
				$return_data[] = $sHTML;
				$return_data[] = $noofrows;
				return $return_data;
			}
		}elseif($outputformat == "PRINT_TOTAL")
		{
			$escapedchars = Array('_SUM','_AVG','_MIN','_MAX');
			$sSQL = $this->sGetSQLforReport($this->reportid,$filterlist,"COLUMNSTOTOTAL");
			if(isset($this->totallist))
			{
				if($sSQL != "")
				{
					$result = $adb->query($sSQL);
					$y=$adb->num_fields($result);
					$custom_field_values = $adb->fetch_array($result);

					$coltotalhtml .= "<br /><table align='center' width='60%' cellpadding='3' cellspacing='0' border='1' class='printReport'><tr class=\"reportRowTitle\"><td class='rptCellLabel'>".$mod_strings['Totals']."</td><td><b>".$mod_strings['SUM']."</b></td><td><b>".$mod_strings['AVG']."</b></td><td><b>".$mod_strings['MIN']."</b></td><td><b>".$mod_strings['MAX']."</b></td></tr>"; // crmv@29686

					// Performation Optimization: If Direct output is desired
					if($directOutput) {
						echo $coltotalhtml;
						$coltotalhtml = '';
					}
					// END

					foreach($this->totallist as $key=>$value)
					{
						$fieldlist = explode(":",$key);
						$mod_query = $adb->pquery("SELECT distinct(tabid) as tabid, uitype as uitype from ".$table_prefix."_field where tablename = ? and columnname=?",array($fieldlist[1],$fieldlist[2]));
						if($adb->num_rows($mod_query)>0){
							$module_name = getTabName($adb->query_result($mod_query,0,'tabid'));
							$fieldlabel = trim(str_replace($escapedchars," ",$fieldlist[3]));
							$fieldlabel = str_replace("_", " ", $fieldlabel);
							if($module_name){
								$field = getTranslatedString($module_name)." ".getTranslatedString($fieldlabel,$module_name);
							} else {
								$field = getTranslatedString($module_name)." ".getTranslatedString($fieldlabel);
							}
						}
						$uitype_arr[str_replace($escapedchars," ",$module_name."_".$fieldlist[3])] = $adb->query_result($mod_query,0,"uitype");
						$totclmnflds[str_replace($escapedchars," ",$module_name."_".$fieldlist[3])] = $field;
					}

					for($i =0;$i<$y;$i++)
					{
						$fld = $adb->field_name($result, $i);
						$keyhdr[$fld->name] = $custom_field_values[$i];

					}
					$rowcount = 0; // crmv@29686
					foreach($totclmnflds as $key=>$value)
					{
						$coltotalhtml .= '<tr class="rptGrpHead reportRow'.(($rowcount++)%2).'">'; // crmv@29686
						$col_header = getTranslatedString(trim(str_replace($modules," ",$value)));
						$fld_name_1 = $this->primarymodule . "_" . trim($value);
						$fld_name_2 = $this->secondarymodule . "_" . trim($value);
						if($uitype_arr[$value]==71 || in_array($fld_name_1,$this->convert_currency) || in_array($fld_name_1,$this->append_currency_symbol_to_value)
								|| in_array($fld_name_2,$this->convert_currency) || in_array($fld_name_2,$this->append_currency_symbol_to_value)) {
							$col_header .= " (".$app_strings['LBL_IN']." ".$current_user->currency_symbol.")";
							$convert_price = true;
						} else{
							$convert_price = false;
						}
						$coltotalhtml .= '<td class="rptData">'. $col_header .'</td>';
						$value = trim($key);
						$arraykey = $value.'_SUM';
						if(isset($keyhdr[$arraykey]))
						{
							if($convert_price)
								$conv_value = convertFromMasterCurrency($keyhdr[$arraykey],$current_user->conv_rate);
							else
								$conv_value = $keyhdr[$arraykey];
							$coltotalhtml .= "<td class='rptTotal'>".$conv_value.'</td>';
						}else
						{
							$coltotalhtml .= "<td class='rptTotal'>&nbsp;</td>";
						}

						$arraykey = $value.'_AVG';
						if(isset($keyhdr[$arraykey]))
						{
							if($convert_price)
								$conv_value = convertFromMasterCurrency($keyhdr[$arraykey],$current_user->conv_rate);
							else
								$conv_value = $keyhdr[$arraykey];
							$coltotalhtml .= "<td class='rptTotal'>".$conv_value.'</td>';
						}else
						{
							$coltotalhtml .= "<td class='rptTotal'>&nbsp;</td>";
						}

						$arraykey = $value.'_MIN';
						if(isset($keyhdr[$arraykey]))
						{
							if($convert_price)
								$conv_value = convertFromMasterCurrency($keyhdr[$arraykey],$current_user->conv_rate);
							else
								$conv_value = $keyhdr[$arraykey];
							$coltotalhtml .= "<td class='rptTotal'>".$conv_value.'</td>';
						}else
						{
							$coltotalhtml .= "<td class='rptTotal'>&nbsp;</td>";
						}

						$arraykey = $value.'_MAX';
						if(isset($keyhdr[$arraykey]))
						{
							if($convert_price)
								$conv_value = convertFromMasterCurrency($keyhdr[$arraykey],$current_user->conv_rate);
							else
								$conv_value = $keyhdr[$arraykey];
							$coltotalhtml .= "<td class='rptTotal'>".$conv_value.'</td>';
						}else
						{
							$coltotalhtml .= "<td class='rptTotal'>&nbsp;</td>";
						}

						$coltotalhtml .= '</tr>';

						// Performation Optimization: If Direct output is desired
						if($directOutput) {
							echo $coltotalhtml;
							$coltotalhtml = '';
						}
						// END
					}

					$coltotalhtml .= "</table>";
					// Performation Optimization: If Direct output is desired
					if($directOutput) {
						echo $coltotalhtml;
						$coltotalhtml = '';
					}
					// END
				}
			}
			return $coltotalhtml;
		}
	}

	// crmv@30385
	// label deve avere almeno un "_"
	static function shortenLabel($label, $maxlength = 30) {
		$retlabel = $label;

		if (strlen($label) > $maxlength){
			$arr_str = str_split($label,strrpos($label,"_"));
			$label_to_shorten = $arr_str[0];
			$label_to_keep = $arr_str[1];
			$lenght = $maxlength - 1 - strlen($label_to_keep);
			$label_to_shorten = substr($label_to_shorten,0,($lenght-2))."..";
			$retlabel = $label_to_shorten."_".$label_to_keep;
		}

		return $retlabel;
	}
	// crmv@30385e

	//<<<<<<<new>>>>>>>>>>
	function getColumnsTotal($reportid)
	{
		// Have we initialized it already?
		if($this->_columnstotallist !== false) {
			return $this->_columnstotallist;
		}

		global $adb;
		global $modules;
		global $log, $current_user;
		global $table_prefix;

		$query = "select * from ".$table_prefix."_reportmodules where reportmodulesid =?";
		$res = $adb->pquery($query , array($reportid));
		$modrow = $adb->fetch_array($res);
		$premod = $modrow["primarymodule"];
		$secmod = $modrow["secondarymodules"];
		$coltotalsql = "select ".$table_prefix."_reportsummary.* from ".$table_prefix."_report";
		$coltotalsql .= " inner join ".$table_prefix."_reportsummary on ".$table_prefix."_report.reportid = ".$table_prefix."_reportsummary.reportsummaryid";
		$coltotalsql .= " where ".$table_prefix."_report.reportid =?";

		$result = $adb->pquery($coltotalsql, array($reportid));

		while($coltotalrow = $adb->fetch_array($result))
		{
			$fieldcolname = $coltotalrow["columnname"];
			if($fieldcolname != "none")
			{
				$fieldlist = explode(":",$fieldcolname);
				$field_tablename = $fieldlist[1];
				$field_columnname = $fieldlist[2];

				$mod_query = $adb->pquery("SELECT distinct(tabid) as tabid from ".$table_prefix."_field where tablename = ? and columnname=?",array($fieldlist[1],$fieldlist[2]));
				//crmv@21249
				if($adb->num_rows($mod_query)>0){
					$module_name = getTabName($adb->query_result($mod_query,0,'tabid'));
					$fieldlabel = trim($fieldlist[3]);
					if (strlen($module_name."_".$fieldlist[3]) > 29){
						$arr_str = str_split($fieldlist[3],strrpos($fieldlist[3],"_"));
						$label_to_shorten = $arr_str[0];
						$label_to_keep = $arr_str[1];
						$lenght = 28 - strlen($module_name) - strlen($label_to_keep);
						$label_to_shorten = substr($label_to_shorten,0,($lenght-2))."..";
						$field_columnalias = $module_name."_".$label_to_shorten."_".$label_to_keep;
					}
					else{
						if($module_name){
							$field_columnalias = $module_name."_".$fieldlist[3];
						} else {
							$field_columnalias = $module_name."_".$fieldlist[3];
						}
					}
				}
				//crmv@21249e

				//$field_columnalias = $fieldlist[3];
				$field_permitted = false;
				if(CheckColumnPermission($field_tablename,$field_columnname,$premod) != "false"){
					$field_permitted = true;
				} else {
					$mod = explode(":",$secmod);
					foreach($mod as $key){
						if(CheckColumnPermission($field_tablename,$field_columnname,$key) != "false"){
							$field_permitted=true;
						}
					}
				}

				// crmv@29686
				// permetto colonna per calcoli riassuntivi
				$remove_from_std = false;
				$add_to_totals = false;
				if ($coltotalrow['show_summary'] == 1) {
					$add_to_totals = true;
					if ($field_tablename == substr($table_prefix.'_inventoryproductrel'.$this->primarymodule, 0, 29)) {
						$field_permitted = true;
						$field_columnalias = 'Products_xx_SUM'; // TODO: gestire altre operazioni
						$remove_from_std = true;
					}
				}
				// crmv@29686e

				if($field_permitted == true)
				{


					$field = $field_tablename.".".$field_columnname;
					if($field_tablename == $table_prefix.'_products' && $field_columnname == 'unit_price') {
						// Query needs to be rebuild to get the value in user preferred currency. [innerProduct and actual_unit_price are table and column alias.]
						$field =  " innerProduct.actual_unit_price";
					}
					if($field_tablename == $table_prefix.'_service' && $field_columnname == 'unit_price') {
						// Query needs to be rebuild to get the value in user preferred currency. [innerProduct and actual_unit_price are table and column alias.]
						$field =  " innerService.actual_unit_price";
					}
					if(($field_tablename == $table_prefix.'_invoice' || $field_tablename == $table_prefix.'_quotes' || $field_tablename == $table_prefix.'_purchaseorder' || $field_tablename == $table_prefix.'_salesorder')
							&& ($field_columnname == 'total' || $field_columnname == 'subtotal' || $field_columnname == 'discount_amount' || $field_columnname == 's_h_amount')) {
						$field =  " $field_tablename.$field_columnname/$field_tablename.conversion_rate ";
					}
					if($fieldlist[4] == 2)
					{
                        if ($fieldlist[2]=='worktime')
                          	$stdfilterlist[$fieldcolname] = "sec_to_time(sum(time_to_sec(".$field."))) \"".$field_columnalias."\"";
                        else
							$stdfilterlist[$fieldcolname] = "sum($field) \"".$field_columnalias."\"";
					}
					if($fieldlist[4] == 3)
					{
						//Fixed average calculation issue due to NULL values ie., when we use avg() function, NULL values will be ignored.to avoid this we use (sum/count) to find average.
						//$stdfilterlist[$fieldcolname] = "avg(".$fieldlist[1].".".$fieldlist[2].") '".$fieldlist[3]."'";
                        if ($fieldlist[2]=='worktime')
                          	$stdfilterlist[$fieldcolname] = "sec_to_time(sum(time_to_sec(".$field."))/count(*)) \"".$field_columnalias."\"";
                        else
							$stdfilterlist[$fieldcolname] = "(sum($field)/count(*)) \"".$field_columnalias."\"";
					}
					if($fieldlist[4] == 4)
					{
                        if ($fieldlist[2]=='worktime')
                          	$stdfilterlist[$fieldcolname] = "sec_to_time(min(time_to_sec(".$field."))) \"".$field_columnalias."\"";
                        else
							$stdfilterlist[$fieldcolname] = "min($field) \"".$field_columnalias."\"";
					}
					if($fieldlist[4] == 5)
					{
					    if ($fieldlist[2]=='worktime')
                          	$stdfilterlist[$fieldcolname] = "sec_to_time(max(time_to_sec(".$field."))) \"".$field_columnalias."\"";
                        else
							$stdfilterlist[$fieldcolname] = "max($field) \"".$field_columnalias."\"";
					}

					// crmv@29686
					if ($add_to_totals)	$this->_columnssumlist[$fieldcolname] = $stdfilterlist[$fieldcolname];
					if ($remove_from_std) unset($stdfilterlist[$fieldcolname]);
					if (count($stdfilterlist) == 0) $stdfilterlist = null;
					// crmv@29686e
				}
			}
		}
		// Save the information
		$this->_columnstotallist = $stdfilterlist;

		$log->info("ReportRun :: Successfully returned getColumnsTotal".$reportid);
		return $stdfilterlist;
	}
	//<<<<<<new>>>>>>>>>


	/** function to get query for the columns to total for the given reportid
	 *  @ param $reportid : Type integer
	 *  This returns columnstoTotal query for the reportid
	 */

	function getColumnsToTotalColumns($reportid)
	{
		global $adb;
		global $modules;
		global $log;
		global $table_prefix;

		$sreportstdfiltersql = "select ".$table_prefix."_reportsummary.* from ".$table_prefix."_report";
		$sreportstdfiltersql .= " inner join ".$table_prefix."_reportsummary on ".$table_prefix."_report.reportid = ".$table_prefix."_reportsummary.reportsummaryid";
		$sreportstdfiltersql .= " where ".$table_prefix."_report.reportid =?";

		$result = $adb->pquery($sreportstdfiltersql, array($reportid));
		$noofrows = $adb->num_rows($result);

		for($i=0; $i<$noofrows; $i++)
		{
			$fieldcolname = $adb->query_result($result,$i,"columnname");

			if($fieldcolname != "none")
			{
				$fieldlist = explode(":",$fieldcolname);
				if($fieldlist[4] == 2)
				{
					$sSQLList[] = "sum(".$fieldlist[1].".".$fieldlist[2].") ".$fieldlist[3];
				}
				if($fieldlist[4] == 3)
				{
					$sSQLList[] = "avg(".$fieldlist[1].".".$fieldlist[2].") ".$fieldlist[3];
				}
				if($fieldlist[4] == 4)
				{
					$sSQLList[] = "min(".$fieldlist[1].".".$fieldlist[2].") ".$fieldlist[3];
				}
				if($fieldlist[4] == 5)
				{
					$sSQLList[] = "max(".$fieldlist[1].".".$fieldlist[2].") ".$fieldlist[3];
				}
			}
		}
		if(isset($sSQLList))
		{
			$sSQL = implode(",",$sSQLList);
		}
		$log->info("ReportRun :: Successfully returned getColumnsToTotalColumns".$reportid);
		return $sSQL;
	}
	/** Function to convert the Report Header Names into i18n
	 *  @param $fldname: Type Varchar
	 *  Returns Language Converted Header Strings
	 **/
	function getLstringforReportHeaders($fldname, $translate = true)	//crmv@27624
	{
		global $modules,$current_language,$current_user,$app_strings;
		//crmv@27408 -- cache the result for better performance
		static $fldname_cache = array();
		if (array_key_exists($fldname, $fldname_cache)) return $fldname_cache[$fldname];
		//crmv@27408e
		$rep_header = ltrim(str_replace($modules," ",$fldname));
		$rep_header_temp = preg_replace("/\s+/","_",$rep_header);
		//crmv@18040
		$rep_module = preg_replace("/_".preg_quote($rep_header_temp,'/')."/","",$fldname);
		//crmv@18040 end
		// htmlentities should be decoded in field names (eg. &). Noticed for fields like 'Terms & Conditions', 'S&H Amount'
		$rep_header = decode_html($rep_header);
		$curr_symb = "";
		if(in_array($fldname, $this->convert_currency)) {
        	$curr_symb = " (".$app_strings['LBL_IN']." ".$current_user->currency_symbol.")";
		}
        //crmv@27624
        if ($rep_header == 'LBL_ACTION') {
        	$rep_header = getTranslatedString($rep_header);
        } elseif ($translate && getTranslatedString($rep_header,$rep_module) != '') {	//crmv@25158
            $rep_header = getTranslatedString($rep_header,$rep_module);
        }
        //crmv@27624e
        $rep_header .=$curr_symb;
        $fldname_cache[$fldname] = $rep_header; //crmv@27408
		return $rep_header;
	}

	/** Function to get picklist value array based on profile
	 *          *  returns permitted fields in array format
	 **/


	function getAccessPickListValues()
	{
		global $adb;
		global $current_user;
		global $table_prefix;
		$id = array(getTabid($this->primarymodule));
		if($this->secondarymodule != '')
			array_push($id,  getTabid($this->secondarymodule));

		$query = 'select fieldname,columnname,fieldid,fieldlabel,tabid,uitype from '.$table_prefix.'_field where tabid in('. generateQuestionMarks($id) .') and uitype in (15,33,55,300)'; //and columnname in (?)'; // crmv@30528
		$result = $adb->pquery($query, $id);//,$select_column));
		$roleid=$current_user->roleid;
		$subrole = getRoleSubordinates($roleid);
		if(count($subrole)> 0)
		{
			$roleids = $subrole;
			array_push($roleids, $roleid);
		}
		else
		{
			$roleids = $roleid;
		}

		$temp_status = Array();
		for($i=0;$i < $adb->num_rows($result);$i++)
		{
			$fieldname = $adb->query_result($result,$i,"fieldname");
			$fieldlabel = $adb->query_result($result,$i,"fieldlabel");
			$tabid = $adb->query_result($result,$i,"tabid");
			$uitype = $adb->query_result($result,$i,"uitype");

			$fieldlabel1 = str_replace(" ","_",$fieldlabel);
			$keyvalue = getTabModuleName($tabid)."_".$fieldlabel1;
			$fieldvalues = Array();
			//se la picklist supporta il nuovo metodo
			$columns = array($adb->database->MetaColumnNames($table_prefix."_$fieldname"));
			if ($columns && in_array('picklist_valueid',$columns) && $fieldname != 'product_lines'){
				$order_by = "sortid,$fieldname";
				$pick_query="select $fieldname from ".$table_prefix."_$fieldname where exists (select * from ".$table_prefix."_role2picklist where ".$table_prefix."_role2picklist.picklistvalueid = ".$table_prefix."_$fieldname.picklist_valueid and roleid in (". generateQuestionMarks($roleids) ."))";
				$params = array($roleids);
			}
			//altrimenti uso il vecchio
			else {
				if (in_array('sortorderid',$columns))
					$order_by = "sortorderid,$fieldname";
				else
					$order_by = $fieldname;
				$pick_query="select $fieldname from ".$table_prefix."_$fieldname";
				if ($fieldname == 'product_lines')
					$pick_query .= ' where presence = 1';
				//vtc e
				$params = array();
			}
			if ($fieldname != 'firstname')
				$mulselresult = $adb->pquery($pick_query,$params);
			if ($mulselresult){
				for($j=0;$j < $adb->num_rows($mulselresult);$j++)
				{
					$fldvalue = $adb->query_result($mulselresult,$j,$fieldname);
					if(in_array($fldvalue,$fieldvalues)) continue;
					$fieldvalues[] = $fldvalue;
				}
			}
			$field_count = count($fieldvalues);
			if( $uitype == 15 && $field_count > 0 && ($fieldname == 'taskstatus' || $fieldname == 'eventstatus'))
			{
				$temp_count =count($temp_status[$keyvalue]);
				if($temp_count > 0)
				{
					for($t=0;$t < $field_count;$t++)
					{
						$temp_status[$keyvalue][($temp_count+$t)] = $fieldvalues[$t];
					}
					$fieldvalues = $temp_status[$keyvalue];
				}
				else
					$temp_status[$keyvalue] = $fieldvalues;
			}

			if($uitype == 33)
				$fieldlists[1][$keyvalue] = $fieldvalues;
			else if($uitype == 55 && $fieldname == 'salutationtype')
				$fieldlists[$keyvalue] = $fieldvalues;
	        else if($uitype == 15)
		        $fieldlists[$keyvalue] = $fieldvalues;
		}
		return $fieldlists;
	}

	//crmv@21198
	function getReferenceFieldColumnList($moduleName, $fieldInfo) {
		global $table_prefix;
		$adb = PearDatabase::getInstance();

		$columnsSqlList = array();

		$fieldInstance = WebserviceField::fromArray($adb, $fieldInfo);
		$referenceModuleList = $fieldInstance->getReferenceList();
		$reportSecondaryModules = explode(':', $this->secondarymodule);

		if($moduleName != $this->primarymodule && in_array($this->primarymodule, $referenceModuleList)) {
			$entityTableFieldNames = getEntityFieldNames($this->primarymodule);
			$entityTableName = $entityTableFieldNames['tablename'];
			$entityFieldNames = $entityTableFieldNames['fieldname'];

			$columnList = array();
			if(is_array($entityFieldNames)) {
				foreach ($entityFieldNames as $entityColumnName) {
					$columnList["$entityColumnName"] = "$entityTableName.$entityColumnName";
				}
			} else {
				$columnList[] = "$entityTableName.$entityFieldNames";
			}
			if(count($columnList) > 1) {
				$columnSql = getSqlForNameInDisplayFormat($columnList, $this->primarymodule);
			} else {
				$columnSql = implode('', $columnList);
			}
			$columnsSqlList[] = $columnSql;

		} else {
			foreach($referenceModuleList as $referenceModule) {
				$entityTableFieldNames = getEntityFieldNames($referenceModule);
				$entityTableName = $entityTableFieldNames['tablename'];
				$entityFieldNames = $entityTableFieldNames['fieldname'];

				if($moduleName == 'HelpDesk' && $referenceModule == 'Accounts') {
					$referenceTableName = $table_prefix.'_accountRelHelpDesk';
				} elseif ($moduleName == 'HelpDesk' && $referenceModule == 'Contacts') {
					$referenceTableName = $table_prefix.'_contactdetailsRelHelpDesk';
				} elseif ($moduleName == 'HelpDesk' && $referenceModule == 'Products') {
					$referenceTableName = $table_prefix.'_productsRel';
				} elseif ($moduleName == 'Calendar' && $referenceModule == 'Accounts') {
					$referenceTableName = $table_prefix.'_accountRelCalendar';
				} elseif ($moduleName == 'Calendar' && $referenceModule == 'Contacts') {
					$referenceTableName = $table_prefix.'_contactdetailsCalendar';
				} elseif ($moduleName == 'Calendar' && $referenceModule == 'Leads') {
					$referenceTableName = $table_prefix.'_leaddetailsRelCalendar';
				} elseif ($moduleName == 'Calendar' && $referenceModule == 'Potentials') {
					$referenceTableName = $table_prefix.'_potentialRelCalendar';
				} elseif ($moduleName == 'Calendar' && $referenceModule == 'Invoice') {
					$referenceTableName = $table_prefix.'_invoiceRelCalendar';
				} elseif ($moduleName == 'Calendar' && $referenceModule == 'Quotes') {
					$referenceTableName = $table_prefix.'_quotesRelCalendar';
				} elseif ($moduleName == 'Calendar' && $referenceModule == 'PurchaseOrder') {
					$referenceTableName = $table_prefix.'_purchaseorderRelCalendar';
				} elseif ($moduleName == 'Calendar' && $referenceModule == 'SalesOrder') {
					$referenceTableName = $table_prefix.'_salesorderRelCalendar';
				} elseif ($moduleName == 'Calendar' && $referenceModule == 'HelpDesk') {
					$referenceTableName = $table_prefix.'_troubleticketsRelCalendar';
				} elseif ($moduleName == 'Calendar' && $referenceModule == 'Campaigns') {
					$referenceTableName = $table_prefix.'_campaignRelCalendar';
				} elseif ($moduleName == 'Contacts' && $referenceModule == 'Accounts') {
					$referenceTableName = $table_prefix.'_accountContacts';
				} elseif ($moduleName == 'Contacts' && $referenceModule == 'Contacts') {
					$referenceTableName = $table_prefix.'_contactdetailsContacts';
				} elseif ($moduleName == 'Accounts' && $referenceModule == 'Accounts') {
					$referenceTableName = $table_prefix.'_accountAccounts';
				} elseif ($moduleName == 'Campaigns' && $referenceModule == 'Products') {
					$referenceTableName = $table_prefix.'_productsCampaigns';
				} elseif ($moduleName == 'Faq' && $referenceModule == 'Products') {
					$referenceTableName = $table_prefix.'_productsFaq';
				} elseif ($moduleName == 'Invoice' && $referenceModule == 'SalesOrder') {
					$referenceTableName = $table_prefix.'_salesorderInvoice';
				} elseif ($moduleName == 'Invoice' && $referenceModule == 'Contacts') {
					$referenceTableName = $table_prefix.'_contactdetailsInvoice';
				} elseif ($moduleName == 'Invoice' && $referenceModule == 'Accounts') {
					$referenceTableName = $table_prefix.'_accountInvoice';
				} elseif ($moduleName == 'Potentials' && $referenceModule == 'Campaigns') {
					$referenceTableName = $table_prefix.'_campaignPotentials';
				} elseif ($moduleName == 'Products' && $referenceModule == 'Vendors') {
					$referenceTableName = $table_prefix.'_vendorRelProducts';
				} elseif ($moduleName == 'PurchaseOrder' && $referenceModule == 'Contacts') {
					$referenceTableName = $table_prefix.'_contactdetailsPurchaseOrder';
				} elseif ($moduleName == 'PurchaseOrder' && $referenceModule == 'Vendors') {
					$referenceTableName = $table_prefix.'_vendorRelPurchaseOrder';
				} elseif ($moduleName == 'Quotes' && $referenceModule == 'Potentials') {
					$referenceTableName = $table_prefix.'_potentialRelQuotes';
				} elseif ($moduleName == 'Quotes' && $referenceModule == 'Accounts') {
					$referenceTableName = $table_prefix.'_accountQuotes';
				} elseif ($moduleName == 'Quotes' && $referenceModule == 'Contacts') {
					$referenceTableName = $table_prefix.'_contactdetailsQuotes';
				} elseif ($moduleName == 'SalesOrder' && $referenceModule == 'Potentials') {
					$referenceTableName = $table_prefix.'_potentialRelSalesOrder';
				} elseif ($moduleName == 'SalesOrder' && $referenceModule == 'Accounts') {
					$referenceTableName = $table_prefix.'_accountSalesOrder';
				} elseif ($moduleName == 'SalesOrder' && $referenceModule == 'Contacts') {
					$referenceTableName = $table_prefix.'_contactdetailsSalesOrder';
				} elseif ($moduleName == 'SalesOrder' && $referenceModule == 'Quotes') {
					$referenceTableName = $table_prefix.'_quotesSalesOrder';
				} elseif ($moduleName == 'Potentials' && $referenceModule == 'Contacts') {
					$referenceTableName = $table_prefix.'_contactdetailsPotentials';
				} elseif ($moduleName == 'Potentials' && $referenceModule == 'Accounts') {
					$referenceTableName = $table_prefix.'_accountPotentials';
				} elseif (in_array($referenceModule, $reportSecondaryModules)) {
					$referenceTableName = "{$entityTableName}Rel$referenceModule";
				} elseif (in_array($moduleName, $reportSecondaryModules)) {
					$referenceTableName = "{$entityTableName}Rel$moduleName";
				} else {
					$referenceTableName = "{$entityTableName}Rel{$moduleName}{$fieldInstance->getFieldId()}";
				}

				$referenceTableName=substr($referenceTableName,0,29);

				$columnList = array();
				if(is_array($entityFieldNames)) {
					foreach ($entityFieldNames as $entityColumnName) {
						$columnList["$entityColumnName"] = "$referenceTableName.$entityColumnName";
					}
				} else {
					$columnList[] = "$referenceTableName.$entityFieldNames";
				}
				if(count($columnList) > 1) {
					$columnSql = getSqlForNameInDisplayFormat($columnList, $referenceModule);
				} else {
					$columnSql = implode('', $columnList);
				}
				if ($referenceModule == 'DocumentFolders' && $fieldInstance->getFieldName() == 'folderid') {
					$columnSql = $table_prefix.'_crmentityfolder.foldername'; // crmv@30967
				}
				if ($referenceModule == 'Currency' && $fieldInstance->getFieldName() == 'currency_id') {
					$columnSql = substr($table_prefix."_currency_info$moduleName",0,29).".currency_name";
				}
				$columnsSqlList[] = $columnSql;
			}
		}
		return $columnsSqlList;
	}
	//crmv@21198e

}
?>