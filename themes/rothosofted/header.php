<?php

/*********************************************************************************

 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2

 * ("License"); You may not use this file except in compliance with the

 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL

 * Software distributed under the License is distributed on an  "AS IS"  basis,

 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for

 * the specific language governing rights and limitations under the License.

 * The Original Code is:  SugarCRM Open Source

 * The Initial Developer of the Original Code is SugarCRM, Inc.

 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;

 * All Rights Reserved.

 * Contributor(s): ______________________________________.

 ********************************************************************************/

/*********************************************************************************

 * $Header$

 * Description:  Contains a variety of utility functions used to display UI

 * components such as form headers and footers.  Intended to be modified on a per

 * theme basis.

 ********************************************************************************/

require_once('Smarty_setup.php');

require_once("data/Tracker.php");

require_once("include/utils/utils.php");

require_once("include/calculator/Calc.php");

require_once("config.inc.php");

global $currentModule;
global $app_strings;
global $app_list_strings;
global $moduleList;
global $theme;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$smarty = new vtigerCRM_Smarty;
//crmv@18592
$menuLayout = getMenuLayout();
if ($menuLayout['type'] != 'modules') {
	$header_array = getHeaderArray();
	$smarty->assign("HEADERS",$header_array);
}
//crmv@18592e
$smarty->assign("THEME",$theme);
$smarty->assign("IMAGEPATH",$image_path);

$qc_modules = getQuickCreateModules();
$smarty->assign("QCMODULE", $qc_modules);
$smarty->assign("APP", $app_strings);

$cnt = count($qc_modules);
$smarty->assign("CNT", $cnt);

$smarty->assign("PRINT_URL", "phprint.php?jt=".session_id().$GLOBALS['request_string']);

$smarty->assign("MODULE_NAME", $currentModule);

$smarty->assign("DATE", getDisplayDate(date("Y-m-d H:i")));

$smarty->assign("CURRENT_USER", $current_user->user_name);

$smarty->assign("CURRENT_USER_ID", $current_user->id);
$smarty->assign("MODULELISTS",$app_list_strings['moduleList']);
$smarty->assign("CATEGORY",getParentTab());
$smarty->assign("CALC",get_calc($image_path));
$smarty->assign("QUICKACCESS",getAllParenttabmoduleslist());
$smarty->assign("ANNOUNCEMENT",get_announcements());
//crmv@7220+18038
$smarty->assign("USE_ASTERISK", get_use_asterisk($current_user->id,'incoming'));
//crmv@7220+18038 end

if (is_admin($current_user)) $smarty->assign("ADMIN_LINK", "<a href='index.php?module=Settings&action=index'>".$app_strings['LBL_SETTINGS']."</a>");



$module_path="modules/".$currentModule."/";

require_once('include/Menu.php');

//Assign the entered global search string to a variable and display it again
if($_REQUEST['query_string'] != '')
	$smarty->assign("QUERY_STRING",htmlspecialchars($_REQUEST['query_string'],ENT_QUOTES));//ds@16s Bugfix "Cross-Site-Scripting"
else
	$smarty->assign("QUERY_STRING","$app_strings[LBL_SEARCH_STRING]");

global $module_menu;


require_once('data/Tracker.php');
$tracFocus=new Tracker();
$list = $tracFocus->get_recently_viewed($current_user->id);
$smarty->assign("TRACINFO",$list);
// Gather the custom link information to display
include_once('vtlib/Vtiger/Link.php');
$hdrcustomlink_params = Array('MODULE'=>$currentModule);
$COMMONHDRLINKS = Vtiger_Link::getAllByType(Vtiger_Link::IGNORE_MODULE, Array('HEADERLINK','HEADERSCRIPT', 'HEADERCSS'), $hdrcustomlink_params);
$smarty->assign('HEADERLINKS', $COMMONHDRLINKS['HEADERLINK']);
$smarty->assign('HEADERSCRIPTS', $COMMONHDRLINKS['HEADERSCRIPT']);
$smarty->assign('HEADERCSS', $COMMONHDRLINKS['HEADERCSS']);
// END

//crmv@18592
if ($menuLayout['type'] == 'modules') {
	$menu_module_list = getMenuModuleList();
	$smarty->assign('VisibleModuleList', $menu_module_list[0]);
	$smarty->assign('OtherModuleList', $menu_module_list[1]);
	
	if (!in_array(getTabId($currentModule),array_keys($menu_module_list[0])) && !in_array($currentModule,array('Settings','Users','Administration')) && getParentTab() != 'Settings')
		$_SESSION['last_module_visited'] = $currentModule;
	$smarty->assign("LAST_MODULE_VISITED", $_SESSION['last_module_visited']);
}
//crmv@18592e

$smarty->display("Header.tpl");
?>
