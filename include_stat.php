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
<script language="javascript">
	var afficher_stat_deja='';
	function affiche_onglet_stat(tmpresult_num) {
		if (afficher_stat_deja=='') {
			afficher_stat_deja=1;
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				method: 'post',
				async:true,
				contentType: 'application/x-www-form-urlencoded',
				encoding: 'latin1',
				data: {action:'pyramide_age',tmpresult_num:tmpresult_num},
				beforeSend: function(requester){
						document.getElementById('id_pyramide_age_document').innerHTML="<img src=\"images/chargement_mac.gif\">";
						document.getElementById('id_pyramide_age_today').innerHTML="<img src=\"images/chargement_mac.gif\">";
						document.getElementById('id_pyramide_age_document_jeune').innerHTML="<img src=\"images/chargement_mac.gif\">";
						document.getElementById('id_pyramide_age_today_jeune').innerHTML="<img src=\"images/chargement_mac.gif\">";
				},
				success: function(requester){
					var data=requester;
					if (data=='deconnexion') {
						afficher_stat_deja=0;
						afficher_connexion("affiche_onglet_stat("+tmpresult_num+")");
					} else {
						eval(data);
					}
				},
				error: function(){
				}
			});
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				method: 'post',
				async:true,
				contentType: 'application/x-www-form-urlencoded',
				encoding: 'latin1',
				data: {action:'nb_patient_sex',tmpresult_num:tmpresult_num},
				beforeSend: function(requester){
						document.getElementById('id_sex_repartition').innerHTML="<img src=\"images/chargement_mac.gif\">";
				},
				success: function(requester){
					var data=requester;
					if (data=='deconnexion') {
						afficher_stat_deja=0;
						afficher_connexion("affiche_onglet_stat("+tmpresult_num+")");
					} else {
						eval(data);
					}
				},
				error: function(){
				}
			});
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				method: 'post',
				async:true,
				contentType: 'application/x-www-form-urlencoded',
				encoding: 'latin1',
				data: {action:'nb_document_document_origin_code',tmpresult_num:tmpresult_num,id_div:'id_document_origin_codeument_repartition'},
				beforeSend: function(requester){
						document.getElementById('id_document_origin_codeument_repartition').innerHTML="<img src=\"images/chargement_mac.gif\">";
				},
				success: function(requester){
					var data=requester;
					if (data=='deconnexion') {
						afficher_stat_deja=0;
						afficher_connexion("affiche_onglet_stat("+tmpresult_num+")");
					} else {
						eval(data);
					}
				},
				error: function(){
				}
			});
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				method: 'post',
				async:true,
				contentType: 'application/x-www-form-urlencoded',
				encoding: 'latin1',
				data: {action:'nb_patients_temps',tmpresult_num:tmpresult_num,id_div:'id_nb_nouveaux_patient_graph',option_affichage:'graph'},
				beforeSend: function(requester){
						document.getElementById('id_nb_nouveaux_patient_graph').innerHTML="<img src=\"images/chargement_mac.gif\">";
				},
				success: function(requester){
					var data=requester;
					if (data=='deconnexion') {
						afficher_stat_deja=0;
						afficher_connexion("affiche_onglet_stat("+tmpresult_num+")");
					} else {
						eval(data);
					}
				},
				error: function(){
				}
			});
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				method: 'post',
				async:true,
				contentType: 'application/x-www-form-urlencoded',
				encoding: 'latin1',
				data: {action:'nb_patients_temps',tmpresult_num:tmpresult_num,id_div:'id_nb_nouveaux_patient_tableau',option_affichage:'tableau'},
				beforeSend: function(requester){
						document.getElementById('id_nb_nouveaux_patient_tableau').innerHTML="<img src=\"images/chargement_mac.gif\">";
				},
				success: function(requester){
					var data=requester;
					if (data=='deconnexion') {
						afficher_stat_deja=0;
						afficher_connexion("affiche_onglet_stat("+tmpresult_num+")");
					} else {
						document.getElementById('id_nb_nouveaux_patient_tableau').innerHTML=data;
					}
				},
				error: function(){
				}
			});
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				method: 'post',
				async:true,
				contentType: 'application/x-www-form-urlencoded',
				encoding: 'latin1',
				data: {action:'nb_nouveau_patients_service',tmpresult_num:tmpresult_num,id_div:'id_nb_nouveau_patients_service'},
				beforeSend: function(requester){
						document.getElementById('id_nb_nouveau_patients_service').innerHTML="<img src=\"images/chargement_mac.gif\">";
				},
				success: function(requester){
					var data=requester;
					if (data=='deconnexion') {
						afficher_stat_deja=0;
						afficher_connexion("affiche_onglet_stat("+tmpresult_num+")");
					} else {
						tableau_data=data.split(';separateur;');
						hauteur=tableau_data[0];
						graph=tableau_data[1];
						
						$('#id_nb_nouveau_patients_service').css('height',hauteur);
						eval(graph);
					}
				},
				error: function(){
				}
			});
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				method: 'post',
				async:true,
				contentType: 'application/x-www-form-urlencoded',
				encoding: 'latin1',
				data: {action:'nb_nouveau_patients_service_hors_mespatients',tmpresult_num:tmpresult_num,id_div:'id_nb_nouveau_patients_service_hors_mespatients'},
				beforeSend: function(requester){
						document.getElementById('id_nb_nouveau_patients_service_hors_mespatients').innerHTML="<img src=\"images/chargement_mac.gif\">";
				},
				success: function(requester){
					var data=requester;
					if (data=='deconnexion') {
						afficher_stat_deja=0;
						afficher_connexion("affiche_onglet_stat("+tmpresult_num+")");
					} else {
						if (data!='') {
							tableau_data=data.split(';separateur;');
							hauteur=tableau_data[0];
							graph=tableau_data[1];
							
							$('#id_nb_nouveau_patients_service_hors_mespatients').css('height',hauteur);
							eval(graph);
						} else {
							document.getElementById('id_nb_nouveau_patients_service_hors_mespatients').innerHTML="";
						}
					}
				},
				error: function(){
				}
			});
		}
	}

</script>
<?

	/// PYRAMIDE  DES AGES //////
	print "
	<a href=\"parcours_moyen_d3.php?tmpresult_num=$tmpresult_num\" target=\"_blank\">".get_translation('DISPLAY_AVERAGE_JOURNEY_HOSPIT','Afficher le parcours moyen (hospitalisations) des patients')."</a><br>
	<a href=\"parcours_complet_d3.php?tmpresult_num=$tmpresult_num\" target=\"_blank\">".get_translation('DISPLAY_AVERAGE_JOURNEY_HOSPIT_CONSULT','Afficher le parcours moyen complet (hospitalisations et consultations) des patients')."</a><br>
	<div id=\"id_pyramide_age_document\" style=\"float:left;width: 600px; height: 400px;\"></div>
	<div id=\"id_pyramide_age_today\" style=\"float:left;width: 600px; height: 400px;\"></div>
	<br><br>
	<div id=\"id_pyramide_age_document_jeune\" style=\"float:left;width: 600px; height: 400px;\"></div>
	<div id=\"id_pyramide_age_today_jeune\" style=\"float:left;width: 600px; height: 400px;\"></div>
	";

	print "<div id=\"id_sex_repartition\" style=\"float:left;width: 600px; height: 400px;\"></div>";
	print "<div id=\"id_document_origin_codeument_repartition\" style=\"float:left; width: 600px; height: 400px;\"></div>";
	
	print "<div id=\"id_nb_nouveaux_patient_graph\" style=\"float:left; width:900px;height:400px;\"></div>";
	print "<div id=\"id_nb_nouveaux_patient_tableau\" style=\"float:left; \"></div>";
	
	print "<br>";
	print "<br>";
	
	print "<div id=\"id_nb_nouveau_patients_service\" style=\"float:left; width:900px;height:800px;\"></div>";
	print "<div id=\"id_nb_nouveau_patients_service_hors_mespatients\" style=\"float:left; width:900px;height:800px;\"></div>";
	
	
	
	

?>