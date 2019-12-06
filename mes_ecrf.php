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
    
    
create table dwh_ecrf
(ecrf_num int,
user_num int,
ecrf_title varchar(4000),
ecrf_description varchar(4000),
ecrf_date date,
primary key (ecrf_num));

create table dwh_ecrf_item (
ecrf_item_num int,
ecrf_num int,
item_str varchar(4000),
item_type varchar(200), // unique, multiple, date, number, text //
item_list varchar(4000),
primary key (ecrf_item_num),
foreign key ( ecrf_num) references dwh_ecrf (ecrf_num) on delete cascade) ;


create table dwh_ecrf_answer (
ecrf_item_num int,
ecrf_num int,
patient_num int,
user_num int,
automated_value varchar(4000),
localisation_value varchar(4000),
user_validation varchar(200)
);

*/
$tableau_ecrf_right=array('utiliser','modifier');

include "head.php";
include "menu.php";
include "fonctions_ecrf.php";
session_write_close();



if ($_POST['action']=='ajouter_ecrf' ) {
	$title_ecrf=nettoyer_pour_insert(urldecode(trim($_POST['title_ecrf'])));
	$description_ecrf=nettoyer_pour_insert(urldecode(trim($_POST['description_ecrf'])));
	$token_ecrf=nettoyer_pour_insert(urldecode(trim($_POST['token_ecrf'])));
	$ecrf_url=nettoyer_pour_insert(urldecode(trim($_POST['ecrf_url'])));
	if ($title_ecrf!='') {
		print("--".$ecrf_url."--");
		$ecrf_num_ajout=insert_ecrf ($ecrf_num_ajout,$title_ecrf,$description_ecrf,$ecrf_url,$user_num_session);
		insert_ecrf_user_right ($ecrf_num_ajout,$user_num_session,'modifier');
		insert_ecrf_user_right ($ecrf_num_ajout,$user_num_session,'utiliser');
		
		insert_ecrf_token ($user_num_session, $ecrf_num_ajout,$token_ecrf);
		
		$_GET['ecrf_num_voir']=$ecrf_num_ajout;
		echo "<script type='text/javascript'>document.location.replace('mes_ecrf.php?ecrf_num_voir=$ecrf_num_ajout');</script>";
	}
}
?>
<h1><? print get_translation('MY_ECRF','Mes Formulaires'); ?></h1>

<script type="text/javascript" src="javascript_ecrf.js?v=<? print $date_today_unique; ?>"></script>

<table border="0">

<tr>
	<td style="vertical-align:top;width:400px;">
		<h2 onclick="plier_deplier('id_div_creer_ecrf');plier_deplier('id_div_mon_ecrf');" style="cursor:pointer;"><span id="plus_id_div_creer_ecrf">+</span> <? print get_translation('CREATE_ECRF','Créer un Formulaire'); ?></h2>
		<h2><? print get_translation('MY_ECRF','Mes Formulaires'); ?> :</h2>
		<div id="id_div_liste_ecrf">
		<?
		
		  lister_mes_ecrf_tableau($user_num_session);
		?>
		</div>
		
	</td>
	<td style="vertical-align:top;">
		<div id="id_div_creer_ecrf" style="display:none;">
			<h3><? print get_translation('CREATE_ECRF','Créer un ecrf'); ?></h3>
			<form action="mes_ecrf.php" method="post" id="id_form_ajouter_ecrf">
				<table>
					<tr><td class="question_user"><? print get_translation('TITLE','Titre'); ?> : </td><td><input type="text" size="50"  name="title_ecrf" id="id_ajouter_titre_ecrf" class="form"></td></tr>
					<tr><td style="vertical-align:top;" class="question_user" ><? print get_translation('DESCRIPTION','Description'); ?> : </td><td>
						<textarea id="id_ajouter_description_ecrf" cols="50" rows="6" class="form" name="description_ecrf"></textarea>
					</td></tr>
					<tr><td class="question_user"><? print get_translation('TOKEN','Token'); ?> : </td><td><input type="text" size="50"  name="token_ecrf" id="id_ajouter_token_ecrf" class="form"></td></tr>
					<tr><td class="question_user"><? print get_translation('URL_ECRF','URL de l\'eCRF'); ?> : </td><td><input type="text" size="50"  name="ecrf_url" id="id_ajouter_ecrf_url" class="form" value="http://redcap.nck.aphp.fr/api/"></input></td></tr>
				</table>
				<input type="button"  class="form" value="<? print get_translation('ADD','ajouter'); ?>" onclick="valider_formulaire_ajouter_ecrf();">
				<input type="hidden" name="action" value="ajouter_ecrf">
			</form>
		</div>
		<div id="id_div_mon_ecrf" style="display:block;">
			<? 
			if ($_GET['ecrf_num_voir'] !='') { 
				$ecrf_num_voir=$_GET['ecrf_num_voir'];
				$autorisation_ecrf_voir=autorisation_ecrf_voir ($ecrf_num_voir,$user_num_session);
				if ($autorisation_ecrf_voir=='ok') {
					print "<input type=\"hidden\" id=\"id_ecrf_num_encours\" value=\"$ecrf_num_voir\">";
					$autorisation_ecrf_modifier=autorisation_ecrf_modifier ($ecrf_num_voir,$user_num_session);
					$autorisation_ecrf_supprimer=autorisation_ecrf_supprimer ($ecrf_num_voir,$user_num_session);
					
					$ecrf=get_ecrf($ecrf_num_voir);
			                $title_ecrf=$ecrf['title_ecrf'];
			                $description_ecrf=$ecrf['description_ecrf'];
			                $user_num_creation=$ecrf['user_num'];
			                $url_ecrf=$ecrf['url_ecrf'];
			                $nb_patients=$ecrf['nb_patients'];
			                $ecrf_start_date=$ecrf['ecrf_start_date'];
			                $ecrf_end_date=$ecrf['ecrf_end_date'];
			                $ecrf_start_age=$ecrf['ecrf_start_age'];
			                $ecrf_end_age=$ecrf['ecrf_end_age'];
			                
			                $tab_token=get_ecrf_token ($user_num_session, $ecrf_num_voir);
  				        $token_ecrf=$tab_token['token_ecrf'];
			                
				        
			                $firstname_lastname_createur= get_user_information ($user_num_creation,'pn');
			                
					if (trim($description_ecrf)=='' && $autorisation_ecrf_modifier=='ok') {
						$description_ecrf= get_translation('ADD_ECRF_DESCRIPTION_CLIC_HERE',"Ajouter une description en cliquant ici");
					}

					// API TOKEN BLOCK
			        	if ($autorisation_ecrf_modifier=='ok') {
			        		print "<br>";
					}

					$description_ecrf_voir=preg_replace("/\n/","<br>",$description_ecrf);

				
					print "<h2>".get_translation('ECRF','Ecrf')." : ";
					if ($autorisation_ecrf_modifier=='ok') {
						print "<span id=\"id_titre_ecrf\" onclick=\"plier('id_titre_ecrf');deplier('id_titre_ecrf_modifier','inline');\"><span id=\"id_sous_span_titre_ecrf\">$title_ecrf</span> <img src=\"images/poubelle_moyenne.png\" border=0 onclick=\"supprimer_ecrf($ecrf_num_voir);\" style=\"cursor:pointer;vertical-align:middle\"></span>";
						print "<span id=\"id_titre_ecrf_modifier\" style=\"display:none;\"><input type='text' size='50' id='id_input_titre_ecrf' value=\"$title_ecrf\"> <input class=\"form_submit\" type=\"button\" value=\"Ok\" onclick=\"modifier_titre_ecrf($ecrf_num_voir);\"></span>";
					} else {
						print "$title_ecrf <a href=\"moteur.php?action=rechercher_dans_ecrf&ecrf_num=$ecrf_num_voir\" target=\"_blank\"><img src=\"images/search.png\" border=\"0\" style=\"cursor:pointer;vertical-align:middle\"></a>";
					}
					print "</h2>";
					print "<input type=\"hidden\" value=\"$ecrf_num_voir\" id=\"id_ecrf_num_voir\">";
					print "
					<div id=\"tabs\" style=\"width:100%\">
						<ul id=\"tab-links\">
							<li class=\"current color-bullet\" id=\"id_bouton_presentation_generale\"><span class=\"li-content\"><a href=\"#\" onclick=\"voir_ecrf_onglet('presentation_generale');return false;\">".get_translation('DESCRIPTION','Description')."</a></span></li>
							<li class=\"color-bullet\" id=\"id_bouton_item\">
								<span class=\"li-content\"><a href=\"#\" onclick=\"voir_ecrf_onglet('item');return false;\">".get_translation('ECRF_ITEM','Les items')."</a></span>
							</li>
							<li class=\"color-bullet\" id=\"id_bouton_patient\">
								<span class=\"li-content\"><a href=\"#\" onclick=\"voir_ecrf_onglet('patient');return false;\">".get_translation('PATIENTS','Patients')."</a></span>
							</li>
						</ul>
					</div>
					<br>
					<br>";
					print "<div id=\"id_div_ecrf_presentation_generale\" class=\"div_result\" style=\"display:inline;width:100%;\" >";
						if ($autorisation_ecrf_modifier=='ok') {
							print "<div id=\"id_div_ecrf_description\"  style=\"display:inline;width:100%;\"  onclick=\"plier('id_div_ecrf_description');deplier('id_div_ecrf_description_modifier','inline');\">
								$description_ecrf_voir
								</div>";
							print "<div id=\"id_div_ecrf_description_modifier\" style=\"display:none;width:100%;\">
									<textarea id=\"id_textarea_description_ecrf_modifier\" cols=\"50\" rows=\"6\" class=\"form\" name=\"description_ecrf\">$description_ecrf</textarea> <input class=\"form_submit\" type=\"button\" value=\"Ok\" onclick=\"modifier_description_ecrf($ecrf_num_voir);\">
								</div>";
						} else {
							print "<div id=\"id_div_ecrf_description\"  style=\"display:inline;width:100%;\" >
								$description_ecrf_voir
								</div>";
						}
						print "<br><br>";

						// API TOKEN BLOCK
						print "<div id=\"id_div_token_ecrf\"  style=\"display:inline;width:100%;\"  onclick=\"plier('id_div_token_ecrf');deplier('id_div_token_ecrf_modifier','inline');\">
							$token_ecrf - ".get_translation('MODIFY_API_TOKEN',"Modifier l'API TOKEN en cliquant ici")."
							</div>";
						print "<div id=\"id_div_token_ecrf_modifier\" style=\"display:none;width:100%;\">
								<input id=\"id_input_token_ecrf_modifier\" size=\"100\" class=\"form\" name=\"token_ecrf\" value=\"$token_ecrf\"></input> <input class=\"form_submit\" type=\"button\" value=\"Ok\" onclick=\"modifier_token_ecrf($ecrf_num_voir);\">
						</div><br>";

						// API TOKEN BLOCK
						print "<div id=\"id_div_url_ecrf\"  style=\"display:inline;width:100%;\"  onclick=\"plier('id_div_url_ecrf');deplier('id_div_url_ecrf_modifier','inline');\">
							$url_ecrf - ".get_translation('MODIFY_URL_ECRF',"Modifier l'URL de l'ECRF")."
							</div>";
						print "<div id=\"id_div_url_ecrf_modifier\" style=\"display:none;width:100%;\">
								<input id=\"id_input_url_ecrf_modifier\" size=\"100\" class=\"form\" name=\"url_ecrf\" value=\"$url_ecrf\"></input> <input class=\"form_submit\" type=\"button\" value=\"Ok\" onclick=\"modifier_url_ecrf($ecrf_num_voir);\">
						</div><br>";
							
							
							
						print "<br>".get_translation('CREATED_BY','Créée par')." $firstname_lastname_createur ";
						print "<br><br>";
						print "<div id=\"id_tableau_liste_user_ecrf\">";
						affiche_liste_user_ecrf($ecrf_num_voir,$user_num_session);
						print "</div>";
						if ($autorisation_ecrf_modifier=='ok') {
							print get_translation('ADD_COLLABORATOR','Ajouter un collaborateur')." : 
							
							<select id=\"id_ajouter_select_user\" size=\"5\" class=\"form chosen-select\" onchange=\"ajouter_user_ecrf();\" data-placeholder=\"Choisissez un collaborateur\"><option value=''></option>";
							$table_users=get_list_users ();
							foreach ($table_users as $user_num) {
								$lastname_firstname= get_user_information ($user_num,'np');
								print "<option  value=\"$user_num\" id=\"id_ajouter_select_num_user_$user_num\">$lastname_firstname</option>";
							}
							print "</select>";
						}
					print "</div>";
					print "<div id=\"id_div_ecrf_patient\" class=\"div_result\" style=\"display:none;width:100%;\" >
						<br><br>
						<div id=\"id_liste_patient_ecrf\" style=\"width:100%;\" ></div>";
					print "</div>";
					
					
					print "<div id=\"id_div_ecrf_item\" class=\"div_result\" style=\"display:none;width:100%;\" >";
  						$list_ecrf_item=list_ecrf_item($ecrf_num_voir,$user_num_session);
  						$ecrf_redcap_items = "";
  						// Ajout Bastien - lien avec l'API REDCap
  						// Si un token est sauvé, le lien est établis.
  						if($token_ecrf != "") {
	  						$data = array(
							    'token' => $token_ecrf,
							    'content' => 'metadata',
							    'format' => 'json',
							    'returnFormat' => 'json'
							);
							$ch = curl_init();
							curl_setopt($ch, CURLOPT_URL, $url_ecrf);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
							curl_setopt($ch, CURLOPT_VERBOSE, 0);
							curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
							curl_setopt($ch, CURLOPT_AUTOREFERER, true);
							curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
							curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
							curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
							curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
							$output = curl_exec($ch);
							$obj = json_decode($output);
							curl_close($ch);
							if (is_array($obj)) {
								foreach($obj as $key => $value) {
								  $type = "";
								  $list_choices = "";
								  $list_codes = "";
								  switch ($value->field_type) {
								    case "text":
								        if ($value->text_validation_type_or_show_slider_number == "date_ymd") {
								        	$type = "date";
								        } elseif($value->text_validation_type_or_show_slider_number == "number") {
								        	$type = "number";
								        } else {
								        	$type = "text";
								        }
								        break;
								    case "yesno":
								        $type = "list";
								        $list_choices = "oui;non";
								        $list_codes = "0;1";
								        break;
								    case "radio":
								        $type = "radio";
	
								        // TO BE REFACTORED CF CHECKBOX BELOW
								        $list_choices_array = [];
								        $list_codes_array = [];
								        $exploded = explode("|", $value->select_choices_or_calculations);
								        foreach($exploded as $v) {
								          $codeValueArray = explode(', ', $v);
								          array_push($list_choices_array,trim($codeValueArray[1]));
								          array_push($list_codes_array,trim($codeValueArray[0]));
								        }
								        $list_choices = implode(";",$list_choices_array);
								        $list_codes = implode(";",$list_codes_array);
								        break;
								    case "checkbox":
								        $type = "list";
	
								        // TO BE REFACTORED CF RADIO ABOVE
								        $list_choices_array = [];
								        $list_codes_array = [];
								        $exploded = explode("|", $value->select_choices_or_calculations);
								        foreach($exploded as $v) {
								          $codeValueArray = explode(', ', $v);
								          array_push($list_choices_array,trim($codeValueArray[1]));
								          array_push($list_codes_array,trim($codeValueArray[0]));
								        }
								        $list_choices = implode(";",$list_choices_array);
								        $list_codes = implode(";",$list_codes_array);
								        break;
									}
	
									$ecrf_redcap_items .= utf8_decode($value->field_label . "\t" . $type . "\t" . $list_choices . "\t" . $value->field_name . "\t" . $list_codes . "\n");
								}
							}
						}
						print "
						
						<a href=\"#\" onclick=\"plier_deplier('id_bloc_ecrf_item');return false;\">+ ".get_translation('ADD_ECRF_LIST_ITEMS',"Ajouter les items par bloc")."</a>
						<div id=\"id_bloc_ecrf_item\" style=\"display:none\">";
							print get_translation('MANUAL_IMPORT_ITEM',"Pour importer des items")."<br>";
							print get_translation('MANUAL_IMPORT_PATIENTS_ECRF_COPY_PAST_LIST_OF_ITEMS',"Copier coller une liste de questions, leurs types et les valeurs attendues")."<br>";
							print get_translation('MANUAL_IMPORT_PATIENTS_ECRF_RESPECT_ORDER_COLUMNS',"Respecter l'ordre des colonnes")." :<br>";
							print get_translation('MANUAL_IMPORT_PATIENTS_ECRF_LIST_COLONNE',"QUESTION [tabulation] TYPE [Tabulation] LISTE DE VALEURS (séparateur ;)")."<br>";
							print "<pre>";
							print "Diabète	list	oui;non\n";
							print "Date intervention	date	\n";
							print "Créatinémie	number	\n";
							print "Antécédents	list	allergie;appendicite;infections;diarrhée\n";
							print "</pre><br>";
						print "
							<textarea id=\"id_textarea_importer_item\"  style=\"width:800px;\" rows=\"10\">".$ecrf_redcap_items."</textarea><br>
							<input type=\"button\" value=\"importer ces items\" onclick=\"importer_item_ecrf($ecrf_num_voir);\"> 
						</div>
						<br/>
						<br/>
						<br/>
						
						".get_translation('PERIOD_OF_EXTRACTION_FROM',"Période d'extraction des données du")." <input type=\"text\" id=\"id_ecrf_start_date\" size=\"12\" value=\"$ecrf_start_date\" placeholder=\"dd/mm/yyyy\" onblur=\" modifier_ecrf_period($ecrf_num_voir)\"> ".get_translation('TO_DATE',"à")." <input  type=\"text\" id=\"id_ecrf_end_date\" size=\"12\" value=\"$ecrf_end_date\" placeholder=\"dd/mm/yyyy\" onblur=\" modifier_ecrf_period($ecrf_num_voir)\">
						<br>
						".get_translation('AGE_INTERVAL_OF_EXTRACTION_FROM',"Interval d'âge (en années) pour l'extraction des données de")." <input  type=\"text\" id=\"id_ecrf_start_age\" size=\"3\" value=\"$ecrf_start_age\" onblur=\" modifier_ecrf_period($ecrf_num_voir)\"> ".get_translation('TO_AGE',"à")." <input  type=\"text\" id=\"id_ecrf_end_age\" size=\"3\" value=\"$ecrf_end_age\"  onblur=\" modifier_ecrf_period($ecrf_num_voir)\">
						
						<br/>
						<br/>
						
						<br/>
						<div id=\"id_list_item\" style=\"font-size:10px;\">$list_ecrf_item</div>
						<a href=\"#\" onclick=\"add_new_item($ecrf_num_voir);return false;\">+ ".get_translation('ADD_AN_ITEM',"Ajouter un item")."</a>
						<br>
						";
						
					print "</div>";
					
					?>	
							
					<?
				} else {
					print get_translation('YOU_CANNOT_SEE_THIS_ECRF',"Vous n'êtes pas autorisé à voir cet ecrf");
				}
			} 
			
			?>
		</div>
	</td>
</tr>
</table>

<script language="javascript">

$(document).ready( function () {
 	jQuery('#id_tableau_liste_ecrf').dataTable( { 
			paging: false,
			"order": [[ 1, "asc" ]],
			   "bInfo" : false,
			   "bDestroy" : true
	} );
	list_patient_ecrf(<? print $ecrf_num_voir;?>);
});
	
</script>


<? save_log_page($user_num_session,'my_ecrf'); ?>
<? include "foot.php"; ?>