<?php
/***************************************************************************************
 * The contents of this file are subject to the CRMVILLAGE.BIZ VTECRM License Agreement
 * ("licenza.txt"); You may not use this file except in compliance with the License
 * The Original Code is:  CRMVILLAGE.BIZ VTECRM
 * The Initial Developer of the Original Code is CRMVILLAGE.BIZ.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
 ***************************************************************************************/
/* crmv@2328m */

class ConsulenzaEventHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		global $adb, $current_user, $table_prefix;
		
		if (!($entityData->focus instanceof Consulenza)) {
			return;
		}
		
		if($eventName == 'vtiger.entity.beforesave') {
			/*
			$data = $entityData->getData();
			if ($data['flg_agente'] == 1 || $data['flg_agente'] == 'on') {
				$entityData->set('parent','');
				$entityData->set('contact','');
			}
			*/
		}
	}
}
?>