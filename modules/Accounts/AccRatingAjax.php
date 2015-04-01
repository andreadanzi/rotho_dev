<?php
/*+**********************************************************************************

 ************************************************************************************/
//danzi.tn@201308091803
//danzi.tn@20140423 CASE -9 per i RP / PROG ELSE  -6
// danzi.tn@20141212 nuova classificazione cf_762 sostituito con vtiger_account.account_client_type = PROGETTISTA
// danzi.tn@20150401 aggiunti gli eventi di calendario di tipo Call/Chiamata per la generazione del punteggio
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
// danzi.tn@20140423 CASE -9 per i RP / PROG ELSE  -6
$sql_visit = "SELECT DISTINCT 'Visitreport' as categoria, 
              vtiger_visitreport.visitreportid,
              vtiger_visitreport.visitreport_no,
              vtiger_visitreport.visitdate
                from 
                 temp_acc_ratings 
                Join vtiger_account ON vtiger_account.accountid = temp_acc_ratings.accountid
                JOIN vtiger_visitreport ON vtiger_visitreport.accountid = temp_acc_ratings.accountid
                JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_visitreport.visitreportid and vtiger_crmentity.deleted = 0
                WHERE temp_acc_ratings.accountid = ".$recordid." 
                AND ( vtiger_visitreport.visitdate BETWEEN DATEADD( month, CASE WHEN temp_acc_ratings.account_client_type = 'PROGETTISTA' THEN -9 ELSE -6 END ,GETDATE())  AND  GETDATE() )
        UNION
        SELECT DISTINCT 'Call' as categoria, 
              vtiger_activity.activityid as visitreportid,
              vtiger_activity.subject as visitreport_no,
              vtiger_activity.date_start as visitdate
                from 
                 temp_acc_ratings 
                Join vtiger_account ON vtiger_account.accountid = temp_acc_ratings.accountid
                JOIN vtiger_seactivityrel ON vtiger_seactivityrel.crmid = temp_acc_ratings.accountid
                JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_seactivityrel.activityid and vtiger_crmentity.deleted = 0
                JOIN vtiger_activity ON vtiger_activity.activityid = vtiger_crmentity.crmid  AND vtiger_activity.activitytype = 'Call'
                WHERE temp_acc_ratings.accountid = ".$recordid."   
                AND ( vtiger_activity.date_start BETWEEN DATEADD( month, CASE WHEN temp_acc_ratings.account_client_type = 'PROGETTISTA' THEN -9 ELSE -6 END ,GETDATE())  AND  GETDATE() )
        ORDER BY visitdate";
// danzi.tn@20130909 e
$result = $adb->pquery($sql,array($recordid));
echo "<table id='pointstable'><tbody>";
echo "<!-- danzi.tn@20140630 modifica per downloads -->";
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
$result_visit = $adb->query($sql_visit);
while($row_visit=$adb->fetchByAssoc($result_visit))
{
	$html_visite = "<tr class='pointRow_Visitreport'><td class='pointCat'>".$app_strings[$row_visit['categoria']]."</td><td class='pointGrp'>".$row_visit['visitreport_no']."</td><td class='pointGrp'>".$row_visit['visitdate']." </td><td class='pointVal'>0</td></tr>";
	$novisite=false;
	break;
}
echo $html_visite;
// danzi.tn@20130909e
echo "<tr class='pointRowSum'><td class='pointCatSum'><button class='small show_points' type='button' onclick=\"return show_points(this, 'showpoints','{$totsumvalore}','{$recordid}','showpoints_{$recordid}')\">{$app_strings['LBL_CLOSE']}</button> </td><td class='pointGrpSum'></td><td class='pointGrpSum'>{$app_strings['LBL_TOTAL']}</td><td class='pointValSum'>".$totsumvalore."</td></tr>";
echo "</tbody></table>";

?>
