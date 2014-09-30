// danzi.tn@20140408 prova in ambiente  di test 
// danzi.tn@20140417 open new window
// danzi.tn@20140929 dettaglio con partita IVA
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
// $MODULE$, $ACTION$ and $RECORD$
function openAccDocCredit(parm) {
	var retVal = "#";
    var vatid = document.getElementById("txtbox_Partita IVA CEE").value;
	// jQuery('html, body').addClass('wait');
	jQuery.ajax ({
        type: 'POST',
        url: 'index.php',
        data: { module: 'Users', action: 'UsersAjax', file:'GetDocCreditDetail', euvat:vatid },
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
