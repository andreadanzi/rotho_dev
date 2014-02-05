{***************************************************************************************
 * The contents of this file are subject to the CRMVILLAGE.BIZ VTECRM License Agreement
 * ("licenza.txt"); You may not use this file except in compliance with the License
 * The Original Code is:  CRMVILLAGE.BIZ VTECRM
 * The Initial Developer of the Original Code is CRMVILLAGE.BIZ.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
 ***************************************************************************************}
{* crmv@36171 *}

{if $sdk_mode eq 'detail'}
	{if $keyreadonly eq 99}
		<td width=25% class="dvtCellInfo" align="left">
			&nbsp;<span id="dtlview_{$label}">{$keyval}</span>
			<div id="editarea_{$label}" style="display:none;">
				<input type="hidden" id="txtbox_{$label}" name="{$keyfldname}" value="{$keyval}"></input>
			</div>
		</td>
	{else}
		<td width="25%" class="dvtCellInfo" align="left" id="mouseArea_{$label}" onmouseover="hndMouseOver({$keyid},'{$label}');" onmouseout="fnhide('crmspanid');">
			&nbsp;&nbsp;<span id="dtlview_{$label}">{$keyval}</span>
			<div id="editarea_{$label}" style="display:none;">
				<input class="detailedViewTextBox" onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'" type="text" id="txtbox_{$label}" name="{$keyfldname}" maxlength='100' value="{$keyval}"></input>
				<br><input name="button_{$label}" type="button" class="crmbutton small save" value="{$APP.LBL_SAVE_LABEL}" onclick="dtlViewAjaxSave('{$label}','{$MODULE}',{$keyid},'{$keytblname}','{$keyfldname}','{$ID}');fnhide('crmspanid');"/> {$APP.LBL_OR}
				<a href="javascript:;" onclick="hndCancel('dtlview_{$label}','editarea_{$label}','{$label}')" class="link">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
			</div>
		</td>
	{/if}
	<script type="text/javascript">
	function save_product_cat_{$keyfldname}_descr() {ldelim}
		dtlViewAjaxSave('{$label}','{$MODULE}',{$keyid},'{$keytblname}','{$keyfldname}','{$ID}');
	{rdelim}
	</script>
{elseif $sdk_mode eq 'edit'}
	{assign var=fld_style value='class="detailedViewTextBox detailedViewReference detailedViewReferenceRO"'}
	{if $fldvalue|trim eq ''}
		{assign var=fld_style value='class="detailedViewTextBox detailedViewReference"'}
	{/if}
	{if $readonly eq 100}
		<input type="hidden" name="{$fldname}" tabindex="{$vt_tab}" value="{$fldvalue}" tabindex="{$vt_tab}" class=detailedViewTextBox >
	{else}
		<td width=20% class="dvtCellLabel" align=right>
			<font color="red">{$mandatory_field}</font>{if $readonly eq 99}{$fldlabel}{else}{$usefldlabel}{/if} {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small">{/if}
		</td>
		<td width=30% align=left class="dvtCellInfo">
			<input id="{$fldname}" name="{$fldname}" type="text" value="{$fldvalue}" {$fld_style} {if $readonly eq 99}readonly{/if}/>
		</td>
	{/if}
{/if}