SELECT 
			vtiger_account.accountid, 
			vtiger_account.account_no,
			vtiger_account.accountname,
			vtiger_targets.targetname,
			vtiger_targetscf.cf_1006 as codice_corso_target,
			vtiger_account.rating,
			vtiger_accountscf.cf_927 as rating_attuale,
			vtiger_campaign.campaignname,
			vtiger_campaignscf.cf_745 as data_corso_campagna,
			vtiger_campaignscf.cf_742 as codice_corso_campagna,
			vtiger_campaignscf.cf_759 as codice_fatturazione, -- Per i download = 'ND'
			CASE WHEN vtiger_campaignscf.cf_759 IN ('RFCACN','RFCAPC','RHCA') THEN 2  ELSE 1 END as prog_rating_value ,
			count(*) as targetsum
			FROM vtiger_account 
			JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid AND vtiger_crmentity.deleted = 0
			JOIN vtiger_accountscf on vtiger_accountscf.accountid =  vtiger_account.accountid AND vtiger_accountscf.cf_762 = 'RP / PROG' 
			JOIN vtiger_accountbillads on vtiger_accountbillads.accountaddressid =  vtiger_account.accountid AND vtiger_accountbillads.bill_country like 'IT%'
			JOIN vtiger_crmentityrel on vtiger_crmentityrel.crmid = vtiger_accountscf.accountid AND vtiger_crmentityrel.relmodule = 'Targets'
			JOIN vtiger_targets on vtiger_targets.targetsid = vtiger_crmentityrel.relcrmid
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
			vtiger_targets.targetname,
			vtiger_targetscf.cf_1006 ,
			vtiger_account.rating,
			vtiger_accountscf.cf_927,
			vtiger_campaign.campaignname,
			vtiger_campaignscf.cf_745,
			vtiger_campaignscf.cf_742,
			vtiger_campaignscf.cf_759,
			CASE WHEN vtiger_campaignscf.cf_759 IN ('RFCACN','RFCAPC','RHCA') THEN 2  ELSE 1 END
			order by vtiger_account.accountid"