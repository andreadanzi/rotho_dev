// danzi.tn@201308091917


function show_points(obj, divid,points,id,showdiv) {
	if (id == "" || id == ";" || id == 'null' || id == 0)
	{
		alert(alert_arr.SELECT);
		return false;
	}
	else {
		var use_worklow = getFile("index.php?module=Accounts&action=AccountsAjax&parenttab=Marketing&file=AccRatingAjax&mode=ajax&points="+points+"&recordid="+id+"&ajxaction=DETAILVIEW");
		var e = document.getElementById(showdiv);
		if(e.className  == 'showpoints_display') {
			e.innerHTML = '';
			e.className  = 'showpoints_hidden';
		}
		else {
			e.innerHTML = use_worklow;
			e.className  = 'showpoints_display';
			var t = document.getElementById('pointstable');
			e.style.height = t.clientHeight + 8;
			e.style.width = t.clientWidth + 8;
		}
	}
}


function show_points_loaded(obj, divid,points,id,showdiv) {
	if (id == "" || id == ";" || id == 'null' || id == 0)
	{
		alert(alert_arr.SELECT);
		return false;
	}
	else {
		var e = document.getElementById(showdiv);
		if(e.className  == 'showpoints_display') {
			e.className  = 'showpoints_hidden';
		}
		else {
			e.className  = 'showpoints_display';
			var t = document.getElementById('pointstable');
			e.style.height = t.clientHeight + 8;
			e.style.width = t.clientWidth + 8;
		}
	}
}

function show_points_formload(points,id,parenttab,use_worklow) {
	
}


/*


							<div id="massedit" class="layerPopup crmvDiv" style="display:none;z-index:21;max-width:90%">									
								<table border="0" cellpadding="5" cellspacing="0" width="100%">
								<tr height="34">
									<td style="padding:5px" class="level3Bg">
										<table cellpadding="0" cellspacing="0" width="100%">
										<tr>
											<td width="80%"><b>Mass Edit - Modifica Campi</b></td>
											<td width="20%" align="right">
												<input title="Salva [Alt+S]" accessKey="S" class="crmbutton small save" onclick="jQuery('#massedit_form input[name=action]').val('MassEditSave'); if (massEditFormValidate()) jQuery('#massedit_form').submit();" type="submit" name="button" value="  Salva  " style="width:70px" >
											</td>
										</tr>
										</table>
									</td>
								</tr>
								</table>
								<div id="massedit_form_div" style="width:100%;overflow:auto"></div>									<div class="closebutton" onClick="fninvsh('massedit');"></div>
							</div>
*/