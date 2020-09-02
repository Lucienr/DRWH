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
session_start();

date_default_timezone_set('Europe/Paris');
putenv("NLS_LANG=French");
putenv("NLS_LANG=FRENCH");
putenv("NLS_LANGUAGE=FRENCH_FRANCE.WE8MSWIN1252");
error_reporting(E_ALL ^ E_NOTICE);

include_once("parametrage.php");
include_once("connexion_bdd.php");
include_once("ldap.php");
include_once("fonctions_droit.php");
include_once("fonctions_dwh.php");
include_once("verif_droit.php");
include_once("fonctions_stat.php");

$dwh_droit_all_departments=$_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_droit_all_departments'.$datamart_num];

session_write_close();

$date_today=date("dmYHi");

$date=date("YMjHms");
header ("Content-Type: application/excel"); 
header ("Content-Disposition: attachment; filename=export_dwh_$date.xls");

$cohort_num=$_GET['cohort_num'];
$status=$_GET['status'];
$request_access_num=$_GET['request_access_num'];
$process_num=$_GET['process_num'];
$option=$_GET['option'];
$tmpresult_num=$_GET['tmpresult_num'];

$style= "<style>.num {  mso-number-format:General;} .text{  mso-number-format:\"\@\";/*force text*/ } </style> ";

if ( $cohort_num!='' && $status!='') {
        $autorisation_voir_patient_cohorte=verif_autorisation_voir_patient_cohorte($cohort_num,$user_num_session);
        if ( $autorisation_voir_patient_cohorte=='ok') {
	        $tab_hospital_patient_id=get_list_master_patient_id_query("select patient_num from dwh_cohort_result where cohort_num=$cohort_num");
	        $tab_list_user_information=get_list_user_information ("select user_num_add from dwh_cohort_result where cohort_num=$cohort_num",'pn');
        	print "$style";
	        print "<table id=\"id_tableau_patient_cohorte_encours$status\" class=\"tableau_cohorte\">";
	        print "<tr>
	        <th>".get_translation('HOSPITAL_PATIENT_UNIQUE_IDENTIFIER_ACRONYM','IPP')."</th>
	        <th>".get_translation('LASTNAME','Lastname')."</th>
	        <th>".get_translation('FIRSTNAME','Prénom')."</th>
	        <th>".get_translation('DATE_OF_BIRTH_SHORT','DDN')."</th>
	        <th>".get_translation('SEX','Sexe')."</th>
	        <th>".get_translation('DEATH','Décés')."</th>
	        <th>".get_translation('ZIP_CODE','Code postal')."</th>
	        <th>".get_translation('TELEPHONE_SHORT','Tel')."</th>
	        <th>".get_translation('INCLUSION_DATE',"Date d'inclusion")."</th>
	        <th>".get_translation('INVESTIGATOR','Investigateur')."</th>
	        <th>".get_translation('COMMENT','Commentaire')."</th>
	        </tr>";
	        $list_patient_in_cohort=display_list_patient_in_cohort($cohort_num,$status,"","","cohort_excel");
	       
	        print "$list_patient_in_cohort</table>";
	}
	save_log_page($user_num_session,'export_patient_cohort');
}



if ( $request_access_num!='') {
	$autorisation_demande_voir=autorisation_demande_voir ($request_access_num,$user_num_session);
        if ( $autorisation_demande_voir=='ok') {
        	print "$style";
	        print "<table id=\"id_tableau_patient_cohorte_encours$status\" class=\"tableau_cohorte\">";
	        print "<tr>
	         <th>".get_translation('HOSPITAL_PATIENT_UNIQUE_IDENTIFIER_ACRONYM','IPP')."</th>
	        <th>".get_translation('LASTNAME','Lastname')."</th>
	        <th>".get_translation('FIRSTNAME','Prénom')."</th>
	        <th>".get_translation('DATE_OF_BIRTH_SHORT','DDN')."</th>
	        <th>".get_translation('SEX','Sexe')."</th>
	        <th>".get_translation('DEATH','Décés')."</th>
	        <th>".get_translation('ZIP_CODE','Code postal')."</th>
	        </tr>";
		$sel=oci_parse($dbh,"select dwh_request_access_patient.patient_num,lastname from dwh_request_access_patient,dwh_patient where request_access_num=$request_access_num and  dwh_request_access_patient.patient_num=dwh_patient.patient_num order by lastname");
	        oci_execute($sel);
	        while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
	                $patient_num=$r['PATIENT_NUM'];
			$patient=get_patient($patient_num);
			print "<tr>";
			print "<td class='text'>".$patient['HOSPITAL_PATIENT_ID']."</td>";
			print "<td class='text'>".$patient['LASTNAME']."</td>";
			print "<td class='text'>".$patient['FIRSTNAME']."</td>";
			print "<td class='text'>".$patient['BIRTH_DATE']."</td>";
			print "<td class='text'>".$patient['SEX']."</td>";
			print "<td class='text'>".$patient['DEATH_DATE']."</td>";
			print "<td class='text'>".$patient['ZIP_CODE']."</td>";
			print "</tr>";
	        }
	        print "</table>";
	}
	save_log_page($user_num_session,'export_patient_demande_acces');
}
	

if ( $tmpresult_num!='' && $option=='patient') {
     
        //pour les datamart, les droits sont sur tous les services , cf verif_droit.php// 
	$filtre_sql_resultat=filter_query_user_right("dwh_tmp_result_$user_num_session",$user_num_session,$_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_droit_all_departments'.$datamart_num],$liste_service_session,$liste_document_origin_code_session);
      

        print "$style";

        print "<table id=\"id_tableau_patient_cohorte_encours$status\" class=\"tableau_cohorte\">";
        print "<tr>
        <th>IPP</th>
        <th>".get_translation('LASTNAME','Lastname')."</th>
        <th>".get_translation('FIRSTNAME','Prénom')."</th>
        <th>".get_translation('DATE_OF_BIRTH_SHORT','DDN')."</th>
	<th>".get_translation('SEX','Sexe')."</th>
        <th>".get_translation('DEATH','Décés')."</th>
        <th>".get_translation('ZIP_CODE','Code postal')."</th>
        <th>".get_translation('TELEPHONE_SHORT','Tel')."</th>
        </tr>";
        $sel=oci_parse($dbh,"select distinct patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num and user_num=$user_num_session $filtre_sql_resultat");
        oci_execute($sel);
        while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $patient_num=$r['PATIENT_NUM'];
		$patient=get_patient($patient_num);
		print "<tr>";
		print "<td class='text'>".$patient['HOSPITAL_PATIENT_ID']."</td>";
		print "<td class='text'>".$patient['LASTNAME']."</td>";
		print "<td class='text'>".$patient['FIRSTNAME']."</td>";
		print "<td class='text'>".$patient['BIRTH_DATE']."</td>";
		print "<td class='text'>".$patient['SEX']."</td>";
		print "<td class='text'>".$patient['DEATH_DATE']."</td>";
		print "<td class='text'>".$patient['ZIP_CODE']."</td>";
		print "<td class='text'>".$patient['PHONE_NUMBER']."</td>";
		print "</tr>";
        }
        print "</table>";
	save_log_page($user_num_session,'export_patient_result');
}
	

if ( $tmpresult_num!='' && $option=='patient_document') {
     
        //pour les datamart, les droits sont sur tous les services , cf verif_droit.php// 
	$filtre_sql_resultat=filter_query_user_right("dwh_tmp_result_$user_num_session",$user_num_session,$_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_droit_all_departments'.$datamart_num],$liste_service_session,$liste_document_origin_code_session);
      

        print "$style";

        print "<table id=\"id_tableau_patient_cohorte_encours$status\" class=\"tableau_cohorte\">";
        print "<tr>
        <th>IPP</th>
        <th>".get_translation('LASTNAME','Lastname')."</th>
        <th>".get_translation('FIRSTNAME','Prénom')."</th>
        <th>".get_translation('DATE_OF_BIRTH_SHORT','DDN')."</th>
	<th>".get_translation('SEX','Sexe')."</th>
        <th>".get_translation('DEATH','Décés')."</th>
        <th>".get_translation('ZIP_CODE','Code postal')."</th>
        <th>".get_translation('TELEPHONE_SHORT','Tel')."</th>
        <th>".get_translation('DOCUMENT_DATE',"Date du document")."</th>
        <th>".get_translation('DOCUMENT_TITLE','Titre du document')."</th>
        <th>".get_translation('DOCUMENT_AUTHOR','Auteur du document')."</th>
        </tr>";
        $sel=oci_parse($dbh,"select  patient_num,document_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num and user_num=$user_num_session  and object_type='document' $filtre_sql_resultat");
        oci_execute($sel);
        while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $patient_num=$r['PATIENT_NUM'];
                $document_num=$r['DOCUMENT_NUM'];
		$patient=get_patient($patient_num);
		$document=get_document($document_num,'');
		print "<tr>";
		print "<td class='text'>".$patient['HOSPITAL_PATIENT_ID']."</td>";
		print "<td class='text'>".$patient['LASTNAME']."</td>";
		print "<td class='text'>".$patient['FIRSTNAME']."</td>";
		print "<td class='text'>".$patient['BIRTH_DATE']."</td>";
		print "<td class='text'>".$patient['SEX']."</td>";
		print "<td class='text'>".$patient['DEATH_DATE']."</td>";
		print "<td class='text'>".$patient['ZIP_CODE']."</td>";
		print "<td class='text'>".$patient['PHONE_NUMBER']."</td>";
		print "<td class='text'>".$document['document_date']."</td>";
		print "<td class='text'>".$document['title']."</td>";
		print "<td class='text'>".$document['author']."</td>";
		print "</tr>";
        }
        print "</table>";
	save_log_page($user_num_session,'export_patient_result');
}

if ( $tmpresult_num!='' && $option=='encounter_result') {
     
        //pour les datamart, les droits sont sur tous les services , cf verif_droit.php// 
	$filtre_sql_resultat=filter_query_user_right("dwh_tmp_result_$user_num_session",$user_num_session,$_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_droit_all_departments'.$datamart_num],$liste_service_session,$liste_document_origin_code_session);

        print "$style";
        print "<table id=\"id_tableau_patient_cohorte_encours$status\" class=\"tableau_cohorte\">";
        print "<tr>
        <th>IPP</th>
        <th>".get_translation('LASTNAME','Lastname')."</th>
        <th>".get_translation('FIRSTNAME','Prénom')."</th>
        <th>".get_translation('DATE_OF_BIRTH_SHORT','DDN')."</th>
	<th>".get_translation('SEX','Sexe')."</th>
        <th>".get_translation('DEATH','Décés')."</th>
        <th>".get_translation('ZIP_CODE','Code postal')."</th>
        <th>".get_translation('TELEPHONE_SHORT','Tel')."</th>
        <th>".get_translation('ENCOUTER_ID','Séjour')."</th>
        <th>".get_translation('TYPE','Type')."</th>
        <th>".get_translation('ENTRY_DATE','Date entrée')."</th>
        <th>".get_translation('OUT_DATE','Date sortie')."</th>
        </tr>";
        $sel=oci_parse($dbh,"select distinct patient_num,encounter_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num and user_num=$user_num_session and encounter_num is not null $filtre_sql_resultat");
        oci_execute($sel);
        while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $patient_num=$r['PATIENT_NUM'];
                $encounter_num=$r['ENCOUNTER_NUM'];
		$patient=get_patient($patient_num);
		$encounter=get_encounter_info ($encounter_num);
		$entry_date=$encounter['ENTRY_DATE'];
		$out_date=$encounter['OUT_DATE'];
		$entry_mode=$encounter['ENTRY_MODE'];
		$out_mode=$encounter['OUT_MODE'];
		$type='H';
		$list_mvt=array();
		if ($entry_date=='') {
			$list_mvt=get_mvt_info_by_encounter ($encounter_num,'asc');
			$entry_date=$list_mvt[0]['ENTRY_DATE'];
			$out_date=$list_mvt[0]['OUT_DATE'];
			$entry_mode=$list_mvt[0]['MVT_ENTRY_MODE'];
			$out_mode=$list_mvt[0]['MVT_EXIT_MODE'];
			$type=$list_mvt[0]['TYPE_MVT'];
		}
		print "<tr>";
		print "<td class='text'>".$patient['HOSPITAL_PATIENT_ID']."</td>";
		print "<td class='text'>".$patient['LASTNAME']."</td>";
		print "<td class='text'>".$patient['FIRSTNAME']."</td>";
		print "<td class='text'>".$patient['BIRTH_DATE']."</td>";
		print "<td class='text'>".$patient['SEX']."</td>";
		print "<td class='text'>".$patient['DEATH_DATE']."</td>";
		print "<td class='text'>".$patient['ZIP_CODE']."</td>";
		print "<td class='text'>".$patient['PHONE_NUMBER']."</td>";
		print "<td class='text'>$encounter_num</td>";
		print "<td class='text'>$type</td>";
		print "<td class='text'>$entry_date</td>";
		print "<td class='text'>$out_date</td>";
		print "</tr>";
        }
        print "</table>";
	save_log_page($user_num_session,'export_patient_sejour_result');
}
if ( $tmpresult_num!='' && $option=='encounter_all') {
     
        //pour les datamart, les droits sont sur tous les services , cf verif_droit.php// 
	$filtre_sql_resultat=filter_query_user_right("dwh_tmp_result_$user_num_session",$user_num_session,$_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_droit_all_departments'.$datamart_num],$liste_service_session,$liste_document_origin_code_session);


        print "$style";
        print "<table id=\"id_tableau_patient_cohorte_encours$status\" class=\"tableau_cohorte\">";
        print "<tr>
        <th>".get_translation('HOSPITAL_PATIENT_ID','IPP')."</th>
        <th>".get_translation('LASTNAME','Lastname')."</th>
        <th>".get_translation('FIRSTNAME','Prénom')."</th>
        <th>".get_translation('DATE_OF_BIRTH_SHORT','DDN')."</th>
	<th>".get_translation('SEX','Sexe')."</th>
        <th>".get_translation('DEATH','Décés')."</th>
        <th>".get_translation('ZIP_CODE','Code postal')."</th>
        <th>".get_translation('TELEPHONE_SHORT','Tel')."</th>
        <th>".get_translation('ENCOUTER_ID','Séjour')."</th>
        <th>".get_translation('TYPE','Type')."</th>
        <th>".get_translation('UNIT','Unité')."</th>
        <th>".get_translation('DEPARTMENT','Département')."</th>
        <th>".get_translation('ENTRY_DATE','Date entrée')."</th>
        <th>".get_translation('OUT_DATE','Date sortie')."</th>
        </tr>";
        $sel=oci_parse($dbh,"select distinct dwh_tmp_result_$user_num_session.patient_num
				from dwh_tmp_result_$user_num_session
				where tmpresult_num=$tmpresult_num and user_num=$user_num_session  $filtre_sql_resultat");
        oci_execute($sel);
        while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $patient_num=$r['PATIENT_NUM'];
		$patient=get_patient($patient_num);
                $list_encounter=get_encounter_info_by_patient($patient_num,"asc");
                foreach ($list_encounter as $encounter) {
	                $encounter_num=$encounter['ENCOUNTER_NUM'];
	                $entry_date=$encounter['ENTRY_DATE_YMDH24'];
	                $out_date=$encounter['OUT_DATE_YMDH24'];
			print "<tr>";
			print "<td class='text'>".$patient['HOSPITAL_PATIENT_ID']."</td>";
			print "<td class='text'>".$patient['LASTNAME']."</td>";
			print "<td class='text'>".$patient['FIRSTNAME']."</td>";
			print "<td class='text'>".$patient['BIRTH_DATE']."</td>";
			print "<td class='text'>".$patient['SEX']."</td>";
			print "<td class='text'>".$patient['DEATH_DATE']."</td>";
			print "<td class='text'>".$patient['ZIP_CODE']."</td>";
			print "<td class='text'>".$patient['PHONE_NUMBER']."</td>";
			print "<td class='text'>$encounter_num</td>";
			print "<td class='text'>H</td>";
			print "<td class='text'></td>";
			print "<td class='text'></td>";
			print "<td class='text'>$entry_date</td>";
			print "<td class='text'>$out_date</td>";
			print "</tr>";
		}
                
                $list_mvt=get_mvt_info_by_patient($patient_num,"asc");
                foreach ($list_mvt as $mvt) {
	                $encounter_num=$mvt['ENCOUNTER_NUM'];
	                $entry_date=$mvt['ENTRY_DATE'];
	                $out_date=$mvt['OUT_DATE'];
	                $type_mvt=$mvt['TYPE_MVT'];
	                $unit_num=$mvt['UNIT_NUM'];
	                $department_num=$mvt['DEPARTMENT_NUM'];
	                $unit_str=get_unit_str ($unit_num,$option='cs');
	                $department_str=get_department_str ($department_num);
	                if ($type_mvt!='H') {
				print "<tr>";
				print "<td class='text'>".$patient['HOSPITAL_PATIENT_ID']."</td>";
				print "<td class='text'>".$patient['LASTNAME']."</td>";
				print "<td class='text'>".$patient['FIRSTNAME']."</td>";
				print "<td class='text'>".$patient['BIRTH_DATE']."</td>";
				print "<td class='text'>".$patient['SEX']."</td>";
				print "<td class='text'>".$patient['DEATH_DATE']."</td>";
				print "<td class='text'>".$patient['ZIP_CODE']."</td>";
				print "<td class='text'>".$patient['PHONE_NUMBER']."</td>";
				print "<td class='text'>$encounter_num</td>";
				print "<td class='text'>$type_mvt</td>";
				print "<td class='text'>$unit_str</td>";
				print "<td class='text'>$department_str</td>";
				print "<td class='text'>$entry_date</td>";
				print "<td class='text'>$out_date</td>";
				print "</tr>";
			}
		}
        }
        print "</table>";
	save_log_page($user_num_session,'export_patient_allsejour_result');
}
	
	
if ( $tmpresult_num!='' && $option=='encounter_all_ancien') {
     
        //pour les datamart, les droits sont sur tous les services , cf verif_droit.php// 
	$filtre_sql_resultat=filter_query_user_right("dwh_tmp_result_$user_num_session",$user_num_session,$_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_droit_all_departments'.$datamart_num],$liste_service_session,$liste_document_origin_code_session);


        print "$style";
        print "<table id=\"id_tableau_patient_cohorte_encours$status\" class=\"tableau_cohorte\">";
        print "<tr>
        <th>IPP</th>
        <th>".get_translation('HOSPITAL_PATIENT_ID','Patient ID')."</th>
        <th>".get_translation('LASTNAME','Lastname')."</th>
        <th>".get_translation('FIRSTNAME','Prénom')."</th>
        <th>".get_translation('DATE_OF_BIRTH_SHORT','DDN')."</th>
	<th>".get_translation('SEX','Sexe')."</th>
        <th>".get_translation('DEATH','Décés')."</th>
        <th>".get_translation('ZIP_CODE','Code postal')."</th>
        <th>".get_translation('TELEPHONE_SHORT','Tel')."</th>
        <th>".get_translation('ENCOUTER_ID','Séjour')."</th>
        <th>".get_translation('ENTRY_DATE','Date entrée')."</th>
        <th>".get_translation('OUT_DATE','Date sortie')."</th>
        <th>".get_translation('TYPE','Type')."</th>
        </tr>";
        $sel=oci_parse($dbh,"	select distinct 
				        dwh_tmp_result_$user_num_session.patient_num,
				        dwh_patient_stay.encounter_num,
		        		to_char(entry_date,'DD/MM/YYYY HH24:MI ') entry_date,
					to_char(out_date,'DD/MM/YYYY HH24:MI ') out_date  ,
					entry_mode,
					out_mode
				from dwh_tmp_result_$user_num_session, dwh_patient_stay  
				where tmpresult_num=$tmpresult_num and user_num=$user_num_session and dwh_tmp_result_$user_num_session.patient_num=dwh_patient_stay.patient_num  $filtre_sql_resultat");
        oci_execute($sel);
        while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $patient_num=$r['PATIENT_NUM'];
                $encounter_num=$r['ENCOUNTER_NUM'];
                $entry_date=$r['ENTRY_DATE'];
                $out_date=$r['OUT_DATE'];
		$patient=get_patient($patient_num);
		print "<tr>";
		print "<td class='text'>".$patient['HOSPITAL_PATIENT_ID']."</td>";
		print "<td class='text'>".$patient['LASTNAME']."</td>";
		print "<td class='text'>".$patient['FIRSTNAME']."</td>";
		print "<td class='text'>".$patient['BIRTH_DATE']."</td>";
		print "<td class='text'>".$patient['SEX']."</td>";
		print "<td class='text'>".$patient['DEATH_DATE']."</td>";
		print "<td class='text'>".$patient['ZIP_CODE']."</td>";
		print "<td class='text'>".$patient['PHONE_NUMBER']."</td>";
		print "<td class=\"text\">$encounter_num</td><td class=\"text\">$entry_date</td><td class=\"text\">$out_date</td>";
		print "</tr>";
        }
        print "</table>";
	save_log_page($user_num_session,'export_patient_allsejour_result');
}
	
	
if ( $tmpresult_num!='' && $option=='stat_movment') {
        print "$style";
	nb_consult_per_unit_per_year_tableau ($tmpresult_num);
	nb_hospit_per_unit_per_year_tableau ($tmpresult_num);
	nb_patient_per_unit_per_year_tableau ($tmpresult_num);
	save_log_page($user_num_session,'export_stat_result');
}

if ( $process_num!='' &&  $option=='similarity_cohort') {
        print "$style";
	$tableau_process=get_process($process_num,'get_result');
	$result=$tableau_process['RESULT'];
	$tab_patient=explode(";",$result);
	print "<table border=0 id=\"id_tableau_similarite_cohorte\" class=\"tablefin\" width=\"800\">";
	print "<thead><th>".get_translation('SIMILAR_PATIENTS','Patients similaires')."</th><th>".get_translation('IN_TOP_20_OF_N_INDEX_PATIENTS','Dans le top20 de N patients index')."</th><th>".get_translation('MEAN_SIMILARITY','Similarité moyenne')."</th></thead><tbody>";
	foreach ($tab_patient as $p) {
		list($patient_num,$nb,$similarite)=explode(",",$p);
            	//print afficher_patient($patient_num,'cohorte_excel','','');
		$patient=get_patient($patient_num);
		$patient_name=$patient['LASTNAME']." ".$patient['FIRSTNAME']." ".$patient['BIRTH_DATE'];
		print "<tr><td>$patient_name </td><td>$nb</td><td>$similarite</td></tr>\n";
	}
	
	print "</tbody></table>";
        print "</div>";
        print "</table>";
}
	

if ($_GET['ecrf_num']!='' &&  $option=='ecrf_patient_answer') {
	include_once("fonctions_ecrf.php");
	$ecrf_num=$_GET['ecrf_num'];
	$autorisation_ecrf_voir=autorisation_ecrf_voir ($ecrf_num,$user_num_session);
	$option_ecrf=$_GET['option_ecrf'];
	if ($autorisation_ecrf_voir=='ok') {
		display_table_ecrf_patient_answer ($ecrf_num,$user_num_session,$option_ecrf);
	}
	save_log_page($user_num_session,"export_list_patient_ecrf");
}
	
?>