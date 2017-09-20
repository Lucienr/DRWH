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
<h2><? print get_translation('ADD_ALL_PATIENTS_IN_DATAMART','Ajouter tous ces patients dans le datamart'); ?> :</h2>
<div id="id_div_liste_ajouter_datamart_select">
<?
	afficher_datamart_select('id_select_num_datamart_ajouter_patient');
?>
</div>
<input type="button" value="<? print get_translation('ADD','Ajouter'); ?>" onclick="ajouter_patient_datamart();">
<div id="id_div_resutat_ajouter_patient_datamart"></div>
<? print get_translation('IF_DATAMART_DOES_NOT_EXIST',"Si le datamart n'existe pas, ajouter le en cliquant ici"); ?> : <a href="admin.php?action=datamart" target="_blank"><? print get_translation('DATAMART_ADD','Ajouter un datamart'); ?></a><br>
<? print get_translation('REFRESH_DATAMART_LIST_WITH_CLICK','Rafraichir les listes de datamart ci dessus en cliquant ici'); ?> : <a href="#" onclick="rafraichir_liste_datamart();return false;"><? print get_translation('REFRESH','Rafraichir'); ?></a><br>
<br>
<br>
<h2><? print get_translation('DELETE_PATIENTS_FROM_DATAMART','Supprimer ces patients du datamart'); ?> :</h2>
<div id="id_div_liste_supprimer_datamart_select">
<?
	afficher_datamart_select('id_select_num_datamart_supprimer_patient');
?>
</div>
<input type="button" value="<? print get_translation('DELETE','Supprimer'); ?>" onclick="supprimer_patient_datamart();">
<div id="id_div_resutat_supprimer_patient_datamart"></div>
<br>

<script language="javascript">
		function rafraichir_liste_datamart() {
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:true,
				data: { action:'afficher_datamart_select',id:'id_select_num_datamart_ajouter_patient'},
				beforeSend: function(requester){
				},
				success: function(requester){
					contenu=requester;
					if (contenu=='deconnexion') {
						afficher_connexion();
					} else {
						$("#id_div_liste_ajouter_datamart_select").html(requester);
					}
				},
				complete: function(requester){
				},
				error: function(){
				}
			});	
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:true,
				data: { action:'afficher_datamart_select',id:'id_select_num_datamart_supprimer_patient'},
				beforeSend: function(requester){
				},
				success: function(requester){
					contenu=requester;
					if (contenu=='deconnexion') {
						afficher_connexion();
					} else {
						$("#id_div_liste_supprimer_datamart_select").html(requester);
					}
				},
				complete: function(requester){
				},
				error: function(){
				}
			});		
		}
		
		function ajouter_patient_datamart() {
			datamart_num=document.getElementById('id_select_num_datamart_ajouter_patient').value;
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:true,
				data: { action:'ajouter_patient_datamart',num_datamart_admin:datamart_num,tmpresult_num:<? print $tmpresult_num; ?>},
				beforeSend: function(requester){
				},
				success: function(requester){
					contenu=requester;
					if (contenu=='deconnexion') {
						afficher_connexion();
					} else {
						$("#id_div_resutat_ajouter_patient_datamart").html(requester);
					}
				},
				complete: function(requester){
				},
				error: function(){
				}
			});		
		}
		function supprimer_patient_datamart() {
			datamart_num=document.getElementById('id_select_num_datamart_supprimer_patient').value;
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:true,
				data: { action:'supprimer_patient_datamart',num_datamart_admin:datamart_num,tmpresult_num:<? print $tmpresult_num; ?>},
				beforeSend: function(requester){
				},
				success: function(requester){
					contenu=requester;
					if (contenu=='deconnexion') {
						afficher_connexion();
					} else {
						$("#id_div_resutat_supprimer_patient_datamart").html(requester);
					}
				},
				complete: function(requester){
				},
				error: function(){
				}
			});		
		}
</script>