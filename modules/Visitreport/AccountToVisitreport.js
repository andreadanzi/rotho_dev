// danzi.tn@20141217 nuova classificazione da report visite
function return_visitreport_to_account(recordid,value,target_fieldname,vr_account_line,vr_account_client_type,vr_account_main_activity,vr_account_sec_activity,vr_account_brand,vr_account_yearly_pot,vr_area_intervento,country,area_mng_name,area_mng_no) {
	var formName = getReturnFormName();
	var form = getReturnForm(formName);
	if (form) {
		var domnode_id = form.elements[target_fieldname];
		var domnode_display = form.elements[target_fieldname+'_display'];
		if(domnode_id) domnode_id.value = recordid;
		if(domnode_display) domnode_display.value = value;
		disableReferenceField(domnode_display,domnode_id,form.elements[target_fieldname+'_mass_edit_check']);	//crmv@29190
		if (enableAdvancedFunction(form)) {
			if (form.elements['vr_account_line']) {
				form.elements['vr_account_line'].value = vr_account_line;
			}
			if (form.elements['vr_account_client_type']) {
				form.elements['vr_account_client_type'].value = vr_account_client_type;
			}
			if (form.elements['vr_account_main_activity']) {
				form.elements['vr_account_main_activity'].value = vr_account_main_activity;
			}
			if (form.elements['vr_account_sec_activity']) {
				form.elements['vr_account_sec_activity'].value = vr_account_sec_activity;
			}
			if (form.elements['vr_account_brand']) {
				form.elements['vr_account_brand'].value = vr_account_brand;
			}
			if (form.elements['vr_account_yearly_pot']) {
				form.elements['vr_account_yearly_pot'].value = vr_account_yearly_pot;
			}
			if (form.elements['vr_area_intervento']) {
				form.elements['vr_area_intervento'].value = vr_area_intervento;
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