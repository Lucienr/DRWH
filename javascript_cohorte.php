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

	function valider_formulaire_ajouter_cohorte () {
		if (document.getElementById('id_ajouter_titre_cohorte').value=='') {
			alert ("  <? print get_translation('JS_MANDATORY_TITLE','le titre est obligatoire'); ?> ");
			return false;
		}
		document.getElementById('id_form_ajouter_cohorte').submit();
	}

	function valider_formulaire_fusionner_cohorte () {
		if (document.getElementById('id_fusionner_titre_cohorte').value=='') {
			alert ("  <? print get_translation('JS_MANDATORY_TITLE','le titre est obligatoire'); ?> ");
			return false;
		}
		if (document.getElementById('id_fusionner_select_cohorte1').value=='') {
			alert (" <? print get_translation('JS_MUST_CHOOSE_BETWEEN_2_COHORTS','il faut choisir 2 cohortes'); ?> ");
			return false;
		}
		if (document.getElementById('id_fusionner_select_cohorte2').value=='') {
			alert (" <? print get_translation('JS_MUST_CHOOSE_BETWEEN_2_COHORTS','il faut choisir 2 cohortes'); ?> ");
			return false;
		}
		document.getElementById('id_form_fusionner_cohorte').submit();
	}

	function select_cohorte(cohort_num_encours) {
		if (cohort_num_encours!='') {
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:true,
				data: { action:'select_cohorte',cohort_num_encours:cohort_num_encours,datamart_num:<? print $datamart_num; ?>},
				beforeSend: function(requester){
				},
				success: function(requester){
					if (requester=='deconnexion') {
						afficher_connexion();
					} else {
						tableau=requester.split(';');
						title=tableau[0];
						nb_patient=tableau[1];
						document.getElementById('id_titre_cohorte_encours').innerHTML="<? print get_translation('JS_COHORT','Cohorte'); ?> : "+title;
						document.getElementById('id_libelle_cohorte_encours').innerHTML="<? print get_translation('JS_OPENED_COHORT','Cohorte ouverte');?> : "+title;
						document.getElementById('id_cohort_num_encours').value=cohort_num_encours;
						
						document.getElementById('id_bouton_cohorte_encours').style.display='block';
						
						colorer_patient_cohorte(cohort_num_encours);
						liste_patient_cohorte_encours(cohort_num_encours,1);
						liste_patient_cohorte_encours(cohort_num_encours,2);
						liste_patient_cohorte_encours(cohort_num_encours,0);
						liste_patient_cohorte_encours(cohort_num_encours,3);
						afficher_cohorte_nb_patient_statut(cohort_num_encours);
						$(".icone_cohorte").css("display","inline");
					}
				},
				complete: function(requester){
				},
				error: function(){
				}
			});
		} else {
			//fermer_cohorte_encours();
		}
	}
	function fermer_cohorte_encours() {
		cohort_num_encours=$('#id_cohort_num_encours').val();
		document.getElementById('id_titre_cohorte_encours').innerHTML="";
		document.getElementById('id_nb_patient_cohorte_inclu').innerHTML="";
		document.getElementById('id_cohort_num_encours').value='';
		
		document.getElementById('id_bouton_cohorte_encours').style.display='none';
		colorer_patient_cohorte('');
		$(".icone_cohorte").css("display","none");
		voir_detail_dwh('resultat_detail');
		liste_patient_cohorte_encours('',1);
		liste_patient_cohorte_encours('',2);
		liste_patient_cohorte_encours('',0);
		liste_patient_cohorte_encours('',3);
		
	
	}

	function colorer_patient_cohorte(cohort_num_encours) {
		$('.img_pencil_resultat').attr('src','images/pencil.png');
		$('.pair').css('backgroundColor','#E6EEF0');
		$('.impair').css('backgroundColor','#FFFFFF');
		if (cohort_num_encours!='') {
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:true,
				data: { action:'colorer_patient_cohorte',cohort_num_encours:cohort_num_encours,datamart_num:<? print $datamart_num; ?>},
				beforeSend: function(requester){
				},
				success: function(requester){
					if (requester=='deconnexion') {
						afficher_connexion();
					} else {
						tableau=requester.split(';');
						eval(requester);
					}
				},
				complete: function(requester){
				},
				error: function(){
				}
			});
		} 
	}
	function liste_patient_cohorte_encours(cohort_num_encours,status) {
		if (document.getElementById('id_liste_patient_cohorte_encours'+status)) {
			if (cohort_num_encours!='') {
				jQuery.ajax({
					type:"POST",
					url:"ajax.php",
					async:true,
					data: { action:'liste_patient_cohorte_encours',cohort_num_encours:cohort_num_encours,status:status,datamart_num:<? print $datamart_num; ?>},
					beforeSend: function(requester){
						document.getElementById('id_liste_patient_cohorte_encours'+status).innerHTML="<img src='images/chargement_mac.gif'>";
					},
					success: function(requester){
						if (requester=='deconnexion') {
							afficher_connexion();
						} else {
							document.getElementById('id_liste_patient_cohorte_encours'+status).innerHTML=requester;
							$(".icone_cohorte").css("display","inline");
						}
					},
					complete: function(requester){
					},
					error: function(){
					}
				});
			} else {
				document.getElementById('id_liste_patient_cohorte_encours'+status).innerHTML='';
			}
		}
	}
	function ajouter_cohorte() {
			title_cohort=document.getElementById('id_ajouter_titre_cohorte').value;
			description_cohort=document.getElementById('id_ajouter_description_cohort').value;
			$("#id_ajouter_select_user_multiple").each(function() {
			    liste_user_cohorte=$(this).val();
			});
			
			liste_droit='';
			$(".ajouter_user_profile_datamart:checked").each(function() {
			    liste_droit+=$(this).val()+",";
			});
			if (title_cohort!='') {
				plier('id_div_liste_creer_cohorte');
				jQuery.ajax({
					type:"POST",
					url:"ajax.php",
					async:true,
					data: { action:'ajouter_cohorte',liste_user_cohorte:liste_user_cohorte,liste_droit:liste_droit,title_cohort:escape(title_cohort),description_cohort:escape(description_cohort),datamart_num:<? print $datamart_num; ?>},
					beforeSend: function(requester){
					},
					success: function(requester){
						if (requester=='deconnexion') {
							afficher_connexion();
						} else {
							document.getElementById('id_ajouter_titre_cohorte').value='';
							document.getElementById('id_ajouter_description_cohort').value='';
							//rafraichir_liste_cohorte();
							rafraichir_select_cohort();
							select_cohorte(requester);
							//voir_detail_dwh('cohorte_encours');
							
						}
					},
					complete: function(requester){
					},
					error: function(){
					}
				});	
			}	
	}

	function rafraichir_select_cohort () {
	
		jQuery.ajax({
			type:"POST",
			url:"ajax_cohorte.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'rafraichir_select_cohort'},
			beforeSend: function(requester){
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("afficher_resultat_similarite ('"+process_num+"','"+cohort_num+"')");
				} else {
					$('#id_select_cohorte_pour_alimentation').empty(); //remove all child nodes
					var newOption = $("<option value='' selected> </option>");
					$('#id_select_cohorte_pour_alimentation').append(newOption);
					tableau_cohorte=contenu.split(";separateur;");
					for(var i= 0; i < tableau_cohorte.length; i++) {
						if (tableau_cohorte[i]!='') {
							tab=tableau_cohorte[i].split(";");
							cohort_num=tab[0];
							title_cohort=tab[1];
							var newOption = $("<option value='"+cohort_num+"'>"+title_cohort+"</option>");
							$('#id_select_cohorte_pour_alimentation').append(newOption);
						}
					}
					$('#id_select_cohorte_pour_alimentation').trigger("chosen:updated");
				}
			}
		});
	}
	
	function rafraichir_liste_cohorte() {
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:true,
			data: { action:'rafraichir_liste_cohorte',datamart_num:<? print $datamart_num; ?>},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion();
				} else {
					document.getElementById('id_div_select_cohorte_select').innerHTML=requester;
					$("#id_tableau_cohorte_select").dataTable( {paging: false, "order": [[ 0, "asc" ]]});
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});		
	}
	function inclure_patient_cohorte(patient_num,status) {
		cohort_num_encours=$('#id_cohort_num_encours').val();
		query_num=$('#query_num_sauver').val();
		if (cohort_num_encours!='') {
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:true,
				data: { action:'inclure_patient_cohorte',patient_num:patient_num,cohort_num_encours:cohort_num_encours,query_num:query_num,status:status,datamart_num:<? print $datamart_num; ?>},
				beforeSend: function(requester){
				},
				success: function(requester){
					if (requester=='deconnexion') {
						afficher_connexion();
					} else {
						if (status==1) {
							$("#id_tr_patient_"+patient_num).css("backgroundColor","#D0EAD1");
						}
						if (status==0) {
							$("#id_tr_patient_"+patient_num).css("backgroundColor","#E9CDCE");
						}
						if (status==2) {
							$("#id_tr_patient_"+patient_num).css("backgroundColor","#E1E1DF");
						}
						//tableau_nb=requester.split(';');
						//$('#id_nb_patient_cohorte_inclu').html(tableau_nb[0]);
						//$('#id_nb_patient_cohorte_inclu_bis').html(tableau_nb[0]);
						//$('#id_nb_patient_cohorte_exclu').html(tableau_nb[1]);
						//$('#id_nb_patient_cohorte_doute').html(tableau_nb[2]);
						//$('#id_nb_patient_cohorte_import').html(tableau_nb[3]);
						
						liste_patient_cohorte_encours(cohort_num_encours,1);
						liste_patient_cohorte_encours(cohort_num_encours,0);
						liste_patient_cohorte_encours(cohort_num_encours,2);
						liste_patient_cohorte_encours(cohort_num_encours,3);
						
						afficher_cohorte_nb_patient_statut(cohort_num_encours);
					}
				},
				complete: function(requester){
				},
				error: function(){
				}
			});
		}
	}
	
	
	function commenter_patient_cohorte(patient_num,context) {
		cohort_num=$('#id_cohort_num_encours').val();
		if (document.getElementById('id_div_lister_commentaire_patient_cohorte_'+context+'_'+patient_num).style.display=='none') {
			if (cohort_num!='') {
				
				$(".div_lister_commentaire_patient_cohorte").css("display","none");
				document.getElementById('id_div_lister_commentaire_patient_cohorte_'+context+'_'+patient_num).style.display='block';
				lister_commentaire_patient_cohorte(patient_num,cohort_num,context);
	    			$('.autosizejs').autosize();
			}
		} else {
			document.getElementById('id_div_lister_commentaire_patient_cohorte_'+context+'_'+patient_num).style.display='none';
		}
	}
	
	function lister_commentaire_patient_cohorte(patient_num,cohort_num,context) {
		jQuery("#id_div_lister_commentaire_patient_cohorte_contenu_"+context+"_"+patient_num).html("<img src='images/chargement_mac.gif'>"); 
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:true,
			data: { action:'lister_commentaire_patient_cohorte',patient_num:patient_num,cohort_num:cohort_num,context:context},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion();
				} else {
					document.getElementById('id_div_lister_commentaire_patient_cohorte_contenu_'+context+'_'+patient_num).innerHTML=requester;
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
	function sauver_commenter_patient_cohorte(patient_num,context) {
		cohort_num=$('#id_cohort_num_encours').val();
		commentary=document.getElementById('id_textarea_ajouter_commentaire_patient_cohorte_'+context+'_'+patient_num).value;
		if (cohort_num!='') {
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:true,
				data: { action:'sauver_commenter_patient_cohorte',patient_num:patient_num,cohort_num:cohort_num,commentary:escape(commentary),datamart_num:<? print $datamart_num; ?>},
				beforeSend: function(requester){
				},
				success: function(requester){
					if (requester=='deconnexion') {
						afficher_connexion();
					} else {
						document.getElementById('id_textarea_ajouter_commentaire_patient_cohorte_'+context+'_'+patient_num).value='';
						lister_commentaire_patient_cohorte(patient_num,cohort_num,context);
						liste_patient_cohorte_encours(cohort_num,3);
						$("#id_img_pencil_resultat_"+patient_num).attr("src","images/pencil_red.png");
						$("#id_img_pencil_cohorte_"+patient_num).attr("src","images/pencil_red.png");
						lister_tous_les_commentaires_patient_cohorte (cohort_num);
	    					$('.autosizejs').autosize();
					}
				},
				complete: function(requester){
				},
				error: function(){
				}
			});
		}
	}
	
	
	function supprimer_commentaire_cohorte (cohort_result_comment_num,context,patient_num,cohort_num) {
		if (cohort_result_comment_num!='') {
			if (confirm ('Etes vous sûr de vouloir supprimer ce commentary ?')) {
				jQuery.ajax({
					type:"POST",
					url:"ajax.php",
					async:true,
					data: { action:'supprimer_commentaire_cohorte',cohort_result_comment_num:cohort_result_comment_num,patient_num:patient_num,cohort_num:cohort_num},
					beforeSend: function(requester){
					},
					success: function(requester){
						if (requester=='deconnexion') {
							afficher_connexion();
						} else {
							document.getElementById('id_commentaire_cohorte_'+context+'_'+cohort_result_comment_num).innerHTML='';
							document.getElementById('id_commentaire_cohorte_'+context+'_'+cohort_result_comment_num).style.display='none';
							if (requester==0) {
								$("#id_img_pencil_resultat_"+patient_num).attr("src","images/pencil.png");
								$("#id_img_pencil_cohorte_"+patient_num).attr("src","images/pencil.png");
							}
							lister_tous_les_commentaires_patient_cohorte (cohort_num);
						}
					},
					complete: function(requester){
					},
					error: function(){
					}
				});
			}
		}
	}
	
	
	function afficher_cohorte_nb_patient_statut(cohort_num) {
		if (cohort_num!='') {
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:true,
				data: { action:'afficher_cohorte_nb_patient_statut',cohort_num:cohort_num},
				beforeSend: function(requester){
				},
				success: function(requester){
					if (requester=='deconnexion') {
						afficher_connexion();
					} else {
						tableau_nb=requester.split(';');
						$('#id_nb_patient_cohorte_inclu').html(tableau_nb[0]);
						$('#id_nb_patient_cohorte_inclu_bis').html(tableau_nb[0]);
						$('#id_nb_patient_cohorte_exclu').html(tableau_nb[1]);
						$('#id_nb_patient_cohorte_doute').html(tableau_nb[2]);
						$('#id_nb_patient_cohorte_import').html(tableau_nb[3]);
					}
				},
				complete: function(requester){
				},
				error: function(){
				}
			});
		}
	}
	function importer_patient_cohorte (cohort_num,option) {
		liste_hospital_patient_id=document.getElementById("id_textarea_importer_patient").value;
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'importer_patient_cohorte',cohort_num:cohort_num,liste_hospital_patient_id:escape(liste_hospital_patient_id),option:option},
			beforeSend: function(requester){
					jQuery("#id_journal_import_patient").html("<img src='images/chargement_mac.gif'>"); 
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("importer_patient_cohorte ('"+cohort_num+"')");
				} else {
					jQuery("#id_journal_import_patient").html(contenu); 
					liste_patient_cohorte_encours(cohort_num,3);
					liste_patient_cohorte_encours(cohort_num,1);
					liste_patient_cohorte_encours(cohort_num,2);
					liste_patient_cohorte_encours(cohort_num,0);
					afficher_cohorte_nb_patient_statut(cohort_num);
				}
			}
		});
	}
	
	
	function lister_tous_les_commentaires_patient_cohorte (cohort_num) {
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:true,
			data: { action:'lister_tous_les_commentaires_patient_cohorte',cohort_num:cohort_num},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion();
				} else {
					if (document.getElementById('id_lister_tous_les_commentaires_patient_cohorte')) {
						document.getElementById('id_lister_tous_les_commentaires_patient_cohorte').innerHTML=requester;
					}
					afficher_cohorte_nb_patient_statut(cohort_num);
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
	function nb_tous_les_commentaires_patient_cohorte (cohort_num) {
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:true,
			data: { action:'nb_tous_les_commentaires_patient_cohorte',cohort_num:cohort_num},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion();
				} else {
					document.getElementById('id_nb_patient_cohorte_import').innerHTML=requester;
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
	
	function tout_inclure_exclure(status,phrase) {
		if (confirm ('Etes vous sûr de vouloir mettre tous ces patients dans les '+phrase+' ? ')) {
			query_num=$('#query_num_sauver').val();
			cohort_num_encours=$('#id_cohort_num_encours').val();
			tmpresult_num=$('#id_num_temp').val();
			val_exclure_cohorte_resultat=$('#id_exclure_cohorte_resultat').val();
			if (cohort_num_encours!='') {
				jQuery.ajax({
					type:"POST",
					url:"ajax.php",
					async:true,
					data: { action:'tout_inclure_exclure',cohort_num_encours:cohort_num_encours,tmpresult_num:tmpresult_num,query_num:query_num,status:status,val_exclure_cohorte_resultat:val_exclure_cohorte_resultat,datamart_num:<? print $datamart_num; ?>},
					beforeSend: function(requester){
					},
					success: function(requester){
						if (requester=='deconnexion') {
							afficher_connexion();
						} else {
							afficher_cohorte_nb_patient_statut(cohort_num_encours)
							colorer_patient_cohorte(cohort_num_encours);
							liste_patient_cohorte_encours(cohort_num_encours,1);
							liste_patient_cohorte_encours(cohort_num_encours,0);
							liste_patient_cohorte_encours(cohort_num_encours,2);
							liste_patient_cohorte_encours(cohort_num_encours,3);
						}
					},
					complete: function(requester){
					},
					error: function(){
					}
				});
			}
		}
	}
			
			
	function voir_cohorte_onglet (onglet) {
		$(".div_result").css("display","none");
		$(".color-bullet").removeClass("current");
		
		document.getElementById('id_div_cohorte_'+onglet).style.display='inline';
		$("#id_bouton_"+onglet).addClass("current");
	}
	
	
	function ajouter_user_cohorte() {
		user_num_cohort=$('#id_ajouter_select_user').val();
		cohort_num=$('#id_cohort_num_voir').val();
		
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:true,
			data: { action:'ajouter_user_cohorte',cohort_num:cohort_num,user_num_cohort:user_num_cohort},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion();
				} else {
					document.getElementById('id_tableau_liste_user_cohorte').innerHTML=requester;
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
	function ajouter_droit_cohorte(cohort_num,user_num_cohort,right) {
	
		if (document.getElementById('id_droit_'+user_num_cohort+'_'+right).checked) {
			option='ajouter';
		} else {
			option='supprimer';
		}
	
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:true,
			data: { action:'ajouter_droit_cohorte',cohort_num:cohort_num,user_num_cohort:user_num_cohort,right:right,option:option},
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
	
	function supprimer_user_cohorte(user_num_cohort,cohort_num) {
		if (confirm ('Etes vous sûr de vouloir supprimer cet utilisateur ? ')) {
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:true,
				data: { action:'supprimer_user_cohorte',cohort_num:cohort_num,user_num_cohort:user_num_cohort},
				beforeSend: function(requester){
				},
				success: function(requester){
					if (requester=='deconnexion') {
						afficher_connexion();
					} else {
						document.getElementById('id_tableau_liste_user_cohorte').innerHTML=requester;
					}
				},
				complete: function(requester){
				},
				error: function(){
				}
			});
		}
	}
	function supprimer_cohorte(cohort_num) {
		if (confirm ('Etes vous sûr de vouloir supprimer cette cohorte ? ')) {
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:true,
				data: { action:'supprimer_cohorte',cohort_num:cohort_num},
				beforeSend: function(requester){
				},
				success: function(requester){
					if (requester=='deconnexion') {
						afficher_connexion();
					} else {
						document.getElementById('id_div_liste_cohorte').innerHTML=requester;
						document.getElementById('id_div_ma_cohorte').innerHTML='';
						jQuery('#id_tableau_liste_cohortes').dataTable( { 
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
	function modifier_titre_cohorte(cohort_num) {
		title_cohort=$('#id_input_titre_cohorte').val();
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:true,
			data: { action:'modifier_titre_cohorte',cohort_num:cohort_num,title_cohort:escape(title_cohort)},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion();
				} else {
					document.getElementById('id_div_liste_cohorte').innerHTML=requester;
				 	jQuery('#id_tableau_liste_cohortes').dataTable( { 
							paging: false,
							"order": [[ 1, "asc" ]],
							   "bInfo" : false,
							   "bDestroy" : true
					} );
					document.getElementById('id_sous_span_titre_cohorte').innerHTML=title_cohort;
					plier('id_titre_cohorte_modifier');
					deplier('id_titre_cohorte','inline');
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
	
	function modifier_description_cohort(cohort_num) {
		description_cohort=document.getElementById('id_textarea_description_cohort_modifier').value;
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:true,
			data: { action:'modifier_description_cohort',cohort_num:cohort_num,description_cohort:escape(description_cohort)},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion();
				} else {
					document.getElementById('id_div_cohorte_description').innerHTML=requester;
					plier('id_div_cohorte_description_modifier');
					deplier('id_div_cohorte_description','inline');
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
	
	
	function display_cohort_concepts_tab (cohort_num) {
		jQuery.ajax({
			type:"POST",
			url:"ajax_cohorte.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'display_cohort_concepts_tab',cohort_num:cohort_num},
			beforeSend: function(requester){
				jQuery("#id_div_cohorte_concepts").html("<img src='images/chargement_mac.gif'>"); 
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("display_cohort_concepts_tab ('"+cohort_num+"')");
				} else {
					jQuery("#id_div_cohorte_concepts").html(contenu); 
					$("#id_tableau_concepts_cohorte").dataTable( {paging: false, "order": [[ 1, "desc" ]]});
				}
			}
		});
	}
	
	////////////////////////// SIMILARITE ///////////////////////
	
	
		
	function precalcul_nb_patient_similarite_cohorte (cohort_num) {
		requete=document.getElementById("id_textarea_similarite_cohorte_requete").value;
		process_num=document.getElementById("id_process_num").value;
		jQuery.ajax({
			type:"POST",
			url:"ajax_cohorte.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'precalcul_nb_patient_similarite_cohorte',cohort_num:cohort_num,requete:escape(requete),process_num:process_num},
			beforeSend: function(requester){
				jQuery("#id_span_precalcul_nb_patient_similarite_cohorte").html("<img src='images/chargement_mac.gif'>"); 
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("precalcul_nb_patient_similarite_cohorte('"+cohort_num+"')");
				} else {
					process_num=contenu;
					if (process_num!='') {
						document.getElementById("id_process_num").value=process_num;
						setTimeout("verifier_process_fini_precalcul_nb_patient_similarite_cohorte('"+process_num+"','"+cohort_num+"')",1000);
					}
				}
			}
		});
	}



	function verifier_process_fini_precalcul_nb_patient_similarite_cohorte (process_num,cohort_num) {
		jQuery.ajax({
			type:"POST",
			url:"ajax_cohorte.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'verifier_process_fini_precalcul_nb_patient_similarite_cohorte',process_num:process_num,cohort_num:cohort_num},
			beforeSend: function(requester){
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("verifier_process_fini_precalcul_nb_patient_similarite_cohorte ('"+process_num+"','"+cohort_num+"')");
				} else {
					tab=contenu.split(';');
					status=tab[0];
					valeur=tab[1];
					if (status=='1') {
						jQuery("#id_span_precalcul_nb_patient_similarite_cohorte").html(valeur+' <img src="images/chargement_mac.gif" width="15px">' +' patients'); 
						if (document.getElementById("id_process_num").value==process_num) { // on verifie que le contenu n a pas change pour ne pas lancer plein de process en parallele //
							setTimeout("verifier_process_fini_precalcul_nb_patient_similarite_cohorte('"+process_num+"','"+cohort_num+"')",1000);
						}
					} else {
						jQuery("#id_span_precalcul_nb_patient_similarite_cohorte").html(valeur+' patients'); 
					}
				}
			}
		});
	}


	function calculer_similarite_cohorte (cohort_num) {
		process_num=document.getElementById("id_process_num").value;
		nbpatient_limite=document.getElementById("id_input_nbpatient_limite").value;
		limite_count_concept_par_patient_num=document.getElementById("id_input_limite_count_concept_par_patient_num").value;
		
		tab_cohorte_exclue= $("#id_select_filtre_cohorte_exclue").val() || [] ;
		if( typeof tab_cohorte_exclue === 'string' ) {
			cohorte_exclue=tab_cohorte_exclue;
		} else {
			cohorte_exclue=tab_cohorte_exclue.join(",");
		}
		
		patients_importes='';
	
		if (document.getElementById('id_checkbox_similarite_sur_patients_importes').checked) {
			patients_importes='1';
		} 
		
		jQuery.ajax({
			type:"POST",
			url:"ajax_cohorte.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'calculer_similarite_cohorte',cohort_num:cohort_num,process_num:process_num,limite_count_concept_par_patient_num:limite_count_concept_par_patient_num,nbpatient_limite:nbpatient_limite,patients_importes:patients_importes,cohorte_exclue:escape(cohorte_exclue)},
			beforeSend: function(requester){
				jQuery("#id_div_patient_similarite_patient_resultat").html("<img src='images/chargement_mac.gif'>"); 
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("calculer_similarite_cohorte('"+cohort_num+"')");
				} else {
					tab=contenu.split(';');
					status=tab[0];
					valeur=tab[1];
					if (status=='erreur') {
						jQuery("#id_div_cohorte_similarite_cohorte_resultat").html(valeur); 
					} else {
						setTimeout("verifier_process_fini_similarite_cohorte('"+process_num+"','"+cohort_num+"')",1000);
					}
				}
			}
		});
	}


	function verifier_process_fini_similarite_cohorte (process_num,cohort_num) {
		jQuery.ajax({
			type:"POST",
			url:"ajax_cohorte.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'verifier_process_fini_similarite_cohorte',process_num:process_num,cohort_num:cohort_num},
			beforeSend: function(requester){
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("verifier_process_fini_similarite_cohorte ('"+process_num+"','"+cohort_num+"')");
				} else {
					tab=contenu.split(';');
					status=tab[0];
					message=tab[1];
					if (status=='1') { // end
						afficher_resultat_similarite_cohorte (process_num,cohort_num);
					} else {
						if (status=='erreur') {
							jQuery("#id_div_cohorte_similarite_cohorte_resultat").html(message); 
						} else {
							jQuery("#id_div_cohorte_similarite_cohorte_resultat").html(message+" <img src='images/chargement_mac.gif'>"); 
							if (document.getElementById("id_process_num").value==process_num) { // on verifie que le contenu n a pas change pour ne pas lancer plein de process en parallele //
								setTimeout("verifier_process_fini_similarite_cohorte('"+process_num+"','"+cohort_num+"')",1000);
							}
						}
					}
				}
			}
		});
	}
	
	

	function afficher_resultat_similarite_cohorte (process_num,cohort_num) {
		jQuery.ajax({
			type:"POST",
			url:"ajax_cohorte.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'afficher_resultat_similarite_cohorte',process_num:process_num,cohort_num:cohort_num},
			beforeSend: function(requester){
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("afficher_resultat_similarite ('"+process_num+"','"+cohort_num+"')");
				} else {
					jQuery("#id_div_cohorte_similarite_cohorte_resultat").html(contenu); 
					$("#id_tableau_similarite_cohorte").dataTable( {paging: false, "order": [[ 1, "desc" ]]});
				}
			}
		});
	}
	
</script>