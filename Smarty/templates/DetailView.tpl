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
<link rel="stylesheet" type="text/css" media="all" href="jscalendar/calendar-win2k-cold-1.css">
<script type="text/javascript" src="jscalendar/calendar.js"></script>
<script type="text/javascript" src="jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="jscalendar/calendar-setup.js"></script>

<script type="text/javascript" src="include/js/reflection.js"></script>
<script src="include/scriptaculous/scriptaculous.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript" src="include/js/dtlviewajax.js"></script>
<span id="crmspanid" style="display:none;position:absolute;"  onmouseover="show('crmspanid');">
   <a class="link"  align="right" href="javascript:;">{$APP.LBL_EDIT_BUTTON}</a>
</span>

<div id="convertleaddiv" style="display:block;position:absolute;left:225px;top:150px;"></div>
<script>
var gVTModule = '{$smarty.request.module|@vtlib_purify}';
{literal}
function callConvertLeadDiv(id)
{
        new Ajax.Request(
                'index.php',
                {queue: {position: 'end', scope: 'command'},
                        method: 'post',
                        postBody: 'module=Leads&action=LeadsAjax&file=ConvertLead&record='+id,
                        onComplete: function(response) {
                                $("convertleaddiv").innerHTML=response.responseText;
								eval($("conv_leadcal").innerHTML);
								jQuery("#convertleaddiv").css('zIndex',jQuery('#vte_menu').css('zIndex')-1); //crmv@32334
                        }
                }
        );
}
<!-- End Of Code modified by SAKTI on 10th Apr, 2008 -->

<!-- Start of code added by SAKTI on 16th Jun, 2008 -->
function setCoOrdinate(elemId){
	oBtnObj = document.getElementById(elemId);
	var tagName = document.getElementById('lstRecordLayout');
	leftpos  = 0;
	toppos = 0;
	aTag = oBtnObj;
	do{					  
	  leftpos  += aTag.offsetLeft;
	  toppos += aTag.offsetTop;
	} while(aTag = aTag.offsetParent);
	
	tagName.style.top= toppos + 20 + 'px';
	tagName.style.left= leftpos - 276 + 'px';
}

function getListOfRecords(obj, sModule, iId,sParentTab)
{
		new Ajax.Request(
		'index.php',
		{queue: {position: 'end', scope: 'command'},
			method: 'post',
			postBody: 'module=Users&action=getListOfRecords&ajax=true&CurModule='+sModule+'&CurRecordId='+iId+'&CurParentTab='+sParentTab,
			onComplete: function(response) {
				sResponse = response.responseText;
				if (sModule == 'Accounts')
					HideHierarch();
				$("lstRecordLayout").innerHTML = sResponse;
				Lay = 'lstRecordLayout';	
				var tagName = document.getElementById(Lay);
				var leftSide = findPosX(obj);
				var topSide = findPosY(obj);
				var maxW = tagName.style.width;
				var widthM = maxW.substring(0,maxW.length-2);
				var getVal = parseInt(leftSide) + parseInt(widthM);
				if(getVal  > document.body.clientWidth ){
					leftSide = parseInt(leftSide) - parseInt(widthM);
					tagName.style.left = leftSide + 230 + 'px';
					tagName.style.top = topSide + 20 + 'px';
				}else{
					tagName.style.left = leftSide + 230 + 'px';
				}
				setCoOrdinate(obj.id);
				
				tagName.style.display = 'block';
				tagName.style.visibility = "visible";
			}
		}
	);
}

function loadDetailViewBlock(urldata, target, indicator) {

	if(typeof(target) == 'undefined') {
		target = false;
	} else {
		target = $(target);
	}
	if(typeof(indicator) == 'undefined') {
		indicator = false;
	} else {
		indicator = $(indicator);
	}
	
	if(indicator) {
		indicator.show();
	}
	
	new Ajax.Request('index.php',
	{	
		queue: {position: 'end', scope: 'command'},
        method: 'post',
        postBody:urldata,
        onComplete: function(response) {
        	if(target) {
        		target.innerHTML = response.responseText;
        		if(indicator) {
					indicator.hide();
				}
        	}
        }
	});	
	return false; // To stop event propogation
}
{/literal}

//Added to send a file, in Documents module, as an attachment in an email
function sendfile_email()
{ldelim}
	filename = $('dldfilename').value;
	OpenCompose(filename,'Documents');
{rdelim}

</script>

<div id="lstRecordLayout" class="layerPopup crmvDiv" style="display:none;width:320px;height:300px;z-index:21;position:fixed;"></div>	{*<!-- crmv@18592 -->*}

{if $MODULE eq 'Accounts' || $MODULE eq 'Contacts' || $MODULE eq 'Leads'}
        {if $MODULE eq 'Accounts'}
                {assign var=address1 value='$MOD.LBL_BILLING_ADDRESS'}
                {assign var=address2 value='$MOD.LBL_SHIPPING_ADDRESS'}
        {/if}
        {if $MODULE eq 'Contacts'}
                {assign var=address1 value='$MOD.LBL_PRIMARY_ADDRESS'}
                {assign var=address2 value='$MOD.LBL_ALTERNATE_ADDRESS'}
        {/if}
        <div id="locateMap" onMouseOut="fninvsh('locateMap')" onMouseOver="fnvshNrm('locateMap')">
                <table bgcolor="#ffffff" border="0" cellpadding="5" cellspacing="0" width="100%">
                        <tr>
							<td nowrap>
								{if $MODULE eq 'Accounts'}
									<a href="javascript:;" onClick="fninvsh('locateMap'); searchMapLocation( 'Main' );" class="calMnu">{$MOD.LBL_BILLING_ADDRESS}</a>
									<a href="javascript:;" onClick="fninvsh('locateMap'); searchMapLocation( 'Other' );" class="calMnu">{$MOD.LBL_SHIPPING_ADDRESS}</a>
                               	{/if}
								{if $MODULE eq 'Contacts'}
									<a href="javascript:;" onClick="fninvsh('locateMap'); searchMapLocation( 'Main' );" class="calMnu">{$MOD.LBL_PRIMARY_ADDRESS}</a>
									<a href="javascript:;" onClick="fninvsh('locateMap'); searchMapLocation( 'Other' );" class="calMnu">{$MOD.LBL_ALTERNATE_ADDRESS}</a>
                               {/if}
							</td>
                        </tr>
                </table>
        </div>
{/if}


<table class="margintop" width="100%" cellpadding="0" cellspacing="0" border="0"> {* crmv@25128 *}
<tr>
	<td>

		{include file='Buttons_List1.tpl'}
		
<!-- Contents -->
{*<!-- crmv@18592 -->*}
<table border=0 cellspacing=0 cellpadding=0 width=100% align=center>
<tr>
	<td valign=top><img src="{'showPanelTopLeft.gif'|@vtiger_imageurl:$THEME}"></td>
	<td class="showPanelBg" valign=top width=100%>
		<!-- PUBLIC CONTENTS STARTS-->
		<div class="small" style="padding:0px" >
		{include file='Buttons_List_Detail.tpl'}
		<!-- Account details tabs -->
		<table border=0 cellspacing=0 cellpadding=0 width=100% align=center>
		<tr>
			<td>
				<table border=0 cellspacing=0 cellpadding=3 width=100% class="small">
				<tr>
					<td class="dvtTabCache" style="width:10px" nowrap>&nbsp;</td>
					<td class="dvtSelectedCell" align=center nowrap>{$SINGLE_MOD|@getTranslatedString:$MODULE} {$APP.LBL_INFORMATION}</td>
					{* crmv@22700 *}
					{php}if (isModuleInstalled('Newsletter')) { {/php}
						{if $MODULE eq 'Campaigns'}
							<td class="dvtTabCache" style="width:10px" nowrap>&nbsp;</td>
							<td class="dvtUnSelectedCell" align=center nowrap><a href="index.php?action=Statistics&module={$MODULE}&record={$ID}&parenttab={$CATEGORY}">{'LBL_STATISTICS'|@getTranslatedString:'Newsletter'}</a></td>
						{/if}
					{php}}{/php}
					{* crmv@22700e *}
					<td class="dvtTabCache" style="width:10px">&nbsp;</td>
					{if $SinglePane_View eq 'false' && $IS_REL_LIST neq false && $IS_REL_LIST|@count > 0}
					<td class="dvtUnSelectedCell" onmouseout="fnHideDrop('More_Information_Modules_List');" onmouseover="fnDropDown(this,'More_Information_Modules_List',-10);" align="center" nowrap>{* crmv@22259 *}{* crmv@22622 *}
						<a href="index.php?action=CallRelatedList&module={$MODULE}&record={$ID}&parenttab={$CATEGORY}">{$APP.LBL_MORE} {$APP.LBL_INFORMATION}</a>
						<div onmouseover="fnShowDrop('More_Information_Modules_List')" onmouseout="fnHideDrop('More_Information_Modules_List')"
									 id="More_Information_Modules_List" class="drop_mnu" style="left: 502px; top: 76px; display: none;">
							<table border="0" cellpadding="0" cellspacing="0" width="100%">
							{foreach key=_RELATION_ID item=_RELATED_MODULE from=$IS_REL_LIST}
								<tr><td><a class="drop_down" href="index.php?action=CallRelatedList&module={$MODULE}&record={$ID}&parenttab={$CATEGORY}&selected_header={$_RELATED_MODULE}&relation_id={$_RELATION_ID}">{$_RELATED_MODULE|@getTranslatedString:$_RELATED_MODULE}</a></td></tr>
							{/foreach}
							</table>
						</div>
					</td>
					{/if}
					<td class="dvtTabCache" align="right" style="width:100%"></td>
{*<!-- crmv@18592e -->*}
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td valign=top align=left >                
				 <table border=0 cellspacing=0 cellpadding=3 width=100% class="dvtContentSpace" style="border-bottom:0;">
				<tr>

					<td align=left valign="top"> {* crmv@20260 *}
					<!-- content cache -->
										
					
				<table border=0 cellspacing=0 cellpadding=0 width=100%>
                <tr>
					<td style="padding:5px">
					<!-- Command Buttons -->
				  	<table border=0 cellspacing=0 cellpadding=0 width=100%>
							 <!-- NOTE: We should avoid form-inside-form condition, which could happen when
								Singlepane view is enabled. -->
							 <form action="index.php" method="post" name="DetailView" id="form">
							{include file='DetailViewHidden.tpl'}
						
							  <!-- Start of File Include by SAKTI on 10th Apr, 2008 -->
							 {include_php file="./include/DetailViewBlockStatus.php"}
							 <!-- Start of File Include by SAKTI on 10th Apr, 2008 -->

							{foreach key=header item=detail from=$BLOCKS}

							<!-- Detailed View Code starts here-->
							<table border=0 cellspacing=0 cellpadding=0 width=100% class="small">
							<tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                             <td align=right>
							{if $header eq $MOD.LBL_ADDRESS_INFORMATION && ($MODULE eq 'Accounts' || $MODULE eq 'Contacts' || $MODULE eq 'Leads') }
                             {if $MODULE eq 'Leads'}
                             <input name="mapbutton" value="{$APP.LBL_LOCATE_MAP}" class="crmbutton small create" type="button" onClick="searchMapLocation( 'Main' )" title="{$APP.LBL_LOCATE_MAP}">
                             {else}
                             <input name="mapbutton" value="{$APP.LBL_LOCATE_MAP}" class="crmbutton small create" type="button" onClick="fnvshobj(this,'locateMap');" onMouseOut="fninvsh('locateMap');" title="{$APP.LBL_LOCATE_MAP}">
							{/if}
                             {/if}
                             </td>
                             </tr>

							<!-- This is added to display the existing comments -->
							{if $header eq $MOD.LBL_COMMENTS || $header eq $MOD.LBL_COMMENT_INFORMATION}
							   <tr>
								<td colspan=4 class="dvInnerHeader">
						        	<b>{$MOD.LBL_COMMENT_INFORMATION}</b>
								</td>
							   </tr>
							   <tr>
									<td colspan=4>{$COMMENT_BLOCK}</td>
							   </tr>
							   <tr><td>&nbsp;</td></tr>
							{/if}
						     <tr>{strip}
						     <td colspan=4 class="dvInnerHeader">
							
							<div style="float:left;font-weight:bold;"><div style="float:left;"><a href="javascript:showHideStatus('tbl{$header|replace:' ':''}','aid{$header|replace:' ':''}','{$IMAGE_PATH}');">
							{if $BLOCKINITIALSTATUS[$header] eq 1}
								<img id="aid{$header|replace:' ':''}" src="{'activate.gif'|@vtiger_imageurl:$THEME}" style="border: 0px solid #000000;" alt="Hide" title="Hide"/>
							{else}
							<img id="aid{$header|replace:' ':''}" src="{'inactivate.gif'|@vtiger_imageurl:$THEME}" style="border: 0px solid #000000;" alt="Display" title="Display"/>
							{/if}
								</a></div><b>&nbsp;
						        	{$header}
	  			     			</b></div>
						     </td>{/strip}
					             </tr>
					</table>
	{if $BLOCKINITIALSTATUS[$header] eq 1}
	<div style="width:auto;display:block;" id="tbl{$header|replace:' ':''}" >
	{else}
	<div style="width:auto;display:none;" id="tbl{$header|replace:' ':''}" >
	{/if}
		<table border=0 cellspacing=0 cellpadding=0 width="100%" class="small">
	       {assign var="fieldcount" value=0}
	       {assign var="fieldstart" value=1}
	   		{assign var="tr_state" value=0}  							
	   		{foreach item=detail from=$detail}
			{foreach key=label item=data from=$detail}
			   {assign var=keyid value=$data.ui}
			   {assign var=keyval value=$data.value}
			   {assign var=keytblname value=$data.tablename}
			   {assign var=keyfldname value=$data.fldname}
			   {assign var=keyfldid value=$data.fldid}
			   {assign var=keyoptions value=$data.options}
			   {assign var=keysecid value=$data.secid}
			   {assign var=keyseclink value=$data.link}
			   {assign var=keycursymb value=$data.cursymb}
			   {assign var=keysalut value=$data.salut}
			   {assign var=keyaccess value=$data.notaccess}
			   {assign var=keycntimage value=$data.cntimage}
			   {assign var=keyadmin value=$data.isadmin}
			   {assign var=keyadmin value=$data.isadmin}
			   {assign var=keyreadonly value=$data.readonly}							   
			   {assign var=display_type value=$data.displaytype}
			   	{if ($fieldcount eq 0 or $fieldstart eq 1) and $tr_state neq 1}	
			  		{if $fieldstart eq 1}
						{assign var="fieldstart" value=0}
					{/if}						
			   		<tr style="height:25px">
			   		{assign var="tr_state" value=1}
				{/if}	
					{if $keyreadonly eq 100}
					{elseif ($keyreadonly eq 99 or $EDIT_PERMISSION neq 'yes' or $display_type eq '2' or empty($DETAILVIEW_AJAX_EDIT) )}
						{if ($keyid eq 19 or $keyid eq 20) and $fieldcount neq 0}
							</tr>
							<tr style="height:25px">
							{assign var="tr_state" value=1}
							{assign var="fieldcount" value=0}
						{/if}						
		                {if $keycntimage ne ''}
							<td class="dvtCellLabel" align=right width=25%><input type="hidden" id="hdtxt_IsAdmin" value={$keyadmin}></input>{$keycntimage}</td>
						{elseif $keyid eq '71' || $keyid eq '72'}<!-- Currency symbol -->
							<td class="dvtCellLabel" align=right width=25%>{$label}<input type="hidden" id="hdtxt_IsAdmin" value={$keyadmin}></input> ({$keycursymb})</td>
						{else}
							<td class="dvtCellLabel" align=right width=25%><input type="hidden" id="hdtxt_IsAdmin" value={$keyadmin}></input>{$label}</td>
						{/if}					
						{assign var="fieldcount" value=$fieldcount+1}
						{include file="DetailViewFields.tpl"}
						<!-- crmv@16834 -->
						{if $keyid eq 19 or $keyid eq 20}
							{assign var="fieldcount" value=$fieldcount+1}
						{/if}
						<!-- crmv16834e -->
					{else}		
						{if ($keyid eq 19 or $keyid eq 20) and $fieldcount neq 0}
							</tr>
							<tr style="height:25px">
							{assign var="tr_state" value=1}
							{assign var="fieldcount" value=0}
						{/if}	
						{assign var="fieldcount" value=$fieldcount+1}
	                	{if $keycntimage ne ''}
							<td class="dvtCellLabel" align=right width=25%><input type="hidden" id="hdtxt_IsAdmin" value={$keyadmin}></input>{$keycntimage}</td>
						{elseif $keyid eq '71' || $keyid eq '72'}<!-- Currency symbol -->
							<td class="dvtCellLabel" align=right width=25%>{$label}<input type="hidden" id="hdtxt_IsAdmin" value={$keyadmin}></input> ({$keycursymb})</td>
						{else}
							<td class="dvtCellLabel" align=right width=25%><input type="hidden" id="hdtxt_IsAdmin" value={$keyadmin}></input>{$label}</td>
						{/if}						
						{include file="DetailViewUI.tpl"}
						{if $keyid eq 19 or $keyid eq 20}
							{assign var="fieldcount" value=$fieldcount+1}
						{/if}
					{/if}
			    {if $fieldcount eq 2}
					</tr>
					{assign var="fieldcount" value=0}	
					{assign var="tr_state" value=0}	
				{/if}
               {/foreach}
	   			{/foreach}	
	     </table>
	 </div>
	</td>
	</tr>
	<tr>
		<td style="padding:5px">
			{/foreach}
{*-- End of Blocks--*}	
			{* vtlib Customization: Embed DetailViewWidget block:// type if any *}
			{if $CUSTOM_LINKS && !empty($CUSTOM_LINKS.DETAILVIEWWIDGET)}
			{foreach item=CUSTOM_LINK_DETAILVIEWWIDGET from=$CUSTOM_LINKS.DETAILVIEWWIDGET}
				{if preg_match("/^block:\/\/.*/", $CUSTOM_LINK_DETAILVIEWWIDGET->linkurl)}
				<!-- crmv@18485 -->
				{php}
					$widgetLinkInfo_tmp = $this->_tpl_vars['CUSTOM_LINK_DETAILVIEWWIDGET'];
					if (preg_match("/^block:\/\/(.*)/", $widgetLinkInfo_tmp->linkurl, $matches)) {
						list($widgetControllerClass_tmp, $widgetControllerClassFile_tmp) = explode(':', $matches[1]);
						if (vtlib_isModuleActive($widgetControllerClass_tmp)) {
				{/php}
				<!-- crmv@18485e -->
					<tr>
						<td style="padding:5px;" >
						{php}
							echo vtlib_process_widget($this->_tpl_vars['CUSTOM_LINK_DETAILVIEWWIDGET'], $this->_tpl_vars);
						{/php}
						</td>
					</tr>
				<!-- crmv@18485 -->				
				{php}}}{/php}
				<!-- crmv@18485e -->
				{/if}
			{/foreach}
			{/if}
			{* END *}
		</td>
	</tr>
	<!-- Inventory - Product Details informations -->
	<tr>
		<td >
			{$ASSOCIATED_PRODUCTS}
		</td>
		</tr>
		</td>
	</tr>
			
			</form>	
			<!-- End the form related to detail view -->			

			{if $SinglePane_View eq 'true' && $IS_REL_LIST|@count > 0}
				{include file= 'RelatedListNew.tpl'}
			{/if}
		</table>
		
		</td>
		{* crmv@26896 *}
		<td width=22% valign=top style="border-left:1px dashed #ffffff;padding: 23px 8px 8px 8px">
		
			{include file='Turbolift.tpl'}
			
			{* vtc *}
			{if $MODULE eq 'Accounts'}
				<br />
				<div>
					<input title="{$MOD.HideHierarchy}" class="crmbutton small create" onclick="showHideHierarch('ShowHierarch',{$ID});" type="button" id="ShowHierarch" name="ShowHierarch" value="{$MOD.HideHierarchy}">&nbsp;
					<div id="accountHierarc" style="width: 100%; display: none; z-index: 10;" class="rightMailMerge">
						<table border="0" cellpadding="0" cellspacing="0" width="100%">
							<tr><td class="rightMailMergeHeader" id="Track_Handle"><strong>{$MOD.AccountsHierarchy}</strong></td></tr>
							<tr><td>
								<div id="accountHierarcContent"></div>
							</td></tr>		
						</table>
					</div>
				</div>
			{/if}
			{* vtc e *}

			<!-- To display the Tag Clouds -->
			<div>
				{include file="TagCloudDisplay.tpl"}
			</div>
			
			<!-- Mail Merge-->
			{if $MERGEBUTTON eq 'permitted'}
				<br />
				<form action="index.php" method="post" name="TemplateMerge" id="form">
					<input type="hidden" name="module" value="{$MODULE}">
					<input type="hidden" name="parenttab" value="{$CATEGORY}">
					<input type="hidden" name="record" value="{$ID}">
					<input type="hidden" name="action">
			  		<table border=0 cellspacing=0 cellpadding=0 width=100% class="rightMailMerge">
			      		<tr>
							<td class="rightMailMergeHeader"><b>{$WORDTEMPLATEOPTIONS}</b></td>
			      		</tr>
			      		<tr style="height:25px">
							<td class="rightMailMergeContent">
								{if $TEMPLATECOUNT neq 0}
									<select name="mergefile">{foreach key=templid item=tempflname from=$TOPTIONS}<option value="{$templid}">{$tempflname}</option>{/foreach}</select>
									<input class="crmbutton small create" value="{$APP.LBL_MERGE_BUTTON_LABEL}" onclick="this.form.action.value='Merge';" type="submit"></input> 
								{else}
									<a href=index.php?module=Settings&action=upload&tempModule={$MODULE}&parenttab=Settings>{$APP.LBL_CREATE_MERGE_TEMPLATE}</a>
								{/if}
							</td>
			      		</tr>
			  		</table>
				</form>
			{/if}
					
			{if !empty($CUSTOM_LINKS.DETAILVIEWWIDGET)}
				{foreach key=CUSTOMLINK_NO item=CUSTOMLINK from=$CUSTOM_LINKS.DETAILVIEWWIDGET}
					{assign var="customlink_href" value=$CUSTOMLINK->linkurl}
					{assign var="customlink_label" value=$CUSTOMLINK->linklabel}
					{* Ignore block:// type custom links which are handled earlier *}
					{if !preg_match("/^block:\/\/.*/", $customlink_href)}
						{if $customlink_label eq ''}
							{assign var="customlink_label" value=$customlink_href}
						{else}
							{* Pickup the translated label provided by the module *}
							{assign var="customlink_label" value=$customlink_label|@getTranslatedString:$CUSTOMLINK->module()}
						{/if}
						<br/>
						<table border=0 cellspacing=0 cellpadding=0 width=100% class="rightMailMerge">
			  				<tr>
								<td class="rightMailMergeHeader">
									<b>{$customlink_label}</b>
									<img id="detailview_block_{$CUSTOMLINK_NO}_indicator" style="display:none;" src="{'vtbusy.gif'|@vtiger_imageurl:$THEME}" border="0" align="absmiddle" />
								</td>
			  				</tr>
			  				<tr style="height:25px">
								<td class="rightMailMergeContent"><div id="detailview_block_{$CUSTOMLINK_NO}"></div></td>
			  				</tr>
			  				<script type="text/javascript">
			  					vtlib_loadDetailViewWidget("{$customlink_href}", "detailview_block_{$CUSTOMLINK_NO}", "detailview_block_{$CUSTOMLINK_NO}_indicator");
			  				</script>
						</table>
					{/if}
				{/foreach}
			{/if}
			
			{include file='TurboliftUp.tpl'}
			
		{* crmv@26896e *}
		</td>
		</tr>
		</table>
		
		</div>
		<!-- PUBLIC CONTENTS STOPS-->
	</td>
</tr>
	<tr>
		<td colpsan=2>			
			<table border=0 cellspacing=0 cellpadding=3 width=100% class="small">
				<tr>
					<td class="dvtTabCacheBottom" style="width:10px" nowrap>&nbsp;</td>
					
					<td class="dvtSelectedCellBottom" align=center nowrap>{$SINGLE_MOD|@getTranslatedString:$MODULE} {$APP.LBL_INFORMATION}</td>	
					<td class="dvtTabCacheBottom" style="width:10px">&nbsp;</td>
					{if $SinglePane_View eq 'false' && $IS_REL_LIST neq false && $IS_REL_LIST|@count > 0}
					<td class="dvtUnSelectedCell" onmouseout="fnHideDrop('More_Information_Modules_List_down');" onmouseover="fnDropUp(this,'More_Information_Modules_List_down');" align="center" nowrap>
						<a href="index.php?action=CallRelatedList&module={$MODULE}&record={$ID}&parenttab={$CATEGORY}">{$APP.LBL_MORE} {$APP.LBL_INFORMATION}</a>
						<div onmouseover="fnShowDrop('More_Information_Modules_List_down')" onmouseout="fnHideDrop('More_Information_Modules_List_down')"
									 id="More_Information_Modules_List_down" class="drop_mnu" style="left: 502px; top: 76px; visibility: hidden, display: block">
							<table border="0" cellpadding="0" cellspacing="0" width="100%" id="More_Information_Modules_List_down_table">
							{foreach key=_RELATION_ID item=_RELATED_MODULE from=$IS_REL_LIST}
								<tr><td><a class="drop_down" href="index.php?action=CallRelatedList&module={$MODULE}&record={$ID}&parenttab={$CATEGORY}&selected_header={$_RELATED_MODULE}&relation_id={$_RELATION_ID}">{$_RELATED_MODULE|@getTranslatedString:$_RELATED_MODULE}</a></td></tr>
							{/foreach}
							</table>
						</div>
					</td>
					{/if}
					<td class="dvtTabCacheBottom" align="right" style="width:100%"></td>	{*<!-- crmv@18592 -->*}
				</tr>
			</table>
		</td>
	</tr>
</table>

{if $MODULE eq 'Products'}
<script language="JavaScript" type="text/javascript" src="modules/Products/Productsslide.js"></script>
<script language="JavaScript" type="text/javascript">Carousel();</script>
{elseif $MODULE eq 'Accounts'}
<script language="JavaScript" type="text/javascript">show_hierach({$ID},1);</script>
{/if}

<!-- added for validation -->
<script language="javascript">
  var fieldname = new Array({$VALIDATION_DATA_FIELDNAME});
  var fieldlabel = new Array({$VALIDATION_DATA_FIELDLABEL});
  var fielddatatype = new Array({$VALIDATION_DATA_FIELDDATATYPE});
</script>
</td>

<td align=right valign=top><img src="{'showPanelTopRight.gif'|@vtiger_imageurl:$THEME}"></td>
</tr></table>

<form name="SendMail" onsubmit="VtigerJS_DialogBox.block();"><div id="sendmail_cont" style="z-index:100001;position:absolute;"></div></form>
<form name="SendFax" onsubmit="VtigerJS_DialogBox.block();"><div id="sendfax_cont" style="z-index:100001;position:absolute;width:300px;"></div></form>
<!-- crmv@16703 -->
<form name="SendSms" id="SendSms" onsubmit="VtigerJS_DialogBox.block();" method="POST" action="index.php"><div id="sendsms_cont" style="z-index:100001;position:absolute;width:300px;"></div></form>
<!-- crmv@16703e -->