/*********************************************************************************
 *
 *
 *
 ********************************************************************************/
// danzi.tn@20150331 modifica allo slider, per step da 500 euro
// danzi.tn@20150408 modifica all'albero delle categorie per abilitare la selezione multipla
// danzi.tn@20160104 passaggio in produzione albero utenti
var asv = { 0:"0", 1:"1", 2:"5", 3:"10", 4:"20", 5:"30", 6:"50", 7:"100" , 8:"500", 9:"1000", 10:"100000"};
var sva = { "0":0, "1":1, "5":2, "10":3, "20":4, "30":5, "50":6, "100":7 , "500":8, "1000":9, "100000":10};

var ticktoval = {
        0:"0",
        1:"500",
        2:"1000",
        3:"1500",
        4:"2000",
        5:"2500",
        6:"3000",
        7:"3500",
        8:"4000",
        9:"4500",
        10:"5000",
        11:"5500",
        12:"6000",
        13:"6500",
        14:"7000",
        15:"7500",
        16:"8000",
        17:"8500",
        18:"9000",
        19:"9500",
        20:"10000",
        21:"100000",
        22:"250000",
   };
   var valtotick = {
       "0":0,
       "500":1,
       "1000":2,
       "1500":3,
       "2000":4,
       "2500":5,
       "3000":6,
       "3500":7,
       "4000":8,
       "4500":9,
       "5000":10,
       "5500":11,
       "6000":12,
       "6500":13,
       "7000":14,
       "7500":15,
       "8000":16,
       "8500":17,
       "9000":18,
       "9500":19,
       "10000":20,
       "100000":21,
       "250000":22,
    };


jQuery(function() {

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
    if (currentval != undefined) {	//mycrmv@manuele
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
	                                        jQuery( "#amount" ).val( "€" + ticktoval[ui.values[ 0 ]] + upperVal );
	                                        jQuery("#amount_value").val(ticktoval[ui.values[ 0 ]]+"-"+ticktoval[ui.values[ 1 ]]);
	                                     }
	        });
        if(jQuery( "#slider-range" ).slider( "values", 1 )<22) upperVal = " - €" +ticktoval[jQuery( "#slider-range" ).slider( "values", 1 )];
        else upperVal = " - Max";
	    jQuery( "#amount" ).val( "€" +  ticktoval[jQuery( "#slider-range" ).slider( "values", 0 )] + upperVal );
	    jQuery("#amount_value").val(ticktoval[jQuery( "#slider-range" ).slider( "values", 0 )]+"-"+ticktoval[jQuery( "#slider-range" ).slider( "values", 1 )]);
	    jQuery( "#submit_search" ).button().click(function( event ) {
	        var parms = "&lv_user_id=" + lv_user_id.val()+"&viewid=" + viewname.val() + "&filter_type=" +stdValueFilterField.val()+ "&filter_value="+ valueId.val()+ "&startdate="+ proddate_start.val()+ "&enddate="+ proddate_end.val()+ "&amountrange="+ amount_value.val();
	        //alert(parms);
	        submit_search.attr("href","index.php?module=Accounts&parenttab=Sales&action=ListViewByProduct"+parms);

	    });
    }	//mycrmv@manuele

    jQuery( "#cat_prodotti").dialog({
            autoOpen: false,
	    hide: "clip",
            height: 400,
            width: 400,
	    position: [920,190],
            modal: false,
            buttons: {
                "Chiudi": function() {
                    jQuery( this ).dialog( "close" );
                }
            },
            close: function() {

            }
        });
    /**/
    // danzi.tn@20150408 selezione multipla
    jQuery("#categorytree")
		.jstree({ "plugins" : ["themes","html_data","ui"] })
		// 1) if using the UI plugin bind to select_node
		.bind("select_node.jstree deselect_node.jstree", function (event, data) {
			// `data.rslt.obj` is the jquery extended node that was clicked
            var i, j, r = [];
            var selected_data = data.inst.get_selected();
            for(i = 0, j = selected_data.length; i < j; i++) {
              r.push(selected_data[i].id);
            }
            var sJoined = r.join(',');
            document.getElementById("valueId").value = sJoined;
			// document.getElementById("valueId").value = data.rslt.obj.attr("id");
			// alert(data.rslt.obj.attr("id"));
		})
		// 2) if not using the UI plugin - the Anchor tags work as expected
		//    so if the anchor has a HREF attirbute - the page will be changed
		//    you can actually prevent the default, etc (normal jquery usage)
		.delegate("a", "click", function (event, data) { event.preventDefault();});
    /**/
    jQuery("#categorytree").delegate("a", "dblclick", function (event, data) { jQuery( "#cat_prodotti").dialog('close');event.preventDefault();});
    jQuery("#stdValueFilterFieldAnchor").delegate("a", "click", function (event, data) {
        if(stdValueFilterField.val()=='cat') jQuery( "#cat_prodotti").dialog('open');
        event.preventDefault();
    });
});


function updateValueFilterContainer(elem)
{
	if(elem.value && elem.value=='cat' )
	{
		jQuery('#cat_prodotti').dialog("open");
	}
	else
	{
		document.getElementById("valueId").value = 'ND';
		jQuery('#cat_prodotti').dialog("close");
	}
}
