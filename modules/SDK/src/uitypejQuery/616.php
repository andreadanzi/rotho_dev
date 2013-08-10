<?php
/*
 * $sdk_value = $value è il valore del dato
 */
global $sdk_mode;
$imgdir = 'modules/SDK/src/uitypejQuery/img/';
switch($sdk_mode) {
	case 'insert':
		$fldvalue = $this->column_fields[$fieldname];	//non è indispensabile questo, serve solo da esempio
		break;
	case 'detail':
		$label_fld[] = getTranslatedString($fieldlabel,$module);
		$label_fld[] = $col_fields[$fieldname];
		break;
	case 'edit':
		$editview_label[] = getTranslatedString($fieldlabel, $module_name);
		$fieldvalue[] = $value; // posso modificare il parametro prima che venga inserito nella textbox per la modifica
		break;
	case 'relatedlist':
//		$value = '<span style="color: green; font-weight: bold;">'.$sdk_value.'</span>';
//		break;
	case 'list':
		if (!empty($sdk_value)) {
		  $value = '<div class="show_points" onclick="show_points(this, \'showpoints\','.$sdk_value.','.$recordId.',\'showpoints_'.$recordId.'\');"><span align="left" >'.$sdk_value.'</span>&nbsp;&times;&nbsp;<span align="left" ><img border="0" src="'.$imgdir.'star_16.png" alt="RP Prog - Rating" title="RP Prog - Rating" /></span></div>';
		  $value .= '<div id="showpoints_'.$recordId.'" class="showpoints_hidden">'.$sdk_value.'</div>';
		} else {
			$value = '';
		}
		break;
}
?>