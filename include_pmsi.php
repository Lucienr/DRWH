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

include_once "fonctions_pmsi.php";
?>
<div>
<span class="bouton_bio" id="id_bouton_pmsi_sejour"><a href="#" onclick="affiche_onglet_pmsi(<? print $tmpresult_num; ?>,'sejour','ok','<? print $thesaurus_code_pmsi; ?>');return false;"><? print get_translation('ON_ALL_FOUND_STAYS','Sur les séjours trouvés'); ?></a></span> 
<span class="bouton_bio">-</span> 
<span class="bouton_bio" id="id_bouton_pmsi_patient"><a href="#" onclick="affiche_onglet_pmsi(<? print $tmpresult_num; ?>,'patient','ok','<? print $thesaurus_code_pmsi; ?>');return false;"><? print get_translation('SUR_TOUS_LES_SEJOURS_DES_PATIENTS_TROUVES','Sur tous les séjours des patients trouvés'); ?></a></span> 
</div>
<?
print "<div id=\"id_div_pmsi_precedent\"></div>";
print "<div id=\"id_div_pmsi\" style=\"width:  100%; height:500px; border: 0px solid #ccc;\"><img src=\"images/chargement_mac.gif\"></div>";

print "<br>
".get_translation('DEPTH','Profondeur')." : <div id=\"id_div_distance_pmsi\" style=\"width:10px;display:inline;\">3</div>
<div id=\"slider-range-max_pmsi\" style=\"width:300px;\"></div>
<br><br>

<div id=\"id_div_pmsi_niveau\" style=\"width: 100%; height:500px; border: 0px solid #ccc;\"><img src=\"images/chargement_mac.gif\"></div>";


//$max_distance_pmsi=max_distance_pmsi ($tmpresult_num,'cim10');
if ($max_distance_pmsi=='') {
	$max_distance_pmsi=6;
}
?>
<script language="javascript">
$(function() {
	$( "#slider-range-max_pmsi" ).slider({
		range: "max",
		min: 1,
		max: <? print $max_distance_pmsi; ?>,
		value: 3,
		slide: function( event, ui ) {
			document.getElementById("id_div_distance_pmsi").innerHTML=ui.value;
			distance_origine_pmsi=ui.value;
			setTimeout("actualiser_graph_pmsi ("+ui.value+",'<? print $thesaurus_code_pmsi; ?>' );",500);
		}
	});
});
</script>