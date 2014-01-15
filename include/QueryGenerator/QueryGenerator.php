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

require_once 'data/CRMEntity.php';
require_once 'modules/CustomView/CustomView.php';
require_once 'include/Webservices/Utils.php';
require_once 'include/Webservices/RelatedModuleMeta.php';

/**
 * Description of QueryGenerator
 *
 * @author MAK
 */
class QueryGenerator {
	protected $module;
	protected $customViewColumnList;
	protected $stdFilterList;
	protected $conditionals;
	protected $manyToManyRelatedModuleConditions;
	protected $groupType;
	protected $whereFields;
	/**
	 *
	 * @var VtigerCRMObjectMeta
	 */
	protected $meta;
	/**
	 *
	 * @var Users
	 */
	protected $user;
	protected $advFilterList;
	protected $fields;
	protected $referenceModuleMetaInfo;
	protected $moduleNameFields;
	protected $referenceFieldInfoList;
	protected $referenceFieldList;
	protected $ownerFields;
	protected $columns;
	protected $fromClause;
	protected $whereClause;
	protected $query;
	protected $groupInfo;
	protected $groupInfoTagL = '@#';	//crmv@23687
	protected $groupInfoTagR = '#@';	//crmv@23687
	protected $conditionInstanceCount;
	protected $conditionalWhere;
	public static $AND = 'AND';
	public static $OR = 'OR';
	protected $customViewFields;
	protected $tableNameAlias;	//crmv@31795
	protected $reportFilter;	//crmv@31775

	//crmv@27834
	public function getInstance($module, $user) {
		$modName = 'QueryGenerator';
		$sdkClass = SDK::getClass($modName);
	  	if (!empty($sdkClass)) {
	  		if (!class_exists($sdkClass['module'])) {
	  			checkFileAccess($sdkClass['src']);
	  			require_once($sdkClass['src']);
	  		}
	  		$modName = $sdkClass['module'];
	  	}
	  	$focus = new $modName($module, $user);
		return $focus;
	}
	//crmv@27834e

	public function __construct($module, $user) {
		$db = PearDatabase::getInstance();
		$this->module = $module;
		$this->customViewColumnList = null;
		$this->stdFilterList = null;
		$this->conditionals = array();
		$this->user = $user;
		$this->advFilterList = null;
		$this->fields = array();
		$this->referenceModuleMetaInfo = array();
		$this->moduleNameFields = array();
		$this->whereFields = array();
		$this->groupType = self::$AND;
		$this->meta = $this->getMeta($module);
		$this->moduleNameFields[$module] = $this->meta->getNameFields();
		$this->referenceFieldInfoList = $this->meta->getReferenceFieldDetails();
		$this->referenceFieldList = array_keys($this->referenceFieldInfoList);
		$this->ownerFields = $this->meta->getOwnerFields();
		$this->columns = null;
		$this->fromClause = null;
		$this->whereClause = null;
		$this->query = null;
		$this->conditionalWhere = null;
		$this->groupInfo = '';
		$this->manyToManyRelatedModuleConditions = array();
		$this->conditionInstanceCount = 0;
		$this->customViewFields = array();
		$this->reportFilter;	//crmv@31775
	}

	/**
	 *
	 * @param String:ModuleName $module
	 * @return EntityMeta
	 */
	public function getMeta($module) {
		$db = PearDatabase::getInstance();
		if (empty($this->referenceModuleMetaInfo[$module])) {
			$handler = vtws_getModuleHandlerFromName($module, $this->user);
			$meta = $handler->getMeta();
			$this->referenceModuleMetaInfo[$module] = $meta;
			if($module == 'Users') {
				$this->moduleNameFields[$module] = 'user_name';
			} else {
				$this->moduleNameFields[$module] = $meta->getNameFields();
			}
		}
		return $this->referenceModuleMetaInfo[$module];
	}

	public function reset() {
		$this->fromClause = null;
		$this->whereClause = null;
		$this->columns = null;
		$this->query = null;
	}

	public function setFields($fields) {
		$this->fields = $fields;
	}

	public function getCustomViewFields() {
		return $this->customViewFields;
	}

	public function getFields() {
		return $this->fields;
	}

	public function getWhereFields() {
		return $this->whereFields;
	}

	public function getOwnerFieldList() {
		return $this->ownerFields;
	}

	public function getModuleNameFields($module) {
		return $this->moduleNameFields[$module];
	}

	public function getReferenceFieldList() {
		return $this->referenceFieldList;
	}

	public function getReferenceFieldInfoList() {
		return $this->referenceFieldInfoList;
	}

	public function getModule () {
		return $this->module;
	}
	//crmv@module fields
	public function getModuleFields () {
		$return = $this->meta->getModuleFields();
		/*
		if($this->getModule() == 'Calendar'){
			$eventsMeta = $this->getMeta('Events');
			$moduleFieldsEvents = $eventsMeta->getModuleFields();
			foreach($moduleFieldsEvents as $fieldMetaName => $fieldMeta) {
				if (!in_array($fieldMetaName,array_keys($return))) {
					$return[$fieldMetaName] = $fieldMeta;
				}
			}
		}
		*/
		return $return;
	}
	//crmv@module fields end
	public function getConditionalWhere() {
		return $this->conditionalWhere;
	}

	public function getDefaultCustomViewQuery() {
		$customView = new CustomView($this->module);
		$viewId = $customView->getViewId($this->module);
		return $this->getCustomViewQueryById($viewId);
	}

	public function initForDefaultCustomView() {
		$customView = new CustomView($this->module);
		$viewId = $customView->getViewId($this->module);
		$this->initForCustomViewById($viewId);
	}

	public function initForCustomViewById($viewId) {
		global $table_prefix;
		$customView = new CustomView($this->module);
		$this->customViewColumnList = $customView->getColumnsListByCvid($viewId);
		if (!empty($this->customViewColumnList))
		foreach ($this->customViewColumnList as $customViewColumnInfo) {
			$details = explode(':', $customViewColumnInfo);
			if(empty($details[2]) && $details[1] == 'crmid' && $details[0] == $table_prefix.'_crmentity') {
				$name = 'id';
				$this->customViewFields[] = $name;
			} else {
				$this->fields[] = $details[2];
				$this->customViewFields[] = $details[2];
			}
		}

		if($this->module == 'Calendar' && !in_array('activitytype', $this->fields)) {
			$this->fields[] = 'activitytype';
		}

		if($this->module == 'Documents') {
			if(in_array('filename', $this->fields)) {
				if(!in_array('filelocationtype', $this->fields)) {
					$this->fields[] = 'filelocationtype';
				}
				if(!in_array('filestatus', $this->fields)) {
					$this->fields[] = 'filestatus';
				}
			}
		}
		$this->fields[] = 'id';

		$this->stdFilterList = $customView->getStdFilterByCvid($viewId);
		$this->advFilterList = $customView->getAdvFilterByCvid($viewId);
		
		$this->reportFilter = $customView->getReportFilter($viewId);	//crmv@31775

		if(is_array($this->stdFilterList)) {
			$value = array();
			if(!empty($this->stdFilterList['columnname'])) {
				//crmv@30702
				$name = explode(':',$this->stdFilterList['columnname']);
				$name = $name[2];
			    $moduleFields = $this->meta->getModuleFields();
			    $field = $moduleFields[$name];
			    if (is_object($field)) {
			    //crmv@30702e
					$this->startGroup('');
					$value[] = $this->fixDateTimeValue($name, $this->stdFilterList['startdate']);
					$value[] = $this->fixDateTimeValue($name, $this->stdFilterList['enddate'], false);
					//crmv@month_day patch
					if ($this->stdFilterList['only_month_and_day'] == 1)
						$this->addCondition($name, $value, 'BETWEEN_MONTHDAY');
					else
						$this->addCondition($name, $value, 'BETWEEN');
					//crmv@month_day patch end
			    }	//crmv@30702
			}
		}
		if($this->conditionInstanceCount <= 0 && is_array($this->advFilterList)) {
			$this->startGroup('');
		} elseif($this->conditionInstanceCount > 0 && is_array($this->advFilterList)) {
			$this->addConditionGlue(self::$AND);
		}
		//mycrmv@rotho fix campo non accessibile/cancellato ma presente nel filtro
		if(is_array($this->advFilterList)) {
			$moduleFieldList = $this->meta->getModuleFields(); //crmv@30118
			foreach ($this->advFilterList as $index=>$filter) {
				$name = explode(':',$filter['columnname']);
				if(empty($name[2]) && $name[1] == 'crmid' && $name[0] == $table_prefix.'_crmentity') {
					$name = $this->getSQLColumn('id');
				} else {
					$name = $name[2];
				}
				$field = $moduleFieldList[$name];
				if(empty($field)) {
					unset($this->advFilterList[$index]);
					// not accessible field.
				}
			}
			foreach ($this->advFilterList as $index=>$filter) {
				$name = explode(':',$filter['columnname']);
				if(empty($name[2]) && $name[1] == 'crmid' && $name[0] == $table_prefix.'_crmentity') {
					$name = $this->getSQLColumn('id');
				} else {
					$name = $name[2];
				}
				//mycrmv@37798 //mycrmv@39995
				if (($name == 'capoarea' || $name == 'agente_riferimento' || $name == 'agente_riferimento_rec') && $filter['comparator'] == 'e') {
					$filter['value'] = getUserName($filter['value']);
				}
				//mycrmv@37798e //mycrmv@39995e								
				$this->addCondition($name, decode_html($filter['value']), $filter['comparator']);
				//crmv@30118
				$field = $moduleFieldList[$name];
				if(empty($field)) {
					// not accessible field.
					continue;
				}
				//crmv@30118 e
				if(count($this->advFilterList) -1  > $index) {
					$this->addConditionGlue(self::$AND);
				}
			}
		}
		//mycrmv@rotho
		if($this->conditionInstanceCount > 0) {
			$this->endGroup();
		}
	}
	//crmv@17997
	public function getReverseTranslate($value,$operator,&$field=null){
		global $current_language;
		// crmv@31396
		if ($field && $field->getFieldDataType() == 'picklist') {
			$plistvalues = getAllPickListValues($field->getFieldName(), $this->module);
			if (is_array($plistvalues)) {
				foreach ($plistvalues as $val=>$trans) {
					// danzi.tn@20140115 comparazione valori tradotti di picklist
					// danzi.tn@20140115 if (stripos($trans, $value) !== false) {
					if (strcasecmp($trans, $value) == 0 ) {
						return $val;
					}
					// danzi.tn@20140115e
				}
			}
		}
		// crmv@31396e
		$lang_strings = return_module_language($current_language,$this->module);
		if (in_array($operator,Array('s','ew','c','k','bwt','ewt','cts','dcts'))){
			foreach ($lang_strings as $fieldlabel=>$trans_fieldlabel){
				if (!is_array($trans_fieldlabel) && stripos($trans_fieldlabel,$value)!==false && strpos($fieldlabel,'LBL_')===false){
					$value = $fieldlabel;
					break;
				}
			}
		}
		else{
			$mod_keys = array_keys(array_map('strtolower',$lang_strings), strtolower($value));
			foreach($mod_keys as $mod_idx=>$mod_key) {
				if (strpos($mod_key, 'LBL_') === false) {
					$value = $mod_key;
					break;
				}
			}
		}
		return $value;
	}
	//crmv@17997 end
	public function getCustomViewQueryById($viewId) {
		$this->initForCustomViewById($viewId);
		return $this->getQuery();
	}
	//crmv@modify getQuery+ Calendar
	public function getQuery($onlyfields = false) {
		if(empty($this->query)) {
			$conditionedReferenceFields = array();
			$allFields = array_merge($this->whereFields,$this->fields);
			foreach ($allFields as $fieldName) {
				if(in_array($fieldName,$this->referenceFieldList)) {
					$moduleList = $this->referenceFieldInfoList[$fieldName];
					foreach ($moduleList as $module) {
						if(empty($this->moduleNameFields[$module])) {
							$meta = $this->getMeta($module);
						}
					}
				} elseif(in_array($fieldName, $this->ownerFields )) {
					$meta = $this->getMeta('Users');
					$meta = $this->getMeta('Groups');
				}
			}
			$query = 'SELECT ';
			//crmv@392267
			$this->getSelectClauseColumnSQL();
			//crmv@18124
			if ($onlyfields)
				return explode(',',$this->columns);
			//crmv@18124 end
			$query .= $this->columns;
			//crmv@392267 e
			$query .= $this->getFromClause();
			$query .= $this->getWhereClause();
			$query = $this->meta->getEntitylistQueryNonAdminChange($query);
			$this->query = $query;
			return $query;
		} else {
			return $this->query;
		}
	}

	public function getSQLColumn($name,$onlyfields) {
		if ($name == 'id') {
			$baseTable = $this->meta->getEntityBaseTable();
			$moduleTableIndexList = $this->meta->getEntityTableIndexList();
			$baseTableIndex = $moduleTableIndexList[$baseTable];
			return $baseTable.'.'.$baseTableIndex;
		}

		$moduleFields = $this->getModuleFields();
		$field = $moduleFields[$name];
		$sql = '';
		//TODO optimization to eliminate one more lookup of name, incase the field refers to only
		//one module or is of type owner.
		$column = $field->getColumnName();
		if ($onlyfields){
			if ($column == 'crmid')
				$column.=" as parent_id";
		}
		return $field->getTableName().'.'.$column;
	}
	//crmv@modify getQuery+ Calendar end
	//crmv@392267
	public function getSelectClauseColumnSQL(){
		global $table_prefix;
		$columns = array();
		$moduleFields = $this->getModuleFields();
		$accessibleFieldList = array_keys($moduleFields);
		$accessibleFieldList[] = 'id';
		$this->fields = array_intersect($this->fields, $accessibleFieldList);
		foreach ($this->fields as $field) {
			$sql = $this->getSQLColumn($field,$onlyfields);
			if (!in_array($sql,$columns)){
				$columns[] = $sql;
			}
		}
		if ($this->meta->getEntityName() == 'Calendar' && !$onlyfields){
			if (!in_array($table_prefix.'_activity.activitytype',$columns))
				$columns[] = $table_prefix.'_activity.activitytype';
			//crmv@17986
			if (!in_array($table_prefix.'_activity.eventstatus',$columns))
				$columns[] = $table_prefix.'_activity.eventstatus';
			//crmv@17986 end
			if (!in_array($table_prefix.'_activity.time_start',$columns))
				$columns[] = $table_prefix.'_activity.time_start';
			// crmv@25610
			if (!in_array($table_prefix.'_activity.time_end',$columns))
				$columns[] = $table_prefix.'_activity.time_end';
			// crmv@25610e

		}
		//crmv@17001 : Private Permissions
		if ($this->meta->getEntityName() == 'Calendar' && !in_array($table_prefix.'_activity.visibility',$columns))
			$columns[] = $table_prefix.'_activity.visibility';
		//crmv@17001e
		//crmv@9433
		if (vtlib_isModuleActive('Conditionals')){
			include_once('modules/Conditionals/ConditionalsUI.php');
			$conditional_fields = getConditionalFields($this->module);
			if (!empty($conditional_fields)){
				foreach ($conditional_fields as $row){
					$field_add = $row['tablename'].".".$row['columnname'];
					if (!in_array($field_add,$columns) && !empty($moduleFields[$row['fieldname']]))
						$columns[] = $field_add;
				}
			}
		}
		//crmv@9433 end
		//crmv@sdk-18508
		$sdk_files = SDK::getViews($this->module,'list_related_query');
		if (!empty($sdk_files)) {
			foreach($sdk_files as $sdk_file) {
				include($sdk_file['src']);
			}
		}
		//crmv@sdk-18508e
		$this->columns = implode(',',$columns);
		return $this->columns;
	}
	//crmv@392267e
	public function getFromClause() {
		global $adb,$table_prefix,$current_user;
		if(!empty($this->query) || !empty($this->fromClause)) {
			return $this->fromClause;
		}
		$moduleFields = $this->getModuleFields();
		$tableList = array();
		$tableJoinMapping = array();
		$tableJoinCondition = array();
		//crmv@fix advanced query
		$instance = CRMEntity::getInstance($this->module);
		$fields = $this->whereFields;
		if ($instance->getListViewAdvSecurityParameter_check($this->module)){
			$arr = $instance->getListViewAdvSecurityParameter_fields($this->module);
			if (count($arr)>0){
				foreach ($arr as $data){
					$data_exploded = explode(":",$data);
					$fields[] = $data_exploded[2];
				}
			}

		}
		//crmv@18242
		if (!empty($_SESSION[$this->module.'_ORDER_BY'])){
			if ($this->module == 'Calendar' && $_SESSION[$this->module.'_ORDER_BY'] == 'crmid')
				$fields[] = 'parent_id';
			else {
				//crmv@21856
				$webservice_field = WebserviceField::fromQueryResult($adb,$adb->pquery('select * from '.$table_prefix.'_field where tabid = ? and columnname = ?',array(getTabid($this->module),$_SESSION[$this->module.'_ORDER_BY'])),0);
				$fields[] = $webservice_field->getFieldName();
				//crmv@21856e
			}
		}
		//crmv@18039
		if (vtlib_isModuleActive('Conditionals')){
			include_once('modules/Conditionals/ConditionalsUI.php');
			$conditional_fields = getConditionalFields($this->module);
			if (!empty($conditional_fields)){
				foreach ($conditional_fields as $row){
					$field_add = $row['fieldname'];
					if (!in_array($field_add,$fields))
						$fields[] = $field_add;
				}
			}
		}
		//crmv@18039 end
		foreach ($this->fields as $fieldName) {
			if ($fieldName == 'id') {
				continue;
			}

			$field = $moduleFields[$fieldName];
			$baseTable = $field->getTableName();
			$tableIndexList = $this->meta->getEntityTableIndexList();
			$baseTableIndex = $tableIndexList[$baseTable];
			if($field->getFieldDataType() == 'reference') {
				$moduleList = $this->referenceFieldInfoList[$fieldName];
				$tableJoinMapping[$field->getTableName()] = 'INNER JOIN';
				foreach($moduleList as $module) {
					if($module == 'Users') {
						$tableJoinCondition[$fieldName][$table_prefix.'_users'] = $field->getTableName().
								".".$field->getColumnName()." = ".$table_prefix."_users.id";
						$tableJoinCondition[$fieldName][$table_prefix.'_groups'] = $field->getTableName().
								".".$field->getColumnName()." = ".$table_prefix."_groups.groupid";
						$tableJoinMapping[$table_prefix.'_users'] = 'LEFT JOIN';
						$tableJoinMapping[$table_prefix.'_groups'] = 'LEFT JOIN';
					}
				}
			} elseif($field->getFieldDataType() == 'owner') {
				$tableList[$table_prefix.'_users'] = $table_prefix.'_users';
				$tableList[$table_prefix.'_groups'] = $table_prefix.'_groups';
				$tableJoinMapping[$table_prefix.'_users'] = 'LEFT JOIN';
				$tableJoinMapping[$table_prefix.'_groups'] = 'LEFT JOIN';
			}
			$tableList[$field->getTableName()] = $field->getTableName();
				$tableJoinMapping[$field->getTableName()] =
						$this->meta->getJoinClause($field->getTableName());
		}
		$baseTable = $this->meta->getEntityBaseTable();
		$moduleTableIndexList = $this->meta->getEntityTableIndexList();
		$baseTableIndex = $moduleTableIndexList[$baseTable];
		foreach ($fields as $fieldName) {
		//crmv@fix advanced query end
			if(empty($fieldName)) {
				continue;
			}
			$field = $moduleFields[$fieldName];
			if(empty($field)) {
				// not accessible field.
				continue;
			}
			$baseTable = $field->getTableName();
			if($field->getFieldDataType() == 'reference') {
				$moduleList = $this->referenceFieldInfoList[$fieldName];
				$tableJoinMapping[$field->getTableName()] = 'INNER JOIN';
				foreach($moduleList as $module) {
					$meta = $this->getMeta($module);
					$nameFields = $this->moduleNameFields[$module];
					$nameFieldList = explode(',',$nameFields);
					foreach ($nameFieldList as $index=>$column) {
						//crmv@24679
						if (!vtlib_isModuleActive($module)){
							continue;
						}
						//crmv@24679e
						//crmv@25084
						if(getTabid($module) != ''){
							$res = $adb->pquery('select * from '.$table_prefix.'_field where tabid=? and fieldname=?',array(getTabid($module),$column));
							if($res){
								$wbs = WebserviceField::fromQueryResult($adb,$res,0);
								$column = $wbs->getColumnName();
							}
						}
						//crmv@25084e
						// for non admin user users module is inaccessible.
						// so need hard code the tablename.
						if($module == 'Users') {
							$instance = CRMEntity::getInstance($module);
							$referenceTable = $instance->table_name;
							$tableIndexList = $instance->tab_name_index;
							$referenceTableIndex = $tableIndexList[$referenceTable];
						} else {
							$referenceField = $meta->getFieldByColumnName($column);
							//crmv@25900
							if(!$referenceField){
								continue;
							}
							//crmv@25900e
							$referenceTable = $referenceField->getTableName();
							$tableIndexList = $meta->getEntityTableIndexList();
							$referenceTableIndex = $tableIndexList[$referenceTable];
						}
						if(isset($moduleTableIndexList[$referenceTable])) {
							$this->tableNameAlias[$referenceTable][$fieldName] = "$referenceTable$fieldName";	//crmv@31795
							$referenceTableName = "$referenceTable $referenceTable$fieldName";
							$referenceTable = "$referenceTable$fieldName";
						} else {
							$referenceTableName = $referenceTable;
							$moduleTableIndexList[$referenceTable] = $referenceTableIndex;	//crmv@25530
						}
						//should always be left join for cases where we are checking for null
						//reference field values.
						$tableJoinMapping[$referenceTableName] = 'LEFT JOIN';
						$tableJoinCondition[$fieldName][$referenceTableName] = $baseTable.'.'.
							$field->getColumnName().' = '.$referenceTable.'.'.$referenceTableIndex;
					}
				}
			} elseif($field->getFieldDataType() == 'owner') {
				$tableList[$table_prefix.'_users'] = $table_prefix.'_users';
				$tableList[$table_prefix.'_groups'] = $table_prefix.'_groups';
				$tableJoinMapping[$table_prefix.'_users'] = 'LEFT JOIN';
				$tableJoinMapping[$table_prefix.'_groups'] = 'LEFT JOIN';
			} else {
				$tableList[$field->getTableName()] = $field->getTableName();
				$tableJoinMapping[$field->getTableName()] =
						$this->meta->getJoinClause($field->getTableName());
			}
		}

		$defaultTableList = $this->meta->getEntityDefaultTableList();
		//crmv@18242 crmv@31396
		if ($this->module == 'Calendar' && in_array('parent_id',$fields)){
			$caltab = $table_prefix.'_seactivityrel';
			$defaultTableList[] = $caltab;
			$tableList[$caltab] = $caltab;
			$tableJoinMapping[$caltab] = 'LEFT JOIN';
		}
		//crmv@18242e crmv@31396e
		foreach ($defaultTableList as $table) {
			if(!in_array($table, $tableList)) {
				$tableList[$table] = $table;
				$tableJoinMapping[$table] = 'INNER JOIN';
			}
		}
		$ownerFields = $this->meta->getOwnerFields();
		if (count($ownerFields) > 0) {
			$ownerField = $ownerFields[0];
		}
		$baseTable = $this->meta->getEntityBaseTable();
		$sql = " FROM $baseTable ";
		unset($tableList[$baseTable]);
		foreach ($defaultTableList as $tableName) {
			$sql .= " $tableJoinMapping[$tableName] $tableName ON $baseTable.".
					"$baseTableIndex = $tableName.$moduleTableIndexList[$tableName]";
			unset($tableList[$tableName]);
		}
		foreach ($tableList as $tableName) {
			if($tableName == $table_prefix.'_users') {
				$field = $moduleFields[$ownerField];
				$sql .= " $tableJoinMapping[$tableName] $tableName ON ".$field->getTableName().".".
					$field->getColumnName()." = $tableName.id";
			} elseif($tableName == $table_prefix.'_groups') {
				$field = $moduleFields[$ownerField];
				$sql .= " $tableJoinMapping[$tableName] $tableName ON ".$field->getTableName().".".
					$field->getColumnName()." = $tableName.groupid";
			} else {
				$sql .= " $tableJoinMapping[$tableName] $tableName ON $baseTable.".
					"$baseTableIndex = $tableName.$moduleTableIndexList[$tableName]";
			}
		}
		
		//crmv@31775
		if ($this->reportFilter) {
			$tableNameTmp = CustomView::getReportFilterTableName($this->reportFilter,$current_user->id);
			$sql .= " INNER JOIN $tableNameTmp ON $tableNameTmp.id = {$table_prefix}_crmentity.crmid";
		}
		//crmv@31775e

		// crmv@30014 - join aggiuntive
		$moduleInstance = CRMEntity::getInstance($this->module);
		if ($moduleInstance && method_exists($moduleInstance, 'getQueryExtraJoin')) {
			$extraJoin = $moduleInstance->getQueryExtraJoin();
			$sql .= " $extraJoin";
		}
		// crmv@30014e

		if( $this->meta->getTabName() == 'Documents') {
			$tableJoinCondition['folderid'] = array(
				$table_prefix.'_crmentityfolder'=>"$baseTable.folderid = ".$table_prefix."_crmentityfolder.folderid", // crmv@30967
			);
			$tableJoinMapping[$table_prefix.'_crmentityfolder'] = 'INNER JOIN'; // crmv@30967
		}
		//crmv@25530
		$alias = 2;
		foreach ($tableJoinCondition as $fieldName=>$conditionInfo) {
			foreach ($conditionInfo as $tableName=>$condition) {
				//mycrmv@28901
				if((!empty($tableList[$tableName]))||(($tableName == 'vtiger_users') && ($fieldName != $ownerField))) {
				//mycrmv@28901e
					$tableNameAlias = $tableName.$alias;
					$condition = str_replace($tableName, $tableNameAlias, $condition);
					$alias++;
				} else {
					$tableNameAlias = '';
				}
				$sql .= " $tableJoinMapping[$tableName] $tableName $tableNameAlias ON $condition";
			}
		}
		//crmv@25530e
		foreach ($this->manyToManyRelatedModuleConditions as $conditionInfo) {
			$relatedModuleMeta = RelatedModuleMeta::getInstance($this->meta->getTabName(),
					$conditionInfo['relatedModule']);
			$relationInfo = $relatedModuleMeta->getRelationMeta();
			$relatedModule = $this->meta->getTabName();
			$sql .= ' INNER JOIN '.$relationInfo['relationTable']." ON ".
			$relationInfo['relationTable'].".$relationInfo[$relatedModule]=".
				"$baseTable.$baseTableIndex";
		}
		$sql .= $this->meta->getEntityAccessControlQuery();
		$this->fromClause = $sql;
		return $sql;
	}
	//crmv@modify where
	public function getWhereClause() {
		global $adb,$table_prefix;
		if(!empty($this->query) || !empty($this->whereClause)) {
			return $this->whereClause;
		}
		$deletedQuery = $this->meta->getEntityDeletedQuery();
		$sql = '';
		if(!empty($deletedQuery)) {
			$sql .= " WHERE $deletedQuery";
		}
		if($this->conditionInstanceCount > 0) {
			$sql .= ' AND ';
		} elseif(empty($deletedQuery)) {
			$sql .= ' WHERE ';
		}

		$moduleFieldList = $this->getModuleFields();
		$baseTable = $this->meta->getEntityBaseTable();
		$moduleTableIndexList = $this->meta->getEntityTableIndexList();
		$baseTableIndex = $moduleTableIndexList[$baseTable];
		$groupSql = $this->groupInfo;
		$fieldSqlList = array();
		foreach ($this->conditionals as $index=>$conditionInfo) {
			$fieldName = $conditionInfo['name'];
			$field = $moduleFieldList[$fieldName];
			if(empty($field)) {
				continue;
			}
			$fieldSql = '(';
			$fieldGlue = '';
			if ($_REQUEST['action'] == 'UnifiedSearch' && $conditionInfo['name'] == 'input_points') {
				 $conditionInfo['operator'] = 'c';
				 }
			$valueSqlList = $this->getConditionValue($conditionInfo['value'],
				$conditionInfo['operator'], $field);
			if(!is_array($valueSqlList)) {
				$valueSqlList = array($valueSqlList);
			}			
			
				
			foreach ($valueSqlList as $valueSql) {
				if (in_array($fieldName, $this->referenceFieldList)) {
					$moduleList = $this->referenceFieldInfoList[$fieldName];
					foreach($moduleList as $module) {

						$nameFields = $this->moduleNameFields[$module];
						$nameFieldList = explode(',',$nameFields);
						$meta = $this->getMeta($module);
						$columnList = array();
						//crmv@27019
						if($module == 'DocumentFolders' && $fieldName == 'folderid'){
							$fieldSql .= "$fieldGlue ".$table_prefix."_crmentityfolder.foldername $valueSql"; //crmv@30967
							$fieldGlue = $this->getFieldGlue($conditionInfo['operator']);
						}
						//crmv@27019e
						//crmv@24679
						if (!vtlib_isModuleActive($module)){
							continue;
						}
						//crmv@24679e
						foreach ($nameFieldList as $column) {
							if($module == 'Users') {
								$instance = CRMEntity::getInstance($module);
								$referenceTable = $instance->table_name;
								if(count($this->ownerFields) > 0 ||
										$this->getModule() == 'Quotes') {
									//mycrmv@rotho
									$colonne = implode($this->customViewColumnList);									
									//if (strpos($colonne,'smcreatorid') > 0 || strpos($colonne,'smownerid') > 0) {
										$referenceTable .= '2';
									//}
									//mycrmv@rotho e
								}
							} else {
								//crmv@25084
								if(getTabid($module) != ''){
									$res = $adb->pquery('select * from '.$table_prefix.'_field where tabid=? and fieldname=?',array(getTabid($module),$column));
									if($res){
										$wbs = WebserviceField::fromQueryResult($adb,$res,0);
										$column = $wbs->getColumnName();
									}
								}
								//crmv@25084e
								$referenceField = $meta->getFieldByColumnName($column);
								//crmv@25900
								if(!$referenceField){
									continue;
								}
								//crmv@25900e
								$referenceTable = $referenceField->getTableName();
								//crmv@31795
								if (isset($this->tableNameAlias[$referenceTable][$fieldName])) {
									$referenceTable = $this->tableNameAlias[$referenceTable][$fieldName];
								}
								//crmv@31795e
							}
							if(isset($moduleTableIndexList[$referenceTable])) {
								$referenceTable = "$referenceTable$fieldName";
							}
							//crmv@36534
							$casttype = $this->getCastValue($field);
							if ($casttype !==false && ($field->getFieldName() != 'account_id') && ($field->getFieldName() != 'parent')){ //mycrmv@42881
								$columnList[] = "COALESCE($referenceTable.$column, cast('' as ".$casttype."))";	//crmv@26983
							}
							else{
								$columnList[] = "$referenceTable.$column";	//crmv@26983
							}
							//crmv@36534 e
						}
						$columnSql = implode("^_^' '^_^",$columnList); //mycrmv@rotho
						//crmv@23805
						$columnList = explode('^_^',$columnSql); //mycrmv@rotho
						//crmv@36534
						$columnSqlArr = Array();
						if (count($columnList) > 1){ //mycrmv@42882
							$cntlist = 0;
							foreach ($columnList as $columnlistchild){
								if ($cntlist > 0){
									$columnSqlArr[] = "' '";
								}
								$columnSqlArr[] = $columnlistchild;
								$cntlist++;
							}
							$columnSql = $adb->sql_concat($columnSqlArr);
						}
						else{
							$columnSql = $columnList[0];
						}
						//crmv@36534 e
						//crmv@23805e
						$fieldSql .= "$fieldGlue $columnSql $valueSql";
						//crmv@16241
						$fieldGlue = $this->getFieldGlue($conditionInfo['operator']);
						//crmv@16241 end
					}
				} elseif (in_array($fieldName, $this->ownerFields)) {
					if (in_array($conditionInfo['operator'], array('e', 'n')) && is_numeric($conditionInfo['value'])) {
						$fieldSql .= "$fieldGlue {$table_prefix}_users.id $valueSql or {$table_prefix}_groups.groupid $valueSql";
					} else {
						$fieldSql .= "$fieldGlue {$table_prefix}_users.user_name $valueSql or {$table_prefix}_groups.groupname $valueSql";
					}
				} else {
					if(($fieldName == 'birthday' || strtolower($conditionInfo['operator']) == 'between_monthday') && !$this->isRelativeSearchOperators(
							$conditionInfo['operator'])) {

						$fieldSql .= "$fieldGlue ".$adb->database->SQLDate('md',$field->getTableName().".".$field->getColumnName())." ".$valueSql;
					} else {
						//crmv@36534
						$casttype = $this->getCastValue($field);
						if ($casttype !==false){
							$fieldSql .= "$fieldGlue COALESCE(".$field->getTableName().'.'.$field->getColumnName().", cast('' as ".$casttype.")) ".$valueSql;	//crmv@26565
						}
						else{
							$fieldSql .= "$fieldGlue ".$field->getTableName().'.'.$field->getColumnName()." ".$valueSql;	//crmv@26565
						}
						//crmv@36534 e
					}
				}
				//crmv@16241
				$fieldGlue = $this->getFieldGlue($conditionInfo['operator']);
				//crmv@16241 end
			}
			$fieldSql .= ')';
			$fieldSqlList[$index] = $fieldSql;
		}
		foreach ($this->manyToManyRelatedModuleConditions as $index=>$conditionInfo) {
			$relatedModuleMeta = RelatedModuleMeta::getInstance($this->meta->getTabName(),
					$conditionInfo['relatedModule']);
			$relationInfo = $relatedModuleMeta->getRelationMeta();
			$relatedModule = $this->meta->getTabName();
			$fieldSql = "(".$relationInfo['relationTable'].'.'.
			$relationInfo[$conditionInfo['column']].$conditionInfo['SQLOperator'].
			$conditionInfo['value'].")";
			$fieldSqlList[$index] = $fieldSql;
		}
		$groupSql = $this->makeGroupSqlReplacements($fieldSqlList, $groupSql);
		if($this->conditionInstanceCount > 0) {
			$this->conditionalWhere = $groupSql;
			$sql .= $groupSql;
		}

		// crmv@30014 - condizioni aggiuntive
		$moduleInstance = CRMEntity::getInstance($this->module);
		if ($moduleInstance && method_exists($moduleInstance, 'getQueryExtraWhere')) {
			$sql .= " ".$moduleInstance->getQueryExtraWhere();
		}
		// crmv@30014e

		$this->whereClause = $sql;
		return $sql;
	}
	//crmv@modify where end
	/**
	 *
	 * @param mixed $value
	 * @param String $operator
	 * @param WebserviceField $field
	 */
	protected function getConditionValue($value, $operator, $field) {
		global $adb, $current_user; // crmv@25610
		$operator = strtolower($operator);
		$db = PearDatabase::getInstance();
		if(is_string($value)) {
			$valueArray = explode(',' , $value);
		} elseif(is_array($value)) {
			$valueArray = $value;
		}else{
			$valueArray = array($value);
		}
		//crmv@17997
		$type = $field->getFieldDataType();
		if ($type == 'picklistmultilanguage' && $value != ''){
			list($valueArray,$operator) = picklistMulti::get_search_values($field->getFieldName(),$valueArray,$operator);
		}
		elseif($type == 'picklist') {
			$values_to_add = Array();
			foreach ($valueArray as $val){
				$val_trans = self::getReverseTranslate($val,$operator,$field);
				if ($val_trans != $val)
					$valueArray[] = $val_trans;
			}
		}
		//crmv@17997 end
		$sql = array();
		//crmv@fix data
		if(strpos($operator,'between') !==false) {
			if($field->getFieldName() == 'birthday' || $operator == 'between_monthday') {
				$sql[] = "BETWEEN ".$db->quote(date('md',strtotime($valueArray[0])))." AND ".$db->quote(date('md',strtotime($valueArray[1])));
			} else {
				$sql[] = "BETWEEN ".$db->quote($valueArray[0])." AND ".
							$db->quote($valueArray[1]);
			}
			return $sql;
		}
		//crmv@fix data end
		foreach ($valueArray as $value) {
			if(!$this->isStringType($field->getFieldDataType())) {
				$value = trim($value);
			}
			if((strtolower(trim($value)) == 'null') ||
					(trim($value) == '' && !$this->isStringType($field->getFieldDataType())) &&
							($operator == 'e' || $operator == 'n')) {
				if($operator == 'e'){
					$sql[] = " = ''"; //crmv@33466
					continue;
				}
				$sql[] = " <> ''"; //crmv@33466
				continue;
			} elseif($field->getFieldDataType() == 'boolean') {
				$value = strtolower($value);
				if ($value == 'yes') {
					$value = 1;
				} elseif($value == 'no') {
					$value = 0;
				}
			} elseif($this->isDateType($field->getFieldDataType())) {
				if($field->getFieldDataType() == 'datetime') {
					$valueList = explode(' ',$value);
					$value = $valueList[0];
				}
				$value = getValidDBInsertDateValue($value);
				if($field->getFieldDataType() == 'datetime') {
					$value .=(' '.$valueList[1]);
					$value = adjustTimezone($value, -$current_user->timezonediff); // crmv@25610-timezone
				}
			}
			//crmv@fix data
			if($field->getFieldName() == 'birthday' && !$this->isRelativeSearchOperators(
					$operator)) {
				$value = $db->quote(date('md',strtotime($value)));
			} else {
				$value = $db->sql_escape_string($value);
			}
			//crmv@fix data end
			if(trim($value) == '' && ($operator == 's' || $operator == 'ew' || $operator == 'c')
					&& ($this->isStringType($field->getFieldDataType()) ||
					$field->getFieldDataType() == 'picklist' ||
					$field->getFieldDataType() == 'multipicklist' ||
					//crmv@picklistmultilanguage
					$field->getFieldDataType() == 'picklistmultilanguage')) {
					//crmv@picklistmultilanguage end
				$sql[] = "LIKE ''";
				continue;
			}

			if(trim($value) == '' && ($operator == 'k') &&
					$this->isStringType($field->getFieldDataType())) {
				$sql[] = "NOT LIKE ''";
				continue;
			}

			switch($operator) {
				case 'e': $sqlOperator = "=";
					break;
				case 'n': $sqlOperator = "<>";
					break;
				case 's': $sqlOperator = "LIKE";
					$value = "$value%";
					break;
				case 'ew': $sqlOperator = "LIKE";
					$value = "%$value";
					break;
				case 'c': $sqlOperator = "LIKE";
					$value = "%$value%";
					break;
				case 'k': $sqlOperator = "NOT LIKE";
					$value = "%$value%";
					break;
				case 'l': $sqlOperator = "<";
					break;
				case 'g': $sqlOperator = ">";
					break;
				case 'm': $sqlOperator = "<=";
					break;
				case 'h': $sqlOperator = ">=";
					break;
			}
			//crmv@25996
			if ($adb->isMssql() || $adb->isOracle()) {
				if ($field->getFieldDataType() == 'text' && $sqlOperator == '=') $sqlOperator = 'LIKE';
			}
			//crmv@25996e
			//crmv@31245
			if( (!$this->isNumericType($field->getFieldDataType()) &&
					($field->getFieldName() != 'birthday' || ($field->getFieldName() == 'birthday' && $this->isRelativeSearchOperators($operator)))
				) || !is_numeric($value)

			){
			// crmv@31245e
				$value = "'$value'";
			}
			$sql[] = "$sqlOperator $value";
		}
		return $sql;
	}

	protected function makeGroupSqlReplacements($fieldSqlList, $groupSql) {
		$pos = 0;
		foreach ($fieldSqlList as $index => $fieldSql) {
			$pos = strpos($groupSql, $this->groupInfoTagL.$index.$this->groupInfoTagR.'');	//crmv@23687
			if($pos !== false) {
				$beforeStr = substr($groupSql,0,$pos);
				$afterStr = substr($groupSql, $pos + strlen($index) + strlen($this->groupInfoTagL) + strlen($this->groupInfoTagR));	//crmv@23687
				$groupSql = $beforeStr.$fieldSql.$afterStr;
			}
		}
		$groupSql = str_replace('OR ()',' ',$groupSql);	//crmv@25266
		return $groupSql;
	}

	protected function isRelativeSearchOperators($operator) {
		$nonDaySearchOperators = array('l','g','m','h');
		return in_array($operator, $nonDaySearchOperators);
	}
	protected function isNumericType($type) {
		return ($type == 'integer' || $type == 'double');
	}

	protected function isStringType($type) {
		return ($type == 'string' || $type == 'text' || $type == 'email' || $type == 'picklist');
	}

	protected function isDateType($type) {
		return ($type == 'date' || $type == 'datetime');
	}

	protected function fixDateTimeValue($name, $value, $first = true) {
		$moduleFields = $this->getModuleFields();
		$field = $moduleFields[$name];
		if (is_object($field)) {	//crmv@27037
			$type = $field->getFieldDataType();
			if($type == 'datetime') {
				if(strrpos($value, ' ') === false) {
					if($first) {
						return $value.' 00:00:00';
					}else{
						return $value.' 23:59:59';
					}
				}
			}
		}	//crmv@27037
		return $value;
	}

	//crmv@30976
	public function addField($fieldname) {
		$this->fields[] = $fieldname;
	}
	//crmv@30976e

	public function addCondition($fieldname,$value,$operator,$glue= null,$newGroup = false,
			$newGroupType = null) {
		//crmv@15351 fix not acessible fields
		$moduleFieldList = $this->getModuleFields();
		$field = $moduleFieldList[$fieldname];
		if(empty($field)) {
			// not accessible field.
			return;
		}
		//crmv@15351 fix not acessible fields	end
		//crmv@17997
		if($this->module == 'Calendar' && ($fieldname == 'taskstatus' || $fieldname == 'eventstatus')){
			$this->startGroup('');
			$conditionNumber = $this->conditionInstanceCount++;
			$this->groupInfo .= $this->groupInfoTagL.$conditionNumber.$this->groupInfoTagR.' ';	//crmv@23687
			$this->whereFields[] = $fieldname;
			$this->reset();
			$this->conditionals[$conditionNumber] = $this->getConditionalArray($fieldname, $value, $operator);
			$this->addConditionGlue($this->getFieldGlue($operator));
			$conditionNumber = $this->conditionInstanceCount++;
			$this->groupInfo .= $this->groupInfoTagL.$conditionNumber.$this->groupInfoTagR.' ';	//crmv@23687
			$this->whereFields[] = $fieldname;
			$this->reset();
			$fieldname_add = ($fieldname=='taskstatus')?'eventstatus':'taskstatus';
			$this->conditionals[$conditionNumber] = $this->getConditionalArray($fieldname_add, $value, $operator);
			$this->endGroup();
		} else {
			$conditionNumber = $this->conditionInstanceCount++;
			$this->groupInfo .= $this->groupInfoTagL.$conditionNumber.$this->groupInfoTagR.' ';	//crmv@23687
			$this->whereFields[] = $fieldname;
			$this->reset();
			$this->conditionals[$conditionNumber] = $this->getConditionalArray($fieldname, $value, $operator);
		}
		//crmv@17997 end

	}

	public function addRelatedModuleCondition($relatedModule,$column, $value, $SQLOperator) {
		$conditionNumber = $this->conditionInstanceCount++;
		$this->groupInfo .= $this->groupInfoTagL.$conditionNumber.$this->groupInfoTagR.' ';	//crmv@23687
		$this->manyToManyRelatedModuleConditions[$conditionNumber] = array('relatedModule'=>
			$relatedModule,'column'=>$column,'value'=>$value,'SQLOperator'=>$SQLOperator);
	}

	protected function getConditionalArray($fieldname,$value,$operator) {
		return array('name'=>$fieldname,'value'=>$value,'operator'=>$operator);
	}

	protected function startGroup($groupType) {
		$this->groupInfo .= "$groupType (";
	}

	protected function endGroup() {
		$this->groupInfo .= ')';
	}

	public function addConditionGlue($glue) {
		$this->groupInfo .= "$glue ";
	}

	public function addUserSearchConditions($input) {
		global $log,$default_charset;
		if($input['searchtype']=='advance') {
			if(empty($input['search_cnt'])) {
				return ;
			}
			$noOfConditions = vtlib_purify($input['search_cnt']);
			if($input['matchtype'] == 'all') {
				$matchType = self::$AND;
			} else {
				$matchType = self::$OR;
			}
			if($this->conditionInstanceCount > 0) {
				$this->startGroup(self::$AND);
			} else {
				$this->startGroup('');
			}
			for($i=0; $i<$noOfConditions; $i++) {
				$fieldInfo = 'Fields'.$i;
				$condition = 'Condition'.$i;
				$value = 'Srch_value'.$i;

				list($fieldName,$typeOfData) = explode("::::",str_replace('\'','',
						stripslashes($input[$fieldInfo])));
				$moduleFields = $this->getModuleFields();
				$field = $moduleFields[$fieldName];
				if (!$field)
					continue;
				$type = $field->getFieldDataType();
				//crmv@23687
				if(($i-1) >= 0 && !empty($this->whereFields)) {
					$this->addConditionGlue($matchType);
				}
				//crmv@23687e
				$operator = str_replace('\'','',stripslashes($input[$condition]));
				$searchValue = $input[$value];
				$searchValue = function_exists(iconv) ? @iconv("UTF-8",$default_charset,
						$searchValue) : $searchValue;
				$this->addCondition($fieldName, $searchValue, $operator);
			}
			$this->endGroup();
		} elseif($input['type']=='dbrd') {
			if($this->conditionInstanceCount > 0) {
				$this->startGroup(self::$AND);
			} else {
				$this->startGroup('');
			}
			$allConditionsList = $this->getDashBoardConditionList();
			$conditionList = $allConditionsList['conditions'];
			$relatedConditionList = $allConditionsList['relatedConditions'];
			$noOfConditions = count($conditionList);
			$noOfRelatedConditions = count($relatedConditionList);
			foreach ($conditionList as $index=>$conditionInfo) {
				$this->addCondition($conditionInfo['fieldname'], $conditionInfo['value'],
						$conditionInfo['operator']);
				if($index < $noOfConditions - 1 || $noOfRelatedConditions > 0) {
					$this->addConditionGlue(self::$AND);
				}
			}
			foreach ($relatedConditionList as $index => $conditionInfo) {
				$this->addRelatedModuleCondition($conditionInfo['relatedModule'],
						$conditionInfo['conditionModule'], $conditionInfo['finalValue'],
						$conditionInfo['SQLOperator']);
				if($index < $noOfRelatedConditions - 1) {
					$this->addConditionGlue(self::$AND);
				}
			}
			$this->endGroup();
		} else {
			// crmv@31245 - ricerca base su tutti i campi della listview
			if(isset($input['search_fields']) && is_array($input['search_fields']) && count($input['search_fields']) > 0) {
				$fieldNames=vtlib_purify($input['search_fields']);
			} elseif (isset($input['search_field']) && $input['search_field'] != '') {
				$fieldNames = array(vtlib_purify($input['search_field']));
			} else {
				return ;
			}
			if($this->conditionInstanceCount > 0) {
				$this->startGroup(self::$AND);
			} else {
				$this->startGroup('');
			}

			if(isset($input['search_text']) && $input['search_text']!="") {
				// search other characters like "|, ?, ?" by jagi
				$value = $input['search_text'];
				$stringConvert = function_exists(iconv) ? @iconv("UTF-8",$default_charset,$value) : $value;
				if (!$this->isStringType($type)) {
					$value = trim($stringConvert);
				}
			}
			$moduleFields = $this->getModuleFields();
			$i = 0;
			foreach ($fieldNames as $fieldName => $fieldLabel) {
				$field = $moduleFields[$fieldName];
				if (!$field) continue;
				$type = $field->getFieldDataType();

				//crmv@23687
				if(($i-1) >= 0 && !empty($this->whereFields)) {
					$this->addConditionGlue(self::$OR);
				}
				//crmv@23687e

				if(!empty($input['operator'])) {
					$operator = $input['operator'];
				} elseif(trim(strtolower($value)) == 'null'){
					$operator = 'e';
				} else {
					$operator = 'c';
				}

				$this->addCondition($fieldName, $value, $operator);
				++$i;
			}
			// crmv@31245
			$this->endGroup();
		}
	}

	public function getDashBoardConditionList() {
		if(isset($_REQUEST['leadsource'])) {
			$leadSource = vtlib_purify($_REQUEST['leadsource']); // crmv@26907
		}
		if(isset($_REQUEST['date_closed'])) {
			$dateClosed = vtlib_purify($_REQUEST['date_closed']); // crmv@26907
		}
		if(isset($_REQUEST['sales_stage'])) {
			$salesStage = vtlib_purify($_REQUEST['sales_stage']); // crmv@26907
		}
		if(isset($_REQUEST['closingdate_start'])) {
			$dateClosedStart = vtlib_purify($_REQUEST['closingdate_start']); // crmv@26907
		}
		if(isset($_REQUEST['closingdate_end'])) {
			$dateClosedEnd = vtlib_purify($_REQUEST['closingdate_end']); // crmv@26907
		}
		if(isset($_REQUEST['owner'])) {
			$owner = vtlib_purify($_REQUEST['owner']);
		}
		if(isset($_REQUEST['campaignid'])) {
			$campaignId = vtlib_purify($_REQUEST['campaignid']);
		}
		if(isset($_REQUEST['quoteid'])) {
			$quoteId = vtlib_purify($_REQUEST['quoteid']);
		}
		if(isset($_REQUEST['invoiceid'])) {
			$invoiceId = vtlib_purify($_REQUEST['invoiceid']);
		}
		if(isset($_REQUEST['purchaseorderid'])) {
			$purchaseOrderId = vtlib_purify($_REQUEST['purchaseorderid']);
		}

		$conditionList = array();
		if(!empty($dateClosedStart) && !empty($dateClosedEnd)) {

			$conditionList[] = array('fieldname'=>'closingdate', 'value'=>$dateClosedStart,
				'operator'=>'h');
			$conditionList[] = array('fieldname'=>'closingdate', 'value'=>$dateClosedEnd,
				'operator'=>'m');
		}
		if(!empty($salesStage)) {
			if($salesStage == 'Other' || $salesStage == getTranslatedString('Other')) { //crmv@26774
				$conditionList[] = array('fieldname'=>'sales_stage', 'value'=>'Closed Won',
					'operator'=>'n');
				$conditionList[] = array('fieldname'=>'sales_stage', 'value'=>'Closed Lost',
					'operator'=>'n');
			} else {
				$conditionList[] = array('fieldname'=>'sales_stage', 'value'=> $salesStage,
					'operator'=>'e');
			}
		}
		if(!empty($leadSource)) {
			$conditionList[] = array('fieldname'=>'leadsource', 'value'=>$leadSource,
					'operator'=>'e');
		}
		if(!empty($dateClosed)) {
			$conditionList[] = array('fieldname'=>'closingdate', 'value'=>$dateClosed,
					'operator'=>'h');
		}
		if(!empty($owner)) {
			$conditionList[] = array('fieldname'=>'assigned_user_id', 'value'=>$owner,
					'operator'=>'e');
		}
		$relatedConditionList = array();
		if(!empty($campaignId)) {
			$relatedConditionList[] = array('relatedModule'=>'Campaigns','conditionModule'=>
				'Campaigns','finalValue'=>$campaignId, 'SQLOperator'=>'=');
		}
		if(!empty($quoteId)) {
			$relatedConditionList[] = array('relatedModule'=>'Quotes','conditionModule'=>
				'Quotes','finalValue'=>$quoteId, 'SQLOperator'=>'=');
		}
		if(!empty($invoiceId)) {
			$relatedConditionList[] = array('relatedModule'=>'Invoice','conditionModule'=>
				'Invoice','finalValue'=>$invoiceId, 'SQLOperator'=>'=');
		}
		if(!empty($purchaseOrderId)) {
			$relatedConditionList[] = array('relatedModule'=>'PurchaseOrder','conditionModule'=>
				'PurchaseOrder','finalValue'=>$purchaseOrderId, 'SQLOperator'=>'=');
		}
		return array('conditions'=>$conditionList,'relatedConditions'=>$relatedConditionList);
	}
	//crmv@16241
	protected function getFieldGlue($operator) {
		if (in_array($operator,Array('k','n')))
			return " ".self::$AND;
		return " ".self::$OR;

	}
	//crmv@16241 end
	//crmv@36534
	public static function getCastValue($field){
		global $adb;
		$type = $field->getFieldDataType();
		static $cachedTableFields = array();
		if(empty($cachedTableFields[$field->getTableName()])){
			$cachedTableFields[$field->getTableName()] = array_change_key_case($adb->database->MetaColumns($field->getTableName()),CASE_LOWER);
		}
		switch ($adb->datadict->MetaType($cachedTableFields[$field->getTableName()][$field->getColumnName()])){
			case 'I':
			case 'N':
				if ($field->getFieldType() == 'V'){
					$datatype = 'char';
				}
				else{
					if ($adb->isMySQL()){
						$datatype = 'unsigned';
					}
					else{
						$datatype = 'char'; //mycrmv@43017
					}
				}
				break;
			case 'D':
			case 'DT':
			case 'T':
				$datatype = 'date';
				break;
			case 'XL':
				$datatype = false; //do not cast clobs!
				break;
			default:
				$datatype = 'char';
				break;
		}
		/*
		$datatype = $adb->datadict->ActualType($datadict_type);
		if (strpos($datatype,"(")!==false){
			$datatype = explode(",",$datatype);
			$datatype = $datatype[0];
		}
		*/
		return $datatype;
	}
	//crmv@36534 e
}
?>