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


function getmicrotime() {
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
}
function benchmark ( $nom_etape )
{
	global $etape_prec;
	$temps_ecoule = ($etape_prec) ? round((getmicrotime() - $etape_prec)*1000) : 0;
	$retour = $nom_etape . ' : ' . $temps_ecoule . 'ms \n';
	$etape_prec = getmicrotime();
	return $retour;
}




$user_num=$argv[1];
$datamart_num=$argv[2];
$query_key_arg=$argv[3];

$sel = oci_parse($dbh, "select table_name from all_tables where table_name='DWH_TMP_PRERESULT_$user_num'  ");   
oci_execute($sel);
$r = oci_fetch_array($sel, OCI_ASSOC);
$table_name_verif=$r['TABLE_NAME'];
if ($table_name_verif=='') {
	$sel = oci_parse($dbh, "create table DWH_TMP_PRERESULT_$user_num 
				(DOCUMENT_NUM INTEGER,
				QUERY_KEY VARCHAR(4000),
				TMPRESULT_DATE DATE,
				USER_NUM INTEGER,
				DATAMART_NUM INTEGER,
				DOCUMENT_ORIGIN_CODE VARCHAR(50),
				PATIENT_NUM  INTEGER,
				DOCUMENT_DATE  DATE,
				OBJECT_TYPE VARCHAR(50)
				) NOLOGGING ");   
	oci_execute($sel);
}


$tableau_patient_num_json=array();

print "query_key_arg : $query_key_arg\n\n";
print "datamart_num : $datamart_num\n\n";
print "select sql_clob from dwh_tmp_query where query_key='$query_key_arg' and user_num=$user_num and datamart_num=$datamart_num\n";
$sel = oci_parse($dbh, "select sql_clob from dwh_tmp_query where query_key='$query_key_arg' and user_num=$user_num and datamart_num=$datamart_num ");   
oci_execute($sel);
$r = oci_fetch_array($sel, OCI_ASSOC);
$sql='';
if ($r['SQL_CLOB']!='') {
	$sql=$r['SQL_CLOB']->load();
}
print "sql : $sql\n\n";

$nb_doc=0;
$tableau_final_query_key=array();
$tableau_patient_num=array();

$req_upd="update  dwh_tmp_query set count_patient=:nb_patient  where query_key='$query_key_arg' and user_num=$user_num and datamart_num=$datamart_num ";
$upd = oci_parse($dbh,$req_upd);   

$sel = oci_parse($dbh,"$sql");   
oci_execute($sel);
print "execute  done\n";
//$nb=oci_fetch_all($sel,$res, 0, -1, OCI_FETCHSTATEMENT_BY_ROW); 
//print " oci_fetch_all done\n";
//foreach ($res as $r) {
while ( $r = oci_fetch_array($sel, OCI_ASSOC+OCI_RETURN_NULLS)) {
	$document_num=$r['DOCUMENT_NUM'];
	$query_key=$r['QUERY_KEY'];
	$query_date=$r['QUERY_DATE'];
	$patient_num=$r['PATIENT_NUM'];
	$datamart_num=$r['DATAMART_NUM'];
	$document_origin_code=$r['DOCUMENT_ORIGIN_CODE'];
	$document_date=$r['DOCUMENT_DATE'];
	$object_type=$r['OBJECT_TYPE'];
	$nb_doc++;
	
	$tableau_patient_num_json[$patient_num].="{\"document_num\":\"$document_num\",\"query_key\":\"$query_key\",\"document_origin_code\":\"$document_origin_code\",\"document_date\":\"$document_date\",\"object_type\":\"$object_type\"},"; 
	if ($nb_doc==1000) {
		$nb_doc=0;
		$nb_patient=count($tableau_patient_num_json);
		oci_bind_by_name($upd, ':nb_patient', $nb_patient);
		$res = oci_execute($upd);  
	}
}


#$nb_patient=count($tableau_patient_num);
#$tableau_patient_num=array();

$nb_patient=count($tableau_patient_num_json);
print "nb_patient : $nb_patient\n\n";

$upd = oci_parse($dbh,"update  dwh_tmp_query set count_patient=$nb_patient  where query_key='$query_key_arg' and user_num=$user_num and datamart_num=$datamart_num ");   
oci_execute($upd);

#foreach ($tableau_final_query_key as $document_num => $query_key) {
#	$patient_num=$tableau_final_patient_num[$document_num];
#	$document_origin_code=$tableau_final_document_origin_code[$document_num];
#	$document_date=$tableau_final_date_document[$document_num];
#	$query_key=str_replace("'","''",$query_key);
#        $ins = oci_parse($dbh, " insert into DWH_TMP_PRERESULT_$user_num  (document_num ,query_key , tmpresult_date,patient_num,user_num,datamart_num,document_origin_code,document_date) values ($document_num,'$query_key',sysdate,'$patient_num','$user_num','$datamart_num','$document_origin_code',to_date('$document_date','DD/MM/YYYY HH24:MI')) ");   
#        oci_execute($ins);
#}

$nb_doc=0;
$ins = oci_parse($dbh, " insert /*+ APPEND NOLOGGING */ into DWH_TMP_PRERESULT_$user_num  (document_num ,query_key , tmpresult_date,patient_num,user_num,datamart_num,document_origin_code,document_date,object_type) values (:document_num,:query_key,sysdate,:patient_num,:user_num,:datamart_num,:document_origin_code,to_date(:document_date,'DD/MM/YYYY HH24:MI'),:object_type) ");   

foreach ($tableau_patient_num_json as $patient_num => $json) {
	$json="{\"list_documents\":[".substr($json,0,-1)."]}";
	$parsed_json = json_decode($json);
	foreach ($parsed_json->{'list_documents'} as $document) {
		$nb_doc++;
		$document_num= $document->{'document_num'};
		$document_origin_code= $document->{'document_origin_code'};
		$document_date= $document->{'document_date'};
		$query_key= $document->{'query_key'};
		$object_type= $document->{'object_type'};
		//$query_key=str_replace("'","''",$query_key);
	        //$ins = oci_parse($dbh, " insert into DWH_TMP_PRERESULT_$user_num  (document_num ,query_key , tmpresult_date,patient_num,user_num,datamart_num,document_origin_code,document_date,object_type) values ($document_num,'$query_key',sysdate,'$patient_num','$user_num','$datamart_num','$document_origin_code',to_date('$document_date','DD/MM/YYYY HH24:MI'),'$object_type') ");   
	        //oci_execute($ins);
	        
		oci_bind_by_name($ins, ':document_num', $document_num);
		oci_bind_by_name($ins, ':query_key', $query_key, 4000, SQLT_LNG);
		oci_bind_by_name($ins, ':patient_num', $patient_num);
		oci_bind_by_name($ins, ':user_num', $user_num);
		oci_bind_by_name($ins, ':datamart_num', $datamart_num);
		oci_bind_by_name($ins, ':document_origin_code', $document_origin_code, 500, SQLT_LNG);
		oci_bind_by_name($ins, ':document_date', $document_date, 500, SQLT_LNG);
		oci_bind_by_name($ins, ':object_type', $object_type, 500, SQLT_LNG);
		$res = oci_execute($ins, OCI_NO_AUTO_COMMIT);  
		
		if ($nb_doc==1000) {
			$nb_doc=0;
			oci_commit($dbh);
		
		}
	}
}
//, OCI_NO_AUTO_COMMIT
oci_commit($dbh);



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

$sel = oci_parse($dbh, "select index_name from all_indexes where index_name='DWH_TPR_I_$user_num'  ");   
oci_execute($sel);
$r = oci_fetch_array($sel, OCI_ASSOC);
$index_name_verif=$r['INDEX_NAME'];
if ($index_name_verif!='') {
	//$sel = oci_parse($dbh, "drop index DWH_TMP_PRERESULT_I_$user_num ");   
	//oci_execute($sel);
}else {
	$sel = oci_parse($dbh, "create index DWH_TPR_I_$user_num  on DWH_TMP_PRERESULT_$user_num  (QUERY_KEY , datamart_num)");   
	oci_execute($sel);
}
$sel = oci_parse($dbh, "select index_name from all_indexes where index_name='DWH_TPR_DOC_$user_num'  ");   
oci_execute($sel);
$r = oci_fetch_array($sel, OCI_ASSOC);
$index_name_verif=$r['INDEX_NAME'];
if ($index_name_verif!='') {
	//$sel = oci_parse($dbh, "drop index DWH_TMP_PRERESULT_DOC_$user_num ");   
	//oci_execute($sel);
} else {
	$sel = oci_parse($dbh, "create index DWH_TPR_DOC_$user_num  on DWH_TMP_PRERESULT_$user_num  (document_num)");   
	oci_execute($sel);
}



oci_close ($dbh);
oci_close ($dbh_etl);
?>