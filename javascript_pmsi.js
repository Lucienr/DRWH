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
var type_en_cours_pmsi='document';
var num_temp_pmsi;
var distance_origine_pmsi;

function affiche_onglet_pmsi (tmpresult_num,type,refresh,thesaurus_code) {
	if (type=='') {
		type=type_en_cours_pmsi;
	} else {
		type_en_cours_pmsi=type;
	}
	num_temp_pmsi=tmpresult_num;
	
	if (type=='sejour') {
		$('#id_bouton_pmsi_sejour').children('a').css('color','#e00adb');
		$('#id_bouton_pmsi_patient').children('a').css('color','#036ba5');
	}
	if (type=='patient') {
		$('#id_bouton_pmsi_sejour').children('a').css('color','#036ba5');
		$('#id_bouton_pmsi_patient').children('a').css('color','#e00adb');
	}
	document.getElementById('id_div_pmsi_precedent').innerHTML="";
	if (tableau_table_cree_type['pmsi']!='ok' || refresh=='ok') {
		tableau_table_cree_type['pmsi']='ok';
		affiche_tableau_pmsi(tmpresult_num,type,thesaurus_code);
		affiche_graph_pmsi ('id_div_pmsi',tmpresult_num,thesaurus_code,type,0,1);
		$("#slider-range-max_pmsi").slider('value', 3);
		document.getElementById("id_div_distance_pmsi").innerHTML=3;
		affiche_graph_pmsi ('id_div_pmsi_niveau',tmpresult_num,thesaurus_code,type,0,3);
	}
}


function actualiser_graph_pmsi (distance,thesaurus_code) {
	if (distance==distance_origine_pmsi) {
		affiche_graph_pmsi ('id_div_pmsi_niveau',num_temp_pmsi,thesaurus_code,type_en_cours_pmsi,0,distance);
	}
}

function affiche_graph_pmsi (id,tmpresult_num,thesaurus_code,type,thesaurus_data_father_num,distance) {
	var sous_titre='';
	if (distance>1) {
		sous_titre=" level <= "+distance;
	}
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		method: 'post',
		async:true,
		contentType: 'application/x-www-form-urlencoded',
		encoding: 'latin1',
		data: {action:'affiche_graph_pmsi',tmpresult_num:tmpresult_num,thesaurus_code:escape(thesaurus_code),type:type,thesaurus_data_father_num:thesaurus_data_father_num,distance:distance},
		beforeSend: function(requester){
				document.getElementById(id).innerHTML="<img src=\"images/chargement_mac.gif\">";
		},
		success: function(requester){
			var data=requester;
			if (data=='deconnexion') {
				afficher_connexion();
			} else {
				if (data!='') {
					tableau_data=data.split(';separateur;');
					eval("var categories=["+tableau_data[0]+"]");
					eval("var liste_serie=["+tableau_data[1]+"]");
					var hauteur=tableau_data[2];
					
					$('#'+id).css('height',hauteur);
						
					 $('#'+id).highcharts({
						chart: {
							type: 'bar'
						},
						credits: {
							enabled: false
						},
				            title: {
				                text: "Nb patients par diagnostic"+sous_titre
				            },
				            xAxis: [{
				                categories: categories,
				                reversed: false,
				                labels: {
				                    step: 1,
				                    maxStaggerLines:1
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
						allowDecimals:false
				            },
				            plotOptions: {
				                series: {
				                    stacking: 'normal'
				                }
				            },
				
				            tooltip: {
				                formatter: function () {
				                    return '<b>' + this.series.name + ',  ' + this.point.category + '</b><br/>' +
				                        '' + Highcharts.numberFormat(Math.abs(this.point.y), 0) + " patients";
				                }
				            },
				            series: liste_serie
					});
					if (distance==1 && document.getElementById(id+'_precedent')) {
						thesaurus_data_num_precedent=trouver_thesaurus_data_father_num (thesaurus_data_father_num,thesaurus_code);
						if (thesaurus_data_num_precedent!='') {
							document.getElementById(id+'_precedent').innerHTML="<br><br><a href=\"#\" onclick=\"affiche_graph_pmsi ('"+id+"','"+tmpresult_num+"','"+thesaurus_code+"','"+type+"','"+thesaurus_data_num_precedent+"','"+distance+"');return false;\">Revenir au niveau précédent</a>";
						} else {
							document.getElementById(id+'_precedent').innerHTML="";
						}
					
						$('.highcharts-axis-labels text').css('cursor','pointer');
						$('#'+id+' .highcharts-axis-labels text').click(function () {
							text=$(this).text();
							thesaurus_data_num=trouver_libelle_pmsi (text,thesaurus_data_father_num,tmpresult_num,thesaurus_code,type);
							if (thesaurus_data_num!='') {
								affiche_graph_pmsi (id,tmpresult_num,thesaurus_code,type,thesaurus_data_num,distance);
							}
						});
					}
				} else {
					document.getElementById(id).innerHTML="";
				}
			}
		},
		error: function(){}
	});
}


function trouver_libelle_pmsi (text,thesaurus_data_father_num,tmpresult_num,thesaurus_code,type) {
	var thesaurus_data_num;
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		method: 'post',
		async:false,
		contentType: 'application/x-www-form-urlencoded',
		encoding: 'latin1',
		data: {action:'trouver_libelle_pmsi',text:escape(text),thesaurus_data_father_num:thesaurus_data_father_num,tmpresult_num:tmpresult_num,thesaurus_code:thesaurus_code,type:type},
		beforeSend: function(requester){
		},
		success: function(requester){
			 thesaurus_data_num=requester;
		},
		error: function(){}
	});
	return (thesaurus_data_num);

}

function trouver_thesaurus_data_father_num (thesaurus_data_num,thesaurus_code) {
	var thesaurus_data_father_num;
	jQuery.ajax({
		type:"POST",
		url:"ajax.php",
		method: 'post',
		async:false,
		contentType: 'application/x-www-form-urlencoded',
		encoding: 'latin1',
		data: {action:'trouver_thesaurus_data_father_num',thesaurus_data_num:thesaurus_data_num,thesaurus_code:thesaurus_code},
		beforeSend: function(requester){
		},
		success: function(requester){
			 thesaurus_data_father_num=requester;
		},
		error: function(){}
	});
	return (thesaurus_data_father_num);

}