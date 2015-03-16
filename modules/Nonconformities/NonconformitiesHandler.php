<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
class NonconformitiesHandler extends VTEventHandler {
	
    // danzi.tn@20141023 gestione custom valutazione - update before save per totali parziali su danno commerciale e dati commerciali e totale danno
	// danzi.tn@20150316 sovrascrivere il campo "Valore da recuperare" con il campo "Totale" della valorizzazione	
	function handleEvent($eventName, $data) {
		global $adb, $current_user,$log;
        $log->debug("handleEvent entered");
		if (!($data->focus instanceof Nonconformities)) {
            $log->debug("handleEvent not a Nonconformities");
			return;
		}
		
		if($eventName == 'vtiger.entity.beforesave') {
			// Entity is about to be saved, take required action
			$log->debug("handleEvent vtiger.entity.beforesave entered");
			$module = $data->getModuleName();
			$focus = $data->focus;
            // danno_comm
			$danno_comm_perd_ord = floatval($focus->column_fields['danno_comm_perd_ord']);
            $log->debug("handleEvent vtiger.entity.beforesave danno_comm_perd_ord = ".$danno_comm_perd_ord);
			$danno_comm_entr_conc = floatval($focus->column_fields['danno_comm_entr_conc']);
			$danno_comm_perd_mar = floatval($focus->column_fields['danno_comm_perd_mar']);
			$danno_comm_perd_cli = floatval($focus->column_fields['danno_comm_perd_cli']);
			$danno_comm_perd_fatt = floatval($focus->column_fields['danno_comm_perd_fatt']);
			$danno_comm_dann_imm = floatval($focus->column_fields['danno_comm_dann_imm']);
			$danno_comm_varie = floatval($focus->column_fields['danno_comm_varie']);
            $focus->column_fields['danno_comm'] = $danno_comm_perd_ord + $danno_comm_entr_conc + $danno_comm_perd_mar + $danno_comm_perd_cli + $danno_comm_perd_fatt + $danno_comm_dann_imm + $danno_comm_varie;
            $log->debug("handleEvent vtiger.entity.beforesave danno_comm = ".$focus->column_fields['danno_comm']);
            // dati_comm
			$dati_comm_fatt_dann = floatval($focus->column_fields['dati_comm_fatt_dann']);
			$dati_comm_note_acc = floatval($focus->column_fields['dati_comm_note_acc']);
			$dati_comm_fermo_can = floatval($focus->column_fields['dati_comm_fermo_can']);
			$dati_comm_omaggio = floatval($focus->column_fields['dati_comm_omaggio']);
            $focus->column_fields['dati_comm'] = $dati_comm_fatt_dann + $dati_comm_note_acc + $dati_comm_fermo_can + $dati_comm_omaggio;	
            $log->debug("handleEvent vtiger.entity.beforesave dati_comm = ".$focus->column_fields['dati_comm']);		
            // totale_valutazione
            $focus->column_fields['totale_valutazione'] = floatval($focus->column_fields['rilavorazione']) +  floatval($focus->column_fields['logistica']) + floatval($focus->column_fields['magazzino']) +  floatval($focus->column_fields['acquisto']) + floatval($focus->column_fields['gestione']) + floatval($focus->column_fields['danno_comm']) +  floatval($focus->column_fields['dati_comm']);
			// danzi.tn@20150316 sovrascrivere il campo "Valore da recuperare" con il campo "Totale" della valorizzazione	
			if(!empty($focus->column_fields['totale_valutazione']) && is_numeric($focus->column_fields['totale_valutazione'])  ) {
				$focus->column_fields['cf_1271'] = $focus->column_fields['totale_valutazione'] + 0;
			}
			// danzi.tn@20150316e
            $log->debug("handleEvent vtiger.entity.beforesave totale_valutazione = ".$focus->column_fields['totale_valutazione']);
			$log->debug("handleEvent vtiger.entity.beforesave treminated");
		}
        $log->debug("handleEvent terminated");
    }
}
?>
