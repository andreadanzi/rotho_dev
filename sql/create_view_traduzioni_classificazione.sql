CREATE VIEW CRM_ACCOUNT_CLIENT_TYPE_QLIK AS
select distinct vtiger_account_client_type.account_client_type, sdk_language.language , convert(varchar(200), sdk_language.trans_label ) as trans_label
from vtiger_account_client_type 
JOIN sdk_language on convert(varchar(200),sdk_language.label) = vtiger_account_client_type.account_client_type 
AND sdk_language.module = 'Accounts';

CREATE VIEW CRM_ACCOUNT_MAIN_ACTIVITY_QLIK AS
select distinct vtiger_account_main_activity.account_main_activity, sdk_language.language , convert(varchar(200), sdk_language.trans_label ) as trans_label
from vtiger_account_main_activity
JOIN sdk_language on convert(varchar(200),sdk_language.label) = vtiger_account_main_activity.account_main_activity
AND sdk_language.module = 'Accounts';

CREATE VIEW CRM_ACCOUNT_SEC_ACTIVITY_QLIK AS
select distinct vtiger_account_sec_activity.account_sec_activity, sdk_language.language , convert(varchar(200), sdk_language.trans_label ) as trans_label
from vtiger_account_sec_activity
JOIN sdk_language on convert(varchar(200),sdk_language.label) = vtiger_account_sec_activity.account_sec_activity 
AND sdk_language.module = 'Accounts';