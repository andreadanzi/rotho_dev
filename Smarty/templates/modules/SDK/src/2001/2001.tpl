{***************************************************************************************
 * The contents of this file are subject to the CRMVILLAGE.BIZ VTECRM License Agreement
 * ("licenza.txt"); You may not use this file except in compliance with the License
 * The Original Code is:  CRMVILLAGE.BIZ VTECRM
 * The Initial Developer of the Original Code is CRMVILLAGE.BIZ.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
 ***************************************************************************************}
{* crmv@36171 *}

{if $sdk_mode eq 'detail' && $keyreadonly < 99}
	{assign var="enable_product_cat_dialog" value=true}
{elseif $sdk_mode eq 'edit' && $readonly < 99}
	{assign var="enable_product_cat_dialog" value=true}
{else}
	{assign var="enable_product_cat_dialog" value=true}
{/if}

{if $enable_product_cat_dialog eq true}
	<link rel="stylesheet" href="include/js/themes/default/style.css">
	{*
	{literal}
	<style type="text/css">
	.ui-widget-header {
		background: url("modules/Map/img/ui-bg_highlight-soft_75_ed9229_1x100.png") repeat-x scroll 50% 50% #ED9229;
		border: 1px solid #AAAAAA;
		font-weight: bold;
	}
	</style>
	{/literal}
	*}
{/if}

{if $sdk_mode eq 'detail'}
	{assign var="fieldname" value=$keyfldname}
	{assign var="descr_field" value=$keyfldname|cat:"_descr"}
	{assign var="dialog_id" value="product_cat_"|cat:$keyfldname|cat:"_dialog"}
	{assign var="categorytree_id" value=$dialog_id|cat:"_categorytree"}
	{if $keyreadonly eq 99}
		<td width=25% class="dvtCellInfo" align="left">
			&nbsp;<span id="dtlview_{$label}">{$keyval}</span>
		</td>
	{else}
		{assign var=fld_style value='class="detailedViewTextBox detailedViewReference detailedViewReferenceRO" readonly'}
		{if $keyval|trim eq ''}
			{assign var=fld_style value='class="detailedViewTextBox detailedViewReference" readonly'}
		{/if}
		<td width="25%" class="dvtCellInfo" align="left" id="mouseArea_{$label}" onmouseover="hndMouseOver({$keyid},'{$label}');" onmouseout="fnhide('crmspanid');" nowrap>
			&nbsp;&nbsp;<span id="dtlview_{$label}">{$keyval}</span>
			<div id="editarea_{$label}" style="display:none;">
				<input type="text" id="txtbox_{$label}" name="{$keyfldname}" value="{$keyval}" {$fld_style}/>
				<img src="{'select.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick="jQuery('#{$dialog_id}').css('display','block');jQuery('#{$dialog_id}').dialog('open');" align="absmiddle" style='cursor:hand;cursor:pointer'>
				<img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" onClick="clear_{$fieldname}_fields(document.forms['DetailView']);" align="absmiddle" style='cursor:pointer' />
				<br><input name="button_{$label}" type="button" class="crmbutton small save" value="{$APP.LBL_SAVE_LABEL}" onclick="dtlViewAjaxSave('{$label}','{$MODULE}',{$keyid},'{$keytblname}','{$keyfldname}','{$ID}');fnhide('crmspanid'); {if $descr_field neq ''}save_product_cat_{$descr_field}_descr();{/if}"/> {$APP.LBL_OR}
				<a href="javascript:;" onclick="hndCancel('dtlview_{$label}','editarea_{$label}','{$label}')" class="link">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
			</div>
		</td>
		<div id="{$dialog_id}" title="{$label}" style="display:none; background: none repeat scroll 0 0 #FFFFEE;">
			<div id="{$categorytree_id}">{$keyoptions}</div>
		</div>
	{/if}
{elseif $sdk_mode eq 'edit'}
	{assign var="fieldname" value=$fldname}
	{assign var="descr_field" value=$fldname|cat:"_descr"}
	{assign var="dialog_id" value="product_cat_"|cat:$fldname|cat:"_dialog"}
	{assign var="categorytree_id" value=$dialog_id|cat:"_categorytree"}
	{assign var=fld_style value='class="detailedViewTextBox detailedViewReference detailedViewReferenceRO" readonly'}
	{if $fldvalue|trim eq ''}
		{assign var=fld_style value='class="detailedViewTextBox detailedViewReference"'}
	{/if}
	{if $readonly eq 99}
		<td width=20% class="dvtCellLabel" align=right>
			<font color="red">*</font>{$fldlabel}
		</td>
		<td width=30% align=left class="dvtCellInfo">
			<input type="hidden" name="{$fldname}" tabindex="{$vt_tab}" value="{$fldvalue}" tabindex="{$vt_tab}" class=detailedViewTextBox >
			{$fldvalue}
		</td>
	{elseif $readonly eq 100}
		<input type="hidden" name="{$fldname}" tabindex="{$vt_tab}" value="{$fldvalue}" tabindex="{$vt_tab}" class=detailedViewTextBox >
	{else}
		<td width=20% class="dvtCellLabel" align=right>
			<font color="red">{$mandatory_field}</font>{$usefldlabel} {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small">{/if}
		</td>
		<td width=30% align=left class="dvtCellInfo">
			<input id="{$fldname}" name="{$fldname}" type="text" value="{$fldvalue}" {$fld_style}/>
			<img src="{'select.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick="jQuery('#{$dialog_id}').css('display','block');jQuery('#{$dialog_id}').dialog('open');" align="absmiddle" style='cursor:hand;cursor:pointer'>
			<img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" onClick="clear_{$fieldname}_fields(document.forms['EditView']);" align="absmiddle" style='cursor:pointer' />
		</td>
		<div id="{$dialog_id}" title="{$fldlabel}" style="display:none; background: none repeat scroll 0 0 #FFFFEE;">
			<div id="{$categorytree_id}">{$secondvalue}</div>
		</div>
	{/if}
{/if}

{if $enable_product_cat_dialog eq true}
	<script type="text/javascript">
	function clear_{$fieldname}_fields(form) {ldelim}
		form.{$fieldname}.value = '';
		enableReferenceField(form.{$fieldname});
		if (form.{$descr_field} != undefined) {ldelim}
			form.{$descr_field}.value = '';
			enableReferenceField(form.{$descr_field});
		{rdelim}
	{rdelim}
	
	jQuery("#{$dialog_id}").dialog(
	{literal}
	{
		autoOpen: false,
		//hide: "clip",
		height: 400,
		width: 400
	});
	{/literal}
	
	jQuery("#{$categorytree_id}")
	.jstree({ldelim} "plugins" : ["themes","html_data","ui"] {rdelim})
	// 1) if using the UI plugin bind to select_node
	.bind("select_node.jstree", function (event, data) {ldelim}
		// `data.rslt.obj` is the jquery extended node that was clicked
		{if $sdk_mode eq 'detail'}
			document.forms['DetailView'].{$fieldname}.value = data.rslt.obj.attr("id");
			disableReferenceField(document.forms['DetailView'].{$fieldname});
			if (document.forms['DetailView'].{$descr_field} != undefined) {ldelim}
				document.forms['DetailView'].{$descr_field}.value = data.rslt.obj.attr("title");
			{rdelim}
		{elseif $sdk_mode eq 'edit'}
			document.forms['EditView'].{$fieldname}.value = data.rslt.obj.attr("id");
			disableReferenceField(document.forms['EditView'].{$fieldname});
			if (document.forms['EditView'].{$descr_field} != undefined) {ldelim}
				document.forms['EditView'].{$descr_field}.value = data.rslt.obj.attr("title");
				disableReferenceField(document.forms['EditView'].{$descr_field});
			{rdelim}
		{/if}
		jQuery('#{$dialog_id}').dialog('close');
	{rdelim})
	// 2) if not using the UI plugin - the Anchor tags work as expected
	//    so if the anchor has a HREF attirbute - the page will be changed
	//    you can actually prevent the default, etc (normal jquery usage)
	.delegate("a", "click", function (event, data) {ldelim}
		event.preventDefault();
	{rdelim});
	</script>
{/if}