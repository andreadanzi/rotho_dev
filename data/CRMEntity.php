<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
/*********************************************************************************
 * $Header: /advent/projects/wesat/vtiger_crm/vtigercrm/data/CRMEntity.php,v 1.16 2005/04/29 04:21:31 mickie Exp $
 * Description:  Defines the base class for all data entities used throughout the
 * application.  The base class including its methods and variables is designed to
 * be overloaded with module-specific methods and variables particular to the
 * module's base entity class.
 ********************************************************************************/

include_once('config.php');
require_once('include/logging.php');
require_once('data/Tracker.php');
require_once('include/utils/utils.php');
require_once('include/utils/UserInfoUtil.php');
require_once("include/Zend/Json.php");
//crmv@33801
if (file_exists('modules/SDK/src/VTEEntity.php')) {
	require_once('modules/SDK/src/VTEEntity.php');
}
if (!class_exists('VTEEntity')) {
	class VTEEntity {}
}
class CRMEntity extends VTEEntity
//crmv@33801e
{
  var $ownedby;

  /**
   * Detect if we are in bulk save mode, where some features can be turned-off
   * to improve performance.
   */
  static function isBulkSaveMode() {
  	global $VTIGER_BULK_SAVE_MODE;
  	if (isset($VTIGER_BULK_SAVE_MODE) && $VTIGER_BULK_SAVE_MODE) {
  		return true;
  	}
  	return false;
  }
  //crmv@31355 
  static function getInstance($module) {
  	$modName = $module;
    if ($module == 'Calendar' || $module == 'Events' || $module == 'Activity') {
  		$modName = 'Activity';
  	}
  	//crmv@sdk-24185
  	// File access security check
  	$sdkClass = SDK::getClass($modName);
  	if (!empty($sdkClass)) {
  		if (!class_exists($sdkClass['module'])) {
  			checkFileAccess($sdkClass['src']);
  			require_once($sdkClass['src']);
  		}
  		$modName = $sdkClass['module'];
  	} elseif(!class_exists($modName)) {
  		if ($module == 'Calendar' || $module == 'Events' || $module == 'Activity') {
  			$module = 'Calendar';
  		}
  		checkFileAccess("modules/$module/$modName.php");
  		require_once("modules/$module/$modName.php");
  	}
  	//crmv@sdk-24185e
  	$focus = new $modName();
	return $focus;
  }
  //crmv@31355e

  function saveentity($module,$fileid='',$longdesc=false)	//crmv@16877
  {
	global $current_user, $adb, $table_prefix;//$adb added by raju for mass mailing
	$insertion_mode = $this->mode;

	$this->db->println("TRANS saveentity starts $module");
	$this->db->startTransaction();


	foreach($this->tab_name as $table_name)
	{

		if($table_name == $table_prefix."_crmentity")
		{
			$this->insertIntoCrmEntity($module,$fileid,$longdesc);
		}
		else
		{
			$this->insertIntoEntityTable($table_name, $module,$fileid);
		}
	}
	//Calling the Module specific save code
	$this->save_module($module);

	$this->db->completeTransaction();
    $this->db->println("TRANS saveentity ends");
     // vtlib customization: Hook provide to enable generic module relation.

  	// Ticket 6386 fix
	global $singlepane_view;

	if($_REQUEST['return_action'] == 'CallRelatedList' ||
		(isset($singlepane_view) && $singlepane_view == true &&
			$_REQUEST['return_action'] == 'DetailView' &&
				!empty($_REQUEST['return_module'])
				&& !empty($_REQUEST['return_id']) //crmv@fix oracle
				)){
		$for_module = vtlib_purify($_REQUEST['return_module']);
		$for_crmid  = vtlib_purify($_REQUEST['return_id']);

		$on_focus = CRMEntity::getInstance($for_module);
		// Do conditional check && call only for Custom Module at present
		// TODO: $on_focus->IsCustomModule is not required if save_related_module function
		// is used for core modules as well.
		//crmv@22700
		//if($on_focus->IsCustomModule && method_exists($on_focus, 'save_related_module')) {
		if(method_exists($on_focus, 'save_related_module')) {
		//crmv@22700e
			$with_module = $module;
			$with_crmid = $this->id;
			$on_focus->save_related_module(
				$for_module, $for_crmid, $with_module, $with_crmid);
		}
	}
  }



	function insertIntoAttachment1($id,$module,$filedata,$filename,$filesize,$filetype,$user_id)
	{
		$date_var = date('YmdHis');
		global $current_user;
		global $adb, $table_prefix;
		//global $root_directory;
		global $log;

		$ownerid = $user_id;

		if($filesize != 0)
		{
			$data = base64_encode(fread(fopen($filedata, "r"), $filesize));
		}

		$current_id = $adb->getUniqueID($table_prefix."_crmentity");

		if($module=='Emails')
		{
			$log->info("module is ".$module);
			$idname='emailid';      $tablename=$table_prefix.'_emails';    $descname='description';
		}
		else
		{
			$idname='notesid';      $tablename=$table_prefix.'_notes';     $descname='notecontent';
		}

		$sql="update $tablename set filename=? where $idname=?";
		$params = array($filename, $id);
		$adb->pquery($sql, $params);

		$sql1 = "insert into {$table_prefix}_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?, ?, ?, ?, ?, ?, ?)";
		$params1 = array($current_id, $current_user->id, $ownerid, $module." Attachment", '', $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
		$adb->pquery($sql1, $params1);

		$sql2="insert into {$table_prefix}_attachments(attachmentsid, name, description, type) values(?, ?, ?, ?)";
		$params2 = array($current_id, $filename, '', $filetype);
		$result=$adb->pquery($sql2, $params2);

		//TODO -- instead of put contents in db now we should store the file in harddisk

		$sql3='insert into '.$table_prefix.'_seattachmentsrel values(?, ?)';
		$params3 = array($id, $current_id);
		$adb->pquery($sql3, $params3);
	}



	/**
	 *      This function is used to upload the attachment in the server and save that attachment information in db.
	 *      @param int $id  - entity id to which the file to be uploaded
	 *      @param string $module  - the current module name
	 *      @param array $file_details  - array which contains the file information(name, type, size, tmp_name and error)
	 *      return void
	*/
	function uploadAndSaveFile($id,$module,$file_details,$copy=false)	//crmv@22123
	{
		global $log;
		$log->debug("Entering into uploadAndSaveFile($id,$module,$file_details) method.");

		global $adb, $current_user,$table_prefix;
		global $upload_badext;
		//crmv@15369
		$date_var = date('Y-m-d H:i:s');
		//crmv@15369 end

		//to get the owner id
		$ownerid = $this->column_fields['assigned_user_id'];
		if(!isset($ownerid) || $ownerid=='')
			$ownerid = $current_user->id;

		if(isset($file_details['original_name']) && $file_details['original_name'] != null) {
			$file_name = $file_details['original_name'];
		} else {
			$file_name = $file_details['name'];
		}

		// Arbitrary File Upload Vulnerability fix - Philip
		$binFile = preg_replace('/\s+/', '_', $file_name);//replace space with _ in filename
		$ext_pos = strrpos($binFile, ".");

		$ext = substr($binFile, $ext_pos + 1);

		if (in_array($ext, $upload_badext))
		{
			$binFile .= ".txt";
		}
		// Vulnerability fix ends
		$binFile = correctEncoding($binFile);	//crmv@25554

		$current_id = $adb->getUniqueID($table_prefix."_crmentity");

		$filename = ltrim(basename(" ".$binFile)); //allowed filename like UTF-8 characters
		$filetype= $file_details['type'];
		$filesize = $file_details['size'];
		$filetmp_name = $file_details['tmp_name'];

		//get the file path inwhich folder we want to upload the file
		$upload_file_path = decideFilePath();

		//upload the file in server
		//crmv@22123
		if ($copy)
			$upload_status = copy($filetmp_name,$upload_file_path.$current_id."_".$binFile);
		else
		//crmv@22123e
			$upload_status = move_uploaded_file($filetmp_name,$upload_file_path.$current_id."_".$binFile);

		$save_file = 'true';
		//only images are allowed for these modules
		if($module == 'Contacts' || $module == 'Products')
		{
			$save_file = validateImageFile($file_details);
		}

		if($save_file == 'true' && $upload_status == 'true')
		{
			//This is only to update the attached filename in the vtiger_notes vtiger_table for the Documents module
			if($module=='Documents')
			{
				$sql="update ".$table_prefix."_notes set filename=? where notesid = ?";
				$params = array($filename, $id);
				$adb->pquery($sql, $params);
			}
			if($module == 'Contacts' || $module == 'Products')
			{
				$sql1 = "insert into ".$table_prefix."_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?, ?, ?, ?, ?, ?, ?)";
				$params1 = array($current_id, $current_user->id, $ownerid, $module." Image", $this->column_fields['description'], $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
			}
			else
			{
				$sql1 = "insert into ".$table_prefix."_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?, ?, ?, ?, ?, ?, ?)";
				$params1 = array($current_id, $current_user->id, $ownerid, $module." Attachment", $this->column_fields['description'], $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
			}
			$adb->pquery($sql1, $params1);

			$sql2="insert into ".$table_prefix."_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)";
			$params2 = array($current_id, $filename, $this->column_fields['description'], $filetype, $upload_file_path);
			$result=$adb->pquery($sql2, $params2);

			if($_REQUEST['mode'] == 'edit')
			{
				if($id != '' && $_REQUEST['fileid'] != '')
				{
					$delquery = 'delete from '.$table_prefix.'_seattachmentsrel where crmid = ? and attachmentsid = ?';
					$delparams = array($id, $_REQUEST['fileid']);
					$adb->pquery($delquery, $delparams);
				}
			}
			if($module == 'Documents')
			{
				$query = "delete from ".$table_prefix."_seattachmentsrel where crmid = ?";
				$qparams = array($id);
				$adb->pquery($query, $qparams);
			}
			if($module == 'Contacts')
			{
				$att_sql="select ".$table_prefix."_seattachmentsrel.attachmentsid  from ".$table_prefix."_seattachmentsrel inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=".$table_prefix."_seattachmentsrel.attachmentsid where ".$table_prefix."_crmentity.setype='Contacts Image' and ".$table_prefix."_seattachmentsrel.crmid=?";
				$res=$adb->pquery($att_sql, array($id));
				$attachmentsid= $adb->query_result($res,0,'attachmentsid');
				if($attachmentsid !='' )
				{
					$delquery='delete from '.$table_prefix.'_seattachmentsrel where crmid=? && attachmentsid=?';
					$adb->pquery($delquery, array($id, $attachmentsid));
					$crm_delquery="delete from ".$table_prefix."_crmentity where crmid=?";
					$adb->pquery($crm_delquery, array($attachmentsid));
					$sql5='insert into '.$table_prefix.'_seattachmentsrel values(?,?)';
					$adb->pquery($sql5, array($id, $current_id));
				}
				else
				{
					$sql3='insert into '.$table_prefix.'_seattachmentsrel values(?,?)';
					$adb->pquery($sql3, array($id, $current_id));
				}
			}
			else
			{
				$sql3='insert into '.$table_prefix.'_seattachmentsrel values(?,?)';
				$adb->pquery($sql3, array($id, $current_id));
			}

			return true;
		}
		else
		{
			$log->debug("Skip the save attachment process.");
			return false;
		}
	}

	/** Function to insert values in the vtiger_crmentity for the specified module
  	  * @param $module -- module:: Type varchar
 	 */

  function insertIntoCrmEntity($module,$fileid='',$longdesc=false)	//crmv@16877
  {
	global $adb,$table_prefix;
	global $current_user;
	global $log;

	if($fileid != '')
	{
		$this->id = $fileid;
		$this->mode = 'edit';
	}

	$date_var = date('Y-m-d H:i:s');

	$ownerid = $this->column_fields['assigned_user_id'];
	$sql="select ownedby from ".$table_prefix."_tab where name=?";
	$res=$adb->pquery($sql, array($module));
	$this->ownedby = $adb->query_result($res,0,'ownedby');

	if($this->ownedby == 1)
	{
		$log->info("module is =".$module);
		$ownerid = $current_user->id;
	}
	// Asha - Change ownerid from '' to null since its an integer field.
	// It is empty for modules like Invoice/Quotes/SO/PO which do not have Assigned to field
	if($ownerid === '') $ownerid = 0;
	
	if ($ownerid == 0) {
		$ownerid = $current_user->id;
		require_once('modules/Emails/mail.php');
		//$mail_status = send_mail('HelpDesk','supporto@crmvillage.biz','RothoBlaas','admin@rothoblaas.com','Assegnatario vuoto','Salvata azienda '.$this->id.' con assegnatario vuoto');
		}

	if($module == 'Events')
	{
		$module = 'Calendar';
	}
	if($this->mode == 'edit')
	{
		$description_val = from_html($this->column_fields['description'],($insertion_mode == 'edit')?true:false);

		require('user_privileges/user_privileges_'.$current_user->id.'.php');
		$tabid = getTabid($module);
		if($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] ==0)
		{
			if ($longdesc){
				$sql = "update ".$table_prefix."_crmentity set smownerid=?,modifiedby=?,modifiedtime=? where crmid=?";
				$params = array($ownerid, $current_user->id, $adb->formatDate($date_var, true), $this->id);
				$updatedescwhere = 	"crmid=$this->id";
			}
			else {
				$sql = "update ".$table_prefix."_crmentity set smownerid=?,modifiedby=?,description=?, modifiedtime=? where crmid=?";
				$params = array($ownerid, $current_user->id, $description_val, $adb->formatDate($date_var, true), $this->id);
			}
		}
		else
		{
			$profileList = getCurrentUserProfileList();
			$perm_qry = "SELECT columnname FROM ".$table_prefix."_field INNER JOIN ".$table_prefix."_def_org_field ON ".$table_prefix."_def_org_field.fieldid = ".$table_prefix."_field.fieldid WHERE ".$table_prefix."_field.tabid = ? AND ".$table_prefix."_def_org_field.visible = 0 and ".$table_prefix."_field.tablename='".$table_prefix."_crmentity' and ".$table_prefix."_field.displaytype in (1,3) and ".$table_prefix."_field.presence in (0,2) ";
 			$perm_qry.=" AND EXISTS(SELECT * FROM ".$table_prefix."_profile2field WHERE ".$table_prefix."_profile2field.fieldid = ".$table_prefix."_field.fieldid AND ".$table_prefix."_profile2field.profileid IN (". generateQuestionMarks($profileList) .") AND ".$table_prefix."_profile2field.visible = 0) ";
			$perm_result = $adb->pquery($perm_qry, array($tabid, $profileList));
			$perm_rows = $adb->num_rows($perm_result);
			for($i=0; $i<$perm_rows; $i++)
			{
				$columname[]=$adb->query_result($perm_result,$i,"columnname");
			}
			if(is_array($columname) && in_array("description",$columname))
			{
				if ($longdesc){
					$sql = "update ".$table_prefix."_crmentity set smownerid=?,modifiedby=?,modifiedtime=? where crmid=?";
					$params = array($ownerid, $current_user->id, $adb->formatDate($date_var, true), $this->id);
					$updatedescwhere = 	"crmid=$this->id";
				}
				else {
					$sql = "update ".$table_prefix."_crmentity set smownerid=?,modifiedby=?,description=?, modifiedtime=? where crmid=?";
					$params = array($ownerid, $current_user->id, $description_val, $adb->formatDate($date_var, true), $this->id);
				}
			}
			else
			{
				$sql = "update ".$table_prefix."_crmentity set smownerid=?,modifiedby=?, modifiedtime=? where crmid=?";
				$params = array($ownerid, $current_user->id, $adb->formatDate($date_var, true), $this->id);
			}
		}
		$adb->pquery($sql, $params);
		if ($longdesc)
			$adb->updateClob($table_prefix.'_crmentity','description',$updatedescwhere,$description_val);
		$sql1 ="delete from ".$table_prefix."_ownernotify where crmid=?";
		$params1 = array($this->id);
		$adb->pquery($sql1, $params1);
		if($ownerid != $current_user->id)
		{
			$sql1 = "insert into ".$table_prefix."_ownernotify values(?,?,?)";
			$params1 = array($this->id, $ownerid, null);
			$adb->pquery($sql1, $params1);
		}
	}
	else
	{
		//if this is the create mode and the group allocation is chosen, then do the following
		//crmv@offline
		if (vtlib_isModuleActive('Offline') !== false && $this->force_id){
			$current_id = $this->force_id;
		}
		else{
			$current_id = $adb->getUniqueID($table_prefix."_crmentity");
		}
		//crmv@offline end
		$_REQUEST['currentid']=$current_id;
		if($current_user->id == '')
			$current_user->id = 0;

		$description_val = from_html($this->column_fields['description'],($insertion_mode == 'edit')?true:false);
		//crmv@fix description
		if ($longdesc){
			$sql = "insert into ".$table_prefix."_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?,?,?,?,".$adb->getEmptyClob(true).",?,?)";
			$params = array($current_id, $current_user->id, $ownerid, $module, $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
			$adb->pquery($sql, $params);
			$adb->updateClob($table_prefix.'_crmentity','description',"crmid=$current_id",$description_val);
		}
		else {
			$sql = "insert into ".$table_prefix."_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?,?,?,?,?,?,?)";
			$params = array($current_id, $current_user->id, $ownerid, $module, $description_val, $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
			$adb->pquery($sql, $params);
		}
		//crmv@fix description end
		$this->id = $current_id;
	}
   }


	/** Function to insert values in the specifed table for the specified module
  	  * @param $table_name -- table name:: Type varchar
  	  * @param $module -- module:: Type varchar
 	 */
  function insertIntoEntityTable($table_name, $module, $fileid='')
  {
	  global $log,$table_prefix;
  	  global $current_user,$app_strings;
	   $log->info("function insertIntoEntityTable ".$module.$table_prefix.'_table name ' .$table_name);
	  global $adb;
	  $insertion_mode = $this->mode;

	  //Checkin whether an entry is already is present in the vtiger_table to update
	  if($insertion_mode == 'edit')
	  {
	  	  $tablekey = $this->tab_name_index[$table_name];
	  	  // Make selection on the primary key of the module table to check.
		  $check_query = "select $tablekey from $table_name where $tablekey=?";
		  $check_result=$adb->pquery($check_query, array($this->id));

		  $num_rows = $adb->num_rows($check_result);

		  if($num_rows <= 0)
		  {
			  $insertion_mode = '';
		  }
	  }

	$tabid= getTabid($module);
  	if($module == 'Calendar' && $this->column_fields["activitytype"] != null && $this->column_fields["activitytype"] != 'Task') {
    	$tabid = getTabid('Events');
  	}
	  if($insertion_mode == 'edit')
	  {
		  $update = array();
		  $update_params = array();
		  require('user_privileges/user_privileges_'.$current_user->id.'.php');
		  if($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] ==0)
		  {
				$sql = "select * from ".$table_prefix."_field where tabid in (". generateQuestionMarks($tabid) .") and tablename=? and displaytype in (1,3) and presence in (0,2)";
				$params = array($tabid, $table_name);
		  }
		  else
		  {
			  $profileList = getCurrentUserProfileList();

			  if (count($profileList) > 0) {
			  	$sql = "SELECT *
			  			FROM ".$table_prefix."_field
			  			INNER JOIN ".$table_prefix."_def_org_field
			  			ON ".$table_prefix."_def_org_field.fieldid = ".$table_prefix."_field.fieldid
			  			WHERE ".$table_prefix."_field.tabid = ?
			  			AND ".$table_prefix."_def_org_field.visible = 0 and ".$table_prefix."_field.tablename=? and ".$table_prefix."_field.displaytype in (1,3) and ".$table_prefix."_field.presence in (0,2)";
 				$sql.=" AND EXISTS(SELECT * FROM ".$table_prefix."_profile2field WHERE ".$table_prefix."_profile2field.fieldid = ".$table_prefix."_field.fieldid AND ".$table_prefix."_profile2field.profileid IN (". generateQuestionMarks($profileList) .") AND ".$table_prefix."_profile2field.visible = 0) ";

			  	$params = array($tabid,$table_name,$profileList);
			  } else {
			  	$sql = "SELECT *
			  			FROM ".$table_prefix."_field
			  			INNER JOIN ".$table_prefix."_def_org_field
			  			ON ".$table_prefix."_def_org_field.fieldid = ".$table_prefix."_field.fieldid
			  			WHERE ".$table_prefix."_field.tabid = ?
			  			AND ".$table_prefix."_def_org_field.visible = 0 and ".$table_prefix."_field.tablename=? and ".$table_prefix."_field.displaytype in (1,3) and ".$table_prefix."_field.presence in (0,2)";
 				$sql.=" AND EXISTS(SELECT * FROM ".$table_prefix."_profile2field WHERE ".$table_prefix."_profile2field.fieldid = ".$table_prefix."_field.fieldid AND ".$table_prefix."_profile2field.visible = 0) ";
				$params = array($tabid, $table_name);
			  }
		  }

	  }
	  else
	  {
		  $table_index_column = $this->tab_name_index[$table_name];
		  if($table_index_column == 'id' && $table_name == $table_prefix.'_users')
		  {
		 	$currentuser_id = $adb->getUniqueID($table_prefix."_users");
			$this->id = $currentuser_id;
		  }
		  $column = array($table_index_column);
		  $value = array($this->id);
		  $sql = "select * from ".$table_prefix."_field where tabid=? and tablename=? and displaytype in (1,3,4) and ".$table_prefix."_field.presence in (0,2)";
		  $params = array($tabid, $table_name);
	  }

	  $result = $adb->pquery($sql, $params);
	  $noofrows = $adb->num_rows($result);
	  for($i=0; $i<$noofrows; $i++) {
		$fieldname=$adb->query_result($result,$i,"fieldname");
		$columname=$adb->query_result($result,$i,"columnname");
		$uitype=$adb->query_result($result,$i,"uitype");
		$generatedtype=$adb->query_result($result,$i,"generatedtype");
		$typeofdata=$adb->query_result($result,$i,"typeofdata");
		$typeofdata_array = explode("~",$typeofdata);
		$datatype = $typeofdata_array[0];

		if($uitype == 4 && $insertion_mode != 'edit') {
			$this->column_fields[$fieldname] = $this->setModuleSeqNumber("increment",$module);
			$fldvalue = $this->column_fields[$fieldname];
		}
		  if(isset($this->column_fields[$fieldname]))
		  {
		  	//crmv@sdk-18509	//crmv@25963
		  	if(SDK::isUitype($uitype))
		  	{
		  		$fldvalue = $this->column_fields[$fieldname];
		  		$sdk_file = SDK::getUitypeFile('php','insert',$uitype);
		  		if ($sdk_file != '') {
		  			include($sdk_file);
		  		}
		  	}
		  	//crmv@sdk-18509 e	//crmv@25963e
			  elseif($uitype == 56)
			  {
				  if($this->column_fields[$fieldname] == 'on' || $this->column_fields[$fieldname] == 1)
				  {
					  $fldvalue = '1';
				  }
				  else
				  {
					  $fldvalue = '0';
				  }

			  }
			  elseif($uitype == 15 || $uitype == 16)
			  {

				  if($this->column_fields[$fieldname] == $app_strings['LBL_NOT_ACCESSIBLE'])
				  {

					//If the value in the request is Not Accessible for a picklist, the existing value will be replaced instead of Not Accessible value.
					 $sql="select $columname from  $table_name where ".$this->tab_name_index[$table_name]."=?";
					 $res = $adb->pquery($sql,array($this->id));
					 $pick_val = $adb->query_result($res,0,$columname);
					 $fldvalue = $pick_val;
				  }
				  else
				  {
					  $fldvalue = $this->column_fields[$fieldname];
				   }
			  }
			  elseif($uitype == 33)
			  {
  				if(is_array($this->column_fields[$fieldname]))
  				{
  				  $field_list = implode(' |##| ',$this->column_fields[$fieldname]);
  				}else
  				{
  				  $field_list = $this->column_fields[$fieldname];
          		}
  				$fldvalue = $field_list;
			  }
			  elseif($uitype == 5 || $uitype == 6 || $uitype ==23)
			  {
				  if($_REQUEST['action'] == 'Import')
				  {
					  $fldvalue = $this->column_fields[$fieldname];
				  }
				  else
				  {
					  //Added to avoid function call getDBInsertDateValue in ajax save
					  if (isset($current_user->date_format)) {
							$fldvalue = getValidDBInsertDateValue($this->column_fields[$fieldname]);
					  } else {
							$fldvalue = $this->column_fields[$fieldname];
					  }
					  // crmv@25610
					  if ($module == 'Calendar' && $fieldname == 'date_start') {
					  	$dtstart = $this->column_fields['date_start'];
					  	if (isset($current_user->date_format)) {
					  		$dtstart = getValidDBInsertDateValue($dtstart);
					  	}
					  	$newval = $dtstart.' '.$this->column_fields['time_start'];
					  	$newval = adjustTimezone($newval, -$current_user->timezonediff);
					  	$fldvalue = substr($newval, 0, 10);
					  } elseif ($module == 'Calendar' &&  $fieldname == 'due_date') {
					  	$dtend = $this->column_fields['due_date'];
					  	if (isset($current_user->date_format)) {
					  		$dtend = getValidDBInsertDateValue($dtend);
					  	}
					  	$newval = $dtend.' '.$this->column_fields['time_end'];
					  	$newval = adjustTimezone($newval, -$current_user->timezonediff);
					  	$fldvalue = substr($newval, 0, 10);
					  } else {
					  	$fldvalue = adjustTimezone($fldvalue, -$current_user->timezonediff);
					  }
					  // crmv@25610e
				  }
			  }
			  // crmv@25610
			  elseif($uitype == 70)
			  {
			  	$fldvalue = adjustTimezone($this->column_fields[$fieldname], -$current_user->timezonediff);
			  }
			  // crmv@25610e
			  elseif($uitype == 7)
			  {
				  //strip out the spaces and commas in numbers if given ie., in amounts there may be ,
				  $fldvalue = str_replace(",","",$this->column_fields[$fieldname]);//trim($this->column_fields[$fieldname],",");

			  }
				elseif($uitype == 26) {
					if(empty($this->column_fields[$fieldname])) {
						$fldvalue = 1; //the documents will stored in default folder
					}else {
						$fldvalue = $this->column_fields[$fieldname];
					}
			  }
			  elseif($uitype == 28){
			  		if($this->column_fields[$fieldname] == null){
				  		$fileQuery = $adb->pquery("SELECT filename from ".$table_prefix."_notes WHERE notesid = ?",array($this->id));
				  		$fldvalue = null;
				  		if(isset($fileQuery)){
							$rowCount = $adb->num_rows($fileQuery);
							if($rowCount > 0){
								$fldvalue = $adb->query_result($fileQuery,0,'filename');
							}
						}
			  		}else {
			  			$fldvalue = $this->column_fields[$fieldname];
			  		}
			  }elseif($uitype == 8) {
			  	$this->column_fields[$fieldname] = rtrim($this->column_fields[$fieldname],',');
				$ids = explode(',',$this->column_fields[$fieldname]);
				$json = new Zend_Json();
				$fldvalue = $json->encode($ids);
			}elseif($uitype == 12) {
				//crmv@22700
				if ($fieldname == 'from_email' && $this->column_fields[$fieldname] != '') {
					$fldvalue = $this->column_fields[$fieldname];
				} else {
				//crmv@22700
				  	$query = "SELECT email1 FROM ".$table_prefix."_users WHERE id = ?";
				  	$res = $adb->pquery($query,array($current_user->id));
				  	$rows = $adb->num_rows($res);
				  	if($rows > 0) {
				  		$fldvalue = $adb->query_result($res,0,'email1');
					}
				}	//crmv@22700
			}elseif($uitype == 71 && $generatedtype == 2) { // Convert currency to base currency value before saving for custom fields of type currency
				$currency_id = $current_user->currency_id;
				$curSymCrate = getCurrencySymbolandCRate($currency_id);
				$fldvalue = convertToDollar($this->column_fields[$fieldname], $curSymCrate['rate']);
			//crmv@16265
			} elseif($uitype == 199) {
				$fldvalue = Users::changepassword($this->column_fields[$fieldname]);
			//crmv@16265e
			} else {
				$fldvalue = $this->column_fields[$fieldname];
			}

			//crmv@25610
			if ($module == 'Calendar' && !empty($fldvalue) && in_array($fieldname, array('time_start', 'time_end'))) {
				$fldvalue = adjustTimezone($fldvalue, -$current_user->timezonediff);
				// strip the date (if the date is different, there's a problem)
				if (strlen($fldvalue) > 5) {
					$fldvalue = substr($fldvalue, -8, 5);
				}
			}
			//crmv@25610e

			if($uitype != 33 && $uitype !=8)
				$fldvalue = from_html($fldvalue,($insertion_mode == 'edit')?true:false);
		  }
		  else
		  {
			  $fldvalue = '';
		  }
		  if($fldvalue == '') {
		  	$fldvalue = $this->get_column_value($columname, $fldvalue, $fieldname, $uitype, $datatype);
		  }

		if($insertion_mode == 'edit') {
			if($table_name != $table_prefix.'_ticketcomments' && $uitype != 4) {
				array_push($update, $columname."=?");
				array_push($update_params, $fldvalue);
			}
		} else {
			array_push($column, $columname);
			array_push($value, $fldvalue);
		}

	  }

	  if($insertion_mode == 'edit')
	  {
		  if($_REQUEST['module'] == 'Potentials')
		  {
			  $dbquery = 'select sales_stage from '.$table_prefix.'_potential where potentialid = ?';
			  $sales_stage = $adb->query_result($adb->pquery($dbquery, array($this->id)),0,'sales_stage');
			  if($sales_stage != $_REQUEST['sales_stage'] && $_REQUEST['sales_stage'] != '')
			  {
				  $date_var = date('Y-m-d H:i:s');
				  $closingdate = ($_REQUEST['ajxaction'] == 'DETAILVIEW')? $this->column_fields['closingdate'] : getDBInsertDateValue($this->column_fields['closingdate']);
				  $histid = $adb->getUniqueID($table_prefix.'_potstagehistory');
				  $sql = "insert into ".$table_prefix."_potstagehistory values(?,?,?,?,?,?,?,?)";
				  $params = array($histid, $this->id, $this->column_fields['amount'], decode_html($sales_stage), $this->column_fields['probability'], 0, $adb->formatDate($closingdate, true), $adb->formatDate($date_var, true));
				  $adb->pquery($sql, $params);
			  }
		  }
		  elseif($_REQUEST['module'] == 'PurchaseOrder' || $_REQUEST['module'] == 'SalesOrder' || $_REQUEST['module'] == 'Quotes' || $_REQUEST['module'] == 'Invoice')
		  {
			  //added to update the history for PO, SO, Quotes and Invoice
			  $history_field_array = Array(
				  			"PurchaseOrder"=>"postatus",
							"SalesOrder"=>"sostatus",
							"Quotes"=>"quotestage",
							"Invoice"=>"invoicestatus"
						      );

			  $inventory_module = $_REQUEST['module'];

			  if($_REQUEST['ajxaction'] == 'DETAILVIEW')//if we use ajax edit
			  {
				  if($inventory_module == "PurchaseOrder")
					  $relatedname = getVendorName($this->column_fields['vendor_id']);
				  else
				  	$relatedname = getAccountName($this->column_fields['account_id']);

				  $total = $this->column_fields['hdnGrandTotal'];
			  }
			  else//using edit button and save
			  {
			  	if($inventory_module == "PurchaseOrder")
			  		$relatedname = $_REQUEST["vendor_name"];
			  	else
			  		$relatedname = $_REQUEST["account_name"];

				$total = $_REQUEST['total'];
			  }

				if($this->column_fields["$history_field_array[$inventory_module]"] == $app_strings['LBL_NOT_ACCESSIBLE'])
				  {

					  //If the value in the request is Not Accessible for a picklist, the existing value will be replaced instead of Not Accessible value.
					  $his_col = $history_field_array[$inventory_module];
					  $his_sql="select $his_col from  $this->table_name where ".$this->table_index."=?";
					 $his_res = $adb->pquery($his_sql,array($this->id));
					  $status_value = $adb->query_result($his_res,0,$his_col);
					 $stat_value = $status_value;
				  }
				  else
				  {
					  $stat_value  = $this->column_fields["$history_field_array[$inventory_module]"];
				  }
			  $oldvalue = getSingleFieldValue($this->table_name,$history_field_array[$inventory_module],$this->table_index,$this->id);
			  if($this->column_fields["$history_field_array[$inventory_module]"]!= '' &&  $oldvalue != $stat_value )
			  {
				  addInventoryHistory($inventory_module, $this->id,$relatedname,$total,$stat_value);
			  }
		  }
		  //Check done by Don. If update is empty the the query fails
		  if(count($update) > 0) {
		  	//crmv@fix column
		  	foreach ($update as $key=>$upd){
		  		$vals = explode('=',$upd);
		  		$adb->format_columns($vals[0]);
		  		$update[$key] = $vals[0].'='.$vals[1];
		  	}
		  	//crmv@fix column end
		  	//crmv@26938 crmv@32409 - fix for fields exceeding maximum length
		  	if ($adb->isOracle()) {
		  		// get fields types and length
		  		$col_defs = array();
		  		$rr = $adb->pquery("select column_name,data_type, data_length from user_tab_columns where table_name = ?", array(strtoupper($table_name)));
		  		if ($rr && $adb->num_rows($rr) > 0)  {
		  			while ($row = $adb->FetchByAssoc($rr, -1, false)) {
		  				$col_defs[$row['column_name']] = array('data_type'=>$row['data_type'], 'data_length'=>$row['data_length']);
		  			}
		  		}
		  		if (count($col_defs) > 0) {
		  			for ($i=0; $i<count($update); ++$i) {
		  				$v = split('=',$update[$i]);
		  				$column_key = strtoupper($v[0]);

		  				$coltype = $col_defs[$column_key]['data_type'];
		  				$colsize = $col_defs[$column_key]['data_length'];
		  				if ($coltype == 'VARCHAR2' && is_string($update_params[$i]) && $colsize > 0 && strlen($update_params[$i]) > 0) {
		  					$update_params[$i] = substr($update_params[$i], 0, $colsize);
		  				} elseif ($coltype == 'CLOB') {
		  					// aggiorno
		  					$adb->updateClob($table_name,str_replace('=?','', $update[$i]), $this->tab_name_index[$table_name].'='.$this->id, $update_params[$i]);
		  					// rimuovo da lista
		  					unset($update_params[$i]);
		  					unset($update[$i]);
		  				}
		  			}
		  		}

		  	}
		  	//crmv@26938e	//crmv@32409e
		  	$sql1 = "update $table_name set ". implode(",",$update) ." where ". $this->tab_name_index[$table_name] ."=?";
			array_push($update_params, $this->id);
			$res = $adb->pquery($sql1, $update_params);
		  }

	  }
	  else
	  {
	  	  //crmv@fix column
	  	  $adb->format_columns($column);
	  	  //crmv@fix column end
	  	  //crmv@26938	//crmv@32409
	  	  if ($adb->isOracle()) {
	  	  	// get fields types and length
	  	  	$col_defs = array();
	  	  	$rr = $adb->pquery("select column_name,data_type, data_length from user_tab_columns where table_name = ?", array(strtoupper($table_name)));
	  	  	if ($rr && $adb->num_rows($rr) > 0)  {
	  	  		while ($row = $adb->FetchByAssoc($rr, -1, false)) {
	  	  			$col_defs[$row['column_name']] = array('data_type'=>$row['data_type'], 'data_length'=>$row['data_length']);
	  	  		}
	  	  	}
	  	  	if (count($col_defs) > 0) {
	  	  		for ($i=0; $i<count($column); ++$i) {
	  	  			$column_key = strtoupper($column[$i]);
	  	  			$coltype = $col_defs[$column_key]['data_type'];
	  	  			$colsize = $col_defs[$column_key]['data_length'];
	  	  			if ($coltype == 'VARCHAR2' && is_string($value[$i]) && $colsize > 0 && strlen($value[$i]) > 0) {
	  	  				$value[$i] = substr($value[$i], 0, $colsize);
	  	  			}
	  	  		}
	  	  	}
	  	  }
	  	  //crmv@26938e	//crmv@32409e
	  	  $sql1 = "insert into $table_name(". implode(",",$column) .") values(". generateQuestionMarks($value) .")";
		  $adb->pquery($sql1, $value);
	  }
  }

  /** Function to delete a record in the specifed table
   * @param $table_name -- table name:: Type varchar
   * The function will delete a record .The id is obtained from the class variable $this->id and the columnname got from $this->tab_name_index[$table_name]
 	 */
  function deleteRelation($table_name)
  {
  	global $adb,$table_prefix;
  	$check_query = "select * from $table_name where ". $this->tab_name_index[$table_name] ."=?";
  	$check_result=$adb->pquery($check_query, array($this->id));
  	$num_rows = $adb->num_rows($check_result);

  	if($num_rows == 1)
  	{
  		$del_query = "DELETE from $table_name where ". $this->tab_name_index[$table_name] ."=?";
  		$adb->pquery($del_query, array($this->id));
  	}

  }
  /** Function to attachment filename of the given entity
   * @param $notesid -- crmid:: Type Integer
   * The function will get the attachmentsid for the given entityid from vtiger_seattachmentsrel table and get the attachmentsname from vtiger_attachments table
   * returns the 'filename'
 	 */
  function getOldFileName($notesid)
  {
  	global $log,$table_prefix;
  	$log->info("in getOldFileName  ".$notesid);
  	global $adb;
  	$query1 = "select * from ".$table_prefix."_seattachmentsrel where crmid=?";
  	$result = $adb->pquery($query1, array($notesid));
  	$noofrows = $adb->num_rows($result);
  	if($noofrows != 0)
  		$attachmentid = $adb->query_result($result,0,'attachmentsid');
  	if($attachmentid != '')
  	{
  		$query2 = "select * from ".$table_prefix."_attachments where attachmentsid=?";
  		$filename = $adb->query_result($adb->pquery($query2, array($attachmentid)),0,'name');
  	}
  	return $filename;
  }

// Code included by Jaguar - Ends

	/** Function to retrive the information of the given recordid ,module
  	  * @param $record -- Id:: Type Integer
  	  * @param $module -- module:: Type varchar
	  * This function retrives the information from the database and sets the value in the class columnfields array
 	 */
//crmv@25872
  function retrieve_entity_info($record, $module, $dieOnError=true)
  {
    global $adb,$log,$app_strings,$current_user,$table_prefix;
    $result = Array();
    foreach($this->tab_name_index as $table_name=>$index)
    {
	    $result[$table_name] = $adb->pquery("select * from $table_name where $index=?", array($record));
	    if($adb->query_result($result[$table_prefix."_crmentity"],0,"deleted") == 1) {
	    	if ($dieOnError) {
	    		die("<br><br><center>".$app_strings['LBL_RECORD_DELETE']." <a href='javascript:window.history.back()'>".$app_strings['LBL_GO_BACK'].".</a></center>");
	    	} else {
	    		return 'LBL_RECORD_DELETE';
	    	}
	    }
    }
	//crmv@16903

	if($module == 'Leads' && $adb->query_result($result[$table_prefix."_leaddetails"],0,"converted") == 1) {
		if ($dieOnError) {
			die("<br><br><center>".$app_strings['LBL_RECORD_DELETE']." <a href='javascript:window.history.back()'>".$app_strings['LBL_GO_BACK'].".</a></center>");
		} else {
			return 'LBL_RECORD_DELETE';
		}
	}
	//crmv@16903e

    /* Prasad: Fix for ticket #4595 */
	if (isset($this->table_name)) {
    	$mod_index_col = $this->tab_name_index[$this->table_name];
    	if($adb->query_result($result[$this->table_name],0,$mod_index_col) == '') {
    		if ($dieOnError) {
	    		die("<br><br><center>".$app_strings['LBL_RECORD_NOT_FOUND'].
					". <a href='javascript:window.history.back()'>".$app_strings['LBL_GO_BACK'].".</a></center>");
    		} else {
	    		return 'LBL_RECORD_NOT_FOUND';
	    	}
    	}
	}

	// Lookup in cache for information
		$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);

	if($cachedModuleFields === false) {
    	$tabid = getTabid($module);

    	// Let us pick up all the fields first so that we can cache information
    	$sql1 =  "SELECT fieldname, fieldid, fieldlabel, columnname, tablename, uitype, typeofdata, presence
    	FROM ".$table_prefix."_field WHERE tabid=?";

    	// NOTE: Need to skip in-active fields which we will be done later.
		$result1 = $adb->pquery($sql1, array($tabid));
        $noofrows = $adb->num_rows($result1);

        if($noofrows) {
        	while($resultrow = $adb->fetch_array($result1)) {
        		// Update information to cache for re-use
	        		VTCacheUtils::updateFieldInfo(
	        			$tabid, $resultrow['fieldname'], $resultrow['fieldid'],
	        			$resultrow['fieldlabel'], $resultrow['columnname'], $resultrow['tablename'],
	        			$resultrow['uitype'], $resultrow['typeofdata'], $resultrow['presence']
	        		);
        	}
        }

        // Get only active field information
        $cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
	}

	if($cachedModuleFields) {
		foreach($cachedModuleFields as $fieldname=>$fieldinfo) {
			$fieldcolname = $fieldinfo['columnname'];
			$tablename    = $fieldinfo['tablename'];
			$fieldname    = $fieldinfo['fieldname'];

	      	// To avoid ADODB execption pick the entries that are in $tablename
	      	// (ex. when we don't have attachment for troubletickets, $result[vtiger_attachments]
	      	// will not be set so here we should not retrieve)
	      	if(isset($result[$tablename])) {
		      $fld_value = $adb->query_result($result[$tablename],0,$fieldcolname);
	      	} else {
		      $adb->println("There is no entry for this entity $record ($module) in the table $tablename");
		      $fld_value = "";
	      	}
	      	//crmv@16265
	      	if ($fieldinfo['uitype'] == 199)
	      		$this->column_fields[$fieldname] = Users::de_cryption($fld_value);
	      	elseif (in_array($fieldinfo['uitype'], array(5,6,23,70))) {
	      		// crmv@25610
	      		if ($current_user && !empty($current_user->timezonediff)) {
	      			$fld_value = adjustTimezone($fld_value, $current_user->timezonediff);
	      		}
				if(!$adb->isMySQL()){
	      				if(in_array($module, array('Calendar','Events')) && $adb->query_result($result['vtiger_activity'],0,'is_all_day_event') == '0' ){
	      					if(in_array($fieldname, array('date_start','due_date'))){
	      						$fld_value = str_replace('00:00:00','',$fld_value);
	      					}
	      				}
	      			}
	      		// crmv@25610e
	      		$this->column_fields[$fieldname] = $fld_value;
	      	} else {
	      	//crmv@16265e
	      		$this->column_fields[$fieldname] = $fld_value;
	      	}
	    }
	    // crmv@25610
	    // correggo le date per quel maledetto calendario
	    if (in_array($module, array('Calendar','Events'))) {
	    	if (!empty($this->column_fields['date_start'])) {
	    		$newval = $this->column_fields['date_start'].' '.$this->column_fields['time_start'];
	    		$newval = adjustTimezone($newval, $current_user->timezonediff);
	    		$this->column_fields['date_start'] = substr($newval, 0, 10);
	    	}
	    	if (!empty($this->column_fields['due_date'])) {
	    		$newval = $this->column_fields['due_date'].' '.$this->column_fields['time_end'];
	    		$newval = adjustTimezone($newval, $current_user->timezonediff);
	    		$this->column_fields['due_date'] = substr($newval, 0, 10);
	    	}
	    	if (!empty($this->column_fields['time_start'])) {
	    		$newval = '2010-01-01 '.$this->column_fields['time_start'];
	    		$newval = adjustTimezone($newval, $current_user->timezonediff);
	    		$this->column_fields['time_start'] = substr($newval, 11, 5);
	    	}
	    	if (!empty($this->column_fields['time_end'])) {
	    		$newval = '2010-01-01 '.$this->column_fields['time_end'];
	    		$newval = adjustTimezone($newval, $current_user->timezonediff);
	    		$this->column_fields['time_end'] = substr($newval, 11, 5);
	    	}
	    }
	    // crmv@25610e
	}
	if($module == 'Users')
	{
		for($i=0; $i<$noofrows; $i++)
		{
			$fieldcolname = $adb->query_result($result1,$i,"columnname");
			$tablename = $adb->query_result($result1,$i,"tablename");
			$fieldname = $adb->query_result($result1,$i,"fieldname");
			$fld_value = $adb->query_result($result[$tablename],0,$fieldcolname);
			$this->$fieldname = $fld_value;

		}
	}

    $this->column_fields["record_id"] = $record;
    $this->column_fields["record_module"] = $module;
  }

	/** Function to retrive the information of the given recordid ,module
  	  * @param $record -- Id:: Type Integer
  	  * @param $module -- module:: Type varchar
	  * This function retrives the information from the database and sets the value in the class columnfields array
 	 */
  function retrieve_entity_info_no_html($record, $module, $dieOnError=true)
  {
    global $adb,$log,$app_strings, $current_user,$table_prefix; // crmv@25610
    $result = Array();
    foreach($this->tab_name_index as $table_name=>$index)
    {
	    $result[$table_name] = $adb->pquery("select * from $table_name where $index=?", array($record));
	    if($adb->query_result($result[$table_prefix."_crmentity"],0,"deleted") == 1) {
	    	if ($dieOnError) {
	    		die("<br><br><center>".$app_strings['LBL_RECORD_DELETE']." <a href='javascript:window.history.back()'>".$app_strings['LBL_GO_BACK'].".</a></center>");
	    	} else {
	    		return 'LBL_RECORD_DELETE';
	    	}
	    }
    }
    //crmv@16903
	if($module == 'Leads' && $adb->query_result($result[$table_prefix."_leaddetails"],0,"converted") == 1) {
		if ($dieOnError) {
			die("<br><br><center>".$app_strings['LBL_RECORD_DELETE']." <a href='javascript:window.history.back()'>".$app_strings['LBL_GO_BACK'].".</a></center>");
		} else {
			return 'LBL_RECORD_DELETE';
		}
	}
	//crmv@16903e

    /* Prasad: Fix for ticket #4595 */
	if (isset($this->table_name)) {
    	$mod_index_col = $this->tab_name_index[$this->table_name];
    	if($adb->query_result($result[$this->table_name],0,$mod_index_col) == '') {
    		if ($dieOnError) {
	    		die("<br><br><center>".$app_strings['LBL_RECORD_NOT_FOUND'].
					". <a href='javascript:window.history.back()'>".$app_strings['LBL_GO_BACK'].".</a></center>");
    		} else {
    			return 'LBL_RECORD_NOT_FOUND';
    		}
    	}
	}

	// Lookup in cache for information
		$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);

	if($cachedModuleFields === false) {
    	$tabid = getTabid($module);

    	// Let us pick up all the fields first so that we can cache information
    	$sql1 =  "SELECT fieldname, fieldid, fieldlabel, columnname, tablename, uitype, typeofdata, presence
    	FROM ".$table_prefix."_field WHERE tabid=?";

    	// NOTE: Need to skip in-active fields which we will be done later.
		$result1 = $adb->pquery($sql1, array($tabid));
        $noofrows = $adb->num_rows($result1);

        if($noofrows) {
        	while($resultrow = $adb->fetch_array_no_html($result1)) {
        		// Update information to cache for re-use
	        		VTCacheUtils::updateFieldInfo(
	        			$tabid, $resultrow['fieldname'], $resultrow['fieldid'],
	        			$resultrow['fieldlabel'], $resultrow['columnname'], $resultrow['tablename'],
	        			$resultrow['uitype'], $resultrow['typeofdata'], $resultrow['presence']
	        		);
        	}
        }

        // Get only active field information
        $cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
	}

	if($cachedModuleFields) {
		foreach($cachedModuleFields as $fieldname=>$fieldinfo) {
			$fieldcolname = $fieldinfo['columnname'];
			$tablename    = $fieldinfo['tablename'];
			$fieldname    = $fieldinfo['fieldname'];

	      	// To avoid ADODB execption pick the entries that are in $tablename
	      	// (ex. when we don't have attachment for troubletickets, $result[vtiger_attachments]
	      	// will not be set so here we should not retrieve)
	      	if(isset($result[$tablename])) {
		      $fld_value = $adb->query_result_no_html($result[$tablename],0,$fieldcolname);
	      	} else {
		      $adb->println("There is no entry for this entity $record ($module) in the table $tablename");
		      $fld_value = "";
	      	}
	      	//crmv@16265
	      	if ($fieldinfo['uitype'] == 199)
	      		$this->column_fields[$fieldname] = Users::de_cryption($fld_value);
	      	// crmv@25610
	      	elseif (in_array($fieldinfo['uitype'], array(5,6,23,70))) {
				if(!$adb->isMySQL()){
	      				if(in_array($module, array('Calendar','Events')) && $adb->query_result($result['vtiger_activity'],0,'is_all_day_event') == '0' ){
	      					if(in_array($fieldname, array('date_start','due_date'))){
	      						$fld_value = str_replace('00:00:00','',$fld_value);
	      					}
	      				}
	      			}
	      		if ($current_user && !empty($current_user->timezonediff)) {
	      			$fld_value = adjustTimezone($fld_value, $current_user->timezonediff);
	      		}
	      		$this->column_fields[$fieldname] = $fld_value;
	      	}
	      	// crmv@25610e
	      	else {
	      	//crmv@16265e
	      		$this->column_fields[$fieldname] = $fld_value;
	      	}
	    }
	    // crmv@25610
	    // correggo le date per quel maledetto calendario
	    if (in_array($module, array('Calendar','Events'))) {
	    	if (!empty($this->column_fields['date_start'])) {
	    		$newval = $this->column_fields['date_start'].' '.$this->column_fields['time_start'];
	    		$newval = adjustTimezone($newval, $current_user->timezonediff);
	    		$this->column_fields['date_start'] = substr($newval, 0, 10);
	    	}
	    	if (!empty($this->column_fields['due_date'])) {
	    		$newval = $this->column_fields['due_date'].' '.$this->column_fields['time_end'];
	    		$newval = adjustTimezone($newval, $current_user->timezonediff);
	    		$this->column_fields['due_date'] = substr($newval, 0, 10);
	    	}
	    	if (!empty($this->column_fields['time_start'])) {
	    		$newval = '2010-01-01 '.$this->column_fields['time_start'];
	    		$newval = adjustTimezone($newval, $current_user->timezonediff);
	    		$this->column_fields['time_start'] = substr($newval, 11, 5);
	    	}
	    	if (!empty($this->column_fields['time_end'])) {
	    		$newval = '2010-01-01 '.$this->column_fields['time_end'];
	    		$newval = adjustTimezone($newval, $current_user->timezonediff);
	    		$this->column_fields['time_end'] = substr($newval, 11, 5);
	    	}
	    }
	    // crmv@25610e
	}
	if($module == 'Users')
	{
		for($i=0; $i<$noofrows; $i++)
		{
			$fieldcolname = $adb->query_result($result1,$i,"columnname");
			$tablename = $adb->query_result($result1,$i,"tablename");
			$fieldname = $adb->query_result($result1,$i,"fieldname");
			$fld_value = $adb->query_result($result[$tablename],0,$fieldcolname);
			$this->$fieldname = $fld_value;

		}
	}

    $this->column_fields["record_id"] = $record;
    $this->column_fields["record_module"] = $module;
  }
//crmv@25872e

	/** Function to saves the values in all the tables mentioned in the class variable $tab_name for the specified module
  	  * @param $module -- module:: Type varchar
 	 */
	 //ds@28 workflow
	 //crmv@8716	//crmv@27096
	 //crmv@offline
	function save($module_name,$longdesc=false,$offline_update=false,$triggerEvent=true)
	{
		global $log,$adb;
        $log->debug("module name is ".$module_name);
		if ($triggerEvent) {
			//Event triggering code
			require_once("include/events/include.inc");
			//crmv@8716
			if ($offline_update){
				$em = new VTEventTrigger_offline($adb);
			}
			else{
				$em = new VTEventsManager($adb);
			}
			// Initialize Event trigger cache
			$em->initTriggerCache();

			$entityData  = VTEntityData::fromCRMEntity($this);
			$em->triggerEvent("history_first", $entityData);
			$em->triggerEvent("vtiger.entity.beforesave.modifiable", $entityData);
			$em->triggerEvent("vtiger.entity.beforesave", $entityData);
			$em->triggerEvent("vtiger.entity.beforesave.final", $entityData);
			//Event triggering code ends
		}
		//GS Save entity being called with the modulename as parameter
		$this->saveentity($module_name,'',$longdesc);
		if ($triggerEvent) {
			//Event triggering code
			//crmv@18338
			$em->triggerEvent("vtiger.entity.aftersave.first", $entityData);
			$em->triggerEvent("vtiger.entity.aftersave", $entityData);
			$em->triggerEvent("vtiger.entity.aftersave.last", $entityData);
			//crmv@18338 end
			$em->triggerEvent("history_last", $entityData);
			//Event triggering code ends
		}
	}
	//crmv@8716e	//crmv@27096e
	//ds@28e
	//crmv@offline
	function process_list_query($query, $row_offset, $limit= -1, $max_per_page = -1)
	{
		global $list_max_entries_per_page;
		$this->log->debug("process_list_query: ".$query);
		if(!empty($limit) && $limit != -1){
			$result =& $this->db->limitQuery($query, $row_offset + 0, $limit,true,"Error retrieving $this->object_name list: ");
		}else{
			$result =& $this->db->query($query,true,"Error retrieving $this->object_name list: ");
		}

		$list = Array();
		if($max_per_page == -1){
			$max_per_page 	= $list_max_entries_per_page;
		}
		$rows_found =  $this->db->getRowCount($result);

		$this->log->debug("Found $rows_found ".$this->object_name."s");

		$previous_offset = $row_offset - $max_per_page;
		$next_offset = $row_offset + $max_per_page;

		if($rows_found != 0)
		{

			// We have some data.

			for($index = $row_offset , $row = $this->db->fetchByAssoc($result, $index); $row && ($index < $row_offset + $max_per_page || $max_per_page == -99) ;$index++, $row = $this->db->fetchByAssoc($result, $index)){


				foreach($this->list_fields as $entry)
				{

					foreach($entry as $key=>$field) // this will be cycled only once
					{
						if (isset($row[$field])) {
							$this->column_fields[$this->list_fields_names[$key]] = $row[$field];


							$this->log->debug("$this->object_name({$row['id']}): ".$field." = ".$this->$field);
						}
						else
						{
							$this->column_fields[$this->list_fields_names[$key]] = "";
						}
					}
				}


				//$this->db->println("here is the bug");


				$list[] = clone($this);//added by Richie to support PHP5
			}
		}

		$response = Array();
		$response['list'] = $list;
		$response['row_count'] = $rows_found;
		$response['next_offset'] = $next_offset;
		$response['previous_offset'] = $previous_offset;

		return $response;
	}

	function process_full_list_query($query)
	{
		$this->log->debug("CRMEntity:process_full_list_query");
		$result =& $this->db->query($query, false);
		//$this->log->debug("CRMEntity:process_full_list_query: result is ".$result);


		if($this->db->getRowCount($result) > 0){

		//	$this->db->println("process_full mid=".$this->module_id." mname=".$this->module_name);
			// We have some data.
			while ($row = $this->db->fetchByAssoc($result)) {
				//moduleid non esiste pi�...inserisco table_index
				$rowid=$row[$this->table_index];

				if(isset($rowid))
			       		$this->retrieve_entity_info($rowid,$this->module_name);
				else
					$this->db->println("rowid not set unable to retrieve");



		//clone function added to resolvoe PHP5 compatibility issue in Dashboards
		//If we do not use clone, while using PHP5, the memory address remains fixed but the
	//data gets overridden hence all the rows that come in bear the same value. This in turn
//provides a wrong display of the Dashboard graphs. The data is erroneously shown for a specific month alone
//Added by Richie
				$list[] = clone($this);//added by Richie to support PHP5
			}
		}

		if (isset($list)) return $list;
		else return null;
	}

	/** This function should be overridden in each module.  It marks an item as deleted.
	* If it is not overridden, then marking this type of item is not allowed
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	*/
	//crmv@2390m
	function mark_deleted($id)
	{
		global $table_prefix;
		$date_var = date('Y-m-d H:i:s');
		$query = "UPDATE ".$table_prefix."_crmentity set deleted=?,modifiedtime=? where crmid=?";
		$this->db->pquery($query, array('1',$this->db->formatDate($date_var, true),$id), true,"Error marking record deleted: ");
	}
	//crmv@2390me

	function retrieve_by_string_fields($fields_array, $encode=true)
	{
		$where_clause = $this->get_where($fields_array);

		$query = "SELECT * FROM $this->table_name $where_clause";
		$this->log->debug("Retrieve $this->object_name: ".$query);
		$result =& $this->db->requireSingleResult($query, true, "Retrieving record $where_clause:");
		if( empty($result))
		{
		 	return null;
		}

		 $row = $this->db->fetchByAssoc($result,-1, $encode);

		foreach($this->column_fields as $field)
		{
			if(isset($row[$field]))
			{
				$this->$field = $row[$field];
			}
		}
		return $this;
	}

	// this method is called during an import before inserting a bean
	// define an associative array called $special_fields
	// the keys are user defined, and don't directly map to the bean's vtiger_fields
	// the value is the method name within that bean that will do extra
	// processing for that vtiger_field. example: 'full_name'=>'get_names_from_full_name'

	function process_special_fields()
	{
		foreach ($this->special_functions as $func_name)
		{
			if ( method_exists($this,$func_name) )
			{
				$this->$func_name();
			}
		}
	}

	/**
         * Function to check if the custom vtiger_field vtiger_table exists
         * return true or false
         */
        function checkIfCustomTableExists($tablename)
        {
        		global $adb;
                $query = "select * from ". $adb->sql_escape_string($tablename);
                $result = $this->db->pquery($query, array());
                $testrow = $this->db->num_fields($result);
                if($testrow > 1)
                {
                        $exists=true;
                }
                else
                {
                        $exists=false;
                }
                return $exists;
        }

	/**
	 * function to construct the query to fetch the custom vtiger_fields
	 * return the query to fetch the custom vtiger_fields
         */
        function constructCustomQueryAddendum($tablename,$module)
        {
                global $adb,$table_prefix;
				$tabid=getTabid($module);
                $sql1 = "select columnname,fieldlabel from ".$table_prefix."_field where generatedtype=2 and tabid=?";
                $result = $adb->pquery($sql1, array($tabid));
                $numRows = $adb->num_rows($result);
                $sql3 = "select ";
                for($i=0; $i < $numRows;$i++)
                {
                        $columnName = $adb->query_result($result,$i,"columnname");
                        $fieldlabel = $adb->query_result($result,$i,"fieldlabel");
                        //construct query as below
                        if($i == 0)
                        {
                                $sql3 .= $tablename.".".$columnName. " '" .$fieldlabel."'";
                        }
                        else
                        {
                                $sql3 .= ", ".$tablename.".".$columnName. " '" .$fieldlabel."'";
                        }

                }
                if($numRows>0)
                {
                        $sql3=$sql3.',';
                }
                return $sql3;

        }


	/**
	 * This function returns a full (ie non-paged) list of the current object type.
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	 */
	function get_full_list($order_by = "", $where = "") {
		$this->log->debug("get_full_list:  order_by = '$order_by' and where = '$where'");
		$query = $this->create_list_query($order_by, $where);
		return $this->process_full_list_query($query);
	}

	/**
	 * Track the viewing of a detail record.  This leverages get_summary_text() which is object specific
	 * params $user_id - The user that is viewing the record.
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	 */
	function track_view($user_id, $current_module,$id='')
	{
		global $table_prefix;
		$this->log->debug("About to call ".$table_prefix."_tracker (user_id, module_name, item_id)($user_id, $current_module, $this->id)");

		$tracker = new Tracker();
		$tracker->track_view($user_id, $current_module, $id, '');
	}

	/**
	* Function to get the column value of a field
	* @param $columnname -- Column name for the field
	* @param $fldvalue -- Input value for the field taken from the User
	* @param $fieldname -- Name of the Field
	* @param $uitype -- UI type of the field
	* @return Column value of the field.
	*/
	function get_column_value($columnname, $fldvalue, $fieldname, $uitype, $datatype='') {
		global $log;
		$log->debug("Entering function get_column_value ($columnname, $fldvalue, $fieldname, $uitype, $datatype='')");

		// Added for the fields of uitype '57' which has datatype mismatch in crmentity table and particular entity table
		if ($uitype == 57 && $fldvalue == '') {
			return 0;
		}
		if (is_uitype($uitype, "_date_") && $fldvalue == '') {
			return null;
		}
		if ($datatype == 'I' || $datatype == 'N' || $datatype == 'NN'){
			return 0;
		}
		$log->debug("Exiting function get_column_value");
		return $fldvalue;
	}

	/**
	* Function to make change to column fields, depending on the current user's accessibility for the fields
	*/
	function apply_field_security() {
		global $current_user, $currentModule;

		require_once('include/utils/UserInfoUtil.php');
		foreach($this->column_fields as $fieldname=>$fieldvalue) {
		$reset_value = false;
			if (getFieldVisibilityPermission($currentModule, $current_user->id, $fieldname) != '0')
				$reset_value = true;

			if ($fieldname == "record_id" || $fieldname == "record_module")
				$reset_value = false;

			/*
				if (isset($this->additional_column_fields) && in_array($fieldname, $this->additional_column_fields) == true)
					$reset_value = false;
			 */

			if ($reset_value == true)
				$this->column_fields[$fieldname] = "";
		}
	}
	/**
	 * Function invoked during export of module record value.
	 */
	function transform_export_value($key, $value) {
		// NOTE: The sub-class can override this function as required.
		return $value;
	}

//crmv@7231
	 function crmv_compare_column_fields($fieldsEXT,$fieldsCRM){
		foreach(array_keys($fieldsEXT) as $key)
		{
			if ( ($fieldsEXT[$key]!=$fieldsCRM[$key]) &&
				((($fieldsEXT[$key]=="") || ($fieldsEXT[$key]=="--None--") ) ||
				($key=="annual_revenue" && ($fieldsEXT[$key]=="0")))
			) {
				$fieldsJDE[$key]=$fieldsCRM[$key];
				$this->column_fields[$key] = $fieldsEXT[$key];
			}

		}
		return true;
	}
	 function crmv_save_ajax_code($key){
		$this->column_fields['external_code']=$key;
		return true;
	}
//crmv@7231e
	//ds@28 workflow
	//crmv@8716
	function check_workflow_event($module_name,$old_column_fields)
	{
		if (array_search_recursive($module_name,getWorkflowModulesList()))
		{
			return fire_workflow_event_check($module_name,$old_column_fields,$this->id,false);
		}
		else
		{
			return false;
		}
	}
	//crmv@8716e
	//ds@28e

	//crmv@8719
	/** Function to initialize the required fields array for that particular module */
	function initRequiredFields($module) {
		global $adb,$table_prefix;

		$tabid = getTabId($module);
		$sql = "select * from ".$table_prefix."_field where tabid= ? and typeofdata like '%M%' and uitype not in ('53','70') and ".$table_prefix."_field.presence in (0,2)";
		$result = $adb->pquery($sql,array($tabid));
        $numRows = $adb->num_rows($result);
        for($i=0; $i < $numRows;$i++)
        {
        	$fieldName = $adb->query_result($result,$i,"fieldname");
			$this->required_fields[$fieldName] = 1;
		}
	}
	/**
	* Function to initialize the importable fields array, based on the User's accessibility to the fields
	*/
	function initImportableFields($module) {
		global $current_user, $adb;
		require_once('include/utils/UserInfoUtil.php');

		$skip_uitypes = array('3'); // uitype 3 is for Mod numbers

		// Look at cache if the fields information is available.
		$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
		if($cachedModuleFields === false) {
			getColumnFields($module); // This API will initialize the cache as well

			// We will succeed now due to above function call
			$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
		}

		$colf = Array();

		if($cachedModuleFields) {
			foreach($cachedModuleFields as $fieldinfo) {
				// Skip non-supported fields
				if(in_array($fieldinfo['uitype'], $skip_uitypes)) {
					continue;
				} else {
					$colf[$fieldinfo['fieldname']] = $fieldinfo['uitype'];
				}
			}
		}
		foreach($colf as $key=>$value) {
			if (getFieldVisibilityPermission($module, $current_user->id, $key) == '0')
				$this->importable_fields[$key] = $value;
		}
	}
	/**
	 * Move the related records of the specified list of id's to the given record.
	 * @param String This module name
	 * @param Array List of Entity Id's from which related records need to be transfered
	 * @param Integer Id of the the Record to which the related records are to be moved
	 */
	function transferRelatedRecords($module, $transferEntityIds, $entityId) {
		global $adb,$log,$table_prefix;
		$log->debug("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");
		foreach($transferEntityIds as $transferId){

			// Pick the records related to the entity to be transfered, but do not pick the once which are already related to the current entity.
			$relatedRecords =  $adb->pquery("SELECT relcrmid, relmodule FROM ".$table_prefix."_crmentityrel WHERE crmid=? AND module=?" .
					" AND relcrmid NOT IN (SELECT relcrmid FROM ".$table_prefix."_crmentityrel WHERE crmid=? AND module=?)",
					 array($transferId, $module, $entityId, $module));
			$numOfRecords = $adb->num_rows($relatedRecords);
			for($i=0;$i<$numOfRecords;$i++) {
				$relcrmid = $adb->query_result($relatedRecords,$i,'relcrmid');
				$relmodule = $adb->query_result($relatedRecords,$i,'relmodule');
				$adb->pquery("UPDATE ".$table_prefix."_crmentityrel SET crmid=? WHERE relcrmid=? AND relmodule=? AND crmid=? AND module=?",
								array($entityId, $relcrmid, $relmodule, $transferId, $module));
			}

			// Pick the records to which the entity to be transfered is related, but do not pick the once to which current entity is already related.
			$parentRecords =  $adb->pquery("SELECT crmid, module FROM ".$table_prefix."_crmentityrel WHERE relcrmid=? AND relmodule=?" .
					" AND crmid NOT IN (SELECT crmid FROM ".$table_prefix."_crmentityrel WHERE relcrmid=? AND relmodule=?)",
					 array($transferId, $module, $entityId, $module));
			$numOfRecords = $adb->num_rows($parentRecords);
			for($i=0;$i<$numOfRecords;$i++) {
				$parcrmid = $adb->query_result($parentRecords,$i,'crmid');
				$parmodule = $adb->query_result($parentRecords,$i,'module');
				$adb->pquery("UPDATE ".$table_prefix."_crmentityrel SET relcrmid=? WHERE crmid=? AND module=? AND relcrmid=? AND relmodule=?",
								array($entityId, $parcrmid, $parmodule, $transferId, $module));
			}
		}
		//crmv@15526
		if (count($transferEntityIds)>0){
			//modify 1-n relations of entities related to the one to be deleted
			$uitype10_fields_res =  $adb->pquery("SELECT ".$table_prefix."_fieldmodulerel.module,".$table_prefix."_field.columnname,".$table_prefix."_field.tablename FROM ".$table_prefix."_field
				INNER JOIN ".$table_prefix."_fieldmodulerel ON ".$table_prefix."_fieldmodulerel.fieldid = ".$table_prefix."_field.fieldid
				AND ".$table_prefix."_fieldmodulerel.relmodule = ?",array($module));
			if ($uitype10_fields_res && $adb->num_rows($uitype10_fields_res)>0){
				while ($row = $adb->fetchByAssoc($uitype10_fields_res,-1-false)){
					$sql = "update {$row[tablename]} set {$row[columnname]} = ? where {$row[columnname]} in (".generateQuestionMarks($transferEntityIds).")";
					$params = Array($entityId,$transferEntityIds);
					$adb->pquery($sql,$params);
				}
			}
		}
		//crmv@15526 end
		$log->debug("Exiting transferRelatedRecords...");
	}
	//crmv@8719
	/**
	* Function to initialize the sortby fields array
	*/
	function initSortByField($module) {
		global $adb, $log,$table_prefix;
		$log->debug("Entering function initSortByField ($module)");
		// Define the columnname's and uitype's which needs to be excluded
		$exclude_columns = Array ('parent_id','quoteid','vendorid','access_count');
		$exclude_uitypes = Array ();

		$tabid = getTabId($module);
		if($module == 'Calendar') {
			$tabid = array('9','16');
		}
		$sql = "SELECT columnname FROM ".$table_prefix."_field ".
				" WHERE (fieldname not like '%\_id' OR fieldname in ('assigned_user_id'))".
				" AND tabid in (". generateQuestionMarks($tabid) .") and ".$table_prefix."_field.presence in (0,2)";
		$params = array($tabid);
		if (count($exclude_columns) > 0) {
			$sql .= " AND columnname NOT IN (". generateQuestionMarks($exclude_columns) .")";
			array_push($params, $exclude_columns);
		}
		if (count($exclude_uitypes) > 0) {
			$sql .= " AND uitype NOT IN (". generateQuestionMarks($exclude_uitypes) . ")";
			array_push($params, $exclude_uitypes);
		}
		$result = $adb->pquery($sql,$params);
		$num_rows = $adb->num_rows($result);
		for($i=0; $i<$num_rows; $i++) {
			$columnname = $adb->query_result($result,$i,'columnname');
			if(in_array($columnname, $this->sortby_fields)) continue;
			else $this->sortby_fields[] = $columnname;
		}
		if($tabid == 21 or $tabid == 22)
			$this->sortby_fields[] = 'crmid';
		$log->debug("Exiting initSortByField");
	}
	function setMaxModuleSeqNumber($module,$req_str){
		global $adb,$table_prefix;
		//select max number of prefix in existing records
		vtlib_setup_modulevars($module, $this);
		$tabid = getTabid($module);
		$fieldinfo = $adb->pquery("SELECT * FROM ".$table_prefix."_field WHERE tabid = ? AND uitype = 4", Array($tabid));
		if ($fieldinfo){
			$row = $adb->fetchByAssoc($fieldinfo);
			$table = $row['tablename'];
			$field = $row['fieldname'];
			if ($req_str == '')
				$sql = "SELECT max($field) AS number
					FROM $table,".$table_prefix."_modentity_num
					WHERE ".$table_prefix."_modentity_num.semodule = ? and prefix = ?";
			else
				$sql = "SELECT max(".$adb->database->substr."($field,(".$adb->database->length."(prefix)+1),".$adb->database->length."($field))) AS num
						FROM $table,".$table_prefix."_modentity_num
						WHERE ".$table_prefix."_modentity_num.semodule = ? and prefix = ?";
			$params = Array($module,$req_str);
			$res = $adb->pquery($sql,$params);
			if ($res){
					$number = $adb->query_result($res,0,'num');
					if (!is_numeric($number))
						$number = 0;
					$sql = "update ".$table_prefix."_modentity_num set cur_id = ? where semodule = ? and prefix = ?";
					$adb->pquery($sql,Array($number,$module,$req_str));
			}
		}
	}
	/* Function to set the Sequence string and sequence number starting value */
	function setModuleSeqNumber($mode, $module, $req_str='', $req_no='')
	{
		global $adb,$table_prefix;
		//when we configure the invoice number in Settings this will be used
		if ($mode == "configure" && $req_no != '') {
			$check = $adb->pquery("select cur_id from ".$table_prefix."_modentity_num where semodule=? and prefix = ?", array($module, $req_str));
			if($adb->num_rows($check)== 0) {
				$numid = $adb->getUniqueId($table_prefix."_modentity_num");
				$active = $adb->pquery("select num_id from ".$table_prefix."_modentity_num where semodule=? and active=1", array($module));
				$adb->pquery("UPDATE ".$table_prefix."_modentity_num SET active=0 where num_id=?", array($adb->query_result($active,0,'num_id')));

				$adb->pquery("INSERT into ".$table_prefix."_modentity_num values(?,?,?,?,?,?)", array($numid,$module,$req_str,$req_no,$req_no,1));
				return true;
			}
			else if($adb->num_rows($check)!=0) {
				$this->setMaxModuleSeqNumber($module,$req_str);
				$num_check = $adb->query_result($check,0,'cur_id');
				if($req_no < $num_check) {
					return false;
				}
				else {
					$adb->pquery("UPDATE ".$table_prefix."_modentity_num SET active=0 where active=1 and semodule=?", array($module));
					$adb->pquery("UPDATE ".$table_prefix."_modentity_num SET cur_id=?, active = 1 where prefix=? and semodule=?", array($req_no,$req_str,$module));
					return true;
				}
			}
		}
		else if ($mode == "increment") {
			//when we save new invoice we will increment the invoice id and write
			$check = $adb->pquery("select cur_id,prefix from ".$table_prefix."_modentity_num where semodule=? and active = 1", array($module));
			$prefix = $adb->query_result($check,0,'prefix');
			$curid = $adb->query_result($check,0,'cur_id');
			$prev_inv_no=$prefix.$curid;
			$strip=strlen($curid)-strlen($curid+1);
			if($strip<0)$strip=0;
			$temp = str_repeat("0",$strip);
			$req_no.= $temp.($curid+1);
			$adb->pquery("UPDATE ".$table_prefix."_modentity_num SET cur_id=? where cur_id=? and active=1 AND semodule=?", array($req_no,$curid,$module));
			return decode_html($prev_inv_no);
		}
	}
	// END

	/* Function to get the next module sequence number for a given module */
	function getModuleSeqInfo($module) {
		global $adb,$table_prefix;
		$check = $adb->pquery("select cur_id,prefix from ".$table_prefix."_modentity_num where semodule=? and active = 1", array($module));
		if ($check){
			$prefix = $adb->query_result($check,0,'prefix');
			$curid = $adb->query_result($check,0,'cur_id');
		}
		return array($prefix, $curid);
	}
	// END

	/* Function to check if the mod number already exits */
	function checkModuleSeqNumber($table, $column, $no)
	{
		global $adb;
		$result=$adb->pquery("select ".$adb->sql_escape_string($column).
			" from ".$adb->sql_escape_string($table).
			" where ".$adb->sql_escape_string($column)." = ?", array($no));

		$num_rows = $adb->num_rows($result);

		if($num_rows > 0)
			return true;
		else
			return false;
	}
	// END

	function updateMissingSeqNumber($module) {
		global $log, $adb, $table_prefix;
		$log->debug("Entered updateMissingSeqNumber function");

		vtlib_setup_modulevars($module, $this);

		$tabid = getTabid($module);
		$fieldinfo = $adb->pquery("SELECT * FROM ".$table_prefix."_field WHERE tabid = ? AND uitype = 4", Array($tabid));

		$returninfo = Array();

		if($fieldinfo && $adb->num_rows($fieldinfo)) {
			// TODO: We assume the following for module sequencing field
			// 1. There will be only field per module
			// 2. This field is linked to module base table column
			$fld_table = $adb->query_result($fieldinfo, 0, 'tablename');
			$fld_column = $adb->query_result($fieldinfo, 0, 'columnname');

			if($fld_table == $this->table_name) {
				$records = $adb->query("SELECT $this->table_index AS recordid FROM $this->table_name " .
					"inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid = ".$this->table_name.".".$this->table_index." WHERE $fld_column = '' OR $fld_column is NULL and deleted = 0");

				if($records && $adb->num_rows($records)) {
					$returninfo['totalrecords'] = $adb->num_rows($records);
					$returninfo['updatedrecords'] = 0;

					$modseqinfo = $this->getModuleSeqInfo($module);
					$prefix = $modseqinfo[0];
					$cur_id = $modseqinfo[1];

					$old_cur_id = $cur_id;
					while($recordinfo = $adb->fetch_array($records)) {
						$value = "$prefix"."$cur_id";
						$adb->pquery("UPDATE $fld_table SET $fld_column = ? WHERE $this->table_index = ?", Array($value, $recordinfo['recordid']));
						$cur_id += 1;
						$returninfo['updatedrecords'] = $returninfo['updatedrecords'] + 1;
					}
					if($old_cur_id != $cur_id) {
						$adb->pquery("UPDATE ".$table_prefix."_modentity_num set cur_id=? where semodule=? and active=1", Array($cur_id, $module));
					}
				}
			} else {
				$log->fatal("Updating Missing Sequence Number FAILED! REASON: Field table and module table mismatching.");
			}
		}
		return $returninfo;
	}
	/* Generic function to get attachments in the related list of a given module */
	function get_attachments($id, $cur_tab_id, $rel_tab_id, $actions=false) {

		global $currentModule, $app_strings,$singlepane_view,$table_prefix;
		$this_module = $currentModule;
		$parenttab = getParentTab();

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);

		// Some standard module class doesn't have required variables
		// that are used in the query, they are defined in this generic API
		vtlib_setup_modulevars($related_module, $other);

		$singular_modname = vtlib_toSingular($related_module);
		$button = '';
		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"openPopup('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;"; //crmv@21048m
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

	 // To make the edit or del link actions to return back to same view.
		if($singlepane_view == 'true') $returnset = "&return_module=$this_module&return_action=DetailView&return_id=$id";
		else $returnset = "&return_module=$this_module&return_action=CallRelatedList&return_id=$id";

	 	$query = "select case when (".$table_prefix."_users.user_name is not null) then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name," .
				"'Documents' ActivityType,".$table_prefix."_attachments.type  FileType,crm2.modifiedtime lastmodified,
				".$table_prefix."_seattachmentsrel.attachmentsid attachmentsid,
				".$table_prefix."_notes.notesid crmid,
				".$table_prefix."_notes.notecontent description,
				".$table_prefix."_notes.note_no,
				".$table_prefix."_notes.title,
				".$table_prefix."_notes.filename,
				".$table_prefix."_notes.folderid,
				".$table_prefix."_notes.filestatus,
				".$table_prefix."_notes.filesize,
				".$table_prefix."_notes.fileversion
				from ".$table_prefix."_notes
				inner join ".$table_prefix."_senotesrel on ".$table_prefix."_senotesrel.notesid= ".$table_prefix."_notes.notesid
				inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid= ".$table_prefix."_notes.notesid and ".$table_prefix."_crmentity.deleted=0
				inner join ".$table_prefix."_notescf on ".$table_prefix."_notescf.notesid = ".$table_prefix."_notes.notesid
				inner join ".$table_prefix."_crmentity crm2 on crm2.crmid=".$table_prefix."_senotesrel.crmid
				LEFT JOIN ".$table_prefix."_groups
				ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
				left join ".$table_prefix."_seattachmentsrel  on ".$table_prefix."_seattachmentsrel.crmid =".$table_prefix."_notes.notesid
				left join ".$table_prefix."_attachments on ".$table_prefix."_seattachmentsrel.attachmentsid = ".$table_prefix."_attachments.attachmentsid
				left join ".$table_prefix."_users on ".$table_prefix."_crmentity.smownerid= ".$table_prefix."_users.id
				where crm2.crmid=".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		return $return_value;
	}
	/**
	 * Default (generic) function to handle the related list for the module.
	 * NOTE: Vtiger_Module::setRelatedList sets reference to this function in vtiger_relatedlists table
	 * if function name is not explicitly specified.
	 */
	function get_related_list($id, $cur_tab_id, $rel_tab_id, $actions=false) {

		global $currentModule, $app_strings, $singlepane_view, $table_prefix;

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
						$query.=",".$table_prefix."_.$table.$field";
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
		//crmv@24527e
		if ($related_module == 'Products'){
			$query .= " INNER JOIN ".$table_prefix."_seproductsrel ON (".$table_prefix."_seproductsrel.crmid = ".$table_prefix."_crmentity.crmid OR ".$table_prefix."_seproductsrel.productid = ".$table_prefix."_crmentity.crmid)";
		}
		elseif ($related_module == 'Documents'){
			$query .= " INNER JOIN ".$table_prefix."_senotesrel ON (".$table_prefix."_senotesrel.notesid = ".$table_prefix."_crmentity.crmid OR ".$table_prefix."_senotesrel.crmid = ".$table_prefix."_crmentity.crmid)";
		}
		else
			$query .= " INNER JOIN ".$table_prefix."_crmentityrel ON (".$table_prefix."_crmentityrel.relcrmid = ".$table_prefix."_crmentity.crmid OR ".$table_prefix."_crmentityrel.crmid = ".$table_prefix."_crmentity.crmid)";
		$query .= " LEFT  JOIN $this->table_name   ON $this->table_name.$this->table_index = $other->table_name.$other->table_index";
		$query .= $more_relation;
		$query .= " LEFT  JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid";
		$query .= " LEFT  JOIN ".$table_prefix."_groups       ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid";
		if ($related_module == 'Products'){
			$query .= " WHERE ".$table_prefix."_crmentity.deleted = 0 AND (".$table_prefix."_seproductsrel.crmid = $id OR ".$table_prefix."_seproductsrel.productid = $id)";
		}
		elseif ($related_module == 'Documents'){
			$query .= " WHERE ".$table_prefix."_crmentity.deleted = 0 AND (".$table_prefix."_senotesrel.crmid = $id OR ".$table_prefix."_senotesrel.notesid = $id)";
		}
		else
			$query .= " WHERE ".$table_prefix."_crmentity.deleted = 0 AND (".$table_prefix."_crmentityrel.crmid = $id OR ".$table_prefix."_crmentityrel.relcrmid = $id)";

		$return_value = GetRelatedList($currentModule, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		return $return_value;
	}
	/**
	 * Default (generic) function to handle the dependents list for the module.
	 * NOTE: UI type '10' is used to stored the references to other modules for a given record.
	 * These dependent records can be retrieved through this function.
	 * For eg: A trouble ticket can be related to an Account or a Contact.
	 * From a given Contact/Account if we need to fetch all such dependent trouble tickets, get_dependents_list function can be used.
	 */
	function get_dependents_list($id, $cur_tab_id, $rel_tab_id, $actions=false) {

		global $currentModule, $app_strings, $singlepane_view, $current_user,$table_prefix;

		$parenttab = getParentTab();

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);

		// Some standard module class doesn't have required variables
		// that are used in the query, they are defined in this generic API
		vtlib_setup_modulevars($currentModule, $this);
		vtlib_setup_modulevars($related_module, $other);

		$singular_modname = vtlib_toSingular($related_module);

		$button = '';

		// To make the edit or del link actions to return back to same view.
		if($singlepane_view == 'true') $returnset = "&return_module=$currentModule&return_action=DetailView&return_id=$id";
		else $returnset = "&return_module=$currentModule&return_action=CallRelatedList&return_id=$id";

		$return_value = null;
		$dependentFieldSql = $this->db->pquery("SELECT tabid, fieldname, columnname FROM ".$table_prefix."_field WHERE uitype='10' AND" .
				" fieldid IN (SELECT fieldid FROM ".$table_prefix."_fieldmodulerel WHERE relmodule=? AND module=?)", array($currentModule, $related_module));
		$numOfFields = $this->db->num_rows($dependentFieldSql);

		if($numOfFields > 0) {
			$dependentColumn = $this->db->query_result($dependentFieldSql, 0, 'columnname');
			$dependentField = $this->db->query_result($dependentFieldSql, 0, 'fieldname');

			$button .= '<input type="hidden" name="'.$dependentColumn.'" id="'.$dependentColumn.'" value="'.$id.'">';
			$button .= '<input type="hidden" name="'.$dependentColumn.'_type" id="'.$dependentColumn.'_type" value="'.$currentModule.'">';
			if($actions) {
				if(is_string($actions)) $actions = explode(',', strtoupper($actions));
				if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes'
						&& getFieldVisibilityPermission($related_module,$current_user->id,$dependentField) == '0') {
					$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname,$related_module) ."' class='crmbutton small create'" .
						" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
						" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname,$related_module) ."'>&nbsp;";
				}
			}

			$query = "SELECT ".$table_prefix."_crmentity.*, $other->table_name.*";

			$query .= ", CASE WHEN (".$table_prefix."_users.user_name is not null) THEN ".$table_prefix."_users.user_name ELSE ".$table_prefix."_groups.groupname END AS user_name";

			$more_relation = '';
			if(!empty($other->related_tables)) {
				foreach($other->related_tables as $tname=>$relmap) {
					$query .= ", $tname.*";

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
			//crmv@24527e
			$query .= " INNER  JOIN $this->table_name   ON $this->table_name.$this->table_index = $other->table_name.$dependentColumn";
			$query .= $more_relation;
			$query .= " LEFT  JOIN ".$table_prefix."_users        ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid";
			if (!empty($other->groupTable) ){
				$query .= "	LEFT JOIN ".$other->groupTable[0]."
					ON ".$other->groupTable[0].".".$other->groupTable[1]." = $other->table_name.$other->table_index ";
				$query .= "	LEFT JOIN ".$table_prefix."_groups
					ON ".$other->groupTable[0].".groupname = ".$table_prefix."_groups.groupname ";
			}
			else {
				$query .= " LEFT  JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid";
			}
			$query .= " WHERE ".$table_prefix."_crmentity.deleted = 0 AND $this->table_name.$this->table_index = $id";
			
			$return_value = GetRelatedList($currentModule, $related_module, $other, $query, $button, $returnset);
		}
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		return $return_value;
	}
	function get_documents_dependents_list($id, $cur_tab_id, $rel_tab_id, $actions=false) {

		global $currentModule, $app_strings, $singlepane_view, $current_user,$table_prefix;

		$parenttab = getParentTab();

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);

		// Some standard module class doesn't have required variables
		// that are used in the query, they are defined in this generic API
		vtlib_setup_modulevars($currentModule, $this);
		vtlib_setup_modulevars($related_module, $other);

		$singular_modname = vtlib_toSingular($related_module);

		$button = '';

		// To make the edit or del link actions to return back to same view.
		if($singlepane_view == 'true') $returnset = "&return_module=$currentModule&return_action=DetailView&return_id=$id";
		else $returnset = "&return_module=$currentModule&return_action=CallRelatedList&return_id=$id";

		$return_value = null;
		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module,$related_module). "' class='crmbutton small edit' " .
						" type='button' onclick=\"openPopup('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\"" . //crmv@21048m
						" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module,$related_module) ."'>&nbsp;";
			}
		}

		$query = "SELECT ".$table_prefix."_crmentity.*, $other->table_name.*";

		$query .= ", CASE WHEN (".$table_prefix."_users.user_name is not null) THEN ".$table_prefix."_users.user_name ELSE ".$table_prefix."_groups.groupname END AS user_name";

		$more_relation = '';
		if(!empty($other->related_tables)) {
			foreach($other->related_tables as $tname=>$relmap) {
				$query .= ", $tname.*";

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
		//crmv@24527e
		$query .= " INNER JOIN ".$table_prefix."_senotesrel ON ".$table_prefix."_senotesrel.crmid = $other->table_name.$other->table_index";
		$query .= " INNER JOIN $this->table_name ON $this->table_name.$this->table_index = ".$table_prefix."_senotesrel.notesid";
		$query .= $more_relation;
		$query .= " LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid";
		if (!empty($other->groupTable) ){
			$query .= "	LEFT JOIN ".$other->groupTable[0]."
				ON ".$other->groupTable[0].".".$other->groupTable[1]." = $other->table_name.$other->table_index ";
			$query .= "	LEFT JOIN ".$table_prefix."_groups
				ON ".$other->groupTable[0].".groupname = ".$table_prefix."_groups.groupname ";
		}
		else {
			$query .= " LEFT  JOIN ".$table_prefix."_groups       ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid";
		}
		$query .= " WHERE ".$table_prefix."_crmentity.deleted = 0 AND $this->table_name.$this->table_index = $id";
		$return_value = GetRelatedList($currentModule, $related_module, $other, $query, $button, $returnset);
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		return $return_value;
	}
	//crmv@18170
	/** Returns a list of the associated emails
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	*/
	function get_emails($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user,$table_prefix;
		$log->debug("Entering get_emails(".$id.") method ...");
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

		$button .= '<input type="hidden" name="email_directing_module"><input type="hidden" name="record">';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='". getTranslatedString('LBL_ADD_NEW')." ". getTranslatedString($singular_modname)."' accessyKey='F' class='crmbutton small create' onclick='fnvshobj(this,\"sendmail_cont\");sendmail(\"$this_module\",$id);' type='button' name='button' value='". getTranslatedString('LBL_ADD_NEW')." ". getTranslatedString($singular_modname)."'></td>";
			}
		}

		//crmv@30521	//crmv@31263
		$query = "SELECT case when (".$table_prefix."_users.user_name not like '') then ".$table_prefix."_users.user_name else ".$table_prefix."_groups.groupname end as user_name,
			".$table_prefix."_activity.activityid, ".$table_prefix."_activity.subject,
			".$table_prefix."_activity.activitytype, ".$table_prefix."_crmentity.modifiedtime,
			".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.smownerid, ".$table_prefix."_activity.date_start, ".$table_prefix."_seactivityrel.crmid as parent_id, {$table_prefix}_emaildetails.from_email
			FROM ".$table_prefix."_activity
			INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_activity.activityid
			INNER JOIN ".$table_prefix."_seactivityrel ON ".$table_prefix."_seactivityrel.activityid = ".$table_prefix."_activity.activityid
			LEFT JOIN {$table_prefix}_emaildetails ON {$table_prefix}_emaildetails.emailid = {$table_prefix}_activity.activityid
			LEFT JOIN $this->table_name ON $this->table_name.$this->table_index = ".$table_prefix."_seactivityrel.crmid
			LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid
			LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
			WHERE ".$table_prefix."_crmentity.deleted = 0 AND ".$table_prefix."_activity.activitytype = 'Emails' AND ".$table_prefix."_emaildetails.email_flag <> 'DRAFT'
			AND $this->table_name.$this->table_index = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_emails method ...");
		return $return_value;
	}
	//crmv@18170e
	/*
	 * Function to get the primary query part of a report for which generateReportsQuery Doesnt exist in module
	 * @param - $module Primary module name
	 * returns the query string formed on fetching the related data for report for primary module
	 */
	function generateReportsQuery($module){
		global $adb,$table_prefix;
		$primary = CRMEntity::getInstance($module);

		vtlib_setup_modulevars($module, $primary);
		$moduletable = $primary->table_name;
		$moduleindex = $primary->table_index;
		$modulecftable = $primary->customFieldTable[0];
		$modulecfindex = $primary->customFieldTable[1];
		if(isset($modulecftable)){
			$cfquery = "inner join $modulecftable $modulecftable on $modulecftable.$modulecfindex=$moduletable.$moduleindex"; //crmv@33980
		} else {
			$cfquery = '';
		}
		$name = substr($table_prefix."_groups$module",0,29);	//crmv@16818
		$name2 = substr($table_prefix."_users$module",0,29);	//crmv@16818
		$query = "from $moduletable $cfquery
	        inner join ".$table_prefix."_crmentity on ".$table_prefix."_crmentity.crmid=$moduletable.$moduleindex
			left join ".$table_prefix."_groups $name on $name.groupid = ".$table_prefix."_crmentity.smownerid
            left join ".$table_prefix."_users $name2 on $name2.id = ".$table_prefix."_crmentity.smownerid
			left join ".$table_prefix."_groups on ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid
            left join ".$table_prefix."_users on ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid";

        $fields_query = $adb->pquery("SELECT ".$table_prefix."_field.columnname,".$table_prefix."_field.tablename,".$table_prefix."_field.fieldid from ".$table_prefix."_field INNER JOIN ".$table_prefix."_tab on ".$table_prefix."_tab.name = ? WHERE ".$table_prefix."_tab.tabid=".$table_prefix."_field.tabid AND ".$table_prefix."_field.uitype IN (10) and ".$table_prefix."_field.presence in (0,2)",array($module));

        if($adb->num_rows($fields_query)>0){
	        for($i=0;$i<$adb->num_rows($fields_query);$i++){
	        	$field_name = $adb->query_result($fields_query,$i,'columnname');
	        	$field_id = $adb->query_result($fields_query,$i,'fieldid');
		        $tab_name = $adb->query_result($fields_query,$i,'tablename');
		        $ui10_modules_query = $adb->pquery("SELECT relmodule FROM ".$table_prefix."_fieldmodulerel WHERE fieldid=?",array($field_id));

		       if($adb->num_rows($ui10_modules_query)>0){
		       		//crmv@16312	//crmv@16818
		       		$name3 = substr($table_prefix."_crmentityRel".$module[0]."$field_id",0,29);
		       		//crmv@16312 end	//crmv@16818e
			        $query.= " left join ".$table_prefix."_crmentity $name3 on $name3.crmid = $tab_name.$field_name and $name3.deleted=0";
			        for($j=0;$j<$adb->num_rows($ui10_modules_query);$j++){
			        	$rel_mod = $adb->query_result($ui10_modules_query,$j,'relmodule');
			        	$rel_obj = CRMEntity::getInstance($rel_mod);
			        	vtlib_setup_modulevars($rel_mod, $rel_obj);

						$rel_tab_name = $rel_obj->table_name;
						$rel_tab_index = $rel_obj->table_index;
						$tablealias = substr($rel_tab_name."Rel$module",0,29);
				        $query.= " left join $rel_tab_name $tablealias on $tablealias.$rel_tab_index = $name3.crmid";
			        }
		       }
	        }
        }
 		return $query;

	}

	/*
	 * Function to get the secondary query part of a report for which generateReportsSecQuery Doesnt exist in module
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	function generateReportsSecQuery($module,$secmodule){
		global $adb, $table_prefix;
		$secondary = CRMEntity::getInstance($secmodule);

		vtlib_setup_modulevars($secmodule, $secondary);

		$tablename = $secondary->table_name;
		$tableindex = $secondary->table_index;
		$modulecftable = $secondary->customFieldTable[0];
		$modulecfindex = $secondary->customFieldTable[1];

		if(isset($modulecftable)){
			$cfquery = "left join $modulecftable $modulecftable on $modulecftable.$modulecfindex=$tablename.$tableindex";
		} else {
			$cfquery = '';
		}
		$query = $this->getRelationQuery($module,$secmodule,"$tablename","$tableindex");
		$name = substr($table_prefix."_crmentity$secmodule",0,29);
		$name2 = substr($table_prefix."_groups$secmodule",0,29);
		$name3 = substr($table_prefix."_users$secmodule",0,29);
		$query .=" 	left join ".$table_prefix."_crmentity $name on $name.crmid = $tablename.$tableindex AND $name.deleted=0
					$cfquery
					left join ".$table_prefix."_groups $name2 on $name2.groupid = $name.smownerid
		            left join ".$table_prefix."_users $name3 on $name3.id = $name.smownerid";

       $fields_query = $adb->pquery("SELECT ".$table_prefix."_field.columnname,".$table_prefix."_field.tablename,".$table_prefix."_field.fieldid from ".$table_prefix."_field INNER JOIN ".$table_prefix."_tab on ".$table_prefix."_tab.name = ? WHERE ".$table_prefix."_tab.tabid=".$table_prefix."_field.tabid AND ".$table_prefix."_field.uitype IN (10) and ".$table_prefix."_field.presence in (0,2)",array($secmodule));

       if($adb->num_rows($fields_query)>0){
	        for($i=0;$i<$adb->num_rows($fields_query);$i++){
	        	$field_name = $adb->query_result($fields_query,$i,'columnname');
	        	$field_id = $adb->query_result($fields_query,$i,'fieldid');
	        	$tab_name = $adb->query_result($fields_query,$i,'tablename');
		        $ui10_modules_query = $adb->pquery("SELECT relmodule FROM ".$table_prefix."_fieldmodulerel WHERE fieldid=?",array($field_id));

				if($adb->num_rows($ui10_modules_query)>0){
		       		$name = substr($table_prefix."_crmentityRel$secmodule",0,28).strval($i % 10); // crmv@30385
			        $query.= " left join ".$table_prefix."_crmentity $name on $name.crmid = $tab_name.$field_name and $name.deleted=0";
			        for($j=0;$j<$adb->num_rows($ui10_modules_query);$j++){
			        	$rel_mod = $adb->query_result($ui10_modules_query,$j,'relmodule');
			        	$rel_obj = CRMEntity::getInstance($rel_mod);
			        	vtlib_setup_modulevars($rel_mod, $rel_obj);

						$rel_tab_name = $rel_obj->table_name;
						$rel_tab_index = $rel_obj->table_index;
						$name2 = substr($rel_tab_name."Rel$secmodule",0,29);
				        $query.= " left join $rel_tab_name $name2 on $name2.$rel_tab_index = $name.crmid";
			        }
		       }
	        }
        }

		return $query;
	}

	/*
	 * Function to get the security query part of a report
	 * @param - $module primary module name
	 * returns the query string formed on fetching the related data for report for security of the module
	 */
	function getListViewSecurityParameter($module){
		$tabid=getTabid($module);
		global $current_user,$table_prefix;
		if($current_user)
		{
	        	require('user_privileges/user_privileges_'.$current_user->id.'.php');
	        	require('user_privileges/sharing_privileges_'.$current_user->id.'.php');
		}
		$sec_query .= " and (".$table_prefix."_crmentity.smownerid in($current_user->id) or ".$table_prefix."_crmentity.smownerid in(select ".$table_prefix."_user2role.userid from ".$table_prefix."_user2role inner join ".$table_prefix."_users on ".$table_prefix."_users.id=".$table_prefix."_user2role.userid inner join ".$table_prefix."_role on ".$table_prefix."_role.roleid=".$table_prefix."_user2role.roleid where ".$table_prefix."_role.parentrole like '".$current_user_parent_role_seq."::%') or ".$table_prefix."_crmentity.smownerid in(select shareduserid from ".$table_prefix."_tmp_read_u_per where userid=".$current_user->id." and tabid=".$tabid.") or (";

        if(sizeof($current_user_groups) > 0)
        {
              $sec_query .= $table_prefix."_groups.groupid in (". implode(",", $current_user_groups) .") or ";
        }
        $sec_query .= $table_prefix."_groups.groupid in(select ".$table_prefix."_tmp_read_g_per.sharedgroupid from ".$table_prefix."_tmp_read_g_per where userid=".$current_user->id." and tabid=".$tabid."))) ";

        $sec_query.= $this->getListViewAdvSecurityParameter_list($module);
	}

	/*
	 * Function to get the security query part of a report
	 * @param - $module primary module name
	 * returns the query string formed on fetching the related data for report for security of the module
	 */
	function getSecListViewSecurityParameter($module){
		$tabid=getTabid($module);
		global $current_user,$table_prefix;
		if($current_user)
		{
	        	require('user_privileges/user_privileges_'.$current_user->id.'.php');
	        	require('user_privileges/sharing_privileges_'.$current_user->id.'.php');
		}
		$sec_query .= " and (".$table_prefix."_crmentity$module.smownerid in($current_user->id) or ".$table_prefix."_crmentity$module.smownerid in(select ".$table_prefix."_user2role.userid from ".$table_prefix."_user2role inner join ".$table_prefix."_users on ".$table_prefix."_users.id=".$table_prefix."_user2role.userid inner join ".$table_prefix."_role on ".$table_prefix."_role.roleid=".$table_prefix."_user2role.roleid where ".$table_prefix."_role.parentrole like '".$current_user_parent_role_seq."::%') or ".$table_prefix."_crmentity$module.smownerid in(select shareduserid from ".$table_prefix."_tmp_read_u_per where userid=".$current_user->id." and tabid=".$tabid.") or (";

        if(sizeof($current_user_groups) > 0)
        {
              $sec_query .= $table_prefix."_groups$module.groupid in (". implode(",", $current_user_groups) .") or ";
        }
        $sec_query .= $table_prefix."_groups$module.groupid in(select ".$table_prefix."_tmp_read_g_per.sharedgroupid from ".$table_prefix."_tmp_read_g_per where userid=".$current_user->id." and tabid=".$tabid.")) ";
	}

	/*
	 * Function to get the relation query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on relating the primary module and secondary module
	 */
	function getRelationQuery($module,$secmodule,$table_name,$column_name){
		global $table_prefix;
		$tab = getRelationTables($module,$secmodule);
		foreach($tab as $key=>$value){
			$tables[]=$key;
			$fields[] = $value;
		}
		$tabname = $tables[0];
		$prifieldname = $fields[0][0];
		$secfieldname = $fields[0][1];
		$tmpname = $tabname."tmp".$secmodule;
		//crmv@oracle fix object name > 30 characters
		$tmpname = substr($tmpname,0,29);
		//crmv@oracle fix end
		$condition = "";
		if(!empty($tables[1]) && !empty($fields[1])){
			$condvalue = $tables[1].".".$fields[1];
		} else {
			$condvalue = $tabname.".".$prifieldname;
		}
		//mycrmv@40563 
		if ($tabname == $table_prefix.'_crmentityrel') {
			$condition = " ( {$tmpname}.{$prifieldname} = {$condvalue} AND {$tmpname}.relmodule = '{$secmodule}' ) ";
		}else {
			$condition = " {$tmpname}.{$prifieldname} = {$condvalue} ";
		}
		//mycrmv@40563e
		$condition_secmod_table = " {$table_name}.{$column_name} = {$tmpname}.{$secfieldname} ";
		if($tabname==$table_prefix.'_crmentityrel' || $tabname==$table_prefix.'_senotesrel'){	//crmv@18829
			$condition = " ($condition OR ( {$tmpname}.{$secfieldname} = $condvalue AND {$tmpname}.module = '{$secmodule}' ) ) "; //mycrmv@40563
			$condition_secmod_table = " ({$condition_secmod_table} OR {$table_name}.{$column_name} = {$tmpname}.{$prifieldname}) ";	//crmv@18829
		}

		$query = " left join {$tabname} {$tmpname} on {$condition}";
		
		
		//mycrmv@41985 
		if ($table_name == 'vtiger_leaddetails') {
		$query .= " LEFT JOIN {$table_name} ON ( {$condition_secmod_table} AND vtiger_leaddetails.converted = 0 ) ";
		}else {
		$query .= " LEFT JOIN {$table_name} ON {$condition_secmod_table}";
		}

		return $query;
	}
	/** END **/

	/**
	 * This function handles the import for uitype 10 fieldtype
	 * @param string $module - the current module name
	 * @param string fieldname - the related to field name
	 */
	function add_related_to($module, $fieldname){
		global $adb, $imported_ids, $current_user,$table_prefix;

		$related_to = $this->column_fields[$fieldname];

		if(empty($related_to)){
			return false;
		}

		//check if the field has module information; if not get the first module
		if(!strpos($related_to, "::::")){
			$module = getFirstModule($module, $fieldname);
			$value = $related_to;
		}else{
			//check the module of the field
			$arr = array();
			$arr = explode("::::", $related_to);
			$module = $arr[0];
			$value = $arr[1];
		}

		$focus1 = CRMEntity::getInstance($module);

		$entityNameArr = getEntityField($module);
		$entityName = $entityNameArr['fieldname'];
		$query = "SELECT ".$table_prefix."_crmentity.deleted, $focus1->table_name.*
					FROM $focus1->table_name
					INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid=$focus1->table_name.$focus1->table_index
						where $entityName=? and ".$table_prefix."_crmentity.deleted=0";
		$result = $adb->pquery($query, array($value));

		if(!isset($this->checkFlagArr[$module])){
			$this->checkFlagArr[$module] = (isPermitted($module,'EditView','') == 'yes');
		}

		if($adb->num_rows($result)>0){
			//record found
			$focus1->id = $adb->query_result($result, 0, $focus1->table_index);
		}elseif($this->checkFlagArr[$module]){
			//record not found; create it
	        $focus1->column_fields[$focus1->list_link_field] = $value;
	        $focus1->column_fields['assigned_user_id'] = $current_user->id;
	        $focus1->column_fields['modified_user_id'] = $current_user->id;
			$focus1->save($module);

    		$last_import = new UsersLastImport();
    		$last_import->assigned_user_id = $current_user->id;
    		$last_import->bean_type = $module;
    		$last_import->bean_id = $focus1->id;
    		$last_import->save();
		}else{
			//record not found and cannot create
			$this->column_fields[$fieldname] = "";
			return false;
		}
		if(!empty($focus1->id)){
			$this->column_fields[$fieldname] = $focus1->id;
			return true;
		}else{
			$this->column_fields[$fieldname] = "";
			return false;
		}
    }
	/**
	 * To keep track of action of field filtering and avoiding doing more than once.
	 *
	 * @var Array
	 */
	protected $__inactive_fields_filtered = false;

	/**
	 * Filter in-active fields based on type
	 *
	 * @param String $module
	 */
	function filterInactiveFields($module) {
		if($this->__inactive_fields_filtered) {
			return;
		}

		global $adb, $mod_strings;

		// Look for fields that has presence value NOT IN (0,2)
		$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module, array('1'));
		if($cachedModuleFields === false) {
			// Initialize the fields calling suitable API
			getColumnFields($module);
			$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module, array('1'));
		}

		$hiddenFields = array();

		if($cachedModuleFields) {
			foreach($cachedModuleFields as $fieldinfo) {
				$fieldLabel= $fieldinfo['fieldlabel'];
				// NOTE: We should not translate the label to enable field diff based on it down
				$fieldName = $fieldinfo['fieldname'];
				$tableName = str_replace($table_prefix."_","",$fieldinfo['tablename']);
				$hiddenFields[$fieldLabel] = array($tableName=>$fieldName);
			}
		}

		if(isset($this->list_fields)) {
			$this->list_fields   = array_diff_assoc($this->list_fields, $hiddenFields);
		}

		if(isset($this->search_fields)) {
			$this->search_fields = array_diff_assoc($this->search_fields, $hiddenFields);
		}

		// To avoid re-initializing everytime.
		$this->__inactive_fields_filtered = true;
	}
	/** END **/
	//crmv@18744
	/**
	 * For Record View Notification
	 */
	function isViewed($crmid=false) {
		if(!$crmid) { $crmid = $this->id; }
		if($crmid) {
			global $adb,$table_prefix;
			$result = $adb->pquery("SELECT viewedtime,modifiedtime,smcreatorid,smownerid,modifiedby FROM ".$table_prefix."_crmentity WHERE crmid=?", Array($crmid));
			$resinfo = $adb->fetch_array($result);

			$lastviewed = $resinfo['viewedtime'];
			$modifiedon = $resinfo['modifiedtime'];
			$smownerid   = $resinfo['smownerid'];
			$smcreatorid = $resinfo['smcreatorid'];
			$modifiedby = $resinfo['modifiedby'];

			if($modifiedby == '0' && ($smownerid == $smcreatorid)) {
				/** When module record is created **/
				return true;
			} else if($smownerid == $modifiedby) {
				/** Owner and Modifier as same. **/
				return true;
			} else if($lastviewed && $modifiedon) {
				/** Lastviewed and Modified time is available. */
				if($this->__timediff($modifiedon, $lastviewed) > 0) return true;
			}
		}
		return false;
	}
	function __timediff($d1, $d2) {
		list($t1_1, $t1_2) = explode(' ', $d1);
		list($t1_y, $t1_m, $t1_d) = explode('-', $t1_1);
		list($t1_h, $t1_i, $t1_s) = explode(':', $t1_2);

		$t1 = mktime($t1_h, $t1_i, $t1_s, $t1_m, $t1_d, $t1_y);

		list($t2_1, $t2_2) = explode(' ', $d2);
		list($t2_y, $t2_m, $t2_d) = explode('-', $t2_1);
		list($t2_h, $t2_i, $t2_s) = explode(':', $t2_2);

		$t2 = mktime($t2_h, $t2_i, $t2_s, $t2_m, $t2_d, $t2_y);

		if( $t1 == $t2 ) return 0;
		return $t2 - $t1;
	}
	//crmv@18744e
	function markAsViewed($userid) {
		global $adb,$table_prefix;
		$adb->pquery("UPDATE ".$table_prefix."_crmentity set viewedtime=? WHERE crmid=? AND smownerid=?",
			Array( date('Y-m-d H:i:s'), $this->id, $userid));
	}
	/**
	 * Save the related module record information. Triggered from CRMEntity->saveentity method or updateRelations.php
	 * @param String This module name
	 * @param Integer This module record number
	 * @param String Related module name
	 * @param mixed Integer or Array of related module record number
	 */
	function save_related_module($module, $crmid, $with_module, $with_crmid) {
		global $adb,$table_prefix;
		if(!is_array($with_crmid)) $with_crmid = Array($with_crmid);
		foreach($with_crmid as $relcrmid) {
			if ($crmid != $relcrmid) {	//crmv@24862
				//crmv@29617
				//crmv@33465 
				if (isModuleInstalled('ModNotifications')) {
					$obj = CRMEntity::getInstance('ModNotifications');
					$obj->saveRelatedModuleNotification($crmid, $module, $relcrmid, $with_module);
				}
				//crmv@33465e
				//crmv@29617e
				if($with_module == 'Documents') {
					$checkpresence = $adb->pquery("SELECT crmid FROM ".$table_prefix."_senotesrel WHERE crmid = ? AND notesid = ?", Array($crmid, $relcrmid));
					// Relation already exists? No need to add again
					if($checkpresence && $adb->num_rows($checkpresence)) continue;

					$adb->pquery("INSERT INTO ".$table_prefix."_senotesrel(crmid, notesid) VALUES(?,?)", array($crmid, $relcrmid));
				}
				elseif($with_module == 'Products') {
					$checkpresence = $adb->pquery("SELECT crmid FROM ".$table_prefix."_seproductsrel WHERE crmid = ? AND productid = ? and setype = ?", Array($crmid, $relcrmid, $module));
					// Relation already exists? No need to add again
					if($checkpresence && $adb->num_rows($checkpresence)) continue;

					$adb->pquery("insert into ".$table_prefix."_seproductsrel(crmid, productid, setype) values(?,?,?)", array($crmid, $relcrmid, $module));
				} else {
					$checkpresence = $adb->pquery("SELECT crmid FROM ".$table_prefix."_crmentityrel WHERE
						crmid = ? AND module = ? AND relcrmid = ? AND relmodule = ?", Array($crmid, $module, $relcrmid, $with_module));
					// Relation already exists? No need to add again
					if($checkpresence && $adb->num_rows($checkpresence)) continue;

					$adb->pquery("INSERT INTO ".$table_prefix."_crmentityrel(crmid, module, relcrmid, relmodule) VALUES(?,?,?,?)",
						Array($crmid, $module, $relcrmid, $with_module));
				}
			}	//crmv@24862
		}
	}

	/**
	 * Delete the related module record information. Triggered from updateRelations.php
	 * @param String This module name
	 * @param Integer This module record number
	 * @param String Related module name
	 * @param mixed Integer or Array of related module record number
	 */
	function delete_related_module($module, $crmid, $with_module, $with_crmid) {
		global $adb,$table_prefix;
		if(!is_array($with_crmid)) $with_crmid = Array($with_crmid);
		foreach($with_crmid as $relcrmid) {
			if($with_module == 'Documents') {
				$adb->pquery("DELETE FROM ".$table_prefix."_senotesrel WHERE crmid=? AND notesid=?",
					Array($crmid, $relcrmid));
			} elseif($with_module == 'Calendar') {
				$adb->pquery("DELETE FROM ".$table_prefix."_seactivityrel WHERE crmid=? AND activityid=?",
					Array($crmid, $relcrmid));
			// crmv@31780
			} elseif($with_module == 'Products') {
				$adb->pquery("DELETE FROM ".$table_prefix."_seproductsrel WHERE crmid=? AND productid=?", Array($crmid, $relcrmid));
			// crmv@31780e
			} else {
				$adb->pquery("DELETE FROM ".$table_prefix."_crmentityrel WHERE crmid=? AND module=? AND relcrmid=? AND relmodule=?",
					Array($crmid, $module, $relcrmid, $with_module));
			}
		}
	}
	/** Function to delete an entity with given Id */
	function trash($module, $id) {
		global $log, $current_user, $adb, $table_prefix;
/*
		require_once("include/events/include.inc");
		$em = new VTEventsManager($adb);

		// Initialize Event trigger cache
		$em->initTriggerCache();

		$entityData = VTEntityData::fromEntityId($adb, $id);

		$em->triggerEvent("vtiger.entity.beforedelete", $entityData);
*/
		$this->mark_deleted($id);
		$this->unlinkDependencies($module, $id);

		require_once('include/freetag/freetag.class.php');
		$freetag = new freetag();
		$freetag->delete_all_object_tags_for_user($current_user->id, $id);

		$sql_recentviewed = 'DELETE FROM '.$table_prefix.'_tracker WHERE user_id = ? AND item_id = ?';
		$this->db->pquery($sql_recentviewed, array($current_user->id, $id));
/*
		$em->triggerEvent("vtiger.entity.afterdelete", $entityData);
*/
	}


	/** Function to unlink all the dependent entities of the given Entity by Id */
	function unlinkDependencies($module, $id) {
		global $log,$table_prefix;

		$fieldRes = $this->db->pquery('SELECT tabid, tablename, columnname FROM '.$table_prefix.'_field WHERE fieldid IN (
			SELECT fieldid FROM '.$table_prefix.'_fieldmodulerel WHERE relmodule=?)', array($module));
		$numOfFields = $this->db->num_rows($fieldRes);
		for ($i=0; $i<$numOfFields; $i++) {
			$tabId = $this->db->query_result($fieldRes, $i, 'tabid');
			$tableName = $this->db->query_result($fieldRes, $i, 'tablename');
			$columnName = $this->db->query_result($fieldRes, $i, 'columnname');

			$relatedModule = vtlib_getModuleNameById($tabId);
			$focusObj = CRMEntity::getInstance($relatedModule);

			//Backup Field Relations for the deleted entity
			$relQuery = "SELECT $focusObj->table_index FROM $tableName WHERE $columnName=?";
			$relResult = $this->db->pquery($relQuery, array($id));
			$numOfRelRecords = $this->db->num_rows($relResult);
			if ($numOfRelRecords > 0) {
				$recordIdsList = array();
				for($k=0;$k < $numOfRelRecords;$k++)
				{
					$recordIdsList[] = $this->db->query_result($relResult,$k,$focusObj->table_index);
				}
				$params = array($id, RB_RECORD_UPDATED, $tableName, $columnName, $focusObj->table_index, implode(",", $recordIdsList));
				$this->db->pquery('INSERT INTO '.$table_prefix.'_relatedlists_rb VALUES (?,?,?,?,?,?)', $params);
			}

		}
		//crmv@23515
		$CalendarRelatedTo = getCalendarRelatedToModules();
		if (in_array($module,$CalendarRelatedTo)) {
			$sql = 'DELETE FROM '.$table_prefix.'_seactivityrel WHERE crmid=?';
			$this->db->pquery($sql, array($id));
		}
		//crmv@23515e
	}

	/** Function to unlink an entity with given Id from another entity */
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log, $currentModule,$table_prefix;

		$query = 'DELETE FROM '.$table_prefix.'_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
		$params = array($id, $return_module, $return_id, $id, $return_module, $return_id);
		$this->db->pquery($query, $params);

		$fieldRes = $this->db->pquery('SELECT tabid, tablename, columnname FROM '.$table_prefix.'_field WHERE fieldid IN (
			SELECT fieldid FROM '.$table_prefix.'_fieldmodulerel WHERE module=? AND relmodule=?)', array($currentModule, $return_module));
		$numOfFields = $this->db->num_rows($fieldRes);
		for ($i=0; $i<$numOfFields; $i++) {
			$tabId = $this->db->query_result($fieldRes, $i, 'tabid');
			$tableName = $this->db->query_result($fieldRes, $i, 'tablename');
			$columnName = $this->db->query_result($fieldRes, $i, 'columnname');

			$relatedModule = vtlib_getModuleNameById($tabId);
			$focusObj = CRMEntity::getInstance($relatedModule);

			$updateQuery = "UPDATE $tableName SET $columnName=0 WHERE $columnName=? AND $focusObj->table_index=?";
			$updateParams = array($return_id, $id);
			$this->db->pquery($updateQuery, $updateParams);
		}
	}

	/** Function to restore a deleted record of specified module with given crmid
  	  * @param $module -- module name:: Type varchar
  	  * @param $entity_ids -- list of crmids :: Array
 	 */
	function restore($module, $id) {
		global $current_user, $adb, $table_prefix;

		$this->db->println("TRANS restore starts $module");
		$this->db->startTransaction();

		$date_var = date('Y-m-d H:i:s');
		$query = 'UPDATE '.$table_prefix.'_crmentity SET deleted=0,modifiedtime=?,modifiedby=? WHERE crmid = ?';
		$this->db->pquery($query, array($this->db->formatDate($date_var, true), $current_user->id, $id), true, "Error restoring records :");
		//Restore related entities/records
		$this->restoreRelatedRecords($module, $id);
/*
		//Event triggering code
		require_once("include/events/include.inc");
		global $adb;
		$em = new VTEventsManager($adb);

		// Initialize Event trigger cache
		$em->initTriggerCache();

		$this->id = $id;
		$entityData = VTEntityData::fromCRMEntity($this);
		//Event triggering code
		$em->triggerEvent("vtiger.entity.afterrestore", $entityData);
		//Event triggering code ends
*/
		$this->db->completeTransaction();
		$this->db->println("TRANS restore ends");
	}

	/** Function to restore all the related records of a given record by id */
	function restoreRelatedRecords($module,$record) {
		global $table_prefix;
		$result = $this->db->pquery('SELECT * FROM '.$table_prefix.'_relatedlists_rb WHERE entityid = ?', array($record));
		$numRows = $this->db->num_rows($result);
		for($i=0; $i < $numRows;$i++)
		{
			$action = $this->db->query_result($result,$i,"action");
			$rel_table = $this->db->query_result($result,$i,"rel_table");
			$rel_column = $this->db->query_result($result,$i,"rel_column");
			$ref_column = $this->db->query_result($result,$i,"ref_column");
			$related_crm_ids = $this->db->query_result($result,$i,"related_crm_ids");

			if($action == RB_RECORD_UPDATED && trim($related_crm_ids)!='') {
				$related_ids = explode(",", $related_crm_ids);
				if($rel_table == $table_prefix.'_crmentity' && $rel_column == 'deleted') {
					$sql = "UPDATE $rel_table set $rel_column = 0 WHERE $ref_column IN (". generateQuestionMarks($related_ids) . ")";
					$this->db->pquery($sql, array($related_ids));
				} else {
					$sql = "UPDATE $rel_table set $rel_column = ? WHERE $rel_column = 0 AND $ref_column IN (". generateQuestionMarks($related_ids) . ")";
					$this->db->pquery($sql, array($record, $related_ids));
				}
			} elseif ($action == RB_RECORD_DELETED) {
				if ($rel_table == $table_prefix.'_seproductrel') {
					$sql = "INSERT INTO $rel_table($rel_column, $ref_column, 'setype') VALUES (?,?,?)";
					$this->db->pquery($sql, array($record, $related_crm_ids, $module));
				} else {
					$sql = "INSERT INTO $rel_table($rel_column, $ref_column) VALUES (?,?)";
					$this->db->pquery($sql, array($record, $related_crm_ids));
				}
			}
		}
		//Clean up the the backup data also after restoring
		$this->db->pquery('DELETE FROM '.$table_prefix.'_relatedlists_rb WHERE entityid = ?', array($record));
	}
	function getListViewAdvSecurityParameter($module,$scope=''){
	    global $current_user;
	    require('user_privileges/user_privileges_'.$current_user->id.'.php');
	    require('user_privileges/sharing_privileges_'.$current_user->id.'.php');
	    $tabid = getTabid($module);
    	if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1
			&& $defaultOrgSharingPermission[$tabid] == 3) {
			$query = getAdvancedresList($module,'listview');
			$query .= SDK::getAdvancedQuery($module);	//crmv@sdk-18507
			//TODO: extend related module permissions
//			if (is_array($related_module_adv_share[$tabid])){
//				foreach ($related_module_adv_share[$tabid] as $rel_tabid)
//				$query .= getParentAdvancedresList($module,getTabname($rel_tabid),'listview');
//			}
		}
		if ($scope != ''){
			$query = str_ireplace($table_prefix.'_crmentity', substr($table_prefix."_crmentity$scope",0,29),$query);
		}
		return $query;
	}
	function getListViewAdvSecurityParameter_list($module){
		    global $current_user;
	    require('user_privileges/user_privileges_'.$current_user->id.'.php');
	    require('user_privileges/sharing_privileges_'.$current_user->id.'.php');
	    $tabid = getTabid($module);
    	if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1
			&& $defaultOrgSharingPermission[$tabid] == 3) {
			$query = getAdvancedresList($module,'listview');
			$query .= SDK::getAdvancedQuery($module);	//crmv@sdk-18507
			//TODO: extend related module permissions
			//crmv@13979
//			if($rel_tabid==6 && $tabid==4)
//				$tabname='Accounts_Contacts';
//			else
//				$tabname=getTabname($rel_tabid);
//			if (is_array($related_module_adv_share[$tabid])){
//				foreach ($related_module_adv_share[$tabid] as $rel_tabid)
//				$query .= getParentAdvancedresList($module,$tabname,'listview');
//			}
			//crmv@13979 end
			$query .= ")";
		}
		return $query;
	}
	function getListViewAdvSecurityParameter_fields($module){
		    global $current_user,$adb;
	    require('user_privileges/user_privileges_'.$current_user->id.'.php');
	    require('user_privileges/sharing_privileges_'.$current_user->id.'.php');
	    $tabid = getTabid($module);
	    $cols = Array();
    	if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1
			&& $defaultOrgSharingPermission[$tabid] == 3) {
			$cols = Zend_Json::decode(getAdvancedresList($module,'columns'));
		}
		return $cols;
	}
	function getListViewAdvSecurityParameter_check($module){
	    if ($this->getListViewAdvSecurityParameter($module))
	    	return true;
	    else
	    	return false;
	}
	function buildSearchQueryForFieldTypes($uitypes, $value) {
		global $adb, $table_prefix;

		if(!is_array($uitypes)) $uitypes = array($uitypes);
		$module = SDK::getParentModule(get_class($this));	//crmv@26936

		$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
		if($cachedModuleFields === false) {
			getColumnFields($module); // This API will initialize the cache as well
			// We will succeed now due to above function call
			$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
		}

		$lookuptables = array();
		$lookupcolumns = array();
		foreach($cachedModuleFields as $fieldinfo) {
			if (in_array($fieldinfo['uitype'], $uitypes)) {
				$lookuptables[] = $fieldinfo['tablename'];
				$lookupcolumns[] = $fieldinfo['columnname'];
			}
		}

		$entityfields = getEntityField($module);
		$querycolumnnames = implode(',', $lookupcolumns);
		$entitycolumnnames = $entityfields['fieldname'];
		$query = "select crmid as id, $querycolumnnames, $entitycolumnnames as name ";
		$query .= " FROM $this->table_name ";
		$query .=" INNER JOIN ".$table_prefix."_crmentity ON $this->table_name.$this->table_index = ".$table_prefix."_crmentity.crmid AND deleted = 0 ";

		//remove the base table
		$LookupTable = array_unique($lookuptables);
		$indexes = array_keys($LookupTable, $this->table_name);
		if(!empty($indexes)) {
			foreach($indexes as $index) {
				unset($LookupTable[$index]);
			}
		}
		foreach($LookupTable as $tablename) {
			$query .= " INNER JOIN $tablename
						on $this->table_name.$this->table_index = $tablename.".$this->tab_name_index[$tablename];
		}
		if(!empty($lookupcolumns)) {
			$query .=" WHERE ";
			$i=0;
			$columnCount = count($lookupcolumns);
			foreach($lookupcolumns as $columnname) {
				if(!empty($columnname)) {
					if($i == 0 || $i == ($columnCount))
						$query .= sprintf("%s = '%s'", $columnname, $value);
					else
						$query .= sprintf(" OR %s = '%s'", $columnname, $value);
					$i++;
				}
			}
		}
		return $query;
	}
	/**
	 *
	 * @param String $tableName
	 * @return String
	 */
	public function getJoinClause($tableName) {
		if(strripos($tableName, 'rel') === (strlen($tableName) - 3)) {
			return 'LEFT JOIN';
		} else {
			return 'INNER JOIN';
		}
	}
	/**
	 *
	 * @param <type> $module
	 * @param <type> $user
	 * @param <type> $parentRole
	 * @param <type> $userGroups
	 */
	function getNonAdminAccessQuery($module,$user,$parentRole,$userGroups){
		$query = $this->getNonAdminUserAccessQuery($user, $parentRole, $userGroups);
		if(!empty($module)) {
			$moduleAccessQuery = $this->getNonAdminModuleAccessQuery($module, $user);
			if(!empty($moduleAccessQuery)) {
				$query .= " UNION $moduleAccessQuery";
			}
		}
		$query.=" ) un_table ";
		return $query;
	}

	/**
	 *
	 * @param <type> $user
	 * @param <type> $parentRole
	 * @param <type> $userGroups
	 */
	function getNonAdminUserAccessQuery($user,$parentRole,$userGroups){
		global $table_prefix;
		$query = "select id from ((SELECT id from ".$table_prefix."_users where id = '$user->id') UNION (SELECT ".$table_prefix."_user2role.userid AS id FROM ".
		"".$table_prefix."_user2role INNER JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id=".$table_prefix."_user2role.userid ".
		"INNER JOIN ".$table_prefix."_role ON ".$table_prefix."_role.roleid=".$table_prefix."_user2role.roleid WHERE ".
		"".$table_prefix."_role.parentrole like '$parentRole::%')";
		if(count($userGroups) > 0 ) {
			$query .= " UNION (SELECT groupid as id FROM ".$table_prefix."_groups where".
				" groupid in (".implode(",", $userGroups)."))";
		}
		return $query;
	}

	/**
	 *
	 * @param <type> $module
	 * @param <type> $user
	 */
	function getNonAdminModuleAccessQuery($module,$user){
		global $table_prefix;
		require('user_privileges/sharing_privileges_'.$user->id.'.php');
		$tabId = getTabid($module);
		$sharingRuleInfoVariable = $module.'_share_read_permission';
		$sharingRuleInfo = $$sharingRuleInfoVariable;
		$sharedTabId = null;
		$query = '';
		if(!empty($sharingRuleInfo) && (count($sharingRuleInfo['ROLE']) > 0 ||
				count($sharingRuleInfo['GROUP']) > 0 ||
				count($sharingRuleInfo['USR']) > 0)) {
			$query = " (SELECT shareduserid FROM ".$table_prefix."_tmp_read_u_per ".
			"WHERE userid=$user->id AND tabid=$tabId) UNION (SELECT ".
			"".$table_prefix."_tmp_read_g_per.sharedgroupid FROM ".
			"".$table_prefix."_tmp_read_g_per WHERE userid=$user->id AND tabid=$tabId)";
		}
		return $query;
	}

	/**
	 *
	 * @param <type> $module
	 * @param <type> $user
	 * @param <type> $parentRole
	 * @param <type> $userGroups
	 */
	protected function setupTemporaryTable($tableName,$tabId, $user,$parentRole,$userGroups) {
		$module = null;
		if (!empty($tabId)) {
			$module = getTabModuleName($tabId);
		}
		$query = $this->getNonAdminAccessQuery($module, $user, $parentRole, $userGroups);
		$db = PearDatabase::getInstance();
		if ($db->isMysql()){
			//mycrmv@carel
			if (substr($tableName,0,8) == 'vt_tmp_u') {
				$db->query("DROP TABLE IF EXISTS $tableName ");
			}
			//mycrmv@carel end
			$query = "create temporary table IF NOT EXISTS $tableName(id int(11) primary key) ignore ".
			$query;
			$result = $db->pquery($query, array());
		}
		else {
			if (!$db->table_exist($tableName,true)){
				Vtiger_Utils::CreateTable($tableName,"id I(11) NOTNULL PRIMARY",true,true);
			}
			$tableName = $db->datadict->changeTableName($tableName);
			//mycrmv@28443 
			$query_trunc = "truncate table $tableName";
			$result_trunc = $db->pquery($query_trunc, array());
			//mycrmv@28443 e
			$query = "insert into $tableName ".
			$query.
			"where not exists (select * from $tableName where $tableName.id = un_table.id)";
			$result = $db->pquery($query, array());
		}
		return $result;
	}

	/**
	 *
	 * @param String $module - module name for which query needs to be generated.
	 * @param Users $user - user for which query needs to be generated.
	 * @return String Access control Query for the user.
	 */
	function getNonAdminAccessControlQuery($module,$user,$scope='',$join_cond=''){	//crmv@31775
		global $table_prefix;
		require('user_privileges/user_privileges_'.$user->id.'.php');
		require('user_privileges/sharing_privileges_'.$user->id.'.php');
		$query = ' ';
		$tabId = getTabid($module);
		if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2]
				== 1 && $defaultOrgSharingPermission[$tabId] == 3) {
			$tableName = 'vt_tmp_u'.$user->id;
			$sharingRuleInfoVariable = $module.'_share_read_permission';
			$sharingRuleInfo = $$sharingRuleInfoVariable;
			$sharedTabId = null;
			if(!empty($sharingRuleInfo) && (count($sharingRuleInfo['ROLE']) > 0 ||
					count($sharingRuleInfo['GROUP']) > 0
					 || count($sharingRuleInfo['USR']) > 0)) {
				$tableName = $tableName.'_t'.$tabId;
				$sharedTabId = $tabId;
			}elseif($module == 'Calendar' || !empty($scope)) {
				$tableName .= '_t'.$tabId;
			}
			$this->setupTemporaryTable($tableName, $sharedTabId, $user,
					$current_user_parent_role_seq, $current_user_groups);
			if (!$this->getListViewAdvSecurityParameter_check($module)){
				$name = substr($tableName.$scope,0,29);
				$name2 = substr($table_prefix."_crmentity$scope",0,29);
				$db = PearDatabase::getInstance();
				$tableName = $db->datadict->changeTableName($tableName);
				//crmv@26650
				if ($module == 'Calendar') {
					$name3 = substr($table_prefix."_activity$scope",0,29);
					$query = " INNER JOIN $tableName $name ON ($name.id = $name2.smownerid and $name.shared=0) ";

					$sharedIds = getSharedCalendarId($user->id);
					if(!empty($sharedIds)){
						$query .= "or ($name.id = $name2.smownerid AND $name.shared=1 and $name3.visibility = 'Public') ";
					}
					//crmv@17001
					$query .= " or ($name.id = $name2.smownerid AND ".$table_prefix."_activity.activityid in(SELECT activityid FROM ".$table_prefix."_invitees WHERE inviteeid = $user->id))";
					//crmv@17001e
				} else { //crmv@26650e
					//crmv@31775
					if ($join_cond == '') {
						$join_cond = 'INNER';
					}
					$query = " $join_cond JOIN $tableName $name ON $name.id = $name2.smownerid ";
					//crmv@31775e
				}
			}
		}
		return $query;
	}
	function getNonAdminAccessControlQuery_onlyquery($module,$user,$scope=''){
		global $table_prefix;
		require('user_privileges/user_privileges_'.$user->id.'.php');
		require('user_privileges/sharing_privileges_'.$user->id.'.php');
		$query = ' ';
		$tabId = getTabid($module);
		if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2]
				== 1 && $defaultOrgSharingPermission[$tabId] == 3) {
			$tableName = 'vt_tmp_u'.$user->id;
			$sharingRuleInfoVariable = $module.'_share_read_permission';
			$sharingRuleInfo = $$sharingRuleInfoVariable;
			$sharedTabId = null;
			if(!empty($sharingRuleInfo) && (count($sharingRuleInfo['ROLE']) > 0 ||
					count($sharingRuleInfo['GROUP']) > 0
					 || count($sharingRuleInfo['USR']) > 0)) {
				$tableName = $tableName.'_t'.$tabId;
				$sharedTabId = $tabId;
			}elseif($module == 'Calendar' || !empty($scope)) {
				$tableName .= '_t'.$tabId;
			}
			$name = substr($tableName.$scope,0,29);
			$name2 = substr($table_prefix."_crmentity$scope",0,29);
			$db = PearDatabase::getInstance();
			$tableName = $db->datadict->changeTableName($tableName);
			//crmv@26650
		   	if ($module == 'Calendar') {
		    	$query = "select *
		    			from $tableName
		    			where ($tableName.id = $name2.smownerid AND $tableName.shared = 0)
		    			OR ($tableName.id = $name2.smownerid AND ".$table_prefix."_activity.activityid IN (
			    				SELECT activityid
							    FROM ".$table_prefix."_invitees
							    WHERE ".$table_prefix."_activity.activityid > 0 AND ".$table_prefix."_invitees.inviteeid = {$user->id}
		    				)
		    			)";
		   	} else {
	   		//crmv@26650e
		   		$query = " select * from  $tableName $name where $name.id = $name2.smownerid ";
		   	}
		}
		return $query;
	}

	public function listQueryNonAdminChange( $query,$module,$scope='' ) {
		//make the module base table as left hand side table for the joins,
		//as mysql query optimizer puts crmentity on the left side and considerably slow down
		$query = preg_replace("/[\n\r\t]+/"," ",$query); //crmv@20049
		if(strripos($query, ' WHERE ') !== false) {
			vtlib_setup_modulevars($module, $this);
		//crmv@7221
		if ($this->getListViewAdvSecurityParameter_check($module)){
			global $current_user;
			$userid_conditions = $this->getNonAdminAccessControlQuery_onlyquery($module,$current_user,$scope);
			$adv_filter_conditions = $this->getListViewAdvSecurityParameter($module,$scope);
			if (!$userid_conditions)
				$add = $adv_filter_conditions;
			else
				$add = " and exists ($userid_conditions $adv_filter_conditions)";
			//crmv@24715
			fix_query_advanced_filters($module,$query);
			//crmv@24715e
		}
		//crmv@7221e
			$query = str_ireplace(' where ', " WHERE $this->table_name.$this->table_index > 0 AND ",
					$query);
			if ($add)
			$query .=  $add;
		}
		return $query;
	}
	public function listQueryNonAdminChange_parent( $query,$module,$scope='' ) {
		//make the module base table as left hand side table for the joins,
		//as mysql query optimizer puts crmentity on the left side and considerably slow down
		$query = preg_replace("/[\n\r\t]+/"," ",$query); //crmv@20049
		if(strripos($query, ' WHERE ') !== false) {
			vtlib_setup_modulevars($module, $this);
		//crmv@7221
		if ($this->getListViewAdvSecurityParameter_check($module)){
			global $current_user;
			$userid_conditions = $this->getNonAdminAccessControlQuery_onlyquery($module,$current_user,$scope);
			$adv_filter_conditions = $this->getListViewAdvSecurityParameter($module,$scope);
			if (!$userid_conditions)
				$add = $adv_filter_conditions;
			else
				$add = " exists ($userid_conditions $adv_filter_conditions)";
		}
		//crmv@7221e
		if ($add)
			$query .= " or ". $add;
		}
		return $query;
	}
	//crmv@9434
	function get_transitions_history($id, $cur_tab_id, $rel_tab_id, $actions=false){
		global $currentModule, $app_strings, $singlepane_view, $current_user;
		$parenttab = getParentTab();
		//crmv@31357
		$trans_obj = CRMEntity::getInstance('Transitions');
		$trans_obj->Initialize(getTabName($cur_tab_id));
		//crmv@31357e
		$return_value = $trans_obj->get_transitions_history($id);
		return $return_value;
	}
	//crmv@9434 end
	//crmv@25403
	public function getFixedOrderBy($module,$order_by,$sorder){
		global $adb, $table_prefix;

		$webservice_field = WebserviceField::fromQueryResult($adb,$adb->pquery('select * from '.$table_prefix.'_field where tabid = ? and columnname = ?',array(getTabid($module),$order_by)),0);
		$reference_modules=$webservice_field->getReferenceList();
		if(!empty($reference_modules) && is_array($reference_modules)){
			$return=array();
			foreach($reference_modules as $reference_module){
				$query = "select fieldname,tablename,entityidfield from ".$table_prefix."_entityname where modulename = ?";
				$result = $adb->pquery($query, array($reference_module));
				$order_by = $adb->query_result($result,0,'fieldname');
				$tablename = $adb->query_result($result,0,'tablename');
				//mycrmv@28901
				if ($tablename == 'vtiger_users' && $order_by != 'smownerid') {
					$tablename = 'vtiger_users2';
				}				
				//mycrmv@28901e
				$order_by = explode(',', $order_by);
				if (is_array($order_by) && !empty($order_by)) {
					foreach ($order_by as $oby) {
						$return[]=$tablename .'.'. $oby . ' ' . $sorder;
					}
				}
			}
			return  ' ORDER BY ' . implode(' , ',$return);
		}
		else{
			$tablename = getTableNameForField($module, $order_by);
			$tablename = ($tablename != '')? ($tablename . '.') : '';
			//mycrmv@37823
			if($adb->isMssql()){
				$type = $webservice_field->getFieldDataType();

				if ($type == 'text' || $type == 'multipicklist') {
					$order_by = "cast({$tablename}{$order_by} as varchar(5000))";
					return  ' ORDER BY ' . $order_by. ' ' . $sorder;
				}
			}
			//mycrmv@37823e
			return  ' ORDER BY ' . $tablename . $order_by . ' ' . $sorder;
		}
	}
	//crmv@25403e
	/**
	 * Get list view query (send more WHERE clause condition if required)
	 */
	function getListQuery($module, $where='') {
		global $current_user, $table_prefix;

		$query = "SELECT ".$table_prefix."_crmentity.*, $this->table_name.*";

		// Select Custom Field Table Columns if present
		if(!empty($this->customFieldTable)) $query .= ", " . $this->customFieldTable[0] . ".* ";

		$query .= " FROM $this->table_name";

		$query .= "	INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = $this->table_name.$this->table_index";

		// Consider custom table join as well.
		if(!empty($this->customFieldTable)) {
			$query .= " INNER JOIN ".$this->customFieldTable[0]." ON ".$this->customFieldTable[0].'.'.$this->customFieldTable[1] .
			" = $this->table_name.$this->table_index";
		}
		$query .= " LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid";
		$query .= " LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid";

		$linkedModulesQuery = $this->db->pquery("SELECT distinct fieldname, columnname, relmodule FROM ".$table_prefix."_field" .
				" INNER JOIN ".$table_prefix."_fieldmodulerel ON ".$table_prefix."_fieldmodulerel.fieldid = ".$table_prefix."_field.fieldid" .
				" WHERE uitype='10' AND ".$table_prefix."_fieldmodulerel.module=?", array($module));
		$linkedFieldsCount = $this->db->num_rows($linkedModulesQuery);

		for($i=0; $i<$linkedFieldsCount; $i++) {
			$related_module = $this->db->query_result($linkedModulesQuery, $i, 'relmodule');
			$fieldname = $this->db->query_result($linkedModulesQuery, $i, 'fieldname');
			$columnname = $this->db->query_result($linkedModulesQuery, $i, 'columnname');

			$other = CRMEntity::getInstance($related_module);
			vtlib_setup_modulevars($related_module, $other);

			//crmv@26198: fix for uitype 10, parentid
			if ($other->table_name == $this->table_name) {
				$query .= " LEFT JOIN $other->table_name AS {$other->table_name}2 ON {$other->table_name}2.$other->table_index = $this->table_name.$columnname";
			} else {
				$query .= " LEFT JOIN $other->table_name ON $other->table_name.$other->table_index = $this->table_name.$columnname";
			}
			//crmv@26198e
		}
		//crmv@31775
	    $reportFilterJoin = '';
		$viewId = $_SESSION['lvs'][$module]['viewname'];
		if (isset($_REQUEST['viewname']) && $_REQUEST['viewname'] != '') {
			$viewId = $_REQUEST['viewname'];
		}
		if ($viewId != '') {
		    $oCustomView = new CustomView($module);
			$reportFilter = $oCustomView->getReportFilter($viewId);
			if ($reportFilter) {
				$tableNameTmp = $oCustomView->getReportFilterTableName($reportFilter,$current_user->id);
				$query .= " INNER JOIN $tableNameTmp ON $tableNameTmp.id = {$table_prefix}_crmentity.crmid";
			}
		}
		//crmv@31775e
		// crmv@30014
		if (method_exists($this, 'getQueryExtraJoin')) {
			$extraJoin = $this->getQueryExtraJoin();
			$query .= " $extraJoin";
		}
		if (method_exists($this, 'getQueryExtraWhere')) {
			$where .= " ".$this->getQueryExtraWhere();
		}
		// crmv@30014e
		$query .= $this->getNonAdminAccessControlQuery($module,$current_user);
		$query .= "	WHERE ".$table_prefix."_crmentity.deleted = 0 ".$where;
		$query = $this->listQueryNonAdminChange($query, $module);
		return $query;
	}
	//crmv@26631	//crmv@29506
	function getQuickCreateDefault($module, $qcreate_array, $search_field, $search_text, $squirrelvalues='') {
		global $table_prefix;
		if ($search_field != '' && $search_text != '') {
			$col_fields[$search_field] = strtolower($search_text);
		}
		$email = '';
		if (!empty($qcreate_array)) {
			foreach($qcreate_array['form'] as $row) {
				foreach($row as $field) {
					if ($field[2][0] == $search_field && $field[0][0] == 13) {
						$email = strtolower($search_text);
						break;
					}
				}
			}
		}
		if ($email != '') {
			$tmp = explode('@',$email);
			if ($tmp[1] != '') {
				$company = ucfirst($tmp[1]);
				$pos = strpos($company, '.');
				if ($pos !== false) {
					$company = substr($company,0,$pos);
				}
				$website = 'www.'.$tmp[1];
			}
			if ($tmp[0] != '') {
				$tmp[0] = preg_replace('/[0-9-]+/','',$tmp[0]);
				$lastname = ucfirst($tmp[0]);
				$firstname = '';
				$separator = '.';
				$pos = strpos($tmp[0], $separator);
				if ($pos === false) {
					$separator = '_';
					$pos = strpos($tmp[0], $separator);
				}
				if ($pos !== false) {
					$firstname = trim(ucfirst(substr($tmp[0],0,$pos)));
					$lastname = trim(ucwords(str_replace($separator,' ',substr($tmp[0],$pos))));
				}
			}
			switch ($module) {
				case 'Accounts':
					$col_fields['accountname'] = $company;
					$col_fields['website'] = $website;
					break;
				case 'Leads':
					$col_fields['lastname'] = $lastname;
					$col_fields['firstname'] = $firstname;
					$col_fields['company'] = $company;
					$col_fields['website'] = $website;
					break;
				case 'Contacts':
					$col_fields['lastname'] = $lastname;
					$col_fields['firstname'] = $firstname;
					break;
				case 'Vendors':
					$col_fields['vendorname'] = trim($firstname.' '.$lastname);
					$col_fields['website'] = $website;
					break;
			}
		}
		if ($squirrelvalues != '') {
			global $current_user, $adb;
			$squirrelvalues = Zend_Json::decode(urldecode($squirrelvalues));
			//crmv@27811
			$column = 'uid';
			$adb->format_columns($column);
			$result = $adb->pquery('SELECT email_subject_sort, email_date, email_from, email_from_sort, email_to, email_to_sort, email_cc, body FROM vte_mailcache_list
									INNER JOIN vte_mailcache_messages ON vte_mailcache_messages.userid = vte_mailcache_list.userid AND vte_mailcache_messages.'.$column.' = vte_mailcache_list.'.$column.' AND vte_mailcache_messages.folder = vte_mailcache_list.folder
									WHERE vte_mailcache_list.userid = ? AND vte_mailcache_list.'.$column.' = ? AND vte_mailcache_list.folder = ?',
									array($current_user->id,$squirrelvalues['passed_id'],$squirrelvalues['mailbox']));
			//crmv@27811e
			if ($result && $adb->num_rows($result) > 0) {
				$row = $adb->fetch_array_no_html($result);

				if ($squirrelvalues['mailbox'] == $squirrelvalues['sent_folder']) {
					$email = $row['email_to'];
				} else {
					$email = $row['email_from'];
				}
				if ($email != '') {
					if (strrpos($email,'<') !== false) {
						$email = substr($email,strrpos($email,'<')+1,-1);
					}
				}
				$contactid = $accountid = '';
				if(vtlib_isModuleActive('Contacts')){
					$result1 = $adb->pquery('SELECT contactid, accountid FROM '.$table_prefix.'_contactdetails
											INNER JOIN '.$table_prefix.'_crmentity ON '.$table_prefix.'_crmentity.crmid = '.$table_prefix.'_contactdetails.contactid
											WHERE deleted = 0 AND email = ?',array($email));
					if ($result1 && $adb->num_rows($result1) > 0) {
						$row1 = $adb->fetch_array_no_html($result1);
						$contactid = $row1['contactid'];
						$accountid = $row1['accountid'];
					}
				}
				if ($contactid == '' && vtlib_isModuleActive('Accounts')) {
					$result1 = $adb->pquery('SELECT * FROM '.$table_prefix.'_account
											INNER JOIN '.$table_prefix.'_crmentity ON '.$table_prefix.'_crmentity.crmid = '.$table_prefix.'_account.accountid
											WHERE deleted = 0 AND (email1 = ? OR email2 = ?)',array($email,$email));
					if ($result1 && $adb->num_rows($result1) > 0) {
						$row1 = $adb->fetch_array_no_html($result1);
						$accountid = $row1['accountid'];
					}
				}

				switch ($module) {
					case 'HelpDesk':
						$col_fields['ticket_title'] = $row['email_subject_sort'];
						($contactid != '') ? $col_fields['parent_id'] = $contactid : $col_fields['parent_id'] = $accountid;
						if (isset($this->column_fields['email_from'])) {
							$col_fields['email_from'] = $row['email_from'];
						}
						if (isset($this->column_fields['email_to'])) {
							$col_fields['email_to'] = $row['email_to'];
						}
						if (isset($this->column_fields['email_cc'])) {
							$col_fields['email_cc'] = $row['email_cc'];
						}
						break;
					case 'Potentials':
						$col_fields['potentialname'] = $row['email_subject_sort'];
						($contactid != '') ? $col_fields['related_to'] = $contactid : $col_fields['related_to'] = $accountid;
						break;
					case 'Calendar':
					case 'Events':
						$col_fields['subject'] = $row['email_subject_sort'];
						break;
					case 'ProjectPlan':
						$col_fields['projectname'] = $row['email_subject_sort'];
						($contactid != '') ? $col_fields['linktoaccountscontacts'] = $contactid : $col_fields['linktoaccountscontacts'] = $accountid;
						break;
					case 'ProjectTask':
						$col_fields['projecttaskname'] = $row['email_subject_sort'];
						break;
				}
				$col_fields['description'] = $row['body'];
			}
		}
		return $col_fields;
	}
	//crmv@26631e	//crmv@29506e
	//crmv@29579
	function get_changelog_list($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		return self::get_dependents_list($id, $cur_tab_id, $rel_tab_id, $actions);
	}
	//crmv@29579e
}
?>