-- UPDATE INSPECTIONS
select distinct 
vtiger_inspections.accountid,
vtiger_account.accountname,
vtiger_inspections.account_cat ,
vtiger_account.account_line
from vtiger_inspections
join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_inspections.inspectionsid and vtiger_crmentity.deleted = 0
join vtiger_account on vtiger_account.accountid = vtiger_inspections.accountid