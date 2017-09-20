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
session_start();

putenv("NLS_LANG=French");

include_once "parametrage.php";
include_once "connexion_bdd.php";
include_once("ldap.php");
include_once("fonctions_droit.php");
include_once("fonctions_dwh.php");
include_once "fonctions_stat.php"; 
include_once "fonctions_concepts.php"; 
include_once "fonctions_pmsi.php"; 
include_once "fonctions_labo.php"; 


if ($_SESSION['dwh_login']=='') {
	print "deconnexion";
	exit;
} else {
	include_once("verif_droit.php");
	if ($erreur_droit!='') {
		print "$erreur_droit";
		exit;
	}
}
session_write_close();

$tmpresult_num=$_GET['tmpresult_num'];
 $localisation_list="[";
$req="select  residence_city, residence_latitude,residence_longitude from dwh_patient where patient_num in (select patient_num from dwh_tmp_result where tmpresult_num=$tmpresult_num)
and  residence_longitude is not null and residence_latitude is not null ";
$sel=oci_parse($dbh,$req);
oci_execute($sel) ;
while ($res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
	$residence_city=str_replace("'"," ",$res['RESIDENCE_CITY']);
	$residence_latitude=str_replace(",",".",$res['RESIDENCE_LATITUDE']);
	$residence_longitude=str_replace(",",".",$res['RESIDENCE_LONGITUDE']);
	$nb_patient=$res['NB_PATIENT'];
	if ($residence_longitude!='-' && $residence_latitude!='-') {
		 $localisation_list.="{lat: $residence_latitude, lng: $residence_longitude},";
	}
}
$localisation_list=substr($localisation_list,0,-1);
$localisation_list.="]";

?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
   <meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
    <style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #map {
        height: 100%;
      }
      /* Optional: Makes the sample page fill the window. */
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
    </style>
  </head>
  <body>
    <div id="map"></div>
    <script>

      function initMap() {

        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 5,
          center: {lat: 48.845853, lng: 2.303152}
        });

        // Create an array of alphabetical characters used to label the markers.
        var labels = 'P';

        // Add some markers to the map.
        // Note: The code uses the JavaScript Array.prototype.map() method to
        // create an array of markers based on a given "locations" array.
        // The map() method here has nothing to do with the Google Maps API.
        var markers = locations.map(function(location, i) {
          return new google.maps.Marker({
            position: location,
            label: labels[i % labels.length]
          });
        });

        // Add a marker clusterer to manage the markers.
        var markerCluster = new MarkerClusterer(map, markers,
            {imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'});
      }
      var locations = <? print $localisation_list; ?>
    </script>
    <script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js">
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=<? print $GOOGLE_MAP_API_KEY; ?>&callback=initMap">
    </script>
  </body>
</html>
