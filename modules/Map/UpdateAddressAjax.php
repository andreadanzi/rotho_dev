<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

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
			$query = "UPDATE vtiger_map SET state = '$state' ,city = '$city', postalCode = '$postalCode', country = '$country', street = '$street', lat = '$lat', lng = '$lng' WHERE mapid = $mapid";
		} else {
			$query = "INSERT INTO vtiger_map (mapid,state,city,postalCode,country,street,lat,lng) VALUES ($mapid,'$state','$city','$postalCode','$country','$street','$lat','$lng')";
		}
		$result = $adb->query($query);
		if(!$result)
		{
			echo ':#:QUERY=' . $query;
			echo ':#:FAILURE';
		}else
		{
			echo ':#:SUCCESS';
		}   
	}else
	{
		echo ':#:FAILURE';
	}
}
?>