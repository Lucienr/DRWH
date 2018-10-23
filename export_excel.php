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
include_once("fonctions_dwh.php");
include_once("fonctions_droit.php");
include_once("verif_droit.php");
include_once("fonctions_stat.php");

$dwh_droit_all_departments=$_SESSION['dwh_droit_all_departments'.$datamart_num];

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

print "<style>
.num {
  mso-number-format:General;
}
.text{
  mso-number-format:\"\@\";/*force text*/
}
</style>";

if ( $cohort_num!='' && $status!='') {
        $autorisation_voir_patient_cohorte=verif_autorisation_voir_patient_cohorte($cohort_num,$user_num_session);
        if ( $autorisation_voir_patient_cohorte=='ok') {
	        print "<table id=\"id_tableau_patient_cohorte_encours$status\" class=\"tableau_cohorte\">";
	        print "<tr>
	        <th>".get_translation('HOSPITAL_PATIENT_UNIQUE_IDENTIFIER_ACRONYM','IPP')."</th>
	        <th>".get_translation('INITIALS','Initials')."</th>
	        <th>".get_translation('LASTNAME','Lastname')."</th>
	        <th>".get_translation('FIRSTNAME','Prénom')."</th>
	        <th>".get_translation('DATE_OF_BIRTH_SHORT','DDN')."</th>
	        <th>".get_translation('DEATH','Décés')."</th>
	        <th>".get_translation('ZIP_CODE','Code postal')."</th>
	        <th>".get_translation('TELEPHONE_SHORT','Tel')."</th>
	        <th>".get_translation('INCLUSION_DATE',"Date d'inclusion")."</th>
	        <th>".get_translation('INVESTIGATOR','Investigateur')."</th>
	        <th>".get_translation('COMMENT','Commentaire')."</th>
	        </tr>";
	        $sel=oci_parse($dbh,"select distinct patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=$status");
	        oci_execute($sel);
	        while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$patient_num=$r['PATIENT_NUM'];
			$comment=lister_commentaire_patient_cohorte ($cohort_num,$patient_num,'excel');
			$patient=afficher_patient($patient_num,'cohorte_excel','',$cohort_num);
			print "<tr>$patient<td>$comment</td></tr>";
	        }
	        print "</table>";
	}
}



if ( $request_access_num!='') {
	$autorisation_demande_voir=autorisation_demande_voir ($request_access_num,$user_num_session);
        if ( $autorisation_demande_voir=='ok') {
	        print "<table id=\"id_tableau_patient_cohorte_encours$status\" class=\"tableau_cohorte\">";
	        print "<tr>
	         <th>".get_translation('HOSPITAL_PATIENT_UNIQUE_IDENTIFIER_ACRONYM','IPP')."</th>
	        <th>".get_translation('INITIALS','Initials')."</th>
	        <th>".get_translation('LASTNAME','Lastname')."</th>
	        <th>".get_translation('FIRSTNAME','Prénom')."</th>
	        <th>".get_translation('DATE_OF_BIRTH_SHORT','DDN')."</th>
	        <th>".get_translation('DEATH','Décés')."</th>
	        <th>".get_translation('ZIP_CODE','Code postal')."</th>
	        </tr>";
		$sel=oci_parse($dbh,"select dwh_request_access_patient.patient_num,lastname from dwh_request_access_patient,dwh_patient where request_access_num=$request_access_num and  dwh_request_access_patient.patient_num=dwh_patient.patient_num order by lastname");
	        oci_execute($sel);
	        while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
	                $patient_num=$r['PATIENT_NUM'];
	               print afficher_patient($patient_num,'demande_acces_excel','',$cohort_num);
	        }
	        print "</table>";
	}
}
	

if ( $tmpresult_num!='' && $option=='patient') {
     
        //pour les datamart, les droits sont sur tous les services , cf verif_droit.php// 
        if ($dwh_droit_all_departments=='') {
                if ($liste_uf_session!='') {
                        $filtre_sql.=" and exists ( select patient_num from dwh_patient_department where department_num in ($liste_service_session) and  dwh_tmp_result.patient_num=dwh_patient_department.patient_num ) ";
                } else {
                        $filtre_sql.=" and 1=2";
                }
        }
	$filtre_sql_document_origin_code='';
        if ($liste_document_origin_code_session!='') {
        	if (!preg_match("/'tout'/i","$liste_document_origin_code_session")) {
                        $filtre_sql_document_origin_code.=" and document_origin_code in ($liste_document_origin_code_session) ";
        	}
        } else {
                $filtre_sql_document_origin_code.=" and 1=2"; 
        }



        print "<table id=\"id_tableau_patient_cohorte_encours$status\" class=\"tableau_cohorte\">";
        print "<tr>
        <th>PATIENT_NUM</th>
        <th>".get_translation('INITIALS','Initials')."</th>
        <th>".get_translation('LASTNAME','Lastname')."</th>
        <th>".get_translation('FIRSTNAME','Prénom')."</th>
        <th>".get_translation('DATE_OF_BIRTH_SHORT','DDN')."</th>
        <th>".get_translation('DEATH','Décés')."</th>
        <th>".get_translation('ZIP_CODE','Code postal')."</th>
        <th>".get_translation('TELEPHONE_SHORT','Tel')."</th>
        <th>".get_translation('INCLUSION_DATE',"Date d'inclusion")."</th>
        <th>".get_translation('INVESTIGATOR','Investigateur')."</th>
        </tr>";
        $sel=oci_parse($dbh,"select distinct patient_num from dwh_tmp_result where tmpresult_num=$tmpresult_num and user_num=$user_num_session $filtre_sql  $filtre_sql_document_origin_code");
        oci_execute($sel);
        while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $patient_num=$r['PATIENT_NUM'];
		print "<tr>";
		print afficher_patient($patient_num,'cohorte_excel','','');
		print "</tr>";
        }
        print "</table>";
}

if ( $tmpresult_num!='' && $option=='encounter_result') {
     
        //pour les datamart, les droits sont sur tous les services , cf verif_droit.php// 
        if ($dwh_droit_all_departments=='') {
                if ($liste_uf_session!='') {
                        $filtre_sql.=" and exists ( select patient_num from dwh_patient_department where department_num in ($liste_service_session) and  dwh_tmp_result.patient_num=dwh_patient_department.patient_num ) ";
                } else {
                        $filtre_sql.=" and 1=2";
                }
        }
	$filtre_sql_document_origin_code='';
        if ($liste_document_origin_code_session!='') {
        	if (!preg_match("/'tout'/i","$liste_document_origin_code_session")) {
                        $filtre_sql_document_origin_code.=" and document_origin_code in ($liste_document_origin_code_session) ";
        	}
        } else {
                $filtre_sql_document_origin_code.=" and 1=2"; 
        }

        print "<table id=\"id_tableau_patient_cohorte_encours$status\" class=\"tableau_cohorte\">";
        print "<tr>
        <th>PATIENT_NUM</th>
        <th>".get_translation('INITIALS','Initials')."</th>
        <th>".get_translation('LASTNAME','Lastname')."</th>
        <th>".get_translation('FIRSTNAME','Prénom')."</th>
        <th>".get_translation('DATE_OF_BIRTH_SHORT','DDN')."</th>
        <th>".get_translation('DEATH','Décés')."</th>
        <th>".get_translation('ZIP_CODE','Code postal')."</th>
        <th>".get_translation('TELEPHONE_SHORT','Tel')."</th>
        <th>".get_translation('ENCOUTER_ID','Séjour')."</th>
        <th>".get_translation('ENTRY_DATE','Date entrée')."</th>
        <th>".get_translation('OUT_DATE','Date sortie')."</th>
        </tr>";
        $sel=oci_parse($dbh,"select distinct patient_num,encounter_num from dwh_tmp_result where tmpresult_num=$tmpresult_num and user_num=$user_num_session $filtre_sql  $filtre_sql_document_origin_code");
        oci_execute($sel);
        while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $patient_num=$r['PATIENT_NUM'];
                $encounter_num=$r['ENCOUNTER_NUM'];
		print "<tr>";
		print afficher_patient($patient_num,'result_excel','','');
		list($entry_date,$out_date,$entry_mode,$out_mode)=get_encounter_info ($encounter_num);
		print "<td class=\"text\">$encounter_num</td><td class=\"text\">$entry_date</td><td class=\"text\">$out_date</td>";
		print "</tr>";
        }
        print "</table>";
}
	
if ( $tmpresult_num!='' && $option=='encounter_all') {
     
        //pour les datamart, les droits sont sur tous les services , cf verif_droit.php// 
        if ($dwh_droit_all_departments=='') {
                if ($liste_uf_session!='') {
                        $filtre_sql.=" and exists ( select dwh_patient_department.patient_num from dwh_patient_department where department_num in ($liste_service_session) and  dwh_tmp_result.patient_num=dwh_patient_department.patient_num ) ";
                } else {
                        $filtre_sql.=" and 1=2";
                }
        }
	$filtre_sql_document_origin_code='';
        if ($liste_document_origin_code_session!='') {
        	if (!preg_match("/'tout'/i","$liste_document_origin_code_session")) {
                        $filtre_sql_document_origin_code.=" and document_origin_code in ($liste_document_origin_code_session) ";
        	}
        } else {
                $filtre_sql_document_origin_code.=" and 1=2"; 
        }

        print "<table id=\"id_tableau_patient_cohorte_encours$status\" class=\"tableau_cohorte\">";
        print "<tr>
        <th>PATIENT_NUM</th>
        <th>".get_translation('INITIALS','Initials')."</th>
        <th>".get_translation('LASTNAME','Lastname')."</th>
        <th>".get_translation('FIRSTNAME','Prénom')."</th>
        <th>".get_translation('DATE_OF_BIRTH_SHORT','DDN')."</th>
        <th>".get_translation('DEATH','Décés')."</th>
        <th>".get_translation('ZIP_CODE','Code postal')."</th>
        <th>".get_translation('TELEPHONE_SHORT','Tel')."</th>
        <th>".get_translation('ENCOUTER_ID','Séjour')."</th>
        <th>".get_translation('ENTRY_DATE','Date entrée')."</th>
        <th>".get_translation('OUT_DATE','Date sortie')."</th>
        </tr>";
        $sel=oci_parse($dbh,"	select distinct 
				        dwh_tmp_result.patient_num,
				        dwh_patient_stay.encounter_num,
		        		to_char(entry_date,'DD/MM/YYYY HH24:MI ') entry_date,
					to_char(out_date,'DD/MM/YYYY HH24:MI ') out_date  ,
					entry_mode,
					out_mode
				from dwh_tmp_result, dwh_patient_stay  
				where tmpresult_num=$tmpresult_num and user_num=$user_num_session and dwh_tmp_result.patient_num=dwh_patient_stay.patient_num(+) $filtre_sql  $filtre_sql_document_origin_code");
        oci_execute($sel);
        while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $patient_num=$r['PATIENT_NUM'];
                $encounter_num=$r['ENCOUNTER_NUM'];
                $entry_date=$r['ENTRY_DATE'];
                $out_date=$r['OUT_DATE'];
		print "<tr>";
		print afficher_patient($patient_num,'result_excel','','');
		print "<td class=\"text\">$encounter_num</td><td class=\"text\">$entry_date</td><td class=\"text\">$out_date</td>";
		print "</tr>";
        }
        print "</table>";
}
	
	
if ( $tmpresult_num!='' && $option=='stat_movment') {
	nb_consult_per_unit_per_year_tableau ($tmpresult_num);
	nb_hospit_per_unit_per_year_tableau ($tmpresult_num);
}

if ( $process_num!='' ) {
	    
	$tableau_process=get_process($process_num);
	$result=$tableau_process['RESULT'];
	
	$tab_patient=explode(";",$result);

	print "<table border=0 id=\"id_tableau_similarite_cohorte\" class=\"tablefin\" width=\"800\">";
	print "<thead><th>".get_translation('SIMILAR_PATIENTS','Patients similaires')."</th><th>".get_translation('IN_TOP_20_OF_N_INDEX_PATIENTS','Dans le top20 de N patients index')."</th><th>".get_translation('MEAN_SIMILARITY','Similarité moyenne')."</th></thead><tbody>";
	foreach ($tab_patient as $p) {
		list($patient_num,$nb,$similarite)=explode(",",$p);
            	//print afficher_patient($patient_num,'cohorte_excel','','');
		$user_name=afficher_patient ($patient_num,'lastname firstname ddn','','','similarite');
		print "<tr><td>$user_name </td><td>$nb</td><td>$similarite</td></tr>\n";
	}
	
	print "</tbody></table>";
        print "</div>";
        print "</table>";
}
	
?>