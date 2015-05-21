<?php
global $default_charset,$adb,$table_prefix,$autocomplete_return_function,$log;
// danzi.tn@20140411 update product category 
// danzi.tn@20150316 added purchase_user_id
// danzi.tn@20150505 nuovo campo Responsabile strategico di prodotto
$log->debug("Entering CategoryToNonconformities.php ...");
$forfield = htmlspecialchars($_REQUEST['forfield'], ENT_QUOTES, $default_charset);
$list_result_count = $i-1;
$value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"search",$focus->popup_type);
if(isset($forfield) && $forfield != '' && $focus->popup_type != 'detailview') {
	$value1 = strip_tags($value);
	$value = htmlspecialchars(addslashes(html_entity_decode(strip_tags($value), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset); // Remove any previous html conversion
	$product_description = '';
	$product_category = '';
	$product_resp_no = '';
	$product_resp_name = '';
	$prodcatdescr = '';
	$vendor_id = '';
	$purchase_user_id = '';
	$purchase_user_id_display = '';
	$vendor_descr = '';
	$query = "SELECT ".$table_prefix."_products.product_cat AS prodcat, ".$table_prefix."_products.prod_category_desc AS prodcatdescr, ".$table_prefix."_crmentity.description as proddesc, ".$table_prefix."_products.vendor_id as prodvendid, ".$table_prefix."_vendor.vendorname as prodvendname , ".$table_prefix."_vendor.purchase_user_id AS purchase_user_id 
	, ".$table_prefix."_users.user_name as purchase_user_uname, ".$table_prefix."_users.last_name as purchase_user_lname, ".$table_prefix."_users.first_name as purchase_user_fname
    , ".$table_prefix."_products.product_resp_no , ".$table_prefix."_products.product_resp_name 
	FROM ".$table_prefix."_products 
	LEFT JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_products.productid = ".$table_prefix."_crmentity.crmid   
	LEFT JOIN ".$table_prefix."_vendor ON ".$table_prefix."_products.vendor_id = ".$table_prefix."_vendor.vendorid  
	LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_vendor.purchase_user_id 
	WHERE ".$table_prefix."_products.productid = ".$entity_id;
	$log->debug("CategoryToNonconformities.php customquery ".$query);
	$result = $adb->query($query);
	if ($result && $adb->num_rows($result)>0) {
		$product_description = $adb->query_result($result,0,'proddesc');
		$product_category = $adb->query_result($result,0,'prodcat');
		$prodcatdescr = $adb->query_result($result,0,'prodcatdescr');
		$vendor_id = $adb->query_result($result,0,'prodvendid');
		$vendor_descr = $adb->query_result($result,0,'prodvendname');
        $product_resp_no = $adb->query_result($result,0,'product_resp_no');
        $product_resp_name = $adb->query_result($result,0,'product_resp_name');
		$purchase_user_id = $adb->query_result($result,0,'purchase_user_id');
		$purchase_user_id_display = $adb->query_result($result,0,'purchase_user_uname') ." (".$adb->query_result($result,0,'purchase_user_lname')." ".$adb->query_result($result,0,'purchase_user_fname').")";
	}
	$autocomplete_return_function[$entity_id] = "return_category_to_nonconformity($entity_id, \"$value\", \"$forfield\", \"$product_description\", \"$product_category\", \"$vendor_id\", \"$vendor_descr\", \"$purchase_user_id\", \"$purchase_user_id_display\", \"$product_resp_no\", \"$product_resp_name\");";
	$value = "<a href='javascript:void(0);' onclick='{$autocomplete_return_function[$entity_id]}closePopup();'>$value1</a>"; //crmv@21048m
}
$log->debug("Exiting CategoryToNonconformities.php ...");
?>
