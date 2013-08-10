<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
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
$totsumvalore = 0;
while($row=$adb->fetchByAssoc($result))
{
	$totsumvalore += $row['sumvalore'];
	echo "<tr class='pointRow'><td class='pointCat'>".$row['categoria']."</td><td class='pointGrp'>".$row['gruppo']." </td><td class='pointVal'>".$row['sumvalore']."</td></tr>";
}
echo "<tr class='pointRowSum'><td class='pointCatSum'></td><td class='pointGrpSum'>Punteggio Totale=</td><td class='pointValSum'>".$totsumvalore."</td></tr>";
echo "</tbody></table>";

?>