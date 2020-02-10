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


var id_tr_selectionne;
function afficher_document_patient (document_num,id_voir,id_query) {
//	jQuery(".tr_document_patient").css("backgroundColor","#ffffff");
	jQuery(".tr_document_patient").css("fontWeight","normal");
	jQuery(".tr_document_patient").css("color","black");
	
	
	jQuery(".id_document_patient_"+document_num).css("fontWeight","bold");
	jQuery(".id_document_patient_"+document_num).css("color","#CB1B3E");
//	jQuery(".id_document_patient_"+document_num).css("backgroundColor","#dcdff5");
	id_tr_selectionne="id_document_patient_"+document_num;
	requete=jQuery('#'+id_query).val(); //id_input_filtre_patient_text
	requete=requete.replace(/\+/g,';plus;');
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'afficher_document_patient',document_num:document_num,requete:escape(requete)},
		beforeSend: function(requester){
			jQuery("#".id_voir).html("<img src='images/chargement_mac.gif'>"); 
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("afficher_document_patient ('"+document_num+"','"+id_voir+"')");
				jQuery("#"+id_voir).html(""); 
			} else {
				jQuery("#"+id_voir).html(contenu); 
				jQuery("#"+id_voir).css("top","0px"); 
				jQuery("#"+id_voir).css("display","block"); 
				//window.location='#ancre_entete';
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
	requete=document.getElementById('id_input_filtre_patient_text_biology').value;
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

jQuery(document).keydown(function(e) {
	id_tr_document='1';
    if (e.which ==38) {
    	tr_precede=jQuery("#"+id_tr_selectionne ).prev( "tr" );
    	id_tr_document=jQuery(tr_precede).attr('id');
    	sousgroupe=jQuery(tr_precede).attr('sousgroupe');
    }
    if (e.which ==40) {
    	tr_precede=jQuery("#"+id_tr_selectionne ).next( "tr" );
    	id_tr_document=jQuery(tr_precede).attr('id');
    	sousgroupe=jQuery(tr_precede).attr('sousgroupe');
    }
	if (id_tr_document!='1') {
		if (id_tr_document) {
		    	document_num=id_tr_document.replace("id_document_patient_","");
			if (document_num) {
				if (sousgroupe=='text') {
			    		afficher_document_patient (document_num,'id_div_voir_document');
			    	}
				if (sousgroupe=='biologie') {
			    		afficher_document_patient_biologie (document_num);
			    	}
		    	} 
		}
	} 
});


function filtre_patient_texte (patient_num) {
	requete=jQuery('#id_input_filtre_patient_text').val();
	requete=requete.replace(/\+/g,';plus;');
	jQuery.ajax({
		type:"POST",
		url:"ajax_patient.php",
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
	requete=document.getElementById('id_input_filtre_patient_text_biology').value;
	requete=requete.replace(/\+/g,';plus;');
	jQuery.ajax({
		type:"POST",
		url:"ajax_patient.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'filtre_patient_texte_biologie',requete:escape(requete),patient_num:patient_num},
		beforeSend: function(requester){
			jQuery("#id_div_list_document_biologie").html("<img src='images/chargement_mac.gif'>"); 
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("filtre_patient_texte_biologie ('"+patient_num+"')");
			} else {
				jQuery("#id_div_list_document_biologie").html(contenu); 
			}
		}
	});
}
var tableau_onglet_deja_ouvert=new Array();
function voir_patient_onglet (onglet,patient_num) {
	jQuery(".div_result").css("display","none");
	jQuery(".color-bullet").removeClass("current");
	jQuery('#id_div_patient_'+onglet).css('display','inline');
	//document.getElementById('id_div_patient_'+onglet).style.display='inline';
	jQuery("#id_bouton_"+onglet).addClass("current");
	
	
	if (onglet=='biologie' && tableau_onglet_deja_ouvert[onglet]!='ok') {
		voir_patient_onglet_biologie('biologie_cr',patient_num);
	}
	
	if (onglet=='concepts' && tableau_onglet_deja_ouvert[onglet]!='ok') {
		afficher_onglet_concept_patient(patient_num);
	}
	if (onglet=='timeline' && tableau_onglet_deja_ouvert[onglet]!='ok') {
		document.getElementById('iframe_lignevie').src="include_lignevie.php?patient_num="+patient_num;
	}
	if (onglet=='similarite_patient' && tableau_onglet_deja_ouvert[onglet]!='ok') {
		afficher_onglet_similarite_patient(patient_num);
	}
	
	if (onglet=='ecrf_patient'  && tableau_onglet_deja_ouvert[onglet]!='ok') {
		afficher_onglet_ecrf_patient(patient_num,'');
	}
	
	tableau_onglet_deja_ouvert[onglet]='ok';
	largeur=jQuery("#id_tableau_patient").css("width");
	jQuery("#id_div_patient_timeline").css("width",largeur);
	
	var isIE = (document.all && !window.opera)?true:false;
	width=isIE  ? window.document.compatMode == "CSS1Compat" ? document.documentElement.clientWidth : document.body.clientWidth : window.innerWidth;
	width=width-50;
	jQuery("#iframe_lignevie").css("width",width);
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
	if (tableau_onglet_deja_ouvert['concepts_resume']!='ok') {
		tableau_onglet_deja_ouvert['concepts_resume']='ok';
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
	if (tableau_onglet_deja_ouvert['similarite_patient']!='ok') {
		tableau_onglet_deja_ouvert['similarite_patient']='ok';
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


function voir_patient_onglet_biologie (onglet,patient_num) {
	jQuery(".div_result_bio").css("display","none");
	jQuery(".bouton_bio").removeClass("current");
	jQuery('#id_div_patient_'+onglet).css('display','block');
	jQuery("#id_bouton_"+onglet).addClass("current");
	
	if (onglet=='biologie_cr' && jQuery("#id_div_list_document_biologie").html()=='') {
		jQuery.ajax({
			type:"POST",
			url:"ajax_patient.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'display_biological_document_list',patient_num:patient_num},
			beforeSend: function(requester){
				jQuery("#id_div_list_document_biologie").html("<img src='images/chargement_mac.gif'>"); 
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("voir_patient_onglet_biologie ('"+onglet+"','"+patient_num+"')");
				} else {
					jQuery("#id_div_list_document_biologie").html(contenu); 
				}
			}
		});
	}	
	if (onglet=='biologie_tableau' && jQuery("#id_div_patient_biologie_tableau").html()=='') {
		jQuery.ajax({
			type:"POST",
			url:"ajax_patient.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'display_biological_table',patient_num:patient_num},
			beforeSend: function(requester){
				jQuery("#id_div_patient_biologie_tableau").html("<img src='images/chargement_mac.gif'>"); 
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("voir_patient_onglet_biologie ('"+onglet+"','"+patient_num+"')");
				} else {
					jQuery("#id_div_patient_biologie_tableau").html(contenu); 
					customize_tableau_biologie();
				}
			}
		});
	}	
}

function customize_tableau_biologie_old() {
	if ( $.fn.dataTable.isDataTable( '#id_tableau_bilan_biologie' ) ) {
	} else {
		var widthwindow= jQuery(window).width()-20;
		var widthdiv=jQuery('#id_tableau_bilan_biologie').width()+40;
		height=jQuery(window).height()-80;

		if (jQuery('#id_tableau_bilan_biologie').width() < widthwindow) {
			jQuery('#id_div_patient_biologie_tableau').width(widthdiv);
			 var oTable = jQuery('#id_tableau_bilan_biologie').DataTable( {
			        "scrollY": height+"px",
					"scrollX":  false,
			        "scrollCollapse": false,
			        "paging": false,
				  	"bSort": false
			        
			    } );
		} else {
			jQuery('#id_div_patient_biologie_tableau').width(widthwindow);
			 var oTable = jQuery('#id_tableau_bilan_biologie').DataTable( {
			        "scrollY": height+"px",
					"scrollX":  scrollX,
			        "scrollCollapse": true,
			        "paging": false,
				  	"bSort": false
			        
			    } );
		    new $.fn.dataTable.FixedColumns( oTable , { leftColumns: 1} );
			jQuery(".DTFC_LeftBodyLiner").css("overflow","hidden");
			//jQuery( ".dataTables_scrollBody" ).scrollLeft( jQuery('#id_tableau_bilan_biologie').width() );
			//jQuery('.dataTable').wrap('<div class="dataTables_scroll" />');
			var widthcol_examen=jQuery('.class_libelle_examen').width()+20;
			jQuery('#id_colonne_examen').css('width',widthcol_examen);
			jQuery('.class_libelle_examen').css('width',widthcol_examen);
		}
	}
} 

function customize_tableau_biologie() {
	if ( $.fn.dataTable.isDataTable( '#id_tableau_bilan_biologie' ) ) {
	} else {
		var widthwindow= $(window).width()-20;
		var widthdiv=$('#id_tableau_bilan_biologie').width()+40;
		if ($('#id_tableau_bilan_biologie').width() < widthwindow) {
			$('#id_div_patient_biologie_tableau').width(widthdiv);
		} else {
			$('#id_div_patient_biologie_tableau').width(widthwindow);
		}
		//if ($(window).height()<700) {
		//	height=700;
		//} else {
			height=$(window).height()-80;
		//}
		
		var widthcol_examen=$('#id_colonne_examen').width();
		$('#id_colonne_examen').css('width',widthcol_examen);
		$('.class_libelle_examen').css('width',widthcol_examen);
		
		 var oTable = $('#id_tableau_bilan_biologie').DataTable( {
		        "scrollY": height+"px",
			"scrollX":        true,
		        "scrollCollapse": true,
		        "paging": false,
			  "bSort": false
		        
		    } );
		    new $.fn.dataTable.FixedColumns( oTable , { leftColumns: 1} );
			$(".DTFC_LeftBodyLiner").css("overflow","hidden");
			//$( ".dataTables_scrollBody" ).scrollLeft( $('#id_tableau_bilan_biologie').width() );
			//jQuery('.dataTable').wrap('<div class="dataTables_scroll" />');
			
	}
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




		
function select_history_query_patient (patient_num) {
	jQuery.ajax({
		type:"POST",
		url:"ajax_patient.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'select_history_query_patient',patient_num:patient_num},
		beforeSend: function(requester){},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion("list_regexp_select ('"+patient_num+"')");
			} else { 
				jQuery('#id_select_history_query_patient').find('option').remove().end();
				jQuery('#id_select_history_query_patient').append(requester);
				jQuery("#id_select_history_query_patient").trigger("chosen:updated");
			}
		},
		complete: function(requester){},
		error: function(){}
	});
}
		
function chose_history_query_patient (patient_num) {
	jQuery('#id_input_filtre_patient_text').val(jQuery('#id_select_history_query_patient').val());
	filtre_patient_texte(patient_num);
}


function afficher_parcours (id) {
	$('#id_lien_department').css('font-weight','normal');
	$('#id_lien_unit').css('font-weight','normal');
	$('#id_lien_complet_department').css('font-weight','normal');
	$('#id_lien_complet_unit').css('font-weight','normal');		
	$('#id_lien_'+id).css('font-weight','bold');
	
	$('#id_div_img_parcours_department').css('display','none');
	$('#id_div_img_parcours_unit').css('display','none');
	$('#id_div_img_parcours_complet_department').css('display','none');
	$('#id_div_img_parcours_complet_unit').css('display','none');
	$('#id_div_img_parcours_'+id).css('display','block');
}

		
function process_parcours (patient_num) {
	jQuery.ajax({
		type:"POST",
		url:"ajax_patient.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'process_parcours',patient_num:patient_num},
		beforeSend: function(requester){
			jQuery("#id_div_process_parcours_patient").html("<img src='images/chargement_mac.gif'>"); 
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion("process_parcours ('"+patient_num+"')");
			} else { 
				jQuery("#id_div_process_parcours_patient").html(requester); 
			}
		},
		complete: function(requester){},
		error: function(){}
	});
}

		
function process_pmsi_patient (patient_num) {
	jQuery.ajax({
		type:"POST",
		url:"ajax_patient.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'process_pmsi_patient',patient_num:patient_num},
		beforeSend: function(requester){
			jQuery("#id_div_process_pmsi_patient").html("<img src='images/chargement_mac.gif'>"); 
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion("process_pmsi_patient ('"+patient_num+"')");
			} else { 
				jQuery("#id_div_process_pmsi_patient").html(requester); 
			}
		},
		complete: function(requester){},
		error: function(){}
	});
}

function display_sentence_with_term (patient_num,list_document_num,concept_str,id) {
	jQuery.ajax({
		type:"POST",
		url:"ajax_patient.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'display_sentence_with_term',patient_num:patient_num,list_document_num:list_document_num,concept_str:concept_str},
		beforeSend: function(requester){
			jQuery("#"+id).html("<img src='images/chargement_mac.gif'>"); 
		},
		success: function(requester){
			if (requester=='deconnexion') {
				afficher_connexion("display_sentence_with_term ('"+patient_num+"','"+list_document_num+"','"+concept_str+"','"+id+"')");
			} else { 
				jQuery("#"+id).html(requester); 
			}
		},
		complete: function(requester){},
		error: function(){}
	});
}


