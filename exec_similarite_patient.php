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

ini_set("memory_limit","1200M");
putenv("NLS_LANG=French");

include_once "parametrage.php";
include_once "connexion_bdd.php";
include_once("ldap.php");
include_once("fonctions_droit.php");
include_once("fonctions_dwh.php");
include_once "similarite_fonction.php"; 

if ($argv[1]!='') {
	$patient_num_principal=$argv[1];
	$distance=$argv[2];
	$limite_count_concept_par_patient_num=$argv[3];
	$limite_min_nb_patient_par_code=$argv[4];
	$process_num=$argv[5];
	
	$negation=$argv[6];
	$codes_exclus=$argv[7];
	$context_famille=$argv[8];
	
	$anonyme=$argv[9];
	$nbpatient_limite=$argv[10];
	$cohort_num_exclure=$argv[11];
	$requete_exclure=$argv[12];
	$date_limite=$argv[13];
} 
  
$limite_longueur_vecteur=0.0001;
$limite_valeur_similarite=45;

if ($distance=='.') {
	$distance=10;
}

$coef_tfidf=1;


if ($limite_count_concept_par_patient_num=='.') {
	$limite_count_concept_par_patient_num=8; // defini le nombre minimum de concepts distincts que doit avoir un patient pour qu'il puisse etre pris en compte dans le calcul
}

if ($limite_longueur_vecteur=='.') {
	$limite_longueur_vecteur=0.1; // defini la longueur minimum du vecteur patient, pour qu'il soit pris en compte 
}

if ($limite_valeur_similarite=='.') {
	$limite_valeur_similarite=40; // defini la valeur minimum du coefficient de similarite
}

if ($limite_min_nb_patient_par_code=='.') {
	$limite_min_nb_patient_par_code=3; //
}


$tableau_process=get_process($process_num);
$user_num_session=$tableau_process['USER_NUM'];


if ($date_limite!='') {
	$req_date_limite="and document_date<to_date('$date_limite','DD/MM/YYYY')";
}
if ($codes_exclus!='.') {
	$tableau_codes_exclus=preg_split("/[^a-z0-9]/i",$codes_exclus);
	$req_codes_exclus='';
	foreach ($tableau_codes_exclus as $concept_code) {
		if ($concept_code!='') {
			$req_codes_exclus.="and  concept_code!='$concept_code'  ";
		}
	}
}

$sel=oci_parse($dbh,"select count(*) as nb_doc from dwh_process_patient where process_num='$process_num' ");
oci_execute($sel) || die ("erreur");
$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
$nb_doc=$r['NB_DOC'];

if ($nb_doc>0) {
	$requete_sous_population="and c.patient_num in (select patient_num from dwh_process_patient where process_num='$process_num') ";
} else {
	$requete_sous_population="and c.concept_code in (select concept_code from dwh_enrsem where patient_num='$patient_num_principal') ";
}

if ($cohort_num_exclure!='.' && $cohort_num_exclure!='') {
	$requete_sous_population.="and c.patient_num not in (select patient_num from dwh_cohort_result where cohort_num='$cohort_num_exclure' and patient_num!='$patient_num_principal') ";
}

if ($requete_exclure!='.' && $requete_exclure!='') {
	$requete_sous_population.="and c.patient_num not in (select patient_num from dwh_text where contains(text,'$requete_exclure')>0 and certainty=1 and context='patient_text') ";
}

if ($negation=='oui') {
	$req_certitude="and (certainty=1 or certainty=-1) ";
} else {
	$req_certitude="and certainty=1  ";
}

if ($context_famille=='oui') {
	$req_contexte="  ";
} else {
	$req_contexte="and context='patient_text' ";
}


update_process ($process_num,'0',get_translation('PROCESS_START','debut du process'),'');

if ($patient_num_principal!='') {
	$tableau_code_autorise=array();
	$tableau_tous_concepts=array();
	$tableau_patient_num_liste_code=array();
	if ($distance==10) {
		      
		$requete="select  concept_code, certainty,count(*) as TF from dwh_enrsem where 
			patient_num='$patient_num_principal'
			$req_contexte
			$req_certitude
			$req_codes_exclus
			$req_date_limite
	          	and concept_code!='C0012634'  and concept_code!='C0012155' and concept_code!='C0039082'
	          	and concept_str_found not like '% norma%'
	          	and concept_code in (select concept_code from dwh_thesaurus_enrsem where phenotype=1) 
			   group by  concept_code,certainty
	      		  order by concept_code,certainty";
	} else {
		$requete=" select  concept_code, certainty,count(*) as TF 
			from (
				select  enrsem_num,a.concept_code_son ,patient_num,certainty from dwh_thesaurus_enrsem_graph a, dwh_thesaurus_enrsem_graph b, dwh_enrsem c
				where a.concept_code_father='RACINE'  and
				patient_num='$patient_num_principal' and 
				a.distance=$distance and
				$req_contexte
				$req_certitude
				a.concept_code_son=b.concept_code_father and
				b.concept_code_son=c.concept_code 
				and context='patient_text'
				$req_codes_exclus
				$req_date_limite
				union
				select  enrsem_num,concept_code_son  ,patient_num,certainty from dwh_thesaurus_enrsem_graph a, dwh_enrsem c
				where a.concept_code_father='RACINE'   and
				patient_num='$patient_num_principal' and 
				a.distance<=$distance and
				a.concept_code_son=c.concept_code 
				$req_certitude
				$req_contexte
				and context='patient_text'
				$req_codes_exclus
				$req_date_limite
				) t,
			dwh_thesaurus_enrsem
			where 
				concept_code_son=concept_code   and concept_code!='C0012634'  and concept_code!='C0012155' and concept_code!='C0039082'
	          	and phenotype=1
			group by  concept_code, certainty
			order by concept_code
		      ";
	}
	print "\n$requete\n";
	$sel=oci_parse($dbh, $requete);
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
		$concept_code=$r['CONCEPT_CODE'];
		$tf=$r['TF'];
		$certainty=$r['CERTAINTY'];
		$tableau_code_autorise[$concept_code]=ok;
		$tableau_patient_num_code_tf[$concept_code]=$tf;
		array_push($tableau_tous_concepts, "$concept_code");
		$tableau_patient_num_liste_code[$patient_num_principal].="$concept_code;";
	}
	
	$fichier_dot='';
	$tableau_html_liste_patients='';
	$deja_patient_num=array();
	$tableau_cluster=array();
	$tableau_cluster_liste_patient_num=array();
	$tableau_html_liste_concepts_patient_similaire='';
        

	calcul_similarite_tfidf ($distance,$limite_count_concept_par_patient_num,$limite_longueur_vecteur,$limite_valeur_similarite,$limite_min_nb_patient_par_code,$requete_sous_population,$patient_num_principal);
	
	$inF = fopen("$CHEMIN_GLOBAL/upload/tmp_graphviz_similarite_tfidf_$patient_num_principal.$process_num.dot","w");
	fputs( $inF,"$fichier_dot");
	fclose($inF);
	
	$inF = fopen("$CHEMIN_GLOBAL/upload/tableau_html_liste_patients_$patient_num_principal.$process_num.html","w");
	fputs( $inF,"$tableau_html_liste_patients");
	fclose($inF);
	
	$inF = fopen("$CHEMIN_GLOBAL/upload/tableau_html_liste_concepts_patient_similaire_$patient_num_principal.$process_num.html","w");
	fputs( $inF,"$tableau_html_liste_concepts_patient_similaire");
	fclose($inF);
	
	update_process ($process_num,'0',get_translation('PROCESS_GRAPH_CREATION','Création du graph'),'');
	
	exec("/usr/bin/twopi \"$CHEMIN_GLOBAL/upload/tmp_graphviz_similarite_tfidf_$patient_num_principal.$process_num.dot\" -Gcharset=latin1 -Tcmapx -o \"$CHEMIN_GLOBAL/upload/tmp_graphviz_similarite_tfidf_$patient_num_principal.$process_num.map\"  -Tpng -o  \"$CHEMIN_GLOBAL/upload/tmp_graphviz_similarite_tfidf_$patient_num_principal.$process_num.png\"");
	
	
	update_process ($process_num,'1',get_translation('PROCESS_END','process fini'),'');
}
?>