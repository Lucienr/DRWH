<?
/*
    Dr Warehouse is a document oriented data warehouse for clinicians. 
    Copyright (C) 2017  Nicolas Garcelon

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

    Contact : Nicolas Garcelon - nicolas.garcelon@institutimagine.org
    Institut Imagine
    24 boulevard du Montparnasse
    75015 Paris
    France
*/

putenv("NLS_LANG=French");

include_once "parametrage.php";
include_once "connexion_bdd.php";
include_once("fonctions_droit.php");
include_once("fonctions_dwh.php");
include_once "fonctions_stat.php"; 
include_once "fonctions_concepts.php"; 
include_once "fonctions_pmsi.php"; 
include_once "fonctions_labo.php"; 
include_once "similarite_fonction.php"; 

$tmpresult_num=$argv[1];
$distance=$argv[2];
$limite_count_concept_par_patient_num=$argv[3];
$limite_longueur_vecteur=$argv[4];
$limite_valeur_similarite=$argv[5];
$limite_min_nb_patient_par_code=$argv[6];
$process_num=$argv[7];
$nb_concept_commun=$argv[8];

$tableau_process=get_process($process_num);
$user_num_session=$tableau_process['USER_NUM'];


update_process ($process_num,'0',get_translation('PROCESS_ONGOING_CLUSTERING','cluster en cours'),'');


if ($distance=='') {
	$distance=10;
}

if ($limite_count_concept_par_patient_num=='') {
	$limite_count_concept_par_patient_num=8; // defini le nombre minimum de concepts distincts que doit avoir un patient pour qu'il puisse etre pris en compte dans le calcul
}

if ($limite_longueur_vecteur=='') {
	$limite_longueur_vecteur=0.05; // defini la longueur minimum du vecteur patient, pour qu'il soit pris en compte 
}

if ($limite_valeur_similarite=='') {
	$limite_valeur_similarite=40; // defini la valeur minimum du coefficient de similarite
}

if ($limite_min_nb_patient_par_code=='') {
	$limite_min_nb_patient_par_code=3; // defini la valeur minimum du coefficient de similarite
}

if ($argv[1]!='') {
	$fichier_dot='';
	$deja_patient_num=array();
	$tableau_cluster=array();
	$tableau_cluster_liste_patient_num=array();
	$tableau_html_liste_patients='';
	$tableau_html_liste_clusters='';
	
	$phrase_parametrage='';
	//$req_certitude=" and certainty=1";
	$req_contexte=" and context='patient_text' ";
	$coef_tfidf=1;
	$coef_freq=0;
	$anonyme='';
	
	calcul_clustering ($distance,$nb_concept_commun,$limite_count_concept_par_patient_num,$limite_longueur_vecteur,$limite_valeur_similarite,$limite_min_nb_patient_par_code,"and patient_num in ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num)");
	
	$inF = fopen("$CHEMIN_GLOBAL_UPLOAD/tmp_graphviz_cluster_tfidf_$tmpresult_num.$process_num.dot","w");
	fputs( $inF,"$fichier_dot");
	fclose($inF);
	
	$inF = fopen("$CHEMIN_GLOBAL_UPLOAD/tableau_html_liste_patients_$tmpresult_num.$process_num.html","w");
	fputs( $inF,"$tableau_html_liste_patients");
	fclose($inF);
	
	
	$inF = fopen("$CHEMIN_GLOBAL_UPLOAD/tableau_html_liste_clusters_$tmpresult_num.$process_num.html","w");
	fputs( $inF,"$tableau_html_liste_clusters");
	fclose($inF);
	
	$inF = fopen("$CHEMIN_GLOBAL_UPLOAD/tableau_csv_liste_clusters_$tmpresult_num.$process_num.csv","w");
	fputs( $inF,"$csv");
	fclose($inF);
	
	exec("/usr/bin/twopi \"$CHEMIN_GLOBAL_UPLOAD/tmp_graphviz_cluster_tfidf_$tmpresult_num.$process_num.dot\" -Gcharset=latin1 -Tcmapx -o \"$CHEMIN_GLOBAL_UPLOAD/tmp_graphviz_cluster_tfidf_$tmpresult_num.$process_num.map\"  -Tpng -o  \"$CHEMIN_GLOBAL_UPLOAD/tmp_graphviz_cluster_tfidf_$tmpresult_num.$process_num.png\"");

	update_process ($process_num,'1',get_translation('PROCESS_CLUSTER_DONE','cluster fini'),'');
	
}