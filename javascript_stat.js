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
					jQuery('#id_pyramide_age_document').html("<img src=\"images/chargement_mac.gif\">");
					jQuery('#id_pyramide_age_today').html("<img src=\"images/chargement_mac.gif\">");
					jQuery('#id_pyramide_age_document_jeune').html("<img src=\"images/chargement_mac.gif\">");
					jQuery('#id_pyramide_age_today_jeune').html("<img src=\"images/chargement_mac.gif\">");
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
					jQuery('#id_sex_repartition').html("<img src=\"images/chargement_mac.gif\">");
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
					jQuery('#id_document_origin_codeument_repartition').html("<img src=\"images/chargement_mac.gif\">");
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
					jQuery('#id_nb_nouveaux_patient_graph').html("<img src=\"images/chargement_mac.gif\">");
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
					jQuery('#id_nb_nouveaux_patient_tableau').html("<img src=\"images/chargement_mac.gif\">");
			},
			success: function(requester){
				var data=requester;
				if (data=='deconnexion') {
					afficher_stat_deja=0;
					afficher_connexion("affiche_onglet_stat("+tmpresult_num+")");
				} else {
					jQuery('#id_nb_nouveaux_patient_tableau').html(data);
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
					jQuery('#id_nb_nouveau_patients_service').html("<img src=\"images/chargement_mac.gif\">");
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
					jQuery('#id_nb_nouveau_patients_service_hors_mespatients').html("<img src=\"images/chargement_mac.gif\">");
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
						
						jQuery('#id_nb_nouveau_patients_service_hors_mespatients').css('height',hauteur);
						eval(graph);
					} else {
						jQuery('#id_nb_nouveau_patients_service_hors_mespatients').html("");
						jQuery('#id_nb_nouveau_patients_service_hors_mespatients').css('display','none');
					}
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
			data: {action:'nb_consult_per_unit_per_year_tableau',tmpresult_num:tmpresult_num},
			beforeSend: function(requester){
					jQuery('#id_nb_consult_per_unit_per_year_tableau').html("<img src=\"images/chargement_mac.gif\">");
			},
			success: function(requester){
				var data=requester;
				if (data=='deconnexion') {
					afficher_stat_deja=0;
					afficher_connexion("affiche_onglet_stat("+tmpresult_num+")");
				} else {
					jQuery('#id_nb_consult_per_unit_per_year_tableau').html(data);
					$("#id_table_nb_consult_per_unit_per_year_tableau").dataTable( {  "columnDefs": [ {"targets": 'no-sort',"orderable": false}],"order": [[ 0, "asc" ]],"paging": false});
					$("#id_table_nb_consult_per_department_per_year_tableau").dataTable( {  "columnDefs": [ {"targets": 'no-sort',"orderable": false}], "order": [[ 0, "asc" ]],"paging": false});
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
			data: {action:'nb_hospit_per_unit_per_year_tableau',tmpresult_num:tmpresult_num},
			beforeSend: function(requester){
					jQuery('#id_nb_hospit_per_unit_per_year_tableau').html("<img src=\"images/chargement_mac.gif\">");
			},
			success: function(requester){
				var data=requester;
				if (data=='deconnexion') {
					afficher_stat_deja=0;
					afficher_connexion("affiche_onglet_stat("+tmpresult_num+")");
				} else {
					jQuery('#id_nb_hospit_per_unit_per_year_tableau').html(data);
					$("#id_table_nb_hospit_per_unit_per_year_tableau").dataTable( {  "columnDefs": [ {"targets": 'no-sort',"orderable": false}],"order": [[ 0, "asc" ]],  "paging": false});
					$("#id_table_nb_hospit_per_department_per_year_tableau").dataTable( {  "columnDefs": [ {"targets": 'no-sort',"orderable": false}], "order": [[ 0, "asc" ]],"paging": false});
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
			data: {action:'nb_patient_per_unit_per_year_tableau',tmpresult_num:tmpresult_num},
			beforeSend: function(requester){
					jQuery('#id_nb_patient_per_unit_per_year_tableau').html("<img src=\"images/chargement_mac.gif\">");
			},
			success: function(requester){
				var data=requester;
				if (data=='deconnexion') {
					afficher_stat_deja=0;
					afficher_connexion("affiche_onglet_stat("+tmpresult_num+")");
				} else {
					jQuery('#id_nb_patient_per_unit_per_year_tableau').html(data);
					$("#id_table_nb_patient_per_unit_per_year_tableau").dataTable( {  "columnDefs": [ {"targets": 'no-sort',"orderable": false}],"order": [[ 0, "asc" ]],  "paging": false});
					$("#id_table_nb_patient_per_department_per_year_tableau").dataTable( {  "columnDefs": [ {"targets": 'no-sort',"orderable": false}],"order": [[ 0, "asc" ]],  "paging": false});
					
				}
			},
			error: function(){
			}
		});
	}
}