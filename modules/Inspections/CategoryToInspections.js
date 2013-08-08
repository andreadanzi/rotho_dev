function return_category_to_inspection(recordid,value,target_fieldname,account_cat,link_to_other,link_to_baselang,link_to_extcode,link_to_account_ownerid,link_to_account_ownerid_display) {
	var formName = getReturnFormName();
	var form = getReturnForm(formName);
	if (form) {
		var domnode_id = form.elements[target_fieldname];
		var domnode_display = form.elements[target_fieldname+'_display'];
		if(domnode_id) domnode_id.value = recordid;
		if(domnode_display) domnode_display.value = value;
		disableReferenceField(domnode_display,domnode_id,form.elements[target_fieldname+'_mass_edit_check']);	//crmv@29190
		if (enableAdvancedFunction(form)) {
			if (form.elements['account_cat']) {
				form.elements['account_cat'].value = account_cat;
			}
			if (form.elements['account_email']) {
				form.elements['account_email'].value = link_to_other;
			}
			if (form.elements['account_baselang']) {
				form.elements['account_baselang'].value = link_to_baselang;
			}
			if (form.elements['account_external_code']) {
				form.elements['account_external_code'].value = link_to_extcode;
			}
			if (form.elements['assigned_user_id']) {
				form.elements['assigned_user_id'].value = link_to_account_ownerid;
			}
			if (form.elements['assigned_user_id_display']) {
				form.elements['assigned_user_id_display'].value = link_to_account_ownerid_display;
			}
		}
		return true;	
	} else {
		return false;
	}
}