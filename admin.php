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
?>
<?
include "head.php";
include "menu.php";
include_once "fonctions_admin.php";

if ($_SESSION['dwh_droit_admin']!='ok') {
	print "<br><br><br>Vous n'avez pas les droits d'administration<br>";
	include "foot.php"; 
	exit;
}
session_write_close();

if (count($tableau_admin_features)==0) {
	$tableau_admin_features=array('admin_department','admin_profil','admin_user','opposition','admin_concepts','admin_thesaurus','admin_etl','analyse_requete','admin_outils','admin_cgu','admin_actu','admin_datamart');
} 


$class_admin_department='admin_menu';
$class_admin_profil='admin_menu';
$class_admin_user='admin_menu';
$class_opposition='admin_menu';
$class_admin_concepts='admin_menu';
$class_admin_thesaurus='admin_menu';
$class_admin_etl='admin_menu';
$class_analyse_requete='admin_menu';
$class_admin_outils='admin_menu';
$class_admin_cgu='admin_menu';
$class_admin_actu='admin_menu';
if ($_GET['action']=='admin_department') {
	$class_admin_department='admin_menu_activated';
}
if ($_GET['action']=='admin_profil') {
	$class_admin_profil='admin_menu_activated';
}
if ($_GET['action']=='admin_user') {
	$class_admin_user='admin_menu_activated';
}
if ($_GET['action']=='opposition') {
	$class_opposition='admin_menu_activated';
}
if ($_GET['action']=='admin_concepts') {
	$class_admin_concepts='admin_menu_activated';
}
if ($_GET['action']=='admin_thesaurus') {
	$class_admin_thesaurus='admin_menu_activated';
}
if ($_GET['action']=='admin_etl') {
	$class_admin_etl='admin_menu_activated';
}
if ($_GET['action']=='analyse_requete') {
	$class_analyse_requete='admin_menu_activated';
}
if ($_GET['action']=='admin_outils') {
	$class_admin_outils='admin_menu_activated';
}
if ($_GET['action']=='admin_cgu') {
	$class_admin_cgu='admin_menu_activated';
}
if ($_GET['action']=='admin_actu') {
	$class_admin_actu='admin_menu_activated';
}

?>
<script src="javascript_admin.js?<? print "v=$date_today_unique"; ?>" type="text/javascript"></script>
<style>
.admin_menu {
	padding:0px 5px;
	background-color: #5F6589;
}
.admin_menu:hover {
	background-color: #D1D0D5;
}
.admin_menu_activated {
	padding:0px 5px;
	background-color: #D1D0D5;
}
</style>

<div id="id_sous_menu_flottant"  align="center"  >
	<table id="id_tableau_sous_menu_flottant" width="100%" height="25" border="0" cellspacing="0" cellpadding="0" bgcolor="#5F6589" style="border-top:0px white solid;border-bottom:1px white solid;">
		<tr>
		<? if (in_array('admin_department',$tableau_admin_features)) {?>
			<td nowrap="nowrap" class="<? print $class_admin_department; ?>"><a class="connexion" href="admin.php?action=admin_department" nowrap=nowrap><? print get_translation('THE_HOSPITAL_DEPARTMENTS','Les services'); ?></a></td>
			<td class="connexion" width="1"> | </td>
		<? } ?>
		<? if (in_array('admin_profil',$tableau_admin_features)) {?>
			<td nowrap="nowrap" class="<? print $class_admin_profil; ?>"><a class="connexion" href="admin.php?action=admin_profil" nowrap=nowrap><? print get_translation('THE_PROFILES','Les profils'); ?></a></td>
			<td class="connexion" width="1"> | </td>
		<? } ?>
			
		<? if (in_array('admin_user',$tableau_admin_features)) {?>
			<td nowrap="nowrap" class="<? print $class_admin_user; ?>"><a class="connexion" href="admin.php?action=admin_user" nowrap=nowrap><? print get_translation('THE_USERS','Les utilisateurs'); ?></a></td>
			<td class="connexion" width="1"> | </td>
		<? } ?>
		<? if (in_array('admin_datamart',$tableau_admin_features)) {?>
<!--
			<td nowrap="nowrap"><a class="connexion" href="admin.php?action=admin_datamart" nowrap=nowrap><? print get_translation('THE_DATAMART','Les datamart'); ?></a></td>
			<td class="connexion" width="1"> | </td>
-->
		<? } ?>
		<? if (in_array('opposition',$tableau_admin_features)) {?>
			<td nowrap="nowrap" class="<? print $class_opposition; ?>"><a class="connexion" href="admin.php?action=opposition" nowrap=nowrap><? print get_translation('OPPOSITION','Opposition'); ?></a></td>
			<td class="connexion" width="1"> | </td>

		<? } ?>
		<? if (in_array('admin_concepts',$tableau_admin_features)) {?>
			<td nowrap="nowrap" class="<? print $class_admin_concepts; ?>"><a class="connexion" href="admin.php?action=admin_concepts" nowrap=nowrap><? print get_translation('THE_CONCEPTS','Les concepts'); ?></a></td>
			<td class="connexion" width="1"> | </td>

		<? } ?>
		<? if (in_array('admin_thesaurus',$tableau_admin_features)) {?>
			<td nowrap="nowrap" class="<? print $class_admin_thesaurus; ?>"><a class="connexion" href="admin.php?action=admin_thesaurus" nowrap=nowrap><? print get_translation('THE_THESAURUS','Les thesaurus'); ?></a></td>
			<td class="connexion" width="1"> | </td>
			
		<? } ?>
		<? if (in_array('admin_etl',$tableau_admin_features)) {?>
			<td nowrap="nowrap" class="<? print $class_admin_etl; ?>"><a class="connexion" href="admin.php?action=admin_etl" nowrap=nowrap><? print get_translation('ETL','ETL'); ?></a></td>
			<td class="connexion" width="1"> | </td>
			
		<? } ?>
		<? if (in_array('analyse_requete',$tableau_admin_features)) {?>
  			<td nowrap="nowrap" class="<? print $class_analyse_requete; ?>"><a class="connexion" href="admin.php?action=analyse_requete" nowrap=nowrap><? print get_translation('QUERIES','Requêtes'); ?></a></td>
			<td class="connexion" width="1"> | </td>
			
		<? } ?>
		<? if (in_array('admin_outils',$tableau_admin_features)) {?>
			<td nowrap="nowrap" class="<? print $class_admin_outils; ?>"><a class="connexion" href="admin.php?action=admin_outils" nowrap=nowrap><? print get_translation('THE_TOOLS','Les outils'); ?></a></td>
			<td class="connexion" width="1"> | </td>
			
		<? } ?>
		<? if (in_array('admin_cgu',$tableau_admin_features)) {?>
			<td nowrap="nowrap" class="<? print $class_admin_cgu; ?>"><a class="connexion" href="admin.php?action=admin_cgu" nowrap=nowrap><? print get_translation('CGU','Les CGU'); ?></a></td>
			<td class="connexion" width="1"> | </td>
			
		<? } ?>
		<? if (in_array('admin_actu',$tableau_admin_features)) {?>
			<td nowrap="nowrap" class="<? print $class_admin_actu; ?>"><a class="connexion" href="admin.php?action=admin_actu" nowrap=nowrap><? print get_translation('NEWS','Les actus'); ?></a></td>
			<td class="connexion" width="1"> | </td>
		<? } ?>
			<td width="100%">&nbsp;</td>
		</tr>
	</table>
</div>
<?



////////////////// admin_department //////////////////
if ($_GET['action']=='admin_department') {
?>

	<h2><? print get_translation('HOSPITAL_DEPARTMENTS_USER_MANAGEMENT','Gestion des services et des utilisateurs'); ?></h2><br>
	<div  style="font-size:11px;text-align:left;padding-left:4px;padding-bottom:400px;">
		<? print get_translation('CREATE_HOSPITAL_DEPARTMENT','Créer un service'); ?> : <input type=text size=12 id=id_service_ajouter > <input type=button value="<? print get_translation('CREATE','Créer'); ?>" onclick="ajouter_service();"><br>
		<br>
		<div id="id_div_admin_department">
		
		</div>
	</div>
	<script language=javascript>
		$(document).ready(function() 
		    { 
		    	display_department();
		    } 
		); 
	</script>

<?
}
?>


		
<? if ($_GET['action']=='admin_profil') { ?>
	<?
	$table_user_profil=get_list_user_profil();
	$sel=oci_parse($dbh,"select  distinct document_origin_code from dwh_info_load where document_origin_code is not null order by document_origin_code");
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$document_origin_code=$r['DOCUMENT_ORIGIN_CODE'];
		$tableau_document_origin_code[$document_origin_code]=$document_origin_code;
	}
	?>
		<h1><? print get_translation('USER_PROFILE_LIST','Liste des profils utilisateurs'); ?></h1>
		<div id="id_admin_lister_profil"  class="div_admin">
		
			<h2><? print get_translation('USER_PROFILE_LIST_FEATURES','Fonctionnalités autorisées'); ?></h2>
			<table class="tablefin" id="id_table_liste_profil_global_features">
				<tr>
					<th><? print get_translation('PROFILES','Profils'); ?></th>
					<th><? print get_translation('NB_USERS','Nb Users'); ?></th>
					<?
					foreach ($tableau_user_droit as $right) { 
						print "<th>$right</th>";
					}
					?>
					<th><? print get_translation('DELETE','Suppr'); ?></th>
				</tr>
				<?
					foreach ($table_user_profil as $user_profile) { 
						print get_line_profile_admin ($user_profile,'global_features') ;
					}
				?>
			</table>
			<? print get_translation('ADD_A_PROFILE','Ajouter un profil'); ?> : <input type="text" size="30" id="id_input_new_profil" class="form"><input type="button" value="<? print get_translation('ADD','ajouter'); ?>" onclick="ajouter_nouveau_profil();" class="form">
						
			
			<h2><? print get_translation('USER_PROFILE_BY_PATIENT_FEATURES','Profils utilisateurs / Fonctionnalités sur le dossier patient'); ?></h2>
			<table class="tablefin" id="id_table_liste_profil_patient_features">
				<tr>
					<th><? print get_translation('PROFILES','Profils'); ?></th>
					<?
					foreach ($tableau_patient_droit as $patient_features) {
						print "<th>$patient_features <a href=\"#\" onclick=\"check_all_patient_features('$patient_features');return false;\">all</a></th>";
					}
					?>
					<th><? print get_translation('PROFILES','Profils'); ?></th>
				</tr>
				<?
					foreach ($table_user_profil as $user_profile) { 
						print get_line_profile_admin ($user_profile,'patient_features') ;
					}
				?>
			</table>
					
					
			
			<h2><? print get_translation('USER_PROFILE_BY_DOCUMENT_ORIGIN','Profils utilisateurs / Données autorisées'); ?></h2>
			<table class="tablefin" id="id_table_liste_profil_document_origin_code">
				<tr>
					<th><? print get_translation('PROFILES','Profils'); ?></th>
					<th><? print get_translation('ALL_EVERYTHING','Tout'); ?></th>
					<?
					foreach ($tableau_document_origin_code as $document_origin_code) { 
						print "<th>$document_origin_code</th>";
					}
					?>
					<th><? print get_translation('PROFILES','Profils'); ?></th>
				</tr>
				
				<?
					foreach ($table_user_profil as $user_profile) { 
						print get_line_profile_admin ($user_profile,'document_origin_code') ;
					}
				?>
			</table>
		</div>
	
<? } ?>









<? if ($_GET['action']=='admin_user') { ?>

	<? 
	$table_user_profil=get_list_user_profil(); 
	$table_list_department=get_list_departments('','');
	?>
	<h1><? print get_translation('USERS_ADMINISTRATION','Administration des utilisateurs'); ?></h1>
	<table border="0">
	<tbody>
	<tr>
	<td style="vertical-align:top;">
		<h2 style="cursor:pointer;" onclick="plier_deplier('id_admin_ajouter_user');plier('id_admin_modifier_expiration_date_group');plier('id_admin_ajouter_liste_user');plier('id_admin_modifier_user');"><span id="plus_id_admin_ajouter_user">+</span> <? print get_translation('ADD_USER','Ajouter un utilisateur'); ?></h2>
		<h2 style="cursor:pointer;" onclick="plier_deplier('id_admin_ajouter_liste_user');plier('id_admin_modifier_expiration_date_group');plier('id_admin_ajouter_user');plier('id_admin_modifier_user');"><span id="plus_id_admin_ajouter_liste_user">+</span> <? print get_translation('ADD_USER_LIST',"Ajouter une liste d'utilisateurs"); ?><h2>
		<h2 style="cursor:pointer;" onclick="plier_deplier('id_admin_modifier_user');plier('id_admin_modifier_expiration_date_group');plier('id_admin_ajouter_liste_user');plier('id_admin_ajouter_user');"><span id="plus_id_admin_modifier_user">+</span> <? print get_translation('USER_MODIFY','Modifier un utilisateur'); ?></h2>
		<h2 style="cursor:pointer;" onclick="plier_deplier('id_admin_modifier_expiration_date_group');plier('id_admin_modifier_user');plier('id_admin_ajouter_liste_user');plier('id_admin_ajouter_user');"><span id="plus_id_admin_enddate_user">+</span> <? print get_translation('USER_MODIFY_ENDDATE',"Ajouter une date d'expiration à une liste d'utilisateurs"); ?></h2>
	</td>
	<td style="vertical-align:top;">
		<div id="id_admin_ajouter_user"  class="div_admin" style="display:none;">
			<h3><? print get_translation('ADD_USER','Ajouter un utilisateur'); ?></h3>
			<table>
				<tr><td class="question_user"><? print get_translation('SEARCH_IN_HOSPITAL_DIRECTORY',"Rechercher dans l'annuaire de l'hôpital"); ?> : </td><td>
				<div class="ui-widget" style="padding-left: 0px;width:260px;font-size:10px;">
					<span class="ui-helper-hidden-accessible" aria-live="polite" role="status"></span>
					<span class="ui-helper-hidden-accessible" role="status" aria-live="polite"></span>
					 <input id="id_champs_recherche_rapide_utilisateur" class="form ui-autocomplete-input" type="text" onclick="if (this.value=='<? print get_translation('LOGIN','Identifiant'); ?>') {this.value='';}" size="15" name="LOGIN" value="<? print get_translation('LOGIN','Identifiant'); ?>" style="font-size:10px;" autocomplete="off">
				 </div>
				 </td></tr>
				 
				<tr><td class="question_user"><? print get_translation('LOGIN','Identifiant'); ?> : </td><td><input type="text" size="30" id="id_ajouter_login_user" class="form"></td></tr>
				<tr><td class="question_user"><? print get_translation('LASTNAME','Nom'); ?> : </td><td><input type="text" size="30" id="id_ajouter_lastname_user" class="form"></td></tr>
				<tr><td class="question_user"><? print get_translation('FIRSTNAME','Prénom'); ?> : </td><td><input type="text" size="30" id="id_ajouter_firstname_user" class="form"></td></tr>
  				<tr><td class="question_user"><? print get_translation('EMAIL','Mail'); ?> : </td><td><input type="text" size="50" id="id_ajouter_mail_user" class="form"></td></tr>
				<tr><td class="question_user"><? print get_translation('EXPIRATION_DATE','Date expiration'); ?> : </td><td><input type="text" size="11" id="id_ajouter_expiration_date_user" class="form"> (dd/mm/yyyy)</td></tr>
				<tr><td class="question_user"><? print get_translation('PASSWORD','Mot de passe'); ?><br><? print get_translation('UNIQUEMENT_SI_VOUS_VOULEZ_LE_MODIFIER','(uniquement si vous voulez le modifier)'); ?> : </td><td><input type="text" size="50" id="id_ajouter_passwd_user" class="form"></td></tr>
				<tr><td style="vertical-align:top;" class="question_user"><? print get_translation('PROFILES','Profils'); ?> : </td><td>
				<?
				foreach ($table_user_profil as $user_profile) { 
					print "<input type=\"checkbox\" class=input_ajouter_user_profile id=\"id_user_profile_$user_profile\" value=\"$user_profile\">$user_profile<br>";
				}
				?>
				<tr><td style="vertical-align:top;" class="question_user"><? print get_translation('HOSPITAL_DEPARTMENT','Service'); ?> : </td><td>
					<select id="id_ajouter_select_service_multiple" multiple size="5" class="form chosen-select"><option value=''></option>
					<?
						foreach ($table_list_department as $department) {
							$department_num=$department['department_num'];
							$department_str=$department['department_str'];
							print "<option  value=\"$department_num\" class=select_ajouter_select_department_num_multiple id=\"id_ajouter_select_department_num_multiple_$department_num\">$department_str</option>";
						}
					?>
					</select>
				</td></tr>
				
			</table>
			<input type="button" onclick="ajouter_user_admin();" class="form" value="<? print get_translation('ADD','ajouter'); ?>">
			<div id="id_div_resultat_ajouter_user"></div>
			<br>
		</div>
		<div id="id_admin_ajouter_liste_user"  class="div_admin" style="display:none;">
			<h3><? print get_translation('ADD_USER_LIST',"Ajouter une liste d'utilisateurs"); ?></h3>
			
			<table>
				<tr><td style="vertical-align:top;" class="question_user"><? print get_translation('LOGIN','Identifiant'); ?>;<? print get_translation('LASTNAME','Nom'); ?>;<? print get_translation('FIRSTNAME','Prénom'); ?>;<? print get_translation('EMAIL','Email'); ?>  :<br><i><? print get_translation('ONLY_LOGIN_IF_HOSPITAL_ACCOUNT',"Uniquement le login si c'est un compte hospitalier"); ?></i> </td><td>
					<textarea id="id_textarea_list_user" rows="6" cols="60" class="form"></textarea>
				</td></tr>
				<tr><td style="vertical-align:top;" class="question_user"><? print get_translation('PROFILES','Profils'); ?> : </td><td>
				<?
					foreach ($table_user_profil as $user_profile) { 
						print "<input type=\"checkbox\" class=checkbox_liste_user_profile id=\"id_liste_user_profile_$user_profile\" value=\"$user_profile\">$user_profile<br>";
					}
				?>
				</td></tr>
				<tr><td style="vertical-align:top;" class="question_user"><? print get_translation('HOSPITAL_DEPARTMENT','Service'); ?> : </td><td>
					<select id="id_select_service" class="form chosen-select"><option value=''></option>
					<?
						foreach ($table_list_department as $department) {
							$department_num=$department['department_num'];
							$department_str=$department['department_str'];
							print "<option  value=\"$department_num\">$department_str</option>";
						}
					?>
					</select>
				</td></tr>
			</table>
			<input type="button" onclick="ajouter_liste_user_admin();" class="form" value="<? print get_translation('ADD','ajouter'); ?>">
			<div id="id_div_resultat_ajouter_liste_user"></div>
			
		</div>
		<div id="id_admin_modifier_user" class="div_admin" style="display:none;">
			
			<h3><? print get_translation('USER_MODIFY',"Modifier un utilisateurs"); ?></h3>
			<table>
				<tr><td class="question_user"><? print get_translation('SEARCH_IN_INTERNAL_DIRECTORY',"Rechercher dans l'annuaire interne"); ?> : </td><td>
				<div class="ui-widget" style="padding-left: 0px;width:260px;font-size:10px;">
					<span class="ui-helper-hidden-accessible" aria-live="polite" role="status"></span>
					<span class="ui-helper-hidden-accessible" role="status" aria-live="polite"></span>
					 <input id="id_champs_recherche_annuaire_interne" class="form ui-autocomplete-input" type="text" onclick="if (this.value=='<? print get_translation('LOGIN','Identifiant'); ?>') {this.value='';}" size="15" name="LOGIN" value="<? print get_translation('LOGIN','Identifiant'); ?>" style="font-size:10px;" autocomplete="off">
				 </div>
				 </td></tr>
				 
				<tr><td class="question_user"><? print get_translation('LOGIN','Identifiant'); ?> : </td><td><input type="text" size="30" id="id_modifier_login_user" class="form"></td></tr>
				<tr><td class="question_user"><? print get_translation('LASTNAME','Nom'); ?> : </td><td><input type="text" size="30" id="id_modifier_lastname_user" class="form"></td></tr>
				<tr><td class="question_user"><? print get_translation('FIRSTNAME','Prénom'); ?> : </td><td><input type="text" size="30" id="id_modifier_firstname_user" class="form"></td></tr>
				<tr><td class="question_user"><? print get_translation('EMAIL','Mail'); ?> : </td><td><input type="text" size="50" id="id_modifier_mail_user" class="form"></td></tr>
				<tr><td class="question_user"><? print get_translation('EXPIRATION_DATE','Date expiration'); ?> : </td><td><input type="text" size="11" id="id_modifier_expiration_date_user" class="form"> (dd/mm/yyyy)</td></tr>
				<tr><td class="question_user"><? print get_translation('PASSWORD','Mot de passe'); ?><br><? print get_translation('UNIQUEMENT_SI_VOUS_VOULEZ_LE_MODIFIER','(uniquement si vous voulez le modifier)'); ?> : </td><td><input type="text" size="50" id="id_modifier_passwd_user" class="form"></td></tr>
				<tr><td style="vertical-align:top;" class="question_user"><? print get_translation('PROFILES','Profils'); ?> : </td><td>
				<?
					foreach ($table_user_profil as $user_profile) { 
						print "<input type=\"checkbox\" class=\"input_modifier_user_profile\" id=\"id_modifier_user_profile_$user_profile\" value=\"$user_profile\">$user_profile<br>";
					}
				?>
				<tr><td style="vertical-align:top;" class="question_user"><? print get_translation('HOSPITAL_DEPARTMENT','Service'); ?> : </td><td>
					<select id="id_modifier_select_service_multiple" multiple size="5" class="form chosen-select"><option value=''></option>
					<?
						foreach ($table_list_department as $department) {
							$department_num=$department['department_num'];
							$department_str=$department['department_str'];
							print "<option  value=\"$department_num\" class=\"select_modifier_select_department_num_multiple\" id=\"id_modifier_select_department_num_multiple_$department_num\">$department_str</option>";
						}
					?>
					</select>
				</td></tr>
				 
			</table>
			<input type="hidden" id="id_modifier_num_user" class="form">
			<input type="button" onclick="modifier_user_admin();" class="form" value="<? print get_translation('MODIFY','Modifier'); ?>">
			
			<div id="id_div_resultat_modifier_user"></div>
			
		</div>
		
		<div id="id_admin_modifier_expiration_date_group"  class="div_admin" style="display:none;">
			<h3><? print get_translation('ADD_USER_LIST_EXPIRATION_DATE',"Ajouter une date d'expiration aux utilisateurs"); ?></h3>
			
			<table>
				<tr><td style="vertical-align:top;" class="question_user"><? print get_translation('LOGIN_LIST','Liste des identifiants hospitaliers'); ?> </td><td>
					<textarea id="id_textarea_list_user_expiration_date_group" rows="6" cols="60" class="form"></textarea>
				</td></tr>
				<tr><td class="question_user"><? print get_translation('EXPIRATION_DATE','Date expiration'); ?> : </td><td><input type="text" size="11" id="id_modifier_expiration_date_group" class="form"> (dd/mm/yyyy)</td></tr>
				
			</table>
			<input type="button" onclick="add_expiration_date_group_admin();" class="form" value="<? print get_translation('ADD','ajouter'); ?>">
			<div id="id_div_resultat_add_expiration_date_group"></div>
		</div>
	</td>
	</tr>
	</tbody>
	</table>

	<div id="id_admin_les_user" class="div_admin">
		<h1><? print get_translation('THE_USERS','Les utilisateurs'); ?></h1>
		<div id="id_div_tableau_users">
		</div>
		<script language=javascript>
			$(document).ready(function() 
			    { 
			    	rafraichir_tableau_users();
			       // $("#id_tableau_users").dataTable(); 
			    } 
			); 
		</script>
		
	</div>

<? } ?>	









<?
///////////////// ETL //////////////////
if ($_POST['action']=='sauver_document_origin_str') {

	$sel=oci_parse($dbh,"delete from dwh_admin_document_origin");
	oci_execute($sel);
	
	$sel=oci_parse($dbh,"select  distinct document_origin_code from dwh_info_load where document_origin_code is not null order by document_origin_code");
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$document_origin_code=$r['DOCUMENT_ORIGIN_CODE'];
		$document_origin_code_champs=preg_replace("/[^a-z0-9]/i","_",$document_origin_code);
		$document_origin_str=$_POST["document_origin_code-$document_origin_code_champs"];
		$tableau_global_document_origin_code[$document_origin_code]=$document_origin_str;
		$document_origin_str=nettoyer_pour_inserer(urldecode($document_origin_str));
		$sel_var1=oci_parse($dbh,"insert into dwh_admin_document_origin (document_origin_code,document_origin_str) values ('$document_origin_code','$document_origin_str')");
		oci_execute($sel_var1);
	}
	$_GET['action']='admin_etl';
}
if ($_GET['action']=='admin_etl') {
?>
	<div id="id_admin_etl"  class="div_admin">
		<h1><? print get_translation('ETL','ETL'); ?></h1>
		
<?
	$nb_jours=200;
	print "<br><h3 style=\"color: #333333;fill: #333333;font-size: 18px;font-family:Lucida Sans Unicode;font-weight:normal;\">".get_translation('DOCUMENT_ORIGINS_DETAILED','Libellé des origines des document')."</h3>
	
	<form method=post action=admin.php>
	<table border=0>";
	$sel_var1=oci_parse($dbh,"select  distinct document_origin_code from dwh_info_load where document_origin_code is not null order by document_origin_code");
	oci_execute($sel_var1);
	while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$document_origin_code=$r['DOCUMENT_ORIGIN_CODE'];
		$document_origin_str=$tableau_global_document_origin_code[$document_origin_code];
		$document_origin_code_champs=preg_replace("/[^a-z0-9]/i","_",$document_origin_code);
		print "<tr><td>$document_origin_code</td><td><input type=\"text\" size=\"50\" class=\"input_text\" name=\"document_origin_code-$document_origin_code_champs\" value=\"$document_origin_str\"></td></tr>";
	}
	print "</table><input type=submit value=".get_translation('SAVE','sauver')."><input type=hidden name=action value=sauver_document_origin_str></form><br><br>";



	// AFFICHAGE nb documents au cours du temps ///
	afficher_etat_entrepot('document_origin_code_an_mois_presence','1000px','','','');


	// AFFICHAGE EXECUTION DES SCRIPTS  ///
	$tableau_script_date=array();
	$tableau_script=array();
	$sel_var1=oci_parse($dbh,"select  script,  to_char(last_execution_date,'DD/MM/YYYY') as char_last_execution_date,commentary,count_document from dwh_etl_script order by script,last_execution_date asc ");
	oci_execute($sel_var1);
	while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$script=$r['SCRIPT'];
		$char_last_execution_date=$r['CHAR_LAST_EXECUTION_DATE'];
		$commentary=$r['COMMENTARY'];
		$count_document=$r['COUNT_DOCUMENT'];
		if ($count_document=='') {
			$count_document='#';
		}
		$tableau_script_date[$script][$char_last_execution_date]="$count_document - $commentary";
		$tableau_script[$script]='ok';
	}
	
	$sel_var1=oci_parse($dbh,"select to_char(max(last_execution_date)-$nb_jours,'DD/MM/YYYY') as min_char_last_execution_date from dwh_etl_script");
	oci_execute($sel_var1);
	$r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC);
	$min_char_last_execution_date=$r['MIN_CHAR_LAST_EXECUTION_DATE'];
	
	print "<br><h3 style=\"color: #333333;fill: #333333;font-size: 18px;font-family:Lucida Sans Unicode;font-weight:normal;\">".get_translation('THE_EXECUTED_SCRIPTS','Les scripts exécutés')."</h3>";
	print "<table cellspacing=0 cellpadding=0 style=\"border: 1px black solid; border-collapse: collapse;\">";
	foreach ($tableau_script as $scriptcommentaire => $ok) {
		list($script,$commentary)=explode(';',$scriptcommentaire);
		print "<tr style=\"border: 1px solid black;border-collapse: collapse;\" onmouseover=\"this.style.backgroundColor='#cccccc';\" onmouseout=\"this.style.backgroundColor='transparent';\"><th style=\"border: 1px solid black;border-collapse: collapse;text-align:left;\" >$script</th><td>$commentary</td>";
		
		$sel_var1=oci_parse($dbh,"select to_char(level + to_date('$min_char_last_execution_date', 'DD/MM/YYYY') - 1,'DD/MM/YYYY') as jour from dual connect by level < sysdate- to_date('$min_char_last_execution_date', 'DD/MM/YYYY') + 2");
		oci_execute($sel_var1);
		while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$jour=$r['JOUR'];
			$nb_doc=$tableau_script_date[$scriptcommentaire][$jour];
			if ($nb_doc!='') {
				print"<td style=\"width:3px;border: 1px solid black;border-collapse: collapse;background-color:red;cursor:pointer;\" alt=\"$jour - $nb_doc enregistrements\" title=\"$jour - $nb_doc enregistrements\"></td>";
			} else {
				$color='';
				print"<td style=\"width:3px;border: 1px solid black;border-collapse: collapse;\"></td>";
			}	
		}
		print "</tr>";
	}
	print "</table>";



	// AFFICHAGE NB DE DOCUMENTS INTEGRES SUR 31 DERNIERS JOURS ///
	$nb_jours=20;
	print "<br><br><h3>".get_translation('NB_DOCUMENTS_INSERTED_LAST_DAYS','Les documents insérés sur ses N derniers jours')."</h3>";
	print get_translation('LAST_N_DAYS','N derniers jours')."  <input type=\"text\" size=\"2\" class=\"form\" value=\"$nb_jours\" id=\"id_calculate_nb_insert_nb_jours\"> 
	".get_translation('BY_DATE_INSERT_OR_DATE_DOCUMENT',"Distribution par ")." <select id=\"id_select_type_distribution\"><option value=\"upload_id\">".get_translation('INSERT_DATE',"Date d'insertion")."</option><option value=\"document_date\">".get_translation('DOCUMENT_DATE',"Date du document")."</option></select>
	<input type=\"button\" value=\"ok\" onclick=\"calculate_nb_insert()\">
	<div id=\"id_calculate_nb_insert\"></div>
	<script language=\"javascript\">
		$(document).ready(function() { 
		        calculate_nb_insert(); 
		}); 
	</script>";



	// AFFICHAGE NB DE DOCUMENT PRODUITS PAR MOIS ///
	$sel_var1=oci_parse($dbh,"select to_char(min(last_execution_date),'DD/MM/YYYY') as min_char_last_execution_date from dwh_etl_script");
	oci_execute($sel_var1);
	$r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC);
	$min_char_last_execution_date=$r['MIN_CHAR_LAST_EXECUTION_DATE'];
	
	$sel_var1=oci_parse($dbh,"select year,month-1 as month from dwh_info_load where  year is not null and year>1995  and month is not null order by year asc,month asc");
	oci_execute($sel_var1);
	$r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC);
	$annee_min=$r['YEAR'];
	$mois_min=$r['MONTH'];
	$an_mois_min="$annee_min,$mois_min";
	
	$sel_var1=oci_parse($dbh,"select year,month-1 as month from dwh_info_load where  year is not null and year>1995  and month is not null order by year desc,month desc");
	oci_execute($sel_var1);
	$r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC);
	$annee_max=$r['YEAR'];
	$mois_max=$r['MONTH'];
	$an_mois_max="$annee_max,$mois_max";
	
	
	print "<br><h3 style=\"color: #333333;fill: #333333;font-size: 18px;font-family:Lucida Sans Unicode;font-weight:normal;\">".get_translation('DOCUMENTS_NUMBER_OVER_TIME','Nombre de documents au cours du temps')."</h3>";
	afficher_etat_entrepot('document_origin_code_an_mois','1000px','','','');
	
	foreach ($tableau_global_document_origin_code as $document_origin_code => $document_origin_str) {
		print "<h2 style=\"cursor:pointer\" onclick=\"plier_deplier('id_nb_document_temps_$document_origin_code');\">+ $document_origin_str</h2>";
		print "<div id=\"id_nb_document_temps_$document_origin_code\" style=\"display:none;\">";
		 afficher_etat_entrepot('document_origin_code_an_mois_unitaire','1000px',$document_origin_code,$an_mois_min,$an_mois_max);
		 print "</div>";
	}
?>
	</div><br><br>
<?
}
?>



<?
///////////////// COHORTE //////////////////
if ($_GET['action']=='cohorte') {
?>


<?
}
?>

<?
///////////////// Analyse des requetes des users  //////////////////
if ($_GET['action']=='analyse_requete') {
?>

	<h2><? print get_translation('USERS_QUERIES_ANALYSIS','Analyse des requêtes des utilisateurs'); ?></h2><br>
	<div  style="font-size:11px;text-align:left;padding-left:4px;padding-bottom:400px;">
		<table border=1 id="id_table_groupe_utilisateur" class="tablefin">
			<thead>
				<th>
					<? print get_translation('DATE','Date'); ?>
				</th>
				<th>
					<? print get_translation('HOSPITAL_DEPARTMENT','Service'); ?>
				</th>
				<th>
					<? print get_translation('PROFILES','Profils'); ?>
				</th>
				<th>
					<? print get_translation('USERS','Utilisateurs'); ?>
				</th>
				<th>
					<? print get_translation('EXECUTE_QUERY','Executer la requête'); ?>
				</th>
				<th>
					<? print get_translation('SUMMARY_SEARCH_QUERY','Requete en clair'); ?>
				</th>
			</thead>
			<tbody>
			<?
				$req="select query_num, user_num, title_query, query_type,  xml_query,to_char(query_date,'DD/MM/YYYY HH24:MI') as char_date_requete  from dwh_query where user_num !=1 and query_date>sysdate-200 order by query_date desc ";
				$sel = oci_parse($dbh,$req);
				oci_execute($sel);
				while ($ligne = oci_fetch_array($sel)) {
					$query_num = $ligne['QUERY_NUM'];
					$user_num = $ligne['USER_NUM'];
					$title_query = $ligne['TITLE_QUERY'];
					$query_type = $ligne['QUERY_TYPE'];
					$xml_query = $ligne['XML_QUERY']->load();
					$char_date_requete = $ligne['CHAR_DATE_REQUETE'];
					
					$readable_query=readable_query ($xml_query) ;
					$tableau_user=array();
					$tableau_user_info=get_user_info ($user_num) ;
					print "<tr>";
					print "<td>$char_date_requete</td>";
					print "<td>".$tableau_user_info['liste_libelle_service']."</td>";
					print "<td>".$tableau_user_info['liste_user_profile']."</td>";
					print "<td>".$tableau_user_info['lastname']." ".$tableau_user_info['firstname']."</td>";
					print "<td><a href=\"moteur.php?action=preremplir_requete&query_num=$query_num\" target=\"_blank\"> <img src=\"images/search.png\" style=\"cursor:pointer;vertical-align:middle\" border=\"0\"></a>";
					print "<td>$readable_query</td>";
				}
			?>
			</tbody>
		</table>
	</div>

<?
}
?>


<?
///////////////// OPPOSITIONS //////////////////
if ($_GET['action']=='opposition') {

?>

	<div id="id_admin_ajouter_opposition"  class="div_admin">
		<h1><? print get_translation('PATIENT_OPPOSITION',"Ajouter l'opposition d'un patient"); ?></h1>
		<i><? print get_translation('AUTO_DELETE_DOCUMENT',"Tous les documents seront automatiquement supprimés de l'entrepôt"); ?></i>
		<table>
			<tr><td class="question_user"><? print get_translation('INDICATE_HOSPITAL_PATIENT_ID',"Précisez l'IPP du patient"); ?> : </td><td><input type="text" size="30" id="id_opposition_term" class="form"></td></tr>
		</table>
		<input type="button" onclick="affiche_patient_opposition();" class="form" value="<? print get_translation('RESEARCH','Rechercher'); ?>">
		<div id="id_div_resultat_opposition_list"></div>
		<br>
	</div>


	<div id="id_admin_lister_opposition"  class="div_admin">
		<h1><? print get_translation('LIST_PATIENT_OPPOSITION',"Liste des patients opposés"); ?></h1>
		<div id="id_div_list_patients_opposed"></div>
		<br>
	</div>
	
	<script language="javascript">
		$(document).ready(function() { 
		        list_patients_opposed(); 
		}); 
	</script>

<?
}
?>




<?
///////////////// OUTILS //////////////////
if ($_GET['action']=='admin_outils') {
?>

	<div id="id_admin_ajouter_outil"  class="div_admin">
		<h1><? print get_translation('EXTERNAL_TOOL_ADD','Ajouter un outil externe'); ?></h1>
		<i><? print get_translation('AUTO_ADD_URL_TOKEN',"Il sera automatiquement ajouté à l'url le token généré par l'API DrWH"); ?></i>
		<table>
			<tr><td class="question_user"><? print get_translation('TITLE','Titre'); ?> : </td><td><input type="text" size="50" id="id_ajouter_outil_titre" class="form"></td></tr>
			<tr><td class="question_user"><? print get_translation('AUTHORS','Authors'); ?> : </td><td><input type="text" size="100" id="id_ajouter_outil_authors" class="form"></td></tr>
			<tr><td class="question_user"><? print get_translation('DESCRIPTION','Description'); ?> : </td><td><textarea cols="50" rows="5" id="id_ajouter_outil_description" class="form"></textarea></td></tr>
			<tr><td class="question_user"><? print get_translation('URL','URL'); ?> : </td><td><input type="text" size="100" id="id_ajouter_outil_url" class="form"></td></tr>
		</table>
		<input type="button" onclick="insert_outil();" class="form" value="<? print get_translation('ADD','ajouter'); ?>">
		<div id="id_div_resultat_ajouter_outil"></div>
		<br>
	</div>

	<div id="id_admin_ajouter_outil"  class="div_admin">
		<h1><? print get_translation('THE_EXTERNAL_TOOLS','Les outils externes'); ?></h1>
		<div id="id_tableau_liste_outils"  class="div_admin">
			<? admin_lister_outil(); ?>
		</div>
	</div>
	
<?
}
?>



<?
///////////////// DATAMART //////////////////
if ($_GET['action']=='admin_datamart') {
?>
	<?
	$sel_var1=oci_parse($dbh,"select  distinct document_origin_code from dwh_info_load where document_origin_code is not null order by document_origin_code");
	oci_execute($sel_var1);
	while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$document_origin_code=$r['DOCUMENT_ORIGIN_CODE'];
		$tableau_document_origin_code[$document_origin_code]=$document_origin_code;
	}
	?>
	<div id="id_admin_ajouter_datamart"  class="div_admin">
		<h1> <? print get_translation('DATAMART_ADD','Ajouter un datamart'); ?></h1>
		<table>
			<tr><td class="question_user"><? print get_translation('TITLE','Titre'); ?> : </td><td><input type="text" size="50" id="id_ajouter_titre_datamart" class="form"></td></tr>
			<tr><td class="question_user"><? print get_translation('DATE_START','Date de début'); ?> : </td><td><input type="text" size="11" id="id_ajouter_date_start_datamart" class="form"></td></tr>
			<tr><td class="question_user"><? print get_translation('DATE_END','Date de fin'); ?> : </td><td><input type="text" size="11" id="id_ajouter_date_fin_datamart" class="form"></td></tr>
			<tr><td style="vertical-align:top;" class="question_user"> <? print get_translation('USER_RIGHTS','Droits'); ?> : </td><td>
			<?
				foreach ($tableau_datamart_droit as $right) {
					print "<input type=\"checkbox\" id=\"id_ajouter_user_profile_datamart_$right\" class=\"ajouter_user_profile_datamart\" value=\"$right\">$right<br>";
				}
			?>
			</td></tr>
			
			<tr><td style="vertical-align:top;" class="question_user"> <? print get_translation('DOCUMENT_ORIGINS','Origines des documents'); ?> : </td><td>
			<?
				print "<input type=checkbox id=\"id_ajouter_document_origin_code_datamart_tout\" class=\"ajouter_document_origin_code_datamart\" value=\"tout\" >Tout<br>";
				foreach ($tableau_document_origin_code as $document_origin_code) {
					$id_document_origin_code=preg_replace("/[^a-z]/i","_",$document_origin_code);
					print "<input type=\"checkbox\" id=\"id_ajouter_document_origin_code_datamart_$id_document_origin_code\" class=\"ajouter_document_origin_code_datamart\" value=\"$document_origin_code\">$document_origin_code<br>";
				}
			?>
			</td></tr>
			
			<tr><td style="vertical-align:top;" class="question_user"><? print get_translation('USERS','Utilisateurs'); ?> : </td><td>
				<select id="id_ajouter_select_user_multiple" multiple size="5" class="form chosen-select"><option value=''></option>
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
			<tr><td style="vertical-align:top;" class="question_user"><? print get_translation('DESCRIPTION','Description'); ?> : </td><td>
				<textarea id="id_ajouter_description_datamart" cols="50" rows="6" class="form"></textarea>
			</td></tr>
			
		</table>
		<input type="button" onclick="ajouter_datamart();" class="form" value="<? print get_translation('ADD','ajouter'); ?>">
		<div id="id_div_resultat_ajouter_datamart"></div>
		<br>
	</div>
	<div id="id_admin_modifier_datamart"  class="div_admin" style="display:none;">
	</div>
	
	<div id="id_admin_voir_datamart"  class="div_admin">
		<h1><? print get_translation('DATAMARTS_LIST','Liste des datamarts'); ?></h1>
		<table id="id_tableau_liste_datamart">
			<thead>
				<tr>
					<th><? print get_translation('TITLE','Titre'); ?></th>
					<th><? print get_translation('DATE_START','Date de début'); ?></th>
					<th><? print get_translation('DATE_END','Date de fin'); ?></th>
					<th><? print get_translation('USER_RIGHTS','Droits'); ?></th>
					<th><? print get_translation('DOCUMENT_ORIGINS','Origines des documents'); ?></th>
					<th><? print get_translation('USERS','Utilisateurs'); ?></th>
					<th><? print get_translation('DESCRIPTION','Description'); ?></th>
					<th><? print get_translation('COUNT_PATIENT_NUMBER_SHORT','Nb patients'); ?></th>
					<th><? print get_translation('MODIFY_SHORT','M'); ?></th>
					<th><? print get_translation('DELETE_SHORT','S'); ?></th>
				</tr>
			</thead>
			<tbody>
				<? afficher_datamart_ligne(''); ?>
			</tbody>
		</table>
	</div>
	
	
	
	<script language="javascript">
		$(document).ready(function() { 
		        $("#id_tableau_liste_datamart").dataTable(); 
		     
		}); 
	</script>
<?
}
?>










<?
///////////////// CONCEPT //////////////////
if ($_GET['action']=='admin_concepts') {
?>
	<div id="id_admin_concepts"  class="div_admin">
		<h1><? print get_translation('CONCEPTS_MANAGE','Administrer Concepts'); ?></h1>
		<? print get_translation('DISPLAY_CONCEPT_LIST','Afficher liste concepts'); ?> : <input type=text id=id_concept value=''><input type=button onclick="afficher_concepts();" value="<? print get_translation('DISPLAY','Afficher'); ?>"><br>
		<div id="id_div_liste_concepts"></div>
		<div id="id_div_exclure_concepts"></div>

		<br>
		<br>
		<br>
		<? print get_translation('ADD_LABEL_IN_NLP_THESAURUS','Ajouter un libellé dans thesaurus TAL'); ?> : <br>
		<? print get_translation('CODE','Code'); ?> : <input type=text id=id_concept_code value=''> <br>
		<? print get_translation('CONCEPT_STRING','Libellé'); ?> : <input type=text id=id_concept_libelle_new value=''>  <br>
		<? print get_translation('SEMANTIC_TYPES','Type Sémantique'); ?> :  <input type=text id=id_type_semantic value=''> <br>
		<? print get_translation('MOTIF_ADD_CONCEPT','Motif ajout'); ?> :  <input type=text id=id_add_mode value='Manual'> <br>
		<input type=button onclick="ajouter_concepts();" value="<? print get_translation('SAVE','Sauver'); ?>"><br>
		<div id="id_div_ajouter_concepts"></div>
	</div>
	
<?
}
?>




<?
///////////////// CONCEPT //////////////////
if ($_GET['action']=='admin_thesaurus') {
?>
	<div id="id_admin_thesaurus"  class="div_admin">
		<h1><? print get_translation('THESAURUS_MANAGE','Administrer Thesaurus'); ?></h1>
		<? print get_translation('DISPLAY_CONCEPT_LIST','Afficher liste concepts'); ?> : 
		<select id="id_thesaurus_code">
			<option value=""></option>	
		<?
	      		$thesaurus=get_list_thesaurus_data();
	      		foreach ($thesaurus as $thesaurus_code) {
	      				print"<option value=\"$thesaurus_code\">$thesaurus_code</option>";	
	      		}	
      		?>
		</select>
		
		Display : <select id="display_type">
			<option value="table">table</option>	
			<option value="tree">arbre</option>	
		</select> 
		<input type=text id=id_thesaurus_data_search value=''> 
		<input type=button onclick="display_thesaurus();" value="<? print get_translation('DISPLAY','Afficher'); ?>"><br>
		
		<div id="id_div_result_thesaurus_data" style="display:none;"></div>

	</div>
	
<?
}
?>



<?
///////////////// CONCEPT //////////////////
if ($_GET['action']=='admin_cgu') {
?>
	
	<!-- Theme included stylesheets -->
	<link href="quill/quill.snow.css" rel="stylesheet">
	<!-- Include the Quill library -->
	<script src="quill/quill.js"></script>
	
	<div id="id_admin_cgu"  class="div_admin">
		<h1><? print get_translation('THESAURUS_MANAGE','Administrer les CGU'); ?></h1>
		
		<? print get_translation('WRITE_OR_MODIFY_LAST_CGU','Rédiger ou modifier le dernier CGU'); ?> : <br>
		<? 
			$cgu=get_last_cgu();
		?>
		
		<div id="editor">
		 <? print $cgu['cgu_text']; ?>
		</div>
		
		<input type=button onclick="save_cgu();" value="<? print get_translation('SAVE','Sauver'); ?>"><br>
		
		
		<br><br>Tant que le CGU n'est pas publié, il ne sera pas visible par les utilisateurs. <br>C'est le dernier CGU publié qui sera proposé aux utilisateurs.<br><br>
		<div id="id_div_list_cgu">
		</div>

	</div>
	
<script language="javascript">
	$(document).ready(function(){
	    $('.autosizejs').autosize();   
	    list_cgu ();
	});
	<!-- Initialize Quill editor -->
	  var quill = new Quill('#editor', {
	    theme: 'snow'
	  });
</script>
	
<?
}
?>





<?
///////////////// CONCEPT //////////////////
if ($_GET['action']=='admin_actu') {
?>
	
	<!-- Theme included stylesheets -->
	<link href="quill/quill.snow.css" rel="stylesheet">
	<!-- Include the Quill library -->
	<script src="quill/quill.js"></script>
	<div id="id_admin_actu"  class="div_admin" style="max-width:1000px;">
		<a name="ancre_actu"></a>
		<h1><? print get_translation('NEWS_MANAGE','Administrer les actualités'); ?></h1>
		
		<? print get_translation('WRITE_A_NEWS','Rédiger une actualité'); ?> : <br>
		<div id="editor">
		</div>
		<div id="id_div_button_add_actu">
		<input type=button onclick="save_actu();" value="<? print get_translation('SAVE','Sauver'); ?>"><br>
		</div>
		<div id="id_div_button_modify_actu" style="display:none;">
			<input type=hidden value="" id="id_input_actu_num_modify">
			<input type=button onclick="modify_actu();" value="<? print get_translation('MODIFY','Modifier'); ?>">	<input type=button onclick="cancel_modify_actu();" value="<? print get_translation('CNCEL','Annuler'); ?>"><br>
		</div>
		
		<br><br>Tant que l'actu n'est pas publiée, elle ne sera pas visible par les utilisateurs. <br><br>
		<div id="id_div_list_actu">
		</div>
	</div>
	
<script language="javascript">
	$(document).ready(function(){
	    $('.autosizejs').autosize();   
	    list_actu ();
	});
	<!-- Initialize Quill editor -->
	  var quill = new Quill('#editor', {
	    theme: 'snow'
	  });
</script>
	
<?
}
?>

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


<?
	include "foot.php"; 
?>