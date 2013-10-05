<?php
/*+**********************************************************************************

 ************************************************************************************/
//danzi.tn@201308091803

global $mod_strings,$app_strings,$theme,$currentModule,$current_user,$adb, $table_prefix;

require_once('Smarty_setup.php');
require_once('include/utils/utils.php');

$focus = CRMEntity::getInstance($currentModule);
$focus->mode = '';
$mode = $_REQUEST['mode'];
$points = $_REQUEST['points'];
$recordid = $_REQUEST['recordid'];
$ajxaction = $_REQUEST['ajxaction'];
$sql = "SELECT
		temp_acc_ratings.categoria,
		temp_acc_ratings.gruppo,
		temp_acc_ratings.eventdatetime,
		sum(temp_acc_ratings.valore) as sumvalore,
		".$table_prefix."_account.accountname,
		".$table_prefix."_account.account_no
		from 
		 temp_acc_ratings 
		join ".$table_prefix."_account ON ".$table_prefix."_account.accountid = temp_acc_ratings.accountid 
		 WHERE temp_acc_ratings.accountid = ?
		group by 
		temp_acc_ratings.categoria,
		temp_acc_ratings.gruppo,
		temp_acc_ratings.eventdatetime,
		".$table_prefix."_account.accountname,
		".$table_prefix."_account.account_no
		ORDER BY 
		temp_acc_ratings.categoria, temp_acc_ratings.eventdatetime, sumvalore";
// danzi.tn@20130909		
$sql_visit = "SELECT DISTINCT 'Visitreport' as categoria, 
      ".$table_prefix."_visitreport.visitreportid,
      ".$table_prefix."_visitreport.visitreport_no,
      ".$table_prefix."_visitreport.visitdate
		from 
		 temp_acc_ratings 
		Join ".$table_prefix."_account ON ".$table_prefix."_account.accountid = temp_acc_ratings.accountid
		JOIN ".$table_prefix."_visitreport ON ".$table_prefix."_visitreport.accountid = temp_acc_ratings.accountid
		JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_visitreport.visitreportid and ".$table_prefix."_crmentity.deleted = 0
		WHERE temp_acc_ratings.accountid = ? 
		AND ( ".$table_prefix."_visitreport.visitdate BETWEEN DATEADD( month, - ".$table_prefix."_account.return_time ,GETDATE())  AND  GETDATE() )
		ORDER BY 
		".$table_prefix."_visitreport.visitdate DESC";
// danzi.tn@20130909 e
$result = $adb->pquery($sql,array($recordid));
echo "<table id='pointstable'><tbody>";
$totsumvalore = 0;
$bFirst = true;
while($row=$adb->fetchByAssoc($result))
{
	if($bFirst) {
		echo "<tr class='pointRowSum'><td class='pointCatHead' colspan=4>{$row['account_no']} - {$row['accountname']}</td></tr>";
		echo "<tr class='pointRowSum'><td class='pointCatHead'>{$app_strings['Category']}</td><td class='pointGrpHead'>{$app_strings['LBL_ACTIVITY_TYPE']}</td><td class='pointGrpHead'>{$app_strings['date']}</td><td class='pointValHead'>{$mod_strings['Points']}</td></tr>";
		$bFirst = false;
	}
	$totsumvalore += $row['sumvalore'];
	echo "<tr class='pointRow_".$row['categoria']."'><td class='pointCat'>".$mod_strings[$row['categoria']]."</td><td class='pointGrp'>".$row['gruppo']." </td><td class='pointGrp'>".$row['eventdatetime']." </td><td class='pointVal'>".$row['sumvalore']."</td></tr>";
}
// danzi.tn@20130909
$novisite=true;
$html_visite = "<tr class='pointRow_NoVisitreport'><td class='pointCat'>".$app_strings['Visitreport']."</td><td class='pointGrp'></td><td class='pointGrp'>ND</td><td class='pointVal'>0</td></tr>";
$result_visit = $adb->pquery($sql_visit,array($recordid));
while($row_visit=$adb->fetchByAssoc($result_visit))
{
	$html_visite = "<tr class='pointRow_".$row_visit['categoria']."'><td class='pointCat'>".$app_strings[$row_visit['categoria']]."</td><td class='pointGrp'></td><td class='pointGrp'>".$row_visit['visitdate']." </td><td class='pointVal'>0</td></tr>";
	$novisite=false;
	break;
}
echo $html_visite;
// danzi.tn@20130909e
echo "<tr class='pointRowSum'><td class='pointCatSum'><button class='small show_points' type='button' onclick=\"return show_points(this, 'showpoints','{$totsumvalore}','{$recordid}','showpoints_{$recordid}')\">{$app_strings['LBL_CLOSE']}</button> </td><td class='pointGrpSum'></td><td class='pointGrpSum'>{$app_strings['LBL_TOTAL']}</td><td class='pointValSum'>".$totsumvalore."</td></tr>";
echo "</tbody></table>";

?>