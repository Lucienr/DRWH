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

	function valider_formulaire_ajouter_ecrf () {
		if (document.getElementById('id_ajouter_titre_ecrf').value=='') {
			alert ("  <? print get_translation('JS_MANDATORY_TITLE','le titre est obligatoire'); ?> ");
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
					document.getElementById('id_tableau_liste_user_ecrf').innerHTML=requester;
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
						document.getElementById('id_tableau_liste_user_ecrf').innerHTML=requester;
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
						document.getElementById('id_div_liste_ecrf').innerHTML=requester;
						document.getElementById('id_div_ma_ecrf').innerHTML='';
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
		title_ecrf=$('#id_input_titre_ecrf').val();
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
					document.getElementById('id_div_liste_ecrf').innerHTML=requester;
				 	jQuery('#id_tableau_liste_ecrf').dataTable( { 
							paging: false,
							"order": [[ 1, "asc" ]],
							   "bInfo" : false,
							   "bDestroy" : true
					} );
					document.getElementById('id_sous_span_titre_ecrf').innerHTML=title_ecrf;
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
		description_ecrf=document.getElementById('id_textarea_description_ecrf_modifier').value;
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
					document.getElementById('id_div_ecrf_description').innerHTML=requester;
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
	
			
	function voir_ecrf_onglet (onglet) {
		$(".div_result").css("display","none");
		$(".color-bullet").removeClass("current");
		
		document.getElementById('id_div_ecrf_'+onglet).style.display='inline';
		$("#id_bouton_"+onglet).addClass("current");
	}
	
	function importer_item_ecrf (ecrf_num) {
		list_item=document.getElementById("id_textarea_importer_item").value;
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
	
	function modify_ecrf_item(ecrf_item_num,variable) {
		$("#id_span_"+variable+"_"+ecrf_item_num).css("display","none");
		$("#id_span_modif_"+variable+"_"+ecrf_item_num).css("display","block");
		$("#id_input_"+variable+"_"+ecrf_item_num).focus();
	}
	
	function save_ecrf_item(ecrf_num,ecrf_item_num,variable) {
		valeur=document.getElementById("id_input_"+variable+"_"+ecrf_item_num).value;
		jQuery.ajax({
			type:"POST",
			url:"ajax_ecrf.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'save_ecrf_item',ecrf_num:ecrf_num,ecrf_item_num:ecrf_item_num,variable:variable,valeur:escape(valeur)},
			beforeSend: function(requester){
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion("save_ecrf_item ('"+ecrf_num+"','"+ecrf_item_num+"','"+variable+"')");
				} else {
					document.getElementById("id_span_"+variable+"_"+ecrf_item_num).innerHTML=valeur;
					$("#id_span_"+variable+"_"+ecrf_item_num).css("display","block");
					$("#id_span_modif_"+variable+"_"+ecrf_item_num).css("display","none");
				}
			}
		});
	}
	
	function supprimer_item_ecrf(ecrf_item_num,ecrf_num) {
		if (confirm ('Etes vous sûr de vouloir supprimer cet item ? ')) {
			jQuery.ajax({
				type:"POST",
				url:"ajax_ecrf.php",
				async:true,
				encoding: 'latin1',
				data:{ action:'supprimer_item_ecrf',ecrf_num:ecrf_num,ecrf_item_num:ecrf_item_num},
				beforeSend: function(requester){
				},
				success: function(requester){
					var contenu=requester;
					if (contenu=='deconnexion') {
						afficher_connexion("supprimer_item_ecrf ('"+ecrf_num+"','"+ecrf_item_num+"')");
					} else {
						jQuery("#id_list_item").html(contenu); 
					}
				}
			});
		}
	}
	
	function add_new_item (ecrf_num) {
		
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
						jQuery("#id_list_item").html(contenu); 
					}
				}
			});
	}
	
</script>