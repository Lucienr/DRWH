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
function open_ecrf (patient_num,ecrf_num) {
	if (ecrf_num!='') {
		jQuery(".div_result").css("display","none");
		jQuery(".color-bullet").removeClass("current");
		jQuery('#id_div_patient_ecrf_patient').css('display','inline');
		jQuery("#id_bouton_ecrf_patient").addClass("current");
		afficher_onglet_ecrf_patient (patient_num,ecrf_num);
	}
}

function afficher_onglet_ecrf_patient (patient_num,ecrf_num) {
	if (tableau_onglet_deja_ouvert['ecrf_patient']!='ok') {
		tableau_onglet_deja_ouvert['ecrf_patient']='ok';
		jQuery.ajax({
			type:"POST",
			url:"ajax_ecrf.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'afficher_onglet_ecrf_patient',patient_num:patient_num},
			beforeSend: function(requester){
				jQuery("#id_div_patient_ecrf_extract").html("<img src='images/chargement_mac.gif'>"); 
			},
			success: function(requester){	
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("afficher_onglet_ecrf_patient('"+patient_num+"')");
				} else {
					jQuery("#id_div_patient_ecrf_extract").html(contenu);
					if (ecrf_num!='') {
						jQuery("#id_select_ecrf").val(ecrf_num);
						start_process_extract_information_ecrf (patient_num);
					}
					$('.chosen-select').chosen({width: '250px',max_selected_options: 50,allow_single_deselect: true,search_contains:true}); 
					$('.autosizejs').autosize();   
					jQuery('.chosen-select').trigger('chosen:updated');
					
					
				}
			}
		});
	}
}

function start_process_extract_information_ecrf (patient_num) {
	jQuery.ajax({
		type:"POST",
		url:"ajax_ecrf.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'start_process_information_ecrf'},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("start_process_extract_information_ecrf ('"+patient_num+"')");
			} else {
				process_num=contenu;
				jQuery('#id_afficher_document_ecrf').html("");
				get_process_extract_information_ecrf (process_num);
				extract_information_ecrf (patient_num,process_num);
			}
		}
	});
}

function get_process_extract_information_ecrf (process_num) {
	jQuery.ajax({
		type:"POST",
		url:"ajax_ecrf.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'get_process_extract_information_ecrf',process_num:process_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("get_process_extract_information_ecrf ('"+process_num+"')");
			} else {
				tab=contenu.split(';');
				status=tab[0];
				message=tab[1];
				if (status=='0') { 
					jQuery("#id_div_result_map_ecrf_process").html(message); 
					setTimeout("get_process_extract_information_ecrf('"+process_num+"')",1000);
				} else {
					jQuery("#id_div_result_map_ecrf_process").html(""); 
				}
			}
		}
	});
}

function extract_information_ecrf (patient_num,process_num) {
	ecrf_num=jQuery("#id_select_ecrf").val();
	next_patient_num=jQuery("#id_next_patient_num").val();
	cohort_num_patient=jQuery("#id_cohort_num_patient").val();
	jQuery.ajax({
		type:"POST",
		url:"ajax_ecrf.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'extract_information_ecrf',ecrf_num:ecrf_num,patient_num:patient_num,process_num:process_num,next_patient_num:next_patient_num,cohort_num_patient:cohort_num_patient},
		beforeSend: function(requester){
			jQuery("#id_div_result_map_ecrf").html("<img src='images/chargement_mac.gif'>"); 
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("extract_information_ecrf ('"+patient_num+"')");
			} else {
				jQuery("#id_div_result_map_ecrf").html(contenu); 
			}
		}
	});
}



function filtre_patient_text_ecrf (patient_num) {
	ecrf_num=jQuery('#id_select_ecrf').val();
	requete=jQuery('#id_input_ecrf_filtre_patient_text').val();
	requete=requete.replace(/\+/g,';plus;');
	//requete=requete.replace(/\\/g,';antislash;');
	if (requete=='') {
		jQuery("#id_afficher_list_document_ecrf").html(''); 
	} else {
		jQuery.ajax({
			type:"POST",
			url:"ajax_ecrf.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'filtre_patient_text_ecrf',requete:escape(requete),patient_num:patient_num,ecrf_num:ecrf_num},
			beforeSend: function(requester){
				jQuery("#id_afficher_list_document_ecrf").html("<img src='images/chargement_mac.gif'>"); 
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("filtre_patient_text_ecrf ('"+patient_num+"')");
				} else {
					jQuery("#id_afficher_list_document_ecrf").html(contenu); 
				}
			}
		});
	}
}


function afficher_document_patient_ecrf(document_num,id_voir,requete,k) {
	jQuery(".tr_document_patient").css("backgroundColor","#ffffff");
	jQuery(".tr_document_patient").css("fontWeight","normal");
	jQuery(".tr_document_patient").css("color","black");
	
	jQuery(".id_document_patient_"+document_num).css("fontWeight","bold");
	jQuery(".id_document_patient_"+document_num).css("color","#CB1B3E");
	jQuery(".id_document_patient_"+document_num).css("backgroundColor","#dcdff5");
	
	requete=requete.replace(/\+/g,';plus;');
	//requete=requete.replace(/\\/g,';antislash;');
	
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'afficher_document_patient',document_num:document_num,requete:escape(requete)},
		beforeSend: function(requester){
			jQuery("#id_input_ecrf_filtre_patient_text").val(''); 
			filtre_patient_text_ecrf(jQuery('#id_select_ecrf').val());
			jQuery("#".id_voir).html("<img src='images/chargement_mac.gif'>"); 
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("afficher_document_patient_ecrf ('"+document_num+"','"+id_voir+"','"+requete+"')");
				jQuery("#"+id_voir).html(""); 
			} else {
				jQuery("#"+id_voir).html("<img src='images/arrow_right_bottom.png'><br>"+contenu); 
				if (jQuery('#id_afficher_list_document_ecrf').css('display')=='block') {
					val_height_list_doc=jQuery('#id_afficher_list_document_ecrf').height();
				} else {
					val_height_list_doc=0;
				}
				val_top_list_doc=getTop(document.getElementById("id_afficher_list_document_ecrf"));
				val_bottom_list_doc=eval(val_top_list_doc+val_height_list_doc);
				val_top=getTop(document.getElementById("id_ancre_document_"+k));
				
				if (val_top<val_bottom_list_doc) {
					val_top_final=15;
				} else {
					val_top_final=val_top-val_bottom_list_doc;
				}
				
				jQuery('#id_afficher_document_ecrf').css('top',val_top_final);
				
				//window.location='#ancre_entete';
			}
			
		}
	});
}

function getFormData($form){
    //console.log($form);
    var unindexed_array = $form.serializeArray();
    var indexed_array = {};
    $.map(unindexed_array, function(n, i){
    	//console.log(n['name'] + ": " + n['value']);
        indexed_array[n['name']] = n['value'];
    });

    return indexed_array;
}

function save_ecrf_form (patient_num) {
	ecrf_num=jQuery("#id_select_ecrf").val();

	var json = "[" + JSON.stringify(getFormData(jQuery("#id_ecrf_form"))) + "]";
	
	jQuery.ajax({
		type:"POST",
		url:"ajax_ecrf.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'save_ecrf_form',ecrf_num:ecrf_num,json:escape(json),patient_num:patient_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("save_ecrf_form ()");
			} else {
				jQuery("#id_ecrf_loading_message").html( "Export des données vers l'e-CRF terminé.<br />" + contenu);
			}
			
		}
	});
	
}

var selected_text;
function getSelectedText(){ 
    if(window.getSelection){
    	selectionObj = window.getSelection();
    	begin = selectionObj.anchorOffset;
    	end = selectionObj.focusOffset;
    	if(end < begin) {
    		a = begin;
    		begin = end;
    		end = a;
    	}
   // 	console.log(begin);
    //	console.log(end);
        return window.getSelection().toString(); 
    } 
    else if(document.getSelection){ 
        return document.getSelection(); 
    } 
    else if(document.selection){ 
        return document.selection.createRange().text; 
    } 
} 

function ecrf_justify_my_choice () {
}


function validate_ecrf_item (ecrf_num, patient_num,ecrf_item_num) {
	var json = "[" + JSON.stringify(getFormData(jQuery("#id_ecrf_form"))) + "]";
	jQuery.ajax({
		type:"POST",
		url:"ajax_ecrf.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'validate_ecrf_item',ecrf_num:ecrf_num,patient_num:patient_num,ecrf_item_num:ecrf_item_num,json:escape(json)},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("validate_ecrf_item ("+ecrf_num+","+ patient_num+","+ecrf_item_num+")");
			} else {
				if (contenu=='validate') {
					jQuery("#id_tr_ecrf_item_"+ecrf_item_num).css( "background-color","#bfe6bf");
				} else {
					jQuery("#id_tr_ecrf_item_"+ecrf_item_num).css( "background-color","white");
				}
			}
			
		}
	});
}


jQuery(document).ready(function(){
  jQuery("#id_afficher_document_ecrf").mouseup(function(){
  	selected_text=getSelectedText();
  });
});

