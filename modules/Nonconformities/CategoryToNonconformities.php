<?php
global $default_charset,$adb,$table_prefix,$autocomplete_return_function,$log;
$log->debug("Entering CategoryToNonconformities.php ...");
$forfield = htmlspecialchars($_REQUEST['forfield'], ENT_QUOTES, $default_charset);
$list_result_count = $i-1;
$value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"search",$focus->popup_type);
if(isset($forfield) && $forfield != '' && $focus->popup_type != 'detailview') {
	$value1 = strip_tags($value);
	$value = htmlspecialchars(addslashes(html_entity_decode(strip_tags($value), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset); // Remove any previous html conversion
	$product_description = '';
	$product_category = '';
	$vendor_id = '';
	$vendor_descr = '';
	$query = "SELECT ".$table_prefix."_productcf.cf_803 AS prodcat, ".$table_prefix."_crmentity.description as proddesc, ".$table_prefix."_products.vendor_id as prodvendid, ".$table_prefix."_vendor.vendorname as prodvendname FROM ".$table_prefix."_products LEFT JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_products.productid = ".$table_prefix."_crmentity.crmid   LEFT JOIN ".$table_prefix."_vendor ON ".$table_prefix."_products.vendor_id = ".$table_prefix."_vendor.vendorid  LEFT JOIN ".$table_prefix."_productcf ON  ".$table_prefix."_products.productid = ".$table_prefix."_productcf.productid	WHERE ".$table_prefix."_products.productid = ".$entity_id;
	$log->debug("CategoryToNonconformities.php customquery ".$query);
	$result = $adb->query($query);
	if ($result && $adb->num_rows($result)>0) {
		$product_description = $adb->query_result($result,0,'proddesc');
		$product_category = $adb->query_result($result,0,'prodcat');
		$vendor_id = $adb->query_result($result,0,'prodvendid');
		$vendor_descr = $adb->query_result($result,0,'prodvendname');
	}
	$autocomplete_return_function[$entity_id] = "return_category_to_nonconformity($entity_id, \"$value\", \"$forfield\", \"$product_description\", \"$product_category\", \"$vendor_id\", \"$vendor_descr\");";
	$value = "<a href='javascript:void(0);' onclick='{$autocomplete_return_function[$entity_id]}closePopup();'>$value1</a>"; //crmv@21048m
}
$log->debug("Exiting CategoryToNonconformities.php ...");
?>
