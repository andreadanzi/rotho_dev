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

<!-- module header -->

<link rel="stylesheet" type="text/css" media="all" href="jscalendar/calendar-win2k-cold-1.css">
<script type="text/javascript" src="jscalendar/calendar.js"></script>
<script type="text/javascript" src="jscalendar/lang/calendar-{$CALENDAR_LANG}.js"></script>
<script type="text/javascript" src="jscalendar/calendar-setup.js"></script>


<script type="text/javascript">
var gVTModule = '{$smarty.request.module}';
function sensex_info()
{ldelim}
        var Ticker = jQuery('tickersymbol').value;
        if(Ticker!='')
        {ldelim}
                jQuery("vtbusy_info").style.display="inline";
                new Ajax.Request(
                      'index.php',
                      {ldelim}queue: {ldelim}position: 'end', scope: 'command'{rdelim},
                                method: 'post',
                                postBody: 'module={$MODULE}&action=Tickerdetail&tickersymbol='+Ticker,
                                onComplete: function(response) {ldelim}
                                        jQuery('autocom').innerHTML = response.responseText;
                                        jQuery('autocom').style.display="block";
                                        jQuery("vtbusy_info").style.display="none";
                                {rdelim}
                        {rdelim}
                );
        {rdelim}
{rdelim}
</script>
{include file='Buttons_List1.tpl'}
<!-- Contents -->
{*<!-- crmv@18592 -->*}
<table border=0 cellspacing=0 cellpadding=0 width=100% align=center>
   <tr>
	<td valign=top><img src="{$IMAGE_PATH}showPanelTopLeft.gif"></td>

	<td class="showPanelBg" valign="top" width=100%>
		<!-- PUBLIC CONTENTS STARTS-->
		{include file='EditViewHidden.tpl'}
	    {include file='Buttons_List_Edit.tpl'}
		<div class="small">
		<!-- Account details tabs -->
		<table class="margintop" border=0 cellspacing=0 cellpadding=0 width=100% align=center> {* crmv@25128 *}
		   <tr>
			<td>
				<table border=0 cellspacing=0 cellpadding=3 width=100% class="small">
				   <tr>
					<td class="dvtTabCache" style="width:10px" nowrap>&nbsp;</td>

					{if $ADVBLOCKS neq ''}	
						<td width=75 style="width:15%" align="center" nowrap class="dvtSelectedCell" id="bi" onclick="fnLoadValues('bi','mi','basicTab','moreTab','normal','{$MODULE}')"><b>{$APP.LBL_BASIC} {$APP.LBL_INFORMATION}</b></td>
                    				<td class="dvtUnSelectedCell" style="width: 100px;" align="center" nowrap id="mi" onclick="fnLoadValues('mi','bi','moreTab','basicTab','normal','{$MODULE}')"><b>{$APP.LBL_MORE} {$APP.LBL_INFORMATION} </b></td>
                   				<td class="dvtTabCache" style="width:65%" nowrap>&nbsp;</td>
					{else}
						<td class="dvtSelectedCell" align=center nowrap>{$APP.LBL_BASIC} {$APP.LBL_INFORMATION}</td>
	                                        <td class="dvtTabCache" style="width:65%">&nbsp;</td>
					{/if}
{*<!-- crmv@18592e -->*}
				   <tr>
				</table>
			</td>
		   </tr>
		   <tr>
			<td valign=top align=left >

			    <!-- Basic Information Tab Opened -->
			    <div id="basicTab">

				<table border=0 cellspacing=0 cellpadding=3 width=100% class="dvtContentSpace">
				   <tr>
					<td align=left>
					<!-- content cache -->
					
						<table border=0 cellspacing=0 cellpadding=0 width=100%>
						   <tr>
							<td id ="autocom"></td>
						   </tr>
						   <tr>
							<td style="padding:5px;padding-top:15px;">
							<!-- General details -->
								<table border=0 cellspacing=0 cellpadding=0 width=100% class="small">

								   {foreach key=header item=data from=$BASBLOCKS}
								   <tr>
									{* crmv@20176 *}
									{if $header== $MOD.LBL_ADDRESS_INFORMATION}
                                    	{include file='AddressCopy.tpl'}
                                    {* crmv@20176e *}
									{else}
						         		<td colspan=4 class="detailedViewHeader">
											<b>{$header}</b>
									{/if}
							 		</td>
									</tr>

								   <!-- Here we should include the uitype handlings-->
								   {include file="DisplayFields.tpl"}							
								   <tr style="height:25px"><td>&nbsp;</td></tr>
								   {/foreach}
                                    <!-- danzi.tn@20141023 gestione custom valutazione -->
                                    <tr>
                                        <td colspan=4 class="detailedViewHeader">
                                        <b>Valutazione</b>
                                        </td>
                                    </tr>
                                    <tr style="height:25px">
                                        <td width="20%" class="dvtCellLabel" align=right>
                                            <font color="red"></font>Rilavorazione: (&euro;) <img style='cursor:pointer' class='help_btn' onclick='vtlib_field_help_show(this, "rilavorazione");' border=0 src='themes/rothosofted/images/help_icon.gif'> 			
                                        </td>
                                        <td width="30%" align=left class="dvtCellInfo">
                                            <input name="rilavorazione" id="rilavorazione" tabindex="" type="text" class="number_text"   value="0">
                                        </td>
                                        <td width="20%" class="dvtCellLabel" align=right>
                                            <font color="red"></font>Logistica: (&euro;) <img style='cursor:pointer'  class='help_btn'  onclick='vtlib_field_help_show(this, "logistica");' border=0 src='themes/rothosofted/images/help_icon.gif'> 			
                                        </td>
                                        <td width="30%" align=left class="dvtCellInfo">
                                            <input name="logistica" id="logistica" tabindex="" type="text" class="number_text"   value="0">
                                        </td>
                                    </tr>
                                    <tr style="height:25px">
                                        <td width="20%" class="dvtCellLabel" align=right>
                                            <font color="red"></font>Magazzino: (&euro;) <img style='cursor:pointer'  class='help_btn'  onclick='vtlib_field_help_show(this, "magazzino");' border=0 src='themes/rothosofted/images/help_icon.gif'> 			
                                        </td>
                                        <td width="30%" align=left class="dvtCellInfo">
                                            <input name="magazzino" id="magazzino" tabindex="" type="text" class="number_text"   value="0">
                                        </td>
                                        <td width="20%" class="dvtCellLabel" align=right>
                                            <font color="red"></font>Acquisto: (&euro;) <img style='cursor:pointer' class='help_btn'  onclick='vtlib_field_help_show(this, "acquisto");' border=0 src='themes/rothosofted/images/help_icon.gif'> 			
                                        </td>
                                        <td width="30%" align=left class="dvtCellInfo">
                                            <input name="acquisto" id="acquisto" tabindex="" type="text" class="number_text"   value="0">
                                        </td>
                                    </tr>
                                    <tr style="height:25px">
                                        <td width="20%" class="dvtCellLabel" align=right>
                                            <font color="red"></font>Danno Commerciale: (&euro;) <img style='cursor:pointer' id="calc-danno-commerciale" border=0 src='themes/rothosofted/images/help_icon.gif'> 	
                                            <!--  onclick='vtlib_field_help_show(this, "danno_comm");'-->
                                        </td>
                                        <td width="30%" align=left class="dvtCellInfo">
                                            <input id="danno_comm"  name="danno_comm" tabindex="" type="text" class="number_text"   value="0">
                                        </td>
                                        <td width="20%" class="dvtCellLabel" align=right>
                                            <font color="red"></font>Dati commerciali: (&euro;) <img id="calc-dati-commerciali" style='cursor:pointer'  border=0 src='themes/rothosofted/images/help_icon.gif'> 			
                                            <!-- onclick='vtlib_field_help_show(this, "dati_comm");'-->
                                        </td>
                                        <td width="30%" align=left class="dvtCellInfo">
                                            <input  id="dati_comm"  name="dati_comm" tabindex="" type="text" class="number_text"   value="0">
                                        </td>
                                    </tr>        

                                    <tr style="height:25px">
                                        <td width="20%" class="dvtCellLabel" align=right>
                                            <font color="red"></font>Gestione: (&euro;)                             
                                            <select name="selgest" id="selgest">
                                                <option value="0">---</option>
                                                <option value="25">Basso</option>
                                                <option value="50">Medio</option>
                                                <option value="100">Alto</option>
                                                <option value="NS">Non Standard</option>
                                            </select>
                                            <img style='cursor:pointer' class='help_btn'  onclick='vtlib_field_help_show(this, "acquisto");' border=0 src='themes/rothosofted/images/help_icon.gif'> 			
                                        </td>
                                        <td width="30%" align=left class="dvtCellInfo">

                                            <input name="gestione" id="gestione"  tabindex="" type="text" class="number_text"   value="0">
                                        </td>
                                        <td width="20%" class="dvtCellLabel" align=right>
                                            <font color="red"></font>Totale: (&euro;) 
                                        </td>
                                        <td width="30%" align=left class="dvtCellInfo">
                                            <input name="totale_valutazione" id="totale_valutazione"  tabindex="" type="text" class="number_text"   value="0">
                                        </td>
                                    </tr>                    
                                    <tr style="height:25px" >
                                        <td colspan=4>
                                            <input type="hidden" name="danno_comm_perd_ord" id="danno_comm_perd_ord" value="0">
                                            <input type="hidden" name="danno_comm_entr_conc" id="danno_comm_entr_conc" value="0">
                                            <input type="hidden" name="danno_comm_perd_mar" id="danno_comm_perd_mar" value="0">
                                            <input type="hidden" name="danno_comm_perd_cli" id="danno_comm_perd_cli" value="0">
                                            <input type="hidden" name="danno_comm_perd_fatt" id="danno_comm_perd_fatt" value="0">
                                            <input type="hidden" name="danno_comm_dann_imm" id="danno_comm_dann_imm" value="0">
                                            <input type="hidden" name="danno_comm_varie" id="danno_comm_varie" value="0">
                                            <input type="hidden" name="dati_comm_fatt_dann" id="dati_comm_fatt_dann" value="0">
                                            <input type="hidden" name="dati_comm_note_acc" id="dati_comm_note_acc" value="0">
                                            <input type="hidden" name="dati_comm_fermo_can" id="dati_comm_fermo_can" value="0">
                                            <input type="hidden" name="dati_comm_omaggio" id="dati_comm_omaggio" value="0">
                                        </td>
                                    </tr>
                                    <tr style="height:25px" >
                                        <td colspan=4>
                                        
                                            <div id="help-val" title="Help">
                                                <p>
                                                Help Here
                                                </p>
                                            </div>
                                            
                                            <div id="danno-comm-form" title="Danno Commerciale: (&euro;) ">
                                                <p class="validateTips">Valorizzare tutti i campi, anche solo con 0.</p>
                                                <div id="danno">
                                                    <table>
                                                        <tr>
                                                            <td>
                                                            <label for="name">Perdita ordine: (&euro;) </label>
                                                            </td>
                                                            <td>
                                                            <input name="tmp_danno_comm_perd_ord" id="tmp_danno_comm_perd_ord" value="0" class="text ui-widget-content ui-corner-all number_text" style="width:60px;">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>  
                                                            <label for="name">Entrata concorrenza: (&euro;) </label>
                                                            </td>
                                                            <td>
                                                            <input name="tmp_danno_comm_entr_conc" id="tmp_danno_comm_entr_conc" value="0" class="text ui-widget-content ui-corner-all number_text" style="width:60px;">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>  
                                                            <label for="name">Perdita margine: (&euro;) </label>
                                                            </td>
                                                            <td>
                                                            <input name="tmp_danno_comm_perd_mar" id="tmp_danno_comm_perd_mar" value="0" class="text ui-widget-content ui-corner-all number_text" style="width:60px;">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>  
                                                            <label for="name">Perdita cliente: (&euro;) </label>
                                                            </td>
                                                            <td>
                                                            <input name="tmp_danno_comm_perd_cli" id="tmp_danno_comm_perd_cli" value="0" class="text ui-widget-content ui-corner-all number_text" style="width:60px;">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>  
                                                            <label for="name">Perdita fatturato prodotto: (&euro;) </label>
                                                            </td>
                                                            <td>
                                                            <input name="tmp_danno_comm_perd_fatt" id="tmp_danno_comm_perd_fatt" value="0" class="text ui-widget-content ui-corner-all number_text" style="width:60px;">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>  
                                                            <label for="name">Danni immagine x probl grave: (&euro;) </label>
                                                            </td>
                                                            <td>
                                                            <input name="tmp_danno_comm_dann_imm" id="tmp_danno_comm_dann_imm" value="0" class="text ui-widget-content ui-corner-all number_text" style="width:60px;">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>  
                                                            <label for="name">Varie: (&euro;) </label>
                                                            </td>
                                                            <td>
                                                            <input name="tmp_danno_comm_varie" id="tmp_danno_comm_varie" value="0" class="text ui-widget-content ui-corner-all number_text" style="width:60px;">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>  
                                                            <label for="name">Totale: (&euro;) </label>
                                                            </td>
                                                            <td>
                                                            <input name="tmp_danno_comm_totale" id="tmp_danno_comm_totale" value="0" class="text ui-widget-content ui-corner-all number_text" style="width:60px;">
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>


                                            <div id="dati-comm-form" title="Dati commerciali: (&euro;) ">
                                                <p class="validateTips">Valorizzare tutti i campi, anche solo con 0.</p>
                                                <div id="dati"><table>
                                                        <tr>
                                                            <td>
                                                            <label for="name">Fatture danni: (&euro;) </label>
                                                            </td>
                                                            <td>
                                                            <input name="tmp_dati_comm_fatt_dann" id="tmp_dati_comm_fatt_dann" value="0" class="text ui-widget-content ui-corner-all number_text" style="width:60px;">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>  
                                                                   
                                                            <label for="name">Note di accredito: (&euro;) </label>
                                                            </td>
                                                            <td>
                                                            <input name="tmp_dati_comm_note_acc" id="tmp_dati_comm_note_acc" value="0" class="text ui-widget-content ui-corner-all number_text" style="width:60px;">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>  
                                                            
                                                            <label for="name">Fatture fermo cantiere: (&euro;) </label>
                                                            </td>
                                                            <td>
                                                            <input name="tmp_dati_comm_fermo_can" id="tmp_dati_comm_fermo_can" value="0" class="text ui-widget-content ui-corner-all number_text" style="width:60px;">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>  
                                                            
                                                            <label for="name">Omaggio: (&euro;) </label>
                                                            </td>
                                                            <td>
                                                            <input name="tmp_dati_comm_omaggio" id="tmp_dati_comm_omaggio" value="0" class="text ui-widget-content ui-corner-all number_text" style="width:60px;">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>  
                                                            
                                                             <label for="name">Totale: (&euro;) </label>
                                                            </td>
                                                            <td>
                                                            <input name="tmp_dati_comm_totale" id="tmp_dati_comm_totale" value="0" class="text ui-widget-content ui-corner-all number_text" style="width:60px;">
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- danzi.tn@20141023e gestione custom valutazione -->
								</table>
							</td>
						   </tr>
						</table>
					</td>
				   </tr>
				</table>
					
			    </div>
			    <!-- Basic Information Tab Closed -->

			    <!-- More Information Tab Opened -->
			    <div id="moreTab">
				<table border=0 cellspacing=0 cellpadding=3 width=100% class="dvtContentSpace">
				   <tr>
					<td align=left>
					<!-- content cache -->
					
						<table border=0 cellspacing=0 cellpadding=0 width=100%>
						   <tr>
							<td id ="autocom"></td>
						   </tr>
						   <tr>
							<td style="padding:5px;padding-top:15px;">
							<!-- General details -->
								<table border=0 cellspacing=0 cellpadding=0 width=100% class="small">

								   {foreach key=header item=data from=$ADVBLOCKS}
								   <tr>
						         		<td colspan=4 class="detailedViewHeader">
                                                	        		<b>{$header}</b>
                                                         		</td>
                                                         	   </tr>

								   <!-- Here we should include the uitype handlings-->
                                                        	   {include file="DisplayFields.tpl"}

							 	   <tr style="height:25px"><td>&nbsp;</td></tr>
								   {/foreach}
{*<!-- crmv@18592e -->*}
								   </tr>
								</table>
							</td>
						   </tr>
						</table>
					</td>
				   </tr>
				</table>
			    </div>

			</td>
		   </tr>
		</table>
	     </div>
	</td>
	<td align=right valign=top><img src="{$IMAGE_PATH}showPanelTopRight.gif"></td>
   </tr>
</table>
</form>
{if $MODULE eq 'Nonconformities'}
<!-- danzi.tn@20141023 gestione custom valutazione -->
{literal}
<style>
    fieldset { padding:0; border:0; margin-top:25px; }
    .ui-dialog .ui-state-error { padding: .3em; }
    .validateTips { border: 1px solid transparent; padding: 0.3em; }
    .number_text {text-align:right; }
</style>
<script type="text/javascript">


    var dialog_danno_comm, dialog_dati_comm, form_danno, help_val, form_dati,selgest,
    // From http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#e-mail-state-%28type=email%29
    emailRegex = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/,
    danno_comm_perd_ord = jQuery( "#tmp_danno_comm_perd_ord" ),
    danno_comm_entr_conc = jQuery( "#tmp_danno_comm_entr_conc" ),
    danno_comm_perd_mar = jQuery( "#tmp_danno_comm_perd_mar" ),
    danno_comm_perd_cli = jQuery( "#tmp_danno_comm_perd_cli" ),
    danno_comm_perd_fatt = jQuery( "#tmp_danno_comm_perd_fatt" ),
    danno_comm_dann_imm = jQuery( "#tmp_danno_comm_dann_imm" ),
    danno_comm_varie = jQuery( "#tmp_danno_comm_varie" ),
    danno_comm_totale =  jQuery( "#tmp_danno_comm_totale"),
    dati_comm_fatt_dann = jQuery( "#tmp_dati_comm_fatt_dann" ),
    dati_comm_note_acc = jQuery( "#tmp_dati_comm_note_acc" ),
    dati_comm_fermo_can = jQuery( "#tmp_dati_comm_fermo_can" ),
    dati_comm_omaggio = jQuery( "#tmp_dati_comm_omaggio" ),
    dati_comm_totale = jQuery( "#tmp_dati_comm_totale" ),
    hidden_danno_comm_perd_ord = jQuery( "#danno_comm_perd_ord" ),
    hidden_danno_comm_entr_conc = jQuery( "#danno_comm_entr_conc" ),
    hidden_danno_comm_perd_mar = jQuery( "#danno_comm_perd_mar" ),
    hidden_danno_comm_perd_cli = jQuery( "#danno_comm_perd_cli" ),
    hidden_danno_comm_perd_fatt = jQuery( "#danno_comm_perd_fatt" ),
    hidden_danno_comm_dann_imm = jQuery( "#danno_comm_dann_imm" ),
    hidden_danno_comm_varie = jQuery( "#danno_comm_varie" ),
    hidden_dati_comm_fatt_dann = jQuery( "#dati_comm_fatt_dann" ),
    hidden_dati_comm_note_acc = jQuery( "#dati_comm_note_acc" ),
    hidden_dati_comm_fermo_can = jQuery( "#dati_comm_fermo_can" ),
    hidden_dati_comm_omaggio = jQuery( "#dati_comm_omaggio" ),
    dati_comm = jQuery( "#dati_comm" ),
    danno_comm = jQuery( "#danno_comm" ),
    rilavorazione = jQuery( "#rilavorazione" ),
    logistica = jQuery( "#logistica" ),
    magazzino = jQuery( "#magazzino" ),
    acquisto = jQuery( "#acquisto" ),
    gestione = jQuery( "#gestione" ),
    selgest = jQuery( "#selgest" ),
    totale_valutazione = jQuery( "#totale_valutazione" ),
    allFieldsDannoComm = jQuery( [] ).add( danno_comm_perd_ord ).add( danno_comm_entr_conc ).add( danno_comm_perd_mar ).add( danno_comm_perd_fatt ).add( danno_comm_perd_cli ).add( danno_comm_dann_imm ).add( danno_comm_varie ).add( danno_comm ),
    allFieldsDatiComm = jQuery( [] ).add( dati_comm_fatt_dann ).add( dati_comm_note_acc ).add( dati_comm_fermo_can ).add( dati_comm_omaggio ).add( dati_comm ),
    allOtherFields = jQuery( [] ).add( rilavorazione ).add( logistica ).add( magazzino ).add( acquisto ).add( gestione ),
    tips = jQuery( ".validateTips" );
    allFieldsDannoComm.val(0);
    allFieldsDatiComm.val(0);
    allOtherFields.val(0);
    totale_valutazione.val(0);
    danno_comm_totale.val(0);
    dati_comm_totale.val(0);
    danno_comm_totale.css("background", "#FF3300");
    dati_comm_totale.css("background", "#FF3300");
    totale_valutazione.css("background", "#FF3300");
    
    
    gestione.attr('readonly', true);
    dati_comm_totale.attr('readonly', true);
    danno_comm_totale.attr('readonly', true);
    dati_comm.attr('readonly', true);
    danno_comm.attr('readonly', true);
    totale_valutazione.attr('readonly', true);
    
    allFieldsDannoComm.change(function() {
        tot_amount = parseFloat(danno_comm_perd_ord.val()) + parseFloat(danno_comm_entr_conc.val()) + parseFloat(danno_comm_perd_mar.val()) + parseFloat(danno_comm_perd_cli.val()) + parseFloat(danno_comm_perd_fatt.val()) + parseFloat(danno_comm_dann_imm.val()) + parseFloat(danno_comm_varie.val());
        if(tot_amount>0) danno_comm_totale.css("background", "#CCFF00");
        else danno_comm_totale.css("background", "#FF3300");
        danno_comm_totale.val(tot_amount);
    });
    
    allFieldsDatiComm.change(function() {
        tot_amount = parseFloat(dati_comm_fatt_dann.val()) + parseFloat(dati_comm_note_acc.val()) + parseFloat(dati_comm_fermo_can.val()) + parseFloat(dati_comm_omaggio.val());
        if(tot_amount>0) dati_comm_totale.css("background", "#CCFF00");
        else dati_comm_totale.css("background", "#FF3300");
        dati_comm_totale.val(tot_amount);
    });
    
    allOtherFields.change(function() {
        tot_amount = parseFloat(rilavorazione.val()) + parseFloat(logistica.val()) + parseFloat(magazzino.val()) + parseFloat(acquisto.val())+ parseFloat(gestione.val())+ parseFloat(danno_comm.val())+ parseFloat(dati_comm.val());
        if(tot_amount>0) totale_valutazione.css("background", "#CCFF00");
        else totale_valutazione.css("background", "#FF3300");
        totale_valutazione.val(tot_amount);
        mapValues();
    });
    
    
    function mapValues() {
        hidden_danno_comm_perd_ord.val(danno_comm_perd_ord.val());
        hidden_danno_comm_entr_conc.val(danno_comm_entr_conc.val());
        hidden_danno_comm_perd_mar.val(danno_comm_perd_mar.val());
        hidden_danno_comm_perd_cli.val(danno_comm_perd_cli.val());
        hidden_danno_comm_perd_fatt.val(danno_comm_perd_fatt.val());
        hidden_danno_comm_dann_imm.val(danno_comm_dann_imm.val());
        hidden_danno_comm_varie.val(danno_comm_varie.val());
        hidden_dati_comm_fatt_dann.val(dati_comm_fatt_dann.val());
        hidden_dati_comm_note_acc.val(dati_comm_note_acc.val());
        hidden_dati_comm_fermo_can.val(dati_comm_fermo_can.val());
        hidden_dati_comm_omaggio.val(dati_comm_omaggio.val());
    }
    
    function updateTips( t ) {
        tips
        .text( t )
        .addClass( "ui-state-highlight" );
        setTimeout(function() {
        tips.removeClass( "ui-state-highlight", 1500 );
        }, 500 );
    }
    
    function checkLength( o, n, min, max ) {
        if ( o.val().length > max || o.val().length < min ) {
        o.addClass( "ui-state-error" );
        updateTips( "Length of " + n + " must be between " +
        min + " and " + max + "." );
        return false;
        } else {
        return true;
        }
    }
    
    function checkRegexp( o, regexp, n ) {
        if ( !( regexp.test( o.val() ) ) ) {
        o.addClass( "ui-state-error" );
        updateTips( n );
        return false;
        } else {
        return true;
        }
    }
    
    function calcDannoComm() {
        var valid = true;
        var tot_amount = 0.0;
        allFieldsDannoComm.removeClass( "ui-state-error" );
        valid = valid && checkLength( danno_comm_perd_ord, "danno_comm_perd_ord", 1, 8 );
        valid = valid && checkLength( danno_comm_entr_conc, "danno_comm_entr_conc", 1, 8  );
        valid = valid && checkLength( danno_comm_perd_mar, "danno_comm_perd_mar", 1, 8  );
        valid = valid && checkLength( danno_comm_perd_cli, "danno_comm_perd_cli", 1, 8 );
        valid = valid && checkLength( danno_comm_perd_fatt, "danno_comm_perd_fatt", 1, 8  );
        valid = valid && checkLength( danno_comm_dann_imm, "danno_comm_dann_imm", 1, 8  );
        valid = valid && checkLength( danno_comm_varie, "danno_comm_varie", 1, 8  );
        //valid = valid && checkRegexp( name, /^[a-z]([0-9a-z_\s])+$/i, "Username may consist of a-z, 0-9, underscores, spaces and must begin with a letter." );
        //valid = valid && checkRegexp( email, emailRegex, "eg. ui@jquery.com" );
        //valid = valid && checkRegexp( password, /^([0-9a-zA-Z])+$/, "Password field only allow : a-z 0-9" );
        if ( valid ) {
            tot_amount = parseFloat(danno_comm_perd_ord.val()) + parseFloat(danno_comm_entr_conc.val()) + parseFloat(danno_comm_perd_mar.val()) + parseFloat(danno_comm_perd_cli.val()) + parseFloat(danno_comm_perd_fatt.val()) + parseFloat(danno_comm_dann_imm.val()) + parseFloat(danno_comm_varie.val());
            danno_comm.val(tot_amount);
            other_amount = parseFloat(rilavorazione.val()) + parseFloat(logistica.val()) + parseFloat(magazzino.val()) + parseFloat(acquisto.val()) + parseFloat(gestione.val());
            if(tot_amount>0) danno_comm.css("background", "#CCFF00");
            else danno_comm.css("background", "#FF3300");
            tot_amount = tot_amount + other_amount + parseFloat(dati_comm.val());
            if(tot_amount>0) totale_valutazione.css("background", "#CCFF00");
            else totale_valutazione.css("background", "#FF3300");
            totale_valutazione.val(tot_amount);
            mapValues();
            dialog_danno_comm.dialog( "close" );
        }
        return valid;
    }
    
    
    function calcDatiComm() {
        var valid = true;
        var tot_amount = 0.0;
        allFieldsDatiComm.removeClass( "ui-state-error" );
        valid = valid && checkLength( dati_comm_fatt_dann, "dati_comm_fatt_dann", 1, 8 );
        valid = valid && checkLength( dati_comm_note_acc, "dati_comm_note_acc", 1, 8  );
        valid = valid && checkLength( dati_comm_fermo_can, "dati_comm_fermo_can", 1, 8  );
        valid = valid && checkLength( dati_comm_omaggio, "dati_comm_omaggio", 1, 8 );
        if ( valid ) {
            tot_amount = parseFloat(dati_comm_fatt_dann.val()) + parseFloat(dati_comm_note_acc.val()) + parseFloat(dati_comm_fermo_can.val()) + parseFloat(dati_comm_omaggio.val());
            dati_comm.val(tot_amount);
            other_amount = parseFloat(rilavorazione.val()) + parseFloat(logistica.val()) + parseFloat(magazzino.val()) + parseFloat(acquisto.val()) + parseFloat(gestione.val());
            if(tot_amount>0) dati_comm.css("background", "#CCFF00");
            else dati_comm.css("background", "#FF3300");
            tot_amount = tot_amount + other_amount + parseFloat(danno_comm.val());
            if(tot_amount>0) totale_valutazione.css("background", "#CCFF00");
            else totale_valutazione.css("background", "#FF3300");
            totale_valutazione.val(tot_amount);
            mapValues();
            dialog_dati_comm.dialog( "close" );
        }
        return valid;
    }
    
    dialog_danno_comm = jQuery( "#danno-comm-form" ).dialog({
        autoOpen: false,
        height: 370,
        width: 340,
        modal: true,
        buttons: {
        "Salva Valutazione Danno": calcDannoComm,
        Cancel: function() {
            allFieldsDannoComm.val(0);
            danno_comm_totale.val(0);
            danno_comm_totale.css("background", "#FF3300");
        }
        },
        close: function() {
        allFieldsDannoComm.removeClass( "ui-state-error" );
        }
    });
    
    
    dialog_dati_comm = jQuery( "#dati-comm-form" ).dialog({
        autoOpen: false,
        height: 370,
        width: 340,
        modal: true,
        buttons: {
        "Salva Dati": calcDatiComm,
        Cancel: function() {
            allFieldsDatiComm.val(0);
            dati_comm_totale.val(0);
            dati_comm_totale.css("background", "#FF3300");
        }
        },
        close: function() {       
        allFieldsDatiComm.removeClass( "ui-state-error" );
        }
    });
    
    help_val = jQuery( "#help-val" ).dialog({
        autoOpen: false,
        height: 400,
        width: 300,
        modal: true,
        buttons: {
            Ok: function() {
                jQuery( this ).dialog( "close" );
            }
        }
    });
    
    selgest.change(function() {
            gestione.attr('readonly', true);
            selectdValue = selgest.val();
            switch(selectdValue) {
                case '25':
                    gestione.val(25*10);
                    break;
                case '50':
                    gestione.val(50*10);
                    break;
                case '100':
                    gestione.val(100*10);
                    break;
                case 'NS':
                    gestione.attr('readonly', false);
                    gestione.focus();
                    gestione.val('');
                    break;
                default:
                    gestione.val(0);
            } 
            tot_amount = parseFloat(rilavorazione.val()) + parseFloat(logistica.val()) + parseFloat(magazzino.val()) + parseFloat(acquisto.val())+ parseFloat(gestione.val())+ parseFloat(danno_comm.val())+ parseFloat(dati_comm.val());
            if(tot_amount>0) totale_valutazione.css("background", "#CCFF00");
            else totale_valutazione.css("background", "#FF3300");
            totale_valutazione.val(tot_amount);
            mapValues();
        }
    );
        
    jQuery( "#calc-danno-commerciale" ).button().click( function() {
        dialog_danno_comm.dialog( "open" );
    });
    
    
    
    jQuery( "#calc-dati-commerciali" ).button().click(  function() {
        dialog_dati_comm.dialog( "open" );
    });
    
    jQuery( ".help_btn" ).button().click(  function() {
        help_val.dialog( "open" );
    });


</script>
{/literal}
<!-- danzi.tn@20141023e gestione custom valutazione -->
{/if}
<!--crmv@16312-->
{if ($MODULE eq 'Emails' || $MODULE eq 'Documents' || $MODULE eq 'Timecards') and ($FCKEDITOR_DISPLAY eq 'true')}
<!--crmv@16312 end-->
<!--crmv@10621-->
	<script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>
	<script>
		var current_language_arr = "{php} echo $_SESSION['authenticated_user_language']; {/php}".split("_");
		var curr_lang = current_language_arr[0];
        {if $MODULE eq 'Timecards'}
			{literal}
			CKEDITOR.replace('description', {
				filebrowserBrowseUrl: 'include/ckeditor/filemanager/index.html',
				toolbar : 'Basic',	//crmv@31210
				language : curr_lang
			});	
			{/literal}	
        {else}					
			{literal}
			CKEDITOR.replace('notecontent', {
				filebrowserBrowseUrl: 'include/ckeditor/filemanager/index.html',
				toolbar : 'Basic',	//crmv@31210
				language : curr_lang
			});	
			{/literal}
		{/if}	
	</script>
<!--crmv@10621 e-->		
{/if}
{if $MODULE eq 'Accounts'}
<script>
	ScrollEffect.limit = 201;
	ScrollEffect.closelimit= 200;
</script>
{/if}
<script>



        var fieldname = new Array({$VALIDATION_DATA_FIELDNAME})

        var fieldlabel = new Array({$VALIDATION_DATA_FIELDLABEL})

        var fielddatatype = new Array({$VALIDATION_DATA_FIELDDATATYPE})


</script>
<!-- vtlib customization: Help information assocaited with the fields -->
{if $FIELDHELPINFO}
<script type='text/javascript'>
{literal}var fieldhelpinfo = {}; {/literal}
{foreach item=FIELDHELPVAL key=FIELDHELPKEY from=$FIELDHELPINFO}
	fieldhelpinfo["{$FIELDHELPKEY}"] = "{$FIELDHELPVAL}";
{/foreach}
</script>
{/if}
<!-- END -->