<?php
/***************************************************************************************
 * The contents of this file are subject to the CRMVILLAGE.BIZ VTECRM License Agreement
 * ("licenza.txt"); You may not use this file except in compliance with the License
 * The Original Code is:  CRMVILLAGE.BIZ VTECRM
 * The Initial Developer of the Original Code is CRMVILLAGE.BIZ.
 * Portions created by CRMVILLAGE.BIZ are Copyright (C) CRMVILLAGE.BIZ.
 * All Rights Reserved.
 ***************************************************************************************/
// danzi.tn@20150806 copia dei valori provenienti da un reclamo (HelpDesk) 	 
if(isset($_REQUEST['return_module']) && $_REQUEST['return_module'] =='HelpDesk' && isset($_REQUEST['ticket_id']) && $_REQUEST['ticket_id'] > 0) {
    $when = array();
    $when['Prodotto incompleto']['Fornitore'] = array("Vendor","Prodotto","Aperta PE");
    $when['Difetto prodotto']['Fornitore'] = array("Vendor","Prodotto","Aperta PE");
    $when['Materiale danneggiato - confezione']['Fornitore'] = array("Vendor","Servizio","Aperta PO");
    $when['Materiale danneggiato - confezione']['Trasportatore'] = array("Vendor","Servizio","Aperta PO");
    $when['Quantita` errata']['Fornitore'] = array("Vendor","Servizio","Aperta PO");
    $when['Articolo sbagliato']['Fornitore'] = array("Vendor","Servizio","Aperta PO");
    $when['Consegna in ritardo']['Fornitore'] = array("Vendor","Servizio","Aperta PO");
    $when['Consegna in ritardo']['Trasportatore'] = array("Vendor","Servizio","Aperta PO");
    $when['Consegna in ritardo']['RB-Acquisto Interno'] = array("Internal","Servizio","Aperta PO");
    $when['Smarrito']['Fornitore'] = array("Vendor","Servizio","Aperta PO");
    global $adb, $table_prefix, $current_user;
    $query = "SELECT 
                tt.ticket_no,
                tt.title,
                tt.parent_id,
                a.accountname,
                tt.product_id, 
                tt.subcategories,
                tc.cf_777,
                tc.cf_798,
                tc.cf_1061,
                p.productname,
                p.product_cat,
                p.prod_category_desc,
                p.vendor_id,
                p.product_resp_no,
                p.product_resp_name,
                v.vendorname,
                v.purchase_user_id,
                u.user_name as purchase_user_uname,
                u.last_name as purchase_user_lname, 
                u.first_name as purchase_user_fname,
                pe.description
                FROM ".$table_prefix."_troubletickets tt
                JOIN ".$table_prefix."_ticketcf tc on tc.ticketid = tt.ticketid 
                LEFT JOIN ".$table_prefix."_account a on a.accountid = tt.parent_id
                LEFT JOIN ".$table_prefix."_products p on p.productid = tt.product_id
                LEFT JOIN ".$table_prefix."_crmentity pe on pe.crmid = p.productid
                LEFT JOIN ".$table_prefix."_vendor v ON p.vendor_id = v.vendorid  
                LEFT JOIN ".$table_prefix."_users u ON u.id = v.purchase_user_id 
                WHERE tt.ticketid = ?";
    $ticket_id =  vtlib_purify($_REQUEST['ticket_id']);
    $result = $adb->pquery($query,array($ticket_id));
    if ($result && $adb->num_rows($result)>0) {
        $title = $adb->query_result($result,0,'title');
        $ticket_no = $adb->query_result($result,0,'ticket_no');
        $parent_id = $adb->query_result($result,0,'parent_id');
        $accountname = $adb->query_result($result,0,'accountname');
        $product_id = $adb->query_result($result,0,'product_id');
        $productname = $adb->query_result($result,0,'productname');
        $product_resp_no = $adb->query_result($result,0,'product_resp_no');
        $product_resp_name = $adb->query_result($result,0,'product_resp_name');
        $subcategories = $adb->query_result($result,0,'subcategories');
        $numero_lotto = $adb->query_result($result,0,'cf_777');
        $errore_da_parte_di = $adb->query_result($result,0,'cf_798');
        $fonte = $adb->query_result($result,0,'cf_1061');
        $product_cat = $adb->query_result($result,0,'product_cat');
        $prod_category_desc = $adb->query_result($result,0,'prod_category_desc');
        $vendor_id = $adb->query_result($result,0,'vendor_id');
        $vendor_id_display = $adb->query_result($result,0,'vendorname');
        $purchase_user_id = $adb->query_result($result,0,'purchase_user_id');
		$purchase_user_id_display = $adb->query_result($result,0,'purchase_user_uname') ." (".$adb->query_result($result,0,'purchase_user_lname')." ".$adb->query_result($result,0,'purchase_user_fname').")";
        $prod_descr = $adb->query_result($result,0,'description');
        if(	isset($when[$subcategories][$errore_da_parte_di] ) ) {
            $source = $when[$subcategories][$errore_da_parte_di];
            $_REQUEST['nc_source'] = $source[0];
            $_REQUEST['cf_1273'] = $source[1];
            $_REQUEST['nonconformity_state'] = $source[2];
        }
        $filled_by_id = $current_user->id;
        $description= "Generata manualmente a partire da " . $ticket_no . " (".$accountname.") fonte " . $fonte . ", errore da parte di " .$errore_da_parte_di . " - " . $subcategories;
        $_REQUEST['nonconformity_name'] = $title . " (MAN. GEN.)";
        $_REQUEST['product_id_display'] = $productname;
        $_REQUEST['product_description'] = $prod_descr;
        $_REQUEST['product_id'] = $product_id;
        $_REQUEST['product_category'] = $product_cat;
        $_REQUEST['cf_1257'] = $numero_lotto;
        $_REQUEST['description'] = $description;
        $_REQUEST['vendor_id_display'] = $vendor_id_display;
        $_REQUEST['vendor_id'] = $vendor_id;
        $_REQUEST['purchase_user_id'] = $purchase_user_id;
        $_REQUEST['purchase_user_id_display'] = $purchase_user_id_display;
        $_REQUEST['product_resp_no'] = $product_resp_no;
        $_REQUEST['product_resp_name'] = $product_resp_name;
        // $_REQUEST['purchase_user_id'] da dove viene questo ?
    }		
}
// danzi.tn@20150806e
 
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
    // select * from vtiger_groups where vtiger_groups.groupname = 'Acquisti'
    $smarty->assign("DEFAULTGROUP","133018");
    $smarty->assign("DEFAULTGROUP_DESCR","Acquisti");
	$smarty->display('modules/Nonconformities/CreateView.tpl');
}
	//$smarty->display('CreateView.tpl');
// danzi.tn@20141024e
?>



