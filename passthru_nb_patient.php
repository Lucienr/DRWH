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


ini_set("memory_limit","200M");
putenv("NLS_LANG=French");

include_once "parametrage.php";
include_once "connexion_bdd.php";


$user_num_session=$argv[1];
$datamart_num=$argv[2];
$query_key_arg=$argv[3];

$sql='';
$sel = oci_parse($dbh, "select SQL_CLOB from dwh_tmp_query where query_key='$query_key_arg' and user_num=$user_num_session and datamart_num=$datamart_num ");   
oci_execute($sel);
$r = oci_fetch_array($sel, OCI_ASSOC);
if ($r['SQL_CLOB']!='') {
	$sql=$r['SQL_CLOB']->load();
}

$all_departments='';


$nb_patient_avant=0;
$tableau_patient_num=array();
$sel = oci_parse($dbh,"$sql");   
oci_execute($sel);
while ( $r = oci_fetch_array($sel, OCI_ASSOC)) {
	$patient_num=$r['PATIENT_NUM'];
	$tableau_patient_num[$patient_num]=$patient_num;
	$nb_patient=count($tableau_patient_num);
	if ($nb_patient>$nb_patient_avant+50) {
		$upd = oci_parse($dbh,"update  dwh_tmp_query set count_patient=$nb_patient  where query_key='$query_key_arg' and user_num=$user_num_session and datamart_num=$datamart_num ");   
		oci_execute($upd);
		$nb_patient_avant=$nb_patient;
	}
}
$nb_patient=count($tableau_patient_num);

$upd = oci_parse($dbh,"update  dwh_tmp_query set count_patient=$nb_patient  where query_key='$query_key_arg' and user_num=$user_num_session and datamart_num=$datamart_num ");   
oci_execute($upd);

oci_close ($dbh);
oci_close ($dbh_etl);
?>