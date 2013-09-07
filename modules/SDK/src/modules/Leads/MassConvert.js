/***************************************************************************************
 * The contents of this file are subject to the CRMVILLAGE.BIZ VTECRM License Agreement
 * ("licenza.txt"); You may not use this file except in compliance with the License
 * The Original Code is:  CRMVILLAGE.BIZ VTECRM
 * The Initial Developer of the Original Code is CRMVILLAGE.BIZ.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
 ***************************************************************************************/
 
/* mycrmv@2707m */

function enableMassAssignedTo(checked) {
	if (checked == true) {
		getObj('massconvert_assigned_to').show();
	} else {
		getObj('massconvert_assigned_to').hide();
	}
}
function mass_convert(obj) {
	var idstring = get_real_selected_ids('Leads');
	if (idstring.substr('0','1')==";") {
		idstring = idstring.substr('1');
	}
	var idarr = idstring.split(';');
	var count = idarr.length;
	var count = count - 1;
	if (idstring == "" || idstring == ";" || idstring == 'null') {
		alert(alert_arr.SELECT);
		return false;
	} else if (count > 50) {
		alert(alert_arr.LBL_MASSCONVERT_LIMIT+' '+count);
		return false;
	} else {
		create_mass_convert_div();
		mass_convert_formload(idstring);
	}
	fnvshobj(obj, 'massconvert');
}
function create_mass_convert_div() {
	if (!jQuery('#Buttons_List_3').find('#massconvert')[0]) {
		var mass_convert_div = '<div id="massconvert" class="layerPopup crmvDiv" style="display:none;z-index:21;">'+
			'<table border="0" cellpadding="5" cellspacing="0" width="100%">'+
			'<tr height="34">'+
			'	<td style="padding:5px" class="level3Bg">'+
			'		<table cellpadding="0" cellspacing="0" width="100%">'+
			'		<tr>'+
			'			<td width="80%"><b>'+alert_arr.LBL_MASSCONVERT_FORM_HEADER+'</b></td>'+
			'			<td width="20%" align="right">'+
			'				<input title="OK" class="crmbutton small save" onclick="jQuery(\'#massconvert_form\').submit();" type="button" name="button" value="  OK  " style="width:70px" >'+
			'			</td>'+
			'		</tr>'+
			'		</table>'+
			'	</td>'+
			'</tr>'+
			'</table>'+
			'<div id="massconvert_form_div"></div>'+
			'<div class="closebutton" onClick="fninvsh(\'massconvert\');"></div>'+
			'</div>';
		jQuery('#Buttons_List_3').append(mass_convert_div);
	}
}
function mass_convert_formload(idstring) {
	if(typeof(parenttab) == 'undefined') parenttab = '';
	$("status").style.display="inline";
	new Ajax.Request(
		'index.php',
		{queue: {position: 'end', scope: 'command'},
	    	method: 'post',
			postBody:"module=SDK&action=SDKAjax&file=src/modules/Leads/MassConvert&mode=ajax",
				onComplete: function(response) {
                	$("status").style.display="none";
               	    var result = response.responseText;
                    $("massconvert_form_div").innerHTML= result;
				}
		}
	);
}