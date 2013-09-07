/***************************************************************************************
 * The contents of this file are subject to the CRMVILLAGE.BIZ VTECRM License Agreement
 * ("licenza.txt"); You may not use this file except in compliance with the License
 * The Original Code is:  CRMVILLAGE.BIZ VTECRM
 * The Initial Developer of the Original Code is CRMVILLAGE.BIZ.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
 ***************************************************************************************/
 
//crmv@2043m
function ReplyMailConverter(id,user) {
	var record = '';
	new Ajax.Request(
		'index.php',
		{queue: {position: 'end', scope: 'command'},
			method: 'post',
			postBody: 'module=Emails&action=EmailsAjax&file=GetReplayMailId&record='+id+'&user='+user,
			onComplete: function(response) {
				record = response.responseText;
				if (record == 'helpdesk_from_empty') {
					alert(alert_arr.HelpDeskFromMail+' '+alert_arr.CANNOT_BE_EMPTY);
					return false;
				}
				window.open('index.php?module=Emails&action=EmailsAjax&file=EditView&record='+record+'&reply=true&reply_mail_converter=true&reply_mail_converter_record='+id+'&reply_mail_user='+user,'_blank');
			}
		}
	);
}
//crmv@2043me
//crmv@27618
function doNotImportAnymore(module,record) {
	openPopup('index.php?module=SDK&action=SDKAjax&file=src/modules/HelpDesk/MailScannerSpam&mode=spamedit&rel_module='+module+'&record='+record);
}
//crmv@27618e