-- drop view vtiger_view_agents_parentchild;
create view vtiger_view_agents_parentchild as
select 
u.id as id, 
u.user_name as agente, 
u.first_name , 
u.last_name,
u.erp_code,
u.agent_cod_capoarea, 
u.status,
count(ue.crmid) as cnt,
p.user_name as parent,
p.first_name as parent_first_name, 
p.last_name as parent_last_name, 
p.status as parent_status, 
p.erp_code as  parent_erp_code
from 
vtiger_users u
join vtiger_crmentity ue on ue.smownerid = u.id and ue.deleted = 0 and ue.setype = 'Accounts'
left join vtiger_users p on p.erp_code = u.agent_cod_capoarea and p.erp_code <>'' and p.erp_code IS NOT NULL
group by 
u.id,
u.user_name , 
u.first_name , 
u.last_name,
u.erp_code,
u.agent_cod_capoarea, 
u.status,
p.user_name ,
p.first_name, 
p.last_name,
p.status,
p.erp_code
132926


select 
u.id as id, 
u.user_name as agente, 
u.first_name , 
u.last_name,
u.erp_code,
u.agent_cod_capoarea, 
u.status,
count(ue.crmid) as cnt,
p.user_name as parent,
p.first_name as parent_first_name, 
p.last_name as parent_last_name, 
p.status as parent_status, 
p.erp_code as  parent_erp_code
from 
vtiger_users u
join vtiger_crmentity ue on ue.smownerid = u.id and ue.deleted = 0 and ue.setype = 'Accounts'
join vtiger_accountbillads acc on acc.accountaddressid = ue.crmid and acc.bill_country = 'IT'
left join vtiger_users p on p.erp_code = u.agent_cod_capoarea and p.erp_code <>'' and p.erp_code IS NOT NULL
group by 
u.id,
u.user_name , 
u.first_name , 
u.last_name,
u.erp_code,
u.agent_cod_capoarea, 
u.status,
p.user_name ,
p.first_name, 
p.last_name,
p.status,
p.erp_code