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

	if (typeof translations[code] !== 'undefined') {
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
				document.getElementById('plus_'+id).innerHTML='+';
			}
		} else {
			document.getElementById(id).style.display='block';
			if (document.getElementById('plus_'+id)) {
				document.getElementById('plus_'+id).innerHTML='-';
			}
		}
	
	}
}

function plier (id) {
	if (document.getElementById(id) ) {
		document.getElementById(id).style.display='none';
		if (document.getElementById('plus_'+id)) {
			document.getElementById('plus_'+id).innerHTML='+';
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
			document.getElementById('plus_'+id).innerHTML='-';
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
	
		document.getElementById('id_span_nbresult_atomique_'+num_filtre).innerHTML='';
		text=document.getElementById('id_input_filtre_texte_'+num_filtre).value;
		chaine_requete_code=document.getElementById('id_input_chaine_requete_code_'+num_filtre).value;
		thesaurus_data_num=document.getElementById('id_input_thesaurus_data_num_'+num_filtre).value;
		id_query_type=document.getElementById('id_query_type_'+num_filtre).value;
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
			title_document=document.getElementById('id_input_filtre_title_document_'+num_filtre).value;
			date_deb_document=document.getElementById('id_input_filtre_date_deb_document_'+num_filtre).value;
			date_fin_document=document.getElementById('id_input_filtre_date_fin_document_'+num_filtre).value;
			periode_document=document.getElementById('id_input_filtre_periode_document_'+num_filtre).value;
			age_deb_document=document.getElementById('id_input_filtre_age_deb_document_'+num_filtre).value;
			age_fin_document=document.getElementById('id_input_filtre_age_fin_document_'+num_filtre).value;
			agemois_deb_document=document.getElementById('id_input_filtre_agemois_deb_document_'+num_filtre).value;
			agemois_fin_document=document.getElementById('id_input_filtre_agemois_fin_document_'+num_filtre).value;
			context=document.getElementById('id_select_filtre_contexte_'+num_filtre).value;
			certainty=document.getElementById('id_select_filtre_certitude_'+num_filtre).value;
			if (document.getElementById('id_input_filtre_exclure_'+num_filtre).checked==true) {
				exclure=document.getElementById('id_input_filtre_exclure_'+num_filtre).value;
			} else {
				exclure='';
			}
			if (document.getElementById('id_checkbox_etendre_syno_'+num_filtre).checked==true) {
				etendre_syno=document.getElementById('id_checkbox_etendre_syno_'+num_filtre).value;
			} else {
				etendre_syno='';
			}
		//	document_origin_code=document.getElementById('id_select_filtre_document_origin_code_'+num_filtre).value;
			
			datamart_num=document.getElementById('id_num_datamart').value;
			plier('id_bouton_submit_moteur');
			deplier('id_bouton_attendre_moteur','block');
			nb_num_filtre_en_cours++;
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:val_async,
				encoding: 'latin1',
				data:{ action:'calcul_nb_resultat_filtre_passthru',num_filtre:num_filtre,text:escape(text),etendre_syno:etendre_syno,id_query_type:id_query_type,thesaurus_data_num:thesaurus_data_num,chaine_requete_code:chaine_requete_code,hospital_department_list:escape(hospital_department_list),date_deb_document:escape(date_deb_document),date_fin_document:escape(date_fin_document),periode_document:periode_document,age_deb_document:escape(age_deb_document),age_fin_document:escape(age_fin_document),agemois_deb_document:escape(agemois_deb_document),agemois_fin_document:escape(agemois_fin_document),context:escape(context),certainty:escape(certainty),document_origin_code:escape(document_origin_code),datamart_num:datamart_num,exclure:exclure,title_document:escape(title_document)},
				beforeSend: function(requester){
					document.getElementById('id_span_nbresult_atomique_chargement_'+num_filtre).innerHTML='<img src="images/chargement_mac.gif" width="10px">';
					document.getElementById('id_span_nbresult_atomique_'+num_filtre).style.color='red';
				},
				success: function(requester){
					var contenu=requester;
					if (contenu=='deconnexion') {
						afficher_connexion("calcul_nb_resultat_filtre("+num_filtre+","+val_async+")");
					} else { 
						document.getElementById('id_query_key_'+num_filtre).value=contenu;
						setTimeout("calcul_nb_resultat_final_passthru('"+num_filtre+"','"+contenu+"')",1000);
					}
				},
				complete: function(requester){
					
				},
				error: function(){
					document.getElementById('id_span_nbresult_atomique_chargement_'+num_filtre).innerHTML='';
				}
			});
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
	datamart_num=document.getElementById('id_num_datamart').value;
	if (document.getElementById('id_query_key_contrainte_temporelle_'+num_filtre)) {
		query_key_contrainte_temporelle=document.getElementById('id_query_key_contrainte_temporelle_'+num_filtre).value;
		tab=query_key_contrainte_temporelle.split(';');
		num_filtre_a=tab[1];
		num_filtre_b=tab[2];
		query_key_a=document.getElementById('id_query_key_'+num_filtre_a).value;
		query_key_b=document.getElementById('id_query_key_'+num_filtre_b).value;
		if (query_key_a=='' || query_key_b=='') {
			document.getElementById('id_span_nbresult_atomique_chargement_'+num_filtre).innerHTML='<img src="images/chargement_mac.gif" width="10px">';
			calcul_nb_resultat_filtre (num_filtre_a,false);
			calcul_nb_resultat_filtre (num_filtre_b,false);
			verification_calcul_nb_resultat_filtre_fini(num_filtre,val_async);
			return ;
		}
		
		if (query_key_contrainte_temporelle!='' && query_key_a!='' && query_key_b!='') {
			document.getElementById('id_span_nbresult_atomique_'+num_filtre).innerHTML='';
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
					document.getElementById('id_span_nbresult_atomique_chargement_'+num_filtre).innerHTML='<img src="images/chargement_mac.gif" width="10px">';
					document.getElementById('id_span_nbresult_atomique_'+num_filtre).style.color='red';
				},
				success: function(requester){
					var contenu=requester;
					if (contenu=='deconnexion') {
						afficher_connexion("calcul_nb_resultat_contrainte_temporelle("+num_filtre+","+val_async+")");
					} else { 
						document.getElementById('id_query_key_'+num_filtre).value=contenu;
						setTimeout("calcul_nb_resultat_final_passthru('"+num_filtre+"','"+contenu+"')",1000);
					}
				},
				complete: function(requester){
					
				},
				error: function(){
					document.getElementById('id_span_nbresult_atomique_chargement_'+num_filtre).innerHTML='';
				}
			});
		}
	}
}




function calcul_nb_resultat_final_passthru(num_filtre,query_key) {
	datamart_num=document.getElementById('id_num_datamart').value;
	plier('id_bouton_submit_moteur');
	deplier('id_bouton_attendre_moteur','block');
	if (document.getElementById('id_query_key_'+num_filtre)) {
		query_key_debut=document.getElementById('id_query_key_'+num_filtre).value;
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
							document.getElementById('id_span_nbresult_atomique_'+num_filtre).innerHTML=nb_patient+"/"+nb_patient_total;
							document.getElementById('id_input_nbresult_atomique_'+num_filtre).value=nb_patient;
							if (status_calculate==1) {
								document.getElementById('id_span_nbresult_atomique_chargement_'+num_filtre).innerHTML='';
								document.getElementById('id_span_nbresult_atomique_'+num_filtre).style.color='green';
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
					document.getElementById('id_span_nbresult_atomique_chargement_'+num_filtre).innerHTML='';
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
	
	text=document.getElementById('id_input_filtre_texte_'+num_filtre).value;
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
				document.getElementById('id_div_correction_orthographique_propositions_'+num_filtre).innerHTML=contenu;
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});
}

function remplacer_texte (num_filtre,text) {
	document.getElementById('id_input_filtre_texte_'+num_filtre).value=text;
	calcul_nb_resultat_filtre (num_filtre,true);
}

function calcul_nb_resultat_filtre_notpassthru(num_filtre,val_async) {
	if (document.getElementById('id_span_nbresult_atomique_'+num_filtre)) {
	
		document.getElementById('id_span_nbresult_atomique_'+num_filtre).innerHTML='';
		text=document.getElementById('id_input_filtre_texte_'+num_filtre).value;
		chaine_requete_code=document.getElementById('id_input_chaine_requete_code_'+num_filtre).value;
		thesaurus_data_num=document.getElementById('id_input_thesaurus_data_num_'+num_filtre).value;
		id_query_type=document.getElementById('id_query_type_'+num_filtre).value;
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
			title_document=document.getElementById('id_input_filtre_title_document_'+num_filtre).value;
			date_deb_document=document.getElementById('id_input_filtre_date_deb_document_'+num_filtre).value;
			date_fin_document=document.getElementById('id_input_filtre_date_fin_document_'+num_filtre).value;
			periode_document=document.getElementById('id_input_filtre_periode_document_'+num_filtre).value;
			age_deb_document=document.getElementById('id_input_filtre_age_deb_document_'+num_filtre).value;
			age_fin_document=document.getElementById('id_input_filtre_age_fin_document_'+num_filtre).value;
			agemois_deb_document=document.getElementById('id_input_filtre_agemois_deb_document_'+num_filtre).value;
			agemois_fin_document=document.getElementById('id_input_filtre_agemois_fin_document_'+num_filtre).value;
			context=document.getElementById('id_select_filtre_contexte_'+num_filtre).value;
			certainty=document.getElementById('id_select_filtre_certitude_'+num_filtre).value;
			if (document.getElementById('id_input_filtre_exclure_'+num_filtre).checked==true) {
				exclure=document.getElementById('id_input_filtre_exclure_'+num_filtre).value;
			} else {
				exclure='';
			}
			if (document.getElementById('id_checkbox_etendre_syno_'+num_filtre).checked==true) {
				etendre_syno=document.getElementById('id_checkbox_etendre_syno_'+num_filtre).value;
			} else {
				etendre_syno='';
			}
			//document_origin_code=document.getElementById('id_select_filtre_document_origin_code_'+num_filtre).value;
			
			datamart_num=document.getElementById('id_num_datamart').value;
			plier('id_bouton_submit_moteur');
			deplier('id_bouton_attendre_moteur','block');
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:val_async,
				encoding: 'latin1',
				data:{ action:'calcul_nb_resultat_filtre',num_filtre:num_filtre,text:escape(text),etendre_syno:etendre_syno,id_query_type:id_query_type,thesaurus_data_num:thesaurus_data_num,chaine_requete_code:chaine_requete_code,hospital_department_list:escape(hospital_department_list),date_deb_document:escape(date_deb_document),date_fin_document:escape(date_fin_document),periode_document:periode_document,age_deb_document:escape(age_deb_document),age_fin_document:escape(age_fin_document),agemois_deb_document:escape(agemois_deb_document),agemois_fin_document:escape(agemois_fin_document),context:escape(context),certainty:escape(certainty),document_origin_code:escape(document_origin_code),datamart_num:datamart_num,exclure:exclure,title_document:escape(title_document)},
				beforeSend: function(requester){
					document.getElementById('id_span_nbresult_atomique_chargement_'+num_filtre).innerHTML='<img src="images/chargement_mac.gif" width="10px">';
				},
				success: function(requester){
					var contenu=requester;
					if (contenu=='deconnexion') {
						afficher_connexion("calcul_nb_resultat_filtre_notpassthru("+num_filtre+","+val_async+")");
					} else {
						document.getElementById('id_span_nbresult_atomique_'+num_filtre).innerHTML=contenu;
						document.getElementById('id_input_nbresult_atomique_'+num_filtre).value=contenu;
					}
					document.getElementById('id_span_nbresult_atomique_chargement_'+num_filtre).innerHTML='';
					deplier('id_bouton_submit_moteur','block');
					plier('id_bouton_attendre_moteur');
				},
				complete: function(requester){
					
				},
				error: function(){
					document.getElementById('id_span_nbresult_atomique_chargement_'+num_filtre).innerHTML='';
				}
			});
		}
	}
}

function ajouter_formulaire_texte_vierge(query_type) {
	num_filtre=eval(document.getElementById('id_input_max_num_filtre').value);
	num_filtre=eval(num_filtre+1);
	document.getElementById('id_input_max_num_filtre').value=num_filtre;
	
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:false,
		encoding: 'latin1',
		data:{ action:'ajouter_formulaire_texte_vierge',num_filtre:num_filtre,query_type:query_type},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("ajouter_formulaire_texte_vierge('"+query_type+"')");
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

function supprimer_formulaire_texte_vierge(num_filtre) {
	document.getElementById('id_div_filtre_texte_'+num_filtre).innerHTML='';
	document.getElementById('id_div_filtre_texte_'+num_filtre).style.display='none';
	ajouter_filtre_select_contrainte();
}

function supprimer_formulaire_contrainte_temporelle(num_filtre) {
	document.getElementById('id_div_filtre_contrainte_temporelle_'+num_filtre).innerHTML='';
	document.getElementById('id_div_filtre_contrainte_temporelle_'+num_filtre).style.display='none';
	ajouter_filtre_select_contrainte();
}


function ajouter_contrainte_temporelle () {
	document.getElementById('id_span_alerte').innerHTML="";
	num_filtre_a=document.getElementById('id_contrainte_select_liste_sous_requete_a').value;
	num_filtre_b=document.getElementById('id_contrainte_select_liste_sous_requete_b').value;
	if (num_filtre_a==num_filtre_b || num_filtre_a=='' || num_filtre_b=='' ) {
		document.getElementById('id_span_alerte').innerHTML= get_translation('JS_IL_FAUT_CHOISIR_DEUX_FILTRES_DIFFERENTS','Il faut choisir deux filtres différents');
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
		minmax=document.getElementById('id_detail_contrainte_beforeafter_minmax').value;
		duree_contrainte=document.getElementById('id_detail_contrainte_beforeafter_duree').value;
		unite_contrainte=document.getElementById('id_detail_contrainte_beforeafter_unite').value;
		type_contrainte='beforeafter';
		if (duree_contrainte=='') {
			document.getElementById('id_span_alerte').innerHTML= get_translation('JS_IL_FAUT_PRECISER_UNE_DUREE','Il faut préciser une durée');
			return false;
		}
	}
	if (document.getElementById('id_contrainte_type_contrainte_periode').checked) {
		minmax=document.getElementById('id_detail_contrainte_periode_minmax').value;
		duree_contrainte=document.getElementById('id_detail_contrainte_periode_duree').value;
		unite_contrainte=document.getElementById('id_detail_contrainte_periode_unite').value;
		type_contrainte='periode';
		if (duree_contrainte=='') {
			document.getElementById('id_span_alerte').innerHTML=get_translation('JS_IL_FAUT_CHOISIR_UNE_DUREE','Il faut choisir une durée');
			return false;
		}
	}
	if (type_contrainte=='') {
		document.getElementById('id_span_alerte').innerHTML=get_translation('JS_IL_FAUT_PRECISER_UN_TYPE_DE_CONTRAINTE','Il faut préciser un type de contrainte');
		return false;
	}

	num_filtre=eval(document.getElementById('id_input_max_num_filtre').value);
	num_filtre=eval(num_filtre+1);
	document.getElementById('id_input_max_num_filtre').value=num_filtre;
	
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
		if (document.getElementById('id_div_filtre_texte_'+i)) {
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
	if (document.getElementById('id_exclure_cohorte_resultat').value=='') {
		val_exclure_cohorte_resultat='ok';
		document.getElementById('id_img_exclure_cohorte_resultat').src='images/voir_tout.png';
		document.getElementById('id_texte_exclure_cohorte_resultat').innerHTML=get_translation('JS_AFFICHER_TOUS_LES_PATIENTS','Afficher tous les patients');
	} else {
		val_exclure_cohorte_resultat='';
		document.getElementById('id_img_exclure_cohorte_resultat').src='images/pas_voir_tout.png';
		document.getElementById('id_texte_exclure_cohorte_resultat').innerHTML=get_translation('JS_NE_PAS_AFFICHER_LES_PATIENTS_DEJA_INCLUS_OU_EXCLUS','Ne pas afficher les patients déjà inclus ou exclus');
	}
	datamart_num=document.getElementById('id_num_datamart').value;
	document.getElementById('id_num_last_ligne').value=0;
	document.getElementById('id_exclure_cohorte_resultat').value=val_exclure_cohorte_resultat;
	cohort_num_encours=document.getElementById('id_cohort_num_encours').value;
	tmpresult_num=document.getElementById('id_num_temp').value;
	filtre_resultat_texte=document.getElementById('id_filtre_resultat_texte').value;
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
				document.getElementById('id_total_ligne').value=requester;
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
	document.getElementById('id_filtre_resultat_texte').value=filtre;
	filtrer_resultat();
}

function filtrer_resultat() {
	datamart_num=document.getElementById('id_num_datamart').value;
	document.getElementById('id_num_last_ligne').value=0;
	val_exclure_cohorte_resultat=document.getElementById('id_exclure_cohorte_resultat').value;
	cohort_num_encours=document.getElementById('id_cohort_num_encours').value;
	tmpresult_num=document.getElementById('id_num_temp').value;
	filtre_resultat_texte=document.getElementById('id_filtre_resultat_texte').value;
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
				document.getElementById('id_total_ligne').value=requester;
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
	tmpresult_num=document.getElementById('id_num_temp').value;
	num_last_ligne=document.getElementById('id_num_last_ligne').value;
	full_text_query=document.getElementById('id_full_text_query').value;
	datamart_num=document.getElementById('id_num_datamart').value;
	cohort_num_encours=document.getElementById('id_cohort_num_encours').value;
	val_exclure_cohorte_resultat=document.getElementById('id_exclure_cohorte_resultat').value;
	filtre_resultat_texte=document.getElementById('id_filtre_resultat_texte').value;
	if (filtre_resultat_texte!='') {
		filtre_resultat_texte=filtre_resultat_texte.replace("'"," ");
		full_text_query=full_text_query+';requete_unitaire;'+filtre_resultat_texte+';requete_unitaire;';
	}

	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'afficher_resultat',tmpresult_num:tmpresult_num,num_last_ligne:num_last_ligne,full_text_query:escape(full_text_query),datamart_num:datamart_num,val_exclure_cohorte_resultat:val_exclure_cohorte_resultat,cohort_num_encours:cohort_num_encours,filtre_resultat_texte:escape(filtre_resultat_texte)},
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
				document.getElementById('id_num_last_ligne').value=parseInt(document.getElementById('id_num_last_ligne').value)+parseInt(document.getElementById('id_modulo_ligne_ajoute').value);
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
	tmpresult_num=document.getElementById('id_num_temp').value;
	num_last_ligne=document.getElementById('id_num_last_ligne').value;
	full_text_query=document.getElementById('id_full_text_query').value;
	datamart_num=document.getElementById('id_num_datamart').value;
	cohort_num_encours=document.getElementById('id_cohort_num_encours').value;

	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:false,
		encoding: 'latin1',
		data:{ action:'afficher_resultat',tmpresult_num:tmpresult_num,num_last_ligne:num_last_ligne,full_text_query:escape(full_text_query),datamart_num:datamart_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("affiner_resultat ()");
			} else {
				jQuery("#id_tableau_resultat").append(contenu);
				document.getElementById('id_num_last_ligne').value=parseInt(document.getElementById('id_num_last_ligne').value)+parseInt(document.getElementById('id_modulo_ligne_ajoute').value);
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
	
	document.getElementById('id_commande_a_rejouer').value=commande;
	
}


function connecter() {
	var login=document.getElementById('id_login').value;
	var passwd=document.getElementById('id_passwd').value;
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
						document.getElementById('id_passwd').value='';
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
	tmpresult_num=document.getElementById('id_num_temp').value;
	full_text_query=document.getElementById('id_full_text_query').value;
	datamart_num=document.getElementById('id_num_datamart').value;
	filtre_resultat_texte=document.getElementById('id_filtre_resultat_texte').value;
	if (filtre_resultat_texte!='') {
		full_text_query=full_text_query+';requete_unitaire;'+filtre_resultat_texte;
	}

	if (!document.getElementById('id_doc_document_'+document_num)) {
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:false,
			encoding: 'latin1',
			data:{ action:'afficher_document',tmpresult_num:tmpresult_num,document_num:document_num,full_text_query:escape(full_text_query),datamart_num:datamart_num},
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
					afficher_connexion("afficher_document_patient_popup('"+document_num+"','"+full_text_query+"',"+datamart_num+",'"+id_cle+"')");
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

function ouvrir_plus_document (patient_num) {
	liste_document=document.getElementById('id_input_liste_document_plus_'+patient_num).value;
	full_text_query=document.getElementById('id_full_text_query').value;
	datamart_num=document.getElementById('id_num_datamart').value;
	filtre_resultat_texte=document.getElementById('id_filtre_resultat_texte').value;
	if (filtre_resultat_texte!='') {
		full_text_query=full_text_query+';requete_unitaire;'+filtre_resultat_texte;
	}
	plier_deplier("id_div_ouvrir_plus_document_"+patient_num);
	
	if (document.getElementById('id_div_ouvrir_plus_document_'+patient_num).innerHTML=='') {
		document.getElementById('id_div_ouvrir_plus_document_'+patient_num).innerHTML="<img src=\"images/chargement_mac.gif\">";
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:false,
			encoding: 'latin1',
			data:{ action:'ouvrir_plus_document',patient_num:patient_num,liste_document:liste_document,full_text_query:escape(full_text_query),datamart_num:datamart_num},
			beforeSend: function(requester){
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("ouvrir_plus_document ('"+patient_num+"')");
				} else {
					document.getElementById('id_div_ouvrir_plus_document_'+patient_num).innerHTML=contenu; 
					
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
	$('#id_document_'+document_num).css('height',$('#id_enveloppe_document_'+document_num).height() -$('#id_bandeau_'+document_num).height() - $('#id_onglet_'+document_num).height() -25);
	$('#id_enveloppe_document_'+document_num)
		.resizable({
		        start: function(e, ui) {
				mettre_index_haut_document(document_num);
		        },
		        resize: function(e, ui) {
				$('#id_document_'+document_num).css('width',$('#id_enveloppe_document_'+document_num).width()-25);
				$('#id_document_'+document_num).css('height',$('#id_enveloppe_document_'+document_num).height() -$('#id_bandeau_'+document_num).height()- $('#id_onglet_'+document_num).height() -25);
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
	
	if (document.getElementById('id_enveloppe_document_'+id).style.display=='block') {
		jQuery('#id_enveloppe_document_'+id).css('zIndex',index_haut);
		jQuery('.div_voir_document').css('font-weight','normal');
		jQuery('#id_button_'+id).css('font-weight','bold');
		jQuery('.voir_document').css('color','');
		jQuery('#id_button_'+id).find('.voir_document').css('color','#CD1DAA');
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
	if (document.getElementById('id_enveloppe_document_'+id).style.display=='block') {
		document.getElementById('id_enveloppe_document_'+id).style.display='none';
	}
}

function rechercher_code (num_filtre) {


	requete_texte=document.getElementById('id_rechercher_code_'+num_filtre).value;
	thesaurus_code=document.getElementById('id_select_thesaurus_data_'+num_filtre).value;
	
	document.getElementById('id_div_resultat_recherche_code_'+num_filtre).innerHTML="<img src=images/chargement_mac.gif>";
	if (requete_texte!='' && thesaurus_code!='') {
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
					document.getElementById('id_div_resultat_recherche_code_'+num_filtre).innerHTML=requester;
				}
			},
			complete: function(requester){
			},
			error: function(){
					}
		});
	} else {
		document.getElementById('id_div_resultat_recherche_code_'+num_filtre).innerHTML="";
		if (thesaurus_code=='') {
			alert(get_translation('JS_PRECISEZ_UN_THESAURUS','précisez un thesaurus'));
		}
	}
}

function rechercher_code_sous_thesaurus (num_filtre,thesaurus_data_father_num,sans_filtre) {
	requete_texte=document.getElementById('id_rechercher_code_'+num_filtre).value;
	thesaurus_code=document.getElementById('id_select_thesaurus_data_'+num_filtre).value;
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
					document.getElementById('plus_'+id).innerHTML="-";
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
			document.getElementById('plus_'+id).innerHTML="+";
		}
	}
}

function ajouter_formulaire_code(num_filtre,thesaurus_data_num) {
	document.getElementById('id_span_nbresult_atomique_'+num_filtre).innerHTML='';
	document.getElementById('id_input_thesaurus_data_num_'+num_filtre).value='';
	document.getElementById('id_input_chaine_requete_code_'+num_filtre).value='';
	document.getElementById('id_query_key_'+num_filtre).value='';
	document.getElementById('id_input_nbresult_atomique_'+num_filtre).value='';
	if (thesaurus_data_num!='') {
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
					document.getElementById('id_div_selection_code_'+num_filtre).innerHTML=requester;
					document.getElementById('id_input_thesaurus_data_num_'+num_filtre).value=thesaurus_data_num;
					creer_chaine_requete_code(num_filtre);
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
}

function vider_items_code (num_filtre,type_item) {
	if (type_item=='hors_borne') {
		document.getElementById('id_select_operateur_'+num_filtre).value='';
		document.getElementById('id_input_valeur_'+num_filtre).value='';
		document.getElementById('id_input_valeur_deb_'+num_filtre).value='';
		document.getElementById('id_input_valeur_fin_'+num_filtre).value='';
		document.getElementById('id_input_valeur_sup_n_x_borne_sup_'+num_filtre).value='';
		document.getElementById('id_input_valeur_inf_n_x_borne_inf_'+num_filtre).value='';
	}
	if (type_item=='operateur') {
		document.getElementById('id_select_hors_borne_'+num_filtre).value='';
		document.getElementById('id_input_valeur_deb_'+num_filtre).value='';
		document.getElementById('id_input_valeur_fin_'+num_filtre).value='';
		document.getElementById('id_input_valeur_sup_n_x_borne_sup_'+num_filtre).value='';
		document.getElementById('id_input_valeur_inf_n_x_borne_inf_'+num_filtre).value='';
	}
	if (type_item=='valeur') {
		document.getElementById('id_select_hors_borne_'+num_filtre).value='';
		document.getElementById('id_select_operateur_'+num_filtre).value='';
		document.getElementById('id_input_valeur_'+num_filtre).value='';
		document.getElementById('id_input_valeur_sup_n_x_borne_sup_'+num_filtre).value='';
		document.getElementById('id_input_valeur_inf_n_x_borne_inf_'+num_filtre).value='';
	}
	if (type_item=='n_x_borne') {
		document.getElementById('id_select_hors_borne_'+num_filtre).value='';
		document.getElementById('id_select_operateur_'+num_filtre).value='';
		document.getElementById('id_input_valeur_'+num_filtre).value='';
		document.getElementById('id_input_valeur_deb_'+num_filtre).value='';
		document.getElementById('id_input_valeur_fin_'+num_filtre).value='';
	}
}

function creer_chaine_requete_code (num_filtre) {
	chaine_requete_code_preced=document.getElementById('id_input_chaine_requete_code_'+num_filtre).value;
	
	hors_borne='';
	operateur='';
	valeur='';
	valeur_deb='';
	valeur_fin='';
	valeur_sup_n_x_borne_sup='';
	valeur_inf_n_x_borne_inf='';
	liste_valeur_checked='';
	
	if (document.getElementById('id_select_hors_borne_'+num_filtre)) {
		hors_borne=document.getElementById('id_select_hors_borne_'+num_filtre).value;
		operateur=document.getElementById('id_select_operateur_'+num_filtre).value;
		valeur=document.getElementById('id_input_valeur_'+num_filtre).value;
		valeur_deb=document.getElementById('id_input_valeur_deb_'+num_filtre).value;
		valeur_fin=document.getElementById('id_input_valeur_fin_'+num_filtre).value;
		valeur_sup_n_x_borne_sup=document.getElementById('id_input_valeur_sup_n_x_borne_sup_'+num_filtre).value;
		valeur_inf_n_x_borne_inf=document.getElementById('id_input_valeur_inf_n_x_borne_inf_'+num_filtre).value;
		
		if (valeur=='') {
			operateur='';
		}
		if (operateur=='') {
			valeur='';
		}
	} else {
		$('#id_div_list_checkbox_thesaurus_data_'+num_filtre+' :checked').each(function() {
			liste_valeur_checked=liste_valeur_checked+"^"+$(this).val();
		});
		if (liste_valeur_checked=='') {
			liste_valeur_checked=1;
		}
	}
	
	
	chaine_requete_code=hors_borne+";"+operateur+";"+valeur+";"+valeur_deb+";"+valeur_fin+";"+valeur_sup_n_x_borne_sup+";"+valeur_inf_n_x_borne_inf+";"+liste_valeur_checked;
	if (chaine_requete_code==';;;;;;;') {
		chaine_requete_code='';
	}
	document.getElementById('id_input_chaine_requete_code_'+num_filtre).value=chaine_requete_code;
	
	if (chaine_requete_code_preced!=chaine_requete_code) {
		calcul_nb_resultat_filtre(num_filtre,true);
	
	}
}


var verif_script_lance_2='';
function verif_chaine_modifiee_avant_creer_chaine(num_filtre,id,val) {
	valeur=document.getElementById(id).value;
	if (valeur==val) {
		verif_script_lance_2='';
		creer_chaine_requete_code (num_filtre);
	} else {
		if (verif_script_lance_2=='') {
			verif_script_lance_2='ok';
			setTimeout("verif_chaine_modifiee_avant_creer_chaine('"+num_filtre+"','"+id+"','"+valeur+"')",1000);
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
		calcul_nb_resultat_filtre (num_filtre,true);
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

function modifier_libelle_service(department_num) {
	department_str=document.getElementById('id_input_libelle_service_'+department_num).value;
	department_code=document.getElementById('id_input_code_service_'+department_num).value;
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:true,
		data: { action:'modifier_libelle_service',department_str:escape(department_str),department_code:escape(department_code),department_num:department_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("modifier_libelle_service("+department_num+")");
			} else {
				if (contenu=='erreur') {
					alert(get_translation('JS_UNE_ERREUR_EST_SURVENUE','Une erreur est survenue'));
				} else {
					document.getElementById('id_div_libelle_service_'+department_num).innerHTML="<strong>"+department_code+" "+department_str+"</strong>";
					document.getElementById('id_div_libelle_service_'+department_num).style.display='block';
					document.getElementById('id_div_libelle_service_modifier_'+department_num).style.display='none';
				}
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		
}



function autocomplete_service (department_num) {
	
	jQuery( "#id_champs_recherche_rapide_utilisateur_"+department_num ).autocomplete({
		source: "ajax.php?action=autocomplete_rech_rapide_utilisateur",
		minLength: 2, 
		search: function( event, ui ) {document.getElementById('id_chargement_'+department_num).style.display='block';},
		select: function( event, ui ) {
			document.getElementById('id_chargement_'+department_num).style.display='none';
			document.getElementById('id_div_reponse_service_'+department_num).innerHTML='';
			document.getElementById('id_champs_recherche_rapide_utilisateur_'+department_num).value='';
			document.getElementById('id_textarea_ajouter_user_'+department_num).value=document.getElementById('id_textarea_ajouter_user_'+department_num).value+ui.item.id+'\n';
			ajouter_user(department_num);
			return false;
		}
	});
}

function ajouter_user(department_num) {
	liste_login=document.getElementById('id_textarea_ajouter_user_'+department_num).value;
	jQuery.ajax({
	type:"POST",
	url:"ajax.php",
	async:true,
	data: { action:'ajouter_user',liste_login:escape(liste_login),department_num:department_num},
	beforeSend: function(requester){
		document.getElementById('id_div_service_'+department_num).style.display='block';
		document.getElementById('id_div_reponse_service_'+department_num).innerHTML='';
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
				
				document.getElementById('id_div_reponse_service_'+department_num).innerHTML=get_translation('JS_UTILISATEURS_AJOUTES_AVEC_SUCCES','Utilisateurs ajoutés avec succès');
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


function supprimer_service(department_num) {
	if (confirm (get_translation('ETES_VOUS_SUR_DE_VOULOIR_SUPPRIMER_CET_UF','Etes vous sûr de vouloir supprimer ce service')+' ?')) {
		jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:true,
		data: { action:'supprimer_service',department_num:department_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("supprimer_service("+department_num+")");
			} else {
				if (contenu=='erreur') {
					alert(get_translation('JS_UNE_ERREUR_EST_SURVENUE','Une erreur est survenue'));
				} else {
					$("tr#id_tr_groupe_"+department_num).remove();
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

function ajouter_uf(department_num) {
	unit_code=document.getElementById('id_input_unit_code_'+department_num).value;
	unit_str=document.getElementById('id_input_unit_str_'+department_num).value;
	unit_start_date=document.getElementById('id_input_date_deb_uf_'+department_num).value;
	unit_end_date=document.getElementById('id_input_date_fin_uf_'+department_num).value;
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:true,
		data: { action:'ajouter_uf',unit_str:escape(unit_str),unit_code:escape(unit_code),department_num:department_num,unit_start_date:escape(unit_start_date),unit_end_date:escape(unit_end_date)},
		beforeSend: function(requester){
		},
		success: function(requester){
			contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("ajouter_uf("+department_num+")");
			} else {
				if (contenu=='erreur') {
					alert(get_translation('JS_UNE_ERREUR_EST_SURVENUE','Une erreur est survenue'));
				} else {
					$('#id_tableau_uf_'+department_num).append(contenu);
					document.getElementById('id_input_unit_code_'+department_num).value='';
					document.getElementById('id_input_unit_str_'+department_num).value='';
				}
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		
}


function ajouter_service() {
	department_str=document.getElementById('id_service_ajouter').value;
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:true,
		data: { action:'ajouter_service',department_str:escape(department_str)},
		beforeSend: function(requester){
		},
		success: function(requester){
			contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("ajouter_service()");
			} else {
				if (contenu=='erreur') {
					alert(get_translation('JS_UNE_ERREUR_EST_SURVENUE','Une erreur est survenue'));
				} else {
					$('#id_table_groupe_utilisateur').append(contenu);
					document.getElementById('id_service_ajouter').value='';
				}
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		
}


function supprimer_uf(unit_num,department_num) {
	if (confirm (get_translation('ETES_VOUS_SUR_DE_VOULOIR_SUPPRIMER_CET_UF','Etes vous sûr de vouloir supprimer cette uf') + ' ?')) {
		jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:true,
		data: { action:'supprimer_uf',unit_num:unit_num,department_num:department_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("supprimer_uf("+unit_num+","+department_num+")");
			} else {
				if (contenu=='erreur') {
					alert(get_translation('JS_UNE_ERREUR_EST_SURVENUE','Une erreur est survenue'));
				} else {
					$("tr#id_tr_uf_"+department_num+"_"+unit_num).remove();
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

function modifier_passwd () {
	mon_password1=document.getElementById('id_mon_password1').value;
	mon_password2=document.getElementById('id_mon_password2').value;
	if (mon_password1!='' && mon_password2!='' && mon_password1==mon_password2) {
		 jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:false,
			data: { action:'modifier_passwd',mon_password1:mon_password1,mon_password2:mon_password2},
			beforeSend: function(requester){
			},
			success: function(requester){
				contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("modifier_passwd()");
				} else {
					if (contenu=='erreur') {
						alert(get_translation('JS_UNE_ERREUR_EST_SURVENUE','Une erreur est survenue'));
					} else {
						document.getElementById('id_modifmdp_result').innerHTML="<strong style='color:green'>"+get_translation('MOT_DE_PASSE_MODIFIE_AVEC_SUCCÈS','Mot de passe modifié avec succès')+"</strong>";
						document.getElementById('id_mon_password1').value='';
						document.getElementById('id_mon_password2').value='';
						deplier('id_span_passwd_1','inline');
						plier('id_span_passwd_2');
						
					}
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	} else {
		document.getElementById('id_modifmdp_result').innerHTML="<strong style='color:red'>"+get_translation('LES_MOTS_DE_PASSE_DOIVENT_ETRE_IDENTIQUES','les mots de passe doivent être identiques')+"</strong><br>";
		document.getElementById('id_mon_password1').value='';
		document.getElementById('id_mon_password2').value='';
		deplier('id_span_passwd_1','inline');
		plier('id_span_passwd_2');
	}
}
function modifier_user_phone_number () {
	user_phone_number=document.getElementById('id_input_user_phone_number').value;
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
					document.getElementById('id_span_user_phone_number').innerHTML=user_phone_number;
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
	mail=document.getElementById('id_input_user_mail').value;
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
					document.getElementById('id_span_user_mail').innerHTML=mail;
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
	document.getElementById('id_div_formulaire_document').innerHTML='';
}

function sauver_requete_en_cours() {
	query_num=document.getElementById('query_num_sauver').value;
	titre_requete_sauver=document.getElementById('id_titre_requete_sauver').value;
	datamart_num=document.getElementById('id_num_datamart').value;
	tmpresult_num=document.getElementById('id_num_temp').value;
	if (document.getElementById('id_crontab_requete').checked==true) {
		crontab_query=1;
	} else {
		crontab_query=0;
	}
	crontab_periode=document.getElementById('id_crontab_periode').value;
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
						document.getElementById('id_titre_requete_sauver').value='';
						document.getElementById('id_liste_requete_sauve').innerHTML="<h1>"+get_translation('SAVED_QUERIES','Requêtes sauvées')+"</h1>"+contenu;
						
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
					document.getElementById('id_div_formulaire_document').innerHTML=contenu;
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
	text=document.getElementById('id_input_filtre_texte_'+num_filtre).value;
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
				document.getElementById('id_input_filtre_texte_'+num_filtre).value=result;
	    			$('.autosizejs').autosize();   
				calcul_nb_resultat_filtre(num_filtre,true);
			}
		},
		error: function(){}
	});
}



var popupdoc=new Array();
function ouvrir_document_print (document_num) {
	
	if (document.getElementById('id_num_temp')) {
		tmpresult_num=document.getElementById('id_num_temp').value;
	}
	if (document.getElementById('id_full_text_query')) {
		full_text_query=document.getElementById('id_full_text_query').value;
	}
	if (document.getElementById('id_num_datamart')) {
		datamart_num=document.getElementById('id_num_datamart').value;
	}
	filtre_resultat_texte='';
	full_text_query='';
	if (document.getElementById('id_filtre_resultat_texte')) {
		filtre_resultat_texte=document.getElementById('id_filtre_resultat_texte').value;
	}
	if (filtre_resultat_texte!='') {
		full_text_query=full_text_query+';requete_unitaire;'+filtre_resultat_texte;
	}
	
	
	if (document.getElementById('id_input_filtre_patient_texte')) {
		full_text_query=document.getElementById('id_input_filtre_patient_texte').value;
		full_text_query=full_text_query.replace(/\+/g,';plus;');
	}

	popupdoc[document_num] = window.open('afficher_document.php?document_num='+document_num+'&option=print&requete='+escape(full_text_query),'doc'+document_num,'height=400,width=570,scrollbars=yes,resizable=yes');
	popupdoc[document_num].focus();
}


var popupdoc=new Array();
function ouvrir_liste_document_print (patient_num) {
	
	if (document.getElementById('id_full_text_query')) {
		full_text_query=document.getElementById('id_full_text_query').value;
		filtre_resultat_texte='';
		full_text_query='';
		if (document.getElementById('id_filtre_resultat_texte')) {
			filtre_resultat_texte=document.getElementById('id_filtre_resultat_texte').value;
		}
		if (filtre_resultat_texte!='') {
			full_text_query=full_text_query+';requete_unitaire;'+filtre_resultat_texte;
		}
	}
	if (document.getElementById('id_input_filtre_patient_texte')) {
		full_text_query=document.getElementById('id_input_filtre_patient_texte').value;
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
	notification_text=document.getElementById('id_notification_text').value;
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
				
				document.getElementById('id_alerte_demande_acces').innerHTML=nb_total;
				if (nb_total>0) {
					document.getElementById('id_alerte_demande_acces').style.display='inline';
				} else {
					document.getElementById('id_alerte_demande_acces').style.display='none';
				}
				
				if (document.getElementById('id_alerte_demande_acces_mes_demandes')) {
					document.getElementById('id_alerte_demande_acces_mes_demandes').innerHTML=nb_mes_demandes;
					if (nb_mes_demandes>0) {
						document.getElementById('id_alerte_demande_acces_mes_demandes').style.display='inline';
					} else {
						document.getElementById('id_alerte_demande_acces_mes_demandes').style.display='none';
					}
				}
				if (document.getElementById('id_alerte_demande_acces_a_traiter')) {
					document.getElementById('id_alerte_demande_acces_a_traiter').innerHTML=nb_a_traiter;
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
					
					document.getElementById('id_alerte_notification').innerHTML=nb_notification;
					if (nb_notification>0) {
						document.getElementById('id_alerte_notification').style.display='inline';
					} else {
						document.getElementById('id_alerte_notification').style.display='none';
					}
					
					setTimeout("crontab_alerte_notification();",2000);
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
		document.getElementById('id_div_contenu_notification').innerHTML="<img src=images/chargement_mac.gif>";
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
				document.getElementById('id_div_contenu_notification').innerHTML=result;
			} 
		}
	},
	complete: function(requester){
	},
	error: function(){
	}
	});
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
				document.getElementById('id_td_notification_'+notification_num).style.color='grey';
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
	num_user_message_prive=document.getElementById('id_select_num_user_message_prive').value;
	message_prive=document.getElementById('id_textarea_message_prive').value;
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
						document.getElementById('id_textarea_message_prive').value='';
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
	document.getElementById('id_select_num_user_message_prive').value=num_user_destinataire;
	document.getElementById('id_textarea_message_prive').focus();
	$('.chosen-select').trigger('chosen:updated');
}