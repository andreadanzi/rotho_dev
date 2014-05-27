/*********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
 ********************************************************************************/

//Utility Functions

//crmv@29463
function c_toggleAssignType(currType){
	if (currType=="U")
	{
		document.getElementById("c_assign_user").style.display="block";
		document.getElementById("c_assign_team").style.display="none";
	}
	else
	{
		document.getElementById("c_assign_user").style.display="none";
		document.getElementById("c_assign_team").style.display="block";
	}
}
//crmv@29463e

var gValidationCall='';

if (document.all)

    var browser_ie=true

else if (document.layers)

    var browser_nn4=true

else if (document.layers || (!document.all && document.getElementById))

    var browser_nn6=true

var gBrowserAgent = navigator.userAgent.toLowerCase();

function hideSelect()
{
        var oselect_array = document.getElementsByTagName('SELECT');
        for(var i=0;i<oselect_array.length;i++)
        {
                oselect_array[i].style.display = 'none';
        }
}

function showSelect()
{
        var oselect_array = document.getElementsByTagName('SELECT');
        for(var i=0;i<oselect_array.length;i++)
        {
                oselect_array[i].style.display = 'block';
        }
}

function getObj(n,d) {

	var p,i,x;

	if(!d) {
		d=document;
	}

	if(n != undefined) {
		// crmv@21048m
		if((p=n.indexOf("?"))>0&&parent.frames.length) {
			d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);
		}
		// crmv@21048me
	}

	if(d.getElementById) {
		x=d.getElementById(n);
		// IE7 was returning form element with name = n (if there was multiple instance)
		// But not firefox, so we are making a double check
		if(x && x.id != n) x = false;
	}

	for(i=0;!x && i<d.forms.length;i++) {
		x=d.forms[i][n];
	}

	for(i=0; !x && d.layers && i<d.layers.length;i++) {
		x=getObj(n,d.layers[i].document);
	}

	if(!x && !(x=d[n]) && d.all) {
		x=d.all[n];
	}

	if(typeof x == 'string') {
		x=null;
	}

	return x;
}

function getOpenerObj(n) {
	//crmv@21048m
    return getObj(n,parent.document)
	//crmv@21048m e
}



function findPosX(obj) {

    var curleft = 0;

    if (document.getElementById || document.all) {

        while (obj.offsetParent) {

            curleft += obj.offsetLeft

            obj = obj.offsetParent;

        }

    } else if (document.layers) {

        curleft += obj.x;

    }



    return curleft;

}



function findPosY(obj) {

    var curtop = 0;



    if (document.getElementById || document.all) {

        while (obj.offsetParent) {

            curtop += obj.offsetTop

            obj = obj.offsetParent;

        }

    } else if (document.layers) {

        curtop += obj.y;

    }



    return curtop;

}



function clearTextSelection() {

    if (browser_ie) document.selection.empty();

    else if (browser_nn4 || browser_nn6) window.getSelection().removeAllRanges();

}

// Setting cookies
function set_cookie ( name, value, exp_y, exp_m, exp_d, path, domain, secure )
{
  var cookie_string = name + "=" + escape ( value );

  if (exp_y) //delete_cookie(name)
  {
    var expires = new Date ( exp_y, exp_m, exp_d );
    cookie_string += "; expires=" + expires.toGMTString();
  }

  if (path) cookie_string += "; path=" + escape ( path );
  if (domain) cookie_string += "; domain=" + escape ( domain );
  if (secure) cookie_string += "; secure";

  document.cookie = cookie_string;
}

// Retrieving cookies
function get_cookie(cookie_name)
{
  var results = document.cookie.match(cookie_name + '=(.*?)(;|$)');
  if (results) return (unescape(results[1]));
  else return null;
}

// Delete cookies
function delete_cookie( cookie_name )
{
  var cookie_date = new Date ( );  // current date & time
  cookie_date.setTime ( cookie_date.getTime() - 1 );
  document.cookie = cookie_name += "=; expires=" + cookie_date.toGMTString();
}
//End of Utility Functions



function emptyCheck(fldName,fldLabel, fldType) {
    var currObj=getObj(fldName);
	
	//mycrmv@rotho
	if (fldName=="assigned_user_id") {
		var currObj_group=getObj("assigned_group_id");
		if (currObj.value.replace(/^\s+/g, '').replace(/\s+$/g, '').length==0 && currObj_group.value.replace(/^\s+/g, '').replace(/\s+$/g, '').length==0 ) {
			alert(fldLabel+alert_arr.CANNOT_BE_EMPTY)
			try {
				currObj.focus()
			} catch(error) {
				// Fix for IE: If element or its wrapper around it is hidden, setting focus will fail
				// So using the try { } catch(error) { }
			}
           	return false
        }
        else
        	return true
    }
	//mycrmv@rotho e
	
    if (fldType=="text") {
		if (currObj.value.replace(/^\s+/g, '').replace(/\s+$/g, '').length==0) {
			alert(fldLabel+alert_arr.CANNOT_BE_EMPTY)
			try {
				currObj.focus()
			} catch(error) {
				// Fix for IE: If element or its wrapper around it is hidden, setting focus will fail
				// So using the try { } catch(error) { }
			}
           	return false
        }
        else
        	return true
    }
    //crmv@add checkbox
    else if (fldType=="checkbox") {
        if (currObj.checked == false) {
        	alert(fldLabel+alert_arr.MUST_BE_CHECKED)
            currObj.focus()
            return false
        }
        else
        	return true
    }
    //crmv@add checkbox end
	//crmv@10621
    else if((fldType == "textarea")
	&& (typeof(CKEDITOR)!=='undefined' && typeof(CKEDITOR.instances[fldName]) !== 'undefined')) {
		var textObj = CKEDITOR.instances[fldName];
		var textValue = textObj.getData();
		if (trim(textValue) == '' || trim(textValue) == '<br>') {
		   	alert(fldLabel+alert_arr.CANNOT_BE_NONE);
			return false;
			} else{
		        	return true;
			}
	}	else{
		if (trim(currObj.value) == '') {
			alert(fldLabel+alert_arr.CANNOT_BE_NONE)
    		return false
  		} else
		return true
	}
	//crmv@10621 e
}



function patternValidate(fldName,fldLabel,type) {
    var currObj=getObj(fldName);
    if (type.toUpperCase()=="YAHOO") //Email ID validation
    {
        //yahoo Id validation
        var re=new RegExp(/^[a-z0-9]([a-z0-9_\-\.]*)@([y][a][h][o][o])(\.[a-z]{2,3}(\.[a-z]{2}){0,2})$/)
    }
    if (type.toUpperCase()=="EMAIL") //Email ID validation
    {
        /*changes made to fix -- ticket#3278 & ticket#3461
          var re=new RegExp(/^.+@.+\..+$/)*/
        //Changes made to fix tickets #4633, #5111  to accomodate all possible email formats
        var re=new RegExp(/^[a-zA-Z0-9]+([\_\-\.]*[a-zA-Z0-9]+[\_\-]?)*@[a-zA-Z0-9]+([\_\-]?[a-zA-Z0-9]+)*\.+([\-\_]?[a-zA-Z0-9])+(\.?[a-zA-Z0-9]+)*$/)
    }

    if (type.toUpperCase()=="DATE") {//DATE validation
		//YMD
		//var reg1 = /^\d{2}(\-|\/|\.)\d{1,2}\1\d{1,2}$/ //2 digit year
		//var re = /^\d{4}(\-|\/|\.)\d{1,2}\1\d{1,2}$/ //4 digit year

		//MYD
		//var reg1 = /^\d{1,2}(\-|\/|\.)\d{2}\1\d{1,2}$/
		//var reg2 = /^\d{1,2}(\-|\/|\.)\d{4}\1\d{1,2}$/

	   //DMY
		//var reg1 = /^\d{1,2}(\-|\/|\.)\d{1,2}\1\d{2}$/
		//var reg2 = /^\d{1,2}(\-|\/|\.)\d{1,2}\1\d{4}$/

		switch (userDateFormat) {
			case "yyyy-mm-dd" :
								var re = /^\d{4}(\-|\/|\.)\d{1,2}\1\d{1,2}$/
								break;
			case "mm-dd-yyyy" :
			case "dd-mm-yyyy" :
								var re = /^\d{1,2}(\-|\/|\.)\d{1,2}\1\d{4}$/
		}
	}

	if (type.toUpperCase()=="TIME") {//TIME validation
		var re = /^\d{1,2}\:\d{1,2}$/
	}
	//Asha: Remove spaces on either side of a Email id before validating
	if (type.toUpperCase()=="EMAIL" || type.toUpperCase() == "DATE") currObj.value = trim(currObj.value);
	if (!re.test(currObj.value)) {
		alert(alert_arr.ENTER_VALID + fldLabel  + " ("+type+")");
		try {
			currObj.focus()
		} catch(error) {
			// Fix for IE: If element or its wrapper around it is hidden, setting focus will fail
			// So using the try { } catch(error) { }
		}
		return false
	}
	else return true
}

function splitDateVal(dateval) {
	var datesep;
	var dateelements = new Array(3);

	if (dateval.indexOf("-")>=0) datesep="-"
	else if (dateval.indexOf(".")>=0) datesep="."
	else if (dateval.indexOf("/")>=0) datesep="/"
	//crmv@add some cases
	switch (userDateFormat) {
		case "yyyy-mm-dd" :
		case "yyyy.mm.dd" :
		case "yyyy/mm/dd" :
							dateelements[0]=dateval.substr(dateval.lastIndexOf(datesep)+1,dateval.length) //dd
							dateelements[1]=dateval.substring(dateval.indexOf(datesep)+1,dateval.lastIndexOf(datesep)) //mm
							dateelements[2]=dateval.substring(0,dateval.indexOf(datesep)) //yyyyy
							break;
		case "mm-dd-yyyy" :
		case "mm.dd.yyyy" :
		case "mm/dd/yyyy" :
							dateelements[0]=dateval.substring(dateval.indexOf(datesep)+1,dateval.lastIndexOf(datesep))
							dateelements[1]=dateval.substring(0,dateval.indexOf(datesep))
							dateelements[2]=dateval.substr(dateval.lastIndexOf(datesep)+1,dateval.length)
							break;
		case "dd-mm-yyyy" :
		case "dd.mm.yyyy" :
		case "dd/mm/yyyy" :
							dateelements[0]=dateval.substring(0,dateval.indexOf(datesep))
							dateelements[1]=dateval.substring(dateval.indexOf(datesep)+1,dateval.lastIndexOf(datesep))
							dateelements[2]=dateval.substr(dateval.lastIndexOf(datesep)+1,dateval.length)
	}
	//crmv@add some cases end
	return dateelements;
}

function compareDates(date1,fldLabel1,date2,fldLabel2,type) {
    var ret=true
    switch (type) {
        case 'L'    :    if (date1>=date2) {//DATE1 VALUE LESS THAN DATE2
                            alert(fldLabel1+ alert_arr.SHOULDBE_LESS +fldLabel2)
                            ret=false
                        }
                        break;
        case 'LE'    :    if (date1>date2) {//DATE1 VALUE LESS THAN OR EQUAL TO DATE2
                            alert(fldLabel1+alert_arr.SHOULDBE_LESS_EQUAL+fldLabel2)
                            ret=false
                        }
                        break;
        case 'E'    :    if (date1!=date2) {//DATE1 VALUE EQUAL TO DATE
                            alert(fldLabel1+alert_arr.SHOULDBE_EQUAL+fldLabel2)
                            ret=false
                        }
                        break;
        case 'G'    :    if (date1<=date2) {//DATE1 VALUE GREATER THAN DATE2
                            alert(fldLabel1+alert_arr.SHOULDBE_GREATER+fldLabel2)
                            ret=false
                        }
                        break;
        case 'GE'    :    if (date1<date2) {//DATE1 VALUE GREATER THAN OR EQUAL TO DATE2
                            alert(fldLabel1+alert_arr.SHOULDBE_GREATER_EQUAL+fldLabel2)
                            ret=false
                        }
                        break;
    }

    if (ret==false) return false
    else return true
}

function dateTimeValidate(dateFldName,timeFldName,fldLabel,type) {
	if(patternValidate(dateFldName,fldLabel,"DATE")==false)
		return false;
	dateval=getObj(dateFldName).value.replace(/^\s+/g, '').replace(/\s+$/g, '')

	var dateelements=splitDateVal(dateval)

	dd=dateelements[0]
	mm=dateelements[1]
	yyyy=dateelements[2]

	if (dd<1 || dd>31 || mm<1 || mm>12 || yyyy<1 || yyyy<1000) {
		alert(alert_arr.ENTER_VALID+fldLabel)
		try { getObj(dateFldName).focus() } catch(error) { }
		return false
	}

	if ((mm==2) && (dd>29)) {//checking of no. of days in february month
		alert(alert_arr.ENTER_VALID+fldLabel)
		try { getObj(dateFldName).focus() } catch(error) { }
		return false
	}

	if ((mm==2) && (dd>28) && ((yyyy%4)!=0)) {//leap year checking
		alert(alert_arr.ENTER_VALID+fldLabel)
		try { getObj(dateFldName).focus() } catch(error) { }
		return false
	}

    switch (parseInt(mm)) {
        case 2 :
        case 4 :
        case 6 :
        case 9 :
        case 11 :	if (dd>30) {
						alert(alert_arr.ENTER_VALID+fldLabel)
						try { getObj(dateFldName).focus() } catch(error) { }
						return false
					}
    }

    if (patternValidate(timeFldName,fldLabel,"TIME")==false)
        return false

    var timeval=getObj(timeFldName).value.replace(/^\s+/g, '').replace(/\s+$/g, '')
    var hourval=parseInt(timeval.substring(0,timeval.indexOf(":")))
    var minval=parseInt(timeval.substring(timeval.indexOf(":")+1,timeval.length))
    var currObj=getObj(timeFldName)

	if (hourval>23 || minval>59) {
		alert(alert_arr.ENTER_VALID+fldLabel)
		try { currObj.focus() } catch(error) { }
		return false
	}

    var currdate=new Date()
    var chkdate=new Date()

    chkdate.setYear(yyyy)
    chkdate.setMonth(mm-1)
    chkdate.setDate(dd)
    chkdate.setHours(hourval)
    chkdate.setMinutes(minval)

	if (type!="OTH") {
		if (!compareDates(chkdate,fldLabel,currdate,"current date & time",type)) {
			try { getObj(dateFldName).focus() } catch(error) { }
			return false
		} else return true;
	} else return true;
}

function dateTimeComparison(dateFldName1,timeFldName1,fldLabel1,dateFldName2,timeFldName2,fldLabel2,type) {
    var dateval1=getObj(dateFldName1).value.replace(/^\s+/g, '').replace(/\s+$/g, '')
    var dateval2=getObj(dateFldName2).value.replace(/^\s+/g, '').replace(/\s+$/g, '')

    var dateelements1=splitDateVal(dateval1)
    var dateelements2=splitDateVal(dateval2)

    dd1=dateelements1[0]
    mm1=dateelements1[1]
    yyyy1=dateelements1[2]

    dd2=dateelements2[0]
    mm2=dateelements2[1]
    yyyy2=dateelements2[2]

    var timeval1=getObj(timeFldName1).value.replace(/^\s+/g, '').replace(/\s+$/g, '')
    var timeval2=getObj(timeFldName2).value.replace(/^\s+/g, '').replace(/\s+$/g, '')

    var hh1=timeval1.substring(0,timeval1.indexOf(":"))
    var min1=timeval1.substring(timeval1.indexOf(":")+1,timeval1.length)

    var hh2=timeval2.substring(0,timeval2.indexOf(":"))
    var min2=timeval2.substring(timeval2.indexOf(":")+1,timeval2.length)

    var date1=new Date()
    var date2=new Date()

    date1.setYear(yyyy1)
    date1.setMonth(mm1-1)
    date1.setDate(dd1)
    date1.setHours(hh1)
    date1.setMinutes(min1)

    date2.setYear(yyyy2)
    date2.setMonth(mm2-1)
    date2.setDate(dd2)
    date2.setHours(hh2)
    date2.setMinutes(min2)

	if (type!="OTH") {
		if (!compareDates(date1,fldLabel1,date2,fldLabel2,type)) {
			try { getObj(dateFldName1).focus() } catch(error) { }
			return false
		} else return true;
	} else return true;
}

function dateValidate(fldName,fldLabel,type) {
    if(patternValidate(fldName,fldLabel,"DATE")==false)
        return false;
    dateval=getObj(fldName).value.replace(/^\s+/g, '').replace(/\s+$/g, '')

    var dateelements=splitDateVal(dateval)

    dd=dateelements[0]
    mm=dateelements[1]
    yyyy=dateelements[2]

	if (dd<1 || dd>31 || mm<1 || mm>12 || yyyy<1 || yyyy<1000) {
		alert(alert_arr.ENTER_VALID+fldLabel)
		try { getObj(fldName).focus() } catch(error) { }
		return false
	}

	if ((mm==2) && (dd>29)) {//checking of no. of days in february month
		alert(alert_arr.ENTER_VALID+fldLabel)
		try { getObj(fldName).focus() } catch(error) { }
		return false
	}

	if ((mm==2) && (dd>28) && ((yyyy%4)!=0)) {//leap year checking
		alert(alert_arr.ENTER_VALID+fldLabel)
		try { getObj(fldName).focus() } catch(error) { }
		return false
	}

    switch (parseInt(mm)) {
        case 2 :
        case 4 :
        case 6 :
        case 9 :
		case 11 :	if (dd>30) {
						alert(alert_arr.ENTER_VALID+fldLabel)
						try { getObj(fldName).focus() } catch(error) { }
						return false
					}
    }

    var currdate=new Date()
    var chkdate=new Date()

    chkdate.setYear(yyyy)
    chkdate.setMonth(mm-1)
    chkdate.setDate(dd)

	if (type!="OTH") {
		if (!compareDates(chkdate,fldLabel,currdate,"current date",type)) {
			try { getObj(fldName).focus() } catch(error) { }
			return false
		} else return true;
	} else return true;
}

function dateComparison(fldName1,fldLabel1,fldName2,fldLabel2,type) {
    var dateval1=getObj(fldName1).value.replace(/^\s+/g, '').replace(/\s+$/g, '')
    var dateval2=getObj(fldName2).value.replace(/^\s+/g, '').replace(/\s+$/g, '')

    var dateelements1=splitDateVal(dateval1)
    var dateelements2=splitDateVal(dateval2)

    dd1=dateelements1[0]
    mm1=dateelements1[1]
    yyyy1=dateelements1[2]

    dd2=dateelements2[0]
    mm2=dateelements2[1]
    yyyy2=dateelements2[2]

    var date1=new Date()
    var date2=new Date()

    date1.setYear(yyyy1)
    date1.setMonth(mm1-1)
    date1.setDate(dd1)

    date2.setYear(yyyy2)
    date2.setMonth(mm2-1)
    date2.setDate(dd2)

	if (type!="OTH") {
		if (!compareDates(date1,fldLabel1,date2,fldLabel2,type)) {
			try { getObj(fldName1).focus() } catch(error) { }
			return false
		} else return true;
	} else return true
}

function timeValidate(fldName,fldLabel,type) {
    if (patternValidate(fldName,fldLabel,"TIME")==false)
        return false

    var timeval=getObj(fldName).value.replace(/^\s+/g, '').replace(/\s+$/g, '')
    var hourval=parseInt(timeval.substring(0,timeval.indexOf(":")))
    var minval=parseInt(timeval.substring(timeval.indexOf(":")+1,timeval.length))
    var currObj=getObj(fldName)

	if (hourval>23 || minval>59) {
		alert(alert_arr.ENTER_VALID+fldLabel)
		try { currObj.focus() } catch(error) { }
		return false
	}

    var currtime=new Date()
    var chktime=new Date()

    chktime.setHours(hourval)
    chktime.setMinutes(minval)

	if (type!="OTH") {
		if (!compareDates(chktime,fldLabel,currtime,"current time",type)) {
			try { getObj(fldName).focus() } catch(error) { }
			return false
		} else return true;
	} else return true
}

function timeComparison(fldName1,fldLabel1,fldName2,fldLabel2,type) {
    var timeval1=getObj(fldName1).value.replace(/^\s+/g, '').replace(/\s+$/g, '')
    var timeval2=getObj(fldName2).value.replace(/^\s+/g, '').replace(/\s+$/g, '')

    var hh1=timeval1.substring(0,timeval1.indexOf(":"))
    var min1=timeval1.substring(timeval1.indexOf(":")+1,timeval1.length)

    var hh2=timeval2.substring(0,timeval2.indexOf(":"))
    var min2=timeval2.substring(timeval2.indexOf(":")+1,timeval2.length)

    var time1=new Date()
    var time2=new Date()

    //added to fix the ticket #5028
    if(fldName1 == "time_end" && (getObj("due_date") && getObj("date_start")))
    {
        var due_date=getObj("due_date").value.replace(/^\s+/g, '').replace(/\s+$/g, '')
        var start_date=getObj("date_start").value.replace(/^\s+/g, '').replace(/\s+$/g, '')
        dateval1 = splitDateVal(due_date);
        dateval2 = splitDateVal(start_date);

        dd1 = dateval1[0];
        mm1 = dateval1[1];
        yyyy1 = dateval1[2];

        dd2 = dateval2[0];
        mm2 = dateval2[1];
        yyyy2 = dateval2[2];

        time1.setYear(yyyy1)
        time1.setMonth(mm1-1)
        time1.setDate(dd1)

        time2.setYear(yyyy2)
        time2.setMonth(mm2-1)
        time2.setDate(dd2)

    }
    //end

    time1.setHours(hh1)
    time1.setMinutes(min1)

    time2.setHours(hh2)
    time2.setMinutes(min2)
	if (type!="OTH") {
		if (!compareDates(time1,fldLabel1,time2,fldLabel2,type)) {
			try { getObj(fldName1).focus() } catch(error) { }
			return false
		} else return true;
	} else return true;
}

function numValidate(fldName,fldLabel,format,neg) {
   var val=getObj(fldName).value.replace(/^\s+/g, '').replace(/\s+$/g, '')
   if (format!="any") {
       if (isNaN(val)) {
           var invalid=true
       } else {
           var format=format.split(",")
           var splitval=val.split(".")
           if (neg==true) {
               if (splitval[0].indexOf("-")>=0) {
                   if (splitval[0].length-1>format[0])
                       invalid=true
               } else {
                   if (splitval[0].length>format[0])
                       invalid=true
               }
           } else {
               if (val<0)
                   invalid=true
           else if (format[0]==2 && splitval[0]==100 && (!splitval[1] || splitval[1]==0))
           invalid=false
               else if (splitval[0].length>format[0])
                   invalid=true
           }
                      if (splitval[1])
               if (splitval[1].length>format[1])
                   invalid=true
       }
              if (invalid==true) {
           alert(alert_arr.INVALID+fldLabel)
           try { getObj(fldName).focus() } catch(error) { }
           return false
       } else return true
   } else {
       // changes made -- to fix the ticket#3272
       var splitval=val.split(".")
       var arr_len = splitval.length;
           var len = 0;
       if(fldName == "probability" || fldName == "commissionrate")
           {
                   if(arr_len > 1)
                           len = splitval[1].length;
                   if(isNaN(val))
                   {
                        alert(alert_arr.INVALID+fldLabel)
                        try { getObj(fldName).focus() } catch(error) { }
                        return false
                   }
                   else if(splitval[0] > 100 || len > 3 || (splitval[0] >= 100 && splitval[1] > 0))
                   {
                        alert( fldLabel + alert_arr.EXCEEDS_MAX);
                        return false;
                   }
           }
       else if(splitval[0]>18446744073709551615)
           {
                   alert( fldLabel + alert_arr.EXCEEDS_MAX);
                   return false;
           }


       if (neg==true)
           var re=/^(-|)(\d)*(\.)?\d+(\.\d\d*)*$/
       else
       var re=/^(\d)*(\.)?\d+(\.\d\d*)*$/
   }

    //for precision check. ie.number must contains only one "."
    var dotcount=0;
    for (var i = 0; i < val.length; i++)
    {
          if (val.charAt(i) == ".")
             dotcount++;
    }

	if(dotcount>1)
	{
       		alert(alert_arr.INVALID+fldLabel)
		try { getObj(fldName).focus() } catch(error) { }
		return false;
	}

	if (!re.test(val)) {
       alert(alert_arr.INVALID+fldLabel)
       try { getObj(fldName).focus() } catch(error) { }
       return false
   } else return true
}


function intValidate(fldName,fldLabel) {
	var val=getObj(fldName).value.replace(/^\s+/g, '').replace(/\s+$/g, '')
	if (isNaN(val) || (val.indexOf(".")!=-1 && fldName != 'potential_amount' && fldName != 'list_price'))
	{
		alert(alert_arr.INVALID+fldLabel)
		try { getObj(fldName).focus() } catch(error) { }
		return false
	}
        else if((fldName != 'employees' || fldName != 'noofemployees') && (val < -2147483648 || val > 2147483647))
        {
                alert(fldLabel +alert_arr.OUT_OF_RANGE);
                return false;
        }
	else if((fldName == 'employees' || fldName != 'noofemployees') && (val < 0 || val > 2147483647))
        {
                alert(fldLabel +alert_arr.OUT_OF_RANGE);
                return false;
        }
	else
	{
		return true
	}
}

function numConstComp(fldName,fldLabel,type,constval) {
    var val=parseFloat(getObj(fldName).value.replace(/^\s+/g, '').replace(/\s+$/g, ''))
    constval=parseFloat(constval)

    var ret=true
    switch (type) {
        case "L"  : if (val>=constval) {
                        alert(fldLabel+alert_arr.SHOULDBE_LESS+constval)
                        ret=false
                    }
                    break;
        case "LE" :    if (val>constval) {
                    alert(fldLabel+alert_arr.SHOULDBE_LESS_EQUAL+constval)
                    ret=false
                    }
                    break;
        case "E"  :    if (val!=constval) {
                                        alert(fldLabel+alert_arr.SHOULDBE_EQUAL+constval)
                                        ret=false
                                }
                                break;
        case "NE" : if (val==constval) {
                         alert(fldLabel+alert_arr.SHOULDNOTBE_EQUAL+constval)
                            ret=false
                    }
                    break;
        case "G"  :    if (val<=constval) {
                            alert(fldLabel+alert_arr.SHOULDBE_GREATER+constval)
                            ret=false
                    }
                    break;
        case "GE" : if (val<constval) {
                            alert(fldLabel+alert_arr.SHOULDBE_GREATER_EQUAL+constval)
                            ret=false
                    }
                    break;
    }

	if (ret==false) {
		try { getObj(fldName).focus() } catch(error) { }
		return false
	} else return true;
}

/* To get only filename from a given complete file path */
function getFileNameOnly(filename) {
  var onlyfilename = filename;
  // Normalize the path (to make sure we use the same path separator)
  var filename_normalized = filename.replace(/\\/g, '/');
  if(filename_normalized.lastIndexOf("/") != -1) {
    onlyfilename = filename_normalized.substring(filename_normalized.lastIndexOf("/") + 1);
  }
  return onlyfilename;
}

/* Function to validate the filename */
function validateFilename(form_ele) {
        if (form_ele.value == '') return true;
        var value = getFileNameOnly(form_ele.value);

        // Color highlighting logic
        var err_bg_color = "#FFAA22";

        if (typeof(form_ele.bgcolor) == "undefined") {
                form_ele.bgcolor = form_ele.style.backgroundColor;
        }

        // Validation starts here
        var valid = true;

        /* Filename length is constrained to 255 at database level */
        if (value.length > 255) {
                alert(alert_arr.LBL_FILENAME_LENGTH_EXCEED_ERR);
                valid = false;
        }

        if (!valid) {
                form_ele.style.backgroundColor = err_bg_color;
                return false;
        }
        form_ele.style.backgroundColor = form_ele.bgcolor;
        form_ele.form[form_ele.name + '_hidden'].value = value;
        return true;
}
//crmv@sdk-18501
function formValidate(form){
	return doformValidation('',form);
}
//crmv@sdk-18501 e
function massEditFormValidate(){
	return doformValidation('mass_edit');
}

function doformValidation(edit_type,form) {	//crmv@sdk-18501
	//Validation for Portal User
	//crmv@fix portal
	if(gVTModule == 'Contacts' && gValidationCall != 'tabchange' && isdefined('existing_portal') && isdefined('portal'))
	{
		//if existing portal value = 0, portal checkbox = checked, ( email field is not available OR  email is empty ) then we should not allow -- OR --
		//if existing portal value = 1, portal checkbox = checked, ( email field is available     AND email is empty ) then we should not allow
		if(edit_type=='')
		{
			if((getObj('existing_portal').value == 0 && getObj('portal').checked && (getObj('email') == null || trim(getObj('email').value) == '')) ||
			    getObj('existing_portal').value == 1 && getObj('portal').checked && getObj('email') != null && trim(getObj('email').value) == '')
			{
				alert(alert_arr.PORTAL_PROVIDE_EMAILID);
				return false;
			}
		}
		else
		{
			if(getObj('portal') != null && getObj('portal').checked && getObj('portal_mass_edit_check').checked && (getObj('email') == null || trim(getObj('email').value) == '' || getObj('email_mass_edit_check').checked==false))
			{
				alert(alert_arr.PORTAL_PROVIDE_EMAILID);
				return false;
			}
			if((getObj('email') != null && trim(getObj('email').value) == '' && getObj('email_mass_edit_check').checked) && !(getObj('portal').checked==false && getObj('portal_mass_edit_check').checked))
			{
				alert(alert_arr.EMAIL_CHECK_MSG);
				return false;
			}
		}
	}
	//crmv@fix portal end
	if(gVTModule == 'SalesOrder') {
		if(edit_type == 'mass_edit') {
			if (getObj('enable_recurring_mass_edit_check') != null
				&& getObj('enable_recurring_mass_edit_check').checked
				&& getObj('enable_recurring') != null) {
					if(getObj('enable_recurring').checked && (getObj('recurring_frequency') == null
						|| trim(getObj('recurring_frequency').value) == '--None--' || getObj('recurring_frequency_mass_edit_check').checked==false)) {
						alert(alert_arr.RECURRING_FREQUENCY_NOT_PROVIDED);
						return false;
					}
					if(getObj('enable_recurring').checked == false && getObj('recurring_frequency_mass_edit_check').checked
						&& getObj('recurring_frequency') != null && trim(getObj('recurring_frequency').value) !=  '--None--') {
						alert(alert_arr.RECURRING_FREQNECY_NOT_ENABLED);
						return false;
					}
			}
		} else if(getObj('enable_recurring') != null && getObj('enable_recurring').checked) {
			if(getObj('recurring_frequency') == null || getObj('recurring_frequency').value == '--None--') {
				alert(alert_arr.RECURRING_FREQUENCY_NOT_PROVIDED);
				return false;
			}
			var start_period = getObj('start_period');
			var end_period = getObj('end_period');
			if (trim(start_period.value) == '' || trim(end_period.value) == '') {
				alert(alert_arr.START_PERIOD_END_PERIOD_CANNOT_BE_EMPTY);
        		return false;
      		}
		}
	}
    for (var i=0; i<fieldname.length; i++) {
		if(edit_type == 'mass_edit') {
			if(fieldname[i]!='salutationtype')
			var obj = getObj(fieldname[i]+"_mass_edit_check");
			if(obj == null || obj.checked == false) continue;
		}
        if(getObj(fieldname[i]) != null)
        {
            var type=fielddatatype[i].split("~")
                if (type[1]=="M") {
                    if (!emptyCheck(fieldname[i],fieldlabel[i],getObj(fieldname[i]).type))
                        return false
                }
            switch (type[0]) {
                case "O"  : break;
                case "V"  :
                	//crmv@add textlength check
                	if (type[2] && type[3]){
	            		if (!lengthComparison(fieldname[i],fieldlabel[i],type[2],type[3]))
	            			return false;
                	};
                	//crmv@add textlength check end
                	break;
                case "C"  : break;
                case "DT" :
                    if (getObj(fieldname[i]) != null && getObj(fieldname[i]).value.replace(/^\s+/g, '').replace(/\s+$/g, '').length!=0)
                    {
                        if (type[1]=="M")
                            if (!emptyCheck(fieldname[2],fieldlabel[i],getObj(type[2]).type))
                                return false

                                    if(typeof(type[3])=="undefined") var currdatechk="OTH"
                                    else var currdatechk=type[3]

                                        if (!dateTimeValidate(fieldname[i],type[2],fieldlabel[i],currdatechk))
                                            return false
                                                if (type[4]) {
                                                    if (!dateTimeComparison(fieldname[i],type[2],fieldlabel[i],type[5],type[6],type[4]))
                                                        return false

                                                }
                    }
                break;
                case "D"  :
                    if (getObj(fieldname[i]) != null && getObj(fieldname[i]).value.replace(/^\s+/g, '').replace(/\s+$/g, '').length!=0)
                    {
                        if(typeof(type[2])=="undefined") var currdatechk="OTH"
                        else var currdatechk=type[2]
                        if (!dateValidate(fieldname[i],fieldlabel[i],currdatechk))
                        	return false
  						if (type[3]) {
							if(gVTModule == 'SalesOrder' && fieldname[i] == 'end_period'
								&& (getObj('enable_recurring') == null || getObj('enable_recurring').checked == false)) {
								continue;
							}
							if (!dateComparison(fieldname[i],fieldlabel[i],type[4],type[5],type[3]))
								return false
						}
                    }
                break;
                case "T"  :
                    if (getObj(fieldname[i]) != null && getObj(fieldname[i]).value.replace(/^\s+/g, '').replace(/\s+$/g, '').length!=0)
                    {
                        if(typeof(type[2])=="undefined") var currtimechk="OTH"
                        else var currtimechk=type[2]

                            if (!timeValidate(fieldname[i],fieldlabel[i],currtimechk))
                                return false
                                    if (type[3]) {
                                        if (!timeComparison(fieldname[i],fieldlabel[i],type[4],type[5],type[3]))
                                            return false
                                    }
                    }
                break;
                case "I"  :
                    if (getObj(fieldname[i]) != null && getObj(fieldname[i]).value.replace(/^\s+/g, '').replace(/\s+$/g, '').length!=0)
                    {
                        if (getObj(fieldname[i]).value.length!=0)
                        {
                            if (!intValidate(fieldname[i],fieldlabel[i]))
                                return false
                                    if (type[2]) {
                                        if (!numConstComp(fieldname[i],fieldlabel[i],type[2],type[3]))
                                            return false
                                    }
                        }
                    }
                break;
                case "N"  :
                    case "NN" :
                    if (getObj(fieldname[i]) != null && getObj(fieldname[i]).value.replace(/^\s+/g, '').replace(/\s+$/g, '').length!=0)
                    {
                        if (getObj(fieldname[i]).value.length!=0)
                        {
                            if (typeof(type[2])=="undefined") var numformat="any"
                            else var numformat=type[2]
                            if(type[0]=="NN")
                            {
                                if (!numValidate(fieldname[i],fieldlabel[i],numformat,true))
                                return false
                            }
                            else if (!numValidate(fieldname[i],fieldlabel[i],numformat))
                                return false
                            if (type[3]) {
                                if (!numConstComp(fieldname[i],fieldlabel[i],type[3],type[4]))
                                    return false
                            }
                        }
                    }
                break;
                case "E"  :
                    if (getObj(fieldname[i]) != null && getObj(fieldname[i]).value.replace(/^\s+/g, '').replace(/\s+$/g, '').length!=0)
                    {
                        if (getObj(fieldname[i]).value.length!=0)
                        {
                            var etype = "EMAIL"
                            if(fieldname[i] == "yahooid" || fieldname[i] == "yahoo_id")
                            {
                                etype = "YAHOO";
                            }
                            if (!patternValidate(fieldname[i],fieldlabel[i],etype))
                                return false;
                        }
                    }
                break;
                //crmv@vtc
                case "PIVA" :
                // controllo il campo Partita Iva
                	if (getObj(fieldname[i]) != null && getObj(fieldname[i]).value.replace(/^\s+/g, '').replace(/\s+$/g, '').length!=0)
                    {
                		res = getFile('index.php?module=Utilities&action=UtilitiesAjax&file=CheckPiva&piva='+getObj(fieldname[i]).value);
						if (res == "false") {
							alert ('Partita IVA non valida!');
							getObj(fieldname[i]).focus();
							return false;
						}
                    }
                break;
                case "CF" :
                // controllo il campo Codice Fiscale
                	if (getObj(fieldname[i]) != null && getObj(fieldname[i]).value.replace(/^\s+/g, '').replace(/\s+$/g, '').length!=0)
                	{
                		res = getFile('index.php?module=Utilities&action=UtilitiesAjax&file=CheckCF&cf='+getObj(fieldname[i]).value);
                		if (res == "false") {
                			alert ('Codice Fiscale non valido!');
                			getObj(fieldname[i]).focus();
                			return false;
                		}
                	}
            	break;
                //crmv@vtc end
            }
            //start Birth day date validation
            if(fieldname[i] == "birthday" && getObj(fieldname[i]).value.replace(/^\s+/g, '').replace(/\s+$/g, '').length!=0 )
            {
                var now =new Date()
                var currtimechk="OTH"
                var datelabel = fieldlabel[i]
                var datefield = fieldname[i]
                var datevalue =getObj(datefield).value.replace(/^\s+/g, '').replace(/\s+$/g, '')
                            if (!dateValidate(fieldname[i],fieldlabel[i],currdatechk))
                {
                            getObj(datefield).focus()
                                return false
                }
                else
                {
                    datearr=splitDateVal(datevalue);
                    dd=datearr[0]
                    mm=datearr[1]
                    yyyy=datearr[2]
                    var datecheck = new Date()
                        datecheck.setYear(yyyy)
                        datecheck.setMonth(mm-1)
                        datecheck.setDate(dd)
                            if (!compareDates(datecheck,datelabel,now,"Current Date","L"))
                    {
                                    getObj(datefield).focus()
                                    return false
                            }
                }
            }
              //End Birth day
        }

    }
    if(gVTModule == 'Contacts')
        {
                if(getObj('imagename'))
                {
                      if(getObj('imagename').value != '')
                        {
                                var image_arr = new Array();
                                var image_arr = (getObj('imagename').value).split(".");
                                var count = (image_arr.length)-1;
                                var image_ext = image_arr[count].toLowerCase();
                                if(image_ext ==  "jpeg" || image_ext ==  "png" || image_ext ==  "jpg" || image_ext ==  "pjpeg" || image_ext ==  "x-png" || image_ext ==  "gif")
                                {
                                        return true;
                                }
                                else
                                {
                                        alert(alert_arr.LBL_WRONG_IMAGE_TYPE);
                                        return false;
                                }
                        }
                }
                //ds@5e
        }

       //added to check Start Date & Time,if Activity Status is Planned.//start
        for (var j=0; j<fieldname.length; j++)
    {
        if(getObj(fieldname[j]) != null)
        {
            if(fieldname[j] == "date_start" || fieldname[j] == "task_date_start" )
            {
                var datelabel = fieldlabel[j]
                var datefield = fieldname[j]
                var startdatevalue = getObj(datefield).value.replace(/^\s+/g, '').replace(/\s+$/g, '')
            }
            if(fieldname[j] == "time_start" || fieldname[j] == "task_time_start")
            {
                var timelabel = fieldlabel[j]
                var timefield = fieldname[j]
                var timeval=getObj(timefield).value.replace(/^\s+/g, '').replace(/\s+$/g, '')
            }
            if(fieldname[j] == "eventstatus" || fieldname[j] == "taskstatus")
            {
                var statusvalue = getObj(fieldname[j]).value.replace(/^\s+/g, '').replace(/\s+$/g, '')
                var statuslabel = fieldlabel[j++]
            }
        }
    }
    if(statusvalue == "Planned")
        {
                var dateelements=splitDateVal(startdatevalue)

                var hourval=parseInt(timeval.substring(0,timeval.indexOf(":")))
                var minval=parseInt(timeval.substring(timeval.indexOf(":")+1,timeval.length))


               dd=dateelements[0]
               mm=dateelements[1]
               yyyy=dateelements[2]

               var chkdate=new Date()
               chkdate.setYear(yyyy)
               chkdate.setMonth(mm-1)
               chkdate.setDate(dd)
               chkdate.setMinutes(minval)
               chkdate.setHours(hourval)
        if(!comparestartdate(chkdate)) return false;
     }//end

	 // We need to enforce fileupload for internal type
	 if(gVTModule == 'Documents') {
	 	if(getObj('filelocationtype') !== undefined && getObj('filelocationtype').value == 'I') {	//crmv@fix
			if(getObj('filename_hidden').value == '') {
				alert(alert_arr.LBL_PLEASE_SELECT_FILE_TO_UPLOAD);
				return false;
			}
		}
	 }
	//crmv@sdk-18501
	sdkValidate = SDKValidate(form);
	if (sdkValidate) {
		sdkValidateResponse = eval('('+sdkValidate.responseText+')');
		if (!sdkValidateResponse['status']) {
			return false;
		}
	}
	//crmv@sdk-18501 e
	//crmv@7231
    if (!AjaxDuplicateValidate(gVTModule,form))	//crmv@25101
    	return false;
 	return true;
	//crmv@7231 end
}
//crmv@save
function SubmitForm(Addform,id,module){
	if (formValidate(Addform)){	//crmv@sdk-18501
		//crmv@19653
		if(module == 'Accounts'){
	 		if (isdefined('external_code')){
	 			var ext=getObj('external_code').value
	 			var exttype=getObj('external_code').type
	 			if ( (trim(ext) != '') && (exttype != "hidden") ) {
	 				if (!AjaxDuplicateValidateEXT_CODE(module,'external_code','','editview'))
	 					return false;
	 			}
	 		}
	 	}
	 	//crmv@19653e
		if (module == 'Accounts' && id != '')
			checkAddress(Addform,id);
		else {
			VtigerJS_DialogBox.block(); //mycrmv@38557			
			Addform.submit();
		}
	}
}
//crmv@save end
//crmv@ajax duplicate
function AjaxDuplicateValidate(module,form)
{
	if (typeof(form) == 'undefined')
		form = 'EditView';
	else
		form = form.name;
	oform = document.forms[form];
	//crmv@23984
	//crmv@26280
	if (merge_user_fields != undefined && merge_user_fields[module] != undefined) {
		fieldvalues = merge_user_fields[module];
	} else {
	    var url = "module=Utilities&action=UtilitiesAjax&file=CheckDuplicate&formodule="+module+"&action_ajax=get_merge_fields";
		var res = getFile('index.php?'+url);
		res = eval('('+res+ ')');
		if (res['success']){
			fieldvalues = res['fieldvalues'];
		}
		else{
			return true;
		}
	}
	var count=fieldvalues.length;
	//crmv@26280e
	if (count == 0){
		return true;
	}
	for(i=0;i<count;i++){
		if (isdefined(fieldvalues[i]['fieldname'])){
			if (fieldvalues[i]['uitype'] == 56 || fieldvalues[i]['uitype'] == 156){
				if (getObj(fieldvalues[i]['fieldname']).checked == true){
					fieldvalues[i]['value'] = 1;
				}
				else{
					fieldvalues[i]['value'] = 0;
				}
			}
			else if (fieldvalues[i]['uitype'] == 33) {
				var selvalues = {};
				jQuery("#"+fieldvalues[i]['fieldname']+" :selected").each(function(i, selected){
					selvalues[i] = jQuery(selected).value();
				});
				fieldvalues[i]['value'] = selvalues.join(" |##| ");
			}
			else{
				fieldvalues[i]['value']=getObj(fieldvalues[i]['fieldname']).value
			}
		}
		else{
			delete fieldvalues[i];
		}
	}
	count=fieldvalues.length;
	if (count == 0){
		return true;
	}
	var record = '';
	if (isdefined('record')){
		record = getObj('record').value;
	}
	//crmv@24240
    var url = "module=Utilities&action=UtilitiesAjax&file=CheckDuplicate&formodule="+module+"&action_ajax=control_duplicate&fieldvalues="+escapeAll(JSON.stringify(fieldvalues))+"&record="+record;
    //crmv@24240e
    var res = getFile('index.php?'+url);
    res = eval('('+res+ ')');
	if(res['success'] == true){
		msg = alert_arr.EXISTING_RECORD;
		for (var data in res['data']){
			msg+="\n"+ data +": "+ res['data'][data];
		}
		//crmv@19438
		if (oform.name == 'ConvertLead')
		msg+="\n"+ alert_arr.EXISTING_SAVE_CONVERTLEAD;
		//crmv@19438e
		msg+="\n"+ alert_arr.EXISTING_SAVE;
		if (!confirm(msg)){
			return false;
		}
	}
	//crmv@23984e
    return true;
}
//crmv@ajax duplicate end
function clearId(fldName) {

    var currObj=getObj(fldName)

    currObj.value=""

}
//crmv@fix async call
//crmv@32334
function comparestartdate(chkdate)
{
    var datObj = [];
        var url = "module=Utilities&action=UtilitiesAjax&file=checkstartdate";
        var currdate = new Date();
        res = getFile('index.php?'+url);
        datObj = eval(res);
        currdate.setFullYear(datObj[0].YEAR);
        currdate.setMonth(datObj[0].MONTH-1);
        currdate.setDate(datObj[0].DAY);
        currdate.setHours(datObj[0].HOUR);
        currdate.setMinutes(datObj[0].MINUTE);
        return compareDates(chkdate,alert_arr.START_DATE_TIME,currdate,alert_arr.DATE_SHOULDNOT_PAST,"GE");
}
//crmv@32334 e
//crmv@fix async call end
function showCalc(fldName) {
    var currObj=getObj(fldName)
    openPopUp("calcWin",currObj,"/crm/Calc.do?currFld="+fldName,"Calc",170,220,"menubar=no,toolbar=no,location=no,status=no,scrollbars=no,resizable=yes")
}

function showLookUp(fldName,fldId,fldLabel,searchmodule,hostName,serverPort,username) {
    var currObj=getObj(fldName)

    //var fldValue=currObj.value.replace(/^\s+/g, '').replace(/\s+$/g, '')

    //need to pass the name of the system in which the server is running so that even when the search is invoked from another system, the url will remain the same

    openPopUp("lookUpWin",currObj,"/crm/Search.do?searchmodule="+searchmodule+"&fldName="+fldName+"&fldId="+fldId+"&fldLabel="+fldLabel+"&fldValue=&user="+username,"LookUp",500,400,"menubar=no,toolbar=no,location=no,status=no,scrollbars=yes,resizable=yes")
}

function openPopUp(winInst,currObj,baseURL,winName,width,height,features) {

    var left=parseInt(findPosX(currObj))
    var top=parseInt(findPosY(currObj))

    if (window.navigator.appName!="Opera") top+=parseInt(currObj.offsetHeight)
    else top+=(parseInt(currObj.offsetHeight)*2)+10

    if (browser_ie)    {
        top+=window.screenTop-document.body.scrollTop
        left-=document.body.scrollLeft
        if (top+height+30>window.screen.height)
            top=findPosY(currObj)+window.screenTop-height-30 //30 is a constant to avoid positioning issue
        if (left+width>window.screen.width)
            left=findPosX(currObj)+window.screenLeft-width
    } else if (browser_nn4 || browser_nn6) {
        top+=(scrY-pgeY)
        left+=(scrX-pgeX)
        if (top+height+30>window.screen.height)
            top=findPosY(currObj)+(scrY-pgeY)-height-30
        if (left+width>window.screen.width)
            left=findPosX(currObj)+(scrX-pgeX)-width
    }

    features="width="+width+",height="+height+",top="+top+",left="+left+";"+features
    eval(winInst+'=openPopup("'+baseURL+'","'+winName+'","'+features+'","auto")');//crmv@21048m
}

var scrX=0,scrY=0,pgeX=0,pgeY=0;

if (browser_nn4 || browser_nn6) {
    document.addEventListener("click",popUpListener,true)
}

function popUpListener(ev) {
    if (browser_nn4 || browser_nn6) {
        scrX=ev.screenX
        scrY=ev.screenY
        pgeX=ev.pageX
        pgeY=ev.pageY
    }
}

function toggleSelect(state,relCheckName) {
    if (getObj(relCheckName)) {
        if (typeof(getObj(relCheckName).length)=="undefined") {
            getObj(relCheckName).checked=state
        } else {
            for (var i=0;i<getObj(relCheckName).length;i++)
                getObj(relCheckName)[i].checked=state
        }
    }
}

function toggleSelectAll(relCheckName,selectAllName) {
    if (typeof(getObj(relCheckName).length)=="undefined") {
        getObj(selectAllName).checked=getObj(relCheckName).checked
    } else {
        var atleastOneFalse=false;
        for (var i=0;i<getObj(relCheckName).length;i++) {
            if (getObj(relCheckName)[i].checked==false) {
                atleastOneFalse=true
                break;
            }
        }
        getObj(selectAllName).checked=!atleastOneFalse
    }
}
//added for show/hide 10July
function expandCont(bn)
{
    var leftTab = document.getElementById(bn);
           leftTab.style.display = (leftTab.style.display == "block")?"none":"block";
           img = document.getElementById("img_"+bn);
          img.src=(img.src.indexOf("images/toggle1.gif")!=-1)?"themes/images/toggle2.gif":"themes/images/toggle1.gif";
          set_cookie_gen(bn,leftTab.style.display)

}

function setExpandCollapse_gen()
{
    var x = leftpanelistarray.length;
    for (i = 0 ; i < x ; i++)
    {
        var listObj=getObj(leftpanelistarray[i])
        var tgImageObj=getObj("img_"+leftpanelistarray[i])
        var status = get_cookie_gen(leftpanelistarray[i])

        if (status == "block") {
            listObj.style.display="block";
            tgImageObj.src="themes/images/toggle2.gif";
        } else if(status == "none") {
            listObj.style.display="none";
            tgImageObj.src="themes/images/toggle1.gif";
        }
    }
}

function toggleDiv(id) {

    var listTableObj=getObj(id)

    if (listTableObj.style.display=="block")
    {
        listTableObj.style.display="none"
    }else{
        listTableObj.style.display="block"
    }
    //set_cookie(id,listTableObj.style.display)
}

//Setting cookies
function set_cookie_gen ( name, value, exp_y, exp_m, exp_d, path, domain, secure )
{
  var cookie_string = name + "=" + escape ( value );

  if ( exp_y )
  {
    var expires = new Date ( exp_y, exp_m, exp_d );
    cookie_string += "; expires=" + expires.toGMTString();
  }

  if ( path )
        cookie_string += "; path=" + escape ( path );

  if ( domain )
        cookie_string += "; domain=" + escape ( domain );

  if ( secure )
        cookie_string += "; secure";

  document.cookie = cookie_string;
}

// Retrieving cookies
function get_cookie_gen ( cookie_name )
{
  var results = document.cookie.match ( cookie_name + '=(.*?)(;|$)' );

  if ( results )
    return ( unescape ( results[1] ) );
  else
    return null;
}

// Delete cookies
function delete_cookie_gen ( cookie_name )
{
  var cookie_date = new Date ( );  // current date & time
  cookie_date.setTime ( cookie_date.getTime() - 1 );
  document.cookie = cookie_name += "=; expires=" + cookie_date.toGMTString();
}
//end added for show/hide 10July

/** This is Javascript Function which is used to toogle between
  * assigntype user and group/team select options while assigning owner to entity.
  */
function toggleAssignType(currType)
{
        if (currType=="U")
        {
                getObj("assign_user").style.display="block"
                getObj("assign_team").style.display="none"
        }
        else
        {
                getObj("assign_user").style.display="none"
                getObj("assign_team").style.display="block"
        }
}
//to display type of address for google map
function showLocateMapMenu()
    {
            getObj("dropDownMenu").style.display="block"
            getObj("dropDownMenu").style.left=findPosX(getObj("locateMap"))
            getObj("dropDownMenu").style.top=findPosY(getObj("locateMap"))+getObj("locateMap").offsetHeight
    }


function hideLocateMapMenu(ev)
    {
            if (browser_ie)
                    currElement=window.event.srcElement
            else if (browser_nn4 || browser_nn6)
                    currElement=ev.target

            if (currElement.id!="locateMap")
                    if (getObj("dropDownMenu").style.display=="block")
                            getObj("dropDownMenu").style.display="none"
    }
/*
* javascript function to display the div tag
* @param divId :: div tag ID
*/
function show(divId)
{
    if(getObj(divId))
    {
        var id = document.getElementById(divId);

        id.style.display = 'inline';
    }
}

/*
* javascript function to display the div tag
* @param divId :: div tag ID
*/
function showBlock(divId)
{
    var id = document.getElementById(divId);
    id.style.display = 'block';
}


/*
* javascript function to hide the div tag
* @param divId :: div tag ID
*/
function hide(divId)
{

    var id = document.getElementById(divId);

    id.style.display = 'none';

}
function fnhide(divId)
{

    var id = document.getElementById(divId);

    id.style.display = 'none';
}

function fnLoadValues(obj1,obj2,SelTab,unSelTab,moduletype,module){


	var oform = document.forms['EditView'];
   oform.action.value='Save';
   //global variable to check the validation calling function to avoid validating when tab change
   gValidationCall = 'tabchange';

	/*var tabName1 = document.getElementById(obj1);
	var tabName2 = document.getElementById(obj2);
	var tagName1 = document.getElementById(SelTab);
	var tagName2 = document.getElementById(unSelTab);
	if(tabName1.className == "dvtUnSelectedCell")
		tabName1.className = "dvtSelectedCell";
	if(tabName2.className == "dvtSelectedCell")
		tabName2.className = "dvtUnSelectedCell";

	tagName1.style.display='block';
	tagName2.style.display='none';*/
	gValidationCall = 'tabchange';

  // if((moduletype == 'inventory' && validateInventory(module)) ||(moduletype == 'normal') && formValidate())
  // if(formValidate())
  // {
	   var tabName1 = document.getElementById(obj1);

	   var tabName2 = document.getElementById(obj2);

	   var tagName1 = document.getElementById(SelTab);

	   var tagName2 = document.getElementById(unSelTab);

	   if(tabName1.className == "dvtUnSelectedCell")

		   tabName1.className = "dvtSelectedCell";

	   if(tabName2.className == "dvtSelectedCell")

		   tabName2.className = "dvtUnSelectedCell";
	   tagName1.style.display='block';

	   tagName2.style.display='none';
  // }

   gValidationCall = '';
}

function fnCopy(source,design){

   document.getElementById(source).value=document.getElementById(design).value;

   document.getElementById(source).disabled=true;

}

function fnClear(source){

   document.getElementById(source).value=" ";

   document.getElementById(source).disabled=false;

}

function fnCpy(){

   var tagName=document.getElementById("cpy");

   if(tagName.checked==true){
       fnCopy("shipaddress","address");

       fnCopy("shippobox","pobox");

       fnCopy("shipcity","city");

       fnCopy("shipcode","code");

       fnCopy("shipstate","state");

       fnCopy("shipcountry","country");

   }

   else{

       fnClear("shipaddress");

       fnClear("shippobox");

       fnClear("shipcity");

       fnClear("shipcode");

       fnClear("shipstate");

       fnClear("shipcountry");

   }

}
function fnDown(obj){
        var tagName = document.getElementById(obj);
        var tabName = document.getElementById("one");
        if(tagName.style.display == 'none'){
                tagName.style.display = 'block';
                tabName.style.display = 'block';
        }
        else{
                tabName.style.display = 'none';
                tagName.style.display = 'none';
        }
}

/*
* javascript function to add field rows
* @param option_values :: List of Field names
*/
var count = 0;
var rowCnt = 1;
//crmv@16312
function fnAddSrch(){

    var tableName = document.getElementById('adSrc');

    var prev = tableName.rows.length;

    var count = prev;

    var row = tableName.insertRow(prev);

    if(count%2)

        row.className = "dvtCellLabel";

    else

        row.className = "dvtCellInfo";

    var fieldObject = document.getElementById("Fields0");
    var conditionObject = document.getElementById("Condition0");
    var searchValueObject = document.getElementById("Srch_value0");
	//crmv@18221
	var searchValueObject = document.getElementById("Srch_value0");
    var andFieldsObject = document.getElementById("andFields0");
    //crmv@18221 end
	var columnone = document.createElement('td');
	var colone = fieldObject.cloneNode(true);
	colone.setAttribute('id','Fields'+count);
	colone.setAttribute('name','Fields'+count);
	colone.setAttribute('value','');
	colone.onchange = function() {
							updatefOptions(colone, 'Condition'+count);
						}
	columnone.appendChild(colone);
	row.appendChild(columnone);

	var columntwo = document.createElement('td');
	var coltwo = conditionObject.cloneNode(true);
	coltwo.setAttribute('id','Condition'+count);
	coltwo.setAttribute('name','Condition'+count);
	coltwo.setAttribute('value','');
	columntwo.appendChild(coltwo);
	row.appendChild(columntwo);

	var columnthree = document.createElement('td');
	var colthree = searchValueObject.cloneNode(true);
	colthree.setAttribute('id','Srch_value'+count);
	colthree.setAttribute('name','Srch_value'+count);
	colthree.setAttribute('value','');
	colthree.value = '';
	columnthree.appendChild(colthree);
	row.appendChild(columnthree);
	//crmv@18221
	var columnfour = document.createElement('td');
	var colfour = andFieldsObject.cloneNode(true);
	colfour.setAttribute('id','andFields'+count);
	colfour.setAttribute('name','andFields'+count);
	colfour.setAttribute('value','');
	colfour.value = '';
	columnfour.appendChild(colfour);
	row.appendChild(columnfour);
	updatefOptions(colone, 'Condition'+count);
	updatefOptionsAll(false);
	//crmv@18221 end
}
//crmv@16312 end
function totalnoofrows()
{
    var tableName = document.getElementById('adSrc');
    jQuery('#basic_search_cnt').val(tableName.rows.length); // crmv@31245
}

/*
* javascript function to delete field rows in advance search
* @param void :: void
*/
function delRow()
{

    var tableName = document.getElementById('adSrc');

    var prev = tableName.rows.length;

    if(prev > 1)

    document.getElementById('adSrc').deleteRow(prev-1);

}

function fnVis(obj){

   var profTag = document.getElementById("prof");

   var moreTag = document.getElementById("more");

   var addrTag = document.getElementById("addr");


   if(obj == 'prof'){

       document.getElementById('mnuTab').style.display = 'block';

       document.getElementById('mnuTab1').style.display = 'none';

       document.getElementById('mnuTab2').style.display = 'none';

       profTag.className = 'dvtSelectedCell';

       moreTag.className = 'dvtUnSelectedCell';

       addrTag.className = 'dvtUnSelectedCell';

   }


   else if(obj == 'more'){

       document.getElementById('mnuTab1').style.display = 'block';

       document.getElementById('mnuTab').style.display = 'none';

       document.getElementById('mnuTab2').style.display = 'none';

       moreTag.className = 'dvtSelectedCell';

       profTag.className = 'dvtUnSelectedCell';

       addrTag.className = 'dvtUnSelectedCell';

   }


   else if(obj == 'addr'){

       document.getElementById('mnuTab2').style.display = 'block';

       document.getElementById('mnuTab').style.display = 'none';

       document.getElementById('mnuTab1').style.display = 'none';

       addrTag.className = 'dvtSelectedCell';

       profTag.className = 'dvtUnSelectedCell';

       moreTag.className = 'dvtUnSelectedCell';

   }

}

function fnvsh(obj,Lay){
    var tagName = document.getElementById(Lay);
    var leftSide = findPosX(obj);
    var topSide = findPosY(obj);
    tagName.style.left= leftSide + 175 + 'px';
    tagName.style.top= topSide + 'px';
    tagName.style.visibility = 'visible';
}

function fnvshobj(obj,Lay){
    var tagName = document.getElementById(Lay);
    var leftSide = findPosX(obj);//-30; //crmv@26807
    var topSide = findPosY(obj);//+30; //crmv@26807
    var widthM = jQuery(tagName).width(); // crmv@29686
    //crmv@30356
    if(isMobile()) {
    	topSide = topSide + 65;
    	leftSide = 20;
	}
    //crmv@30356e
    if(Lay == 'editdiv')
    {
    //crmv@ds47
    //window should open more left
        leftSide = leftSide - 625;
        topSide = topSide - 125;
    //crmv@ds47 end
    }else if(Lay == 'transferdiv')
    {
        leftSide = leftSide - 10;
            topSide = topSide;
    }
    var IE = document.all?true:false;
    if(IE)
   {
    if($("repposition1"))
    {
    if(topSide > 1200)
    {
        topSide = topSide-250;
    }
    }
   }

    var getVal = eval(leftSide) + eval(widthM);
    if(getVal  > document.body.clientWidth ){
        leftSide = eval(leftSide) - eval(widthM);
        tagName.style.left = leftSide + 34 + 'px';
    }
    else
        tagName.style.left= leftSide + 'px';
    tagName.style.top= topSide + 'px';
    tagName.style.display = 'block';
    tagName.style.visibility = "visible";
    tagName.style.zIndex = findZMax()+1;	//crmv@26986
}

//crmv@ds2  add new function for INFO/DESCRIPTION POPUP
function fnvshobj2(obj,Lay){
    var tagName = document.getElementById(Lay);
    var leftSide = findPosX(obj);
    var topSide = findPosY(obj);

    leftSide = leftSide * 1 + 25;
    topSide = topSide *1 - 90;
    var maxW = tagName.style.width;
    var widthM = maxW.substring(0,maxW.length-2);

    tagName.style.left= leftSide + 'px';
    tagName.style.top= topSide + 'px';
    tagName.style.display = 'block';
    tagName.style.visibility = "visible";
}
//crmv@ds2 end

function posLay(obj,Lay){
    var tagName = document.getElementById(Lay);
    var leftSide = findPosX(obj);
    var topSide = findPosY(obj);
    var maxW = tagName.style.width;
    var widthM = maxW.substring(0,maxW.length-2);
    var getVal = eval(leftSide) + eval(widthM);
    if(getVal  > document.body.clientWidth ){
        leftSide = eval(leftSide) - eval(widthM);
        tagName.style.left = leftSide + 'px';
    }
    else
        tagName.style.left= leftSide + 'px';
    tagName.style.top= topSide + 'px';
}

function fninvsh(Lay){
    var tagName = document.getElementById(Lay);
    tagName.style.visibility = 'hidden';
    tagName.style.display = 'none';
}

function fnvshNrm(Lay){
    var tagName = document.getElementById(Lay);
    tagName.style.visibility = 'visible';
    tagName.style.display = 'block';
}

function cancelForm(frm)
{
        window.history.back();
}

function trim(str)
{
	if (str != undefined) {
	    var s = str.replace(/\s+$/,'');
	    s = s.replace(/^\s+/,'');
	    return s;
	}
}

function clear_form(form)
{
    for (j = 0; j < form.elements.length; j++)
    {
        if (form.elements[j].type == 'text' || form.elements[j].type == 'select-one')
        {
            form.elements[j].value = '';
        }
    }
}

function ActivateCheckBox()
{
        var map = document.getElementById("saved_map_checkbox");
        var source = document.getElementById("saved_source");

        if(map.checked == true)
        {
                source.disabled = false;
        }
        else
        {
                source.disabled = true;
        }
}

//wipe for Convert Lead

function fnSlide2(obj,inner)
{
  var buff = document.getElementById(obj).height;
  closeLimit = buff.substring(0,buff.length);
  menu_max = eval(closeLimit);
  var tagName = document.getElementById(inner);
  document.getElementById(obj).style.height=0 + "px"; menu_i=0;
  if (tagName.style.display == 'none')
          fnexpanLay2(obj,inner);
  else
        fncloseLay2(obj,inner);
 }

function fnexpanLay2(obj,inner)
{
    // document.getElementById(obj).style.display = 'run-in';
   var setText = eval(closeLimit) - 1;
   if (menu_i<=eval(closeLimit))
   {
            if (menu_i>setText){document.getElementById(inner).style.display='block';}
       document.getElementById(obj).style.height=menu_i + "px";
           setTimeout(function() { fnexpanLay2(obj,inner); },5);
        menu_i=menu_i+5;
   }
}

 function fncloseLay2(obj,inner)
{
  if (menu_max >= eval(openLimit))
   {
            if (menu_max<eval(closeLimit)){document.getElementById(inner).style.display='none';}
       document.getElementById(obj).style.height=menu_max +"px";
          setTimeout(function() { fncloseLay2(obj,inner); }, 5);
       menu_max = menu_max -5;
   }
}

function addOnloadEvent(fnc){
  if ( typeof window.addEventListener != "undefined" )
    window.addEventListener( "load", fnc, false );
  else if ( typeof window.attachEvent != "undefined" ) {
    window.attachEvent( "onload", fnc );
  }
  else {
    if ( window.onload != null ) {
      var oldOnload = window.onload;
      window.onload = function ( e ) {
        oldOnload( e );
        window[fnc]();
      };
    }
    else
      window.onload = fnc;
  }
}
function InternalMailer(record_id,field_id,field_name,par_module,type) {
	var url;
	switch(type) {
		case 'record_id':
		        url = 'index.php?module=Emails&action=EmailsAjax&internal_mailer=true&type='+type+'&field_id='+field_id+'&rec_id='+record_id+'&fieldname='+field_name+'&file=EditView&par_module='+par_module;//query string field_id added for listview-compose email issue
		break;
		case 'email_addy':
		        url = 'index.php?module=Emails&action=EmailsAjax&internal_mailer=true&type='+type+'&email_addy='+record_id+'&file=EditView';
		break;
	}
	//crmv@31197
	//var opts = "menubar=no,toolbar=no,location=no,status=no,resizable=yes,scrollbars=yes";
	//openPopUp('xComposeEmail',this,url,'createemailWin',830,662,opts);
	window.open(url,'_blank');
	//crmv@31197e
}

function fnHide_Event(obj){
	document.getElementById(obj).style.visibility = 'hidden';
}
function OpenCompose(id,mode,openpopup,path)	//crmv@25472	//crmv@31197
{
    switch(mode)
    {
        case 'edit':
            url = 'index.php?module=Emails&action=EmailsAjax&file=EditView&record='+id+'&draft_id='+id;	//crmv@31197
            break;
        case 'create':
            url = 'index.php?module=Emails&action=EmailsAjax&file=EditView';
            break;
        case 'forward':
            url = 'index.php?module=Emails&action=EmailsAjax&file=EditView&record='+id+'&forward=true';
            break;
		case 'Invoice':
            url = 'index.php?module=Emails&action=EmailsAjax&file=EditView&attachment='+mode+'_'+id+'.pdf';
			break;
		case 'PurchaseOrder':
            url = 'index.php?module=Emails&action=EmailsAjax&file=EditView&attachment='+mode+'_'+id+'.pdf';
			break;
		case 'SalesOrder':
            url = 'index.php?module=Emails&action=EmailsAjax&file=EditView&attachment='+mode+'_'+id+'.pdf';
			break;
		case 'Quote':
            url = 'index.php?module=Emails&action=EmailsAjax&file=EditView&attachment='+mode+'_'+id+'.pdf';
			break;
		case 'Documents':
            url = 'index.php?module=Emails&action=EmailsAjax&file=EditView&attachment='+id+'&rec='+document.DetailView.record.value;	//crmv@31691
			break;
		case 'print':
			url = 'index.php?module=Emails&action=EmailsAjax&file=PrintEmail&record='+id+'&print=true';
    }
    //crmv@31197
    if (path != undefined && path != '') {
    	url = path+url;
    }
    //crmv@31197e
    //crmv@25472
    if (openpopup == 'no') {
    	window.location = url+'&cancel_button=history';	//crmv@26512
   	} else {
   		//crmv@31197
    	//openPopUp('xComposeEmail',this,url,'createemailWin',820,689,'menubar=no,toolbar=no,location=no,status=no,resizable=no,scrollbars=yes');
    	window.open(url,'_blank');
    	//crmv@31197e
   	}
   	//crmv@25472e
}
//Function added for Mass select in Popup - Philip
//crmv@selectall fix
function SelectAll(mod,parmod)
{
	//crmv@26807
	//crmv@26961
	var strHtml = '';
	var start = 0;
	var end = 0;
	//crmv@30408
	if ((typeof(top.wdCalendar) == 'undefined') && top.getObj('mode').value == 'edit' && top.getObj('record').value != '' && (mod == 'Contacts' || mod == 'Users') && top.jQuery('div#addEventInviteUI').is(":visible")) { 
		jQuery('[name="selectall"]').find('input:checked').parent().parent().each(function() {
			checkId = jQuery(this).find('input:checked').attr('id');
			if (checkId != 'selectall' && top.jQuery('div#addEventInviteUI').contents().find('#' + checkId + '_' + mod + '_dest').length < 1) {
				strHtml = jQuery(this).html();
				start = strHtml.indexOf('set_return');
				end = strHtml.indexOf(')',start);
				eval(strHtml.substring(start,end+1));
			}
		});
		closePopup();
	}
	//crmv@30408e
	else if ((typeof(top.wdCalendar) == 'undefined') && (mod == 'Contacts' || mod == 'Users') && top.jQuery('div#addEventInviteUI').is(":visible")) { 
		jQuery('[name="selectall"]').find('input:checked').parent().parent().each(function() {
			checkId = jQuery(this).find('input:checked').attr('id');
			if (checkId != 'selectall' && top.jQuery('div#addEventInviteUI').contents().find('#' + checkId + '_' + mod + '_dest').length < 1) {
				strHtml = jQuery(this).html();
				start = strHtml.indexOf('linkInviteesTableEditView');
				end = strHtml.indexOf(')',start+1);
				eval('top.' + strHtml.substring(start,end+1));
			}
		});
		closePopup();
	}
	//crmv@30408
	else if ((typeof(top.wdCalendar) != 'undefined') && (mod == 'Contacts' || mod == 'Users') && top.jQuery('div#addEventInviteUI').css('display') == 'block' && top.wdCalendar.jQuery('#bbit-cal-buddle').css('visibility') != 'visible') {
		jQuery('[name="selectall"]').find('input:checked').parent().parent().each(function() {
			checkId = jQuery(this).find('input:checked').attr('id');
			if (checkId != 'selectall' && top.jQuery('div#addEventInviteUI').contents().find('#' + checkId + '_' + mod + '_dest').length < 1) {
				strHtml = jQuery(this).html();				
				start = strHtml.indexOf('linkContactsTable');
				end = strHtml.indexOf(')',start+1);
				eval('top.wdCalendar.' + strHtml.substring(start,end+1));
			}
		});
		closePopup();
	}
	//crmv@30408e
	else if ((typeof(top.wdCalendar) != 'undefined') && (mod == 'Contacts' || mod == 'Users') && top.wdCalendar.jQuery('#bbit-cal-buddle').css('visibility') == 'visible') {
		jQuery('[name="selectall"]').find('input:checked').parent().parent().each(function() {
			checkId = jQuery(this).find('input:checked').attr('id');
			if (checkId != 'selectall' && top.wdCalendar.jQuery('#bbit-cal-buddle').contents().find('#' + checkId + '_' + mod + '_dest').length < 1) {
				strHtml = jQuery(this).html();
				start = strHtml.indexOf('linkContactsTable');
				end = strHtml.indexOf(')',start+1);
				eval('top.wdCalendar.' + strHtml.substring(start,end+1));
			}
		});
		closePopup();
	}	//crmv@26961e
	else {
		//crmv@26921
		var idstring = get_real_selected_ids(mod);
		if (idstring.substr('0','1')==";")
			idstring = idstring.substr('1');
		var idarr = idstring.split(';');
		var count = idarr.length;
		var count = xx = count-1;
		if (idstring == "" || idstring == ";" || idstring == 'null')
		{
			alert(alert_arr.SELECT);
			return false;
		} else {
		//crmv@26921e
	//crmv@26807e
			//crmv@17001
		    if (parmod == 'Calendar' && mod == 'Contacts') {
		    	var namestr = '';
		    	for (var i=0;i<count;i++){
		    		if (trim(idarr[i]) != ''){
	                    if (isdefined('calendarCont'+idarr[i])){
	                    	var str=document.getElementById('calendarCont'+idarr[i]).innerHTML+"\n";
	                    }
	                    else {
	                    	var str=idarr[i]+"\n";
	                    }
		    			namestr +=str;
		    		}
		    	}
		    }
		    else if (parmod != 'Emails') //crmv@22366
		    {
		    	//crmv@21048m
	            var module = parent.document.getElementById('RLreturn_module').value
	            var entity_id = parent.document.getElementById('RLparent_id').value
	            var parenttab = parent.document.getElementById('parenttab').value
	            //crmv@21048m e
		    }
			//crmv@17001e
		    if(confirm(alert_arr.ADD_CONFIRMATION+xx+alert_arr.RECORDS)){
		        if (parmod == 'Calendar' && mod == 'Contacts')	//crmv@17001
	            {
			        //this blcok has been modified to provide delete option for contact in Calendar
			        idval = parent.document.EditView.contactidlist.value;//crmv@21048m
			        if(idval != '')
			        {
			            var avalIds = new Array();
			            avalIds = idstring.split(';');

			            var selectedIds = new Array();
			            selectedIds = idval.split(';');

			            for(i=0; i < (avalIds.length-1); i++)
			            {
			            	if (trim(avalIds[i]) == '') continue;
			                var rowFound=false;
			                for(k=0; k < selectedIds.length; k++)
			                {
			                    if (selectedIds[k]==avalIds[i])
			                    {
			                        rowFound=true;
			                        break;
			                    }

			                }
			                if(rowFound != true)
			                {
			                    idval = idval+';'+avalIds[i];
			                    parent.document.EditView.contactidlist.value = idval;//crmv@21048m
			                    if (isdefined('calendarCont'+avalIds[i])){
			                    	var str=document.getElementById('calendarCont'+avalIds[i]).innerHTML;
			                    }
			                    else {
			                    	var str=avalIds[i];
			                    }
			                    parent.addOption(avalIds[i],str);//crmv@21048m
			                }
			            }
			        }
			        else
			        {
			        	parent.document.EditView.contactidlist.value = idstring;//crmv@21048m
			        	var temp = new Array();
			        	temp = namestr.split('\n');
			        	var tempids = new Array();
			        	tempids = idstring.split(';');

			        	for(k=0; k < temp.length; k++)
			        	{
			        		if (trim(tempids[k]) == '') continue;
			        		parent.addOption(tempids[k],temp[k]);//crmv@21048m
			        	}
			        }
			        //end
	            }
		        //crmv@22366
			    else if (parmod == 'Emails' && (mod == 'Contacts' || mod == 'Accounts' ||  mod == 'Vendors' ||  mod == 'Users' || mod == 'Leads')) {
			    	var url = 'module=Emails&action=EmailsAjax&file=MultiAddressEmail&mod='+mod+'&ids='+idarr;
			    	var response = getFile('index.php?'+url);
			    	eval(response);
			    }
			    //crmv@22366e
		        //crmv@21048m
		        else
		        {
		        	parent.location.href="index.php?module="+module+"&parentid="+entity_id+"&action=updateRelations&destination_module="+mod+"&idlist="+idstring+"&parenttab="+parenttab;
		        }
		        //crmv@21048m e
		        closePopup();//crmv@22366
		    }
		} //crmv@26921
	}
}
//crmv@selectall fix end
function ShowEmail(id)
{
	url = 'index.php?module=Emails&action=EmailsAjax&file=DetailView&record='+id;
	//crmv@31197
	//openPopUp('xComposeEmail',this,url,'createemailWin',820,695,'menubar=no,toolbar=no,location=no,status=no,resizable=no,scrollbars=yes');
	window.open(url,'_blank');
	//crmv@31197e
}

var bSaf = (navigator.userAgent.indexOf('Safari') != -1);
var bOpera = (navigator.userAgent.indexOf('Opera') != -1);
var bMoz = (navigator.appName == 'Netscape');
function execJS(node) {
    var st = node.getElementsByTagName('SCRIPT');
    var strExec;
    for(var i=0;i<st.length; i++) {
      if (bSaf) {
        strExec = st[i].innerHTML;
      }
      else if (bOpera) {
        strExec = st[i].text;
      }
      else if (bMoz) {
        strExec = st[i].textContent;
      }
      else {
        strExec = st[i].text;
      }
      try {
        eval(strExec);
      } catch(e) {
        alert(e);
      }
    }
}

//Function added for getting the Tab Selected Values (Standard/Advanced Filters) for Custom View - Ahmed
//crmv@31775
function fnLoadCvValues(obj1,obj2,obj3,SelTab,unSelTab,unSelTab2){

	var tabName1 = document.getElementById(obj1);
	var tagName1 = document.getElementById(SelTab);
	if(tabName1.className == "dvtUnSelectedCell") {
		tabName1.className = "dvtSelectedCell";
	}
	tagName1.style.display='block';

	if (obj2 != '') {
		var tabName2 = document.getElementById(obj2);
		var tagName2 = document.getElementById(unSelTab);
		if(tabName2.className == "dvtSelectedCell") {
			tabName2.className = "dvtUnSelectedCell";
		}
		tagName2.style.display='none';
	}
	
	if (obj3 != '' && isdefined(obj3) && isdefined(unSelTab2)) { //crmv@33978
		var tabName3 = document.getElementById(obj3);
		var tagName3 = document.getElementById(unSelTab2);
		if(tabName3.className == "dvtSelectedCell") {
			tabName3.className = "dvtUnSelectedCell";
		}
		tagName3.style.display='none';
	}
}
//crmv@31775e

// Drop Dwon Menu
function fnDropDown(obj,Lay,offsetTop){//crmv@22259
    var tagName = document.getElementById(Lay);
    var leftSide = findPosX(obj);
    var topSide = findPosY(obj);
    var maxW = tagName.style.width;
    var widthM = maxW.substring(0,maxW.length-2);
    var getVal = eval(leftSide) + eval(widthM);
	var browser = navigator.userAgent.toLowerCase();
	//crmv@22952
    if(getVal > document.body.clientWidth){
    	var diff = getVal - document.body.clientWidth;
        tagName.style.left = leftSide - diff + 'px';
    } else {
		tagName.style.left= leftSide + 'px';
	}
	//crmv@22259
	if (typeof offsetTop == 'undefined') {
		var offsetTop = 0;
	}
	//crmv@22259e
	//crmv@22622
	topSide = topSide + 34 + offsetTop;
	tagName.style.top = topSide + 'px'; //crmv@20253 //crmv@22259 //crmv@18592
	//crmv@22622e
	tagName.style.display = 'block';
	//crmv@22952e
}


function fnShowDrop(obj){
    document.getElementById(obj).style.display = 'block';
}

function fnHideDrop(obj){
    document.getElementById(obj).style.display = 'none';
}

function getCalendarPopup(imageid,fieldid,dateformat)
{
        Calendar.setup ({
                inputField : fieldid, ifFormat : dateformat, showsTime : false, button : imageid, singleClick : true, step : 1
        });
}


/**to get SelectContacts Popup
check->to check select options enable or disable
*type->to differentiate from task
*frmName->form name*/

function selectContact(check,type,frmName,autocomplete)	//crmv@29190
{
	//crmv@21048m	//crmv@29190
	var record = document.getElementsByName("record")[0].value;
	if($("single_accountid"))
	{
		var potential_id = '';
		if($("potential_id"))
			potential_id = frmName.potential_id.value;
		account_id = frmName.account_id.value;
		if(potential_id != '')
		{
			record_id = potential_id;
			module_string = "&parent_module=Potentials";
		}
		else
		{
			record_id = account_id;
			module_string = "&parent_module=Accounts";
		}
		if(record_id == '' || autocomplete == 'yes')	//crmv@29190
			return "module=Contacts&action=Popup&html=Popup_picker&popuptype=specific&form=EditView";
		else
			return "module=Contacts&action=Popup&html=Popup_picker&popuptype=specific&form=EditView"+module_string+"&relmod_id="+record_id;
	}
	else if(($("parentid")) && type != 'task')
	{
		if(getObj("parent_type")){
			rel_parent_module = frmName.parent_type.value;
			record_id = frmName.parent_id.value;
			module = rel_parent_module.split("&");
			if(record_id != '' && module[0] == "Leads")
			{
				alert(alert_arr.CANT_SELECT_CONTACTS);
			}
			else
			{
				if(check == 'true')
					search_string = "&return_module=Calendar&select=enable&popuptype=detailview&form_submit=false";
				else
					search_string="&popuptype=specific";
				if(record_id == '' || autocomplete == 'yes')	//crmv@29190
					return "module=Contacts&action=Popup&html=Popup_picker&form=EditView"+search_string;
				else
					return "module=Contacts&action=Popup&html=Popup_picker&form=EditView"+search_string+"&relmod_id="+record_id+"&parent_module="+module[0];
			}
		}else{
			return "module=Contacts&action=Popup&html=Popup_picker&return_module=Calendar&select=enable&popuptype=detailview&form=EditView&form_submit=false";
		}
	}
	else if(($("contact_name")) && type == 'task')
	{
		var formName = frmName.name;
		var task_recordid = '';
		if(formName == 'EditView')
		{
			if($("parent_type"))
			{
				task_parent_module = frmName.parent_type.value;
				task_recordid = frmName.parent_id.value;
				task_module = task_parent_module.split("&");
				popuptype="&popuptype=specific";
			}
		}
		else
		{
			if($("task_parent_type"))
			{
				task_parent_module = frmName.task_parent_type.value;
				task_recordid = frmName.task_parent_id.value;
				task_module = task_parent_module.split("&");
				popuptype="&popuptype=toDospecific";
			}
		}
		if(task_recordid != '' && task_module[0] == "Leads" )
		{
			//crmv@31556
			var formName = frmName.name;
   			return "module=Contacts&action=Popup&html=Popup_picker&popuptype=specific&form="+formName;
   			//crmv@31556e
		}
		else
		{
			//crmv@23220
			if(task_recordid == '' || autocomplete == 'yes')	//crmv@29190
				return "module=Contacts&action=Popup&html=Popup_picker&popuptype=specific&form="+formName;
			else
				return "module=Contacts&action=Popup&html=Popup_picker"+popuptype+"&form="+formName+"&task_relmod_id="+task_recordid+"&task_parent_module="+task_module[0];
		}
	}
	else
	{
		var formName = frmName.name;
		return "module=Contacts&action=Popup&html=Popup_picker&popuptype=specific&form="+formName;
	}
	//crmv@ds28 workflow
	if ($("get_users_list")){
		var formName = frmName.name;
		return "module=Users&action=Popup&html=Popup_picker&popuptype=specific&form="+formName;
	}
	//crmv@ds28 end
	//crmv@23220 end
	//crmv@21048me	//crmv@29190e
}
//to get Select Potential Popup
function selectPotential()
{
	// To support both B2B and B2C model
	var record_id = '';
	var parent_module = '';
	var acc_element = document.EditView.account_id;
	var cnt_element = document.EditView.contact_id;
	if (acc_element != null) {
		record_id= acc_element.value;
		parent_module = 'Accounts';
	} else if (cnt_element != null) {
		record_id= cnt_element.value;
		parent_module = 'Contacts';
	}
	//crmv@21048m	//crmv@29190
	if(record_id != '')
		var options = "&relmod_id="+record_id+"&parent_module="+parent_module;
	else
		var options = '';
	return "module=Potentials&action=Popup&html=Popup_picker&popuptype=specific_potential_account_address&form=EditView"+options;
	//crmv@21048me	//crmv@29190e
}
//to select Quote Popup
function selectQuote()
{
	// To support both B2B and B2C model
	var record_id = '';
	var parent_module = '';
	var acc_element = document.EditView.account_id;
	var cnt_element = document.EditView.contact_id;
	if (acc_element != null) {
		record_id= acc_element.value;
		parent_module = 'Accounts';
	} else if (cnt_element != null) {
		record_id= cnt_element.value;
		parent_module = 'Contacts';
	}
	//crmv@21048m	//crmv@29190
	if(record_id != '')
		var options = "&relmod_id="+record_id+"&parent_module="+parent_module;
	else
		var options = '';
	return "module=Quotes&action=Popup&html=Popup_picker&popuptype=specific&form=EditView"+options;
	//crmv@21048me	//crmv@29190e
}
//to get select SalesOrder Popup
function selectSalesOrder()
{
	// To support both B2B and B2C model
	var record_id = '';
	var parent_module = '';
	var acc_element = document.EditView.account_id;
	var cnt_element = document.EditView.contact_id;
	if (acc_element != null) {
		record_id= acc_element.value;
		parent_module = 'Accounts';
	} else if (cnt_element != null) {
		record_id= cnt_element.value;
		parent_module = 'Contacts';
	}
	//crmv@21048m	//crmv@29190
	if(record_id != '')
		var options = "&relmod_id="+record_id+"&parent_module="+parent_module;
	else
		var options = '';
	return "module=SalesOrder&action=Popup&html=Popup_picker&popuptype=specific&form=EditView"+options;
	//crmv@21048me	//crmv@29190e
}

function checkEmailid(parent_module,emailid,yahooid)
 {
       var check = true;
       if(emailid == '' && yahooid == '')
       {
               alert(alert_arr.LBL_THIS+parent_module+alert_arr.DOESNOT_HAVE_MAILIDS);
               check=false;
       }
       return check;
 }

function calQCduedatetime()
{
        var datefmt = document.QcEditView.dateFormat.value;
        var type = document.QcEditView.activitytype.value;
        var dateval1=getObj('date_start').value.replace(/^\s+/g, '').replace(/\s+$/g, '');
        var dateelements1=splitDateVal(dateval1);
        dd1=parseInt(dateelements1[0],10);
        mm1=dateelements1[1];
        yyyy1=dateelements1[2];
        var date1=new Date();
        date1.setYear(yyyy1);
        date1.setMonth(mm1-1,dd1+1);
        var yy = date1.getFullYear();
        var mm = date1.getMonth() + 1;
        var dd = date1.getDate();
        var date = document.QcEditView.date_start.value;
        var starttime = document.QcEditView.time_start.value;
        if (!timeValidate('time_start',' Start Date & Time','OTH'))
                return false;
        var timearr = starttime.split(":");
        var hour = parseInt(timearr[0],10);
        var min = parseInt(timearr[1],10);
        dd = _2digit(dd);
        mm = _2digit(mm);
        var tempdate = yy+'-'+mm+'-'+dd;
        if(datefmt == '%d-%m-%Y')
                var tempdate = dd+'-'+mm+'-'+yy;
        else if(datefmt == '%m-%d-%Y')
                var tempdate = mm+'-'+dd+'-'+yy;
        if(type == 'Meeting')
        {
                hour = hour + 1;
                if(hour == 24)
                {
                        hour = 0;
                        date =  tempdate;
                }
                hour = _2digit(hour);
        min = _2digit(min);
                document.QcEditView.due_date.value = date;
                document.QcEditView.time_end.value = hour+':'+min;
        }
        if(type == 'Call')
        {
                if(min >= 55)
                {
                        min = min%55;
                        hour = hour + 1;
                }else min = min + 5;
                if(hour == 24)
                {
                        hour = 0;
                        date =  tempdate;
                }
                hour = _2digit(hour);
        min = _2digit(min);
                document.QcEditView.due_date.value = date;
                document.QcEditView.time_end.value = hour+':'+min;
        }

}

function _2digit( no ){
        if(no < 10) return "0" + no;
        else return "" + no;
}

function confirmdelete(url,module)
{
//crmv@15157
if(confirm(alert_arr.ARE_YOU_SURE)){
	document.location.href=url;
}
//crmv@15157 end
}

//function modified to apply the patch ref : Ticket #4065
function valid(c,type)
{
    if(type == 'name')
    {
        return (((c >= 'a') && (c <= 'z')) ||((c >= 'A') && (c <= 'Z')) ||((c >= '0') && (c <= '9')) || (c == '.') || (c == '_') || (c == '-') || (c == '@') );
    }
    else if(type == 'namespace')
    {
        return (((c >= 'a') && (c <= 'z')) ||((c >= 'A') && (c <= 'Z')) ||((c >= '0') && (c <= '9')) || (c == '.')||(c==' ') || (c == '_') || (c == '-') );
    }
}
//end

function CharValidation(s,type)
{
    for (var i = 0; i < s.length; i++)
    {
        if (!valid(s.charAt(i),type))
        {
            return false;
        }
    }
    return true;
}


/** Check Upload file is in specified format(extension).
  * @param fldname -- name of the file field
  * @param fldLabel -- Lable of the file field
  * @param filter -- List of file extensions to allow. each extension must be seperated with a | sybmol.
  * Example: upload_filter("imagename","Image", "jpg|gif|bmp|png")
  * @returns true -- if the extension is IN  specified extension.
  * @returns false -- if the extension is NOT IN specified extension.
  *
  * NOTE: If this field is mandatory,  please call emptyCheck() function before calling this function.
 */

function upload_filter(fldName, filter)
{
	var currObj=getObj(fldName)
	if(currObj.value !="")
	{
		var file=currObj.value;
		var type=file.split(".");
		var valid_extn=filter.split("|");

		if(valid_extn.indexOf(type[type.length-1]) == -1)
		{
			alert(alert_arr.PLS_SELECT_VALID_FILE+valid_extn)
			try {
				currObj.focus()
			} catch(error) {
				// Fix for IE: If element or its wrapper around it is hidden, setting focus will fail
				// So using the try { } catch(error) { }
			}
		 	return false;
		}
	}
	return true

}

function validateUrl(name)
{
    var Url = getObj(name);
    var wProtocol;

    var oRegex = new Object();
    oRegex.UriProtocol = new RegExp('');
    oRegex.UriProtocol.compile( '^(((http|https|ftp|news):\/\/)|mailto:)', 'gi' );
    oRegex.UrlOnChangeProtocol = new RegExp('') ;
    oRegex.UrlOnChangeProtocol.compile( '^(http|https|ftp|news)://(?=.)', 'gi' );

    wUrl = Url.value;
    wProtocol=oRegex.UrlOnChangeProtocol.exec( wUrl ) ;
    if ( wProtocol )
    {
        wUrl = wUrl.substr( wProtocol[0].length );
        Url.value = wUrl;
    }
}

function LTrim( value )
{

        var re = /\s*((\S+\s*)*)/;
        return value.replace(re, "$1");

}

function selectedRecords(module,category)
{
    var idstring = get_real_selected_ids(module);
    if(idstring != '')
            window.location.href="index.php?module="+module+"&action=ExportRecords&parenttab="+category+"&idstring="+idstring;
    else
            window.location.href="index.php?module="+module+"&action=ExportRecords&parenttab="+category;
    return false;
}

function record_export(module,category,exform,idstring)
{
    var searchType = document.getElementsByName('search_type');
    var exportData = document.getElementsByName('export_data');
    for(i=0;i<2;i++){
        if(searchType[i].checked == true)
            var sel_type = searchType[i].value;
    }
    for(i=0;i<3;i++){
        if(exportData[i].checked == true)
            var exp_type = exportData[i].value;
    }
        new Ajax.Request(
                'index.php',
                {queue: {position: 'end', scope: 'command'},
                        method: 'post',
                        postBody: "module="+module+"&action=ExportAjax&export_record=true&search_type="+sel_type+"&export_data="+exp_type+"&idstring="+idstring,
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
                            else
                {
                                       exform.submit();
                }
                        }
                }
        );

}


function hideErrorMsg1()
{
        $('not_search').style.display = 'none';
}

// Replace the % sign with %25 to make sure the AJAX url is going wel.
function escapeAll(tagValue)
{
        //return escape(tagValue.replace(/%/g, '%25'));
    if(default_charset.toLowerCase() == 'utf-8')
            return encodeURIComponent(tagValue.replace(/%/g, '%25'));
    else
        return escape(tagValue.replace(/%/g, '%25'));
}

function removeHTMLFormatting(str) {
        str = str.replace(/<([^<>]*)>/g, " ");
        str = str.replace(/&nbsp;/g, " ");
        return str;
}
function get_converted_html(str)
{
        var temp = str.toLowerCase();
        if(temp.indexOf('<') != '-1' || temp.indexOf('>') != '-1')
        {
                str = str.replace(/</g,'&lt;');
                str = str.replace(/>/g,'&gt;');
        }
    if( temp.match(/(script).*(\/script)/))
        {
                str = str.replace(/&/g,'&amp;');
        }
        else if(temp.indexOf('&') != '-1')
        {
                str = str.replace(/&/g,'&amp;');
    }
    return str;
}
//To select the select all check box(if all the items are selected) when the form loads.
function default_togglestate(obj_id,elementId)
{
	var all_state=true;
	var groupElements = document.getElementsByName(obj_id);
	for (var i=0;i<groupElements.length;i++) {
		var state=groupElements[i].checked;
		if (state == false)
		{
			all_state=false;
			break;
		}
	}
	if(typeof elementId=='undefined'){
		elementId = 'selectall';
	}
	if(getObj(elementId)) {
		getObj(elementId).checked=all_state;
	}
}

//for select  multiple check box in multiple pages for Campaigns related list:

function rel_check_object(sel_id,module)
{
        var selected;
        var select_global=new Array();
        var cookie_val=get_cookie(module+"_all");
        if(cookie_val == null)
                selected=sel_id.value+";";
        else
                selected=trim(cookie_val);
        select_global=selected.split(";");
        var box_value=sel_id.checked;
        var id= sel_id.value;
        var duplicate=select_global.indexOf(id);
        var size=select_global.length-1;
        var result="";
        //crmv@ds47
        if(box_value == true)
        {
                if(duplicate == "-1")
                {
                        select_global[size]=id;
                }

                size=select_global.length-1;
                var i=0;
                for(i=0;i<=size;i++)
                {
                        if(trim(select_global[i])!='')	//crmv@19139
                                result=select_global[i]+";"+result;
                }
                rel_default_togglestate(module);

        }
        else
        {
                if(duplicate != "-1")

            select_global.splice(duplicate,1)

                size=select_global.length-1;
                var i=0;
                for(i=size;i>=0;i--)
                {
                        if(trim(select_global[i])!='')	//crmv@19139
                                result=select_global[i]+";"+result;
                }
                        getObj(module+"_selectall").checked=false;

        }
        //crmv@ds47 end
        set_cookie(module+"_all",result);
}

//Function to select all the items in the current page for Campaigns related list:.
function rel_toggleSelect(state,relCheckName,module) {
        if (getObj(relCheckName)) {
                if (typeof(getObj(relCheckName).length)=="undefined") {
                        getObj(relCheckName).checked=state
                } else
                {
                        for (var i=0;i<getObj(relCheckName).length;i++)
                        {
                                getObj(relCheckName)[i].checked=state
                                        rel_check_object(getObj(relCheckName)[i],module)
                        }
                }
        }
}
//To select the select all check box(if all the items are selected) when the form loads for Campaigns related list:.
function rel_default_togglestate(module)
{
	var all_state=true;
	var groupElements = document.getElementsByName(module+"_selected_id");
	if(typeof(groupElements) == 'undefined') return;

	for (var i=0;i<groupElements.length;i++) {
		var state=groupElements[i].checked;
		if (state == false)
		{
			all_state=false;
			break;
		}
	}
	if(getObj(module+"_selectall")) {
		getObj(module+"_selectall").checked=all_state;
	}
}
//To clear all the checked items in all the pages for Campaigns related list:
function clear_checked_all(module)
{
	var cookie_val=get_cookie(module+"_all");
	if(cookie_val != null)
		delete_cookie(module+"_all");
	//Uncheck all the boxes in current page..
	var obj = document.getElementsByName(module+"_selected_id");
	if (obj) {
		for (var i=0;i<obj.length;i++) {
			obj[i].checked=false;
		}
	}
	if(getObj(module+"_selectall")) {
		getObj(module+"_selectall").checked=false;
	}
}
//groupParentElementId is added as there are multiple groups in Documents listview.
function toggleSelect_ListView(state,relCheckName,groupParentElementId) {
    var obj = document.getElementsByName(relCheckName);
	if (obj) {
        for (var i=0;i<obj.length;i++) {
          	obj[i].checked=state;
			if(typeof(check_object) == 'function') {
				// This function is defined in ListView.js (check for existence)
				check_object(obj[i],groupParentElementId);
			}
        }
    }
}
//crmv@fix listview
function toggleSelect_ListView2(state,relCheckName)
{

   	if (getObj(relCheckName))
    {
    		if (typeof(getObj(relCheckName).length)=="undefined")
        {
    			getObj(relCheckName).checked=state

    			updateIdlist(state,getObj(relCheckName).value)

    		} else {
      			for (var i=0;i<getObj(relCheckName).length;i++)
      			{
      			  obj_check = getObj(relCheckName)[i];

              obj_check.checked = state;

      				updateIdlist(state,obj_check.value)
            }
    		}
  	}
}

function updateIdlist(obj_checked,obj_value)
{
    idstring = document.getElementById('idlist').value;
    if (idstring == "") idstring = ";";

    if (obj_checked == true)
    {
       idstring = idstring.replace(";" + obj_value + ";", ";");
       document.getElementById('idlist').value = idstring + obj_value + ";";
    }
    else
    {
       newidstring = idstring.replace(";" + obj_value + ";", ";");
       document.getElementById('idlist').value = newidstring;
    }
}
//crmv@fix listview end
function gotourl(url)
{
                document.location.href=url;
}

//crmv@ds2  add new funciton for INFO/DESCRIPTION POPUP
function showInfoWindow(thiss,entity_id,title)
{
    document.getElementById('wlastcontactLV_title').innerHTML = "<b>" + title + "</b>";
    document.getElementById('wlastcontactLV_content').innerHTML = "<img src='Image/ajax-loader.gif'>";

    var url = "module=Accounts&action=AccountsAjax&file=Save&lc_check=true&last_contact="+entity_id;
    new Ajax.Request(
    'index.php',
      {queue: {position: 'end', scope: 'command'},
              method: 'post',
              postBody:url,
              onComplete: function(response) {
                      var str = response.responseText
                      document.getElementById('wlastcontactLV_content').innerHTML = str;

              }
      }
    );

    fnvshobj2(thiss,'wlastcontactLV');

}

function hideInfoWindow()
{
    document.getElementById('wlastcontactLV_title').innerHTML = "";
    document.getElementById('wlastcontactLV_content').innerHTML = "";
    fninvsh('wlastcontactLV');
}
//crmv@ds2end

//crmv@7231	//crmv@19653
function AjaxDuplicateValidateEXT_CODE(module,fieldname,fieldvalue,mode)
{
	if (mode != 'ajax')
		var fieldvalue = getObj(fieldname).value;
	var crmId=getObj('record').value;
	var url = "module="+module+"&action="+module+"Ajax&file=Save&"+fieldname+"="+fieldvalue+"&EXT_CODE=true&record="+crmId;
	str = getFile('index.php?'+url);
	if ( (str!="false") && (str!="duplicate") && (str!="owner")) {
		if (confirm(alert_arr.LBL_ALERT_EXT_CODE)){
					var url = "module="+module+"&action=Save&MergeCode=true&idEXT="+str+"&idCRM="+crmId;	//crmv@26320
					strss = getFile('index.php?'+url);
					if (strss=="true") {
						alert (alert_arr.LBL_ALERT_EXT_CODE_COMMIT);
						document.location.href="index.php?module="+module+"&action=DetailView&record="+str+"&parenttab=Marketing";
					}
					else alert (alert_arr.LBL_ALERT_EXT_CODE_FAIL);
		}
		else {
			if (mode != 'ajax') oform.external_code.value='';
			return false;
		}
	}
	else {
		if (str=="duplicate") {
			alert(alert_arr.LBL_ALERT_EXT_CODE_DUPLICATE);
			if (mode != 'ajax') oform.external_code.value='';
			return false;
		}
		else if (str=="owner") {
			alert(alert_arr.LBL_ALERT_EXT_CODE_NO_PERMISSION)
			if (mode != 'ajax') oform.external_code.value='';
			return false;
		}
		else if (str=="false") {
			if (confirm(alert_arr.LBL_ALERT_EXT_CODE_NOTFOUND_SAVE)){
				if (mode != 'ajax') oform.action.value='Save';
				return true;
			}
			else {
				if (mode != 'ajax') oform.external_code.value='';
				return false;
			}
		}
	}
}
//crmv@19653e
//crmv@7216
function InternalFax(record_id,field_id,field_name,par_module,type) {
        var url;
        switch(type) {
                case 'record_id':
                        url = 'index.php?module=Fax&action=FaxAjax&internal_mailer=true&type='+type+'&field_id='+field_id+'&rec_id='+record_id+'&fieldname='+field_name+'&file=EditView&par_module='+par_module;//query string field_id added for listview-compose email issue
                break;
                case 'email_addy':
                        url = 'index.php?module=Fax&action=FaxAjax&internal_mailer=true&type='+type+'&email_addy='+record_id+'&file=EditView';
                break;

        }

        var opts = "menubar=no,toolbar=no,location=no,status=no,resizable=yes,scrollbars=yes";
        openPopUp('xComposeFax',this,url,'createfaxWin',830,362,opts);
}
function ShowFax(id)
{
       url = 'index.php?module=Fax&action=FaxAjax&file=DetailView&record='+id;
       openPopUp('xComposeFax',this,url,'createfaxWin',830,362,'menubar=no,toolbar=no,location=no,status=no,resizable=no,scrollbars=yes');
}
function OpenComposeFax(id,mode)
{
    switch(mode)
    {
        case 'edit':
            url = 'index.php?module=Fax&action=FaxAjax&file=EditView&record='+id;
            break;
        case 'create':
            url = 'index.php?module=Fax&action=FaxAjax&file=EditView';
            break;
        case 'forward':
            url = 'index.php?module=Fax&action=FaxAjax&file=EditView&record='+id+'&forward=true';
            break;
        case 'Invoice':
                        url = 'index.php?module=Fax&action=FaxAjax&file=EditView&attachment='+mode+'.pdf';
            break;
    }
    openPopUp('xComposeFax',this,url,'createfaxWin',830,362,'menubar=no,toolbar=no,location=no,status=no,resizable=no,scrollbars=yes');
}
//crmv@7216e

//crmv@7217
function OpenComposeSms(id,mode)
{
    switch(mode)
    {
        case 'edit':
            url = 'index.php?module=Sms&action=SmsAjax&file=EditView&record='+id;
            break;
        case 'create':
            url = 'index.php?module=Sms&action=SmsAjax&file=EditView';
            break;
        case 'forward':
            url = 'index.php?module=Sms&action=SmsAjax&file=EditView&record='+id+'&forward=true';
            break;
    }
    openPopUp('xComposeSms',this,url,'createsmsWin',830,540,'menubar=no,toolbar=no,location=no,status=no,resizable=no,scrollbars=yes');
}
//crmv@7217e
//crmv@8719
//added for finding duplicates
function movefields()
{
	availListObj=getObj("availlist")
	selectedColumnsObj=getObj("selectedCol")
	for (i=0;i<selectedColumnsObj.length;i++)
	{

		selectedColumnsObj.options[i].selected=false
	}

	movefieldsStep1();
}

function movefieldsStep1()
{

	availListObj=getObj("availlist")
	selectedColumnsObj=getObj("selectedCol")
	document.getElementById("selectedCol").style.width="164px";
	var count=0;
	for(i=0;i<availListObj.length;i++)
	{
			if (availListObj.options[i].selected==true)
			{
				count++;
			}

	}
	var total_fields=count+selectedColumnsObj.length;
	if (total_fields >4 )
	{
		alert(alert_arr.MAX_RECORDS)
			return false
	}
	if (availListObj.options.selectedIndex > -1)
	{
		for (i=0;i<availListObj.length;i++)
		{
			if (availListObj.options[i].selected==true)
			{
				var rowFound=false;
				for (j=0;j<selectedColumnsObj.length;j++)
				{
					selectedColumnsObj.options[j].value==availListObj.options[i].value;
					if (selectedColumnsObj.options[j].value==availListObj.options[i].value)
					{
						var rowFound=true;
						var existingObj=selectedColumnsObj.options[j];
						break;
					}
				}

				if (rowFound!=true)
				{
					var newColObj=document.createElement("OPTION")
					newColObj.value=availListObj.options[i].value
					if (browser_ie) newColObj.innerText=availListObj.options[i].innerText
					else if (browser_nn4 || browser_nn6) newColObj.text=availListObj.options[i].text
					selectedColumnsObj.appendChild(newColObj)
					newColObj.selected=true
				}
				else
				{
					existingObj.selected=true
				}
				availListObj.options[i].selected=false
				movefieldsStep1();
			}
		}
	}
}

function selectedColClick(oSel)
{
	if (oSel.selectedIndex == -1 || oSel.options[oSel.selectedIndex].disabled == true)
	{
		alert(alert_arr.NOT_ALLOWED_TO_EDIT);
		oSel.options[oSel.selectedIndex].selected = false;
	}
}

function delFields()
{
	selectedColumnsObj=getObj("selectedCol");
	selected_tab = $("dupmod").value;
	if (selectedColumnsObj.options.selectedIndex > -1)
	{
		for (i=0;i < selectedColumnsObj.options.length;i++)
		{
			if(selectedColumnsObj.options[i].selected == true)
			{
				if(selected_tab == 4)
				{
					if(selectedColumnsObj.options[i].innerHTML == "Last Name")
					{
						alert(alert_arr.DEL_MANDATORY);
						del = false;
						return false;
					}
					else
						del = true;

				}
				else if(selected_tab == 7)
				{
					if(selectedColumnsObj.options[i].innerHTML == "Last Name" || selectedColumnsObj.options[i].innerHTML == "Company")
					{
						alert(alert_arr.DEL_MANDATORY);
						del = false;
						return false;
					}
					else
						del = true;
				}
				else if(selected_tab == 6)
				{
					if(selectedColumnsObj.options[i].innerHTML == "Account Name")
					{
						alert(alert_arr.DEL_MANDATORY);
						del = false;
						return false;
					}
					else
						del = true;
				}
				else if(selected_tab == 14)
				{
					if(selectedColumnsObj.options[i].innerHTML == "Product Name")
					{
						alert(alert_arr.DEL_MANDATORY);
						del = false;
						return false;
					}
					else
						del = true;
				}
				if(del == true)
				{
					selectedColumnsObj.remove(i);
					delFields();
				}
			}
		}
	}
}

function moveFieldUp()
{
	selectedColumnsObj=getObj("selectedCol")
	var currpos=selectedColumnsObj.options.selectedIndex
	var tempdisabled= false;
	for (i=0;i<selectedColumnsObj.length;i++)
	{
		if(i != currpos)
			selectedColumnsObj.options[i].selected=false
	}
	if (currpos>0)
	{
		var prevpos=selectedColumnsObj.options.selectedIndex-1

		if (browser_ie)
		{
			temp=selectedColumnsObj.options[prevpos].innerText
			tempdisabled = selectedColumnsObj.options[prevpos].disabled;
			selectedColumnsObj.options[prevpos].innerText=selectedColumnsObj.options[currpos].innerText
			selectedColumnsObj.options[prevpos].disabled = false;
			selectedColumnsObj.options[currpos].innerText=temp
			selectedColumnsObj.options[currpos].disabled = tempdisabled;
		}
		else if (browser_nn4 || browser_nn6)
		{
			temp=selectedColumnsObj.options[prevpos].text
			tempdisabled = selectedColumnsObj.options[prevpos].disabled;
			selectedColumnsObj.options[prevpos].text=selectedColumnsObj.options[currpos].text
			selectedColumnsObj.options[prevpos].disabled = false;
			selectedColumnsObj.options[currpos].text=temp
			selectedColumnsObj.options[currpos].disabled = tempdisabled;
		}
		temp=selectedColumnsObj.options[prevpos].value
		selectedColumnsObj.options[prevpos].value=selectedColumnsObj.options[currpos].value
		selectedColumnsObj.options[currpos].value=temp
		selectedColumnsObj.options[prevpos].selected=true
		selectedColumnsObj.options[currpos].selected=false
		}

}

function moveFieldDown()
{
	selectedColumnsObj=getObj("selectedCol")
	var currpos=selectedColumnsObj.options.selectedIndex
	var tempdisabled= false;
	for (i=0;i<selectedColumnsObj.length;i++)
	{
		if(i != currpos)
			selectedColumnsObj.options[i].selected=false
	}
	if (currpos<selectedColumnsObj.options.length-1)
	{
		var nextpos=selectedColumnsObj.options.selectedIndex+1

		if (browser_ie)
		{
			temp=selectedColumnsObj.options[nextpos].innerText
			tempdisabled = selectedColumnsObj.options[nextpos].disabled;
			selectedColumnsObj.options[nextpos].innerText=selectedColumnsObj.options[currpos].innerText
			selectedColumnsObj.options[nextpos].disabled = false;
			selectedColumnsObj.options[nextpos];

			selectedColumnsObj.options[currpos].innerText=temp
			selectedColumnsObj.options[currpos].disabled = tempdisabled;
		}
		else if (browser_nn4 || browser_nn6)
		{
			temp=selectedColumnsObj.options[nextpos].text
			tempdisabled = selectedColumnsObj.options[nextpos].disabled;
			selectedColumnsObj.options[nextpos].text=selectedColumnsObj.options[currpos].text
			selectedColumnsObj.options[nextpos].disabled = false;
			selectedColumnsObj.options[nextpos];
			selectedColumnsObj.options[currpos].text=temp
			selectedColumnsObj.options[currpos].disabled = tempdisabled;
		}
		temp=selectedColumnsObj.options[nextpos].value
		selectedColumnsObj.options[nextpos].value=selectedColumnsObj.options[currpos].value
		selectedColumnsObj.options[currpos].value=temp

		selectedColumnsObj.options[nextpos].selected=true
		selectedColumnsObj.options[currpos].selected=false
	}
}

function lastImport(module,req_module)
{
	var module_name= module;
	var parent_tab= document.getElementById('parenttab').value;
	if(module == '')
	{
		return false;
	}
	else

		//alert("index.php?module="+module_name+"&action=lastImport&req_mod="+req_module+"&parenttab="+parent_tab);
		openPopup("index.php?module="+module_name+"&action=lastImport&req_mod="+req_module+"&parenttab="+parent_tab,"lastImport","width=750,height=602,menubar=no,toolbar=no,location=no,status=no,resizable=no,scrollbars=yes");//crmv@21048m
}

function merge_fields(selectedNames,module,parent_tab)
{

		var select_options=document.getElementsByName(selectedNames);
		var x= select_options.length;
		var req_module=module;
		var num_group=$("group_count").innerHTML;
		var pass_url="";
		var flag=0;
		//var i=0;
		var xx = 0;
		for(i = 0; i < x ; i++)
		{
			if(select_options[i].checked)
			{
				pass_url = pass_url+select_options[i].value +","
				xx++
			}
		}
		var tmp = 0
		if ( xx != 0)
		{

			if(xx > 3)
			{
				alert(alert_arr.MAX_THREE)
					return false;
			}
			if(xx > 0)
			{
				for(j=0;j<num_group;j++)
				{
					flag = 0
					var group_options=document.getElementsByName("group"+j);
					for(i = 0; i < group_options.length ; i++)
						{
							if(group_options[i].checked)
							{
								flag++
							}
						}
					if(flag > 0)
					tmp++;
				}
				if (tmp > 1)
				{
				alert(alert_arr.SAME_GROUPS)
				return false;
				}
				if(xx <2)
				{
					alert(alert_arr.ATLEAST_TWO)
					return false;
				}

			}

			openPopup("index.php?module="+req_module+"&action=ProcessDuplicates&mergemode=mergefields&passurl="+pass_url+"&parenttab="+parent_tab,"Merge","width=750,height=602,menubar=no,toolbar=no,location=no,status=no,resizable=no,scrollbars=yes");//crmv@21048m
		}
		else
		{
			alert(alert_arr.ATLEAST_TWO);
			return false;
		}
}

function delete_fields(module)
{
	var select_options=document.getElementsByName('del');
	var x=select_options.length;
	var xx=0;
	url_rec="";

	for(var i=0;i<x;i++)
	{
		if(select_options[i].checked)
		{
		url_rec=url_rec+select_options[i].value +","
		xx++
		}
	}
	if($("current_action"))
		cur_action = $("current_action").innerHTML
	if (xx == 0)
        {
            alert(alert_arr.SELECT);
            return false;
        }
        var alert_str = alert_arr.DELETE + xx +alert_arr.RECORDS;
	if(module=="Accounts")
	alert_str = alert_arr.DELETE_ACCOUNT + xx +alert_arr.RECORDS;
	if(confirm(alert_str))
		{
			$("status").style.display="inline";
			new Ajax.Request(
          	  	      'index.php',
			      	{queue: {position: 'end', scope: 'command'},
		                        method: 'post',
                		        postBody:"module="+module+"&action="+module+"Ajax&file=FindDuplicateRecords&del_rec=true&ajax=true&return_module="+module+"&idlist="+url_rec+"&current_action="+cur_action+"&"+dup_start,
		                        onComplete: function(response) {
        	        	                $("status").style.display="none";
                	        	        $("duplicate_ajax").innerHTML= response.responseText;
						}
              			 }
       			);
		}
	else
		return false;
}


function validate_merge(module)
{
	var check_var=false;
	var check_lead1=false;
	var check_lead2=false;

	var select_parent=document.getElementsByName('record');
	var len = select_parent.length;
	for(var i=0;i<len;i++)
	{
		if(select_parent[i].checked)
		{
			var check_parentvar=true;
		}
	}
	if (check_parentvar!=true)
	{
		alert(alert_arr.Select_one_record_as_parent_record);
		return false;
	}
	return true;
}

function select_All(fieldnames,cnt,module)
{
	var new_arr = Array();
	new_arr = fieldnames.split(",");
	var len=new_arr.length;
	for(i=0;i<len;i++)
	{
		var fld_names=new_arr[i]
		var value=document.getElementsByName(fld_names)
		var fld_len=document.getElementsByName(fld_names).length;
		for(j=0;j<fld_len;j++)
		{
			value[cnt].checked='true'
			//	alert(value[j].checked)
		}

	}
}

function selectAllDel(state,checkedName)
{
		var selectedOptions=document.getElementsByName(checkedName);
		var length=document.getElementsByName(checkedName).length;
		if(typeof(length) == 'undefined')
		{
			return false;
		}
		for(var i=0;i<length;i++)
		{
			selectedOptions[i].checked=state;
		}
}

function selectDel(ThisName,CheckAllName)
	{
		var ThisNameOptions=document.getElementsByName(ThisName);
		var CheckAllNameOptions=document.getElementsByName(CheckAllName);
		var len1=document.getElementsByName(ThisName).length;
		var flag = true;
		if (typeof(document.getElementsByName(ThisName).length)=="undefined")
	       	{
			flag=true;
		}
	       	else
		{
			for (var j=0;j<len1;j++)
			{
				if (ThisNameOptions[j].checked==false)
		       		{
					flag=false
					break;
				}
			}
		}
		CheckAllNameOptions[0].checked=flag
}

// Added for page navigation in duplicate-listview
var dup_start = "";
function getDuplicateListViewEntries_js(module,url)
{
	dup_start = url;
	$("status").style.display="block";
	new Ajax.Request(
			'index.php',
			{queue: {position: 'end', scope: 'command'},
				method: 'post',
				postBody:"module="+module+"&action="+module+"Ajax&file=FindDuplicateRecords&ajax=true&"+dup_start,
				onComplete: function(response) {
					$("status").style.display="none";
					$("duplicate_ajax").innerHTML = response.responseText;
				}
			}
	);
}

function getUnifiedSearchEntries_js(module,url){
   var qryStr = document.getElementsByName('search_criteria')[0].value;
   $("status").style.display="block";
   var recordCount = document.getElementById(module+'RecordCount').value;
   new Ajax.Request(
           'index.php',
           {queue: {position: 'end', scope: 'command'},
                   method: 'post',
                   postBody:"module="+module+"&action="+module+"Ajax&file=UnifiedSearch&ajax=true&"+url+
                           '&query_string='+qryStr+'&search_onlyin='+encodeURIComponent('--USESELECTED--')+'&recordCount='+recordCount,
                   onComplete: function(response) {
                           $("status").style.display="none";
                           $('global_list_'+module).innerHTML = response.responseText;
                   }
           }
   );
}
//crmv@8719e

//crmv@vtc
function dldCntIncrease(fileid)
{
	new Ajax.Request(
            'index.php',
            {queue: {position: 'end', scope: 'command'},
             method: 'post',
             postBody: 'action=DocumentsAjax&mode=ajax&file=SaveFile&module=Documents&file_id='+fileid+"&act=updateDldCnt",
             onComplete: function(response) {
                }
    		}
  		);
}

function getFile(url) {
  if (window.XMLHttpRequest) {
    AJAX=new XMLHttpRequest();
  } else {
    AJAX=new ActiveXObject("Microsoft.XMLHTTP");
  }
  if (AJAX) {
     AJAX.open("GET", url, false);
     AJAX.send(null);
     return AJAX.responseText;
  } else {
     return false;
  }
}
//crmv@vtc end
function isdefined(variable)
{
    return (getObj(variable) == null)?  false: true;
}
/**
* this function accepts a node and puts it at the center of the screen
* @param object node - the dom object which you want to set in the center
*/
function placeAtCenter(node){
	var centerPixel = getViewPortCenter();
	node.style.position = "absolute";
	var point = getDimension(node);
	var topvalue = (centerPixel.y - point.y/2) ;
	var rightvalue = (centerPixel.x - point.x/2);

	//to ensure that values will not be negative
	if(topvalue<0) topvalue = 0;
	if(rightvalue < 0) rightvalue = 0;

	node.style.top = topvalue + "px";
	node.style.left =rightvalue + "px";
}

/**
* this function gets the dimension of a node
* @param node - the node whose dimension you want
* @return height and width in array format
*/
function getDimension(node){
	var ht = node.offsetHeight;
	var wdth = node.offsetWidth;
	var nodeChildren = node.getElementsByTagName("*");
	var noOfChildren = nodeChildren.length;
	for(var index =0;index<noOfChildren;++index){
		ht = Math.max(nodeChildren[index].offsetHeight, ht);
		wdth = Math.max(nodeChildren[index].offsetWidth,wdth);
	}
	return {x: wdth,y: ht};
}

/**
* this function returns the center co-ordinates of the viewport as an array
*/
function getViewPortCenter(){
	var height;
	var width;

	if(typeof window.pageXOffset != "undefined"){
		height = window.innerHeight/2;
		width = window.innerWidth/2;
		height +=window.pageYOffset;
		width +=window.pageXOffset;
	}else if(document.documentElement && typeof document.documentElement.scrollTop != "undefined"){
		height = document.documentElement.clientHeight/2;
		width = document.documentElement.clientWidth/2;
		height += document.documentElement.scrollTop;
		width += document.documentElement.scrollLeft;
	}else if(document.body && typeof document.body.clientWidth != "undefined"){
		height = window.screen.availHeight/2;
		width = window.screen.availWidth/2;
		height += document.body.clientHeight;
		width += document.body.clientWidth;
	}
	return {x: width,y: height};
}

/**
* this function accepts a number and displays a div stating that there is an outgoing call
* then it calls the number
* @param number - the number to be called
*/
function startCall(number, recordid){
	div = document.getElementById('OutgoingCall').innerHTML;
	outgoingPopup = _defPopup();
	outgoingPopup.content = div;
	outgoingPopup.displayPopup(outgoingPopup.content);

	//var ASTERISK_DIV_TIMEOUT = 6000;
	new Ajax.Request(
		'index.php',
		{	queue: {position: 'end', scope: 'command'},
			method: 'post',
			postBody: 'action=PBXManagerAjax&mode=ajax&file=StartCall&ajax=true&module=PBXManager&number='+number+'&recordid='+recordid,
			onComplete: function(response) {
							if(response.responseText == ''){
								//successfully called
							}else{
								alert(response.responseText);
							}
						}
		}
	);
}

function submitFormForActionWithConfirmation(formName, action, confirmationMsg) {
	if (confirm(confirmationMsg)) {
		return submitFormForAction(formName, action);
	}
	return false;
}

function submitFormForAction(formName, action) {
	var form = document.forms[formName];
	if (!form) return false;
	form.action.value = action;
	form.submit();
	return true;
}

/** Javascript dialog box utility functions **/
VtigerJS_DialogBox = {
	_olayer : function(toggle) {
		var olayerid = "__vtigerjs_dialogbox_olayer__";
		VtigerJS_DialogBox._removebyid(olayerid);

		if(typeof(toggle) == 'undefined' || !toggle) return;

		var olayer = document.getElementById(olayerid);
		if(!olayer) {
			olayer = document.createElement("div");
			olayer.id = olayerid;
			olayer.className = "small veil";
			olayer.style.zIndex = findZMax();//(new Date()).getTime();	//crmv@26491
			// In case zIndex goes to negative side!
			if(olayer.style.zIndex < 0) olayer.style.zIndex *= -1;
			if (browser_ie) {
				olayer.style.height = document.body.offsetHeight + (document.body.scrollHeight - document.body.offsetHeight) + "px";
			} else if (browser_nn4 || browser_nn6) { olayer.style.height = document.body.offsetHeight + "px"; }
			olayer.style.width = "100%";
			document.body.appendChild(olayer);

			var closeimg = document.createElement("img");
			closeimg.src = 'themes/images/close.gif';
			closeimg.alt = 'X';
			closeimg.style.right= '10px';
			closeimg.style.top  = '5px';
			closeimg.style.position = 'absolute';
			closeimg.style.cursor = 'pointer';
			closeimg.onclick = VtigerJS_DialogBox.unblock;
			olayer.appendChild(closeimg);
		}
		if(olayer) {
			if(toggle) olayer.style.display = "block";
			else olayer.style.display = "none";
		}
		return olayer;
	},
	_removebyid : function(id) {
		if(isdefined(id)) $(id).remove();
	},
	unblock : function() {
		VtigerJS_DialogBox._olayer(false);
	},
	block : function(opacity) {
		if(typeof(opactiy)=='undefined') opacity = '0.3';
		var olayernode = VtigerJS_DialogBox._olayer(true);
		olayernode.style.opacity = opacity;
	},
	hideprogress : function() {
		VtigerJS_DialogBox._olayer(false);
		VtigerJS_DialogBox._removebyid('__vtigerjs_dialogbox_progress_id__');
	},
	progress : function(imgurl) {
		VtigerJS_DialogBox._olayer(true);
		if(typeof(imgurl) == 'undefined') imgurl = 'themes/images/plsWaitAnimated.gif';

		var prgbxid = "__vtigerjs_dialogbox_progress_id__";
		var prgnode = document.getElementById(prgbxid);
		if(!prgnode) {
			prgnode = document.createElement("div");
			prgnode.id = prgbxid;
			prgnode.className = 'veil_new';
			prgnode.style.position = 'absolute';
			prgnode.style.width = '100%';
			prgnode.style.height = '100%';
			prgnode.style.top = '0';
			prgnode.style.left = '0';
			prgnode.style.display = 'block';

			document.body.appendChild(prgnode);

			prgnode.innerHTML =
			'<table border="5" cellpadding="0" cellspacing="0" align="center" style="vertical-align:middle;width:100%;height:100%;">' +
			'<tr><td class="big" align="center"><img src="'+ imgurl + '"></td></tr></table>';

		}
		if(prgnode) prgnode.style.display = 'block';
	},
	hideconfirm : function() {
		VtigerJS_DialogBox._olayer(false);
		VtigerJS_DialogBox._removebyid('__vtigerjs_dialogbox_alert_boxid__');
	},
	confirm : function(msg, onyescode) {
		VtigerJS_DialogBox._olayer(true);

		var dlgbxid = "__vtigerjs_dialogbox_alert_boxid__";
		var dlgbxnode = document.getElementById(dlgbxid);
		if(!dlgbxnode) {
			dlgbxnode = document.createElement("div");
			dlgbxnode.style.display = 'none';
			dlgbxnode.className = 'veil_new small';
			dlgbxnode.id = dlgbxid;
			dlgbxnode.innerHTML =
			'<table cellspacing="0" cellpadding="18" border="0" class="options small">' +
			'<tbody>' +
				'<tr>' +
				'<td nowrap="" align="center" style="color: rgb(255, 255, 255); font-size: 15px;">' +
				'<b>'+ msg + '</b></td>' +
				'</tr>' +
				'<tr>' +
				'<td align="center">' +
				'<input type="button" style="text-transform: capitalize;" onclick="$(\''+ dlgbxid + '\').hide();VtigerJS_DialogBox._olayer(false);VtigerJS_DialogBox._confirm_handler();" value="'+ alert_arr.YES + '"/>' +
				'<input type="button" style="text-transform: capitalize;" onclick="$(\''+ dlgbxid + '\').hide();VtigerJS_DialogBox._olayer(false)" value="' + alert_arr.NO + '"/>' +
				'</td>'+
				'</tr>' +
			'</tbody>' +
			'</table>';
			document.body.appendChild(dlgbxnode);
		}
		if(typeof(onyescode) == 'undefined') onyescode = '';
		dlgbxnode._onyescode = onyescode;
		if(dlgbxnode) dlgbxnode.style.display = 'block';
	},
	_confirm_handler : function() {
		var dlgbxid = "__vtigerjs_dialogbox_alert_boxid__";
		var dlgbxnode = document.getElementById(dlgbxid);
		if(dlgbxnode) {
			if(typeof(dlgbxnode._onyescode) != 'undefined' && dlgbxnode._onyescode != '') {
				eval(dlgbxnode._onyescode);
			}
		}
	}
}
//crmv@picklistmultiplanguage
function resetpicklist(field){
	rm_all_opt(field);
	add_opt(field,alert_arr.LBL_PLEASE_SELECT,'');
	getObj(field).value = '';
}
function rm_all_opt(field)
{
	var elSel;
	elSel = getObj(field);
  var i;
  for (i = elSel.length - 1; i>=0; i--) {
      elSel.remove(i);
  }
}

function add_opt(field,text,value)
{
  var elOptNew = document.createElement('option');
  elOptNew.text = text;
  elOptNew.value = value;
  var elSel = getObj(field);

  try {
    elSel.add(elOptNew, null); // standards compliant; doesn't work in IE
  }
  catch(ex) {
    elSel.add(elOptNew); // IE only
  }
}
//crmv@picklistmultiplanguage end
//crmv@add textlength check
function lengthComparison(fldName,fldLabel,type,constval) {
	var val=getObj(fldName).value.replace(/^\s+/g, '').replace(/\s+$/g, '').length;
	constval=parseFloat(constval)
	var ret=true
	switch (type) {
	case "L"  : if (val>=constval) {
		alert(alert_arr.LENGTH+" "+fldLabel+alert_arr.SHOULDBE_LESS+constval+" "+alert_arr.CHARACTER)
		ret=false
	}
	break;
	case "LE" :    if (val>constval) {
		alert(alert_arr.LENGTH+" "+fldLabel+alert_arr.SHOULDBE_LESS_EQUAL+constval+" "+alert_arr.CHARACTER)
		ret=false
	}
	break;
	case "E"  :    if (val!=constval) {
		alert(alert_arr.LENGTH+" "+fldLabel+alert_arr.SHOULDBE_EQUAL+constval+" "+alert_arr.CHARACTER)
		ret=false
	}
	break;
	case "NE" : if (val==constval) {
		alert(alert_arr.LENGTH+" "+fldLabel+alert_arr.SHOULDNOTBE_EQUAL+constval+" "+alert_arr.CHARACTER)
		ret=false
	}
	break;
	case "G"  :    if (val<=constval) {
		alert(alert_arr.LENGTH+" "+fldLabel+alert_arr.SHOULDBE_GREATER+constval+" "+alert_arr.CHARACTER)
		ret=false
	}
	break;
	case "GE" : if (val<constval) {
		alert(alert_arr.LENGTH+" "+fldLabel+alert_arr.SHOULDBE_GREATER_EQUAL+constval+" "+alert_arr.CHARACTER)
		ret=false
	}
	break;
	}

	if (ret==false) {
		getObj(fldName).focus()
		return false
	} else return true;
}
//crmv@add textlength check end
/******************************************************************************/
/* Activity reminder Customization: Setup Callback */
function ActivityReminderProgressIndicator(show) {
	if(show) $("status").style.display = "inline";
	else $("status").style.display = "none";
}

function ActivityReminderSetupCallback(cbmodule, cbrecord) {
	if(cbmodule && cbrecord) {

		ActivityReminderProgressIndicator(true);
		new Ajax.Request(
    		'index.php',
	        {queue: {position: 'end', scope: 'command'},
        		method: 'post',
                postBody:"module=Calendar&action=CalendarAjax&ajax=true&file=ActivityReminderSetupCallbackAjax&cbmodule="+
					encodeURIComponent(cbmodule) + "&cbrecord=" + encodeURIComponent(cbrecord),
                onComplete: function(response) {
                $("ActivityReminder_callbacksetupdiv").innerHTML=response.responseText;

				ActivityReminderProgressIndicator(false);

                }});
	}
}

function ActivityReminderSetupCallbackSave(form) {
	var cbmodule = form.cbmodule.value;
	var cbrecord = form.cbrecord.value;
	var cbaction = form.cbaction.value;

	var cbdate   = form.cbdate.value;
	var cbtime   = form.cbhour.value + ":" + form.cbmin.value;

	if(cbmodule && cbrecord) {
		ActivityReminderProgressIndicator(true);

		new Ajax.Request("index.php",
			{ queue:{position:"end", scope:"command"}, method:"post",
				postBody:"module=Calendar&action=CalendarAjax&ajax=true&file=ActivityReminderSetupCallbackAjax" +
				"&cbaction=" + encodeURIComponent(cbaction) +
				"&cbmodule="+ encodeURIComponent(cbmodule) +
				"&cbrecord=" + encodeURIComponent(cbrecord) +
				"&cbdate=" + encodeURIComponent(cbdate) +
				"&cbtime=" + encodeURIComponent(cbtime),
				onComplete:function (response) {ActivityReminderSetupCallbackSaveProcess(response.responseText);}});
	}
}
function ActivityReminderSetupCallbackSaveProcess(message) {
	ActivityReminderProgressIndicator(false);
	$('ActivityReminder_callbacksetupdiv_lay').style.display='none';
}

function ActivityReminderPostponeCallback(cbmodule, cbrecord, cbreminderid) {
	if(cbmodule && cbrecord) {

		ActivityReminderProgressIndicator(true);
		new Ajax.Request("index.php",
			{ queue:{position:"end", scope:"command"}, method:"post",
				postBody:"module=Calendar&action=CalendarAjax&ajax=true&file=ActivityReminderSetupCallbackAjax&cbaction=POSTPONE&cbmodule="+
				encodeURIComponent(cbmodule) + "&cbrecord=" + encodeURIComponent(cbrecord) + "&cbreminderid=" + encodeURIComponent(cbreminderid),
				onComplete:function (response) {ActivityReminderPostponeCallbackProcess(response.responseText);}});
	}
}
function ActivityReminderCloseCallback(cbmodule, cbrecord, cbreminderid) {
	if(cbmodule && cbrecord) {

		ActivityReminderProgressIndicator(true);
		new Ajax.Request("index.php",
				{ queue:{position:"end", scope:"command"}, method:"post",
			postBody:"module=Calendar&action=CalendarAjax&ajax=true&file=ActivityReminderSetupCallbackAjax&cbaction=CLOSE&cbmodule="+
			encodeURIComponent(cbmodule) + "&cbrecord=" + encodeURIComponent(cbrecord) + "&cbreminderid=" + encodeURIComponent(cbreminderid),
			onComplete:function (response) {ActivityReminderPostponeCallbackProcess(response.responseText);}});
	}
}
function ActivityReminderPostponeCallbackProcess(message) {
	ActivityReminderProgressIndicator(false);
}
function ActivityReminderRemovePopupDOM(id) {
	if($(id)) $(id).remove();
}
/* END */

/* ActivityReminder Customization: Pool Callback */
var ActivityReminder_regcallback_timer;

var ActivityReminder_callback_delay = 40 * 1000; // Milli Seconds
var ActivityReminder_autohide = false; // If the popup should auto hide after callback_delay?

var ActivityReminder_popup_maxheight = 75;

var ActivityReminder_callback;
var ActivityReminder_timer;
var ActivityReminder_progressive_height = 2; // px
var ActivityReminder_popup_onscreen = 2 * 1000; // Milli Seconds (should be less than ActivityReminder_callback_delay)

var ActivityReminder_callback_win_uniqueids = new Object();

function ActivityReminderCallback() {
	if(typeof(Ajax) == 'undefined') {
		return;
	}
	if(ActivityReminder_regcallback_timer) {
		window.clearTimeout(ActivityReminder_regcallback_timer);
		ActivityReminder_regcallback_timer = null;
	}
	new Ajax.Request("index.php",
			{ queue:{position:"end", scope:"command"}, method:"post",
			postBody:"module=Calendar&action=CalendarAjax&file=ActivityReminderCallbackAjax&ajax=true",
			onComplete:function (response) {ActivityReminderCallbackProcess(response.responseText);}});
}
function ActivityReminderCallbackProcess(message) {
	ActivityReminder_callback = document.getElementById("ActivityRemindercallback");
	if(ActivityReminder_callback == null) return;
	ActivityReminder_callback.style.display = 'block';

	var winuniqueid = 'ActivityReminder_callback_win_' + (new Date()).getTime();
	if(ActivityReminder_callback_win_uniqueids[winuniqueid]) {
		winuniqueid += "-" + (new Date()).getTime();
	}
	ActivityReminder_callback_win_uniqueids[winuniqueid] = true;

	var ActivityReminder_callback_win = document.createElement("span");
	ActivityReminder_callback_win.id  = winuniqueid;
	ActivityReminder_callback.appendChild(ActivityReminder_callback_win);

	$(ActivityReminder_callback_win).update(message);
	ActivityReminder_callback_win.style.height = "0px";
	ActivityReminder_callback_win.style.display = "";

	var ActivityReminder_Newdelay_response_node = '_vtiger_activityreminder_callback_interval_';
	if($(ActivityReminder_Newdelay_response_node)) {
		var ActivityReminder_Newdelay_response_value = parseInt($(ActivityReminder_Newdelay_response_node).innerHTML);
		if(ActivityReminder_Newdelay_response_value > 0) {
			ActivityReminder_callback_delay = ActivityReminder_Newdelay_response_value;
		}
		// We don't need the no any longer, it will be sent from server for next Popup
		$(ActivityReminder_Newdelay_response_node).remove();
	}
	if(message == '' || trim(message).indexOf('<script') == 0) {
		// We got only new dealay value but no popup information, let us remove the callback win created
		$(ActivityReminder_callback_win.id).remove();
		ActivityReminder_callback_win = false;
		message = '';
	}

	if(message != "") ActivityReminderCallbackRollout(ActivityReminder_popup_maxheight, ActivityReminder_callback_win);
	else { ActivityReminderCallbackReset(0, ActivityReminder_callback_win); }
}
function ActivityReminderCallbackRollout(z, ActivityReminder_callback_win) {
	ActivityReminder_callback_win = $(ActivityReminder_callback_win);

	if (ActivityReminder_timer) { window.clearTimeout(ActivityReminder_timer); }
	if (ActivityReminder_callback_win && parseInt(ActivityReminder_callback_win.style.height) < z) {
		ActivityReminder_callback_win.style.height = parseInt(ActivityReminder_callback_win.style.height) + ActivityReminder_progressive_height + "px";
		ActivityReminder_timer = setTimeout("ActivityReminderCallbackRollout(" + z + ",'" + ActivityReminder_callback_win.id + "')", 1);
	} else {
		ActivityReminder_callback_win.style.height = z + "px";
		if(ActivityReminder_autohide) ActivityReminder_timer = setTimeout("ActivityReminderCallbackRollin(1,'" + ActivityReminder_callback_win.id + "')", ActivityReminder_popup_onscreen);
		else ActivityReminderRegisterCallback(ActivityReminder_callback_delay);
	}
}
function ActivityReminderCallbackRollin(z, ActivityReminder_callback_win) {
	ActivityReminder_callback_win = $(ActivityReminder_callback_win);

	if (ActivityReminder_timer) { window.clearTimeout(ActivityReminder_timer); }
	if (parseInt(ActivityReminder_callback_win.style.height) > z) {
		ActivityReminder_callback_win.style.height = parseInt(ActivityReminder_callback_win.style.height) - ActivityReminder_progressive_height + "px";
		ActivityReminder_timer = setTimeout("ActivityReminderCallbackRollin(" + z + ",'" + ActivityReminder_callback_win.id + "')", 1);
	} else {
		ActivityReminderCallbackReset(z, ActivityReminder_callback_win);
	}
}
function ActivityReminderCallbackReset(z, ActivityReminder_callback_win) {
	ActivityReminder_callback_win = $(ActivityReminder_callback_win);

	if(ActivityReminder_callback_win) {
		ActivityReminder_callback_win.style.height = z + "px";
		ActivityReminder_callback_win.style.display = "none";
	}
	if(ActivityReminder_timer) {
		window.clearTimeout(ActivityReminder_timer);
		ActivityReminder_timer = null;
	}
	ActivityReminderRegisterCallback(ActivityReminder_callback_delay);
}
function ActivityReminderRegisterCallback(timeout) {
	if(timeout == null) timeout = 1;
	if(ActivityReminder_regcallback_timer == null) {
		ActivityReminder_regcallback_timer = setTimeout("ActivityReminderCallback()", timeout);
	}
}
function gotourl(url)
{
                document.location.href=url;
}

// Function to display the element with id given by showid and hide the element with id given by hideid
function toggleShowHide(showid, hideid)
{
	var show_ele = document.getElementById(showid);
	var hide_ele = document.getElementById(hideid);
	if(show_ele != null)
		show_ele.style.display = "inline";
	if(hide_ele != null)
		hide_ele.style.display = "none";
}
// Refactored APIs from DisplayFiels.tpl
function fnshowHide(currObj,txtObj) {
	if(currObj.checked == true)
		document.getElementById(txtObj).style.visibility = 'visible';
	else
		document.getElementById(txtObj).style.visibility = 'hidden';
}

function fntaxValidation(txtObj) {
	if (!numValidate(txtObj,"Tax","any"))
		document.getElementById(txtObj).value = 0;
}

function fnpriceValidation(txtObj) {
	if (!numValidate(txtObj,"Price","any"))
		document.getElementById(txtObj).value = 0;
}

function delimage(id) {
	new Ajax.Request(
		'index.php',
		{queue: {position: 'end', scope: 'command'},
			method: 'post',
			postBody: 'module=Contacts&action=ContactsAjax&file=DelImage&recordid='+id,
			onComplete: function(response) {
					if(response.responseText.indexOf("SUCCESS")>-1)
						$("replaceimage").innerHTML=alert_arr.LBL_IMAGE_DELETED;
					else
						alert(alert_arr.ERROR_WHILE_EDITING);
			}
		}
	);
}

function delUserImage(id) {
	new Ajax.Request(
		'index.php',
		{queue: {position: 'end', scope: 'command'},
			method: 'post',
			postBody: 'module=Users&action=UsersAjax&file=Save&deleteImage=true&recordid='+id,
			onComplete: function(response) {
					if(response.responseText.indexOf("SUCCESS")>-1)
						$("replaceimage").innerHTML=alert_arr.LBL_IMAGE_DELETED;
					else
						alert(alert_arr.ERROR_WHILE_EDITING);
			}
		}
	);
}

// Function to enable/disable related elements based on whether the current object is checked or not
function fnenableDisable(currObj,enableId) {
	var disable_flag = true;
	if(currObj.checked == true)
		disable_flag = false;

	document.getElementById('curname'+enableId).disabled = disable_flag;
	document.getElementById('cur_reset'+enableId).disabled = disable_flag;
	document.getElementById('base_currency'+enableId).disabled = disable_flag;
}

// Update current value with current value of base currency and the conversion rate
function updateCurrencyValue(currObj,txtObj,base_curid,conv_rate) {
	var unit_price = $(base_curid).value;
	//if(currObj.checked == true)
	//{
		document.getElementById(txtObj).value = unit_price * conv_rate;
	//}
}

// Synchronize between Unit price and Base currency value.
function updateUnitPrice(from_cur_id, to_cur_id) {
    var from_ele = document.getElementById(from_cur_id);
    if (from_ele == null) return;

    var to_ele = document.getElementById(to_cur_id);
    if (to_ele == null) return;

    to_ele.value = from_ele.value;
}

// Update hidden base currency value, everytime the base currency value is changed in multi-currency UI
function updateBaseCurrencyValue() {
    var cur_list = document.getElementsByName('base_currency_input');
    if (cur_list == null) return;

    var base_currency_ele = document.getElementById('base_currency');
    if (base_currency_ele == null) return;

    for(var i=0; i<cur_list.length; i++) {
		var cur_ele = cur_list[i];
		if (cur_ele != null && cur_ele.checked == true)
    		base_currency_ele.value = cur_ele.value;
	}
}
// END
//crmv@9434
function query_change_state_motivation(fieldLabel,module,uitype,tableName,fieldName,crmId,tagValue){

	var obj = null;
	var div_obj = getObj("change_"+fieldName+"_div");
	if(div_obj) {
		obj = getObj("change_status_fieldlabel");
		if(obj) obj.value = fieldLabel;
		obj = getObj("change_status_module");
		if(obj) obj.value = module;
		obj = getObj("change_status_uitype");
		if(obj) obj.value = uitype;
		obj = getObj("change_status_tablename");
		if(obj) obj.value = tableName;
		obj = getObj("change_status_fieldname");
		if(obj) obj.value = fieldName;
		obj = getObj("change_status_crmid");
		if(obj) obj.value = crmId;
		obj = getObj("change_status_tagvalue");
		if(obj) obj.value = tagValue;

		var div_2_obj = document.getElementById('change_to_state_'+fieldName+'_div');
		if(div_2_obj) div_2_obj.innerHTML = alert_arr.LBL_STATUS_CHANGING+"\""+fieldLabel+"\" "+alert_arr.LBL_STATUS_CHANGING_MOTIVATION;

		div_obj.style.display = "inline";
		div_obj.style.visible = true;

	}

}
function hide_question(div_name) {
	var div_obj = getObj(div_name);
	if(div_obj) {
		div_obj.style.display = "none";
		div_obj.style.visible = true;
	}
}
function change_state() {

	var fieldLabel = null;
	var module = null;
	var uitype = null;
	var tableName = null;
	var fieldName = null;
	var crmId = null;
	var tagValue = null;
	var motivation = null;


	var obj = null;
	obj = getObj("change_status_fieldlabel");
	if(obj) fieldLabel = obj.value;

	obj = getObj("change_status_module");
	if(obj) module = obj.value;

	obj = getObj("change_status_uitype");
	if(obj) uitype = obj.value;

	obj = getObj("change_status_tablename");
	if(obj) tableName = obj.value;

	obj = getObj("change_status_fieldname");
	if(obj) fieldName = obj.value;

	obj = getObj("change_status_crmid");
	if(obj) crmId = obj.value;

	obj = getObj("change_status_tagvalue");
	if(obj) tagValue = obj.value;

	obj = div_obj = getObj("motivation_"+fieldName);
	if(obj) motivation = obj.value;

	var data = "file=DetailViewAjax&module=" + module + "&action=" + module + "Ajax&record=" + crmId+"&recordid=" + crmId ;
	data = data + "&fldName=" + fieldName + "&fieldValue=" + escapeAll(tagValue) + "&ajxaction=DETAILVIEW&motivation="+escape(motivation);
	new Ajax.Request(
		'index.php',
                {queue: {position: 'end', scope: 'command'},
                        method: 'post',
                        postBody: data,
                        onComplete: function(response) {
							if(response.responseText.indexOf(":#:FAILURE")>-1)
							{
								alert(alert_arr.ERROR_WHILE_EDITING);
							}
							else if(response.responseText.indexOf(":#:SUCCESS")>-1)
							{

								document.location.reload();
							}
                        }
                }
            );
}
//crmv@9434  end
//crmv@18170
function SubmitQCForm(module,form) {
	if (getFormValidate()) {
		if (AjaxDuplicateValidate(module,form)) {
			return true;
		}
	}
	return false;
}
//crmv@18170e
//crmv@18592
function calculateButtonsList3() {
	jQuery('#Buttons_List_3').html(jQuery('#Buttons_List_3_Container').html());
	jQuery('#Buttons_List_3_Container').remove(); //crmv@24604
	jQuery('#Buttons_List_3').show();
	jQuery('#vte_menu_white').height(jQuery('#vte_menu').height());
}
//crmv@18592e

//crmv@21048m
function findZMax() {
	var zmax = 0;
	jQuery('*').each(function() {
    	var cur = parseInt(jQuery(this).css('zIndex'));
    	zmax = cur > zmax ? jQuery(this).css('zIndex') : zmax;
 	});
 	return eval(zmax);	//crmv@30406
}

function searchValue(search, separator, str) {
	if ((str) && str.indexOf(search + "=") > -1) {
		var fromIndex = str.indexOf(search + "=");
		var searchLen = (search + "=").length;
		var toIndex = str.indexOf(separator, fromIndex + searchLen);
		var searchValue = str.substring(fromIndex + searchLen, toIndex);
	}
	else {
		var searchValue = -1;
	}
	return searchValue;
}

function openPopup(link,title,options,scroll,newWidth,newHeight,topframe,spinner) { //crmv@22055
	var newIdAppend = searchValue('module', '&', link);
	var newId = 'openPopup' + '_' + newIdAppend;

	if (!newWidth){
		newWidth = '100%';
	}
	if (!newHeight){
		newHeight = '100%';
	}

	//crmv@22022
	if (scroll != 'no' && scroll != 'yes') {
		scroll = 'auto';
	}
	//crmv@22022e

	if (topframe == 'top') {
		var newjQuery = top.jQuery;
	} else {
 		var newjQuery = jQuery;
	}

	//crmv@29875
	var popcont = newjQuery("#popupContainer");
	var newjid = popcont.find('#'+newId);
	if (newjid.length == 0) {
		popcont.append('<a id="' + newId + '" href="'+link+'">fancybox</a>');
		newjid = popcont.find('#'+newId);
		newjid.fancybox({
			'width'    : newWidth,
			'height'   : newHeight,
			'autoScale'   : false,
			'transitionIn'  : 'none',
			'transitionOut'  : 'none',
			'type'    : 'iframe',
			'centerOnScroll' : true,
			'showCloseButton' : true,
			'scrolling'   : 'auto',
			'overlayOpacity' : 0.75,
			'padding'   : 0,
			'margin'   : 20
			//'speedIn'   : 1000
		});
	} else {
		newjid.attr('href', link);
	}
	newjid.click();
	var maxzindex = findZMax();

	newjQuery("#fancybox-wrap").css('zIndex', maxzindex+1);
	//crmv@22055
	if (spinner != 'nospinner') {
		newjQuery.fancybox.showActivity();
		newjQuery("#fancybox-loading").css('zIndex', maxzindex+2);
	}
	//crmv@22055e
	//crmv@29875e
}

function loadedPopup(topframe) {
	if (topframe == 'top') {
		newjQuery = top.jQuery;
	}
	else if (topframe == 'parent.parent') {
		newjQuery = parent.parent.jQuery;
	}
	else {
		newjQuery = parent.jQuery;
	}
	newjQuery("#fancybox-loading").each(function(i) {
		newjQuery(this).fadeOut();
	});
}

function closePopup() {
	parent.jQuery.fancybox.close();
}
//crmv@21048m e

//crmv@21996	//crmv@22622
function winMaxSetHeader(winMaxOptions) {
	//crmv@30356
 	if (isMobile()) {
 		jQuery('#winMax').hide();
 		winMaxSetHeaderMax(winMaxOptions);
 		if (getCookie('crmvWinMaxStatus') == 'close') {
 			jQuery('#winMax').click();
 		}
 	} else {
		jQuery('#winMax').hide();
		jQuery('#winMax').html('<a href="javascript:void(0);" style="display:block;text-decoration:none;"><img id="winMaxImg" alt="winMax" border=0></a>'); //crmv@24822
		jQuery('#winMaxImg').attr('src', winMaxOptions[3]);
		jQuery('#winMaxImg').attr('title', winMaxOptions[1]);
		jQuery('#winMax').fadeIn();
 	}
 	//crmv@30356e
	if (getCookie('crmvWinMaxStatus') == 'close') {
 		winMaxSetHeaderMin(winMaxOptions);
 		jQuery('#winMaxImg').attr('title', winMaxOptions[2]);
		jQuery('#winMaxImg').attr('src', winMaxOptions[4]);
 	}
 	else if (getCookie('crmvWinMaxStatus') == 'open') {
 		winMaxSetHeaderMax(winMaxOptions);
 		jQuery('#winMaxImg').attr('title', winMaxOptions[1]);
		jQuery('#winMaxImg').attr('src', winMaxOptions[3]);
 	}

 	jQuery('.winMaxWait').css('visibility', '');
 	jQuery('.winMaxWait').css('display', 'block');
 	jQuery('#vte_menu_white').height(jQuery('#vte_menu').height());

 	//crmv@25128
 	//se #winMax non � ancora disegnato la sua altezza � = 0 invece di 9, quindi
 	//aggiungo manualmente 9 (altezza di #winMax) a #vte_menu_white
 	if (jQuery('#winMax').height() == 0) {
 		jQuery('#vte_menu_white').height(jQuery('#vte_menu_white').height() + 9 );
 	}
	//crmv@25128e

	jQuery('#winMax').click(function() {
		changeStatusMenu(winMaxOptions);
		//crmv@26510
		if (gVTModule == 'Webmails') {
			getObj('squirrelmail_frame').height = jQuery(window).height()-jQuery('#vte_menu').height()-squirrelmail_frame_offset;
			squirrelmail_frame.webmailResize();
		}
		//crmv@26510e
	});

}

function changeStatusAttribute(obj,status,typeAttr,nameAttr) {

	var str, offset, addStr;

	switch(typeAttr) {
		case 'attr':
			str = obj.attr(nameAttr);
			break;
		case 'css':
			str = obj.css(nameAttr);
			break;
	}

	if(typeof(str) == 'undefined'){ return false;} //crmv@24373

	switch(nameAttr) {
		case 'src':
			offset = 4;
			addStr = '_min';
			break;
		case 'background-image':
			if (navigator.userAgent.toLowerCase().indexOf('chrome') > -1) {offset = 5;}
			else {offset = 6;}
			addStr = '_min';
			break;
		case 'onClick':
			if (navigator.userAgent.indexOf('MSIE 7.0') > -1) {
				str = obj[0].getAttributeNode('onclick').value;
			}
			offset = 2;
			addStr = ',-16';
			break;
		case 'onMouseOver':
			offset = 2;
			addStr = ',-16';
			break;
	}

	if (status == 'min') {
		str = str.substr(0, str.length - offset) + addStr + str.substr(str.length - offset, str.length);
	}
	else if (status == 'max') {
		str = str.substr(0, str.length - offset - 4) + str.substr(str.length - offset, str.length);
	}

	switch(typeAttr) {
		case 'attr':
			obj.attr(nameAttr,str);
			break;
		case 'css':
			obj.css(nameAttr,str);
			break;
	}
}

function changeStatusAll(status) {
	changeStatusAttribute(jQuery('#Buttons_List_QuickCreate img'),status,'attr','src');
	changeStatusAttribute(jQuery('#Buttons_List_Contestual_BgSx img'),status,'attr','src');
	changeStatusAttribute(jQuery('#Buttons_List_Contestual_BgDx img'),status,'attr','src');
	changeStatusAttribute(jQuery('#Buttons_List_Contestual_Container_Table'),status,'css','background-image');
	jQuery('#Buttons_List_Fixed img').each(function(index) {
		changeStatusAttribute(jQuery(this),status,'attr','src');
	});
	changeStatusAttribute(jQuery('#Buttons_List_QuickCreate img'),status,'attr','onClick');
	if (gVTModule == 'Calendar') {
		changeStatusAttribute(jQuery('#CalendarAddButton img'),status,'attr','onMouseOver');
	}
	jQuery('#Buttons_List_Contestual img').each(function(index) {
		changeStatusAttribute(jQuery(this),status,'attr','src');
	});
	jQuery('.userIcons img').each(function(index) {
		changeStatusAttribute(jQuery(this),status,'attr','src');
	});
}

//crmv@20049
function changeStatusMenu(winMaxOptions) {
	NotificationsCommon.hide('ModCommentsCheckChangesDiv');			//crmv@29079
	NotificationsCommon.hide('ModNotificationsCheckChangesDiv');	//crmv@29617
	NotificationsCommon.hide('TodosCheckChangesDiv');				//crmv@28295
	jQuery('#winMax').hide();
	var diffHeight = 28 + 27; //28 + jQuery('.winMaxHide').height();
	var diffHeightResize = diffHeight;
	var value_openclose = '';
	if (getCookie('crmvWinMaxStatus') != 'close') {
		value_openclose = 'close';
		diffHeight = '-=' + diffHeight;
		diffHeightResize = diffHeightResize;

		winMaxSetHeaderMin(winMaxOptions);
		changeStatusAll('min');

		jQuery('#winMaxImg').attr('title', winMaxOptions[2]);
		jQuery('#winMaxImg').attr('src', winMaxOptions[4]);
	}
	else {
		value_openclose = 'open';
		diffHeight = '+=' + diffHeight;
		diffHeightResize = - diffHeightResize;

		winMaxSetHeaderMax(winMaxOptions);
		changeStatusAll('max');

		jQuery('#winMaxImg').attr('title', winMaxOptions[1]);
		jQuery('#winMaxImg').attr('src', winMaxOptions[3]);
	}
	//crmv@29079	//crmv@29617	//crmv@28295
	jQuery('.winMaxAnimate').animate({
		height: diffHeight
		}, 300, function() {
			NotificationsCommon.setDivPosition(value_openclose,'ModCommentsCheckChangesDiv','ModCommentsCheckChangesImg');
			NotificationsCommon.drawChanges('ModCommentsCheckChangesDiv','ModCommentsCheckChangesImg',NotificationsCommon.unseen['ModComments'],'ModComments');
			NotificationsCommon.setDivPosition(value_openclose,'ModNotificationsCheckChangesDiv','ModNotificationsCheckChangesImg');
			NotificationsCommon.drawChanges('ModNotificationsCheckChangesDiv','ModNotificationsCheckChangesImg',NotificationsCommon.unseen['ModNotifications'],'ModNotifications');
			NotificationsCommon.setDivPosition(value_openclose,'TodosCheckChangesDiv','TodosCheckChangesImg');
			NotificationsCommon.drawChanges('TodosCheckChangesDiv','TodosCheckChangesImg',NotificationsCommon.unseen['Todos'],'Todos');
		}
	);
	//crmv@29079e	//crmv@29617e	//crmv@28295e
	saveMenuView('crmvWinMaxStatus',value_openclose);
	if (winMaxOptions[0] == 'Calendar') {
		//crmv@vte10usersFix
		jQuery('#wdCalendar').height(jQuery('#wdCalendar').height() + diffHeightResize);
		jQuery("#wdCalendar").contents().find('#filterDivCalendar').height(jQuery("#wdCalendar").contents().find('#filterDivCalendar').height() + diffHeightResize);
		jQuery("#wdCalendar").contents().find('#dvCalMain').height(jQuery("#wdCalendar").contents().find('#dvCalMain').height() + diffHeightResize);
		jQuery("#wdCalendar").contents().find('#gridcontainer').height(jQuery("#wdCalendar").contents().find('#gridcontainer').height() + diffHeightResize);

		var openAllDay = jQuery("#wdCalendar").contents().find("#openAllDay").val();
		if (openAllDay == 'false' && value_openclose == 'open') {
			jQuery("#wdCalendar").contents().find('#weekViewAllDaywk_container').height(jQuery("#wdCalendar").contents().find('#weekViewAllDaywk_container').height() + diffHeightResize);
		}
		else {
			jQuery("#wdCalendar").contents().find('#dvtec').height(jQuery("#wdCalendar").contents().find('#dvtec').height() + diffHeightResize);
		}
		jQuery("#wdCalendar").contents().find('#mvEventContainer').height(jQuery("#wdCalendar").contents().find('#mvEventContainer').height() + diffHeightResize);

		if (jQuery("#wdCalendar").contents().find('#open_dvtec').val() != '') {
			jQuery("#wdCalendar").contents().find('#open_dvtec').val(parseInt(jQuery("#wdCalendar").contents().find('#open_dvtec').val()) + diffHeightResize);
			if (jQuery("#wdCalendar").contents().find('#open_weekViewAllDaywk_container').val() != '42') {
				jQuery("#wdCalendar").contents().find('#open_weekViewAllDaywk_container').val(parseInt(jQuery("#wdCalendar").contents().find('#open_weekViewAllDaywk_container').val()) + diffHeightResize);
			}
		}
		//crmv@vte10usersFix e
	}
	else if (winMaxOptions[0] == 'Webmails') {
		jQuery('#squirrelmail_frame').height(jQuery('#squirrelmail_frame').height() + diffHeightResize);
	}
}
//crmv@20049e

function winMaxSetHeaderMax(winMaxOptions) {
	jQuery('.winMaxHide').height('27');
	jQuery('.moduleName a').css('font-size','22px');

	if (jQuery('#tdMoveTo').html() != '') {
		jQuery('#tdMoveFrom').html(jQuery('#tdMoveTo').html());
		jQuery('#tdMoveTo').html('');
		jQuery('#tdMoveFrom').css('display', '');
	}

	jQuery('#orangeTable').attr('cellpadding',5);

	jQuery('#Buttons_List_Contestual_BgSx').attr('padding-left',5);
	jQuery('#Buttons_List_Contestual_BgDx').attr('padding-right',5);
	jQuery('#Buttons_List_Contestual_Container_Table').height(55);

	if (winMaxOptions[0] == 'Webmails') {
		jQuery('#Buttons_List_Contestual_Container_Table').attr('cellpadding',5);
		jQuery('#WebmailsOptionsButton2').height(32);
	}
	else {
		jQuery('#Buttons_List_Contestual_Container_Table').attr('cellpadding',6);
	}

	jQuery('#menu_tooltip').css('top','42px');
}

function winMaxSetHeaderMin(winMaxOptions) {
	jQuery('.winMaxHide').height('0');
	jQuery('.moduleName a').css('font-size','14px');

	if (jQuery('#tdMoveFrom').html() != '') {
		jQuery('#tdMoveTo').html(jQuery('#tdMoveFrom').html());
		jQuery('#tdMoveFrom').html('');
	}

	jQuery('#orangeTable').attr('cellpadding',0);

	jQuery('#Buttons_List_Contestual_BgSx').attr('padding-left',2);
	jQuery('#Buttons_List_Contestual_BgDx').attr('padding-right',2);
	jQuery('#Buttons_List_Contestual_Container_Table').height(28);
	jQuery('#Buttons_List_Contestual_Container_Table').attr('cellpadding',4);

	if (winMaxOptions[0] == 'Webmails') {
		jQuery('#WebmailsOptionsButton2').height(18);
	}

	jQuery('#menu_tooltip').css('top','14px');
}

//crmv@20049
function saveMenuView(c_name,value) {
	setCookie('crmvWinMaxStatus',value);
	new Ajax.Request(
		'index.php',
		{queue: {position: 'end', scope: 'command'},
	        method: 'post',
	        postBody: "module=Users&action=UsersAjax&file=SaveMenuView&value="+value,
	        onComplete: function(response) {
	        	jQuery('#winMax').fadeIn(500);
	        }
	});
}
//crmv@20049e

function setCookie(c_name,value) {
	var c_value=escape(value);
	document.cookie=c_name + "=" + c_value;
}

function getCookie(c_name) {
	var i,x,y,ARRcookies=document.cookie.split(";");
	for (i=0;i<ARRcookies.length;i++) {
		x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
		y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
		x=x.replace(/^\s+|\s+$/g,"");
		if (x==c_name) {
			return unescape(y);
		}
	}
}
//crmv@21996e	//crmv@22622e

//crmv@25620
function updateBrowserTitle(title) {
	var tmp = '';
	if (title != '') {
		tmp = title;
		if (browser_title != '') {
			tmp += ' - '+browser_title;
		}
		document.title = tmp;
	}
}
//crmv@25620e

//crmv@26961
function linkInviteesTableEditView(entity_id,strVal,parentId,linkedMod) {
	if (top.jQuery('div#addEventInviteUI').contents().find('#' + entity_id + '_' + linkedMod + '_dest').length < 1) {
		strHtlm = '<tr id="' + entity_id + '_' + linkedMod + '_dest' + '" onclick="checkTr(this.id)">' +
						'<td align="center" style="display:none;"><input type="checkbox" value="' + entity_id + '_' + linkedMod + '"></td>' +
						'<td nowrap align="left" class="parent_name" style="width:100%">' + strVal + '</td>' +
					'</tr>';
		top.jQuery('#selectedTable').append(strHtlm);
	}
}
//crmv@26961e

//crmv@26986
function get_more_favorites() {
	new Ajax.Request(
		'index.php',
		{queue: {position: 'end', scope: 'command'},
	        method: 'post',
	        postBody: "module=SDK&action=SDKAjax&file=src/Favorites/GetFavoritesList&mode=all",
	        onComplete: function(response) {
	        	jQuery('#favorites_button').hide();
	        	jQuery('#favorites_div').height(jQuery('#favorites_list').height());
	        	jQuery('#favorites_div').css('overflow-y','auto');
	        	jQuery('#favorites_div').css('overflow-x','hidden');
	        	jQuery('#favorites_list').html(response.responseText);
	        }
	});
}
//crmv@26986e

//crmv@32429
function getFavoriteList() {
	if (trim(jQuery('#favorites_list').html()) == '') {
		jQuery('#indicatorFavorites').show();
		new Ajax.Request(
			'index.php',
			{queue: {position: 'end', scope: 'command'},
		        method: 'post',
		        postBody: "module=SDK&action=SDKAjax&file=src/Favorites/GetFavoritesList",
		        onComplete: function(response) {
		        	jQuery('#indicatorFavorites').hide();
		        	jQuery('#favorites_list').html(response.responseText);
		        }
		});
	}
}
function getLastViewedList() {
	if (trim(jQuery('#lastviewed_list').html()) == '') {
		jQuery('#indicatorTracker').show();
		new Ajax.Request(
			'index.php',
			{queue: {position: 'end', scope: 'command'},
		        method: 'post',
		        postBody: "module=Home&action=HomeAjax&file=LastViewed",
		        onComplete: function(response) {
		        	jQuery('#indicatorTracker').hide();
		        	jQuery('#lastviewed_list').html(response.responseText);
		        }
		});
	}
}
//crmv@32429e

//crmv@28295	//crmv@30009
function getTodoList() {
	jQuery('#indicatorTodos').show();	//crmv@32429
	jQuery('#todos_list input:checkbox').attr("disabled", true);
	new Ajax.Request(
		'index.php',
		{queue: {position: 'end', scope: 'command'},
	        method: 'post',
	        postBody: "module=SDK&action=SDKAjax&file=src/Todos/GetTodosList",
	        onComplete: function(response) {
	        	jQuery('#indicatorTodos').hide();	//crmv@32429
	        	jQuery('#todos_button').show();
	        	jQuery('#todos_list').html(response.responseText);
	        }
	});
}
function closeTodo(id,checked) {
	if (checked) {
		var status = 'Completed';
	} else {
		var status = 'Not Started';
	}
	jQuery('#todo_'+id).attr("disabled", true);
	new Ajax.Request(
		'index.php',
		{queue: {position: 'end', scope: 'command'},
			method: 'post',
			postBody: "action=Save&module=Calendar&record="+id+"&change_status=true&status="+status+'&ajaxCalendar=closeTodo',
			onComplete: function(response) {
				NotificationsCommon.drawChanges('TodosCheckChangesDiv','TodosCheckChangesImg',response.responseText,'Todos');
				jQuery('#todos_list_row_'+id).fadeOut();
				var container_id = jQuery('#todos_list_row_'+id).parent().attr('id');
				if ((jQuery('#'+container_id+' tr:visible').length-1) < 2) {
					jQuery('#'+container_id+'_toggle').fadeOut();
				}
			}
	});
}
function get_more_todos() {
	new Ajax.Request(
		'index.php',
		{queue: {position: 'end', scope: 'command'},
	        method: 'post',
	        postBody: "module=SDK&action=SDKAjax&file=src/Todos/GetTodosList&mode=all",
	        onComplete: function(response) {
	        	jQuery('#todos_button').hide();
	        	jQuery('#todos_div').height(jQuery('#todos_list').height());
	        	jQuery('#todos_div').css('overflow-y','auto');
	        	jQuery('#todos_div').css('overflow-x','hidden');
	        	jQuery('#todos_list').html(response.responseText);
	        }
	});
}
function toggleTodoPeriod(id) {
	var div = id;
	var img = '#'+id+'_img';

	if(getObj(div).style.display != "block"){
		getObj(div).style.display = "block";
        jQuery(img).attr("src", 'include/squirrelmail/images/close_details.png');
	}else{
		getObj(div).style.display = "none";
        jQuery(img).attr("src", 'include/squirrelmail/images/open_details.png');
	}
}
//crmv@28295e	//crmv@30009e
//crmv@29190
function getReturnFormName() {
	if( (jQuery('#qcform').css('display') != undefined && jQuery('#qcform').css('display') != 'none')
		|| (parent.jQuery('#qcform').css('display') != undefined && parent.jQuery('#qcform').css('display') != 'none')
	) {
		var formName = 'QcEditView';
	} else {
		var formName = 'EditView';
	}
	return formName;
}
function getReturnForm(formName) {
	if (formName == 'QcEditView' && jQuery('#qcform').css('display') == 'none' && parent.jQuery('#qcform').css('display') != 'none') {
		var form = parent.document.forms[formName];
	} else if (document.forms[formName] != undefined) {
		var form = document.forms[formName];
	} else {
		var form = parent.document.forms[formName];	//crmv@21048m
	}
	return form;
}
function loadFileJs(file) {
	//if (autocomplete_include_script != 'no') {
		jQuery.getScript(file, function(data){eval(data);});
	//}
}
function enableAdvancedFunction(form) {
	if (form.id == 'massedit_form' || form.id == 'customview_form') {
		return false;
	} else {
		return true;
	}
}
//crmv@29190e
//crmv@30356
function isMobile() {
	if (navigator.userAgent.match(/Android/i)
		|| navigator.userAgent.match(/webOS/i)
		|| navigator.userAgent.match(/iPhone/i)
		|| navigator.userAgent.match(/iPad/i)
		|| navigator.userAgent.match(/iPod/i)
		|| navigator.userAgent.match(/BlackBerry/i) 
	){
		return true;
	} else {
		return false
	}
}
//crmv@30356e
//crmv@30828
function loadContentGantt(image) {
	var string = '<img src="'+image+'" />'
	jQuery('#div_gantt').width(jQuery('#div_gantt').parent().width());
	getObj('div_gantt').innerHTML = string;
}
//crmv@30828e
//crmv@31126
function convertOptionsToJSONArray(objName,targetObjName) {
	var obj = getObj(objName); //fix
	var arr = [];
	if(typeof(obj) != 'undefined') {
		for (i=0; i<obj.options.length; ++i) {
			arr.push(obj.options[i].value);
		}
	}
	if(targetObjName != 'undefined') {
		var targetObj = getObj(targetObjName); //fix
		if(typeof(targetObj) != 'undefined') targetObj.value = JSON.stringify(arr);
	}
	return arr;
}
function copySelectedOptions(source, destination) {

	var srcObj = $(source);
	var destObj = $(destination);

	if(typeof(srcObj) == 'undefined' || typeof(destObj) == 'undefined') return;

	for (i=0;i<srcObj.length;i++) {
		if (srcObj.options[i].selected==true) {
			var rowFound=false;
			var existingObj=null;
			for (j=0;j<destObj.length;j++) {
				if (destObj.options[j].value==srcObj.options[i].value) {
					rowFound=true
					existingObj=destObj.options[j]
					break
				}
			}

			if (rowFound!=true) {
				var newColObj=document.createElement("OPTION")
				newColObj.value=srcObj.options[i].value
				if (browser_ie) newColObj.innerText=srcObj.options[i].innerText
				else if (browser_nn4 || browser_nn6) newColObj.text=srcObj.options[i].text
				destObj.appendChild(newColObj)
				srcObj.options[i].selected=false
				newColObj.selected=true
				rowFound=false
			} else {
				if(existingObj != null) existingObj.selected=true
			}
		}
	}
}

function removeSelectedOptions(objName) {
	var obj = getObj(objName);
	if(obj == null || typeof(obj) == 'undefined') return;

	for (i=obj.options.length-1;i>=0;i--) {
		if (obj.options[i].selected == true) {
			obj.options[i] = null;
		}
	}
}
//crmv@31126e
//crmv@32091
function cleanArray(actual){
	var newArray = new Array();
	for(var i = 0; i<actual.length; i++){
		var value = actual[i].trim();
		if (value && value != undefined && value != '') {
			newArray.push(actual[i]);
		}
	}
	return newArray;
}
//crmv@32091e