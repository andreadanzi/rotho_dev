-- danzi.tn@20150713 
UPDATE
vtiger_account
SET 
vtiger_account.account_main_activity = 'EDIFICI IN LEGNO'
from 
vtiger_account
join vtiger_crmentity  on vtiger_crmentity.crmid = vtiger_account.accountid and vtiger_crmentity.deleted = 0
WHERE
vtiger_account.account_main_activity = 'CASE IN LEGNO';

UPDATE
vtiger_account
SET 
vtiger_account.account_sec_activity = 'EDIFICI IN LEGNO'
from 
vtiger_account
join vtiger_crmentity  on vtiger_crmentity.crmid = vtiger_account.accountid and vtiger_crmentity.deleted = 0
WHERE
vtiger_account.account_sec_activity = 'CASE IN LEGNO';

-- vtiger_leaddetails

UPDATE
vtiger_leaddetails

SET 
vtiger_leaddetails.leads_main_activity = 'EDIFICI IN LEGNO'
from 
vtiger_leaddetails
join vtiger_crmentity  on vtiger_crmentity.crmid = vtiger_leaddetails.leadid and vtiger_crmentity.deleted = 0
WHERE
vtiger_leaddetails.leads_main_activity = 'CASE IN LEGNO';


-- vtiger_visitreport
UPDATE
vtiger_visitreport
SET 
vtiger_visitreport.vr_account_main_activity = 'EDIFICI IN LEGNO'
from 
vtiger_visitreport
join vtiger_crmentity  on vtiger_crmentity.crmid = vtiger_visitreport.visitreportid and vtiger_crmentity.deleted = 0
WHERE
vtiger_visitreport.vr_account_main_activity = 'CASE IN LEGNO';

UPDATE
vtiger_visitreport
SET 
vtiger_visitreport.vr_account_sec_activity = 'EDIFICI IN LEGNO'
from 
vtiger_visitreport
join vtiger_crmentity  on vtiger_crmentity.crmid = vtiger_visitreport.visitreportid and vtiger_crmentity.deleted = 0
WHERE
vtiger_visitreport.vr_account_sec_activity = 'CASE IN LEGNO';


