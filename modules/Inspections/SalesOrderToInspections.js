function return_salesorder_to_inspection(recordid,value,target_fieldname,salesorder_date,link_to_other) {
	var formName = getReturnFormName();
	var form = getReturnForm(formName);
	if (form) {
		var domnode_id = form.elements[target_fieldname];
		var domnode_display = form.elements[target_fieldname+'_display'];
		if(domnode_id) domnode_id.value = recordid;
		if(domnode_display) domnode_display.value = value;
		disableReferenceField(domnode_display,domnode_id,form.elements[target_fieldname+'_mass_edit_check']);	//crmv@29190
		if (enableAdvancedFunction(form)) {
			if (form.elements['salesorder_date']) {
				form.elements['salesorder_date'].value = salesorder_date;
			}
		}
		return true;	
	} else {
		return false;
	}
}