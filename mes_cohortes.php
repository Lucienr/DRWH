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



if ($_POST['action']=='ajouter_cohorte' ) {
	$title_cohort=nettoyer_pour_insert(urldecode(trim($_POST['title_cohort'])));
	$description_cohort=nettoyer_pour_insert(urldecode(trim($_POST['description_cohort'])));
	$num_datamart_cohorte=$_POST['num_datamart_cohorte'];
	
	if ($title_cohort!='') {
		$cohort_num_ajout=get_uniqid();
		
		$req="insert into dwh_cohort  (cohort_num , title_cohort ,description_cohort ,cohort_date,user_num,datamart_num ) 
					values ($cohort_num_ajout,'$title_cohort','$description_cohort',sysdate,$user_num_session,$num_datamart_cohorte)";
		$sel_var1=oci_parse($dbh,$req);
		oci_execute($sel_var1) || die ("<strong style=\"color:red\">".get_translation('ERROR','erreur')." : $title_cohort ".get_translation('NOT_SAVED','non sauvé')." $req</strong><br>");
		
		
		if ($num_datamart_cohorte==0) {
			foreach ($tableau_cohorte_droit as $right) {
				if ($_SESSION['dwh_droit_'.$right.'0']=='ok') {
					$req="insert into dwh_cohort_user_right  (cohort_num , user_num ,right) values ($cohort_num_ajout,$user_num_session,'$right')";
					$sel_var1=oci_parse($dbh,$req);
					oci_execute($sel_var1) || die ("<strong style=\"color:red\">".get_translation('ERROR','erreur')." : ".get_translation('USER_AND_RIGHTS_NOT_SAVED','user et droit non sauvé')."</strong><br>");
				}
			}
		} else {
			$sel_var1=oci_parse($dbh,"select right from dwh_datamart_user_right where datamart_num=$num_datamart_cohorte and user_num=$user_num_session  ");
			oci_execute($sel_var1);
			while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
				$right=$r['RIGHT'];
				$req="insert into dwh_cohort_user_right  (cohort_num , user_num ,right) values ($cohort_num_ajout,$user_num_session,'$right')";
				$sel_var1=oci_parse($dbh,$req);
				oci_execute($sel_var1) || die ("<strong style=\"color:red\">".get_translation('ERROR','erreur')." : ".get_translation('USER_AND_RIGHTS_NOT_SAVED','user et droit non sauvé')."</strong><br>");
			}
		}
		$_GET['cohort_num_voir']=$cohort_num_ajout;
		echo "<script type='text/javascript'>document.location.replace('mes_cohortes.php?cohort_num_voir=$cohort_num_ajout');</script>";
	}
}

if ($_POST['action']=='fusionner_cohorte' ) {
	$title_cohort=nettoyer_pour_insert(urldecode(trim($_POST['title_cohort'])));
	$description_cohort=nettoyer_pour_insert(urldecode(trim($_POST['description_cohort'])));
	$cohort_num1=$_POST['cohort_num1'];
	$cohort_num2=$_POST['cohort_num2'];
	$num_datamart_cohorte=0;
	
	if ($title_cohort!='') {
		$cohort_num_ajout=get_uniqid();
		
		$req="insert into dwh_cohort  (cohort_num , title_cohort ,description_cohort ,cohort_date,user_num,datamart_num ) 
					values ($cohort_num_ajout,'$title_cohort','$description_cohort',sysdate,$user_num_session,$num_datamart_cohorte)";
		$sel_var1=oci_parse($dbh,$req);
		oci_execute($sel_var1) || die ("<strong style=\"color:red\"".get_translation('ERROR','erreur')." : $title_cohort ".get_translation('NOT_SAVED','non sauvé')." $req</strong><br>");
		
		
		if ($num_datamart_cohorte==0) {
			foreach ($tableau_cohorte_droit as $right) {
				if ($_SESSION['dwh_droit_'.$right.'0']=='ok') {
					$req="insert into dwh_cohort_user_right  (cohort_num , user_num ,right) values ($cohort_num_ajout,$user_num_session,'$right')";
					$sel_var1=oci_parse($dbh,$req);
					oci_execute($sel_var1) || die ("<strong style=\"color:red\">".get_translation('ERROR','erreur')." : ".get_translation('USER_AND_RIGHTS_NOT_SAVED','user et right non sauvé')."</strong><br>");
				}
			}
		} 
		
		
		$req="select status,to_char(add_date,'DD/MM/YYYY'),user_num_add,patient_num  from dwh_cohort_result where cohort_num = $cohort_num1
		union
		select status,to_char(add_date,'DD/MM/YYYY'),user_num_add,patient_num  from dwh_cohort_result where cohort_num = $cohort_num2 ";
		$sel=oci_parse($dbh,$req);
		oci_execute($sel);
		while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$status=$r['STATUS'];
			$add_date=$r['ADD_DATE'];
			$user_ajout=$r['USER_NUM_ADD'];
			$patient_num=$r['PATIENT_NUM'];
			
			if ($tableau_patient_num_statut[$patient_num]=='') {
				$tableau_patient_num_statut[$patient_num]=$status;
				$tableau_patient_add_date[$patient_num]=" to_date('$add_date','DD/MM/YYYY') ";
				$tableau_patient_user_ajout[$patient_num]=$user_ajout;
			} else {
				if ($tableau_patient_num_statut[$patient_num]!=$status) {
					$tableau_patient_num_statut[$patient_num]=3;
				}			
			}			
		}
		
		foreach ($tableau_patient_num_statut as $patient_num=> $status) {
			$add_date=$tableau_patient_add_date[$patient_num];
			$user_ajout=$tableau_patient_user_ajout[$patient_num];
			$req="insert into dwh_cohort_result  (cohort_num , patient_num ,status,add_date,user_num_add) values ($cohort_num_ajout,$patient_num,$status,$add_date,$user_ajout)";
			$sel_var1=oci_parse($dbh,$req);
			oci_execute($sel_var1) || die ("<strong style=\"color:red\">".get_translation('ERROR','erreur')." : ".get_translation('PATIENT_NOT_ADDED_TO_COHORT','patient non ajouté à la cohorte')."</strong><br>");
		}
		
		
		$_GET['cohort_num_voir']=$cohort_num_ajout;
		echo "<script type='text/javascript'>document.location.replace('mes_cohortes.php?cohort_num_voir=$cohort_num_ajout');</script>";
	}
}
?>
<h1><? print get_translation('MY_COHORTS','Mes Cohortes'); ?></h1>


<? include "javascript_cohorte.php"; ?>

<table border="0">

<tr>
	<td style="vertical-align:top;width:400px;">
		<h2 onclick="plier_deplier('id_div_creer_cohorte');plier_deplier('id_div_ma_cohorte');" style="cursor:pointer;"><span id="plus_id_div_creer_cohorte">+</span> <? print get_translation('CREATE_COHORT','Créer une cohorte'); ?></h2>
		<h2 onclick="plier_deplier('id_div_fusionner_cohorte');plier_deplier('id_div_ma_cohorte');" style="cursor:pointer;"><span id="plus_id_div_fusionner_cohorte">+</span> <? print get_translation('MERGE_2_COHORTS','Fusionner 2 cohortes'); ?></h2>
		<h2><? print get_translation('MY_COHORTS','Mes cohortes'); ?> :</h2>
		<div id="id_div_liste_cohorte">
		<?
		
		  display_user_cohorts_table($user_num_session);
		?>
		</div>
		
	</td>
	<td style="vertical-align:top;">
		<div id="id_div_creer_cohorte" style="display:none;">
			<h3><? print get_translation('CREATE_COHORT','Créer une cohorte'); ?></h3>
			<form action="mes_cohortes.php" method="post" id="id_form_ajouter_cohorte">
				<table>
					<tr><td class="question_user"><? print get_translation('TITLE','Titre'); ?> : </td><td><input type="text" size="50"  name="title_cohort" id="id_ajouter_titre_cohorte" class="form"></td></tr>
					<tr>
						<td class="question_user"><? print get_translation('CONCERNED_DATAMART','Datamart concerné'); ?> : </td>
						<td>
							<select id="id_ajouter_select_datamart" class="form chosen-select" name="num_datamart_cohorte" data-placeholder="Choisissez un datamart"><option value='0'><? print get_translation('DEFAULT','Défaut'); ?></option>
							<?
								$sel_var1=oci_parse($dbh,"select distinct title_datamart,dwh_datamart.datamart_num from  dwh_datamart,dwh_datamart_user_right where temporary_status is null and dwh_datamart.datamart_num=dwh_datamart_user_right.datamart_num and dwh_datamart_user_right.user_num=$user_num_session and right='see_detailed' ");
								oci_execute($sel_var1);
								while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
									$num_datamart_cohorte=$r['DATAMART_NUM'];
									$title_datamart=$r['TITLE_DATAMART'];
									print "<option  value=\"$num_datamart_cohorte\">$title_datamart</option>";
								}
							?>
							</select>
						</td>
						</tr>
					<tr><td style="vertical-align:top;" class="question_user" ><? print get_translation('DESCRIPTION','Description'); ?> : </td><td>
						<textarea id="id_ajouter_description_cohort" cols="50" rows="6" class="form" name="description_cohort"></textarea>
					</td></tr>
					
				</table>
				<input type="button"  class="form" value="<? print get_translation('ADD','ajouter'); ?>" onclick="valider_formulaire_ajouter_cohorte();">
				<input type="hidden" name="action" value="ajouter_cohorte">
			</form>
		</div>
		<div id="id_div_fusionner_cohorte" style="display:none;">
			<h3><? print get_translation('MERGE_COHORT','Fusionner une cohorte'); ?></h3>
			<i><? print get_translation('IN_CASE_OF_CONFLICT_PATIENT_IN_IMPORTED','En cas de conflit sur 2 patients, il sera placé dans la liste des patients "importés"'); ?></i>
			<form action="mes_cohortes.php" method="post" id="id_form_fusionner_cohorte">
				<table>
					<tr><td class="question_user"><? print get_translation('TITLE_NEW_COHORT','Titre nouvelle cohorte'); ?> : </td><td><input type="text" size="50"  name="title_cohort" id="id_fusionner_titre_cohorte" class="form"></td></tr>
					<tr>
						<td class="question_user"><? print get_translation('COHORT','Cohorte'); ?> 1 : </td>
						<td>
							<select id="id_fusionner_select_cohorte1" class="form chosen-select" name="cohort_num1" data-placeholder="Choisissez une cohorte">
							<?
								display_user_cohorts_option ($user_num_session,'id_fusionner_select_cohorte1');
							?>
							</select>
						</td>
					</tr>
						
					<tr>
						<td class="question_user"><? print get_translation('COHORT','Cohorte'); ?> 2 : </td>
						<td>
							<select id="id_fusionner_select_cohorte2" class="form chosen-select" name="cohort_num2" data-placeholder="Choisissez une cohorte">
							<?
								display_user_cohorts_option ($user_num_session,'id_fusionner_select_cohorte2');
							?>
							</select>
						</td>
					</tr>
					<tr><td style="vertical-align:top;" class="question_user" ><? print get_translation('DESCRIPTION','Description'); ?> : </td><td>
						<textarea id="id_fusionner_description_cohort" cols="50" rows="6" class="form" name="description_cohort"></textarea>
					</td></tr>
					
				</table>
				<input type="button" class="form" value="<? print get_translation('MERGE','fusionner'); ?>" onclick="valider_formulaire_fusionner_cohorte();">
				<input type="hidden" name="action" value="fusionner_cohorte">
			</form>
		</div>
		<div id="id_div_ma_cohorte" style="display:block;">
			<? 
			if ($_GET['cohort_num_voir'] !='') { 
				$cohort_num_voir=$_GET['cohort_num_voir'];
				$autorisation_cohorte_voir=autorisation_cohorte_voir ($cohort_num_voir,$user_num_session);
				if ($autorisation_cohorte_voir=='ok') {
					print "<input type=\"hidden\" id=\"id_cohort_num_encours\" value=\"$cohort_num_voir\">";
					$autorisation_cohorte_modifier=autorisation_cohorte_modifier ($cohort_num_voir,$user_num_session);
					$autorisation_cohorte_supprimer=autorisation_cohorte_supprimer ($cohort_num_voir,$user_num_session);
					 $sel_vardroit=oci_parse($dbh,"select title_cohort,description_cohort,user_num,datamart_num from dwh_cohort where 
				         (user_num=$user_num_session or cohort_num in (select cohort_num from dwh_cohort_user_right where user_num=$user_num_session))
				         and cohort_num=$cohort_num_voir ");
				        oci_execute($sel_vardroit);
				        $r=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
			                $title_cohort=$r['TITLE_COHORT'];
			                $description_cohort=$r['DESCRIPTION_COHORT'];
			                $user_num_creation=$r['USER_NUM'];
			                $num_datamart_cohorte=$r['DATAMART_NUM'];
			                
			                $sel_pat=oci_parse($dbh,"select count(*) as nb_patient from dwh_cohort_result where cohort_num=$cohort_num_voir and status=1");
			                oci_execute($sel_pat);
			                $r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC);
			                $nb_patient_cohorte_inclu=$r_pat['NB_PATIENT'];
			                
			                
			                $sel_pat=oci_parse($dbh,"select count(*) as nb_patient from dwh_cohort_result where cohort_num=$cohort_num_voir and status=2");
			                oci_execute($sel_pat);
			                $r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC);
			                $nb_patient_cohorte_doute=$r_pat['NB_PATIENT'];
			                
			                
			                $sel_pat=oci_parse($dbh,"select count(*) as nb_patient from dwh_cohort_result where cohort_num=$cohort_num_voir and status=0");
			                oci_execute($sel_pat);
			                $r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC);
			                $nb_patient_cohorte_exclu=$r_pat['NB_PATIENT'];
			                
			                
			                $sel_pat=oci_parse($dbh,"select count(*) as nb_patient from dwh_cohort_result where cohort_num=$cohort_num_voir and status=3");
			                oci_execute($sel_pat);
			                $r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC);
			                $nb_patient_cohorte_import=$r_pat['NB_PATIENT'];
			                
			                $sel_pat=oci_parse($dbh,"select count(*) as nb_patient from dwh_cohort_result_comment where cohort_num=$cohort_num_voir ");
			                oci_execute($sel_pat);
			                $r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC);
			                $nb_patient_cohorte_commentaire=$r_pat['NB_PATIENT'];
			                
			                
			                $sel_pat=oci_parse($dbh,"select lastname,firstname from dwh_user where user_num=$user_num_creation");
			                oci_execute($sel_pat);
			                $r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC);
			                $lastname_createur=$r_pat['LASTNAME'];
			                $firstname_createur=$r_pat['FIRSTNAME'];
			                
			                $sel_pat=oci_parse($dbh,"select title_datamart from dwh_datamart where datamart_num=$num_datamart_cohorte");
			                oci_execute($sel_pat);
			                $r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC);
			                $title_datamart=$r_pat['TITLE_DATAMART'];
			                
					if (trim($description_cohort)=='' && $autorisation_cohorte_modifier=='ok') {
						$description_cohort= get_translation('ADD_COHORT_DESCRIPTION_CLIC_HERE',"Ajouter une description en cliquant ici");
					}
			                
					$description_cohort_voir=preg_replace("/\n/","<br>",$description_cohort);
				
					print "<h2>".get_translation('COHORT','Cohorte')." : ";
					if ($autorisation_cohorte_modifier=='ok') {
						print "<span id=\"id_titre_cohorte\" onclick=\"plier('id_titre_cohorte');deplier('id_titre_cohorte_modifier','inline');\"><span id=\"id_sous_span_titre_cohorte\">$title_cohort</span> <img src=\"images/poubelle_moyenne.png\" border=0 onclick=\"supprimer_cohorte($cohort_num_voir);\" style=\"cursor:pointer;vertical-align:middle\"></span>";
						print "<span id=\"id_titre_cohorte_modifier\" style=\"display:none;\"><input type='text' size='50' id='id_input_titre_cohorte' value=\"$title_cohort\"> <input class=\"form_submit\" type=\"button\" value=\"Ok\" onclick=\"modifier_titre_cohorte($cohort_num_voir);\"></span>";
						print " <a href=\"moteur.php?action=rechercher_dans_cohorte&cohort_num=$cohort_num_voir\" target=\"_blank\"><img src=\"images/search.png\" border=\"0\" style=\"cursor:pointer;vertical-align:middle\"></a>";
					} else {
						print "$title_cohort <a href=\"moteur.php?action=rechercher_dans_cohorte&cohort_num=$cohort_num_voir\" target=\"_blank\"><img src=\"images/search.png\" border=\"0\" style=\"cursor:pointer;vertical-align:middle\"></a>";
					}
					print "</h2>";
					print "<input type=\"hidden\" value=\"$cohort_num_voir\" id=\"id_cohort_num_voir\">";
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////////// ONGLET MENU COHORT  /////////////////////////////////////////////////////////////
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					print "
					<div id=\"tabs\" style=\"width:100%\">
						<ul id=\"tab-links\">
							<li class=\"current color-bullet\" id=\"id_bouton_presentation_generale\"><span class=\"li-content\"><a href=\"#\" onclick=\"voir_cohorte_onglet('presentation_generale');return false;\">".get_translation('DESCRIPTION','Description')."</a></span></li>
							<li class=\"color-bullet\" id=\"id_bouton_patient_inclu\">
								<span class=\"li-content\"><a href=\"#\" onclick=\"voir_cohorte_onglet('patient_inclu');return false;\">".get_translation('INCLUDED_PATIENTS','Patients inclus')."</a></span>
								<span id=\"id_nb_patient_cohorte_inclu\" style=\"color: red; font-weight: bold; left: 0px; position: relative;top: -11px; display: inline;\">$nb_patient_cohorte_inclu</span>
							</li>
							<li class=\"color-bullet\" id=\"id_bouton_patient_exclu\">
								<span class=\"li-content\"><a href=\"#\" onclick=\"voir_cohorte_onglet('patient_exclu');return false;\">".get_translation('PATIENTS_EXCLUDED','Patients exclus')."</a></span>
								<span id=\"id_nb_patient_cohorte_exclu\" style=\"color: red; font-weight: bold; left: 0px; position: relative;top: -11px; display: inline;\">$nb_patient_cohorte_exclu</span>
							</li>
							<li class=\"color-bullet\" id=\"id_bouton_patient_doute\">
								<span class=\"li-content\"><a href=\"#\" onclick=\"voir_cohorte_onglet('patient_doute');return false;\">".get_translation('PATIENTS_DOUBT','Patients doute')."</a></span>
								<span id=\"id_nb_patient_cohorte_doute\" style=\"color: red; font-weight: bold; left: 0px; position: relative;top: -11px; display: inline;\">$nb_patient_cohorte_doute</span>
							</li>
							<li class=\"color-bullet\" id=\"id_bouton_patient_import\">
								<span class=\"li-content\"><a href=\"#\" onclick=\"voir_cohorte_onglet('patient_import');return false;\">".get_translation('IMPORT_PATIENTS','Importer des patients')."</a></span>
								<span id=\"id_nb_patient_cohorte_import\" style=\"color: red; font-weight: bold; left: 0px; position: relative;top: -11px; display: inline;\">$nb_patient_cohorte_import</span>
							</li>
							<li class=\"color-bullet\" id=\"id_bouton_patient_commentaire\">
								<span class=\"li-content\"><a href=\"#\" onclick=\"voir_cohorte_onglet('patient_commentaire');return false;\">".get_translation('PATIENTS_COMMENTS','Commentaires patients')."</a></span>
								<span id=\"id_nb_patient_cohorte_commentaire\" style=\"color: red; font-weight: bold; left: 0px; position: relative;top: -11px; display: inline;\">$nb_patient_cohorte_commentaire</span>
							</li>
							<li class=\"color-bullet\" id=\"id_bouton_concepts\">
								<span class=\"li-content\"><a href=\"#\" onclick=\"voir_cohorte_onglet('concepts');display_cohort_concepts_tab ('$cohort_num_voir');return false;\">".get_translation('CONCEPTS','Concepts')."</a></span>
							</li>
							<li class=\"color-bullet\" id=\"id_bouton_patient_similaire\">
								<span class=\"li-content\"><a href=\"#\" onclick=\"voir_cohorte_onglet('patient_similaire');return false;\">".get_translation('SIMILAR_PATIENTS','Patients Similaires')."</a></span>
							</li>
						</ul>
					</div>
					<br>
					<br>";
					
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					///////////////////////////////// GENERAL PRESENTATION //////////////////////////////////////////////////////////
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					print "<div id=\"id_div_cohorte_presentation_generale\" class=\"div_result\" style=\"display:inline;width:100%;\" >";
					
						///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
						print "<h3>".get_translation("COHORT_INFORMATION","Description de la cohorte")."</h3>";
						if ($autorisation_cohorte_modifier=='ok') {
							print "<div id=\"id_div_cohorte_description\"  style=\"display:inline;width:100%;\"  onclick=\"plier('id_div_cohorte_description');deplier('id_div_cohorte_description_modifier','inline');\">
								$description_cohort_voir
								</div>";
							print "<div id=\"id_div_cohorte_description_modifier\" style=\"display:none;width:100%;\">
									<textarea id=\"id_textarea_description_cohort_modifier\" cols=\"50\" rows=\"6\" class=\"form\" name=\"description_cohort\">$description_cohort</textarea> <input class=\"form_submit\" type=\"button\" value=\"Ok\" onclick=\"modifier_description_cohort($cohort_num_voir);\">
								</div>";
						} else {
							print "<div id=\"id_div_cohorte_description\"  style=\"display:inline;width:100%;\" >
								$description_cohort_voir
								</div>";
						}
						print "<br>".get_translation('CREATED_BY','Créée par')." $firstname_createur $lastname_createur ";
						if ($num_datamart_cohorte==0) {
							print get_translation('ON_THE_ENTIRE_DATAWAREHOUSE',"sur l'ensemble de l'entrepôt")."<br>";
						} else {
							print get_translation('ON_THE_DATAMART','sur le datamart')." $title_datamart<br>";
						}
						print "<br><br>";
						
						
						////////////// /////////////////////////////////////////////////////////////////////////////////////
						print "<div id=\"id_div_liste_user_cohorte\">
							<h3>".get_translation("MANAGE_COHORT_USERS","Gestion des utilisateurs")."</h3>
						<div id=\"id_tableau_liste_user_cohorte\">";
						affiche_liste_user_cohorte($cohort_num_voir,$user_num_session);
						print "</div>";
						if ($autorisation_cohorte_modifier=='ok') {
							print get_translation('ADD_COLLABORATOR','Ajouter un collaborateur')." : 
							
							<select id=\"id_ajouter_select_user\" size=\"5\" class=\"form chosen-select\" onchange=\"ajouter_user_cohorte();\" data-placeholder=\"Choisissez un collaborateur\"><option value=''></option>";
							$sel_var1=oci_parse($dbh,"select  user_num,lastname,firstname from dwh_user order by lastname,firstname ");
							oci_execute($sel_var1);
							while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
								$user_num=$r['USER_NUM'];
								$lastname=$r['LASTNAME'];
								$firstname=$r['FIRSTNAME'];
								print "<option  value=\"$user_num\" id=\"id_ajouter_select_num_user_$user_num\">$lastname $firstname</option>";
							}
							print "</select>";
						}
						print "</div>";
						
						
						///////////// QUERIES /////////////////////////////////////////////////////////////////////////////////
						print "<div id=\"id_tableau_liste_queries_cohort\">
							<h3>".get_translation("QUERIES_ON_COHORT","Les requêtes sur la cohorte")."</h3>
							<i><a href=\"moteur.php?action=rechercher_dans_cohorte&cohort_num=$cohort_num_voir\" target=\"_blank\">Rechercher sur la cohorte <img src=\"images/search.png\" border=\"0\" style=\"vertical-align:middle\"></a></i><br><br>
							<i>Pour ajouter des requêtes ici, il suffit de les <img src=\"images/pin.png\"> dans l'historique du moteur de recherche</i><br><br>
						";
						$lister_requete_cohort= lister_requete_cohort ($cohort_num_voir);
						print "$lister_requete_cohort</div>";
							
						
					print "</div>";
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					/////////////////////////////// PATIENT INCLUDED ////////////////////////////////////////////////////////////////
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					print "<div id=\"id_div_cohorte_patient_inclu\" class=\"div_result\" style=\"display:none;width:100%;\" >
						<br><br>
						<div id=\"id_liste_patient_cohorte_encours1\" style=\"width:100%;\" ></div>";
					print "</div>";
					
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					////////////////////////////// PATIENT EXCLUDED /////////////////////////////////////////////////////////////////
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					
					print "<div id=\"id_div_cohorte_patient_exclu\" class=\"div_result\" style=\"display:none;width:100%;\" >";
						print "<br><br><div id=\"id_liste_patient_cohorte_encours0\" style=\"width:100%;\" ></div>";
					print "</div>";
					
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					/////////////////////////////// PATIENT WAITING LIST //////////////////////////////////////////////////////////////////
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					print "<div id=\"id_div_cohorte_patient_doute\" class=\"div_result\" style=\"display:none;width:100%;\" >";
						print "<br><br><div id=\"id_liste_patient_cohorte_encours2\" style=\"width:100%;\" ></div>";
					print "</div>";
					
					
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////// IMPORTED PATIENTS ////////////////////////////////////////////////////////////////
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					print "<div id=\"id_div_cohorte_patient_import\" class=\"div_result\" style=\"display:none;width:100%;\" >";
						print get_translation('MANUAL_IMPORT_PATIENTS_COHORT',"Pour importer des patients")."<br>";
						print get_translation('MANUAL_IMPORT_PATIENTS_COHORT_COPY_PAST_LIST_OF_PATIENTS',"Copier coller une liste de patients")."<br>";
						print get_translation('MANUAL_IMPORT_PATIENTS_COHORT_RESPECT_ORDER_COLUMNS',"Respecter l'ordre des colonnes")." :<br>";
						print get_translation('MANUAL_IMPORT_PATIENTS_COHORT_LIST_COLONNE',"IPP;LASTNAME;FIRSTNAME;DATE DE NAISSANCE")."<br>";
						print get_translation('MANUAL_IMPORT_PATIENTS_COHORT_SEPARATOR',"Utiliser comme séparateur le \";\" ou la \",\" ou la tabulation (un copier coller depuis Excel fonctionnera)")."<br>";
						print get_translation('MANUAL_IMPORT_PATIENTS_COHORT_HOSPITALID_OR_PATIENTINFO',"Vous pouvez n'avoir que l'IPP ou que le nom + prenom + date de naissance sans l'IPP")." :<br>";
						print "<pre>;garcelon;nicolas;13/05/2007<br>9999999<br></pre><br>";
						print "
						<table border=\"0\">
							<tr><td style=\"vertical-align:top;\">
							<textarea id=\"id_textarea_importer_patient\"  style=\"width:500px;\" rows=\"10\"></textarea>
							</td><td style=\"vertical-align:top;\"><div id=\"id_journal_import_patient\" style=\"overflow: auto;width:500px;height:177px;font-family:monospace;font-size:10px;\"></div></td></tr>
						</table>
						<br>
						<!--<input type=\"button\" value=\"importer ces patients\" onclick=\"importer_patient_cohorte($cohort_num_voir,'importer');\"> -->
						<input type=\"button\" value=\"Inclure ces patients\" onclick=\"importer_patient_cohorte($cohort_num_voir,'inclure');\"> 
						<input type=\"button\" value=\"Exclure ces patients\" onclick=\"importer_patient_cohorte($cohort_num_voir,'exclure');\">";
						
						print "<br><br><div id=\"id_liste_patient_cohorte_encours3\" style=\"width:100%;\" ></div>";
					print "</div>";
					
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					////////////////////////// COMMENTS ////////////////////////////////////////////////////////////////////
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					print "<div id=\"id_div_cohorte_patient_commentaire\" class=\"div_result\" style=\"display:none;width:100%;\" >";
						print "<br><br><div id=\"id_lister_tous_les_commentaires_patient_cohorte\" style=\"width:100%;\" >";
							print lister_tous_les_commentaires_patient_cohorte($cohort_num_voir);
						print "</div>";
					print "</div>";
					
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					/////////////////////// CONCEPTS /////////////////////////////////////////////////////////////////////
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					print "<div id=\"id_div_cohorte_concepts\" class=\"div_result\" style=\"display:none;\" ></div>";
							
					
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					/////////////////////// SIMILAR PATIENTS  /////////////////////////////////////////////////////////////////////
					///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					?>	
					
					<div id="id_div_cohorte_patient_similaire" class="div_result" style="display:none;" >
						<div  style="display:none;">
							<h2><? print get_translation('FIRST_STEP','1ère étape'); ?> : </h2>
							<input type="radio" name="reduire_entrepot_sous_population" value="1" id="id_radio_reduire_entrepot_sous_population_oui"> <? print get_translation('SIMILARITY_COHORT_REDUCE_POPULATION',"Réduire la population totale à une quantité raisonnable (<10 000 patients)"); ?> :<br> 
							 <? print get_translation('SIMILARITY_COHORT_MODIFY_QUERY_TO_REDUCE_NB_PATIENT',"Modifier la requête pour réduire le nombre de patients"); ?> <br>
							<textarea id="id_textarea_similarite_cohorte_requete" cols="70" rows="3"></textarea><br>
							<? print get_translation('COUNT_PATIENT_NUMBER_SHORT','Nb patients'); ?> : <span id="id_span_precalcul_nb_patient_similarite_cohorte"></span><br>
							<input type="button" onclick="precalcul_nb_patient_similarite_cohorte('<? print $cohort_num_voir; ?>');" value="<? print get_translation('PRECOMPUTE','Pré-calculer'); ?>">
							<br><br>
						</div>
						<h3><? print get_translation('OPTIONS','Options'); ?></h3>
						<? print get_translation('DEFAULT_EXCLUDE_PATIENTS_FROM_SIMILARITY','Par défaut, les patients inclus ou exclus dans la cohorte ne sont pas pris en compte pour le calcul de similarité'); ?>
						<br>
						<? print get_translation('COMPUTE_SIMILARITY_COHORTE_IMPORTED_PATIENTS_ONLY','Calculer la similarité uniquement sur les patients importés dans la cohorte'); ?> : <input type="checkbox" value="1" id="id_checkbox_similarite_sur_patients_importes"><br>
						<? print get_translation('EXCLUDE_PATIENTS_INCLUDED_EXCLUDED_FROM_THESE_COHORTS','Exclure les patients inclus ou exclus de ces cohortes');?> : 
				             	   <select  id="id_select_filtre_cohorte_exclue" name="cohorte_exclue[]" class="chosen-select "  data-placeholder="<? print get_translation('CHOOSE_1_N_COHORTS','Choisissez une ou plusieurs cohortes'); ?>" multiple>";
				                           <?    display_user_cohorts_option ($user_num_session,'id_select_filtre_cohorte_exclue'); ?>
				                  </select><br>
							<input type="hidden" id="id_input_nbpatient_limite" value="20">
							<input type="hidden" id="id_input_limite_count_concept_par_patient_num" value="40">
							<? $process_num=get_uniqid(); ?>
							<input type="hidden" id="id_process_num" value="<? print $process_num; ?>">
						<br>
						<input type="button" onclick="calculer_similarite_cohorte('<? print $cohort_num_voir; ?>');" value="calculer">
						<br><br>
						<div id="id_div_cohorte_similarite_cohorte_resultat" style="display:block;" >
						</div>
					</div>
					<?
				} else {
					print get_translation('YOU_CANNOT_SEE_THIS_COHORT',"Vous n'êtes pas autorisé à voir cette cohorte");
				}
			} 
			
			?>
		</div>
	</td>
</tr>

</table>
<script language="javascript">
	jQuery(document).ready(function() {
		jQuery.fn.dataTable.moment('DD/MM/YYYY');
	
	 	jQuery('#id_tableau_liste_cohortes').dataTable( { 
				paging: false,
				"order": [[ 2, "desc" ]],
				   "bInfo" : false,
				   "bDestroy" : true
		} );
		
		<? if ($cohort_num_voir != '') { ?>
			liste_patient_cohorte_encours(<? print "$cohort_num_voir"; ?> ,0) ;
			liste_patient_cohorte_encours(<? print "$cohort_num_voir"; ?> ,1) ;
			liste_patient_cohorte_encours(<? print "$cohort_num_voir"; ?> ,2) ;
			liste_patient_cohorte_encours(<? print "$cohort_num_voir"; ?> ,3) ;
		<? } ?>
		
	} );
	
	
</script>


<? save_log_page($user_num_session,'my_cohorts'); ?>
<? include "foot.php"; ?>