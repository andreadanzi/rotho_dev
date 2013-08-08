<?php
/*+*******************************************************************************
 *  The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 *********************************************************************************/

require_once 'include/ListView/ListViewController.php';
/**
 * Description of ListViewController
 *
 * @author MAK
 */
class ListViewByProductController extends ListViewController {

	public function getInstance($db, $user, $generator) {
		$modName = 'ListViewByProductController';
		$sdkClass = SDK::getClass($modName);
	  	if (!empty($sdkClass)) {
	  		if (!class_exists($sdkClass['module'])) {
	  			checkFileAccess($sdkClass['src']);
	  			require_once($sdkClass['src']);
	  		}
	  		$modName = $sdkClass['module'];
	  	}
	  	$focus = new $modName($db, $user, $generator);
		return $focus;
	}

	public function __construct($db, $user, $generator) {
		parent::__construct($db, $user, $generator);
	}

	/**This function generates the List view entries in a list view
	 * Param $focus - module object
	 * Param $result - resultset of a listview query
	 * Param $navigation_array - navigation values in an array
	 * Param $relatedlist - check for related list flag
	 * Param $returnset - list query parameters in url string
	 * Param $edit_action - Edit action value
	 * Param $del_action - delete action value
	 * Param $oCv - vtiger_customview object
	 * Returns an array type
	 */
	function getListViewEntries($focus, $module,$result,$navigationInfo,$skipActions=false) {
		$_SESSION['query_show'] = $result->sql;	//crmv@show_query
		require('user_privileges/user_privileges_'.$this->user->id.'.php');
		global $listview_max_textlength, $theme,$default_charset,$current_user,$table_prefix;
		$fields = $this->queryGenerator->getFields();
		$whereFields = $this->queryGenerator->getWhereFields();
		$meta = $this->queryGenerator->getMeta($this->queryGenerator->getModule());
		//crmv@7230
		$used_status_field = getUsedStatusField($module);
		$tabid = getTabid($module);
		//crmv@7230e
		$moduleFields = $meta->getModuleFields();
		$accessibleFieldList = array_keys($moduleFields);
		$listViewFields = array_intersect($fields, $accessibleFieldList);
		$listViewFields[count($listViewFields)]="cf_1078";
		$referenceFieldList = $this->queryGenerator->getReferenceFieldList();
		foreach ($referenceFieldList as $fieldName) {
			if (in_array($fieldName, $listViewFields)) {
				$field = $moduleFields[$fieldName];
				$this->fetchNameList($field, $result);
			}
		}

		$db = PearDatabase::getInstance();
		$rowCount = $db->num_rows($result);
		$ownerFieldList = $this->queryGenerator->getOwnerFieldList();
		foreach ($ownerFieldList as $fieldName) {
			if (in_array($fieldName, $listViewFields)) {
				$field = $moduleFields[$fieldName];
				$idList = array();
				for ($i = 0; $i < $rowCount; $i++) {
					$id = $this->db->query_result($result, $i, $field->getColumnName());
					if (!isset($this->ownerNameList[$fieldName][$id])) {
						$idList[] = $id;
					}
				}
				$this->ownerNameList[$fieldName] = getOwnerNameList($idList);
			}
		}

		foreach ($listViewFields as $fieldName) {
			$field = $moduleFields[$fieldName];
			//crmv@9433
			$conditional_fieldid[] = $field->getFieldId();
			//crmv@9433 end
			if(!$is_admin && ($field->getFieldDataType() == 'picklist' ||
					$field->getFieldDataType() == 'multipicklist')) {
				$this->setupAccessiblePicklistValueList($fieldName);
			}
		}
	   	//crmv@9433
		if (vtlib_isModuleActive('Conditionals') && !is_admin($current_user)){
			include_once('modules/Conditionals/ConditionalsUI.php');
			$conditional_fields_arr = getConditionalFields($module);
		}
		//crmv@9433 end
		$data = array();
		for ($i = 0; $i < $rowCount; ++$i) {
			//Getting the recordId
			if($module != 'Users') {
				$baseTable = $meta->getEntityBaseTable();
				$moduleTableIndexList = $meta->getEntityTableIndexList();
				$baseTableIndex = $moduleTableIndexList[$baseTable];

				$recordId = $db->query_result($result,$i,$baseTableIndex);
				$ownerId = $db->query_result($result,$i,"smownerid");
			}else {
				$recordId = $db->query_result($result,$i,"id");
			}
			//crmv@17001 : Private Permissions
			if($module == 'Calendar')
				$visibility = $db->query_result($result,$i,"visibility");
			//crmv@17001e
			$row = array();
			//crmv@9433
			if (vtlib_isModuleActive('Conditionals') && !is_admin($current_user) && is_array($conditional_fields_arr)){
				foreach ($conditional_fields_arr as $arr_cond){
					$conditional_column_fields[$arr_cond[fieldname]] = $this->db->query_result($result, $i, $arr_cond[columnname]);
				}
				include_once('modules/Conditionals/Conditionals.php');
				$conditionals_obj = new Conditionals($module,$tabid,$conditional_column_fields);
				$conditional_rules = $conditionals_obj->permissions;
			}
			//crmv@9433 end
			//crmv@21618
			if($module == 'Calendar') {
				$activityType = $this->db->query_result($result, $i, 'activitytype');
			}
			//crmv@21618e
			//crmv@18744
			//Added for Actions ie., edit and delete links in listview
			$actionLinkInfo = "";
			if(isPermitted($module,"EditView","") == 'yes' && $module != 'Sms'){	//crmv@16703
			//crmv@fix Calendar
				$edit_link = $this->getListViewEditLink($module,$recordId,$activityType);
			//crmv@fix Calendar end
				if(isset($navigationInfo['start']) && $navigationInfo['start'] > 1 && $module != 'Emails') {
					$actionLinkInfo .= "<a href=\"$edit_link&start=".$navigationInfo['start']."\"><img src='".vtiger_imageurl('small_edit.png',$theme)."' title='".getTranslatedString("LBL_EDIT",$module)."' border=0 /></a> ";
				} else {
					$actionLinkInfo .= "<a href=\"$edit_link\"><img src='".vtiger_imageurl('small_edit.png',$theme)."' title='".getTranslatedString("LBL_EDIT",$module)."' border=0 /></a> ";
				}
			}

			if(isPermitted($module,"Delete","") == 'yes'){
				$del_link = $this->getListViewDeleteLink($module,$recordId);
				if($actionLinkInfo != "" && $del_link != "")
					$actionLinkInfo .=  "&nbsp;";
				if($del_link != "")
					$actionLinkInfo .=	"<a href='javascript:confirmdelete(\"".addslashes(urlencode($del_link))."\")'><img src='".vtiger_imageurl('small_delete.png',$theme)."' title='".getTranslatedString("LBL_DELETE",$module)."' border=0 /></a>";
			}
			// Record Change Notification
			//crmv@23685
			$change_indic = PerformancePrefs::getBoolean('LISTVIEW_RECORD_CHANGE_INDICATOR', true);
			if(method_exists($focus, 'isViewed') && $change_indic) {
			//crmv@23685e
				if(!$focus->isViewed($recordId)) {
					$actionLinkInfo .= "&nbsp;<img src='" . vtiger_imageurl('important1.gif',$theme) . "' border=0>";
				}
			}
			// END
			if(!$skipActions && ($change_indic || $actionLinkInfo != "")) { //crmv@23685
				$row[] = $actionLinkInfo;
			}
			//crmv@18744e

			foreach ($listViewFields as $fieldName) {
				$field = $moduleFields[$fieldName];
				$uitype = $field->getUIType();
				$rawValue = $this->db->query_result($result, $i, $field->getColumnName());
				
				//crmv@fix Calendar
				if($module == 'Calendar' && ($fieldName=='status' || $fieldName=='taskstatus')){ //crmv@33466
					if($activityType == 'Task'){
						$fieldName='taskstatus';
					} else {
						$fieldName='eventstatus';
						$rawValue = $this->db->query_result($result, $i, $fieldName);
					}
				}
				//crmv@fix Calendar end
				if(stristr(html_entity_decode($rawValue), "<a href") === false &&
						$field->getUIType() != 8  && !($field->getFieldDataType() == 'picklist' || $field->getFieldDataType() == 'multipicklist')){ //crmv@29102
					$value = textlength_check($rawValue);
				}elseif($uitype != 8){
					$value = html_entity_decode($rawValue,ENT_QUOTES);
				}else{
					$value = $rawValue;
				}
				//crmv@9433		crmv@sdk-18508
				if (vtlib_isModuleActive('Conditionals')){
					$conditional_permissions = null;
					if(!is_admin($current_user) && $fieldName != "") {
							$conditional_permissions = $conditional_rules[$field->getFieldId()];
					}
				}
				$readonly = $field->getReadOnly();
				if(vtlib_isModuleActive('Conditionals') && $conditional_permissions != null && $conditional_permissions['f2fp_visible'] == "0") {
					$readonly = 100;
				}
				$sdk_files = SDK::getViews($module,'list');
				if (!empty($sdk_files)) {
					foreach($sdk_files as $sdk_file) {
						$success = false;
						$readonly_old = $readonly;
						$fieldname = $fieldName;
						include($sdk_file['src']);
						SDK::checkReadonly($readonly_old,$readonly,$sdk_file['mode']);
						if ($success && $sdk_file['on_success'] == 'stop') {
							break;
						}
					}
				}
				if ($readonly == 100) {
					$value = "<font color='red'>".getTranslatedString('LBL_NOT_ACCESSIBLE')."</font>";
				}
				//crmv@9433 end	   crmv@sdk-18508e
				//crmv@17001 : Private Permissions
				elseif(SDK::isUitype($field->getUIType())) {
					$sdk_file = SDK::getUitypeFile('php','list',$field->getUIType());
					$sdk_value = $value;
					if ($sdk_file != '') {
						include($sdk_file);
					}
				//crmv@sdk-18509 e
				} elseif ($field->getUIType() == '27') {
					if ($value == 'I') {
						$value = getTranslatedString('LBL_INTERNAL',$module);
					}elseif ($value == 'E') {
						$value = getTranslatedString('LBL_EXTERNAL',$module);
					}else {
						$value = ' --';
					}
				}elseif ($field->getFieldDataType() == 'picklist') {
					//crmv@27889
					$value = correctEncoding($value);
					if ($value != '' && !$is_admin && $this->picklistRoleMap[$fieldName] &&
							!in_array($value, array_keys($this->picklistValueMap[$fieldName]))) {
					//crmv@27889e
						$value = "<font color='red'>".getTranslatedString('LBL_NOT_ACCESSIBLE',
								$module)."</font>";
					} else {
					//crmv@fix translate
						$value = textlength_check(getTranslatedString($value,$module));
					//crmv@fix translate end
					}
				//crmv@picklistmultilanguage
				}elseif ($field->getFieldDataType() == 'picklistmultilanguage') {
					$value = textlength_check(PickListMulti::getTranslatedPicklist($value,$fieldName));
				//crmv@picklistmultilanguage end
				}elseif($field->getFieldDataType() == 'date' ||
						$field->getFieldDataType() == 'datetime') {
					//crmv@fix date
					if ($field->getFieldDataType() == 'date'){
						$value = substr($value,0,10);
						//crmv@calendar fix
						if ($module == 'Calendar' && $fieldName == 'date_start'){
							$time_start = $this->db->query_result($result, $i, 'time_start');
							$value .= " $time_start";
						}
						//crmv@16703
						if ($module == 'Sms' && $fieldName == 'date_start'){
							$sql="select sms_flag from ".$table_prefix."_smsdetails where smsid=?";
							$tmp_res=$db->pquery($sql, array($recordId));
							$sms_flag=$db->query_result($tmp_res,0,"sms_flag");
							if($sms_flag != 'SENT') $value = '';
						}
						//crmv@16703e
					}
					// crmv@25610
					$removetime = false;
					if ($module == 'Calendar' && $fieldName == 'due_date'){
						$value .= ' '.$this->db->query_result($result, $i, 'time_end');
						$removetime = true;
					}
					$value = adjustTimezone($value, $current_user->timezonediff);
					if ($module == 'Calendar' && $fieldName == 'date_start'){
						//remove seconds
						$value = substr($value, 0, 16);
					}
					if ($removetime) $value = substr($value, 0, 10);
					// crmv@25610e
					//crmv@fix date	end
					if($value != '' && $value != '0000-00-00') {
						$value = getDisplayDate($value);
					} elseif ($value == '0000-00-00') {
						$value = '';
					}
				} elseif(in_array($fieldName,array('time_start','time_end')) && $module == 'Calendar' && !empty($value)) {
					$value = adjustTimezone($value, $current_user->timezonediff);
					// strip the date (if the date is different, there's a problem)
					if (strlen($value) > 5) {
						$value = substr($value, -8, 5);
					}
				} elseif($field->getUIType() == 71 || $field->getUIType() == 72) {
					if($value != '') {
						if($fieldName == 'unit_price') {
							$currencyId = getProductBaseCurrency($recordId,$module);
							$cursym_convrate = getCurrencySymbolandCRate($currencyId);
							$value = "<font style='color:grey;'>".$cursym_convrate['symbol'].
								"</font> ". $value;
						} else {
							$rate = $user_info['conv_rate'];
							//changes made to remove vtiger_currency symbol infront of each
							//vtiger_potential amount
							if ($value != 0) {
								$value = convertFromDollar($value,$rate);
							}
						}
					}
				} elseif($field->getFieldDataType() == 'url') {
					$value = '<a href="http://'.$rawValue.'" target="_blank">'.$value.'</a>';
				//crmv@28670
				} elseif($field->getUIType() == 55) {
					$value = getTranslatedString($value,$module);
				//crmv@28670e
				} elseif ($field->getFieldDataType() == 'email') {
					if($_SESSION['internal_mailer'] == 1) {
						//check added for email link in user detailview
						$fieldId = $field->getFieldId();
						$value = "<a href=\"javascript:InternalMailer($recordId,$fieldId,".
						"'$fieldName','$module','record_id');\">$value</a>";
					}else {
						$value = '<a href="mailto:'.$rawValue.'">'.$value.'</a>';
					}
				} elseif($field->getFieldDataType() == 'boolean') {
					if($value == 1) {
						$value = getTranslatedString('yes',$module);
					} elseif($value == 0) {
						$value = getTranslatedString('no',$module);
					} else {
						$value = '--';
					}
				} elseif($field->getUIType() == 98) {
					$value = '<a href="index.php?action=RoleDetailView&module=Settings&parenttab='.
						'Settings&roleid='.$value.'">'.textlength_check(getRoleName($value)).'</a>';
				} elseif($field->getFieldDataType() == 'multipicklist') {
					$value = correctEncoding($value);
					if(!$is_admin && $value != '') {
						$valueArray = ($value != "") ? explode(' |##| ',$value) : array();
						$notaccess = '<font color="red">'.getTranslatedString('LBL_NOT_ACCESSIBLE',
								$module)."</font>";
						$tmp = '';
						$tmpArray = array();
						foreach($valueArray as $index => $val) {
							if(!$listview_max_textlength ||
									!(strlen(preg_replace("/(<\/?)(\w+)([^>]*>)/i","",$tmp)) >
											$listview_max_textlength)) {
								if (!$is_admin && $this->picklistRoleMap[$fieldName] &&
										!in_array(trim($val), array_keys($this->picklistValueMap[$fieldName]))) {	//crmv@27889
									$tmpArray[] = $notaccess;
									$tmp .= ', '.$notaccess;
								} else {
									$tmpArray[] = $val;
									$tmp .= ', '.$val;
								}
							} else {
								$tmpArray[] = '...';
								$tmp .= '...';
							}
						}
						$value = implode(', ', $tmpArray);
					} else {
						$value = ($value != "") ? str_replace(' |##| ',', ',$value) : "";
					}
				} elseif ($field->getFieldDataType() == 'skype') {
					$value = ($value != "") ? "<a href='skype:$value?call'>$value</a>" : "";
				//crmv@17471
				} elseif ($field->getFieldDataType() == 'phone' && get_use_asterisk($current_user->id) == 'true') {
					$value = "<a href='javascript:;' onclick='startCall(&quot;$value&quot;, ".
						"&quot;$recordId&quot;)'>$value</a>";
				//crmv@17471 end
				} elseif($field->getFieldDataType() == 'reference') {
					$referenceFieldInfoList = $this->queryGenerator->getReferenceFieldInfoList();
					$moduleList = $referenceFieldInfoList[$fieldName];
					if(count($moduleList) == 1) {
						$parentModule = $moduleList[0];
					} else {
						$parentModule = $this->typeList[$value];
					}
					if(!empty($value) && !empty($this->nameList[$fieldName]) && !empty($parentModule)) {
						$parentMeta = $this->queryGenerator->getMeta($parentModule);
						$value = textlength_check($this->nameList[$fieldName][$value]);
						if ($parentMeta->isModuleEntity() && $parentModule != "Users") {
							$value = "<a href='index.php?module=$parentModule&action=DetailView&".
								"record=$rawValue' title='$parentModule'>$value</a>";
						}
					} else {
						$value = '--';
					}
				} elseif($field->getFieldDataType() == 'owner') {
					$value = textlength_check($this->ownerNameList[$fieldName][$value]);
				} elseif ($field->getUIType() == 25) {
					//TODO clean request object reference.
					$contactId=$_REQUEST['record'];
					$emailId=$this->db->query_result($result,$i,"activityid");
					$result1 = $this->db->pquery("SELECT access_count FROM ".$table_prefix."_email_track WHERE ".
							"crmid=? AND mailid=?", array($contactId,$emailId));
					$value=$this->db->query_result($result1,0,"access_count");
					if(!$value) {
						$value = 0;
					}
				} elseif($field->getUIType() == 8){
					if(!empty($value)){
						$temp_val = html_entity_decode($value,ENT_QUOTES,$default_charset);
						$json = new Zend_Json();
						$value = vt_suppressHTMLTags(implode(',',$json->decode($temp_val)));
					}
				}
				//crmv@18338
				elseif($field->getUIType() == 1020){
					$temp_val = $value;
					$value=time_duration(abs($temp_val));
					if (strpos($fieldName,"remaining")!==false || strpos($fieldName,"_out_")!==false){
						if (strpos($fieldName,"remaining")!==false){
							if ($temp_val<=0)
								$color = "red";
							else
								$color = "green";
						}
						if (strpos($fieldName,"_out_")!==false){
							if ($temp_val>0)
								$color = "red";
							else
								$color = "green";
						}
						$value = "<font color=$color>$value</font>";
					}
				}
				//crmv@18338 end
				elseif ($fieldName == 'expectedroi' || $fieldName == 'actualroi' ||
						$fieldName == 'actualcost' || $fieldName == 'budgetcost' ||
						$fieldName == 'expectedrevenue') {
					$rate = $user_info['conv_rate'];
					$value = convertFromDollar($value,$rate);
				} elseif(($module == 'Invoice' || $module == 'Quotes' || $module == 'PurchaseOrder' ||
						$module == 'SalesOrder') && ($fieldName == 'hdnGrandTotal' ||
						$fieldName == 'hdnSubTotal' || $fieldName == 'txtAdjustment' ||
						$fieldName == 'hdnDiscountAmount' || $fieldName == 'hdnS_H_Amount')) {
					$currencyInfo = getInventoryCurrencyInfo($module, $recordId);
					$currencyId = $currencyInfo['currency_id'];
					$currencySymbol = $currencyInfo['currency_symbol'];
					$value = $currencySymbol.$value;
				//crmv@21092	crmv@23734
				} elseif ($field->getFieldDataType() == 'text') {
					$temp_val = preg_replace("/(<\/?)(\w+)([^>]*>)/i","",$rawValue);
					$temp_val = trim(html_entity_decode($temp_val, ENT_QUOTES, $default_charset));
					if ($value != '' && strlen($temp_val) > $listview_max_textlength) {
						$value .= '&nbsp;<a href="javascript:;"><img onmouseout="getObj(\'content_'.$fieldName.'_'.$recordId.'\').hide();" onmouseover="getObj(\'content_'.$fieldName.'_'.$recordId.'\').show();" src="themes/softed/images/readmore.png" border="0"></a>';
						$value .= '<div id="content_'.$fieldName.'_'.$recordId.'" class="layerPopup" style="width:300px;z-index:10000001;display:none;position:absolute;" onmouseout="getObj(\'content_'.$fieldName.'_'.$recordId.'\').hide();" onmouseover="getObj(\'content_'.$fieldName.'_'.$recordId.'\').show();">
							         <table style="background-color:#F2F2F2;" align="center" border="0" cellpadding="5" cellspacing="0" width="100%">
							         <tr><td class="small">'.$temp_val.'</td></tr>
							         </table></div>';
					}
				//crmv@21092e	crmv@23734e
				}
				if ( in_array($uitype,array(71,72,7,9,90)) ) {
					$value = "<span align='right'>$value</span>";
				}
				//crmv@16312
				$parenttab = getParentTab();
				$nameFields = $this->queryGenerator->getModuleNameFields($module);
				$nameFieldList = explode(',',$nameFields);
				if(in_array($fieldName, $nameFieldList) && $module != 'Emails') {
					$value = "<a href='index.php?module=$module&parenttab=$parenttab&action=DetailView&record=".
					"$recordId' title='$module'>$value</a>";
				} elseif($fieldName == $focus->list_link_field && $module != 'Emails') {
					$value = "<a href='index.php?module=$module&parenttab=$parenttab&action=DetailView&record=".
					"$recordId' title='$module'>$value</a>";
				}
				//crmv@16312 end
				// vtlib customization: For listview javascript triggers
				$value = "$value <span type='vtlib_metainfo' vtrecordid='{$recordId}' vtfieldname=".
					"'{$fieldName}' vtmodule='$module' style='display:none;'></span>";
				// END
				$row[] = $value;
			}

			//crmv@7230 / crmv@10445
			if($used_status_field != "") {
				$excolor=getEntityColor($tabid,getEntityStatus($tabid,$module,$used_status_field,$recordId));
				$color = color_blend_by_opacity($excolor,50);
				$row['clv_color'] = $color;
			}
			//crmv@7230e / crmv@10445e
			$data[$recordId] = $row;

		}
		return $data;
	}

	public function getListViewHeader($focus, $module,$sort_qry='',$sorder='',$orderBy='',
			$skipActions=false) {
		global $log, $singlepane_view;
		global $theme;

		$arrow='';
		$qry = getURLstring($focus);
		$theme_path="themes/".$theme."/";
		$image_path=$theme_path."images/";
		$header = Array();

		//Get the vtiger_tabid of the module
		$tabid = getTabid($module);
		$tabname = getParentTab();
		global $current_user;

		require('user_privileges/user_privileges_'.$current_user->id.'.php');
		$fields = $this->queryGenerator->getFields();
		$whereFields = $this->queryGenerator->getWhereFields();
		$meta = $this->queryGenerator->getMeta($this->queryGenerator->getModule());

		$moduleFields = $meta->getModuleFields();
		$accessibleFieldList = array_keys($moduleFields);
		$listViewFields = array_intersect($fields, $accessibleFieldList);
		$listViewFields[count($listViewFields)]="cf_1078";

		//crmv@18744 crmv@23685
		//Added for Action - edit and delete link header in listview
		$change_indic = PerformancePrefs::getBoolean('LISTVIEW_RECORD_CHANGE_INDICATOR', true);
		if(!$skipActions && ($change_indic || isPermitted($module,"EditView","") == 'yes' || isPermitted($module,"Delete","") == 'yes')) {
			$header[] = getTranslatedString("LBL_ACTION", $module);
		}
		//crmv@18744e crmv@23685e

		//Added on 14-12-2005 to avoid if and else check for every list
		//vtiger_field for arrow image and change order
		$change_sorder = array('ASC'=>'DESC','DESC'=>'ASC');
		$arrow_gif = array('ASC'=>'arrow_down.gif','DESC'=>'arrow_up.gif');
		foreach($listViewFields as $fieldName) {
			$field = $moduleFields[$fieldName];
			if(in_array($field->getColumnName(),$focus->sortby_fields)) {
				if($orderBy == $field->getColumnName()) {
					$temp_sorder = $change_sorder[$sorder];
					$arrow = "&nbsp;<img src ='".vtiger_imageurl($arrow_gif[$sorder], $theme)."' border='0'>";
				} else {
					$temp_sorder = 'ASC';
				}
				$label = getTranslatedString($field->getFieldLabelKey(), $module);
				//added to display vtiger_currency symbol in listview header
				if($label =='Amount') {
					$label .=' ('.getTranslatedString('LBL_IN', $module).' '.
							$user_info['currency_symbol'].')';
				}
				if($module == 'Users' && $fieldName == 'User Name') {
					$name = "<a href='javascript:;' onClick='getListViewEntries_js(\"".$module.
						"\",\"parenttab=".$tabname."&order_by=".$field->getColumnName()."&sorder=".
						$temp_sorder.$sort_qry."\");' class='listFormHeaderLinks'>".
						getTranslatedString('LBL_LIST_USER_NAME_ROLE',$module)."".$arrow."</a>";
				} else {
					if($this->isHeaderSortingEnabled()) {
						//crmv@16312
						$name = "<a href='javascript:;' onClick='getListViewEntries_js(\"".$module.
							"\",\"parenttab=".$tabname."&foldername=Default&order_by=".$field->getColumnName()."&start=".
							$_SESSION["lvs"][$module]["start"]."&sorder=".$temp_sorder."".
						$sort_qry."\");' class='listFormHeaderLinks'>".$label."".$arrow."</a>";
						//crmv@16312 end
					} else {
						$name = $label;
					}
				}
				$arrow = '';
			} else {
				$name = getTranslatedString($field->getFieldLabelKey(), $module);
			}
			//added to display vtiger_currency symbol in related listview header
			if($name =='Amount') {
				$name .=' ('.getTranslatedString('LBL_IN').' '.$user_info['currency_symbol'].')';
			}

			$header[]=$name;
		}

		return $header;
	}
}
?>
