<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once('modules/Newsletter/Newsletter.php');

class SDK_Newsletter extends Newsletter {

	function unsubscribe($crmid) {
		//il controllo lo faccio sul campo email perch? se modifico il target e aggiungo un lead con 
		//la stessa email di un contatto che si ? gi? disiscritto non devo comunque mandargli la mail
		/*
		 * return: 1	done
		 * return: 2	already done
		 * return: 3	problems
		 */
		global $adb,$table_prefix;
		$module = getSalesEntityType($crmid);
		$focus = CRMEntity::getInstance($module);
		$focus->retrieve_entity_info($crmid,$module);
		$email = $focus->column_fields[$this->email_fields[$module]['fieldname']];
		//$focus->column_fields['newsletter_permission'] = 0; //per evitare problemi faccio un update secco;
		
		//mycrmv@orienta2014
		if($module == 'Accounts'){
			$adb->pquery('UPDATE '.$table_prefix.'_account SET newsletter_permission = 0 WHERE accountid = ?',array($crmid));
		}elseif($module == 'Contacts'){
			$adb->pquery('UPDATE '.$table_prefix.'_contactdetails SET newsletter_permission = 0 WHERE contactid = ?',array($crmid));
		}elseif($module == 'Leads'){
			$adb->pquery('UPDATE '.$table_prefix.'_leaddetails SET newsletter_permission = 0 WHERE leadid = ?',array($crmid));
		}
		//mycrmv@orienta2014e
		 		
		$result = $adb->pquery('select * from tbl_s_newsletter_unsub where newsletterid = ? and email = ?',array($this->id,$email));
		if ($result && $adb->num_rows($result)>0) {
			return 2;
		} else {
			$adb->pquery('insert into tbl_s_newsletter_unsub (newsletterid,email,type) values (?,?,?)',array($this->id,$email,'User unsubscription from email'));
			$result = $adb->pquery('select * from tbl_s_newsletter_unsub where newsletterid = ? and email = ?',array($this->id,$email));
			if ($result && $adb->num_rows($result)>0) {
				return 1;
			}
		}
		return 3;
	}
	
	/*
	 * Questa è la funzione che ritorna le email di quelli che sono unsubscribed.
	 */
	
	function getUnsubscriptedList() {
		global $adb,$table_prefix;
		$newsletterid = array();
		if ($this->column_fields['campaignid'] != '') {
			$result = $adb->query('SELECT newsletterid FROM '.$table_prefix.'_newsletter 
									INNER JOIN '.$table_prefix.'_crmentity ON '.$table_prefix.'_crmentity.crmid = '.$table_prefix.'_newsletter.newsletterid
									WHERE deleted = 0 AND campaignid = '.$this->column_fields['campaignid']);
			if ($result && $adb->num_rows($result)>0) {
				while($row=$adb->fetchByAssoc($result)) {
					$newsletterid[] = $row['newsletterid'];
				}
			}
		} else {
			$newsletterid[] = $this->id;
		}
		//mycrmv@orienta2014
		// danzi.tn@20140628 criterio di selezione solo per email valide <>'' e NOT NULL
		$unsubscripted = array();
//		$result = $adb->query('select email from tbl_s_newsletter_unsub where newsletterid in ('.implode(',',$newsletterid).')');
		//ACCOUNTS
		$result = $adb->query('SELECT email1 FROM '.$table_prefix.'_account
		INNER JOIN '.$table_prefix.'_crmentity ON '.$table_prefix.'_crmentity.crmid = '.$table_prefix.'_account.accountid
		WHERE deleted = 0 AND newsletter_permission = 0 AND email1<>\'\' AND email1 IS NOT NULL');
		if ($result && $adb->num_rows($result)>0) {
			while($row=$adb->fetchByAssoc($result)) {
				$unsubscripted[] = $row['email1'];
			}
		}
		//CONTACTS
		$result = $adb->query('SELECT email FROM '.$table_prefix.'_contactdetails
		INNER JOIN '.$table_prefix.'_crmentity ON '.$table_prefix.'_crmentity.crmid = '.$table_prefix.'_contactdetails.contactid
		WHERE deleted = 0 AND newsletter_permission = 0 AND email<>\'\' AND email IS NOT NULL');
		if ($result && $adb->num_rows($result)>0) {
			while($row=$adb->fetchByAssoc($result)) {
				$unsubscripted[] = $row['email'];
			}
		}
		//LEADS
		$result = $adb->query('SELECT email FROM '.$table_prefix.'_leaddetails
		INNER JOIN '.$table_prefix.'_crmentity ON '.$table_prefix.'_crmentity.crmid = '.$table_prefix.'_leaddetails.leadid
		WHERE deleted = 0 AND newsletter_permission = 0 AND email<>\'\' AND email IS NOT NULL');
		if ($result && $adb->num_rows($result)>0) {
			while($row=$adb->fetchByAssoc($result)) {
				$unsubscripted[] = $row['email'];
			}
		}
		//mycrmv@orienta2014e
		return $unsubscripted;
	}
} 
?>