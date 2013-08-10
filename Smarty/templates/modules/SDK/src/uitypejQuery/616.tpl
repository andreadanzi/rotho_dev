{if $sdk_mode eq 'detail'}
	{if $keyreadonly eq 99}
		<td width=25% class="dvtCellInfo" align="left">
			&nbsp;<span id ="dtlview_{$keyfldname}">
			{if $keyval neq ''}
			  <span  onclick="return show_points(this, 'showpoints','{$keyval}','{$ID}','showpoints_{$ID}')" align="left" >{$keyval}&nbsp;&times;&nbsp;</span>
			{/if}
			<span align="left" ><img  id="opener"  src="modules/SDK/src/uitypejQuery/img/star_16.png" alt="RP Prog - Rating" title="RP Prog - Rating" /></span>

			</span>
			<div id="showpoints_{$ID}" class="showpoints_hidden" onclick="return show_points(this, 'showpoints','{$keyval}','{$ID}','showpoints_{$ID}')"   >
				99{$keyval}
			</div>
		</td>
	{else}
		<td width="25%" class="dvtCellInfo" align="left" id="mouseArea_{$label}" onmouseover="hndMouseOver({$keyid},'{$label}');" onmouseout="fnhide('crmspanid');">
			&nbsp;&nbsp;<span id="dtlview_{$keyfldname}">
			{if $keyval neq ''}
			  <span align="left"  onclick="return show_points(this, 'showpoints','{$keyval}','{$ID}','showpoints_{$ID}')" >{$keyval}&nbsp;&times;&nbsp;</span>
			{/if}
			<span align="left" ><img id="opener" src="modules/SDK/src/uitypejQuery/img/star_16.png" alt="RP Prog - Rating" title="RP Prog - Rating" /></span>
			</span>
			<div id="showpoints_{$ID}" class="showpoints_hidden" onclick="return show_points(this, 'showpoints','{$keyval}','{$ID}','showpoints_{$ID}')"   >
				99{$keyval}
			</div>
		</td>
	{/if}
{elseif $sdk_mode eq 'edit'}
	{if $readonly eq 99}
		<td width=20% class="dvtCellLabel" align=right>
			<font color="red">*</font>{$fldlabel}
		</td>
		<td width=30% align=left class="dvtCellInfo">
			<span onclick="return show_points(this, 'showpoints','{$fldvalue}','{$ID}','showpoints_{$ID}')" align="left" ><input   type="hidden" name="{$keyfldname}" tabindex="{$vt_tab}" value="{$fldvalue}" tabindex="{$vt_tab}" class=detailedViewTextBox >{$fldvalue}</span>
			<span>&nbsp;&times;&nbsp;</span>
		    <span align="left" ><img src="modules/SDK/src/uitypejQuery/img/star_16.png" alt="RP Prog - Rating" title="RP Prog - Rating" /></span>
			<div id="showpoints_{$ID}" class="showpoints_hidden" onclick="return show_points(this, 'showpoints','{$fldvalue}','{$ID}','showpoints_{$ID}')"   >
				99{$fldvalue}
			</div>
		</td>
	{elseif $readonly eq 100}
		<input type="hidden" name="{$keyfldname}" tabindex="{$vt_tab}" value="{$fldvalue}" tabindex="{$vt_tab}" class=detailedViewTextBox >
	{else}
		<td width=20% class="dvtCellLabel" align=right>
			<font color="red">{$mandatory_field}</font>{$usefldlabel} {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$keyfldname}_mass_edit_check" id="{$keyfldname}_mass_edit_check" class="small">{/if}
		</td>
		<td width=30% align=left class="dvtCellInfo"
			<span onclick="return show_points(this, 'showpoints','{$fldvalue}','{$ID}','showpoints_{$ID}')" align="left" ><input style="width:49%;" type="text" name="{$keyfldname}" tabindex="{$vt_tab}" value="{$fldvalue}" tabindex="{$vt_tab}" class=detailedViewTextBox onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'"></span>
			<span>&nbsp;&times;&nbsp;</span>
			<span align="left" ><img src="modules/SDK/src/uitypejQuery/img/star_16.png" alt="RP Prog - Rating" title="RP Prog - Rating" /></span>
			<div id="showpoints_{$ID}" class="showpoints_hidden" onclick="return show_points(this, 'showpoints','{$fldvalue}','{$ID}','showpoints_{$ID}')"   >
				99{$fldvalue}
			</div>
		</td>
	{/if}
{/if}

