
jQuery(function() {
	jQuery( "#id_champs_recherche_rapide_utilisateur" ).autocomplete({
		source: "ajax.php?action=autocomplete_rech_rapide_utilisateur_ajout",
		minLength: 2,
		select: function( event, ui ) {
			login=ui.item.id;
			user=ui.item.value;
			tableau_user=user.split(',');
			lastname=tableau_user[0];
			firstname=tableau_user[1];
			mail=tableau_user[2];
			
			document.getElementById('id_ajouter_login_user').value=login;
			document.getElementById('id_ajouter_lastname_user').value=lastname;
			document.getElementById('id_ajouter_firstname_user').value=firstname;
			document.getElementById('id_ajouter_mail_user').value=mail;
			return false;
		}
	});
});

function ajouter_user_admin () {
	document.getElementById('id_div_resultat_ajouter_user').innerHTML='';
	
	login=document.getElementById('id_ajouter_login_user').value;
	passwd=document.getElementById('id_ajouter_passwd_user').value;
	lastname=document.getElementById('id_ajouter_lastname_user').value;
	firstname=document.getElementById('id_ajouter_firstname_user').value;
	expiration_date=document.getElementById('id_ajouter_expiration_date_user').value;
	mail=document.getElementById('id_ajouter_mail_user').value;
	
	liste_profils='';
	$('.input_ajouter_user_profile:checked').each(function(){
		liste_profils=liste_profils+','+$(this).val();
	});
	
	liste_services='';
	$('.select_ajouter_select_department_num_multiple:selected').each(function(){
		liste_services=liste_services+','+$(this).val();
	});
	
	jQuery.ajax({
		type:"POST",
		url:"ajax_admin.php",
		async:true,
		data: { action:'ajouter_user_admin',login:login,passwd:escape(passwd),lastname:escape(lastname),firstname:escape(firstname),mail:escape(mail),expiration_date:escape(expiration_date),liste_profils:escape(liste_profils),liste_services:escape(liste_services)},
		beforeSend: function(requester){
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} else {
				document.getElementById('id_div_resultat_ajouter_user').innerHTML=requester;
				document.getElementById('id_ajouter_login_user').value='';
				document.getElementById('id_ajouter_lastname_user').value='';
				document.getElementById('id_ajouter_firstname_user').value='';
				document.getElementById('id_ajouter_expiration_date_user').value='';
				document.getElementById('id_ajouter_mail_user').value='';
				document.getElementById('id_ajouter_passwd_user').value='';
				rafraichir_tableau_users();
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		

}


function ajouter_liste_user_admin() {
	document.getElementById('id_div_resultat_ajouter_liste_user').innerHTML='';
	
	list_user=document.getElementById('id_textarea_list_user').value;
	
	liste_profils='';
	$('.checkbox_liste_user_profile:checked').each(function(){
		liste_profils=liste_profils+','+$(this).val();
	});
	
	department_num=document.getElementById('id_select_service').value;
	jQuery.ajax({
		type:"POST",
		url:"ajax_admin.php",
		async:true,
		data: { action:'ajouter_liste_user_admin',list_user:list_user,liste_profils:escape(liste_profils),department_num:department_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} else {
				document.getElementById('id_div_resultat_ajouter_liste_user').innerHTML=requester;
				rafraichir_tableau_users();
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		

}


function add_expiration_date_group_admin() {
	document.getElementById('id_div_resultat_add_expiration_date_group').innerHTML='';
	list_user=document.getElementById('id_textarea_list_user_expiration_date_group').value;
	expiration_date=document.getElementById('id_modifier_expiration_date_group').value;
	
	jQuery.ajax({
		type:"POST",
		url:"ajax_admin.php",
		async:true,
		data: { action:'add_expiration_date_group_admin',list_user:list_user,expiration_date:escape(expiration_date)},
		beforeSend: function(requester){
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} else {
				document.getElementById('id_div_resultat_add_expiration_date_group').innerHTML=requester;
				rafraichir_tableau_users();
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		

}

jQuery(function() {
	jQuery( "#id_champs_recherche_annuaire_interne" ).autocomplete({
		source: "ajax.php?action=recherche_annuaire_interne",
		minLength: 2,
		select: function( event, ui ) {
			login=ui.item.id;
			user=ui.item.value;
			tableau_user=user.split(',');
			lastname=tableau_user[0];
			firstname=tableau_user[1];
			mail=tableau_user[2];
			user_num=tableau_user[3];

			
			
			document.getElementById('id_modifier_login_user').value=login;
			document.getElementById('id_modifier_lastname_user').value=lastname;
			document.getElementById('id_modifier_firstname_user').value=firstname;
			document.getElementById('id_modifier_mail_user').value=mail;
			document.getElementById('id_modifier_num_user').value=user_num;
			recup_profils(user_num);
			recup_services(user_num);
			document.getElementById('id_champs_recherche_annuaire_interne').value='';
			
			return false;
		}
	});
});


function modifier_user_admin() {
	$(".chosen-select").trigger("chosen:updated");
	document.getElementById('id_div_resultat_ajouter_user').innerHTML='';
	
	user_num=document.getElementById('id_modifier_num_user').value;
	login=document.getElementById('id_modifier_login_user').value;
	lastname=document.getElementById('id_modifier_lastname_user').value;
	firstname=document.getElementById('id_modifier_firstname_user').value;
	mail=document.getElementById('id_modifier_mail_user').value;
	expiration_date=document.getElementById('id_modifier_expiration_date_user').value;
	passwd=document.getElementById('id_modifier_passwd_user').value;
	
	
	
	liste_profils='';
	$('.input_modifier_user_profile:checked').each(function(){
		liste_profils=liste_profils+','+$(this).val();
	});
	
	liste_services='';
	$('.select_modifier_select_department_num_multiple:selected').each(function(){
		liste_services=liste_services+','+$(this).val();
	});
	
	
	
	
	jQuery.ajax({
		type:"POST",
		url:"ajax_admin.php",
		async:true,
		data: { action:'modifier_user_admin',passwd:passwd,user_num:user_num,login:login,lastname:escape(lastname),firstname:escape(firstname),mail:escape(mail),expiration_date:escape(expiration_date),liste_profils:escape(liste_profils),liste_services:escape(liste_services)},
		beforeSend: function(requester){
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} else {
				document.getElementById('id_div_resultat_modifier_user').innerHTML=requester;
				document.getElementById('id_modifier_login_user').value='';
				document.getElementById('id_modifier_lastname_user').value='';
				document.getElementById('id_modifier_firstname_user').value='';
				document.getElementById('id_modifier_mail_user').value='';
				document.getElementById('id_modifier_expiration_date_user').value='';
				document.getElementById('id_modifier_num_user').value='';
				document.getElementById('id_modifier_passwd_user').value='';
				$('.input_modifier_user_profile').each(function(){
					document.getElementById('id_modifier_user_profile_'+$(this).val()).checked=false;
				});
				$('.select_modifier_select_department_num_multiple').each(function(){
					document.getElementById('id_modifier_select_department_num_multiple_'+$(this).val()).selected=false;
				});
				
				$(".chosen-select").trigger("chosen:updated");
				rafraichir_tableau_users();
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		

}


function add_table_line_profil(user_profile) {
	jQuery.ajax({
		type:"POST",
		url:"ajax_admin.php",
		async:true,
		data: { action:'add_table_line_profil',user_profile:escape(user_profile),option:'global_features'},
		beforeSend: function(requester){
		},
		success: function(requester){
			$('#id_table_liste_profil_global_features').append(requester);
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		
	jQuery.ajax({
		type:"POST",
		url:"ajax_admin.php",
		async:true,
		data: { action:'add_table_line_profil',user_profile:escape(user_profile),option:'patient_features'},
		beforeSend: function(requester){
		},
		success: function(requester){
			$('#id_table_liste_profil_patient_features').append(requester);
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		
	jQuery.ajax({
		type:"POST",
		url:"ajax_admin.php",
		async:true,
		data: { action:'add_table_line_profil',user_profile:escape(user_profile),option:'document_origin_code'},
		beforeSend: function(requester){
		},
		success: function(requester){
			$('#id_table_liste_profil_document_origin_code').append(requester);
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		

}

function check_all_patient_features(patient_features) {
	jQuery.ajax({
		type:"POST",
		url:"ajax_admin.php",
		async:true,
		data: { action:'check_all_patient_features',patient_features:escape(patient_features)},
		beforeSend: function(requester){
		},
		success: function(requester){
			eval(requester);
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		
}
function ajouter_nouveau_profil() {
	user_profile=document.getElementById('id_input_new_profil').value;
	jQuery.ajax({
		type:"POST",
		url:"ajax_admin.php",
		async:true,
		data: { action:'ajouter_nouveau_profil',user_profile:escape(user_profile)},
		beforeSend: function(requester){
		},
		success: function(requester){
			document.getElementById('id_input_new_profil').value='';
			add_table_line_profil(user_profile);
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		
}
function rafraichir_tableau_users() {
	jQuery.ajax({
		type:"POST",
		url:"ajax_admin.php",
		async:true,
		data: { action:'rafraichir_tableau_users'},
		beforeSend: function(requester){
			jQuery("#id_div_tableau_users").html("<img src='images/chargement_mac.gif'>"); 
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} else {
				jQuery("#id_div_tableau_users").html(requester); 
				$("#id_tableau_users").dataTable( { "order": [[ 1, "asc" ]],"pageLength": 25});
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		
}


function affecter_manager_department_service(department_num,user_num) {
	if (document.getElementById('id_manager_department_service_'+department_num+'_'+user_num).checked==true) {
		manager_department=1;
	} else {
		manager_department=0;
	}
	jQuery.ajax({
		type:"POST",
		url:"ajax_admin.php",
		async:true,
		data: { action:'affecter_manager_department_service',department_num:department_num,user_num:user_num,manager_department:manager_department},
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
function supprimer_user_admin(user_num) {
	if (confirm (get_translation('ETES_VOUS_SUR_DE_VOULOIR_SUPPRIMER_CET_UTILISATEUR','Etes vous sûr de vouloir supprimer cet utilisateur')+' ?')) {
		jQuery.ajax({
		type:"POST",
		url:"ajax_admin.php",
		async:true,
		data: { action:'supprimer_user_admin',user_num:user_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} else {
				$("tr#id_tr_user_"+user_num).remove();
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		
	
	}
}

	
function afficher_modif_user (user_num) {
				recup_profils(user_num);
				recup_services(user_num);
	jQuery.ajax({
		type:"POST",
		url:"ajax_admin.php",
		async:true,
		data: { action:'afficher_modif_user',user_num:user_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} else {
				tableau_user=requester.split(',');
				lastname=tableau_user[0];
				firstname=tableau_user[1];
				mail=tableau_user[2];
				login=tableau_user[3];
				expiration_date=tableau_user[4];
				document.getElementById('id_modifier_login_user').value=login;
				document.getElementById('id_modifier_lastname_user').value=lastname;
				document.getElementById('id_modifier_firstname_user').value=firstname;
				document.getElementById('id_modifier_mail_user').value=mail;
				document.getElementById('id_modifier_expiration_date_user').value=expiration_date;
				document.getElementById('id_modifier_num_user').value=user_num;
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		
}

function recup_profils(user_num) {
	jQuery.ajax({
		type:"POST",
		url:"ajax_admin.php",
		async:true,
		data: { action:'recup_profils',user_num:user_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} else {
				eval(requester);
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		
}
function recup_services(user_num) {
	jQuery.ajax({
		type:"POST",
		url:"ajax_admin.php",
		async:true,
		data: { action:'recup_services',user_num:user_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} else {
				eval(requester);
				$(".chosen-select").trigger("chosen:updated");
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		
}

function modifier_droit_profil(user_profile, right) {
	var action='';
	if (document.getElementById('id_checkbox_'+user_profile+'_'+right).checked==true) {
		action='ajouter_droit_profil';
		jQuery('#id_td_'+user_profile+'_'+right).css('backgroundColor','#ffccff');
	} else {
		action='supprimer_droit_profil';
		jQuery('#id_td_'+user_profile+'_'+right).css('backgroundColor','#ffffff');
	}

	jQuery.ajax({
		type:"POST",
		url:"ajax_admin.php",
		async:true,
		data: { action:action,user_profile:user_profile,right:right},
		beforeSend: function(requester){
		},
		success: function(requester){
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		
}	
	
function supprimer_profil(user_profile) {
	if (confirm (get_translation('ETES_VOUS_SUR_DE_VOULOIR_SUPPRIMER_CE_PROFIL','Etes vous sûr de vouloir supprimer ce profil')+' ?')) {
		jQuery.ajax({
			type:"POST",
			url:"ajax_admin.php",
			async:true,
			data: { action:'supprimer_profil',user_profile:user_profile},
			beforeSend: function(requester){
			},
			success: function(requester){
				$("tr#id_tr_global_features_"+user_profile).remove();
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
}
	
function modifier_droit_profil_document_origin_code(user_profile, document_origin_code,id_document_origin_code) {
	var action='';
	if (document.getElementById('id_checkbox_document_origin_code_'+user_profile+'_'+id_document_origin_code).checked==true) {
		action='ajouter_droit_profil_document_origin_code';
		jQuery('#id_td_'+user_profile+'_'+id_document_origin_code).css('backgroundColor','#ffccff');
	} else {
		action='supprimer_droit_profil_document_origin_code';
		jQuery('#id_td_'+user_profile+'_'+id_document_origin_code).css('backgroundColor','#ffffff');
	}

	jQuery.ajax({
		type:"POST",
		url:"ajax_admin.php",
		async:true,
		data: { action:action,user_profile:user_profile,document_origin_code:document_origin_code},
		beforeSend: function(requester){
		},
		success: function(requester){
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
		url:"ajax_admin.php",
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
		url:"ajax_admin.php",
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
		url:"ajax_admin.php",
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
		url:"ajax_admin.php",
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

function affiche_patient_opposition() {
	term=document.getElementById('id_opposition_term').value;
	jQuery.ajax({
		type:"POST",
		url:"ajax_admin.php",
		async:true,
		data: { action:'affiche_patient_opposition',term:escape(term)},
		beforeSend: function(requester){
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} else {
				$("#id_div_resultat_opposition_list").html(requester);
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		
}

function validate_opposition(patient_num) {
	jQuery.ajax({
		type:"POST",
		url:"ajax_admin.php",
		async:true,
		data: { action:'validate_opposition',patient_num:patient_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} else {
				$("#id_div_opposition_patient_"+patient_num).html(requester);
			}
		},
		complete: function(requester){
			list_patients_opposed();
		},
		error: function(){
		}
	});		
}


function cancel_opposition(patient_num) {
	jQuery.ajax({
		type:"POST",
		url:"ajax_admin.php",
		async:true,
		data: { action:'cancel_opposition',patient_num:patient_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} else {
				$("#id_div_opposition_patient_"+patient_num).html(requester);
			}
		},
		complete: function(requester){
			list_patients_opposed();
		},
		error: function(){
		}
	});		
}

function list_patients_opposed() {
	jQuery.ajax({
		type:"POST",
		url:"ajax_admin.php",
		async:true,
		data: { action:'list_patients_opposed'},
		beforeSend: function(requester){
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} else {
				$("#id_div_list_patients_opposed").html(requester);
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		
}
 
function insert_outil() {
	title=document.getElementById('id_ajouter_outil_titre').value;
	url=document.getElementById('id_ajouter_outil_url').value;
	authors=document.getElementById('id_ajouter_outil_authors').value;
	description=document.getElementById('id_ajouter_outil_description').value;
	jQuery.ajax({
		type:"POST",
		url:"ajax_admin.php",
		async:true,
		data: { action:'insert_outil',title:escape(title),url:escape(url),authors:escape(authors),description:escape(description)},
		beforeSend: function(requester){
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} else {
				$("#id_tableau_liste_outils").html(requester);
				document.getElementById('id_ajouter_outil_titre').value='';
				document.getElementById('id_ajouter_outil_url').value='';
				document.getElementById('id_ajouter_outil_authors').value='';
				document.getElementById('id_ajouter_outil_description').value='';
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		
}

function update_outil(tool_num,item) {
	title=document.getElementById('id_input_outil_titre_'+tool_num).value;
	description=document.getElementById('id_textarea_outil_description_'+tool_num).value;
	authors=document.getElementById('id_input_outil_authors_'+tool_num).value;
	url=document.getElementById('id_input_outil_url_'+tool_num).value;
	jQuery.ajax({
		type:"POST",
		url:"ajax_admin.php",
		async:true,
		data: { action:'update_outil',tool_num:tool_num,title:escape(title),description:escape(description),authors:escape(authors),url:escape(url)},
		beforeSend: function(requester){
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} else {
				
				document.getElementById('id_span_outil_titre_'+tool_num).innerHTML=title;
				document.getElementById('id_span_outil_authors_'+tool_num).innerHTML=authors;
				document.getElementById('id_span_outil_url_'+tool_num).innerHTML=url;
				description=description.replace(/\n/g,'<br>');
				document.getElementById('id_span_outil_description_'+tool_num).innerHTML=description;
				
				deplier('id_span_outil_'+item+'_'+tool_num,'block') ;
				plier('id_span_form_outil_'+item+'_'+tool_num) ;
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		
}

function delete_outil(tool_num) {
	if (confirm("Etes vous sûr de vouloir supprimer cet outil ? ")) {
		jQuery.ajax({
			type:"POST",
			url:"ajax_admin.php",
			async:true,
			data: { action:'delete_outil',tool_num:tool_num},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion();
				} else {
					$("tr#id_tr_outil_"+tool_num).remove();
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});		
	}	
}

function afficher_concepts() {
	concept=document.getElementById('id_concept').value;
	if (concept!='') {
		jQuery.ajax({
			type:"POST",
			url:"ajax_admin.php",
			async:true,
			data: { action:'afficher_concepts',concept:escape(concept)},
			beforeSend: function(requester){
					$("#id_div_liste_concepts").empty();
					$("#id_div_exclure_concepts").empty();
					$("#id_div_liste_concepts").append("<img src='images/chargement_mac.gif'>");
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion();
				} else {
					$("#id_div_liste_concepts").empty();
					$("#id_div_liste_concepts").append(requester);
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
}
function ajouter_concepts() {
	concept_code=document.getElementById('id_concept_code').value;
	concept_libelle_new=document.getElementById('id_concept_libelle_new').value;
	semantic_type=document.getElementById('id_type_semantic').value;
	add_mode=document.getElementById('id_add_mode').value;
	if (concept_code!='') {
		jQuery.ajax({
			type:"POST",
			url:"ajax_admin.php",
			async:true,
			data: { action:'ajouter_concepts',concept_libelle_new:escape(concept_libelle_new),concept_code:concept_code,semantic_type:escape(semantic_type),add_mode:escape(add_mode)},
			beforeSend: function(requester){
					$("#id_div_ajouter_concepts").empty();
					$("#id_div_ajouter_concepts").append("<img src='images/chargement_mac.gif'>");
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion();
				} else {
					$("#id_div_ajouter_concepts").empty();
					$("#id_div_ajouter_concepts").append(requester);
				}
			},
			complete: function(requester){
			},
			error: function(){
				$("#id_div_ajouter_concepts").append(requester);
			}
		});
	}
}


function exclure_concepts() {
	liste_val='';
	$('.concept_a_exclure:checked').each(function(){
		liste_val=liste_val+';'+$(this).val();
	});
	if (liste_val!='') {
		jQuery.ajax({
			type:"POST",
			url:"ajax_admin.php",
			async:true,
			data: { action:'exclure_concepts',liste_val:escape(liste_val)},
			beforeSend: function(requester){
				$("#id_div_exclure_concepts").empty();
				$("#id_div_exclure_concepts").append("<img src='images/chargement_mac.gif'>");
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion();
				} else {
					$("#id_div_exclure_concepts").empty();
					verif_process_exclure_concepts(requester);
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
}


function verif_process_exclure_concepts(process_num) {
	if (process_num!='') {
		jQuery.ajax({
			type:"POST",
			url:"ajax_admin.php",
			async:true,
			data: { action:'verif_process_exclure_concepts',process_num:process_num},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion();
				} else {
					tab=requester.split(';');
					etat=tab[0];
					valeur=tab[1];
					$("#id_div_exclure_concepts").html(valeur);
					if (etat!='1') { // end
						setTimeout("verif_process_exclure_concepts('"+process_num+"')",1000);
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


function calculate_nb_insert() {
	nb_jours=$('#id_calculate_nb_insert_nb_jours').val();
	jQuery.ajax({
		type:'POST',
		url:'ajax_admin.php',
		async:true,
		data: { action:'calculate_nb_insert',nb_jours:nb_jours},
		beforeSend: function(requester){
					$("#id_calculate_nb_insert").html("<img src='images/chargement_mac.gif'>");
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} else {
				$('#id_calculate_nb_insert').html(requester);
			}
		},
		complete: function(requester){
		},
		error: function(){
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

function ajouter_datamart() {
	title_datamart=document.getElementById('id_ajouter_titre_datamart').value;
	description_datamart=document.getElementById('id_ajouter_description_datamart').value;
	date_start=document.getElementById('id_ajouter_date_start_datamart').value;
	end_date=document.getElementById('id_ajouter_date_fin_datamart').value;
	$("#id_ajouter_select_user_multiple").each(function() {
	    liste_user_datamart=$(this).val();
	});
	liste_droit='';
	$(".ajouter_user_profile_datamart:checked").each(function() {
	    liste_droit+=$(this).val()+",";
	});
	liste_document_origin_code='';
	$(".ajouter_document_origin_code_datamart:checked").each(function() {
	    liste_document_origin_code+=$(this).val()+",";
	});
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:true,
		data: { action:'ajouter_datamart',liste_user_datamart:liste_user_datamart,liste_droit:liste_droit,title_datamart:escape(title_datamart),description_datamart:escape(description_datamart),date_start:date_start,end_date:end_date,liste_document_origin_code:liste_document_origin_code},
		beforeSend: function(requester){
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} else {
				$("#id_tableau_liste_datamart").append(requester);
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		
}

function afficher_formulaire_modifier_datamart(datamart_num) {
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:true,
		data: { action:'afficher_formulaire_modifier_datamart',num_datamart_admin:datamart_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} else {
				document.getElementById('id_admin_ajouter_datamart').style.display='none';
				document.getElementById('id_admin_modifier_datamart').style.display='block';
				document.getElementById('id_admin_modifier_datamart').innerHTML=requester;
				$(".chosen-select").chosen({width: "300px",max_selected_options: 50}); 
				$(".chosen-select").trigger("chosen:updated");
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});	
}
function annuler_modifier_datamart() {
	document.getElementById('id_admin_ajouter_datamart').style.display='block';
	document.getElementById('id_admin_modifier_datamart').style.display='none';
	document.getElementById('id_admin_modifier_datamart').innerHTML='';
}
function modifier_datamart() {
	datamart_num=document.getElementById('id_modifier_num_datamart').value;
	title_datamart=document.getElementById('id_modifier_titre_datamart').value;
	description_datamart=document.getElementById('id_modifier_description_datamart').value;
	date_start=document.getElementById('id_modifier_date_start_datamart').value;
	end_date=document.getElementById('id_modifier_date_fin_datamart').value;
	$("#id_modifier_select_user_multiple").each(function() {
	    liste_user_datamart=$(this).val();
	});
	liste_droit='';
	$(".modifier_user_profile_datamart:checked").each(function() {
	    liste_droit+=$(this).val()+",";
	});
	liste_document_origin_code='';
	$(".modifier_document_origin_code_datamart:checked").each(function() {
	    liste_document_origin_code+=$(this).val()+",";
	});
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:true,
		data: { action:'modifier_datamart',num_datamart_admin:datamart_num,liste_user_datamart:liste_user_datamart,liste_droit:liste_droit,liste_document_origin_code:liste_document_origin_code,title_datamart:escape(title_datamart),description_datamart:escape(description_datamart),date_start:date_start,end_date:end_date},
		beforeSend: function(requester){
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion();
			} else {
				$("tr#id_tr_datamart_"+datamart_num).replaceWith(requester);
				annuler_modifier_datamart() ;
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		
}

function supprimer_datamart(datamart_num) {
	if (confirm("Etes vous sûr de vouloir supprimer ce datamart ? ")) {
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:true,
			data: { action:'supprimer_datamart',num_datamart_admin:datamart_num},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion();
				} else {
					$("tr#id_tr_datamart_"+datamart_num).remove();
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});		
	}	
}

function save_cgu() {
	cgu_text=jQuery('.ql-editor').html();
	jQuery.ajax({
		type:"POST",
		url:"ajax_admin.php",
		async:true,
		data: { action:'save_cgu',cgu_text:escape(cgu_text)},
		beforeSend: function(requester){
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion("save_cgu ();");
			} else {
				list_cgu ();
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});
}

function list_cgu () {
	jQuery.ajax({
		type:"POST",
		url:"ajax_admin.php",
		async:true,
		data: { action:'list_cgu'},
		beforeSend: function(requester){
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion("delete_cgu ("+num_cgu+");");
			} else {
				$("#id_div_list_cgu").html(requester);
			}
		},
		complete: function(requester){
		},
		error: function(){
		}
	});		
}

function delete_cgu (cgu_num) {
	if (confirm("Etes vous sûr de vouloir supprimer ce CGU ? ")) {
		jQuery.ajax({
			type:"POST",
			url:"ajax_admin.php",
			async:true,
			data: { action:'delete_cgu',cgu_num:cgu_num},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion("delete_cgu ("+cgu_num+");");
				} else {
					$("tr#id_tr_cgu_"+cgu_num).remove();
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});		
	}	
}

function published_cgu (cgu_num) {
	if (confirm("Etes vous sûr de vouloir publier ce CGU ? ")) {
		jQuery.ajax({
			type:"POST",
			url:"ajax_admin.php",
			async:true,
			data: { action:'published_cgu',cgu_num:cgu_num},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion("published_cgu ("+cgu_num+");");
				} else {
					list_cgu ();
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});		
	}	
}

function unpublished_cgu (cgu_num) {
	if (confirm("Etes vous sûr de vouloir publier ce CGU ? ")) {
		jQuery.ajax({
			type:"POST",
			url:"ajax_admin.php",
			async:true,
			data: { action:'unpublished_cgu',cgu_num:cgu_num},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion("unpublished_cgu ("+cgu_num+");");
				} else {
					list_cgu ();
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});		
	}	
}

