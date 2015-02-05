
// danzi.tn@20150123 per lookup che hanno già il valore collegato alla seconda picklist
/*
 * funzione da chiamare quando la picklist obj cambia
 */
function linkedListChainChangeVisitReport( pickselection, otherval) { // crmv@30528
  pickname = 'vr_account_client_type';
  jQuery.ajax({
    url:"index.php?module=SDK&action=SDKAjax&file=examples/uitypePicklist/300Ajax",
	dataType:"json",
	type: "post",
	data: "function=linkedListGetChanges"+
		  "&modname="+encodeURIComponent('Visitreport')+  // crmv@30528
	      "&name="+encodeURIComponent(pickname)+
	      "&sel="+encodeURIComponent(pickselection),
	async: true,
	cache: false,
	//contentType: "application/json",
	success: function(res) {
	  linkedListUpdateListsVisitReport(res, otherval);
	}
  });

}


function linkedListUpdateListsVisitReport(res,  otherval) {
  if (!res) return;

  for (i=0; i<res.length; ++i) {
	
    name = res[i][0];
	if('vr_account_main_activity'==name) {
		list = res[i][1];
		list_trans = res[i][2];
		otherpl = document.getElementsByName(name);
		// take the first matching element
		if (otherpl.length > 0) {
		 otherpl = otherpl[0];
		} else {
		 // try a multiselect picklist
		 otherpl = document.getElementsByName(name+"[]");
		 if (otherpl.length > 0) otherpl = otherpl[0]; else continue;
		}
		var oldval = otherval;
		// delete inside
		otherpl.innerHTML = "";
		// re-populate
		for (j=0; j<list.length; ++j) {
		  var option = document.createElement("option");
		  option.text = list_trans[j];
		  option.value = list[j];
		  otherpl.add(option);
		  if (option.value == oldval) option.selected = true;
		}

		// change other lists
		if (otherpl.onchange) otherpl.onchange(otherpl);
	}
  }
}
// danzi.tn@20150123e

function fireOnChange(form, elementName) {
	var formelement = form.elements[elementName];
	if ("createEvent" in document) {
		var evt = document.createEvent("HTMLEvents");
		evt.initEvent("change", false, true);
		formelement.dispatchEvent(evt);
	}
	else
	{
		formelement.fireEvent("onchange");
	}
	
}

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
				form.elements['vr_account_main_activity'].selectedIndex = 0;
				form.elements['vr_account_main_activity'].options[0].value = vr_account_main_activity;
				form.elements['vr_account_client_type'].value = vr_account_client_type;
				// danzi.tn@20150123 per lookup che hanno già il valore collegato ala seconda picklist
				linkedListChainChangeVisitReport( vr_account_client_type, vr_account_main_activity);
				fireOnChange(form, 'vr_account_client_type');
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