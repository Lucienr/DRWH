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

$req="select mail from dwh_user where user_num=$user_num_session ";
$sel=oci_parse($dbh,$req);
oci_execute($sel);
$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
$mail_session=$r['MAIL'];


		
/* nb de requetes faites  */
$req="select count(*) as NB_REQUETES from DWH_QUERY where user_num=$user_num_session ";
$sel=oci_parse($dbh,$req);
oci_execute($sel);
$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
$nb_requetes=$r['NB_REQUETES'];

/* nb distincts dossiers patients consultes  */
$req="select count(distinct patient_num) as NB_PATIENT_CONSULTES from DWH_LOG_PATIENT where user_num=$user_num_session and log_context='dossier'";
$sel=oci_parse($dbh,$req);
oci_execute($sel);
$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
$nb_patient_consultes=$r['NB_PATIENT_CONSULTES'];

/* nb  de cohorte creees  par vous*/
$req="select count(* ) as NB_COHORTE_CREEES from DWH_COHORT  where user_num=$user_num_session";
$sel=oci_parse($dbh,$req);
oci_execute($sel);
$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
$nb_cohorte_creees=$r['NB_COHORTE_CREEES'];

/* nb  de cohortes auxquelles vous etes associés */
$req="select count(*) as NB_COHORTE_ASSOCIES from DWH_COHORT  where  cohort_num in (select cohort_num from DWH_COHORT_USER_RIGHT  where user_num=$user_num_session) and user_num!=$user_num_session";
$sel=oci_parse($dbh,$req);
oci_execute($sel);
$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
$nb_cohorte_associes=$r['NB_COHORTE_ASSOCIES'];

/* nb  de patients inclus  dans vos cohortes*/
$req="select count(*) as NB_PATIENT_INCLUS_COHORTE from DWH_COHORT_RESULT where cohort_num in (select cohort_num from DWH_COHORT where user_num=$user_num_session)";
$sel=oci_parse($dbh,$req);
oci_execute($sel);
$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
$nb_patient_inclus=$r['NB_PATIENT_INCLUS_COHORTE'];

/* nb  de patients inclus  par vous*/
$req="select count(*) as NB_PATIENT_INCLUS_PAR_MOI from DWH_COHORT_RESULT  where  user_num_add=$user_num_session";
$sel=oci_parse($dbh,$req);
oci_execute($sel);
$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
$nb_patient_inclus_par_moi=$r['NB_PATIENT_INCLUS_PAR_MOI'];

?>
<style>
.mes_stats {
	float:left;
	font-size:24px;    
	padding-left: 20px;
	padding-top: 20px;
	position: relative;
	text-align: left;
}
</style>
<h1><? print get_translation('MY_ACCOUNT','Mon Compte'); ?></h1>

<div class="div_accueil">
	<h1>
	<img style="vertical-align: middle" src="images/health7.png">
	<? print get_translation('MY_PROFILE','Mon profil'); ?>
	</h1>
	<? afficher_mes_droits ($user_num_session); ?>
</div>


<div class="div_accueil">
	<h1>
	<img style="vertical-align: middle" src="images/graph.png"><? print get_translation('MY_STATISTICS','Mes stats'); ?>
	</h1>
	<img src="images/search_64.png" width="32" style="vertical-align:middle"> <? print $nb_requetes." ".get_translation('QUERY','requêtes')."<br><br>";?>
	<img src="images/medic-folder.png" width="32" style="vertical-align:middle"> <? print $nb_patient_consultes." ".get_translation('CONSULTED_PATIENTS_RECORDS','dossiers patients consultés')."<br><br>";?>
	<img src="images/mine2.png" width="32" style="vertical-align:middle"> <? print $nb_cohorte_creees." ".get_translation('CREATED_BY_YOU','cohortes créées par vous')."<br><br>";?>
	<img src="images/mine2.png" width="32" style="vertical-align:middle"> <? print $nb_cohorte_associes." ".get_translation('USER_ASSOCIATED_COHORTS','cohortes auxquelles vous êtes associé')."<br><br>";?>
	<img src="images/inclure_patient_cohorte.png" width="32" style="vertical-align:middle"> <? print $nb_patient_inclus." ".get_translation('INCLUDED_PATIENTS_IN_YOUR_COHORTS','patients inclus dans vos cohortes')."<br><br>";?>
	<img src="images/inclure_patient_cohorte.png" width="32" style="vertical-align:middle"> <? print $nb_patient_inclus_par_moi." ".get_translation('PATIENTS_INCLUDED_BY_YOU','patients inclus pas vous')."<br><br>"; ?>
</div>


<div class="div_accueil">
	<h1>
	<img style="vertical-align: middle" src="images/health7.png">
	<? print get_translation('MY_COORDINATES','Mes coordonnées'); ?>
	</h1>
	<span  style="line-height:2;">

		<? print get_translation('EMAIL','Email'); ?> : 
		<? 
		$user_mail_session=$_SESSION['dwh_mail'];
		$user_mail_session_value=$_SESSION['dwh_mail'];
		if ($user_mail_session_value=='') {
			$user_mail_session=get_translation('ADD_AN_EMAIL','Add an email');
		}

		?>
		<span id=id_span_user_mail style="display:inline" onclick="plier('id_span_user_mail');deplier('id_span_modifier_user_mail','inline');"><? print $user_mail_session; ?></span>
		<span id=id_span_modifier_user_mail style="display:none">
			<input type="text" size="30" id="id_input_user_mail" name="email" title="<? print $user_mail_session; ?>" value="<? print $user_mail_session_value; ?>">
			<input type=button class="input_texte" value="<? print get_translation('SAVE','Sauver'); ?>"  onclick="modifier_user_mail();">
		</span>
		<br>
		<? print get_translation('TELEPHONE_SHORT','Tel'); ?> : 
		<? 
		$user_phone_number_session=$_SESSION['dwh_user_phone_number'];
		$user_phone_number_session_value=$_SESSION['dwh_user_phone_number'];
		if ($user_phone_number_session_value=='') {
			$user_phone_number_session=get_translation('ADD_A_PHONE_NUMBER','Ajouter un numéro de téléphone');
		}
		?>
		<span id=id_span_user_phone_number style="display:inline" onclick="plier('id_span_user_phone_number');deplier('id_span_modifier_user_phone_number','inline');"><? print $user_phone_number_session; ?></span>
		<span id=id_span_modifier_user_phone_number style="display:none">
			<input type="text" size="30" id="id_input_user_phone_number" name="phone_number" title="<? print $user_phone_number_session; ?>" value="<? print $user_phone_number_session_value; ?>">
			<input type=button class="input_texte" value="<? print get_translation('SAVE','Sauver'); ?>"  onclick="modifier_user_phone_number();">
		</span>
		<br>
		<? print get_translation('MODIFY_MY_LOCAL_PASSWORD','Modifier mon mot de passe local'); ?> : 
		<span id=id_span_passwd_1 style="display:inline"><input type="password" id="id_mon_password1"> <input type=button value=valider class="input_texte" onclick="plier('id_span_passwd_1');deplier('id_span_passwd_2','inline');"></span>
		<span id=id_span_passwd_2 style="display:none"><? print get_translation('SECOND_INPUT_CONFIRMATION','2ieme saisie'); ?> <input type="password" id="id_mon_password2"> <input type=button class="input_texte" value="<? print get_translation('CONFIRM_MODIFICATION_PASSWORD','Confirmer'); ?>"  onclick="modifier_passwd('');"></span>
		<br>
		<span id="id_modifmdp_result"></span>
		<br>
	</span>
	<? affiche_mes_services (); ?>
</div>


<? save_log_page($user_num_session,'moncompte'); ?>
<? include "foot.php"; ?>