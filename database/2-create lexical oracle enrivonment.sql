-- version 2019 03 06

/* create a list of stopwords in you language  */
begin 
CTX_DDL.CREATE_STOPLIST ('dwh_stoplist', 'BASIC_STOPLIST');
/* pas de stop word pour la recherche dans le texte normal.  */ 
ctx_ddl.add_stopword('dwh_stoplist', 'à');
ctx_ddl.add_stopword('dwh_stoplist', 'ça');
ctx_ddl.add_stopword('dwh_stoplist', 'étaient');
ctx_ddl.add_stopword('dwh_stoplist', 'était');
ctx_ddl.add_stopword('dwh_stoplist', 'étant');
ctx_ddl.add_stopword('dwh_stoplist', 'a');
ctx_ddl.add_stopword('dwh_stoplist', 'afin');
ctx_ddl.add_stopword('dwh_stoplist', 'ainsi');
ctx_ddl.add_stopword('dwh_stoplist', 'alors');
ctx_ddl.add_stopword('dwh_stoplist', 'après');
ctx_ddl.add_stopword('dwh_stoplist', 'au');
ctx_ddl.add_stopword('dwh_stoplist', 'aussi');
ctx_ddl.add_stopword('dwh_stoplist', 'autant');
ctx_ddl.add_stopword('dwh_stoplist', 'aux');
ctx_ddl.add_stopword('dwh_stoplist', 'auxquelles');
ctx_ddl.add_stopword('dwh_stoplist', 'auxquels');
ctx_ddl.add_stopword('dwh_stoplist', 'avec');
ctx_ddl.add_stopword('dwh_stoplist', 'ce');
ctx_ddl.add_stopword('dwh_stoplist', 'ceci');
ctx_ddl.add_stopword('dwh_stoplist', 'cela');
ctx_ddl.add_stopword('dwh_stoplist', 'celle');
ctx_ddl.add_stopword('dwh_stoplist', 'celles');
ctx_ddl.add_stopword('dwh_stoplist', 'celui');
ctx_ddl.add_stopword('dwh_stoplist', 'ces');
ctx_ddl.add_stopword('dwh_stoplist', 'cet');
ctx_ddl.add_stopword('dwh_stoplist', 'cette');
ctx_ddl.add_stopword('dwh_stoplist', 'ceux');
ctx_ddl.add_stopword('dwh_stoplist', 'chacun');
ctx_ddl.add_stopword('dwh_stoplist', 'chacune');
ctx_ddl.add_stopword('dwh_stoplist', 'chaque');
ctx_ddl.add_stopword('dwh_stoplist', 'comme');
ctx_ddl.add_stopword('dwh_stoplist', 'comment');
ctx_ddl.add_stopword('dwh_stoplist', 'dans');
ctx_ddl.add_stopword('dwh_stoplist', 'de');
ctx_ddl.add_stopword('dwh_stoplist', 'des');
ctx_ddl.add_stopword('dwh_stoplist', 'dont');
ctx_ddl.add_stopword('dwh_stoplist', 'du');
ctx_ddl.add_stopword('dwh_stoplist', 'dès');
ctx_ddl.add_stopword('dwh_stoplist', 'déjà');
ctx_ddl.add_stopword('dwh_stoplist', 'elle');
ctx_ddl.add_stopword('dwh_stoplist', 'elles');
ctx_ddl.add_stopword('dwh_stoplist', 'en');
ctx_ddl.add_stopword('dwh_stoplist', 'entre');
ctx_ddl.add_stopword('dwh_stoplist', 'et');
ctx_ddl.add_stopword('dwh_stoplist', 'etc');
ctx_ddl.add_stopword('dwh_stoplist', 'eux');
ctx_ddl.add_stopword('dwh_stoplist', 'furent');
ctx_ddl.add_stopword('dwh_stoplist', 'grâce');
ctx_ddl.add_stopword('dwh_stoplist', 'il');
ctx_ddl.add_stopword('dwh_stoplist', 'ils');
ctx_ddl.add_stopword('dwh_stoplist', 'jadis');
ctx_ddl.add_stopword('dwh_stoplist', 'je');
ctx_ddl.add_stopword('dwh_stoplist', 'jusqu');
ctx_ddl.add_stopword('dwh_stoplist', 'jusque');
ctx_ddl.add_stopword('dwh_stoplist', 'la');
ctx_ddl.add_stopword('dwh_stoplist', 'laquelle');
ctx_ddl.add_stopword('dwh_stoplist', 'le');
ctx_ddl.add_stopword('dwh_stoplist', 'lequel');
ctx_ddl.add_stopword('dwh_stoplist', 'les');
ctx_ddl.add_stopword('dwh_stoplist', 'lesquelles');
ctx_ddl.add_stopword('dwh_stoplist', 'lesquels');
ctx_ddl.add_stopword('dwh_stoplist', 'leur');
ctx_ddl.add_stopword('dwh_stoplist', 'leurs');
ctx_ddl.add_stopword('dwh_stoplist', 'lors');
ctx_ddl.add_stopword('dwh_stoplist', 'lorsque');
ctx_ddl.add_stopword('dwh_stoplist', 'lui');
ctx_ddl.add_stopword('dwh_stoplist', 'là');
ctx_ddl.add_stopword('dwh_stoplist', 'ma');
ctx_ddl.add_stopword('dwh_stoplist', 'malgré');
ctx_ddl.add_stopword('dwh_stoplist', 'me');
ctx_ddl.add_stopword('dwh_stoplist', 'mes');
ctx_ddl.add_stopword('dwh_stoplist', 'mien');
ctx_ddl.add_stopword('dwh_stoplist', 'mienne');
ctx_ddl.add_stopword('dwh_stoplist', 'miennes');
ctx_ddl.add_stopword('dwh_stoplist', 'miens');
ctx_ddl.add_stopword('dwh_stoplist', 'moins');
ctx_ddl.add_stopword('dwh_stoplist', 'moment');
ctx_ddl.add_stopword('dwh_stoplist', 'mon');
ctx_ddl.add_stopword('dwh_stoplist', 'ne');
ctx_ddl.add_stopword('dwh_stoplist', 'ni');
ctx_ddl.add_stopword('dwh_stoplist', 'nos');
ctx_ddl.add_stopword('dwh_stoplist', 'notamment');
ctx_ddl.add_stopword('dwh_stoplist', 'notre');
ctx_ddl.add_stopword('dwh_stoplist', 'notres');
ctx_ddl.add_stopword('dwh_stoplist', 'nous');
ctx_ddl.add_stopword('dwh_stoplist', 'nulle');
ctx_ddl.add_stopword('dwh_stoplist', 'nulles');
ctx_ddl.add_stopword('dwh_stoplist', 'nôtre');
ctx_ddl.add_stopword('dwh_stoplist', 'nôtres');
ctx_ddl.add_stopword('dwh_stoplist', 'on');
ctx_ddl.add_stopword('dwh_stoplist', 'ou');
ctx_ddl.add_stopword('dwh_stoplist', 'où');
ctx_ddl.add_stopword('dwh_stoplist', 'par');
ctx_ddl.add_stopword('dwh_stoplist', 'parce');
ctx_ddl.add_stopword('dwh_stoplist', 'plus');
ctx_ddl.add_stopword('dwh_stoplist', 'pour');
ctx_ddl.add_stopword('dwh_stoplist', 'puisque');
ctx_ddl.add_stopword('dwh_stoplist', 'quand');
ctx_ddl.add_stopword('dwh_stoplist', 'quant');
ctx_ddl.add_stopword('dwh_stoplist', 'que');
ctx_ddl.add_stopword('dwh_stoplist', 'quel');
ctx_ddl.add_stopword('dwh_stoplist', 'quelle');
ctx_ddl.add_stopword('dwh_stoplist', 'quelqu''un');
ctx_ddl.add_stopword('dwh_stoplist', 'quelqu''une');
ctx_ddl.add_stopword('dwh_stoplist', 'quelque');
ctx_ddl.add_stopword('dwh_stoplist', 'quelques-unes');
ctx_ddl.add_stopword('dwh_stoplist', 'quelques-uns');
ctx_ddl.add_stopword('dwh_stoplist', 'quels');
ctx_ddl.add_stopword('dwh_stoplist', 'qui');
ctx_ddl.add_stopword('dwh_stoplist', 'quoi');
ctx_ddl.add_stopword('dwh_stoplist', 'quoique');
ctx_ddl.add_stopword('dwh_stoplist', 'sa');
ctx_ddl.add_stopword('dwh_stoplist', 'se');
ctx_ddl.add_stopword('dwh_stoplist', 'selon');
ctx_ddl.add_stopword('dwh_stoplist', 'ses');
ctx_ddl.add_stopword('dwh_stoplist', 'sien');
ctx_ddl.add_stopword('dwh_stoplist', 'sienne');
ctx_ddl.add_stopword('dwh_stoplist', 'siennes');
ctx_ddl.add_stopword('dwh_stoplist', 'siens');
ctx_ddl.add_stopword('dwh_stoplist', 'soi');
ctx_ddl.add_stopword('dwh_stoplist', 'soit');
ctx_ddl.add_stopword('dwh_stoplist', 'sont');
ctx_ddl.add_stopword('dwh_stoplist', 'suis');
ctx_ddl.add_stopword('dwh_stoplist', 'sur');
ctx_ddl.add_stopword('dwh_stoplist', 'ta');
ctx_ddl.add_stopword('dwh_stoplist', 'tandis');
ctx_ddl.add_stopword('dwh_stoplist', 'tant');
ctx_ddl.add_stopword('dwh_stoplist', 'te');
ctx_ddl.add_stopword('dwh_stoplist', 'telle');
ctx_ddl.add_stopword('dwh_stoplist', 'telles');
ctx_ddl.add_stopword('dwh_stoplist', 'tes');
ctx_ddl.add_stopword('dwh_stoplist', 'tienne');
ctx_ddl.add_stopword('dwh_stoplist', 'tiennes');
ctx_ddl.add_stopword('dwh_stoplist', 'tiens');
ctx_ddl.add_stopword('dwh_stoplist', 'toi');
ctx_ddl.add_stopword('dwh_stoplist', 'ton');
ctx_ddl.add_stopword('dwh_stoplist', 'tous');
ctx_ddl.add_stopword('dwh_stoplist', 'toute');
ctx_ddl.add_stopword('dwh_stoplist', 'toutes');
ctx_ddl.add_stopword('dwh_stoplist', 'très');
ctx_ddl.add_stopword('dwh_stoplist', 'tu');
ctx_ddl.add_stopword('dwh_stoplist', 'un');
ctx_ddl.add_stopword('dwh_stoplist', 'une');
ctx_ddl.add_stopword('dwh_stoplist', 'vos');
ctx_ddl.add_stopword('dwh_stoplist', 'votre');
ctx_ddl.add_stopword('dwh_stoplist', 'vous');
ctx_ddl.add_stopword('dwh_stoplist', 'vu');
ctx_ddl.add_stopword('dwh_stoplist', 'vôtre');
ctx_ddl.add_stopword('dwh_stoplist', 'vôtres');
ctx_ddl.add_stopword('dwh_stoplist', 'y');
END;



/* create a list of stopwords in you language  */
begin 
CTX_DDL.CREATE_STOPLIST ('dwh_stoplist_empty', 'BASIC_STOPLIST');
ctx_ddl.add_stopword('dwh_stoplist_empty', 'noword');
END; 


/* precier le tablespace de stockage de l'index full text */

begin
ctx_ddl.drop_preference('dwhstore');
end;

begin
ctx_ddl.create_preference('dwhstore', 'BASIC_STORAGE');
ctx_ddl.set_attribute('dwhstore', 'I_TABLE_CLAUSE', 'tablespace TS_DWH');
ctx_ddl.set_attribute('dwhstore', 'K_TABLE_CLAUSE', 'tablespace TS_DWH');
ctx_ddl.set_attribute('dwhstore', 'R_TABLE_CLAUSE','tablespace TS_DWH');
ctx_ddl.set_attribute('dwhstore', 'N_TABLE_CLAUSE','tablespace TS_DWH');
ctx_ddl.set_attribute('dwhstore', 'I_INDEX_CLAUSE','tablespace TS_DWH');
ctx_ddl.set_attribute('dwhstore', 'P_TABLE_CLAUSE','tablespace TS_DWH');
end;

begin
ctx_ddl.drop_preference('dwh_lexer');
end;

begin
ctx_ddl.create_preference ( 'dwh_lexer', 'BASIC_LEXER' );
ctx_ddl.set_attribute( 'dwh_lexer', 'BASE_LETTER', 'true' );
ctx_ddl.set_attribute( 'dwh_lexer', 'OVERRIDE_BASE_LETTER', 'true');
end;
