/*
 * Funzioni per la gestione delle picklist collegate
 */

/*
 * Aggiorna le varie picklist
 */
//crmv@27229
function linkedListUpdateLists(res) {
  if (!res) return;

  for (i=0; i<res.length; ++i) {
    name = res[i][0];
    list = res[i][1];
    list_trans = res[i][2];
    otherpl = document.getElementsByName(name);
    // take the first matching element
    if (otherpl.length > 0) {
     otherpl = otherpl[0];
    } else {
     // try a multiselect picklist
     otherpl = document.getElementsByName(name+"[]");
     if (otherpl.length > 0) otherpl = otherpl[0]; else continue;
    }
	var oldval = otherpl.options[otherpl.selectedIndex].value;
    // delete inside
    otherpl.innerHTML = "";
    // re-populate
    for (j=0; j<list.length; ++j) {
      var option = document.createElement("option");
      option.text = list_trans[j];
      option.value = list[j];
      otherpl.add(option);
      if (option.value == oldval) option.selected = true;
    }

    // change other lists
    if (otherpl.onchange) otherpl.onchange(otherpl);
  }
}
//crmv@27229e

/*
 * funzione da chiamare quando la picklist obj cambia
 */
function linkedListChainChange(obj, module) { // crmv@30528
  if (!obj) return;
  pickname = obj.name;

  opt = obj.options.item(obj.selectedIndex);
  pickselection = opt.value;

  jQuery.ajax({
    url:"index.php?module=SDK&action=SDKAjax&file=examples/uitypePicklist/300Ajax",
	dataType:"json",
	type: "post",
	data: "function=linkedListGetChanges"+
		  "&modname="+encodeURIComponent(module)+  // crmv@30528
	      "&name="+encodeURIComponent(pickname)+
	      "&sel="+encodeURIComponent(pickselection),
	async: true,
	cache: false,
	//contentType: "application/json",
	success: function(res) {
	  linkedListUpdateLists(res);
	}
  });

}

// danzi.tn@20150123 per lookup che hanno già il valore collegato ala seconda picklist
/*
 * funzione da chiamare quando la picklist obj cambia
 */
function linkedListChainChange2(obj, module, linkedSel, otherval) { // crmv@30528
  if (!obj) return;
  pickname = obj.name;

  opt = obj.options.item(obj.selectedIndex);
  pickselection = opt.value;

  jQuery.ajax({
    url:"index.php?module=SDK&action=SDKAjax&file=examples/uitypePicklist/300Ajax",
	dataType:"json",
	type: "post",
	data: "function=linkedListGetChanges"+
		  "&modname="+encodeURIComponent(module)+  // crmv@30528
	      "&name="+encodeURIComponent(pickname)+
	      "&sel="+encodeURIComponent(pickselection),
	async: true,
	cache: false,
	//contentType: "application/json",
	success: function(res) {
	  linkedListUpdateLists2(res,linkedSel, otherval);
	}
  });

}


function linkedListUpdateLists2(res, linkedSel, otherval) {
  if (!res) return;

  for (i=0; i<res.length; ++i) {
	
    name = res[i][0];
	if(linkedSel==name) {
		list = res[i][1];
		list_trans = res[i][2];
		otherpl = document.getElementsByName(name);
		// take the first matching element
		if (otherpl.length > 0) {
		 otherpl = otherpl[0];
		} else {
		 // try a multiselect picklist
		 otherpl = document.getElementsByName(name+"[]");
		 if (otherpl.length > 0) otherpl = otherpl[0]; else continue;
		}
		var oldval = otherval;
		// delete inside
		otherpl.innerHTML = "";
		// re-populate
		for (j=0; j<list.length; ++j) {
		  var option = document.createElement("option");
		  option.text = list_trans[j];
		  option.value = list[j];
		  otherpl.add(option);
		  if (option.value == oldval) option.selected = true;
		}

		// change other lists
		if (otherpl.onchange) otherpl.onchange(otherpl);
	}
  }
}
// danzi.tn@20150123e
