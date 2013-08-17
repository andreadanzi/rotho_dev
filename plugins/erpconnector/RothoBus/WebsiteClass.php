<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
 * ********************************************************************************** */
// Switch the working directory to base
// chdir(dirname(__FILE__) . '/../..');

include_once 'include/Zend/Json.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'include/utils/VtlibUtils.php';
include_once 'include/Webservices/Create.php';
include_once 'data/CRMEntity.php';

class WebsiteClass {
	var $log_active = false;
	var $import_result = Array();
	var $mapping = Array();

	var $website_host = "82.150.199.184";
	var $website_port = "3306";
	var $website_dbweb ="rothoblaas";
	var $website_dbsafe = "safe";
	var $website_tableweb ="tt_address";
	var $website_safettaddress ="tt_address";
	var $website_tablesafe = "fe_users";
	var $temp_safe = "web_temp_fe_users";
	var $temp_safettaddress = "web_temp_safe_tt_address";
	var $temp_web = "web_temp_tt_address";
	var $website_usr="rothoblaas";
	var $website_pwd="ThacyianVegg";
	
	function __construct() { 
		// set mapping between columns of temp tables and vtiger fieldnames
		$this->_set_mappings();
	}
		
	function setLog($log_active) {
		$this->log_active = $log_active;
	}
	// populates vtiger entities from temp tables
	function populateNow() {
		global $adb,$table_prefix;
		if($this->log_active) echo "WebsiteClass.populateNow is starting!\n";
		// associative array for outputting the number of items inserted or updated
		$this->import_result['records_created']=0;
		$this->import_result['records_updated']=0;
		$maxtimestamp_tt_address = $this->_get_maxtimestamp_tt_address();
		if($this->log_active) echo "_get_maxtimestamp_tt_address returns ".$maxtimestamp_tt_address."\n";
		$maxtimestamp_fe_users = $this->_get_maxtimestamp_fe_users();
		if($this->log_active) echo "_get_maxtimestamp_fe_users returns ".$maxtimestamp_fe_users."\n";
		$maxtimestamp_safe_tt_address = $this->_get_maxtimestamp_safe_tt_address();
		if($this->log_active) echo "_get_maxtimestamp_safe_tt_address returns ".$maxtimestamp_safe_tt_address."\n";
		$sql_fe_users = $this->_get_sql_fe_users($maxtimestamp_fe_users);
		if($this->log_active) echo "_get_sql_fe_users returns ".$sql_fe_users."\n";
		$sql_tt_address = $this->_get_sql_tt_address($maxtimestamp_tt_address);
		if($this->log_active) echo "_get_sql_tt_address returns ".$sql_tt_address."\n";
		$sql_safe_tt_address = $this->_get_sql_safe_tt_address($maxtimestamp_safe_tt_address);
		if($this->log_active) echo "_get_sql_safe_tt_address returns ".$sql_safe_tt_address."\n";
		if($this->log_active) echo "WebsiteClass.populateNow  terminated!\n";
		return $this->import_result;
	}
	
	
	private function _get_maxtimestamp_safe_tt_address() {
		global $adb,$table_prefix;
		$max_timestamp = "";
		$sql = "SELECT CASE  WHEN  MAX(".$this->temp_safettaddress.".tstamp ) IS NULL THEN 0 ELSE MAX(".$this->temp_safettaddress.".tstamp )  END as max_timestamp
				FROM ".$this->temp_safettaddress."
				WHERE tstamp is not null";
		if($this->log_active) echo "_get_maxtimestamp_safe_tt_address sql = ".$sql."\n";
		$wsresult = $adb->query($sql);
		while($row = $adb->fetchByAssoc($wsresult)) {
			$max_timestamp = $row['max_timestamp'];
		}	
		return $max_timestamp;
	}
	
	
	private function _get_maxtimestamp_tt_address() {
		global $adb,$table_prefix;
		$max_timestamp = "";
		$sql = "SELECT CASE  WHEN  MAX(".$this->temp_web.".tstamp ) IS NULL THEN 0 ELSE MAX(".$this->temp_web.".tstamp )  END as max_timestamp
				FROM ".$this->temp_web."
				WHERE tstamp is not null";
		if($this->log_active) echo "_get_maxtimestamp_tt_address sql = ".$sql."\n";
		$wsresult = $adb->query($sql);
		while($row = $adb->fetchByAssoc($wsresult)) {
			$max_timestamp = $row['max_timestamp'];
		}	
		return $max_timestamp;
	}
	
	private function _get_maxtimestamp_fe_users() {
		global $adb,$table_prefix;
		$max_timestamp = "";
		$sql = "SELECT CASE  WHEN  MAX(".$this->temp_safe.".tstamp ) IS NULL THEN 0 ELSE MAX(".$this->temp_safe.".tstamp )  END as max_timestamp
				FROM ".$this->temp_safe."
				WHERE tstamp is not null";
		if($this->log_active) echo "_get_maxtimestamp_fe_users sql = ".$sql."\n";
		$wsresult = $adb->query($sql);
		while($row = $adb->fetchByAssoc($wsresult)) {
			$max_timestamp = $row['max_timestamp'];
		}	
		return $max_timestamp;
	}
	
	
	private function _get_sql_fe_users($max_timestamp) {
		$sql = "select 
					".$this->website_tablesafe.".uid,
					".$this->website_tablesafe.".pid,
					FROM_UNIXTIME(".$this->website_tablesafe.".tstamp) as insertdate ,
					".$this->website_tablesafe.".tstamp ,
					0 as hidden,
					".$this->website_tablesafe.".name,
					".$this->website_tablesafe.".gender,
					".$this->website_tablesafe.".first_name,
					".$this->website_tablesafe.".last_name,
					".$this->website_tablesafe.".date_of_birth as birthday,
					".$this->website_tablesafe.".title,
					".$this->website_tablesafe.".email,
					".$this->website_tablesafe.".telephone as phone,
					".$this->website_tablesafe.".telephone as mobile,
					".$this->website_tablesafe.".www,
					".$this->website_tablesafe.".address,
					".$this->website_tablesafe.".company,
					".$this->website_tablesafe.".city,
					".$this->website_tablesafe.".zip,
					".$this->website_tablesafe.".zone as region,
					".$this->website_tablesafe.".static_info_country as country,
					".$this->website_tablesafe.".fax,
					null as room,
					".$this->website_tablesafe.".language as user_countries_countries, 
					safe.pages.title AS titlepage,
					safe.pages.pid as pidpage,
					safe.pages.doktype,
					usergroup
					 from ".$this->website_tablesafe."
					 JOIN safe.pages on  safe.pages.uid = ".$this->website_tablesafe.".pid and safe.pages.deleted=0
					 where
					".$this->website_tablesafe.".deleted =0 
					AND ".$this->website_tablesafe.".tstamp  > " . $max_timestamp . " order by ".$this->website_tablesafe.".uid ";
		$this->_build_insert($this->website_dbsafe, $this->website_tablesafe,$sql,$this->temp_safe);
		return $sql;
	}
	
	private function _get_sql_tt_address($max_timestamp) {
		$sql = "select 
				".$this->website_tableweb.".uid,
				".$this->website_tableweb.".pid,
				FROM_UNIXTIME(".$this->website_tableweb.".tstamp) as insertdate ,
				".$this->website_tableweb.".tstamp ,
				".$this->website_tableweb.".hidden,
				".$this->website_tableweb.".name,
				".$this->website_tableweb.".gender,
				".$this->website_tableweb.".first_name,
				".$this->website_tableweb.".last_name,
				".$this->website_tableweb.".birthday,
				".$this->website_tableweb.".title,
				".$this->website_tableweb.".email,
				".$this->website_tableweb.".phone,
				".$this->website_tableweb.".mobile,
				".$this->website_tableweb.".www,
				".$this->website_tableweb.".address,
				".$this->website_tableweb.".company,
				".$this->website_tableweb.".city,
				".$this->website_tableweb.".zip,
				".$this->website_tableweb.".region,
				".$this->website_tableweb.".country,
				".$this->website_tableweb.".fax,
				".$this->website_tableweb.".description,
				".$this->website_tableweb.".room, 
				".$this->website_tableweb.".user_countries_countries, 
				pages.title AS titlepage,
				pages.pid as pidpage,
				pages.doktype
				from
				".$this->website_tableweb."
				JOIN pages on pages.uid = ".$this->website_tableweb.".pid and pages.deleted=0
				where ".$this->website_tableweb.".deleted=0
				and ".$this->website_tableweb.".tstamp  > " . $max_timestamp . "  order by ".$this->website_tableweb.".uid ";
		$this->_build_insert($this->website_dbweb, $this->website_tableweb,$sql,$this->temp_web);
		return $sql;
	}
	
	private function _get_sql_safe_tt_address($max_timestamp) {
		$sql = "select 
				".$this->website_safettaddress.".uid,
				".$this->website_safettaddress.".pid,
				FROM_UNIXTIME(".$this->website_safettaddress.".tstamp) as insertdate ,
				".$this->website_safettaddress.".tstamp ,
				".$this->website_safettaddress.".hidden,
				".$this->website_safettaddress.".name,
				".$this->website_safettaddress.".gender,
				".$this->website_safettaddress.".first_name,
				".$this->website_safettaddress.".last_name,
				".$this->website_safettaddress.".birthday,
				".$this->website_safettaddress.".title,
				".$this->website_safettaddress.".email,
				".$this->website_safettaddress.".phone,
				".$this->website_safettaddress.".mobile,
				".$this->website_safettaddress.".www,
				".$this->website_safettaddress.".address,
				".$this->website_safettaddress.".company,
				".$this->website_safettaddress.".city,
				".$this->website_safettaddress.".zip,
				".$this->website_safettaddress.".region,
				".$this->website_safettaddress.".country,
				".$this->website_safettaddress.".fax,
				".$this->website_safettaddress.".description,
				".$this->website_safettaddress.".room, 
				pages.title AS titlepage,
				pages.pid as pidpage,
				pages.doktype
				from
				".$this->website_safettaddress."
				JOIN pages on pages.uid = ".$this->website_safettaddress.".pid and pages.deleted=0
				where ".$this->website_safettaddress.".deleted=0
				and ".$this->website_safettaddress.".tstamp  > " . $max_timestamp . "  order by ".$this->website_safettaddress.".uid ";
		$this->_build_insert($this->website_dbsafe, $this->website_safettaddress,$sql,$this->temp_safettaddress);
		return $sql;
	}
	
	
	private function _build_insert($website_db, $source_table,$sql,$temp_table) {
		$parm_array = array();
		$retsql = "INSERT INTO " . $temp_table;
		$retsql .= " (".implode(", ", array_keys($this->mapping[$source_table])).")";
		$retsql .= " VALUES ";
		$conn = mysql_connect($this->website_host, $this->website_usr, $this->website_pwd);
		mysql_select_db($website_db);
		$wsresult = mysql_query($sql);
		$values_array = array();
		while($row = mysql_fetch_assoc($wsresult)) {
			$val_array = array();
			// CASO DI fe_users
			if( $source_table == $this->website_tablesafe )
			{
				$group_sql = "SELECT title from fe_groups WHERE FIND_IN_SET(fe_groups.uid,'".$row['usergroup']."') >0";
				$group_result = mysql_query($group_sql);
				$group_descr="";
				while($grp_row = mysql_fetch_assoc($group_result)) {
					$group_descr = $group_descr . " " . $grp_row["title"];
					if($this->log_active) echo "WebsiteClass._build_insert found usergroup = ".$grp_row["title"]." !\n";
				}
				$row['usergroup_descr'] = $group_descr;
			}
			foreach($this->mapping[$source_table] as $key=>$value) {
				$val_array[] = $row[$value];
			}
			global $adb;
			$insert_sql = $retsql . " ('".implode("', '", $val_array)."')";
			$adb->query($insert_sql );			
			$this->import_result['records_created']++;
		}
		mysql_free_result($wsresult);
	}
	
	private function _set_mappings() {
		$this->mapping['tt_address']['uid'] = 'uid';
		$this->mapping['tt_address']['pid'] = 'pid';
		$this->mapping['tt_address']['insertdate'] = 'insertdate';
		$this->mapping['tt_address']['tstamp'] = 'tstamp';
		$this->mapping['tt_address']['hidden'] = 'hidden';
		$this->mapping['tt_address']['name'] = 'name';
		$this->mapping['tt_address']['gender'] = 'gender';
		$this->mapping['tt_address']['first_name'] = 'first_name';
		$this->mapping['tt_address']['last_name'] = 'last_name';
		$this->mapping['tt_address']['birthday'] = 'birthday';
		$this->mapping['tt_address']['title'] = 'title';
		$this->mapping['tt_address']['email'] = 'email';
		$this->mapping['tt_address']['phone'] = 'phone';
		$this->mapping['tt_address']['mobile'] = 'mobile';
		$this->mapping['tt_address']['www'] = 'www';
		$this->mapping['tt_address']['address'] = 'address';
		$this->mapping['tt_address']['company'] = 'company';
		$this->mapping['tt_address']['city'] = 'city';
		$this->mapping['tt_address']['zip'] = 'zip';
		$this->mapping['tt_address']['region'] = 'region';
		$this->mapping['tt_address']['country'] = 'country';
		$this->mapping['tt_address']['fax'] = 'fax';
		$this->mapping['tt_address']['description'] = 'description';
		$this->mapping['tt_address']['room'] = 'room';
		$this->mapping['tt_address']['user_countries_countries'] = 'user_countries_countries';
		$this->mapping['tt_address']['pagetitle'] = 'titlepage';
		$this->mapping['tt_address']['pagepid'] = 'pidpage';
		$this->mapping['tt_address']['doktype'] = 'doktype';
		$this->mapping['tt_address']['jobinsertdate'] = 'jobinsertdate';
		// FE USERS
		$this->mapping['fe_users']['uid'] = 'uid';
		$this->mapping['fe_users']['pid'] = 'pid';
		$this->mapping['fe_users']['insertdate'] = 'insertdate';
		$this->mapping['fe_users']['tstamp'] = 'tstamp';
		$this->mapping['fe_users']['hidden'] = 'hidden';
		$this->mapping['fe_users']['name'] = 'name';
		$this->mapping['fe_users']['gender'] = 'gender';
		$this->mapping['fe_users']['first_name'] = 'first_name';
		$this->mapping['fe_users']['last_name'] = 'last_name';
		$this->mapping['fe_users']['birthday'] = 'date_of_birth';
		$this->mapping['fe_users']['title'] = 'title';
		$this->mapping['fe_users']['email'] = 'email';
		$this->mapping['fe_users']['phone'] = 'telephone';
		$this->mapping['fe_users']['mobile'] = 'telephone1';
		$this->mapping['fe_users']['www'] = 'www';
		$this->mapping['fe_users']['address'] = 'address';
		$this->mapping['fe_users']['company'] = 'company';
		$this->mapping['fe_users']['city'] = 'city';
		$this->mapping['fe_users']['zip'] = 'zip';
		$this->mapping['fe_users']['region'] = 'region';
		$this->mapping['fe_users']['country'] = 'country';
		$this->mapping['fe_users']['fax'] = 'fax';
		$this->mapping['fe_users']['description'] = 'description';
		$this->mapping['fe_users']['room'] = 'room';
		$this->mapping['fe_users']['user_countries_countries'] = 'user_countries_countries';
		$this->mapping['fe_users']['pagetitle'] = 'titlepage';
		$this->mapping['fe_users']['pagepid'] = 'pidpage';
		$this->mapping['fe_users']['doktype'] = 'doktype';
		$this->mapping['fe_users']['jobinsertdate'] = 'jobinsertdate';
		$this->mapping['fe_users']['language'] = 'language';
		$this->mapping['fe_users']['usergroup'] = 'usergroup';
		$this->mapping['fe_users']['usergroup_descr'] = 'usergroup_descr';
	}
}

?>
