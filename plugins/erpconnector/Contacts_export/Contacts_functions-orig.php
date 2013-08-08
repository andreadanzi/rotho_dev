<?php
function do_export_contacts($time_start) {
	global $adb,$seq_log,$current_user,$root_directory;	
	// CONTATTI
	$sql = "
		select  
		vtiger_contactdetails.contact_no as number, 
		vtiger_contactdetails.firstname as person_givenname, 
		vtiger_contactdetails.lastname as person_surname,
		1 as human,
		vtiger_contactaddress.mailingzip as addressdata_postalcode,
		vtiger_contactaddress.mailingstreet as addressdata_street,
		vtiger_contactaddress.mailingcity as addressdata_city,
		vtiger_contactaddress.mailingstate as addressdata_region_code,
		vtiger_contactaddress.mailingcountry as addressdata_country_isocode,
		' ' as language_isocode,
		vtiger_account.external_code as maintainingorganization_number,
		' ' as employee_jobcategory_name,
		' ' as employee_partnerrelations_sourceorganizationalunit_number,
		' ' as employee_partnerrelations_target_number,
		' ' as employee_partnerrelations_type_name,
		vtiger_contactscf.cf_1015 as rotho_emailconfirmationorder,
		vtiger_contactscf.cf_1016 as rotho_emaildeliveryslip,
		vtiger_contactscf.cf_1014 as rotho_emailcustomerinvoice,
		vtiger_contactscf.cf_1017 as rotho_emaildunning
		from 
		vtiger_contactdetails, vtiger_contactaddress, vtiger_contactscf, vtiger_crmentity,  vtiger_account
		where
		vtiger_contactdetails.contactid = vtiger_crmentity.crmid
		and 
		vtiger_contactdetails.contactid = vtiger_contactaddress.contactaddressid
		and 
		vtiger_contactdetails.contactid = vtiger_contactscf.contactid
		and 
		vtiger_crmentity.deleted = 0
		and  vtiger_account.accountid = vtiger_contactdetails.accountid
	";
	$fp = fopen($exportdir.'1_import_contatto.csv', 'w');
	fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
	fputs($fp,mb_convert_encoding("com.cisag.app.general.obj.Partner\n\r","UCS-2LE","UTF-8"));
	fputs($fp,"\n");
	fputcsv($fp, array('number','person.givenName','person.surname','human','addressData.postalCode','addressData.street','addressData.city','addressData.Region.code','addressData.Country.isoCode','Language.isoCode','MaintainingOrganization.number','Employee.JobCategory.name','Employee.PartnerRelations.SourceOrganizationalUnit.number','Employee.PartnerRelations.Target.number','Employee.PartnerRelations.Type.name','rotho_emailConfirmationOrder','rotho_emailDeliverySlip','rotho_emailCustomerInvoice','rotho_emailDunning'),chr(9));
	$recordSelected = 0;
	$result = $adb->query($sql);
	while ($row = $adb->fetchByAssoc($result,-1,false)) {
		fputcsv( $fp, array($row['number'],$row['person_givenname'],$row['person_surname'],$row['human'],$row['addressdata_postalcode'],$row['addressdata_street'],$row['addressdata_city'],$row['addressdata_region_code'],$row['addressdata_country_isocode'],$row['language_isocode'],$row['maintainingorganization_number'],$row['employee_jobcategory_name'],$row['employee_partnerrelations_sourceorganizationalunit_number'],$row['employee_partnerrelations_target_number'],$row['employee_partnerrelations_type_name'],$row['rotho_emailconfirmationorder'],$row['rotho_emaildeliveryslip'],$row['rotho_emailcustomerinvoice'],$row['rotho_emaildunning']), chr(9));
		$recordSelected++;
	}
	fclose($fp);
	// RECAPITI CONTATTI
	$sql = "
	select vtiger_contactdetails.contact_no as number, vtiger_contactdetails.email as contact_value, 0 as preferred ,'email' as contact_type
	from vtiger_contactdetails, vtiger_crmentity
	where vtiger_crmentity.deleted = 0 and vtiger_crmentity.crmid=vtiger_contactdetails.contactid and vtiger_contactdetails.email is not null and  vtiger_contactdetails.email <>''
	union
	select vtiger_contactdetails.contact_no as number, vtiger_contactdetails.otheremail as contact_value,0 as preferred , 'email' as contact_type
	from vtiger_contactdetails, vtiger_crmentity
	where vtiger_crmentity.deleted = 0 and vtiger_crmentity.crmid=vtiger_contactdetails.contactid and vtiger_contactdetails.otheremail is not null and  vtiger_contactdetails.otheremail <>''
	union
	select vtiger_contactdetails.contact_no as number, vtiger_contactdetails.fax as contact_value, 0 as preferred ,'fax' as contact_type
	from vtiger_contactdetails, vtiger_crmentity
	where vtiger_crmentity.deleted = 0 and vtiger_crmentity.crmid=vtiger_contactdetails.contactid and vtiger_contactdetails.fax is not null and  vtiger_contactdetails.fax <>''
	union
	select vtiger_contactdetails.contact_no as number, vtiger_contactdetails.phone as contact_value, 1 as preferred , 'phone' as contact_type
	from vtiger_contactdetails, vtiger_crmentity
	where vtiger_crmentity.deleted = 0 and vtiger_crmentity.crmid=vtiger_contactdetails.contactid and vtiger_contactdetails.phone is not null and  vtiger_contactdetails.phone <>''
	union
	select vtiger_contactdetails.contact_no as number, vtiger_contactdetails.mobile as contact_value, 0 as preferred , 'mobile' as contact_type
	from vtiger_contactdetails, vtiger_crmentity
	where vtiger_crmentity.deleted = 0 and vtiger_crmentity.crmid=vtiger_contactdetails.contactid and vtiger_contactdetails.mobile is not null and  vtiger_contactdetails.mobile <>''
	union
	select vtiger_contactdetails.contact_no as number, vtiger_contactsubdetails.otherphone as contact_value, 0 as preferred ,'phone' as contact_type
	from vtiger_contactdetails, vtiger_crmentity, vtiger_contactsubdetails
	where vtiger_crmentity.deleted = 0 and vtiger_crmentity.crmid=vtiger_contactdetails.contactid and vtiger_contactsubdetails.contactsubscriptionid = vtiger_contactdetails.contactid and vtiger_contactsubdetails.otherphone  is not null and  vtiger_contactsubdetails.otherphone  <>''
	order by number
	";
	$fp = fopen($exportdir.'2_import_contatto_recapiti.csv', 'w');
	fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
	fputs($fp,"com.cisag.app.general.obj.Partner\n\r");
	fputs($fp,"\n");
	fputcsv($fp, array('number','CommunicationData.commData','CommunicationData.preferred','CommunicationData.Method.name'),chr(9));
	$result = $adb->query($sql);
	while ($row = $adb->fetchByAssoc($result,-1,false)) {
		$newarray = array($row['number'],$row['contact_value'],$row['preferred'],$row['contact_type']);
		fputcsv($fp, $newarray,chr(9));
	}
	fclose($fp);
	// COLLEGAMENTI CONTATTI
	$sql = "
	select 
	vtiger_account.external_code as number,
	' ' as sourceorganizationalunit_number,
	vtiger_contactdetails.contact_no as target_number,
	'contact' as type_name
	from vtiger_contactdetails, vtiger_crmentity, vtiger_account
	where 
	vtiger_crmentity.deleted = 0 and 
	vtiger_crmentity.crmid=vtiger_contactdetails.contactid and
	vtiger_account.accountid = vtiger_contactdetails.accountid and
	vtiger_account.external_code is not null and vtiger_account.external_code <> ''
	order by account_no
	";
	$fp = fopen($exportdir.'3_import_contatto_collegamenti.csv', 'w');
	fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
	fputs($fp,"com.cisag.app.general.obj.Partner\n\r");
	fputs($fp,"\n");
	fputcsv($fp, array('number','PartnerRelations.SourceOrganizationalUnit.number','PartnerRelations.Target.number','PartnerRelations.Type.name'),chr(9));
	$result = $adb->query($sql);
	while ($row = $adb->fetchByAssoc($result,-1,false)) {
		fputcsv( $fp, array($row['number'],$row['sourceorganizationalunit_number'],$row['target_number'],$row['type_name']), chr(9));	
	}
	fclose($fp);
	return array('records_selected'=>$recordSelected);
}
?>
