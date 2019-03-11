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
?>