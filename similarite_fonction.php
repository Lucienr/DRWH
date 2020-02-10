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
function calcul_similarite_tfidf ($distance,$limite_count_concept_par_patient_num,$limite_longueur_vecteur,$limite_valeur_similarite,$limite_min_nb_patient_par_code,$filtre_sql,$patient_num_principal) {
	global $dbh,$fichier_dot,$deja_patient_num,$tableau_cluster,$tableau_cluster_liste_patient_num,$tableau_code_autorise,$tableau_html_liste_patients,$user_num_session,$tableau_html_liste_clusters,$tableau_html_liste_concepts_patient_similaire,$liste_type_semantic;
	global $phrase_parametrage,$req_certitude,$req_contexte,$anonyme,$nbpatient_limite,$cohort_num_exclure,$process_num,$tableau_patient_num_liste_code,$tab_concept_code_principal,$table_concept_code_weight_principal;
	
	print "nbpatient_limite :$nbpatient_limite<br>"; 
	$sel=oci_parse($dbh, "select count_patient from dwh_info_load where year is null and document_origin_code is null");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS);
	$nb_pat_total_entrepot=$r['COUNT_PATIENT'];
	
	print benchmark ( 'debut 1' );
	$tableau_code_nb_pat=array();
	$tableau_code_libelle=array();
	$tableau_patient_num=array();
	$tableau_patient_num_code_nb_concept=array();
	$tableau_patient_num_nb_concept_total=array();
	$tableau_patient_num_tous_les_codes_nb_concept_par_code=array();
	$tableau_patient_num_tous_les_codes_nb_concept_total=array();
	
	if ($patient_num_principal=='VIRTUAL') {
		foreach ($tab_concept_code_principal as $concept_code) {
			if ($concept_code!='') {
				$tableau_code_nb_pat[$concept_code]++;
				$tf=$table_concept_code_weight_principal[$concept_code];
				$tableau_patient_num_code_nb_concept['VIRTUAL'][$concept_code]=$tf;
				$tableau_patient_num_nb_concept_total['VIRTUAL']+=$tf;
				$tableau_patient_num['VIRTUAL']='ok';
			}
		}
	}
	print_r($tableau_patient_num_code_nb_concep);
	update_process ($process_num,'0',get_translation('PROCESS_EXTRACT_PATIENTS','Extraction des patients'),'',$user_num_session,"");
	
	// pour le calcul du TF/IDF
	if ($distance==10) {
		     $requete=" select  concept_code,patient_num,certainty,count(*) as TF from dwh_enrsem c
		     where  context='patient_text' $req_certitude $filtre_sql   and concept_code!='C0012634'  and concept_code!='C0012155' and concept_code!='C0039082'
                  	and concept_str_found not like '% norma%'
		     	and concept_code in (select concept_code from dwh_thesaurus_enrsem where phenotype =1)
		     group by  concept_code, patient_num,certainty
			order by certainty
		      ";
	} else {
		$requete=" select  concept_code,patient_num,certainty,count(*) as TF 
			from (
				select  enrsem_num,a.concept_code_son ,patient_num,certainty from dwh_thesaurus_enrsem_graph a, dwh_thesaurus_enrsem_graph b, dwh_enrsem c
				where a.concept_code_father='RACINE'  
		    		  $filtre_sql  and
				a.distance=$distance and
				a.concept_code_son=b.concept_code_father 
				
				$req_certitude
				$req_contexte
				and b.concept_code_son=c.concept_code 
	                  	and concept_str_found not like '% norma%'
			     	and c.concept_code in (select concept_code from dwh_thesaurus_enrsem where phenotype =1)
				union
				select  enrsem_num,concept_code_son  ,patient_num,certainty from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
				where a.concept_code_father='RACINE'  
		    		  $filtre_sql   and 
				a.distance<=$distance 
				$req_certitude
				$req_contexte
				and a.concept_code_son=c.concept_code 
				and context='patient_text'
	                  	and concept_str_found not like '% norma%'
			     	and c.concept_code in (select concept_code from dwh_thesaurus_enrsem where phenotype =1)
				) t,
			dwh_thesaurus_enrsem
			where 
				concept_code_son=concept_code   and concept_code!='C0012634'  and concept_code!='C0012155' and concept_code!='C0039082'
			group by  concept_code,patient_num,certainty
			order by concept_code,certainty
		      ";
	}
	$p=0;
	print "\n$requete\n";
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$concept_code=$r['CONCEPT_CODE'];
		$patient_num=$r['PATIENT_NUM'];
		$tf=$r['TF'];
		$certainty=$r['CERTAINTY'];
		if ($tableau_patient_num[$patient_num]=='') {
			$p++;
		}
		if ($tableau_code_autorise[$concept_code]!='') {
			$tableau_code_nb_pat[$concept_code]++;
			$tableau_patient_num_code_nb_concept[$patient_num][$concept_code]=$tf*$certainty;
			$tableau_patient_num_nb_concept_total[$patient_num]+=$tf;
			if ($patient_num!=$patient_num_principal) {
				$tableau_patient_num_liste_code[$patient_num].="$concept_code;";
			}
			$tableau_patient_num[$patient_num]='ok';
		}
		if ($p==10000) {
			update_process ($process_num,'0',get_translation('PROCESS_EXTRACT_PATIENTS','Extraction des patients')." ".count($tableau_patient_num)." patients",'',$user_num_session,"");
			$p=0;
		}
	}
	$nb_pat_total=count($tableau_patient_num);
	
	foreach ($tableau_patient_num_liste_code as $patient_num => $liste_code) {
		$liste_code=preg_replace("/;$/","",$liste_code);
		$tableau_patient_num_liste_code[$patient_num]=$liste_code;
	}
	
	print "\n\n".benchmark ( 'debut 2' )."\n\n";
	print "nb_pat_total : $nb_pat_total<br>";
	
	$tableau_final_code=array();
	foreach ($tableau_code_nb_pat as $concept_code => $nb_pat) {
		if ($nb_pat>=$limite_min_nb_patient_par_code) {
			$tableau_final_code[$concept_code]=$nb_pat;
		}
	}
	print "tableau_final_code : ".count($tableau_final_code)."<br>";

	print "\n\n".benchmark ( 'debut 3' )."\n\n";
	
	
	update_process ($process_num,'0',get_translation('PROCESS_PATIENTS_TO_VECTORS','Vectorisation des patients'),'',$user_num_session,"");
	
	$tableau_longueur_vecteur=array();
	$tableau_nb_concept_patient_num=array();
	///creation des vecteurs et calcul des longueurs de vecteur 
	foreach ($tableau_patient_num as $patient_num => $ok) {
		if (count($tableau_patient_num_code_nb_concept[$patient_num]) >= $limite_count_concept_par_patient_num || $patient_num==$patient_num_principal) { 
			$i=0;
			$longueur_vecteur=0;
			foreach ($tableau_final_code as $concept_code => $nb_pat) {
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
				$tableau_nb_concept_patient_num[$patient_num]=$longueur_vecteur;
			} else {
				$i=0;
				foreach ($tableau_final_code as $concept_code => $nb_pat) {
					unset($tableau_patient_num_vecteur["$patient_num;$i"]);
					$i++;
				}
			}
		}
	}

	$max_width=15;
	$max_length=2;
	print "\n\n".benchmark ( 'debut 4' )."\n\n";
	print "tableau_longueur_vecteur nb patient_num: ".count($tableau_longueur_vecteur)."<br>"; 

//	$coef_tfidf=3;
//	$coef_freq=1;
	$tableau_patient_num_final=array();
	$tableau_cluster_liste_patient_num=array();
	$tableau_weight=array();
	$tableau_length=array();
	//// Option pour calculer similarite a partir d'un patient principal ////
	update_process ($process_num,'0',get_translation('PROCESS_COMPUTE_SIMILARITY_INDEX_PATIENT','Calcul de similarite avec le patient Index'),'',$user_num_session,"");
	foreach ($tableau_longueur_vecteur as $patient_num_2 => $longeur_v_2) {
		if ($patient_num_principal!=$patient_num_2) {
		
			/// similarite par le cosinus µ///
			$produit_scalaire=0;
			for ($i=0;$i<count($tableau_final_code);$i++) {
				//$produit_scalaire+=$tableau_patient_num_vecteur["$patient_num_principal;$i"]*$tableau_patient_num_vecteur["$patient_num_2;$i"];
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
				
				$similarite_normalise=round(($similarite-$limite_valeur_similarite) *$max_width/(100-$limite_valeur_similarite)); // calcul de l'epaisseur du trait
				if ($similarite_normalise<=0) {
					$similarite_normalise=1;
				}
				
				$tableau_similarite_patient_num_principal[$patient_num_2]=$similarite;
				$tableau_similarite_normalise_patient_num_principal[$patient_num_2]=$similarite_normalise;
				$tableau_weight[$patient_num_2]=$similarite;
				$tableau_length[$patient_num_2]=$max_length-round(($similarite-$limite_valeur_similarite) *$max_length/(100-$limite_valeur_similarite),2); 
				
				$tableau_similarite_label["$patient_num_2"]="$phrase_similarite";
			}
		}
	}
	print "tableau_similarite nb patient_num: ".count($tableau_similarite)."<br>nbpatient_limite : $nbpatient_limite<br>"; 
	update_process ($process_num,'0',get_translation('PROCESS_TERMFREQUENCY','Calcul des frequences'),'',$user_num_session,"");
	$i=0;
	$table_type_semantic=array();
	$tableau_patient_num_similaire=array();
	$tableau_patient_num_similaire_code_nb_pat=array();
	$tableau_patient_num_similaire_code_nb_concept=array();
	$tableau_anonyme=array();
	$tableau_anonyme[$patient_num_principal]='Patient Index';
	$tableau_patient_num_similaire[0]=$patient_num_principal;
	arsort($tableau_similarite_patient_num_principal);
	foreach ($tableau_similarite_patient_num_principal as $patient_num => $similarite) {
		$i++;
		if ($i<=$nbpatient_limite) {
			$similarite_normalise=$tableau_similarite_normalise_patient_num_principal[$patient_num];
			$weight=$similarite/100;
			$jpgraph_connexion.="$patient_num_principal -- $patient_num  [weight=".$tableau_weight[$patient_num].",len=".$tableau_length[$patient_num].", penwidth=$similarite_normalise, label=\"".$tableau_similarite_label[$patient_num]."\",fontcolor=\"black\",URL=\"javascript:affiche_intersect('$patient_num_principal','$patient_num','','".$tableau_similarite_label[$patient_num]."');\"] ;\n";
			$tableau_patient_num_temporaire[$patient_num]=$tableau_longueur_vecteur[$patient_num];
			$tableau_patient_num_final[$patient_num_principal]='ok';
			$tableau_patient_num_final[$patient_num]='ok';
			$tableau_patient_num_similaire[$i]=$patient_num;
			$tableau_anonyme[$patient_num]="Patient $i";
			$tableau_cluster_liste_patient_num[$patient_num_principal].=";$patient_num;$patient_num_principal;";
			$tableau_cluster_liste_patient_num[$patient_num].=";$patient_num_principal;$patient_num;";
			
			
			
			if ($distance==10) {
				$requete=" select  concept_code,patient_num,count(*) as TF from dwh_enrsem c
				     where certainty=1 and context='patient_text' and patient_num=$patient_num    and concept_code!='C0012634'  and concept_code!='C0012155' and concept_code!='C0039082'
	                  			and concept_code in (select concept_code from dwh_thesaurus_enrsem where phenotype=1 or genotype=1)
				     group by  concept_code, patient_num ";
			} else {
				$requete=" select  concept_code,count(*) as TF 
					from (
						select  enrsem_num,a.concept_code_son  from dwh_thesaurus_enrsem_graph a, dwh_thesaurus_enrsem_graph b, dwh_enrsem c
						where a.concept_code_father='RACINE'  
				    		   and patient_num=$patient_num   and
						a.distance=$distance and
						a.concept_code_son=b.concept_code_father and
						 context='patient_text' and
						 certainty=1 and
						b.concept_code_son=c.concept_code 
						union
						select  enrsem_num,concept_code_son   from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
						where a.concept_code_father='RACINE'  
				    		  and patient_num=$patient_num    and 
						a.distance<=$distance and
						 context='patient_text' and
						 certainty=1 and
						a.concept_code_son=c.concept_code 
						) t,
					dwh_thesaurus_enrsem
					where 
						concept_code_son=concept_code  and concept_code!='C0012634'  and concept_code!='C0012155' and concept_code!='C0039082'
					group by concept_code
					order by concept_code
				      ";
			}
			$sel=oci_parse($dbh, $requete);
			oci_execute($sel);
			while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
				$concept_code=$r['CONCEPT_CODE'];
				$tf=$r['TF'];
				$tableau_patient_num_tous_les_codes_nb_concept_par_code[$patient_num][$concept_code]=$tf;
				if ($table_type_semantic[$concept_code]=='') {
					$table_type_semantic[$concept_code]=get_semantic_type_concept ($concept_code,'list');
				}
				$tableau_patient_num_tous_les_codes_nb_concept_total[$patient_num]+=$tf;
				//$tableau_patient_num_tous_les_codes_nb_pat[$concept_code]++;
				$tableau_patient_num_similaire_code_nb_pat[$concept_code]++;
				$tableau_patient_num_similaire_code_nb_concept[$concept_code]+=$tf;
				
			}
		}
	}
	
	$tableau_longueur_vecteur=array();// on vide le tableau pour le remplir qu'avec les patients liés au patient principal
	$tableau_longueur_vecteur=$tableau_patient_num_temporaire;

	print "\n\n".benchmark ( 'debut 5' )."\n\n";

	update_process ($process_num,'0',get_translation('PROCESS_GRAPH_CREATION','Création du graph'),'',$user_num_session,"");
	///creation des vecteurs et calcul des longueurs de vecteur
	foreach ($tableau_longueur_vecteur as $patient_num_1 => $longeur_v_1) {
		foreach ($tableau_longueur_vecteur as $patient_num_2 => $longeur_v_2) {
			if ($patient_num_1!=$patient_num_2 && $patient_num_1!=$patient_num_principal && $patient_num_2!=$patient_num_principal) {
				print "patient_num_1 :$patient_num_1 , patient_num_2 : $patient_num_2\n";
				
				$tableau_patient_num_code_1=explode(";",$tableau_patient_num_liste_code[$patient_num_1]);
				$tableau_patient_num_code_2=explode(";",$tableau_patient_num_liste_code[$patient_num_2]);
				$intersect = array_intersect($tableau_patient_num_code_1,$tableau_patient_num_code_2);
				$seul1 = array_diff($tableau_patient_num_code_1, $tableau_patient_num_code_2);
				$seul2 = array_diff($tableau_patient_num_code_2, $tableau_patient_num_code_1);
				$similarite2=round(100*count($intersect)/ 50,3); //nb de concepts en commun / nb 50

				/// similarite par le cosinus ///
				$produit_longueur=$longeur_v_1*$longeur_v_2;
				if ($produit_longueur>0) {
					$produit_scalaire=0;
					for ($i=0;$i<count($tableau_final_code);$i++) {
						$produit_scalaire+=$tableau_patient_num_vecteur["$patient_num_1;$i"]*$tableau_patient_num_vecteur["$patient_num_2;$i"];
					}
					$similarite=round(100*$produit_scalaire/$produit_longueur);
				} else {
					$similarite=0;
				}

				if ($similarite>=$limite_valeur_similarite) {
					if ($tableau_similarite["$patient_num_1;$patient_num_2"]=='' && $tableau_similarite["$patient_num_2;$patient_num_1"]=='') {
						$tableau_similarite["$patient_num_1;$patient_num_2"]=$similarite;
						$tableau_similarite["$patient_num_2;$patient_num_1"]=$similarite;
						$similarite_normalise=round(($similarite-$limite_valeur_similarite) *$max_width/(100-$limite_valeur_similarite)); // calcul de l'epaisseur du trait
						
						$length=$max_length-round(($similarite-$limite_valeur_similarite) *$max_length/(100-$limite_valeur_similarite),2); 
						if ($similarite_normalise==0) {
							$similarite_normalise=1;
						}
						$tableau_patient_num_final[$patient_num_1]='ok';
						$tableau_patient_num_final[$patient_num_2]='ok';

						$tableau_cluster_liste_patient_num[$patient_num_1].=";$patient_num_2;$patient_num_1;";
						$tableau_cluster_liste_patient_num[$patient_num_2].=";$patient_num_1;$patient_num_2;";

						$weight=$similarite;
						
						$jpgraph_connexion.="$patient_num_1 -- $patient_num_2  [weight=$weight,len=$length, penwidth=$similarite_normalise, label=\"$phrase_similarite\",fontcolor=\"black\",URL=\"javascript:affiche_intersect('$patient_num_1','$patient_num_2','','$phrase_similarite');\"] ;\n";
					}
				}
			}
		}	
	}
		
	
	update_process ($process_num,'0',get_translation('PROCESS_COMPUTE_DISPLAY_TABLE','Calcul du tableau d affichage'),'',$user_num_session,"");
	
	$nb_patient_vp=0;
	$nb_patient_doute=0;
	$tableau_score_total_par_code=array();
	print "\n\n".benchmark ( 'debut 6' )."\n\n";
	$tableau_html_liste_patients="<br><br>
	<img src=\"images/copier_coller.png\" onclick=\"plier_deplier('id_div_tableau_visualisation');plier_deplier('id_div_tableau_copier_coller');fnSelect('id_textarea_copier_coller');\" style=\"cursor:pointer;\" title=\"Copier Coller pour exporter dans excel\" alt=\"Copier Coller pour exporter dans excel\">
	<div id=\"id_div_tableau_visualisation\" style=\"display:block\">
	<table border=\"0\" class=\"tablefin\" id=\"id_tableau_similarite_patient\"><thead><th width=\"200px\">Patient</th><th width=\"10px\">Similarité</th><th width=\"10px\">Cohortes</th><th width=\"40%\">Concepts communs</th><th>Concepts patient</th></thead><tbody>";
	foreach ($tableau_patient_num_similaire as $patient_num ) {
		if ($anonyme=='non') {
			$tableau_user_name[$patient_num]=afficher_patient ($patient_num,'lastname firstname','','','similarite');
		} else {
			$tableau_user_name[$patient_num]=$tableau_anonyme[$patient_num];
			afficher_patient ($patient_num,'patient','','','similarite');
		}
		$jpgraph_connexion.="$patient_num [label=\"".$tableau_user_name[$patient_num]."\",URL=\"patient.php?patient_num=$patient_num\",target=\"_blank\"] ;\n";

		if ($patient_num!=$patient_num_principal) {
			$tableau_tous_les_codes_nb_concept_par_code=array();
			$tableau_tous_les_codes_nb_concept_par_code=$tableau_patient_num_tous_les_codes_nb_concept_par_code[$patient_num];
		
			arsort($tableau_tous_les_codes_nb_concept_par_code);
			
			$tableau_patient_num_code_1=explode(";",$tableau_patient_num_liste_code[$patient_num_principal]);
			$tableau_patient_num_code_2=explode(";",$tableau_patient_num_liste_code[$patient_num]);
			
			$patient=$tableau_user_name[$patient_num];
			/// liste mots en commun 
			$nb_mot=0;
			$intersect=array();
			$liste_concept_intersect_suite='';
			$liste_concept_intersect='';
			$intersect = array_intersect($tableau_patient_num_code_1,$tableau_patient_num_code_2);
			foreach ($tableau_tous_les_codes_nb_concept_par_code as $concept_code =>$nb_concept) {
			//foreach ($intersect as $concept_code) {
				if (in_array($concept_code, $intersect)) {
					$sel=oci_parse($dbh, "select concept_str from dwh_thesaurus_enrsem where concept_code='$concept_code' and pref='Y'");
					oci_execute($sel);
					$r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS);
					$concept_str=$r['CONCEPT_STR'];
					$nb_mot++;
					if ($nb_mot<=25) {
						$liste_concept_intersect.= " $concept_str (x$nb_concept)<br>";
					} else {
						$liste_concept_intersect_suite.= " $concept_str (x$nb_concept)<br>";
					}
				}
			}
			#$liste_concept_intersect=substr($liste_concept_intersect,0,-2);
			if ($nb_mot>35) {
				$liste_concept_intersect=$liste_concept_intersect."<a onclick=\"plier_deplier('id_liste_concept_intersect_suite_$patient_num');return false;\">display all the concepts</a><span style=\"display:none\"  id='id_liste_concept_intersect_suite_$patient_num'>$liste_concept_intersect_suite</span>";
			} else {
				$liste_concept_intersect=$liste_concept_intersect.$liste_concept_intersect_suite;
			}
			
			/// liste mots pas en commun 
			$max_tf_idf=0;
			$tableau_calcul_tfidf=array();
			#foreach ($tableau_patient_num_tous_les_codes_nb_concept_par_code[$patient_num] as $concept_code =>$nb_concept) {
			foreach ($tableau_tous_les_codes_nb_concept_par_code as $concept_code =>$nb_concept) {
				if ($concept_code!='') {
					$sel=oci_parse($dbh, "select concept_str,count_patient from dwh_thesaurus_enrsem where concept_code='$concept_code' and pref='Y'");
					oci_execute($sel);
					$r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS);
					$concept_str=$r['CONCEPT_STR'];
					$nb_patient=$r['COUNT_PATIENT'];
					
					$tf=$nb_concept/$tableau_patient_num_tous_les_codes_nb_concept_total[$patient_num]; // nb fois le concept_code dans le patient / nb de fois tous les codes du patient
					//$idf=log($nb_pat_total/$tableau_patient_num_tous_les_codes_nb_pat[$concept_code]); // 
					//$idf=log(count($tableau_patient_num_tous_les_codes_nb_concept_total)/$tableau_patient_num_tous_les_codes_nb_pat[$concept_code]); // log (nb total de patients preselectionnes /nb patient avec ce concept_code dans toute la preselection de patient
					$idf=log($nb_pat_total_entrepot/$nb_patient); // log (nb total de patient dans le panel selectionne / nb patient avec ce concept_code dans tout l'entrepot
					$tf_idf_augmente=round(5*$tf*$idf,3);
					$tf_idf=round($tf*$idf,3);
					$tableau_calcul_tfidf[$concept_code]=$tf_idf_augmente;
					$tableau_score_total_par_code[$concept_code]+=$tf_idf;
					
					if ($max_tf_idf<$tf_idf_augmente) {
						$max_tf_idf=$tf_idf_augmente;
					}
				}
			}
			
			
			
			/// GENE OR GENOME
			$liste_concept_patient_hors_intersect_gene='';
			arsort($tableau_calcul_tfidf);
			foreach ($tableau_calcul_tfidf as $concept_code =>$tf_idf) {
				if ($tableau_patient_num_code_nb_concept[$patient_num_principal][$concept_code]=='') {
					$score_normalise=round($tf_idf *5/$max_tf_idf,2); // calcul de la taile du text
					if ($score_normalise<0.8) {
						$score_normalise=0.8;
					}
					if ($table_type_semantic[$concept_code]=='Gene or Genome') {
						$sel=oci_parse($dbh, "select concept_str from dwh_thesaurus_enrsem where concept_code='$concept_code' and pref='Y'");
						oci_execute($sel);
						$r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS);
						$concept_str=$r['CONCEPT_STR'];
						$liste_concept_patient_hors_intersect_gene.= "<span style=\"font-size:".$score_normalise."em;\">$concept_str</span><br>";
					}
				}
			}
			/// Disease or syndrome
			
			$nb_mot_disease_syndrome=0;
			$liste_concept_patient_hors_intersect_disease_syndrome='';
			$liste_concept_patient_hors_intersect_disease_syndrome_suite='';
			arsort($tableau_calcul_tfidf);
			foreach ($tableau_calcul_tfidf as $concept_code =>$tf_idf) {
				if ($tableau_patient_num_code_nb_concept[$patient_num_principal][$concept_code]=='') {
					$score_normalise=round($tf_idf *3/$max_tf_idf,2); // calcul de la taile du text
					if ($score_normalise<0.8) {
						$score_normalise=0.8;
					}
					if ($table_type_semantic[$concept_code]=='Disease or Syndrome') {
						$nb_mot_disease_syndrome++;
						$sel=oci_parse($dbh, "select concept_str from dwh_thesaurus_enrsem where concept_code='$concept_code' and pref='Y'");
						oci_execute($sel);
						$r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS);
						$concept_str=$r['CONCEPT_STR'];
						if ($nb_mot_disease_syndrome<=25) {
							$liste_concept_patient_hors_intersect_disease_syndrome.= "<span style=\"font-size:".$score_normalise."em;\">$concept_str</span><br>";
						} else {
							$liste_concept_patient_hors_intersect_disease_syndrome_suite.= "<span style=\"font-size:".$score_normalise."em;\">$concept_str</span><br>";
						}
					}
				}
			}
			
			/// Sign
			$liste_concept_patient_hors_intersect_sign='';
			$liste_concept_patient_hors_intersect_sign_suite='';
			$nb_mot_sign=0;
			arsort($tableau_calcul_tfidf);
			foreach ($tableau_calcul_tfidf as $concept_code =>$tf_idf) {
				if ($tableau_patient_num_code_nb_concept[$patient_num_principal][$concept_code]=='') {
					$score_normalise=round($tf_idf *3/$max_tf_idf,2); // calcul de la taile du text
					if ($score_normalise<0.8) {
						$score_normalise=0.8;
					}
					if ($table_type_semantic[$concept_code]!='Disease or Syndrome' && $table_type_semantic[$concept_code]!='Gene or Genome') {
						$nb_mot_sign++;
						$sel=oci_parse($dbh, "select concept_str from dwh_thesaurus_enrsem where concept_code='$concept_code' and pref='Y'");
						oci_execute($sel);
						$r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS);
						$concept_str=$r['CONCEPT_STR'];
						if ($nb_mot_sign<=25) {
							$liste_concept_patient_hors_intersect_sign.= "<span style=\"font-size:".$score_normalise."em;\">$concept_str</span><br>";
						} else {
							$liste_concept_patient_hors_intersect_sign_suite.= "<span style=\"font-size:".$score_normalise."em;\">$concept_str</span><br>";
						}
					}
				}
			}
			
			$liste_concept_patient_hors_intersect= "<table border=\"0\" width=\"100%\"><tr style=\"background-color: transparent;\"><td style=\"vertical-align:top; border: 0px;width:33%;\">";
			if ($liste_concept_patient_hors_intersect_gene!='') {
				$liste_concept_patient_hors_intersect.= "<h3 class=\"similarity_subtitle\">Gene or Genome</h3>$liste_concept_patient_hors_intersect_gene";
			}
			$liste_concept_patient_hors_intersect.= "</td><td style=\"vertical-align:top; border: 0px;width:33%;\">";
			if ($liste_concept_patient_hors_intersect_disease_syndrome!='') {
				$liste_concept_patient_hors_intersect.=  "<h3 class=\"similarity_subtitle\">Disease or syndrome</h3>$liste_concept_patient_hors_intersect_disease_syndrome";
				if ($nb_mot_disease_syndrome>35) {
					$liste_concept_patient_hors_intersect.="<a onclick=\"plier_deplier('id_liste_concept_patient_hors_intersect_disease_syndrome_suite_$patient_num');return false;\">+Display all the concepts</a><span style=\"display:none\" id='id_liste_concept_patient_hors_intersect_disease_syndrome_suite_$patient_num'>$liste_concept_patient_hors_intersect_disease_syndrome_suite</span>";
				} else {
					$liste_concept_patient_hors_intersect.=$liste_concept_patient_hors_intersect_disease_syndrome_suite;
				}
			}
			$liste_concept_patient_hors_intersect.= "</td><td style=\"vertical-align:top; border: 0px;width:33%;\">";
			if ($liste_concept_patient_hors_intersect_sign!='') {
				$liste_concept_patient_hors_intersect.=  "<h3 class=\"similarity_subtitle\">Sign</h3> $liste_concept_patient_hors_intersect_sign";
				if ($nb_mot_sign>35) {
					$liste_concept_patient_hors_intersect.="<a onclick=\"plier_deplier('id_liste_concept_patient_hors_intersect_sign_suite_$patient_num');return false;\">+Display all the concepts</a><span style=\"display:none\" id='id_liste_concept_patient_hors_intersect_sign_suite_$patient_num'>$liste_concept_patient_hors_intersect_sign_suite</span>";
				} else {
					$liste_concept_patient_hors_intersect.=$liste_concept_patient_hors_intersect_sign_suite;
				}
			}
			
			$liste_concept_patient_hors_intersect.= "</td></tr></table>";
			
			
			/* cohorte d'inclusion pour évaluation */ 
			$cohorte_inclusion='';
			$vrai_positif=0;
			$sel=oci_parse($dbh, "select dwh_cohort.cohort_num,status,title_cohort from dwh_cohort,dwh_cohort_result where patient_num=$patient_num and dwh_cohort.cohort_num=dwh_cohort_result.cohort_num"); 
			oci_execute($sel);
			while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
				$cohort_num=$r['COHORT_NUM'];
				$status=$r['STATUS'];
				$title_cohort=$r['TITLE_COHORT'];
				if ($status=='1') {
					$lib_status="inclu";
				}
				if ($status=='2') {
					$lib_status="en attente";
				}
				if ($status=='0') {
					$lib_status="exclu";
				}
				$cohorte_inclusion .= " <br>$lib_status - $title_cohort";
			}
			/* -*---------------- */
			
			
			$tableau_html_liste_patients.= "<tr>
			<td style=\"vertical-align:top\">
				$patient <a target=\"_blank\" href=\"patient.php?patient_num=$patient_num\"><img width=\"10px\" border=\"0\" src=\"images/dossier_patient.png\"></a><br>
				<a href=\"outils.php?action=comparateur&patient_num_1=$patient_num_principal&patient_num_2=$patient_num\" target=\"_blank\">".get_translation('DISPLAY_COMPARATOR','Afficher le comparateur')."</a></td>
			<td style=\"vertical-align:top\">".$tableau_similarite["$patient_num_principal;$patient_num"]."</td>
			<td style=\"vertical-align:top\">$cohorte_inclusion</td>
			<td style=\"vertical-align:top;width:40%\"><div style=\"column-count: 3;\">$liste_concept_intersect</div></td>
			<td style=\"vertical-align:top;width:40%\"><div>$liste_concept_patient_hors_intersect</div></td>
			</tr>";
			$patient_copier_coller=afficher_patient ($patient_num,'cohorte_textarea','','','similarite');
			$patient_copier_coller=trim($patient_copier_coller);
			$liste_patient_copier_coller.="$patient_copier_coller\t".$tableau_similarite["$patient_num_principal;$patient_num"]."\t$vrai_positif\n";
		}
	}
	$tableau_html_liste_patients.= "</tbody></table></div>";
	
	/* POUR L EVALUATION */
	$tableau_html_liste_patients.= "
	<div id=\"id_div_tableau_copier_coller\" style=\"display:none\">
	<textarea cols=120 rows=40 id=\"id_textarea_copier_coller\">
		$liste_patient_copier_coller
	</textarea>
	</div>
	";	
	/* FIN evaluation */
	
	
	/////// GENES ///////////////
	$tableau_html_liste_concepts_patient_similaire.= "<table border=\"0\" class=\"tablefin\" id=\"id_tableau_liste_concepts_patient_similaire\"><thead><th>Concepts</th><th>Semantic type</th><th>nb concepts</th><th>nb pat</th>
	<th>% nb pat / nb patient entrepot</th>
	<th>Relevance score</th>
	</thead>
	<tbody>";
	foreach ($tableau_patient_num_similaire_code_nb_pat as $concept_code => $nb_pat) {
		if ($tableau_code_autorise[$concept_code]=='') { // on ne garde que les concepts non communs
			$count_concept=$tableau_patient_num_similaire_code_nb_concept[$concept_code];
			$sel=oci_parse($dbh, "select concept_str,count_patient from dwh_thesaurus_enrsem where concept_code='$concept_code' and pref='Y'");
			oci_execute($sel);
			$r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS);
			$concept_str=$r['CONCEPT_STR'];
			$nb_patient=$r['COUNT_PATIENT'];
			$prev_patient=round(100*$nb_pat/$nb_patient,0);
			$score_moyen=round(100*$tableau_score_total_par_code[$concept_code]/$nb_pat);
			$score_tfidf_prevalence=round(100*$tableau_score_total_par_code[$concept_code] * $nb_pat/$nb_patient,5);
			if ($table_type_semantic[$concept_code]=='Gene or Genome') {
				$semantic_type=$table_type_semantic[$concept_code];
			} else if ($table_type_semantic[$concept_code]=='Disease or Syndrome') {
				$semantic_type=$table_type_semantic[$concept_code];
			
			} else {
				$semantic_type="other";
			}
			$tableau_html_liste_concepts_patient_similaire.= "<tr>
			<td>$concept_str</td>
			<td>$semantic_type</td>
			<td>$count_concept</td>
			<td>$nb_pat</td>
			<td>$prev_patient</td>
			<td>$score_tfidf_prevalence</td>
			</tr>";
		}
	}
	$tableau_html_liste_concepts_patient_similaire.= "</tbody></table>";	
	
	
	
	$fichier_dot= "	
	strict  graph cluster_patient {
		ranksep=3;
		ratio=auto;
		 node [fontsize=9,fontcolor=\"black\",shape=\"plaintext\",style = filled,fontfamily=\"Arial\",shape=\"box\"]; 
		overlap=false;
		splines=\"true\";
	         edge [ fontname=Helvetica,  fontsize=9 ];
	        $jpgraph_connexion
	}";	
	
}



function parcours_patient_num($num_cluster,$patient_num) {
	global $tableau_cluster_liste_patient_num,$tableau_cluster,$deja_patient_num;
	
	if ($patient_num!='' && $deja_patient_num[$patient_num]=='') {
		$tableau_cluster[$num_cluster].=";$patient_num;";
	}
	
	$liste_patient_num=$tableau_cluster_liste_patient_num[$patient_num];
	$tpatient_num=array();
	$tpatient_num= array_unique(explode(";",$liste_patient_num));
	foreach ($tpatient_num as $patient_num_local) {
		if ($patient_num_local!='' && $deja_patient_num[$patient_num_local]=='') {
			$tableau_cluster[$num_cluster].=";$patient_num_local;";
			parcours_patient_num($num_cluster,$patient_num_local);
		}
		$deja_patient_num[$patient_num]='ok';
	}
}

function calcul_clustering ($distance,$nb_concept_commun,$limite_count_concept_par_patient_num,$limite_longueur_vecteur,$limite_valeur_similarite,$limite_min_nb_patient_par_code,$filtre_sql) {
	global $dbh,$fichier_dot,$deja_patient_num,$tableau_cluster,$tableau_cluster_liste_patient_num,$tableau_code_autorise,$tableau_html_liste_patients,$user_num_session,$tableau_html_liste_clusters,$tableau_html_liste_concepts_patient_similaire,$liste_type_semantic;
	global $req_certitude,$req_contexte,$anonyme,$coef_tfidf,$coef_freq,$process_num,$tableau_patient_num_liste_code,$csv;
	
	$sel=oci_parse($dbh, "select count_patient from dwh_info_load where year is null and document_origin_code is null");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS);
	$nb_pat_total_entrepot=$r['COUNT_PATIENT'];
	
	print benchmark ( 'debut 1' );
	$tableau_code_nb_pat=array();
	$tableau_code_libelle=array();
	$tableau_patient_num_code_nb_concept=array();
	$tableau_patient_num_nb_concept_total=array();
	$tableau_patient_num_tous_les_codes_nb_concept_par_code=array();
	$tableau_patient_num_tous_les_codes_nb_concept_total=array();
	
	
	
	update_process ($process_num,'0',get_translation('PROCESS_EXTRACT_PATIENTS','Extraction des patients'),'',$user_num_session,"");
	
	// pour le calcul du TF/IDF
	if ($distance==10) {
		     $requete=" select  concept_code,patient_num,certainty,count(*) as TF from dwh_enrsem c
		     where  context='patient_text' $req_certitude $filtre_sql  $req_contexte   and concept_code!='C0012634'  and concept_code!='C0012155' and concept_code!='C0039082'
	                  	and concept_str_found not like '% norma%'
			     	and concept_code in (select concept_code from dwh_thesaurus_enrsem where phenotype =1)
		     group by  concept_code, patient_num,certainty
			order by certainty
		      ";
	} else {
		$requete=" select  concept_code,patient_num,certainty,count(*) as TF 
			from (
				select  enrsem_num,a.concept_code_son ,patient_num,certainty from dwh_thesaurus_enrsem_graph a, dwh_thesaurus_enrsem_graph b, dwh_enrsem c
				where a.concept_code_father='RACINE'  
		    		  $filtre_sql  and
				a.distance=$distance and
				a.concept_code_son=b.concept_code_father 
				$req_certitude
				$req_contexte
				and b.concept_code_son=c.concept_code 
				and context='patient_text'
	                  	and concept_str_found not like '% norma%'
			     	and c.concept_code in (select concept_code from dwh_thesaurus_enrsem where phenotype =1)
				union
				select  enrsem_num,concept_code_son  ,patient_num,certainty from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
				where a.concept_code_father='RACINE'  
		    		  $filtre_sql   and 
				a.distance<=$distance 
				$req_certitude
				$req_contexte
				and a.concept_code_son=c.concept_code 
				and context='patient_text'
	                  	and concept_str_found not like '% norma%'
			     	and c.concept_code in (select concept_code from dwh_thesaurus_enrsem where phenotype =1)
				) t,
			dwh_thesaurus_enrsem
			where 
				concept_code_son=concept_code   and concept_code!='C0012634'  and concept_code!='C0012155' and concept_code!='C0039082'
			group by  concept_code,patient_num,certainty
			order by concept_code,certainty
		      ";
	}
	print $requete;
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$concept_code=$r['CONCEPT_CODE'];
		$patient_num=$r['PATIENT_NUM'];
		$tf=$r['TF'];
		$certainty=$r['CERTAINTY'];
		$tableau_code_nb_pat[$concept_code]++;
		$tableau_patient_num_code_nb_concept[$patient_num][$concept_code]=$tf*$certainty;
		$tableau_patient_num_nb_concept_total[$patient_num]+=$tf;
		$tableau_patient_num_liste_code[$patient_num].="$concept_code;";
		$tableau_patient_num_tous_les_codes_nb_concept_total[$patient_num]+=$tf;
		$tableau_patient_num_tous_les_codes_nb_concept_par_code[$patient_num][$concept_code]=$tf;
		$tableau_patient_num[$patient_num]='ok';
	}
	$nb_pat_total=count($tableau_patient_num);
	
	foreach ($tableau_patient_num_liste_code as $patient_num => $liste_code) {
		$liste_code=preg_replace("/;$/","",$liste_code);
		$tableau_patient_num_liste_code[$patient_num]=$liste_code;
	}
	
	print "\n\n".benchmark ( 'debut 2' )."\n\n";
	print "nb_pat_total : $nb_pat_total<br>";
	
	$tableau_final_code=array();
	foreach ($tableau_code_nb_pat as $concept_code => $nb_pat) {
		if ($nb_pat>=$limite_min_nb_patient_par_code) {
			$tableau_final_code[$concept_code]=$nb_pat;
		}
	}
	print "tableau_final_code : ".count($tableau_final_code)."<br>";

	print "\n\n".benchmark ( 'debut 3' )."\n\n";
	
	
	update_process ($process_num,'0',get_translation('PROCESS_PATIENTS_TO_VECTORS','Vectorisation des patients'),'',$user_num_session,"");
	
	$tableau_longueur_vecteur=array();
	$tableau_nb_concept_patient_num=array();
	///creation des vecteurs et calcul des longueurs de vecteur 
	//
	$csv='';
	foreach ($tableau_patient_num as $patient_num => $ok) {
		if (count($tableau_patient_num_code_nb_concept[$patient_num]) > $limite_count_concept_par_patient_num) { 
			$csv.="$patient_num	";
			$i=0;
			$longueur_vecteur=0;
			foreach ($tableau_final_code as $concept_code => $nb_pat) {
				if ($tableau_patient_num_code_nb_concept[$patient_num][$concept_code]!='') {
					$tf=$tableau_patient_num_code_nb_concept[$patient_num][$concept_code]/$tableau_patient_num_nb_concept_total[$patient_num];
					$idf=log($nb_pat_total/$tableau_code_nb_pat[$concept_code]);
					//$tableau_patient_num_vecteur["$patient_num;$i"]=round($tf*$idf,3);
					$tableau_patient_num_vecteur["$patient_num;$concept_code"]=round($tf*$idf,3);
				} else {
					$tf=0;
					$idf=0;
				}
				//$longueur_vecteur+=$tableau_patient_num_vecteur["$patient_num;$i"]*$tableau_patient_num_vecteur["$patient_num;$i"];
				//$csv.=$tableau_patient_num_vecteur["$patient_num;$i"]."	";
				$longueur_vecteur+=$tableau_patient_num_vecteur["$patient_num;$concept_code"]*$tableau_patient_num_vecteur["$patient_num;$concept_code"];
				$csv.=$tableau_patient_num_vecteur["$patient_num;$concept_code"]."	";
				$i++;
			}
			if ($longueur_vecteur>=$limite_longueur_vecteur ) {
				$tableau_longueur_vecteur[$patient_num]=round(sqrt($longueur_vecteur),3);
				$tableau_nb_concept_patient_num[$patient_num]=$longueur_vecteur;
			} else {
				$i=0;
				foreach ($tableau_final_code as $concept_code => $nb_pat) {
					//unset($tableau_patient_num_vecteur["$patient_num;$i"]);
					unset($tableau_patient_num_vecteur["$patient_num;$concept_code"]);
					$i++;
				}
			}
			$csv.="\n";
		}
	}
	

	$max_width=15;
	$max_length=2;
	print "\n\n".benchmark ( 'debut 4' )."\n\n";
	print "tableau_longueur_vecteur nb patient_num: ".count($tableau_longueur_vecteur)."<br>"; 
	$nb_patient_total_pris_en_compte=count($tableau_longueur_vecteur);
//	$coef_tfidf=3;
//	$coef_freq=1;
	$tableau_patient_num_final=array();
	$tableau_cluster_liste_patient_num=array();
	$tableau_weight=array();
	$tableau_length=array();
	//// Option pour calculer similarite a partir d'un patient principal ////


	print "\n\n".benchmark ( 'debut 5' )."\n\n";
	update_process ($process_num,'0',get_translation('PROCESS_GRAPH_CREATION','Création du graph'),'',$user_num_session,"");
	$nb_patient_tt=0;
	///creation des vecteurs et calcul des longueurs de vecteur
	foreach ($tableau_longueur_vecteur as $patient_num_1 => $longeur_v_1) {
		foreach ($tableau_longueur_vecteur as $patient_num_2 => $longeur_v_2) {
			if ($patient_num_1!=$patient_num_2 && $tableau_similarite["$patient_num_1;$patient_num_2"]=='' && $tableau_similarite["$patient_num_2;$patient_num_1"]=='') {
				$tableau_patient_num_code_1=explode(";",$tableau_patient_num_liste_code[$patient_num_1]);
				$tableau_patient_num_code_2=explode(";",$tableau_patient_num_liste_code[$patient_num_2]);
				$intersect = array_intersect($tableau_patient_num_code_1,$tableau_patient_num_code_2);
				
				if (count($intersect)>$nb_concept_commun) {
					$produit_longueur=$longeur_v_1*$longeur_v_2;
					if ($produit_longueur>0) {
						$produit_scalaire=0;
#						for ($i=0;$i<count($tableau_final_code);$i++) {
#							$poids_patient_num_2=$tableau_patient_num_vecteur["$patient_num_2;$i"];
#							if ($poids_patient_num_2=='') {
#								$poids_patient_num_2=0;
#							}
#							$poids_patient_num_1=$tableau_patient_num_vecteur["$patient_num_1;$i"];
#							if ($poids_patient_num_1=='') {
#								$poids_patient_num_1=0;
#							}
#							$produit_scalaire+=$poids_patient_num_1*$poids_patient_num_2;
#						}
						$tableau_patient_num_code=array();
						if (count($tableau_patient_num_code_1)<count($tableau_patient_num_code_2) ){
							$tableau_patient_num_code=$tableau_patient_num_code_1;
						} else {
							$tableau_patient_num_code=$tableau_patient_num_code_2;
						}
						foreach ($tableau_patient_num_code as $concept_code) {
							$poids_patient_num_2=$tableau_patient_num_vecteur["$patient_num_2;$concept_code"];
							if ($poids_patient_num_2=='') {
								$poids_patient_num_2=0;
							}
							$poids_patient_num_1=$tableau_patient_num_vecteur["$patient_num_1;$concept_code"];
							if ($poids_patient_num_1=='') {
								$poids_patient_num_1=0;
							}
							$produit_scalaire+=$poids_patient_num_1*$poids_patient_num_2;
						}
						$similarite=round(100*$produit_scalaire/$produit_longueur);
					} else {
						$similarite=0;
					}
	
					if ($similarite>=$limite_valeur_similarite) {
						$tableau_similarite["$patient_num_1;$patient_num_2"]=$similarite;
						$tableau_similarite["$patient_num_2;$patient_num_1"]=$similarite;
						$similarite_normalise=round(($similarite-$limite_valeur_similarite) *$max_width/(100-$limite_valeur_similarite)); // calcul de l'epaisseur du trait
						
						$length=$max_length-round(($similarite-$limite_valeur_similarite) *$max_length/(100-$limite_valeur_similarite),2); 
						if ($similarite_normalise==0) {
							$similarite_normalise=1;
						}
						$tableau_patient_num_final[$patient_num_1]='ok';
						$tableau_patient_num_final[$patient_num_2]='ok';

						$tableau_cluster_liste_patient_num[$patient_num_1].=";$patient_num_2;$patient_num_1;";
						$tableau_cluster_liste_patient_num[$patient_num_2].=";$patient_num_1;$patient_num_2;";

						$weight=$similarite;
						
						$jpgraph_connexion.="$patient_num_1 -- $patient_num_2  [weight=$weight,len=$length, penwidth=$similarite_normalise, label=\"$phrase_similarite\", fontcolor=\"black\", URL=\"javascript:affiche_intersect('$patient_num_1','$patient_num_2','','$phrase_similarite');\"] ;\n";
					}
				}
			}
		}	
		$nb_patient_tt++;
		update_process ($process_num,'0',"$nb_patient_tt ".get_translation('PROCESS_PATIENTS_TREATED_OUT_OF','patients traités sur')." $nb_patient_total_pris_en_compte",'',$user_num_session,"");
	}
		
	
	update_process ($process_num,'0',get_translation('PROCESS_COMPUTE_DISPLAY_TABLE','Calcul du tableau d affichage'),'',$user_num_session,"");

	$deja_patient_num=array();
	$tableau_cluster=array();
	$num_cluster=0;
	foreach ($tableau_patient_num_final as $patient_num => $ok) {
		$num_cluster++;
		parcours_patient_num($num_cluster,$patient_num);
	}
	
	$j=0;
	$tableau_html_liste_clusters="<table border=\"0\" class=\"tablefin\" id=\"id_tableau_similarite_patient\"><thead><th>Cluster</th><th>Patient</th><th>Concepts communs</th></thead><tbody>";
	foreach ($tableau_cluster as $num_cluster => $liste_patient_num) {
		$j++;
		$tableau_html_liste_clusters.= "<tr><td>Cluster $j</td><td nowrap=nowrap>";
		$tpatient_num= array_unique(explode(";",$liste_patient_num));
		$tableau_code_final_cluster=array();
		foreach ($tpatient_num as $patient_num) {
			if ($patient_num!='') {
				$tableau_code_1=explode(';',$tableau_patient_num_liste_code[$patient_num]);
				if (count($tableau_code_final_cluster)==0) {
					$tableau_code_final_cluster=$tableau_code_1;
				} else {
					$intersect = array_intersect($tableau_code_final_cluster,$tableau_code_1);
					$tableau_code_final_cluster=array();
					$tableau_code_final_cluster=$intersect;
				}
				if ($tableau_user_name[$patient_num]=='') {
					$tableau_user_name[$patient_num]=afficher_patient ($patient_num,'lastname firstname','','','clustering');
					$jpgraph_connexion.="$patient_num [label=\"".$tableau_user_name[$patient_num]."\",URL=\"patient.php?patient_num=$patient_num\",target=\"_blank\"] ;\n";
				}
				
				$tableau_html_liste_clusters.= "".$tableau_user_name[$patient_num]." <a target=\"_blank\" href=\"patient.php?patient_num=$patient_num\"><img border=\"0\" src=\"images/dossier_patient.png\" width=15></a><br>";
			}
		}
		$tableau_html_liste_clusters.= "</td><td>";
		$liste_concept='';
		$tableau_code_final_cluster=array_unique($tableau_code_final_cluster);
		
		foreach ($tableau_code_final_cluster as $concept_code) {
			if ($concept_code!='') {
				$sel=oci_parse($dbh, "select concept_str,count_patient from dwh_thesaurus_enrsem where concept_code='$concept_code' and pref='Y'");
				oci_execute($sel);
				$r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS);
				$concept_str=$r['CONCEPT_STR'];
				$nb_patient=$r['COUNT_PATIENT'];
				
				$liste_concept.= "$concept_str, ";
				
				
			}
		}
		$liste_concept=substr($liste_concept,0,-2);
		$tableau_html_liste_clusters.= "$liste_concept</td></tr>";
	}
	$tableau_html_liste_clusters.= "</tbody></table>";
	
	print "\n\n".benchmark ( 'debut 9' )."\n\n";

	$fichier_dot= "	
	strict  graph cluster_patient {
		rankdir=TB;
	        bgcolor=white; 
		 node [fontsize=9,fontcolor=\"black\",shape=\"plaintext\",style = filled,fontfamily=\"Arial\",shape=\"box\"]; 
		overlap=false;
		splines=\"true\";
	         edge [ fontname=Helvetica,  fontsize=9 ];
	        $jpgraph_connexion
	}";	
}


?>