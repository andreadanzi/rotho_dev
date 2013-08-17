{if $sdk_mode eq 'detail'}
	<td width=25% class="dvtCellInfo" align="left">
		&nbsp;<span id ="dtlview_{$keyfldname}">
			{if $keyval neq ''}
			  <span class="show_points" onclick="show_points_loaded(this, 'showpoints','{$keyval}','{$ID}','showpoints_{$ID}');" align="left" >{$keyval}&nbsp;&times;&nbsp;</span>
			{/if}
			<span align="left" ><img  id="opener"  src="modules/SDK/src/uitypejQuery/img/star_16.png" alt="RP Prog - Rating" title="RP Prog - Rating" /></span>
		</span>
		<div id="showpoints_{$ID}" class="showpoints_hidden"    >
			{$INNER_POINTS}
		</div>
	</td>
{elseif $sdk_mode eq 'edit'}
	{if $readonly eq 100}
		<input type="hidden" name="{$keyfldname}" tabindex="{$vt_tab}" value="{$fldvalue}" tabindex="{$vt_tab}" class=detailedViewTextBox >
	{else}
		<td width=20% class="dvtCellLabel" align=right>
			<font color="red">{$mandatory_field}</font>{$usefldlabel} {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$keyfldname}_mass_edit_check" id="{$keyfldname}_mass_edit_check" class="small">{/if}
		</td>
		<td width=30% align=left class="dvtCellInfo">
			&nbsp;<span id ="dtlview_{$keyfldname}">
				<span class="show_points" onclick="show_points_loaded(this, 'showpoints','{$fldvalue}','{$ID}','showpoints_{$ID}');" align="left" >{$fldvalue}&nbsp;&times;&nbsp;</span>
				<span align="left" ><img  id="opener" src="modules/SDK/src/uitypejQuery/img/star_16.png" alt="RP Prog - Rating" title="RP Prog - Rating" /></span>
			</span>
			<div id="showpoints_{$ID}" class="showpoints_hidden"   >
				{$INNER_POINTS}
			</div>
		</td>
	{/if}
{/if}

