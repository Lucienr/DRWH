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
jQuery(function() {
	jQuery( "#id_champs_comparateur_patient_1").autocomplete({
		source: "ajax.php?action=patient_quick_access",
		minLength: 1,
		select: function( event, ui ) {
			patient_num=ui.item.id;
			document.getElementById('id_patient_num_1').value=patient_num;
			document.getElementById('id_patient_1').innerHTML=ui.item.label;
			comparer_patient();
			return false;
		}
	});
	jQuery( "#id_champs_comparateur_patient_2").autocomplete({
		source: "ajax.php?action=patient_quick_access",
		minLength: 1,
		select: function( event, ui ) {
			patient_num=ui.item.id;
			document.getElementById('id_patient_num_2').value=patient_num;
			document.getElementById('id_patient_2').innerHTML=ui.item.label;
			comparer_patient();
			return false;
		}
	});
});


function comparer_patient () {
	patient_num_1=document.getElementById('id_patient_num_1').value;
	patient_num_2=document.getElementById('id_patient_num_2').value;
	distance=document.getElementById('id_distance').value;

	jQuery.ajax({
		type:"POST",
		url:"ajax_outils.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'comparer_patient',patient_num_1:patient_num_1,patient_num_2:patient_num_2,distance:distance},
		beforeSend: function(requester){
			document.getElementById('id_div_resultat_patient_1').innerHTML='<img src="images/chargement_mac.gif">';
			document.getElementById('id_div_resultat_intersect').innerHTML='<img src="images/chargement_mac.gif">';
			document.getElementById('id_div_resultat_patient_2').innerHTML='<img src="images/chargement_mac.gif">';
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("comparer_patient ()");
				document.getElementById('id_div_resultat_patient_1').innerHTML='';
				document.getElementById('id_div_resultat_intersect').innerHTML='';
				document.getElementById('id_div_resultat_patient_2').innerHTML='';
			} else {
				tableau_requester=requester.split('-separateur_ajax-');
				patient_1=tableau_requester[0];
				intersect=tableau_requester[1];
				patient_2=tableau_requester[2];
				document.getElementById('id_div_resultat_patient_1').innerHTML=patient_1;
				document.getElementById('id_div_resultat_intersect').innerHTML=intersect;
				document.getElementById('id_div_resultat_patient_2').innerHTML=patient_2;
			}
		},
		complete: function(requester){
			
		},
		error: function(){
				document.getElementById('id_div_resultat_intersect').innerHTML='Erreur';
				document.getElementById('id_div_resultat_patient_1').innerHTML='';
				document.getElementById('id_div_resultat_patient_2').innerHTML='';
		}
	});
}

function comparer_cohorte () {
	cohort_num_1=document.getElementById('id_cohort_num_1').value;
	cohort_num_2=document.getElementById('id_cohort_num_2').value;
		etat_patient_cohorte_1_inclu='';
		etat_patient_cohorte_1_exclu='';
		etat_patient_cohorte_1_doute='';
		etat_patient_cohorte_2_inclu='';
		etat_patient_cohorte_2_exclu='';
		etat_patient_cohorte_2_doute='';
	if (document.getElementById('id_etat_patient_cohorte_1_inclu').checked==true) {
		etat_patient_cohorte_1_inclu=1;
	}
	if (document.getElementById('id_etat_patient_cohorte_1_exclu').checked==true) {
		etat_patient_cohorte_1_exclu=0;
	}
	if (document.getElementById('id_etat_patient_cohorte_1_doute').checked==true) {
		etat_patient_cohorte_1_doute=2;
	}
	if (document.getElementById('id_etat_patient_cohorte_2_inclu').checked==true) {
		etat_patient_cohorte_2_inclu=1;
	}
	if (document.getElementById('id_etat_patient_cohorte_2_exclu').checked==true) {
		etat_patient_cohorte_2_exclu=0;
	}
	if (document.getElementById('id_etat_patient_cohorte_2_doute').checked==true) {
		etat_patient_cohorte_2_doute=2;
	}
	distance=document.getElementById('id_distance').value;
	jQuery.ajax({
		type:"POST",
		url:"ajax_outils.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'comparer_cohorte',cohort_num_1:cohort_num_1,cohort_num_2:cohort_num_2,distance:distance,etat_patient_cohorte_1_inclu:etat_patient_cohorte_1_inclu,etat_patient_cohorte_1_exclu:etat_patient_cohorte_1_exclu,etat_patient_cohorte_1_doute:etat_patient_cohorte_1_doute,etat_patient_cohorte_2_inclu:etat_patient_cohorte_2_inclu,etat_patient_cohorte_2_exclu:etat_patient_cohorte_2_exclu,etat_patient_cohorte_2_doute:etat_patient_cohorte_2_doute},
		beforeSend: function(requester){
			document.getElementById('id_div_resultat_cohorte_1').innerHTML='<img src="images/chargement_mac.gif">';
			document.getElementById('id_div_resultat_cohorte_intersect').innerHTML='<img src="images/chargement_mac.gif">';
			document.getElementById('id_div_resultat_cohorte_2').innerHTML='<img src="images/chargement_mac.gif">';
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("comparer_cohorte ()");
				document.getElementById('id_div_resultat_cohorte_1').innerHTML='';
				document.getElementById('id_div_resultat_cohorte_intersect').innerHTML='';
				document.getElementById('id_div_resultat_cohorte_2').innerHTML='';
			} else {
				tableau_requester=requester.split('-separateur_ajax-');
				cohorte_1=tableau_requester[0];
				intersect=tableau_requester[1];
				cohorte_2=tableau_requester[2];
				document.getElementById('id_div_resultat_cohorte_1').innerHTML=cohorte_1;
				document.getElementById('id_div_resultat_cohorte_intersect').innerHTML=intersect;
				document.getElementById('id_div_resultat_cohorte_2').innerHTML=cohorte_2;
			}
		},
		complete: function(requester){
			
		},
		error: function(){
				document.getElementById('id_div_resultat_cohorte_1').innerHTML='Erreur';
				document.getElementById('id_div_resultat_cohorte_intersect').innerHTML='';
				document.getElementById('id_div_resultat_cohorte_2').innerHTML='';
		}
	});
}

function afficher_outil (tool_num) {
	jQuery.ajax({
		type:"POST",
		url:"ajax_outils.php",
		async:true,
		data: { action:'afficher_outil',tool_num:tool_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} else {
				$("#id_div_description_outil").html(requester);
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		
}

function mapper_patient () {
	process_num=create_process ('debut','sysdate+4','mapper_patient') ;
	check_process_status (process_num,'id_journal_mapping_patient');
	liste_patient=document.getElementById("id_textarea_mapper_patient").value;
	option_limite=1;
	if (document.getElementById("id_option_limite").checked) {
		option_limite='2';
	}
	jQuery.ajax({
		type:"POST",
		url:"ajax_outils.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'mapper_patient',liste_patient:escape(liste_patient),process_num:process_num,option_limite:option_limite},
		beforeSend: function(requester){
				jQuery("#id_result_mapping_patient").html("<img src='images/chargement_mac.gif'>"); 
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("mapper_patient ()");
			} else {
				display_mapper_patient (process_num);
				//jQuery("#id_result_mapping_patient").html(contenu); 
				
			}
		}
	});
}

	

function display_mapper_patient (process_num) {
	jQuery.ajax({
		type:"POST",
		url:"ajax_outils.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'display_mapper_patient',process_num:process_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("display_mapper_patient ('"+process_num+"')");
			} else {
				if (contenu=='') {
					setTimeout("display_mapper_patient('"+process_num+"')",1000);
				} else {
					jQuery("#id_result_mapping_patient").html(contenu); 
				}
				
			}
		}
	});
}

	