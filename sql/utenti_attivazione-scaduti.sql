/****** Script for SelectTopNRows command from SSMS  ******/
SELECT TOP 1000 userid
      ,last_login
      ,last_change_pwd
  FROM vte_check_pwd where userid = 35

--   select * from vte40_387.dbo.vtiger_users where user_name like '%robertb%' -- 35
  
  update [vte40_387].[dbo].[vte_check_pwd] set last_login = GETDATE()
  where  userid = 35