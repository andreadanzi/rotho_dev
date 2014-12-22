-- Selezione aziende che hanno agenti con linea non valorizzata

SELECT
vtiger_account.accountname,
vtiger_users.user_name,
vtiger_account.accountid,
vtiger_account.account_line,
erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC,
vtiger_accountscf.cf_927,
vtiger_account.external_code,
CASE
 WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'CARP' THEN  'RC / CARP' 
 WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'SAFE' THEN  'RS / SAFE'
 WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'DIST' THEN  'RD / DIST'
 WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'INDUST' THEN 'RR / DIREZ'
 ELSE  '---' 
END as newline
FROM vtiger_account
JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid  
JOIN vtiger_users ON vtiger_users.id = accent.smownerid
JOIN erp_temp_crm_agenti ON vtiger_users.user_name = erp_temp_crm_agenti.AGENT_USERNAME 
WHERE
CASE
 WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'CARP' THEN  'RC / CARP' 
 WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'SAFE' THEN  'RS / SAFE'
 WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'DIST' THEN  'RD / DIST'
 WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'INDUST' THEN 'RR / DIREZ'
 ELSE  '---' 
END <>vtiger_account.account_line
AND vtiger_account.external_code IS NOT NULL AND vtiger_account.external_code <>''
-- AND vtiger_accountscf.cf_927 = '1'
