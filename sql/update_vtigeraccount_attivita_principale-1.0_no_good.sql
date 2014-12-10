-- update_vtigeraccount_attivita_principale.sql
-- danzi.tn@20141130 nuova classificazione
-- CLEAR ALL ATTENZIONE ...verificare vtiger_account.account_client_type = 'PROGETTISTA'
UPDATE vtiger_account
SET
vtiger_account.account_client_type = NULL,
vtiger_account.account_main_activity = NULL,
vtiger_account.account_sec_activity = NULL;

-- PEr tutti i vecchi 'RP / PROG' metto progettista
UPDATE vtiger_account
SET vtiger_account.account_client_type = 'PROGETTISTA'
FROM vtiger_account
JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid 
WHERE vtiger_accountscf.cf_762 = 'RP / PROG';

UPDATE vtiger_account
SET vtiger_account.account_client_type = 'RIVENDITORE'
FROM vtiger_account
JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid 
WHERE vtiger_accountscf.cf_762 = 'RD / DIST';

UPDATE vtiger_account
SET vtiger_account.account_client_type = 'UTILIZZATORE'
FROM vtiger_account
JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid 
WHERE vtiger_accountscf.cf_762 = 'RC / CARP';

-- UPDATE F1 - Main activity without selected '---'
UPDATE vtiger_account
SET
vtiger_account.account_client_type = tmp_type_from_subcat.account_client_type,
vtiger_account.account_main_activity = tmp_type_from_subcat.account_main_activity

FROM vtiger_account
 JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
 JOIN vtiger_accountscf on vtiger_accountscf.accountid = vtiger_account.accountid 
 JOIN tmp_type_from_subcat on 
 
CHARINDEX(tmp_type_from_subcat.subcat ,
CASE 
WHEN tmp_type_from_subcat.subfield = 'cf_894' THEN  vtiger_accountscf.cf_894 
WHEN tmp_type_from_subcat.subfield = 'cf_902' THEN  vtiger_accountscf.cf_902
WHEN tmp_type_from_subcat.subfield = 'cf_903' THEN  vtiger_accountscf.cf_903 
WHEN tmp_type_from_subcat.subfield = 'cf_895' THEN  vtiger_accountscf.cf_895 
WHEN tmp_type_from_subcat.subfield = 'cf_904' THEN  vtiger_accountscf.cf_904 
END ) = 1

WHERE 

CHARINDEX('---' ,
CASE 
WHEN tmp_type_from_subcat.subfield = 'cf_894' THEN  vtiger_accountscf.cf_894 
WHEN tmp_type_from_subcat.subfield = 'cf_902' THEN  vtiger_accountscf.cf_902
WHEN tmp_type_from_subcat.subfield = 'cf_903' THEN  vtiger_accountscf.cf_903 
WHEN tmp_type_from_subcat.subfield = 'cf_895' THEN  vtiger_accountscf.cf_895 
WHEN tmp_type_from_subcat.subfield = 'cf_904' THEN  vtiger_accountscf.cf_904 
END ) = 0;

--UPDATE F2  - Main activity with selected '---'
UPDATE vtiger_account
SET
vtiger_account.account_client_type = tmp_type_from_subcat.account_client_type,
vtiger_account.account_main_activity = tmp_type_from_subcat.account_main_activity
from vtiger_account
 JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
 JOIN vtiger_accountscf on vtiger_accountscf.accountid = vtiger_account.accountid 
 join tmp_type_from_subcat on 

CHARINDEX(tmp_type_from_subcat.subcat ,
CASE 
WHEN tmp_type_from_subcat.subfield = 'cf_894' THEN  vtiger_accountscf.cf_894 
WHEN tmp_type_from_subcat.subfield = 'cf_902' THEN  vtiger_accountscf.cf_902
WHEN tmp_type_from_subcat.subfield = 'cf_903' THEN  vtiger_accountscf.cf_903 
WHEN tmp_type_from_subcat.subfield = 'cf_895' THEN  vtiger_accountscf.cf_895 
WHEN tmp_type_from_subcat.subfield = 'cf_904' THEN  vtiger_accountscf.cf_904 
END ) = 10
WHERE 
CHARINDEX('---' ,
CASE 
WHEN tmp_type_from_subcat.subfield = 'cf_894' THEN  vtiger_accountscf.cf_894 
WHEN tmp_type_from_subcat.subfield = 'cf_902' THEN  vtiger_accountscf.cf_902
WHEN tmp_type_from_subcat.subfield = 'cf_903' THEN  vtiger_accountscf.cf_903 
WHEN tmp_type_from_subcat.subfield = 'cf_895' THEN  vtiger_accountscf.cf_895 
WHEN tmp_type_from_subcat.subfield = 'cf_904' THEN  vtiger_accountscf.cf_904 
END ) = 1;

-- UPDATE G1 - Secondary activity without selected '---'
UPDATE vtiger_account
SET
vtiger_account.account_sec_activity = tmp_type_from_subcat.account_main_activity

FROM vtiger_account
 JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
 JOIN vtiger_accountscf on vtiger_accountscf.accountid = vtiger_account.accountid 
 JOIN tmp_type_from_subcat on 
CHARINDEX(tmp_type_from_subcat.subcat ,
CASE 
WHEN tmp_type_from_subcat.subfield = 'cf_894' THEN  vtiger_accountscf.cf_894 
WHEN tmp_type_from_subcat.subfield = 'cf_902' THEN  vtiger_accountscf.cf_902
WHEN tmp_type_from_subcat.subfield = 'cf_903' THEN  vtiger_accountscf.cf_903 
WHEN tmp_type_from_subcat.subfield = 'cf_895' THEN  vtiger_accountscf.cf_895 
WHEN tmp_type_from_subcat.subfield = 'cf_904' THEN  vtiger_accountscf.cf_904 
END ) > 1
WHERE 
CHARINDEX('---' ,
CASE 
WHEN tmp_type_from_subcat.subfield = 'cf_894' THEN  vtiger_accountscf.cf_894 
WHEN tmp_type_from_subcat.subfield = 'cf_902' THEN  vtiger_accountscf.cf_902
WHEN tmp_type_from_subcat.subfield = 'cf_903' THEN  vtiger_accountscf.cf_903 
WHEN tmp_type_from_subcat.subfield = 'cf_895' THEN  vtiger_accountscf.cf_895 
WHEN tmp_type_from_subcat.subfield = 'cf_904' THEN  vtiger_accountscf.cf_904 
END ) = 0
AND tmp_type_from_subcat.account_main_activity <> vtiger_account.account_main_activity;

-- UPDATE G2 - Secondary activity with selected '---'
UPDATE vtiger_account
SET
vtiger_account.account_sec_activity = tmp_type_from_subcat.account_main_activity

FROM vtiger_account
 JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
 JOIN vtiger_accountscf on vtiger_accountscf.accountid = vtiger_account.accountid 
 JOIN tmp_type_from_subcat on 
CHARINDEX(tmp_type_from_subcat.subcat ,
CASE 
WHEN tmp_type_from_subcat.subfield = 'cf_894' THEN  vtiger_accountscf.cf_894 
WHEN tmp_type_from_subcat.subfield = 'cf_902' THEN  vtiger_accountscf.cf_902
WHEN tmp_type_from_subcat.subfield = 'cf_903' THEN  vtiger_accountscf.cf_903 
WHEN tmp_type_from_subcat.subfield = 'cf_895' THEN  vtiger_accountscf.cf_895 
WHEN tmp_type_from_subcat.subfield = 'cf_904' THEN  vtiger_accountscf.cf_904 
END ) > 10
WHERE 
CHARINDEX('---' ,
CASE 
WHEN tmp_type_from_subcat.subfield = 'cf_894' THEN  vtiger_accountscf.cf_894 
WHEN tmp_type_from_subcat.subfield = 'cf_902' THEN  vtiger_accountscf.cf_902
WHEN tmp_type_from_subcat.subfield = 'cf_903' THEN  vtiger_accountscf.cf_903 
WHEN tmp_type_from_subcat.subfield = 'cf_895' THEN  vtiger_accountscf.cf_895 
WHEN tmp_type_from_subcat.subfield = 'cf_904' THEN  vtiger_accountscf.cf_904 
END ) = 1
AND tmp_type_from_subcat.account_main_activity <> vtiger_account.account_main_activity;
