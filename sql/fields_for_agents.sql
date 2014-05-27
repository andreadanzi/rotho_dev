-- danzi.tn@20140206 procedura cambio codici nazione
-- LEADS - ATTENZIONE  il match per la selezione non considera differenze dovute ad accenti tipo Canicattì diverso da Canicatti'
SELECT 
			vtiger_crmentity.crmid, 
			vtiger_crmentity.setype,
			vtiger_crmentity.smownerid, 
			vtiger_crmentity.smcreatorid, 
			vtiger_crmentity.createdtime, 
			vtiger_crmentity.description,
			vtiger_leaddetails.company,
			vtiger_leaddetails.email,
			vtiger_leadaddress.city,
			vtiger_leadaddress.code,
			vtiger_leadaddress.country,
			vtiger_leadaddress.state
			FROM vtiger_crmentity 
			JOIN vtiger_leaddetails on vtiger_leaddetails.leadid = vtiger_crmentity.crmid and vtiger_leaddetails.converted = 0
			JOIN  vtiger_leadaddress on vtiger_leadaddress.leadaddressid = vtiger_crmentity.crmid AND vtiger_leadaddress.country like 'IT%'
			WHERE 
			vtiger_crmentity.deleted = 0 and
			vtiger_crmentity.setype = 'Leads' and
			vtiger_crmentity.smownerid in (0,9,167)
-- CONTACTS
SELECT 
vtiger_crmentity.crmid, 
vtiger_crmentity.smcreatorid, 
vtiger_crmentity.smownerid, 
vtiger_crmentity.createdtime, 
vtiger_crmentity.description,
vtiger_contactdetails.firstname,
vtiger_contactdetails.lastname,
vtiger_contactdetails.email,
vtiger_contactdetails.accountid,
vtiger_accountbillads.bill_country,
vtiger_accountbillads.bill_state,
vtiger_accountbillads.bill_code,
vtiger_accountbillads.bill_city
FROM vtiger_crmentity 
JOIN vtiger_contactdetails on vtiger_contactdetails.contactid = vtiger_crmentity.crmid
JOIN vtiger_accountbillads on vtiger_accountbillads.accountaddressid = vtiger_contactdetails.accountid
JOIN vtiger_crmentity as acc_entity on acc_entity.crmid = vtiger_accountbillads.accountaddressid and acc_entity.deleted = 0
WHERE vtiger_crmentity.deleted = 0 and
vtiger_crmentity.setype = 'Contacts'
and
vtiger_crmentity.smownerid in (0,9,167)


-- ACCOUNTS
SELECT 
vtiger_crmentity.crmid, 
vtiger_crmentity.smownerid, 
vtiger_crmentity.smcreatorid, 
vtiger_crmentity.createdtime, 
vtiger_crmentity.description,
vtiger_account.accountname,
vtiger_account.email1,
vtiger_accountbillads.bill_country,
vtiger_accountbillads.bill_state,
vtiger_accountbillads.bill_code,
vtiger_accountbillads.bill_city
FROM vtiger_crmentity 
JOIN vtiger_account on vtiger_account.accountid = vtiger_crmentity.crmid
JOIN vtiger_accountbillads on vtiger_accountbillads.accountaddressid = vtiger_crmentity.crmid
WHERE vtiger_crmentity.deleted = 0 AND
vtiger_crmentity.setype in ('Leads','Accounts','Contacts')
AND
vtiger_crmentity.smownerid in (0,9,167)
order by setype

SELECT Agente,  vtiger_users.id ,  COUNT(Agente) 
				FROM tmp_assegnazione_agenti 
				JOIN vtiger_users ON vtiger_users.user_name = Agente
				WHERE Comune = 'Montecavolo' 
				GROUP BY Agente, vtiger_users.id

UPDATE
			vtiger_crmentity
			SET 
			vtiger_crmentity.smownerid = ,
			vtiger_crmentity.modifiedby = , 
			vtiger_crmentity.modifiedtime = GETDATE()
			WHERE 
			vtiger_crmentity.crmid = ".$entity_id." AND
			vtiger_crmentity.smownerid = ".$previous_owner_id."