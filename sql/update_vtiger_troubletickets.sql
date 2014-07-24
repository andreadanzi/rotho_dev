UPDATE
vtiger_troubletickets
SET 
vtiger_troubletickets.area_mng_no = CASE WHEN vtiger_account.area_mng_no <> '' THEN vtiger_account.area_mng_no ELSE vtiger_users.agent_cod_capoarea END,
vtiger_troubletickets.area_mng_name = CASE WHEN vtiger_account.area_mng_name <> '' THEN vtiger_account.area_mng_name ELSE amuser.first_name + ' '+ amuser.last_name  END
FROM
vtiger_troubletickets
JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_troubletickets.ticketid AND vtiger_crmentity.deleted = 0
LEFT JOIN vtiger_account on vtiger_account.accountid = vtiger_troubletickets.parent_id
LEFT JOIN vtiger_crmentity accentity ON accentity.crmid = vtiger_account.accountid
LEFT JOIN vtiger_users ON vtiger_users.id = accentity.smownerid
LEFT JOIN vtiger_users as amuser on amuser.erp_code = vtiger_users.agent_cod_capoarea AND vtiger_users.agent_cod_capoarea <> ''
WHERE
vtiger_account.accountid IS NOT NULL AND (
vtiger_troubletickets.area_mng_no = '' OR 
vtiger_troubletickets.area_mng_no IS NULL)