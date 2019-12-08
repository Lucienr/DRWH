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
include "fonctions_concepts.php";
include "fonctions_patient.php";
include "fonctions_ecrf.php";
?>
<?
	$patient_num=$_GET['patient_num'];
	$tab_patient=get_patient ($patient_num);
	$hospital_patient_id=$tab_patient['HOSPITAL_PATIENT_ID'];
	$patient=afficher_patient ($patient_num,'patient','','','dossier');
	if ($patient=='') {
		$patient_num='';
	}
	if ($_SESSION['dwh_droit_modify_patient']=='ok') {
		print "<h1 id=\"id_h1_patient\"  style=\"display:block;\" onclick=\"plier_deplier('id_h1_patient_modifier');\">$patient</h1>";
		print "<h1 id=\"id_h1_patient_modifier\" style=\"display:none;\"><input type=text id=id_input_hospital_patient_id value='$hospital_patient_id'> <input type=button value= ".get_translation('MODIFY','modifier')." onclick=\"modify_hospital_patient_id('$patient_num','$hospital_patient_id');\"></h1>";
		print "<div id=\"id_div_modifier_patient\"></div>";
	} else {
		print "<h1>$patient</h1>";
	}
	$cohort_num_patient=$_GET['cohort_num_patient'];
	$ecrf_num_open=$_GET['ecrf_num_open'];
?>
	
<? if ($patient_num=='') { ?>
<? print "<h1>".get_translation('YOU_CANNOT_SEE_THIS_PATIENT',"Vous n'êtes pas autorisé à voir ce patient")."</h1>";
?>

<? } else { ?>
	
	<script type="text/javascript" src="javascript_patient.js?v=<? print $date_today_unique; ?>"></script>
	<script type="text/javascript" src="javascript_ecrf_patient.js?v=<? print $date_today_unique; ?>"></script>
	<script language="javascript">
		<? 
		if ($_SESSION['dwh_droit_modify_patient']=='ok') { 
		?>
			function modify_hospital_patient_id (patient_num,hospital_patient_id) {
				hospital_patient_id_new=document.getElementById('id_input_hospital_patient_id').value;
				jQuery.ajax({
					type:"POST",
					url:"ajax.php",
					async:true,
					encoding: 'latin1',
					data:{ action:'modify_hospital_patient_id',patient_num:patient_num,hospital_patient_id_new:hospital_patient_id_new,hospital_patient_id:hospital_patient_id},
					beforeSend: function(requester){
						jQuery("#id_div_modifier_patient").html("Modification en cours <img src='images/chargement_mac.gif'>"); 
					},
					success: function(requester){
						var contenu=requester;
						if (contenu=='deconnexion') {
							afficher_connexion("modify_hospital_patient_id ('"+patient_num+"','"+hospital_patient_id+"')");
						} else {
							jQuery("#id_h1_patient").html(contenu); 
							jQuery("#id_div_modifier_patient").html("modification terminée"); 
							document.getElementById('id_h1_patient_modifier').style.display='none';
							document.getElementById('id_input_hospital_patient_id').value=hospital_patient_id_new;
						}
						
					}
				});
			}
		<? 
		}
		?>
		
		jQuery(document).ready(function() {
			select_history_query_patient ('<? print $patient_num; ?>');
			process_parcours ('<? print $patient_num; ?>');
			process_pmsi_patient  ('<? print $patient_num; ?>');
		})
	</script>
			
	<style>
		li.cohorte_1 {
			background-image: url("images/inclure_patient_cohorte.png");
			background-repeat: no-repeat;
			padding-left: 30px;
			font-size: 13px;
			list-style: none;
			margin: 0;    
			height: 24px;
			padding-top: 3px;
		}
		li.cohorte_0 {
			background-image: url("images/noninclure_patient_cohorte.png");
			background-repeat: no-repeat;
			padding-left: 30px;
			font-size: 13px;
			list-style: none;
			margin: 0;    
			height: 24px;
			padding-top: 3px;
		}
		li.cohorte_2 {
			background-image: url("images/doute_patient_cohorte.png");
			background-repeat: no-repeat;
			padding-left: 30px;
			list-style: none;
			margin: 0;
			font-size: 13px;    
			height: 24px;
			padding-top: 3px;
		}
		li.cohorte_3 {
			background-image: url("images/doute_patient_cohorte.png");
			background-repeat: no-repeat;
			padding-left: 30px;
			list-style: none;
			margin: 0;
			font-size: 13px;    
			height: 24px;
			padding-top: 3px;
		}
		table.dataTable thead th, table.dataTable thead td {
		    padding: 1px;
		}
		
		#id_tableau_bilan_biologie {
		    margin-top: -2px;
		}
		
		
		table.dataTable tbody th, table.dataTable tbody td {
		    padding: 2px 2px;
		}
		
		
		#tabs {
			pointer-events: none;
			opacity: 0.5;
		}
		
	</style>
	<?
	
	// if patient vizualized from a cohort //
	if ($cohort_num_patient!='') {
		$cohort=get_cohort($cohort_num_patient,'');
		$title_cohort=$cohort['TITLE_COHORT'];
		print "<input type=\"hidden\" id=\"id_cohort_num_patient\" value=\"$cohort_num_patient\">";
		if ($cohort_num_patient!='') {
			$next_patient_num=get_a_patient_in_a_cohort ($cohort_num_patient,$patient_num,'next');
		}
		print "<input type=\"hidden\" id=\"id_next_patient_num\" value=\"$next_patient_num\">";
		if ($next_patient_num!='') {
			print "<a href=\"patient.php?patient_num=$next_patient_num&cohort_num_patient=$cohort_num_patient\" class=\"link\"> Direct access to the next patient of the cohort $title_cohort</a>";
		} else {
			$first_patient_num=get_a_patient_in_a_cohort ($cohort_num_patient,'','first');
			print "<a href=\"patient.php?patient_num=$first_patient_num&cohort_num_patient=$cohort_num_patient\" class=\"link\"> No more patient in the cohort $title_cohort, back to the first one</a>";
		}
	 }
	 
	 $resultat_famille=famille_patient_bis ($patient_num); 
	?>
	
	<a name="ancre_entete"> </a>
	<div id="tabs" style="width:100%">
		<ul id="tab-links">
		<? if ($_SESSION['dwh_droit_patient_documents']=='ok') {  ?>
			<li class="current color-bullet" id="id_bouton_documents"><span class="li-content"><a href="#" onclick="voir_patient_onglet('documents',<? print $patient_num; ?>);return false;"><? print get_translation('DOCUMENTS','Documents'); ?></a></span></li>
		<? } ?>
		<? if ($_SESSION['dwh_droit_patient_labo']=='ok') {  ?>
			<li class="color-bullet" id="id_bouton_biologie"><span class="li-content"><a href="#" onclick="voir_patient_onglet('biologie',<? print $patient_num; ?>);return false;"><? print get_translation('BIOLOGY','Biologie'); ?></a></span></li>
		<? } ?>
		
		<? if ($_SESSION['dwh_droit_patient_family']=='ok') {  ?>
			<? if ($resultat_famille!='') { ?>
				<li class="color-bullet" id="id_bouton_famille"><span class="li-content"><a href="#" onclick="voir_patient_onglet('famille',<? print $patient_num; ?>);return false;"><? print get_translation('FAMILY','Famille'); ?></a></span></li>
			<? } ?>
 		<? } ?>
 		
		<? if ($_SESSION['dwh_droit_patient_timeline']=='ok') {  ?>
 			<li class="color-bullet" id="id_bouton_timeline"><span class="li-content"><a href="#" onclick="voir_patient_onglet('timeline',<? print $patient_num; ?>);return false;"><? print get_translation('TIMELINE','TimeLine'); ?></a></span></li>
		<? } ?>
		<? if ($_SESSION['dwh_droit_patient_carepath']=='ok') {  ?>
			<li class="color-bullet" id="id_bouton_parcours"><span class="li-content"><a href="#" onclick="voir_patient_onglet('parcours',<? print $patient_num; ?>);return false;"><? print get_translation('JOURNEY','Parcours'); ?></a></span></li>
		<? } ?>
		<? if ($_SESSION['dwh_droit_patient_pmsi']=='ok') {  ?>
			<li class="color-bullet" id="id_bouton_pmsi"><span class="li-content"><a href="#" onclick="voir_patient_onglet('pmsi',<? print $patient_num; ?>);return false;"><? print get_translation('PMSI','PMSI'); ?></a></span></li>
		<? } ?>
		<? if ($_SESSION['dwh_droit_patient_cohort']=='ok') {  ?>
			<li class="color-bullet" id="id_bouton_cohorte"><span class="li-content"><a href="#" onclick="voir_patient_onglet('cohorte',<? print $patient_num; ?>);return false;"><? print get_translation('COHORT','Cohorte'); ?></a></span></li>
		<? } ?>
		<? if ($_SESSION['dwh_droit_patient_concept']=='ok') {  ?>
			<li class="color-bullet" id="id_bouton_concepts"><span class="li-content"><a href="#" onclick="voir_patient_onglet('concepts',<? print $patient_num; ?>);return false;"><? print get_translation('CONCEPTS','Concepts'); ?></a></span></li>
		<? } ?>
		<? if ($_SESSION['dwh_droit_patient_similarity']=='ok') {  ?>
			<li class="color-bullet" id="id_bouton_similarite_patient"><span class="li-content"><a href="#" onclick="voir_patient_onglet('similarite_patient',<? print $patient_num; ?>);return false;"><? print get_translation('SIMILARITY','Similarité'); ?></a></span></li>
		<? } ?>
		<? if ($_SESSION['dwh_droit_patient_ecrf']=='ok') {  ?>
			<li class="color-bullet" id="id_bouton_ecrf_patient"><span class="li-content"><a href="#" onclick="voir_patient_onglet('ecrf_patient',<? print $patient_num; ?>);return false;"><? print get_translation('ECRF','eCRF'); ?></a></span></li>
		<? } ?>
		</ul>
	</div>
	<br>
	<table width="100%" id="id_tableau_patient">
		<tr>
		<td width="100%">
			<div id="id_div_patient_documents" class="div_result" style="display:inline;" >
				<form autocomplete="off">
				<input name="input_filtre_patient_texte" id="id_input_filtre_patient_text" class="filtre_texte" type="text" value="" size="45" onkeypress="if(event.keyCode==13) {filtre_patient_texte('<? print $patient_num; ?>');return false;}" onkeyup="if(this.value=='') {filtre_patient_texte('<? print "$patient_num"; ?>');}"><input class="form_submit" type="button" value="<? print get_translation('SEARCH','RECHERCHER'); ?>" onclick="filtre_patient_texte('<? print $patient_num; ?>');">
				</form>
				<br>
				<select id="id_select_history_query_patient" class="form chosen-select" onchange="chose_history_query_patient('<? print $patient_num; ?>');"  data-placeholder="<? print get_translation('QUERY_HISTORY','Historique des requêtes'); ?>" ></select>
				<br>
				<table border="0" width="100%">
					<tr>
						<td style="vertical-align:top" width="500px">
							<div id="id_div_tableau_document" style="height:500px;overflow-y:auto;"></div>
						</td>
						<td style="vertical-align:top">
							<div id="id_div_voir_document" class="afficher_document_patient">
							
							</div>
						</td>
					</tr>
				</table>
			</div>
			
			<div id="id_div_patient_biologie" class="div_result" style="display:none;" >
				<span class="bouton_bio" id="id_bouton_biologie_cr"><a href="#" onclick="voir_patient_onglet_biologie('biologie_cr','<? print $patient_num; ?>');return false;"><? print get_translation('MEDICAL_REPORTS','Comptes Rendus'); ?></a></span> <span class="bouton_bio">-</span> 
				<span class="bouton_bio" id="id_bouton_biologie_tableau"><a href="#" onclick="voir_patient_onglet_biologie('biologie_tableau','<? print $patient_num; ?>');return false;"><? print get_translation('TABLE','Tableau'); ?></a>
				<!--</span> <span class="bouton_bio">-</span> 
				<span class="bouton_bio" id="id_bouton_biologie_code"><a href="#" onclick="voir_patient_onglet_biologie('biologie_code');return false;"><? print get_translation('CURVES','Courbes'); ?></a></span>-->
				<br>
				<br>
				<div id="id_div_patient_biologie_cr" class="div_result_bio" style="display:block;" >
					<input id="id_input_filtre_patient_text_biology" class="filtre_texte" type="text" value="" size="45" onkeypress="if(event.keyCode==13) {filtre_patient_texte_biologie('<? print $patient_num; ?>');}" onkeyup="if(this.value=='') {filtre_patient_texte_biologie('<? print "$patient_num"; ?>');}"><input class="form_submit" type="button" value="<? print get_translation('SEARCH','RECHERCHER'); ?>" onclick="filtre_patient_texte_biologie('<? print $patient_num; ?>');">
					<table border="0" width="100%">
						<tr>
							<td style="vertical-align:top" width="500px">
								<div id="id_div_list_document_biologie" style="height:500px;overflow-y:auto;"></div>
							</td>
							<td style="vertical-align:top">
								<div id="id_div_voir_biologie"></div>
							</td>
						</tr>
					</table>
				</div>
				
				<div id="id_div_patient_biologie_tableau" class="div_result_bio" style="display:none;" ></div>
				
				<div id="id_div_patient_biologie_code" class="div_result_bio" style="display:none;" >
					
					<table border="0" width="100%">
						<tr>
							<td style="vertical-align:top" width="500px">
								<div id="id_div_tableau_biologie_code" style="height:500px;overflow-y:auto;">
									<?
									//affiche_liste_code_biologie($patient_num);
									?>
								</div>
							</td>
							<td style="vertical-align:top">
								<div id="id_div_voir_biologie_code">
								</div>
							</td>
						</tr>
					</table>
				</div>
			</div>
			
			<div id="id_div_patient_timeline" class="div_result" style="display:none;" >
				<iframe id="iframe_lignevie" height="600px" width="600px" name="lignevie" src="javascript:var x;"></iframe>
				<br>
			</div>
			
			<div id="id_div_patient_famille" class="div_result" style="display:none;" >
				<h2>Famille</h2>
				<div style="width:800px">
					<? print $resultat_famille; ?>
				</div>
			</div>
			
			<div id="id_div_patient_parcours" class="div_result" style="display:none;" >
				<h2><? print get_translation('JOURNEY','Parcours'); ?></h2>
				<div style="width:800px" id="id_div_process_parcours_patient">
					<? //parcours_patient ($patient_num); ?>
				</div>
			</div>
			
			
			<div id="id_div_patient_pmsi" class="div_result" style="display:none;" >
				<div style="width:100%" id="id_div_process_pmsi_patient">
					
				</div>
			</div>
			
			<div id="id_div_patient_cohorte" class="div_result" style="display:none;" >
				<h2><? print get_translation('COHORTS_OF_PATIENT','Cohortes de ce patient'); ?></h2>
				<div id="id_div_liste_cohortes_patient">
					<? lister_cohorte_un_patient($patient_num); ?>
				</div>
				
				<h2><? print get_translation('INCLUDE_EXCLUDE_PATIENT_FROM_COHORT',"Inclure / Exclure ce patient d'une cohorte"); ?></h2>
				
				<? print get_translation('SELECT_A_COHORT','Sélectionner une cohorte'); ?> : <select id="id_select_ajouter_patient_cohorte" class="form chosen-select">
				<? display_user_cohorts_option_addright($user_num_session); ?>
				</select>
				&nbsp;&nbsp;&nbsp;
				<a onclick="inclure_patient(1,'<? print $patient_num; ?>');return false;" href="#">
					<img width="20px" border="0" align="absmiddle" src="images/inclure_patient_cohorte.png" alt="<? print get_translation('INCLUDE_PATIENT','Inclure le patient'); ?>" title="<? print get_translation('INCLUDE_PATIENT','Inclure le patient'); ?>">
				</a>&nbsp;&nbsp;&nbsp;
				<a onclick="inclure_patient(0,'<? print $patient_num; ?>');return false;" href="#">
					<img width="20px" border="0" align="absmiddle" src="images/noninclure_patient_cohorte.png" alt="<? print get_translation('EXCLUDE_PATIENT','Exclure le patient'); ?>" title="<? print get_translation('EXCLUDE_PATIENT','Exclure le patient'); ?>">
				</a>&nbsp;&nbsp;&nbsp;
				<a onclick="inclure_patient(2,'<? print $patient_num; ?>');return false;" href="#">
					<img width="20px" border="0" align="absmiddle" src="images/doute_patient_cohorte.png" alt="<? print get_translation('PUT_PATIENT_IN_STANDBY','Mettre en attente le patient'); ?>" title="<? print get_translation('PUT_PATIENT_IN_STANDBY','Mettre en attente le patient'); ?>">
				</a>
			</div>
			
			<div id="id_div_patient_concepts" class="div_result" style="display:none;" >
			</div>
			
			
			<div id="id_div_patient_similarite_patient" class="div_result" style="display:none;" >
				
				<input class="bouton_sauver" type="button" onclick="calculer_similarite_patient('<? print $patient_num; ?>');" value="<? print get_translation('FIND_SIMILAR_PATIENTS','Trouver les patients similaires'); ?>"> 
				<input class="bouton_sauver" type="button" onclick="plier_deplier('id_div_patient_similarite_patient_options');" value="<? print get_translation('OPTIONS','Options'); ?>"><br>
				<div id="id_div_patient_similarite_patient_options" style="display:none;" >
					<? $process_num=get_uniqid(); ?>
					<input type="hidden" id="id_process_num" value="<? print $process_num; ?>">
					<? print get_translation('LIMIT_TO_PATIENTS_WITH_TERMS_IN_DOCUMENTS','Limiter aux patients avec ces termes dans les comptes rendus');?>:  <input type="text" size="50" id="id_textarea_similarite_patient_requete"><input type="button" onclick="precalcul_nb_patient_similarite_patient('<? print $patient_num; ?>');" value="<? print get_translation('PRECOMPUTE','Pré-calculer'); ?>"><? print get_translation('COUNT_PATIENT_NUMBER_SHORT','Nb de patients'); ?> : <span id="id_span_precalcul_nb_patient_similarite_patient"></span><br>
					<? print get_translation('EXCLUDE_PATIENT_WITH_TERMS_IN_CASE_REPORT','Exclure les patients avec ces termes dans les comptes rendus');?> :  <input type="text" size="50" id="id_input_requete_exclure"><br>
					<? print get_translation('COUNT_MINIMUM_CONCEPTS_PER_PATIENT','Nb minimum de concepts par patient'); ?> : <input type="text" size="3" id="id_input_limite_count_concept_par_patient_num" value="10"><br>
					<? print get_translation('LIMIT_NUMBER_SIMILAR_PATIENTS','Limite nombre de patients similaires'); ?> : <input type="text" size="3" id="id_input_nbpatient_limite" value="20"><br>
					<? print get_translation('EXCLUDE_RESULT_COHORT','Exclure cette cohorte des résultats'); ?> : <select id=id_cohort_num_exclure class="chosen-select">
					<?	
						display_user_cohorts_option ($user_num_session,'id_option_similarite_cohorte_') 
					?>
					</select><br>
				</div>
			
				<div id="id_div_patient_similarite_patient_resultat" style="display:block;" >
				</div>
				<div id="id_affiche_intersect" style="width:300px;height:500px;border:1px black solid;display:none;overflow-y: auto;position:absolute;background-color:white;"></div>
			</div>
		
			<div id="id_div_patient_ecrf_patient" class="div_result" style="display:none;" >
				<table border="0" width="100%">
				<tr><td style="vertical-align:top;width:50%">
					<div id="id_div_patient_ecrf_extract">
					</div>
				</td>
				<td style="vertical-align:top">document<br>
					<input name="input_filtre_patient_texte" id="id_input_ecrf_filtre_patient_text" class="filtre_texte" type="text" value="" size="45" onkeypress="if(event.keyCode==13) {filtre_patient_text_ecrf('<? print $patient_num; ?>');return false;}" onkeyup="if(this.value=='') {filtre_patient_text_ecrf('<? print "$patient_num"; ?>');}"> <input class="form_submit" type="button" value="<? print get_translation('SEARCH','RECHERCHER'); ?>" onclick="filtre_patient_text_ecrf('<? print $patient_num; ?>');">

					<div id="id_afficher_list_document_ecrf" style="overflow-y:auto; max-height:350px;">
					</div>
					<div id="id_afficher_document_ecrf" style="position:relative;background-color:white">
					</div>
				</td>
				</tr>
				</table>
			</div>
		</td></tr>
	</table>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<script language=javascript>
		
	jQuery(document).ready(function() { 
		$('#tabs').css('pointer-events','auto');
		$('#tabs').css('opacity','1');
		open_ecrf('<? print $patient_num; ?>','<? print $ecrf_num_open; ?>');
		filtre_patient_texte('<? print $patient_num; ?>');
	})
	
</script>
	<? } ?>
<style>
	.dataTables_scrollHead {position:static;}
</style>
<? save_log_page($user_num_session,'patient'); ?>
<? include "foot.php"; ?>