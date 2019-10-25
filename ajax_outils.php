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
include ("fonctions_outils.php");


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



if ($_POST['action']=='comparer_patient') {
	$patient_num_1=$_POST['patient_num_1'];
	$patient_num_2=$_POST['patient_num_2'];
	$distance=$_POST['distance'];
	
	if ($distance=='') {
		$distance=9;
	}
	
	$tableau_patient_num_1=array();
	$tableau_patient_num_2=array();
	
	if ($distance==10) {
		$requete=" select  concept_code,concept_str,count(*) as TF 
		from (
			select  enrsem_num,concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
			where a.concept_code_father='RACINE'   and
			patient_num='$patient_num_1' and 
			a.distance<=$distance and
			 context='patient_text' and
			 certainty=1 and
			a.concept_code_son=c.concept_code 
			) t,
		dwh_thesaurus_enrsem
		where 
			concept_code_son=concept_code and pref='Y'
		group by  concept_code, concept_str
		order by concept_code
	      ";
	} else {
	
		$requete=" select  concept_code, concept_str,count(*) as TF 
		from (
			select  enrsem_num,a.concept_code_son ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_thesaurus_enrsem_graph b, dwh_enrsem c
			where a.concept_code_father='RACINE'  and
			patient_num='$patient_num_1' and 
			a.distance=$distance and
			a.concept_code_son=b.concept_code_father and
			 context='patient_text' and 
			 certainty=1 and
			b.concept_code_son=c.concept_code 
			union
			select  enrsem_num,concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
			where a.concept_code_father='RACINE'   and
			patient_num='$patient_num_1' and 
			a.distance<=$distance and
			 context='patient_text' and
			 certainty=1 and
			a.concept_code_son=c.concept_code 
			) t,
		dwh_thesaurus_enrsem
		where 
			concept_code_son=concept_code and pref='Y'
		group by  concept_code, concept_str
		order by concept_code
	      ";
	}
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$concept_str=$r['CONCEPT_STR'];
		$concept_code=$r['CONCEPT_CODE'];
		$tf=$r['TF'];
		$concept_str=preg_replace("/'/"," ",$concept_str);
		$tableau_code_libelle[$concept_code]=ucwords(strtolower($concept_str));
		//$tableau_patient_num_1[$concept_code]='ok';
		array_push($tableau_patient_num_1, "$concept_code");
		$tableau_code_nb_concept[$concept_code]=$tf;
	}

	if ($distance==10) {
		$requete=" select  concept_code,concept_str,count(*) as TF 
		from (
			select  enrsem_num,concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
			where a.concept_code_father='RACINE'   and
			patient_num='$patient_num_2' and 
			a.distance<=$distance and
			 context='patient_text' and
			 certainty=1 and
			a.concept_code_son=c.concept_code 
			) t,
		dwh_thesaurus_enrsem
		where 
			concept_code_son=concept_code and pref='Y'
		group by  concept_code, concept_str
		order by concept_code
	      ";
	} else {
	
		$requete=" select  concept_code, concept_str,count(*) as TF 
		from (
			select  enrsem_num,a.concept_code_son ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_thesaurus_enrsem_graph b, dwh_enrsem c
			where a.concept_code_father='RACINE'  and
			patient_num='$patient_num_2' and 
			a.distance=$distance and
			a.concept_code_son=b.concept_code_father and
			 context='patient_text' and
			 certainty=1 and
			b.concept_code_son=c.concept_code 
			union
			select  enrsem_num,concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
			where a.concept_code_father='RACINE'   and
			patient_num='$patient_num_2' and 
			a.distance<=$distance and
			 context='patient_text' and
			 certainty=1 and
			a.concept_code_son=c.concept_code 
			) t,
		dwh_thesaurus_enrsem
		where 
			concept_code_son=concept_code and pref='Y'
		group by  concept_code, concept_str
		order by concept_code
	      ";
	}
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$concept_str=$r['CONCEPT_STR'];
		$concept_code=$r['CONCEPT_CODE'];
		$tf=$r['TF'];
		$concept_str=preg_replace("/'/"," ",$concept_str);
		$tableau_code_libelle[$concept_code]=ucwords(strtolower($concept_str));
		array_push($tableau_patient_num_2, "$concept_code");
		$tableau_code_nb_concept[$concept_code]=$tf+$tableau_code_nb_concept[$concept_code];
	}
	$tableau_final_intersect=array();
	$tableau_final_seul1=array();
	$tableau_final_seul2=array();
	$intersect = array_intersect($tableau_patient_num_2,$tableau_patient_num_1);
	$seul1 = array_diff($tableau_patient_num_1, $tableau_patient_num_2);
	$seul2 = array_diff($tableau_patient_num_2, $tableau_patient_num_1);
	
	
	$tf_max=0;
	foreach ($tableau_code_nb_concept as $concept_code => $tf) {
		if ($tf_max<$tf) {
			$tf_max=$tf;
		}
	}
	
	
	
	foreach ($intersect as $concept_code) {
		$tableau_final_intersect[$tableau_code_libelle[$concept_code]]=$concept_code;
	}
	ksort($tableau_final_intersect);
	
	
	foreach ($seul1 as $concept_code) {
		$tableau_final_seul1[$tableau_code_libelle[$concept_code]]=$concept_code;
	}
	ksort($tableau_final_seul1);
	
	foreach ($seul2 as $concept_code) {
		$tableau_final_seul2[$tableau_code_libelle[$concept_code]]=$concept_code;
	}
	ksort($tableau_final_seul2);
	
	
	foreach ($tableau_final_seul1 as $concept_str => $concept_code) {
		$tf=$tableau_code_nb_concept[$concept_code];
		$tf_normalise=round($tf *2/$tf_max,2); // calcul de la taile du text
		if ($tf_normalise<0.8) {
			$tf_normalise=0.8;
		}
		print "<font style=\"font-size:".$tf_normalise."em\">$concept_str#$tf</font><br>";
	}
	print "-separateur_ajax-";
	
	foreach ($tableau_final_intersect as $concept_str => $concept_code) {
	
		$tf=$tableau_code_nb_concept[$concept_code];
		$tf_normalise=round($tf *2/$tf_max,2); // calcul de la taile du text
		if ($tf_normalise<0.8) {
			$tf_normalise=0.8;
		}
		print "<font style=\"font-size:".$tf_normalise."em\">$concept_str#$tf</font><br>";
	}
	print "-separateur_ajax-";
	
	foreach ($tableau_final_seul2 as $concept_str => $concept_code) {
		$tf=$tableau_code_nb_concept[$concept_code];
		$tf_normalise=round($tf *2/$tf_max,2); // calcul de la taile du text
		if ($tf_normalise<0.8) {
			$tf_normalise=0.8;
		}
		print "<font style=\"font-size:".$tf_normalise."em\">$concept_str#$tf</font><br>";
	}
	print "-separateur_ajax-";
	
	save_log_page($user_num_session,"comparer_patient");
}





if ($_POST['action']=='comparer_cohorte') {
	$cohort_num_1=$_POST['cohort_num_1'];
	$cohort_num_2=$_POST['cohort_num_2'];
	$distance=$_POST['distance'];
	$etat_patient_cohorte_1_inclu=$_POST['etat_patient_cohorte_1_inclu'];
	$etat_patient_cohorte_1_exclu=$_POST['etat_patient_cohorte_1_exclu'];
	$etat_patient_cohorte_1_doute=$_POST['etat_patient_cohorte_1_doute'];
	$etat_patient_cohorte_2_inclu=$_POST['etat_patient_cohorte_2_inclu'];
	$etat_patient_cohorte_2_exclu=$_POST['etat_patient_cohorte_2_exclu'];
	$etat_patient_cohorte_2_doute=$_POST['etat_patient_cohorte_2_doute'];
	$liste_etat_1='';
	if ($etat_patient_cohorte_1_inclu!='') {
		$liste_etat_1.="$etat_patient_cohorte_1_inclu,";
	}
	if ($etat_patient_cohorte_1_exclu!='') {
		$liste_etat_1.="$etat_patient_cohorte_1_exclu,";
	}
	if ($etat_patient_cohorte_1_doute!='') {
		$liste_etat_1.="$etat_patient_cohorte_1_doute,";
	}
	$liste_etat_1=substr($liste_etat_1,0,-1);
	if ($liste_etat_1!='') {
		$req_1="and status in ($liste_etat_1)";
	}
	
	$liste_etat_2='';
	if ($etat_patient_cohorte_2_inclu!='') {
		$liste_etat_2.="$etat_patient_cohorte_2_inclu,";
	}
	if ($etat_patient_cohorte_2_exclu!='') {
		$liste_etat_2.="$etat_patient_cohorte_2_exclu,";
	}
	if ($etat_patient_cohorte_2_doute!='') {
		$liste_etat_2.="$etat_patient_cohorte_2_doute,";
	}
	$liste_etat_2=substr($liste_etat_2,0,-1);
	if ($liste_etat_2!='') {
		$req_2="and status in ($liste_etat_2)";
	}
	
	if ($distance=='') {
		$distance=9;
	}
	
	$tableau_patient_num_1=array();
	$tableau_patient_num_2=array();
	
	if ($distance==10) {
		$requete=" select  concept_code, concept_str,count(*) as TF 
		from (
			select  enrsem_num,concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
			where a.concept_code_father='RACINE'   and
			patient_num in (select patient_num from dwh_cohort_result where cohort_num=$cohort_num_1 $req_1)  and 
			a.distance<=$distance and
			 context='patient_text' and
			 certainty=1 and
			a.concept_code_son=c.concept_code 
			) t,
		dwh_thesaurus_enrsem
		where 
			concept_code_son=concept_code and pref='Y'
		group by  concept_code, concept_str
		order by concept_code
	      ";
	} else {
	
		$requete=" select  concept_code, concept_str,count(*) as TF 
		from (
			select  enrsem_num,a.concept_code_son ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_thesaurus_enrsem_graph b, dwh_enrsem c
			where a.concept_code_father='RACINE'  and
			patient_num in (select patient_num from dwh_cohort_result where cohort_num=$cohort_num_1  $req_1)  and 
			a.distance=$distance and
			a.concept_code_son=b.concept_code_father and
			 context='patient_text' and
			 certainty=1 and
			b.concept_code_son=c.concept_code 
			union
			select  enrsem_num,concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
			where a.concept_code_father='RACINE'   and
			patient_num in (select patient_num from dwh_cohort_result where cohort_num=$cohort_num_1  $req_1)  and 
			a.distance<=$distance and
			 context='patient_text' and
			 certainty=1 and
			a.concept_code_son=c.concept_code 
			) t,
		dwh_thesaurus_enrsem
		where 
			concept_code_son=concept_code and pref='Y'
		group by  concept_code, concept_str
		order by concept_code
	      ";
	}
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$concept_str=$r['CONCEPT_STR'];
		$concept_code=$r['CONCEPT_CODE'];
		$tf=$r['TF'];
		$concept_str=preg_replace("/'/"," ",$concept_str);
		$tableau_code_libelle[$concept_code]=ucwords(strtolower($concept_str));
		//$tableau_patient_num_1[$concept_code]='ok';
		array_push($tableau_patient_num_1, "$concept_code");
		$tableau_code_nb_concept[$concept_code]=$tf;
	}

	if ($distance==10) {
		$requete=" select  concept_code, concept_str,count(*) as TF 
		from (
			select  enrsem_num,concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
			where a.concept_code_father='RACINE'   and
			patient_num in (select patient_num from dwh_cohort_result where cohort_num=$cohort_num_2  $req_2)  and 
			a.distance<=$distance and
			 context='patient_text' and
			 certainty=1 and
			a.concept_code_son=c.concept_code 
			) t,
		dwh_thesaurus_enrsem
		where 
			concept_code_son=concept_code and pref='Y'
		group by  concept_code, concept_str
		order by concept_code
	      ";
	} else {
	
		$requete=" select  concept_code, concept_str,count(*) as TF 
		from (
			select  enrsem_num,a.concept_code_son ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_thesaurus_enrsem_graph b, dwh_enrsem c
			where a.concept_code_father='RACINE'  and
			patient_num in (select patient_num from dwh_cohort_result where cohort_num=$cohort_num_2  $req_2)  and 
			a.distance=$distance and
			a.concept_code_son=b.concept_code_father and
			 context='patient_text' and
			 certainty=1 and
			b.concept_code_son=c.concept_code 
			union
			select  enrsem_num,concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
			where a.concept_code_father='RACINE'   and
			patient_num in (select patient_num from dwh_cohort_result where cohort_num=$cohort_num_2  $req_2)  and 
			a.distance<=$distance and
			 context='patient_text' and
			 certainty=1 and
			a.concept_code_son=c.concept_code 
			) t,
		dwh_thesaurus_enrsem
		where 
			concept_code_son=concept_code and pref='Y'
		group by  concept_code, concept_str
		order by concept_code
	      ";
	}
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$concept_str=$r['CONCEPT_STR'];
		$concept_code=$r['CONCEPT_CODE'];
		$tf=$r['TF'];
		$concept_str=preg_replace("/'/"," ",$concept_str);
		$tableau_code_libelle[$concept_code]=ucwords(strtolower($concept_str));
		array_push($tableau_patient_num_2, "$concept_code");
		$tableau_code_nb_concept[$concept_code]=$tf+$tableau_code_nb_concept[$concept_code];
	}
	$tableau_final_intersect=array();
	$tableau_final_seul1=array();
	$tableau_final_seul2=array();
	$intersect = array_intersect($tableau_patient_num_2,$tableau_patient_num_1);
	$seul1 = array_diff($tableau_patient_num_1, $tableau_patient_num_2);
	$seul2 = array_diff($tableau_patient_num_2, $tableau_patient_num_1);
	
	
	$tf_max=0;
	foreach ($tableau_code_nb_concept as $concept_code => $tf) {
		if ($tf_max<$tf) {
			$tf_max=$tf;
		}
	}
	
	
	
	foreach ($intersect as $concept_code) {
		$tableau_final_intersect[$tableau_code_libelle[$concept_code]]=$concept_code;
	}
	ksort($tableau_final_intersect);
	
	
	foreach ($seul1 as $concept_code) {
		$tableau_final_seul1[$tableau_code_libelle[$concept_code]]=$concept_code;
	}
	ksort($tableau_final_seul1);
	
	foreach ($seul2 as $concept_code) {
		$tableau_final_seul2[$tableau_code_libelle[$concept_code]]=$concept_code;
	}
	ksort($tableau_final_seul2);
	
	
	foreach ($tableau_final_seul1 as $concept_str => $concept_code) {
		$tf=$tableau_code_nb_concept[$concept_code];
		$tf_normalise=round($tf *2/$tf_max,2); // calcul de la taile du text
		if ($tf_normalise<0.8) {
			$tf_normalise=0.8;
		}
		print "<font style=\"font-size:".$tf_normalise."em\">$concept_str#$tf</font><br>";
	}
	print "-separateur_ajax-";
	
	foreach ($tableau_final_intersect as $concept_str => $concept_code) {
	
		$tf=$tableau_code_nb_concept[$concept_code];
		$tf_normalise=round($tf *2/$tf_max,2); // calcul de la taile du text
		if ($tf_normalise<0.8) {
			$tf_normalise=0.8;
		}
		print "<font style=\"font-size:".$tf_normalise."em\">$concept_str#$tf</font><br>";
	}
	print "-separateur_ajax-";
	foreach ($tableau_final_seul2 as $concept_str => $concept_code) {
		$tf=$tableau_code_nb_concept[$concept_code];
		$tf_normalise=round($tf *2/$tf_max,2); // calcul de la taile du text
		if ($tf_normalise<0.8) {
			$tf_normalise=0.8;
		}
		print "<font style=\"font-size:".$tf_normalise."em\">$concept_str#$tf</font><br>";
	}
	print "-separateur_ajax-";
	save_log_page($user_num_session,"comparer_cohorte");
}

if ($_POST['action']=='afficher_outil' ) {
	$tool_num=$_POST['tool_num'];
	afficher_outil($tool_num);
	save_log_page($user_num_session,"afficher_outil $tool_num");
}

if ($_POST['action']=='mapper_patient' ) {
	$i=0;
	$process_num=$_POST['process_num'];
	$liste_patient=urldecode($_POST['liste_patient']);
	$option_limite=$_POST['option_limite'];
  	update_process ($process_num,"0","traitement en cours","$liste_patient",$user_num_session,"");
	passthru( "php exec_mapping_patient.php \"$user_num_session\" \"$process_num\" \"$option_limite\"> $CHEMIN_GLOBAL_LOG/log_mapper_patient_$process_num.txt 2>$CHEMIN_GLOBAL_LOG/log_mapper_patient_$process_num.txt &");
	save_log_page($user_num_session,"mapper_patient");
}

if ($_POST['action']=='display_mapper_patient' ) {
	$i=0;
	$process_num=$_POST['process_num'];
	$process=get_process ($process_num);
	$status=$process['STATUS'];	
	$resultat_mapping=$process['RESULT'];	
	if ($status==1) {
		print "<a href=\"export_process.php?process_num=$process_num\">Telecharger sur excel</a><div id=\"id_copier_coller_mapping\"><br>";
		print $resultat_mapping;
		print "</div>";
	}
}

?>