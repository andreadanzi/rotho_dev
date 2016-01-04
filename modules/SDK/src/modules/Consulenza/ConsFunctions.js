//crmv@23526
// danzi.tn@20160104 passaggio in produzione albero utenti
function set_extra_info(recordid,value,target_fieldname,acc_id,capoarea){
	var formName = getReturnFormName();
	var form = getReturnForm(formName);
	if (form) {
		var domnode_id = form.elements[target_fieldname];
		var domnode_display = form.elements[target_fieldname+'_display'];
		if(domnode_id) domnode_id.value = recordid;
		if(domnode_display) domnode_display.value = value.replace(/&amp;/g, '&');
		//agente riferimento
		if (acc_id != '') {
			var agente_id = form.elements['agente_riferimento'];
			if(agente_id) agente_id.value = acc_id;
		}
		//capoarea
		if (capoarea != '') {
			var capoarea_id = form.elements['capoarea'];
			if(capoarea_id) capoarea_id.value = capoarea;
		}
		disableReferenceField(domnode_display);
		return true;
	} else {
		return false;
	}
}

function set_helpdesk_info(recordid,value,target_fieldname,acc_id){
	if ((parent.document.QcEditView) && (jQuery('#qcform', parent.document).css("display") != 'none')) {
		var domnode_id = parent.document.QcEditView[target_fieldname];
		var domnode_display = parent.document.QcEditView[target_fieldname+'_display'];
		if(domnode_id) domnode_id.value = recordid;
		if(domnode_display) domnode_display.value = value;
		//assegnatario
		//var accdomnode_id = parent.document.QcEditView['assigned_user_id'];
		//if(accdomnode_id) accdomnode_id.value = acc_id;
		//nome campo 68
		var accdomnode_name = parent.document.EditView['parent_name'];
		if(accdomnode_name) accdomnode_name.value = value;
		//agente riferimento
		var agente_id = parent.document.QcEditView['agente_riferimento_rec'];
		if(agente_id) agente_id.value = acc_id;

		return true;
	}else if(parent.document.EditView) {
		var domnode_id = parent.document.EditView[target_fieldname];
		var domnode_display = parent.document.EditView[target_fieldname+'_display'];
		if(domnode_id) domnode_id.value = recordid;
		if(domnode_display) domnode_display.value = value;
		//assegnatario
		//var accdomnode_id = parent.document.EditView['assigned_user_id'];
		//if(accdomnode_id) accdomnode_id.value = acc_id;
		//nome campo 68
		var accdomnode_name = parent.document.EditView['parent_name'];
		if(accdomnode_name) accdomnode_name.value = value;
		//agente riferimento
		var agente_id = parent.document.EditView['agente_riferimento_rec'];
		if(agente_id) agente_id.value = acc_id;

		return true;
	} else {
		return false;
	}
}
//crmv@23526e

function set_product_to_helpdesk(product_id, description, base_number, categoria) {
	// danzi.tn@20151127 valore di default null se undefined
	categoria = categoria || null;
	// danzi.tn@20151127e
	//crmv@29190
	var formName = getReturnFormName();
	var form = getReturnForm(formName);
	//crmv@29190e
	form.product_id_display.value = base_number;
	form.cf_771.value = description;
	form.product_id.value = product_id;
	if (categoria != null)
	{
		form.cf_1060.value = categoria;
	}
	disableReferenceField(form.parent_name);	//crmv@29190
}

//mycrmv@24524
function changeRichiedente(objFlag,destField){
	if (objFlag.checked) {
		//flag attivo
		getObj('parent').value = '';
		getObj('contact').value = '';
		getObj('parent_display').value = '';
		getObj('contact_display').value = '';

		disableReferenceField(getObj('parent_display'));
		disableReferenceField(getObj('contact_display'));

		jQuery('#parent').parent('td').children('img').hide();
		jQuery('#parent').parent('td').children('input[type="image"]').hide();

		jQuery('#contact').parent('td').children('img').hide();
		jQuery('#contact').parent('td').children('input[type="image"]').hide();

	} else {
		//flag disattivo
		if (getObj('parent').value == '') {
			enableReferenceField(getObj('parent_display'));
		}
		if (getObj('contact').value == '') {
			enableReferenceField(getObj('contact_display'));
		}
		jQuery('#parent').parent('td').children('img').show();
		jQuery('#parent').parent('td').children('input[type="image"]').show();

		jQuery('#contact').parent('td').children('img').show();
		jQuery('#contact').parent('td').children('input[type="image"]').show();
	}
}
//mycrmv@24524e

//mycrmv@rotho_blaas
function changeCapoarea() {
	var agente = jQuery('[name="agente_riferimento"]').val();
	url = 'module=Consulenza&action=ConsulenzaAjax&file=crmv_capoarea&role_id='+agente;
	new Ajax.Request(
		'index.php',
		{queue: {position: 'end', scope: 'command'},
		method: 'post',
		postBody:url,
		onComplete: function(response) {
			var str = response.responseText
			if(str) {
				getObj('capoarea').value = str;
			}
		}
	});
}
//mycrmv@rotho_blaase
