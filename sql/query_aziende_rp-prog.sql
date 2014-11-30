-- danzi.tn@20141126 nuova classificazione

SELECT vtiger_account.accountid,
vtiger_account.accountname,
vtiger_account.account_type,
vtiger_account.rating,
vtiger_accountscf.cf_927 as rating_attuale,
vtiger_accountscf.cf_762 as ccategory,
vtiger_accountscf.cf_895 as subcat, 
CASE 
WHEN  tmp_accno.accno IS NULL THEN 'CARP' ELSE 'SAFE'
END as new_cat
FROM vtiger_accountscf
JOIN vtiger_crmentity accent on vtiger_accountscf.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_account on vtiger_account.accountid = vtiger_accountscf.accountid 
LEFT JOIN tmp_accno on tmp_accno.accno = vtiger_account.account_no
WHERE vtiger_accountscf.cf_762 = 'RP / PROG' 
--ORDER BY vtiger_accountscf.cf_895


--- determinazione tipo subcat sulla base della categoria con pipe!!

select  
CASE 
WHEN vtiger_accountscf.cf_762 = 'RC / CARP' THEN  vtiger_accountscf.cf_894 
WHEN vtiger_accountscf.cf_762 = 'RD / DIST' THEN  vtiger_accountscf.cf_902
WHEN vtiger_accountscf.cf_762 = 'RP / PROG' THEN  vtiger_accountscf.cf_903 
WHEN vtiger_accountscf.cf_762 = 'Università' THEN  vtiger_accountscf.cf_895 
WHEN vtiger_accountscf.cf_762 = 'RE / ALTRO' THEN  vtiger_accountscf.cf_904 
END as subcat
FROM vtiger_accountscf
JOIN vtiger_crmentity accent on vtiger_accountscf.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_account on vtiger_account.accountid = vtiger_accountscf.accountid 
JOIN vtiger_cf_762 on vtiger_cf_762.cf_762 = vtiger_accountscf.cf_762

select vtiger_cf_894.* , 'Sub 1' as SUBCAT from vtiger_cf_894
UNION
select vtiger_cf_902.* , 'Sub 2' as SUBCAT from vtiger_cf_902
UNION
select vtiger_cf_903.* , 'Sub 3' as SUBCAT from vtiger_cf_903
UNION
select vtiger_cf_895.* , 'Sub 4' as SUBCAT from vtiger_cf_895
UNION
select vtiger_cf_904.* , 'Sub 5' as SUBCAT from vtiger_cf_904
ORDER BY SUBCAT