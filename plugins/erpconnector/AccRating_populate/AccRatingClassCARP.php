<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
// Switch the working directory to base
// chdir(dirname(__FILE__) . '/../..');
// danzi.tn@20140224 - GESTIONE RC / CARP per Sudamerica - RICORDARSI DELETE MANUALE
// danzi.tn@20140307 eliminato il criterio sullo stato di fatturazione
// danzi.tn@20140603 - filtro su ultimi 21 mesi BETWEEN DATEADD( month, -24 ,GETDATE())  AND  GETDATE() )
// danzi.tn@20140630 - separazione tra corsi e downloads. Downloads adesso va a vedere la data sull'Evento di calendario _get_target_campaign_downloads_sql

include_once 'include/Zend/Json.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'include/utils/VtlibUtils.php';
include_once 'include/Webservices/Create.php';
include_once 'include/QueryGenerator/QueryGenerator.php';


class AccRatingClassCARP {
	
	var $entity_id = 0;
	var $_log_active = false;
	var $_ratingField = '';
	var $_codiceCorsoTargetField = '';
	var $_codiceCorsoCampagnaField = '';
	var $_dataCorsoCampagnaField = '';
	var $_codiceFatturazioneCorsoField = '';
	var $_codiceCategoriaField = '';
	var $_tipoAffiliazioneField = '';
	var $_map_corsi = Array();
	
	function setEntityId($id=0) {
		$this->entity_id = $id;
	}
	
	function setVars($p_log_active, $p_ratingField, $p_codiceCorsoTargetField,$p_codiceCorsoCampagnaField, $p_codiceFatturazioneCorsoField,$p_codiceCategoriaField,$p_tipoAffiliazioneField, $p_map_corsi , $p_dataCorsoCampagnaField)
	{
		$this->_log_active = $p_log_active;
		$this->_ratingField = $p_ratingField;
		$this->_codiceCorsoTargetField = $p_codiceCorsoTargetField;
		$this->_codiceCorsoCampagnaField = $p_codiceCorsoCampagnaField;
		$this->_dataCorsoCampagnaField = $p_dataCorsoCampagnaField;
		$this->_codiceFatturazioneCorsoField = $p_codiceFatturazioneCorsoField;
		$this->_codiceCategoriaField = $p_codiceCategoriaField;
		$this->_tipoAffiliazioneField = $p_tipoAffiliazioneField;
		$this->_map_corsi = $p_map_corsi;
	}
	
	function __construct()
	{
		global $log_active, $ratingField, $codiceCorsoTargetField, $codiceCorsoCampagnaField,$dataCorsoCampagnaField, $codiceCategoriaField, $tipoAffiliazioneField, $codiceFatturazioneCorsoField,  $map_corsi;
		$this->_log_active = $log_active;
		$this->_ratingField = $ratingField;
		$this->_codiceCorsoTargetField = $codiceCorsoTargetField;
		$this->_codiceCorsoCampagnaField = $codiceCorsoCampagnaField;
		$this->_dataCorsoCampagnaField = $dataCorsoCampagnaField;
		$this->_codiceFatturazioneCorsoField = $codiceFatturazioneCorsoField;
		$this->_codiceCategoriaField = $codiceCategoriaField;
		$this->_tipoAffiliazioneField = $tipoAffiliazioneField;
		$this->_map_corsi = $map_corsi;
	}
	
	function populateNow() {
		global $adb, $current_user;
		global $table_prefix;
		$account_rating = array();
		$account_rating_table = array();
		$import_result = array();
		$import_result['records_created']=0;
		$import_result['records_updated']=0;
		try {
			// Retrieve user information
			$user = CRMEntity::getInstance('Users');
			$user->id=$user->getActiveAdminId();
			$user->retrieve_entity_info($user->id, 'Users');
			if($this->_log_active) echo "codiceCorsoCampagnaField = ".$this->_codiceCorsoCampagnaField ." and log_active = ".$this->_log_active." \n";
			// CORSI E ALTRO
			$query = $this->_get_target_campaign_sql();
			if($this->_log_active) echo "_get_target_campaign_sql query= ".$query." \n";
			$result = $adb->query($query);
			while($row=$adb->fetchByAssoc($result))
			{
				// gestire prog_rating_date così com'è, cioè valore stringa
				$account_rating[$row['accountid']] += intval($row['prog_rating_value']);
				if($row['codice_fatturazione'] == 'ND') {
					$account_rating_table[$row['accountid']]['Altro'][$row['campaignname']][$row['prog_rating_date']] += intval($row['prog_rating_value']);
				} else {
					if( array_key_exists($row['codice_fatturazione'],$this->_map_corsi) ) $keycorsi = $this->_map_corsi[$row['codice_fatturazione']];
					else $keycorsi = $row['codice_fatturazione'];
					$account_rating_table[$row['accountid']]['Corsi'][$keycorsi][$row['prog_rating_date']] += intval($row['prog_rating_value']);
				}
				$import_result['records_updated']+=1;
			}		
			// DOWNLOADS danzi.tn@20140630 - separazione tra corsi e downloads _get_target_campaign_downloads_sql
			$query = $this->_get_target_campaign_downloads_sql();
			if($this->_log_active) echo "_get_target_campaign_downloads_sql query= ".$query." \n";
			$result = $adb->query($query);
			while($row=$adb->fetchByAssoc($result))
			{
				$account_rating[$row['accountid']] += intval($row['prog_rating_value']);
				$account_rating_table[$row['accountid']]['Download'][$row['campaignname']][$row['prog_rating_date']] += intval($row['prog_rating_value']);
				$import_result['records_updated']+=1;
			}					
			// CONSULENZE
			$query = $this->_get_consulenze_sql();
			if($this->_log_active) echo "_get_consulenze_sql query= ".$query." \n";
			$result = $adb->query($query);
			while($row=$adb->fetchByAssoc($result))
			{
				// if($this->_log_active) echo "accountid = ".$row['accountid']."\n";
				// gestire prog_rating_date come data e convertirlo in stringa per utente
				$account_rating[$row['accountid']] += intval($row['prog_rating_value']);
				$account_rating_table[$row['accountid']]['Consulenze'][$row['consulenza_title']][$row['prog_rating_date']] += intval($row['prog_rating_value']);
				$import_result['records_updated']+=1;
			}
			
			// AFFILIAZIONI 
			/* il rating deve essere fatto a codice, splittando "CASA CLIMA" |##| "ARCA Tecnico Corso Base" |##| "ARCA Progettista" |##| "ZEPHIR"
			-- ARCA Tecnico Corso Base => 2
			-- ARCA Progettista => 1
			-- ZEPHIR => 2
			-- CASA CLIMA => 2 */
			$query = $this->_get_affiliazione_sql();
			if($this->_log_active) echo "_get_affiliazione_sql query= ".$query." \n";
			$result = $adb->query($query);
			while($row=$adb->fetchByAssoc($result))
			{
				$delimiter = " |##| ";
				$pieces = explode($delimiter, $row['tipo_affiliazione']);
				// gestire prog_rating_date come data e convertirlo in stringa per utente
				foreach($pieces as $piece) {
					if( trim($piece) == "ARCA Tecnico Corso Base" ){ 
						$account_rating[$row['accountid']] += 1;
						$account_rating_table[$row['accountid']]['Affiliazione']['ARCA Tecnico Corso Base'][$row['prog_rating_date']] += 2;
					}
					if( trim($piece) == "ARCA Progettista" ) { 
						$account_rating[$row['accountid']] += 2;
						$account_rating_table[$row['accountid']]['Affiliazione']['ARCA Progettista'][$row['prog_rating_date']]  += 1;
					}
					if( trim($piece) == "ZEPHIR" ) { 
						$account_rating[$row['accountid']] += 2;
						$account_rating_table[$row['accountid']]['Affiliazione']['ZEPHIR'][$row['prog_rating_date']] += 2;
					}
					if( trim($piece) == "CASA CLIMA" ) { 
						$account_rating[$row['accountid']] += 2;
						$account_rating_table[$row['accountid']]['Affiliazione']['CASA CLIMA'][$row['prog_rating_date']] += 2;
					}
				}
				// if($this->_log_active) echo "accountid = ".$row['accountid']."\n";
				$import_result['records_updated']+=1;
			}
			// OPPORTUNITA
			$query = $this->_get_opportunita_sql();
			if($this->_log_active) echo "_get_opportunita_sql query= ".$query." \n";
			$result = $adb->query($query);
			while($row=$adb->fetchByAssoc($result))
			{
				// if($this->_log_active) echo "accountid = ".$row['accountid']."\n";
				// gestire prog_rating_date come data e convertirlo in stringa per utente
				$account_rating[$row['accountid']] += intval($row['prog_rating_value']);
				$account_rating_table[$row['accountid']]['Opportunita'][$row['prog_rating_title']][$row['prog_rating_date']] += intval($row['prog_rating_value']);
				$import_result['records_updated']+=1;
			}
			// OPPORTUNITA CHIUSE VINTE
			$query = $this->_get_opportunita_closed_won_sql();
			if($this->_log_active) echo "_get_opportunita_closed_won_sql query= ".$query." \n";
			$result = $adb->query($query);
			while($row=$adb->fetchByAssoc($result))
			{
				// if($this->_log_active) echo "accountid = ".$row['accountid']."\n";
				// gestire prog_rating_date come data e convertirlo in stringa per utente
				$account_rating[$row['accountid']] += intval($row['prog_rating_value']);
				$account_rating_table[$row['accountid']]['Opportunita'][$row['prog_rating_title']][$row['prog_rating_date']] += intval($row['prog_rating_value']);
				$import_result['records_updated']+=1;
			}
			// CONTATTI FIERE danzi.tn@20131010
			$query = $this->_get_fiere_sql();
			if($this->_log_active) echo "_get_fiere_sql query= ".$query." \n";
			$result = $adb->query($query);
			while($row=$adb->fetchByAssoc($result))
			{
				$account_rating[$row['accountid']] += intval($row['prog_rating_value']);
				$account_rating_table[$row['accountid']]['Trade Show'][$row['prog_rating_title']][$row['prog_rating_date']] += intval($row['prog_rating_value']);
				$import_result['records_updated']+=1;
			}
			// PUNTEGGIO MANUALE
			$query = $this->_get_input_points_sql();
			if($this->_log_active) echo "_get_input_points_sql query= ".$query." \n";
			$result = $adb->query($query);
			while($row=$adb->fetchByAssoc($result))
			{
				// if($this->_log_active) echo "accountid = ".$row['accountid']."\n";
				// gestire prog_rating_date come data e convertirlo in stringa per utente
				$account_rating[$row['accountid']] += intval($row['prog_rating_value']);
				$account_rating_table[$row['accountid']]['Input Points'][' '][$row['prog_rating_date']] += intval($row['prog_rating_value']);
				$import_result['records_updated']+=1;
			}
			foreach($account_rating as $key=>$val) {
				if($this->_log_active) echo "$key;$val\n";
			}
			$this->_check_temp_table();
			$this->_insert_temp_table($account_rating_table);
			$this->_update_account_table();
			$this->_update_accounts_focus_trimestre();
			return $import_result;
		} catch (Exception $e) {
			return $import_result;
		}
	}
	
	private function _check_temp_table() {
		global $adb;
		$create_sql = "IF NOT EXISTS (select * from sysobjects where name='temp_acc_ratings' and xtype='U')
							CREATE TABLE temp_acc_ratings (
								accountid INT NULL,
								categoria VARCHAR(50) NULL,
								gruppo VARCHAR(255) NULL,
								valore INT NULL,
								insdatetime DATETIME NULL,
								eventdatetime VARCHAR(255) NULL
							)";
		$adb->query($create_sql);
		$delete_sql= "delete from temp_acc_ratings " .( $this->entity_id > 0 ? " WHERE account_category = 'RC / CARP' AND accountid = ".$this->entity_id : " WHERE account_category = 'RC / CARP'" );
		$adb->query($delete_sql);
	}
	
	private function _insert_temp_table($account_rating_table) {
		global $adb;
		$sql = "INSERT INTO temp_acc_ratings ";
		$sql .= "(accountid,categoria,gruppo,valore,eventdatetime,insdatetime,account_category)";
		$sql .= " VALUES (?,?,?,?,?,GETDATE(),'RC / CARP')";
		foreach($account_rating_table as $key=>$val) {
			foreach($val as $type_key=>$type_val) {
				foreach($type_val as $type_val_key=>$type_val_date) {
					foreach($type_val_date as $type_val_date_key=>$type_val_val) {
						if($this->_log_active) echo "$key;$type_key;$type_val_key;$type_val_date_key;$type_val_val\n";
						$adb->pquery($sql,array($key,$type_key,$type_val_key,$type_val_val,$type_val_date_key));
					}
				}
			}
		}
	}
	
	private function _update_account_table() {
		global $adb, $table_prefix;
		// danzi.tn@20130920 - mettere a 0 tutti gli account che non sono più in temp_acc_ratings
		$sql = "UPDATE 
				".$table_prefix."_account
				SET ".$table_prefix."_account.points = 0
				from 
				".$table_prefix."_account
				join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_account.accountid and ".$table_prefix."_crmentity.deleted=0
				left join temp_acc_ratings on temp_acc_ratings.accountid = ".$table_prefix."_account.accountid
				where
				".$table_prefix."_account.points > 0 AND
				temp_acc_ratings.accountid IS NULL";
		$adb->query($sql);
		// danzi.tn@20130920 e
		$sql = "UPDATE VTEACC
				SET 
				VTEACC.points = VTEACCSUM.sum_valore
				from 
				".$table_prefix."_account as VTEACC
				INNER JOIN
				(
				SELECT
				".$table_prefix."_account.accountid as acc_id,
				sum(temp_acc_ratings.valore) as sum_valore
				FROM
				".$table_prefix."_account
				JOIN ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_account.accountid and ".$table_prefix."_crmentity.deleted=0
				JOIN temp_acc_ratings on temp_acc_ratings.accountid = ".$table_prefix."_account.accountid
				".( $this->entity_id > 0 ? " WHERE ".$table_prefix."_account.accountid = ".$this->entity_id : "" ). 
				" GROUP BY ".$table_prefix."_account.accountid
				)	VTEACCSUM	
				ON VTEACCSUM.acc_id = VTEACC.accountid";
		$adb->query($sql);
	}
	
	// danzi.tn@20131112 nuove modifiche su algoritmo: distinzione IT e resto del mondo (io la farei sulla base del codice corso RBCACM estero Curso Avanzado, come adesso)
	// danzi.tn@20140307 eliminato il criterio sullo stato di fatturazione
	private function _get_target_campaign_sql() {
		global $table_prefix; // danzi.tn@20140129 - aggiunti codici paese FR GB IE PL e RO
		$sql = "SELECT DISTINCT 
			".$table_prefix."_account.accountid, 
			".$table_prefix."_account.account_no,
			".$table_prefix."_account.accountname,
			".$table_prefix."_accountbillads.bill_country, 
			".$table_prefix."_targets.targetname,
			".$table_prefix."_targetscf.".$this->_codiceCorsoTargetField." as codice_corso_target,
			".$table_prefix."_account.rating,
			".$table_prefix."_accountscf.".$this->_ratingField." as rating_attuale,
			".$table_prefix."_campaign.campaignname,
			".$table_prefix."_campaignscf.".$this->_codiceCorsoCampagnaField." as codice_corso_campagna,
			".$table_prefix."_campaignscf.".$this->_dataCorsoCampagnaField." as prog_rating_date,
			".$table_prefix."_campaignscf.".$this->_codiceFatturazioneCorsoField." as codice_fatturazione, -- Per i download = 'ND'
			2 as prog_rating_value ,
			count(*) as targetsum
			FROM ".$table_prefix."_account 
			JOIN ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_account.accountid AND ".$table_prefix."_crmentity.deleted = 0
			JOIN ".$table_prefix."_accountscf on ".$table_prefix."_accountscf.accountid =  ".$table_prefix."_account.accountid AND ".$table_prefix."_accountscf.".$this->_codiceCategoriaField." = 'RC / CARP' 
			JOIN ".$table_prefix."_accountbillads on ".$table_prefix."_accountbillads.accountaddressid =  ".$table_prefix."_account.accountid 
			JOIN ".$table_prefix."_crmentityrel on ".$table_prefix."_crmentityrel.relcrmid = ".$table_prefix."_accountscf.accountid AND ".$table_prefix."_crmentityrel.module = 'Targets'
			JOIN ".$table_prefix."_targets on ".$table_prefix."_targets.targetsid = ".$table_prefix."_crmentityrel.crmid
			JOIN ".$table_prefix."_targetscf on ".$table_prefix."_targetscf.targetsid = ".$table_prefix."_targets.targetsid AND ".$table_prefix."_targetscf.".$this->_codiceCorsoTargetField." <>''  AND ".$table_prefix."_targetscf.".$this->_codiceCorsoTargetField." IS NOT NULL
			JOIN ".$table_prefix."_crmentityrel as campaigns_crmentityrel on campaigns_crmentityrel.crmid = ".$table_prefix."_targets.targetsid AND campaigns_crmentityrel.module = 'Targets' AND campaigns_crmentityrel.relmodule = 'Campaigns'
			JOIN ".$table_prefix."_campaign on ".$table_prefix."_campaign.campaignid = campaigns_crmentityrel.relcrmid AND ".$table_prefix."_campaign.campaigntype <> 'Download' AND ".$table_prefix."_campaign.campaigntype <> 'Form Fiere'
			JOIN ".$table_prefix."_campaignscf on ".$table_prefix."_campaignscf.campaignid = ".$table_prefix."_campaign.campaignid
			JOIN ".$table_prefix."_crmentity as campaign_crmentity on campaign_crmentity.crmid = ".$table_prefix."_campaign.campaignid AND campaign_crmentity.deleted=0
			WHERE (".$table_prefix."_account.rating = '' OR ".$table_prefix."_account.rating = 'Active' OR ".$table_prefix."_account.rating ='--None--' OR ".$table_prefix."_account.rating ='Acquired') 
			AND (".$table_prefix."_accountscf.".$this->_ratingField." IS NULL OR ".$table_prefix."_accountscf.".$this->_ratingField."='' OR ".$table_prefix."_accountscf.".$this->_ratingField."='1'  OR ".$table_prefix."_accountscf.".$this->_ratingField."='35' OR ".$table_prefix."_accountscf.".$this->_ratingField."='36'   OR ".$table_prefix."_accountscf.".$this->_ratingField."='Riattivato')
			AND campaign_crmentity.createdtime BETWEEN DATEADD( month, -24 ,GETDATE())  AND  GETDATE() 
			AND ".$table_prefix."_campaignscf.".$this->_codiceFatturazioneCorsoField." IN ('RFCBC','RFCAC','RFCACN','RFCAPC','RSCAP','RSCA','RSCBDPI','RHCB','RHCA','RHCT','RBFCACM','RHCI','ND') 
			" .( $this->entity_id > 0 ? " AND ".$table_prefix."_account.accountid = ".$this->entity_id : "" ).  "
			group by 
			".$table_prefix."_account.accountid, 
			".$table_prefix."_account.account_no,
			".$table_prefix."_account.accountname,
			".$table_prefix."_accountbillads.bill_country, 
			".$table_prefix."_targets.targetname,
			".$table_prefix."_targetscf.".$this->_codiceCorsoTargetField." ,
			".$table_prefix."_account.rating,
			".$table_prefix."_accountscf.".$this->_ratingField.",
			".$table_prefix."_campaign.campaignname,
			".$table_prefix."_campaignscf.".$this->_dataCorsoCampagnaField.",
			".$table_prefix."_campaignscf.".$this->_codiceCorsoCampagnaField.",
			".$table_prefix."_campaignscf.".$this->_codiceFatturazioneCorsoField.",
			CASE WHEN ".$table_prefix."_campaignscf.".$this->_codiceFatturazioneCorsoField." IN ('RFCACN','RFCAPC','RFCAPRING', 'RSCAP','RHCA','RHCT','RBCACM') THEN 2  ELSE 1 END
			order by ".$table_prefix."_account.accountid";
		return $sql;
	}
	// danzi.tn@20140630 per Downloads
	private function _get_target_campaign_downloads_sql() {
		global $table_prefix; // danzi.tn@20140129 - aggiunti codici paese FR GB IE PL e RO
		$sql = "SELECT DISTINCT 
			".$table_prefix."_account.accountid, 
			".$table_prefix."_account.account_no,
			".$table_prefix."_account.accountname,
			".$table_prefix."_accountbillads.bill_country, 
			".$table_prefix."_targets.targetname,
			".$table_prefix."_targetscf.".$this->_codiceCorsoTargetField." as codice_corso_target,
			".$table_prefix."_account.rating,
			".$table_prefix."_accountscf.".$this->_ratingField." as rating_attuale,
			".$table_prefix."_campaign.campaignname,
			".$table_prefix."_campaignscf.".$this->_codiceCorsoCampagnaField." as codice_corso_campagna,
			".$table_prefix."_activity.date_start as prog_rating_date,
			".$table_prefix."_campaignscf.".$this->_codiceFatturazioneCorsoField." as codice_fatturazione, -- Per i download = 'ND'
			2 as prog_rating_value ,
			count(*) as targetsum
			FROM ".$table_prefix."_account 
			JOIN ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_account.accountid AND ".$table_prefix."_crmentity.deleted = 0
			JOIN ".$table_prefix."_accountscf on ".$table_prefix."_accountscf.accountid =  ".$table_prefix."_account.accountid AND ".$table_prefix."_accountscf.".$this->_codiceCategoriaField." = 'RC / CARP' 
			JOIN ".$table_prefix."_accountbillads on ".$table_prefix."_accountbillads.accountaddressid =  ".$table_prefix."_account.accountid 
			JOIN ".$table_prefix."_crmentityrel on ".$table_prefix."_crmentityrel.relcrmid = ".$table_prefix."_accountscf.accountid AND ".$table_prefix."_crmentityrel.module = 'Targets'
			JOIN ".$table_prefix."_targets on ".$table_prefix."_targets.targetsid = ".$table_prefix."_crmentityrel.crmid
			JOIN ".$table_prefix."_targetscf on ".$table_prefix."_targetscf.targetsid = ".$table_prefix."_targets.targetsid AND ".$table_prefix."_targetscf.".$this->_codiceCorsoTargetField." <>''  AND ".$table_prefix."_targetscf.".$this->_codiceCorsoTargetField." IS NOT NULL
			JOIN ".$table_prefix."_crmentityrel as campaigns_crmentityrel on campaigns_crmentityrel.crmid = ".$table_prefix."_targets.targetsid AND campaigns_crmentityrel.module = 'Targets' AND campaigns_crmentityrel.relmodule = 'Campaigns'
			JOIN ".$table_prefix."_campaign on ".$table_prefix."_campaign.campaignid = campaigns_crmentityrel.relcrmid AND ".$table_prefix."_campaign.campaigntype = 'Download'
			JOIN ".$table_prefix."_campaignscf on ".$table_prefix."_campaignscf.campaignid = ".$table_prefix."_campaign.campaignid
			JOIN ".$table_prefix."_crmentity as campaign_crmentity on campaign_crmentity.crmid = ".$table_prefix."_campaign.campaignid AND campaign_crmentity.deleted=0
			JOIN ".$table_prefix."_seactivityrel on ".$table_prefix."_seactivityrel.crmid = ".$table_prefix."_account.accountid
			JOIN ".$table_prefix."_activity ON ".$table_prefix."_activity.activityid = ".$table_prefix."_seactivityrel.activityid AND ".$table_prefix."_activity.activitytype = 'Download - Web'
			JOIN ".$table_prefix."_crmentity actentity ON actentity.crmid = ".$table_prefix."_activity.activityid AND actentity.deleted = 0
			WHERE (".$table_prefix."_account.rating = '' OR ".$table_prefix."_account.rating = 'Active' OR ".$table_prefix."_account.rating ='--None--' OR ".$table_prefix."_account.rating ='Acquired') 
			AND (".$table_prefix."_accountscf.".$this->_ratingField." IS NULL OR ".$table_prefix."_accountscf.".$this->_ratingField."='' OR ".$table_prefix."_accountscf.".$this->_ratingField."='1'  OR ".$table_prefix."_accountscf.".$this->_ratingField."='35' OR ".$table_prefix."_accountscf.".$this->_ratingField."='36'   OR ".$table_prefix."_accountscf.".$this->_ratingField."='Riattivato')
			AND ".$table_prefix."_activity.date_start BETWEEN DATEADD( month, -24 ,GETDATE())  AND  GETDATE() 
			AND ".$table_prefix."_campaignscf.".$this->_codiceFatturazioneCorsoField." = 'ND'
			" .( $this->entity_id > 0 ? " AND ".$table_prefix."_account.accountid = ".$this->entity_id : "" ).  "
			group by 
			".$table_prefix."_account.accountid, 
			".$table_prefix."_account.account_no,
			".$table_prefix."_account.accountname,
			".$table_prefix."_accountbillads.bill_country, 
			".$table_prefix."_targets.targetname,
			".$table_prefix."_targetscf.".$this->_codiceCorsoTargetField." ,
			".$table_prefix."_account.rating,
			".$table_prefix."_accountscf.".$this->_ratingField.",
			".$table_prefix."_campaign.campaignname,
			".$table_prefix."_campaignscf.".$this->_codiceCorsoCampagnaField.",
			".$table_prefix."_activity.date_start,
			".$table_prefix."_campaignscf.".$this->_codiceFatturazioneCorsoField."	
			order by ".$table_prefix."_account.accountid";
		return $sql;
	}
	
	
	// danzi.tn@20131130 target invertiti
	private function _get_target_rev_campaign_sql() {
		global $table_prefix;
		$sql = "SELECT DISTINCT 
			".$table_prefix."_account.accountid, 
			".$table_prefix."_account.account_no,
			".$table_prefix."_account.accountname,
			".$table_prefix."_accountbillads.bill_country, 
			".$table_prefix."_targets.targetname,
			".$table_prefix."_targetscf.".$this->_codiceCorsoTargetField." as codice_corso_target,
			".$table_prefix."_account.rating,
			".$table_prefix."_accountscf.".$this->_ratingField." as rating_attuale,
			".$table_prefix."_campaign.campaignname,
			".$table_prefix."_campaignscf.".$this->_codiceCorsoCampagnaField." as codice_corso_campagna,
			".$table_prefix."_campaignscf.".$this->_dataCorsoCampagnaField." as prog_rating_date,
			".$table_prefix."_campaignscf.".$this->_codiceFatturazioneCorsoField." as codice_fatturazione, -- Per i download = 'ND'
			2 as prog_rating_value ,
			count(*) as targetsum
			FROM ".$table_prefix."_account 
			JOIN ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_account.accountid AND ".$table_prefix."_crmentity.deleted = 0
			JOIN ".$table_prefix."_accountscf on ".$table_prefix."_accountscf.accountid =  ".$table_prefix."_account.accountid AND ".$table_prefix."_accountscf.".$this->_codiceCategoriaField." = 'RC / CARP' 
			JOIN ".$table_prefix."_accountbillads on ".$table_prefix."_accountbillads.accountaddressid =  ".$table_prefix."_account.accountid 
			JOIN ".$table_prefix."_crmentityrel on ".$table_prefix."_crmentityrel.crmid = ".$table_prefix."_accountscf.accountid AND ".$table_prefix."_crmentityrel.relmodule = 'Targets'
			JOIN ".$table_prefix."_targets on ".$table_prefix."_targets.targetsid = ".$table_prefix."_crmentityrel.relcrmid
			JOIN ".$table_prefix."_targetscf on ".$table_prefix."_targetscf.targetsid = ".$table_prefix."_targets.targetsid AND ".$table_prefix."_targetscf.".$this->_codiceCorsoTargetField." <>''  AND ".$table_prefix."_targetscf.".$this->_codiceCorsoTargetField." IS NOT NULL
			JOIN ".$table_prefix."_crmentityrel as campaigns_crmentityrel on campaigns_crmentityrel.crmid = ".$table_prefix."_targets.targetsid AND campaigns_crmentityrel.module = 'Targets' AND campaigns_crmentityrel.relmodule = 'Campaigns'
			JOIN ".$table_prefix."_campaign on ".$table_prefix."_campaign.campaignid = campaigns_crmentityrel.relcrmid
			JOIN ".$table_prefix."_campaignscf on ".$table_prefix."_campaignscf.campaignid = ".$table_prefix."_campaign.campaignid
			JOIN ".$table_prefix."_crmentity as campaign_crmentity on campaign_crmentity.crmid = ".$table_prefix."_campaign.campaignid AND campaign_crmentity.deleted=0
			WHERE (".$table_prefix."_account.rating = '' OR ".$table_prefix."_account.rating = 'Active' OR ".$table_prefix."_account.rating ='--None--' OR ".$table_prefix."_account.rating ='Acquired') 
			AND (".$table_prefix."_accountscf.".$this->_ratingField." IS NULL OR ".$table_prefix."_accountscf.".$this->_ratingField."='' OR ".$table_prefix."_accountscf.".$this->_ratingField."='1'  OR ".$table_prefix."_accountscf.".$this->_ratingField."='35' OR ".$table_prefix."_accountscf.".$this->_ratingField."='36'   OR ".$table_prefix."_accountscf.".$this->_ratingField."='Riattivato')
			AND campaign_crmentity.createdtime BETWEEN DATEADD( month, -24 ,GETDATE())  AND  GETDATE() 
			AND ".$table_prefix."_campaignscf.".$this->_codiceFatturazioneCorsoField." IN ('RFCBC','RFCAC','RFCACN','RFCAPC','RSCAP','RSCA','RSCBDPI','RHCB','RHCA','RHCT','RBFCACM','RHCI','ND') 
			" .( $this->entity_id > 0 ? " AND ".$table_prefix."_account.accountid = ".$this->entity_id : "" ).  "
			group by 
			".$table_prefix."_account.accountid, 
			".$table_prefix."_account.account_no,
			".$table_prefix."_account.accountname,
			".$table_prefix."_accountbillads.bill_country, 
			".$table_prefix."_targets.targetname,
			".$table_prefix."_targetscf.".$this->_codiceCorsoTargetField." ,
			".$table_prefix."_account.rating,
			".$table_prefix."_accountscf.".$this->_ratingField.",
			".$table_prefix."_campaign.campaignname,
			".$table_prefix."_campaignscf.".$this->_dataCorsoCampagnaField.",
			".$table_prefix."_campaignscf.".$this->_codiceCorsoCampagnaField.",
			".$table_prefix."_campaignscf.".$this->_codiceFatturazioneCorsoField.",
			CASE WHEN ".$table_prefix."_campaignscf.".$this->_codiceFatturazioneCorsoField." IN ('RFCACN','RFCAPRING', 'RFCAPC','RSCAP','RHCA','RHCT','RBCACM') THEN 2  ELSE 1 END
			order by ".$table_prefix."_account.accountid";
		return $sql;
	}
	
	
	private function _get_consulenze_sql() {
		global $table_prefix;
		$sql = "SELECT 
				".$table_prefix."_account.accountid, 
				".$table_prefix."_account.account_no,
				".$table_prefix."_account.accountname,
				".$table_prefix."_accountbillads.bill_country, 
				".$table_prefix."_consulenza.consulenzaname,
				".$table_prefix."_consulenzaname.consulenzaname as consulenza_title,
				".$table_prefix."_consulenza.product_cat, 
				".$table_prefix."_account.rating,
				".$table_prefix."_accountscf.".$this->_ratingField." as rating_attuale,
				consulenza_crmentity.modifiedtime as prog_rating_date,
				2 as prog_rating_value
				FROM ".$table_prefix."_account 
				JOIN ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_account.accountid AND ".$table_prefix."_crmentity.deleted = 0
				JOIN ".$table_prefix."_accountscf on ".$table_prefix."_accountscf.accountid =  ".$table_prefix."_account.accountid AND ".$table_prefix."_accountscf.".$this->_codiceCategoriaField." = 'RC / CARP' 
				JOIN ".$table_prefix."_accountbillads on ".$table_prefix."_accountbillads.accountaddressid =  ".$table_prefix."_account.accountid 
				JOIN ".$table_prefix."_consulenza on ".$table_prefix."_consulenza.parent = ".$table_prefix."_account.accountid
				JOIN ".$table_prefix."_crmentity as consulenza_crmentity on consulenza_crmentity.crmid = ".$table_prefix."_consulenza.consulenzaid AND consulenza_crmentity.deleted = 0 
				LEFT JOIN ".$table_prefix."_consulenzaname on CONVERT(VARCHAR, ".$table_prefix."_consulenzaname.consulenzanameid ) = ".$table_prefix."_consulenza.consulenzaname
				WHERE (".$table_prefix."_account.rating = '' OR ".$table_prefix."_account.rating = 'Active' OR ".$table_prefix."_account.rating ='--None--' OR ".$table_prefix."_account.rating ='Acquired') 
				AND consulenza_crmentity.modifiedtime BETWEEN DATEADD( month, -24 ,GETDATE())  AND  GETDATE() 
				AND (".$table_prefix."_accountscf.".$this->_ratingField." IS NULL OR ".$table_prefix."_accountscf.".$this->_ratingField."='' OR ".$table_prefix."_accountscf.".$this->_ratingField."='1'  OR ".$table_prefix."_accountscf.".$this->_ratingField."='35' OR ".$table_prefix."_accountscf.".$this->_ratingField."='36'   OR ".$table_prefix."_accountscf.".$this->_ratingField."='Riattivato') " .( $this->entity_id > 0 ? " AND ".$table_prefix."_account.accountid = ".$this->entity_id : "" );
		return $sql;
	}
	
	private function _get_affiliazione_sql() {
		global $table_prefix;
		$sql = "SELECT 
				".$table_prefix."_account.accountid, 
				".$table_prefix."_account.account_no,
				".$table_prefix."_account.accountname,
				".$table_prefix."_account.rating,
				".$table_prefix."_accountbillads.bill_country, 
				".$table_prefix."_accountscf.".$this->_ratingField." as rating_attuale,
				".$table_prefix."_accountscf.".$this->_tipoAffiliazioneField." as tipo_affiliazione,
				".$table_prefix."_crmentity.modifiedtime as prog_rating_date 
				FROM ".$table_prefix."_account 
				JOIN ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_account.accountid AND ".$table_prefix."_crmentity.deleted = 0
				JOIN ".$table_prefix."_accountscf on ".$table_prefix."_accountscf.accountid =  ".$table_prefix."_account.accountid AND ".$table_prefix."_accountscf.".$this->_codiceCategoriaField." = 'RC / CARP' 
				JOIN ".$table_prefix."_accountbillads on ".$table_prefix."_accountbillads.accountaddressid =  ".$table_prefix."_account.accountid 
				WHERE (".$table_prefix."_account.rating = '' OR ".$table_prefix."_account.rating = 'Active' OR ".$table_prefix."_account.rating ='--None--' OR ".$table_prefix."_account.rating ='Acquired') 
				AND  ".$table_prefix."_crmentity.modifiedtime BETWEEN DATEADD( month, -24 ,GETDATE())  AND  GETDATE() 
				AND (".$table_prefix."_accountscf.".$this->_ratingField." IS NULL 
						OR ".$table_prefix."_accountscf.".$this->_ratingField."='' 
						OR ".$table_prefix."_accountscf.".$this->_ratingField."='1'  
						OR ".$table_prefix."_accountscf.".$this->_ratingField."='35' 
						OR ".$table_prefix."_accountscf.".$this->_ratingField."='36'   
						OR ".$table_prefix."_accountscf.".$this->_ratingField."='Riattivato')
				AND ".$table_prefix."_accountscf.".$this->_tipoAffiliazioneField." IS NOT NULL
				AND ".$table_prefix."_accountscf.".$this->_tipoAffiliazioneField." <>''
				" .( $this->entity_id > 0 ? " AND ".$table_prefix."_account.accountid = ".$this->entity_id : "" );
		return $sql;
	}
	// danzi.tn@20131010 nuovo metodo per contare i contatti fiera
	private function _get_fiere_sql() {
		global $table_prefix;
		$sql = "SELECT 
				".$table_prefix."_account.accountid, 
				".$table_prefix."_account.account_no,
				".$table_prefix."_account.accountname,
				".$table_prefix."_account.rating,
				".$table_prefix."_accountscf.".$this->_ratingField." as rating_attuale,
				".$table_prefix."_activity.date_start as prog_rating_date ,
				".$table_prefix."_activity.subject as prog_rating_title ,
				2 as prog_rating_value
				FROM ".$table_prefix."_account 
				JOIN ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_account.accountid AND ".$table_prefix."_crmentity.deleted = 0
				JOIN ".$table_prefix."_accountscf on ".$table_prefix."_accountscf.accountid =  ".$table_prefix."_account.accountid AND ".$table_prefix."_accountscf.".$this->_codiceCategoriaField." = 'RC / CARP' 
				JOIN ".$table_prefix."_accountbillads on ".$table_prefix."_accountbillads.accountaddressid =  ".$table_prefix."_account.accountid 
				JOIN ".$table_prefix."_seactivityrel ON ".$table_prefix."_seactivityrel.crmid = ".$table_prefix."_account.accountid
				JOIN ".$table_prefix."_activity ON ".$table_prefix."_activity.activityid = ".$table_prefix."_seactivityrel.activityid AND ".$table_prefix."_activity.activitytype ='Contatto - Fiera'
				JOIN ".$table_prefix."_crmentity as activity_crmentity ON activity_crmentity.crmid = ".$table_prefix."_activity.activityid  AND activity_crmentity.deleted = 0 
				WHERE (".$table_prefix."_account.rating = '' OR ".$table_prefix."_account.rating = 'Active' OR ".$table_prefix."_account.rating ='--None--' OR ".$table_prefix."_account.rating ='Acquired') 
				AND ".$table_prefix."_activity.date_start BETWEEN DATEADD( month, -24 ,GETDATE())  AND  GETDATE() 
				AND (".$table_prefix."_accountscf.".$this->_ratingField." IS NULL 
						OR ".$table_prefix."_accountscf.".$this->_ratingField."='' 
						OR ".$table_prefix."_accountscf.".$this->_ratingField."='1'  
						OR ".$table_prefix."_accountscf.".$this->_ratingField."='35' 
						OR ".$table_prefix."_accountscf.".$this->_ratingField."='36'   
						OR ".$table_prefix."_accountscf.".$this->_ratingField."='Riattivato')
				" .( $this->entity_id > 0 ? " AND ".$table_prefix."_account.accountid = ".$this->entity_id : "" );
		return $sql;
	}
			
	private function _get_opportunita_sql() {
		global $table_prefix;
		$sql = "SELECT 
				".$table_prefix."_account.accountid, 
				".$table_prefix."_account.account_no,
				".$table_prefix."_account.accountname,
				".$table_prefix."_accountbillads.bill_country, 
				".$table_prefix."_account.rating,
				".$table_prefix."_accountscf.".$this->_ratingField." as rating_attuale,
				".$table_prefix."_potential.potentialname,
				".$table_prefix."_potential.potentialid,
				".$table_prefix."_potential.potential_no,
				".$table_prefix."_potential.amount,
				CASE 
					WHEN ".$table_prefix."_potential.amount < 10000 THEN 2  
					WHEN ".$table_prefix."_potential.amount >= 10000 AND ".$table_prefix."_potential.amount < 20000 THEN 3
					WHEN ".$table_prefix."_potential.amount >= 20000 AND ".$table_prefix."_potential.amount < 50000 THEN 4
					WHEN ".$table_prefix."_potential.amount >= 50000 AND ".$table_prefix."_potential.amount < 100000 THEN 5
					WHEN ".$table_prefix."_potential.amount >= 100000 THEN 6
				END as prog_rating_value,
				CASE 
					WHEN ".$table_prefix."_potential.amount < 10000 THEN ".$table_prefix."_potential.potentialname + ' (< 10K)'  
					WHEN ".$table_prefix."_potential.amount >= 10000 AND ".$table_prefix."_potential.amount < 20000 THEN ".$table_prefix."_potential.potentialname + ' (> 10K)'  
					WHEN ".$table_prefix."_potential.amount >= 20000 AND ".$table_prefix."_potential.amount < 50000 THEN ".$table_prefix."_potential.potentialname + ' (> 20K)'  
					WHEN ".$table_prefix."_potential.amount >= 50000 AND ".$table_prefix."_potential.amount < 100000 THEN ".$table_prefix."_potential.potentialname + ' (> 50K)'  
					WHEN ".$table_prefix."_potential.amount >= 100000 THEN ".$table_prefix."_potential.potentialname + ' (> 100K)'  
				END as prog_rating_title,
				potential_crmentity.modifiedtime as prog_rating_date 
				FROM ".$table_prefix."_account 
				JOIN ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_account.accountid AND ".$table_prefix."_crmentity.deleted = 0
				JOIN ".$table_prefix."_accountscf on ".$table_prefix."_accountscf.accountid =  ".$table_prefix."_account.accountid AND ".$table_prefix."_accountscf.".$this->_codiceCategoriaField." = 'RC / CARP' 
				JOIN ".$table_prefix."_accountbillads on ".$table_prefix."_accountbillads.accountaddressid =  ".$table_prefix."_account.accountid 
				JOIN ".$table_prefix."_potential on ".$table_prefix."_potential.related_to = ".$table_prefix."_account.accountid 
				JOIN ".$table_prefix."_crmentity as potential_crmentity on potential_crmentity.crmid = ".$table_prefix."_potential.potentialid AND potential_crmentity.deleted = 0
				WHERE (".$table_prefix."_account.rating = '' OR ".$table_prefix."_account.rating = 'Active' OR ".$table_prefix."_account.rating ='--None--' OR ".$table_prefix."_account.rating ='Acquired') 
				AND ".$table_prefix."_potential.sales_stage not in ( 'Closed Won' , 'Closed Lost') 
				AND potential_crmentity.modifiedtime BETWEEN DATEADD( month, -24 ,GETDATE())  AND  GETDATE() 
				AND (".$table_prefix."_accountscf.".$this->_ratingField." IS NULL 
						OR ".$table_prefix."_accountscf.".$this->_ratingField."='' 
						OR ".$table_prefix."_accountscf.".$this->_ratingField."='1'  
						OR ".$table_prefix."_accountscf.".$this->_ratingField."='35' 
						OR ".$table_prefix."_accountscf.".$this->_ratingField."='36'   
						OR ".$table_prefix."_accountscf.".$this->_ratingField."='Riattivato')
				" .( $this->entity_id > 0 ? " AND ".$table_prefix."_account.accountid = ".$this->entity_id : "" );
		return $sql;
	}
	
	private function _get_opportunita_closed_won_sql() {
		global $table_prefix;
		$sql = "SELECT 
				".$table_prefix."_account.accountid, 
				".$table_prefix."_account.account_no,
				".$table_prefix."_account.accountname,
				".$table_prefix."_accountbillads.bill_country, 
				".$table_prefix."_account.rating,
				".$table_prefix."_accountscf.".$this->_ratingField." as rating_attuale,
				".$table_prefix."_potential.potentialname + ' (Chiusa Vinta) '  as prog_rating_title,
				".$table_prefix."_potential.potentialid,
				".$table_prefix."_potential.potential_no,
				".$table_prefix."_potential.amount,
				1 as prog_rating_value ,
				potential_crmentity.createdtime as prog_rating_date 
				FROM ".$table_prefix."_account 
				JOIN ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_account.accountid AND ".$table_prefix."_crmentity.deleted = 0
				JOIN ".$table_prefix."_accountscf on ".$table_prefix."_accountscf.accountid =  ".$table_prefix."_account.accountid AND ".$table_prefix."_accountscf.".$this->_codiceCategoriaField." = 'RC / CARP' 
				JOIN ".$table_prefix."_accountbillads on ".$table_prefix."_accountbillads.accountaddressid =  ".$table_prefix."_account.accountid 
				JOIN ".$table_prefix."_potential on ".$table_prefix."_potential.related_to = ".$table_prefix."_account.accountid 
				JOIN ".$table_prefix."_crmentity as potential_crmentity on potential_crmentity.crmid = ".$table_prefix."_potential.potentialid AND potential_crmentity.deleted = 0
				WHERE (".$table_prefix."_account.rating = '' OR ".$table_prefix."_account.rating = 'Active' OR ".$table_prefix."_account.rating ='--None--' OR ".$table_prefix."_account.rating ='Acquired') 
				AND ".$table_prefix."_potential.sales_stage = 'Closed Won'  
				AND potential_crmentity.createdtime  BETWEEN DATEADD( month, -24 ,GETDATE())  AND  GETDATE() 
				AND (".$table_prefix."_accountscf.".$this->_ratingField." IS NULL 
						OR ".$table_prefix."_accountscf.".$this->_ratingField."='' 
						OR ".$table_prefix."_accountscf.".$this->_ratingField."='1'  
						OR ".$table_prefix."_accountscf.".$this->_ratingField."='35' 
						OR ".$table_prefix."_accountscf.".$this->_ratingField."='36'   
						OR ".$table_prefix."_accountscf.".$this->_ratingField."='Riattivato')
				" .( $this->entity_id > 0 ? " AND ".$table_prefix."_account.accountid = ".$this->entity_id : "" );
		return $sql;
	}

	private function _get_input_points_sql() {
		global $table_prefix;
		$sql = "SELECT 
				".$table_prefix."_account.accountid, 
				".$table_prefix."_account.account_no,
				".$table_prefix."_account.accountname,
				".$table_prefix."_accountbillads.bill_country, 
				".$table_prefix."_account.rating,
				".$table_prefix."_accountscf.".$this->_ratingField." as rating_attuale,
				".$table_prefix."_account.input_points as prog_rating_value ,
				".$table_prefix."_crmentity.modifiedtime as prog_rating_date 
				FROM ".$table_prefix."_account 
				JOIN ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_account.accountid AND ".$table_prefix."_crmentity.deleted = 0
				JOIN ".$table_prefix."_accountscf on ".$table_prefix."_accountscf.accountid =  ".$table_prefix."_account.accountid AND ".$table_prefix."_accountscf.".$this->_codiceCategoriaField." = 'RC / CARP' 
				JOIN ".$table_prefix."_accountbillads on ".$table_prefix."_accountbillads.accountaddressid =  ".$table_prefix."_account.accountid 
				WHERE (".$table_prefix."_account.rating = '' OR ".$table_prefix."_account.rating = 'Active' OR ".$table_prefix."_account.rating ='--None--' OR ".$table_prefix."_account.rating ='Acquired') 
				AND (".$table_prefix."_accountscf.".$this->_ratingField." IS NULL 
						OR ".$table_prefix."_accountscf.".$this->_ratingField."='' 
						OR ".$table_prefix."_accountscf.".$this->_ratingField."='1'  
						OR ".$table_prefix."_accountscf.".$this->_ratingField."='35' 
						OR ".$table_prefix."_accountscf.".$this->_ratingField."='36'   
						OR ".$table_prefix."_accountscf.".$this->_ratingField."='Riattivato')
				AND ".$table_prefix."_account.input_points IS NOT NULL
				AND ".$table_prefix."_account.input_points <> 0 
				" .( $this->entity_id > 0 ? " AND ".$table_prefix."_account.accountid = ".$this->entity_id : "" );
		return $sql;
	}
	
	private function _update_accounts_focus_trimestre($crmid=0) {
		global $table_prefix, $adb;
		// danzi.tn@20131209 prima setto tutti i Focus trimestre a 0, manca la gestione di crmid
		$sql = "UPDATE ".$table_prefix."_accountscf
				SET ".$table_prefix."_accountscf.cf_1224 = 0
				FROM
				".$table_prefix."_accountscf
				JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_accountscf.accountid and ".$table_prefix."_crmentity.deleted = 0";
		$adb->query($sql);		
		// danzi.tn@20131209  poi, per quelli che hanno voci in calendario d 90 giorni a questa parte setto  i Focus trimestre a 1, manca la gestione di crmid
		$sql="UPDATE
				".$table_prefix."_accountscf
				SET ".$table_prefix."_accountscf.cf_1224 = 1
				FROM
				".$table_prefix."_accountscf
				JOIN temp_acc_ratings on temp_acc_ratings.accountid = ".$table_prefix."_accountscf.accountid				
				JOIN ".$table_prefix."_seactivityrel ON ".$table_prefix."_seactivityrel.crmid = temp_acc_ratings.accountid
				JOIN ".$table_prefix."_activity ON ".$table_prefix."_activity.activityid = ".$table_prefix."_seactivityrel.activityid 
				AND ".$table_prefix."_activity.activitytype in ( 'Contatto - Fiera', 'Registrazione - Safe', 'Download - Web','Iscrizione Corso - Web','Consulenza - Web')
				JOIN ".$table_prefix."_crmentity as activity_crmentity ON activity_crmentity.crmid = ".$table_prefix."_activity.activityid  AND activity_crmentity.deleted = 0 
				WHERE 
				".$table_prefix."_activity.date_start  BETWEEN DATEADD(day,-90, GETDATE()) AND GETDATE()";
		$adb->query($sql);	
	}

}

?>
