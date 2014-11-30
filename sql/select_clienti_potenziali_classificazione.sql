-- danzi.tn@20141126 nuova classificazione
SELECT 
vtiger_account.accountid,
vtiger_account.accountname,
vtiger_account.account_no,
vtiger_account.external_code,
vtiger_accountscf.cf_762,
 vtiger_accountscf.cf_903,
vtiger_account.account_line, --erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC
vtiger_account.account_client_type, 
vtiger_account.account_main_activity,
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
JOIN vtiger_users ON vtiger_users.id = accent.smownerid
JOIN erp_temp_crm_agenti ON vtiger_users.user_name = erp_temp_crm_agenti.AGENT_USERNAME -- OR vtiger_users.erp_code = erp_temp_crm_agenti.AGENT_NUMBER

WHERE
(vtiger_account.external_code IS NULL OR vtiger_account.external_code = '' )
AND 
vtiger_accountscf.cf_762 <> 'RP / PROG' AND
AGENT_LINEAVENDITA IS NOT NULL
order by vtiger_account.accountid

-- SELECT  61.048 
SELECT 
vtiger_account.account_line,
vtiger_accountscf.cf_762,
CASE
 when erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC IS NULL THEN '---'
 when erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC ='' THEN '---'
 ELSE  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC
END
FROM vtiger_account
JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid 
JOIN vtiger_users ON vtiger_users.id = accent.smownerid
JOIN erp_temp_crm_agenti ON vtiger_users.user_name = erp_temp_crm_agenti.AGENT_USERNAME -- OR vtiger_users.erp_code = erp_temp_crm_agenti.AGENT_NUMBER
WHERE
(vtiger_account.external_code IS  NULL OR vtiger_account.external_code = '' ) 
AND erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC IS NOT NULL 
AND erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC <> 'non definito'

ORDER BY vtiger_accountscf.cf_762

-- UPDATE C
UPDATE vtiger_account
SET vtiger_account.account_line = 
CASE
 when erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC IS NULL THEN '---'
 when erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC ='' THEN '---'
 ELSE  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC
END
FROM vtiger_account
JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid 
JOIN vtiger_users ON vtiger_users.id = accent.smownerid
JOIN erp_temp_crm_agenti ON vtiger_users.user_name = erp_temp_crm_agenti.AGENT_USERNAME -- OR vtiger_users.erp_code = erp_temp_crm_agenti.AGENT_NUMBER
WHERE
(vtiger_account.external_code IS  NULL OR vtiger_account.external_code = '' ) 
AND erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC IS NOT NULL 
AND erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC <> 'non definito'