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
session_start();

ini_set("memory_limit","800M");
putenv("NLS_LANG=French");

include_once "parametrage.php";
include_once "connexion_bdd.php";
include_once("ldap.php");
include_once("fonctions_droit.php");
include_once("fonctions_dwh.php");
include_once "fonctions_stat.php"; 
include_once "fonctions_concepts.php"; 
include_once "fonctions_pmsi.php"; 
include_once "fonctions_labo.php"; 

if ($_POST['action']=='connexion') {
	$erreur=verif_connexion($_POST['login'],$_POST['passwd'],'page_ajax');
	print "$erreur";
	exit;
}

if ($_SESSION['dwh_login']=='') {
	print "deconnexion";
	exit;
} else {
	include_once("verif_droit.php");
	if ($erreur_droit!='') {
		print "$erreur_droit";
		exit;
	}
}
session_write_close();




if ($_POST['action']=='executer_clustering') {
	$tmpresult_num=$_POST['tmpresult_num'];
	$limite_similarite=$_POST['limite_similarite'];
	$process_num=uniqid();
	
	create_process ($process_num,$user_num_session,'0',get_translation('PROCESS_START','début du process'),'','sysdate+7');
	
	passthru( "php exec_clustering.php \"$tmpresult_num\" \"10\" \"3\" \"0.01\" \"$limite_similarite\" \"2\" \"$process_num\" \"7\" >> upload/log_chargement_clustering_$tmpresult_num.$process_num.txt 2>&1 &");
	print $process_num;
	save_log_page($user_num_session,'clustering');
}

if ($_POST['action']=='verifier_process_fini_executer_clustering') {
	$process_num=$_POST['process_num'];
	$tmpresult_num=$_POST['tmpresult_num'];
	if ($process_num!='') {
		$tableau_process=get_process ($process_num);
		$status=$tableau_process['STATUS'];
		$commentary=$tableau_process['COMMENTARY'];
		$res="$status;$commentary";
		
		if ($status==0) {
			$process=verif_process("exec_clustering");
			if ($process==1) { // fini
				$process=verif_process("tmp_graphviz_cluster_tfidf_$tmpresult_num.$process_num.dot");
				if ($process==1) { // fini
					$res= "erreur;".get_translation('ERROR_CLUSTERING_COMPUTATION','il y a une erreur dans le calcul de clustering ...');
				}
			}
		} 
		print $res;
	}
}

if ($_POST['action']=='afficher_resultat_clustering') {
	$process_num=$_POST['process_num'];
	$tmpresult_num=$_POST['tmpresult_num'];
	if ($process_num!='') {
		print "<img src=\"upload/tmp_graphviz_cluster_tfidf_$tmpresult_num.$process_num.png?$process_num\" usemap=\"#cluster_patient\" border=0>";
		$map=join('',file("$CHEMIN_GLOBAL/upload/tmp_graphviz_cluster_tfidf_$tmpresult_num.$process_num.map"));
		print $map;
		$tableau_html_liste_clusters = join('',file("$CHEMIN_GLOBAL/upload/tableau_html_liste_clusters_$tmpresult_num.$process_num.html")); 
		print $tableau_html_liste_clusters;
	}
}

?>