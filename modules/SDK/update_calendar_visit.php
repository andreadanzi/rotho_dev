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
$module = Vtiger_Module::getInstance('Calendar');

//danzi.tn@20140310 Bottone custom che dal Calendario chiama la funzione javascript per le creazione di report visita
SDK::setMenuButton("contestual", "LBL_CREATEVISTREPORT_BTN", "createVisitReport(this);", 'themes/rothosofted/images/tbarVistitReport.png', 'Calendar', 'DetailView');
//danzi.tn@20140310 CreateVisitReport funzione javascript per le creazione di report visita
Vtiger_Link::addLink($module->id, 'HEADERSCRIPT', 'CreateVisitReport', 'modules/SDK/src/modules/Calendar/CreateVisitReport.js');
SDK::setExtraSrc('Calendar', 'modules/SDK/src/modules/Calendar/CreateVisitReport.js');
//danzi.tn@20140310 CreateVisitReport modulo PHP per le creazione di report visita da evento di calendario
SDK::setExtraSrc('Visitreport', 'modules/SDK/src/modules/Visitreport/CreateVisit.php');

SDK::setLanguageEntry('Calendar','it_it', 'LBL_CREATEVISTREPORT_BTN' , 'Crea Report Visita');
SDK::setLanguageEntry('Calendar','en_us', 'LBL_CREATEVISTREPORT_BTN' , 'Crea Report Visita');
SDK::setLanguageEntry('Calendar','de_de', 'LBL_CREATEVISTREPORT_BTN' , 'Crea Report Visita');

?>