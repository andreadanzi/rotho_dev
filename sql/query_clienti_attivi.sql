-- danzi@20141001 update massivo Account per individuare clienti attivi a partire dal 2013
SELECT vtiger_account.accountid, vtiger_accountscf.cf_927, 
CASE 
    WHEN vtiger_salesorder.salesorderid IS NOT NULL THEN
        CASE 
            WHEN vtiger_accountscf.cf_927 IS NULL THEN '1'
            WHEN vtiger_accountscf.cf_927 = '' THEN '1'
            WHEN vtiger_accountscf.cf_927 = '1' THEN '1'
            WHEN vtiger_accountscf.cf_927 = '10' THEN '1'
            WHEN vtiger_accountscf.cf_927 = '20' THEN '1'
            WHEN vtiger_accountscf.cf_927 = '30' THEN '30'
            WHEN vtiger_accountscf.cf_927 = '31' THEN '1'
            WHEN vtiger_accountscf.cf_927 = '32' THEN '1'
            WHEN vtiger_accountscf.cf_927 = '33' THEN '1'
            WHEN vtiger_accountscf.cf_927 = '40' THEN '1'
            WHEN vtiger_accountscf.cf_927 = 'Riattivato' THEN '1' 
        END
    ELSE
        CASE 
            WHEN vtiger_accountscf.cf_927 IS NULL THEN '33'
            WHEN vtiger_accountscf.cf_927 = '' THEN '33'
            WHEN vtiger_accountscf.cf_927 = '1' THEN '33'
            WHEN vtiger_accountscf.cf_927 = '10' THEN '10'
            WHEN vtiger_accountscf.cf_927 = '20' THEN '20'
            WHEN vtiger_accountscf.cf_927 = '30' THEN '30'
            WHEN vtiger_accountscf.cf_927 = '31' THEN '31'
            WHEN vtiger_accountscf.cf_927 = '32' THEN '32'
            WHEN vtiger_accountscf.cf_927 = '33' THEN '33'
            WHEN vtiger_accountscf.cf_927 = '40' THEN '33'
            WHEN vtiger_accountscf.cf_927 = 'Riattivato' THEN '33' 
        END
END
AS new_cf_927 ,
count(vtiger_salesorder.salesorderid) as CNT_TOT
FROM vtiger_accountscf
JOIN vtiger_crmentity accent on vtiger_accountscf.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_account on vtiger_account.accountid = vtiger_accountscf.accountid 
LEFT JOIN vtiger_salesorder on vtiger_account.accountid = vtiger_salesorder.accountid
AND vtiger_salesorder.data_ordine_ven BETWEEN DATEADD(YEAR,-1,DATEADD(yy, DATEDIFF(yy,0,getdate()), 0) ) AND GETDATE()
LEFT JOIN vtiger_crmentity salent on vtiger_salesorder.salesorderid = salent.crmid AND salent.deleted = 0
WHERE 
vtiger_account.external_code <> '' -- Codice Cleinte Valorizzato
AND vtiger_account.external_code IS NOT NULL -- Codice Cleinte Valorizzato
GROUP BY 
vtiger_account.accountid, 
vtiger_accountscf.cf_927,
CASE 
    WHEN vtiger_salesorder.salesorderid IS NOT NULL THEN
        CASE 
            WHEN vtiger_accountscf.cf_927 IS NULL THEN '1'
            WHEN vtiger_accountscf.cf_927 = '' THEN '1'
            WHEN vtiger_accountscf.cf_927 = '1' THEN '1'
            WHEN vtiger_accountscf.cf_927 = '10' THEN '1'
            WHEN vtiger_accountscf.cf_927 = '20' THEN '1'
            WHEN vtiger_accountscf.cf_927 = '30' THEN '30'
            WHEN vtiger_accountscf.cf_927 = '31' THEN '1'
            WHEN vtiger_accountscf.cf_927 = '32' THEN '1'
            WHEN vtiger_accountscf.cf_927 = '33' THEN '1'
            WHEN vtiger_accountscf.cf_927 = '40' THEN '1'
            WHEN vtiger_accountscf.cf_927 = 'Riattivato' THEN '1' 
        END
    ELSE
        CASE 
            WHEN vtiger_accountscf.cf_927 IS NULL THEN '33'
            WHEN vtiger_accountscf.cf_927 = '' THEN '33'
            WHEN vtiger_accountscf.cf_927 = '1' THEN '33'
            WHEN vtiger_accountscf.cf_927 = '10' THEN '10'
            WHEN vtiger_accountscf.cf_927 = '20' THEN '20'
            WHEN vtiger_accountscf.cf_927 = '30' THEN '30'
            WHEN vtiger_accountscf.cf_927 = '31' THEN '31'
            WHEN vtiger_accountscf.cf_927 = '32' THEN '32'
            WHEN vtiger_accountscf.cf_927 = '33' THEN '33'
            WHEN vtiger_accountscf.cf_927 = '40' THEN '33'
            WHEN vtiger_accountscf.cf_927 = 'Riattivato' THEN '33' 
        END
END

--- UPDATE
UPDATE vtiger_accountscf
SET vtiger_accountscf.cf_927 = 
CASE 
    WHEN vtiger_salesorder.salesorderid IS NOT NULL THEN
        CASE 
            WHEN vtiger_accountscf.cf_927 IS NULL THEN '1'
            WHEN vtiger_accountscf.cf_927 = '' THEN '1'
            WHEN vtiger_accountscf.cf_927 = '1' THEN '1'
            WHEN vtiger_accountscf.cf_927 = '10' THEN '1'
            WHEN vtiger_accountscf.cf_927 = '20' THEN '1'
            WHEN vtiger_accountscf.cf_927 = '30' THEN '30'
            WHEN vtiger_accountscf.cf_927 = '31' THEN '1'
            WHEN vtiger_accountscf.cf_927 = '32' THEN '1'
            WHEN vtiger_accountscf.cf_927 = '33' THEN '1'
            WHEN vtiger_accountscf.cf_927 = '40' THEN '1'
            WHEN vtiger_accountscf.cf_927 = 'Riattivato' THEN '1' 
        END
    ELSE
        CASE 
            WHEN vtiger_accountscf.cf_927 IS NULL THEN '33'
            WHEN vtiger_accountscf.cf_927 = '' THEN '33'
            WHEN vtiger_accountscf.cf_927 = '1' THEN '33'
            WHEN vtiger_accountscf.cf_927 = '10' THEN '10'
            WHEN vtiger_accountscf.cf_927 = '20' THEN '20'
            WHEN vtiger_accountscf.cf_927 = '30' THEN '30'
            WHEN vtiger_accountscf.cf_927 = '31' THEN '31'
            WHEN vtiger_accountscf.cf_927 = '32' THEN '32'
            WHEN vtiger_accountscf.cf_927 = '33' THEN '33'
            WHEN vtiger_accountscf.cf_927 = '40' THEN '33'
            WHEN vtiger_accountscf.cf_927 = 'Riattivato' THEN '33' 
        END
END
FROM vtiger_accountscf
JOIN vtiger_crmentity accent on vtiger_accountscf.accountid = accent.crmid AND accent.deleted = 0
JOIN vtiger_account on vtiger_account.accountid = vtiger_accountscf.accountid 
LEFT JOIN vtiger_salesorder on vtiger_account.accountid = vtiger_salesorder.accountid
AND vtiger_salesorder.data_ordine_ven BETWEEN DATEADD(YEAR,-1,DATEADD(yy, DATEDIFF(yy,0,getdate()), 0) ) AND GETDATE()
LEFT JOIN vtiger_crmentity salent on vtiger_salesorder.salesorderid = salent.crmid AND salent.deleted = 0
WHERE 
vtiger_account.external_code <> '' -- Codice Cleinte Valorizzato
AND vtiger_account.external_code IS NOT NULL -- Codice Cleinte Valorizzato
