select 
A.account_main_activity AS LABEL, 
B.trans_label AS IT_IT, 
A.trans_label AS DE_DE, 
C.trans_label AS EN_US, 
D.trans_label AS FR_FR, 
E.trans_label AS ES_ES, 
F.trans_label AS PT_PT, 
G.trans_label AS RU_RU
from CRM_ACCOUNT_MAIN_ACTIVITY_QLIK A
Join CRM_ACCOUNT_MAIN_ACTIVITY_QLIK B ON B.account_main_activity = A.account_main_activity AND B.language = 'it_it'
Join CRM_ACCOUNT_MAIN_ACTIVITY_QLIK C ON C.account_main_activity = A.account_main_activity AND C.language = 'en_us'
Join CRM_ACCOUNT_MAIN_ACTIVITY_QLIK D ON D.account_main_activity = A.account_main_activity AND D.language = 'fr_fr'
Join CRM_ACCOUNT_MAIN_ACTIVITY_QLIK E ON E.account_main_activity = A.account_main_activity AND E.language = 'es_es'
Join CRM_ACCOUNT_MAIN_ACTIVITY_QLIK F ON F.account_main_activity = A.account_main_activity AND F.language = 'pt_pt'
Join CRM_ACCOUNT_MAIN_ACTIVITY_QLIK G ON G.account_main_activity = A.account_main_activity AND G.language = 'ru_ru'
WHERE A.language = 'de_de'