/***************************************************************************************
 * The contents of this file are subject to the CRMVILLAGE.BIZ VTECRM License Agreement
 * ("licenza.txt"); You may not use this file except in compliance with the License
 * The Original Code is:  CRMVILLAGE.BIZ VTECRM
 * The Initial Developer of the Original Code is CRMVILLAGE.BIZ.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
 ***************************************************************************************/
//crmv@36406
function return_parent_to_helpdesk(recordid,value,target_fieldname, userid) {
	var formName = getReturnFormName();
	var form = getReturnForm(formName);
	if (form) {
		var domnode_id = form.elements[target_fieldname];
		var domnode_display = form.elements[target_fieldname+'_display'];
		if(domnode_id) domnode_id.value = recordid;
		if(domnode_display) domnode_display.value = value.replace(/&amp;/g, '&');
		if (userid != '') {
			var agente_id = form.elements['agente_riferimento_rec'];
			if(agente_id) agente_id.value = userid;
		}
		disableReferenceField(domnode_display);
		return true;
	} else {
		return false;
	}
}
//crmv@36406e