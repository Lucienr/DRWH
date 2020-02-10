
function precalcul_nb_patient_similarite_patient (patient_num) {
	requete=document.getElementById("id_textarea_similarite_patient_requete").value;
	requete_exclure=document.getElementById("id_input_requete_exclure").value;
	
	process_num=document.getElementById("id_process_num").value;
	jQuery.ajax({
		type:"POST",
		url:"similarite_ajax.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'precalcul_nb_patient_similarite_patient',patient_num:patient_num,requete:escape(requete),requete_exclure:escape(requete_exclure),process_num:process_num},
		beforeSend: function(requester){
			jQuery("#id_span_precalcul_nb_patient_similarite_patient").html("<img src='images/chargement_mac.gif'>"); 
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("precalcul_nb_patient_similarite_patient('"+patient_num+"')");
			} else {
				process_num=contenu;
				if (process_num!='') {
					document.getElementById("id_process_num").value=process_num;
					setTimeout("verifier_process_fini_precalcul_nb_patient_similarite_patient('"+process_num+"','"+patient_num+"')",1000);
				}
			}
		}
	});
}



function verifier_process_fini_precalcul_nb_patient_similarite_patient (process_num,patient_num) {
	jQuery.ajax({
		type:"POST",
		url:"similarite_ajax.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'verifier_process_fini_precalcul_nb_patient_similarite_patient',process_num:process_num,patient_num:patient_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("verifier_process_fini_precalcul_nb_patient_similarite_patient ('"+process_num+"','"+patient_num+"')");
			} else {
			
				tab=contenu.split(';');
				status=tab[0];
				valeur=tab[1];
				if (status=='1') { // end
					jQuery("#id_span_precalcul_nb_patient_similarite_patient").html(valeur+' patients'); 
				} else {
					jQuery("#id_span_precalcul_nb_patient_similarite_patient").html(valeur+' <img src="images/chargement_mac.gif" width="15px">' +' patients'); 
					if (document.getElementById("id_process_num").value==process_num) { // on verifie que le contenu n a pas change pour ne pas lancer plein de process en parallele //
						setTimeout("verifier_process_fini_precalcul_nb_patient_similarite_patient('"+process_num+"','"+patient_num+"')",400);
					}
				} 
			}
		}
	});
}


function calculer_similarite_patient (patient_num) {
	process_num=document.getElementById("id_process_num").value;
	requete=document.getElementById("id_textarea_similarite_patient_requete").value;
	nbpatient_limite=document.getElementById("id_input_nbpatient_limite").value;
	cohort_num_exclure=document.getElementById("id_cohort_num_exclure").value;
	limite_count_concept_par_patient_num=document.getElementById("id_input_limite_count_concept_par_patient_num").value;
	requete_exclure='';
	//if (requete=='') {
		requete_exclure=document.getElementById("id_input_requete_exclure").value;
	//}
	if (document.getElementById("id_list_concept_selected")) {
		list_concept_selected=document.getElementById("id_list_concept_selected").value;
		list_concept_selected_weight=document.getElementById("id_list_concept_selected_weight").value;
	} else {
		list_concept_selected='';
		list_concept_selected_weight='';
	}
	
	
	jQuery.ajax({
		type:"POST",
		url:"similarite_ajax.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'calculer_similarite_patient',patient_num:patient_num,list_concept_selected:list_concept_selected,list_concept_selected_weight:list_concept_selected_weight,process_num:process_num,nbpatient_limite:nbpatient_limite,cohort_num_exclure:cohort_num_exclure,limite_count_concept_par_patient_num:limite_count_concept_par_patient_num,requete_exclure:escape(requete_exclure)},
		beforeSend: function(requester){
			jQuery("#id_div_patient_similarite_patient_resultat").html("<img src='images/chargement_mac.gif'>"); 
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("calculer_similarite_patient('"+patient_num+"')");
			} else {
				tab=contenu.split(';');
				status=tab[0];
				valeur=tab[1];
				if (status=='erreur') {
					jQuery("#id_div_patient_similarite_patient_resultat").html(valeur); 
				} else {
					tab=contenu.split(';');
					status=tab[0];
					valeur=tab[1];
					nb_patient=tab[2];
					if (status=='erreur') {
						jQuery("#id_div_patient_similarite_patient_resultat").html(valeur); 
					} else {
						setTimeout("verifier_process_fini_similarite('"+process_num+"','"+patient_num+"')",1000);
					}
				}
			}
		}
	});
}


function verifier_process_fini_similarite (process_num,patient_num) {
	jQuery.ajax({
		type:"POST",
		url:"similarite_ajax.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'verifier_process_fini_similarite',process_num:process_num,patient_num:patient_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("verifier_process_fini_similarite ('"+process_num+"','"+patient_num+"')");
			} else {
				tab=contenu.split(';');
				status=tab[0];
				message=tab[1];
				if (status=='1') { // end
					afficher_resultat_similarite (process_num,patient_num);
				} else {
					if (status=='erreur') {
						jQuery("#id_div_patient_similarite_patient_resultat").html(message); 
					} else {
						if (document.getElementById("id_process_num").value==process_num) { // on verifie que le contenu n a pas change pour ne pas lancer plein de process en parallele //
							jQuery("#id_div_patient_similarite_patient_resultat").html(message+" <img src='images/chargement_mac.gif'>"); 
							setTimeout("verifier_process_fini_similarite('"+process_num+"','"+patient_num+"')",1000);
						}
					}
				}
			}
		}
	});
}



function afficher_resultat_similarite (process_num,patient_num) {
	jQuery.ajax({
		type:"POST",
		url:"similarite_ajax.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'afficher_resultat_similarite',process_num:process_num,patient_num:patient_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("afficher_resultat_similarite ('"+process_num+"','"+patient_num+"')");
			} else {
				jQuery("#id_div_patient_similarite_patient_resultat").html(contenu); 
				jQuery("#id_tableau_similarite_patient").dataTable( {paging: false, "order": [[ 1, "desc" ]]});
				jQuery("#id_tableau_liste_concepts_patient_similaire").dataTable( {paging: false, "order": [[ 5, "desc" ]]});
			}
		}
	});
}


var mouseX;
var mouseY;
jQuery(document).mousedown( function(e) {
	mouseX = e.pageX; 
	mouseY = e.pageY;
});  
function affiche_intersect(patient_num_1,patient_num_2,id,similarite) {
	jQuery.ajax({
		type:"POST",
		url:"similarite_ajax.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'affiche_intersect',patient_num_1:patient_num_1,patient_num_2:patient_num_2,distance:10},
		beforeSend: function(requester){
				document.getElementById('id_affiche_intersect').style.display='block';
				document.getElementById('id_affiche_intersect').innerHTML='<img src="images/chargement_mac.gif">';
				jQuery('#id_affiche_intersect').css({'top':mouseY,'left':mouseX}).fadeIn('slow');
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion();
			} else {
				document.getElementById('id_affiche_intersect').innerHTML="similarite : "+similarite+"<br>"+contenu;
			}
		},
		complete: function(requester){
			
		},
		error: function(){
		}
	});
}