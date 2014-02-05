<?
global $default_charset,$adb,$table_prefix,$autocomplete_return_function;
$forfield = htmlspecialchars($_REQUEST['forfield'], ENT_QUOTES, $default_charset);
$list_result_count = $i-1;
$value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"search",$focus->popup_type);
if(isset($forfield) && $forfield != '' && $focus->popup_type != 'detailview') {
	//crmv@16312
	$value1 = strip_tags($value);
	$value = htmlspecialchars(addslashes(html_entity_decode(strip_tags($value), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset); // Remove any previous html conversion
	//crmv@29190
	global $autocomplete_return_function;
	$query_pdesc = "SELECT description, case when cf_803 IS NULL then '' else cf_803 end as productcat FROM [vte40_387].[dbo].vtiger_crmentity 
					inner join [vte40_387].[dbo].vtiger_productcf
					on vtiger_crmentity.crmid = vtiger_productcf.productid WHERE crmid = ".$entity_id;	
	$res_pdesc = $adb->query($query_pdesc);
	$p_desc = $adb->query_result($res_pdesc,0,'description');
	$p_categ = $adb->query_result($res_pdesc,0,'productcat');
	$autocomplete_return_function[$entity_id] = "set_product_to_helpdesk($entity_id, \"$p_desc\", \"$value\", \"$p_categ\", \"$forfield\");";
	$value = "<a href='javascript:void(0);' onclick='{$autocomplete_return_function[$entity_id]}closePopup();'>$value1</a>"; //crmv@21048m
	//crmv@16312 end
	//crmv@29190e
	}
?>