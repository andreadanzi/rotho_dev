<?php

// danzi.tn@20140213  - bisogna aggiungere il criterio vtiger_contactscf.cf_1229 <> 'XXX' in produzione è cf_1229 in _test è cf_1239
function do_export_contacts($time_start) {
	global $adb,$seq_log,$current_user,$root_directory;	
	$invalid_characters = array("$", "%", "#", "<", ">", "|","\n","\r");
	$exportdir = "plugins/erpconnector/Contacts_export/exportdir/bzrv/";
	// CONTATTI
	$sql = "
		SELECT   
		CASE
			WHEN (vtiger_contactdetails.ext_code IS NOT NULL AND RTRIM(vtiger_contactdetails.ext_code) <> '')
			THEN vtiger_contactdetails.ext_code
			WHEN LEFT(vtiger_contactdetails.contact_no,3) = 'CON'
			THEN 'ZZZ' + REPLACE(STR(CONVERT ( INT, SUBSTRING(vtiger_contactdetails.contact_no,4,LEN(vtiger_contactdetails.contact_no)-3)),7), ' ' , '0')
			ELSE
			LEFT(vtiger_contactdetails.contact_no,3)
		END	
		AS number, 
		vtiger_contactdetails.firstname as person_givenname, 
		vtiger_contactdetails.lastname as person_surname,
		'true' as human,
		vtiger_contactaddress.mailingzip as addressdata_postalcode,
		vtiger_contactaddress.mailingstreet as addressdata_street,
		vtiger_contactaddress.mailingcity as addressdata_city,
		vtiger_contactaddress.mailingstate as addressdata_region_code,
		vtiger_contactaddress.mailingcountry as addressdata_country_isocode,
		'it' as language_isocode,
		'IT000' as maintainingorganization_number,
		CASE
			WHEN vtiger_contactdetails.title = '2' THEN '099' 
			WHEN vtiger_contactdetails.title = '3' THEN '100' 
			WHEN vtiger_contactdetails.title = '7' THEN '160' 
			WHEN vtiger_contactdetails.title = '1' THEN '300' 
			WHEN vtiger_contactdetails.title = '6' THEN '520' 
			WHEN vtiger_contactdetails.title = '4' THEN '600'
			WHEN vtiger_contactdetails.title = '5' THEN '700'
			WHEN vtiger_contactdetails.title = '8' THEN '999' 
			ELSE ' ' 
		END 
		AS employee_jobcategory_name,
		'IT000' as employee_source_number,
		vtiger_account.external_code as employee_target_number,
		'Employee' as employee_type_name,
		vtiger_contactscf.cf_1015 as rotho_emailconfirmationorder,
		vtiger_contactscf.cf_1016 as rotho_emaildeliveryslip,
		vtiger_contactscf.cf_1014 as rotho_emailcustomerinvoice,
		vtiger_contactscf.cf_1017 as rotho_emaildunning,
		vtiger_contactscf.cf_1018 as rotho_tracking
		FROM 
		vtiger_contactdetails
		INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid AND vtiger_crmentity.deleted = 0
		INNER JOIN vtiger_contactaddress ON vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid
		INNER JOIN vtiger_contactscf ON vtiger_contactscf.contactid = vtiger_contactdetails.contactid AND (vtiger_contactscf.cf_1229 IS NULL OR vtiger_contactscf.cf_1229 <> 'XXX')  AND (vtiger_contactscf.cf_1170 IS NULL OR ( DATEADD(second,75,vtiger_contactscf.cf_1170) < DATEADD(hh, DATEDIFF(hh, GETDATE(), GETUTCDATE())-1,vtiger_crmentity.modifiedtime) ))
		INNER JOIN vtiger_account ON vtiger_account.accountid = vtiger_contactdetails.accountid AND vtiger_account.external_code IS NOT NULL AND vtiger_account.external_code <>''
	";
	$fp = fopen($exportdir.'01x_import_contatto.csv', 'w');
	fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
	fputs($fp,"com.cisag.app.general.obj.Partner\n\r");
	fputs($fp,"\n");
	fputcsv($fp, array('number','person.givenName','person.surname','human','addressData.postalCode','addressData.street','addressData.city','addressData.Region.code','addressData.Country.isoCode','Language.isoCode','MaintainingOrganization.number','Employee.JobCategory.name','Employee.PartnerRelations.SourceOrganizationalUnit.number','Employee.PartnerRelations.Target.number','Employee.PartnerRelations.Type.name','rotho_emailConfirmationOrder','rotho_emailDeliverySlip','rotho_emailCustomerInvoice','rotho_emailDunning','rotho_tracking'),chr(9));
	$recordSelected = 0;
	$result01x = $adb->query($sql);
	while ($row = $adb->fetchByAssoc($result01x)) {
		fputcsv( $fp, array($row['number'],str_replace($invalid_characters, "",$row['person_givenname']),
		str_replace($invalid_characters, "",$row['person_surname']),
		$row['human'],
		$row['addressdata_postalcode'],
		str_replace($invalid_characters, "", $row['addressdata_street']),
		str_replace($invalid_characters, "",$row['addressdata_city']),
		$row['addressdata_region_code'],
		$row['addressdata_country_isocode'],
		$row['language_isocode'],
		$row['maintainingorganization_number'],
		$row['employee_jobcategory_name'],
		$row['employee_source_number'],$row['employee_target_number'],
		$row['employee_type_name'],
		($row['rotho_emailconfirmationorder']==''?'false':($row['rotho_emailconfirmationorder']==1?'true':'false')),
		($row['rotho_emaildeliveryslip']==''?'false':($row['rotho_emaildeliveryslip']==1?'true':'false')),
		($row['rotho_emailcustomerinvoice']==''?'false':($row['rotho_emailcustomerinvoice']==1?'true':'false')),
		($row['rotho_emaildunning']==''?'false':($row['rotho_emaildunning']==1?'true':'false')),
		($row['rotho_tracking']==''?'false':($row['rotho_tracking']==1?'true':'false'))), chr(9));
		$recordSelected++;
	}
	fclose($fp);
	// danzi.tn@20130726 - UPDATE DEI CONTATTI CON ext_code NON VALORIZZATO
	$update_sql = "
		UPDATE
		vtiger_contactdetails 
		SET
		vtiger_contactdetails.ext_code = 
		CASE
			WHEN (vtiger_contactdetails.ext_code IS NOT NULL AND RTRIM(vtiger_contactdetails.ext_code) <> '')
			THEN vtiger_contactdetails.ext_code
			WHEN LEFT(vtiger_contactdetails.contact_no,3) = 'CON'
			THEN 'ZZZ' + REPLACE(STR(CONVERT ( INT, SUBSTRING(vtiger_contactdetails.contact_no,4,LEN(vtiger_contactdetails.contact_no)-3)),7), ' ' , '0')
			ELSE
			LEFT(vtiger_contactdetails.contact_no,3)
		END	
		FROM 
		vtiger_contactdetails
		INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid AND vtiger_crmentity.deleted = 0
		INNER JOIN vtiger_contactaddress ON vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid
		INNER JOIN vtiger_contactscf ON vtiger_contactscf.contactid = vtiger_contactdetails.contactid AND (vtiger_contactscf.cf_1229 IS NULL OR vtiger_contactscf.cf_1229 <> 'XXX')  AND (vtiger_contactscf.cf_1170 IS NULL OR ( DATEADD(second,75,vtiger_contactscf.cf_1170) < DATEADD(hh, DATEDIFF(hh, GETDATE(), GETUTCDATE())-1,vtiger_crmentity.modifiedtime) ))
		INNER JOIN vtiger_account ON vtiger_account.accountid = vtiger_contactdetails.accountid AND vtiger_account.external_code IS NOT NULL AND vtiger_account.external_code <>''
		WHERE vtiger_contactdetails.ext_code IS NULL OR vtiger_contactdetails.ext_code = ''
	";
	$adb->query($update_sql);
	// danzi.tn@20130726e
	// RECAPITI CONTATTI
	$sql = "
	select CASE
			WHEN (vtiger_contactdetails.ext_code IS NOT NULL AND RTRIM(vtiger_contactdetails.ext_code) <> '')
			THEN vtiger_contactdetails.ext_code
			WHEN LEFT(vtiger_contactdetails.contact_no,3) = 'CON'
			THEN 'ZZZ' + REPLACE(STR(CONVERT ( INT, SUBSTRING(vtiger_contactdetails.contact_no,4,LEN(vtiger_contactdetails.contact_no)-3)),7), ' ' , '0')
			ELSE
			LEFT(vtiger_contactdetails.contact_no,3)
		END	
		AS number, vtiger_contactdetails.email as contact_value, 'true' as preferred ,'300' as contact_type
	from vtiger_contactdetails 
	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid AND vtiger_crmentity.deleted = 0 
	INNER JOIN vtiger_contactscf ON vtiger_contactscf.contactid = vtiger_contactdetails.contactid AND (vtiger_contactscf.cf_1229 IS NULL OR vtiger_contactscf.cf_1229 <> 'XXX')  AND (vtiger_contactscf.cf_1170 IS NULL OR ( DATEADD(second,75,vtiger_contactscf.cf_1170) < DATEADD(hh, DATEDIFF(hh, GETDATE(), GETUTCDATE())-1,vtiger_crmentity.modifiedtime) ))
	INNER JOIN vtiger_account ON vtiger_account.accountid = vtiger_contactdetails.accountid AND vtiger_account.external_code IS NOT NULL AND vtiger_account.external_code <>'' 
	where vtiger_contactdetails.email is not null and  vtiger_contactdetails.email <>''
	union
	select CASE
			WHEN (vtiger_contactdetails.ext_code IS NOT NULL AND RTRIM(vtiger_contactdetails.ext_code) <> '')
			THEN vtiger_contactdetails.ext_code
			WHEN LEFT(vtiger_contactdetails.contact_no,3) = 'CON'
			THEN 'ZZZ' + REPLACE(STR(CONVERT ( INT, SUBSTRING(vtiger_contactdetails.contact_no,4,LEN(vtiger_contactdetails.contact_no)-3)),7), ' ' , '0')
			ELSE
			LEFT(vtiger_contactdetails.contact_no,3)
		END	
		AS number, vtiger_contactdetails.otheremail as contact_value,'true' as preferred , '310' as contact_type
	from vtiger_contactdetails 
	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid AND vtiger_crmentity.deleted = 0 
	INNER JOIN vtiger_contactscf ON vtiger_contactscf.contactid = vtiger_contactdetails.contactid AND (vtiger_contactscf.cf_1229 IS NULL OR vtiger_contactscf.cf_1229 <> 'XXX')  AND (vtiger_contactscf.cf_1170 IS NULL OR ( DATEADD(second,75,vtiger_contactscf.cf_1170) < DATEADD(hh, DATEDIFF(hh, GETDATE(), GETUTCDATE())-1,vtiger_crmentity.modifiedtime) ))
	INNER JOIN vtiger_account ON vtiger_account.accountid = vtiger_contactdetails.accountid AND vtiger_account.external_code IS NOT NULL AND vtiger_account.external_code <>'' 
	where  vtiger_contactdetails.otheremail is not null and  vtiger_contactdetails.otheremail <>''
	union
	select CASE
			WHEN (vtiger_contactdetails.ext_code IS NOT NULL AND RTRIM(vtiger_contactdetails.ext_code) <> '')
			THEN vtiger_contactdetails.ext_code
			WHEN LEFT(vtiger_contactdetails.contact_no,3) = 'CON'
			THEN 'ZZZ' + REPLACE(STR(CONVERT ( INT, SUBSTRING(vtiger_contactdetails.contact_no,4,LEN(vtiger_contactdetails.contact_no)-3)),7), ' ' , '0')
			ELSE
			LEFT(vtiger_contactdetails.contact_no,3)
		END	
		AS number, vtiger_contactdetails.fax as contact_value, 'true' as preferred ,'200' as contact_type
	from vtiger_contactdetails 
	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid AND vtiger_crmentity.deleted = 0 
	INNER JOIN vtiger_contactscf ON vtiger_contactscf.contactid = vtiger_contactdetails.contactid AND (vtiger_contactscf.cf_1229 IS NULL OR vtiger_contactscf.cf_1229 <> 'XXX')  AND (vtiger_contactscf.cf_1170 IS NULL OR ( DATEADD(second,75,vtiger_contactscf.cf_1170) < DATEADD(hh, DATEDIFF(hh, GETDATE(), GETUTCDATE())-1,vtiger_crmentity.modifiedtime) ))
	INNER JOIN vtiger_account ON vtiger_account.accountid = vtiger_contactdetails.accountid AND vtiger_account.external_code IS NOT NULL AND vtiger_account.external_code <>'' 
	where  vtiger_contactdetails.fax is not null and  vtiger_contactdetails.fax <>''
	union
	select CASE
			WHEN (vtiger_contactdetails.ext_code IS NOT NULL AND RTRIM(vtiger_contactdetails.ext_code) <> '')
			THEN vtiger_contactdetails.ext_code
			WHEN LEFT(vtiger_contactdetails.contact_no,3) = 'CON'
			THEN 'ZZZ' + REPLACE(STR(CONVERT ( INT, SUBSTRING(vtiger_contactdetails.contact_no,4,LEN(vtiger_contactdetails.contact_no)-3)),7), ' ' , '0')
			ELSE
			LEFT(vtiger_contactdetails.contact_no,3)
		END	
		AS number, vtiger_contactdetails.phone as contact_value, 'true' as preferred , '100' as contact_type
	from vtiger_contactdetails 
	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid AND vtiger_crmentity.deleted = 0 
	INNER JOIN vtiger_contactscf ON vtiger_contactscf.contactid = vtiger_contactdetails.contactid AND (vtiger_contactscf.cf_1229 IS NULL OR vtiger_contactscf.cf_1229 <> 'XXX')  AND (vtiger_contactscf.cf_1170 IS NULL OR ( DATEADD(second,75,vtiger_contactscf.cf_1170) < DATEADD(hh, DATEDIFF(hh, GETDATE(), GETUTCDATE())-1,vtiger_crmentity.modifiedtime) ))
	INNER JOIN vtiger_account ON vtiger_account.accountid = vtiger_contactdetails.accountid AND vtiger_account.external_code IS NOT NULL AND vtiger_account.external_code <>'' 
	where  vtiger_contactdetails.phone is not null and  vtiger_contactdetails.phone <>''
	union
	select CASE
			WHEN (vtiger_contactdetails.ext_code IS NOT NULL AND RTRIM(vtiger_contactdetails.ext_code) <> '')
			THEN vtiger_contactdetails.ext_code
			WHEN LEFT(vtiger_contactdetails.contact_no,3) = 'CON'
			THEN 'ZZZ' + REPLACE(STR(CONVERT ( INT, SUBSTRING(vtiger_contactdetails.contact_no,4,LEN(vtiger_contactdetails.contact_no)-3)),7), ' ' , '0')
			ELSE
			LEFT(vtiger_contactdetails.contact_no,3)
		END	
		AS number, vtiger_contactdetails.mobile as contact_value, 'true' as preferred , '120' as contact_type
	from vtiger_contactdetails 
	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid AND vtiger_crmentity.deleted = 0 
	INNER JOIN vtiger_contactscf ON vtiger_contactscf.contactid = vtiger_contactdetails.contactid AND (vtiger_contactscf.cf_1229 IS NULL OR vtiger_contactscf.cf_1229 <> 'XXX')  AND (vtiger_contactscf.cf_1170 IS NULL OR ( DATEADD(second,75,vtiger_contactscf.cf_1170) < DATEADD(hh, DATEDIFF(hh, GETDATE(), GETUTCDATE())-1,vtiger_crmentity.modifiedtime) ))
	INNER JOIN vtiger_account ON vtiger_account.accountid = vtiger_contactdetails.accountid AND vtiger_account.external_code IS NOT NULL AND vtiger_account.external_code <>''
	where  vtiger_contactdetails.mobile is not null and  vtiger_contactdetails.mobile <>''
	union
	select CASE
			WHEN (vtiger_contactdetails.ext_code IS NOT NULL AND RTRIM(vtiger_contactdetails.ext_code) <> '')
			THEN vtiger_contactdetails.ext_code
			WHEN LEFT(vtiger_contactdetails.contact_no,3) = 'CON'
			THEN 'ZZZ' + REPLACE(STR(CONVERT ( INT, SUBSTRING(vtiger_contactdetails.contact_no,4,LEN(vtiger_contactdetails.contact_no)-3)),7), ' ' , '0')
			ELSE
			LEFT(vtiger_contactdetails.contact_no,3)
		END	
		AS number, vtiger_contactsubdetails.homephone as contact_value, 'true' as preferred ,'110' as contact_type
	from vtiger_contactdetails
	INNER JOIN vtiger_contactsubdetails on vtiger_contactsubdetails.contactsubscriptionid = vtiger_contactdetails.contactid and  vtiger_contactsubdetails.homephone  is not null and  vtiger_contactsubdetails.homephone  <>''
	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid AND vtiger_crmentity.deleted = 0 
	INNER JOIN vtiger_contactscf ON vtiger_contactscf.contactid = vtiger_contactdetails.contactid AND (vtiger_contactscf.cf_1229 IS NULL OR vtiger_contactscf.cf_1229 <> 'XXX')  AND (vtiger_contactscf.cf_1170 IS NULL OR ( DATEADD(second,75,vtiger_contactscf.cf_1170) < DATEADD(hh, DATEDIFF(hh, GETDATE(), GETUTCDATE())-1,vtiger_crmentity.modifiedtime) ))
	INNER JOIN vtiger_account ON vtiger_account.accountid = vtiger_contactdetails.accountid AND vtiger_account.external_code IS NOT NULL AND vtiger_account.external_code <>'' 
	ORDER BY number
	";
	$fp = fopen($exportdir.'02x_import_contatto_recapiti.csv', 'w');
	fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
	fputs($fp,"com.cisag.app.general.obj.Partner\n\r");
	fputs($fp,"\n");
	fputcsv($fp, array('number','CommunicationData.commData','CommunicationData.preferred','CommunicationData.Method.name'),chr(9));
	$result02x = $adb->query($sql);
	while ($row = $adb->fetchByAssoc($result02x)) {
		$newarray = array($row['number'],$row['contact_value'],$row['preferred'],$row['contact_type']);
		fputcsv($fp, $newarray,chr(9));
	}
	fclose($fp);
	// COLLEGAMENTI CONTATTI
	$sql = "
	select 
	vtiger_account.external_code as number,	'IT000' as source_number, CASE
			WHEN (vtiger_contactdetails.ext_code IS NOT NULL AND RTRIM(vtiger_contactdetails.ext_code) <> '')
			THEN vtiger_contactdetails.ext_code
			WHEN LEFT(vtiger_contactdetails.contact_no,3) = 'CON'
			THEN 'ZZZ' + REPLACE(STR(CONVERT ( INT, SUBSTRING(vtiger_contactdetails.contact_no,4,LEN(vtiger_contactdetails.contact_no)-3)),7), ' ' , '0')
			ELSE
			LEFT(vtiger_contactdetails.contact_no,3)
		END	
		AS target_number, 'Contact' as type_name
	from vtiger_contactdetails
	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid AND vtiger_crmentity.deleted = 0 
	INNER JOIN vtiger_account ON vtiger_account.accountid = vtiger_contactdetails.accountid AND vtiger_account.external_code IS NOT NULL AND vtiger_account.external_code <>'' 
	INNER JOIN vtiger_contactscf ON vtiger_contactscf.contactid = vtiger_contactdetails.contactid AND (vtiger_contactscf.cf_1229 IS NULL OR vtiger_contactscf.cf_1229 <> 'XXX')  AND (vtiger_contactscf.cf_1170 IS NULL OR ( DATEADD(second,75,vtiger_contactscf.cf_1170) < DATEADD(hh, DATEDIFF(hh, GETDATE(), GETUTCDATE())-1,vtiger_crmentity.modifiedtime) ))
	order by account_no
	";
	$fp = fopen($exportdir.'03x_import_contatto_collegamenti.csv', 'w');
	fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
	fputs($fp,"com.cisag.app.general.obj.Partner\n\r");
	fputs($fp,"\n");
	fputcsv($fp, array('number','PartnerRelations.SourceOrganizationalUnit.number','PartnerRelations.Target.number','PartnerRelations.Type.name'),chr(9));
	$result03x = $adb->query($sql);
	while ($row = $adb->fetchByAssoc($result03x)) {
		fputcsv( $fp, array($row['number'],$row['source_number'],$row['target_number'],$row['type_name']), chr(9));	
	}
	fclose($fp);
	exec("iconv -f UTF-8 -t UCS-2LE ".$exportdir."01x_import_contatto.csv > ".$exportdir."File_01/01x_import_contatto_ucs-2le.csv");
	exec("iconv -f UTF-8 -t UCS-2LE ".$exportdir."02x_import_contatto_recapiti.csv > ".$exportdir."File_02/02x_import_contatto_recapiti_ucs-2le.csv");
	exec("iconv -f UTF-8 -t UCS-2LE ".$exportdir."03x_import_contatto_collegamenti.csv > ".$exportdir."File_03/03x_import_contatto_collegamenti_ucs-2le.csv");
	return array('records_selected'=>$recordSelected);
}
?>
