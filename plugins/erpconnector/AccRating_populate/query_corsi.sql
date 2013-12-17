SELECT DISTINCT
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
			CASE WHEN vtiger_campaignscf.cf_759 IN ('RFCACN','RFCAPC','RHCA','RBCACM') THEN 2  ELSE 1 END as prog_rating_value ,
			count(*) as targetsum
			FROM vtiger_account 
			JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid AND vtiger_crmentity.deleted = 0
			JOIN vtiger_accountscf on vtiger_accountscf.accountid =  vtiger_account.accountid AND vtiger_accountscf.cf_762 = 'RP / PROG' 
			JOIN vtiger_accountbillads on vtiger_accountbillads.accountaddressid =  vtiger_account.accountid AND ( vtiger_accountbillads.bill_country like 'IT%' OR  vtiger_accountbillads.bill_country like 'ES%' OR  vtiger_accountbillads.bill_country like 'PT%' )
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
			CASE WHEN vtiger_campaignscf.cf_759 IN ('RFCACN','RFCAPC','RHCA') THEN 2  ELSE 1 END
			order by vtiger_account.accountid


	
			SELECT 
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
			CASE WHEN vtiger_campaignscf.cf_759 IN ('RFCACN','RFCAPC','RHCA','RBCACM') THEN 2  ELSE 1 END as prog_rating_value ,
			count(*) as targetsum
			FROM vtiger_account 
			JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid AND vtiger_crmentity.deleted = 0
			JOIN vtiger_accountscf on vtiger_accountscf.accountid =  vtiger_account.accountid AND vtiger_accountscf.cf_762 = 'RP / PROG' 
			JOIN vtiger_accountbillads on vtiger_accountbillads.accountaddressid =  vtiger_account.accountid AND ( vtiger_accountbillads.bill_country like 'IT%' OR  vtiger_accountbillads.bill_country like 'ES%' OR  vtiger_accountbillads.bill_country like 'PT%' )
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
			CASE WHEN vtiger_campaignscf.cf_759 IN ('RFCACN','RFCAPC','RHCA') THEN 2  ELSE 1 END
			order by vtiger_account.accountid
			
			
			
			
-- Fiere

SELECT 
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
				JOIN vtiger_accountbillads on vtiger_accountbillads.accountaddressid =  vtiger_account.accountid AND ( vtiger_accountbillads.bill_country like 'IT%' OR  vtiger_accountbillads.bill_country like 'ES%' OR  vtiger_accountbillads.bill_country like 'PT%' )
				JOIN vtiger_seactivityrel ON vtiger_seactivityrel.crmid = vtiger_account.accountid
				JOIN vtiger_activity ON vtiger_activity.activityid = vtiger_seactivityrel.activityid AND vtiger_activity.activitytype = 'Contatto - Fiera'
				JOIN vtiger_crmentity as activity_crmentity ON activity_crmentity.crmid = vtiger_activity.activityid  AND activity_crmentity.deleted = 0 
				WHERE (vtiger_account.rating = '' OR vtiger_account.rating = 'Active' OR vtiger_account.rating ='--None--' OR vtiger_account.rating ='Acquired') 
				AND (vtiger_accountscf.cf_927 IS NULL 
						OR vtiger_accountscf.cf_927='' 
						OR vtiger_accountscf.cf_927='1'  
						OR vtiger_accountscf.cf_927='35' 
						OR vtiger_accountscf.cf_927='36'   
						OR vtiger_accountscf.cf_927='Riattivato')
			
			
			
			
select 
					DATEDIFF(s, '1970-01-01 00:00:00', vtiger_crmentity.createdtime ) as tstamp,
					vtiger_leaddetails.leadid as uid,
					vtiger_leaddetails.firstname + '  ' + vtiger_leaddetails.lastname as name,
					vtiger_leaddetails.firstname as first_name,
					vtiger_leaddetails.lastname as last_name,
					vtiger_leaddetails.email,
					vtiger_leadaddress.phone,
					vtiger_leadaddress.mobile,
					vtiger_leadsubdetails.website as www,
					vtiger_leadaddress.lane as address,
					vtiger_leaddetails.company,
					vtiger_leadaddress.city,
					vtiger_leadaddress.code as zip,
					vtiger_leadaddress.state as region,
					vtiger_leadaddress.country,
					vtiger_crmentity.description,
					vtiger_leadaddress.fax,
					'Webform' as type 
					, vtiger_leaddetails.leadsource 
					, 'Held' as leadstatus
					, '167' as assigned_user_id
					, vtiger_crmentity.createdtime as  insertdate 
					, 'form_consulenza_web' as idtarget
					, 'Formulario richiesta consulenza: Soluzioni per strutture in legno - rothoblaas' as page_title 
					, 'ND' as location 
					, 'ND' as codfatt 
					, '1' AS cf_807 
					, 'Web' as cf_757 
					, 'ND' as cf_737
					, vtiger_leadscf.cf_758 as title 
					from vtiger_leaddetails
					join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_leaddetails.leadid and vtiger_crmentity.deleted =0 AND vtiger_leaddetails.converted = 0 
					join vtiger_leadscf on vtiger_leadscf.leadid = vtiger_leaddetails.leadid
					join vtiger_leadaddress on vtiger_leadaddress.leadaddressid = vtiger_leaddetails.leadid
					join vtiger_leadsubdetails on vtiger_leadsubdetails.leadsubscriptionid = vtiger_leaddetails.leadid
					WHERE vtiger_leaddetails.leadsource like 'Fiera%'
					AND vtiger_leadscf.cf_808 = 0			