SELECT U.user_name, C.campaignname, A.account_client_type, A.accountid, A.accountname, T.targetname
     
  FROM vtiger_campaign C
  INNER JOIN vtiger_crmentity E on C.campaignid = E.crmid

  join vtiger_crmentityrel REL on C.campaignid = REL.crmid
  join vtiger_targets T on T.targetsid = REL.relcrmid
  
  join vtiger_crmentityrel RELT on RELT.crmid = T.targetsid
  join vtiger_account A on A.accountid = RELT.relcrmid
  
  join vtiger_crmentity EA on EA.crmid = A.accountid
  join vtiger_accountbillads BA on A.accountid = BA.accountaddressid
  
  join vtiger_users U on EA.smownerid = U.id
  
  where 
  C.campaigntype = 'Email'
    and E.createdtime between CONVERT(datetime, '2014-01-01', 120) and CONVERT(datetime, '2014-12-31', 120)
    and EA.deleted = 0
    and BA.bill_country    = 'IT'
    --and EA.smownerid = 6946
    
    order by user_name asc, campaignname asc