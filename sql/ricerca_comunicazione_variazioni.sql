select 
vtiger_activity.activityid, vtiger_activity.subject, vtiger_activity.date_start, vtiger_crmentity.description
from vtiger_activity
join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_activity.activityid AND vtiger_crmentity.deleted = 0
join vtiger_seactivityrel on vtiger_seactivityrel.activityid = vtiger_activity.activityid 
join vtiger_crmentity accentity on accentity.crmid = vtiger_seactivityrel.crmid AND accentity.deleted = 0
where vtiger_activity.activitytype = 'Comunicazione variazioni (Auto-gen)'
AND vtiger_activity.eventstatus = 'Planned'
AND  vtiger_activity.date_start = CONVERT (date, GETDATE())
AND  vtiger_activity.time_start <= left( CONVERT (time, GETDATE()), 5)


$order   = array("\r\n", "\n", "\r");
$tt_descr = str_replace($order,'|',$tt_description);
$arr = explode('|',$tt_descr);


-- 			$AccountFocus = CRMEntity::getInstance('Accounts');
			$AccountFocus->retrieve_entity_info_no_html($focus->column_fields['accountid'], 'Accounts');
			$AccountFocus->id = $focus->column_fields['accountid'];
			
			
<emailaddress>andrea.dnz@gmail.com</emailaddress>
<emailstatus>planned</emailstatus>
<eventtype>assigned_user_id</eventtype>
<emailtemplate>438=Comunicazione Cambio Agente</emailtemplate>
##########

preg_match_all('#<emailtemplate>(.*?)</emailtemplate>#', $html, $matches);
array $matches[1]

$html = str_get_html($content);
$el = $html->find('emailtemplate', 0);
$innertext = $el->innertext;