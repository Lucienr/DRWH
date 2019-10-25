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
		<h2><a href="outils.php?action=comparateur"><? print get_translation('PATIENTS_COMPARE','Comparateur de patients'); ?></a></h2>
		<h2><a href="outils.php?action=comparateur_cohorte"><? print get_translation('COHORTS_COMPARE','Comparateur de cohortes'); ?></a></h2>
		<h2><a href="outils.php?action=mapper_patient"><? print get_translation('MAPPING_PATIENT','Retrouver IPP des patients'); ?></a></h2>
		<?
			list_outil();
		?>
	</td>
	<td style="vertical-align:top;">
		<div id="id_div_description_outil">
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
					<td><? print get_translation('GRANULARITY','Granularit�'); ?> : <input type="text" size="2" value="10" id="id_distance"><br>
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
					<td><? print get_translation('GRANULARITY','Granularit�'); ?> : <input type="text" size="2" value="10" id="id_distance"><br>
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
			<h1>Mapper patient</h1>
			<?
				print get_translation('MANUAL_MAPPING_PATIENTS',"Pour mapper des patients")."<br>";
				print get_translation('MANUAL_MAPPING_PATIENTS_COPY_PAST_LIST_OF_PATIENTS',"Copier coller une liste de patients")."<br>";
				print get_translation('MANUAL_MAPPING_PATIENTS_RESPECT_ORDER_COLUMNS',"Respecter l'ordre des colonnes")." :<br>";
				print get_translation('MANUAL_MAPPING_PATIENTSLIST_COLONNE',"LASTNAME;FIRSTNAME;DATE DE NAISSANCE")."<br>";
				print get_translation('MANUAL_MAPPING_PATIENTS_SEPARATOR',"Utiliser comme s�parateur le \";\" ou la \",\" ou la tabulation (un copier coller depuis Excel fonctionnera)")."<br>";
				print "<pre>garcelon;nicolas;13/05/2007<br>garcelon	nicolas	13/05/2007<br>garcelon,nicolas,13/05/2007</pre><br>";
				print get_translation('YOU_CAN_ADD_COMMENT_COLUMN',"Vous pouvez ajouter une 4ieme colonne de commentaire (avec une patient_id si vous voulez)")."<br>";
				
				
				print get_translation('MANUAL_MAPPING_LIST_IPP_TEXT',"Vous pouvez aussi mettre une liste d'IPP pour r�cup�rer les noms pr�noms date de naissace et IPP maitre")."<br>";
				print "<pre>123456789<br>2122145324<br>687654131</pre><br>";
				print "
				<table border=\"0\">
					<tr><td style=\"vertical-align:top;\"><textarea id=\"id_textarea_mapper_patient\"  style=\"width:500px;\" rows=\"10\"></textarea></td></tr>
				</table>
				<br>". get_translation('AUTHORIZE_MORE_THAN_ONE_PATIENT',"Autoriser les mapping si plus d'un patient trouv� par patient")." : <input type='checkbox'  name='option_limite' id='id_option_limite' value='oui'><br><br>
				<input type=\"button\" value=\"Mapper ces patients\" onclick=\"mapper_patient();\"> ";
				
				print "<br><br><div id=\"id_journal_mapping_patient\" style=\"width:100%;\" ></div>";
				
				print "<br><div id=\"id_result_mapping_patient\" style=\"width:100%;\" ></div>";
			?>
		<? } ?>
		</div>
	
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


<? save_log_page($user_num_session,'tools'); ?>
<? include "foot.php"; ?>