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

function pyramide_age ($tmpresult_num,$tableau_interval,$liste_interval_libelle,$id_pyramide_age,$option_age,$title) {
	global $dbh,$user_num_session;
	foreach ($tableau_interval as $interval) {
		$tableau_age[$interval]=0;
	}
	$unk=0;
	$age_max=0;
	$tableau_age_annee=array();
	$tableau_sex=array();
	$tableau_age_sex=array();
	if ($option_age=='document') {
		$req="select trunc((document_date-birth_date)/365) as age,sex,count(*) as nb from dwh_patient, (select patient_num,min(document_date) as document_date from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num  group by patient_num) resultat where  resultat.patient_num=dwh_patient.patient_num group by trunc((document_date-BIRTH_DATE)/365),sex";
	} else {	
		$req="select decode(death_date,null,trunc((sysdate-BIRTH_DATE)/365) , trunc((death_date-BIRTH_DATE)/365) ) as age,sex,count(*) as nb from dwh_patient where 
		(death_date is null and (death_code is null or death_code='0') or death_date is not null)
		and  exists (select * from dwh_tmp_result_$user_num_session where TMPRESULT_NUM=$tmpresult_num and patient_num=dwh_patient.patient_num) g
		roup by decode(death_date,null,trunc((sysdate-BIRTH_DATE)/365) , trunc((death_date-BIRTH_DATE)/365) ) ,sex";
	}
	$selage=oci_parse($dbh,$req);
	oci_execute($selage) ;
	while ($resselage=oci_fetch_array($selage,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$age=$resselage['AGE'];
		$nb=$resselage['NB'];
		$sex=$resselage['SEX'];
		$tableau_sex[$sex]=$sex;
		if ($age==''){
			$unk+=$nb;
		} else {
			foreach ($tableau_interval as $interval) {
				$tableau_tranche=preg_split("/-/",$interval);
				$deb=$tableau_tranche[0];
				$fin=$tableau_tranche[1];
				if ($fin!='') {
					if ($age>=$deb && $age<$fin) {
						$tableau_age[$interval]+=$nb;
						$tableau_age_sex[$sex][$interval]+=$nb;
					}
				} else {
					if ($age>=$deb) {
						$tableau_age[$interval]+=$nb;
						$tableau_age_sex[$sex][$interval]+=$nb;
					}
				}
			}
		}
		$tableau_age_annee[$age]+=$nb;
		if ($age_max<$age) {
			$age_max=$age;
		}
	}
	$nb_max=0;
	$list_values="{
                name: '".get_translation('JS_MALE','M')."',
                data: [";
	foreach ($tableau_interval as $interval) {
		$nb=$tableau_age_sex['M'][$interval];
		if ($nb=='') {
			$nb=0;
	        } 
	        $list_values.='-'.$nb.',';
		if ($nb_max<$nb) {
			$nb_max=$nb;
		}
	}
	$list_values=substr($list_values,0,-1);
	$list_values.="]},";
	$list_values.="{
                name: '".get_translation('JS_FEMALE','F')."',
                data: [";
	foreach ($tableau_interval as $interval) {
		$nb=$tableau_age_sex['F'][$interval];
		if ($nb=='') {
			$nb=0;
	        } 
	        $list_values.=$nb.',';
		if ($nb_max<$nb) {
			$nb_max=$nb;
		}
	}
	$list_values=substr($list_values,0,-1);
	$list_values.="]}";
	
	
	
	
	print "
	
		var categories = [$liste_interval_libelle];
		$(document).ready(function () {
	        $('#$id_pyramide_age').highcharts({
	            chart: {
	                type: 'bar'
	            },
		credits: {
			enabled: false
		},
	            title: {
	                text: \"$title\"
	            },
	            xAxis: [{
	                categories: categories,
	                reversed: false,
	                labels: {
	                    step: 1
	                }
	            }, { // mirror axis on right side
	                opposite: true,
	                reversed: false,
	                categories: categories,
	                linkedTo: 0,
	                labels: {
	                    step: 1
	                }
	            }],
	            yAxis: {
	                title: {
	                    text: null
	                },
	                labels: {
	                    formatter: function () {
	                        return (Math.abs(this.value));
	                    }
	                },
	                min: -$nb_max,
	                max: $nb_max,
			allowDecimals:false
	            },
	            plotOptions: {
	                series: {
	                    stacking: 'normal'
	                }
	            },
	
	            tooltip: {
	                formatter: function () {
	                    return '<b>' + this.series.name + ', ".get_translation('JS_AGE','age')." ' + this.point.category + '</b><br/>' +
	                        '".get_translation('JS_POPULATION','Population').": ' + Highcharts.numberFormat(Math.abs(this.point.y), 0);
	                }
	            },
	            series: [$list_values]
	        });
	    });
	
	";
}



function pyramide_age_vivant_dcd ($tmpresult_num,$tableau_interval,$liste_interval_libelle,$id_pyramide_age,$title) {
	global $dbh,$user_num_session;
	foreach ($tableau_interval as $interval) {
		$tableau_age[$interval]=0;
	}
	$unk=0;
	$age_max=0;
	$tableau_age_annee=array();
	$tableau_sex=array();
	$tableau_age_sex=array();
	$tableau_age_sex_statut=array();
	// il peut y avoir un concept_code deces à 'd' avec une death_date null ...	
	$req="select 
	decode(death_date,null,trunc((sysdate-BIRTH_DATE)/365) , trunc((death_date-BIRTH_DATE)/365) ) as age,
	sex,
	decode(death_date,null,'vivant','dcd') status,
	count(*) as nb from dwh_patient 
	where
	(death_date is null and (death_code is null or death_code='0') or death_date is not null)
	and 
	 exists (select * from dwh_tmp_result_$user_num_session where TMPRESULT_NUM=$tmpresult_num and patient_num=dwh_patient.patient_num) group by decode(death_date,null,trunc((sysdate-BIRTH_DATE)/365) , trunc((death_date-BIRTH_DATE)/365) ),sex,decode(death_date,null,'vivant','dcd')";
	$selage=oci_parse($dbh,$req);
	oci_execute($selage) ;
	while ($resselage=oci_fetch_array($selage,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$age=$resselage['AGE'];
		$nb=$resselage['NB'];
		$sex=$resselage['SEX'];
		$status=$resselage['STATUS'];
		$tableau_sex[$sex]=$sex;
		if ($age==''){
			$unk+=$nb;
		} else {
			foreach ($tableau_interval as $interval) {
				$tableau_tranche=preg_split("/-/",$interval);
				$deb=$tableau_tranche[0];
				$fin=$tableau_tranche[1];
				if ($fin!='') {
					if ($age>=$deb && $age<$fin) {
						$tableau_age[$interval]+=$nb;
						$tableau_age_sex[$sex][$interval]+=$nb;
						$tableau_age_sex_statut[$sex][$status][$interval]+=$nb;
					}
				} else {
					if ($age>=$deb) {
						$tableau_age[$interval]+=$nb;
						$tableau_age_sex[$sex][$interval]+=$nb;
						$tableau_age_sex_statut[$sex][$status][$interval]+=$nb;
					}
				}
			}
		}
		$tableau_age_annee[$age]+=$nb;
		if ($age_max<$age) {
			$age_max=$age;
		}
	}
	$nb_max=0;
	$liste_valeur_vivant="{
                name: '".get_translation('JS_MALE','M').get_translation('JS_ALIVE','vivant')."',
                color:'#7cb5ec',
                data: [";
	$liste_valeur_dcd="{
                name: '".get_translation('JS_MALE','M')." ".get_translation('JS_DECEASED','dcd')."',
                color:'#F39EA0',
                data: [";
	foreach ($tableau_interval as $interval) {
		$nb_vivant=$tableau_age_sex_statut['M']['vivant'][$interval];
		if ($nb_vivant=='') {
			$nb_vivant=0;
	        } 
	        $liste_valeur_vivant.='-'.$nb_vivant.',';
	        
		$nb_dcd=$tableau_age_sex_statut['M']['dcd'][$interval];
		if ($nb_dcd=='') {
			$nb_dcd=0;
	        } 
	        $liste_valeur_dcd.='-'.$nb_dcd.',';
	        
		$nb=$tableau_age_sex['M'][$interval];
		if ($nb_max<$nb) {
			$nb_max=$nb;
		}
	}
	$liste_valeur_vivant=substr($liste_valeur_vivant,0,-1);
	$liste_valeur_vivant.="]},";
	
	$liste_valeur_dcd=substr($liste_valeur_dcd,0,-1);
	$liste_valeur_dcd.="]},";
	
	$liste_valeur_vivant.="{
                name: '".get_translation('JS_FEMALE','F').get_translation('JS_ALIVE','vivant')."',
                color:'#434348',
                data: [";
	$liste_valeur_dcd.="{
                name: '".get_translation('JS_FEMALE','F')." ".get_translation('JS_DECEASED','dcd')."',
                color:'#E4292E',
                data: [";
	foreach ($tableau_interval as $interval) {
		$nb_vivant=$tableau_age_sex_statut['F']['vivant'][$interval];
		if ($nb_vivant=='') {
			$nb_vivant=0;
	        } 
	        $liste_valeur_vivant.=$nb_vivant.',';
	        
		$nb_dcd=$tableau_age_sex_statut['F']['dcd'][$interval];
		if ($nb_dcd=='') {
			$nb_dcd=0;
	        } 
	        $liste_valeur_dcd.=$nb_dcd.',';
	        
		$nb=$tableau_age_sex['F'][$interval];
		if ($nb_max<$nb) {
			$nb_max=$nb;
		}
	}
	$liste_valeur_vivant=substr($liste_valeur_vivant,0,-1);
	$liste_valeur_vivant.="]}";
	$liste_valeur_dcd=substr($liste_valeur_dcd,0,-1);
	$liste_valeur_dcd.="]}";
	
	
	
	
	print "
		var categories = [$liste_interval_libelle];
		$(document).ready(function () {
	        $('#$id_pyramide_age').highcharts({
	            chart: {
	                type: 'bar'
	            },
		credits: {
			enabled: false
		},
	            title: {
	                text: \"$title\"
	            },
	            xAxis: [{
	                categories: categories,
	                reversed: false,
	                labels: {
	                    step: 1
	                }
	            }, { // mirror axis on right side
	                opposite: true,
	                reversed: false,
	                categories: categories,
	                linkedTo: 0,
	                labels: {
	                    step: 1
	                }
	            }],
	            yAxis: {
	                title: {
	                    text: null
	                },
	                labels: {
	                    formatter: function () {
	                        return (Math.abs(this.value));
	                    }
	                },
	                min: -$nb_max,
	                max: $nb_max,
			allowDecimals:false
	            },
	            plotOptions: {
	                series: {
	                    stacking: 'normal'
	                }
	            },
	
	            tooltip: {
	                formatter: function () {
	                    return '<b>' + this.series.name + ', ".get_translation('JS_AGE','age')." ' + this.point.category + '</b><br/>' +
	                        '".get_translation('JS_POPULATION','Population').": ' + Highcharts.numberFormat(Math.abs(this.point.y), 0);
	                }
	            },
	            series: [$liste_valeur_dcd,$liste_valeur_vivant]
	        });
	    });
	
	";
}

function nb_patients_temps ($tmpresult_num,$id_div,$option_affichage) {
	global $dbh,$user_num_session;
	$tableau_nb_nouveau_patient=array();
	$tableau_nb_dejavu_patient=array();
	$tableau_nb_patient=array();
	
	$an_max=0;
	$an_min=2012;
	
	$req="select distinct to_char(document_date,'YYYY') as an,  patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num and document_date is not null and document_date<=sysdate and to_char(document_date,'YYYY')>1990   order by to_char(document_date,'YYYY')";
	$sel=oci_parse($dbh,$req);
	oci_execute($sel) ;
	while ($res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$an=$res['AN'];
		$patient_num=$res['PATIENT_NUM'];
		if ($tableau_patient_num_deja[$patient_num]=='') {
			$tableau_nb_nouveau_patient[$an]=$tableau_nb_nouveau_patient[$an]+0+1;
		} else {
			$tableau_nb_dejavu_patient[$an]=$tableau_nb_dejavu_patient[$an]+0+1;
		}
		$tableau_nb_patient[$an]++;
		$tableau_patient_num_deja[$patient_num]='ok';
		if ($an_min>$an) {
			$an_min=$an;
		}
		if ($an_max<$an) {
			$an_max=$an;
		}
	}
	$liste_categorie='';
	$liste_nb_nouveaux_patient='';
	for ($an=$an_min;$an<=$an_max;$an++) {
		  $liste_categorie.="'$an',";
		  if ($tableau_nb_nouveau_patient[$an]=='') {
		  	$tableau_nb_nouveau_patient[$an]=0;
		  }
		  if ($tableau_nb_dejavu_patient[$an]=='') {
		  	$tableau_nb_dejavu_patient[$an]=0;
		  }
		  $liste_nb_nouveaux_patient.=$tableau_nb_nouveau_patient[$an].",";
		  $liste_nb_dejavu_patient.=$tableau_nb_dejavu_patient[$an].",";
		  
	}
	$liste_categorie=substr($liste_categorie,0,-1);
	$liste_nb_nouveaux_patient=substr($liste_nb_nouveaux_patient,0,-1);
	$liste_nb_dejavu_patient=substr($liste_nb_dejavu_patient,0,-1);
	
	if ($option_affichage=='graph') {
		print "
		    $('#$id_div').highcharts({
		        chart: {
		            type: 'column'
		        },
			credits: {
				enabled: false
			},
		        title: {
		            text: '".get_translation('JS_COUNT_PATIENTS_PER_YEAR','Nombre de patients par an')."'
		        },
		          legend: {
		            enabled: true
		        },
		        xAxis: {
		            categories: [
		                $liste_categorie
		            ]
		        },
		        yAxis: {
		            min: 0,
		            title: {
		                text: '".get_translation('JS_COUNT_PATIENTS','Nb patients')."'
		            },
				allowDecimals:false
		        },
		        plotOptions: {
		            column: {
	               		 stacking: 'normal',
		                pointPadding: 0.2,
		                borderWidth: 0
		            }
		        },
		
	            tooltip: {
	                formatter: function () {
	                    return '<b>' + this.series.name + ' : </b>' + Highcharts.numberFormat(Math.abs(this.point.y), 0);
	                }
	            },
		        series: [{
	          	  name: '".get_translation('JS_NEW_PATIENTS','Nouveaux patients')."',
		            data: [$liste_nb_nouveaux_patient]
		
		        },
		        {
	            		name: '".get_translation('JS_PATIENTS_ALREADY_SEEN','Patients déjà vus')."',
		            data: [$liste_nb_dejavu_patient]
		
		        }]
		    });
		";
	}
	if ($option_affichage=='tableau') {
		print "<table border=\"1\" class=\"tablefin\">
		<thead><tr><th>".get_translation('YEAR','An')."</th><th>".get_translation('PATIENT_ALREADY_SEEN','Patient déjà vu')."</th><th>".get_translation('NEW_PATIENTS','Nouveaux patients')."</th><th>".get_translation('TOTAL','Total')."</th></tr></thead>
		<tbody>";
		for ($an=$an_min;$an<=$an_max;$an++) {
			print "<tr><td>$an</td>";
			print "<td>$tableau_nb_dejavu_patient[$an]</td>";
			print "<td>$tableau_nb_nouveau_patient[$an]</td>";
			$total=$tableau_nb_nouveau_patient[$an]+$tableau_nb_dejavu_patient[$an];
			print "<td>$total</td></tr>";
		}
		print "</tbody></table>";
		
	}
}

function nb_patients_service ($tmpresult_num,$id_div) {
	global $dbh,$user_num_session;
	
	
	$liste_libelle_service='';
	$liste_nb_patient='';
	$req="select department_str,count(distinct patient_num) nb_patient from dwh_tmp_result_$user_num_session, dwh_thesaurus_department
		where tmpresult_num=$tmpresult_num
		and dwh_tmp_result_$user_num_session.department_num=dwh_thesaurus_department.department_num
		group by department_str order by department_str";
	$sel=oci_parse($dbh,$req);
	oci_execute($sel) ;
	while ($res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$department_str=$res['DEPARTMENT_STR'];
		$nb_patient=$res['NB_PATIENT'];
		$liste_libelle_service.="'$department_str',";
		$liste_nb_patient.="$nb_patient,";
		
	}
	
	$liste_libelle_service=substr($liste_libelle_service,0,-1);
	$liste_nb_patient=substr($liste_nb_patient,0,-1);
	
	print "<script language=\"javascript\">
	$(function () {
	    $('#$id_div').highcharts({
	        chart: {
	            type: 'bar'
	        },
		credits: {
			enabled: false
		},
	        title: {
	            text: '".get_translation('JS_COUNT_PATIENTS_PER_HOSPITAL_DEPARTMENT','Nombre de patients par service')."'
	        },
	          legend: {
	            enabled: false
	        },
	        xAxis: {
	            categories: [
	                $liste_libelle_service
	            ]
	        },
	        yAxis: {
	            min: 0,
	            title: {
	                text: '".get_translation('JS_COUNT_PATIENTS','Nb patients')."'
	            },
			allowDecimals:false
	        },
	        plotOptions: {
	            column: {
               		 stacking: 'normal',
	                pointPadding: 0.2,
	                borderWidth: 0
	            }
	        },
	        series: [{
	            data: [$liste_nb_patient]
	
	        }]
	    });
	});
	</script>";
}

function nb_document_document_origin_code ($tmpresult_num,$id_div) {
	global $dbh,$user_num_session;
	
	
	$liste_libelle_service='';
	$liste_nb_patient='';
	$req="select document_origin_code,count(*) nb_document from dwh_tmp_result_$user_num_session
		where tmpresult_num=$tmpresult_num
		group by document_origin_code order by document_origin_code";
	$sel=oci_parse($dbh,$req);
	oci_execute($sel) ;
	while ($res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$document_origin_code=$res['DOCUMENT_ORIGIN_CODE'];
		$nb_document=$res['NB_DOCUMENT'];
		$liste_document_origin_code.="'$document_origin_code',";
		$liste_nb_document.="$nb_document,";
		
	}
	
	$liste_document_origin_code=substr($liste_document_origin_code,0,-1);
	$liste_nb_document=substr($liste_nb_document,0,-1);
	
	print "
	    $('#$id_div').highcharts({
	        chart: {
	            type: 'bar'
	        },
		credits: {
			enabled: false
		},
	        title: {
	            text: '".get_translation('JS_COUNT_DOCUMENTS_PER_ORIGIN','Nombre de documents par origine du document')."'
	        },
	          legend: {
	            enabled: false
	        },
	        xAxis: {
	            categories: [
	                $liste_document_origin_code
	            ]
	        },
	        yAxis: {
	            min: 0,
	            title: {
	                text: '".get_translation('JS_COUNT_PATIENTS','Nb patients')."'
	            },
			allowDecimals:false
	        },
	        plotOptions: {
	            column: {
               		 stacking: 'normal',
	                pointPadding: 0.2,
	                borderWidth: 0
	            }
	        },
	        series: [{
	            data: [$liste_nb_document]
	
	        }]
	    });
	";
}






function nb_nouveau_patients_service ($tmpresult_num,$id_div) {
	global $dbh,$user_num_session;
	$tableau_nb_nouveau_patient=array();
	$tableau_nb_dejavu_patient=array();
	$tableau_nb_patient=array();
	$tableau_service_patient=array();
	
	$an_max=0;
	$an_min=2012;
	
	$req="select department_str,  patient_num ,document_date from dwh_tmp_result_$user_num_session, dwh_thesaurus_department where tmpresult_num=$tmpresult_num 
		and dwh_tmp_result_$user_num_session.department_num=dwh_thesaurus_department.department_num  order by document_date";
	$sel=oci_parse($dbh,$req);
	oci_execute($sel) ;
	while ($res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$department_str=$res['DEPARTMENT_STR'];
		$patient_num=$res['PATIENT_NUM'];
		if (!is_array($tableau_service_patient[$department_str])) {
			$tableau_service_patient[$department_str]=array();
		}
		
		if ($tableau_patient_num_deja[$patient_num]=='') {
			if ($tableau_service_patient[$department_str][$patient_num]=='') {
				$tableau_nb_nouveau_patient[$department_str]=$tableau_nb_nouveau_patient[$department_str]+0+1;
			}
		} else {
			if ($tableau_service_patient[$department_str][$patient_num]=='') {
				$tableau_nb_dejavu_patient[$department_str]=$tableau_nb_dejavu_patient[$department_str]+0+1;
			}
		}
		$tableau_nb_patient[$department_str]++;
		$tableau_patient_num_deja[$patient_num]='ok';
		$tableau_service_patient[$department_str][$patient_num]='ok';
	}
	$liste_categorie='';
	$liste_nb_nouveaux_patient='';
	ksort($tableau_nb_patient);
	$nb_c=0;
	foreach ( $tableau_nb_patient as $department_str => $n) {
		$nb_c++;
		  $liste_categorie.="'$department_str',";
		  if ($tableau_nb_nouveau_patient[$department_str]=='') {
		  	$tableau_nb_nouveau_patient[$department_str]=0;
		  }
		  if ($tableau_nb_dejavu_patient[$department_str]=='') {
		  	$tableau_nb_dejavu_patient[$department_str]=0;
		  }
		  $liste_nb_nouveaux_patient.=$tableau_nb_nouveau_patient[$department_str].",";
		  $liste_nb_dejavu_patient.=$tableau_nb_dejavu_patient[$department_str].",";
		  
	}
	$liste_categorie=substr($liste_categorie,0,-1);
	$liste_nb_nouveaux_patient=substr($liste_nb_nouveaux_patient,0,-1);
	$liste_nb_dejavu_patient=substr($liste_nb_dejavu_patient,0,-1);
	
	$height=150+$nb_c*27;
	print "$height;separateur;";
	
	print "$('#$id_div').highcharts({
	        chart: {
	            type: 'bar'
	        },
		credits: {
			enabled: false
		},
	        title: {
	            text: '".get_translation('JS_PATIENTS_PER_HOSPITAL_DEPARTMENT','Patients par service')."'
	        },
		legend: {
	            enabled: true
	        },
	        xAxis: {
	            categories: [
	                $liste_categorie
	            ]
	        },
	        yAxis: {
	            min: 0,
	            title: {
	                text: '".get_translation('JS_COUNT_PATIENTS','Nb patients')."'
	            },
			allowDecimals:false
	        },
	        plotOptions: {
	            series: {
	                stacking: 'normal'
	            }
	        },
	
	            tooltip: {
	                formatter: function () {
	                    return '<b>' + this.series.name + ' : </b>' + Highcharts.numberFormat(Math.abs(this.point.y), 0);
	                }
	            },
	        series: [{
				name: '".get_translation('JS_NEW_PATIENTS','Nouveaux patients')."',
				data: [$liste_nb_nouveaux_patient]
	
		        },
		        {
				name: '".get_translation('JS_PATIENTS_ALREADY_SEEN','Patients déjà vus')."',
				data: [$liste_nb_dejavu_patient]
	
	        	}]
	    });";
}





function nb_nouveau_patients_service_hors_mespatients ($tmpresult_num,$id_div) {
	global $dbh,$liste_uf_session,$datamart_num,$liste_service_session,$user_num_session;
	$tableau_nb_nouveau_patient=array();
	$tableau_nb_dejavu_patient=array();
	$tableau_nb_patient=array();
	$tableau_service_patient=array();
	$filtre_sql_service="";
        if ($_SESSION['dwh_droit_all_departments'.$datamart_num]=='') {
                if ($liste_service_session!='') {
                        $filtre_sql_service=" and patient_num not in (select patient_num from dwh_document where department_num in ($liste_service_session) ) ";
                }
	}

	if ($filtre_sql_service!='') {
		$req="select department_str,  patient_num ,document_date from dwh_tmp_result_$user_num_session, dwh_thesaurus_department where tmpresult_num=$tmpresult_num and dwh_thesaurus_department.department_num=dwh_tmp_result_$user_num_session.department_num
			  $filtre_sql_service order by document_date";
		$sel=oci_parse($dbh,$req);
		oci_execute($sel) ;
		while ($res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$department_str=$res['DEPARTMENT_STR'];
			$patient_num=$res['PATIENT_NUM'];
			if (!is_array($tableau_service_patient[$department_str])) {
				$tableau_service_patient[$department_str]=array();
			}
			
			if ($tableau_patient_num_deja[$patient_num]=='') {
				if ($tableau_service_patient[$department_str][$patient_num]=='') {
					$tableau_nb_nouveau_patient[$department_str]=$tableau_nb_nouveau_patient[$department_str]+0+1;
				}
			} else {
				if ($tableau_service_patient[$department_str][$patient_num]=='') {
					$tableau_nb_dejavu_patient[$department_str]=$tableau_nb_dejavu_patient[$department_str]+0+1;
				}
			}
			$tableau_nb_patient[$department_str]++;
			$tableau_patient_num_deja[$patient_num]='ok';
			$tableau_service_patient[$department_str][$patient_num]='ok';
		}
		$liste_categorie='';
		$liste_nb_nouveaux_patient='';
		ksort($tableau_nb_patient);
		$nb_c=0;
		foreach ( $tableau_nb_patient as $department_str => $n) {
			$nb_c++;
			  $liste_categorie.="'$department_str',";
			  if ($tableau_nb_nouveau_patient[$department_str]=='') {
			  	$tableau_nb_nouveau_patient[$department_str]=0;
			  }
			  if ($tableau_nb_dejavu_patient[$department_str]=='') {
			  	$tableau_nb_dejavu_patient[$department_str]=0;
			  }
			  $liste_nb_nouveaux_patient.=$tableau_nb_nouveau_patient[$department_str].",";
			  $liste_nb_dejavu_patient.=$tableau_nb_dejavu_patient[$department_str].",";
			  
		}
		$liste_categorie=substr($liste_categorie,0,-1);
		$liste_nb_nouveaux_patient=substr($liste_nb_nouveaux_patient,0,-1);
		$liste_nb_dejavu_patient=substr($liste_nb_dejavu_patient,0,-1);
		
		$height=150+$nb_c*27;
		print "$height;separateur;";
		print "$('#$id_div').highcharts({
		        chart: {
		            type: 'bar'
		        },
			credits: {
				enabled: false
			},
		        title: {
		            text: 'Patients hors mon service'
		        },
			legend: {
		            enabled: true
		        },
		        xAxis: {
		            categories: [
		                $liste_categorie
		            ]
		        },
		        yAxis: {
		            min: 0,
		            title: {
		                text: '".get_translation('JS_COUNT_PATIENTS','Nb patients')."'
		            },
				allowDecimals:false
		        },
		        plotOptions: {
		            series: {
		                stacking: 'normal'
		            }
		        },
		
		            tooltip: {
		                formatter: function () {
		                    return '<b>' + this.series.name + ' : </b>' + Highcharts.numberFormat(Math.abs(this.point.y), 0);
		                }
		            },
		        series: [{
					name: '".get_translation('JS_NEW_PATIENTS','Nouveaux patients')."',
					data: [$liste_nb_nouveaux_patient]
		
			        },
			        {
					name: '".get_translation('JS_PATIENTS_ALREADY_SEEN','Patients déjà vus')."',
					data: [$liste_nb_dejavu_patient]
		
		        	}]
		    });";
	}
}

function nb_patient_sex ($tmpresult_num) {
	global $dbh,$user_num_session;
			
	$liste_valeur_m="{
                name: '".get_translation('JS_MALE','M')."',
                data: [";
	$liste_valeur_f="{
                name: '".get_translation('JS_FEMALE','F')."',
                data: [";
                
	
	$req="select sex,round(count(*)*100/$nb_total,0) as NB  from dwh_patient where exists (select * from dwh_tmp_result_$user_num_session where TMPRESULT_NUM=$tmpresult_num and patient_num=dwh_patient.patient_num) group by sex";
	$req="select sex,count(*) NB  from dwh_patient where exists (select * from dwh_tmp_result_$user_num_session where TMPRESULT_NUM=$tmpresult_num and patient_num=dwh_patient.patient_num) group by sex";
	$selage=oci_parse($dbh,$req);
	oci_execute($selage) ;
	while ($resselage=oci_fetch_array($selage,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$nb=$resselage['NB'];
		$sex=$resselage['SEX'];
		if ($sex=='M') {
			$liste_valeur_m.=$nb;
		}
		if ($sex=='F') {
			$liste_valeur_f.=$nb;
		}
	}
	$liste_valeur_m.="]}";
	$liste_valeur_f.="]}";
	
	
	
	
	
	print "
	        $('#id_sex_repartition').highcharts({
	            chart: {
	                type: 'column'
	            },
		credits: {
			enabled: false
		},
	            title: {
	                text: \"".get_translation('JS_SEX_RATIO','Répartition par sex')."\"
	            },
	            yAxis: {
	                title: {
	                    text: null
	                },
	                labels: {
	                    formatter: function () {
	                        return (Math.abs(this.value));
	                    }
	                },
			allowDecimals:false
	            },
	
	            tooltip: {
	                formatter: function () {
	                    return '<b>' + this.series.name + ' :  ' +
	                       Highcharts.numberFormat(Math.abs(this.point.y), 0)+  ' </b>' ;
	                }
	            },
	            series: [$liste_valeur_m,$liste_valeur_f]
	        });
	";
}


function nb_patient_per_unit_per_year_tableau ($tmpresult_num) {
	global $dbh,$user_num_session;
	$tableau_nb_patient_department=array();
	$tableau_nb_nouveau_patient_department=array();
	$tableau_nb_dejavu_patient_department=array();
	$tableau_patient_num_deja_vu_department=array();
	$tableau_patient_num_deja_vu_department_year=array();
	$tableau_nb_patient_unit=array();
	$tableau_nb_nouveau_patient_unit=array();
	$tableau_nb_dejavu_patient_unit=array();
	$tableau_patient_num_deja_vu_unit=array();
	
	$year_max=0;
	$year_min=2012;
	
	$year_max=date("Y");
	
	$req="select distinct unit_num,department_num, to_char(entry_date,'YYYY') as year,  patient_num from 
	dwh_patient_mvt 
	where patient_num in (select patient_num  from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) 
	and entry_date is not null 
	order by to_char(entry_date,'YYYY') ";
	$sel=oci_parse($dbh,$req);
	oci_execute($sel) ;
	while ($res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$unit_num=$res['UNIT_NUM'];
		$department_num=$res['DEPARTMENT_NUM'];
		$year=$res['YEAR'];
		$patient_num=$res['PATIENT_NUM'];
		if (intval($year)<2012) {
			$year="<2012";
		}
		
		if ($tableau_patient_num_deja_vu_department[$department_num][$patient_num]=='') {
			$tableau_nb_nouveau_patient_department[$department_num][$year]=intval($tableau_nb_nouveau_patient_department[$department_num][$year])+1;
			$tableau_nb_patient_department[$department_num]++;
		} else {
			if ($tableau_patient_num_deja_vu_department_year[$department_num][$patient_num][$year]=='') {
				$tableau_nb_dejavu_patient_department[$department_num][$year]=intval($tableau_nb_dejavu_patient_department[$department_num][$year])+1;
			} else {
			}
		}
		$tableau_patient_num_deja_vu_department[$department_num][$patient_num]='ok';
		$tableau_patient_num_deja_vu_department_year[$department_num][$patient_num][$year]='ok';
		
		if ($tableau_patient_num_deja_vu_unit[$unit_num][$patient_num]=='') {
			$tableau_nb_nouveau_patient_unit[$unit_num][$year]=intval($tableau_nb_nouveau_patient_unit[$unit_num][$year])+1;
			$tableau_nb_patient_unit[$unit_num]++;
		} else {
			$tableau_nb_dejavu_patient_unit[$unit_num][$year]=intval($tableau_nb_dejavu_patient_unit[$unit_num][$year])+1;
		}
		$tableau_patient_num_deja_vu_unit[$unit_num][$patient_num]='ok';
		
	}
		

	print "<h2 onclick=\"plier_deplier('div_id_table_nb_patient_per_department_per_year_tableau');\" style=\"cursor:pointer\"><span id=\"plus_div_id_table_nb_patient_per_department_per_year_tableau\">+</span> ".get_translation('TABLE_NB_PATIENTS_PER_DEPARTMENT_AND_YEAR','Nombre de patients par service et par an (hospit, consult, etc.)')."<h2>";	
	print "<div id=\"div_id_table_nb_patient_per_department_per_year_tableau\" style=\"display:none\">";
	print "<table border=\"1\" class=\"tablefin\" id=\"id_table_nb_patient_per_department_per_year_tableau\">
	<thead>
	<tr><th rowspan=\"2\" >".get_translation('SERVICE','Service')."</th>";
	print "<th colspan=\"2\">&lt;$year_min</th>";
	for ($year=$year_min;$year<=$year_max;$year++) {
		print "<th colspan=\"2\">$year</th>";
	}
	print "<th rowspan=\"2\">".get_translation('TOTAL','Total')."</th></tr>
	<tr>";
	print "<th>Seen</th><th>New</th>";
	for ($year=$year_min;$year<=$year_max;$year++) {
		print "<th>Seen</th><th>New</th>";
	}
	print "</tr>
	</thead>
	<tbody>";
	foreach ($tableau_nb_patient_department as $department_num => $nb_patient) {
                $department_str=get_department_str ($department_num);    
		print "<tr class=\"over_color\"><td nowrap>$department_str</td>";
		$year_before="<$year_min";
		if ($tableau_nb_dejavu_patient_department[$department_num][$year_before]=='') {
			print "<td style=\"color:#cccccc\">0</td>";
		} else {
			print "<td>".$tableau_nb_dejavu_patient_department[$department_num][$year_before]."</td>";
		}
		if ($tableau_nb_nouveau_patient_department[$department_num][$year_before]=='') {
			print "<td style=\"color:#cccccc\">0</td>";
		} else {
			print "<td>".$tableau_nb_nouveau_patient_department[$department_num][$year_before]."</td>";
		}
		for ($year=$year_min;$year<=$year_max;$year++) {
			if ($tableau_nb_dejavu_patient_department[$department_num][$year]=='') {
				print "<td style=\"color:#cccccc\">0</td>";
			} else {
				print "<td>".$tableau_nb_dejavu_patient_department[$department_num][$year]."</td>";
			}
			if ($tableau_nb_nouveau_patient_department[$department_num][$year]=='') {
				print "<td style=\"color:#cccccc\">0</td>";
			} else {
				print "<td>".$tableau_nb_nouveau_patient_department[$department_num][$year]."</td>";
			}
		}
		print "<td>$nb_patient</td>";
		print "</tr>";
	}
	print "</tbody></table>";
	print "</div>";
	
	
	
	print "<h2 onclick=\"plier_deplier('div_id_table_nb_patient_per_unit_per_year_tableau');\" style=\"cursor:pointer\"><span id=\"plus_div_id_table_nb_patient_per_unit_per_year_tableau\">+</span> ".get_translation('TABLE_NB_PATIENTS_PER_UNIT_AND_YEAR','Nombre de patients par unité et par an (hospit, consult, etc.)')."<h2>";	
	print "<div id=\"div_id_table_nb_patient_per_unit_per_year_tableau\" style=\"display:none\">";
	print "<table border=\"1\" class=\"tablefin\" id=\"id_table_nb_patient_per_unit_per_year_tableau\">
	<thead>
	<tr><th rowspan=\"2\" >".get_translation('UNIT','Unité')."</th>";
	print "<th colspan=\"2\">&lt;$year_min</th>";
	for ($year=$year_min;$year<=$year_max;$year++) {
		print "<th colspan=\"2\">$year</th>";
	}
	print "<th rowspan=\"2\">".get_translation('TOTAL','Total')."</th></tr>
	<tr>";
	print "<th>Seen</th><th>New</th>";
	for ($year=$year_min;$year<=$year_max;$year++) {
		print "<th>Seen</th><th>New</th>";
	}
	print "</tr>
	</thead>
	<tbody>";
		
	foreach ($tableau_nb_patient_unit as $unit_num => $nb_patient) {
                $unit_str=get_unit_str ($unit_num);    
		print "<tr class=\"over_color\"><td nowrap>$unit_str</td>";
		$year_before="<$year_min";
		if ($tableau_nb_dejavu_patient_unit[$unit_num][$year_before]=='') {
			print "<td style=\"color:#cccccc\">0</td>";
		} else {
			print "<td>".$tableau_nb_dejavu_patient_unit[$unit_num][$year_before]."</td>";
		}
		if ($tableau_nb_nouveau_patient_unit[$unit_num][$year_before]=='') {
			print "<td style=\"color:#cccccc\">0</td>";
		} else {
			print "<td>".$tableau_nb_nouveau_patient_unit[$unit_num][$year_before]."</td>";
		}
		for ($year=$year_min;$year<=$year_max;$year++) {
			if ($tableau_nb_dejavu_patient_unit[$unit_num][$year]=='') {
				print "<td style=\"color:#cccccc\">0</td>";
			} else {
				print "<td>".$tableau_nb_dejavu_patient_unit[$unit_num][$year]."</td>";
			}
			if ($tableau_nb_nouveau_patient_unit[$unit_num][$year]=='') {
				print "<td style=\"color:#cccccc\">0</td>";
			} else {
				print "<td>".$tableau_nb_nouveau_patient_unit[$unit_num][$year]."</td>";
			}
		}
		print "<td>$nb_patient</td>";
		print "</tr>";
	}
	print "</tbody></table>";
	print "</div>";
		
}


function nb_consult_per_unit_per_year_tableau ($tmpresult_num) {
	global $dbh,$user_num_session;
	$tableau_nb_patient=array();
	$tableau_nb_patient_department=array();
	$tableau_nb_patient_unit=array();
	$tableau_nb_nouveau_patient_unit=array();
	
	$year_max=0;
	$year_min=2012;
	$year_max=date("Y");
	
	$req="select  unit_code,unit_num,department_num, to_char(entry_date,'YYYY') as year,  patient_num from 
	dwh_patient_mvt
	where patient_num in (select patient_num  from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num)  and type_mvt='C'  and entry_date is not null ";
	$sel=oci_parse($dbh,$req);
	oci_execute($sel) ;
	while ($res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$unit_code=$res['UNIT_CODE'];
		$unit_num=$res['UNIT_NUM'];
		$department_num=$res['DEPARTMENT_NUM'];
		$year=$res['YEAR'];
		$patient_num=$res['PATIENT_NUM'];

		$tableau_nb_patient[$year]++;
		
		$tableau_nb_patient_department[$department_num]++;
		$tableau_nb_patient_department_year[$department_num][$year]++;
		
		$tableau_nb_patient_unit[$unit_num]++;
		$tableau_nb_patient_unit_year[$unit_num][$year]++;
		
		if ($year_min>$year) {
			$year_min=$year;
		}
	}
	
	print "<h2 onclick=\"plier_deplier('div_id_table_nb_consult_per_department_per_year_tableau');\" style=\"cursor:pointer\"><span id=\"plus_div_id_table_nb_consult_per_department_per_year_tableau\">+</span> ".get_translation('TABLE_NB_CONSULT_PER_DEPARTMENT_AND_YEAR','Nombre de consultations par service et par an')."<h2>";
	print "<div id=\"div_id_table_nb_consult_per_department_per_year_tableau\" style=\"display:none\">";
		print "<table border=\"1\" class=\"tablefin\" id=\"id_table_nb_consult_per_department_per_year_tableau\">
		<thead>
		<tr><th>".get_translation('SERVICE','Service')."</th>";
		for ($year=$year_min;$year<=$year_max;$year++) {
			print "<th class=\"no-sort\">$year</th>";
		}
		print "<th>".get_translation('TOTAL','Total')."</th></tr>
		</thead>
		<tbody>";
			
		foreach ($tableau_nb_patient_department as $department_num => $nb_patient) {
	                $department_str=get_department_str ($department_num);    
			print "<tr class=\"over_color\"><td nowrap>$department_str</td>";
			for ($year=$year_min;$year<=$year_max;$year++) {
				if ($tableau_nb_patient_department_year[$department_num][$year]=='') {
					print "<td style=\"color:#cccccc\">0</td>";
				} else {
					print "<td>".$tableau_nb_patient_department_year[$department_num][$year]."</td>";
				}
			}
			print "<td>$nb_patient</td>";
			print "</tr>";
		}
		print "</tbody></table>";
	print "</div>";
		
		
		
	print "<h2 onclick=\"plier_deplier('div_id_table_nb_consult_per_unit_per_year_tableau');\" style=\"cursor:pointer\"><span id=\"plus_div_id_table_nb_consult_per_unit_per_year_tableau\">+</span> ".get_translation('TABLE_NB_CONSULT_PER_UNIT_AND_YEAR','Nombre de consultations par unité et par an')."<h2>";	
	print "<div id=\"div_id_table_nb_consult_per_unit_per_year_tableau\" style=\"display:none\">";
		print "<table border=\"1\" class=\"tablefin\" id=\"id_table_nb_consult_per_unit_per_year_tableau\">
		<thead>
		<tr><th>".get_translation('UNIT','Unité')."</th>";
		for ($year=$year_min;$year<=$year_max;$year++) {
			print "<th class=\"no-sort\">$year</th>";
		}
		print "<th>".get_translation('TOTAL','Total')."</th></tr>
		</thead>
		<tbody>";
			
		foreach ($tableau_nb_patient_unit as $unit_num => $nb_patient) {
	                $unit_str=get_unit_str ($unit_num);    
			print "<tr class=\"over_color\"><td nowrap>$unit_str</td>";
			for ($year=$year_min;$year<=$year_max;$year++) {
				if ($tableau_nb_patient_unit_year[$unit_num][$year]=='') {
					print "<td style=\"color:#cccccc\">0</td>";
				} else {
					print "<td>".$tableau_nb_patient_unit_year[$unit_num][$year]."</td>";
				}
			}
			print "<td>$nb_patient</td>";
			print "</tr>";
		}
		print "</tbody></table>";
	print "</div>";
}






function nb_hospit_per_unit_per_year_tableau ($tmpresult_num) {
	global $dbh,$user_num_session;
	$tableau_nb_patient=array();
	$tableau_nb_patient_department=array();
	$tableau_nb_patient_unit=array();
	$tableau_nb_nouveau_patient_unit=array();
	
	$year_max=0;
	$year_min=2012;
	$year_max=date("Y");
	
	$req="select  unit_code,unit_num,department_num, to_char(out_date,'YYYY') as year,  patient_num from 
	dwh_patient_mvt
	where patient_num in (select patient_num  from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) 
	and type_mvt='H'  and out_date is not null
	";
	$sel=oci_parse($dbh,$req);
	oci_execute($sel) ;
	while ($res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$unit_code=$res['UNIT_CODE'];
		$unit_num=$res['UNIT_NUM'];
		$department_num=$res['DEPARTMENT_NUM'];
		$year=$res['YEAR'];
		$patient_num=$res['PATIENT_NUM'];

		$tableau_nb_patient[$year]++;
		
		$tableau_nb_patient_department[$department_num]++;
		$tableau_nb_patient_department_year[$department_num][$year]++;
		
		$tableau_nb_patient_unit[$unit_num]++;
		$tableau_nb_patient_unit_year[$unit_num][$year]++;
		
		if ($year_min>$year) {
			$year_min=$year;
		}
		//if ($year_max<$year) {
		//	$year_max=$year;
		//}
	}
	
	print "<h2 onclick=\"plier_deplier('div_id_table_nb_hospit_per_department_per_year_tableau');\" style=\"cursor:pointer\"><span id=\"plus_div_id_table_nb_hospit_per_department_per_year_tableau\">+</span> ".get_translation('TABLE_NB_HOSPIT_PER_DEPARTMENT_AND_YEAR','Nombre d hospitalisations par service et par an')."<h2>";	
	print "<div id=\"div_id_table_nb_hospit_per_department_per_year_tableau\" style=\"display:none\">";
	print "<table border=\"1\" class=\"tablefin\" id=\"id_table_nb_hospit_per_department_per_year_tableau\">
	<thead>
	<tr><th>".get_translation('SERVICE','Service')."</th>";
	for ($year=$year_min;$year<=$year_max;$year++) {
		print "<th class=\"no-sort\">$year</th>";
	}
	print "<th>".get_translation('TOTAL','Total')."</th></tr>
	</thead>
	<tbody>";
		
	foreach ($tableau_nb_patient_department as $department_num => $nb_patient) {
                $department_str=get_department_str ($department_num);    
		print "<tr class=\"over_color\"><td nowrap>$department_str</td>";
		for ($year=$year_min;$year<=$year_max;$year++) {
			if ($tableau_nb_patient_department_year[$department_num][$year]=='') {
				print "<td style=\"color:#cccccc\">0</td>";
			} else {
				print "<td>".$tableau_nb_patient_department_year[$department_num][$year]."</td>";
			}
		}
		print "<td>$nb_patient</td>";
		print "</tr>";
	}
	print "</tbody></table>";
	print "</div>";
	
	
	
	print "<h2 onclick=\"plier_deplier('div_id_table_nb_hospit_per_unit_per_year_tableau');\" style=\"cursor:pointer\"><span id=\"plus_div_id_table_nb_hospit_per_unit_per_year_tableau\">+</span> ".get_translation('TABLE_NB_HOSPIT_PER_UNIT_AND_YEAR','Nombre d hospitalisations par unité et par an')."<h2>";	
	print "<div id=\"div_id_table_nb_hospit_per_unit_per_year_tableau\" style=\"display:none\">";
	print "<table border=\"1\" class=\"tablefin\" id=\"id_table_nb_hospit_per_unit_per_year_tableau\">
	<thead>
	<tr><th>".get_translation('UNIT','Unité')."</th>";
	for ($year=$year_min;$year<=$year_max;$year++) {
		print "<th class=\"no-sort\">$year</th>";
	}
	print "<th>".get_translation('TOTAL','Total')."</th></tr>
	</thead>
	<tbody>";
		
	foreach ($tableau_nb_patient_unit as $unit_num => $nb_patient) {
                $unit_str=get_unit_str ($unit_num);    
		print "<tr class=\"over_color\"><td nowrap>$unit_str</td>";
		for ($year=$year_min;$year<=$year_max;$year++) {
			if ($tableau_nb_patient_unit_year[$unit_num][$year]=='') {
				print "<td style=\"color:#cccccc\">0</td>";
			} else {
				print "<td>".$tableau_nb_patient_unit_year[$unit_num][$year]."</td>";
			}
		}
		print "<td>$nb_patient</td>";
		print "</tr>";
	}
	print "</tbody></table>";
	print "</div>";
		
}
?>