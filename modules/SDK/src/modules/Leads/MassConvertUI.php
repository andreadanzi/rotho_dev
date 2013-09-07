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

require_once 'modules/Leads/ConvertLeadUI.php';
class MassConvertUI extends ConvertLeadUI {
	var $userselected = 'checked';
	var $userdisplay = 'block';
	var $groupselected = '';
	var $groupdisplay = 'none';
	function __construct($current_user) {
		$this->current_user = $current_user;
	}
}
?>