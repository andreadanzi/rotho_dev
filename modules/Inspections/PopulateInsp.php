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

echo "Inizia PopulateInsp.php\n";


class Inspections_Populate {
	
	function populateNow() {
		// echo "Inizia Inspections_Populate::populateNow()";
		try {

			if(!vtlib_isModuleActive('Inspections')) throw new Exception('Inspections is not active');
			

			// Retrieve user information
			$user = CRMEntity::getInstance('Users');
			$user->id=$user->getActiveAdminId();
			$user->retrieve_entity_info($user->id, 'Users');
			global $adb, $current_user;
			global $table_prefix, $cf_product_category, $cf_account_category,$cf_account_base_language, $insp_activitytype,$insp_eventstatus;
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
					".$table_prefix."_accountscf.".$cf_account_base_language." AS account_baselang, -- cf_1113 per rotho
					case when ".$table_prefix."_products.productid is not null then ".$table_prefix."_products.inspection_frequency else 'ND' end as inspection_frequency, 
					".$table_prefix."_inventoryproductrel.sequence_no as sequence_no,
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
				AND ".$table_prefix."_salesorder.salesorderid IS NOT NULL  AND DATEADD( month, 12, ".$table_prefix."_salesorder.data_ordine_ven) BETWEEN DATEADD( month, -1,GETDATE())  AND  DATEADD( month, 1,GETDATE()) 
				ORDER BY ".$table_prefix."_salesorder.salesorderid, sequence_no
			";
			$result = $adb->query($query); 
			
			while($row=$adb->fetchByAssoc($result))
			{
				$iqty = intval($row['inspection_qty']);
				for ($i = 1; $i <= $iqty; $i++) {
					$newInspection = CRMEntity::getInstance('Inspections'); //$adb->getUniqueID($table_prefix."_crmentity");??
					vtlib_setup_modulevars('Inspections',$newInspection);
					$newInspection->column_fields['inspection_name'] = $row['year_due_date']."-".$row['salesorder_subject'].'_'.sprintf('%05d',$row['sequence_no']).'_'.$row['product_description']."_[". $i . "\\" . intval($row['inspection_qty'])."]";
					$newInspection->column_fields['inspection_uid'] = sprintf('%08d',$row['salesorderid']).'-'.sprintf('%05d',$row['sequence_no']).'-'.sprintf('%03d',$i);
					$newInspection->column_fields['inspection_sequence']=1;
					$newInspection->column_fields['accountid']=$row['accountid'];
					$newInspection->column_fields['account_cat']=$row['account_cat'];
					$newInspection->column_fields['account_baselang'] = $row['account_baselang'];
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
				}
			}
			
			$closedquery = "select 
				".$table_prefix."_inspections.*,
				".$table_prefix."_crmentity.description as description,
				".$table_prefix."_crmentity.smownerid as smownerid,
				".$table_prefix."_crmentity.modifiedtime,
				case 
                    when ".$table_prefix."_inspections.inspection_frequency = 'lbl_6m' then  
						dateadd(month,6,".$table_prefix."_inspections.inspection_date) 
                    when ".$table_prefix."_inspections.inspection_frequency = 'lbl_12m' then 
                        dateadd(month,12,".$table_prefix."_inspections.inspection_date)  
                    when ".$table_prefix."_inspections.inspection_frequency = 'lbl_18m' then 
                        dateadd(month,18,".$table_prefix."_inspections.inspection_date) 
                    when ".$table_prefix."_inspections.inspection_frequency = 'lbl_24m' then 
                        dateadd(month,24,".$table_prefix."_inspections.inspection_date) 
                    else
                        dateadd(month,12,".$table_prefix."_inspections.inspection_date) 
                    end
                    AS new_date,
				case 
                    when ".$table_prefix."_inspections.inspection_frequency = 'lbl_6m' then 
						YEAR( dateadd(month,6,".$table_prefix."_inspections.inspection_date) )
                    when ".$table_prefix."_inspections.inspection_frequency = 'lbl_12m' then 
						YEAR( dateadd(month,12,".$table_prefix."_inspections.inspection_date) )
                    when ".$table_prefix."_inspections.inspection_frequency = 'lbl_18m' then 
						YEAR( dateadd(month,18,".$table_prefix."_inspections.inspection_date) )
                    when ".$table_prefix."_inspections.inspection_frequency = 'lbl_24m' then 
                        YEAR( dateadd(month,24,".$table_prefix."_inspections.inspection_date) )
                    else
                        YEAR( dateadd(month,12,".$table_prefix."_inspections.inspection_date) )
                    end
                    AS year_new_date,
				previnsp.inspection_no
				from ".$table_prefix."_inspections
				INNER JOIN ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_inspections.inspectionsid
				left join ".$table_prefix."_inspections previnsp on previnsp.inspection_uid = ".$table_prefix."_inspections.inspection_uid and previnsp.inspectionsid <>  ".$table_prefix."_inspections.inspectionsid and previnsp.inspection_sequence = ".$table_prefix."_inspections.inspection_sequence  +1
				where 
				".$table_prefix."_inspections.inspection_state = 'Chiusa' and 
				previnsp.inspection_no is  null and ".$table_prefix."_inspections.inspection_date is not null 
				order by ".$table_prefix."_inspections.inspectionsid
			";
			$resultclosed = $adb->query($closedquery); 
			while($closedrow=$adb->fetchByAssoc($resultclosed))
			{
				$newInspection = CRMEntity::getInstance('Inspections'); //$adb->getUniqueID($table_prefix."_crmentity");??
				vtlib_setup_modulevars('Inspections',$newInspection);
				$explarray = explode('-',$closedrow['inspection_name']);
				array_splice($explarray,0,1,$closedrow['year_new_date']);
				$newInspection->column_fields['inspection_name'] =  implode('-',$explarray);
				$newInspection->column_fields['inspection_uid'] = $closedrow['inspection_uid'];
				$newInspection->column_fields['inspection_sequence']=$closedrow['inspection_sequence']+1;
				$newInspection->column_fields['accountid']=$closedrow['accountid'];
				$newInspection->column_fields['account_cat']=$closedrow['account_cat'];
				$newInspection->column_fields['account_email']=$closedrow['account_email'];
				$newInspection->column_fields['salesorderid']=$closedrow['salesorderid'];
				$newInspection->column_fields['salesorder_date']=$closedrow['salesorder_date'];
				$newInspection->column_fields['productid']=$closedrow['productid'];
				$newInspection->column_fields['inspection_qty']=$closedrow['inspection_qty'];
				$newInspection->column_fields['inspection_prog'] = $closedrow['inspection_prog'];
				$newInspection->column_fields['description'] = $closedrow['description'];
				$newInspection->column_fields['product_serialno']=$closedrow['product_serialno'];
				$newInspection->column_fields['product_category']=$closedrow['product_category'];
				$newInspection->column_fields['inspection_frequency']=$closedrow['inspection_frequency'];
				$newInspection->column_fields['vendor_id']=$closedrow['vendor_id'];
				$newInspection->column_fields['due_date']=$closedrow['new_date'];
				$newInspection->column_fields['inspection_state']='Aperta';
				$newInspection->column_fields['smownerid']=$closedrow['smownerid'];
				$newInspection->column_fields['assigned_user_id']=$closedrow['smownerid'];
				$newInspection->column_fields['createdtime']=$closedrow["modifiedtime"];
				$newInspection->column_fields['modifiedtime']= $closedrow["modifiedtime"];
				$newInspection->save($module_name='Inspections',$longdesc=false);
				
			}
			$sameorderquery="
			INSERT INTO ".$table_prefix."_crmentityrel
			(crmid,module,relcrmid,relmodule)
				select 
				".$table_prefix."_inspections.inspectionsid,  
				'Inspections',
				relinsp.inspectionsid,  
				'Inspections'
			from ".$table_prefix."_inspections
			INNER join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=".$table_prefix."_inspections.inspectionsid and ".$table_prefix."_crmentity.deleted=0
			LEFT JOIN ".$table_prefix."_inspections AS relinsp on relinsp.salesorderid = ".$table_prefix."_inspections.salesorderid and relinsp.inspectionsid <> ".$table_prefix."_inspections.inspectionsid 
			WHERE relinsp.inspectionsid is not null
			AND NOT EXISTS (
				select 
				1 
				from 
				".$table_prefix."_crmentityrel 
				where ".$table_prefix."_crmentityrel.crmid = ".$table_prefix."_inspections.inspectionsid and 
				".$table_prefix."_crmentityrel.relcrmid = relinsp.inspectionsid
			)
			";
			$adb->query($sameorderquery); 
			$sameuidquery="
			INSERT INTO ".$table_prefix."_crmentityrel
				(crmid,module,relcrmid,relmodule)
				select 
					".$table_prefix."_inspections.inspectionsid,  
					'Inspections',
					relinsp.inspectionsid,  
					'Inspections'
				from ".$table_prefix."_inspections
				INNER join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=".$table_prefix."_inspections.inspectionsid and ".$table_prefix."_crmentity.deleted=0
				LEFT JOIN ".$table_prefix."_inspections AS relinsp on relinsp.inspection_uid = ".$table_prefix."_inspections.inspection_uid and relinsp.inspectionsid <> ".$table_prefix."_inspections.inspectionsid 
				WHERE relinsp.inspectionsid is not null
				AND NOT EXISTS (
					select 
					1 
					from 
					".$table_prefix."_crmentityrel 
					where ".$table_prefix."_crmentityrel.crmid = ".$table_prefix."_inspections.inspectionsid and 
					".$table_prefix."_crmentityrel.relcrmid = relinsp.inspectionsid
				)
			";
			$adb->query($sameuidquery); 
			// echo "Inspections_Populate::populateNow() termina con successo!";
			return;

		} catch (Exception $e) {
			// echo "Inspections_Populate::populateNow() termina con errore ". $e->getMessage();
			return;
		}
	}

}

//  NOTE: Take care of stripping slashes...
$inspectionPopulate = new Inspections_Populate();
$inspectionPopulate->populateNow();

echo "Finisce PopulateInsp.php\n";
?>
