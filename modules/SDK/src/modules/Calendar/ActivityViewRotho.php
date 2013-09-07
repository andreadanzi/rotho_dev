<?php
global $sdk_mode,$table_prefix;
switch($sdk_mode) {
	case 'edit':
	case 'detail':		 
		if ($col_fields['activitytype'] != 'Visita' && ($fieldname == 'cf_890' || $fieldname == 'cf_891')) {
			$readonly = 100;
			$success = true;
		}
		break;	
}
?>