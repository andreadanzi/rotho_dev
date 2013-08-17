<?php
/*
 * $sdk_value = $value è il valore del dato
 */
global $sdk_mode, $focus, $adb,$app_strings,$mod_strings,$smarty;
$imgdir = 'modules/SDK/src/uitypejQuery/img/';
$sql = "SELECT
		temp_acc_ratings.categoria,
		temp_acc_ratings.gruppo,
		sum(temp_acc_ratings.valore) as sumvalore,
		vtiger_account.accountname,
		vtiger_account.account_no
		from 
		 temp_acc_ratings 
		Join vtiger_account ON vtiger_account.accountid = temp_acc_ratings.accountid
		 WHERE temp_acc_ratings.accountid = ?
		group by 
		temp_acc_ratings.categoria,
		temp_acc_ratings.gruppo,
		vtiger_account.accountname,
		vtiger_account.account_no
		ORDER BY 
		temp_acc_ratings.categoria, sumvalore";
switch($sdk_mode) {
	case 'insert':
		$fldvalue = $this->column_fields[$fieldname];	//non è indispensabile questo, serve solo da esempio
		break;
	case 'detail':
		$result = $adb->pquery($sql,array($focus->id));
		$html_str = "<table id='pointstable'><tbody>";
		$totsumvalore = 0;
		$bFirst = true;
		while($row=$adb->fetchByAssoc($result))
		{
			if($bFirst) {
				$html_str .= "<tr class='pointRowSum'><td class='pointCatHead' colspan=3>{$row['account_no']}-{$row['accountname']}</td></tr>";
				$html_str .= "<tr class='pointRowSum'><td class='pointCatHead'>{$app_strings['Category']}</td><td class='pointGrpHead'>{$app_strings['LBL_ACTIVITY_TYPE']}</td><td class='pointValHead'>{$mod_strings['Points']}</td></tr>";
				$bFirst = false;
			}
			$totsumvalore += $row['sumvalore'];
			$html_str .= "<tr class='pointRow_".$row['categoria']."'><td class='pointCat'>".$mod_strings[$row['categoria']]."</td><td class='pointGrp'>".htmlspecialchars_decode($row['gruppo'])." </td><td class='pointVal'>".$row['sumvalore']."</td></tr>";
		}
		$html_str .= "<tr class='pointRowSum'><td class='pointCatSum'><button class='small show_points' type='button' onclick=\"return show_points_loaded(this, 'showpoints','{$totsumvalore}','{$focus->id}','showpoints_{$focus->id}')\">{$app_strings['LBL_CLOSE']}</button> </td><td class='pointGrpSum'>{$app_strings['LBL_TOTAL']}</td><td class='pointValSum'>".$totsumvalore."</td></tr>";
		$html_str .= "</tbody></table>";
		$smarty->assign("INNER_POINTS", $html_str);
		$label_fld[] = getTranslatedString($fieldlabel,$module);
		$label_fld[] = $col_fields[$fieldname];
		break;
	case 'edit':
		$result = $adb->pquery($sql,array($focus->id));
		$html_str = "<table id='pointstable'><tbody>";
		$totsumvalore = 0;
		$bFirst = true;
		while($row=$adb->fetchByAssoc($result))
		{
			if($bFirst) {
				$html_str .= "<tr class='pointRowSum'><td class='pointCatHead' colspan=3>{$row['account_no']}-{$row['accountname']}</td></tr>";
				$html_str .= "<tr class='pointRowSum'><td class='pointCatHead'>{$app_strings['Category']}</td><td class='pointGrpHead'>{$app_strings['LBL_ACTIVITY_TYPE']}</td><td class='pointValHead'>{$mod_strings['Points']}</td></tr>";
				$bFirst = false;
			}
			$totsumvalore += $row['sumvalore'];
			$html_str .= "<tr class='pointRow_".$row['categoria']."'><td class='pointCat'>".$mod_strings[$row['categoria']]."</td><td class='pointGrp'>".htmlspecialchars_decode($row['gruppo'])." </td><td class='pointVal'>".$row['sumvalore']."</td></tr>";
		}
		$html_str .= "<tr class='pointRowSum'><td class='pointCatSum'><button class='small show_points' type='button' onclick=\"return show_points_loaded(this, 'showpoints','{$totsumvalore}','{$focus->id}','showpoints_{$focus->id}')\">{$app_strings['LBL_CLOSE']}</button> </td><td class='pointGrpSum'>{$app_strings['LBL_TOTAL']}</td><td class='pointValSum'>".$totsumvalore."</td></tr>";
		$html_str .= "</tbody></table>";
		$smarty->assign("INNER_POINTS", $html_str);
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