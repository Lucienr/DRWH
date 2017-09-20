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

var tableau_table_cree_type_labo='';

function affiche_onglet_labo (tmpresult_num) {
	if (tableau_table_cree_type_labo=='') {
		visualiser_tableau_all_exam(tmpresult_num);
		tableau_table_cree_type_labo='ok';
	}
}


function rechercher_code_labo () {
	requete_texte=document.getElementById('id_rechercher_code_labo').value;
	tmpresult_num=document.getElementById('id_num_temp').value;
	document.getElementById('id_div_resultat_recherche_code_labo').innerHTML="<img src=images/chargement_mac.gif>";
	if (requete_texte!='') {
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'rechercher_code_labo',requete_texte:escape(requete_texte),tmpresult_num:tmpresult_num},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion("rechercher_code_labo ()");
				} else { 
					document.getElementById('id_div_resultat_recherche_code_labo').innerHTML=requester;
				}
			},
			complete: function(requester){
			},
			error: function(){
					}
		});
	} else {
		document.getElementById('id_div_resultat_recherche_code_labo').innerHTML="";
	}
}


function rechercher_code_sous_thesaurus_labo (thesaurus_data_father_num,sans_filtre) {
	requete_texte=document.getElementById('id_rechercher_code_labo').value;
	id='id_div_thesaurus_sous_data_labo_'+thesaurus_data_father_num;
	tmpresult_num=document.getElementById('id_num_temp').value;
	if (document.getElementById(id)) {
		if (document.getElementById(id).style.display=="none") {
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:true,
				encoding: 'latin1',
				data:{ action:'rechercher_code_labo',requete_texte:escape(requete_texte),thesaurus_data_father_num:thesaurus_data_father_num,sans_filtre:sans_filtre,tmpresult_num:tmpresult_num},
				beforeSend: function(requester){
					document.getElementById('plus_'+id).innerHTML="-";
					document.getElementById(id).style.display="block";
					document.getElementById(id).innerHTML="<img src=images/chargement_mac.gif>";
				},
				success: function(requester){
					if (requester=='deconnexion') {
						afficher_connexion("rechercher_code_sous_thesaurus_labo ('"+thesaurus_data_father_num+"','"+sans_filtre+"')");
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

function visualiser_data_labo (thesaurus_data_num) {
	if (thesaurus_data_num!='') {
		visualiser_graph_scatterplot (thesaurus_data_num);
		visualiser_tableau_groupe (thesaurus_data_num);
	} else {
	}
}

function visualiser_tableau_groupe (thesaurus_data_num) {
	tmpresult_num=document.getElementById('id_num_temp').value;
	if (thesaurus_data_num!='') {
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'visualiser_tableau_groupe',thesaurus_data_num:thesaurus_data_num,tmpresult_num:tmpresult_num},
			beforeSend: function(requester){
				$('#id_div_resultat_tableau_groupe').empty();
				document.getElementById('id_div_resultat_tableau_groupe').innerHTML="<img src=images/chargement_mac.gif>";
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion("visualiser_tableau_groupe ('"+thesaurus_data_num+"')");
				} else { 
					$('#id_div_resultat_tableau_groupe').html(requester);
					$("#id_tableau_visualiser_tableau_groupe").dataTable( {paging: false});
				}
			},
			complete: function(requester){
			},
			error: function(){}
		});
	} else {
	}
}
function visualiser_tableau_all_exam (thesaurus_data_num) {
	tmpresult_num=document.getElementById('id_num_temp').value;
	if (thesaurus_data_num!='') {
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'visualiser_tableau_all_exam',tmpresult_num:tmpresult_num},
			beforeSend: function(requester){
				$('#id_div_resultat_tableau_all_exam').empty();
				document.getElementById('id_div_resultat_tableau_all_exam').innerHTML="<img src=images/chargement_mac.gif>";
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion("visualiser_tableau_all_exam ('"+thesaurus_data_num+"')");
				} else { 
					$('#id_div_resultat_tableau_all_exam').html(requester);
					$("#id_tableau_visualiser_tableau_all_exam").dataTable( {paging: false});
				}
			},
			complete: function(requester){
			},
			error: function(){}
		});
	} else {
	}
}

function visualiser_graph_scatterplot (thesaurus_data_num) {
	tmpresult_num=document.getElementById('id_num_temp').value;
	if (thesaurus_data_num!='') {
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'visualiser_graph_scatterplot',thesaurus_data_num:thesaurus_data_num,tmpresult_num:tmpresult_num},
			beforeSend: function(requester){
				$('#id_visualiser_graph_scatterplot').empty();
				document.getElementById('id_visualiser_graph_scatterplot').innerHTML="<img src=images/chargement_mac.gif>";
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion("visualiser_graph_scatterplot ('"+thesaurus_data_num+"')");
				} else { 
					eval(requester);
				}
			},
			complete: function(requester){
			},
			error: function(){
					}
		});
	} else {
	}
}
