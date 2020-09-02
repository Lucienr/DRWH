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
    
print "<h2>".get_translation('CODED_GENES','G�nes cod�s')."</h2>";
print "<table class=\"tablefin\" id=\"id_tableau_genes_data\">
	<thead>
		<tr>
		<th>".get_translation('CONCEPTS','Concepts')."</th>
		<th>".get_translation('COUNT_PATIENT_NUMBER_SHORT','Nb de patients')."</th>
		<th>".get_translation('PERCENTAGE_RESULT','% r�sultat')."</th>
		</tr>
	</thead>
	</table>";
	
print "<h2>".get_translation('GENE_ONTOLOGY_ON_CODED_GENES','Gene ontology sur les g�nes cod�s')."</h2>";
print "<table class=\"tablefin\" id=\"id_tableau_go_data\">";
print "<thead>
	<tr>
		<th>".get_translation('CATEGORIES','Cat�gories')."</th>
		<th>".get_translation('CONCEPTS','Concepts')."</th>
		<th>".get_translation('GENES','G�nes')."</th>
		<th>".get_translation('COUNT_PATIENT_NUMBER_SHORT','Nb de patients')."</th>
	</tr>
	</thead>
	</table>";
print "<h2>".get_translation('GENES_EXTRACTED_FROM_DOCUMENTS','G�nes extraits des comptes rendus')."	</h2>";
print "<table class=\"tablefin\" id=\"id_tableau_genes\">
	<thead>
		<tr>
		<th>".get_translation('CONCEPTS','Concepts')."</th>
		<th>".get_translation('COUNT_PATIENT_NUMBER_SHORT','Nb de patients')."</th>
		<th>".get_translation('PERCENTAGE_RESULT','% r�sultat')."</th>
		<th>".get_translation('PERCENTAGE_DATAWAREHOUSE','% entrep�t')."</th>
		<th>".get_translation('PERCENTAGE_RESULT_VS_DATAWAREHOUSE','% resultat / entrep�t')."</th>
		</tr>
	</thead>
	</table>";

print "<h2>".get_translation('GENE_ONTOLOGY','Gene ontology')."</h2>";
print "<table class=\"tablefin\" id=\"id_tableau_go\">";
print "<thead>
	<tr>
		<th>".get_translation('CATEGORIES','Cat�gories')."</th>
		<th>".get_translation('CONCEPTS','Concepts')."</th>
		<th>".get_translation('GENES','G�nes')."</th>
		<th>".get_translation('COUNT_PATIENT_NUMBER_SHORT','Nb de patients')."</th>
	</tr>
	</thead>
	</table>";

if ($_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_droit_see_debug']=='ok') {
	print "<h2>".get_translation('GENES_IN_TEXT','G�nes dans le texte')."</h2>";
	print "<div id=\"id_repartition_concepts_resumer_texte\"></div>";
}
?>