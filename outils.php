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
<style>
	ul.ui-widget-content {
		width:300px;
	}
</style>

<script charset="utf-8" type="text/javascript" src="javascript_outils.js?<? print uniqid(); ?>"></script>
<table border="0">
<tr>
	<td style="vertical-align:top;width:450px;">
		<h1><? print get_translation('THE_TOOLS','Les outils'); ?></h1>
		<h2><a href="outils.php?action=outil_thesaurus"><? print get_translation('REFERENTIAL_STRUCTURED_DATA','Référentiel données structurées'); ?></a></h2>
		<h2><a href="outils.php?action=mapper_patient"><? print get_translation('MAPPING_PATIENT','Retrouver IPP des patients'); ?></a></h2>
		<h2><a href="outils.php?action=similarity_virtual_patient"><? print get_translation('SIMILARITY_ON_VIRTUAL_PATIENT','Similarité sur patient virtuel'); ?></a></h2>
		<h2><a href="outils.php?action=comparateur"><? print get_translation('PATIENTS_COMPARE','Comparateur de patients'); ?></a></h2>
		<h2><a href="outils.php?action=comparateur_cohorte"><? print get_translation('COHORTS_COMPARE','Comparateur de cohortes'); ?></a></h2>
		<?
			list_outil();
		?>
	</td>
	<td style="vertical-align:top;">
		<div id="id_div_description_outil">
		
		<? if ($_GET['action']=='similarity_virtual_patient') { ?>
			<h1><? print get_translation('SIMILARITY_ON_VIRTUAL_PATIENT','Similarité sur patient virtuel'); ?></h1>
			<script type="text/javascript" src="javascript_similarite_patient.js?v=<? print $date_today_unique; ?>"></script>
		      	<div>		
			<h2><? print get_translation('SELECT_CONCEPT','Choisissez les concepts médicaux'); ?></h2>
		      		<select class="js-concept-select"  id="id_concept_select" style="width:500px" name="concept" lang="fr"></select><br><br>
		      		
			<h2><? print get_translation('SELECT_A_VIRTUAL_PATIENT','Choisissez un patient virtuel'); ?></h2>
		      		<select class="js-list-virtual-patient-select"  id="id_list_virtual_patient_select" style="width:500px" name="virtual_patient_num" lang="fr"></select><br><br>
		      		
			<h2><? print get_translation('LIST_SELECTED_CONCEPTS','Liste des concepts sélectionnés'); ?></h2>
				<div id="id_list_concepts" style="background-color:#f3f6fd;padding:10px;"></div><br>
				<br>
				<input type="button" class="form_submit" value="Calculer" onclick="calculate_similarity();">
				<input class="bouton_sauver option_button_similarity" id="button_id_div_patient_similarite_patient_options" type="button" onclick="open_option_virtual_patient('id_div_patient_similarite_patient_options');" value="<? print get_translation('OPTIONS','Options'); ?>">
				<input class="bouton_sauver option_button_similarity" id="button_id_div_save_virtual_patient" type="button" onclick="open_option_virtual_patient('id_div_save_virtual_patient');" value="<? print get_translation('SAVE_VIRTUAL_PATIENT','Sauver le patient virtuel'); ?>">
				<input class="bouton_sauver option_button_similarity" id="button_id_div_virtual_patient_list" type="button" onclick="open_option_virtual_patient('id_div_virtual_patient_list');" value="<? print get_translation('MANAGE_VIRTUAL_PATIENT','Gérer vos patients virtuels'); ?>"><br>
				<br><br>
				<!-- DIV SAVE PATIENT --> 
				<div id="id_div_save_virtual_patient" style="display:none;" >
					<? print get_translation('NAME_OF_VIRTUAL_PATIENT','Nommer le patient virtuel'); ?>  <input type="text" size="50" id="id_input_name_virtual_patient"><br>
					<? print get_translation('DESCRIPTION','Description'); ?>  <textarea cols="50" rows="5" id="id_input_description_virtual_patient"></textarea><br>
					<input type="button" onclick="save_name_virtual_patient();" value="<? print get_translation('SAVE_VIRTUAL_PATIENT','Sauver le patient virtuel'); ?>">
				</div>
				<div id="id_div_save_virtual_patient_done" style="display:none;" ><? print get_translation('VIRTUAL_PATIENT_SAVED','Patient virtuel sauvé'); ?> </div>
				<div id="id_div_virtual_patient_list" style="display:none;" ></div>
				
				<!-- DIV OPTION SIMILARITY --> 
				<div id="id_div_patient_similarite_patient_options" style="display:none;" >
					<? $process_num=get_uniqid(); ?>
					<input type="hidden" id="id_process_num" value="<? print $process_num; ?>">
					<input type="hidden" id="id_list_concept_selected" value="">
					<input type="hidden" id="id_list_concept_selected_weight" value="">
					<? print get_translation('LIMIT_TO_PATIENTS_WITH_TERMS_IN_DOCUMENTS','Limiter aux patients avec ces termes dans les comptes rendus');?>:  <input type="text" size="50" id="id_textarea_similarite_patient_requete"><input type="button" onclick="precalcul_nb_patient_similarite_patient('<? print $patient_num; ?>');" value="<? print get_translation('PRECOMPUTE','Pré-calculer'); ?>"><? print get_translation('COUNT_PATIENT_NUMBER_SHORT','Nb de patients'); ?> : <span id="id_span_precalcul_nb_patient_similarite_patient"></span><br>
					<? print get_translation('EXCLUDE_PATIENT_WITH_TERMS_IN_CASE_REPORT','Exclure les patients avec ces termes dans les comptes rendus');?> :  <input type="text" size="50" id="id_input_requete_exclure"><br>
					<? print get_translation('COUNT_MINIMUM_CONCEPTS_PER_PATIENT','Nb minimum de concepts par patient'); ?> : <input type="text" size="3" id="id_input_limite_count_concept_par_patient_num" value="4"><br>
					<? print get_translation('LIMIT_NUMBER_SIMILAR_PATIENTS','Limite nombre de patients similaires'); ?> : <input type="text" size="3" id="id_input_nbpatient_limite" value="20"><br>
					<? print get_translation('EXCLUDE_RESULT_COHORT','Exclure cette cohorte des résultats'); ?> : <select id=id_cohort_num_exclure class="chosen-select" data-placeholder="<? print get_translation('SELECT_A_COHORT','Sélectionnez une cohorte'); ?>">
					<?	
						display_user_cohorts_option ($user_num_session,'id_option_similarite_cohorte_') 
					?>
					</select><br>
				</div>
				
				
				<!-- DIV RESULT --> 
				<div id="id_div_patient_similarite_patient_resultat" style="display:block;" ></div>
				<div id="id_affiche_intersect" style="width:300px;height:500px;border:1px black solid;display:none;overflow-y: auto;position:absolute;background-color:white;"></div>

			</div>
	<script language="javascript">
	
		/* select2 concepts*/
		var table_concept_code=[];
		jQuery(".js-concept-select").select2({
			placeholder:"search concept",
			language: "fr",
		    	minimumInputLength: 3,
		    	allowClear: true,
			ajax: {
			      url: "ajax_outils.php",
			      dataType: 'json',
			      type: "POST",
			      delay: 250,
			      data: function (params) {
			      	return {
			      		q: params.term, 
			      		action : 'get_concept'
			        	};
			      },
			      processResults: function (data) {
			          return {
			              results: data.items
			          };
			      },
			}
		
		});
		
		jQuery(".js-concept-select").on("change", function (e) {			
			var concept_code = jQuery(".js-concept-select").val();		
			var concept_str = jQuery(".js-concept-select").html();	
			if (concept_code === null) {
			} else {		
				get_concept_info (concept_code);
			}
			
		})
		
		jQuery("#id_list_virtual_patient_select").on("change", function (e) {
			table_concept_code=[];	
			var virtual_patient_num = jQuery("#id_list_virtual_patient_select").val();		
			
			if (virtual_patient_num === null) {
			} else {		
				get_list_concept_virtual_patient (virtual_patient_num);
			}
			
		})
		jQuery(document).ready(function(){
		    jQuery('.autosizejs').autosize();   
		    get_list_patient_in_select();
		    
		});
	</script>
		
	<? } ?>
		
		
		
		
		<? if ($_GET['action']=='comparateur') { ?>
			<table border=0>
				<tr>
					<td style="vertical-align:top;">
						<h2><? print get_translation('CHOOSE_PATIENT_SINGULAR','Choisissez le patient'); ?> 1 </h2>
						<div class="ui-widget" style="padding-left: 0px;width:260px;font-size:10px;display:inline;">
							<span class="ui-helper-hidden-accessible" aria-live="polite" role="status"></span>
							<span class="ui-helper-hidden-accessible" role="status" aria-live="polite"></span>
							<input id="id_champs_comparateur_patient_1" onclick="if (this.value=='<? print get_translation('NAME_OR_PATIENT_ID','Nom / IPP'); ?>') {this.value='';}" class="form ui-autocomplete-input" type="text" size="15" name="hospital_patient_id" value="<? print get_translation('NAME_OR_PATIENT_ID','Nom / IPP'); ?>" style="font-size:10px;padding:0px;" autocomplete="off" onkeypress="if(event.keyCode==13) {alert('<? print get_translation('SELECT_A_PATIENT_UNDERNEATH','Selecionnez un patient ci dessous'); ?>');return false;}">
						</div>
						<div id="id_patient_1"></div>
						<input type="hidden" id="id_patient_num_1">
					</td>
					<td></td>
					<td style="vertical-align:top;">
						<h2><? print get_translation('CHOOSE_PATIENT_SINGULAR','Choisissez le patient'); ?> 2 </h2>
						<div class="ui-widget" style="padding-left: 0px;width:260px;font-size:10px;display:inline;">
							<span class="ui-helper-hidden-accessible" aria-live="polite" role="status"></span>
							<span class="ui-helper-hidden-accessible" role="status" aria-live="polite"></span>
							<input id="id_champs_comparateur_patient_2" onclick="if (this.value=='<? print get_translation('NAME_OR_PATIENT_ID','Nom / IPP'); ?>') {this.value='';}" class="form ui-autocomplete-input" type="text" size="15" name="hospital_patient_id" value="<? print get_translation('NAME_OR_PATIENT_ID','Nom / IPP'); ?>" style="font-size:10px;padding:0px;" autocomplete="off" onkeypress="if(event.keyCode==13) {alert('<? print get_translation('SELECT_A_PATIENT_UNDERNEATH','Selecionnez un patient ci dessous'); ?>');return false;}">
						</div>
						<div id="id_patient_2"></div>
						<input type="hidden" id="id_patient_num_2">
					</td>
					<td><? print get_translation('GRANULARITY','Granularité'); ?> : <input type="text" size="2" value="10" id="id_distance"><br>
					<input type="button" value="<? print get_translation('COMPARE','comparer'); ?>" onclick="comparer_patient();"></td>
				</tr>
				<tr>
					<td style="padding-right:20px; border-bottom: 1px solid black;font-size: 15px;">
						<? print get_translation('CONCEPTS_PATIENT_ONLY','Concepts uniquement patient'); ?> 1
					</td>
					<td style="padding-left:20px;padding-right:20px;border-right:1px black solid;border-left:1px black solid; border-bottom: 1px solid black;font-size: 15px;">
						<? print get_translation('CONCEPTS_COMMON','Concepts communs'); ?>
					</td>
					<td style="padding-left:20px; border-bottom: 1px solid black;font-size: 15px;">
						<? print get_translation('CONCEPTS_PATIENT_ONLY','Concepts uniquement patient'); ?> 2
					</td>
				</tr>
				<tr>
					<td style="vertical-align:top; font-size: 13px;padding-right:20px;">
						<div id="id_div_resultat_patient_1"></div>
					</td>
					<td style="vertical-align:top; font-size: 13px;padding-left:20px;padding-right:20px;border-right:1px black solid;border-left:1px black solid;">
						<div id="id_div_resultat_intersect"></div>
					</td>
					<td style="vertical-align:top; font-size: 13px;padding-left:20px;">
						<div id="id_div_resultat_patient_2"></div>
					</td>
				</tr>
			</table>
		
			<?
				$patient_num_1=$_GET['patient_num_1'];
				$patient_num_2=$_GET['patient_num_2'];
				
				if ($patient_num_1!='') {
					$patient1=afficher_patient ($patient_num_1,'basique','','','outils');
				}
				if ($patient_num_2!='') {
					$patient2=afficher_patient ($patient_num_2,'basique','','','outils');
				}
			?>
			<? if ($patient_num_1!='' || $patient_num_2!='') { ?>
				<script language="javascript">
				
					document.getElementById('id_patient_num_1').value="<? print $patient_num_1; ?>";
					document.getElementById('id_patient_num_2').value="<? print $patient_num_2; ?>";
					document.getElementById('id_patient_1').innerHTML="<? print $patient1; ?>";
					document.getElementById('id_patient_2').innerHTML="<? print $patient2; ?>";
					comparer_patient();
				</script>
			<? } ?>
		<? } ?>
		
		
		
		<? if ($_GET['action']=='comparateur_cohorte') { ?>
			<table border=0>
				<tr>
					<td style="vertical-align:top;">
						<h2><? print get_translation('CHOOSE_COHORT','Choisissez la cohorte'); ?> 1 </h2>
						<select id=id_cohort_num_1>
						<option value=''></option>
						<?
							$sel=oci_parse($dbh,"select cohort_num,title_cohort,user_num from dwh_cohort where cohort_num in (select cohort_num from DWH_COHORT_USER_RIGHT where user_num=$user_num_session) order by title_cohort  ");
							oci_execute($sel);
							while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
								$cohort_num=$r['COHORT_NUM'];
								$title_cohort=$r['TITLE_COHORT'];
								$user_num_creation=$r['USER_NUM'];
								$author=get_user_information ($user_num_creation,'pn');
								print "<option value='$cohort_num'>$title_cohort ($author)</option>";
							}
						?>
						</select>
						<br>
						<? print get_translation('CHOOSE_PATIENT_PLURAL','Choisissez les patiens'); ?> : <br>
						<input type=checkbox value=1 id=id_etat_patient_cohorte_1_inclu checked> <? print get_translation('INCLUDED_PLURAL','Inclus'); ?>
						<input type=checkbox value=0 id=id_etat_patient_cohorte_1_exclu> <? print get_translation('EXCLUDED_PLURAL','Exclus'); ?>
						<input type=checkbox value=2 id=id_etat_patient_cohorte_1_doute> <? print get_translation('DOUBT','Doute'); ?>
					</td>
					<td></td>
					<td style="vertical-align:top;">
						<h2><? print get_translation('CHOOSE_COHORT','Choisissez la cohorte'); ?> 2 </h2>
						<select id=id_cohort_num_2>
						<option value=''></option>
						<?
							$sel=oci_parse($dbh,"select cohort_num,title_cohort,user_num from dwh_cohort where cohort_num in (select cohort_num from DWH_COHORT_USER_RIGHT where user_num=$user_num_session) order by title_cohort  ");
							oci_execute($sel);
							while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
								$cohort_num=$r['COHORT_NUM'];
								$title_cohort=$r['TITLE_COHORT'];
								$user_num_creation=$r['USER_NUM'];
								$author=get_user_information ($user_num_creation,'pn');
								print "<option value='$cohort_num'>$title_cohort ($author)</option>";
							}
						?>
						</select>
						<br>
						<? print get_translation('CHOOSE_PATIENT_PLURAL','Choisissez les patiens'); ?> : <br>
						<input type=checkbox value=1 id=id_etat_patient_cohorte_2_inclu checked> <? print get_translation('INCLUDED_PLURAL','Inclus'); ?>
						<input type=checkbox value=0 id=id_etat_patient_cohorte_2_exclu> <? print get_translation('EXCLUDED_PLURAL','Exclus'); ?>
						<input type=checkbox value=2 id=id_etat_patient_cohorte_2_doute> <? print get_translation('DOUBT','Doute'); ?>
					</td>
					<td><? print get_translation('GRANULARITY','Granularité'); ?> : <input type="text" size="2" value="10" id="id_distance"><br>
					<input type="button" value="<? print get_translation('COMPARE','comparer'); ?>" onclick="comparer_cohorte();"></td>
				</tr>
				<tr>
					<td style="padding-right:20px; border-bottom: 1px solid black;font-size: 15px;">
						<? print get_translation('CONCEPTS_COHORT_ONLY','Concepts uniquement cohorte'); ?> 1
					</td>
					<td style="padding-left:20px;padding-right:20px;border-right:1px black solid;border-left:1px black solid; border-bottom: 1px solid black;font-size: 15px;">
						<? print get_translation('CONCEPTS_COMMON','Concepts communs'); ?>
					</td>
					<td style="padding-left:20px; border-bottom: 1px solid black;font-size: 15px;">
						<? print get_translation('CONCEPTS_COHORT_ONLY','Concepts uniquement cohorte'); ?> 2
				</tr>
				<tr>
					<td style="vertical-align:top; font-size: 13px;padding-right:20px;">
						<div id="id_div_resultat_cohorte_1"></div>
					</td>
					<td style="vertical-align:top; font-size: 13px;padding-left:20px;padding-right:20px;border-right:1px black solid;border-left:1px black solid;">
						<div id="id_div_resultat_cohorte_intersect"></div>
					</td>
					<td style="vertical-align:top; font-size: 13px;padding-left:20px;">
						<div id="id_div_resultat_cohorte_2"></div>
					</td>
				</tr>
			</table>
		
		<? } ?>
		
		
		
		<? if ($_GET['action']=='mapper_patient') { ?>
			<h1><? print get_translation('TO_MAP_PATIENT','Retrouver les patients'); ?></h1>
			<?
				print get_translation('MANUAL_MAPPING_PATIENTS',"Pour retrouver des patients")."<br>";
				print get_translation('MANUAL_MAPPING_PATIENTS_COPY_PAST_LIST_OF_PATIENTS',"Copier coller une liste de patients")."<br>";
				print get_translation('MANUAL_MAPPING_PATIENTS_RESPECT_ORDER_COLUMNS',"Respecter l'ordre des colonnes")." :<br>";
				print get_translation('MANUAL_MAPPING_PATIENTSLIST_COLONNE',"LASTNAME;FIRSTNAME;DATE DE NAISSANCE")."<br>";
				print get_translation('MANUAL_MAPPING_PATIENTS_SEPARATOR',"Utiliser comme séparateur le \";\" ou la \",\" ou la tabulation (un copier coller depuis Excel fonctionnera)")." :<br>";
				print "<pre>garcelon;nicolas;13/05/2007<br>garcelon	nicolas	13/05/2007<br>garcelon,nicolas,13/05/2007</pre><br><br>";
				print get_translation('YOU_CAN_ADD_COMMENT_COLUMN',"Vous pouvez ajouter une 4ieme colonne de commentaire (avec une patient_id si vous voulez)").".<br><br>";
				
				
				print get_translation('MANUAL_MAPPING_LIST_IPP_TEXT',"Vous pouvez aussi mettre une liste d'IPP pour récupérer les noms prénoms date de naissace et IPP maitre <br>(ou une liste de patient ids internes à Dr Warehouse, il faut alors cocher la case le précisant)")."<br>";
				print "<pre>123456789<br>2122145324<br>687654131</pre><br>";
				print "
				<table border=\"0\">
					<tr><td style=\"vertical-align:top;\"><textarea id=\"id_textarea_mapper_patient\"  style=\"width:500px;\" rows=\"10\"></textarea></td></tr>
				</table>
				<br><input type='checkbox'  name='option_limite' id='id_option_limite' value='oui'> ". get_translation('AUTHORIZE_MORE_THAN_ONE_PATIENT',"Autoriser les mapping si plus d'un patient trouvé par patient")." 
				<br><input type='checkbox'  name='option_patient_num' id='id_option_patient_num' value='yes'> ". get_translation('LIST_PAT_ID_DRWH',"Liste de patient id dans dr warehouse")."<br><br>
				<input type=\"button\" value=\"Mapper ces patients\" onclick=\"mapper_patient();\"> ";
				
				print "<br><br><div id=\"id_journal_mapping_patient\" style=\"width:100%;\" ></div>";
				
				print "<br><div id=\"id_result_mapping_patient\" style=\"width:100%;\" ></div>";
			?>
		<? } ?>
				
		<?
		///////////////// CONCEPT //////////////////
		if ($_GET['action']=='outil_thesaurus') {
		?>
			<h1><? print get_translation('DISPLAY_STRUCTURED_DATA','Afficher les données structurées'); ?></h1>
			
			<table border=0>
			<tr><td><? print get_translation('THESAURUS_CHOICE','Choix du thesaurus'); ?> : </td>
			<td><select id="id_thesaurus_code">
				<option value="">Thesaurus</option>	
			<?
		      		$thesaurus=get_list_thesaurus_data();
		      		foreach ($thesaurus as $thesaurus_code) {
		      				print"<option value=\"$thesaurus_code\">$thesaurus_code</option>";	
		      		}	
			?>
			</select></td></tr>
			<tr><td><? print get_translation('DISPLAY_TYPE',"Type d'affichage"); ?> : </td>
			<td><select id="display_type">
				<option value="table">Tableau</option>	
				<option value="tree">Arbre</option>	
			</select></td></tr>
			<tr><td><? print get_translation('FILTER',"Filtrer"); ?> : </td>
			<td><input type=text id=id_thesaurus_data_search value=''  onkeypress="if(event.keyCode==13) {display_thesaurus();}"> </tr>
			</table><br>
			<input type=button onclick="display_thesaurus();" value="<? print get_translation('DISPLAY','Afficher'); ?>"><br>
			<div id="id_div_result_thesaurus_data" style="display:none;"></div>

			
		<?
		}
		?>
		</div>
	
	</td>
</tr>
</table>



<? save_log_page($user_num_session,'tools'); ?>
<? include "foot.php"; ?>