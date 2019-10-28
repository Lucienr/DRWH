<?




$req="select table_name from all_tables where table_name ='DWH_ADMIN_CGU' ";
$sel = oci_parse($dbh_etl,$req);
oci_execute($sel);
$ligne = oci_fetch_array($sel);
$verif_cgu = $ligne['TABLE_NAME'];
if ($verif_cgu=='') {
	$req="create table DWH_ADMIN_CGU (cgu_num int, cgu_text clob,cgu_date date,published int,published_date date,unpublished_date date, primary key (cgu_num))  TABLESPACE TS_DWH ";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	
	$req="GRANT DELETE, INSERT, SELECT, UPDATE ON DWH.DWH_ADMIN_CGU TO DWH_APP ";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	
	$req="CREATE SYNONYM DWH_ADMIN_CGU FOR DWH.DWH_ADMIN_CGU";
	$sel = oci_parse($dbh,$req);
	oci_execute($sel);
}

$req="select table_name from all_tables where table_name ='DWH_ADMIN_CGU_USER' ";
$sel = oci_parse($dbh_etl,$req);
oci_execute($sel);
$ligne = oci_fetch_array($sel);
$verif_cgu = $ligne['TABLE_NAME'];
if ($verif_cgu=='') {
	$req="create table DWH_ADMIN_CGU_USER (cgu_num int, user_num int,date_signature date, foreign key (cgu_num) references DWH_ADMIN_CGU (cgu_num) on delete cascade) TABLESPACE TS_DWH ";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	
	$req="GRANT DELETE, INSERT, SELECT, UPDATE ON DWH.DWH_ADMIN_CGU_USER TO DWH_APP ";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	
	$req="CREATE SYNONYM DWH_ADMIN_CGU_USER FOR DWH.DWH_ADMIN_CGU_USER";
	$sel = oci_parse($dbh,$req);
	oci_execute($sel);
}


$req="select table_name from all_tables where table_name ='DWH_PATIENT_OPPOSITION' ";
$sel = oci_parse($dbh_etl,$req);
oci_execute($sel);
$ligne = oci_fetch_array($sel);
$verif_opposition = $ligne['TABLE_NAME'];
if ($verif_opposition=='') {
	$req="create table  DWH_PATIENT_OPPOSITION (hospital_patient_id varchar(100), origin_patient_id varchar(40), patient_num int,opposition_date date)  ";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
}


$sel = oci_parse($dbh_etl, "select count(*) verif from user_tab_columns where table_name='DWH_PROCESS' and column_name ='CATEGORY_PROCESS'  ");   
oci_execute($sel);
$r = oci_fetch_array($sel, OCI_ASSOC);
$verif=$r['VERIF'];
if ($verif==0) {
	$sel = oci_parse($dbh_etl, "alter table DWH_process add category_process varchar(4000)");   
	oci_execute($sel);
}


$sel = oci_parse($dbh_etl, "select count(*) verif from user_tab_columns where table_name='DWH_PROCESS' and column_name ='TYPE_RESULT'  ");   
oci_execute($sel);
$r = oci_fetch_array($sel, OCI_ASSOC);
$verif=$r['VERIF'];
if ($verif==0) {
	$sel = oci_parse($dbh_etl, "alter table DWH_process add TYPE_RESULT varchar(30)");   
	oci_execute($sel);
}

$sel = oci_parse($dbh_etl, "select DATA_TYPE from user_tab_columns where table_name='DWH_PROCESS' and column_name ='PROCESS_NUM'  ");   
oci_execute($sel);
$r = oci_fetch_array($sel, OCI_ASSOC);
$DATA_TYPE=$r['DATA_TYPE'];
if ($DATA_TYPE!='NUMBER') {
	$sel = oci_parse($dbh_etl, "alter table DWH_process add process_num_int int");   
	oci_execute($sel);
	$sel = oci_parse($dbh_etl, "update DWH_process set process_num_int=dwh_seq.nextval");   
	oci_execute($sel);
	$sel = oci_parse($dbh_etl, "alter table DWH_process rename column process_num to process_num_old");   
	oci_execute($sel);
	$sel = oci_parse($dbh_etl, "alter table DWH_process rename column process_num_int to process_num");   
	oci_execute($sel);
}

?>