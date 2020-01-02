<?

$chemin="/var/www/xxxx/xxx";

include_once "$chemin/parametrage.php";
include_once "$chemin/connexion_bdd.php";

$option_sync_index='non';

$truncate = oci_parse($dbh, "delete from  dwh_tmp_query");   
oci_execute($truncate);

$truncate = oci_parse($dbh, "delete from  dwh_process where process_end_date<sysdate or process_end_date is null");   
oci_execute($truncate);

$truncate = oci_parse($dbh, "delete from dwh_process_patient where process_num not in (select process_num from dwh_process) ");   
oci_execute($truncate);

// we delete notification associated to deleted process
$truncate = oci_parse($dbh, " delete from dwh_notification where notification_type='process' and shared_element_num not in (select process_num from dwh_process)");   
oci_execute($truncate);

$truncate = oci_parse($dbh, "delete from dwh_datamart where temporary_status=1 and end_date<sysdate");  // on delete cascade for  dwh_datamart_result
oci_execute($truncate);

$sel = oci_parse($dbh,"select user_num from dwh_user");   
oci_execute($sel);
while ( $r = oci_fetch_array($sel, OCI_ASSOC)) {
	$user_num=$r['USER_NUM'];
	$truncate = oci_parse($dbh, "drop table DWH_TMP_PRERESULT_$user_num ");   
	oci_execute($truncate);
	$truncate = oci_parse($dbh, "drop table DWH_TMP_RESULT_$user_num ");   
	oci_execute($truncate);
	$truncate = oci_parse($dbh, "drop table DWH_TMP_RESULTALL_$user_num ");   
	oci_execute($truncate);
}

if ($CHEMIN_GLOBAL_LOG!='') {
	system("rm $CHEMIN_GLOBAL_LOG/*.txt");
}

if ($CHEMIN_GLOBAL_UPLOAD!='') {
	system("rm $CHEMIN_GLOBAL_UPLOAD/*.js");
	system("rm $CHEMIN_GLOBAL_UPLOAD/*.png");
	system("rm $CHEMIN_GLOBAL_UPLOAD/*.html");
	system("rm $CHEMIN_GLOBAL_UPLOAD/*.map");
	system("rm $CHEMIN_GLOBAL_UPLOAD/*.dot");
	system("rm $CHEMIN_GLOBAL_UPLOAD/*.txt");
	system("rm $CHEMIN_GLOBAL_UPLOAD/*.csv");
	system("rm $CHEMIN_GLOBAL_UPLOAD/*.json");
}

if ($CHEMIN_GLOBAL!='') {
	system("rm $CHEMIN_GLOBAL/timeline/xml/*.xml");
}

if ($option_sync_index=='ok') {
	$cridx=oci_parse($dbh_etl,"begin ctx_ddl.sync_index('DWH_TEXT_IDX', '300M');end;");
	oci_execute($cridx);
	oci_close($dbh);
	
	$cridx=oci_parse($dbh_etl,"begin ctx_ddl.sync_index('DWH_TEXT_ENRICH_IDX', '300M'); end;");
	oci_execute($cridx);
	oci_close($dbh);
	
	$req = "begin ctx_ddl.sync_index('DWH_TEXT_TITLE_IDX', '300M'); end; "; 
	$stmt=oci_parse($dbh_etl,$req);
	oci_execute($stmt) || die ("erreur index : $req\n");
	
	$req = "begin ctx_ddl.sync_index('DWH_THESAURUS_DATA_DESCRI', '300M'); end; "; 
	$stmt=oci_parse($dbh_etl,$req);
	oci_execute($stmt) || die ("erreur index : $req\n");
	
	
	$req = "begin ctx_ddl.sync_index('DWH_THESAURUS_ENRSEM_STR', '300M'); end; "; 
	$stmt=oci_parse($dbh_etl,$req);
	oci_execute($stmt) || die ("erreur index : $req\n");
	
	
	$req = "begin ctx_ddl.sync_index('DWH_THESAURUS_TAL_IDX', '300M'); end; "; 
	$stmt=oci_parse($dbh_etl,$req);
	oci_execute($stmt) || die ("erreur index : $req\n");
}
?>