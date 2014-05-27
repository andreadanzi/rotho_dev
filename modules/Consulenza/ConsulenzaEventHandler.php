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
/* danzi.tn@20140505 */

class ConsulenzaEventHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		global $adb, $current_user, $table_prefix;
		
		if (!($entityData->focus instanceof Consulenza)) {
			return;
		}
		
		if($eventName == 'vtiger.entity.beforesave') {
			$module = $entityData->getModuleName();
			$focus = $entityData->focus;
			$id = $focus->id;
			// danzi.tn@20140513 Modificato la regola di assegnazione: bisogna considerare anche update
			// danzi.tn@20140505 Numero Consulenza Garanzia Zenit
			$consulenzaname = $focus->column_fields['consulenzaname']; // 30 == Garanzia Zenit
			$warranty_serial_no = 'ND';
			if( $entityData->isNew() ) {
				$focus->column_fields['warranty_serial_no'] = $warranty_serial_no;
			}
			if( $consulenzaname == "30" &&	( $focus->column_fields['warranty_serial_no'] == $warranty_serial_no || 
											  $focus->column_fields['warranty_serial_no'] == '' )) {
				$formatstr = "GZ-%d-%04d";	
				$yearn = date("Y");
				$serialno = $this->_get_next_serial_no($yearn);
				$warranty_serial_no = sprintf( $formatstr,$yearn,$serialno );
				$focus->column_fields['warranty_serial_no'] = $warranty_serial_no;
			}
			// danzi.tn@20140505e
			// danzi.tn@20140513e
		}
	}
	
	// danzi.tn@20140505 Numero Consulenza Garanzia Zenit
	function _get_next_serial_no($yearn) {
		global $adb;
		$cur_serial_no = 0;
		$sql = "select year_no, serial_no from temp_zenit_warranty_serials where year_no = ? ORDER BY year_no, serial_no";
		$result = $adb->pquery($sql,array($yearn));
		if ($result && $adb->num_rows($result) > 0){
			while($row = $adb->fetchByAssoc($result)) {
				$cur_serial_no = $row['serial_no'];
			}
			$serial_no = $cur_serial_no + 1;
			$sql = "UPDATE temp_zenit_warranty_serials SET serial_no = ? WHERE year_no = ?";
			$adb->pquery($sql,array($serial_no,$yearn));
		} else {
			$sql = "INSERT INTO temp_zenit_warranty_serials (year_no, serial_no) VALUES (?,?)";
			$serial_no = $cur_serial_no + 1;
			$adb->pquery($sql,array($yearn,$serial_no));
		}
		return $serial_no;
	}
	// danzi.tn@20140505e
}
?>