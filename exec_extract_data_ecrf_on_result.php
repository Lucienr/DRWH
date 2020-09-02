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

ini_set("memory_limit","800M");
putenv("NLS_LANG=French");

//$user_num_session $tmpresult_num $process_num $datamart_num \"$ecrf_num\" \"$filter_query_user_right
include_once "parametrage.php";
include_once "connexion_bdd.php";
include_once("fonctions_droit.php");
include_once("fonctions_dwh.php");
include_once("fonctions_ecrf.php");

$user_num_session=$argv[1];
$tmpresult_num=$argv[2];
$process_num=$argv[3];
$datamart_num=$argv[4];
$ecrf_num=$argv[5];
$filter_query_user_right=$argv[6];
$option_perimetre=$argv[7]; // patient / document
$option_une_ligne=$argv[8]; // patient / document
$cohort_num=$argv[9]; // patient / document
$ecrf_name_extraction=$argv[10]; // 
if ($ecrf_name_extraction=='') {
	$ecrf_name_extraction="Extraction";
}


//$option_une_ligne_par_patient_document="patient";
$sel=oci_parse($dbh,"select right from dwh_user_profile,dwh_profile_right  where dwh_user_profile.user_profile=dwh_profile_right.user_profile and user_num=$user_num_session");
oci_execute($sel);
while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
	$right=$r['RIGHT'];
	$tableau_droit[$right]='ok';
}

print "user_num_session $user_num_session\n
tmpresult_num $tmpresult_num\n
process_num $process_num\n
datamart_num $datamart_num\n
ecrf_num $ecrf_num\n
filter_query_user_right $filter_query_user_right\n
option_perimetre $option_perimetre\n
option_une_ligne $option_une_ligne\n";

create_process ($process_num,$user_num_session,0,'','',"sysdate+15",'ecrf_on_result');

$tableau_list_ecrf_items=get_list_ecrf_items ($ecrf_num,"");

$entete= "<a href=\"export_process.php?process_num=$process_num\">Telecharger sur excel</a><br><br>";
$entete.= "<table class=\"tableau_solid_black\" id=\"id_tableau_liste_result_ecrf\">";
$entete.= "<thead><tr>";
$entete.= "<th>".get_translation('HOSPITAL_PATIENT_ID',"IPP")."</th>";
$entete.= "<th>".get_translation('PATIENT_IDENTITY',"Nom Prénom")."</th>";
$entete.= "<th>".get_translation('PATIENT_SEX',"Sexe")."</th>";
if ($option_une_ligne=='document') {
	$entete.= "<th>".get_translation('DATE_DOCUMENT',"Date document")."</th>";
	$entete.= "<th>".get_translation('PATIENT_AGE',"Age patient")."</th>";
	$entete.= "<th>".get_translation('TITLE',"Titre")."</th>";
	$entete.= "<th>".get_translation('AUTHOR',"Auteur")."</th>";
}
foreach ($tableau_list_ecrf_items as $item) {
        $ecrf_item_num=$item['ecrf_item_num'];
        $item_str=$item['item_str'];
        $item_type=$item['item_type'];
        $document_search=$item['document_search'];
        $item_ext_name=$item['item_ext_name'];
	$entete.= "<th>$item_str</th>";
}
$entete.= "</tr></thead>";
update_process ($process_num,0,"Extraction patient",$entete,$user_num_session,'') ;


if ($option_perimetre=='document') {
	$filter_query_option_perimetre=" and document_num in (select document_num from dwh_tmp_result_$user_num_session WHERE tmpresult_num = $tmpresult_num and object_type='document' )";
} else {
	$filter_query_option_perimetre='';
}
print "filter_query_option_perimetre $filter_query_option_perimetre";

$table_document_all_aff=array();
$table_document_all_neg=array();

if ($option_une_ligne=='patient') {
	$ecrf=get_ecrf ($ecrf_num);
        $ecrf_start_date=$ecrf['ecrf_start_date'];
        $ecrf_end_date=$ecrf['ecrf_end_date'];
        $ecrf_start_age=$ecrf['ecrf_start_age'];
        $ecrf_end_age=$ecrf['ecrf_end_age'];
	$query_filter_ecrf='';
        if ($ecrf_patient_event_num!='') {
		$ecrf_patient_event=get_ecrf_patient_event ($ecrf_num ,$patient_num,$ecrf_patient_event_num);
		$date_patient_ecrf=$ecrf_patient_event[0]['date_patient_ecrf'];
		$nb_days_before=$ecrf_patient_event[0]['nb_days_before'];
		$nb_days_after=$ecrf_patient_event[0]['nb_days_after'];
		if ($date_patient_ecrf!='' && $nb_days_before!='' && $nb_days_after!='') {
			$query_filter_ecrf.=" and document_date >= to_date('$date_patient_ecrf','DD/MM/YYYY')-$nb_days_before and  document_date <= to_date('$date_patient_ecrf','DD/MM/YYYY')+$nb_days_after  ";
		}
	}
	if ($ecrf_start_date!='') {
		$query_filter_ecrf.=" and document_date >= to_date('$ecrf_start_date','DD/MM/YYYY') ";
	}
	if ($ecrf_end_date!='') {
		$query_filter_ecrf.=" and document_date <= to_date('$ecrf_end_date','DD/MM/YYYY') ";
	}
	if ($ecrf_start_age!='') {
		$query_filter_ecrf.=" and age_patient >= '$ecrf_start_age' ";
	}
	if ($ecrf_end_age!='') {
		$query_filter_ecrf.=" and age_patient <= '$ecrf_end_age' ";
	}
	if ($filter_query!='') {
		$query_filter_ecrf.=" $filter_query  ";
	}

	$tableau_patient=get_list_patients_in_result ($tmpresult_num,$user_num_session,$filter_query_user_right);
	$nb_patient=0;
	foreach ($tableau_patient as $patient_num => $tab_patient ) {
			
		$table_document_all_aff=get_dwh_text('',$patient_num,'',"$filter_query_user_right",$user_num_session,'patient_text',1,"$query_filter_ecrf $filter_query_option_perimetre",'');
		$table_document_all_neg=get_dwh_text('',$patient_num,'',"$filter_query_user_right",$user_num_session,'patient_text',-1,"$query_filter_ecrf $filter_query_option_perimetre",'');

	
		$nb_patient++;
		$res= "<tr onmouseout=\"this.style.backgroundColor='#ffffff';\" onmouseover=\"this.style.backgroundColor='#B9C2C8';\" style=\"cursor: pointer; background-color:#ffffff;\" id=\"id_document_patient_$patient_num\" class=\"tr_document_patient\" sousgroupe=\"text\">";
		$res.= "<td class='text'>".$tab_patient['HOSPITAL_PATIENT_ID']."</td>";
		$res.= "<td>".$tab_patient['LASTNAME']." ".$tab_patient['FIRSTNAME']."</td>";
		$res.= "<td>".$tab_patient['SEX']."</td>";
		foreach ($tableau_list_ecrf_items as $item) {
		        $ecrf_item_num=$item['ecrf_item_num'];
		        $item_str=$item['item_str'];
		        $item_type=$item['item_type'];
		        $document_search=$item['document_search'];
		        $item_ext_name=$item['item_ext_name'];
		        $item_ext_code=$item['item_ext_code'];
		        $regexp=$item['regexp'];
		        $regexp_index=$item['regexp_index'];
		        $item_local_code=$item['item_local_code'];
		        
		        
			list($table_value,$table_appercu)=extract_information_ecrf($patient_num,$ecrf_num,$ecrf_item_num,$filter_query_option_perimetre,'',$table_document_all_aff,$table_document_all_neg);
			//$tab_value_global= array_filter(explode(";",$list_value_global), 'strlen');
	
		      	$valeur_final='';
			if ($item_type=='radio' || $item_type=='list') {
			
				$tab_sub_items=get_list_ecrf_sub_items ($ecrf_item_num);

		 		foreach ($tab_sub_items as $sub_item) {
					$sub_item_local_str=$sub_item['sub_item_local_str'];
					$sub_item_local_code=$sub_item['sub_item_local_code'];
					$sub_item_ext_code=$sub_item['sub_item_ext_code'];
					$sub_item_ext_name=$sub_item['sub_item_ext_name'];
					foreach ($table_value as $tab) {
						$val=$tab['sous_item_value'];
						$concept_str=$tab['concept_str'];
						$concept_code=$tab['concept_code'];
						$info=$tab['info'];
						$document_date=$tab['document_date'];
						if (
						// if code == code du sous item
						($sub_item_local_code==$concept_code && $concept_code!='' && $sub_item_local_code!='') 
						// or sub label = value of data or label + sub label and value of data 
						|| ((trim(strtolower("$sub_item_local_str"))==trim(strtolower($val))  || trim(strtolower("$item_str $sub_item_local_str"))==trim(strtolower($val))) && trim($val)!='' && trim($sub_item_local_str)!='')
						// or sub label = label of data
						|| ( trim(strtolower("$sub_item_local_str"))==trim(strtolower($concept_str)) && trim($concept_str)!='' && trim($sub_item_local_str)!='')
						) {
							$valeur_final.= "$sub_item_local_str ; ";
						}
					}
				}
			} else {
				foreach ($table_value as $tab) {
					$val=$tab['sous_item_value'];
					$concept_str=$tab['concept_str'];
					$concept_code=$tab['concept_code'];
					$info=$tab['info'];
					$document_date=$tab['document_date'];
					$valeur_final="$val ; ";
				}
			}
			$valeur_final=substr($valeur_final,0,-2);
			$res.=  "<td>$valeur_final</td>";
		}
		$res.=  "</tr>";
		update_process ($process_num,0,"Extraction ecrf patient $nb_patient","$res",$user_num_session,'');
	}
}




if ($option_une_ligne=='patient_old') {
	$tableau_patient=get_list_patients_in_result ($tmpresult_num,$user_num_session,$filter_query_user_right);
	$nb_patient=0;
	foreach ($tableau_patient as $patient_num ) {
		$nb_patient++;
	  	$tab_patient=get_patient ($patient_num);
		$res= "<tr onmouseout=\"this.style.backgroundColor='#ffffff';\" onmouseover=\"this.style.backgroundColor='#B9C2C8';\" style=\"cursor: pointer; background-color:#ffffff;\" id=\"id_document_patient_$patient_num\" class=\"tr_document_patient\" sousgroupe=\"text\">";
		$res.= "<td class='text'>".$tab_patient['HOSPITAL_PATIENT_ID']."</td>";
		$res.= "<td>".$tab_patient['LASTNAME']." ".$tab_patient['FIRSTNAME']."</td>";
		foreach ($tableau_list_ecrf_items as $item) {
		        $ecrf_item_num=$item['ecrf_item_num'];
		        $item_str=$item['item_str'];
		        $item_type=$item['item_type'];
		        $document_search=$item['document_search'];
		        $item_ext_name=$item['item_ext_name'];
		        $item_ext_code=$item['item_ext_code'];
		        $regexp=$item['regexp'];
		        $regexp_index=$item['regexp_index'];
		        $item_local_code=$item['item_local_code'];
		        
			list($table_value,$table_appercu)=extract_information_ecrf($patient_num,$ecrf_num,$ecrf_item_num,$filter_query_option_perimetre,'',$table_document_all_aff,$table_document_all_neg);
			//$tab_value_global= array_filter(explode(";",$list_value_global), 'strlen');
	
		      	$valeur_final='';
			if ($item_type=='radio' || $item_type=='list') {
			
				$tab_sub_items=get_list_ecrf_sub_items ($ecrf_item_num);

		 		foreach ($tab_sub_items as $sub_item) {
					$sub_item_local_str=$sub_item['sub_item_local_str'];
					$sub_item_local_code=$sub_item['sub_item_local_code'];
					$sub_item_ext_code=$sub_item['sub_item_ext_code'];
					$sub_item_ext_name=$sub_item['sub_item_ext_name'];
					foreach ($table_value as $tab) {
						$val=$tab['sous_item_value'];
						$concept_str=$tab['concept_str'];
						$concept_code=$tab['concept_code'];
						$info=$tab['info'];
						$document_date=$tab['document_date'];
						if (
						// if code == code du sous item
						($sub_item_local_code==$concept_code && $concept_code!='' && $sub_item_local_code!='') 
						// or sub label = value of data or label + sub label and value of data 
						|| ((trim(strtolower("$sub_item_local_str"))==trim(strtolower($val))  || trim(strtolower("$item_str $sub_item_local_str"))==trim(strtolower($val))) && trim($val)!='' && trim($sub_item_local_str)!='')
						// or sub label = label of data
						|| ( trim(strtolower("$sub_item_local_str"))==trim(strtolower($concept_str)) && trim($concept_str)!='' && trim($sub_item_local_str)!='')
						) {
							$valeur_final.= "$sub_item_local_str ; ";
						}
					}
				}
			} else {
				foreach ($table_value as $tab) {
					$val=$tab['sous_item_value'];
					$concept_str=$tab['concept_str'];
					$concept_code=$tab['concept_code'];
					$info=$tab['info'];
					$document_date=$tab['document_date'];
					$valeur_final="$val ; ";
				}
			}
			$valeur_final=substr($valeur_final,0,-2);
			$res.=  "<td>$valeur_final</td>";
		}
		$res.=  "</tr>";
		update_process ($process_num,0,"Extraction ecrf patient $nb_patient","$res",$user_num_session,'');
	}
}



if ($option_une_ligne=='document') {
	if ($option_perimetre=='document') {
		#$tableau_document=get_list_objects_in_result ($tmpresult_num,$user_num_session,"$filter_query_user_right");
		$tableau_document=get_dwh_document ('','',$tmpresult_num,$filter_query_user_right,$user_num_session,'','not_displayed_text');
	} else {
		//$tableau_patient=get_list_patients_in_result ($tmpresult_num,$user_num_session,'');
		//$tableau_document=array();
		//foreach ($tableau_patient as $patient_num) {
		//	$table_document=get_document_for_a_patient($patient_num,"");
		//	$tableau_document=array_merge($table_document,$tableau_document);
		//}
		$tableau_document=get_dwh_document ('','','',$filter_query_user_right,$user_num_session,"and patient_num in (SELECT  document_num FROM dwh_tmp_result_$user_num_session WHERE tmpresult_num = $tmpresult_num    $filter_query_user_right  and object_type='document') ",'not_displayed_text');
	}
	print "\n\n".benchmark ( 'fin get_dwh_document' )."\n\n";
	print "\n\n".benchmark ( 'debut get_list_patients_in_result' )."\n\n";
	$tableau_patient=get_list_patients_in_result ($tmpresult_num,$user_num_session,$filter_query_user_right);
	
	print "\n\n".benchmark ( 'fin get_list_patients_in_result' )."\n\n";
	$nb_document=0;
	foreach ($tableau_document as $document_num => $document) {
		$nb_document++;
		$patient_num=$document['patient_num'];
	  	$tab_patient=$tableau_patient[$patient_num];
	  	
		print "\n\n".benchmark ( 'debut document_num' )."\n\n";
		$list_result_doc='';
		$resultat_obtenu='';
print "\n\n".benchmark ( 'debut tableau_list_ecrf_items' )."\n\n";
		foreach ($tableau_list_ecrf_items as $item) {
		        $ecrf_item_num=$item['ecrf_item_num'];
		        $item_str=$item['item_str'];
		        $item_type=$item['item_type'];
		        $document_search=$item['document_search'];
		        $item_ext_name=$item['item_ext_name'];
		        $item_ext_code=$item['item_ext_code'];
		        $regexp=$item['regexp'];
		        $regexp_index=$item['regexp_index'];
		        $item_local_code=$item['item_local_code'];
		        
			list($table_value,$table_appercu)=extract_information_ecrf($patient_num,$ecrf_num,$ecrf_item_num,"and document_num=$document_num ",'',$table_document_all_aff,$table_document_all_neg);
			//$tab_value_global= array_filter(explode(";",$list_value_global), 'strlen');

		      	$valeur_final='';
			if ($item_type=='radio' || $item_type=='list') {
			
				$tab_sub_items=get_list_ecrf_sub_items ($ecrf_item_num);

		 		foreach ($tab_sub_items as $sub_item) {
					$sub_item_local_str=$sub_item['sub_item_local_str'];
					$sub_item_local_code=$sub_item['sub_item_local_code'];
					$sub_item_ext_code=$sub_item['sub_item_ext_code'];
					$sub_item_ext_name=$sub_item['sub_item_ext_name'];
					foreach ($table_value as $tab) {
						$val=$tab['sous_item_value'];
						$concept_str=$tab['concept_str'];
						$concept_code=$tab['concept_code'];
						$info=$tab['info'];
						$document_date=$tab['document_date'];
						if (
						// if code == code du sous item
						($sub_item_local_code==$concept_code && $concept_code!='' && $sub_item_local_code!='') 
						// or sub label = value of data or label + sub label and value of data 
						|| ((trim(strtolower("$sub_item_local_str"))==trim(strtolower($val))  || trim(strtolower("$item_str $sub_item_local_str"))==trim(strtolower($val))) && trim($val)!='' && trim($sub_item_local_str)!='')
						// or sub label = label of data
						|| ( trim(strtolower("$sub_item_local_str"))==trim(strtolower($concept_str)) && trim($concept_str)!='' && trim($sub_item_local_str)!='')
						) {
							$valeur_final.= "$sub_item_local_str ; ";
						}
					}
				}
			} else {
				foreach ($table_value as $tab) {
					$val=$tab['sous_item_value'];
					$concept_str=$tab['concept_str'];
					$concept_code=$tab['concept_code'];
					$info=$tab['info'];
					$document_date=$tab['document_date'];
					$valeur_final="$val ; ";
				}
			
			}
			$valeur_final=substr($valeur_final,0,-2);
			if ($valeur_final!='') {
				$resultat_obtenu='ok';
			}
			$list_result_doc.=  "<td>$valeur_final</td>";
		}
		if ($resultat_obtenu!='') {
			$res= "<tr onmouseout=\"this.style.backgroundColor='#ffffff';\" onmouseover=\"this.style.backgroundColor='#B9C2C8';\" onclick=\"afficher_document_ecrf($document_num);\" style=\"cursor: pointer; background-color:#ffffff;\" id=\"id_document_patient_ecrf_$document_num\" class=\"tr_document_patient\" sousgroupe=\"text\">";
			$res.= "<td class='text'>".$tab_patient['HOSPITAL_PATIENT_ID']."</td>";
			$res.= "<td>".$tab_patient['LASTNAME']." ".$tab_patient['FIRSTNAME']."</td>";
			$res.= "<td>".$tab_patient['SEX']."</td>";
			$res.= "<td>".$document['document_date']."</td>";
			$res.= "<td>".$document['age_patient']."</td>";
			$res.= "<td>".$document['title']."</td>";
			$res.= "<td>".$document['author']."</td>";
			$res.= $list_result_doc;
			$res.=  "</tr>";
			update_process ($process_num,0,"Extraction ecrf document $nb_document","$res",$user_num_session,'');
		}
	}
}


update_process ($process_num,'1',"$ecrf_name_extraction.xls","</table>",$user_num_session,"xls");
sauver_notification ($user_num_session,$user_num_session,'process',"ECRF - $ecrf_name_extraction",$process_num);


oci_close ($dbh);
oci_close ($dbh_etl);
?>