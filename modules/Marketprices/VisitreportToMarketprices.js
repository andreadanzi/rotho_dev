function return_account_to_marketprices(recordid,value,target_fieldname,account_id, account_name, customer_cat,country,area_mng_name,area_mng_no) {
	var formName = getReturnFormName();
	var form = getReturnForm(formName);
	if (form) {
		var domnode_id = form.elements[target_fieldname];
		var domnode_display = form.elements[target_fieldname+'_display'];
		if(domnode_id) domnode_id.value = recordid;
		if(domnode_display) domnode_display.value = value;
		disableReferenceField(domnode_display,domnode_id,form.elements[target_fieldname+'_mass_edit_check']);	//crmv@29190
		if (enableAdvancedFunction(form)) {
			//danzi.tn@20140724 revisione 2 del modulo Marketprices
			if (form.elements['accounts_customer']) {
				form.elements['accounts_customer'].value = account_id;
			}
			if (form.elements['accounts_customer_display']) {
				form.elements['accounts_customer_display'].value = account_name;
			}
			if (form.elements['customer_cat']) {
				form.elements['customer_cat'].value = customer_cat;
			}
			if (form.elements['country']) {
				form.elements['country'].value = country;
			}
			if (form.elements['area_mng_name']) {
				form.elements['area_mng_name'].value = area_mng_name;
			}
			if (form.elements['area_mng_no']) {
				form.elements['area_mng_no'].value = area_mng_no;
			}
		}
		return true;	
	} else {
		return false;
	}
}