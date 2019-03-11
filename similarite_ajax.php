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
	print 'deconnexion';
	exit;
} else {
	include_once("verif_droit.php");
	if ($erreur_droit!='') {
		print "$erreur_droit";
		exit;
	}
}

session_write_close();



if ($_POST['action']=='afficher_onglet_similarite_patient') {
	$patient_num=$_POST['patient_num'];
	$phenotype_genotype=$_POST['phenotype_genotype'];
	if ($phenotype_genotype!='') {
		$filtre_phenotype_genotype=" and $phenotype_genotype=1 ";
	}
	$tableau_code_libelle_autorise=array();
	$requete=" select  concept_code, concept_str,count(*) as TF 
		from (
			select  enrsem_num,concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
			where a.concept_code_father='RACINE'   and
			patient_num=$patient_num and 
			 context='patient_text' and
			 certainty=1 and
			a.concept_code_son=c.concept_code 
			) t,
		dwh_thesaurus_enrsem
		where 
			concept_code_son=concept_code and pref='Y' and concept_code!='C0012634'  and concept_code!='C0012155' and concept_code!='C0039082'
			$filtre_phenotype_genotype
		group by  concept_code, concept_str
		order by tf desc
	      ";
	$liste_concept='';
	$i=0;
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$concept_str=$r['CONCEPT_STR'];
		$concept_code=$r['CONCEPT_CODE'];
		$tf=$r['TF'];
		$concept_str=preg_replace("/'/"," ",$concept_str);
		if (strtolower($concept_str)!=strtolower('fievre') && strtolower($concept_str)!=strtolower('infection') && strtolower($concept_str)!=strtolower('pathologie') && strtolower($concept_str)!=strtolower('syndrome') && strtolower($concept_str)!=strtolower('alimentation')) {
			$i++;
			if ($i<=1) {
				$liste_concept.=" $concept_str or";
			}
		}
	}
	$liste_concept=substr($liste_concept,0,-2);
	print $liste_concept;
}


if ($_POST['action']=='precalcul_nb_patient_similarite_patient') {
	$patient_num_principal=$_POST['patient_num'];
	$requete=urldecode($_POST['requete']);
	$requete_exclure=urldecode($_POST['requete_exclure']);
	
	$process_num=$_POST['process_num'];
		
	$tableau_process=get_process($process_num);
	$verif_process_num=$tableau_process['PROCESS_NUM'];
	
        
        if ($verif_process_num=='') {
		create_process ($process_num,$user_num_session,'0',get_translation('PROCESS_START','debut du process'),'',"sysdate+2","similarity_patient");
	} else {
		update_process ($process_num,'0',get_translation('PROCESS_START','debut du process'),'');
	}
	
	passthru( "php exec_precalcul_nb_patient_similarite_patient.php $patient_num_principal $process_num \"$requete\" \"$requete_exclure\">> $CHEMIN_GLOBAL_LOG/log_chargement_similarite_patient_$patient_num_principal"."_$process_num".".txt 2>&1 &");
	print $process_num;
}



if ($_POST['action']=='verifier_process_fini_precalcul_nb_patient_similarite_patient') {
	$process_num=$_POST['process_num'];
	$patient_num=$_POST['patient_num'];
	if ($process_num!='') {
		
		
		$tableau_process=get_process ($process_num);
		$status=$tableau_process['STATUS'];
		
		$sel=oci_parse($dbh,"select count(distinct patient_num) as nb_patient from dwh_process_patient where process_num='$process_num' ");
		oci_execute($sel) || die ("erreur");
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$nb_patient=$r['NB_PATIENT'];
		
		print "$status;$nb_patient";
	}
}

if ($_POST['action']=='calculer_similarite_patient') {

	save_log_page($user_num_session,'similarities');
	$patient_num_principal=$_POST['patient_num'];
	$process_num=$_POST['process_num'];
	
	$context_famille='non';
	$codes_exclus=$_POST['codes_exclus'];
	$exclusion_date=$_POST['exclusion_date'];
	
	$requete_exclure=urldecode($_POST['requete_exclure']);
	$anonyme=$_POST['anonyme'];
	$nbpatient_limite=$_POST['nbpatient_limite'];
	$cohort_num_exclure=$_POST['cohort_num_exclure'];
	$limite_count_concept_par_patient_num=$_POST['limite_count_concept_par_patient_num'];
	
	$limite_min_nb_patient_par_code=0;
	$distance=10;
	if ($codes_exclus=='') {
		$codes_exclus='.';
	}
	
	if ($anonyme=='') {
		$anonyme='non';
	}
	if ($nbpatient_limite=='') {
		$nbpatient_limite=20;
	}
	if ($cohort_num_exclure=='') {
		$cohort_num_exclure=".";
	}
	$requete_exclure=preg_replace("/\"/"," ",$requete_exclure);
	
	$tableau_process=get_process($process_num);
	$verif_process_num=$tableau_process['PROCESS_NUM'];
	
        
        if ($verif_process_num=='') {
		create_process ($process_num,$user_num_session,'0',get_translation('PROCESS_START','debut du process'),'',"sysdate+2","similarity_patient");
	} else {
		update_process ($process_num,'0',get_translation('PROCESS_START','debut du process'),'');
	}
	
	$sel=oci_parse($dbh,"select count(distinct patient_num) as nb_patient from dwh_process_patient where process_num='$process_num' ");
	oci_execute($sel) || die ("erreur");
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb_patient=$r['NB_PATIENT'];

	passthru( "php exec_similarite_patient.php \"$patient_num_principal\" \"$distance\" \"$limite_count_concept_par_patient_num\" \"$limite_min_nb_patient_par_code\" \"$process_num\" \"$negation\" \"$codes_exclus\" \"$context_famille\" \"$anonyme\" \"$nbpatient_limite\" \"$cohort_num_exclure\" \"$requete_exclure\" \"$exclusion_date\"> $CHEMIN_GLOBAL_LOG/log_chargement_similarite_patient_$patient_num_principal.$process_num.txt 2>&1 &");

	print "process_num;$process_num;$nb_patient";
}


if ($_POST['action']=='verifier_process_fini_similarite') {
	$process_num=$_POST['process_num'];
	$patient_num=$_POST['patient_num'];
	if ($process_num!='') {
	
		$tableau_process=get_process ($process_num);
		$status=$tableau_process['STATUS'];
		$commentary=$tableau_process['COMMENTARY'];
		$res= "$status;$commentary";

		if ($status==0) {
			$process=verif_process("exec_similarite_patient");
			if ($process==1) {
				$process=verif_process("tmp_graphviz_similarite_tfidf_$patient_num.$process_num.dot");
				if ($process==1) {
					$res= "erreur;".get_translation('ERROR_SIMILARITY_COMPUTATION','il y a une erreur dans le calcul de similarite ...');
				} 
			}
		}
		print $res;
	}
}



if ($_POST['action']=='afficher_resultat_similarite') {
	$process_num=$_POST['process_num'];
	$patient_num=$_POST['patient_num'];
	if ($process_num!='') {
		$aleatoire=uniqid();
		print "<img src=\"$URL_UPLOAD/tmp_graphviz_similarite_tfidf_$patient_num.$process_num.png?$process_num.$aleatoire\" usemap=\"#cluster_patient\">";
		$map=join('',file("$CHEMIN_GLOBAL_UPLOAD/tmp_graphviz_similarite_tfidf_$patient_num.$process_num.map"));
		print $map;
		$tableau_html_liste_patients = join('',file("$CHEMIN_GLOBAL_UPLOAD/tableau_html_liste_patients_$patient_num.$process_num.html")); 
		print $tableau_html_liste_patients;
		
		$tableau_html_liste_concepts_patient_similaire = join('',file("$CHEMIN_GLOBAL_UPLOAD/tableau_html_liste_concepts_patient_similaire_$patient_num.$process_num.html")); 
		print $tableau_html_liste_concepts_patient_similaire;
	}
}







if ($_POST['action']=='affiche_intersect') {
	$tmpresult_num=$_POST['tmpresult_num']; // utiliser pour calculer l'idf //
	$patient_num_1=$_POST['patient_num_1'];
	$patient_num_2=$_POST['patient_num_2'];
	$distance=$_POST['distance'];
	
	if ($tmpresult_num!='') {
		$filtre_sql="and patient_num in ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num)";
		/////////// calcul idf par concept /////////////////////
		if ($distance==10) {
			$requete=" select  concept_code, concept_str,patient_num,count(*) as TF 
				from (
						select enrsem_num,concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
						where a.concept_code_father='RACINE' $filtre_sql and 
						context='patient_text' and
						certainty=1 and
						a.concept_code_son=c.concept_code 
						) t, dwh_thesaurus_enrsem
				where concept_code_son=concept_code and pref='Y'  and concept_code!='C0012634'  and concept_code!='C0012155' and concept_code!='C0039082'
				group by  concept_code, concept_str,patient_num
					order by concept_code
				      ";
		} else {
			$requete=" select  concept_code, concept_str,patient_num,count(*) as TF 
				from (
						select  enrsem_num,a.concept_code_son ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_thesaurus_enrsem_graph b, dwh_enrsem c
						where a.concept_code_father='RACINE' $filtre_sql and 
						a.distance=$distance and
						a.concept_code_son=b.concept_code_father and
						context='patient_text' and
						certainty=1 and
						b.concept_code_son=c.concept_code 
					union
						select enrsem_num,concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
						where a.concept_code_father='RACINE' $filtre_sql and 
						a.distance<=$distance and
						context='patient_text' and
						certainty=1 and
						a.concept_code_son=c.concept_code 
						) t, dwh_thesaurus_enrsem
				where concept_code_son=concept_code and pref='Y'  and concept_code!='C0012634'  and concept_code!='C0012155' and concept_code!='C0039082'
				group by  concept_code, concept_str,patient_num
					order by concept_code
				      ";
		}
		$sel=oci_parse($dbh, $requete);
		oci_execute($sel);
		while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
			$concept_str=$r['CONCEPT_STR'];
			$concept_code=$r['CONCEPT_CODE'];
			$patient_num=$r['PATIENT_NUM'];
			$tf=$r['TF'];
			$tableau_code_nb_pat[$concept_code]++;
			$tableau_patient_num[$patient_num]='ok';
		}
		$nb_pat_total=count($tableau_patient_num);
	}
	
	$tf_max=0;
	if ($distance==10) {
		$requete=" select  concept_code, concept_str,count(*) as TF 
		from (
			select  enrsem_num,concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
			where a.concept_code_father='RACINE'   and
			patient_num='$patient_num_1' and 
			context='patient_text' and
			certainty=1 and
			a.concept_code_son=c.concept_code 
			) t,
		dwh_thesaurus_enrsem
		where 
			concept_code_son=concept_code and pref='Y'  and concept_code!='C0012634'  and concept_code!='C0012155' and concept_code!='C0039082'
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
			concept_code_son=concept_code and pref='Y'  and concept_code!='C0012634'  and concept_code!='C0012155' and concept_code!='C0039082'
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
		$tableau_code[$concept_code]++;
		$concept_str=preg_replace("/'/"," ",$concept_str);
		$tableau_code_libelle[$concept_code]=ucwords(strtolower($concept_str));
		$tableau_patient_num_1[$concept_code]='ok';
		$nb_concept_total+=$tf;
		$tableau_code_nb_concept[$concept_code]=$tf;
	}

	if ($distance==10) {
		$requete=" select  concept_code, concept_str,count(*) as TF 
		from (
			select  enrsem_num,concept_code_son  ,patient_num from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
			where a.concept_code_father='RACINE'  and
			patient_num='$patient_num_2' and 
			context='patient_text' and
			certainty=1 and
			a.concept_code_son=c.concept_code 
			) t,
		dwh_thesaurus_enrsem
		where 
			concept_code_son=concept_code and pref='Y'  and concept_code!='C0012634'  and concept_code!='C0012155' and concept_code!='C0039082'
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
			where a.concept_code_father='RACINE'  and
			patient_num='$patient_num_2' and 
			a.distance<=$distance and
			context='patient_text' and
			certainty=1 and
			a.concept_code_son=c.concept_code 
			) t,
		dwh_thesaurus_enrsem
		where 
			concept_code_son=concept_code and pref='Y'  and concept_code!='C0012634'  and concept_code!='C0012155' and concept_code!='C0039082'
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
		$tableau_code[$concept_code]++;
		$concept_str=preg_replace("/'/"," ",$concept_str);
		$tableau_code_libelle[$concept_code]=ucwords(strtolower($concept_str));
		$tableau_patient_num_2[$concept_code]='ok';
		
		
		$nb_concept_total+=$tf;
		$tableau_code_nb_concept[$concept_code]=$tf+$tableau_code_nb_concept[$concept_code];
		
	}
	$intersect = array_intersect_assoc($tableau_patient_num_2,$tableau_patient_num_1);
	
	
	foreach ($intersect as $concept_code => $ok) {
		if ($nb_pat_total!='') {
			if ($tableau_code_nb_pat[$concept_code]==0 || $tableau_code_nb_pat[$concept_code]=='') {
				print "tableau_code_nb_pat $concept_code : ".$tableau_code_nb_pat[$concept_code];
				exit;
			}
			$tf=$tableau_code_nb_concept[$concept_code]/$nb_concept_total;
			$idf=log($nb_pat_total/$tableau_code_nb_pat[$concept_code]);
			$score=$tf*$idf*100;
		} else {
			$tf=$tableau_code_nb_concept[$concept_code];
			$score=$tf;
		}
		
		
		$tableau_code_score[$concept_code]=$score;
		if ($tf_max<$tf) {
			$tf_max=$tf;
		}
		if ($score_max<$score) {
			$score_max=$score;
		}
	}
	
	$tableau_final=array();
	
	foreach ($intersect as $concept_code => $ok) {	;
		$score=$tableau_code_score[$concept_code];
		
		$score_normalise=round($score *2/$score_max,2); // calcul de la taile du text
		if ($score_normalise<0.8) {
			$score_normalise=0.8;
		}
		$tableau_final[$tableau_code_libelle[$concept_code]]=$concept_code;
		$tableau_code_score_normalise[$concept_code]=$score_normalise;
	}
	
	arsort($tableau_code_score);
	print "<table width=\"100%\">
	<tr><td style=\"text-align:right;font-weight:bold;cursor:pointer;\" onclick=\"document.getElementById('id_affiche_intersect').style.display='none';\">x</td></tr>
	<tr><td style=\"text-align:right;font-weight:bold;cursor:pointer;\"><a href=\"outils.php?action=comparateur&patient_num_1=$patient_num_1&patient_num_2=$patient_num_2\" target=\"_blank\">Afficher le comparateur</a></td></tr>";
	foreach ($tableau_code_score as $concept_code => $score) {
		$concept_str=$tableau_code_libelle[$concept_code];
		$score_normalise=$tableau_code_score_normalise[$concept_code];
		print "<tr><td style=\"font-size:".$score_normalise."em\">$concept_str#$score</td></tr>";
	}	
	// foreach ($tableau_final as $concept_str => $concept_code) {
	// 	$score=round($tableau_code_score[$concept_code]);
	// 	$score_normalise=$tableau_code_score_normalise[$concept_code];
	// 	print "<tr><td style=\"font-size:".$score_normalise."em\">$concept_str#$score</td></tr>";
	// }
	print "</table>";
}

?>