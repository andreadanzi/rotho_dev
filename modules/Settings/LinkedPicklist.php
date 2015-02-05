<?php
/* editor di picklist collegate - crmv@30528 */
require_once('Smarty_setup.php');
require_once('include/database/PearDatabase.php');
require_once('database/DatabaseConnection.php');
require_once('modules/SDK/examples/uitypePicklist/300Utils.php');

global $mod_strings, $app_strings, $app_list_strings;
global $current_language, $currentModule, $theme;
global $adb, $table_prefix;

$smarty = new vtigerCRM_Smarty;

$smarty->assign("MODULE",$fld_module);
$smarty->assign("MOD", return_module_language($current_language,'Settings'));
$smarty->assign("THEME", $theme);
$smarty->assign("IMAGE_PATH",$image_path);
$smarty->assign("APP", $app_strings);
$smarty->assign("CMOD", $mod_strings);

linkedListInitTables();

if ($_REQUEST['subaction'] == 'getlinktable') {
	$id1 = vtlib_purify($_REQUEST['picklist1']);
	$id2 = vtlib_purify($_REQUEST['picklist2']);

	$modname = vtlib_purify($_REQUEST['modname']);

	// get fields name
	$res = $adb->pquery("select name,fieldname,fieldlabel from {$table_prefix}_field inner join {$table_prefix}_tab on {$table_prefix}_tab.tabid = {$table_prefix}_field.tabid where {$table_prefix}_tab.name = ? and fieldname = ?", array($modname, $id1));
	if ($res) {
		$mod = $adb->query_result($res, 0, 'name');
		$picklist1 = $adb->query_result($res, 0, 'fieldname');
		$picklist1_label = getTranslatedString($adb->query_result($res, 0, 'fieldlabel'), $mod);
	}
	$res = $adb->pquery("select name,fieldname,fieldlabel from {$table_prefix}_field inner join {$table_prefix}_tab on {$table_prefix}_tab.tabid = {$table_prefix}_field.tabid where {$table_prefix}_tab.name = ? and fieldname = ?", array($modname, $id2));
	if ($res) {
		$mod = $adb->query_result($res, 0, 'name');
		$picklist2 = $adb->query_result($res, 0, 'fieldname');
		$picklist2_label = getTranslatedString($adb->query_result($res, 0, 'fieldlabel'), $mod);
	}

	$rr = linkedListGetAllOptions($picklist1, $picklist2, $mod);

	$smarty->assign("PICKMATRIX", $rr);
	$smarty->assign("PICKLIST1", array('name'=>$picklist1, 'label'=>$picklist1_label));
	$smarty->assign("PICKLIST2", array('name'=>$picklist2, 'label'=>$picklist2_label));

	$smarty->display("Settings/LinkedPicklistTable.tpl");

} elseif ($_REQUEST['subaction'] == 'savepicklist') {

	$picklist1 = vtlib_purify($_REQUEST['picklist1']);
	$picklist2 = vtlib_purify($_REQUEST['picklist2']);
	$mod = vtlib_purify($_REQUEST['modname']);

	$flatmatrix = array_map(intval, explode(',',$_REQUEST['matrix']));

	if (count($flatmatrix) == 0) die('ERROR::NOMATRIX');

	// assicuro che l'uitype esista
	$pldir = 'modules/SDK/examples/uitypePicklist/';
	SDK::setUitype(300,$pldir.strval(300).'.php',$pldir.strval(300).'.tpl', $pldir.strval(300).'.js');
	Vtiger_Link::addLink(getTabid('SDK'),'HEADERSCRIPT','SDKUitype', $pldir.'300Utils.js');


	// inizializzo e controllo cicli
	$cycles = checkCyclicPaths($picklist1, $picklist2, $mod);

	if ($cycles) die('ERROR::'.getTranslatedString('LBL_ERROR_CYCLE', 'Settings'));

	// converto l'uitype a 300
	$adb->pquery("update {$table_prefix}_field set uitype = 300 where fieldname in (?,?)", array($picklist1, $picklist2));

	// costruisco array per l'inserimento
	$allopt = linkedListGetAllOptions($picklist1, $picklist2, $mod);

	// transpose matrix
	$srclen = count($allopt['matrix']);
	foreach ($allopt['matrix'] as $src=>$destarray) {
		$destlen = count($destarray);
		break;
	}
	$newarr = array();
	for ($j=0; $j<$srclen; ++$j) {
		for ($i=0; $i<$destlen; ++$i) {
			$newarr[] = $flatmatrix[$i*$srclen + $j];
		}
	}
	$flatmatrix = $newarr;

	$i=0;
	linkedListDeleteLink($picklist1, $mod, $picklist2); // reset connections
	foreach ($allopt['matrix'] as $src=>$destarray) {
		$destlinks = array();
		foreach ($destarray as $dest=>$destval) {
			if ($flatmatrix[$i++] == 1) $destlinks[] = $dest;
		}
		if (count($destlinks) > 0) {
			linkedListAddLink($picklist1, $picklist2, $mod, $src, $destlinks);
		}
	}

} elseif ($_REQUEST['subaction'] == 'unlinkpicklist') {

	$picklist1 = vtlib_purify($_REQUEST['picklist1']);
	$picklist2 = vtlib_purify($_REQUEST['picklist2']);
	$mod = vtlib_purify($_REQUEST['modname']);
	$tabid = getTabid($mod);

	linkedListDeleteLink($picklist1, $mod, $picklist2); // reset connections

	// cambia il tipo se la picklist non ha altre subordinate
	// TODO: come faccio a sapere se rimetterla a 15 o 16 ?
	$res = $adb->pquery("select picksrc from {$table_prefix}_linkedlist where module = ? and picksrc = ?", array($mod, $picklist1));
	if ($res && $adb->num_rows($res) == 0) {
		$adb->pquery("update {$table_prefix}_field set uitype = 15 where tabid = ? and fieldname = ?", array($tabid, $picklist1));
	}
	$res = $adb->pquery("select picksrc from {$table_prefix}_linkedlist where module = ? and picksrc = ?", array($mod, $picklist2));
	if ($res && $adb->num_rows($res) == 0) {
		$adb->pquery("update {$table_prefix}_field set uitype = 15 where tabid = ? and fieldname = ?", array($tabid, $picklist2));
	}

} else {
	$plist = getAllPicklists();
	$conn = getConnections('All');
	$smarty->assign("PLISTS", $plist);
	$smarty->assign("PLIST_CONNECTIONS", $conn);
	$smarty->display("Settings/LinkedPicklist.tpl");
}


// -- functions
// restituisce un array con le picklist disponibili per modulo
// TODO: rimuovi le picklist che provocan cicli?
// TODO: traduci label picklist
function getAllPicklists() {
	global $adb, $table_prefix;

	$plist = array();

	$query = "
		SELECT vtab.name, vfield.fieldid, vfield.fieldname, vfield.uitype, vfield.fieldlabel
		from {$table_prefix}_field vfield
			inner join {$table_prefix}_tab vtab on vfield.tabid = vtab.tabid
		where vfield.uitype in (15,16,300) and vfield.presence in (0,2) and vtab.presence = 0 and vtab.tabid != 29
		order by vfield.tabid ASC";
	$res = $adb->query($query);

	if ($res) {
		while ($row = $adb->fetchByAssoc($res, -1, false)) {
			$modname = $row['name'];
			unset($row['name']);
			$row['fieldlabel'] = getTranslatedString($row['fieldlabel'], $modname);
			$plist[$modname][] = $row;
		}
		// rimuovo moduli con una sola picklist
		$plist = array_filter($plist, create_function('$v', 'return (count($v) > 1);'));
	}

	return $plist;
}

// restituisce un array con le connessioni esistenti (coppie di campi)
function getConnections($mod = 'All') {
	global $adb, $table_prefix;

	$params = array();
	if (!empty($mod) && $mod != 'All') {
		$extrawhere = ' and vlink.module = ?';
		$params[] = $mod;
	}

	$query = "
		SELECT distinct vlink.picksrc, vlink.pickdest, vtab.name as modulename, vfield.fieldid, vfield.uitype, vfield.fieldlabel, vfield2.fieldlabel as fieldlabel2, vfield.tabid 
		from {$table_prefix}_linkedlist vlink
			inner join {$table_prefix}_tab vtab on vlink.module = vtab.name
			inner join {$table_prefix}_field vfield on vfield.fieldname = vlink.picksrc and vfield.tabid = vtab.tabid
			inner join {$table_prefix}_field vfield2 on vfield2.fieldname = vlink.pickdest and vfield2.tabid = vtab.tabid
		where vfield.uitype in (15,16,300) and vfield.presence in (0,2) and vtab.presence = 0 $extrawhere 
		order by vfield.tabid ASC";
	$res = $adb->pquery($query, $params);

	$plist = array();
	if ($res) {
		while ($row = $adb->fetchByAssoc($res, -1, false)) {
			$modname = $row['modulename'];
			unset($row['name']);
			$row['label1'] = getTranslatedString($row['fieldlabel'], $modname);
			$row['label2'] = getTranslatedString($row['fieldlabel2'], $modname);
			$plist[] = $row;
		}
	}

	return $plist;
}
?>