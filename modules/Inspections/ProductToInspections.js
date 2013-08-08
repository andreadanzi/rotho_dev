function return_product_to_inspection(recordid,value,target_fieldname,product_cat,link_to_other,link_to_vendor,link_to_description) {
	var formName = getReturnFormName();
	var form = getReturnForm(formName);
	if (form) {
		var domnode_id = form.elements[target_fieldname];
		var domnode_display = form.elements[target_fieldname+'_display'];
		if(domnode_id) domnode_id.value = recordid;
		if(domnode_display) domnode_display.value = value;
		disableReferenceField(domnode_display,domnode_id,form.elements[target_fieldname+'_mass_edit_check']);	//crmv@29190
		if (enableAdvancedFunction(form)) {
			if (form.elements['product_category']) {
				form.elements['product_category'].value = product_cat;
			}
			if (form.elements['vendor_id']) {
				form.elements['vendor_id'].value = link_to_vendor;
			}
			if (form.elements['inspection_frequency']) {
				form.elements['inspection_frequency'].value = link_to_other;
			}
			if (form.elements['product_description']) {
				form.elements['product_description'].value = link_to_description;
			}
		}
		return true;	
	} else {
		return false;
	}
}