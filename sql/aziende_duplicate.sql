SELECT 
vtiger_account.accountid,
vtiger_account.accountname,
vtiger_account.account_line,
vtiger_account.sem_importflag,
vtiger_account.sem_importdate,
vtiger_account.external_code,
duplaccount.accountid as dupl_id,
duplaccount.accountname as dupl_name
-- erp_temp_crm_aziende.INSERTDATE,
-- erp_temp_crm_aziende.BASE_NUMBER
FROM
vtiger_account
JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid and vtiger_crmentity.deleted=0
JOIN vtiger_account duplaccount on duplaccount.accountname = vtiger_account.accountname AND duplaccount.external_code = vtiger_account.external_code AND duplaccount.accountid <> vtiger_account.accountid
JOIN vtiger_crmentity duplentity on duplentity.crmid = duplaccount.accountid and duplentity.deleted=0
-- LEFT JOIN erp_temp_crm_aziende ON erp_temp_crm_aziende.BASE_NUMBER = vtiger_account.external_code
WHERE 
vtiger_account.external_code IS NOT NULL
AND vtiger_account.external_code <> ''
-- AND erp_temp_crm_aziende.KEYID is NOT NULL
-- AND vtiger_account.sem_importflag <>'NN'

ORDER BY vtiger_account.accountname ASC

SELECT 
vtiger_account.accountid,
vtiger_account.accountname,
vtiger_account.account_line,
vtiger_account.sem_importflag,
vtiger_account.sem_importdate,
vtiger_account.external_code,
duplaccount.accountid as dupl_id,
duplaccount.accountname as dupl_name,
duplaccount.sem_importflag as dupl_semflag,
duplaccount.sem_importdate as dupl_semdate
-- erp_temp_crm_aziende.INSERTDATE,
-- erp_temp_crm_aziende.BASE_NUMBER
FROM
vtiger_account
JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid and vtiger_crmentity.deleted=0
JOIN vtiger_account duplaccount on duplaccount.accountname <> vtiger_account.accountname AND duplaccount.external_code = vtiger_account.external_code AND duplaccount.accountid <> vtiger_account.accountid
JOIN vtiger_crmentity duplentity on duplentity.crmid = duplaccount.accountid and duplentity.deleted=0
-- LEFT JOIN erp_temp_crm_aziende ON erp_temp_crm_aziende.BASE_NUMBER = vtiger_account.external_code
WHERE 
vtiger_account.external_code IS NOT NULL
AND vtiger_account.external_code <> ''
-- AND erp_temp_crm_aziende.KEYID is NOT NULL
-- AND vtiger_account.sem_importflag <>'NN'

ORDER BY vtiger_account.external_code ASC


-- AZIENDE DUPLICATE IN erp_temp_crm_aziende


select 
erp_temp_crm_aziende.BASE_NAME, min(erp_temp_crm_aziende.KEYID  ), count(*)  as cntitem
from erp_temp_crm_aziende
join erp_temp_crm_aziende aziendedup on aziendedup.BASE_NAME = erp_temp_crm_aziende.BASE_NAME 
												AND aziendedup.BASE_NUMBER = erp_temp_crm_aziende.BASE_NUMBER 
												AND aziendedup.FINANCE_LOCALTAXID = erp_temp_crm_aziende.FINANCE_LOCALTAXID 
												AND aziendedup.KEYID <> erp_temp_crm_aziende.KEYID 

GROUP BY
erp_temp_crm_aziende.BASE_NAME
ORDER BY cntitem desc


select 
erp_temp_crm_aziende.KEYID
, erp_temp_crm_aziende.BASE_NAME
, erp_temp_crm_aziende.BASE_NUMBER
, erp_temp_crm_aziende.FINANCE_LOCALTAXID
, erp_temp_crm_aziende.BASE_DELETED
, erp_temp_crm_aziende.CUSTOMER_STATUS
, erp_temp_crm_aziende.*
from erp_temp_crm_aziende
join erp_temp_crm_aziende aziendedup on aziendedup.BASE_NAME = erp_temp_crm_aziende.BASE_NAME 
												AND aziendedup.BASE_NUMBER = erp_temp_crm_aziende.BASE_NUMBER 
												AND aziendedup.FINANCE_LOCALTAXID = erp_temp_crm_aziende.FINANCE_LOCALTAXID 
												AND aziendedup.KEYID <> erp_temp_crm_aziende.KEYID 
												-- AND aziendedup.KEYID = 22808
ORDER BY  erp_temp_crm_aziende.BASE_NUMBER										




-- Duplicati Creati il 2014-01-28 12:15:29

SELECT 
vtiger_account.accountid,
vtiger_account.accountname,
vtiger_account.account_line,
vtiger_account.sem_importflag,
vtiger_account.sem_importdate,
vtiger_account.external_code,
duplaccount.accountid as dupl_id,
duplaccount.accountname as dupl_name,
duplaccount.sem_importflag,
duplaccount.sem_importdate,
vtiger_crmentity.smcreatorid,
vtiger_crmentity.createdtime,
vtiger_crmentity.modifiedtime,
duplentity.smcreatorid as dupl_smcreatorid,
duplentity.createdtime as dupl_createdtime
-- erp_temp_crm_aziende.INSERTDATE,
-- erp_temp_crm_aziende.BASE_NUMBER
FROM
vtiger_account
JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_account.accountid and vtiger_crmentity.deleted=0
JOIN vtiger_account duplaccount on duplaccount.accountname = vtiger_account.accountname AND duplaccount.external_code = vtiger_account.external_code AND duplaccount.accountid <> vtiger_account.accountid
JOIN vtiger_crmentity duplentity on duplentity.crmid = duplaccount.accountid and duplentity.deleted=0
-- LEFT JOIN erp_temp_crm_aziende ON erp_temp_crm_aziende.BASE_NUMBER = vtiger_account.external_code
WHERE 
vtiger_account.external_code IS NOT NULL
AND vtiger_account.external_code <> ''
-- AND erp_temp_crm_aziende.KEYID is NOT NULL
-- AND vtiger_account.sem_importflag <>'NN'
AND vtiger_crmentity.createdtime= convert(datetime ,'2014-01-28 12:15:29', 120)
AND duplaccount.accountid < vtiger_account.accountid
ORDER BY vtiger_account.accountname ASC, vtiger_account.accountid ASC

--- Cerco le prime
SELECT min(vtiger_account.accountid) as min_accountid, count(*) as count_dupl,
vtiger_account.accountname,
vtiger_account.external_code
FROM
vtiger_account
JOIN vtiger_crmentity 				on vtiger_crmentity.crmid = vtiger_account.accountid and vtiger_crmentity.deleted=0
JOIN vtiger_account duplaccount 	on duplaccount.accountname = vtiger_account.accountname 
											AND duplaccount.external_code = vtiger_account.external_code 
											AND duplaccount.accountid > vtiger_account.accountid
JOIN vtiger_crmentity duplentity on duplentity.crmid = duplaccount.accountid and duplentity.deleted=0
-- LEFT JOIN erp_temp_crm_aziende ON erp_temp_crm_aziende.BASE_NUMBER = vtiger_account.external_code
WHERE 
vtiger_account.external_code IS NOT NULL AND vtiger_account.external_code <> ''
-- AND erp_temp_crm_aziende.KEYID is NOT NULL
-- AND vtiger_account.sem_importflag <>'NN'
-- AND vtiger_crmentity.createdtime= convert(datetime ,'2014-01-28 12:15:29', 120)
-- AND duplaccount.accountid < vtiger_account.accountid
GROUP BY 
vtiger_account.accountname,
vtiger_account.external_code
ORDER BY count_dupl desc, vtiger_account.accountname ASC


-- Per ognuna identifico i suoi duplicati
SELECT 
vtiger_account.accountid,
vtiger_account.accountname,
vtiger_account.external_code,
vtiger_account.account_line,
vtiger_account.sem_importflag,
vtiger_account.sem_importdate,
vtiger_crmentity.smcreatorid,
vtiger_crmentity.createdtime,
vtiger_crmentity.modifiedtime,
duplaccount.accountid as dupl_id,
duplentity.smcreatorid as dupl_smcreatorid,
duplentity.createdtime as dupl_createdtime
FROM
vtiger_account
JOIN vtiger_crmentity 				on vtiger_crmentity.crmid = vtiger_account.accountid and vtiger_crmentity.deleted=0
JOIN vtiger_account duplaccount 	on duplaccount.accountname = vtiger_account.accountname 
											AND duplaccount.external_code = vtiger_account.external_code 
											AND duplaccount.accountid < vtiger_account.accountid
											AND duplaccount.accountid = 560931
JOIN vtiger_crmentity duplentity on duplentity.crmid = duplaccount.accountid and duplentity.deleted=0
ORDER BY vtiger_account.accountid ASC

---- COME FARE
--- Plugin che gira regolarmente
------select come questa sopra o simile, con filtri su
--------- external_code e accountname
--------- scegliere quella con duplaccount.accountid < vtiger_account.accountid come principale
--------- Chiamare la Classe Account->transferRelatedRecords($module, $transferEntityIds, $entityId) poi unlinkRelationship($id, $return_module, $return_id)
--- * module@param String This module name
--- * transferEntityIds@param Array List of Entity Id's from which related records need to be transfered
--- * entityId@param Integer Id of the the Record to which the related records are to be moved
--------- Salvare in un file excel tutte le aziende eliminate (transferEntityIds)

