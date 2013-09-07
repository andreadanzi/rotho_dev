<?php
global $default_charset,$adb,$table_prefix,$autocomplete_return_function,$log;
$log->debug("Entering ProductToRumors.php ...");
$forfield = htmlspecialchars($_REQUEST['forfield'], ENT_QUOTES, $default_charset);
$list_result_count = $i-1;
$value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"search",$focus->popup_type);
if(isset($forfield) && $forfield != '' && $focus->popup_type != 'detailview') {
	$value1 = strip_tags($value);
	$value = htmlspecialchars(addslashes(html_entity_decode(strip_tags($value), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset); // Remove any previous html conversion
	$link_to_category = '';
	$link_to_description = '';
	// cf_796 local, ROTHO --> cf_803 in test
	$query = "SELECT ".$table_prefix."_productcf.cf_803 AS category, ".$table_prefix."_crmentity.description 
			FROM ".$table_prefix."_products
			LEFT JOIN ".$table_prefix."_productcf ON ".$table_prefix."_products.productid = ".$table_prefix."_productcf.productid
			LEFT JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_products.productid = ".$table_prefix."_crmentity.crmid
			WHERE ".$table_prefix."_crmentity.deleted = 0 AND ".$table_prefix."_productcf.productid = ".$entity_id;
	$log->debug("ProductToRumors.php customquery ".$query);
	$result = $adb->query($query);
	if ($result && $adb->num_rows($result)>0) {
		$link_to_category = $adb->query_result($result,0,'category');
		$link_to_description = $adb->query_result($result,0,'description');
	}
	$autocomplete_return_function[$entity_id] = "return_product_to_rumors($entity_id, \"$value\", \"$forfield\", \"$link_to_category\", \"$link_to_description\");";
	$value = "<a href='javascript:void(0);' onclick='{$autocomplete_return_function[$entity_id]}closePopup();'>$value1</a>"; //crmv@21048m
}
$log->debug("Exiting ProductToRumors.php ...");
?>
