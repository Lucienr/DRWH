<?


$req="select index_name from all_indexes where index_name ='DWH_ENRSEM_PCC' ";
$sel = oci_parse($dbh_etl,$req);
oci_execute($sel);
$ligne = oci_fetch_array($sel);
$verif_index_name = $ligne['INDEX_NAME'];
if ($verif_index_name=='') {
	$req="create index dwh_enrsem_pcc on dwh_enrsem ( patient_num,certainty,context) tablespace ts_idx";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
}


$req="select table_name from all_tables where table_name ='DWH_ADMIN_PARAMETERS' ";
$sel = oci_parse($dbh_etl,$req);
oci_execute($sel);
$ligne = oci_fetch_array($sel);
$verif_DWH_ADMIN_PARAMETERS = $ligne['TABLE_NAME'];
if ($verif_DWH_ADMIN_PARAMETERS=='') {
	$req="CREATE TABLE DWH_ADMIN_PARAMETERS
		(
		 contact clob
		)
		TABLESPACE TS_DWH
		LOGGING 
		MONITORING";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	
	$req="GRANT DELETE, INSERT, SELECT, UPDATE ON DWH.DWH_ADMIN_PARAMETERS TO DWH_APP ";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	
	$req="CREATE SYNONYM DWH_ADMIN_PARAMETERS FOR DWH.DWH_ADMIN_PARAMETERS";
	$sel = oci_parse($dbh,$req);
	oci_execute($sel);
}



$req="select table_name from all_tables where table_name ='DWH_ADMIN_ACTU' ";
$sel = oci_parse($dbh_etl,$req);
oci_execute($sel);
$ligne = oci_fetch_array($sel);
$verif_DWH_ADMIN_ACTU = $ligne['TABLE_NAME'];
if ($verif_DWH_ADMIN_ACTU=='') {
	$req="CREATE TABLE DWH_ADMIN_ACTU
		(
		 actu_num int, 
		 actu_text clob,
		 actu_date date,
		 published int,
		 published_date date,
		 alert int, 
		 primary key (actu_num)
		)
		TABLESPACE TS_DWH
		LOGGING 
		MONITORING";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	
	$req="GRANT DELETE, INSERT, SELECT, UPDATE ON DWH.DWH_ADMIN_ACTU TO DWH_APP ";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	
	$req="CREATE SYNONYM DWH_ADMIN_ACTU FOR DWH.DWH_ADMIN_ACTU";
	$sel = oci_parse($dbh,$req);
	oci_execute($sel);
}


$sel = oci_parse($dbh_etl, "select count(*) verif from user_tab_columns where table_name='DWH_THESAURUS_DEPARTMENT' and column_name ='DEPARTMENT_MASTER'  ");   
oci_execute($sel);
$r = oci_fetch_array($sel, OCI_ASSOC);
$verif=$r['VERIF'];
if ($verif==0) {
	$sel = oci_parse($dbh_etl, "alter table DWH_THESAURUS_DEPARTMENT add DEPARTMENT_MASTER int");   
	oci_execute($sel);
}


$req="select table_name from all_tables where table_name ='DWH_USER_CONNEXION' ";
$sel = oci_parse($dbh_etl,$req);
oci_execute($sel);
$ligne = oci_fetch_array($sel);
$verif_ecrf = $ligne['TABLE_NAME'];
if ($verif_ecrf=='') {
	$req="CREATE TABLE DWH_USER_CONNEXION
(
  LOGIN             VARCHAR2(30 CHAR),
  LAST_UPDATE_DATE  DATE,
  DATABASE          VARCHAR2(30 CHAR),
  USER_NAME         VARCHAR2(100 CHAR)
)
TABLESPACE TS_DWH
LOGGING 
MONITORING";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	
	$req="GRANT DELETE, INSERT, SELECT, UPDATE ON DWH.DWH_USER_CONNEXION TO DWH_APP ";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	
	$req="CREATE SYNONYM DWH_USER_CONNEXION FOR DWH.DWH_USER_CONNEXION";
	$sel = oci_parse($dbh,$req);
	oci_execute($sel);

}



$req="select table_name from all_tables where table_name ='DWH_ECRF' ";
$sel = oci_parse($dbh_etl,$req);
oci_execute($sel);
$ligne = oci_fetch_array($sel);
$verif_ecrf = $ligne['TABLE_NAME'];
if ($verif_ecrf=='') {
	$req="CREATE TABLE DWH_ECRF
(
  ECRF_NUM          INTEGER,
  USER_NUM          INTEGER,
  TITLE_ECRF        VARCHAR2(4000 CHAR),
  DESCRIPTION_ECRF  VARCHAR2(4000 CHAR),
  ECRF_DATE         DATE,
  TOKEN_ECRF        VARCHAR2(200 CHAR),
  URL_ECRF            VARCHAR(400 CHAR),
  ECRF_START_DATE     DATE,
  ECRF_END_DATE       DATE, 
  ECRF_START_AGE      FLOAT,
  ECRF_END_AGE        FLOAT,
  PRIMARY KEY  (ECRF_NUM)
)
TABLESPACE TS_DWH
LOGGING 
MONITORING";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	
	$req="GRANT DELETE, INSERT, SELECT, UPDATE ON DWH.DWH_ECRF TO DWH_APP ";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	
	$req="CREATE SYNONYM DWH_ECRF FOR DWH.DWH_ECRF";
	$sel = oci_parse($dbh,$req);
	oci_execute($sel);





	$req="CREATE TABLE DWH_ECRF_ITEM
(
  ECRF_ITEM_NUM  INTEGER,
  ECRF_NUM       INTEGER,
  ITEM_STR       VARCHAR2(4000 CHAR),
  ITEM_TYPE      VARCHAR2(200 CHAR),
  ITEM_LIST      VARCHAR2(4000 CHAR),
  ITEM_EXT_NAME      VARCHAR2(200 CHAR),
  ITEM_EXT_CODE     VARCHAR2(4000 CHAR),
  REGEXP         VARCHAR2(500 CHAR),
  ITEM_LOCAL_CODE    VARCHAR2(4000 CHAR),
  REGEXP_INDEX   VARCHAR2(10 CHAR),
  PERIOD         VARCHAR2(50 CHAR),
  ITEM_ORDER     INTEGER,
  PRIMARY KEY
  (ECRF_ITEM_NUM),
  FOREIGN KEY (ECRF_NUM) 
  REFERENCES DWH_ECRF (ECRF_NUM)
  ON DELETE CASCADE
)
TABLESPACE TS_DWH
LOGGING 
MONITORING";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	
	$req="GRANT DELETE, INSERT, SELECT, UPDATE ON DWH.DWH_ECRF_ITEM TO DWH_APP ";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	
	$req="CREATE SYNONYM DWH_ECRF_ITEM FOR DWH.DWH_ECRF_ITEM";
	$sel = oci_parse($dbh,$req);
	oci_execute($sel);


	$req="CREATE TABLE DWH_ECRF_ANSWER
(
  ECRF_ITEM_NUM       INTEGER,
  ECRF_NUM            INTEGER,
  PATIENT_NUM         INTEGER,
  USER_NUM            INTEGER,
  USER_VALUE          VARCHAR2(4000 CHAR),
  AUTOMATED_VALUE     VARCHAR2(4000 CHAR),
  ECRF_ITEM_EXTRACT  CLOB,
  USER_VALUE_DATE          DATE,
  AUTOMATED_VALUE_DATE     DATE,
  USER_VALIDATION          INTEGER, 
  USER_VALIDATION_DATE     DATE,
 foreign key (ECRF_ITEM_NUM) references  DWH_ECRF_ITEM (ECRF_ITEM_NUM) on delete cascade
)
TABLESPACE TS_DWH
LOGGING 
MONITORING";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	
	$req="GRANT DELETE, INSERT, SELECT, UPDATE ON DWH.DWH_ECRF_ANSWER TO DWH_APP ";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	
	$req="CREATE SYNONYM DWH_ECRF_ANSWER FOR DWH.DWH_ECRF_ANSWER";
	$sel = oci_parse($dbh,$req);
	oci_execute($sel);



	$req="CREATE TABLE DWH_ECRF_SUB_ITEM
(
  ECRF_SUB_ITEM_NUM    INTEGER,
  ECRF_ITEM_NUM        INTEGER,
  SUB_ITEM_LOCAL_STR   VARCHAR2(4000 CHAR),
  SUB_ITEM_LOCAL_CODE  VARCHAR2(4000 CHAR),
  SUB_ITEM_EXT_CODE    VARCHAR2(4000 CHAR),
  SUB_ITEM_REGEXP      VARCHAR2(4000 CHAR),
  PRIMARY KEY
  (ECRF_SUB_ITEM_NUM),
  FOREIGN KEY (ECRF_ITEM_NUM) 
  REFERENCES DWH_ECRF_ITEM (ECRF_ITEM_NUM)
  ON DELETE CASCADE
)
TABLESPACE TS_DWH
LOGGING 
MONITORING";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	
	$req="GRANT DELETE, INSERT, SELECT, UPDATE ON DWH.DWH_ECRF_SUB_ITEM TO DWH_APP ";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	
	$req="CREATE SYNONYM DWH_ECRF_SUB_ITEM FOR DWH.DWH_ECRF_SUB_ITEM";
	$sel = oci_parse($dbh,$req);
	oci_execute($sel);



	$req="CREATE TABLE DWH_ECRF_USER_RIGHT
(
  ECRF_NUM  INTEGER,
  USER_NUM  INTEGER,
  RIGHT     VARCHAR2(50 CHAR),
  FOREIGN KEY (ECRF_NUM) 
  REFERENCES DWH_ECRF (ECRF_NUM)
  ON DELETE CASCADE
)
TABLESPACE TS_DWH
LOGGING 
MONITORING";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	
	$req="GRANT DELETE, INSERT, SELECT, UPDATE ON DWH.DWH_ECRF_USER_RIGHT TO DWH_APP ";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	
	$req="CREATE SYNONYM DWH_ECRF_USER_RIGHT FOR DWH.DWH_ECRF_USER_RIGHT";
	$sel = oci_parse($dbh,$req);
	oci_execute($sel);


	$req="CREATE TABLE DWH_USER_ECRF
( 
	USER_NUM INT, 
	ECRF_NUM INT, 
	TOKEN_ECRF VARCHAR2 (50) 
) 
TABLESPACE TS_DWH";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	
	$req="GRANT DELETE, INSERT, SELECT, UPDATE ON DWH.DWH_USER_ECRF TO DWH_APP ";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	
	$req="CREATE SYNONYM DWH_USER_ECRF FOR DWH.DWH_USER_ECRF";
	$sel = oci_parse($dbh,$req);
	oci_execute($sel);

}



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