SELECT  
		 A.accountid
      ,accountname
		,account_line
      ,account_client_type
      ,account_main_activity
      ,account_sec_activity
      ,account_brand
      ,E.deleted
      ,CF.cf_894 as c1,CF.cf_895 as c4, cf_902 as c2,
cf_903 as c3,
cf_904 as c5
  FROM vtiger_account A
  join vtiger_crmentity E on 
  A.accountid = E.crmid
  join vte40_387.dbo.vtiger_accountscf CF
  on A.accountid = CF.accountid
  where (account_client_type is null or account_client_type = '') 
  and external_code is not null and external_code <> '' 
  and  (CF.cf_894 is not null or CF.cf_895 is not null or cf_902 is not null or cf_903 is not null or cf_904 is not null) 
  and  (convert(varchar,CF.cf_894) <> '' or convert(varchar,CF.cf_895) <> '' or convert(varchar,cf_902) <> '' or   convert(varchar,cf_903) <>'' or convert(varchar,cf_904) <> '')
  and deleted = 0
  