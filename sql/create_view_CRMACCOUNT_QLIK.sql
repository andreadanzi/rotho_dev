DROP VIEW CRMACCOUNTS_QLIK;
CREATE VIEW CRMACCOUNTS_QLIK AS 
SELECT     dbo.vtiger_account.accountid, dbo.vtiger_account.accountname, dbo.vtiger_account.sem_importflag, dbo.vtiger_account.sem_importdate, 
                      dbo.vtiger_account.account_client_type, dbo.vtiger_account.account_line, dbo.vtiger_account.account_main_activity, dbo.vtiger_account.account_brand, 
                      dbo.vtiger_account.external_code, dbo.vtiger_crmentity.crmid, dbo.vtiger_crmentity.deleted
FROM         dbo.vtiger_account INNER JOIN
                      dbo.vtiger_crmentity ON dbo.vtiger_account.accountid = dbo.vtiger_crmentity.crmid
WHERE     (dbo.vtiger_account.external_code <> '') AND (dbo.vtiger_crmentity.deleted = 0) AND (dbo.vtiger_account.external_code IS NOT NULL)


DROP VIEW CRMACCOUNTS_QLIK;
CREATE VIEW CRMACCOUNTS_QLIK AS 
SELECT    
min(vtiger_account.accountid) as accountid, 
vtiger_account.accountname, 
max(vtiger_account.sem_importflag) as sem_importflag, 
max(vtiger_account.sem_importdate) as sem_importdate, 
vtiger_account.account_client_type,
vtiger_account.account_line, 
vtiger_account.account_main_activity, 
vtiger_account.account_brand, 
vtiger_account.external_code, 
min(vtiger_crmentity.crmid) as crmid, 
min( vtiger_crmentity.deleted) as deleted,
count(*) as cnt_accounts
FROM vtiger_account 
INNER JOIN vtiger_crmentity ON vtiger_account.accountid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0
WHERE     
vtiger_account.external_code <> ''
AND 
vtiger_account.external_code IS NOT NULL
GROUP BY
vtiger_account.accountname, 
vtiger_account.account_client_type,
vtiger_account.account_line, 
vtiger_account.account_main_activity, 
vtiger_account.account_brand, 
vtiger_account.external_code;