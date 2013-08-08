
function inspections_record_export(module,category,exform,idstring)
{
    var searchType = document.getElementsByName('search_type');
    var exportData = document.getElementsByName('export_data');
	var actionForm = exform.action;
	var fileForm = exform.file;
	var ajaxactionForm = exform.ajaxaction;
	var insp_exp_type = 'printexp';
	var exportType = exform.export_type;
	for(i=0;i<2;i++){
        if(exportType[i].checked == true)
		{
            insp_exp_type = exportType[i].value;
		}
    }
    for(i=0;i<2;i++){
        if(searchType[i].checked == true)
            var sel_type = searchType[i].value;
    }
    for(i=0;i<3;i++){
        if(exportData[i].checked == true)
            var exp_type = exportData[i].value;
    }
	if(insp_exp_type == 'printexp')
	{
		var postBodyString = "file=ExportInspectionsAjax&module="+module+"&action=InspectionsAjax&ajaxaction=LISTVIEW&search_type="+sel_type+"&export_data="+exp_type+"&export_type="+insp_exp_type+"&idstring="+idstring;
		actionForm.value = "InspectionsAjax";
		fileForm.value = "ExportInspectionsAjax";
		ajaxactionForm.value = "EXPORT";
	}
	else {
		var postBodyString = "module="+module+"&action=ExportAjax&export_record=true&search_type="+sel_type+"&export_data="+exp_type+"&export_type="+insp_exp_type+"&idstring="+idstring;
		actionForm.value = "Export";
		fileForm.value = "";
		ajaxactionForm.value = "";
	}
    var ajxReq = new Ajax.Request(
                'index.php',
                {queue: {position: 'end', scope: 'command'},
                        method: 'post',
                        postBody: postBodyString,
                        onComplete: function(response) {
                                if(response.responseText == 'NOT_SEARCH_WITHSEARCH_ALL')
                {
                                        $('not_search').style.display = 'block';
                    $('not_search').innerHTML="<font color='red'><b>"+alert_arr.LBL_NOTSEARCH_WITHSEARCH_ALL+" "+module+"</b></font>";
                    setTimeout(hideErrorMsg1,6000);

                    exform.submit();
                }
                else if(response.responseText == 'NOT_SEARCH_WITHSEARCH_CURRENTPAGE')
                                {
                                        $('not_search').style.display = 'block';
                                        $('not_search').innerHTML="<font color='red'><b>"+alert_arr.LBL_NOTSEARCH_WITHSEARCH_CURRENTPAGE+" "+module+"</b></font>";
                                        setTimeout(hideErrorMsg1,7000);

                                        exform.submit();
                                }
                else if(response.responseText == 'NO_DATA_SELECTED')
                {
                    $('not_search').style.display = 'block';
                    $('not_search').innerHTML="<font color='red'><b>"+alert_arr.LBL_NO_DATA_SELECTED+"</b></font>";
                    setTimeout(hideErrorMsg1,3000);
                }
                else if(response.responseText == 'SEARCH_WITHOUTSEARCH_ALL')
                                {
                    if(confirm(alert_arr.LBL_SEARCH_WITHOUTSEARCH_ALL))
                    {
                        exform.submit();
                    }
                                }
                else if(response.responseText == 'SEARCH_WITHOUTSEARCH_CURRENTPAGE')
                                {
                                        if(confirm(alert_arr.LBL_SEARCH_WITHOUTSEARCH_CURRENTPAGE))
                                        {
                                                exform.submit();
                                        }
                                }
				else if(response.responseText == ':#:EXP_FAILURE') {
										$('not_search').style.display = 'block';
										$('not_search').innerHTML="<font color='red'><b>"+alert_arr.LBL_NO_DATA_SELECTED+"</b></font>";
										setTimeout(hideErrorMsg1,3000);
				}
				else if(response.responseText == ':#:EXP_SUCCESS') {
										exform.submit();
				}
				else
                {
                                       exform.submit();
                }
                        }
                }
        );

}
