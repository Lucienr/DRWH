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
var type_en_cours_concepts='document';
var distance_en_cours_concepts='';
var distance_origine_concepts;
var num_temp_concepts;
var type_concepts;

function affiche_onglet_concepts (tmpresult_num,type,distance) {
	if (type=='') {
		type=type_en_cours_concepts;
	} else {
		type_en_cours_concepts=type;
	}
	if (distance_en_cours_concepts=='') {
		distance_en_cours_concepts=document.getElementById("id_div_distance_concepts").innerHTML;
	}
	if (distance=='') {
		distance=distance_en_cours_concepts;
	} else {
		distance_en_cours_concepts=distance;
	}
	num_temp_concepts=tmpresult_num;
	
	$("#slider-range-max_concepts").slider('value', distance);
	document.getElementById("id_div_distance_concepts").innerHTML=distance;
	
	if (type=='document') {
		jQuery('#id_bouton_concepts_document').children('a').css('color','#e00adb');
		jQuery('#id_bouton_concepts_patient').children('a').css('color','#036ba5');
		jQuery('#id_radio_concepts_document').prop('checked',true);
		jQuery('#id_radio_concepts_patient').prop('checked',false);
	}
	if (type=='patient') {
		jQuery('#id_bouton_concepts_document').children('a').css('color','#036ba5');
		jQuery('#id_bouton_concepts_patient').children('a').css('color','#e00adb');
		jQuery('#id_radio_concepts_document').prop('checked',false);
		jQuery('#id_radio_concepts_patient').prop('checked',true);
	}
	
	affiche_concepts(tmpresult_num,"phenotype","pref",type,distance);
	
//	affiche_nuage_concepts ('id_div_nuage_signes',tmpresult_num,filtre_type_semantic_pheno,"pref",type,distance);
//	affiche_tableau_concepts ('id_tableau_signes',tmpresult_num,filtre_type_semantic_pheno,"pref",type,distance);
//	affiche_graph_concepts ('id_concepts_signes',tmpresult_num,filtre_type_semantic_pheno,"pref",type,distance);
//	affiche_heatmap_concepts ('id_div_heatmap_concept',tmpresult_num,"and semantic_type in ('Sign or Symptom','Physiologic Function','Finding','Disease or Syndrome','Pathologic Function','Congenital Abnormality','Anatomical Abnormality','Neoplastic Process','Acquired Abnormality')","pref",type,distance);
}



function concepts_sub_menu (id) {
	test_ouvrir='';
	if (jQuery("#"+id).css('display')=='none') {
		test_ouvrir='ok';
	}
	plier('id_div_filter_the_concepts');
	plier('id_div_export_concepts_below');
	jQuery("#button_id_div_filter_the_concepts").css('backgroundColor','grey');
	jQuery("#button_id_div_export_concepts_below").css('backgroundColor','grey');
	
	if (test_ouvrir=='ok') {
		plier_deplier(id);
		jQuery("#button_"+id).css('backgroundColor','#00b2d7');
	} 
}

function concepts_sub_menu_hover (id) {
	if (jQuery("#"+id).css('display')=='none') {
		jQuery("#button_"+id).css('backgroundColor','#00b2d7');
	}
}

function concepts_sub_menu_out (id) {
	if (jQuery("#"+id).css('display')=='none') {
		jQuery("#button_"+id).css('backgroundColor','grey');
	}
}


var table_concepts=new Array();
var tableau_table_cree=new Array();
var tableau_table_cree_type=new Array();

function filter_concept_by_age(tmpresult_num) {	
	affiche_concepts (tmpresult_num,"phenotype","pref","","");
}

function reset_filter_concept_by_age(tmpresult_num) {
	jQuery('#id_age_concept_min').val("");
	jQuery('#id_age_concept_max').val("");
	filter_concept_by_age(tmpresult_num);
}

function affiche_concepts (tmpresult_num,phenotype_genotype,donnees_reelles_ou_pref,type,distance) {
	if (type=='' && type_en_cours_concepts!='') {
		type=type_en_cours_concepts;
	}
	if (distance=='' && distance_en_cours_concepts!='') {
		distance=distance_en_cours_concepts;
	} 
	age_concept_min='';
	age_concept_max='';
	
	age_concept_min=jQuery('#id_age_concept_min').val();
	age_concept_max=jQuery('#id_age_concept_max').val();
	
	if (tableau_table_cree_type['general']!=type+distance+age_concept_min+age_concept_max) {
		tableau_table_cree_type['general']=type+distance+age_concept_min+age_concept_max;
		jQuery.ajax({
			type:"GET",
			url:"ajax.php",
			method: 'get',
			async:true,
			contentType: 'application/x-www-form-urlencoded',
			encoding: 'latin1',
			data: {action:'affiche_concepts',tmpresult_num:tmpresult_num,phenotype_genotype:phenotype_genotype,donnees_reelles_ou_pref:donnees_reelles_ou_pref,type:type,distance:distance,age_concept_min:age_concept_min,age_concept_max:age_concept_max},
			beforeSend: function(requester){
				document.getElementById('id_div_nuage_signes').innerHTML="<img src=\"images/chargement_mac.gif\">";
				document.getElementById('gauche_id_concepts_signes').innerHTML="<img src=\"images/chargement_mac.gif\">";
				document.getElementById('droite_id_concepts_signes').innerHTML="<img src=\"images/chargement_mac.gif\">";
				if (tableau_table_cree['general']=='ok') {
					table_concepts['id_tableau_signes'].destroy();
				}
				document.getElementById('id_tableau_signes').innerHTML="";
			},
			success: function(requester){
				if (requester=='deconnexion') {
					document.getElementById('id_div_nuage_signes').innerHTML="";
					document.getElementById('gauche_id_concepts_signes').innerHTML="";
					document.getElementById('droite_id_concepts_signes').innerHTML="";
					afficher_connexion("");
				} else { 
					json_general=requester;
					if (json_general!='') {
						tableau_data=json_general.split(';separateur_general;');
						json_tableau=tableau_data[0];
						json_cloud=tableau_data[1];
						json_graph=tableau_data[2];
						
						affiche_nuage_concepts_direct('id_div_nuage_signes',json_cloud);
						affiche_tableau_concepts_direct('id_tableau_signes',json_tableau);
						affiche_graph_concepts_direct('id_concepts_signes',json_graph);
						
					}
				}
			},
			error: function(){}
		});
	}
}

function export_concepts (tmpresult_num) {
	type=type_en_cours_concepts;
	distance=distance_en_cours_concepts;
	age_concept_min='';
	age_concept_max='';
	age_concept_min=jQuery('#id_age_concept_min').val();
	age_concept_max=jQuery('#id_age_concept_max').val();
	window.open("export_concepts.php?type_export=agregated_concept&tmpresult_num="+tmpresult_num+"&phenotype_genotype=phenotype&donnees_reelles_ou_pref=pref&type="+type+"&distance="+distance+"&age_concept_min="+age_concept_min+"&age_concept_max="+age_concept_max+"",'width=710,height=555,left=160,top=170');
}

function export_concepts_patient (tmpresult_num) {
	type=type_en_cours_concepts;
	distance=distance_en_cours_concepts;
	window.open("export_concepts.php?type_export=detail_patient_concept&tmpresult_num="+tmpresult_num+"&phenotype_genotype=phenotype&donnees_reelles_ou_pref=pref&type="+type+"&distance="+distance+"",'width=710,height=555,left=160,top=170');
}

function actualiser_graph_concepts (distance) {
	if (distance==distance_origine_concepts) {
		affiche_onglet_concepts (num_temp_concepts,'',distance);
	}
}
function affiche_nuage_concepts_direct (id,data) {
	var liste_mot=data;
	document.getElementById(id).innerHTML='';
	eval ("var liste_mot=["+liste_mot+"]");
	jQuery("#"+id).jQCloud(liste_mot, {removeOverflowing :true});
}
//<br>100*nbfois_ce_code_dans_le_res/nbcode_non_distinct_dans_res*log(nb_patient_total/nb_patient_concept_global
// <br>100*nb patient avec ce code dans le res/nb patient dans le res*log(nb_patient_total/nb_patient_concept_global
function affiche_tableau_concepts_direct (id,data) {
	tableau_table_cree['general']='ok';
	eval ("var dataSet=["+data+"]");
	 table_concepts[id]=jQuery("#"+id).DataTable({
        		"data": dataSet,
		        columns: [
				{ title: get_translation('JS_ORDRE','Order') ,"orderSequence": [] },
				{ title: get_translation('JS_CONCEPTS','Concepts') },
				{ title: "# "+ get_translation('JS_PATIENTS','patients'),"orderSequence": [ "desc","asc" ]  },
				{ title: "See" ,"orderSequence": [] },
				{ title: "FreqRes" ,"orderSequence": [ "desc","asc" ] },
				{ title: "TF-IDF","orderSequence": [ "desc","asc" ]  },
				{ title: "PSS" ,"orderSequence": [ "desc","asc" ] },
				{ title: "Case-Weighted PSS","orderSequence": [ "desc","asc" ]  },
				{ title: "Median age","orderSequence": [ "desc","asc" ]  }
		        ] ,
		         "columnDefs": [ {
			            "searchable": false,
			            "orderable": false,
			            "targets": 0
			        } ],
		        "order": [[ 2, "desc" ]]
    		});
	        
	         table_concepts[id].on( 'order.dt ', function () {
		        table_concepts[id].column(0, {order:'applied'}).nodes().each( function (cell, i) {
		            cell.innerHTML = i+1;
		        } );
		    } );
		    
	        table_concepts[id].column(0, {order:'applied'}).nodes().each( function (cell, i) {
	            cell.innerHTML = i+1;
	        } );
}

function affiche_graph_concepts_direct (id,data) {

	tableau_data=data.split(';separateur;');
	eval("var categories=["+tableau_data[0]+"]");
	eval("var liste_locale=["+tableau_data[1]+"]");
	eval("var liste_entrepot=["+tableau_data[2]+"]");
	eval("var liste_pourc_res_entrepot=["+tableau_data[3]+"]");
	var hauteur=tableau_data[4];
	jQuery('#gauche_'+id).css('height',hauteur);
	jQuery('#droite_'+id).css('height',hauteur);
		
	 jQuery('#gauche_'+id).highcharts({
            chart: {
                type: 'bar'
            },
	credits: {
		enabled: false
	},
            title: {
                text: get_translation('JS_FREQUENCY_OF_CONCEPT','Frequency of concept')+ " (%) / " + get_translation('JS_PHENOTYPE_SPECIFICITY_SCORING','Phenotype Specificity scoring')+ " (%)"
            },
            xAxis: [{
                categories: categories,
                reversed: false,
                labels: {
                    step: 1
                }
            }, { // mirror axis on right side
                opposite: true,
                reversed: false,
                linkedTo: 0,
                labels: {
                    step: 1,
			 enabled: false
                }
            }],
            yAxis: {
                title: {
                    text: null
                },
                labels: {
                    formatter: function () {
                        return (Math.abs(this.value));
                    }
                },
                min: -100,
                max: 100
            },
            plotOptions: {
                series: {
                    stacking: 'normal'
                }
            },

            tooltip: {
                formatter: function () {
                    return '<b>' + this.point.category + '</b><br/>' +
                        '' + Highcharts.numberFormat(Math.abs(this.point.y), 0) + "%";
                }
            },
            series: [{
                name: "% " + get_translation('JS_PATIENT','patient'),
                data: liste_locale},
		{
                name: "% " + get_translation('JS_PATIENT','patient'),
                data: liste_entrepot}
                ]
	});
	
	 jQuery('#droite_'+id).highcharts({
	            chart: {
	                type: 'bar'
	            },
		credits: {
			enabled: false
		},
	            title: {
	                text: "Case-weighted PSS"
	            },
	            xAxis: [{
	                categories: categories,
	                reversed: false,
	                labels: {
	                    step: 1,
	                enabled:false,
	                }
	            }],
	            yAxis: {
	                title: {
	                    text: get_translation('JS_SCORE','Score')
	                },
	                labels: {
	                    formatter: function () {
	                        return (Math.abs(this.value));
	                    }
	                },
	                min: 0,
	                max: 100
	            },
	            plotOptions: {
	                series: {
	                    stacking: 'normal'
	                }
	            },
	
	            tooltip: {
	                formatter: function () {
	                    return '<b>' + this.point.category + '</b><br/>' +
	                        '' + Highcharts.numberFormat(Math.abs(this.point.y), 0) + "";
	                }
	            },
	            series: [{
	                name:null,
	                data: liste_pourc_res_entrepot}
	                ]
	});
			
}


function affiche_nuage_concepts (id,tmpresult_num,phenotype_genotype,donnees_reelles_ou_pref,type,distance) {
	if (tableau_table_cree_type[id]!=type+distance) {
	
		tableau_table_cree_type[id]=type+distance;
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			method: 'post',
			async:true,
			contentType: 'application/x-www-form-urlencoded',
			encoding: 'latin1',
			data: {action:'affiche_nuage_concepts',tmpresult_num:tmpresult_num,phenotype_genotype:phenotype_genotype,donnees_reelles_ou_pref:donnees_reelles_ou_pref,type:type,distance:distance},
			beforeSend: function(requester){
				document.getElementById(id).innerHTML="<img src=\"images/chargement_mac.gif\">";
			},
			success: function(requester){
				if (requester=='deconnexion') {
					document.getElementById(id).innerHTML="";
					afficher_connexion("");
				} else { 
					var liste_mot=requester;
					document.getElementById(id).innerHTML='';
					eval ("var liste_mot=["+liste_mot+"]");
					jQuery("#"+id).jQCloud(liste_mot, {removeOverflowing :true});
				}
			},
			error: function(){}
		});
	
	}
}


function affiche_tableau_concepts (id,tmpresult_num,phenotype_genotype,donnees_reelles_ou_pref,type,distance) {
	if (tableau_table_cree_type[id]!=type+distance) {
		if (tableau_table_cree[id]=='ok') {
			table_concepts[id].fnDestroy();
		}
		tableau_table_cree_type[id]=type+distance;
		tableau_table_cree[id]='ok';
		table_concepts[id]=jQuery("#"+id).dataTable({
	        		"ajax": "ajax.php?action=affiche_tableau_concepts&distance="+distance+"&type="+type+"&tmpresult_num="+tmpresult_num+"&phenotype_genotype="+phenotype_genotype+"&donnees_reelles_ou_pref="+donnees_reelles_ou_pref+""
	    		
	    		, "order": [[ 1, "desc" ]]
	    		});
	}
}
function affiche_tableau_data (id,tmpresult_num,thesaurus_code) {
	if (tableau_table_cree_type[id]!=id+tmpresult_num) {
		if (tableau_table_cree[id]=='ok') {
			table_concepts[id].fnDestroy();
		}
		tableau_table_cree_type[id]=id+tmpresult_num;
		tableau_table_cree[id]='ok';
		table_concepts[id]=jQuery("#"+id).dataTable({
	        		"ajax": "ajax.php?action=affiche_tableau_data&tmpresult_num="+tmpresult_num+"&thesaurus_code="+thesaurus_code+""
	    		, "order": [[ 1, "desc" ]]
	    		});
	}
}

function affiche_graph_concepts (id,tmpresult_num,phenotype_genotype,donnees_reelles_ou_pref,type,distance) {
	if (tableau_table_cree_type[id]!=type+distance) {
		tableau_table_cree_type[id]=type+distance;
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			method: 'post',
			async:true,
			contentType: 'application/x-www-form-urlencoded',
			encoding: 'latin1',
			data: {action:'affiche_graph_concepts',tmpresult_num:tmpresult_num,phenotype_genotype:phenotype_genotype,donnees_reelles_ou_pref:donnees_reelles_ou_pref,type:type,distance:distance},
			beforeSend: function(requester){
					document.getElementById('gauche_'+id).innerHTML="<img src=\"images/chargement_mac.gif\">";
					document.getElementById('droite_'+id).innerHTML="<img src=\"images/chargement_mac.gif\">";
			},
			success: function(requester){
				if (requester=='deconnexion') {
					document.getElementById('gauche_'+id).innerHTML="";
					document.getElementById('droite_'+id).innerHTML="";
					afficher_connexion("");
				} else { 
					var data=requester;
					tableau_data=data.split(';separateur;');
					eval("var categories=["+tableau_data[0]+"]");
					eval("var liste_locale=["+tableau_data[1]+"]");
					eval("var liste_entrepot=["+tableau_data[2]+"]");
					eval("var liste_pourc_res_entrepot=["+tableau_data[3]+"]");
					var hauteur=tableau_data[4];
					jQuery('#gauche_'+id).css('height',hauteur);
					jQuery('#droite_'+id).css('height',hauteur);
						
					 jQuery('#gauche_'+id).highcharts({
				            chart: {
				                type: 'bar'
				            },
					credits: {
						enabled: false
					},
				            title: {
				                text: "% " + get_translation('JS_PATIENTS_DANS_LE_RESULTAT','patients dans le résultat') + " / % " + get_translation('JS_PATIENTS_DANS_ENTREPOT','patients dans entrepôt')
				            },
				            xAxis: [{
				                categories: categories,
				                reversed: false,
				                labels: {
				                    step: 1
				                }
				            }, { // mirror axis on right side
				                opposite: true,
				                reversed: false,
				                linkedTo: 0,
				                labels: {
				                    step: 1,
							 enabled: false
				                }
				            }],
				            yAxis: {
				                title: {
				                    text: null
				                },
				                labels: {
				                    formatter: function () {
				                        return (Math.abs(this.value));
				                    }
				                },
				                min: -100,
				                max: 100
				            },
				            plotOptions: {
				                series: {
				                    stacking: 'normal'
				                }
				            },
				
				            tooltip: {
				                formatter: function () {
				                    return '<b>' + this.series.name + ',  ' + this.point.category + '</b><br/>' +
				                        "% " + get_translation('JS_PATIENT','patient') + " : " + Highcharts.numberFormat(Math.abs(this.point.y), 0) + "%";
				                }
				            },
				            series: [{
				                name:  get_translation('JS_RESULTAT','Résulat'),
				                data: liste_locale},
						{
				                name: get_translation('JS_ENTREPOT','Entrepôt'),
				                data: liste_entrepot}
				                ]
					});
					
					
					
					
					
					 jQuery('#droite_'+id).highcharts({
					            chart: {
					                type: 'bar'
					            },
						credits: {
							enabled: false
						},
					            title: {
					                text: get_translation('JS_NB_PATIENTS_RESULTAT','Nb patients résultat') + " / " + get_translation('JS_NB_PATIENTS_ENTREPOT','Nb patients entrepôt')
					            },
					            xAxis: [{
					                categories: categories,
					                reversed: false,
					                labels: {
					                    step: 1
					                }
					            }],
					            yAxis: {
					                title: {
					                    text: null
					                },
					                labels: {
					                    formatter: function () {
					                        return (Math.abs(this.value));
					                    }
					                },
					                min: 0,
					                max: 100
					            },
					            plotOptions: {
					                series: {
					                    stacking: 'normal'
					                }
					            },
					
					            tooltip: {
					                formatter: function () {
					                    return '<b>' + this.series.name + ',  ' + this.point.category + '</b><br/>' +
					                        '% patient : ' + Highcharts.numberFormat(Math.abs(this.point.y), 0) + "%";
					                }
					            },
					            series: [{
					                name: get_translation('RESULT','Résulat'),
					                data: liste_pourc_res_entrepot}
					                ]
					});
				}
				
			},
			error: function(){}
		});
	}
}


function affiche_onglet_genes (tmpresult_num) {
	affiche_tableau_data ('id_tableau_genes_data',tmpresult_num,'gene');
	affiche_tableau_go_data ('id_tableau_go_data',tmpresult_num);
	affiche_tableau_concepts ('id_tableau_genes',tmpresult_num,"genotype","reelles","patient");
	affiche_tableau_go ('id_tableau_go',tmpresult_num,"patient");
	repartition_concepts_resumer_texte ('id_repartition_concepts_resumer_texte',tmpresult_num,"genotype","patient");
}

function affiche_tableau_go (id,tmpresult_num,type) {
	if (!tableau_table_cree[id]) {
		tableau_table_cree[id]='ok';
		jQuery("#"+id).dataTable({
	        		"ajax": "ajax.php?action=affiche_tableau_go&tmpresult_num="+tmpresult_num+"&type="+type+"", "order": [[ 3, "desc" ]]} );
	}
}

function affiche_tableau_go_data (id,tmpresult_num) {
	if (!tableau_table_cree[id]) {
		tableau_table_cree[id]='ok';
		jQuery("#"+id).dataTable({
	        		"ajax": "ajax.php?action=affiche_tableau_go_data&tmpresult_num="+tmpresult_num+"", "order": [[ 3, "desc" ]]} );
	}
}

function repartition_concepts_resumer_texte (id,tmpresult_num,phenotype_genotype,type) {
	if (document.getElementById(id)) {
		if (document.getElementById(id).innerHTML=='') {
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				method: 'post',
				async:true,
				contentType: 'application/x-www-form-urlencoded',
				encoding: 'latin1',
				data: {action:'repartition_concepts_resumer_texte',tmpresult_num:tmpresult_num,phenotype_genotype:phenotype_genotype,type:type},
				beforeSend: function(requester){
				},
				success: function(requester){
					if (requester=='deconnexion') {
						afficher_connexion("repartition_concepts_resumer_texte ('"+id+"','"+tmpresult_num+"','"+phenotype_genotype+"','"+type+"')");
					} else { 
						document.getElementById(id).innerHTML=requester;
					}
				},
				error: function(){}
			});
		}
	}
}

function affiche_heatmap_concepts (id,tmpresult_num,phenotype_genotype,donnees_reelles_ou_pref,type,distance) {
	if (document.getElementById(id).style.display=='none') {
		document.getElementById(id).style.display='block';
		if (distance=='') {
			distance=distance_en_cours_concepts;
		}
		if (type=='') {
			type=type_en_cours_concepts;
		}
		if (tableau_table_cree_type[id]!=type+distance) {
			tableau_table_cree_type[id]=type+distance;
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				method: 'post',
				async:true,
				contentType: 'application/x-www-form-urlencoded',
				encoding: 'latin1',
				data: {action:'affiche_heatmap_concepts',tmpresult_num:tmpresult_num,phenotype_genotype:phenotype_genotype,donnees_reelles_ou_pref:donnees_reelles_ou_pref,type:type,distance:distance},
				beforeSend: function(requester){
						document.getElementById(id).innerHTML="<img src=\"images/chargement_mac.gif\">";
				},
				success: function(requester){
					if (requester=='deconnexion') {
						afficher_connexion("affiche_heatmap_concepts ('"+id+"','"+tmpresult_num+"','"+phenotype_genotype+"','"+donnees_reelles_ou_pref+"','"+type+"','"+distance+"')");
					} else { 
						document.getElementById(id).innerHTML=requester;
				 		if (document.getElementById('id_table_heatmap_concept')) {
						 	var tableDnD=new TableDnD();
							tableDnD.init(document.getElementById('id_table_heatmap_concept')); 
						}
					}
				},
				error: function(){}
			});
		}
	} else {
		document.getElementById(id).style.display='none';
	}
}



function liste_combinaison_concepts (id,tmpresult_num,phenotype_genotype,donnees_reelles_ou_pref,type,distance) {
	if (document.getElementById(id).style.display=='none') {
		document.getElementById(id).style.display='block';
		if (distance=='') {
			distance=distance_en_cours_concepts;
		}
		if (type=='') {
			type=type_en_cours_concepts;
		}
		if (tableau_table_cree_type[id]!=type+distance) {
			//tableau_table_cree_type[id]=type+distance;
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				method: 'post',
				async:true,
				contentType: 'application/x-www-form-urlencoded',
				encoding: 'latin1',
				data: {action:'liste_combinaison_concepts',tmpresult_num:tmpresult_num,phenotype_genotype:phenotype_genotype,donnees_reelles_ou_pref:donnees_reelles_ou_pref,type:type,distance:distance},
				beforeSend: function(requester){
						document.getElementById(id).innerHTML="<img src=\"images/chargement_mac.gif\">";
				},
				success: function(requester){
					if (requester=='deconnexion') {
						afficher_connexion("liste_combinaison_concepts ('"+id+"','"+tmpresult_num+"','"+phenotype_genotype+"','"+donnees_reelles_ou_pref+"','"+type+"','"+distance+"')");
					} else { 
						document.getElementById(id).innerHTML=requester;
					}
				},
				error: function(){}
			});
		}
	} else {
		document.getElementById(id).style.display='none';
	}
}


var var_id_ligne_rouge='';
function encadrer_rouge_ligne(id) {
	if (var_id_ligne_rouge==id) {	
		jQuery("#"+id).css("border-bottom","1px solid black"); 
		jQuery("#"+id).css("border-top","1px solid black"); 
		var_id_ligne_rouge='';
	} else {
		if (var_id_ligne_rouge!='') {	
			jQuery("#"+var_id_ligne_rouge).css("border-bottom","1px solid black"); 
			jQuery("#"+var_id_ligne_rouge).css("border-top","1px solid black"); 
		}
		jQuery("#"+id).css("border-bottom","medium solid red"); 
		jQuery("#"+id).css("border-top","medium solid red"); 
		var_id_ligne_rouge=id;
	}
}