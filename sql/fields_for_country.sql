-- danzi.tn@20131206 procedura cambio codici nazione
select 
vtiger_tab.name as NomeModulo,
vtiger_field.fieldid, vtiger_field.columnname, vtiger_field.tablename, vtiger_field.fieldname,
vtiger_field.fieldlabel ,vtiger_field.uitype
 from vtiger_field
JOIN vtiger_tab on vtiger_tab.tabid = vtiger_field.tabid
 where vtiger_field.columnname like '%countr%'