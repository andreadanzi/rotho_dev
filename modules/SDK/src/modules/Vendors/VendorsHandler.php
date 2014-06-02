<?php

class VendorsHandler extends VTEventHandler {
    
    function handleEvent($eventName, $data) {
		global $adb, $current_user,$log;
		global $table_prefix, $insp_activitytype, $insp_activitytype;		
		if (!($data->focus instanceof Vendors)) {
			return;
		}
		if($eventName == 'vtiger.entity.beforesave') {
			// Entity is about to be saved, take required action
			$log->debug("handleEvent VendorsHandler vtiger.entity.beforesave entered");
			
			$log->debug("handleEvent VendorsHandler vtiger.entity.beforesave treminated");
		}
		if($eventName == 'vtiger.entity.aftersave') {
			// Entity is about to be saved, take required action
			$log->debug("handleEvent VendorsHandler vtiger.entity.aftersave entered");
			
			$focus = $data->focus;
			$client_code = $focus->column_fields['cf_1115'];
			if(empty($client_code)){
				$vendor_no = $focus->column_fields['vendor_no'];
				$vendor_number = intval(substr($vendor_no,4,strlen($vendor_no)-4));
				$client_code = sprintf("ZZZ%07d",$vendor_number);
				$sql = "UPDATE ".$table_prefix."_vendorcf SET cf_1115='".$client_code."' WHERE vendorid=".$focus->id;
				$adb->query($sql);
			}
			$log->debug("handleEvent VendorsHandler vtiger.entity.aftersave treminated");
		}		
    }
}


?>