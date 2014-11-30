-- danzi.tn@20141126 nuova classificazione
-- tutti i progettististi
SELECT 
vtiger_account.accountid,
vtiger_account.accountname,
vtiger_account.account_line, -- = 'CARP',
vtiger_account.account_client_type -- = 'PROGETTISTA'
FROM vtiger_account
JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid 
WHERE vtiger_accountscf.cf_762 = 'RP / PROG' 
-- UPDATE C tutti i Prog
UPDATE vtiger_account
SET vtiger_account.account_line = 'CARP',
SET vtiger_account.account_client_type = 'PROGETTISTA'
FROM vtiger_account
JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid 
-- JOIN tmp_accno on tmp_accno.accno = vtiger_account.account_no
WHERE vtiger_accountscf.cf_762 = 'RP / PROG'

-- UPDATE D - LISTA SAFE vtiger_account.accountname 
UPDATE vtiger_account
SET vtiger_account.account_line = 'SAFE',
vtiger_account.account_client_type = 'PROGETTISTA'
FROM vtiger_account
JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid 
JOIN tmp_accno on tmp_accno.accno = vtiger_account.account_no
WHERE vtiger_accountscf.cf_762 = 'RP / PROG'

SELECT 
vtiger_account.accountid,
vtiger_account.accountname,
vtiger_accountscf.cf_762,
 vtiger_accountscf.cf_903,
vtiger_account.account_line, --SAFE
vtiger_account.account_client_type, --PROGETTISTA
vtiger_account.account_main_activity, --PROGANTICAD
vtiger_account.account_brand,
accent.smownerid,
vtiger_users.id,
vtiger_users.first_name,
vtiger_users.last_name,
erp_temp_crm_agenti.AGENT_NUMBER, 
erp_temp_crm_agenti.AGENT_USERNAME,
erp_temp_crm_agenti.AGENT_LINEAVENDITA,
erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC
FROM vtiger_accountscf
JOIN vtiger_crmentity accent on vtiger_accountscf.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_account on vtiger_account.accountid = vtiger_accountscf.accountid 
JOIN tmp_accno on tmp_accno.accno = vtiger_account.account_no
LEFT JOIN vtiger_users ON vtiger_users.id = accent.smownerid
LEFT JOIN erp_temp_crm_agenti ON vtiger_users.user_name = erp_temp_crm_agenti.AGENT_USERNAME -- OR vtiger_users.erp_code = erp_temp_crm_agenti.AGENT_NUMBER
WHERE vtiger_accountscf.cf_762 = 'RP / PROG' 
order by vtiger_account.accountid