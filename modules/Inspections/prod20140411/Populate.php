<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
 * ********************************************************************************** */
// Switch the working directory to base
chdir(dirname(__FILE__) . '/../..');

include_once 'include/Zend/Json.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'include/utils/VtlibUtils.php';
include_once 'include/Webservices/Create.php';
include_once 'modules/Inspections/Inspections.php';
include_once 'modules/Inspections/Inspections_conf.php';
include_once 'include/QueryGenerator/QueryGenerator.php';

class Inspections_Populate {
	
	function populateNow($request) {
		$returnURL = false;
		
		try {

			if(!vtlib_isModuleActive('Inspections')) throw new Exception('Inspections is not active');
			
			$returnURL = 'localhost:8040/index.php?module=Inspections&action=index';

			// Retrieve user information
			$user = CRMEntity::getInstance('Users');
			$user->id=$user->getActiveAdminId();
			$user->retrieve_entity_info($user->id, 'Users');
			global $adb, $current_user,$log;
			global $table_prefix, $cf_product_category, $cf_account_category,$insp_activitytype,$insp_eventstatus;
			$interval = "12 MONTH";
			$query = "
				select 
					".$table_prefix."_inspections.inspectionsid,
					'Revisione Periodica' AS inspection_name,
					".$table_prefix."_salesorder.accountid AS accountid,
					".$table_prefix."_account.accountname AS accountname,
					".$table_prefix."_account.email1 AS email,
					".$table_prefix."_account.account_no AS account_no,
					".$table_prefix."_salesorder.salesorderid AS salesorderid,
					".$table_prefix."_crmentity.smownerid AS smownerid,
					".$table_prefix."_crmentity.createdtime AS createdtime,
					".$table_prefix."_crmentity.modifiedtime AS modifiedtime,
					case when ".$table_prefix."_products.productid is not null then ".$table_prefix."_products.productid else ".$table_prefix."_service.serviceid end as productid, 
					case when ".$table_prefix."_products.productid is not null then ".$table_prefix."_products.productname else ".$table_prefix."_service.servicename end as product_description, 
					case when ".$table_prefix."_productcf.productid is not null then ".$table_prefix."_productcf.".$cf_product_category." else 'ND' end as product_category, -- cf_803
					case when ".$table_prefix."_products.productid is not null then ".$table_prefix."_products.vendor_id else 'ND' end as vendor_id, 
					".sql_date_add($table_prefix).", -- Verificare qual'è la data dell'Ordine di vendita e come calcolare la due_date
					'Aperta' AS inspection_state, -- Eventualmente calcolare se in scadenza
					NULL AS inspection_date, -- a NULL perchè non è stata fatta
					".$table_prefix."_salesorder.data_ordine_ven AS salesorder_date, -- Verificare qual'è la data dell'Ordine di vendita e come calcolare la due_date
					".$table_prefix."_accountscf.".$cf_account_category." AS account_cat, -- cf_762 per rotho
					case when ".$table_prefix."_products.productid is not null then ".$table_prefix."_products.inspection_frequency else 'ND' end as inspection_frequency, 
					".$table_prefix."_inventoryproductrel.quantity as inspection_qty,
					case when ".$table_prefix."_products.productid is not null then 'Products' else 'Services' end as entitytype, 
					case when ".$table_prefix."_products.productid is not null then ".$table_prefix."_products.unit_price else ".$table_prefix."_service.unit_price end as unit_price, 
					case when ".$table_prefix."_products.productid is not null then ".$table_prefix."_products.productcode else ".$table_prefix."_service.service_no end as productcode, 
					case when ".$table_prefix."_products.productid is not null then ".$table_prefix."_products.qtyinstock else 0 end as qtyinstock, 
					".$table_prefix."_salesorder.subject as salesorder_subject 
				from ".$table_prefix."_inventoryproductrel 
				left join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_inventoryproductrel.id 
				left join ".$table_prefix."_products on ".$table_prefix."_products.productid=".$table_prefix."_inventoryproductrel.productid 
				left join ".$table_prefix."_productcf on ".$table_prefix."_productcf.productid=".$table_prefix."_inventoryproductrel.productid 
				left join ".$table_prefix."_service on ".$table_prefix."_service.serviceid=".$table_prefix."_inventoryproductrel.productid  
				left join ".$table_prefix."_salesorder on ".$table_prefix."_salesorder.salesorderid=".$table_prefix."_inventoryproductrel.id  
				left join ".$table_prefix."_inspections on ".$table_prefix."_inspections.salesorderid = ".$table_prefix."_salesorder.salesorderid 
				left join ".$table_prefix."_account on ".$table_prefix."_account.accountid = ".$table_prefix."_salesorder.accountid 
				left join ".$table_prefix."_accountscf on ".$table_prefix."_accountscf.accountid = ".$table_prefix."_account.accountid
				where 
				".$table_prefix."_crmentity.deleted =0 
				AND ".$table_prefix."_products.inspection_frequency IS NOT NULL AND ".$table_prefix."_products.inspection_frequency <>'lbl_nd'
				AND ".$table_prefix."_inspections.inspectionsid IS  NULL AND ".$table_prefix."_inventoryproductrel.quantity > 0
				AND ".$table_prefix."_salesorder.salesorderid IS NOT NULL 
				ORDER BY ".$table_prefix."_salesorder.salesorderid, sequence_no
			";
			$result = $adb->query($query); 
			// echo $query;
			while($row=$adb->fetchByAssoc($result))
			{
				$iqty = intval($row['inspection_qty']);
				for ($i = 1; $i <= $iqty; $i++) {
					$newInspection = CRMEntity::getInstance('Inspections'); //$adb->getUniqueID($table_prefix."_crmentity");??
					vtlib_setup_modulevars('Inspections',$newInspection);
					$newInspection->column_fields['inspection_name'] = $row['salesorder_subject'].'_'.$row['product_description']."_[". $i . "\\" . intval($row['inspection_qty'])."]";
					$newInspection->column_fields['accountid']=$row['accountid'];
					$newInspection->column_fields['account_cat']=$row['account_cat'];
					$newInspection->column_fields['account_email']=$row['email'];
					$newInspection->column_fields['salesorderid']=$row['salesorderid'];
					$newInspection->column_fields['salesorder_date']=$row['salesorder_date'];
					$newInspection->column_fields['productid']=$row['productid'];
					$newInspection->column_fields['inspection_qty']=$row['inspection_qty'];
					$newInspection->column_fields['inspection_prog'] = $i;
					$newInspection->column_fields['description'] = "Inspection for \"". $row['product_description'] . "\" (". $i . "\\" . intval($row['inspection_qty']).") sold on \"".$row['salesorder_subject'] . "\" , date: ". $row['salesorder_date']  . " account ".$row['accountname']." (" . $row['account_no'].")";
					$newInspection->column_fields['product_serialno']=$row['product_serialno'];
					$newInspection->column_fields['product_category']=$row['product_category'];
					$newInspection->column_fields['inspection_frequency']=$row['inspection_frequency'];
					$newInspection->column_fields['vendor_id']=$row['vendor_id'];
					$newInspection->column_fields['due_date']=$row['due_date'];
					$newInspection->column_fields['inspection_state']=$row['inspection_state'];
					$newInspection->column_fields['inspection_date']=$row['inspection_date'];
					$newInspection->column_fields['smownerid']=$row['smownerid'];
					$newInspection->column_fields['assigned_user_id']=$row['smownerid'];
					$newInspection->column_fields['createdtime']=$row["createdtime"];
					$newInspection->column_fields['modifiedtime']= $row["modifiedtime"];
					$newInspection->save($module_name='Inspections',$longdesc=false);
					// echo "<p>".$i.$row['account_no']."</p>";
				}
			}
			// $this->sendResponse($returnURL, 'ok');
			return;

		} catch (Exception $e) {
			//$this->sendResponse($returnURL, false, $e->getMessage());
			echo $e->getMessage();
			return;
		}
	}

	protected function sendResponse($url, $success=false, $failure=false) {
		if (empty($url)) {
			if ($success) $response = Zend_Json::encode(array('success' => true, 'result' => $success));
			else $response = Zend_Json::encode(array('success' => false, 'error' => array('message' => $failure)));

			// Support JSONP
			if (!empty($_REQUEST['callback'])) {
				$callback = vtlib_purify($_REQUEST['callback']);
				echo sprintf("%s(%s)", $callback, $response);
			} else {
				echo $response;
			}
		} else {
			header(sprintf("Location: http://%s&%s=%s", $url, ($success? 'success' : 'error'), ($success? $success: $failure)));
		}
	}
}

// NOTE: Take care of stripping slashes...
$inspectionPopulate = new Inspections_Populate();
$inspectionPopulate->populateNow($_REQUEST);
?>
