<?php
/***************************************************************************************
 * The contents of this file are subject to the CRMVILLAGE.BIZ VTECRM License Agreement
 * ("licenza.txt"); You may not use this file except in compliance with the License
 * The Original Code is:  CRMVILLAGE.BIZ VTECRM
 * The Initial Developer of the Original Code is CRMVILLAGE.BIZ.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
 ***************************************************************************************/

include_once('vtlib/Vtiger/Utils.php');
include_once('include/Zend/Json.php');
include_once('modules/SDK/LangUtils.php');

class SDK {

	var $sdk_session_keys = array('sdk_uitype', 'sdk_utils', 'sdk_popup_return_funct', 'sdk_smarty', 'sdk_presave', 'sdk_popup_query', 'sdk_adv_query', 'sdk_adv_permission', 'sdk_class_all', 'sdk_class', 'sdk_class_parent', 'sdk_view', 'sdk_file', 'sdk_home_iframe', 'sdk_reportfolders', 'sdk_reports', 'sdk_js_lang', 'sdk_transitions', 'vte_languages', 'sdk_dashboards','sdk_pdf_cfunctions');
	var $other_session_keys = array('installed_modules');

	/**
	* Invoked when special actions are performed on the module.
	* @param String Module name
	* @param String Event Type
	*/
 	function vtlib_handler($moduleName, $eventType) {

		require_once('include/utils/utils.php');
		global $adb,$table_prefix;

		if($eventType == 'module.postinstall') {

 			require_once('modules/SDK/InstallTables.php');

 			$adb->pquery('UPDATE '.$table_prefix.'_tab SET customized=0 WHERE name=?', array($moduleName));

 			$moduleInstance = Vtiger_Module::getInstance('SDK');
			Vtiger_Link::addLink($moduleInstance->id,'HEADERSCRIPT','SDKScript','modules/SDK/SDK.js');
			self::setUtil('modules/SDK/LangUtils.php');
			self::setUtil('modules/SDK/src/Utils.php');

			$langinfo = vtlib_getToggleLanguageInfo();
			$languages = array_keys($langinfo);
 			if (empty($languages)) {
				$languages = array('en_us','it_it');
			}
			foreach ($languages as $language){
				self::importPhpLanguage($language);
				//l'import della lingua js viene fatto in Header.tpl
			}

			$adb->pquery('DELETE FROM '.$table_prefix.'_profile2tab WHERE tabid = ?',array($moduleInstance->id));
			$adb->pquery('DELETE FROM '.$table_prefix.'_profile2standardperm WHERE tabid = ?',array($moduleInstance->id));
			$adb->pquery('DELETE FROM '.$table_prefix.'_profile2utility WHERE tabid = ?',array($moduleInstance->id));
			$adb->pquery('DELETE FROM '.$table_prefix.'_profile2field WHERE tabid = ?',array($moduleInstance->id));

			$moduleInstance->hide(array('hide_module_manager'=>1,'hide_profile'=>0,'hide_report'=>1));

			self::setUitype('201','modules/SDK/src/201/201.php','modules/SDK/src/201/201.tpl','modules/SDK/src/201/201.js');	//crmv@26523
			self::setUitype('202','modules/SDK/src/202/202.php','modules/SDK/src/202/202.tpl','modules/SDK/src/202/202.js');	//crmv@26809
			self::setUitype('203','modules/SDK/src/203/203.php','modules/SDK/src/203/203.tpl','modules/SDK/src/203/203.js');	//crmv@26809
			self::setPopupQuery('related', 'Webmails', 'Calendar', 'modules/SDK/src/modules/Webmails/CalendarQuery.php');		//crmv@26265

			self::setMenuButton('fixed','LBL_FAVORITES',"fnvshobj(this,'favorites');getFavoriteList();",'favorites.png');	//crmv@26986

    		//crmv@29079
    		self::setUitype(205,'modules/SDK/src/205/205.php','modules/SDK/src/205/205.tpl','');

    		//crmv@30014
    		self::setUitype(206, 'modules/SDK/src/206/206.php', 'modules/SDK/src/206/206.tpl', 'modules/SDK/src/206/206.js', 'integer');

    		$adb->pquery('insert into '.$table_prefix.'_home_iframe (hometype,url) values (?,?)',array('MODCOMMENTS','index.php?module=ModComments&action=ModCommentsAjax&file=ModCommentsWidgetHandler&ajax=true&widget=DetailViewBlockCommentWidget'));

    		$homeModule = Vtiger_Module::getInstance('SDK');
			$homeModule->addLink('HEADERSCRIPT', 'NotificationsScript', 'modules/SDK/src/Notifications/NotificationsCommon.js');
			$homeModule->addLink('HEADERCSS', 'NotificationsScript', 'modules/SDK/src/Notifications/NotificationsCommon.css');
			//crmv@29079e

			self::addView('Users', 'modules/SDK/src/modules/Users/UsersView.php', 'constrain', 'continue');	//crmv@29506

			$result = $adb->pquery("select * from {$table_prefix}_field where tabid = 13 and fieldname in (?,?)",array('projecttaskid','projectplanid'));
			if ($result && $adb->num_rows($result) == 2) {
				self::setPopupQuery('field', 'HelpDesk', 'projecttaskid', 'modules/SDK/src/modules/HelpDesk/ProjectTaskQuery.php', array('projectplanid'=>'getObj("projectplanid").value'));
			}

		} else if($event_type == 'module.disabled') {
			// TODO Handle actions when this module is disabled.
		} else if($event_type == 'module.enabled') {
			// TODO Handle actions when this module is enabled.
		} else if($eventType == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
		} else if($eventType == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} else if($eventType == 'module.postupdate') {
			// TODO Handle actions after this module is updated.
		}
 	}

	static function log($message, $delimit=true) {
		Vtiger_Utils::Log($message, $delimit);
	}

	function getSessionKeys($add_other_session_keys=false) {
		/*
		if (isset($this)) {
			if ($add_other_session_keys) {
				$tmp = $this->sdk_session_keys;
				foreach ($this->other_session_keys as $other_session_key) {
					$tmp[] = $other_session_key;
				}
				return $tmp;
			} else {
				return $this->sdk_session_keys;
			}
		} else {
		*/
			$focus = CRMEntity::getInstance('SDK');
			if ($add_other_session_keys) {
				$tmp = $focus->sdk_session_keys;
				foreach ($focus->other_session_keys as $other_session_key) {
					$tmp[] = $other_session_key;
				}
				return $tmp;
			} else {
				return $focus->sdk_session_keys;
			}
		//}
	}

	function clearSessionValue($key,$all_users=true) {
		global $table_prefix;
		$dependentSessions = array(
			'sdk_uitype' => array('sdk_js_uitype'),
			'sdk_js_lang' => array('sdk_check_js_lang'),
		);
		if (array_key_exists($key, $_SESSION)) {
			unset($_SESSION[$key]);
		}
		if (in_array($key,array_keys($dependentSessions))) {
			if (!empty($dependentSessions[$key])) {
				foreach($dependentSessions[$key] as $dependentSession) {
					unset($_SESSION[$dependentSession]);
				}
			}
		}
		global $PERFORMANCE_CONFIG;
		if ($PERFORMANCE_CONFIG['RELOAD_ALL_SDK_SESSION']) {
			global $adb, $current_user, $table_prefix;
			$columns = array_keys($adb->datadict->MetaColumns($table_prefix.'_users'));
			if (in_array(strtoupper('reload_session'),$columns)) {
				if ($all_users) {
					$adb->pquery('update '.$table_prefix.'_users set reload_session = ?',array(1));
				} else {
					$adb->pquery('update '.$table_prefix.'_users set reload_session = ? where id = ?',array(0,$current_user->id));
				}
			}
		}
	}

	function clearSessionValues($all_users=true) {
		$keys = self::getSessionKeys(true);
		foreach ($keys as $k) {
			self::clearSessionValue($k,$all_users);
		}
	}

 	function getUitypes() {
 		global $adb;
 		if (!isset($_SESSION['sdk_uitype'])) {
	 		$tmp = array();
	 		$result = $adb->query('select * from sdk_uitype');
	 		if ($result && $adb->num_rows($result)>0) {
	 			while($row=$adb->fetchByAssoc($result)) {
	 				$tmp[$row['uitype']] = array('src_php'=>$row['src_php'],'src_tpl'=>$row['src_tpl'],'src_js'=>$row['src_js']);
	 			}
	 		}
	 		$_SESSION['sdk_uitype'] = $tmp;
 		}
 		return $_SESSION['sdk_uitype'];
 	}

 	function isUitype($uitype) {
 		$uitypes = self::getUitypes();
 		if (in_array($uitype,array_keys($uitypes))) {
 			return true;
 		}
 		return false;
 	}

 	function getUitypeInfo($uitype) {
 		$uitypes = self::getUitypes();
 		return $uitypes[$uitype];
 	}

	function getUitypeFile($src,$mode,$uitype) {
 		global $sdk_mode;
 		$sdk_mode = $mode;
 		$info = self::getUitypeInfo($uitype);
		$checkFileAccess = $info['src_'.$src];
		if ($src == 'tpl') {
			$checkFileAccess = "Smarty/templates/$checkFileAccess";
		}
 		if ($info['src_'.$src] != '' && Vtiger_Utils::checkFileAccess($checkFileAccess,false)) {
 			return $info['src_'.$src];
 		}
 	}

	function getJsUitypes() {
 		global $adb;
 		if (!isset($_SESSION['sdk_js_uitype']) && $adb->table_exist('sdk_uitype')) {
	 		$tmp = array();
	 		$result = $adb->query("SELECT uitype,src_js FROM sdk_uitype WHERE src_js <> '' OR src_js IS NOT NULL");
	 		if ($result && $adb->num_rows($result)>0) {
	 			while($row=$adb->fetchByAssoc($result)) {
	 				$tmp[$row['uitype']] = $row['src_js'];
	 			}
	 		}
	 		$_SESSION['sdk_js_uitype'] = Zend_Json::encode($tmp);
 		}
 		return $_SESSION['sdk_js_uitype'];
 	}

 	/*
 	 * Register new Uitype
 	 * $uitype	: numeric value
 	 * $src_php	: path of the php file source code
 	 * $src_tpl	: path of the tpl file source code
 	 * $src_js	: path of the js file source code
 	 * $type	: webservice format (ex. text, boolean, datetime, reference, ...)
 	 * $params	: array width other params (ex. modules per reference field)
 	 */
 	function setUitype($uitype,$src_php,$src_tpl,$src_js,$type='',$params='') {
 		global $adb,$table_prefix;
 		$result = $adb->query('select * from sdk_uitype where uitype = '.$uitype);
 		if ($result && $adb->num_rows($result)>0) {
 			self::log("Adding SDK Uitype ($uitype) ... FAILED ($uitype already exists!)");
 			return;
 		}
 		$uitypeid = $adb->getUniqueID("sdk_uitype");
 		$params = array($uitypeid,$uitype,$src_php,$src_tpl,$src_js);
 		$adb->pquery('insert into sdk_uitype (uitypeid,uitype,src_php,src_tpl,src_js) values ('.generateQuestionMarks($params).')',array($params));
 		if ($type != '') {
 			$fieldtypeid = $adb->getUniqueId($table_prefix.'_ws_fieldtype');
			$result = $adb->pquery("insert into ".$table_prefix."_ws_fieldtype(fieldtypeid,uitype,fieldtype) values(?,?,?)",array($fieldtypeid,$uitype,$type));
			if ($type == 'reference') {
				//TODO : insert into vtiger_ws_referencetype
				self::log("<b>TODO</b> : insert into vtiger_ws_referencetype");
			}
 		}
 		self::log("Adding SDK Uitype ($uitype) ... DONE");
 		// put it in the current session
 		self::clearSessionValue('sdk_uitype');
 	}

 	/*
 	 * Unregister a uitype
 	 * $uitype : the uitype to be unregistered. its files won't be deleted
 	 * TODO: cancellare da tabelle vtiger_ws*
 	 */
 	function unsetUitype($uitype) {
 		global $adb,$table_prefix;
 		$res = $adb->pquery('delete from sdk_uitype where uitype = ?',array($uitype));
 		if ($res && $adb->getAffectedRowCount($res) > 0) {
 			$adb->pquery('delete from '.$table_prefix.'_ws_fieldtype where uitype = ?',array($uitype));
 			self::log("Deleting SDK Uitype ($uitype) ... DONE");
 			self::clearSessionValue('sdk_uitype');
 		} else {
 			self::log("Deleting SDK Uitype ($uitype) ... FAILED");
 		}
 	}

 	function getUtilsList() {
 		global $adb;
 		if (empty($adb->database) || !$adb->table_exist('sdk_utils') || !isModuleInstalled('SDK')) {
 			return;
 		}
 		if (!isset($_SESSION['sdk_utils'])) {
	 		$tmp = array();
	 		$result = $adb->query('select * from sdk_utils');
	 		if ($result && $adb->num_rows($result)>0) {
	 			while($row=$adb->fetchByAssoc($result)) {
	 				$tmp[$row['utilid']] = $row['src'];
	 			}
	 		}
	 		$_SESSION['sdk_utils'] = $tmp;
 		}
 		return $_SESSION['sdk_utils'];
 	}

 	function getUtils() {
 		$sdk_utils = self::getUtilsList();
 		if (!empty($sdk_utils)) {
	 		foreach ($sdk_utils as $sdk_util) {
		 		if ($sdk_util != '' && Vtiger_Utils::checkFileAccess($sdk_util,false)) {
		 			require_once($sdk_util);
		 		}
	 		}
 		}
 	}

 	/*
 	 * Register new Util
 	 * $src	: path of the php file source code
 	 * Note: there is no control if the same file is included twice
 	 */
	function setUtil($src) {
		global $adb;
		if ($src == '') {
			self::log("Adding SDK Util ($src) ... FAILED (src empty!)");
			return;
		}
		// check if it already exists
		$utils = self::getUtilsList();
		if (in_array($src, array_values($utils))) {
			self::log("Adding SDK Util ($src) ... FAILED (File already in utils list)");
			return;
		}
		$utilid = $adb->getUniqueID("sdk_utils");
		$params = array($utilid,$src);
		$adb->pquery('insert into sdk_utils (utilid,src) values ('.generateQuestionMarks($params).')',array($params));
		self::log("Adding SDK Util ($src) ... DONE");
		self::clearSessionValue('sdk_utils');
	}

 	/*
 	 * Delete a registered util
 	 * $src: path to the php file to be unregistered (the file itself won't be deleted)
 	 */
 	function unsetUtil($src) {
 		global $adb;
 		$res = $adb->pquery('delete from sdk_utils where src = ?',array($src));
 		if ($res && $adb->getAffectedRowCount($res) > 0) {
 			self::log("Deleting SDK Util ($src) ... DONE");
 			self::clearSessionValue('sdk_utils');
 		} else {
 			self::log("Deleting SDK Util ($src) ... FAILED");
 		}
 	}

	function getPopupReturnFunctions() {
 		global $adb;
 		if (!isset($_SESSION['sdk_popup_return_funct'])) {
	 		$tmp = array();
	 		$result = $adb->query('select * from sdk_popup_return_funct');
	 		if ($result && $adb->num_rows($result)>0) {
	 			while($row=$adb->fetchByAssoc($result)) {
	 				$tmp[$row['id']] = array('module'=>$row['module'],'fieldname'=>$row['fieldname'],'src'=>$row['src']);
	 			}
	 		}
	 		$_SESSION['sdk_popup_return_funct'] = $tmp;
 		}
 		return $_SESSION['sdk_popup_return_funct'];
 	}

 	function isPopupReturnFunction($module,$fieldname) {
 		if ($module != '' && $fieldname != '') {
	 		$popupReturnFunctions = self::getPopupReturnFunctions();
	 		foreach($popupReturnFunctions as $id => $info) {
				if ($module == $info['module'] && $fieldname == $info['fieldname']) {
					return true;
				}
	 		}
 		}
 		return false;
 	}

 	function getPopupReturnFunctionFile($module,$fieldname) {
 		$popupReturnFunctions = self::getPopupReturnFunctions();
 		foreach($popupReturnFunctions as $id => $info) {
			if ($module == $info['module'] && $fieldname == $info['fieldname']) {
				return $info['src'];
			}
 		}
 	}

 	function setPopupReturnFunction($module,$fieldname,$src) {
 		global $adb;
 		if ($module == '' || $fieldname == '' || $src == '') {
 			self::log("Adding SDK Popup Return Function ($module,$fieldname,$src) ... FAILED (empty value)");
 			return;
 		}
 		// check duplicates
 		$file = self::getPopupReturnFunctionFile($module, $fieldname);
 		if (isset($file) && !empty($file)) {
 			self::log("Adding SDK Popup Return Function ($src) ... FAILED (duplicate)");
 			return;
 		}
 		$id = $adb->getUniqueID("sdk_popup_return_funct");
 		$params = array($id,$module,$fieldname,$src);
 		$adb->pquery('insert into sdk_popup_return_funct (id,module,fieldname,src) values ('.generateQuestionMarks($params).')',array($params));
 		self::log("Adding SDK Popup Return Function ($module,$fieldname,$src) ... DONE");
 		self::clearSessionValue('sdk_popup_return_funct');
 	}

 	function unsetPopupReturnFunction($module, $fieldname = NULL, $src = NULL) {
		global $adb;

 		$query = 'delete from sdk_popup_return_funct where module = ?';
 		$qpar = array($module);
 		if (!empty($fieldname)) {
 			$query .= 'and fieldname = ?';
 			$qpar[] = $fieldname;
 		}
 		if (!empty($src)) {
 			$query .= 'and src = ?';
 			$qpar[] = $src;
 		}
 		$res = $adb->pquery($query, $qpar);
 		if ($res && $adb->getAffectedRowCount($res) > 0) {
 			self::log("Deleting SDK Popup Return Function ($src) ... DONE");
 			self::clearSessionValue('sdk_popup_return_funct');
 		} else {
 			self::log("Deleting SDK Popup Return Function ($src) ... FAILED");
 		}
 	}

 	function db2FileLanguages($module) {
	 	$langinfo = vtlib_getToggleLanguageInfo();
		foreach($langinfo as $prefix => $info) {
			$lang = get_lang_strings($module,$prefix);
			$data = "<?php\n\$mod_strings = array(\n";
			foreach($lang as $key => $value){
				$data .= "\t'".addcslashes(html_entity_decode($key, ENT_QUOTES), "'")."'=>'".addcslashes(html_entity_decode($value, ENT_QUOTES), "'")."',\n";
			}
			$data .= ");\n?>";
			$fp = fopen("modules/$module/language/$prefix.lang.php","wb");
			fwrite($fp,$data);
		}
 	}

 	function file2DbLanguages($module) {
 		$langinfo = vtlib_getToggleLanguageInfo();
		foreach($langinfo as $prefix => $info) {
			self::file2DbLanguage($module,$prefix);
		}
 	}

 	function file2DbLanguage($module,$language) {
 		unset($mod_strings);
		@include("modules/$module/language/$language.lang.php");
		if (isset($mod_strings)){
			insert_language($module,$language,$mod_strings);
		}
 	}

	/*
 	 * Deletes all the strings for the specified module or language (non entrambi i criteri -> ??? )
	 */
 	function deleteLanguage($module='',$language='') {
 		global $adb;
 		if ($module != '' && $language == '') {
 			$adb->pquery('DELETE FROM sdk_language WHERE module = ?',array($module));
 		} elseif ($module == '' && $language != '') {
 			$adb->pquery('DELETE FROM sdk_language WHERE language = ?',array($language));
 		}
 		self::clearSessionValue('sdk_js_lang');
 		self::clearSessionValue('vte_languages');
 	}

	/*
 	 * Updates (or create a new one if it doesn't exist) an entry in the language table
 	 */
	function setLanguageEntry($module, $langid, $label, $newlabel) {
 		global $adb;

 		// delete old row
 		self::deleteLanguageEntry($module, $langid, $label);

 		// insert new
 		$newid = $adb->getUniqueID("sdk_language");
 		//$qparam = array($newid, $module, $langid, correctEncoding(html_entity_decode($label)), correctEncoding(html_entity_decode($newlabel)));
		$qparam = array($newid, $module, $langid, $label, utf8_encode($newlabel));
 		$query = 'insert into sdk_language (languageid, module, language, label, trans_label) values ('.generateQuestionMarks($qparam).')';
 		$res = $adb->pquery($query, $qparam);
 		self::log("Adding SDK Language Entry ($module $langid $label) ... DONE");
 		self::clearSessionValue('sdk_js_lang');
 		self::clearSessionValue('vte_languages');
 	}

 	/*
 	 * Same as previous, but accepts multiple languages
 	 */
 	function setLanguageEntries($module, $label, $strings) {
 		foreach ($strings as $langid=>$newlabel) {
 			self::setLanguageEntry($module, $langid, $label, $newlabel);
 		}
 	}

 	/*
 	 * Deletes a string in the language table
 	 */
 	function deleteLanguageEntry($module, $langid = NULL, $label = NULL) {
 		global $adb;
 	 	$query = 'delete from sdk_language where module = ?';
 		$qpar = array($module);
 		if (!empty($langid)) {
 			$query .= ' and language = ?';
 			$qpar[] = $langid;
 		}
 		if (!empty($label)) {
 			$query .= ' and label like ?';
 			$qpar[] = $label;
 		}
 		$res = $adb->pquery($query, $qpar);
 		if ($res && $adb->getAffectedRowCount($res) > 0) {
 			self::log("Deleting SDK Language Entry ($module $langid) ... DONE");
 		} else {
 			self::log("Deleting SDK Language Entry ($module $langid) ... FAILED");
 		}
 		self::clearSessionValue('sdk_js_lang');
 		self::clearSessionValue('vte_languages');
 	}

 	function getModuleLanguageList() {
 		global $adb,$table_prefix;
 		$sql = "select name from ".$table_prefix."_tab where name <> ?";
		$res = $adb->pquery($sql,Array('Events'));
		while ($row = $adb->fetchByAssoc($res,-1,false)){
			$modules[] = $row[name];
		}
		$modules[] = 'Settings';
		$modules[] = 'CustomView';
		$modules[] = 'Administration';
		$modules[] = 'System';
		$modules[] = 'Picklistmulti';
		$modules[] = 'PickList';
		$modules[] = 'Import';
		$modules[] = 'Help';
		$modules[] = 'com_vtiger_workflow';
		$modules[] = 'Utilities';
		$modules[] = 'Yahoo';
		return $modules;
 	}

 	function importPhpLanguage($language) {
 		$modules = self::getModuleLanguageList();
 		foreach ($modules as $module){
 			self::file2DbLanguage($module,$language);
 		}
	 	unset($app_strings);
		unset($app_list_strings);
		unset($app_strings);
		@include("include/language/$language.lang.php");
		if (isset($app_strings)){
			insert_language('APP_STRINGS',$language,$app_strings);
		}
		if (isset($app_list_strings)){
			insert_language('APP_LIST_STRINGS',$language,$app_list_strings);
		}
		if (isset($app_currency_strings)){
			insert_language('APP_CURRENCY_STRINGS',$language,$app_currency_strings);
		}
 	}

 	function importJsLanguage($language) {
 		echo '<div style="display:none;"><iframe src="index.php?module=SDK&action=SDKAjax&file=InstallJsLang&language='.$language.'"></iframe></div>';
 	}

	function checkJsLanguage() {
 		global $adb, $current_language;
 		if (isModuleInstalled('SDK')) {
 			if ($_SESSION['sdk_check_js_lang'] == 'yes') {
	 			return;
	 		}
	 		$result = $adb->pquery("SELECT * FROM sdk_language WHERE module = ? and language = ?",array('ALERT_ARR',$current_language));
	 		if (!$result || $adb->num_rows($result) == 0) {
	 			require_once('modules/SDK/InstallJsLangs.php');
	 		}
	 		$_SESSION['sdk_check_js_lang'] = 'yes';
 		}
 	}

 	function loadJsLanguage() {
 		if (!isset($_SESSION['sdk_js_lang'])) {
 			global $current_language;
			$_SESSION['sdk_js_lang'] = Zend_Json::encode(get_lang_strings('ALERT_ARR',$current_language));
 		}
 		return $_SESSION['sdk_js_lang'];
 	}

 	function getSmartyTemplates() {
 		global $adb;
 		if (!isset($_SESSION['sdk_smarty'])) {
	 		$tmp = array();
	 		$result = $adb->query('select * from sdk_smarty');
	 		if ($result && $adb->num_rows($result)>0) {
	 			while($row=$adb->fetchByAssoc($result,-1,false)) {
	 				$tmp[$row['smartyid']] = array('params'=>$row['params'],'src'=>$row['src']);
	 			}
	 		}
	 		$_SESSION['sdk_smarty'] = $tmp;
 		}
 		return $_SESSION['sdk_smarty'];
 	}

 	function getSmartyTemplate($request) {
 		$smartyTemplates = self::getSmartyTemplates();
 		$src = array();
 		foreach($smartyTemplates as $smartyTemplate) {
 			$params = Zend_Json::decode($smartyTemplate['params']);
			// controllo se la request matcha con i parametri
			require_once('SDKParams.php');
 			if (SDKParams::paramsMatch($request, $params)) {
 				$src[] = array($smartyTemplate['src'], $params);
 			}
 		}
 		// choose best option (most specific/minimum)
 		if (!empty($src)) {
 			return SDKParams::paramsMin($src);
 		}
 		return '';
 	}

 	/*
 	 * Register a custom template
 	 * Check if the new template params are compatible with the existing ones
 	 */
 	function setSmartyTemplate($params,$src) {
 		global $adb;

 		// check parameters
 		require_once('SDKParams.php');
 		$plist = self::getSmartyTemplates();
 		foreach ($plist as $k=>$t) $plist[$k] = Zend_Json::decode($t['params']);

 		$compcheck = SDKParams::paramsValidate($plist, $params);

 		if (!empty($compcheck)) {
 			self::log("Adding SDK Smarty Template ($src) ... FAIL");
 			$failstr = '';
 			foreach ($compcheck as $v) {
 				$failstr .= ($v[0]==1)?'Duplicated ':'Incompatible ';
 				$failstr .= "params $v[1]\n";
 			}
 			self::log(nl2br($failstr));
 			return;
 		}

 		$smartyid = $adb->getUniqueID("sdk_smarty");
 		$params = array($smartyid,Zend_Json::encode($params),$src);
 		$adb->pquery('insert into sdk_smarty (smartyid,params,src) values ('.generateQuestionMarks($params).')',array($params));
 		self::log("Adding SDK Smarty Template ($src) ... DONE");
 		self::clearSessionValue('sdk_smarty');
 	}

 	/*
 	 * Delete a registered template
 	 */
 	function unsetSmartyTemplate($params, $src = NULL) {
 		global $adb;
 		$query = 'delete from sdk_smarty where params = ?';
 		$qpar = array(Zend_Json::encode($params));
 		if (!empty($src)) {
 			$query .= 'and src = ?';
 			$qpar[] = $src;
 		}
 		$res = $adb->pquery($query, $qpar);
 		if ($res && $adb->getAffectedRowCount($res) > 0) {
 			self::log("Deleting SDK Smarty Template ($src) ... DONE");
 			self::clearSessionValue('sdk_smarty');
 		} else {
 			self::log("Deleting SDK Smarty Template ($src) ... FAILED");
 		}
 	}

 	function getNotRewritableSmartyTemplates() {
 		$return = array(
 			'Header.tpl',
 			'modules/ModComments/widgets/DetailViewBlockComment.tpl',
 			'Buttons_List.tpl',
 			'Buttons_List1.tpl',
 			'Buttons_List4.tpl',
 			'Buttons_List_Detail.tpl',
 			'Buttons_List_Edit.tpl',
 			'loginheader.tpl',
 		);
 		return $return;
 	}

	function setPreSave($module, $src) {
 		global $adb;

 		// check if module already has a presave file
 		$presave = self::getPreSave($module);
 		if (isset($presave) && !empty($presave)) {
 			self::log("Adding SDK PreSave ($module) ... FAILED (PreSave already defined)");
 			return;
 		}

 		$presaveid = $adb->getUniqueID("sdk_presave");
 		$params = array($presaveid,$module,$src);
 		$adb->pquery('insert into sdk_presave (presaveid,module,src) values ('.generateQuestionMarks($params).')',array($params));
 		self::log("Adding SDK PreSave ($src) ... DONE");
 		self::clearSessionValue('sdk_presave');
 	}

 	function unsetPreSave($module, $src = NULL) {
 		global $adb;

 		$query = 'delete from sdk_presave where module = ?';
 		$qpar = array($module);
 		if (!empty($src)) {
 			$query .= 'and src = ?';
 			$qpar[] = $src;
 		}
 		$res = $adb->pquery($query, $qpar);
 		if ($res && $adb->getAffectedRowCount($res) > 0) {
 			self::log("Deleting SDK PreSave ($src) ... DONE");
 			self::clearSessionValue('sdk_presave');
 		} else {
 			self::log("Deleting SDK PreSave ($src) ... FAILED");
 		}
 	}

 	function getPreSaveList() {
 		global $adb;
 		if (!isset($_SESSION['sdk_presave']) && $adb->table_exist('sdk_presave')) {
	 		$tmp = array();
	 		$result = $adb->query('select * from sdk_presave');
	 		if ($result && $adb->num_rows($result)>0) {
	 			while($row=$adb->fetchByAssoc($result)) {
	 				$tmp[$row['presaveid']] = array('module'=>$row['module'],'src'=>$row['src']);
	 			}
	 		}
	 		$_SESSION['sdk_presave'] = $tmp;
 		}
 		return $_SESSION['sdk_presave'];
 	}

	function getPreSave($module) {
		$preSave = self::getPreSaveList();
 		foreach($preSave as $id => $info) {
			if ($module == $info['module']) {
				return $info['src'];
			}
 		}
 	}

	function getPopupQueries($type) {
 		global $adb;
 		if (!isset($_SESSION['sdk_popup_query']) || !isset($_SESSION['sdk_popup_query'][$type])) {
	 		$tmp = array();
	 		$result = $adb->pquery('select * from sdk_popup_query where type = ?',array($type));
	 		if ($result && $adb->num_rows($result)>0) {
	 			while($row=$adb->fetchByAssoc($result)) {
	 				$tmp[$row['id']] = array('module'=>$row['module'],'param'=>$row['param'],'src'=>$row['src'],'hidden_rel_fields'=>$row['hidden_rel_fields']);	//crmv@26920
	 			}
	 		}
 			$_SESSION['sdk_popup_query'][$type] = $tmp;
 		}
 		return $_SESSION['sdk_popup_query'][$type];
 	}

	function getJSPreSaveLis() {
		$tmp = self::getPreSaveList();
		if (!empty($tmp)) {
 			return Zend_Json::encode($tmp);
		} else {
			return '';
		}
 	}

 	/*
 	 * $type: field/related -> popup open by field / popup open by field
 	 * $module : module from which popup is open
 	 * $param : if $type is field $param is the fieldname else il the destination module
 	 */
 	function getPopupQuery($type,$module,$param) {
 		$popupQueries = self::getPopupQueries($type);
 		if (!empty($popupQueries)) {
	 		foreach($popupQueries as $info) {
	 			if ($module == $info['module'] && $param == $info['param']) {
	 				return $info['src'];
	 			}
	 		}
 		}
 	}

 	//crmv@26920
	function getPopupHiddenElements($module,$param,$only_fields=false){
 		require_once('include/Zend/Json.php');
 		$popupQueries = self::getPopupQueries('field');
 		if (!empty($popupQueries)) {
 			foreach($popupQueries as $info) {
 				if ($module == $info['module'] && $param == $info['param'] && $info['hidden_rel_fields'] != '') {
 					if (empty($hidden_fields)) {
 						$hidden_fields = Zend_Json::decode(html_entity_decode($info['hidden_rel_fields']));
 					}
					if($only_fields === true){
						return array_keys($hidden_fields);
					}
					if($only_fields == 'autocomplete'){
						return html_entity_decode($info['hidden_rel_fields']);
					}
					$js_string = '';
					$index = 0;
					if (!empty($hidden_fields)) {
	 					foreach($hidden_fields as $field =>$value ){
	 						$js_string .= '&'.$field.'="+'.str_replace("\\","",$value);
	 						if($index < sizeof($hidden_fields)-1){
	 							$js_string .= '+"';
	 						}
	 						$index++;
	 					}
					}
 					if($index > 0){
 						$js_string .= '+"';
 					}
 					return $js_string;
 				}
 			}
 		}
 	}
 	//crmv@26920e

	function setPopupQuery($type, $module, $param, $src, $hidden_rel_fields='') {	//crmv@26920
 		global $adb;
 		// check duplicates
 		$file = self::getPopupQuery($type, $module, $param);
 		if (isset($file) && !empty($file)) {
 			self::log("Adding SDK Popup Query ($src) ... FAILED (duplicate)");
 			return;
 		}
 		$popupid = $adb->getUniqueID("sdk_popup_query");
 		//crmv@26920
 		$columns = 'id,type,module,param,src';
 		$params = array($popupid,$type,$module,$param,$src);
 		if ($hidden_rel_fields != '') {
 			$columns .= ',hidden_rel_fields';
 			$params[] =  Zend_Json::encode($hidden_rel_fields);
 		}
 		$adb->pquery('insert into sdk_popup_query ('.$columns.') values ('.generateQuestionMarks($params).')',array($params));
 		//crmv@26920e
 		self::log("Adding SDK Popup Query ($src) ... DONE");
 		self::clearSessionValue('sdk_popup_query');
 	}

 	function unsetPopupQuery($type, $module, $param, $src) {
 		global $adb;

 		$query = 'delete from sdk_popup_query where type = ? and module = ? and param = ? and src = ?';
 		$qpar = array($type, $module, $param, $src);

 		$res = $adb->pquery($query, $qpar);
 		if ($res && $adb->getAffectedRowCount($res) > 0) {
 			self::log("Deleting SDK Popup Query ($src) ... DONE");
 			self::clearSessionValue('sdk_popup_query');
 		} else {
 			self::log("Deleting SDK Popup Query ($src) ... FAILED");
 		}
 	}

 	function getAdvancedQueries() {
 		global $adb;
 		if (!isset($_SESSION['sdk_adv_query'])) {
	 		$tmp = array();
	 		$result = $adb->query('select * from sdk_adv_query');
	 		if ($result && $adb->num_rows($result)>0) {
	 			while($row=$adb->fetchByAssoc($result)) {
	 				$tmp[$row['module']] = array('src'=>$row['src'],'function'=>$row['function']);
	 			}
	 		}
	 		$_SESSION['sdk_adv_query'] = $tmp;
 		}
 		return $_SESSION['sdk_adv_query'];
 	}

 	function getAdvancedQuery($module) {
 		$filter = '';
 		$advancedQuery = self::getAdvancedQueries();
 		if ($advancedQuery[$module] != '') {
 			$src = $advancedQuery[$module]['src'];
 			if ($src != '' && Vtiger_Utils::checkFileAccess($src,false)) {
 				require_once($src);
 				$filter = $advancedQuery[$module]['function']($module);
 			}
 		}
		return $filter;
 	}

	function setAdvancedQuery($module, $func, $src) {
 		global $adb;
 		$qs = self::getAdvancedQueries();
 		if (array_key_exists($module, $qs)) {
 			self::log("Adding SDK Advanced Query ($module) ... FAILED");
 			return;
 		}
 		$adqueryid = $adb->getUniqueID("sdk_adv_query");
 		$params = array($adqueryid,$module,$func, $src);
 		$column = array('id','module','function','src');
 		$adb->format_columns($column);
 		$adb->pquery('insert into sdk_adv_query ('.implode(',',$column).') values ('.generateQuestionMarks($params).')',array($params));
 		self::log("Adding SDK Advanced Query ($module) ... DONE");
 		self::clearSessionValue('sdk_adv_query');
 	}

 	function unsetAdvancedQuery($module) {
 		global $adb;

 		$query = 'delete from sdk_adv_query where module = ?';
 		$qpar = array($module);

 		$res = $adb->pquery($query, $qpar);
 		if ($res && $adb->getAffectedRowCount($res) > 0) {
 			self::log("Deleting SDK Advanced Query ($module) ... DONE");
 			self::clearSessionValue('sdk_adv_query');
 		} else {
 			self::log("Deleting SDK Advanced Query ($module) ... FAILED");
 		}
 	}

	function getAdvancedPermissions() {
 		global $adb;
 		if (!isset($_SESSION['sdk_adv_permission'])) {
	 		$tmp = array();
	 		$result = $adb->query('select * from sdk_adv_permission');
	 		if ($result && $adb->num_rows($result)>0) {
	 			while($row=$adb->fetchByAssoc($result)) {
	 				$tmp[$row['module']] = array('src'=>$row['src'],'function'=>$row['function']);
	 			}
	 		}
	 		$_SESSION['sdk_adv_permission'] = $tmp;
 		}
 		return $_SESSION['sdk_adv_permission'];
 	}

	function getAdvancedPermissionFunction($module) {
 		$advancedPermission = self::getAdvancedPermissions();
 		if ($advancedPermission[$module] != '') {
 			$src = $advancedPermission[$module]['src'];
 			if ($src != '' && Vtiger_Utils::checkFileAccess($src,false)) {
 				require_once($src);
 				return $advancedPermission[$module]['function'];
 			}
 		}
 	}

	function setAdvancedPermissionFunction($module, $func, $src) {
 		global $adb;
 		$qs = self::getAdvancedPermissions();
 		if (array_key_exists($module, $qs)) {
 			self::log("Adding SDK Advanced Permission Function ($module) ... FAILED");
 			return;
 		}
 		$adqueryid = $adb->getUniqueID("sdk_adv_permission");
 		$params = array($adqueryid,$module,$func,$src);
 		$column = array('id','module','function','src');
 		$adb->format_columns($column);
 		$res = $adb->pquery('insert into sdk_adv_permission ('.implode(',',$column).') values ('.generateQuestionMarks($params).')',array($params));
 		if ($res && $adb->getAffectedRowCount($res) > 0) {
 			self::log("Adding SDK Advanced Permission Function ($module) ... DONE");
 			self::clearSessionValue('sdk_adv_permission');
 		} else {
 			self::log("Adding SDK Advanced Permission Function ($module) ... FAILED");
 		}
 	}

 	function unsetAdvancedPermissionFunction($module) {
 		global $adb;

 		$query = 'delete from sdk_adv_permission where module = ?';
 		$qpar = array($module);

 		$res = $adb->pquery($query, $qpar);
 		if ($res && $adb->getAffectedRowCount($res) > 0) {
 			self::log("Deleting SDK Advanced Permission Function ($module) ... DONE");
 			self::clearSessionValue('sdk_adv_permission');
 		} else {
 			self::log("Deleting SDK Advanced Permission Function ($module) ... FAILED");
 		}
 	}

 	function getClasses($all='') {
 		global $adb;
 		if (empty($adb->database) || !isModuleInstalled('SDK')) {
 			return;
 		}
 		if (!isset($_SESSION['sdk_class'.$all])) {
	 		$tmp = array();
	 		$result = $adb->query('select * from sdk_class');
	 		if ($result && $adb->num_rows($result)>0) {
	 			while($row=$adb->fetchByAssoc($result)) {
	 				if ($all == '_all') {
	 					$tmp[$row['extends']] = array('module'=>$row['module'],'src'=>$row['src']);
	 				} elseif ($all == '_parent') {
	 					$tmp[$row['module']] = self::getParentModule($row['extends']);
	 				} else {
	 					$module = self::getSonModule($row['module']);
	 					$result1 = $adb->pquery('select * from sdk_class where module = ?',array($module));
	 					if ($result1 && $adb->num_rows($result1)>0) {
	 						$tmp[$row['extends']] = array('module'=>$module,'src'=>$adb->query_result($result1,0,'src'));
	 					}
	 				}
	 			}
	 		}
	 		$_SESSION['sdk_class'.$all] = $tmp;
 		}
 		return $_SESSION['sdk_class'.$all];
 	}

	function getSonModule($extends) {
 		$classes = self::getClasses('_all');
 		if ($classes[$extends] != '') {
 			return self::getSonModule($classes[$extends]['module']);
 		} else {
 			return $extends;
 		}
 	}

	function getDirectSonModule($extends) {
 		$classes = self::getClasses('_all');
 		if ($classes[$extends] != '') {
 			return $classes[$extends]['module'];
 		} else {
 			return '';
 		}
 	}

 	function getParentModule($module) {
		global $adb;
		$result = $adb->pquery('select extends from sdk_class where module = ?',array($module));
		if ($result && $adb->num_rows($result)>0) {
			$extends = $adb->query_result($result,0,'extends');
			$return = self::getParentModule($extends);
			if ($return != '') {
				$module = $return;
	 		}
		}
		return $module;
 	}

 	function getClass($extends) {
 		$classes_all = self::getClasses('_all');
 		$classes = self::getClasses();
 		if ($classes[$extends] != '') {
 			return $classes[$extends];
 		}
 	}

	/*
 	 * Extends the class $extends with the class $module (which is in $src)
 	 * Some classes are not allowed to be extended and it's not permitted to
 	 * derive a class more than once
 	 */
 	function setClass($extends, $module, $src) {
 	 	global $adb;
 	 	// check for blacklisted classes
 	 	$badclasses = array('Conditionals', 'Rss', 'vtigerRSS', 'Reports'); //crmv@31357+31355
 	 	if (in_array($extends, $badclasses)) {
 			self::log("Adding SDK Class ($module) ... FAILED (Class is blacklisted)");
 			return;
 	 	}

 	 	// check if class has already been extended
 		$classes = self::getClasses('_all');
 		if (in_array($extends, array_keys($classes))) {
 			self::log("Adding SDK Class ($module) ... FAILED (Class already extended)");
 			return;
 		}

 		// update the database
 		$classid = $adb->getUniqueID("sdk_class");
 		$params = array($classid,$extends,$module,$src);
 		$res = $adb->pquery('insert into sdk_class (id,extends,module,src) values ('.generateQuestionMarks($params).')',array($params));
 		if ($res && $adb->getAffectedRowCount($res) > 0) {
 			self::log("Adding SDK Class ($module) ... DONE");
 			self::clearSessionValue('sdk_class_all');
 			self::clearSessionValue('sdk_class_parent');
			self::clearSessionValue('sdk_class');
 		} else {
 			self::log("Adding SDK Class ($module) ... FAILED");
 		}
 	}

 	/*
 	 * Notes: deletes also all derived classes
 	 */
 	function unsetClass($extends) {
		global $adb;

		// create array with all the sons
		$deletelist = array();
		$ds = $extends;
		while (($ds = self::getDirectSonModule($ds)) != '')
			$deletelist[] = $ds;
		array_pop($deletelist);
		$deletelist = array_reverse($deletelist);
		$deletelist[] = $extends;

		// do the deletion
		// TODO: join all the queries in a combined one to speed up things
		foreach ($deletelist as $ext) {
	 		$query = 'delete from sdk_class where extends = ?';
	 		$qpar = array($ext);
 			$res = $adb->pquery($query, $qpar);
 			if ($res && $adb->getAffectedRowCount($res) > 0) {
	 			self::log("Deleting SDK Class ($ext) ... DONE");
 				self::clearSessionValue('sdk_class_all');
 				self::clearSessionValue('sdk_class_parent');
				self::clearSessionValue('sdk_class');
	 		} else {
 				self::log("Deleting SDK Class ($ext) ... FAILED");
 			}
		}
 	}

 	function getViews($module,$mode) {
 		global $adb, $sdk_mode;
 		$sdk_mode = $mode;
 		if (!isset($_SESSION['sdk_view']) || !isset($_SESSION['sdk_view'][$module])) {
	 		$tmp = array();
	 		$result = $adb->pquery('select * from sdk_view where module = ? order by sequence',array($module));
	 		if ($result && $adb->num_rows($result)>0) {
	 			while($row=$adb->fetchByAssoc($result)) {
	 				$tmp[$row['sequence']] = array('src'=>$row['src'],'mode'=>$row['mode'],'on_success'=>$row['on_success']);
	 			}
	 		}
	 		$_SESSION['sdk_view'][$module] = $tmp;
 		}
 		return $_SESSION['sdk_view'][$module];
 	}

 	function checkReadonly($readonly_old,$readonly,$mode) {
 		if ($mode == 'restrict') {
 			$readonly = max($readonly,$readonly_old);
 		} elseif ($mode == 'constrain') {
			//do nothing
 		}
 	}

 	/*
 	 * Retrieves the last sequence number for the specified module
 	 */
 	private	function getLastViewSequence($module) {
 		global $adb;

 		$res = $adb->pquery('select max(sequence) from sdk_view where module = ?', array($module));
 		if ($res && $adb->num_rows($res) > 0) {
 			$row = $adb->fetch_array($res);
 			return intval($row[0]);
 		} else {
 			return 0;
 		}
 	}

 	/*
 	 * Adds a new View at the end of the list (for that module)
 	 */
	function addView($module, $src, $mode, $success) {
 		global $adb;

 		$valid_modes = array('default'=>'restrict', 'constrain');
 		$valid_success = array('default'=>'continue', 'stop');

 		if (!in_array($mode, $valid_modes)) $mode = $valid_modes['default'];
 		if (!in_array($success, $valid_success)) $success = $valid_success['default'];

 		// check duplicates
 		$query = 'select module from sdk_view where module = ? and src = ?';
 		$qparam = array($module, $src);
 		$res = $adb->pquery($query, $qparam);
 		if ($res && $adb->num_rows($res) > 0) {
 			self::log("Adding SDK View ($module - $src) ... FAILED (duplicate)");
 			return;
 		}

 		$seq = self::getLastViewSequence($module) + 1;
 		$viewid = $adb->getUniqueID("sdk_view");

 		$qparam = array($viewid, $module, $src, $seq, $mode, $success);

 		$column=array("viewid","module","src","sequence","mode","on_success");
 		$adb->format_columns($column);
 		$query = 'insert into sdk_view ('.implode(',',$column).') values ('.generateQuestionMarks($qparam).')';

 		$res = $adb->pquery($query, $qparam);
 		if ($res && $adb->getAffectedRowCount($res) > 0) {
 			self::log("Adding SDK View ($module - $src) ... DONE");
 			self::clearSessionValue('sdk_view');
 		} else {
 			self::log("Adding SDK View ($module - $src) ... FAILED");
 		}
 	}

 	/*
 	 * Deletes a view
 	 */
 	function deleteView($module, $src) {
 		global $adb;

 		$query = 'delete from sdk_view where module = ? and src = ?';
 		$qparam = array($module, $src);

 		$res = $adb->pquery($query, $qparam);
 		if ($res && $adb->getAffectedRowCount($res) > 0) {
 			self::log("Deleting SDK View ($module - $src) ... DONE");
 			self::clearSessionValue('sdk_view');
 		} else {
 			self::log("Deleting SDK View ($module - $src) ... FAILED");
 		}
 	}

 	/*
 	 * Returns an array of files/dirs associated with the module
 	 */
	function getExtraSrc($module) {
 		global $adb;
 		$ret = array();

 		$query = 'select src from sdk_extra_src where module = ?';
 		$qparam = array($module);
 		$res = $adb->pquery($query, $qparam);
 		if ($res && $adb->num_rows($res) > 0) {
 			while ($row = $adb->fetch_array_no_html($res)) {
 				$ret[] = $row[0];
 			}
 		}
 		return $ret;
 	}

 	/*
 	 * Adds a file/dir association
 	 */
 	function setExtraSrc($module, $src) {
 		global $adb;

 		// check duplicates
 		$query = 'select id from sdk_extra_src where module = ? and src = ?';
 		$qparam = array($module, $src);
 		$res = $adb->pquery($query, $qparam);
 		if ($res && $adb->num_rows($res) > 0) {
 			self::log("Adding SDK Extra Src ($module - $src) ... FAILED (duplicate)");
 			return;
 		}

 		$srcid = $adb->getUniqueID("sdk_extra_src");
 		$qparam = array($srcid, $module, $src);
 		$query = 'insert into sdk_extra_src (id, module, src) values ('.generateQuestionMarks($qparam).')';
 		$res = $adb->pquery($query, $qparam);
 		if ($res && $adb->getAffectedRowCount($res) > 0) {
 			self::log("Adding SDK Extra Src ($module - $src) ... DONE");
 		} else {
 			self::log("Adding SDK Extra Src ($module - $src) ... FAILED");
 		}
 	}

 	/*
 	 * Deletes a file/dir association
 	 */
 	function unsetExtraSrc($module, $src) {
 		global $adb;

 		$query = 'delete from sdk_extra_src where module = ? and src = ?';
 		$qparam = array($module, $src);

 		$res = $adb->pquery($query, $qparam);
 		if ($res && $adb->getAffectedRowCount($res) > 0) {
 			self::log("Deleting SDK Extra Src ($module - $src) ... DONE");
 		} else {
 			self::log("Deleting SDK Extra Src ($module - $src) ... FAILED");
 		}
 	}

 	function getFiles($module) {
 		global $adb;
 		if (!isset($_SESSION['sdk_file']) || !isset($_SESSION['sdk_file'][$module])) {
	 		$result = $adb->pquery('select * from sdk_file where module = ?',array($module));
	 		if ($result && $adb->num_rows($result)>0) {
	 			while($row=$adb->fetchByAssoc($result)) {
	 				$_SESSION['sdk_file'][$module][$row['file']] = $row['new_file'];
	 			}
	 		}
 		}
 		return $_SESSION['sdk_file'][$module];
 	}

 	function getFile($module,$file) {
 		$files = self::getFiles($module);
 		return $files[$file];
 	}

	function setFile($module,$file,$new_file) {
		global $adb;
		$not_permitted_modules = array('Home','Calendar','Events');
		if ($module == '' || $file == '' || $new_file == '') {
			self::log("Adding SDK File ($new_file) ... FAILED (module, file or new_file empty!)");
			return;
		}
		if (self::getFile($module,$file) != '') {
			self::log("Adding SDK File ($new_file) ... FAILED (new_file already registered for module $module and file $file)");
			return;
		}
		$fileid = $adb->getUniqueID("sdk_file");
		$params = array($fileid,$module,$file,$new_file);
		$column = array('fileid','module','file','new_file');
		$adb->format_columns($column);
		$adb->pquery('insert into sdk_file ([fileid],[module],[file],[new_file])  values ('.generateQuestionMarks($params).')',array($params));		
		self::log("Adding SDK File ($new_file) ... DONE");
		self::clearSessionValue('sdk_file');
	}

	function unsetFile($module,$file) {
		global $adb;
		$column = 'file';
		$adb->format_columns($column);
		$res = $adb->pquery('delete from sdk_file where module = ? and '.$column.' = ?',array($module,$file));
		if ($res && $adb->getAffectedRowCount($res) > 0) {
			self::log("Deleting SDK File ($module,$file) ... DONE");
			self::clearSessionValue('sdk_file');
		} else {
			self::log("Deleting SDK File ($module,$file) ... FAILED");
		}
	}

	function getHomeIframes() {
 		global $adb,$table_prefix;
 		if (!isset($_SESSION['sdk_home_iframe'])) {
 			$result = $adb->query('select * from sdk_home_iframe inner join '.$table_prefix.'_homestuff on sdk_home_iframe.stuffid = '.$table_prefix.'_homestuff.stuffid');
 			if ($result && $adb->num_rows($result)>0) {
 				while ($row = $adb->fetchByAssoc($result)) {
 					$_SESSION['sdk_home_iframe'][$row['stuffid']] = $row;
 				}
 			}
 		}
 		return $_SESSION['sdk_home_iframe'];
 	}

 	function getHomeIframe($stuffid) {
 		$iframes = self::getHomeIframes();
 		return $iframes[$stuffid];
 	}

	function setHomeIframe($size, $url, $title, $userid = null, $useframe = true) {
 		global $adb,$table_prefix;
 		if (empty($url)) {
 			self::log("Adding SDK Home Iframe ($url) ... FAILED (url empty)");
 			return;
 		}
 		// users
 		if (is_null($userid)) {
 			// all users
 			$userid = array_keys(get_user_array(false));
 		} elseif (!is_array($userid)) {
 			$userid = array($userid);
 		}
 	 	//duplicate
 		$iframes = self::getHomeIframes();
 		if (!empty($iframes)) {
 			foreach ($iframes as $id=>$idata) {
 				if ($idata['url'] == htmlspecialchars($url) && in_array($idata['userid'], $userid)) {
 					self::log("Adding SDK Home Iframe ($url) ... FAILED (url already registered)");
 					return;
 				}
 			}
 		}
 		// restrict size
 		$size = max(1, min($size, 4));
 		$useframe = intval($useframe);
 		foreach ($userid as $uid) {
 			$iframeid = $adb->getUniqueID($table_prefix."_homestuff");
 			$params = array($iframeid,0,'SDKIframe', $uid, 0, $title);
 			$adb->pquery('insert into '.$table_prefix.'_homestuff (stuffid,stuffsequence,stufftype,userid,visible,stufftitle) values ('.generateQuestionMarks($params).')',array($params));

 			$params = array($iframeid,$size, $useframe, $url);
 			$column = array('stuffid','size','iframe','url');
 			$adb->format_columns($column);
 			$adb->pquery('insert into sdk_home_iframe ('.implode(',',$column).') values ('.generateQuestionMarks($params).')',array($params));
 		}
 		self::log("Adding SDK Home Iframe ($url) ... DONE");
 		self::clearSessionValue('sdk_home_iframe');
 	}

 	// todo: come parametro passare un array
 	function unsetHomeIframe($stuffid) {
 		global $adb,$table_prefix;
 		$res = $adb->pquery('delete from sdk_home_iframe where stuffid = ?', array($stuffid));
 		$res2 = $adb->pquery('delete from '.$table_prefix.'_homestuff where stuffid = ?', array($stuffid));
 		if ($res && $res2 && $adb->getAffectedRowCount($res) > 0) {
 			self::log("Deleting SDK Home Iframe ($stuffid) ... DONE");
 			self::clearSessionValue('sdk_home_iframe');
 		} else {
 			self::log("Deleting SDK Home Iframe ($stuffid) ... FAILED");
 		}
 	}

 	private function getHomeIframeByUrl($url) {
 		global $adb;
 		$url = strtolower($url);

 		$params = array($url);
 		$query = 'select stuffid from sdk_home_iframe where lower(url) = ?';

 		$res = $adb->pquery($query, $params);
 		$ret = array();
 		if ($res && $adb->num_rows($res) > 0) {
 			while ($row = $adb->fetch_array($res)) {
 				$ret[] = $row[0];
 			}
 		}
 		return $ret;
 	}

 	function unsetHomeIframeByUrl($url) {
 		global $adb;

 		$ids = self::getHomeIframeByUrl($url);

 		foreach ($ids as $id) {
 			self::unsetHomeIframe($id);
 		}
 	}

	function getMenuButton($type, $module='', $action='',$check='') {
 		global $adb, $theme;
 		$buttons = '';
 		$minImg = '';
 		if ($_COOKIE['crmvWinMaxStatus'] == 'close') {
	 		$minImg = '_min';
 		}
 		if ($type == 'fixed') {
 			$res = $adb->query('select * from sdk_menu_fixed order by id');
 			if ($res && $adb->num_rows($res) > 0) {
	 			while($row=$adb->fetchByAssoc($res,-1,false)) {
	 				$image = explode('.',$row['image']);
	 				$image = $image[0].$minImg.'.'.$image[1];
	 				$buttons .= '<td><img src="'.vtiger_imageurl($image,$theme).'" onClick="'.$row['onclick'].'" alt="'.getTranslatedString($row['title'],$module).'" title="'.getTranslatedString($row['title'],$module).'" style="cursor:pointer;"/></td>';
	 			}
 			}
 		} elseif ($type == 'contestual' && $module != '') {
 			$query = 'select * from sdk_menu_contestual where module = ?';
			if ($action == '') {
				$query .= ' and (action = ? or action is null)';
			} else {
				$query .= ' and action = ?';
			}
			// danzi.tn@20130426
			if( $check == 'no' ) $query .= " and title <> 'LBL_BYPRODUCT_BTN'";
			// danzi.tn@20130426e
			$query.=" order by id";
 			$res = $adb->pquery($query,array($module,$action));
 			if ($res && $adb->num_rows($res) > 0) {
	 			while($row=$adb->fetchByAssoc($res,-1,false)) {
	 				$image = explode('.',$row['image']);
	 				$image = $image[0].$minImg.'.'.$image[1];
	 				$buttons .= '<td><img src="'.vtiger_imageurl($image,$theme).'" onClick="'.$row['onclick'].'" alt="'.getTranslatedString($row['title'],$module).'" title="'.getTranslatedString($row['title'],$module).'" style="cursor:pointer;"/></td>';
	 			}
 			}
 		}
 		return $buttons;
 	}

 	function setMenuButton($type, $title, $onclick, $image='', $module='', $action='') {
 		global $adb;
 		if ($title == '' || $onclick == '' || ($type == 'contestual' && $module == '')) {
 			self::log("Adding SDK Menu Button ... FAILED (one or more params omitted)");
 			return;
 		}
 		if ($type == 'fixed') {
 			$res = $adb->pquery('select * from sdk_menu_fixed where title = ? and onclick like ?',array($title,$onclick));
 			if ($res && $adb->num_rows($res) > 0) {
	 			self::log("Adding SDK Menu Button Fixed ... FAILED (button already registered)");
	 			return;
 			}
 			$id = $adb->getUniqueID("sdk_menu_fixed");
	 		$params = array($id,$title,$onclick,$image);
	 		$column = array('id','title','onclick','image');
	 		$adb->format_columns($column);
	 		$adb->pquery('insert into sdk_menu_fixed ('.implode(',',$column).') values ('.generateQuestionMarks($params).')',array($params));
	 		self::log("Adding SDK Menu Button Fixed ($id) ... DONE");
 		} elseif ($type == 'contestual') {
 			/*	TODO: check duplicates
 			$col_title = 'title';
 			$col_onclick = 'onclick';
 			$col_module = 'module';
 			$col_action = 'action';
 			$adb->format_columns($col_title);
 			$adb->format_columns($col_onclick);
 			$adb->format_columns($col_module);
 			$adb->format_columns($col_action);
 			$res = $adb->pquery('select * from sdk_menu_contestual where '.$col_title.' = ? and '.$col_onclick.' = ? and '.$col_module.' = ? and '.$col_action.' = ?',array($title,$onclick,$module,$action));
 			if ($res && $adb->num_rows($res) > 0) {
	 			self::log("Adding SDK Menu Button Contestual ... FAILED (button already registered)");
	 			return;
 			}*/
 			$id = $adb->getUniqueID("sdk_menu_contestual");
	 		$params = array($id,$module,$action,$title,$onclick,$image);
	 		$column = array('id','module','action','title','onclick','image');
	 		$adb->format_columns($column);
	 		$adb->pquery('insert into sdk_menu_contestual ('.implode(',',$column).') values ('.generateQuestionMarks($params).')',array($params));
	 		self::log("Adding SDK Menu Button Contestual ($id) ... DONE");
 		}
 	}

 	function unsetMenuButton($type, $id) {
 		global $adb;
 		$res = $adb->pquery('delete from sdk_menu_'.$type.' where id = ?',array($id));
 		if ($res && $adb->getAffectedRowCount($res) > 0) {
 			self::log("Deleting SDK Menu Button ... DONE");
 		} else {
 			self::log("Deleting SDK Menu Button ... FAILED");
 		}
 	}

 	function getReportFolders($read_reports = true) {
 		global $adb,$table_prefix;
 		if (!isset($_SESSION['sdk_reportfolders'])) {
 			$tmp = array();
 			$result = $adb->pquery('select * from '.$table_prefix.'_crmentityfolder where tabid = ? and state = ?',array(getTabid('Reports'), 'SDK')); // crmv@30967
 			if ($result && $adb->num_rows($result)>0) {
 				while($row=$adb->fetchByAssoc($result)) {
 					$row['name'] = $row['foldername'];
 					$row['id'] = $row['folderid'];
 					if ($read_reports) {
 						$row['details'] = self::getReports($row['folderid']);
 					}
 					$tmp[$row['folderid']] = $row;
 				}
 			}
 			$_SESSION['sdk_reportfolders'] = $tmp;
 		}
 		return $_SESSION['sdk_reportfolders'];
 	}

 	function getReportFolderIdByName($foldername) {
 		$folders = self::getReportFolders();

 		foreach ($folders as $id=>$fldr) {
 			if ($fldr['foldername'] == $foldername) {
 				return $id;
 			}
 		}
 		return null;
 	}

	function setReport($name, $description, $foldername, $reportrun, $class, $jsfunction = '') {
 		global $adb,$table_prefix;

 		// check duplicates
 		if (!is_null(self::getReportIdByName($name))) {
 			self::log("Adding SDK Report ($name) ... FAILED (duplicate report)");
 			return false;
 		}

 		// folderid
 		$folderid = self::getReportFolderIdByName($foldername);
 		if (is_null($folderid)) {
 			self::log("Adding SDK Report ($name) ... FAILED (folder doesn't exist)");
 			return false;
 		}

 		// insert
 		$reportid = $adb->getUniqueID($table_prefix.'_selectquery');

 		$params = array($reportid, $reportrun, $class, $jsfunction);
 		$res = $adb->pquery('insert into sdk_reports (reportid, reportrun, runclass, jsfunction) values ('.generateQuestionMarks($params).')', $params);

 		$params = array($reportid, 0, 0);
 		$res1 = $adb->pquery('insert into '.$table_prefix.'_selectquery (queryid, startindex, numofobjects) values ('.generateQuestionMarks($params).')', $params);

 		$params = array($reportid, $folderid, $name, $description, 'tabular', $reportid, 'SDK', 0, 1, 1, 'Public');
 		$res2 = $adb->pquery('insert into '.$table_prefix.'_report (reportid, folderid, reportname, description, reporttype, queryid, state, customizable, category, owner, sharingtype) values ('.generateQuestionMarks($params).')', $params);

 		if ($res && $res2 && $adb->getAffectedRowCount($res) > 0) {
 			self::log("Adding SDK Report ($name) ... DONE");
 			self::clearSessionValue('sdk_reports');
 			self::clearSessionValue('sdk_reportfolders');
 		} else {
 			self::log("Adding SDK Report ($name) ... FAILED");
 		}
 	}

	function setReportFolder($name, $description) {
 		global $adb,$table_prefix;

 		// check duplicates
 		$folders = self::getReportFolders();
 		foreach ($folders as $fld) {
 			if ($fld['foldername'] == $name) {
 				self::log("Adding SDK Report Folder ($name) ... FAILED (folder exists)");
 				return false;
 			}
 		}

 		$folderid = $adb->getUniqueID($table_prefix.'_reportfolder');
 		$params = array($folderid, $name, $description, 'SDK');

 		// crmv@30967
 		$res = addEntityFolder('Reports', $name, $description, 1, 'SDK');
 		if ($res) {
 		// crmv@30967e
 			self::log("Adding SDK Report Folder ($name) ... DONE");
 			self::clearSessionValue('sdk_reportfolders');
 		} else {
 			self::log("Adding SDK Report Folder ($name) ... FAILED");
 		}
 	}

 	function unsetReportFolder($name, $delreports = true) {
 		global $adb;

 		$folderid = self::getReportFolderIdByName($name);
 		if (is_null($folderid)) {
 			self::log("Deleting SDK Report Folder ($name) ... FAILED (folder not found)");
 			return;
 		}

 		if ($delreports) {
 			// delete all reports in folder
 			$reps = self::getReports($folderid);
 			foreach ($reps as $rep) {
 				self::unsetReport($rep['reportname']);
 			}
 		}

 		// crmv@30967
 		$res = deleteEntityFolder($folderid);
 		if ($res) {
		// crmv@30967e
 			self::log("Deleting SDK Report Folder ($name) ... DONE");
 			self::clearSessionValue('sdk_reportfolders');
 		} else {
 			self::log("Deleting SDK Report Folder ($name) ... FAILED");
 		}
 	}

 	function getReports($folderid) {
 		global $adb,$table_prefix;
 		if (!isset($_SESSION['sdk_reports']) || !isset($_SESSION['sdk_reports'][$folderid])) {
 			$tmp = array();
 			$result = $adb->pquery('select * from sdk_reports inner join '.$table_prefix.'_report on '.$table_prefix.'_report.reportid = sdk_reports.reportid where folderid = ?',array($folderid));
 			if ($result && $adb->num_rows($result)>0) {
 				while($row=$adb->fetchByAssoc($result)) {
 					$tmp[$row['reportid']] = $row;
 				}
 			}
 			if (!is_array($_SESSION['sdk_reports'])) $_SESSION['sdk_reports'] = array();
 			$_SESSION['sdk_reports'][$folderid] = $tmp;
 		}
 		return $_SESSION['sdk_reports'][$folderid];
 	}

 	function getReportIdByName($reportname) {
 		$folders = self::getReportFolders();
 		foreach ($folders as $fld) {
 			foreach ($fld['details'] as $repid=>$report) {
 				if ($report['reportname'] == $reportname) return $repid;
 			}
 		}
 		return null;
 	}

 	function getReport($reportid, $folderid) {
 		$reports = self::getReports($folderid);
 		if (array_key_exists($reportid, $reports)) return $reports[$reportid];
 		return null;
 	}

 	function unsetReport($name) {
 		global $adb,$table_prefix;

 		$repid = self::getReportIdByName($name);

 		if (is_null($repid)) {
 			self::log("Deleting SDK Report ($name) ... FAILED (report not found)");
 			return;
 		}

 		$query = 'delete from sdk_reports where reportid = ?';
 		$res = $adb->pquery($query, array($repid));

 		$query = 'delete from '.$table_prefix.'_report where reportid = ? and state = ?';
 		$res2 = $adb->pquery($query, array($repid, 'SDK'));

 		if ($res && $res2 && $adb->getAffectedRowCount($res) > 0 && $adb->getAffectedRowCount($res2) > 0) {
 			self::log("Deleting Report ($name) ... DONE");
 			self::clearSessionValue('sdk_reports');
 			self::clearSessionValue('sdk_reportfolders');
 		} else {
 			self::log("Deleting SDK Report ($name) ... FAILED");
 		}
 	}

 	//crmv@sdk-27926
 	function getTransitions($module) {
 		global $adb;
 		if (!isset($_SESSION['sdk_transitions']) || !isset($_SESSION['sdk_transitions'][$module])) {
 			$result = $adb->pquery('select * from sdk_transitions where module = ?', array($module));
 			if ($result && $adb->num_rows($result)>0) {
 				while($row=$adb->fetchByAssoc($result)) {
 					$_SESSION['sdk_transitions'][$module][$row['fieldname']] = $row;
 				}
 			}
 		}
 		return $_SESSION['sdk_transitions'][$module];
 	}

 	function getTransition($module, $fieldname) {
 		$trans = self::getTransitions($module);
 		return $trans[$fieldname];
 	}

 	function setTransition($module, $fieldname, $file, $function) {
 		global $adb;

 		if ($module == '' || $fieldname == '' || $file == '') {
 			self::log("Adding SDK Transition ($file) ... FAILED (module, fieldname or file or empty!)");
 			return;
 		}
 		if (self::getTransition($module,$fieldname) != '') {
 			self::log("Adding SDK Transition ($file) ... FAILED (file already registered for module $module and fieldname $fieldname)");
 			return;
 		}
 		$transid = $adb->getUniqueID("sdk_transitions");
 		$params = array($transid,$module,$fieldname,$file,$function);
 		$column = array('transitionid','module','fieldname','file', 'function');
 		$adb->format_columns($column);
 		$adb->pquery('insert into sdk_transitions ('.implode(',',$column).') values ('.generateQuestionMarks($params).')',array($params));
 		self::log("Adding SDK Transition ($file) ... DONE");
 		self::clearSessionValue('sdk_transitions');
 	}

 	function unsetTransition($module, $fieldname, $file, $function) {
 		global $adb;
 		$column = 'fieldname';
 		$adb->format_columns($column);
 		$res = $adb->pquery('delete from sdk_transitions where module = ? and '.$column.' = ?',array($module,$fieldname));
 		if ($res && $adb->getAffectedRowCount($res) > 0) {
 			self::log("Deleting SDK Transition ($module,$fieldname) ... DONE");
 			self::clearSessionValue('sdk_transitions');
 		} else {
 			self::log("Deleting SDK Transition ($module,$fieldname) ... FAILED");
 		}
 	}
 	//crmv@sdk-27926e

 	//crmv@sdk-28873
 	function getDashboards() {
 		global $adb;
 		if (!isset($_SESSION['sdk_dashboards'])) {
 			$tmp = array();
 			$result = $adb->query('select * from sdk_dashboard');
			if ($result && $adb->num_rows($result)>0) {
				if (!is_array($_SESSION['sdk_dashboards'])) $_SESSION['sdk_dashboards'] = array();
				while($row=$adb->fetchByAssoc($result)) {
					$_SESSION['sdk_dashboards'][$row['dashboardname']] = $row;
				}
			}
		}
		return $_SESSION['sdk_dashboards'];
	}

	function getDashboard($dashname) {
		$dashes = self::getDashboards();
		if (is_array($dashes) && array_key_exists($dashname, $dashes)) return $dashes[$dashname];
		return null;
	}

	function setDashboard($name,$file) {
		global $adb;

		if ($name == '' || $file == '') {
			self::log("Adding SDK Dashboard ($name) ... FAILED (name or file empty!)");
			return;
		}
		if (self::getDashboard($name) != '') {
			self::log("Adding SDK Dashboard ($name) ... FAILED (Dashboard $name already registered)");
			return;
		}
		$dashid = $adb->getUniqueID("sdk_dashboard");
		$params = array($dashid ,$name,$file);
		$column = array('dashboardid','dashboardname','file');
		$adb->format_columns($column);
		$adb->pquery('insert into sdk_dashboard ('.implode(',',$column).') values ('.generateQuestionMarks($params).')',array($params));
		self::log("Adding SDK Dashboard ($name) ... DONE");
		self::clearSessionValue('sdk_dashboards');
	}

	function unsetDashboard($name) {
		global $adb;
		$column = 'dashboardname';
		$adb->format_columns($column);
		$res = $adb->pquery('delete from sdk_dashboard where '.$column.' = ?',array($name));
		if ($res && $adb->getAffectedRowCount($res) > 0) {
			self::log("Deleting SDK Dashboard ($name) ... DONE");
			self::clearSessionValue('sdk_dashboards');
		} else {
			self::log("Deleting SDK Dashboard ($name) ... FAILED");
		}
	}
	//crmv@sdk-28873e

 	/*
 	 * returns an array of all files/dirs which has been loaded by SDK
 	 * TODO: optional parameter to pretty print the array, by module or customization type
 	 * TODO: check if files are missing
 	 * TODO: add SDK API to query without module
 	 */
 	function getAllCustomizations($readLinks = false) {
 		global $adb,$table_prefix;

 		$advPerm = self::getAdvancedPermissions();
 		$advQueries = self::getAdvancedQueries();
 		$classes = self::getClasses();
 		//$extraSrc = self::getExtraSrc();
 		//$files = self::getFiles();
 		$iframes = self::getHomeIframes();
 		//$buttons = self::getMenuButton();
 		//$popQueries = self::getPopupQueries();
 		$popFuncs = self::getPopupReturnFunctions();
 		$preSave = self::getPreSaveList();
 		$repFolders = self::getReportFolders();
 		$smarty = self::getSmartyTemplates();
 		$uitypes = self::getUitypes();
 		//$views = self::getViews();

 		$files = array();
 		if (is_array($advPerm)) {
 			foreach ($advPerm as $perm) $files[] = $perm['src'];
 		}
 		if (is_array($advQueries)) {
 			foreach ($advQueries as $query) $files[] = $query['src'];
 		}
 		if (is_array($classes)) {
 			foreach ($classes as $class) $files[] = $class['src'];
 		}
 	 	if (is_array($iframes)) {
 	 		$frs = array();
 			foreach ($iframes as $frame) $frs[] = $frame['url'];
 			$frs = array_unique($frs);
 			$files = array_merge($files, array_values($frs));
 		}
 	 	if (is_array($popFuncs)) {
 			foreach ($popFuncs as $func) $files[] = $func['src'];
 		}
 		if (is_array($preSave)) {
 			foreach ($preSave as $ps) $files[] = $ps['src'];
 		}
 		if (is_array($repFolders)) {
 			foreach ($repFolders as $folder) {
 				$reports = $folder['details'];
 				if (is_array($reports)) {
 					foreach ($reports as $rep) $files[] = $rep['reportrun'];
 				}
 			}
 		}
 		if (is_array($smarty)) {
 			foreach ($smarty as $templ) $files[] = 'Smarty/templates/'.$templ['src'];
 		}
 		if (is_array($uitypes)) {
 			foreach ($uitypes as $type) {
 				$files[] = $type['src_php'];
 				$files[] = $type['src_tpl'];
 				if (!empty($type['src_js'])) $files[] = $type['src_js'];
 			}
 		}

 		// for the next ones we need to directly query the db
 		$res = $adb->query('select src from sdk_extra_src');
 		if ($res && $adb->num_rows($res) > 0) {
 			while ($row = $adb->fetchByAssoc($res)) $files[] = $row['src'];
 		}
 		$res = $adb->query('select module,new_file from sdk_file');
 		if ($res && $adb->num_rows($res) > 0) {
 			while ($row = $adb->fetchByAssoc($res)) $files[] = 'modules/'.$row['module'].'/'.$row['new_file'].'.php';
 		}
 		/* menu buttons: javascript code, should we parse it? */
 		$res = $adb->query('select src from sdk_popup_query');
 		if ($res && $adb->num_rows($res) > 0) {
 			while ($row = $adb->fetchByAssoc($res)) $files[] = $row['src'];
 		}
 		$res = $adb->query('select src from sdk_view');
 		if ($res && $adb->num_rows($res) > 0) {
 			while ($row = $adb->fetchByAssoc($res)) $files[] = $row['src'];
 		}

 		// read stuff loaded in vtiger_links
 		if ($readLinks) {
 			$res = $adb->query('select linkurl,linkicon from '.$table_prefix.'_links where linktype in ("HEADERSCRIPT", "HEADERCSS") or linklabel like "%sdk%"');
 			if ($res && $adb->num_rows($res) > 0) {
 				$stuff = array();
 				while ($row = $adb->fetchByAssoc($res)) {
 					$stuff[] = $row['linkurl'];
 					if (!empty($row['linkicon'])) $stuff[] = $row['linkicon'];
 				}
 				$stuff = array_unique($stuff);
 				$files = array_merge($files, array_values($stuff));
 			}
 		}

 		$files = array_unique($files);
 		sort($files, SORT_STRING);
 		return $files;
 	}

 	function exportXml($moduleInstance) {
 		global $adb,$table_prefix;

		$module = $moduleInstance->name;
		$this->openNode('sdk');

		//sdk_adv_permission - i
 		$advancedPermission = self::getAdvancedPermissions();
 		if ($advancedPermission[$module] != '') {
 			$this->openNode('adv_permission');
			$this->outputNode($advancedPermission[$module]['src'], 'src');
 			$this->outputNode($advancedPermission[$module]['function'], 'function');
 			$this->closeNode('adv_permission');
 		}
 		//sdk_adv_permission - e

 		//sdk_adv_query - i
 		$advancedQuery = self::getAdvancedQueries();
 		if ($advancedQuery[$module] != '') {
			$this->openNode('adv_query');
			$this->outputNode($advancedQuery[$module]['src'], 'src');
 			$this->outputNode($advancedQuery[$module]['function'], 'function');
 			$this->closeNode('adv_query');
 		}
 		//sdk_adv_query - e

 		//sdk_class - i
 		$sdk_class = self::getClass($module);
 		if (!empty($sdk_class)) {
 			$this->openNode('classes');

 			$result = $adb->pquery('select * from sdk_class where extends = ?',array($module));
 			if ($result && $adb->num_rows($result)>0) {
				$this->openNode('class');
				$this->outputNode($adb->query_result($result,0,'extends'), 'extends');
				$this->outputNode($adb->query_result($result,0,'module'), 'module');
				$this->outputNode($adb->query_result($result,0,'src'), 'src');
				$this->closeNode('class');
 			}
 			$ds = $adb->query_result($result,0,'module');
	 		while(($ds = self::getDirectSonModule($ds)) != '') {
	 			$result = $adb->pquery('select * from sdk_class where module = ?',array($ds));
 				if ($result && $adb->num_rows($result)>0) {
					$this->openNode('class');
					$this->outputNode($adb->query_result($result,0,'extends'), 'extends');
					$this->outputNode($adb->query_result($result,0,'module'), 'module');
					$this->outputNode($adb->query_result($result,0,'src'), 'src');
					$this->closeNode('class');
 				}
			}

			$this->closeNode('classes');
 		}
		//sdk_class - e

		//sdk_extra_src - i
		$sdk_extra_src = self::getExtraSrc($module);
		if (!empty($sdk_extra_src)) {
			$this->openNode('extra_sources');
			foreach($sdk_extra_src as $extra_src) {
				$this->outputNode($extra_src, 'extra_src');
			}
			$this->closeNode('extra_sources');
		}
		//sdk_extra_src - e

 		//sdk_popup_query - i
 		$this->openNode('popup_queries');
 		$sdk_popup_query_types = array('field','related');
 		foreach($sdk_popup_query_types as $sdk_popup_query_type) {
	 		$popupQueries = self::getPopupQueries($sdk_popup_query_type);
	 		if (!empty($popupQueries)) {
		 		foreach($popupQueries as $info) {
		 			if ($module == $info['module']) {
						$this->openNode('popup_query');
						$this->outputNode($sdk_popup_query_type, 'type');
						$this->outputNode($info['param'], 'param');
						$this->outputNode($info['src'], 'src');
						$this->outputNode($info['hidden_rel_fields'], 'hidden_rel_fields');	//crmv@26920
						$this->closeNode('popup_query');
		 			}
		 		}
	 		}
 		}
 		$this->closeNode('popup_queries');
 		//sdk_popup_query - e

 		//sdk_popup_return_funct - i
 		$popupReturnFunctions = self::getPopupReturnFunctions();
 		if (!empty($popupReturnFunctions)) {
 			$this->openNode('popup_return_functs');
	 		foreach($popupReturnFunctions as $id => $info) {
				if ($module == $info['module']) {
					$this->openNode('popup_return_funct');
					$this->outputNode($info['fieldname'], 'fieldname');
					$this->outputNode($info['src'], 'src');
					$this->closeNode('popup_return_funct');
				}
	 		}
	 		$this->closeNode('popup_return_functs');
 		}
 		//sdk_popup_return_funct - e

 		//sdk_presave - i
 		$preSave = self::getPreSaveList();
 		if (!empty($popupReturnFunctions)) {
	 		foreach($preSave as $id => $info) {
	 			if ($module == $info['module']) {
	 				$this->openNode('presave');
					$this->outputNode($info['src'], 'src');
					$this->closeNode('presave');
					break;
	 			}
	 		}
 		}
		//sdk_presave - e

 		//sdk_smarty - i
 		$smartyTemplates = self::getSmartyTemplates();
 		if (!empty($smartyTemplates)) {
 			$this->openNode('smarty_templates');
	 		$src = array();
	 		foreach($smartyTemplates as $smartyTemplate) {
	 			$params = Zend_Json::decode($smartyTemplate['params']);
	 			if (!empty($params['module']) && $params['module'] == $module) {
					$this->openNode('smarty_template');
					$this->outputNode($smartyTemplate['params'], 'params');
					$this->outputNode($smartyTemplate['src'], 'src');
					$this->closeNode('smarty_template');
	 			}
	 		}
	 		$this->closeNode('smarty_templates');
 		}
 		//sdk_smarty - e

 		//sdk_uitype - i
 		$sdkUitype = self::getUitypes();
 		if (!empty($sdkUitype)) {
 			$this->openNode('uitypes');
 			$result = $adb->pquery("SELECT distinct uitype FROM ".$table_prefix."_field WHERE tabid = ? AND uitype IN (".generateQuestionMarks(array_keys($sdkUitype)).")",array(getTabid($module),array_keys($sdkUitype)));
 			if ($result && $adb->num_rows($result)>0) {
	 			while($row=$adb->fetchByAssoc($result)) {
	 				$this->openNode('uitype');
	 				$this->outputNode($row['uitype'], 'uitype');
	 				$this->outputNode($sdkUitype[$row['uitype']]['src_php'], 'src_php');
	 				$this->outputNode($sdkUitype[$row['uitype']]['src_tpl'], 'src_tpl');
	 				$this->outputNode($sdkUitype[$row['uitype']]['src_js'], 'src_js');
	 				$this->closeNode('uitype');
	 			}
 			}
 			$this->closeNode('uitypes');
 		}
 		//sdk_uitype - e

 		//sdk_view - i
 		$sdkView = self::getViews($module,'');
 		if (!empty($sdkView)) {
 			$this->openNode('views');
 			foreach ($sdkView as $sequence => $info) {
				$this->openNode('view');
 				$this->outputNode($info['src'], 'src');
 				$this->outputNode($sequence, 'sequence');
 				$this->outputNode($info['mode'], 'mode');
 				$this->outputNode($info['on_success'], 'on_success');
 				$this->closeNode('view');
 			}
 			$this->closeNode('views');
 		}
 		//sdk_view - e

 		//sdk_file - i
 		$sdkFile = self::getFiles($module);
 		if (!empty($sdkFile)) {
 			$this->openNode('files');
 			foreach ($sdkFile as $file => $new_file) {
				$this->openNode('file');
 				$this->outputNode($file, 'file');
 				$this->outputNode($new_file, 'new_file');
 				$this->closeNode('file');
 			}
 			$this->closeNode('files');
 		}
 		//sdk_file - e

		$this->closeNode('sdk');
 	}

 	function exportPackage($module,$zip) {
 		global $adb,$table_prefix;

 		//sdk_adv_permission - i
 		$advancedPermission = self::getAdvancedPermissions();
 		if ($advancedPermission[$module] != '') {
 			$src = $advancedPermission[$module]['src'];
 			$dir = substr($src, 0, strripos($src,'/'));
			$file = substr($src, strripos($src,'/')+1, strlen($src));
			$zip->copyFileFromDisk($dir,'sdk/adv_permission',$file);
 		}
 		//sdk_adv_permission - e

 		//sdk_adv_query - i
 		$advancedQuery = self::getAdvancedQueries();
 		if ($advancedQuery[$module] != '') {
 			$src = $advancedQuery[$module]['src'];
 			$dir = substr($src, 0, strripos($src,'/'));
			$file = substr($src, strripos($src,'/')+1, strlen($src));
			$zip->copyFileFromDisk($dir,'sdk/adv_query',$file);
 		}
 		//sdk_adv_query - e

 		//sdk_class - i
 		$sdk_class = self::getClass($module);
 		$sdk_class_list = array();
 		if (!empty($sdk_class)) {
 			$result = $adb->pquery('select * from sdk_class where extends = ?',array($module));
 			if ($result && $adb->num_rows($result)>0) {
				$sdk_class_list[$adb->query_result($result,0,'module')] = $adb->query_result($result,0,'src');
 			}
 			$ds = $adb->query_result($result,0,'module');
	 		while(($ds = self::getDirectSonModule($ds)) != '') {
	 			$result = $adb->pquery('select * from sdk_class where module = ?',array($ds));
 				if ($result && $adb->num_rows($result)>0) {
					$sdk_class_list[$ds] = $adb->query_result($result,0,'src');
 				}
			}
			if (!empty($sdk_class_list)) {
				$sdk_class_list = array_unique($sdk_class_list);
				foreach($sdk_class_list as $src) {
		 			$dir = substr($src, 0, strripos($src,'/'));
					$file = substr($src, strripos($src,'/')+1, strlen($src));
					$zip->copyFileFromDisk($dir,'sdk/class',$file);
				}
			}
 		}
		//sdk_class - e

 		//sdk_extra_src - i
		$sdk_extra_src = self::getExtraSrc($module);
		if (!empty($sdk_extra_src)) {
			foreach($sdk_extra_src as $extra_src) {
				$src = $extra_src;
				if (is_file($src)) {
					$dir = substr($src, 0, strripos($src,'/'));
					$file = substr($src, strripos($src,'/')+1, strlen($src));
					$zip->copyFileFromDisk($dir,'sdk/extra_src',$file);
				} elseif (is_dir($src)) {
					$dir = substr($src, strripos($src,'/')+1, strlen($src));
					$zip->copyDirectoryFromDisk($src,"sdk/extra_src/$dir");
				}
			}
		}
		//sdk_extra_src - e

 		//sdk_popup_query - i
 		$sdk_popup_query_types = array('field','related');
 		foreach($sdk_popup_query_types as $sdk_popup_query_type) {
	 		$popupQueries = self::getPopupQueries($sdk_popup_query_type);
	 		if (!empty($popupQueries)) {
		 		foreach($popupQueries as $info) {
		 			if ($module == $info['module']) {
		 				$src = $info['src'];
		 				$dir = substr($src, 0, strripos($src,'/'));
						$file = substr($src, strripos($src,'/')+1, strlen($src));
						$zip->copyFileFromDisk($dir,'sdk/popup_query',$file);
		 			}
		 		}
	 		}
 		}
 		//sdk_popup_query - e

 		//sdk_popup_return_funct - i
 		$popupReturnFunctions = self::getPopupReturnFunctions();
 		foreach($popupReturnFunctions as $id => $info) {
			if ($module == $info['module']) {
				$src = $info['src'];
				$dir = substr($src, 0, strripos($src,'/'));
				$file = substr($src, strripos($src,'/')+1, strlen($src));
				$zip->copyFileFromDisk($dir,'sdk/popup_return_funct',$file);
			}
 		}
 		//sdk_popup_return_funct - e

		//sdk_presave - i
 		$preSave = self::getPreSaveList();
 		foreach($preSave as $id => $info) {
 			if ($module == $info['module']) {
 				$src = $info['src'];
				$dir = substr($src, 0, strripos($src,'/'));
				$file = substr($src, strripos($src,'/')+1, strlen($src));
				$zip->copyFileFromDisk($dir,'sdk/presave',$file);
 			}
 		}
		//sdk_presave - e

		//sdk_smarty - i
 		$smartyTemplates = self::getSmartyTemplates();
 		if (!empty($smartyTemplates)) {
	 		$src = array();
	 		foreach($smartyTemplates as $smartyTemplate) {
	 			$params = Zend_Json::decode($smartyTemplate['params']);
	 			if (!empty($params['module']) && $params['module'] == $module) {
	 				$src = $smartyTemplate['src'];
	 				$dir = substr($src, 0, strripos($src,'/'));
					$file = substr($src, strripos($src,'/')+1, strlen($src));
					$zip->copyFileFromDisk('Smarty/templates/'.$dir,'sdk/smarty',$file);
	 			}
	 		}
 		}
 		//sdk_smarty - e

 		//sdk_uitype - i
 		$sdkUitype = self::getUitypes();
 		if (!empty($sdkUitype)) {
 			$result = $adb->pquery("SELECT distinct uitype FROM ".$table_prefix."_field WHERE tabid = ? AND uitype IN (".generateQuestionMarks(array_keys($sdkUitype)).")",array(getTabid($module),array_keys($sdkUitype)));
 			if ($result && $adb->num_rows($result)>0) {
	 			while($row=$adb->fetchByAssoc($result)) {
	 				$src_php = $sdkUitype[$row['uitype']]['src_php'];
	 				$dir = substr($src_php, 0, strripos($src_php,'/'));
	 				$file = substr($src_php, strripos($src_php,'/')+1, strlen($src_php));
	 				$zip->copyFileFromDisk($dir,'sdk/uitype/php',$file);

	 				$src_tpl = $sdkUitype[$row['uitype']]['src_tpl'];
	 				$dir = substr($src_tpl, 0, strripos($src_tpl,'/'));
	 				$file = substr($src_tpl, strripos($src_tpl,'/')+1, strlen($src_tpl));
	 				$zip->copyFileFromDisk('Smarty/templates/'.$dir,'sdk/uitype/tpl',$file);

	 				$src_js = $sdkUitype[$row['uitype']]['src_js'];
	 				$dir = substr($src_js, 0, strripos($src_js,'/'));
	 				$file = substr($src_js, strripos($src_js,'/')+1, strlen($src_js));
	 				$zip->copyFileFromDisk($dir,'sdk/uitype/js',$file);
	 			}
 			}
 		}
 		//sdk_uitype - e

 		//sdk_view - i
 		$sdkView = self::getViews($module,'');
 		if (!empty($sdkView)) {
 			foreach ($sdkView as $info) {
 				$src = $info['src'];
				$dir = substr($src, 0, strripos($src,'/'));
				$file = substr($src, strripos($src,'/')+1, strlen($src));
				$zip->copyFileFromDisk($dir,'sdk/view',$file);
 			}
 		}
 		//sdk_view - e

 		//sdk_file - i
 		/*
 		$sdkFile = self::getFiles($module);
 		if (!empty($sdkFile)) {
 			foreach ($sdkFile as $new_file) {
				$zip->copyFileFromDisk("modules/$module",'sdk/file',"$new_file.php");
 			}
 		}
		*/
 		//sdk_file - e
 	}

 	function importPackage($modulenode, $moduleInstance) {

 		if (empty($modulenode->sdk)) return;
 		$module = strval($moduleInstance->name);
 		$tmp_dir = 'modules/SDK/tmp';

		if (!empty($modulenode->sdk->adv_permission)) {
			self::setAdvancedPermissionFunction($module, $modulenode->sdk->adv_permission->function, $modulenode->sdk->adv_permission->src);

			$dest = $modulenode->sdk->adv_permission->src;
 			$file = basename($dest);
 			@mkdir(dirname($dest));
			copy("$tmp_dir/adv_permission/$file", $dest);
		}
		if (!empty($modulenode->sdk->adv_query)) {
			self::setAdvancedQuery($module, $modulenode->sdk->adv_query->function, $modulenode->sdk->adv_query->src);

			$dest = $modulenode->sdk->adv_query->src;
 			$file = basename($dest);
 			@mkdir(dirname($dest));
			copy("$tmp_dir/adv_query/$file", $dest);
		}
		if (!empty($modulenode->sdk->classes)) {
			foreach($modulenode->sdk->classes->class as $class) {
				self::setClass($class->extends, $class->module, $class->src);

				$dest = $class->src;
 				$file = basename($dest);
 				@mkdir(dirname($dest));
				copy("$tmp_dir/class/$file", $dest);
			}
		}
		if (!empty($modulenode->sdk->extra_sources)) {
			foreach($modulenode->sdk->extra_sources->extra_src as $extra_src) {
				self::setExtraSrc($module, $extra_src);

				$dest = $extra_src;
				$file = basename($dest);
				@mkdir(dirname($dest));
				$src = "$tmp_dir/extra_src/$file";
				rcopy($src, $dest);
			}
		}
		if (!empty($modulenode->sdk->popup_queries)) {
			foreach($modulenode->sdk->popup_queries->popup_query as $popup_query) {
				self::setPopupQuery(strval($popup_query->type), $module, $popup_query->param, $popup_query->src, $popup_query->hidden_rel_fields);	//crmv@26920

				$dest = $popup_query->src;
 				$file = basename($dest);
 				@mkdir(dirname($dest));
				copy("$tmp_dir/popup_query/$file", $dest);
			}
		}
		if (!empty($modulenode->sdk->popup_return_functs)) {
			foreach($modulenode->sdk->popup_return_functs->popup_return_funct as $popup_return_funct) {
				self::setPopupReturnFunction($module, $popup_return_funct->fieldname, $popup_return_funct->src);

				$dest = $popup_return_funct->src;
 				$file = basename($dest);
 				@mkdir(dirname($dest));
				copy("$tmp_dir/popup_return_funct/$file", $dest);
			}
		}
		if (!empty($modulenode->sdk->presave)) {
			self::setPreSave($module, $modulenode->sdk->presave->src);

			$dest = $modulenode->sdk->presave->src;
 			$file = basename($dest);
 			@mkdir(dirname($dest));
			copy("$tmp_dir/presave/$file", $dest);
		}
		if (!empty($modulenode->sdk->smarty_templates)) {
			foreach($modulenode->sdk->smarty_templates->smarty_template as $smarty_template) {
				self::setSmartyTemplate(Zend_Json::decode($smarty_template->params), $smarty_template->src);

				$dest = "Smarty/templates/$smarty_template->src";
 				$file = basename($dest);
 				@mkdir(dirname($dest));
				copy("$tmp_dir/smarty/$file", $dest);
			}
		}
		if (!empty($modulenode->sdk->uitypes)) {
			foreach($modulenode->sdk->uitypes->uitype as $uitype) {
				self::setUitype($uitype->uitype,$uitype->src_php,$uitype->src_tpl,$uitype->src_js);

				if ($uitype->src_php != '') {
					$dest = $uitype->src_php;
	 				$file = basename($dest);
	 				@mkdir(dirname($dest));
					copy("$tmp_dir/uitype/php/$file", $dest);
				}

				if ($uitype->src_tpl != '') {
					$dest = "Smarty/templates/$uitype->src_tpl";
	 				$file = basename($dest);
	 				@mkdir(dirname($dest));
					copy("$tmp_dir/uitype/tpl/$file", $dest);
				}

				if ($uitype->src_js != '') {
					$dest = $uitype->src_js;
	 				$file = basename($dest);
	 				@mkdir(dirname($dest));
					copy("$tmp_dir/uitype/js/$file", $dest);
				}
			}
		}
 		if (!empty($modulenode->sdk->views)) {
			foreach($modulenode->sdk->views->view as $view) {
				self::addView($module, $view->src, $view->mode, $view->on_success);

				$dest = $view->src;
 				$file = basename($dest);
 				@mkdir(dirname($dest));
				copy("$tmp_dir/view/$file", $dest);
			}
		}
 		if (!empty($modulenode->sdk->files)) {
			foreach($modulenode->sdk->files->file as $file) {
				self::setFile($module, $file->file, $file->new_file);
			}
		}
		//cancello la cartella temporanea
		if (is_dir($tmp_dir)) {
			folderDetete($tmp_dir);
		}
 	}
	function getPDFCustomFunctions() {
 		global $adb;
 		if (!isset($_SESSION['sdk_pdf_cfunctions'])) {
 			$tmp = array();
 			$result = $adb->query('select * from sdk_pdf_cfunctions');
			if ($result && $adb->num_rows($result)>0) {
				if (!is_array($_SESSION['sdk_pdf_cfunctions'])) $_SESSION['sdk_pdf_cfunctions'] = array();
				while($row=$adb->fetchByAssoc($result)) {
					$_SESSION['sdk_pdf_cfunctions'][$row['name']] = $row;
				}
			}
		}
		return $_SESSION['sdk_pdf_cfunctions'];
	}
	
	function getPDFCustomFunction($name) {
		$functions = self::getPDFCustomFunctions();
		if (is_array($functions) && array_key_exists($name, $functions)) return $functions[$name];
		return null;
	}
	
	function setPDFCustomFunction($label,$name,$params) {
		global $adb;
		if ($label == '' || $name == '' || $params == '') {
			self::log("Adding SDK PDF Custom Function ($name) ... FAILED (label, name or params empty!)");
			return;
		}
		if (self::getPDFCustomFunction($name) != '') {
			self::log("Adding SDK PDF Custom Function ($name) ... FAILED ($name already registered)");
			return;
		}
		if (!is_array($params)) {
			$params = array($params);
		}
		$id = $adb->getUniqueID("sdk_pdf_cfunctions");
		$params = array($id, $label, $name, implode('|',$params));
		$column = array('id','label','name','params');
		$adb->format_columns($column);
		$adb->pquery('insert into sdk_pdf_cfunctions ('.implode(',',$column).') values ('.generateQuestionMarks($params).')',array($params));
		self::log("Adding SDK PDF Custom Function ($name) ... DONE");
		self::clearSessionValue('sdk_pdf_cfunctions');
	}
	
	function unsetPDFCustomFunction($name) {
		global $adb;
		$column = 'name';
		$adb->format_columns($column);
		$res = $adb->pquery('delete from sdk_pdf_cfunctions where '.$column.' = ?',array($name));
		if ($res && $adb->getAffectedRowCount($res) > 0) {
			self::log("Deleting SDK PDF Custom Function ($name) ... DONE");
			self::clearSessionValue('sdk_pdf_cfunctions');
		} else {
			self::log("Deleting SDK PDF Custom Function ($name) ... FAILED");
		}
	}
	//crmv@2539me
}
?>
