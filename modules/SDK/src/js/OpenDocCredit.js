// danzi.tn@20140408 prova in ambiente  di test 
// danzi.tn@20140417 open new window
function openDocCredit(parm) {
	var retVal = "#";
	// jQuery('html, body').addClass('wait');
	jQuery.ajax ({
        type: 'POST',
        url: 'index.php',
        data: { module: 'Users', action: 'UsersAjax', file:'GetDocCreditInfo' },
		async: false,
        success: function(response) {
					if(response == "_EMPTY_USR_") {
						alert(alert_arr.ALERT_DCLINK_NOUSER);
					} else if(response == "_EMPTY_COOKIE_") {
						alert('Empty cookies');
					} else {
						retVal = response;
					}
                },
        error:  function(xhr, status, error) {
                    alert(alert_arr.ALERT_DCLINK_NOUSER);
                },
		beforeSend : function(jqxhr, sett) {
					
		},
		complete: function(response) {	
		}
    });
	
	// jQuery('html, body').removeClass('wait');
	return retVal;
}

function openAccDocCredit(parm) {
	alert("COMING SOON!");
}
