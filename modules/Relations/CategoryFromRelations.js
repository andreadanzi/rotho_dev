function return_category_from_relation(recordid,value,target_fieldname,link_from_category,link_from_other) {
	var formName = getReturnFormName();
	var form = getReturnForm(formName);
	if (form) {
		var domnode_id = form.elements[target_fieldname];
		var domnode_display = form.elements[target_fieldname+'_display'];
		if(domnode_id) domnode_id.value = recordid;
		if(domnode_display) domnode_display.value = value;
		disableReferenceField(domnode_display,domnode_id,form.elements[target_fieldname+'_mass_edit_check']);	//crmv@29190
		if (enableAdvancedFunction(form)) {
			if (form.elements['link_from_category']) {
				form.elements['link_from_category'].value = link_from_category;
			}
		}
		return true;
	} else {
		return false;
	}
}