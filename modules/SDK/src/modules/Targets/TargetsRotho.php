<?php
require_once('modules/Targets/Targets.php');

class TargetsRotho extends Targets {
//danzi.tn@20130906
	var $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'Created Time'=>'createdtime',
		'Target Name'=> 'targetname',
		'Target Type'=> 'target_type',
		'Target State'=> 'target_state',
		'End Time'=> 'target_endtime',
		'Assigned To' => 'assigned_user_id'
	);

	function __construct() {
		global $log, $currentModule;
		global $table_prefix;
		$this->table_name = $table_prefix.'_targets';
		$this->customFieldTable = Array($table_prefix.'_targetscf', 'targetsid');
		$this->tab_name = Array($table_prefix.'_crmentity', $table_prefix.'_targets', $table_prefix.'_targetscf');
		$this->tab_name_index = Array(
				$table_prefix.'_crmentity' => 'crmid',
				$table_prefix.'_targets'   => 'targetsid',
			    $table_prefix.'_targetscf' => 'targetsid');
		$this->column_fields = getColumnFields($currentModule);
		$this->db = PearDatabase::getInstance();
		$this->log = $log;
		
		$this->list_fields = Array (
			/* Format: Field Label => Array(tablename, columnname) */
			// tablename should not have prefix 'vtiger_'
			'Created Time'=> Array('crmentity','createdtime'),
			'Target Name'=> Array('targets', 'targetname'),
			'Target Type'=> Array('targets', 'target_type'),
			'Target State'=> Array('targets', 'target_state'),
			'End Time'=> Array('targets', 'target_endtime'),
			'Assigned To' => Array('crmentity','smownerid')
		);
		
	}
//danzi.tn@20130906e
	
	function get_related_list_target($id, $cur_tab_id, $rel_tab_id, $actions=false) {

		global $currentModule, $app_strings, $singlepane_view;
		global $table_prefix;
		$parenttab = getParentTab();

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);
		
		// Some standard module class doesn't have required variables
		// that are used in the query, they are defined in this generic API
		vtlib_setup_modulevars($currentModule, $this);
		vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$button = '';
		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('EMAIL', $actions)) {
				// Send mail button for selected elements
				$button .= "<input title='".getTranslatedString('LBL_SEND_MAIL_BUTTON')."' class='crmbutton small edit' value='".getTranslatedString('LBL_SEND_MAIL_BUTTON')."' type='button' name='button' onclick='rel_eMail(\"$currentModule\",this,\"$related_module\")'>&nbsp;&nbsp;";
			}
			if(in_array('LOAD', $actions)) {
				/* To get CustomView -START */
				require_once('modules/CustomView/CustomView.php');
				$ahtml = "<select id='".$related_module."_cv_list' class='small'><option value='None'>-- ".getTranslatedString('Select One')." --</option>";
				$oCustomView = new CustomView($related_module);
				$viewid = $oCustomView->getViewId($related_module);
				$customviewcombo_html = $oCustomView->getCustomViewCombo($viewid, false);
				$ahtml .= $customviewcombo_html;
				$ahtml .= "</select>";
				$ahtml .= '&nbsp;&nbsp;';
				/* To get CustomView -END */
				$button .= $ahtml."<input title='".getTranslatedString('LBL_LOAD_LIST',$currentModule)."' class='crmbutton small edit' value='".getTranslatedString('LBL_LOAD_LIST',$currentModule)."' type='button' name='button' onclick='loadCvListTargets(\"$related_module\",\"$id\")'>";
				$button .= '&nbsp;&nbsp;&nbsp;&nbsp;';
			}
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module,$related_module). "' class='crmbutton small edit' " .
						" type='button' onclick=\"openPopup('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\"" . //crmv@21048m
						" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module,$related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname,$related_module) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname,$related_module) ."'>&nbsp;";
			}
		}

		// To make the edit or del link actions to return back to same view.
		if($singlepane_view == 'true') $returnset = "&return_module=$currentModule&return_action=DetailView&return_id=$id";
		else $returnset = "&return_module=$currentModule&return_action=CallRelatedList&return_id=$id";

		$query = "SELECT ".$table_prefix."_crmentity.crmid";
		//crmv@fix query
		foreach ($other->list_fields as $label=>$arr){
			foreach ($arr as $table=>$field){
				if ($table != 'crmentity' && !is_numeric($table) && $field){
					if (strpos($table,$table_prefix.'_') !== false)
						$query.=",$table.$field";
					else
						$query.=",".$table_prefix."_$table.$field";
				}	
			}
		}
		//crmv@fix query end
		$query .= ", CASE WHEN (".$table_prefix."_users.user_name is not null) THEN ".$table_prefix."_users.user_name ELSE ".$table_prefix."_groups.groupname END AS user_name";
		
		$more_relation = '';
		if(!empty($other->related_tables)) {
			foreach($other->related_tables as $tname=>$relmap) {
				// Setup the default JOIN conditions if not specified
				if(empty($relmap[1])) $relmap[1] = $other->table_name;
				if(empty($relmap[2])) $relmap[2] = $relmap[0];
				$more_relation .= " LEFT JOIN $tname ON $tname.$relmap[0] = $relmap[1].$relmap[2]";
			}
		}

		$query .= " FROM $other->table_name";
		$query .= " INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = $other->table_name.$other->table_index";
		//crmv@24527
		if (!empty($other->customFieldTable)) {
			$query .= " INNER JOIN ".$other->customFieldTable[0]." ON $other->table_name.$other->table_index = ".$other->customFieldTable[0].".".$other->customFieldTable[1];
		}
		if ($related_module == 'Contacts') {
		$query .= " INNER JOIN ".$table_prefix."_contactsubdetails
				ON ".$table_prefix."_contactsubdetails.contactsubscriptionid = ".$table_prefix."_contactdetails.contactid";
		}
		//crmv@24527e
		if ($related_module == 'Products'){
			$query .= " INNER JOIN ".$table_prefix."_seproductsrel ON (".$table_prefix."_seproductsrel.crmid = ".$table_prefix."_crmentity.crmid OR ".$table_prefix."_seproductsrel.productid = ".$table_prefix."_crmentity.crmid)";
		}
		elseif ($related_module == 'Documents'){
			$query .= " INNER JOIN ".$table_prefix."_senotesrel ON (".$table_prefix."_senotesrel.notesid = ".$table_prefix."_crmentity.crmid OR ".$table_prefix."_senotesrel.crmid = ".$table_prefix."_crmentity.crmid)";
		}
		else {
			//$query .= " INNER JOIN ".$table_prefix."_crmentityrel ON (".$table_prefix."_crmentityrel.relcrmid = ".$table_prefix."_crmentity.crmid OR ".$table_prefix."_crmentityrel.crmid = ".$table_prefix."_crmentity.crmid)";
			$query .= " INNER JOIN ".$table_prefix."_crmentityrel ON ".$table_prefix."_crmentityrel.relcrmid = ".$table_prefix."_crmentity.crmid";
		}
		$query .= " LEFT  JOIN $this->table_name   ON $this->table_name.$this->table_index = ".$table_prefix."_crmentityrel.crmid";
		$query .= $more_relation;
		$query .= " LEFT  JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid";
		$query .= " LEFT  JOIN ".$table_prefix."_groups       ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid";	
		if ($related_module == 'Products'){
			$query .= " WHERE ".$table_prefix."_crmentity.deleted = 0 AND (".$table_prefix."_seproductsrel.crmid = $id OR ".$table_prefix."_seproductsrel.productid = $id)";
		}
		elseif ($related_module == 'Documents'){
			$query .= " WHERE ".$table_prefix."_crmentity.deleted = 0 AND (".$table_prefix."_senotesrel.crmid = $id OR ".$table_prefix."_senotesrel.notesid = $id)";
		}
		else {
			//$query .= " WHERE ".$table_prefix."_crmentity.deleted = 0 AND (".$table_prefix."_crmentityrel.crmid = $id OR ".$table_prefix."_crmentityrel.relcrmid = $id)";
			$query .= " WHERE ".$table_prefix."_crmentity.deleted = 0 AND ".$table_prefix."_crmentityrel.crmid = $id";
		}
		$return_value = GetRelatedList($currentModule, $related_module, $other, $query, $button, $returnset);	

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		
		return $return_value;
	}
}
?>