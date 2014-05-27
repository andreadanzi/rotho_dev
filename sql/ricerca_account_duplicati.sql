select 
vtiger_account.accountid,
dup_account.accountid,
vtiger_account.account_no,
dup_account.account_no,
vtiger_account.accountname,
dup_account.accountname,
vtiger_crmentity.smownerid,
dup_crmentity.smownerid
from vtiger_account
join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid and vtiger_crmentity.deleted =0
join vtiger_account as dup_account on dup_account.external_code = vtiger_account.external_code and dup_account.accountid <> vtiger_account.accountid 
join vtiger_crmentity as dup_crmentity on dup_crmentity.crmid = dup_account.accountid and dup_crmentity.deleted =0
where vtiger_account.external_code IS NOT NULL AND vtiger_account.external_code <>''