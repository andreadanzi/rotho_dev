select 
u.user_name as agente, 
u.erp_code,
u.agent_cod_capoarea, 
count(*) as cnt,
am.user_name as areamanager,
max(addr.bill_country),
rm.user_name as regionmanager,
dm.user_name as divisionmanager
from 
vtiger_users u
join vtiger_crmentity e on e.smownerid = u.id and e.deleted = 0 and e.setype = 'Accounts'
join vtiger_accountbillads addr on addr.accountaddressid = e.crmid
left join vtiger_users am on am.erp_code = u.agent_cod_capoarea and am.erp_code <>'' and am.erp_code IS NOT NULL
left join vtiger_users rm on rm.erp_code = am.agent_cod_capoarea and rm.erp_code <>'' and rm.erp_code IS NOT NULL
left join vtiger_users dm on dm.erp_code = rm.agent_cod_capoarea and dm.erp_code <>'' and dm.erp_code IS NOT NULL
where 
u.status = 'Active' 
-- and u.agent_cod_capoarea = 'AREA501'
-- and rm.user_name='RM007'
group by 
u.user_name , u.agent_cod_capoarea, u.erp_code,
am.user_name,
rm.user_name ,
dm.user_name 
order by  divisionmanager, regionmanager, areamanager, cnt