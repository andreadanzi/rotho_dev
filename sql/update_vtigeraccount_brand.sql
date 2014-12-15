-- update_vtigeraccount_linea.sql
-- danzi.tn@20141130 nuova classificazione
-- CLEAR ALL
UPDATE vtiger_account
SET
vtiger_account.account_brand = NULL;

-- UPDATE A - set account_line accordingly to Account's Agent Line -  CLIENTI ATTIVI <-> external_code IS NOT NULL
UPDATE vtiger_account
SET vtiger_account.account_brand = 'ROTHOBLAAS'
FROM vtiger_account
JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid 
WHERE vtiger_account.account_line = 'RC / CARP' OR vtiger_account.account_line = 'RS / SAFE' ;

UPDATE vtiger_account
SET vtiger_account.account_brand = 'ROTHOBLAAS'
FROM vtiger_account
JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid 
JOIN vtiger_accountbillads on  vtiger_accountbillads.accountaddressid =vtiger_account.accountid 
WHERE vtiger_account.account_line = 'RD / DIST' AND
(vtiger_accountbillads.bill_country = 'DE' OR vtiger_accountbillads.bill_country = 'CH');


UPDATE vtiger_account
SET vtiger_account.account_brand = 'HOLZTECHNIC'
FROM vtiger_account
JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid 
WHERE vtiger_account.account_line = 'RD / DIST' ;

UPDATE vtiger_account
SET vtiger_account.account_brand = 'HOLZTECHNIC'
FROM vtiger_account
JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid 
JOIN vtiger_accountbillads on  vtiger_accountbillads.accountaddressid =vtiger_account.accountid 
WHERE vtiger_account.account_client_type = 'RIVENDITORE' AND
(vtiger_accountbillads.bill_country = 'PT' OR vtiger_accountbillads.bill_country = 'ES');