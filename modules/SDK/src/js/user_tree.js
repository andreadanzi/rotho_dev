// danzi.tn@20150825 tree on user array (for listview)
// danzi.tn@20150922 filtro per stato selected_country
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
          str = $( this ).value;
        });
        jQuery( "#selected_country" ).val( str );
    });    
    
     // <input type="hidden" name="module" value="{$MODULE}" />
     // <input type="hidden" name="parenttab" value="{$CATEGORY}" />
    
    jQuery( "#agent_tree_container").dialog({
        autoOpen: false,
        hide: "clip",
		height: 600,
		width: 760,
        modal: true,
        buttons: {                
            Ok: function() {
                jQuery( this ).dialog( "close" );
                basicSearchForm = jQuery("#basicSearch");
                module = jQuery("#basicSearch :input[name='module']").val();
                parenttab = jQuery("#basicSearch :input[name='parenttab']").val();
                showDefaultCustomView(null,module,parenttab, '');
            },               
            Cancel: function() {
                jQuery('#selected_agent_ids').val("");
                jQuery('#selected_agent_ids_display').val("");
                jQuery( "#selected_country" ).val( "" );
                jQuery( this ).dialog( "close" );
            }
        },
        close: function() {
            
        }
    });
    
    jQuery("#agent_jstree").delegate("a", "dblclick", function (event, data) { 
        jQuery( "#agent_tree_container").dialog('close');
        alert("Filtra aziende per " + jQuery('#selected_agent_ids').val());
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

