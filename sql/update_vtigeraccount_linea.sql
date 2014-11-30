-- update_vtigeraccount_linea.sql
-- danzi.tn@20141130 nuova classificazione
-- CLEAR ALL
UPDATE vtiger_account
SET
vtiger_account.account_line = NULL;

-- UPDATE A - set account_line accordingly to CUSTOMER_CATEGORYDESC - CLIENTI ATTIVI <-> external_code IS NOT NULL #23945 items
-- ATTENZIONE: vado in LEFT JOIN su erp_temp_crm_aziende perchè ci sono circa 1070 accounts con external_code valorizzato ma senza corrispondenza in Semiramis, per questi recupero la linea da cf_762
-- 'RC / CARP', 'RD / DIST', 'RS / SAFE', 'RR / DIREZ'
UPDATE vtiger_account
SET vtiger_account.account_line = 
CASE 
	WHEN CUSTOMER_CATEGORYDESC ='CARP' THEN 'RC / CARP' 
	WHEN CUSTOMER_CATEGORYDESC ='GDO' THEN 'GD / GDO'
	WHEN CUSTOMER_CATEGORYDESC ='PROG' THEN '---'    
	WHEN CUSTOMER_CATEGORYDESC ='SAFE' THEN 'RS / SAFE'     
	WHEN CUSTOMER_CATEGORYDESC ='DIST' THEN 'RD / DIST'
    WHEN CUSTOMER_CATEGORYDESC ='DIPENDENTE INTERNO' OR 
         CUSTOMER_CATEGORYDESC ='FORNITORE' OR 
         CUSTOMER_CATEGORYDESC ='AGENTE' OR 
         CUSTOMER_CATEGORYDESC ='***ALTRO'
         THEN 'RR / DIREZ'
	WHEN CUSTOMER_CATEGORYDESC IS NULL THEN 
    	CASE 
            WHEN CHARINDEX( 'CARP', vtiger_accountscf.cf_762) > 0 THEN  'RC / CARP' 
            WHEN CHARINDEX( 'SAFE', vtiger_accountscf.cf_762) > 0 THEN  'RS / SAFE'
            WHEN CHARINDEX( 'DIST', vtiger_accountscf.cf_762) > 0 THEN  'RD / DIST'
            WHEN CHARINDEX( 'GDO', vtiger_accountscf.cf_762) > 0 THEN  'GD / GDO'
            WHEN CHARINDEX( 'PROG', vtiger_accountscf.cf_762) > 0 THEN  '---'
            WHEN CHARINDEX( 'DIPENDENTE INTERNO', vtiger_accountscf.cf_762) > 0 OR
                CHARINDEX( 'FORNITORE', vtiger_accountscf.cf_762) > 0 OR
                CHARINDEX( 'AGENTE', vtiger_accountscf.cf_762) > 0 OR
                CHARINDEX( '***ALTRO', vtiger_accountscf.cf_762) > 0 
                THEN  'RR / DIREZ'
            ELSE  '---'
		END 
	ELSE  '---'     
END	
FROM vtiger_account
JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_accountscf on vtiger_accountscf.accountid = vtiger_account.accountid 
LEFT JOIN erp_temp_crm_aziende on erp_temp_crm_aziende.BASE_NUMBER = vtiger_account.external_code
WHERE
vtiger_account.external_code IS NOT NULL AND vtiger_account.external_code <> '';

-- UPDATE B - set account_line accordingly to Account's Agent Line -  CLIENTI ATTIVI <-> external_code IS NOT NULL
-- per tutti quelli con external_code valorizzato e che non hanno ricevuto una line valorizzata dal giro precedente, vado a vedere il loro agente...se ce l'hanno
UPDATE vtiger_account
SET vtiger_account.account_line = 
CASE
 WHEN erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC IS NULL OR  
      erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'non definito' OR
	  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = ''   
	  THEN 
 		CASE 
            WHEN CHARINDEX( 'CARP', vtiger_accountscf.cf_762) > 0 THEN  'RC / CARP' 
            WHEN CHARINDEX( 'SAFE', vtiger_accountscf.cf_762) > 0 THEN  'RS / SAFE'
            WHEN CHARINDEX( 'DIST', vtiger_accountscf.cf_762) > 0 THEN  'RD / DIST'
            WHEN CHARINDEX( 'GDO', vtiger_accountscf.cf_762) > 0 THEN  'GD / GDO'
            WHEN CHARINDEX( 'PROG', vtiger_accountscf.cf_762) > 0 THEN  '---'
            WHEN CHARINDEX( 'DIPENDENTE INTERNO', vtiger_accountscf.cf_762) > 0 OR
                CHARINDEX( 'FORNITORE', vtiger_accountscf.cf_762) > 0 OR
                CHARINDEX( 'AGENTE', vtiger_accountscf.cf_762) > 0 OR
                CHARINDEX( '***ALTRO', vtiger_accountscf.cf_762) > 0 
                THEN  'RR / DIREZ'
            ELSE  '---'
		END 
 WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'CARP' THEN  'RC / CARP' 
 WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'SAFE' THEN  'RS / SAFE'
 WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'DIST' THEN  'RD / DIST'
 WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'INDUST' THEN 'RR / DIREZ'
 ELSE  '---'
END
FROM vtiger_account
JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid 
JOIN vtiger_users ON vtiger_users.id = accent.smownerid
JOIN erp_temp_crm_agenti ON vtiger_users.user_name = erp_temp_crm_agenti.AGENT_USERNAME -- OR vtiger_users.erp_code = erp_temp_crm_agenti.AGENT_NUMBER
WHERE
vtiger_account.external_code IS NOT NULL AND vtiger_account.external_code <> '' 
AND (vtiger_account.account_line = '---' OR vtiger_account.account_line IS NULL)
AND erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC IS NOT NULL 
AND erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC <> 'non definito';

-- UPDATE C - set account_line accordingly to Account's Agent Line -  CLIENTI POTENZIALI <-> external_code IS NULL #61048 items
-- per tutti i potenziali, quelli con external_code non valorizzato , vado a vedere il loro agente...se ce l'hanno...ci sono anche 28.866 RP / Prog
UPDATE vtiger_account
SET vtiger_account.account_line = 
CASE
 WHEN erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC IS NULL OR  
      erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'non definito' OR
	  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = ''   
	  THEN 
 		CASE 
            WHEN CHARINDEX( 'CARP', vtiger_accountscf.cf_762) > 0 THEN  'RC / CARP' 
            WHEN CHARINDEX( 'SAFE', vtiger_accountscf.cf_762) > 0 THEN  'RS / SAFE'
            WHEN CHARINDEX( 'DIST', vtiger_accountscf.cf_762) > 0 THEN  'RD / DIST'
            WHEN CHARINDEX( 'GDO', vtiger_accountscf.cf_762) > 0 THEN  'GD / GDO'
            WHEN CHARINDEX( 'PROG', vtiger_accountscf.cf_762) > 0 THEN  '---'
            WHEN CHARINDEX( 'DIPENDENTE INTERNO', vtiger_accountscf.cf_762) > 0 OR
                CHARINDEX( 'FORNITORE', vtiger_accountscf.cf_762) > 0 OR
                CHARINDEX( 'AGENTE', vtiger_accountscf.cf_762) > 0 OR
                CHARINDEX( '***ALTRO', vtiger_accountscf.cf_762) > 0 
                THEN  'RR / DIREZ'
            ELSE  '---'
		END 
 WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'CARP' THEN  'RC / CARP' 
 WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'SAFE' THEN  'RS / SAFE'
 WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'DIST' THEN  'RD / DIST'
 WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'INDUST' THEN 'RR / DIREZ'
 ELSE  '---'
END
FROM vtiger_account
JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid 
JOIN vtiger_users ON vtiger_users.id = accent.smownerid
LEFT JOIN erp_temp_crm_agenti ON vtiger_users.user_name = erp_temp_crm_agenti.AGENT_USERNAME -- OR vtiger_users.erp_code = erp_temp_crm_agenti.AGENT_NUMBER
WHERE
(vtiger_account.external_code IS NULL OR vtiger_account.external_code = '' );

-- UPDATE D tutti gli attuali RP / Prog sono in tutto 29383...ce ne sono 684 attivi, o in ogni caso con external code not null, ma hanno comunque tutti CARP, ok!
UPDATE vtiger_account
SET vtiger_account.account_line = 'RC / CARP'
FROM vtiger_account
JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid 
WHERE 
(vtiger_account.account_line = '---' OR vtiger_account.account_line  IS NULL)
AND vtiger_accountscf.cf_762 = 'RP / PROG';

-- UPDATE E - LISTA SAFE vtiger_account.accountname sono in tutto 919 dalla 
UPDATE vtiger_account
SET vtiger_account.account_line = 'RS / SAFE'
FROM vtiger_account
JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid 
JOIN tmp_accno on tmp_accno.accno = vtiger_account.account_no
WHERE vtiger_accountscf.cf_762 = 'RP / PROG';
