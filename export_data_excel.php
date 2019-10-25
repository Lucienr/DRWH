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
date_default_timezone_set('Europe/Paris');
putenv("NLS_LANG=French");
putenv("NLS_LANG=FRENCH");
putenv("NLS_LANGUAGE=FRENCH_FRANCE.WE8MSWIN1252");
error_reporting(E_ALL ^ E_NOTICE);

if ($_GET['process_num'] !='') {
	session_start();
	$user_num_session=$_SESSION['dwh_user_num'];
}
ini_set("memory_limit","500M");


include_once("parametrage.php");
include_once("connexion_bdd.php");
include_once("ldap.php");
include_once("fonctions_dwh.php");
include_once("fonctions_droit.php");

$date_today=date("dmYHi");
$date=date("YMjHms");
 
 // si en passthru via ajax_export.php : 
//php export_data_excel.php $user_num_session $tmpresult_num $process_num $file_type $export_type \"$list_thesaurus\" \"$list_concepts\" \"$file_name\"

if ($_GET['process_num'] !='') {
	$process=get_process ($_GET['process_num']);
	$user_num=$process['USER_NUM'];
	if ($user_num==$user_num_session) {
		$file_name=$process['COMMENTARY'];
		$result=$process['RESULT'];
		if (preg_match("/.txt/i",$file_name)) {
			header("Content-Type: text/plain");
			header ("Content-Disposition: attachment; filename=$file_name.txt");
		}
		if (preg_match("/.xls/i",$file_name)) {
			header ("Content-Type: application/excel");
			header ("Content-Disposition: attachment; filename=$file_name.xls");	
		}
		print $result;
	}
	session_write_close();
	exit;
}

if ($argv[1] !='') {
	$mode_export="argv";
	$user_num_session=$argv[1];
	$tmpresult_num=$argv[2];
	$process_num=$argv[3];
	$file_type=$argv[4];
	$export_type=$argv[5];
	$list_thesaurus=$argv[6];
	$list_concepts=$argv[7];
	$file_name=$argv[8];
	$patient_or_document=$argv[9];

	$file_name=preg_replace("/\s/","_",$file_name);
	
	if ($file_name==''){
		$file_name="export_dwh_data_$date";
	}
	
	
	$thesaurus=explode(',',$list_thesaurus);
	$concepts=explode(',',$list_concepts);
	
	create_process ($process_num,$user_num_session,'0',get_translation('PROCESS_START','debut du process'),'',"sysdate+20",'export_data');
	
	if ($export_type=='row') {
		$query_patients="select count(distinct patient_num) as nb_patient from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num";
		$sel = oci_parse($dbh,$query_patients); 
		oci_execute($sel);	
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$nb_patient=$r['NB_PATIENT'];
	} else {
		$query_patients="select count(*) as nb_patient from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num)";
		$sel = oci_parse($dbh,$query_patients); 
		oci_execute($sel);	
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$nb_patient=$r['NB_PATIENT'];
	}
	update_process ($process_num,'0',get_translation('PROCESS_START','debut du process'),'',$user_num_session,$file_type);

	print "
	file_type $file_type
	process_num $process_num
	tmpresult_num $tmpresult_num
	export_type $export_type
	list_thesaurus $list_thesaurus
	list_concepts $list_concepts
	file_name $file_name
	";	
} else {
	$mode_export="post";
	$user_num_session=$_SESSION['dwh_user_num'];

	$tmpresult_num=$_POST['tmpresult_num'];
	$export_type=$_POST['export_type']; // empty, row //
	$file_type=$_POST['file_type'];
			
	$file_name=$_POST['file_name'];
	$file_name=preg_replace("/\s/","_",$file_name);
	
	if ($file_name==''){
		$file_name="export_dwh_concepts_$date";
	}
	
	$concepts=$_POST['concepts'];	
	$thesaurus=$_POST['thesaurus'];	
	if ($file_type=='xls'){
		header ("Content-Type: application/excel");
		header ("Content-Disposition: attachment; filename=$file_name.xls");	
	} else {
		header("Content-Type: text/plain");
		header ("Content-Disposition: attachment; filename=$file_name.txt");
	}
}
print "3
";

$tableau_thesaurus_data_used=array();
if (!empty($thesaurus)){
	print "test_thes deb\n";
	foreach ($thesaurus as $selected_thesaurus){
		$query="select thesaurus_data_num from DWH_DATA where thesaurus_code='$selected_thesaurus' and $patient_or_document in (select dwh_tmp_result_$user_num_session.$patient_or_document from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num)";
		$sel=oci_parse($dbh,$query);
		oci_execute($sel);
		while ($r=oci_fetch_array($sel, OCI_RETURN_NULLS+OCI_ASSOC)) {
			$tableau_thesaurus_data_used[$r['THESAURUS_DATA_NUM']]='ok';
			$concepts[]=$r['THESAURUS_DATA_NUM'];
		}
	}
}
if (!empty($concepts)){		
	foreach ($concepts as $thesaurus_data_num){
		if ($thesaurus_data_num!='') {
			$tableau_thesaurus_data_used[$thesaurus_data_num]='ok';
		}
	}
}
print_r($tableau_thesaurus_data_used);
/// ENTETE EXPORT 
if ($file_type=='xls'){
#	$resultat_final.="<style>
#		.num {
#		  mso-number-format:General;
#		}
#		.text{	
#		  mso-number-format:\"\@\";/*force text*/
#		}
#		</style>";
	$resultat_final.="<table id=\"id_table_export_concept\" style=\"width:100%\">";	
}
		
if ($export_type=='row'){
	if ($file_type=='xls'){			
		$resultat_final.="<tr>
			<th>PATIENT_NUM</th>
			<th>IPP</th>
			<th>BIRTH_DATE</th>
			<th>ENCOUNTER_NUM</th>
			<th>PATIENT_SEX</th>
			<th>PATIENT_AGE</th>
			<th>DOCUMENT_NUM</th>
			<th>DOCUMENT_DATE</th>
			<th>THESAURUS_CODE</th>
			<th>CONCEPT_CODE</th>
			<th>CONCEPT_STR</th>
			<th>INFO_COMPLEMENT</th>
			<th>MEASURING_UNIT</th>
			<th>VAL_NUMERIC</th>
			<th>VAL_TEXT</th>
			<th>LOWER_BOUND</th>
			<th>UPPER_BOUND</th>
		</tr>";
	} else {
		$resultat_final.="PATIENT_NUM	";
		$resultat_final.="IPP	";
		$resultat_final.="BIRTH_DATE	";
		$resultat_final.="ENCOUNTER_NUM	";
		$resultat_final.="PATIENT_SEX	";
		$resultat_final.="PATIENT_AGE	";
		$resultat_final.="DOCUMENT_NUM	";
		$resultat_final.="DOCUMENT_DATE	";
		$resultat_final.="THESAURUS_CODE	";
		$resultat_final.="CONCEPT_CODE	";
		$resultat_final.="CONCEPT_STR	";
		$resultat_final.="INFO_COMPLEMENT	";
		$resultat_final.="MEASURING_UNIT	";
		$resultat_final.="VAL_NUMERIC	";
		$resultat_final.="VAL_TEXT	";
		$resultat_final.="LOWER_BOUND	";
		$resultat_final.="UPPER_BOUND\n";
	}
} else if ($export_type=='col'){
	if ($file_type=='xls'){
		$resultat_final.="<tr>
			<th>PATIENT_NUM</th>
			<th>IPP</th>
			<th>BIRTH_DATE</th>
			<th>PATIENT_SEX</th>
			<th>DOCUMENT_DATE</th>";
	} else {
		$resultat_final.="PATIENT_NUM	";
		$resultat_final.="IPP	";
		$resultat_final.="BIRTH_DATE	";
		$resultat_final.="PATIENT_SEX	";
		$resultat_final.="DOCUMENT_DATE	";
	}	
	if (!empty($concepts)){
		foreach ($tableau_thesaurus_data_used as $thesaurus_data_num => $ok){
			//select info concept
			if ($thesaurus_data_num!='') {
		  		$query="select concept_str,info_complement,measuring_unit,thesaurus_data_num from dwh_thesaurus_data where thesaurus_data_num='$thesaurus_data_num'";
				$sel=oci_parse($dbh,$query);
				oci_execute($sel);
				$r=oci_fetch_array($sel, OCI_RETURN_NULLS+OCI_ASSOC);
				$CONCEPT_STR=$r['CONCEPT_STR'];
				$INFO_COMPLEMENT=$r['INFO_COMPLEMENT'];
				$MEASURING_UNIT=$r['MEASURING_UNIT'];
				$CONCEPT_STR=str_replace("	"," ",$CONCEPT_STR);
				$INFO_COMPLEMENT=str_replace("	"," ",$INFO_COMPLEMENT);
				$MEASURING_UNIT=str_replace("	"," ",$MEASURING_UNIT);
				if ($file_type=='xls'){
					$resultat_final.="<th>$CONCEPT_STR $INFO_COMPLEMENT $MEASURING_UNIT</th>";
				} else {
					$resultat_final.="$CONCEPT_STR $INFO_COMPLEMENT $MEASURING_UNIT	";
				}
			}
		}
		print_r($resultat_final);
	}
	if ($file_type=='xls'){
		$resultat_final.="</tr>";
	} else {
		$resultat_final.="\n";
	}
	update_process ($process_num,'0',"entete calculee",$resultat_final,$user_num_session,$file_type);
	$resultat_final='';
} 

//// CONTENU EXPORT
if (!empty($tableau_thesaurus_data_used)){		
	// EXPORT EN LIGNE AVEC UNE LIGNE PAR PATIENT / DATE / CONCEPT DATA
	if ($export_type=='row'){
		$i_patient=0;
		//select patient
		update_process ($process_num,'0',"$i_patient / $nb_patient",'',$user_num_session,$file_type);
    		$query_patients="select distinct $patient_or_document as cle , patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num order by patient_num";
    		$sel_patient = oci_parse($dbh,$query_patients); 
    		oci_execute($sel_patient);	
    		while ($res_patient=oci_fetch_array($sel_patient,OCI_RETURN_NULLS+OCI_ASSOC)) {	
    			$patient_num=$res_patient['PATIENT_NUM'];
    			$cle=$res_patient['CLE'];
    			$i_patient++;
    			//if ($i_patient % 20==0 && $mode_export=='argv') {
				update_process ($process_num,'0',"$i_patient / $nb_patient",$resultat_final,$user_num_session,$file_type);
				$resultat_final='';
    			//}
    			$verif=autorisation_voir_patient($patient_num,$user_num_session);
    			if($verif=='ok'){
	    			$tab_patient=get_patient ($patient_num);
	    			$HOSPITAL_PATIENT_ID=$tab_patient['HOSPITAL_PATIENT_ID'];   
	    			$PATIENT_SEX=$tab_patient['SEX'];  
	    			$BIRTH_DATE=$tab_patient['BIRTH_DATE'];  
				foreach ($tableau_thesaurus_data_used as $thesaurus_data_num => $ok){
					if ($thesaurus_data_num!='') {
						//select info concept
				  		$query="select concept_str,info_complement,measuring_unit,thesaurus_data_num from dwh_thesaurus_data where thesaurus_data_num='$thesaurus_data_num'";
						$sel=oci_parse($dbh,$query);
						oci_execute($sel);
						$r=oci_fetch_array($sel, OCI_RETURN_NULLS+OCI_ASSOC);
						$CONCEPT_STR=$r['CONCEPT_STR'];
						$INFO_COMPLEMENT=$r['INFO_COMPLEMENT'];
						$MEASURING_UNIT=$r['MEASURING_UNIT'];
		      				$query_data="select DOCUMENT_NUM,THESAURUS_CODE,VAL_NUMERIC,VAL_TEXT,to_char(DOCUMENT_DATE,'DD/MM/YYYY HH24:MI') as document_date,LOWER_BOUND,UPPER_BOUND,ENCOUNTER_NUM,AGE_PATIENT  
		      				from DWH_DATA where $patient_or_document=$cle and THESAURUS_DATA_NUM=$thesaurus_data_num";			      				
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
		
							if ($file_type=='xls'){
									$resultat_final.="<tr>
									<td>$patient_num</td>
									<td>'$HOSPITAL_PATIENT_ID</td>
									<td>$BIRTH_DATE</td>
									<td>$ENCOUNTER_NUM</td>
									<td>$PATIENT_SEX</td>
									<td>$PATIENT_AGE</td>
								   	<td>$DOCUMENT_NUM</td>
									<td>$DOCUMENT_DATE</td>
									<td>$THESAURUS_CODE</td>
									<td>$concept</td>
									<td>$CONCEPT_STR</td>
									<td>$INFO_COMPLEMENT</td>
									<td>$MEASURING_UNIT</td>
									<td>$VAL_NUMERIC</td>
									<td>$VAL_TEXT</td>
									<td>$LOWER_BOUND</td>
									<td>$UPPER_BOUND</td>
								  	</tr>";
							}else{
								$resultat_final.="$patient_num	$HOSPITAL_PATIENT_ID	$BIRTH_DATE\t$ENCOUNTER_NUM\t$PATIENT_SEX\t$PATIENT_AGE\t$DOCUMENT_NUM\t$DOCUMENT_DATE\t$THESAURUS_CODE\t$concept\t$CONCEPT_STR\t$INFO_COMPLEMENT\t$MEASURING_UNIT\t$VAL_NUMERIC\t$VAL_TEXT\t$LOWER_BOUND\t$UPPER_BOUND\n";
							}
						} 	
					}	
				}
			}		
		}
	}// format 
	// EXPORT EN COLONNE AVEC UNE LIGNE PAR PATIENT / DATE 
	else if ($export_type=='col'){
		//select patient
		$i_patient=0;
		$tableau_patient_ok=array();
		$tableau_patient=array();
    		
		print "$i_patient / $nb_patient\n";
		update_process ($process_num,'0',"$i_patient / $nb_patient",'',$user_num_session,$file_type);
    		$query_patients="select distinct $patient_or_document as cle,patient_num, to_char(document_date,'DD/MM/YYYY HH24:MI') as date_char, document_date from DWH_DATA where $patient_or_document in (select dwh_tmp_result_$user_num_session.$patient_or_document from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) order by patient_num, document_date";
    		$sel_patient = oci_parse($dbh,$query_patients); 
    		oci_execute($sel_patient);	
    		while ($res_patient=oci_fetch_array($sel_patient,OCI_RETURN_NULLS+OCI_ASSOC)) {	
    			$patient_num=$res_patient['PATIENT_NUM'];
    			$cle=$res_patient['CLE'];
    			$date_char=$res_patient['DATE_CHAR'];
			$i_patient++;
	    		if ($i_patient % 100==0 && $mode_export=='argv') {
				update_process ($process_num,'0',"$i_patient / $nb_patient",$resultat_final,$user_num_session,$file_type);
				$resultat_final='';
				print "$i_patient / $nb_patient\n";
    			}
    			$verif=autorisation_voir_patient($patient_num,$user_num_session);
    			if($verif=='ok'){
    				
				if ($tableau_patient_ok["$patient_num"]=='') {
			    		print "tableau_patient deb\n";
				    	$tableau_patient=array();
					$query_data="select data_num,thesaurus_data_num,to_char(document_date,'DD/MM/YYYY HH24:MI') as document_date_char,VAL_NUMERIC,VAL_TEXT from DWH_DATA where $patient_or_document=$cle  ";			      				
					$sel = oci_parse($dbh,$query_data); 
					oci_execute($sel);
					while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
						$data_num=$r['DATA_NUM'];
						$thesaurus_data_num=$r['THESAURUS_DATA_NUM'];
						$document_date_char=$r['DOCUMENT_DATE_CHAR'];
						$VAL_NUMERIC=$r['VAL_NUMERIC'];
						$VAL_TEXT=$r['VAL_TEXT'];
						$val=$VAL_NUMERIC.$VAL_TEXT;
						$tableau_patient_ok["$patient_num"]='ok';
						if ($document_date_char!='' && $tableau_thesaurus_data_used[$thesaurus_data_num]=='ok') {
							$tableau_patient["$patient_num;$thesaurus_data_num;$document_date_char"]=$val;
						}
					}
			    		print "tableau_patient fini\n";
		    			$tab_patient=array();
		    			$tab_patient=get_patient ($patient_num);
		    			$HOSPITAL_PATIENT_ID=$tab_patient['HOSPITAL_PATIENT_ID'];   
		    			$PATIENT_SEX=$tab_patient['SEX'];  
			    		$BIRTH_DATE=$tab_patient['BIRTH_DATE'];
		    		}
		    		
				$val_exists='';
				if ($file_type=='xls'){
					$line_concepts="<tr>
				   	<td>$patient_num</td>
					<td>'$HOSPITAL_PATIENT_ID</td>
				   	<td>$BIRTH_DATE</td>
					<td>$PATIENT_SEX</td>
					<td>$date_char</td>";
				} else {
					$line_concepts="$patient_num	$HOSPITAL_PATIENT_ID	$BIRTH_DATE	$PATIENT_SEX	$date_char	";
				}
				foreach ($tableau_thesaurus_data_used as $thesaurus_data_num => $ok){
					if ($thesaurus_data_num!='') {
						//select info concept
	    					if ($date_char!='') {
	    						$val=$tableau_patient["$patient_num;$thesaurus_data_num;$date_char"];
	    						if ($val!='') {
				      				//$query_data="select DOCUMENT_NUM,THESAURUS_CODE,VAL_NUMERIC,VAL_TEXT,DOCUMENT_DATE,LOWER_BOUND,UPPER_BOUND,ENCOUNTER_NUM,AGE_PATIENT  
				      				//from DWH_DATA where data_num=$data_num";			      				
				      				//$sel = oci_parse($dbh,$query_data); 
				      				//oci_execute($sel);
				      				//$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);	
			      		      			//$VAL_NUMERIC=$r['VAL_NUMERIC'];
			      		      			//$VAL_TEXT=$r['VAL_TEXT'];
			      		      			
								if ($file_type=='xls'){
									$line_concepts.="<td>$val</td>";
								}else{
									$line_concepts.="$val\t";
								}
								if ($val!='') {
									$val_exists='ok';
								}
							} else {
								if ($file_type=='xls'){
									$line_concepts.="<td></td>";
								}else{
									$line_concepts.="\t";
								}
							}
						} else {
						
			      				$query_data="select DOCUMENT_NUM,THESAURUS_CODE,VAL_NUMERIC,VAL_TEXT,LOWER_BOUND,UPPER_BOUND,ENCOUNTER_NUM,AGE_PATIENT  
			      				from DWH_DATA where patient_num=$patient_num and THESAURUS_DATA_NUM=$thesaurus_data_num and document_date is null";			      				
			      				$sel = oci_parse($dbh,$query_data); 
			      				oci_execute($sel);
			      				$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);	
		      		      			$VAL_NUMERIC=$r['VAL_NUMERIC'];
		      		      			$VAL_TEXT=$r['VAL_TEXT'];
		      		    			$LOWER_BOUND=$r['LOWER_BOUND'];
		      		    			$UPPER_BOUND=$r['UPPER_BOUND'];
		      		    			$ENCOUNTER_NUM=$r['ENCOUNTER_NUM'];
		      		      			$PATIENT_AGE=$r['AGE_PATIENT'];
		
							if ($file_type=='xls'){
								$line_concepts.="<td>$VAL_NUMERIC$VAL_TEXT</td>";
							}else{
								$line_concepts.="$VAL_NUMERIC$VAL_TEXT\t";
							}
							if ($VAL_NUMERIC!=''||$VAL_TEXT!='') {
								$val_exists='ok';
							}
						}
					}
				}
				if ($val_exists=='ok') {
					$resultat_final.=$line_concepts;
					if ($file_type=='xls'){
						$resultat_final.="</tr>";
					}else{
						$resultat_final.="\n";
					}
				}
					
			}// if verif		
		}//WHILE	
	} // format
}//IF EMPTY	


if ($file_type=='xls'){
	$resultat_final.="</table>";		
}

if ($mode_export=='argv') {
	update_process ($process_num,'1',"$file_name.$file_type",$resultat_final,$user_num_session,$file_type);
	update_process_end_date ($process_num,"sysdate + 5",$user_num_session) ;
	sauver_notification ($user_num_session,$user_num_session,'process',"",$process_num);
} else {
	print "$resultat_final";
}

save_log_page($user_num_session,'export_data');
	
?>