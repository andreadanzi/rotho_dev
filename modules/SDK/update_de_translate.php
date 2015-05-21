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
//danzi.tn@20150429 traduzioni DE
SDK::setLanguageEntry("Accounts","de_de","Customer","Unternehmen");
SDK::setLanguageEntry("Accounts","de_de","LBL_LIST_ACCOUNT_NAME","Firmenname");
SDK::setLanguageEntry("Accounts","de_de","Nome Azienda","Firmenname");
SDK::setLanguageEntry("Accounts","de_de","Modified Time","Änderungsdatum");
SDK::setLanguageEntry("Accounts","de_de","Origine Lead","Herkunft des Kontakts");
SDK::setLanguageEntry("Accounts","de_de","Progetto","Projekt");
SDK::setLanguageEntry("Accounts","de_de","Consenso Newsletter","Einwilligung für den Erhalt von Newslettern");
SDK::setLanguageEntry("Accounts","de_de","Created Time","Erstellt am");
SDK::setLanguageEntry("Accounts","de_de","Lingua Base","Sprache");
SDK::setLanguageEntry("Accounts","de_de","LBL_DESCRIPTION_INFORMATION","Zusatzinformationen");
SDK::setLanguageEntry("Accounts","de_de","Comprato mag dic 2012","Kauf im Mai/Dez 2012");
SDK::setLanguageEntry("Accounts","de_de","Classificazione livello 1","Klassifizierung 1");
SDK::setLanguageEntry("Accounts","de_de","Linea","Verkaufslinie");
SDK::setLanguageEntry("Accounts","de_de","Attivitá principale","Haupttätigkeit");
SDK::setLanguageEntry("Accounts","de_de","Frequenza visite","Häufigkeit der Besuche");
SDK::setLanguageEntry("Accounts","de_de","Area visite","Zone");
SDK::setLanguageEntry("Accounts","de_de","Area di intervento","Tätigkeitsgebiet des Unternehmens");
SDK::setLanguageEntry("Accounts","de_de","Facoltá","Fakultät");
SDK::setLanguageEntry("Accounts","de_de","Attivitá secondaria","Nebentätigkeit");
SDK::setLanguageEntry("Accounts","de_de","Frequenza proposta","Empfohlene Häufigkeit der Besuche");
SDK::setLanguageEntry("Accounts","de_de","Zona visite","Wochentag");
SDK::setLanguageEntry("Accounts","de_de","Potenziale &euro,/Anno","Potenzieller Umsatz/Jahr");
SDK::setLanguageEntry("Accounts","de_de","With a Laboratory","Mit Labor");
SDK::setLanguageEntry("Accounts","de_de","Classificazione livello 2","Klassifizierung 2");
SDK::setLanguageEntry("Accounts","de_de","Centro di taglio","Abbundzentrum/Lohnabbund");
SDK::setLanguageEntry("Accounts","de_de","Lavora con enti pubblici","Zusammenarbeit mit Öffentlichen Ämtern");
SDK::setLanguageEntry("Accounts","de_de","Numero Soci","Anzahl der Gesellschafter");
SDK::setLanguageEntry("Accounts","de_de","NR Dipendenti","Anzahl der Mitarbeiter");
SDK::setLanguageEntry("Accounts","de_de","Assistenza","Beratungsservice");
SDK::setLanguageEntry("Accounts","de_de","Dati Semiramis","Daten aus Semiramis");
SDK::setLanguageEntry("Accounts","de_de","agent name","Name des Vertreters");
SDK::setLanguageEntry("Accounts","de_de","agent number","Nummer des Vertreters");
SDK::setLanguageEntry("Accounts","de_de","blocco avvocato","Gesperrt durch Anwalt");
SDK::setLanguageEntry("Accounts","de_de","condizioni prezzo","Preisbedingungen");
SDK::setLanguageEntry("Accounts","de_de","condizioni pagamento","Zahlungsziele");
SDK::setLanguageEntry("Accounts","de_de","Nr. Area Manager","Nr des Area Managers)");
SDK::setLanguageEntry("Accounts","de_de","Nome Area MAnager","Name des Area Managers)");
SDK::setLanguageEntry("Accounts","de_de","Codice listino","Art der Preisliste");
SDK::setLanguageEntry("Accounts","de_de","Divisa listino","Währung");
SDK::setLanguageEntry("Accounts","de_de","Note Pagamenti","Notiz zur Zahlung");
SDK::setLanguageEntry("Accounts","de_de","Secondo Agente","Zweiter Vertreter");
SDK::setLanguageEntry("Accounts","de_de","Codice Ref.Vend.Int.","Kodex der Verkaufsreferentin");
SDK::setLanguageEntry("Accounts","de_de","Codice CRM","Kodex CRM");
SDK::setLanguageEntry("Accounts","de_de","Descrizione listino","Beschreibung Preisliste");
SDK::setLanguageEntry("Accounts","de_de","Dati genrali concorrente","Daten des Mitbewerbers");
SDK::setLanguageEntry("Accounts","de_de","Comunicazione","Kommunikation");
SDK::setLanguageEntry("Accounts","de_de","Servizi offerti","Angebotene Dienste");
SDK::setLanguageEntry("Accounts","de_de","Attivitá","Aktivitäten");
SDK::setLanguageEntry("Accounts","de_de","Aggiungi compito","Aufgabe hinzufügen");
SDK::setLanguageEntry("Accounts","de_de","Aggiungi evento","Event  hinzufügen");
SDK::setLanguageEntry("Accounts","de_de","Storico Attivitá","Vergangene Aktivitäten");
SDK::setLanguageEntry("Accounts","de_de","Ordini Vendita","Bestellungen");
SDK::setLanguageEntry("Accounts","de_de","Aggiungi ordine","Bestellung hinzufügen");
SDK::setLanguageEntry("Accounts","de_de","Consulenze","Technische Beratung");
SDK::setLanguageEntry("Accounts","de_de","Aggiungi Consulenza","Technische Beratung hinzufügen");
SDK::setLanguageEntry("Accounts","de_de","Opportunitá","Gelegenheit");
SDK::setLanguageEntry("Accounts","de_de","Aggiungi opportunitá","Gelegenheit hinzufügen");
SDK::setLanguageEntry("Accounts","de_de","Revisioni","Revisionen");
SDK::setLanguageEntry("Consulenza","de_de","Consulenza","Technische Beratung");
SDK::setLanguageEntry("Consulenza","de_de","Consulenza Name","Art der Beratung");
SDK::setLanguageEntry("Consulenza","de_de","Product category","Produktlinie");
SDK::setLanguageEntry("Consulenza","de_de","Product category description","Beschreibung der Produktlinie");
SDK::setLanguageEntry("Consulenza","de_de","consulenzastatus","Stand");
SDK::setLanguageEntry("Consulenza","de_de","numconsulenza","Beratungsnummer");
SDK::setLanguageEntry("Consulenza","de_de","Parent","Unternehmen");
SDK::setLanguageEntry("Consulenza","de_de","Contact","Kontakt");
SDK::setLanguageEntry("Consulenza","de_de","consulenzacountry","Land");
SDK::setLanguageEntry("Consulenza","de_de","Flag Agente","Beratung durch Handelsvertreter");
SDK::setLanguageEntry("Consulenza","de_de","Agente Riferimento","zuständiger Außendienstmitarbeiter");
SDK::setLanguageEntry("Consulenza","de_de","In carico a Ing","zuständiger Ingenieur");
SDK::setLanguageEntry("Consulenza","de_de","Warranty Serial No","Garantie Nummer");
SDK::setLanguageEntry("Consulenza","de_de","Report Visite","Besucherbericht");
SDK::setLanguageEntry("Consulenza","de_de","Nome Visita","Art des Besuchs");
SDK::setLanguageEntry("Consulenza","de_de","Azienda","Unternehmen");
SDK::setLanguageEntry("Consulenza","de_de","Attivitá principale","Haupttätigkeit");
SDK::setLanguageEntry("Consulenza","de_de","Attivitá secondaria","Nebentätigkeit");
SDK::setLanguageEntry("Consulenza","de_de","Area di intervento","Tätigkeitsgebiet des Unternehmens");
SDK::setLanguageEntry("Consulenza","de_de","Nr Visita","Nummer des Besucherberichts");
SDK::setLanguageEntry("Consulenza","de_de","Data visita","Besuchsdatum");
SDK::setLanguageEntry("Consulenza","de_de","Linea di Vendita","Verkaufslinie");
SDK::setLanguageEntry("Consulenza","de_de","Potenziale €/Anno","Potenzieller Umsatz/Jahr");
SDK::setLanguageEntry("Consulenza","de_de","Scopo della visita","Ziel des Besuchs");
SDK::setLanguageEntry("Consulenza","de_de","Note visita","Notizen");
SDK::setLanguageEntry("Consulenza","de_de","Risultato della Visita","Besuchsergebnis");
SDK::setLanguageEntry("Consulenza","de_de","Ordine realizzato","Auftrag abgeschlossen");
SDK::setLanguageEntry("Consulenza","de_de","Materiale informativo lasciato","Abgegebenes Informationsmaterial");
SDK::setLanguageEntry("Consulenza","de_de","Categoria Prodotti presentati","Präsentierte Produktlinien");
SDK::setLanguageEntry("Consulenza","de_de","Prodotto specifico","Spezifische Produkte");

?>