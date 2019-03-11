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

include_once "parametrage.php";
include_once "connexion_bdd.php";
include_once("fonctions_droit.php");
include_once("fonctions_dwh.php");
//$user_num_session $tmpresult_num $process_num $datamart_num \"$regexp
$user_num_session=$argv[1];
$tmpresult_num=$argv[2];
$process_num=$argv[3];
$datamart_num=$argv[4];
$regexp=$argv[5];
$filter_query_user_right=$argv[6];
//($process_num,$user_num_session,$status,$commentary,$result,$process_end_date,$category_process)
create_process ($process_num,$user_num_session,0,'','',"sysdate+3",'regexp');


$res= "<a href=\"#\" onclick=\"fnSelect('id_tableau_liste_document');return false;\">".get_translation('SELECT_TABLE','Sélectionner le tableau')."</a>";
$res.= "<table class=\"tableau_solid_black\" id=\"id_tableau_liste_document\">";
$res.= "<tr>";
$res.= "<th>".get_translation('HOSPITAL_PATIENT_ID',"IPP")."</th>";
$res.= "<th>".get_translation('PATIENT_IDENTITY',"Nom Prénom")."</th>";
$res.= "<th>".get_translation('DATE_DOCUMENT',"Date document")."</th>";
$res.= "<th>".get_translation('TITLE',"Title")."</th>";
$res.= "<th>".get_translation('PATIENT_AGE',"Age patient")."</th>";
$res.= "<th>".get_translation('CONTEXT',"Contexte")."</th>";
$j=preg_match_all("/(\()/i","$regexp",$out, PREG_SET_ORDER);
for ($i=1;$i<=$j;$i++) {
	$res.= "<th>".get_translation('PATTERN',"Motif ")."$i</th>";
}
$res.= "</tr>";
print "update_process ($process_num,'0',entete calculee,$res);";
update_process ($process_num,'0',"entete calculee",$res);
$res='';

#	$sel_doc = oci_parse($dbh,"SELECT patient_num,
#					       document_num,
#					       encounter_num,
#					       title,
#					       author,
#					       document_date,
#					       document_origin_code,
#					       text,
#					       TO_CHAR (document_date, 'DD/MM/YYYY') AS date_document_char,
#					       age_patient
#					  FROM dwh_text
#					 WHERE    
#					 	CONTEXT = 'text'
#					       AND CERTAINTY = '0'
#					       AND document_num IN (SELECT document_num FROM dwh_tmp_result_$user_num_session WHERE tmpresult_num = $tmpresult_num $filter_query_user_right)
#					       AND REGEXP_LIKE (text, '$regexp', 'i')
#						 order by  patient_num desc " );   
$sel_doc = oci_parse($dbh,"SELECT patient_num,
				       document_num,
				       encounter_num,
				       title,
				       author,
				       document_date,
				       document_origin_code,
				       text,
				       TO_CHAR (document_date, 'DD/MM/YYYY') AS date_document_char,
				       age_patient
				  FROM dwh_text
				 WHERE    
				 	CONTEXT = 'text'
				       AND CERTAINTY = '0'
				       AND document_num IN (SELECT document_num FROM dwh_tmp_result_$user_num_session WHERE tmpresult_num = $tmpresult_num $filter_query_user_right)
					 order by  patient_num desc " );   
print "SELECT patient_num,
document_num,
encounter_num,
title,
author,
document_date,
document_origin_code,
text,
TO_CHAR (document_date, 'DD/MM/YYYY') AS date_document_char,
age_patient
FROM dwh_text
WHERE    
CONTEXT = 'text'
AND CERTAINTY = '0'
AND document_num IN (SELECT document_num FROM dwh_tmp_result_$user_num_session WHERE tmpresult_num = $tmpresult_num $filter_query_user_right)
order by  patient_num desc ";
oci_execute($sel_doc);
while ($row_doc = oci_fetch_array($sel_doc, OCI_ASSOC)) {
	$patient_num=$row_doc['PATIENT_NUM'];
	$document_num=$row_doc['DOCUMENT_NUM'];
	$encounter_num=$row_doc['ENCOUNTER_NUM'];
	$title=$row_doc['TITLE'];
	$date_document_char=$row_doc['DATE_DOCUMENT_CHAR'];
	$document_origin_code=$row_doc['DOCUMENT_ORIGIN_CODE'];
	$author=$row_doc['AUTHOR'];
	$age_patient=$row_doc['AGE_PATIENT'];
	if ($_SESSION['dwh_droit_fuzzy_display']=='ok') {
		$author='[AUTHOR]';
		$date_document_char='[DATE]';
	}
	$nb_document++;	
	if ($row_doc['TEXT']!='') {		
		$text=$row_doc['TEXT']->load();    
		$text=preg_replace("/\n/"," ",$text);
		$out=array();
		if (preg_match_all("/...........$regexp........./i","$text",$out, PREG_SET_ORDER)) {

  			$tab_patient=get_patient ($patient_num);

			$res.= "<tr onmouseout=\"this.style.backgroundColor='#ffffff';\" onmouseover=\"this.style.backgroundColor='#B9C2C8';\" onclick=\"afficher_document($document_num);\" style=\"cursor: pointer; background-color:#ffffff;\" id=\"id_document_patient_$document_num\" class=\"tr_document_patient\" sousgroupe=\"text\">";
			$res.= "<td>".$tab_patient['HOSPITAL_PATIENT_ID']."</td>";
			$res.= "<td>".$tab_patient['LASTNAME']." ".$tab_patient['FIRSTNAME']."</td>";
			$res.= "<td>$date_document_char</td><td>$title</td>";
			$res.= "<td>$age_patient</td>";
			foreach ($out[0] as $val) {
				$res.= "<td>".$val."</td>";
			}
			$res.= "</tr>";
			if ($nb_document % 50==0) {
				update_process ($process_num,'0',"$nb_document",$res);
				$res='';
			}
		}
	}
}
$res.= "</table>";
update_process ($process_num,'1',"end",$res);
$res='';
?>