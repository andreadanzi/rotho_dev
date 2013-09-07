<?php
/***************************************************************************************
 * The contents of this file are subject to the CRMVILLAGE.BIZ VTECRM License Agreement
 * ("licenza.txt"); You may not use this file except in compliance with the License
 * The Original Code is:  CRMVILLAGE.BIZ VTECRM
 * The Initial Developer of the Original Code is CRMVILLAGE.BIZ.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
 ***************************************************************************************/
/* crmv@2328m */

global $default_charset, $adb, $table_prefix, $autocomplete_return_function, $current_user;
$forfield = htmlspecialchars($_REQUEST['forfield'], ENT_QUOTES, $default_charset);
$list_result_count = $i-1;
$value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"search",$focus->popup_type);
if(isset($forfield) && $forfield != '' && $focus->popup_type != 'detailview') {
	$value1 = strip_tags($value);
	$value = htmlspecialchars(addslashes(html_entity_decode(strip_tags($value), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset); // Remove any previous html conversion
	
	$smowner1 = '';
	$smowner=getUserId($entity_id);
	if (!empty($_REQUEST['agente_riferimento']) && $_REQUEST['agente_riferimento'] != 'undefined' && $_REQUEST['agente_riferimento'] == $current_user->id) {
		$smowner1 = $smowner;
	}
	$capoarea_test = '';
	if (!empty($_REQUEST['capoarea']) && $_REQUEST['capoarea'] != 'undefined' && $_REQUEST['capoarea'] == $current_user->id) {
		$capoarea_test=crmv_capoarea2(0,fetchUserRole($smowner));
		if ($capoarea_test == '' && $capoarea_test == null){
			$capoarea_test=1;
		}
	}
	
	$autocomplete_return_function[$entity_id] = "set_extra_info(\"$entity_id\", \"$value\", \"$forfield\", \"$smowner1\", \"$capoarea_test\");";
	$value = "<a href='javascript:void(0);' onclick='{$autocomplete_return_function[$entity_id]}closePopup();'>$value1</a>";
}
?>