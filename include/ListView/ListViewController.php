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


/**
 * Description of ListViewController
 *
 * @author MAK
 */
class ListViewController {
	/**
	 *
	 * @var QueryGenerator
	 */
	protected $queryGenerator;
	/**
	 *
	 * @var PearDatabase
	 */
	protected $db;
	protected $nameList;
	protected $typeList;
	protected $ownerNameList;
	protected $user;
	protected $picklistValueMap;
	protected $picklistRoleMap;
	protected $headerSortingEnabled;

	//crmv@28836
	public function getInstance($db, $user, $generator) {
		$modName = 'ListViewController';
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
	//crmv@28836e

	public function __construct($db, $user, $generator) {
		$this->queryGenerator = $generator;
		$this->db = $db;
		$this->user = $user;
		$this->nameList = array();
		$this->typeList = array();
		$this->ownerNameList = array();
		$this->picklistValueMap = array();
		$this->picklistRoleMap = array();
		$this->headerSortingEnabled = true;
	}

	public function isHeaderSortingEnabled() {
		return $this->headerSortingEnabled;
	}

	public function setHeaderSorting($enabled) {
		$this->headerSortingEnabled = $enabled;
	}

	public function setupAccessiblePicklistValueList($name) {
		$isRoleBased = vtws_isRoleBasedPicklist($name);
		$this->picklistRoleMap[$name] = $isRoleBased;
		if ($this->picklistRoleMap[$name]) {
			$this->picklistValueMap[$name] = getAssignedPicklistValues($name, $this->user->roleid, $this->db, '', '', false, false);	//crmv@27889
			//crmv@29102
			if($name == 'activitytype'){
				$this->picklistValueMap[$name]['Task'] = getTranslatedString('Task','Calendar');
			}
			//crmv@29102e
		}
	}

	public function fetchNameList($field, $result) {
		$referenceFieldInfoList = $this->queryGenerator->getReferenceFieldInfoList();
		$fieldName = $field->getFieldName();
		$rowCount = $this->db->num_rows($result);

		$idList = array();
		for ($i = 0; $i < $rowCount; $i++) {
			$id = $this->db->query_result($result, $i, $field->getColumnName());
			if (!isset($this->nameList[$fieldName][$id])) {
				$idList[$id] = $id;
			}
		}
		//crmv@fix empty array
		$idList = array_values(array_filter(array_keys($idList)));
		//crmv@fix empty array end
		if(count($idList) == 0) {
			return;
		}
		$moduleList = $referenceFieldInfoList[$fieldName];
		foreach ($moduleList as $module) {
			$meta = $this->queryGenerator->getMeta($module);
			if ($meta->isModuleEntity()) {
				if($module == 'Users') {
					$nameList = getOwnerNameList($idList);
				} else {
					//TODO handle multiple module names overriding each other.
					$nameList = getEntityName($module, $idList);
				}
			} else {
				$nameList = vtws_getActorEntityName($module, $idList);
			}
			//crmv@fix empty array
			if(count($nameList) == 0) {
				continue;
			}
			$entityTypeList = array_intersect(array_keys($nameList), $idList);
			foreach ($entityTypeList as $id) {
				$this->typeList[$id] = $module;
			}
			if(empty($this->nameList[$fieldName])) {
				$this->nameList[$fieldName] = array();
			}
			foreach ($entityTypeList as $id) {
				$this->typeList[$id] = $module;
				$this->nameList[$fieldName][$id] = $nameList[$id];
			}
		}
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
            	elseif ($module == 'Calendar' && !is_admin($current_user) && $ownerId != $current_user->id && $visibility == 'Private' && !in_array($fieldName,array('assigned_user_id','date_start','time_start','time_end','due_date','activitytype','visibility','duration_hours','duration_minutes'))) {
            		if ($fieldName == 'subject')
            			$value = getTranslatedString('Private Event','Calendar');
            		else
            			$value = "<font color='red'>".getTranslatedString('LBL_NOT_ACCESSIBLE')."</font>";
            	}
            	//crmv@17001e
				elseif($module == 'Documents' && $fieldName == 'filename') {
					$downloadtype = $db->query_result($result,$i,'filelocationtype');
					if($downloadtype == 'I') {
						$ext =substr($value, strrpos($value, ".") + 1);
						$ext = strtolower($ext);
						if($value != ''){
							if($ext == 'bin' || $ext == 'exe' || $ext == 'rpm') {
								$fileicon = "<img src='" . vtiger_imageurl('fExeBin.gif', $theme).
										"' hspace='3' align='absmiddle' border='0'>";
							} elseif($ext == 'jpg' || $ext == 'gif' || $ext == 'bmp') {
								$fileicon = "<img src='".vtiger_imageurl('fbImageFile.gif', $theme).
										"' hspace='3' align='absmiddle' border='0'>";
							} elseif($ext == 'txt' || $ext == 'doc' || $ext == 'xls') {
								$fileicon = "<img src='".vtiger_imageurl('fbTextFile.gif', $theme).
										"' hspace='3' align='absmiddle' border='0'>";
							} elseif($ext == 'zip' || $ext == 'gz' || $ext == 'rar') {
								$fileicon = "<img src='".vtiger_imageurl('fbZipFile.gif', $theme).
										"' hspace='3' align='absmiddle'	border='0'>";
							} else {
								$fileicon = "<img src='".vtiger_imageurl('fbUnknownFile.gif',$theme)
										. "' hspace='3' align='absmiddle' border='0'>";
							}
						}
					} elseif($downloadtype == 'E') {
						if(trim($value) != '' ) {
							$fileicon = "<img src='" . vtiger_imageurl('fbLink.gif', $theme) .
									"' alt='".getTranslatedString('LBL_EXTERNAL_LNK',$module).
									"' title='".getTranslatedString('LBL_EXTERNAL_LNK',$module).
									"' hspace='3' align='absmiddle' border='0'>";
						} else {
							$value = '--';
							$fileicon = '';
						}
					} else {
						$value = ' --';
						$fileicon = '';
					}

					$fileName = $db->query_result($result,$i,'filename');
					$downloadType = $db->query_result($result,$i,'filelocationtype');
					$status = $db->query_result($result,$i,'filestatus');
					$fileIdQuery = "select attachmentsid from ".$table_prefix."_seattachmentsrel where crmid=?";
					$fileIdRes = $db->pquery($fileIdQuery,array($recordId));
					$fileId = $db->query_result($fileIdRes,0,'attachmentsid');
					if($fileName != '' && $status == 1) {
						if($downloadType == 'I' ) {
							$value = "<a href='index.php?module=uploads&action=downloadfile&".
									"entityid=$recordId&fileid=$fileId' title='".
									getTranslatedString("LBL_DOWNLOAD_FILE",$module).
									"' onclick='javascript:dldCntIncrease($recordId);'>".$value.
									"</a>";
						} elseif($downloadType == 'E') {
							$value = "<a target='_blank' href='$fileName' onclick='javascript:".
									"dldCntIncrease($recordId);' title='".
									getTranslatedString("LBL_DOWNLOAD_FILE",$module)."'>".$value.
									"</a>";
						} else {
							$value = ' --';
						}
					}
					$value = $fileicon.$value;
				} elseif($module == 'Documents' && $fieldName == 'filesize') {
					$downloadType = $db->query_result($result,$i,'filelocationtype');
					if($downloadType == 'I') {
						$filesize = $value;
						if($filesize < 1024)
							$value=$filesize.' B';
						elseif($filesize > 1024 && $filesize < 1048576)
							$value=round($filesize/1024,2).' KB';
						else if($filesize > 1048576)
							$value=round($filesize/(1024*1024),2).' MB';
					} else {
						$value = ' --';
					}
				} elseif( $module == 'Documents' && $fieldName == 'filestatus') {
					if($value == 1)
						$value=getTranslatedString('yes',$module);
					elseif($value == 0)
						$value=getTranslatedString('no',$module);
					else
						$value='--';
				} elseif( $module == 'Documents' && $fieldName == 'filetype') {
					$downloadType = $db->query_result($result,$i,'filelocationtype');
					if($downloadType == 'E' || $downloadType != 'I') {
						$value = '--';
					}
				//crmv@sdk-18509
				} elseif(SDK::isUitype($field->getUIType())) {
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
				//crmv@sdk-18509 e
				} elseif ($field->getFieldDataType() == 'picklist') {
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
						//mycrmv@38946
						$value_trans = Array();
						if ($value != '') {
							
							$value1 = explode(',',$value);														
							for ($k = 0; $k < count($value1); $k++) {					
								$value_trim = trim($value1[$k]);
								$value_trans[] = getTranslatedString($value_trim);
							}
						
						$value = implode(',',$value_trans);
						}
						//mycrmv@38946e
					} else {
						$value = ($value != "") ? str_replace(' |##| ',', ',$value) : "";
						//mycrmv@38946
						$value_trans = Array();
						if ($value != '') {
							
							$value1 = explode(',',$value);														
							for ($k = 0; $k < count($value1); $k++) {					
								$value_trim = trim($value1[$k]);
								$value_trans[] = getTranslatedString($value_trim);
							}
						
						$value = implode(',',$value_trans);
							
						}
						//mycrmv@38946e
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

	public function getListViewEditLink($module,$recordId, $activityType='') {
		if($module == 'Emails')
	        return 'javascript:;" onclick="OpenCompose(\''.$recordId.'\',\'edit\');';
		if($module != 'Calendar') {
			$return_action = "index";
		} else {
			$return_action = 'ListView';
		}
		//Added to fix 4600
		$url = getBasic_Advance_SearchURL();
		$parent = getParentTab();
		//Appending view name while editing from ListView
		$link = "index.php?module=$module&action=EditView&record=$recordId&return_module=$module".
			"&return_action=$return_action&parenttab=$parent".$url."&return_viewname=".
			$_SESSION['lvs'][$module]["viewname"];

		if($module == 'Calendar') {
			if($activityType == 'Task') {
				$link .= '&activity_mode=Task';
			} else {
				$link .= '&activity_mode=Events';
			}
		}
		return $link;
	}

	public function getListViewDeleteLink($module,$recordId) {
		//crmv@16312
		$parenttab = getParentTab();
		$viewname = $_SESSION['lvs'][$module]['viewname'];
		//Added to fix 4600
		$url = getBasic_Advance_SearchURL();
		if($module == "Calendar")
			$return_action = "ListView";
		else
			$return_action = "index";
		//This is added to avoid the del link in Product related list for the following modules
		$link = "index.php?module=$module&action=Delete&record=$recordId".
			"&return_module=$module&return_action=$return_action".
			"&parenttab=$parenttab&return_viewname=".$viewname.$url;
		//crmv@16312 end
		// vtlib customization: override default delete link for custom modules
		$requestModule = vtlib_purify($_REQUEST['module']);
		$requestRecord = vtlib_purify($_REQUEST['record']);
		$requestAction = vtlib_purify($_REQUEST['action']);
		$parenttab = vtlib_purify($_REQUEST['parenttab']);
		$isCustomModule = vtlib_isCustomModule($requestModule);
		if($isCustomModule && !in_array($requestAction, Array('index','ListView'))) {
			$link = "index.php?module=$requestModule&action=updateRelations&parentid=$requestRecord";
			$link .= "&destination_module=$module&idlist=$entity_id&mode=delete&parenttab=$parenttab";
		}
		// END
		return $link;
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

	public function getBasicSearchFieldInfoList() {
		$fields = $this->queryGenerator->getFields();
		$whereFields = $this->queryGenerator->getWhereFields();
		$meta = $this->queryGenerator->getMeta($this->queryGenerator->getModule());

		$moduleFields = $meta->getModuleFields();
		$accessibleFieldList = array_keys($moduleFields);
		$listViewFields = array_intersect($fields, $accessibleFieldList);
		$basicSearchFieldInfoList = array();
		foreach ($listViewFields as $fieldName) {
			$field = $moduleFields[$fieldName];
			$basicSearchFieldInfoList[$fieldName] = getTranslatedString($field->getFieldLabelKey(),
					$this->queryGenerator->getModule());
		}
		return $basicSearchFieldInfoList;
	}
//crmv@17997
	public function getAdvancedSearchOptionString($old_mode=false) {
		$module = $this->queryGenerator->getModule();
		$meta = $this->queryGenerator->getMeta($module);

		$moduleFields = $meta->getModuleFields();
		$i =0;
		foreach ($moduleFields as $fieldName=>$field) {
			//crmv@32955
			if(!in_array($field->getPresence(), array('0','2'))){
				continue;
			}
			//crmv@32955e
			if($field->getFieldDataType() == 'reference' || $field->getFieldDataType() == 'owner') {
				$typeOfData = 'V';
			} else if($field->getFieldDataType() == 'boolean') {
				$typeOfData = 'C';
			} else {
				$typeOfData = $field->getTypeOfData();
				$typeOfData = explode("~",$typeOfData);
				$typeOfData = $typeOfData[0];
			}
			$label = getTranslatedString($field->getFieldLabelKey(), $module);
			if(empty($label)) {
				$label = $field->getFieldLabelKey();
			}
			if($label == "Start Date & Time") {
				$fieldlabel = "Start Date";
			}
			$selected = '';
			if($i++ == 0) {
				$selected = "selected";
			}
			//crmv@16312
			// place option in array for sorting later
			$blockName = getTranslatedString($field->getBlockName(), $module);
			if ($old_mode){
				$tableName = $field->getTableName();
				//crmv@31979
				$columnName = $field->getColumnName();
				$OPTION_SET[$blockName][$label] = "<option value=\'$tableName.$columnName::::$typeOfData\' $selected>$label</option>";
				//crmv@31979e
			}
			else
				$OPTION_SET[$blockName][$label] = "<option value=\'$fieldName::::$typeOfData\' $selected>$label</option>";
		}
		if (!is_array($OPTION_SET)) return '';	//crmv@18917

	   	// sort array on block label
	    ksort($OPTION_SET, SORT_STRING);

		foreach ($OPTION_SET as $key=>$value) {
	  		$shtml .= "<optgroup label='$key' class='select' style='border:none'>";
	   		// sort array on field labels
	   		ksort($value, SORT_STRING);
	  		$shtml .= implode('',$value);
	  	}

	    return $shtml;
	    //crmv@16312 end
	}
//crmv@17997 end
}
?>