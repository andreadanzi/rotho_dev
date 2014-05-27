{if $typeofdata eq 'M'}
	{assign var="mandatory_field" value="*"}
{else}
	{assign var="mandatory_field" value=""}
{/if}

		{* vtlib customization: Help information for the fields *}
		{assign var="usefldlabel" value=$fldlabel}
		{assign var="fldhelplink" value=""}
		{if $FIELDHELPINFO && $FIELDHELPINFO.$fldname}
			{assign var="fldhelplinkimg" value='help_icon.gif'|@vtiger_imageurl:$THEME}
			{assign var="fldhelplink" value="<img style='cursor:pointer' onclick='vtlib_field_help_show(this, \"$fldname\");' border=0 src='$fldhelplinkimg'>"}
			{if $uitype neq '10'}
				{assign var="usefldlabel" value="$fldlabel $fldhelplink"}
			{/if}
		{/if}
		{* END *}

		{* crmv@sdk-18509 *}
		{if $SDK->isUitype($uitype) eq 'true'}
			{assign var="sdk_mode" value="edit"}
			{assign var="sdk_file" value=$SDK->getUitypeFile('tpl',$sdk_mode,$uitype)}
			{if $sdk_file neq ''}
				{include file=$sdk_file}
			{/if}
		{* crmv@sdk-18509 e *}
		{* vtlib customization *}
		{elseif $uitype eq '10'}
			{assign var="popup_params" value="&action=Popup&html=Popup_picker&form=vtlibPopupView&forfield=$fldname&srcmodule=$MODULE&forrecord=$ID"}	{* crmv@29190 *}
			<td width=20% class="dvtCellLabel" align=right>
			<font color="red">{$mandatory_field}</font>
			{$fldlabel.displaylabel}
			{if count($fldlabel.options) eq 1}
				{assign var="use_parentmodule" value=$fldlabel.options.0}
				<!-- //crmv@16312 -->
				<input type='hidden' class='small' name="{$fldname}_type" value="{$use_parentmodule}">
				<!-- //crmv@16312 end -->
			{else}
				{if $fromlink eq 'qcreate'}
					<select id="{$fldname}_type" class="small" name="{$fldname}_type" onChange='reloadAutocomplete("{$fldname}","{$fldname}_display","module="+this.value+"{$popup_params}"); document.QcEditView.{$fldname}_display.value=""; document.QcEditView.{$fldname}.value=""; enableReferenceField(document.QcEditView.{$fldname}_display);'>	{* crmv@29190 *}
				{else}
					<select id="{$fldname}_type" class="small" name="{$fldname}_type" onChange='reloadAutocomplete("{$fldname}","{$fldname}_display","module="+this.value+"{$popup_params}"); document.EditView.{$fldname}_display.value=""; document.EditView.{$fldname}.value="";$("qcform").innerHTML=""; enableReferenceField(document.EditView.{$fldname}_display);'>	{* crmv@29190 *}
				{/if}
				{foreach item=option from=$fldlabel.options}
					<option value="{$option}"
						{if $fldlabel.selected == $option}selected{/if}>
						{$option|@getTranslatedString:$MODULE}
					</option>
				{/foreach}
				</select>
			{/if}
			{if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
			{$fldhelplink}
			</td>
			<td width="30%" align=left class="dvtCellInfo" nowrap> {* crmv@22583 *}
				{* crmv@21048m *}	{* crmv@29190 *}
				<input id="{$fldname}" name="{$fldname}" type="hidden" value="{$fldvalue.entityid}">
				{assign var=fld_displayvalue value=$fldvalue.displayvalue}
				{assign var=fld_style value='class="detailedViewTextBox detailedViewReference detailedViewReferenceRO" readonly'}
				{if $fld_displayvalue|trim eq ''}
					{assign var=fld_displayvalue value='LBL_SEARCH_STRING'|getTranslatedString}
					{assign var=fld_style value='class="detailedViewTextBox detailedViewReference"'}
				{/if}
				<input id="{$fldname}_display" name="{$fldname}_display" type="text" value="{$fld_displayvalue}" {$fld_style}>
				{if $fromlink eq 'qcreate'}
					{assign var="editViewType" value="QcEditView"}
				{else}
					{assign var="editViewType" value="EditView"}
				{/if}
				<script type="text/javascript">
				var sdk_popup_hidden_elements = eval({$SDK->getPopupHiddenElements($MODULE,$fldname,'autocomplete')});
				reloadAutocomplete('{$fldname}','{$fldname}_display',"module="+document.{$editViewType}.{$fldname}_type.value+"{$popup_params}",sdk_popup_hidden_elements);
				</script>
				<img src="{'select.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='openPopup("index.php?module="+document.{$editViewType}.{$fldname}_type.value+"&action=Popup&html=Popup_picker&form=vtlibPopupView&forfield={$fldname}&srcmodule={$MODULE}&forrecord={$ID}{$SDK->getPopupHiddenElements($MODULE,$fldname)}","test","width=640,height=602,resizable=0,scrollbars=0,top=150,left=200");' align="absmiddle" style='cursor:hand;cursor:pointer'>
				{* crmv@37211 *}
				{if $fromlink eq 'qcreate'}
					<img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE="javascript" onClick="document.QcEditView.{$fldname}.value=''; document.QcEditView.{$fldname}_display.value=''; enableReferenceField(document.QcEditView.{$fldname}_display); return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
				{else}
					<img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE="javascript" onClick="document.EditView.{$fldname}.value=''; document.EditView.{$fldname}_display.value=''; enableReferenceField(document.EditView.{$fldname}_display); return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
				{/if}
				{* crmv@37211e *}
				{* crmv@21048me *}	{* crmv@29190e *}
			</td>
		{* END *}
{elseif $uitype eq 2}
	<td width=20% class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel} {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small">{/if}
	</td>
	<td width=30% align=left class="dvtCellInfo">
		<input type="text" name="{$fldname}" tabindex="{$vt_tab}" value="{$fldvalue}" tabindex="{$vt_tab}" class=detailedViewTextBox onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'">
	</td>
	<!--   //crmv@8056 -->
{elseif $uitype eq 3 || $uitype eq 4}<!-- Non Editable field, only configured value will be loaded -->
	<td width=20% class="dvtCellLabel" align=right><font color="red">{$mandatory_field}</font>{$usefldlabel} {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small">{/if}</td>
    <td width=30% align=left class="dvtCellInfo">
    <input readonly type="text" tabindex="{$vt_tab}" name="{$fldname}" id ="{$fldname}" {if $MODE eq 'edit'} value="{$fldvalue}" {else} value="{$MOD_SEQ_ID}" {/if} class=detailedViewTextBox onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'"></td>
<!--   //crmv@8056e -->
 <!--   //crmv@7231 - crmv@7216 crmv@7220-->
{elseif $uitype eq 11 || $uitype eq 1 || $uitype eq 13 || $uitype eq 7 || $uitype eq 9 || $uitype eq 1112 || $uitype eq 1013 || $uitype eq 1014}
	<td width=20% class="dvtCellLabel" align=right><font color="red">{$mandatory_field}</font>{$usefldlabel}
		{if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	{if $fldname eq 'tickersymbol' && $MODULE eq 'Accounts'}
		<td width=30% align=left class="dvtCellInfo">
			<input type="text" name="{$fldname}" tabindex="{$vt_tab}" id ="{$fldname}" value="{$fldvalue}" class=detailedViewTextBox onFocus="this.className='detailedViewTextBoxOn';" onBlur="this.className='detailedViewTextBox';{if $fldname eq 'tickersymbol' && $MODULE eq 'Accounts'}sensex_info(){/if}">
			<span id="vtbusy_info" style="display:none;">
				<img src="{$IMAGE_PATH}vtbusy.gif" border="0"></span>
		</td>
	{else}
		<!--   //crmv@7231 -->
		{if $uitype eq 1112 && ($fldvalue neq '' && $fldvalue neq '--None--') }
			<td width=30% align=left class="dvtCellInfo" >{$fldvalue}<input type="hidden"  name="{$fldname}" id ="{$fldname}" value="{$fldvalue}"></td>
		{else}
			<td width=30% align=left class="dvtCellInfo"><input type="text" tabindex="{$vt_tab}" name="{$fldname}" id ="{$fldname}" value="{$fldvalue}" class=detailedViewTextBox onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'"></td>
		{/if}
	{/if}
{elseif $uitype eq 19 || $uitype eq 20}
	<!-- In Add Comment are we should not display anything -->
	{if $fldlabel eq $MOD.LBL_ADD_COMMENT}
		{assign var=fldvalue value=""}
	{/if}
	<td width=20% class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td colspan=3>
		<!-- crmv@manuele -->
		{if $MOBILE eq 'yes'}
			{assign var=cols value="25"}
		{else}
			{assign var=cols value="90"}
		{/if}
		<!-- crmv@manuele -->
		<textarea class="detailedViewTextBox" tabindex="{$vt_tab}" onFocus="this.className='detailedViewTextBoxOn'" name="{$fldname}"  onBlur="this.className='detailedViewTextBox'" cols="{$cols}" rows="8">{$fldvalue}</textarea>
		{if $fldlabel eq $MOD.Solution}
		<input type = "hidden" name="helpdesk_solution" value = '{$fldvalue}'>
		{/if}
	</td>

{elseif $uitype eq 21 || $uitype eq 24}
	<td width=20% class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width=30% align=left class="dvtCellInfo">
		<textarea value="{$fldvalue}" name="{$fldname}" tabindex="{$vt_tab}" class=detailedViewTextBox onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'" rows=2>{$fldvalue}</textarea>
	</td>
{* <!-- ds@8 project tool --> *}
{elseif $uitype eq 25}
	<td width=20% class="dvtCellLabel" align=right>
				<font color="red">{$mandatory_field}</font>{$usefldlabel}
			</td>
			<td width=30% align=left class="dvtCellInfo">
				<textarea readonly value="{$fldvalue|escape}" name="{$fldname}" tabindex="{$vt_tab}" rows="2" cols="5">{$fldvalue|escape}</textarea>
         		<input type="hidden" name="projects_ids" value="{$PROJECTS_IDS}">
         		<button onclick='openPopup("index.php?module=Projects&action=Popup&html=Popup_picker&form=HelpDeskEditView&select=enable&record={$RECORD}","test","width=640,height=602,resizable=0,scrollbars=0,top=150,left=200"); return false;' class="crmbutton small save" style="margin-left:150px;">{$MOD.choose}</button>{* crmv@21048m *}
         	</td>
{* <!--  ds@8e --> *}
<!-- //crmv@8982 -->
{elseif $uitype eq 1099}
 	<td width="20%" class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
	</td>
	<td width="30%" align=left class="dvtCellInfo">
		<input type="hidden" value="{$fldvalue}" name="{$fldname}" id="{$fldname}" tabindex="{$vt_tab}">
		{$secondvalue}
	</td>
{elseif $uitype eq 15 || $uitype eq 1015 || $uitype eq 16 || $uitype eq 111 || $uitype eq 54} <!-- uitype 111 added for noneditable existing picklist values - ahmed --> {* <!-- DS-ED VlMe 31.3.2008 - add uitype 504 --> *}
	<td width="20%" class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>
		{$usefldlabel} {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width="30%" align=left class="dvtCellInfo">
		{if $MODULE eq 'Calendar'}
	   		<select name="{$fldname}" tabindex="{$vt_tab}" class="small" style="width:160px;">
		{else}
	   		<select name="{$fldname}" tabindex="{$vt_tab}" class="small">
	   	{/if}
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
		{foreachelse}
			<option value=""></option>
			<option value="" style='color: #777777' disabled>{$APP.LBL_NONE}</option>
		{/foreach}
	   </select>
	</td>
{elseif $uitype eq 33}
	  <td width="20%" align=right class="dvtCellLabel">
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width="30%" align=left class="dvtCellInfo">
	   <select MULTIPLE name="{$fldname}[]" size="4" style="width:160px;" tabindex="{$vt_tab}" class="small">
		{foreach item=arr from=$fldvalue}
			<option value="{$arr[1]}" {$arr[2]}>
                                                {$arr[0]}
                                        </option>
		{/foreach}
	   </select>
	</td>
		{* crmv@31171 *}
		{elseif $uitype eq 53}
			{assign var="editViewType" value="QcEditView"}
			{if $fromlink eq 'qcreate'}
				{assign var="editViewType" value="QcEditView"}
			{else}
				{assign var="editViewType" value="EditView"}
			{/if}
			{php}$this->assign('JSON',new Zend_Json());{/php}
			{assign var="popup_params" value="&action=Popup&html=Popup_picker&form=vtlibPopupView&forfield=$fldname&srcmodule=$MODULE&forrecord=$ID"}	{* crmv@29190 *}
			<td width="20%" class="dvtCellLabel" align=right>
				<font color="red">{$mandatory_field}</font>{$usefldlabel} {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
				{assign var=check value=1}
				{foreach key=key_one item=arr from=$fldvalue}
					{foreach key=sel_value item=value from=$arr}
						{if $value ne ''}
							{assign var=check value=$check*0}
						{else}
							{assign var=check value=$check*1}
						{/if}
					{/foreach}
				{/foreach}
				{if $check eq 0}
					{assign var=select_user value='selected="selected"'}
					{assign var=select_group value=''}
					{assign var=style_user value='display:block'}
					{assign var=style_group value='display:none'}
				{else}
					{assign var=select_user value=''}
					{assign var=select_group value='selected="selected"'}
					{assign var=style_user value='display:none'}
					{assign var=style_group value='display:block'}
				{/if}

				{if $secondvalue neq ''}
					{if $fromlink eq 'qcreate'}
						<select id="{$fldname}_type" class="small" name="assigntype" onChange='toggleAssignType(this.value); document.QcEditView.{$fldname}_display.value=""; document.QcEditView.{$fldname}.value="0"; enableReferenceField(document.QcEditView.{$fldname}_display); document.QcEditView.assigned_group_id_display.value=""; document.QcEditView.assigned_group_id.value=""; enableReferenceField(document.QcEditView.assigned_group_id_display); closeAutocompleteList("{$fldname}_display"); closeAutocompleteList("assigned_group_id_display");'>	{* crmv@29190 *}
					{else}
						<select id="{$fldname}_type" class="small" name="assigntype" onChange='toggleAssignType(this.value); document.EditView.{$fldname}_display.value=""; document.EditView.{$fldname}.value="0"; enableReferenceField(document.EditView.{$fldname}_display); document.EditView.assigned_group_id_display.value=""; document.EditView.assigned_group_id.value=""; enableReferenceField(document.EditView.assigned_group_id_display); closeAutocompleteList("{$fldname}_display"); closeAutocompleteList("assigned_group_id_display");'>	{* crmv@29190 *}
					{/if}
						<option value="U" {$select_user}>{$APP.LBL_USER}</option>
						<option value="T" {$select_group}>{$APP.LBL_GROUP}</option>
					</select>
				{else}
					<input type="hidden" id="{$fldname}_type" name="assigntype" value="U">
				{/if}
			</td>
			<td width="30%" align=left class="dvtCellInfo">

				<span id="assign_user" style="{$style_user}">
					{assign var=fld_value value="0"}
					{foreach key=key_one item=arr from=$fldvalue}
						{foreach key=sel_value item=value from=$arr}
							{if $value eq 'selected'}
								{assign var=fld_value value=$key_one}
								{assign var=fld_displayvalue value=$sel_value}
							{/if}
						{/foreach}
					{/foreach}
					<input id="{$fldname}" name="{$fldname}" type="hidden" value="{$fld_value}">
					{assign var=fld_style value='class="detailedViewTextBox detailedViewReference detailedViewReferenceRO" readonly'}
					{if $fld_displayvalue|trim eq ''}
						{assign var=fld_displayvalue value='LBL_SEARCH_STRING'|getTranslatedString}
						{assign var=fld_style value='class="detailedViewTextBox detailedViewReference"'}
					{/if}
					<input id="{$fldname}_display" name="{$fldname}_display" type="text" value="{$fld_displayvalue}" {$fld_style}>
					<script type="text/javascript">
					initAutocompleteUG('Users','{$fldname}','{$fldname}_display','{$JSON->encode($fldvalue)|addslashes}');	{* crmv@31950 *}
					</script>
					&nbsp;<img src="{'select.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='toggleAutocompleteList("{$fldname}_display");' align="absmiddle" style='cursor:hand;cursor:pointer'>
					{* crmv@37211 *}
					{if $fromlink eq 'qcreate'}
						<img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE="javascript" onClick="document.QcEditView.{$fldname}.value=''; document.QcEditView.{$fldname}_display.value=''; enableReferenceField(document.QcEditView.{$fldname}_display); return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
					{else}
						<img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE="javascript" onClick="document.EditView.{$fldname}.value=''; document.EditView.{$fldname}_display.value=''; enableReferenceField(document.EditView.{$fldname}_display); return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
					{/if}
					{* crmv@37211e *}
				</span>

				{if $secondvalue neq ''}
					<span id="assign_team" style="{$style_group}">
						{assign var=fld_secondvalue value="0"}
						{foreach key=key_one item=arr from=$secondvalue}
							{foreach key=sel_value item=value from=$arr}
								{if $value eq 'selected'}
									{assign var=fld_secondvalue value=$key_one}
									{assign var=fld_displaysecondvalue value=$sel_value}
								{/if}
							{/foreach}
						{/foreach}
						<input id="assigned_group_id" name="assigned_group_id" type="hidden" value="{$fld_secondvalue}">
						{assign var=fld_style value='class="detailedViewTextBox detailedViewReference detailedViewReferenceRO" readonly'}
						{if $fld_displaysecondvalue|trim eq ''}
							{assign var=fld_displaysecondvalue value='LBL_SEARCH_STRING'|getTranslatedString}
							{assign var=fld_style value='class="detailedViewTextBox detailedViewReference"'}
						{/if}
						<input id="assigned_group_id_display" name="assigned_group_id_display" type="text" value="{$fld_displaysecondvalue}" {$fld_style}>
						<script type="text/javascript">
						initAutocompleteUG('Groups','assigned_group_id','assigned_group_id_display','{$JSON->encode($secondvalue)|addslashes}');	{* crmv@31950 *}
						</script>
						&nbsp;<img src="{'select.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='toggleAutocompleteList("assigned_group_id_display");' align="absmiddle" style='cursor:hand;cursor:pointer'>
						{* crmv@37211 *}
						{if $fromlink eq 'qcreate'}
							<img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE="javascript" onClick="document.QcEditView.assigned_group_id.value=''; document.QcEditView.assigned_group_id_display.value=''; enableReferenceField(document.QcEditView.assigned_group_id_display); return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
						{else}
							<img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE="javascript" onClick="document.EditView.assigned_group_id.value=''; document.EditView.assigned_group_id_display.value=''; enableReferenceField(document.EditView.assigned_group_id_display); return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
						{/if}
						{* crmv@37211e *}
					</span>
				{/if}
			</td>
		{* crmv@31171e *}
{elseif $uitype eq 52 || $uitype eq 77 || $uitype eq 1077}
	<td width="20%" class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width="30%" align=left class="dvtCellInfo">
		{if $uitype eq 52}
			<select name="{$fldname}" class="small">
		{elseif $uitype eq 77 || $uitype eq 1077}
			{*<select name="assigned_user_id1" tabindex="{$vt_tab}" class="small">*}
			{*mycrmv@rotho_blaas danzi.tn@20140220*}
			{if $fldname eq 'agente_riferimento'}
				<select name="{$fldname}" tabindex="{$vt_tab}" class="small" onChange="changeCapoarea()">
			{else}
				<select name="{$fldname}" tabindex="{$vt_tab}" class="small">
			{/if}
			{*mycrmv@rotho_blaase danzi.tn@20140220e*}
		{else}
			<select name="{$fldname}" tabindex="{$vt_tab}" class="small">
		{/if}

		{foreach key=key_one item=arr from=$fldvalue}
			{foreach key=sel_value item=value from=$arr}
				<option value="{$key_one}" {$value}>{$sel_value}</option>
			{/foreach}
		{/foreach}
		</select>
	</td>
{elseif $uitype eq 51}
	{if $MODULE eq 'Accounts'}
		{assign var='popuptype' value = 'specific_account_address'}
	{else}
		{assign var='popuptype' value = 'specific_contact_account_address'}
	{/if}
	<td width="20%" class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width="30%" align=left class="dvtCellInfo" nowrap> {* crmv@22583 *}
		{* crmv@21048m *}	{* crmv@29190 *}
		<input id="{$fldname}" name="{$fldname}" type="hidden" value="{$secondvalue}">
		{assign var=fld_displayvalue value=$fldvalue}
		{assign var=fld_style value='class="detailedViewTextBox detailedViewReference detailedViewReferenceRO" readonly'}
		{if $fld_displayvalue|trim eq ''}
			{assign var=fld_displayvalue value='LBL_SEARCH_STRING'|getTranslatedString}
			{assign var=fld_style value='class="detailedViewTextBox detailedViewReference"'}
		{/if}
		<input id="{$fldname}_display" name="account_name" type="text" value="{$fld_displayvalue}" {$fld_style}>
		{assign var="popup_params" value="module=Accounts&action=Popup&popuptype=$popuptype&form=TasksEditView&form_submit=false&fromlink=$fromlink&recordid=$ID"}
		<script type="text/javascript">
		initAutocomplete('{$fldname}','{$fldname}_display',encodeURIComponent('{$popup_params}'));
		</script>
		<img src="{'select.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='openPopup("index.php?{$popup_params}","test","width=640,height=602,resizable=0,scrollbars=0,top=150,left=200");' align="absmiddle" style='cursor:hand;cursor:pointer'>
		{* crmv@37211 *}
		{if $fromlink eq 'qcreate'}
			<img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE="javascript" onClick="document.QcEditView.{$fldname}.value=''; document.QcEditView.account_name.value=''; enableReferenceField(document.QcEditView.account_name); return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
		{else}
			<img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE="javascript" onClick="document.EditView.{$fldname}.value=''; document.EditView.account_name.value=''; enableReferenceField(document.EditView.account_name); return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
		{/if}
		{* crmv@37211e *}
		{* crmv@21048me *}	{* crmv@29190e *}
	</td>
<!-- crmv@8839 -->
<!-- //###---insert_here_uitypeNewPopupFieldIts4YouModule--- -->
<!-- crmv@8839e -->
{elseif $uitype eq 73}
	<td width="20%" class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width="30%" align=left class="dvtCellInfo">
		{* crmv@21048m *}	{* crmv@29190 *}
		<input id="{$fldname}" name="{$fldname}" type="hidden" value="{$secondvalue}">
		{assign var=fld_displayvalue value=$fldvalue}
		{assign var=fld_style value='class="detailedViewTextBox detailedViewReference detailedViewReferenceRO" readonly'}
		{if $fld_displayvalue|trim eq ''}
			{assign var=fld_displayvalue value='LBL_SEARCH_STRING'|getTranslatedString}
			{assign var=fld_style value='class="detailedViewTextBox detailedViewReference"'}
		{/if}
		<input id="{$fldname}_display" name="account_name" type="text" value="{$fld_displayvalue}" {$fld_style}>
		{if $MODULE eq 'Projects'}
			{assign var="popup_params" value="module=Accounts&action=Popup&popuptype=specific_account_noaddress&form=TasksEditView&form_submit=false&fromlink=$fromlink"}
		{else}
			{assign var="popup_params" value="module=Accounts&action=Popup&popuptype=specific_account_address&form=TasksEditView&form_submit=false&fromlink=$fromlink"}
		{/if}
		<script type="text/javascript">
		initAutocomplete('{$fldname}','{$fldname}_display',encodeURIComponent('{$popup_params}'));
		</script>
		<img src="{'select.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='openPopup("index.php?{$popup_params}","test","width=640,height=602,resizable=0,scrollbars=0,top=150,left=200");' align="absmiddle" style='cursor:hand;cursor:pointer'>
		{* crmv@37211 *}
		{if $fromlink eq 'qcreate'}
			<img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE="javascript" onClick="document.QcEditView.{$fldname}.value=''; document.QcEditView.account_name.value=''; enableReferenceField(document.QcEditView.account_name); return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
		{else}
			<img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE="javascript" onClick="document.EditView.{$fldname}.value=''; document.EditView.account_name.value=''; enableReferenceField(document.EditView.account_name); return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
		{/if}
		{* crmv@37211e *}
		{* crmv@21048me *}	{* crmv@29190e *}
	</td>
{elseif $uitype eq 75 || $uitype eq 81}
	<td width="20%" class="dvtCellLabel" align=right>
		{if $uitype eq 81}
			{assign var="pop_type" value="specific_vendor_address"}
			{else}{assign var="pop_type" value="specific"}
		{/if}
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width="30%" align=left class="dvtCellInfo">
		{* crmv@21048m *}	{* crmv@29190 *}
		<input id="{$fldname}" name="{$fldname}" type="hidden" value="{$secondvalue}">
		{assign var=fld_displayvalue value=$fldvalue}
		{assign var=fld_style value='class="detailedViewTextBox detailedViewReference detailedViewReferenceRO" readonly'}
		{if $fld_displayvalue|trim eq ''}
			{assign var=fld_displayvalue value='LBL_SEARCH_STRING'|getTranslatedString}
			{assign var=fld_style value='class="detailedViewTextBox detailedViewReference"'}
		{/if}
		<input id="{$fldname}_display" name="vendor_name" type="text" value="{$fld_displayvalue}" {$fld_style}>
		{assign var="popup_params" value="module=Vendors&action=Popup&html=Popup_picker&popuptype=$pop_type&form=EditView&fromlink=$fromlink"}
		<script type="text/javascript">
		initAutocomplete('{$fldname}','{$fldname}_display',encodeURIComponent('{$popup_params}'));
		</script>
		<img src="{'select.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='openPopup("index.php?{$popup_params}","test","width=640,height=602,resizable=0,scrollbars=0,top=150,left=200");' align="absmiddle" style='cursor:hand;cursor:pointer'>
		{* crmv@37211 *}
		{if $fromlink eq 'qcreate'}
			<img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE="javascript" onClick="document.QcEditView.{$fldname}.value=''; document.QcEditView.vendor_name.value=''; enableReferenceField(document.QcEditView.vendor_name); return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
		{else}
			<img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE="javascript" onClick="document.EditView.{$fldname}.value=''; document.EditView.vendor_name.value=''; enableReferenceField(document.EditView.vendor_name); return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
		{/if}
		{* crmv@37211e *}
		{* crmv@21048me *}	{* crmv@29190e *}
	</td>
{elseif $uitype eq 57}
	<td width="20%" class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width="30%" align=left class="dvtCellInfo">
		{* crmv@21048m *}	{* crmv@29190 *}
		<input id="{$fldname}" name="{$fldname}" type="hidden" value="{$secondvalue}">
		{assign var=fld_displayvalue value=$fldvalue}
		{assign var=fld_style value='class="detailedViewTextBox detailedViewReference detailedViewReferenceRO" readonly'}
		{if $fld_displayvalue|trim eq ''}
			{assign var=fld_displayvalue value='LBL_SEARCH_STRING'|getTranslatedString}
			{assign var=fld_style value='class="detailedViewTextBox detailedViewReference"'}
		{/if}
		<input id="{$fldname}_display" name="contact_name" type="text" value="{$fld_displayvalue}" {$fld_style}>
		{if $fromlink eq 'qcreate'}
			{assign var="editViewType" value="QcEditView"}
		{else}
			{assign var="editViewType" value="EditView"}
		{/if}
		<script type="text/javascript">
		initAutocomplete('{$fldname}','{$fldname}_display',encodeURIComponent(selectContact("false","general",document.{$editViewType},'yes')));
		</script>
		<img src="{'select.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='openPopup("index.php?"+selectContact("false","general",document.{$editViewType}),"test","width=640,height=602,resizable=0,scrollbars=0,top=150,left=200");' align="absmiddle" style='cursor:hand;cursor:pointer'>
		<input type="image" src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE=javascript onClick="this.form.{$fldname}.value=''; this.form.contact_name.value=''; enableReferenceField(this.form.contact_name); return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
		{* crmv@21048me *}	{* crmv@29190e *}
	</td>

{elseif $uitype eq 58}
	<td width="20%" class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width="30%" align=left class="dvtCellInfo">
		{* crmv@21048m *}	{* crmv@29190 *}
		<input id="{$fldname}" name="{$fldname}" type="hidden" value="{$secondvalue}">
		{assign var=fld_displayvalue value=$fldvalue}
		{assign var=fld_style value='class="detailedViewTextBox detailedViewReference detailedViewReferenceRO" readonly'}
		{if $fld_displayvalue|trim eq ''}
			{assign var=fld_displayvalue value='LBL_SEARCH_STRING'|getTranslatedString}
			{assign var=fld_style value='class="detailedViewTextBox detailedViewReference"'}
		{/if}
		<input id="{$fldname}_display" name="campaignname" type="text" value="{$fld_displayvalue}" {$fld_style}>
		{assign var="popup_params" value="module=Campaigns&action=Popup&html=Popup_picker&popuptype=specific_campaign&form=EditView&fromlink=$fromlink"}
		<script type="text/javascript">
		initAutocomplete('{$fldname}','{$fldname}_display',encodeURIComponent('{$popup_params}'));
		</script>
		<img src="{'select.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='openPopup("index.php?{$popup_params}","test","width=640,height=602,resizable=0,scrollbars=0,top=150,left=200");' align="absmiddle" style='cursor:hand;cursor:pointer'>
		{* crmv@37211 *}
		{if $fromlink eq 'qcreate'}
			<img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE="javascript" onClick="document.QcEditView.{$fldname}.value=''; document.QcEditView.campaignname.value=''; enableReferenceField(document.QcEditView.campaignname); return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
		{else}
			<img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE="javascript" onClick="document.EditView.{$fldname}.value=''; document.EditView.campaignname.value=''; enableReferenceField(document.EditView.campaignname); return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
		{/if}
		{* crmv@37211e *}
		{* crmv@21048me *}	{* crmv@29190e *}
	</td>

{elseif $uitype eq 80}
	<td width="20%" class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width="30%" align=left class="dvtCellInfo">
		{* crmv@21048m *}	{* crmv@29190 *}
		<input id="{$fldname}" name="{$fldname}" type="hidden" value="{$secondvalue}">
		{assign var=fld_displayvalue value=$fldvalue}
		{assign var=fld_style value='class="detailedViewTextBox detailedViewReference detailedViewReferenceRO" readonly'}
		{if $fld_displayvalue|trim eq ''}
			{assign var=fld_displayvalue value='LBL_SEARCH_STRING'|getTranslatedString}
			{assign var=fld_style value='class="detailedViewTextBox detailedViewReference"'}
		{/if}
		<input id="{$fldname}_display" name="salesorder_name" type="text" value="{$fld_displayvalue}" {$fld_style}>
		{if $fromlink eq 'qcreate'}
			{assign var="editViewType" value="QcEditView"}
		{else}
			{assign var="editViewType" value="EditView"}
		{/if}
		<script type="text/javascript">
		var popup_params_{$fldname}_{$fromlink} = selectSalesOrder();
		initAutocomplete('{$fldname}','{$fldname}_display',encodeURIComponent(popup_params_{$fldname}_{$fromlink}));
		</script>
		<img src="{'select.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='openPopup("index.php?"+popup_params_{$fldname}_{$fromlink},"test","width=640,height=602,resizable=0,scrollbars=0,top=150,left=200");' align="absmiddle" style='cursor:hand;cursor:pointer'>
		<input type="image" src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE=javascript onClick="this.form.{$fldname}.value=''; this.form.salesorder_name.value=''; enableReferenceField(this.form.salesorder_name); return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
		{* crmv@37211 *}
		<img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE="javascript" onClick="document.{$editViewType}.{$fldname}.value=''; document.{$editViewType}.salesorder_name.value=''; enableReferenceField(document.{$editViewType}.salesorder_name); return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
		{* crmv@37211e *}
		{* crmv@21048me *}	{* crmv@29190e *}
	</td>

{elseif $uitype eq 78}
	<td width="20%" class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width="30%" align=left class="dvtCellInfo">
		{* crmv@21048m *}	{* crmv@29190 *}
		<input id="{$fldname}" name="{$fldname}" type="hidden" value="{$secondvalue}">
		{assign var=fld_displayvalue value=$fldvalue}
		{assign var=fld_style value='class="detailedViewTextBox detailedViewReference detailedViewReferenceRO" readonly'}
		{if $fld_displayvalue|trim eq ''}
			{assign var=fld_displayvalue value='LBL_SEARCH_STRING'|getTranslatedString}
			{assign var=fld_style value='class="detailedViewTextBox detailedViewReference"'}
		{/if}
		<input id="{$fldname}_display" name="quote_name" type="text" value="{$fld_displayvalue}" {$fld_style}>
		{if $fromlink eq 'qcreate'}
			{assign var="editViewType" value="QcEditView"}
		{else}
			{assign var="editViewType" value="EditView"}
		{/if}
		<script type="text/javascript">
		var popup_params_{$fldname}_{$fromlink} = selectQuote();
		initAutocomplete('{$fldname}','{$fldname}_display',encodeURIComponent(popup_params_{$fldname}_{$fromlink}));
		</script>
		<img src="{'select.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='openPopup("index.php?"+popup_params_{$fldname}_{$fromlink},"test","width=640,height=602,resizable=0,scrollbars=0,top=150,left=200");' align="absmiddle" style='cursor:hand;cursor:pointer'>		
		{* crmv@37211 *}
		<img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE="javascript" onClick="document.{$editViewType}.{$fldname}.value=''; document.{$editViewType}.quote_name.value=''; enableReferenceField(document.{$editViewType}.quote_name); return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
		{* crmv@37211e *}
		{* crmv@21048me *}	{* crmv@29190e *}
	</td>

{elseif $uitype eq 76}
	<td width="20%" class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width="30%" align=left class="dvtCellInfo">
		{* crmv@21048m *}	{* crmv@29190 *}
		<input id="{$fldname}" name="{$fldname}" type="hidden" value="{$secondvalue}">
		{assign var=fld_displayvalue value=$fldvalue}
		{assign var=fld_style value='class="detailedViewTextBox detailedViewReference detailedViewReferenceRO" readonly'}
		{if $fld_displayvalue|trim eq ''}
			{assign var=fld_displayvalue value='LBL_SEARCH_STRING'|getTranslatedString}
			{assign var=fld_style value='class="detailedViewTextBox detailedViewReference"'}
		{/if}
		<input id="{$fldname}_display" name="potential_name" type="text" value="{$fld_displayvalue}" {$fld_style}>
		{if $fromlink eq 'qcreate'}
			{assign var="editViewType" value="QcEditView"}
		{else}
			{assign var="editViewType" value="EditView"}
		{/if}
		<script type="text/javascript">
		var popup_params_{$fldname}_{$fromlink} = selectPotential();
		initAutocomplete('{$fldname}','{$fldname}_display',encodeURIComponent(popup_params_{$fldname}_{$fromlink}));
		</script>
		<img src="{'select.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='openPopup("index.php?"+popup_params_{$fldname}_{$fromlink},"test","width=640,height=602,resizable=0,scrollbars=0,top=150,left=200");' align="absmiddle" style='cursor:hand;cursor:pointer'>
		{* crmv@37211 *}
		<img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE="javascript" onClick="document.{$editViewType}.{$fldname}.value=''; document.{$editViewType}.potential_name.value=''; enableReferenceField(document.{$editViewType}.potential_name); return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
		{* crmv@37211e *}
		{* crmv@21048me *}	{* crmv@29190e *}
	</td>

{elseif $uitype eq 17}
	<td width="20%" class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width="30%" align=left class="dvtCellInfo">
		&nbsp;&nbsp;http://
	<input style="width:74%;" class = 'detailedViewTextBoxOn' type="text" tabindex="{$vt_tab}" name="{$fldname}" style="border:1px solid #bababa;" size="27" onFocus="this.className='detailedViewTextBoxOn'"onBlur="this.className='detailedViewTextBox'" onkeyup="validateUrl('{$fldname}');" value="{$fldvalue}">
	</td>

{elseif $uitype eq 85}
                        <td width="20%" class="dvtCellLabel" align=right>
                                <font color="red">{$mandatory_field}</font>{$usefldlabel}
                                 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
                        </td>
                        <td width="30%" align=left class="dvtCellInfo">
                                <img src="{$IMAGE_PATH}skype.gif" alt="Skype" title="Skype" LANGUAGE=javascript align="absmiddle"></img><input type="text" tabindex="{$vt_tab}" name="{$fldname}" style="border:1px solid #bababa;" size="27" onFocus="this.className='detailedViewTextBoxOn'"onBlur="this.className='detailedViewTextBox'" value="{$fldvalue}">
                        </td>

		{elseif $uitype eq 71 || $uitype eq 72}
			<td width="20%" class="dvtCellLabel" align=right>
				<font color="red">{$mandatory_field}</font>{$usefldlabel} {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
			</td>
			<td width="30%" align=left class="dvtCellInfo">
				{if $fldname eq "unit_price" && $fromlink neq 'qcreate'}
					<span id="multiple_currencies">
						<input name="{$fldname}" id="{$fldname}" tabindex="{$vt_tab}" type="text" class=detailedViewTextBox onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'; updateUnitPrice('unit_price', '{$BASE_CURRENCY}');"  value="{$fldvalue}" style="width:60%;">
					{if $MASS_EDIT neq 1}
						&nbsp;<a href="javascript:void(0);" onclick="updateUnitPrice('unit_price', '{$BASE_CURRENCY}'); toggleShowHide('currency_class','multiple_currencies');">{$APP.LBL_MORE_CURRENCIES} &raquo;</a>
					{/if}
					</span>
					{if $MASS_EDIT neq 1}
					<div id="currency_class" class="multiCurrencyEditUI" width="350">
						<input type="hidden" name="base_currency" id="base_currency" value="{$BASE_CURRENCY}" />
						<input type="hidden" name="base_conversion_rate" id="base_currency" value="{$BASE_CURRENCY}" />
						<table width="100%" height="100%" class="small" cellpadding="5">
						<tr class="detailedViewHeader">
							<th colspan="4">
								<b>{$MOD.LBL_PRODUCT_PRICES}</b>
							</th>
							<th align="right">
								<img border="0" style="cursor: pointer;" onclick="toggleShowHide('multiple_currencies','currency_class');" src="{'close.gif'|@vtiger_imageurl:$THEME}"/>
							</th>
						</tr>
						<tr class="detailedViewHeader">
							<th>{$APP.LBL_CURRENCY}</th>
							<th>{$APP.LBL_PRICE}</th>
							<th>{$APP.LBL_CONVERSION_RATE}</th>
							<th>{$APP.LBL_RESET_PRICE}</th>
							<th>{$APP.LBL_BASE_CURRENCY}</th>
						</tr>
						{foreach item=price key=count from=$PRICE_DETAILS}
							<tr>
								{if $price.check_value eq 1 || $price.is_basecurrency eq 1}
									{assign var=check_value value="checked"}
									{assign var=disable_value value=""}
								{else}
									{assign var=check_value value=""}
									{assign var=disable_value value="disabled=true"}
								{/if}

								{if $price.is_basecurrency eq 1}
									{assign var=base_cur_check value="checked"}
								{else}
									{assign var=base_cur_check value=""}
								{/if}

								{if $price.curname eq $BASE_CURRENCY}
									{assign var=call_js_update_func value="updateUnitPrice('$BASE_CURRENCY', 'unit_price');"}
								{else}
									{assign var=call_js_update_func value=""}
								{/if}

								<td align="right" class="dvtCellLabel">
									{$price.currencylabel|@getTranslatedCurrencyString} ({$price.currencysymbol})
									<input type="checkbox" name="cur_{$price.curid}_check" id="cur_{$price.curid}_check" class="small" onclick="fnenableDisable(this,'{$price.curid}'); updateCurrencyValue(this,'{$price.curname}','{$BASE_CURRENCY}','{$price.conversionrate}');" {$check_value}>
								</td>
								<td class="dvtCellInfo" align="left">
									<input {$disable_value} type="text" size="10" class="small" name="{$price.curname}" id="{$price.curname}" value="{$price.curvalue}" onBlur="{$call_js_update_func} fnpriceValidation('{$price.curname}');">
								</td>
								<td class="dvtCellInfo" align="left">
									<input disabled=true type="text" size="10" class="small" name="cur_conv_rate{$price.curid}" value="{$price.conversionrate}">
								</td>
								<td class="dvtCellInfo" align="center">
									<input {$disable_value} type="button" class="crmbutton small edit" id="cur_reset{$price.curid}"  onclick="updateCurrencyValue(this,'{$price.curname}','{$BASE_CURRENCY}','{$price.conversionrate}');" value="{$APP.LBL_RESET}"/>
								</td>
								<td class="dvtCellInfo">
									<input {$disable_value} type="radio" class="detailedViewTextBox" id="base_currency{$price.curid}" name="base_currency_input" value="{$price.curname}" {$base_cur_check} onchange="updateBaseCurrencyValue()" />
								</td>
							</tr>
						{/foreach}
						</table>
					</div>
					{/if}
				{else}
					<input name="{$fldname}" tabindex="{$vt_tab}" type="text" class=detailedViewTextBox onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'"  value="{$fldvalue}">
				{/if}
			</td>

{elseif $uitype eq 56}
	<td width="20%" class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	{* crmv@9010e *}	{* crmv@28327 *}
	{if $fldname eq 'use_ldap' && $MODULE eq 'Settings' && $MODE eq 'create'}
	    <td width="30%" align=left class="dvtCellInfo">
		<input name="{$fldname}" type="checkbox" tabindex="{$vt_tab}">
		</td>
	{else}
	{* crmv@9010e *}	{* crmv@28327e *}
		{if $fldname eq 'notime' && $ACTIVITY_MODE eq 'Events'}
			{if $fldvalue eq 1}
				<td width="30%" align=left class="dvtCellInfo">
					<input name="{$fldname}" type="checkbox" tabindex="{$vt_tab}" onclick="toggleTime()" checked>
				</td>
			{else}
				<td width="30%" align=left class="dvtCellInfo">
					<input name="{$fldname}" tabindex="{$vt_tab}" type="checkbox" onclick="toggleTime()" >
				</td>
			{/if}
		<!-- For Portal Information we need a hidden field existing_portal with the current portal value -->
		{elseif $fldname eq 'portal'}
			<td width="30%" align=left class="dvtCellInfo">
				<input type="hidden" name="existing_portal" value="{$fldvalue}">
				<input name="{$fldname}" type="checkbox" tabindex="{$vt_tab}" {if $fldvalue eq 1}checked{/if}>
			</td>
		{* mycrmv@2328m mycrmv@24524
		{elseif $fldname eq 'flg_agente'}
			<td width="30%" align=left class="dvtCellInfo">
				<script type="text/javascript">
					jQuery(document).ready(function() {ldelim}
						changeRichiedente(getObj('{$fldname}'),'parent');
					{rdelim});
				</script>
				<input name="{$fldname}" type="checkbox" onclick="changeRichiedente(this,'parent');" tabindex="{$vt_tab}" {if $fldvalue eq 1}checked{/if}>
			</td>
		mycrmv@24524e mycrmv@2328me *}
		{else}
			{if $fldvalue eq 1}
				<td width="30%" align=left class="dvtCellInfo">
					<input name="{$fldname}" type="checkbox" tabindex="{$vt_tab}" checked>
				</td>
			{elseif $fldname eq 'filestatus'&& $MODE eq 'create'}
				<td width="30%" align=left class="dvtCellInfo">
					<input name="{$fldname}" type="checkbox" tabindex="{$vt_tab}" checked>
				</td>
			{else}
				<td width="30%" align=left class="dvtCellInfo">
					<input name="{$fldname}" tabindex="{$vt_tab}" type="checkbox" {if ($PROD_MODE eq 'create' && $fldname eq 'discontinued') ||($fldname|substr:0:3 neq 'cf_' && $PRICE_BOOK_MODE eq 'create') || ($USER_MODE eq 'create' && $fldname neq 'use_asterisk')}checked{/if}>	{* crmv@26847 *}
				</td>
			{/if}
		{/if}
		<!-- crmv@9010 -->
	{/if}
	<!-- crmv@9010e -->
{elseif $uitype eq 23 || $uitype eq 5 || $uitype eq 6}
	<td width="20%" class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel} {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width="30%" align=left class="dvtCellInfo">
		{foreach key=date_value item=time_value from=$fldvalue}
			{assign var=date_val value="$date_value"}
			{assign var=time_val value="$time_value"}
		{/foreach}
		<table border="0" cellpadding="0" cellspacing="0">
		<tr>
		<td>
		<input name="{$fldname}" tabindex="{$vt_tab}" id="jscal_field_{$fldname}" type="text" style="border:1px solid #bababa;" size="11" maxlength="10" value="{$date_val}" {if $fromlink eq 'qcreate' && $fldname eq 'date_start'}onchange="parent.calDuedatetimeQC(this.form,'date');"{/if}>	{* //crmv@31315 *}
		</td>
		{* crmv@22583 *}
		<td style="padding-right:2px;">
		<img src="{'btnL3Calendar.gif'|@vtiger_imageurl:$THEME}" id="jscal_trigger_{$fldname}" >
		</td>
		{* crmv@22583e *}
		<td nowrap>
		{if $uitype eq 6}
			{php}echo getTimeCombo('am','start',date('H'),date('i'),'','',true);{/php}
			<input type="hidden" name="time_start" id="time_start" value="{php}echo date('H').':'.date('i');{/php}">
		{/if}
		{if $uitype eq 6 && $ACTIVITY_MODE eq 'Events'}
			<input name="dateFormat" type="hidden" value="{$dateFormat}">
		{/if}
		{if $uitype eq 23 && $ACTIVITY_MODE eq 'Events'}
			{php}echo getTimeCombo($current_user->hour_format,'end',date('H'),date('i'),'','',true);{/php}
			<input type="hidden" name="time_end" id="time_end" value="{php}echo date('H').':'.date('i');{/php}">
		{/if}
		</td>
		</tr>
		{foreach key=date_format item=date_str from=$secondvalue}
			{assign var=dateFormat value="$date_format"}
			{assign var=dateStr value="$date_str"}
		{/foreach}
		<tr>
		<td colspan="2">
		{if $uitype eq 5 || $uitype eq 23}
			<font size=1><em old="(yyyy-mm-dd)">({$dateStr})</em></font>
		{else}
			<font size=1><em old="(yyyy-mm-dd)">({$dateStr})</em></font>
		{/if}
		</td>
		</tr>
		</table>
		<script type="text/javascript" id='massedit_calendar_{$fldname}'>
			Calendar.setup ({ldelim}
				inputField : "jscal_field_{$fldname}", ifFormat : "{$dateFormat}", showsTime : false, button : "jscal_trigger_{$fldname}", singleClick : true, step : 1
			{rdelim})
		</script>
	</td>

{elseif $uitype eq 63}
	<td width="20%" class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width="30%" align=left class="dvtCellInfo">
		<input name="{$fldname}" type="text" size="2" value="{$fldvalue}" tabindex="{$vt_tab}" >&nbsp;
		<select name="duration_minutes" tabindex="{$vt_tab}" class="small">
			{foreach key=labelval item=selectval from=$secondvalue}
				<option value="{$labelval}" {$selectval}>{$labelval}</option>
			{/foreach}
		</select>

{elseif $uitype eq 68 || $uitype eq 66 || $uitype eq 62}
	{assign var="popup_params" value="&action=Popup&html=Popup_picker&form=HelpDeskEditView&fromlink=$fromlink"}	{* crmv@29190 *}
	<td width="20%" class="dvtCellLabel" align=right>
		{if $fromlink eq 'qcreate'}
			<select class="small" name="parent_type" onChange='reloadAutocomplete("{$fldname}","{$fldname}_display","module="+this.value+"{$popup_params}"); document.QcEditView.parent_name.value=""; document.QcEditView.parent_id.value=""; enableReferenceField(document.QcEditView.parent_name);'>	{* crmv@29190 *}
		{else}
			<select class="small" name="parent_type" onChange='reloadAutocomplete("{$fldname}","{$fldname}_display","module="+this.value+"{$popup_params}"); document.EditView.parent_name.value=""; document.EditView.parent_id.value=""; enableReferenceField(document.EditView.parent_name);'>	{* crmv@29190 *}
		{/if}
			{section name=combo loop=$fldlabel}
				<option value="{$fldlabel_combo[combo]}" {$fldlabel_sel[combo]}>{$fldlabel[combo]} </option>
			{/section}
		</select>
		{if $MASS_EDIT eq '1'}<input type="checkbox" name="parent_id_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width="30%" align=left class="dvtCellInfo">
		{* crmv@21048m *}	{* crmv@29190 *}
		<input id="{$fldname}" name="{$fldname}" type="hidden" value="{$secondvalue}">
		{assign var=fld_displayvalue value=$fldvalue}
		{assign var=fld_style value='class="detailedViewTextBox detailedViewReference detailedViewReferenceRO" readonly'}
		{if $fld_displayvalue|trim eq ''}
			{assign var=fld_displayvalue value='LBL_SEARCH_STRING'|getTranslatedString}
			{assign var=fld_style value='class="detailedViewTextBox detailedViewReference"'}
		{/if}
		<input id="{$fldname}_display" name="parent_name" type="text" value="{$fld_displayvalue}" {$fld_style}>
		{if $fromlink eq 'qcreate'}
			{assign var="editViewType" value="QcEditView"}
		{else}
			{assign var="editViewType" value="EditView"}
		{/if}
		<script type="text/javascript">
		reloadAutocomplete('{$fldname}','{$fldname}_display','module='+document.{$editViewType}.parent_type.value+'{$popup_params}');
		</script>
		<img src="{'select.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='openPopup("index.php?module="+document.{$editViewType}.parent_type.value+"&action=Popup&html=Popup_picker&form=HelpDeskEditView&fromlink={$fromlink}","test","width=640,height=602,resizable=0,scrollbars=0,top=150,left=200");' align="absmiddle" style='cursor:hand;cursor:pointer'>
		{* crmv@37211 *}
		<img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE="javascript" onClick="document.{$editViewType}.{$fldname}.value=''; document.{$editViewType}.parent_name.value=''; enableReferenceField(document.{$editViewType}.parent_name); return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
		{* crmv@37211e *}
		{* crmv@21048me *}	{* crmv@29190e *}
	</td>

{elseif $uitype eq 357}
	<td width="20%" class="dvtCellLabel" align=right>To:&nbsp;</td>
	<td width="90%" colspan="3">
		<input name="{$fldname}" type="hidden" value="{$secondvalue}">
		<textarea readonly name="parent_name" cols="70" rows="2">{$fldvalue}</textarea>&nbsp;
		<select name="parent_type" class="small">
			{foreach key=labelval item=selectval from=$fldlabel}
				<option value="{$labelval}" {$selectval}>{$labelval}</option>
			{/foreach}
		</select>
		&nbsp;
		{if $fromlink eq 'qcreate'}
			<img tabindex="{$vt_tab}" src="{'select.gif'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='openPopup("index.php?module="+ document.QcEditView.parent_type.value +"&action=Popup&html=Popup_picker&form=HelpDeskEditView&fromlink={$fromlink}","test","width=640,height=602,resizable=0,scrollbars=0,top=150,left=200");' align="absmiddle" style='cursor:hand;cursor:pointer'>&nbsp;<input type="image" src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE=javascript onClick="this.form.parent_id.value=''; this.form.parent_name.value=''; return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>{* crmv@21048m *}
		{else}
			<img tabindex="{$vt_tab}" src="{'select.gif'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='openPopup("index.php?module="+ document.EditView.parent_type.value +"&action=Popup&html=Popup_picker&form=HelpDeskEditView&fromlink={$fromlink}","test","width=640,height=602,resizable=0,scrollbars=0,top=150,left=200");' align="absmiddle" style='cursor:hand;cursor:pointer'>&nbsp;<input type="image" src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE=javascript onClick="this.form.parent_id.value=''; this.form.parent_name.value=''; return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>{* crmv@21048m *}
		{/if}
	</td>
   <tr style="height:25px">
	<td width="20%" class="dvtCellLabel" align=right>CC:&nbsp;</td>
	<td width="30%" align=left class="dvtCellInfo">
		<input name="ccmail" type="text" class=detailedViewTextBox onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'"  value="">
	</td>
	<td width="20%" class="dvtCellLabel" align=right>BCC:&nbsp;</td>
	<td width="30%" align=left class="dvtCellInfo">
		<input name="bccmail" type="text" class=detailedViewTextBox onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'"  value="">
	</td>
   </tr>

{elseif $uitype eq 59}
	<td width="20%" class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width="30%" align=left class="dvtCellInfo" nowrap> {* crmv@22583 *}
		{* crmv@21048m *}	{* crmv@29190 *}
		<input id="{$fldname}" name="{$fldname}" type="hidden" value="{$secondvalue}">
		{assign var=fld_displayvalue value=$fldvalue}
		{assign var=fld_style value='class="detailedViewTextBox detailedViewReference detailedViewReferenceRO" readonly'}
		{if $fld_displayvalue|trim eq ''}
			{assign var=fld_displayvalue value='LBL_SEARCH_STRING'|getTranslatedString}
			{assign var=fld_style value='class="detailedViewTextBox detailedViewReference"'}
		{/if}
		<input id="{$fldname}_display" name="product_name" type="text" value="{$fld_displayvalue}" {$fld_style}>
		{assign var="popup_params" value="module=Products&action=Popup&html=Popup_picker&form=HelpDeskEditView&popuptype=specific&fromlink=$fromlink"}
		<script type="text/javascript">
		initAutocomplete('{$fldname}','{$fldname}_display',encodeURIComponent('{$popup_params}'));
		</script>
		<img src="{'select.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='openPopup("index.php?{$popup_params}","test","width=640,height=602,resizable=0,scrollbars=0,top=150,left=200");' align="absmiddle" style='cursor:hand;cursor:pointer'>
		{* crmv@37211 *}
		{if $fromlink eq 'qcreate'}
			<img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE="javascript" onClick="document.QcEditView.{$fldname}.value=''; document.QcEditView.product_name.value=''; enableReferenceField(document.QcEditView.product_name); return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
		{else}
			<img src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE="javascript" onClick="document.EditView.{$fldname}.value=''; document.EditView.product_name.value=''; enableReferenceField(document.EditView.product_name); return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
		{/if}
		{* crmv@37211e *}
		{* crmv@21048me *}	{* crmv@29190e *}
	</td>

{elseif $uitype eq 55 || $uitype eq 255}
	{if $uitype eq 55}
		<td width="20%" class="dvtCellLabel" align=right><font color="red">{$mandatory_field}</font>{$usefldlabel}
			 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
		</td>
	{elseif $uitype eq 255}
		<td width="20%" class="dvtCellLabel" align=right><font color="red">{$mandatory_field}</font>{$usefldlabel}
			 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
		</td>
	{/if}

	<td width="30%" align=left class="dvtCellInfo">
	{if $fldvalue neq ''}
	<select name="salutationtype" class="small">
		{foreach item=arr from=$fldvalue}
				<option value="{$arr[1]}" {$arr[2]}>
                                                {$arr[0]}
                                                </option>
		{/foreach}
	</select>
	{/if}
	<input type="text" name="{$fldname}" tabindex="{$vt_tab}" class=detailedViewTextBox onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'" style="width:58%;" value= "{$secondvalue}" >
	</td>

{elseif $uitype eq 22}
	<td width="20%" class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width="30%" align=left class="dvtCellInfo">
		<textarea name="{$fldname}" cols="30" tabindex="{$vt_tab}" rows="2">{$fldvalue}</textarea>
	</td>

{elseif $uitype eq 69}
	<td width="20%" class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td colspan="3" width="30%" align=left class="dvtCellInfo">
		{if $MODULE eq 'Products'}
			<input name="del_file_list" type="hidden" value="">
			<div id="files_list" style="border: 1px solid grey; width: 500px; padding: 5px; background: rgb(255, 255, 255) none repeat scroll 0%; -moz-background-clip: initial; -moz-background-origin: initial; -moz-background-inline-policy: initial; font-size: x-small">{$APP.Files_Maximum_6}
				<input id="my_file_element" type="file" name="file_1" tabindex="{$vt_tab}"  onchange="validateFilename(this)"/>
				<!--input type="hidden" name="file_1_hidden" value=""/-->
				{assign var=image_count value=0}
				{if $maindata[3].0.name neq '' && $DUPLICATE neq 'true'}
				   {foreach name=image_loop key=num item=image_details from=$maindata[3]}
					<div align="center">
						<img src="{$image_details.path}{$image_details.name}" height="50">&nbsp;&nbsp;[{$image_details.orgname}]<input id="file_{$num}" value="Delete" type="button" class="crmbutton small delete" onclick='this.parentNode.parentNode.removeChild(this.parentNode);delRowEmt("{$image_details.orgname}")'>
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
			<input name="{$fldname}"  type="file" value="{$maindata[3].0.name}" tabindex="{$vt_tab}" onchange="validateFilename(this);" />
			<input name="{$fldname}_hidden"  type="hidden" value="{$maindata[3].0.name}" />
			<input type="hidden" name="id" value=""/>
			{ if $maindata[3].0.name != "" && $DUPLICATE neq 'true'}

		<div id="replaceimage">[{$maindata[3].0.orgname}] <a href="javascript:;" onClick="delimage({$ID})">Del</a></div>
			{/if}

		{/if}
	</td>

{elseif $uitype eq 61}
	<td width="20%" class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width="30%" align=left class="dvtCellInfo">
		<input name="{$fldname}"  type="file" value="{$secondvalue}" tabindex="{$vt_tab}" onchange="validateFilename(this)"/>
		<input type="hidden" name="{$fldname}_hidden" value="{$secondvalue}"/>
		<input type="hidden" name="id" value=""/>{$fldvalue}
	</td>
{elseif $uitype eq 156}
	<td width="20%" class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
		{if $fldvalue eq 'on'}
			<td width="30%" align=left class="dvtCellInfo">
				{if ($secondvalue eq 1 && $CURRENT_USERID != $smarty.request.record) || ($MODE == 'create')}
					<input name="{$fldname}" tabindex="{$vt_tab}" type="checkbox" checked>
				{else}
					<input name="{$fldname}" type="hidden" value="on">
					<input name="{$fldname}" disabled tabindex="{$vt_tab}" type="checkbox" checked>
				{/if}
			</td>
		{else}
			<td width="30%" align=left class="dvtCellInfo">
				{if ($secondvalue eq 1 && $CURRENT_USERID != $smarty.request.record) || ($MODE == 'create')}
					<input name="{$fldname}" tabindex="{$vt_tab}" type="checkbox">
				{else}
					<input name="{$fldname}" disabled tabindex="{$vt_tab}" type="checkbox">
				{/if}
			</td>
		{/if}
{elseif $uitype eq 98}<!-- Role Selection Popup -->
	<td width="20%" class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width="30%" align=left class="dvtCellInfo">
	{if $thirdvalue eq 1}
		<input name="role_name" id="role_name" readonly class="txtBox" tabindex="{$vt_tab}" value="{$secondvalue}" type="text">&nbsp;
		<a href="javascript:open_Popup();"><img src="{$IMAGE_PATH}select.gif" align="absmiddle" border="0"></a>{* crmv@21048m *}
	{else}
		<input name="role_name" id="role_name" tabindex="{$vt_tab}" class="txtBox" readonly value="{$secondvalue}" type="text">&nbsp;
	{/if}
	<input name="user_role" id="user_role" value="{$fldvalue}" type="hidden">
	</td>
{elseif $uitype eq 104}<!-- Mandatory Email Fields -->
	 <td width=20% class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
	  {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	 </td>
         <td width=30% align=left class="dvtCellInfo"><input type="text" name="{$fldname}" id ="{$fldname}" value="{$fldvalue}" tabindex="{$vt_tab}" class=detailedViewTextBox onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'"></td>
	{elseif $uitype eq 115}<!-- for Status field Disabled for nonadmin -->
	<td width="20%" class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width="30%" align=left class="dvtCellInfo">
	   {if $secondvalue eq 1 && $CURRENT_USERID != $smarty.request.record}
	   	<select id="user_status" name="{$fldname}" tabindex="{$vt_tab}" class="small">
	   {else}
	   	<select id="user_status" disabled name="{$fldname}" class="small">
	   {/if}
		{foreach item=arr from=$fldvalue}
                                        <option value="{$arr[1]}" {$arr[2]} >
                                                {$arr[0]}
                                        </option>
		{/foreach}
	   </select>
	</td>
	{elseif $uitype eq 105}
	<td width="20%" class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width="30%" align=left class="dvtCellInfo">
		{if $MODE eq 'edit' && $IMAGENAME neq ''}
			<input name="{$fldname}"  type="file" value="{$maindata[3].0.name}" tabindex="{$vt_tab}" onchange="validateFilename(this);" /><div id="replaceimage">[{$IMAGENAME}]&nbsp;<a href="javascript:;" onClick="delUserImage({$ID})">Del</a></div>
			<br>{'LBL_IMG_FORMATS'|@getTranslatedString:$MODULE}
			<input name="{$fldname}_hidden"  type="hidden" value="{$maindata[3].0.name}" />
		{else}
			<input name="{$fldname}"  type="file" value="{$maindata[3].0.name}" tabindex="{$vt_tab}" onchange="validateFilename(this);" /><br>{'LBL_IMG_FORMATS'|@getTranslatedString:$MODULE}
			<input name="{$fldname}_hidden"  type="hidden" value="{$maindata[3].0.name}" />
		{/if}
			<input type="hidden" name="id" value=""/>
			{$maindata[3].0.name}
	</td>
	{elseif $uitype eq 103}
	<td width="20%" class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width="30%" colspan="3" align=left class="dvtCellInfo">
		<input type="text" name="{$fldname}" value="{$fldvalue}" tabindex="{$vt_tab}" class=detailedViewTextBox onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'">
	</td>
	{elseif $uitype eq 101}<!-- for reportsto field USERS POPUP -->
		<td width="20%" class="dvtCellLabel" align=right>
	      <font color="red">{$mandatory_field}</font>{$usefldlabel} {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
            </td>
		<td width="30%" align=left class="dvtCellInfo">
        {* crmv@21048m *}	{* crmv@29190 *}
		<input id="{$fldname}" name="reports_to_id" type="hidden" value="{$secondvalue}">
		{assign var=fld_displayvalue value=$fldvalue}
		{assign var=fld_style value='class="detailedViewTextBox detailedViewReference detailedViewReferenceRO" readonly'}
		{if $fld_displayvalue|trim eq ''}
			{assign var=fld_displayvalue value='LBL_SEARCH_STRING'|getTranslatedString}
			{assign var=fld_style value='class="detailedViewTextBox detailedViewReference"'}
		{/if}
		<input id="{$fldname}_display" name="reports_to_name" type="text" value="{$fld_displayvalue}" {$fld_style}>
		{if $MODULE eq 'Projects'}
			{assign var="popup_params" value="module=Users&action=Popup&form=UsersEditView&form_submit=false&fromlink=$fromlink&recordid=$ID&mode=projectleader"}
		{else}
			{assign var="popup_params" value="module=Users&action=Popup&form=UsersEditView&form_submit=false&fromlink=$fromlink&recordid=$ID"}
		{/if}
		<script type="text/javascript">
		initAutocomplete('{$fldname}','{$fldname}_display',encodeURIComponent('{$popup_params}'));
		</script>
		<img src="{'select.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='openPopup("index.php?{$popup_params}","test","width=640,height=602,resizable=0,scrollbars=0,top=150,left=200");' align="absmiddle" style='cursor:hand;cursor:pointer'>
		<input type="image" src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE=javascript onClick="this.form.{$fldname}.value=''; this.form.reports_to_name.value=''; enableReferenceField(this.form.reports_to_name); return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
		{* crmv@21048me *}	{* crmv@29190e *}
        </td>
	{elseif $uitype eq 116 || $uitype eq 117}<!-- for currency in users details-->
	<td width="20%" class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel} {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width="30%" align=left class="dvtCellInfo">
	   {if $secondvalue eq 1 || $uitype eq 117}
	   	<select name="{$fldname}" tabindex="{$vt_tab}" class="small">
	   {else}
	   	<select disabled name="{$fldname}" tabindex="{$vt_tab}" class="small">
	   {/if}

		{foreach item=arr key=uivalueid from=$fldvalue}
			{foreach key=sel_value item=value from=$arr}
				<option value="{$uivalueid}" {$value}>{$sel_value|@getTranslatedCurrencyString}</option>
				<!-- code added to pass Currency field value, if Disabled for nonadmin -->
				{if $value eq 'selected' && $secondvalue neq 1}
					{assign var="curr_stat" value="$uivalueid"}
				{/if}
				<!--code ends -->
			{/foreach}
		{/foreach}
	   </select>
	<!-- code added to pass Currency field value, if Disabled for nonadmin -->
	{if $curr_stat neq '' && $uitype neq 117}
		<input name="{$fldname}" type="hidden" value="{$curr_stat}">
	{/if}
	<!--code ends -->
	</td>
	{elseif $uitype eq 106}
	<td width=20% class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width=30% align=left class="dvtCellInfo">
		{if $MODE eq 'edit'}
		<input type="text" readonly name="{$fldname}" value="{$fldvalue}" tabindex="{$vt_tab}" class=detailedViewTextBox onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'">
		{else}
		<input type="text" name="{$fldname}" value="{$fldvalue}" tabindex="{$vt_tab}" class=detailedViewTextBox onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'">
		{/if}
	</td>
	{elseif $uitype eq 99}
		{if $MODE eq 'create'}
		<td width=20% class="dvtCellLabel" align=right>
			<font color="red">{$mandatory_field}</font>{$usefldlabel}
			 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
		</td>
		<td width=30% align=left class="dvtCellInfo">
			<input type="password" name="{$fldname}" tabindex="{$vt_tab}" value="{$fldvalue}" class=detailedViewTextBox onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'">
		</td>
		{/if}
{elseif $uitype eq 30}
	<td width="20%" class="dvtCellLabel" align=right>
		<font color="red">{$mandatory_field}</font>{$usefldlabel}
		 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td colspan="3" width="30%" align=left class="dvtCellInfo">
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
	</td>
<!--	//ds@26 -->
{elseif $uitype eq 999}
  <td width=20% class="dvtCellLabel" align=right>
   {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
  </td>
      <td width=30% align=left class="dvtCellInfo"></td>
<!--	//ds@26e -->
<!-- vtc -->
{elseif $uitype eq 26}
<td width="20%" class="dvtCellLabel" align=right>
<font color="red">{$mandatory_field}</font>{$usefldlabel}
 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
</td>
<td width="30%" align=left class="dvtCellInfo">
	<select name="{$fldname}" tabindex="{$vt_tab}" class="small">
		{foreach item=v key=k from=$fldvalue}
		<option value="{$k}">{$v}</option>
		{/foreach}
	</select>
</td>
{elseif $uitype eq 27}
<td width="20%" class="dvtCellLabel" align="right" >
	<font color="red">{$mandatory_field}</font>{$maindata[1][3]}&nbsp;
	 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
</td>
<td width="30%" align=left class="dvtCellInfo">
	<select class="small" name="{$fldname}" onchange="changeDldType((this.value=='I')? 'file': 'text');">
		{section name=combo loop=$fldlabel}
			<option value="{$fldlabel_combo[combo]}" {$fldlabel_sel[combo]} >{$fldlabel[combo]} </option>
		{/section}
	</select>
	<script>
		function vtiger_{$fldname}Init(){ldelim}
			var d = document.getElementsByName('{$fldname}')[0];
			var type = (d.value=='I')? 'file': 'text';

		changeDldType(type, true);
		{rdelim}
		if(typeof window.onload =='function'){ldelim}
			var oldOnLoad = window.onload;
			document.body.onload = function(){ldelim}
				vtiger_{$fldname}Init();
				oldOnLoad();
			{rdelim}
		{rdelim}else{ldelim}
			window.onload = function(){ldelim}
				vtiger_{$fldname}Init();
			{rdelim}
		{rdelim}

	</script>
</td>
{elseif $uitype eq 28}
<td width="20%" class="dvtCellLabel" align=right>
	<font color="red">{$mandatory_field}</font>{$usefldlabel}
	 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
</td>

<td colspan="1" width="30%" align="left" class="dvtCellInfo">
<script type="text/javascript">
	{* crmv@18625 *}
	{* function changeDldType(type, onInit){ldelim} *}
	changeDldType = function(type, onInit){ldelim}
	{* crmv@18625e *}
		var fieldname = '{$fldname}';
		if(!onInit){ldelim}
			var dh = getObj('{$fldname}_hidden');
			if(dh) dh.value = '';
		{rdelim}

		var v1 = document.getElementById(fieldname+'_E__');
		var v2 = document.getElementById(fieldname+'_I__');

		var text = v1.type =="text"? v1: v2;
		var file = v1.type =="file"? v1: v2;
		var filename = document.getElementById(fieldname+'_value');
		{literal}
		if(type == 'file'){
			// Avoid sending two form parameters with same key to server
			file.name = fieldname;
			text.name = '_' + fieldname;

			file.style.display = '';
			text.style.display = 'none';
			text.value = '';
			filename.style.display = '';
		}else{
			// Avoid sending two form parameters with same key to server
			text.name = fieldname;
			file.name = '_' + fieldname;

			file.style.display = 'none';
			text.style.display = '';
			file.value = '';
			filename.style.display = 'none';
			filename.innerHTML="";
		}
		{/literal}
	{rdelim}
</script>
<div>
	<input name="{$fldname}" id="{$fldname}_I__" type="file" value="{$secondvalue}" tabindex="{$vt_tab}" onchange="validateFilename(this)" style="display: none;"/>
	<input type="hidden" name="{$fldname}_hidden" value="{$secondvalue}"/>
	<input type="hidden" name="id" value=""/>
	<input type="text" id="{$fldname}_E__" name="{$fldname}" class="detailedViewTextBox" onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'" value="{$secondvalue}" /><br>
	<span id="{$fldname}_value" style="display:none;">
		{if $secondvalue neq ''}
			[{$secondvalue}]
		{/if}
	</span>
</div>
</td>
<!-- vtc-e -->
{elseif $uitype eq 83} <!-- Handle the Tax in Inventory -->
	{foreach item=tax key=count from=$TAX_DETAILS}
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
			 {if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
		</td>
		<td class="dvtCellInfo" align="left" style="border:0px solid red;">
			<input type="text" class="detailedViewTextBox" name="{$tax.taxname}" id="{$tax.taxname}" value="{$tax.percentage}" style="visibility:{$show_value};" onBlur="fntaxValidation('{$tax.taxname}')">
		</td>
	   </tr>
	{/foreach}

	<td colspan="2" class="dvtCellInfo">&nbsp;
	{if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
<!-- crmv@16265 -->
{elseif $uitype eq 199}
	<td width=20% class="dvtCellLabel" align=right><font color="red">{$mandatory_field}</font>{$fldlabel}
		{if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width=30% align=left class="dvtCellInfo"><input type="password" tabindex="{$vt_tab}" name="{$fldname}" id ="{$fldname}" value="{$fldvalue}" class=detailedViewTextBox onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'"></td>
<!-- crmv@16265e -->
<!-- crmv@18338 end -->
{elseif $uitype eq 1020}
	<td width=20% class="dvtCellLabel" align=right><font color="red">{$mandatory_field}</font>{$fldlabel}
		{if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width=30% align=left class="dvtCellInfo"><input type="input" tabindex="{$vt_tab}" name="{$fldname}" id ="{$fldname}" value="{$fldvalue}" class=detailedViewTextBox onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'"></td>
{elseif $uitype eq 1021}
	<td width=20% class="dvtCellLabel" align=right><font color="red">{$mandatory_field}</font>{$fldlabel}
		{if $MASS_EDIT eq '1'}<input type="checkbox" name="{$fldname}_mass_edit_check" id="{$fldname}_mass_edit_check" class="small" >{/if}
	</td>
	<td width=30% align=left class="dvtCellInfo"><input type="password" tabindex="{$vt_tab}" name="{$fldname}" id ="{$fldname}" value="{$fldvalue}" class=detailedViewTextBox onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'"></td>
<!-- crmv@18338 end -->
{/if}
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