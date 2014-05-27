<?php
// danzi.tn@20140326 gestione dipendenze tra rumor prezzo e clientre / categoria prodotto
if($type=='EditView') {
	if(isset($values['rumor_name']) && $values['rumor_name']=="Price") {
		if(empty($values['accounts_customer']) || empty($values['accounts_customer_display'])) {
			$status = false;
			$message = getTranslatedString("MSG_MANDATORY_ACCOUNT","Rumors");
			$focus = "accounts_customer_display";
		}
		if(empty($values['product_cat'])) {
			$status = false;
			$message = getTranslatedString("MSG_MANDATORY_CATEGORY","Rumors");
			$focus = "product_cat";
		}
	}
}
?>