{if $sdk_mode eq 'detail'}
	<td width=25% class="dvtCellInfo" align="left">
		&nbsp;<span id ="dtlview_{$keyfldname}">
			{if $keyval neq ''}
			 <span class="show_points" onclick="show_points_loaded(this, 'showpoints','{$keyval}','{$ID}','showpoints_{$ID}');" align="left" >{$keyval}&nbsp;&times;&nbsp;
		    {else}
			<span class="show_points" align="left" >0&nbsp;&times;&nbsp;
			{/if}
			{if $NO_VISITE }
			<img  id="opener"  src="modules/SDK/src/uitypejQuery/img/r_{$ICONFILE}" alt="Acc - Rating" title="Acc - Rating" /></span>
			{else}
			<img  id="opener"  src="modules/SDK/src/uitypejQuery/img/{$ICONFILE}" alt="Acc - Rating" title="Acc - Rating" /></span>
			{/if}
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
			<font color="red">{$mandatory_field}</font>{$usefldlabel}
		</td>
		<td width=30% align=left class="dvtCellInfo">
			&nbsp;<span id ="dtlview_points">
				<span class="show_points" onclick="show_points_loaded(this, 'showpoints','{$fldvalue}','{$ID}','showpoints_{$ID}');" align="left" >{$fldvalue}&nbsp;&times;&nbsp;
				{if $NO_VISITE }
				<img  id="opener" src="modules/SDK/src/uitypejQuery/img/r_{$ICONFILE}" alt="Acc - Rating" title="Acc - Rating" /></span>
				{else}
				<img  id="opener" src="modules/SDK/src/uitypejQuery/img/{$ICONFILE}" alt="Acc - Rating" title="Acc - Rating" /></span>
				{/if}
				<input type="hidden" name="points" tabindex="{$vt_tab}" value="{$fldvalue}" tabindex="{$vt_tab}" class=detailedViewTextBox >
			</span>
			<div id="showpoints_{$ID}" class="showpoints_hidden"   >
				{$INNER_POINTS}
			</div>
		</td>
	{/if}
{/if}

