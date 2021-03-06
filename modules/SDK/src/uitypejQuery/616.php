<?php
/*
 * $sdk_value = $value � il valore del dato
 */
global $sdk_mode, $focus, $adb,$app_strings,$mod_strings,$smarty;
$imgdir = 'modules/SDK/src/uitypejQuery/img/';
$iconfile = 'star_16.png';
$sql = "SELECT
		temp_acc_ratings.categoria,
		temp_acc_ratings.gruppo,
		temp_acc_ratings.eventdatetime,
		CASE WHEN temp_acc_ratings.account_category = 'RP / PROG' THEN 'star_16.png' ELSE 'carp_16.png' END AS iconfile,
		sum(temp_acc_ratings.valore) as sumvalore,
		vtiger_account.accountname,
		vtiger_account.account_no
		--, max(vtiger_visitreport.visitreportid) as visit_id 
		from 
		 temp_acc_ratings 
		JOIN vtiger_account ON vtiger_account.accountid = temp_acc_ratings.accountid
		-- LEFT JOIN vtiger_visitreport ON vtiger_visitreport.accountid = temp_acc_ratings.accountid 
		 WHERE temp_acc_ratings.accountid = ?
		group by 
		temp_acc_ratings.categoria,
		temp_acc_ratings.gruppo,
		temp_acc_ratings.eventdatetime,
		CASE WHEN temp_acc_ratings.account_category = 'RP / PROG' THEN 'star_16.png' ELSE 'carp_16.png' END,
		vtiger_account.accountname,
		vtiger_account.account_no
		ORDER BY 
		temp_acc_ratings.categoria,temp_acc_ratings.eventdatetime, sumvalore";
// danzi.tn@20130909	
// danzi.tn@20140423 CASE -9 per i RP / PROG ELSE  -6
$sql_visit = "SELECT DISTINCT 
      CASE WHEN temp_acc_ratings.account_category = 'RP / PROG' THEN 'star_16.png' ELSE 'carp_16.png' END AS iconfile,
	  'Visitreport' as categoria, 
      vtiger_visitreport.visitreportid,
      vtiger_visitreport.visitreport_no,
      vtiger_visitreport.visitdate
		from 
		 temp_acc_ratings 
		Join vtiger_account ON vtiger_account.accountid = temp_acc_ratings.accountid
		LEFT JOIN vtiger_visitreport ON vtiger_visitreport.accountid = temp_acc_ratings.accountid AND ( vtiger_visitreport.visitdate BETWEEN DATEADD( month, CASE WHEN temp_acc_ratings.account_category = 'RP / PROG' THEN -9 ELSE -6 END ,GETDATE())  AND  GETDATE() )
		LEFT JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_visitreport.visitreportid and vtiger_crmentity.deleted = 0
		WHERE temp_acc_ratings.accountid = ?
		ORDER BY 
		vtiger_visitreport.visitdate DESC";
// danzi.tn@20130909e
switch($sdk_mode) {
	case 'insert':
		$fldvalue = $this->column_fields[$fieldname];	//non � indispensabile questo, serve solo da esempio
		break;
	case 'detail':
		$result = $adb->pquery($sql,array($focus->id));
		$html_str = "<table id='pointstable'><tbody>";
		$html_str .= "<!-- danzi.tn@20140630 modifica per downloads -->";
		$totsumvalore = 0;
		$bFirst = true;
		while($row=$adb->fetchByAssoc($result))
		{
			$iconfile = $row['iconfile'];
			if($bFirst) {
				$html_str .= "<tr class='pointRowSum'><td class='pointCatHead' colspan=4>{$row['account_no']}-{$row['accountname']}</td></tr>";
				$html_str .= "<tr class='pointRowSum'><td class='pointCatHead'>{$app_strings['Category']}</td><td class='pointGrpHead'>{$app_strings['LBL_ACTIVITY_TYPE']}</td><td class='pointGrpHead'>{$app_strings['date']}</td><td class='pointValHead'>{$mod_strings['Points']}</td></tr>";
				$bFirst = false;
			}
			$totsumvalore += $row['sumvalore'];
			$html_str .= "<tr class='pointRow_".$row['categoria']."'><td class='pointCat'>".$mod_strings[$row['categoria']]."</td><td class='pointGrp'>".htmlspecialchars_decode($row['gruppo'])." </td><td class='pointGrp'>".htmlspecialchars_decode($row['eventdatetime'])." </td><td class='pointVal'>".$row['sumvalore']."</td></tr>";
		}
		// danzi.tn@20130909
		$novisite=true;
		$html_visite = "<tr class='pointRow_NoVisitreport'><td class='pointCat'>".$app_strings['Visitreport']."</td><td class='pointGrp'></td><td class='pointGrp'>ND</td><td class='pointVal'>0</td></tr>";
		$result_visit = $adb->pquery($sql_visit,array($focus->id));
		while($row_visit=$adb->fetchByAssoc($result_visit))
		{
			$iconfile = $row_visit['iconfile'];
			if(!empty($row_visit['visitreportid'])  )
			{
				$html_visite = "<tr class='pointRow_".$row_visit['categoria']."'><td class='pointCat'>".$app_strings[$row_visit['categoria']]."</td><td class='pointGrp'></td><td class='pointGrp'>".$row_visit['visitdate']." </td><td class='pointVal'>0</td></tr>";
				$novisite=false;
				break;
			}
		}
		$html_str .= $html_visite;
		$smarty->assign("ICONFILE", $iconfile);
		$smarty->assign("NO_VISITE", $novisite);
		// danzi.tn@20130909e
		$html_str .= "<tr class='pointRowSum'><td class='pointCatSum'><button class='small show_points' type='button' onclick=\"return show_points_loaded(this, 'showpoints','{$totsumvalore}','{$focus->id}','showpoints_{$focus->id}')\">{$app_strings['LBL_CLOSE']}</button> </td><td class='pointGrpSum'></td><td class='pointGrpSum'>{$app_strings['LBL_TOTAL']}</td><td class='pointValSum'>".$totsumvalore."</td></tr>";
		$html_str .= "</tbody></table>";
		$smarty->assign("INNER_POINTS", $html_str);
		$label_fld[] = getTranslatedString($fieldlabel,$module);
		$label_fld[] = $col_fields[$fieldname];
		break;
	case 'edit':
		$result = $adb->pquery($sql,array($focus->id));
		$html_str = "<table id='pointstable'><tbody>";
		$html_str .= "<!-- danzi.tn@20140630 modifica per downloads -->";
		$totsumvalore = 0;
		$bFirst = true;
		while($row=$adb->fetchByAssoc($result))
		{
			$iconfile = $row['iconfile'];
			if($bFirst) {
				$html_str .= "<tr class='pointRowSum'><td class='pointCatHead' colspan=4>{$row['account_no']}-{$row['accountname']}</td></tr>";
				$html_str .= "<tr class='pointRowSum'><td class='pointCatHead'>{$app_strings['Category']}</td><td class='pointGrpHead'>{$app_strings['LBL_ACTIVITY_TYPE']}</td><td class='pointGrpHead'>{$app_strings['date']}</td><td class='pointValHead'>{$mod_strings['Points']}</td></tr>";
				$bFirst = false;
			}
			$totsumvalore += $row['sumvalore'];
			$html_str .= "<tr class='pointRow_".$row['categoria']."'><td class='pointCat'>".$mod_strings[$row['categoria']]."</td><td class='pointGrp'>".htmlspecialchars_decode($row['gruppo'])." </td><td class='pointGrp'>".htmlspecialchars_decode($row['eventdatetime'])." </td><td class='pointVal'>".$row['sumvalore']."</td></tr>";
		}
		// danzi.tn@20130909
		$novisite=true;
		$html_visite = "<tr class='pointRow_NoVisitreport'><td class='pointCat'>".$app_strings['Visitreport']."</td><td class='pointGrp'></td><td class='pointGrp'>ND</td><td class='pointVal'>0</td></tr>";
		$result_visit = $adb->pquery($sql_visit,array($focus->id));
		while($row_visit=$adb->fetchByAssoc($result_visit))
		{
			$iconfile = $row_visit['iconfile'];
			if(!empty($row_visit['visitreportid'])  )
			{
				$html_visite = "<tr class='pointRow_".$row_visit['categoria']."'><td class='pointCat'>".$app_strings[$row_visit['categoria']]."</td><td class='pointGrp'> </td><td class='pointGrp'>".$row_visit['visitdate']." </td><td class='pointVal'>0</td></tr>";
				$novisite=false;
				break;
			}
		}
		$html_str .= $html_visite;
		$smarty->assign("ICONFILE", $iconfile);
		$smarty->assign("NO_VISITE", $novisite);
		// danzi.tn@20130909e
		$html_str .= "<tr class='pointRowSum'><td class='pointCatSum'><button class='small show_points' type='button' onclick=\"return show_points_loaded(this, 'showpoints','{$totsumvalore}','{$focus->id}','showpoints_{$focus->id}')\">{$app_strings['LBL_CLOSE']}</button> </td><td class='pointGrpSum'></td><td class='pointGrpSum'>{$app_strings['LBL_TOTAL']}</td><td class='pointValSum'>".$totsumvalore."</td></tr>";
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
			// danzi.tn@20130909
			$novisite=true;		
			$result_visit = $adb->pquery($sql_visit,array($recordId));
			while($row_visit=$adb->fetchByAssoc($result_visit))
			{
				$iconfile = $row_visit['iconfile'];
				if(!empty($row_visit['visitreportid'])  )
				{
					$novisite=false;
					break;
				}
			}
			// danzi.tn@20130909e
			$value = '<div class="show_points" onclick="show_points(this, \'showpoints\','.$sdk_value.','.$recordId.',\'showpoints_'.$recordId.'\');"><span align="left" >'.$sdk_value.'</span>&nbsp;&times;&nbsp;<span align="left" ><img border="0" src="'.$imgdir.($novisite?'r_'.$iconfile:$iconfile).'" alt="RP Prog - Rating" title="RP Prog - Rating" /></span></div>';
			$value .= '<div id="showpoints_'.$recordId.'" class="showpoints_hidden">'.$sdk_value.'</div>';
		} else {
			$value = '';
		}
		break;
}
?>