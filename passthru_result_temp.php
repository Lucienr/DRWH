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

$user_num=$argv[1];
$datamart_num=$argv[2];
$query_key_arg=$argv[3];

print "query_key_arg : $query_key_arg\n\n";
print "datamart_num : $datamart_num\n\n";
print "select sql_clob from dwh_tmp_query where query_key='$query_key_arg' and user_num=$user_num and datamart_num=$datamart_num\n";
$sel = oci_parse($dbh, "select sql_clob from dwh_tmp_query where query_key='$query_key_arg' and user_num=$user_num and datamart_num=$datamart_num ");   
oci_execute($sel);
$r = oci_fetch_array($sel, OCI_ASSOC);
if ($r['SQL_CLOB']!='') {
	$sql=$r['SQL_CLOB']->load();
}
print "sql : $sql\n\n";

$nb_doc=0;
$tableau_final_query_key=array();
$tableau_patient_num=array();
$sel = oci_parse($dbh,"$sql");   
oci_execute($sel);
while ( $r = oci_fetch_array($sel, OCI_ASSOC)) {
	$document_num=$r['DOCUMENT_NUM'];
	$query_key=$r['QUERY_KEY'];
	$query_date=$r['QUERY_DATE'];
	$patient_num=$r['PATIENT_NUM'];
	//$user_num=$r['USER_NUM'];
	$datamart_num=$r['DATAMART_NUM'];
	$document_origin_code=$r['DOCUMENT_ORIGIN_CODE'];
	$document_date=$r['DOCUMENT_DATE'];
	$nb_doc++;
	
	$tableau_patient_num[$patient_num]=$patient_num;
	
	
	$tableau_final_query_key[$document_num]=$query_key;
	$tableau_final_patient_num[$document_num]=$patient_num;
	//$tableau_final_user_num[$document_num]=$user_num;
	//$tableau_final_num_datamart[$document_num]=$datamart_num;
	$tableau_final_document_origin_code[$document_num]=$document_origin_code;
	$tableau_final_date_document[$document_num]=$document_date;
	
	if ($nb_doc==1000) {
		$nb_doc=0;
		$nb_patient=count($tableau_patient_num);
		$upd = oci_parse($dbh,"update  dwh_tmp_query set count_patient=$nb_patient  where query_key='$query_key_arg' and user_num=$user_num and datamart_num=$datamart_num ");   
		oci_execute($upd);
	}
}
$nb_patient=count($tableau_patient_num);
print "nb_patient : $nb_patient\n\n";

$upd = oci_parse($dbh,"update  dwh_tmp_query set count_patient=$nb_patient  where query_key='$query_key_arg' and user_num=$user_num and datamart_num=$datamart_num ");   
oci_execute($upd);

print "update  dwh_tmp_query set count_patient=$nb_patient  where query_key='$query_key_arg' and user_num=$user_num and datamart_num=$datamart_num \n\n";

foreach ($tableau_final_query_key as $document_num => $query_key) {
	$patient_num=$tableau_final_patient_num[$document_num];
	//$user_num=$tableau_final_user_num[$document_num];
	//$datamart_num=$tableau_final_num_datamart[$document_num];
	$document_origin_code=$tableau_final_document_origin_code[$document_num];
	$document_date=$tableau_final_date_document[$document_num];
	$query_key=str_replace("'","''",$query_key);
        $ins = oci_parse($dbh, " insert into dwh_tmp_preresult (document_num ,query_key , tmpresult_date,patient_num,user_num,datamart_num,document_origin_code,document_date) values ($document_num,'$query_key',sysdate,'$patient_num','$user_num','$datamart_num','$document_origin_code',to_date('$document_date','DD/MM/YYYY HH24:MI')) ");   
        oci_execute($ins);
}


$upd = oci_parse($dbh,"update  dwh_tmp_query set status_calculate=1  where query_key='$query_key_arg' and user_num=$user_num and datamart_num=$datamart_num ");   
oci_execute($upd);

print "update  dwh_tmp_query set status_calculate=1  where query_key='$query_key_arg' and user_num=$user_num and datamart_num=$datamart_num  \n\n";

if ($nb_patient>0) {
	$tab_requete=explode(";",$query_key_arg);
	$requete_ft=$tab_requete[0];
	
	$sel = oci_parse($dbh,"select count(*) nbmot from dwh_thesaurus_corrortho where term=lower('$requete_ft')");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	$nbmot=$r['NBMOT'];
	if ($nbmot==0) {
		$sel = oci_parse($dbh,"insert into dwh_thesaurus_corrortho values (lower('$requete_ft'))");   
		oci_execute($sel);
	}
}




#ini_set("memory_limit","200M");
#putenv("NLS_LANG=French");
#
#include_once "parametrage.php";
#include_once "connexion_bdd.php";
#
#$user_num=$argv[1];
#$datamart_num=$argv[2];
#$query_key_arg=$argv[3];
#
#print "query_key_arg : $query_key_arg\n\n";
#print "datamart_num : $datamart_num\n\n";
#print "select sql_clob from dwh_tmp_query where query_key='$query_key_arg' and user_num=$user_num and datamart_num=$datamart_num\n";
#$sel = oci_parse($dbh, "select sql_clob from dwh_tmp_query where query_key='$query_key_arg' and user_num=$user_num and datamart_num=$datamart_num ");   
#oci_execute($sel);
#$r = oci_fetch_array($sel, OCI_ASSOC);
#if ($r['SQL_CLOB']!='') {
#	$sql=$r['SQL_CLOB']->load();
#}
#print "sql : $sql\n\n";
#
#$nb_doc=0;
#$tableau_final_query_key=array();
#$tableau_patient_num=array();
#$sel = oci_parse($dbh,"$sql");   
#oci_execute($sel);
#while ( $r = oci_fetch_array($sel, OCI_ASSOC)) {
#	$document_num=$r['DOCUMENT_NUM'];
#	$query_key=$r['QUERY_KEY'];
#	$query_date=$r['QUERY_DATE'];
#	$patient_num=$r['PATIENT_NUM'];
#	//$user_num=$r['USER_NUM'];
#	$datamart_num=$r['DATAMART_NUM'];
#	$document_origin_code=$r['DOCUMENT_ORIGIN_CODE'];
#	$document_date=$r['DOCUMENT_DATE'];
#	$nb_doc++;
#	
#	$tableau_patient_num[$patient_num]=$patient_num;
#	$tableau_final_query_key[$document_num]=$query_key;
#	$tableau_final_patient_num[$document_num]=$patient_num;
#	$tableau_final_document_origin_code[$document_num]=$document_origin_code;
#	$tableau_final_date_document[$document_num]=$document_date;
#	//$tableau_final_user_num[$document_num]=$user_num;
#	//$tableau_final_num_datamart[$document_num]=$datamart_num;
#	
#	if ($nb_doc==1000) {
#		$memory_get_usage=memory_get_usage();
#		print "memory_get_usage : $memory_get_usage\n";
#		$nb_doc=0;
#		$nb_patient=count($tableau_patient_num);
#		$upd = oci_parse($dbh,"update  dwh_tmp_query set count_patient=$nb_patient  where query_key='$query_key_arg' and user_num=$user_num and datamart_num=$datamart_num ");   
#		oci_execute($upd);
#		 
#		foreach ($tableau_final_query_key as $document_num => $query_key) {
#			$patient_num=$tableau_final_patient_num[$document_num];
#			$document_origin_code=$tableau_final_document_origin_code[$document_num];
#			$document_date=$tableau_final_date_document[$document_num];
#			//$user_num=$tableau_final_user_num[$document_num];
#			//$datamart_num=$tableau_final_num_datamart[$document_num];
#			$query_key=str_replace("'","''",$query_key);
#		        $ins = oci_parse($dbh, " insert into dwh_tmp_preresult (document_num ,query_key , tmpresult_date,patient_num,user_num,datamart_num,document_origin_code,document_date) values ($document_num,'$query_key',sysdate,'$patient_num','$user_num','$datamart_num','$document_origin_code',to_date('$document_date','DD/MM/YYYY HH24:MI')) ");   
#		        oci_execute($ins);
#		}
#		unset($tableau_final_patient_num);
#		unset($tableau_final_document_origin_code);
#		unset($tableau_final_date_document);
#		$memory_get_usage=memory_get_usage();
#		print "memory_get_usage : $memory_get_usage\n";
#	}
#}
#
#foreach ($tableau_final_query_key as $document_num => $query_key) {
#	$patient_num=$tableau_final_patient_num[$document_num];
#	//$user_num=$tableau_final_user_num[$document_num];
#	//$datamart_num=$tableau_final_num_datamart[$document_num];
#	$document_origin_code=$tableau_final_document_origin_code[$document_num];
#	$document_date=$tableau_final_date_document[$document_num];
#	$query_key=str_replace("'","''",$query_key);
#        $ins = oci_parse($dbh, " insert into dwh_tmp_preresult (document_num ,query_key , tmpresult_date,patient_num,user_num,datamart_num,document_origin_code,document_date) values ($document_num,'$query_key',sysdate,'$patient_num','$user_num','$datamart_num','$document_origin_code',to_date('$document_date','DD/MM/YYYY HH24:MI')) ");   
#        oci_execute($ins);
#}
#
#
#$nb_patient=count($tableau_patient_num);
#print "nb_patient : $nb_patient\n\n";
#
#$upd = oci_parse($dbh,"update  dwh_tmp_query set count_patient=$nb_patient  where query_key='$query_key_arg' and user_num=$user_num and datamart_num=$datamart_num ");   
#oci_execute($upd);
#
#print "update  dwh_tmp_query set count_patient=$nb_patient  where query_key='$query_key_arg' and user_num=$user_num and datamart_num=$datamart_num \n\n";
#if ($nb_patient>0) {
#	$tab_requete=explode(";",$query_key_arg);
#	$requete_ft=$tab_requete[0];
#	
#	$sel = oci_parse($dbh,"select count(*) nbmot from dwh_thesaurus_corrortho where term=lower('$requete_ft')");   
#	oci_execute($sel);
#	$r = oci_fetch_array($sel, OCI_ASSOC);
#	$nbmot=$r['NBMOT'];
#	if ($nbmot==0) {
#		$sel = oci_parse($dbh,"insert into dwh_thesaurus_corrortho values (lower('$requete_ft'))");   
#		oci_execute($sel);
#	}
#}
#print "test 2 \n\n";
#
#print "test 3 \n\n";
#
#
#$upd = oci_parse($dbh,"update  dwh_tmp_query set status_calculate=1  where query_key='$query_key_arg' and user_num=$user_num and datamart_num=$datamart_num ");   
#oci_execute($upd);
#
#print "update  dwh_tmp_query set status_calculate=1  where query_key='$query_key_arg' and user_num=$user_num and datamart_num=$datamart_num  \n\n";


?>