<?
ini_set('display_errors','off');
ini_set('default_charset','ISO-8859-1');
//error_reporting(E_ALL);
date_default_timezone_set('Europe/Paris');
//error_reporting(0);
error_reporting(E_ALL & ~E_NOTICE);


$maintenance='';
$CHARSET='latin1'; // UTF8

$USER_DB_DBH='dwh_app'; // User APP  with read only
$PASSWD_DB_DBH='dwh11';
$SID_DB_DBH='localhost:1521/xe';
$USER_DB_DBH_ETL='dwh'; // User std with read write
$PASSWD_DB_DBH_ETL='dwh11';

// specify ldap information
$LDAP_SERVER='';
$LDAP_BASE="";
$LDAP_PROTOCOLE_VERSION=3;
$LDAP_REFERRALS=0;
$LDAP_USER="";
$LDAP_PASSWD="";
$SUFFIXE_MAIL="";

$GEO_CENTRE='48.845853,2.303152'; // defaut center of google map

$CHEMIN_GLOBAL=""; // Full path to the web directory
$CHEMIN_GLOBAL_ETL="";// Full path to the ETL directory
$CHEMIN_GLOBAL_ETL_LOG="";// Full path to the ETL LOG directory

$CHEMIN_GLOBAL_LOG="";// Full path to the  LOG directory

$CHEMIN_GLOBAL_UPLOAD="";// Full path to the upload directory (-creation of image, dot, map)

$CHEMIN_GLOBAL_MAINTENANCE="";// Full path to the MAINTENANCE directory : crontab_requete.php, crontab_purge.php

$URL_UPLOAD="upload";// relative URL directory to access to the UPLOAD directory

$CHEMIN_GRAPHVIZ="/usr/bin/dot"; // full path to the bin dot : which dot

$URL_DWH=""; // http url to access to Dr WH http://<ip or full dns>/DRWH/

$JSON_TRANSLATION_FILE=$CHEMIN_GLOBAL."/fr.json";  // json file for the language file

$API_FAMILY_TREE=""; // http://url_to_create_family_tree/create_familytree.php URL of the Family tree API

$URL_SIH_PATIENT_API=""; // http url to access to SIH Patient API

$GOOGLE_MAP_API_KEY=""; // create your own API key : https://developers.google.com/maps/documentation/javascript/get-api-key

// requete pour faire mini démo pour les utilsiateurs !
$tableau_query_demo[9740841]="Essayez cette requête avec plusieurs sous recherches";
$tableau_query_demo[9740821]="Essayez cette autre requête avec une recherche avancée";
$tableau_query_demo[9740801]="Essayez cette requête simple";

// Les contexts :
$type_texte_defaut='patient_text';
$tableau_global_contexte['patient_text']='Patient';
$tableau_global_contexte['family_text']='Texte famille auto';
$tableau_global_contexte['text']='Text';


$thesaurus_code_labo='BIO'; // Thesaurus code used for biological result
$document_origin_code_labo='BIO'; // Origin document code for biological result

$thesaurus_code_pmsi='CIM10'; // thesaurus code used for ICD10 structured data
$document_origin_code_pmsi='CIM10';  // thesausus code used for document origin code for ICD10.

// list of stop words for your language (here in english)
$liste_stop_words="'ces', 'par','les','te','nom','mais','cp','lp','se','or','la','ca','des','a','mal','oscar','mars','sera','sans','sur','par','est','des','avp','fat','et','la','le','au','sa','en','y','chs','ide','mal','vit','met','ait','marie','etat general','cap','asp','rcp','sert','cle','lsd','ct','ado','faim','aura','ira','maladie','diagnostic','etat general','pr','a','hyper','separe','atteinte','net','pathologie'";

$EXCEL_DECIMAL=",";
$modulo_ligne_ajoute=50; // number of patients display in the result of the search engine

//$tableau_user_droit=array('admin','search_engine','all_departments','nominative','anonymized','see_detailed','see_stat','see_concept','see_drg','see_biology','see_genetic','see_map','see_clustering','patient_quick_access','see_debug','admin_directory','admin_datamart','modify_patient','fuzzy_display');
//$tableau_datamart_droit=array('nominative','see_detailed','see_stat','see_concept','see_drg','see_biology','see_map','see_genetic','see_clustering');
//$tableau_cohorte_droit=array('ajouter_patient','see_stat','see_concept','see_drg','see_biology','nominative','see_detailed','see_genetic','see_clustering');

$tableau_user_droit=array('admin','search_engine','all_departments','nominative','anonymized','patient_quick_access','see_detailed',
'see_stat','see_concept','export_data','regexp','see_drg','see_biology',
'see_genetic','see_map','see_clustering',
'admin_directory','opposition_patient');

$tableau_datamart_droit=array('nominative','see_detailed','see_stat','see_concept','see_drg','see_biology','see_map','see_genetic','see_clustering','export_data');
$tableau_cohorte_droit=array('add_patient','see_stat','see_concept','see_drg','see_biology','nominative','see_detailed','see_genetic','see_clustering','export_data');

$tableau_patient_droit=array('patient_documents','patient_labo','patient_family','patient_timeline','patient_carepath','patient_cohort','patient_concept','patient_similarity','patient_ecrf');

$tableau_patient_droit_default=array('patient_documents','patient_labo','patient_timeline','patient_carepath','patient_cohort','patient_concept');
$tableau_user_droit_default=array('search_engine','nominative','see_detailed','see_stat','see_concept','patient_quick_access');



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

$module_anonymous_active=TRUE;
/* 
IF MODULE ANONYMOUS ACTIVE 
- add 'export_anonymized' in $tableau_user_droit
- add $anonymous_api_url
*/
$anonymous_api_url = "http://127.0.0.1:8000/anonymous/";
$module_arno_active=TRUE;
?>
