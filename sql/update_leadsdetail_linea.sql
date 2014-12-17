update
vtiger_leaddetails
SET
vtiger_leaddetails.leads_line =
CASE 
            WHEN vtiger_leadscf.cf_761 = '' THEN  '---'
            WHEN vtiger_leadscf.cf_761 IS NULL THEN  '---'
            WHEN CHARINDEX( 'CARP', vtiger_leadscf.cf_761) > 0 THEN  'RC / CARP' 
            WHEN CHARINDEX( 'SAFE', vtiger_leadscf.cf_761) > 0 THEN  'RS / SAFE'
            WHEN CHARINDEX( 'DIST', vtiger_leadscf.cf_761) > 0 THEN  'RD / DIST'
            WHEN CHARINDEX( 'GDO', vtiger_leadscf.cf_761) > 0 THEN  'GD / GDO'
            WHEN CHARINDEX( 'PROG', vtiger_leadscf.cf_761) > 0 THEN  '---'
            WHEN CHARINDEX( 'DIPENDENTE INTERNO', vtiger_leadscf.cf_761) > 0 OR
                CHARINDEX( 'FORNITORE', vtiger_leadscf.cf_761) > 0 OR
                CHARINDEX( 'AGENTE', vtiger_leadscf.cf_761) > 0 OR
                CHARINDEX( 'ASS', vtiger_leadscf.cf_761) > 0 OR
                CHARINDEX( 'ORGANIZZAZIONE', vtiger_leadscf.cf_761) > 0 OR
                CHARINDEX( 'ALTRO', vtiger_leadscf.cf_761) > 0 
                THEN  'RR / DIREZ'
            ELSE  '---'
		END   
from vtiger_leaddetails
join vtiger_crmentity on   vtiger_crmentity.crmid = vtiger_leaddetails.leadid and deleted=0
join vtiger_leadscf on vtiger_leadscf.leadid  = vtiger_leaddetails.leadid 
where
vtiger_leadscf.cf_761 IS NOT NULL
AND vtiger_leadscf.cf_761 <>'' 