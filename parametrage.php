<?php
ini_set('display_errors','off');
ini_set('default_charset','ISO-8859-1');
//error_reporting(E_ALL);
date_default_timezone_set('Europe/Paris');
error_reporting(E_ALL ^ E_NOTICE);

$maintenance='';
$CHARSET='latin1'; // UTF8 

$USER_DB_DBH='USER_DB_DBH';
$PASSWD_DB_DBH='PASSWD_DB_DBH';
$SID_DB_DBH='SID_DB_DBH';

// specify ldap information
$LDAP_SERVER="LDAP_SERVER";
$LDAP_BASE="LDAP_BASE";
$LDAP_PROTOCOLE_VERSION=3;
$LDAP_REFERRALS=0;
$LDAP_USER="CN=xxxxxxx,OU=100-utilisateurs,OU=-standard,OU=xxxx,DC=xxxxxx,DC=xx,DC=xxxxxx,DC=xx";
$LDAP_PASSWD="xxxxxx";
$SUFFIXE_MAIL="xxx.xx.xxxx.fr";

$GEO_CENTRE='48.845853,2.303152'; // defaut center of google map

$CHEMIN_GLOBAL="/var/www/sites/dwh"; // Full path to the web directory
$CHEMIN_GLOBAL_ETL="/var/www/sites/dwh/TAL";// Full path to the ETL directory
$CHEMIN_GLOBAL_ETL_LOG="/var/www/sites/dwh/TAL/log";// Full path to the ETL directory

$CHEMIN_GLOBAL_LOG="/var/www/sites/dwh/upload";// Full path to the LOG directory

$CHEMIN_GLOBAL_UPLOAD="/var/www/sites/dwh/upload";// Full path to the upload directory (-creation of image, dot, map)

$CHEMIN_GLOBAL_MAINTENANCE="/var/www/sites/dwh/maintenance";// Full path to the MAINTENANCE directory : crontab_requete.php, crontab_purge.php

$URL_UPLOAD="upload";// Full path to the ETL directory

$CHEMIN_GRAPHVIZ="/usr/bin/dot"; // full path to the bin dot : which dot

$URL_DWH="http://serveur/sites/dwh/"; // http url to access to Dr WH

$JSON_TRANSLATION_FILE=$CHEMIN_GLOBAL."/fr.json";  // json file for the language file

$API_FAMILY_TREE="http://serveur/family_tree_ws/creer_familytree.php"; // URL of the Family tree API

$URL_SIH_PATIENT_API="http://serveur/sites/api_patient/sih_patient_api.php"; // http url to access to SIH Patient API 

$GOOGLE_MAP_API_KEY="cle api google map"; // Google MAP API KEY, please, create your own API key : https://developers.google.com/maps/documentation/javascript/get-api-key

// requete pour faire mini démo pour les utilsiateurs !
//$tableau_query_demo[9740841]="Essayez cette requête avec plusieurs sous recherches";


$thesaurus_code_labo='STARE';
$document_origin_code_labo='STARE';

$thesaurus_code_pmsi='cim10';
$document_origin_code_pmsi='PMSI_DIAG';

$EXCEL_DECIMAL=",";

$modulo_ligne_ajoute=50;

$tableau_user_droit=array('admin','search_engine','all_departments','nominative','anonymized','patient_quick_access','see_detailed','see_stat','see_concept','export_data','regexp','see_drg','see_biology','see_genetic','see_map','see_clustering','admin_directory','opposition_patient');

$tableau_datamart_droit=array('nominative','see_detailed','see_stat','see_concept','see_drg','see_biology','see_map','see_genetic','see_clustering','export_data','regexp');
$tableau_cohorte_droit=array('add_patient','see_stat','see_concept','see_drg','see_biology','nominative','see_detailed','see_genetic','see_clustering','export_data','regexp');

$tableau_patient_droit=array('patient_documents','patient_labo','patient_family','patient_timeline','patient_carepath','patient_cohort','patient_concept','patient_similarity','patient_ecrf');

$tableau_patient_droit_default=array('patient_documents','patient_labo','patient_timeline','patient_carepath','patient_cohort','patient_concept');
$tableau_user_droit_default=array('search_engine','nominative','see_detailed','see_stat','see_concept','patient_quick_access');

// Les contexts :
$type_texte_defaut='patient_text';
$tableau_global_contexte['patient_text']='Patient';
$tableau_global_contexte['family_text']='Antécédents familiaux';
$tableau_global_contexte['text']='Texte intégral';


$tableau_couleur[0]="#FF99FF";
$tableau_couleur[1]="#FF9999";
$tableau_couleur[2]="#FF9933";
$tableau_couleur[3]="#FF3333";
$tableau_couleur[4]="#CC6633";
$tableau_couleur[5]="#996699";
$tableau_couleur[6]="#9900FF";
$tableau_couleur[7]="#993300";
$tableau_couleur[8]="#999900";
$tableau_couleur[9]="#3300FF";
$tableau_couleur[10]="#009966";
$tableau_couleur[11]="#6699CC";
$tableau_couleur[12]="#FF00FF";
$tableau_couleur[13]="#00CC00";
$tableau_couleur[14]="#00CCCC";
$tableau_couleur[15]="#CC0099";
$tableau_couleur[16]="#66CC00";


$liste_type_semantic_phenotype="'Sign or Symptom','Disease or Syndrome','Finding','Pathologic Function','Congenital Abnormality','Physiologic Function','Anatomical Abnormality','Neoplastic Process','Acquired Abnormality','Mental or Behavioral Dysfunction'";
$liste_type_semantic_genotype="'Gene or Genome'";
// list of stop words for your language (here in english) 
$liste_stop_words="'ces', 'par','les','te','nom','mais','cp','lp','se','or','la','ca','des','a','mal','oscar','mars','sera','sans','sur','par','est','des','avp','fat','et','la','le','au','sa','en','y','chs','ide','mal','vit','met','ait','marie','etat general','cap','asp','rcp','sert','cle','lsd','ct','ado','faim','aura','ira','maladie','diagnostic','etat general','pr','a','hyper','separe','atteinte','net','pathologie'";


?>