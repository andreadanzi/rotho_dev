-- update_vtigeraccount_linea.sql
-- danzi.tn@20141130 nuova classificazione
-- CLEAR ALL
UPDATE vtiger_account
SET
vtiger_account.account_line = NULL;

-- UPDATE A - set account_line accordingly to Account's Agent Line -  CLIENTI ATTIVI <-> external_code IS NOT NULL
UPDATE vtiger_account
SET vtiger_account.account_line = 
CASE
 WHEN erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC IS NULL OR  
      erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'non definito' OR
	  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = ''   
	  THEN 
 		CASE 
            WHEN vtiger_accountscf.cf_762 = '' THEN  '---'
            WHEN vtiger_accountscf.cf_762 IS NULL THEN  '---'
            WHEN CHARINDEX( 'CARP', vtiger_accountscf.cf_762) > 0 THEN  'RC / CARP' 
            WHEN CHARINDEX( 'SAFE', vtiger_accountscf.cf_762) > 0 THEN  'RS / SAFE'
            WHEN CHARINDEX( 'DIST', vtiger_accountscf.cf_762) > 0 THEN  'RD / DIST'
            WHEN CHARINDEX( 'GDO', vtiger_accountscf.cf_762) > 0 THEN  'GD / GDO'
            WHEN CHARINDEX( 'PROG', vtiger_accountscf.cf_762) > 0 THEN  '---'
            WHEN CHARINDEX( 'DIPENDENTE INTERNO', vtiger_accountscf.cf_762) > 0 OR
                CHARINDEX( 'FORNITORE', vtiger_accountscf.cf_762) > 0 OR
                CHARINDEX( 'AGENTE', vtiger_accountscf.cf_762) > 0 OR
                CHARINDEX( 'ASS', vtiger_accountscf.cf_762) > 0 OR
                CHARINDEX( 'ORGANIZZAZIONE', vtiger_accountscf.cf_762) > 0 OR
                CHARINDEX( 'ALTRO', vtiger_accountscf.cf_762) > 0 
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
JOIN erp_temp_crm_agenti ON vtiger_users.user_name = erp_temp_crm_agenti.AGENT_USERNAME; -- OR vtiger_users.erp_code = erp_temp_crm_agenti.AGENT_NUMBER


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

-- UPDATE F - LISTA SAFE vtiger_account.accountname sono in tutto 919 dalla 
UPDATE vtiger_account
SET vtiger_account.account_line = 
CASE 
    WHEN vtiger_accountscf.cf_762 = '' THEN  '---'
    WHEN vtiger_accountscf.cf_762 IS NULL THEN  '---'
    WHEN CHARINDEX( 'CARP', vtiger_accountscf.cf_762) > 0 THEN  'RC / CARP' 
    WHEN CHARINDEX( 'SAFE', vtiger_accountscf.cf_762) > 0 THEN  'RS / SAFE'
    WHEN CHARINDEX( 'DIST', vtiger_accountscf.cf_762) > 0 THEN  'RD / DIST'
    WHEN CHARINDEX( 'GDO', vtiger_accountscf.cf_762) > 0 THEN  'GD / GDO'
    WHEN CHARINDEX( 'DIPENDENTE INTERNO', vtiger_accountscf.cf_762) > 0 OR
        CHARINDEX( 'FORNITORE', vtiger_accountscf.cf_762) > 0 OR
        CHARINDEX( 'AGENTE', vtiger_accountscf.cf_762) > 0 OR
        CHARINDEX( 'ASS', vtiger_accountscf.cf_762) > 0 OR
        CHARINDEX( 'ORGANIZZAZIONE', vtiger_accountscf.cf_762) > 0 OR
        CHARINDEX( 'ALTRO', vtiger_accountscf.cf_762) > 0 
        THEN  'RR / DIREZ'
    ELSE  '---'
END 
FROM vtiger_account
JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid 
WHERE 
(vtiger_account.account_line = '---' OR vtiger_account.account_line  IS NULL)