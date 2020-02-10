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

	

function display_thesaurus() {
	display_type=jQuery('#display_type').val();
	
	if (display_type=='table') {
		display_thesaurus_table();
	} else {
		display_thesaurus_tree('0');
	}
}


function display_thesaurus_table() {
	data_search=jQuery('#id_thesaurus_data_search').val();
	thesaurus_code=jQuery('#id_thesaurus_code').val();
	
	if (data_search!='' || thesaurus_code!='') {
		jQuery.ajax({
			type:"POST",
			url:"ajax_admin.php",
			async:true,
			data: { action:'display_thesaurus_table',data_search:escape(data_search),thesaurus_code:thesaurus_code},
			beforeSend: function(requester){
					$("#id_div_result_thesaurus_data").empty();
					$("#id_div_result_thesaurus_data").css('display','block');
					$("#id_div_result_thesaurus_data").append("<img src='images/chargement_mac.gif'>");
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion("display_thesaurus_table()");
				} else {
					$("#id_div_result_thesaurus_data").empty();
					$("#id_div_result_thesaurus_data").append(requester);
					$("#id_table_list_thesaurus_data").dataTable( { "order": [[ 1, "asc" ]],"pageLength": 25});
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
}

function display_thesaurus_tree (thesaurus_data_num) {
	data_search=jQuery('#id_thesaurus_data_search').val();
	thesaurus_code=jQuery('#id_thesaurus_code').val();
	if( thesaurus_code=='') {
		alert(get_translation('SELECT_A_THESAURUS','Selectionnez un thesaurus'));
		return false;
	}
	if (thesaurus_data_num=='0') {
		div_result='id_div_result_thesaurus_data';
		$("#"+div_result).css('display','none');
	} else {
		div_result='id_span_thesaurus_son_'+thesaurus_data_num;
	}
	if ($("#"+div_result).css('display')=='none') {
		if (data_search!='' || thesaurus_code!='') {
			jQuery.ajax({
				type:"POST",
				url:"ajax_admin.php",
				async:true,
				data: { action:'display_thesaurus_tree',data_search:escape(data_search),thesaurus_code:thesaurus_code,thesaurus_data_num:thesaurus_data_num},
				beforeSend: function(requester){
						$("#"+div_result).empty();
						$("#"+div_result).css('display','block');
						$("#"+div_result).append("<img src='images/chargement_mac.gif'>");
				},
				success: function(requester){
					if (requester=='deconnexion') {
						afficher_connexion("display_thesaurus_tree('"+thesaurus_data_num+"')");
					} else {
						$("#"+div_result).empty();
						$("#"+div_result).append(requester);
					}
				},
				complete: function(requester){
				},
				error: function(){
				}
			});
		}
	} else {
		$("#"+div_result).css('display','none');
	}
}


function open_option_virtual_patient (id) {
	jQuery(".option_button_similarity").css("background-color","grey");
	if (jQuery("#"+id).css("display")=='block') {
		plier(id);
		plier('id_div_save_virtual_patient_done');
	} else {
		plier('id_div_patient_similarite_patient_options');
		plier('id_div_save_virtual_patient');
		plier('id_div_virtual_patient_list');
		plier('id_div_save_virtual_patient_done');
		deplier(id,'block');
		jQuery("#button_"+id).css("background-color","#00b2d7");
		if (id=='id_div_virtual_patient_list') {
			manage_virtual_patient();
		}
	}
	

}
function get_concept_info (concept_code) {
	jQuery.ajax({
		type:"POST",
		url:"ajax_outils.php",
		async:true,
		data: {action:'get_concept_info',concept_code:concept_code},
		beforeSend: function(requester){
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} else {
				jQuery('#id_list_concepts').append(requester);
				$('.js-concept-select').select2('open');
			}
		},
		complete: function(requester){
			jQuery(".js-concept-select").empty().trigger('change');	
		},
		error: function(){
		}
	});
}

function get_list_concept_virtual_patient (virtual_patient_num) {
	jQuery.ajax({
		type:"POST",
		url:"ajax_outils.php",
		async:true,
		data: {action:'get_list_concept_virtual_patient',virtual_patient_num:virtual_patient_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} else {
				jQuery('#id_list_concepts').html(requester);
			}
		},
		complete: function(requester){	
		},
		error: function(){
		}
	});
}
	
function delete_concept_similarity (concept_code) {
	jQuery('#id_span_concept_'+concept_code).remove();
}

function calculate_similarity () {
	list_code_concept='';
	list_code_concept_weight='';
	jQuery(".list_concept_code_used").each( function() {
		val=$(this).html();
		list_code_concept+=val+';';
		list_code_concept_weight+= jQuery("#id_weight_"+val).val()+";";
		
	});
	
	jQuery("#id_list_concept_selected").val(list_code_concept);
	jQuery("#id_list_concept_selected_weight").val(list_code_concept_weight);
	calculer_similarite_patient('');
}

function save_name_virtual_patient () {
	list_code_concept='';
	list_code_concept_weight='';
	jQuery(".list_concept_code_used").each( function() {
		val=$(this).html();
		list_code_concept+=val+';';
		list_code_concept_weight+= jQuery("#id_weight_"+val).val()+";";
		
	});
	name_virtual_patient = jQuery("#id_input_name_virtual_patient").val();	
	description = jQuery("#id_input_description_virtual_patient").val();	
	
	jQuery.ajax({
		type:"POST",
		url:"ajax_outils.php",
		async:true,
		data: {action:'save_name_virtual_patient',list_code_concept:list_code_concept,list_code_concept_weight:list_code_concept_weight,name_virtual_patient:escape(name_virtual_patient),description:escape(description)},
		beforeSend: function(requester){
			jQuery("#id_div_save_virtual_patient_done").css("display","none");	
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} else {
				jQuery("#id_div_save_virtual_patient").css("display","none");
				jQuery("#id_div_save_virtual_patient_done").css("display","block");
				jQuery("#id_input_name_virtual_patient").val("");
				jQuery("#id_input_description_virtual_patient").val("");
				manage_virtual_patient();
				get_list_patient_in_select ();
			}
		},
		complete: function(requester){
		},
		error: function(){
	}
});	
	
}




function manage_virtual_patient () {
	jQuery.ajax({
		type:"POST",
		url:"ajax_outils.php",
		async:true,
		data: {action:'manage_virtual_patient'},
		beforeSend: function(requester){
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} else {
				jQuery("#id_div_virtual_patient_list").html(requester);
			}
		},
		complete: function(requester){
		},
		error: function(){
	}
	});	
	
}

function delete_virtual_patient (virtual_patient_num) {
	if (confirm (get_translation('JS_REMOVE_VIRTUAL_PATIENT',"Etes-vous sur de vouloir supprimer ce patient virtuel")+'?')) {
		jQuery.ajax({
			type:"POST",
			url:"ajax_outils.php",
			async:true,
			data: {action:'delete_virtual_patient',virtual_patient_num:virtual_patient_num},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion();
				} else {
					jQuery("#id_tr_virtual_patient_"+virtual_patient_num).remove();
					get_list_patient_in_select ();
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});	
	}
}
function share_virtual_patient (virtual_patient_num) {
	if(jQuery('#id_checkbox_shared_vp_'+virtual_patient_num).is(':checked') ){
		shared=1;
	} else {
		shared=0;	
	}
	jQuery.ajax({
		type:"POST",
		url:"ajax_outils.php",
		async:true,
		data: {action:'share_virtual_patient',virtual_patient_num:virtual_patient_num,shared:shared},
		beforeSend: function(requester){
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} 
		},
		complete: function(requester){
		},
		error: function(){
		}
	});	
}

function get_list_patient_in_select () {
	jQuery.ajax({
		type:"POST",
		url:"ajax_outils.php",
		async:true,
		data: {action:'get_list_patient_in_select'},
		beforeSend: function(requester){
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} else {
				jQuery("#id_list_virtual_patient_select").html(requester);
				$("#id_list_virtual_patient_select").select2({
					placeholder:"search virtual patient",
					allowClear: true,
					language: "fr"
				});
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});
}
