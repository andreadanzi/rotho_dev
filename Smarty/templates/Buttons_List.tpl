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
<!-- crmv@18549 crmv@19842 -->
<script type="text/javascript" src="modules/{$MODULE}/{$MODULE}.js"></script>
{* crmv@22622 *}
{php}
	if ($_COOKIE['crmvWinMaxStatus'] == 'close') {
		{/php}
			{assign var="minImg" value="_min"}
			{assign var="minFontSize" value="font-size:14px;"}
		{php}
	}
	else {
		{/php}
			{assign var="minImg" value=""}
			{assign var="minFontSize" value=""}
		{php}
	}
{/php}
{* crmv@30356 *}
{if isMobile() neq true}
<div id="Buttons_List_SiteMap_Container" style="display:none;">
	<table border=0 cellspacing=0 cellpadding=5 class=small>
	<tr>
		{if $REQUEST_ACTION neq 'ListView' && ($MODULE eq 'Calendar' || $MODULE eq 'Home' || $MODULE eq 'Webmails')}	{* crmv@26077 *}
			{assign var="action" value="index"}
		{else}
			{assign var="action" value="ListView"}
		{/if}
		{assign var="MODULELABEL" value=$MODULE|@getTranslatedString:$MODULE}
		{* crmv@20209 *}
		{if $smarty.request.module eq 'Users' || $smarty.request.module eq 'Administration'}
			{assign var=MODULE value=Users}
			{assign var="MODULELABEL" value=$MODULE|@getTranslatedString:$MODULE}
			{assign var=CATEGORY value=$smarty.request.parenttab}
			<td style="padding-left:10px;padding-right:50px" class="moduleName" nowrap><a class="hdrLink" style="{$minFontSize}" href="index.php?action=index&module=Administration&parenttab={$CATEGORY}">{$MODULELABEL}</a></td>
		{* crmv@30683 *}
		{elseif $smarty.request.module eq 'Settings' || $smarty.request.module eq 'PickList' || $smarty.request.module eq 'Picklistmulti' || $smarty.request.module eq 'com_vtiger_workflow' || $smarty.request.module eq 'Conditionals' || $smarty.request.module eq 'Transitions'}
			{assign var=MODULE value=Settings}
			{assign var="MODULELABEL" value=$MODULE|@getTranslatedString:$MODULE}
			<td style="padding-left:10px;padding-right:50px" class="moduleName" nowrap><a class="hdrLink" style="{$minFontSize}" href="index.php?module=Settings&action=index&parenttab=Settings&reset_session_menu_tab=true">{$MODULELABEL}</a></td>
		{* crmv@30683e *}
		{* crmv@20209e *}
		{elseif $smarty.request.module eq 'Home' && $REQUEST_ACTION eq 'UnifiedSearch'}	
			<td style="padding-left:10px;padding-right:50px" class="moduleName" nowrap><a class="hdrLink" style="{$minFontSize}" href="javascript:;">{'LBL_SEARCH'|@getTranslatedString:'Home'}</a></td>
		{elseif $MENU_LAYOUT.type eq 'modules' || $CATEGORY eq 'Settings'}
			<!-- No List View in Settings - Action is index -->
			<td style="padding-left:10px;padding-right:50px" class="moduleName" nowrap><a class="hdrLink" style="{$minFontSize}" href="index.php?action=index&module={$MODULE}&parenttab={$CATEGORY}">{$MODULELABEL}</a></td>
		{else}
			<td style="padding-left:10px;padding-right:50px" class="moduleName" nowrap>{$APP.$CATEGORY} > <a class="hdrLink" style="{$minFontSize}" href="index.php?action={$action}&module={$MODULE}&parenttab={$CATEGORY}">{$MODULELABEL}</a></td>
		{/if}
	</tr>
	</table>
</div>
{/if}
{* crmv@30356e *}
<div id="Buttons_List_Fixed_Container" style="display:none;">
	<table border=0 cellspacing=0 cellpadding=2 class=small>
	<tr>
		{if $CALENDAR_DISPLAY eq 'true' && $CHECK.Calendar eq 'yes'}
			{if $CATEGORY eq 'Settings' || $CATEGORY eq 'Tools' || $CATEGORY eq 'Analytics'}
				<td><a href="javascript:;" onClick='fnvshobj(this,"miniCal");getMiniCal("parenttab=My Home Page");'><img src="{'btnL3Calendar'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_CALENDAR_ALT}" title="{$APP.LBL_CALENDAR_TITLE}" border=0></a></a></td>
			{else}
				<td><a href="javascript:;" onClick='fnvshobj(this,"miniCal");getMiniCal("parenttab={$CATEGORY}");'><img src="{'btnL3Calendar'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_CALENDAR_ALT}" title="{$APP.LBL_CALENDAR_TITLE}" border=0></a></a></td>
			{/if}
		{/if}
		{if $WORLD_CLOCK_DISPLAY eq 'true'}
			<td><a href="javascript:;"><img src="{'btnL3Clock'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_CLOCK_ALT}" title="{$APP.LBL_CLOCK_TITLE}" border=0 onClick="fnvshobj(this,'wclock');"></a></a></td>
		{/if}
		{if $CALCULATOR_DISPLAY eq 'true'}
			<td><a href="javascript:;"><img src="{'btnL3Calc'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_CALCULATOR_ALT}" title="{$APP.LBL_CALCULATOR_TITLE}" border=0 onClick="fnvshobj(this,'calculator_cont');fetch_calc();"></a></td>
		{/if}
		{if $CHAT_DISPLAY eq 'true'}
			<td><a href="javascript:;" onClick='return window.open("index.php?module=Home&action=vtchat","Chat","width=600,height=450,resizable=1,scrollbars=1");'><img src="{'tbarChat'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_CHAT_ALT}" title="{$APP.LBL_CHAT_TITLE}" border=0></a></td>
		{/if}
		<!-- All Menu -->
		{if $MENU_LAYOUT.type neq 'modules'}
			<td><a href="javascript:;" onmouseout="fninvsh('allMenu');" onClick="fnvshobj(this,'allMenu')"><img src="{'btnL3AllMenu'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_ALL_MENU_ALT}" title="{$APP.LBL_ALL_MENU_TITLE}" border="0"></a></td>
		{/if}
		<td><a href="javascript:;"><img src="{'btnL3Tracker'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_LAST_VIEWED}" title="{$APP.LBL_LAST_VIEWED}" border=0 onClick="fnvshobj(this,'tracker');getLastViewedList();"></a></td>	{* crmv@32429 *}
		{$SDK->getMenuButton('fixed')}	{* crmv@24189 *}
		<td id="composeEmailButton"><img style="cursor:pointer;" title="Email" alt="Email" onclick="window.open('index.php?module=Emails&action=EmailsAjax&file=EditView','_blank');" src="{'themes/'|cat:$THEME|cat:'/images/squirrelmail/crystalline/compose'|cat:$minImg|cat:'.png'}"></td>	{* crmv@31197 *}
		{* crmv@28295 *}
		<td>
			<div style="position:relative;">
				<a href="javascript:;">
					<img id="TodosCheckChangesImg" src="{'todos'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" onclick="fnvshobj(this,'todos');getTodoList();" alt="{'Todos'|getTranslatedString:'ModComments'}" title="{'Todos'|getTranslatedString:'ModComments'}" border=0>
				</a>
				<div id="TodosCheckChangesDiv" class="NotificationDiv" onclick="fnvshobj(this,'todos');getTodoList();">
					<table cellpadding="0" cellspacing="0" border="0"><tr>
						<td><img src="{'notification_left'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" border="0" /></td>
						<td id="TodosCheckChangesDivCount" class="NotificationCount"></td>
						<td><img src="{'notification_right'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" border="0" /></td>
					</tr></table>
				</div>
			</div>
		</td>
		{* crmv@28295e *}
		{* crmv@29079 *}
		{if 'ModComments'|vtlib_isModuleActive}
			<td>
				<div style="position:relative;">
					<a href="javascript:;">
						<img id="ModCommentsCheckChangesImg" src="{'mod_comments'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" alt="{'LBL_MODCOMMENTS_COMMUNICATIONS'|getTranslatedString:'ModComments'}" title="{'LBL_MODCOMMENTS_COMMUNICATIONS'|getTranslatedString:'ModComments'}" border=0 onClick="getModCommentsNews(this);">
					</a>
					<div id="ModCommentsCheckChangesDiv" class="NotificationDiv" onClick="getModCommentsNews(this);">
						<table cellpadding="0" cellspacing="0" border="0"><tr>
							<td><img src="{'notification_left'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" border="0" /></td>
							<td id="ModCommentsCheckChangesDivCount" class="NotificationCount"></td>
							<td><img src="{'notification_right'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" border="0" /></td>
						</tr></table>
					</div>
				</div>
			</td>
		{/if}
		{* crmv@29079e *}
		{* crmv@29617 *}
		<td>
			<div style="position:relative;">
				<a href="javascript:;">
					<img id="ModNotificationsCheckChangesImg" src="{'mod_notifications'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" alt="{'ModNotifications'|getTranslatedString:'ModNotifications'}" title="{'ModNotifications'|getTranslatedString:'ModNotifications'}" border=0 onClick="ModNotificationsCommon.getLastNotifications(this);">
				</a>
				<div id="ModNotificationsCheckChangesDiv" class="NotificationDiv" onClick="ModNotificationsCommon.getLastNotifications(this);">
					<table cellpadding="0" cellspacing="0" border="0"><tr>
						<td><img src="{'notification_left'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" border="0" /></td>
						<td id="ModNotificationsCheckChangesDivCount" class="NotificationCount"></td>
						<td><img src="{'notification_right'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" border="0" /></td>
					</tr></table>
				</div>
			</div>
		</td>
		{* crmv@29617e *}
	</tr>
	</table>
</div>
<div id="Buttons_List_Contestual_Container" style="display:none;">
	<table id="Buttons_List_Contestual_Container_Table" border=0 cellspacing=0
			cellpadding={if $MODULE eq 'Webmails'}
							{php}echo ($_COOKIE['crmvWinMaxStatus'] == 'close' ? "4" : "5");{/php}
						{else}
							{php}echo ($_COOKIE['crmvWinMaxStatus'] == 'close' ? "4" : "6");{/php}
						{/if}
			class=small style="height: {php}echo ($_COOKIE['crmvWinMaxStatus'] == 'close' ? "22" : "55");{/php}; background: url({'Buttons_List_Contestual_Bg'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME});">
	<tr>
		{if $MODULE eq 'Home'}
			<td><img onClick='fnAddWindow(this,"addWidgetDropDown",-4);' onMouseOut='fnRemoveWindow();' src="{'btnL3Add'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" border="0" title="{'LBL_HOME_ADDWINDOW'|getTranslatedString:$MODULE}" alt="{'LBL_HOME_ADDWINDOW'|getTranslatedString:$MODULE}" style="cursor:pointer;"></td>{*crmv@23264*}
		{elseif $CHECK.EditView eq 'yes' || ($MODULE eq 'Projects' && ( $ISPROJECTADMIN eq 'yes' || $ISPROJECTLEADER eq 'yes'))}
			{if $MODULE neq 'Calendar' && $HIDE_BUTTON_CREATE neq true} {* crmv@30014 *}
				<td><a href="index.php?module={$MODULE}&action=EditView&return_action=DetailView&parenttab={$CATEGORY}&folderid={$FOLDERID}"><img src="{'btnL3Add'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_CREATE_BUTTON_LABEL} {$SINGLE_MOD|getTranslatedString:$MODULE}" title="{$APP.LBL_CREATE_BUTTON_LABEL} {$SINGLE_MOD|getTranslatedString:$MODULE}" border=0></a></td> {* crmv@30967 *}
			{/if}
		{/if}
		{* crmv@29386 *}
		{if $MODULE eq 'Webforms'}
			<td><a href="index.php?module={$MODULE}&action=WebformsEditView&return_action=DetailView&parenttab={$CATEGORY}"><img src="{'btnL3Add'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_CREATE_BUTTON_LABEL} {$SINGLE_MOD|getTranslatedString:$MODULE}" title="{$APP.LBL_CREATE_BUTTON_LABEL} {$SINGLE_MOD|getTranslatedString:$MODULE}" border=0></a></td>
		{/if}
		{* crmv@29386e *}
		{if $REQUEST_ACTION eq 'index' || $REQUEST_ACTION eq 'ListView'}
			{* vtlib customization: Hook to enable import/export button for custom modules. Added CUSTOM_MODULE *}
			{if $MODULE eq 'Assets' || $MODULE eq 'ServiceContracts' || $MODULE eq 'Vendors' || $MODULE eq 'HelpDesk' || $MODULE eq 'Contacts' || $MODULE eq 'Leads' || $MODULE eq 'Accounts' || $MODULE eq 'Potentials' || $MODULE eq 'Products'  || $MODULE eq 'Calendar' || $CUSTOM_MODULE eq 'true'} {* crmv@32465 *}
		   		{if $CHECK.Import eq 'yes' && $MODULE neq 'Calendar'}
					<td><a href="index.php?module={$MODULE}&action=Import&step=1&return_module={$MODULE}&return_action=index&parenttab={$CATEGORY}"><img src="{'tbarImport'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_IMPORT} {$APP.$MODULE}" title="{$APP.LBL_IMPORT} {$APP.$MODULE}" border="0"></a></td>
				{elseif  $CHECK.Import eq 'yes' && $MODULE eq 'Calendar'}
					<td><a name='export_link' href="javascript:void(0);" onclick="fnvshobj(this,'CalImport');" ><img src="{'tbarImport'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_IMPORT} {$MODULELABEL}" title="{$APP.LBL_IMPORT} {$MODULELABEL}" border="0"></a></td>	<!-- crmv@16531 -->
				{/if}
				{if $CHECK.Export eq 'yes' && $MODULE neq 'Calendar'}
					<td><a name='export_link' href="javascript:void(0)" onclick="return selectedRecords('{$MODULE}','{$CATEGORY}')"><img src="{'tbarExport'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_EXPORT} {$APP.$MODULE}" title="{$APP.LBL_EXPORT} {$APP.$MODULE}" border="0"></a></td>
				{elseif  $CHECK.Export eq 'yes' && $MODULE eq 'Calendar'}
					<td><a name='export_link' href="javascript:void(0);" onclick="fnvshobj(this,'CalExport');" ><img src="{'tbarExport'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_EXPORT} {$MODULELABEL}" title="{$APP.LBL_EXPORT} {$MODULELABEL}" border="0"></a></td>
				{/if}
			{elseif $MODULE eq 'Documents' && $CHECK.Export eq 'yes' && $REQUEST_ACTION eq 'ListView'} {* crmv@30967 *}
				<td><a name='export_link' href="javascript:void(0)" onclick="return selectedRecords('{$MODULE}','{$CATEGORY}')"><img src="{'tbarExport'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_EXPORT} {$APP.$MODULE}" title="{$APP.LBL_EXPORT} {$APP.$MODULE}" border="0"></a></td>
			{/if}
		{/if}
		<!-- crmv@8719 -->
		{if ($REQUEST_ACTION eq 'index' || $REQUEST_ACTION eq 'ListView') && ($MODULE eq 'Contacts' || $MODULE eq 'Leads' || $MODULE eq 'Accounts'|| $MODULE eq 'Products'|| $MODULE eq 'Potentials'|| $MODULE eq 'HelpDesk'|| $MODULE eq 'Vendors' || $CUSTOM_MODULE eq 'true')}
			{if $CHECK.DuplicatesHandling eq 'yes'}
				<td><a href="javascript:;" onClick="moveMe('mergeDup');mergeshowhide('mergeDup');searchhide('searchAcc','advSearch');"><img src="{'findduplicates'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_FIND_DUPICATES}" title="{$APP.LBL_FIND_DUPLICATES}" border="0"></a></td>
			{/if}
		{/if}
		<!-- crmv@8719e -->
		{if $MODULE eq 'Reports'}
			<td><a href="javascript:;" onclick="gcurrepfolderid=0;fnvshobj(this,'reportLay');"><img src="{'btnL3Add'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" alt="{'LBL_CREATE_REPORT'|@getTranslatedString:$MODULE}" title="{'LBL_CREATE_REPORT'|@getTranslatedString:$MODULE}" border=0></a></td>
			{* crmv@29686 crmv@30967 - removed *}
		{/if}
		{if $MODULE eq 'Home'}
			<td><img onClick='showOptions("changeLayoutDiv");' src="{'orgshar'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" border="0" title="{'LBL_HOME_LAYOUT'|getTranslatedString:$MODULE}" alt="{'LBL_HOME_LAYOUT'|getTranslatedString:$MODULE}" style="cursor:pointer;"></td>
			<td><a href='index.php?module=Users&action=EditView&record={$CURRENT_USER_ID}&scroll=home_page_components&return_module=Home&return_action=index'><img src="{'settingsBox'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_SETTINGS} {$MODULELABEL}" title="{$APP.LBL_SETTINGS} {$MODULELABEL}" border="0"></a></td>
		{/if}
		{* crmv@20209 *}
		{if $MODULE eq 'Calendar' && $REQUEST_ACTION eq 'index'}
			<td id="CalendarAddButton" style="height: {php}echo ($_COOKIE['crmvWinMaxStatus'] == 'close' ? "18" : "32");{/php};"></td>	{* crmv@20480 *}
			{assign var=scroll value="LBL_CALENDAR_CONFIGURATION"|getTranslatedString:"Users"}
			{assign var=scroll value=$scroll|replace:' ':'_'}
			<td><a href='index.php?module=Users&action=EditView&record={$CURRENT_USER_ID}&scroll={$scroll}&return_module=Calendar&return_action=index'><img src="{'tbarSettings'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_SETTINGS} {$MODULELABEL}" title="{$APP.LBL_SETTINGS} {$MODULELABEL}" border="0"></a></td>
		{/if}
		{* crmv@20209e *}
		{* crmv@19842 *}
		{if $MODULE eq 'Webmails'}
			<td id="WebmailsOptionsButton2"></td>
		{/if}
		{* crmv@19842e *}
		{* crmv@20640 *}
		{if $CHECK.moduleSettings eq 'yes' && $IS_ADMIN eq 1}
        	<td><a href='index.php?module=Settings&action=ModuleManager&module_settings=true&formodule={$MODULE}&parenttab=Settings'><img src="{'settingsBox'|cat:$minImg|cat:'.png'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_MODULE_MANAGER}" title="{$APP.LBL_MODULE_MANAGER}" border="0"></a></td>
		{/if}
		{* crmv@20640e *}
		{* crmv@24189 *}
			{* danzi.tn@20130426 *}
			{$SDK->getMenuButton('contestual',$MODULE,'',$CHECK.LBL_BYPRODUCT_BTN)}
			{$SDK->getMenuButton('contestual',$MODULE,$REQUEST_ACTION,$CHECK.LBL_BYPRODUCT_BTN)}
			{* danzi.tn@20130426e *}
		{* crmv@24189e *}
	</tr>
	</table>
</div>
{* crmv@22622e *}
<script type="text/javascript">
jQuery('#Buttons_List_SiteMap').html(jQuery('#Buttons_List_SiteMap_Container').html());jQuery('#Buttons_List_SiteMap_Container').html('');
{if $MENU_LAYOUT.type eq 'modules'}
	//crmv@30356
	{if isMobile()}
		jQuery('#Buttons_List_SiteMap').width(10);
	{else}
		jQuery('#Buttons_List_SiteMap').width(200);
	{/if}
{else}
	{if isMobile()}
		jQuery('#Buttons_List_SiteMap').width(10);
	{else}
		jQuery('#Buttons_List_SiteMap').width(280);
	{/if}
	//crmv@30356e
{/if}

{* crmv@22622 *}
{* crmv@30356 *}
{if $MODULE eq 'Webmails' && isMobile() eq true}

{else}
	jQuery('#Buttons_List_Fixed').html(jQuery('#Buttons_List_Fixed_Container').html());
	jQuery('#Buttons_List_Fixed_Container').html('');
	jQuery('#Buttons_List_QuickCreate').show();
{/if}
{* crmv@30356e *}
contestual_menu = jQuery('#Buttons_List_Contestual_Container').html();
jQuery('#Buttons_List_Contestual').html(contestual_menu);jQuery('#Buttons_List_Contestual_Container').html('');
//crmv@20445
if ((contestual_menu.indexOf('IMG') != -1) || (contestual_menu.indexOf('img') != -1)) {ldelim}
	jQuery('#Buttons_List_Contestual_BgSx').show();
	jQuery('#Buttons_List_Contestual_BgDx').show();
	jQuery('#Buttons_List_Contestual').show();
{rdelim}
//crmv@20445e
jQuery('#vte_menu_white').height(jQuery('#vte_menu').height());

{if $MODULE eq 'Webmails'}
	jQuery('#Buttons_List_Contestual_BgSx').hide();
	jQuery('#Buttons_List_Contestual_BgDx').hide();
	jQuery('#Buttons_List_Contestual').hide();
{/if}
{* crmv@22622 e *}
var menubar = '{php}echo $_SESSION['menubar'];{/php}';
{literal}
jQuery('.level2Bg img').live('mouseover mouseout', function(event) {
	if (getCookie('crmvWinMaxStatus') != 'close' || menubar != 'no') {	//crmv@23715
		if (event.type == 'mouseover') {
			if (jQuery(this).attr('title') != '')
		    	var title = jQuery(this).attr('title');
		    else
		    	var title = jQuery(this).attr('title1');
		    if (title == '' || title == undefined) return false;

		    jQuery('#menu_tooltip_text').html(title);
		    jQuery(this).attr('title1',title);
		    jQuery(this).attr('title','');

			jQuery('#menu_tooltip').width('10');
		    var position = jQuery(this).offset();
		    jQuery('#menu_tooltip').width(jQuery('#menu_tooltip_text').width()+2);
		    //jQuery('#menu_tooltip').css('left',position.left+(jQuery(this).width()/2)-(jQuery('#menu_tooltip').width()/2));
		    jQuery('#menu_tooltip').css('left',position.left);
		    //crmv@23715
		    if (menubar == 'no') {
		    	jQuery('#menu_tooltip').css('top',8);
			}
			//crmv@23715e
		    jQuery('#menu_tooltip').show();
		} else {
			jQuery('#menu_tooltip').hide();
		}
	}
});
{/literal}
NotificationsCommon.showChanges('CheckChangesDiv','CheckChangesImg','ModComments,ModNotifications,Todos');	{* crmv@29079 *}	{* crmv@29617 *}	{* crmv@28295 *}
</script>
<!-- crmv@18549e crmv@19842e -->
