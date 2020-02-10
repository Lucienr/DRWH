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
include_once("fonctions_regexp.php");
//$user_num_session $tmpresult_num $process_num $datamart_num \"$regexp
$user_num_session=$argv[1];
$tmpresult_num=$argv[2];
$process_num=$argv[3];
$datamart_num=$argv[4];
$regexp=$argv[5];
$filter_query_user_right=$argv[6];
//($process_num,$user_num_session,$status,$commentary,$result,$process_end_date,$category_process)
create_process ($process_num,$user_num_session,0,'','',"sysdate+3",'regexp');


$res= "<a href=\"export_process.php?process_num=$process_num\">Telecharger sur excel</a><br><br>";
$res.= "<table class=\"tableau_solid_black\" id=\"id_tableau_liste_document\">";
$res.= "<thead><tr>";
$res.= "<th>".get_translation('HOSPITAL_PATIENT_ID',"IPP")."</th>";
$res.= "<th>".get_translation('PATIENT_IDENTITY',"Nom Prénom")."</th>";
$res.= "<th>".get_translation('DATE_DOCUMENT',"Date document")."</th>";
$res.= "<th>".get_translation('TITLE',"Titre")."</th>";
$res.= "<th>".get_translation('PATIENT_AGE',"Age patient")."</th>";
$res.= "<th>".get_translation('PATIENT_AGE_MONTH',"Age patient mois")."</th>";
$res.= "<th>".get_translation('CONTEXT',"Contexte")."</th>";
$j=preg_match_all("/(\()/i","$regexp",$out, PREG_SET_ORDER);
for ($i=1;$i<=$j;$i++) {
	$res.= "<th>".get_translation('PATTERN',"Motif ")."$i</th>";
}
$res.= "</tr></thead>";
update_process ($process_num,'0',"entete calculee",$res,$user_num_session,"xls");
$res='';

$liste_document=get_dwh_text('','',$tmpresult_num,"$filter_query_user_right",$user_num_session,'text',0,'','');

foreach ($liste_document as $document_num => $document) {
	$patient_num=$document['patient_num'];
	$document_num=$document['document_num'];
	$encounter_num=$document['encounter_num'];
	$title=$document['title'];
	$document_date=$document['document_date'];
	$document_origin_code=$document['document_origin_code'];
	$author=$document['author'];
	$age_patient=$document['age_patient'];
	$age_patient_month=$document['age_patient_month'];
	$text=$document['text'];
	
	$nb_document++;	
	if ($text!='') {		
		$text=preg_replace("/\n/"," ",$text);
		$out=array();
		if (preg_match_all("/.{0,20}$regexp.{0,20}/i","$text",$out, PREG_SET_ORDER)) {
  			$tab_patient=get_patient ($patient_num);
			$res.= "<tr onmouseout=\"this.style.backgroundColor='#ffffff';\" onmouseover=\"this.style.backgroundColor='#B9C2C8';\" onclick=\"afficher_document_regexp($document_num);\" style=\"cursor: pointer; background-color:#ffffff;\" id=\"id_button_regexp_$document_num\" class=\"tr_document_patient button_regexp\" sousgroupe=\"text\">";
			$res.= "<td>".$tab_patient['HOSPITAL_PATIENT_ID']."</td>";
			$res.= "<td>".$tab_patient['LASTNAME']." ".$tab_patient['FIRSTNAME']."</td>";
			$res.= "<td>$document_date</td><td>$title</td>";
			$res.= "<td>$age_patient</td>";
			$res.= "<td>$age_patient_month</td>";
			foreach ($out[0] as $val) {
				$res.= "<td>".$val."</td>";
			}
			$res.= "</tr>";
			if ($nb_document % 50==0) {
				update_process ($process_num,'0',"$nb_document",$res,$user_num_session,"xls");
				$res='';
			}
		}
	}
}
$res.= "</table>";
update_process ($process_num,'1',"extraction.xls",$res,$user_num_session,"xls");
sauver_notification ($user_num_session,$user_num_session,'process',"",$process_num);


oci_close ($dbh);
oci_close ($dbh_etl);
?>