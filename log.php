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
<style>
.div_log {   
	float: left;
	font-size: 14px;
	max-width: 700px;
	padding-left: 20px;
	padding-top: 20px;
	position: relative;
	text-align: left;
}

.div_log h3  {   
 	font-size: 14px; 
}
.div_log h2  {   
	font-size: 20px; 
	color: #e00adb; 
}
</style>
<h1><? print get_translation('LOG','Log'); ?></h1>
<?
		
		
		
print "
<div class=\"div_log\">
	<h2>".get_translation('THE_USERS','Les utilisateurs')."</h2>";
		
	
	$requete="select count(distinct user_num) as nb_user_distinct from dwh_log_page where user_num !=1 and page='connexion' ";
	$sel=oci_parse($dbh,$requete);
	oci_execute($sel) || die ("erreur requete $requete\n");
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb_user_distinct=$r['NB_USER_DISTINCT'];
	
	$requete="select count( user_num) as nb_connexions from dwh_log_page where user_num !=1 and page='connexion' ";
	$sel=oci_parse($dbh,$requete);
	oci_execute($sel) || die ("erreur requete $requete\n");
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb_connexions=$r['NB_CONNEXIONS'];
		
		
	print "<h3>$nb_user_distinct ".get_translation('DISTINCT_CONNECTED_USERS','utilisateurs distinct connectés')." $nb_connexions ".get_translation('TIMES','fois')."</h3>";
	
	$liste_nb_user='';
	$requete="select  to_char(log_date,'YYYY') as year , to_char(log_date,'MM') as month ,count( user_num) as nb_user from dwh_log_page where user_num !=1 and page='connexion' group by to_char(log_date,'YYYY'),to_char(log_date,'MM')  order by to_char(log_date,'YYYY'),to_char(log_date,'MM')";
	$sel=oci_parse($dbh,$requete);
	oci_execute($sel) || die ("erreur requete $requete\n");
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$year=$r['YEAR'];
		$month=$r['MONTH']-1;
		$nb_user=$r['NB_USER'];
		$liste_nb_user.="[Date.UTC($year,$month), $nb_user],";
	}
	$liste_nb_user=substr($liste_nb_user,0,-1);
	$series="{yAxis: 0,
		name: \"".get_translation('JS_SEARCH','Recherche')."\",
		type: 'column',
		data: [$liste_nb_user],
		color :'#0B3861'
		}";
	graph_temps ($series,get_translation('JS_COUNT_CONNEXIONS_PER_MONTH',"Nb de connexions par mois à l'entrepôt"),"nb_connexion_mois","650px","300px",get_translation('JS_CONNEXIONS_COUNT','Nb connexions'));

print "</div>";	
		
print "
<div class=\"div_log\">
	<h2>".get_translation('THE_SEARCHES','Les recherches')."</h2>";
	$requete="select count(*) as nb_requetes from DWH_LOG_QUERY where user_num !=1 and query_context='engine' ";
	$sel=oci_parse($dbh,$requete);
	oci_execute($sel) || die ("erreur requete $requete\n");
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb_requetes=$r['NB_REQUETES'];
	print "<h3>$nb_requetes ".get_translation('EXECUTED_QUERIES','requêtes executés')."</h3>";
	
	#/* REQUETES FAITES par MOIS*/
	$liste_nb_requetes='';
	$requete="select  to_char(log_date,'YYYY') as year , to_char(log_date,'MM') as month ,query_context,count(*) as nb_requetes from DWH_LOG_QUERY where user_num !=1 and query_context='engine' group by to_char(log_date,'YYYY'),to_char(log_date,'MM'),query_context  order by to_char(log_date,'YYYY'),to_char(log_date,'MM'),query_context";
	$sel=oci_parse($dbh,$requete);
	oci_execute($sel) || die ("erreur requete $requete\n");
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$year=$r['YEAR'];
		$month=$r['MONTH']-1;
		$nb_requetes=$r['NB_REQUETES'];
		$liste_nb_requetes.="[Date.UTC($year,$month), $nb_requetes],";
	}
	$liste_nb_requetes=substr($liste_nb_requetes,0,-1);
	$series="{yAxis: 0,
		name: \"".get_translation('JS_SEARCH','Recherche')."\",
		type: 'column',
		data: [$liste_nb_requetes],
		color :'#0B3861'
		}";
	graph_temps ($series,get_translation('JS_COUNT_SEARCH_PER_MONTH',"Nb de recherches par mois sur l'entrepôt"),"Recherche","650px","300px",get_translation('JS_COUNT_SEARCH','Nb recherches'));
print "</div>";



print "<div class=\"div_log\">
<h2>".get_translation('THE_COHORTS','Les cohortes')."</h2>";
	#/* COHORTES */
	
	$requete="select count(*) nb_cohortes from dwh_cohort where user_num!=1 ";
	$sel=oci_parse($dbh,$requete);
	oci_execute($sel) || die ("erreur requete $requete\n");
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb_cohortes=$r['NB_COHORTES'];
	print "<h3>$nb_cohortes ".get_translation('CREATED_COHORTS','cohortes créées')."</h3>";
	
	$requete="select count(*) nb_traite from dwh_cohort_result where user_num_add!=1  ";
	$sel=oci_parse($dbh,$requete);
	oci_execute($sel) || die ("erreur requete $requete\n");
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb_traite=$r['NB_TRAITE'];
	
	$requete="select count(*) nb_inclu from dwh_cohort_result where user_num_add!=1 and status=1 ";
	$sel=oci_parse($dbh,$requete);
	oci_execute($sel) || die ("erreur requete $requete\n");
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb_inclu=$r['NB_INCLU'];
	print "<h3>$nb_inclu ".get_translation('X_PATIENTS_INCLUDED_OUT_OF_Y','patients inclus sur')." $nb_traite ".get_translation('TREATED','traités')."</h3>";
	

	#/* REQUETES FAITES par MOIS*/
	$liste_nb_cohortes='';
	$requete="select  to_char(cohort_date,'YYYY') as year , to_char(cohort_date,'MM') as month ,count(*) as nb_cohortes from dwh_cohort where user_num !=1  group by to_char(cohort_date,'YYYY'),to_char(cohort_date,'MM')  order by to_char(cohort_date,'YYYY'),to_char(cohort_date,'MM')";
	$sel=oci_parse($dbh,$requete);
	oci_execute($sel) || die ("erreur requete $requete\n");
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$year=$r['YEAR'];
		$month=$r['MONTH']-1;
		$nb_cohortes=$r['NB_COHORTES'];
		$liste_nb_cohortes.="[Date.UTC($year,$month), $nb_cohortes],";
	}
	$liste_nb_cohortes=substr($liste_nb_cohortes,0,-1);
	$series="{yAxis: 0,
		name: \"".get_translation('JS_COHORTS','Cohortes')."\",
		type: 'column',
		data: [$liste_nb_cohortes],
		color :'#0B3861'
		}";
	graph_temps ($series,get_translation('JS_COUNT_CREATED_COHORTS_PER_MONTH','Nb de cohortes créées par mois'),"nb_cohorte_cree","650px","300px",get_translation('JS_COHORT_COUNT','Nb cohortes'));



	#/* cohorte ajout patient */
	$liste_nb_patient='';
	$requete="select  to_char(add_date,'YYYY') as year , to_char(add_date,'MM') as month ,count(*) as nb_patient from dwh_cohort_result where user_num_add !=1 and add_date is not null
		  group by to_char(add_date,'YYYY'),to_char(add_date,'MM')  order by to_char(add_date,'YYYY'),to_char(add_date,'MM')";
	$sel=oci_parse($dbh,$requete);
	oci_execute($sel) || die ("erreur requete $requete\n");
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$year=$r['YEAR'];
		$month=$r['MONTH']-1;
		$nb_patient=$r['NB_PATIENT'];
		$liste_nb_patient.="[Date.UTC($year,$month), $nb_patient],";
	}
	$liste_nb_patient=substr($liste_nb_patient,0,-1);
	$series="{yAxis: 0,
		name: \"".get_translation('JS_PATIENTS','Patients')."\",
		type: 'column',
		data: [$liste_nb_patient],
		color :'#0B3861'
		}";
	graph_temps ($series,get_translation('JS_COUNT_INCLUDED_PATIENTS_PER_MONTH','Nb patients inclus par mois'),"nb_patient_inclu","650px","300px",get_translation('JS_COUNT_PATIENTS','Nb patients'));



print "</div>";




print "<div class=\"div_log\">
<h2>".get_translation('THE_PATIENTS','Les patients')."</h2>";
	$requete="select count(*) as nb_patient from DWH_LOG_PATIENT where log_context='dossier' and user_num!=1";
	$sel=oci_parse($dbh,$requete);
	oci_execute($sel) || die ("erreur requete $requete\n");
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb_patient=$r['NB_PATIENT'];
	print "<h3>$nb_patient ".get_translation('ACCESSED_PATIENT_FILES','dossiers patients accédés')."</h3>";

	$liste_nb_patients='';
	$requete="select  to_char(log_date,'YYYY') as year , to_char(log_date,'MM') as month ,count(*) as nb_patients from dwh_log_patient where user_num !=1 and log_context='dossier' group by to_char(log_date,'YYYY'),to_char(log_date,'MM')  order by to_char(log_date,'YYYY'),to_char(log_date,'MM')";
	$sel=oci_parse($dbh,$requete);
	oci_execute($sel) || die ("erreur requete $requete\n");
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$year=$r['YEAR'];
		$month=$r['MONTH']-1;
		$nb_patients=$r['NB_PATIENTS'];
		$liste_nb_patients.="[Date.UTC($year,$month), $nb_patients],";
	}
	$liste_nb_patients=substr($liste_nb_patients,0,-1);
	$series="{yAxis: 0,
		name: \"".get_translation('JS_COHORTS','Cohortes')."\",
		type: 'column',
		data: [$liste_nb_patients],
		color :'#0B3861'
		}";
	graph_temps ($series,get_translation('JS_COUNT_PATIENT_FILES_VIEWS_PER_MONTH','Nb dossiers patients visualisés par mois'),"nb_patients_accedes","650px","300px",get_translation('JS_COUNT_PATIENTS_SEEN','Nb patients vus'));
	
	
	
	
	$liste_nb_requetes='';
	$requete="select  to_char(log_date,'YYYY') as year , to_char(log_date,'MM') as month ,query_context,count(*) as nb_requetes from DWH_LOG_QUERY where user_num !=1 and query_context='patient' group by to_char(log_date,'YYYY'),to_char(log_date,'MM'),query_context  order by to_char(log_date,'YYYY'),to_char(log_date,'MM'),query_context";
	$sel=oci_parse($dbh,$requete);
	oci_execute($sel) || die ("erreur requete $requete\n");
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$year=$r['YEAR'];
		$month=$r['MONTH']-1;
		$nb_requetes=$r['NB_REQUETES'];
		$liste_nb_requetes.="[Date.UTC($year,$month), $nb_requetes],";
	}
	$liste_nb_requetes=substr($liste_nb_requetes,0,-1);
	$series="{yAxis: 0,
		name: \"".get_translation('JS_SEARCH','Recherche')."\",
		type: 'column',
		data: [$liste_nb_requetes],
		color :'#0B3861'
		}";
	graph_temps ($series,get_translation('JS_COUNT_SEARCH_ON_DOCUMENT_PER_MONTH','Nb de recherches sur dossier patient par mois'),"Recherche_dans_patient","650px","300px",get_translation('JS_COUNT_SEARCH_IN_PATIENT_FILE','Nb recherches dans dossier patient'));
	
	
	$liste_nb_requetes='';
	$requete="select  to_char(log_date,'YYYY') as year , to_char(log_date,'MM') as month ,query_context,count(*) as nb_requetes from DWH_LOG_QUERY where user_num !=1 and query_context='patienttimeline' group by to_char(log_date,'YYYY'),to_char(log_date,'MM'),query_context  order by to_char(log_date,'YYYY'),to_char(log_date,'MM'),query_context";
	$sel=oci_parse($dbh,$requete);
	oci_execute($sel) || die ("erreur requete $requete\n");
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$year=$r['YEAR'];
		$month=$r['MONTH']-1;
		$nb_requetes=$r['NB_REQUETES'];
		$liste_nb_requetes.="[Date.UTC($year,$month), $nb_requetes],";
	}
	$liste_nb_requetes=substr($liste_nb_requetes,0,-1);
	$series="{yAxis: 0,
		name: \"".get_translation('JS_SEARCH','Recherche')."\",
		type: 'column',
		data: [$liste_nb_requetes],
		color :'#0B3861'
		}";
	graph_temps ($series,get_translation('JS_COUNT_TIMELINE_SEARCH_PER_MONTH','Nb de recherches sur timeline par mois'),"Recherche_dans_patienttimeline","650px","300px",get_translation('JS_COUNT_SEARCH_IN_PATIENT_FILE','Nb recherches dans dossier le patient'));

print "</div>";



print "<div class=\"div_log\">
<h2>".get_translation('THE_USED_TOOLS','Les outils utilisés')."</h2>";
	
	#/* PAGES VISITES */
	# /* pages visités */
	$liste_nb_page_mois='';
	$selp=oci_parse($dbh,"select  distinct page from DWH_LOG_PAGE order by page");
	oci_execute($selp);
	while ($rp=oci_fetch_array($selp,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$req_page=$rp['PAGE'];
		
		$liste_nb_page='';
		$ajouter_min='';
		$ajouter_max='';
		$requete="select   to_char(log_date,'YYYY') as year , to_char(log_date,'MM') as month ,page,count(*) NB_PAGES from DWH_LOG_PAGE where user_num !=1 and page='$req_page' and log_date is not null group by to_char(log_date,'YYYY'),to_char(log_date,'MM'),page  order by to_char(log_date,'YYYY'),to_char(log_date,'MM'),page";
		$sel=oci_parse($dbh,$requete);
		oci_execute($sel) || die ("erreur requete $requete\n");
		while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$year=$r['YEAR'];
			$month=$r['MONTH']-1;
			$nb_pages=$r['NB_PAGES'];
			$liste_nb_page.="[Date.UTC($year,$month), $nb_pages],";
		}
		$libelle_page=$tableau_global_page[$req_page];
		$liste_nb_page=substr($liste_nb_page,0,-1);
		$series="{yAxis: 0,
			name: \"$libelle_page\",
			type: 'column',
			data: [$liste_nb_page],
			color :'#0B3861'
			}";
		$page=preg_replace ("/ /","_",$req_page);
		graph_temps ($series,get_translation('JS_CLICK_PER_MONTH','Nb clics par mois')." - $page","$page","650px","300px",get_translation('JS_COUNT_VISITS','Nb visites'));
		
	}

print "</div>";
 
 
?>
	</div><br><br>
<?

function graph_temps ($series,$title,$id,$width,$height,$titre_y) {
	print "
		<div id=\"id_graph_temps_$id\" style=\"width:$width;height:$height;\"></div>
		<script type=\"text/javascript\">
		jQuery(function () {
			var chart;
			jQuery(document).ready(function() {
		    	$('#id_graph_temps_$id').highcharts({
					chart: {
						type: 'column',
						polar: false,
						zoomType: 'x'
					},
					credits: {
						enabled: false
					}
					,plotOptions: {
						series: {
							stacking: 'normal'
						}
					},
					legend: {
						enabled: false
					},
					title: {
						text: \"$title\"
					},
					subtitle: {
						text: ''
					},
					xAxis: {
						type: 'datetime',
						dateTimeLabelFormats: {
							day: '%Y'
						},
						labels: {
							rotation: 90,
							align: 'left'
						}
					},
					yAxis : [{
						labels: {
							formatter: function() {
								return this.value +'';
							},
							style: {
								color: '#0B3861'
							}
						},
						title: {
							text: \"$titre_y\",
							style: {
								color: '#0B3861'
							}
						},
						opposite: false
					}],
					
					series: [$series]
				});
			});
		});
		</script>
		";

}
?>






<?
	include "foot.php"; 
?>