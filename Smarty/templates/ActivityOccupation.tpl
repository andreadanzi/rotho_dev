<!-- crmv@17001 -->
<link href="modules/Calendar/wdCalendar/css/calendar.css" rel="stylesheet" type="text/css" /> 
{literal}
<style>
.current_activity {
	background-color: red;
	display: block;
    height: 1.2em;
    overflow: hidden;
    text-decoration: none;
    width: 100%;
    cursor: pointer; /* crmv@26807 */
}
.busy {
	background-color: #9999DD;
	display: block;
    height: 1.2em;
    overflow: hidden;
    text-decoration: none;
    width: 100%;
    cursor: pointer; /* crmv@26807 */
}
.free {
	display: block;
    height: 1.2em;
    overflow: hidden;
    text-decoration: none;
    width: 100%;
}
</style>
{/literal}

{assign var=start_hour value=$start_hour}
{assign var=end_hour value=$end_hour}

<table class="small" cellspacing="0" cellpadding="0" border="0" width="100%">
{foreach item=row from=$OCCUPATION}
	<tr style="height: 25px;">
		<td class="lvtCol" style="border:1px 0"><b>{$row.label}</b></td>
	{section name=hours start=$start_hour loop=$end_hour step=1}
		<td class="lvtCol" colspan="2">{$smarty.section.hours.index}:00</td>
	{/section}
	</tr>
	
	{foreach item=row from=$row.occupation}
		<tr style="height: 25px;">
		<td class="lvtCol" style="border:0"></td>
		{foreach key=header item=maindata from=$row}
			{if $maindata.colspan neq 0}
				<td colspan="{$maindata.colspan}">
					<div class="{$maindata.type}" onClick='showPreview({$maindata.info},this);'></div>
				</td>
			{/if}
		{/foreach}
		</tr>
	{/foreach}
{/foreach}
</table>

{literal}
<script type="text/javascript">
function showPreview(info,obj) {
	$('bbit-cs-buddle').style.display = 'none';
	$('bbit-cs-buddle').style.visible = 'hidden';
	info = eval(info);
	jQuery('#bbit-cs-what').html('<b>'+info['subject']+'</b>');
	jQuery('#bbit-cs-buddle-timeshow').html(info['date']);
	jQuery('#preview_other_info').html('');
	for (i=0;i<info['other_info'].length;i++) {
		jQuery('#preview_other_info').append('<tr><td>'+info['other_info'][i]+'</td></tr>');
	}
	fnvshobj(obj,'bbit-cs-buddle');
}
</script>
{/literal}
{* crmv@26807 *}
<div id="bbit-cs-buddle" style="background-color:white;border:1px solid #999999;z-index:1000;width:320px;visibility:hidden;" class="bubble">
	<table cellspacing="0" cellpadding="0" class="bubble-table">
		{* <tr>
			<td class="bubble-cell-side">
				<div class="bubble-corner" id="tl1">
					<div class="bubble-sprite bubble-tl"></div>
				</div>
			</td>
			<td class="bubble-cell-main">
				<div class="bubble-top"></div>
			</td>
			<td class="bubble-cell-side">
				<div class="bubble-corner" id="tr1">
					<div class="bubble-sprite bubble-tr"></div>
				</div>
			</td>
		</tr> *}
		{* <tr><td>
			<table style="width:100%;padding:6px" border="0" cellspacing="0" cellpadding="0" class="mailClientWriteEmailHeader" id="emailHeader">
				<tr>
					<td></td>
				</tr>
			</table>
		</td></tr> *}
		{* <tr>
			<td class="bubble-cell-main level3Bg" " valign="middle" style="text-align:center;padding:5px">
				<div>
					<div align="right"> <!-- danzi.tn@20140310 Crea Report Visita tpl -->
						<input id="bbit-cs-visit" class="crmbutton small edit" value="" type="button"/>
						&nbsp;<input id="bbit-cs-delete" class="crmbutton small delete" value="" type="button"/>
						&nbsp;<input id="bbit-cs-close" class="crmbutton small cancel" value="" type="button"/>
						&nbsp;<input id="bbit-cs-editLink" class="crmbutton small edit" value="" type="button"/>
					</div>
				</div>
			</td>
		</tr> *}
		<tr>
			<td class="bubble-mid">
				<div id="bubbleContent1" style="overflow: hidden;">
					<div>
						<div></div>
						<div class="cb-root">
							<table cellspacing="0" cellpadding="0" class="cb-table">
								<tr>
									<td class="cb-value">
										<div id="bbit-cs-what"></div>
									</td>
								</tr>
								<tr>
									<td class="cb-value">
										<div id="bbit-cs-buddle-timeshow"></div>
									</td>
								</tr>
							</table>
						</div>
						<div class="cb-root">
							<table cellspacing="0" cellpadding="0" class="cb-table" id="preview_other_info">
							</table>
						</div>
					</div>
				</div>
			</td>
		</tr>
		<tr>
			<td>
			</td>
		</tr>
		{* <tr>
			<td>
				<div class="bubble-corner" id="bl1">
					<div class="bubble-sprite bubble-bl"></div>
				</div>
			</td>
			<td>
				<div class="bubble-bottom"></div>
			</td>
			<td>
				<div class="bubble-corner" id="br1">
					<div class="bubble-sprite bubble-br"></div>
				</div>
			</td>
		</tr> *}
	</table>
	<br><br>
	<div class="bubble-closebutton" id="bubbleClose2" onClick="$('bbit-cs-buddle').style.display = 'none';$('bbit-cs-buddle').style.visible = 'hidden';"></div>
	<div class="prong" id="prong1" style="display: none;">
		<div class="bubble-sprite"></div>
	</div>
</div>
{* crmv@26807e *}
<!-- crmv@17001e -->