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
include_once("fonctions_concepts.php");
session_write_close();

$date_today=date("dmYHi");

$date=date("YMjHms");
header ("Content-Type: application/excel"); 
header ("Content-Disposition: attachment; filename=export_dwh_concepts_$date.xls");

$tmpresult_num=$_GET['tmpresult_num'];
$type_export=$_GET['type_export'];

print "<style>
.num {
  mso-number-format:General;
}
.text{
  mso-number-format:\"\@\";/*force text*/
}
</style>";

if ( $tmpresult_num!='' && $type_export=='detail_patient_concept') {
	$phenotype_genotype=$_GET['phenotype_genotype'];
	$donnees_reelles_ou_pref=$_GET['donnees_reelles_ou_pref'];
	$type=$_GET['type'];
	$distance=$_GET['distance'];
        print "<table class=\"tableau_cohorte\" border=\"1\">";
        print "<tr>
        <th>".get_translation('HOSPITAL_PATIENT_UNIQUE_IDENTIFIER_ACRONYM','IPP')."</th>
        <th>".get_translation('LASTNAME','Lastname')."</th>
        <th>".get_translation('FIRSTNAME','Prénom')."</th>
        <th>".get_translation('DATE_OF_BIRTH_SHORT','DDN')."</th>
        <th>".get_translation('DEATH','Décés')."</th>
        <th>".get_translation('SEX','Sexe')."</th>
        <th>".get_translation('AGE_PATIENT','Age')."</th>
        <th>".get_translation('DOCUMENT_DATE','Document date')."</th>
        <th>".get_translation('CONCEPT_CODE','Code concept')."</th>
        <th>".get_translation('CONCEPT_PREF','Prefered concept')."</th>
        <th>".get_translation('CONCEPT_FOUND','Concept found')."</th>
        <th>".get_translation('COUNT_CONCEPT_FOUND','Nb found in document')."</th>
        </tr>";
	if ($type=='document') {
	        $sel=oci_parse($dbh,"select  dwh_patient.patient_num  ,
		        to_char( DOCUMENT_DATE,'DD/MM/YYYY')  as DOCUMENT_DATE,
		        AGE_PATIENT,   
		        dwh_enrsem.CONCEPT_CODE,
		        CONCEPT_STR_FOUND as CONCEPT_FOUND, 
		        COUNT_CONCEPT_STR_FOUND as COUNT_CONCEPT_FOUND, 
		        concept_str as concept_pref
	 	from dwh_patient,dwh_enrsem,dwh_thesaurus_enrsem 
	 	where dwh_enrsem.document_num in (select document_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num and object_type='document')
			and certainty=1 and context='patient_text'
			and dwh_patient.patient_num=dwh_enrsem.patient_num
			and dwh_enrsem.concept_code=dwh_thesaurus_enrsem.concept_code
			and  pref='Y'");
	}
	if ($type=='patient') {
	        $sel=oci_parse($dbh,"select  dwh_patient.patient_num  ,
		        to_char( DOCUMENT_DATE,'DD/MM/YYYY')  as DOCUMENT_DATE,
		        AGE_PATIENT,   
		        dwh_enrsem.CONCEPT_CODE,
		        CONCEPT_STR_FOUND as CONCEPT_FOUND, 
		        COUNT_CONCEPT_STR_FOUND as COUNT_CONCEPT_FOUND, 
		        concept_str as concept_pref
	 	from dwh_patient,dwh_enrsem,dwh_thesaurus_enrsem 
	 	where dwh_enrsem.patient_num in (select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num )
			and certainty=1 and context='patient_text'
			and dwh_patient.patient_num=dwh_enrsem.patient_num
			and dwh_enrsem.concept_code=dwh_thesaurus_enrsem.concept_code
			and  pref='Y'");
	}
        oci_execute($sel);
        while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $patient_num=$r['PATIENT_NUM'];
                $age_patient=convert_decimal_for_export($r['AGE_PATIENT']);
                $document_date=$r['DOCUMENT_DATE'];
                $concept_code=$r['CONCEPT_CODE'];
                $concept_found=$r['CONCEPT_FOUND'];
                $count_concept_found=$r['COUNT_CONCEPT_FOUND'];
                $concept_pref=$r['CONCEPT_PREF'];
                
		$tab_patient=get_patient ($patient_num);
		$hospital_patient_id=$tab_patient['HOSPITAL_PATIENT_ID'];         
		$lastname=$tab_patient['LASTNAME'];               
		$firstname=ucfirst ($tab_patient['FIRSTNAME']);               
		$birth_date=$tab_patient['BIRTH_DATE'];             
		$sex=$tab_patient['SEX'];
		$death_date=$tab_patient['DEATH_DATE'];
		
		print "<tr>
				<td>$hospital_patient_id</td>
				<td>$lastname</td>
				<td>$firstname</td>
				<td>$birth_date</td>
				<td>$death_date</td>
				<td>$sex</td>
				
				<td>$age_patient</td>
				<td>$document_date</td>
				<td>$concept_code</td>
				<td>$concept_pref</td>
				<td>$concept_found</td>
				<td>$count_concept_found</td>
			</tr>
		";
        }
        print "</table>";
	save_log_page($user_num_session,'export_engine_detail_patient_concept');
}


if ( $tmpresult_num!='' && $type_export=='agregated_concept') {
	$tmpresult_num=$_GET['tmpresult_num'];
	$phenotype_genotype=$_GET['phenotype_genotype'];
	$donnees_reelles_ou_pref=$_GET['donnees_reelles_ou_pref'];
	$type=$_GET['type'];
	$distance=$_GET['distance'];
	$age_concept_min=$_GET['age_concept_min'];
	$age_concept_max=$_GET['age_concept_max'];
	$json=repartition_concepts_general_json ($tmpresult_num,$phenotype_genotype,2,0,0,$donnees_reelles_ou_pref,$type,$distance,$age_concept_min,$age_concept_max);
	$tab=explode(";separateur_general;",$json);
	$json_table=$tab[0];
	$json_table=preg_replace("/[éèê]/","e",$json_table);
	$json_table=preg_replace("/[âà]/","a",$json_table);
	$json_table=preg_replace("/[ôö]/","o",$json_table);
	$json_table=preg_replace("/[ç]/","c",$json_table);
	$json_table=preg_replace("/[ïî]/","i",$json_table);
	$json_table=preg_replace("/[ùûü]/","u",$json_table);
	$tableau=json_decode("[$json_table]");
	
	
	save_log_page($user_num_session,'export_engine_agregated_concept');
	
        print "<table  class=\"tableau_cohorte\" border=\"1\">";
        print "<tr>
        <th>".get_translation('CONCEPT_CODE','CODE')."</th>
        <th>".get_translation('CONCEPTS','Concepts')."</th>
        <th>".get_translation('NUMBER_OF_PATIENTS','# Patients')."</th>
        <th>".get_translation('FREQ_PATIENTS','FreqRes')."</th>
        <th>".get_translation('TF-IDF','TF-IDF')."</th>
        <th>".get_translation('PSS','PSS')."</th>
        <th>".get_translation('CASE-WEIGHTED PSS','Case-Weighted PSS')."</th>
        <th>".get_translation('MEDIAN AGE','Median age')."</th>
        </tr>";
	foreach ($tableau as $t) {
		$concept_code=$t[0];
		$concepts=$t[1];
		$number_of_patients=$t[2];
		$freq_patients=convert_decimal_for_export($t[3]);
		$tf_idf=convert_decimal_for_export($t[4]);
		$pss=convert_decimal_for_export($t[5]);
		$case_weighted_pss=convert_decimal_for_export($t[6]);
		$median_age=convert_decimal_for_export($t[7]);
		
		print "<tr>";
		print "<td>$concept_code</td>";
		print "<td>$concepts</td>";
		print "<td>$number_of_patients</td>";
		print "<td>$freq_patients</td>";
		print "<td>$tf_idf</td>";
		print "<td>$pss</td>";
		print "<td>$case_weighted_pss</td>";
		print "<td>$median_age</td>";
		print "</tr>";
	}
	print "</table>";
}
