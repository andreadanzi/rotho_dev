<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/
-->

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html style="height:98%"> {* crmv@32091 *}
<head>
<meta http-equiv="Content-Type" content="text/html; charset={$APP.LBL_CHARSET}">
<title>{$MOD.TITLE_COMPOSE_MAIL}</title>
<link REL="SHORTCUT ICON" HREF="{php}echo get_logo('favicon');{/php}">
<style type="text/css">
@import url("themes/{$THEME}/style.css");
span.cke_wrapper cke_ltr,table.cke_editor, td.cke_contents, span.cke_skin_kama, span.cke_wrapper, span.cke_browser_webkit {ldelim}
    height: 98% !important;
{rdelim}
</style>
{* crmv@sdk-18501 *}
{if $HEADERCSS}
	{foreach item=HDRCSS from=$HEADERCSS}
		<link rel="stylesheet" type="text/css" href="{$HDRCSS->linkurl}"></script>
	{/foreach}
{/if}
{* crmv@sdk-18501 e *}
<script src="include/js/general.js" type="text/javascript"></script>
{* crmv@33041 *}
<script type="text/javascript">
var gVTModule = '{$smarty.request.module|@vtlib_purify}';
</script>
{* crmv@33041e *}
<script language="JavaScript" type="text/javascript" src="include/js/{php} echo $_SESSION['authenticated_user_language'];{/php}.lang.js?{php} echo $_SESSION['vtiger_version'];{/php}"></script>
</head>
<body marginheight="0" marginwidth="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" class="small" style="background-color:#909090;height:98%" > {* crmv@32091 *}

<div id="fakePopup" style="margin:20px;background-color:white;height:100%"> {* crmv@32091 *}
<a id="fancybox-close" style="display: block; top:5px; right:5px; z-index: 1000" onclick="window.close()" ></a>

{* crmv@21048m *}
<script language="JavaScript" type="text/javascript" src="include/js/jquery.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/jquery_plugins/timers.js"></script>
<script type="text/javascript" src="include/js/jquery_plugins/fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
<script type="text/javascript" src="include/js/jquery_plugins/fancybox/jquery.fancybox-1.3.4.js"></script>
<link rel="stylesheet" type="text/css" href="include/js/jquery_plugins/fancybox/jquery.fancybox-1.3.4.css" media="screen" />
<div id="popupContainer" style="display:none;"></div>
{* crmv@21048m e *}
{* crmv@22123 *}
{* <script type="text/javascript" src="modules/Products/multifile.js"></script> *}
<link rel="stylesheet" href="modules/Emails/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css" type="text/css" media="screen" />
<script type="text/javascript" src="modules/Emails/plupload/plupload.js"></script>
<script type="text/javascript" src="modules/Emails/plupload/plupload.gears.js"></script>
<script type="text/javascript" src="modules/Emails/plupload/plupload.silverlight.js"></script>
<script type="text/javascript" src="modules/Emails/plupload/plupload.flash.js"></script>
<script type="text/javascript" src="modules/Emails/plupload/plupload.browserplus.js"></script>
<script type="text/javascript" src="modules/Emails/plupload/plupload.html4.js"></script>
<script type="text/javascript" src="modules/Emails/plupload/plupload.html5.js"></script>
<script type="text/javascript" src="modules/Emails/plupload/jquery.plupload.queue/jquery.plupload.queue.js"></script>
{*//crmv@24568*}
<script type="text/javascript" src="modules/Emails/plupload/i18n/{php}echo get_short_language();{/php}.js"></script>
{*//crmv@24568e*}
{* crmv@22123e *}
{* crmv@sdk-18501 *}
{if $HEADERSCRIPTS}
	{foreach item=HEADERSCRIPT from=$HEADERSCRIPTS}
		<script type="text/javascript" src="{$HEADERSCRIPT->linkurl}"></script>
	{/foreach}
{/if}
{* crmv@sdk-18501 e *}
{include file='CachedValues.tpl'}	{* crmv@26316 *}
{* crmv@25356 *}
<link rel="stylesheet" href="include/js/jquery_plugins/ui/themes/base/jquery.ui.all.css">
<script type="text/javascript" src="include/js/jquery_plugins/ui/minified/jquery.ui.core.min.js"></script>
<script type="text/javascript" src="include/js/jquery_plugins/ui/minified/jquery.ui.widget.min.js"></script>
<script type="text/javascript" src="include/js/jquery_plugins/ui/minified/jquery.ui.position.min.js"></script>
<script type="text/javascript" src="include/js/jquery_plugins/ui/minified/jquery.ui.autocomplete.min.js"></script>
<script language="javascript" type="text/javascript" src="include/scriptaculous/prototype.js"></script>
<script src="include/scriptaculous/scriptaculous.js" type="text/javascript"></script>
<!-- vtlib customization: Help information assocaited with the fields -->
<script type="text/javascript" src="include/js/vtlib.js"></script>
{if $FIELDHELPINFO}
	<script type='text/javascript'>
	{literal}var fieldhelpinfo = {}; {/literal}
	{foreach item=FIELDHELPVAL key=FIELDHELPKEY from=$FIELDHELPINFO}
		fieldhelpinfo["{$FIELDHELPKEY}"] = "{$FIELDHELPVAL}";
	{/foreach}
	</script>
{/if}
<!-- END -->
<link rel="stylesheet" href="include/js/jquery_plugins/ui/themes/demos.css">
<style>
.ui-autocomplete-loading {ldelim}
	background: white url('include/js/jquery_plugins/ui/themes/ui-anim_basic_16x16.gif') right center no-repeat;
{rdelim}
#to_mail {ldelim}
	background-color: transparent;
    border: 0 none !important;
    margin-left: 3px;
    outline: medium none !important;
{rdelim}
.addrBubble {ldelim}
	overflow: visible;
	position: static;
	background-color: #EDF4FD;
	border: 1px solid #999999;
    border-radius: 4px 4px 4px 4px;
    color: black;
    display: inline-block;
    margin-bottom: 2px;
    margin-left: 3px;
    padding: 1px 3px;
{rdelim}
.ImgBubbleDelete {ldelim}
	display:inline-block;
	cursor:pointer;
	background: url("themes/images/close.png") no-repeat;
    height: 12px !important;
    overflow: hidden;
    width: 12px !important;
{rdelim}
.ui-menu  {ldelim}
	font-size: 11px;
{rdelim}
</style>
{* crmv@25356e *}

<form name="EditView" method="POST" ENCTYPE="multipart/form-data" action="index.php" style="height:100%"> {* crmv@32091 *}
<input type="hidden" name="send_mail" >
<input type="hidden" name="contact_id" value="{$CONTACT_ID}">
<input type="hidden" name="user_id" value="{$USER_ID}">
<input type="hidden" name="filename" value="{$FILENAME}">
<input type="hidden" name="old_id" value="{$OLD_ID}">
<input type="hidden" name="module" value="{$MODULE}">
<input type="hidden" name="record" value="{$ID}">
<input type="hidden" name="mode" value="{$MODE}">
<input type="hidden" name="action">
<input type="hidden" name="popupaction" value="create">
<input type="hidden" name="hidden_toid" id="hidden_toid">
<input type="hidden" name="draft_id" id="draft_id" value="{$smarty.request.draft_id}">
<!-- crmv@16265 -->
{if $SQUIRRELMAIL eq true}
	<input type="hidden" name="squirrelmail" value="true">
	<input type="hidden" name="squirrelvalues" value="{$SQUIRRELVALUES}">
{/if}
<!-- crmv@16265e -->
{* crmv@2043m *}
{if $smarty.request.reply_mail_converter neq ''}
	<input type="hidden" name="reply_mail_converter" value="{$smarty.request.reply_mail_converter}">
	<input type="hidden" name="reply_mail_converter_record" value="{$smarty.request.reply_mail_converter_record}">
	<input type="hidden" name="reply_mail_user" value="{$smarty.request.reply_mail_user}">
{/if}
{* crmv@2043me *}
{* crmv@25356 *}
<table class="small mailClient" border="0" cellpadding="0" cellspacing="0" width="100%" height="100%"> {* crmv@32091 *}
<tbody>
   <tr>
	<td colspan=3 >
	<!-- Email Header -->
	<table id="emailHeader" border=0 cellspacing=0 cellpadding=0 width=100% class="mailClientWriteEmailHeader" style="position:relative;" >{* crmv@22227 *}
	<tr>
		<td >{$MOD.LBL_COMPOSE_EMAIL}</td>
	</tr>
	</table>
	</td>
	</tr>
	{* crmv@22227 *}

	<tr><td colspan="3">{include file='Buttons_List_Edit.tpl'}</td></tr>
	<tr valign="top" height="60px"><td>
	<script>
		{literal}
		jQuery('#Buttons_List_4').css({position: 'relative'});
		{/literal}
	</script>
	<table cellspacing="0" cellpadding="0" height="100%"> {* crmv@32091 *}
	{* crmv@22227e *}
	{foreach item=row from=$BLOCKS}
	{foreach item=elements from=$row}
	{if $elements.2.0 eq 'from_email'}
		{* crmv@2051m *}
   		<tr height="30px">
			<td class="mailSubHeader edit" align="center" width="60px"><b>{$MOD.LBL_FROM}</b></td>
			<td class="cellText" align="left" style="width:500px">
				<select id="from_email" name="from_email" class="small">
					{foreach item="from_email_entity" from=$FROM_EMAIL_LIST}
						<option value="{$from_email_entity.email}" {if $from_email_entity.selected eq 'selected'}selected{/if}>{if $from_email_entity.name neq ''}"{$from_email_entity.name}"{/if}&lt;{$from_email_entity.email}&gt;</option>
					{/foreach}
				</select>
			</td>
			<td>&nbsp;</td>
			{* crmv@26639 *}
			<td nowrap rowspan="2" valign="top">
				{'Send Mode'|getTranslatedString:'Emails'}:&nbsp;
				{* vtlib customization: Help information for the fields *}
				{assign var="fldhelplink" value=""}
				{if $FIELDHELPINFO && $FIELDHELPINFO.send_mode}
					{assign var="fldhelplinkimg" value='help_icon.gif'|@vtiger_imageurl:$THEME}
					{assign var="fldhelplink" value="<img style='cursor:pointer' onclick='vtlib_field_help_show(this, \"send_mode\");' border=0 src='$fldhelplinkimg'>"}
					{$fldhelplink}
				{/if}
				{* END *}
				<br />
				<input type="radio" name="send_mode" id="send_mode_single" value="single" {if $SEND_MODE eq 'single'}checked="checked"{/if}/>&nbsp;<label for="send_mode_single">{'LBL_SINGLE_MODE'|getTranslatedString:'Emails'}</label><br />
				<input type="radio" name="send_mode" id="send_mode_multiple" value="multiple" {if $SEND_MODE eq 'multiple'}checked="checked"{/if}/>&nbsp;<label for="send_mode_multiple">{'LBL_MULTIPLE_MODE'|getTranslatedString:'Emails'}</label>
			</td>
			{* crmv@26639e *}
   		</tr>
   		{* crmv@2051me *}
   	{elseif $elements.2.0 eq 'parent_id'}
   		<tr height="30px">
			{* crmv@25562 *}
			<td class="mailSubHeader" align="center" width="60px">
				<input class="crmbutton small edit" style="width:100%" type="button" value="{$MOD.LBL_TO}"
				onclick='openPopup("index.php?return_module={$MODULE}&module=Emails&action=EmailsAjax&file=PopupDest&fromEmail=1","","","auto",1050,505);'>
			</td>
			{* crmv@25562e *}
			<td class="cellText" align="left" style="width:500px">	{* crmv@25562 *}
		 		<input name="{$elements.2.0}" id="{$elements.2.0}" type="hidden" value="{$IDLISTS}">
				<input type="hidden" name="saved_toid" id="saved_toid" value="{$TO_MAIL}">
				<input id="parent_name" name="parent_name" readonly class="txtBox1" type="hidden" value="{$TO_MAIL}" style="width:500px">&nbsp;
				<div class="txtBox1" style="width:500px;" id="autosuggest_to">
					{$AUTOSUGGEST}
					<input id="to_mail" name="to_mail" class="txtBox1" style="width: 497px;" value="{$OTHER_TO_MAIL}">
				</div>&nbsp;
			</td>
			<td class="cellText" style="padding: 5px;" align="left" width="60px" nowrap>
				{* crmv@21048m *}
				<span class="mailClientCSSButton" ><img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE=javascript onClick="jQuery('#parent_id').val(''); jQuery('#hidden_toid').val('');jQuery('#parent_name').val('');jQuery('#saved_toid').val('');jQuery('#to_mail').val('');jQuery('#autosuggest_to span').remove();return false;" align="absmiddle" style='cursor:hand;cursor:pointer'></span>
				{* crmv@21048me *}
			</td>
   		</tr>
		<tr height="30px">
		{if 'ccmail'|@emails_checkFieldVisiblityPermission eq '0'}
		   	{* crmv@25562 *}
			<td class="mailSubHeader" align="center" width="60px">
				<input class="crmbutton small edit" style="width:100%" type="button" value="{$MOD.LBL_CC}"
				onclick='openPopup("index.php?return_module={$MODULE}&module=Emails&action=EmailsAjax&file=PopupDest&fromEmail=1","","","auto",1050,505);'>
			</td>
			{* crmv@25562e *}
			<td class="cellText" align="left" style="width:500px; padding-top: 14px;">	{* crmv@25562 *}
				<input name="ccmail" id ="cc_name" class="txtBox1" type="text" value="{$CC_MAIL}" style="width:500px">&nbsp;{* crmv@21048m *}
			</td>
			<td class="cellText" style="padding: 5px;" align="left" width="60px" nowrap>
				<span class="mailClientCSSButton" ><img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE=javascript onClick="jQuery('#cc_name').val('');return false;" align="absmiddle" style='cursor:hand;cursor:pointer'></span>
			</td>
		{else}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		{/if}
		<td><a href="javascript:;" onclick="getObj('ccn_row').show();this.hide();">Aggiungi Ccn</a></td>{* crmv@26639 *}
	   	</tr>
		<tr height="30px" id="ccn_row" style="display:none;">
	   	{if 'bccmail'|@emails_checkFieldVisiblityPermission eq '0'}
			{* crmv@25562 *}
			<td class="mailSubHeader" align="center" width="60px">
				<input class="crmbutton small edit" style="width:100%" type="button" value="{$MOD.LBL_BCC}"
				onclick='openPopup("index.php?return_module={$MODULE}&module=Emails&action=EmailsAjax&file=PopupDest&fromEmail=1","","","auto",1050,505);'>
			</td>
			{* crmv@25562e *}
			<td class="cellText" align="left" style="width:500px; padding-top: 14px;">	{* crmv@25562 *}
				<input name="bccmail" id="bcc_name" class="txtBox1" type="text" value="{$BCC_MAIL}" style="width:500px">&nbsp;{* crmv@21048m *}
			</td>
			<td class="cellText" style="padding: 5px;" align="left" width="60px" nowrap>
				<span class="mailClientCSSButton" ><img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE=javascript onClick="jQuery('#bcc_name').val('');return false;" align="absmiddle" style='cursor:hand;cursor:pointer'></span>
			</td>
		{else}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
	   	{/if}
	   	<td>&nbsp;</td>{* crmv@26639 *}
		</tr>
	   	</table>
	   	</td>
	   	{* crmv@22123 *}
	    <td valign="top" align="right" class="cellLabel" style="padding-top: 3px; padding-right: 0px;" rowspan="2">
			<div id="attach_cont" class="addEventInnerBox" style="border:none;width:100%;position:relative;left:0px;top:0px;"></div>
		</td>
		{* crmv@22123e *}
	   	</tr>
	{elseif $elements.2.0 eq 'subject'}
	   	<tr height="30px">
		<td colspan="2">
		<table cellspacing="0" cellpadding="0"><tr>
			<td class="mailSubHeader edit" style="padding: 4px;" align="center" width="60px" nowrap><b>{$elements.1.0}:</b></td>
	        {if $WEBMAIL eq 'true' or $RET_ERROR eq 1}
				<td class="cellText"><input type="text" class="txtBox1" name="{$elements.2.0}" value="{$SUBJECT}" id="{$elements.2.0}" style="width:500px"></td>{* crmv@21048m *}
	        {else}
				<td class="cellText"><input type="text" class="txtBox1" name="{$elements.2.0}" value="{$elements.3.0}" id="{$elements.2.0}" style="width:500px"></td>{* crmv@21048m *}
	        {/if}
	    </tr></table>
	    </td>
	   	</tr>
	{elseif $elements.2.0 eq 'filename'}
   	<tr>
	<td class="cellText" style="padding: 5px;">
		{* crmv@22123 *}	{* crmv@30356 *}
		{if isMobile() neq true}
			<div id="attach_temp_cont" style="display:none;border:none;">
				<table cellspacing="0" cellpadding="0" width="170px" class="small attachmentsEmail">
					<tr><td colspan="2"><div style="border:none;">{$elements.1.0}</div></td></tr>
					<tr>
						<td></td>
						<td align="right" valign="middle">
							{if ($elements.3|@count gt 0) OR ($smarty.request.attachment != '') OR ($COMMON_TEMPLATE_NAME neq '') OR ($webmail_attachments neq '')}{* crmv@22139 *} {* crmv@23060 *} {* crmv@25554 *}
							<div style="width: 450px;height:60px;overflow:auto;">
								<table cellpaddin="0" cellspacing="0" class="small" width="100%">
								{if $smarty.request.attachment != ''}
									<tr><td width="100%" colspan="2">{$smarty.request.attachment|@vtlib_purify}<input type="hidden" value="{$smarty.request.attachment|@vtlib_purify}" name="pdf_attachment"></td></tr>
								{else} {* crmv@23060 *}
									{foreach item="attach_files" key="attach_id" from=$elements.3}
										<tr id="row_{$attach_id}"><td width="90%">{$attach_files}</td><td align="right"><img src="{'no.gif'|@vtiger_imageurl:$THEME}" onClick="delAttachments({$attach_id})" alt="{$APP.LBL_DELETE_BUTTON}" title="{$APP.LBL_DELETE_BUTTON}" style="cursor:pointer;"></td></tr>
									{/foreach}
									<input type='hidden' name='att_id_list' value='{$ATT_ID_LIST}' />
								{/if}
								{if $WEBMAIL eq 'true'}
									{foreach item="attach_files" from=$webmail_attachments}
							                <tr><td width="90%">{$attach_files}</td></tr>
							        {/foreach}
								{/if}
								</table>
							</div>
							{/if}
							<div id="uploader" style="width: 450px;height: 90px;">You browser doesn't support upload.</div>
						</td>
					</tr>
				</table>
			</div>
		{/if}
		{* crmv@22123e *}	{* crmv@30356e *}
		{$elements.3.0}
	</td>
   	</tr>
	{elseif $elements.2.0 eq 'description'}
   	<tr height="100%"> {* crmv@32091 *}
	<td colspan="3" align="center" valign="top" style="height:100%"> {* crmv@32091 *}
        {if $WEBMAIL eq 'true' or $RET_ERROR eq 1}
		<input type="hidden" name="from_add" value="{$from_add}">
		<input type="hidden" name="att_module" value="Webmails">
		<input type="hidden" name="mailid" value="{$mailid}">
		<input type="hidden" name="mailbox" value="{$mailbox}">
		{* crmv@23060 *} {* crmv@24717 - tolto display:none *}
        	<textarea class="detailedViewTextBox" id="description" name="description" cols="90" rows="8">{$DESCRIPTION}&nbsp;</textarea>
        {else}
            <textarea class="detailedViewTextBox" id="description" name="description" cols="90" rows="16" style="height:100%">{$elements.3.0}&nbsp;</textarea>
        {/if}
        {* crmv@23060e *} {* crmv@24717e *}
	</td>
  	</tr>
	{/if}
	{/foreach}
	{/foreach}
</tbody>
</table>
</form>

</div>

</body>
<script>
var cc_err_msg = '{$MOD.LBL_CC_EMAIL_ERROR}';
var no_rcpts_err_msg = '{$MOD.LBL_NO_RCPTS_EMAIL_ERROR}';
var bcc_err_msg = '{$MOD.LBL_BCC_EMAIL_ERROR}';
var conf_mail_srvr_err_msg = '{$MOD.LBL_CONF_MAILSERVER_ERROR}';
//crmv@7216
var no_subject = '{$MOD.MESSAGE_NO_SUBJECT}';
var no_subject_label = '{$MOD.LBL_NO_SUBJECT}';
//crmv@7216e
{literal}
function email_validate(oform,mode) {
	if(trim(mode) == '') {
		return false;
	}

	var empty_rcpt = false;
	var empty_cc = false;
	var empty_bcc = false;
	var empty_subject = false;
	var empty_body = false;

	// controlla destinatario
	var dests = jQuery('#parent_id').val();
	var dests1 = jQuery('#to_mail').val();
	if (dests != undefined && dests == '' && dests1 != undefined && dests1 == '') {
		if (mode == 'save' || mode == 'auto_save') {
			empty_rcpt = true;
		} else {
			alert(no_rcpts_err_msg);
			return false;
		}
	}
	// altri destinatari
	var ccraw = jQuery('#cc_name').val();
	if (ccraw != undefined && ccraw == '') empty_cc = true;
	ccraw = jQuery('#bcc_name').val();
	if (ccraw != undefined && ccraw == '') empty_bcc = true;
	// corpo
	var rawbody = CKEDITOR.instances.description.getData();
	if (rawbody != undefined && rawbody == '') empty_body = true;

	//Changes made to fix tickets #4633, # 5111 to accomodate all possible email formats
	var email_regex = /^[a-zA-Z0-9]+([\_\-\.]*[a-zA-Z0-9]+[\_\-]?)*@[a-zA-Z0-9]+([\_\-]?[a-zA-Z0-9]+)*\.+([\_\-]?[a-zA-Z0-9])+(\.?[a-zA-Z0-9]+)*$/;

	if(document.EditView.ccmail != null){
		if(document.EditView.ccmail.value.length >= 1){
			var str = document.EditView.ccmail.value;
            arr = new Array();
            arr = str.split(",");
            var tmp;
	    	for(var i=0; i<=arr.length-1; i++){
	            tmp = arr[i];
	            if(tmp.match('<') && tmp.match('>')) {
                    if(!findAngleBracket(arr[i])) {
                    	if (mode == 'save' || mode == 'auto_save') {
                    		empty_cc = true;
                    	} else {
                        	alert(cc_err_msg+": "+arr[i]);
                        	return false;
                    	}
                    }
            	}
				else if(trim(arr[i]) != "" && !(email_regex.test(trim(arr[i])))) {
					if (mode == 'save' || mode == 'auto_save') {
                		empty_cc = true;
					} else {
	                    alert(cc_err_msg+": "+arr[i]);
	                    return false;
					}
	            }
			}
		}
	}
	if(document.EditView.bccmail != null){
		if(document.EditView.bccmail.value.length >= 1){
			var str = document.EditView.bccmail.value;
			arr = new Array();
			arr = str.split(",");
			var tmp;
			for(var i=0; i<=arr.length-1; i++){
				tmp = arr[i];
				if(tmp.match('<') && tmp.match('>')) {
                    if(!findAngleBracket(arr[i])) {
                    	if (mode == 'save' || mode == 'auto_save') {
                    		empty_bcc = true;
                    	} else {
                        	alert(bcc_err_msg+": "+arr[i]);
                        	return false;
                    	}
                    }
            	}
            	else if(trim(arr[i]) != "" && !(email_regex.test(trim(arr[i])))){
            		if (mode == 'save' || mode == 'auto_save') {
                		empty_bcc = true;
            		} else {
						alert(bcc_err_msg+": "+arr[i]);
						return false;
            		}
				}
			}
		}
	}
	if(oform.subject.value.replace(/^\s+/g, '').replace(/\s+$/g, '').length==0)	{
		if (mode == 'save' || mode == 'auto_save') {
			empty_subject = true;
		} else {
			if(email_sub = prompt(no_subject,no_subject_label)) { //crmv@7216
				oform.subject.value = email_sub;
			} else {
				return false;
			}
		}
	}
	//crmv@sdk-18501	//crmv@sdk-26260
	if (mode != 'save' && mode != 'auto_save') {
		sdkValidate = SDKValidate();
		if (sdkValidate) {
			sdkValidateResponse = eval('('+sdkValidate.responseText+')');
			if (!sdkValidateResponse['status']) {
				return false;
			}
		}
	}

	var all_empty = (empty_rcpt && empty_cc && empty_bcc && empty_subject && empty_body);

	//crmv@sdk-18501e	//crmv@sdk-26260e
	if(mode == 'send') {
		server_check()
	} else if(mode == 'save' || mode == 'auto_save') {
		if (all_empty) return false;
		//crmv@26491
		if (mode == 'auto_save' && typeof(getObj('__vtigerjs_dialogbox_olayer__')) != 'undefined' && getObj('__vtigerjs_dialogbox_olayer__').style.display == "block") {
			//se sto salvando la bozza (da bottone) salto il salvataggio automatico
			return false;
		} else if (mode == 'auto_save') {
			//durante il salvataggio automatico blocco i pulsanti Salva Bozza e Invia e mostro un messaggio di salvataggio automatico in corso...
			 jQuery("input[type=button]").attr("disabled", "disabled");
			 jQuery("input[type=button]").addClass("disabled");
			 jQuery('#composeEmailDraftUpdate').html(alert_arr.LBL_SAVING_DRAFT);
		}
		if (mode == 'save') {
			VtigerJS_DialogBox.block();
			jQuery('#composeEmailDraftUpdate').html(alert_arr.LBL_SAVING_DRAFT);
		}
		//crmv@26491e
		//crmv@31263
		oform.action.value='Save';
		var inputs = jQuery(oform).serializeArray();
		var params = '';
		jQuery.each(inputs, function(i, field) {
			if (field.name == 'description')
				params += '&'+field.name+'='+encodeURIComponent(rawbody);
			else if (field.name == 'mode')
				params += '&mode=';
			else
	    		params += '&'+field.name+'='+encodeURIComponent(field.value);
		});
		jQuery.ajax({
			url: 'index.php?save_in_draft='+mode,
			type: 'POST',
			data: params,
			async: (mode == 'auto_save'),
			success: function(data){
				var tmp = data.split('|##|');
				var id = tmp[1];
				if (id != 'ERROR_DRAFT')
					document.EditView.draft_id.value = id;
				document.EditView.record.value = '';
				document.EditView.mode.value = '';
				jQuery('#composeEmailDraftUpdate').html(tmp[2]);
				if (mode == 'save') VtigerJS_DialogBox.unblock();
				if (mode == 'auto_save') {
					//ripristino i pulsanti al termine del salvataggio bozza
					jQuery("input[type=button]").removeAttr("disabled");
					jQuery("input[type=button]").removeClass("disabled");
				}
			}
		});
		//crmv@31263e
	}else {
		return false;
	}
}
//function to extract the mailaddress inside < > symbols.......for the bug fix #3752
function findAngleBracket(mailadd)
{
        var strlen = mailadd.length;
        var success = 0;
        var gt = 0;
        var lt = 0;
        var ret = '';
        for(i=0;i<strlen;i++){
                if(mailadd.charAt(i) == '<' && gt == 0){
                        lt = 1;
                }
                if(mailadd.charAt(i) == '>' && lt == 1){
                        gt = 1;
                }
                if(mailadd.charAt(i) != '<' && lt == 1 && gt == 0)
                        ret = ret + mailadd.charAt(i);

        }
        if(/^[a-z0-9]([a-z0-9_\-\.]*)@([a-z0-9_\-\.]*)(\.[a-z]{2,3}(\.[a-z]{2}){0,2})$/.test(ret)){
                return true;
        }
        else
                return false;

}
function server_check()
{
	var oform = window.document.EditView;
        new Ajax.Request(
        	'index.php',
                {queue: {position: 'end', scope: 'command'},
                	method: 'post',
                        postBody:"module=Emails&action=EmailsAjax&file=Save&ajax=true&server_check=true",
			onComplete: function(response) {
						if(response.responseText.indexOf('SUCCESS') > -1)
						{
							oform.send_mail.value='true';
							oform.action.value='Save';
							//crmv@26491
							VtigerJS_DialogBox.block();
							jQuery.fancybox.showActivity();
							jQuery("#fancybox-loading").css('zIndex', findZMax()+1);
							//crmv@26491
							oform.submit();
						}else
						{
							//alert('Please Configure Your Mail Server');
							alert(conf_mail_srvr_err_msg);
							return false;
						}
               	    }
                }
        );
}
jQuery('#attach_cont').html(jQuery('#attach_temp_cont').html());// crmv@22139
function delAttachments(id)
{
    new Ajax.Request(
        'index.php',
        {queue: {position: 'end', scope: 'command'},
            method: 'post',
            postBody: 'module=Contacts&action=ContactsAjax&file=DelImage&attachmodule=Emails&recordid='+id,
            onComplete: function(response)
            {
		Effect.Fade('row_'+id);
            }
        }
    );

}

// crmv@21048m
jQuery(window).load(function() {
	loadedPopup();
});
//crmv@21048m e
//crmv@22227
/*
jQuery(document).ready(function() {
	jQuery('#emailHeader').css('z-index',findZMax());
	if (!browser_ie) {
		var addHeight = 21;
	}
	else {
		var addHeight = 0;
	}
	jQuery('#vte_menu_white_small').height(jQuery('#emailHeader').height() + addHeight);
})
*/;
//crmv@22227e
{/literal}
</script>
<!--crmv@10621-->
<script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>
<script type="text/javascript" defer="1">
{literal}
CKEDITOR.replace('description', {
	filebrowserBrowseUrl: 'include/ckeditor/filemanager/index.html',
	toolbar : 'Basic',	//crmv@31210
	{/literal}
	language : "{php}echo get_short_language();{/php}"
	{literal}
});
{/literal}
//crmv@22123
{literal}
jQuery(document).ready(function() {
	jQuery(document).everyTime('30s', function(i) {
    	//email_validate(document.EditView,'auto_save');
	});
	jQuery("#uploader").pluploadQueue({
		// General settings
		runtimes: 'html5,flash,silverlight', //crmv@25883
		url: 'index.php?module=Emails&action=EmailsAjax&file=plupload/upload',
		max_file_size: '{/literal}{php}global $upload_maxsize; echo ($upload_maxsize/1000000);{/php}{literal}mb',
		chunk_size: '1mb',
		unique_names: true,
		runtime_visible: false, // show current runtime in statusbar
		// Resize images on clientside if we can
		//resize: {width: 320, height: 240, quality: 90},
		// Specify what files to browse for
		/*
		filters: [
			{title: "Image files", extensions: "jpg,gif,png"},
			{title: "Zip files", extensions: "zip"}
		],
		*/
		// Flash/Silverlight paths
		flash_swf_url: 'modules/Emails/plupload/plupload.flash.swf',
		silverlight_xap_url: 'modules/Emails/plupload/plupload.silverlight.xap',
		// PreInit events, bound before any internal events
		preinit: {
			Init: function(up, info) {
			},
			UploadFile: function(up, file) {
				// You can override settings before the file is uploaded
				// up.settings.url = 'upload.php?id=' + file.id;
				// up.settings.multipart_params = {param1: 'value1', param2: 'value2'};
			}
		},
		// Post init events, bound after the internal events
		init: {
			Refresh: function(up) {
				// Called when upload shim is moved
			},
			StateChanged: function(up) {
				// Called when the state of the queue is changed
			},
			QueueChanged: function(up) {
				// Called when the files in queue are changed by adding/removing files
			},
			UploadProgress: function(up, file) {
				// Called while a file is being uploaded
			},
			FilesAdded: function(up, files) {
				// Callced when files are added to queue
				plupload.each(files, function(file) {
				});
				up.start();	//crmv@24568
			},
			FilesRemoved: function(up, files) {
				// Called when files where removed from queue
				plupload.each(files, function(file) {
				});
			},
			FileUploaded: function(up, file, info) {
				// Called when a file has finished uploading
				jQuery('.plupload_buttons').show();
				jQuery('.plupload_upload_status').hide();
			},
			ChunkUploaded: function(up, file, info) {
				// Called when a file chunk has finished uploading
			},
			Error: function(up, args) {
				// Called when a error has occured
				// Handle file specific error and general error
				if (args.file) {
				} else {
				}
			}
		}
	});
	//crmv@24568
	jQuery(".plupload_start").detach();
	jQuery(".plupload_header").detach();
	jQuery(".plupload_filelist_header").hide();
	//crmv@24568e
});
//crmv@22123e
//crmv@22139	//crmv@31691
{/literal}{if $smarty.request.attachment != '' && $smarty.request.rec != ''}{literal}
	new Ajax.Request(
		'index.php',
		{queue: {position: 'end', scope: 'command'},
	             method: 'post',
	  		     postBody:"module=Documents&action=DocumentsAjax&file=EmailFile&record={/literal}{$smarty.request.rec}{literal}",
	             onComplete: function(response) {}
		}
	);
{/literal}{/if}{literal}
//crmv@22139e	//crmv@31691e
</script>
{/literal}
</script>
<!--crmv@10621 e-->
<script>
{literal}
jQuery(function() {
	//crmv@32091
	function split( val ) {
		var arr = val.split( /,\s*/ );
		arr = cleanArray(arr);
		return arr;
	}
	//crmv@32091e
	function extractLast( term ) {
		return split( term ).pop();
	}
	jQuery("#to_mail")
		// don't navigate away from the field on tab when selecting an item
		.bind( "keydown", function( event ) {
			if ( event.keyCode === jQuery.ui.keyCode.TAB &&
					jQuery( this ).data( "autocomplete" ).menu.active ) {
				event.preventDefault();
			}
		})
		.autocomplete({
			source: function( request, response ) {
				jQuery.getJSON( "index.php?module=Emails&action=EmailsAjax&file=Autocomplete", {
					term: extractLast( request.term )
				}, response );
			},
			search: function() {
				// custom minLength
				var term = extractLast( this.value );
				if ( term.length < 3 ) {
					return false;
				}
			},
			focus: function() {
				// prevent value inserted on focus
				return false;
			},
			select: function( event, ui ) {
				var terms = split( this.value );
				// remove the current input
				terms.pop();
				// add placeholder to get the comma-and-space at the end
				terms.push('');
				this.value = terms.join(', ');

				// add the selected item
				var span = '<span id="to_'+ui.item.id+'" class="addrBubble">'+ui.item.value
						+'<div id="to_'+ui.item.id+'_parent_id" style="display:none;">'+ui.item.parent_id+'</div>'
						+'<div id="to_'+ui.item.id+'_parent_name" style="display:none;">'+ui.item.parent_name+'</div>'
						+'<div id="to_'+ui.item.id+'_hidden_toid" style="display:none;">'+ui.item.hidden_toid+'</div>'
						+'<div id="to_'+ui.item.id+'_remove" class="ImgBubbleDelete" onClick="removeAddress(\'to\',\''+ui.item.id+'\');"></div>'
						+'</span>';
				jQuery("#autosuggest_to").prepend(span);

				document.EditView.parent_id.value = document.EditView.parent_id.value+ui.item.parent_id+'|';
				document.EditView.parent_name.value = document.EditView.parent_name.value+ui.item.parent_name+' <'+ui.item.hidden_toid+'>,';
				document.EditView.hidden_toid.value = ui.item.hidden_toid+','+document.EditView.hidden_toid.value;

				return false;
			}
		}
	);
	jQuery("#cc_name")
		// don't navigate away from the field on tab when selecting an item
		.bind( "keydown", function( event ) {
			if ( event.keyCode === jQuery.ui.keyCode.TAB &&
					jQuery( this ).data( "autocomplete" ).menu.active ) {
				event.preventDefault();
			}
		})
		.autocomplete({
			source: function( request, response ) {
				jQuery.getJSON( "index.php?module=Emails&action=EmailsAjax&file=Autocomplete&field=cc_name", {
					term: extractLast( request.term )
				}, response );
			},
			search: function() {
				// custom minLength
				var term = extractLast( this.value );
				if ( term.length < 3 ) {
					return false;
				}
			},
			focus: function() {
				// prevent value inserted on focus
				return false;
			},
			select: function( event, ui ) {
				var terms = split( this.value );
				// remove the current input
				terms.pop();
				// add the selected item
				terms.push( ui.item.value );
				// add placeholder to get the comma-and-space at the end
				terms.push( "" );
				this.value = terms.join( ", " );
				return false;
			}
		}
	);
	jQuery("#bcc_name")
		// don't navigate away from the field on tab when selecting an item
		.bind( "keydown", function( event ) {
			if ( event.keyCode === jQuery.ui.keyCode.TAB &&
					jQuery( this ).data( "autocomplete" ).menu.active ) {
				event.preventDefault();
			}
		})
		.autocomplete({
			source: function( request, response ) {
				jQuery.getJSON( "index.php?module=Emails&action=EmailsAjax&file=Autocomplete&field=bcc_name", {
					term: extractLast( request.term )
				}, response );
			},
			search: function() {
				// custom minLength
				var term = extractLast( this.value );
				if ( term.length < 3 ) {
					return false;
				}
			},
			focus: function() {
				// prevent value inserted on focus
				return false;
			},
			select: function( event, ui ) {
				var terms = split( this.value );
				// remove the current input
				terms.pop();
				// add the selected item
				terms.push( ui.item.value );
				// add placeholder to get the comma-and-space at the end
				terms.push( "" );
				this.value = terms.join( ", " );
				return false;
			}
		}
	);
});
function removeAddress(type,id) {
	if (type == 'to') {

		var parent_id = getObj('to_'+id+'_parent_id').innerHTML;
		var parent_name = getObj('to_'+id+'_parent_name').innerHTML+' <'+getObj('to_'+id+'_hidden_toid').innerHTML+'>';
		var hidden_toid = getObj('to_'+id+'_hidden_toid').innerHTML;

		var tmp1 = getObj('parent_id').value;
		tmp1 = tmp1.replace(parent_id,'');
		getObj('parent_id').value = tmp1;

		var tmp2 = getObj('parent_name').value;
		tmp2 = tmp2.replace(parent_name,'');
		if (getObj('parent_name').value != tmp2) {
			getObj('parent_name').value = tmp2;
		} else {
			var parent_name_1 = getObj('to_'+id+'_parent_name').innerHTML+'<'+getObj('to_'+id+'_hidden_toid').innerHTML+'>';
			tmp2 = getObj('parent_name').value;
			tmp2 = tmp2.replace(parent_name_1,'');
			getObj('parent_name').value = tmp2;
		}

		var tmp3 = getObj('hidden_toid').value;
		tmp3 = tmp3.replace(hidden_toid,'');
		getObj('hidden_toid').value = tmp3;

		var d = document.getElementById('autosuggest_to');
		var olddiv = document.getElementById('to_'+id);
		d.removeChild(olddiv);
	}
}
{/literal}
</script>
{* crmv@25356e *}
</html>