<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
class HelpDeskHandler extends VTEventHandler {
	//danzi.tn@20140423 Handler per HelpDesk
	function handleEvent($eventName, $data) {
		global $adb, $current_user,$log;
		global $table_prefix;
		$when = array();
		$when['Prodotto incompleto']['Fornitore'] = "Vendor";
		$when['Difetto prodotto']['Fornitore'] = "Vendor";
		$when['Materiale danneggiato - confezione']['Fornitore'] = "Vendor";
		$when['Materiale danneggiato - confezione']['Trasportatore'] = "Vendor";
		$when['Quantita` errata']['Fornitore'] = "Vendor";
		$when['Articolo sbagliato']['Fornitore'] = "Vendor";
		$when['Consegna in ritardo']['Fornitore'] = "Vendor";
		$when['Consegna in ritardo']['Trasportatore'] = "Vendor";
		$when['Consegna in ritardo']['RB-Acquisto Interno'] = "Internal";
		$when['Smarrito']['Fornitore'] = "Vendor";
		// check irs a timcard we're saving.
		if (!($data->focus instanceof HelpDesk)) {
			return;
		}
		
		if($eventName == 'vtiger.entity.beforesave') {
			// Entity is about to be saved, take required action
			$log->debug("handleEvent vtiger.entity.beforesave entered");
			$log->debug("handleEvent vtiger.entity.beforesave treminated");
		}

		if($eventName == 'vtiger.entity.aftersave') {
			$log->debug("handleEvent vtiger.entity.aftersave entered");
			$module = $data->getModuleName();
			$focus = $data->focus;
			$id = $focus->id;
			// danzi.tn@20140512 verifica se c'è una NC già relazionata
			$nonconformitiesid = $this->_checkExistingNC($id);
			if($nonconformitiesid == 0)
			{
				$log->debug("handleEvent vtiger.entity.aftersave nothing found");
				$ticket_title = $focus->column_fields['ticket_title'];
				if(	isset($when[$focus->column_fields['ticketsubcategories']][$focus->column_fields['cf_798']] ) ) {
					$source = $when[$focus->column_fields['ticketsubcategories']][$focus->column_fields['cf_798']];
					$this->_newNonCoformity($id, $focus->column_fields,$source);
				}
			} else {
				$log->debug("handleEvent vtiger.entity.aftersave found nonconformitiesid=".$nonconformitiesid);
			}
			$log->debug("handleEvent vtiger.entity.aftersave terminated");
		}
	}
	
	// danzi.tn@20140512 verifica se c'è una NC già relazionata
	function _checkExistingNC($hd_id) {
		global $adb,  $table_prefix;
		$ticketid = 0;
		$nonconformitiesid = 0;
		$query = "SELECT  	".$table_prefix."_troubletickets.ticketid, 
							".$table_prefix."_nonconformities.nonconformitiesid, 
							".$table_prefix."_nonconformities.nonconformity_name 
							FROM ".$table_prefix."_nonconformities
							JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_nonconformities.nonconformitiesid AND ".$table_prefix."_crmentity.deleted =0
							JOIN ".$table_prefix."_crmentityrel ON ".$table_prefix."_crmentityrel.relcrmid = ".$table_prefix."_nonconformities.nonconformitiesid AND module='HelpDesk' AND relmodule='Nonconformities'
							JOIN ".$table_prefix."_troubletickets ON ".$table_prefix."_troubletickets.ticketid = ".$table_prefix."_crmentityrel.crmid  AND  ".$table_prefix."_troubletickets.ticketid = ?";
		$result = $adb->pquery($query,array($hd_id));
		if ($result && $adb->num_rows($result)>0) {			
			$ticketid = $adb->query_result($result,0,'ticketid');
			$nonconformitiesid = $adb->query_result($result,0,'nonconformitiesid');
		} else {
			$query = "SELECT  	
				".$table_prefix."_troubletickets.ticketid, 
				".$table_prefix."_nonconformities.nonconformitiesid, 
				".$table_prefix."_nonconformities.nonconformity_name,
				prodnonconf.productname
				FROM ".$table_prefix."_nonconformities
				JOIN ".$table_prefix."_nonconformitiescf ON ".$table_prefix."_nonconformitiescf.nonconformitiesid = ".$table_prefix."_nonconformities.nonconformitiesid
				JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_nonconformities.nonconformitiesid AND ".$table_prefix."_crmentity.deleted =0
				JOIN ".$table_prefix."_ticketcf ON ".$table_prefix."_ticketcf.cf_777 = ".$table_prefix."_nonconformitiescf.cf_1257  
				JOIN ".$table_prefix."_troubletickets ON ".$table_prefix."_troubletickets.ticketid = ".$table_prefix."_ticketcf.ticketid 
				JOIN ".$table_prefix."_products prodtickets ON prodtickets.productid = ".$table_prefix."_troubletickets.product_id
				JOIN ".$table_prefix."_products prodnonconf ON prodnonconf.productid = ".$table_prefix."_nonconformities.productid 
				LEFT JOIN ".$table_prefix."_crmentityrel ON ".$table_prefix."_crmentityrel.relcrmid = ".$table_prefix."_nonconformities.nonconformitiesid AND module='HelpDesk' AND relmodule='Nonconformities' 
				AND ".$table_prefix."_troubletickets.ticketid = ".$table_prefix."_crmentityrel.crmid 
				WHERE 
				prodnonconf.productname = prodtickets.productname
				AND ".$table_prefix."_crmentityrel.crmid IS NULL
				AND ".$table_prefix."_troubletickets.ticketid = ?
				ORDER BY ".$table_prefix."_crmentity.createdtime DESC";
			$result = $adb->pquery($query,array($hd_id));
			if ($result && $adb->num_rows($result)>0) {			
				$nonconformitiesid = $adb->query_result($result,0,'nonconformitiesid');
				$query = "INSERT INTO ".$table_prefix."_crmentityrel (crmid,module,relcrmid,relmodule) VALUES (?,'HelpDesk',?,'Nonconformities')";
				$result = $adb->pquery($query,array($hd_id,$nonconformitiesid));
			}
		}
		return $nonconformitiesid;
	}
	
	function _newNonCoformity($hd_id, $data_array, $source) {
		global $adb,  $current_user, $table_prefix;
		$product_id = $data_array['product_id'];
		$newNC = CRMEntity::getInstance('Nonconformities');
		vtlib_setup_modulevars('Nonconformities',$newNC);
		// CAMPI Del Ticket
		$ticketsubcategories = $data_array['ticketsubcategories'];
		$cf_798 = $data_array['cf_798']; // Errore da parte di
		$cf_1061 = $data_array['cf_1061']; // cf_1061 Fonte ESTERNO - INTERNO 
		$newNC->column_fields['nonconformity_name'] =  $data_array['ticket_title'] . " (AUTO. GEN.)";
		$newNC->column_fields['product_category'] = $data_array['product_cat'];
		if(!empty($product_id)) {
			$newNC->column_fields['product_id'] =  $product_id;
			$query = "SELECT ".$table_prefix."_products.product_cat, 
							 ".$table_prefix."_products.inspection_frequency, 
							 ".$table_prefix."_crmentity.description , 
							 ".$table_prefix."_products.vendor_id 		
					FROM ".$table_prefix."_products
					JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_products.productid = ".$table_prefix."_crmentity.crmid AND ".$table_prefix."_crmentity.deleted = 0
					WHERE ".$table_prefix."_products.productid = ?";
			$result = $adb->pquery($query,array($product_id));
			if ($result && $adb->num_rows($result)>0) {			
				$newNC->column_fields['product_description'] = $adb->query_result($result,0,'description');
				$newNC->column_fields['product_category'] = $adb->query_result($result,0,'product_cat');
				$newNC->column_fields['vendor_id'] = $adb->query_result($result,0,'vendor_id');
			}
		}
		$newNC->column_fields['nc_source'] = $source;
		$newNC->column_fields['createdtime'] = $data_array['createdtime'];
		$newNC->column_fields['modifiedtime'] = $data_array['createdtime'];
		// danzi.tn@20140603 assegnare al gruppo Ufficio Acquisti
		$newNC->column_fields['assigned_user_id'] = 133018; //$data_array['assigned_user_id'];
		$newNC->column_fields['smownerid'] = 133018; //$data_array['assigned_user_id'];
		// danzi.tn@20140630 aggiunto numero lotto
		$newNC->column_fields['cf_1257'] = $data_array['cf_777'];
		if($data_array['ticketcategories'] == "Prodotto") { 
			$newNC->column_fields['cf_1273'] = "Prodotto";
		}
		// danzi.tn@20140630e
		$newNC->column_fields['nonconformity_state'] = "Aperta"; // picklist
		$newNC->column_fields["description"] =  $data_array['description'].  " -- ". $data_array["ticket_title"] . " (".$data_array["ticket_no"].", '".$ticketsubcategories."', '".$cf_798."') --";
		$newNC->save($module_name='Nonconformities');
		$nc_id = $newNC->id;
		$insert_sql="INSERT INTO ".$table_prefix."_crmentityrel (crmid, module, relcrmid, relmodule) VALUES (?, 'HelpDesk', ?, 'Nonconformities')";
		$adb->pquery($insert_sql,array($hd_id,$nc_id));
	}
}
?>
