<?php
/*+*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ******************************************************************************/

class WebserviceField{
	private $fieldId;
	private $uitype;
	private $blockId;
	private $blockName;
	private $nullable;
	private $default;
	private $tableName;
	private $columnName;
	private $fieldName;
	private $fieldLabel;
	private $editable;
	private $fieldType;
	private $displayType;
	private $mandatory;
	private $massEditable;
	private $tabid;
	private $presence;
	private $sequence; // crmv@31780
	/**
	 *
	 * @var PearDatabase
	 */
	private $pearDB;
	private $typeOfData;
	private $fieldDataType;
	private $dataFromMeta;
	private static $tableMeta = array();
	private static $fieldTypeMapping = array();
	private $referenceList;
	private $defaultValuePresent;
	private $explicitDefaultValue;

	private $genericUIType = 10;

	private $readOnly = 0;

	private function __construct($adb,$row){
		$this->uitype = $row['uitype'];
		$this->blockId = $row['block'];
		$this->blockName = null;
		$this->tableName = $row['tablename'];
		$this->columnName = $row['columnname'];
		$this->fieldName = $row['fieldname'];
		$this->fieldLabel = $row['fieldlabel'];
		$this->displayType = $row['displaytype'];
		$this->massEditable = ($row['masseditable'] === '1')? true: false;
		$typeOfData = $row['typeofdata'];
		$this->presence = $row['presence'];
		$this->typeOfData = $typeOfData;
		$typeOfData = explode("~",$typeOfData);
		$this->mandatory = ($typeOfData[1] == 'M')? true: false;
		if($this->uitype == 4){
			$this->mandatory = false;
		}
		$this->fieldType = $typeOfData[0];
		$this->tabid = $row['tabid'];
		$this->fieldId = $row['fieldid'];
		$this->sequence = $row['sequence']; // crmv@31780
		$this->pearDB = $adb;
		$this->fieldDataType = null;
		$this->dataFromMeta = false;
		$this->defaultValuePresent = false;
		$this->referenceList = null;
		$this->explicitDefaultValue = false;

		$this->readOnly = (isset($row['readonly']))? $row['readonly'] : 0;

		if(isset($row['defaultvalue']) && $row['defaultvalue'] != '') {	//crmv@fix
			$this->setDefault($row['defaultvalue']);
		}
	}

	public static function fromQueryResult($adb,$result,$rowNumber){
		 return new WebserviceField($adb,$adb->query_result_rowdata($result,$rowNumber));
	}

	public static function fromArray($adb,$row){
		return new WebserviceField($adb,$row);
	}

	public function getTableName(){
		return $this->tableName;
	}

	public function getFieldName(){
		return $this->fieldName;
	}

	public function getFieldLabelKey(){
		return $this->fieldLabel;
	}

	public function getFieldType(){
		return $this->fieldType;
	}

	public function isMandatory(){
		return $this->mandatory;
	}

	public function getTypeOfData(){
		return $this->typeOfData;
	}

	public function getDisplayType(){
		return $this->displayType;
	}

	public function getMassEditable(){
		return $this->massEditable;
	}

	public function getFieldId(){
		return $this->fieldId;
	}

	// crmv@31780
	public function getSequence() {
		return $this->sequence;
	}
	// crmv@31780e

	public function getDefault(){
		if($this->dataFromMeta !== true && $this->explicitDefaultValue !== true){
			$this->fillColumnMeta();
		}
		return $this->default;
	}

	public function getColumnName(){
		return $this->columnName;
	}

	public function getBlockId(){
		return $this->blockId;
	}

	public function getBlockName(){
		if(empty($this->blockName)) {
			$this->blockName = getBlockName($this->blockId);
		}
		return $this->blockName;
	}

	public function getTabId(){
		return $this->tabid;
	}

	public function isNullable(){
		if($this->dataFromMeta !== true){
			$this->fillColumnMeta();
		}
		return $this->nullable;
	}

	public function hasDefault(){
		if($this->dataFromMeta !== true && $this->explicitDefaultValue !== true){
			$this->fillColumnMeta();
		}
		return $this->defaultValuePresent;
	}

	public function getUIType(){
		return $this->uitype;
	}

	public function isReadOnly() {
		if($this->readOnly == 99) return true;
		return false;
	}

	private function setNullable($nullable){
		$this->nullable = $nullable;
	}

	public function setDefault($value){
		$this->default = $value;
		$this->explicitDefaultValue = true;
		$this->defaultValuePresent = true;
	}

	public function setFieldDataType($dataType){
		$this->fieldDataType = $dataType;
	}

	public function setReferenceList($referenceList){
		$this->referenceList = $referenceList;
	}

	public function getTableFields(){
		$tableFields = null;
		if(isset(WebserviceField::$tableMeta[$this->getTableName()])){
			$tableFields = WebserviceField::$tableMeta[$this->getTableName()];
		}else{
			$dbMetaColumns = $this->pearDB->database->MetaColumns($this->getTableName());
			$tableFields = array();
			if(is_array($dbMetaColumns))//mycrmv@38850 
			foreach ($dbMetaColumns as $key => $dbField) {
				$tableFields[$dbField->name] = $dbField;
			}
			WebserviceField::$tableMeta[$this->getTableName()] = $tableFields;
		}
		return $tableFields;
	}
	public function fillColumnMeta(){
		$tableFields = $this->getTableFields();
		foreach ($tableFields as $fieldName => $dbField) {
			if(strcmp($fieldName,$this->getColumnName())===0){
				$this->setNullable(!$dbField->not_null);
				if($dbField->has_default === true && !$this->explicitDefaultValue){
					$this->defaultValuePresent = $dbField->has_default;
					$this->setDefault($dbField->default_value);
				}
			}
		}
		$this->dataFromMeta = true;
	}

	public function getFieldDataType(){
		if($this->fieldDataType === null){
			$fieldDataType = $this->getFieldTypeFromUIType();
			if($fieldDataType === null){
				$fieldDataType = $this->getFieldTypeFromTypeOfData();
			}
			//crmv@15893 fix datetime
			$tableFieldDataType = $this->getFieldTypeFromTable();
			if(($fieldDataType != 'date' && $fieldDataType != 'time') && ($tableFieldDataType == 'datetime' || $tableFieldDataType == 'timestamp')){	//crmv@21249
				$fieldDataType = 'datetime';
			}
			//crmv@15893 fix datetime end
			// crmv@31780
			if ($this->getUIType() == '55' && $this->getFieldName() == 'salutationtype') {
				$fieldDataType = 'picklist';
			}
			// crmv@31780e
			$this->fieldDataType = $fieldDataType;
		}
		return $this->fieldDataType;
	}

	public function getReferenceList(){
		global $table_prefix;
		static $referenceList = array();
		if($this->referenceList === null){
			if(isset($referenceList[$this->getFieldId()])){
				$this->referenceList = $referenceList[$this->getFieldId()];
				return $referenceList[$this->getFieldId()];
			}
			if(!isset(WebserviceField::$fieldTypeMapping[$this->getUIType()])){
				$this->getFieldTypeFromUIType();
			}
			$fieldTypeData = WebserviceField::$fieldTypeMapping[$this->getUIType()];
			$referenceTypes = array();
			if($this->getUIType() != $this->genericUIType){
				$sql = "select * from ".$table_prefix."_ws_referencetype where fieldtypeid=?";
				$params = array($fieldTypeData['fieldtypeid']);
			}else{
				$sql = 'select relmodule as type from '.$table_prefix.'_fieldmodulerel where fieldid=?';
				$params = array($this->getFieldId());
			}
			$result = $this->pearDB->pquery($sql,$params);
			$numRows = $this->pearDB->num_rows($result);
			for($i=0;$i<$numRows;++$i){
				array_push($referenceTypes,$this->pearDB->query_result($result,$i,"type"));
			}

			//to handle hardcoding done for Calendar module todo activities.
			//crmv@23515
			if(in_array($this->tabid,array(9,16)) && $this->fieldName =='parent_id'){
				$relatedto = getCalendarRelatedToModules();
				foreach($relatedto as $relatedto_module) {
					if (!in_array($relatedto_module,$referenceTypes)) {
						$referenceTypes[] = $relatedto_module;
					}
				}
			}
			//crmv@23515e
			//crmv@392267
			global $current_user;
			$types = vtws_listtypes(null, $current_user);
			$accessibleTypes = $types['types'];
			if(!is_admin($current_user)) {
				array_push($accessibleTypes, 'Users');
			}
			$referenceTypes = array_values(array_intersect($accessibleTypes,$referenceTypes));
			$referenceList[$this->getFieldId()] = $referenceTypes;
			$this->referenceList = $referenceTypes;
			return $referenceTypes;
		}
		return $this->referenceList;
	}
	//crmv@fix index column
	public function getIndexColumn($adb,$tableName){
		global $table_prefix;
		$sql = "select index_field from ".$table_prefix."_ws_entity_name where table_name = ?";
		$params = array($tableName);
		$res = $adb->pquery($sql,$params);
		if ($res)
			$index_field = $adb->query_result($res,0,'index_field');
		return 	$index_field;
	}
	//crmv@fix index column end
	private function getFieldTypeFromTable(){
		$tableFields = $this->getTableFields();
		foreach ($tableFields as $fieldName => $dbField) {
			if(strcmp($fieldName,$this->getColumnName())===0){
				return $dbField->type;
			}
		}
		//This should not be returned if entries in DB are correct.
		return null;
	}

	private function getFieldTypeFromTypeOfData(){
		switch($this->fieldType){
			case 'T': return "time";
			case 'D':
			case 'DT': return "date";
			case 'E': return "email";
			case 'N':
			case 'NN': return "double";
			case 'P': return "password";
			case 'I': return "integer";
			case 'V':
			default: return "string";
		}
	}

	private function getFieldTypeFromUIType(){
		global $table_prefix;
		// Cache all the information for futher re-use
		if(empty(self::$fieldTypeMapping)) {
			$result = $this->pearDB->pquery("select * from ".$table_prefix."_ws_fieldtype", array());
			while($resultrow = $this->pearDB->fetch_array($result)) {
				self::$fieldTypeMapping[$resultrow['uitype']] = $resultrow;
			}
		}

		if(isset(WebserviceField::$fieldTypeMapping[$this->getUIType()])){
			if(WebserviceField::$fieldTypeMapping[$this->getUIType()] === false){
				return null;
			}
			$row = WebserviceField::$fieldTypeMapping[$this->getUIType()];
			return $row['fieldtype'];
		} else {
			WebserviceField::$fieldTypeMapping[$this->getUIType()] = false;
			return null;
		}
	}

	function getPicklistDetails(){
		$hardCodedPickListNames = array("hdntaxtype","email_flag");
		$hardCodedPickListValues = array(
				"hdntaxtype"=>array(
					array("label"=>"Individual","value"=>"individual"),
					array("label"=>"Group","value"=>"group")
				),
				"email_flag" => array(
					array('label'=>'SAVED','value'=>'SAVED'),
					array('label'=>'SENT','value' => 'SENT'),
					array('label'=>'MAILSCANNER','value' => 'MAILSCANNER')
				)
			);
		if(in_array(strtolower($this->getFieldName()),$hardCodedPickListNames)){
			return $hardCodedPickListValues[strtolower($this->getFieldName())];
		}
		return $this->getPickListOptions($this->getFieldName());
	}

	function getPickListOptions(){
		$fieldName = $this->getFieldName();
		global $table_prefix;
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		$options = array();
		$sql = "select * from ".$table_prefix."_picklist where name=?";
		$result = $this->pearDB->pquery($sql,array($fieldName));
		$numRows = $this->pearDB->num_rows($result);
		if($numRows == 0){
			$sql = "select * from ".$table_prefix."_$fieldName";
			$result = $this->pearDB->pquery($sql,array());
			$numRows = $this->pearDB->num_rows($result);
			for($i=0;$i<$numRows;++$i){
				$elem = array();
				$picklistValue = $this->pearDB->query_result($result,$i,$fieldName);
				$picklistValue = decode_html($picklistValue);
				$moduleName = getTabModuleName($this->getTabId());
				if($moduleName == 'Events') $moduleName = 'Calendar';
				$elem["label"] = getTranslatedString($picklistValue,$moduleName);
				$elem["value"] = $picklistValue;
				array_push($options,$elem);
			}
		}else{
			$user = VTWS_PreserveGlobal::getGlobal('current_user');
			$details = getPickListValues($fieldName,$user->roleid);
			for($i=0;$i<sizeof($details);++$i){
				$elem = array();
				$picklistValue = decode_html($details[$i]);
				$moduleName = getTabModuleName($this->getTabId());
				if($moduleName == 'Events') $moduleName = 'Calendar';
				$elem["label"] = getTranslatedString($picklistValue,$moduleName);
				$elem["value"] = $picklistValue;
				array_push($options,$elem);
			}
		}
		return $options;
	}

	function getPresence() {
		return $this->presence;
	}

	//crmv@sdk-18508
	function getReadOnly() {
		return $this->readonly;
	}
	//crmv@sdk-18508e
}

?>
