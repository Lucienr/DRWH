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

<div id="map_pays_google" style="width:100%;height:600px">
	<iframe id="id_iframe_google_map" style="width: 100%; height: 100%; border: 0px none;" scrolling="no" src=""></iframe>
</div>
<br><br>



<div id="id_div_repartition_par_pays"></div>
<br><br>



<?
$liste_ville='';
$liste_nb_enr='';

$req="select residence_city, residence_latitude,residence_longitude, count(distinct dwh_tmp_result_$user_num_session.patient_num) nb_patient from dwh_tmp_result_$user_num_session, dwh_patient where tmpresult_num=$tmpresult_num and dwh_tmp_result_$user_num_session.patient_num=dwh_patient.patient_num 
and residence_city is not null and residence_longitude is not null and residence_latitude is not null group by residence_city, residence_latitude,residence_longitude";
$sel=oci_parse($dbh,$req);
oci_execute($sel) ;
while ($res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
	$residence_city=str_replace("'"," ",$res['RESIDENCE_CITY']);
	$residence_latitude=str_replace(",",".",$res['RESIDENCE_LATITUDE']);
	$residence_longitude=str_replace(",",".",$res['RESIDENCE_LONGITUDE']);
	$nb_patient=$res['NB_PATIENT'];
	if ($residence_longitude!='-' && $residence_latitude!='-') {
		$liste_ville.="{latLng: [$residence_latitude, $residence_longitude], name: '$residence_city #$nb_patient'},";
		$liste_nb_enr.="$nb_patient,";
	}
}
$liste_ville=substr($liste_ville,0,-1);
$liste_nb_enr=substr($liste_nb_enr,0,-1);


?>


<link href="map/jquery-jvectormap.css" media="all" rel="stylesheet"></link>
<script src="map/jquery-jvectormap.js"></script>
<script src="map/jquery-mousewheel.js"></script>
<script src="map/lib/jvectormap.js"></script>
<script src="map/lib/abstract-element.js"></script>
<script src="map/lib/abstract-canvas-element.js"></script>
<script src="map/lib/abstract-shape-element.js"></script>
<script src="map/lib/svg-element.js"></script>
<script src="map/lib/svg-group-element.js"></script>
<script src="map/lib/svg-canvas-element.js"></script>
<script src="map/lib/svg-shape-element.js"></script>
<script src="map/lib/svg-path-element.js"></script>
<script src="map/lib/svg-circle-element.js"></script>
<script src="map/lib/vml-element.js"></script>
<script src="map/lib/vml-group-element.js"></script>
<script src="map/lib/vml-canvas-element.js"></script>
<script src="map/lib/vml-shape-element.js"></script>
<script src="map/lib/vml-path-element.js"></script>
<script src="map/lib/vml-circle-element.js"></script>
<script src="map/lib/vector-canvas.js"></script>
<script src="map/lib/simple-scale.js"></script>
<script src="map/lib/ordinal-scale.js"></script>
<script src="map/lib/numeric-scale.js"></script>
<script src="map/lib/color-scale.js"></script>
<script src="map/lib/data-series.js"></script>
<script src="map/lib/proj.js"></script>
<script src="map/lib/world-map.js"></script>
<script src="map/lib/jquery-jvectormap-world-merc-en.js"></script>


<div id="map_pays" style="width:900px;height:500px"></div>
<script language="javascript">
function generer_map() {
	document.getElementById('map_pays').innerHTML='';
	jQuery(function(){
		var map,
		markers = [
		<? print $liste_ville; ?>
		],
		map = new jvm.WorldMap({
			container: jQuery('#map_pays'),
			map: 'world_merc_en',
			zoomMax:500,
			regionsSelectable: true,
			markersSelectable: true,
			markers: markers,
			markerStyle: {
				initial: {
					fill: '#F8BAF8'
				},
				selected: {
					fill: '#DA3DCA'
				}
			},
			regionStyle: {
				initial: {
					fill: '#D0DCEE'
				},
				selected: {
					fill: '#F4A582'
				}
			},
			series: {
				markers: [{
					attribute: 'r',
					scale: [5, 25],
					values: [<? print $liste_nb_enr; ?>]
				}]
			},
			onRegionSelected: function(){
				if (window.localStorage) {
					window.localStorage.setItem(
						'jvectormap-selected-regions',
						JSON.stringify(map.getSelectedRegions())
					);
				}
			},
			onMarkerSelected: function(){
				if (window.localStorage) {
					window.localStorage.setItem(
						'jvectormap-selected-markers',
						JSON.stringify(map.getSelectedMarkers())
					);
				}
			}
		});
		map.setSelectedRegions( JSON.parse( window.localStorage.getItem('jvectormap-selected-regions') || '[]' ) );
		map.setSelectedMarkers( JSON.parse( window.localStorage.getItem('jvectormap-selected-markers') || '[]' ) );
	});
	
	
	
	
	if (tableau_table_cree_type['affichage_par_pays']!='ok') {
		document.getElementById('id_iframe_google_map').src="google_map.php?tmpresult_num=<? print $tmpresult_num; ?>";
		tableau_table_cree_type['affichage_par_pays']='ok';
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			method: 'post',
			async:true,
			contentType: 'application/x-www-form-urlencoded',
			encoding: 'latin1',
			data: {action:'afficher_repartition_par_pays',tmpresult_num:<? print $tmpresult_num; ?>},
			beforeSend: function(requester){
				document.getElementById('id_div_repartition_par_pays').innerHTML="<img src=\"images/chargement_mac.gif\">";
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion("generer_map()");
				} else { 
					document.getElementById('id_div_repartition_par_pays').innerHTML=requester;
					$("#id_tableau_repartition_par_pays").dataTable( {paging: false, "order": [[ 0, "asc" ]]});
				}
			},
			error: function(){}
		});
	
	}
}
</script>


<br><br>
