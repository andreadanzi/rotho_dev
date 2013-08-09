<?php

class ContactsHandler extends VTEventHandler {
    
    function handleEvent($eventName, $data) {
		global $adb, $current_user,$log;
		global $table_prefix, $insp_activitytype, $insp_activitytype;		
		if (!($data->focus instanceof Contacts)) {
			return;
		}
		if($eventName == 'vtiger.entity.aftersave') {
			// Entity is about to be saved, take required action
			$log->debug("handleEvent ContactsHandler vtiger.entity.aftersave entered");
			$focus = $data->focus;
			$ext_code_fieldname = 'ext_code'; // ext_code
			$ext_code = $focus->column_fields[$ext_code_fieldname];
			if(empty($ext_code)){
				$contact_no = $focus->column_fields['contact_no'];
				$contact_number = intval(substr($contact_no,3,strlen($contact_no)-3));
				$log->debug("handleEvent ContactsHandler $contact_number ".$contact_number);
				$ext_code = sprintf("ZZZ%07d",$contact_number);
				//$sql = "UPDATE ".$table_prefix."_contactscf SET cf_889='".$ext_code."' WHERE contactid=".$focus->id;
				$sql = "UPDATE ".$table_prefix."_contactdetails SET ext_code='".$ext_code."' WHERE contactid=".$focus->id;
				$log->debug("handleEvent ContactsHandler vtiger.entity.aftersave sql=".$sql);
				$adb->query($sql);
			}
			$log->debug("handleEvent ContactsHandler vtiger.entity.aftersave treminated");
		}		
    }
}


?>