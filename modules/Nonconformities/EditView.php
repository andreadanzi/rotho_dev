<?php
/***************************************************************************************
 * The contents of this file are subject to the CRMVILLAGE.BIZ VTECRM License Agreement
 * ("licenza.txt"); You may not use this file except in compliance with the License
 * The Original Code is:  CRMVILLAGE.BIZ VTECRM
 * The Initial Developer of the Original Code is CRMVILLAGE.BIZ.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
 ***************************************************************************************/
require_once 'modules/VteCore/EditView.php';	//crmv@30447


// danzi.tn@20141024 gestione custom della valutazione
$vtiger_field = array(
	array( // row #14
		'columnname' => 'rilavorazione',
	),
	array( // row #15
		'columnname' => 'logistica',
	),
	array( // row #16
		'columnname' => 'magazzino',
	),
	array( // row #17
		'columnname' => 'acquisto',
	),
	array( // row #18
		'columnname' => 'danno_comm',
	),
	array( // row #19
		'columnname' => 'danno_comm_perd_ord',
	),
	array( // row #20
		'columnname' => 'dati_comm',
	),
	array( // row #21
		'columnname' => 'dati_comm_fatt_dann',
	),
	array( // row #22
		'columnname' => 'dati_comm_note_acc',
	),
	array( // row #23
		'columnname' => 'dati_comm_fermo_can',
	),
	array( // row #24
		'columnname' => 'dati_comm_omaggio',
	),
	array( // row #25
		'columnname' => 'totale_valutazione',
	),
	array( // row #26
		'columnname' => 'danno_comm_entr_conc',
	),
	array( // row #27
		'columnname' => 'danno_comm_perd_mar',
	),
	array( // row #28
		'columnname' => 'danno_comm_perd_cli',
	),
	array( // row #29
		'columnname' => 'danno_comm_perd_fatt',
	),
	array( // row #30
		'columnname' => 'danno_comm_dann_imm',
	),
	array( // row #31
		'columnname' => 'danno_comm_varie',
	),
	array( // row #32
		'columnname' => 'gestione',
	),
);
if ($focus->mode == 'edit')
{
    foreach($vtiger_field as $row) {
        //  echo strtoupper($row['columnname']) ."=".$focus->column_fields[$row['columnname']]."\n";
        $smarty->assign(strtoupper($row['columnname']),$focus->column_fields[$row['columnname']]);
    }
	$smarty->display('modules/Nonconformities/salesEditView.tpl');
}
else {
	$smarty->display('modules/Nonconformities/CreateView.tpl');
}
	//$smarty->display('CreateView.tpl');
// danzi.tn@20141024e
?>



