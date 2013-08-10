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
		sum(temp_acc_ratings.valore) as sumvalore
		from 
		 temp_acc_ratings 
		 WHERE temp_acc_ratings.accountid = ?
		group by 
		temp_acc_ratings.categoria,
		temp_acc_ratings.gruppo
		ORDER BY 
		temp_acc_ratings.categoria, sumvalore";
$result = $adb->pquery($sql,array($recordid));
echo "<table id='pointstable'><tbody>";
echo "<tr class='pointRowSum'><td class='pointCatHead'>{$app_strings['Category']}</td><td class='pointGrpHead'>{$app_strings['LBL_ACTIVITY_TYPE']}</td><td class='pointValHead'>{$mod_strings['Rating']}</td></tr>";
$totsumvalore = 0;
while($row=$adb->fetchByAssoc($result))
{
	$totsumvalore += $row['sumvalore'];
	echo "<tr class='pointRow_".$row['categoria']."'><td class='pointCat'>".$mod_strings[$row['categoria']]."</td><td class='pointGrp'>".$row['gruppo']." </td><td class='pointVal'>".$row['sumvalore']."</td></tr>";
}
echo "<tr class='pointRowSum'><td class='pointCatSum'><button class='small' type='button' onclick=\"return show_points(this, 'showpoints','{$totsumvalore}','{$recordid}','showpoints_{$recordid}')\">{$app_strings['LBL_CLOSE']}</button> </td><td class='pointGrpSum'>{$app_strings['LBL_TOTAL']}</td><td class='pointValSum'>".$totsumvalore."</td></tr>";
echo "</tbody></table>";

?>