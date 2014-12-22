<?php 
//crmv@24344
include_once("../../config.inc.php");
chdir($root_directory);
include_once("config.inc.php");
require_once('include/utils/utils.php');
include_once("plugins/erpconnector/utils.php");
include_once("modules/Users/Users.php");
$current_user= new Users();
$current_user->id = 1;
global $adb;
// danzi.tn@20141217 nuova classificazione
$query="SELECT 
      AGENT_NUMBER
      ,AGENT_FULLNAME
      ,AGENT_GIVENNAME
      ,AGENT_SURNAME
      ,AGENT_ACTIVE
      ,AGENT_CODAGENTE
      ,AGENT_CODCAPOAREA
      ,AGENT_USERNAME
      ,AGENT_CN
	  ,AGENT_TIPORAPPORTO
	  ,AGENT_INTERNALUSER_NUMBER
	  ,AGENT_INTERNALUSER_FULLNAME
	  
 ,CASE	
 WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'CARP' THEN  'RC / CARP' 
 WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'SAFE' THEN  'RS / SAFE'
 WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'DIST' THEN  'RD / DIST'
 WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'INDUST' THEN 'RR / DIREZ'
 ELSE  '---'
 END AS AGENT_LINEAVENDITA_DESC
  FROM erp_temp_crm_agenti";
//mycrmv@3147e
$res=$adb->query($query);
while($row=$adb->fetchByAssoc($res,-1,false)){
	
	$erp_code=$row['agent_number'];
	$roleid='H59'; //Default Profile
	
	if($row['agent_username']=='' || $row['agent_cn']=='ND'){
		$ldap_auth=false;
		$username=$row['agent_number'];
	}
	else{
		$ldap_auth=true;
		$username=$row['agent_username'];
	}
	
	//mycrmv@26940
	$capoarea = '';
	if ($row['agent_codcapoarea'] != null && $row['agent_codcapoarea'] != ''){
		$capoarea = $row['agent_codcapoarea'];
	}//mycrmv@26940 e
	
	//mycrmv@rotho
	$tipo_rapp = '';
	if ($row['agent_tiporapporto'] != null && $row['agent_tiporapporto'] != ''){
		$tipo_rapp = $row['agent_tiporapporto'];
	}//mycrmv@rotho e
	
	// danzi.tn@20141217 nuova classificazione
	$linea = '';
	if ($row['agent_lineavendita_desc'] != null && $row['agent_lineavendita_desc'] != ''){
		$linea = $row['agent_lineavendita_desc'];
	}// danzi.tn@20141217 e
	
	// VERIFICA DELLA PRESENZA DELL'utente
	$qry = "select count(id) as presence, id  from vtiger_users where erp_code = ? or user_name =? group by id ";
	$result2=$adb->pquery($qry,array($erp_code,$username));
	$numrows2=$adb->num_rows($result2);
	$user = CRMEntity::getInstance('Users');
	if( $numrows2 > 0 && $adb->query_result($result2 , 0, 'presence') > 0) {
		$id=$adb->query_result($result2 , 0, 'id');
		$user->retrieve_entity_info($id,"Users");
		$user->mode = 'edit';
		$user->id = $id;
		$user->column_fields["roleid"] = $roleid;
	}
	else  { 
		$id="";
		$user->mode = '';
		$user->column_fields["user_name"] = $username;
		$user->column_fields["user_password"] = $username;
		$user->column_fields["confirm_password"] = $username;	
		$user->column_fields["is_admin"] = 'off';
		$user->column_fields["roleid"] = $roleid;
		$user->column_fields["email1"] = 'impostami@crm.it';
		$user->column_fields["activity_view"] = 'Today';
		$user->column_fields["lead_view"] = 'Today';
		$user->column_fields["currency_id"] = 1;
		$user->column_fields["tz"] = 'Europe/Berlin';
		$user->column_fields["holidays"] = 'de,en_uk,fr,it,us,';
		$user->column_fields["workdays"] = '0,1,2,3,4,5,6,';
		$user->column_fields["weekstart"] = '1';
		$user->column_fields["namedays"] = '';
		$user->column_fields["date_format"] = 'dd-mm-yyyy';
		$user->column_fields["hour_format"] = '24';
		$user->column_fields["start_hour"] = '08:00';
		$user->column_fields["end_hour"] = '18:00';
		$user->column_fields["imagename"] = '';
		$user->column_fields["defhomeview"] = 'home_metrics';
		$user->column_fields["description"] = '';
		
	}
	
	$user->column_fields["erp_code"] = $erp_code;
	
	//mycrmv@26940
	if ($capoarea != ''){
		$user->column_fields["agent_cod_capoarea"] = $capoarea;
	}
	//mycrmv@26940 e
	
	//mycrmv@rotho
	if ($tipo_rapp != ''){
		$user->column_fields["agent_tiporapporto"] = $tipo_rapp;
	}
	//mycrmv@rotho e
		
	//mycrmv@rotho
	if ($linea != ''){
		$user->column_fields["user_line"] = $linea;
	}
	
	//mycrmv@26940
	if($row['agent_active'] == 1){
		$user->column_fields["status"] = 'Active';
	}
	else{
		$user->column_fields["status"] = 'Inactive';
	}
	//mycrmv@26940e
	
	if($ldap_auth){
		//mycrmv@29588
		//$user->column_fields["use_ldap"]='on'; 
		$user->column_fields["use_ldap"]='off';
		//mycrmv@29588e
	}
	else{
		$user->column_fields["use_ldap"]='off';
	}
	
	$user->column_fields["first_name"] = $row['agent_givenname'];			
	if($row['agent_surname']==''){
		$user->column_fields["last_name"] =$row['agent_fullname'];
	}
	else{
		$user->column_fields["last_name"] = $row['agent_surname'];
	}

	//mycrmv@3147
	if ($row['agent_internaluser_number'] != null && $row['agent_internaluser_number'] != ''){
		$user->column_fields["referente_codice"] = $row['agent_internaluser_number'];
	}
	if ($row['agent_internaluser_fullname'] != null && $row['agent_internaluser_fullname'] != ''){
		$user->column_fields["referente_nome"] = $row['agent_internaluser_fullname'];
	}
	//mycrmv@3147e
	
	$user->saveentity('Users');
	$user->saveHomeStuffOrder($user->id);
	SaveTagCloudView($user->id);
	
	if ($user->mode != 'edit'){
		if ($user->id){
			$query_prev_interval = $adb->pquery("SELECT reminder_interval from vtiger_users where id=?",array($user->id));
			$prev_reminder_interval = $adb->query_result($query_prev_interval,0,'reminder_interval');
		}
		$user->resetReminderInterval($prev_reminder_interval);
	}
	
	if ($user->mode == 'edit'){
		file_put_contents("debug_userimport","EDIT ".$user->id." ",FILE_APPEND);
//		updateUser2RoleMapping("H7",$user->id);
//		updateUsers2GroupMapping("",$user->id);
	}
	else{
		insertUser2RoleMapping($roleid,$user->id);
		file_put_contents("debug_userimport","INSERT ".$user->id." ",FILE_APPEND);
//		insertUsers2GroupMapping("",$user->id);			
	}

	//Creating the Privileges Flat File
	require_once('modules/Users/CreateUserPrivilegeFile.php');
	createUserPrivilegesfile($user->id);
	createUserSharingPrivilegesfile($user->id);
	
	echo '.';
}

//crmv@24344e
?>