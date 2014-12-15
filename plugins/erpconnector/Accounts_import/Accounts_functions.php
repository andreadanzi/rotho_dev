<?php
// danzi.tn@20141212 nova classificazione cf_762 sostituito con vtiger_account.account_line
function do_import_accounts($time_start) {
	global $adb,$seq_log,$current_user,$mapping,$root_directory,$external_code,$module,$table,$fields_auto_create,$fields_auto_update,$where;	
	$import = new importer($module,$mapping,$external_code,$time_start,$fields_auto_create,$fields_auto_update);
//	$sql="select ".implode(",",array_keys($mapping))." from $table $where";
	
	$array_key=array_keys($mapping);
	$key = array_search('CUSTOMER_ZONE__DESC', $array_key);
	$array_key[$key]="customer_zone+' / '+customer_zonedesc as CUSTOMER_ZONE__DESC";
    //danzi.tn@20141126 nuova classificazione
    $key = array_search('NEW_CATEGORY_DESC', $array_key);
    $array_key[$key]="(CASE 
	WHEN CUSTOMER_CATEGORYDESC ='CARP' THEN 'RC / CARP' 
	WHEN CUSTOMER_CATEGORYDESC ='GDO' THEN 'GD / GDO'
	WHEN CUSTOMER_CATEGORYDESC ='PROG' THEN '---'    
	WHEN CUSTOMER_CATEGORYDESC ='SAFE' THEN 'RS / SAFE'     
	WHEN CUSTOMER_CATEGORYDESC ='DIST' THEN 'RD / DIST'
    WHEN CUSTOMER_CATEGORYDESC ='DIPENDENTE INTERNO' OR 
         CUSTOMER_CATEGORYDESC ='FORNITORE' OR 
         CUSTOMER_CATEGORYDESC ='AGENTE' OR 
         CUSTOMER_CATEGORYDESC ='***ALTRO'
         THEN 'RR / DIREZ'
    ELSE  '---'
    END) AS NEW_CATEGORY_DESC";
    //danzi.tn@20141126e
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
    // danzi.tn@20141212 nova classificazione per i PROG....si va a prendetre l'agente
	$updt_query="UPDATE vtiger_account
                    SET vtiger_account.account_line = 
                    CASE
                     WHEN erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC IS NULL OR  
                          erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'non definito' OR
                          erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = ''   
                          THEN 
                            CASE 
                                WHEN vtiger_accountscf.cf_762 = '' THEN  '---'
                                WHEN vtiger_accountscf.cf_762 IS NULL THEN  '---'
                                WHEN CHARINDEX( 'CARP', vtiger_accountscf.cf_762) > 0 THEN  'RC / CARP' 
                                WHEN CHARINDEX( 'SAFE', vtiger_accountscf.cf_762) > 0 THEN  'RS / SAFE'
                                WHEN CHARINDEX( 'DIST', vtiger_accountscf.cf_762) > 0 THEN  'RD / DIST'
                                WHEN CHARINDEX( 'GDO', vtiger_accountscf.cf_762) > 0 THEN  'GD / GDO'
                                WHEN CHARINDEX( 'PROG', vtiger_accountscf.cf_762) > 0 THEN  '---'
                                WHEN CHARINDEX( 'DIPENDENTE INTERNO', vtiger_accountscf.cf_762) > 0 OR
                                    CHARINDEX( 'FORNITORE', vtiger_accountscf.cf_762) > 0 OR
                                    CHARINDEX( 'AGENTE', vtiger_accountscf.cf_762) > 0 OR
                                    CHARINDEX( 'ASS', vtiger_accountscf.cf_762) > 0 OR
                                    CHARINDEX( 'ORGANIZZAZIONE', vtiger_accountscf.cf_762) > 0 OR
                                    CHARINDEX( 'ALTRO', vtiger_accountscf.cf_762) > 0 
                                    THEN  'RR / DIREZ'
                                ELSE  '---'
                            END 
                     WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'CARP' THEN  'RC / CARP' 
                     WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'SAFE' THEN  'RS / SAFE'
                     WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'DIST' THEN  'RD / DIST'
                     WHEN  erp_temp_crm_agenti.AGENT_LINEAVENDITA_DESC = 'INDUST' THEN 'RR / DIREZ'
                     ELSE  '---'
                    END
                    FROM vtiger_account
                    JOIN vtiger_crmentity accent on vtiger_account.accountid = accent.crmid AND accent.deleted = 0
                    JOIN vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid 
                    JOIN vtiger_users ON vtiger_users.id = accent.smownerid
                    JOIN erp_temp_crm_agenti ON vtiger_users.user_name = erp_temp_crm_agenti.AGENT_USERNAME";
    $adb->query($updt_query);
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