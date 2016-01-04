<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

// danzi.tn@20151210 gestione mappingdate e mappingstatus su Map JS
// danzi.tn@20151214 set cf_871 = 1 in seguito ad aggiornamento indirizzo Map JS
// danzi.tn@20160104 passaggio in produzione albero utenti
global $adb;
$ajaxaction = $_REQUEST["ajxaction"];
if($ajaxaction == 'UPDATEADDRESS')
{
	$mapid = $_REQUEST['recordid'];
	$city = utf8RawUrlDecode($_REQUEST['city']);
	$postalCode = utf8RawUrlDecode($_REQUEST['code']);
	$state = utf8RawUrlDecode($_REQUEST['state']);
	$country = utf8RawUrlDecode($_REQUEST['country']);
	$street = utf8RawUrlDecode($_REQUEST['street']);
	$lat = utf8RawUrlDecode($_REQUEST['lat']);
	$lng = utf8RawUrlDecode($_REQUEST['lng']);
	$targetModule = $_REQUEST['module'];
	if($mapid != '')
	{
		$query = "SELECT lat, lng FROM  vtiger_map WHERE mapid = $mapid";
		$result = $adb->query($query);
		$row = $adb->fetchByAssoc($result);
		if( $row ) {
			$query = "UPDATE vtiger_map SET state = ? ,city = ?, postalCode = ?, country = ?, street = ?, lat = ?, lng = ?,  mappingdate = GETDATE(), mappingstatus =1 WHERE mapid = ?";
		} else {
			$query = "INSERT INTO vtiger_map (state,city,postalCode,country,street,lat,lng,mapid,mappingdate,mappingstatus) VALUES (?,?,?,?,?,?,?,?,GETDATE(),1)";
		}
		$result = $adb->pquery($query,array($state,$city,$postalCode,$country,$street,$lat,$lng,$mapid));
		if(!$result)
		{
			echo ':#:QUERY=' . $query;
			echo ':#:FAILURE';
		}else
		{
            $query = "UPDATE vtiger_accountscf SET cf_871 = '1' WHERE accountid = " . $mapid ;
			$adb->query($query);
			echo ':#:SUCCESS';
		}
	}else
	{
		echo ':#:FAILURE';
	}
}
?>
