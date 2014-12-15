<?
//crmv@23526
function crmv_capoarea($roleid){
	global $adb;
	//mi serve avere il ruolo superiore a quello dato
	//poi avere un utente avente quel ruolo
	$sql="select parentrole from vtiger_role where roleid=?";
	$res=$adb->pquery($sql,array($roleid));
	if($res && $adb->num_rows($res) > 0){
		$parentrole=$adb->query_result($res,0,'parentrole');
		$roles=explode('::',$parentrole);
		unset($roles[array_search($roleid,$roles)]);
		$sup_role=max($roles); //il ruolo superiore a quello dato
		$sql1="select userid from vtiger_user2role where roleid=?";
		$res1=$adb->pquery($sql1,array($sup_role));
		if($res1 && $adb->num_rows($res1) > 0){
			return $adb->query_result($res1,0,'userid'); //ritorno l'id dell'utente
		}
	}
}
//crmv@23526e

//crmv@23526 seconda versione
function crmv_capoarea2($accountid,$roleid){
	global $adb;

	$sql="select area_mng_no,external_code from vtiger_account where accountid=?";
	$res=$adb->pquery($sql,array($accountid));
	if ($res){
		$area_mng=$adb->query_result($res,0,'area_mng_no');
		$ex_code =$adb->query_result($res,0,'external_code');
	}
	
	if ($ex_code != '' && $ex_code != null){
		$sql1="select id from vtiger_users where erp_code=?";////////////////
		$res1=$adb->pquery($sql1,array($area_mng));//////////////////
		if ($res1){/////////////////
			$userid = $adb->query_result($res1,0,'id');
			return $userid;
		}
	}else{
		$sql="select parentrole from vtiger_role where roleid=?";
		$res=$adb->pquery($sql,array($roleid));
		if($res && $adb->num_rows($res) > 0){
			$parentrole=$adb->query_result($res,0,'parentrole');
			$roles=explode('::',$parentrole);
			unset($roles[array_search($roleid,$roles)]);
			$sup_role=max($roles); //il ruolo superiore a quello dato
			$sql1="select vtiger_user2role.userid from vtiger_user2role inner join vtiger_users on vtiger_users.id = vtiger_user2role.userid where roleid=? and status='Active'";
			$res1=$adb->pquery($sql1,array($sup_role));
			if($res1 && $adb->num_rows($res1) > 0){
				return $adb->query_result($res1,0,'userid'); //ritorno l'id dell'utente
			}
			else{
				return 1;
			}
		}else{
			return 1; //è l'userid dell'admin
		}
	}
}
//crmv@23526e

//crmv@myrotho_blaas
function crmv_categoria($accountid){
	global $adb;
    // danzi.tn@20141212 nova classificazione cf_762 sostituito con vtiger_account.account_client_type
	$sql="select account_client_type from vtiger_account where accountid=?";
	$res=$adb->pquery($sql,array($accountid));
	if($res && $adb->num_rows($res) > 0){
		$categoria=$adb->query_result($res,0,'cf_762');
		if ($categoria == '---' || $categoria == '. / Classification not defined.' || $categoria == ''){
			$categoria = '/';
		}
	}
	return $categoria;
//crmv@myrotho_blaase
}

function getDetailAssociatedProducts_override($module,$focus) {
	
	global $log;
	$log->debug("Entering getDetailAssociatedProducts(".$module.",focus) method ...");
	global $adb,$table_prefix;
	global $mod_strings;
	global $theme;
	global $log;
	global $app_strings,$current_user;
	global $default_charset; //crmv@16267
	$theme_path="themes/".$theme."/";
	$image_path=$theme_path."images/";
	
	if($module != 'PurchaseOrder')
	{
		$colspan = '2';
	}
	else
	{
		$colspan = '1';
	}

	//Get the taxtype of this entity
	$taxtype = getInventoryTaxType($module,$focus->id);
	$currencytype = getInventoryCurrencyInfo($module, $focus->id);

	$output = '';
	//Header Rows
	$output .= '

	<table width="100%"  border="0" align="center" cellpadding="5" cellspacing="0" class="crmTable" id="proTab">
	   <tr valign="top">
	   	<td colspan="'.$colspan.'" class="dvInnerHeader"><b>'.$app_strings['LBL_ITEM_DETAILS'].'</b></td>
		<td class="dvInnerHeader" align="center" colspan="2"><b>'.
			$app_strings['LBL_CURRENCY'].' : </b>'. getTranslatedCurrencyString($currencytype['currency_name']). ' ('. $currencytype['currency_symbol'] .')
		</td>
		<td class="dvInnerHeader" align="center" colspan="2"><b>'.
			$app_strings['LBL_TAX_MODE'].' : </b>'.$app_strings[$taxtype].'
		</td>
	   </tr>
	   <tr valign="top">
		<td width=40% class="lvtCol"><font color="red">*</font>
			<b>'.$app_strings['LBL_ITEM_NAME'].'</b>
		</td>';

	//Add Quantity in Stock column for SO, Quotes and Invoice
	if($module != 'PurchaseOrder')	//crmv@18498
		$output .= '<td width=10% class="lvtCol"><b>'.$app_strings['LBL_QTY_IN_STOCK'].'</b></td>';

	$output .= '

		<td width=10% class="lvtCol"><b>'.$app_strings['LBL_QTY'].'</b></td>
		<td width=10% class="lvtCol" align="right"><b>'.$app_strings['LBL_LIST_PRICE'].'</b></td>
		<td width=12% nowrap class="lvtCol" align="right"><b>'.$app_strings['LBL_TOTAL'].'</b></td>
		<td width=13% valign="top" class="lvtCol" align="right"><b>'.$app_strings['LBL_NET_PRICE'].'</b></td>
	   </tr>
	   	';


	// DG 15 Aug 2006
	// Add "ORDER BY sequence_no" to retain add order on all inventoryproductrel items

	if($module == 'Quotes' || $module == 'PurchaseOrder' || $module == 'SalesOrder' || $module == 'Invoice' || $module == 'Ddt')	//crmv@18498
	{
		$query="select case when ".$table_prefix."_products.productid is not null then ".$table_prefix."_products.productname else ".$table_prefix."_service.servicename end as productname," .
				" case when ".$table_prefix."_products.productid is not null then 'Products' else 'Services' end as entitytype," .
				" case when ".$table_prefix."_products.productid is not null then ".$table_prefix."_products.unit_price else ".$table_prefix."_service.unit_price end as unit_price," .
				//crmv@16267
				" case when ".$table_prefix."_products.productid is not null then ".$table_prefix."_products.productcode else ".$table_prefix."_service.service_no end as productcode," .
				//crmv@16267e
				" case when ".$table_prefix."_products.productid is not null then ".$table_prefix."_products.qtyinstock else 0 end as qtyinstock, ".$table_prefix."_inventoryproductrel.* " .
				" , ".$table_prefix."_crmentity.description as descprod " .
				" from ".$table_prefix."_inventoryproductrel" .
				" left join ".$table_prefix."_products on ".$table_prefix."_products.productid=".$table_prefix."_inventoryproductrel.productid " .
				" left join ".$table_prefix."_service on ".$table_prefix."_service.serviceid=".$table_prefix."_inventoryproductrel.productid " .
				" left join ".$table_prefix."_crmentity on ".$table_prefix."_products.productid = ".$table_prefix."_crmentity.crmid " .
				" where id=? ORDER BY sequence_no";
	}

	$result = $adb->pquery($query, array($focus->id));
	$_SESSION['query_show'] = $result->sql;	//crmv@show_query
	$num_rows=$adb->num_rows($result);
	$netTotal = '0.00';
	for($i=1;$i<=$num_rows;$i++)
	{
		$sub_prod_query = $adb->pquery("SELECT productid from ".$table_prefix."_inventorysubproductrel WHERE id=? AND sequence_no=?",array($focus->id,$i));
		$subprodname_str='';
		if($adb->num_rows($sub_prod_query)>0){
			for($j=0;$j<$adb->num_rows($sub_prod_query);$j++){
				$sprod_id = $adb->query_result($sub_prod_query,$j,'productid');
				$sprod_name = getProductName($sprod_id);
				$str_sep = "";
				if($j>0) $str_sep = ":";
				$subprodname_str .= $str_sep." - ".$sprod_name;
			}
		}
		$subprodname_str = str_replace(":","<br>",$subprodname_str);

		$productid=$adb->query_result($result,$i-1,'productid');
		$entitytype=$adb->query_result($result,$i-1,'entitytype');
		$productname=$adb->query_result($result,$i-1,'productname');
		if($subprodname_str!='') $productname .= "<br/><span style='color:#C0C0C0;font-style:italic;'>".$subprodname_str."</span>";
		//crmv@16267
		$productcode=$adb->query_result($result,$i-1,'productcode');
		$productdescription= nl2br($adb->query_result($result,$i-1,'description'));
		$productdescription= html_entity_decode($productdescription,ENT_QUOTES,$default_charset);
		$descprod= nl2br($adb->query_result($result,$i-1,'descprod'));
		$descprod= html_entity_decode($descprod,ENT_QUOTES,$default_charset);
		
		$comment= nl2br(from_html($adb->query_result($result,$i-1,'comment')));
//		$comment=$adb->query_result($result,$i-1,'comment');
		//crmv@16267e
		$qtyinstock=$adb->query_result($result,$i-1,'qtyinstock');
		$qty=$adb->query_result($result,$i-1,'quantity');
		$unitprice=$adb->query_result($result,$i-1,'unit_price');
		$listprice=$adb->query_result($result,$i-1,'listprice');
		$total = $qty*$listprice;

		//Product wise Discount calculation - starts
		$discount_percent=$adb->query_result($result,$i-1,'discount_percent');
		$discount_amount=$adb->query_result($result,$i-1,'discount_amount');
		$totalAfterDiscount = $total;

		$productDiscount = '0.00';
		if($discount_percent != 'NULL' && $discount_percent != '')
		{
			$productDiscount = $total*$discount_percent/100;
			$totalAfterDiscount = $total-$productDiscount;
			//if discount is percent then show the percentage
			$discount_info_message = "$discount_percent % of $total = $productDiscount";
		}
		elseif($discount_amount != 'NULL' && $discount_amount != '')
		{
			$productDiscount = $discount_amount;
			$totalAfterDiscount = $total-$productDiscount;
			$discount_info_message = $app_strings['LBL_DIRECT_AMOUNT_DISCOUNT']." = $productDiscount";
		}
		else
		{
			$discount_info_message = $app_strings['LBL_NO_DISCOUNT_FOR_THIS_LINE_ITEM'];
		}
		//Product wise Discount calculation - ends

		$netprice = $totalAfterDiscount;
		//Calculate the individual tax if taxtype is individual
		if($taxtype == 'individual')
		{
			$taxtotal = '0.00';
			$tax_info_message = $app_strings['LBL_TOTAL_AFTER_DISCOUNT']." = $totalAfterDiscount \\n";
			$tax_details = getTaxDetailsForProduct($productid,'all');
			for($tax_count=0;$tax_count<count($tax_details);$tax_count++)
			{
				$tax_name = $tax_details[$tax_count]['taxname'];
				$tax_label = $tax_details[$tax_count]['taxlabel'];
				$tax_value = getInventoryProductTaxValue($focus->id, $productid, $tax_name);

				$individual_taxamount = $totalAfterDiscount*$tax_value/100;
				$taxtotal = $taxtotal + $individual_taxamount;
				$tax_info_message .= "$tax_label : $tax_value % = $individual_taxamount \\n";
			}
			$tax_info_message .= "\\n ".$app_strings['LBL_TOTAL_TAX_AMOUNT']." = $taxtotal";
			$netprice = $netprice + $taxtotal;
		}

		$sc_image_tag = '';
		//crmv@16644	//crmv@16267
		if ($entitytype == 'Services' && $module == 'SalesOrder') {
			$modstr = getTranslatedString('SINGLE_ServiceContracts','ServiceContracts');
			$sc_image_tag = '<a href="index.php?module=ServiceContracts&action=EditView&service_id='.$productid.'&return_module='.$module.'&return_id='.$focus->id.'">' .
							'<img border="0" src="'.vtiger_imageurl('handshake.gif', $theme).'" title="'.getTranslatedString('LBL_ADD_ITEM').' '.getTranslatedString('SINGLE_ServiceContracts','ServiceContracts').'" style="cursor: pointer;" align="absmiddle" />&nbsp;'.$modstr[0].
							'</a>';
		}
		if ($entitytype == 'Products' && $module == 'SalesOrder') {
			$modstr = getTranslatedString('SINGLE_Assets','Assets');
			$sc_image_tag = '<a href="index.php?module=Assets&action=EditView&product='.$productid.'&return_module='.$module.'&return_id='.$focus->id.'&sorderid='.$focus->id.'&account='.$focus->column_fields['account_id'].'">' .
							'<img border="0" src="'.vtiger_imageurl('handshake.gif', $theme).'" title="'.getTranslatedString('LBL_ADD_ITEM').' '.getTranslatedString('SINGLE_Assets','Assets').'" style="cursor: pointer;" align="absmiddle" />&nbsp;'.$modstr[0].
							'</a>';
		}
		//crmv@16644e	//crmv@16267e

		//For Product Name
		//crmv@16267
		$output .= '
			   <tr valign="top">
				<td class="crmTableRow small lineOnTop">
					<font color="gray">'.$productcode.'</font>
					<br><font color="black">'.$productname.' - '.$descprod.'</font>&nbsp;&nbsp;'.$sc_image_tag.'
					<br><font color="gray">'.$productdescription.'</font>
					<br><font color="gray">'.$comment.'</font>
				</td>';
		//crmv@16267e
		//Upto this added to display the Product name and comment


		if($module != 'PurchaseOrder')
		{
			$output .= '<td class="crmTableRow small lineOnTop">'.$qtyinstock.'</td>';
		}
		$output .= '<td class="crmTableRow small lineOnTop">'.$qty.'</td>';
		//mycrmv@2707m
		$output .= '
			<td class="crmTableRow small lineOnTop" align="right">
				<table width="100%" border="0" cellpadding="5" cellspacing="0">
				   <tr>
				   	<td align="right">'.$listprice.'</td>
				   </tr>
				   <tr>
					   <!-- <td align="right">(-)&nbsp;<b><a href="javascript:;" onclick="alert(\''.$discount_info_message.'\'); ">'.$app_strings['LBL_DISCOUNT'].' : </a></b></td> -->
					   <td align="right">(-)&nbsp;'.$app_strings['LBL_DISCOUNT'].' : </td>
				   </tr>
				   <tr>
				   	<td align="right" nowrap>'.$app_strings['LBL_TOTAL_AFTER_DISCOUNT'].' : </td>
				   </tr>';
		//mycrmv@2707me
		if($taxtype == 'individual')
		{
			$output .= '
				   <tr>
					   <td align="right" nowrap>(+)&nbsp;<b><a href="javascript:;" onclick="alert(\''.$tax_info_message.'\');">'.$app_strings['LBL_TAX'].' : </a></b></td>
				   </tr>';
		}
		$output .= '
				</table>
			</td>';

		//mycrmv@2707m
		$total = '&nbsp;';
		$productDiscount = $adb->query_result($result,$i-1,'erp_discount');
		if (empty($productDiscount)) {
			$productDiscount = '&nbsp;';
		}
		//mycrmv@2707me
		$output .= '
			<td class="crmTableRow small lineOnTop" align="right">
				<table width="100%" border="0" cellpadding="5" cellspacing="0">
				   <tr><td align="right">'.$total.'</td></tr>
				   <tr><td align="right">'.$productDiscount.'</td></tr>
				   <tr><td align="right" nowrap>'.$totalAfterDiscount.'</td></tr>';

		if($taxtype == 'individual')
		{
			$output .= '<tr><td align="right" nowrap>'.$taxtotal.'</td></tr>';
		}

		$output .= '
				</table>
			</td>';
		$output .= '<td class="crmTableRow small lineOnTop" valign="bottom" align="right">'.$netprice.'</td>';
		$output .= '</tr>';

		$netTotal = $netTotal + $netprice;
	}

	$output .= '</table>';

	//$netTotal should be equal to $focus->column_fields['hdnSubTotal']
	$netTotal = $focus->column_fields['hdnSubTotal'];

	//Display the total, adjustment, S&H details
	$output .= '<table width="100%" border="0" cellspacing="0" cellpadding="5" class="crmTable">';
	$output .= '<tr>';
	$output .= '<td width="88%" class="crmTableRow small" align="right"><b>'.$app_strings['LBL_NET_TOTAL'].'</td>';
	$output .= '<td width="12%" class="crmTableRow small" align="right"><b>'.$netTotal.'</b></td>';
	$output .= '</tr>';

	//Decide discount
	$finalDiscount = '0.00';
	$final_discount_info = '0';
	//if($focus->column_fields['hdnDiscountPercent'] != '') - previously (before changing to prepared statement) the selected option (either percent or amount) will have value and the other remains empty. So we can find the non selected item by empty check. But now with prepared statement, the non selected option stored as 0
	if($focus->column_fields['hdnDiscountPercent'] != '0')
	{
		$finalDiscount = ($netTotal*$focus->column_fields['hdnDiscountPercent']/100);
		$final_discount_info = $focus->column_fields['hdnDiscountPercent']." % of $netTotal = $finalDiscount";
	}
	elseif($focus->column_fields['hdnDiscountAmount'] != '0')
	{
		$finalDiscount = $focus->column_fields['hdnDiscountAmount'];
		$final_discount_info = $finalDiscount;
	}

	//Alert the Final Discount amount even it is zero
	$final_discount_info = $app_strings['LBL_FINAL_DISCOUNT_AMOUNT']." = $final_discount_info";
	$final_discount_info = 'onclick="alert(\''.$final_discount_info.'\');"';

	$output .= '<tr>';
	$output .= '<td align="right" class="crmTableRow small lineOnTop">(-)&nbsp;<b><a href="javascript:;" '.$final_discount_info.'>'.$app_strings['LBL_DISCOUNT'].'</a></b></td>';
	$output .= '<td align="right" class="crmTableRow small lineOnTop">'.$finalDiscount.'</td>';
	$output .= '</tr>';

	if($taxtype == 'group')
	{
		$taxtotal = '0.00';
		$final_totalAfterDiscount = $netTotal - $finalDiscount;
		$tax_info_message = $app_strings['LBL_TOTAL_AFTER_DISCOUNT']." = $final_totalAfterDiscount \\n";
		//First we should get all available taxes and then retrieve the corresponding tax values
		$tax_details = getAllTaxes('available','','edit',$focus->id);
		//if taxtype is group then the tax should be same for all products in vtiger_inventoryproductrel table
		for($tax_count=0;$tax_count<count($tax_details);$tax_count++)
		{
			$tax_name = $tax_details[$tax_count]['taxname'];
			$tax_label = $tax_details[$tax_count]['taxlabel'];
			$tax_value = $adb->query_result($result,0,$tax_name);
			if($tax_value == '' || $tax_value == 'NULL')
				$tax_value = '0.00';

			$taxamount = ($netTotal-$finalDiscount)*$tax_value/100;
			$taxtotal = $taxtotal + $taxamount;
			$tax_info_message .= "$tax_label : $tax_value % = $taxamount \\n";
		}
		$tax_info_message .= "\\n ".$app_strings['LBL_TOTAL_TAX_AMOUNT']." = $taxtotal";

		$output .= '<tr>';
		$output .= '<td align="right" class="crmTableRow small">(+)&nbsp;<b><a href="javascript:;" onclick="alert(\''.$tax_info_message.'\');">'.$app_strings['LBL_TAX'].'</a></b></td>';
		$output .= '<td align="right" class="crmTableRow small">'.$taxtotal.'</td>';
		$output .= '</tr>';
	}

	$shAmount = ($focus->column_fields['hdnS_H_Amount'] != '')?$focus->column_fields['hdnS_H_Amount']:'0.00';
	$output .= '<tr>';
	$output .= '<td align="right" class="crmTableRow small">(+)&nbsp;<b>'.$app_strings['LBL_SHIPPING_AND_HANDLING_CHARGES'].'</b></td>';
	$output .= '<td align="right" class="crmTableRow small">'.$shAmount.'</td>';
	$output .= '</tr>';

	//calculate S&H tax
	$shtaxtotal = '0.00';
	//First we should get all available taxes and then retrieve the corresponding tax values
	$shtax_details = getAllTaxes('available','sh','edit',$focus->id);
	//if taxtype is group then the tax should be same for all products in vtiger_inventoryproductrel table
	$shtax_info_message = $app_strings['LBL_SHIPPING_AND_HANDLING_CHARGE']." = $shAmount \\n";
	for($shtax_count=0;$shtax_count<count($shtax_details);$shtax_count++)
	{
		$shtax_name = $shtax_details[$shtax_count]['taxname'];
		$shtax_label = $shtax_details[$shtax_count]['taxlabel'];
		$shtax_percent = getInventorySHTaxPercent($focus->id,$shtax_name);
		$shtaxamount = $shAmount*$shtax_percent/100;
		$shtaxtotal = $shtaxtotal + $shtaxamount;
		$shtax_info_message .= "$shtax_label : $shtax_percent % = $shtaxamount \\n";
	}
	$shtax_info_message .= "\\n ".$app_strings['LBL_TOTAL_TAX_AMOUNT']." = $shtaxtotal";

	$output .= '<tr>';
	$output .= '<td align="right" class="crmTableRow small">(+)&nbsp;<b><a href="javascript:;" onclick="alert(\''.$shtax_info_message.'\')">'.$app_strings['LBL_TAX_FOR_SHIPPING_AND_HANDLING'].'</a></b></td>';
	$output .= '<td align="right" class="crmTableRow small">'.$shtaxtotal.'</td>';
	$output .= '</tr>';

	$adjustment = ($focus->column_fields['txtAdjustment'] != '')?$focus->column_fields['txtAdjustment']:'0.00';
	$output .= '<tr>';
	$output .= '<td align="right" class="crmTableRow small">&nbsp;<b>'.$app_strings['LBL_ADJUSTMENT'].'</b></td>';
	$output .= '<td align="right" class="crmTableRow small">'.$adjustment.'</td>';
	$output .= '</tr>';

	$grandTotal = ($focus->column_fields['hdnGrandTotal'] != '')?$focus->column_fields['hdnGrandTotal']:'0.00';
	$output .= '<tr>';
	$output .= '<td align="right" class="crmTableRow small lineOnTop"><b>'.$app_strings['LBL_GRAND_TOTAL'].'</b></td>';
	$output .= '<td align="right" class="crmTableRow small lineOnTop">'.$grandTotal.'</td>';
	$output .= '</tr>';
	$output .= '</table>';

	$log->debug("Exiting getDetailAssociatedProducts method ...");
	return $output;
	
}
?>