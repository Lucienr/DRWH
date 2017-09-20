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
include_once "fonctions_concepts.php"; 

?>
<style>
.sorting_disabled {
	 background: rgba(0, 0, 0, 0);
}
</style>
<div>
<span class="bouton_bio" id="id_bouton_concepts_document"><a href="#" onclick="affiche_onglet_concepts(<? print $tmpresult_num; ?>,'document','');return false;"><? print get_translation('ON_ALL_FOUND_DOCUMENTS','Sur les documents trouvés'); ?></a></span> <span class="bouton_bio">-</span> 
<span class="bouton_bio" id="id_bouton_concepts_patient"><a href="#" onclick="affiche_onglet_concepts(<? print $tmpresult_num; ?>,'patient','');return false;"><? print get_translation('ON_ALL_FOUND_PATIENTS','Sur tous les documents des patients trouvés'); ?></a></span> 
</div>
<?

?>
<br>
<div id="id_slider" style="display:none;">
	<? print get_translation('DEPTH','Profondeur'); ?> : <div id="id_div_distance_concepts" style="width:10px;display:inline;">1</div><div id="slider-range-max_concepts" style="width:300px;"></div>
</div>


<script language="javascript">
$(function() {
	//jQuery.ajax({
	//	type:"POST",
	//	url:"ajax.php",
	//	method: 'post',
	//	async:false,
	//	contentType: 'application/x-www-form-urlencoded',
	//	encoding: 'latin1',
	//	data: {action:'calcul_max_concepts',tmpresult_num:<? print $tmpresult_num; ?>},
	//	beforeSend: function(requester){
	//	},
	//	success: function(requester){
	//		document.getElementById("id_div_distance_concepts").innerHTML=requester;
	//		$( "#slider-range-max_concepts" ).slider({
	//			range: "max",
	//			min: 1,
	//			max: requester,
	//			value: requester,
	//			slide: function( event, ui ) {
	//				document.getElementById("id_div_distance_concepts").innerHTML=ui.value;
	//				distance_origine_concepts=ui.value;
	//				setTimeout("actualiser_graph_concepts ("+ui.value+");",500);
	//			}
	//		});
	//	},
	//	error: function(){}
	//});
	
	requester=10;
	document.getElementById("id_div_distance_concepts").innerHTML=requester;
	$( "#slider-range-max_concepts" ).slider({
		range: "max",
		min: 1,
		max: requester,
		value: requester,
		slide: function( event, ui ) {
			document.getElementById("id_div_distance_concepts").innerHTML=ui.value;
			distance_origine_concepts=ui.value;
			setTimeout("actualiser_graph_concepts ("+ui.value+");",500);
		}
	});
	
});
</script>

<?
print "<div id=\"id_div_nuage_signes\" style=\"width: 850px; height: 550px; border: 0px solid #ccc;\"><img src=\"images/chargement_mac.gif\"></div>";

print "<div id=\"id_div_nuage_signes\" style=\"width: 850px;  border: 0px solid #ccc;\">
	<table class=\"tablefin\" id=\"id_tableau_signes\" width=\"850px\">
	</table>
	</div>";
	

print "
<table border=0>
<tr>
<td><div id=\"gauche_id_concepts_signes\" style=\"width: 600px; height:500px; border: 0px solid #ccc;\"><img src=\"images/chargement_mac.gif\"></div></td>
<td><div id=\"droite_id_concepts_signes\" style=\"width: 250px; height:500px; border: 0px solid #ccc;\"><img src=\"images/chargement_mac.gif\"></div></td>
</tr>
</table>";

print "<h2 onclick=\"liste_combinaison_concepts('id_div_liste_combinaison_concepts',$tmpresult_num,'','pref','','');\" style=\"cursor:pointer;\">+ ".get_translation('DISPLAY_COOCCURENCES','Afficher les cooccurences')." </h2>
<div id=\"id_div_liste_combinaison_concepts\" style=\"display:none;\"><img src=\"images/chargement_mac.gif\"></div>";

print "<h2 onclick=\"affiche_heatmap_concepts('id_div_heatmap_concept',$tmpresult_num,'','pref','','');\" style=\"cursor:pointer;\">+ ".get_translation('DISPLAY_CONCEPTS_HEATMAP','Afficher la HeatMap des concepts')."</h2>
<div id=\"id_div_heatmap_concept\" style=\"display:none;\"><img src=\"images/chargement_mac.gif\"></div>";

?>
