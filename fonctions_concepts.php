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




function repartition_concepts_general_json ($tmpresult_num,$phenotype_genotype,$tableau,$graph,$cloud,$donnees_reelles_ou_pref,$type,$distance,$age_concept_min,$age_concept_max) {
	global $dbh,$datamart_num,$user_num_session;
	$age_concept_min=str_replace(".",",",trim($age_concept_min));
	$age_concept_max=str_replace(".",",",trim($age_concept_max));
	
	if ($age_concept_min!='' && $age_concept_max!='') {
		$query_age=" and age_patient>='$age_concept_min' and age_patient<='$age_concept_max' ";
	
		// number of patients with this age in the data warehouse
		$requete=" select count (distinct patient_num) as NB_PATIENT from dwh_enrsem where  age_patient>='$age_concept_min' and age_patient<='$age_concept_max' ";
		$sel=oci_parse($dbh, $requete);
		oci_execute($sel);
		$r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS);
		$nb_patient_total=$r['NB_PATIENT'];
		
		// number of patients with this age in the result
		$requete=" select count (distinct patient_num) as NB_PATIENT from dwh_enrsem where age_patient>='$age_concept_min' and age_patient<='$age_concept_max' and patient_num in (select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num)";
		$sel=oci_parse($dbh, $requete);
		oci_execute($sel);
		$r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS);
		$nb_patient_dans_resultat=$r['NB_PATIENT'];
	}else {
		$age_concept_min='';
		$age_concept_max='';
		$query_age='';
		$requete=" select count_distinct_patient as NB_PATIENT from dwh_info_enrsem";
		$sel=oci_parse($dbh, $requete);
		oci_execute($sel);
		$r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS);
		$nb_patient_total=$r['NB_PATIENT'];
		
		$requete=" select count(distinct patient_num) as NB_PATIENT from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num ";
		$sel=oci_parse($dbh, $requete);
		oci_execute($sel);
		$r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS);
		$nb_patient_dans_resultat=$r['NB_PATIENT'];
	}
	
	$json_tableau='';
	$json_cloud='';
	$json_graph='';
	$i=0;
	$nb_c=0;
	
	if ($phenotype_genotype!='') {
		$filtre_phenotype_genotype="and $phenotype_genotype=1";
	}
	if ($type=='document') {
		$requete=" select sum(count_concept_str_found) as nbcode_non_distinct_dans_res from dwh_enrsem where document_num in (select document_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and certainty=1 and context='patient_text' $query_age";
		$sel=oci_parse($dbh, $requete);
		oci_execute($sel) ||die ("$requete");
		$r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS);
		$nbcode_non_distinct_dans_res=$r['NBCODE_NON_DISTINCT_DANS_RES'];
		
		if ($donnees_reelles_ou_pref=='reelles') {
			$requete=" select 
						dwh_enrsem.concept_code,
						concept_str_found as concept_str,
						count(distinct dwh_tmp_result_$user_num_session.patient_num) nb, 
						count_patient_subsumption as nb_patient_concept_global, 
						sum(count_concept_str_found) as nbfois_ce_code_dans_le_res,
						median(AGE_PATIENT) as median_AGE_PATIENT
			 from dwh_tmp_result_$user_num_session, dwh_enrsem,dwh_thesaurus_enrsem
			 where tmpresult_num=$tmpresult_num
			 and dwh_tmp_result_$user_num_session.document_num=dwh_enrsem.document_num
			 and context='patient_text'
			 and certainty=1
			 and dwh_enrsem.concept_code=dwh_thesaurus_enrsem.concept_code
			 $filtre_phenotype_genotype
			 $query_age
			 group by dwh_enrsem.concept_code,concept_str_found,count_patient_subsumption
			 order by count(distinct dwh_tmp_result_$user_num_session.patient_num) desc
			  ";
		} else {
			if ($distance==10) {
				$requete="select  
						dwh_thesaurus_enrsem.concept_code, 
						concept_str,
						count(distinct patient_num) as nb, 
						count_patient_subsumption as nb_patient_concept_global, 
						sum(count_concept_str_found) as nbfois_ce_code_dans_le_res ,
						median(AGE_PATIENT) as median_AGE_PATIENT
					from 
					dwh_enrsem , dwh_thesaurus_enrsem
					where dwh_enrsem.concept_code=dwh_thesaurus_enrsem.concept_code and pref='Y'
				 	$filtre_phenotype_genotype
					and document_num in ( select document_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and 
					context='patient_text' and
					certainty=1 
					$query_age
					group by dwh_thesaurus_enrsem.concept_code, concept_str,count_patient_subsumption
					order by count(distinct patient_num)  desc";
			} else {
				// a revoir pour prendre en compte les fils // 
				$requete=" select  
						 concept_code, 
						 concept_str,
						 count(distinct patient_num) as nb, 
						 count_patient_subsumption as nb_patient_concept_global, 
						 sum(count_concept_str_found) as nbfois_ce_code_dans_le_res ,
						median(AGE_PATIENT) as median_AGE_PATIENT
				 	from (
					select a.concept_code_son ,patient_num, count_concept_str_found,AGE_PATIENT 
						from dwh_thesaurus_enrsem_graph a, dwh_thesaurus_enrsem_graph b, dwh_enrsem c
					     where a.concept_code_father='RACINE'  and
					   document_num in ( select document_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num ) and 
					         a.distance=$distance and
					   a.concept_code_son=b.concept_code_father and
					     b.concept_code_son=c.concept_code 
					     UNION ALL
					select concept_code_son  ,patient_num, count_concept_str_found ,AGE_PATIENT 
					from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
					     where a.concept_code_father='RACINE'   and
					   document_num in ( select document_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and 
					         a.distance<=$distance and
					      context='patient_text' and
					      certainty=1 and
					   a.concept_code_son=c.concept_code 
					     ) t, dwh_thesaurus_enrsem
					     where concept_code_son=concept_code and pref='Y'
						$filtre_phenotype_genotype
						$query_age
					    group by concept_code, concept_str,count_patient_subsumption
					     order by count(distinct patient_num)  desc";
			}
		}
	} else {
		$requete="select 
				sum(count_concept_str_found) as nbcode_non_distinct_dans_res 
			from 
				dwh_enrsem where patient_num in (select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and certainty=1 and context='patient_text' $query_age";
		$sel=oci_parse($dbh, $requete);
		oci_execute($sel);
		$r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS);
		$nbcode_non_distinct_dans_res=$r['NBCODE_NON_DISTINCT_DANS_RES'];
		
		if ($donnees_reelles_ou_pref=='reelles') {
			$requete=" select 
					dwh_enrsem.concept_code,
					concept_str_found as concept_str,
					count(distinct t.patient_num) nb, 
					count_patient_subsumption as nb_patient_concept_global, 
					sum(count_concept_str_found) as nbfois_ce_code_dans_le_res,
					median(AGE_PATIENT) as median_AGE_PATIENT
			 from (select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) t, dwh_enrsem,dwh_thesaurus_enrsem
			 where t.patient_num=dwh_enrsem.patient_num and
			      context='patient_text' and
			      certainty=1 and
			  dwh_enrsem.concept_code=dwh_thesaurus_enrsem.concept_code
			$filtre_phenotype_genotype
			$query_age
			 group by dwh_enrsem.concept_code,concept_str_found,count_patient_subsumption
			 order by count(distinct t.patient_num) desc
			  ";
		} else {
			if ($distance==10) {

				$requete="select  
						dwh_thesaurus_enrsem.concept_code, 
						concept_str,count(distinct patient_num) as nb, 
						count_patient_subsumption as nb_patient_concept_global, 
						sum(count_concept_str_found) as nbfois_ce_code_dans_le_res ,
						median(AGE_PATIENT) as median_AGE_PATIENT
					from 
						dwh_enrsem , dwh_thesaurus_enrsem
					where 
						dwh_enrsem.concept_code=dwh_thesaurus_enrsem.concept_code and pref='Y'
					 	$filtre_phenotype_genotype
						and patient_num in ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num)  and 
						context='patient_text' and
						certainty=1 
						$query_age
						group by dwh_thesaurus_enrsem.concept_code, concept_str,count_patient_subsumption
						order by count(distinct patient_num)  desc";
			} else {
				// a revoir pour prendre en compte les fils ... la somme est doublée par rapport à la réalié ... bug // 
				 $requete=" select 
				 		concept_code, 
				 		concept_str,
				 		count(distinct patient_num) as nb, 
				 		count_patient_subsumption as nb_patient_concept_global, 
				 		sum(count_concept_str_found) as nbfois_ce_code_dans_le_res ,
						median(AGE_PATIENT) as median_AGE_PATIENT
				 	from (
						select a.concept_code_son ,patient_num,count_concept_str_found,AGE_PATIENT from dwh_thesaurus_enrsem_graph a, dwh_thesaurus_enrsem_graph b, dwh_enrsem c
						     where a.concept_code_father='RACINE'  and
						   patient_num in ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num ) and 
						         a.distance=$distance and
						   a.concept_code_son=b.concept_code_father and
						      context='patient_text' and
						      certainty=1 and
						     b.concept_code_son=c.concept_code 
						     UNION ALL
						select concept_code_son  ,patient_num,count_concept_str_found,AGE_PATIENT from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
						     where a.concept_code_father='RACINE'   and
						   patient_num in ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and 
						         a.distance<=$distance and
						      context='patient_text' and
						      certainty=1 and
						   a.concept_code_son=c.concept_code 
						     ) t, dwh_thesaurus_enrsem
					where 
						concept_code_son=concept_code and 
						pref='Y'
			 			$filtre_phenotype_genotype
			 			$query_age
					    group by concept_code, concept_str,count_patient_subsumption
					     order by count(distinct patient_num)  desc";
			}
		}
	}
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$concept_str=$r['CONCEPT_STR'];
		$concept_code=$r['CONCEPT_CODE'];
		$nb_patient_avec_ce_code_dans_resultat=$r['NB'];
		$nbfois_ce_code_dans_le_res=$r['NBFOIS_CE_CODE_DANS_LE_RES'];
		$nb_patient_concept_global=$r['NB_PATIENT_CONCEPT_GLOBAL'];
		$median_age_patient=preg_replace("/,/",".",$r['MEDIAN_AGE_PATIENT']);
		$i++;
		
		
		$req_nb=" select round($nb_patient_avec_ce_code_dans_resultat*100/$nb_patient_dans_resultat,1) as pourc_local from dual";
		$sel_nb=oci_parse($dbh, $req_nb);
		oci_execute($sel_nb);
		$r_nb=oci_fetch_array($sel_nb,OCI_ASSOC+OCI_RETURN_NULLS);
		$pourc_local=$r_nb['POURC_LOCAL'];
		
		
		if ($i<=5000) {
			if ($nb_patient_concept_global>0) {
				$req_nb=" select round($nb_patient_concept_global*100/$nb_patient_total,1) as pourc_entrepot,round($nb_patient_avec_ce_code_dans_resultat*100/$nb_patient_concept_global,1) as pourc_res_entrepot from dual";
			} else {
				$req_nb=" select round($nb_patient_concept_global*100/$nb_patient_total,1) as pourc_entrepot,0 as pourc_res_entrepot from dual";
			}
			$sel_nb=oci_parse($dbh, $req_nb);
			oci_execute($sel_nb);
			$r_nb=oci_fetch_array($sel_nb,OCI_ASSOC+OCI_RETURN_NULLS);
			$pourc_entrepot=$r_nb['POURC_ENTREPOT'];
			$pourc_res_entrepot=$r_nb['POURC_RES_ENTREPOT'];
			
			// tf idf concept / resultat ///
			//TF : nb patient avec le concept_code dans res / nb patient avec le concept_code dans resultat
			// IDF : log ( nb patient dans dwh / nb patient avec le concept_code dans dwh)
			
			if ($nb_patient_concept_global>0) {
				$req_nb_tf_idf_nbcode=" select round(100*$nbfois_ce_code_dans_le_res/$nbcode_non_distinct_dans_res*log(10,$nb_patient_total/$nb_patient_concept_global),2) as tf_idf from dual";
			} else {
				$req_nb_tf_idf_nbcode=" select 0 as tf_idf from dual";
			}
			$sel_nb=oci_parse($dbh, $req_nb_tf_idf_nbcode);
			oci_execute($sel_nb) ||die ("$req_nb_tf_idf_nbcode");
			$r_nb=oci_fetch_array($sel_nb,OCI_ASSOC+OCI_RETURN_NULLS);
			$tf_idf_nbcode=$r_nb['TF_IDF'];
			$tf_idf_nbcode=str_replace(",",".",$tf_idf_nbcode);
			
			
			
			if ($nb_patient_concept_global>0) {
				$req_nb=" select round(100*$nb_patient_avec_ce_code_dans_resultat/$nb_patient_dans_resultat*log(10,$nb_patient_total/$nb_patient_concept_global),2) as tf_idf from dual";
			} else {
				$req_nb=" select 0 as tf_idf from dual";
			}
			$sel_nb=oci_parse($dbh, $req_nb);
			oci_execute($sel_nb) ||die ("$req_nb");
			$r_nb=oci_fetch_array($sel_nb,OCI_ASSOC+OCI_RETURN_NULLS);
			$tf_idf_nbpatient=$r_nb['TF_IDF'];
			$tf_idf_nbpatient=str_replace(",",".",$tf_idf_nbpatient);
			
			

			
			if ($nb_patient_avec_ce_code_dans_resultat>1) {
				if ($nb_patient_concept_global>0) {
					$req_nb=" select round('$pourc_res_entrepot'*100*$nb_patient_avec_ce_code_dans_resultat/$nb_patient_dans_resultat*log(10,$nb_patient_total/$nb_patient_concept_global),2) as tf_idf from dual";
				} else {
					$req_nb=" select 0 as tf_idf from dual";
				}
					
				$sel_nb=oci_parse($dbh, $req_nb);
				oci_execute($sel_nb) ||die ("$req_nb");
				$r_nb=oci_fetch_array($sel_nb,OCI_ASSOC+OCI_RETURN_NULLS);
				$tf_idf_nbpatient_pourc_res_entrepot=$r_nb['TF_IDF'];
				$tf_idf_nbpatient_pourc_res_entrepot=str_replace(",",".",$tf_idf_nbpatient_pourc_res_entrepot);
			}else {
				$tf_idf_nbpatient_pourc_res_entrepot=0;
				$pourc_res_entrepot=0;
			}
			
			
			$pourc_local=str_replace(",",".",$pourc_local);
			$pourc_entrepot=str_replace(",",".",$pourc_entrepot);
			$pourc_res_entrepot=str_replace(",",".",$pourc_res_entrepot);
			$code_libelle_sans_apostrophe=str_replace("'"," ",$concept_str);
			if ($tableau==1) {
				if ($_SESSION['dwh_droit_see_detailed'.$datamart_num]) {
					$onclick=" onclick=\"ajouter_filtre('$code_libelle_sans_apostrophe');\"";
				}
				
#				$json_tableau.= " [
#				     \"$concept_str\",
#					 \"$nb_patient_avec_ce_code_dans_resultat\",
#					 \"$pourc_local\",
#					 \"$pourc_entrepot\",
#					 \"$pourc_res_entrepot\",
#					 \"$tf_idf_nbcode\",
#					 \"$tf_idf_nbpatient\",
#					 \"$tf_idf_nbcode_pourc_res_entrepot\",
#					 \"$tf_idf_nbpatient_pourc_res_entrepot\"
#				    ],";
				$json_tableau.= " [
					     \"\",
					     \"$concept_str\",
					 \"$nb_patient_avec_ce_code_dans_resultat\",
					 \"<img src='images/search.png' style='cursor:pointer;' onclick=\\\"window.open('moteur.php?action=rechercher_dans_resultat&type=$type&concept_code=$concept_code&tmpresult_num=$tmpresult_num&datamart_num=$datamart_num', '_blank');\\\">\",
					 \"$pourc_local\",
					 \"$tf_idf_nbcode\",
					 \"$pourc_res_entrepot\",
					 \"$tf_idf_nbpatient_pourc_res_entrepot\",
					 \"$median_age_patient\"
				    ],";
			}
			if ($tableau==2) {
				$json_tableau.= " [
					     \"$concept_code\",
					     \"$concept_str\",
					 \"$nb_patient_avec_ce_code_dans_resultat\",
					 \"$pourc_local\",
					 \"$tf_idf_nbcode\",
					 \"$pourc_res_entrepot\",
					 \"$tf_idf_nbpatient_pourc_res_entrepot\",
					 \"$median_age_patient\"
				    ],";
			}
			if ($i<100) {
				if ($graph=='1') {
					$nb_c++;
					$liste_categories="'$code_libelle_sans_apostrophe ($nb_patient_avec_ce_code_dans_resultat)',".$liste_categories;
					$liste_locale="-$pourc_local,".$liste_locale;
					$liste_entrepot="$pourc_res_entrepot,".$liste_entrepot;
					$liste_pourc_res_entrepot="$tf_idf_nbpatient_pourc_res_entrepot,".$liste_pourc_res_entrepot;
				}
			}
			if ($i<100) {
				if ($cloud=='1') {
					$json_cloud.=" {text: \"$concept_str\", weight: $nb_patient_avec_ce_code_dans_resultat},";
				}
			}
		}
	}
	if ($tableau==1 || $tableau==2) {
		$json_tableau=substr($json_tableau,0,-1);
	}
	if ($cloud==1) {
		$json_cloud=substr($json_cloud,0,-1);
	}
	if ($graph=='1' ) {
		$liste_categories=substr($liste_categories,0,-1);
		$liste_locale=substr($liste_locale,0,-1);
		$liste_entrepot=substr($liste_entrepot,0,-1);
		$liste_pourc_res_entrepot=substr($liste_pourc_res_entrepot,0,-1);
		$height=150+$nb_c*18;
		$json_graph= "$liste_categories;separateur;$liste_locale;separateur;$liste_entrepot;separateur;$liste_pourc_res_entrepot;separateur;$height";
	}
	return "$json_tableau;separateur_general;$json_cloud;separateur_general;$json_graph";
}


function repartition_concepts_general_json_php ($tmpresult_num,$phenotype_genotype,$tableau,$graph,$cloud,$donnees_reelles_ou_pref,$type,$distance) {
	global $dbh,$datamart_num,$user_num_session;
	$requete=" select count_distinct_patient as NB_PATIENT from dwh_info_enrsem";
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS);
	$nb_patient_total=$r['NB_PATIENT'];
	if ($phenotype_genotype!='') {
		$filtre_phenotype_genotype=" and $phenotype_genotype=1 ";
	}
	
	$requete=" select count(distinct patient_num) as NB_PATIENT from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num ";
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS);
	$nb_patient=$r['NB_PATIENT'];
	
	$json_tableau='';
	$json_cloud='';
	$json_graph='';
	$i=0;
	$nb_c=0;
	if ($type=='document') {
		if ($donnees_reelles_ou_pref=='reelles') {
			$requete=" select dwh_enrsem.concept_code,concept_str_found as concept_str, dwh_tmp_result_$user_num_session.patient_num , count_patient_subsumption as nb_patient_concept_global
			 from dwh_tmp_result_$user_num_session, dwh_enrsem,dwh_thesaurus_enrsem
			 where tmpresult_num=$tmpresult_num
			 and dwh_tmp_result_$user_num_session.document_num=dwh_enrsem.document_num
			 and context='patient_text'
			 and certainty=1
			 and dwh_enrsem.concept_code=dwh_thesaurus_enrsem.concept_code
			 $filtre_phenotype_genotype
			  ";
		} else {
			if ($distance==10) {
				 $requete=" select  concept_code, concept_str, patient_num, count_patient_subsumption as nb_patient_concept_global from (
					select concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
					     where a.concept_code_father='RACINE'   and
					   document_num in ( select document_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and 
					      context='patient_text' and
					      certainty=1 and
					   a.concept_code_son=c.concept_code 
					     ) t, dwh_thesaurus_enrsem
					     where concept_code_son=concept_code and pref='Y'
					      $filtre_phenotype_genotype";
			} else {
				 $requete=" select  concept_code, concept_str, patient_num, count_patient_subsumption as nb_patient_concept_global from (
					select a.concept_code_son ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_thesaurus_enrsem_graph b, dwh_enrsem c
					     where a.concept_code_father='RACINE'  and
					   document_num in ( select document_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num ) and 
					         a.distance=$distance and
					   a.concept_code_son=b.concept_code_father and
					      context='patient_text' and
					      certainty=1 and
					     b.concept_code_son=c.concept_code 
					     UNION ALL
					select concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
					     where a.concept_code_father='RACINE'   and
					   document_num in ( select document_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and 
					         a.distance<=$distance and
					      context='patient_text' and
					      certainty=1 and
					   a.concept_code_son=c.concept_code 
					     ) t, dwh_thesaurus_enrsem
					     where concept_code_son=concept_code and pref='Y'
					      $filtre_phenotype_genotype
					      ";
			}
				     
		}
	} else {
		if ($donnees_reelles_ou_pref=='reelles') {
			$requete=" select dwh_enrsem.concept_code,concept_str_found as concept_str, t.patient_num, count_patient_subsumption as nb_patient_concept_global
			 from (select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) t, dwh_enrsem,dwh_thesaurus_enrsem
			 where t.patient_num=dwh_enrsem.patient_num
			    and  context='patient_text' 
			    and  certainty=1 
			 and dwh_enrsem.concept_code=dwh_thesaurus_enrsem.concept_code
			 $filtre_phenotype_genotype
			  ";
		} else {
			if ($distance==10) {
				 $requete=" select  concept_code, concept_str, patient_num  , count_patient_subsumption as nb_patient_concept_global from (
					select concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
					     where a.concept_code_father='RACINE'   and
					   patient_num in ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and 
					      context='patient_text' and
					      certainty=1 and
					   a.concept_code_son=c.concept_code 
					     ) t, dwh_thesaurus_enrsem
					     where concept_code_son=concept_code and pref='Y'
					      $filtre_phenotype_genotype
					   ";
			} else {
				 $requete=" select  concept_code, concept_str, patient_num , count_patient_subsumption as nb_patient_concept_global from (
					select a.concept_code_son ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_thesaurus_enrsem_graph b, dwh_enrsem c
					     where a.concept_code_father='RACINE'  and
					   patient_num in ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num ) and 
					         a.distance=$distance and
					   a.concept_code_son=b.concept_code_father and
					      context='patient_text' and
					      certainty=1 and
					     b.concept_code_son=c.concept_code 
					     UNION ALL
					select concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
					     where a.concept_code_father='RACINE'   and
					   patient_num in ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and 
					         a.distance<=$distance and
					      context='patient_text' and
					      certainty=1 and
					   a.concept_code_son=c.concept_code 
					     ) t, dwh_thesaurus_enrsem
					     where concept_code_son=concept_code and pref='Y'
					      $filtre_phenotype_genotype
					     ";
			}
		}
	}
	$tableau_total=array();
	$tableau_nb_patient=array();
	$tableau_nb_patient_concept_global=array();
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$concept_str=$r['CONCEPT_STR'];
		$concept_code=$r['CONCEPT_CODE'];
		$patient_num=$r['PATIENT_NUM'];
		$nb_patient_concept_global=$r['NB_PATIENT_CONCEPT_GLOBAL'];
		
		if ($tableau_total[$concept_code.$concept_str.$patient_num]=='') {
			$tableau_total[$concept_code.$concept_str.$patient_num]='ok';
			$tableau_nb_patient[$concept_code.";".$concept_str]++;
		}
		$tableau_nb_patient_concept_global[$concept_code.";".$concept_str]=$nb_patient_concept_global;
	}
	
	foreach ($tableau_nb_patient as $code_code_libelle => $nb) {
		$i++;
		$nb_patient_concept_global=$tableau_nb_patient_concept_global[$code_code_libelle];
		list($concept_code,$concept_str)=explode(';',$code_code_libelle);
		$req_nb=" select round($nb*100/$nb_patient,1) as pourc_local from dual";
		$sel_nb=oci_parse($dbh, $req_nb);
		oci_execute($sel_nb);
		$r_nb=oci_fetch_array($sel_nb,OCI_ASSOC+OCI_RETURN_NULLS);
		$pourc_local=$r_nb['POURC_LOCAL'];
		
		if ($i<=500) {
			$nb_c++;
			if ($nb_patient_concept_global>0) {
				$req_nb=" select round($nb_patient_concept_global*100/$nb_patient_total,1) as pourc_entrepot,round($nb*100/$nb_patient_concept_global,1) as pourc_res_entrepot from dual";
			} else {
				$req_nb=" select round($nb_patient_concept_global*100/$nb_patient_total,1) as pourc_entrepot,0 as pourc_res_entrepot from dual";
			}
			$sel_nb=oci_parse($dbh, $req_nb);
			oci_execute($sel_nb);
			$r_nb=oci_fetch_array($sel_nb,OCI_ASSOC+OCI_RETURN_NULLS);
			$pourc_entrepot=$r_nb['POURC_ENTREPOT'];
			$pourc_res_entrepot=$r_nb['POURC_RES_ENTREPOT'];
			
			$pourc_local=str_replace(",",".",$pourc_local);
			$pourc_entrepot=str_replace(",",".",$pourc_entrepot);
			$pourc_res_entrepot=str_replace(",",".",$pourc_res_entrepot);
			$code_libelle_sans_apostrophe=str_replace("'"," ",$concept_str);
			if ($tableau==1) {
				if ($_SESSION['dwh_droit_see_detailed'.$datamart_num]) {
					$onclick=" onclick=\"ajouter_filtre('$code_libelle_sans_apostrophe');\"";
				}
				
				$json_tableau.= " [
				     \"$concept_str\",
					 \"$nb\",
					 \"$pourc_local\",
					 \"$pourc_entrepot\",
					 \"$pourc_res_entrepot\"
				    ],";
			}
			if ($graph=='1') {
				$liste_categories="'$code_libelle_sans_apostrophe ($nb)',".$liste_categories;
				$liste_locale="-$pourc_local,".$liste_locale;
				$liste_entrepot="$pourc_entrepot,".$liste_entrepot;
				$liste_pourc_res_entrepot="$pourc_res_entrepot,".$liste_pourc_res_entrepot;
			}
			if ($i<100) {
				if ($cloud=='1') {
					$json_cloud.=" {text: \"$concept_str\", weight: $nb},";
				}
			}
		}
	}
	if ($tableau==1) {
		$json_tableau=substr($json_tableau,0,-1);
	}
	if ($cloud==1) {
		$json_cloud=substr($json_cloud,0,-1);
	}
	if ($graph=='1' ) {
		$liste_categories=substr($liste_categories,0,-1);
		$liste_locale=substr($liste_locale,0,-1);
		$liste_entrepot=substr($liste_entrepot,0,-1);
		$liste_pourc_res_entrepot=substr($liste_pourc_res_entrepot,0,-1);
		$height=150+$nb_c*18;
		$json_graph= "$liste_categories;separateur;$liste_locale;separateur;$liste_entrepot;separateur;$liste_pourc_res_entrepot;separateur;$height";
	}
	return "$json_tableau;separateur_general;$json_cloud;separateur_general;$json_graph";
}


function cloud_concepts_json ($tmpresult_num,$phenotype_genotype,$donnees_reelles_ou_pref,$type,$distance) {
	global $dbh,$user_num_session;
	
	$liste_mot="";
	$i=0;
	
	if ($phenotype_genotype!='') {
		$filtre_phenotype_genotype=" and $phenotype_genotype=1 ";
	}
	if ($type=='document') {
		if ($distance==10) {
			//on supprime distance si 9, pour prendre en compte distance inférieure à 9
			 $requete=" select  concept_code, concept_str,count(distinct patient_num) as nb from (
				select concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
				     where a.concept_code_father='RACINE'   and
				   document_num in ( select document_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and 
					      context='patient_text' and
					      certainty=1 and
				   a.concept_code_son=c.concept_code 
				     ) t, dwh_thesaurus_enrsem
				     where concept_code_son=concept_code and pref='Y'
				      $filtre_phenotype_genotype
				    group by concept_code, concept_str
				     order by count(distinct patient_num)  desc";
			
		} else {
			 $requete=" select  concept_code, concept_str,count(distinct patient_num) as nb from (
				select a.concept_code_son ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_thesaurus_enrsem_graph b, dwh_enrsem c
				     where a.concept_code_father='RACINE'  and
				   document_num in ( select document_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num ) and 
				         a.distance=$distance and
				   a.concept_code_son=b.concept_code_father and
					      context='patient_text' and
					      certainty=1 and
				     b.concept_code_son=c.concept_code 
				    union
				select concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
				     where a.concept_code_father='RACINE'   and
				   document_num in ( select document_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and 
				         a.distance<=$distance and
					      context='patient_text' and
					      certainty=1 and
				   a.concept_code_son=c.concept_code 
				     ) t, dwh_thesaurus_enrsem
				     where concept_code_son=concept_code and pref='Y'
				      $filtre_phenotype_genotype
				    group by concept_code, concept_str
				     order by count(distinct patient_num)  desc";
			
		}
	} else {
		if ($distance==10) {
			 $requete=" select  concept_code, concept_str,count(distinct patient_num) as nb from (
				select concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
				     where a.concept_code_father='RACINE'   and
				   patient_num in ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and 
					      context='patient_text' and
					      certainty=1 and
				   a.concept_code_son=c.concept_code 
				     ) t, dwh_thesaurus_enrsem
				     where concept_code_son=concept_code and pref='Y'
				      $filtre_phenotype_genotype
				    group by concept_code, concept_str
				     order by count(distinct patient_num)  desc";
			
		} else {
			 $requete=" select  concept_code, concept_str,count(distinct patient_num) as nb from (
				select a.concept_code_son ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_thesaurus_enrsem_graph b, dwh_enrsem c
				     where a.concept_code_father='RACINE'  and
				   patient_num in ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num ) and 
				         a.distance=$distance and
				   a.concept_code_son=b.concept_code_father and
					      context='patient_text' and
					      certainty=1 and
				     b.concept_code_son=c.concept_code 
				    union
				select concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
				     where a.concept_code_father='RACINE'   and
				   patient_num in ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and 
				         a.distance<=$distance and
					      context='patient_text' and
					      certainty=1 and
				   a.concept_code_son=c.concept_code 
				     ) t, dwh_thesaurus_enrsem
				     where concept_code_son=concept_code and pref='Y'
				      $filtre_phenotype_genotype
				    group by concept_code, concept_str
				     order by count(distinct patient_num)  desc";
			
		}
	}
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$i++;
		if ($i<100) {
			$concept_str=$r['CONCEPT_STR'];
			$nb=$r['NB'];
			$liste_mot.=" {text: \"$concept_str\", weight: $nb},";
		}
	}
	$liste_mot=substr($liste_mot,0,-1);
	return $liste_mot;
}



function repartition_concepts_json ($tmpresult_num,$phenotype_genotype,$tableau,$graph,$donnees_reelles_ou_pref,$type,$distance) {
	global $dbh,$datamart_num,$user_num_session;
	//$requete=" select count(distinct patient_num) as NB_PATIENT from dwh_enrsem";
	$requete=" select count_distinct_patient as NB_PATIENT from dwh_info_enrsem";
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS);
	$nb_patient_total=$r['NB_PATIENT'];
	
	$requete=" select count(distinct patient_num) as NB_PATIENT from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num ";
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS);
	$nb_patient=$r['NB_PATIENT'];
	
	if ($phenotype_genotype!='') {
		$filtre_phenotype_genotype=" and $phenotype_genotype=1 ";
	}
	
	$i=0;
	$nb_c=0;
	if ($type=='document') {
		if ($donnees_reelles_ou_pref=='reelles') {
			$requete=" select dwh_enrsem.concept_code,concept_str_found as concept_str,count(distinct dwh_tmp_result_$user_num_session.patient_num) nb, count_patient_subsumption as nb_patient_concept_global
			 from dwh_tmp_result_$user_num_session, dwh_enrsem,dwh_thesaurus_enrsem
			 where tmpresult_num=$tmpresult_num
			 and dwh_tmp_result_$user_num_session.document_num=dwh_enrsem.document_num
					    and  context='patient_text' 
					     and certainty=1 
			 and dwh_enrsem.concept_code=dwh_thesaurus_enrsem.concept_code
			 $filtre_phenotype_genotype
			 group by dwh_enrsem.concept_code,concept_str_found,count_patient_subsumption
			 order by count(distinct dwh_tmp_result_$user_num_session.patient_num) desc
			  ";
		} else {
			if ($distance==10) {
				 $requete=" select  concept_code, concept_str,count(distinct patient_num) as nb, count_patient_subsumption as nb_patient_concept_global from (
					select concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
					     where a.concept_code_father='RACINE'   and
					   document_num in ( select document_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and 
					      context='patient_text' and
					      certainty=1 and
					   a.concept_code_son=c.concept_code 
					     ) t, dwh_thesaurus_enrsem
					     where concept_code_son=concept_code and pref='Y'
						$filtre_phenotype_genotype
					    group by concept_code, concept_str,count_patient_subsumption
					     order by count(distinct patient_num)  desc";
			} else {
				 $requete=" select  concept_code, concept_str,count(distinct patient_num) as nb, count_patient_subsumption as nb_patient_concept_global from (
					select a.concept_code_son ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_thesaurus_enrsem_graph b, dwh_enrsem c
					     where a.concept_code_father='RACINE'  and
					   document_num in ( select document_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num ) and 
					         a.distance=$distance and
					   a.concept_code_son=b.concept_code_father and
					      context='patient_text' and
					      certainty=1 and
					     b.concept_code_son=c.concept_code 
					    union
					select concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
					     where a.concept_code_father='RACINE'   and
					   document_num in ( select document_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and 
					         a.distance<=$distance and
					      context='patient_text' and
					      certainty=1 and
					   a.concept_code_son=c.concept_code 
					     ) t, dwh_thesaurus_enrsem
					     where concept_code_son=concept_code and pref='Y'
			 			$filtre_phenotype_genotype
					    group by concept_code, concept_str,count_patient_subsumption
					     order by count(distinct patient_num)  desc";
			}
				     
		}
	} else {
		if ($donnees_reelles_ou_pref=='reelles') {
			$requete=" select dwh_enrsem.concept_code,concept_str_found as concept_str,count(distinct t.patient_num) nb, count_patient_subsumption as nb_patient_concept_global
			 from (select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) t, dwh_enrsem,dwh_thesaurus_enrsem
			 where t.patient_num=dwh_enrsem.patient_num
			    and  context='patient_text' 
			   and   certainty=1 
			 and dwh_enrsem.concept_code=dwh_thesaurus_enrsem.concept_code
			$filtre_phenotype_genotype
			 group by dwh_enrsem.concept_code,concept_str_found,count_patient_subsumption
			 order by count(distinct t.patient_num) desc
			  ";
		} else {
			if ($distance==10) {
				 $requete=" select  concept_code, concept_str,count(distinct patient_num) as nb, count_patient_subsumption as nb_patient_concept_global from (
					select concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
					     where a.concept_code_father='RACINE'   and
					   patient_num in ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and 
					      context='patient_text' and
					      certainty=1 and
					   a.concept_code_son=c.concept_code 
					     ) t, dwh_thesaurus_enrsem
					     where concept_code_son=concept_code and pref='Y'
			 			$filtre_phenotype_genotype
					    group by concept_code, concept_str,count_patient_subsumption
					     order by count(distinct patient_num)  desc";
			} else {
				 $requete=" select  concept_code, concept_str,count(distinct patient_num) as nb, count_patient_subsumption as nb_patient_concept_global from (
					select a.concept_code_son ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_thesaurus_enrsem_graph b, dwh_enrsem c
					     where a.concept_code_father='RACINE'  and
					   patient_num in ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num ) and 
					         a.distance=$distance and
					   a.concept_code_son=b.concept_code_father and
					      context='patient_text' and
					      certainty=1 and
					     b.concept_code_son=c.concept_code 
					    union
					select concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
					     where a.concept_code_father='RACINE'   and
					   patient_num in ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and 
					         a.distance<=$distance and
					      context='patient_text' and
					      certainty=1 and
					   a.concept_code_son=c.concept_code 
					     ) t, dwh_thesaurus_enrsem
					     where concept_code_son=concept_code and pref='Y'
			 			$filtre_phenotype_genotype
					    group by concept_code, concept_str,count_patient_subsumption
					     order by count(distinct patient_num)  desc";
			}
		}
	}
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$concept_str=$r['CONCEPT_STR'];
		$concept_code=$r['CONCEPT_CODE'];
		$nb=$r['NB'];
		$nb_patient_concept_global=$r['NB_PATIENT_CONCEPT_GLOBAL'];
		$i++;
		
		
		$req_nb=" select round($nb*100/$nb_patient,1) as pourc_local from dual";
		$sel_nb=oci_parse($dbh, $req_nb);
		oci_execute($sel_nb);
		$r_nb=oci_fetch_array($sel_nb,OCI_ASSOC+OCI_RETURN_NULLS);
		$pourc_local=$r_nb['POURC_LOCAL'];
		
		
		if ($i<=500) {
			$nb_c++;
			if ($nb_patient_total>0) {
				if ($nb_patient_concept_global>0) {
					$req_nb=" select round($nb_patient_concept_global*100/$nb_patient_total,1) as pourc_entrepot,round($nb*100/$nb_patient_concept_global,1) as pourc_res_entrepot from dual";
					$sel_nb=oci_parse($dbh, $req_nb);
					oci_execute($sel_nb);
					$r_nb=oci_fetch_array($sel_nb,OCI_ASSOC+OCI_RETURN_NULLS);
					$pourc_entrepot=$r_nb['POURC_ENTREPOT'];
					$pourc_res_entrepot=$r_nb['POURC_RES_ENTREPOT'];
				} else {
					$req_nb=" select round($nb_patient_concept_global*100/$nb_patient_total,1) as pourc_entrepot,0 as pourc_res_entrepot from dual";
					$sel_nb=oci_parse($dbh, $req_nb);
					oci_execute($sel_nb);
					$r_nb=oci_fetch_array($sel_nb,OCI_ASSOC+OCI_RETURN_NULLS);
					$pourc_entrepot=$r_nb['POURC_ENTREPOT'];
					$pourc_res_entrepot=$r_nb['POURC_RES_ENTREPOT'];
				}
			}
			$pourc_local=str_replace(",",".",$pourc_local);
			$pourc_entrepot=str_replace(",",".",$pourc_entrepot);
			$pourc_res_entrepot=str_replace(",",".",$pourc_res_entrepot);
			$code_libelle_sans_apostrophe=str_replace("'"," ",$concept_str);
			if ($tableau==1) {
				if ($_SESSION['dwh_droit_see_detailed'.$datamart_num]) {
					$onclick=" onclick=\"ajouter_filtre('$code_libelle_sans_apostrophe');\"";
				}
				
				$json.= " [
				     \"$concept_str\",
					 \"$nb\",
					 \"$pourc_local\",
					 \"$pourc_entrepot\",
					 \"$pourc_res_entrepot\"
				    ],";
			}
			if ($graph=='1') {
				$liste_categories="'$code_libelle_sans_apostrophe ($nb)',".$liste_categories;
				$liste_locale="-$pourc_local,".$liste_locale;
				$liste_entrepot="$pourc_entrepot,".$liste_entrepot;
				$liste_pourc_res_entrepot="$pourc_res_entrepot,".$liste_pourc_res_entrepot;
			}
		}
	}
	if ($tableau==1) {
		$json=substr($json,0,-1);
		return "{\"data\":[$json]}";
	}
	if ($graph=='1' ) {
		$liste_categories=substr($liste_categories,0,-1);
		$liste_locale=substr($liste_locale,0,-1);
		$liste_entrepot=substr($liste_entrepot,0,-1);
		$liste_pourc_res_entrepot=substr($liste_pourc_res_entrepot,0,-1);
		$height=150+$nb_c*18;
		
		if ($graph=='1') {
			return "$liste_categories;separateur;$liste_locale;separateur;$liste_entrepot;separateur;$liste_pourc_res_entrepot;separateur;$height";
		}
	}
}

function repartition_data_json ($tmpresult_num,$thesaurus_code) {
	global $dbh,$datamart_num,$user_num_session;
	
	$requete=" select count(distinct patient_num) as NB_PATIENT from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num ";
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS);
	$nb_patient=$r['NB_PATIENT'];
	
	$i=0;
	$nb_c=0;
	
	$requete=" 
	 select b.thesaurus_data_num,b.concept_code,b.concept_str,count(distinct patient_num) as nb from dwh_data a, dwh_thesaurus_data b where 
    a.thesaurus_code=b.thesaurus_code and a.thesaurus_code='$thesaurus_code'
    and a.thesaurus_data_num=b.thesaurus_data_num
    and     patient_num in ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num )
    group by b.thesaurus_data_num,b.concept_code,b.concept_str
	";
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$concept_str=$r['CONCEPT_STR'];
		$concept_code=$r['CONCEPT_CODE'];
		$nb=$r['NB'];
		$i++;
		
		$req_nb=" select round($nb*100/$nb_patient,1) as pourc_local from dual";
		$sel_nb=oci_parse($dbh, $req_nb);
		oci_execute($sel_nb);
		$r_nb=oci_fetch_array($sel_nb,OCI_ASSOC+OCI_RETURN_NULLS);
		$pourc_local=$r_nb['POURC_LOCAL'];
		
		if ($i<=500) {
			$nb_c++;
			$pourc_local=str_replace(",",".",$pourc_local);
			//if ($_SESSION['dwh_droit_see_detailed'.$datamart_num]) {
			//	$onclick=" onclick=\"ajouter_filtre('$code_libelle_sans_apostrophe');\"";
			//}
			$concept_str=str_replace("\\","/",$concept_str);
			$json.= " [
			     \"$concept_str\",
				 \"$nb\",
				 \"$pourc_local\"
			    ],";
		}
	}
	$json=substr($json,0,-1);
	return "{\"data\":[$json]}";
}


function repartition_concepts_resumer_texte ($tmpresult_num,$phenotype_genotype,$type) {
	global $dbh,$user_num_session;
	
	if ($phenotype_genotype!='') {
		$filtre_phenotype_genotype="  $phenotype_genotype=1 ";
	}
	
	
	$tableau_resumer=array();
	print "<br><br><table class=tablefin>
	<tr><th>".get_translation('CONCEPT','Concept')."</th><th>".get_translation('EXTRACTS','Extraits')."</th></tr>";
	if ($type=='document') {
		$requete=" select concept_str_found as concept_str,text
		 from dwh_tmp_result_$user_num_session,dwh_text, dwh_enrsem
		 where 
		 tmpresult_num=$tmpresult_num
		 and dwh_tmp_result_$user_num_session.document_num=dwh_enrsem.document_num
		 and dwh_enrsem.context='patient_text'
		 and dwh_enrsem.certainty=1
		 and dwh_text.document_num=dwh_tmp_result_$user_num_session.document_num
		 and dwh_text.context='text'
		 and dwh_text.certainty=0
	    	 and dwh_enrsem.concept_code in (select concept_code from dwh_thesaurus_enrsem where  $filtre_phenotype_genotype)
		 order by concept_str_found
		  ";
	} else {
		$requete=" select concept_str_found as concept_str,text
		 from (select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) t,dwh_text, dwh_enrsem
		 where t.patient_num=dwh_enrsem.patient_num
		 and dwh_text.patient_num=t.patient_num
		 and dwh_enrsem.context='patient_text'
		 and dwh_enrsem.certainty=1
		 and dwh_text.document_num=dwh_enrsem.document_num
		 and dwh_text.context='text'
		 and dwh_text.certainty=0
	    	 and dwh_enrsem.concept_code in (select concept_code from dwh_thesaurus_enrsem where  $filtre_phenotype_genotype)
		 order by concept_str_found
		  ";
	}
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$concept_str=$r['CONCEPT_STR'];
  		if ($r['TEXT']) {
			$text=$r['TEXT']->load();
		}
		$resumer=resumer_resultat($text,$concept_str,array(),'');
		$tableau_resumer[$resumer]=$concept_str;
	}
	foreach ($tableau_resumer as $resumer => $concept_str) {
		print "<tr><td>$concept_str</td><td>$resumer</td></tr>";
	}
	print "</table>";
}







function repartition_go ($tmpresult_num,$id,$filtre_category) {
	global $dbh,$datamart_num,$user_num_session;
	$tableau=array();
	$tableau_gene=array();
	$requete=" 
	SELECT 
		category,
		go_term ,
		concept_str_found
	from 
		DWH_THESAURUS_PUBMED_GENE2GO,
		DWH_THESAURUS_PUBMED_GENE,
		dwh_enrsem,
		dwh_tmp_result_$user_num_session
	where  tmpresult_num=$tmpresult_num
		and dwh_tmp_result_$user_num_session.document_num=dwh_enrsem.document_num
		and context='patient_text'
		and certainty=1
		and dwh_enrsem.concept_code=DWH_THESAURUS_PUBMED_GENE.cui
		and DWH_THESAURUS_PUBMED_GENE.geneid=DWH_THESAURUS_PUBMED_GENE2GO.geneid and evidence in ('IDA','IEA','IPI','TAS') 
		and qualifier is null
	";
	//and concept_code in (select concept_code from dwh_thesaurus_enrsem where genotype=1)
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$category=$r['CATEGORY'];
		$go_term=$r['GO_TERM'];
		$concept_str_found=$r['CONCEPT_STR_FOUND'];
		if ($tableau_gene[$category][$go_term][$concept_str_found]=='') {
			$tableau[$category][$go_term].="$concept_str_found, ";
		}
		$tableau_gene[$category][$go_term][$concept_str_found]='ok';
	}
	
	print "<table class=\"tablefin\" id=\"tableau_$id\">";
	print "<thead>
		<tr>
			<th>".get_translation('CATEGORIES','Catégories')."</th>
			<th>".get_translation('CONCEPTS','Concepts')."</th>
			<th>".get_translation('GENES','Gènes')."</th>
			<th>".get_translation('COUNT_PATIENT_NUMBER_SHORT','Nb patients')."</th>
		</tr>
		</thead>
		<tbody>";
	$requete=" 
	SELECT 
		category,
		go_term ,
		count(distinct dwh_tmp_result_$user_num_session.patient_num) as nb_patient_go
	from 
		DWH_THESAURUS_PUBMED_GENE2GO,
		DWH_THESAURUS_PUBMED_GENE,
		dwh_enrsem,
		dwh_tmp_result_$user_num_session
	where  tmpresult_num=$tmpresult_num
		and dwh_tmp_result_$user_num_session.document_num=dwh_enrsem.document_num
		and context='patient_text'
		and certainty=1
		and dwh_enrsem.concept_code=DWH_THESAURUS_PUBMED_GENE.cui
		and DWH_THESAURUS_PUBMED_GENE.geneid=DWH_THESAURUS_PUBMED_GENE2GO.geneid and evidence in ('IDA','IEA','IPI','TAS') 
		and qualifier is null
	group by category,go_term
	";
	// and concept_code in (select concept_code from dwh_thesaurus_enrsem where genotype=1)
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$category=$r['CATEGORY'];
		$go_term=$r['GO_TERM'];
		$nb_patient_go=$r['NB_PATIENT_GO'];

		print "<tr>
		<td>$category</td>
		<td>$go_term</td>
		<td>".substr($tableau[$category][$go_term],0,-2)."</td>
		<td>$nb_patient_go</td>
		</tr>";
		
	}
	print "</tbody></table>";
	
	print "<script language=\"javascript\">
		$(document).ready(function() {
			$(\"#tableau_$id\").dataTable();
		}
		); 
	</script>";
}



function repartition_go_json ($tmpresult_num,$type) {
	global $dbh,$datamart_num,$user_num_session;
	$tableau=array();
	$tableau_gene=array();
	if ($type=='document') {
		$requete=" 
		SELECT 
			category,
			go_term ,
			concept_str_found
		from 
			DWH_THESAURUS_PUBMED_GENE2GO,
			DWH_THESAURUS_PUBMED_GENE,
			dwh_enrsem,
			dwh_tmp_result_$user_num_session
		where  tmpresult_num=$tmpresult_num
			and dwh_tmp_result_$user_num_session.document_num=dwh_enrsem.document_num
			and context='patient_text'
			and certainty=1
			and dwh_enrsem.concept_code=DWH_THESAURUS_PUBMED_GENE.cui
			and DWH_THESAURUS_PUBMED_GENE.geneid=DWH_THESAURUS_PUBMED_GENE2GO.geneid and evidence in ('IDA','IEA','IPI','TAS') 
			and qualifier is null
		";
		//and concept_code in (select concept_code from dwh_thesaurus_enrsem where genotype=1 )
	} else {
		$requete=" 
		SELECT 
			category,
			go_term ,
			concept_str_found
		from 
			DWH_THESAURUS_PUBMED_GENE2GO,
			DWH_THESAURUS_PUBMED_GENE,
			dwh_enrsem,
			 (select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) t
		where  t.patient_num=dwh_enrsem.patient_num
			and context='patient_text'
			and certainty=1
			and dwh_enrsem.concept_code=DWH_THESAURUS_PUBMED_GENE.cui
			and DWH_THESAURUS_PUBMED_GENE.geneid=DWH_THESAURUS_PUBMED_GENE2GO.geneid and evidence in ('IDA','IEA','IPI','TAS') 
			and qualifier is null
		";
		//and concept_code in (select concept_code from dwh_thesaurus_enrsem where genotype=1 )
	}
	
	
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$category=$r['CATEGORY'];
		$go_term=$r['GO_TERM'];
		$concept_str_found=$r['CONCEPT_STR_FOUND'];
		if ($tableau_gene[$category][$go_term][$concept_str_found]=='') {
			$tableau[$category][$go_term].="$concept_str_found, ";
		}
		$tableau_gene[$category][$go_term][$concept_str_found]='ok';
	}
	
	if ($type=='document') {
		$requete=" 
		SELECT 
			category,
			go_term ,
			count(distinct dwh_tmp_result_$user_num_session.patient_num) as nb_patient_go
		from 
			DWH_THESAURUS_PUBMED_GENE2GO,
			DWH_THESAURUS_PUBMED_GENE,
			dwh_enrsem,
			dwh_tmp_result_$user_num_session
		where  tmpresult_num=$tmpresult_num
			and dwh_tmp_result_$user_num_session.document_num=dwh_enrsem.document_num
			and context='patient_text'
			and certainty=1
			and dwh_enrsem.concept_code=DWH_THESAURUS_PUBMED_GENE.cui
			and DWH_THESAURUS_PUBMED_GENE.geneid=DWH_THESAURUS_PUBMED_GENE2GO.geneid and evidence in ('IDA','IEA','IPI','TAS') 
			and qualifier is null
		group by category,go_term
		";
		//and concept_code in (select concept_code from dwh_thesaurus_enrsem where genotype=1 )
	} else {
		$requete=" 
		SELECT 
			category,
			go_term ,
			count(distinct t.patient_num) as nb_patient_go
		from 
			DWH_THESAURUS_PUBMED_GENE2GO,
			DWH_THESAURUS_PUBMED_GENE,
			dwh_enrsem,
			 (select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) t
		where  t.patient_num=dwh_enrsem.patient_num
			and context='patient_text'
			and certainty=1
			and dwh_enrsem.concept_code=DWH_THESAURUS_PUBMED_GENE.cui
			and DWH_THESAURUS_PUBMED_GENE.geneid=DWH_THESAURUS_PUBMED_GENE2GO.geneid and evidence in ('IDA','IEA','IPI','TAS') 
			and qualifier is null
		group by category,go_term
		";
		//and concept_code in (select concept_code from dwh_thesaurus_enrsem where genotype=1 )
	}
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$category=$r['CATEGORY'];
		$go_term=$r['GO_TERM'];
		$nb_patient_go=$r['NB_PATIENT_GO'];

		$json.= "[
		\"$category\",
		\"$go_term\",
		\"".substr($tableau[$category][$go_term],0,-2)."\",
		\"$nb_patient_go\"],";
		
	}
	$json=substr($json,0,-1);
	return "{\"data\":[$json]}";
}



function repartition_go_data_json ($tmpresult_num) {
	global $dbh,$datamart_num,$user_num_session;
	$tableau=array();
	$tableau_gene=array();

	$requete=" 
	SELECT 
		category,
		go_term,
		dwh_thesaurus_data.concept_str
	from 
		DWH_THESAURUS_PUBMED_GENE2GO,
		DWH_THESAURUS_PUBMED_GENE,
		dwh_data,
		dwh_thesaurus_data,
		 (select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) t
	where  t.patient_num=dwh_data.patient_num
		and dwh_thesaurus_data.thesaurus_data_num=dwh_data.thesaurus_data_num
		and dwh_thesaurus_data.concept_code=DWH_THESAURUS_PUBMED_GENE.cui
		and DWH_THESAURUS_PUBMED_GENE.geneid=DWH_THESAURUS_PUBMED_GENE2GO.geneid and evidence in ('IDA','IEA','IPI','TAS') 
		and qualifier is null
		and dwh_data.thesaurus_code='gene'
	";
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$category=$r['CATEGORY'];
		$go_term=$r['GO_TERM'];
		$concept_str=$r['CONCEPT_STR'];
		if ($tableau_gene[$category][$go_term][$concept_str]=='') {
			$tableau[$category][$go_term].="$concept_str, ";
		}
		$tableau_gene[$category][$go_term][$concept_str]='ok';
	}

	$requete=" 
	SELECT 
		category,
		go_term ,
		count(distinct t.patient_num) as nb_patient_go
	from 
		DWH_THESAURUS_PUBMED_GENE2GO,
		DWH_THESAURUS_PUBMED_GENE,
		dwh_data,
		dwh_thesaurus_data,
		 (select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) t
	where  t.patient_num=dwh_data.patient_num
		and dwh_thesaurus_data.thesaurus_data_num=dwh_data.thesaurus_data_num
		and dwh_thesaurus_data.concept_code=DWH_THESAURUS_PUBMED_GENE.cui
		and DWH_THESAURUS_PUBMED_GENE.geneid=DWH_THESAURUS_PUBMED_GENE2GO.geneid and evidence in ('IDA','IEA','IPI','TAS') 
		and qualifier is null
		and dwh_data.thesaurus_code='gene'
	group by category,go_term";
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$category=$r['CATEGORY'];
		$go_term=$r['GO_TERM'];
		$nb_patient_go=$r['NB_PATIENT_GO'];

		$category=str_replace("\\","/",$category);
		$go_term=str_replace("\\","/",$go_term);
		$tableau[$category][$go_term]=str_replace("\\","/",$tableau[$category][$go_term]);
		$json.= "[
		\"$category\",
		\"$go_term\",
		\"".substr($tableau[$category][$go_term],0,-2)."\",
		\"$nb_patient_go\"],";
	}
	$json=substr($json,0,-1);
	return "{\"data\":[$json]}";
}




function repartition_concepts_tableau_patient ($patient_num,$id,$filtre_phenotype_genotype,$donnees_reelles_ou_pref) {
	global $dbh;
	
	$sel_nb=oci_parse($dbh, "select sum(count_concept_str_found) as NB from dwh_enrsem where patient_num=$patient_num  ");
	oci_execute($sel_nb);
	$r=oci_fetch_array($sel_nb,OCI_ASSOC+OCI_RETURN_NULLS);
	$nb_concept_dans_ce_patient=$r['NB'];
	
	$sel_nb=oci_parse($dbh, "select count(*) as NB from dwh_document where patient_num=$patient_num  ");
	oci_execute($sel_nb);
	$r=oci_fetch_array($sel_nb,OCI_ASSOC+OCI_RETURN_NULLS);
	$nb_document_total_patient=$r['NB'];
	
	
	$sel=oci_parse($dbh, "select sum(count_document) as count_document ,sum(count_patient) as count_patient from dwh_info_load where document_origin_code is not null and document_origin_code!='STARE'  and month is not null ");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS);
	$nb_document_total_entrepot=$r['COUNT_DOCUMENT'];
		
	$requete=" select count_distinct_patient as NB_PATIENT from dwh_info_enrsem";
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS);
	$nb_patient_total=$r['NB_PATIENT'];
	
	
	////////////////////////////////////////////////

	$sous_sel_nb=oci_parse($dbh, "select 
	concept_code,
	count(distinct document_num) as nb1,
	sum(count_concept_str_found) as nb2  
	from dwh_enrsem where patient_num=$patient_num  and  certainty=1 and context='patient_text' group by concept_code");
	oci_execute($sous_sel_nb);
	while ($sr_nb=oci_fetch_array($sous_sel_nb,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$concept_code=$sr_nb['CONCEPT_CODE'];
		$nb_document_patient_avec_ce_code=$sr_nb['NB1'];
		$nb_fois_ce_code_dans_ce_patient=$sr_nb['NB2'];
		$tableau_nb_document_patient_avec_ce_code[$concept_code]=$nb_document_patient_avec_ce_code;
		$tableau_nb_fois_ce_code_dans_ce_patient[$concept_code]=$nb_fois_ce_code_dans_ce_patient;
	}
	
	$sous_sel_nb=oci_parse($dbh, "select document_num, sum(count_concept_str_found) as nb_concept_dans_ce_document from dwh_enrsem where patient_num=$patient_num and  certainty=1 and context='patient_text' group by document_num");
	oci_execute($sous_sel_nb);
	while ($sr_nb=oci_fetch_array($sous_sel_nb,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$document_num=$sr_nb['DOCUMENT_NUM'];
		$nb_concept_dans_ce_document=$sr_nb['NB_CONCEPT_DANS_CE_DOCUMENT'];
		$tableau_nb_concept_dans_ce_document[$document_num]=$nb_concept_dans_ce_document;
	}
	
	
	
	
	////////////////////////////////////////////
	
	$i=0;
	print "<form autocomplete=\"off\"><table class=\"tablefin\" id=\"$id\" width=\"800\">";
	print "<thead>
		<tr>
			<th>".get_translation('CONCEPTS','Concepts')."</th>
			<th>".get_translation('COUNT_DOCUMENTS','Nb documents')."</th>
			<th>".get_translation('TF_IDF_DOC','TF-IDF doc')."</th>
			<th>".get_translation('TF_IDF_PATIENT','TF-IDF patient')."</th>
		</tr>
		</thead>
		<tbody>";
		
	if ($donnees_reelles_ou_pref=='reelles') {
		$requete=" select dwh_enrsem.concept_code,concept_str_found concept_str,count(*) nb
		 from dwh_enrsem,dwh_thesaurus_enrsem
		 where dwh_enrsem.patient_num=$patient_num
		 and context='patient_text'
		 and certainty=1
		 and dwh_enrsem.concept_code=dwh_thesaurus_enrsem.concept_code
		 $filtre_phenotype_genotype
		 group by dwh_enrsem.concept_code,concept_str_found
		 order by count(*) desc
		  ";
	} else {
		$requete=" select dwh_enrsem.concept_code, concept_str,count(*) nb
		 from dwh_enrsem,dwh_thesaurus_enrsem
		 where dwh_enrsem.patient_num=$patient_num
		 and context='patient_text'
		 and certainty=1
		 and dwh_enrsem.concept_code=dwh_thesaurus_enrsem.concept_code
		 and pref='Y'
		 $filtre_phenotype_genotype
		 group by dwh_enrsem.concept_code,concept_str
		 order by count(*) desc
		  ";
	}
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$concept_str=$r['CONCEPT_STR'];
		$concept_code=$r['CONCEPT_CODE'];
		$nb=$r['NB'];
		$i++;
		
		$nb_document_patient_avec_ce_code=$tableau_nb_document_patient_avec_ce_code[$concept_code];
		
		$tfidf_document_total=0;
		$sel_nb=oci_parse($dbh, "select document_num,sum(count_concept_str_found) as nb from dwh_enrsem where patient_num=$patient_num and concept_code='$concept_code' and  certainty=1 and context='patient_text' group by document_num");
		oci_execute($sel_nb);
		while ($r_nb=oci_fetch_array($sel_nb,OCI_ASSOC+OCI_RETURN_NULLS)) {
			$document_num=$r_nb['DOCUMENT_NUM'];
			$nb_fois_ce_code_dans_ce_document=$r_nb['NB'];
		
			$nb_concept_dans_ce_document=$tableau_nb_concept_dans_ce_document[$document_num];
			
			$idf=log($nb_document_total_patient/$nb_document_patient_avec_ce_code);
			$tf=$nb_fois_ce_code_dans_ce_document/$nb_concept_dans_ce_document;
			$tfidf_document=$idf*$tf;
			$tfidf_document_total+=$tfidf_document;
		}
		
		$nb_fois_ce_code_dans_ce_patient=$tableau_nb_fois_ce_code_dans_ce_patient[$concept_code];
		
		$sel_nb=oci_parse($dbh, "select count_patient from dwh_thesaurus_enrsem where concept_code='$concept_code'");
		oci_execute($sel_nb);
		$r_nb=oci_fetch_array($sel_nb,OCI_ASSOC+OCI_RETURN_NULLS);
		$nb_patient_avec_code=$r_nb['COUNT_PATIENT'];
		
		$idf=log($nb_patient_total/$nb_patient_avec_code);
		$tf=$nb_fois_ce_code_dans_ce_patient/$nb_concept_dans_ce_patient;
		
		$tfidf_document_total=round($tfidf_document_total,2);
		$tfidf_patient=round($idf*$tf,2);
		print "<tr><td>$concept_str</td><td>$nb</td><td>$tfidf_document_total</td><td>$tfidf_patient</td></tr>";
	}
	print "</tbody></table></form>";
	
	print "<script language=\"javascript\">
		$(document).ready(function() {
			$(\"#$id\").dataTable( {paging: false
	    		, \"order\": [[ 1, \"desc\" ]]});
		}
		); 
	</script>";
}





function repartition_concepts_tableau_patient_resume ($patient_num,$id,$filtre_phenotype_genotype,$donnees_reelles_ou_pref) {
	global $dbh,$user_num_session;

	$i=0;
	print "<table class=\"tablefin\" id=\"$id\" width=\"100%\">";
	print "<thead>
		<tr>
			<th style=\"width: 151px;\">Concepts</th>
			<th style=\"width: 51px;\">Nb documents</th>
			<th>Resume</th>
			<th>validation</th>
		</tr>
		</thead>
		<tbody>";
		
	if ($donnees_reelles_ou_pref=='reelles') {
		$requete=" select dwh_enrsem.concept_code,concept_str_found concept_str,count(distinct document_num) nb
		 from dwh_enrsem,dwh_thesaurus_enrsem
		 where dwh_enrsem.patient_num=$patient_num
		 and context='patient_text'
		 and certainty=1
		 and dwh_enrsem.concept_code=dwh_thesaurus_enrsem.concept_code
		 $filtre_phenotype_genotype
		 group by dwh_enrsem.concept_code,concept_str_found
		 order by count(*) desc
		  ";
	} else {
		$requete=" select dwh_enrsem.concept_code, concept_str,listagg(document_num,',') within group (order by document_num)  as liste_document ,  count( distinct document_num) nb
		 from dwh_enrsem,dwh_thesaurus_enrsem
		 where dwh_enrsem.patient_num=$patient_num
		 and context='patient_text'
		 and certainty=1
		 and dwh_enrsem.concept_code=dwh_thesaurus_enrsem.concept_code
		 and pref='Y'
		 $filtre_phenotype_genotype
		 group by dwh_enrsem.concept_code,concept_str
		 order by count(*) desc
		  ";
	}
		
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$concept_str=$r['CONCEPT_STR'];
		$concept_code=$r['CONCEPT_CODE'];
		$nb=$r['NB'];
		$liste_document=$r['LISTE_DOCUMENT'];
		$i++;
		if ($i<300) {
		
			$resume=appercu_liste_document ($liste_document,$concept_str);
			print "<tr><td>$concept_str</td><td>$nb</td><td>$resume</td><td></td></tr>";
		}
	}
	print "</tbody></table>";
	
	print "<script language=\"javascript\">
		$(document).ready(function() {
			$(\"#$id\").dataTable( {
			\"columns\": [
				{ \"width\": \"151px\" },
				{ \"width\": \"151px\" },
				null,
				{ \"width\": \"51px\" }
			  ],
			paging: false
	    		, \"order\": [[ 1, \"desc\" ]]});
		}
		); 
	</script>";
}

function repartition_concepts_tableau_cohorte ($cohort_num,$id,$filtre_phenotype_genotype,$donnees_reelles_ou_pref) {
	global $dbh,$user_num_session;
	
	$i=0;
	print "<form autocomplete=\"off\"><table class=\"tablefin\" id=\"$id\" width=\"800\">";
	print "<thead>
		<tr>
			<th>".get_translation('CONCEPTS','Concepts')."</th>
			<th>".get_translation('COUNT_PATIENT_NUMBER_SHORT','Nb patients')."</th>
			<th>".get_translation('COUNT_OCCURENCES','Nb occurences')."</th>
		</tr>
		</thead>
		<tbody>";
		
	if ($donnees_reelles_ou_pref=='reelles') {
		$requete=" select dwh_enrsem.concept_code,concept_str_found concept_str,count(distinct patient_num) nb_patient,count(*) nb
		 from dwh_enrsem,dwh_thesaurus_enrsem
		 where dwh_enrsem.patient_num in (select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1)
		 and context='patient_text'
		 and certainty=1
		 and dwh_enrsem.concept_code=dwh_thesaurus_enrsem.concept_code
		 $filtre_phenotype_genotype
		 group by dwh_enrsem.concept_code,concept_str_found
		 order by count(*) desc
		  ";
	} else {
		$requete=" select dwh_enrsem.concept_code, concept_str,count(distinct patient_num) nb_patient,count(*) nb
		 from dwh_enrsem,dwh_thesaurus_enrsem
		 where dwh_enrsem.patient_num in (select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1)
		 and context='patient_text'
		 and certainty=1
		 and dwh_enrsem.concept_code=dwh_thesaurus_enrsem.concept_code
		 and pref='Y'
		 $filtre_phenotype_genotype
		 group by dwh_enrsem.concept_code,concept_str
		 order by count(*) desc
		  ";
	}
		
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$concept_str=$r['CONCEPT_STR'];
		$concept_code=$r['CONCEPT_CODE'];
		$nb=$r['NB'];
		$nb_patient=$r['NB_PATIENT'];
		$i++;
		if ($i<=500) {
			print "<tr><td>$concept_str</td><td>$nb_patient</td><td>$nb</td></tr>";
		}
	}
	print "</tbody></table></form>";
}

function max_distance_concepts ($tmpresult_num) {
	global $dbh,$user_num_session;
	
	$req="select  max(distance) as distance_max  from 
		dwh_thesaurus_enrsem_graph a, 
		dwh_enrsem b 
	         where a.concept_code_father='RACINE' and
	         a.concept_code_son=b.concept_code and
	      	 b.patient_num in ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num)  ";
	$selval=oci_parse($dbh,"$req ");
	oci_execute($selval);
	$res=oci_fetch_array($selval,OCI_RETURN_NULLS+OCI_ASSOC);
	$distance_max=$res['DISTANCE_MAX'];
	
	return $distance_max;
}


function affiche_heatmap_concepts ($tmpresult_num,$phenotype_genotype,$tableau,$graph,$donnees_reelles_ou_pref,$type,$distance) {
	global $dbh,$datamart_num,$user_num_session;
	if ($phenotype_genotype!='') {
		$filtre_phenotype_genotype=" and $phenotype_genotype=1 ";
	}
	
	$i=0;
	$nb_c=0;
	if ($type=='document') {
		if ($donnees_reelles_ou_pref=='reelles') {
			$requete=" select distinct dwh_enrsem.concept_code,concept_str_found as concept_str,patient_num
			 from dwh_tmp_result_$user_num_session, dwh_enrsem,dwh_thesaurus_enrsem
			 where tmpresult_num=$tmpresult_num
			 and dwh_tmp_result_$user_num_session.document_num=dwh_enrsem.document_num
			 and context='patient_text'
			 and certainty=1
			 and dwh_enrsem.concept_code=dwh_thesaurus_enrsem.concept_code
			 $filtre_phenotype_genotype
			  ";
		} else {
			if ($distance==10) {
				 $requete=" select  distinct concept_code, concept_str,patient_num from (
					select concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
					     where a.concept_code_father='RACINE'   and
					   document_num in ( select document_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and 
					         a.distance<=$distance and
					      context='patient_text' and
					      certainty=1 and
					   a.concept_code_son=c.concept_code 
					     ) t, dwh_thesaurus_enrsem
					     where concept_code_son=concept_code and pref='Y'
					      $filtre_phenotype_genotype
					 	";
			} else {
				 $requete=" select  distinct concept_code, concept_str,patient_num from (
					select a.concept_code_son ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_thesaurus_enrsem_graph b, dwh_enrsem c
					     where a.concept_code_father='RACINE'  and
					   document_num in ( select document_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num ) and 
					         a.distance=$distance and
					   a.concept_code_son=b.concept_code_father and
					      context='patient_text' and
					      certainty=1 and
					     b.concept_code_son=c.concept_code 
					    union
					select concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
					     where a.concept_code_father='RACINE'   and
					   document_num in ( select document_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and 
					         a.distance<=$distance and
					      context='patient_text' and
					      certainty=1 and
					   a.concept_code_son=c.concept_code 
					     ) t, dwh_thesaurus_enrsem
					     where concept_code_son=concept_code and pref='Y'
					      $filtre_phenotype_genotype
					 	";
			}
				     
		}
	} else {
		if ($donnees_reelles_ou_pref=='reelles') {
			$requete=" select distinct dwh_enrsem.concept_code,concept_str_found as concept_str,patient_num
			 from (select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) t, dwh_enrsem,dwh_thesaurus_enrsem
			 where t.patient_num=dwh_enrsem.patient_num
			 and context='patient_text'
			 and certainty=1
			 and dwh_enrsem.concept_code=dwh_thesaurus_enrsem.concept_code
			 $filtre_phenotype_genotype
			  ";
		} else {
			if ($distance==10) {
				 $requete=" select  distinct concept_code, concept_str,patient_num from (
					select concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
					     where a.concept_code_father='RACINE'   and
					   patient_num in ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and 
					         a.distance<=$distance and
					      context='patient_text' and
					      certainty=1 and
					   a.concept_code_son=c.concept_code 
					     ) t, dwh_thesaurus_enrsem
					     where concept_code_son=concept_code and pref='Y'
					      $filtre_phenotype_genotype
					";
			} else {
				 $requete=" select  distinct concept_code,concept_str,patient_num from (
					select a.concept_code_son ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_thesaurus_enrsem_graph b, dwh_enrsem c
					     where a.concept_code_father='RACINE'  and
					   patient_num in ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num ) and 
					         a.distance=$distance and
					   a.concept_code_son=b.concept_code_father and
					      context='patient_text' and
					      certainty=1 and
					     b.concept_code_son=c.concept_code 
					    union
					select concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
					     where a.concept_code_father='RACINE'   and
					   patient_num in ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and 
					         a.distance<=$distance and
					      context='patient_text' and
					      certainty=1 and
					   a.concept_code_son=c.concept_code 
					     ) t, dwh_thesaurus_enrsem
					     where concept_code_son=concept_code and pref='Y'
					      $filtre_phenotype_genotype
					";
			}
		}
	}
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$concept_str=$r['CONCEPT_STR'];
		$concept_code=$r['CONCEPT_CODE'];
		$patient_num=$r['PATIENT_NUM'];
		$i++;
		$tableau_nb_patient_par_concept[$concept_str]++;
		$tableau_concept_patient_num[$concept_str][$patient_num]=1;
		$tableau_nb_concept_par_patient[$patient_num]++;
	}
	arsort($tableau_nb_patient_par_concept);
	arsort($tableau_nb_concept_par_patient);
	
	$i_ligne=0;
	$res= "<table border=\"1\" class=\"tableheatmap\" id=\"id_table_heatmap_concept\">";
	foreach ($tableau_nb_patient_par_concept as $concept_str => $nb) {
		if ($tableau_nb_patient_par_concept[$concept_str]>1) {
		 	$i_ligne++;
			$res.= "<tr id=\"id_ligne_table_couleur_$i_ligne\"><th class=\"tableheatmap\" nowrap=\"nowrap\"  onclick=\"encadrer_rouge_ligne('id_ligne_table_couleur_$i_ligne');\">$concept_str</th>";
			foreach ($tableau_nb_concept_par_patient as $patient_num => $nb) {
				if ($tableau_concept_patient_num[$concept_str][$patient_num]==1) {
					$res.= "<td style=\"background-color:#7CB5EC;width:1px;cursor:pointer;\" class=\"tableheatmap td_heatmap\"></td>";
				} else {
					$res.= "<td style=\"background-color:white;width:1px;cursor:pointer;\" class=\"tableheatmap td_heatmap\"></td>";
				}
			}
		}
	}
	$res.= "</table>";
	return $res;
}














$tableau_result_liste_combinaison_concepts=array();


function liste_combinaison_concepts ($tmpresult_num,$phenotype_genotype,$donnees_reelles_ou_pref,$type,$distance) {
	global $dbh,$datamart_num,$user_num_session;
	global $tableau_result_liste_combinaison_concepts;
	if ($phenotype_genotype!='') {
		$filtre_phenotype_genotype=" and $phenotype_genotype=1 ";
	}
	$i=0;
	$nb_c=0;
	if ($type=='document') {
		if ($donnees_reelles_ou_pref=='reelles') {
			$requete=" select distinct dwh_enrsem.concept_code,concept_str_found as concept_str,patient_num
			 from dwh_tmp_result_$user_num_session, dwh_enrsem,dwh_thesaurus_enrsem
			 where tmpresult_num=$tmpresult_num
			 and dwh_tmp_result_$user_num_session.document_num=dwh_enrsem.document_num
			 and context='patient_text'
			 and certainty=1
			 and dwh_enrsem.concept_code=dwh_thesaurus_enrsem.concept_code
			 $filtre_phenotype_genotype
			  ";
		} else {
			if ($distance==10) {
				 $requete=" select distinct dwh_enrsem.concept_code, concept_str,patient_num
						 from dwh_tmp_result_$user_num_session, dwh_enrsem,dwh_thesaurus_enrsem
						 where tmpresult_num=$tmpresult_num
						 and dwh_tmp_result_$user_num_session.document_num=dwh_enrsem.document_num
						 and context='patient_text'
						 and certainty=1
						 and dwh_enrsem.concept_code=dwh_thesaurus_enrsem.concept_code and pref='Y'
						 $filtre_phenotype_genotype 
						$filtre_phenotype_genotype
					 	";
			} else {
				 $requete=" select  distinct concept_code, concept_str,patient_num from (
					select a.concept_code_son ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_thesaurus_enrsem_graph b, dwh_enrsem c
					     where a.concept_code_father='RACINE'  and
					   document_num in ( select document_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num ) and 
					         a.distance=$distance and
					   a.concept_code_son=b.concept_code_father and
						  context='patient_text' and
						  certainty=1 and
					     b.concept_code_son=c.concept_code 
					    union
					select concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
					     where a.concept_code_father='RACINE'   and
					   document_num in ( select document_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and 
					         a.distance<=$distance and
						  context='patient_text' and
						  certainty=1 and
					   a.concept_code_son=c.concept_code 
					     ) t, dwh_thesaurus_enrsem
					     where concept_code_son=concept_code and pref='Y'
					      $filtre_phenotype_genotype
					 	";
			}
				     
		}
	} else {
		if ($donnees_reelles_ou_pref=='reelles') {
			$requete=" select distinct dwh_enrsem.concept_code,concept_str_found as concept_str,patient_num
			 from (select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) t, dwh_enrsem,dwh_thesaurus_enrsem
			 where t.patient_num=dwh_enrsem.patient_num and
			  context='patient_text' and
			  certainty=1 and
			  dwh_enrsem.concept_code=dwh_thesaurus_enrsem.concept_code
			 $filtre_phenotype_genotype
			  ";
		} else {
			if ($distance==10) {
				 $requete=" select  distinct concept_code, concept_str,patient_num from (
					select concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
					     where a.concept_code_father='RACINE'   and
					   patient_num in ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and 
					         a.distance<=$distance and
						  context='patient_text' and
						  certainty=1 and
					   a.concept_code_son=c.concept_code 
					     ) t, dwh_thesaurus_enrsem
					     where concept_code_son=concept_code and pref='Y'
					      $filtre_phenotype_genotype
					";
			} else {
				 $requete=" select  distinct concept_code, concept_str,patient_num from (
					select a.concept_code_son ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_thesaurus_enrsem_graph b, dwh_enrsem c
					     where a.concept_code_father='RACINE'  and
					   patient_num in ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num ) and 
					         a.distance=$distance and
					   a.concept_code_son=b.concept_code_father and
						  context='patient_text' and
						  certainty=1 and
					     b.concept_code_son=c.concept_code 
					    union
					select concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
					     where a.concept_code_father='RACINE'   and
					   patient_num in ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and 
					         a.distance<=$distance and
						  context='patient_text' and
						  certainty=1 and
					   a.concept_code_son=c.concept_code 
					     ) t, dwh_thesaurus_enrsem
					     where concept_code_son=concept_code and pref='Y'
					      $filtre_phenotype_genotype
					";
			}
		}
	}
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$concept_str=$r['CONCEPT_STR'];
		$concept_code=$r['CONCEPT_CODE'];
		$patient_num=$r['PATIENT_NUM'];
		if ($concept_str!='Syndrome') {
			$i++;
			$tableau_nb_patient_par_concept[$concept_str]++;
			$traduction_code[$concept_code]=$concept_str;
			$liste_patient_num_par_concept[$concept_str].="$patient_num;";
		}
	}
	arsort($tableau_nb_patient_par_concept);
	
	$i=0;
	$tableau_concept_final=array();
	foreach ($tableau_nb_patient_par_concept as $concept_code => $nb_patient) {
		if ($i<60 && $i>30) {
			$tableau_concept_final[]=$concept_code;
		}
		$i++;
	}

	resolve($tableau_concept_final, 4);
	
	foreach ($tableau_result_liste_combinaison_concepts as $liste_concept) {
		$tab=array_filter(explode(';',$liste_concept));
		if (count($tab)>3) {
			$code_preced='';
			$intersect=array();
			foreach ($tab as $concept_code) {
				if ($code_preced=='') {
					$intersect=array_filter(explode(';',$liste_patient_num_par_concept[$concept_code]));
				} else {
					$t2=array_filter(explode(';',$liste_patient_num_par_concept[$concept_code]));
					$intersect = array_intersect($intersect,$t2);
				}
				$code_preced=$concept_code;
			}
			if (count($intersect)>1) {
				print "$liste_concept : ".count($intersect)."<br>";
			}
		}
	}
	
	return $res;
}



function resolve($el, $depth, $comb=array()){
	global $tableau_result_liste_combinaison_concepts;
	if ($depth < 1) {
		$combi= implode(';', $comb);
		$tableau_result_liste_combinaison_concepts[]=$combi;
		return;
	}
	$combi= implode(';', $comb);
	$tableau_result_liste_combinaison_concepts[]=$combi;
	$lim = count($el);
	foreach ($el as $i => $num){
		$newComb = $comb;
		$newComb[] = $num;
		unset($el[$i]);
		resolve($el, $depth-1, $newComb);
	}
}















?>