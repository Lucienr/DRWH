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



$_SESSION=array();
$liste_service_session='';
$liste_uf_session='';
$liste_document_origin_code_session='';
	
//// LES DROITS GLOBAUX DE L'UTILISATEUR //////////
$sel_var1=oci_parse($dbh,"select user_profile from dwh_user_profile where user_num =$user_num_session");
oci_execute($sel_var1);
while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
	$user_profile=$r['USER_PROFILE'];
	$_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_profil_'.$user_profile]='ok';

	$sel_vardroit=oci_parse($dbh,"select right from dwh_profile_right where user_profile='$user_profile'");
	oci_execute($sel_vardroit);
	while ($r_droit=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$right=$r_droit['RIGHT'];
		$_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_droit_'.$right.'0']='ok';
		$_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_droit_'.$right]='ok';
	}
	
}

//// LES SERVICES DE L'UTILISATEUR //////////
$liste_service_session='';
$liste_uf_session='';
$sel_var1=oci_parse($dbh,"select department_num from dwh_user_department where user_num='$user_num_session' ");
oci_execute($sel_var1);
while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
	$department_num_session=$r['DEPARTMENT_NUM'];
	$liste_service_session.="$department_num_session,";
	$sel_var_uf=oci_parse($dbh,"select unit_code from dwh_thesaurus_unit where department_num='$department_num_session' ");
	oci_execute($sel_var_uf);
	while ($r_uf=oci_fetch_array($sel_var_uf,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$unit_code=$r_uf['UNIT_CODE'];
		$liste_uf_session.="'$unit_code',";
	}
}
$liste_service_session=substr($liste_service_session,0,-1);
$liste_uf_session=substr($liste_uf_session,0,-1);

//// LES TYPES DOC DE L'UTILISATEUR //////////
$liste_document_origin_code_session=list_authorized_document_origin_code_for_one_datamart(0,$user_num_session,'sql');



create_process ($process_num,$user_num_session,0,'','',"sysdate+3",'regexp');


$res= "<a href=\"export_process.php?process_num=$process_num\">Telecharger sur excel</a><br><br>";
$res.= "<table class=\"tableau_solid_black\" id=\"id_tableau_liste_document\">";
$res.= "<thead><tr>";
$res.= "<th>".get_translation('HOSPITAL_PATIENT_ID',"IPP")."</th>";
$res.= "<th>".get_translation('PATIENT_IDENTITY',"Nom Prénom")."</th>";
$res.= "<th>".get_translation('DATE_OF_BIRTH_SHORT','DDN')."</th>";
$res.= "<th>".get_translation('SEX',"Sexe")."</th>";
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

$liste_document=get_dwh_text('','',$tmpresult_num,"$filter_query_user_right",$user_num_session,'patient_text',1,'','');

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
		if (preg_match_all("/.{0,20}$regexp.{0,20}/iS","$text",$out, PREG_SET_ORDER)) {
  			$tab_patient=get_patient ($patient_num);
			$res.= "<tr onmouseout=\"this.style.backgroundColor='#ffffff';\" onmouseover=\"this.style.backgroundColor='#B9C2C8';\" onclick=\"afficher_document_regexp($document_num);\" style=\"cursor: pointer; background-color:#ffffff;\" id=\"id_button_regexp_$document_num\" class=\"tr_document_patient button_regexp\" sousgroupe=\"text\">";
			$res.= "<td class='text'>".$tab_patient['HOSPITAL_PATIENT_ID']."</td>";
			$res.= "<td>".$tab_patient['LASTNAME']." ".$tab_patient['FIRSTNAME']."</td>";
			$res.= "<td>".$tab_patient['BIRTH_DATE']."</td>";
			$res.= "<td>".$tab_patient['SEX']."</td>";
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
update_process ($process_num,'1',"extraction_regexp.xls",$res,$user_num_session,"xls");
sauver_notification ($user_num_session,$user_num_session,'process',"",$process_num);


oci_close ($dbh);
oci_close ($dbh_etl);
?>