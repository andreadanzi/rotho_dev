<?php
global $sdk_mode, $current_user, $default_timezone; // crmv@25610

switch($sdk_mode) {
	case '':
	case 'create':
		// crmv@25610
		if ($fieldname == 'user_timezone') {
			$col_fields[$fieldname] = $default_timezone;
		}
		// crmv@25610e
	case 'edit':
		if ($fieldname == 'exchange_sync_ldap') {
			$readonly = 100;
			$success = true;
		}
	case 'detail':
		if ($fieldname == 'exchange_password' && $col_fields['exchange_sync_ldap'] == 1) {
			$readonly = 99;
			$success = true;
		}
		break;
}
if (in_array($fieldname,array('allow_generic_talks','receive_public_talks'))) {
	if ($sdk_mode == '') {
		$col_fields[$fieldname] = 1;
		$success = true;
	}
	if (!is_admin($current_user)) {
		$readonly = 100;
		$success = true;
	}
}
?>