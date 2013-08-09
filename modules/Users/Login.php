<!-- crmv@24269 -->
<html>
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
 * $Header: /advent/projects/wesat/vtiger_crm/sugarcrm/modules/Users/Login.php,v 1.6 2005/01/08 13:15:03 jack Exp $
 * Description: TODO:  To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
$theme_path="themes/".$theme."/";
$image_path="include/images/";

global $app_language;
//we don't want the parent module's string file, but rather the string file specifc to this subpanel
global $current_language;
$current_module_strings = return_module_language($current_language, 'Users');

 define("IN_LOGIN", true);

include_once('vtlib/Vtiger/Language.php');

//crmv@16312
// Retrieve username and password from the session if possible.
if(isset($_SESSION["login_user_name"]))
{
	if (isset($_REQUEST['default_user_name']))
		$login_user_name = trim(vtlib_purify($_REQUEST['default_user_name']), '"\'');
	else
		$login_user_name =  trim(vtlib_purify($_REQUEST['login_user_name']), '"\'');
		
}
else
{
	if (isset($_REQUEST['default_user_name']))
	{
		$login_user_name = trim(vtlib_purify($_REQUEST['default_user_name']), '"\'');
	}
	elseif (isset($_REQUEST['ck_login_id_vtiger'])) {
		$login_user_name = getUserName($_REQUEST['ck_login_id_vtiger']);
	}
	else
	{
		$login_user_name = $default_user_name;
	}
	$_session['login_user_name'] = $login_user_name;
}
$current_module_strings['VLD_ERROR'] = base64_decode('UGxlYXNlIHJlcGxhY2UgdGhlIFN1Z2FyQ1JNIGxvZ29zLg==');

// Retrieve username and password from the session if possible.
if(isset($_SESSION["login_password"]))
{
	$login_password = trim(vtlib_purify($_REQUEST['login_password']), '"\'');
}
else
{
	$login_password = $default_password;
	$_session['login_password'] = $login_password;
}
//crmv@16312 end
if(isset($_SESSION["login_error"]))
{
	$login_error = $_SESSION['login_error'];
}

?>
<!--Added to display the footer in the login page by Dina-->
<style type="text/css">@import url("themes/<?php echo $theme; ?>/style.css");</style>
<script language="JavaScript" type="text/javascript" src="include/js/vtlib.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/general.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/jquery.js"></script>
<script type="text/javascript" src="include/js/jquery_plugins/fancybox/jquery.mousewheel-3.0.4.pack.js"></script> 
<script type="text/javascript" src="include/js/jquery_plugins/fancybox/jquery.fancybox-1.3.4.js"></script>
<script src="include/scriptaculous/prototype.js" type="text/javascript" language="javascript"></script> <!-- crmv@27520 -->
<link rel="stylesheet" type="text/css" href="include/js/jquery_plugins/fancybox/jquery.fancybox-1.3.4.css" media="screen" />
<body>
<div id="popupContainer" style="display:none;"></div>
<!-- crmv@18742 -->
<div class="bodylogin">
<div align="center">
<div class="small" style="width:480px;height:500px;">
<div class="headerlogin"></div>
<?php
	if (isset($_REQUEST['ck_login_language_vtiger'])) {
		$display_language = vtlib_purify($_REQUEST['ck_login_language_vtiger']);
	}
	else {
		$display_language = $default_language;
	}

	if (isset($_REQUEST['ck_login_theme_vtiger'])) {
		$display_theme = vtlib_purify($_REQUEST['ck_login_theme_vtiger']);
	}
	else {
		$display_theme = $default_theme;
	}
?>
<!-- Sign in form -->
<br /><br />
	<form action="index.php" method="post" name="DetailView" id="form">
	<input type="hidden" name="module" value="Users">
	<input type="hidden" name="action" value="Authenticate">
	<input type="hidden" name="return_module" value="Users">
	<input type="hidden" name="return_action" value="Login">
	<table border="0" cellpadding="0" cellspacing="0" width="480" height="371" class="loginpage" style="padding-right: 20px;">
		<tr height="21"><td></td></tr>
		<tr height="40"><td></td></tr>
		<tr height="306">
			<td width="100%">
			<!-- form elements -->
				<table border="0" cellpadding="5" cellspacing="0" width="100%" align="center" class="small">
				<tr>
					<td class="small logintxt" align="right" width="30%"><?php echo $current_module_strings['LBL_USER_NAME'] ?></td>
					<td class="small" align="left" width="70%"><input class="small" type="text" size='37' name="user_name" value="<?php echo $login_user_name ?>" tabindex="1"></td>
				</tr>
				<tr>
					<td class="small logintxt"  align="right"><?php echo $current_module_strings['LBL_PASSWORD'] ?></td>
					<td class="small" align="left"><input class="small" type="password" size='37' name="user_password" value="<?php echo $login_password ?>" tabindex="2"></td>
				</tr>
				<tr>
					<td></td>
					<!-- crmv@27520 crmv@29377 -->
				     <td align="left" style="padding-top: 4px; vertical-align: baseline;">
				         <input type="checkbox" class="small" name="savelogin" id="savelogin" tabindex="3" <?php echo ($savelogin?'checked="checked"':''); ?> />
				         <label for="savelogin" class="small logintxt" style="font-weight: normal; text-decoration:none; cursor: pointer;"><?php echo $current_module_strings['LBL_KEEP_ME_LOGGED_IN']; ?></label>
				     </td>
				     <!-- crmv@27520e crmv@29377e -->
				</tr>
				<?php
				if (isset($_SESSION['validation'])) {
				?>
					<tr height="30px"><td colspan="2" align="center" class="small logintxt"><?php echo $current_module_strings['VLD_ERROR']; ?></td></tr>
				<?php
				} elseif (isset($login_error) && $login_error != "") {
				?>
					<tr height="30px"><td colspan="2" align="center" class="small logintxt"><?php echo $login_error; ?></td></tr>
				<?php
				} else {
				?>
					<tr height="30px"><td colspan="2">&nbsp;</td></tr>
				<?php
				}
				?>
				<tr height="10px"><td colspan="2"></td></tr>
				<tr>
					<td colspan="2" width="100%" align="center">
						<input title="<?php echo $current_module_strings['LBL_LOGIN_BUTTON_TITLE'] ?>" alt="<?php echo $current_module_strings['LBL_LOGIN_BUTTON_LABEL'] ?>" accesskey="<?php echo $current_module_strings['LBL_LOGIN_BUTTON_TITLE'] ?>" type="submit" style="background-image: url(themes/<?php echo $theme;?>/images/loginbutton.png); width: 170px; height: 30px;" name="Login" value="<?php echo $current_module_strings['LBL_LOGIN_BUTTON_LABEL'] ?>" class="buttonlogin" style="height:100%;" tabindex="4">
					</td>
				</tr>
				<tr>
					<td colspan="2" class="small logintxt" align="center" width="100%" style="font-weight: normal;">
						<a href="modules/Users/Recover.php" class="small logintxt" style="font-weight: normal;" tabindex="5"><?php echo $current_module_strings['LBL_FORGOT_YOUR_PASSWORD']; ?></a></td>	<!-- crmv@27589 -->
					</td>
				</tr>
				<tr height="60px"><td colspan="2"></td></tr>
				</table>
			</td>
			<td></td>
		</tr>
	</table>
	</form>
	<div class="crmvillagelogo"><img src="<?php echo get_logo('project'); ?>" border="0"></div>
</div>
</div>
</div>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#form input[name=user_name]').focus();
});
</script>
</body>
</html>
<!-- crmv@18742e -->