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

ini_set("memory_limit","100M");
putenv("NLS_LANG=French");

include_once "parametrage.php";
include_once "connexion_bdd.php";
include_once("ldap.php");
include_once("fonctions_droit.php");
include_once("fonctions_dwh.php");
include_once("fonctions_patient.php");

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


if ($_POST['action']=='select_history_query_patient') {
	$patient_num=$_POST['patient_num'];
        $autorisation_voir_patient=autorisation_voir_patient ($patient_num,$user_num_session) ;
	if ($patient_num!='' && $autorisation_voir_patient=='ok') {
		$list_query_patient=get_query_patient_user ($patient_num,$user_num_session);
		$list_query_patient_all=get_query_patient_user ('',$user_num_session);
		
		print "<option value=\"\"></option>";
		print "<optgroup label=\"Historique sur ce patient\">";
		foreach ($list_query_patient as $query => $t) {
			$query_substr=substr($query,0,100);
			if ($query_substr!=$query) {
				$query_substr.="[...]";
			}
			print "<option value=\"$query\">$query_substr</option>";
		}
		print "</optgroup>";
		
		$list_cohort=get_list_cohort_patient($user_num_session,$patient_num);
		foreach ($list_cohort as $cohort_num) {
			$cohort=get_cohort($cohort_num,$option_nb);
			$title_cohort=$cohort['TITLE_COHORT'];
			print "<optgroup label=\"$title_cohort\">";
			$list_query_cohort=get_query_cohort($cohort_num);
			foreach ($list_query_cohort as $query => $t) {
				$query_substr=substr($query,0,100);
				if ($query_substr!=$query) {
					$query_substr.="[...]";
				}
				print "<option value=\"$query\">$query_substr</option>";
			}
			print "</optgroup>";
		}
		
		print "<optgroup label=\"Historique sur tous mes patients\">";
		foreach ($list_query_patient_all as $query => $t) {
				$query_substr=substr($query,0,100);
				if ($query_substr!=$query) {
					$query_substr.="[...]";
				}
			print "<option value=\"$query\">$query_substr</option>";
		}
		print "</optgroup>";
	
	}
}



if ($_POST['action']=='process_parcours') {
	$patient_num=$_POST['patient_num'];
        $autorisation_voir_patient=autorisation_voir_patient ($patient_num,$user_num_session) ;
	if ($patient_num!='' && $autorisation_voir_patient=='ok') {
	
	
		$parcours_sejour_uf_png=parcours_sejour_uf ('','',$patient_num,'patient_num',0);
		$parcours_sejour_service_png=parcours_sejour_service ('','',$patient_num,'patient_num',0);
		$parcours_complet_department_png=parcours_complet('dot','','',$patient_num,'department',0);
		$parcours_complet_unit_png=parcours_complet('dot','','',$patient_num,'unit',0);
		
		if ($parcours_sejour_uf_png!='') {
			$parcours_sejour_service='ok';
		}
		if ($parcours_sejour_service_png!='') {
			$parcours_sejour_uf='ok';
		}
		if ($parcours_complet_department_png!='') {
			$parcours_complet_department='ok';
		}
		if ($parcours_complet_unit_png!='') {
			$parcours_complet_unit='ok';
		}
		
		if ($parcours_sejour_service=='ok') {print "- <span id=\"id_lien_department\" style=\"font-weight:bold;\"><a  href=\"#\" onclick=\"afficher_parcours('department');return false;\">".get_translation('DISPLAY_JOURNEY_STAY_BY_HOSPITAL_DEPARTMENT','Afficher les hospitalisations (niveau service)')."</a></span><br>";}
		if ($parcours_sejour_uf=='ok') {print "- <span id=\"id_lien_unit\" style=\"font-weight:normal;\"><a  href=\"#\"  onclick=\"afficher_parcours('unit');return false;\">".get_translation('DISPLAY_JOURNEY_STAY_BY_HOSPITAL_UNIT','Afficher les hospitalisations (niveau unité)')."</a></span><br>";}
		if ($parcours_complet_department=='ok') {print "- <span id=\"id_lien_complet_department\" style=\"font-weight:normal;\"><a  href=\"#\"  onclick=\"afficher_parcours('complet_department');return false;\">".get_translation('DISPLAY_ALL_MOVEMENT_INCLUDING_CONSULT_DEPARTMENT','Afficher tous les passages (niveau service)')."</a></span><br>";}
		if ($parcours_complet_unit=='ok') {print "- <span id=\"id_lien_complet_unit\" style=\"font-weight:normal;\"><a  href=\"#\"  onclick=\"afficher_parcours('complet_unit');return false;\">".get_translation('DISPLAY_ALL_MOVEMENT_INCLUDING_CONSULT_UNIT','Afficher tous les passages (niveau unité)')."</a></span><br>";}
		print "<br> ";

		if ($parcours_sejour_service=='ok') {print "<div id=\"id_div_img_parcours_department\" style=\"display:block\"><img src=\"data: image/x-png;base64,".base64_encode($parcours_sejour_uf_png)."\"></div> ";}
		if ($parcours_sejour_uf=='ok') {print "<div id=\"id_div_img_parcours_unit\" style=\"display:none\"><img src=\"data: image/x-png;base64,".base64_encode($parcours_sejour_service_png)."\"></div> ";}
		if ($parcours_complet_department=='ok') {print "<div id=\"id_div_img_parcours_complet_department\" style=\"display:none\"><img src=\"data: image/x-png;base64,".base64_encode($parcours_complet_department_png)."\"></div> ";}
		if ($parcours_complet_unit=='ok') {print "<div id=\"id_div_img_parcours_complet_unit\" style=\"display:none\"><img src=\"data: image/x-png;base64,".base64_encode($parcours_complet_unit_png)."\"></div> ";}
		print "<br> ";
		print "<br> ";
		print "<br> ";
		
		$tableau_parcours=display_patient_mvt ($patient_num);
		print $tableau_parcours;
	}
}



if ($_POST['action']=='filtre_patient_texte') {
	$patient_num=$_POST['patient_num'];
	$requete=trim(nettoyer_pour_requete_patient(urldecode($_POST['requete'])));
	affiche_liste_document_patient($patient_num,$requete);
	save_log_query($user_num_session,'patient',$requete,$patient_num);
		
}
if ($_POST['action']=='filtre_patient_texte_biologie') {
	$patient_num=$_POST['patient_num'];
	$requete=trim(nettoyer_pour_requete(urldecode($_POST['requete'])));
	affiche_liste_document_biologie($patient_num,$requete);
	save_log_query($user_num_session,'patient',$requete,$patient_num);
		
}


if ($_POST['action']=='filtre_patient_texte_timeline') {
	$patient_num=$_POST['patient_num'];
	$requete=trim(nettoyer_pour_requete(urldecode($_POST['requete'])));
	$tableau_id_document=affiche_liste_id_document_patient($patient_num,$requete);
	
//	$tableau_id_document =explode(';',$liste_id_document);
	foreach ($tableau_id_document as $document_num) {
		if ($document_num!='') {
			print "jQuery(\".class_$document_num\").css({ opacity: 1 });";
			print "jQuery(\".class_doc_$document_num\").css('color','red');";
		}
	}
	save_log_query($user_num_session,'patienttimeline',$requete,$patient_num);
}

if ($_POST['action']=='display_biological_document_list') {
	$patient_num=$_POST['patient_num'];
	affiche_liste_document_biologie($patient_num,"");
	save_log_query($user_num_session,'display_biological_document_list',"",$patient_num);
}

if ($_POST['action']=='display_biological_table') {
	$patient_num=$_POST['patient_num'];
	affiche_tableau_biologie($patient_num);
	save_log_query($user_num_session,'display_biological_table',"",$patient_num);
}


if ($_POST['action']=='process_pmsi_patient') {
	$patient_num=$_POST['patient_num'];
	display_pmsi_patient ($patient_num);
	save_log_query($user_num_session,'display_pmsi_patient',"",$patient_num);
}

if ($_POST['action']=='display_sentence_with_term') {
	$patient_num=$_POST['patient_num'];
	$concept_str=$_POST['concept_str'];
	
	$appercu=display_sentence_with_term ($patient_num,$_POST['list_document_num'],$concept_str);
	print "$appercu";
}
?>