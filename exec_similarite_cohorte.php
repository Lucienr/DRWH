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

ini_set("memory_limit","400M");
putenv("NLS_LANG=French");

include_once "parametrage.php";
include_once "connexion_bdd.php";
include_once("ldap.php");
include_once("fonctions_droit.php");
include_once("fonctions_dwh.php");
include_once("similarite_fonction.php");

$cohort_num=$argv[1];
$process_num=$argv[2];
$limite_nb_patient_principal=$argv[3];
$limite_count_concept_par_patient_num=$argv[4];
$cohort_nums_exclues=$argv[5];
$patients_importes=$argv[6];

if ($patients_importes==1) {
	$limite_nb_patient_principal=100000000;
	$distance=10;
	$limite_longueur_vecteur=0.1;
	$limite_min_nb_patient_par_code=1; 
} else {

	#$limite_nb_patient_principal=30;
	#$limite_count_concept_par_patient_num=8;
	$distance=10;
	$limite_longueur_vecteur=0.1;
	$limite_min_nb_patient_par_code=3; 
}
$reduire_entrepot_sous_population='non';
$espace_vectoriel='index';
$negation='non';
$tfidf='oui';
$codes_exclus='';
$anonyme='non';
$nbpatient_limite=40;

if ($codes_exclus!='.' && $codes_exclus!='') {
	$tableau_codes_exclus=preg_split("/[^a-z0-9]/i",$codes_exclus);
	$req_codes_exclus='';
	foreach ($tableau_codes_exclus as $concept_code) {
		if ($concept_code!='') {
			$req_codes_exclus.="and  concept_code!='$concept_code'  ";
		}
	}
}

if ($negation=='oui') {
	$req_certitude="and (certainty=1 or certainty=-1) ";
} else {
	$req_certitude="and certainty=1  ";
}
$req_contexte="and context='patient_text' ";

$requete_exclude_cohorte='';
if ($cohort_nums_exclues!='') {
	$tab_cohort_nums_exclues=array_filter(preg_split("/[^0-9]/",$cohort_nums_exclues));
	$liste_cohort_num=implode(",",$tab_cohort_nums_exclues);
	$requete_exclude_cohorte=" and patient_num not in (select patient_num from dwh_cohort_result where cohort_num in ($liste_cohort_num) and status=1) ";
}
$requete_include_cohorte='';
if ($patients_importes!='') {
	$requete_include_cohorte=" and patient_num  in (select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=3) ";
}


$tableau_process=get_process ($process_num) ;

$user_num_session=$tableau_process['USER_NUM'];

update_process ($process_num,'0',get_translation('PROCESS_START','debut du process'),'');



if ($cohort_num!='') {
		
	$requete="drop table dwh_temp_$process_num    ";
	$sel_patient=oci_parse($dbh, $requete);
	oci_execute($sel_patient);

	$requete="create table dwh_temp_$process_num as  select  concept_code,patient_num,certainty,count(*) as TF from dwh_enrsem
	     where patient_num in (select patient_num from dwh_patient_stat where count_unique_concept_nonneg >= $limite_count_concept_par_patient_num)
	     $req_contexte $req_certitude and concept_code in (select concept_code from dwh_enrsem where patient_num in ( select patient_num from dwh_cohort_result where cohort_num=$cohort_num) $req_contexte $req_certitude) 
	        and concept_code!='C0012634'  and concept_code!='C0012155' and concept_code!='C0039082'
          	and concept_str_found not like '% norma%'
	     	and concept_code in (select concept_code from dwh_thesaurus_enrsem where phenotype =1)
	     	$requete_exclude_cohorte
	     	$requete_include_cohorte
	     group by  concept_code, patient_num,certainty
	      ";
	$sel_patient=oci_parse($dbh, $requete);
	oci_execute($sel_patient);
	
	$requete="insert into   dwh_temp_$process_num   select  concept_code,patient_num,certainty,count(*) as TF from dwh_enrsem
	     where patient_num in ( select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status in (0,1))
	     	and patient_num not in ( select patient_num from dwh_temp_$process_num)
	       $req_contexte $req_certitude and concept_code!='C0012634'  and concept_code!='C0012155' and concept_code!='C0039082'
          	and concept_str_found not like '% norma%'
	     	and concept_code in (select concept_code from dwh_thesaurus_enrsem where phenotype =1)
	     group by  concept_code, patient_num,certainty";
	$sel_patient=oci_parse($dbh, $requete);
	oci_execute($sel_patient);
	
	

	$requete=" create index dwh_temp_".$process_num."_i on dwh_temp_$process_num (concept_code) tablespace idx  ";
	$sel_patient=oci_parse($dbh, $requete);
	oci_execute($sel_patient);
	
	$nb_patient_principal=0;
	$requete_patient=" select patient_num from dwh_cohort_result where status=1 and cohort_num=$cohort_num and patient_num in (select patient_num from dwh_patient_stat where count_unique_concept_nonneg >= $limite_count_concept_par_patient_num)";
	$sel_patient=oci_parse($dbh, $requete_patient);
	oci_execute($sel_patient);
	while ($r_patient=oci_fetch_array($sel_patient,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$patient_num_principal=$r_patient['PATIENT_NUM'];

		if ($nb_patient_principal<=$limite_nb_patient_principal) {
			$tableau_code_autorise=array();
			print "patient_num_principal : $patient_num_principal\n";
			$nb_concept_distinct_non_negatif=0;
			if ($distance==10) {
				$requete="select  concept_code, certainty,count(*) as TF from dwh_enrsem where 
						patient_num='$patient_num_principal'
						$req_contexte
						$req_certitude
						$req_codes_exclus
				          	and concept_code!='C0012634'  and concept_code!='C0012155' and concept_code!='C0039082'
				          	and concept_str_found not like '% norma%'
						and concept_code in (select concept_code from dwh_thesaurus_enrsem where phenotype=1)
						   group by  concept_code,certainty";
			} else {
#				$requete=" select  concept_code, certainty,count(*) as TF 
#					from (
#						select  enrsem_num,a.concept_code_son ,patient_num,certainty from dwh_thesaurus_enrsem_graph a, dwh_thesaurus_enrsem_graph b, dwh_enrsem c
#						where a.concept_code_father='RACINE'  and
#						patient_num =$patient_num_principal and 
#						a.distance=$distance and
#						a.concept_code_son=b.concept_code_father and
#						b.concept_code_son=c.concept_code 
#						$req_certitude
#						$req_contexte
#						$req_codes_exclus
#						union
#						select  enrsem_num,concept_code_son  ,patient_num,certainty from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
#						where a.concept_code_father='RACINE'   and
#						patient_num =$patient_num_principal and 
#						a.distance<=$distance and
#						a.concept_code_son=c.concept_code 
#						$req_certitude
#						$req_contexte
#						$req_codes_exclus
#						) t,
#					dwh_thesaurus_enrsem
#					where 
#						concept_code_son=concept_code   and concept_code!='C0012634'  and concept_code!='C0012155' and concept_code!='C0039082'
#						 and phenotype =1
#					group by  concept_code, certainty
#					order by concept_code
#				      ";
			}
			$sel=oci_parse($dbh, $requete);
			oci_execute($sel);
			while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
				$concept_code=$r['CONCEPT_CODE'];
				$tf=$r['TF'];
				$certainty=$r['CERTAINTY'];
				if ($negation=='oui' && $certainty==-1) {
					$concept_code="NEGATION_$concept_code";
				}
				$tableau_code_autorise[$concept_code]=ok;
				$tableau_patient_num_code_tf[$concept_code]=$tf;
				if ($certainty==1) {
					$nb_concept_distinct_non_negatif++; 
				} 
			}
			
			if ($nb_concept_distinct_non_negatif>=$limite_count_concept_par_patient_num) {
				$filtre_sql=" ";
				print "calcul_similarite_tfidf_simplifie : \n";
				$nb_patient_principal++;
				$tableau_similarite_patient_num_principal[$patient_num_principal]=calcul_similarite_tfidf_simplifie ($cohort_num,$process_num,$patient_num_principal,$distance,$limite_count_concept_par_patient_num,$limite_longueur_vecteur,$limite_min_nb_patient_par_code,$filtre_sql);
			}
		}
	}
	$drop=oci_parse($dbh, "drop table dwh_temp_$process_num ");
	oci_execute($drop);	

	$tableau_intersection=array();
	$nb_max_patient_commun=0;
	foreach ($tableau_similarite_patient_num_principal as $patient_num_principal => $tableau_similaire) {
		print "patient_num_principal : $patient_num_principal\n";
		print_r ($tableau_similaire);
		
		foreach ($tableau_similaire as $patient_num => $similarite) {
			print " tableau_intersection[$patient_num]++;\n";
			$tableau_intersection[$patient_num]++;
			$tableau_intersection_similarite[$patient_num]+=$similarite;
			if ($nb_max_patient_commun<$tableau_intersection[$patient_num]) {
				$nb_max_patient_commun=$tableau_intersection[$patient_num];
			}
		}
	}
	foreach ($tableau_intersection as $patient_num => $nb) {
		$tableau_intersection_similarite[$patient_num]=$tableau_intersection_similarite[$patient_num]/$nb;
	}
	
	arsort($tableau_intersection);
	$liste_patient_num='';
	foreach ($tableau_intersection as $patient_num => $nb) {
		$similarite=round($tableau_intersection_similarite[$patient_num]);
		$liste_patient_num.="$patient_num,$nb,$similarite;";
	}
	$liste_patient_num=substr($liste_patient_num,0,-1);
	
	update_process ($process_num,'1',get_translation('PROCESS_END','process fini'),$liste_patient_num);
}


function calcul_similarite_tfidf_simplifie ($cohort_num,$process_num,$patient_num_principal,$distance,$limite_count_concept_par_patient_num,$limite_longueur_vecteur,$limite_min_nb_patient_par_code,$filtre_sql) {
	global $dbh,$tableau_code_autorise,$user_num_session;
	global $req_certitude,$req_contexte,$nbpatient_limite,$cohort_num_exclure,$nb_patient_principal;
	
	$sel=oci_parse($dbh, "select count_patient from dwh_info_load where year is null and document_origin_code is null");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS);
	$nb_pat_total_entrepot=$r['COUNT_PATIENT'];
	
	$memory_get_usage=memory_get_usage();
	print "debut  calcul_similarite_tfidf_simplifie memory_get_usage $memory_get_usage\n";
	
	print "\n\n".benchmark ( 'debut 1' )."\n\n";
	$tableau_code_nb_pat=array();
	$tableau_patient_num_code_nb_concept=array();
	$tableau_patient_num_nb_concept_total=array();
	$tableau_patient_num_nb_concept_distinct_non_negatif=array();
	
	update_process ($process_num,'0',get_translation('PATIENT','patient')."  $nb_patient_principal - ".get_translation('PROCESS_EXTRACTION_CONCEPTS_FROM_ALL_PATIENTS','Extraction tous les patients et concepts'),'');
	
	$requete="  select  count( *) nb_a_traiter from dwh_temp_$process_num  where concept_code in (select concept_code from dwh_enrsem where patient_num=$patient_num_principal   )
		     	and (patient_num not in (select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status in (0,1) ) or patient_num=$patient_num_principal )";
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS);
	$nb_a_traiter=$r['NB_A_TRAITER'];
		
	
	$i=0;
	// pour le calcul du TF/IDF
	$requete="  select  concept_code,patient_num,certainty, TF from dwh_temp_$process_num  where concept_code in (select concept_code from dwh_enrsem where patient_num=$patient_num_principal   )
		     	and (patient_num not in (select patient_num from dwh_cohort_result where cohort_num=$cohort_num  and status in (0,1)) or patient_num=$patient_num_principal )";
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$concept_code=$r['CONCEPT_CODE'];
		$patient_num=$r['PATIENT_NUM'];
		$tf=$r['TF'];
		$certainty=$r['CERTAINTY'];
		if ($negation=='oui' && $certainty==-1) {
			$concept_code="NEGATION_$concept_code";
		}
		if ($tableau_code_autorise[$concept_code]!='') {
			$tableau_code_nb_pat[$concept_code]++;
			$tableau_patient_num_code_nb_concept[$patient_num][$concept_code]=$tf;
			$tableau_patient_num_nb_concept_total[$patient_num]+=$tf;
			$tableau_patient_num[$patient_num]='ok';
			if ($certainty==1) {
				$tableau_patient_num_nb_concept_distinct_non_negatif[$patient_num]++; 
			} 
		}
		$i++;
		if ($i % 1000==0) {
			update_process ($process_num,'0',get_translation('PATIENT','patient')."  $nb_patient_principal - ".get_translation('PROCESS_EXTRACTION_CONCEPTS_FROM_ALL_PATIENTS','Extraction tous les patients et concepts')." : $i / $nb_a_traiter",'');
		}
	}
	$nb_pat_total=count($tableau_patient_num);
	print "nb_pat_total : $nb_pat_total<br>";
	print "\n\n".benchmark ( 'debut 2' )."\n\n";
	
	print "tableau_code_nb_pat : ".count($tableau_code_nb_pat)."<br>";
	$memory_get_usage=memory_get_usage();
	
	$tableau_longueur_vecteur=array();
	
	print "avant unset memory_get_usage $memory_get_usage\n";
	foreach ($tableau_patient_num as $patient_num => $ok) {
		if ($tableau_patient_num_nb_concept_distinct_non_negatif[$patient_num] < $limite_count_concept_par_patient_num && $patient_num!=$patient_num_principal) {
			unset($tableau_patient_num_code_nb_concept[$patient_num]);
			unset($tableau_patient_num_nb_concept_total[$patient_num]);
			unset($tableau_patient_num[$patient_num]);
			unset($tableau_patient_num_nb_concept_distinct_non_negatif[$patient_num]);
		}
	}	
	$memory_get_usage=memory_get_usage();
	print "apres unset memory_get_usage $memory_get_usage\n";
	
	update_process ($process_num,'0',get_translation('PATIENT','patient')." $nb_patient_principal - ".get_translation('PROCESS_PATIENTS_VECTORISATION','Vectorisation des patients'),'');

	///creation des vecteurs et calcul des longueurs de vecteur 
	foreach ($tableau_patient_num as $patient_num => $ok) {
		//if ($tableau_patient_num_nb_concept_distinct_non_negatif[$patient_num]  >= $limite_count_concept_par_patient_num || $patient_num==$patient_num_principal) { 
			$i=0;
			$longueur_vecteur=0;
			foreach ($tableau_code_nb_pat as $concept_code => $nb_pat) {
				if ($tableau_patient_num_code_nb_concept[$patient_num][$concept_code]!='') {
					$tf=$tableau_patient_num_code_nb_concept[$patient_num][$concept_code]/$tableau_patient_num_nb_concept_total[$patient_num];
					$idf=log($nb_pat_total/$tableau_code_nb_pat[$concept_code]);
					$tableau_patient_num_vecteur["$patient_num;$i"]=round($tf*$idf,3);
				} else {
					$tf=0;
					$idf=0;
				}
				$longueur_vecteur+=$tableau_patient_num_vecteur["$patient_num;$i"]*$tableau_patient_num_vecteur["$patient_num;$i"];
				$i++;
			}
			if ($longueur_vecteur>=$limite_longueur_vecteur || $patient_num==$patient_num_principal) {
				$tableau_longueur_vecteur[$patient_num]=round(sqrt($longueur_vecteur),3);
				
			} else {
				$i=0;
				foreach ($tableau_code_nb_pat as $concept_code => $nb_pat) {
					unset($tableau_patient_num_vecteur["$patient_num;$i"]);
					unset($tableau_patient_num_code_nb_concept[$patient_num]);
					$i++;
				}
			}
		//} 
	}
	print "\n\n".benchmark ( 'debut 3' )."\n\n";

	print "tableau_longueur_vecteur nb patient_num: ".count($tableau_longueur_vecteur)."<br>"; 
	$tableau_similarite_patient_num_principal=array();
	update_process ($process_num,'0',get_translation('PATIENT','PATIENT')." $nb_patient_principal - ".get_translation('PROCESS_COMPUTE_SIMILARITY_INDEX_PATIENT','Calcul de similarite avec le patient Index'),'');
	
	foreach ($tableau_longueur_vecteur as $patient_num_2 => $longeur_v_2) {
		if ($patient_num_principal!=$patient_num_2) {
		
			/// similarite par le cosinus µ///
			$produit_scalaire=0;
			for ($i=0;$i<count($tableau_code_nb_pat);$i++) {
				$poids_patient_num_2=$tableau_patient_num_vecteur["$patient_num_2;$i"];
				if ($poids_patient_num_2=='') {
					$poids_patient_num_2=0;
				}
				$poids_patient_num_principal=$tableau_patient_num_vecteur["$patient_num_principal;$i"];
				if ($poids_patient_num_principal=='') {
					$poids_patient_num_principal=0;
				}
				$produit_scalaire+=$poids_patient_num_principal*$poids_patient_num_2;
			}
			$produit_longueur=$tableau_longueur_vecteur[$patient_num_principal]*$longeur_v_2;
			$similarite=0;
			if ($produit_longueur>0) {
				$similarite=round(100*$produit_scalaire/$produit_longueur);
			}
			if ($tableau_similarite["$patient_num_principal;$patient_num_2"]=='' && $tableau_similarite["$patient_num_2;$patient_num_principal"]=='') {
				$tableau_similarite["$patient_num_principal;$patient_num_2"]=$similarite;
				$tableau_similarite["$patient_num_2;$patient_num_principal"]=$similarite;
				$tableau_similarite_patient_num_principal[$patient_num_2]=$similarite;
				
			}
		}
	}
	$i=0;
	arsort($tableau_similarite_patient_num_principal);
	$tableau_final=array();
	foreach ($tableau_similarite_patient_num_principal as $patient_num => $similarite) {
		if ($i<=$nbpatient_limite) {
			$tableau_final[$patient_num]=$similarite;
		}
		$i++;
	}
	print "\n\n".benchmark ( 'debut 4' )."\n\n";
	
	return $tableau_final;
}
?>
	