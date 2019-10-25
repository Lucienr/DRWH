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
include "head.php";
include "menu.php";
session_write_close();
?>

	<div style="padding-left:3px;">
		<h1>Etat de chargement de l'entrepôt :</h1>
		
		<?
			afficher_etat_entrepot('document_origin_code_an_mois_presence','1000px','','','');
			
			$sel_var1=oci_parse($dbh,"select to_char(min(last_execution_date),'DD/MM/YYYY') as min_char_last_execution_date from dwh_etl_script");
			oci_execute($sel_var1);
			$r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC);
			$min_char_last_execution_date=$r['MIN_CHAR_LAST_EXECUTION_DATE'];
			
			$sel_var1=oci_parse($dbh,"select year,month-1 as month from dwh_info_load where  year is not null and year>1995  and year <=to_char(sysdate,'YYYY') and month is not null order by year asc,month asc");
			oci_execute($sel_var1);
			$r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC);
			$annee_min=$r['YEAR'];
			$mois_min=$r['MONTH'];
			$an_mois_min="$annee_min,$mois_min";
			
			$sel_var1=oci_parse($dbh,"select year,month-1 as month from dwh_info_load where  year is not null and year>1995  and year <=to_char(sysdate,'YYYY') and month is not null order by year desc,month desc");
			oci_execute($sel_var1);
			$r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC);
			$annee_max=$r['YEAR'];
			$mois_max=$r['MONTH'];
			$an_mois_max="$annee_max,$mois_max";
			
			
			print "<br><h3 style=\"color: #333333;fill: #333333;font-size: 18px;font-family:Lucida Sans Unicode;font-weight:normal;\">".get_translation('DOCUMENTS_NUMBER_OVER_TIME','Nombre de documents au cours du temps')."</h3>";
			afficher_etat_entrepot('document_origin_code_an_mois','1000px','','','');
			
			
			foreach ($tableau_global_document_origin_code as $document_origin_code => $document_origin_str) {
				print "<h2 style=\"cursor:pointer\" onclick=\"plier_deplier('id_nb_document_temps_$document_origin_code');\">+ $document_origin_str</h2>";
				print "<div id=\"id_nb_document_temps_$document_origin_code\" style=\"display:none;\">";
				 afficher_etat_entrepot('document_origin_code_an_mois_unitaire','1000px',$document_origin_code,$an_mois_min,$an_mois_max);
				 print "</div>";
			}
		?>
	</div>
	
<? include "foot.php"; ?>