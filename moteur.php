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
include "head.php";
include "menu.php";
session_write_close();

?>

<script type="text/javascript" src="tablednd.js"></script>
<link rel="stylesheet" type="text/css" href="jqcloud/jqcloud.css" />
<script type="text/javascript" src="jqcloud/jqcloud-1.0.4.js"></script>
<script type="text/javascript" src="javascript_concept.js?v=<? print $date_today_unique; ?>"></script>
<script type="text/javascript" src="javascript_pmsi.js?v=<? print $date_today_unique; ?>"></script>
<script type="text/javascript" src="javascript_labo.js?v=<? print $date_today_unique; ?>"></script>
<script language="javascript">
	$(document).ready(function(){
	    $('.autosizejs').autosize();   
	});
</script>
<?
if ($_POST['tmpresult_num']=='' && $_GET['tmpresult_num']=='') {
	$sel = oci_parse($dbh,"select dwh_temp_seq.nextval as tmpresult_num from  dual " );   
	oci_execute($sel);
	$row = oci_fetch_array($sel, OCI_ASSOC);
	$tmpresult_num=$row['TMPRESULT_NUM'];
} else {
	
	if ($_GET['tmpresult_num']=='') {
		$tmpresult_num=$_POST['tmpresult_num'];
	} else {
		$tmpresult_num=$_GET['tmpresult_num'];
	}
}
if ($_GET['cohort_num_encours']=='') {
	$cohort_num_encours=$_POST['cohort_num_encours'];
} else {
	$cohort_num_encours=$_GET['cohort_num_encours'];
}

if ($_POST['action']=='rechercher') {
	$max_num_filtre=$_POST['max_num_filtre'];
	$xml="<query>";
	$full_text_query="";
	for ($i=1;$i<=$max_num_filtre;$i++) {
		if ($_POST['text_'.$i]!='' && strlen(trim($_POST['text_'.$i]))>1 || $_POST['chaine_requete_code_'.$i]!='') {
			$xml_unitaire="<text_filter>";
			
			
			//// text 
			$xml_unitaire.="<text>".nettoyer_pour_requete(trim($_POST['text_'.$i]))."</text>";
			$xml_unitaire.="<synonym_expansion>".$_POST['etendre_syno_'.$i]."</synonym_expansion>";
			$full_text_query.=nettoyer_pour_requete(trim($_POST['text_'.$i])).";requete_unitaire;";
			//// code
			if (trim($_POST['thesaurus_data_num_'.$i])!='') {
				$libelle_code=get_data_concept_str (trim($_POST['thesaurus_data_num_'.$i]));
				$full_text_query.=nettoyer_pour_requete(trim($libelle_code)).";requete_unitaire;";
			}
			$xml_unitaire.="<thesaurus_data_num>".trim($_POST['thesaurus_data_num_'.$i])."</thesaurus_data_num>";
			$xml_unitaire.="<str_structured_query>".trim($_POST['chaine_requete_code_'.$i])."</str_structured_query>";
			$xml_unitaire.="<query_type>".trim($_POST['query_type_'.$i])."</query_type>";
			
			$xml_unitaire.="<exclude>".trim($_POST['exclure_'.$i])."</exclude>";
			//$xml_unitaire.="<document_origin_code>".trim($_POST['document_origin_code_'.$i])."</document_origin_code>";
			$xml_unitaire.="<context>".trim($_POST['context_'.$i])."</context>";
			$xml_unitaire.="<certainty>".trim($_POST['certainty_'.$i])."</certainty>";
			$xml_unitaire.="<title_document>".trim($_POST['title_document_'.$i])."</title_document>";
			$xml_unitaire.="<document_date_start>".trim($_POST['date_deb_document_'.$i])."</document_date_start>";
			$xml_unitaire.="<document_date_end>".trim($_POST['date_fin_document_'.$i])."</document_date_end>";
			$xml_unitaire.="<document_last_nb_days>".trim($_POST['document_last_nb_days_'.$i])."</document_last_nb_days>";
			$xml_unitaire.="<period_document>".trim($_POST['periode_document_'.$i])."</period_document>";
			$xml_unitaire.="<document_ageyear_start>".trim($_POST['age_deb_document_'.$i])."</document_ageyear_start>";
			$xml_unitaire.="<document_ageyear_end>".trim($_POST['age_fin_document_'.$i])."</document_ageyear_end>";
			$xml_unitaire.="<document_agemonth_start>".trim($_POST['agemois_deb_document_'.$i])."</document_agemonth_start>";
			$xml_unitaire.="<document_agemonth_end>".trim($_POST['agemois_fin_document_'.$i])."</document_agemonth_end>";
			$xml_unitaire.="<filter_num>".trim($_POST['num_filtre_'.$i])."</filter_num>";
			$xml_unitaire.="<datamart_text_num>$datamart_num</datamart_text_num>";
			$liste_department_num='';
			if (is_array($_POST['unite_heberg_'.$i])) {
				foreach ($_POST['unite_heberg_'.$i] as $val) {
					if ($val!='') {
						$liste_department_num.="$val,";
					}
				}
				$liste_department_num=substr($liste_department_num,0,-1);
			}
			$xml_unitaire.="<hospital_department_list>".trim($liste_department_num)."</hospital_department_list>";
			
			$liste_document_origin_code='';
			if (is_array($_POST['document_origin_code_'.$i])) {
				foreach ($_POST['document_origin_code_'.$i] as $val) {
					if ($val!='') {
						$liste_document_origin_code.="$val,";
					}
				}
				$liste_document_origin_code=substr($liste_document_origin_code,0,-1);
			}
			$xml_unitaire.="<document_origin_code>".trim($liste_document_origin_code)."</document_origin_code>";
			
			if ($_POST['texte_nbresult_'.$i]=='') {
				$requete_sql_filtre_texte=creer_requete_sql_filtre("$xml_unitaire</text_filter>","patient_num");
				$nb=calcul_nb_resultat_filtre($requete_sql_filtre_texte);
				$xml_unitaire.="<count_result>$nb</count_result>";
			} else {
				$xml_unitaire.="<count_result>".trim($_POST['texte_nbresult_'.$i])."</count_result>";
			}
			$xml_unitaire.="</text_filter>";
			$xml.=$xml_unitaire;
		}
		if ($_POST['query_type_'.$i]=='contrainte_temporelle' && $_POST['query_key_contrainte_temporelle_'.$i]!='') {
			$tab_query_key_contrainte=explode(";",$_POST['query_key_contrainte_temporelle_'.$i]);
			$num_filtre=$tab_query_key_contrainte[0];
			$num_filtre_a=$tab_query_key_contrainte[1];
			$num_filtre_b=$tab_query_key_contrainte[2];
			$type_contrainte=$tab_query_key_contrainte[3];
			$minmax=$tab_query_key_contrainte[4];
			$unite_contrainte=$tab_query_key_contrainte[5];
			$duree_contrainte=$tab_query_key_contrainte[6];
			$contrainte_nbresult=trim($_POST['contrainte_nbresult_'.$i]);
			$xml_unitaire="<time_constraint>";
				$xml_unitaire.="<filter_num>".trim($num_filtre)."</filter_num>";
				$xml_unitaire.="<time_filter_num_a>".trim($num_filtre_a)."</time_filter_num_a>";
				$xml_unitaire.="<time_filter_num_b>".trim($num_filtre_b)."</time_filter_num_b>";
				$xml_unitaire.="<time_constraint_type>".trim($type_contrainte)."</time_constraint_type>";
				$xml_unitaire.="<minmax>".trim($minmax)."</minmax>";
				$xml_unitaire.="<time_constraint_unit>".trim($unite_contrainte)."</time_constraint_unit>";
				$xml_unitaire.="<time_constraint_duration>".trim($duree_contrainte)."</time_constraint_duration>";
				$xml_unitaire.="<time_constraint_count_result>".trim($contrainte_nbresult)."</time_constraint_count_result>";
			$xml_unitaire.="</time_constraint>";
			$xml.=$xml_unitaire;
		}
		if ($_POST['query_type_'.$i]=='mvt' && $_POST['query_key_'.$i]!='') {
			$exclude=trim($_POST['exclure_'.$i]);
			$num_filtre=trim($_POST['num_filtre_'.$i]);
			$mvt_department=trim($_POST['mvt_department_'.$i]);
			$mvt_unit=trim($_POST['mvt_unit_'.$i]);
			$type_mvt=trim($_POST['type_mvt_'.$i]);
			
			$encounter_duration_min=trim($_POST['encounter_duration_min_'.$i]);
			$encounter_duration_max=trim($_POST['encounter_duration_max_'.$i]);
			
			$mvt_duration_min=trim($_POST['mvt_duration_min_'.$i]);
			$mvt_duration_max=trim($_POST['mvt_duration_max_'.$i]);
			
			$mvt_nb_min=trim($_POST['mvt_nb_min_'.$i]);
			$mvt_nb_max=trim($_POST['mvt_nb_max_'.$i]);
			
			$stay_nb_min=trim($_POST['stay_nb_min_'.$i]);
			$stay_nb_max=trim($_POST['stay_nb_max_'.$i]);
			
			$mvt_last_nb_days=trim($_POST['mvt_last_nb_days_'.$i]);
			
			$mvt_date_start=trim($_POST['mvt_date_start_'.$i]);
			$mvt_date_end=trim($_POST['mvt_date_end_'.$i]);
			$periode_mvt=trim($_POST['periode_mvt_'.$i]);
			$mvt_ageyear_start=trim($_POST['mvt_ageyear_start_'.$i]);
			$mvt_ageyear_end=trim($_POST['mvt_ageyear_end_'.$i]);
			$mvt_agemonth_start=trim($_POST['mvt_agemonth_start_'.$i]);
			$mvt_agemonth_end=trim($_POST['mvt_agemonth_end_'.$i]);
		
			$mvt_agemonth_end=trim($_POST['mvt_agemonth_end_'.$i]);
			
			$xml_unitaire="<mvt_filter>";
			$xml_unitaire.="<query_type>mvt</query_type>
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
<filter_num>$num_filtre</filter_num>
<exclude>$exclude</exclude>
";
			if ($_POST['texte_nbresult_'.$i]=='') {
				$requete_sql_filtre_mvt=creer_requete_sql_filtre_mvt("$xml_unitaire</mvt_filter>","patient_num");
				$nb=calcul_nb_resultat_filtre($requete_sql_filtre_mvt);
				$xml_unitaire.="<count_result>$nb</count_result>";
			} else {
				$xml_unitaire.="<count_result>".trim($_POST['texte_nbresult_'.$i])."</count_result>";
			}
			$xml_unitaire.="</mvt_filter>";
			$xml.=$xml_unitaire;
		}
	}
	
	
	$xml.="<datamart_num>$datamart_num</datamart_num>";
	$xml.="<sex>".trim($_POST['sex'])."</sex>";
	$xml.="<age_start>".trim($_POST['age_deb'])."</age_start>";
	$xml.="<age_end>".trim($_POST['age_fin'])."</age_end>";
	
	$xml.="<alive_death>".trim($_POST['vivant_dcd'])."</alive_death>";
	$xml.="<age_death_start>".trim($_POST['age_dcd_deb'])."</age_death_start>";
	$xml.="<age_death_end>".trim($_POST['age_dcd_fin'])."</age_death_end>";
	
	$xml.="<first_stay_date_start>".trim($_POST['date_deb_1ervenue'])."</first_stay_date_start>";
	$xml.="<first_stay_date_end>".trim($_POST['date_fin_1ervenue'])."</first_stay_date_end>";
	$xml.="<minimum_period_folloup>".trim($_POST['duree_minimum_prise_en_charge'])."</minimum_period_folloup>";
	
	$liste_cohorte_exclue='';
	if (is_array($_POST['cohorte_exclue'])) {
		foreach ($_POST['cohorte_exclue'] as $val) {
			if ($val!='') {
				$liste_cohorte_exclue.="$val,";
			}
		}
		$liste_cohorte_exclue=substr($liste_cohorte_exclue,0,-1);
	}
	$xml.="<list_excluded_cohort>".trim($liste_cohorte_exclue)."</list_excluded_cohort>";
	
	$xml.="</query>";
	$query_num=sauver_requete_temp($xml);
} else {
	if ($datamart_num!='' && $datamart_num!='0') {
		$max_num_filtre=1;
		$xml_unitaire.="<text_filter>";
			$xml_unitaire.="<text></text>";
			$xml_unitaire.="<synonym_expansion></synonym_expansion>";
			$xml_unitaire.="<thesaurus_data_num></thesaurus_data_num>";
			$xml_unitaire.="<str_structured_query></str_structured_query>";
			$xml_unitaire.="<exclude></exclude>";
			$xml_unitaire.="<document_origin_code></document_origin_code>";
			$xml_unitaire.="<context></context>";
			$xml_unitaire.="<certainty></certainty>";
			$xml_unitaire.="<document_date_start></document_date_start>";
			$xml_unitaire.="<document_date_end></document_date_end>";
			$xml_unitaire.="<document_last_nb_days></document_last_nb_days>";
			$xml_unitaire.="<period_document></period_document>";
			$xml_unitaire.="<document_ageyear_start></document_ageyear_start>";
			$xml_unitaire.="<document_ageyear_end></document_ageyear_end>";
			$xml_unitaire.="<document_agemonth_start></document_agemonth_start>";
			$xml_unitaire.="<document_agemonth_end></document_agemonth_end>";
			$xml_unitaire.="<filter_num>1</filter_num>";
			$xml_unitaire.="<datamart_text_num>$datamart_num</datamart_text_num>";
			$xml_unitaire.="<hospital_department_list></hospital_department_list>";
			$xml_unitaire.="<count_result></count_result>";
			$xml_unitaire.="<query_type>text</query_type>";
		$xml_unitaire.="</text_filter>";
		
		$xml="<query>";
		$xml.="$xml_unitaire<datamart_num>$datamart_num</datamart_num>";
		$xml.="<sex></sex>";
		$xml.="<age_start></age_start>";
		$xml.="<age_end></age_end>";
		$xml.="<alive_death></alive_death>";
		$xml.="<age_death_start></age_death_start>";
		$xml.="<age_death_end></age_death_end>";
		$xml.="<first_stay_date_start></first_stay_date_start>";
		$xml.="<first_stay_date_end></first_stay_date_end>";
		$xml.="<minimum_period_folloup></minimum_period_folloup>";
		$xml.="<list_excluded_cohort></list_excluded_cohort>";
		$xml.="</query>";
		$query_num=sauver_requete_temp($xml);
	}
}

?>
<a name="ancre_entete_moteur"> </a>
<table border=0 width="100%">
<tr>
	<td style="vertical-align:top;width:450px;">
		<form method="post" action="moteur.php" id="id_form_moteur">
			<h1><? print get_translation('SEARCH_PATIENTS','Rechercher des patients'); ?></h1>
			<h2><? print $titre_global; ?></h2>
			<div id="id_div_formulaire_document">
				<?
					if ($xml=='') {
						$max_num_filtre=1;
						add_atomic_query($max_num_filtre,'text');
					} else {
						ajouter_filtre_texte($xml);
					}
					
				?>
			</div>
			<h2 style="cursor:pointer;" onclick="add_atomic_query('text');">+ <? print get_translation('ADD_FULLTEXT_FILTER','Ajouter un filtre Full text'); ?></h2>
			<h2 style="cursor:pointer;" onclick="add_atomic_query('code');">+ <? print get_translation('ADD_STRUCTURED_FILTER','Ajouter un filtre structuré'); ?></h2>
			<h2 style="cursor:pointer;" onclick="add_atomic_query_mvt();">+ <? print get_translation('ADD_MOVMENT_FILTER','Ajouter un filtre mouvement'); ?></h2>
			
			<h2 style="cursor:pointer;" onclick="ajouter_filtre_select_contrainte ();plier_deplier('id_div_formulaire_contrainte_temporelle');"><span id="plus_id_div_formulaire_contrainte_temporelle">+</span> <? print get_translation('TEMPORAL_CONSTRAINTS','Contraintes temporelles'); ?></h2>
			<div id="id_div_formulaire_contrainte_temporelle" class="div_filtre" style="display:none;">
				<?
					selection_contrainte_temporelle();
				?>
			</div>
			
			<h2 style="cursor:pointer;" onclick="plier_deplier('id_div_formulaire_patient');"><span id="plus_id_div_formulaire_patient">+</span> <? print get_translation('PATIENT_FILTER','Filtre patient'); ?></h2>
			<div id="id_div_formulaire_patient" class="div_filtre" style="display:none;">
				<?
					ajouter_formulaire_patient();
				?>
			</div>
			<br>
			<input type="hidden" name="max_num_filtre" id="id_input_max_num_filtre" value="<? print $max_num_filtre; ?>">
			<input type="hidden" name="tmpresult_num" id="id_num_temp" value="<? print $tmpresult_num; ?>">
			<input type="hidden" name="datamart_num" id="id_num_datamart" value="<? print $datamart_num; ?>">
			<input type="hidden" name="cohort_num_encours" id="id_cohort_num_encours" value="<? print $cohort_num_encours; ?>">
			
			<input type="hidden" name="action" value="rechercher">
			<span id="id_bouton_submit_moteur"style="display:block;">
				<input type=button onclick="pre_calcul_total();" value="<? print get_translation('SEARCH_LAUNCH','Lancer la recherche'); ?>" class="form_submit"> <input type=button onclick="javascript:startIntro(); return false;" value="?" class="form_submit">
			</span>
			<span id="id_bouton_attendre_moteur" style="display:none;">
				<input type=button id="id_button_bouton_attendre_moteur" value="<? print get_translation('WAIT_END_PRECOMPUTING','Attendez que le pré calcul se termine'); ?>" class="form_submit_wait" onclick="valider_quand_precalcul_termine();">
			</span>
		</form>
		<script language="javascript">
		
			function pre_calcul_total() {
				nb_num_filtre_en_cours=0;
				max_num_filtre=jQuery('#id_input_max_num_filtre').val();
				for (num_filtre=1;num_filtre<=max_num_filtre;num_filtre++) {
					if (document.getElementById('id_input_filtre_texte_'+num_filtre)) {
						calcul_nb_resultat_filtre(num_filtre,true);
					}				
					if (document.getElementById('id_div_filtre_contrainte_temporelle_'+num_filtre)) {
						calcul_nb_resultat_contrainte_temporelle(num_filtre,true);
					}				
					if (document.getElementById('id_div_filtre_mvt_'+num_filtre)) {
						calcul_nb_resultat_filtre_mvt(num_filtre,true);
					}
					
						
				}
				valider_quand_precalcul_termine();
			}
			function valider_quand_precalcul_termine() {
				jQuery('#id_button_bouton_attendre_moteur').val('<? print get_translation('JS_SEARCH_WILL_START_WHEN_PRECOMPUTING_FINISHED','La recherche se lancera une fois le précalcul terminé'); ?>');
				jQuery('#id_button_bouton_attendre_moteur').css('background-color','red');
				if (nb_num_filtre_en_cours==0) {
					document.getElementById('id_form_moteur').submit();
				} else {
					setTimeout("valider_quand_precalcul_termine()",1000);
				}
			}
		</script>
		
		<div id="id_liste_requete_sauve" style="width:450px;display:none;">
			<h1><? print get_translation('SAVED_QUERIES','Requêtes sauvées'); ?></h1>
			<div id="id_div_liste_requete_sauve" style="width:400px;">
				<? print lister_requete_sauve ($datamart_num); ?>
			</div>
		</div>
		<div id="id_list_query_history" style="width:450px;">
			<h1><? print get_translation('QUERIES_HISTORY','Historique requête'); ?></h1>
			<!--<div id="id_div_list_query_history" style="width:400px;height:400px;overflow-y:auto;">-->
			<div id="id_div_list_query_history" style="width:400px;">
			       <table border="0" class="tableau_liste_requete" id="id_tableau_liste_requete" width="400px">
			        <thead><th style="width:18px;padding:2px;">&nbsp;</th><th><? print get_translation('DATE','date'); ?></th><th><? print get_translation('QUERIES','Requêtes'); ?></th></thead>
			       </table>
			</div>
		</div>
		<?
		if ($xml!='') {
			print "<script language=\"javascript\">";
       			 $max_num_filtre=0;
			peupler_filtre_texte($xml);
			peupler_filtre_mvt($xml);
			peupler_contrainte_temporelle($xml);
			peupler_filtre_patient($xml);
       			print "jQuery('#id_input_max_num_filtre').val(\"$max_num_filtre\");";
			print "</script>";
		}
		?>
	</td>
	<td style="vertical-align:top">
	<?
	if ($xml!='') {
	
	
	
		// affichage onglet par defaut ///
		
		$display_pmsi='none';
		$class_menu_pmsi='';
		$display_stat='none';
		$class_menu_stat='';
		$display_detail='none';
		$class_menu_detail='';
		$afficher_result="
		generer_resultat('$query_num','$tmpresult_num','$datamart_num') ;
		";
		if ($_SESSION['dwh_droit_see_detailed'.$datamart_num]=='ok') {
			$display_detail='block';
			$class_menu_detail=' current ';
			$initiate_result="voir_detail_dwh('resultat_detail');";
		} else {
			if ($_SESSION['dwh_droit_see_stat'.$datamart_num]=='ok') {
				$display_stat='block';
				$class_menu_stat=' current ';
				$initiate_result="voir_detail_dwh('stat');affiche_onglet_stat( $tmpresult_num);";
			} else {
				if ($_SESSION['dwh_droit_see_drg'.$datamart_num]=='ok') {
					$display_pmsi='block';
					$class_menu_pmsi=' current ';
					$initiate_result="voir_detail_dwh('pmsi');affiche_onglet_pmsi($tmpresult_num,'sejour','ok','$thesaurus_code_pmsi');";
				} 
			}
		}
	?>
		<div id="tabs" style="width:100%">
			<ul id="tab-links">
			<? if ($_SESSION['dwh_droit_see_detailed'.$datamart_num]=='ok') { ?>
					<li class="<? print $class_menu_detail; ?> color-bullet" id="id_bouton_resultat_detail"><span class="li-content"><img src="images/detail.png" border="0" style="vertical-align:middle"> <a href="#" onclick="voir_detail_dwh('resultat_detail');return false;"><? print get_translation('RESULT','Résultat'); ?></a></span></li>
			<? } ?>
			<? if ($_SESSION['dwh_droit_see_detailed'.$datamart_num]=='ok') { ?>
				<? //	<li class="color-bullet" id="id_bouton_cohorte"><span class="li-content"><img src="images/cohorte.png" border="0" style="vertical-align:middle"> <a href="#" onclick="voir_detail_dwh('cohorte');return false;">Cohortes</a></span></li> ?>
			<? } ?>
			<? if ($_SESSION['dwh_droit_see_detailed'.$datamart_num]=='ok') {
				if ($cohort_num_encours!='') { 
					list($titre_cohorte_encours,$nb_patient_cohorte_encours)=recup_titre_cohorte($cohort_num_encours);
					$style_display='block';
				} else {
					$style_display='none';
				}
				?>
					<li class="color-bullet" id="id_bouton_cohorte_encours" style="display:<? print $style_display; ?>"><span class="li-content"><img src="images/cohorte.png" border="0" style="vertical-align:middle"> <a href="#" onclick="voir_detail_dwh('cohorte_encours');return false;" id="id_titre_cohorte_encours"><? print get_translation('COHORT','Cohorte')." : ".$titre_cohorte_encours; ?></a>
					</span>
					<span id="id_nb_patient_cohorte_inclu" style="color: red; font-weight: bold; left: 0px; position: relative;top: -11px; display: inline;"><? print $nb_patient_cohorte_encours; ?></span>
					<span style="color: black; font-weight: bold; left: 0px; position: relative;top: -11px; display: inline;cursor:pointer;" alt="fermer" title="fermer" onclick="fermer_cohorte_encours();">x</span></li>
			<? } ?>
			<? if ($_SESSION['dwh_droit_see_stat'.$datamart_num]=='ok') { ?>
					<li class="<? print $class_menu_stat; ?> color-bullet" id="id_bouton_stat"><span class="li-content"><img src="images/graph.png" border="0" style="vertical-align:middle"> <a href="#" onclick="voir_detail_dwh('stat');affiche_onglet_stat(<? print $tmpresult_num; ?>);return false;"><? print get_translation('STATISTICAL_DATA','Données stat'); ?></a></span></li>
			<? }?>
			<? if ($_SESSION['dwh_droit_see_concept'.$datamart_num]=='ok') { ?>
					<li class="color-bullet" id="id_bouton_concepts"><span class="li-content"><img src="images/flow2.png" border="0" style="vertical-align:middle"> <a href="#" onclick="voir_detail_dwh('concepts');affiche_onglet_concepts (<? print $tmpresult_num; ?>,'','');return false;"><? print get_translation('PHENOTYPES','Phenotypes'); ?></a></span></li>
			<? }?>
			<? if ($_SESSION['dwh_droit_see_drg'.$datamart_num]=='ok') { ?>
					<li class="<? print $class_menu_pmsi; ?> color-bullet" id="id_bouton_pmsi"><span class="li-content"><img src="images/flow2.png" border="0" style="vertical-align:middle"> <a href="#" onclick="voir_detail_dwh('pmsi');affiche_onglet_pmsi (<? print $tmpresult_num; ?>,'sejour','','<? print $thesaurus_code_pmsi; ?>');return false;"><? print get_translation('HOSPITAL_DRG','PMSI'); ?></a></span></li>
			<? } ?>
			<? if ($_SESSION['dwh_droit_see_biology'.$datamart_num]=='ok') { ?>
					<li class="color-bullet" id="id_bouton_labo"><span class="li-content"><img src="images/chemistry21.png" border="0" style="vertical-align:middle"> <a href="#" onclick="voir_detail_dwh('labo');affiche_onglet_labo (<? print $tmpresult_num; ?>);return false;"><? print get_translation('BIOLOGY','Biologie'); ?></a></span></li>
			<? } ?>
			<? if ($_SESSION['dwh_droit_see_genetic'.$datamart_num]=='ok') { ?>
					<li class="color-bullet" id="id_bouton_genes"><span class="li-content"><img src="images/adn2.png" border="0" style="vertical-align:middle"> <a href="#" onclick="voir_detail_dwh('genes'); affiche_onglet_genes(<? print $tmpresult_num; ?>);return false;"><? print get_translation('GENES','Genes'); ?></a></span></li>
			<? } ?>
			<? if ($_SESSION['dwh_droit_see_map'.$datamart_num]=='ok') { ?>
					<li class="color-bullet" id="id_bouton_map"><span class="li-content"><img src="images/map.png" border="0" style="vertical-align:middle"> <a href="#" onclick="voir_detail_dwh('map');generer_map(<? print $tmpresult_num; ?>);return false;"><? print get_translation('MAP','Map'); ?></a></span></li>
			<? } ?>
			<? if ($_SESSION['dwh_droit_see_clustering'.$datamart_num]=='ok') { ?>
					<li class="color-bullet" id="id_bouton_clustering"><span class="li-content"><img src="images/clustering.png" border="0" style="vertical-align:middle"> <a href="#" onclick="voir_detail_dwh('clustering'); return false;"><? print get_translation('CLUSTERING','Clustering'); ?></a></span></li>
			<? } ?>
			<? if ($_SESSION['dwh_droit_admin_datamart0']=='ok') { ?>
					<li class="color-bullet" id="id_bouton_admin_datamart"><span class="li-content"><img src="images/datamart.png" border="0" style="vertical-align:middle"> <a href="#" onclick="voir_detail_dwh('admin_datamart');return false;"><? print get_translation('DATAMART','Datamart'); ?></a></span></li>
			<? } ?>
			<? if ($_SESSION['dwh_droit_export_data']=='ok') { ?>
			<li class="color-bullet" id="id_bouton_export_data"><span class="li-content"><img src="images/download.png" border="0" style="vertical-align:middle"> <a href="#" onclick="voir_detail_dwh('export_data');return false;"><? print get_translation('EXPORT_DATA','EXport données'); ?></a></span></li>
			<? } ?>
			<? if ($_SESSION['dwh_droit_regexp']=='ok') { ?>
			<li class="color-bullet" id="id_bouton_regexp"><span class="li-content"><img src="images/download.png" border="0" style="vertical-align:middle"> <a href="#" onclick="voir_detail_dwh('regexp');return false;"><? print get_translation('EXTRACTION','Extraction'); ?></a></span></li>
			<? } ?>
			<? if ($_SESSION['dwh_droit_extract_ecrf_on_result']=='ok') { ?>
			<li class="color-bullet" id="id_bouton_extract_ecrf_on_result"><span class="li-content"><img src="images/download.png" border="0" style="vertical-align:middle"> <a href="#" onclick="voir_detail_dwh('extract_ecrf_on_result');return false;"><? print get_translation('ECRF','ECRF'); ?></a></span></li>
			<? } ?>
			</ul>
		</div>
	<?
		$readable_query=readable_query ($xml);
		//list($nb_patient,$nb_document,$nb_patient_user,$nb_document_user)=generer_resultat($xml,$tmpresult_num);
	?>
	
	<?
		if ($_SESSION['dwh_droit_all_departments'.$datamart_num]=='' || !preg_match("/'tout'/i","$liste_document_origin_code_session"))  {
			$phrase_nb_patient_detail='';
			if ($_SESSION['dwh_droit_all_departments'.$datamart_num]=='') { 
				$phrase_nb_patient_detail.= "<strong>".get_translation('ON_YOUR_HOSPITAL_DEPARTMENTS','Sur Vos services')."</strong> <br>";
			} 
			if (!preg_match("/'tout'/i","$liste_document_origin_code_session"))  {
				$liste_document_origin_code=preg_replace("/'/","","$liste_document_origin_code_session");
				$phrase_nb_patient_detail.= "<strong>".get_translation('ON_YOUR_AUTHORIZED_DOCUMENTS_ORIGIN','Sur Vos types de document')." ($liste_document_origin_code)</strong> <br>";
			} 
			
			$phrase_nb_patient_detail.= "<span id=\"id_span_nb_patient_detail_user\"><img src=\"images/chargement_mac.gif\" width=\"15\"></span>  ".get_translation('X_OUT_OF_Y_PATIENTS','sur')." 
							<span id=\"id_span_nb_patient_detail\"><img src=\"images/chargement_mac.gif\" width=\"15\"></span> ".get_translation('PATIENTS','Patients')."<br>";
			$phrase_nb_patient_detail.= "<span id=\"id_span_nb_document_detail_user\"><img src=\"images/chargement_mac.gif\" width=\"15\"></span> ".get_translation('X_OUT_OF_Y_PATIENTS','sur')." 
							<span id=\"id_span_nb_document_detail\"><img src=\"images/chargement_mac.gif\" width=\"15\"></span> ".get_translation('DOCUMENTS','Documents')."<br>";
			$phrase_nb_patient_detail.= "<span id=\"id_span_nb_mvt_detail_user\"><img src=\"images/chargement_mac.gif\" width=\"15\"></span> ".get_translation('X_OUT_OF_Y_PATIENTS','sur')." 
							<span id=\"id_span_nb_mvt_detail\"><img src=\"images/chargement_mac.gif\" width=\"15\"></span> ".get_translation('MOVMENTS','Mouvements')."<br><br>";
			
		} else {
			$phrase_nb_patient_detail= "<strong>".get_translation('ON_THE_ENTIRE_HOSPITAL',"Sur tout l'hôpital")." :</strong> <br>";
			$phrase_nb_patient_detail= "<span id=\"id_span_nb_patient_detail\"><img src=\"images/chargement_mac.gif\" width=\"15\"></span> ".get_translation('PATIENTS','Patients')."<br>";
			$phrase_nb_patient_detail.= "<span id=\"id_span_nb_document_detail\"><img src=\"images/chargement_mac.gif\" width=\"15\"></span> ".get_translation('DOCUMENTS','Documents')."<br>";
			$phrase_nb_patient_detail.= "<span id=\"id_span_nb_mvt_detail\"><img src=\"images/chargement_mac.gif\" width=\"15\"></span> ".get_translation('MOVMENTS','Mouvements')."<br><br>";
		}
		//$phrase_nb_patient_total= "<strong>".get_translation('ON_THE_ENTIRE_HOSPITAL',"Sur tout l'hôpital")." :</strong> <br>";
		//$phrase_nb_patient_total.= "<span class=\"id_span_nb_patient_total\"></span> ".get_translation('PATIENTS','Patients')."<br>";
		//$phrase_nb_patient_total.= "<span class=\"id_span_nb_document_total\"></span> ".get_translation('DOCUMENTS','Documents')."<br><br>";
		
		
		
		 print $phrase_nb_patient_detail; 
		
		?>
		<? if ($_SESSION['dwh_droit_see_detailed'.$datamart_num]=='ok') { ?>
			<div id="id_div_dwh_resultat_detail" class="div_result">
				
				<table border="0">
					<tr>
						<td>
							<div class="requete_clair"><? print "$readable_query"; ?></div>
						</td>
					</tr>
				</table>
				<strong style="color:red;display:none">A partir de maintenant, pour alimenter une cohorte, il faut passer par le bouton ci dessous "alimenter une cohorte"<br>
				Vous pourrez ajouter ou sélectionner une cohorte existante.<br>
				Le mécanisme d'alimentation reste le même !!<br>
				Nicolas
				</strong>
				<table border="0">
					<tr>
						<td>
						
							<span id="button_id_div_alimenter_cohorte" onclick="search_engine_sub_menu('id_div_alimenter_cohorte');" onmouseover="search_engine_sub_menu_hover('id_div_alimenter_cohorte');" onmouseout="search_engine_sub_menu_out('id_div_alimenter_cohorte');" class="bouton_sauver"><img src="images/cohorte.png" style="cursor:pointer;height:14px;vertical-align:text-bottom" border="0"> <? print get_translation('COHORT_RECRUIT','Alimenter une cohorte'); ?></span>
							<? if ($datamart_temporaire=='') { ?>
								<span  id="button_id_div_sauver_requete" onclick="search_engine_sub_menu('id_div_sauver_requete');" onmouseover="search_engine_sub_menu_hover('id_div_sauver_requete');" onmouseout="search_engine_sub_menu_out('id_div_sauver_requete');"class="bouton_sauver"><img src="images/save_16.png" style="cursor:pointer;height:14px;vertical-align:text-bottom" border="0"> <? print get_translation('SAVE_SEARCH_QUERY','Sauver la requête'); ?></span>
							<? }?>
							<span id="button_id_div_rechercher_dans_resultat" onclick="window.open('<? print "moteur.php?action=rechercher_dans_resultat&tmpresult_num=$tmpresult_num&datamart_num=$datamart_num"; ?>', '_blank');" class="bouton_sauver"><img src="images/search.png" style="cursor:pointer;height:14px;vertical-align:text-bottom" border="0"> <? print get_translation('SEARCH_IN_RESULT','Rechercher sur le résultat'); ?></span>
							<span id="button_id_div_export_excel" onclick="search_engine_sub_menu('id_div_export_excel');" onmouseover="search_engine_sub_menu_hover('id_div_export_excel');" onmouseout="search_engine_sub_menu_out('id_div_export_excel');"class="bouton_sauver"><img src="images/excel_noir.png" style="cursor:pointer;height:14px;vertical-align:text-bottom;" title="Export Excel" alt="Export Excel" border="0"> <? print get_translation('EXPORT_PATIENTS_TO_EXCEL','Exporter les patients sur Excel'); ?></span>
							<span id="button_id_div_filtre_resultat_texte" onclick="search_engine_sub_menu('id_div_filtre_resultat_texte');" onmouseover="search_engine_sub_menu_hover('id_div_filtre_resultat_texte');" onmouseout="search_engine_sub_menu_out('id_div_filtre_resultat_texte');"class="bouton_sauver"><img src="images/funnel.png" style="cursor:pointer;height:14px;vertical-align:text-bottom" border="0"> <? print get_translation('FILTER_RESULT_UNDERNEATH','Fitlrer le résultat ci dessous'); ?></span>
							<? if ($datamart_temporaire=='') { ?>
								<span id="button_id_div_partager_requete" onclick="search_engine_sub_menu('id_div_partager_requete');" onmouseover="search_engine_sub_menu_hover('id_div_partager_requete');" onmouseout="search_engine_sub_menu_out('id_div_partager_requete');"class="bouton_sauver"><img src="images/share_fleche.png" style="cursor:pointer;height:14px;vertical-align:text-bottom" border="0"> <? print get_translation('SHARE_SEARCH_QUERY','Partager la requête'); ?></span>
							<? }?>
						</td>
					</tr>
				</table>
				<div id="id_div_sauver_requete" style="width:550px;display:none;font-size:13px">
					<h3><? print get_translation('SAVE_SEARCH_QUERY','Sauver la requête'); ?></h3>
					<? print get_translation('TITLE','Titre'); ?> : <input type="text" size="40" id="id_titre_requete_sauver" class="input_texte"><br>
					<? print get_translation('EXECUTE_QUERY_AUTOMATICALLY','Executer automatiquement cette requête'); ?>: <input type="checkbox" id="id_crontab_requete" name="crontab_query" value="1"> <br>
					<? print get_translation('PERIODICITY','Périodicité'); ?>: 
						<select id="id_crontab_periode" name="crontab_periode">
							<option value=''></option>
							<option value='month'><? print get_translation('EVERY_MONTH','Tous les mois'); ?></option>
							<option value='week'><? print get_translation('EVERY_WEEK','Toutes les dimanches'); ?></option>
							<option value='day'><? print get_translation('EVERY_MORNING','Tous les matins'); ?></option>
						</select>
					<br> <input type=button value='<? print get_translation('SAVE','sauver'); ?>' onclick="sauver_requete_en_cours();">
					<input type="hidden" id="query_num_sauver" value="<? print $query_num; ?>">
					<br>
					<br>
				</div>
				<div id="id_div_filtre_resultat_texte" style="width:550px;display:none;font-size:13px">
					<h3><? print get_translation('FILTER_RESULT','Filtrer le résultat'); ?></h3>
					<? print get_translation('FULLTEXT_SEARCH_QUERY','Requête plein texte'); ?> : <input type="text" size="40" class="input_texte" id="id_filtre_resultat_texte" onkeyup="if (this.value=='') {filtrer_resultat();} if(event.keyCode==13) {filtrer_resultat();}"><input type="button" value="ok" onclick="filtrer_resultat();"><input type="button" value="annuler" onclick="jQuery('#id_filtre_resultat_texte').val('');filtrer_resultat();">
				</div>
				<div id="id_div_export_excel" style="width:550px;display:none;font-size:13px">
					<h3><? print get_translation('EXPORT_EXCEL','Export Excel'); ?></h3>
					<strong><a href="export_excel.php?tmpresult_num=<? print "$tmpresult_num"; ?>&option=patient"><? print get_translation('PATIENT_LIST','La liste des patients'); ?></a></Strong><br>
					<strong><a href="export_excel.php?tmpresult_num=<? print "$tmpresult_num"; ?>&option=patient_document"><? print get_translation('PATIENT_LIST_AND_DOCUMENT','La liste des patients et le titre des comptes rendus'); ?></a></Strong><br>
					<strong><a href="export_excel.php?tmpresult_num=<? print "$tmpresult_num"; ?>&option=encounter_result"><? print get_translation('ENCOUNTER_LIST_RESULT','La liste des séjours associés à des documents trouvés'); ?></a></strong><br>
					<strong><a href="export_excel.php?tmpresult_num=<? print "$tmpresult_num"; ?>&option=encounter_all"><? print get_translation('ENCOUNTER_LIST_ALL','La liste de tous les séjours / consultations / HDJ des patients trouvés'); ?></a></strong><br>
				</div>
				<div id="id_div_partager_requete" style="width:550px;display:none;font-size:13px">
					<h3><? print get_translation('SHARE_SEARCH_QUERY','Partager votre requête'); ?></h3>
					<? print get_translation('NAME_YOUR_SEARCH_QUERY','Nommer votre requete'); ?> : <input type="text" size="40" class="input_texte" id="id_notification_text" ><br>
					<? print get_translation('SELECT_PERSONS_TO_SHARE_WITH','Sélectionner les personnes auprès de qui la partager'); ?> : 
					<select class="chosen-select" multiple id="id_select_num_user_partager" data-placeholder="<? print get_translation('JS_SELECT_USERS','Choisissez des utilisateurs'); ?>">
					<option value=''></option>
					<?
						$sel_var1=oci_parse($dbh,"select  user_num,lastname,firstname from dwh_user where user_num!=$user_num_session order by lastname,firstname ");
						oci_execute($sel_var1);
						while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
							$user_num=$r['USER_NUM'];
							$lastname=$r['LASTNAME'];
							$firstname=$r['FIRSTNAME'];
							print "<option  value=\"$user_num\">$lastname $firstname</option>";
						}
					?>
					</select><br>
					<input type=button value='<? print get_translation('SHARE','Partager'); ?>' onclick="partager_requete_en_cours('<? print $query_num; ?>');">
				</div>
				<div id="id_span_action_partager_requete_en_cours"></div>
				
				<div id="id_div_alimenter_cohorte" style="width:550px;display:none;font-size:13px">
					<? include_once "javascript_cohorte.php"; ?>
					<h3 onclick="plier_deplier('id_div_liste_creer_cohorte');plier('id_div_list_choose_cohort');" class="link"><span id="plus_id_div_liste_creer_cohorte">+</span> <? print get_translation('ADD_PATIENT_IN_NEW_COHORT','Ajouter des patients dans un nouvelle cohorte'); ?></h3>
					<div id="id_div_liste_creer_cohorte" style="display:none;">
						<table border="0">
							<tr><td><? print get_translation('TITLE','Titre'); ?> : </td><td><input type="text" size="50" id="id_ajouter_titre_cohorte" class="form"></td></tr>
							<tr><td style="vertical-align:top;"><? print get_translation('SHARE_WITH','Partager avec'); ?> : </td><td>
								<select id="id_ajouter_select_user_multiple" multiple size="5" class="form chosen-select" data-placeholder="<? print get_translation('JS_SELECT_USERS','Choisissez des utilisateurs'); ?>"><option value=''></option>
								<?
									$sel_var1=oci_parse($dbh,"select  user_num,lastname,firstname from dwh_user order by lastname,firstname ");
									oci_execute($sel_var1);
									while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
										$user_num=$r['USER_NUM'];
										$lastname=$r['LASTNAME'];
										$firstname=$r['FIRSTNAME'];
										print "<option  value=\"$user_num\" id=\"id_ajouter_select_num_user_multiple_$user_num\">$lastname $firstname</option>";
									}
								?>
								</select>
							</td></tr>
							<tr><td style="vertical-align:top;"><? print get_translation('DESCRIPTION','Description'); ?> : </td><td>
								<textarea id="id_ajouter_description_cohort" cols="50" rows="6" class="form"></textarea>
							</td></tr>
							
						</table>
						<input type="button" onclick="ajouter_cohorte();" class="form" value="ajouter">
						<div id="id_div_resultat_ajouter_cohorte"></div>
					</div>
			
					<h3 onclick="plier('id_div_liste_creer_cohorte');plier_deplier('id_div_list_choose_cohort');" class="link"><span id="plus_id_div_list_choose_cohort">+</span> <? print get_translation('ADD_PATIENT_IN_EXISTING_COHORT',"Ajouter des patients dans une cohorte existante"); ?></h3>
					<div id="id_div_list_choose_cohort" style="display:none;">
						<select  id="id_select_cohorte_pour_alimentation" class="chosen-select" onchange="select_cohorte(this.value);"  data-placeholder="<? print get_translation('JS_SELECT_COHORT','Choisissez une cohorte'); ?>" >
							<? display_user_cohorts_option ($user_num_session,'id_select_cohorte_pour_alimentation'); ?>
						</select>
					</div>
				</div>
				
				<div class="icone_cohorte" style="display: none;">
					<h2><span style="cursor: default;" id="id_libelle_cohorte_encours"><? print get_translation('OPENED_COHORT','Cohorte ouverte'); ?> : <? print $titre_cohorte_encours; ?></span>&nbsp;<sup style="color: red; font-weight: bold; left: 0px;cursor:pointer;" alt="fermer" title="fermer" onclick="fermer_cohorte_encours();">x</sup></h2>
					<a href="#" onclick="tout_inclure_exclure(1,'inclus');return false;"><img src="images/inclure_patient_cohorte.png" border="0" align="absmiddle" width="20px"><? print get_translation('INCLUDE_UNDERNEATH_PATIENTS_IN_COHORT','inclure tous les patients ci-dessous dans la cohorte'); ?></a><br>
					<a href="#" onclick="tout_inclure_exclure(0,'exclus');return false;"><img src="images/noninclure_patient_cohorte.png" border="0" align="absmiddle" width="20px"><? print get_translation('EXCLUDE_PATIENTS_UNDERNEATH_FROM_COHORT','Exclure tous les patients ci-dessous de la cohorte'); ?></a><br>
					<a href="#" onclick="tout_inclure_exclure(2,'doutes');return false;"><img src="images/doute_patient_cohorte.png" border="0" align="absmiddle" width="20px"><? print get_translation('DOUBTING_ALL_PATIENTS_UNDERNEATH','En doute tous les patients ci-dessous'); ?></a><br>
					<a href="#" onclick="tout_inclure_exclure(3,'importés');return false;"><img src="images/download.png" border="0" align="absmiddle" width="20px"><? print get_translation('IMPORT_ALL_UNDERNEATH_PATIENTS_INTO_COHORT','Importer tous les patients ci-dessous dans la cohorte'); ?></a><br>
					<a href="#" onclick="exclure_cohorte_resultat();return false;"><img src="images/pas_voir_tout.png" border="0" align="absmiddle" width="20px" id="id_img_exclure_cohorte_resultat"><span id="id_texte_exclure_cohorte_resultat"><? print get_translation('DO_NOT_DISPLAY_PATIENTS_INCLUDED_EXCLUDED','Ne pas afficher les patients déjà inclus ou exclus'); ?></span></a>
					<br>
				</div>
				<br>
				<div id="id_div_list_div_affichage"></div>
			<?
				
				print "<table class=\"tableau_resultat\" id=\"id_tableau_resultat\">$lignes</table>";
				print "<input type=\"hidden\" value=\"0\" id=\"id_num_last_ligne\">";
				print "<input type=\"hidden\" value=\"$full_text_query\" id=\"id_full_text_query\">";
				print "<input type=\"hidden\" value=\"$modulo_ligne_ajoute\" id=\"id_modulo_ligne_ajoute\">";
				print "<input type=\"hidden\" value=\"$nb_patient_user\" id=\"id_total_ligne\">";
				print "<input type=\"hidden\" value=\"\" id=\"id_exclure_cohorte_resultat\">";
				
				print "<br><span id=\"id_span_afficher_suite\" style=\"display:none;\"><a href=\"#\" onclick=\"afficher_suite();return false;\" class=\"lien_afficher_suite\">".get_translation('DISPLAY_NEXT_DOCUMENTS','Afficher la suite')."</a></span>";
			?>
				<span id="id_span_chargement" style="display: none;"><img src="images/chargement_mac.gif"> Calcul en cours</span>
			</div>
		<? } ?>
		
		<? if ($_SESSION['dwh_droit_see_detailed'.$datamart_num]=='ok') { ?>
			<div id="id_div_dwh_cohorte_encours" style="display:none;" class="div_result">
				<? //print $phrase_nb_patient_total; ?>
				<? include "include_cohorte_encours.php"; ?>
			</div>
		<? } ?>
		<? if ($_SESSION['dwh_droit_see_stat'.$datamart_num]=='ok') { ?>
			<div id="id_div_dwh_stat" style="display:<? print $display_stat; ?>;" class="div_result">
				<? //print $phrase_nb_patient_total; ?>
				<? include "include_stat.php"; ?>
			</div>
		<? } ?>
		<? if ($_SESSION['dwh_droit_see_concept'.$datamart_num]=='ok') { ?>
			<div id="id_div_dwh_concepts" style="display:none;" class="div_result">
				<? //print $phrase_nb_patient_total; ?>
				<? include "include_concepts.php"; ?>
			</div>
		<? } ?>
		<? if ($_SESSION['dwh_droit_see_drg'.$datamart_num]=='ok') { ?>
			<div id="id_div_dwh_pmsi" style="display:<? print $display_pmsi; ?>;" class="div_result">
				<? //print $phrase_nb_patient_total; ?>
				<? include "include_pmsi.php"; ?>
			</div>
		<? } ?>
		<? if ($_SESSION['dwh_droit_see_biology'.$datamart_num]=='ok') { ?>
			<div id="id_div_dwh_labo" style="display:none;" class="div_result">
				<? //print $phrase_nb_patient_total; ?>
				<? include "include_labo.php"; ?>
			</div>
		<? } ?>
		<? if ($_SESSION['dwh_droit_see_genetic'.$datamart_num]=='ok') { ?>
			<div id="id_div_dwh_genes" style="display:none;" class="div_result">
				<? //print $phrase_nb_patient_total; ?>
				<? include "include_genes.php"; ?>
			</div>
		<? } ?>
		<? if ($_SESSION['dwh_droit_see_clustering'.$datamart_num]=='ok') { ?>
			<div id="id_div_dwh_clustering" style="display:none;" class="div_result">
				<? //print $phrase_nb_patient_total; ?>
				<? include "include_clustering.php"; ?>
			</div>
		<? } ?>
		<? if ($_SESSION['dwh_droit_see_map'.$datamart_num]=='ok') { ?>
			<div id="id_div_dwh_map" style="display:none;" class="div_result">
				<? //print $phrase_nb_patient_total; ?>
				<? include "include_map.php"; ?>
			</div>
		<? } ?>
		<? if ($_SESSION['dwh_droit_admin_datamart0']=='ok') { ?>
			<div id="id_div_dwh_admin_datamart" style="display:none;" class="div_result">
				<? //print $phrase_nb_patient_total; ?>
				<? include "include_datamart.php"; ?>
			</div>
		<? } ?>
		
		<? if ($_SESSION['dwh_droit_export_data']=='ok') { ?>
			<div id="id_div_dwh_export_data" style="display:none;" class="div_result">
				<? //print $phrase_nb_patient_total; ?>
				<? include "include_export_data.php"; ?>
			</div>
		<? } ?>
		
		<? if ($_SESSION['dwh_droit_regexp']=='ok') { ?>
			<div id="id_div_dwh_regexp" style="display:none;" class="div_result">
				<? //print $phrase_nb_patient_total; ?>
				<? include "include_regexp.php"; ?>
			</div>
		<? } ?>
		
		<? if ($_SESSION['dwh_droit_extract_ecrf_on_result']=='ok') { ?>
			<div id="id_div_dwh_extract_ecrf_on_result" style="display:none;" class="div_result">
				<? //print $phrase_nb_patient_total; ?>
				<? include "include_extract_ecrf_on_result.php"; ?>
			</div>
		<? } ?>
	<? } ?>

	</td>
</tr>
</table>

<script language="javascript">


$(document).ready(function(){
	$.fn.dataTable.moment( 'DD/MM/YYYY HH:mm' );
 	// list_query_history (<? print $datamart_num; ?>) ;
	get_json_query_history(<? print $datamart_num; ?>) ;
	
 	jQuery('#id_tableau_liste_requete_sauve').dataTable( { 
	 		"scrollY": "200px",
			  "scrollCollapse": true,
			  "paging": false,
			"order": [[ 0, "asc" ]],
			   "bInfo" : false,
			   "bDestroy" : true
	} );
	
  
  
	if (document.getElementById('id_input_filtre_texte_1')) {
		document.getElementById('id_input_filtre_texte_1').focus();
	}
	<? print $afficher_result; ?>
	if (clic_on_tab=='') { // affichage par défaut du tab, sauf si l'utilisateur a deja clique sur un tab
	<?
		print $initiate_result;
	?>
	}
	<?
	if (($_GET['action']=='preremplir_requete' || $_GET['option']=='preremplir_requete') && $_GET['query_num']!='') {
		$query_num=$_GET['query_num'];
		print "charger_moteur_recherche($query_num);";
	}
	?>
} );


</script>

<? save_log_page($user_num_session,'engine'); ?>
<? include "foot.php"; ?>