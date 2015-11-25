<?php 
// danzi.tn@20151019 gestione Tipologia
if ($type == 'MassEditSave') {
    global $adb,$table_prefix, $log;
    $recordids = trim($values['massedit_recordids']);
    $log->debug("PresaveHelpDesk - MassEditSave for ".$recordids);
    if( !empty($recordids) ) {
        $recordids_array = explode(";", $recordids);
        $sql = "select ".$table_prefix."_troubletickets.ticketid,".$table_prefix."_troubletickets.ticket_no,".$table_prefix."_troubletickets.title  from ".$table_prefix."_troubletickets
                join ".$table_prefix."_ticketcf cf on cf.ticketid = ".$table_prefix."_troubletickets.ticketid 
                where cf.cf_1394 = 'Da compilare' and ".$table_prefix."_troubletickets.ticketid in (".implode(",", $recordids_array).")";
        $msg_array = array();
        $wsresult = $adb->query($sql);
        $log->debug("PresaveHelpDesk - MassEditSave sql ".$sql);
        while($row = $adb->fetchByAssoc($wsresult)) {
            $title = $row['title'];
            $ticket_no = $row['ticket_no'];
            $msg_array[]=$ticket_no." - " .$title;
            $status = false;
        }
        $focus = 'cf_1394';
        $message = 'Sistemare campo Tipologia prima di proseguire! ' . implode(",", $recordids_array) ;
        $log->debug("PresaveHelpDesk - MassEditSave message ".$message);
    }
} else {
	if (isset($values['cf_1394']) && $values['cf_1394'] == 'Da compilare') {
		$status = false;
		$message = 'Sistemare campo Tipologia prima di proseguire!';
		$focus = 'cf_1394';
	}
} 
?>