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

global $table_prefix;
//echo $query;
//ho tolto la join sul nome utente rispetto a versione standard "INNER JOIN vt_tmp_u4 vt_tmp_u4 ON vt_tmp_u4.id = ".$table_prefix."_crmentity.smownerid": 
$query = "SELECT ".$table_prefix."_crmentity.crmid, ".$table_prefix."_crmentity.smownerid, ".$table_prefix."_account.accountname, ".$table_prefix."_account.email1, 
".$table_prefix."_account.email2, ".$table_prefix."_account.website, ".$table_prefix."_account.phone, ".$table_prefix."_accountbillads.bill_city, ".$table_prefix."_accountscf.* 
FROM ".$table_prefix."_account INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_account.accountid 
INNER JOIN ".$table_prefix."_accountbillads ON ".$table_prefix."_account.accountid = ".$table_prefix."_accountbillads.accountaddressid 
INNER JOIN ".$table_prefix."_accountshipads ON ".$table_prefix."_account.accountid = ".$table_prefix."_accountshipads.accountaddressid 
INNER JOIN ".$table_prefix."_accountscf ON ".$table_prefix."_account.accountid = ".$table_prefix."_accountscf.accountid 
LEFT JOIN ".$table_prefix."_groups ON ".$table_prefix."_groups.groupid = ".$table_prefix."_crmentity.smownerid 
LEFT JOIN ".$table_prefix."_users ON ".$table_prefix."_users.id = ".$table_prefix."_crmentity.smownerid 
LEFT JOIN ".$table_prefix."_account ".$table_prefix."_account2 ON ".$table_prefix."_account.parentid = ".$table_prefix."_account2.accountid 
WHERE ".$table_prefix."_account.accountid > 0 AND ".$table_prefix."_crmentity.deleted = 0";
// danzi.tn@20140909 eliminare filtro aziende concorrenti $query .= " and ".$table_prefix."_account.account_type = 'Concorrente' ";
?>