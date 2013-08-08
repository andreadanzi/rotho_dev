<!--
/*********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
*
 ********************************************************************************/
-->

<!-- module header -->
<!-- danzi.tn@13022013 for the new layout -->
<script language="JavaScript" type="text/javascript" src="include/js/ListViewByProduct.js"></script>
<!-- danzi.tn@13022013 e -->
<script language="JavaScript" type="text/javascript" src="include/js/search.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/Merge.js"></script> {* crmv@8719 *}
{* crmv@30967 - moved js functions to ListView.js *}
<script language="JavaScript" type="text/javascript" src="modules/{$MODULE}/{$MODULE}.js"></script>

<input type="hidden" id="user_dateformat" name="user_dateformat" value="{$DATEFORMAT}">
<textarea name="select_ids" id="select_ids" style="display:none;"></textarea>
<!--//crmv@10760 e-->
        {include file='Buttons_List.tpl'}
                                <div id="searchingUI" style="display:none;">
                                        <table border=0 cellspacing=0 cellpadding=0 width=100%>
                                        <tr>
                                                <td align=center>
                                                <img src="{$IMAGE_PATH}searching.gif" alt="{$APP.LBL_SEARCHING}"  title="{$APP.LBL_SEARCHING}">
                                                </td>
                                        </tr>
                                        </table>

                                </div>
                        </td>
                </tr>
                </table>
        </td>
</tr>
</table>

<!-- Contents -->
<table border=0 cellspacing=0 cellpadding=0 width=100% align=center>	{*<!-- crmv@18592 -->*}
     <tr>
        <td valign=top><img src="{$IMAGE_PATH}showPanelTopLeft.gif"></td>

    <td class="showPanelBg" valign="top" width=100% style="padding:0px;">


<!-- ADVANCED SEARCH crmv@31245 -->
<div id="advSearch" style="display:none;padding:10px;" >
<form name="advSearch" method="post" action="index.php" onSubmit="totalnoofrows();return callSearch('Advanced', '{$FOLDERID}');"> {* crmv@30967 *}
        <table  cellspacing=0 cellpadding=5 width=100% class="searchUIAdv1 small" align="center" border=0>
            <tr>
                    <td class="searchUIName small" nowrap align="left"><span class="moduleName">{$APP.LBL_SEARCH}</span></td> {* crmv@31245 *}
                    <td nowrap class="small"><b><input name="matchtype" type="radio" value="all" onclick="updatefOptionsAll(this.value);">&nbsp;{$APP.LBL_ADV_SEARCH_MSG_ALL}</b></td>
                    <td nowrap width=60% class="small" ><b><input name="matchtype" type="radio" value="any" checked onclick="updatefOptionsAll(this.value);">&nbsp;{$APP.LBL_ADV_SEARCH_MSG_ANY}</b></td>
                    <td class="small" valign="top" onMouseOver="this.style.cursor='pointer';" onclick="jQuery('#advSearch').hide()"><img src="{$IMAGE_PATH}close.gif"></td> {* crmv@31245 *}
            </tr>
        </table>

        <table cellpadding="2" cellspacing="0" width="100%" align="center" class="searchUIAdv2 small" border=0>
            <tr>
                <td align="center" class="small" width=90%>
                <div id="fixed" style="position:relative;width:95%;height:80px;padding:0px; overflow:auto;border:1px solid #CCCCCC;background-color:#ffffff" class="small">
                    <table border=0 width=95%>
                    <tr>
                    <td align=left>
                        <table width="100%"  border="0" cellpadding="2" cellspacing="0" id="adSrc" align="left">
                        <tr  >
<!--                        //crmv@10760-->
                            <td width="25%"><select name="Fields0" id="Fields0" class="detailedViewTextBox" onchange="updatefOptions(this, 'Condition0')">{$FIELDNAMES}</select>
                            </td>
                            <td width="25%"><select name="Condition0" id="Condition0" class="detailedViewTextBox">{$CRITERIA}</select>
                            </td>
                            <!-- //crmv@16312 -->
                            <td width="40%"><input type="text" name="Srch_value0" id="Srch_value0"  class="detailedViewTextBox"></td>
                            <!-- //crmv@16312 end -->
                            <td width="10%"><div id="andFields0" name="and0" width="10%"><script>getcondition(false)</script></div></td>
<!--                        //crmv@10760 e-->
                        </tr>
                        </table>
                    </td>
                    </tr>
                </table>
                </div>
                </td>
            </tr>
        </table>

        <table border=0 cellspacing=0 cellpadding=5 width=100% class="searchUIAdv3 small" align="center">
        <tr>
            <td align=left width=40%>
            			<!-- //crmv@16312 -->
                        <input type="button" name="more" value=" {$APP.LBL_MORE} " onClick="fnAddSrch()" class="crmbuttom small edit" >
                        <!-- //crmv@16312 end -->
                        <input name="button" type="button" value=" {$APP.LBL_FEWER_BUTTON} " onclick="delRow()" class="crmbuttom small edit" >
            </td>
            <td align=left class="small"><input type="button" class="crmbutton small create" value=" {$APP.LBL_SEARCH_NOW_BUTTON} " onClick="totalnoofrows();callSearch('Advanced', '{$FOLDERID}');"> {* crmv@30967 *}
            </td>
        </tr>
    </table>
</form>
</div>
<!-- Searching UI -->
 <!-- crmv@8719 -->
 <div id="mergeDup" style="z-index:1;display:none;position:relative;">
	{include file="MergeColumns.tpl"}
</div>
 <!-- crmv@8719e -->
	<!-- PUBLIC CONTENTS STARTS-->

    <div id="ListViewContents" class="small" style="width:100%;position:relative;">
    	{include file="ListViewEntriesByProduct.tpl" MOD=$MOD} {* danzi.tn@20130207 *}
    </div>

     </td>
	<td valign=top><img src="{$IMAGE_PATH}showPanelTopRight.gif"></td>
   </tr>
</table>

<form name="SendMail"><div id="sendmail_cont" style="z-index:100001;position:absolute;"></div></form>
<form name="SendFax"><div id="sendfax_cont" style="z-index:100001;position:absolute;"></div></form>
<!-- crmv@16703 -->
<form name="SendSms" id="SendSms" method="POST" action="index.php"><div id="sendsms_cont" style="z-index:100001;position:absolute;"></div></form>
<!-- crmv@16703e -->
{if $MODULE eq 'Contacts'}
{literal}
<script>
function modifyimage(imagename)
{
    imgArea = getObj('dynloadarea');
        if(!imgArea)
        {
                imgArea = document.createElement("div");
                imgArea.id = 'dynloadarea';
                imgArea.setAttribute("style","z-index:100000001;");
                imgArea.style.position = 'absolute';
                imgArea.innerHTML = '<img width="260" height="200" src="'+imagename+'" class="thumbnail">';
        document.body.appendChild(imgArea);
        }
    PositionDialogToCenter(imgArea.id);
}

function PositionDialogToCenter(ID)
{
       var vpx,vpy;
       if (self.innerHeight) // Mozilla, FF, Safari and Opera
       {
               vpx = self.innerWidth;
               vpy = self.innerHeight;
       }
       else if (document.documentElement && document.documentElement.clientHeight) //IE

       {
               vpx = document.documentElement.clientWidth;
               vpy = document.documentElement.clientHeight;
       }
       else if (document.body) // IE
       {
               vpx = document.body.clientWidth;
               vpy = document.body.clientHeight;
       }

       //Calculate the length from top, left
       dialogTop = (vpy/2 - 280/2) + document.documentElement.scrollTop;
       dialogLeft = (vpx/2 - 280/2);

       //Position the Dialog to center
       $(ID).style.top = dialogTop+"px";
       $(ID).style.left = dialogLeft+"px";
       $(ID).style.display="block";
}

function removeDiv(ID){
        var node2Rmv = getObj(ID);
        if(node2Rmv){node2Rmv.parentNode.removeChild(node2Rmv);}
}

</script>
{/literal}
{/if}
