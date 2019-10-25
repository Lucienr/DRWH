function rechercher_regexp() {
	regexp=jQuery('#id_rechercher_regexp').val();
	regexp=regexp.replace(/\+/g,';plus;');
	tmpresult_num=jQuery('#id_num_temp').val();
	datamart_num=jQuery('#id_num_datamart').val();
	libelle_regexp_selected=$( "#id_select_list_regexp option:selected" ).text();;
	jQuery('#id_div_resultat_recherche_regexp').html("<img src=images/chargement_mac.gif>");
	if (regexp!='') {
		jQuery.ajax({
			type:"POST",
			url:"ajax_regexp.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'rechercher_regexp',regexp:escape(regexp),tmpresult_num:tmpresult_num,datamart_num:datamart_num,libelle_regexp_selected:escape(libelle_regexp_selected)},
			beforeSend: function(requester){},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion("rechercher_regexp ()");
				} else { 
								
					 $('#id_button_rechercher_regexp').addClass("form_submit_activated"); 
					 $('#id_button_rechercher_regexp').removeClass("form_submit"); 
					verif_process_execute_regexp (requester);
				}
			},
			complete: function(requester){},
			error: function(){}
		});
	} else {
		jQuery('#id_div_resultat_recherche_regexp').html("");
	}

}

function verif_process_execute_regexp (process_num) {
	jQuery.ajax({
		type:"POST",
		url:"ajax_regexp.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'verif_process_execute_regexp',process_num:process_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			tab=contenu.split(';');
			status=tab[0];
			message=tab[1];
			if (status=='1') { // end
				get_regexp_result (process_num);
				 $('#id_button_rechercher_regexp').addClass("form_submit"); 
				 $('#id_button_rechercher_regexp').removeClass("form_submit_activated"); 
			} else {
				if (status=='erreur') {
					jQuery("#id_div_resultat_recherche_regexp").html(message); 
				} else {
					jQuery("#id_div_resultat_recherche_regexp").html(message+" <img src='images/chargement_mac.gif'>"); 
					setTimeout("verif_process_execute_regexp('"+process_num+"')",1000);
				}
			}
		}
	});
}

function get_regexp_result (process_num) {
	jQuery.ajax({
		type:"POST",
		url:"ajax_regexp.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'get_regexp_result',process_num:process_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			jQuery("#id_div_resultat_recherche_regexp").html(contenu); 
		}
	});
}

function save_regexp () {
	jQuery('#id_div_save_regexp_log').html("");
	regexp=jQuery('#id_rechercher_regexp').val();
	regexp=regexp.replace(/\+/g,';plus;');
	
	title=jQuery('#id_input_regexp_title').val();
	description=jQuery('#id_input_regexp_description').val();
	shared=0;
	if (document.getElementById('id_input_regexp_shared').checked) {
		shared=1;
	}
	if (regexp!='') {
		jQuery.ajax({
			type:"POST",
			url:"ajax_regexp.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'save_regexp',regexp:escape(regexp),title:escape(title),description:escape(description),shared:shared},
			beforeSend: function(requester){},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion("save_regexp ()");
				} else { 
					plier_deplier('id_div_save_regexp_log');
					jQuery('#id_div_save_regexp_log').html(get_translation ('PATTERN_SAVED','Pattern sauvé')+"<br><br>");
					open_save_regexp ();
					list_regexp_select ();
					jQuery('#id_input_regexp_title').val("");
					jQuery('#id_input_regexp_description').val("");
					document.getElementById('id_input_regexp_shared').checked=false;
					setTimeout("plier_deplier('id_div_save_regexp_log');",2000);
				}
			},
			complete: function(requester){},
			error: function(){}
		});
	} 
}


function list_regexp_select () {
	
	jQuery.ajax({
		type:"POST",
		url:"ajax_regexp.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'list_regexp_select'},
		beforeSend: function(requester){},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion("list_regexp_select ()");
			} else { 
				jQuery('#id_select_list_regexp').find('option').remove().end();
				eval(requester);
				jQuery(".chosen-select").trigger("chosen:updated");
			}
		},
		complete: function(requester){},
		error: function(){}
	});
}

function select_regexp () {
	regexp_num=jQuery('#id_select_list_regexp').val();
	
	jQuery.ajax({
		type:"POST",
		url:"ajax_regexp.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'select_regexp',regexp_num:regexp_num},
		beforeSend: function(requester){},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion("select_regexp ()");
			} else { 
				jQuery('#id_rechercher_regexp').val(requester);
				rechercher_regexp();
			}
		},
		complete: function(requester){},
		error: function(){}
	});
}

function manage_regexp () {
	plier_deplier('id_div_manage_regexp');
	jQuery.ajax({
		type:"POST",
		url:"ajax_regexp.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'manage_regexp'},
		beforeSend: function(requester){},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion("manage_regexp ()");
			} else { 
				jQuery("#id_div_manage_regexp").html(requester); 
			}
		},
		complete: function(requester){},
		error: function(){}
	});
	if ($('#id_div_manage_regexp').css('display') == 'none') {
		 $('#id_button_manage_regexp').addClass("form_submit"); 
		 $('#id_button_manage_regexp').removeClass("form_submit_activated"); 
	} else {
		 $('#id_button_manage_regexp').addClass("form_submit_activated"); 
		 $('#id_button_manage_regexp').removeClass("form_submit"); 
	}
}


function edit_modify_regexp (regexp_num) {
	$(".regexp_"+regexp_num).css("display","none");
	$(".regexp_modify_"+regexp_num).css("display","inline");
}


function modify_regexp (regexp_num) {
	regexp=document.getElementById('id_regexp_input_modify_regexp_'+regexp_num).value;
	regexp=regexp.replace(/\+/g,';plus;');
	title=document.getElementById('id_regexp_input_modify_title_'+regexp_num).value;
	description=document.getElementById('id_regexp_input_modify_description_'+regexp_num).value;
	shared=0;
	if (document.getElementById('id_regexp_input_modify_shared_'+regexp_num).checked) {
		shared=1;
	}
	if (regexp!='') {
		jQuery.ajax({
			type:"POST",
			url:"ajax_regexp.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'modify_regexp',regexp_num:regexp_num,regexp:escape(regexp),title:escape(title),description:escape(description),shared:shared},
			beforeSend: function(requester){},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion("modify_regexp ("+regexp_num+")");
				} else { 
					jQuery('#id_regexp_title_'+regexp_num).html(document.getElementById('id_regexp_input_modify_title_'+regexp_num).value);
					jQuery('#id_regexp_regexp_'+regexp_num).html(document.getElementById('id_regexp_input_modify_regexp_'+regexp_num).value);
					jQuery('#id_regexp_description_'+regexp_num).html(document.getElementById('id_regexp_input_modify_description_'+regexp_num).value);
					jQuery('#id_regexp_shared_'+regexp_num).html(shared);
					
					$(".regexp_"+regexp_num).css("display","inline");
					$(".regexp_modify_"+regexp_num).css("display","none");
					list_regexp_select ();
				}
			},
			complete: function(requester){},
			error: function(){}
		});
	} 
}



function delete_regexp (regexp_num) {
	if (confirm ('Etes vous sûr(e) de vouloir supprimer ce pattern ? ')) {
		jQuery.ajax({
			type:"POST",
			url:"ajax_regexp.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'delete_regexp',regexp_num:regexp_num},
			beforeSend: function(requester){},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion("delete_regexp ()");
				} else { 
					$("#id_tr_regexp_"+regexp_num).css("display","none");
					list_regexp_select ();
				}
			},
			complete: function(requester){},
			error: function(){}
		});
	}
}


function open_save_regexp () {
	plier_deplier('id_div_save_regexp');
	if ($('#id_div_save_regexp').css('display') == 'none') {
		 $('#id_button_open_save_regexp').addClass("form_submit"); 
		 $('#id_button_open_save_regexp').removeClass("form_submit_activated"); 
	} else {
		 $('#id_button_open_save_regexp').addClass("form_submit_activated"); 
		 $('#id_button_open_save_regexp').removeClass("form_submit"); 
	}
}

function afficher_document_regexp(document_num) {
	tmpresult_num=jQuery('#id_num_temp').val();
	regexp=jQuery('#id_rechercher_regexp').val();
	datamart_num=jQuery('#id_num_datamart').val();
	
	regexp=regexp.replace(/\+/g,';plus;');
	//regexp=regexp.replace(/\\/g,';antislash;');
	
	jQuery('.button_regexp').css('font-weight','normal');
	jQuery('.button_regexp').css('background-color','white');

	jQuery.ajax({
		type:"POST",
		url:"ajax_regexp.php",
		async:false,
		encoding: 'latin1',
		data:{ action:'afficher_document_regexp',tmpresult_num:tmpresult_num,document_num:document_num,regexp:escape(regexp),datamart_num:datamart_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("afficher_document("+document_num+")");
			} else {
				jQuery("#id_div_list_div_affichage_regexp").html(contenu); 
				
				jQuery( "#id_enveloppe_document_regexp_"+document_num).css("display","block");
				positionner("id_enveloppe_document_regexp_"+document_num,"id_button_regexp_"+document_num);
				mettre_index_haut_document_regexp (document_num);
				drag_resize_regexp(document_num);
			}
			
		},
		complete: function(requester){
		},
		error: function(){
		}
	});

}


function mettre_index_haut_document_regexp (id) {
	index_haut++;
	
	if (document.getElementById('id_enveloppe_document_regexp_'+id)) {
		if (document.getElementById('id_enveloppe_document_regexp_'+id).style.display=='block') {
			jQuery('#id_enveloppe_document_regexp_'+id).css('zIndex',index_haut);
			jQuery('#id_button_regexp_'+id).css('font-weight','bold');
			jQuery('#id_button_regexp_'+id).css('background-color','#CD1DAA');
		}
	}
	
}


function fermer_document_regexp(id) {
	jQuery('#id_button_regexp_'+id).css('font-weight','normal');
	jQuery('#id_button_regexp_'+id).css('background-color','white');
	jQuery('#id_enveloppe_document_regexp_'+id).css('display','none');
}


function drag_resize_regexp(document_num) {
	$('#id_enveloppe_document_regexp_'+document_num).css('min-width',400);
	$('#id_enveloppe_document_regexp_'+document_num).css('min-height',400);
	
	$('#id_document_regexp_'+document_num).css('width',$('#id_enveloppe_document_regexp_'+document_num).width()-25);
	$('#id_document_regexp_'+document_num).css('height',$('#id_enveloppe_document_regexp_'+document_num).height() -$('#id_bandeau_regexp_'+document_num).height() - 25);
	$('#id_enveloppe_document_regexp_'+document_num)
		.resizable({
		        start: function(e, ui) {
				mettre_index_haut_document_regexp(document_num);
		        },
		        resize: function(e, ui) {
				$('#id_document_regexp_'+document_num).css('width',$('#id_enveloppe_document_regexp_'+document_num).width()-25);
				$('#id_document_regexp_'+document_num).css('height',$('#id_enveloppe_document_regexp_'+document_num).height() -$('#id_bandeau_regexp_'+document_num).height()-25);
		        },
		        stop: function(e, ui) {
		        }
	});
	$('#id_bandeau_regexp_'+document_num).mouseover(function(){
		$('#id_enveloppe_document_regexp_'+document_num)
			.draggable({
			        start: function(e, ui) {
			        },
			        stop: function(e, ui) {
			        },
			       disabled: false, 
			       scroll: false ,
			        opacity: 1 
			});;
		$('#id_enveloppe_document_regexp_'+document_num).css('opacity',1);
		mettre_index_haut_document_regexp(document_num);
	});
	$('#id_bandeau_regexp_'+document_num).mouseout(function(){
		$('#id_enveloppe_document_regexp_'+document_num)
			.draggable({
			        start: function(e, ui) {
			        },
			        stop: function(e, ui) {
			        },
			       disabled: true,
			        opacity: 1 
			});
		$('#id_enveloppe_document_regexp_'+document_num).css('opacity',1);
	});
	
	$('#id_enveloppe_document_regexp_'+document_num).click(function(){
		mettre_index_haut_document_regexp(document_num);
	});
}


