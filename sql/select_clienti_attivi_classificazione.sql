-- danzi.tn@20141126 nuova classificazione
-- sono 23.205
SELECT 
vtiger_account.accountid,
vtiger_account.accountname,
vtiger_account.account_no,
vtiger_account.external_code,
vtiger_accountscf.cf_762,
erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC,
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
erp_temp_crm_agenti.AGENT_USERNAME
FROM vtiger_accountscf
JOIN vtiger_crmentity accent on vtiger_accountscf.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_account on vtiger_account.accountid = vtiger_accountscf.accountid 
JOIN vtiger_users ON vtiger_users.id = accent.smownerid
JOIN erp_temp_crm_agenti ON vtiger_users.user_name = erp_temp_crm_agenti.AGENT_USERNAME -- OR vtiger_users.erp_code = erp_temp_crm_agenti.AGENT_NUMBER

WHERE
vtiger_account.external_code IS NOT NULL 
AND vtiger_account.external_code <> '' 
AND vtiger_accountscf.cf_762 <> 'RP / PROG' 
order by vtiger_account.accountid

-- questi sono 22.182 - 1.029 hanno null
SELECT 
vtiger_account.accountid,
vtiger_account.accountname,
vtiger_account.account_no,
vtiger_account.external_code,
vtiger_accountscf.cf_762,
erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC,
erp_temp_crm_aziende.CUSTOMER_CATEGORY,
erp_temp_crm_aziende.CUSTOMER_CATEGORYDESC,
erp_temp_crm_aziende.CUSTOMER_TYPE,
erp_temp_crm_aziende.CUSTOMER_TYPEDESC,
vtiger_accountscf.cf_903,
case 
	when CUSTOMER_CATEGORYDESC ='CARP' then 'CARP' 
	when CUSTOMER_CATEGORYDESC ='GDO' then 'DIST' 
	when CUSTOMER_CATEGORYDESC ='Classification not defined.' then '---' 
	when CUSTOMER_CATEGORYDESC ='ORGANIZZAZIONE' then '---'  
	when CUSTOMER_CATEGORYDESC IS NULL then '---'  
	when CUSTOMER_CATEGORYDESC ='***ALTRO' then '---'  
	when CUSTOMER_CATEGORYDESC ='DIPENDENTE INTERNO' then '---'    
	when CUSTOMER_CATEGORYDESC ='PROG' then 'CARP'    
	when CUSTOMER_CATEGORYDESC ='SAFE' then 'SAFE'    
	when CUSTOMER_CATEGORYDESC ='FORNITORE' then '---'    
	when CUSTOMER_CATEGORYDESC ='DIST' then 'DIST'    
	when CUSTOMER_CATEGORYDESC ='da definire' then '---'    
	when CUSTOMER_CATEGORYDESC ='AGENTE' then '---'      
end	as new_line, 
vtiger_account.account_line, --CUSTOMER_CATEGORYDESC
vtiger_account.account_client_type, --PROGETTISTA
vtiger_account.account_main_activity, --PROGANTICAD
vtiger_account.account_brand,
accent.smownerid,
vtiger_users.id,
vtiger_users.first_name,
vtiger_users.last_name,

erp_temp_crm_agenti.AGENT_NUMBER, 
erp_temp_crm_agenti.AGENT_USERNAME
FROM vtiger_accountscf
JOIN vtiger_crmentity accent on vtiger_accountscf.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_account on vtiger_account.accountid = vtiger_accountscf.accountid 
JOIN vtiger_users ON vtiger_users.id = accent.smownerid
JOIN erp_temp_crm_agenti ON vtiger_users.user_name = erp_temp_crm_agenti.AGENT_USERNAME -- OR vtiger_users.erp_code = erp_temp_crm_agenti.AGENT_NUMBER
LEFT JOIN erp_temp_crm_aziende on erp_temp_crm_aziende.BASE_NUMBER = vtiger_account.external_code
WHERE
vtiger_account.external_code IS NOT NULL 
-- AND erp_temp_crm_aziende.BASE_NUMBER IS NULL
AND vtiger_account.external_code <> '' 
AND vtiger_accountscf.cf_762 <> 'RP / PROG' 
order by vtiger_account.accountid

-- sono 23.945
SELECT vtiger_account.accountid,
vtiger_account.accountname,
vtiger_account.account_no,
vtiger_account.external_code,
case 
	when CUSTOMER_CATEGORYDESC ='CARP' then 'CARP' 
	when CUSTOMER_CATEGORYDESC ='GDO' then 'DIST' 
	when CUSTOMER_CATEGORYDESC ='Classification not defined.' then '---' 
	when CUSTOMER_CATEGORYDESC ='ORGANIZZAZIONE' then '---'  
	when CUSTOMER_CATEGORYDESC IS NULL then '---'  
	when CUSTOMER_CATEGORYDESC ='***ALTRO' then '---'  
	when CUSTOMER_CATEGORYDESC ='DIPENDENTE INTERNO' then '---'    
	when CUSTOMER_CATEGORYDESC ='PROG' then 'CARP'    
    when CUSTOMER_CATEGORYDESC ='INDUST' then '---'  
	when CUSTOMER_CATEGORYDESC ='SAFE' then 'SAFE'    
	when CUSTOMER_CATEGORYDESC ='FORNITORE' then '---'    
	when CUSTOMER_CATEGORYDESC ='DIST' then 'DIST'    
	when CUSTOMER_CATEGORYDESC ='da definire' then '---'    
	when CUSTOMER_CATEGORYDESC ='AGENTE' then '---'      
end	as new_line

FROM vtiger_account
JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
LEFT JOIN erp_temp_crm_aziende on erp_temp_crm_aziende.BASE_NUMBER = vtiger_account.external_code
WHERE
vtiger_account.external_code IS NOT NULL 
AND vtiger_account.external_code <> '' 
order by vtiger_account.accountid



--- UPDATE A
UPDATE vtiger_account
SET vtiger_account.account_line = 
case 
	when CUSTOMER_CATEGORYDESC ='CARP' then 'CARP' 
	when CUSTOMER_CATEGORYDESC ='GDO' then 'DIST' 
	when CUSTOMER_CATEGORYDESC ='Classification not defined.' then '---' 
	when CUSTOMER_CATEGORYDESC ='ORGANIZZAZIONE' then '---'  
	when CUSTOMER_CATEGORYDESC IS NULL then '---'  
	when CUSTOMER_CATEGORYDESC ='***ALTRO' then '---'  
	when CUSTOMER_CATEGORYDESC ='DIPENDENTE INTERNO' then '---'    
	when CUSTOMER_CATEGORYDESC ='PROG' then 'CARP'    
	when CUSTOMER_CATEGORYDESC ='SAFE' then 'SAFE'    
    when CUSTOMER_CATEGORYDESC ='INDUST' then '---'  
	when CUSTOMER_CATEGORYDESC ='FORNITORE' then '---'    
	when CUSTOMER_CATEGORYDESC ='DIST' then 'DIST'    
	when CUSTOMER_CATEGORYDESC ='da definire' then '---'    
	when CUSTOMER_CATEGORYDESC ='AGENTE' then '---'      
end	

FROM vtiger_account
JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
LEFT JOIN erp_temp_crm_aziende on erp_temp_crm_aziende.BASE_NUMBER = vtiger_account.external_code
WHERE
vtiger_account.external_code IS NOT NULL 
AND vtiger_account.external_code <> '' 


--
SELECT 
vtiger_account.accountid,
vtiger_account.accountname,
vtiger_account.account_no,
vtiger_account.external_code,
vtiger_accountscf.cf_762,
erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC,
vtiger_account.account_line, -- erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC
vtiger_account.account_client_type, --PROGETTISTA
vtiger_account.account_main_activity, --PROGANTICAD
vtiger_account.account_brand,
accent.smownerid,
vtiger_users.id,
vtiger_users.first_name,
vtiger_users.last_name,

erp_temp_crm_agenti.AGENT_NUMBER, 
erp_temp_crm_agenti.AGENT_USERNAME
FROM vtiger_account
JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid 
JOIN vtiger_users ON vtiger_users.id = accent.smownerid
JOIN erp_temp_crm_agenti ON vtiger_users.user_name = erp_temp_crm_agenti.AGENT_USERNAME -- OR vtiger_users.erp_code = erp_temp_crm_agenti.AGENT_NUMBER
WHERE
vtiger_account.external_code IS NOT NULL 
AND vtiger_account.external_code <> '' 
AND (vtiger_account.account_line = '---' OR vtiger_account.account_line IS NULL)
AND erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC IS NOT NULL 
AND erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC <> 'non definito'
order by vtiger_account.accountid

-- UPDATE B

UPDATE vtiger_account
SET vtiger_account.account_line = erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC
FROM vtiger_account
JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid 
JOIN vtiger_users ON vtiger_users.id = accent.smownerid
JOIN erp_temp_crm_agenti ON vtiger_users.user_name = erp_temp_crm_agenti.AGENT_USERNAME -- OR vtiger_users.erp_code = erp_temp_crm_agenti.AGENT_NUMBER
WHERE
vtiger_account.external_code IS NOT NULL 
AND vtiger_account.external_code <> '' 
AND (vtiger_account.account_line = '---' OR vtiger_account.account_line IS NULL)
AND erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC IS NOT NULL 
AND erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC <> 'non definito'