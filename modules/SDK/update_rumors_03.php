<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix;
session_start();

// danzi.tn@20140326 Mittente segnalazione, fonte, valute usate in rothoblaas, nome notizia –
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');

$module = Vtiger_Module::getInstance('Rumors');

$block_info = Vtiger_Block::getInstance('LBL_RUMORS_INFORMATION',$module); 

$field21 = new Vtiger_Field();
$field21->name = 'infosender';
$field21->table = $module->basetable;
$field21->label= 'Mittente segnalazione';
$field21->columntype = 'VARCHAR(255)';
$field21->uitype = 1;
$field21->typeofdata = 'V~O';
$block_info->addField($field21);

$field22 = new Vtiger_Field();
$field22->name = 'source';
$field22->table = $module->basetable;
$field22->label= 'Rumor Source';
$field22->columntype = 'VARCHAR(100)';
$field22->uitype = 15;
$field22->typeofdata = 'V~O';
$block_info->addField($field22);
$field22->setPicklistValues( Array ('Sconosciuta', 'Acquisti', 'Vendite', 'Fiera', 'Public relations', 'Altro') );

$field23 = new Vtiger_Field();
$field23->name = 'rumorscat';
$field23->table = $module->basetable;
$field23->label= 'Rumor Category';
$field23->columntype = 'VARCHAR(100)';
$field23->uitype = 15;
$field23->typeofdata = 'V~O';
$block_info->addField($field23); // Nuovo, Comparazione, Modifica.
$field23->setPicklistValues( Array ('---', 'Nuovo', 'Comparazione', 'Modifica', 'Altro') );

SDK::setLanguageEntry('Rumors','it_it', 'Mittente segnalazione' , 'Mittente segnalazione');
SDK::setLanguageEntry('Rumors','en_us', 'Mittente segnalazione' , 'Sender');
SDK::setLanguageEntry('Rumors','de_de', 'Mittente segnalazione' , 'Sender');

SDK::setLanguageEntry('Rumors','it_it', 'Rumor Source' , 'Fonte Rumor');
SDK::setLanguageEntry('Rumors','en_us', 'Rumor Source' , 'Rumor Source');
SDK::setLanguageEntry('Rumors','de_de', 'Rumor Source' , 'Rumor Source');
SDK::setLanguageEntry('Rumors','pt_br', 'Rumor Source' , 'Rumor Source');

SDK::setLanguageEntry('Rumors','it_it', 'Rumor Category' , 'Categoria Rumor');
SDK::setLanguageEntry('Rumors','en_us', 'Rumor Category' , 'Rumor Category');
SDK::setLanguageEntry('Rumors','de_de', 'Rumor Category' , 'Rumor Category');
SDK::setLanguageEntry('Rumors','pt_br', 'Rumor Category' , 'Rumor Category');

SDK::setLanguageEntry('Rumors','it_it', 'Sconosciuta' , 'Sconosciuta');
SDK::setLanguageEntry('Rumors','en_us', 'Sconosciuta' , 'Unknown');
SDK::setLanguageEntry('Rumors','de_de', 'Sconosciuta' , 'Unknown');
SDK::setLanguageEntry('Rumors','pt_br', 'Sconosciuta' , 'Unknown');

SDK::setLanguageEntry('Rumors','it_it', 'Acquisti' , 'Acquisti');
SDK::setLanguageEntry('Rumors','en_us', 'Acquisti' , 'Buying');
SDK::setLanguageEntry('Rumors','de_de', 'Acquisti' , 'Buying');
SDK::setLanguageEntry('Rumors','pt_br', 'Acquisti' , 'Buying');

SDK::setLanguageEntry('Rumors','it_it', 'Vendite' , 'Vendite');
SDK::setLanguageEntry('Rumors','en_us', 'Vendite' , 'Selling');
SDK::setLanguageEntry('Rumors','de_de', 'Vendite' , 'Selling');
SDK::setLanguageEntry('Rumors','pt_br', 'Vendite' , 'Selling');

SDK::setLanguageEntry('Rumors','it_it', 'Fiera' , 'Fiera');
SDK::setLanguageEntry('Rumors','en_us', 'Fiera' , 'Fair');
SDK::setLanguageEntry('Rumors','de_de', 'Fiera' , 'Fair');
SDK::setLanguageEntry('Rumors','pt_br', 'Fiera' , 'Fair');

SDK::setLanguageEntry('Rumors','it_it', 'Public relations' , 'Public relations');
SDK::setLanguageEntry('Rumors','en_us', 'Public relations' , 'Public relations');
SDK::setLanguageEntry('Rumors','de_de', 'Public relations' , 'Public relations');
SDK::setLanguageEntry('Rumors','pt_br', 'Public relations' , 'Public relations');

SDK::setLanguageEntry('Rumors','it_it', 'Altro' , 'Altro');
SDK::setLanguageEntry('Rumors','en_us', 'Altro' , 'Other');
SDK::setLanguageEntry('Rumors','de_de', 'Altro' , 'Other');
SDK::setLanguageEntry('Rumors','pt_br', 'Altro' , 'Other');

SDK::setLanguageEntry('Rumors','it_it', '---' , '---');
SDK::setLanguageEntry('Rumors','en_us', '---' , '---');
SDK::setLanguageEntry('Rumors','de_de', '---' , '---');
SDK::setLanguageEntry('Rumors','pt_br', '---' , '---');

SDK::setLanguageEntry('Rumors','it_it', 'Nuovo' , 'Nuovo');
SDK::setLanguageEntry('Rumors','en_us', 'Nuovo' , 'New');
SDK::setLanguageEntry('Rumors','de_de', 'Nuovo' , 'New');
SDK::setLanguageEntry('Rumors','pt_br', 'Nuovo' , 'New');

SDK::setLanguageEntry('Rumors','it_it', 'Comparazione' , 'Comparazione');
SDK::setLanguageEntry('Rumors','en_us', 'Comparazione' , 'Compare');
SDK::setLanguageEntry('Rumors','de_de', 'Comparazione' , 'Compare');
SDK::setLanguageEntry('Rumors','pt_br', 'Comparazione' , 'Compare');

SDK::setLanguageEntry('Rumors','it_it', 'Modifica' , 'Modifica');
SDK::setLanguageEntry('Rumors','en_us', 'Modifica' , 'Update');
SDK::setLanguageEntry('Rumors','de_de', 'Modifica' , 'Update');
SDK::setLanguageEntry('Rumors','pt_br', 'Modifica' , 'Update');

SDK::setLanguageEntry('Rumors','it_it', 'Altro' , 'Altro');
SDK::setLanguageEntry('Rumors','en_us', 'Altro' , 'Other');
SDK::setLanguageEntry('Rumors','de_de', 'Altro' , 'Other');
SDK::setLanguageEntry('Rumors','pt_br', 'Altro' , 'Other');


SDK::setLanguageEntry('Rumors','it_it', 'MSG_MANDATORY_ACCOUNT' , 'Per Rumors Prezzo &egrave; obbligatorio selezionare un Cliente');
SDK::setLanguageEntry('Rumors','en_us', 'MSG_MANDATORY_ACCOUNT' , 'For Price Rumors, Client is mandatory');
SDK::setLanguageEntry('Rumors','de_de', 'MSG_MANDATORY_ACCOUNT' , 'For Price Rumors, Client is mandatory');
SDK::setLanguageEntry('Rumors','pt_br', 'MSG_MANDATORY_ACCOUNT' , 'For Price Rumors, Client is mandatory');


SDK::setLanguageEntry('Rumors','it_it', 'MSG_MANDATORY_CATEGORY' , 'Per Rumors Prezzo &egrave; obbligatorio selezionare una Categoria Rothoblaas');
SDK::setLanguageEntry('Rumors','en_us', 'MSG_MANDATORY_CATEGORY' , 'For Price Rumors, Category is mandatory');
SDK::setLanguageEntry('Rumors','de_de', 'MSG_MANDATORY_CATEGORY' , 'For Price Rumors, Category is mandatory');
SDK::setLanguageEntry('Rumors','pt_br', 'MSG_MANDATORY_CATEGORY' , 'For Price Rumors, Category is mandatory');


SDK::setPreSave('Rumors', 'modules/SDK/src/modules/Rumors/PreSave.php');
?>
