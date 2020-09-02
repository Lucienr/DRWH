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
var index_haut='';
var index_preced='';
var index_courant='';
var translations='';

function get_translation (code,libelle_defaut) {
	if (translations=='') {
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			method: 'post',
			async:true,
			contentType: 'application/x-www-form-urlencoded',
			encoding: 'latin1',
			data: {action:'get_translations_json_string'},
			beforeSend: function(requester){
			},
			success: function(requester){
				translations = JSON.parse(requester);
			},
			error: function(){}
		});
	}

	if (typeof translations[code] != 'undefined') {
		return translations[code];
	}
	else if (libelle_defaut==''){
		return "[" + code + "]" ;
	}
	else{
		return libelle_defaut;
	}
	
}

function plier_deplier (id) {
	if (document.getElementById(id) ) {
		if (document.getElementById(id).style.display=='block') {
			document.getElementById(id).style.display='none';
			if (document.getElementById('plus_'+id)) {
				jQuery('#plus_'+id).html('+');
			}
		} else {
			document.getElementById(id).style.display='block';
			if (document.getElementById('plus_'+id)) {
				jQuery('#plus_'+id).html('-');
			}
		}
	
	}
}

function plier (id) {
	if (document.getElementById(id) ) {
		document.getElementById(id).style.display='none';
		if (document.getElementById('plus_'+id)) {
			jQuery('#plus_'+id).html('+');
		}
	}
}
function deplier (id,type) {
	if (type=='') {
		type='block';
	}
	if (document.getElementById(id) ) {
		document.getElementById(id).style.display=type;
		if (document.getElementById('plus_'+id)) {
			jQuery('#plus_'+id).html('-');
		}
	}
}

function focus_form (id) {
	if (document.getElementById(id) ) {
		document.getElementById(id).focus();
	}
}

var tableau_query_key_fait=new Array; 
var nb_num_filtre_en_cours=0;
function calcul_nb_resultat_filtre(num_filtre,val_async) {
	if (document.getElementById('id_span_nbresult_atomique_'+num_filtre)) {
	
		query_type=jQuery('#id_query_type_'+num_filtre).val();
		
		if (query_type=='text' || query_type=='code') {
			//textetrim=text.trim();
			text=jQuery('#id_input_filtre_texte_'+num_filtre).val();
			chaine_requete_code=jQuery('#id_input_chaine_requete_code_'+num_filtre).val();
			thesaurus_data_num=jQuery('#id_input_thesaurus_data_num_'+num_filtre).val();
			textetrim=text.replace(/^\s+|\s+$/g, ''); 
			
			jQuery('#id_span_nbresult_atomique_'+num_filtre).html('?');
			tab_service= $( "#id_select_filtre_unite_heberg_"+num_filtre).val() || [] ;
			if( typeof tab_service === 'string' ) {
				hospital_department_list=tab_service;
			} else {
				hospital_department_list=tab_service.join( "," );
			}
			tab_document_origin_code= $( "#id_select_filtre_document_origin_code_"+num_filtre).val() || [] ;
			if( typeof tab_document_origin_code === 'string' ) {
				document_origin_code=tab_document_origin_code;
			} else {
				document_origin_code=tab_document_origin_code.join( "," );
			}
			tab_document_type= $( "#id_select_filtre_document_type_"+num_filtre).val() || [] ;
			if( typeof tab_document_type === 'string' ) {
				document_type=tab_document_type;
			} else {
				document_type=tab_document_type.join( "," );
			}
			title_document=jQuery('#id_input_filtre_title_document_'+num_filtre).val();
			date_deb_document=jQuery('#id_input_filtre_date_deb_document_'+num_filtre).val();
			date_fin_document=jQuery('#id_input_filtre_date_fin_document_'+num_filtre).val();
			document_last_nb_days=jQuery('#id_input_filtre_document_last_nb_days_'+num_filtre).val();
			periode_document=jQuery('#id_input_filtre_periode_document_'+num_filtre).val();
			age_deb_document=jQuery('#id_input_filtre_age_deb_document_'+num_filtre).val();
			age_fin_document=jQuery('#id_input_filtre_age_fin_document_'+num_filtre).val();
			stay_length_min=jQuery('#id_input_filtre_stay_length_min_'+num_filtre).val();
			stay_length_max=jQuery('#id_input_filtre_stay_length_max_'+num_filtre).val();
			agemois_deb_document=jQuery('#id_input_filtre_agemois_deb_document_'+num_filtre).val();
			agemois_fin_document=jQuery('#id_input_filtre_agemois_fin_document_'+num_filtre).val();
			context=jQuery('#id_select_filtre_contexte_'+num_filtre).val();
			certainty=jQuery('#id_select_filtre_certitude_'+num_filtre).val();
			if (jQuery('#id_select_filtre_certitude_'+num_filtre).prop('checked')==true) {
				exclure=1;
			} else {
				exclure='';
			}
			if (jQuery('#id_checkbox_etendre_syno_'+num_filtre).prop('checked')==true) {
				etendre_syno=1;
			} else {
				etendre_syno='';
			}
				
			if (textetrim.length>1 || chaine_requete_code!=''
			|| hospital_department_list!='' 
			|| document_origin_code!='' 
			|| document_type!='' 
			|| title_document!='' 
			|| (date_deb_document!='' && date_fin_document!='' )
			|| document_last_nb_days!='' 
			|| periode_document!='' 
			|| (age_deb_document!='' && age_fin_document!='' )
			|| (stay_length_min!='' && stay_length_max!='' )
			|| (agemois_deb_document!='' && agemois_fin_document!='' )
			) {
			
				datamart_num=jQuery('#id_num_datamart').val();
				plier('id_bouton_submit_moteur');
				deplier('id_bouton_attendre_moteur','block');
				nb_num_filtre_en_cours++;
				jQuery.ajax({
					type:"POST",
					url:"ajax.php",
					async:val_async,
					encoding: 'latin1',
					data:{ action:'calcul_nb_resultat_filtre_passthru',num_filtre:num_filtre,text:escape(text),etendre_syno:etendre_syno,query_type:query_type,thesaurus_data_num:thesaurus_data_num,chaine_requete_code:chaine_requete_code,hospital_department_list:escape(hospital_department_list),date_deb_document:escape(date_deb_document),date_fin_document:escape(date_fin_document),periode_document:periode_document,age_deb_document:escape(age_deb_document),age_fin_document:escape(age_fin_document),agemois_deb_document:escape(agemois_deb_document),agemois_fin_document:escape(agemois_fin_document),context:escape(context),certainty:escape(certainty),document_origin_code:escape(document_origin_code),document_type:escape(document_type),datamart_num:datamart_num,exclure:exclure,title_document:escape(title_document),document_last_nb_days:escape(document_last_nb_days),stay_length_min:escape(stay_length_min),stay_length_max:escape(stay_length_max)},
					beforeSend: function(requester){
						jQuery('#id_span_nbresult_atomique_chargement_'+num_filtre).html('<img src="images/chargement_mac.gif" width="10px">');
						jQuery('#id_span_nbresult_atomique_'+num_filtre).css('color','red');
					},
					success: function(requester){
						var contenu=requester;
						if (contenu=='deconnexion') {
							afficher_connexion("calcul_nb_resultat_filtre("+num_filtre+","+val_async+")");
						} else { 
							jQuery('#id_query_key_'+num_filtre).val(contenu);
							setTimeout("calcul_nb_resultat_final_passthru('"+num_filtre+"','"+contenu+"')",1000);
						}
					},
					complete: function(requester){
						
					},
					error: function(){
						jQuery('#id_span_nbresult_atomique_chargement_'+num_filtre).html('Erreur');
					}
				});
			}
		}
	}
}


function calcul_nb_resultat_filtre_mvt(num_filtre,val_async) {
	if (document.getElementById('id_span_nbresult_atomique_'+num_filtre)) {
	
		query_type=jQuery('#id_query_type_'+num_filtre).val();
		
		if (query_type=='mvt') {
			jQuery('#id_span_nbresult_atomique_'+num_filtre).html('?');
			
			mvt_department=jQuery('#id_select_filtre_mvt_department_'+num_filtre).val();
			mvt_unit=jQuery('#id_select_filtre_mvt_unit_'+num_filtre).val();
			type_mvt=jQuery('#id_select_filtre_type_mvt_'+num_filtre).val();
			encounter_duration_min=jQuery('#id_input_filtre_encounter_duration_min_'+num_filtre).val();
			encounter_duration_max=jQuery('#id_input_filtre_encounter_duration_max_'+num_filtre).val();
			mvt_duration_min=jQuery('#id_input_filtre_mvt_duration_min_'+num_filtre).val();
			mvt_duration_max=jQuery('#id_input_filtre_mvt_duration_max_'+num_filtre).val();
			mvt_nb_min=jQuery('#id_input_filtre_mvt_nb_min_'+num_filtre).val();
			mvt_nb_max=jQuery('#id_input_filtre_mvt_nb_max_'+num_filtre).val();
			stay_nb_min=jQuery('#id_input_filtre_stay_nb_min_'+num_filtre).val();
			stay_nb_max=jQuery('#id_input_filtre_stay_nb_max_'+num_filtre).val();
			mvt_last_nb_days=jQuery('#id_input_filtre_mvt_last_nb_days_'+num_filtre).val();
			mvt_date_start=jQuery('#id_input_filtre_mvt_date_start_'+num_filtre).val();
			mvt_date_end=jQuery('#id_input_filtre_mvt_date_end_'+num_filtre).val();
			mvt_ageyear_start=jQuery('#id_input_filtre_mvt_ageyear_start_'+num_filtre).val();
			mvt_ageyear_end=jQuery('#id_input_filtre_mvt_ageyear_end_'+num_filtre).val();
			mvt_agemonth_start=jQuery('#id_input_filtre_mvt_agemonth_start_'+num_filtre).val();
			mvt_agemonth_end=jQuery('#id_input_filtre_mvt_agemonth_end_'+num_filtre).val();
			
			if (mvt_department!='' ||
				mvt_unit!='' ||
				type_mvt!='' ||
				encounter_duration_min!='' ||
				encounter_duration_max!='' ||
				mvt_duration_min!='' ||
				mvt_duration_max!='' ||
				mvt_nb_min!='' ||
				mvt_nb_max!='' ||
				stay_nb_min!='' ||
				stay_nb_max!='' ||
				mvt_last_nb_days!='' ||
				mvt_date_start!='' ||
				mvt_date_end!='' ||
				mvt_ageyear_start!='' ||
				mvt_ageyear_end!='' ||
				mvt_agemonth_start!='' ||
				mvt_agemonth_end!='') {
	
				if (jQuery('#id_select_filtre_certitude_'+num_filtre).prop('checked')==true) {
					exclure=1;
				} else {
					exclure='';
				}
				datamart_num=jQuery('#id_num_datamart').val();
				plier('id_bouton_submit_moteur');
				deplier('id_bouton_attendre_moteur','block');
				nb_num_filtre_en_cours++;
				jQuery.ajax({
					type:"POST",
					url:"ajax.php",
					async:val_async,
					encoding: 'latin1',
					data:{ action:'calcul_nb_resultat_filtre_mvt_passthru',
					num_filtre:num_filtre,
					query_type:query_type,
					mvt_department:escape(mvt_department),
					mvt_unit:escape(mvt_unit),
					type_mvt:escape(type_mvt),
					encounter_duration_min:escape(encounter_duration_min),
					encounter_duration_max:escape(encounter_duration_max),
					mvt_duration_min:escape(mvt_duration_min),
					mvt_duration_max:escape(mvt_duration_max),
					mvt_nb_min:escape(mvt_nb_min),
					mvt_nb_max:escape(mvt_nb_max),
					stay_nb_min:escape(stay_nb_min),
					stay_nb_max:escape(stay_nb_max),
					mvt_last_nb_days:escape(mvt_last_nb_days),
					mvt_date_start:escape(mvt_date_start),
					mvt_date_end:escape(mvt_date_end),
					mvt_ageyear_start:escape(mvt_ageyear_start),
					mvt_ageyear_end:escape(mvt_ageyear_end),
					mvt_agemonth_start:escape(mvt_agemonth_start),
					mvt_agemonth_end:escape(mvt_agemonth_end),
					datamart_num:datamart_num,
					exclure:exclure
					},
					beforeSend: function(requester){
						jQuery('#id_span_nbresult_atomique_chargement_'+num_filtre).html('<img src="images/chargement_mac.gif" width="10px">');
						jQuery('#id_span_nbresult_atomique_'+num_filtre).css('color','red');
					},
					success: function(requester){
						var contenu=requester;
						if (contenu=='deconnexion') {
							afficher_connexion("calcul_nb_resultat_filtre_mvt("+num_filtre+","+val_async+")");
						} else { 
							jQuery('#id_query_key_'+num_filtre).val(contenu);
							setTimeout("calcul_nb_resultat_final_passthru('"+num_filtre+"','"+contenu+"')",1000);
						}
					},
					complete: function(requester){
						
					},
					error: function(){
						jQuery('#id_span_nbresult_atomique_chargement_'+num_filtre).html('');
					}
				});
			}
		}
	}
}


function verification_calcul_nb_resultat_filtre_fini(num_filtre,val_async) {
	if (nb_num_filtre_en_cours==0) {
		calcul_nb_resultat_contrainte_temporelle(num_filtre,val_async)
	} else {
		setTimeout("verification_calcul_nb_resultat_filtre_fini('"+num_filtre+"','"+val_async+"');",500);
	}

}

function calcul_nb_resultat_contrainte_temporelle(num_filtre,val_async) {
	datamart_num=jQuery('#id_num_datamart').val();
	if (document.getElementById('id_query_key_contrainte_temporelle_'+num_filtre)) {
		query_key_contrainte_temporelle=jQuery('#id_query_key_contrainte_temporelle_'+num_filtre).val();
		tab=query_key_contrainte_temporelle.split(';');
		num_filtre_a=tab[1];
		num_filtre_b=tab[2];
		query_key_a=jQuery('#id_query_key_'+num_filtre_a).val();
		query_key_b=jQuery('#id_query_key_'+num_filtre_b).val();
		if (query_key_a=='' || query_key_b=='') {
			jQuery('#id_span_nbresult_atomique_chargement_'+num_filtre).html('<img src="images/chargement_mac.gif" width="10px">');
			calcul_nb_resultat_filtre (num_filtre_a,false);
			calcul_nb_resultat_filtre (num_filtre_b,false);
			verification_calcul_nb_resultat_filtre_fini(num_filtre,val_async);
			return ;
		}
		
		if (query_key_contrainte_temporelle!='' && query_key_a!='' && query_key_b!='') {
			jQuery('#id_span_nbresult_atomique_'+num_filtre).html('?');
			plier('id_bouton_submit_moteur');
			deplier('id_bouton_attendre_moteur','block');
			nb_num_filtre_en_cours++;
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:val_async,
				encoding: 'latin1',
				data:{ action:'calcul_nb_resultat_contrainte_temporelle_passthru',num_filtre:num_filtre,query_key_contrainte_temporelle:query_key_contrainte_temporelle,query_key_a:query_key_a,query_key_b:query_key_b,datamart_num:datamart_num},
				beforeSend: function(requester){
					jQuery('#id_span_nbresult_atomique_chargement_'+num_filtre).html('<img src="images/chargement_mac.gif" width="10px">');
					jQuery('#id_span_nbresult_atomique_'+num_filtre).css('color','red');
				},
				success: function(requester){
					var contenu=requester;
					if (contenu=='deconnexion') {
						afficher_connexion("calcul_nb_resultat_contrainte_temporelle("+num_filtre+","+val_async+")");
					} else { 
						jQuery('#id_query_key_'+num_filtre).val(contenu);
						setTimeout("calcul_nb_resultat_final_passthru('"+num_filtre+"','"+contenu+"')",1000);
					}
				},
				complete: function(requester){
					
				},
				error: function(){
					jQuery('#id_span_nbresult_atomique_chargement_'+num_filtre).html('');
				}
			});
		}
	}
}




function calcul_nb_resultat_final_passthru(num_filtre,query_key) {
	datamart_num=jQuery('#id_num_datamart').val();
	plier('id_bouton_submit_moteur');
	deplier('id_bouton_attendre_moteur','block');
	if (document.getElementById('id_query_key_'+num_filtre)) {
		query_key_debut=jQuery('#id_query_key_'+num_filtre).val();
		if (query_key==query_key_debut) {
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:true,
				encoding: 'latin1',
				data:{ action:'calcul_nb_resultat_final_passthru',query_key:escape(query_key),datamart_num:datamart_num},
				beforeSend: function(requester){
				},
				success: function(requester){
					var contenu=requester;
					if (contenu=='deconnexion') {
						afficher_connexion("calcul_nb_resultat_final_passthru("+num_filtre+",'"+query_key+"')");
					} else {
						tab=contenu.split(';');
						nb_patient=tab[0];
						status_calculate=tab[1];
						nb_patient_total=tab[2];
						if (document.getElementById('id_span_nbresult_atomique_'+num_filtre) || document.getElementById('id_span_nbresult_atomique_'+num_filtre)) {
							jQuery('#id_span_nbresult_atomique_'+num_filtre).html(nb_patient+"/"+nb_patient_total);
							jQuery('#id_input_nbresult_atomique_'+num_filtre).val(nb_patient);
							if (status_calculate==1) {
								jQuery('#id_span_nbresult_atomique_chargement_'+num_filtre).html('');
								jQuery('#id_span_nbresult_atomique_'+num_filtre).css('color','green');
								if (nb_patient_total<=5 && document.getElementById('id_input_filtre_texte_'+num_filtre)) {
									if (document.getElementById('id_input_filtre_texte_'+num_filtre).style.display!='none' ) {
										correction_orthographique(num_filtre);
									}
								}
								tableau_query_key_fait[query_key]='ok';
								nb_num_filtre_en_cours--;
								if (nb_num_filtre_en_cours==0) {
									deplier('id_bouton_submit_moteur','block');
									plier('id_bouton_attendre_moteur');
								}
								if (nb_patient_total=='') {
									jQuery('#id_span_nbresult_atomique_'+num_filtre).html('?');								
								}
								// verification si num filtre utiliser dans contrainte temporelle
								
								num_filtre_max=eval(jQuery('#id_input_max_num_filtre').val());
								for (num_filtre_i=1;num_filtre_i<=num_filtre_max;num_filtre_i++) {
									if (jQuery('#id_div_filtre_contrainte_temporelle_'+num_filtre_i).length) {
										if (jQuery('#id_div_filtre_contrainte_temporelle_'+num_filtre_i).html()!='') {
											query_key_contrainte_temporelle=jQuery('#id_query_key_contrainte_temporelle_'+num_filtre_i).val();
											tab=query_key_contrainte_temporelle.split(';');
											num_filtre_a=tab[1];
											num_filtre_b=tab[2];
											if (num_filtre==num_filtre_a || num_filtre==num_filtre_b) {
												calcul_nb_resultat_contrainte_temporelle(num_filtre_i,true);
											}
										}
									}
								}
								
							} else {
								tableau_query_key_fait[query_key]='';
								setTimeout("calcul_nb_resultat_final_passthru('"+num_filtre+"','"+query_key+"')",1000);
							}
						} else {
							nb_num_filtre_en_cours--;
							if (nb_num_filtre_en_cours==0) {
								deplier('id_bouton_submit_moteur','block');
								plier('id_bouton_attendre_moteur');
							} else {
							
							}
						}
					}
				},
				complete: function(requester){
					
				},
				error: function(){
					jQuery('#id_span_nbresult_atomique_chargement_'+num_filtre).html('');
				}
			});
		} else {
			nb_num_filtre_en_cours--;
			if (nb_num_filtre_en_cours==0) {
				deplier('id_bouton_submit_moteur','block');
				plier('id_bouton_attendre_moteur');
			}
		}
	} else {
		nb_num_filtre_en_cours--;
		if (nb_num_filtre_en_cours==0) {
			deplier('id_bouton_submit_moteur','block');
			plier('id_bouton_attendre_moteur');
		}
	}
}

function correction_orthographique(num_filtre) {
	
	text=jQuery('#id_input_filtre_texte_'+num_filtre).val();
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'correction_orthographique',text:escape(text),num_filtre:num_filtre},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("correction_orthographique("+num_filtre+")");
			} else {			
				deplier("id_div_correction_orthographique_"+num_filtre,'block');
				jQuery('#id_div_correction_orthographique_propositions_'+num_filtre).html(contenu);
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});
}

function remplacer_texte (num_filtre,text) {
	jQuery('#id_input_filtre_texte_'+num_filtre).val(text);
	calcul_nb_resultat_filtre (num_filtre,true);
}

function calcul_nb_resultat_filtre_notpassthru(num_filtre,val_async) {
	if (document.getElementById('id_span_nbresult_atomique_'+num_filtre)) {
	
		jQuery('#id_span_nbresult_atomique_'+num_filtre).html('');
		text=jQuery('#id_input_filtre_texte_'+num_filtre).val();
		chaine_requete_code=jQuery('#id_input_chaine_requete_code_'+num_filtre).val();
		thesaurus_data_num=jQuery('#id_input_thesaurus_data_num_'+num_filtre).val();
		id_query_type=jQuery('#id_query_type_'+num_filtre).val();
		//textetrim=text.trim();
		textetrim=text.replace(/^\s+|\s+$/g, ''); 
		if (textetrim.length>1 || chaine_requete_code!='') {
			tab_service= $( "#id_select_filtre_unite_heberg_"+num_filtre).val() || [] ;
			if( typeof tab_service === 'string' ) {
				hospital_department_list=tab_service;
			} else {
				hospital_department_list=tab_service.join( "," );
			}
			tab_document_origin_code= $( "#id_select_filtre_document_origin_code_"+num_filtre).val() || [] ;
			if( typeof tab_document_origin_code === 'string' ) {
				document_origin_code=tab_document_origin_code;
			} else {
				document_origin_code=tab_document_origin_code.join( "," );
			}
			tab_document_type= $( "#id_select_filtre_document_type_"+num_filtre).val() || [] ;
			if( typeof tab_document_type === 'string' ) {
				document_type=tab_document_type;
			} else {
				document_type=tab_document_type.join( "," );
			}
			title_document=jQuery('#id_input_filtre_title_document_'+num_filtre).val();
			date_deb_document=jQuery('#id_input_filtre_date_deb_document_'+num_filtre).val();
			date_fin_document=jQuery('#id_input_filtre_date_fin_document_'+num_filtre).val();
			document_last_nb_days=jQuery('#id_input_filtre_document_last_nb_days_'+num_filtre).val();
			periode_document=jQuery('#id_input_filtre_periode_document_'+num_filtre).val();
			age_deb_document=jQuery('#id_input_filtre_age_deb_document_'+num_filtre).val();
			age_fin_document=jQuery('#id_input_filtre_age_fin_document_'+num_filtre).val();
			stay_length_min=jQuery('#id_input_filtre_stay_length_min_'+num_filtre).val();
			stay_length_max=jQuery('#id_input_filtre_stay_length_max_'+num_filtre).val();
			agemois_deb_document=jQuery('#id_input_filtre_agemois_deb_document_'+num_filtre).val();
			agemois_fin_document=jQuery('#id_input_filtre_agemois_fin_document_'+num_filtre).val();
			context=jQuery('#id_select_filtre_contexte_'+num_filtre).val();
			certainty=jQuery('#id_select_filtre_certitude_'+num_filtre).val();
			if (document.getElementById('id_input_filtre_exclure_'+num_filtre).checked==true) {
				exclure=jQuery('#id_input_filtre_exclure_'+num_filtre).val();
			} else {
				exclure='';
			}
			if (document.getElementById('id_checkbox_etendre_syno_'+num_filtre).checked==true) {
				etendre_syno=jQuery('#id_checkbox_etendre_syno_'+num_filtre).val();
			} else {
				etendre_syno='';
			}
			//document_origin_code=jQuery('#id_select_filtre_document_origin_code_'+num_filtre).val();
			
			datamart_num=jQuery('#id_num_datamart').val();
			plier('id_bouton_submit_moteur');
			deplier('id_bouton_attendre_moteur','block');
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:val_async,
				encoding: 'latin1',
				data:{ action:'calcul_nb_resultat_filtre',num_filtre:num_filtre,text:escape(text),etendre_syno:etendre_syno,id_query_type:id_query_type,thesaurus_data_num:thesaurus_data_num,chaine_requete_code:chaine_requete_code,hospital_department_list:escape(hospital_department_list),date_deb_document:escape(date_deb_document),date_fin_document:escape(date_fin_document),periode_document:periode_document,age_deb_document:escape(age_deb_document),age_fin_document:escape(age_fin_document),agemois_deb_document:escape(agemois_deb_document),agemois_fin_document:escape(agemois_fin_document),context:escape(context),certainty:escape(certainty),document_origin_code:escape(document_origin_code),document_type:escape(document_type),datamart_num:datamart_num,exclure:exclure,title_document:escape(title_document),document_last_nb_days:escape(document_last_nb_days),stay_length_min:escape(stay_length_min),stay_length_max:escape(stay_length_max)},
				beforeSend: function(requester){
					jQuery('#id_span_nbresult_atomique_chargement_'+num_filtre).html('<img src="images/chargement_mac.gif" width="10px">');
				},
				success: function(requester){
					var contenu=requester;
					if (contenu=='deconnexion') {
						afficher_connexion("calcul_nb_resultat_filtre_notpassthru("+num_filtre+","+val_async+")");
					} else {
						jQuery('#id_span_nbresult_atomique_'+num_filtre).html(contenu);
						jQuery('#id_input_nbresult_atomique_'+num_filtre).val(contenu);
					}
					jQuery('#id_span_nbresult_atomique_chargement_'+num_filtre).html('');
					deplier('id_bouton_submit_moteur','block');
					plier('id_bouton_attendre_moteur');
				},
				complete: function(requester){
					
				},
				error: function(){
					jQuery('#id_span_nbresult_atomique_chargement_'+num_filtre).html('');
				}
			});
		}
	}
}

function add_atomic_query(query_type) {
	num_filtre=eval(jQuery('#id_input_max_num_filtre').val());
	for (num_filtre_i=1;num_filtre_i<=num_filtre;num_filtre_i++) {
		if (jQuery('#id_div_filtre_texte_'+num_filtre_i).length) {
			calcul_nb_resultat_filtre (num_filtre_i,true);
		}
		if (jQuery('#id_div_filtre_mvt_'+num_filtre_i).length) {
			calcul_nb_resultat_filtre_mvt (num_filtre_i,true);
		}
	}
	
	num_filtre=eval(num_filtre+1);
	jQuery('#id_input_max_num_filtre').val(num_filtre);
	
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:false,
		encoding: 'latin1',
		data:{ action:'add_atomic_query',num_filtre:num_filtre,query_type:query_type},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("add_atomic_query('"+query_type+"')");
			} else {
				$("#id_div_formulaire_document").append(contenu);
				$(".chosen-select").chosen({width: "250px",max_selected_options: 50}); 
	    			$('.autosizejs').autosize();   
				ajouter_filtre_select_contrainte();
			}
		},
		complete: function(requester){
			
		},
		error: function(){
		}
	});
}

function add_atomic_query_mvt() {
	num_filtre=eval(jQuery('#id_input_max_num_filtre').val());
	for (num_filtre_i=1;num_filtre_i<=num_filtre;num_filtre_i++) {
		if (jQuery('#id_div_filtre_texte_'+num_filtre_i).length) {
			calcul_nb_resultat_filtre (num_filtre_i,true);
		}
		if (jQuery('#id_div_filtre_mvt_'+num_filtre_i).length) {
			calcul_nb_resultat_filtre_mvt (num_filtre_i,true);
		}
	}
	num_filtre=eval(num_filtre+1);
	jQuery('#id_input_max_num_filtre').val(num_filtre);
	
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:false,
		encoding: 'latin1',
		data:{ action:'add_atomic_query_mvt',num_filtre:num_filtre},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("add_atomic_query_mvt()");
			} else {
				$("#id_div_formulaire_document").append(contenu);
				$(".chosen-select").chosen({width: "250px",allow_single_deselect: true,max_selected_options: 50}); 
	    			$('.autosizejs').autosize();   
				ajouter_filtre_select_contrainte();
			}
		},
		complete: function(requester){
			
		},
		error: function(){
		}
	});
}

function supprimer_formulaire_texte_vierge(num_filtre) {
	jQuery('#id_div_filtre_texte_'+num_filtre).html('');
	document.getElementById('id_div_filtre_texte_'+num_filtre).style.display='none';
	ajouter_filtre_select_contrainte();
}

function supprimer_formulaire_contrainte_temporelle(num_filtre) {
	jQuery('#id_div_filtre_contrainte_temporelle_'+num_filtre).html('');
	document.getElementById('id_div_filtre_contrainte_temporelle_'+num_filtre).style.display='none';
	ajouter_filtre_select_contrainte();
}

function delete_atomic_query_mvt(num_filtre) {
	jQuery('#id_div_filtre_mvt_'+num_filtre).html('');
	document.getElementById('id_div_filtre_mvt_'+num_filtre).style.display='none';
	ajouter_filtre_select_contrainte();
}

function ajouter_contrainte_temporelle () {
	jQuery('#id_span_alerte').html("");
	num_filtre_a=jQuery('#id_contrainte_select_liste_sous_requete_a').val();
	num_filtre_b=jQuery('#id_contrainte_select_liste_sous_requete_b').val();
	if (num_filtre_a==num_filtre_b || num_filtre_a=='' || num_filtre_b=='' ) {
		jQuery('#id_span_alerte').html( get_translation('JS_IL_FAUT_CHOISIR_DEUX_FILTRES_DIFFERENTS','Il faut choisir deux filtres différents'));
		return false;
	}
	minmax='';
	duree_contrainte='';
	unite_contrainte='';
	type_contrainte='';
	if (document.getElementById('id_contrainte_type_contrainte_stay').checked) {
		type_contrainte='stay';
	}
	if (document.getElementById('id_contrainte_type_contrainte_simultaneous').checked) {
		type_contrainte='simultaneous';
	}
	if (document.getElementById('id_contrainte_type_contrainte_beforeafter').checked) {
		minmax=jQuery('#id_detail_contrainte_beforeafter_minmax').val();
		duree_contrainte=jQuery('#id_detail_contrainte_beforeafter_duree').val();
		unite_contrainte=jQuery('#id_detail_contrainte_beforeafter_unite').val();
		type_contrainte='beforeafter';
		if (duree_contrainte=='') {
			jQuery('#id_span_alerte').html( get_translation('JS_IL_FAUT_PRECISER_UNE_DUREE','Il faut préciser une durée'));
			return false;
		}
	}
	if (document.getElementById('id_contrainte_type_contrainte_periode').checked) {
		minmax=jQuery('#id_detail_contrainte_periode_minmax').val();
		duree_contrainte=jQuery('#id_detail_contrainte_periode_duree').val();
		unite_contrainte=jQuery('#id_detail_contrainte_periode_unite').val();
		type_contrainte='periode';
		if (duree_contrainte=='') {
			jQuery('#id_span_alerte').html(get_translation('JS_IL_FAUT_CHOISIR_UNE_DUREE','Il faut choisir une durée'));
			return false;
		}
	}
	if (type_contrainte=='') {
		jQuery('#id_span_alerte').html(get_translation('JS_IL_FAUT_PRECISER_UN_TYPE_DE_CONTRAINTE','Il faut préciser un type de contrainte'));
		return false;
	}

	num_filtre=eval(jQuery('#id_input_max_num_filtre').val());

	num_filtre=eval(num_filtre+1);
	jQuery('#id_input_max_num_filtre').val(num_filtre);
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:false,
		encoding: 'latin1',
		data:{ action:'ajouter_contrainte_temporelle',num_filtre:num_filtre,num_filtre_a:num_filtre_a,num_filtre_b:num_filtre_b,type_contrainte:type_contrainte,minmax:minmax,unite_contrainte:unite_contrainte,duree_contrainte:duree_contrainte},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("ajouter_contrainte_temporelle()");
			} else {
				$("#id_div_formulaire_document").append(contenu);
					for (num_filtre_i=1;num_filtre_i<=num_filtre;num_filtre_i++) {
						if (jQuery('#id_div_filtre_texte_'+num_filtre_i).length) {
							calcul_nb_resultat_filtre (num_filtre_i,true);
						}
						if (jQuery('#id_div_filtre_mvt_'+num_filtre_i).length) {
							calcul_nb_resultat_filtre_mvt (num_filtre_i,true);
						}
					}
				
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});

}

function ajouter_contrainte_temporelle_auto (num_filtre,num_filtre_a,num_filtre_b,type_contrainte,minmax,unite_contrainte,duree_contrainte) {
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:false,
		encoding: 'latin1',
		data:{ action:'ajouter_contrainte_temporelle',num_filtre:num_filtre,num_filtre_a:num_filtre_a,num_filtre_b:num_filtre_b,type_contrainte:type_contrainte,minmax:minmax,unite_contrainte:unite_contrainte,duree_contrainte:duree_contrainte},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("ajouter_contrainte_temporelle()");
			} else {
				$("#id_div_formulaire_document").append(contenu);
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});

}
function ajouter_filtre_select_contrainte () {
	$('#id_contrainte_select_liste_sous_requete_a option').remove();
	$('#id_contrainte_select_liste_sous_requete_b option').remove();
	num_filtre_max=eval(document.getElementById('id_input_max_num_filtre').value);
	$('#id_contrainte_select_liste_sous_requete_a').append($('<option>', {
	    value: '',
	    text: 'Sélectionner un filtre'
	}));
	$('#id_contrainte_select_liste_sous_requete_b').append($('<option>', {
	    value: '',
	    text: 'Sélectionner un filtre'
	}));
	for (i=1;i<=num_filtre_max;i++) {
		if (document.getElementById('id_div_filtre_texte_'+i) || document.getElementById('id_div_filtre_mvt_'+i)) {
			$('#id_contrainte_select_liste_sous_requete_a').append($('<option>', {
			    value: i,
			    text: 'Filtre '+i
			}));
			$('#id_contrainte_select_liste_sous_requete_b').append($('<option>', {
			    value: i,
			    text: 'Filtre '+i
			}));
		
		}
	}
}


function exclure_cohorte_resultat() {
	if (jQuery('#id_exclure_cohorte_resultat').val()=='') {
		val_exclure_cohorte_resultat='ok';
		document.getElementById('id_img_exclure_cohorte_resultat').src='images/voir_tout.png';
		jQuery('#id_texte_exclure_cohorte_resultat').html(get_translation('JS_AFFICHER_TOUS_LES_PATIENTS','Afficher tous les patients'));
	} else {
		val_exclure_cohorte_resultat='';
		document.getElementById('id_img_exclure_cohorte_resultat').src='images/pas_voir_tout.png';
		jQuery('#id_texte_exclure_cohorte_resultat').html(get_translation('JS_NE_PAS_AFFICHER_LES_PATIENTS_DEJA_INCLUS_OU_EXCLUS','Ne pas afficher les patients déjà inclus ou exclus'));
	}
	datamart_num=jQuery('#id_num_datamart').val();
	jQuery('#id_num_last_ligne').val(0);
	jQuery('#id_exclure_cohorte_resultat').val(val_exclure_cohorte_resultat);
	cohort_num_encours=jQuery('#id_cohort_num_encours').val();
	tmpresult_num=jQuery('#id_num_temp').val();
	filtre_resultat_texte=jQuery('#id_filtre_resultat_texte').val();
	jQuery('#id_tableau_resultat').empty();
	jQuery("#id_span_afficher_suite").css('display','none');
	jQuery("#id_span_chargement").css('display','block');
	
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:false,
		encoding: 'latin1',
		data:{ action:'calcul_nb_patient_resultat',tmpresult_num:tmpresult_num,val_exclure_cohorte_resultat:val_exclure_cohorte_resultat,cohort_num_encours:cohort_num_encours,datamart_num:datamart_num,filtre_resultat_texte:escape(filtre_resultat_texte)},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("exclure_cohorte_resultat()");
			} else {
				jQuery('#id_total_ligne').val(requester);
				afficher_suite();
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});
	
}

function ajouter_filtre(filtre) {
	self.location="#ancre_entete_moteur";
	voir_detail_dwh('resultat_detail');
	jQuery('#id_filtre_resultat_texte').val(filtre);
	filtrer_resultat();
}

function filtrer_resultat() {
	datamart_num=jQuery('#id_num_datamart').val();
	jQuery('#id_num_last_ligne').val(0);
	val_exclure_cohorte_resultat=jQuery('#id_exclure_cohorte_resultat').val();
	cohort_num_encours=jQuery('#id_cohort_num_encours').val();
	tmpresult_num=jQuery('#id_num_temp').val();
	filtre_resultat_texte=jQuery('#id_filtre_resultat_texte').val();
	jQuery('.class_document').css('display','none');
	jQuery('#id_tableau_resultat').empty();
	jQuery("#id_span_afficher_suite").css('display','none');
	jQuery("#id_span_chargement").css('display','block');
	
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'calcul_nb_patient_resultat',tmpresult_num:tmpresult_num,val_exclure_cohorte_resultat:val_exclure_cohorte_resultat,cohort_num_encours:cohort_num_encours,datamart_num:datamart_num,filtre_resultat_texte:escape(filtre_resultat_texte)},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("filtrer_resultat()");
			} else {
				jQuery('#id_total_ligne').val(requester);
				afficher_suite();
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});
}

function afficher_suite() {
	tmpresult_num=jQuery('#id_num_temp').val();
	num_last_ligne=jQuery('#id_num_last_ligne').val();
	full_text_query=jQuery('#id_full_text_query').val();
	json_full_text_queries=jQuery('#id_json_full_text_queries').val();
	datamart_num=jQuery('#id_num_datamart').val();
	cohort_num_encours=jQuery('#id_cohort_num_encours').val();
	val_exclure_cohorte_resultat=jQuery('#id_exclure_cohorte_resultat').val();
	filtre_resultat_texte=jQuery('#id_filtre_resultat_texte').val();
	if (filtre_resultat_texte!='') {
		filtre_resultat_texte=filtre_resultat_texte.replace("'"," ");
		full_text_query=full_text_query+';requete_unitaire;'+filtre_resultat_texte+';requete_unitaire;';
		json_full_text_queries=json_full_text_queries+",{'query':'"+filtre_resultat_texte+"','type':'fulltext','synonym':''}";
	}

	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'afficher_resultat',tmpresult_num:tmpresult_num,num_last_ligne:num_last_ligne,full_text_query:escape(full_text_query),json_full_text_queries:escape(json_full_text_queries),datamart_num:datamart_num,val_exclure_cohorte_resultat:val_exclure_cohorte_resultat,cohort_num_encours:cohort_num_encours,filtre_resultat_texte:escape(filtre_resultat_texte)},
		beforeSend: function(requester){
			jQuery("#id_span_chargement").css('display','block');
			jQuery("#id_span_afficher_suite").css('display','none');
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("afficher_suite()");
			} else {
				jQuery("#id_span_chargement").css('display','none');
				jQuery("#id_tableau_resultat").append(contenu);
				jQuery('#id_num_last_ligne').val(parseInt(document.getElementById('id_num_last_ligne').value)+parseInt(document.getElementById('id_modulo_ligne_ajoute').value));
				if (document.getElementById('id_span_afficher_suite')) {
					if (parseInt(document.getElementById('id_num_last_ligne').value)>parseInt(document.getElementById('id_total_ligne').value)) {
						jQuery("#id_span_afficher_suite").css('display','none');
					} else {
						jQuery("#id_span_afficher_suite").css('display','block');
					}
				}
				select_cohorte(cohort_num_encours) ;
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});
}

function affiner_resultat () {
	tmpresult_num=jQuery('#id_num_temp').val();
	num_last_ligne=jQuery('#id_num_last_ligne').val();
	full_text_query=jQuery('#id_full_text_query').val();
	json_full_text_queries=jQuery('#id_json_full_text_queries').val();
	datamart_num=jQuery('#id_num_datamart').val();
	cohort_num_encours=jQuery('#id_cohort_num_encours').val();

	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:false,
		encoding: 'latin1',
		data:{ action:'afficher_resultat',tmpresult_num:tmpresult_num,num_last_ligne:num_last_ligne,full_text_query:escape(full_text_query),json_full_text_queries:escape(json_full_text_queries),datamart_num:datamart_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("affiner_resultat ()");
			} else {
				jQuery("#id_tableau_resultat").append(contenu);
				jQuery('#id_num_last_ligne').val(parseInt(document.getElementById('id_num_last_ligne').value)+parseInt(document.getElementById('id_modulo_ligne_ajoute').value));
				if (parseInt(document.getElementById('id_num_last_ligne').value)>parseInt(document.getElementById('id_total_ligne').value)) {
					document.getElementById('id_span_afficher_suite').style.display='none';
				}
				select_cohorte(cohort_num_encours) ;
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});
}

	
function afficher_connexion(commande) {
	jQuery('#id_div_connexion').css('display','block');
	jQuery('#id_div_corps').css({ opacity: 0.5 });
	var viewportWidth = document.body.clientWidth;
	//var viewportHeight = document.body.clientHeight;
	
	//var scroll_position = jQuery(window).scrollTop();
	var hauteur_ecran = jQuery(window).height();
	var top=hauteur_ecran/2-jQuery('#id_div_connexion').height()/2;
	
	var left=viewportWidth/2-jQuery('#id_div_connexion').width()/2;
	//var top=viewportHeight/2-jQuery('#id_div_connexion').height()/2;
	jQuery('#id_div_connexion').css("left",left);
	jQuery('#id_div_connexion').css("top",top);
	
	jQuery('#id_commande_a_rejouer').val(commande);
	
}


function connecter() {
	var login=jQuery('#id_login').val();
	var passwd=jQuery('#id_passwd').val();
	$( "#id_message_erreur" ).html( "" );
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:false,
		encoding: 'latin1',
		data:{ action:'connexion',login:login,passwd:passwd},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='passwd') {
				$( "#id_div_connexion" ).effect( "shake" );
			} else {
				if (contenu=='right') {
					$( "#id_message_erreur" ).html( get_translatione('LOGIN_OR_PASSWORD_CORRECT_BUT_NORIGHTS',"Votre identifiant et mot de passe sont corrects mais vous n'avez pas les droits suffisants"));
				} else {
					if (contenu=='ok') {
						jQuery('#id_div_connexion').css('display','none');
						jQuery('#id_div_connexion').css('left','200');
						jQuery('#id_div_connexion').css('top','400');
						jQuery('#id_div_corps').css({ opacity: 1 });
						jQuery('#id_passwd').val('');
						document.body.style.overflow = "";
						eval(document.getElementById('id_commande_a_rejouer').value);
					}
				}
			}
			
		},
		complete: function(requester){
		},
		error: function(){
		}
	});
}

function afficher_document(document_num) {
	tmpresult_num=jQuery('#id_num_temp').val();
	full_text_query=jQuery('#id_full_text_query').val();
	json_full_text_queries=jQuery('#id_json_full_text_queries').val();
	datamart_num=jQuery('#id_num_datamart').val();
	filtre_resultat_texte=jQuery('#id_filtre_resultat_texte').val();
	if (filtre_resultat_texte!='') {
		filtre_resultat_texte=filtre_resultat_texte.replace("'"," ");
		full_text_query=full_text_query+';requete_unitaire;'+filtre_resultat_texte;
		json_full_text_queries=json_full_text_queries+",{'query':'"+filtre_resultat_texte+"','type':'fulltext','synonym':''}";
	}

	if (!document.getElementById('id_doc_document_'+document_num)) {
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:false,
			encoding: 'latin1',
			data:{ action:'afficher_document',tmpresult_num:tmpresult_num,document_num:document_num,full_text_query:escape(full_text_query),json_full_text_queries:escape(json_full_text_queries),datamart_num:datamart_num},
			beforeSend: function(requester){
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("afficher_document("+document_num+")");
				} else {
					jQuery("#id_div_list_div_affichage").append(contenu); 
					
					jQuery( "#id_enveloppe_document_"+document_num).css("display","block");
					positionner("id_enveloppe_document_"+document_num,"id_button_"+document_num);
					mettre_index_haut_document (document_num);
					
					drag_resize(document_num);
				}
				
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	} else {
		positionner("id_enveloppe_document_"+document_num,"id_button_"+document_num);
		mettre_index_haut_document (document_num);
		
		drag_resize(document_num);
	}
}


function afficher_mvt(mvt_num,datamart_num) {
	tmpresult_num=jQuery('#id_num_temp').val();

	if (!document.getElementById('id_doc_document_'+mvt_num)) {
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:false,
			encoding: 'latin1',
			data:{ action:'afficher_mvt',tmpresult_num:tmpresult_num,mvt_num:mvt_num,datamart_num:datamart_num},
			beforeSend: function(requester){
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("afficher_mvt("+mvt_num+")");
				} else {
					jQuery("#id_div_list_div_affichage").append(contenu); 
					jQuery( "#id_enveloppe_document_"+mvt_num).css("display","block");
					positionner("id_enveloppe_document_"+mvt_num,"id_button_"+mvt_num);
					mettre_index_haut_document (mvt_num);
					drag_resize(mvt_num);
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	} else {
		positionner("id_enveloppe_document_"+mvt_num,"id_button_"+mvt_num);
		mettre_index_haut_document (mvt_num);
		drag_resize(mvt_num);
	}
}

function afficher_document_patient_popup(document_num,full_text_query,datamart_num,id_cle) {

	if (!document.getElementById('id_doc_document_'+id_cle)) {
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:false,
			encoding: 'latin1',
			data:{ action:'afficher_document_patient_popup',document_num:document_num,full_text_query:escape(full_text_query),datamart_num:datamart_num,id_cle:id_cle},
			beforeSend: function(requester){
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("afficher_document_patient_popup('"+document_num+"','"+concept_str+"',"+datamart_num+",'"+id_cle+"')");
				} else {
					jQuery("#id_div_list_div_affichage").append(contenu); 
					
					jQuery( "#id_enveloppe_document_"+id_cle).css("display","block");
					positionner("id_enveloppe_document_"+id_cle,"id_button_"+id_cle);
					mettre_index_haut_document (id_cle);
					
					drag_resize(id_cle);
				}
				
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	} else {
		positionner("id_enveloppe_document_"+id_cle,"id_button_"+id_cle);
		mettre_index_haut_document (id_cle);
		
		drag_resize(id_cle);
	}
}

function ouvrir_plus_document (patient_num,tmpresult_num) {
	liste_document=jQuery('#id_input_liste_document_plus_'+patient_num).val();
	full_text_query=jQuery('#id_full_text_query').val();
	json_full_text_queries=jQuery('#id_json_full_text_queries').val();
	datamart_num=jQuery('#id_num_datamart').val();
	filtre_resultat_texte=jQuery('#id_filtre_resultat_texte').val();
	if (filtre_resultat_texte!='') {
		full_text_query=full_text_query+';requete_unitaire;'+filtre_resultat_texte;
	}
	plier_deplier("id_div_ouvrir_plus_document_"+patient_num);
	
	if (jQuery('#id_div_ouvrir_plus_document_'+patient_num).html()=='') {
		jQuery('#id_div_ouvrir_plus_document_'+patient_num).html("<img src=\"images/chargement_mac.gif\">");
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:false,
			encoding: 'latin1',
			data:{ action:'ouvrir_plus_document',patient_num:patient_num,tmpresult_num:tmpresult_num,liste_document:liste_document,full_text_query:escape(full_text_query),json_full_text_queries:escape(json_full_text_queries),datamart_num:datamart_num},
			beforeSend: function(requester){
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("ouvrir_plus_document ('"+patient_num+"')");
				} else {
					jQuery('#id_div_ouvrir_plus_document_'+patient_num).html(contenu); 
					
				}
				
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
}

function positionner(id,ancre) {
	var val_left= getLeft(document.getElementById( ancre));
	var val_top= getTop(document.getElementById( ancre));
	jQuery('#'+id).css('left',val_left+150);
	jQuery('#'+id).css('top',val_top+jQuery( "#"+ancre).height());
	
}

function positionner_v2(id,ancre,option_left,option_top) {
	var val_left= getLeft(document.getElementById( ancre));
	var val_top= getTop(document.getElementById( ancre));
	jQuery('#'+id).css('left',val_left+option_left);
	jQuery('#'+id).css('top',val_top+option_top);
}

function getLeft(MyObject) {
	if (MyObject.offsetParent) {
		return (MyObject.offsetLeft + getLeft(MyObject.offsetParent));
	} else {
		return (MyObject.offsetLeft);
	}
}
function getTop(MyObject) {
	if (MyObject.offsetParent) {
		return (MyObject.offsetTop + getTop(MyObject.offsetParent));
	} else {
		return (MyObject.offsetTop);
	}
}

function drag_resize(document_num) {
	$('#id_enveloppe_document_'+document_num).css('min-width',400);
	$('#id_enveloppe_document_'+document_num).css('min-height',400);
	
	$('#id_document_'+document_num).css('width',$('#id_enveloppe_document_'+document_num).width()-25);
	//$('#id_document_'+document_num).css('height',$('#id_enveloppe_document_'+document_num).height() -$('#id_bandeau_'+document_num).height() - $('#id_onglet_'+document_num).height() -25);
	$('#id_document_'+document_num).css('height',$('#id_enveloppe_document_'+document_num).height() -$('#id_bandeau_'+document_num).height() - 25);
	$('#id_enveloppe_document_'+document_num)
		.resizable({
		        start: function(e, ui) {
				mettre_index_haut_document(document_num);
		        },
		        resize: function(e, ui) {
				$('#id_document_'+document_num).css('width',$('#id_enveloppe_document_'+document_num).width()-25);
				//$('#id_document_'+document_num).css('height',$('#id_enveloppe_document_'+document_num).height() -$('#id_bandeau_'+document_num).height()- $('#id_onglet_'+document_num).height() -25);
				$('#id_document_'+document_num).css('height',$('#id_enveloppe_document_'+document_num).height() -$('#id_bandeau_'+document_num).height()-25);
		        },
		        stop: function(e, ui) {
		        }
	});
	$('#id_bandeau_'+document_num).mouseover(function(){
		$('#id_enveloppe_document_'+document_num)
			.draggable({
			        start: function(e, ui) {
			        },
			        stop: function(e, ui) {
			        },
			       disabled: false, 
			       scroll: false ,
			        opacity: 1 
			});;
		$('#id_enveloppe_document_'+document_num).css('opacity',1);
		mettre_index_haut_document(document_num);
	});
	$('#id_bandeau_'+document_num).mouseout(function(){
		$('#id_enveloppe_document_'+document_num)
			.draggable({
			        start: function(e, ui) {
			        },
			        stop: function(e, ui) {
			        },
			       disabled: true,
			        opacity: 1 
			});
		$('#id_enveloppe_document_'+document_num).css('opacity',1);
	});
	
	$('#id_enveloppe_document_'+document_num).click(function(){
		mettre_index_haut_document(document_num);
	});
}



function mettre_index_haut_document (id) {
	index_haut++;
	if (document.getElementById('id_enveloppe_document_'+id)) {
		if (jQuery('#id_enveloppe_document_'+id).css('display')=='block') {
			jQuery('#id_enveloppe_document_'+id).css('zIndex',index_haut);
			jQuery('.div_voir_document').css('font-weight','normal');
			jQuery('#id_button_'+id).css('font-weight','bold');
			jQuery('.voir_document').css('color','');
			jQuery('#id_button_'+id).find('.voir_document').css('color','#CD1DAA');
		}
	}
}



function mettre_index_haut (id) {
	val_id_index_haut=index_haut;
	if (val_id_index_haut!=id && id!='') {
		if (val_id_index_haut!='') {
			val_index=document.getElementById(val_id_index_haut).style.zIndex;
			if (document.getElementById(val_id_index_haut).style.zIndex>1) {
				document.getElementById(val_id_index_haut).style.zIndex=val_index-1;
			}
		} else {
			val_index=1;
		}
		document.getElementById(id).style.zIndex=val_index+1;
		index_haut=id;
	} 
}


function fermer_document(id) {
	jQuery('.div_voir_document').css('font-weight','normal');
	jQuery('.voir_document').css('color','');
	jQuery('#id_enveloppe_document_'+id).css('display','none');
}

function rechercher_code (num_filtre) {

	deplier('id_div_resultat_recherche_code_'+num_filtre,'block');
	requete_texte=jQuery('#id_rechercher_code_'+num_filtre).val();
	thesaurus_code=jQuery('#id_select_thesaurus_data_'+num_filtre).val();
	
	jQuery('#id_div_resultat_recherche_code_'+num_filtre).html("<img src=images/chargement_mac.gif>");
	if (thesaurus_code!='') {
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:false,
			encoding: 'latin1',
			data:{ action:'rechercher_code',num_filtre:num_filtre,requete_texte:escape(requete_texte),thesaurus_code:thesaurus_code},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion("rechercher_code('"+num_filtre+"')");
				} else {
					jQuery('#id_div_resultat_recherche_code_'+num_filtre).html(requester);
				}
			},
			complete: function(requester){
			},
			error: function(){
					}
		});
	} else {
		jQuery('#id_div_resultat_recherche_code_'+num_filtre).html("");
		if (thesaurus_code=='') {
			alert(get_translation('JS_PRECISEZ_UN_THESAURUS','précisez un thesaurus'));
		}
	}
}

function rechercher_code_sous_thesaurus (num_filtre,thesaurus_data_father_num,sans_filtre) {
	requete_texte=jQuery('#id_rechercher_code_'+num_filtre).val();
	thesaurus_code=jQuery('#id_select_thesaurus_data_'+num_filtre).val();
	id='id_div_thesaurus_sous_data_'+num_filtre+'_'+thesaurus_data_father_num;
	if (document.getElementById(id)) {
		if (document.getElementById(id).style.display=="none") {
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:false,
				encoding: 'latin1',
				data:{ action:'rechercher_code',num_filtre:num_filtre,requete_texte:escape(requete_texte),thesaurus_code:thesaurus_code,thesaurus_data_father_num:thesaurus_data_father_num,sans_filtre:sans_filtre},
				beforeSend: function(requester){
					jQuery('#plus_'+id).html("-");
					document.getElementById(id).style.display="block";
					document.getElementById(id).innerHTML="<img src=images/chargement_mac.gif>";
				},
				success: function(requester){
					if (requester=='deconnexion') {
						afficher_connexion("rechercher_code_sous_thesaurus('"+num_filtre+"','"+thesaurus_data_father_num+"','"+sans_filtre+"')");
					} else {
						document.getElementById(id).innerHTML=requester;
					}
				},
				complete: function(requester){
				},
				error: function(){
						}
			});
		} else {
			document.getElementById(id).style.display="none";
			jQuery('#plus_'+id).html("+");
		}
	}
}


function display_hierarchy_thesaurus (num_filtre,thesaurus_data_num,requete_texte,thesaurus_code) {
	jQuery('#id_select_thesaurus_data_'+num_filtre).val(thesaurus_code);
	id='id_div_resultat_recherche_code_'+num_filtre;
	if (jQuery('#'+id).length) {
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:false,
			encoding: 'latin1',
			data:{ action:'display_hierarchy_thesaurus',num_filtre:num_filtre,thesaurus_data_num:thesaurus_data_num,requete_texte:escape(requete_texte)},
			beforeSend: function(requester){
				jQuery('#'+id).css('display','block');
				jQuery('#'+id).html("<img src=images/chargement_mac.gif>");
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion("display_hierarchy_thesaurus('"+num_filtre+"','"+thesaurus_data_num+"','"+requete_texte+"','"+thesaurus_code+"')");
				} else {
					jQuery('#'+id).html(requester);
				}
			},
			complete: function(requester){
			},
			error: function(){
					}
		});
	}
}

function ajouter_formulaire_code(num_filtre,thesaurus_data_num) {
	if (thesaurus_data_num!='' && jQuery("#id_div_selection_code_"+num_filtre+"_"+thesaurus_data_num).length==0) {
		jQuery('#id_span_nbresult_atomique_'+num_filtre).html('?');
		jQuery('#id_input_thesaurus_data_num_'+num_filtre).val('');
		jQuery('#id_input_chaine_requete_code_'+num_filtre).val('');
		jQuery('#id_query_key_'+num_filtre).val('');
		jQuery('#id_input_nbresult_atomique_'+num_filtre).val('');
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:false,
			encoding: 'latin1',
			data:{ action:'ajouter_formulaire_code',num_filtre:num_filtre,thesaurus_data_num:thesaurus_data_num},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion("ajouter_formulaire_code('"+num_filtre+"','"+thesaurus_data_num+"')");
				} else {
					jQuery('#id_div_selection_code_'+num_filtre).append(requester);
					jQuery('#id_input_thesaurus_data_num_'+num_filtre).val("");
					list_thesaurus_data_num="";
					jQuery(".class_selection_code_"+num_filtre).each(function() {
						list_thesaurus_data_num+=jQuery(this).attr("thesaurus_data_num")+";";
					});
					jQuery('#id_input_thesaurus_data_num_'+num_filtre).val(list_thesaurus_data_num);
					creer_chaine_requete_code(num_filtre);
					$(".chosen-select").chosen({width: "250px",max_selected_options: 50,allow_single_deselect: true,search_contains:true}); 
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
}

function delete_sub_query_coded (id,num_filtre) {
	jQuery("#"+id).remove();
	jQuery('#id_input_thesaurus_data_num_'+num_filtre).val("");
	list_thesaurus_data_num="";
	jQuery(".class_selection_code_"+num_filtre).each(function() {
		list_thesaurus_data_num+=jQuery(this).attr("thesaurus_data_num")+";";
	});
	jQuery('#id_input_thesaurus_data_num_'+num_filtre).val(list_thesaurus_data_num);
	creer_chaine_requete_code(num_filtre);
}

function vider_items_code (num_filtre_thesaurus_data_num,type_item) {
	if (type_item=='hors_borne' && jQuery('#id_select_hors_borne_'+num_filtre_thesaurus_data_num).val()!='') {
		jQuery('#id_select_operateur_'+num_filtre_thesaurus_data_num).val('');
		jQuery('#id_input_valeur_'+num_filtre_thesaurus_data_num).val('');
		jQuery('#id_input_valeur_deb_'+num_filtre_thesaurus_data_num).val('');
		jQuery('#id_input_valeur_fin_'+num_filtre_thesaurus_data_num).val('');
		jQuery('#id_input_valeur_sup_n_x_borne_sup_'+num_filtre_thesaurus_data_num).val('');
		jQuery('#id_input_valeur_inf_n_x_borne_inf_'+num_filtre_thesaurus_data_num).val('');
		//jQuery('#id_input_checkbox_presence_'+num_filtre_thesaurus_data_num).prop('checked',false);
	}
	if (type_item=='operateur' && jQuery('#id_select_operateur_'+num_filtre_thesaurus_data_num).val()!='') {
		jQuery('#id_select_hors_borne_'+num_filtre_thesaurus_data_num).val('');
		jQuery('#id_input_valeur_deb_'+num_filtre_thesaurus_data_num).val('');
		jQuery('#id_input_valeur_fin_'+num_filtre_thesaurus_data_num).val('');
		jQuery('#id_input_valeur_sup_n_x_borne_sup_'+num_filtre_thesaurus_data_num).val('');
		jQuery('#id_input_valeur_inf_n_x_borne_inf_'+num_filtre_thesaurus_data_num).val('');
		//jQuery('#id_input_checkbox_presence_'+num_filtre_thesaurus_data_num).prop('checked',false);
	}
	if (type_item=='valeur' && jQuery('#id_input_valeur_deb_'+num_filtre_thesaurus_data_num).val()!='') {
		jQuery('#id_select_hors_borne_'+num_filtre_thesaurus_data_num).val('');
		jQuery('#id_select_operateur_'+num_filtre_thesaurus_data_num).val('');
		jQuery('#id_input_valeur_'+num_filtre_thesaurus_data_num).val('');
		jQuery('#id_input_valeur_sup_n_x_borne_sup_'+num_filtre_thesaurus_data_num).val('');
		jQuery('#id_input_valeur_inf_n_x_borne_inf_'+num_filtre_thesaurus_data_num).val('');
		//jQuery('#id_input_checkbox_presence_'+num_filtre_thesaurus_data_num).prop('checked',false);
	}
	if (type_item=='n_x_borne' && jQuery('#id_input_valeur_inf_n_x_borne_inf_'+num_filtre_thesaurus_data_num).val()!='') {
		jQuery('#id_select_hors_borne_'+num_filtre_thesaurus_data_num).val('');
		jQuery('#id_select_operateur_'+num_filtre_thesaurus_data_num).val('');
		jQuery('#id_input_valeur_'+num_filtre_thesaurus_data_num).val('');
		jQuery('#id_input_valeur_deb_'+num_filtre_thesaurus_data_num).val('');
		jQuery('#id_input_valeur_fin_'+num_filtre_thesaurus_data_num).val('');
		jQuery('#id_input_valeur_sup_n_x_borne_sup_'+num_filtre_thesaurus_data_num).val('');
		//jQuery('#id_input_checkbox_presence_'+num_filtre_thesaurus_data_num).prop('checked',false);
	}
	if (type_item=='n_x_borne' && jQuery('#id_input_valeur_sup_n_x_borne_sup_'+num_filtre_thesaurus_data_num).val()!='') {
		jQuery('#id_select_hors_borne_'+num_filtre_thesaurus_data_num).val('');
		jQuery('#id_select_operateur_'+num_filtre_thesaurus_data_num).val('');
		jQuery('#id_input_valeur_'+num_filtre_thesaurus_data_num).val('');
		jQuery('#id_input_valeur_deb_'+num_filtre_thesaurus_data_num).val('');
		jQuery('#id_input_valeur_fin_'+num_filtre_thesaurus_data_num).val('');
		jQuery('#id_input_valeur_inf_n_x_borne_inf_'+num_filtre_thesaurus_data_num).val('');
		//jQuery('#id_input_checkbox_presence_'+num_filtre_thesaurus_data_num).prop('checked',false);
	}
	//if (type_item=='presence' && jQuery('#id_input_checkbox_presence_'+num_filtre_thesaurus_data_num).is(':checked')) {
	//	jQuery('#id_select_hors_borne_'+num_filtre_thesaurus_data_num).val('');
	//	jQuery('#id_select_operateur_'+num_filtre_thesaurus_data_num).val('');
	//	jQuery('#id_input_valeur_'+num_filtre_thesaurus_data_num).val('');
	//	jQuery('#id_input_valeur_deb_'+num_filtre_thesaurus_data_num).val('');
	//	jQuery('#id_input_valeur_fin_'+num_filtre_thesaurus_data_num).val('');
	//	jQuery('#id_input_valeur_inf_n_x_borne_inf_'+num_filtre_thesaurus_data_num).val('');
	//	jQuery('#id_input_valeur_sup_n_x_borne_sup_'+num_filtre_thesaurus_data_num).val('');
	//	jQuery('.class_input_checkbox_'+num_filtre_thesaurus_data_num).prop('checked',false);
	//}
	if (type_item=='list_values') {
		jQuery('#id_input_data_textual_research_'+num_filtre_thesaurus_data_num).val('');
	}
	if (type_item=='data_textual_research') {
		jQuery( "#id_list_checkbox_thesaurus_data_"+num_filtre_thesaurus_data_num).val("");
	}
	
	
}

function creer_chaine_requete_code (num_filtre) {
	chaine_requete_code_final='';
	chaine_requete_code_preced=jQuery('#id_input_chaine_requete_code_'+num_filtre).val();
	jQuery(".class_selection_code_"+num_filtre).each(function() {
		thesaurus_data_num=jQuery(this).attr("thesaurus_data_num");
		num_filtre_thesaurus_data_num=num_filtre+"_"+thesaurus_data_num;
		
		hors_borne='';
		operateur='';
		valeur='';
		valeur_deb='';
		valeur_fin='';
		valeur_sup_n_x_borne_sup='';
		valeur_inf_n_x_borne_inf='';
		liste_valeur_checked='';
		data_textual_research='';
		
		if (document.getElementById('id_select_hors_borne_'+num_filtre_thesaurus_data_num)) {
			hors_borne=jQuery('#id_select_hors_borne_'+num_filtre_thesaurus_data_num).val();
			operateur=jQuery('#id_select_operateur_'+num_filtre_thesaurus_data_num).val();
			valeur=jQuery('#id_input_valeur_'+num_filtre_thesaurus_data_num).val();
			valeur_deb=jQuery('#id_input_valeur_deb_'+num_filtre_thesaurus_data_num).val();
			valeur_fin=jQuery('#id_input_valeur_fin_'+num_filtre_thesaurus_data_num).val();
			valeur_sup_n_x_borne_sup=jQuery('#id_input_valeur_sup_n_x_borne_sup_'+num_filtre_thesaurus_data_num).val();
			valeur_inf_n_x_borne_inf=jQuery('#id_input_valeur_inf_n_x_borne_inf_'+num_filtre_thesaurus_data_num).val();
			
			if (valeur=='') {
				operateur='';
			}
			if (operateur=='') {
				valeur='';
			}
			//if (jQuery('#id_input_checkbox_presence_'+num_filtre_thesaurus_data_num).is(':checked')) {
				liste_valeur_checked=1;
			//}
		} else {
		
			tab_liste_valeur_checked= $( "#id_list_checkbox_thesaurus_data_"+num_filtre_thesaurus_data_num).val() || [] ;
			if( typeof tab_service === 'string' ) {
				liste_valeur_checked=tab_liste_valeur_checked;
			} else {
				liste_valeur_checked=tab_liste_valeur_checked.join( "^" );
			}
			//$('#id_div_list_checkbox_thesaurus_data_'+num_filtre_thesaurus_data_num+' :checked').each(function() {
			//	liste_valeur_checked=liste_valeur_checked+"^"+$(this).val();
			//});
			if (liste_valeur_checked=='') {
				liste_valeur_checked=1;
			}
		}
		if (document.getElementById('id_input_data_textual_research_'+num_filtre_thesaurus_data_num)) {
			data_textual_research=jQuery('#id_input_data_textual_research_'+num_filtre_thesaurus_data_num).val();
		}
		chaine_requete_code=hors_borne+";"+operateur+";"+valeur+";"+valeur_deb+";"+valeur_fin+";"+valeur_sup_n_x_borne_sup+";"+valeur_inf_n_x_borne_inf+";"+liste_valeur_checked+";"+data_textual_research;
		
		if (  chaine_requete_code.match(/^;+$/)) {
		//if (chaine_requete_code==';;;;;;;;') {
			alert('vide');
			chaine_requete_code='';
		} else {
			chaine_requete_code_final=chaine_requete_code_final+";separator;"+chaine_requete_code+";"+thesaurus_data_num;
		}
		
	});
	
	jQuery('#id_input_chaine_requete_code_'+num_filtre).val(chaine_requete_code_final);
	
	if (chaine_requete_code_preced!=chaine_requete_code) {
		//calcul_nb_resultat_filtre(num_filtre,true);
	
	}
}


var verif_script_lance_2='';
function verif_chaine_modifiee_avant_creer_chaine(num_filtre,id,val,num_filtre_thesaurus_data_num) {
	valeur=document.getElementById(id).value;
	if (valeur==val) {
		verif_script_lance_2='';
		creer_chaine_requete_code (num_filtre);
	} else {
		if (verif_script_lance_2=='') {
			verif_script_lance_2='ok';
			setTimeout("verif_chaine_modifiee_avant_creer_chaine('"+num_filtre+"','"+id+"','"+valeur+"','"+num_filtre_thesaurus_data_num+"')",1000);
		} else {
			verif_script_lance_2='';
		}
	}
}

var verif_script_lance_1='';
function verif_chaine_modifiee_avant_calcul (num_filtre,id,val) {
	valeur=document.getElementById(id).value;
	if (valeur==val) {
		verif_script_lance_1='';
		//calcul_nb_resultat_filtre (num_filtre,true);
	} else {
		if (verif_script_lance_1=='') {
			verif_script_lance_1='ok';
			setTimeout("verif_chaine_modifiee_avant_calcul('"+num_filtre+"','"+id+"','"+valeur+"')",1000);
		} else {
			verif_script_lance_1='';
		}
	}
}

function verif_chaine_texte_modifiee_avant_calcul (num_filtre,id,val) {
	valeur=document.getElementById(id).value;
	if (verif_script_lance_1=='') {
		if (valeur==val) {
			verif_script_lance_1='';
			calcul_nb_resultat_filtre (num_filtre,true);
		} else {
			if (verif_script_lance_1=='') {
				verif_script_lance_1='ok';
				setTimeout("verif_chaine_modifiee_avant_calcul('"+num_filtre+"','"+id+"','"+valeur+"')",4000);
			} else {
				verif_script_lance_1='';
			}
		}
	}
}

/////////// ANNUAIRE ////////////


function afficher_modifier_service(department_num) {
	document.getElementById('id_div_libelle_service_'+department_num).style.display='none';
	document.getElementById('id_div_libelle_service_modifier_'+department_num).style.display='block';
}



function autocomplete_service (department_num) {
	
	jQuery( "#id_champs_recherche_rapide_utilisateur_"+department_num ).autocomplete({
		source: "ajax.php?action=autocomplete_rech_rapide_utilisateur",
		minLength: 2, 
		search: function( event, ui ) {document.getElementById('id_chargement_'+department_num).style.display='block';},
		select: function( event, ui ) {
			document.getElementById('id_chargement_'+department_num).style.display='none';
			jQuery('#id_div_reponse_service_'+department_num).html('');
			jQuery('#id_champs_recherche_rapide_utilisateur_'+department_num).val('');
			jQuery('#id_textarea_ajouter_user_'+department_num).val(document.getElementById('id_textarea_ajouter_user_'+department_num).value+ui.item.id+'\n');
			ajouter_user(department_num);
			return false;
		}
	});
}

function ajouter_user(department_num) {
	liste_login=jQuery('#id_textarea_ajouter_user_'+department_num).val();
	jQuery.ajax({
	type:"POST",
	url:"ajax.php",
	async:true,
	data: { action:'ajouter_user',liste_login:escape(liste_login),department_num:department_num},
	beforeSend: function(requester){
		document.getElementById('id_div_service_'+department_num).style.display='block';
		jQuery('#id_div_reponse_service_'+department_num).html('');
	},
	success: function(requester){
		contenu=requester;

		if (contenu=='deconnexion') {
			afficher_connexion("ajouter_user("+department_num+")");
		} else {
			if (contenu=='erreur') {
				alert(get_translation('JS_UNE_ERREUR_EST_SURVENUE','Une erreur est survenue'));
			} else {
				reg=new RegExp(";MAJ;[^;]*;MAJ;");
				if (reg.test(contenu)) {
					while (reg.test(contenu)) {
						reg_rep=new RegExp(";MAJ;([^;]*);MAJ;");
						maj=contenu.replace(reg_rep,"$1");
						tab_maj=maj.split(',');
						id_span=tab_maj[0];
						manager_department=tab_maj[1];
						if (manager_department==1) {
							document.getElementById(id_span).innerHTML='*';
						} else {
							document.getElementById(id_span).innerHTML='';
						}
						reg_rep=new RegExp(";MAJ;[^;]*;MAJ;");
						contenu=contenu.replace(reg_rep,"");
					}
				}
				
				$('#id_tableau_user_'+department_num).append(contenu);
				
				jQuery('#id_div_reponse_service_'+department_num).html(get_translation('JS_UTILISATEURS_AJOUTES_AVEC_SUCCES','Utilisateurs ajoutés avec succès'));
			}
		}
	 	
	},
	complete: function(requester){
	},
	error: function(){
	}
	});		
	

}



function supprimer_user(user_num,department_num) {
	jQuery.ajax({
	type:"POST",
	url:"ajax.php",
	async:true,
	data: { action:'supprimer_user',user_num:user_num,department_num:department_num},
	beforeSend: function(requester){
	},
	success: function(requester){
		contenu=requester;
		if (contenu=='deconnexion') {
			afficher_connexion("supprimer_user("+user_num+","+department_num+")");
		} else {
			if (contenu=='erreur') {
				alert(get_translation('JS_UNE_ERREUR_EST_SURVENUE','Une erreur est survenue'));
			} else {
				$("tr#id_tr_user_"+department_num+"_"+user_num).remove();
			}
		}
	},
	complete: function(requester){
	},
	error: function(){
	}
	});		
}


function modifier_passwd (page) {
	mon_password1=jQuery('#id_mon_password1').val();
	mon_password2=jQuery('#id_mon_password2').val();
	mon_password1=mon_password1.replace(/\+/g,';plus;');
	mon_password2=mon_password2.replace(/\+/g,';plus;');
	if (mon_password1!='' && mon_password2!='' && mon_password1==mon_password2) {
		 jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:false,
			data: { action:'modifier_passwd',mon_password1:escape(mon_password1),mon_password2:escape(mon_password2)},
			beforeSend: function(requester){
			},
			success: function(requester){
				contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("modifier_passwd('')");
				} else {
					tab=contenu.split(';');
					status=tab[0];
					commentary=tab[1];
					if (status=='erreur') {
						alert(get_translation('JS_UNE_ERREUR_EST_SURVENUE','Une erreur est survenue')+" : "+commentary);
						jQuery('#id_mon_password1').val('');
						jQuery('#id_mon_password2').val('');
						deplier('id_span_passwd_1','inline');
						plier('id_span_passwd_2');
						jQuery('#id_mon_password1').focus();
						
					} else {
						jQuery('#id_modifmdp_result').html("<strong style='color:green'>"+get_translation('MOT_DE_PASSE_MODIFIE_AVEC_SUCCÈS','Mot de passe modifié avec succès')+"</strong>");
						jQuery('#id_mon_password1').val('');
						jQuery('#id_mon_password2').val('');
						deplier('id_span_passwd_1','inline');
						plier('id_span_passwd_2');
						if (page!='') {
							setTimeout("window.location = '"+page+"'",1000);
							//window.location =page;
						}
						
					}
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	} else {
		jQuery('#id_modifmdp_result').html("<strong style='color:red'>"+get_translation('LES_MOTS_DE_PASSE_DOIVENT_ETRE_IDENTIQUES','les mots de passe doivent être identiques')+"</strong><br>");
		jQuery('#id_mon_password1').val('');
		jQuery('#id_mon_password2').val('');
		deplier('id_span_passwd_1','inline');
		plier('id_span_passwd_2');
	}
}

function modifier_user_phone_number () {
	user_phone_number=jQuery('#id_input_user_phone_number').val();
	 jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:false,
		data: { action:'modifier_user_phone_number',user_phone_number:escape(user_phone_number)},
		beforeSend: function(requester){
		},
		success: function(requester){
			contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("modifier_user_phone_number()");
			} else {
				if (contenu=='erreur') {
					alert(get_translation('JS_UNE_ERREUR_EST_SURVENUE','Une erreur est survenue'));
				} else {
					jQuery('#id_span_user_phone_number').html(user_phone_number);
					deplier('id_span_user_phone_number','inline');
					plier('id_span_modifier_user_phone_number');
					
				}
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});
}


function modifier_user_mail () {
	mail=jQuery('#id_input_user_mail').val();
	 jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:false,
		data: { action:'modifier_user_mail',mail:escape(mail)},
		beforeSend: function(requester){
		},
		success: function(requester){
			contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("modifier_user_mail()");
			} else {
				if (contenu=='erreur') {
					alert(get_translation('JS_UNE_ERREUR_EST_SURVENUE','Une erreur est survenue'));
				} else {
					jQuery('#id_span_user_mail').html(mail);
					deplier('id_span_user_mail','inline');
					plier('id_span_modifier_user_mail');
					
				}
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});
}
function vider_moteur_recherche() {
	jQuery('#id_div_formulaire_document').html('');
}

function sauver_requete_en_cours() {
	query_num=jQuery('#query_num_sauver').val();
	titre_requete_sauver=jQuery('#id_titre_requete_sauver').val();
	datamart_num=jQuery('#id_num_datamart').val();
	tmpresult_num=jQuery('#id_num_temp').val();
	if (document.getElementById('id_crontab_requete').checked==true) {
		crontab_query=1;
	} else {
		crontab_query=0;
	}
	crontab_periode=jQuery('#id_crontab_periode').val();
	if (query_num!='' && titre_requete_sauver!='') {
		 jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:false,
			data: { action:'sauver_requete_en_cours',query_num:query_num,titre_requete_sauver:escape(titre_requete_sauver),datamart_num:datamart_num,tmpresult_num:tmpresult_num,crontab_query:crontab_query,crontab_periode:crontab_periode},
			beforeSend: function(requester){
			},
			success: function(requester){
				contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("sauver_requete_en_cours()");
				} else {
					if (contenu=='erreur') {
						alert(get_translation('JS_UNE_ERREUR_EST_SURVENUE','Une erreur est survenue'));
					} else {
						document.getElementById('id_div_sauver_requete').style.display='none';
						jQuery('#id_titre_requete_sauver').val('');
						jQuery('#id_liste_requete_sauve').html("<h1>"+get_translation('SAVED_QUERIES','Requêtes sauvées')+"</h1>"+contenu);
						
					}
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
}

function charger_moteur_recherche(query_num) {
	 jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:false,
		data: { action:'charger_moteur_recherche',query_num:query_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("charger_moteur_recherche("+query_num+")");
			} else {
				if (contenu=='erreur') {
					alert(get_translation('JS_UNE_ERREUR_EST_SURVENUE','Une erreur est survenue'));
				} else {
					jQuery('#id_div_formulaire_document').html(contenu);
					peupler_moteur_recherche(query_num);
					$(".chosen-select").chosen({width: "250px",max_selected_options: 50}); 
					document.location="#ancre_entete_moteur"; 
	    				$('.autosizejs').autosize();   
				}
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		
}
function peupler_moteur_recherche(query_num) {
	 jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:false,
		data: { action:'peupler_moteur_recherche',query_num:query_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("peupler_moteur_recherche("+query_num+")");
			} else {
				if (contenu=='erreur') {
					alert(get_translation('JS_UNE_ERREUR_EST_SURVENUE','Une erreur est survenue'));
				} else {
					eval(contenu);
	    				$('.autosizejs').autosize();   
					$('.chosen-select').trigger('chosen:updated');
				}
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		
}


function punaiser_requete(query_num) {
	 jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:true,
		data: { action:'punaiser_requete',query_num:query_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("punaiser_requete("+query_num+")");
			} else {
				if (contenu=='erreur') {
					alert(get_translation('JS_UNE_ERREUR_EST_SURVENUE','Une erreur est survenue'));
				} else {
					$('#id_img_pin_'+query_num).html(contenu);
				}
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		
}

function visualiser_ct (id_ct) {
	id_ct_open=id_ct;
	$('.tr_id_ct').css('background','white');
	
	$(".class_tr_"+id_ct).css('background','#EA71E7');
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		method: 'post',
		async:true,
		contentType: 'application/x-www-form-urlencoded',
		encoding: 'latin1',
		data: {action:'visualiser_ct',id_ct:id_ct},
		beforeSend: function(requester){
		},
		success: function(requester){
			var result=requester;
			document.getElementById('id_div_contenu_ct').innerHTML =result;
			document.getElementById('id_div_visualiser_ct').style.display='block';
		},
		error: function(){}
	});
}

var clic_on_tab='';
function voir_detail_dwh (onglet) {
	$(".div_result").css("display","none");
	$(".color-bullet").removeClass("current");
	$("#id_div_dwh_"+onglet).css("display",'block');
	$("#id_bouton_"+onglet).addClass("current");
	clic_on_tab='ok';
}

function fnSelect(objId) {
	if (document.selection) {
		var range = document.body.createTextRange();
	        range.moveToElementText(document.getElementById(objId));
		range.select();
	} else if (window.getSelection) {
	        window.getSelection().removeAllRanges();
		var range = document.createRange();
		range.selectNode(document.getElementById(objId));
		window.getSelection().addRange(range);
	}
}

function reecrire_requete (num_filtre) {
	text=jQuery('#id_input_filtre_texte_'+num_filtre).val();
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		method: 'post',
		async:true,
		contentType: 'application/x-www-form-urlencoded',
		encoding: 'latin1',
		data: {action:'reecrire_requete',text:escape(text)},
		beforeSend: function(requester){
		},
		success: function(requester){
			var result=requester;
			if (result!='') {
				jQuery('#id_input_filtre_texte_'+num_filtre).val(result);
	    			$('.autosizejs').autosize();   
				calcul_nb_resultat_filtre(num_filtre,true);
			}
		},
		error: function(){}
	});
}



var popupdoc=new Array();
function ouvrir_document_print (document_num) {
	tmpresult_num='';
	filtre_resultat_texte='';
	full_text_query='';
	datamart_num='';
	if (document.getElementById('id_num_temp')) {
		tmpresult_num=jQuery('#id_num_temp').val();
	}
	if (document.getElementById('id_full_text_query')) {
		full_text_query=jQuery('#id_full_text_query').val();
		json_full_text_queries=jQuery('#id_json_full_text_queries').val();
	}
	if (document.getElementById('id_num_datamart')) {
		datamart_num=jQuery('#id_num_datamart').val();
	}
	if (document.getElementById('id_filtre_resultat_texte')) {
		filtre_resultat_texte=jQuery('#id_filtre_resultat_texte').val();
	}
	if (filtre_resultat_texte!='') {
		full_text_query=full_text_query+';requete_unitaire;'+filtre_resultat_texte;
	}
	
	
	if (document.getElementById('id_input_filtre_patient_text')) {
		full_text_query=jQuery('#id_input_filtre_patient_text').val();
		full_text_query=full_text_query.replace(/\+/g,';plus;');
		//full_text_query=full_text_query.replace(/\\/g,';antislash;');
	}

	popupdoc[document_num] = window.open('afficher_document.php?document_num='+document_num+'&option=print&requete='+escape(full_text_query),'doc'+document_num,'height=400,width=570,scrollbars=yes,resizable=yes');
	popupdoc[document_num].focus();
}


var popupdoc=new Array();
function ouvrir_liste_document_print (patient_num) {
	full_text_query='';
	if (document.getElementById('id_full_text_query')) {
		full_text_query=jQuery('#id_full_text_query').val();
		json_full_text_queries=jQuery('#id_json_full_text_queries').val();
	}
	filtre_resultat_texte='';
	if (document.getElementById('id_filtre_resultat_texte')) {
		filtre_resultat_texte=jQuery('#id_filtre_resultat_texte').val();
		if (filtre_resultat_texte!='') {
			full_text_query=full_text_query+';requete_unitaire;'+filtre_resultat_texte;
		}
	}

	if (document.getElementById('id_input_filtre_patient_text')) {
		full_text_query=jQuery('#id_input_filtre_patient_text').val();
	}
	if (full_text_query!='') {
		full_text_query=full_text_query.replace(/\+/g,';plus;');
	}

	popupdoc[patient_num] = window.open('afficher_document.php?document_num=tout&patient_num='+patient_num+'&option=print&requete='+escape(full_text_query),'listedoc'+patient_num,'height=400,width=570,scrollbars=yes,resizable=yes,menubar=yes,toolbar=yes');
	popupdoc[patient_num].focus();
}


function demande_acces_patient (department_num,tmpresult_num,num_user_manager_department,query_num) {
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		method: 'post',
		async:true,
		contentType: 'application/x-www-form-urlencoded',
		encoding: 'latin1',
		data: {action:'demande_acces_patient',department_num:department_num,tmpresult_num:tmpresult_num,num_user_manager_department:num_user_manager_department,query_num:query_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("demande_acces_patient ("+department_num+",'"+tmpresult_num+","+num_user_manager_department+","+query_num+")");
			} else {
				if (contenu=='erreur') {
					alert(get_translation('JS_UNE_ERREUR_EST_SURVENUE','Une erreur est survenue'));
				} else {
					$('#id_span_action_demande_acces_'+department_num+"_"+num_user_manager_department).html(get_translation('ENVOYEE','Envoyée'));
					
				}
			}
		},
		error: function(){}
	});
}

function search_engine_sub_menu (id) {
	test_ouvrir='';
	if (jQuery("#"+id).css('display')=='none') {
		test_ouvrir='ok';
	}
	plier('id_div_alimenter_cohorte');
	plier('id_div_sauver_requete');
	plier('id_div_export_excel');
	plier('id_div_filtre_resultat_texte');
	plier('id_div_partager_requete');
	jQuery("#button_id_div_alimenter_cohorte").css('backgroundColor','grey');
	jQuery("#button_id_div_sauver_requete").css('backgroundColor','grey');
	jQuery("#button_id_div_export_excel").css('backgroundColor','grey');
	jQuery("#button_id_div_filtre_resultat_texte").css('backgroundColor','grey');
	jQuery("#button_id_div_partager_requete").css('backgroundColor','grey');
	
	if (test_ouvrir=='ok') {
		plier_deplier(id);
		jQuery("#button_"+id).css('backgroundColor','#00b2d7');
	} 
}

function search_engine_sub_menu_hover (id) {
	if (jQuery("#"+id).css('display')=='none') {
		jQuery("#button_"+id).css('backgroundColor','#00b2d7');
	}
}

function search_engine_sub_menu_out (id) {
	if (jQuery("#"+id).css('display')=='none') {
		jQuery("#button_"+id).css('backgroundColor','grey');
	}
}


function partager_requete_en_cours (query_num) {
	notification_text=jQuery('#id_notification_text').val();
	tab_num_user_partage= $( "#id_select_num_user_partager").val() || [] ;
	if( typeof tab_service === 'string' ) {
		liste_num_user_partage=tab_num_user_partage;
	} else {
		liste_num_user_partage=tab_num_user_partage.join( "," );
	}
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		method: 'post',
		async:true,
		contentType: 'application/x-www-form-urlencoded',
		encoding: 'latin1',
		data: {action:'partager_requete_en_cours',notification_text:escape(notification_text),liste_num_user_partage:escape(liste_num_user_partage),query_num:query_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("partager_requete_en_cours ('"+query_num+"')");
			} else {
				if (contenu=='erreur') {
					alert(get_translation('JS_UNE_ERREUR_EST_SURVENUE','Une erreur est survenue'));
				} else {
					setTimeout("search_engine_sub_menu ('id_div_partager_requete');",1000);
					$('#id_span_action_partager_requete_en_cours').html(get_translation('REQUETE_PARTAGEE_AVEC_SUCCES','Requête partagée avec succès'));
					setTimeout("$('#id_span_action_partager_requete_en_cours').html('');",3000);
				}
			}
		},
		error: function(){}
	});
}


function crontab_alerte_demande_acces () {
	jQuery.ajax({
	type:"POST",
	url:"ajax.php",
	async:true,
	data: { action:'crontab_alerte_demande_acces'},
	beforeSend: function(requester){
	},
	success: function(requester){
		result=requester;
		if (result=='erreur') {
			alert(get_translation('JS_UNE_ERREUR_EST_SURVENUE','Une erreur est survenue'));
		} else {
			if (document.getElementById('id_alerte_demande_acces')) {
				tab=result.split(',');
				nb_total=tab[0];
				nb_mes_demandes=tab[1];
				nb_a_traiter=tab[2];
				
				jQuery('#id_alerte_demande_acces').html(nb_total);
				if (nb_total>0) {
					document.getElementById('id_alerte_demande_acces').style.display='inline';
				} else {
					document.getElementById('id_alerte_demande_acces').style.display='none';
				}
				
				if (document.getElementById('id_alerte_demande_acces_mes_demandes')) {
					jQuery('#id_alerte_demande_acces_mes_demandes').html(nb_mes_demandes);
					if (nb_mes_demandes>0) {
						document.getElementById('id_alerte_demande_acces_mes_demandes').style.display='inline';
					} else {
						document.getElementById('id_alerte_demande_acces_mes_demandes').style.display='none';
					}
				}
				if (document.getElementById('id_alerte_demande_acces_a_traiter')) {
					jQuery('#id_alerte_demande_acces_a_traiter').html(nb_a_traiter);
					if (nb_a_traiter>0) {
						document.getElementById('id_alerte_demande_acces_a_traiter').style.display='inline';
					} else {
						document.getElementById('id_alerte_demande_acces_a_traiter').style.display='none';
					}
				}
				setTimeout("crontab_alerte_demande_acces();",2000000);
			}
		} 
	},
	complete: function(requester){
	},
	error: function(){
	}
	});


}


function crontab_alerte_notification () {
	if (document.getElementById('id_alerte_notification')) {
		jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:true,
		data: { action:'crontab_alerte_notification'},
		beforeSend: function(requester){
		},
		success: function(requester){
			result=requester;
			if (result=='erreur') {
				alert(get_translation('JS_UNE_ERREUR_EST_SURVENUE','Une erreur est survenue'));
			} else {
				if (document.getElementById('id_alerte_notification')) {
					nb_notification=result;
					if (nb_notification>0) {
						jQuery('#id_alerte_notification').html(nb_notification);
						jQuery('#id_alerte_notification').css('backgroundColor','red');
					} else {
						jQuery('#id_alerte_notification').html('&nbsp;');
						jQuery('#id_alerte_notification').css('backgroundColor','transparent');
					}
					
					setTimeout("crontab_alerte_notification();",20000);
				}
			} 
		},
		complete: function(requester){
		},
		error: function(){
		}
		});
	}
}	


function open_notification (forcer) {
	if (forcer=='oui') {
		deplier('id_div_notification');
	} else {
		plier_deplier('id_div_notification');
	}
		
	jQuery.ajax({
	type:"POST",
	url:"ajax.php",
	async:true,
	data: { action:'open_notification'},
	beforeSend: function(requester){
		jQuery('#id_div_contenu_notification').html("<img src=images/chargement_mac.gif>");
	},
	success: function(requester){
		result=requester;
		if (result=='deconnexion') {
			afficher_connexion("open_notification ()");
		} else {
			if (result=='erreur') {
				alert(get_translation('JS_UNE_ERREUR_EST_SURVENUE','Une erreur est survenue'));
			} else {
				if (result=='') {
					result=get_translation('NO_NOTIFICATION',"Vous n'avez aucune notification");
				}
				jQuery('#id_div_contenu_notification').html(result);
			} 
		}
	},
	complete: function(requester){
	},
	error: function(){
	}
	});
}



function delete_notification (notification_num) {
	
	if (confirm (get_translation('ETES_VOUS_SUR_DE_VOULOIR_SUPPRIMER_CETTE_NOTIFICATION','Etes vous sûr de vouloir supprimer cette notification')+' ?')) {
		
		jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:true,
		data: { action:'delete_notification',notification_num:notification_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			result=requester;
			if (result=='deconnexion') {
				afficher_connexion("delete_notification ("+notification_num+")");
			} else {
				if (result=='erreur') {
					alert(get_translation('JS_UNE_ERREUR_EST_SURVENUE','Une erreur est survenue'));
				} else {
					jQuery('#id_td_notification_'+notification_num).html('deleted');
					setTimeout("jQuery('#id_tr_notification_"+notification_num+"').remove();jQuery('#id_hr_notification_"+notification_num+"').remove();",500);
					
				} 
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
		});
	}
}


function notification_marquer_lue (notification_num) {

	jQuery.ajax({
	type:"POST",
	url:"ajax.php",
	async:true,
	data: { action:'notification_marquer_lue',notification_num:notification_num},
	beforeSend: function(requester){
	},
	success: function(requester){
		result=requester;
		if (result=='deconnexion') {
			afficher_connexion("notification_marquer_lue ('"+notification_num+"')");
		} else {
			if (result=='erreur') {
				alert(get_translation('JS_UNE_ERREUR_EST_SURVENUE','Une erreur est survenue'));
			} else {
				//document.getElementById('id_td_notification_'+notification_num).style.color='grey';
				jQuery('#id_td_notification_'+notification_num).css('color','grey');
				jQuery('#id_td_notification_'+notification_num).css('backgroundColor','white');
			} 
		}
	},
	complete: function(requester){
	},
	error: function(){
	}
	});

}

function envoyer_message_prive () {
	num_user_message_prive=jQuery('#id_select_num_user_message_prive').val();
	message_prive=jQuery('#id_textarea_message_prive').val();
	if (message_prive!='' && num_user_message_prive!='') {
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:true,
			data: { action:'envoyer_message_prive',num_user_message_prive:num_user_message_prive,message_prive:escape(message_prive)},
			beforeSend: function(requester){
			},
			success: function(requester){
				result=requester;
				if (result=='deconnexion') {
					afficher_connexion("envoyer_message_prive ()");
				} else {
					if (result=='erreur') {
						alert(get_translation('JS_UNE_ERREUR_EST_SURVENUE','Une erreur est survenue'));
					} else {
						open_notification ('oui');
						jQuery('#id_textarea_message_prive').val('');
						document.getElementById('id_textarea_message_prive').focus();
					} 
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
}

function repondre_message_prive (num_user_destinataire) {
	deplier('id_div_envoyer_message_prive','');
	jQuery('#id_select_num_user_message_prive').val(num_user_destinataire);
	document.getElementById('id_textarea_message_prive').focus();
	$('.chosen-select').trigger('chosen:updated');
}



function generer_resultat(query_num,tmpresult_num,datamart_num) {
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:true,
		data: { action:'generer_resultat',tmpresult_num:tmpresult_num,query_num:query_num,datamart_num:datamart_num},
		beforeSend: function(requester){
			$('#tabs').css('pointer-events','none');
			$('#tabs').css('opacity','0.5');
			jQuery("#id_span_chargement").css('display','block');
			jQuery("#id_span_afficher_suite").css('display','none');
		},
		success: function(requester){
			result=requester;
			if (result=='deconnexion') {
					afficher_connexion("");
			} else {
				if (result=='erreur') {
					alert(get_translation('JS_UNE_ERREUR_EST_SURVENUE','Une erreur est survenue'));
				} else {
					eval(result);
					//tab=result.split(',');
					//nb_patient=tab[0];
					//nb_document=tab[1];
					//nb_patient_user=tab[2];
					//nb_document_user=tab[3];
					jQuery(".id_span_nb_patient_total").html(nb_patient);
					jQuery(".id_span_nb_document_total").html(nb_document);
					jQuery("#id_span_nb_patient_detail").html(nb_patient);
					jQuery("#id_span_nb_document_detail").html(nb_document);
					jQuery("#id_span_nb_mvt_detail").html(nb_mvt);
					
					jQuery("#id_span_nb_patient_detail_user").html(nb_patient_user);
					jQuery("#id_span_nb_document_detail_user").html(nb_document_user);
					jQuery("#id_span_nb_mvt_detail_user").html(nb_mvt_user);
					
					jQuery("#id_total_ligne").val(nb_patient_user);
					
					if (document.getElementById('id_num_last_ligne')) {
						afficher_suite();
					}
				} 
			}
		},
		complete: function(requester){
			$('#tabs').css('pointer-events','auto');
			$('#tabs').css('opacity','1');
		},
		error: function(){
		}
	});
}



function create_process (commentary,process_end_date,category_process) {
	var process_num='';
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:false,
		encoding: 'latin1',
		data:{ action:'create_process',commentary:escape(commentary),process_end_date:escape(process_end_date),category_process:escape(category_process)},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("create_process ("+commentary+","+process_end_date+","+category_process+")");
			} else {
				process_num=contenu
			}
		}
	});
	return process_num;
}



function delete_process (process_num) {
	if (confirm (get_translation('ETES_VOUS_SUR_DE_VOULOIR_SUPPRIMER_CE_PROCESS','Etes vous sûr de vouloir supprimer ce process')+' ?')) {
		jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:true,
		data: { action:'delete_process',process_num:process_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			result=requester;
			if (result=='deconnexion') {
				afficher_connexion("delete_process ("+process_num+")");
			} else {
				if (result=='erreur') {
					alert(get_translation('JS_UNE_ERREUR_EST_SURVENUE','Une erreur est survenue'));
				} else {
					jQuery('#id_tr_process_'+process_num).remove();
				} 
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
		});
	}
}

function check_process_status (process_num,id_display_commentary) {
	var commentary='';
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'check_process_status',process_num:process_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("check_process_status ('"+process_num+"')");
			} else {
				tab=contenu.split(';');
				status=tab[0];
				commentary=tab[1];
				jQuery("#"+id_display_commentary).html(commentary); 
				if (status=='1') { // end
					jQuery("#"+id_display_commentary).html(""); 
				} else {
					setTimeout("check_process_status('"+process_num+"','"+id_display_commentary+"')",1000);
				}
			}
		}
	});
}



function list_query_history (datamart_num) {
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:true,
		data: { action:'list_query_history',datamart_num:datamart_num},
		beforeSend: function(requester){
			jQuery("#id_div_list_query_history").html("<img src='images/chargement_mac.gif'>"); 
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion("list_query_history ('"+datamart_num+"')");
			} else {
				jQuery('#id_div_list_query_history').html(requester);
				$.fn.dataTable.moment( 'DD/MM/YYYY HH:mm' );
			 	jQuery('#id_tableau_liste_requete').dataTable( { 
			 		"scrollY": "500px",
					  "scrollCollapse": true,
					  "paging": false,
					"order": [[ 0, "asc" ]],
					   "bInfo" : false,
					   "bDestroy" : true
				} );
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});
}


function get_json_query_history (datamart_num) {
   // $.fn.dataTable.moment( 'DD/MM/YYYY' );
	$.fn.dataTable.moment( 'DD/MM/YYYY HH:mm' );
	jQuery('#id_tableau_liste_requete').DataTable({
		"scrollY": "500px",
		"paging":   true,
		"scrollCollapse": true,
		"processing":   true,
		"serverSide":   true,
		"order": [[ 1, "desc" ]],
		"ajax":   'ajax.php?action=get_json_query_history&datamart_num='+datamart_num,
		"lengthMenu": [[20,100, 250, 500, -1], [20,100, 250, 500, "All"]],
		"bInfo" : false,
		"deferRender": true,
		"bSort": true,
		"columnDefs": [{ targets: [2], orderable: false},{ targets: [3], visible: false}]
	} );
	var table = $('#id_tableau_liste_requete').DataTable();
	jQuery('#id_tableau_liste_requete').on('click', 'tbody tr', function () {
		var row = table.row($(this)).data();
		charger_moteur_recherche(row[3]);
	});
}

var num_filtre_open_quick_test_fulltext_query='';
function quick_test_fulltext_query (num_filtre) {
	if (num_filtre!='') {
		if (num_filtre_open_quick_test_fulltext_query==num_filtre) {
			num_filtre_open_quick_test_fulltext_query='';
			jQuery('#id_quick_test_fulltext_research').css("display","none");
			return false;
		}
		jQuery('#id_input_filtre_quick_test_fulltext_research').val(num_filtre);
		query=jQuery('#id_input_filtre_texte_'+num_filtre).val();
		jQuery('#id_input_text_quick_test_fulltext_research').val(query);
	} else {
		num_filtre=jQuery('#id_input_filtre_quick_test_fulltext_research').val();
	}
	num_filtre_open_quick_test_fulltext_query=num_filtre;
	
	datamart_num=jQuery('#id_num_datamart').val();
	nb_doc=jQuery('#id_input_nb_quick_test_fulltext_research').val();
	
	query=jQuery('#id_input_text_quick_test_fulltext_research').val();
	val_top_list_doc=getTop(document.getElementById("id_div_filtre_texte_"+num_filtre));
	jQuery('#id_quick_test_fulltext_research').css("top",val_top_list_doc);
	jQuery('#id_quick_test_fulltext_research').css("display","inline");
	query=jQuery('#id_input_text_quick_test_fulltext_research').val();
	
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:true,
		data: { action:'quick_test_fulltext_query',datamart_num:datamart_num,query:escape(query),nb_doc:nb_doc},
		beforeSend: function(requester){
			jQuery("#id_quick_test_fulltext_research_result").html("<img src='images/chargement_mac.gif'>"); 
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion("quick_test_fulltext_query ('"+num_filtre+"')");
			} else {
				jQuery('#id_quick_test_fulltext_research_result').html(requester);
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});
}


function position_info_new_feature (id,text,j) {
		val_top=getTop(document.getElementById(id));
		val_height=jQuery('#'+id).height();
		val_left=getLeft(document.getElementById(id));
		val_top_total=eval(val_top+val_height+12);
		jQuery('#id_div_info_new_feature_'+j).css('top',val_top_total);		
		jQuery('#id_div_info_new_feature_'+j).css('left',val_left);	
		jQuery('#id_div_info_new_feature_'+j).css('display','inline');	
		jQuery('#id_span_info_new_feature_'+j).html(text);		
}

function html_replace_supinf (str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}


function check_logical_constraint () {
	logical_constraint=jQuery('#id_logical_constraint').val();
	if (logical_constraint!='') {
		// " 2 ("   ou " ) ( "
		if (logical_constraint.match(/([0-9]|[)]) *[(]/)) {
			alert("Erreur dans la structure de la contrainte logique");
			return false;
		}
		// " ) 3 "   ou " ) ( "
		if (logical_constraint.match(/[)] *([0-9]|[(])/)) {
			alert("Erreur dans la structure de la contrainte logique");
			return false;
		}
		
		// "and or" or "or or" or "or and" or "and and"
		if (logical_constraint.match(/(and|or|not) *(and|or|not)/)) {
			alert("Erreur dans la structure de la contrainte logique");
			return false;
		}
	
		// tout saug 123456789andornot()
		if (logical_constraint.match(/[^123456789andornot() ]/)) {
			alert("Erreur dans la structure de la contrainte logique");
			return false;
		}
		
		// no spaces between number
		if (logical_constraint.match(/[0-9] +[0-9]/)) {
			alert("Erreur dans la structure de la contrainte logique");
			return false;
		}
		nb_a=logical_constraint.match(/[(]/g).length;
		nb_b=logical_constraint.match(/[)]/g).length;
		if (nb_a!=nb_b) {
			alert("Erreur dans la structure de la contrainte logique : nb de parenthèses ouvertes != nb arenthèses fermées");
			return false;
		}
	}
}