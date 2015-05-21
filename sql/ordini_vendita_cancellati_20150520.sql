SELECT 
            count(*)
            from  vtiger_crmentity as salent  
            JOIN vtiger_salesorder ON vtiger_salesorder.accountid  = salent.crmid       
            WHERE  YEAR(vtiger_salesorder.data_ordine_ven)< 2013 and salent.deleted =1
				AND salent.description like 'DELETED BY DANZI.TN@20150520%'  ;
