<?
ini_set('display_errors','off');
ini_set('default_charset','ISO-8859-1');
//error_reporting(E_ALL);
date_default_timezone_set('Europe/Paris');
error_reporting(E_ALL ^ E_NOTICE);



$USER_DB_DBH='xxxxx';
$PASSWD_DB_DBH='xxxxx';
$SID_DB_DBH='//xxxxxxx:1521/xxxx';

// specify ldap information
$LDAP_SERVER='';
$LDAP_BASE="";
$LDAP_PROTOCOLE_VERSION=3;
$LDAP_REFERRALS=0;
$LDAP_USER="";
$LDAP_PASSWD="";
$SUFFIXE_MAIL="";

$GEO_CENTRE='48.845853,2.303152'; // defaut center of google map

$CHEMIN_GLOBAL="/var/www/dwh"; // Full path to the web directory
$CHEMIN_GRAPHVIZ="/usr/bin/dot"; // full path to the bin dot 
$URL_DWH="http://your_url/dwh/"; // http url to access to Dr WH
$JSON_TRANSLATION_FILE=$CHEMIN_GLOBAL."/en.json";  // json file for the language file
$API_FAMILY_TREE="http://url_to_create_family_tree/create_familytree.php"; // URL of the Family tree API
$GOOGLE_MAP_API_KEY="xxxxxxxxxxx"; // Google MAP API KEY, please, create your own API key : https://developers.google.com/maps/documentation/javascript/get-api-key


$type_texte_defaut='patient_text';
$tableau_global_contexte['patient_text']='Patient';
$tableau_global_contexte['family_text']='Family history';
$tableau_global_contexte['text']='Text';

$thesaurus_code_labo='XXXX'; // Thesaurus code used for biological result 
$document_origin_code_labo='yyyy'; // Origin document code for biological result 

$thesaurus_code_pmsi='BBBB'; // thesaurus code used for ICD10 structured data
$document_origin_code_pmsi='CCCC';  // thesausus code used for document origin code for ICD10.

// list of stop words for your language (here in english) 
$liste_stop_words="'I','a','about','an','are','as','at','be','by','com','for','from','how','in','is','it','of','on','or','that','the','this','to','was','what','when','where','who','will','with','the','www'";


$modulo_ligne_ajoute=50; // number of patients display in the result of the search engine




$tableau_user_droit=array('admin','search_engine','all_departments','nominative','anonymized','see_detailed','see_stat','see_concept','see_drg','see_biology','see_genetic','see_map','see_clustering','patient_quick_access','see_debug','admin_directory','admin_datamart','modify_patient','fuzzy_display');
$tableau_datamart_droit=array('nominative','see_detailed','see_stat','see_concept','see_drg','see_biology','see_map','see_genetic','see_clustering');
$tableau_cohorte_droit=array('ajouter_patient','see_stat','see_concept','see_drg','see_biology','nominative','see_detailed','see_genetic','see_clustering');

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



?>