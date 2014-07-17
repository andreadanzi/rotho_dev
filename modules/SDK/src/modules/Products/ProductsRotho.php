<?
require_once('modules/Products/Products.php');
// danzi.tn@20140717 creazione nuovo modulo Marketprices => get_marketprices e gestione get_tickets per related lists
class ProductsRotho extends Products {
	
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
			INNER JOIN ".$table_prefix."_contactsubdetails	ON ".$table_prefix."_contactsubdetails.contactsubscriptionid = ".$table_prefix."_contactdetails.contactid
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
	
	
	//danzi.tn@20140717 creazione nuovo modulo Marketprices
	function get_marketprices($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user,$table_prefix;
		$log->debug("Entering get_marketprices(".$id.") method ...");
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

		$rel_query = "SELECT distinct ".$table_prefix."_marketprices.* 
					, ".$table_prefix."_crmentity.crmid
					, ".$table_prefix."_crmentity.smownerid
					, ".$table_prefix."_products.productname
					, case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name
					FROM
					".$table_prefix."_marketprices
					JOIN ".$table_prefix."_marketpricescf on ".$table_prefix."_marketpricescf.marketpricesid = ".$table_prefix."_marketprices.marketpricesid
					JOIN ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$table_prefix."_marketpricescf.marketpricesid AND ".$table_prefix."_crmentity.deleted = 0
					LEFT JOIN ".$table_prefix."_groups	ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid	
					LEFT JOIN ".$table_prefix."_users	ON ".$table_prefix."_crmentity.smownerid = ".$table_prefix."_users.id
					LEFT JOIN ".$table_prefix."_seproductsrel ON ".$table_prefix."_seproductsrel.crmid = ".$table_prefix."_marketprices.marketpricesid 
					LEFT JOIN ".$table_prefix."_products ON ".$table_prefix."_products.productid = ".$table_prefix."_seproductsrel.productid OR ".$table_prefix."_products.productid = ".$table_prefix."_marketprices.product_code 
					WHERE ".$table_prefix."_crmentity.deleted = 0 
					AND ".$table_prefix."_products.productid = ".$id;

		$log->debug("get_marketprices sql = ".$rel_query);	
		$return_value = GetRelatedList($this_module, $related_module, $other, $rel_query, $button, $returnset); 
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_marketprices method ...");		
		return $return_value;

	}
	//danzi.tn@20140717e
	
	//danzi.tn@20140717 Get_tickets improved for related products
	function get_tickets($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		global $table_prefix;
		$log->debug("Entering ProductsRotho::get_tickets(".$id.") method ...");
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

		$query = "SELECT distinct ".$table_prefix."_troubletickets.ticketid
			, ".$table_prefix."_troubletickets.parent_id
			, ".$table_prefix."_troubletickets.title
			, ".$table_prefix."_troubletickets.status
			, ".$table_prefix."_troubletickets.priority
			, ".$table_prefix."_troubletickets.ticket_no
			, ".$table_prefix."_products.productid
			, ".$table_prefix."_products.productname
			, ".$table_prefix."_crmentity.crmid
			, ".$table_prefix."_crmentity.smownerid
			, convert(varchar(255),".$table_prefix."_crmentity.description) as description
			, ".$table_prefix."_crmentity.modifiedtime
			, ".$table_prefix."_users.id 
			, case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name
			FROM ".$table_prefix."_troubletickets
			INNER JOIN ".$table_prefix."_crmentity
				ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_troubletickets.ticketid
			INNER JOIN ".$table_prefix."_ticketcf
				ON ".$table_prefix."_ticketcf.ticketid = ".$table_prefix."_troubletickets.ticketid 
			LEFT JOIN ".$table_prefix."_users
				ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_seproductsrel ON ".$table_prefix."_seproductsrel.crmid = ".$table_prefix."_troubletickets.ticketid
			LEFT JOIN ".$table_prefix."_products ON ".$table_prefix."_products.productid = ".$table_prefix."_seproductsrel.productid OR ".$table_prefix."_products.productid = ".$table_prefix."_troubletickets.product_id 
			WHERE ".$table_prefix."_crmentity.deleted = 0
			AND ".$table_prefix."_products.productid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting ProductsRotho::get_tickets method ...");
		return $return_value;
	}
	//danzi.tn@20140717e
	
}
?>