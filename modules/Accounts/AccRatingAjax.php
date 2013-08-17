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
		".$table_prefix."_account.accountname,
		".$table_prefix."_account.account_no
		ORDER BY 
		temp_acc_ratings.categoria, sumvalore";
$result = $adb->pquery($sql,array($recordid));
echo "<table id='pointstable'><tbody>";
$totsumvalore = 0;
$bFirst = true;
while($row=$adb->fetchByAssoc($result))
{
	if($bFirst) {
		echo "<tr class='pointRowSum'><td class='pointCatHead' colspan=3>{$row['account_no']} - {$row['accountname']}</td></tr>";
		echo "<tr class='pointRowSum'><td class='pointCatHead'>{$app_strings['Category']}</td><td class='pointGrpHead'>{$app_strings['LBL_ACTIVITY_TYPE']}</td><td class='pointValHead'>{$mod_strings['Points']}</td></tr>";
		$bFirst = false;
	}
	$totsumvalore += $row['sumvalore'];
	echo "<tr class='pointRow_".$row['categoria']."'><td class='pointCat'>".$mod_strings[$row['categoria']]."</td><td class='pointGrp'>".$row['gruppo']." </td><td class='pointVal'>".$row['sumvalore']."</td></tr>";
}
echo "<tr class='pointRowSum'><td class='pointCatSum'><button class='small show_points' type='button' onclick=\"return show_points(this, 'showpoints','{$totsumvalore}','{$recordid}','showpoints_{$recordid}')\">{$app_strings['LBL_CLOSE']}</button> </td><td class='pointGrpSum'>{$app_strings['LBL_TOTAL']}</td><td class='pointValSum'>".$totsumvalore."</td></tr>";
echo "</tbody></table>";

?>