2018 05 29 : 
alter table dwh_cohort_result add query_num int;


2018 09 01 :
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