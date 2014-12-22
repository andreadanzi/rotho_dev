update
vtiger_leaddetails
SET
vtiger_leaddetails.leads_line =
CASE
 WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'CARP' THEN  'RC / CARP' 
 WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'SAFE' THEN  'RS / SAFE'
 WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'DIST' THEN  'RD / DIST'
 WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'INDUST' THEN 'RR / DIREZ'
 ELSE  '---'
END
from vtiger_leaddetails
join vtiger_crmentity on   vtiger_crmentity.crmid = vtiger_leaddetails.leadid and deleted=0
join vtiger_leadscf on vtiger_leadscf.leadid  = vtiger_leaddetails.leadid 
JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
JOIN erp_temp_crm_agenti ON vtiger_users.user_name = erp_temp_crm_agenti.AGENT_USERNAME
WHERE 
vtiger_leaddetails.leads_line =''
or vtiger_leaddetails.leads_line = '---'
or vtiger_leaddetails.leads_line IS NULL