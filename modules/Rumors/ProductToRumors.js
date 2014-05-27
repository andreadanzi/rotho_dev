function return_product_to_rumors(recordid,value,target_fieldname,product_cat,link_to_description,link_to_category_descr) {
	var formName = getReturnFormName();
	var form = getReturnForm(formName);
	if (form) {
		var domnode_id = form.elements[target_fieldname];
		var domnode_display = form.elements[target_fieldname+'_display'];
		if(domnode_id) domnode_id.value = recordid;
		if(domnode_display) domnode_display.value = value;
		disableReferenceField(domnode_display,domnode_id,form.elements[target_fieldname+'_mass_edit_check']);	//crmv@29190
		if (enableAdvancedFunction(form)) {
			if (form.elements['product_cat']) {
				form.elements['product_cat'].value = product_cat;
			}
			if (form.elements['product_desc']) {
				form.elements['product_desc'].value = link_to_description;
			}
			// danzi.tn@20140225 aggiunto descrizione categoria category_descr
			if (form.elements['product_cat_descr']) {
				form.elements['product_cat_descr'].value = link_to_category_descr;
			}
		}
		return true;	
	} else {
		return false;
	}
}