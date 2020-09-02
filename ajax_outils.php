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

if ($_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_login']=='') {
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
	$option_patient_num=$_POST['option_patient_num'];
  	update_process ($process_num,"0","traitement en cours","$liste_patient",$user_num_session,"");
	passthru( "php exec_mapping_patient.php \"$user_num_session\" \"$process_num\" \"$option_limite\" \"$option_patient_num\"> $CHEMIN_GLOBAL_LOG/log_mapper_patient_$process_num.txt 2>$CHEMIN_GLOBAL_LOG/log_mapper_patient_$process_num.txt &");
	save_log_page($user_num_session,"mapper_patient");
}

if ($_POST['action']=='display_mapper_patient' ) {
	$i=0;
	$process_num=$_POST['process_num'];
	$process=get_process ($process_num,'get_result');
	$status=$process['STATUS'];	
	$resultat_mapping=$process['RESULT'];	
	if ($status==1) {
		print "<a href=\"export_process.php?process_num=$process_num\">Telecharger sur excel</a><div id=\"id_copier_coller_mapping\"><br>";
		print $resultat_mapping;
		print "</div>";
	}
}






if ($_POST['action']=='display_thesaurus_table' ) {
	$data_search=supprimer_apost(trim(urldecode($_POST['data_search'])));
	$thesaurus_code=$_POST['thesaurus_code'];	
	if ($data_search!='' || $thesaurus_code!='') {
		$thesaurus_data_concept=get_thesaurus_data_concept ($data_search,$thesaurus_code,'');
		print "<table class=tablefin id=\"id_table_list_thesaurus_data\">
			<thead>
				<tr>
					<td>Data Thesaurus num</td>
					<td>Thesaurus</td>
					<td>Concept code</td>
					<td>Label</td>
					<td>Info complementaire</td>
					<td>Unit</td>
					<td>Value type</td>
					<td>List values</td>
					<td>Description</td>
					<td>Code Parent</td>
					<td>Count data used</td>
					<td>Min date used</td>
					<td>Max date used</td>
				</tr>
			</thead>
			<tbody>";
		foreach ($thesaurus_data_concept as $r) {
			$thesaurus_data_num=$r['THESAURUS_DATA_NUM'];
			$thesaurus_code=$r['THESAURUS_CODE'];
			$concept_code=$r['CONCEPT_CODE'];
			$concept_str=$r['CONCEPT_STR'];
			$info_complement=$r['INFO_COMPLEMENT'];
			$measuring_unit=$r['MEASURING_UNIT'];
			$value_type=$r['VALUE_TYPE'];
			$list_values=$r['LIST_VALUES'];
			$thesaurus_parent_num=$r['THESAURUS_PARENT_NUM'];
			$description=$r['DESCRIPTION'];
			$count_data_used=$r['COUNT_DATA_USED'];
			//list($min_date,$max_date)=get_min_max_date_used_data($thesaurus_data_num);
			print "<tr onmouseout=\"this.style.backgroundColor='#ffffff';\" onmouseover=\"this.style.backgroundColor='#dcdff5';\">
				<td>$thesaurus_data_num</td>
				<td>$thesaurus_code</td>
				<td>$concept_code</td>
				<td>$concept_str</td>
				<td>$info_complement</td>
				<td>$measuring_unit</td>
				<td>$value_type</td>
				<td>$list_values</td>
				<td>$description</td>
				<td>$thesaurus_parent_num</td>
				<td>$count_data_used</td>
				<td>$min_date</td>
				<td>$max_date</td>
				</tr>";
		}
		
		print "</tbody></table>";
	}
}




if ($_POST['action']=='display_thesaurus_tree') {
	$data_search=supprimer_apost(trim(urldecode($_POST['data_search'])));
	$thesaurus_code=$_POST['thesaurus_code'];
	$thesaurus_data_num=$_POST['thesaurus_data_num'];
	if ($data_search!='' || $thesaurus_code!='') {
		$thesaurus_data_concept=get_thesaurus_data_concept ($data_search,$thesaurus_code,$thesaurus_data_num);
		foreach ($thesaurus_data_concept as $r) {
			$thesaurus_data_num=$r['THESAURUS_DATA_NUM'];
			$thesaurus_code=$r['THESAURUS_CODE'];
			$concept_code=$r['CONCEPT_CODE'];
			$concept_str=$r['CONCEPT_STR'];
			$info_complement=$r['INFO_COMPLEMENT'];
			$measuring_unit=$r['MEASURING_UNIT'];
			$value_type=$r['VALUE_TYPE'];
			$list_values=$r['LIST_VALUES'];
			$thesaurus_parent_num=$r['THESAURUS_PARENT_NUM'];
			$description=$r['DESCRIPTION'];
			$count_data_used=$r['COUNT_DATA_USED'];
			$test_son=get_thesaurus_data_concept ($data_search,$thesaurus_code,$thesaurus_data_num);
			if (count($test_son)==0) {
				print "<div id=\"id_span_thesaurus_$thesaurus_data_num\">- [$concept_code] $concept_str - $info_complement $measuring_unit - $value_type - $list_values ($count_data_used values)</div>";
			} else {
				print "<div id=\"id_span_thesaurus_$thesaurus_data_num\" style=\"cursor:pointer;\" onclick=\"display_thesaurus_tree($thesaurus_data_num);\"><span id=\"plus_id_span_thesaurus_$thesaurus_data_num\">+</span> [$concept_code] $concept_str</div>";
				print "<div id=\"id_span_thesaurus_son_$thesaurus_data_num\" style=\"padding-left:20px;display:none;\"></div>";
			}
		}
		print "<br>";
	}
}


if ($_POST['action']=='get_concept') {
	$concepts=array();
	$concepts['items']=array();

	$text=trim(replace_accent(utf8_decode($_POST['q'])));
	$text=preg_replace("/\s+/"," ",$text);
	$text=nettoyer_pour_requete(trim($text));
	$text=trim(preg_replace("/([a-z])\s/i","$1% ","$text "));
	$text=preg_replace("/([^\s])\s+([^\s])/","$1 and $2",$text);

	$query="select  concept_code,concept_str ,THESAURUS_ENRSEM_NUM,length(concept_str) from dwh_thesaurus_enrsem where THESAURUS_ENRSEM_NUM in (select THESAURUS_ENRSEM_NUM from dwh_thesaurus_enrsem where contains(concept_str,'$text')>0) 
	 order by length(concept_str) asc ";	
	$sel=oci_parse($dbh,$query);
	oci_execute($sel);
	while($r=oci_fetch_array($sel, OCI_RETURN_NULLS+OCI_ASSOC)){
		$concept_str=utf8_encode($r['CONCEPT_STR']);
		$concept_code=$r['CONCEPT_CODE'];
		$thesaurus_enrsem_num=$r['THESAURUS_ENRSEM_NUM'];
		$string="$concept_str ($concept_code)";
		array_push($concepts['items'],array('id'=>$concept_code, 'text'=>$string));
	}
	print json_encode($concepts);	
}

if ($_POST['action']=='get_concept_info') {
	$concept_code=trim($_POST['concept_code']);
	$concept_str=get_concept_str($concept_code,'');
	print "<span id='id_span_concept_$concept_code'><span class=\"list_concept_code_used\" style=\"display:none\">$concept_code</span>$concept_str <input type=\"text\" id=\"id_weight_$concept_code\" size=\"3\" value=\"1\"> <a href='#' onclick=\"delete_concept_similarity('$concept_code');return false;\">x</a><br></span>";
}


if ($_POST['action']=='get_list_concept_virtual_patient') {
	$virtual_patient_num=trim($_POST['virtual_patient_num']);
	$tab_concept=get_virtual_patient_concept ($virtual_patient_num);
	foreach ($tab_concept as $concept) {
		$concept_code=$concept['concept_code'];
		$concept_weight=$concept['concept_weight'];
		$concept_str=get_concept_str($concept_code,'');
		print "<span id='id_span_concept_$concept_code'><span class=\"list_concept_code_used\" style=\"display:none\">$concept_code</span>$concept_str <input type=\"text\" id=\"id_weight_$concept_code\" size=\"3\" value=\"$concept_weight\"> <a href='#' onclick=\"delete_concept_similarity('$concept_code');return false;\">x</a><br></span>";
	}
}


if ($_POST['action']=='save_name_virtual_patient') {
	$list_code_concept=trim($_POST['list_code_concept']);
	$tab_concept=explode(";",$list_code_concept);
	$list_code_concept_weight=trim($_POST['list_code_concept_weight']);
	$tab_concept_weight=explode(";",$list_code_concept_weight);
	$name_virtual_patient=supprimer_apost(trim(urldecode($_POST['name_virtual_patient'])));
	$description=supprimer_apost(trim(urldecode($_POST['description'])));
	$virtual_patient_num=get_uniqid();
	$query="insert into DWH_VIRTUAL_PATIENT (user_num, virtual_patient_num,  patient_name, date_creation, description) values ($user_num_session, $virtual_patient_num,  '$name_virtual_patient', sysdate, '$description') ";	
	$ins=oci_parse($dbh,$query);
	oci_execute($ins);
	
	$j=0;
	$json_concepts="";
	foreach ($tab_concept as $concept_code) {
		if ($concept_code!='') {
			$concept_weight=$tab_concept_weight[$j];
			$j++;
			$json_concepts.="{\"concept_code\":\"$concept_code\",\"concept_weight\":\"$concept_weight\"},";
			#$query="insert into DWH_VIRTUAL_PATIENT_CONCEPT ( virtual_patient_num,  concept_code, weight ) values ($virtual_patient_num, '$concept_code',  '$weight') ";	
			#$ins=oci_parse($dbh,$query);
			#oci_execute($ins);
		}
		
	}
	$json_concepts=substr($json_concepts,0,-1);
	$patient_record="{\"phenotypes\":[$json_concepts]}";
        $requeteupd="update DWH_VIRTUAL_PATIENT set  patient_record=:patient_record where virtual_patient_num=$virtual_patient_num  ";
        $upd = ociparse($dbh,$requeteupd);
        $rowid = ocinewdescriptor($dbh, OCI_D_LOB);
        ocibindbyname($upd, ":patient_record",$patient_record);
        $execState = ociexecute($upd);
        ocifreestatement($upd);
	
	
}

if ($_POST['action']=='manage_virtual_patient') {
	$tab_virtual_patient=get_virtual_patient ($user_num_session,'','','');
	print "<table class=tablefin><thead>
	<th>".get_translation('NAME','Nom')."</th>
	<th>".get_translation('DESCRIPTION','Description')."</th>
	<th>".get_translation('DATE_CREATION','Date')."</th>
	<th>".get_translation('CONCEPTS','Concepts')."</th>
	<th>".get_translation('DEL','Suppr')."</th>
	<th>".get_translation('SHARED','Partager')."</th>
	</thead>
	<tbody>";
	foreach ($tab_virtual_patient as $virtual_patient) {
		$virtual_patient_num=$virtual_patient['VIRTUAL_PATIENT_NUM'];
		$patient_name=$virtual_patient['PATIENT_NAME'];
		$description=$virtual_patient['DESCRIPTION'];
		$date_creation_char=$virtual_patient['DATE_CREATION_CHAR'];
		$shared=$virtual_patient['SHARED'];
		
		$tab_concept=get_virtual_patient_concept ($virtual_patient_num);
		print "<tr id=\"id_tr_virtual_patient_$virtual_patient_num\">
			<td>$patient_name</td>
			<td>$description</td>
			<td>$date_creation_char</td>
			<td>";
		foreach ($tab_concept as $concept) {
			$concept_code=$concept['concept_code'];
			$concept_weight=$concept['concept_weight'];
			$concept_str=get_concept_str($concept_code,'');
			print "$concept_str ($concept_weight)<br>";
		}
		print "</td><td><img src=\"images/poubelle_moyenne.png\" onclick=\"delete_virtual_patient('$virtual_patient_num');\" style=\"cursor:pointer\"></td>";
		if ($shared==1) {
			 $checked='checked';
		} else {
			 $checked='';
		}
		print "<td><input type=\"checkbox\" id=\"id_checkbox_shared_vp_$virtual_patient_num\" onclick=\"share_virtual_patient('$virtual_patient_num');\" $checked></td>";
		print "</tr>";
	}
	print "</tbody></table>";
}

if ($_POST['action']=='delete_virtual_patient') {
	$virtual_patient_num=trim($_POST['virtual_patient_num']);
	delete_virtual_patient ($virtual_patient_num,$user_num_session);
}


if ($_POST['action']=='get_list_patient_in_select') {
	$tab_virtual_patient=get_virtual_patient ($user_num_session,'','','');
	print "<option value=\"\"></option>";
	print "<optgroup label=\"".get_translation("MY_VIRTUAL_PATIENTS","Mes patients virtuels")."\">";
	foreach ($tab_virtual_patient as $virtual_patient) {
		$virtual_patient_num=$virtual_patient['VIRTUAL_PATIENT_NUM'];
		$patient_name=$virtual_patient['PATIENT_NAME'];
		print "<option value=\"$virtual_patient_num\">$patient_name</option>";
	}
	print "</optgroup>";
	print "<optgroup label=\"".get_translation("SHARED_VIRTUAL_PATIENTS","Les patients virtuels partagés")."\">";
	$tab_virtual_patient_shared=get_virtual_patient ($user_num_session,'','',1);
	print "<subgroup ></subroup>";
	foreach ($tab_virtual_patient_shared as $virtual_patient) {
		$virtual_patient_num=$virtual_patient['VIRTUAL_PATIENT_NUM'];
		$patient_name=$virtual_patient['PATIENT_NAME'];
		print "<option value=\"$virtual_patient_num\">$patient_name</option>";
	}
}

if ($_POST['action']=='share_virtual_patient') {
	$virtual_patient_num=trim($_POST['virtual_patient_num']);
	$shared=trim($_POST['shared']);
	share_virtual_patient ($virtual_patient_num,$user_num_session,$shared);
}



oci_close ($dbh);
oci_close ($dbh_etl);
?>