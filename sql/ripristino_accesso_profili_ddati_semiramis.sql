-- danzi.tn@20150804 caso speciale per campi del blocco semiramis che in creazione non devono essere mostrati
-- select vtiger_field.fieldlabel, vtiger_field.sequence, vtiger_profile2field.* 
update vtiger_profile2field
set vtiger_profile2field.visible = 0
from vtiger_profile2field
join vtiger_field  on vtiger_profile2field.tabid = vtiger_field.tabid and vtiger_profile2field.fieldid = vtiger_field.fieldid
where vtiger_field.tabid=6 and vtiger_field.block = 136 -- and vtiger_field.readonly <>99
and vtiger_profile2field.visible = 1
and vtiger_profile2field.profileid in (11,12,26,28,30)
-- order by vtiger_field.sequence

-- 792,789,791,833,788,836,787


-- vtiger_profile2field.profileid in (11,12,26,28,30)
-- 751,753,750,752,763,1062