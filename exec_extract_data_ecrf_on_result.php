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

//$option_une_ligne_par_patient_document="patient";

print " user_num_session $user_num_session\n
tmpresult_num $tmpresult_num\n
process_num $process_num\n
datamart_num $datamart_num\n
ecrf_num $ecrf_num\n
filter_query_user_right $filter_query_user_right\n
option_perimetre $option_perimetre\n
option_une_ligne $option_une_ligne\n";



create_process ($process_num,$user_num_session,0,'','',"sysdate+3",'regexp');

$tableau_list_ecrf_items=get_list_ecrf_items ($ecrf_num);


$entete= "<a href=\"export_process.php?process_num=$process_num\">Telecharger sur excel</a><br><br>";
$entete.= "<table class=\"tableau_solid_black\" id=\"id_tableau_liste_document\">";
$entete.= "<thead><tr>";
$entete.= "<th>".get_translation('HOSPITAL_PATIENT_ID',"IPP")."</th>";
$entete.= "<th>".get_translation('PATIENT_IDENTITY',"Nom Prénom")."</th>";
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
        $item_list=$item['item_list'];
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
if ($option_une_ligne=='patient') {
	$tableau_patient=get_list_patients_in_result ($tmpresult_num,$user_num_session,$filter_query_user_right);
	$nb_patient=0;
	foreach ($tableau_patient as $patient_num ) {
		$nb_patient++;
	  	$tab_patient=get_patient ($patient_num);
		$res= "<tr onmouseout=\"this.style.backgroundColor='#ffffff';\" onmouseover=\"this.style.backgroundColor='#B9C2C8';\" onclick=\"afficher_document($document_num);\" style=\"cursor: pointer; background-color:#ffffff;\" id=\"id_document_patient_$document_num\" class=\"tr_document_patient\" sousgroupe=\"text\">";
		$res.= "<td>".$tab_patient['HOSPITAL_PATIENT_ID']."</td>";
		$res.= "<td>".$tab_patient['LASTNAME']." ".$tab_patient['FIRSTNAME']."</td>";
		foreach ($tableau_list_ecrf_items as $item) {
		        $ecrf_item_num=$item['ecrf_item_num'];
		        $item_str=$item['item_str'];
		        $item_type=$item['item_type'];
		        //$item_list=$item['item_list'];
		        $item_ext_name=$item['item_ext_name'];
		        $item_ext_code=$item['item_ext_code'];
		        $regexp=$item['regexp'];
		        $regexp_index=$item['regexp_index'];
		        $item_local_code=$item['item_local_code'];
		        
			list($table_value,$table_appercu)=extract_information_ecrf($patient_num,$ecrf_num,$ecrf_item_num,$filter_query_option_perimetre);
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
		$tableau_document=get_list_objects_in_result ($tmpresult_num,$user_num_session,"$filter_query_user_right");
	} else {
		$tableau_patient=get_list_patients_in_result ($tmpresult_num,$user_num_session,'');
		$tableau_document=array();
		foreach ($tableau_patient as $patient_num) {
			$table_document=get_document_for_a_patient($patient_num,"");
			print "get_document_for_a_patient($patient_num,);\n";
			print count($table_document)."\n";
			$tableau_document=array_merge($table_document,$tableau_document);
		}
	}
	$nb_document=0;
	foreach ($tableau_document as $document_num ) {
		$nb_document++;
	
		$document=get_document ($document_num);
		$patient_num=$document['patient_num'];
	  	$tab_patient=get_patient ($patient_num);
	  	
		$list_result_doc='';
		$resultat_obtenu='';
		foreach ($tableau_list_ecrf_items as $item) {
		        $ecrf_item_num=$item['ecrf_item_num'];
		        $item_str=$item['item_str'];
		        $item_type=$item['item_type'];
		        $item_list=$item['item_list'];
		        $item_ext_name=$item['item_ext_name'];
		        $item_ext_code=$item['item_ext_code'];
		        $regexp=$item['regexp'];
		        $regexp_index=$item['regexp_index'];
		        $item_local_code=$item['item_local_code'];
		        
			list($table_value,$table_appercu)=extract_information_ecrf($patient_num,$ecrf_num,$ecrf_item_num,"and document_num=$document_num ");
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
			$res.= "<td>".$tab_patient['HOSPITAL_PATIENT_ID']."</td>";
			$res.= "<td>".$tab_patient['LASTNAME']." ".$tab_patient['FIRSTNAME']."</td>";
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


update_process ($process_num,'1',"extraction.xls","</table>",$user_num_session,"xls");
sauver_notification ($user_num_session,$user_num_session,'process',"",$process_num);

?>