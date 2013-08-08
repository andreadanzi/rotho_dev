<?php
function do_import_accounts($time_start) {
	global $adb,$seq_log,$current_user,$mapping,$root_directory,$external_code,$module,$table,$fields_auto_create,$fields_auto_update,$where;	
	$import = new importer($module,$mapping,$external_code,$time_start,$fields_auto_create,$fields_auto_update);
//	$sql="select ".implode(",",array_keys($mapping))." from $table $where";
	
	$array_key=array_keys($mapping);
	$key = array_search('CUSTOMER_ZONE__DESC', $array_key);
	$array_key[$key]="customer_zone+' / '+customer_zonedesc as CUSTOMER_ZONE__DESC";
	$key = array_search('CUSTOMER_CATEGORY__DESC', $array_key);
	$array_key[$key]="customer_category+' / '+customer_categorydesc as CUSTOMER_CATEGORY__DESC";
	$key = array_search('CUSTOMER_PRICE__DESC', $array_key);
	$array_key[$key]="customer_price+' / '+customer_pricedesc as CUSTOMER_PRICE__DESC";
	$key = array_search('FINANCE_PAYMENTTERMS__DESC', $array_key);
	$array_key[$key]="finance_paymentterms+' / '+finance_paymenttermsdesc as FINANCE_PAYMENTTERMS__DESC";
	$key = array_search('AGENT_NUMBER1', $array_key);
	$array_key[$key]="AGENT_NUMBER as AGENT_NUMBER1";
	$key = array_search('FINANCE_RATINGCLIENTE', $array_key);
	$array_key[$key]="(CASE WHEN FINANCE_RATINGCLIENTE = '1' THEN 'A' 
		WHEN FINANCE_RATINGCLIENTE = '2' THEN 'B'
		WHEN FINANCE_RATINGCLIENTE = '3' THEN 'C'
		WHEN FINANCE_RATINGCLIENTE = '4' THEN 'D'
		WHEN FINANCE_RATINGCLIENTE = '5' THEN 'E'
		ELSE '---' END) AS FINANCE_RATINGCLIENTE";
	
	
	
	$sql="select ".implode(",",$array_key)." from $table $where";
	$import_info = $import->go($sql);

	
	foreach($import_info['external_code_rows'] as $ext_cod){
		if (!in_array($ext_cod,$import_info['upd_ext_codes'])) {
			import_accounts_info($ext_cod);
		}
	}
	
	return $import_info;
}

function import_accounts_info($ext_cod){
	global $adb,$table_info,$external_code_info;	
	$q="select * from $table_info where $external_code_info = ?";
	$res=$adb->pquery($q,array($ext_cod));
	if($res && $adb->num_rows($res) > 0){
		while($row=$adb->fetchByAssoc($res,-1,false)){
			$field="";
			switch($row['contact_media']){
				case 'TELEFONO':
					if($row['contact_typecode'] == '100') $field='phone';
					// if($row['contact_typecode'] == '120') $field='mobile';
					if($row['contact_typecode'] == '110') $field='otherphone';
				break;
				case 'FAX':
					if($row['contact_typecode'] == '200') $field='fax';
				break;
				case 'WWW':
					if($row['contact_typecode'] == '400') $field='website';
				break;
				case 'EMAIL':
					if($row['contact_typecode'] == '300') $field='email1';
					if($row['contact_typecode'] == '310') $field='email2';
				break;
				
			}
			if( empty($field) ) continue;
			$updt_query="update vtiger_account 
						set $field=?
						from vtiger_account
						inner join vtiger_crmentity 
						on vtiger_account.accountid=vtiger_crmentity.crmid and deleted=0
						 where external_code=?";
			$adb->pquery($updt_query,array($row['contact_commdata'],$ext_cod));
		}
	}
}
?>