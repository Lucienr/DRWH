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
<style>
#mapid { height: 600px; width:1000px; }
</style>
 <link rel="stylesheet" href="leaflet/leaflet.css" /> 
 <!-- Make sure you put this AFTER Leaflet's CSS -->
 <script src="leaflet/leaflet.js" ></script>
 <script type="text/javascript" src="leaflet/leaflet.markercluster.js"></script>
<link rel="stylesheet" href="leaflet/MarkerCluster.Default.css" />
<div id="mapid" ></div>
<div id="id_div_repartition_par_pays"></div>

<script language=javascript>
function generer_map(tmpresult_num) {
	if (tableau_table_cree_type['generer_map']!='ok') {
		tableau_table_cree_type['generer_map']='ok';
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			method: 'post',
			async:true,
			contentType: 'application/x-www-form-urlencoded',
			encoding: 'latin1',
			data: {action:'generer_map',tmpresult_num:tmpresult_num},
			beforeSend: function(requester){
				document.getElementById('mapid').innerHTML="<img src=\"images/chargement_mac.gif\">";
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion("generer_map()");
				} else { 
					var map = L.map('mapid').setView([<? print $GEO_CENTRE; ?>], 6);
					var stamenToner = L.tileLayer('https://stamen-tiles-{s}.a.ssl.fastly.net/toner/{z}/{x}/{y}.png', {
						attribution: 'Map tiles by <a href="https://stamen.com">Stamen Design</a>, <a href="https://creativecommons.org/licenses/by/3.0">CC BY 3.0</a> &mdash; Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
						subdomains: 'abcd',
						minZoom: 0,
						maxZoom: 20,
						ext: 'png'
					});
					map.addLayer(stamenToner);
				
					var markersCluster = new L.MarkerClusterGroup();
					var points = eval(requester);
					for (var i = 0; i < points.length; i++) {
						var latLng = new L.LatLng(points[i][1], points[i][2]);
						var marker = new L.Marker(latLng, {title: points[i][0]});
						markersCluster.addLayer(marker);
					}
					map.addLayer(markersCluster);
				}
			},
			error: function(){}
		});
		
	}
	
	
	if (tableau_table_cree_type['affichage_par_pays']!='ok') {
		tableau_table_cree_type['affichage_par_pays']='ok';
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			method: 'post',
			async:true,
			contentType: 'application/x-www-form-urlencoded',
			encoding: 'latin1',
			data: {action:'afficher_repartition_par_pays',tmpresult_num:tmpresult_num},
			beforeSend: function(requester){
				jQuery('#id_div_repartition_par_pays').html("<img src=\"images/chargement_mac.gif\">");
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion("generer_map()");
				} else { 
					jQuery('#id_div_repartition_par_pays').html(requester);
					$("#id_tableau_repartition_par_pays").dataTable( {paging: false, "order": [[ 0, "asc" ]]});
				}
			},
			error: function(){}
		});
	}

}
</script>

