<?php
/***************************************************************************************
 * The contents of this file are subject to the CRMVILLAGE.BIZ VTECRM License Agreement
 * ("licenza.txt"); You may not use this file except in compliance with the License
 * The Original Code is:  CRMVILLAGE.BIZ VTECRM
 * The Initial Developer of the Original Code is CRMVILLAGE.BIZ.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
 ***************************************************************************************/

/* mycrmv@2707m */

function getDuplicateQueryRotho($module,$field_values,$ui_type_arr,$select_fields='')	//mycrmv@2707m
{
	global $current_user, $table_prefix, $adb;
	//mycrmv@2707m
	$where = '';
	if (!empty($select_fields)) {
		$tbl_cols = array();
		$tbl_col_fld = explode(",", $select_fields);
		$i=0;
		
		foreach($tbl_col_fld as $val) {
			list($tbl[$i], $cols[$i], $fields[$i]) = explode(".", $val);
			$tbl_cols[$i] = $tbl[$i]. "." . $cols[$i];
			$query_field = "SELECT * FROM ".$table_prefix."_field WHERE fieldname = ? and tablename = ? ";
			$res_field = $adb->pquery($query_field,Array($cols[$i],$tbl[$i]));
			require_once('include/Webservices/WebserviceField.php');
			$ws_field = WebserviceField::fromQueryResult($adb,$res_field,0);
			$datatype = $ws_field->getFieldDataType();
			if ($cols[$i] == 'cf_894' || $cols[$i] == 'cf_895') {
				$tbl_cols[$i] = "CAST ( ".$tbl[$i]. "." . $cols[$i]."  AS VARCHAR(5000)) ";
				}
			$i++;
		}
		$table_cols = implode(",",$tbl_cols);
		
		$tbl_cols = array();
		$tbl_col_fld = explode(",", $field_values);
		$i=0;
		foreach($tbl_col_fld as $val) {
			list($tbl[$i], $cols[$i], $fields[$i]) = explode(".", $val);
			$tbl_cols[$i] = $tbl[$i]. "." . $cols[$i];
			$i++;
		}
		$inner_table_cols = implode(",",$tbl_cols);
		
		$customView = new CustomView($module);
		$viewid = $customView->getViewId($module);
		$queryGenerator = QueryGenerator::getInstance($module, $current_user);
		$queryGenerator->initForCustomViewById($viewid);
		$queryGenerator->getQuery();
		$where = $queryGenerator->getConditionalWhere();
		if(isset($_REQUEST['lv_user_id'])) {
			$_SESSION['lv_user_id'] = $_REQUEST['lv_user_id'];
		} else {
			$_REQUEST['lv_user_id'] = $_SESSION['lv_user_id'];
		}
		if( $_REQUEST['lv_user_id'] == "all" || $_REQUEST['lv_user_id'] == "") {
			 // all event (normal rule)
		} else if ( $_REQUEST['lv_user_id'] == "mine") { // only assigned to me
			if (!empty($where)) {
			$where .= " AND ";
			}
			$where .= " {$table_prefix}_crmentity.smownerid = ".$current_user->id." ";
		} else if ( $_REQUEST['lv_user_id'] == "others") { // only assigneto others
			if (!empty($where)) {
			$where .= " AND ";
			}
			$where .= " {$table_prefix}_crmentity.smownerid <> ".$current_user->id." ";
		} else { // a selected userid
			if (!empty($where)) {
			$where .= " AND ";
			}
			$where .= " {$table_prefix}_crmentity.smownerid = ".$_REQUEST['lv_user_id']." ";
		}
	} else {
		$tbl_cols = array();
		$tbl_col_fld = explode(",", $field_values);
		$i=0;
		foreach($tbl_col_fld as $val) {
			list($tbl[$i], $cols[$i], $fields[$i]) = explode(".", $val);
			$tbl_cols[$i] = $tbl[$i]. "." . $cols[$i];
			$i++;
		}
		$table_cols = implode(",",$tbl_cols);
	}
	$sec_parameter = getSecParameterforMerge($module);
	if (!empty($where)) {
		$sec_parameter .= ' and '.$where;
	}
	//mycrmv@2707me
	if( stristr($_REQUEST['action'],'ImportStep') || ($_REQUEST['action'] == $_REQUEST['module'].'Ajax' && $_REQUEST['current_action'] == 'ImportSteplast'))
	{
		if($module == 'Contacts')
		{
			$ret_arr = get_special_on_clause($table_cols);
			$select_clause = $ret_arr['sel_clause'];
			$on_clause = $ret_arr['on_clause'];
			$nquery="select ".$table_prefix."_contactdetails.contactid as recordid,".$table_prefix."_users_last_import.deleted,$table_cols
					FROM ".$table_prefix."_contactdetails
					INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_contactdetails.contactid
					INNER JOIN ".$table_prefix."_contactaddress ON ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_contactaddress.contactaddressid
					INNER JOIN ".$table_prefix."_contactsubdetails ON ".$table_prefix."_contactaddress.contactaddressid = ".$table_prefix."_contactsubdetails.contactsubscriptionid
					LEFT JOIN ".$table_prefix."_contactscf ON ".$table_prefix."_contactscf.contactid = ".$table_prefix."_contactdetails.contactid
					LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_contactdetails.contactid
					LEFT JOIN ".$table_prefix."_account ON ".$table_prefix."_account.accountid=".$table_prefix."_contactdetails.accountid
					LEFT JOIN ".$table_prefix."_customerdetails ON ".$table_prefix."_customerdetails.customerid=".$table_prefix."_contactdetails.contactid
					LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
					LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
					INNER JOIN (select $select_clause from ".$table_prefix."_contactdetails t
							INNER JOIN ".$table_prefix."_crmentity crm ON crm.crmid=t.contactid
							INNER JOIN ".$table_prefix."_contactaddress addr ON t.contactid = addr.contactaddressid
							INNER JOIN ".$table_prefix."_contactsubdetails subd ON addr.contactaddressid = subd.contactsubscriptionid
							LEFT JOIN ".$table_prefix."_contactscf tcf ON t.contactid = tcf.contactid
    						LEFT JOIN ".$table_prefix."_account acc ON acc.accountid=t.accountid
							LEFT JOIN ".$table_prefix."_customerdetails custd ON custd.customerid=t.contactid
							WHERE crm.deleted=0 group by $select_clause  HAVING COUNT(*)>1) temp
						ON ".get_on_clause($field_values,$ui_type_arr,$module)."
					WHERE ".$table_prefix."_crmentity.deleted=0 $sec_parameter ORDER BY $table_cols,".$table_prefix."_contactdetails.contactid ASC";

		}

	else if($module == 'Accounts')
		{
			$ret_arr = get_special_on_clause($field_values);
			$select_clause = $ret_arr['sel_clause'];
			$on_clause = $ret_arr['on_clause'];
			$nquery="SELECT ".$table_prefix."_account.accountid AS recordid,".$table_prefix."_users_last_import.deleted,".$table_cols."
				FROM ".$table_prefix."_account
				INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_account.accountid
				INNER JOIN ".$table_prefix."_accountbillads ON ".$table_prefix."_account.accountid = ".$table_prefix."_accountbillads.accountaddressid
				INNER JOIN ".$table_prefix."_accountshipads ON ".$table_prefix."_account.accountid = ".$table_prefix."_accountshipads.accountaddressid
				LEFT JOIN ".$table_prefix."_accountscf ON ".$table_prefix."_account.accountid=".$table_prefix."_accountscf.accountid
				LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_account.accountid
				LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
				INNER JOIN (select $select_clause from ".$table_prefix."_account t
							INNER JOIN ".$table_prefix."_crmentity crm ON crm.crmid=t.accountid
							INNER JOIN ".$table_prefix."_accountbillads badd ON t.accountid = badd.accountaddressid
							INNER JOIN ".$table_prefix."_accountshipads sadd ON t.accountid = sadd.accountaddressid
							LEFT JOIN ".$table_prefix."_accountscf tcf ON t.accountid = tcf.accountid
							WHERE crm.deleted=0 group by $select_clause HAVING COUNT(*)>1) temp
					ON ".get_on_clause($field_values,$ui_type_arr,$module)."
				WHERE ".$table_prefix."_crmentity.deleted=0 $sec_parameter ORDER BY $table_cols,".$table_prefix."_account.accountid ASC";

		}
	else if($module == 'Leads')
		{
			$ret_arr = get_special_on_clause($field_values);
			$select_clause = $ret_arr['sel_clause'];
			$on_clause = $ret_arr['on_clause'];
			$nquery="select ".$table_prefix."_leaddetails.leadid as recordid, ".$table_prefix."_users_last_import.deleted,$table_cols
					FROM ".$table_prefix."_leaddetails
					INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_leaddetails.leadid
					INNER JOIN ".$table_prefix."_leadsubdetails ON ".$table_prefix."_leadsubdetails.leadsubscriptionid = ".$table_prefix."_leaddetails.leadid
					INNER JOIN ".$table_prefix."_leadaddress ON ".$table_prefix."_leadaddress.leadaddressid = ".$table_prefix."_leadsubdetails.leadsubscriptionid
					LEFT JOIN ".$table_prefix."_leadscf ON ".$table_prefix."_leadscf.leadid=".$table_prefix."_leaddetails.leadid
					LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
					LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
					LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_leaddetails.leadid
					INNER JOIN (select $select_clause from ".$table_prefix."_leaddetails t
							INNER JOIN ".$table_prefix."_crmentity crm ON crm.crmid=t.leadid
							INNER JOIN ".$table_prefix."_leadsubdetails subd ON subd.leadsubscriptionid = t.leadid
							INNER JOIN ".$table_prefix."_leadaddress addr ON addr.leadaddressid = subd.leadsubscriptionid
							LEFT JOIN ".$table_prefix."_leadscf tcf ON tcf.leadid=t.leadid
							WHERE crm.deleted=0 and t.converted = 0 group by $select_clause HAVING COUNT(*)>1) temp
						ON ".get_on_clause($field_values,$ui_type_arr,$module)."
				WHERE ".$table_prefix."_crmentity.deleted=0 AND ".$table_prefix."_leaddetails.converted = 0 $sec_parameter ORDER BY $table_cols,".$table_prefix."_leaddetails.leadid ASC";

		}
	else if($module == 'Products')
		{
			$ret_arr = get_special_on_clause($field_values);
			$select_clause = $ret_arr['sel_clause'];
			$on_clause = $ret_arr['on_clause'];

			$nquery="SELECT ".$table_prefix."_products.productid AS recordid,".$table_prefix."_users_last_import.deleted,".$table_cols."
				FROM ".$table_prefix."_products
				INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_products.productid
				LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_products.productid
				LEFT JOIN ".$table_prefix."_productcf ON ".$table_prefix."_productcf.productid = ".$table_prefix."_products.productid
				INNER JOIN (select $select_clause from ".$table_prefix."_products t
						INNER JOIN ".$table_prefix."_crmentity crm ON crm.crmid=t.productid
						LEFT JOIN ".$table_prefix."_productcf tcf ON tcf.productid=t.productid
						WHERE crm.deleted=0 group by $select_clause HAVING COUNT(*)>1) temp
					ON ".get_on_clause($field_values,$ui_type_arr,$module)."
				WHERE ".$table_prefix."_crmentity.deleted=0 ORDER BY $table_cols,".$table_prefix."_products.productid ASC";

		}
		else if($module == 'HelpDesk')
		{
			$ret_arr = get_special_on_clause($field_values);
			$select_clause = $ret_arr['sel_clause'];
			$on_clause = $ret_arr['on_clause'];
			$nquery="SELECT ".$table_prefix."_troubletickets.ticketid AS recordid,".$table_prefix."_users_last_import.deleted,".$table_cols."
				FROM ".$table_prefix."_troubletickets
				INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_troubletickets.ticketid
				LEFT JOIN ".$table_prefix."_account ON ".$table_prefix."_account.accountid = ".$table_prefix."_troubletickets.parent_id
				LEFT JOIN ".$table_prefix."_contactdetails ON ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_troubletickets.parent_id
				LEFT JOIN ".$table_prefix."_ticketcf ON ".$table_prefix."_ticketcf.ticketid = ".$table_prefix."_troubletickets.ticketid
				LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_crmentity.smownerid = ".$table_prefix."_users.id
				LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_troubletickets.ticketid
				LEFT JOIN ".$table_prefix."_attachments ON ".$table_prefix."_attachments.attachmentsid=".$table_prefix."_crmentity.crmid
				LEFT JOIN ".$table_prefix."_ticketcomments ON ".$table_prefix."_ticketcomments.ticketid = ".$table_prefix."_crmentity.crmid
				INNER JOIN (select $select_clause from ".$table_prefix."_troubletickets t
						INNER JOIN ".$table_prefix."_crmentity crm ON crm.crmid=t.ticketid
						LEFT JOIN ".$table_prefix."_account acc ON acc.accountid = t.parent_id
						LEFT JOIN ".$table_prefix."_contactdetails contd ON contd.contactid = t.parent_id
						LEFT JOIN ".$table_prefix."_ticketcf tcf ON tcf.ticketid = t.ticketid
						WHERE crm.deleted=0 group by $select_clause HAVING COUNT(*)>1) temp
					ON ".get_on_clause($field_values,$ui_type_arr,$module)."
				WHERE ".$table_prefix."_crmentity.deleted=0". $sec_parameter ." ORDER BY $table_cols,".$table_prefix."_troubletickets.ticketid ASC";

		}
		else if($module == 'Potentials')
		{
			$ret_arr = get_special_on_clause($field_values);
			$select_clause = $ret_arr['sel_clause'];
			$on_clause = $ret_arr['on_clause'];
			$nquery="SELECT ".$table_prefix."_potential.potentialid AS recordid,
				".$table_prefix."_users_last_import.deleted,".$table_cols."
				FROM ".$table_prefix."_potential
				INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_potential.potentialid
				LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
				LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_potential.potentialid
				LEFT JOIN ".$table_prefix."_potentialscf ON ".$table_prefix."_potentialscf.potentialid = ".$table_prefix."_potential.potentialid
				INNER JOIN (select $select_clause from ".$table_prefix."_potential t
						INNER JOIN ".$table_prefix."_crmentity crm ON crm.crmid=t.potentialid
						LEFT JOIN ".$table_prefix."_potentialscf tcf ON tcf.potentialid=t.potentialid
						WHERE crm.deleted=0 group by $select_clause HAVING COUNT(*)>1) temp
					ON ".get_on_clause($field_values,$ui_type_arr,$module)."
				WHERE ".$table_prefix."_crmentity.deleted=0 $sec_parameter ORDER BY $table_cols,".$table_prefix."_potential.potentialid ASC";

		}
		else if($module == 'Vendors')
		{
			$ret_arr = get_special_on_clause($field_values);
			$select_clause = $ret_arr['sel_clause'];
			$on_clause = $ret_arr['on_clause'];
			$nquery="SELECT ".$table_prefix."_vendor.vendorid AS recordid,
				".$table_prefix."_users_last_import.deleted,".$table_cols."
				FROM ".$table_prefix."_vendor
				INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_vendor.vendorid
				LEFT JOIN ".$table_prefix."_vendorcf ON ".$table_prefix."_vendorcf.vendorid=".$table_prefix."_vendor.vendorid
				LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_vendor.vendorid
				INNER JOIN (select $select_clause from ".$table_prefix."_vendor t
						INNER JOIN ".$table_prefix."_crmentity crm ON crm.crmid=t.vendorid
						LEFT JOIN ".$table_prefix."_vendorcf tcf ON tcf.vendorid=t.vendorid
						WHERE crm.deleted=0 group by $select_clause HAVING COUNT(*)>1) temp
					ON ".get_on_clause($field_values,$ui_type_arr,$module)."
				WHERE ".$table_prefix."_crmentity.deleted=0 ORDER BY $table_cols,".$table_prefix."_vendor.vendorid ASC";

		} else {
			$ret_arr = get_special_on_clause($field_values);
			$select_clause = $ret_arr['sel_clause'];
			$on_clause = $ret_arr['on_clause'];
			$modObj = CRMEntity::getInstance($module);
			if ($modObj != null && method_exists($modObj, 'getDuplicatesQuery')) {
				$nquery = $modObj->getDuplicatesQuery($module,$table_cols,$field_values,$ui_type_arr,$select_clause);
			}
		}
	}
	else
	{
		/*
		 * mycrmv@2707m
		 * 
		 * di seguito è stato sostituito $inner_table_cols a $table_cols nelle subquery
		 //mycrmv@41849 : inner_table_cols anche nell'ordinamento (altrimenti non raggruppa correttamente poi nella visualizzazione grafica)
		 
		 */
		if($module == 'Contacts')
		{
			$nquery = "SELECT ".$table_prefix."_contactdetails.contactid AS recordid,
					".$table_prefix."_users_last_import.deleted,".$table_cols."
					FROM ".$table_prefix."_contactdetails
					INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_contactdetails.contactid
					INNER JOIN ".$table_prefix."_contactaddress ON ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_contactaddress.contactaddressid
					INNER JOIN ".$table_prefix."_contactsubdetails ON ".$table_prefix."_contactaddress.contactaddressid = ".$table_prefix."_contactsubdetails.contactsubscriptionid
					LEFT JOIN ".$table_prefix."_contactscf ON ".$table_prefix."_contactscf.contactid = ".$table_prefix."_contactdetails.contactid
					LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_contactdetails.contactid
					LEFT JOIN ".$table_prefix."_account ON ".$table_prefix."_account.accountid=".$table_prefix."_contactdetails.accountid
					LEFT JOIN ".$table_prefix."_customerdetails ON ".$table_prefix."_customerdetails.customerid=".$table_prefix."_contactdetails.contactid
					LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
					LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
					INNER JOIN (SELECT $inner_table_cols
							FROM ".$table_prefix."_contactdetails
							INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_contactdetails.contactid
							INNER JOIN ".$table_prefix."_contactaddress ON ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_contactaddress.contactaddressid
							INNER JOIN ".$table_prefix."_contactsubdetails ON ".$table_prefix."_contactaddress.contactaddressid = ".$table_prefix."_contactsubdetails.contactsubscriptionid
							LEFT JOIN ".$table_prefix."_contactscf ON ".$table_prefix."_contactscf.contactid = ".$table_prefix."_contactdetails.contactid
							LEFT JOIN ".$table_prefix."_account ON ".$table_prefix."_account.accountid=".$table_prefix."_contactdetails.accountid
							LEFT JOIN ".$table_prefix."_customerdetails ON ".$table_prefix."_customerdetails.customerid=".$table_prefix."_contactdetails.contactid
							LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
							LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
							WHERE ".$table_prefix."_crmentity.deleted=0 $sec_parameter
							GROUP BY ".$inner_table_cols." HAVING COUNT(*)>1) temp
						ON ".get_on_clause($field_values,$ui_type_arr,$module) ."
	                                WHERE ".$table_prefix."_crmentity.deleted=0 $sec_parameter ORDER BY $inner_table_cols,".$table_prefix."_contactdetails.contactid ASC";

		}
		else if($module == 'Accounts')
		{
			$nquery="SELECT ".$table_prefix."_account.accountid AS recordid,
				".$table_prefix."_users_last_import.deleted,$table_cols,$inner_table_cols
				FROM ".$table_prefix."_account
				INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_account.accountid
				INNER JOIN ".$table_prefix."_accountbillads ON ".$table_prefix."_account.accountid = ".$table_prefix."_accountbillads.accountaddressid
				INNER JOIN ".$table_prefix."_accountshipads ON ".$table_prefix."_account.accountid = ".$table_prefix."_accountshipads.accountaddressid
				LEFT JOIN ".$table_prefix."_accountscf ON ".$table_prefix."_account.accountid=".$table_prefix."_accountscf.accountid
				LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_account.accountid
				LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
				INNER JOIN (SELECT $inner_table_cols
					FROM ".$table_prefix."_account
					INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_account.accountid
					INNER JOIN ".$table_prefix."_accountbillads ON ".$table_prefix."_account.accountid = ".$table_prefix."_accountbillads.accountaddressid
					INNER JOIN ".$table_prefix."_accountshipads ON ".$table_prefix."_account.accountid = ".$table_prefix."_accountshipads.accountaddressid
					LEFT JOIN ".$table_prefix."_accountscf ON ".$table_prefix."_account.accountid=".$table_prefix."_accountscf.accountid
					LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
					LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
					WHERE ".$table_prefix."_crmentity.deleted=0 $sec_parameter
					GROUP BY ".$inner_table_cols." HAVING COUNT(*)>1) temp
				ON ".get_on_clause($field_values,$ui_type_arr,$module) ."
                                WHERE ".$table_prefix."_crmentity.deleted=0 $sec_parameter ORDER BY $inner_table_cols,".$table_prefix."_account.accountid ASC";
		}
		else if($module == 'Leads')
		{
			$nquery = "SELECT ".$table_prefix."_leaddetails.leadid AS recordid, ".$table_prefix."_users_last_import.deleted,$table_cols,$inner_table_cols
					FROM ".$table_prefix."_leaddetails
					INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_leaddetails.leadid
					INNER JOIN ".$table_prefix."_leadsubdetails ON ".$table_prefix."_leadsubdetails.leadsubscriptionid = ".$table_prefix."_leaddetails.leadid
					INNER JOIN ".$table_prefix."_leadaddress ON ".$table_prefix."_leadaddress.leadaddressid = ".$table_prefix."_leadsubdetails.leadsubscriptionid
					LEFT JOIN ".$table_prefix."_leadscf ON ".$table_prefix."_leadscf.leadid=".$table_prefix."_leaddetails.leadid
					LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
					LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
					LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_leaddetails.leadid
					INNER JOIN (SELECT $inner_table_cols
							FROM ".$table_prefix."_leaddetails
							INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_leaddetails.leadid
							INNER JOIN ".$table_prefix."_leadsubdetails ON ".$table_prefix."_leadsubdetails.leadsubscriptionid = ".$table_prefix."_leaddetails.leadid
							INNER JOIN ".$table_prefix."_leadaddress ON ".$table_prefix."_leadaddress.leadaddressid = ".$table_prefix."_leadsubdetails.leadsubscriptionid
							LEFT JOIN ".$table_prefix."_leadscf ON ".$table_prefix."_leadscf.leadid=".$table_prefix."_leaddetails.leadid
							LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
							LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
							WHERE ".$table_prefix."_crmentity.deleted=0 AND ".$table_prefix."_leaddetails.converted = 0 $sec_parameter
							GROUP BY $inner_table_cols HAVING COUNT(*)>1) temp
					ON ".get_on_clause($field_values,$ui_type_arr,$module) ."
					WHERE ".$table_prefix."_crmentity.deleted=0  AND ".$table_prefix."_leaddetails.converted = 0 $sec_parameter ORDER BY $inner_table_cols,".$table_prefix."_leaddetails.leadid ASC";

		}
		else if($module == 'Products')
		{
			$nquery = "SELECT ".$table_prefix."_products.productid AS recordid,
				".$table_prefix."_users_last_import.deleted,$table_cols,$inner_table_cols
				FROM ".$table_prefix."_products
				INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_products.productid
				LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_products.productid
				LEFT JOIN ".$table_prefix."_productcf ON ".$table_prefix."_productcf.productid = ".$table_prefix."_products.productid
				INNER JOIN (SELECT $inner_table_cols
							FROM ".$table_prefix."_products
							INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_products.productid
							LEFT JOIN ".$table_prefix."_productcf ON ".$table_prefix."_productcf.productid = ".$table_prefix."_products.productid
							WHERE ".$table_prefix."_crmentity.deleted=0
							GROUP BY ".$inner_table_cols." HAVING COUNT(*)>1) temp
				ON ".get_on_clause($field_values,$ui_type_arr,$module) ."
                                WHERE ".$table_prefix."_crmentity.deleted=0  ORDER BY $inner_table_cols,".$table_prefix."_products.productid ASC";
		}
		else if($module == "HelpDesk")
		{
			$nquery = "SELECT ".$table_prefix."_troubletickets.ticketid AS recordid,
				".$table_prefix."_users_last_import.deleted,$table_cols,$inner_table_cols
				FROM ".$table_prefix."_troubletickets
				INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_troubletickets.ticketid
				LEFT JOIN ".$table_prefix."_ticketcf ON ".$table_prefix."_ticketcf.ticketid = ".$table_prefix."_troubletickets.ticketid
				LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_troubletickets.ticketid
				LEFT JOIN ".$table_prefix."_attachments ON ".$table_prefix."_attachments.attachmentsid=".$table_prefix."_crmentity.crmid
				LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				LEFT JOIN ".$table_prefix."_contactdetails ON ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_troubletickets.parent_id
				LEFT JOIN ".$table_prefix."_ticketcomments ON ".$table_prefix."_ticketcomments.ticketid = ".$table_prefix."_crmentity.crmid
				INNER JOIN (SELECT $inner_table_cols FROM ".$table_prefix."_troubletickets
							INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_troubletickets.ticketid
							LEFT JOIN ".$table_prefix."_ticketcf ON ".$table_prefix."_ticketcf.ticketid = ".$table_prefix."_troubletickets.ticketid
							LEFT JOIN ".$table_prefix."_attachments ON ".$table_prefix."_attachments.attachmentsid=".$table_prefix."_crmentity.crmid
							LEFT JOIN ".$table_prefix."_contactdetails ON ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_troubletickets.parent_id
							LEFT JOIN ".$table_prefix."_ticketcomments ON ".$table_prefix."_ticketcomments.ticketid = ".$table_prefix."_crmentity.crmid
							LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
							LEFT JOIN ".$table_prefix."_contactdetails contd ON contd.contactid = ".$table_prefix."_troubletickets.parent_id
				WHERE ".$table_prefix."_crmentity.deleted=0 $sec_parameter
							GROUP BY ".$inner_table_cols." HAVING COUNT(*)>1) temp
				ON ".get_on_clause($field_values,$ui_type_arr,$module) ."
                                WHERE ".$table_prefix."_crmentity.deleted=0 $sec_parameter ORDER BY $inner_table_cols,".$table_prefix."_troubletickets.ticketid ASC";
		}
		else if($module == "Potentials")
		{
			$nquery = "SELECT ".$table_prefix."_potential.potentialid AS recordid,
				".$table_prefix."_users_last_import.deleted,$table_cols,$inner_table_cols
				FROM ".$table_prefix."_potential
				INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_potential.potentialid
				LEFT JOIN ".$table_prefix."_potentialscf ON ".$table_prefix."_potentialscf.potentialid = ".$table_prefix."_potential.potentialid
				LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_potential.potentialid
				LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
				INNER JOIN (SELECT $inner_table_cols
							FROM ".$table_prefix."_potential
							INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_potential.potentialid
							LEFT JOIN ".$table_prefix."_potentialscf ON ".$table_prefix."_potentialscf.potentialid = ".$table_prefix."_potential.potentialid
							LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
							LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
							WHERE ".$table_prefix."_crmentity.deleted=0 $sec_parameter
							GROUP BY ".$inner_table_cols." HAVING COUNT(*)>1) temp
				ON ".get_on_clause($field_values,$ui_type_arr,$module) ."
                                WHERE ".$table_prefix."_crmentity.deleted=0 $sec_parameter ORDER BY $inner_table_cols,".$table_prefix."_potential.potentialid ASC";
		}
		else if($module == "Vendors")
		{
			$nquery = "SELECT ".$table_prefix."_vendor.vendorid AS recordid,
				".$table_prefix."_users_last_import.deleted,$table_cols,$inner_table_cols
				FROM ".$table_prefix."_vendor
				INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_vendor.vendorid
				LEFT JOIN ".$table_prefix."_vendorcf ON ".$table_prefix."_vendorcf.vendorid=".$table_prefix."_vendor.vendorid
				LEFT JOIN ".$table_prefix."_users_last_import ON ".$table_prefix."_users_last_import.bean_id=".$table_prefix."_vendor.vendorid
				INNER JOIN (SELECT $inner_table_cols
							FROM ".$table_prefix."_vendor
							INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_vendor.vendorid
							LEFT JOIN ".$table_prefix."_vendorcf ON ".$table_prefix."_vendorcf.vendorid=".$table_prefix."_vendor.vendorid
							WHERE ".$table_prefix."_crmentity.deleted=0
							GROUP BY ".$inner_table_cols." HAVING COUNT(*)>1) temp
				ON ".get_on_clause($field_values,$ui_type_arr,$module) ."
                                WHERE ".$table_prefix."_crmentity.deleted=0  ORDER BY $inner_table_cols,".$table_prefix."_vendor.vendorid ASC";
		} else {
			$modObj = CRMEntity::getInstance($module);
			if ($modObj != null && method_exists($modObj, 'getDuplicatesQuery')) {
				//mycrmv@2707m
				if (isset($inner_table_cols) && trim($inner_table_cols) != '') {
					$inner_table_cols = str_replace($modObj->table_name.'.','t.',$inner_table_cols);
					$inner_table_cols = str_replace($modObj->customFieldTable[0].'.','tcf.',$inner_table_cols);
				}
				//TODO passare la variabile $where a tutte le funzioni getDuplicatesQuery
				$nquery = $modObj->getDuplicatesQuery($module,$table_cols,$field_values,$ui_type_arr,$inner_table_cols);
				//mycrmv@2707me
			}
		}
	}
	return $nquery;
}
function getDuplicateRecordSelectedFields($module)
{
	global $adb,$current_user,$table_prefix;

	$customView = new CustomView($module);
	$viewid = $customView->getViewId($module);
	if (empty($viewid)) {
		return false;
	}

	$fieldname_query = "select columnname from {$table_prefix}_cvcolumnlist where cvid = ? order by columnindex";
	$fieldname_result = $adb->pquery($fieldname_query, array($viewid));
	if ($fieldname_result && $adb->num_rows($fieldname_result) > 0) {
		$field_ids = array();
		while($row=$adb->fetchByAssoc($fieldname_result)) {
			$tmp_columns = explode(':',$row['columnname']);
			$result = $adb->pquery("select fieldid from {$table_prefix}_field where tablename = ? and columnname = ? and fieldname = ?",array($tmp_columns[0],$tmp_columns[1],$tmp_columns[2]));
			if ($result && $adb->num_rows($result) > 0) {
				$field_ids[] = $adb->query_result($result,0,'fieldid');
			}
		}
	}
	
	//In future if we want to change a id mapping to name or other string then we can add that elements in this array.
	//$fld_table_arr = Array("vtiger_contactdetails.account_id"=>"vtiger_account.accountname");
	//$special_fld_arr = Array("account_id"=>"accountname");
	
	$fld_table_arr = Array();
	$special_fld_arr = Array();
	$tabid = getTabid($module);
	
	$fieldname_query="select fieldname,fieldlabel,uitype,tablename,columnname from ".$table_prefix."_field where fieldid in
					(".generateQuestionMarks($field_ids).") and ".$table_prefix."_field.presence in (0,2)";
	if ($adb->isMssql()) {
		$fieldname_query.=" order by case fieldid";
		$i=0;
		foreach($field_ids as $field_id) {
			$fieldname_query.=" when $field_id THEN ".$i++;
		}
		$fieldname_query.=" else $i end";
	}
	//TODO: elseif Oracle elseif MySQL
	$fieldname_result = $adb->pquery($fieldname_query, $field_ids);

	$field_num_rows = $adb->num_rows($fieldname_result);

	$fld_arr = array();
	$col_arr = array();
	for($j=0;$j< $field_num_rows;$j ++)
	{
		$tablename = $adb->query_result($fieldname_result,$j,'tablename');
		$column_name = $adb->query_result($fieldname_result,$j,'columnname');
		$field_name = $adb->query_result($fieldname_result,$j,'fieldname');
		$field_lbl = $adb->query_result($fieldname_result,$j,'fieldlabel');
		$ui_type = $adb->query_result($fieldname_result,$j,'uitype');
		$table_col = $tablename.".".$column_name;
		if(getFieldVisibilityPermission($module,$current_user->id,$field_name) == 0)
		{
			$fld_name = ($special_fld_arr[$field_name] != '')?$special_fld_arr[$field_name]:$field_name;

			$fld_arr[] = $fld_name;
			$col_arr[] = $column_name;
			if($fld_table_arr[$table_col] != '')
				$table_col = $fld_table_arr[$table_col];

			$field_values_array['fieldnames_list'][] = $table_col . "." . $fld_name;
			$fld_labl_arr[]=$field_lbl;
			$uitype[$field_name]=$ui_type;
		}
	}
	$field_values_array['fieldnames_list']=implode(",",$field_values_array['fieldnames_list']);
	$field_values=implode(",",$fld_arr);
	$field_values_array['fieldnames']=$field_values;
	$field_values_array["fieldnames_array"]=$fld_arr;
	$field_values_array["columnnames_array"]=$col_arr;
	$field_values_array['fieldlabels_array']=$fld_labl_arr;
	$field_values_array['fieldname_uitype']=$uitype;

	return $field_values_array;
}
?>