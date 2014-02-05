<?php 
include('include/utils/crmv_utils.php');
include('include/utils/UserInfoUtil.php');

/*
$roleid = fetchUserRole(getUserId($_REQUEST['role_id']));
function crmv_capoareaajax($roleid){
	global $adb;
	//mycrmv@26940

	$id_us= $_REQUEST['role_id'];
	$sql_new = "SELECT agent_cod_capoarea FROM vtiger_users WHERE id = ?";
	$result_new=$adb->pquery($sql_new,array($id_us));
	if ($result_new)
	$username_capoarea = $adb->query_result($result_new,0,'agent_cod_capoarea');
	
	if ($username_capoarea != null && $username_capoarea!= ''){
		$sql_fin = "SELECT id FROM vtiger_users WHERE user_name = ?";
		$result_fin=$adb->pquery($sql_fin,array($username_capoarea));
		if ($result_fin){
			return $adb->query_result($result_fin,0,'id');
		}
	}
	else{
		//mycrmv@26940 e
		//mi serve avere il ruolo superiore a quello dato
		//poi avere un utente avente quel ruolo
		$sql="select parentrole from vtiger_role where roleid=?";
		$res=$adb->pquery($sql,array($roleid));
		//echo $adb->convert2Sql($sql,$adb->flatten_array(array($roleid)));
		if($res && $adb->num_rows($res) > 0){
			$parentrole=$adb->query_result($res,0,'parentrole');
			$roles=explode('::',$parentrole);
			unset($roles[array_search($roleid,$roles)]);
			$sup_role=max($roles); //il ruolo superiore a quello dato
			$sql1="select userid from vtiger_user2role where roleid=?";
			$res1=$adb->pquery($sql1,array($sup_role));
			//echo $adb->convert2Sql($sql1,$adb->flatten_array(array($sup_role)));
			if($res1 && $adb->num_rows($res1) > 0){
				return $adb->query_result($res1,0,'userid'); //ritorno l'id dell'utente
			}
			else{
				return 1;
			}
		}else{
			return 1; //è l'userid dell'admin
		}
	}
}
$id_categoria= crmv_capoareaajax($roleid);
echo $id_categoria;
*/

$roleid = fetchUserRole(getUserId($_REQUEST['role_id']));
$user_id = $_REQUEST['role_id'];
function crmv_capoareaajax($user_id){
	global $adb;
	//mycrmv@26940
	$sql = "select agent_cod_capoarea from vtiger_users where id = ?";
	$res=$adb->pquery($sql,array($user_id));
	if ($res)
	$username_agente = $adb->query_result($res,0,'agent_cod_capoarea');

	if ($username_agente == '' || $username_agente == null){
		return 1;
	}
	
	$sql1 = "select id,agent_tiporapporto from vtiger_users where UPPER(user_name) = ?";
	$res1=$adb->pquery($sql1,array(strtoupper($username_agente)));
	if ($res1 && $adb->num_rows($res1) == 1){
		$id_agente = $adb->query_result($res1,0,'id');
		return $id_agente;
	}elseif ($res1 && $adb->num_rows($res1) > 1){
		while($row=$adb->fetchByAssoc($res1,-1,false)){
			$id_agente=$row['id'];
			$tipo_rapp=$row['agent_tiporapporto'];
			if($tipo_rapp == 'S'){
				return $id_agente;
			}
		}
	}else{
		return 1;
	}
	
	//return $username;
}
$id_categoria= crmv_capoareaajax($user_id);
echo $id_categoria;


?>