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
?>
<?
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
session_write_close();

$date_today=date("dmYHi");

$date=date("YMjHms");
 
$user_num_session=$_SESSION['dwh_user_num'];


$file_name=$_POST['file_name'];
$file_name=preg_replace("/\s/","_",$file_name);
if($file_name==''){
	$file_name="export_dwh_concepts_$date";
}

$export_type=$_POST['export_type'];
if ($export_type=='xls'){
	header ("Content-Type: application/excel");
	header ("Content-Disposition: attachment; filename=$file_name.xls");
	print "<style>
		.num {
		  mso-number-format:General;
		}
		.text{	
		  mso-number-format:\"\@\";/*force text*/
		}
		</style>";
		
    	print"<table id=\"id_table_export_concept\" style=\"width:100%\">";				
		print"<tr>
			<th>DOCUMENT_NUM</th>
			<th>THESAURUS_CODE</th>
			<th>CONCEPT_CODE</th>
			<th>CONCEPT_STR</th>
			<th>CONCEPT_PATH</th>
			<th>INFO_COMPLEMENT</th>
			<th>MEASURING_UNIT</th>
			<th>VAL_NUMERIC</th>
			<th>VAL_TEXT</th>
			<th>DOCUMENT_DATE</th>
			<th>LOWER_BOUND</th>
			<th>UPPER_BOUND</th>
			<th>ENCOUNTER_NUM</th>
			<th>PATIENT_NUM</th>
			<th>PATIENT_SEX</th>
			<th>PATIENT_AGE</th>
		</tr>";

}else{
	header("Content-Type: text/plain");
	header ("Content-Disposition: attachment; filename=$file_name.txt");
	print "DOCUMENT_NUM\tTHESAURUS_CODE\tCONCEPT_CODE\tCONCEPT_STR\tCONCEPT_PATH\tINFO_COMPLEMENT\tMEASURING_UNIT\tVAL_NUMERIC\tVAL_TEXT\tDOCUMENT_DATE\tLOWER_BOUND\tUPPER_BOUND\tENCOUNTER_NUM\tHOSPITAL_PATIENT_ID\tPATIENT_SEX\tPATIENT_AGE\n";
}


$cohort_num=$_GET['cohort_num'];
$status=$_GET['status'];
$request_access_num=$_GET['request_access_num'];
$process_num=$_GET['process_num'];
$tmpresult_num=$_POST['tmpresult_num'];

$concepts=$_POST['concepts'];			
if (!empty($concepts)){		

	foreach ($concepts as $concept){
		//select info concept
  		$query="select concept_str,info_complement,measuring_unit,thesaurus_data_num,concept_path from dwh_thesaurus_data where concept_code='$concept'";
		$sel=oci_parse($dbh,$query);
		oci_execute($sel);
		$r=oci_fetch_array($sel, OCI_RETURN_NULLS+OCI_ASSOC);
		$CONCEPT_STR=$r['CONCEPT_STR'];
		$INFO_COMPLEMENT=$r['INFO_COMPLEMENT'];
		$MEASURING_UNIT=$r['MEASURING_UNIT'];
		if ($r['CONCEPT_PATH']!='') {
			$CONCEPT_PATH=$r['CONCEPT_PATH']->load();
		}
		$THESAURUS_DATA_NUM=$r['THESAURUS_DATA_NUM'];
				
		//select patient
    		$query_patients="select distinct patient_num from dwh_tmp_result where tmpresult_num=$tmpresult_num";
    		$sel_patient = oci_parse($dbh,$query_patients); 
    		oci_execute($sel_patient);	
    		while ($res_patient=oci_fetch_array($sel_patient,OCI_RETURN_NULLS+OCI_ASSOC)) {	
    			$patient_num=$res_patient['PATIENT_NUM'];
    			$verif=autorisation_voir_patient($patient_num,$user_num_session);
    			if($verif=='ok'){
    			
	    			$tab_patient=get_patient ($patient_num);
	    			$HOSPITAL_PATIENT_ID=$tab_patient['HOSPITAL_PATIENT_ID'];   
	    			$PATIENT_SEX=$tab_patient['SEX'];  
	    			
    			
      				$query_data="select DOCUMENT_NUM,THESAURUS_CODE,VAL_NUMERIC,VAL_TEXT,DOCUMENT_DATE,LOWER_BOUND,UPPER_BOUND,ENCOUNTER_NUM,AGE_PATIENT  
      				from DWH_DATA where PATIENT_NUM=$patient_num and THESAURUS_DATA_NUM=$THESAURUS_DATA_NUM";			      				
      				$sel = oci_parse($dbh,$query_data); 
      				oci_execute($sel);
      				while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {			
      	      				$DOCUMENT_NUM=$r['DOCUMENT_NUM'];
      		      			$THESAURUS_CODE=$r['THESAURUS_CODE'];     		      			
      		      			$VAL_NUMERIC=$r['VAL_NUMERIC'];
      		      			$VAL_TEXT=$r['VAL_TEXT'];
      		      			$DOCUMENT_DATE=$r['DOCUMENT_DATE'];
      		    			$LOWER_BOUND=$r['LOWER_BOUND'];
      		    			$UPPER_BOUND=$r['UPPER_BOUND'];
      		    			$ENCOUNTER_NUM=$r['ENCOUNTER_NUM'];
      		      			$PATIENT_AGE=$r['AGE_PATIENT'];

					if ($export_type=='xls'){
							print"<tr>
						   	<td>$DOCUMENT_NUM</td>
							<td>$THESAURUS_CODE</td>
							<td>$concept</td>
							<td>$CONCEPT_STR</td>
							<td>$CONCEPT_PATH</td>
							<td>$INFO_COMPLEMENT</td>
							<td>$MEASURING_UNIT</td>
							<td>$VAL_NUMERIC</td>
							<td>$VAL_TEXT</td>
							<td>$DOCUMENT_DATE</td>
							<td>$LOWER_BOUND</td>
							<td>$UPPER_BOUND</td>
							<td>$ENCOUNTER_NUM</td>
							<td>$HOSPITAL_PATIENT_ID</td>
							<th>$PATIENT_SEX</th>
							<th>$PATIENT_AGE</th>
						  	</tr>";
					}else{
						print "$DOCUMENT_NUM\t$THESAURUS_CODE\t$concept\t$CONCEPT_STR\t$CONCEPT_PATH\t$INFO_COMPLEMENT\t$MEASURING_UNIT\t$VAL_NUMERIC\t$VAL_TEXT\t$DOCUMENT_DATE\t$LOWER_BOUND\t$UPPER_BOUND\t$ENCOUNTER_NUM\t$HOSPITAL_PATIENT_ID\t$PATIENT_SEX\t$PATIENT_AGE\n";
					}
				} //WHILE	
			}// if verif		
		}//WHILE			
	}//FOREACH
}//IF EMPTY	

	
$thesaurus=$_POST['thesaurus'];
if (!empty($thesaurus)){

	foreach ($thesaurus as $selected_thesaurus){

	$query_patients="select distinct patient_num from dwh_tmp_result where tmpresult_num=$tmpresult_num";
	$sel_patient = oci_parse($dbh,$query_patients); 
	oci_execute($sel_patient);	
	while ($res_patient=oci_fetch_array($sel_patient,OCI_RETURN_NULLS+OCI_ASSOC)) {	
		$patient_num=$res_patient['PATIENT_NUM'];
		$verif=autorisation_voir_patient($patient_num,$user_num_session);
		if($verif=='ok'){
				$tab_patient=get_patient ($patient_num);
	    			$HOSPITAL_PATIENT_ID=$tab_patient['HOSPITAL_PATIENT_ID'];   
	    			$PATIENT_SEX=$tab_patient['SEX']; 

	      			$query_data="select DOCUMENT_NUM,THESAURUS_CODE,THESAURUS_DATA_NUM,VAL_NUMERIC,VAL_TEXT,DOCUMENT_DATE,LOWER_BOUND,UPPER_BOUND,ENCOUNTER_NUM,AGE_PATIENT
				from DWH_DATA where patient_num=$patient_num and thesaurus_code='$selected_thesaurus'";
				$sel = oci_parse($dbh,$query_data); 
				oci_execute($sel);
				while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)){		
	      				$DOCUMENT_NUM=$r['DOCUMENT_NUM'];
	      				$THESAURUS_CODE=$r['THESAURUS_CODE'];
	      				$THESAURUS_DATA_NUM=$r['THESAURUS_DATA_NUM'];
	      				$VAL_NUMERIC=$r['VAL_NUMERIC'];
	      				$VAL_TEXT=$r['VAL_TEXT'];
	      				$DOCUMENT_DATE=$r['DOCUMENT_DATE'];
	    				$LOWER_BOUND=$r['LOWER_BOUND'];
		    			$UPPER_BOUND=$r['UPPER_BOUND'];
		    			$ENCOUNTER_NUM=$r['ENCOUNTER_NUM'];
		      			$PATIENT_AGE=$r['AGE_PATIENT'];
		      			 

					//select info data
					$query_2="select concept_code,concept_str,info_complement,measuring_unit,concept_path from dwh_thesaurus_data where THESAURUS_DATA_NUM='$THESAURUS_DATA_NUM'";
					$sel_res=oci_parse($dbh,$query_2);
					oci_execute($sel_res);
					$res=oci_fetch_array($sel_res, OCI_RETURN_NULLS+OCI_ASSOC);
					$concept=$res['CONCEPT_CODE'];
					$CONCEPT_STR=$res['CONCEPT_STR'];
					$INFO_COMPLEMENT=$res['INFO_COMPLEMENT'];
					$MEASURING_UNIT=$res['MEASURING_UNIT'];
					if ($r['CONCEPT_PATH']!='') {
						$CONCEPT_PATH=$r['CONCEPT_PATH']->load();
					}
				
				if ($export_type=='xls'){
							print"<tr>
						   	<td>$DOCUMENT_NUM</td>
							<td>$THESAURUS_CODE</td>
							<td>$concept</td>
							<td>$CONCEPT_STR</td>
							<td>$CONCEPT_PATH</td>
							<td>$INFO_COMPLEMENT</td>
							<td>$MEASURING_UNIT</td>
							<td>$VAL_NUMERIC</td>
							<td>$VAL_TEXT</td>
							<td>$DOCUMENT_DATE</td>
							<td>$LOWER_BOUND</td>
							<td>$UPPER_BOUND</td>
							<td>$ENCOUNTER_NUM</td>
							<td>$HOSPITAL_PATIENT_ID</td>
							<th>$PATIENT_SEX</th>
							<th>$PATIENT_AGE</th>
						  	</tr>";
					}else{
						
						print "$DOCUMENT_NUM\t$THESAURUS_CODE\t$concept\t$CONCEPT_STR\t$CONCEPT_PATH\t$INFO_COMPLEMENT\t$MEASURING_UNIT\t$VAL_NUMERIC\t$VAL_TEXT\t$DOCUMENT_DATE\t$LOWER_BOUND\t$UPPER_BOUND\t$ENCOUNTER_NUM\t$HOSPITAL_PATIENT_ID\t$PATIENT_SEX\t$PATIENT_AGE\n";
					}  				  					
				}
			}//if verif
		} // WHILE
	}//foreach			
}//IF EMPTY



if ($export_type=='xls'){
	print"</table>";		
}

save_log_page($user_num_session,'export_data');
	
?>