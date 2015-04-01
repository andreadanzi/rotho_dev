<?php

// include_once 'plugins/erpconnector/AccRating_populate/AccRatingClassALL.php';

class AccountsHandler extends VTEventHandler {

	function handleEvent($eventName, $data) {
		global $adb, $current_user, $log;
		
		$log->debug("handleEvent AccountsHandler entered for ". $eventName);
		
		if (!($data->focus instanceof Accounts)) {
			return;
		}
		
		if($eventName == 'vtiger.entity.beforesave') {
			// Entity is about to be saved, take required action
			$log->debug("handleEvent AccountsHandler vtiger.entity.beforesave entered");
			$log->debug("handleEvent AccountsHandler vtiger.entity.beforesave treminated");
		}

		if($eventName == 'vtiger.entity.aftersave') {
			$log->debug("handleEvent AccountsHandler vtiger.entity.aftersave entered");
			$id = $data->getId();
			$module = $data->getModuleName();
			$focus = $data->focus;
			$input_points = $focus->column_fields['input_points'];
			
			// global $codiceFatturazioneCorsoField;
			$log->debug("handleEvent AccountsHandler vtiger.entity.aftersave input_points = " . $input_points);		
			
			$log_active = false;

            $temp_table = "temp_acc_ratings";
            $ratingField = 'cf_927'; // dev=>cf_891 rotho_prod=>cf_927
            $codiceCorsoTargetField = 'cf_1006'; // dev=>cf_887 rotho_prod=>cf_1006
            $codiceCorsoCampagnaField = 'cf_742'; // dev=>cf_886 rotho_prod=>cf_742
            $dataCorsoCampagnaField = 'cf_745'; // dev=>cf_886 rotho_prod=>cf_742
            $codiceFatturazioneCorsoField = 'cf_759'; // dev=>cf_892 rotho_prod=>cf_759
            $tipoAffiliazioneField = 'cf_1178'; // dev=>cf_893 rotho_prod=>cf_1178

            $map_corsi=array();
            $map_corsi["RFCBC"] = "Corso base di carpenteria";
            $map_corsi["RFCAC"] = "Corso avanzato di carpenteria";
            $map_corsi["RFCACN"] = "Corso avanzato di progettazione delle connessioni per strutture di legno";
            $map_corsi["RFCAPC"] = "Corso avanzato di progettazione per edificidi legno: statica, sismica e cantiere";
            $map_corsi["RSCAP"] = "Corso di progettazione di sistemi anticaduta";
            $map_corsi["RSCA"] = "Corso per installatori qualificati di sistemi anticaduta";
            $map_corsi["RSCBDPI"] = "Corso per l'utilizzo di dispositivi di protezione individuale contro le cadute dall'alto e sistemi di salvataggio";
            $map_corsi["RSCB"] = "Corso per l'utilizzo di dispositivi di protezione individuale contro le cadute dall'alto e sistemi di salvataggio";
            $map_corsi["RHCB"] = "Corso base per applicatori";
            $map_corsi["RHCA"] = "Corso per progettisti";
            $map_corsi["RHCT"] = "Corso per tecnici di impresa e direttori lavori";
            $map_corsi["RBFCACM"] = "Curso avanzado de conexiones en madiera";
            $map_corsi["RHCI"] = "Corso di formazione teoricao/pratica Intego";
            $map_corsi["ND"] = "Download";
			/*
			$accRating = new AccRatingClassALL();
			$accRating->setVars($log_active,$ratingField, $codiceCorsoTargetField,$codiceCorsoCampagnaField, $codiceFatturazioneCorsoField,$tipoAffiliazioneField,$map_corsi );
			$accRating->setEntityId($id);
			$accRating->populateNow();		
			*/
			if($data->isNew())
			{
				$log->debug("handleEvent AccountsHandler vtiger.entity.aftersave this is an insert");
			} else {
				$log->debug("handleEvent AccountsHandler vtiger.entity.aftersave this is an update");
			}
			$log->debug("handleEvent AccountsHandler vtiger.entity.aftersave terminated");
		}
	}
}
?>
