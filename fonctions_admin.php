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

function get_list_user_profil() {
	global $dbh;
	$table_user_profil=array();
	$sel_profile=oci_parse($dbh,"select distinct user_profile from dwh_profile_right order by user_profile ");
	oci_execute($sel_profile);
	while ($r=oci_fetch_array($sel_profile,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$table_user_profil[]=$r['USER_PROFILE'];
		
	}
	return $table_user_profil;
}

function get_list_department() {
	global $dbh;
	$table_list_department=array();
	$sel_var1=oci_parse($dbh,"select  department_num,department_str from dwh_thesaurus_department order by department_str ");
	oci_execute($sel_var1);
	while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$table_list_department[$r['DEPARTMENT_NUM']]=$r['DEPARTMENT_STR'];
	}
	return $table_list_department;
}
						
						
function get_line_profile_admin ($user_profile,$option) {
	global $dbh,$tableau_user_droit,$tableau_patient_droit;
	if ($option=='global_features') {
		$line= "<tr id=\"id_tr_global_features_$user_profile\" class=\"over_color\"><td>$user_profile</td>";
		
		$sel=oci_parse($dbh," select count(distinct user_num) as NB from dwh_user_profile  where user_profile='$user_profile'
               and user_num in (select user_num from dwh_user where expiration_date is null or expiration_date>sysdate)");
		oci_execute($sel);
		$res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$nb_user=$res['NB'];
		$line.= "<td>$nb_user</td>";
		foreach ($tableau_user_droit as $right) { 
			$sel=oci_parse($dbh,"select count(*) as NB from dwh_profile_right where user_profile='$user_profile' and right='$right'");
			oci_execute($sel);
			$res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
			$nb=$res['NB'];
			if ($nb>0) {
				$check='checked';
				$bgcolor='#ffccff';
			} else {
				$check='';
				$bgcolor='#ffffff';
			}
			$line.= "<td id=\"id_td_".$user_profile."_".$right."\" style=\"background-color:$bgcolor;\"><input type=checkbox id=\"id_checkbox_".$user_profile."_".$right."\" onclick=\"modifier_droit_profil('$user_profile','$right');\" $check></td>";
		}
		$line.= "<td><a href=\"#\" onclick=\"supprimer_profil('$user_profile');return false;\"><img src=\"images/poubelle_moyenne.png\" border=\"0\" width=\"15px\"></a></td>";
		$line.= "<td>$user_profile</td>";
		$line.= "</tr>";
	}
	if ($option=='patient_features') {
		$line= "<tr id=\"id_tr_patient_features_$user_profile\" class=\"over_color\"><td>$user_profile</td>";
		
		foreach ($tableau_patient_droit as $patient_features) { 
			$sel=oci_parse($dbh,"select count(*) as NB from dwh_profile_right where user_profile='$user_profile' and right='$patient_features'");
			oci_execute($sel);
			$res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
			$nb=$res['NB'];
			if ($nb>0) {
				$check='checked';
				$bgcolor='#ffccff';
			} else {
				$check='';
				$bgcolor='#ffffff';
			}
			$line.= "<td id=\"id_td_".$user_profile."_".$patient_features."\" style=\"background-color:$bgcolor;\"><input type=checkbox id=\"id_checkbox_".$user_profile."_".$patient_features."\" onclick=\"modifier_droit_profil('$user_profile','$patient_features');\" $check></td>";
		}
		$line.= "<td>$user_profile</td>";
		$line.= "</tr>";
	
	}
	if ($option=='document_origin_code') {
	
		$sel=oci_parse($dbh,"select  distinct document_origin_code from dwh_info_load where document_origin_code is not null order by document_origin_code");
		oci_execute($sel);
		while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$document_origin_code=$r['DOCUMENT_ORIGIN_CODE'];
			$tableau_document_origin_code[$document_origin_code]=$document_origin_code;
		}
		
		$line= "<tr id=\"id_tr_document_origin_code_$user_profile\" class=\"over_color\"><td>$user_profile</td>";
		
		$sel=oci_parse($dbh,"select count(*) as NB from dwh_profile_document_origin where user_profile='$user_profile' and document_origin_code='tout'");
		oci_execute($sel);
		$res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$nb=$res['NB'];
		if ($nb>0) {
			$check='checked';
			$bgcolor='#ffccff';
		} else {
			$check='';
			$bgcolor='#ffffff';
		}
		$line.= "<td id=\"id_td_".$user_profile."_tout\"  style=\"background-color:$bgcolor;\"><input type=checkbox id=\"id_checkbox_document_origin_code_".$user_profile."_tout\" onclick=\"modifier_droit_profil_document_origin_code('$user_profile','tout','tout');\" $check></td>";
		foreach ($tableau_document_origin_code as $document_origin_code) { 
			$sel=oci_parse($dbh,"select count(*) as NB from dwh_profile_document_origin where user_profile='$user_profile' and document_origin_code='$document_origin_code'");
			oci_execute($sel);
			$res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
			$nb=$res['NB'];
			if ($nb>0) {
				$check='checked';
				$bgcolor='#ffccff';
			} else {
				$check='';
				$bgcolor='#ffffff';
			}
			$id_document_origin_code=preg_replace("/[^a-z]/i","_",$document_origin_code);
			$line.= "<td id=\"id_td_".$user_profile."_".$id_document_origin_code."\" style=\"background-color:$bgcolor;\"><input type=checkbox id=\"id_checkbox_document_origin_code_".$user_profile."_".$id_document_origin_code."\" onclick=\"modifier_droit_profil_document_origin_code('$user_profile','$document_origin_code','$id_document_origin_code');\" $check></td>";
		}
		$line.= "<td>$user_profile</td>";
		$line.= "</tr>";
	}
	
	return $line;
}



function get_list_patients_opposed () {
	global $dbh;
	$tableau_result=array();
	$sel=oci_parse($dbh,"select patient_num, hospital_patient_id , origin_patient_id , patient_num, opposition_date , to_char(opposition_date,'DD/MM/YYYY HH24:MI') as  opposition_date_char from DWH_PATIENT_OPPOSITION  order by opposition_date desc");
	oci_execute($sel);
	while ($res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$patient_num=$res['PATIENT_NUM'];
		$hospital_patient_id=$res['HOSPITAL_PATIENT_ID'];
		$origin_patient_id=$res['ORIGIN_PATIENT_ID'];
		$opposition_date_char=$res['OPPOSITION_DATE_CHAR'];
		$tableau_result[]=array('patient_num'=>$patient_num,'hospital_patient_id'=>$hospital_patient_id,'origin_patient_id'=>$origin_patient_id,'opposition_date_char'=>$opposition_date_char);
	}
	return $tableau_result;
}
function cancel_opposition($patient_num) {
	global $dbh_etl;
	$req="delete from DWH_PATIENT_OPPOSITION where patient_num=$patient_num  ";
	$sel=oci_parse($dbh_etl,$req);
	oci_execute($sel) || die ("erreur req $req\n");
}

function validate_opposition($patient_num) {
	global $dbh_etl;
	$req="insert into DWH_PATIENT_OPPOSITION (hospital_patient_id , origin_patient_id , patient_num, opposition_date) select hospital_patient_id , origin_patient_id , patient_num,sysdate from dwh_patient_ipphist where patient_num=$patient_num ";
	$sel=oci_parse($dbh_etl,$req);
	oci_execute($sel) || die ("erreur req $req\n");
	    
	$list_table_to_delete=array('DWH_COHORT_RESULT',
					'DWH_COHORT_RESULT_COMMENT',
					'DWH_DATAMART_RESULT',
					'DWH_ECRF_ANSWER',
					'DWH_LOG_PATIENT',
					'DWH_LOG_QUERY',
					'DWH_PATIENT_STAT',
					'DWH_PROCESS_PATIENT',
					'DWH_QUERY_RESULT',
					'DWH_REQUEST_ACCESS_PATIENT');
	foreach ($list_table_to_delete as $table_name) {
	    $req="delete from $table_name where patient_num=$patient_num ";
	    //$sel=oci_parse($dbh,$req);
	   //oci_execute($sel) || die ("erreur req $req\n");
	}
	
/*	TABLE WITH DATA IMPORTED : 

Creation of view : create view dwh_patient_view as select * from dwh_patient where patient_num not in (select patient_num from dwh_patient_opposition) ?
DWH_PATIENT
DWH_PATIENT_IPPHIST
DWH_PATIENT_REL
DWH_PATIENT_MVT
DWH_PATIENT_STAY
DWH_DOCUMENT
DWH_TEXT
DWH_DATA
DWH_ENRSEM
DWH_FILE
DWH_PATIENT_DEPARTMENT
*/
}

function get_list_cgu () {
	global $dbh;
	$tableau_result=array();
	$sel=oci_parse($dbh," select cgu_num, cgu_text ,to_char(cgu_date,'DD/MM/YYYY') as char_cgu_date, cgu_date , published,to_char(published_date,'DD/MM/YYYY') as char_published_date,to_char(unpublished_date,'DD/MM/YYYY') as char_unpublished_date  from DWH_ADMIN_CGU order by cgu_date desc");
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$cgu_num=$r['CGU_NUM'];
		if ($r['CGU_TEXT']!='') {
			$cgu_text=$r['CGU_TEXT']->load();
		}
		$char_cgu_date=$r['CHAR_CGU_DATE'];
		$published=$r['PUBLISHED'];
		$published_date=$r['CHAR_PUBLISHED_DATE'];
		$unpublished_date=$r['CHAR_UNPUBLISHED_DATE'];
		$tableau_result[]=array('cgu_num'=>$cgu_num,'cgu_text'=>$cgu_text,'cgu_date'=>$char_cgu_date,'published'=>$published,'published_date'=>$published_date,'unpublished_date'=>$unpublished_date);
	}
	return $tableau_result;

}




function get_last_cgu () {
	global $dbh;
	$sel=oci_parse($dbh," select cgu_num, cgu_text ,to_char(cgu_date,'DD/MM/YYYY') as char_cgu_date, cgu_date , published,to_char(published_date,'DD/MM/YYYY') as char_published_date,to_char(unpublished_date,'DD/MM/YYYY') as char_unpublished_date 
	 from DWH_ADMIN_CGU order by cgu_date desc");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$cgu_num=$r['CGU_NUM'];
	if ($r['CGU_TEXT']!='') {
		$cgu_text=$r['CGU_TEXT']->load();
	}
	$char_cgu_date=$r['CHAR_CGU_DATE'];
	$published=$r['PUBLISHED'];
	$published_date=$r['CHAR_PUBLISHED_DATE'];
	$unpublished_date=$r['CHAR_UNPUBLISHED_DATE'];
	$tableau_result=array('cgu_num'=>$cgu_num,'cgu_text'=>$cgu_text,'cgu_date'=>$char_cgu_date,'published'=>$published,'published_date'=>$published_date,'unpublished_date'=>$unpublished_date);
	return $tableau_result;
}

function get_list_cgu_user ($cgu_num) {
	global $dbh;
	$tableau_result=array();
	$sel=oci_parse($dbh,"select user_num,to_char(date_signature,'DD/MM/YYYY') as char_date_signature,date_signature from DWH_ADMIN_CGU_user where cgu_num=$cgu_num order by date_signature desc");
	oci_execute($sel);
	while ($res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$user_num=$res['USER_NUM'];
		$date_signature=$res['CHAR_DATE_SIGNATURE'];
		$tableau_result[]=array('user_num'=>$user_num,'date_signature'=>$date_signature);
	}
	return $tableau_result;

}

function get_nb_cgu_user ($cgu_num) {
	global $dbh;
	
	$sel=oci_parse($dbh,"select count(*) as nb  from DWH_ADMIN_CGU_user where cgu_num=$cgu_num ");
	oci_execute($sel);
	$res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb=$res['NB'];
	return $nb;

}

function insert_cgu ($cgu_text) {
	global $dbh;
	if ($cgu_text!='') {
		$cgu_num=get_uniqid();
                $requeteins="insert into dwh_admin_cgu  (cgu_num, cgu_text ,cgu_date , published ) values ('$cgu_num', :cgu_text ,sysdate , 0)";
                $stmt = ociparse($dbh,$requeteins);
                $rowid = ocinewdescriptor($dbh, OCI_D_LOB);
                ocibindbyname($stmt, ":cgu_text",$cgu_text);
                $execState = ociexecute($stmt);
                ocifreestatement($stmt);
	}
}


function delete_cgu ($cgu_num) {
	global $dbh;
	if ($cgu_num!='') {
	        $req="delete from dwh_admin_cgu where cgu_num=$cgu_num and published=0"; // delete cascade on dwh_admin_cgu_user
		$del=oci_parse($dbh,$req);
		oci_execute($del);
		
	       
	}
}


function update_cgu ($cgu_num,$published) {
	global $dbh;
	if ($cgu_num!='') {
		if ($published==1) {
	                $req="update dwh_admin_cgu set published=1,published_date=sysdate where cgu_num=$cgu_num ";
	  		$del=oci_parse($dbh,$req);
			oci_execute($del);
		} else {
	                $req="update dwh_admin_cgu set published=0,unpublished_date=sysdate where cgu_num=$cgu_num ";
	  		$del=oci_parse($dbh,$req);
			oci_execute($del);
		}
	}
}

function delete_cgu_user ($cgu_num) {
	global $dbh;
	
	$sel=oci_parse($dbh,"select count(*) as nb  from DWH_ADMIN_CGU_user where cgu_num=$cgu_num ");
	oci_execute($sel);
	$res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb=$res['NB'];
	return $nb;

}
?>