{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}

<script language="JAVASCRIPT" type="text/javascript" src="include/js/smoothscroll.js"></script>


<br>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="98%">
<tbody><tr>
        <td valign="top"><img src="{'showPanelTopLeft.gif'|@vtiger_imageurl:$THEME}"></td>
        <td class="showPanelBg" style="padding: 10px;" valign="top" width="100%">
<form action="index.php" method="post" id="form" onsubmit="saveMailChimpId();return false;">
<input type='hidden' name='module' value='Mailchimp'>
<input type='hidden' name='action' value='MailchimpSettings'>
<input type='hidden' name='parenttab' value='Mailchimp'>

        <br>

	<div align=center>
			{include file='SetMenu.tpl'}
			<!-- DISPLAY -->
				<table border=0 cellspacing=0 cellpadding=5 width=100% class="settingsSelUITopLine">
					<tr>
						<td width=50 rowspan=2 valign=top><img src="{'custom.gif'|@vtiger_imageurl:$THEME}" alt="MailChimp Settings" width="48" height="48" border=0 title="MailChimp Settings"></td>
						<td class=heading2 valign=bottom><b><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a> > MailChimp Settings</b></td>
					</tr>
					<tr>
						<td valign=top class="small">Set up your module to enable the synchronization with MailChimp</td>
					</tr>
				</table>
				
				<br>
				<table border=0 cellspacing=0 cellpadding=10 width=100% >
					<tr>
					<td>
					
					<table border=0 cellspacing=0 cellpadding=5 width=100% class="tableHeading">
						<tr>
						<td class="big" height="40px;" width="90%"><strong>MailChimp Settings</strong></td>
						<td class="small" align="center" width="10%">&nbsp;
							<input title="save" class="crmButton small save" type="button" name="save" onclick="saveMailChimpId();" value="Save">
						</td>
						</tr>
					</table>
					
					<table border=0 cellspacing=0 cellpadding=0 width=100% class="listRow">
						<tr>
							<td class="small" valign=top >
								<table width="100%"  border="0" cellspacing="0" cellpadding="5">
									<tr>
										<td width="20%" nowrap class="small cellLabel">
											<strong>Please enter your MailChimp API key</strong>
										</td>
										<td width="40%" class="small cellText">
											<input type="text" id="apikey" name="apikey" size="45" value="{$apikey}"/>
										</td>
										<td width="30%" class="small" align="center">
											<span id="view_info" class="crmButton small cancel" style="display:none;"></span>
										</td>
									</tr>							
								</table>
							</td>
						</tr>
						<tr>
							<td class="small" valign=top >
								<table width="100%"  border="0" cellspacing="0" cellpadding="5">
									<tr>
										<td width="20%" nowrap class="small cellLabel">
											<strong>Please enter your Mailchimp List ID</strong>
										</td>
										<td width="40%" class="small cellText">
											<input type="text" id="listid" name="listid" size="45" value="{$listid}"/>
										</td>
										<td width="30%" class="small" align="center">
											<span id="view_info" class="crmButton small cancel" style="display:none;"></span>
										</td>
									</tr>							
								</table>
							</td>
						</tr>
						<tr>
							<td class="small" valign=top >
								<table width="100%"  border="0" cellspacing="0" cellpadding="5">
									<tr>
										<td width="20%" nowrap class="small cellLabel">
											<strong>Select whether Mailchimp Subscribers are<br />created as Contacts or Leads in vTiger</strong>
										</td>
										<td width="40%" class="small cellText">
											<input type="radio" name="newsubscriber" id="makeContact" checked="true" /><label for="makeContact">Contacts</label>
											<input type="radio" name="newsubscriber" id="makeLead" /><label for="makeLead">Leads</label>
										</td>
										<td width="30%" class="small" align="center">
											<span id="view_info" class="crmButton small cancel" style="display:none;"></span>
										</td>
									</tr>							
								</table>
							</td>
						</tr>
					</table>
					</td>
					</tr>
				</table>
			
			
			
		</td>
		</tr>
		</table>
		</td>
	</tr>
	</table>
	</div>

</td>
        <td valign="top"><img src="{'showPanelTopRight.gif'|@vtiger_imageurl:$THEME}"></td>
   </tr>
</tbody>
</form>
</table>

{literal}
<script type="text/javascript">
	function saveMailChimpId(){

	
	var apikey = document.getElementById('apikey').value;
	var listid = document.getElementById('listid').value;
	
	newsubscriber = 'contact';
	
	console.log(document.getElementById('makeContact'));
	
	if (document.getElementById('makeContact').checked == true) newsubscriber = 'contact';
	else if (document.getElementById('makeLead').checked == true) newsubscriber = 'lead'; 
	
	$('view_info').style.display = 'block';
	$('view_info').innerHTML = 'Saving in process...';
	$('view_info').className = 'crmButton small cancel';
	new Ajax.Request(
		'index.php',
		{queue: {position: 'end', scope: 'command'},
			method: 'post',
			postBody: 'module=Mailchimp&action=MailchimpAjax&file=SaveMailChimpId&apikey='+apikey+'&listid='+listid+'&parenttab=Mailchimp&newsubscriber='+newsubscriber+'&ajax=true',
			onComplete: function(response) {
				if(response.responseText == "FAILURE"){
					alert(alert_arr.ERR_FIELD_SELECTION);
					return false;
				}else{
					$('view_info').innerHTML = 'Successfully saved';
					$('view_info').className = 'crmButton small save';
					//success
					/*var div = document.getElementById('fieldList');
					div.innerHTML = response.responseText;*/
				}
			}
		}
	);
	setTimeout("hide('view_info')",3000);
}
</script>
{/literal}
