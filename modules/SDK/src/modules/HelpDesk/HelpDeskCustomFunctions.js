/***************************************************************************************
 * The contents of this file are subject to the CRMVILLAGE.BIZ VTECRM License Agreement
 * ("licenza.txt"); You may not use this file except in compliance with the License
 * The Original Code is:  CRMVILLAGE.BIZ VTECRM
 * The Initial Developer of the Original Code is CRMVILLAGE.BIZ.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
 ***************************************************************************************/
//crmv@36406
// danzi.tn@20140630 decodifica area_mng_name e area_mng_no
function return_parent_to_helpdesk(recordid,value,target_fieldname, userid,area_mng_name,area_mng_no) {
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
		if (form.elements['area_mng_name']) {
			form.elements['area_mng_name'].value = area_mng_name;
		}
		if (form.elements['area_mng_no']) {
			form.elements['area_mng_no'].value = area_mng_no;
		}
		disableReferenceField(domnode_display);
		return true;
	} else {
		return false;
	}
}
//crmv@36406e