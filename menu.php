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
<div id="id_menu_flottant"  align="center" class="noprint" >
	<table id="id_tableau_menu_flottant" width="100%" height="25" border="0" cellspacing="0" cellpadding="0" bgcolor="#1e2a63" style="border-top:1px white solid;border-bottom:1px white solid;">
		<tr>
			<td width="80px" nowrap="nowrap" onmouseover="this.style.backgroundColor='#D1D0D5';" onmouseout="this.style.backgroundColor='#1e2a63';" style="text-align:center;padding:0px 10px;"><a class="connexion" href="index.php" nowrap=nowrap><? print get_translation('HOME','Accueil'); ?></a></td>
			<td class="connexion" width="1"> | </td>

			<? if ($_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_droit_search_engine0']!='') { ?>
				<td  nowrap="nowrap" style="text-align:center;padding:0px 10px;" onmouseover="this.style.backgroundColor='#D1D0D5';" onmouseout="this.style.backgroundColor='#1e2a63';"><a href="moteur.php" class="connexion"><? print get_translation('SEARCH_ENGINE','Moteur de recherche'); ?></a></td>
				<td class="connexion" width="1"> | </td>
				
				<? if ($_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_login']!='') { ?>
					<td  nowrap="nowrap" style="text-align:center;padding:0px 10px;" onmouseover="this.style.backgroundColor='#D1D0D5';" onmouseout="this.style.backgroundColor='#1e2a63';"><a href="mes_requetes.php" class="connexion"><? print get_translation('MY_QUERIES','Mes requêtes'); ?></a></td>
					<td class="connexion" width="1"> | </td>
				<? } ?>
			<? } ?>

			<? if ($_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_login']!='') { ?>
				<td  nowrap="nowrap" style="text-align:center;padding:0px 10px;" onmouseover="this.style.backgroundColor='#D1D0D5';" onmouseout="this.style.backgroundColor='#1e2a63';"><a href="mes_cohortes.php" class="connexion"><? print get_translation('MY_COHORTS','Mes cohortes'); ?></a></td>
				<td class="connexion" width="1"> | </td>
	
				<!--
				<td  nowrap="nowrap" style="text-align:center;padding:0px 10px;" onmouseover="this.style.backgroundColor='#D1D0D5';" onmouseout="this.style.backgroundColor='#1e2a63';">
					<span><a href="mes_demandes.php" class="connexion">Mes demandes</a></span>
					<span id="id_alerte_demande_acces" class="span_alerte" style="display:none;"></span>
				</td>
				<td class="connexion" width="1"> | </td>
				-->

				<td  nowrap="nowrap" style="text-align:center;padding:0px 10px;" onmouseover="this.style.backgroundColor='#D1D0D5';" onmouseout="this.style.backgroundColor='#1e2a63';"><a href="outils.php" class="connexion"><? print get_translation('TOOLS','Outils'); ?></a></td>
				<td class="connexion" width="1"> | </td>
				
				<td  nowrap="nowrap" style="text-align:center;padding:0px 10px;" onmouseover="this.style.backgroundColor='#D1D0D5';" onmouseout="this.style.backgroundColor='#1e2a63';"><a href="mes_ecrf.php" class="connexion"><? print get_translation('FORMS','Mes ecrf'); ?></a></td>
				<td class="connexion" width="1"> | </td>
				<? if ($module_arno_active) { ?>
					<td  nowrap="nowrap" style="text-align:center;padding:0px 10px;" onmouseover="this.style.backgroundColor='#D1D0D5';" onmouseout="this.style.backgroundColor='#1e2a63';"><a href="protocols.php" class="connexion"><? print get_translation('MY_PROTOCOLS','Mes protocoles'); ?></a></td>
					<td class="connexion" width="1"> | </td>
				<? } ?>
			<? } ?>
			
		<!-- <? if ($_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_droit_search_engine0']!='') { ?>
			
				<td  nowrap="nowrap" style="text-align:center;padding:0px 10px;" onmouseover="this.style.backgroundColor='#D1D0D5';" onmouseout="this.style.backgroundColor='#1e2a63';">
				<span class="connexion">Recherche rapide : 
				</span>
				<form action="moteur.php" method="post" style="display:inline;padding:0;margin:0">
					<input type="hidden" name="action" value="rechercher">
					<input type="hidden" name="max_num_filtre" value="1">
					<input type="hidden" name="datamart_num" value="0">
					<input type="hidden" name="num_filtre_1" value="1">
					<input type="hidden" name="tmpresult_num" value="<? print $tmpresult_num; ?>">
					<input type="text" name="text_1" value="" class="form ui-autocomplete-input" size="20" style="font-size:10px;padding:0px;">
				</form>
				</td>
				<td class="connexion" width="1"> | </td>
			<? } ?>
		-->
			
			<? if ($_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_droit_patient_quick_access0']!='') { ?>
				<td  nowrap="nowrap" style="text-align:center;padding:0px 10px;" onmouseover="this.style.backgroundColor='#D1D0D5';" onmouseout="this.style.backgroundColor='#1e2a63';"><span class="connexion"><? print get_translation('PATIENT','Patient'); ?> </a></span>
					<div class="ui-widget" style="padding-left: 0px;width:260px;font-size:10px;display:inline;">
						<span class="ui-helper-hidden-accessible" aria-live="polite" role="status"></span>
						<span class="ui-helper-hidden-accessible" role="status" aria-live="polite"></span>
						<form style="display:inline" action="patient.php" id="id_form_patient_quick_access"><input id="id_champs_patient_quick_access" onclick="if (this.value=='<? print get_translation('NAME_OR_PATIENT_ID','Nom / IPP'); ?>') {this.value='';}" class="form ui-autocomplete-input" type="text" size="15" name="patient_num" value="<? print get_translation('NAME_OR_PATIENT_ID','Nom / IPP'); ?>" style="font-size:10px;padding:0px;" autocomplete="off" onkeypress="recherche_encours();if(event.keyCode==13) {champs_patient_quick_access();return false;}">
						</form>
					</div>
					<style>
					ul.ui-widget-content {
						width:300px;
					}
					.ui-autocomplete-loading { background:white url("images/chargement_mac.gif") no-repeat scroll right center / 10px auto; background-size:10px;}
					</style>
					<script language="javascript">
						function recherche_encours() {
							deplier('ui-id-1','');
							document.getElementById('ui-id-1').innerHTML='<? print get_translation('JS_SEARCH_ONGOING','recherche en cours'); ?>';
							document.getElementById('ui-id-1').style.top='143.5px'; 
							document.getElementById('ui-id-1').style.left='719px'; 
							document.getElementById('ui-id-1').style.width='301px'; 
							document.getElementById('ui-id-1').style.fontSize='12px'; 
							document.getElementById('ui-id-1').style.fontFamily='Verdana'; 
						}
						
						jQuery(function() {
							jQuery( "#id_champs_patient_quick_access").autocomplete({
								source: "ajax.php?action=patient_quick_access",
								minLength: 2,
								select: function( event, ui ) {
									patient_num=ui.item.id;
									document.getElementById('id_champs_patient_quick_access').value=patient_num;
									document.getElementById('id_form_patient_quick_access').submit();
									return false;
								}
							});
						});
						function champs_patient_quick_access() {
							jQuery( "#id_champs_patient_quick_access").autocomplete({
								source: "ajax.php?action=patient_quick_access",
								minLength: 2,
								select: function( event, ui ) {
									patient_num=ui.item.id;
									document.getElementById('id_champs_patient_quick_access').value=patient_num;
									document.getElementById('id_form_patient_quick_access').submit();
									return false;
								}
							});
						}
					</script>
				</td>
				<td class="connexion" width="1"> | </td>
			<? } ?>
			<? if ($_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_droit_admin']!='') { ?>
				<td  nowrap="nowrap" style="text-align:center;padding:0px 10px;" onmouseover="this.style.backgroundColor='#D1D0D5';" onmouseout="this.style.backgroundColor='#1e2a63';"><a href="admin.php" class="connexion"><? print get_translation('ADMIN','Admin'); ?></a></td>
				<td class="connexion" width="1"> | </td>
			<? } ?>	

			<td width="100%"></td>
			<? if ($_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_login']!='') { ?>
				<td  style="text-align:right;padding:0px 10px;" onmouseover="this.style.backgroundColor='#D1D0D5';" onmouseout="this.style.backgroundColor='#1e2a63';" nowrap="nowrap">
					<div style="display:inline;position:relative;" class="connexion"  id="id_div_menu_notification"><span onclick="open_notification('non');" style="cursor:pointer;"><? print get_translation('NOTIFICATIONS','Notifications'); ?></span>
						<div  id="id_div_notification" style="display:none;position:absolute;background-color:white;color:#333333;left:-200px;width:370px;border:1px solid grey;text-align:left;top: 30px;" >
							<div style="display:block;position:absolute;background-image:url('images/triangle_blanc.png');background-repeat:no-repeat;background-size:auto;height:11px;width:20px;top: -12px;left: 240px;" ></div>
							<div onclick="plier_deplier('id_div_envoyer_message_prive');" style="padding:10px;cursor:pointer;"><? print get_translation('SEND_PRIVATE_MESSAGE','Envoyer un message privé'); ?></div>
							<div id="id_div_envoyer_message_prive" style="display:none;">
								<select class="chosen-select"  id="id_select_num_user_message_prive"  data-placeholder="<? print get_translation('SELECT_A_USER','Sélectionnez un utilisateur'); ?>">
								<option value=''></option>
								<optgroup label="Connectés">
								<?
									$sel_var1=oci_parse($dbh,"select  user_num,lastname,firstname from dwh_user where user_num!=$user_num_session
									and user_num in (select  user_num from dwh_user_online  where last_update_date>sysdate-'0,001') order by lastname,firstname ");
									oci_execute($sel_var1);
									while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
										$user_num=$r['USER_NUM'];
										$lastname=$r['LASTNAME'];
										$firstname=$r['FIRSTNAME'];
										
										print "<option  value=\"$user_num\" class=\"online\">$lastname $firstname </option>";
									}
								?>
								</optgroup>
								<optgroup label="Non Connectés">
								<?
									$sel_var1=oci_parse($dbh,"select  user_num,lastname,firstname from dwh_user where user_num!=$user_num_session 
									and user_num not in (select  user_num from dwh_user_online  where last_update_date>sysdate-'0,001') order by lastname,firstname ");
									oci_execute($sel_var1);
									while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
										$user_num=$r['USER_NUM'];
										$lastname=$r['LASTNAME'];
										$firstname=$r['FIRSTNAME'];
										print "<option  value=\"$user_num\"  class=\"notonline\">$lastname $firstname </option>";
									}
								?>
								</optgroup>
								</select><br>
								<textarea cols="45" rows="3" id="id_textarea_message_prive" class="filtre_texte input_texte autosizejs" style="display: inline; overflow: hidden; overflow-wrap: break-word; resize: horizontal; height: 30px;"></textarea>
								<input type=button value='Envoyer' onclick="envoyer_message_prive();">
							</div>
							<div id="id_div_contenu_notification" ></div>

						</div>
					</div>
					<span id="id_alerte_notification" class="span_alerte" style="width:22;text-align:left">&nbsp;</span>
				</td>
				<td class="connexion" width="1"> | </td>
				<td  style="text-align:right;padding:0px 10px;" onmouseover="this.style.backgroundColor='#D1D0D5';" onmouseout="this.style.backgroundColor='#1e2a63';" nowrap="nowrap">
					<span><a href="moncompte.php" class="connexion"><? print "$firstname_user_session $lastname_user_session"; ?><a/></span>
				</td>
				<td class="connexion" width="1"> | </td>
			<? } ?>
			<? if ($_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_login']!='') { ?>
				<td style="text-align:right;padding:0px 10px;" onmouseover="this.style.backgroundColor='#D1D0D5';" onmouseout="this.style.backgroundColor='#1e2a63';" nowrap="nowrap"><a href="connexion_user.php" class="connexion"><? print get_translation('LOGOUT','Déconnexion'); ?></a></td>
			<? } ?>
		</tr>
	</table>
</div>
<?
	if ($erreur_droit!=''  && !preg_match("/connexion_user\.php/",$_SERVER['REQUEST_URI']) && !preg_match("/contact\.php/",$_SERVER['REQUEST_URI'])) {
		print "$erreur_droit";
		exit;
	}
?>