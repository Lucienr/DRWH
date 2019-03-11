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
	$liste_patient=urldecode($_POST['liste_patient']);

	$i=0;
  	$tableau_ligne=preg_split("/[\n\r]/",$liste_patient);	
	print "
	<a href=\"#\" onclick=\"fnSelect('id_copier_coller_mapping');return false\">Copier coller</a><div id=\"id_copier_coller_mapping\"><table class=\"tablefin\"><tr>
	<td>i</td>
	<td>lastname</td>
	<td>firstname</td>
	<td>birth_date</td>
	<td>method</td>
	<td>IPP</td>
	<td>LASTNAME</td>
	<td>LASTNAME 2</td>
	<td>FIRSTNAME</td>
	<td>BIRTH_DATE</td>
	<td>SEX</td>
	</tr>";
  	foreach ($tableau_ligne as $ligne) {
  		$i++;
  		$tab=preg_split("/[;,\t]/",$ligne);
  		$lastname=trim($tab[0]);
  		$firstname=trim($tab[1]);
  		$birth_date=trim($tab[2]);
  		$patient_num='';
		$lastname_q=nettoyer_pour_insert ($lastname);
		$firstname_q=nettoyer_pour_insert ($firstname);
		$method='';
		$sql_sih="and patient_num in (select patient_num from dwh_patient_ipphist where origin_patient_id='SIH') ";
		if ($lastname!='' && $firstname!='' && $birth_date!='') {
			$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
			(
			regexp_replace(upper( CONVERT(lastname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') or 
			regexp_replace(upper( CONVERT(maiden_name, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') 
			)
			and 
			regexp_replace(upper( CONVERT(firstname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$firstname_q', 'US7ASCII') ),'[^A-Z]','') and
			lastname is not null and 
			firstname is not null and
			birth_date is not null and
			birth_date=to_date('$birth_date','DD/MM/YYYY')
			$sql_sih ");
			oci_execute($sel);
			$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
			$patient_num=$r['PATIENT_NUM'];
			$method='exact_match';
			
			// is error on encodage date in excel ... 1462 days 
	  		if ($patient_num=='') {
				$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
				(
				regexp_replace(upper( CONVERT(lastname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') or 
				regexp_replace(upper( CONVERT(maiden_name, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') 
				)
				and 
				regexp_replace(upper( CONVERT(firstname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$firstname_q', 'US7ASCII') ),'[^A-Z]','') and
				lastname is not null and 
				firstname is not null and
				birth_date is not null and
				abs(to_date('$birth_date','DD/MM/YYYY')-birth_date) =1462
				$sql_sih ");
				oci_execute($sel);
				$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
				$patient_num=$r['PATIENT_NUM'];
				$method='lastname and firstname exact match, birth date 1462 days : excel convert';
			}
			
			
	  		if ($patient_num=='' && preg_match("/ /",$lastname_q)) {
	  			$tab_lastname=explode(" ",$lastname_q);
	  			$new_lastname_q=$tab_lastname[1]." ".$tab_lastname[0];
				$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
				(
				regexp_replace(upper( CONVERT(lastname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$new_lastname_q', 'US7ASCII') ),'[^A-Z]','') or 
				regexp_replace(upper( CONVERT(maiden_name, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$new_lastname_q', 'US7ASCII') ),'[^A-Z]','') 
				)
				and 
				regexp_replace(upper( CONVERT(firstname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$firstname_q', 'US7ASCII') ),'[^A-Z]','') and
				lastname is not null and 
				firstname is not null and
				birth_date is not null and
				birth_date=to_date('$birth_date','DD/MM/YYYY')
				$sql_sih ");
				oci_execute($sel);
				$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
				$patient_num=$r['PATIENT_NUM'];
				$method='exact_match - inversion nom compose';
	  		
	  		}
	  		
	  		
	  		if ($patient_num=='') {
				$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
					(
					INSTR(regexp_replace(upper( CONVERT(lastname, 'US7ASCII') ),'[^A-Z]','') ,regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]',''))>0 or 
					INSTR(regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') ,regexp_replace(upper( CONVERT(lastname, 'US7ASCII') ),'[^A-Z]',''))>0 or 
					INSTR(regexp_replace(upper( CONVERT(maiden_name, 'US7ASCII') ),'[^A-Z]',''), regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]',''))>0 or 
					INSTR(regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') ,regexp_replace(upper( CONVERT(maiden_name, 'US7ASCII') ),'[^A-Z]',''))>0 
					) and 
				regexp_replace(upper( CONVERT(firstname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$firstname_q', 'US7ASCII') ),'[^A-Z]','') and
				lastname is not null and 
				firstname is not null and
				birth_date is not null and
				birth_date=to_date('$birth_date','DD/MM/YYYY')
				$sql_sih ");
				oci_execute($sel);
				$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
				$patient_num=$r['PATIENT_NUM'];
				$method='inclusion nom-exact prenom et ddn';
	  		}	
			
	  		if ($patient_num=='') {
				$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
					(
						regexp_replace(upper( CONVERT(lastname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') or 
						regexp_replace(upper( CONVERT(maiden_name, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') 
					)
					and
					(
				instr(regexp_replace(upper( CONVERT(firstname, 'US7ASCII') ),'[^A-Z]','') ,regexp_replace(upper( CONVERT('$firstname_q', 'US7ASCII') ),'[^A-Z]',''))>0 or
				instr(regexp_replace(upper( CONVERT('$firstname_q', 'US7ASCII') ),'[^A-Z]','') ,regexp_replace(upper( CONVERT(firstname, 'US7ASCII') ),'[^A-Z]',''))>0
				)
				and
				lastname is not null and 
				firstname is not null and
				birth_date is not null and
				birth_date=to_date('$birth_date','DD/MM/YYYY')
				$sql_sih ");
				oci_execute($sel);
				$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
				$patient_num=$r['PATIENT_NUM'];
				$method='inclusion prenom-exact nom et ddn';
	  		}
	  		if ($patient_num=='') {
	  			$month_year=preg_replace("/^[0-9]+\/([0-9]+)\/([0-9]+)$/","$1/$2",$birth_date);
				$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
					(
					regexp_replace(upper( CONVERT(lastname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') or 
					regexp_replace(upper( CONVERT(maiden_name, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') 
					)
					and 
					regexp_replace(upper( CONVERT(firstname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$firstname_q', 'US7ASCII') ),'[^A-Z]','') and
					lastname is not null and 
					firstname is not null and
					birth_date is not null and
					to_char(birth_date,'MM/YYYY')='$month_year'
					$sql_sih ");
				oci_execute($sel);
				$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
				$patient_num=$r['PATIENT_NUM'];
				$method='Erreur jour de naissance';
			}
	  		if ($patient_num=='') {
	  			$day_year=preg_replace("/^([0-9]+)\/[0-9]+\/([0-9]+)$/","$1/$2",$birth_date);
				$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
					(
					regexp_replace(upper( CONVERT(lastname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') or 
					regexp_replace(upper( CONVERT(maiden_name, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') 
					)
					and 
					regexp_replace(upper( CONVERT(firstname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$firstname_q', 'US7ASCII') ),'[^A-Z]','') and
					lastname is not null and 
					firstname is not null and
					birth_date is not null and
					to_char(birth_date,'DD/YYYY')='$day_year'
					$sql_sih ");
				oci_execute($sel);
				$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
				$patient_num=$r['PATIENT_NUM'];
				$method='Erreur mois de naissance';
			}
	  		
	  		
	  		if ($patient_num=='') {
				$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
				(soundex(lastname) =soundex('$lastname_q') or soundex(maiden_name) =soundex('$lastname_q')) and 
				regexp_replace(upper( CONVERT(firstname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$firstname_q', 'US7ASCII') ),'[^A-Z]','') and
				lastname is not null and 
				firstname is not null and
				birth_date is not null and
				birth_date=to_date('$birth_date','DD/MM/YYYY')
				$sql_sih ");
				oci_execute($sel);
				$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
				$patient_num=$r['PATIENT_NUM'];
				$method='phonetique lastname-exact prenom et ddn';
	  		}
	  		
	  		if ($patient_num=='') {
				$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
				(
				regexp_replace(upper( CONVERT(lastname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') or 
				regexp_replace(upper( CONVERT(maiden_name, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') 
				)
				and
				soundex(firstname) =soundex('$firstname_q') and 
				lastname is not null and 
				firstname is not null and
				birth_date is not null and
				birth_date=to_date('$birth_date','DD/MM/YYYY')
				$sql_sih ");
				oci_execute($sel);
				$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
				$patient_num=$r['PATIENT_NUM'];
				$method='phonetique prenom-exact nom et ddn';
	  		}
	  		
	  		if ($patient_num=='') {
				$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
				(soundex(lastname) =soundex('$lastname_q') or soundex(maiden_name) =soundex('$lastname_q'))  
				and
				soundex(firstname) =soundex('$firstname_q') and 
				lastname is not null and 
				firstname is not null and
				birth_date is not null and
				birth_date=to_date('$birth_date','DD/MM/YYYY')
				$sql_sih ");
				oci_execute($sel);
				$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
				$patient_num=$r['PATIENT_NUM'];
				$method='phonetique lastname et prenom-exact ddn';
	  		}
	  		
	  		if ($patient_num=='') {
				$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
				(
					regexp_replace(upper( CONVERT(lastname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') or 
					regexp_replace(upper( CONVERT(maiden_name, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') 
				)
				and
				utl_match.jaro_winkler(regexp_replace(upper( CONVERT(firstname, 'US7ASCII') ),'[^A-Z]',''), regexp_replace(upper( CONVERT('$firstname_q', 'US7ASCII') ),'[^A-Z]',''))>'0,8'  
				and 
				lastname is not null and 
				firstname is not null and
				birth_date is not null and
				birth_date=to_date('$birth_date','DD/MM/YYYY')
				$sql_sih ");
				oci_execute($sel);
				$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
				$patient_num=$r['PATIENT_NUM'];
				$method='orthographe proche prenom-exact nom et ddn';
	  		}
	  		
	  		if ($patient_num=='') {
				$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
				(
					utl_match.jaro_winkler(regexp_replace(upper( CONVERT(lastname, 'US7ASCII') ),'[^A-Z]','') ,regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]',''))>'0,8' or 
					utl_match.jaro_winkler(regexp_replace(upper( CONVERT(maiden_name, 'US7ASCII') ),'[^A-Z]','') ,regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]',''))>'0,8' and maiden_name is not null
				)
				and
				regexp_replace(upper( CONVERT(firstname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$firstname_q', 'US7ASCII') ),'[^A-Z]','')  
				and 
				lastname is not null and 
				firstname is not null and
				birth_date is not null and
				birth_date=to_date('$birth_date','DD/MM/YYYY')
				$sql_sih ");
				oci_execute($sel);
				$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
				$patient_num=$r['PATIENT_NUM'];
				$method='orthographe proche nom-exact prenom et ddn';
	  		}
	  		if ($patient_num!='') {
		  		$autorisation_voir_patient=autorisation_voir_patient($patient_num,$user_num_session);
		  		if ($autorisation_voir_patient=='ok') {
					$patient=get_patient($patient_num);	
					$hospital_patient_id_sih=get_master_patient_id_sih($patient_num);
					if ($hospital_patient_id_sih!='') { 				
						print "<tr><td>$i</td>
						<td>$lastname</td>
						<td>$firstname</td>
						<td>$birth_date</td>
						<td>$method</td>
						<td>$hospital_patient_id_sih</td>
						<td>".$patient['LASTNAME']."</td>
						<td>".$patient['MAIDEN_NAME']."</td>
						<td>".$patient['FIRSTNAME']."</td>
						<td>".$patient['BIRTH_DATE']."</td>
						<td>".$patient['SEX']."</td>
						</tr>
						";
					} else {	
						print "<tr><td>$i</td>
						<td>$lastname</td>
						<td>$firstname</td>
						<td>$birth_date</td>
						<td>non trouve</td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						</tr>
						";
					}
									
		  		} else {				
					print "<tr><td>$i</td>
					<td>$lastname</td>
					<td>$firstname</td>
					<td>$birth_date</td>
					<td>non autorise</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					</tr>
					";
		  		}
		  		
		  	} else {			
				print "<tr><td>$i</td>
					<td>$lastname</td>
					<td>$firstname</td>
					<td>$birth_date</td>
					<td>non trouve</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					</tr>
				";
		  	}
		} else {	
			print "<tr><td>$i</td>
				<td>$lastname</td>
				<td>$firstname</td>
				<td>$birth_date</td>
				<td>incomplet</td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				</tr>
			";
		
		}
  	}	
  	print "</table></div>";
	save_log_page($user_num_session,"mapper_patient");
}

?>