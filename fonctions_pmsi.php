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

function graph_pmsi_json_ancien ($tmpresult_num,$thesaurus_data_father_num,$thesaurus_code,$type,$distance) {
	global $dbh,$user_num_session;
	
	$tableau_nb_total=array();
	$tableau_concept_str=array();
	$tableau_nb=array();
	$tableau_sous_category=array();
	
	if ($type=='sejour') {
		$req_type=" c.encounter_num in ( select encounter_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num and encounter_num is not null) and ";
	} else {
		$req_type=" c.patient_num in ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and ";
	}
	
	$req="select list_values from  dwh_thesaurus_data  where  thesaurus_code='$thesaurus_code' ";
	$selval=oci_parse($dbh,"$req ");
	oci_execute($selval);
	$res=oci_fetch_array($selval,OCI_RETURN_NULLS+OCI_ASSOC);
	$list_values=$res['LIST_VALUES'];
	$tableau_liste_valeur=explode(';',$list_values);
	foreach ($tableau_liste_valeur as $valeur) {
		if ($valeur!='') {
			$tableau_sous_category[$valeur]=$valeur;
		}
	}
	if ($distance==1) {
		$req="SELECT t.thesaurus_data_num,
			         concept_str,
			         val_text,
			         COUNT (DISTINCT patient_num) AS nb
			    FROM (
			    SELECT  b.thesaurus_data_father_num as thesaurus_data_num , patient_num, val_text
			            FROM 
			                  dwh_data c,dwh_thesaurus_data_graph b
			           WHERE c.thesaurus_data_num=b.thesaurus_data_son_num and
			                  b.thesaurus_code = '$thesaurus_code'
			           AND
			             b.thesaurus_data_father_num in (select  a.thesaurus_data_son_num from dwh_thesaurus_data_graph a where  a.thesaurus_data_father_num = $thesaurus_data_father_num AND a.thesaurus_code = '$thesaurus_code'  AND a.distance = $distance)
			                    AND   $req_type   c.thesaurus_code = '$thesaurus_code'
				) t,
			         dwh_thesaurus_data
			   WHERE t.thesaurus_data_num = dwh_thesaurus_data.thesaurus_data_num
			GROUP BY  t.thesaurus_data_num, concept_str, val_text";

	}else {
		$req=" select thesaurus_data_num,concept_str,val_text, count(distinct patient_num) as nb from (
			select a.thesaurus_data_son_num ,patient_num,val_text from dwh_thesaurus_data_graph a, dwh_thesaurus_data_graph b, dwh_data c
			     where a.thesaurus_data_father_num=$thesaurus_data_father_num  and a.thesaurus_code='$thesaurus_code'  and b.thesaurus_code='$thesaurus_code'  and c.thesaurus_code='$thesaurus_code' and
			   $req_type
						 a.distance=$distance and
			   a.thesaurus_data_son_num=b.thesaurus_data_father_num and
			     b.thesaurus_data_son_num=c.thesaurus_data_num 
			     
			    union
			    
			select thesaurus_data_son_num  ,patient_num,val_text from dwh_thesaurus_data_graph a, dwh_data c
			     where a.thesaurus_data_father_num=$thesaurus_data_father_num  and a.thesaurus_code='$thesaurus_code'  and   c.thesaurus_code='$thesaurus_code' and
			   		$req_type
						 a.distance<=$distance and
			   a.thesaurus_data_son_num=c.thesaurus_data_num 
			     ) t, dwh_thesaurus_data
			     where thesaurus_data_son_num=thesaurus_data_num
			group by thesaurus_data_num, concept_str,val_text";
	}
	$selval=oci_parse($dbh,"$req ");
	oci_execute($selval);
	while ($res=oci_fetch_array($selval,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$thesaurus_data_num=$res['THESAURUS_DATA_NUM'];
		$concept_str=$res['CONCEPT_STR'];
		$val_text=$res['VAL_TEXT'];
		$nb=$res['NB'];
		$tableau_sous_category[$val_text]=$val_text;
		
		$tableau_nb_total[$thesaurus_data_num]+=$nb;
		$tableau_nb[$thesaurus_data_num][$val_text]=$nb;
		$tableau_concept_str[$thesaurus_data_num]=$concept_str;
	}
	asort($tableau_nb_total);
	ksort($tableau_sous_category);
	$liste_series='';
	foreach ($tableau_sous_category as $sous_category => $cat) {
	        $liste_data='';
		foreach ($tableau_nb_total as $thesaurus_data_num => $nb_total) {
			if ($tableau_nb[$thesaurus_data_num][$sous_category]=='') {
				$tableau_nb[$thesaurus_data_num][$sous_category]=0;
			}
			$liste_data.=$tableau_nb[$thesaurus_data_num][$sous_category].",";
		}	
		$liste_data=substr($liste_data,0,-1);
		$liste_series.="{
	                name: '$sous_category',
	                data: [$liste_data]},";
	}
	$liste_series=substr($liste_series,0,-1);
	
	if (is_array($tableau_concept_str)) {
		
		$liste_category='';
		$nb_c=0;
		foreach ($tableau_nb_total as $thesaurus_data_num => $nb_total) {
			$concept_str=str_replace("'"," ",$tableau_concept_str[$thesaurus_data_num]);
			$liste_category.="'$concept_str',";
			$nb_c++;
		}
		$liste_category=substr($liste_category,0,-1);
		$height=150+$nb_c*24;
	}
	if ($liste_category!='') {
		return "$liste_category;separateur;$liste_series;separateur;$height";
	} else {
		return "";
	}
}

function max_distance_pmsi ($tmpresult_num,$thesaurus_code) {
	global $dbh,$user_num_session;
	
	$req="select  max(distance) as distance_max  from 
		dwh_thesaurus_data_graph a, 
		dwh_data b 
	         where a.thesaurus_data_father_num=0 and
	         a.thesaurus_data_son_num=b.thesaurus_data_num and
	         b.thesaurus_code='$thesaurus_code' and a.thesaurus_code='$thesaurus_code' and
	      	 b.patient_num in ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num)  ";
	$selval=oci_parse($dbh,"$req ");
	oci_execute($selval);
	$res=oci_fetch_array($selval,OCI_RETURN_NULLS+OCI_ASSOC);
	$distance_max=$res['DISTANCE_MAX'];
	
	return $distance_max;
}




function graph_pmsi_json ($tmpresult_num,$thesaurus_data_father_num,$thesaurus_code,$type,$distance) {
	global $dbh,$user_num_session;
	
	$tableau_nb_total=array();
	$tableau_concept_str=array();
	$tableau_nb=array();
	$tableau_nb_detaille=array();
	$tableau_sous_category=array();
	
	if ($type=='sejour') {
		$req_type="  exists  ( select encounter_num from dwh_tmp_result_$user_num_session where dwh_data.encounter_num=dwh_tmp_result_$user_num_session.encounter_num and tmpresult_num=$tmpresult_num and encounter_num is not null)  and encounter_num is not null ";
		$req_type="  dwh_data.encounter_num in  ( select encounter_num from dwh_tmp_result_$user_num_session where  tmpresult_num=$tmpresult_num and encounter_num is not null)  and encounter_num is not null ";
	} else {
		$req_type="  exists  ( select patient_num from dwh_tmp_result_$user_num_session where dwh_data.patient_num=dwh_tmp_result_$user_num_session.patient_num and tmpresult_num=$tmpresult_num and patient_num is not null)  ";
		$req_type="  dwh_data.patient_num in  ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num  )  ";
	}
	
	$req="select list_values from  dwh_thesaurus_data  where  thesaurus_code='$thesaurus_code' ";
	$selval=oci_parse($dbh,"$req ");
	oci_execute($selval);
	$res=oci_fetch_array($selval,OCI_RETURN_NULLS+OCI_ASSOC);
	$list_values=$res['LIST_VALUES'];
	$tableau_liste_valeur=explode(';',$list_values);
	foreach ($tableau_liste_valeur as $valeur) {
		if ($valeur!='') {
			$tableau_sous_category[$valeur]=$valeur;
		}
	}
	
	$req_nb="SELECT thesaurus_data_num,val_text,patient_num from dwh_data where thesaurus_code='$thesaurus_code' and $req_type   ";
	$sel_nb=oci_parse($dbh,"$req_nb ");
	oci_execute($sel_nb);
	while ($r=oci_fetch_array($sel_nb,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$thesaurus_data_num=$r['THESAURUS_DATA_NUM'];
		$val_text=$r['VAL_TEXT'];
  		$patient_num=$r['PATIENT_NUM'];
		$tableau_nb_detaille[$thesaurus_data_num][$val_text].=";$patient_num;";
	}
	
	$req="SELECT b.thesaurus_data_num,
		         concept_str
		    FROM  dwh_thesaurus_data_graph a,dwh_thesaurus_data b      
		           WHERE a.thesaurus_data_son_num=b.thesaurus_data_num 
		           and b.thesaurus_code = '$thesaurus_code'
		           and a.thesaurus_code = '$thesaurus_code'
		           and distance=$distance 
		           and thesaurus_data_father_num = $thesaurus_data_father_num";
	$selval=oci_parse($dbh,"$req ");
	oci_execute($selval);
	while ($res=oci_fetch_array($selval,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$thesaurus_data_num=$res['THESAURUS_DATA_NUM'];
		$concept_str=$res['CONCEPT_STR'];
		$tableau_concept_str[$thesaurus_data_num]=$concept_str;
		$nb=0;
		$liste_patient_num=array();
		$req_nb="select thesaurus_data_son_num from dwh_thesaurus_data_graph where thesaurus_data_father_num=$thesaurus_data_num and thesaurus_code='$thesaurus_code'";
		$sel_nb=oci_parse($dbh,"$req_nb ");
		oci_execute($sel_nb);
		while ($r=oci_fetch_array($sel_nb,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$thesaurus_data_son_num=$r['THESAURUS_DATA_SON_NUM'];
			foreach ($tableau_sous_category  as $sous_category => $val_text) {
				$liste_patient_num[$val_text].=$tableau_nb_detaille[$thesaurus_data_son_num][$val_text];
			}
		}
		
		foreach ($tableau_sous_category  as $sous_category => $val_text) {
			$tableau_patient_num=array_unique(explode(";",$liste_patient_num[$val_text]));
			
			$nb=count($tableau_patient_num)-1;
			if ($nb>0) {
				$tableau_nb_total[$thesaurus_data_num]+=$nb;
				$tableau_nb[$thesaurus_data_num][$val_text]+=$nb;
			}
		}
		
			
	}
	asort($tableau_nb_total);
	ksort($tableau_sous_category);
	$liste_series='';
	foreach ($tableau_sous_category as $sous_category => $cat) {
	        $liste_data='';
		foreach ($tableau_nb_total as $thesaurus_data_num => $nb_total) {
			if ($tableau_nb[$thesaurus_data_num][$sous_category]=='') {
				$tableau_nb[$thesaurus_data_num][$sous_category]=0;
			}
			$liste_data.=$tableau_nb[$thesaurus_data_num][$sous_category].",";
		}	
		$liste_data=substr($liste_data,0,-1);
		$liste_series.="{
	                name: '$sous_category',
	                data: [$liste_data]},";
	}
	$liste_series=substr($liste_series,0,-1);
	
	if (is_array($tableau_concept_str)) {
		
		$liste_category='';
		$nb_c=0;
		foreach ($tableau_nb_total as $thesaurus_data_num => $nb_total) {
			$concept_str=str_replace("'"," ",$tableau_concept_str[$thesaurus_data_num]);
			$concept_str=trim($concept_str);
			$liste_category.="'$concept_str',";
			$nb_c++;
		}
		$liste_category=substr($liste_category,0,-1);
		$height=150+$nb_c*24;
	}
	if ($liste_category!='') {
		return "$liste_category;separateur;$liste_series;separateur;$height";
	} else {
		return ";separateur;;separateur;";
	}
}


function affiche_tableau_pmsi ($tmpresult_num,$thesaurus_code,$type) {
	global $dbh,$user_num_session;
	
	$tableau_nb_total=array();
	$tableau_concept_str=array();
	$tableau_nb=array();
	$tableau_nb_detaille=array();
	$tableau_sous_category=array();
	
	if ($type=='sejour') {
		$req_type="  exists  ( select encounter_num from dwh_tmp_result_$user_num_session where dwh_data.encounter_num=dwh_tmp_result_$user_num_session.encounter_num and tmpresult_num=$tmpresult_num and encounter_num is not null)  and encounter_num is not null ";
		$req_type="  dwh_data.encounter_num in  ( select encounter_num from dwh_tmp_result_$user_num_session where  tmpresult_num=$tmpresult_num and encounter_num is not null)  and encounter_num is not null ";
	} else {
		$req_type="  exists  ( select patient_num from dwh_tmp_result_$user_num_session where dwh_data.patient_num=dwh_tmp_result_$user_num_session.patient_num and tmpresult_num=$tmpresult_num and patient_num is not null)  ";
		$req_type="  dwh_data.patient_num in  ( select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num  )  ";
	}

	$req_nb="SELECT thesaurus_data_num,val_text,count(*) as nb_code,count(distinct patient_num) as nb from dwh_data where thesaurus_code='$thesaurus_code' and $req_type  group by  thesaurus_data_num,val_text";
	$sel_nb=oci_parse($dbh,"$req_nb ");
	oci_execute($sel_nb);
	while ($r=oci_fetch_array($sel_nb,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$thesaurus_data_num=$r['THESAURUS_DATA_NUM'];
		$val_text=$r['VAL_TEXT'];
  		$nb=$r['NB'];
  		$nb_code=$r['NB_CODE'];
		$tableau_nb_code[$thesaurus_data_num][$val_text]="$nb_code";
		$tableau_nb_patients[$thesaurus_data_num][$val_text]="$nb";
	}
	
	$req_nb="SELECT thesaurus_data_num,count(*) as NB_CODE,count(distinct patient_num) as nb from dwh_data where thesaurus_code='$thesaurus_code' and $req_type  group by  thesaurus_data_num";
	$sel_nb=oci_parse($dbh,"$req_nb ");
	oci_execute($sel_nb);
	while ($r=oci_fetch_array($sel_nb,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$thesaurus_data_num=$r['THESAURUS_DATA_NUM'];
		$val_text=$r['VAL_TEXT'];
  		$nb=$r['NB'];
  		$nb_code=$r['NB_CODE'];
		$tableau_nb_code[$thesaurus_data_num]['total']=0+$nb_code+$tableau_nb_code[$thesaurus_data_num]['total'];
		$tableau_nb_patients[$thesaurus_data_num]['total']=0+$nb+$tableau_nb_patients[$thesaurus_data_num]['total'];
	}
	
	$json_tableau='';
	$req="select thesaurus_data_num,concept_str,list_values,concept_code from  dwh_thesaurus_data  where  thesaurus_code='$thesaurus_code' ";
	$selval=oci_parse($dbh,"$req ");
	oci_execute($selval);
	while ($res=oci_fetch_array($selval,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$thesaurus_data_num=$res['THESAURUS_DATA_NUM'];
		$concept_str=$res['CONCEPT_STR'];
		$list_values=$res['LIST_VALUES'];
		$concept_code=$res['CONCEPT_CODE'];
		$tab_list_values=explode(";",$list_values);
		$concept_str=preg_replace("/\n/"," ",$concept_str);
		foreach ($tab_list_values as $val_text) {
			if ($tableau_nb_code[$thesaurus_data_num][$val_text]!='') {
					$json_tableau.= " [
						     \"$concept_code-$concept_str\",
						     \"$val_text\",
						 \"".$tableau_nb_code[$thesaurus_data_num][$val_text]."\",
						 \"".$tableau_nb_patients[$thesaurus_data_num][$val_text]."\"
					    ],";
			}
		}
		if ($tableau_nb_code[$thesaurus_data_num]['total']!='') {
				$json_tableau.= " [
					     \"$concept_code-$concept_str\",
					     \"total\",
					 \"".$tableau_nb_code[$thesaurus_data_num]['total']."\",
					 \"".$tableau_nb_patients[$thesaurus_data_num]['total']."\"
				    ],";
		}
	}
	$json_tableau=substr($json_tableau,0,-1);
	
	return "$json_tableau";
}
?>