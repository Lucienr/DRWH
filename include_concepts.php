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

<table border="0">
	<tr>
		<td>
			<span id="button_id_div_filter_the_concepts" onclick="concepts_sub_menu('id_div_filter_the_concepts');" onmouseover="concepts_sub_menu_hover('id_div_filter_the_concepts');" onmouseout="concepts_sub_menu_out('id_div_filter_the_concepts');" class="bouton_sauver">
				<? print get_translation('FILTER_THE_CONCEPTS','Filtrer les concepts'); ?>
			</span>
			<span id="button_id_div_export_concepts_below" onclick="concepts_sub_menu('id_div_export_concepts_below');" onmouseover="concepts_sub_menu_hover('id_div_export_concepts_below');" onmouseout="concepts_sub_menu_out('id_div_export_concepts_below');" class="bouton_sauver">
				<? print get_translation('EXPORT_CONCEPTS_BELOW','Exporter les concepts ci dessous'); ?>
			</span>
		</td>
	</tr>
</table>			
<div id="id_div_filter_the_concepts" style="width:550px;display:none;font-size:13px">
	<span class="bouton_bio"  id="id_bouton_concepts_document"><input type="radio" id="id_radio_concepts_document" onclick="affiche_onglet_concepts(<? print $tmpresult_num; ?>,'document','');"> <a href="#" onclick="affiche_onglet_concepts(<? print $tmpresult_num; ?>,'document','');return false;"><? print get_translation('ON_ALL_FOUND_DOCUMENTS','Uniquement sur les documents trouvés'); ?></a></span> 
	<br>
	<span class="bouton_bio" id="id_bouton_concepts_patient"><input type="radio" id="id_radio_concepts_patient" onclick="affiche_onglet_concepts(<? print $tmpresult_num; ?>,'patient','');"> <a href="#" onclick="affiche_onglet_concepts(<? print $tmpresult_num; ?>,'patient','');return false;"><? print get_translation('ON_ALL_FOUND_PATIENTS','Sur tous les documents des patients trouvés'); ?></a></span> 
	<br>
	<? print get_translation('PATIENT_AGE_AT_CONCEPT',"Age du patient à l'évocation du concept"); ?> : <? print get_translation('FROM_AGE',"de"); ?> <input type="text" size="3" id="id_age_concept_min"> <? print get_translation('TO_AGE',"à"); ?>  <input type="text" size="3" id="id_age_concept_max"> <? print get_translation('YEARS',"Ans"); ?> <input type="button" value="<? print get_translation('FILTER',"Filtrer"); ?>" onclick="filter_concept_by_age(<? print $tmpresult_num; ?>);"> <input type="button" value="<? print get_translation('RESET',"Reset"); ?>" onclick="reset_filter_concept_by_age(<? print $tmpresult_num; ?>);">
	<div id="id_slider" style="display:none;">
		<? print get_translation('DEPTH','Profondeur'); ?> : <div id="id_div_distance_concepts" style="width:10px;display:inline;">1</div><div id="slider-range-max_concepts" style="width:300px;"></div>
	</div>
</div>

<div id="id_div_export_concepts_below" style="width:550px;display:none;font-size:13px">
	<span class="bouton_bio"><a href="#" onclick="export_concepts(<? print $tmpresult_num; ?>);return false;"><? print get_translation('CONCEPT_SCORES','les concepts et les scores'); ?></a></span> <br>
	<span class="bouton_bio"><a href="#" onclick="export_concepts_patient(<? print $tmpresult_num; ?>);return false;"><? print get_translation('CONCEPT_PATIENT','les concepts par patient'); ?></a></span> 
</div>
	


<h2><? print get_translation('CONCEPTS','Les concepts'); ?></h2>

<script language="javascript">
$(function() {
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
print "<div id=\"id_div_nuage_signes\" style=\"width: 850px; height: 350px; border: 0px solid #ccc;\"><img src=\"images/chargement_mac.gif\"></div>";

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

//print "<h2 onclick=\"liste_combinaison_concepts('id_div_liste_combinaison_concepts',$tmpresult_num,'','pref','','');\" style=\"cursor:pointer;\">+ ".get_translation('DISPLAY_COOCCURENCES','Afficher les cooccurences')." </h2>
//<div id=\"id_div_liste_combinaison_concepts\" style=\"display:none;\"><img src=\"images/chargement_mac.gif\"></div>";

//print "<h2 onclick=\"affiche_heatmap_concepts('id_div_heatmap_concept',$tmpresult_num,'','pref','','');\" style=\"cursor:pointer;\">+ ".get_translation('DISPLAY_CONCEPTS_HEATMAP','Afficher la HeatMap des concepts')."</h2>
//<div id=\"id_div_heatmap_concept\" style=\"display:none;\"><img src=\"images/chargement_mac.gif\"></div>";

?>
