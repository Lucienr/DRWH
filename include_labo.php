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
    
include_once "fonctions_labo.php";
?>
<div><? print get_translation('SEARCH_AN_EXAM','Rechercher un examen'); ?> : <input id="id_rechercher_code_labo" class="filtre_date_document" type="text" onkeypress="if(event.keyCode==13) {rechercher_code_labo();}">
</div>
<div id="id_div_resultat_recherche_code_labo"></div>

<div id="id_div_resultat_tableau_groupe"></div>
<div id="id_div_resultat_tableau_all_exam"></div>
<div id="id_div_labo_liste_patient">
	<? 
		$sel=oci_parse($dbh,"select distinct patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num");
		oci_execute($sel);
		while ($r=oci_fetch_array($sel)) {
			$patient_num=$r['PATIENT_NUM'];
			print "<div id=\"id_div_graph_labo_$patient_num\"></div>";
		}
	
	?>
</div>
<div id="id_visualiser_graph_scatterplot"></div>