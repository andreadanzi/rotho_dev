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
// chdir(dirname(__FILE__) . '/../..');

include_once 'include/Zend/Json.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'include/utils/VtlibUtils.php';
include_once 'include/Webservices/Create.php';
include_once 'include/QueryGenerator/QueryGenerator.php';

class AccRatingClass {
	
	function populateNow() {
		global $temp_table;
		global $adb, $current_user, $map_corsi;
		global $log_active, $table_prefix, $ratingField, $codiceCorsoTargetField,$codiceCorsoCampagnaField, $codiceFatturazioneCorsoField,$codiceCategoriaField,$tipoAffiliazioneField;
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
			if($log_active) echo "codiceCorsoCampagnaField = $codiceCorsoCampagnaField and log_active = $log_active \n";
			// $query = $this->_get_target_sql();
			// if($log_active) echo "_get_target_sql query= ".$query." \n";
			// CORSI E DOWNLOADS
			$query = $this->_get_target_campaign_sql();
			if($log_active) echo "_get_target_campaign_sql query= ".$query." \n";
			$result = $adb->query($query);
			while($row=$adb->fetchByAssoc($result))
			{
				// if($log_active) echo "accountid = ".$row['accountid']."\n";
				$account_rating[$row['accountid']] += intval($row['prog_rating_value']);
				if($row['codice_fatturazione'] == 'ND') {
					$account_rating_table[$row['accountid']]['Download'][$row['campaignname']] += intval($row['prog_rating_value']);
				} else {
					if( array_key_exists($row['codice_fatturazione'],$map_corsi) ) $keycorsi = $map_corsi[$row['codice_fatturazione']];
					else $keycorsi = $row['codice_fatturazione'];
					$account_rating_table[$row['accountid']]['Corsi'][$keycorsi] += intval($row['prog_rating_value']);
				}
				$import_result['records_updated']+=1;
			}			
			// CONSULENZE
			$query = $this->_get_consulenze_sql();
			if($log_active) echo "_get_consulenze_sql query= ".$query." \n";
			$result = $adb->query($query);
			while($row=$adb->fetchByAssoc($result))
			{
				// if($log_active) echo "accountid = ".$row['accountid']."\n";
				$account_rating[$row['accountid']] += intval($row['prog_rating_value']);
				$account_rating_table[$row['accountid']]['Consulenze'][$row['consulenza_title']] += intval($row['prog_rating_value']);
				$import_result['records_updated']+=1;
			}
			// AFFILIAZIONI 
			/* il rating deve essere fatto a codice, splittando "CASA CLIMA" |##| "ARCA Tecnico Corso Base" |##| "ARCA Progettista" |##| "ZEPHIR"
			-- ARCA Tecnico Corso Base => 2
			-- ARCA Progettista => 1
			-- ZEPHIR => 2
			-- CASA CLIMA => 2 */
			$query = $this->_get_affiliazione_sql();
			if($log_active) echo "_get_affiliazione_sql query= ".$query." \n";
			$result = $adb->query($query);
			while($row=$adb->fetchByAssoc($result))
			{
				$delimiter = " |##| ";
				$pieces = explode($delimiter, $row['tipo_affiliazione']);
				foreach($pieces as $piece) {
					if( trim($piece) == "ARCA Tecnico Corso Base" ){ 
						$account_rating[$row['accountid']] += 2;
						$account_rating_table[$row['accountid']]['Affiliazione']['ARCA Tecnico Corso Base'] += 2;
					}
					if( trim($piece) == "ARCA Progettista" ) { 
						$account_rating[$row['accountid']] += 1;
						$account_rating_table[$row['accountid']]['Affiliazione']['ARCA Progettista']  += 1;
					}
					if( trim($piece) == "ZEPHIR" ) { 
						$account_rating[$row['accountid']] += 2;
						$account_rating_table[$row['accountid']]['Affiliazione']['ZEPHIR'] += 2;
					}
					if( trim($piece) == "CASA CLIMA" ) { 
						$account_rating[$row['accountid']] += 2;
						$account_rating_table[$row['accountid']]['Affiliazione']['CASA CLIMA'] += 2;
					}
				}
				// if($log_active) echo "accountid = ".$row['accountid']."\n";
				$import_result['records_updated']+=1;
			}
			// OPPORTUNITA
			$query = $this->_get_opportunita_sql();
			if($log_active) echo "_get_opportunita_sql query= ".$query." \n";
			$result = $adb->query($query);
			while($row=$adb->fetchByAssoc($result))
			{
				// if($log_active) echo "accountid = ".$row['accountid']."\n";
				$account_rating[$row['accountid']] += intval($row['prog_rating_value']);
				$account_rating_table[$row['accountid']]['Opportunita'][$row['prog_rating_title']] += intval($row['prog_rating_value']);
				$import_result['records_updated']+=1;
			}
			foreach($account_rating as $key=>$val) {
				if($log_active) echo "$key;$val\n";
			}
			$this->_check_temp_table();
			$this->_insert_temp_table($account_rating_table);
			$this->_update_account_table();
			return $import_result;
		} catch (Exception $e) {
			return $import_result;
		}
	}
	
	private function _check_temp_table() {
		global $adb, $log_active;
		$create_sql = "IF NOT EXISTS (select * from sysobjects where name='temp_acc_ratings' and xtype='U')
							CREATE TABLE temp_acc_ratings (
								accountid INT NULL,
								categoria VARCHAR(50) NULL,
								gruppo VARCHAR(50) NULL,
								valore INT NULL,
								insdatetime DATETIME NULL
							)";
		$adb->query($create_sql);
		$delete_sql= "delete from temp_acc_ratings";
		$adb->query($delete_sql);
	}
	
	private function _insert_temp_table($account_rating_table) {
		global $adb, $log_active;
		$sql = "INSERT INTO temp_acc_ratings ";
		$sql .= "(accountid,categoria,gruppo,valore,insdatetime)";
		$sql .= " VALUES (?,?,?,?,GETDATE())";
		foreach($account_rating_table as $key=>$val) {
			foreach($val as $type_key=>$type_val) {
				foreach($type_val as $type_val_key=>$type_val_val) {
					if($log_active) echo "$key;$type_key;$type_val_key;$type_val_val\n";
					$adb->pquery($sql,array($key,$type_key,$type_val_key,$type_val_val));
				}
			}
		}
	}
	
	private function _update_account_table() {
		global $adb;
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
				GROUP BY ".$table_prefix."_account.accountid
				)	VTEACCSUM	
				ON VTEACCSUM.acc_id = VTEACC.accountid";
		$adb->query($sql);
	}
	
	private function _get_target_sql() {
		global $table_prefix, $ratingField, $codiceCorsoTargetField,$codiceCorsoCampagnaField, $codiceFatturazioneCorsoField,$codiceCategoriaField,$tipoAffiliazioneField;
		$query = "SELECT 
			".$table_prefix."_account.accountid, 
			".$table_prefix."_account.account_no,
			".$table_prefix."_account.accountname,
			".$table_prefix."_targets.targetname,
			".$table_prefix."_targetscf.".$codiceCorsoTargetField." as codice_corso_target,
			".$table_prefix."_account.rating,
			".$table_prefix."_accountscf.".$ratingField." as rating_attuale,
			count(*) as targetsum
			FROM ".$table_prefix."_account 
			JOIN ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_account.accountid AND ".$table_prefix."_crmentity.deleted = 0
			JOIN ".$table_prefix."_accountscf on ".$table_prefix."_accountscf.accountid =  ".$table_prefix."_account.accountid AND ".$table_prefix."_accountscf.".$codiceCategoriaField." = 'RP / PROG' 
			JOIN ".$table_prefix."_accountbillads on ".$table_prefix."_accountbillads.accountaddressid =  ".$table_prefix."_account.accountid AND ".$table_prefix."_accountbillads.bill_country like 'IT%'
			JOIN ".$table_prefix."_crmentityrel on ".$table_prefix."_crmentityrel.crmid = ".$table_prefix."_accountscf.accountid AND ".$table_prefix."_crmentityrel.relmodule = 'Targets'
			JOIN ".$table_prefix."_targets on ".$table_prefix."_targets.targetsid = ".$table_prefix."_crmentityrel.relcrmid
			JOIN ".$table_prefix."_targetscf on ".$table_prefix."_targetscf.targetsid = ".$table_prefix."_targets.targetsid AND ".$table_prefix."_targetscf.".$codiceCorsoTargetField." <>''  AND ".$table_prefix."_targetscf.".$codiceCorsoTargetField." IS NOT NULL
			WHERE (".$table_prefix."_account.rating = '' OR ".$table_prefix."_account.rating = 'Active' OR ".$table_prefix."_account.rating ='--None--' OR ".$table_prefix."_account.rating ='Acquired') 
			AND (".$table_prefix."_accountscf.".$ratingField." IS NULL OR ".$table_prefix."_accountscf.".$ratingField."='' OR ".$table_prefix."_accountscf.".$ratingField."='1'  OR ".$table_prefix."_accountscf.".$ratingField."='35' OR ".$table_prefix."_accountscf.".$ratingField."='36'   OR ".$table_prefix."_accountscf.".$ratingField."='Riattivato')
			group by 
			".$table_prefix."_account.accountid, 
			".$table_prefix."_account.account_no,
			".$table_prefix."_account.accountname,
			".$table_prefix."_targets.targetname,
			".$table_prefix."_targetscf.".$codiceCorsoTargetField." ,
			".$table_prefix."_account.rating,
			".$table_prefix."_accountscf.".$ratingField."
			order by ".$table_prefix."_account.accountid";
		return $sql;
	}
	
	private function _get_target_campaign_sql() {
		global $table_prefix, $ratingField, $codiceCorsoTargetField,$codiceCorsoCampagnaField, $codiceFatturazioneCorsoField,$codiceCategoriaField,$tipoAffiliazioneField;
		$sql = "SELECT 
			".$table_prefix."_account.accountid, 
			".$table_prefix."_account.account_no,
			".$table_prefix."_account.accountname,
			".$table_prefix."_targets.targetname,
			".$table_prefix."_targetscf.".$codiceCorsoTargetField." as codice_corso_target,
			".$table_prefix."_account.rating,
			".$table_prefix."_accountscf.".$ratingField." as rating_attuale,
			".$table_prefix."_campaign.campaignname,
			".$table_prefix."_campaignscf.".$codiceCorsoCampagnaField." as codice_corso_campagna,
			".$table_prefix."_campaignscf.".$codiceFatturazioneCorsoField." as codice_fatturazione, -- Per i download = 'ND'
			CASE WHEN ".$table_prefix."_campaignscf.".$codiceFatturazioneCorsoField." IN ('RFCACN','RFCAPC','RHCA') THEN 2  ELSE 1 END as prog_rating_value ,
			count(*) as targetsum
			FROM ".$table_prefix."_account 
			JOIN ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_account.accountid AND ".$table_prefix."_crmentity.deleted = 0
			JOIN ".$table_prefix."_accountscf on ".$table_prefix."_accountscf.accountid =  ".$table_prefix."_account.accountid AND ".$table_prefix."_accountscf.".$codiceCategoriaField." = 'RP / PROG' 
			JOIN ".$table_prefix."_accountbillads on ".$table_prefix."_accountbillads.accountaddressid =  ".$table_prefix."_account.accountid AND ".$table_prefix."_accountbillads.bill_country like 'IT%'
			JOIN ".$table_prefix."_crmentityrel on ".$table_prefix."_crmentityrel.crmid = ".$table_prefix."_accountscf.accountid AND ".$table_prefix."_crmentityrel.relmodule = 'Targets'
			JOIN ".$table_prefix."_targets on ".$table_prefix."_targets.targetsid = ".$table_prefix."_crmentityrel.relcrmid
			JOIN ".$table_prefix."_targetscf on ".$table_prefix."_targetscf.targetsid = ".$table_prefix."_targets.targetsid AND ".$table_prefix."_targetscf.".$codiceCorsoTargetField." <>''  AND ".$table_prefix."_targetscf.".$codiceCorsoTargetField." IS NOT NULL
			JOIN ".$table_prefix."_crmentityrel as campaigns_crmentityrel on campaigns_crmentityrel.crmid = ".$table_prefix."_targets.targetsid AND campaigns_crmentityrel.module = 'Targets' AND campaigns_crmentityrel.relmodule = 'Campaigns'
			JOIN ".$table_prefix."_campaign on ".$table_prefix."_campaign.campaignid = campaigns_crmentityrel.relcrmid
			JOIN ".$table_prefix."_campaignscf on ".$table_prefix."_campaignscf.campaignid = ".$table_prefix."_campaign.campaignid
			JOIN ".$table_prefix."_crmentity as campaign_crmentity on campaign_crmentity.crmid = ".$table_prefix."_campaign.campaignid AND campaign_crmentity.deleted=0
			WHERE (".$table_prefix."_account.rating = '' OR ".$table_prefix."_account.rating = 'Active' OR ".$table_prefix."_account.rating ='--None--' OR ".$table_prefix."_account.rating ='Acquired') 
			AND (".$table_prefix."_accountscf.".$ratingField." IS NULL OR ".$table_prefix."_accountscf.".$ratingField."='' OR ".$table_prefix."_accountscf.".$ratingField."='1'  OR ".$table_prefix."_accountscf.".$ratingField."='35' OR ".$table_prefix."_accountscf.".$ratingField."='36'   OR ".$table_prefix."_accountscf.".$ratingField."='Riattivato')
			AND ".$table_prefix."_campaignscf.".$codiceFatturazioneCorsoField." IN ('RFCBC','RFCAC','RFCACN','RFCAPC','RSCAP','RSCA','RSCBDPI','RHCB','RHCA','RHCT','RBFCACM','RHCI','ND') -- = 'ND' sono i download
			group by 
			".$table_prefix."_account.accountid, 
			".$table_prefix."_account.account_no,
			".$table_prefix."_account.accountname,
			".$table_prefix."_targets.targetname,
			".$table_prefix."_targetscf.".$codiceCorsoTargetField." ,
			".$table_prefix."_account.rating,
			".$table_prefix."_accountscf.".$ratingField.",
			".$table_prefix."_campaign.campaignname,
			".$table_prefix."_campaignscf.".$codiceCorsoCampagnaField.",
			".$table_prefix."_campaignscf.".$codiceFatturazioneCorsoField.",
			CASE WHEN ".$table_prefix."_campaignscf.".$codiceFatturazioneCorsoField." IN ('RFCACN','RFCAPC','RHCA') THEN 2  ELSE 1 END
			order by ".$table_prefix."_account.accountid";
		return $sql;
	}
	
	private function _get_consulenze_sql() {
		global $table_prefix, $ratingField, $codiceCorsoTargetField,$codiceCorsoCampagnaField, $codiceFatturazioneCorsoField,$codiceCategoriaField,$tipoAffiliazioneField;
		$sql = "SELECT 
				".$table_prefix."_account.accountid, 
				".$table_prefix."_account.account_no,
				".$table_prefix."_account.accountname,
				".$table_prefix."_consulenza.consulenzaname,
				".$table_prefix."_consulenzaname.consulenzaname as consulenza_title,
				".$table_prefix."_consulenza.product_cat, 
				".$table_prefix."_account.rating,
				".$table_prefix."_accountscf.".$ratingField." as rating_attuale,
				2 as prog_rating_value
				FROM ".$table_prefix."_account 
				JOIN ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_account.accountid AND ".$table_prefix."_crmentity.deleted = 0
				JOIN ".$table_prefix."_accountscf on ".$table_prefix."_accountscf.accountid =  ".$table_prefix."_account.accountid AND ".$table_prefix."_accountscf.".$codiceCategoriaField." = 'RP / PROG' 
				JOIN ".$table_prefix."_accountbillads on ".$table_prefix."_accountbillads.accountaddressid =  ".$table_prefix."_account.accountid AND ".$table_prefix."_accountbillads.bill_country like 'IT%'
				JOIN ".$table_prefix."_consulenza on ".$table_prefix."_consulenza.parent = ".$table_prefix."_account.accountid
				JOIN ".$table_prefix."_crmentity as consulenza_crmentity on consulenza_crmentity.crmid = ".$table_prefix."_consulenza.consulenzaid AND consulenza_crmentity.deleted = 0 
				LEFT JOIN ".$table_prefix."_consulenzaname on CONVERT(VARCHAR, ".$table_prefix."_consulenzaname.consulenzanameid ) = ".$table_prefix."_consulenza.consulenzaname
				WHERE (".$table_prefix."_account.rating = '' OR ".$table_prefix."_account.rating = 'Active' OR ".$table_prefix."_account.rating ='--None--' OR ".$table_prefix."_account.rating ='Acquired') 
				AND (".$table_prefix."_accountscf.".$ratingField." IS NULL OR ".$table_prefix."_accountscf.".$ratingField."='' OR ".$table_prefix."_accountscf.".$ratingField."='1'  OR ".$table_prefix."_accountscf.".$ratingField."='35' OR ".$table_prefix."_accountscf.".$ratingField."='36'   OR ".$table_prefix."_accountscf.".$ratingField."='Riattivato')
				";
		return $sql;
	}
	
	private function _get_affiliazione_sql() {
		global $table_prefix, $ratingField, $codiceCorsoTargetField,$codiceCorsoCampagnaField, $codiceFatturazioneCorsoField,$codiceCategoriaField,$tipoAffiliazioneField;
		$sql = "SELECT 
				".$table_prefix."_account.accountid, 
				".$table_prefix."_account.account_no,
				".$table_prefix."_account.accountname,
				".$table_prefix."_account.rating,
				".$table_prefix."_accountscf.".$ratingField." as rating_attuale,
				".$table_prefix."_accountscf.".$tipoAffiliazioneField." as tipo_affiliazione
				FROM ".$table_prefix."_account 
				JOIN ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_account.accountid AND ".$table_prefix."_crmentity.deleted = 0
				JOIN ".$table_prefix."_accountscf on ".$table_prefix."_accountscf.accountid =  ".$table_prefix."_account.accountid AND ".$table_prefix."_accountscf.".$codiceCategoriaField." = 'RP / PROG' 
				JOIN ".$table_prefix."_accountbillads on ".$table_prefix."_accountbillads.accountaddressid =  ".$table_prefix."_account.accountid AND ".$table_prefix."_accountbillads.bill_country like 'IT%'
				WHERE (".$table_prefix."_account.rating = '' OR ".$table_prefix."_account.rating = 'Active' OR ".$table_prefix."_account.rating ='--None--' OR ".$table_prefix."_account.rating ='Acquired') 
				AND (".$table_prefix."_accountscf.".$ratingField." IS NULL 
						OR ".$table_prefix."_accountscf.".$ratingField."='' 
						OR ".$table_prefix."_accountscf.".$ratingField."='1'  
						OR ".$table_prefix."_accountscf.".$ratingField."='35' 
						OR ".$table_prefix."_accountscf.".$ratingField."='36'   
						OR ".$table_prefix."_accountscf.".$ratingField."='Riattivato')
				AND ".$table_prefix."_accountscf.".$tipoAffiliazioneField." IS NOT NULL
				AND ".$table_prefix."_accountscf.".$tipoAffiliazioneField." <>''";
		return $sql;
	}
	
	private function _get_opportunita_sql() {
		global $table_prefix, $ratingField, $codiceCorsoTargetField,$codiceCorsoCampagnaField, $codiceFatturazioneCorsoField,$codiceCategoriaField,$tipoAffiliazioneField;
		$sql = "SELECT 
				".$table_prefix."_account.accountid, 
				".$table_prefix."_account.account_no,
				".$table_prefix."_account.accountname,
				".$table_prefix."_account.rating,
				".$table_prefix."_accountscf.".$ratingField." as rating_attuale,
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
					WHEN ".$table_prefix."_potential.amount < 10000 THEN '< 10K'  
					WHEN ".$table_prefix."_potential.amount >= 10000 AND ".$table_prefix."_potential.amount < 20000 THEN '> 10K'  
					WHEN ".$table_prefix."_potential.amount >= 20000 AND ".$table_prefix."_potential.amount < 50000 THEN '> 20K'  
					WHEN ".$table_prefix."_potential.amount >= 50000 AND ".$table_prefix."_potential.amount < 100000 THEN '> 50K'  
					WHEN ".$table_prefix."_potential.amount >= 100000 THEN '> 100K'  
				END as prog_rating_title
				FROM ".$table_prefix."_account 
				JOIN ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_account.accountid AND ".$table_prefix."_crmentity.deleted = 0
				JOIN ".$table_prefix."_accountscf on ".$table_prefix."_accountscf.accountid =  ".$table_prefix."_account.accountid AND ".$table_prefix."_accountscf.".$codiceCategoriaField." = 'RP / PROG' 
				JOIN ".$table_prefix."_accountbillads on ".$table_prefix."_accountbillads.accountaddressid =  ".$table_prefix."_account.accountid AND ".$table_prefix."_accountbillads.bill_country like 'IT%'
				JOIN ".$table_prefix."_potential on ".$table_prefix."_potential.related_to = ".$table_prefix."_account.accountid 
				JOIN ".$table_prefix."_crmentity as potential_crmentity on potential_crmentity.crmid = ".$table_prefix."_potential.potentialid AND potential_crmentity.deleted = 0
				WHERE (".$table_prefix."_account.rating = '' OR ".$table_prefix."_account.rating = 'Active' OR ".$table_prefix."_account.rating ='--None--' OR ".$table_prefix."_account.rating ='Acquired') 
				AND (".$table_prefix."_accountscf.".$ratingField." IS NULL 
						OR ".$table_prefix."_accountscf.".$ratingField."='' 
						OR ".$table_prefix."_accountscf.".$ratingField."='1'  
						OR ".$table_prefix."_accountscf.".$ratingField."='35' 
						OR ".$table_prefix."_accountscf.".$ratingField."='36'   
						OR ".$table_prefix."_accountscf.".$ratingField."='Riattivato')";
		return $sql;
	}


}

?>
