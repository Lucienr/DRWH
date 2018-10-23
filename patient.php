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
    
include "head.php";
include "menu.php";
session_write_close();
include "fonctions_concepts.php";
include "fonctions_patient.php";
?>
<?
	$patient_num=$_GET['patient_num'];
	$tab_patient=get_patient ($patient_num);
	$hospital_patient_id=$tab_patient['HOSPITAL_PATIENT_ID'];
	$patient=afficher_patient ($patient_num,'patient','','','dossier');
	if ($patient=='') {
		$patient_num='';
	}
	if ($_SESSION['dwh_droit_modify_patient']=='ok') {
		print "<h1 id=\"id_h1_patient\"  style=\"display:block;\" onclick=\"plier_deplier('id_h1_patient_modifier');\">$patient</h1>";
		print "<h1 id=\"id_h1_patient_modifier\" style=\"display:none;\"><input type=text id=id_input_hospital_patient_id value='$hospital_patient_id'> <input type=button value= ".get_translation('MODIFY','modifier')." onclick=\"modify_hospital_patient_id('$patient_num','$hospital_patient_id');\"></h1>";
		print "<div id=\"id_div_modifier_patient\"></div>";
	} else {
		print "<h1>$patient</h1>";
	}
?>
	
<? if ($patient_num=='') { ?>
<? print "<h1>".get_translation('YOU_CANNOT_SEE_THIS_PATIENT',"Vous n'êtes pas autorisé à voir ce patient")."</h1>";
?>

<? } else { ?>

	<style>
	.tableau_document {
		font-size:12px;
	}
	</style>
	<script language="javascript">
		<? 
		if ($_SESSION['dwh_droit_modify_patient']=='ok') { 
		?>
			function modify_hospital_patient_id (patient_num,hospital_patient_id) {
				hospital_patient_id_new=document.getElementById('id_input_hospital_patient_id').value;
				jQuery.ajax({
					type:"POST",
					url:"ajax.php",
					async:true,
					encoding: 'latin1',
					data:{ action:'modify_hospital_patient_id',patient_num:patient_num,hospital_patient_id_new:hospital_patient_id_new,hospital_patient_id:hospital_patient_id},
					beforeSend: function(requester){
						jQuery("#id_div_modifier_patient").html("Modification en cours <img src='images/chargement_mac.gif'>"); 
					},
					success: function(requester){
						var contenu=requester;
						if (contenu=='deconnexion') {
							afficher_connexion("afficher_document_patient ('"+document_num+"')");
							jQuery("#id_div_voir_document").html(""); 
						} else {
							jQuery("#id_h1_patient").html(contenu); 
							jQuery("#id_div_modifier_patient").html("modification terminée"); 
							document.getElementById('id_h1_patient_modifier').style.display='none';
							document.getElementById('id_input_hospital_patient_id').value=hospital_patient_id_new;
						}
						
					}
				});
			}
		<? 
		}
		?>
		
		
		var id_tr_selectionne;
		function afficher_document_patient (document_num) {
			jQuery(".tr_document_patient").css("backgroundColor","#ffffff");
			jQuery(".tr_document_patient").css("fontWeight","normal");
			jQuery(".tr_document_patient").css("color","black");
			
			jQuery("#id_document_patient_"+document_num).css("fontWeight","bold");
			jQuery("#id_document_patient_"+document_num).css("color","#CB1B3E");
			jQuery("#id_document_patient_"+document_num).css("backgroundColor","#dcdff5");
			id_tr_selectionne="id_document_patient_"+document_num;
			requete=document.getElementById('id_input_filtre_patient_texte').value;
			requete=requete.replace(/\+/g,';plus;');
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:true,
				encoding: 'latin1',
				data:{ action:'afficher_document_patient',document_num:document_num,requete:escape(requete)},
				beforeSend: function(requester){
					jQuery("#id_div_voir_document").html("<img src='images/chargement_mac.gif'>"); 
				},
				success: function(requester){
					var contenu=requester;
					if (contenu=='deconnexion') {
						afficher_connexion("afficher_document_patient ('"+document_num+"')");
						jQuery("#id_div_voir_document").html(""); 
					} else {
						jQuery("#id_div_voir_document").html(contenu); 
						window.location='#ancre_entete';
					}
					
				}
			});
		}
		
	
		function afficher_document_patient_biologie (document_num) {
			jQuery(".tr_document_patient").css("backgroundColor","#ffffff");
			jQuery(".tr_document_patient").css("fontWeight","normal");
			jQuery(".tr_document_patient").css("color","black");
			
			jQuery("#id_document_patient_"+document_num).css("fontWeight","bold");
			jQuery("#id_document_patient_"+document_num).css("color","#CB1B3E");
			jQuery("#id_document_patient_"+document_num).css("backgroundColor","#dcdff5");
			id_tr_selectionne="id_document_patient_"+document_num;
			requete=document.getElementById('id_input_filtre_patient_texte_biologie').value;
			requete=requete.replace(/\+/g,';plus;');
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:true,
				encoding: 'latin1',
				data:{ action:'afficher_document_patient',document_num:document_num,requete:escape(requete)},
				beforeSend: function(requester){
					jQuery("#id_div_voir_biologie").html("<img src='images/chargement_mac.gif'>"); 
				},
				success: function(requester){
					var contenu=requester;
					if (contenu=='deconnexion') {
						afficher_connexion("afficher_document_patient_biologie ("+document_num+")");
					} else {
						jQuery("#id_div_voir_biologie").html(contenu); 
						window.location='#ancre_entete';
					}
					
				}
			});
		}
		
		$(document).keydown(function(e) {
			id_tr_document='1';
		    if (e.which ==38) {
		    	tr_precede=$("#"+id_tr_selectionne ).prev( "tr" );
		    	id_tr_document=$(tr_precede).attr('id');
		    	sousgroupe=$(tr_precede).attr('sousgroupe');
		    }
		    if (e.which ==40) {
		    	tr_precede=$("#"+id_tr_selectionne ).next( "tr" );
		    	id_tr_document=$(tr_precede).attr('id');
		    	sousgroupe=$(tr_precede).attr('sousgroupe');
		    }
			if (id_tr_document!='1') {
				if (id_tr_document) {
				    	document_num=id_tr_document.replace("id_document_patient_","");
					if (document_num) {
						if (sousgroupe=='text') {
					    		afficher_document_patient (document_num);
					    	}
						if (sousgroupe=='biologie') {
					    		afficher_document_patient_biologie (document_num);
					    	}
				    	} 
				}
			} 
		});
	
	
		function filtre_patient_texte (patient_num) {
			requete=document.getElementById('id_input_filtre_patient_texte').value;
			requete=requete.replace(/\+/g,';plus;');
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:true,
				encoding: 'latin1',
				data:{ action:'filtre_patient_texte',requete:escape(requete),patient_num:patient_num},
				beforeSend: function(requester){
					jQuery("#id_div_tableau_document").html("<img src='images/chargement_mac.gif'>"); 
				},
				success: function(requester){
					var contenu=requester;
					if (contenu=='deconnexion') {
						afficher_connexion("filtre_patient_texte ('"+patient_num+"')");
					} else {
						jQuery("#id_div_tableau_document").html(contenu); 
					}
				}
			});
		}
	
		function filtre_patient_texte_biologie (patient_num) {
			requete=document.getElementById('id_input_filtre_patient_texte_biologie').value;
			requete=requete.replace(/\+/g,';plus;');
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:true,
				encoding: 'latin1',
				data:{ action:'filtre_patient_texte_biologie',requete:escape(requete),patient_num:patient_num},
				beforeSend: function(requester){
					jQuery("#id_div_tableau_biologie").html("<img src='images/chargement_mac.gif'>"); 
				},
				success: function(requester){
					var contenu=requester;
					if (contenu=='deconnexion') {
						afficher_connexion("filtre_patient_texte_biologie ('"+patient_num+"')");
					} else {
						jQuery("#id_div_tableau_biologie").html(contenu); 
					}
				}
			});
		}
		var tableau_onglet_deja_ouvert=new Array();
		function voir_patient_onglet (onglet) {
			$(".div_result").css("display","none");
			$(".color-bullet").removeClass("current");
			
			document.getElementById('id_div_patient_'+onglet).style.display='inline';
			$("#id_bouton_"+onglet).addClass("current");
			
			
			if (onglet=='concepts' && tableau_onglet_deja_ouvert[onglet]!='ok') {
				tableau_onglet_deja_ouvert[onglet]='ok';
				afficher_onglet_concept_patient('<? print $patient_num; ?>');
			}
			if (onglet=='timeline' && tableau_onglet_deja_ouvert[onglet]!='ok') {
				tableau_onglet_deja_ouvert[onglet]='ok';
				document.getElementById('iframe_lignevie').src="include_lignevie.php?patient_num=<? print $patient_num; ?>";
			}
			if (onglet=='similarite_patient' && tableau_onglet_deja_ouvert[onglet]!='ok') {
				tableau_onglet_deja_ouvert[onglet]='ok';
				afficher_onglet_similarite_patient('<? print $patient_num; ?>');
			}
			
			
			largeur=$("#id_tableau_patient").css("width");
			$("#id_div_patient_timeline").css("width",largeur);
			
			var isIE = (document.all && !window.opera)?true:false;
			width=isIE  ? window.document.compatMode == "CSS1Compat" ? document.documentElement.clientWidth : document.body.clientWidth : window.innerWidth;
			width=width-50;
			$("#iframe_lignevie").css("width",width);
		}
		function afficher_onglet_concept_patient (patient_num) {
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:true,
				encoding: 'latin1',
				data:{ action:'afficher_onglet_concept_patient',patient_num:patient_num},
				beforeSend: function(requester){
					jQuery("#id_div_patient_concepts").html("<img src='images/chargement_mac.gif'>"); 
				},
				success: function(requester){
					var contenu=requester;
					if (contenu=='deconnexion') {
						afficher_connexion("afficher_onglet_concept_patient ('"+patient_num+"')");
					} else {
						jQuery("#id_div_patient_concepts").html(contenu); 
					}
				}
			});
		}
		function afficher_onglet_concepts_patient_et_resume (patient_num) {
			if (tableau_onglet_deja_ouvert['afficher_onglet_concepts_patient_et_resume']!='ok') {
				tableau_onglet_deja_ouvert['afficher_onglet_concepts_patient_et_resume']='ok';
				jQuery.ajax({
					type:"POST",
					url:"ajax.php",
					async:true,
					encoding: 'latin1',
					data:{ action:'afficher_onglet_concepts_patient_et_resume',patient_num:patient_num},
					beforeSend: function(requester){
						jQuery("#id_liste_concepts_patient_et_resume").html("<img src='images/chargement_mac.gif'>"); 
					},
					success: function(requester){
						var contenu=requester;
						if (contenu=='deconnexion') {
							afficher_connexion("afficher_onglet_concepts_patient_et_resume ('"+patient_num+"')");
						} else {
							jQuery("#id_liste_concepts_patient_et_resume").html(contenu); 
						}
					}
				});
			}
		}
		
		function afficher_onglet_similarite_patient (patient_num) {
			if (tableau_onglet_deja_ouvert['afficher_onglet_similarite_patient']!='ok') {
				tableau_onglet_deja_ouvert['afficher_onglet_similarite_patient']='ok';
				jQuery.ajax({
					type:"POST",
					url:"similarite_ajax.php",
					async:true,
					encoding: 'latin1',
					data:{ action:'afficher_onglet_similarite_patient',patient_num:patient_num},
					beforeSend: function(requester){
					},
					success: function(requester){
						var contenu=requester;
						if (contenu=='deconnexion') {
							afficher_connexion("afficher_onglet_similarite_patient('"+patient_num+"')");
						} else {
							document.getElementById("id_textarea_similarite_patient_requete").value=contenu;
						}
					}
				});
			}
		}
		
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
			
			
			jQuery.ajax({
				type:"POST",
				url:"similarite_ajax.php",
				async:true,
				encoding: 'latin1',
				data:{ action:'calculer_similarite_patient',patient_num:patient_num,process_num:process_num,nbpatient_limite:nbpatient_limite,cohort_num_exclure:cohort_num_exclure,limite_count_concept_par_patient_num:limite_count_concept_par_patient_num,requete_exclure:escape(requete_exclure)},
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
						$("#id_tableau_similarite_patient").dataTable( {paging: false, "order": [[ 1, "desc" ]]});
						$("#id_tableau_liste_concepts_patient_similaire").dataTable( {paging: false, "order": [[ 6, "desc" ]]});
					}
				}
			});
		}
		
		function voir_patient_onglet_biologie (onglet) {
			$(".div_result_bio").css("display","none");
			$(".bouton_bio").removeClass("current");
			
			
			document.getElementById('id_div_patient_'+onglet).style.display='block';
			$("#id_bouton_"+onglet).addClass("current");
		}
	
		function inclure_patient (status,patient_num) {
			cohort_num=document.getElementById('id_select_ajouter_patient_cohorte').value;
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:false,
				encoding: 'latin1',
				data:{ action:'inclure_patient',status:status,patient_num:patient_num,cohort_num:cohort_num},
				beforeSend: function(requester){
					jQuery("#id_div_liste_cohortes_patient").html("<img src='images/chargement_mac.gif'>"); 
				},
				success: function(requester){
					var contenu=requester;
					if (contenu=='deconnexion') {
						afficher_connexion("inclure_patient ('"+status+"','"+patient_num+"')");
					} else {
						jQuery("#id_div_liste_cohortes_patient").html(contenu); 
					}
					
				}
			});
		}
		
		
		
		var mouseX;
		var mouseY;
		$(document).mousedown( function(e) {
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
						$('#id_affiche_intersect').css({'top':mouseY,'left':mouseX}).fadeIn('slow');
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
	</script>
			
	<style>
		li.cohorte_1 {
			background-image: url("images/inclure_patient_cohorte.png");
			background-repeat: no-repeat;
			padding-left: 30px;
			font-size: 13px;
			list-style: none;
			margin: 0;    
			height: 24px;
			padding-top: 3px;
		}
		li.cohorte_0 {
			background-image: url("images/noninclure_patient_cohorte.png");
			background-repeat: no-repeat;
			padding-left: 30px;
			font-size: 13px;
			list-style: none;
			margin: 0;    
			height: 24px;
			padding-top: 3px;
		}
		li.cohorte_2 {
			background-image: url("images/doute_patient_cohorte.png");
			background-repeat: no-repeat;
			padding-left: 30px;
			list-style: none;
			margin: 0;
			font-size: 13px;    
			height: 24px;
			padding-top: 3px;
		}
		li.cohorte_3 {
			background-image: url("images/doute_patient_cohorte.png");
			background-repeat: no-repeat;
			padding-left: 30px;
			list-style: none;
			margin: 0;
			font-size: 13px;    
			height: 24px;
			padding-top: 3px;
		}
		table.dataTable thead th, table.dataTable thead td {
		    padding: 1px;
		}
		
		#id_tableau_bilan_biologie {
		    margin-top: -2px;
		}
		
		
		table.dataTable tbody th, table.dataTable tbody td {
		    padding: 2px 2px;
		}
	</style>
	<?
	// $resultat_famille=famille_patient ($patient_num); 
	 
	 $resultat_famille=famille_patient_bis ($patient_num); 
	?>

	<a name="ancre_entete"> </a>
	<div id="tabs" style="width:100%">
		<ul id="tab-links">
			<li class="current color-bullet" id="id_bouton_documents"><span class="li-content"><a href="#" onclick="voir_patient_onglet('documents');return false;"><? print get_translation('DOCUMENTS','Documents'); ?></a></span></li>
			<li class="color-bullet" id="id_bouton_biologie"><span class="li-content"><a href="#" onclick="voir_patient_onglet('biologie');return false;"><? print get_translation('BIOLOGY','Biologie'); ?></a></span></li>
			<? if ($resultat_famille!='') { ?>
				<li class="color-bullet" id="id_bouton_famille"><span class="li-content"><a href="#" onclick="voir_patient_onglet('famille');return false;"><? print get_translation('FAMILY','Famille'); ?></a></span></li>
			<? } ?>
  			<li class="color-bullet" id="id_bouton_timeline"><span class="li-content"><a href="#" onclick="voir_patient_onglet('timeline');return false;"><? print get_translation('TIMELINE','TimeLine'); ?></a></span></li>
			<li class="color-bullet" id="id_bouton_parcours"><span class="li-content"><a href="#" onclick="voir_patient_onglet('parcours');return false;"><? print get_translation('JOURNEY','Parcours'); ?></a></span></li>
			<li class="color-bullet" id="id_bouton_cohorte"><span class="li-content"><a href="#" onclick="voir_patient_onglet('cohorte');return false;"><? print get_translation('COHORT','Cohorte'); ?></a></span></li>
			<li class="color-bullet" id="id_bouton_concepts"><span class="li-content"><a href="#" onclick="voir_patient_onglet('concepts');return false;"><? print get_translation('CONCEPTS','Concepts'); ?></a></span></li>
			<li class="color-bullet" id="id_bouton_similarite_patient"><span class="li-content"><a href="#" onclick="voir_patient_onglet('similarite_patient');return false;"><? print get_translation('SIMILARITY','Similarité'); ?></a></span></li>
		</ul>
	</div>
	<br>
	<table width="100%" id="id_tableau_patient">
		<tr>
		<td width="100%">
			<div id="id_div_patient_documents" class="div_result" style="display:inline;" >
				<input id="id_input_filtre_patient_texte" class="filtre_texte" type="text" value="" size="45" onkeypress="if(event.keyCode==13) {filtre_patient_texte('<? print $patient_num; ?>');}" onkeyup="if(this.value=='') {filtre_patient_texte('<? print "$patient_num"; ?>');}"><input class="form_submit" type="button" value="<? print get_translation('SEARCH','RECHERCHER'); ?>" onclick="filtre_patient_texte('<? print $patient_num; ?>');">
				<table border="0" width="100%">
					<tr>
						<td style="vertical-align:top" width="500px">
							<div id="id_div_tableau_document" style="height:500px;overflow-y:auto;">
								<?
								affiche_liste_document_patient($patient_num,"");
								?>
							</div>
						</td>
						<td style="vertical-align:top">
							<div id="id_div_voir_document">
							
							</div>
						</td>
					</tr>
				</table>
			</div>
			
			<div id="id_div_patient_biologie" class="div_result" style="display:none;" >
				<span class="bouton_bio" id="id_bouton_biologie_cr"><a href="#" onclick="voir_patient_onglet_biologie('biologie_cr');return false;"><? print get_translation('MEDICAL_REPORTS','Comptes Rendus'); ?></a></span> <span class="bouton_bio">-</span> 
				<span class="bouton_bio" id="id_bouton_biologie_tableau"><a href="#" onclick="voir_patient_onglet_biologie('biologie_tableau');ouvrir_tableau_biologie();return false;"><? print get_translation('TABLE','Tableau'); ?></a></span> <span class="bouton_bio">-</span> 
				<span class="bouton_bio" id="id_bouton_biologie_code"><a href="#" onclick="voir_patient_onglet_biologie('biologie_code');return false;"><? print get_translation('CURVES','Courbes'); ?></a></span>
				<br>
				<br>
				<div id="id_div_patient_biologie_cr" class="div_result_bio" style="display:block;" >
					<input id="id_input_filtre_patient_texte_biologie" class="filtre_texte" type="text" value="" size="45" onkeypress="if(event.keyCode==13) {filtre_patient_texte_biologie('<? print $patient_num; ?>');}" onkeyup="if(this.value=='') {filtre_patient_texte_biologie('<? print "$patient_num"; ?>');}"><input class="form_submit" type="button" value="<? print get_translation('SEARCH','RECHERCHER'); ?>" onclick="filtre_patient_texte_biologie('<? print $patient_num; ?>');">
					<table border="0" width="100%">
						<tr>
							<td style="vertical-align:top" width="500px">
								<div id="id_div_tableau_biologie" style="height:500px;overflow-y:auto;">
									<?
									affiche_liste_document_biologie($patient_num,"");
									?>
								</div>
							</td>
							<td style="vertical-align:top">
								<div id="id_div_voir_biologie">
								</div>
							</td>
						</tr>
					</table>
				</div>
				
				<div id="id_div_patient_biologie_tableau" class="div_result_bio" style="display:none;" >
						<?
						affiche_tableau_biologie($patient_num);
						?>
				</div>
				
				<div id="id_div_patient_biologie_code" class="div_result_bio" style="display:none;" >
					
					<table border="0" width="100%">
						<tr>
							<td style="vertical-align:top" width="500px">
								<div id="id_div_tableau_biologie_code" style="height:500px;overflow-y:auto;">
									<?
									//affiche_liste_code_biologie($patient_num);
									?>
								</div>
							</td>
							<td style="vertical-align:top">
								<div id="id_div_voir_biologie_code">
								</div>
							</td>
						</tr>
					</table>
				</div>
			</div>
			
			<div id="id_div_patient_timeline" class="div_result" style="display:none;" >
				<iframe id="iframe_lignevie" height="600px" width="600px" name="lignevie" src="javascript:var x;"></iframe>
				<br>
			</div>
			
			<div id="id_div_patient_famille" class="div_result" style="display:none;" >
				<h2>Famille</h2>
				<div style="width:800px">
					<? print $resultat_famille; ?>
				</div>
			</div>
			
			<div id="id_div_patient_parcours" class="div_result" style="display:none;" >
				<h2><? print get_translation('JOURNEY','Parcours'); ?></h2>
				<div style="width:800px">
					<? parcours_patient ($patient_num); ?>
				</div>
			</div>
			
			<div id="id_div_patient_cohorte" class="div_result" style="display:none;" >
				<h2><? print get_translation('COHORTS_OF_PATIENT','Cohortes de ce patient'); ?></h2>
				<div id="id_div_liste_cohortes_patient">
					<? lister_cohorte_un_patient($patient_num); ?>
				</div>
				
				<h2><? print get_translation('INCLUDE_EXCLUDE_PATIENT_FROM_COHORT',"Inclure / Exclure ce patient d'une cohorte"); ?></h2>
				
				<? print get_translation('SELECT_A_COHORT','Sélectionner une cohorte'); ?> : <select id="id_select_ajouter_patient_cohorte">
				<? lister_mes_cohortes_ajouter_patient($user_num_session); ?>
				</select>
				&nbsp;&nbsp;&nbsp;
				<a onclick="inclure_patient(1,'<? print $patient_num; ?>');return false;" href="#">
					<img width="20px" border="0" align="absmiddle" src="images/inclure_patient_cohorte.png" alt="<? print get_translation('INCLUDE_PATIENT','Inclure le patient'); ?>" title="<? print get_translation('INCLUDE_PATIENT','Inclure le patient'); ?>">
				</a>&nbsp;&nbsp;&nbsp;
				<a onclick="inclure_patient(0,'<? print $patient_num; ?>');return false;" href="#">
					<img width="20px" border="0" align="absmiddle" src="images/noninclure_patient_cohorte.png" alt="<? print get_translation('EXCLUDE_PATIENT','Exclure le patient'); ?>" title="<? print get_translation('EXCLUDE_PATIENT','Exclure le patient'); ?>">
				</a>&nbsp;&nbsp;&nbsp;
				<a onclick="inclure_patient(2,'<? print $patient_num; ?>');return false;" href="#">
					<img width="20px" border="0" align="absmiddle" src="images/doute_patient_cohorte.png" alt="<? print get_translation('PUT_PATIENT_IN_STANDBY','Mettre en attente le patient'); ?>" title="<? print get_translation('PUT_PATIENT_IN_STANDBY','Mettre en attente le patient'); ?>">
				</a>
			</div>
			
			<div id="id_div_patient_concepts" class="div_result" style="display:none;" >
			</div>
			
			
			<div id="id_div_patient_similarite_patient" class="div_result" style="display:none;" >
				
				<input class="bouton_sauver" type="button" onclick="calculer_similarite_patient('<? print $patient_num; ?>');" value="<? print get_translation('FIND_SIMILAR_PATIENTS','Trouver les patients similaires'); ?>"> 
				<input class="bouton_sauver" type="button" onclick="plier_deplier('id_div_patient_similarite_patient_options');" value="<? print get_translation('OPTIONS','Options'); ?>"><br>
				<div id="id_div_patient_similarite_patient_options" style="display:none;" >
					<? $process_num=uniqid(); ?>
					<input type="hidden" id="id_process_num" value="<? print $process_num; ?>">
					<? print get_translation('LIMIT_TO_PATIENTS_WITH_TERMS_IN_DOCUMENTS','Limiter aux patients avec ces termes dans les comptes rendus');?>:  <input type="text" size="50" id="id_textarea_similarite_patient_requete"><input type="button" onclick="precalcul_nb_patient_similarite_patient('<? print $patient_num; ?>');" value="<? print get_translation('PRECOMPUTE','Pré-calculer'); ?>"><? print get_translation('COUNT_PATIENT_NUMBER_SHORT','Nb patients'); ?> : <span id="id_span_precalcul_nb_patient_similarite_patient"></span><br>
					<? print get_translation('EXCLUDE_PATIENT_WITH_TERMS_IN_CASE_REPORT','Exclure les patients avec ces termes dans les comptes rendus');?> :  <input type="text" size="50" id="id_input_requete_exclure"><br>
					<? print get_translation('COUNT_MINIMUM_CONCEPTS_PER_PATIENT','Nb minimum de concepts par patient'); ?> : <input type="text" size="3" id="id_input_limite_count_concept_par_patient_num" value="10"><br>
					<? print get_translation('LIMIT_NUMBER_SIMILAR_PATIENTS','Limite nombre de patients similaires'); ?> : <input type="text" size="3" id="id_input_nbpatient_limite" value="20"><br>
					<? print get_translation('EXCLUDE_RESULT_COHORT','Exclure cette cohorte des résultats'); ?> : <select id=id_cohort_num_exclure class="chosen-select">
					<?	
						lister_mes_cohortes_option ($user_num_session,'id_option_similarite_cohorte_') 
					?>
					</select><br>
				</div>
			
				<div id="id_div_patient_similarite_patient_resultat" style="display:block;" >
				</div>
				<div id="id_affiche_intersect" style="width:300px;height:500px;border:1px black solid;display:none;overflow-y: auto;position:absolute;background-color:white;"></div>
			</div>
		
		</td></tr>
	</table>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<script language=javascript>
	function ouvrir_tableau_biologie() {
		if ( $.fn.dataTable.isDataTable( '#id_tableau_bilan_biologie' ) ) {
		} else {
			var widthwindow= $(window).width()-20;
			var widthdiv=$('#id_tableau_bilan_biologie').width()+40;
			height=$(window).height()-80;

			if ($('#id_tableau_bilan_biologie').width() < widthwindow) {
				$('#id_div_patient_biologie_tableau').width(widthdiv);
				 var oTable = $('#id_tableau_bilan_biologie').DataTable( {
				        "scrollY": height+"px",
						"scrollX":  false,
				        "scrollCollapse": false,
				        "paging": false,
					  	"bSort": false
				        
				    } );
			} else {
				$('#id_div_patient_biologie_tableau').width(widthwindow);
				 var oTable = $('#id_tableau_bilan_biologie').DataTable( {
				        "scrollY": height+"px",
						"scrollX":  scrollX,
				        "scrollCollapse": true,
				        "paging": false,
					  	"bSort": false
				        
				    } );
			    new $.fn.dataTable.FixedColumns( oTable , { leftColumns: 1} );
				$(".DTFC_LeftBodyLiner").css("overflow","hidden");
				//$( ".dataTables_scrollBody" ).scrollLeft( $('#id_tableau_bilan_biologie').width() );
				//jQuery('.dataTable').wrap('<div class="dataTables_scroll" />');
				var widthcol_examen=$('.class_libelle_examen').width()+20;
				$('#id_colonne_examen').css('width',widthcol_examen);
				$('.class_libelle_examen').css('width',widthcol_examen);
			}
		}
	} 
</script>
	<? } ?>
<style>
	.dataTables_scrollHead {position:static;}
</style>
<? save_log_page($user_num_session,'patient'); ?>
<? include "foot.php"; ?>