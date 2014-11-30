-- danzi.tn@20141126 nuova classificazione

select 
vtiger_account.accountid,
tmp_type_from_subcat.account_main_activity,
tmp_type_from_subcat.account_client_type,
vtiger_accountscf.cf_762,
vtiger_account.account_no,
tmp_type_from_subcat.subfield,
CASE 
WHEN tmp_type_from_subcat.subfield = 'cf_894' THEN  vtiger_accountscf.cf_894 
WHEN tmp_type_from_subcat.subfield = 'cf_902' THEN  vtiger_accountscf.cf_902
WHEN tmp_type_from_subcat.subfield = 'cf_903' THEN  vtiger_accountscf.cf_903 
WHEN tmp_type_from_subcat.subfield = 'cf_895' THEN  vtiger_accountscf.cf_895 
WHEN tmp_type_from_subcat.subfield = 'cf_904' THEN  vtiger_accountscf.cf_904 
END

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
END ) = 1

WHERE 

CHARINDEX('---' ,
CASE 
WHEN tmp_type_from_subcat.subfield = 'cf_894' THEN  vtiger_accountscf.cf_894 
WHEN tmp_type_from_subcat.subfield = 'cf_902' THEN  vtiger_accountscf.cf_902
WHEN tmp_type_from_subcat.subfield = 'cf_903' THEN  vtiger_accountscf.cf_903 
WHEN tmp_type_from_subcat.subfield = 'cf_895' THEN  vtiger_accountscf.cf_895 
WHEN tmp_type_from_subcat.subfield = 'cf_904' THEN  vtiger_accountscf.cf_904 
END ) = 0

ORDER BY vtiger_account.account_no

-- UPDATE F1
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
END ) = 0

-- Quelli che hanno com prima  selezione ---

select 
vtiger_account.accountid,
tmp_type_from_subcat.account_main_activity,
tmp_type_from_subcat.account_client_type,
vtiger_accountscf.cf_762,
vtiger_account.account_no,
tmp_type_from_subcat.subfield,
CASE 
WHEN tmp_type_from_subcat.subfield = 'cf_894' THEN  vtiger_accountscf.cf_894 
WHEN tmp_type_from_subcat.subfield = 'cf_902' THEN  vtiger_accountscf.cf_902
WHEN tmp_type_from_subcat.subfield = 'cf_903' THEN  vtiger_accountscf.cf_903 
WHEN tmp_type_from_subcat.subfield = 'cf_895' THEN  vtiger_accountscf.cf_895 
WHEN tmp_type_from_subcat.subfield = 'cf_904' THEN  vtiger_accountscf.cf_904 
END
,
CHARINDEX(tmp_type_from_subcat.subcat ,
CASE 
WHEN tmp_type_from_subcat.subfield = 'cf_894' THEN  vtiger_accountscf.cf_894 
WHEN tmp_type_from_subcat.subfield = 'cf_902' THEN  vtiger_accountscf.cf_902
WHEN tmp_type_from_subcat.subfield = 'cf_903' THEN  vtiger_accountscf.cf_903 
WHEN tmp_type_from_subcat.subfield = 'cf_895' THEN  vtiger_accountscf.cf_895 
WHEN tmp_type_from_subcat.subfield = 'cf_904' THEN  vtiger_accountscf.cf_904 
END )
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
END ) = 1

--UPDATE F2 
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
END ) = 1



-- UPDATE G1
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
AND tmp_type_from_subcat.account_main_activity <> vtiger_account.account_main_activity

-- UPDATE G2
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
AND tmp_type_from_subcat.account_main_activity <> vtiger_account.account_main_activity

select 
convert(varchar, vtiger_accountscf.cf_762),
convert(varchar, vtiger_accountscf.cf_894),
convert(varchar, vtiger_accountscf.cf_902),
convert(varchar, vtiger_accountscf.cf_903),
convert(varchar, vtiger_accountscf.cf_895),
convert(varchar, vtiger_accountscf.cf_904),
count(*) as cnt_items
from vtiger_accountscf
JOIN vtiger_crmentity accent on vtiger_accountscf.accountid = accent.crmid AND accent.deleted = 0
WHERE vtiger_accountscf.cf_762 = 'RS / SAFE'

group by
convert(varchar, vtiger_accountscf.cf_762),
convert(varchar, vtiger_accountscf.cf_894),
convert(varchar, vtiger_accountscf.cf_902),
convert(varchar, vtiger_accountscf.cf_903),
convert(varchar, vtiger_accountscf.cf_895),
convert(varchar, vtiger_accountscf.cf_904)

ORDER BY cnt_items