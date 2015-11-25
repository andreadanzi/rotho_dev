select  
o.CLIENTE_FATTURAZIONE, count(*) AS NumeroDiOrdini
from erp_temp_crm_ordini o
LEFT JOIN erp_temp_crm_aziende a on a.BASE_NUMBER = o.CLIENTE_FATTURAZIONE
where
a.BASE_NAME IS NULL 
GROUP BY o.CLIENTE_FATTURAZIONE
ORDER BY NumeroDiOrdini desc