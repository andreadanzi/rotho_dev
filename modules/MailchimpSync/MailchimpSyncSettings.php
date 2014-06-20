<?php
require_once('include/utils/utils.php');
require_once('Smarty_setup.php');
require_once('modules/MailchimpSync/SyncWithMailChimpUtils.php');
global $app_strings;
global $mod_strings;
global $currentModule;
global $theme;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";
global $current_language;
global $adb, $table_prefix;

$smarty = new vtigerCRM_Smarty;

$smarty->assign("apikey", $MailChimpAPIKey);
$smarty->assign("listid", $MailChimpListId);
$smarty->assign("MOD", return_module_language($current_language,'Settings'));
$smarty->assign("CMOD", $mod_strings);
$smarty->assign("APP", $app_strings);
$smarty->assign("THEME", $theme);
$smarty->assign("IMAGE_PATH",$image_path);


$smarty->display(vtlib_getModuleTemplate('MailchimpSync','MailChimpSyncSettings.tpl'));
?>