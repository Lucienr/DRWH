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
<script type="text/javascript" src="javascript_stat.js?v=<? print $date_today_unique; ?>"></script>
<?

	if ($_SESSION['dwh_droit_all_departments'.$datamart_num]=='') {
		print "
		<h1 onclick=\"plier_deplier('id_div_stat_patient_out_user_department');\" style=\"cursor:pointer\">
			<span id=\"plus_id_div_stat_patient_out_user_department\">+</span> ".get_translation('INFORMATION_ON_PATIENTS_NOT_IN_SERVICE',"Informations sur les patients éligibles n'étant pas passés par votre service")."
		</h1>";
		print "<div id=\"id_div_stat_patient_out_user_department\" style=\"float:left;display:none;width:100%\">";
		 //  	lister_services_nbpatient_manager_department ($tmpresult_num,$query_num); 
		print "<div id=\"id_nb_nouveau_patients_service_hors_mespatients\" style=\" width:900px;height:800px;\"></div>
		</div>";
	}
	
	print "
	<h1  onclick=\"plier_deplier('id_div_stat_on_document_found');\" style=\"cursor:pointer;clear: left;padding-top:20px;\">
	<span id=\"plus_id_div_stat_on_document_found\">+</span> ".get_translation('STAT_BASED_ON_DOCUMENTS_FOUND','Statistiques basées sur les documents retrouvés')."
	</h1>
	<div id=\"id_div_stat_on_document_found\" style=\"float:left;display:none;width:100%\">
		
		<div id=\"id_pyramide_age_document\" style=\"float:left;width: 600px; height: 400px;\"></div>
		<div id=\"id_pyramide_age_today\" style=\"float:left;width: 600px; height: 400px;\"></div>
		<br><br>
		<div id=\"id_pyramide_age_document_jeune\" style=\"float:left;width: 600px; height: 400px;\"></div>
		<div id=\"id_pyramide_age_today_jeune\" style=\"float:left;width: 600px; height: 400px;\"></div>
	
		<div id=\"id_sex_repartition\" style=\"float:left;width: 600px; height: 400px;\"></div>
		<div id=\"id_document_origin_codeument_repartition\" style=\"float:left; width: 600px; height: 400px;\"></div>
		
		<div id=\"id_nb_nouveaux_patient_graph\" style=\"float:left; width:900px;height:400px;\"></div>
		<div id=\"id_nb_nouveaux_patient_tableau\" style=\"float:left; \"></div>
		<div id=\"id_nb_nouveau_patients_service\" style=\"float:left; width:900px;height:800px;\"></div>
	</div>
	
	<h1  onclick=\"plier_deplier('id_div_stat_on_mvt_patients_found');\" style=\"cursor:pointer;clear: left;padding-top:20px;\">
		<span id=\"plus_id_div_stat_on_mvt_patients_found\">+</span> 
		".get_translation('STAT_BASED_ON_MVT_PATIENTS_FOUND','Statistiques basées sur les passages des patients retrouvés')."  <a href=\"export_excel.php?tmpresult_num=$tmpresult_num&option=stat_movment\"><img src=\"images/excel_noir.png\" border=\"0\"></a>
	</h1>
	<div id=\"id_div_stat_on_mvt_patients_found\" style=\"float:left;display:none;width:100%\">
		<br><strong>Interactif :</strong></br>
		<a href=\"parcours_moyen_d3.php?tmpresult_num=$tmpresult_num\" target=\"_blank\">".get_translation('DISPLAY_AVERAGE_JOURNEY_HOSPIT','Afficher le parcours moyen (hospitalisations) des patients')."</a><br>
		<a href=\"parcours_complet_d3.php?tmpresult_num=$tmpresult_num\" target=\"_blank\">".get_translation('DISPLAY_AVERAGE_JOURNEY_HOSPIT_CONSULT','Afficher le parcours moyen complet (hospitalisations et consultations) des patients')."</a><br>
		<br><strong>Images :</strong><br>
		<a href=\"parcours_moyen.php?tmpresult_num=$tmpresult_num\" target=\"_blank\">".get_translation('DISPLAY_AVERAGE_JOURNEY_HOSPIT','Afficher le parcours moyen (hospitalisations) des patients')."</a><br>
		<a href=\"parcours_complet.php?tmpresult_num=$tmpresult_num\" target=\"_blank\">".get_translation('DISPLAY_AVERAGE_JOURNEY_HOSPIT_CONSULT','Afficher le parcours moyen complet (hospitalisations et consultations) des patients')."</a><br>
		<div id=\"id_nb_consult_per_unit_per_year_tableau\" style=\"float:none; \"></div>
		<div id=\"id_nb_hospit_per_unit_per_year_tableau\" style=\"float:none; \"></div>
		<div id=\"id_nb_patient_per_unit_per_year_tableau\" style=\"float:none; \"></div>
		<div id=\"id_nb_stay_per_unit_per_year_tableau\" style=\"float:none; \"></div>
	</div>";
	
	
	
	

?>