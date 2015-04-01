SELECT top 100 
vtiger_account.accountname,
vtiger_account.account_line,
vtiger_account.sem_importflag,
vtiger_account.sem_importdate,
vtiger_account.external_code,
erp_temp_crm_aziende.INSERTDATE,
erp_temp_crm_aziende.BASE_NUMBER
FROM
vtiger_account
JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid and vtiger_crmentity.deleted=0
LEFT JOIN erp_temp_crm_aziende ON erp_temp_crm_aziende.BASE_NUMBER = vtiger_account.external_code
WHERE 
vtiger_account.external_code IS NOT NULL
AND vtiger_account.external_code <> ''
-- AND erp_temp_crm_aziende.KEYID is NOT NULL
AND vtiger_account.sem_importflag <>'NN'
AND vtiger_account.accountname = 'CAROLLI SRL'
ORDER BY vtiger_account.sem_importdate DESC