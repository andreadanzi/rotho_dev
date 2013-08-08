function return_category_to_nonconformity(recordid,value,target_fieldname,product_description,product_category, vendor_id, vendor_descr) {
	var formName = getReturnFormName();
	var form = getReturnForm(formName);
	if (form) {
		var domnode_id = form.elements[target_fieldname];
		var domnode_display = form.elements[target_fieldname+'_display'];
		if(domnode_id) domnode_id.value = recordid;
		if(domnode_display) domnode_display.value = value;
		disableReferenceField(domnode_display,domnode_id,form.elements[target_fieldname+'_mass_edit_check']);	//crmv@29190
		if (enableAdvancedFunction(form)) {
			if (form.elements['product_description']) {
				form.elements['product_description'].value = product_description;
			}
			if (form.elements['product_category']) {
				form.elements['product_category'].value = product_category;
			}
			if (form.elements['vendor_id']) {
				form.elements['vendor_id'].value = vendor_id;
			}
			if (form.elements['vendor_id_display']) {
				form.elements['vendor_id_display'].value = vendor_descr;
			}
		}
		return true;
	} else {
		return false;
	}
}