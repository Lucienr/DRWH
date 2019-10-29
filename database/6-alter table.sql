--2018 05 29 : 
alter table dwh_cohort_result add query_num int;


--2018 09 01 :
CREATE TABLE DWH_FILE
( 
  PATIENT_NUM NUMBER(38,0),
  DOCUMENT_NUM        NUMBER(38,0),
  FILE_NUM            NUMBER(38,0),
  FILE_TITLE          VARCHAR2(4000 BYTE),
  FILE_ORDER          NUMBER(38,0),
  FILE_CONTENT        CLOB,
  FILE_MIME_TYPE      VARCHAR2(20 BYTE),
  UPLOAD_ID           NUMBER(38,0),
  PRIMARY KEY (FILE_NUM)
);


--2018 11 11 : (nouvel affichage du chargement ETL )

create index dwh_doc_upload_id on dwh_document ( substr(upload_id,1,8)) tablespace ts_idx;

create index dwh_doc_orig_upload_id on dwh_document ( document_origin_code,substr(upload_id,1,8)) tablespace ts_idx;

alter table DWH_process add category_process varchar(4000);

 create table dwh_regexp  (regexp_num int, 
 title varchar(4000),description varchar(4000),  regexp varchar(4000),  user_num_creation int,  creation_date date,  shared int)

--2019 05 31
create index  dwh_log_page_i on dwh_log_page ( user_num) tablespace ts_idx; 

-- 2019 06 03
alter table dwh_log_query add patient_num int

-- 2019 06 04
create index  dwh_file_patnum on dwh_file (patient_num) tablespace ts_idx; 
alter table dwh_document add patient_num_old int;
alter table dwh_patient_ipphist add patient_num_old int;

-- 2019 06 21
alter table DWH_process add type_result varchar(30);


alter table DWH_process add process_num_int int;
update DWH_process set process_num_int=dwh_seq.nextval;
alter table DWH_process rename column process_num to process_num_old;
alter table DWH_process rename column process_num_int to process_num;


alter table DWH_process_patient modify  process_num int;


alter table dwh_notification add  hide_receiver int;


-- 2019 07 10


CREATE TABLE DWH_EXPORT_DATA
(
  EXPORT_DATA_NUM  NUMBER(38),
  USER_NUM         NUMBER(38),
  TITLE            VARCHAR2(500 CHAR),
  LIST_CONCEPT     VARCHAR2(4000 CHAR),
  CREATION_DATE    DATE,
  SHARE_LIST       VARCHAR2(500 CHAR)
)
TABLESPACE TS_DWH
LOGGING 
MONITORING;


CREATE TABLE DWH_ECRF
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
  PRIMARY KEY
  (ECRF_NUM)
)
TABLESPACE TS_DWH
LOGGING 
MONITORING;


CREATE TABLE DWH_ECRF_ITEM
(
  ECRF_ITEM_NUM  INTEGER,
  ECRF_NUM       INTEGER,
  ITEM_STR       VARCHAR2(4000 CHAR),
  ITEM_TYPE      VARCHAR2(200 CHAR),
  ITEM_LIST      VARCHAR2(4000 CHAR),
  ITEM_NAME      VARCHAR2(200 CHAR),
  ITEM_CODES     VARCHAR2(4000 CHAR),
  REGEXP         VARCHAR2(500 CHAR),
  LOCAL_CODES    VARCHAR2(4000 CHAR),
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
MONITORING;


CREATE TABLE DWH_ECRF_ANSWER
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
MONITORING;


CREATE TABLE DWH_ECRF_SUB_ITEM
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
MONITORING;

CREATE TABLE DWH_ECRF_USER_RIGHT
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
MONITORING;


CREATE TABLE DWH_USER_ECRF
( 
	USER_NUM INT, 
	ECRF_NUM INT, 
	TOKEN_ECRF VARCHAR2 (50) 
) 
TABLESPACE TS_DWH; 



-- ALTER TABLE DWH_ECRF_ITEM ADD REGEXP VARCHAR2(500); 
-- ALTER TABLE DWH_ECRF ADD URL_ECRF VARCHAR(400); 


-- alter table dwh_ecrf add ( ecrf_start_date date , ecrf_end_date date, ecrf_start_age float, ecrf_end_age  float);


-- 2019 07 15

-- ALTER TABLE DWH_ECRF_ITEM ADD PERIOD VARCHAR2(50 CHAR); 
-- ALTER TABLE DWH_ECRF_ITEM ADD LOCAL_CODES VARCHAR2(4000 CHAR); 
-- ALTER TABLE DWH_ECRF_ITEM ADD REGEXP_INDEX VARCHAR2(50 CHAR); 


-- 2019 07 22


-- create table dwh_ecrf_sub_item (
-- ECRF_SUB_ITEM_NUM integer, 
-- ECRF_ITEM_NUM integer, 
-- sub_item_local_str varchar(4000),
-- sub_item_local_code  varchar(4000),
-- sub_item_ext_code  varchar(4000),
-- sub_item_regexp  varchar(4000),
-- primary key (ECRF_SUB_ITEM_NUM),
-- foreign key (ECRF_ITEM_NUM) references dwh_ecrf_item (ECRF_ITEM_NUM) on delete cascade)
-- tablespace ts_dwh;

-- drop table DWH_ECRF_ANSWER;

-- CREATE TABLE DWH_ECRF_ANSWER
-- (
--   ECRF_ITEM_NUM       INTEGER,
--   ECRF_NUM            INTEGER,
--   PATIENT_NUM         INTEGER,
--   USER_NUM            INTEGER,
--   USER_VALUE          VARCHAR2(4000 CHAR),
--   AUTOMATED_VALUE     VARCHAR2(4000 CHAR),
--   ECRF_ITEM_EXTRACT  CLOB,
--   USER_VALUE_DATE         DATE,
--   AUTOMATED_VALUE_DATE     DATE,
--  foreign key (ECRF_ITEM_NUM) references  DWH_ECRF_ITEM (ECRF_ITEM_NUM) on delete cascade
-- )
-- TABLESPACE TS_DWH
-- LOGGING 
-- MONITORING;



-- alter table dwh_ecrf_answer add user_value_date date;
-- alter table dwh_ecrf_answer add automated_value_date date;
-- alter table dwh_ecrf_item add item_order int;
-- alter table dwh_ecrf_answer add ( user_validation int, user_validation_date date)


create index dwh_patient_opposition_i on dwh_patient_opposition (patient_num) tablespace ts_idx;


create index dwh_data_minmaxdate on dwh_data (thesaurus_data_num,to_number(to_char(document_date,'YYYY'))) tablespace ts_idx


create table DWH_ADMIN_CGU 
	(cgu_num int, cgu_text clob,cgu_date date,published int,published_date date,unpublished_date date, primary key (cgu_num)) 
TABLESPACE TS_DWH
LOGGING 
MONITORING;

create table DWH_ADMIN_CGU_USER (cgu_num int, user_num int,date_signature date, foreign key (cgu_num) references DWH_ADMIN_CGU (cgu_num) on delete cascade) 
TABLESPACE TS_DWH
LOGGING 
MONITORING;



alter table dwh_thesaurus_department add (count_document int, count_mvt int);


alter table DWH_THESAURUS_UNIT add (count_document int, count_mvt int);