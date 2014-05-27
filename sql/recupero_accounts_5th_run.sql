select vtiger_crmentity.*  from vtiger_crmentity
-- JOIN vtiger_account on vtiger_account.accountid = vtiger_crmentity.crmid
where vtiger_crmentity.deleted = 1 
and setype = 'Accounts'
and
 vtiger_crmentity.modifiedby <>1
and
 vtiger_crmentity.modifiedby <>0
and vtiger_crmentity.createdtime NOT BETWEEN 
CONVERT(datetime, '2014-01-28 11:20:00',120)
AND
CONVERT(datetime, '2014-01-28 13:00:00',120)
and 
vtiger_crmentity.modifiedtime  BETWEEN 
--DATEADD(day, -1, GETDATE()) '2014-01-28 11:20:00' 2014-01-28 13:54:23 - 2014-01-28 16:17:19
CONVERT(datetime, '2014-01-28 11:20:00',120)
AND 
-- DATEADD(day, 0, GETDATE()) '2014-01-28 13:00:00' 2014-01-28 14:29:01 - 2014-01-28 16:17:47
CONVERT(datetime, '2014-01-28 13:00:00',120)

order by  vtiger_crmentity.modifiedtime