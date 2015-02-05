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
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'AMMINISTRCOND' ,'Amministratori Di Condominio');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'CARPENTERIA' ,'Carpenteria');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'CARPMET' ,'Carpenteria Metallica');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'CASE IN LEGNO' ,'Case In Legno');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'CONCIATETTO' ,'Conciatetto');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'EDIFICI IN LEGNO' ,'Edifici In Legno');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'ENTEPUBB' ,'Ente Pubblico');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'FACCIATE E RIVESTIMENTI' ,'Facciate E Rivestimenti');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'FALEGNAME/SERRAMENTISTA' ,'Falegname/Serramentista');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'FERRAMENTA E ATTREZZATURE' ,'Ferramenta E Attrezzature');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'GDO' ,'GDO');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'GENERAL CONTRACTOR / IMMOBILIARE' ,'General Contractor / Immobiliare');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'GRANDI STRUTTURE' ,'Grandi Strutture');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'GROSSISTA' ,'Grossista');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'IMPERM' ,'Impermeabilizzazione Edile E Isolazione Per Edilizia');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'IMPIANTISTI' ,'Impiantisti');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'IMPEDILE' ,'Impresa Edile');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'INDEXTRALEGNO' ,'Industria Extra Legno');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'LATTONERIA' ,'Lattoneria');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'MONTANTICAD' ,'Montatore Linee Vita');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'ORDINI CATEGORIA/ASSOCIAZIONI' ,'Ordini Categoria/Associazioni');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'PICCOLE STRUTTURE' ,'Piccole Strutture');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'PRODUTTORI LAMELLARE' ,'Produttori Lamellare');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'RIVENDITA LEGNAME / SEGHERIA CHE FA LAVORI' ,'Rivendita Legname / Segheria Che Fa Lavori');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'RIVEMATED' ,'Rivendita Materiali Edili');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'RIVENDITA SPECIALIZZATA SETTORE CARPENTERIA' ,'Rivendita Specializzata Settore Carpenteria');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'RIVENDITE SISTEMI ANTICADUTA' ,'Rivendite Sistemi Anticaduta');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'RIVENDITA LEGNAME / SEGHERIA (NON FA LAVORI)' ,'Rivenditore Di Legname / Segheria (NON Fa Lavori)');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'SCUOLEPROF' ,'Scuole Professionali');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'AMIANTO' ,'Smaltimento Amianto');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'ARCH.' ,'Studio Architettura');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'GEOM.' ,'Studio Geometra');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'ING.' ,'Studio Ingegneria');
SDK::setLanguageEntry('Visitreport' ,'it_it' ,'UNIVERSITA' ,'Universita E Ricerca');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'AMMINISTRCOND' ,'Asbestentsorgung');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'CARPENTERIA' ,'Zimmerei');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'CARPMET' ,'Metallbau');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'CASE IN LEGNO' ,'Holzhaeuser');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'CONCIATETTO' ,'Dachdecker');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'EDIFICI IN LEGNO' ,'Holzgebaeude');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'ENTEPUBB' ,'	Oeffentliche Einrichtung');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'FACCIATE E RIVESTIMENTI' ,'Terrassen Und Fassaden');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'FALEGNAME/SERRAMENTISTA' ,'Tischler/Bautischler');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'FERRAMENTA E ATTREZZATURE' ,'Eisenwaren/Werkzeug');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'GDO' ,'Organisierter Grossverteiler');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'GENERAL CONTRACTOR / IMMOBILIARE' ,'Immobilienagentur');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'GRANDI STRUTTURE' ,'Grosse Strukturen');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'GROSSISTA' ,'Grosshaendler');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'IMPERM' ,'Abdichtung');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'IMPIANTISTI' ,'Installateur');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'IMPEDILE' ,'Bauunternehmen');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'INDEXTRALEGNO' ,'Industrie Ausser Holz');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'LATTONERIA' ,'Spengler');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'MONTANTICAD' ,'Absturzsicherung Monteur');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'ORDINI CATEGORIA/ASSOCIAZIONI' ,'Verbaende');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'PICCOLE STRUTTURE' ,'Kleine Strukturen');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'PRODUTTORI LAMELLARE' ,'Leimholz Industrie');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'RIVENDITA LEGNAME / SEGHERIA CHE FA LAVORI' ,'Holzhaendler Mit Zimmerei');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'RIVEMATED' ,'Baustoffhaendler');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'RIVENDITA SPECIALIZZATA SETTORE CARPENTERIA' ,'Spezialisiert Auf Zimmerei');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'RIVENDITE SISTEMI ANTICADUTA' ,'Absturzsicherung');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'RIVENDITA LEGNAME / SEGHERIA (NON FA LAVORI)' ,'Holzhaendler/Saegewerkholzhaendler/Saegewerk');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'SCUOLEPROF' ,'Berufsschulen');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'AMIANTO' ,'Asbestentsorgung');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'ARCH.' ,'Architekturbüro');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'GEOM.' ,'Geometer');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'ING.' ,'Bauingenieurbüro');
SDK::setLanguageEntry('Visitreport' ,'de_de' ,'UNIVERSITA' ,'Universitaet Und Forschungsinstitut');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'AMMINISTRCOND' ,'Administrateurs De Condominium');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'CARPENTERIA' ,'Charpenterie');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'CARPMET' ,'Menuiserie Metallique');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'CASE IN LEGNO' ,'Maisons');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'CONCIATETTO' ,'Couvreur');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'EDIFICI IN LEGNO' ,'Batiment Bois');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'ENTEPUBB' ,'Institution Public');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'FACCIATE E RIVESTIMENTI' ,'Facades Et Revetements');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'FALEGNAME/SERRAMENTISTA' ,'Menuiserie');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'FERRAMENTA E ATTREZZATURE' ,'Quincallerie Et Outillage');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'GDO' ,'Gsb');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'GENERAL CONTRACTOR / IMMOBILIARE' ,'Immobilier');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'GRANDI STRUTTURE' ,'Grand Structures');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'GROSSISTA' ,'Grossiste');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'IMPERM' ,'Impermeabilisation');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'IMPIANTISTI' ,'Installateurs');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'IMPEDILE' ,'Entreprise De Construction');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'INDEXTRALEGNO' ,'Industrie Extra-Bois');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'LATTONERIA' ,'Ferblanterie');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'MONTANTICAD' ,'Monteures Ligne De Vie');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'ORDINI CATEGORIA/ASSOCIAZIONI' ,'Association');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'PICCOLE STRUTTURE' ,'Structures L&egrave;g&eacute;res');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'PRODUTTORI LAMELLARE' ,'Fabricant De Lamelle Colle');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'RIVENDITA LEGNAME / SEGHERIA CHE FA LAVORI' ,'Distributeur De Bois/Scierie (AUSSI Charpente)');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'RIVEMATED' ,'Revente Mat&egrave;riaux De Construction');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'RIVENDITA SPECIALIZZATA SETTORE CARPENTERIA' ,'Distributeur Specialise En Charpenterie');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'RIVENDITE SISTEMI ANTICADUTA' ,'Distributeur Systemes Antichute');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'RIVENDITA LEGNAME / SEGHERIA (NON FA LAVORI)' ,'Distributeur De Bois/Scierie (NO Charpente)');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'SCUOLEPROF' ,'&Egrave;coles Professionnelles');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'AMIANTO' ,'Elimination De L\'amiante');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'ARCH.' ,'Architecture');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'GEOM.' ,'G&egrave;om&eacute;tre');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'ING.' ,'Ing&egrave;nierie');
SDK::setLanguageEntry('Visitreport' ,'fr_fr' ,'UNIVERSITA' ,'Universit&egrave; Et Recherche');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'AMMINISTRCOND' ,'Condominium Administrator');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'CARPENTERIA' ,'Carpentry');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'CARPMET' ,'Metal Carpentry');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'CASE IN LEGNO' ,'Wooden Houses');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'CONCIATETTO' ,'Roofer');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'EDIFICI IN LEGNO' ,'Wooden Buildings');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'ENTEPUBB' ,'Public Institution');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'FACCIATE E RIVESTIMENTI' ,'Terraces Facades');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'FALEGNAME/SERRAMENTISTA' ,'Joiner');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'FERRAMENTA E ATTREZZATURE' ,'Hardware And Equipment');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'GDO' ,'Diy');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'GENERAL CONTRACTOR / IMMOBILIARE' ,'General Contractor/Real Estate');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'GRANDI STRUTTURE' ,'Big Structures');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'GROSSISTA' ,'Wholesaler');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'IMPERM' ,'Waterproofing');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'IMPIANTISTI' ,'Installer');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'IMPEDILE' ,'Building Firm');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'INDEXTRALEGNO' ,'Industry Extra Wood');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'LATTONERIA' ,'Tinsmith Industry');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'MONTANTICAD' ,'Fall Protection Assembler');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'ORDINI CATEGORIA/ASSOCIAZIONI' ,'Associations');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'PICCOLE STRUTTURE' ,'Small Structures');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'PRODUTTORI LAMELLARE' ,'Clt Producer');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'RIVENDITA LEGNAME / SEGHERIA CHE FA LAVORI' ,'Timber Dealer With Carpentry');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'RIVEMATED' ,'Building Material Retailer');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'RIVENDITA SPECIALIZZATA SETTORE CARPENTERIA' ,'Specialized Carpentry Retailer');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'RIVENDITE SISTEMI ANTICADUTA' ,'Fall Protection Retailer');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'RIVENDITA LEGNAME / SEGHERIA (NON FA LAVORI)' ,'Timber Dealer/Sawmill');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'SCUOLEPROF' ,'Professional Schools');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'AMIANTO' ,'Disposalof Asbetos');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'ARCH.' ,'Architecture Bureau');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'GEOM.' ,'Building Surveyor');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'ING.' ,'Engineering Bureau');
SDK::setLanguageEntry('Visitreport' ,'en_us' ,'UNIVERSITA' ,'Universities And Research');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'AMMINISTRCOND' ,'Administradores De Condominio');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'CARPENTERIA' ,'Carpinteria');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'CARPMET' ,'Carpenteria Metalica');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'CASE IN LEGNO' ,'Casas De Madera');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'CONCIATETTO' ,'Techador');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'EDIFICI IN LEGNO' ,'Edificios De Madera');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'ENTEPUBB' ,'Ente Publico');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'FACCIATE E RIVESTIMENTI' ,'Terrazas Y Fachadas');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'FALEGNAME/SERRAMENTISTA' ,'Ebanista');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'FERRAMENTA E ATTREZZATURE' ,'Ferreteria Y Equipo');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'GDO' ,'GDO');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'GENERAL CONTRACTOR / IMMOBILIARE' ,'General Contractor/Inmobiliaria');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'GRANDI STRUTTURE' ,'Estructuras Grandes');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'GROSSISTA' ,'Mayorista');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'IMPERM' ,'Impermeabilizacion Y Aislamiento');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'IMPIANTISTI' ,'Instaladores');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'IMPEDILE' ,'Empresa De Construccion');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'INDEXTRALEGNO' ,'Industria Extra Madera');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'LATTONERIA' ,'Hojalateria');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'MONTANTICAD' ,'Montadores Lineas De Vida');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'ORDINI CATEGORIA/ASSOCIAZIONI' ,'Asociacion');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'PICCOLE STRUTTURE' ,'Estructuras Pequenas');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'PRODUTTORI LAMELLARE' ,'Productor De Madera');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'RIVENDITA LEGNAME / SEGHERIA CHE FA LAVORI' ,'Distribuidor De Madera/Carpinteria');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'RIVEMATED' ,'Reventa Materiales De Construccion');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'RIVENDITA SPECIALIZZATA SETTORE CARPENTERIA' ,'Distribuidor Especializado En Carpinteria');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'RIVENDITE SISTEMI ANTICADUTA' ,'Reventa Sistemas Anticaidas');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'RIVENDITA LEGNAME / SEGHERIA (NON FA LAVORI)' ,'Distribuidor De Madera/Serreria');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'SCUOLEPROF' ,'Escuelas Profesionales');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'AMIANTO' ,'Eliminacion Amianto');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'ARCH.' ,'Estudio Arquitectura');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'GEOM.' ,'Estudio Aparejador');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'ING.' ,'Estudio Ingenieria');
SDK::setLanguageEntry('Visitreport' ,'es_es' ,'UNIVERSITA' ,'Universidad Y  Investigacion');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'AMMINISTRCOND' ,'Administradores De Condominio');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'CARPENTERIA' ,'Carpintaria');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'CARPMET' ,'Carpintaria Metmet&aacute;lica');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'CASE IN LEGNO' ,'Casas De Madeira');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'CONCIATETTO' ,'Telhador');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'EDIFICI IN LEGNO' ,'Imoveis Em Madeira');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'ENTEPUBB' ,'Entidade Publica');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'FACCIATE E RIVESTIMENTI' ,'Faces E Tarracos');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'FALEGNAME/SERRAMENTISTA' ,'Ebanista');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'FERRAMENTA E ATTREZZATURE' ,'Ferragens E Equipamentos');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'GDO' ,'GDO');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'GENERAL CONTRACTOR / IMMOBILIARE' ,'Imobili&aacute;ria/Empreitero General');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'GRANDI STRUTTURE' ,'Grande Estruturas');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'GROSSISTA' ,'Atacado');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'IMPERM' ,'Impermeabilizacao E Isolamento');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'IMPIANTISTI' ,'Instalador');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'IMPEDILE' ,'Empresa De Construcao');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'INDEXTRALEGNO' ,'Industria Extra Madeira');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'LATTONERIA' ,'Latoaria');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'MONTANTICAD' ,'Instalador Linha De Vida');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'ORDINI CATEGORIA/ASSOCIAZIONI' ,'Associacao');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'PICCOLE STRUTTURE' ,'Pequenas Estruturas');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'PRODUTTORI LAMELLARE' ,'Produtores De Madeira');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'RIVENDITA LEGNAME / SEGHERIA CHE FA LAVORI' ,'Revenda De Madeira/Carpintaria');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'RIVEMATED' ,'Revenda De Materiais De Construcao');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'RIVENDITA SPECIALIZZATA SETTORE CARPENTERIA' ,'Distribuidor Especializado Em Carpintaria');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'RIVENDITE SISTEMI ANTICADUTA' ,'Revenda Sistemas De Proteccao');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'RIVENDITA LEGNAME / SEGHERIA (NON FA LAVORI)' ,'Revenda De Madeira/Serracao');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'SCUOLEPROF' ,'Escuelas Profesionales');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'AMIANTO' ,'Eliminacao Do Amianto');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'ARCH.' ,'Estudo De Arquitetura');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'GEOM.' ,'Estudo De Aparelhador');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'ING.' ,'Estudo De Engenharia');
SDK::setLanguageEntry('Visitreport' ,'pt_pt' ,'UNIVERSITA' ,'Universidad Y Investigacion');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'AMMINISTRCOND' ,'Кондоминиум Администратора');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'CARPENTERIA' ,'Покрытия');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'CARPMET' ,'Металлоконструкции');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'CASE IN LEGNO' ,'Деревянные Дома');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'CONCIATETTO' ,'Кро́вельщик');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'EDIFICI IN LEGNO' ,'Imoveis Em Madeira');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'ENTEPUBB' ,'Государственное Учреждение');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'FACCIATE E RIVESTIMENTI' ,'Террасы Фасады');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'FALEGNAME/SERRAMENTISTA' ,'Столя́р');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'FERRAMENTA E ATTREZZATURE' ,'Скобяны́е Изде́лия Е Инструменты');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'GDO' ,'Diy');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'GENERAL CONTRACTOR / IMMOBILIARE' ,'Генеральный Подрядчик');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'GRANDI STRUTTURE' ,'Большой Структуры');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'GROSSISTA' ,'Оптовиков');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'IMPERM' ,'Гидроизоляционные');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'IMPIANTISTI' ,'Установки');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'IMPEDILE' ,'Строительная Фирма');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'INDEXTRALEGNO' ,'Промышленность Не Лес');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'LATTONERIA' ,'Жестя́ник');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'MONTANTICAD' ,'Сборщиков Линии Жизни');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'ORDINI CATEGORIA/ASSOCIAZIONI' ,'Ассоциация');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'PICCOLE STRUTTURE' ,'Маленькие Структуры');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'PRODUTTORI LAMELLARE' ,'Изготови́тель Лес');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'RIVENDITA LEGNAME / SEGHERIA CHE FA LAVORI' ,'Продаве́ц Древеси́на/Покрытия');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'RIVEMATED' ,'Продажи Строительные Материалы');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'RIVENDITA SPECIALIZZATA SETTORE CARPENTERIA' ,'Специалист По Железу');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'RIVENDITE SISTEMI ANTICADUTA' ,'Падение Продажи Системы Защиты');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'RIVENDITA LEGNAME / SEGHERIA (NON FA LAVORI)' ,'Продаве́ц Древеси́на/Лесопи́льный Заво́д');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'SCUOLEPROF' ,'Профессиональные Школы');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'AMIANTO' ,'Удаление Асбест');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'ARCH.' ,'Студия Архитектуры');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'GEOM.' ,'Студия Инспектор');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'ING.' ,'Исследование Техники');
SDK::setLanguageEntry('Visitreport' ,'ru_ru' ,'UNIVERSITA' ,'Университетами И Исследовательскими');


?>