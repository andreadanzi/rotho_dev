<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once('data/SugarBean.php');
require_once('include/utils/utils.php');
require_once('include/RelatedListView.php');
require_once('user_privileges/default_module_view.php');
// danzi.tn@20140411 update product category 

class Products extends CRMEntity {
	var $db, $log; // Used in class functions of CRMEntity

	var $table_name;
	var $table_index= 'productid';
    var $column_fields = Array();

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array();

	var $tab_name = Array();

	var $tab_name_index = Array();


	// danzi.tn@20140411 update product category 
	// This is the list of vtiger_fields that are in the lists.
	var $list_fields = Array(
		'Product Name'=>Array('products'=>'productname'),
		// 'Part Number'=>Array('products'=>'productcode'),
		'Description'=>Array('crmentity'=>'description'),
		'Classificazione ABC'=>Array('productscf'=>'cf_1166'),
		'Categoria Prodotto'=>Array('productscf'=>'cf_803'),
		'Product Active'=>Array('products'=>'discontinued') 
	);
	var $list_fields_name = Array(
		'Product Name'=>'productname',
		// 'Part Number'=>'productcode',
		'Description'=>'description',
		'Classificazione ABC'=>'cf_1166',
		'Categoria Prodotto'=>'cf_803',
		'Product Active'=>'discontinued'
	);

	var $list_link_field= 'productname';

	var $search_fields = Array(
		'Product Name'=>Array('products'=>'productname'),
		'Part Number'=>Array('products'=>'productcode'),
		'Unit Price'=>Array('products'=>'unit_price')
	);
	var $search_fields_name = Array(
		'Product Name'=>'productname',
		'Part Number'=>'productcode',
		'Unit Price'=>'unit_price'
	);

    var $required_fields = Array(
            'productname'=>1
    );

	// Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
	var $sortby_fields = Array();
	var $def_basicsearch_col = 'productname';

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'productname';
	var $default_sort_order = 'ASC';

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('createdtime', 'modifiedtime', 'productname','imagename');
	 // Josh added for importing and exporting -added in patch2
    var $unit_price;
	//crmv@10759
	var $search_base_field = 'productname';
	//crmv@10759 e
	/**	Constructor which will set the column_fields in this object
	 */
	function Products() {
		global $table_prefix;
		$this->table_name = $table_prefix.'_products';
		$this->customFieldTable = Array($table_prefix.'_productcf','productid');
		$this->tab_name = Array($table_prefix.'_crmentity',$table_prefix.'_products',$table_prefix.'_productcf');
		$this->tab_name_index = Array($table_prefix.'_crmentity'=>'crmid',$table_prefix.'_products'=>'productid',$table_prefix.'_productcf'=>'productid',$table_prefix.'_seproductsrel'=>'productid',$table_prefix.'_producttaxrel'=>'productid');
		$this->log =LoggerManager::getLogger('product');
		$this->log->debug("Entering Products() method ...");
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Products');
		$this->log->debug("Exiting Product method ...");
	}

	/**
	 * Function to get sort order
	 * return string  $sorder    - sortorder string either 'ASC' or 'DESC'
	 */
	function getSortOrder()
	{
		global $log,$currentModule;
		$log->debug("Entering getSortOrder() method ...");
		$use_default_order_by = '';
		//default listview sorting
		if(PerformancePrefs::getBoolean('LISTVIEW_DEFAULT_SORTING', true)) {
				$use_default_sort_order = $this->default_sort_order;
		}
		//crmv default listview customview sorting
		if ($this->customview_sort_order != '' && $use_default_sort_order != $this->customview_sort_order)
				$use_default_sort_order = $this->customview_sort_order;
		if(isset($_REQUEST['sorder']))
			$sorder = $this->db->sql_escape_string($_REQUEST['sorder']);
		elseif ($_REQUEST['override_orderby'] == 'true')
			$sorder = $use_default_sort_order;
		else
			$sorder = (($_SESSION[$currentModule.'_SORT_ORDER'] != '')?($_SESSION[$currentModule.'_SORT_ORDER']):($use_default_sort_order));

		$log->debug("Exiting getSortOrder method ...");
		return $sorder;
	}

	/**
	 * Function to get order by
	 * return string  $order_by    - fieldname(eg: 'campaignname')
	 */
	function getOrderBy()
	{
		global $log,$currentModule;
		$log->debug("Entering getOrderBy() method ...");
		$use_default_order_by = '';
		//default listview sorting
		if(PerformancePrefs::getBoolean('LISTVIEW_DEFAULT_SORTING', true)) {
				$use_default_order_by = $this->default_order_by;
		}
		//crmv default listview customview sorting
		if ($this->customview_order_by != '' && $use_default_order_by != $this->customview_order_by)
				$use_default_order_by = $this->customview_order_by;
		if (isset($_REQUEST['order_by']))
			$order_by = $this->db->sql_escape_string($_REQUEST['order_by']);
		elseif ($_REQUEST['override_orderby'] == 'true')
			$order_by = $use_default_order_by;
		else
			$order_by = (($_SESSION[$currentModule.'_ORDER_BY'] != '')?($_SESSION[$currentModule.'_ORDER_BY']):($use_default_order_by));

		$log->debug("Exiting getOrderBy method ...");
		return $order_by;
	}

	function save_module($module)
	{
		global $table_prefix;
		//Inserting into product_taxrel table
		if($_REQUEST['ajxaction'] != 'DETAILVIEW' && $_REQUEST['action'] != 'MassEditSave')	//crmv@26792
		{
			$this->insertTaxInformation($table_prefix.'_producttaxrel', 'Products');
			//crmv@20706
			if ($_REQUEST['action'] == 'ProductsAjax' &&  $_REQUEST['file'] == 'QuickCreate')
				$this->insertQCPriceInformation($table_prefix.'_productcurrencyrel', 'Products');
			else
			//crmv@20706e
				$this->insertPriceInformation($table_prefix.'_productcurrencyrel', 'Products');
		}

		if(isset($this->parentid) &&  $this->parentid!=''){
			$this->insertIntoseProductsRel($this->id,$this->parentid,$this->return_module);
		}

		// Update unit price value in vtiger_productcurrencyrel
		$this->updateUnitPrice();
		//Inserting into attachments
		$this->insertIntoAttachment($this->id,'Products');

	}

	/**	function to save the product tax information in vtiger_producttaxrel table
	 *	@param string $tablename - vtiger_tablename to save the product tax relationship (producttaxrel)
	 *	@param string $module	 - current module name
	 *	$return void
	*/
	function insertTaxInformation($tablename, $module)
	{
		global $adb, $log;
		global $table_prefix;
		$log->debug("Entering into insertTaxInformation($tablename, $module) method ...");
		$tax_details = getAllTaxes();

		$tax_per = '';
		//Save the Product - tax relationship if corresponding tax check box is enabled
		//Delete the existing tax if any
		if($this->mode == 'edit')
		{
			for($i=0;$i<count($tax_details);$i++)
			{
				$taxid = getTaxId($tax_details[$i]['taxname']);
				$sql = "delete from ".$table_prefix."_producttaxrel where productid=? and taxid=?";
				$adb->pquery($sql, array($this->id,$taxid));
			}
		}
		for($i=0;$i<count($tax_details);$i++)
		{
			$tax_name = $tax_details[$i]['taxname'];
			$tax_checkname = $tax_details[$i]['taxname']."_check";
			if($_REQUEST[$tax_checkname] == 'on' || $_REQUEST[$tax_checkname] == 1)
			{
				$taxid = getTaxId($tax_name);
				$tax_per = $_REQUEST[$tax_name];
				if($tax_per == '')
				{
					$log->debug("Tax selected but value not given so default value will be saved.");
					$tax_per = getTaxPercentage($tax_name);
				}

				$log->debug("Going to save the Product - $tax_name tax relationship");

				$query = "insert into ".$table_prefix."_producttaxrel values(?,?,?)";
				$adb->pquery($query, array($this->id,$taxid,$tax_per));
			}
		}

		$log->debug("Exiting from insertTaxInformation($tablename, $module) method ...");
	}

	/**	function to save the product price information in vtiger_productcurrencyrel table
	 *	@param string $tablename - vtiger_tablename to save the product currency relationship (productcurrencyrel)
	 *	@param string $module	 - current module name
	 *	$return void
	*/
	function insertPriceInformation($tablename, $module)
	{
		global $adb, $log, $current_user;
		global $table_prefix;
		$log->debug("Entering into insertPriceInformation($tablename, $module) method ...");
		//removed the update of currency_id based on the logged in user's preference : fix 6490

		$currency_details = getAllCurrencies('all');

		//Delete the existing currency relationship if any
		if($this->mode == 'edit' && $_REQUEST['action'] !== 'MassEditSave')
		{
			for($i=0;$i<count($currency_details);$i++)
			{
				$curid = $currency_details[$i]['curid'];
				$sql = "delete from ".$table_prefix."_productcurrencyrel where productid=? and currencyid=?";
				$adb->pquery($sql, array($this->id,$curid));
			}
		}

		$product_base_conv_rate = getBaseConversionRateForProduct($this->id, $this->mode);

		//Save the Product - Currency relationship if corresponding currency check box is enabled
		for($i=0;$i<count($currency_details);$i++)
		{
			$curid = $currency_details[$i]['curid'];
			$curname = $currency_details[$i]['currencylabel'];
			$cur_checkname = 'cur_' . $curid . '_check';
			$cur_valuename = 'curname' . $curid;
			$base_currency_check = 'base_currency' . $curid;
			if($_REQUEST[$cur_checkname] == 'on' || $_REQUEST[$cur_checkname] == 1)
			{
				$conversion_rate = $currency_details[$i]['conversionrate'];
				$actual_conversion_rate = $product_base_conv_rate * $conversion_rate;
				$converted_price = $actual_conversion_rate * $_REQUEST['unit_price'];
				$actual_price = $_REQUEST[$cur_valuename];

				$log->debug("Going to save the Product - $curname currency relationship");

				$query = "insert into ".$table_prefix."_productcurrencyrel values(?,?,?,?)";
				$adb->pquery($query, array($this->id,$curid,$converted_price,$actual_price));

				// Update the Product information with Base Currency choosen by the User.
				if ($_REQUEST['base_currency'] == $cur_valuename) {
					$adb->pquery("update ".$table_prefix."_products set currency_id=?, unit_price=? where productid=?", array($curid, $actual_price, $this->id));
				}
			}
		}

		$log->debug("Exiting from insertPriceInformation($tablename, $module) method ...");
	}

	function updateUnitPrice() {
		global $table_prefix;
		$prod_res = $this->db->pquery("select unit_price, currency_id from ".$table_prefix."_products where productid=?", array($this->id));
		$prod_unit_price = $this->db->query_result($prod_res, 0, 'unit_price');
		$prod_base_currency = $this->db->query_result($prod_res, 0, 'currency_id');

		$query = "update ".$table_prefix."_productcurrencyrel set actual_price=? where productid=? and currencyid=?";
		$params = array($prod_unit_price, $this->id, $prod_base_currency);
		$this->db->pquery($query, $params);
	}

	function insertIntoAttachment($id,$module)
	{
		global $log, $adb;
		global $table_prefix;
		$log->debug("Entering into insertIntoAttachment($id,$module) method.");

		$file_saved = false;

		foreach($_FILES as $fileindex => $files)
		{
			if($files['name'] != '' && $files['size'] > 0)
			{
			      if($_REQUEST[$fileindex.'_hidden'] != '')
				      $files['original_name'] = vtlib_purify($_REQUEST[$fileindex.'_hidden']);
			      else
				      $files['original_name'] = stripslashes($files['name']);
			      $files['original_name'] = str_replace('"','',$files['original_name']);
				$file_saved = $this->uploadAndSaveFile($id,$module,$files);
			}
		}

		//Remove the deleted vtiger_attachments from db - Products
		if($module == 'Products' && $_REQUEST['del_file_list'] != '')
		{
			$del_file_list = explode("###",trim($_REQUEST['del_file_list'],"###"));
			foreach($del_file_list as $del_file_name)
			{
				$attach_res = $adb->pquery("select ".$table_prefix."_attachments.attachmentsid from ".$table_prefix."_attachments inner join ".$table_prefix."_seattachmentsrel on ".$table_prefix."_attachments.attachmentsid=".$table_prefix."_seattachmentsrel.attachmentsid where crmid=? and name=?", array($id,$del_file_name));
				$attachments_id = $adb->query_result($attach_res,0,'attachmentsid');

				$del_res1 = $adb->pquery("delete from ".$table_prefix."_attachments where attachmentsid=?", array($attachments_id));
				$del_res2 = $adb->pquery("delete from ".$table_prefix."_seattachmentsrel where attachmentsid=?", array($attachments_id));
			}
		}

		$log->debug("Exiting from insertIntoAttachment($id,$module) method.");
	}



	/**	function used to get the list of leads which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_leads($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		global $table_prefix;
		$log->debug("Entering get_leads(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"openPopup('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;"; // crmv@21048m
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$query = "SELECT ".$table_prefix."_leaddetails.leadid, ".$table_prefix."_crmentity.crmid, ".$table_prefix."_leaddetails.firstname, ".$table_prefix."_leaddetails.lastname, ".$table_prefix."_leaddetails.company, ".$table_prefix."_leadaddress.phone, ".$table_prefix."_leadsubdetails.website, ".$table_prefix."_leaddetails.email, case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name, ".$table_prefix."_crmentity.smownerid, ".$table_prefix."_products.productname, ".$table_prefix."_products.qty_per_unit, ".$table_prefix."_products.unit_price, ".$table_prefix."_products.expiry_date
			FROM ".$table_prefix."_leaddetails
			INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_leaddetails.leadid
			INNER JOIN ".$table_prefix."_leadaddress ON ".$table_prefix."_leadaddress.leadaddressid = ".$table_prefix."_leaddetails.leadid
			INNER JOIN ".$table_prefix."_leadsubdetails ON ".$table_prefix."_leadsubdetails.leadsubscriptionid = ".$table_prefix."_leaddetails.leadid
			INNER JOIN ".$table_prefix."_leadscf ON ".$table_prefix."_leadscf.leadid = ".$table_prefix."_leaddetails.leadid
			INNER JOIN ".$table_prefix."_seproductsrel ON ".$table_prefix."_seproductsrel.crmid=".$table_prefix."_leaddetails.leadid
			INNER JOIN ".$table_prefix."_products ON ".$table_prefix."_seproductsrel.productid = ".$table_prefix."_products.productid
			LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			WHERE ".$table_prefix."_crmentity.deleted = 0 AND ".$table_prefix."_products.productid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_leads method ...");
		return $return_value;
	}

	/**	function used to get the list of accounts which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_accounts($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		global $table_prefix;
		$log->debug("Entering get_accounts(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"openPopup('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;"; // crmv@21048m
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$query = "SELECT ".$table_prefix."_account.accountid, ".$table_prefix."_crmentity.crmid, ".$table_prefix."_account.accountname, ".$table_prefix."_accountbillads.bill_city, ".$table_prefix."_account.website, ".$table_prefix."_account.phone, case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name, ".$table_prefix."_crmentity.smownerid, ".$table_prefix."_products.productname, ".$table_prefix."_products.qty_per_unit, ".$table_prefix."_products.unit_price, ".$table_prefix."_products.expiry_date
			FROM ".$table_prefix."_account
			INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_account.accountid
			INNER JOIN ".$table_prefix."_accountscf ON ".$table_prefix."_accountscf.accountid = ".$table_prefix."_account.accountid
			INNER JOIN ".$table_prefix."_accountbillads ON ".$table_prefix."_accountbillads.accountaddressid = ".$table_prefix."_account.accountid
			INNER JOIN ".$table_prefix."_seproductsrel ON ".$table_prefix."_seproductsrel.crmid=".$table_prefix."_account.accountid
			INNER JOIN ".$table_prefix."_products ON ".$table_prefix."_seproductsrel.productid = ".$table_prefix."_products.productid
			LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			WHERE ".$table_prefix."_crmentity.deleted = 0 AND ".$table_prefix."_products.productid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_accounts method ...");
		return $return_value;
	}

	/**	function used to get the list of contacts which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_contacts($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		global $table_prefix;
		$log->debug("Entering get_contacts(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"openPopup('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;"; // crmv@21048m
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$query = "SELECT ".$table_prefix."_contactdetails.firstname, ".$table_prefix."_contactdetails.lastname, ".$table_prefix."_contactdetails.title, ".$table_prefix."_contactdetails.accountid, ".$table_prefix."_contactdetails.email, ".$table_prefix."_contactdetails.phone, ".$table_prefix."_crmentity.crmid, case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name, ".$table_prefix."_crmentity.smownerid, ".$table_prefix."_products.productname, ".$table_prefix."_products.qty_per_unit, ".$table_prefix."_products.unit_price, ".$table_prefix."_products.expiry_date,".$table_prefix."_account.accountname
			FROM ".$table_prefix."_contactdetails
			INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_contactdetails.contactid
			INNER JOIN ".$table_prefix."_contactscf ON ".$table_prefix."_contactscf.contactid = ".$table_prefix."_contactdetails.contactid
			INNER JOIN ".$table_prefix."_seproductsrel ON ".$table_prefix."_seproductsrel.crmid=".$table_prefix."_contactdetails.contactid
			INNER JOIN ".$table_prefix."_products ON ".$table_prefix."_seproductsrel.productid = ".$table_prefix."_products.productid
			LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_account ON ".$table_prefix."_account.accountid = ".$table_prefix."_contactdetails.accountid
			WHERE ".$table_prefix."_crmentity.deleted = 0 AND ".$table_prefix."_products.productid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_contacts method ...");
		return $return_value;
	}


	/**	function used to get the list of potentials which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_opportunities($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		global $table_prefix;
		$log->debug("Entering get_opportunities(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"openPopup('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;"; // crmv@21048m
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$query = "SELECT ".$table_prefix."_potential.potentialid, ".$table_prefix."_crmentity.crmid, ".$table_prefix."_potential.potentialname, ".$table_prefix."_account.accountname, ".$table_prefix."_potential.related_to, ".$table_prefix."_potential.sales_stage, ".$table_prefix."_potential.amount, ".$table_prefix."_potential.closingdate, case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name, ".$table_prefix."_crmentity.smownerid, ".$table_prefix."_products.productname, ".$table_prefix."_products.qty_per_unit, ".$table_prefix."_products.unit_price, ".$table_prefix."_products.expiry_date
			FROM ".$table_prefix."_potential
			INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_potential.potentialid
			INNER JOIN ".$table_prefix."_potentialscf ON ".$table_prefix."_potentialscf.potentialid = ".$table_prefix."_potential.potentialid
			INNER JOIN ".$table_prefix."_seproductsrel ON ".$table_prefix."_seproductsrel.crmid = ".$table_prefix."_potential.potentialid
			INNER JOIN ".$table_prefix."_products ON ".$table_prefix."_seproductsrel.productid = ".$table_prefix."_products.productid
			LEFT JOIN ".$table_prefix."_account ON ".$table_prefix."_potential.related_to = ".$table_prefix."_account.accountid
			LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			WHERE ".$table_prefix."_crmentity.deleted = 0 AND ".$table_prefix."_products.productid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_opportunities method ...");
		return $return_value;
	}

	/**	function used to get the list of tickets which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_tickets($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		global $table_prefix;
		$log->debug("Entering get_tickets(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"openPopup('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;"; // crmv@21048m
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$query = "SELECT  case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name, ".$table_prefix."_users.id,
			".$table_prefix."_products.productid, ".$table_prefix."_products.productname,
			".$table_prefix."_troubletickets.ticketid,
			".$table_prefix."_troubletickets.parent_id, ".$table_prefix."_troubletickets.title,
			".$table_prefix."_troubletickets.status, ".$table_prefix."_troubletickets.priority,
			".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.smownerid,
			".$table_prefix."_crmentity.modifiedtime, ".$table_prefix."_troubletickets.ticket_no
			FROM ".$table_prefix."_troubletickets
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_troubletickets.ticketid
			INNER JOIN ".$table_prefix."_ticketcf
				ON ".$table_prefix."_ticketcf.ticketid = ".$table_prefix."_troubletickets.ticketid
			LEFT JOIN ".$table_prefix."_products
				ON ".$table_prefix."_products.productid = ".$table_prefix."_troubletickets.product_id
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			WHERE ".$table_prefix."_crmentity.deleted = 0
			AND ".$table_prefix."_products.productid = ".$id;

		$log->debug("Exiting get_tickets method ...");

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_tickets method ...");
		return $return_value;
	}

	/**	function used to get the list of activities which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_activities($id)
	{
		global $log, $singlepane_view;
		$log->debug("Entering get_activities(".$id.") method ...");
		global $app_strings;
		global $table_prefix;
        	//if($this->column_fields['contact_id']!=0 && $this->column_fields['contact_id']!='')
        	$focus = CRMEntity::getInstance('Activity');

		$button = '';

		if($singlepane_view == 'true')
			$returnset = '&return_module=Products&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module=Products&return_action=CallRelatedList&return_id='.$id;


		$query = "SELECT ".$table_prefix."_contactdetails.lastname,
			".$table_prefix."_contactdetails.firstname,
			".$table_prefix."_contactdetails.contactid,
			".$table_prefix."_activity.*,

			".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.smownerid,
			".$table_prefix."_crmentity.modifiedtime,
			".$table_prefix."_users.user_name
			FROM ".$table_prefix."_activity
			INNER JOIN ".$table_prefix."_seactivityrel
				ON ".$table_prefix."_seactivityrel.activityid = ".$table_prefix."_activity.activityid
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid=".$table_prefix."_activity.activityid
			INNER JOIN ".$table_prefix."_activitycf
				ON ".$table_prefix."_activitycf.activityid=".$table_prefix."_activity.activityid
			LEFT JOIN ".$table_prefix."_cntactivityrel
				ON ".$table_prefix."_cntactivityrel.activityid = ".$table_prefix."_activity.activityid
			LEFT JOIN ".$table_prefix."_contactdetails
				ON ".$table_prefix."_contactdetails.contactid = ".$table_prefix."_cntactivityrel.contactid
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			LEFT OUTER JOIN ".$table_prefix."_recurringevents
				ON ".$table_prefix."_recurringevents.activityid = ".$table_prefix."_activity.activityid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			WHERE ".$table_prefix."_seactivityrel.crmid=".$id."
			AND (activitytype != 'Emails')";
		$log->debug("Exiting get_activities method ...");
		return GetRelatedList('Products','Calendar',$focus,$query,$button,$returnset);
	}

	/**	function used to get the list of quotes which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_quotes($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $table_prefix;
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_quotes(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"openPopup('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;"; // crmv@21048m
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$query = "SELECT ".$table_prefix."_crmentity.*,
			".$table_prefix."_quotes.*,
			".$table_prefix."_potential.potentialname,
			".$table_prefix."_account.accountname,
			".$table_prefix."_inventoryproductrel.productid,
			case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name
				else ".$table_prefix."_groups.groupname end as user_name
			FROM ".$table_prefix."_quotes
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_quotes.quoteid
			INNER JOIN ".$table_prefix."_quotescf
				ON ".$table_prefix."_quotescf.quoteid = ".$table_prefix."_quotes.quoteid
			INNER JOIN ".$table_prefix."_inventoryproductrel
				ON ".$table_prefix."_inventoryproductrel.id = ".$table_prefix."_quotes.quoteid
			LEFT OUTER JOIN ".$table_prefix."_account
				ON ".$table_prefix."_account.accountid = ".$table_prefix."_quotes.accountid
			LEFT OUTER JOIN ".$table_prefix."_potential
				ON ".$table_prefix."_potential.potentialid = ".$table_prefix."_quotes.potentialid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			WHERE ".$table_prefix."_crmentity.deleted = 0
			AND ".$table_prefix."_inventoryproductrel.productid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_quotes method ...");
		return $return_value;
	}

	/**	function used to get the list of purchase orders which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_purchase_orders($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		global $table_prefix;
		$log->debug("Entering get_purchase_orders(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"openPopup('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;"; // crmv@21048m
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$query = "SELECT ".$table_prefix."_crmentity.*,
			".$table_prefix."_purchaseorder.*,
			".$table_prefix."_products.productname,
			".$table_prefix."_inventoryproductrel.productid,
			case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name
				else ".$table_prefix."_groups.groupname end as user_name
			FROM ".$table_prefix."_purchaseorder
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_purchaseorder.purchaseorderid
			INNER JOIN ".$table_prefix."_purchaseordercf
				ON ".$table_prefix."_purchaseordercf.purchaseorderid = ".$table_prefix."_purchaseorder.purchaseorderid
			INNER JOIN ".$table_prefix."_inventoryproductrel
				ON ".$table_prefix."_inventoryproductrel.id = ".$table_prefix."_purchaseorder.purchaseorderid
			INNER JOIN ".$table_prefix."_products
				ON ".$table_prefix."_products.productid = ".$table_prefix."_inventoryproductrel.productid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			WHERE ".$table_prefix."_crmentity.deleted = 0
			AND ".$table_prefix."_products.productid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_purchase_orders method ...");
		return $return_value;
	}

	/**	function used to get the list of sales orders which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_salesorder($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		global $table_prefix;
		$log->debug("Entering get_salesorder(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"openPopup('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;"; // crmv@21048m
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$query = "SELECT ".$table_prefix."_crmentity.*,
			".$table_prefix."_salesorder.*,
			".$table_prefix."_products.productname AS productname,
			".$table_prefix."_account.accountname,
			case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name
				else ".$table_prefix."_groups.groupname end as user_name
			FROM ".$table_prefix."_salesorder
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_salesorder.salesorderid
			INNER JOIN ".$table_prefix."_salesordercf
				ON ".$table_prefix."_salesordercf.salesorderid = ".$table_prefix."_salesorder.salesorderid
			INNER JOIN ".$table_prefix."_inventoryproductrel
				ON ".$table_prefix."_inventoryproductrel.id = ".$table_prefix."_salesorder.salesorderid
			INNER JOIN ".$table_prefix."_products
				ON ".$table_prefix."_products.productid = ".$table_prefix."_inventoryproductrel.productid
			LEFT OUTER JOIN ".$table_prefix."_account
				ON ".$table_prefix."_account.accountid = ".$table_prefix."_salesorder.accountid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			WHERE ".$table_prefix."_crmentity.deleted = 0
			AND ".$table_prefix."_products.productid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_salesorder method ...");
		return $return_value;
	}

	/**	function used to get the list of invoices which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_invoices($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		global $table_prefix;
		$log->debug("Entering get_invoices(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"openPopup('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;"; // crmv@21048m
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$query = "SELECT ".$table_prefix."_crmentity.*,
			".$table_prefix."_invoice.*,
			".$table_prefix."_inventoryproductrel.quantity,
			".$table_prefix."_account.accountname,
			case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name
				else ".$table_prefix."_groups.groupname end as user_name
			FROM ".$table_prefix."_invoice
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_invoice.invoiceid
			INNER JOIN ".$table_prefix."_invoicecf
				ON ".$table_prefix."_invoicecf.invoiceid = ".$table_prefix."_invoice.invoiceid
			LEFT OUTER JOIN ".$table_prefix."_account
				ON ".$table_prefix."_account.accountid = ".$table_prefix."_invoice.accountid
			INNER JOIN ".$table_prefix."_inventoryproductrel
				ON ".$table_prefix."_inventoryproductrel.id = ".$table_prefix."_invoice.invoiceid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_users
				ON  ".$table_prefix."_users.id=".$table_prefix."_crmentity.smownerid
			WHERE ".$table_prefix."_crmentity.deleted = 0
			AND ".$table_prefix."_inventoryproductrel.productid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_invoices method ...");
		return $return_value;
	}

	/**	function used to get the list of pricebooks which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_product_pricebooks($id, $cur_tab_id, $rel_tab_id, $actions=false)
	{
		global $log,$singlepane_view,$currentModule;
		global $table_prefix;
		$log->debug("Entering get_product_pricebooks(".$id.") method ...");

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		$focus = CRMEntity::getInstance($related_module);
		$singular_modname = vtlib_toSingular($related_module);

		$button = '';
		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_TO'). " ". getTranslatedString($related_module) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"AddProductToPriceBooks\";this.form.module.value=\"$currentModule\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_TO'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
		}

		if($singlepane_view == 'true')
			$returnset = '&return_module=Products&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module=Products&return_action=CallRelatedList&return_id='.$id;


		$query = "SELECT ".$table_prefix."_crmentity.crmid,
			".$table_prefix."_pricebook.*,
			".$table_prefix."_pricebookproductrel.productid as prodid
			FROM ".$table_prefix."_pricebook
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_pricebook.pricebookid
			INNER JOIN ".$table_prefix."_pricebookcf
				ON ".$table_prefix."_pricebookcf.pricebookid = ".$table_prefix."_pricebook.pricebookid
			INNER JOIN ".$table_prefix."_pricebookproductrel
				ON ".$table_prefix."_pricebookproductrel.pricebookid = ".$table_prefix."_pricebook.pricebookid
			WHERE ".$table_prefix."_crmentity.deleted = 0
			AND ".$table_prefix."_pricebookproductrel.productid = ".$id;
		$log->debug("Exiting get_product_pricebooks method ...");

		$return_value = GetRelatedList($currentModule, $related_module, $focus, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		return $return_value;
	}

	/**	function used to get the number of vendors which are related to the product
	 *	@param int $id - product id
	 *	@return int number of rows - return the number of products which do not have relationship with vendor
	 */
	function product_novendor()
	{
		global $log;
		global $table_prefix;
		$log->debug("Entering product_novendor() method ...");
		$query = "SELECT ".$table_prefix."_products.productname, ".$table_prefix."_crmentity.deleted
			FROM ".$table_prefix."_products
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_products.productid
			WHERE ".$table_prefix."_crmentity.deleted = 0
			AND ".$table_prefix."_products.vendor_id is NULL";
		$result=$this->db->pquery($query, array());
		$log->debug("Exiting product_novendor method ...");
		return $this->db->num_rows($result);
	}

	/**
	* Function to get Product's related Products
	* @param  integer   $id      - productid
	* returns related Products record in array format
	*/
	function get_products($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		global $table_prefix;
		$log->debug("Entering get_products(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		if($actions && $this->ismember_check() === 0) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"openPopup('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;"; // crmv@21048m
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$query = "SELECT ".$table_prefix."_products.productid, ".$table_prefix."_products.productname,
			".$table_prefix."_products.productcode, ".$table_prefix."_products.commissionrate,
			".$table_prefix."_products.qty_per_unit, ".$table_prefix."_products.unit_price,
			".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.smownerid
			FROM ".$table_prefix."_products
			INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_products.productid
			INNER JOIN ".$table_prefix."_productcf ON ".$table_prefix."_productcf.productid = ".$table_prefix."_products.productid
			LEFT JOIN ".$table_prefix."_seproductsrel ON ".$table_prefix."_seproductsrel.crmid = ".$table_prefix."_products.productid AND ".$table_prefix."_seproductsrel.setype='Products'
			WHERE ".$table_prefix."_crmentity.deleted = 0 AND ".$table_prefix."_seproductsrel.productid = $id ";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_products method ...");
		return $return_value;
	}

	/**
	* Function to get Product's related Products
	* @param  integer   $id      - productid
	* returns related Products record in array format
	*/
	function get_parent_products($id)
	{
		global $log, $singlepane_view;
                $log->debug("Entering get_products(".$id.") method ...");
		global $table_prefix;
		global $app_strings;

		$focus = CRMEntity::getInstance('Products');

		$button = '';

		if(isPermitted("Products",1,"") == 'yes')
		{
			$button .= '<input title="'.$app_strings['LBL_NEW_PRODUCT'].'" accessyKey="F" class="button" onclick="this.form.action.value=\'EditView\';this.form.module.value=\'Products\';this.form.return_module.value=\'Products\';this.form.return_action.value=\'DetailView\'" type="submit" name="button" value="'.$app_strings['LBL_NEW_PRODUCT'].'">&nbsp;';
		}
		if($singlepane_view == 'true')
			$returnset = '&return_module=Products&return_action=DetailView&is_parent=1&return_id='.$id;
		else
			$returnset = '&return_module=Products&return_action=CallRelatedList&is_parent=1&return_id='.$id;

		$query = "SELECT ".$table_prefix."_products.productid, ".$table_prefix."_products.productname,
			".$table_prefix."_products.productcode, ".$table_prefix."_products.commissionrate,
			".$table_prefix."_products.qty_per_unit, ".$table_prefix."_products.unit_price,
			".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.smownerid
			FROM ".$table_prefix."_products
			INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_products.productid
			INNER JOIN ".$table_prefix."_productcf ON ".$table_prefix."_productcf.productid = ".$table_prefix."_products.productid
			INNER JOIN ".$table_prefix."_seproductsrel ON ".$table_prefix."_seproductsrel.productid = ".$table_prefix."_products.productid AND ".$table_prefix."_seproductsrel.setype='Products'
			WHERE ".$table_prefix."_crmentity.deleted = 0 AND ".$table_prefix."_seproductsrel.crmid = $id ";

		$log->debug("Exiting get_products method ...");
		return GetRelatedList('Products','Products',$focus,$query,$button,$returnset);
	}

	/**	function used to get the export query for product
	 *	@param reference $where - reference of the where variable which will be added with the query
	 *	@return string $query - return the query which will give the list of products to export
	 */
	function create_export_query($where,$oCustomView,$viewId)	//crmv@31775
	{
		global $log;
		global $table_prefix;
		$log->debug("Entering create_export_query(".$where.") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Products", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT $fields_list FROM ".$this->table_name ."
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_products.productid
			LEFT JOIN ".$table_prefix."_productcf
				ON ".$table_prefix."_products.productid = ".$table_prefix."_productcf.productid
			INNER JOIN ".$table_prefix."_users
				ON ".$table_prefix."_users.id=".$table_prefix."_products.handler
			LEFT JOIN ".$table_prefix."_vendor
				ON ".$table_prefix."_vendor.vendorid = ".$table_prefix."_products.vendor_id";

		//crmv@31775
		$reportFilter = $oCustomView->getReportFilter($viewId);
		if ($reportFilter) {
			$tableNameTmp = $oCustomView->getReportFilterTableName($reportFilter,$current_user->id);
			$query .= " INNER JOIN $tableNameTmp ON $tableNameTmp.id = {$table_prefix}_crmentity.crmid";
		}
		//crmv@31775e
		
		$query .= " WHERE ".$table_prefix."_crmentity.deleted = 0 and ".$table_prefix."_users.status = 'Active'";

		if($where != "")
			$query .= " AND ($where) ";
		$query = $this->listQueryNonAdminChange($query, 'Products');
		$log->debug("Exiting create_export_query method ...");
		return $query;
	}

	/** Function to check if the product is parent of any other product
	*/
	function isparent_check(){
		global $adb;
		global $table_prefix;
		$isparent_query = $adb->pquery(getListQuery("Products")." AND (".$table_prefix."_products.productid IN (SELECT productid from ".$table_prefix."_seproductsrel WHERE ".$table_prefix."_seproductsrel.productid = ? AND ".$table_prefix."_seproductsrel.setype='Products'))",array($this->id));
		$isparent = $adb->num_rows($isparent_query);
		return $isparent;
	}

	/** Function to check if the product is member of other product
	*/
	function ismember_check(){
		global $adb;
		global $table_prefix;
		$ismember_query = $adb->pquery(getListQuery("Products")." AND (".$table_prefix."_products.productid IN (SELECT crmid from ".$table_prefix."_seproductsrel WHERE ".$table_prefix."_seproductsrel.crmid = ? AND ".$table_prefix."_seproductsrel.setype='Products'))",array($this->id));
		$ismember = $adb->num_rows($ismember_query);
		return $ismember;
	}

	/**
	 * Move the related records of the specified list of id's to the given record.
	 * @param String This module name
	 * @param Array List of Entity Id's from which related records need to be transfered
	 * @param Integer Id of the the Record to which the related records are to be moved
	 */
	function transferRelatedRecords($module, $transferEntityIds, $entityId) {
		global $adb,$log;
		global $table_prefix;
		$log->debug("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");

		$rel_table_arr = Array("HelpDesk"=>$table_prefix."_troubletickets","Products"=>$table_prefix."_seproductsrel","Attachments"=>$table_prefix."_seattachmentsrel",
				"Quotes"=>$table_prefix."_inventoryproductrel","PurchaseOrder"=>$table_prefix."_inventoryproductrel","SalesOrder"=>$table_prefix."_inventoryproductrel",
				"Invoice"=>$table_prefix."_inventoryproductrel","PriceBooks"=>$table_prefix."_pricebookproductrel","Leads"=>$table_prefix."_seproductsrel",
				"Accounts"=>$table_prefix."_seproductsrel","Potentials"=>$table_prefix."_seproductsrel","Contacts"=>$table_prefix."_seproductsrel",
				"Documents"=>$table_prefix."_senotesrel");

		$tbl_field_arr = Array($table_prefix."_troubletickets"=>"ticketid",$table_prefix."_seproductsrel"=>"crmid",$table_prefix."_seattachmentsrel"=>"attachmentsid",
				$table_prefix."_inventoryproductrel"=>"id",$table_prefix."_pricebookproductrel"=>"pricebookid",$table_prefix."_seproductsrel"=>"crmid",
				$table_prefix."_senotesrel"=>"notesid");

		$entity_tbl_field_arr = Array($table_prefix."_troubletickets"=>"product_id",$table_prefix."_seproductsrel"=>"crmid",$table_prefix."_seattachmentsrel"=>"crmid",
				$table_prefix."_inventoryproductrel"=>"productid",$table_prefix."_pricebookproductrel"=>"productid",$table_prefix."_seproductsrel"=>"productid",
				$table_prefix."_senotesrel"=>"crmid");

		foreach($transferEntityIds as $transferId) {
			foreach($rel_table_arr as $rel_module=>$rel_table) {
				$id_field = $tbl_field_arr[$rel_table];
				$entity_id_field = $entity_tbl_field_arr[$rel_table];
				// IN clause to avoid duplicate entries
				$sel_result =  $adb->pquery("select $id_field from $rel_table where $entity_id_field=? " .
						" and $id_field not in (select $id_field from $rel_table where $entity_id_field=?)",
						array($transferId,$entityId));
				$res_cnt = $adb->num_rows($sel_result);
				if($res_cnt > 0) {
					for($i=0;$i<$res_cnt;$i++) {
						$id_field_value = $adb->query_result($sel_result,$i,$id_field);
						$adb->pquery("update $rel_table set $entity_id_field=? where $entity_id_field=? and $id_field=?",
							array($entityId,$transferId,$id_field_value));
					}
				}
			}
		}
		//crmv@15526
		parent::transferRelatedRecords($module, $transferEntityIds, $entityId);
		//crmv@15526 end
		$log->debug("Exiting transferRelatedRecords...");
	}

	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	function generateReportsSecQuery($module,$secmodule){
		global $current_user;
		global $table_prefix;
		// crmv@29686
		// niente join se il modulo ha già i prodotti
		if ( !in_array($module, array('Invoice', 'PurchaseOrder', 'SalesOrder', 'Quotes')) || $secmodule != 'Products') {
			$query = $this->getRelationQuery($module,$secmodule,$table_prefix."_products","productid");
		}
		// crmv@29686e
		$query .= " LEFT JOIN (
				SELECT ".$table_prefix."_products.productid,
						(CASE WHEN (".$table_prefix."_products.currency_id = 1 ) THEN ".$table_prefix."_products.unit_price
							ELSE (".$table_prefix."_products.unit_price / ".$table_prefix."_currency_info.conversion_rate) END
						) AS actual_unit_price
				FROM ".$table_prefix."_products
				LEFT JOIN ".$table_prefix."_currency_info ON ".$table_prefix."_products.currency_id = ".$table_prefix."_currency_info.id
				LEFT JOIN ".$table_prefix."_productcurrencyrel ON ".$table_prefix."_products.productid = ".$table_prefix."_productcurrencyrel.productid
				AND ".$table_prefix."_productcurrencyrel.currencyid = ". $current_user->currency_id . "
			) innerProduct ON innerProduct.productid = ".$table_prefix."_products.productid
			left join ".$table_prefix."_crmentity ".$table_prefix."_crmentityProducts on ".$table_prefix."_crmentityProducts.crmid=".$table_prefix."_products.productid and ".$table_prefix."_crmentityProducts.deleted=0
			left join ".$table_prefix."_productcf on ".$table_prefix."_products.productid = ".$table_prefix."_productcf.productid
			left join ".$table_prefix."_users ".$table_prefix."_usersProducts on ".$table_prefix."_usersProducts.id = ".$table_prefix."_products.handler
			left join ".$table_prefix."_vendor ".$table_prefix."_vendorRelProducts on ".$table_prefix."_vendorRelProducts.vendorid = ".$table_prefix."_products.vendor_id ";
		return $query;
	}

	/*
	 * Function to get the relation tables for related modules
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */
	function setRelationTables($secmodule){
		global $table_prefix;
		$rel_tables = array (
			"HelpDesk" => array($table_prefix."_troubletickets"=>array("product_id","ticketid"),$table_prefix."_products"=>"productid"),
			"Quotes" => array($table_prefix."_inventoryproductrel"=>array("productid","id"),$table_prefix."_products"=>"productid"),
			"PurchaseOrder" => array($table_prefix."_inventoryproductrel"=>array("productid","id"),$table_prefix."_products"=>"productid"),
			"SalesOrder" => array($table_prefix."_inventoryproductrel"=>array("productid","id"),$table_prefix."_products"=>"productid"),
			"Invoice" => array($table_prefix."_inventoryproductrel"=>array("productid","id"),$table_prefix."_products"=>"productid"),
			"Leads" => array($table_prefix."_seproductsrel"=>array("productid","crmid"),$table_prefix."_products"=>"productid"),
			"Accounts" => array($table_prefix."_seproductsrel"=>array("productid","crmid"),$table_prefix."_products"=>"productid"),
			"Contacts" => array($table_prefix."_seproductsrel"=>array("productid","crmid"),$table_prefix."_products"=>"productid"),
			"Potentials" => array($table_prefix."_seproductsrel"=>array("productid","crmid"),$table_prefix."_products"=>"productid"),
			"Products" => array($table_prefix."_products"=>array("productid","product_id"),$table_prefix."_products"=>"productid"),
			"PriceBooks" => array($table_prefix."_pricebookproductrel"=>array("productid","pricebookid"),$table_prefix."_products"=>"productid"),
			"Documents" => array($table_prefix."_senotesrel"=>array("crmid","notesid"),$table_prefix."_products"=>"productid"),
		);
		return $rel_tables[$secmodule];
	}

	function deleteProduct2ProductRelation($record,$return_id,$is_parent){
		global $adb;
		global $table_prefix;
		if($is_parent==0){
			$sql = "delete from ".$table_prefix."_seproductsrel WHERE crmid = ? AND productid = ?";
			$adb->pquery($sql, array($record,$return_id));
		} else {
			$sql = "delete from ".$table_prefix."_seproductsrel WHERE crmid = ? AND productid = ?";
			$adb->pquery($sql, array($return_id,$record));
		}
	}

	function insertIntoseProductsRel($record_id,$parentid,$return_module){
		global $adb;
		global $table_prefix;
		$query = $adb->pquery("SELECT * from ".$table_prefix."_seproductsrel WHERE ((crmid=? and productid=?) OR (crmid=? and productid=?)) AND setype='Products'",array($record_id,$parentid,$parentid,$record_id));
		if($adb->num_rows($query)==0 && $return_module=='Products'){
			$adb->pquery("insert into ".$table_prefix."_seproductsrel values (?,?,?)",array($record_id,$parentid,$return_module));
		}
	}

	// Function to unlink all the dependent entities of the given Entity by Id
	function unlinkDependencies($module, $id) {
		global $log;
		global $table_prefix;
		//Backup Campaigns-Product Relation
		$cmp_q = 'SELECT campaignid FROM '.$table_prefix.'_campaign WHERE product_id = ?';
		$cmp_res = $this->db->pquery($cmp_q, array($id));
		if ($this->db->num_rows($cmp_res) > 0) {
			$cmp_ids_list = array();
			for($k=0;$k < $this->db->num_rows($cmp_res);$k++)
			{
				$cmp_ids_list[] = $this->db->query_result($cmp_res,$k,"campaignid");
			}
			$params = array($id, RB_RECORD_UPDATED, $table_prefix.'_campaign', 'product_id', 'campaignid', implode(",", $cmp_ids_list));
			$this->db->pquery('INSERT INTO '.$table_prefix.'_relatedlists_rb VALUES (?,?,?,?,?,?)', $params);
		}
		//we have to update the product_id as null for the campaigns which are related to this product
		$this->db->pquery('UPDATE '.$table_prefix.'_campaign SET product_id=0 WHERE product_id = ?', array($id));

		$this->db->pquery('DELETE from '.$table_prefix.'_seproductsrel WHERE productid=? or crmid=?',array($id,$id));

		parent::unlinkDependencies($module, $id);
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		global $table_prefix;
		if(empty($return_module) || empty($return_id)) return;

		if($return_module == 'Calendar') {
			$sql = 'DELETE FROM '.$table_prefix.'_seactivityrel WHERE crmid = ? AND activityid = ?';
			$this->db->pquery($sql, array($id, $return_id));
		} elseif($return_module == 'Leads' || $return_module == 'Accounts' || $return_module == 'Contacts' || $return_module == 'Potentials') {
			$sql = 'DELETE FROM '.$table_prefix.'_seproductsrel WHERE productid = ? AND crmid = ?';
			$this->db->pquery($sql, array($id, $return_id));
		} elseif($return_module == 'Vendors') {
			$sql = 'UPDATE '.$table_prefix.'_products SET vendor_id = 0 WHERE productid = ?';
			$this->db->pquery($sql, array($id));
		} else {
			$sql = 'DELETE FROM '.$table_prefix.'_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
			$params = array($id, $return_module, $return_id, $id, $return_module, $return_id);
			$this->db->pquery($sql, $params);
		}
	}

	//crmv@20706
	function insertQCPriceInformation($tablename, $module)
	{
		global $adb, $current_user;
		global $table_prefix;
		$currency_details = getAllCurrencies('all');
		$product_base_currency = fetchCurrency($current_user->id);
		$product_base_conv_rate = getBaseConversionRateForProduct($this->id, $this->mode);
		for($i=0;$i<count($currency_details);$i++)
		{
			$curid = $currency_details[$i]['curid'];
			$curname = $currency_details[$i]['currencylabel'];
			$cur_checkname = 'cur_' . $curid . '_check';
			$cur_valuename = 'curname' . $curid;
			$base_currency_check = 'base_currency' . $curid;
			if($product_base_currency == $curid)
			{
				$conversion_rate = $currency_details[$i]['conversionrate'];
				$actual_conversion_rate = $product_base_conv_rate * $conversion_rate;
				$converted_price = $actual_conversion_rate * $_REQUEST['unit_price'];
				$actual_price = $converted_price;

				$query = "insert into ".$table_prefix."_productcurrencyrel values(?,?,?,?)";
				$adb->pquery($query, array($this->id,$curid,$converted_price,$actual_price));

				$adb->pquery("update ".$table_prefix."_products set currency_id=?, unit_price=? where productid=?", array($curid, $actual_price, $this->id));
			}
		}
	}
	//crmv@20706e
}
?>
