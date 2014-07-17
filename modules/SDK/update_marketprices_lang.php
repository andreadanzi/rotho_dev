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

// Get module instance
SDK::setLanguageEntry('Marketprices','it_it', 'Marketprices' , 'Market Prices');
SDK::setLanguageEntry('Marketprices','en_us', 'Marketprices' , 'Market Prices');
SDK::setLanguageEntry('Marketprices','de_de', 'Marketprices' , 'Market Prices');

SDK::setLanguageEntry('Marketprices','it_it', 'SINGLE_Marketprices' , 'Market Price');
SDK::setLanguageEntry('Marketprices','en_us', 'SINGLE_Marketprices' , 'Market Price');
SDK::setLanguageEntry('Marketprices','de_de', 'SINGLE_Marketprices' , 'Market Price');

SDK::setLanguageEntry('Marketprices','it_it', 'Market Price Name' , 'Nome Market Price');
SDK::setLanguageEntry('Marketprices','en_us', 'Market Price Name' , 'Market Price Name');
SDK::setLanguageEntry('Marketprices','de_de', 'Market Price Name' , 'Market Price Name');


SDK::setLanguageEntry('Marketprices','it_it', 'LBL_MARKETPRICES_INFORMATION' , 'Informazioni Prezzi di Mercato');
SDK::setLanguageEntry('Marketprices','en_us', 'LBL_MARKETPRICES_INFORMATION' , 'Market Prices Information');
SDK::setLanguageEntry('Marketprices','de_de', 'LBL_MARKETPRICES_INFORMATION' , 'Market Prices Information');

SDK::setLanguageEntry('Marketprices','it_it', 'Rothoblaas Category' , 'Categoria prodotto Rothoblaas');
SDK::setLanguageEntry('Marketprices','en_us', 'Rothoblaas Category' , 'Rothoblaas Category');
SDK::setLanguageEntry('Marketprices','de_de', 'Rothoblaas Category' , 'Rothoblaas Category');

SDK::setLanguageEntry('Marketprices','it_it', 'Competitor' , 'Concorrente');
SDK::setLanguageEntry('Marketprices','en_us', 'Competitor' , 'Competitor');
SDK::setLanguageEntry('Marketprices','de_de', 'Competitor' , 'Competitor');


SDK::setLanguageEntry('Marketprices','it_it', 'Currency' , 'Divisa');
SDK::setLanguageEntry('Marketprices','en_us', 'Currency' , 'Currency');
SDK::setLanguageEntry('Marketprices','de_de', 'Currency' , 'Currency');


SDK::setLanguageEntry('Marketprices','it_it', 'Customer ' , 'Cliente');
SDK::setLanguageEntry('Marketprices','en_us', 'Customer ' , 'Customer ');
SDK::setLanguageEntry('Marketprices','de_de', 'Customer ' , 'Customer ');

SDK::setLanguageEntry('Marketprices','it_it', 'Competitor Product Code' , 'Codice Prodotto Concorrente');
SDK::setLanguageEntry('Marketprices','en_us', 'Competitor Product Code' , 'Competitor Product Code');
SDK::setLanguageEntry('Marketprices','de_de', 'Competitor Product Code' , 'Competitor Product Code');

SDK::setLanguageEntry('Marketprices','it_it', 'Competitor Product Description' , 'Descrizione Prodotto Concorrente');
SDK::setLanguageEntry('Marketprices','en_us', 'Competitor Product Description' , 'Competitor Product Description');
SDK::setLanguageEntry('Marketprices','de_de', 'Competitor Product Description' , 'Competitor Product Description');

SDK::setLanguageEntry('Marketprices','it_it', 'Rothoblaas Product code' , 'Codice prodotto Rothoblaas');
SDK::setLanguageEntry('Marketprices','en_us', 'Rothoblaas Product code' , 'Rothoblaas product code');
SDK::setLanguageEntry('Marketprices','de_de', 'Rothoblaas Product code' , 'Rothoblaas product code');

SDK::setLanguageEntry('Marketprices','it_it', 'Rothoblaas Product Description' , 'Descrizione prodotto Rothoblaas');
SDK::setLanguageEntry('Marketprices','en_us', 'Rothoblaas Product Description' , 'Rothoblaas Product Description');
SDK::setLanguageEntry('Marketprices','de_de', 'Rothoblaas Product Description' , 'Rothoblaas Product Description');

SDK::setLanguageEntry('Marketprices','it_it', 'Rothoblaas Category Description' , 'Descrizione Categoria');
SDK::setLanguageEntry('Marketprices','en_us', 'Rothoblaas Category Description' , 'Category Description');
SDK::setLanguageEntry('Marketprices','de_de', 'Rothoblaas Category Description' , 'Category Description');

SDK::setLanguageEntry('Marketprices','it_it', 'Quantity' , 'Quantit&agrave;');
SDK::setLanguageEntry('Marketprices','en_us', 'Quantity' , 'Quantity');
SDK::setLanguageEntry('Marketprices','de_de', 'Quantity' , 'Quantity');

SDK::setLanguageEntry('Marketprices','it_it', 'Unit of measure' , 'Unit&agrave; di misura');
SDK::setLanguageEntry('Marketprices','en_us', 'Unit of measure' , 'Unit of measure');
SDK::setLanguageEntry('Marketprices','de_de', 'Unit of measure' , 'Unit of measure');

SDK::setLanguageEntry('Marketprices','it_it', 'Net Price' , 'Prezzo Netto');
SDK::setLanguageEntry('Marketprices','en_us', 'Net Price' , 'Net Price');
SDK::setLanguageEntry('Marketprices','de_de', 'Net Price' , 'Net Price');


SDK::setLanguageEntry('Marketprices','it_it', 'Area Manager No' , 'Numero A.M.');
SDK::setLanguageEntry('Marketprices','en_us', 'Area Manager No' , 'Area Manager No');
SDK::setLanguageEntry('Marketprices','de_de', 'Area Manager No' , 'Area Manager No');


SDK::setLanguageEntry('Marketprices','it_it', 'Area Manager Name' , 'Nome A.M.');
SDK::setLanguageEntry('Marketprices','en_us', 'Area Manager Name' , 'Area Manager Name');
SDK::setLanguageEntry('Marketprices','de_de', 'Area Manager Name' , 'Area Manager Name');

SDK::setLanguageEntry('Marketprices','it_it', 'Other Products ' , 'Altri Prodotti');
SDK::setLanguageEntry('Marketprices','en_us', 'Other Products ' , 'Other Products ');
SDK::setLanguageEntry('Marketprices','de_de', 'Other Products ' , 'Other Products ');

//'lbl_mkp_none', 'lbl_mkp_news', 'lbl_mkp_price', 'lbl_mkp_compet', 'lbl_mkp_other'

SDK::setLanguageEntry('Marketprices','it_it', 'lbl_mkp_none' , '--Nessuno--');
SDK::setLanguageEntry('Marketprices','en_us', 'lbl_mkp_none' , '--None--');
SDK::setLanguageEntry('Marketprices','de_de', 'lbl_mkp_none' , '--None--');

SDK::setLanguageEntry('Marketprices','it_it', 'lbl_mkp_news' , 'Notizia');
SDK::setLanguageEntry('Marketprices','en_us', 'lbl_mkp_news' , 'News');
SDK::setLanguageEntry('Marketprices','de_de', 'lbl_mkp_news' , 'News');

SDK::setLanguageEntry('Marketprices','it_it', 'lbl_mkp_price' , 'Prezzo');
SDK::setLanguageEntry('Marketprices','en_us', 'lbl_mkp_price' , 'Price');
SDK::setLanguageEntry('Marketprices','de_de', 'lbl_mkp_price' , 'Price');

SDK::setLanguageEntry('Marketprices','it_it', 'lbl_mkp_compet' , 'Concorrente');
SDK::setLanguageEntry('Marketprices','en_us', 'lbl_mkp_compet' , 'Competitor');
SDK::setLanguageEntry('Marketprices','de_de', 'lbl_mkp_compet' , 'Competitor');

SDK::setLanguageEntry('Marketprices','it_it', 'lbl_mkp_other' , 'Altro');
SDK::setLanguageEntry('Marketprices','en_us', 'lbl_mkp_other' , 'Other');
SDK::setLanguageEntry('Marketprices','de_de', 'lbl_mkp_other' , 'Other');



?>
