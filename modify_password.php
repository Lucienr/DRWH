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
	<? print get_translation('MODIFY_MY_LOCAL_PASSWORD','Modifier mon mot de passe local'); ?><br>
	<? print get_translation('TEXT_DETAIL_MODIFY_PASSWORD',"Il s'agit du mot de passe interne à Dr Warehouse. Cela n'impactera aucune autre application du système d'information."); ?>
	</h1>
	<? 
	if ($OPTION_PASSWORD_COMPLEX=='ok' || $OPTION_PASSWORD_COMPLEX=='1') {
		print get_translation('PASSWORD_CONDITION',"Le mot de passe doit faire plus de 8 caractères, avec une majuscule, un chiffre et un caractère particulier."); 
	}
	?><br>
	<span  style="line-height:2;">
	
		<span id=id_span_passwd_1 style="display:inline">
		Saisir un nouveau mot de passe : 
		<input type="password" id="id_mon_password1" onkeypress="if(event.keyCode==13) {plier('id_span_passwd_1');deplier('id_span_passwd_2','inline');document.getElementById('id_mon_password2').focus();}"> 
		<input type=button value=valider class="input_texte" onclick="plier('id_span_passwd_1');deplier('id_span_passwd_2','inline');document.getElementById('id_mon_password2').focus();"></span>
		
		<span id=id_span_passwd_2 style="display:none">
		<? print get_translation('CONFIRM_THE_PASSWORD','Confirmer le mot de passe'); ?> 
		<input type="password" id="id_mon_password2" onkeypress="if(event.keyCode==13) {modifier_passwd('index.php');}"> 
		<input type=button class="input_texte" value="<? print get_translation('CONFIRM_MODIFICATION_PASSWORD','Confirmer'); ?>"  onclick="modifier_passwd('index.php');">
		</span>
		
		<br>
		<span id="id_modifmdp_result"></span>
		<br>
	</span>
</div>
<script language="javascript">
jQuery('#id_mon_password1').focus();
</script>

<? save_log_page($user_num_session,'moncompte'); ?>
<? include "foot.php"; ?>