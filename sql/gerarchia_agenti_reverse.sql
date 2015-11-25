select 
ag.user_name as agente, 
ag.last_name as agente_last_name,
ag.first_name as agente_first_name,
ag.erp_code,
ag.agent_cod_capoarea, 
count(*) as cnt,
am.user_name as areamanager,
am.last_name as areamanager_last_name,
am.first_name as areamanager_first_name,
am.erp_code as areamanager_erp_code,
max(addr.bill_country) AS region_country,
rm.user_name as regionmanager,
rm.last_name as regionmanager_last_name,
rm.first_name as regionmanager_first_name,
rm.erp_code as regionmanager_erp_code,
dm.user_name as divisionmanager,
dm.last_name as divisionmanager_last_name,
dm.first_name as divisionmanager_first_name,
dm.erp_code as divisionmanager_erp_code
from 
vtiger_users dm

join vtiger_users rm on rm.agent_cod_capoarea = dm.erp_code and rm.agent_cod_capoarea <>'' and rm.agent_cod_capoarea IS NOT NULL
join vtiger_users am on rm.erp_code = am.agent_cod_capoarea and am.agent_cod_capoarea <>'' and am.agent_cod_capoarea IS NOT NULL
join vtiger_users ag on am.erp_code = ag.agent_cod_capoarea and ag.agent_cod_capoarea <>'' and ag.agent_cod_capoarea IS NOT NULL
join vtiger_crmentity e on e.smownerid = ag.id and e.deleted = 0 and e.setype = 'Accounts'
join vtiger_accountbillads addr on addr.accountaddressid = e.crmid

where 
ag.status = 'Active' 

group by 
ag.user_name , 
ag.last_name,
ag.first_name,
ag.agent_cod_capoarea, ag.erp_code,
am.user_name,
am.last_name,
am.first_name ,
am.erp_code,
rm.user_name ,
rm.last_name ,
rm.first_name,
rm.erp_code,
dm.user_name ,
dm.last_name ,
dm.first_name,
dm.erp_code
order by  divisionmanager, regionmanager,region_country, areamanager, cnt
