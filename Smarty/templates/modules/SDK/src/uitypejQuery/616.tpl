{if $sdk_mode eq 'detail'}
	{if $keyreadonly eq 99}
		<td width=25% class="dvtCellInfo" align="left">
			&nbsp;<span id ="dtlview_{$fldname}">
			{if $keyval neq ''}
			  <span align="left" ><a  href="#{$keyval}">{$keyval}&nbsp;&times;&nbsp;</a></span>
			{/if}
			<span align="left" ><img src="modules/SDK/src/uitypejQuery/img/star_16.png" alt="RP Prog - Rating" title="RP Prog - Rating" /></span>

			</span>
		</td>
	{else}
		<td width="25%" class="dvtCellInfo" align="left" id="mouseArea_{$label}" onmouseover="hndMouseOver({$keyid},'{$label}');" onmouseout="fnhide('crmspanid');">
			&nbsp;&nbsp;<span id="dtlview_{$fldname}">
			{if $keyval neq ''}
			  <span align="left" ><a  href="#{$keyval}">{$keyval}&nbsp;&times;&nbsp;</a></span>
			{/if}
			<span align="left" ><img src="modules/SDK/src/uitypejQuery/img/star_16.png" alt="RP Prog - Rating" title="RP Prog - Rating" /></span>
			</span>
			<div id="editarea_{$label}" style="display:none;">
				<input class="detailedViewTextBox" onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'" type="text" id="txtbox_{$label}" name="{$keyfldname}" maxlength='100' value="{$keyval}"></input>
				<br><input name="button_{$label}" type="button" class="crmbutton small save" value="{$APP.LBL_SAVE_LABEL}" onclick="dtlViewAjaxSave('{$label}','{$MODULE}',{$keyid},'{$keytblname}','{$keyfldname}','{$ID}');fnhide('crmspanid');"/> {$APP.LBL_OR}
				<a href="javascript:;" onclick="hndCancel('dtlview_{$fldname}','editarea_{$label}','{$label}')" class="link">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
			</div>
		</td>
	{/if}
{elseif $sdk_mode eq 'edit'}
	{if $readonly eq 99}
		<td width=20% class="dvtCellLabel" align=right>
			<font color="red">*</font>{$fldlabel}
		</td>
		<td width=30% align=left class="dvtCellInfo">
			<span align="left" ><input type="hidden" name="{$fldname}" tabindex="{$vt_tab}" value="{$fldvalue}" tabindex="{$vt_tab}" class=detailedViewTextBox >{$fldvalue}</span>
			<span>&nbsp;&times;&nbsp;</span>
		    <span align="left" ><img src="modules/SDK/src/uitypejQuery/img/star_16.png" alt="RP Prog - Rating" title="RP Prog - Rating" /></span>

		</td>
	{elseif $readonly eq 100}
		<input type="hidden" name="{$fldname}" tabindex="{$vt_tab}" value="{$fldvalue}" tabindex="{$vt_tab}" class=detailedViewTextBox >
	{else}
		<td width=20% class="dvtCellLabel" align=right>
			<font color="red">{$mandatory_field}</font>{$usefldlabel} {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small">{/if}
		</td>
		<td width=30% align=left class="dvtCellInfo"
			<span align="left" ><input style="width:49%;" type="text" name="{$fldname}" tabindex="{$vt_tab}" value="{$fldvalue}" tabindex="{$vt_tab}" class=detailedViewTextBox onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'"></span>
			<span>&nbsp;&times;&nbsp;</span>
			<span align="left" ><img src="modules/SDK/src/uitypejQuery/img/star_16.png" alt="RP Prog - Rating" title="RP Prog - Rating" /></span>
		</td>
	{/if}
{/if}