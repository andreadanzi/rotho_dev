SELECT TOP 100
	vtiger_account.account_line,  
	vtiger_account.account_client_type,  
	vtiger_account.account_main_activity,  
	vtiger_account.account_sec_activity,  
	vtiger_account.account_brand, 
	vtiger_account.area_intervento,
	vtiger_accountscf.cf_1232,
	case 
		when vtiger_accountscf.cf_1232 = '' then '---'
		when vtiger_accountscf.cf_1232 = NULL then '---'
		when vtiger_accountscf.cf_1232 = '1' then 'Locale'
		when vtiger_accountscf.cf_1232 = '2' then 'Nazionale'
		when vtiger_accountscf.cf_1232 = '3' then 'Internazionale'
	end
	FROM vtiger_account
	join vtiger_crmentity on  vtiger_account.accountid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0
	join vtiger_accountscf on  vtiger_accountscf.accountid = vtiger_account.accountid
	join vtiger_accountbillads on  vtiger_accountbillads.accountaddressid = vtiger_account.accountid
	WHERE
	vtiger_accountscf.cf_1232 IS NOT NULL AND 	vtiger_accountscf.cf_1232 <>''
	
	
	UPDATE
	vtiger_account
	SET
	vtiger_account.area_intervento = 
	case 
		when vtiger_accountscf.cf_1232 = '' then '---'
		when vtiger_accountscf.cf_1232 = NULL then '---'
		when vtiger_accountscf.cf_1232 = '1' then 'Locale'
		when vtiger_accountscf.cf_1232 = '2' then 'Nazionale'
		when vtiger_accountscf.cf_1232 = '3' then 'Internazionale'
	end
	FROM vtiger_account
	join vtiger_crmentity on  vtiger_account.accountid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0
	join vtiger_accountscf on  vtiger_accountscf.accountid = vtiger_account.accountid
	join vtiger_accountbillads on  vtiger_accountbillads.accountaddressid = vtiger_account.accountid
	WHERE
	vtiger_accountscf.cf_1232 IS NOT NULL AND 	vtiger_accountscf.cf_1232 <>''