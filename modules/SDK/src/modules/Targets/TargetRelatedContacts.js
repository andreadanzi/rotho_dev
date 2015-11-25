//danzi.tn@20150630 funzione ajax custom per attaccare i contatti delle aziende di un target
function addRelatedContacts(elem) {	
    var recordid = jQuery( "input[name='record']" ).val();
	var postBodyString = "file=AddRelatedContactsAjax&module=Targets&action=TargetsAjax&ajaxaction=ADDRELATEDCONTACTS&recordid="+recordid;
	
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
								if(response.responseText == ':#:ADD_FAILURE MISSING RECORDID') {
									alert("ERRORE: " + response.responseText);
								} else if (response.responseText == ':#:ADD_FAILURE MISSING TARGETAJAX') {
									alert("ERRORE: " + response.responseText);
								} else {
                                    location.reload();
								}
                        }
                }
        );
	
}