//danzi.tn@20151214 Bottone custom su DetailView e ListView per visualizzare in Mappa
// danzi.tn@20160104 passaggio in produzione albero utenti
function showDetailInMap(elem) {
	var ids = jQuery( "#accountid" ).val();
	document.location.href = 'index.php?module=Map&from=DetailView&action=index&ids='+ids;
}

function showSelectedItemsInMap(elem)
{
    var select_options = get_real_selected_ids("Accounts");
	if (select_options.substr('0','1')==";")
		select_options = select_options.substr('1');
    var x = select_options.split(";");
    var count = x.length;
    count = count-1;
    if (count < 1)
    {
        alert(alert_arr.SELECT);
        return false;
    }
    document.location.href = 'index.php?module=Map&from=ListView&action=index&ids='+select_options;
}
