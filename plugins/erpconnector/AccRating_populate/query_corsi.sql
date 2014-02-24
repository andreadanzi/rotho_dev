codiceCorsoCampagnaField = cf_742 and log_active = 1 
_get_target_campaign_sql query= SELECT DISTINCT 
			vtiger_account.accountid, 
			vtiger_account.account_no,
			vtiger_account.accountname,
			vtiger_accountbillads.bill_country, 
			vtiger_targets.targetname,
			vtiger_targetscf.cf_1006 as codice_corso_target,
			vtiger_account.rating,
			vtiger_accountscf.cf_927 as rating_attuale,
			vtiger_campaign.campaignname,
			vtiger_campaignscf.cf_742 as codice_corso_campagna,
			vtiger_campaignscf.cf_745 as prog_rating_date,
			vtiger_campaignscf.cf_759 as codice_fatturazione, -- Per i download = 'ND'
			CASE WHEN vtiger_campaignscf.cf_759 IN ('RFCACN','RFCAPC','RSCAP','RHCA','RHCT','RBCACM') THEN 2  ELSE 1 END as prog_rating_value ,
			count(*) as targetsum
			FROM vtiger_account 
			JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid AND vtiger_crmentity.deleted = 0
			JOIN vtiger_accountscf on vtiger_accountscf.accountid =  vtiger_account.accountid AND vtiger_accountscf.cf_762 = 'RP / PROG' 
			JOIN vtiger_accountbillads on vtiger_accountbillads.accountaddressid =  vtiger_account.accountid AND ( 
			vtiger_accountbillads.bill_country like 'IT%' OR  
			vtiger_accountbillads.bill_country like 'ES%' OR  
			vtiger_accountbillads.bill_country like 'PT%' OR  
			vtiger_accountbillads.bill_country like 'DE%' OR  
			vtiger_accountbillads.bill_country like 'AT%' OR  
			vtiger_accountbillads.bill_country like 'CH%' OR
			vtiger_accountbillads.bill_country like 'FR%' OR  
			vtiger_accountbillads.bill_country like 'GB%' OR  
			vtiger_accountbillads.bill_country like 'PL%' OR  
			vtiger_accountbillads.bill_country like 'RO%' OR  
			vtiger_accountbillads.bill_country like 'IE%' OR
			vtiger_accountbillads.bill_country like 'RU%' OR
			vtiger_accountbillads.bill_country like 'SE%' OR
			vtiger_accountbillads.bill_country like 'NO%' OR
			vtiger_accountbillads.bill_country like 'FI%'  )
			JOIN vtiger_crmentityrel on vtiger_crmentityrel.relcrmid = vtiger_accountscf.accountid AND vtiger_crmentityrel.module = 'Targets'
			JOIN vtiger_targets on vtiger_targets.targetsid = vtiger_crmentityrel.crmid
			JOIN vtiger_targetscf on vtiger_targetscf.targetsid = vtiger_targets.targetsid AND vtiger_targetscf.cf_1006 <>''  AND vtiger_targetscf.cf_1006 IS NOT NULL
			JOIN vtiger_crmentityrel as campaigns_crmentityrel on campaigns_crmentityrel.crmid = vtiger_targets.targetsid AND campaigns_crmentityrel.module = 'Targets' AND campaigns_crmentityrel.relmodule = 'Campaigns'
			JOIN vtiger_campaign on vtiger_campaign.campaignid = campaigns_crmentityrel.relcrmid
			JOIN vtiger_campaignscf on vtiger_campaignscf.campaignid = vtiger_campaign.campaignid
			JOIN vtiger_crmentity as campaign_crmentity on campaign_crmentity.crmid = vtiger_campaign.campaignid AND campaign_crmentity.deleted=0
			WHERE (vtiger_account.rating = '' OR vtiger_account.rating = 'Active' OR vtiger_account.rating ='--None--' OR vtiger_account.rating ='Acquired') 
			AND (vtiger_accountscf.cf_927 IS NULL OR vtiger_accountscf.cf_927='' OR vtiger_accountscf.cf_927='1'  OR vtiger_accountscf.cf_927='35' OR vtiger_accountscf.cf_927='36'   OR vtiger_accountscf.cf_927='Riattivato')
			AND vtiger_campaignscf.cf_759 IN ('RFCBC','RFCAC','RFCACN','RFCAPC','RSCAP','RSCA','RSCBDPI','RHCB','RHCA','RHCT','RBFCACM','RHCI','ND') 
			
			group by 
			vtiger_account.accountid, 
			vtiger_account.account_no,
			vtiger_account.accountname,
			vtiger_accountbillads.bill_country, 
			vtiger_targets.targetname,
			vtiger_targetscf.cf_1006 ,
			vtiger_account.rating,
			vtiger_accountscf.cf_927,
			vtiger_campaign.campaignname,
			vtiger_campaignscf.cf_745,
			vtiger_campaignscf.cf_742,
			vtiger_campaignscf.cf_759,
			CASE WHEN vtiger_campaignscf.cf_759 IN ('RFCACN','RFCAPC','RSCAP','RHCA','RHCT','RBCACM') THEN 2  ELSE 1 END
			order by vtiger_account.accountid 
_get_consulenze_sql query= SELECT 
				vtiger_account.accountid, 
				vtiger_account.account_no,
				vtiger_account.accountname,
				vtiger_accountbillads.bill_country, 
				vtiger_consulenza.consulenzaname,
				vtiger_consulenzaname.consulenzaname as consulenza_title,
				vtiger_consulenza.product_cat, 
				vtiger_account.rating,
				vtiger_accountscf.cf_927 as rating_attuale,
				consulenza_crmentity.modifiedtime as prog_rating_date,
				2 as prog_rating_value
				FROM vtiger_account 
				JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid AND vtiger_crmentity.deleted = 0
				JOIN vtiger_accountscf on vtiger_accountscf.accountid =  vtiger_account.accountid AND vtiger_accountscf.cf_762 = 'RP / PROG' 
				JOIN vtiger_accountbillads on vtiger_accountbillads.accountaddressid =  vtiger_account.accountid AND ( 
				vtiger_accountbillads.bill_country like 'IT%' OR  
				vtiger_accountbillads.bill_country like 'ES%' OR  
				vtiger_accountbillads.bill_country like 'PT%' OR  
				vtiger_accountbillads.bill_country like 'DE%' OR  
				vtiger_accountbillads.bill_country like 'AT%' OR  
				vtiger_accountbillads.bill_country like 'CH%' OR
				vtiger_accountbillads.bill_country like 'FR%' OR  
				vtiger_accountbillads.bill_country like 'GB%' OR  
				vtiger_accountbillads.bill_country like 'PL%' OR  
				vtiger_accountbillads.bill_country like 'RO%' OR  
				vtiger_accountbillads.bill_country like 'IE%' OR
				vtiger_accountbillads.bill_country like 'RU%' OR
				vtiger_accountbillads.bill_country like 'SE%' OR
				vtiger_accountbillads.bill_country like 'NO%' OR
				vtiger_accountbillads.bill_country like 'FI%'  )
				JOIN vtiger_consulenza on vtiger_consulenza.parent = vtiger_account.accountid
				JOIN vtiger_crmentity as consulenza_crmentity on consulenza_crmentity.crmid = vtiger_consulenza.consulenzaid AND consulenza_crmentity.deleted = 0 
				LEFT JOIN vtiger_consulenzaname on CONVERT(VARCHAR, vtiger_consulenzaname.consulenzanameid ) = vtiger_consulenza.consulenzaname
				WHERE (vtiger_account.rating = '' OR vtiger_account.rating = 'Active' OR vtiger_account.rating ='--None--' OR vtiger_account.rating ='Acquired') 
				AND (vtiger_accountscf.cf_927 IS NULL OR vtiger_accountscf.cf_927='' OR vtiger_accountscf.cf_927='1'  OR vtiger_accountscf.cf_927='35' OR vtiger_accountscf.cf_927='36'   OR vtiger_accountscf.cf_927='Riattivato')  
_get_affiliazione_sql query= SELECT 
				vtiger_account.accountid, 
				vtiger_account.account_no,
				vtiger_account.accountname,
				vtiger_account.rating,
				vtiger_accountbillads.bill_country, 
				vtiger_accountscf.cf_927 as rating_attuale,
				vtiger_accountscf.cf_1178 as tipo_affiliazione,
				vtiger_crmentity.modifiedtime as prog_rating_date 
				FROM vtiger_account 
				JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid AND vtiger_crmentity.deleted = 0
				JOIN vtiger_accountscf on vtiger_accountscf.accountid =  vtiger_account.accountid AND vtiger_accountscf.cf_762 = 'RP / PROG' 
				JOIN vtiger_accountbillads on vtiger_accountbillads.accountaddressid =  vtiger_account.accountid AND ( 
				vtiger_accountbillads.bill_country like 'IT%' OR  
				vtiger_accountbillads.bill_country like 'ES%' OR  
				vtiger_accountbillads.bill_country like 'PT%' OR  
				vtiger_accountbillads.bill_country like 'DE%' OR  
				vtiger_accountbillads.bill_country like 'AT%' OR  
				vtiger_accountbillads.bill_country like 'CH%' OR
				vtiger_accountbillads.bill_country like 'FR%' OR  
				vtiger_accountbillads.bill_country like 'GB%' OR  
				vtiger_accountbillads.bill_country like 'PL%' OR  
				vtiger_accountbillads.bill_country like 'RO%' OR  
				vtiger_accountbillads.bill_country like 'IE%' OR
				vtiger_accountbillads.bill_country like 'RU%' OR
				vtiger_accountbillads.bill_country like 'SE%' OR
				vtiger_accountbillads.bill_country like 'NO%' OR
				vtiger_accountbillads.bill_country like 'FI%'  )
				WHERE (vtiger_account.rating = '' OR vtiger_account.rating = 'Active' OR vtiger_account.rating ='--None--' OR vtiger_account.rating ='Acquired') 
				AND (vtiger_accountscf.cf_927 IS NULL 
						OR vtiger_accountscf.cf_927='' 
						OR vtiger_accountscf.cf_927='1'  
						OR vtiger_accountscf.cf_927='35' 
						OR vtiger_accountscf.cf_927='36'   
						OR vtiger_accountscf.cf_927='Riattivato')
				AND vtiger_accountscf.cf_1178 IS NOT NULL
				AND vtiger_accountscf.cf_1178 <>''
				 
_get_opportunita_sql query= SELECT 
				vtiger_account.accountid, 
				vtiger_account.account_no,
				vtiger_account.accountname,
				vtiger_accountbillads.bill_country, 
				vtiger_account.rating,
				vtiger_accountscf.cf_927 as rating_attuale,
				vtiger_potential.potentialname,
				vtiger_potential.potentialid,
				vtiger_potential.potential_no,
				vtiger_potential.amount,
				CASE 
					WHEN vtiger_potential.amount < 10000 THEN 2  
					WHEN vtiger_potential.amount >= 10000 AND vtiger_potential.amount < 20000 THEN 3
					WHEN vtiger_potential.amount >= 20000 AND vtiger_potential.amount < 50000 THEN 4
					WHEN vtiger_potential.amount >= 50000 AND vtiger_potential.amount < 100000 THEN 5
					WHEN vtiger_potential.amount >= 100000 THEN 6
				END as prog_rating_value,
				CASE 
					WHEN vtiger_potential.amount < 10000 THEN vtiger_potential.potentialname + ' (< 10K)'  
					WHEN vtiger_potential.amount >= 10000 AND vtiger_potential.amount < 20000 THEN vtiger_potential.potentialname + ' (> 10K)'  
					WHEN vtiger_potential.amount >= 20000 AND vtiger_potential.amount < 50000 THEN vtiger_potential.potentialname + ' (> 20K)'  
					WHEN vtiger_potential.amount >= 50000 AND vtiger_potential.amount < 100000 THEN vtiger_potential.potentialname + ' (> 50K)'  
					WHEN vtiger_potential.amount >= 100000 THEN vtiger_potential.potentialname + ' (> 100K)'  
				END as prog_rating_title,
				potential_crmentity.modifiedtime as prog_rating_date 
				FROM vtiger_account 
				JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid AND vtiger_crmentity.deleted = 0
				JOIN vtiger_accountscf on vtiger_accountscf.accountid =  vtiger_account.accountid AND vtiger_accountscf.cf_762 = 'RP / PROG' 
				JOIN vtiger_accountbillads on vtiger_accountbillads.accountaddressid =  vtiger_account.accountid AND (  
				vtiger_accountbillads.bill_country like 'IT%' OR  
				vtiger_accountbillads.bill_country like 'ES%' OR  
				vtiger_accountbillads.bill_country like 'PT%' OR  
				vtiger_accountbillads.bill_country like 'DE%' OR  
				vtiger_accountbillads.bill_country like 'AT%' OR  
				vtiger_accountbillads.bill_country like 'CH%' OR
				vtiger_accountbillads.bill_country like 'FR%' OR  
				vtiger_accountbillads.bill_country like 'GB%' OR  
				vtiger_accountbillads.bill_country like 'PL%' OR  
				vtiger_accountbillads.bill_country like 'RO%' OR  
				vtiger_accountbillads.bill_country like 'IE%' OR
				vtiger_accountbillads.bill_country like 'RU%' OR
				vtiger_accountbillads.bill_country like 'SE%' OR
				vtiger_accountbillads.bill_country like 'NO%' OR
				vtiger_accountbillads.bill_country like 'FI%'  )
				JOIN vtiger_potential on vtiger_potential.related_to = vtiger_account.accountid 
				JOIN vtiger_crmentity as potential_crmentity on potential_crmentity.crmid = vtiger_potential.potentialid AND potential_crmentity.deleted = 0
				WHERE (vtiger_account.rating = '' OR vtiger_account.rating = 'Active' OR vtiger_account.rating ='--None--' OR vtiger_account.rating ='Acquired') 
				AND vtiger_potential.sales_stage not in ( 'Closed Won' , 'Closed Lost') 
				AND (vtiger_accountscf.cf_927 IS NULL 
						OR vtiger_accountscf.cf_927='' 
						OR vtiger_accountscf.cf_927='1'  
						OR vtiger_accountscf.cf_927='35' 
						OR vtiger_accountscf.cf_927='36'   
						OR vtiger_accountscf.cf_927='Riattivato')
				 
_get_opportunita_closed_won_sql query= SELECT 
				vtiger_account.accountid, 
				vtiger_account.account_no,
				vtiger_account.accountname,
				vtiger_accountbillads.bill_country, 
				vtiger_account.rating,
				vtiger_accountscf.cf_927 as rating_attuale,
				vtiger_potential.potentialname + ' (Chiusa Vinta) '  as prog_rating_title,
				vtiger_potential.potentialid,
				vtiger_potential.potential_no,
				vtiger_potential.amount,
				1 as prog_rating_value ,
				potential_crmentity.createdtime as prog_rating_date 
				FROM vtiger_account 
				JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid AND vtiger_crmentity.deleted = 0
				JOIN vtiger_accountscf on vtiger_accountscf.accountid =  vtiger_account.accountid AND vtiger_accountscf.cf_762 = 'RP / PROG' 
				JOIN vtiger_accountbillads on vtiger_accountbillads.accountaddressid =  vtiger_account.accountid AND (  
				vtiger_accountbillads.bill_country like 'IT%' OR  
				vtiger_accountbillads.bill_country like 'ES%' OR  
				vtiger_accountbillads.bill_country like 'PT%' OR  
				vtiger_accountbillads.bill_country like 'DE%' OR  
				vtiger_accountbillads.bill_country like 'AT%' OR  
				vtiger_accountbillads.bill_country like 'CH%' OR
				vtiger_accountbillads.bill_country like 'FR%' OR  
				vtiger_accountbillads.bill_country like 'GB%' OR  
				vtiger_accountbillads.bill_country like 'PL%' OR  
				vtiger_accountbillads.bill_country like 'RO%' OR  
				vtiger_accountbillads.bill_country like 'IE%' OR
				vtiger_accountbillads.bill_country like 'RU%' OR
				vtiger_accountbillads.bill_country like 'SE%' OR
				vtiger_accountbillads.bill_country like 'NO%' OR
				vtiger_accountbillads.bill_country like 'FI%'  )
				JOIN vtiger_potential on vtiger_potential.related_to = vtiger_account.accountid 
				JOIN vtiger_crmentity as potential_crmentity on potential_crmentity.crmid = vtiger_potential.potentialid AND potential_crmentity.deleted = 0
				WHERE (vtiger_account.rating = '' OR vtiger_account.rating = 'Active' OR vtiger_account.rating ='--None--' OR vtiger_account.rating ='Acquired') 
				AND vtiger_potential.sales_stage = 'Closed Won'  
				AND (vtiger_accountscf.cf_927 IS NULL 
						OR vtiger_accountscf.cf_927='' 
						OR vtiger_accountscf.cf_927='1'  
						OR vtiger_accountscf.cf_927='35' 
						OR vtiger_accountscf.cf_927='36'   
						OR vtiger_accountscf.cf_927='Riattivato')
				 
_get_fiere_sql query= SELECT 
				vtiger_account.accountid, 
				vtiger_account.account_no,
				vtiger_account.accountname,
				vtiger_account.rating,
				vtiger_accountscf.cf_927 as rating_attuale,
				vtiger_activity.date_start as prog_rating_date ,
				vtiger_activity.subject as prog_rating_title ,
				2 as prog_rating_value
				FROM vtiger_account 
				JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid AND vtiger_crmentity.deleted = 0
				JOIN vtiger_accountscf on vtiger_accountscf.accountid =  vtiger_account.accountid AND vtiger_accountscf.cf_762 = 'RP / PROG' 
				JOIN vtiger_accountbillads on vtiger_accountbillads.accountaddressid =  vtiger_account.accountid AND (  
				vtiger_accountbillads.bill_country like 'IT%' OR  
				vtiger_accountbillads.bill_country like 'ES%' OR  
				vtiger_accountbillads.bill_country like 'PT%' OR  
				vtiger_accountbillads.bill_country like 'DE%' OR  
				vtiger_accountbillads.bill_country like 'AT%' OR  
				vtiger_accountbillads.bill_country like 'CH%' OR
				vtiger_accountbillads.bill_country like 'FR%' OR  
				vtiger_accountbillads.bill_country like 'GB%' OR  
				vtiger_accountbillads.bill_country like 'PL%' OR  
				vtiger_accountbillads.bill_country like 'RO%' OR  
				vtiger_accountbillads.bill_country like 'IE%' OR
				vtiger_accountbillads.bill_country like 'RU%' OR
				vtiger_accountbillads.bill_country like 'SE%' OR
				vtiger_accountbillads.bill_country like 'NO%' OR
				vtiger_accountbillads.bill_country like 'FI%'  )
				JOIN vtiger_seactivityrel ON vtiger_seactivityrel.crmid = vtiger_account.accountid
				JOIN vtiger_activity ON vtiger_activity.activityid = vtiger_seactivityrel.activityid AND vtiger_activity.activitytype ='Contatto - Fiera'
				JOIN vtiger_crmentity as activity_crmentity ON activity_crmentity.crmid = vtiger_activity.activityid  AND activity_crmentity.deleted = 0 
				WHERE (vtiger_account.rating = '' OR vtiger_account.rating = 'Active' OR vtiger_account.rating ='--None--' OR vtiger_account.rating ='Acquired') 
				AND (vtiger_accountscf.cf_927 IS NULL 
						OR vtiger_accountscf.cf_927='' 
						OR vtiger_accountscf.cf_927='1'  
						OR vtiger_accountscf.cf_927='35' 
						OR vtiger_accountscf.cf_927='36'   
						OR vtiger_accountscf.cf_927='Riattivato')
				 
_get_input_points_sql query= SELECT 
				vtiger_account.accountid, 
				vtiger_account.account_no,
				vtiger_account.accountname,
				vtiger_accountbillads.bill_country, 
				vtiger_account.rating,
				vtiger_accountscf.cf_927 as rating_attuale,
				vtiger_account.input_points as prog_rating_value ,
				vtiger_crmentity.modifiedtime as prog_rating_date 
				FROM vtiger_account 
				JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid AND vtiger_crmentity.deleted = 0
				JOIN vtiger_accountscf on vtiger_accountscf.accountid =  vtiger_account.accountid AND vtiger_accountscf.cf_762 = 'RP / PROG' 
				JOIN vtiger_accountbillads on vtiger_accountbillads.accountaddressid =  vtiger_account.accountid AND (  
				vtiger_accountbillads.bill_country like 'IT%' OR  
				vtiger_accountbillads.bill_country like 'ES%' OR  
				vtiger_accountbillads.bill_country like 'PT%' OR  
				vtiger_accountbillads.bill_country like 'DE%' OR  
				vtiger_accountbillads.bill_country like 'AT%' OR  
				vtiger_accountbillads.bill_country like 'CH%' OR
				vtiger_accountbillads.bill_country like 'FR%' OR  
				vtiger_accountbillads.bill_country like 'GB%' OR  
				vtiger_accountbillads.bill_country like 'PL%' OR  
				vtiger_accountbillads.bill_country like 'RO%' OR  
				vtiger_accountbillads.bill_country like 'IE%' OR
				vtiger_accountbillads.bill_country like 'RU%' OR
				vtiger_accountbillads.bill_country like 'SE%' OR
				vtiger_accountbillads.bill_country like 'NO%' OR
				vtiger_accountbillads.bill_country like 'FI%'  )
				WHERE (vtiger_account.rating = '' OR vtiger_account.rating = 'Active' OR vtiger_account.rating ='--None--' OR vtiger_account.rating ='Acquired') 
				AND (vtiger_accountscf.cf_927 IS NULL 
						OR vtiger_accountscf.cf_927='' 
						OR vtiger_accountscf.cf_927='1'  
						OR vtiger_accountscf.cf_927='35' 
						OR vtiger_accountscf.cf_927='36'   
						OR vtiger_accountscf.cf_927='Riattivato')
				AND vtiger_account.input_points IS NOT NULL
				AND vtiger_account.input_points <> 0 
				
				
				
--------------- RC CARP				
_get_target_campaign_sql query= SELECT DISTINCT 
			vtiger_account.accountid, 
			vtiger_account.account_no,
			vtiger_account.accountname,
			vtiger_accountbillads.bill_country, 
			vtiger_targets.targetname,
			vtiger_targetscf.cf_1006 as codice_corso_target,
			vtiger_account.rating,
			vtiger_accountscf.cf_927 as rating_attuale,
			vtiger_campaign.campaignname,
			vtiger_campaignscf.cf_742 as codice_corso_campagna,
			vtiger_campaignscf.cf_745 as prog_rating_date,
			vtiger_campaignscf.cf_759 as codice_fatturazione, -- Per i download = 'ND'
			CASE WHEN vtiger_campaignscf.cf_759 IN ('RFCACN','RFCAPC','RSCAP','RHCA','RHCT','RBCACM') THEN 2  ELSE 1 END as prog_rating_value ,
			count(*) as targetsum
			FROM vtiger_account 
			JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid AND vtiger_crmentity.deleted = 0
			JOIN vtiger_accountscf on vtiger_accountscf.accountid =  vtiger_account.accountid AND vtiger_accountscf.cf_762 = 'RC / CARP' 
			JOIN vtiger_accountbillads on vtiger_accountbillads.accountaddressid =  vtiger_account.accountid AND ( 
			vtiger_accountbillads.bill_country like 'AR%' OR  
			vtiger_accountbillads.bill_country like 'BR%' OR  
			vtiger_accountbillads.bill_country like 'CL%' OR  
			vtiger_accountbillads.bill_country like 'CO%' OR  
			vtiger_accountbillads.bill_country like 'EC%' OR  
			vtiger_accountbillads.bill_country like 'UY%' OR
			vtiger_accountbillads.bill_country like 'PE%' OR  
			vtiger_accountbillads.bill_country like 'CR%' OR  
			vtiger_accountbillads.bill_country like 'MX%' OR  
			vtiger_accountbillads.bill_country like 'BO%' OR  
			vtiger_accountbillads.bill_country like 'PY%' OR
			vtiger_accountbillads.bill_country like 'PA%' OR
			vtiger_accountbillads.bill_country like 'VE%' OR
			vtiger_accountbillads.bill_country like 'NI%' OR
			vtiger_accountbillads.bill_country like 'GT%' OR
			vtiger_accountbillads.bill_country like 'GY%' OR
			vtiger_accountbillads.bill_country like 'GF%'  )
			JOIN vtiger_crmentityrel on vtiger_crmentityrel.relcrmid = vtiger_accountscf.accountid AND vtiger_crmentityrel.module = 'Targets'
			JOIN vtiger_targets on vtiger_targets.targetsid = vtiger_crmentityrel.crmid
			JOIN vtiger_targetscf on vtiger_targetscf.targetsid = vtiger_targets.targetsid AND vtiger_targetscf.cf_1006 <>''  AND vtiger_targetscf.cf_1006 IS NOT NULL
			JOIN vtiger_crmentityrel as campaigns_crmentityrel on campaigns_crmentityrel.crmid = vtiger_targets.targetsid AND campaigns_crmentityrel.module = 'Targets' AND campaigns_crmentityrel.relmodule = 'Campaigns'
			JOIN vtiger_campaign on vtiger_campaign.campaignid = campaigns_crmentityrel.relcrmid
			JOIN vtiger_campaignscf on vtiger_campaignscf.campaignid = vtiger_campaign.campaignid
			JOIN vtiger_crmentity as campaign_crmentity on campaign_crmentity.crmid = vtiger_campaign.campaignid AND campaign_crmentity.deleted=0
			WHERE (vtiger_account.rating = '' OR vtiger_account.rating = 'Active' OR vtiger_account.rating ='--None--' OR vtiger_account.rating ='Acquired') 
			AND (vtiger_accountscf.cf_927 IS NULL OR vtiger_accountscf.cf_927='' OR vtiger_accountscf.cf_927='1'  OR vtiger_accountscf.cf_927='35' OR vtiger_accountscf.cf_927='36'   OR vtiger_accountscf.cf_927='Riattivato')
			AND vtiger_campaignscf.cf_759 IN ('RFCBC','RFCAC','RFCACN','RFCAPC','RSCAP','RSCA','RSCBDPI','RHCB','RHCA','RHCT','RBFCACM','RHCI','ND') 
			
			group by 
			vtiger_account.accountid, 
			vtiger_account.account_no,
			vtiger_account.accountname,
			vtiger_accountbillads.bill_country, 
			vtiger_targets.targetname,
			vtiger_targetscf.cf_1006 ,
			vtiger_account.rating,
			vtiger_accountscf.cf_927,
			vtiger_campaign.campaignname,
			vtiger_campaignscf.cf_745,
			vtiger_campaignscf.cf_742,
			vtiger_campaignscf.cf_759,
			CASE WHEN vtiger_campaignscf.cf_759 IN ('RFCACN','RFCAPC','RSCAP','RHCA','RHCT','RBCACM') THEN 2  ELSE 1 END
			order by vtiger_account.accountid 
_get_consulenze_sql query= SELECT 
				vtiger_account.accountid, 
				vtiger_account.account_no,
				vtiger_account.accountname,
				vtiger_accountbillads.bill_country, 
				vtiger_consulenza.consulenzaname,
				vtiger_consulenzaname.consulenzaname as consulenza_title,
				vtiger_consulenza.product_cat, 
				vtiger_account.rating,
				vtiger_accountscf.cf_927 as rating_attuale,
				consulenza_crmentity.modifiedtime as prog_rating_date,
				2 as prog_rating_value
				FROM vtiger_account 
				JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid AND vtiger_crmentity.deleted = 0
				JOIN vtiger_accountscf on vtiger_accountscf.accountid =  vtiger_account.accountid AND vtiger_accountscf.cf_762 = 'RC / CARP' 
				JOIN vtiger_accountbillads on vtiger_accountbillads.accountaddressid =  vtiger_account.accountid AND ( 
				vtiger_accountbillads.bill_country like 'AR%' OR  
				vtiger_accountbillads.bill_country like 'BR%' OR  
				vtiger_accountbillads.bill_country like 'CL%' OR  
				vtiger_accountbillads.bill_country like 'CO%' OR  
				vtiger_accountbillads.bill_country like 'EC%' OR  
				vtiger_accountbillads.bill_country like 'UY%' OR
				vtiger_accountbillads.bill_country like 'PE%' OR  
				vtiger_accountbillads.bill_country like 'CR%' OR  
				vtiger_accountbillads.bill_country like 'MX%' OR  
				vtiger_accountbillads.bill_country like 'BO%' OR  
				vtiger_accountbillads.bill_country like 'PY%' OR
				vtiger_accountbillads.bill_country like 'PA%' OR
				vtiger_accountbillads.bill_country like 'VE%' OR
				vtiger_accountbillads.bill_country like 'NI%' OR
				vtiger_accountbillads.bill_country like 'GT%' OR
				vtiger_accountbillads.bill_country like 'GY%' OR
				vtiger_accountbillads.bill_country like 'GF%' )
				JOIN vtiger_consulenza on vtiger_consulenza.parent = vtiger_account.accountid
				JOIN vtiger_crmentity as consulenza_crmentity on consulenza_crmentity.crmid = vtiger_consulenza.consulenzaid AND consulenza_crmentity.deleted = 0 
				LEFT JOIN vtiger_consulenzaname on CONVERT(VARCHAR, vtiger_consulenzaname.consulenzanameid ) = vtiger_consulenza.consulenzaname
				WHERE (vtiger_account.rating = '' OR vtiger_account.rating = 'Active' OR vtiger_account.rating ='--None--' OR vtiger_account.rating ='Acquired') 
				AND (vtiger_accountscf.cf_927 IS NULL OR vtiger_accountscf.cf_927='' OR vtiger_accountscf.cf_927='1'  OR vtiger_accountscf.cf_927='35' OR vtiger_accountscf.cf_927='36'   OR vtiger_accountscf.cf_927='Riattivato')  
_get_affiliazione_sql query= SELECT 
				vtiger_account.accountid, 
				vtiger_account.account_no,
				vtiger_account.accountname,
				vtiger_account.rating,
				vtiger_accountbillads.bill_country, 
				vtiger_accountscf.cf_927 as rating_attuale,
				vtiger_accountscf.cf_1178 as tipo_affiliazione,
				vtiger_crmentity.modifiedtime as prog_rating_date 
				FROM vtiger_account 
				JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid AND vtiger_crmentity.deleted = 0
				JOIN vtiger_accountscf on vtiger_accountscf.accountid =  vtiger_account.accountid AND vtiger_accountscf.cf_762 = 'RC / CARP' 
				JOIN vtiger_accountbillads on vtiger_accountbillads.accountaddressid =  vtiger_account.accountid AND ( 
				vtiger_accountbillads.bill_country like 'AR%' OR  
				vtiger_accountbillads.bill_country like 'BR%' OR  
				vtiger_accountbillads.bill_country like 'CL%' OR  
				vtiger_accountbillads.bill_country like 'CO%' OR  
				vtiger_accountbillads.bill_country like 'EC%' OR  
				vtiger_accountbillads.bill_country like 'UY%' OR
				vtiger_accountbillads.bill_country like 'PE%' OR  
				vtiger_accountbillads.bill_country like 'CR%' OR  
				vtiger_accountbillads.bill_country like 'MX%' OR  
				vtiger_accountbillads.bill_country like 'BO%' OR  
				vtiger_accountbillads.bill_country like 'PY%' OR
				vtiger_accountbillads.bill_country like 'PA%' OR
				vtiger_accountbillads.bill_country like 'VE%' OR
				vtiger_accountbillads.bill_country like 'NI%' OR
				vtiger_accountbillads.bill_country like 'GT%' OR
				vtiger_accountbillads.bill_country like 'GY%' OR
				vtiger_accountbillads.bill_country like 'GF%' )				
				WHERE (vtiger_account.rating = '' OR vtiger_account.rating = 'Active' OR vtiger_account.rating ='--None--' OR vtiger_account.rating ='Acquired') 
				AND (vtiger_accountscf.cf_927 IS NULL 
						OR vtiger_accountscf.cf_927='' 
						OR vtiger_accountscf.cf_927='1'  
						OR vtiger_accountscf.cf_927='35' 
						OR vtiger_accountscf.cf_927='36'   
						OR vtiger_accountscf.cf_927='Riattivato')
				AND vtiger_accountscf.cf_1178 IS NOT NULL
				AND vtiger_accountscf.cf_1178 <>''
				 
_get_opportunita_sql query= SELECT 
				vtiger_account.accountid, 
				vtiger_account.account_no,
				vtiger_account.accountname,
				vtiger_accountbillads.bill_country, 
				vtiger_account.rating,
				vtiger_accountscf.cf_927 as rating_attuale,
				vtiger_potential.potentialname,
				vtiger_potential.potentialid,
				vtiger_potential.potential_no,
				vtiger_potential.amount,
				CASE 
					WHEN vtiger_potential.amount < 10000 THEN 2  
					WHEN vtiger_potential.amount >= 10000 AND vtiger_potential.amount < 20000 THEN 3
					WHEN vtiger_potential.amount >= 20000 AND vtiger_potential.amount < 50000 THEN 4
					WHEN vtiger_potential.amount >= 50000 AND vtiger_potential.amount < 100000 THEN 5
					WHEN vtiger_potential.amount >= 100000 THEN 6
				END as prog_rating_value,
				CASE 
					WHEN vtiger_potential.amount < 10000 THEN vtiger_potential.potentialname + ' (< 10K)'  
					WHEN vtiger_potential.amount >= 10000 AND vtiger_potential.amount < 20000 THEN vtiger_potential.potentialname + ' (> 10K)'  
					WHEN vtiger_potential.amount >= 20000 AND vtiger_potential.amount < 50000 THEN vtiger_potential.potentialname + ' (> 20K)'  
					WHEN vtiger_potential.amount >= 50000 AND vtiger_potential.amount < 100000 THEN vtiger_potential.potentialname + ' (> 50K)'  
					WHEN vtiger_potential.amount >= 100000 THEN vtiger_potential.potentialname + ' (> 100K)'  
				END as prog_rating_title,
				potential_crmentity.modifiedtime as prog_rating_date 
				FROM vtiger_account 
				JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid AND vtiger_crmentity.deleted = 0
				JOIN vtiger_accountscf on vtiger_accountscf.accountid =  vtiger_account.accountid AND vtiger_accountscf.cf_762 = 'RC / CARP' 
				JOIN vtiger_accountbillads on vtiger_accountbillads.accountaddressid =  vtiger_account.accountid AND (  
				vtiger_accountbillads.bill_country like 'AR%' OR  
				vtiger_accountbillads.bill_country like 'BR%' OR  
				vtiger_accountbillads.bill_country like 'CL%' OR  
				vtiger_accountbillads.bill_country like 'CO%' OR  
				vtiger_accountbillads.bill_country like 'EC%' OR  
				vtiger_accountbillads.bill_country like 'UY%' OR
				vtiger_accountbillads.bill_country like 'PE%' OR  
				vtiger_accountbillads.bill_country like 'CR%' OR  
				vtiger_accountbillads.bill_country like 'MX%' OR  
				vtiger_accountbillads.bill_country like 'BO%' OR  
				vtiger_accountbillads.bill_country like 'PY%' OR
				vtiger_accountbillads.bill_country like 'PA%' OR
				vtiger_accountbillads.bill_country like 'VE%' OR
				vtiger_accountbillads.bill_country like 'NI%' OR
				vtiger_accountbillads.bill_country like 'GT%' OR
				vtiger_accountbillads.bill_country like 'GY%' OR
				vtiger_accountbillads.bill_country like 'GF%' )
				JOIN vtiger_potential on vtiger_potential.related_to = vtiger_account.accountid 
				JOIN vtiger_crmentity as potential_crmentity on potential_crmentity.crmid = vtiger_potential.potentialid AND potential_crmentity.deleted = 0
				WHERE (vtiger_account.rating = '' OR vtiger_account.rating = 'Active' OR vtiger_account.rating ='--None--' OR vtiger_account.rating ='Acquired') 
				AND vtiger_potential.sales_stage not in ( 'Closed Won' , 'Closed Lost') 
				AND (vtiger_accountscf.cf_927 IS NULL 
						OR vtiger_accountscf.cf_927='' 
						OR vtiger_accountscf.cf_927='1'  
						OR vtiger_accountscf.cf_927='35' 
						OR vtiger_accountscf.cf_927='36'   
						OR vtiger_accountscf.cf_927='Riattivato')
				 
_get_opportunita_closed_won_sql query= SELECT 
				vtiger_account.accountid, 
				vtiger_account.account_no,
				vtiger_account.accountname,
				vtiger_accountbillads.bill_country, 
				vtiger_account.rating,
				vtiger_accountscf.cf_927 as rating_attuale,
				vtiger_potential.potentialname + ' (Chiusa Vinta) '  as prog_rating_title,
				vtiger_potential.potentialid,
				vtiger_potential.potential_no,
				vtiger_potential.amount,
				1 as prog_rating_value ,
				potential_crmentity.createdtime as prog_rating_date 
				FROM vtiger_account 
				JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid AND vtiger_crmentity.deleted = 0
				JOIN vtiger_accountscf on vtiger_accountscf.accountid =  vtiger_account.accountid AND vtiger_accountscf.cf_762 = 'RC / CARP' 
				JOIN vtiger_accountbillads on vtiger_accountbillads.accountaddressid =  vtiger_account.accountid AND (  
				vtiger_accountbillads.bill_country like 'AR%' OR  
				vtiger_accountbillads.bill_country like 'BR%' OR  
				vtiger_accountbillads.bill_country like 'CL%' OR  
				vtiger_accountbillads.bill_country like 'CO%' OR  
				vtiger_accountbillads.bill_country like 'EC%' OR  
				vtiger_accountbillads.bill_country like 'UY%' OR
				vtiger_accountbillads.bill_country like 'PE%' OR  
				vtiger_accountbillads.bill_country like 'CR%' OR  
				vtiger_accountbillads.bill_country like 'MX%' OR  
				vtiger_accountbillads.bill_country like 'BO%' OR  
				vtiger_accountbillads.bill_country like 'PY%' OR
				vtiger_accountbillads.bill_country like 'PA%' OR
				vtiger_accountbillads.bill_country like 'VE%' OR
				vtiger_accountbillads.bill_country like 'NI%' OR
				vtiger_accountbillads.bill_country like 'GT%' OR
				vtiger_accountbillads.bill_country like 'GY%' OR
				vtiger_accountbillads.bill_country like 'GF%'  )
				JOIN vtiger_potential on vtiger_potential.related_to = vtiger_account.accountid 
				JOIN vtiger_crmentity as potential_crmentity on potential_crmentity.crmid = vtiger_potential.potentialid AND potential_crmentity.deleted = 0
				WHERE (vtiger_account.rating = '' OR vtiger_account.rating = 'Active' OR vtiger_account.rating ='--None--' OR vtiger_account.rating ='Acquired') 
				AND vtiger_potential.sales_stage = 'Closed Won'  
				AND (vtiger_accountscf.cf_927 IS NULL 
						OR vtiger_accountscf.cf_927='' 
						OR vtiger_accountscf.cf_927='1'  
						OR vtiger_accountscf.cf_927='35' 
						OR vtiger_accountscf.cf_927='36'   
						OR vtiger_accountscf.cf_927='Riattivato')
				 
_get_fiere_sql query= SELECT 
				vtiger_account.accountid, 
				vtiger_account.account_no,
				vtiger_account.accountname,
				vtiger_account.rating,
				vtiger_accountscf.cf_927 as rating_attuale,
				vtiger_activity.date_start as prog_rating_date ,
				vtiger_activity.subject as prog_rating_title ,
				2 as prog_rating_value
				FROM vtiger_account 
				JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid AND vtiger_crmentity.deleted = 0
				JOIN vtiger_accountscf on vtiger_accountscf.accountid =  vtiger_account.accountid AND vtiger_accountscf.cf_762 = 'RC / CARP' 
				JOIN vtiger_accountbillads on vtiger_accountbillads.accountaddressid =  vtiger_account.accountid AND (  
				vtiger_accountbillads.bill_country like 'AR%' OR  
				vtiger_accountbillads.bill_country like 'BR%' OR  
				vtiger_accountbillads.bill_country like 'CL%' OR  
				vtiger_accountbillads.bill_country like 'CO%' OR  
				vtiger_accountbillads.bill_country like 'EC%' OR  
				vtiger_accountbillads.bill_country like 'UY%' OR
				vtiger_accountbillads.bill_country like 'PE%' OR  
				vtiger_accountbillads.bill_country like 'CR%' OR  
				vtiger_accountbillads.bill_country like 'MX%' OR  
				vtiger_accountbillads.bill_country like 'BO%' OR  
				vtiger_accountbillads.bill_country like 'PY%' OR
				vtiger_accountbillads.bill_country like 'PA%' OR
				vtiger_accountbillads.bill_country like 'VE%' OR
				vtiger_accountbillads.bill_country like 'NI%' OR
				vtiger_accountbillads.bill_country like 'GT%' OR
				vtiger_accountbillads.bill_country like 'GY%' OR
				vtiger_accountbillads.bill_country like 'GF%'  )
				JOIN vtiger_seactivityrel ON vtiger_seactivityrel.crmid = vtiger_account.accountid
				JOIN vtiger_activity ON vtiger_activity.activityid = vtiger_seactivityrel.activityid AND vtiger_activity.activitytype ='Contatto - Fiera'
				JOIN vtiger_crmentity as activity_crmentity ON activity_crmentity.crmid = vtiger_activity.activityid  AND activity_crmentity.deleted = 0 
				WHERE (vtiger_account.rating = '' OR vtiger_account.rating = 'Active' OR vtiger_account.rating ='--None--' OR vtiger_account.rating ='Acquired') 
				AND (vtiger_accountscf.cf_927 IS NULL 
						OR vtiger_accountscf.cf_927='' 
						OR vtiger_accountscf.cf_927='1'  
						OR vtiger_accountscf.cf_927='35' 
						OR vtiger_accountscf.cf_927='36'   
						OR vtiger_accountscf.cf_927='Riattivato')
				 
_get_input_points_sql query= SELECT 
				vtiger_account.accountid, 
				vtiger_account.account_no,
				vtiger_account.accountname,
				vtiger_accountbillads.bill_country, 
				vtiger_account.rating,
				vtiger_accountscf.cf_927 as rating_attuale,
				vtiger_account.input_points as prog_rating_value ,
				vtiger_crmentity.modifiedtime as prog_rating_date 
				FROM vtiger_account 
				JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid AND vtiger_crmentity.deleted = 0
				JOIN vtiger_accountscf on vtiger_accountscf.accountid =  vtiger_account.accountid AND vtiger_accountscf.cf_762 = 'RC / CARP' 
				JOIN vtiger_accountbillads on vtiger_accountbillads.accountaddressid =  vtiger_account.accountid AND (  
				vtiger_accountbillads.bill_country like 'AR%' OR  
				vtiger_accountbillads.bill_country like 'BR%' OR  
				vtiger_accountbillads.bill_country like 'CL%' OR  
				vtiger_accountbillads.bill_country like 'CO%' OR  
				vtiger_accountbillads.bill_country like 'EC%' OR  
				vtiger_accountbillads.bill_country like 'UY%' OR
				vtiger_accountbillads.bill_country like 'PE%' OR  
				vtiger_accountbillads.bill_country like 'CR%' OR  
				vtiger_accountbillads.bill_country like 'MX%' OR  
				vtiger_accountbillads.bill_country like 'BO%' OR  
				vtiger_accountbillads.bill_country like 'PY%' OR
				vtiger_accountbillads.bill_country like 'PA%' OR
				vtiger_accountbillads.bill_country like 'VE%' OR
				vtiger_accountbillads.bill_country like 'NI%' OR
				vtiger_accountbillads.bill_country like 'GT%' OR
				vtiger_accountbillads.bill_country like 'GY%' OR
				vtiger_accountbillads.bill_country like 'GF%'  )
				WHERE (vtiger_account.rating = '' OR vtiger_account.rating = 'Active' OR vtiger_account.rating ='--None--' OR vtiger_account.rating ='Acquired') 
				AND (vtiger_accountscf.cf_927 IS NULL 
						OR vtiger_accountscf.cf_927='' 
						OR vtiger_accountscf.cf_927='1'  
						OR vtiger_accountscf.cf_927='35' 
						OR vtiger_accountscf.cf_927='36'   
						OR vtiger_accountscf.cf_927='Riattivato')
				AND vtiger_account.input_points IS NOT NULL
				AND vtiger_account.input_points <> 0 