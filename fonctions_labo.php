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
  
 function rechercher_examen_thesaurus_labo ($requete_texte,$thesaurus_data_father_num,$sans_filtre,$tmpresult_num) {
	global $dbh,$thesaurus_code_labo;
	$requete_texte=nettoyer_pour_requete(trim($requete_texte));
	
	if ($requete_texte!='') {
		$requete_texte=preg_replace("/\s+/"," ",$requete_texte);
		$requete_texte_sans_pourcent=preg_replace("/\s/"," and ",$requete_texte);
		$requete_texte_avec_pourcent=preg_replace("/\s/","% and ",$requete_texte);
		
		$compteur=0;
		
		if (preg_match("/^[0-9]+$/",$requete_texte)) {
			$req_num_thesaurus=" or thesaurus_data_num='$requete_texte' ";
		} else {
			$req_num_thesaurus="";
		}
		
		if ($sans_filtre=='') {
			$req="select distinct a.thesaurus_data_num,concept_code,concept_str,info_complement,measuring_unit,count_data_used ,value_type
			from dwh_thesaurus_data a,
	           dwh_thesaurus_data_graph b
	           where 
	            a.thesaurus_code='$thesaurus_code_labo' and  
	           a.thesaurus_code=b.thesaurus_code and 
	           a.thesaurus_data_num=b.thesaurus_data_son_num and 
	           b.thesaurus_data_father_num=$thesaurus_data_father_num  and 
	           distance=1  and 
	           ( ( contains(description,'$requete_texte_avec_pourcent%')>0 or contains(description,'$requete_texte')>0    $req_num_thesaurus )
	           or a.thesaurus_data_num in (select thesaurus_data_father_num from dwh_thesaurus_data a, dwh_thesaurus_data_graph b where    a.thesaurus_code='$thesaurus_code_labo' and  
	           a.thesaurus_code=b.thesaurus_code and a.thesaurus_data_num=b.thesaurus_data_son_num and    ( contains(description,'$requete_texte_avec_pourcent%')>0 or contains(description,'$requete_texte')>0  $req_num_thesaurus )))
	           
			";
			$selobx=oci_parse($dbh,"$req");
		} else {
			$selobx=oci_parse($dbh,"select distinct a.thesaurus_data_num,concept_code,concept_str,info_complement,measuring_unit,count_data_used ,value_type
			from dwh_thesaurus_data a,
	           dwh_thesaurus_data_graph b
	           where 
	            a.thesaurus_code='$thesaurus_code_labo' and  
	           a.thesaurus_code=b.thesaurus_code and 
	           a.thesaurus_data_num=b.thesaurus_data_son_num and 
	           b.thesaurus_data_father_num=$thesaurus_data_father_num  and 
	           distance=1    ");
		}
		oci_execute($selobx);
		while ($r_varobx=oci_fetch_array($selobx)) {
			if ($r_varobx) {
				foreach ($r_varobx as $var => $val) {
					$var=strtolower($var);
					$$var=$val;
				}
			}
			
			$compteur++;
			
			
			// verif si libelle contient fils
			$sel=oci_parse($dbh,"select 1 as test_fils from dwh_thesaurus_data where thesaurus_parent_num=$thesaurus_data_num");
			oci_execute($sel);
			$r=oci_fetch_array($sel);
			$test_fils=$r['TEST_FILS'];
			
			if ($test_fils==1) {
				// verif si libelle contient element recherche. Si oui, on affiche tous les fils de l element, sans filtre
				
				if ($sans_filtre=='') {
					$sel=oci_parse($dbh,"select 1 as test_fils from dwh_thesaurus_data where thesaurus_data_num=$thesaurus_data_num and (contains(description,'$requete_texte_avec_pourcent%')>0 or contains(description,'$requete_texte_sans_pourcent%')>0)");
					oci_execute($sel);
					$r=oci_fetch_array($sel);
					$test_terme_rech_dans_libelle=$r['TEST_FILS'];
				}
				
				if ($test_terme_rech_dans_libelle==1 || $sans_filtre!='') {
					print "<div id=\"id_div_thesaurus_data_labo_$thesaurus_data_num\" style=\"display:block;cursor:pointer;font-size: 12px;\" onclick=\"rechercher_code_sous_thesaurus_labo ($thesaurus_data_num,'sans_filtre');\">";
				} else {
					print "<div id=\"id_div_thesaurus_data_labo_$thesaurus_data_num\" style=\"display:block;cursor:pointer;font-size: 12px;\" onclick=\"rechercher_code_sous_thesaurus_labo ($thesaurus_data_num,'');\">";
				}
				
				print "<span id=\"plus_id_div_thesaurus_sous_data_labo_".$thesaurus_data_num."\">+</span> $concept_str";
				if ($measuring_unit!='') { 
					print" ($measuring_unit)";
				}
				if ($info_complement!='') { 
					print"$info_complement";
				}
				print " ($count_data_used) ";
				if ($value_type!='parent' && $value_type!='') {
					print "<input name=\"bouton_radio_labo\" type=radio onclick=\"visualiser_data_labo($thesaurus_data_num);\">";
				} 
				print "
				</div>
				<div id=\"id_div_thesaurus_sous_data_labo_".$thesaurus_data_num."\" style=\"display:none;padding-left:15px;\">
				</div>";
			} else {
			
				$sel=oci_parse($dbh,"select count(distinct patient_num) nb_patient_reel from dwh_tmp_result where tmpresult_num=$tmpresult_num and patient_num in (select patient_num from dwh_data where thesaurus_data_num=$thesaurus_data_num)");
				oci_execute($sel);
				$r=oci_fetch_array($sel);
				$count_data_used=$r['NB_PATIENT_REEL'];
				print "
				<div id=\"id_div_thesaurus_data_concept_$thesaurus_data_num\" style=\"display:block;font-size: 12px;\">
					 $concept_str";
				if ($measuring_unit!='') { 
					print" ($measuring_unit)";
				}
				if ($info_complement!='') { 
					print"$info_complement";
				}
				print " ($count_data_used) ";
				if ($value_type!='parent' && $value_type!='') {
					print "<input name=\"bouton_radio_labo\" type=radio onclick=\"visualiser_data_labo($thesaurus_data_num);\">";
				} 
				print "
				</div>
				";
			}
			
		}
		
		if ($compteur==0) {
			print "<tr><td>".get_translation('NO_RESULT_FOUND','Aucun résultat trouvé')."";
		}
	}
}         
           
 function visualiser_graph_groupe ($thesaurus_data_num,$tmpresult_num) {
	global $dbh;
	
	$sel_patient_num=oci_parse($dbh,"select distinct patient_num from dwh_tmp_result where tmpresult_num=$tmpresult_num");
	oci_execute($sel_patient_num);
	while ($r_patient_num=oci_fetch_array($sel_patient_num)) {
		$patient_num=$r_patient_num['PATIENT_NUM'];
		if ($patient_num!='') {
			$sel=oci_parse($dbh,"select val_numeric, lower_bound,upper_bound,to_char(document_date,'DD/MM/YYYY HH24:MI') as date_document_char,document_date  from dwh_data where thesaurus_data_num=$thesaurus_data_num and patient_num=$patient_num order by document_date");
			oci_execute($sel);
			while ($r=oci_fetch_array($sel)) {
				$date_document_char=$r['DATE_DOCUMENT_CHAR'];
				$val_numeric=$r['VAL_NUMERIC'];
				$borne_inf=$r['LOWER_BOUND'];
				$borne_sup=$r['UPPER_BOUND'];
			}
		}
	}
}



           
 function visualiser_tableau_groupe ($thesaurus_data_num,$tmpresult_num) {
	global $dbh;
	print "<table class=\"tablefin\" id=\"id_tableau_visualiser_tableau_groupe\">
	<thead><tr>
		<th>".get_translation('PATIENT','Patient')."</th>
		<th>".get_translation('COUNT_OR_NUMBER_SHORT','nb')."</th>
		<th>".get_translation('MINIMUM_SHORT','min')."</th>
		<th>".get_translation('MAXIMUM_SHORT','max')."</th>
		<th>".get_translation('MEDIAN','Médiane')."</th>
		<th>".get_translation('MEAN_STATISTICS','Moyenne')."</th>
		<th>".get_translation('STANDARD_DEVIATION','Ecart Type')."</th>
		<th>".get_translation('COUNT_VALUES_SHORT','Nb val')." &gt; ".get_translation('UPPER_LIMIT_SHORT','borne sup')."</th>
		<th>% &gt; ".get_translation('UPPER_LIMIT_SHORT','borne sup')."</th>
		<th>".get_translation('COUNT_VALUES_SHORT','Nb Val')." &lt ".get_translation('LOWER_LIMIT_SHORT','borne inf')."</th>
		<th>% &lt ".get_translation('LOWER_LIMIT_SHORT','borne inf')."</th>
		<th>".get_translation('TREND','Trend')."</th>
	</tr></thead>
	<tbody>";
	$sel_patient_num=oci_parse($dbh,"select distinct patient_num from dwh_tmp_result where tmpresult_num=$tmpresult_num");
	oci_execute($sel_patient_num);
	while ($r_patient_num=oci_fetch_array($sel_patient_num)) {
		$patient_num=$r_patient_num['PATIENT_NUM'];
		if ($patient_num!='') {
			$sel=oci_parse($dbh,"select max(val_numeric) as max, min(val_numeric) as min, round(avg(val_numeric),2) as avg, median (val_numeric) as median, round(STDDEV(val_numeric),2) as STDDEV,count(*) as nb  from dwh_data where thesaurus_data_num=$thesaurus_data_num and patient_num=$patient_num ");
			oci_execute($sel);
			$r=oci_fetch_array($sel);
			$max=$r['MAX'];
			$min=$r['MIN'];
			$avg=$r['AVG'];
			$median=$r['MEDIAN'];
			$stddev=$r['STDDEV'];
			$nb=$r['NB'];
			$max=str_replace(",",".",$max);
			$min=str_replace(",",".",$min);
			$avg=str_replace(",",".",$avg);
			$median=str_replace(",",".",$median);
			$stddev=str_replace(",",".",$stddev);
			if ($nb>0) {
				$user_name=afficher_patient ($patient_num,'lastname firstname','','','tableau_groupe_bio');
				
				$sel=oci_parse($dbh,"select count(*) as nb_supborne_sup ,round(100*count(*)/$nb) as pourcent_sup from dwh_data where thesaurus_data_num=$thesaurus_data_num and patient_num=$patient_num and val_numeric>upper_bound");
				oci_execute($sel);
				$r=oci_fetch_array($sel);
				$nb_supborne_sup=$r['NB_SUPBORNE_SUP'];
				$pourcent_sup=$r['POURCENT_SUP'];
				
				$sel=oci_parse($dbh,"select count(*) as nb_infborne_inf ,round(100*count(*)/$nb) as pourcent_inf from dwh_data where thesaurus_data_num=$thesaurus_data_num and patient_num=$patient_num and val_numeric<lower_bound");
				oci_execute($sel);
				$r=oci_fetch_array($sel);
				$nb_infborne_inf=$r['NB_INFBORNE_INF'];
				$pourcent_inf=$r['POURCENT_INF'];
				
				print "<tr><td>$user_name</td><td>$nb</td><td>$min</td><td>$max</td><td>$median</td><td>$avg</td><td>$stddev</td><td>$nb_supborne_sup</td><td>$pourcent_sup</td><td>$nb_infborne_inf</td><td>$pourcent_inf</td>";
				if ($nb>2) {
					//$trend=calcul_tendance_lineare_patient ($patient_num,$thesaurus_data_num);
					print "<td>$trend</td>";
				} else {
					print "<td></td>";
				}
				print "</tr>";
			}
				
			
				
		}
	}
	print "</tbody>";
	
	
	$sel=oci_parse($dbh,"select max(val_numeric) as max, min(val_numeric) as min, round(avg(val_numeric),2) as avg, median (val_numeric) as median, round(STDDEV(val_numeric),2) as STDDEV,count(*) as nb  from dwh_data where thesaurus_data_num=$thesaurus_data_num and patient_num in (select patient_num from dwh_tmp_result where tmpresult_num=$tmpresult_num) ");
	oci_execute($sel);
	$r=oci_fetch_array($sel);
	$max=$r['MAX'];
	$min=$r['MIN'];
	$avg=$r['AVG'];
	$median=$r['MEDIAN'];
	$stddev=$r['STDDEV'];
	$nb=$r['NB'];
				
	$max=str_replace(",",".",$max);
	$min=str_replace(",",".",$min);
	$avg=str_replace(",",".",$avg);
	$median=str_replace(",",".",$median);
	$stddev=str_replace(",",".",$stddev);
	
	$sel=oci_parse($dbh,"select count(*) as nb_supborne_sup ,round(100*count(*)/$nb) as pourcent_sup from dwh_data where thesaurus_data_num=$thesaurus_data_num and patient_num in (select patient_num from dwh_tmp_result where tmpresult_num=$tmpresult_num)  and val_numeric>upper_bound");
	oci_execute($sel);
	$r=oci_fetch_array($sel);
	$nb_supborne_sup=$r['NB_SUPBORNE_SUP'];
	$pourcent_sup=$r['POURCENT_SUP'];
	
	$sel=oci_parse($dbh,"select count(*) as nb_infborne_inf ,round(100*count(*)/$nb) as pourcent_inf from dwh_data where thesaurus_data_num=$thesaurus_data_num and patient_num in (select patient_num from dwh_tmp_result where tmpresult_num=$tmpresult_num)  and val_numeric<lower_bound");
	oci_execute($sel);
	$r=oci_fetch_array($sel);
	$nb_infborne_inf=$r['NB_INFBORNE_INF'];
	$pourcent_inf=$r['POURCENT_INF'];
	
	print "<tfoot>";
	print "<tr><th>".get_translation('TOTAL','Total')."</th><th>$nb</th><th>$min</th><th>$max</th><th>$median</th><th>$avg</th><th>$stddev</th><th>$nb_supborne_sup</th><th>$pourcent_sup</th><th>$nb_infborne_inf</th><th>$pourcent_inf</th><th></th></tr>";
	print "<tr><th>".get_translation('PATIENT','Patient')."</th><th>".get_translation('COUNT_OR_NUMBER_SHORT','nb')."</th><th>".get_translation('MINIMUM_SHORT','min')."</th><th>".get_translation('MAXIMUM_SHORT','max')."</th><th>".get_translation('MEDIAN','Médiane')."</th><th>".get_translation('MEAN_STATISTICS','Moyenne')."</th><th>".get_translation('STANDARD_DEVIATION','Ecart Type')."</th><th>".get_translation('COUNT_VALUES_SHORT','Nb val')." &gt; ".get_translation('UPPER_LIMIT_SHORT','borne sup')."</th><th>% &gt; ".get_translation('UPPER_LIMIT_SHORT','borne sup')."</th><th>".get_translation('COUNT_VALUES_SHORT','Nb val')." &lt ".get_translation('LOWER_LIMIT_SHORT','borne inf')."</th><th>% &lt ".get_translation('LOWER_LIMIT_SHORT','borne inf')."</th><th>".get_translation('TREND','Trend')."</th></tr>";
	print "</tfoot></table>";
}

















           
 function visualiser_tableau_all_exam ($tmpresult_num) {
	global $dbh,$thesaurus_code_labo;
	print "<table class=\"tablefin\" id=\"id_tableau_visualiser_tableau_all_exam\">
	<thead><tr>
		<th>".get_translation('EXAMS','Examens')."</th>
		<th>".get_translation('COUNT_PATIENT_NUMBER_SHORT','nb patients')."</th>
		<th>".get_translation('COUNT_EXAMS','nb examens')."</th>
		<th>".get_translation('MINIMUM_SHORT','min')."</th>
		<th>".get_translation('MAXIMUM_SHORT','max')."</th>
		<th>".get_translation('MEDIAN','Médiane')."</th>
		<th>".get_translation('MEAN_STATISTICS','Moyenne')."</th>
		<th>".get_translation('STANDARD_DEVIATION','Ecart Type')."</th>
		<th>".get_translation('COUNT_VALUES_SHORT','Nb val')." &gt; ".get_translation('UPPER_LIMIT_SHORT','borne sup')."</th>
		<th>% &gt; ".get_translation('UPPER_LIMIT_SHORT','borne sup')."</th>
		<th>".get_translation('COUNT_VALUES_SHORT','Nb val')." &lt ".get_translation('LOWER_LIMIT_SHORT','borne inf')."</th>
		<th>% &lt ".get_translation('LOWER_LIMIT_SHORT','borne inf')."</th>
		<th>".get_translation('TREND','Trend')."</th>
	</tr></thead>
	<tbody>";
	
	
	 
  
	$sel=oci_parse($dbh,"select thesaurus_data_num,count(distinct patient_num),count(*) nb_supborne_sup from dwh_data where   patient_num in (select patient_num from dwh_tmp_result where tmpresult_num=$tmpresult_num)
	and thesaurus_code='$thesaurus_code_labo'  and val_numeric>upper_bound having count(distinct patient_num) >2  group by thesaurus_data_num");
	oci_execute($sel);
	while ($r=oci_fetch_array($sel)) {
		$thesaurus_data_num=$r['THESAURUS_DATA_NUM'];
		$nb_supborne_sup=$r['NB_SUPBORNE_SUP'];
		$tableau_final_sup[$thesaurus_data_num]=$nb_supborne_sup;
	}
	 
  
	$sel=oci_parse($dbh,"select thesaurus_data_num,count(distinct patient_num),count(*) nb_infborne_inf from dwh_data where   patient_num in (select patient_num from dwh_tmp_result where tmpresult_num=$tmpresult_num)
	and thesaurus_code='$thesaurus_code_labo'  and val_numeric<lower_bound having count(distinct patient_num) >2  group by thesaurus_data_num");
	oci_execute($sel);
	while ($r=oci_fetch_array($sel)) {
		$thesaurus_data_num=$r['THESAURUS_DATA_NUM'];
		$nb_infborne_inf=$r['NB_INFBORNE_INF'];
		$tableau_final_inf[$thesaurus_data_num]=$nb_infborne_inf;
	}
  
  
	$sel_patient_num=oci_parse($dbh,"select concept_str,measuring_unit,info_complement,dwh_data.thesaurus_data_num,count(distinct patient_num) as nb_patient,count(*) as nb_examen,median(val_numeric) as median,round(stddev(val_numeric),1) as stddev,round(avg(val_numeric),1) as avg,min(val_numeric) as min,max(val_numeric) as max,round(avg(lower_bound),1),round(avg(upper_bound),1) 
	from dwh_data, dwh_thesaurus_data where dwh_data.thesaurus_data_num= dwh_thesaurus_data.thesaurus_data_num and  patient_num in (select patient_num from dwh_tmp_result where tmpresult_num=$tmpresult_num)
  and dwh_data.thesaurus_code='$thesaurus_code_labo' having count(distinct patient_num) >2  group by concept_str,measuring_unit,info_complement,dwh_data.thesaurus_data_num order by count(distinct patient_num)  desc");
	oci_execute($sel_patient_num);
	while ($r_patient_num=oci_fetch_array($sel_patient_num)) {
		$concept_str=$r_patient_num['CONCEPT_STR'];
		$measuring_unit=$r_patient_num['MEASURING_UNIT'];
		$info_complement=$r_patient_num['INFO_COMPLEMENT'];
		$thesaurus_data_num=$r_patient_num['THESAURUS_DATA_NUM'];
		$nb_patient=$r_patient_num['NB_PATIENT'];
		$nb_examen=$r_patient_num['NB_EXAMEN'];
		$avg=$r_patient_num['AVG'];
		$min=$r_patient_num['MIN'];
		$max=$r_patient_num['MAX'];
		$median=$r_patient_num['MEDIAN'];
		$stddev=$r_patient_num['STDDEV'];
		if ($concept_str!='') {
			$nb_supborne_sup=$tableau_final_sup[$thesaurus_data_num];
			$nb_infborne_inf=$tableau_final_inf[$thesaurus_data_num];
			$pourcent_sup='';
			if ($nb_examen!=0 && $nb_supborne_sup!='') {
				$sel=oci_parse($dbh,"select round(100*$nb_supborne_sup/$nb_examen) as POURCENT_SUP from dual  ");
				oci_execute($sel);
				$r=oci_fetch_array($sel);
				$pourcent_sup=$r['POURCENT_SUP'];
			}
			$pourcent_inf='';
			if ($nb_examen!=0 && $nb_infborne_inf!='') {
				$sel=oci_parse($dbh,"select round(100*$nb_infborne_inf/$nb_examen) as pourcent_inf from dual  ");
				oci_execute($sel);
				$r=oci_fetch_array($sel);
				$pourcent_inf=$r['POURCENT_INF'];
			}
			
			$max=str_replace(",",".",$max);
			$min=str_replace(",",".",$min);
			$avg=str_replace(",",".",$avg);
			$median=str_replace(",",".",$median);
			$stddev=str_replace(",",".",$stddev);
			print "<tr><td>$concept_str $measuring_unit $info_complement</td><td>$nb_patient</td><td>$nb_examen</td><td>$min</td><td>$max</td><td>$median</td><td>$avg</td><td>$stddev</td><td>$nb_supborne_sup</td><td>$pourcent_sup</td><td>$nb_infborne_inf</td><td>$pourcent_inf</td>";
			print "<td></td>";
			print "</tr>";
		}
	}
	print "</tbody>";
	
	print "</table>";
}
           
 function visualiser_graph_scatterplot ($thesaurus_data_num,$tmpresult_num,$option_date_alignement,$option_population) {
	global $dbh;
	
	$sel=oci_parse($dbh,"select measuring_unit,concept_str  from dwh_thesaurus_data where thesaurus_data_num=$thesaurus_data_num ");
	oci_execute($sel);
	$r=oci_fetch_array($sel);
	$measuring_unit=$r['MEASURING_UNIT'];
	$concept_str=$r['CONCEPT_STR'];
	
	
	if ($option_date_alignement=='age_naissance') {
		$i=0;
		$sel_patient_num=oci_parse($dbh,"select distinct patient_num from dwh_tmp_result where tmpresult_num=$tmpresult_num");
		oci_execute($sel_patient_num);
		while ($r_patient_num=oci_fetch_array($sel_patient_num)) {
			$patient_num=$r_patient_num['PATIENT_NUM'];
			$i++;
			if ($patient_num!='' && $i<30) {
				if ($option_population=='sex') {
					$sel=oci_parse($dbh,"select sex  from dwh_patient where patient_num=$patient_num ");
					oci_execute($sel);
					$r=oci_fetch_array($sel);
					$sex=$r['SEX'];
					
					$sel=oci_parse($dbh,"select val_numeric, age_patient  from dwh_data where thesaurus_data_num=$thesaurus_data_num and patient_num=$patient_num order by age_patient");
					oci_execute($sel);
					while ($r=oci_fetch_array($sel)) {
						$age_patient=$r['AGE_PATIENT'];
						$val_numeric=$r['VAL_NUMERIC'];
						$val_numeric=str_replace(",",".",$val_numeric);
						$age_patient=str_replace(",",".",$age_patient);
						if ($sex=='M') {
							$data_seriem.="[$age_patient, $val_numeric],";
						}
						if ($sex=='F') {
							$data_serief.="[$age_patient, $val_numeric],";
						}
					}
				}
				if ($option_population=='patient') {
					$serie_finale.="{
		           			 name: '$patient_num',  data: [";
	           			 $serie='';
					$sel=oci_parse($dbh,"select val_numeric, age_patient  from dwh_data where thesaurus_data_num=$thesaurus_data_num and patient_num=$patient_num order by age_patient");
					oci_execute($sel);
					while ($r=oci_fetch_array($sel)) {
						$age_patient=$r['AGE_PATIENT'];
						$val_numeric=$r['VAL_NUMERIC'];
						$val_numeric=str_replace(",",".",$val_numeric);
						$age_patient=str_replace(",",".",$age_patient);
						$serie.="[$age_patient, $val_numeric],";
					}
					$serie=substr($serie,0,-1);
					$serie_finale.="$serie]},";
				}
			}
		}
	}
	if ($option_population=='sex') {
		$data_serief=substr($data_serief,0,-1);
		$data_seriem=substr($data_seriem,0,-1);
		$serie_finale="{
		            name: 'data F',
		            data: [$data_serief]
		
		        },
		        {
		            name: 'data M',
		            data: [$data_seriem]
		
		        }";
	}
	if ($option_population=='patient') {
		$serie_finale=substr($serie_finale,0,-1);
	}
	
	print "
	$('#id_visualiser_graph_scatterplot').highcharts({
        chart: {
            type: 'scatter'
        },
        legend: {
            enabled: false
        },
        title: {
            text: '$concept_str par age du patient '
        },
        subtitle: {
            text: ''
        },
        xAxis: {
            title: {
                enabled: true,
                text: 'Age (ans)'
            },
            startOnTick: true,
            endOnTick: true,
            showLastLabel: true
        },
        yAxis: {
            title: {
                text: '$measuring_unit'
            }
        },
        plotOptions: {
            scatter: {
                marker: {
                    radius: 2,
                    states: {
                 enabled: false,
                        hover: {
                            enabled: false,
                            lineColor: 'rgb(100,100,100)'
                        }
                    }
                },
                states: {
                    hover: {
                        marker: {
                            enabled: false
                        }
                    }
                }
            }
        },
        tooltip: {
            enabled: false
        },

        series: [$serie_finale]
    });
	
	";
}

$RootDirectory="stat/";
function calcul_tendance_lineare_patient ($patient_num,$thesaurus_data_num) {
	global $dbh,$RootDirectory;
  	require_once( $RootDirectory . 'PolynomialRegression/PolynomialRegression.php' ); 
	$i=0;
	$sel=oci_parse($dbh,"select val_numeric, age_patient  from dwh_data where thesaurus_data_num=$thesaurus_data_num and patient_num=$patient_num order by age_patient");
	oci_execute($sel);
	while ($r=oci_fetch_array($sel)) {
		$age_patient=$r['AGE_PATIENT'];
		$val_numeric=$r['VAL_NUMERIC'];
		$val_numeric=str_replace(",",".",$val_numeric);
		$age_patient=str_replace(",",".",$age_patient);
		$data[$i]=array($age_patient,$val_numeric);
		$i++;
	}
	
	  // Precision digits in BC math.
	  bcscale( 10 );
	
	  // Start a regression class of order 2--linear regression.
	  $PolynomialRegression = new PolynomialRegression( 2 );
	
	  // Add all the data to the regression analysis.
	  foreach ( $data as $dataPoint )
	    $PolynomialRegression->addData( $dataPoint[ 0 ], $dataPoint[ 1 ] );
	
	  // Get coefficients for the polynomial.
	  $coefficients = $PolynomialRegression->getCoefficients();
	
	  // Print slope and intercept of linear regression.
	  return "Slope : " . round( $coefficients[ 1 ], 2 ) . "<br /> Y-intercept')  : " . round( $coefficients[ 0 ], 2 ) ;
}
?>