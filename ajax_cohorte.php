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


if ($_POST['action']=='display_cohort_concepts_tab') {
	$cohort_num=$_POST['cohort_num'];
	print "<h2>".get_translation('CONCEPTS_INCLUDED_PATIENTS','Concepts des patients inclus')."</h2>";
	repartition_concepts_tableau_cohorte ($cohort_num,'id_tableau_concepts_cohorte'," and dwh_enrsem.concept_code in (select concept_code from dwh_thesaurus_enrsem where genotype=1 or phenotype=1 ) ",'pref'); 
	save_log_page($user_num_session,'display_cohort_concepts_tab');
}



if ($_POST['action']=='rafraichir_select_cohort') {
	display_user_cohort_javascript ($user_num_session);
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
		update_process ($process_num,'0',get_translation('PROCESS_START','debut du process'),'',$user_num_session,'');
	}
	
	passthru( "php exec_precalcul_nb_patient_similarite_cohorte.php \"$cohort_num\" \"$process_num\" \"$requete\" \"$user_num_session\">> $CHEMIN_GLOBAL_LOG/log_chargement_similarite_patient_$cohort_num.txt 2>&1 &");
	print $process_num;
}


if ($_POST['action']=='verifier_process_fini_precalcul_nb_patient_similarite_cohorte') {
	$process_num=$_POST['process_num'];
	$cohort_num=$_POST['cohort_num'];
	if ($process_num!='') {
		$tableau_process=get_process ($process_num,'dontget_result');
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

	$tableau_process=get_process($process_num,'dontget_result');
	$verif_process_num=$tableau_process['PROCESS_NUM'];
	
        
        if ($verif_process_num=='') {
		create_process ($process_num,$user_num_session,'0',get_translation('PROCESS_START','debut du process'),'',"sysdate+7","similarite_cohorte");
	} else {
		update_process ($process_num,'0',get_translation('PROCESS_START','debut du process'),'',$user_num_session,'');
	}
	
	passthru( "php exec_similarite_cohorte.php \"$cohort_num\" \"$process_num\" \"$nbpatient_limite\" \"$limite_count_concept_par_patient_num\" \"$cohorte_exclue\" \"$patients_importes\" > $CHEMIN_GLOBAL_LOG/log_chargement_similarite_cohorte_$cohort_num.$process_num.txt 2>&1 &");
	print "process;"."get_translation('PROCESS_TIME_WARNING','Debut du process, cela peut prendre plusieurs minutes')";
	save_log_page($user_num_session,'calculer_similarite_cohorte');
}


if ($_POST['action']=='verifier_process_fini_similarite_cohorte') {
	$process_num=$_POST['process_num'];
	$cohort_num=$_POST['cohort_num'];
	if ($process_num!='') {
		$tableau_process=get_process ($process_num,'dontget_result');
		$status=$tableau_process['STATUS'];
		$commentary=$tableau_process['COMMENTARY'];
		$res= "$status;$commentary";

		if ($status==0) {
			$process=verif_process("exec_similarite_cohorte");
			if ($process==1) {
			//	$process=verif_process("tmp_graphviz_similarite_tfidf_$cohort_num.$process_num.dot");
			//	if ($process==1) {
					$res="erreur; ".get_translation('ERROR_SIMILARITY_COMPUTATION','il y a une erreur dans le calcul de similarite ...');
			//	} 
			}
		}
		print $res;
	}
}



if ($_POST['action']=='afficher_resultat_similarite_cohorte') {
	$process_num=$_POST['process_num'];
	$cohort_num=$_POST['cohort_num'];
	$result='';
	if ($process_num!='') {
		$tableau_process=get_process ($process_num,'get_result');
		$result=$tableau_process['RESULT'];
	}
	print $result;
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

if ($_POST['action']=='lister_tous_les_commentaires_patient_cohorte') {
	$cohort_num=$_POST['cohort_num'];
	print lister_tous_les_commentaires_patient_cohorte ($cohort_num);
	
}

if ($_POST['action']=='importer_patient_cohorte') {
	$liste_hospital_patient_id=urldecode($_POST['liste_hospital_patient_id']);
	$cohort_num=$_POST['cohort_num'];
	$option=$_POST['option'];
	if ($option=='importer') {
		$status=3;
	}
	if ($option=='exclure') {
		$status=0;
	}
	if ($option=='inclure') {
		$status=1;
	}
	$i=0;
  	$tableau_ligne=preg_split("/[\n\r]/",$liste_hospital_patient_id);
  	foreach ($tableau_ligne as $ligne) {
  		$i++;
	  	$tab=preg_split("/[;,\t]/",$ligne);
  		
  		if (preg_match("/^[a-z]/i",$ligne)) {
	  		$lastname=trim($tab[0]);
	  		$firstname=trim($tab[1]);
	  		$birth_date=trim($tab[2]);
	  	} else {
	  		$hospital_patient_id=trim($tab[0]);
	  		$lastname=trim($tab[1]);
	  		$firstname=trim($tab[2]);
	  		$birth_date=trim($tab[3]);
	  	}
  		$patient_num='';
  		if ($hospital_patient_id!='') {
			$patient_num=get_patient_num ($hospital_patient_id,'');
		} 
		$birth_date=verif_format_date($birth_date,'DD/MM/YYYY'); // if bad format, date = null;
		if ($patient_num=='' && $lastname!='' && $firstname!='' && $birth_date!='') {
			$lastname=nettoyer_pour_insert ($lastname);
			$firstname=nettoyer_pour_insert ($firstname);
			$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
			(regexp_replace(upper( CONVERT(lastname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname', 'US7ASCII') ),'[^A-Z]','') or 
			regexp_replace(upper( CONVERT(maiden_name, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname', 'US7ASCII') ),'[^A-Z]','') 
			)
			and 
			regexp_replace(upper( CONVERT(firstname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$firstname', 'US7ASCII') ),'[^A-Z]','') and
			lastname is not null and 
			firstname is not null and
			birth_date is not null and
			birth_date=to_date('$birth_date','DD/MM/YYYY')");
			oci_execute($sel);
			$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
			$patient_num=$r['PATIENT_NUM'];
		}
	  		
  		if ($patient_num!='') {
	  		$autorisation_voir_patient=autorisation_voir_patient($patient_num,$user_num_session);
	  		if ($autorisation_voir_patient=='ok') {
		        	$autorisation_cohorte_ajouter_patient=autorisation_cohorte_ajouter_patient ($cohort_num,$user_num_session);
		        	if ($autorisation_cohorte_ajouter_patient=='ok') {
					$sel=oci_parse($dbh,"select count(*) as verif_deja_inclu from dwh_cohort_result where   cohort_num=$cohort_num and  patient_num =$patient_num ");
					oci_execute($sel);
					$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
					$verif_deja_inclu=$r['VERIF_DEJA_INCLU'];
					
					if ($verif_deja_inclu==0 ) {
						$req="insert into dwh_cohort_result  (cohort_num , patient_num ,status,add_date,user_num_add) values ($cohort_num,'$patient_num',$status,sysdate,$user_num_session)";
						$ins=oci_parse($dbh,$req);
						oci_execute($ins) || die ("<strong style=\"color:red\">erreur : patient non ajouté à la cohorte</strong><br>");
	  					print "$i-$ligne : $option<br>";
					} else {
						if ($option=='exclure' || $option=='inclure') {
							$req="update dwh_cohort_result set status=$status,add_date=sysdate,user_num_add=$user_num_session where  cohort_num=$cohort_num and patient_num=$patient_num ";
							$upd=oci_parse($dbh,$req);
							oci_execute($upd) || die ("<strong style=\"color:red\">erreur : patient non ajouté à la cohorte</strong><br>");
	  						print "$i-$ligne : ".get_translation('STATUS_UPDATE','Mise à jour du status')." : $option<br>";
						} else {
		  					print "$i-$ligne ".get_translation('NOT_INCLUDED_ALREADY_IN_COHORT','non inclu :  patient déjà dans la cohorte')."<br>";
		  				}
					}
					
	  			} else {
	  				print "$i-$ligne non inclu :  vous n'êtes pas autorisé à ajouter des patients dans la cohorte<br>";
	  			}
	  		} else {
	  			print "$i-$ligne non inclu :  vous n'êtes pas autorisé à le voir<br>";
	  		}
	  		
	  	} else {
	  		print "$i-$ligne non inclu : absent de l'entrepôt de données<br>";
	  	}
  	}
}
oci_close ($dbh);
oci_close ($dbh_etl);
?>