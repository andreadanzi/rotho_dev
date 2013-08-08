<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix;
session_start();

// Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');

// Create module instance and save it first
$module_relations = Vtiger_Module::getInstance('Relations');

//modifica ai campi to e from per metterci i Contatti
$fieldlink_from = Vtiger_Field::getInstance('link_from',$module_relations);
$fieldlink_to = Vtiger_Field::getInstance('link_to',$module_relations);
$fieldlink_from->setRelatedModules(Array('Contacts'));
$fieldlink_to->setRelatedModules(Array('Contacts'));
$fieldlink_from->save();
$fieldlink_to->save();

//relazione 1 a n Contacts
$contacts = Vtiger_Module::getInstance('Contacts');
$contacts->setRelatedList(Vtiger_Module::getInstance('Relations'), 'Relations', Array('ADD'), 'get_dependents_list');

//nuovi tipi relazione
$field1 = Vtiger_Field::getInstance('relation_name',$module_relations);
$field1->setPicklistValues( Array ( 'lbl_rel_member_of_association','lbl_rel_association_member_of', 'lbl_rel_pres_of_association','lbl_rel_association_pres_of') );

SDK::setLanguageEntry('Relations','it_it', 'lbl_rel_member_of_association' , 'Membro di');
SDK::setLanguageEntry('Relations','en_us', 'lbl_rel_member_of_association' , 'Member of');

SDK::setLanguageEntry('Relations','it_it','lbl_rel_association_member_of' , 'Ha come membro');
SDK::setLanguageEntry('Relations','en_us','lbl_rel_association_member_of' , 'Has as a member');

SDK::setLanguageEntry('Relations','it_it','lbl_rel_pres_of_association' , 'Presidente di');
SDK::setLanguageEntry('Relations','en_us','lbl_rel_pres_of_association' , 'President of');

SDK::setLanguageEntry('Relations','it_it','lbl_rel_association_pres_of' , 'Presieduta da');
SDK::setLanguageEntry('Relations','en_us','lbl_rel_association_pres_of' , 'Chaired by');

?>
