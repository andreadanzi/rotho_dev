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


{if $uitype eq 19 && $uitype eq 20 && $uitype eq 21 && $uitype eq 24}
<div id="textarea" style="display:none;"></div>
{/if}		
<!-- Added this file to display the fields in Create Entity page based on ui types  -->
{foreach key=label item=subdata from=$data}
	{foreach key=mainlabel item=maindata from=$subdata}
		{assign var="uitype" value="$maindata[0][0]"}
		{assign var="fldlabel" value="$maindata[1][0]"}
		{assign var="fldlabel_sel" value="$maindata[1][1]"}
		{assign var="fldlabel_combo" value="$maindata[1][2]"}
		{assign var="fldname" value="$maindata[2][0]"}
		{assign var="fldvalue" value="$maindata[3][0]"}
		{assign var="secondvalue" value="$maindata[3][1]"}
		{assign var="thirdvalue" value="$maindata[3][2]"}
		{assign var="vt_tab" value="$maindata[4][0]"}

		{if $uitype eq 2}
				<input size="20" type="text" class="detailedViewTextBoxQuick" name="{$fldname}" id="{$fldname}" tabindex="{$vt_tab}" value="{$fldvalue|escape}" tabindex="{$vt_tab}" OnBlur="changeMassUpdateValue(this.value);">
			
		{elseif $uitype eq 3}<!-- Non Editable field, only configured value will be loaded -->
				<input readonly type="text" class="detailedViewTextBoxQuick" tabindex="{$vt_tab}" name="{$fldname}" id ="{$fldname}" {if $MODE eq 'edit'} value="{$fldvalue|escape}" {else} value="{$inv_no}" {/if} >
		<!--   //crmv@7231 - crmv@7216 -->		
		{elseif $uitype eq 11 || $uitype eq 1 || $uitype eq 13 || $uitype eq 7 || $uitype eq 9 || $uitype eq 1112 || $uitype eq 1013 || $uitype eq 1014}
			{if $fldname eq 'tickersymbol' && $MODULE eq 'Accounts'}

				 <input size="20" type="text" class="detailedViewTextBoxQuick" name="{$fldname}" tabindex="{$vt_tab}" id ="{$fldname}" value="{$fldvalue|escape}" OnBlur="changeMassUpdateValue(this.value);">
				 <span id="vtbusy_info" style="display:none;">
				 <img src="{$IMAGE_PATH}vtbusy.gif" border="0"></span>
			{else}
         <input type="text" class="detailedViewTextBoxQuick" name="{$fldname}" id="{$fldname}" value="" height="25" OnBlur="changeMassUpdateValue(this.value);">
			{/if}

		{elseif $uitype eq 19 || $uitype eq 20 || $uitype eq 21 || $uitype eq 24}
			<!-- In Add Comment are we should not display anything -->
				<textarea  tabindex="{$vt_tab}" id="textarea" name="{$fldname}" class="detailedViewTextBoxQuick" OnBlur="changeMassUpdateValue(this.value);">{$fldvalue|escape}</textarea>
				{if $fldlabel eq $MOD.Solution}
				<input type = "hidden" name="helpdesk_solution" value = '{$fldvalue}'>
				{/if}
			<!-- //crmv@8982 --> 	
			{elseif $uitype eq 15 || $keyid eq '1015' || $uitype eq 16 || $uitype eq 111 || $uitype eq 150} <!-- uitype 111 added for noneditable existing picklist values - ahmed -->
			<!-- //crmv@8982e --> 
			   <select name="{$fldname}" tabindex="{$vt_tab}" class="small" OnChange="changeMassUpdateValue(this.value);">
				{foreach item=arr from=$fldvalue}
					{if $arr[0] eq $APP.LBL_NOT_ACCESSIBLE}
					<option value="{$arr[0]}" {$arr[2]}>
						{$arr[0]}
					</option>
					{else}
					<option value="{$arr[1]}" {$arr[2]}>
                    	{$arr[0]}
                    </option>
					{/if}
					{if $arr[2] eq 'selected'}
						{assign var="value_15" value=$arr[1]}
					{/if}	
				{/foreach}
			   </select>
		{elseif $uitype eq 33 || $uitype eq 330}
		
			   <select MULTIPLE name="{$fldname}[]" size="4" style="width:160px;" tabindex="{$vt_tab}" class="small" OnBlur="changeMassUpdateMultipleValue('multiple');">
				{foreach item=arr from=$fldvalue}
					<option value="{$arr[1]}" {$arr[2]}>
                                                {$arr[0]}
                                        </option>
				{/foreach}
			   </select>

		{elseif $uitype eq 53}
		<!--crmv@7223-->
		{if $SECONDARY_OWNER_MODIFY eq 'yes'}
			{assign var="style_53" value="display:none"}
		{else}
			{assign var="style_53" value="display:block"}	
		{/if}	
			{assign var="assigntype" value="assigntype_"|cat:$fldname}
			{assign var="assign_user" value="assign_user_"|cat:$fldname}
			{assign var="assign_team" value="assign_team_"|cat:$fldname}
			{assign var="assigned_group_name" value="assigned_group_name_"|cat:$fldname}
			{assign var="assigned_user_id" value=$fldname}
			{assign var="select_user" value=""}
			{assign var="select_group" value=""}
			<td width="30%" align=left class="dvtCellInfo">
				{assign var=check value=2}
				{foreach key=key_one item=arr from=$fldvalue}
					{foreach key=sel_value item=value from=$arr}
						{if $value eq 'selected'}
							{assign var="value_backup" value=$sel_value}
							{assign var=check value=0}
						{/if}
					{/foreach}
				{/foreach}
				{foreach key=key_one item=arr from=$secondvalue}
					{foreach key=sel_value item=value from=$arr}
						{if $value eq 'selected'}
							{assign var="value_backup" value=$sel_value}
							{assign var=check value=1}
						{/if}
					{/foreach}
				{/foreach}
				{if $check eq 0}
					{assign var=select_user value='checked'}
					{assign var=style_user value='display:block'}
					{assign var=style_group value='display:none'}
				{elseif $check eq 1}
					{assign var=select_group value='checked'}
					{assign var=style_user value='display:none'}
					{assign var=style_group value='display:block'}
				{else}
					{assign var=select_nobody value='checked'}
					{assign var=style_user value='display:none'}
					{assign var=style_group value='display:none'}															
				{/if}				
				<div id='all_data{$fldname}' style='{$style_53}'>
				<input type="radio" tabindex="{$vt_tab}" name="{$assigntype}" {$select_user} value="U" onclick="toggleAssignType(this.value,'{$assign_user}','{$assign_team}')" >&nbsp;{$APP.LBL_USER}

				{if $secondvalue neq ''}
					<input type="radio" name="{$assigntype}" {$select_group} value="T" onclick="toggleAssignType(this.value,'{$assign_user}','{$assign_team}')">&nbsp;{$APP.LBL_GROUP}
				{/if}
				{if $thirdvalue eq 'nobody'}
					<input type="radio" name="{$assigntype}" {$select_nobody} value="U" onclick="toggleAssignType(this.value,'{$assign_user}','{$assign_team}',true,'{$assigned_user_id}')">&nbsp;{$APP.LBL_NOBODY}
				{/if}
				<span id="{$assign_user}" style="{$style_user}">
					<select name="{$assigned_user_id}" class="small" OnBlur="changeMassUpdateValue(this.value);">
						{foreach key=key_one item=arr from=$fldvalue}
							{foreach key=sel_value item=value from=$arr}
								<option value="{$key_one}" {$value}>{$sel_value}</option>
							{/foreach}
						{/foreach}
					</select>
				</span>

				{if $secondvalue neq ''}
					<span id="{$assign_team}" style="{$style_group}" >
						<select name="{$assigned_group_name}" class="small" OnBlur="changeMassUpdateValue(this.value);">';
							{foreach key=key_one item=arr from=$secondvalue}
								{foreach key=sel_value item=value from=$arr}
									<option value="{$sel_value}" {$value}>{$sel_value}</option>
								{/foreach}
							{/foreach}
						</select>
					</span>
				{/if}
				</div>
				{if $SECONDARY_OWNER_MODIFY eq 'yes'}
					{$value_backup}
				{/if}	
			</td>
			<!--crmv@7223e-->
			<!--danzi.tn@20140220-->
		{elseif $uitype eq 52 || $uitype eq 77 || $uitype eq 1077}
				{if $uitype eq 52}
					<select name="assigned_user_id" class="small" OnChange="changeMassUpdateValue(this.value);">
				{elseif $uitype eq 77}
					<select name="assigned_user_id1" tabindex="{$vt_tab}" class="small" OnChange="changeMassUpdateValue(this.value);">
				{elseif $uitype eq 1077}
					<select name="assigned_user_id1" tabindex="{$vt_tab}" class="small" OnChange="changeMassUpdateValue(this.value);">
				{else}
					<select name="{$fldname}" tabindex="{$vt_tab}" class="small" OnChange="changeMassUpdateValue(this.value);">
				{/if}

				{foreach key=key_one item=arr from=$fldvalue}
					{foreach key=sel_value item=value from=$arr}
						<option value="{$key_one}" {$value}>{$sel_value}</option>
					{/foreach}
				{/foreach}
				</select>
		{elseif $uitype eq 51}
				
			<input readonly id="account_name" name="account_name" style="border:1px solid #bababa;" type="text" class="detailedViewTextBoxQuick" value="{$fldvalue|escape}">&nbsp;<img tabindex="{$vt_tab}" src="{$IMAGE_PATH}select.gif" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='openPopup("index.php?module=Accounts&action=Popup&popuptype=specific_to_id&form=TasksEditView&form_submit=false","test","width=640,height=602,resizable=0,scrollbars=0");' align="absmiddle" style='cursor:hand;cursor:pointer'>&nbsp;<input type="image" src="{$IMAGE_PATH}clear_field.gif" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE=javascript onClick="clearMassAccountNameId();" align="absmiddle" style='cursor:hand;cursor:pointer'>{* crmv@21048m *}

		{elseif $uitype eq 50}
		
				<input readonly name="account_name" class="detailedViewTextBoxQuick" type="text" value="{$fldvalue|escape}"><input name="{$fldname}" type="hidden" value="{$secondvalue}" OnBlur="changeMassUpdateValue(this.value);">&nbsp;<img src="{$IMAGE_PATH}select.gif" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='openPopup("index.php?module=Accounts&action=Popup&popuptype=specific&form=TasksEditView&form_submit=false","test","width=640,height=602,resizable=0,scrollbars=0");' align="absmiddle" style='cursor:hand;cursor:pointer'>{* crmv@21048m *}
		{elseif $uitype eq 73}
				<input readonly name="account_name" id = "single_accountid" type="text" class="detailedViewTextBoxQuick" value="{$fldvalue|escape}"><input name="{$fldname}" type="hidden" value="{$secondvalue}" OnBlur="changeMassUpdateValue(this.value);">&nbsp;<img src="{$IMAGE_PATH}select.gif" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='openPopup("index.php?module=Accounts&action=Popup&popuptype=specific_account_address&form=TasksEditView&form_submit=false","test","width=640,height=602,resizable=0,scrollbars=0");' align="absmiddle" style='cursor:hand;cursor:pointer'>{* crmv@21048m *}
		{elseif $uitype eq 75 || $uitype eq 81}
			
			
				<input name="vendor_name" readonly type="text" class="detailedViewTextBoxQuick" style="border:1px solid #bababa;" value="{$fldvalue|escape}"><input name="{$fldname}" type="hidden" value="{$secondvalue}" OnBlur="changeMassUpdateValue(this.value);">&nbsp;<img src="{$IMAGE_PATH}select.gif" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='openPopup("index.php?module=Vendors&action=Popup&html=Popup_picker&popuptype={$pop_type}&form=EditView","test","width=640,height=602,resizable=0,scrollbars=0");' align="absmiddle" style='cursor:hand;cursor:pointer'>{* crmv@21048m *}
				{if $uitype eq 75}
					&nbsp;<input type="image" tabindex="{$vt_tab}" src="{$IMAGE_PATH}clear_field.gif" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE=javascript onClick="this.form.vendor_id.value='';this.form.vendor_name.value='';return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
				{/if}

		{elseif $uitype eq 57}
			
      	<input name="contact_name"  readonly type="text" class="detailedViewTextBoxQuick" style="border:1px solid #bababa;" value="{$fldvalue|escape}"><input OnBlur="changeMassUpdateValue(this.value);" name="{$fldname}" type="hidden" value="{$secondvalue}">&nbsp;<img src="{$IMAGE_PATH}select.gif" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='selectContact("false","general",document.EditView)' align="absmiddle" style='cursor:hand;cursor:pointer'>&nbsp;<input type="image" tabindex="{$vt_tab}" src="{$IMAGE_PATH}clear_field.gif" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE=javascript onClick="this.form.contact_id.value=''; this.form.contact_name.value='';return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
		
		{elseif $uitype eq 58}
				<input name="campaignname" readonly type="text" class="detailedViewTextBoxQuick" style="border:1px solid #bababa;" value="{$fldvalue|escape}"><input OnBlur="changeMassUpdateValue(this.value);" name="{$fldname}" type="hidden" value="{$secondvalue}">&nbsp;<img src="{$IMAGE_PATH}select.gif" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='openPopup("index.php?module=Campaigns&action=Popup&html=Popup_picker&popuptype=specific_campaign&form=EditView","test","width=640,height=602,resizable=0,scrollbars=0");' align="absmiddle" style='cursor:hand;cursor:pointer'>&nbsp;<input type="image" tabindex="{$vt_tab}" src="{$IMAGE_PATH}clear_field.gif" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE=javascript onClick="this.form.campaignid.value=''; this.form.campaignname.value='';return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>{* crmv@21048m *}

		{elseif $uitype eq 80}
				<input name="salesorder_name" readonly type="text" class="detailedViewTextBoxQuick" style="border:1px solid #bababa;" value="{$fldvalue|escape}"><input OnBlur="changeMassUpdateValue(this.value);" name="{$fldname}" type="hidden" value="{$secondvalue}">&nbsp;<img src="{$IMAGE_PATH}select.gif" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='selectSalesOrder();' align="absmiddle" style='cursor:hand;cursor:pointer'>&nbsp;<input type="image" tabindex="{$vt_tab}" src="{$IMAGE_PATH}clear_field.gif" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE=javascript onClick="this.form.salesorder_id.value=''; this.form.salesorder_name.value='';return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>

		{elseif $uitype eq 78}
				<input name="quote_name" readonly type="text" class="detailedViewTextBoxQuick" style="border:1px solid #bababa;" value="{$fldvalue|escape}"><input OnBlur="changeMassUpdateValue(this.value);" name="{$fldname}" type="hidden" value="{$secondvalue}">&nbsp;<img src="{$IMAGE_PATH}select.gif" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='selectQuote()' align="absmiddle" style='cursor:hand;cursor:pointer'>&nbsp;<input type="image" tabindex="{$vt_tab}" src="{$IMAGE_PATH}clear_field.gif" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE=javascript onClick="this.form.quote_id.value=''; this.form.quote_name.value='';return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>

		{elseif $uitype eq 76}
				<input name="potential_name" readonly type="text" class="detailedViewTextBoxQuick" style="border:1px solid #bababa;" value="{$fldvalue|escape}"><input OnBlur="changeMassUpdateValue(this.value);" name="{$fldname}" type="hidden" value="{$secondvalue}">&nbsp;<img tabindex="{$vt_tab}" src="{$IMAGE_PATH}select.gif" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='selectPotential()' align="absmiddle" style='cursor:hand;cursor:pointer'>&nbsp;<input type="image" src="{$IMAGE_PATH}clear_field.gif" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE=javascript onClick="this.form.potential_id.value=''; this.form.potential_name.value='';return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>

		{elseif $uitype eq 17}
			&nbsp;&nbsp;http://
			<input type="text" tabindex="{$vt_tab}" name="{$fldname}" class="detailedViewTextBoxQuick" style="border:1px solid #bababa;" size="20" onkeyup="validateUrl('{$fldname}');" value="{$fldvalue|escape}" OnBlur="changeMassUpdateValue(this.value);">

		{elseif $uitype eq 85}
        <img src="{$IMAGE_PATH}skype.gif" alt="Skype" title="Skype" LANGUAGE=javascript align="absmiddle"></img><input type="text" tabindex="{$vt_tab}" name="{$fldname}" class="detailedViewTextBoxQuick" style="border:1px solid #bababa;" size="27" value="{$fldvalue|escape}" OnBlur="changeMassUpdateValue(this.value);">

		{elseif $uitype eq 71 || $uitype eq 72}
				<input name="{$fldname}" tabindex="{$vt_tab}" type="text" class="detailedViewTextBoxQuick" value="{$fldvalue|escape}" OnBlur="changeMassUpdateValue(this.value);">

		{elseif $uitype eq 56}
			<!--Mobile access-->
			<td width="20%" class="dvtCellLabel" align=right>
				{$fldlabel}
			</td>
			{if $fldname eq 'notime' && $ACTIVITY_MODE eq 'Events'}
					<input name="{$fldname}" value="1" tabindex="{$vt_tab}" type="checkbox" onclick="toggleTime()" OnBlur="changeMassUpdateValue(this.value);" >
				
			{else}
					<input name="{$fldname}" value="1" tabindex="{$vt_tab}" type="checkbox" {if $PROD_MODE eq 'create'}checked{/if} OnClick="changeCheckboxValue(this.checked);">
			   
      {/if}
		{elseif $uitype eq 23 || $uitype eq 5 || $uitype eq 6}
				{foreach key=date_value item=time_value from=$fldvalue}
					{assign var=date_val value="$date_value"}
					{assign var=time_val value="$time_value"}
				{/foreach}

				<input name="{$fldname}" tabindex="{$vt_tab}" id="jscal_field_{$fldname}" type="text" class="detailedViewTextBoxQuick" style="border:1px solid #bababa;" size="11" maxlength="10" value="{$date_val|escape}" OnBlur="changeMassUpdateValue(this.value);">
				
				{if $uitype eq 6}
					<input name="time_start" tabindex="{$vt_tab}" style="border:1px solid #bababa;" size="5" maxlength="5" type="text" class="detailedViewTextBoxQuick" value="{$time_val}">
				{/if}

				{foreach key=date_format item=date_str from=$secondvalue}
					{assign var=dateFormat value="$date_format"}
					{assign var=dateStr value="$date_str"}
				{/foreach}

				{if $uitype eq 5 || $uitype eq 23}
					<br><font size=1><em old="(yyyy-mm-dd)">({$dateStr})</em></font>
				{else}
					<br><font size=1><em old="(yyyy-mm-dd)">({$dateStr})</em></font>
				{/if}

				

		{elseif $uitype eq 63}
				<input name="{$fldname}" type="text" class="detailedViewTextBoxQuick" size="2" value="{$fldvalue|escape}" OnBlur="changeMassUpdateValue(this.value);"  tabindex="{$vt_tab}" >&nbsp;
				<select name="duration_minutes" tabindex="{$vt_tab}" class="small">
					{foreach key=labelval item=selectval from=$secondvalue}
						<option value="{$labelval}" {$selectval}>{$labelval}</option>
					{/foreach}
				</select>
		{elseif $uitype eq 68 || $uitype eq 66 || $uitype eq 62}
		  <table>
		  <tr>
			<td width="20%" class="dvtCellLabel" align=right>
				<select class="small" name="parent_type" onChange='document.EditView.parent_name.value=""; document.EditView.parent_id.value=""'>
					{section name=combo loop=$fldlabel}
						<option value="{$fldlabel_combo[combo]}" {$fldlabel_sel[combo]}>{$fldlabel[combo]}</option>
					{/section}
				</select>
			</td>
			<td width="30%" align=left class="dvtCellInfo">
				<input name="{$fldname}" type="hidden" value="{$secondvalue}">
				<input name="parent_name" readonly id = "parentid" type="text" class="detailedViewTextBoxQuick" style="border:1px solid #bababa;" value="{$fldvalue|escape}" OnBlur="changeMassUpdateValue(this.value);" >
				&nbsp;<img src="{$IMAGE_PATH}select.gif" tabindex="{$vt_tab}" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='openPopup("index.php?module="+ document.EditView.parent_type.value +"&action=Popup&html=Popup_picker&form=HelpDeskEditView","test","width=640,height=602,resizable=0,scrollbars=0,top=150,left=200");' align="absmiddle" style='cursor:hand;cursor:pointer'>&nbsp;<input type="image" src="{$IMAGE_PATH}clear_field.gif" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE=javascript onClick="this.form.parent_id.value=''; this.form.parent_name.value=''; return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>{* crmv@21048m *}
			</td>
      </tr>
      </table>
		{elseif $uitype eq 357}
		  <table>
		  <tr>
			<td width="20%" class="dvtCellLabel" align=right>To:&nbsp;</td>
			<td width="90%" colspan="3">
				<input name="{$fldname}" type="hidden" value="{$secondvalue}">
				<textarea readonly name="parent_name" cols="70" rows="2" class="detailedViewTextBoxQuick">{$fldvalue}</textarea>&nbsp;
				<select name="parent_type" class="small">
					{foreach key=labelval item=selectval from=$fldlabel}
						<option value="{$labelval}" {$selectval}>{$labelval}</option>
					{/foreach}
				</select>
				&nbsp;<img tabindex="{$vt_tab}" src="{$IMAGE_PATH}select.gif" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='openPopup("index.php?module="+ document.EditView.parent_type.value +"&action=Popup&html=Popup_picker&form=HelpDeskEditView","test","width=640,height=602,resizable=0,scrollbars=0,top=150,left=200");' align="absmiddle" style='cursor:hand;cursor:pointer'>&nbsp;<input type="image" src="{$IMAGE_PATH}clear_field.gif" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE=javascript onClick="this.form.parent_id.value=''; this.form.parent_name.value=''; return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>{* crmv@21048m *}
			</td>
		   <tr style="height:25px">
			<td width="20%" class="dvtCellLabel" align=right>CC:&nbsp;</td>	
			<td width="30%" align=left class="dvtCellInfo">
				<input name="ccmail" type="text"   value="">
			</td>
			<td width="20%" class="dvtCellLabel" align=right>BCC:&nbsp;</td>
			<td width="30%" align=left class="dvtCellInfo">
				<input name="bccmail" type="text"   value="">
			</td>
		   </tr>
      </table>
		{elseif $uitype eq 59}
				<input name="{$fldname}" type="hidden" value="{$secondvalue}" OnBlur="changeMassUpdateValue(this.value);">
				<input name="product_name" readonly type="text" class="detailedViewTextBoxQuick" value="{$fldvalue|escape}" >&nbsp;<img tabindex="{$vt_tab}" src="{$IMAGE_PATH}select.gif" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='openPopup("index.php?module=Products&action=Popup&html=Popup_picker&form=HelpDeskEditView&popuptype=specific","test","width=640,height=602,resizable=0,scrollbars=0,top=150,left=200");' align="absmiddle" style='cursor:hand;cursor:pointer'>&nbsp;<input type="image" src="{$IMAGE_PATH}clear_field.gif" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE=javascript onClick="this.form.product_id.value=''; this.form.product_name.value=''; return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>{* crmv@21048m *}
		{elseif $uitype eq 55} 
       
       {if $COLUMNAME eq "salutation"}
        	<select name="salutationtype" class="small" OnBlur="changeMassUpdateValue(this.value);">
                {foreach item=arr from=$fldvalue}
                        <option value="{$arr[1]}" {$arr[2]}>
                                                    {$arr[0]}
                                                </option>
                {/foreach}
			{else}	
		    	<input size="20" type="text" class="detailedViewTextBoxQuick" name="{$fldname}" tabindex="{$vt_tab}" value= "{$secondvalue|escape}" OnBlur="changeMassUpdateValue(this.value);">
		  {/if}	
    {elseif $uitype eq 22}
			<textarea name="{$fldname}" cols="30" class="detailedViewTextBoxQuick" tabindex="{$vt_tab}" rows="2" OnBlur="changeMassUpdateValue(this.value);">{$fldvalue}</textarea>
		{elseif $uitype eq 69}
			
				{if $MODULE eq 'Products'}
					<input name="del_file_list" type="hidden" value="">
					<div id="files_list" style="border: 1px solid grey; width: 500px; padding: 5px; background: rgb(255, 255, 255) none repeat scroll 0%; -moz-background-clip: initial; -moz-background-origin: initial; -moz-background-inline-policy: initial; font-size: x-small">{$APP.Files_Maximum_6}
						<input id="my_file_element" type="file" name="file_1" tabindex="{$vt_tab}" >
						{assign var=image_count value=0}
						{if $maindata[3].0.name neq ''}
						   {foreach name=image_loop key=num item=image_details from=$maindata[3]}
							<div align="center">
								<img src="{$image_details.path}{$image_details.name}" height="50">&nbsp;&nbsp;[{$image_details.name}]<input id="file_{$num}" value="Delete" type="button" class="crmbutton small delete" onclick='this.parentNode.parentNode.removeChild(this.parentNode);delRowEmt("{$image_details.name}")'>
							</div>
					   	   {assign var=image_count value=$smarty.foreach.image_loop.iteration}
					   	   {/foreach}
						{/if}
					</div>

					<script>
						<!-- Create an instance of the multiSelector class, pass it the output target and the max number of files -->
						var multi_selector = new MultiSelector( document.getElementById( 'files_list' ), 6 );
						multi_selector.count = {$image_count}
						<!-- Pass in the file element -->
						multi_selector.addElement( document.getElementById( 'my_file_element' ) );
					</script>
				{else}
					<input name="{$fldname}"  type="file" value="{$maindata[3].0.name}" tabindex="{$vt_tab}" />
					<input type="hidden" name="id" value=""/>
					{ if $maindata[3].0.name != "" }
						
				<div id="replaceimage">[{$maindata[3].0.name}] <a href="javascript:;" onClick="delimage({$ID})">Del</a></div>
					{/if}
					
				{/if}

		{elseif $uitype eq 61}
				<input name="{$fldname}"  type="file" value="{$secondvalue}" tabindex="{$vt_tab}" />
				<input type="hidden" name="id" value="" OnBlur="changeMassUpdateValue(this.value);"/>{$fldvalue}
		{elseif $uitype eq 156}
			 {if $fldvalue eq 'on'}
					
						{if ($secondvalue eq 1 && $CURRENT_USERID != $smarty.request.record) || ($MODE == 'create')}
							<input name="{$fldname}" tabindex="{$vt_tab}" type="checkbox" checked>
						{else}
							<input name="{$fldname}" type="hidden" value="on">
							<input name="{$fldname}" disabled tabindex="{$vt_tab}" type="checkbox" checked>
						{/if}	
				{else}
						{if ($secondvalue eq 1 && $CURRENT_USERID != $smarty.request.record) || ($MODE == 'create')}
							<input name="{$fldname}" tabindex="{$vt_tab}" type="checkbox">
						{else}
							<input name="{$fldname}" disabled tabindex="{$vt_tab}" type="checkbox">
						{/if}	
				{/if}
		{elseif $uitype eq 98}<!-- Role Selection Popup -->		
			{if $thirdvalue eq 1}
				<input name="role_name" id="role_name" readonly class="txtBox" tabindex="{$vt_tab}" value="{$secondvalue|escape}" type="text">&nbsp;
				<a href="javascript:openPopup();"><img src="{$IMAGE_PATH}select.gif" align="absmiddle" border="0"></a>
			{else}	
				<input name="role_name" id="role_name" tabindex="{$vt_tab}" class="txtBox" readonly value="{$secondvalue|escape}" type="text">&nbsp;
			{/if}	
			<input name="user_role" id="user_role" value="{$fldvalue}" type="hidden">
		{elseif $uitype eq 104}<!-- Mandatory Email Fields -->			
			 <input type="text" name="{$fldname}" id ="{$fldname}" value="{$fldvalue|escape}" OnBlur="changeMassUpdateValue(this.value);"  class="detailedViewTextBoxQuick" tabindex="{$vt_tab}" >
			{elseif $uitype eq 115}<!-- for Status field Disabled for nonadmin -->
			   {if $secondvalue eq 1 && $CURRENT_USERID != $smarty.request.record}
			   	<select id="user_status" name="{$fldname}" tabindex="{$vt_tab}" class="small">
			   {else}
			   	<select id="user_status" disabled name="{$fldname}" class="small">
			   {/if} 
				{foreach item=arr from=$fldvalue}
					{foreach key=sel_value item=value from=$arr}
						<option value="{$sel_value}" {$value}>{$sel_value}</option>
					{/foreach}
				{/foreach}
			   </select>
			{elseif $uitype eq 105}
		
				{if $MODE eq 'edit' && $IMAGENAME neq ''}
					<input name="{$fldname}"  type="file" value="{$maindata[3].0.name}" tabindex="{$vt_tab}" />[{$IMAGENAME}]<br>{$APP.LBL_IMG_FORMATS}
				{else}
					<input name="{$fldname}"  type="file" value="{$maindata[3].0.name}" tabindex="{$vt_tab}" /><br>{$APP.LBL_IMG_FORMATS}
				{/if}
					<input type="hidden" name="id" value=""/>
					{$maindata[3].0.name}

			{elseif $uitype eq 103}
				<input type="text" class="detailedViewTextBoxQuick" name="{$fldname}" value="{$fldvalue|escape}" OnBlur="changeMassUpdateValue(this.value);"  tabindex="{$vt_tab}" >
			{elseif $uitype eq 101}<!-- for reportsto field USERS POPUP -->
				
				<input readonly name='reports_to_name' class="detailedViewTextBoxQuick" class="small" type="text" value='{$fldvalue|escape}' tabindex="{$vt_tab}" ><input name='reports_to_id' type="hidden" value='{$secondvalue}'>&nbsp;<input title="Change [Alt+C]" accessKey="C" type="button" class="small" value='{$UMOD.LBL_CHANGE}' name=btn1 LANGUAGE=javascript onclick='openPopup("index.php?module=Users&action=Popup&form=UsersEditView&form_submit=false","test","width=640,height=522,resizable=0,scrollbars=0");'>{* crmv@21048m *}
			{elseif $uitype eq 116}<!-- for currency in users details-->	
			   {if $secondvalue eq 1}
			   	<select name="{$fldname}" tabindex="{$vt_tab}" class="small">
			   {else}
			   	<select disabled name="{$fldname}" tabindex="{$vt_tab}" class="small">
			   {/if} 

				{foreach item=arr key=uivalueid from=$fldvalue}
					{foreach key=sel_value item=value from=$arr}
						<option value="{$uivalueid}" {$value}>{$sel_value}</option>
						<!-- code added to pass Currency field value, if Disabled for nonadmin -->
						{if $value eq 'selected' && $secondvalue neq 1}
							{assign var="curr_stat" value="$uivalueid"}
						{/if}
						<!--code ends -->
					{/foreach}
				{/foreach}
			   </select>
			<!-- code added to pass Currency field value, if Disabled for nonadmin -->
			{if $curr_stat neq ''}
				<input name="{$fldname}" type="hidden" value="{$curr_stat}">
			{/if}
			<!--code ends -->
			{elseif $uitype eq 106}
				{if $MODE eq 'edit'}
				<input type="text" readonly name="{$fldname}" value="{$fldvalue|escape}" OnBlur="changeMassUpdateValue(this.value);"  tabindex="{$vt_tab}" >
				{else}
				<input type="text" name="{$fldname}" value="{$fldvalue|escape}" OnBlur="changeMassUpdateValue(this.value);"  tabindex="{$vt_tab}" >
				{/if}
			{elseif $uitype eq 99}
				{if $MODE eq 'create'}
						<input type="password" name="{$fldname}" tabindex="{$vt_tab}" value="{$fldvalue}" >
				{/if}
		{elseif $uitype eq 30}
			
				{assign var=check value=$secondvalue[0]}
				{assign var=yes_val value=$secondvalue[1]}
				{assign var=no_val value=$secondvalue[2]}

				<input type="radio" name="set_reminder" tabindex="{$vt_tab}" value="Yes" {$check}>&nbsp;{$yes_val}&nbsp;
				<input type="radio" name="set_reminder" value="No">&nbsp;{$no_val}&nbsp;

				{foreach item=val_arr from=$fldvalue}
					{assign var=start value="$val_arr[0]"}
					{assign var=end value="$val_arr[1]"}
					{assign var=sendname value="$val_arr[2]"}
					{assign var=disp_text value="$val_arr[3]"}
					{assign var=sel_val value="$val_arr[4]"}
					<select name="{$sendname}" class="small">
						{section name=reminder start=$start max=$end loop=$end step=1 }
							{if $smarty.section.reminder.index eq $sel_val}
								{assign var=sel_value value="SELECTED"}
							{else}
								{assign var=sel_value value=""}
							{/if}
							<OPTION VALUE="{$smarty.section.reminder.index}" "{$sel_value}">{$smarty.section.reminder.index}</OPTION>
						{/section}
					</select>
					&nbsp;{$disp_text}
				{/foreach}
		{elseif $uitype eq 255} 		

			<input type="text" name="{$fldname}" id="{$fldname}" tabindex="{$vt_tab}" class=detailedViewTextBox onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'" style="width:58%;" OnBlur="changeMassUpdateValue(this.value);">
			
		{elseif $uitype eq 83} <!-- Handle the Tax in Inventory -->
		   <table>
			{foreach item=tax key=count from=$TAX_DETAILS}
			<tr>
				{if $tax.check_value eq 1}
					{assign var=check_value value="checked"}
					{assign var=show_value value="visible"}
				{else}
					{assign var=check_value value=""}
					{assign var=show_value value="hidden"}
				{/if}
				<td align="right" class="dvtCellLabel" style="border:0px solid red;">
					{$tax.taxlabel} {$APP.COVERED_PERCENTAGE}
					<input type="checkbox" name="{$tax.check_name}" id="{$tax.check_name}" class="small" onclick="fnshowHide(this,'{$tax.taxname}')" {$check_value}>
				</td>
				<td class="dvtCellInfo" align="left" style="border:0px solid red;">
					<input type="text" name="{$tax.taxname}" id="{$tax.taxname}" value="{$tax.percentage}" style="visibility:{$show_value};" onBlur="fntaxValidation('{$tax.taxname}')">
				</td>
			   </tr>
			   
			{/foreach}
			</table>
			
		{/if}
	{/foreach}
   </tr>
{/foreach}

<script language="javascript">
	function fnshowHide(currObj,txtObj)
	{ldelim}
			if(currObj.checked == true)
				document.getElementById(txtObj).style.visibility = 'visible';
			else
				document.getElementById(txtObj).style.visibility = 'hidden';
	{rdelim}
	
	function fntaxValidation(txtObj)
	{ldelim}
			if (!numValidate(txtObj,"Tax","any"))
				document.getElementById(txtObj).value = 0;
	{rdelim}	

function delimage(id)
{ldelim}
	new Ajax.Request(
		'index.php',
		{ldelim}queue: {ldelim}position: 'end', scope: 'command'{rdelim},
			method: 'post',
			postBody: 'module=Contacts&action=ContactsAjax&file=DelImage&recordid='+id,
			onComplete: function(response)
				    {ldelim}
					if(response.responseText.indexOf("SUCESS")>-1)
						$("replaceimage").innerHTML='{$APP.LBL_IMAGE_DELETED}';
					else
						alert("{$APP.ERROR_WHILE_EDITING}")
				    {rdelim}
		{rdelim}
	);

{rdelim}

</script>

