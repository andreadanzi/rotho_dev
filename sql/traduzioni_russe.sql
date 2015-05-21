select 
'SDK::setLanguageEntry("' +
sdk_language.module + '","ru_ru","'+
convert(varchar,sdk_language.label) + '","' +
convert(varchar,sdk_language.trans_label) + '");',
'xxx' as trans_label_xxx,
sdk_language.label as trans_label,
rulang.trans_label as ru_label,
rulang.languageid as ru_id

from sdk_language
LEFT JOIN sdk_language rulang ON rulang.module =  sdk_language.module 
                                 AND rulang.language = 'ru_ru'
                                 AND convert(varchar,rulang.label) = convert(varchar,sdk_language.label)
where 
sdk_language.language = 'it_it' 
and sdk_language.module in ( 'APP_STRINGS','APP_LIST_STRINGS', 'ALERT_ARR', 'Calendar', 'Accounts', 'Contacts','Visitreport','HelpDesk')
-- sdk_language.language = 'it_it' and sdk_language.module in ( 'Accounts')
AND (rulang.languageid IS NULL OR  convert(varchar,rulang.trans_label) = '')
AND sdk_language.trans_label IS NOT NULL
AND  convert(varchar,sdk_language.trans_label) <> ''
