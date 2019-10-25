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

	function valider_formulaire_ajouter_ecrf () {
		if (jQuery('#id_ajouter_titre_ecrf').val()=='') {
			alert (get_translation('JS_MANDATORY_TITLE','le titre est obligatoire'));
			return false;
		}
		document.getElementById('id_form_ajouter_ecrf').submit();
	}

	function ajouter_user_ecrf() {
		user_num_ecrf=$('#id_ajouter_select_user').val();
		ecrf_num=$('#id_ecrf_num_voir').val();
		
		jQuery.ajax({
			type:"POST",
			url:"ajax_ecrf.php",
			async:true,
			data: { action:'ajouter_user_ecrf',ecrf_num:ecrf_num,user_num_ecrf:user_num_ecrf},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion();
				} else {
					jQuery('#id_tableau_liste_user_ecrf').html(requester);
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
	function ajouter_droit_ecrf(ecrf_num,user_num_ecrf,right) {
	
		if (document.getElementById('id_droit_'+user_num_ecrf+'_'+right).checked) {
			option='ajouter';
		} else {
			option='supprimer';
		}
	
		jQuery.ajax({
			type:"POST",
			url:"ajax_ecrf.php",
			async:true,
			data: { action:'ajouter_droit_ecrf',ecrf_num:ecrf_num,user_num_ecrf:user_num_ecrf,right:right,option:option},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion();
				} else {
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
	
	function supprimer_user_ecrf(user_num_ecrf,ecrf_num) {
		if (confirm ('Etes vous sûr de vouloir supprimer cet utilisateur ? ')) {
			jQuery.ajax({
				type:"POST",
				url:"ajax_ecrf.php",
				async:true,
				data: { action:'supprimer_user_ecrf',ecrf_num:ecrf_num,user_num_ecrf:user_num_ecrf},
				beforeSend: function(requester){
				},
				success: function(requester){
					if (requester=='deconnexion') {
						afficher_connexion();
					} else {
						jQuery('#id_tableau_liste_user_ecrf').html(requester);
					}
				},
				complete: function(requester){
				},
				error: function(){
				}
			});
		}
	}
	function supprimer_ecrf(ecrf_num) {
		if (confirm ('Etes vous sûr de vouloir supprimer cet ecrf ? ')) {
			jQuery.ajax({
				type:"POST",
				url:"ajax_ecrf.php",
				async:true,
				data: { action:'supprimer_ecrf',ecrf_num:ecrf_num},
				beforeSend: function(requester){
				},
				success: function(requester){
					if (requester=='deconnexion') {
						afficher_connexion();
					} else {
						jQuery('#id_div_liste_ecrf').html(requester);
						jQuery('#id_div_mon_ecrf').html("");
						jQuery('#id_tableau_liste_ecrf').dataTable( { 
							paging: false,
							"order": [[ 1, "asc" ]],
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
	}
	function modifier_titre_ecrf(ecrf_num) {
		title_ecrf=jQuery('#id_input_titre_ecrf').val();
		jQuery.ajax({
			type:"POST",
			url:"ajax_ecrf.php",
			async:true,
			data: { action:'modifier_titre_ecrf',ecrf_num:ecrf_num,title_ecrf:escape(title_ecrf)},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion();
				} else {
					jQuery('#id_div_liste_ecrf').html(requester);
				 	jQuery('#id_tableau_liste_ecrf').dataTable( { 
							paging: false,
							"order": [[ 1, "asc" ]],
							   "bInfo" : false,
							   "bDestroy" : true
					} );
					jQuery('#id_sous_span_titre_ecrf').html(title_ecrf);
					plier('id_titre_ecrf_modifier');
					deplier('id_titre_ecrf','inline');
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
	
	function modifier_description_ecrf(ecrf_num) {
		description_ecrf=jQuery("#id_textarea_description_ecrf_modifier").val();
		jQuery.ajax({
			type:"POST",
			url:"ajax_ecrf.php",
			async:true,
			data: { action:'modifier_description_ecrf',ecrf_num:ecrf_num,description_ecrf:escape(description_ecrf)},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion();
				} else {
					jQuery('#id_div_ecrf_description').html(requester);
					plier('id_div_ecrf_description_modifier');
					deplier('id_div_ecrf_description','inline');
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
	
	
	function modifier_token_ecrf(ecrf_num) {
		token_ecrf=jQuery("#id_input_token_ecrf_modifier").val();
		jQuery.ajax({
			type:"POST",
			url:"ajax_ecrf.php",
			async:true,
			data: { action:'modifier_token_ecrf',ecrf_num:ecrf_num,token_ecrf:token_ecrf},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion();
				} else {
					if (requester=='') {
						requester=get_translation('MODIFY_API_TOKEN',"Modifier l'API TOKEN en cliquant ici");
					}
					jQuery('#id_div_token_ecrf').html(requester);
					plier('id_div_token_ecrf_modifier');
					deplier('id_div_token_ecrf','inline');
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
	
	
	function modifier_url_ecrf(ecrf_num) {
		url_ecrf=jQuery("#id_input_url_ecrf_modifier").val();
		jQuery.ajax({
			type:"POST",
			url:"ajax_ecrf.php",
			async:true,
			data: { action:'modifier_url_ecrf',ecrf_num:ecrf_num,url_ecrf:url_ecrf},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion();
				} else {
					if (requester=='') {
						requester=get_translation('MODIFY_URL_ECRF',"Modifier l'URL de l'ECRF");
					}
					jQuery('#id_div_url_ecrf').html(requester);
					plier('id_div_url_ecrf_modifier');
					deplier('id_div_url_ecrf','inline');
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
	
	function modifier_ecrf_period(ecrf_num) {
		ecrf_start_date=jQuery('#id_ecrf_start_date').val();
		ecrf_end_date=jQuery('#id_ecrf_end_date').val();
		ecrf_start_age=jQuery('#id_ecrf_start_age').val();
		ecrf_end_age=jQuery('#id_ecrf_end_age').val();
		
		jQuery.ajax({
			type:"POST",
			url:"ajax_ecrf.php",
			async:true,
			data: { action:'modifier_ecrf_period',ecrf_num:ecrf_num,ecrf_start_date:ecrf_start_date,ecrf_end_date:ecrf_end_date,ecrf_start_age:ecrf_start_age,ecrf_end_age:ecrf_end_age},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion("modifier_ecrf_period("+ecrf_num+")");
				} 
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
			
	function voir_ecrf_onglet (onglet) {
		$(".div_result").css("display","none");
		$(".color-bullet").removeClass("current");
		
		jQuery('#id_div_ecrf_'+onglet).css('display','inline');
		$("#id_bouton_"+onglet).addClass("current");
	}
	
	function importer_item_ecrf (ecrf_num) {
		list_item=jQuery("#id_textarea_importer_item").val();
		jQuery.ajax({
			type:"POST",
			url:"ajax_ecrf.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'importer_item_ecrf',ecrf_num:ecrf_num,list_item:escape(list_item)},
			beforeSend: function(requester){
					jQuery("#id_list_item").html("<img src='images/chargement_mac.gif'>"); 
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("importer_item_ecrf ('"+ecrf_num+"')");
				} else {
					jQuery("#id_list_item").html(contenu); 
				}
			}
		});
	}
	
	function get_list_ecrf_sub_item_num (ecrf_num,ecrf_item_num) {
		list_ecrf_sub_item_num='';
		jQuery.ajax({
			type:"POST",
			url:"ajax_ecrf.php",
			async:true, //false
			encoding: 'latin1',
			data:{ action:'get_list_ecrf_sub_item_num',ecrf_num:ecrf_num,ecrf_item_num:ecrf_item_num},
			beforeSend: function(requester){
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("get_list_ecrf_sub_item_num ('"+ecrf_num+"','"+ecrf_item_num+"')");
				} else {
					list_ecrf_sub_item_num=contenu;
					
					tab_sub_item_num=list_ecrf_sub_item_num.split(';');
					tab_sub_item_num.forEach(function(ecrf_sub_item_num)  { 
						if (ecrf_sub_item_num!='') {
							if (jQuery("#id_input_sub_item_local_str_"+ecrf_sub_item_num).val()=='') {
								delete_ecrf_sub_item(ecrf_num,ecrf_item_num,ecrf_sub_item_num);
							} else {
								save_ecrf_sub_item(ecrf_num,ecrf_item_num,ecrf_sub_item_num,'sub_item_local_str');
								save_ecrf_sub_item(ecrf_num,ecrf_item_num,ecrf_sub_item_num,'sub_item_local_code');
								save_ecrf_sub_item(ecrf_num,ecrf_item_num,ecrf_sub_item_num,'sub_item_ext_code');
								save_ecrf_sub_item(ecrf_num,ecrf_item_num,ecrf_sub_item_num,'sub_item_regexp');
							}
						}
					}
					);
				}
			}
		});
		return list_ecrf_sub_item_num;
	}
	var ecrf_item_num_open='';
	function modify_ecrf_item(ecrf_num,ecrf_item_num) {
		if (ecrf_item_num_open!=ecrf_item_num && ecrf_item_num_open!='') {
			save_ecrf_item_all(ecrf_num,ecrf_item_num_open);
		}
		ecrf_item_num_open=ecrf_item_num;
		$(".id_span_ecrf_item_"+ecrf_item_num).css("display","none");
		$(".id_span_modif_ecrf_item_"+ecrf_item_num).css("display","block");
		adapt_ecrf_form (ecrf_item_num);
	}
	
//	var flag_click_ecrf=0;
//	function modify_ecrf_item_old(ecrf_item_num,variable) {
//		flag_click_ecrf=1;
//		$("#id_span_"+variable+"_"+ecrf_item_num).css("display","none");
//		$("#id_span_modif_"+variable+"_"+ecrf_item_num).css("display","block");
//		$("#id_input_"+variable+"_"+ecrf_item_num).focus();
//		setTimeout("flag_click_ecrf=0",1000);
//	}
	
	function save_ecrf_item(ecrf_num,ecrf_item_num,variable) {
		valeur=jQuery("#id_input_"+variable+"_"+ecrf_item_num).val();
		if (!valeur) {
			valeur='';
		}
		//valeur=jQuery("#id_input_"+variable+"_"+ecrf_item_num).val(); // pour recuperer les donnees de type select ...
		valeur_clean=valeur.replace(/\+/g,';plus;');
		//valeur_clean=valeur_clean.replace(/\\/g,';antislash;');
		jQuery.ajax({
			type:"POST",
			url:"ajax_ecrf.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'save_ecrf_item',ecrf_num:ecrf_num,ecrf_item_num:ecrf_item_num,variable:variable,valeur:escape(valeur_clean)},
			beforeSend: function(requester){
//				next_variable='';
//				if (variable=='item_str') {
//					next_variable='item_type';					
//				}
//				if (variable=='item_type') {
//					next_variable='item_list';					
//				}
//				if (variable=='item_list') {
//					next_variable='regexp';					
//				}
//				if (variable=='regexp') {
//					next_variable='regexp_index';					
//				}
//				if (variable=='regexp_index') {
//					next_variable='item_local_code';					
//				}
//				if (variable=='regexp_index') {
//					next_variable='item_local_code';					
//				}
//				if (variable=='item_local_code') {
//					next_variable='period';					
//				}
//				if (next_variable!='') {
//					if (flag_click_ecrf==0) {
//						modify_ecrf_item(ecrf_item_num,next_variable);
//					} else {
//						flag_click_ecrf=0;
//					}	
//				}
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("save_ecrf_item ('"+ecrf_num+"','"+ecrf_item_num+"','"+variable+"')");
				} else {
					jQuery("#id_span_"+variable+"_"+ecrf_item_num).html(jQuery("#id_input_"+variable+"_"+ecrf_item_num).val());
					//$("#id_span_"+variable+"_"+ecrf_item_num).css("display","block");
					//$("#id_span_modif_"+variable+"_"+ecrf_item_num).css("display","none");
				}
			}
		});
	}
	
	function save_ecrf_sub_item (ecrf_num,ecrf_item_num,ecrf_sub_item_num,variable) {
		valeur=jQuery("#id_input_"+variable+"_"+ecrf_sub_item_num).val();
		if (!valeur) {
			valeur='';
		}
		valeur_clean=valeur.replace(/\+/g,';plus;');
		//valeur_clean=valeur_clean.replace(/\\/g,';antislash;');
		jQuery.ajax({
			type:"POST",
			url:"ajax_ecrf.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'save_ecrf_sub_item',ecrf_num:ecrf_num,ecrf_item_num:ecrf_item_num,ecrf_sub_item_num:ecrf_sub_item_num,variable:variable,valeur:escape(valeur_clean)},
			beforeSend: function(requester){

			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("save_ecrf_item ('"+ecrf_num+"','"+ecrf_item_num+"','"+ecrf_sub_item_num+"','"+variable+"')");
				} else {
					refresh_ecrf_sub_item(ecrf_num,ecrf_item_num,variable,'see');
					refresh_ecrf_sub_item(ecrf_num,ecrf_item_num,variable,'input');
					//jQuery("#id_span_"+variable+"_"+ecrf_item_num).html(jQuery("#id_input_"+variable+"_"+ecrf_item_num).val());
				}
			}
		});
	}
	
	function adapt_ecrf_form (ecrf_item_num) {
		valeur=jQuery("#id_input_item_type_"+ecrf_item_num).val();
		if (!valeur) {
			valeur='';
		}
		if (valeur=='list' || valeur=='radio') {
			$("#id_span_modif_item_list_"+ecrf_item_num).css("display","none");
			$("#id_span_modif_regexp_"+ecrf_item_num).css("display","none");
			$("#id_span_modif_regexp_index_"+ecrf_item_num).css("display","none");
			$("#id_span_modif_item_local_code_"+ecrf_item_num).css("display","none");
			$("#id_span_modif_item_ext_code_"+ecrf_item_num).css("display","none");
			
			$("#id_span_modif_sub_item_local_str_"+ecrf_item_num).css("display","block");
			$("#id_span_modif_sub_item_regexp_"+ecrf_item_num).css("display","block");
			$("#id_span_modif_sub_item_local_code_"+ecrf_item_num).css("display","block");
			$("#id_span_modif_sub_item_ext_code_"+ecrf_item_num).css("display","block");
		} else {
		
			$("#id_span_modif_item_list_"+ecrf_item_num).css("display","none");
			$("#id_span_modif_regexp_"+ecrf_item_num).css("display","block");
			$("#id_span_modif_regexp_index_"+ecrf_item_num).css("display","block");
			$("#id_span_modif_item_local_code_"+ecrf_item_num).css("display","block");
			$("#id_span_modif_item_ext_code_"+ecrf_item_num).css("display","block");
			
			
			$("#id_span_modif_sub_item_local_str_"+ecrf_item_num).css("display","none");
			$("#id_span_modif_sub_item_regexp_"+ecrf_item_num).css("display","none");
			$("#id_span_modif_sub_item_local_code_"+ecrf_item_num).css("display","none");
			$("#id_span_modif_sub_item_ext_code_"+ecrf_item_num).css("display","none");
		}
		
	}
	function save_ecrf_item_all(ecrf_num,ecrf_item_num) {
		$(".id_span_ecrf_item_"+ecrf_item_num).css("display","block");
		$(".id_span_modif_ecrf_item_"+ecrf_item_num).css("display","none");		
		
		//$("#id_tr_ecrf_item_"+ecrf_item_num).css("background-color","grey");
		//$("#id_tr_ecrf_item_"+ecrf_item_num).css('opacity', '0.6');
		
		save_ecrf_item(ecrf_num,ecrf_item_num,'item_str');
		save_ecrf_item(ecrf_num,ecrf_item_num,'item_type');
		save_ecrf_item(ecrf_num,ecrf_item_num,'item_list');
		save_ecrf_item(ecrf_num,ecrf_item_num,'regexp');
		save_ecrf_item(ecrf_num,ecrf_item_num,'regexp_index');
		save_ecrf_item(ecrf_num,ecrf_item_num,'item_ext_name');
		save_ecrf_item(ecrf_num,ecrf_item_num,'item_ext_code');
		save_ecrf_item(ecrf_num,ecrf_item_num,'item_local_code');
		save_ecrf_item(ecrf_num,ecrf_item_num,'period');
		save_ecrf_item(ecrf_num,ecrf_item_num,'item_order');
		
		list_ecrf_sub_item_num=get_list_ecrf_sub_item_num (ecrf_num,ecrf_item_num);
		
//		tab_sub_item_num=list_ecrf_sub_item_num.split(';');
//		tab_sub_item_num.forEach(function(ecrf_sub_item_num)  { 
//			if (ecrf_sub_item_num!='') {
//				if (jQuery("#id_input_sub_item_local_str_"+ecrf_sub_item_num).val()=='') {
//					delete_ecrf_sub_item(ecrf_num,ecrf_item_num,ecrf_sub_item_num);
//				} else {
//					save_ecrf_sub_item(ecrf_num,ecrf_item_num,ecrf_sub_item_num,'sub_item_local_str');
//					save_ecrf_sub_item(ecrf_num,ecrf_item_num,ecrf_sub_item_num,'sub_item_local_code');
//					save_ecrf_sub_item(ecrf_num,ecrf_item_num,ecrf_sub_item_num,'sub_item_ext_code');
//					save_ecrf_sub_item(ecrf_num,ecrf_item_num,ecrf_sub_item_num,'sub_item_regexp');
//				}
//			}
//		}
//		);
	}
	
	function delete_item_ecrf(ecrf_num,ecrf_item_num) {
		if (confirm ('Etes vous sûr de vouloir supprimer cet item ? ')) {
			jQuery.ajax({
				type:"POST",
				url:"ajax_ecrf.php",
				async:true,
				encoding: 'latin1',
				data:{ action:'delete_item_ecrf',ecrf_num:ecrf_num,ecrf_item_num:ecrf_item_num},
				beforeSend: function(requester){
				},
				success: function(requester){
					var contenu=requester;
					if (contenu=='deconnexion') {
						afficher_connexion("delete_item_ecrf ('"+ecrf_num+"','"+ecrf_item_num+"')");
					} else {
						jQuery("#id_list_item").html(contenu); 
					}
				}
			});
		}
	}
	
	function delete_patient_ecrf(ecrf_num,patient_num) {
		if (confirm ('Etes vous sûr de vouloir supprimer ce patient ? ')) {
			jQuery.ajax({
				type:"POST",
				url:"ajax_ecrf.php",
				async:true,
				encoding: 'latin1',
				data:{ action:'delete_patient_ecrf',ecrf_num:ecrf_num,patient_num:patient_num},
				beforeSend: function(requester){
				},
				success: function(requester){
					var contenu=requester;
					if (contenu=='deconnexion') {
						afficher_connexion("delete_patient_ecrf ('"+ecrf_num+"','"+patient_num+"')");
					} else {
						$("tr#id_tr_ecrf_patient_"+ecrf_num+"_"+patient_num).remove();
					}
				}
			});
		}
	}
	
	function delete_ecrf_sub_item(ecrf_num,ecrf_item_num,ecrf_sub_item_num) {
		jQuery.ajax({
			type:"POST",
			url:"ajax_ecrf.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'delete_ecrf_sub_item',ecrf_num:ecrf_num,ecrf_item_num:ecrf_item_num,ecrf_sub_item_num:ecrf_sub_item_num},
			beforeSend: function(requester){
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("delete_ecrf_sub_item ('"+ecrf_num+"','"+ecrf_item_num+"','"+ecrf_sub_item_num+"')");
				} else {
					refresh_ecrf_sub_item (ecrf_num,ecrf_item_num,'sub_item_local_str','see');
					refresh_ecrf_sub_item (ecrf_num,ecrf_item_num,'sub_item_local_code','see');
					refresh_ecrf_sub_item (ecrf_num,ecrf_item_num,'sub_item_ext_code','see');
					refresh_ecrf_sub_item (ecrf_num,ecrf_item_num,'sub_item_regexp','see');
					
					refresh_ecrf_sub_item (ecrf_num,ecrf_item_num,'sub_item_local_str','input');
					refresh_ecrf_sub_item (ecrf_num,ecrf_item_num,'sub_item_local_code','input');
					refresh_ecrf_sub_item (ecrf_num,ecrf_item_num,'sub_item_ext_code','input');
					refresh_ecrf_sub_item (ecrf_num,ecrf_item_num,'sub_item_regexp','input');
				}
			}
		});
	}
	
	function refresh_ecrf_sub_item(ecrf_num,ecrf_item_num,variable,display) {
		jQuery.ajax({
			type:"POST",
			url:"ajax_ecrf.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'refresh_ecrf_sub_item',ecrf_num:ecrf_num,ecrf_item_num:ecrf_item_num,variable:variable,display:display},
			beforeSend: function(requester){
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("refresh_ecrf_sub_item ('"+ecrf_num+"','"+ecrf_item_num+"','"+variable+"','"+display+"')");
				} else {
					if (display=='input') {
						jQuery("#id_div_modif_"+variable+"_"+ecrf_item_num).html(contenu); 
					} else {
						jQuery("#id_span_"+variable+"_"+ecrf_item_num).html(contenu); 
					}
				}
			}
		});
	}
	
	function add_new_item (ecrf_num) {
		var ecrf_item_num='';
		jQuery.ajax({
			type:"POST",
			url:"ajax_ecrf.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'add_new_item',ecrf_num:ecrf_num},
			beforeSend: function(requester){
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("add_new_item ('"+ecrf_num+"')");
				} else {
					ecrf_item_num=contenu;
					list_item (ecrf_num);
					modify_ecrf_item(ecrf_num,ecrf_item_num);
				}
			}
		});
	}
	
	
	function add_sub_item (ecrf_num,ecrf_item_num) {
		jQuery.ajax({
			type:"POST",
			url:"ajax_ecrf.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'add_sub_item',ecrf_num:ecrf_num,ecrf_item_num:ecrf_item_num},
			beforeSend: function(requester){
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("add_sub_item ('"+ecrf_num+"','"+ecrf_item_num+"')");
				} else {
					ecrf_sub_item_num=contenu;
					jQuery('#id_div_modif_sub_item_local_str_'+ecrf_item_num).append('<input type="text" size="30" class="form" value="" id="id_input_sub_item_local_str_'+ecrf_sub_item_num+'"><br>');
					jQuery('#id_div_modif_sub_item_local_code_'+ecrf_item_num).append('<input type="text" size="30" class="form" value="" id="id_input_sub_item_local_code_'+ecrf_sub_item_num+'"><br>');
					jQuery('#id_div_modif_sub_item_ext_code_'+ecrf_item_num).append('<input type="text" size="30" class="form" value="" id="id_input_sub_item_ext_code_'+ecrf_sub_item_num+'"><br>');
					jQuery('#id_div_modif_sub_item_regexp_'+ecrf_item_num).append('<input type="text" size="30" class="form" value="" id="id_input_sub_item_regexp_'+ecrf_sub_item_num+'"><br>');
					
					//refresh_ecrf_sub_item (ecrf_num,ecrf_item_num,'sub_item_local_str','input');
					//refresh_ecrf_sub_item (ecrf_num,ecrf_item_num,'sub_item_local_code','input');
					//refresh_ecrf_sub_item (ecrf_num,ecrf_item_num,'sub_item_ext_code','input');
					//refresh_ecrf_sub_item (ecrf_num,ecrf_item_num,'sub_item_regexp','input');
					
					jQuery('#id_input_sub_item_local_str_'+ecrf_sub_item_num).focus();
				}
			}
		});
	}
	
	function list_item (ecrf_num) {
		jQuery.ajax({
			type:"POST",
			url:"ajax_ecrf.php",
			async:true,//false
			encoding: 'latin1',
			data:{ action:'display_ecrf_item',ecrf_num:ecrf_num},
			beforeSend: function(requester){
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("list_item ('"+ecrf_num+"')");
				} else {
					jQuery("#id_list_item").html(contenu); 
				}
			}
		});
	}
	
	function list_patient_ecrf (ecrf_num) {
		jQuery.ajax({
			type:"POST",
			url:"ajax_ecrf.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'list_patient_ecrf',ecrf_num:ecrf_num},
			beforeSend: function(requester){
					jQuery("#id_liste_patient_ecrf").html("<img src='images/chargement_mac.gif'>"); 
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("list_patient_ecrf ('"+ecrf_num+"')");
				} else {
					jQuery("#id_liste_patient_ecrf").html(contenu); 
				}
			}
		});
	}