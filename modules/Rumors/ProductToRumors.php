<?php
global $default_charset,$adb,$table_prefix,$autocomplete_return_function,$log;

// danzi.tn@20140411 update product category
$log->debug("Entering ProductToRumors.php ...");
$forfield = htmlspecialchars($_REQUEST['forfield'], ENT_QUOTES, $default_charset);
$list_result_count = $i-1;
$value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"search",$focus->popup_type);
if(isset($forfield) && $forfield != '' && $focus->popup_type != 'detailview') {
	$value1 = strip_tags($value);
	$value = htmlspecialchars(addslashes(html_entity_decode(strip_tags($value), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset); // Remove any previous html conversion
	$link_to_category = '';
	$link_to_description = '';
	// danzi.tn@20140225 aggiunto descrizione categoria category_descr
	$query = "SELECT ".$table_prefix."_products.product_cat AS category, ".$table_prefix."_crmentity.description ,".$table_prefix."_products.prod_category_desc AS category_descr 
			FROM ".$table_prefix."_products
			JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_products.productid = ".$table_prefix."_crmentity.crmid AND ".$table_prefix."_crmentity.deleted = 0 
			WHERE  ".$table_prefix."_products.productid = ".$entity_id;
	$log->debug("ProductToRumors.php customquery ".$query);
	$result = $adb->query($query);
	if ($result && $adb->num_rows($result)>0) {
		$link_to_category = $adb->query_result($result,0,'category');
		$link_to_category_descr = $adb->query_result($result,0,'category_descr');
		$link_to_description = $adb->query_result($result,0,'description');
	}
	$autocomplete_return_function[$entity_id] = "return_product_to_rumors($entity_id, \"$value\", \"$forfield\", \"$link_to_category\", \"$link_to_description\", \"$link_to_category_descr\");";
	$value = "<a href='javascript:void(0);' onclick='{$autocomplete_return_function[$entity_id]}closePopup();'>$value1</a>"; //crmv@21048m
}
$log->debug("Exiting ProductToRumors.php ...");
?>
