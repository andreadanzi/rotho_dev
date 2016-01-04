// danzi.tn@20150825 tree on user array (for listview)
// danzi.tn@20150922 filtro per stato selected_country
// danzi.tn@20160104 passaggio in produzione albero utenti
jQuery(function() {
    function listChildren(tnode) {
      var retval = [];

      (function crawl(treenode) {
        var childrenArr = jQuery.jstree._reference("#agent_jstree")._get_children(treenode);
        for (var i = 0; i < childrenArr.length; i++) {
            retval.push(childrenArr[i].id);
            crawl(childrenArr[i]);
        }
      })(tnode);

      return retval;
    }

    jQuery("#agent_jstree")
		.jstree({   "search" : {show_only_matches: true,
                                }  ,
                      "themes" : {
                            "theme" : "default",
                            "dots" : false,
                            "icons" : false
                                },
                    "plugins" : ["themes","html_data","search","ui"]
                 })
		// 1) if using the UI plugin bind to select_node
		.bind("select_node.jstree deselect_node.jstree", function (event, data) {
			// `data.rslt.obj` is the jquery extended node that was clicked
            var i, j, r = [], p = [];
            var selected_data = data.inst.get_selected();
            for(i = 0, j = selected_data.length; i < j; i++) {
                r.push(selected_data[i].id);
                p.push(selected_data[i].title);
                // get all the children....one level below (recursive if all)
                // var childrenArr = jQuery.jstree._reference("#agent_jstree")._get_children(selected_data[i]);
                var childrenArr = listChildren(selected_data[i]);
                for(ii = 0, jj = childrenArr.length; ii < jj; ii++) {
                  r.push(childrenArr[ii]);
                }
            }
            var sJoined = r.join(',');
            var sSelectedParentJoined = p.join(',');
            jQuery('#selected_agent_ids').val(sJoined);
            jQuery('#selected_agent_ids_display').val(sSelectedParentJoined);
		})
		// 2) if not using the UI plugin - the Anchor tags work as expected
		//    so if the anchor has a HREF attirbute - the page will be changed
		//    you can actually prevent the default, etc (normal jquery usage)
		.delegate("a", "click", function (event, data) { event.preventDefault(); });

    var to = false;
    jQuery('#agent_jstree_q').keyup(function () {
        if(to) { clearTimeout(to); }
        to = setTimeout(function () {
            var v = jQuery('#agent_jstree_q').val();
            jQuery('#agent_jstree').jstree("search",v);
        }, 250);
    });

    jQuery('#country_combobox').change(function () {
        var str = "";
        jQuery( "#country_combobox option:selected" ).each(function() {
          str = this.value;
        });
        jQuery( "#selected_country" ).val( str );
    });

     // <input type="hidden" name="module" value="{$MODULE}" />
     // <input type="hidden" name="parenttab" value="{$CATEGORY}" />

    var tree_ok = jQuery( "#tree_ok").val();
    var tree_cancel = jQuery( "#tree_cancel").val();
    var tree_close = jQuery( "#tree_close").val();
    jQuery( "#agent_tree_container").dialog({
        autoOpen: false,
        hide: "clip",
        buttons: [{
            text: tree_ok,
            "id": "btnTreeOk",
            click: function(event ) {
             jQuery( this ).dialog( "close" );
             basicSearchForm = jQuery("#basicSearch");
             module = jQuery("#basicSearch :input[name='module']").val();
             parenttab = jQuery("#basicSearch :input[name='parenttab']").val();
             showDefaultTreeCustomView(null,module,parenttab, '');
             event.preventDefault();
         }},
         {
             text: tree_cancel,
             "id": "btnTreeCancel",
             click: function(event ) {
               jQuery('#selected_agent_ids').val("");
               jQuery('#selected_agent_ids_display').val("");
               jQuery( "#selected_country" ).val( "" );
               jQuery('#country_combobox option:eq(0)').attr('selected','selected');
               jQuery( this ).dialog( "close" );
               module = jQuery("#basicSearch :input[name='module']").val();
               parenttab = jQuery("#basicSearch :input[name='parenttab']").val();
               showDefaultTreeCustomView(null,module,parenttab, '');
               event.preventDefault();
           }
         },
         {
             text: tree_close,
             "id": "btnTreeClose",
             click: function(event ) {
               jQuery( this ).dialog( "close" );
               event.preventDefault();
           }
         }
    ],
		height: 600,
		width: 760,
        modal: true,
        close: function() {

        }
    });
    jQuery("#agent_jstree").delegate("a", "dblclick", function (event, data) {
        jQuery( "#agent_tree_container").dialog('close');
        basicSearchForm = jQuery("#basicSearch");
        module = jQuery("#basicSearch :input[name='module']").val();
        parenttab = jQuery("#basicSearch :input[name='parenttab']").val();
        showDefaultTreeCustomView(null,module,parenttab, '');
        event.preventDefault();
    });
    /*
    jQuery("#user_search").delegate("img", "click", function (event, data) {
        jQuery( "#agent_tree_container").css("visibility","visible");
	    jQuery( "#agent_tree_container").dialog('open');
	    event.preventDefault();
	});
    */
});

function showDefaultTreeCustomView(selectView,module,parenttab, folderid) // crmv@30967
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
    // danzi.tn@20150825 user tree
    var selected_agent_ids = getObj("selected_agent_ids");
    if(selected_agent_ids != null) {
        userid_url += "&selected_agent_ids="+selected_agent_ids.value;
    }
    // danzi.tn@20150825e
    // danzi.tn@20150922 filtro per stato selected_country
    var selected_country = getObj("selected_country");
    if(selected_country != null) {
        userid_url += "&selected_country="+selected_country.value;
    }
    file_value = "RothoListView";
    var treeFile = getObj("treeFile");
    if(treeFile != null) {
        file_value = treeFile.value;
    }
    // danzi.tn@20150922e
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
		postbody="module="+module+"&action="+module+"Ajax&file="+file_value+"&ajax=true&changecustomview=true&start=1&viewname="+viewName+"&parenttab="+parenttab+userid_url+urlstring+override_orderby; //crmv@7634
		if (folderid != undefined && folderid != '') postbody += '&folderid='+folderid; // crmv@30967

		// crmv@31245
		var searchrest = jQuery.data(document.getElementById('basic_search_text'), 'restored');
		var searchval = jQuery('#basic_search_text').val();
		if (searchrest == false && searchval != '') {
			postbody += '&searchtype=BasicSearch&search_field=&query=true&search_text='+encodeURIComponent(searchval);
		}
		// crmv@31245e

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


//danzi.tn@20150825
function clearSelectedAgents(elem) {
	var rest = jQuery.data(elem, 'restored');
	if (rest == undefined || rest == true) {
		jQuery('#agent_search_icn_canc').show();
		jQuery.data(elem, 'restored', false);
	}
}
function restoreDefaultAgents(elem, deftext) {
	var jelem = jQuery(elem);
	jelem.val(deftext);
	jQuery('#selected_agent_ids').val('');
    jQuery('#selected_country').val('');
	jQuery('#agent_search_icn_canc').hide();
	jQuery.data(elem, 'restored', true);
}
function cancelSearchAgents(deftext) {
	jQuery('#selected_agent_ids_display').val('');
	jQuery('#selected_agent_ids').val('');
    jQuery('#selected_country').val('');
	restoreDefaultAgents(document.getElementById('selected_agent_ids_display'), deftext);
}

function open_tree_container() {
    jQuery( "#agent_tree_container").css("visibility","visible");
	jQuery( "#agent_tree_container").dialog('open');
}

//danzi.tn@20150825e
