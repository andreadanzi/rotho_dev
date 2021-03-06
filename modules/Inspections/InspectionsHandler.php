<?php
include_once 'modules/Inspections/Inspections_conf.php';
class InspectionsHandler extends VTEventHandler {
    
    function handleEvent($eventName, $data) {
		global $adb, $current_user,$log;
		global $table_prefix, $insp_activitytype, $insp_activitytype;
		// check irs a timcard we're saving.
		if (!($data->focus instanceof Inspections)) {
			return;
		}
		
		if($eventName == 'vtiger.entity.beforesave') {
			// Entity is about to be saved, take required action
			$log->debug("handleEvent InspectionsHandler vtiger.entity.beforesave entered");
			$focus = $data->focus;
			$inspection_name = $focus->column_fields['inspection_name'];
			$pos = strrpos($inspection_name, "-");
			if( $pos === false)
			{
				$log->debug("handleEvent InspectionsHandler vtiger.entity.beforesave - not found");
				$focus->column_fields['inspection_name'] =  date('Y'). '-' . $inspection_name;
			}
			$inspection_uid = $focus->column_fields['inspection_uid'];
			$inspection_sequence = $focus->column_fields['inspection_sequence'];
			if( $inspection_sequence=='' )
			{
				$inspection_sequence = 1;
				$focus->column_fields['inspection_sequence'] = $inspection_sequence;
			}
			if( $inspection_uid == '' )
			{
				$inspection_uid = uniqid();
				$focus->column_fields['inspection_uid'] = $inspection_uid;
			}
			$log->debug("handleEvent InspectionsHandler vtiger.entity.beforesave treminated");
		}
                if($eventName == 'vtiger.entity.aftersave') {
			$log->debug("handleEvent InspectionsHandler vtiger.entity.aftersave entered");
			$id = $data->getId();
			$module = $data->getModuleName();
			$focus = $data->focus;
			$log->debug("handleEvent InspectionsHandler vtiger.entity.aftersave inspection_state = ".$focus->column_fields['inspection_state']);
			if( !$data->isNew() )
			{
				$log->debug("handleEvent InspectionsHandler vtiger.entity.aftersave this is an update");
				$sqlgetEvent = "SELECT    ".$table_prefix."_activity.activityid as main_activityid
								FROM      ".$table_prefix."_seactivityrel
								INNER JOIN ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=".$table_prefix."_seactivityrel.crmid and ".$table_prefix."_crmentity.deleted=0
								INNER JOIN ".$table_prefix."_activity on ".$table_prefix."_activity.activityid=".$table_prefix."_seactivityrel.activityid 
								JOIN ".$table_prefix."_crmentity act_entity on act_entity.crmid=".$table_prefix."_activity.activityid and act_entity.deleted=0
								AND ".$table_prefix."_activity.activitytype = 'Revisione (Auto-gen)' AND ".$table_prefix."_activity.eventstatus='Planned'
								WHERE ".$table_prefix."_seactivityrel.crmid = ".$focus->id;
				$result = $adb->query($sqlgetEvent); 
				while($row=$adb->fetchByAssoc($result)){
					$eventstatus = 'Planned';
					$date_start = getValidDBInsertDateValue($focus->column_fields['due_date']);// 2013-05-27
					$date_var = date('Y-m-d H:i:s');
					if( $focus->column_fields['inspection_state']=='Chiusa' )
					{
						$eventstatus = 'Held';
						$date_start = getValidDBInsertDateValue($focus->column_fields['inspection_date']);// 2013-05-27
					}
					$sqlUpdateEvent = "UPDATE ".$table_prefix."_activity
										SET date_start='".$date_start."' , eventstatus='".$eventstatus."', due_date = date_start 
										WHERE ".$table_prefix."_activity.activityid=".$row['main_activityid'];
					$adb->query($sqlUpdateEvent); 
					$sqlUpdateCrmEntity = "UPDATE ".$table_prefix."_crmentity
										SET modifiedtime=? 
										WHERE ".$table_prefix."_crmentity.crmid=?";
					$adb->pquery($sqlUpdateCrmEntity,array($adb->formatDate($date_var, true),$row['main_activityid']));
				}
                                // danzi.tn@20130528  end
			} elseif( $data->isNew()) {
				$log->debug("handleEvent InspectionsHandler vtiger.entity.aftersave this is an insert");
				$log->debug("handleEvent InspectionsHandler vtiger.entity.aftersave this is an insert focus->id = ". $focus->id );
                                $newEvent = CRMEntity::getInstance('Events');
                                vtlib_setup_modulevars('Events',$newEvent);
                                $newEvent->column_fields['subject'] = $focus->column_fields['inspection_name'];// . "-[" . $row['account_no'] . "]";
                                $newEvent->column_fields['smownerid'] = $focus->column_fields["assigned_user_id"];
                                $newEvent->column_fields['assigned_user_id'] = $focus->column_fields["assigned_user_id"];
                                $newEvent->column_fields['createdtime'] = $focus->column_fields["createdtime"];
                                $newEvent->column_fields['modifiedtime'] = $focus->column_fields["modifiedtime"];
                                $newEvent->column_fields['parent_id'] = $focus->id;
                                $newEvent->column_fields['date_start'] = $focus->column_fields['due_date'];// 2013-05-27
                                $newEvent->column_fields['time_start'] = '00:00';// 15:50
                                $newEvent->column_fields['due_date'] =  $newEvent->column_fields['date_start']; // 2013-05-27
                                $newEvent->column_fields['time_end'] = '00:00';// 15:55
                                $newEvent->column_fields['duration_hours'] = 23;// 2
                                $newEvent->column_fields['duration_minutes'] = 59;// 2
                                $newEvent->column_fields['activitytype'] = 'Revisione (Auto-gen)'; //$insp_activitytype;
                                $newEvent->column_fields['is_all_day_event'] = 1;
                                $newEvent->column_fields['eventstatus'] = 'Planned';// $insp_eventstatus 
                                $newEvent->column_fields['description'] = $focus->column_fields['description'];
                                $newEvent->save($module_name='Events',$longdesc=false);
			}
			$log->debug("handleEvent InspectionsHandler vtiger.entity.aftersave terminated");
		}
    }
}


?>
