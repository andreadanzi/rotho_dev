-- danzi.tn@20141212 nova classificazione
-- UPDATE INSPECTIONS
UPDATE 
vtiger_inspections
SET
vtiger_inspections.account_cat = vtiger_account.account_client_type
from vtiger_inspections
join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_inspections.inspectionsid and vtiger_crmentity.deleted = 0
join vtiger_account on vtiger_account.accountid = vtiger_inspections.accountid


-- UPDATE MARKETPRICES
UPDATE 
vtiger_marketprices
SET
vtiger_marketprices.customer_cat = vtiger_account.account_client_type
from vtiger_marketprices
join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_marketprices.marketpricesid and vtiger_crmentity.deleted = 0
join vtiger_account on vtiger_account.accountid = vtiger_marketprices.accounts_customer


-- UPDATE RELATIONS FROM
UPDATE 
vtiger_relations
SET
vtiger_relations.link_from_category = vtiger_account.account_client_type
from vtiger_relations
join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_relations.relationsid and vtiger_crmentity.deleted = 0
join vtiger_account on vtiger_account.accountid = vtiger_relations.link_from


-- UPDATE RELATIONS TO
UPDATE 
vtiger_relations
SET
vtiger_relations.link_to_category = vtiger_account.account_client_type
from vtiger_relations
join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_relations.relationsid and vtiger_crmentity.deleted = 0
join vtiger_account on vtiger_account.accountid = vtiger_relations.link_to


-- UPDATE CONSULENZE
UPDATE 
vtiger_relations
SET
vtiger_relations.link_to_category = vtiger_account.account_client_type
from vtiger_relations
join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_relations.relationsid and vtiger_crmentity.deleted = 0
join vtiger_account on vtiger_account.accountid = vtiger_relations.link_to

