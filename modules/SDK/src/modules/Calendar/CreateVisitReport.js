//danzi.tn@20140310 CreateVisitReport funzione javascript per le creazione di report visita
function createVisitReport (parm) {
	pp = parm;
	id = parm.ownerDocument.form.elements.record.value;
	var record = '';
	new Ajax.Request(
		'index.php',
		{queue: {position: 'end', scope: 'command'},
			method: 'post',
			postBody: 'module=Visitreport&action=VisitreportAjax&file=CreateVisit&ajxaction=DETAILVIEW&record='+id,
			onComplete: function(response) {
				record = response.responseText;
				if (record == 0) {
					alert(alert_arr.ERROR); // "Nothing Found"
					return false;
				}
				if (record == -1) {
					alert(alert_arr.ERROR); // "Found Event But With Wrong Parameters (type or Related Entity)"
					return false;
				}
				window.location.assign('index.php?module=Visitreport&parenttab=Sales&action=EditView&record='+record+'&return_module=Visitreport&return_action=DetailView&parenttab=Sales');
			}
		}
	);
}
