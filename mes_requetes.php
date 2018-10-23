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
include "javascript_requete.php";
session_write_close();
?>

<h1><? print get_translation('MY_QUERIES','Mes requêtes'); ?></h1>

<table border="0">
<tr>
	<td style="vertical-align:top;width:450px;" nowrap="nowrap">
		<h2><? print get_translation('MY_QUERIES','Mes requêtes'); ?> :</h2>
		<div id="id_div_liste_requete">
			<ul>
		<?
		  get_my_queries($user_num_session);
		?>
			</ul>
		</div>
	</td>
	<td style="vertical-align:top;">
		<div id="id_div_ma_requete" style="display:block;">
			<? 
			if ($_GET['query_num_voir'] !='') { 
				$query_num_voir=$_GET['query_num_voir'];
				$autorisation_requete_voir=autorisation_requete_voir ($query_num_voir,$user_num_session);
				if ($autorisation_requete_voir=='ok') {
					
					$sel_vardroit=oci_parse($dbh,"select title_query,user_num,datamart_num,XML_QUERY,crontab_query,crontab_periode from dwh_query where user_num=$user_num_session  and query_num=$query_num_voir");
				        oci_execute($sel_vardroit);
				        $r=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
			                $title_query=$r['TITLE_QUERY'];
			                $user_num=$r['USER_NUM'];
			                $num_datamart_requete=$r['DATAMART_NUM'];
					if ($r['XML_QUERY']) {
						$xml=$r['XML_QUERY']->load();
					}
			                $crontab_periode=$r['CRONTAB_PERIODE'];
			                $crontab_query=$r['CRONTAB_QUERY'];
			                $readable_query=readable_query ($xml) ;
			                
			                if ($crontab_query==1) {
			                	$check_crontab='checked';
			                } else {
			                	$check_crontab='';
			                }
			                if ($crontab_periode=='month') {
			                	$select_mois='selected';
			                	$select_semaine='';
			                	$select_jour='';
			                } 
			                if ($crontab_periode=='week') {
			                	$select_mois='';
			                	$select_semaine='selected';
			                	$select_jour='';
			                } 
			                if ($crontab_periode=='day') {
			                	$select_mois='';
			                	$select_semaine='';
			                	$select_jour='selected';
			                } 
			                $sel_pat=oci_parse($dbh,"select title_datamart from dwh_datamart where datamart_num=$num_datamart_requete");
			                oci_execute($sel_pat);
			                $r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC);
			                $title_datamart=$r_pat['TITLE_DATAMART'];
			                
				
					print "<h2>".get_translation('QUERY','Requête')." : ";
					print "<span id=\"id_titre_requete\" onclick=\"plier('id_titre_requete');deplier('id_titre_requete_modifier','inline');\">
						<span id=\"id_sous_span_titre_requete\">$title_query</span>
						 <img src=\"images/poubelle_moyenne.png\" border=0 onclick=\"supprimer_requete($query_num_voir);\" style=\"cursor:pointer;vertical-align:middle\">
						 <a href=\"moteur.php?action=preremplir_requete&query_num=$query_num_voir\"><img src=\"images/search.png\" style=\"cursor:pointer;vertical-align:middle\" border=\"0\"></a>
					 </span>";
					print "<span id=\"id_titre_requete_modifier\" style=\"display:none;\"><input type='text' size='50' id='id_input_titre_requete' value=\"$title_query\"> <input class=\"form_submit\" type=\"button\" value=\"Ok\" onclick=\"modifier_requete($query_num_voir);\"></span>";
					print "</h2>";
					print get_translation('SAVED_QUERY_MONTHLY_EXECUTION',"Executer automatiquement cette requête tous les mois");
					print " : <input type=\"checkbox\" id=\"id_crontab_requete\" name=\"crontab_query\" value=\"1\" $check_crontab onclick=\"modifier_requete($query_num_voir);\"> <br>
									<input type=\"hidden\" id=\"id_crontab_periode\" name=\"crontab_periode\" value=\"month\"> 
									<!--Périodicité : 
									<select id=\"id_crontab_periode\" name=\"crontab_periode\" onchange=\"modifier_requete($query_num_voir);\">
										<option value='month' $select_mois>monthly</option>
										<option value='week' $select_semaine>weekly</option>
										<option value='day' $select_jour>dayly</option>
									</select> <br>-->";
					
					print "<input type=\"hidden\" value=\"$query_num_voir\" id=\"id_query_num_voir\">";
					print "
					<br>";
					print "<div id=\"id_div_cohorte_presentation_generale\" class=\"div_result\" style=\"display:inline;width:100%;\" >";
						if ($num_datamart_requete==0) {
							print get_translation('ON_THE_ENTIRE_DATAWAREHOUSE',"Sur l'ensemble de l'entrepôt")."<br>";
						} else {
							print get_translation('ON_THE_DATAMART','Sur le datamart')." $title_datamart<br>";
						}
						print "<br> $readable_query<br>";
					print "</div>";
					
					
					lister_requete_detail ($query_num_voir);
				} else {
					print get_translation('YOU_CANNOT_SEE_THIS_QUERY',"Vous n'êtes pas autorisé à voir cette requête");
				}
			} 
			
			if ($_GET['action'] =='all_queries') { 
				print "<h2>".get_translation('MY_QUERIES','Mes requêtes')."</h2><div>";
				$sel=oci_parse($dbh,"select QUERY_NUM ,title_query,  QUERY_DATE from dwh_query where user_num=$user_num_session and query_type='sauve' order by query_date desc");
			        oci_execute($sel);
			        while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
			                $title_query=$r['TITLE_QUERY'];
			                $query_num=$r['QUERY_NUM'];
			         	print "<input type=checkbox value=\"$query_num\" id=\"id_checkbox_query_$query_num\" name=\"checkbox_query\" checked > <label for=\"id_checkbox_query_$query_num\">$title_query</label> ";
			         	
			        }
			        print "</div>";
				print "<h2>".get_translation('MY_COHORTS_EXCLUDED','Mes cohortes exclues')."</h2><div>";
				$sel=oci_parse($dbh,"select cohort_num ,title_cohort  from dwh_cohort where user_num=$user_num_session  
				or cohort_num in (select cohort_num from dwh_cohort_user_right where user_num=$user_num_session  ) 
				order by title_cohort desc");
			        oci_execute($sel);
			        while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
			                $cohort_num=$r['COHORT_NUM'];
			                $title_cohort=$r['TITLE_COHORT'];
			         	print "<input type=checkbox value=\"$cohort_num\" id=\"id_checkbox_cohort_$cohort_num\" name=\"checkbox_cohort\" checked > <label for=\"id_checkbox_cohort_$cohort_num\">$title_cohort</label> ";
			         	
			        }
			        print "</div>";
			        print "<input type=\"button\" onclick=\"display_patients_all_queries();\" value=\"Refresh\">";
			        print "<h2> Patients </h2>";
			        print "<div id=\"id_div_all_queries_patients\"><div>";
			        
			        
				print "<script language=\"javascript\">jQuery(document).ready(function() { 
					display_patients_all_queries () ;
				});
				</script>";
			}
			?>
		</div>
	</td>
</tr>
</table>
<? save_log_page($user_num_session,'my_queries'); ?>
<? include "foot.php"; ?>