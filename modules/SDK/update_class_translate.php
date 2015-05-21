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
//danzi.tn@20150521 traduzioni DE

// "EDIFICI IN LEGNO"
SDK::setLanguageEntry("Accounts","de_de","EDIFICI IN LEGNO","Gebaude brettsperrholzbau");
SDK::setLanguageEntry("Accounts","fr_fr","EDIFICI IN LEGNO","Batiments bois");

// GRANDI STRUTTURE
SDK::setLanguageEntry("Accounts","de_de","GRANDI STRUTTURE","Hallenbau ingenieurholzbau");
SDK::setLanguageEntry("Accounts","fr_fr","GRANDI STRUTTURE","Grandes structures");

// PRODUTTORI LAMELLARE
SDK::setLanguageEntry("Accounts","de_de","PRODUTTORI LAMELLARE","Holzleimbau");
SDK::setLanguageEntry("Accounts","fr_fr","PRODUTTORI LAMELLARE","Fabricant de lamelle colle");
SDK::setLanguageEntry("Accounts","en_us","PRODUTTORI LAMELLARE","Glulam producer");
SDK::setLanguageEntry("Accounts","es_es","PRODUTTORI LAMELLARE","Productor de madera laminada");
SDK::setLanguageEntry("Accounts","pt_pt","PRODUTTORI LAMELLARE","Produtores de mlc");

// PRODUTTORI XLAM
SDK::setLanguageEntry("Accounts","it_it","PRODUTTORI XLAM","Produttore XLAM");
SDK::setLanguageEntry("Accounts","de_de","PRODUTTORI XLAM","CLT Hersteller");
SDK::setLanguageEntry("Accounts","fr_fr","PRODUTTORI XLAM","Fabricant XLAM");
SDK::setLanguageEntry("Accounts","en_us","PRODUTTORI XLAM","CLT producer");
SDK::setLanguageEntry("Accounts","es_es","PRODUTTORI XLAM","Productor de madera contralaminada");
SDK::setLanguageEntry("Accounts","pt_pt","PRODUTTORI XLAM","Produtores de CLT");
SDK::setLanguageEntry("Accounts","ru_ru","PRODUTTORI XLAM","CLT producer");

// PAVIMENTISTA
SDK::setLanguageEntry("PAVIMENTISTA","fr_fr","PAVIMENTISTA","Poseur de parquet");
// FALEGNAME/SERRAMENTISTA
SDK::setLanguageEntry("Accounts","de_de","FALEGNAME/SERRAMENTISTA","Tischler/fensterbauertischler/fensterbauer");
SDK::setLanguageEntry("Accounts","fr_fr","FALEGNAME/SERRAMENTISTA","Menuisier/artisan bois");
SDK::setLanguageEntry("Accounts","en_us","FALEGNAME/SERRAMENTISTA","Joiner/windows fitterjoiner/windows fitter");
SDK::setLanguageEntry("Accounts","es_es","FALEGNAME/SERRAMENTISTA","Ebanisteria/cerramientos");
SDK::setLanguageEntry("Accounts","pt_pt","FALEGNAME/SERRAMENTISTA","Artesao de carpintaria/carpinteiro de limpos");

// PRODUTTORE SERRAMENTI
SDK::setLanguageEntry("Accounts","de_de","PRODUTTORE SERRAMENTI","Produzent fenstersysteme");
SDK::setLanguageEntry("Accounts","fr_fr","PRODUTTORE SERRAMENTI","Fabricant de menuiseries");
SDK::setLanguageEntry("Accounts","en_us","PRODUTTORE SERRAMENTI","Windows-door producer");
SDK::setLanguageEntry("Accounts","es_es","PRODUTTORE SERRAMENTI","Fabricante de cerramientos");
SDK::setLanguageEntry("Accounts","pt_pt","PRODUTTORE SERRAMENTI","Produtor de janelas");

// CASE IN LEGNO
SDK::setLanguageEntry("Accounts","it_it","CASE IN LEGNO","Holzhauser rahmenbau");
SDK::setLanguageEntry("Accounts","de_de","CASE IN LEGNO","Maisons en bois");

//IMPERM
SDK::setLanguageEntry("Accounts","de_de","IMPERM","Bauabdichtung");
SDK::setLanguageEntry("Accounts","fr_fr","IMPERM","Impermeabilisation et isolation batiment");

//IMPIANTISTI
SDK::setLanguageEntry("Accounts","de_de","IMPIANTISTI","Solar - photovoltaikmonteure");
SDK::setLanguageEntry("Accounts","fr_fr","MONTANTICAD","Installateur ligne de vie");


SDK::setLanguageEntry("Accounts","fr_fr","RIVENDITA LEGNAME / SEGHERIA (NON FA LAVORI)","Revendeur de bois/scierie (non charpente)");
SDK::setLanguageEntry("Accounts","fr_fr","RIVENDITA LEGNAME / SEGHERIA CHE FA LAVORI","Revendeur de bois/scierie (aussi charpente)");

SDK::setLanguageEntry("Accounts","de_de","RIVENDITA SPECIALIZZATA SETTORE CARPENTERIA","Einzelhandel spezialisiert auf zimmerei");
SDK::setLanguageEntry("Accounts","fr_fr","RIVENDITA SPECIALIZZATA SETTORE CARPENTERIA","Revente specialisee en charpenterie");
SDK::setLanguageEntry("Accounts","fr_fr","RIVEMATED","Revente materiaux de construction");
SDK::setLanguageEntry("Accounts","de_de","GDO","Organisierter grosshaendler");
SDK::setLanguageEntry("Accounts","fr_fr","RIVENDITE SISTEMI ANTICADUTA","Revente systemes antichute");
SDK::setLanguageEntry("Accounts","de_de","DISTRIBUTORE SERRAMENTI","Haendler fenstersysteme");
SDK::setLanguageEntry("Accounts","fr_fr","DISTRIBUTORE SERRAMENTI","Revente menuiseries");
SDK::setLanguageEntry("Accounts","en_us","DISTRIBUTORE SERRAMENTI","Windows-door distributor");
SDK::setLanguageEntry("Accounts","es_es","DISTRIBUTORE SERRAMENTI","Distribuidor de cerramientos");
SDK::setLanguageEntry("Accounts","pt_pt","DISTRIBUTORE SERRAMENTI","Distribuidor de perfil - molduras de janelas");

SDK::setLanguageEntry("Accounts","de_de","ENTEPUBB","Oeffentliche aemter");
SDK::setLanguageEntry("Accounts","fr_fr","ORDINI CATEGORIA/ASSOCIAZIONI","Associations");
SDK::setLanguageEntry("Accounts","fr_fr","AMMINISTRCOND","Administrateurs de immeuble");


// VISIT REPORT

// "EDIFICI IN LEGNO"
SDK::setLanguageEntry("Visitreport","de_de","EDIFICI IN LEGNO","Gebaude brettsperrholzbau");
SDK::setLanguageEntry("Visitreport","fr_fr","EDIFICI IN LEGNO","Batiments bois");

// GRANDI STRUTTURE
SDK::setLanguageEntry("Visitreport","de_de","GRANDI STRUTTURE","Hallenbau ingenieurholzbau");
SDK::setLanguageEntry("Visitreport","fr_fr","GRANDI STRUTTURE","Grandes structures");

// PRODUTTORI LAMELLARE
SDK::setLanguageEntry("Visitreport","de_de","PRODUTTORI LAMELLARE","Holzleimbau");
SDK::setLanguageEntry("Visitreport","fr_fr","PRODUTTORI LAMELLARE","Fabricant de lamelle colle");
SDK::setLanguageEntry("Visitreport","en_us","PRODUTTORI LAMELLARE","Glulam producer");
SDK::setLanguageEntry("Visitreport","es_es","PRODUTTORI LAMELLARE","Productor de madera laminada");
SDK::setLanguageEntry("Visitreport","pt_pt","PRODUTTORI LAMELLARE","Produtores de mlc");

// PRODUTTORI XLAM
SDK::setLanguageEntry("Visitreport","it_it","PRODUTTORI XLAM","Produttore XLAM");
SDK::setLanguageEntry("Visitreport","de_de","PRODUTTORI XLAM","CLT Hersteller");
SDK::setLanguageEntry("Visitreport","fr_fr","PRODUTTORI XLAM","Fabricant XLAM");
SDK::setLanguageEntry("Visitreport","en_us","PRODUTTORI XLAM","CLT producer");
SDK::setLanguageEntry("Visitreport","es_es","PRODUTTORI XLAM","Productor de madera contralaminada");
SDK::setLanguageEntry("Visitreport","pt_pt","PRODUTTORI XLAM","Produtores de CLT");
SDK::setLanguageEntry("Visitreport","ru_ru","PRODUTTORI XLAM","CLT producer");

// PAVIMENTISTA
SDK::setLanguageEntry("PAVIMENTISTA","fr_fr","PAVIMENTISTA","Poseur de parquet");
// FALEGNAME/SERRAMENTISTA
SDK::setLanguageEntry("Visitreport","de_de","FALEGNAME/SERRAMENTISTA","Tischler/fensterbauertischler/fensterbauer");
SDK::setLanguageEntry("Visitreport","fr_fr","FALEGNAME/SERRAMENTISTA","Menuisier/artisan bois");
SDK::setLanguageEntry("Visitreport","en_us","FALEGNAME/SERRAMENTISTA","Joiner/windows fitterjoiner/windows fitter");
SDK::setLanguageEntry("Visitreport","es_es","FALEGNAME/SERRAMENTISTA","Ebanisteria/cerramientos");
SDK::setLanguageEntry("Visitreport","pt_pt","FALEGNAME/SERRAMENTISTA","Artesao de carpintaria/carpinteiro de limpos");

// PRODUTTORE SERRAMENTI
SDK::setLanguageEntry("Visitreport","de_de","PRODUTTORE SERRAMENTI","Produzent fenstersysteme");
SDK::setLanguageEntry("Visitreport","fr_fr","PRODUTTORE SERRAMENTI","Fabricant de menuiseries");
SDK::setLanguageEntry("Visitreport","en_us","PRODUTTORE SERRAMENTI","Windows-door producer");
SDK::setLanguageEntry("Visitreport","es_es","PRODUTTORE SERRAMENTI","Fabricante de cerramientos");
SDK::setLanguageEntry("Visitreport","pt_pt","PRODUTTORE SERRAMENTI","Produtor de janelas");

// CASE IN LEGNO
SDK::setLanguageEntry("Visitreport","it_it","CASE IN LEGNO","Holzhauser rahmenbau");
SDK::setLanguageEntry("Visitreport","de_de","CASE IN LEGNO","Maisons en bois");

//IMPERM
SDK::setLanguageEntry("Visitreport","de_de","IMPERM","Bauabdichtung");
SDK::setLanguageEntry("Visitreport","fr_fr","IMPERM","Impermeabilisation et isolation batiment");

//IMPIANTISTI
SDK::setLanguageEntry("Visitreport","de_de","IMPIANTISTI","Solar - photovoltaikmonteure");
SDK::setLanguageEntry("Visitreport","fr_fr","MONTANTICAD","Installateur ligne de vie");


SDK::setLanguageEntry("Visitreport","fr_fr","RIVENDITA LEGNAME / SEGHERIA (NON FA LAVORI)","Revendeur de bois/scierie (non charpente)");
SDK::setLanguageEntry("Visitreport","fr_fr","RIVENDITA LEGNAME / SEGHERIA CHE FA LAVORI","Revendeur de bois/scierie (aussi charpente)");

SDK::setLanguageEntry("Visitreport","de_de","RIVENDITA SPECIALIZZATA SETTORE CARPENTERIA","Einzelhandel spezialisiert auf zimmerei");
SDK::setLanguageEntry("Visitreport","fr_fr","RIVENDITA SPECIALIZZATA SETTORE CARPENTERIA","Revente specialisee en charpenterie");
SDK::setLanguageEntry("Visitreport","fr_fr","RIVEMATED","Revente materiaux de construction");
SDK::setLanguageEntry("Visitreport","de_de","GDO","Organisierter grosshaendler");
SDK::setLanguageEntry("Visitreport","fr_fr","RIVENDITE SISTEMI ANTICADUTA","Revente systemes antichute");
SDK::setLanguageEntry("Visitreport","de_de","DISTRIBUTORE SERRAMENTI","Haendler fenstersysteme");
SDK::setLanguageEntry("Visitreport","fr_fr","DISTRIBUTORE SERRAMENTI","Revente menuiseries");
SDK::setLanguageEntry("Visitreport","en_us","DISTRIBUTORE SERRAMENTI","Windows-door distributor");
SDK::setLanguageEntry("Visitreport","es_es","DISTRIBUTORE SERRAMENTI","Distribuidor de cerramientos");
SDK::setLanguageEntry("Visitreport","pt_pt","DISTRIBUTORE SERRAMENTI","Distribuidor de perfil - molduras de janelas");

SDK::setLanguageEntry("Visitreport","de_de","ENTEPUBB","Oeffentliche aemter");
SDK::setLanguageEntry("Visitreport","fr_fr","ORDINI CATEGORIA/ASSOCIAZIONI","Associations");
SDK::setLanguageEntry("Visitreport","fr_fr","AMMINISTRCOND","Administrateurs de immeuble");

// APP_STRINGS
// "EDIFICI IN LEGNO"
SDK::setLanguageEntry("APP_STRINGS","de_de","EDIFICI IN LEGNO","Gebaude brettsperrholzbau");
SDK::setLanguageEntry("APP_STRINGS","fr_fr","EDIFICI IN LEGNO","Batiments bois");

// GRANDI STRUTTURE
SDK::setLanguageEntry("APP_STRINGS","de_de","GRANDI STRUTTURE","Hallenbau ingenieurholzbau");
SDK::setLanguageEntry("APP_STRINGS","fr_fr","GRANDI STRUTTURE","Grandes structures");

// PRODUTTORI LAMELLARE
SDK::setLanguageEntry("APP_STRINGS","de_de","PRODUTTORI LAMELLARE","Holzleimbau");
SDK::setLanguageEntry("APP_STRINGS","fr_fr","PRODUTTORI LAMELLARE","Fabricant de lamelle colle");
SDK::setLanguageEntry("APP_STRINGS","en_us","PRODUTTORI LAMELLARE","Glulam producer");
SDK::setLanguageEntry("APP_STRINGS","es_es","PRODUTTORI LAMELLARE","Productor de madera laminada");
SDK::setLanguageEntry("APP_STRINGS","pt_pt","PRODUTTORI LAMELLARE","Produtores de mlc");

// PRODUTTORI XLAM
SDK::setLanguageEntry("APP_STRINGS","it_it","PRODUTTORI XLAM","Produttore XLAM");
SDK::setLanguageEntry("APP_STRINGS","de_de","PRODUTTORI XLAM","CLT Hersteller");
SDK::setLanguageEntry("APP_STRINGS","fr_fr","PRODUTTORI XLAM","Fabricant XLAM");
SDK::setLanguageEntry("APP_STRINGS","en_us","PRODUTTORI XLAM","CLT producer");
SDK::setLanguageEntry("APP_STRINGS","es_es","PRODUTTORI XLAM","Productor de madera contralaminada");
SDK::setLanguageEntry("APP_STRINGS","pt_pt","PRODUTTORI XLAM","Produtores de CLT");
SDK::setLanguageEntry("APP_STRINGS","ru_ru","PRODUTTORI XLAM","CLT producer");

// PAVIMENTISTA
SDK::setLanguageEntry("PAVIMENTISTA","fr_fr","PAVIMENTISTA","Poseur de parquet");
// FALEGNAME/SERRAMENTISTA
SDK::setLanguageEntry("APP_STRINGS","de_de","FALEGNAME/SERRAMENTISTA","Tischler/fensterbauertischler/fensterbauer");
SDK::setLanguageEntry("APP_STRINGS","fr_fr","FALEGNAME/SERRAMENTISTA","Menuisier/artisan bois");
SDK::setLanguageEntry("APP_STRINGS","en_us","FALEGNAME/SERRAMENTISTA","Joiner/windows fitterjoiner/windows fitter");
SDK::setLanguageEntry("APP_STRINGS","es_es","FALEGNAME/SERRAMENTISTA","Ebanisteria/cerramientos");
SDK::setLanguageEntry("APP_STRINGS","pt_pt","FALEGNAME/SERRAMENTISTA","Artesao de carpintaria/carpinteiro de limpos");

// PRODUTTORE SERRAMENTI
SDK::setLanguageEntry("APP_STRINGS","de_de","PRODUTTORE SERRAMENTI","Produzent fenstersysteme");
SDK::setLanguageEntry("APP_STRINGS","fr_fr","PRODUTTORE SERRAMENTI","Fabricant de menuiseries");
SDK::setLanguageEntry("APP_STRINGS","en_us","PRODUTTORE SERRAMENTI","Windows-door producer");
SDK::setLanguageEntry("APP_STRINGS","es_es","PRODUTTORE SERRAMENTI","Fabricante de cerramientos");
SDK::setLanguageEntry("APP_STRINGS","pt_pt","PRODUTTORE SERRAMENTI","Produtor de janelas");

// CASE IN LEGNO
SDK::setLanguageEntry("APP_STRINGS","it_it","CASE IN LEGNO","Holzhauser rahmenbau");
SDK::setLanguageEntry("APP_STRINGS","de_de","CASE IN LEGNO","Maisons en bois");

//IMPERM
SDK::setLanguageEntry("APP_STRINGS","de_de","IMPERM","Bauabdichtung");
SDK::setLanguageEntry("APP_STRINGS","fr_fr","IMPERM","Impermeabilisation et isolation batiment");

//IMPIANTISTI
SDK::setLanguageEntry("APP_STRINGS","de_de","IMPIANTISTI","Solar - photovoltaikmonteure");
SDK::setLanguageEntry("APP_STRINGS","fr_fr","MONTANTICAD","Installateur ligne de vie");


SDK::setLanguageEntry("APP_STRINGS","fr_fr","RIVENDITA LEGNAME / SEGHERIA (NON FA LAVORI)","Revendeur de bois/scierie (non charpente)");
SDK::setLanguageEntry("APP_STRINGS","fr_fr","RIVENDITA LEGNAME / SEGHERIA CHE FA LAVORI","Revendeur de bois/scierie (aussi charpente)");

SDK::setLanguageEntry("APP_STRINGS","de_de","RIVENDITA SPECIALIZZATA SETTORE CARPENTERIA","Einzelhandel spezialisiert auf zimmerei");
SDK::setLanguageEntry("APP_STRINGS","fr_fr","RIVENDITA SPECIALIZZATA SETTORE CARPENTERIA","Revente specialisee en charpenterie");
SDK::setLanguageEntry("APP_STRINGS","fr_fr","RIVEMATED","Revente materiaux de construction");
SDK::setLanguageEntry("APP_STRINGS","de_de","GDO","Organisierter grosshaendler");
SDK::setLanguageEntry("APP_STRINGS","fr_fr","RIVENDITE SISTEMI ANTICADUTA","Revente systemes antichute");
SDK::setLanguageEntry("APP_STRINGS","de_de","DISTRIBUTORE SERRAMENTI","Haendler fenstersysteme");
SDK::setLanguageEntry("APP_STRINGS","fr_fr","DISTRIBUTORE SERRAMENTI","Revente menuiseries");
SDK::setLanguageEntry("APP_STRINGS","en_us","DISTRIBUTORE SERRAMENTI","Windows-door distributor");
SDK::setLanguageEntry("APP_STRINGS","es_es","DISTRIBUTORE SERRAMENTI","Distribuidor de cerramientos");
SDK::setLanguageEntry("APP_STRINGS","pt_pt","DISTRIBUTORE SERRAMENTI","Distribuidor de perfil - molduras de janelas");

SDK::setLanguageEntry("APP_STRINGS","de_de","ENTEPUBB","Oeffentliche aemter");
SDK::setLanguageEntry("APP_STRINGS","fr_fr","ORDINI CATEGORIA/ASSOCIAZIONI","Associations");
SDK::setLanguageEntry("APP_STRINGS","fr_fr","AMMINISTRCOND","Administrateurs de immeuble");
?>
