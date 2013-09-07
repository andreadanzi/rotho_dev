<?php
/***************************************************************************************
 * The contents of this file are subject to the CRMVILLAGE.BIZ VTECRM License Agreement
 * ("licenza.txt"); You may not use this file except in compliance with the License
 * The Original Code is:  CRMVILLAGE.BIZ VTECRM
 * The Initial Developer of the Original Code is CRMVILLAGE.BIZ.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
 ***************************************************************************************/

/* mycrmv@2707m */

require_once('Smarty_setup.php');
require_once('include/utils/utils.php');

global $currentModule, $current_user;
$currentModule = 'Leads';

$smarty = new vtigerCRM_Smarty;

$smarty->assign('MODULE',$currentModule);

$selected_leads = getListViewCheck($currentModule);
$smarty->assign('SELECTED_LEADS_COUNT',count($selected_leads));

require_once('modules/SDK/src/modules/Leads/MassConvertUI.php');
$uiinfo = new MassConvertUI($current_user);
$smarty->assign('UIINFO', $uiinfo);

$smarty->display('modules/SDK/src/modules/Leads/MassConvertForm.tpl');
?>