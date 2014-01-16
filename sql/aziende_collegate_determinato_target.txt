select
vtiger_targets.targetname,
vtiger_targets.target_no,
vtiger_account.accountid,
vtiger_account.accountname,
vtiger_account.account_no,
vtiger_accountbillads.bill_city,
vtiger_accountbillads.bill_state
from vtiger_targets 
JOIN vtiger_crmentityrel on vtiger_crmentityrel.crmid = vtiger_targets.targetsid and vtiger_crmentityrel.relmodule = 'Accounts'
JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_crmentityrel.relcrmid and vtiger_crmentity.deleted = 0
JOIN vtiger_account on vtiger_account.accountid = vtiger_crmentity.crmid
JOIN vtiger_accountbillads on vtiger_accountbillads.accountaddressid = vtiger_account.accountid
where vtiger_targets.targetsid = 1137978
order by accountname