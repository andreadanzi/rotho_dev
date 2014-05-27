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
<!-- This file is used to display the fields based on the ui type in detailview -->
		{* crmv@sdk-18509 *}
		{if $SDK->isUitype($keyid) eq 'true'}
			{assign var="sdk_mode" value="detail"}
			{assign var="sdk_file" value=$SDK->getUitypeFile('tpl',$sdk_mode,$keyid)}
			{if $sdk_file neq ''}
				{include file=$sdk_file}
			{/if}
		{* crmv@sdk-18509 e *}
<!--   //crmv@7231+7217 --> 
		{elseif $keyid eq '1' || $keyid eq 2 || $keyid eq '7' || $keyid eq '9' || $keyid eq '55' || $keyid eq '71' || $keyid eq '72' || $keyid eq '103' || $keyid eq '255' || $keyid eq 1112} <!--TextBox-->
				{if $keyid eq 1112 && ($keyval neq '' && $keyval neq '--None--') }
					<td width=25% class="dvtCellInfo" align="left" id="mouseArea_{$label}">
				{else}
               <td width=25% class="dvtCellInfo" align="left" id="mouseArea_{$label}" onmouseover="hndMouseOver({$keyid},'{$label}');" onmouseout="fnhide('crmspanid');">
            {/if}                             		
				{if $keyid eq '55' || $keyid eq '255'}<!--SalutationSymbol-->
					{if $keyaccess eq $APP.LBL_NOT_ACCESSIBLE}
						<font color='red'>{$APP.LBL_NOT_ACCESSIBLE}</font>	
					{else}
						{$keysalut}
					{/if}
				{/if}
                                                       &nbsp;&nbsp;<span id="dtlview_{$label}">{$keyval}</span>
                                              		<div id="editarea_{$label}" style="display:none;">
                                              		  <input class="detailedViewTextBox" onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'" type="text" id="txtbox_{$label}" name="{$keyfldname}" maxlength='100' value="{$keyval}"></input>
                                              		  <br><input name="button_{$label}" type="button" class="crmbutton small save" value="{$APP.LBL_SAVE_LABEL}" onclick="dtlViewAjaxSave('{$label}','{$MODULE}',{$keyid},'{$keytblname}','{$keyfldname}','{$ID}');fnhide('crmspanid');"/> {$APP.LBL_OR}
                                              		  <a href="javascript:;" onclick="hndCancel('dtlview_{$label}','editarea_{$label}','{$label}')" class="link">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
                                                       </div>
                {if $keyid eq '71' && $keyfldname eq 'unit_price'}	
                	{if $PRICE_DETAILS|@count > 0}				
						<span id="multiple_currencies" width="38%" style="align:right;">
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" onclick="toggleShowHide('currency_class','multiple_currencies');">{$APP.LBL_MORE_CURRENCIES} &raquo;</a>
						</span>
						
						<div id="currency_class" class="multiCurrencyDetailUI">					
							<table width="100%" height="100%" class="small" cellpadding="5">
							<tr>
								<th colspan="2">
									<b>{$MOD.LBL_PRODUCT_PRICES}</b>
								</th>
								<th align="right">
									<img border="0" style="cursor: pointer;" onclick="toggleShowHide('multiple_currencies','currency_class');" src="{'close.gif'|@vtiger_imageurl:$THEME}"/>
								</th>
							</tr>							
							<tr class="detailedViewHeader">
								<th>{$APP.LBL_CURRENCY}</th>
								<th colspan="2">{$APP.LBL_PRICE}</th>
							</tr>
							{foreach item=price key=count from=$PRICE_DETAILS}
								<tr>
									{*if $price.check_value eq 1*}
									<td class="dvtCellLabel" width="40%">
										{$price.currencylabel|@getTranslatedCurrencyString} ({$price.currencysymbol})
									</td>
									<td class="dvtCellInfo" width="60%" colspan="2">
										{$price.curvalue}
									</td>
								</tr>
							{/foreach}
							</table>
						</div>
					{/if}
                {/if}                                                       
                                                  </td>
                                             {elseif $keyid eq '13' || $keyid eq '104'} <!--Email-->
                                                  <td width=25% class="dvtCellInfo" align="left" id="mouseArea_{$label}" onmouseover="hndMouseOver({$keyid},'{$label}');" onmouseout="fnhide('crmspanid');"><span id="dtlview_{$label}">
							{if $smarty.session.internal_mailer eq 1}
								<a href="javascript:InternalMailer({$ID},{$keyfldid},'{$keyfldname}','{$MODULE}','record_id');">{$keyval}</a>
							{else}
								<a href="mailto:{$keyval}" target="_blank" >{$keyval}</a>
							{/if}
							</span>
                                              		<div id="editarea_{$label}" style="display:none;">
                                              		  <input class="detailedViewTextBox" onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'" type="text" id="txtbox_{$label}" name="{$keyfldname}" maxlength='100' value="{$keyval}"></input>
                                              		  <br><input name="button_{$label}" type="button" class="crmbutton small save" value="{$APP.LBL_SAVE_LABEL}" onclick="dtlViewAjaxSave('{$label}','{$MODULE}',{$keyid},'{$keytblname}','{$keyfldname}','{$ID}');fnhide('crmspanid');"/> {$APP.LBL_OR}
                                              		  <a href="javascript:;" onclick="hndCancel('dtlview_{$label}','editarea_{$label}','{$label}')" class="link">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
                                                       </div>
							<div id="internal_mailer_{$keyfldname}" style="display: none;">{$keyfldid}####{$smarty.session.internal_mailer}</div>
                                                  </td>
							<!--crmv@7216-->
                                             {elseif $keyid eq '1013'} <!--Fax-->
                                                  <td width=25% class="dvtCellInfo" align="left" id="mouseArea_{$label}" onmouseover="hndMouseOver({$keyid},'{$label}');" onmouseout="fnhide('crmspanid');"><span id="dtlview_{$label}">
								<a href="javascript:InternalFax({$ID},{$keyfldid},'{$keyfldname}','{$MODULE}','record_id');">{$keyval}</a>
							</span>
                                              		<div id="editarea_{$label}" style="display:none;">
                                              		  <input class="detailedViewTextBox" onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'" type="text" id="txtbox_{$label}" name="{$keyfldname}" maxlength='100' value="{$keyval}"></input>
                                              		  <br><input name="button_{$label}" type="button" class="crmbutton small save" value="{$APP.LBL_SAVE_LABEL}" onclick="dtlViewAjaxSave('{$label}','{$MODULE}',{$keyid},'{$keytblname}','{$keyfldname}','{$ID}');fnhide('crmspanid');"/> {$APP.LBL_OR}
                                              		  <a href="javascript:;" onclick="hndCancel('dtlview_{$label}','editarea_{$label}','{$label}')" class="link">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
                                                       </div>
							<div id="internal_mailer_{$keyfldname}" style="display: none;">{$keyfldid}####{$smarty.session.internal_mailer}</div>
                                                  </td>
							<!--crmv@7216e-->
							<!--crmv@7220-->
                                             {elseif $keyid eq '1014' or $keyid eq '11'} <!--telephone numbers-->
                                                  <td width=25% class="dvtCellInfo" align="left" id="mouseArea_{$label}" onmouseover="hndMouseOver({$keyid},'{$label}');" onmouseout="fnhide('crmspanid');"><span id="dtlview_{$label}">
								{if $USE_ASTERISK eq 'true'}
									<a href='javascript:;' onclick='startCall("{$keyval}", "{$ID}")'>{$keyval}</a>
								{else}
									{$keyval}								
								{/if}	
							</span>
                                              		<div id="editarea_{$label}" style="display:none;">
                                              		  <input class="detailedViewTextBox" onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'" type="text" id="txtbox_{$label}" name="{$keyfldname}" maxlength='100' value="{$keyval}"></input>
                                              		  <br><input name="button_{$label}" type="button" class="crmbutton small save" value="{$APP.LBL_SAVE_LABEL}" onclick="dtlViewAjaxSave('{$label}','{$MODULE}',{$keyid},'{$keytblname}','{$keyfldname}','{$ID}');fnhide('crmspanid');"/> {$APP.LBL_OR}
                                              		  <a href="javascript:;" onclick="hndCancel('dtlview_{$label}','editarea_{$label}','{$label}')" class="link">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
                                                       </div>
							<div id="internal_mailer_{$keyfldname}" style="display: none;">{$keyfldid}####{$smarty.session.internal_mailer}</div>
                                                  </td>
							<!--crmv@7220-->
                                                  
                                                  
						<!-- uitype 111 added for noneditable existing picklist values - ahmed -->
						<!-- //crmv@8982 --> 
                                             {elseif $keyid eq '15' || $keyid eq '1015' || $keyid eq '16' || $keyid eq '111'} <!--ComboBox-->
                        <!-- //crmv@8982e -->                      
						{foreach item=arr from=$keyoptions}
							{if $arr[0] eq $APP.LBL_NOT_ACCESSIBLE && $arr[2] eq 'selected'}
								{assign var=keyval value=$APP.LBL_NOT_ACCESSIBLE}
								{assign var=fontval value='red'}
							{else}
                                                                {assign var=fontval value=''}
							{/if}
						{/foreach}               
                        <!-- //ds@26 -->
                        
               				{if $keyfldname eq "memory_center"}
                               <td width=25% colspan="3" class="dvtCellInfo" align="left" id="mouseArea_{$label}" onmouseover="hndMouseOver({$keyid},'{$label|escape:'quotes'}');" onmouseout="fnhide('crmspanid');">&nbsp;<span id="dtlview_{$label}">{$keyval|@getTranslatedString:$MODULE}</span>
                            {else}
							 <td width=25% class="dvtCellInfo" align="left" id="mouseArea_{$label}" onmouseover="hndMouseOver({$keyid},'{$label}');" onmouseout="fnhide('crmspanid');"><span id="dtlview_{$label}"><font color="{$fontval}">{$keyval|@getTranslatedString:$MODULE}</font></span>
                            {/if}

                        <!-- //ds@26e -->
                                              		<div id="editarea_{$label}" style="display:none;">
                    							   <select id="txtbox_{$label}" name="{$keyfldname}">
                    								{foreach item=arr from=$keyoptions}
											{if $arr[0] eq $APP.LBL_NOT_ACCESSIBLE}
                    									<option value="{$arr[0]}" {$arr[2]}>{$arr[0]}</option>
											{else}
        							                                <option value="{$arr[1]}" {$arr[2]}>
							                                                {$arr[0]}
								                                </option>
							                                {/if}

										{/foreach}
                    							   </select>
                    							   <br><input name="button_{$label}" type="button" class="crmbutton small save" value="{$APP.LBL_SAVE_LABEL}" onclick="dtlViewAjaxSave('{$label}','{$MODULE}',{$keyid},'{$keytblname}','{$keyfldname}','{$ID}');fnhide('crmspanid');"/> {$APP.LBL_OR}
                                              		   <a href="javascript:;" onclick="hndCancel('dtlview_{$label}','editarea_{$label}','{$label}')" class="link">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
                    							</div>
               							</td>
                    <!-- //ds@26 -->
               						{if $keyfldname eq "memory_center"}
            							  </tr>
                          {/if}
                    <!-- //ds@26e -->          
                                          {elseif $keyid eq '33'}<!--Multi Select Combo box-->
						<!--code given by Neil start Ref:http://forums.vtiger.com/viewtopic.php?p=31096#31096-->
						<!--{assign var="MULTISELECT_COMBO_BOX_ITEM_SEPARATOR_STRING" value=", "}  {* Separates Multi-Select Combo Box items *}
						{assign var="DETAILVIEW_WORDWRAP_WIDTH" value="70"} {* No. of chars for word wrapping long lines of Multi-Select Combo Box items *}-->
                                          <td width=25% class="dvtCellInfo" align="left" id="mouseArea_{$label}" onmouseover="hndMouseOver({$keyid},'{$label}');" onmouseout="fnhide('crmspanid');">&nbsp;<span id="dtlview_{$label}">
					{foreach item=sel_val from=$keyoptions }
						{if $sel_val[2] eq 'selected'}
							{if $selected_val neq ''}
							{assign var=selected_val value=$selected_val|cat:', '}
							{/if}
							{assign var=selected_val value=$selected_val|cat:$sel_val[0]}
						{/if}
					{/foreach}
						{$selected_val|replace:"\n":"<br>&nbsp;&nbsp;"}
						<!-- commented to fix ticket4631 -using wordwrap will affect Not Accessible font color -->
						<!--{$selected_val|replace:$MULTISELECT_COMBO_BOX_ITEM_SEPARATOR_STRING:"\x1"|replace:" ":"\x0"|replace:"\x1":$MULTISELECT_COMBO_BOX_ITEM_SEPARATOR_STRING|wordwrap:$DETAILVIEW_WORDWRAP_WIDTH:"<br>&nbsp;"|replace:"\x0":"&nbsp;"}-->
						</span>
						<!--code given by Neil End-->
                                          <div id="editarea_{$label}" style="display:none;">
                                          <select MULTIPLE id="txtbox_{$label}" name="{$keyfldname}" size="4" style="width:160px;">
				                                    {foreach item=arr from=$keyoptions}
										<option value="{$arr[1]}" {$arr[2]}>{$arr[0]}</option>
				                                    {/foreach}
			                                   </select>
			                                   <br><input name="button_{$label}" type="button" class="crmbutton small save" value="{$APP.LBL_SAVE_LABEL}" onclick="dtlViewAjaxSave('{$label}','{$MODULE}',{$keyid},'{$keytblname}','{$keyfldname}','{$ID}');fnhide('crmspanid');"/> {$APP.LBL_OR}
                                              		   <a href="javascript:;" onclick="hndCancel('dtlview_{$label}','editarea_{$label}','{$label}')" class="link">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
                    							</div>
               							</td>
						{elseif $keyid eq '115'} <!--ComboBox Status edit only for admin Users-->
               							<td width=25% class="dvtCellInfo" align="left">{$keyval}</td>
						{elseif $keyid eq '116'} <!--ComboBox currency id edit only for admin Users-->
								{if $keyadmin eq 1}
               							<td width=25% class="dvtCellInfo" align="left" id="mouseArea_{$label}" onmouseover="hndMouseOver({$keyid},'{$label}');" onmouseout="fnhide('crmspanid');">&nbsp;<span id="dtlview_{$label}">{$keyval}</span>
								<div id="editarea_{$label}" style="display:none;">
                    							   <select id="txtbox_{$label}" name="{$keyfldname}">
									{foreach item=arr key=uivalueid from=$keyoptions}
									{foreach key=sel_value item=value from=$arr}
										<option value="{$uivalueid}" {$value}>{$sel_value}</option>	
									{/foreach}
									{/foreach}
                    							   </select>
                    							   <br><input name="button_{$label}" type="button" class="crmbutton small save" value="{$APP.LBL_SAVE_LABEL}" onclick="dtlViewAjaxSave('{$label}','{$MODULE}',{$keyid},'{$keytblname}','{$keyfldname}','{$ID}');fnhide('crmspanid');"/> {$APP.LBL_OR}
                                              		   <a href="javascript:;" onclick="hndCancel('dtlview_{$label}','editarea_{$label}','{$label}')" class="link">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
                    							</div>
								{else}
               							<td width=25% class="dvtCellInfo" align="left">{$keyval}
								{/if}	

                                        		
               							</td>
                                             {elseif $keyid eq '17'} <!--WebSite-->
                                                  <td width=25% class="dvtCellInfo" align="left" id="mouseArea_{$label}" onmouseover="hndMouseOver({$keyid},'{$label}');" onmouseout="fnhide('crmspanid');">&nbsp;<span id="dtlview_{$label}"><a href="http://{$keyval}" target="_blank">{$keyval}</a></span>
                                              		<div id="editarea_{$label}" style="display:none;">
                                              		  <input class="detailedViewTextBox" onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'" onkeyup="validateUrl('{$keyfldname}');" type="text" id="txtbox_{$label}" name="{$keyfldname}" maxlength='100' value="{$keyval}"></input>
                                              		  <br><input name="button_{$label}" type="button" class="crmbutton small save" value="{$APP.LBL_SAVE_LABEL}" onclick="dtlViewAjaxSave('{$label}','{$MODULE}',{$keyid},'{$keytblname}','{$keyfldname}','{$ID}');fnhide('crmspanid');"/> {$APP.LBL_OR}
                                              		  <a href="javascript:;" onclick="hndCancel('dtlview_{$label}','editarea_{$label}','{$label}')" class="link">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
                                                       </div>
                                                  </td>
					     {elseif $keyid eq '85'}<!--Skype-->
                                                <td width=25% class="dvtCellInfo" align="left" id="mouseArea_{$label}" onmouseover="hndMouseOver({$keyid},'{$label}');" onmouseout="fnhide('crmspanid');">&nbsp;<img src="{$IMAGE_PATH}skype.gif" alt="{$APP.LBL_SKYPE}" title="{$APP.LBL_SKYPE}" LANGUAGE=javascript align="absmiddle"></img><span id="dtlview_{$label}"><a href="skype:{$keyval}?call">{$keyval}</a></span>
                                                        <div id="editarea_{$label}" style="display:none;">
                                                          <input class="detailedViewTextBox" onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'" type="text" id="txtbox_{$label}" name="{$keyfldname}" maxlength='100' value="{$keyval}"></input>
                                                          <br><input name="button_{$label}" type="button" class="crmbutton small save" value="{$APP.LBL_SAVE_LABEL}" onclick="dtlViewAjaxSave('{$label}','{$MODULE}',{$keyid},'{$keytblname}','{$keyfldname}','{$ID}');fnhide('crmspanid');"/> {$APP.LBL_OR}
                                                          <a href="javascript:;" onclick="hndCancel('dtlview_{$label}','editarea_{$label}','{$label}')" class="link">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
                                                       </div>
                                                  </td>	
                                             {elseif $keyid eq '19' || $keyid eq '20'} <!--TextArea/Description-->
						<!-- we will empty the value of ticket and faq comment -->
						{if $label eq $MOD.LBL_ADD_COMMENT}
							{assign var=keyval value=''}
						{/if}
							<!--{assign var="DESCRIPTION_SEPARATOR_STRING" value=" "}  {* Separates Description *}-->
							<!--{assign var="DESCRIPTION_WORDWRAP_WIDTH" value="70"} {* No. of chars for word wrapping long lines of Description *}-->
							{if $MODULE eq 'Documents' or $MODULE eq 'Timecards'}
							<!--To give hyperlink to URL-->
                                                        <td width="100%" colspan="3" class="dvtCellInfo" align="left">{$keyval|regex_replace:"/(^|[\n ])([\w]+?:\/\/.*?[^ \"\n\r\t<]*)/":"\\1<a href=\"\\2\" target=\"_blank\">\\2</a>"|regex_replace:"/(^|[\n ])((www|ftp)\.[\w\-]+\.[\w\-.\~]+(?:\/[^ \"\t\n\r<]*)?)/":"\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>"|regex_replace:"/(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)/i":"\\1<a href=\"javascript:InternalMailer('\\2@\\3','','','','email_addy');\">\\2@\\3</a>"|regex_replace:"/,\"|\.\"|\)\"|\)\.\"|\.\)\"/":"\""|replace:"\n":"<br>&nbsp;"}&nbsp;	{* crmv@27617 *}
                                                        </td>
                                                  	{else}
                                                        <td width="100%" colspan="3" class="dvtCellInfo" align="left" id="mouseArea_{$label}" onmouseover="hndMouseOver({$keyid},'{$label}');" onmouseout="fnhide('crmspanid');">&nbsp;<span id="dtlview_{$label}">
								{$keyval|regex_replace:"/(^|[\n ])([\w]+?:\/\/.*?[^ \"\n\r\t<]*)/":"\\1<a href=\"\\2\" target=\"_blank\">\\2</a>"|regex_replace:"/(^|[\n ])((www|ftp)\.[\w\-]+\.[\w\-.\~]+(?:\/[^ \"\t\n\r<]*)?)/":"\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>"|regex_replace:"/(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)/i":"\\1<a href=\"javascript:InternalMailer('\\2@\\3','','','','email_addy');\">\\2@\\3</a>"|regex_replace:"/,\"|\.\"|\)\"|\)\.\"|\.\)\"/":"\""|replace:"\n":"<br>&nbsp;"}	{* crmv@27617 *}
                                                                </span>
                                                                <div id="editarea_{$label}" style="display:none;">
                                                                <textarea id="txtbox_{$label}" name="{$keyfldname}"  class=detailedViewTextBox onFocus="this.className='detailedViewTextBoxOn'"onBlur="this.className='detailedViewTextBox'" cols="90" rows="8">{$keyval|replace:"<br>":"\n"}</textarea>
                                                                <br><input name="button_{$label}" type="button" class="crmbutton small save" value="{$APP.LBL_SAVE_LABEL}" onclick="dtlViewAjaxSave('{$label}','{$MODULE}',{$keyid},'{$keytblname}','{$keyfldname}','{$ID}');fnhide('crmspanid');"/> {$APP.LBL_OR}
                                                                <a href="javascript:;" onclick="hndCancel('dtlview_{$label}','editarea_{$label}','{$label}')" class="link">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
                                                                </div>
                                                        </td>
                                                   {/if}
                                             {elseif $keyid eq '21' || $keyid eq '24' || $keyid eq '22'} <!--TextArea/Street-->
                                                  <td width=25% class="dvtCellInfo" align="left" id="mouseArea_{$label}" onmouseover="hndMouseOver({$keyid},'{$label}');" onmouseout="fnhide('crmspanid');">&nbsp;<span id="dtlview_{$label}">{$keyval}</span>
                                              		<div id="editarea_{$label}" style="display:none;">
                                              		  <textarea id="txtbox_{$label}" name="{$keyfldname}"  class=detailedViewTextBox onFocus="this.className='detailedViewTextBoxOn'"onBlur="this.className='detailedViewTextBox'" rows=2>{$keyval|regex_replace:"/<br\s*\/>/":""}</textarea>                                            		  
                                              		  <br><input name="button_{$label}" type="button" class="crmbutton small save" value="{$APP.LBL_SAVE_LABEL}" onclick="dtlViewAjaxSave('{$label}','{$MODULE}',{$keyid},'{$keytblname}','{$keyfldname}','{$ID}');fnhide('crmspanid');"/> {$APP.LBL_OR}
                                              		  <a href="javascript:;" onclick="hndCancel('dtlview_{$label}','editarea_{$label}','{$label}')" class="link">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
                                                       </div>
                                                  </td>
                                             {elseif $keyid eq '50' || $keyid eq '73' || $keyid eq '51'} <!--AccountPopup-->
                                                  <td width=25% class="dvtCellInfo" align="left" id="mouseArea_{$label}">&nbsp;<a href="{$keyseclink}">{$keyval}</a>
                                                  </td>
<!-- crmv@8839 -->
<!-- //###---insert_here_uitypeNewPopupFieldIts4YouModule--- -->
<!-- crmv@8839e -->
                                             {elseif $keyid eq '57'} <!--ContactPopup-->
						<!-- Ajax edit link not provided for contact - Reports To -->
                                                  	<td width=25% class="dvtCellInfo" align="left" id="mouseArea_{$label}">&nbsp;<a href="{$keyseclink}">{$keyval}</a></td>
                                             {elseif $keyid eq '59'} <!--ProductPopup-->
                                                  <td width=25% class="dvtCellInfo" align="left" id="mouseArea_{$label}">&nbsp;<a href="{$keyseclink}">{$keyval}</a>
                                                  </td>
                                             {elseif $keyid eq '75' || $keyid eq '81'} <!--VendorPopup-->
                                                  <td width=25% class="dvtCellInfo" align="left" id="mouseArea_{$label}">&nbsp;<a href="{$keyseclink}">{$keyval}</a>
                                                  </td>
                                             {elseif $keyid eq 76} <!--PotentialPopup-->
                                                  <td width=25% class="dvtCellInfo" align="left" id="mouseArea_{$label}">&nbsp;<a href="{$keyseclink}">{$keyval}</a>
                                                  </td>
                                             {elseif $keyid eq 78} <!--QuotePopup-->
                                                  <td width=25% class="dvtCellInfo" align="left" id="mouseArea_{$label}">&nbsp;<a href="{$keyseclink}">{$keyval}</a>
                                                  </td>
                                             {elseif $keyid eq 82} <!--Email Body-->
                                                  <td colspan="3" width=100% class="dvtCellInfo" align="left"><div id="dtlview_{$label}" style="width:100%;height:200px;overflow:hidden;border:1px solid gray" class="detailedViewTextBox" onmouseover="this.className='detailedViewTextBoxOn'" onmouseout="this.className='detailedViewTextBox'">{$keyval}</div>
                                                  </td>
                                             {elseif $keyid eq 80} <!--SalesOrderPopup-->
                                                  <td width=25% class="dvtCellInfo" align="left" id="mouseArea_{$label}">&nbsp;<a href="{$keyseclink}">{$keyval}</a>
                                                  </td>
												  {*danzi.tn@20140220*}
					     {elseif $keyid eq '52' || $keyid eq '77' || $keyid eq '1077'} 
													<!-- danzi.tn@20140220 -->
                                                                <td width=25% class="dvtCellInfo" align="left" id="mouseArea_{$label}" onmouseover="hndMouseOver({$keyid},'{$label}');" onmouseout="fnhide('crmspanid');">&nbsp;<span id="dtlview_{$label}">{$keyval}</span>
                                                        <div id="editarea_{$label}" style="display:none;">
                                                                           <select id="txtbox_{$label}" name="{$keyfldname}">
                                                                                {foreach item=arr key=uid from=$keyoptions}
                                                                                        {foreach key=sel_value item=value from=$arr}
                                                                                                <option value="{$uid}" {$value}>{if $APP.$sel_value}{$APP.$sel_value}{else}{$sel_value}{/if}</option>

                                                                                        {/foreach}
                                                                                {/foreach}
                                                                           </select>
                                                                           <br><input name="button_{$label}" type="button" class="crmbutton small save" value="{$APP.LBL_SAVE_LABEL}" onclick="dtlViewAjaxSave('{$label}','{$MODULE}',{$keyid},'{$keytblname}','{$keyfldname}','{$ID}');fnhide('crmspanid');"/> {$APP.LBL_OR}
                                                           <a href="javascript:;" onclick="hndCancel('dtlview_{$label}','editarea_{$label}','{$label}')" class="link">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
                                                                        </div>
                                                                </td>	
													<!-- danzi.tn@20140220end -->
						{* crmv@31171 *}
						{elseif $keyid eq '53'} <!--Assigned To-->
							{php}$this->assign('JSON',new Zend_Json());{/php}
							<td width=25% class="dvtCellInfo" align="left" id="mouseArea_{$label}" onmouseover="hndMouseOver({$keyid},'{$label}');" onmouseout="fnhide('crmspanid');">&nbsp;<span id="dtlview_{$label}">
							{if $keyadmin eq 1}
								<a href="{$keyseclink.0}">{$keyval}</a>         
							{else}	
								{$keyval}
							{/if}
							</span>
							<div id="editarea_{$label}" style="display:none;">
							<input type="hidden" id="hdtxt_{$label}" value="{$keyval}"></input>
							{if $keyoptions.2 neq ''}
								<select id="assigntype" name="assigntype" class="small" onChange='toggleAssignType(this.value); document.DetailView.{$keyfldname}_display.value=""; document.DetailView.{$keyfldname}.value=""; enableReferenceField(document.DetailView.{$keyfldname}_display); document.DetailView.assigned_group_id_display.value=""; document.DetailView.assigned_group_id.value=""; enableReferenceField(document.DetailView.assigned_group_id_display); closeAutocompleteList("{$keyfldname}_display"); closeAutocompleteList("assigned_group_id_display");'>	{* crmv@29190 *}
									<option value="U" {if $keyoptions.0 eq 'User'}selected{/if}>{$APP.LBL_USER}</option>
									<option value="T" {if $keyoptions.0 eq 'Group'}selected{/if}>{$APP.LBL_GROUP}</option>
								</select>
							{else}
								<input type="hidden" id="assigntype" name="assigntype" value="U">
							{/if}
							{if $keyoptions.0 eq 'User'}
								<span id="assign_user" style="display: block;">
							{else}
								<span id="assign_user" style="display: none;">
							{/if}
								{assign var=fld_value value='0'}
			                    {foreach item=arr key=id from=$keyoptions.1}
			                    	{foreach key=sel_value item=value from=$arr}
                       					{if $value eq 'selected'}
											{assign var=fld_value value=$id}
											{assign var=fld_displayvalue value=$sel_value}
										{/if}
			                        {/foreach}
			                    {/foreach}
		                    	<input id="txtbox_U{$label}" name="{$keyfldname}" type="hidden" value="{$fld_value}">
								{assign var=fld_style value='class="detailedViewTextBox detailedViewReference detailedViewReferenceRO" readonly'}
								{if $fld_displayvalue|trim eq ''}
									{assign var=fld_displayvalue value='LBL_SEARCH_STRING'|getTranslatedString}
									{assign var=fld_style value='class="detailedViewTextBox detailedViewReference"'}
								{/if}
								<input id="{$keyfldname}_display" name="{$keyfldname}_display" type="text" value="{$fld_displayvalue}" {$fld_style}>	
								<script type="text/javascript">
								initAutocompleteUG('Users','{$keyfldname}','{$keyfldname}_display','{$JSON->encode($keyoptions.1)}','{$label}',document.DetailView);
								</script>
								&nbsp;<img src="{'select.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='toggleAutocompleteList("{$keyfldname}_display");' align="absmiddle" style='cursor:hand;cursor:pointer'>
								<input type="image" src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE=javascript onClick="this.form.{$keyfldname}.value=''; this.form.{$keyfldname}_display.value=''; enableReferenceField(this.form.{$keyfldname}_display); return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
							</span>
							{if $keyoptions.0 eq 'Group'}
								<span id="assign_team" style="display: block;">
							{else}
								<span id="assign_team" style="display: none;">
							{/if}
								{assign var=fld_secondvalue value='0'}
			                    {foreach item=arr key=id from=$keyoptions.2}
			                    	{foreach key=sel_value item=value from=$arr}
			                       		 {if $value eq 'selected'}
											{assign var=fld_secondvalue value=$id}
											{assign var=fld_displaysecondvalue value=$sel_value}
										{/if}
			                        {/foreach}
			                    {/foreach}
			                    <input id="txtbox_G{$label}" name="assigned_group_id" type="hidden" value="{$fld_secondvalue}">
								{assign var=fld_style value='class="detailedViewTextBox detailedViewReference detailedViewReferenceRO" readonly'}
								{if $fld_displaysecondvalue|trim eq ''}
									{assign var=fld_displaysecondvalue value='LBL_SEARCH_STRING'|getTranslatedString}
									{assign var=fld_style value='class="detailedViewTextBox detailedViewReference"'}
								{/if}
								<input id="assigned_group_id_display" name="assigned_group_id_display" type="text" value="{$fld_displaysecondvalue}" {$fld_style}>	
								<script type="text/javascript">
								initAutocompleteUG('Groups','assigned_group_id','assigned_group_id_display','{$JSON->encode($keyoptions.2)}','{$label}',document.DetailView);
								</script>
								&nbsp;<img src="{'select.gif'|@vtiger_imageurl:$THEME}" tabindex="{$vt_tab}" alt="{$APP.LBL_SELECT}" title="{$APP.LBL_SELECT}" LANGUAGE=javascript onclick='toggleAutocompleteList("assigned_group_id_display");' align="absmiddle" style='cursor:hand;cursor:pointer'>
								<input type="image" src="{'clear_field.gif'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_CLEAR}" title="{$APP.LBL_CLEAR}" LANGUAGE=javascript onClick="this.form.assigned_group_id.value=''; this.form.assigned_group_id_display.value=''; enableReferenceField(this.form.assigned_group_id_display); return false;" align="absmiddle" style='cursor:hand;cursor:pointer'>
							</span>
		                    <br>
		                    <input name="button_{$label}" type="button" class="crmbutton small save" value="{$APP.LBL_SAVE_LABEL}" onclick="dtlViewAjaxSave('{$label}','{$MODULE}',{$keyid},'{$keytblname}','{$keyfldname}','{$ID}');"/> {$APP.LBL_OR}
		                    <a href="javascript:;" onclick="hndCancel('dtlview_{$label}','editarea_{$label}','{$label}')" class="link">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
                    		</div>
                    	</td>
                    	{* crmv@31171e *}
						{elseif $keyid eq '99'}<!-- Password Field-->
						<td width=25% class="dvtCellInfo" align="left">{$CHANGE_PW_BUTTON}</td>	
					    {elseif $keyid eq '56'} <!--CheckBox--> 
                      <td width=25% class="dvtCellInfo" align="left" id="mouseArea_{$label}" onMouseOver="hndMouseOver({$keyid},'{$label}');" onmouseout="fnhide('crmspanid');">&nbsp;<span id="dtlview_{$label}">{$keyval}&nbsp;</span>
                    	<div id="editarea_{$label}" style="display:none;">
                        {if $keyval eq 'yes'}                                              		  
                            <input id="txtbox_{$label}" name="{$keyfldname}" type="checkbox" style="border:1px solid #bababa;" checked value="1">
                        {else}
                          <input id="txtbox_{$label}" type="checkbox" name="{$keyfldname}" style="border:1px solid #bababa;" value="0">
                       	{/if}
                         <br><input name="button_{$label}" type="button" class="crmbutton small save" value="{$APP.LBL_SAVE_LABEL}" onclick="dtlViewAjaxSave('{$label}','{$MODULE}',{$keyid},'{$keytblname}','{$keyfldname}','{$ID}');"/> {$APP.LBL_OR}
                          <a href="javascript:;" onclick="hndCancel('dtlview_{$label}','editarea_{$label}','{$label}')" class="link">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
                        </div>
                        </td>    
			{elseif $keyid eq '156'} <!--CheckBox for is admin-->
			{if $smarty.request.record neq $CURRENT_USERID && $keyadmin eq 1} 
                      <td width=25% class="dvtCellInfo" align="left" id="mouseArea_{$label}" onMouseOver="hndMouseOver({$keyid},'{$label}');" onmouseout="fnhide('crmspanid');">&nbsp;<span id="dtlview_{$label}">{if $APP.$keyval!=''}{$APP.$keyval}{elseif $MOD.$keyval!=''}{$MOD.$keyval}{else}{$keyval}{/if}&nbsp;</span>
                    	<div id="editarea_{$label}" style="display:none;">
                        {if $keyval eq 'on'}                                              		  
                            <input id="txtbox_{$label}" name="{$keyfldname}" type="checkbox" style="border:1px solid #bababa;" checked value="1">
                        {else}
                          <input id="txtbox_{$label}" type="checkbox" name="{$keyfldname}" style="border:1px solid #bababa;" value="0">
                       	{/if}
                         <br><input name="button_{$label}" type="button" class="crmbutton small save" value="{$APP.LBL_SAVE_LABEL}" onclick="dtlViewAjaxSave('{$label}','{$MODULE}',{$keyid},'{$keytblname}','{$keyfldname}','{$ID}');"/> {$APP.LBL_OR}
                          <a href="javascript:;" onclick="hndCancel('dtlview_{$label}','editarea_{$label}','{$label}')" class="link">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
                        </div>
			{else}
				 <td width=25% class="dvtCellInfo" align="left">{$keyval}
			{/if}
                        </td>    
			 
						{elseif $keyid eq 83}<!-- Handle the Tax in Inventory -->
						</tr>
							{foreach item=tax key=count from=$TAX_DETAILS}
								<td align="right" class="dvtCellLabel">
									{$tax.taxlabel} {$APP.COVERED_PERCENTAGE}
							
								</td>
								<td class="dvtCellInfo" align="left">
									{$tax.percentage}
								</td>
								<td colspan="2" class="dvtCellInfo">&nbsp;</td>
							   </tr>
							{/foreach}
                <!-- //ds@26 -->
				{elseif $keyid eq 330}
              <td align="left" width=75%" colspan=3 class="dvtCellInfo" height="25px">&nbsp;{$keyval}</td></tr>
                <!-- //ds@26e -->
				{elseif $keyid eq 5}
					{* Initialize the date format if not present *}
					{if empty($dateFormat)}
						{assign var="dateFormat" value=$APP.NTC_DATE_FORMAT|@parse_calendardate}
					{/if}
					<td width=25% class="dvtCellInfo" align="left" id="mouseArea_{$label}" onmouseover="hndMouseOver({$keyid},'{$label}');" onmouseout="fnhide('crmspanid');">
						&nbsp;&nbsp;<span id="dtlview_{$label}" >
							{if $keysecid eq 'ok'}<font color='green'>{elseif $keysecid eq 'ko'}<font color='red'>{/if}{$keyval}{if $keysecid eq 'ok'}</font><img src=themes/images/ok.gif>{elseif $keysecid eq 'ko'}</font><img src=themes/images/no.gif>{/if}
						</span>
						<div id="editarea_{$label}" style="display:none;">
								<table border="0" cellpadding="0" cellspacing="0">
									<tr>
										<td>
											<input style="border:1px solid #bababa;" size="11" maxlength="10" type="text" id="txtbox_{$label}" name="{$keyfldname}" maxlength='100' value="{$keyval|regex_replace:'/[^-]*(--)[^-]*$/':''}"></input>
										</td>
										<td>	
											<img src="{'btnL3Calendar.gif'|@vtiger_imageurl:$THEME}" id="jscal_trigger_{$keyfldname}">
										</td>
									</tr>
									<tr>
										<td colspan=2>
											<font size=1><em old="(yyyy-mm-dd)">({php}global $current_user;echo $current_user->date_format;{/php})</em></font>
										</td>	
									</tr>										
									<tr>
										<td colspan=2>
											<input name="button_{$label}" type="button" class="crmbutton small save" value="{$APP.LBL_SAVE_LABEL}" onclick="dtlViewAjaxSave('{$label}','{$MODULE}',{$keyid},'{$keytblname}','{$keyfldname}','{$ID}');fnhide('crmspanid');"/> {$APP.LBL_OR}
											<a href="javascript:;" onclick="hndCancel('dtlview_{$label}','editarea_{$label}','{$label}')" class="link">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
										</td>	
									</tr>
								</table>		
							<script type="text/javascript">
								Calendar.setup ({ldelim}
									inputField : "txtbox_{$label}", ifFormat : '{$dateFormat}', showsTime : false, button : "jscal_trigger_{$keyfldname}", singleClick : true, step : 1
								{rdelim})
							</script>
						</div>
					</td>

				{elseif $keyid eq 69}<!-- for Image Reflection -->
					<td align="left" width=25%">&nbsp;{$keyval}</td>
                <!-- crmv@16265 -->
                {elseif $keyid eq '199'}
					<td width=25% class="dvtCellInfo" align="left" id="mouseArea_{$label}" onmouseover="hndMouseOver({$keyid},'{$label}');" onmouseout="fnhide('crmspanid');">
						&nbsp;&nbsp;<span id="dtlview_{$label}">{$keyoptions}</span>
						<div id="editarea_{$label}" style="display:none;">
						<input class="detailedViewTextBox" onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'" type="password" id="txtbox_{$label}" name="{$keyfldname}" maxlength='100' value="{$keyval}"></input>
						<br><input name="button_{$label}" type="button" class="crmbutton small save" value="{$APP.LBL_SAVE_LABEL}" onclick="dtlViewAjaxSave('{$label}','{$MODULE}',{$keyid},'{$keytblname}','{$keyfldname}','{$ID}');fnhide('crmspanid');"/> {$APP.LBL_OR}
						<a href="javascript:;" onclick="hndCancel('dtlview_{$label}','editarea_{$label}','{$label}')" class="link">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
						</div>
					</td>
				<!-- crmv@16265e -->
				<!-- crmv@18338 -->
				{elseif $keyid eq 1020}
					<td width=25% class="dvtCellInfo" align="left" id="mouseArea_{$label}" onmouseover="hndMouseOver({$keyid},'{$label}');" onmouseout="fnhide('crmspanid');">
						&nbsp;&nbsp;<span id="dtlview_{$label}">{$keyoptions}</span>
						<div id="editarea_{$label}" style="display:none;">
						<input class="detailedViewTextBox" onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'" type="input" id="txtbox_{$label}" name="{$keyfldname}" maxlength='100' value="{$keyval}"></input>
						<br><input name="button_{$label}" type="button" class="crmbutton small save" value="{$APP.LBL_SAVE_LABEL}" onclick="dtlViewAjaxSave('{$label}','{$MODULE}',{$keyid},'{$keytblname}','{$keyfldname}','{$ID}');fnhide('crmspanid');"/> {$APP.LBL_OR}
						<a href="javascript:;" onclick="hndCancel('dtlview_{$label}','editarea_{$label}','{$label}')" class="link">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
						</div>
					</td>
				<!-- crmv@18338 end -->					
				{else}
					<td class="dvtCellInfo" align="left" width=25%">&nbsp;{$keyval}</td>
				{/if}