/*********************************************************************************

** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
// danzi.tn@20150331 modifica allo slider, per step da 500 euro
 //crmv@add ajax control
var ajaxcall_list = null;
var ajaxcall_count = null;

/* crmv@30967 */
var typeofdata = new Array();
typeofdata['E'] = ['e','n','s','ew','c','k'];
typeofdata['V'] = ['e','n','s','ew','c','k'];
typeofdata['N'] = ['e','n','l','g','m','h'];
typeofdata['NN'] = ['e','n','l','g','m','h'];
typeofdata['T'] = ['e','n','l','g','m','h'];
typeofdata['I'] = ['e','n','l','g','m','h'];
typeofdata['C'] = ['e','n'];
typeofdata['DT'] = ['e','n','l','g','m','h'];
typeofdata['D'] = ['e','n','l','g','m','h'];
var fLabels = new Array();
if (typeof(alert_arr) !== 'undefined') {
	fLabels['e'] = alert_arr.EQUALS;
	fLabels['n'] = alert_arr.NOT_EQUALS_TO;
	fLabels['s'] = alert_arr.STARTS_WITH;
	fLabels['ew'] = alert_arr.ENDS_WITH;
	fLabels['c'] = alert_arr.CONTAINS;
	fLabels['k'] = alert_arr.DOES_NOT_CONTAINS;
	fLabels['l'] = alert_arr.LESS_THAN;
	fLabels['g'] = alert_arr.GREATER_THAN;
	fLabels['m'] = alert_arr.LESS_OR_EQUALS;
	fLabels['h'] = alert_arr.GREATER_OR_EQUALS;
}
/* crmv@30967e */

//crmv@add ajax control end
// MassEdit Feature
function massedit_togglediv(curTabId,total){

   for(var i=0;i<total;i++){
	tagName = $('massedit_div'+i);
	tagName1 = $('tab'+i)
	tagName.style.display = 'none';
	tagName1.className = 'dvtUnSelectedCell';
   }

   tagName = $('massedit_div'+curTabId);
   tagName.style.display = 'block';
   tagName1 = $('tab'+curTabId)
   tagName1.className = 'dvtSelectedCell';
}

function massedit_initOnChangeHandlers() {
	var form = document.getElementById('massedit_form');
	// Setup change handlers for input boxes
	var inputs = form.getElementsByTagName('input');
	for(var index = 0; index < inputs.length; ++index) {
		var massedit_input = inputs[index];
		// TODO Onchange on readonly and hidden fields are to be handled later.
		massedit_input.onchange = function() {
			var checkbox = document.getElementById(this.name + '_mass_edit_check');
			if(checkbox) checkbox.checked = true;
		}
	}
	// Setup change handlers for select boxes
	var selects = form.getElementsByTagName('select');
	for(var index = 0; index < selects.length; ++index) {
		var massedit_select = selects[index];
		massedit_select.onchange = function() {
			var checkbox = document.getElementById(this.name + '_mass_edit_check');
			if(checkbox) checkbox.checked = true;
		}
	}
}
//crmv@fix mass_edit
function mass_edit(obj,divid,module,parenttab) {
	var idstring = get_real_selected_ids(module);
	if (idstring.substr('0','1')==";")
		idstring = idstring.substr('1');
	var idarr = idstring.split(';');
	var count = idarr.length;
	var xx = count-1;
	if (idstring == "" || idstring == ";" || idstring == 'null')
	{
		alert(alert_arr.SELECT);
		return false;
	}
	else {
		//crmv@27096
		var use_worklow = getFile("index.php?module="+encodeURIComponent(module)+"&action="+encodeURIComponent(module+'Ajax')+"&parenttab="+encodeURIComponent(parenttab)+"&file=MassEdit&mode=ajax&check_count=true");
		use_worklow = use_worklow.split('###');
		if (use_worklow[0] == 'no_worklow') {
			if (confirm(alert_arr.LBL_MASS_EDIT_WITHOUT_WF_1+use_worklow[1]+alert_arr.LBL_MASS_EDIT_WITHOUT_WF_2) == false) {
				return false;
			}
		}
		mass_edit_formload(idstring,module,parenttab,use_worklow[0]);
		//crmv@27096e
	}
	fnvshobj(obj, divid);
}
//crmv@fix mass_edit end
function mass_edit_formload(idstring,module,parenttab,use_worklow) {	//crmv@27096
	if(typeof(parenttab) == 'undefined') parenttab = '';
	$("status").style.display="inline";
	new Ajax.Request(
		'index.php',
		{queue: {position: 'end', scope: 'command'},
	    	method: 'post',
			postBody:"module="+encodeURIComponent(module)+"&action="+encodeURIComponent(module+'Ajax')+"&parenttab="+encodeURIComponent(parenttab)+"&file=MassEdit&mode=ajax&use_worklow="+use_worklow,	//crmv@27096
				onComplete: function(response) {
                	$("status").style.display="none";
               	    var result = response.responseText;
                    $("massedit_form_div").innerHTML= result;
					//$("massedit_form")["massedit_recordids"].value = idstring;	//crmv@27096
					$("massedit_form")["massedit_module"].value = module;
					//crmv@29190
					var scriptTags = $("massedit_form_div").getElementsByTagName("script");
					for(var i = 0; i< scriptTags.length; i++){
						var scriptTag = scriptTags[i];
						eval(scriptTag.innerHTML);
						if (scriptTag.id == 'massedit_javascript') {
							// Updating global variables
							fieldname = mass_fieldname;
							for(var i=0;i<fieldname.length;i++){
								calendar_jscript = $('massedit_calendar_'+fieldname[i]);
								if(calendar_jscript){
									eval(calendar_jscript.innerHTML);
								}
							}
							fieldlabel = mass_fieldlabel;
							fielddatatype = mass_fielddatatype;
							count = mass_count;
						}
					}
                    eval($("massedit_form_div"));
                    //crmv@29190e
				}
		}
	);
}
function mass_edit_fieldchange(selectBox) {
	var oldSelectedIndex = selectBox.oldSelectedIndex;
	var selectedIndex = selectBox.selectedIndex;

	if($('massedit_field'+oldSelectedIndex)) $('massedit_field'+oldSelectedIndex).style.display='none';
	if($('massedit_field'+selectedIndex)) $('massedit_field'+selectedIndex).style.display='block';

	selectBox.oldSelectedIndex = selectedIndex;
}

function mass_edit_save(){
	var masseditform = $("massedit_form");
	var module = masseditform["massedit_module"].value;
	var viewid = document.getElementById("viewname").options[document.getElementById("viewname").options.selectedIndex].value;
	var searchurl = document.getElementById("search_url").value;

	var urlstring =
		"module="+encodeURIComponent(module)+"&action="+encodeURIComponent(module+'Ajax')+
		"&return_module="+encodeURIComponent(module)+"&return_action=ListView"+
		"&mode=ajax&file=MassEditSave&viewname=" + viewid ;//+"&"+ searchurl;

	fninvsh("massedit");
	new Ajax.Request(
		"index.php",
		{queue:{position:"end", scope:"command"},
			method:"post",
			postBody:urlstring,
			onComplete:function (response) {
				$("status").style.display = "none";
				var result = response.responseText.split("&#&#&#");
				$("ListViewContents").innerHTML = result[2];
				if (result[1] != "") {
					alert(result[1]);
				}
				$("basicsearchcolumns").innerHTML = "";
			}
		}
	);

}
function ajax_mass_edit() {
	$("status").style.display = "inline";

	var masseditform = $("massedit_form");
	var module = masseditform["massedit_module"].value;

	var viewid = document.getElementById("viewname").options[document.getElementById("viewname").options.selectedIndex].value;
	var idstring = masseditform["massedit_recordids"].value;
	var searchurl = document.getElementById("search_url").value;
	var tplstart = "&";
	if (gstart != "") { tplstart = tplstart + gstart; }

	var masseditfield = masseditform['massedit_field'].value;
	var masseditvalue = masseditform['massedit_value_'+masseditfield].value;

	var urlstring =
		"module="+encodeURIComponent(module)+"&action="+encodeURIComponent(module+'Ajax')+
		"&return_module="+encodeURIComponent(module)+
		"&mode=ajax&file=MassEditSave&viewname=" + viewid +
		"&massedit_field=" + encodeURIComponent(masseditfield) +
		"&massedit_value=" + encodeURIComponent(masseditvalue) +
	   	"&idlist=" + idstring + searchurl;

	fninvsh("massedit");

	new Ajax.Request(
		"index.php",
		{queue:{position:"end", scope:"command"},
			method:"post",
			postBody:urlstring,
			onComplete:function (response) {
				$("status").style.display = "none";
				var result = response.responseText.split("&#&#&#");
				$("ListViewContents").innerHTML = result[2];
				if (result[1] != "") {
					alert(result[1]);
				}
				$("basicsearchcolumns").innerHTML = "";
			}
		}
	);
}

// END

function change(obj,divid)
{
//crmv@7216
		var select_options  =  document.getElementsByName('selected_id');
		var x = select_options.length;
		var viewid =getviewId();
		idstring = "";
        xx = 0;
        for(i = 0; i < x ; i++)
        {
        	if(select_options[i].checked)
            {
            	idstring = select_options[i].value +";"+idstring
                xx++
            }
        }
		idlen=idstring.length;
		str=idstring.substr(1,(idlen-2));
		idarr=str.split(";");
      xx=idarr.length;
//crmv@7216e
        if (xx != 0 && idstring !="" && idstring !=";" && idstring != 'null')
        {
            document.getElementById('selected_ids').value=idstring;
        }
        else
        {
            alert(alert_arr.SELECT);
            return false;
        }
  fnvshobj(obj,divid);
}
function getviewId()
{
        if(isdefined("viewname"))
        {
                var oViewname = document.getElementById("viewname");
                var viewid = oViewname.options[oViewname.selectedIndex].value;
        }
        else
        {
                var viewid ='';
        }
        return viewid;
}
var gstart='';
//crmv@fix massdelete
//crmv@30967
function massDelete(module) {

	var idstring = get_real_selected_ids(module);
	if (idstring.substr('0', '1') == ";")
		idstring = idstring.substr('1');
	var idarr = idstring.split(';');
	var count = idarr.length;
	var xx = count - 1;
	var viewid = getviewId();
	if (idstring == "" || idstring == ";" || idstring == 'null') {
		alert(alert_arr.SELECT);
		return false;
	} else {
		var alert_str = alert_arr.DELETE + xx + alert_arr.RECORDS;

		if (module == "Accounts")
			alert_str = alert_arr.DELETE_ACCOUNT + xx + alert_arr.RECORDS;
		else if (module == "Vendors")
			alert_str = alert_arr.DELETE_VENDOR + xx + alert_arr.RECORDS;

		if (confirm(alert_str)) {
			var postbody = "module=Users&action=massdelete&return_module="
					+ module + "&" + gstart + "&viewname=" + viewid; // crmv@27096
			var postbody2 = "module=" + module + "&action=" + module
					+ "Ajax&file=ListViewByProduct&ajax=true&" + gstart + "&viewname="
					+ viewid;

			$("status").style.display = "inline";
			new Ajax.Request('index.php', {
				queue : {
					position : 'end',
					scope : 'command'
				},
				method : 'post',
				postBody : postbody,
				onComplete : function(response) {
					$("status").style.display = "none";
					result = response.responseText.split('&#&#&#');
					$("ListViewContents").innerHTML = result[2];
					if (result[1] != '')
						alert(result[1]);

					$('basicsearchcolumns').innerHTML = '';
					update_navigation_values(postbody2);
				}
			});
		} else {
			return false;
		}
	}
}
//crmv@30967e
//crmv@fix massdelete end
//crmv@customview fix
function showDefaultCustomView(selectView,module,parenttab, folderid) // crmv@30967
{
	$("status").style.display="inline";
	if (ajaxcall_list){
		ajaxcall_list.abort();
	}
	if (ajaxcall_count){
		ajaxcall_count.abort();
	}
	//crmv@7634
	var userid_url = ""
	var userid_obj = getObj("lv_user_id");
	if(userid_obj != null) {
		//crmv@29682
		if (navigator.appName == 'Microsoft Internet Explorer') {
			if (typeof(userid_obj.options) != 'undefined') {
				userid_url = "&lv_user_id="+userid_obj.options[userid_obj.options.selectedIndex].value;
			}else {
				userid_url = "&lv_user_id="+userid_obj.item(0).options[userid_obj.item(0).options.selectedIndex].value;
			}
		} else {
			userid_url = "&lv_user_id="+userid_obj.options[userid_obj.options.selectedIndex].value;
		}
		//crmv@29682e
	}
	override_orderby="";
	if(selectView == null)
		selectView = getObj("viewname")
	else
		override_orderby="&override_orderby=true";
	//crmv@7634e
	//crmv@23640
		/*
    if(isdefined('search_url'))
    	urlstring = $('search_url').value;
    else
    */
    //crmv@23640 end
    	urlstring = '';
        var viewName = selectView.options[selectView.options.selectedIndex].value;
		postbody="module="+module+"&action="+module+"Ajax&file=ListViewByProduct&ajax=true&changecustomview=true&start=1&viewname="+viewName+"&parenttab="+parenttab+userid_url+urlstring+override_orderby; //crmv@7634
		if (folderid != undefined && folderid != '') postbody += '&folderid='+folderid; // crmv@30967

		// crmv@31245
		var searchrest = jQuery.data(document.getElementById('basic_search_text'), 'restored');
		var searchval = jQuery('#basic_search_text').val();
		if (searchrest == false && searchval != '') {
			postbody += '&searchtype=BasicSearch&search_field=&query=true&search_text='+encodeURIComponent(searchval);
		}
		// crmv@31245e
		// danzi.tn@13022013
		var stdValueFilterField = jQuery( "#stdValueFilterField" ),
			valueId = jQuery( "#valueId" ),
			proddate_start = jQuery( "#jscal_field_proddate_start" ),
			proddate_end = jQuery( "#jscal_field_proddate_end" ),
			amount_value = jQuery("#amount_value");
		var myparms = "&filter_type=" +stdValueFilterField.val()+ "&filter_value="+ valueId.val()+ "&startdate="+ proddate_start.val()+ "&enddate="+ proddate_end.val()+ "&amountrange="+ amount_value.val();
		postbody += myparms;
		// danzi.tn@13022013 e

        new Ajax.Request(
				'index.php',
                {queue: {position: 'end', scope: 'command'},
                        method: 'post',
                        postBody: postbody,
                        onComplete: function(response) {
	                        $("status").style.display="none";
	                        result = response.responseText.split('&#&#&#');
	                        $("ListViewContents").innerHTML= result[2];
	                        if(result[1] != '')
								alert(result[1]);
	                        //crmv@31245
	                        $('basicsearchcolumns').innerHTML = '';
	                        //crmv@31245e
	                      	update_navigation_values(postbody);
	                      	$('Buttons_List_3_Container').innerHTML = ''; //crmv@24604
	                      	ModNotificationsCommon.setFollowImgCV(viewName);	//crmv@29617
                      	}
                }
        );
}
//crmv@customview fix end
//crmv@pulldown list
function showMoreEntries(selectView,module,folderid) // crmv@30967
{
        $("status").style.display="inline";
    	if (ajaxcall_list){
    		ajaxcall_list.abort();
    	}
    	if (ajaxcall_count){
    		ajaxcall_count.abort();
    	}
        var viewCounts = selectView.options[selectView.options.selectedIndex].value;
        var viewid =getviewId();
        $("status").style.display="inline";
        if(isdefined('search_url'))
                urlstring = $('search_url').value;
        else
                urlstring = '';
        if (isdefined('selected_ids'))
        	urlstring += "&selected_ids=" + document.getElementById('selected_ids').value;
        if (isdefined('all_ids'))
        	urlstring += "&all_ids=" + document.getElementById('all_ids').value;
        if (isdefined('modulename'))
        	var modulename=document.getElementById('modulename').value;
        else
        	modulename = '';
        postbody = "module="+module+"&modulename="+modulename+"&action="+module+"Ajax&file=ListViewByProduct&start=1&ajax=true&changecount=true"+urlstring+"&counts="+viewCounts;
        if (folderid != undefined && folderid != '') postbody += '&folderid='+folderid; // crmv@30967
        new Ajax.Request(
                'index.php',
                {queue: {position: 'end', scope: 'command'},
                        method: 'post',
                        postBody:postbody,
                        onComplete: function(response) {
                                $("status").style.display="none";
                                result = response.responseText.split('&#&#&#');
                                $("ListViewContents").innerHTML= result[2];
                                if(result[1] != '')
                                        alert(result[1]);
                                if (module != 'Users' && module != 'Import' && module != 'Notes'){
                                	update_navigation_values(postbody);
                                	$('basicsearchcolumns').innerHTML = '';
                                }
                          }
                }
        );
}
//crmv@pulldown list end
//crmv@add customview popup
function showMoreEntries_popup(selectView,module)
{
        $("status").style.display="inline";
    	if (ajaxcall_list){
    		ajaxcall_list.abort();
    	}
    	if (ajaxcall_count){
    		ajaxcall_count.abort();
    	}
        var viewCounts = selectView.options[selectView.options.selectedIndex].value;
        var viewid =getviewId();
        $("status").style.display="inline";
		popuptype = $('popup_type').value;
		act_tab = $('maintab').value;
		urlstring = '&popuptype='+popuptype;
		urlstring += '&maintab='+act_tab;
		urlstring = urlstring +'&query=true&file=Popup&module='+module+'&action='+module+'Ajax&ajax=true&changecount=true&counts='+viewCounts;
		urlstring +=gethiddenelements();
		urlstring += "&start=1";
        new Ajax.Request(
                'index.php',
                {queue: {position: 'end', scope: 'command'},
                        method: 'post',
                        postBody:urlstring,
                        onComplete: function(response) {
							$("status").style.display="none";
							$("ListViewContents").innerHTML= response.responseText;
							update_navigation_values(urlstring);

							setListHeight(); //crmv@21048m
                        }
                }
        );
}
function showDefaultCustomView_popup(selectView,module,parenttab)
{
	$("status").style.display="inline";
	if (ajaxcall_list){
		ajaxcall_list.abort();
	}
	if (ajaxcall_count){
		ajaxcall_count.abort();
	}
	//crmv@7634
	if(selectView == null) selectView = getObj("viewname")
	//crmv@7634e
      if(isdefined('search_url'))
    	urlstring = $('search_url').value;
    else
    	urlstring = '';
        var viewName = selectView.options[selectView.options.selectedIndex].value;
        var viewid =getviewId();
        $("status").style.display="inline";
		popuptype = $('popup_type').value;
		act_tab = $('maintab').value;
		urlstring += '&popuptype='+popuptype;
		urlstring += '&maintab='+act_tab;
		urlstring = urlstring +'&query=true&file=Popup&module='+module+'&action='+module+'Ajax&ajax=true&viewname='+viewName+'&changecustomview=true&start=1';
		urlstring +=gethiddenelements();
        new Ajax.Request(
                       'index.php',
                {queue: {position: 'end', scope: 'command'},
                               method: 'post',
                        postBody: urlstring,
                        onComplete: function(response) {
							$("status").style.display="none";
							$("ListViewContents").innerHTML= response.responseText;
							update_navigation_values(urlstring);

							setListHeight(); //crmv@21048m
                        }
                }
        );
}
//crmv@add customview popup end
//crmv@10759 / fix listview
function getListViewEntries_js(module,url)
{
	if (ajaxcall_list){
		ajaxcall_list.abort();
	}
	if (ajaxcall_count){
		ajaxcall_count.abort();
	}
    var viewid =getviewId();
    $("status").style.display="inline";
    if(isdefined('search_url'))
            urlstring = $('search_url').value;
    else
            urlstring = '';
    if (isdefined('selected_ids'))
    	urlstring += "&selected_ids=" + document.getElementById('selected_ids').value;
    if (isdefined('all_ids'))
    	urlstring += "&all_ids=" + document.getElementById('all_ids').value;
    if (isdefined('modulename'))
    	var modulename=document.getElementById('modulename').value;
    else
    	modulename = '';
    gstart = url;
    postbody = "module="+module+"&modulename="+modulename+"&action="+module+"Ajax&file=ListViewByProduct&ajax=true&"+url+urlstring;
   // danzi.tn@18022013
    var stdValueFilterField = jQuery( "#stdValueFilterField" ),
		valueId = jQuery( "#valueId" ),
		proddate_start = jQuery( "#jscal_field_proddate_start" ),
		proddate_end = jQuery( "#jscal_field_proddate_end" ),
		amount_value = jQuery("#amount_value");
    var myparms = "&filter_type=" +stdValueFilterField.val()+ "&filter_value="+ valueId.val()+ "&startdate="+ proddate_start.val()+ "&enddate="+ proddate_end.val()+ "&amountrange="+ amount_value.val();
    postbody += myparms;
    // danzi.tn@18022013 e
    new Ajax.Request(
            'index.php',
            {queue: {position: 'end', scope: 'command'},
                    method: 'post',
                    postBody:postbody,
                    onComplete: function(response) {
                            $("status").style.display="none";
                            result = response.responseText.split('&#&#&#');
                            $("ListViewContents").innerHTML= result[2];
			    update_filter_values();
                            if(result[1] != '')
                                    alert(result[1]);
                            if (isdefined("basicsearchcolumns"))
                            	$('basicsearchcolumns').innerHTML = '';
                            if ($('import_flag').value == 1)
                           		update_navigation_values(postbody);
                      }
            }
    );
}


function update_filter_values(){
	// danzi.tn@13022013 url => module=Accounts&action=AccountsAjax&file=ListViewByProduct&ajax=true&changecustomview=true&start=1&viewname=55&parenttab=&lv_user_id=others&filter_type=cat&filter_value=02&startdate=13-02-2007&enddate=13-02-2013&amountrange=1663-6531
    var ticktoval = {
        '0':0,
        '1':500,
        '2':1000,
        '3':1500,
        '4':2000,
        '5':2500,
        '6':3000,
        '7':3500,
        '8':4000,
        '9':4500,
        '10':5000,
        '11':5500,
        '12':6000,
        '13':6500,
        '14':7000,
        '15':7500,
        '16':8000,
        '17':8500,
        '18':9000,
        '19':9500,
        '20':10000,
        '21':100000,
        '22':250000,
   };   
   var valtotick = {
       '0':0,
       '500':1,
       '1000':2,
       '1500':3,
       '2000':4,
       '2500':5,
       '3000':6,
       '3500':7,
       '4000':8,
       '4500':9,
       '5000':10,
       '5500':11,
       '6000':12,
       '6500':13,
       '7000':14,
       '7500':15,
       '8000':16,
       '8500':17,
       '9000':18,
       '9500':19,
       '10000':20,
       '100000':21,
       '250000':22,
    };
	var stdValueFilterField = jQuery( "#stdValueFilterField" ),
            valueId = jQuery( "#valueId" ),
            proddate_start = jQuery( "#jscal_field_proddate_start" ),
            proddate_end = jQuery( "#jscal_field_proddate_end" ),
            slider_range = jQuery( "#slider-range" ),
            amount_value = jQuery("#amount_value"),
            submit_search = jQuery( "#submit_search" ),
            viewname = jQuery("#viewname"),
            lv_user_id = jQuery("#lv_user_id"),
            allFields = jQuery( [] ).add( stdValueFilterField ).add( valueId ).add( proddate_start ).add( proddate_end ).add( slider_range );
    
	var currentval = jQuery("#amount_value").val();
	var currentval_splitted = currentval.split('-');
	minval = 0;
	maxval = 1;
	if( currentval_splitted.length > 1 ) {
	    minval = valtotick[currentval_splitted[0]];
	    maxval = valtotick[currentval_splitted[1]];
	}
	jQuery( "#slider-range" ).slider({
	    range: true,
	    min: 0,
	    max: 22,
	    values: [ minval, maxval ],
	    slide: function( event, ui ) {
                        if(ui.values[ 1 ]<22) upperVal = " - €" +ticktoval[ui.values[ 1 ]];
                        else upperVal = " - Max";
					    jQuery( "#amount" ).val( "€" + ticktoval[ui.values[ 0 ]] + upperVal);
					    jQuery("#amount_value").val(ticktoval[ui.values[ 0 ]]+"-"+ticktoval[ui.values[ 1 ]]);
					 }
	    });
    if(jQuery( "#slider-range" ).slider( "values", 1 )<22) upperVal = " - €" +ticktoval[jQuery( "#slider-range" ).slider( "values", 1 )];
    else upperVal = " - Max";
	jQuery( "#amount" ).val( "€" + ticktoval[jQuery( "#slider-range" ).slider( "values", 0 )] + upperVal );
	/*
	jQuery( "#submit_search" ).button().click(function( event ) {
	    var parms = "&lv_user_id=" + lv_user_id.val()+"&viewid=" + viewname.val() + "&filter_type=" +stdValueFilterField.val()+ "&filter_value="+ valueId.val()+ "&startdate="+ proddate_start.val()+ "&enddate="+ proddate_end.val()+ "&amountrange="+ amount_value.val();
	    //alert(parms);       
	    submit_search.attr("href","index.php?module=Accounts&parenttab=Sales&action=ListViewByProduct"+parms);
	    
	});*/
	Calendar.setup ({
		inputField : "jscal_field_proddate_start", ifFormat :js_dateformat, showsTime : false, button : "jscal_trigger_proddate_start", singleClick : true, step : 1
	});
	Calendar.setup ({
		inputField : "jscal_field_proddate_end", ifFormat : js_dateformat, showsTime : false, button : "jscal_trigger_proddate_end", singleClick : true, step : 1
	});
	jQuery( "#cat_prodotti").dialog({
		autoOpen: false,
		hide: "clip",
		height: 400,
		width: 400,
		position: [920,190],
		modal: false,
		buttons: {                
		    "Annulla": function() {
			jQuery( this ).dialog( "close" );
		    }
		},
		close: function() {
		    
		}
	    });
	jQuery("#stdValueFilterFieldAnchor").delegate("a", "click", function (event, data) {
	    if(stdValueFilterField.val()=='cat') jQuery( "#cat_prodotti").dialog('open');
	    event.preventDefault();
	});
	// danzi.tn@13022013 e
}

function update_navigation_values(url,module){
	$("status").style.display="inline";
	//crmv@27924
	// danzi.tn@13022013 
	update_filter_values();
	// danzi.tn@13022013 e
	
	// alert("update_navigation_values");
	if(url.indexOf('index.php?')>=0){
  		var url_split = url.split('index.php?');
  		var module_var = '';
  		var action_var = '';
  		var url_vars = url_split[1].split('&');
  		for (i=0; i<url_vars.length; i++) {
  			if (url_vars[i].indexOf('module=') != -1) {
				var url_tmp = url_vars[i].split('=');
				if (url_tmp[0] == 'module') {
					module_var = url_tmp[1];
				}
			} else if (url_vars[i].indexOf('action=') != -1) {
				var url_tmp = url_vars[i].split('=');
				if (url_tmp[0] == 'action') {
					action_var = url_tmp[1];
				}
			}
		}
  		url_split[1] = url_split[1].replace('action='+action_var,'action='+module_var+'Ajax&file='+action_var);
  		url_post = url_split[1]+"&calc_nav=true";
 	} else {
  		url_post = url+"&calc_nav=true";
 	}
 	if (module != undefined && url.indexOf("module")<0){
  		url_post = "module="+module+"&action="+module+"Ajax&file=ListViewByProduct&calc_nav=true";
 	}
 	//crmv@27924e
    if (isdefined('modulename'))
    	var modulename=document.getElementById('modulename').value;
    else
    	modulename = '';
    url_post+="&modulename="+modulename;
    new Ajax.Request(
            'index.php',
            {queue: {position: 'end', scope: 'command'},
                    method: 'post',
                    postBody:url_post,
                    onComplete: function(response) {
                            result = response.responseText.split('&#&#&#');
                            res_arr = eval ('('+result[1]+')');
                            if (isdefined("nav_buttons"))
                            	$("nav_buttons").innerHTML= res_arr['nav_array'];
                            if (isdefined("rec_string"))
                            	$("rec_string").innerHTML= res_arr['rec_string'];
                            if (isdefined("nav_buttons2"))
                            	$("nav_buttons2").innerHTML= res_arr['nav_array'];
                            if (isdefined("rec_string2"))
                            	$("rec_string2").innerHTML= res_arr['rec_string'];
                            if (res_arr['permitted']){
	                            if (isdefined("select_all_button_top"))
	                            	$("select_all_button_top").style.display = 'inline';
	                        	if (isdefined("select_all_button_bottom"))
	                        		$("select_all_button_bottom").style.display = 'inline';
                        	}
                        	//crmv@29617
                        	if (res_arr['reload_notification_count']) {
                        		NotificationsCommon.showChanges('CheckChangesDiv','CheckChangesImg','ModNotifications');
                        	}
                        	//crmv@29617e
                            $("status").style.display="none";
                      }
            }
    );
}
//crmv@10759 e

function update_selected_ids(checked,entityid,form)
{
    var idstring = form.selected_ids.value;
    if (idstring == "") idstring = ";";
    var all_ids = form.all_ids.value;
    if (all_ids == 1){
    	if (checked == true)
    		checked = false;
    	else
    		checked = true;
    }
    if (checked == true)
    {
    	form.selected_ids.value = idstring + entityid + ";";
    }
    else
    {
      form.selectall.checked = false;
      form.selected_ids.value = idstring.replace(entityid + ";", '');
    }
}

function select_all_page(state,form)
{
	if (typeof(form.selected_id.length)=="undefined"){
		if (form.selected_id.checked != state){
			form.selected_id.checked = state;
			update_selected_ids(state,form.selected_id.value,form)
		}
    }
	else {
	    for (var i=0;i<form.selected_id.length;i++){
	        obj_check = form.selected_id[i];
	        if (obj_check.checked != state){
		        obj_check.checked = state;
		        update_selected_ids(state,obj_check.value,form)
	        }
	    }
    }
}
//crmv@fix listview end
//for multiselect check box in list view:

function check_object(sel_id,groupParentElementId)
{
        var select_global=new Array();
        var selected=trim(document.getElementById("allselectedboxes").value);
        select_global=selected.split(";");
        var box_value=sel_id.checked;
        var id= sel_id.value;
        var duplicate=select_global.indexOf(id);
        var size=select_global.length-1;
		var result="";
        //alert("size: "+size);
        //alert("Box_value: "+box_value);
        //alert("Duplicate: "+duplicate);
        if(box_value == true)
        {
                if(duplicate == "-1")
                {
                        select_global[size]=id;
                }

                size=select_global.length-1;
                var i=0;
                for(i=0;i<=size;i++)
                {
                        if(trim(select_global[i])!='')
                                result=select_global[i]+";"+result;
                }
                default_togglestate(sel_id.name,groupParentElementId);
        }
        else
        {
                if(duplicate != "-1")
                        select_global.splice(duplicate,1)

                size=select_global.length-1;
                var i=0;
                for(i=size;i>=0;i--)
                {
                        if(trim(select_global[i])!='')
                                result=select_global[i]+";"+result;
                }
          //      getObj("selectall").checked=false
                default_togglestate(sel_id.name,groupParentElementId);
        }

        document.getElementById("allselectedboxes").value=result;
        //alert("Result: "+result);
}
function update_selected_checkbox()
{
        var all=document.getElementById('current_page_boxes').value;
        var tocheck=document.getElementById('allselectedboxes').value;
        var allsplit=new Array();
        allsplit=all.split(";");

        var selsplit=new Array();
        selsplit=tocheck.split(";");

        var n=selsplit.length;
        for(var i=0;i<n;i++)
        {
                if(allsplit.indexOf(selsplit[i]) != "-1")
                        document.getElementById(selsplit[i]).checked='true';
        }
}

//Function to Set the status as Approve/Deny for Public access by Admin
function ChangeCustomViewStatus(viewid,now_status,changed_status,module,parenttab)
{
	$('status').style.display = 'block';
	new Ajax.Request(
       		'index.php',
               	{queue: {position: 'end', scope: 'command'},
               		method: 'post',
                    postBody:'module=CustomView&action=CustomViewAjax&file=ChangeStatus&dmodule='+module+'&record='+viewid+'&status='+changed_status,
					onComplete: function(response)
					{
			        	var responseVal=response.responseText;
						if(responseVal.indexOf(':#:FAILURE') > -1) {
							alert('Failed');
						} else if(responseVal.indexOf(':#:SUCCESS') > -1) {
							var values = responseVal.split(':#:');
							var module_name = values[2];
							var customview_ele = $('viewname');
							showDefaultCustomView(customview_ele, module_name, parenttab);
						} else {
							$('ListViewContents').innerHTML = responseVal;
						}
						$('status').style.display = 'none';
					}
				}
	);
}

function getListViewCount(module,element,parentElement,url){
	if(module != 'Documents'){
		var elementList = document.getElementsByName(module+'_listViewCountRefreshIcon');
		for(var i=0;i<elementList.length;++i){
			elementList[i].style.display = 'none';
		}
	}else{
		element.style.display = 'none';
	}
	var elementList = document.getElementsByName(module+'_listViewCountContainerBusy');
	for(var i=0;i<elementList.length;++i){
		elementList[i].style.display = '';
	}
	var element = document.getElementsByName('search_url')[0];
	var searchURL = '';
	if(typeof element !='undefined'){
		searchURL = element.value;
	}else if(typeof document.getElementsByName('search_text')[0] != 'undefined'){
		element = document.getElementsByName('search_text')[0];
		var searchField = document.getElementsByName('search_field')[0];
		if(element.value.length > 0) {
			searchURL = '&query=true&searchtype=BasicSearch&search_field='+
				encodeURIComponent(searchField.value)+'&search_text='+encodeURIComponent(element.value);
		}
	}else if(document.getElementById('globalSearchText') != null &&
			typeof document.getElementById('globalSearchText') != 'undefined'){
		var searchText = document.getElementById('globalSearchText').value;
		searchURL = '&query=true&globalSearch=true&globalSearchText='+encodeURIComponent(searchText);
	}
	if(module != 'Documents'){
		searchURL += (url);
	}
	// Url parameters to carry forward the Alphabetical search in Popups,
	// which is stored in the global variable gPopupAlphaSearchUrl
	if(typeof gPopupAlphaSearchUrl != 'undefined' && gPopupAlphaSearchUrl != '')
		searchURL += gPopupAlphaSearchUrl;

	new Ajax.Request(
			'index.php',
			{queue: {position: 'end', scope: 'command'},
				method: 'post',
				postBody:"module="+module+"&action="+module+"Ajax&file=ListViewPagging&ajax=true"+searchURL,
				onComplete: function(response) {
					var elementList = document.getElementsByName(module+'_listViewCountContainerBusy');
					for(var i=0;i<elementList.length;++i){
						elementList[i].style.display = 'none';
					}
					elementList = document.getElementsByName(module+'_listViewCountRefreshIcon');
					if(module != 'Documents' && typeof parentElement != 'undefined' && elementList.length !=0){
						for(i=0;i<=elementList.length;){
							//No need to increment the count, as the element will be eliminated in the next step.
							elementList[i].parentNode.innerHTML = response.responseText;
						}
					}else{
						parentElement.innerHTML = response.responseText;
					}
				}
			}
	);
}

function VT_disableFormSubmit(evt) {
	var evt = (evt) ? evt : ((event) ? event : null);
	var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
	if ((evt.keyCode == 13) && (node.type=='text')) {
		node.onchange();
		return false;
	}
	return true;
}
var statusPopupTimer = null;
function closeStatusPopup(elementid)
{
	statusPopupTimer = setTimeout("document.getElementById('" + elementid + "').style.display = 'none';", 50);
}

function updateCampaignRelationStatus(relatedmodule, campaignid, crmid, campaignrelstatusid, campaignrelstatus)
{
	$("vtbusy_info").style.display="inline";
	document.getElementById('campaignstatus_popup_' + crmid).style.display = 'none';
	var data = "action=updateRelationsAjax&module=Campaigns&relatedmodule=" + relatedmodule + "&campaignid=" + campaignid + "&crmid=" + crmid + "&campaignrelstatusid=" + campaignrelstatusid;
	new Ajax.Request(
		'index.php',
			{queue: {position: 'end', scope: 'command'},
			method: 'post',
			postBody: data,
			onComplete: function(response) {
				if(response.responseText.indexOf(":#:FAILURE")>-1)
				{
					alert(alert_arr.ERROR_WHILE_EDITING);
				}
				else if(response.responseText.indexOf(":#:SUCCESS")>-1)
				{
					document.getElementById('campaignstatus_' + crmid).innerHTML = campaignrelstatus;
					$("vtbusy_info").style.display="none";
				}
			}
		}
	);
}

function loadCvList(type,id) {
	var element = type+"_cv_list";
	var value = document.getElementById(element).value;

	var filter = $(element)[$(element).selectedIndex].value	;
	if(filter=='None')return false;
	if(value != '') {
		$("status").style.display="inline";
		new Ajax.Request(
			'index.php',
			{queue: {position: 'end', scope: 'command'},
				method: 'post',
				postBody: 'module=Campaigns&action=CampaignsAjax&file=LoadList&ajax=true&return_action=DetailView&return_id='+id+'&list_type='+type+'&cvid='+value,
				onComplete: function(response) {
					$("status").style.display="none";
					$("RLContents").update(response.responseText);
				}
			}
		);
	}
}
//crmv@add select all	//crmv@20065
function get_real_selected_ids(module){
	//crmv@21048m
	/*if (module == 'Documents') {
		allids = document.getElementById('allids').value;
		selected_ids_obj = 'allselectedboxes';
	}
	else {*/
		allids = document.getElementById('all_ids').value;
		selected_ids_obj = 'selected_ids';
	//}
	//crmv@21048m e
	ret_value = '';
	if (allids == 1){
	    $("status").style.display="inline";
	    urlstring="&calc_nav=true&get_all_ids=true";
    	selected_ids = document.getElementById(selected_ids_obj).value.replace(/;/g,",");
		if (selected_ids == "" || selected_ids == ","){
		}
		else{
			if (selected_ids.substr('0','1')==","){
				selected_ids = selected_ids.substr('1');
			}
			urlstring+="&ids_to_jump="+selected_ids;
		}
		if (module == 'RecycleBin')
			urlstring+="&selected_module="+document.getElementById('selected_module').value;
    	postbody = "index.php?module="+module+"&action="+module+"Ajax&file=ListViewByProduct&ajax=true&"+urlstring;
		res = getFile(postbody);
		res_ = res.split("&#&#&#");
		res_real = res_[1];
		if (module == 'RecycleBin')
			res_real = res;
		res_arr = eval ('('+res_real+')');
		if (res_arr['all_ids']){
			ret_value = res_arr['all_ids'];
		}
		$("status").style.display="none";
	}
	else {
		ret_value = document.getElementById(selected_ids_obj).value;
		//if (module == 'Documents' && ret_value != '') ret_value = ';'+ret_value; //crmv@21048m
		//crmv@27096
		var res = '';
		$("status").style.display="inline";
		res = new Ajax.Request(
			'index.php',
			{queue: {position: 'end', scope: 'command'},
				asynchronous: false,
		    	method: 'post',
				postBody:"module=Utilities&action=UtilitiesAjax&file=ListViewCheckSave&selected_module="+module+"&selected_ids="+ret_value,
			}
		);
		$("status").style.display="none";
		//crmv@27096e
	}
	return ret_value;
}
//crmv@add select all end	//crmv@20065e

/* crmv@30967 */
//moved here
function trimfValues(value) {
 var string_array;
 string_array = value.split(":");
 return string_array[4];
}

function updatefOptions(sel, opSelName) {
	var split = opSelName.split('Condition');
	var index = split[1];
	var selObj = document.getElementById(opSelName);
	var fieldtype = null;
	var currOption = selObj.options[selObj.selectedIndex];
	var currField = sel.options[sel.selectedIndex];
	currField.value = currField.value.replace(/\\'/g, '');
	var fld = currField.value.split(":");
	var tod = fld[4];
	label = getcondition(false);
	if (fld[4] == 'D' || fld[4] == 'DT') {
		$("and" + sel.id).innerHTML = "";
		$("and" + sel.id).innerHTML = "<em old='(yyyy-mm-dd)'>("
				+ $("user_dateformat").value + ")</em>&nbsp;" + label;
	} else if (fld[4] == 'T' && fld[1] != 'time_start' && fld[1] != 'time_end') {
		$("and" + sel.id).innerHTML = "";
		$("and" + sel.id).innerHTML = "<em old='(yyyy-mm-dd)'>("
				+ $("user_dateformat").value + " hh:mm:ss)</em>&nbsp;" + label;
	}

	else if (fld[4] == 'I' && fld[1] == 'time_start' || fld[1] == 'time_end') {
		$("and" + sel.id).innerHTML = "hh:mm&nbsp;" + label;
	}

	else if (fld[4] == 'T' && fld[1] == 'time_start' || fld[1] == 'time_end') {
		$("and" + sel.id).innerHTML = "hh:mm&nbsp;" + label;
	}

	else if (fld[4] == 'C') {
		$("and" + sel.id).innerHTML = "( Yes / No )&nbsp;" + label;
	} else {
		$("and" + sel.id).innerHTML = "&nbsp;" + label;
	}
	if (currField.value != null && currField.value.length != 0) {
		fieldtype = trimfValues(currField.value);
		fieldtype = fieldtype.replace(/\\'/g, '');
		ops = typeofdata[fieldtype];
		var off = 0;
		if (ops != null) {

			var nMaxVal = selObj.length;
			for (nLoop = 0; nLoop < nMaxVal; nLoop++) {
				selObj.remove(0);
			}
			// selObj.options[0] = new Option ('None', '');
			// if (currField.value == '') {
			// selObj.options[0].selected = true;
			// }
			for ( var i = 0; i < ops.length; i++) {
				var label = fLabels[ops[i]];
				if (label == null)
					continue;
				var option = new Option(fLabels[ops[i]], ops[i]);
				selObj.options[i] = option;
				if (currOption != null && currOption.value == option.value) {
					option.selected = true;
				}
			}
		}
	} else {
		var nMaxVal = selObj.length;
		for (nLoop = 0; nLoop < nMaxVal; nLoop++) {
			selObj.remove(0);
		}
		selObj.options[0] = new Option('None', '');
		if (currField.value == '') {
			selObj.options[0].selected = true;
		}
	}
}


function getcondition(mode){
	if (mode == false){
		mode = jQuery("input[@name=matchtype]:checked").val();
	}

	if (mode == 'all')
		return alert_arr.LBL_AND;
	else
		return alert_arr.LBL_OR;
}

function checkgroup() {
	if($("group_checkbox").checked) {
		document.change_ownerform_name.lead_group_owner.style.display = "block";
		document.change_ownerform_name.lead_owner.style.display = "none";
	} else {
		document.change_ownerform_name.lead_owner.style.display = "block";
		document.change_ownerform_name.lead_group_owner.style.display = "none";
	}
}

function updatefOptionsAll(mode) {
	label = getcondition(mode);
	var table = document.getElementById('adSrc');
	if (table == undefined) return;
	for (i = 0; i < table.rows.length; i++) {
		var selObj = getObj('Fields' + i);
		var currField = selObj.options[selObj.selectedIndex];
		currField.value = currField.value.replace(/\\'/g, '');
		var fld = currField.value.split(":");

		if (fld[4] == 'D' || fld[4] == 'DT') {
			$("andFields" + i).innerHTML = "";
			$("andFields" + i).innerHTML = "<em old='(yyyy-mm-dd)'>("+ $("user_dateformat").value + ")</em>&nbsp;" + label;
		} else if (fld[4] == 'T' && fld[1] != 'time_start'	&& fld[1] != 'time_end') {
			$("andFields" + i).innerHTML = "";
			$("andFields" + i).innerHTML = "<em old='(yyyy-mm-dd)'>("+ $("user_dateformat").value + " hh:mm:ss)</em>&nbsp;"	+ label;
		}
		else if (fld[4] == 'I' && fld[1] == 'time_start' || fld[1] == 'time_end') {
			$("andFields" + i).innerHTML = "hh:mm&nbsp;" + label;
		}
		else if (fld[4] == 'T' && fld[1] == 'time_start' || fld[1] == 'time_end') {
			$("andFields" + i).innerHTML = "hh:mm&nbsp;" + label;
		}
		else if (fld[4] == 'C') {
			$("andFields" + i).innerHTML = "( Yes / No )&nbsp;" + label;
		} else {
			$("andFields" + i).innerHTML = "&nbsp;" + label;
		}
	}
}

// crmv@31245
function callSearch(searchtype, folderid) {

	if (gVTModule == undefined || gVTModule == '')
		return;

	if (ajaxcall_list) {
		ajaxcall_list.abort();
	}
	if (ajaxcall_count) {
		ajaxcall_count.abort();
	}
	$("status").style.display = "inline";

	gPopupAlphaSearchUrl = '';
	//search_fld_val = $('bas_searchfield').options[$('bas_searchfield').selectedIndex].value;
	var search_fld_val = '';
	var search_txt_val = encodeURIComponent(jQuery('#basic_search_text').val());
	var urlstring = '';
	if (searchtype == 'Basic') {
		var p_tab = document.getElementsByName("parenttab");
		urlstring = 'search_field=' + search_fld_val
				+ '&searchtype=BasicSearch&search_text=' + search_txt_val + '&';
		urlstring = urlstring + 'parenttab=' + p_tab[0].value + '&';
	} else if (searchtype == 'Advanced') {
		var no_rows = jQuery('#basic_search_cnt').val();
		for (jj = 0; jj < no_rows; jj++) {
			// crmv@advanced search fix
			var sfld_name = getObj("Fields" + jj);
			var scndn_name = getObj("Condition" + jj);
			var srchvalue_name = getObj("Srch_value" + jj);
			var currOption = scndn_name.options[scndn_name.selectedIndex];
			var currField = sfld_name.options[sfld_name.selectedIndex];
			currField.value = currField.value.replace(/\\'/g, '');
			var fld = currField.value.split(":");
			var convert_fields = new Array();
			if (fld[4] == 'D'
					|| (fld[4] == 'T' && fld[1] != 'time_start' && fld[1] != 'time_end')
					|| fld[4] == 'DT') {
				convert_fields.push(jj);
			}
			var p_tab = document.getElementsByName("parenttab");
			urlstring = urlstring + 'Fields' + jj + '='
					+ sfld_name[sfld_name.selectedIndex].value + '&';
			urlstring = urlstring + 'Condition' + jj + '='
					+ scndn_name[scndn_name.selectedIndex].value + '&';
			urlstring = urlstring + 'Srch_value' + jj + '='
					+ encodeURIComponent(srchvalue_name.value) + '&';
			urlstring = urlstring + 'parenttab=' + p_tab[0].value + '&';
		}
		for (i = 0; i < getObj("matchtype").length; i++) {
			if (getObj("matchtype")[i].checked == true)
				urlstring += 'matchtype=' + getObj("matchtype")[i].value + '&';
		}
		if (convert_fields.length > 0) {
			urlstring += 'fields_to_convert=';
			for (i = 0; i < convert_fields.length; i++) {
				urlstring += convert_fields[i] + ';';
			}
			urlstring += '&';
		}
		// crmv@advanced search fix e
		urlstring += 'search_cnt=' + no_rows + '&';
		urlstring += 'searchtype=advance&'
	}

	if (document.massdelete)
		urlstring += "idlist=" + document.massdelete.selected_ids.value + "&";
	postbody = urlstring + 'query=true&file=ListViewByProduct&module=' + gVTModule + '&action=' + gVTModule + 'Ajax&ajax=true&search=true';
	if (folderid != undefined && folderid != '' && folderid > 0) postbody += '&folderid=' + folderid; // crmv@30967
	basic_search_submitted = true; // crmv@31245

	new Ajax.Request('index.php', {
		queue : {
			position : 'end',
			scope : 'command'
		},
		method : 'post',
		postBody : postbody,
		onComplete : function(response) {
			$("status").style.display = "none";
			result = response.responseText.split('&#&#&#');
			$("ListViewContents").innerHTML = result[2];
			if (result[1] != '')
				alert(result[1]);
			$('basicsearchcolumns').innerHTML = '';
			update_navigation_values(postbody);
		}
	});
	return false
}
//crmv@31245e

// crmv@31245 - removed stuff

//----------

function lviewfold_showTooltip(folderid) {
	if (lviewFolder.disabled == true) return; // crmv@30976
	jQuery('#lviewfold_tooltip_'+folderid).show();
	lviewFolder.hidden = false;
}

function lviewfold_hideTooltip(folderid) {
	if (lviewFolder.disabled == true) return; // crmv@30976
	jQuery('#lviewfold_tooltip_'+folderid).hide();
	lviewFolder.hidden = true;
}

function lviewfold_moveTooltip(folderid) {
	if (!lviewFolder.hidden) {
		var newx, newy;
		var ttip = jQuery('#lviewfold_tooltip_'+folderid);
		tw = ttip.width();
		th = ttip.height();
		dw = jQuery(document).width();
		dh = jQuery(document).height();
		dx = dy = 10;
		if (lviewFolder.x + dx + tw > dw) {
			newx = dw - tw;
		} else {
			newx = lviewFolder.x+dx;
		}
		if (lviewFolder.y + dy + th > dh) {
			newy = dh - th;
		} else {
			newy = lviewFolder.y+dy;
		}
		ttip.css({'left':newx, 'top':newy});
	}
}

function lviewfold_add() {

	var baseurl = 'index.php?module=Utilities&action=UtilitiesAjax&file=FolderHandler';
	var formdata = jQuery('#lview_folder_addform').serialize();

	$("status").style.display = "inline";
	jQuery.ajax({
		type: 'POST',
		url: baseurl,
		data: formdata,
		success: function(data, tstatus) {
			if (data.substr(0, 7) == 'ERROR::') {
				$("status").style.display = "none";
				window.alert(data.substr(7));
			} else {
				location.reload();
			}
		}
	});
}

function lviewfold_del() {
	var checklist = jQuery('#lview_table_cont span[id^=lview_folder_checkspan]');
	if (checklist.length == 0) return window.alert(alert_arr.LBL_NO_EMPTY_FOLDERS);
	jQuery('#lviewfolder_button_del').hide();
	jQuery('#lviewfolder_button_add').hide();
	jQuery('#lviewfolder_button_list').hide();
	jQuery('#lviewfolder_button_del_cancel').show();
	jQuery('#lviewfolder_button_del_save').show();
	checklist.show();
	// crmv@30976 - ingrigisce le altre cartelle
	lviewFolder.disabled = true;
	jQuery('#lview_table_cont div[class=lview_folder_td]:not(:has(span[id^=lview_folder_checkspan]))').css({opacity: 0.5});
	// crmv@30976e
}

function lviewfold_del_cancel() {
	jQuery('#lviewfolder_button_del').show();
	jQuery('#lviewfolder_button_add').show();
	jQuery('#lviewfolder_button_list').show();
	jQuery('#lviewfolder_button_del_cancel').hide();
	jQuery('#lviewfolder_button_del_save').hide();
	jQuery('#lview_table_cont span[id^=lview_folder_checkspan]').hide();
	// crmv@30976
	lviewFolder.disabled = false;
	jQuery('#lview_table_cont div[class=lview_folder_td]').css({opacity: 1});
	// crmv@30976e
}

function lviewfold_del_save(module) {
	var delids = [];
	jQuery('#lview_table_cont input[type=checkbox]:checked').each(function (idx, el) {
		delids.push(parseInt(el.id.replace('lvidefold_check_', '')));
	});

	if (delids.length == 0) return window.alert(alert_arr.LBL_SELECT_DEL_FOLDER);

	var baseurl = 'index.php?module=Utilities&action=UtilitiesAjax&file=FolderHandler&subaction=del';
	var formdata = 'folderids='+delids.join(',')+'&formodule='+module;

	$("status").style.display = "inline";
	jQuery.ajax({
		type: 'POST',
		url: baseurl,
		data: formdata,
		success: function(data, tstatus) {
			if (data.substr(0, 7) == 'ERROR::') {
				$("status").style.display = "none";
				window.alert(data.substr(7));
			} else {
				location.reload();
			}
		}
	});
}
/* crmv@30967e */
var basic_search_submitted = false;

//crmv@31245
function clearText(elem) {
	var jelem = jQuery(elem);
	var rest = jQuery.data(elem, 'restored');
	if (rest == undefined || rest == true) {
		jelem.val('');
		jQuery('#basic_search_icn_canc').show();
		jQuery.data(elem, 'restored', false);
	}
}
function restoreDefaultText(elem, deftext) {
	var jelem = jQuery(elem);
	if (jelem.val() == '') {
		jelem.val(deftext);
		jQuery('#basic_search_icn_canc').hide();
		jQuery.data(elem, 'restored', true);
	}
}
function cancelSearchText(deftext) {
	jQuery('#basic_search_text').val('');
	restoreDefaultText(document.getElementById('basic_search_text'), deftext);
	if (basic_search_submitted) {
		if (gVTModule == 'Users') {
			jQuery('#basic_search_text').val('');
			jQuery('#basicSearch').submit();
			restoreDefaultText(document.getElementById('basic_search_text'), deftext);
		} else {
			jQuery('#viewname').change();
		}
		basic_search_submitted = false;
 	}
}
//crmv@31245e

// danzi.tn@20150204 Export dei risultati di ListViewByProduct e visualizza in mappa
function exportCurrentListViewData(elem) {	
	selectView = getObj("viewname");
	var viewName = selectView.options[selectView.options.selectedIndex].value;
	var stdValueFilterField = jQuery( "#stdValueFilterField" ),
		valueId = jQuery( "#valueId" ),
		proddate_start = jQuery( "#jscal_field_proddate_start" ),
		proddate_end = jQuery( "#jscal_field_proddate_end" ),
		amount_value = jQuery("#amount_value");
	var postBodyString = "file=ExportListViewByProductAjax&module=Accounts&action=AccountsAjax&ajaxaction=EXPORT&viewname="+viewName+"&filter_type=" +stdValueFilterField.val()+ "&filter_value="+ valueId.val()+ "&startdate="+ proddate_start.val()+ "&enddate="+ proddate_end.val()+ "&amountrange="+ amount_value.val();
	
	//window.location='index.php?'+postBodyString;
	//window.location='modules/Accounts/01simple-download-xls.php';
	var ajxReq = new Ajax.Request(
                /*'index.php?'+postBodyString,*/
				'index.php',
				// 'modules/Accounts/01simple-download-xls.php',
                {queue: {position: 'end', scope: 'command'},
                        method: 'post',
						postBody: postBodyString,
                        onComplete: function(response) {
								if(response.responseText == ':#:EXP_FAILURE LISTQUERY') {
									alert("ERRORE: " + response.responseText);
								} else if (response.responseText == ':#:EXP_FAILURE ACCOUNTSAJAX') {
									alert("ERRORE: " + response.responseText);
								} else {
									document.location.href = (response.responseText);
								}
                        }
                }
        );
	
}

function showInMapListViewData(elem) {
	selectView = getObj("viewname");
	var viewName = selectView.options[selectView.options.selectedIndex].value;
	var viewId = getviewId();
	var userid_url = ""
	var userid_obj = getObj("lv_user_id");
	if(userid_obj != null) {
		//crmv@29682
		if (navigator.appName == 'Microsoft Internet Explorer') {
			if (typeof(userid_obj.options) != 'undefined') {
				userid_url = "&lv_user_id="+userid_obj.options[userid_obj.options.selectedIndex].value;
			}else {
				userid_url = "&lv_user_id="+userid_obj.item(0).options[userid_obj.item(0).options.selectedIndex].value;
			}
		} else {
			userid_url = "&lv_user_id="+userid_obj.options[userid_obj.options.selectedIndex].value;
		}
		//crmv@29682e
	}
	/*
	<span id="valueSelContainer">
	<input type="radio" value="ND" name="valueSel" id="valueSelND" class="small">Nessuno
	<input type="radio" value="cat_prodotti" name="valueSel" id="valueSel" class="small"> Categorie
	<input type="radio" value="prodotto" name="valueSel" id="valueSelPROD" class="small"> Prodotto
	</span>http://crm.rothoblaas.com/__test/index.php?show=Accounts&viewid=3199&lv_user_id=all&valueSel=cat_prodotti&valueId=02&map_mindate=&map_maxdate=&type_or_value=type&cluster=Enable&module=Map&action=index&parenttab=Tools
	*/
	var stdValueFilterField = jQuery( "#stdValueFilterField" ),// valueSel
		valueId = jQuery( "#valueId" ),  // valueId
		proddate_start = jQuery( "#jscal_field_proddate_start" ), // map_mindate - jscal_field_map_mindate
		proddate_end = jQuery( "#jscal_field_proddate_end" ), // map_maxdate - jscal_field_map_maxdate
		amount_value = jQuery("#amount_value");
	var valueSel = "ND";
	if(stdValueFilterField.val() == "cat") valueSel = "cat_prodotti";
	if(stdValueFilterField.val() == "prod") valueSel = "prodotto";
	document.location.href = 'index.php?from=ListViewByProduct&show=Accounts&viewid=' + viewId + userid_url +'&valueSel=' + valueSel + '&valueId='+valueId.val()+'&map_mindate='+proddate_start.val()+'&map_maxdate='+proddate_end.val()+'&type_or_value=type&cluster=Enable&module=Map&action=index&parenttab=Tools&amountrange=' + amount_value.val();;
}