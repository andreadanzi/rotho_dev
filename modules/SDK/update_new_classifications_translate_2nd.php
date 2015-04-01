<?php
include_once('../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix,$default_charset;
session_start();
// html_entity_decode($description, ENT_NOQUOTES, $default_charset);
// htmlentities( , ENT_NOQUOTES, $default_charset);
//Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');
//danzi.tn@20141212 nova classificazione cf_762 sostituito con vtiger_account.account_line
SDK::setLanguageEntry('Accounts' , 'de_de' , 'RIVENDITORE' , htmlentities('Händler', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Visitreport' , 'de_de' , 'RIVENDITORE' , htmlentities('Händler', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'RIVENDITORE' , htmlentities('Аге́нт по перепрода́же', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'PROGETTISTA' , htmlentities('Планови́к', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'INFLUENZATORE' , htmlentities('Стейкхолдер', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Leads' , 'de_de' , 'RIVENDITORE' , htmlentities('Händler', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' , 'de_de' , 'Attivita Principale' , htmlentities('Kundenaktivität (1)', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' , 'de_de' , 'Attivita Secondaria' , htmlentities('Kundenaktivität (2)', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Visitreport' , 'de_de' , 'Attivita Principale' , htmlentities('Kundenaktivität (1)', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Visitreport' , 'de_de' , 'Attivita Secondaria' , htmlentities('Kundenaktivität (2)', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' , 'de_de' , 'Potenziale &euro;/Anno' ,  htmlentities('Potenzielle €/Jahr', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Visitreport' , 'de_de' , 'Potenziale &euro;/Anno' ,  htmlentities('Potenzielle €/Jahr', ENT_NOQUOTES, $default_charset));

SDK::setLanguageEntry('APP_STRINGS' ,'de_de' ,'ARCH.' , htmlentities('Architekturbüro', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('APP_STRINGS' ,'de_de' ,'ING.' , htmlentities('Bauingenieurbüro', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'de_de' ,'ARCH.' , htmlentities('Architekturbüro', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'de_de' ,'ING.' , htmlentities('Bauingenieurbüro', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'ARCH.' , htmlentities('Architekturbüro', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'ING.' , htmlentities('Bauingenieurbüro', ENT_NOQUOTES, $default_charset));


SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'AMMINISTRCOND' ,htmlentities('Кондоминиум Администратора', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'CARPENTERIA' ,htmlentities('Покрытия', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'CARPMET' ,htmlentities('Металлоконструкции', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'CASE IN LEGNO' ,htmlentities('Деревянные Дома', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'CONCIATETTO' ,htmlentities('Кро́вельщик', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'EDIFICI IN LEGNO' ,htmlentities('Imoveis Em Madeira', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'ENTEPUBB' ,htmlentities('Государственное Учреждение', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'FACCIATE E RIVESTIMENTI' ,htmlentities('Террасы Фасады', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'FALEGNAME/SERRAMENTISTA' ,htmlentities('Столя́р', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'FERRAMENTA E ATTREZZATURE' ,htmlentities('Скобяны́е Изде́лия Е Инструменты', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'GDO' ,htmlentities('Diy', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'GENERAL CONTRACTOR / IMMOBILIARE' ,htmlentities('Генеральный Подрядчик', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'GRANDI STRUTTURE' ,htmlentities('Большой Структуры', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'GROSSISTA' ,htmlentities('Оптовиков', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'IMPERM' ,htmlentities('Гидроизоляционные', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'IMPIANTISTI' ,htmlentities('Установки', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'IMPEDILE' ,htmlentities('Строительная Фирма', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'INDEXTRALEGNO' ,htmlentities('Промышленность Не Лес', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'LATTONERIA' ,htmlentities('Жестя́ник', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'MONTANTICAD' ,htmlentities('Сборщиков Линии Жизни', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'ORDINI CATEGORIA/ASSOCIAZIONI' ,htmlentities('Ассоциация', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'PICCOLE STRUTTURE' ,htmlentities('Маленькие Структуры', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'PRODUTTORI LAMELLARE' ,htmlentities('Изготови́тель Лес', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'RIVENDITA LEGNAME / SEGHERIA CHE FA LAVORI' ,htmlentities('Продаве́ц Древеси́на/Покрытия', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'RIVEMATED' ,htmlentities('Продажи Строительные Материалы', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'RIVENDITA SPECIALIZZATA SETTORE CARPENTERIA' ,htmlentities('Специалист По Железу', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'RIVENDITE SISTEMI ANTICADUTA' ,htmlentities('Падение Продажи Системы Защиты', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'RIVENDITA LEGNAME / SEGHERIA (NON FA LAVORI)' ,htmlentities('Продаве́ц Древеси́на/Лесопи́льный Заво́д', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'SCUOLEPROF' ,htmlentities('Профессиональные Школы', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'AMIANTO' ,htmlentities('Удаление Асбест', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'ARCH.' ,htmlentities('Студия Архитектуры', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'GEOM.' ,htmlentities('Студия Инспектор', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'ING.' ,htmlentities('Исследование Техники', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' ,'ru_ru' ,'UNIVERSITA' ,htmlentities('Университетами И Исследовательскими', ENT_NOQUOTES, $default_charset));


SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'UTILIZZATORE' , htmlentities('По́льзователь', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'RIVENDITORE' , htmlentities('Аге́нт по перепрода́же', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'PROGETTISTA' , htmlentities('Планови́к', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'INFLUENZATORE' ,htmlentities( 'Стейкхолдер', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'UTILIZZATORE' , htmlentities('По́льзователь', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'RIVENDITORE' ,htmlentities( 'Аге́нт по перепрода́же', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'PROGETTISTA' ,htmlentities( 'Планови́к', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'INFLUENZATORE' , htmlentities('Стейкхолдер', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Leads' , 'ru_ru' , 'UTILIZZATORE' , htmlentities('По́льзователь', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Leads' , 'ru_ru' , 'RIVENDITORE' , htmlentities('Аге́нт по перепрода́же', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Leads' , 'ru_ru' , 'PROGETTISTA' , htmlentities('Планови́к', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Leads' , 'ru_ru' , 'INFLUENZATORE' ,htmlentities( 'Стейкхолдер', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'Linea' , htmlentities('Линия по продажам', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'Tipo Cliente' , htmlentities('Тип клиента', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'Attivita Principale' ,htmlentities( 'Деятельности заказчика (1)', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'Attivita Secondaria' ,htmlentities( 'Деятельности заказчика (2)', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'Marchio' , htmlentities('Марка', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'Potenziale &euro;/Anno' ,htmlentities( 'Потенциальные €/год', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'Area di intervento' ,htmlentities( 'Область вмешательства', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'Locale' ,htmlentities( 'Местный', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'Nazionale' ,htmlentities( 'Национальный', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Accounts' , 'ru_ru' , 'Internazionale' , htmlentities('Международный', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'Tipo Cliente' , htmlentities('Тип клиента', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'Attivita Principale' , htmlentities('Деятельности заказчика (1)', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'Attivita Secondaria' , htmlentities('Деятельности заказчика (2)', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'Marchio' , htmlentities('Марка', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'Potenziale &euro;/Anno' ,htmlentities( 'Потенциальные €/год', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'Area di intervento' , htmlentities('Область вмешательства', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'Locale' , htmlentities('Местный', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'Nazionale' , htmlentities('Национальный', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Visitreport' , 'ru_ru' , 'Internazionale' , htmlentities('Международный', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Leads' , 'ru_ru' , 'Tipo Cliente' , htmlentities('Тип клиента', ENT_NOQUOTES, $default_charset));
SDK::setLanguageEntry('Leads' , 'ru_ru' , 'Linea' ,htmlentities( 'Линия по продажам', ENT_NOQUOTES, $default_charset));


?>