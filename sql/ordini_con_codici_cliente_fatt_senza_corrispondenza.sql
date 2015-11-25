select  
s.customerno, 
a.accountname, 
ae.deleted , 
count(s.salesorderid) 
from vtiger_salesorder s
JOIN vtiger_crmentity e on e.crmid = s.salesorderid and e.deleted = 0
LEFT JOIN vtiger_account a on a.external_code = s.customerno
LEFT JOIN vtiger_crmentity ae on ae.crmid = a.accountid
where
s.accountid = 0
AND e.modifiedtime BETWEEN DATEADD(year,-1,GETDATE()) AND GETDATE()
GROUP BY s.customerno, a.accountname, ae.deleted 
ORDER BY a.accountname desc
