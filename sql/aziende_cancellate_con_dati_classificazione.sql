select vtiger_account.accountid,
vtiger_account.accountname,
vtiger_account.account_client_type,
vtiger_account.account_main_activity,
vtiger_account.account_sec_activity,
vtiger_account.account_brand,
dupaccount.accountid,
dupaccount.accountname,
dupaccount.account_client_type,
dupaccount.account_main_activity,
dupaccount.account_sec_activity,
dupaccount.account_brand,
dupcrment.*
 from vtiger_account
JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid AND vtiger_crmentity.deleted = 0
JOIN vtiger_account dupaccount on dupaccount.accountname =  vtiger_account.accountname AND dupaccount.external_code = vtiger_account.external_code AND dupaccount.accountid <> vtiger_account.accountid
JOIN vtiger_crmentity dupcrment on dupcrment.crmid = dupaccount.accountid AND dupcrment.deleted = 1
WHERE
 (vtiger_account.account_client_type IS NULL OR vtiger_account.account_client_type ='')
AND dupaccount.account_client_type  IS NOT NULL 
AND dupaccount.account_client_type <> ''
AND vtiger_account.external_code IS NOT NULL
AND vtiger_account.external_code <> ''

UPDATE 
vtiger_account
SET
vtiger_account.account_client_type = dupaccount.account_client_type,
vtiger_account.account_main_activity = dupaccount.account_main_activity,
vtiger_account.account_sec_activity=dupaccount.account_sec_activity,
vtiger_account.account_brand=dupaccount.account_brand
 from vtiger_account
JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid AND vtiger_crmentity.deleted = 0
JOIN vtiger_account dupaccount on dupaccount.accountname =  vtiger_account.accountname AND dupaccount.external_code = vtiger_account.external_code AND dupaccount.accountid <> vtiger_account.accountid
JOIN vtiger_crmentity dupcrment on dupcrment.crmid = dupaccount.accountid AND dupcrment.deleted = 1
WHERE
 (vtiger_account.account_client_type IS NULL OR vtiger_account.account_client_type ='')
AND dupaccount.account_client_type  IS NOT NULL 
AND dupaccount.account_client_type <> ''
AND vtiger_account.external_code IS NOT NULL
AND vtiger_account.external_code <> ''