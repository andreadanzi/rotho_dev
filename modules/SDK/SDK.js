function get_js_lang(){
	jQuery.ajax({
		url:"index.php?module=SDK&action=SDKAjax&file=GetJsLang",
		dataType:"json",
		type: "post",
		async: true,
	  	success: function(alert_msg){
	  		alert_arr = new Array();
	  		alert_arr = alert_msg;
  		}
	});
}
function getSDKUitype(uitype) {
	if (sdk_js_uitypes[uitype] != undefined) {
		return sdk_js_uitypes[uitype];
	} else {
		return '';
	}
}
function SDKValidate(form) {
	if (form == undefined || form == '') {
		form = this.document.EditView;
	}
	if (form == undefined) {
		return false;
	}
	if (top.sdk_js_presave != undefined) {
		var exists_pre_save = false;
		for (i in top.sdk_js_presave) {
            // danzi.tn@20151019
            var moduleVal = form.module.value;
            if(form.module.length !== 'undefined' && form.module.length > 0) moduleVal = form.module[0].value;
			if (top.sdk_js_presave[i]['module'] == moduleVal) {
				exists_pre_save = true;
				break;
			} 
		}
		if (exists_pre_save == false) {
			return false;
		}
	}
    var url = '';
 	var inputs = jQuery(form).serializeArray();
	jQuery.each(inputs, function(i, field) {
    	url += '&sdk_par_'+field.name+'='+encodeURIComponent(field.value);
	});
 	var inputs_checkbox = jQuery(form).find(':checkbox');
	jQuery.each(inputs_checkbox, function(i, field) {
		var value = 0;
		if (field.checked) value = 1;
    	url += '&sdk_par_'+field.name+'='+value;
	});
	//crmv@26919
	var force_false = false;
	var response = jQuery.ajax({
	//crmv@26919e
		url:'index.php?module=SDK&action=SDKAjax&file=Validate&form='+form.name,
		dataType:"json",
		type: "post",
		async: false,
		data: url,
	  	success: function(data,textStatus){
	  		if (textStatus == 'success') {
	  			if (data['changes'] != '' && jQuery(data['changes']).length > 0) {
  					jQuery.each(data['changes'], function(field,value){
  						jQuery('[name="'+form.name+'"] :input[name="'+field+'"]').val(value);
  					})
  				}
  				if(data['focus'] != '') {
  					jQuery('[name="'+form.name+'"] :input[name="'+data['focus']+'"]').focus();
  				}
  				//crmv@26919
  				if (data['confirm']){
  					if (!confirm(data['message'])){
  						force_false = true;
  					}
  				}
  				else if (data['message'] != '') {
  				//crmv@26919e
  					alert(data['message']);
  				}
	  		}
  		}
	});
	//crmv@26919
	if (force_false){
		var respose2 = new Object();
		var data = eval("("+response.responseText+")");
		data['status'] = false;
		respose2.responseText = JSON.stringify(data);
		response = respose2;
	}
	return response;
	//crmv@26919e
}
function getSDKHomeIframe(stuffid) {
	var data = getFile('index.php?module=SDK&action=SDKAjax&file=GetHomeIframe&stuffid='+stuffid);
	return eval('('+data+')');
}