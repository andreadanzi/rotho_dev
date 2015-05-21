-- danzi.tn@20150408 aggiornare smownerid dei contatti sulla base della propria azienda
select
vtiger_contactdetails.contactid ,
vtiger_contactdetails.accountid,
CASE 
	WHEN accentity.smownerid IS NULL THEN vtiger_crmentity.smownerid
	ELSE accentity.smownerid
END	,
vtiger_crmentity.*
from vtiger_crmentity
join vtiger_contactdetails on vtiger_crmentity.crmid = vtiger_contactdetails.contactid 
join vtiger_account on vtiger_account.accountid = vtiger_contactdetails.accountid
join vtiger_crmentity accentity on accentity.crmid = vtiger_account.accountid and accentity.deleted = 0

where 
vtiger_crmentity.deleted = 0
AND accentity.smownerid <> vtiger_crmentity.smownerid;

UPDATE 
vtiger_crmentity
SET
vtiger_crmentity.smownerid = 
CASE 
	WHEN accentity.smownerid IS NULL THEN vtiger_crmentity.smownerid
	ELSE accentity.smownerid
END	
from vtiger_crmentity
join vtiger_contactdetails on vtiger_crmentity.crmid = vtiger_contactdetails.contactid 
join vtiger_account on vtiger_account.accountid = vtiger_contactdetails.accountid
join vtiger_crmentity accentity on accentity.crmid = vtiger_account.accountid and accentity.deleted = 0
where 
vtiger_crmentity.deleted = 0
AND accentity.smownerid <> vtiger_crmentity.smownerid;