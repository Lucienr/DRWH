<?
	 
	
$sel = oci_parse($dbh_etl, "select count(*) verif from user_tab_columns where table_name='DWH_PROCESS' and column_name ='PROCESS_START_DATE'  ");   
oci_execute($sel);
$r = oci_fetch_array($sel, OCI_ASSOC);
$verif=$r['VERIF'];
if ($verif==0) {
	$sel = oci_parse($dbh_etl, "alter table DWH_PROCESS add PROCESS_START_DATE date ");   
	oci_execute($sel);
	
	$req="select index_name from all_indexes where index_name ='DWH_DOCUMENT_DOC_TYPE'  and  owner=upper('$USER_DB_DBH_ETL') ";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	$ligne = oci_fetch_array($sel);
	$verif_index_name = $ligne['INDEX_NAME'];
	if ($verif_index_name=='') {
		$req="create index dwh_document_doc_type on dwh_document ( CONVERT (document_type, 'US7ASCII')) tablespace ts_idx";
		$sel = oci_parse($dbh_etl,$req);
		oci_execute($sel);
	}
	
	$sel = oci_parse($dbh_etl, "select count(*) verif from user_tab_columns where table_name='DWH_COHORT_RESULT' and column_name ='COHORT_RESULT_NUM'  ");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	$verif=$r['VERIF'];
	if ($verif==0) {
		$sel = oci_parse($dbh_etl, "alter table DWH_COHORT_RESULT add COHORT_RESULT_NUM int ");   
		oci_execute($sel);
	}
	
	$sel = oci_parse($dbh_etl, "select count(*) verif from all_tables where table_name='DWH_BACKUP_PATIENT_MERGE'  and  owner=upper('$USER_DB_DBH_ETL') ");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	$verif=$r['VERIF'];
	if ($verif==0) {
		$req="CREATE TABLE DWH_BACKUP_PATIENT_MERGE
			(
			PATIENT_NUM_DELETED  INT,
			PATIENT_NUM_NEW      INT,
			KEY_NAME             VARCHAR(30),
			KEY_VALUE            INT,
			DATE_MERGE           DATE,
			COMMENTARY             VARCHAR(4000)
			)
			TABLESPACE TS_DWH
			LOGGING 
			MONITORING";
		$sel = oci_parse($dbh_etl,$req);
		oci_execute($sel);
		
		if ($USER_DB_DBH_ETL!=$USER_DB_DBH) {
			$req="GRANT DELETE, INSERT, SELECT, UPDATE ON $USER_DB_DBH_ETL.DWH_BACKUP_PATIENT_MERGE TO $USER_DB_DBH ";
			$sel = oci_parse($dbh_etl,$req);
			oci_execute($sel);
			
			$req="CREATE SYNONYM DWH_BACKUP_PATIENT_MERGE FOR $USER_DB_DBH_ETL.DWH_BACKUP_PATIENT_MERGE";
			$sel = oci_parse($dbh,$req);
			oci_execute($sel);
		}
	}
	
	$sel = oci_parse($dbh_etl, "select count(*) verif from all_tables where table_name='DWH_BACKUP_PATIENT_MERGEIPP'  and owner=upper('$USER_DB_DBH_ETL')");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	$verif=$r['VERIF'];
	if ($verif==0) {
		$req="CREATE TABLE DWH_BACKUP_PATIENT_MERGEIPP
			(
			PATIENT_NUM_DELETED  INT,
			PATIENT_NUM_NEW      INT,
			HOSPITAL_PATIENT_ID  VARCHAR(100),
			ORIGIN_PATIENT_ID    VARCHAR(100),
			DATE_MERGE           DATE,
			COMMENTARY             VARCHAR(4000)
			)
			TABLESPACE TS_DWH
			LOGGING 
			MONITORING";
		$sel = oci_parse($dbh_etl,$req);
		oci_execute($sel);
		
		if ($USER_DB_DBH_ETL!=$USER_DB_DBH) {
			$req="GRANT DELETE, INSERT, SELECT, UPDATE ON $USER_DB_DBH_ETL.DWH_BACKUP_PATIENT_MERGEIPP TO $USER_DB_DBH ";
			$sel = oci_parse($dbh_etl,$req);
			oci_execute($sel);
			
			$req="CREATE SYNONYM DWH_BACKUP_PATIENT_MERGEIPP FOR $USER_DB_DBH_ETL.DWH_BACKUP_PATIENT_MERGEIPP";
			$sel = oci_parse($dbh,$req);
			oci_execute($sel);
		}
	}
	
	$sel = oci_parse($dbh_etl, "select count(*) verif from user_tab_columns where table_name='DWH_ECRF_ITEM' and column_name ='DOCUMENT_SEARCH'  ");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	$verif=$r['VERIF'];
	if ($verif==0) {
		$sel = oci_parse($dbh_etl, "alter table DWH_ECRF_ITEM rename column ITEM_LIST TO DOCUMENT_SEARCH ");   
		oci_execute($sel);
	}
		
	$sel = oci_parse($dbh_etl, "select count(*) verif from user_tab_columns where table_name='DWH_ECRF_ANSWER' and column_name ='ECRF_ANSWER_NUM'  ");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	$verif=$r['VERIF'];
	if ($verif==0) {
		$sel = oci_parse($dbh_etl, "alter table DWH_ECRF_ANSWER add ECRF_ANSWER_NUM INT");   
		oci_execute($sel);
		$sel = oci_parse($dbh_etl, "update DWH_ECRF_ANSWER set ECRF_ANSWER_NUM=dwh_seq.nextval where ECRF_ANSWER_NUM is null");   
		oci_execute($sel);
	}
	
	$sel = oci_parse($dbh_etl, "select count(*) verif from all_tables where table_name='DWH_ECRF_PATIENT_EVENT'  and owner=upper('$USER_DB_DBH_ETL')");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	$verif=$r['VERIF'];
	if ($verif==0) {
		$req="CREATE TABLE DWH_ECRF_PATIENT_EVENT
			(
			ECRF_PATIENT_EVENT_NUM  INT,
			ECRF_NUM            INT,
			PATIENT_NUM         INT,
			USER_NUM            INT,
			EVENT_ID            INT,
			DATE_PATIENT_ECRF   DATE,
			NB_DAYS_BEFORE      FLOAT,
			NB_DAYS_AFTER       FLOAT,
			PRIMARY KEY (ECRF_PATIENT_EVENT_NUM),
			FOREIGN KEY (ECRF_NUM) REFERENCES DWH_ECRF (ECRF_NUM)
			ON DELETE CASCADE
			)
			TABLESPACE TS_DWH
			LOGGING 
			MONITORING";
		$sel = oci_parse($dbh_etl,$req);
		oci_execute($sel);
		
		if ($USER_DB_DBH_ETL!=$USER_DB_DBH) {
			$req="GRANT DELETE, INSERT, SELECT, UPDATE ON $USER_DB_DBH_ETL.DWH_ECRF_PATIENT_EVENT TO $USER_DB_DBH ";
			$sel = oci_parse($dbh_etl,$req);
			oci_execute($sel);
			
			$req="CREATE SYNONYM DWH_ECRF_PATIENT_EVENT FOR $USER_DB_DBH_ETL.DWH_ECRF_PATIENT_EVENT";
			$sel = oci_parse($dbh,$req);
			oci_execute($sel);
		}
	}
		
	$sel = oci_parse($dbh_etl, "select count(*) verif from user_tab_columns where table_name='DWH_ECRF_ANSWER' and column_name ='ECRF_PATIENT_EVENT_NUM'  ");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	$verif=$r['VERIF'];
	if ($verif==0) {
		$sel = oci_parse($dbh_etl, "alter table DWH_ECRF_ANSWER add ECRF_PATIENT_EVENT_NUM INT");   
		oci_execute($sel);
	}

	$sel = oci_parse($dbh_etl, "select count(*) verif from user_tab_columns where table_name='DWH_PATIENT_MVT' and column_name ='ID_MVT_SOURCE'  ");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	$verif=$r['VERIF'];
	if ($verif==0) {
		$sel = oci_parse($dbh_etl, "alter table DWH_PATIENT_MVT add id_mvt_source  varchar(100)");   
		oci_execute($sel);
		$sel = oci_parse($dbh_etl, "drop view DWH_PATIENT_MVT_VIEW");   
		oci_execute($sel);
		$sel = oci_parse($dbh_etl, "create view DWH_PATIENT_MVT_VIEW as select * from DWH_PATIENT_MVT where patient_num not in (select patient_num from dwh_patient_opposition)");   
		oci_execute($sel);
		$sel = oci_parse($dbh_etl, "GRANT SELECT ON DWH.DWH_PATIENT_MVT_VIEW TO DWH_APP");   
		oci_execute($sel);
	}
			
	$sel = oci_parse($dbh_etl, "select count(*) verif from user_tab_columns where table_name='DWH_ADMIN_PARAMETERS' and column_name ='OPTION_PASSWORD_COMPLEX'  ");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	$verif=$r['VERIF'];
	if ($verif==0) {
		
		$req="select table_name from all_tables where table_name ='DWH_ADMIN_PARAMETERS'  and owner=upper('$USER_DB_DBH_ETL')";
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
			
			if ($USER_DB_DBH_ETL!=$USER_DB_DBH) {
				$req="GRANT DELETE, INSERT, SELECT, UPDATE ON $USER_DB_DBH_ETL.DWH_ADMIN_PARAMETERS TO $USER_DB_DBH ";
				$sel = oci_parse($dbh_etl,$req);
				oci_execute($sel);
				
				$req="CREATE SYNONYM DWH_ADMIN_PARAMETERS FOR $USER_DB_DBH_ETL.DWH_ADMIN_PARAMETERS";
				$sel = oci_parse($dbh,$req);
				oci_execute($sel);
			}
		}
		
		
		$sel = oci_parse($dbh_etl, "alter table DWH_ADMIN_PARAMETERS add OPTION_PASSWORD_COMPLEX int");   
		oci_execute($sel);
		$sel = oci_parse($dbh_etl, "alter table DWH_ADMIN_PARAMETERS add FORCE_MODIFY_PASSWORD int");   
		oci_execute($sel);
		$sel = oci_parse($dbh_etl, "alter table DWH_ADMIN_PARAMETERS add MODIFY_PASSWORD_AFTER_NB_DAYS int");   
		oci_execute($sel);
	}
	
	$sel = oci_parse($dbh_etl, "select count(*) verif from ALL_SEQUENCES where sequence_name='DWH_USER_SEQ'");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	$verif=$r['VERIF'];
	if ($verif==0) {
		$sel = oci_parse($dbh_etl, "select  max(user_num) as max_user_num from dwh_user ");   
		oci_execute($sel);
		$r = oci_fetch_array($sel, OCI_ASSOC);
		$max_user_num=$r['MAX_USER_NUM'];
		if ($max_user_num=='') {
			$max_user_num=1;
		} else {
			$max_user_num=$max_user_num+1;
		}
		if ($max_user_num>99999999) {
			$maxvalue=$max_user_num+100000000;
		} else {
			$maxvalue=99999999;
		}
		$sel = oci_parse($dbh_etl, "CREATE SEQUENCE DWH_USER_SEQ
			  START WITH $max_user_num
			  MAXVALUE $maxvalue
			  MINVALUE 1
			  NOCYCLE
			  CACHE 20
			  NOORDER");   
		oci_execute($sel);
		
		if ($USER_DB_DBH_ETL!=$USER_DB_DBH) {
			$sel = oci_parse($dbh_etl, "GRANT SELECT ON $USER_DB_DBH_ETL.DWH_USER_SEQ TO $USER_DB_DBH");   
			oci_execute($sel);
			
			$sel = oci_parse($dbh, "CREATE SYNONYM DWH_USER_SEQ FOR  $USER_DB_DBH_ETL.DWH_USER_SEQ");   
			oci_execute($sel);
		}
	}
		 
		 
		
	$sel = oci_parse($dbh_etl, "select count(*) verif from user_tab_columns where table_name='DWH_LOG_QUERY' and column_name ='PATIENT_NUM'  ");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	$verif=$r['VERIF'];
	if ($verif==0) {
		$sel = oci_parse($dbh_etl, "alter table DWH_LOG_QUERY add PATIENT_NUM int");   
		oci_execute($sel);
	}
		 
	$sel = oci_parse($dbh_etl, "select count(*) verif from user_tab_columns where table_name='DWH_USER' and column_name ='DEFAULT_PASSWORD'  ");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	$verif=$r['VERIF'];
	if ($verif==0) {
		$sel = oci_parse($dbh_etl, "alter table DWH_USER add DEFAULT_PASSWORD int");   
		oci_execute($sel);
	}
	
	
	$sel = oci_parse($dbh_etl, "select count(*) verif from user_tab_columns where table_name='DWH_USER' and column_name ='LAST_MODIF_PASSWORD_DATE'  ");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	$verif=$r['VERIF'];
	if ($verif==0) {
		$sel = oci_parse($dbh_etl, "alter table DWH_USER add LAST_MODIF_PASSWORD_DATE date");   
		oci_execute($sel);
	}
	
	$req="select table_name from all_tables where table_name ='DWH_VIRTUAL_PATIENT'  and owner=upper('$USER_DB_DBH_ETL')";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	$ligne = oci_fetch_array($sel);
	$verif_DWH_VIRTUAL_PATIENT = $ligne['TABLE_NAME'];
	if ($verif_DWH_VIRTUAL_PATIENT=='') {
		$req="CREATE TABLE DWH_VIRTUAL_PATIENT
			(
			 user_num int,
			 virtual_patient_num int,
			 patient_name varchar(100),
			 description varchar(4000),
			 date_creation date, 
			 shared int,
			 patient_record clob,
			 primary key (virtual_patient_num)
			)
			TABLESPACE TS_DWH
			LOGGING 
			MONITORING";
		$sel = oci_parse($dbh_etl,$req);
		oci_execute($sel);
		
		if ($USER_DB_DBH_ETL!=$USER_DB_DBH) {
			$req="GRANT DELETE, INSERT, SELECT, UPDATE ON $USER_DB_DBH_ETL.DWH_VIRTUAL_PATIENT TO $USER_DB_DBH ";
			$sel = oci_parse($dbh_etl,$req);
			oci_execute($sel);
			
			$req="CREATE SYNONYM DWH_VIRTUAL_PATIENT FOR $USER_DB_DBH_ETL.DWH_VIRTUAL_PATIENT";
			$sel = oci_parse($dbh,$req);
			oci_execute($sel);
		}
	}


	$sel = oci_parse($dbh_etl, "select count(*) verif from user_tab_columns where table_name='DWH_ECRF_ITEM' and column_name ='DOCUMENT_ORIGIN_CODE'  ");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	$verif=$r['VERIF'];
	if ($verif==0) {
		$sel = oci_parse($dbh_etl, "alter table DWH_ECRF_ITEM add DOCUMENT_ORIGIN_CODE varchar(200)");   
		oci_execute($sel);
	}
	
	 
	$sel = oci_parse($dbh_etl, "select count(*) verif from user_tab_columns where table_name='DWH_ECRF_ITEM' and column_name ='ECRF_FUNCTION'  ");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	$verif=$r['VERIF'];
	if ($verif==0) {
		$sel = oci_parse($dbh_etl, "alter table DWH_ECRF_ITEM add ECRF_FUNCTION varchar(200)");   
		oci_execute($sel);
	}
	
	 
	$req="select index_name from all_indexes where index_name ='DWH_ECRF_USER_RIGHT_I'  and  owner=upper('$USER_DB_DBH_ETL')";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	$ligne = oci_fetch_array($sel);
	$verif_index_name = $ligne['INDEX_NAME'];
	if ($verif_index_name=='') {
		$req="create index DWH_ECRF_USER_RIGHT_I on DWH_ECRF_USER_RIGHT ( user_num,right,ecrf_num) tablespace ts_idx";
		$sel = oci_parse($dbh_etl,$req);
		oci_execute($sel);
	}
	 
	$req="select index_name from all_indexes where index_name ='DWH_ENRSEM_PCC'  and  owner=upper('$USER_DB_DBH_ETL')";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	$ligne = oci_fetch_array($sel);
	$verif_index_name = $ligne['INDEX_NAME'];
	if ($verif_index_name=='') {
		$req="create index dwh_enrsem_pcc on dwh_enrsem ( patient_num,certainty,context) tablespace ts_idx";
		$sel = oci_parse($dbh_etl,$req);
		oci_execute($sel);
	}
	
	
	
	$req="select table_name from all_tables where table_name ='DWH_ADMIN_ACTU'  and owner=upper('$USER_DB_DBH_ETL')";
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
		
		if ($USER_DB_DBH_ETL!=$USER_DB_DBH) {
			$req="GRANT DELETE, INSERT, SELECT, UPDATE ON $USER_DB_DBH_ETL.DWH_ADMIN_ACTU TO $USER_DB_DBH ";
			$sel = oci_parse($dbh_etl,$req);
			oci_execute($sel);
			
			$req="CREATE SYNONYM DWH_ADMIN_ACTU FOR $USER_DB_DBH_ETL.DWH_ADMIN_ACTU";
			$sel = oci_parse($dbh,$req);
			oci_execute($sel);
		}
	}
	
	
	$sel = oci_parse($dbh_etl, "select count(*) verif from user_tab_columns where table_name='DWH_THESAURUS_DEPARTMENT' and column_name ='DEPARTMENT_MASTER'  ");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	$verif=$r['VERIF'];
	if ($verif==0) {
		$sel = oci_parse($dbh_etl, "alter table DWH_THESAURUS_DEPARTMENT add DEPARTMENT_MASTER int");   
		oci_execute($sel);
	}
	
	
	$req="select table_name from all_tables where table_name ='DWH_USER_CONNEXION'  and owner=upper('$USER_DB_DBH_ETL')";
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
		
		if ($USER_DB_DBH_ETL!=$USER_DB_DBH) {
			$req="GRANT DELETE, INSERT, SELECT, UPDATE ON $USER_DB_DBH_ETL.DWH_USER_CONNEXION TO $USER_DB_DBH ";
			$sel = oci_parse($dbh_etl,$req);
			oci_execute($sel);
			
			$req="CREATE SYNONYM DWH_USER_CONNEXION FOR $USER_DB_DBH_ETL.DWH_USER_CONNEXION";
			$sel = oci_parse($dbh,$req);
			oci_execute($sel);
		}
	
	}
	
	
	
	$req="select table_name from all_tables where table_name ='DWH_ECRF'  and owner=upper('$USER_DB_DBH_ETL')";
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
		
		if ($USER_DB_DBH_ETL!=$USER_DB_DBH) {
			$req="GRANT DELETE, INSERT, SELECT, UPDATE ON $USER_DB_DBH_ETL.DWH_ECRF TO $USER_DB_DBH ";
			$sel = oci_parse($dbh_etl,$req);
			oci_execute($sel);
			
			$req="CREATE SYNONYM DWH_ECRF FOR $USER_DB_DBH_ETL.DWH_ECRF";
			$sel = oci_parse($dbh,$req);
			oci_execute($sel);
		}
	}
	
	$req="select table_name from all_tables where table_name ='DWH_ECRF_ITEM'  and owner=upper('$USER_DB_DBH_ETL')";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	$ligne = oci_fetch_array($sel);
	$verif_ecrf = $ligne['TABLE_NAME'];
	if ($verif_ecrf=='') {
		$req="CREATE TABLE DWH_ECRF_ITEM
	(
	  ECRF_ITEM_NUM  INTEGER,
	  ECRF_NUM       INTEGER,
	  ITEM_STR       VARCHAR2(4000 CHAR),
	  ITEM_TYPE      VARCHAR2(200 CHAR),
	  DOCUMENT_SEARCH      VARCHAR2(4000 CHAR),
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
		
		if ($USER_DB_DBH_ETL!=$USER_DB_DBH) {
			$req="GRANT DELETE, INSERT, SELECT, UPDATE ON $USER_DB_DBH_ETL.DWH_ECRF_ITEM TO $USER_DB_DBH ";
			$sel = oci_parse($dbh_etl,$req);
			oci_execute($sel);
			
			$req="CREATE SYNONYM DWH_ECRF_ITEM FOR $USER_DB_DBH_ETL.DWH_ECRF_ITEM";
			$sel = oci_parse($dbh,$req);
			oci_execute($sel);
		}
	
	
	}
	
	$req="select table_name from all_tables where table_name ='DWH_ECRF_ANSWER'  and owner=upper('$USER_DB_DBH_ETL')";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	$ligne = oci_fetch_array($sel);
	$verif_ecrf = $ligne['TABLE_NAME'];
	if ($verif_ecrf=='') {
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
		
		if ($USER_DB_DBH_ETL!=$USER_DB_DBH) {
			$req="GRANT DELETE, INSERT, SELECT, UPDATE ON $USER_DB_DBH_ETL.DWH_ECRF_ANSWER TO $USER_DB_DBH ";
			$sel = oci_parse($dbh_etl,$req);
			oci_execute($sel);
			
			$req="CREATE SYNONYM DWH_ECRF_ANSWER FOR $USER_DB_DBH_ETL.DWH_ECRF_ANSWER";
			$sel = oci_parse($dbh,$req);
			oci_execute($sel);
		}
	}
	
	$req="select table_name from all_tables where table_name ='DWH_ECRF_SUB_ITEM'  and owner=upper('$USER_DB_DBH_ETL')";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	$ligne = oci_fetch_array($sel);
	$verif_ecrf = $ligne['TABLE_NAME'];
	if ($verif_ecrf=='') {
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
		
		if ($USER_DB_DBH_ETL!=$USER_DB_DBH) {
			$req="GRANT DELETE, INSERT, SELECT, UPDATE ON $USER_DB_DBH_ETL.DWH_ECRF_SUB_ITEM TO $USER_DB_DBH ";
			$sel = oci_parse($dbh_etl,$req);
			oci_execute($sel);
			
			$req="CREATE SYNONYM DWH_ECRF_SUB_ITEM FOR $USER_DB_DBH_ETL.DWH_ECRF_SUB_ITEM";
			$sel = oci_parse($dbh,$req);
			oci_execute($sel);
		}
	
	
	}
	
	$req="select table_name from all_tables where table_name ='DWH_ECRF_USER_RIGHT'  and owner=upper('$USER_DB_DBH_ETL')";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	$ligne = oci_fetch_array($sel);
	$verif_ecrf = $ligne['TABLE_NAME'];
	if ($verif_ecrf=='') {
	
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
		
		if ($USER_DB_DBH_ETL!=$USER_DB_DBH) {
			$req="GRANT DELETE, INSERT, SELECT, UPDATE ON $USER_DB_DBH_ETL.DWH_ECRF_USER_RIGHT TO $USER_DB_DBH ";
			$sel = oci_parse($dbh_etl,$req);
			oci_execute($sel);
			
			$req="CREATE SYNONYM DWH_ECRF_USER_RIGHT FOR $USER_DB_DBH_ETL.DWH_ECRF_USER_RIGHT";
			$sel = oci_parse($dbh,$req);
			oci_execute($sel);
		}
	
	}
	
	$req="select table_name from all_tables where table_name ='DWH_USER_ECRF'  and owner=upper('$USER_DB_DBH_ETL')";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	$ligne = oci_fetch_array($sel);
	$verif_ecrf = $ligne['TABLE_NAME'];
	if ($verif_ecrf=='') {
	
		$req="CREATE TABLE DWH_USER_ECRF
	( 
		USER_NUM INT, 
		ECRF_NUM INT, 
		TOKEN_ECRF VARCHAR2 (50) 
	) 
	TABLESPACE TS_DWH";
		$sel = oci_parse($dbh_etl,$req);
		oci_execute($sel);
		
		if ($USER_DB_DBH_ETL!=$USER_DB_DBH) {
			$req="GRANT DELETE, INSERT, SELECT, UPDATE ON $USER_DB_DBH_ETL.DWH_USER_ECRF TO $USER_DB_DBH ";
			$sel = oci_parse($dbh_etl,$req);
			oci_execute($sel);
			
			$req="CREATE SYNONYM DWH_USER_ECRF FOR $USER_DB_DBH_ETL.DWH_USER_ECRF";
			$sel = oci_parse($dbh,$req);
			oci_execute($sel);
		}
	
	}
	
	
	
	$req="select table_name from all_tables where table_name ='DWH_ADMIN_CGU'  and owner=upper('$USER_DB_DBH_ETL')";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	$ligne = oci_fetch_array($sel);
	$verif_cgu = $ligne['TABLE_NAME'];
	if ($verif_cgu=='') {
		$req="create table DWH_ADMIN_CGU (cgu_num int, cgu_text clob,cgu_date date,published int,published_date date,unpublished_date date, primary key (cgu_num))  TABLESPACE TS_DWH ";
		$sel = oci_parse($dbh_etl,$req);
		oci_execute($sel);
		
		if ($USER_DB_DBH_ETL!=$USER_DB_DBH) {
			$req="GRANT DELETE, INSERT, SELECT, UPDATE ON $USER_DB_DBH_ETL.DWH_ADMIN_CGU TO $USER_DB_DBH ";
			$sel = oci_parse($dbh_etl,$req);
			oci_execute($sel);
			
			$req="CREATE SYNONYM DWH_ADMIN_CGU FOR $USER_DB_DBH_ETL.DWH_ADMIN_CGU";
			$sel = oci_parse($dbh,$req);
			oci_execute($sel);
		}
	}
	
	$req="select table_name from all_tables where table_name ='DWH_ADMIN_CGU_USER'  and owner=upper('$USER_DB_DBH_ETL')";
	$sel = oci_parse($dbh_etl,$req);
	oci_execute($sel);
	$ligne = oci_fetch_array($sel);
	$verif_cgu = $ligne['TABLE_NAME'];
	if ($verif_cgu=='') {
		$req="create table DWH_ADMIN_CGU_USER (cgu_num int, user_num int,date_signature date, foreign key (cgu_num) references DWH_ADMIN_CGU (cgu_num) on delete cascade) TABLESPACE TS_DWH ";
		$sel = oci_parse($dbh_etl,$req);
		oci_execute($sel);
		
		if ($USER_DB_DBH_ETL!=$USER_DB_DBH) {
			$req="GRANT DELETE, INSERT, SELECT, UPDATE ON $USER_DB_DBH_ETL.DWH_ADMIN_CGU_USER TO $USER_DB_DBH ";
			$sel = oci_parse($dbh_etl,$req);
			oci_execute($sel);
			
			$req="CREATE SYNONYM DWH_ADMIN_CGU_USER FOR $USER_DB_DBH_ETL.DWH_ADMIN_CGU_USER";
			$sel = oci_parse($dbh,$req);
			oci_execute($sel);
		}
	}
	
	
	$req="select table_name from all_tables where table_name ='DWH_PATIENT_OPPOSITION'  and owner=upper('$USER_DB_DBH_ETL')";
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
}
?>