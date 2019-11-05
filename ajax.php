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
	print "deconnexion";
	exit;
} else {
	include_once("verif_droit.php");
	if ($erreur_droit!='') {
		print "$erreur_droit";
		exit;
	}
}
if ($_POST['action']=='choose_lang') {
	if($_SESSION['DWH_LANG']=='') {
		$_SESSION['DEFAULT_DWH_LANG']=$JSON_TRANSLATION_FILE;
	}
	if (is_file($_POST['lang'].".json")) {
		$_SESSION['DWH_LANG']=$_POST['lang'].".json";
	} else {
		$_SESSION['DWH_LANG']=$JSON_TRANSLATION_FILE;
	}
}

session_write_close();

if ($_POST['action']=='get_translations_json_string') {
	$json_translation_file="";
	
	if (is_file($JSON_TRANSLATION_FILE)) {
		$json_translation_file=file_get_contents($JSON_TRANSLATION_FILE);
	}
	
	print $json_translation_file;
	
}


if ($_POST['action']=='calcul_nb_resultat_final_passthru') {
	$datamart_num=trim($_POST['datamart_num']);
	$query_key=urldecode($_POST['query_key']);

	
	$nb_patient=calcul_nb_resultat_filtre ("query_key ='$query_key' and user_num=$user_num_session and datamart_num=$datamart_num");
	$sel = oci_parse($dbh, "select status_calculate,count_patient from dwh_tmp_query where query_key ='$query_key' and user_num=$user_num_session and datamart_num=$datamart_num");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	$status_calculate=$r['STATUS_CALCULATE'];
	$nb_patient_total=$r['COUNT_PATIENT'];
	if ($nb_patient_total=='') {
		$nb_patient_total='?';
	}
	print "$nb_patient;$status_calculate;$nb_patient_total";
	
}
	

if ($_POST['action']=='calcul_nb_resultat_filtre_passthru') {
	$query_type=trim($_POST['query_type']);
	$datamart_num=trim($_POST['datamart_num']);

	if ($query_type=='text' || $query_type=='code') {
		$text=trim(nettoyer_pour_requete(urldecode(trim($_POST['text']))));
		
		$etendre_syno=trim($_POST['etendre_syno']);
		
		$thesaurus_data_num=trim($_POST['thesaurus_data_num']);
		$chaine_requete_code=trim($_POST['chaine_requete_code']);
		
		$hospital_department_list=urldecode(trim($_POST['hospital_department_list']));
		$context=urldecode(trim($_POST['context']));
		$certainty=urldecode(trim($_POST['certainty']));
		$title_document=urldecode(trim($_POST['title_document']));
		$date_deb_document=urldecode(trim($_POST['date_deb_document']));
		$date_fin_document=urldecode(trim($_POST['date_fin_document']));
		$document_last_nb_days=urldecode(trim($_POST['document_last_nb_days']));
		$periode_document=urldecode(trim($_POST['periode_document']));
		$age_deb_document=urldecode(trim($_POST['age_deb_document']));
		$age_fin_document=urldecode(trim($_POST['age_fin_document']));
		$agemois_deb_document=urldecode(trim($_POST['agemois_deb_document']));
		$agemois_fin_document=urldecode(trim($_POST['agemois_fin_document']));
		$document_origin_code=urldecode(trim($_POST['document_origin_code']));
		$liste_department_num='';
		if ($hospital_department_list!='') {
			$tableau_unite_heberg=explode(',',$hospital_department_list);
			if (is_array($tableau_unite_heberg)) {
				foreach ($tableau_unite_heberg as $department_num) {
					if ($department_num!='') {
						$liste_department_num.="$department_num,";
					}
				}
				$liste_department_num=substr($liste_department_num,0,-1);
			}
		}
		$liste_document_origin_code='';
		if ($document_origin_code!='') {
			$tableau_document_origin_code=explode(',',$document_origin_code);
			if (is_array($tableau_document_origin_code)) {
				foreach ($tableau_document_origin_code as $document_origin_code) {
					if ($document_origin_code!='') {
						$liste_document_origin_code.="$document_origin_code,";
					}
				}
				$liste_document_origin_code=substr($liste_document_origin_code,0,-1);
			}
		}
		$xml="<text_filter>
<text>$text</text>
<synonym_expansion>$etendre_syno</synonym_expansion>
<thesaurus_data_num>$thesaurus_data_num</thesaurus_data_num>
<str_structured_query>$chaine_requete_code</str_structured_query>
<query_type>$query_type</query_type>
<title_document>$title_document</title_document>
<document_date_start>$date_deb_document</document_date_start>
<document_date_end>$date_fin_document</document_date_end>
<document_last_nb_days>$document_last_nb_days</document_last_nb_days>
<period_document>$periode_document</period_document>
<document_ageyear_start>$age_deb_document</document_ageyear_start>
<document_ageyear_end>$age_fin_document</document_ageyear_end>
<document_agemonth_start>$agemois_deb_document</document_agemonth_start>
<document_agemonth_end>$agemois_fin_document</document_agemonth_end>
<document_origin_code>$liste_document_origin_code</document_origin_code>
<context>$context</context>
<certainty>$certainty</certainty>
<hospital_department_list>$liste_department_num</hospital_department_list>
<datamart_text_num>$datamart_num</datamart_text_num>
</text_filter>";
	}
	$query_key=creer_requete_sql_filtre_passthru($xml);
	print "$query_key";
}

if ($_POST['action']=='calcul_nb_resultat_filtre_mvt_passthru') {
	$query_type=trim($_POST['query_type']);
	$datamart_num=trim($_POST['datamart_num']);
	
	if ($query_type=='mvt') {
		$mvt_department=urldecode(trim($_POST['mvt_department']));
		$mvt_unit=urldecode(trim($_POST['mvt_unit']));
		$type_mvt=urldecode(trim($_POST['type_mvt']));
		
		$encounter_duration_min=urldecode(trim($_POST['encounter_duration_min']));
		$encounter_duration_max=urldecode(trim($_POST['encounter_duration_max']));
		
		$mvt_duration_min=urldecode(trim($_POST['mvt_duration_min']));
		$mvt_duration_max=urldecode(trim($_POST['mvt_duration_max']));
		
		$mvt_nb_min=urldecode(trim($_POST['mvt_nb_min']));
		$mvt_nb_max=urldecode(trim($_POST['mvt_nb_max']));
		
		$stay_nb_min=urldecode(trim($_POST['stay_nb_min']));
		$stay_nb_max=urldecode(trim($_POST['stay_nb_max']));
		
		$mvt_last_nb_days=urldecode(trim($_POST['mvt_last_nb_days']));
		
		$mvt_date_start=urldecode(trim($_POST['mvt_date_start']));
		$mvt_date_end=urldecode(trim($_POST['mvt_date_end']));
		$periode_mvt=urldecode(trim($_POST['periode_mvt']));
		$mvt_ageyear_start=urldecode(trim($_POST['mvt_ageyear_start']));
		$mvt_ageyear_end=urldecode(trim($_POST['mvt_ageyear_end']));
		$mvt_agemonth_start=urldecode(trim($_POST['mvt_agemonth_start']));
		$mvt_agemonth_end=urldecode(trim($_POST['mvt_agemonth_end']));
		
		
		$xml="<mvt_filter>
<query_type>mvt</query_type>
<mvt_department>$mvt_department</mvt_department>
<mvt_unit>$mvt_unit</mvt_unit>
<type_mvt>$type_mvt</type_mvt>
<encounter_duration_min>$encounter_duration_min</encounter_duration_min>
<encounter_duration_max>$encounter_duration_max</encounter_duration_max>
<mvt_duration_min>$mvt_duration_min</mvt_duration_min>
<mvt_duration_max>$mvt_duration_max</mvt_duration_max>
<mvt_nb_min>$mvt_nb_min</mvt_nb_min>
<mvt_nb_max>$mvt_nb_max</mvt_nb_max>
<stay_nb_min>$stay_nb_min</stay_nb_min>
<stay_nb_max>$stay_nb_max</stay_nb_max>
<mvt_last_nb_days>$mvt_last_nb_days</mvt_last_nb_days>
<mvt_date_start>$mvt_date_start</mvt_date_start>
<mvt_date_end>$mvt_date_end</mvt_date_end>
<mvt_ageyear_start>$mvt_ageyear_start</mvt_ageyear_start>
<mvt_ageyear_end>$mvt_ageyear_end</mvt_ageyear_end>
<mvt_agemonth_start>$mvt_agemonth_start</mvt_agemonth_start>
<mvt_agemonth_end>$mvt_agemonth_end</mvt_agemonth_end>
<datamart_text_num>$datamart_num</datamart_text_num>
</mvt_filter>";
	}

	$query_key=creer_requete_sql_filtre_mvt_passthru($xml);
	print "$query_key";
}

if ($_POST['action']=='calcul_nb_resultat_contrainte_temporelle_passthru') {
	
	$query_key_contrainte_temporelle=trim($_POST['query_key_contrainte_temporelle']);
	$query_key_a=trim($_POST['query_key_a']);
	$query_key_b=trim($_POST['query_key_b']);
	$datamart_num=trim($_POST['datamart_num']);
	
	$tab_query_key_contrainte=explode(";",$query_key_contrainte_temporelle);
	$num_filtre=$tab_query_key_contrainte[0];
	$num_filtre_a=$tab_query_key_contrainte[1];
	$num_filtre_b=$tab_query_key_contrainte[2];
	$type_contrainte=$tab_query_key_contrainte[3];
	$minmax=$tab_query_key_contrainte[4];
	$unite_contrainte=$tab_query_key_contrainte[5];
	$duree_contrainte=$tab_query_key_contrainte[6];
	$xml="<time_constraint>";
		$xml.="<filter_num>".trim($num_filtre)."</filter_num>";
		$xml.="<time_filter_num_a>".trim($num_filtre_a)."</time_filter_num_a>";
		$xml.="<time_filter_num_b>".trim($num_filtre_b)."</time_filter_num_b>";
		$xml.="<time_constraint_type>".trim($type_contrainte)."</time_constraint_type>";
		$xml.="<minmax>".trim($minmax)."</minmax>";
		$xml.="<time_constraint_unit>".trim($unite_contrainte)."</time_constraint_unit>";
		$xml.="<time_constraint_duration>".trim($duree_contrainte)."</time_constraint_duration>";
	$xml.="</time_constraint>";
	$query_key=creer_requete_sql_contrainte_temporelle_passthru($xml,$query_key_a,$query_key_b,$datamart_num);
	print $query_key;
}


if ($_POST['action']=='calcul_nb_resultat_filtre') {
	$text=nettoyer_pour_requete(urldecode(trim($_POST['text'])));
	
	$etendre_syno=trim($_POST['etendre_syno']);
	
	$thesaurus_data_num=trim($_POST['thesaurus_data_num']);
	$chaine_requete_code=trim($_POST['chaine_requete_code']);
	$query_type=trim($_POST['query_type']);
	
	$hospital_department_list=urldecode(trim($_POST['hospital_department_list']));
	$context=urldecode(trim($_POST['context']));
	$certainty=urldecode(trim($_POST['certainty']));
	$title_document=urldecode(trim($_POST['title_document']));
	$date_deb_document=urldecode(trim($_POST['date_deb_document']));
	$date_fin_document=urldecode(trim($_POST['date_fin_document']));
	$document_last_nb_days=urldecode(trim($_POST['document_last_nb_days']));
	$periode_document=urldecode(trim($_POST['periode_document']));
	$age_deb_document=urldecode(trim($_POST['age_deb_document']));
	$age_fin_document=urldecode(trim($_POST['age_fin_document']));
	$agemois_deb_document=urldecode(trim($_POST['agemois_deb_document']));
	$agemois_fin_document=urldecode(trim($_POST['agemois_fin_document']));
	$document_origin_code=urldecode(trim($_POST['document_origin_code']));
	$datamart_num=trim($_POST['datamart_num']);
	$liste_department_num='';
	if ($hospital_department_list!='') {
		$tableau_unite_heberg=explode(',',$hospital_department_list);
		if (is_array($tableau_unite_heberg)) {
			foreach ($tableau_unite_heberg as $department_num) {
				if ($department_num!='') {
					$liste_department_num.="$department_num,";
				}
			}
			$liste_department_num=substr($liste_department_num,0,-1);
		}
	}
	$liste_document_origin_code='';
	if ($document_origin_code!='') {
		$tableau_document_origin_code=explode(',',$document_origin_code);
		if (is_array($tableau_document_origin_code)) {
			foreach ($tableau_document_origin_code as $document_origin_code) {
				if ($document_origin_code!='') {
					$liste_document_origin_code.="$document_origin_code,";
				}
			}
			$liste_document_origin_code=substr($liste_document_origin_code,0,-1);
		}
	}
	$xml="<text_filter>
<text>$text</text>
<synonym_expansion>$etendre_syno</synonym_expansion>
<thesaurus_data_num>$thesaurus_data_num</thesaurus_data_num>
<str_structured_query>$chaine_requete_code</str_structured_query>
<query_type>$query_type</query_type>
<title_document>$title_document</title_document>
<document_date_start>$date_deb_document</document_date_start>
<document_date_end>$date_fin_document</document_date_end>
<document_last_nb_days>$document_last_nb_days</document_last_nb_days>
<period_document>$periode_document</period_document>
<document_ageyear_start>$age_deb_document</document_ageyear_start>
<document_ageyear_end>$age_fin_document</document_ageyear_end>
<document_agemonth_start>$agemois_deb_document</document_agemonth_start>
<document_agemonth_end>$agemois_fin_document</document_agemonth_end>
<document_origin_code>$liste_document_origin_code</document_origin_code>
<context>$context</context>
<certainty>$certainty</certainty>
<hospital_department_list>$liste_department_num</hospital_department_list>
<datamart_text_num>$datamart_num</datamart_text_num>
</text_filter>";
	$requete_sql_filtre_texte=creer_requete_sql_filtre($xml,'patient_num');
	$nb=calcul_nb_resultat_filtre($requete_sql_filtre_texte);
	print "$nb";
}

if ($_POST['action']=='add_atomic_query') {
	$num_filtre=$_POST['num_filtre'];
	$query_type=$_POST['query_type'];
	add_atomic_query($num_filtre,$query_type);
}


if ($_POST['action']=='add_atomic_query_mvt') {
	$num_filtre=$_POST['num_filtre'];
	add_atomic_query_mvt($num_filtre);
}


if ($_POST['action']=='ajouter_contrainte_temporelle') {
	$num_filtre=$_POST['num_filtre'];
	$num_filtre_a=$_POST['num_filtre_a'];
	$num_filtre_b=$_POST['num_filtre_b'];
	$type_contrainte=$_POST['type_contrainte'];
	$minmax=$_POST['minmax'];
	$unite_contrainte=$_POST['unite_contrainte'];
	$duree_contrainte=$_POST['duree_contrainte'];
	$contrainte_temporelle=ajouter_contrainte_temporelle ($num_filtre,$num_filtre_a,$num_filtre_b,$type_contrainte,$minmax,$unite_contrainte,$duree_contrainte);
	print $contrainte_temporelle;
	
}


if ($_POST['action']=='afficher_resultat') {
	$num_last_ligne=$_POST['num_last_ligne'];
	$tmpresult_num=$_POST['tmpresult_num'];
	$datamart_num=$_POST['datamart_num'];
	$full_text_query=urldecode($_POST['full_text_query']);
	
	$cohort_num_encours=$_POST['cohort_num_encours'];
	$val_exclure_cohorte_resultat=$_POST['val_exclure_cohorte_resultat'];
	$filtre_resultat_texte=nettoyer_pour_insert(urldecode(trim($_POST['filtre_resultat_texte'])));
	$filtre_sql='';
	if ($val_exclure_cohorte_resultat=='ok' && $cohort_num_encours!='') {
		$filtre_sql.=" and patient_num not in (select patient_num from dwh_cohort_result where cohort_num=$cohort_num_encours and status in (0,1)) ";
	}
        
        if ($filtre_resultat_texte!='') {
       		$full_text_query.=";requete_unitaire;$filtre_resultat_texte";
       		// filtre sur tmp_result deja fait dans recuperer_resultat //
                $filtre_sql.=" and document_num in (select document_num from dwh_text where contains (text,'$filtre_resultat_texte')>0 and context='patient_text' and certainty=1) ";
        }
	$tableau_resultat=recuperer_resultat ($tmpresult_num,"$full_text_query",$num_last_ligne,$filtre_sql);
	$lignes=afficher_resultat ($tmpresult_num,$tableau_resultat,$num_last_ligne,$cohort_num_encours);
	
	print "$lignes";
}


if ($_POST['action']=='afficher_document') {
	$document_num=$_POST['document_num'];
	$datamart_num=$_POST['datamart_num'];
	$full_text_query=urldecode($_POST['full_text_query']);

	$tableau_liste_synonyme=recupere_liste_concept_full_texte ($full_text_query,500);
	$afficher_document=afficher_document ($document_num,"$full_text_query",$tableau_liste_synonyme);

	print "$afficher_document";
}


if ($_POST['action']=='afficher_mvt') {
	$mvt_num=$_POST['mvt_num'];
	$datamart_num=$_POST['datamart_num'];
	$afficher_mvt=afficher_mvt($mvt_num);
	print "$afficher_mvt";
}

if ($_POST['action']=='afficher_document_patient_popup') {
	$document_num=$_POST['document_num'];
	$full_text_query=urldecode($_POST['full_text_query']);
	$id_cle=$_POST['id_cle'];

	$tableau_liste_synonyme=recupere_liste_concept_full_texte ($full_text_query,500);
	
	$afficher_document=afficher_document_patient_popup ($document_num,"$full_text_query",$tableau_liste_synonyme,$id_cle);

	print "$afficher_document";
}


if ($_POST['action']=='charger_moteur_recherche') {
	$query_num=$_POST['query_num'];
	if ($_SESSION['dwh_droit_admin']=='ok') {
		$autorisation_requete_voir="ok";
	} else {
		$autorisation_requete_voir=autorisation_requete_voir ($query_num,$user_num_session);
	}

	if ($autorisation_requete_voir=='ok') {
		$query=get_query ($query_num);
	#	$sel = oci_parse($dbh, "select QUERY_NUM,xml_query , to_char(QUERY_DATE,'DD/MM/YYYY HH24:MI') as DATE_REQUETE_CHAR, QUERY_DATE from dwh_query where query_num=$query_num $filtre");   
	#	oci_execute($sel);
	#	$r = oci_fetch_array($sel, OCI_ASSOC);
		$query_date=$query['QUERY_DATE'];
		$query_num=$query['QUERY_NUM'];
		$xml_query=$query['XML_QUERY'];
		ajouter_filtre_texte($xml_query);
	}
}


if ($_POST['action']=='peupler_moteur_recherche') {
	$query_num=$_POST['query_num'];
	if ($_SESSION['dwh_droit_admin']=='ok') {
		$autorisation_requete_voir="ok";
	} else {
		$autorisation_requete_voir=autorisation_requete_voir ($query_num,$user_num_session);
	}
	if ($autorisation_requete_voir=='ok') {
		$query=get_query ($query_num);
#	$sel = oci_parse($dbh, "select QUERY_NUM,xml_query , to_char(QUERY_DATE,'DD/MM/YYYY HH24:MI') as DATE_REQUETE_CHAR, QUERY_DATE,datamart_num from dwh_query where query_num=$query_num ");   
#	oci_execute($sel);
#	$r = oci_fetch_array($sel, OCI_ASSOC);
		$query_date=$query['QUERY_DATE'];
		$query_num=$query['QUERY_NUM'];
		$xml_query=$query['XML_QUERY'];
		$num_datamart_requete=$query['DATAMART_NUM'];
		if ($xml_query!='') {
		       	$max_num_filtre=0;
			peupler_filtre_texte($xml_query);
			peupler_filtre_mvt($xml_query);
			peupler_contrainte_temporelle($xml_query);
			peupler_filtre_patient($xml_query);
		       	print "jQuery('#id_input_max_num_filtre').val(\"$max_num_filtre\");";
		}
	}
}


if ($_POST['action']=='sauver_requete_en_cours') {
	$query_num=$_POST['query_num'];
	$datamart_num=$_POST['datamart_num'];
	$tmpresult_num=$_POST['tmpresult_num'];
	$crontab_query=$_POST['crontab_query'];
	$crontab_periode=$_POST['crontab_periode'];
	$titre_requete_sauver=trim(nettoyer_pour_requete(urldecode($_POST['titre_requete_sauver'])));
	
	if ($titre_requete_sauver!='' && $query_num!='') {
	        $sel_var=oci_parse($dbh,"update dwh_query set query_type='sauve',title_query='$titre_requete_sauver',crontab_query='$crontab_query',crontab_periode='$crontab_periode' where query_num=$query_num and user_num=$user_num_session");
		oci_execute($sel_var);
		
	        $sel_var=oci_parse($dbh,"insert into dwh_query_result (patient_num,load_date,query_num) select distinct patient_num, sysdate, $query_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num and user_num=$user_num_session");
		oci_execute($sel_var);
		
		print lister_requete_sauve($datamart_num);
	}
}


if ($_POST['action']=='punaiser_requete') {
	$query_num=$_POST['query_num'];
	
	$sel = oci_parse($dbh, "select pin from dwh_query where query_num=$query_num and user_num=$user_num_session");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	$pin=$r['PIN'];
	if ($pin==1) {
		$pin=0;
		$img="<img src=\"images/pin_off.png\" alt=\"Punaiser la requête\" title=\"Punaiser la requête\" style=\"border:0px;\">";
	} else {
		$pin=1;
		$img="<img src=\"images/pin.png\" alt=\"Dépunaiser la requête\" title=\"Dépunaiser la requête\" style=\"border:0px;\">";
	}
        $sel_var=oci_parse($dbh,"update dwh_query set pin='$pin' where query_num=$query_num and user_num=$user_num_session");
	oci_execute($sel_var);
	print $img;
}




/////////////////// ADMINISTRATION //////////// ANNUAIRE //////////////////
if ($_POST['action']=='modifier_libelle_service' ) {
	$department_str=nettoyer_pour_inserer(urldecode($_POST['department_str']));
	$department_code=urldecode($_POST['department_code']);
	$department_num=$_POST['department_num'];
	if ($_SESSION['dwh_droit_admin']=='ok') {
		$req_user="update dwh_thesaurus_department set department_str='$department_str', department_code='$department_code' where department_num=$department_num ";
		$sel_user = oci_parse($dbh,$req_user);
		oci_execute($sel_user);
	}
}

if ($_GET['action']=='autocomplete_rech_rapide_utilisateur') {
	$term=urldecode($_GET['term']);
	if ($term!='') {
		$resultat=rechercher_ldap_user_name_tableau($term,$grp);
		$tableau_res=explode('-separateur-',$resultat);
		$i=0;
		foreach ($tableau_res as $k) {
			$i++;
			$t=explode(';',$k);
			$login_local=$t[0];
			$label_affiche=$t[1];
			$label_value=$t[2];
			if ($i<100) {
				$json.="{\"id\":\"$login_local\",\"label\":\"$label_affiche\",\"value\":\"$label_value\"},";
			}
		}
		
		$json=substr($json,0,-1);
		$res="[$json]";
		print "$res";
	}
}


if ($_POST['action']=='ajouter_user') {
	$liste_login=urldecode($_POST['liste_login']);
	$department_num=$_POST['department_num'];
	$user_profile='medecin';
	
        $sel_var=oci_parse($dbh,"select manager_department from dwh_user_department where department_num=$department_num and user_num=$user_num_session");
	oci_execute($sel_var);
	$r=oci_fetch_array($sel_var);
	$manager_department_groupe=$r[0];
	if ($_SESSION['dwh_droit_admin']=='ok' || $manager_department_groupe==1) {
		$tableau_login=preg_split("/[,;\n]/",$liste_login);
		foreach ($tableau_login as $login_user) {
			$login_user=trim($login_user);
			$manager_department=0;
			if (preg_match("/\*/",$login_user)) {
				$manager_department=1;
				$login_user=str_replace('*','',$login_user);
			} 
			$login_user=trim($login_user);
			if ($login_user!='') {
				$nomfirstname=ldap_user_name($login_user);
				$ident=preg_split("/,/",$nomfirstname);
				$lastname=$ident[0];
				$firstname=$ident[1];
				$mail=$ident[2];
			
				$user_num=ajouter_user ($login_user,$lastname,$firstname,$mail,'',$user_profile,$liste_services,'ok') ;
								
			        $sel_var=oci_parse($dbh,"select count(*) from dwh_user_department where department_num=$department_num and  user_num =$user_num ");
				oci_execute($sel_var);
				$r=oci_fetch_array($sel_var);
				$verif=$r[0];
				if ($verif==0) {
					$req="insert into dwh_user_department (department_num, user_num ,manager_department) values ($department_num,$user_num,$manager_department)";
					$sel=oci_parse($dbh,$req);
					oci_execute($sel) || die("erreur");
					print "<tr id=\"id_tr_user_".$department_num."_".$user_num."\" style=\"background-color:#B9C2C8;\" onmouseover=\"this.style.backgroundColor='#B9C2C8';\" onmouseout=\"this.style.backgroundColor='#F5F5F5';\"><td>$firstname $lastname <span style=\"color:#990000;font-size:18px;font-weight:bold;\" id=\"id_span_user_".$department_num."_".$user_num."\">$texte_manager_department</span></td><td><a onclick=\"supprimer_user('$user_num','$department_num');return false;\"  href=\"#\">X</a></td></tr>";
				} else {
				        $sel_var=oci_parse($dbh,"select manager_department from dwh_user_department where department_num=$department_num and  user_num =$user_num ");
					oci_execute($sel_var);
					$r=oci_fetch_array($sel_var);
					$manager_department_deja=$r[0];
					if ($manager_department!=$manager_department_deja) {
						$req="update  dwh_user_department set manager_department=$manager_department where department_num=$department_num and  user_num =$user_num";
						$sel=oci_parse($dbh,$req);
						oci_execute($sel) || die("erreur");
					}
					
					print ";MAJ;id_span_user_".$department_num."_".$user_num.",$manager_department;MAJ;";
				}
			}
		}
	} else {
		print get_translation('YOU_CANNOT_ADD_USER_TO_GROUP',"Vous n'avez pas le droit d'ajouter un utilisateur dans ce groupe");
	}
}

if ($_POST['action']=='modifier_passwd') {
	$mon_password1=$_POST['mon_password1'];
	$mon_password2=$_POST['mon_password2'];
	if ($mon_password1!='' && $mon_password2!='' && $mon_password1==$mon_password2) {
		$req="update  dwh_user set passwd='".md5($mon_password1)."'  where user_num =$user_num_session";
		$sel=oci_parse($dbh,$req);
		oci_execute($sel) || die("erreur");
	}	
}

if ($_POST['action']=='modifier_user_phone_number') {
	$user_phone_number=$_POST['user_phone_number'];
	$user_phone_number=nettoyer_pour_inserer(urldecode($_POST['user_phone_number']));
	$req="update  dwh_user set user_phone_number='$user_phone_number'  where user_num =$user_num_session";
	$sel=oci_parse($dbh,$req);
	oci_execute($sel) || die("erreur");
}
if ($_POST['action']=='modifier_user_mail') {
	$mail=$_POST['mail'];
	$mail=nettoyer_pour_inserer(urldecode($_POST['mail']));
	$req="update  dwh_user set mail='$mail'  where user_num =$user_num_session";
	$sel=oci_parse($dbh,$req);
	oci_execute($sel) || die("erreur");
}

if ($_POST['action']=='supprimer_user' ) {
	$user_num=$_POST['user_num'];
	$department_num=$_POST['department_num'];
        $sel_var=oci_parse($dbh,"select manager_department from dwh_user_department where department_num=$department_num and user_num=$user_num_session");
	oci_execute($sel_var);
	$r=oci_fetch_array($sel_var);
	$manager_department_groupe=$r[0];
	if ($_SESSION['dwh_droit_admin']=='ok' || $manager_department_groupe==1) {
		$req_user="delete dwh_user_department where department_num=$department_num and user_num=$user_num ";
		$sel_user = oci_parse($dbh,$req_user);
		oci_execute($sel_user);
	}
}


if ($_GET['action']=='autocomplete_rech_rapide_utilisateur_ajout') {
	$term=urldecode($_GET['term']);
	if ($term!='') {
		$resultat=rechercher_ldap_user_name_tableau($term,$grp);
		$tableau_res=explode('-separateur-',$resultat);
		$i=0;
		foreach ($tableau_res as $k) {
			$i++;
			$t=explode(';',$k);
			$login_local=$t[0];
			$label_affiche=$t[1];
			$label_value=$t[2];
			if ($i<100) {
				$json.="{\"id\":\"$login_local\",\"label\":\"$label_affiche\",\"value\":\"$label_value\"},";
			}
		}
		$json=substr($json,0,-1);
		$res="[$json]";
		print "$res";
	}
}



if ($_GET['action']=='recherche_annuaire_interne') {
	$term=trim(urldecode($_GET['term']));
	if ($term!='') {
		$i=0;
		$sel=oci_parse($dbh,"select login,lastname,firstname,mail,dwh_user.user_num from dwh_user where 
		login='$term' or 
		lower(lastname) like lower('%$term%') or 
		lower(lastname||' '||firstname) like lower('%$term%') or 
		lower(firstname||' '||lastname) like lower('%$term%')");
		oci_execute($sel);
		while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$login=$r['LOGIN'];
			$lastname=$r['LASTNAME'];
			$firstname=$r['FIRSTNAME'];
			$mail=$r['MAIL'];
			$user_num=$r['USER_NUM'];
			$i++;
			if ($i<100) {
				$json.="{\"id\":\"$login\",\"label\":\"$lastname $firstname\",\"value\":\"$lastname,$firstname,$mail,$user_num\"},";
			}
		}
		
		if ($json=='') {
			$resultat=rechercher_ldap_user_name_tableau($term,$grp);
			$tableau_res=explode('-separateur-',$resultat);
			$i=0;
			foreach ($tableau_res as $k) {
				$i++;
				$t=explode(';',$k);
				$login_local=$t[0];
				$label_affiche=$t[1];
				$label_value=$t[2];
				if ($i<100) {
					$json.="{\"id\":\"$login_local\",\"label\":\"$label_affiche\",\"value\":\"$label_value,\"},";
				}
			}
		}
		
		$json=substr($json,0,-1);
		$res="[$json]";
		print "$res";
	}
}

if ($_POST['action']=='ajouter_datamart' && $_SESSION['dwh_droit_admin_datamart0']=='ok') {
	$title_datamart=nettoyer_pour_insert(urldecode(trim($_POST['title_datamart'])));
	$description_datamart=nettoyer_pour_insert(urldecode(trim($_POST['description_datamart'])));
	$date_start=trim($_POST['date_start']);
	$end_date=trim($_POST['end_date']);
	$liste_droit=trim($_POST['liste_droit']);
	$tableau_droit=explode(',',$liste_droit);
	
	
	$num_datamart_admin=get_uniqid();
	
	insert_datamart ($num_datamart_admin,$title_datamart,$description_datamart,'sysdate',$date_start,$end_date,'','');
	
	if (is_array($_POST['liste_user_datamart'])) {
		foreach ($_POST['liste_user_datamart'] as $user_num_datamart) {
			foreach ($tableau_droit as $right) {
				if ($right!='' && $user_num_datamart!='') {
					insert_datamart_user_droit ($num_datamart_admin,$user_num_datamart,$right);
				}
			}
		}
	}
	$liste_document_origin_code=trim($_POST['liste_document_origin_code']);
	$tableau_document_origin_code=explode(',',$liste_document_origin_code);
	foreach ($tableau_document_origin_code as $document_origin_code) {
		if ($document_origin_code!='' && $num_datamart_admin!='') {
			insert_datamart_document_origin_code ($num_datamart_admin,$document_origin_code);
		}
	}
	afficher_datamart_ligne($num_datamart_admin);
}

if ($_POST['action']=='supprimer_datamart' && $_SESSION['dwh_droit_admin_datamart0']=='ok') {
	$num_datamart_admin=trim($_POST['num_datamart_admin']);
	
	delete_datamart_resultat ($num_datamart_admin,$sql);
	
	$req="delete from dwh_datamart_user_right where datamart_num=$num_datamart_admin";
	$del=oci_parse($dbh,$req);
	oci_execute($del) || die ("<strong style=\"color:red\">erreur : $title_datamart non supprimé</strong><br>");
	
	$req="delete from dwh_datamart_doc_origin where datamart_num=$num_datamart_admin";
	$del=oci_parse($dbh,$req);
	oci_execute($del) || die ("<strong style=\"color:red\">erreur : $title_datamart non supprimé</strong><br>");
	
	$req="delete from dwh_datamart where datamart_num=$num_datamart_admin";
	$del=oci_parse($dbh,$req);
	oci_execute($del) || die ("<strong style=\"color:red\">erreur : $title_datamart non supprimé</strong><br>");
}


if ($_POST['action']=='afficher_formulaire_modifier_datamart' && $_SESSION['dwh_droit_admin_datamart0']=='ok') {
	$num_datamart_admin=trim($_POST['num_datamart_admin']);
	
	$sel_vardroit=oci_parse($dbh,"select title_datamart,description_datamart,to_char(date_start,'DD/MM/YYYY') as date_start,to_char(end_date,'DD/MM/YYYY') as end_date ,datamart_num from dwh_datamart where datamart_num=$num_datamart_admin ");
	oci_execute($sel_vardroit);
	$r_droit=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
	$title_datamart=$r_droit['TITLE_DATAMART'];
	$description_datamart=$r_droit['DESCRIPTION_DATAMART'];
	$date_start=$r_droit['DATE_START'];
	$end_date=$r_droit['END_DATE'];
	$datamart_num=$r_droit['DATAMART_NUM'];
		
	$tableau_liste_droit=array();
	$sel_pat=oci_parse($dbh,"select distinct right from dwh_datamart_user_right where datamart_num=$datamart_num");
	oci_execute($sel_pat);
	while ($r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$tableau_liste_droit[$r_pat['RIGHT']]='ok';
	}
	
	$tableau_liste_user=array();
	$sel_pat=oci_parse($dbh,"select  dwh_user.user_num from dwh_datamart_user_right,dwh_user where datamart_num=$datamart_num and dwh_user.user_num=dwh_datamart_user_right.user_num");
	oci_execute($sel_pat);
	while ($r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$tableau_liste_user[$r_pat['USER_NUM']]='ok';
	}
	
	$tableau_document_origin_code_coche=array();
	$sel_pat=oci_parse($dbh,"select distinct document_origin_code from dwh_datamart_doc_origin where datamart_num=$datamart_num");
	oci_execute($sel_pat);
	while ($r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$tableau_document_origin_code_coche[$r_pat['DOCUMENT_ORIGIN_CODE']]='ok';
	}
	
	$sel=oci_parse($dbh,"select distinct document_origin_code from dwh_document ");
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$document_origin_code=$r['DOCUMENT_ORIGIN_CODE'];
		$tableau_document_origin_code[$document_origin_code]=$document_origin_code;
	}
		
	print "
	<h1>".get_translation('MODIFY_DATAMART','Modifier un datamart')."</h1>
	<table>
		<tr><td class=\"question_user\">".get_translation('TITLE','Titre')." : </td><td><input type=\"text\" size=\"50\" id=\"id_modifier_titre_datamart\" class=\"form\" value=\"$title_datamart\"></td></tr>
		<tr><td class=\"question_user\">".get_translation('DATE_START','Date de début')." : </td><td><input type=\"text\" size=\"11\" id=\"id_modifier_date_start_datamart\" class=\"form\" value=\"$date_start\"></td></tr>
		<tr><td class=\"question_user\">".get_translation('DATE_END','Date de fin')." : </td><td><input type=\"text\" size=\"11\" id=\"id_modifier_date_fin_datamart\" class=\"form\" value=\"$end_date\"></td></tr>
		<tr><td style=\"vertical-align:top;\" class=\"question_user\">".get_translation('USER_RIGHTS','Droits')." : </td><td>";
	foreach ($tableau_datamart_droit as $right) {
		if ($tableau_liste_droit[$right]=='ok') {
			$check='checked';
		} else {
			$check='';
		}
		print "<input type=\"checkbox\" id=\"id_modifier_user_profile_datamart_$right\" class=\"modifier_user_profile_datamart\" value=\"$right\" $check>$right<br>";
	}
	print "<tr><td style=\"vertical-align:top;\" class=\"question_user\">".get_translation('DOCUMENT_ORIGINS','Origines des documents')." : </td><td>";
		if ($tableau_document_origin_code_coche['tout']=='ok') {
			$check='checked';
		} else {
			$check='';
		}
		print "<input type=checkbox id=\"id_modifier_document_origin_code_datamart_tout\" class=\"modifier_document_origin_code_datamart\" value=\"tout\" $check>Tout<br>";
		foreach ($tableau_document_origin_code as $document_origin_code) {
			if ($tableau_document_origin_code_coche[$document_origin_code]=='ok') {
				$check='checked';
			} else {
				$check='';
			}
			$id_document_origin_code=preg_replace("/[^a-z]/i","_",$document_origin_code);
			print "<input type=\"checkbox\" id=\"id_modifier_document_origin_code_datamart_$id_document_origin_code\" class=\"modifier_document_origin_code_datamart\" value=\"$document_origin_code\" $check>$document_origin_code<br>";
		}
	print "</td></tr>";
	
	
	
	print "<tr><td style=\"vertical-align:top;\" class=\"question_user\">".get_translation('USERS','Utilisateurs')." : </td><td>
			<select id=\"id_modifier_select_user_multiple\" multiple size=\"5\" class=\"form chosen-select\"  data-placeholder=\"Choisissez des utilisateurs\"><option value=''></option>";
		$sel=oci_parse($dbh,"select  user_num,lastname,firstname from dwh_user order by lastname,firstname ");
		oci_execute($sel);
		while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$user_num=$r['USER_NUM'];
			$lastname=$r['LASTNAME'];
			$firstname=$r['FIRSTNAME'];
			if ($tableau_liste_user[$user_num]=='ok') {
				$select='selected';
			} else {
				$select='';
			}
			print "<option  value=\"$user_num\" id=\"id_modifier_select_num_user_multiple_$user_num\" $select>$lastname $firstname</option>";
		}
	print "	</select>
		</td></tr>
		<tr><td style=\"vertical-align:top;\" class=\"question_user\">".get_translation('DESCRIPTION','Description')." : </td><td>
			<textarea id=\"id_modifier_description_datamart\" cols=\"50\" rows=\"6\" class=\"form\">$description_datamart</textarea>
		</td></tr>
		
	</table>
	<input type=\"hidden\" id=\"id_modifier_num_datamart\" value=\"$num_datamart_admin\">
	<input type=\"button\" onclick=\"modifier_datamart();\" class=\"form\" value=\"modifier\"> 
	<input type=\"button\" onclick=\"annuler_modifier_datamart();\" class=\"form\" value=\"annuler\">
	<div id=\"id_div_resultat_modifier_datamart\"></div>
	<br>";
	
}

if ($_POST['action']=='modifier_datamart' && $_SESSION['dwh_droit_admin_datamart0']=='ok') {
	$num_datamart_admin=trim($_POST['num_datamart_admin']);
	$title_datamart=nettoyer_pour_insert(urldecode(trim($_POST['title_datamart'])));
	$description_datamart=nettoyer_pour_insert(urldecode(trim($_POST['description_datamart'])));
	$date_start=trim($_POST['date_start']);
	$end_date=trim($_POST['end_date']);
	$liste_droit=trim($_POST['liste_droit']);
	$liste_document_origin_code=trim($_POST['liste_document_origin_code']);
	$tableau_droit=explode(',',$liste_droit);
	
	$req="update dwh_datamart  set title_datamart='$title_datamart' ,description_datamart ='$description_datamart' ,date_start=to_date('$date_start','DD/MM/YYYY'),end_date=to_date('$end_date','DD/MM/YYYY')
	where datamart_num=$num_datamart_admin";
	$upd=oci_parse($dbh,$req);
	oci_execute($upd) || die ("<strong style=\"color:red\">erreur : $title_datamart non sauvé</strong><br>");
	
	$req="delete from dwh_datamart_user_right where datamart_num=$num_datamart_admin";
	$del=oci_parse($dbh,$req);
	oci_execute($del) || die ("<strong style=\"color:red\">erreur : $title_datamart non supprimé</strong><br>");
	
	if (is_array($_POST['liste_user_datamart'])) {
		foreach ($_POST['liste_user_datamart'] as $user_num_datamart) {
			foreach ($tableau_droit as $right) {
				if ($right!='' && $user_num_datamart!='') {
					insert_datamart_user_droit ($num_datamart_admin,$user_num_datamart,$right);
				}
			}
		}
	}
	
	
	
	$req="delete from dwh_datamart_doc_origin where datamart_num=$num_datamart_admin";
	$del=oci_parse($dbh,$req);
	oci_execute($del) || die ("<strong style=\"color:red\">erreur : $title_datamart non supprimé</strong><br>");
	
	$liste_document_origin_code=trim($_POST['liste_document_origin_code']);
	$tableau_document_origin_code=explode(',',$liste_document_origin_code);
	foreach ($tableau_document_origin_code as $document_origin_code) {
		if ($document_origin_code!='' && $num_datamart_admin!='') {
			insert_datamart_document_origin_code ($num_datamart_admin,$document_origin_code);
		}
	}
	afficher_datamart_ligne($num_datamart_admin);
}

if ($_POST['action']=='afficher_datamart_select' && $_SESSION['dwh_droit_admin_datamart0']=='ok') {
	$id=trim($_POST['id']);
	afficher_datamart_select($id);
}

if ($_POST['action']=='ajouter_patient_datamart' && $_SESSION['dwh_droit_admin_datamart0']=='ok') {
	$num_datamart_admin=trim($_POST['num_datamart_admin']);
	$tmpresult_num=trim($_POST['tmpresult_num']);
	
	$sel=oci_parse($dbh,"select  count(distinct patient_num) NB from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num and patient_num not in (select patient_num from dwh_datamart_result where datamart_num=$num_datamart_admin) ");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb=$r['NB'];
	
	insert_datamart_resultat ("select distinct $num_datamart_admin,patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num and patient_num not in (select patient_num from dwh_datamart_result where datamart_num=$num_datamart_admin)");
	
	print "<strong style=\"color:green\"><br>$nb ".get_translation('PATIENTS_ADDED_TO_DATAMART','patients ajoutés dans le datamart')."</strong><br>";
}

if ($_POST['action']=='supprimer_patient_datamart' && $_SESSION['dwh_droit_admin_datamart0']=='ok') {
	$num_datamart_admin=trim($_POST['num_datamart_admin']);
	$tmpresult_num=trim($_POST['tmpresult_num']);
	
	$sel=oci_parse($dbh,"select  count(distinct patient_num) NB from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num and patient_num  in (select patient_num from dwh_datamart_result where datamart_num=$num_datamart_admin) ");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb=$r['NB'];
	
	 delete_datamart_resultat ($num_datamart_admin,"and patient_num in (select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num )");
	
	print "<strong style=\"color:green\"><br>$nb ".get_translation('PATIENTS_DELETED_FROM_DATAMART','patients supprimés du datamart')."</strong><br>";
}


if ($_POST['action']=='ajouter_cohorte') {
	$title_cohort=nettoyer_pour_insert(urldecode(trim($_POST['title_cohort'])));
	$description_cohort=nettoyer_pour_insert(urldecode(trim($_POST['description_cohort'])));
	$datamart_num=$_POST['datamart_num'];
	
	$sel=oci_parse($dbh,"select temporary_status,datamart_num_origin from dwh_datamart where datamart_num=$datamart_num ");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$temporaire=$r['TEMPORARY_STATUS'];
	$datamart_num_origin=$r['DATAMART_NUM_ORIGIN'];
	if ($temporaire==1) {
		$datamart_num=$datamart_num_origin;
	}
			
	if ($title_cohort!='') {
		$cohort_num_admin=get_uniqid();
		
		$req="insert into dwh_cohort  (cohort_num , title_cohort ,description_cohort ,cohort_date,user_num,datamart_num ) 
					values ($cohort_num_admin,'$title_cohort','$description_cohort',sysdate,$user_num_session,$datamart_num)";
		$ins=oci_parse($dbh,$req);
		oci_execute($ins) || die ("<strong style=\"color:red\">erreur : $title_cohort non sauvé</strong><br>");

		$tableau_cohorte_droit_local=array();
			
		if ($num_datamart_cohorte==0) {
			foreach ($tableau_cohorte_droit as $right) {
				if ($_SESSION['dwh_droit_'.$right.'0']=='ok') {
					$req="insert into dwh_cohort_user_right  (cohort_num , user_num ,right) values ($cohort_num_admin,$user_num_session,'$right')";
					$ins=oci_parse($dbh,$req);
					oci_execute($ins) || die ("<strong style=\"color:red\">erreur : user et right non sauvé</strong><br>");
					$tableau_cohorte_droit_local[]=$right;
				}
			}
		} else {
			$sel=oci_parse($dbh,"select right from dwh_datamart_user_right where datamart_num=$num_datamart_cohorte and user_num=$user_num_session  ");
			oci_execute($sel);
			while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
				$right=$r['RIGHT'];
				$req="insert into dwh_cohort_user_right  (cohort_num , user_num ,right) values ($cohort_num_admin,$user_num_session,'$right')";
				$ins=oci_parse($dbh,$req);
				oci_execute($ins) || die ("<strong style=\"color:red\">erreur : user et right non sauvé</strong><br>");
				$tableau_cohorte_droit_local[]=$right;
			}
		}
		
		if (is_array($_POST['liste_user_cohorte'])) {
			foreach ($_POST['liste_user_cohorte'] as $user_num_cohort) {
				foreach ($tableau_cohorte_droit_local as $right) {
					if ($right!='' && $user_num_cohort!='') {
						$req="insert into dwh_cohort_user_right  (cohort_num , user_num ,right) values ($cohort_num_admin,$user_num_cohort,'$right')";
						$ins=oci_parse($dbh,$req);
						oci_execute($ins) || die ("<strong style=\"color:red\">erreur : user et right non sauvé</strong><br>");
					}
				}
			}
		}
		print "$cohort_num_admin";
	}
}


if ($_POST['action']=='ajouter_user_cohorte') {
	$user_num_cohort=$_POST['user_num_cohort'];
	$cohort_num=$_POST['cohort_num'];
	
	$autorisation_cohorte_modifier=autorisation_cohorte_modifier ($cohort_num,$user_num_session);
	$autorisation_cohorte_voir_patient_nominative_global=autorisation_cohorte_voir_patient_nominative_global ($cohort_num,$user_num_cohort);
	if ($autorisation_cohorte_modifier=='ok' && $user_num_cohort!='' &&  $cohort_num!='') {
		$req="delete from   dwh_cohort_user_right where  cohort_num=$cohort_num and user_num=$user_num_cohort";
		$del=oci_parse($dbh,$req);
		oci_execute($del) || die ("erreur : user et right non sauvé<br>");
		
		foreach ($tableau_cohorte_droit as $right ) {
			if ($right=='nominative' && $autorisation_cohorte_voir_patient_nominative_global=='ok' || $right!='nominative') {
				$req="insert into dwh_cohort_user_right  (cohort_num , user_num ,right) values ($cohort_num,$user_num_cohort,'$right')";
				$ins=oci_parse($dbh,$req);
				oci_execute($ins) || die ("<strong style=\"color:red\">erreur : user et right non sauvé</strong><br>");
			}
		}
		
		$sel=oci_parse($dbh,"select title_cohort from dwh_cohort where   cohort_num=$cohort_num");
		oci_execute($sel);
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$title_cohort=$r['TITLE_COHORT'];
		sauver_notification ($user_num_session,$user_num_cohort,'cohorte',$title_cohort,$cohort_num);		
	}
	affiche_liste_user_cohorte($cohort_num,$user_num_session);
}

if ($_POST['action']=='supprimer_user_cohorte') {
	$user_num_cohort=$_POST['user_num_cohort'];
	$cohort_num=$_POST['cohort_num'];
	
	$autorisation_cohorte_modifier=autorisation_cohorte_modifier ($cohort_num,$user_num_session);
	if ($autorisation_cohorte_modifier=='ok' && $user_num_cohort!='' &&  $cohort_num!='') {
		$req="delete from   dwh_cohort_user_right where  cohort_num=$cohort_num and user_num=$user_num_cohort";
		$del=oci_parse($dbh,$req);
		oci_execute($del) || die ("erreur : user et right non supprimé<br>");
		
	}
	
	 affiche_liste_user_cohorte($cohort_num,$user_num_session);
}

if ($_POST['action']=='supprimer_cohorte') {
	$cohort_num=$_POST['cohort_num'];
	
	$autorisation_cohorte_modifier=autorisation_cohorte_modifier ($cohort_num,$user_num_session);
	if ($autorisation_cohorte_modifier=='ok' && $cohort_num!='') {
		$req="delete from   dwh_cohort where  cohort_num=$cohort_num ";
		$del=oci_parse($dbh,$req);
		oci_execute($del) || die ("erreur :  cohorte non supprimée<br>");
		
	}
	display_user_cohorts_table($user_num_session);
}

if ($_POST['action']=='modifier_titre_cohorte') {
	$cohort_num=$_POST['cohort_num'];
	$title_cohort=nettoyer_pour_insert(urldecode($_POST['title_cohort']));
	
	$autorisation_cohorte_modifier=autorisation_cohorte_modifier ($cohort_num,$user_num_session);
	if ($autorisation_cohorte_modifier=='ok' && $cohort_num!='') {
		$req="update dwh_cohort set title_cohort='$title_cohort' where  cohort_num=$cohort_num ";
		$upd=oci_parse($dbh,$req);
		oci_execute($upd) || die ("erreur :  cohorte non modifiée<br>");
		
	}
	display_user_cohorts_table($user_num_session);
}

if ($_POST['action']=='modifier_description_cohort') {
	$cohort_num=$_POST['cohort_num'];
	$description_cohort=trim(nettoyer_pour_insert(urldecode($_POST['description_cohort'])));
	
	$autorisation_cohorte_modifier=autorisation_cohorte_modifier ($cohort_num,$user_num_session);
	if ($description_cohort==get_translation('ADD_COHORT_DESCRIPTION_CLIC_HERE',"Ajouter une description en cliquant ici")) {
		$description_cohort='';
	}
	if ($autorisation_cohorte_modifier=='ok' && $cohort_num!='') {
		$req="update dwh_cohort set description_cohort='$description_cohort' where  cohort_num=$cohort_num ";
		$upd=oci_parse($dbh,$req);
		oci_execute($upd) || die ("erreur :  cohorte non modifiée<br>");
		
	}
	if (trim($description_cohort)=='' && $autorisation_cohorte_modifier=='ok') {
		$description_cohort= get_translation('ADD_COHORT_DESCRIPTION_CLIC_HERE',"Ajouter une description en cliquant ici");
	}

	$description_cohort_voir=preg_replace("/\n/","<br>",$description_cohort);
	$description_cohort_voir=preg_replace("/''/","'",$description_cohort_voir);
	print $description_cohort_voir;
}


if ($_POST['action']=='rafraichir_liste_cohorte') {
	afficher_cohorte_ligne('');
}


if ($_POST['action']=='select_cohorte' ) {
	$cohort_num_encours=$_POST['cohort_num_encours'];
	list($titre_cohorte_encours,$nb_patient_cohorte)=recup_titre_cohorte($cohort_num_encours);
	print "$titre_cohorte_encours;$nb_patient_cohorte";
}

if ($_POST['action']=='inclure_patient_cohorte') {
	$cohort_num_encours=$_POST['cohort_num_encours'];
	$patient_num=$_POST['patient_num'];
	$status=$_POST['status'];
	$query_num=$_POST['query_num'];
	$autorisation_cohorte_ajouter_patient=autorisation_cohorte_ajouter_patient ($cohort_num_encours,$user_num_session);
	if ($autorisation_cohorte_ajouter_patient=='ok') {
	
		$sel=oci_parse($dbh,"select count(*) as verif_deja_inclu from dwh_cohort_result where   cohort_num=$cohort_num_encours and  patient_num =$patient_num ");
		oci_execute($sel);
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$verif_deja_inclu=$r['VERIF_DEJA_INCLU'];
		
		if ($verif_deja_inclu==0 ) {
			$req="insert into dwh_cohort_result  (cohort_num , patient_num ,status,add_date,user_num_add,query_num) values ($cohort_num_encours,'$patient_num',$status,sysdate,$user_num_session,'$query_num')";
			$ins=oci_parse($dbh,$req);
			oci_execute($ins) || die ("<strong style=\"color:red\">erreur : patient non ajouté à la cohorte</strong><br>");
		} else {
			$req="update dwh_cohort_result set status=$status,add_date=sysdate,user_num_add=$user_num_session,query_num='$query_num' where  cohort_num=$cohort_num_encours and patient_num=$patient_num ";
			$upd=oci_parse($dbh,$req);
			oci_execute($upd) || die ("<strong style=\"color:red\">erreur : patient non ajouté à la cohorte</strong><br>");
		}
	}
	print afficher_cohorte_nb_patient_statut ($cohort_num_encours,$user_num_session) ;
}


if ($_POST['action']=='colorer_patient_cohorte') {
	$cohort_num_encours=$_POST['cohort_num_encours'];
	$autorisation_cohorte_voir=autorisation_cohorte_voir ($cohort_num_encours,$user_num_session);
	if ($autorisation_cohorte_voir=='ok') {
		$sel=oci_parse($dbh,"select patient_num,status from dwh_cohort_result where cohort_num=$cohort_num_encours ");
		oci_execute($sel);
		while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$patient_num=$r['PATIENT_NUM'];
			$status=$r['STATUS'];
			if ($status==1) {
				print "\$('#id_tr_patient_$patient_num').css('backgroundColor','#D0EAD1');";
			}
			if ($status==0) {
				print "\$('#id_tr_patient_$patient_num').css('backgroundColor','#E9CDCE');";
			}
			if ($status==2) {
				print "\$('#id_tr_patient_$patient_num').css('backgroundColor','#ccf2ff');";
			}
			$nb_comment_cohorte=calcul_nb_comment_cohorte($patient_num,$cohort_num_encours);
			if ($nb_comment_cohorte>0) {
				print "\$('#id_img_pencil_resultat_$patient_num').attr('src','images/pencil_red.png');";
				print "\$('#id_img_pencil_cohorte_$patient_num').attr('src','images/pencil_red.png');";
			}
		}
	}
}

if ($_POST['action']=='liste_patient_cohorte_encours') {
	$cohort_num_encours=$_POST['cohort_num_encours'];
	$datamart_num=$_POST['datamart_num'];
	$status=$_POST['status'];
	$verif_autorisation_voir_patient_cohorte=verif_autorisation_voir_patient_cohorte ($cohort_num_encours,$user_num_session);
	if ($verif_autorisation_voir_patient_cohorte=='ok') {
		print liste_patient_cohorte_encours($cohort_num_encours,$status);
	}
}


if ($_POST['action']=='tout_inclure_exclure') {
	$cohort_num_encours=$_POST['cohort_num_encours'];
	$tmpresult_num=$_POST['tmpresult_num'];
	$status=$_POST['status'];
	$query_num=$_POST['query_num'];
	$datamart_num=$_POST['datamart_num'];
	$val_exclure_cohorte_resultat=$_POST['val_exclure_cohorte_resultat'];
	
	$autorisation_cohorte_ajouter_patient=autorisation_cohorte_ajouter_patient ($cohort_num_encours,$user_num_session);
	if ($autorisation_cohorte_ajouter_patient=='ok') {
		$filtre_sql="";
		if ($val_exclure_cohorte_resultat=='ok' && $cohort_num_encours!='') {
			$filtre_sql=" and patient_num not in (select patient_num from dwh_cohort_result where cohort_num=$cohort_num_encours and status in (0,1)) ";
		}
		
		$filter_query_user_right=filter_query_user_right("dwh_tmp_result_$user_num_session",$user_num_session,$_SESSION['dwh_droit_all_departments'.$datamart_num],$liste_service_session,$liste_document_origin_code_session);
	
		$sel_varpatient_num=oci_parse($dbh,"select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num and user_num=$user_num_session $filter_query_user_right $filtre_sql");
		oci_execute($sel_varpatient_num);
		while ($rpatient_num=oci_fetch_array($sel_varpatient_num,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$patient_num=$rpatient_num['PATIENT_NUM'];
			
			$sel=oci_parse($dbh,"select count(*) as verif_deja_inclu from dwh_cohort_result where   cohort_num=$cohort_num_encours and  patient_num =$patient_num ");
			oci_execute($sel);
			$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
			$verif_deja_inclu=$r['VERIF_DEJA_INCLU'];
			
			if ($verif_deja_inclu==0 ) {
				$req="insert into dwh_cohort_result  (cohort_num , patient_num ,status,add_date,user_num_add,query_num) values ($cohort_num_encours,'$patient_num',$status,sysdate,$user_num_session,'$query_num')";
				$ins=oci_parse($dbh,$req);
				oci_execute($ins) || die ("<strong style=\"color:red\">erreur : patient non ajouté à la cohorte</strong><br>");
			} else {
				$req="update dwh_cohort_result set status=$status,add_date=sysdate,user_num_add=$user_num_session,query_num='$query_num' where  cohort_num=$cohort_num_encours and patient_num=$patient_num ";
				$upd=oci_parse($dbh,$req);
				oci_execute($upd) || die ("<strong style=\"color:red\">erreur : patient non ajouté à la cohorte</strong><br>");
			}
		}
	}
	print afficher_cohorte_nb_patient_statut ($cohort_num_encours,$user_num_session) ;
}


if ($_POST['action']=='sauver_commenter_patient_cohorte') {
	$cohort_num=$_POST['cohort_num'];
	$patient_num=$_POST['patient_num'];
	$commentary=nettoyer_pour_insert(urldecode($_POST['commentary']));

	$sel=oci_parse($dbh,"select status from  dwh_cohort_result where   cohort_num=$cohort_num and  patient_num =$patient_num");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$status=$r['STATUS'];
	
	if ($status=='') {
		$req="insert into dwh_cohort_result  (cohort_num , patient_num ,status,add_date,user_num_add) values ($cohort_num,'$patient_num',3,sysdate,$user_num_session)";
		$ins=oci_parse($dbh,$req);
		oci_execute($ins) || die ("<strong style=\"color:red\">erreur : patient non ajouté à la cohorte</strong><br>");
	}

	$cohort_result_comment_num=get_uniqid();
	
	$req="insert into dwh_cohort_result_comment  (cohort_result_comment_num , cohort_num , patient_num , comment_date , user_num , commentary) 
						values ($cohort_result_comment_num,$cohort_num,$patient_num,sysdate,$user_num_session,'$commentary')";
	$ins=oci_parse($dbh,$req);
	oci_execute($ins) || die ("<strong style=\"color:red\">erreur : comment non ajouté à la cohorte</strong><br>");
}



if ($_POST['action']=='lister_commentaire_patient_cohorte') {
	$cohort_num=$_POST['cohort_num'];
	$patient_num=$_POST['patient_num'];
	$context=$_POST['context'];
	print lister_commentaire_patient_cohorte ($cohort_num,$patient_num,$context);
	
}


if ($_POST['action']=='nb_tous_les_commentaires_patient_cohorte') {
	$cohort_num=$_POST['cohort_num'];
	$sel_pat=oci_parse($dbh,"select count(*) as nb_patient from dwh_cohort_result_comment where cohort_num=$cohort_num ");
	oci_execute($sel_pat);
	$r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb_patient_cohorte_commentaire=$r_pat['NB_PATIENT'];
	print $nb_patient_cohorte_commentaire;
	
}


if ($_POST['action']=='supprimer_commentaire_cohorte') {
	$cohort_result_comment_num=$_POST['cohort_result_comment_num'];
	$cohort_num=$_POST['cohort_num'];
	$patient_num=$_POST['patient_num'];
	
	$del=oci_parse($dbh,"delete from  dwh_cohort_result_comment where cohort_result_comment_num=$cohort_result_comment_num and user_num=$user_num_session");
	oci_execute($del);
	oci_execute($del) || die ("<strong style=\"color:red\">erreur : comment non supprimé</strong><br>");
	
	$nb_comment_cohorte=calcul_nb_comment_cohorte($patient_num,$cohort_num);
	print "$nb_comment_cohorte"; 
}


if ($_POST['action']=='afficher_document_patient') {
	$document_num=$_POST['document_num'];
	$datamart_num=$_POST['datamart_num'];
	$requete=nettoyer_pour_requete(urldecode($_POST['requete']));
	print afficher_document_patient($document_num,$requete,$user_num_session);
}

if ($_POST['action']=='ajouter_droit_cohorte') {
	$cohort_num=$_POST['cohort_num'];
	$right=$_POST['right'];
	$user_num_cohort=$_POST['user_num_cohort'];
	$option=$_POST['option'];
	$autorisation_cohorte_modifier=autorisation_cohorte_modifier ($cohort_num,$user_num_session);
	if ($autorisation_cohorte_modifier=='ok' && $cohort_num!='' && $user_num_cohort!='' && $option!='') {
		$req="delete from dwh_cohort_user_right where cohort_num=$cohort_num and user_num=$user_num_cohort and right='$right'";
		$del=oci_parse($dbh,$req);
		oci_execute($del) || die ("erreur : user et right non sauvé<br>");
		if ($option=='ajouter') {
			$req="insert into dwh_cohort_user_right  (cohort_num , user_num ,right) values ($cohort_num,$user_num_cohort,'$right')";
			$ins=oci_parse($dbh,$req);
			oci_execute($ins) || die ("<strong style=\"color:red\">erreur : user et right non sauvé</strong><br>");
		}
	}
}





if ($_POST['action']=='supprimer_requete') {
	$query_num=$_POST['query_num'];
	
	$autorisation_requete_voir=autorisation_requete_voir ($query_num,$user_num_session);
	if ($autorisation_requete_voir=='ok' && $query_num!='') {
		$req="delete from dwh_query where query_num=$query_num ";
		$del=oci_parse($dbh,$req);
		oci_execute($del) || die ("erreur :  requete non supprimée<br>");
	}
	get_my_queries($user_num_session);
}

if ($_POST['action']=='modifier_requete') {
	$query_num=$_POST['query_num'];
	$title_query=nettoyer_pour_insert(urldecode($_POST['title_query']));
	$crontab_query=$_POST['crontab_query'];
	$crontab_periode=$_POST['crontab_periode'];
	
	$autorisation_requete_voir=autorisation_requete_voir ($query_num,$user_num_session);
	if ($autorisation_requete_voir=='ok' && $query_num!='') {
		$req="update dwh_query set title_query='$title_query',crontab_query='$crontab_query',crontab_periode='$crontab_periode' where  query_num=$query_num ";
		$upd=oci_parse($dbh,$req);
		oci_execute($upd) || die ("erreur :  requete non modifiée<br>");
		
	}
	get_my_queries($user_num_session);
}

if ($_POST['action']=='display_patients_all_queries') {
	$list_num_query=$_POST['list_num_query'];
	$list_num_cohort=$_POST['list_num_cohort'];
	
	$list_num_query=preg_replace("/^,/","",$list_num_query);
	$list_num_cohort=preg_replace("/^,/","",$list_num_cohort);
	
	print "<table id=\"id_tableau_patient_requete\" style=\"border: 0px solid black;\">";
	
	$sel = oci_parse($dbh,"select distinct patient_num from dwh_query_result 
						where 
						QUERY_NUM in (select QUERY_NUM from dwh_query where user_num=$user_num_session and query_type='sauve' and query_num in ($list_num_query) ) 
						and 
						patient_num not in (select patient_num from dwh_cohort_result where 
						(cohort_num in (select cohort_num from dwh_cohort where user_num=$user_num_session) 
						or cohort_num in (select cohort_num from dwh_cohort_user_right where user_num=$user_num_session  )  
						) and cohort_num in ($list_num_cohort)
						)
		");
	oci_execute($sel);
	while ($r = oci_fetch_array($sel, OCI_ASSOC)) {
		$patient_num=$r['PATIENT_NUM'];
		$patient= afficher_patient($patient_num,'requete','','');
		if ($patient!='') {
			print "<tr id=\"id_tr_patient_cohorte_$patient_num\"  style=\"border: 0px solid black;\" onmouseover=\"this.style.backgroundColor='#dcdff5';\" onmouseout=\"this.style.backgroundColor='#ffffff';\"><td  style=\"border: 0px solid black;\">$patient</td></tr>";
	        }
	}
	print "</table>";
}


if ($_POST['action']=='calcul_nb_patient_resultat') {
	$tmpresult_num=$_POST['tmpresult_num'];
	$val_exclure_cohorte_resultat=$_POST['val_exclure_cohorte_resultat'];
	$cohort_num_encours=$_POST['cohort_num_encours'];
	$datamart_num=$_POST['datamart_num'];
	$filtre_resultat_texte=nettoyer_pour_insert(urldecode(trim($_POST['filtre_resultat_texte'])));
        
        $filtre_sql='';
        if ($val_exclure_cohorte_resultat=='ok') {
                $filtre_sql.=" and not exists (select patient_num from dwh_cohort_result where cohort_num=$cohort_num_encours and  dwh_tmp_result_$user_num_session.patient_num=dwh_cohort_result.patient_num and status in (0,1)) ";
        }
        if ($filtre_resultat_texte!='') {
                $filtre_sql.=" and document_num in  (select document_num from dwh_text where document_num in (select document_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num and object_type='document') and  contains (enrich_text,'$filtre_resultat_texte')>0 and context='patient_text' and certainty=1) ";
        }
        
	$filter_query_user_right=filter_query_user_right("dwh_tmp_result_$user_num_session",$user_num_session,$_SESSION['dwh_droit_all_departments'.$datamart_num],$liste_service_session,$liste_document_origin_code_session);
	
        $sel = oci_parse($dbh,"select count(distinct patient_num) nb from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num $filter_query_user_right $filtre_sql ");   
        oci_execute($sel);
        $row = oci_fetch_array($sel, OCI_ASSOC);
        $nb_patient_user=$row['NB'];
        
        print $nb_patient_user;
        
}

if ($_POST['action']=='inclure_patient') {
	$status=$_POST['status'];
	$patient_num=$_POST['patient_num'];
	$cohort_num=$_POST['cohort_num'];
        
        if ($patient_num!='' && $cohort_num!='' && $status!='') {
        	$autorisation_cohorte_ajouter_patient=autorisation_cohorte_ajouter_patient ($cohort_num,$user_num_session);
        	$autorisation_voir_patient=autorisation_voir_patient ($patient_num,$user_num_session);
        	if ($autorisation_cohorte_ajouter_patient=='ok' && $autorisation_voir_patient=='ok') {
			$sel=oci_parse($dbh,"select count(*) as verif_deja_inclu from dwh_cohort_result where   cohort_num=$cohort_num and  patient_num =$patient_num ");
			oci_execute($sel);
			$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
			$verif_deja_inclu=$r['VERIF_DEJA_INCLU'];
			
			if ($verif_deja_inclu==0 ) {
				$req="insert into dwh_cohort_result  (cohort_num , patient_num ,status,add_date,user_num_add) values ($cohort_num,'$patient_num',$status,sysdate,$user_num_session)";
				$ins=oci_parse($dbh,$req);
				oci_execute($ins) || die ("<strong style=\"color:red\">erreur : patient non ajouté à la cohorte</strong><br>");
			} else {
				$req="update dwh_cohort_result set status=$status,add_date=sysdate,user_num_add=$user_num_session where  cohort_num=$cohort_num and patient_num=$patient_num ";
				$upd=oci_parse($dbh,$req);
				oci_execute($upd) || die ("<strong style=\"color:red\">erreur : patient non ajouté à la cohorte</strong><br>");
			}
			
			lister_cohorte_un_patient ($patient_num) ;
	        } else {
		       print "erreur";
	        }
        }
}
if ($_POST['action']=='afficher_cohorte_nb_patient_statut') {
	$cohort_num=$_POST['cohort_num'];
	print afficher_cohorte_nb_patient_statut ($cohort_num,$user_num_session);
}




if ($_GET['action']=='patient_quick_access') {
	$json='';
	if ($_SESSION['dwh_droit_patient_quick_access0']=='ok' && $_SESSION['dwh_droit_anonymized0']=='') {
		$term=supprimer_apost(trim(utf8_decode($_GET['term'])));
		$term=replace_accent($term);	
		$json="";
		$i=0;
		$req_date_nais='';
		if (preg_match("/[0-9][0-9]\/[0-9][0-9]\/[0-9][0-9][0-9][0-9]/i",$term,$matches)) {
			$date_nais=$matches[0];
			$req_date_nais=" and birth_date=to_date('$date_nais','DD/MM/YYYY') ";
			$term=trim(preg_replace("/[0-9][0-9]\/[0-9][0-9]\/[0-9][0-9][0-9][0-9]/i"," ",$term));
		} else if (preg_match("/[^0-9][0-9][0-9][0-9][0-9][^0-9]/i"," $term ")) {
			$date_nais=preg_replace("/.*[^0-9]([0-9][0-9][0-9][0-9])[^0-9].*/i","$1"," $term ");
			$req_date_nais=" and to_char(birth_date,'YYYY')='$date_nais' ";
			$term=trim(preg_replace("/[^0-9][0-9][0-9][0-9][0-9][^0-9]/i"," "," $term "));
		}
		
		if (!preg_match("/[0-9][0-9][0-9][0-9][0-9]/i",$term) || $req_date_nais!='') {
			$query_name=create_query_name ($term,"");
			$sel=oci_parse($dbh,"select  patient_num,lastname,firstname, to_char(birth_date,'DD/MM/YYYY') as birth_date  from dwh_patient where 
			$query_name
			$req_date_nais
			");
			oci_execute($sel);
			while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
				$patient_num=$r['PATIENT_NUM'];
				$hospital_patient_id=get_master_patient_id ($patient_num);
				$lastname=$r['LASTNAME'];
				$firstname=$r['FIRSTNAME'];
				$birth_date=$r['BIRTH_DATE'];
				if ($i>30) {
					if ($i==31) {
						$json.="{\"id\":\"\",\"label\":\" nb patients trop importants\",\"value\":\"\"},";
					}
					$i++;
				} else {
					$autorisation_voir_patient_nominative=autorisation_voir_patient_nominative ($patient_num,$user_num_session);
					if ($autorisation_voir_patient_nominative=='ok') {
						$i++;
						$json.="{\"id\":\"$patient_num\",\"label\":\"$firstname $lastname $birth_date ($hospital_patient_id)\",\"value\":\"$patient_num\"},";
					}
				}
			}
		} else {
			$patient_num=get_patient_num ($term);
			if ($patient_num!='') {
				$sel=oci_parse($dbh,"select  patient_num,lastname,firstname, to_char(birth_date,'DD/MM/YYYY') as birth_date  from dwh_patient where patient_num =$patient_num");
				oci_execute($sel);
				$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
				$patient_num=$r['PATIENT_NUM'];
				$hospital_patient_id=get_master_patient_id ($patient_num);
				$lastname=$r['LASTNAME'];
				$firstname=$r['FIRSTNAME'];
				$birth_date=$r['BIRTH_DATE'];
				$autorisation_voir_patient_nominative=autorisation_voir_patient_nominative ($patient_num,$user_num_session);
				if ($autorisation_voir_patient_nominative=='ok') {
					$i++;
					$json.="{\"id\":\"$patient_num\",\"label\":\"$firstname $lastname $birth_date ($hospital_patient_id)\",\"value\":\"$patient_num\"},";
				}
			}
		}
		
		if ($i==0 && preg_match("/[a-z]/i",$term)) {
			$query_name=create_query_name ($term,"soundex");
		
			$json_soundex='';
			$sel=oci_parse($dbh,"select  patient_num,lastname,firstname, to_char(birth_date,'DD/MM/YYYY') as birth_date  from dwh_patient where 
			$query_name
			$req_date_nais
			");
			oci_execute($sel);
			while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
				$patient_num=$r['PATIENT_NUM'];
				$hospital_patient_id=get_master_patient_id ($patient_num);
				$lastname=$r['LASTNAME'];
				$firstname=$r['FIRSTNAME'];
				$birth_date=$r['BIRTH_DATE'];
				if ($i>30) {
					if ($i==31) {
						$json_soundex.="{\"id\":\"\",\"label\":\" nb patients trop importants\",\"value\":\"\"},";
					}
					$i++;
				} else {
					$autorisation_voir_patient_nominative=autorisation_voir_patient_nominative ($patient_num,$user_num_session);
					if ($autorisation_voir_patient_nominative=='ok') {
						$i++;
						$json_soundex.="{\"id\":\"$patient_num\",\"label\":\"$firstname $lastname $birth_date ($hospital_patient_id)\",\"value\":\"$patient_num\"},";
					}
				}
			}
			if ($json_soundex!='') {
				$json.="{\"id\":\"\",\"label\":\" Phonetique \",\"value\":\"\"},$json_soundex";
			}
		}
		if ($i==0 && preg_match("/[a-z]/i",$term)) {
			$query_name=create_query_name ($term,"ortho");
			$json_ortho='';
			$sel=oci_parse($dbh,"select patient_num, lastname,firstname, to_char(birth_date,'DD/MM/YYYY') as birth_date from dwh_patient where $query_name $req_date_nais ");
			oci_execute($sel);
			while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
				$patient_num=$r['PATIENT_NUM'];
				$hospital_patient_id=get_master_patient_id ($patient_num);
				$lastname=$r['LASTNAME'];
				$firstname=$r['FIRSTNAME'];
				$birth_date=$r['BIRTH_DATE'];
				if ($i>10) {
					if ($i==11 ) {
						$json_ortho.="{\"id\":\"\",\"label\":\" nb patients trop importants\",\"value\":\"\"},";
						break;
					}
					$i++;
				} else {
					$autorisation_voir_patient_nominative=autorisation_voir_patient_nominative ($patient_num,$user_num_session);
					if ($autorisation_voir_patient_nominative=='ok') {
						$i++;
						$json_ortho.="{\"id\":\"$patient_num\",\"label\":\"$firstname $lastname $birth_date ($hospital_patient_id)\",\"value\":\"$patient_num\"},";
					}
				}
			}
			if ($json_ortho!='') {
				$json.="{\"id\":\"\",\"label\":\" orthographe approchante \",\"value\":\"\"},$json_ortho";
			}
		}
		if ($json=='') {
			$json.="{\"id\":\"\",\"label\":\"Aucun patient trouvé $term\",\"value\":\"\"},";
		}
		$json=substr($json,0,-1);
		print "[$json]";
	} else {
		print "[{\"id\":\"\",\"label\":\"Vous n êtes pas autorisé\",\"value\":\"\"}]";
	}
	
}






if ($_POST['action']=='rechercher_code') {
	$num_filtre=$_POST['num_filtre'];
	$requete_texte=nettoyer_pour_requete(trim(urldecode($_POST['requete_texte'])));
	//$requete_texte=trim($requete_texte);
	//$requete_texte=preg_replace("/\"/"," ",$requete_texte);
	//$requete_texte=preg_replace("/'/","''",$requete_texte);
	//$requete_texte=preg_replace("/-/","\-",$requete_texte);
	$thesaurus_code=$_POST['thesaurus_code'];
	
	$sans_filtre=$_POST['sans_filtre'];
	$thesaurus_data_father_num=$_POST['thesaurus_data_father_num'];
	if ($thesaurus_data_father_num=='') {
		$thesaurus_data_father_num=0;
		print "<hr>";
	}
	rechercher_examen_thesaurus ($requete_texte,$num_filtre,$thesaurus_code,$thesaurus_data_father_num,$sans_filtre);
}


if ($_POST['action']=='ajouter_formulaire_code') {
	$num_filtre=$_POST['num_filtre'];
	$thesaurus_data_num=$_POST['thesaurus_data_num'];
	
	ajouter_formulaire_code($num_filtre,$thesaurus_data_num);
}

if ($_POST['action']=='affiche_nuage_concepts') {
	$tmpresult_num=$_POST['tmpresult_num'];
	$phenotype_genotype=$_POST['phenotype_genotype'];
	$donnees_reelles_ou_pref=$_POST['donnees_reelles_ou_pref'];
	$type=$_POST['type'];
	$distance=$_POST['distance'];
	$liste_mot=cloud_concepts_json ($tmpresult_num,$phenotype_genotype,$donnees_reelles_ou_pref,$type,$distance);	
	print $liste_mot;
}
if ($_GET['action']=='affiche_tableau_concepts') {
	$tmpresult_num=$_GET['tmpresult_num'];
	$phenotype_genotype=$_GET['phenotype_genotype'];
	$donnees_reelles_ou_pref=$_GET['donnees_reelles_ou_pref'];
	$type=$_GET['type'];
	$distance=$_GET['distance'];
	$json=repartition_concepts_json ($tmpresult_num,$phenotype_genotype,1,0,$donnees_reelles_ou_pref,$type,$distance);
	print $json;
}

if ($_GET['action']=='affiche_tableau_data') {
	$tmpresult_num=$_GET['tmpresult_num'];
	$thesaurus_code=$_GET['thesaurus_code'];
	$json=repartition_data_json ($tmpresult_num,$thesaurus_code);
	print $json;
	save_log_page($user_num_session,"affiche_tableau_data $thesaurus_code");
}

if ($_GET['action']=='affiche_concepts') {
	$tmpresult_num=$_GET['tmpresult_num'];
	$phenotype_genotype=$_GET['phenotype_genotype'];
	$donnees_reelles_ou_pref=$_GET['donnees_reelles_ou_pref'];
	$type=$_GET['type'];
	$distance=$_GET['distance'];
	$age_concept_min=$_GET['age_concept_min'];
	$age_concept_max=$_GET['age_concept_max'];
	$json=repartition_concepts_general_json ($tmpresult_num,$phenotype_genotype,1,1,1,$donnees_reelles_ou_pref,$type,$distance,$age_concept_min,$age_concept_max);
	print $json;
	save_log_page($user_num_session,'engine_concepts');
}



if ($_POST['action']=='affiche_graph_concepts') {
	$tmpresult_num=$_POST['tmpresult_num'];
	$phenotype_genotype=$_POST['phenotype_genotype'];
	$donnees_reelles_ou_pref=$_POST['donnees_reelles_ou_pref'];
	$type=$_POST['type'];
	$distance=$_POST['distance'];
	$data=repartition_concepts_json ($tmpresult_num,$phenotype_genotype,0,'1',$donnees_reelles_ou_pref,$type,$distance);
	print $data;
}
if ($_POST['action']=='affiche_heatmap_concepts') {
	$tmpresult_num=$_POST['tmpresult_num'];
	$phenotype_genotype=$_POST['phenotype_genotype'];
	$donnees_reelles_ou_pref=$_POST['donnees_reelles_ou_pref'];
	$type=$_POST['type'];
	$distance=$_POST['distance'];
	$data=affiche_heatmap_concepts ($tmpresult_num,$phenotype_genotype,0,'1',$donnees_reelles_ou_pref,$type,$distance);
	print $data;
}
if ($_POST['action']=='liste_combinaison_concepts') {
	$tmpresult_num=$_POST['tmpresult_num'];
	$phenotype_genotype=$_POST['phenotype_genotype'];
	$donnees_reelles_ou_pref=$_POST['donnees_reelles_ou_pref'];
	$type=$_POST['type'];
	$distance=$_POST['distance'];
	$data=liste_combinaison_concepts ($tmpresult_num,$phenotype_genotype,$donnees_reelles_ou_pref,$type,$distance);
	print $data;
}
if ($_GET['action']=='affiche_tableau_go') {
	$tmpresult_num=$_GET['tmpresult_num'];
	$type=$_GET['type'];
	$json=repartition_go_json ($tmpresult_num,$type);
	print $json;
}

if ($_GET['action']=='affiche_tableau_go_data') {
	$tmpresult_num=$_GET['tmpresult_num'];
	$json=repartition_go_data_json ($tmpresult_num);
	print $json;
}

if ($_POST['action']=='repartition_concepts_resumer_texte') {
	$tmpresult_num=$_POST['tmpresult_num'];
	$phenotype_genotype=$_POST['phenotype_genotype'];
	$type=$_POST['type'];
	repartition_concepts_resumer_texte ($tmpresult_num,$phenotype_genotype,$type);
}

if ($_POST['action']=='ouvrir_plus_document') {
	$liste_document=$_POST['liste_document'];
	$tmpresult_num=$_POST['tmpresult_num'];
	$full_text_query=urldecode($_POST['full_text_query']);
	$tableau_liste_synonyme=recupere_liste_concept_full_texte ($full_text_query,50);
	$res=ouvrir_plus_document ($tmpresult_num,$liste_document,$full_text_query,$tableau_liste_synonyme);
	print $res;
}


if ($_POST['action']=='affiche_graph_pmsi') {
	$tmpresult_num=$_POST['tmpresult_num'];
	$thesaurus_code=urldecode($_POST['thesaurus_code']);
	$thesaurus_data_father_num=$_POST['thesaurus_data_father_num'];
	$type=$_POST['type'];
	$distance=$_POST['distance'];
	$res= graph_pmsi_json ($tmpresult_num,$thesaurus_data_father_num,$thesaurus_code,$type,$distance);
	print $res;
}



if ($_POST['action']=='affiche_tableau_pmsi') {
	$tmpresult_num=$_POST['tmpresult_num'];
	$thesaurus_code=urldecode($_POST['thesaurus_code']);
	$type=$_POST['type'];
	$res= affiche_tableau_pmsi ($tmpresult_num,$thesaurus_code,$type);
	print $res;
}

if ($_POST['action']=='trouver_libelle_pmsi') {
	$tmpresult_num=$_POST['tmpresult_num'];
	$thesaurus_code=$_POST['thesaurus_code'];
	$type=$_POST['type'];
	$thesaurus_data_father_num=$_POST['thesaurus_data_father_num'];
	$text=trim(urldecode($_POST['text']));
	$text=preg_replace("/[^a-z0-9]/i","",$text);
	
	
	$sel=oci_parse($dbh," select thesaurus_data_num from dwh_thesaurus_data where thesaurus_parent_num=$thesaurus_data_father_num and REGEXP_REPLACE(lower(concept_str),'[^a-z0-9]','')=lower('$text')");
	oci_execute($sel) || die ("erreur");
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$thesaurus_data_num=$r['THESAURUS_DATA_NUM'];
	
	print $thesaurus_data_num;
	
}

if ($_POST['action']=='trouver_libelle_pmsi_ancien') {
	$tmpresult_num=$_POST['tmpresult_num'];
	$thesaurus_code=$_POST['thesaurus_code'];
	$type=$_POST['type'];
	$thesaurus_data_father_num=$_POST['thesaurus_data_father_num'];
	$text=trim(urldecode($_POST['text']));
	$text=preg_replace("/[^a-z0-9]/i","",$text);
	
	if ($type=='sejour') {
		$req_type=" c.encounter_num in ( select encounter_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num and encounter_num is not null) and ";
	} else {
		$req_type=" c.patient_num in ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and ";
	}
	
	$sel=oci_parse($dbh," select thesaurus_data_num from dwh_thesaurus_data where thesaurus_parent_num=$thesaurus_data_father_num and REGEXP_REPLACE(lower(concept_str),'[^a-z0-9]','')=lower('$text')");
	oci_execute($sel) || die ("erreur");
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$thesaurus_data_num=$r['THESAURUS_DATA_NUM'];
	
	
	$sel=oci_parse($dbh," select count(*) as nb from dwh_thesaurus_data where thesaurus_parent_num=$thesaurus_data_num");
	oci_execute($sel) || die ("erreur");
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb=$r['NB'];
	if ($nb>0) {
		$req="
		  select count(*) as nb from (
        select a.thesaurus_data_son_num,patient_num from dwh_thesaurus_data_graph a, dwh_thesaurus_data_graph b, dwh_data c
             where a.thesaurus_data_father_num=$thesaurus_data_num  and a.thesaurus_code='$thesaurus_code'  and b.thesaurus_code='$thesaurus_code'  and c.thesaurus_code='$thesaurus_code' and
           	$req_type
                 a.distance=1 and
           a.thesaurus_data_son_num=b.thesaurus_data_father_num and
             b.thesaurus_data_son_num=c.thesaurus_data_num 
            union
        select thesaurus_data_son_num,patient_num from dwh_thesaurus_data_graph a, dwh_data c
             where a.thesaurus_data_father_num=$thesaurus_data_num  and a.thesaurus_code='$thesaurus_code'  and   c.thesaurus_code='$thesaurus_code' and
           	$req_type
                 a.distance=1 and
           a.thesaurus_data_son_num=c.thesaurus_data_num 
             ) t, dwh_thesaurus_data
             where thesaurus_data_son_num=thesaurus_data_num
		";
		$selval=oci_parse($dbh,"$req ");
		oci_execute($selval);
		$r=oci_fetch_array($selval,OCI_RETURN_NULLS+OCI_ASSOC);
		$nb=$r['NB'];
		if ($nb>0) {
			print $thesaurus_data_num;
		}
	}
	
}

if ($_POST['action']=='trouver_thesaurus_data_father_num') {
	$thesaurus_code=$_POST['thesaurus_code'];
	$thesaurus_data_num=$_POST['thesaurus_data_num'];
	
	$sel=oci_parse($dbh," select thesaurus_data_father_num from dwh_thesaurus_data_graph where thesaurus_data_son_num=$thesaurus_data_num and thesaurus_code='$thesaurus_code' and distance=1 ");
	oci_execute($sel) || die ("erreur");
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$thesaurus_data_father_num=$r['THESAURUS_DATA_FATHER_NUM'];
	
	print $thesaurus_data_father_num;
	
	
}

if ($_POST['action']=='rechercher_code_labo') {
	$requete_texte=urldecode($_POST['requete_texte']);
	$requete_texte=trim($requete_texte);
	$requete_texte=preg_replace("/\"/"," ",$requete_texte);
	$requete_texte=preg_replace("/'/","''",$requete_texte);
	$requete_texte=preg_replace("/-/","\-",$requete_texte);
	$sans_filtre=$_POST['sans_filtre'];
	$tmpresult_num=$_POST['tmpresult_num'];
	$thesaurus_data_father_num=$_POST['thesaurus_data_father_num'];
	if ($thesaurus_data_father_num=='') {
		$thesaurus_data_father_num=0;
	}
	
	rechercher_examen_thesaurus_labo ($requete_texte,$thesaurus_data_father_num,$sans_filtre,$tmpresult_num);
	save_log_page($user_num_session,"rechercher_code_labo");
}

if ($_POST['action']=='visualiser_graph_scatterplot') {
	$thesaurus_data_num=$_POST['thesaurus_data_num'];
	$tmpresult_num=$_POST['tmpresult_num'];
	visualiser_graph_scatterplot ($thesaurus_data_num,$tmpresult_num,'age_naissance','patient');
}
if ($_POST['action']=='visualiser_tableau_groupe') {
	$thesaurus_data_num=$_POST['thesaurus_data_num'];
	$tmpresult_num=$_POST['tmpresult_num'];
	visualiser_tableau_groupe ($thesaurus_data_num,$tmpresult_num);
}
if ($_POST['action']=='visualiser_tableau_all_exam') {
	$tmpresult_num=$_POST['tmpresult_num'];
	visualiser_tableau_all_exam ($tmpresult_num);
}

if ($_POST['action']=='pyramide_age') {
	$tmpresult_num=$_POST['tmpresult_num'];

	$tableau_interval=array('0-5', '5-10', '10-15', '15-20','20-25', '25-30', '30-35', '35-40', '40-45','45-50', '50-55', '55-60', '60-65', '65-70','70-75', '75-80', '80-85', '85-90', '90-95','95-100', '100-');
	$liste_interval_libelle="'0-4', '5-9', '10-14', '15-19','20-24', '25-29', '30-34', '35-39', '40-44','45-49', '50-54', '55-59', '60-64', '65-69','70-74', '75-79', '80-84', '85-89', '90-94','95-99', '100 +'";

	$year=get_translation('YEAR','an');
	$years=get_translation('YEARS','ans');
	$tableau_interval_jeune=array('0-1', '1-2', '2-3', '3-4','4-5', '5-6', '6-7', '7-8', '8-9','9-10', '10-11', '11-12', '12-13', '13-14','14-15', '15-16', '16-17', '17-18', '18-19','19-20');
	$liste_interval_libelle_jeune="'< 1 $year','1 $year', '2 $years', '3 $years', '4 $years','5 $years', '6 $years', '7 $years', '8 $years', '9 $years', '10 $years', '11 $years', '12 $years', '13 $years', '14 $years', '15 $years', '16 $years', '17 $years', '18 $years', '19 $years'";	
	
	pyramide_age ($tmpresult_num,$tableau_interval,$liste_interval_libelle,'id_pyramide_age_document','document',get_translation('JS_AGE_STRUCTURE_AT_FIRST_DOCUMENT','Pyramide des âges au 1er document trouvé'));
	pyramide_age_vivant_dcd ($tmpresult_num,$tableau_interval,$liste_interval_libelle,'id_pyramide_age_today',get_translation('JS_AGE_STRUCTURE_TODAY',"Pyramide des âges aujourd'hui"));

	pyramide_age ($tmpresult_num,$tableau_interval_jeune,$liste_interval_libelle_jeune,'id_pyramide_age_document_jeune','document',get_translation('JS_AGE_STRUCTURE_LOWER_20Y_AT_FIRST_DOCUMENT','Pyramide des âges  < 20 ans au 1er document trouvé'));
	pyramide_age_vivant_dcd ($tmpresult_num,$tableau_interval_jeune,$liste_interval_libelle_jeune,'id_pyramide_age_today_jeune',get_translation('JS_AGE_STRUCTURE_LOWER_20Y_TODAY',"Pyramide des âges < 20 ans aujourd'hui"));
	save_log_page($user_num_session,'engine_stat');
}

if ($_POST['action']=='nb_patient_sex') {
	$tmpresult_num=$_POST['tmpresult_num'];
	nb_patient_sex ($tmpresult_num);
}


if ($_POST['action']=='nb_document_document_origin_code') {
	$tmpresult_num=$_POST['tmpresult_num'];
	$id_div=$_POST['id_div'];
	nb_document_document_origin_code ($tmpresult_num,$id_div);
}


if ($_POST['action']=='nb_patients_temps') {
	$tmpresult_num=$_POST['tmpresult_num'];
	$id_div=$_POST['id_div'];
	$option_affichage=$_POST['option_affichage'];
	nb_patients_temps ($tmpresult_num,$id_div,$option_affichage);
}

if ($_POST['action']=='nb_nouveau_patients_service') {
	$tmpresult_num=$_POST['tmpresult_num'];
	$id_div=$_POST['id_div'];
	nb_nouveau_patients_service ($tmpresult_num,$id_div);
}

if ($_POST['action']=='nb_nouveau_patients_service_hors_mespatients') {
	$tmpresult_num=$_POST['tmpresult_num'];
	$id_div=$_POST['id_div'];
	nb_nouveau_patients_service_hors_mespatients ($tmpresult_num,$id_div);
}


if ($_POST['action']=='nb_consult_per_unit_per_year_tableau') {
	$tmpresult_num=$_POST['tmpresult_num'];
	nb_consult_per_unit_per_year_tableau ($tmpresult_num);
}

if ($_POST['action']=='nb_hospit_per_unit_per_year_tableau') {
	$tmpresult_num=$_POST['tmpresult_num'];
	nb_hospit_per_unit_per_year_tableau ($tmpresult_num);
}

if ($_POST['action']=='nb_patient_per_unit_per_year_tableau') {
	$tmpresult_num=$_POST['tmpresult_num'];
	nb_patient_per_unit_per_year_tableau ($tmpresult_num);
}

if ($_POST['action']=='calcul_max_concepts') {
	$tmpresult_num=$_POST['tmpresult_num'];
	$max_distance_concepts=max_distance_concepts ($tmpresult_num);
	if ($max_distance_concepts=='') {
		$max_distance_concepts=1;
	}
	print $max_distance_concepts;
}



	

function supprimer_stop_word_phrase ($text) {
	$liste_stop_word=array('par','les','te','se','la','ca','des','a','sur','de','la','le','au','en','y','l','d','s');
	foreach ($liste_stop_word as $word) {
		$text=preg_replace("/([^a-z])$word([^a-z])/","$1 $2",$text);
	}
	return $text;
}
//(maladies respiratoires or Pneumopathie% or bronchi%) and infection% and (adenopathie% or amygdalectomie)
if ($_POST['action']=='reecrire_requete_ancien') {
	$text=nettoyer_pour_requete(urldecode(trim($_POST['text'])));
	if (!preg_match("/near\(/i",$text)) {
		$texte_not=preg_replace("/.*(not .*)$/i","$1",$text);
		$text=preg_replace("/not .*$/i","",$text);
		$text=preg_replace("/([a-z])[^a-z]+or[^a-z]+([a-z])/i","$1_or_$2",$text);
		
		$text=preg_replace("/([^a-z])et([^a-z])/i","$1 and $2",$text);
		$text=preg_replace("/([^a-z])and([^a-z])/i","$1 and $2",$text);
		$text=preg_replace("/[(),]/"," ",$text);
		$text=supprimer_stop_word_phrase ($text);
	
		if (preg_match("/ and /i",$text)) {
			$tableau_requete=preg_split("/ and |\s/i",$text);
		} else {
			$tableau_requete=preg_split("/ /i",$text);
		}
		$requete="near((";
		foreach ($tableau_requete as $req) {
			$req=trim($req);
			if ($req!='') {
				$requete.="$req%,";
			}
		}
		$requete=substr($requete,0,-1);
		$requete.="),5,TRUE) $texte_not";
		
		$requete=preg_replace("/_or_/i"," or ",$requete);
		print $requete;
	}
}

//(maladies respiratoires or Pneumopathie% or bronchi%) and infection% and (adenopathie% or amygdalectomie)
//near((maladies%,respiratoires or Pneumopathie or bronchi%%,infection%%,adenopathie or amygdalectomie%),5,TRUE) (maladies respiratoires or Pneumopathie% or bronchi%) and infection% and (adenopathie% or amygdalectomie)
if ($_POST['action']=='reecrire_requete') {
	$text=nettoyer_pour_requete(urldecode(trim($_POST['text'])));
	$text=supprimer_stop_word_phrase ($text);
	if (!preg_match("/near\(/i",$text)) {
		if (preg_match("/ not /i",$text)) {
			$texte_not=preg_replace("/.*(not .*)$/i","$1",$text);
			$text=preg_replace("/not .*$/i","",$text);
		}
		$text=preg_replace("/([a-z])[^a-z]+or[^a-z]+([a-z])/i","$1_or_$2",$text);
		
		if (preg_match("/ and /i",$text)) {
			$text=preg_replace("/([^a-z])et([^a-z])/i","$1 and $2",$text);
			$text=preg_replace("/([^a-z])and([^a-z])/i","$1 and $2",$text);
			$text=preg_replace("/[(),]/"," ",$text);
			$tableau_requete=preg_split("/ and /i",$text);
		} else {
			$tableau_requete=preg_split("/ /i",$text);
		}
		
		$requete="near((";
		foreach ($tableau_requete as $req) {
			$req=trim($req);
			if ($req!='') {
				$requete.="$req%,";
			}
		}
		$requete=substr($requete,0,-1);
		$requete.="),5,TRUE) $texte_not";
		
		$requete=preg_replace("/_or_/i"," or ",$requete);
		
		print $requete;
	}
}



if ($_POST['action']=='afficher_onglet_concept_patient') {
	$patient_num=$_POST['patient_num'];
	save_log_page($user_num_session,'patient_concept');
	
	print "<h2>".get_translation('CONCEPTS','Concepts')."</h2>
	<span class=\"bouton_bio\" id=\"id_bouton_biologie_cr\"><a href=\"#\" onclick=\"deplier('id_liste_concepts_patient','block');plier('id_liste_concepts_patient_et_resume');return false;\">".get_translation('PATIENT_CONCEPTS_SIMPLE_DISPLAY','Affichage simplifié')."</a></span> <span class=\"bouton_bio\">-</span> 
	<span class=\"bouton_bio\" id=\"id_bouton_biologie_cr\"><a href=\"#\" onclick=\" afficher_onglet_concepts_patient_et_resume ('$patient_num');deplier('id_liste_concepts_patient_et_resume','block');plier('id_liste_concepts_patient');return false;\">".get_translation('PATIENT_CONCEPTS_DISPLAY_FOR_VALIDATION','Affichage pour validation')."</a></span> 
	<div id=\"id_div_list_div_affichage\"></div>
	<div style=\"width:800px;display:block;\" id=\"id_liste_concepts_patient\">
	";
	repartition_concepts_tableau_patient ($patient_num,'id_tableau_concepts_patient',"and dwh_enrsem.concept_code in (select concept_code from dwh_thesaurus_enrsem where genotype=1 or phenotype=1 ) ",'pref'); 
	print "</div>
	<div style=\"width:100%;display:none;\" id=\"id_liste_concepts_patient_et_resume\">";
	print "</div>";
}



if ($_POST['action']=='afficher_onglet_concepts_patient_et_resume') {
	$patient_num=$_POST['patient_num'];
	repartition_concepts_tableau_patient_resume ($patient_num,'id_tableau_concepts_patient_et_resume',"and dwh_enrsem.concept_code in (select concept_code from dwh_thesaurus_enrsem where genotype=1 or phenotype=1 ) ",'pref'); 

}




if ($_POST['action']=='generer_map') {
	$tmpresult_num=$_POST['tmpresult_num'];
	$localisation_list="";
	$req="select  residence_city, residence_latitude,residence_longitude from dwh_patient where patient_num in (select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num)
	and  residence_longitude is not null and residence_latitude is not null ";
	$sel=oci_parse($dbh,$req);
	oci_execute($sel) ;
	while ($res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$residence_city=str_replace("'"," ",$res['RESIDENCE_CITY']);
		$residence_latitude=str_replace(",",".",$res['RESIDENCE_LATITUDE']);
		$residence_longitude=str_replace(",",".",$res['RESIDENCE_LONGITUDE']);
		$nb_patient=$res['NB_PATIENT'];
		if ($residence_longitude!='-' && $residence_latitude!='-') {
			$localisation_list.="['X',$residence_latitude,$residence_longitude],";
		}
	}
	$localisation_list=substr($localisation_list,0,-1);
	print "[$localisation_list]";
}


if ($_POST['action']=='afficher_repartition_par_pays') {
	$tmpresult_num=$_POST['tmpresult_num'];
	print "<table class=\"tablefin\" id=\"id_tableau_repartition_par_pays\">";
	print "<thead>
		<tr>
			<th>Country</th>
			<th>Nb patients résidents</th>
			<th>% patients résidents / entrepôt</th>
			<th>Nb patients nés</th>
			<th>% patients nés / entrepôt</th>
		</tr>
		</thead>
		<tbody>";
	$tableau_pays=array();
	$req="select birth_country,count(distinct dwh_tmp_result_$user_num_session.patient_num) NB_PATIENT from dwh_tmp_result_$user_num_session, dwh_patient where tmpresult_num=$tmpresult_num and dwh_tmp_result_$user_num_session.patient_num=dwh_patient.patient_num and birth_country is not null group by birth_country";
	$sel=oci_parse($dbh,$req);
	oci_execute($sel) ;
	while ($res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$birth_country=$res['BIRTH_COUNTRY'];
		$nb_patient=$res['NB_PATIENT'];
		
		$birth_country_query=preg_replace("/'/","''",$birth_country);
		$req_dwh="select round(100*$nb_patient/count(*),2) POURC_PATIENT_DWH from  dwh_patient where birth_country='$birth_country_query' ";
		$sel_dwh=oci_parse($dbh,$req_dwh);
		oci_execute($sel_dwh) ;
		$res_dwh=oci_fetch_array($sel_dwh,OCI_RETURN_NULLS+OCI_ASSOC);
		$pourc_patient_dwh=$res_dwh['POURC_PATIENT_DWH'];
		$pourc_patient_dwh=str_replace(",",".",$pourc_patient_dwh);
		
		$tableau_pays[$birth_country]=$birth_country;
		$tableau_nb_patient_pays_naissance[$birth_country]=$nb_patient;
		$tableau_pourc_patient_pays_naissance[$birth_country]=$pourc_patient_dwh;
	}
	
	$req="select residence_country,count(distinct dwh_tmp_result_$user_num_session.patient_num) NB_PATIENT from dwh_tmp_result_$user_num_session, dwh_patient where tmpresult_num=$tmpresult_num and dwh_tmp_result_$user_num_session.patient_num=dwh_patient.patient_num and residence_country is not null group by residence_country";
	$sel=oci_parse($dbh,$req);
	oci_execute($sel) ;
	while ($res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$residence_country=$res['RESIDENCE_COUNTRY'];
		$nb_patient=$res['NB_PATIENT'];
		
		$residence_country_query=preg_replace("/'/","''",$residence_country);
		$req_dwh="select round(100*$nb_patient/count(*),2) POURC_PATIENT_DWH from  dwh_patient where residence_country='$residence_country_query' ";
		$sel_dwh=oci_parse($dbh,$req_dwh);
		oci_execute($sel_dwh) ;
		$res_dwh=oci_fetch_array($sel_dwh,OCI_RETURN_NULLS+OCI_ASSOC);
		$pourc_patient_dwh=$res_dwh['POURC_PATIENT_DWH'];
		$pourc_patient_dwh=str_replace(",",".",$pourc_patient_dwh);
		
		$tableau_pays[$residence_country]=$residence_country;
		$tableau_nb_patient_pays[$residence_country]=$nb_patient;
		$tableau_pourc_patient_pays[$residence_country]=$pourc_patient_dwh;
	}
	foreach ($tableau_pays as $country => $p) {
		if ($tableau_nb_patient_pays[$country]=='') {
			$tableau_nb_patient_pays[$country]=0;
		}
		if ($tableau_pourc_patient_pays[$country]=='') {
			$tableau_pourc_patient_pays[$country]=0;
		}
		if ($tableau_nb_patient_pays_naissance[$country]=='') {
			$tableau_nb_patient_pays_naissance[$country]=0;
		}
		if ($tableau_pourc_patient_pays_naissance[$country]=='') {
			$tableau_pourc_patient_pays_naissance[$country]=0;
		}
		print "<tr><td>$country</td><td>$tableau_nb_patient_pays[$country]</td><td>$tableau_pourc_patient_pays[$country]</td><td>$tableau_nb_patient_pays_naissance[$country]</td><td>$tableau_pourc_patient_pays_naissance[$country]</td></tr>";
	}
	
	print "</tbody></table>";
	save_log_page($user_num_session,'engine_map');
}

if ($_POST['action']=='modify_hospital_patient_id') {
	if ($_SESSION['dwh_droit_modify_patient']=='ok') {
		$patient_num=$_POST['patient_num'];
		$hospital_patient_id_new=$_POST['hospital_patient_id_new'];
		$hospital_patient_id_ancien=$_POST['hospital_patient_id'];
		
		if ($patient_num!='' && $hospital_patient_id_new!='') {
			$patient_num_new_maitre=modify_hospital_patient_id ($patient_num,$hospital_patient_id_ancien,$hospital_patient_id_new);
			$patient=afficher_patient ($patient_num_new_maitre,'patient','','');
			print "$patient";
		} else {
			$patient=afficher_patient ($patient_num,'patient','','');
			print "$patient";
		}
	}
	save_log_page($user_num_session,'modify_patient');
}

if ($_POST['action']=='demande_acces_patient') {
	$department_num=$_POST['department_num'];
	$tmpresult_num=$_POST['tmpresult_num'];
	$num_user_manager_department=$_POST['num_user_manager_department'];
	$query_num=$_POST['query_num'];
	
	$sel = oci_parse($dbh, "select xml_query  from dwh_query where query_num=$query_num ");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	if ($r['XML_QUERY']) {
		$xml_query=$r['XML_QUERY']->load();
	}
	$readable_query=readable_query ($xml_query);

	$request_access_num=get_uniqid();
		
	$requeteins="insert into dwh_request_access ( request_access_num,user_num_request,department_num,nuser_num_department_manager,readable_query,request_access_date) 
			values ($request_access_num,$user_num_session,$department_num,$num_user_manager_department,:readable_query,sysdate)";
	$stmtupd = ociparse($dbh,$requeteins);
	$rowid = ocinewdescriptor($dbh, OCI_D_LOB);
	ocibindbyname($stmtupd, ":readable_query",$readable_query );
	$execState = ociexecute($stmtupd);
                          
	$req=" SELECT distinct patient_num  from  dwh_tmp_resultall_$user_num_session WHERE tmpresult_num = $tmpresult_num and department_num=$department_num
    and patient_num not in (SELECT patient_num from  dwh_tmp_result_$user_num_session WHERE tmpresult_num = $tmpresult_num )";
	$sel=oci_parse($dbh,"$req");
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)){
		$patient_num=$r['PATIENT_NUM'];
		$ins=oci_parse($dbh,"insert into dwh_request_access_patient (request_access_num , patient_num ) values ($request_access_num , $patient_num ) ");
		oci_execute($ins);
	}
	sauver_notification ($user_num_session,$num_user_manager_department,'demande_acces',"Demande d'accès",$request_access_num);
	save_log_page($user_num_session,'demande_acces_patient');
}




if ($_POST['action']=='supprimer_demande_acces') {
	$request_access_num=$_POST['request_access_num'];
	$autorisation_demande_proprietaire=autorisation_demande_proprietaire ($request_access_num,$user_num_session);
	if ($autorisation_demande_proprietaire=='ok' && $request_access_num!='') {
		
		$req="delete from dwh_request_access_patient where request_access_num=$request_access_num ";
		$del=oci_parse($dbh,$req);
		oci_execute($del) || die ("erreur :  requete non supprimée<br>");
		
		$req="delete from dwh_request_access where request_access_num=$request_access_num ";
		$del=oci_parse($dbh,$req);
		oci_execute($del) || die ("erreur :  requete non supprimée<br>");
	}
	print "<ul>
		<li>Demandes en attente";
		  lister_mes_demandes($user_num_session,'attente','mes_demandes');
	print "</li>
		<li>Demandes acceptées";
		  lister_mes_demandes($user_num_session,'ok','mes_demandes');
	print "</li>
				<li>Demandes refusées";
		  lister_mes_demandes($user_num_session,'pasok','mes_demandes');
	print "</li>
	</ul>";
	save_log_page($user_num_session,'supprimer_demande_acces');
}


if ($_POST['action']=='autoriser_demande_acces') {
	$request_access_num=$_POST['request_access_num'];
	$manager_agreement=$_POST['manager_agreement'];
	$autorisation_demande_manager_department=autorisation_demande_manager_department ($request_access_num,$user_num_session);
	if ($autorisation_demande_manager_department=='ok' && $request_access_num!='') {
		$req_detail='';
		if ($manager_agreement==-1) {
			$req_detail=",viewed_by_manager_notok_date=''";
		}
		if ($manager_agreement==1) {
			$req_detail=",viewed_by_manager_ok_date=''";
		}
		$req="update  dwh_request_access set manager_agreement='$manager_agreement',manager_agreement_date=sysdate $req_detail where request_access_num=$request_access_num ";
		$upd=oci_parse($dbh,$req);
		oci_execute($upd) || die ("erreur :  requete non validee<br>");
		
	}

	print "<ul>
		<li>Demandes en attente";
		  lister_mes_demandes($user_num_session,'attente','a_traiter');
	print "</li>
		<li>Demandes acceptées";
		  lister_mes_demandes($user_num_session,'ok','a_traiter');
	print "</li>
				<li>Demandes refusées";
		  lister_mes_demandes($user_num_session,'pasok','a_traiter');
	print "</li>
	</ul>";
	
	$sel=oci_parse($dbh,"select user_num_request from dwh_request_access  ");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$user_num_request=$r['USER_NUM_REQUEST'];
	sauver_notification ($user_num_session,$user_num_request,'accord_acces',"Accord pour l'accès",$request_access_num);
	
	save_log_page($user_num_session,'autoriser_demande_acces');
}

if ($_POST['action']=='supprimer_patient_demande_acces') {
	$request_access_num=$_POST['request_access_num'];
	$patient_num=$_POST['patient_num'];
	$autorisation_demande_proprietaire=autorisation_demande_proprietaire ($request_access_num,$user_num_session);
	if ($autorisation_demande_proprietaire=='ok' && $request_access_num!='' && $patient_num!='') {
		
		$req="delete from dwh_request_access_patient where request_access_num=$request_access_num and patient_num=$patient_num";
		$del=oci_parse($dbh,$req);
		oci_execute($del) || die ("erreur :  requete non supprimée<br>");
		
	}
	save_log_page($user_num_session,'supprimer_patient_demande_acces');
	
}

if ($_POST['action']=='crontab_alerte_demande_acces') {
  
	$sel=oci_parse($dbh,"select  count(*) nb1 from dwh_request_access where nuser_num_department_manager=$user_num_session and viewed_by_manager_date is null ");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb1=$r['NB1']; // non vu par responsable service
  
	$sel=oci_parse($dbh,"select  count(*) nb2 from dwh_request_access where user_num_request=$user_num_session and  manager_agreement=1 and viewed_by_manager_ok_date is null ");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb2=$r['NB2']; // response ok non vu par demandeur
  
	$sel=oci_parse($dbh,"select  count(*) nb3 from dwh_request_access where user_num_request=$user_num_session and  manager_agreement=-1 and viewed_by_manager_notok_date is null ");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb3=$r['NB3'];// response pas ok  non vu par demandeur
	
	$nb_total=$nb1+$nb2+$nb3;
	$nb_mes_demandes=$nb2+$nb3;
	$nb_a_traiter=$nb1;
	print "$nb_total,$nb_mes_demandes,$nb_a_traiter";
}

if ($_POST['action']=='correction_orthographique') {
	$text=strtolower(nettoyer_pour_requete(urldecode(trim($_POST['text']))));
	$num_filtre=$_POST['num_filtre'];
	$resultat='';
	if ($text!='') {
		$tableau_term_deja[$text]='ok';		
	
		$sel=oci_parse($dbh,"select  lower(term) as term ,distance,rownum from (select term,UTL_MATCH.EDIT_DISTANCE ('$text' ,lower(term) ) as distance from dwh_thesaurus_corrortho where UTL_MATCH.EDIT_DISTANCE ('$text' ,lower(term) )>0    order by UTL_MATCH.EDIT_DISTANCE ('$text' ,lower(term) ) asc ) t where  rownum<4");
		oci_execute($sel);
		while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$term=trim($r['TERM']);
			$term=preg_replace("/'/"," ",$term);
			if ($term!='' && $tableau_term_deja[$term]=='') {
				$resultat.="<a onclick=\"remplacer_texte('$num_filtre','$term');\" class=\"correction_orthographique_proposition\" style=\"cursor:pointer\">$term</a><br>";
				$tableau_term_deja[$term]='ok';
			}
		}	

		if (preg_match("/[a-z][a-z]es?\s/i","$text ")) {
			$texte_jockerise=preg_replace("/([a-z][a-z]e)s?\s/i","$1% ","$text ");
			$texte_jockerise=trim($rtexte_jockerise);
			if ($texte_jockerise!='' && $tableau_term_deja[$texte_jockerise]=='') {
				$resultat.="<a onclick=\"remplacer_texte('$num_filtre','$texte_jockerise');\" class=\"correction_orthographique_proposition\" style=\"cursor:pointer\">$texte_jockerise</a><br>";
				$tableau_term_deja[$texte_jockerise]='ok';
			}
		}
		if (preg_match("/\s/i","$text ")) {
			$texte_jockerise=preg_replace("/([a-z][a-z]e)s?\s/i","$1% ","$text ");
			$texte_and=preg_replace("/([0-9a-z%])\s([0-9a-z])/i","$1 and $2"," $texte_jockerise ");
			$texte_and=preg_replace("/ and and and /i"," and "," $texte_jockerise ");
			$texte_and=trim($texte_and);
			if ($texte_and!='' && $tableau_term_deja[$texte_and]=='') {
				$resultat.="<a onclick=\"remplacer_texte('$num_filtre','$texte_and');\" class=\"correction_orthographique_proposition\" style=\"cursor:pointer\">$texte_and</a><br>";
				$tableau_term_deja[$texte_and]='ok';
			}
		}
	}
	print "$resultat";
}

if ($_POST['action']=='partager_requete_en_cours') {
	$notification_text=urldecode(trim($_POST['notification_text']));
	$liste_num_user_partage=urldecode(trim($_POST['liste_num_user_partage']));
	$query_num=$_POST['query_num'];
	partager_requete_en_cours ($user_num_session,$liste_num_user_partage,$notification_text,$query_num);
	save_log_page($user_num_session,'partager_requete_en_cours');
}
if ($_POST['action']=='crontab_alerte_notification') {
	
	$sel=oci_parse($dbh,"select  count(*) nb from dwh_notification where user_num_receiver=$user_num_session and view_date is null ");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb=$r['NB']; // non vu par responsable service
  
	print "$nb";
}


if ($_POST['action']=='open_notification') {
	$sel=oci_parse($dbh,"select notification_num,user_num_sent, notification_type, notification_text, shared_element_num, to_char(notification_date,'DD/MM/YYYY') ||' à '|| to_char(notification_date,'HH24:MI') notification_date_char,notification_date, view_date from dwh_notification where user_num_receiver=$user_num_session and hide_receiver is null order by notification_date desc, notification_num desc");
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$notification_num=$r['NOTIFICATION_NUM'];
		$user_num_sent=$r['USER_NUM_SENT'];
		$notification_type=$r['NOTIFICATION_TYPE'];
		$notification_text=$r['NOTIFICATION_TEXT'];
		$shared_element_num=$r['SHARED_ELEMENT_NUM'];
		$notification_date_char=$r['NOTIFICATION_DATE_CHAR'];
		$view_date=$r['VIEW_DATE'];
		$user_name=get_user_information ($user_num_sent,'pn');
		$notification="";
		if ($notification_type=='message_prive') {
			if (preg_match_all("/https?:\/\/[^ \t\n]+/i",$notification_text,$out)) {
				foreach ($out[0] as $url) {
					$notification_text=str_replace("$url","<a href=\"$url\" tagert=\"_blanck\" style=\"color: #036ba5;\">shared url</a>",$notification_text);
				}
			}
			$notification=" <strong>$user_name</strong> ".get_translation('SENT_YOU_A_MESSAGE','Sent you a message')." : <br>$notification_text";
			$notification.="<br><a href=\"#\"  style=\"color: #036ba5;\" onclick=\"repondre_message_prive('$user_num_sent');\">Answer</a>";
		}
		if ($notification_type=='recipisse_message_prive') {
			if (preg_match_all("/https?:\/\/[^ \t\n]+/i",$notification_text,$out)) {
				foreach ($out[0] as $url) {
					$notification_text=str_replace("$url","<a href=\"$url\" tagert=\"_blanck\" style=\"color: #036ba5;\">shared url</a>",$notification_text);
				}
			}
			$notification=" ".get_translation('YOU_SENT_THIS_MESSAGE_TO','Vous avez envoyé ce message à')." <strong>$user_name</strong>: <br>$notification_text";
		}
		
		if ($notification_type=='requete') {
			$notification="<a href=\"moteur.php?action=preremplir_requete&query_num=$shared_element_num\">";
			$notification.=" <strong>$user_name</strong>  ".get_translation('SHARED_A_QUERY_WITH_YOU_NAMED','a partagé une requête avec vous intitulée')." <strong style=\"color: #036ba5;\">$notification_text</strong>";
			$notification.="</a>";
		}
		if ($notification_type=='cohorte') {
			$notification="<a href=\"mes_cohortes.php?cohort_num_voir=$shared_element_num\">";
			$notification.=" <strong>$user_name</strong> ".get_translation('SHARED_THE_COHORT','a partagé la cohorte')."   <strong style=\"color: #036ba5;\">$notification_text</strong> avec vous ";
			$notification.="</a>";
		}
		if ($notification_type=='demande_acces') {
			$notification="<a href=\"mes_demandes.php?action=a_traiter&request_access_num=$shared_element_num\">";
			$notification.="<strong>$user_name</strong> ".get_translation('ASKED_YOU_ACCESS_TO_PATIENTS',"vous a fait une demande d'accès à des patients")."  ";
			$notification.="</a>";
		}
		if ($notification_type=='accord_acces') {
			$notification="<a href=\"mes_demandes.php?action=mes_demandes&request_access_num=$shared_element_num\">";
			$notification.="<strong>$user_name</strong> ".get_translation('GRANTED_ACCESS_TO_PATIENTS',"vous a accordé l'accès aux patients")."  ";
			$notification.="</a>";
		}
		if ($notification_type=='process') {
			$process=get_process($shared_element_num);
			$user_num=$process['USER_NUM'];
			if ($user_num==$user_num_session) {
				$file_name=$process['COMMENTARY'];
				$process_category=$process['CATEGORY_PROCESS'];
				if ($file_name!='') {
					$notification="<strong>".get_translation('PROCESS_TERMINATED',"Process terminé ")." $process_category:</strong><br><strong  style=\"color: #036ba5;\"> <a href=\"export_process.php?process_num=$shared_element_num\">";
					$notification.=" ".get_translation('DOWNLOAD_IT',"Téléchargez-le ")." : $file_name ";
					$notification.="</a></strong>";
				}
			}
		}
		if ($view_date=='') {
			$si_date_vue=" onmouseover=\"notification_marquer_lue($notification_num);\" style=\"color:black;background-color:#d1dfee\"";
		} else {
			$si_date_vue=" style=\"color:grey\"";
		}
		if ($notification!='') {
			$res.="<tr class=\"td_notification\" id=\"id_tr_notification_$notification_num\" ><td id=\"id_td_notification_$notification_num\" class=\"td_notification\" $si_date_vue>";
			$res.="<br><i>".get_translation('DATE_THE',"Le")." $notification_date_char</i> ";
			$res.="$notification</td><td>
			<img src=\"images/poubelle_moyenne.png\" width=\"20\" onclick=\"delete_notification($notification_num);\" style=\"cursor:pointer\">
			</td></tr>
			<tr  id=\"id_hr_notification_$notification_num\"><td colspan=2><hr></td></tr>";
		}
	}
	if ($res!='') {
		print "<table class=\"table_notification\" width=\"100%\" style=\"word-wrap:break-word;\">$res</table>";
	
	}
}

if ($_POST['action']=='notification_marquer_lue') {
	$notification_num=$_POST['notification_num'];
	
	$upd=oci_parse($dbh,"update dwh_notification set view_date=sysdate where notification_num=$notification_num and user_num_receiver=$user_num_session ");
	oci_execute($upd);
}



if ($_POST['action']=='delete_notification') {
	$notification_num=$_POST['notification_num'];
	
	$upd=oci_parse($dbh,"update dwh_notification set hide_receiver=1,view_date=sysdate  where notification_num=$notification_num and user_num_receiver=$user_num_session ");
	oci_execute($upd);
	
}

if ($_POST['action']=='delete_process') {
	$process_num=$_POST['process_num'];
	if ($process_num!='') {	
		$upd=oci_parse($dbh,"delete from dwh_process_patient where process_num=$process_num and process_num in (select process_num from dwh_process where process_num=$process_num and user_num=$user_num_session)");
		oci_execute($upd);
		
		$upd=oci_parse($dbh,"delete from dwh_process where process_num=$process_num and user_num=$user_num_session ");
		oci_execute($upd);
	}
}

if ($_POST['action']=='envoyer_message_prive') {
	$message_prive=urldecode(trim($_POST['message_prive']));
	$num_user_message_prive=$_POST['num_user_message_prive'];

	sauver_notification ($user_num_session,$num_user_message_prive,'message_prive',$message_prive,'');
	sauver_notification ($num_user_message_prive,$user_num_session,'recipisse_message_prive',$message_prive,'');
}


if ($_POST['action']=='insert_outil' && $_SESSION['dwh_droit_admin']=='ok') {
	$tableau['TITLE']=urldecode($_POST['title']);
	$tableau['DESCRIPTION']=urldecode($_POST['description']);
	$tableau['AUTHORS']=urldecode($_POST['authors']);
	$tableau['URL']=urldecode($_POST['url']);
	insert_outil($tableau);
	admin_lister_outil () ;
}

if ($_POST['action']=='update_outil' && $_SESSION['dwh_droit_admin']=='ok') {
	$tableau['TOOL_NUM']=urldecode($_POST['tool_num']);
	$tableau['TITLE']=urldecode($_POST['title']);
	$tableau['DESCRIPTION']=urldecode($_POST['description']);
	$tableau['AUTHORS']=urldecode($_POST['authors']);
	$tableau['URL']=urldecode($_POST['url']);
	update_outil($tableau);
}

if ($_POST['action']=='delete_outil' && $_SESSION['dwh_droit_admin']=='ok') {
	$tool_num=$_POST['tool_num'];
	delete_outil($tool_num);
}


if ($_GET['action']=='load_file' ) {
	$file_num=$_GET['file_num'];
	$load_file=load_file($file_num,$_GET['preview']);
	
	header("Pragma: public");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	print "$load_file";
}

if ($_POST['action']=='generer_resultat' ) {
	$query_num=$_POST['query_num'];
	$tmpresult_num=$_POST['tmpresult_num'];
	$datamart_num=$_POST['datamart_num'];
	$sel = oci_parse($dbh, "select xml_query , to_char(QUERY_DATE,'DD/MM/YYYY HH24:MI') as DATE_REQUETE_CHAR, QUERY_DATE,user_num from dwh_query where query_num=$query_num");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	if ($r['XML_QUERY']) {
		$xml=$r['XML_QUERY']->load();
	}
	generer_resultat($xml,$tmpresult_num);
	$tab_nb_result=get_nb_result($tmpresult_num,$datamart_num);
	$nb_patient=$tab_nb_result['nb_patient'];
	$nb_document=$tab_nb_result['nb_document'];
	$nb_mvt=$tab_nb_result['nb_mvt'];
	$nb_patient_user=$tab_nb_result['nb_patient_user'];
	$nb_document_user=$tab_nb_result['nb_document_user'];
	$nb_mvt_user=$tab_nb_result['nb_mvt_user'];
	#print "$nb_patient,$nb_document,$nb_patient_user,$nb_document_user";
	print "nb_patient='$nb_patient';nb_document='$nb_document';nb_patient_user='$nb_patient_user';nb_document_user='$nb_document_user';nb_mvt_user='$nb_mvt_user';nb_mvt='$nb_mvt';";
}

if ($_POST['action']=='create_process' ) {
	$category_process=$_POST['category_process'];
	$process_end_date=$_POST['process_end_date'];
	$commentary=$_POST['commentary'];
	if ($process_end_date=='') {
		$process_end_date="sysdate+4";
	}
	if ($commentary=='') {
		$commentary="debut";
	}
	$process_num=get_uniqid();
	create_process ($process_num,$user_num_session,"0",$commentary,"",$process_end_date,$category_process);
	print "$process_num";
}


if ($_POST['action']=='check_process_status' ) {
	$process_num=$_POST['process_num'];
	$process=get_process  ($process_num);
	print $process['STATUS'].";".$process['COMMENTARY'];
}



if ($_POST['action']=='list_query_history' ) {
	$datamart_num=$_POST['datamart_num'];
	print list_query_history ($datamart_num,$user_num_session);
}


if ($_GET['action']=='get_json_query_history' ) {
	$datamart_num=$_GET['datamart_num'];
	
	$start=$_GET['start'];
	$length=$_GET['length'];
	if ($length==-1) {
		$length=100000000000000000;
	}
	$draw=$_GET['draw'];
	//$search=nettoyer_requete_texte_fulltxt(trim(utf8_decode($_GET['search']['value'])));
	$search=trim(utf8_decode($_GET['search']['value']));
	
	$order_column=$_GET['order'][0]['column'];
	$order_dir=$_GET['order'][0]['dir'];
	
	print get_json_query_history ($datamart_num,$user_num_session,$start,$length,$draw,$search,$order_column,$order_dir);
}




?>