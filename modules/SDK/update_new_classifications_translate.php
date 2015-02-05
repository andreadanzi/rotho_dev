<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix;
session_start();

//Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');
//danzi.tn@20141212 nova classificazione cf_762 sostituito con vtiger_account.account_line
SDK::setLanguageEntry('Accounts' , 'it_it' , 'UTILIZZATORE' , 'Utilizzatore');
SDK::setLanguageEntry('Accounts' , 'de_de' , 'UTILIZZATORE' , 'Anwender');
SDK::setLanguageEntry('Accounts' , 'fr_fr' , 'UTILIZZATORE' , 'Utilisateur');
SDK::setLanguageEntry('Accounts' , 'en_us' , 'UTILIZZATORE' , 'User');
SDK::setLanguageEntry('Accounts' , 'es_es' , 'UTILIZZATORE' , 'Aplicador');
SDK::setLanguageEntry('Accounts' , 'pt_pt' , 'UTILIZZATORE' , 'Utilizador');
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'UTILIZZATORE' , 'По́льзователь');
SDK::setLanguageEntry('Accounts' , 'it_it' , 'RIVENDITORE' , 'Rivenditore');
SDK::setLanguageEntry('Accounts' , 'de_de' , 'RIVENDITORE' , 'Händler');
SDK::setLanguageEntry('Accounts' , 'fr_fr' , 'RIVENDITORE' , 'Distributeur');
SDK::setLanguageEntry('Accounts' , 'en_us' , 'RIVENDITORE' , 'Retailer');
SDK::setLanguageEntry('Accounts' , 'es_es' , 'RIVENDITORE' , 'Distribuidor');
SDK::setLanguageEntry('Accounts' , 'pt_pt' , 'RIVENDITORE' , 'Distribuidor');
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'RIVENDITORE' , 'Аге́нт по перепрода́же');
SDK::setLanguageEntry('Accounts' , 'it_it' , 'PROGETTISTA' , 'Progettisti');
SDK::setLanguageEntry('Accounts' , 'de_de' , 'PROGETTISTA' , 'Planer');
SDK::setLanguageEntry('Accounts' , 'fr_fr' , 'PROGETTISTA' , 'Bureau d\'&eacute;tudes');
SDK::setLanguageEntry('Accounts' , 'en_us' , 'PROGETTISTA' , 'Designer');
SDK::setLanguageEntry('Accounts' , 'es_es' , 'PROGETTISTA' , 'Proyectista');
SDK::setLanguageEntry('Accounts' , 'pt_pt' , 'PROGETTISTA' , 'Projetista');
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'PROGETTISTA' , 'Планови́к');
SDK::setLanguageEntry('Accounts' , 'it_it' , 'INFLUENZATORE' , 'Stakeholder');
SDK::setLanguageEntry('Accounts' , 'de_de' , 'INFLUENZATORE' , 'Stakeholder');
SDK::setLanguageEntry('Accounts' , 'fr_fr' , 'INFLUENZATORE' , 'Intervenants');
SDK::setLanguageEntry('Accounts' , 'en_us' , 'INFLUENZATORE' , 'Stakeholder');
SDK::setLanguageEntry('Accounts' , 'es_es' , 'INFLUENZATORE' , 'Stakeholder');
SDK::setLanguageEntry('Accounts' , 'pt_pt' , 'INFLUENZATORE' , 'Stakeholder');
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'INFLUENZATORE' , 'Стейкхолдер');
SDK::setLanguageEntry('Visitreport' , 'it_it' , 'UTILIZZATORE' , 'Utilizzatore');
SDK::setLanguageEntry('Visitreport' , 'de_de' , 'UTILIZZATORE' , 'Anwender');
SDK::setLanguageEntry('Visitreport' , 'fr_fr' , 'UTILIZZATORE' , 'Utilisateur');
SDK::setLanguageEntry('Visitreport' , 'en_us' , 'UTILIZZATORE' , 'User');
SDK::setLanguageEntry('Visitreport' , 'es_es' , 'UTILIZZATORE' , 'Aplicador');
SDK::setLanguageEntry('Visitreport' , 'pt_pt' , 'UTILIZZATORE' , 'Utilizador');
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'UTILIZZATORE' , 'По́льзователь');
SDK::setLanguageEntry('Visitreport' , 'it_it' , 'RIVENDITORE' , 'Rivenditore');
SDK::setLanguageEntry('Visitreport' , 'de_de' , 'RIVENDITORE' , 'Händler');
SDK::setLanguageEntry('Visitreport' , 'fr_fr' , 'RIVENDITORE' , 'Distributeur');
SDK::setLanguageEntry('Visitreport' , 'en_us' , 'RIVENDITORE' , 'Retailer');
SDK::setLanguageEntry('Visitreport' , 'es_es' , 'RIVENDITORE' , 'Distribuidor');
SDK::setLanguageEntry('Visitreport' , 'pt_pt' , 'RIVENDITORE' , 'Distribuidor');
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'RIVENDITORE' , 'Аге́нт по перепрода́же');
SDK::setLanguageEntry('Visitreport' , 'it_it' , 'PROGETTISTA' , 'Progettisti');
SDK::setLanguageEntry('Visitreport' , 'de_de' , 'PROGETTISTA' , 'Planer');
SDK::setLanguageEntry('Visitreport' , 'fr_fr' , 'PROGETTISTA' , 'Bureau d\'&eacute;tudes');
SDK::setLanguageEntry('Visitreport' , 'en_us' , 'PROGETTISTA' , 'Designer');
SDK::setLanguageEntry('Visitreport' , 'es_es' , 'PROGETTISTA' , 'Proyectista');
SDK::setLanguageEntry('Visitreport' , 'pt_pt' , 'PROGETTISTA' , 'Projetista');
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'PROGETTISTA' , 'Планови́к');
SDK::setLanguageEntry('Visitreport' , 'it_it' , 'INFLUENZATORE' , 'Stakeholder');
SDK::setLanguageEntry('Visitreport' , 'de_de' , 'INFLUENZATORE' , 'Stakeholder');
SDK::setLanguageEntry('Visitreport' , 'fr_fr' , 'INFLUENZATORE' , 'Intervenants');
SDK::setLanguageEntry('Visitreport' , 'en_us' , 'INFLUENZATORE' , 'Stakeholder');
SDK::setLanguageEntry('Visitreport' , 'es_es' , 'INFLUENZATORE' , 'Stakeholder');
SDK::setLanguageEntry('Visitreport' , 'pt_pt' , 'INFLUENZATORE' , 'Stakeholder');
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'INFLUENZATORE' , 'Стейкхолдер');
SDK::setLanguageEntry('Leads' , 'it_it' , 'UTILIZZATORE' , 'Utilizzatore');
SDK::setLanguageEntry('Leads' , 'de_de' , 'UTILIZZATORE' , 'Anwender');
SDK::setLanguageEntry('Leads' , 'fr_fr' , 'UTILIZZATORE' , 'Utilisateur');
SDK::setLanguageEntry('Leads' , 'en_us' , 'UTILIZZATORE' , 'User');
SDK::setLanguageEntry('Leads' , 'es_es' , 'UTILIZZATORE' , 'Aplicador');
SDK::setLanguageEntry('Leads' , 'pt_pt' , 'UTILIZZATORE' , 'Utilizador');
SDK::setLanguageEntry('Leads' , 'ru_ru' , 'UTILIZZATORE' , 'По́льзователь');
SDK::setLanguageEntry('Leads' , 'it_it' , 'RIVENDITORE' , 'Rivenditore');
SDK::setLanguageEntry('Leads' , 'de_de' , 'RIVENDITORE' , 'Händler');
SDK::setLanguageEntry('Leads' , 'fr_fr' , 'RIVENDITORE' , 'Distributeur');
SDK::setLanguageEntry('Leads' , 'en_us' , 'RIVENDITORE' , 'Retailer');
SDK::setLanguageEntry('Leads' , 'es_es' , 'RIVENDITORE' , 'Distribuidor');
SDK::setLanguageEntry('Leads' , 'pt_pt' , 'RIVENDITORE' , 'Distribuidor');
SDK::setLanguageEntry('Leads' , 'ru_ru' , 'RIVENDITORE' , 'Аге́нт по перепрода́же');
SDK::setLanguageEntry('Leads' , 'it_it' , 'PROGETTISTA' , 'Progettisti');
SDK::setLanguageEntry('Leads' , 'de_de' , 'PROGETTISTA' , 'Planer');
SDK::setLanguageEntry('Leads' , 'fr_fr' , 'PROGETTISTA' , 'Bureau d\'&eacute;tudes');
SDK::setLanguageEntry('Leads' , 'en_us' , 'PROGETTISTA' , 'Designer');
SDK::setLanguageEntry('Leads' , 'es_es' , 'PROGETTISTA' , 'Proyectista');
SDK::setLanguageEntry('Leads' , 'pt_pt' , 'PROGETTISTA' , 'Projetista');
SDK::setLanguageEntry('Leads' , 'ru_ru' , 'PROGETTISTA' , 'Планови́к');
SDK::setLanguageEntry('Leads' , 'it_it' , 'INFLUENZATORE' , 'Stakeholder');
SDK::setLanguageEntry('Leads' , 'de_de' , 'INFLUENZATORE' , 'Stakeholder');
SDK::setLanguageEntry('Leads' , 'fr_fr' , 'INFLUENZATORE' , 'Intervenants');
SDK::setLanguageEntry('Leads' , 'en_us' , 'INFLUENZATORE' , 'Stakeholder');
SDK::setLanguageEntry('Leads' , 'es_es' , 'INFLUENZATORE' , 'Stakeholder');
SDK::setLanguageEntry('Leads' , 'pt_pt' , 'INFLUENZATORE' , 'Stakeholder');
SDK::setLanguageEntry('Leads' , 'ru_ru' , 'INFLUENZATORE' , 'Стейкхолдер');
SDK::setLanguageEntry('Accounts' , 'it_it' , 'Linea' , 'Linea di vendita');
SDK::setLanguageEntry('Accounts' , 'de_de' , 'Linea' , 'Verkaufsleitung');
SDK::setLanguageEntry('Accounts' , 'fr_fr' , 'Linea' , 'Ligne de vente');
SDK::setLanguageEntry('Accounts' , 'en_us' , 'Linea' , 'Sales line');
SDK::setLanguageEntry('Accounts' , 'es_es' , 'Linea' , 'Linea de Ventas');
SDK::setLanguageEntry('Accounts' , 'pt_pt' , 'Linea' , 'Linha de Vendas');
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'Linea' , 'Линия по продажам');
SDK::setLanguageEntry('Accounts' , 'it_it' , 'Tipo Cliente' , 'Tipo cliente');
SDK::setLanguageEntry('Accounts' , 'de_de' , 'Tipo Cliente' , 'Kundentypologie');
SDK::setLanguageEntry('Accounts' , 'fr_fr' , 'Tipo Cliente' , 'Tipologie du client');
SDK::setLanguageEntry('Accounts' , 'en_us' , 'Tipo Cliente' , 'Customer type');
SDK::setLanguageEntry('Accounts' , 'es_es' , 'Tipo Cliente' , 'Tipologia de cliente');
SDK::setLanguageEntry('Accounts' , 'pt_pt' , 'Tipo Cliente' , 'Tipo do cliente');
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'Tipo Cliente' , 'Тип клиента');
SDK::setLanguageEntry('Accounts' , 'it_it' , 'Attivita Principale' , 'Attivit&agrave; principale');
SDK::setLanguageEntry('Accounts' , 'de_de' , 'Attivita Principale' , 'Kundenaktivität (1)');
SDK::setLanguageEntry('Accounts' , 'fr_fr' , 'Attivita Principale' , 'Activit&eacute; du client (1)');
SDK::setLanguageEntry('Accounts' , 'en_us' , 'Attivita Principale' , 'Main customer activity');
SDK::setLanguageEntry('Accounts' , 'es_es' , 'Attivita Principale' , 'Actividad del cliente (1)');
SDK::setLanguageEntry('Accounts' , 'pt_pt' , 'Attivita Principale' , 'Atividades do cliente (1)');
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'Attivita Principale' , 'Деятельности заказчика (1)');
SDK::setLanguageEntry('Accounts' , 'it_it' , 'Attivita Secondaria' , 'Attivit&agrave; secondaria');
SDK::setLanguageEntry('Accounts' , 'de_de' , 'Attivita Secondaria' , 'Kundenaktivität (2)');
SDK::setLanguageEntry('Accounts' , 'fr_fr' , 'Attivita Secondaria' , 'Activit&eacute; du client (2)');
SDK::setLanguageEntry('Accounts' , 'en_us' , 'Attivita Secondaria' , 'Secondary customer activity');
SDK::setLanguageEntry('Accounts' , 'es_es' , 'Attivita Secondaria' , 'Actividad del cliente (2)');
SDK::setLanguageEntry('Accounts' , 'pt_pt' , 'Attivita Secondaria' , 'Atividades do cliente (2)');
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'Attivita Secondaria' , 'Деятельности заказчика (2)');
SDK::setLanguageEntry('Accounts' , 'it_it' , 'Marchio' , 'Marchio');
SDK::setLanguageEntry('Accounts' , 'de_de' , 'Marchio' , 'Marke');
SDK::setLanguageEntry('Accounts' , 'fr_fr' , 'Marchio' , 'Marque');
SDK::setLanguageEntry('Accounts' , 'en_us' , 'Marchio' , 'Brand');
SDK::setLanguageEntry('Accounts' , 'es_es' , 'Marchio' , 'Marca');
SDK::setLanguageEntry('Accounts' , 'pt_pt' , 'Marchio' , 'Marca');
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'Marchio' , 'Марка');
SDK::setLanguageEntry('Accounts' , 'it_it' , 'Potenziale &euro;/Anno' , 'Potenziale &euro;/Anno');
SDK::setLanguageEntry('Accounts' , 'de_de' , 'Potenziale &euro;/Anno' , 'Potenzielle &euro;/Jahr');
SDK::setLanguageEntry('Accounts' , 'fr_fr' , 'Potenziale &euro;/Anno' , 'Potentiels &euro;/ann&eacute;e');
SDK::setLanguageEntry('Accounts' , 'en_us' , 'Potenziale &euro;/Anno' , 'Potential &euro;/year');
SDK::setLanguageEntry('Accounts' , 'es_es' , 'Potenziale &euro;/Anno' , 'Posibles &euro;/año');
SDK::setLanguageEntry('Accounts' , 'pt_pt' , 'Potenziale &euro;/Anno' , 'Potenciais &euro;/ano');
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'Potenziale &euro;/Anno' , 'Потенциальные &euro;/год');
SDK::setLanguageEntry('Accounts' , 'it_it' , 'Area di intervento' , 'Area di intervento');
SDK::setLanguageEntry('Accounts' , 'de_de' , 'Area di intervento' , 'Interventionsbereich');
SDK::setLanguageEntry('Accounts' , 'fr_fr' , 'Area di intervento' , 'Zone d\'intervention');
SDK::setLanguageEntry('Accounts' , 'en_us' , 'Area di intervento' , 'Area of intervention');
SDK::setLanguageEntry('Accounts' , 'es_es' , 'Area di intervento' , 'Ambito de intervencion');
SDK::setLanguageEntry('Accounts' , 'pt_pt' , 'Area di intervento' , 'Area de intervencao');
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'Area di intervento' , 'Область вмешательства');
SDK::setLanguageEntry('Accounts' , 'it_it' , 'Locale' , 'Locale');
SDK::setLanguageEntry('Accounts' , 'de_de' , 'Locale' , 'Lokal');
SDK::setLanguageEntry('Accounts' , 'fr_fr' , 'Locale' , 'Local');
SDK::setLanguageEntry('Accounts' , 'en_us' , 'Locale' , 'Local');
SDK::setLanguageEntry('Accounts' , 'es_es' , 'Locale' , 'Local');
SDK::setLanguageEntry('Accounts' , 'pt_pt' , 'Locale' , 'Local');
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'Locale' , 'Местный');
SDK::setLanguageEntry('Accounts' , 'it_it' , 'Nazionale' , 'Nazionale');
SDK::setLanguageEntry('Accounts' , 'de_de' , 'Nazionale' , 'Nationale');
SDK::setLanguageEntry('Accounts' , 'fr_fr' , 'Nazionale' , 'National');
SDK::setLanguageEntry('Accounts' , 'en_us' , 'Nazionale' , 'National');
SDK::setLanguageEntry('Accounts' , 'es_es' , 'Nazionale' , 'National');
SDK::setLanguageEntry('Accounts' , 'pt_pt' , 'Nazionale' , 'National');
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'Nazionale' , 'Национальный');
SDK::setLanguageEntry('Accounts' , 'it_it' , 'Internazionale' , 'Internazionale');
SDK::setLanguageEntry('Accounts' , 'de_de' , 'Internazionale' , 'International');
SDK::setLanguageEntry('Accounts' , 'fr_fr' , 'Internazionale' , 'International');
SDK::setLanguageEntry('Accounts' , 'en_us' , 'Internazionale' , 'International');
SDK::setLanguageEntry('Accounts' , 'es_es' , 'Internazionale' , 'International');
SDK::setLanguageEntry('Accounts' , 'pt_pt' , 'Internazionale' , 'International');
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'Internazionale' , 'Международный');
SDK::setLanguageEntry('Visitreport' , 'it_it' , 'Tipo Cliente' , 'Tipo cliente');
SDK::setLanguageEntry('Visitreport' , 'de_de' , 'Tipo Cliente' , 'Kundentypologie');
SDK::setLanguageEntry('Visitreport' , 'fr_fr' , 'Tipo Cliente' , 'Tipologie du client');
SDK::setLanguageEntry('Visitreport' , 'en_us' , 'Tipo Cliente' , 'Customer type');
SDK::setLanguageEntry('Visitreport' , 'es_es' , 'Tipo Cliente' , 'Tipologia de cliente');
SDK::setLanguageEntry('Visitreport' , 'pt_pt' , 'Tipo Cliente' , 'Tipo do cliente');
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'Tipo Cliente' , 'Тип клиента');
SDK::setLanguageEntry('Visitreport' , 'it_it' , 'Attivita Principale' , 'Attivit&agrave; principale');
SDK::setLanguageEntry('Visitreport' , 'de_de' , 'Attivita Principale' , 'Kundenaktivität (1)');
SDK::setLanguageEntry('Visitreport' , 'fr_fr' , 'Attivita Principale' , 'Activit&eacute; du client (1)');
SDK::setLanguageEntry('Visitreport' , 'en_us' , 'Attivita Principale' , 'Main customer activity');
SDK::setLanguageEntry('Visitreport' , 'es_es' , 'Attivita Principale' , 'Actividad del cliente (1)');
SDK::setLanguageEntry('Visitreport' , 'pt_pt' , 'Attivita Principale' , 'Atividades do cliente (1)');
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'Attivita Principale' , 'Деятельности заказчика (1)');
SDK::setLanguageEntry('Visitreport' , 'it_it' , 'Attivita Secondaria' , 'Attivit&agrave; secondaria');
SDK::setLanguageEntry('Visitreport' , 'de_de' , 'Attivita Secondaria' , 'Kundenaktivität (2)');
SDK::setLanguageEntry('Visitreport' , 'fr_fr' , 'Attivita Secondaria' , 'Activit&eacute; du client (2)');
SDK::setLanguageEntry('Visitreport' , 'en_us' , 'Attivita Secondaria' , 'Secondary customer activity');
SDK::setLanguageEntry('Visitreport' , 'es_es' , 'Attivita Secondaria' , 'Actividad del cliente (2)');
SDK::setLanguageEntry('Visitreport' , 'pt_pt' , 'Attivita Secondaria' , 'Atividades do cliente (2)');
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'Attivita Secondaria' , 'Деятельности заказчика (2)');
SDK::setLanguageEntry('Visitreport' , 'it_it' , 'Marchio' , 'Marchio');
SDK::setLanguageEntry('Visitreport' , 'de_de' , 'Marchio' , 'Marke');
SDK::setLanguageEntry('Visitreport' , 'fr_fr' , 'Marchio' , 'Marque');
SDK::setLanguageEntry('Visitreport' , 'en_us' , 'Marchio' , 'Brand');
SDK::setLanguageEntry('Visitreport' , 'es_es' , 'Marchio' , 'Marca');
SDK::setLanguageEntry('Visitreport' , 'pt_pt' , 'Marchio' , 'Marca');
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'Marchio' , 'Марка');
SDK::setLanguageEntry('Visitreport' , 'it_it' , 'Potenziale &euro;/Anno' , 'Potenziale &euro;/Anno');
SDK::setLanguageEntry('Visitreport' , 'de_de' , 'Potenziale &euro;/Anno' , 'Potenzielle &euro;/Jahr');
SDK::setLanguageEntry('Visitreport' , 'fr_fr' , 'Potenziale &euro;/Anno' , 'Potentiels &euro;/ann&eacute;e');
SDK::setLanguageEntry('Visitreport' , 'en_us' , 'Potenziale &euro;/Anno' , 'Potential &euro;/year');
SDK::setLanguageEntry('Visitreport' , 'es_es' , 'Potenziale &euro;/Anno' , 'Posibles &euro;/año');
SDK::setLanguageEntry('Visitreport' , 'pt_pt' , 'Potenziale &euro;/Anno' , 'Potenciais &euro;/ano');
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'Potenziale &euro;/Anno' , 'Потенциальные &euro;/год');
SDK::setLanguageEntry('Visitreport' , 'it_it' , 'Area di intervento' , 'Area di intervento');
SDK::setLanguageEntry('Visitreport' , 'de_de' , 'Area di intervento' , 'Interventionsbereich');
SDK::setLanguageEntry('Visitreport' , 'fr_fr' , 'Area di intervento' , 'Zone d\'intervention');
SDK::setLanguageEntry('Visitreport' , 'en_us' , 'Area di intervento' , 'Area of intervention');
SDK::setLanguageEntry('Visitreport' , 'es_es' , 'Area di intervento' , 'Ambito de intervencion');
SDK::setLanguageEntry('Visitreport' , 'pt_pt' , 'Area di intervento' , 'Area de intervencao');
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'Area di intervento' , 'Область вмешательства');
SDK::setLanguageEntry('Visitreport' , 'it_it' , 'Locale' , 'Locale');
SDK::setLanguageEntry('Visitreport' , 'de_de' , 'Locale' , 'Lokal');
SDK::setLanguageEntry('Visitreport' , 'fr_fr' , 'Locale' , 'Local');
SDK::setLanguageEntry('Visitreport' , 'en_us' , 'Locale' , 'Local');
SDK::setLanguageEntry('Visitreport' , 'es_es' , 'Locale' , 'Local');
SDK::setLanguageEntry('Visitreport' , 'pt_pt' , 'Locale' , 'Local');
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'Locale' , 'Местный');
SDK::setLanguageEntry('Visitreport' , 'it_it' , 'Nazionale' , 'Nazionale');
SDK::setLanguageEntry('Visitreport' , 'de_de' , 'Nazionale' , 'Nationale');
SDK::setLanguageEntry('Visitreport' , 'fr_fr' , 'Nazionale' , 'National');
SDK::setLanguageEntry('Visitreport' , 'en_us' , 'Nazionale' , 'National');
SDK::setLanguageEntry('Visitreport' , 'es_es' , 'Nazionale' , 'National');
SDK::setLanguageEntry('Visitreport' , 'pt_pt' , 'Nazionale' , 'National');
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'Nazionale' , 'Национальный');
SDK::setLanguageEntry('Visitreport' , 'it_it' , 'Internazionale' , 'Internazionale');
SDK::setLanguageEntry('Visitreport' , 'de_de' , 'Internazionale' , 'International');
SDK::setLanguageEntry('Visitreport' , 'fr_fr' , 'Internazionale' , 'International');
SDK::setLanguageEntry('Visitreport' , 'en_us' , 'Internazionale' , 'International');
SDK::setLanguageEntry('Visitreport' , 'es_es' , 'Internazionale' , 'International');
SDK::setLanguageEntry('Visitreport' , 'pt_pt' , 'Internazionale' , 'International');
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'Internazionale' , 'Международный');
SDK::setLanguageEntry('Leads' , 'it_it' , 'Tipo Cliente' , 'Tipo cliente');
SDK::setLanguageEntry('Leads' , 'de_de' , 'Tipo Cliente' , 'Kundentypologie');
SDK::setLanguageEntry('Leads' , 'fr_fr' , 'Tipo Cliente' , 'Tipologie du client');
SDK::setLanguageEntry('Leads' , 'en_us' , 'Tipo Cliente' , 'Customer type');
SDK::setLanguageEntry('Leads' , 'es_es' , 'Tipo Cliente' , 'Tipologia de cliente');
SDK::setLanguageEntry('Leads' , 'pt_pt' , 'Tipo Cliente' , 'Tipo do cliente');
SDK::setLanguageEntry('Leads' , 'ru_ru' , 'Tipo Cliente' , 'Тип клиента');
SDK::setLanguageEntry('Leads' , 'it_it' , 'Linea' , 'Linea di vendita');
SDK::setLanguageEntry('Leads' , 'de_de' , 'Linea' , 'Verkaufsleitung');
SDK::setLanguageEntry('Leads' , 'fr_fr' , 'Linea' , 'Ligne de vente');
SDK::setLanguageEntry('Leads' , 'en_us' , 'Linea' , 'Sales line');
SDK::setLanguageEntry('Leads' , 'es_es' , 'Linea' , 'Linea de Ventas');
SDK::setLanguageEntry('Leads' , 'pt_pt' , 'Linea' , 'Linha de Vendas');
SDK::setLanguageEntry('Leads' , 'ru_ru' , 'Linea' , 'Линия по продажам');

?>