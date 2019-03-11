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
include_once "fonctions_concepts.php"; 


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


if ($_POST['action']=='display_cohort_concepts_tab') {
	$cohort_num=$_POST['cohort_num'];
	print "<h2>".get_translation('CONCEPTS_INCLUDED_PATIENTS','Concepts des patients inclus')."</h2>";
	repartition_concepts_tableau_cohorte ($cohort_num,'id_tableau_concepts_cohorte'," and dwh_enrsem.concept_code in (select concept_code from dwh_thesaurus_enrsem where genotype=1 or phenotype=1 ) ",'pref'); 
	save_log_page($user_num_session,'display_cohort_concepts_tab');
}



if ($_POST['action']=='rafraichir_select_cohort') {
	lister_mes_cohortes_cohort_num_titre ($user_num_session);
}


if ($_POST['action']=='precalcul_nb_patient_similarite_cohorte') {
	$cohort_num=$_POST['cohort_num'];
	$requete=urldecode($_POST['requete']);
	$process_num=$_POST['process_num'];
	
	$tableau_process=get_process($process_num);
	$verif_process_num=$tableau_process['PROCESS_NUM'];
	
        
        if ($verif_process_num=='') {
		create_process ($process_num,$user_num_session,'0',get_translation('PROCESS_START','debut du process'),'',"sysdate+2","similarite_cohorte");
	} else {
		update_process ($process_num,'0',get_translation('PROCESS_START','debut du process'),'');
	}
	
	passthru( "php exec_precalcul_nb_patient_similarite_cohorte.php $cohort_num $process_num \"$requete\">> $CHEMIN_GLOBAL_LOG/log_chargement_similarite_patient_$cohort_num.txt 2>&1 &");
	print $process_num;
}


if ($_POST['action']=='verifier_process_fini_precalcul_nb_patient_similarite_cohorte') {
	$process_num=$_POST['process_num'];
	$cohort_num=$_POST['cohort_num'];
	if ($process_num!='') {
		$tableau_process=get_process ($process_num);
		$status=$tableau_process['STATUS'];
		print "$status;";
		
		$sel=oci_parse($dbh,"select count(distinct patient_num) as nb_patient from dwh_process_patient where process_num='$process_num' ");
		oci_execute($sel) || die ("erreur");
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$nb_patient=$r['NB_PATIENT'];
		
		print $nb_patient;
	}
}

if ($_POST['action']=='calculer_similarite_cohorte') {
	$cohort_num=$_POST['cohort_num'];
	$process_num=$_POST['process_num'];
	$limite_count_concept_par_patient_num=$_POST['limite_count_concept_par_patient_num'];
	$nbpatient_limite=$_POST['nbpatient_limite'];
	$patients_importes=$_POST['patients_importes'];
	$cohorte_exclue=urldecode($_POST['cohorte_exclue']);

	$tableau_process=get_process($process_num);
	$verif_process_num=$tableau_process['PROCESS_NUM'];
	
        
        if ($verif_process_num=='') {
		create_process ($process_num,$user_num_session,'0',get_translation('PROCESS_START','debut du process'),'',"sysdate+7","similarite_cohorte");
	} else {
		update_process ($process_num,'0',get_translation('PROCESS_START','debut du process'),'');
	}
	
	passthru( "php exec_similarite_cohorte.php \"$cohort_num\" \"$process_num\" \"$nbpatient_limite\" \"$limite_count_concept_par_patient_num\" \"$cohorte_exclue\" \"$patients_importes\" > $CHEMIN_GLOBAL_LOG/log_chargement_similarite_cohorte_$cohort_num.$process_num.txt 2>&1 &");
	print "process;"."get_translation('PROCESS_TIME_WARNING','Debut du process, cela peut prendre plusieurs minutes')";
	save_log_page($user_num_session,'calculer_similarite_cohorte');
}


if ($_POST['action']=='verifier_process_fini_similarite_cohorte') {
	$process_num=$_POST['process_num'];
	$cohort_num=$_POST['cohort_num'];
	if ($process_num!='') {
		$tableau_process=get_process ($process_num);
		$status=$tableau_process['STATUS'];
		$commentary=$tableau_process['COMMENTARY'];
		$res= "$status;$commentary";

		if ($status==0) {
			$process=verif_process("exec_similarite_cohorte");
			if ($process==1) {
				$process=verif_process("tmp_graphviz_similarite_tfidf_$cohort_num.$process_num.dot");
				if ($process==1) {
					$res="erreur; ".get_translation('ERROR_SIMILARITY_COMPUTATION','il y a une erreur dans le calcul de similarite ...');
				} 
			}
		}
		print $res;
	}
}



if ($_POST['action']=='afficher_resultat_similarite_cohorte') {
	$process_num=$_POST['process_num'];
	$cohort_num=$_POST['cohort_num'];
	$res='';
	if ($process_num!='') {
		
		$tableau_process=get_process ($process_num);
		$result=$tableau_process['RESULT'];
		
		$tab_patient=explode(";",$result);
	
		$res.= "<a href=\"export_excel.php?process_num=$process_num\"><img src=\"images/excel_noir.png\" style=\"cursor:pointer;width:25px;\" title=\"Export Excel\" alt=\"Export Excel\" border=\"0\"></a> ";
		$res.= "<img src=\"images/copier_coller.png\" onclick=\"plier_deplier('id_div_tableau_similarite_cohorte');plier_deplier('id_div_textarea_patient_cohorte_similarite');fnSelect('id_div_textarea_patient_cohorte_similarite');\" style=\"cursor:pointer;\" title=\"Copier Coller pour exporter dans Gecko\" alt=\"Copier Coller pour exporter dans Gecko\"> ";
		$res.= "<div  id=\"id_div_tableau_similarite_cohorte\" style=\"display:block;\">";
		$res.="<table border=0 id=\"id_tableau_similarite_cohorte\" class=\"tablefin\" width=\"800\">";
		$res.="<thead><th>Patients similaires</th><th> Dans le top20 de N patients index </th><th>Similarité moyenne</th></thead><tbody>";
		foreach ($tab_patient as $p) {
			list($patient_num,$nb,$similarite)=explode(",",$p);
			$user_name=afficher_patient ($patient_num,'lastname firstname ddn','','','similarite');
			$res.= "<tr><td>$user_name <a target=\"_blank\" href=\"patient.php?patient_num=$patient_num\"><img border=\"0\" src=\"images/dossier_patient.png\"></a></td><td>$nb</td><td>$similarite</td></tr>\n";
		}
		
		$res.= "</tbody></table>";
	        $res.= "</div>";
		
		$res.= "<pre id=\"id_div_textarea_patient_cohorte_similarite\" style=\"display:none;\" >";
		foreach ($tab_patient as $p) {
			list($patient_num,$nb,$similarite)=explode(",",$p);
	                $ligne=afficher_patient($patient_num,'textarea','',$cohort_num,'similarite');
	                $ligne=rtrim($ligne);
	                $res.=$ligne."\t$nb\t$similarite\n";
	        }
        	$res.= "</pre>";
	
	
	}
	print $res;
}

if ($_POST['action']=='display_query_inclusion') {
	$patient_num=$_POST['patient_num'];
	$query_num_inclusion=$_POST['query_num_inclusion'];
	$cohort_num=$_POST['cohort_num'];
	if ($patient_num!='' && $cohort_num!='') {
		$query_num_inclusion=get_query_inclusion ($cohort_num, $patient_num);
	        $query_inclusion_patient=get_query_clear($query_num_inclusion);
		print $query_inclusion_patient;
	}
}

?>