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
<!-- danzi.tn@20160104 passaggio in produzione albero utenti -->
<link rel="stylesheet" type="text/css" media="all" href="jscalendar/calendar-win2k-cold-1.css">
{$DATE_JS}
<script type="text/javascript" src="jscalendar/calendar.js"></script>
<script type="text/javascript" src="jscalendar/lang/calendar-{$APP.LBL_JSCALENDAR_LANG}.js"></script>
<script type="text/javascript" src="jscalendar/calendar-setup.js"></script>
<script language="JavaScript" type="text/javascript" src="include/calculator/calc.js"></script>
<script language="JavaScript">
var js_dateformat = '{$JS_DATEFORMAT}';

</script>

{if $smarty.request.ajax neq ''}
&#&#&#{$ERROR}&#&#&#
{/if}
{if $HIDE_CUSTOM_LINKS eq 1}
<script language="JavaScript" type="text/javascript" src="include/js/ListView.js"></script>
<div id="ListViewContents">
{else}
                {*<!-- crmv@18592 -->*}
                <div id="Buttons_List_3_Container" style="display:none;">
                <table id="bl3" border=0 cellspacing=0 cellpadding=2 width=100% class="small">{*crmv@22259*}
                <tr>
                <!-- Buttons -->
                <td style="padding:5px" nowrap>

                                {* crmv@vte10usersFix *}
                				{if $MODULE eq 'Calendar'}
                					<input class="crmbutton small edit" type="button" value="{$MOD.LBL_DAY}" onclick="listToCalendar('Today')"/>
                					<input class="crmbutton small edit" type="button" value="{$MOD.LBL_WEEK}" onclick="listToCalendar('This Week')"/>
                					<input class="crmbutton small edit" type="button" value="{$MOD.LBL_MON}" onclick="listToCalendar('This Month')"/>
                					<input class="crmbutton small edit" type="button" value="{$MOD.LBL_CAL_TO_FILTER}"/>
                				{/if}
                				{* crmv@vte10usersFix e *}

                                 {foreach key=button_check item=button_label from=$BUTTONS}
                                 		{* crmv@30967 *}
                                 		{if $button_check eq 'back'}
                             	    		{if $FOLDERID > 0}
												<a href="index.php?module={$MODULE}&action=index"><img src="{'folderback.png'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_GO_BACK}" title="{$APP.LBL_GO_BACK}" align="absbottom" border="0" /></a>&nbsp;
											{else}
												<input class="crmbutton small edit" type="button" value="{$APP.LBL_FOLDERS}" onclick="location.href='index.php?module={$MODULE}&action=index';" />
											{/if}
                                        {elseif $button_check eq 'del'}
                                        {* crmv@30967e *}
                                             <input class="crmbutton small delete" type="button" value="{$button_label}" onclick="return massDelete('{$MODULE}')"/>
                                        {elseif $button_check eq 's_mail'}
                                             <input class="crmbutton small edit" type="button" value="{$button_label}" onclick="return eMail('{$MODULE}',this);"/>
                                        <!-- //crmv@7216 -->
                                        {elseif $button_check eq 's_fax'}
											<input class="crmbutton small edit" type="button" value="{$button_label}" onclick="return Fax('{$MODULE}',this);"/>
										<!-- //crmv@7216e -->
                                        <!-- //crmv@7217 -->
                                        {elseif $button_check eq 's_sms'}
											<input class="crmbutton small edit" type="button" value="{$button_label}" onclick="return Sms('{$MODULE}',this);"/>
										<!-- //crmv@7217e -->
                    {elseif $button_check eq 's_cmail'}
                                             <input class="crmbutton small edit" type="submit" value="{$button_label}" onclick="return massMail('{$MODULE}')"/>
                                        {elseif $button_check eq 'c_status'}
                                             <input class="crmbutton small edit" type="button" value="{$button_label}" onclick="return change(this,'changestatus')"/>
                    {elseif $button_check eq 'mailer_exp'}
                                             <input class="crmbutton small edit" type="submit" value="{$button_label}" onclick="return mailer_export()"/>
<!--//crmv@9183                                                -->
                     {elseif $button_check eq 'mass_edit'}
                           <input class="crmbutton small edit" type="button" value="{$button_label}" onclick="return mass_edit(this, 'massedit', '{$MODULE}', '{$CATEGORY}')"/>
                           	<div id="massedit" class="layerPopup crmvDiv" style="display:none;z-index:21;">	{*<!-- crmv@18592 -->*}
								<table border="0" cellpadding="5" cellspacing="0" width="100%">
								<tr height="34">
									<td style="padding:5px" class="level3Bg">
										<table cellpadding="0" cellspacing="0" width="100%">
										<tr>
											<td width="80%"><b>{$APP.LBL_MASSEDIT_FORM_HEADER}</b></td>
											<td width="20%" align="right">
												<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="crmbutton small save" onclick="jQuery('#massedit_form input[name=action]').val('MassEditSave'); if (massEditFormValidate()) jQuery('#massedit_form').submit();" type="submit" name="button" value="  {$APP.LBL_SAVE_BUTTON_LABEL}  " style="width:70px" >
											</td>
										</tr>
										</table>
									</td>
								</tr>
								</table>
								<div id="massedit_form_div"></div>
								<div class="closebutton" onClick="fninvsh('massedit');"></div>
							</div>
                     {/if}
<!--//crmv@9183e                                                -->
                                 {/foreach}
                 {if ($ALL_IDS eq 1)}
                    <input class="crmbutton small edit" id="select_all_button_top" {if $AJAX neq 'true'} style="display:none;"{/if}  type="button" value="{$APP.LBL_UNSELECT_ALL_IDS}" onClick="selectAllIds();"/>
                 {else}
                    <input class="crmbutton small edit" id="select_all_button_top" {if $AJAX neq 'true'} style="display:none;"{/if}  type="button" value="{$APP.LBL_SELECT_ALL_IDS}" onClick="selectAllIds();"/>
                 {/if}

                {* vtlib customization: Custom link buttons on the List view basic buttons *}
				{if $CUSTOM_LINKS && $CUSTOM_LINKS.LISTVIEWBASIC}
					{foreach item=CUSTOMLINK from=$CUSTOM_LINKS.LISTVIEWBASIC}
						{assign var="customlink_href" value=$CUSTOMLINK->linkurl}
						{assign var="customlink_label" value=$CUSTOMLINK->linklabel}
						{if $customlink_label eq ''}
							{assign var="customlink_label" value=$customlink_href}
						{else}
							{* Pickup the translated label provided by the module *}
							{assign var="customlink_label" value=$customlink_label|@getTranslatedString:$CUSTOMLINK->module()}
						{/if}
						<input class="crmbutton small edit" type="button" value="{$customlink_label}" onclick="{$customlink_href}" />
					{/foreach}
				{/if}

				{* vtlib customization: Custom link buttons on the List view *}
				{if $CUSTOM_LINKS && !empty($CUSTOM_LINKS.LISTVIEW)}
					&nbsp;
					<a href="javascript:;" onmouseover="fnvshobj(this,'vtlib_customLinksLay');" onclick="fnvshobj(this,'vtlib_customLinksLay');">
							<b>{$APP.LBL_MORE} {$APP.LBL_ACTIONS} <img src="{'arrow_down.gif'|@vtiger_imageurl:$THEME}" border="0"></b>
					</a>
					<div class="drop_mnu" style="display: none; left: 193px; top: 106px;width:155px; position:absolute;" id="vtlib_customLinksLay"
						onmouseout="fninvsh('vtlib_customLinksLay')" onmouseover="fnvshNrm('vtlib_customLinksLay')">
						<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr><td style="border-bottom: 1px solid rgb(204, 204, 204); padding: 5px;"><b>{$APP.LBL_MORE} {$APP.LBL_ACTIONS} &#187;</b></td></tr>
						<tr>
							<td>
								{foreach item=CUSTOMLINK from=$CUSTOM_LINKS.LISTVIEW}
									{assign var="customlink_href" value=$CUSTOMLINK->linkurl}
									{assign var="customlink_label" value=$CUSTOMLINK->linklabel}
									{if $customlink_label eq ''}
										{assign var="customlink_label" value=$customlink_href}
									{else}
										{* Pickup the translated label provided by the module *}
										{assign var="customlink_label" value=$customlink_label|@getTranslatedString:$CUSTOMLINK->module()}
									{/if}
									<a href="{$customlink_href}" class="drop_down">{$customlink_label}</a>
								{/foreach}
							</td>
						</tr>
						</table>
					</div>
				{/if}
				{* END *}
                </td>

				{* crmv@31245*}
				<td align="right" width="100%">
					<form id="basicSearch" name="basicSearch" method="post" action="index.php" onSubmit="return callSearch('Basic', '{$FOLDERID}');">
						<input type="hidden" name="searchtype" value="BasicSearch" />
                        <input type="hidden" name="module" value="{$MODULE}" />
                        <input type="hidden" name="parenttab" value="{$CATEGORY}" />
            			<input type="hidden" name="action" value="index" />
                        <input type="hidden" name="query" value="true" />
            			<input type="hidden" id="basic_search_cnt" name="search_cnt" />

            			<table class="crmButton" style="background-color:#fff" cellspacing="0" cellpadding="0" border="0">
            				<tr>
            					<td style="padding-left:5px;"><input type="text" class="searchBox" id="basic_search_text" name="search_text" value="{$APP.LBL_SEARCH_TITLE}{$MODULE|getTranslatedString:$MODULE}" onclick="clearText(this)" onblur="restoreDefaultText(this, '{$APP.LBL_SEARCH_TITLE}{$MODULE|getTranslatedString:$MODULE}')" /></td>
            					<td width="20" align="right" valign="bottom">
            						<img id="basic_search_icn_canc" style="display:none" border="0" alt="Reset" title="Reset" style="cursor:pointer" onclick="cancelSearchText('{$APP.LBL_SEARCH_TITLE}{$MODULE|getTranslatedString:$MODULE}')" src="{'close_little.png'|@vtiger_imageurl:$THEME}" />&nbsp;
            					</td>
            					<td style="padding-right:5px;">
            						<img id="basic_search_icn_go" border="0" alt="{$APP.LBL_FIND}" title="{$APP.LBL_FIND}" style="cursor:pointer" onclick="jQuery('#basicSearch').submit();" src="{'UnifiedSearchButton.png'|@vtiger_imageurl:$THEME}" />
            					</td>
            				</tr>
            			</table>
					</form>
				</td>
				<td align="right">
					<input type="button" class="crmbutton small create" onclick="jQuery('#advSearch').toggle();updatefOptions(document.getElementById('Fields0'), 'Condition0');" value="{$APP.LNK_ADVANCED_SEARCH}" />
				</td>
				{* crmv@31245e crmv@22259e  *}
			</tr>
            </table>
            </div>
            <script type="text/javascript">calculateButtonsList3();</script>

{/if}
<form name="massdelete" method="POST" id="massdelete" onsubmit="VtigerJS_DialogBox.block();">
     <input name='search_url' id="search_url" type='hidden' value='{$SEARCH_URL}'>
     {if $HIDE_CUSTOM_LINKS eq 1}
      <input id="modulename" name="modulename" type="hidden" value="{$MODULE}">
     {/if}
     <input name="change_owner" type="hidden">
     <input name="change_status" type="hidden">
     <input name="action" type="hidden">
     <input name="where_export" type="hidden" value="{php} echo to_html($_SESSION['export_where']);{/php}">
     <input name="step" type="hidden">
<!-- //crmv@9183  -->
     <input name="selected_ids" type="hidden" id="selected_ids" value="{$SELECTED_IDS}">
     <input name="all_ids" type="hidden" id="all_ids" value="{$ALL_IDS}">
     <input name="import_flag" type="hidden" id="import_flag" value="{$HIDE_CUSTOM_LINKS}">
<!-- //crmv@9183 e -->
                <!-- List View Master Holder starts -->
                <table border=0 cellspacing=1 cellpadding=0 width=100% class="lvtBg">
                <tr>
                <td>
                <!-- List View's Buttons and Filters starts -->

            {*<!-- crmv@18592e -->*}
            {* crmv@21723 *}
			{if $HIDE_CUSTOM_LINKS neq '1'}
				<div class="drop_mnu" id="customLinks" onmouseover="fnShowDrop('customLinks');" onmouseout="fnHideDrop('customLinks');" style="width:150px;">
					<table cellspacing="0" cellpadding="0" border="0" width="100%">
						{* crmv@22259 *}
						{if $ALL eq 'All'}
							<tr>
								<td><a class="drop_down" href="index.php?module={$MODULE}&action=CustomView&duplicate=true&record={$VIEWID}&parenttab={$CATEGORY}">{$APP.LNK_CV_DUPLICATE}</a></td>
							</tr>
							<tr>
								<td><a class="drop_down" href="index.php?module={$MODULE}&action=CustomView&parenttab={$CATEGORY}">{$APP.LNK_CV_CREATEVIEW}</a></td>
							</tr>
					    {else}
							{if $CV_EDIT_PERMIT eq 'yes'}
								<tr>
									<td><a class="drop_down" href="index.php?module={$MODULE}&action=CustomView&record={$VIEWID}&parenttab={$CATEGORY}">{$APP.LNK_CV_EDIT}</a></td>
								</tr>
							{/if}
							<tr>
								<td><a class="drop_down" href="index.php?module={$MODULE}&action=CustomView&duplicate=true&record={$VIEWID}&parenttab={$CATEGORY}">{$APP.LNK_CV_DUPLICATE}</a></td>
							</tr>
							{if $CV_DELETE_PERMIT eq 'yes'}
								<tr>
									<td><a class="drop_down" href="javascript:confirmdelete('index.php?module=CustomView&action=Delete&dmodule={$MODULE}&record={$VIEWID}&parenttab={$CATEGORY}')">{$APP.LNK_CV_DELETE}</a></td>
								</tr>
							{/if}
							{if $CUSTOMVIEW_PERMISSION.ChangedStatus neq '' && $CUSTOMVIEW_PERMISSION.Label neq ''}
								<tr>
							   		<td><a class="drop_down" href="#" id="customstatus_id" onClick="ChangeCustomViewStatus({$VIEWID},{$CUSTOMVIEW_PERMISSION.Status},{$CUSTOMVIEW_PERMISSION.ChangedStatus},'{$MODULE}','{$CATEGORY}')">{$CUSTOMVIEW_PERMISSION.Label}</a></td>
							   	</tr>
							{/if}
							<tr>
								<td><a class="drop_down" href="index.php?module={$MODULE}&action=CustomView&parenttab={$CATEGORY}">{$APP.LNK_CV_CREATEVIEW}</a></td>
							</tr>
					    {/if}
					    {* crmv@22259e *}
					</table>
				</div>
			{/if}
			{* crmv@21723 e *}

			<table width="100%" >

			{* crmv@30967 *}
			{if $FOLDERID > 0}
				<tr style="margin:2px">
					<td colspan="3"><span class="dvHeaderText">{$APP.LBL_FOLDER}: {$FOLDERINFO.foldername}</span></td>
				</tr>
			{/if}
			{* crmv@30967e *}

			<tr>
<!--			//crmv@10759-->
			{* crmv@21723 *}
			<td id="rec_string" align="left" width="33%" class="small" nowrap>{$RECORD_COUNTS}</td>
			<td id="nav_buttons" align="center" width="33%" style="padding:5px;">{$NAVIGATION}</td>
			{* crmv@31245 *}
			<td width="33%" align="right">
		        <!-- Filters -->
                <table border=0 cellspacing=0 cellpadding=0 class="small"><tr>
									<!-- danzi.tn@20150922 filtro per stato danzi.tn@20150825 -->
									<td>

									<table class="crmButton" style="background-color:#fff" cellspacing="0" cellpadding="0" border="0">
											<tr>
													<td style="padding-left:5px;">
															<input type="text" class="small" style="border: none; width: 20px;" id="selected_country" name="selected_country_code" value="{$SELECTED_COUNTRY}" readonly> -
													</td>
													<td style="padding-left:5px;">
															<input type="text" style="width:160px;" class="searchBox" id="selected_agent_ids_display" name="search_agent_display" value="{$SELECTED_AGENT_IDS_DISPLAY}" onclick="clearSelectedAgents(this)" readonly>
															<input type="hidden" id="selected_agent_ids" name="search_agent_ids" value="{$SELECTED_AGENT_IDS}">
													</td>
													<td width="20" align="right" valign="bottom">
															<img id="agent_search_icn_canc" style="display:none" border="0" alt="Reset" title="Reset" style="cursor:pointer" onclick="cancelSearchAgents('')" src="{'close_little.png'|@vtiger_imageurl:$THEME}" />&nbsp;
													</td>
													<td style="padding-right:5px;">
															<span id="user_search">
																	<img id="agent_search_icn_go" border="0" alt="{$APP.LBL_FIND}" title="{$APP.LBL_FIND}" style="cursor:pointer"  src="{'UnifiedSearchButton.png'|@vtiger_imageurl:$THEME}" onclick="open_tree_container();" />
															</span>
													</td>
											</tr>
									</table>

									</td>
									<!-- danzi.tn@20150825e -->
                    {* crmv@22259 *}
                    <td style="padding-left:5px;padding-right:5px">{$APP.LBL_VIEW}&nbsp;<SELECT NAME="viewname" id="viewname" class="small" onchange="showDefaultCustomView(this,'{$MODULE}','{$CATEGORY}', '{$FOLDERID}')">{$CUSTOMVIEW_OPTION}</SELECT></td> {* crmv@30967 *}
					{* crmv@21723 crmv@21827 crmv@22622 *}
					{if $HIDE_CUSTOM_LINKS neq '1'}
						<td onmouseover="fnDropDown(this,'customLinks',-18);" onmouseout="fnHideDrop('customLinks');" nowrap><!-- <a href="javascript:void(0);" style="text-decoration:none;"><img id="filter_option_img" src="{'gear_off.png'|@vtiger_imageurl:$THEME}" border=0 title="{$APP.LBL_FILTER_OPTIONS}"></a>-->&nbsp;</td>
					{/if}
					{* crmv@21723e crmv@21827e crmv@22622e *}
					{* crmv@29617 *}
					<!-- <td><img id="followImgCV" title="{'LBL_FOLLOW'|getTranslatedString:'ModNotifications'}" src="{$VIEWID|@getFollowImg:'customview'}" style="cursor: pointer;" align="top" onClick="ModNotificationsCommon.followCV();"/></td> -->
					{* crmv@29617e *}
					{* crmv@7634 *}
					{if $OWNED_BY eq 0}
						<td style="padding:5px" nowrap>{$APP.LBL_ASSIGNED_TO}:&nbsp;{$LV_USER_PICKLIST}</td>
					{/if}
					{* crmv@7634e *}
				</tr></table>
			</td>
			{* crmv@31245e crmv@21723e *}

<!--			//crmv@10759 e-->
			{if $HIDE_CUSTOM_LINKS neq '1'}
				</tr>
				</table>
			<!-- Filters  END-->
			{/if}
            <!-- List View's Buttons and Filters ends -->

<!--			//danzi.tn@20130207-->
            <!-- List View's byproduct Filter starts-->
	    <div id="byproduct">
	    <table width=100% cellspacing=1 cellpadding=3 border=0 class="small">
		<tbody>
		<tr>
			<td>
				<form name="filter-byproduct-form" action="index.php" method="POST">

				<table width='100%' class='small'>
				<tbody>
					<tr style='height:25px'>
						<td class='dvtCellLabel' align='right'>
							<span style='font-weight: bold ; font-size: 110%'> {$MOD.LBL_VALUEFROM}:</span>
						</td>
						<td class='dvtCellInfo'>
							<select id="stdValueFilterField" name="stdValueFilterField" class="select small" onchange="updateValueFilterContainer(this);">
							{foreach item=stdfilter key=type_id from=$STDVALUEFILTERS}
								<option {$stdfilter.selected} value={$type_id}>{$stdfilter.text}</option>
							{/foreach}
						        </select>
							<span id="stdValueFilterFieldAnchor"><a href="#">...</a></span>
						</td>
						<td class='dvtCellLabel' align='right'>
							<span style='font-weight: bold ; font-size: 110%'>{$MOD.LBL_VALUEFILTER}:</span>
						</td>
						<td class='dvtCellInfo'>
							<span id='valueFilterContainer'>
								<input  class='small'  type='text' name='valueId' id='valueId' value='{$valueIdValue}' maxlength='50' size='12'  />
							</span>
						</td>
						<td class='dvtCellInfo'>
							&nbsp;
						</td>
					</tr>
					<tr style='height:25px'>
						<td class='dvtCellLabel' align='right'>
							<span style='font-weight: bold ; font-size: 110%'>{$MOD.LBL_START_DATE} :</span>
						</td>
						<td class='dvtCellInfo' align='left'>
							 <input name="startdate" id="jscal_field_proddate_start" type="text" size="10" class="textField" value="{$STARTDATE}" {$msg_style}>
							<img src="{$IMAGE_PATH}calendar.gif" id="jscal_trigger_proddate_start" style={$img_style}>
							<font size=1><em old="(yyyy-mm-dd)">({$DATEFORMAT})</em></font>
							<script type="text/javascript">
								Calendar.setup ({ldelim}
								inputField : "jscal_field_proddate_start", ifFormat : "{$JS_DATEFORMAT}", showsTime : false, button : "jscal_trigger_proddate_start", singleClick : true, step : 1
								{rdelim})
							</script>
						</td>
						<td class='dvtCellLabel' align='right'>
							<span style='font-weight: bold ; font-size: 110%'>{$MOD.LBL_END_DATE} :</span>
						</td>
						<td class='dvtCellInfo' align='left'>
							<input name="enddate" {$msg_style} id="jscal_field_proddate_end" type="text" size="10" class="textField" value="{$ENDDATE}">
							<img src="{$IMAGE_PATH}calendar.gif" id="jscal_trigger_proddate_end" style={$img_style}>
							<font size=1><em old="(yyyy-mm-dd)">({$DATEFORMAT})</em></font>
							<script type="text/javascript">
								Calendar.setup ({ldelim}
								inputField : "jscal_field_proddate_end", ifFormat : "{$JS_DATEFORMAT}", showsTime : false, button : "jscal_trigger_proddate_end", singleClick : true, step : 1
								{rdelim})
							</script>
						</td>
						<td class='dvtCellInfo'>
								<div id="cat_prodotti" title="{$MOD.LBL_CAT}">
								<div id='categorytree'>
									{$PRODUCT_CATEGORY_TREE}
								</div>
								</div>

						</td>
					</tr>
					<tr style='height:25px'>
						<td class='dvtCellLabel' align='right'>
							<span style='font-weight: bold ; font-size: 110%'><label for="slider-range">{$MOD.LBL_MYFILTER_VALUE}:</label></span>
						</td>
						<td class='dvtCellInfo'>


							<div id="slider-range" lang="it_it"></div>
						</td>
						<td class='dvtCellLabel' align='right'>
							<span style='font-weight: bold ; font-size: 110%'><label for="amount">{$MOD.LBL_MAGG_DI}:</label></span>
						</td>
						<td class='dvtCellInfo'>
							<p>
								<input type="text" id="amount" style="border: 0; color: #f6931f; font-weight: bold;" />
							</p>
						</td>
						<td class='dvtCellInfo'>
							&nbsp;<a class="button" href="#" onclick="showDefaultCustomView(null,'{$MODULE}','{$CATEGORY}', '{$FOLDERID}');return false;">{$MOD.LBL_SHOW_ITEMS}</a>
						</td>
					</tr>
				</tbody>
				</table>
				<input type="hidden" name="module" value="Accounts"/>
				<!-- <input type="hidden" name="action" value="ListViewByProduct"/> -->
				<input type="hidden" id="amount_value" name="amount_value" value="{$amountrangevalue}"/>
				<input type="hidden" name="parenttab" value="Sales"/>
				</form>
			</td>
		</tr>
		</tbody>
	    </table>
	    </div>
<!--			//danzi.tn@20130207 e -->
            <!-- List View's byproduct Filter ends-->
            <div  >
            <table border=0 cellspacing=1 cellpadding=3 width=100% class="lvt small">
            <!-- Table Headers -->
            <tr>
             <!-- DS-ED VlMe 27.3.2008 -->
            <td class="lvtCol"><input type="checkbox" id="selectall" name="selectall" onClick="select_all_page(this.checked,this.form);"></td> <!-- //ds@1s -->
             <!-- DS-END -->
                 {foreach name="listviewforeach" item=header from=$LISTHEADER}
             <td class="lvtCol">{$header}</td>
                {/foreach}
            </tr>
            <!-- Table Contents -->
            {foreach item=entity key=entity_id from=$LISTENTITY}
					<!-- crmv@7230 -->
					{assign var=color value=$entity.clv_color}
					<tr bgcolor=white onMouseOver="this.className='lvtColDataHover'" onMouseOut="this.className='lvtColData'" id="row_{$entity_id}">
					 <!-- DS-ED VlMe 27.3.2008 -->
					 {* <!-- KoKr bugfix add (check_object) idlist for csv export --> *}
					<td width="2%"><input type="checkbox" name="selected_id" id="{$entity_id}" value="{$entity_id}" onClick="update_selected_ids(this.checked,'{$entity_id}',this.form,true);"
					{if count($SELECTED_IDS_ARRAY) > 0}
						{if $ALL_IDS eq 1 && !in_array($entity_id,$SELECTED_IDS_ARRAY)}
							checked
						{else}
							{if ($ALL_IDS neq 1 and $SELECTED_IDS neq "" and in_array($entity_id,$SELECTED_IDS_ARRAY))}
								checked
							{/if}
						{/if}
					{else}
						{if $ALL_IDS eq 1}
							checked
						{/if}
					{/if}
					></td>
					 <!-- DS-END -->
					{foreach key=colname item=data from=$entity}
					{if $colname neq 'clv_color' or $colname eq '0'}
							<td bgcolor="{$color}">{$data}</td>
					{/if}
					{/foreach}
				</tr>
				<!-- crmv@7230e -->
            {foreachelse}
            <tr><td style="background-color:#ffffff;height:340px" align="center" colspan="{$smarty.foreach.listviewforeach.iteration+1}">
            <div style="border: 1px solid rgb(246, 249, 252); background-color: rgb(255, 255, 255); width: 45%; position: relative;">	<!-- crmv@18592 -->
                {assign var=vowel_conf value='LBL_A'}
                {if $CHECK.EditView eq 'yes' && $MODULE neq 'Emails' && $MODULE neq 'Webmails'}

                <table border="0" cellpadding="5" cellspacing="0" width="98%">
                <tr>
                    <td rowspan="2" width="25%"><img src="{$IMAGE_PATH}empty.jpg" height="60" width="61"></td>
                    <td class="small" align="left" style="border-bottom: 1px solid rgb(204, 204, 204);" nowrap="nowrap" width="75%"><span class="genHeaderSmall">
                    <!-- crmv@10453 -->
                    {$APP.LBL_NO_M} {$APP.LBL_RECORDS} {$APP.LBL_FOUND} !
                    <!-- crmv@10453e -->
                    </span></td>
                </tr>
                {if $MODULE neq 'Charts'} {* crmv@30967 *}
                <tr>
                    <td class="small" align="left" nowrap="nowrap">{$APP.LBL_YOU_CAN_CREATE} {$APP.$vowel_conf}
                    {$APP.LBL_RECORDS} {$APP.LBL_NOW}, {$APP.LBL_CLICK_THE_LINK}:<br>
                    {if $MODULE neq 'Calendar'}
                      &nbsp;&nbsp;-<a href="index.php?module={$MODULE}&action=EditView&return_action=DetailView&parenttab={$CATEGORY}">{$APP.LBL_CREATE}
                    {"SINGLE_"|cat:$MODULE|@getTranslatedString:$MODULE}
                    </a><br>
                    {else}
                    &nbsp;&nbsp;-<a href="index.php?module={$MODULE}&amp;action=EditView&amp;return_module=Calendar&amp;activity_mode=Events&amp;return_action=DetailView&amp;parenttab={$CATEGORY}">{$APP.LBL_CREATE} {$APP.LBL_AN} {$APP.Event}</a><br>
                    &nbsp;&nbsp;-<a href="index.php?module={$MODULE}&amp;action=EditView&amp;return_module=Calendar&amp;activity_mode=Task&amp;return_action=DetailView&amp;parenttab={$CATEGORY}">{$APP.LBL_CREATE} {$APP.LBL_A} {$APP.Task}</a>
                    {/if}
                    </td>
                </tr>
                {/if}
                </table>
                    {else}
                <table border="0" cellpadding="5" cellspacing="0" width="98%">
                <tr>
                <td rowspan="2" width="25%"><img src="{$IMAGE_PATH}denied.gif"></td>
                <td style="border-bottom: 1px solid rgb(204, 204, 204);" nowrap="nowrap" width="75%"><span class="genHeaderSmall">
                {$APP.LBL_NO_M} {$APP.LBL_RECORDS} {$APP.LBL_FOUND} !
                </tr>
                <tr>
                <td class="small" align="left" nowrap="nowrap">{$APP.LBL_YOU_ARE_NOT_ALLOWED_TO_CREATE} {$APP.$vowel_conf} {$APP.LBL_RECORDS}
                <br>
                </td>
                </tr>
                </table>
                {/if}
                </div>
                </td></tr>
                 {/foreach}
             </table>
             </div>

            <table width=100% >
			<tr>
<!--			//crmv@10759-->
{*<!-- crmv@18592 -->*}
			<td id="rec_string2" align="left" class="small" width="33%" nowrap>{$RECORD_COUNTS}</td>
			<td id="nav_buttons2" align="center" style="padding:5px;" width="33%">{$NAVIGATION}</td>
			<td width="33%">
				{if $HIDE_CUSTOM_LINKS neq '1'}
				{*<!-- crmv@18592 -->*}
             	<table border=0 cellspacing=0 cellpadding=2 width=100%>
                  <tr>
					<td align="right" width=100%>
                   		<table border=0 cellspacing=0 cellpadding=0 class="small">
                    	<tr>
                        	{$WORDTEMPLATEOPTIONS}{$MERGEBUTTON}
                    	</tr>
                   		</table>
                   	</td>
                   </tr>
				</table>
				{/if}
				{*<!-- crmv@18592e -->*}
			</td>
			</tr>
			<tr>
				<td align="center" colspan="3" width="33%" nowrap>
					{$APP.LBL_LIST_SHOW}&nbsp;<SELECT NAME="counts" id="counts" class="small" onchange="showMoreEntries(this,'{$MODULE}', '{$FOLDERID}')">{$CUSTOMCOUNTS_OPTION}</SELECT> {$APP.LBL_ELEMENTS} {* crmv@30967 crmv@31245 *}
				</td>
			</tr>
			</table>
            <!-- List View's Buttons and Filters ends -->
<!--			//crmv@10759 e-->
               </td>
           </tr>
        </table>
   </form>
{$SELECT_SCRIPT}
<div id="basicsearchcolumns" style="display:none;"><select name="search_field" id="bas_searchfield" class="txtBox" style="width:150px">{html_options  options=$SEARCHLISTHEADER}</select></div>
{if $HIDE_CUSTOM_LINKS eq 1}
</div>
{/if}
<script type="text/javascript">
{*<!-- crmv@18592 -->*}
function unselectAllIds()
{ldelim}
   var button_top = document.getElementById("select_all_button_top");
   button_top.value = "{$APP.LBL_SELECT_ALL_IDS}";
{rdelim}


function selectAllIds()
{ldelim}
   var button_top = document.getElementById("select_all_button_top");
   var choose_id = document.getElementById("select_ids");

   if (button_top.value == "{$APP.LBL_SELECT_ALL_IDS}")
   {ldelim}

      button_top.value = "{$APP.LBL_UNSELECT_ALL_IDS}";
      //crmv@7216
      document.getElementById("all_ids").value = 1;
      document.getElementById("selected_ids").value = '';
	  //crmv@7216e
      document.getElementById("selectall").checked=true;

  	if (isdefined("selected_id")){ldelim}
	      if (typeof(getObj("selected_id").length)=="undefined")
	      {ldelim}
	             getObj("selected_id").checked=true;
	          {rdelim} else {ldelim}
	         for (var i=0;i<getObj("selected_id").length;i++){ldelim}
	                    getObj("selected_id")[i].checked=true;
	         {rdelim}
	      {rdelim}
  	{rdelim}

   {rdelim} else {ldelim}
      button_top.value = "{$APP.LBL_SELECT_ALL_IDS}";
      choose_id.value = "";
      //crmv@7216
      document.getElementById("all_ids").value = '';
	  document.getElementById("selected_ids").value="";
	  //crmv@7216e
      document.getElementById("selectall").checked=false;

      if (typeof(getObj("selected_id").length)=="undefined")
      {ldelim}
         getObj("selected_id").checked=false;
          {rdelim} else {ldelim}
         for (var i=0;i<getObj("selected_id").length;i++){ldelim}
                    getObj("selected_id")[i].checked=false;
         {rdelim}
            {rdelim}
   {rdelim}
{rdelim}
//crmv@10759	//crmv@16627
jQuery(document).ready(
	update_navigation_values(window.location.href,'{$MODULE}')
);
//crmv@10759e	//crmv@16627e
{*<!-- crmv@18592e -->*}
</script>
