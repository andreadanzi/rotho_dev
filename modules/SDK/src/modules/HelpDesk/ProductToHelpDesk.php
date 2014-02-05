<?php
global $default_charset,$adb,$table_prefix,$autocomplete_return_function,$log;
$log->debug("Entering ProductToHelpDesk.php ...");
$forfield = htmlspecialchars($_REQUEST['forfield'], ENT_QUOTES, $default_charset);
$list_result_count = $i-1;
$value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"search",$focus->popup_type);
if(isset($forfield) && $forfield != '' && $focus->popup_type != 'detailview') {
	$value1 = strip_tags($value);
	$value = htmlspecialchars(addslashes(html_entity_decode(strip_tags($value), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset); // Remove any previous html conversion
	$link_to_category = '';
	$link_to_description = '';
	// cf_796 local, ROTHO --> cf_803 in test + LEFT
	$query = "SELECT LEFT(".$table_prefix."_productcf.cf_803,8) AS category, ".$table_prefix."_crmentity.description , erp_temp_crm_classificazioni.class_desc3 as category_descr
			FROM ".$table_prefix."_products
			LEFT JOIN ".$table_prefix."_productcf ON ".$table_prefix."_products.productid = ".$table_prefix."_productcf.productid
			LEFT JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_products.productid = ".$table_prefix."_crmentity.crmid
			LEFT JOIN erp_temp_crm_classificazioni ON erp_temp_crm_classificazioni.class3 = LEFT(".$table_prefix."_productcf.cf_803,8)
			WHERE ".$table_prefix."_crmentity.deleted = 0 AND ".$table_prefix."_productcf.productid = ".$entity_id;
	$log->debug("ProductToHelpDesk.php customquery ".$query);
	$result = $adb->query($query);
	if ($result && $adb->num_rows($result)>0) {
		$link_to_category = $adb->query_result($result,0,'category');
		$link_to_category_descr = $adb->query_result($result,0,'category_descr');
		$link_to_description = $adb->query_result($result,0,'description');
	}
	$autocomplete_return_function[$entity_id] = "return_product_to_helpdesk($entity_id, \"$value\", \"$forfield\", \"$link_to_category\", \"$link_to_description\", \"$link_to_category_descr\");";
	$value = "<a href='javascript:void(0);' onclick='{$autocomplete_return_function[$entity_id]}closePopup();'>$value1</a>"; //crmv@21048m
}
$log->debug("Exiting ProductToHelpDesk.php ...");
/*ELECT DISTINCT class3 as categorycode, class1 as parentlevel1, class2 as parentlevel2, class_desc3 as categorydescr, class_desc1, class_desc2 
	FROM erp_temp_crm_classificazioni , vtiger_productcf
	WHERE erp_temp_crm_classificazioni.class3 = LEFT(vtiger_productcf.cf_803,8)
	ORDER BY parentlevel1 ASC, parentlevel2 ASC, categorycode ASC
	*/
?>


