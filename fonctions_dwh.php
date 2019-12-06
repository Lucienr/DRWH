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

if ($_SESSION['DWH_LANG']!='') {
	$JSON_TRANSLATION_FILE=$_SESSION['DWH_LANG'];
}

/* modification database */ 
include_once ("check_database_uptodate.php");

/* ------------------- */
$user_num_session=$_SESSION['dwh_user_num'];
if ($user_num_session!='') {
	$sel=oci_parse($dbh,"delete from dwh_user_online where user_num='$user_num_session' and database='DrWH'");
	oci_execute($sel);
	$user_name=get_user_information ($user_num_session,'pn');
	$sel=oci_parse($dbh,"insert into dwh_user_online (user_num,last_update_date,database,user_name) values ('$user_num_session',sysdate,'DrWH','$user_name')");
	oci_execute($sel);
	#if ($_SESSION['dwh_create_table_temp_query']=='') {
		create_table_temp_query ($user_num_session);
	#}
	$_SESSION['dwh_create_table_temp_query']='ok';
	
}

$sel=oci_parse($dbh,"select  document_origin_str,document_origin_code from dwh_admin_document_origin where document_origin_str is not null order by document_origin_str ");
oci_execute($sel);
while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
	$document_origin_code=$r['DOCUMENT_ORIGIN_CODE'];
	$document_origin_str=$r['DOCUMENT_ORIGIN_STR'];
	$tableau_global_document_origin_code[$document_origin_code]=$document_origin_str;
}



$stop_words=array ('des','les','du','de','la','avec','une','un','mais','or','and','et','ne','le');



function create_table_temp_query ($user_num) {
	global $dbh;
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
					OBJECT_TYPE  VARCHAR(50)
					)
					nologging
					");   
		oci_execute($sel);
	}

	$sel = oci_parse($dbh, "select table_name from all_tables where table_name='DWH_TMP_RESULT_$user_num'  ");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	$table_name_verif=$r['TABLE_NAME'];
	if ($table_name_verif=='') {
		$sel = oci_parse($dbh, "create table DWH_TMP_RESULT_$user_num 
					(
					  TMPRESULT_NUM         INTEGER,
					  USER_NUM              VARCHAR(30),
					  ENCOUNTER_NUM         VARCHAR(20),
					  DOCUMENT_NUM          INTEGER,
					  DOCUMENT_DATE         DATE,
					  AUTHOR                VARCHAR(200),
					  DOCUMENT_ORIGIN_CODE  VARCHAR(30),
					  TITLE                 VARCHAR(300),
					  DEPARTMENT_NUM        INTEGER,
					  PATIENT_NUM           INTEGER,
					  OBJECT_TYPE  VARCHAR(50)
					)
					nologging
					NOPARALLEL ");   
		oci_execute($sel);
		$sel = oci_parse($dbh, "CREATE INDEX DWH_TMP_RESX1_$user_num ON DWH_TMP_RESULT_$user_num  (TMPRESULT_NUM, DOCUMENT_NUM) NOLOGGING TABLESPACE TS_IDX NOPARALLEL");
		oci_execute($sel);
		$sel = oci_parse($dbh, "CREATE INDEX DWH_TMP_RESX2_$user_num ON DWH_TMP_RESULT_$user_num  (PATIENT_NUM, TMPRESULT_NUM) NOLOGGING TABLESPACE TS_IDX NOPARALLEL");
		oci_execute($sel);
		$sel = oci_parse($dbh, "CREATE INDEX DWH_TMP_RESX3_$user_num ON DWH_TMP_RESULT_$user_num  (TMPRESULT_NUM, PATIENT_NUM) NOLOGGING TABLESPACE TS_IDX NOPARALLEL");
		oci_execute($sel);
		$sel = oci_parse($dbh, "CREATE INDEX DWH_TMP_RESX4_$user_num ON DWH_TMP_RESULT_$user_num  (TMPRESULT_NUM) NOLOGGING TABLESPACE TS_IDX NOPARALLEL");
		oci_execute($sel);
	}
	
	$sel = oci_parse($dbh, "select table_name from all_tables where table_name='DWH_TMP_RESULTALL_$user_num'  ");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	$table_name_verif=$r['TABLE_NAME'];
	if ($table_name_verif=='') {
		$sel = oci_parse($dbh, "create table DWH_TMP_RESULTALL_$user_num 
					(
					  TMPRESULT_NUM         INTEGER,
					  USER_NUM              VARCHAR(30),
					  ENCOUNTER_NUM         VARCHAR(20),
					  DOCUMENT_NUM          INTEGER,
					  DOCUMENT_DATE         DATE,
					  AUTHOR                VARCHAR(200),
					  DOCUMENT_ORIGIN_CODE  VARCHAR(30),
					  TITLE                 VARCHAR(300),
					  DEPARTMENT_NUM        INTEGER,
					  PATIENT_NUM           INTEGER,
					  OBJECT_TYPE  VARCHAR(50)
					)
					nologging
					NOPARALLEL ");   
		oci_execute($sel);
		$sel = oci_parse($dbh, "CREATE INDEX DWH_TMP_RESAX1_$user_num ON DWH_TMP_RESULTALL_$user_num  (TMPRESULT_NUM, DOCUMENT_NUM) NOLOGGING TABLESPACE TS_IDX NOPARALLEL");
		oci_execute($sel);
		$sel = oci_parse($dbh, "CREATE INDEX DWH_TMP_RESAX2_$user_num ON DWH_TMP_RESULTALL_$user_num  (PATIENT_NUM, TMPRESULT_NUM) NOLOGGING TABLESPACE TS_IDX NOPARALLEL");
		oci_execute($sel);
		$sel = oci_parse($dbh, "CREATE INDEX DWH_TMP_RESAX3_$user_num ON DWH_TMP_RESULTALL_$user_num  (TMPRESULT_NUM, PATIENT_NUM) NOLOGGING TABLESPACE TS_IDX NOPARALLEL");
		oci_execute($sel);
		$sel = oci_parse($dbh, "CREATE INDEX DWH_TMP_RESAX4_$user_num ON DWH_TMP_RESULTALL_$user_num  (TMPRESULT_NUM) NOLOGGING TABLESPACE TS_IDX NOPARALLEL");
		oci_execute($sel);
	}
}

function add_atomic_query ($num_filtre,$query_type) {
        global $dbh, $tableau_global_document_origin_code, $tableau_global_contexte,$liste_service_session,$user_num_session,$thesaurus_code_labo,$thesaurus_code_pmsi;
        print "
        <div id=\"id_div_filtre_texte_$num_filtre\" class=\"div_filtre\">";
	print "<div class=\"circle\">$num_filtre</div>";
        //if ($num_filtre>1) {
	        print "<div style=\"position:absolute;left:400px;z-index:22;top:-6;color:grey;cursor:pointer;\" onclick=\"supprimer_formulaire_texte_vierge($num_filtre);\">x</div>";
	//}


        if ($query_type=='text' || $query_type=='texte' || $query_type=='') {
        	$style_display_texte='inline';
        } else {
        	$style_display_texte='none';
        }
	if ($query_type=='code') {
		$style_display_code='inline';
		$style_display_code_recherche='block';
	} else {
		$style_display_code='none';
		$style_display_code_recherche='none';
	}

  	print "<textarea onkeypress=\"if(event.keyCode==13) {calcul_nb_resultat_filtre ($num_filtre,true);event.preventDefault();} else {}\" id=\"id_input_filtre_texte_$num_filtre\" name=\"text_$num_filtre\" style=\"display:$style_display_texte\" class=\"filtre_texte input_texte autosizejs\" cols=\"50\" rows=\"2\"></textarea>";

	print "<div id=\"id_div_filtre_texte_structure_$num_filtre\" class=\"filtre_texte_avance\" style=\"display:$style_display_code\">
			".get_translation('SEARCH_A_CODE','Rechercher un code')." : 
			<select id=\"id_select_thesaurus_data_$num_filtre\">
				<option value=\"\">".get_translation('THESAURUS','Thesaurus')."</option>";
	$sel=oci_parse($dbh,"select distinct thesaurus_code from dwh_thesaurus_data ");
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$thesaurus_code=$r['THESAURUS_CODE'];
		print "<option value=\"$thesaurus_code\">$thesaurus_code</option>";
	}
	print "</select>			
			<input type=\"text\" id=\"id_rechercher_code_$num_filtre\" onkeypress=\"if(event.keyCode==13) {rechercher_code($num_filtre);}\"  class=\"filtre_date_document\"><input type=\"button\" value=\"Go\"  class=\"filtre_date_document\" onclick=\"rechercher_code($num_filtre);\"> 
			<input type=hidden value=\"\" name=\"thesaurus_data_num_$num_filtre\" id=\"id_input_thesaurus_data_num_$num_filtre\">
			<input type=hidden name=\"chaine_requete_code_$num_filtre\" id=\"id_input_chaine_requete_code_$num_filtre\" value=\"\"> 
			
                </div>";
	print " <span id=\"id_span_nbresult_atomique_$num_filtre\" class=\"filtre_texte_nbresult\" style=\"display:inline;cursor:pointer;\" onclick=\"calcul_nb_resultat_filtre ($num_filtre,true);\">?</span><span id=\"id_span_nbresult_atomique_chargement_$num_filtre\" style=\"display:inline\"></span>";

	print "<div id=\"id_div_correction_orthographique_$num_filtre\" style=\"display:none;background-color:#E6D9E3;margin-top:10px;\">
			<div onclick=\"plier('id_div_correction_orthographique_$num_filtre');\" style=\"float:right;color:grey;cursor:pointer;\">x</div>
			<strong class=\"filtre_texte_avance\"  style=\"display:inline\">".get_translation('PROPOSED_CORRECTIONS','Corrections proposées')."</strong><br>
			<div id=\"id_div_correction_orthographique_propositions_$num_filtre\" class=\"filtre_texte_avance\" style=\"display: block; margin-left: 10px;\"></div>
		</div>";

	print "<span style=\"display:$style_display_texte;font-size:13px;\" ><br>".get_translation('EXPAND_TO_SYNONYMS','Etendre aux synonymes')." : <input id=\"id_checkbox_etendre_syno_$num_filtre\"  name=\"etendre_syno_$num_filtre\" type=\"checkbox\" onclick=\"calcul_nb_resultat_filtre($num_filtre,true);\" value=\"1\"></span>";

	print "<div id=\"id_div_selection_code_$num_filtre\" style=\"display:$style_display_code_recherche\">
		</div>
		<div id=\"id_div_resultat_recherche_code_$num_filtre\" style=\"display:$style_display_code_recherche\">
		</div><br>";
		
		
	/* RECHERCHE AVANCEE */
	print "<span id=\"id_span_filtre_texte_ouvrir_avance_$num_filtre\" class=\"filtre_texte_ouvrir_avance\"  style=\"display:inline;\">
		        <a onclick=\"plier_deplier('id_div_filtre_texte_avance_$num_filtre');\" id=\"id_a_filtre_texte_ouvrir_avance_$num_filtre\"><span id=\"plus_id_div_filtre_texte_avance_$num_filtre\">+</span>".get_translation('ADVANCED','Avancé')." </a>
		</span>
		<span id=\"id_span_filtre_texte_reecrire_$num_filtre\" class=\"filtre_texte_ouvrir_avance\" style=\"display:$style_display_texte;\">
		      - <a onclick=\"reecrire_requete('$num_filtre');\"> ".get_translation('REWRITE_SEARCH_QUERY','Réécrire la requête')."</a>
		</span>
                 <div id=\"id_div_filtre_texte_avance_$num_filtre\" class=\"filtre_texte_avance\">
               			 <table border=\"0\">
               			 <tr><td>".get_translation('EXCLUDE','Exclure')." : </td><td><input type=\"checkbox\" id=\"id_input_filtre_exclure_$num_filtre\" name=\"exclure_$num_filtre\" value=\"1\"> </td></tr>
               			 <tr><td rowspan=2 style=\"vertical-align:top\">".get_translation('PATIENT_AGE_AT_DOCUMENT_DATE','Age du patient à la date du document')." :</td><td>".get_translation('FROM_AGE','de')." <input type=\"text\" id=\"id_input_filtre_age_deb_document_$num_filtre\" name=\"age_deb_document_$num_filtre\" class=\"filtre_date_document\" size=\"2\" value=\"\"> ".get_translation('YEARS','ans')." ".get_translation('TO_YEAR','à')." <input type=\"text\" id=\"id_input_filtre_age_fin_document_$num_filtre\" name=\"age_fin_document_$num_filtre\" class=\"filtre_date_document\" size=\"2\" value=\"\" onblur=\"verif_chaine_modifiee_avant_calcul($num_filtre,'id_input_filtre_age_fin_document_$num_filtre','');\"> ".get_translation('YEARS','ans')."</td></tr>
               			 <tr><td>".get_translation('FROM_AGE','de')." <input type=\"text\" id=\"id_input_filtre_agemois_deb_document_$num_filtre\" name=\"agemois_deb_document_$num_filtre\" class=\"filtre_date_document\" size=\"2\" value=\"\"> ".get_translation('MONTH','mois')." ".get_translation('TO_YEAR','à')." <input type=\"text\" id=\"id_input_filtre_agemois_fin_document_$num_filtre\" name=\"agemois_fin_document_$num_filtre\" class=\"filtre_date_document\" size=\"2\" value=\"\" onblur=\"verif_chaine_modifiee_avant_calcul($num_filtre,'id_input_filtre_agemois_fin_document_$num_filtre','');\"> ".get_translation('MONTH','mois')."</td></tr>";
        print "<tr><td>".get_translation('DOCUMENT_ORIGIN','Origine du document')." :</td><td><select  onchange=\"calcul_nb_resultat_filtre($num_filtre,true);\" id=\"id_select_filtre_document_origin_code_$num_filtre\" name=\"document_origin_code_".$num_filtre."[]\" class=\"chosen-select filtre_document_origin_code\"  data-placeholder=\"".get_translation('CHOOSE_1_N_SOURCES','Choisissez une ou plusieurs sources')."\" multiple>";
        foreach ($tableau_global_document_origin_code as $document_origin_code => $document_origin_str) {
                print "<option value=\"$document_origin_code\" id=\"id_option_filtre_document_origin_code_".$num_filtre."_".$document_origin_code."\">$document_origin_str</option>";
        }
        print "</select></td></tr>";

        print "<tr><td>".get_translation('HOSPITAL_DEPARTMENT','Service')." : </td><td><select  onchange=\"calcul_nb_resultat_filtre($num_filtre,true);\" id=\"id_select_filtre_unite_heberg_$num_filtre\" name=\"unite_heberg_".$num_filtre."[]\" class=\"chosen-select filtre_unite_heberg\"  data-placeholder=\"".get_translation('CHOOSE_1_N_HOSPITAL_DEPARTMENTS','Choisissez un ou plusieurs services')."\" multiple>";
        $table_department=get_list_departments('master','');
        foreach ($table_department as $department) {
                $department_num=$department['department_num'];
                $department_str=$department['department_str'];
                print "<option value=\"$department_num\" id=\"id_option_filtre_unite_heberg_".$num_filtre."_".$department_num."\">$department_str</option>";
        }
	print "</select></td></tr>";
	
        print "<tr><td>".get_translation('DOCUMENT_TITLE','Titre du document')." : </td><td><input type='text' size='20' onblur=\"calcul_nb_resultat_filtre($num_filtre,true);\" id=\"id_input_filtre_title_document_$num_filtre\" name=\"title_document_$num_filtre\" class=\"filtre_date_document\" ></td></tr>";
        
	print "<tr><td>".get_translation('DOCUMENT_DATE','Date du document')." :</td><td>".get_translation('DATE_FROM','du')." <input type=\"text\" id=\"id_input_filtre_date_deb_document_$num_filtre\" name=\"date_deb_document_$num_filtre\" class=\"filtre_date_document\" size=\"10\" value=\"\"  onblur=\"if (this.value.length==4) {this.value='01/01/'+this.value;}\"> 
	               ".get_translation('DATE_TO','au')." <input type=\"text\" id=\"id_input_filtre_date_fin_document_$num_filtre\" name=\"date_fin_document_$num_filtre\" class=\"filtre_date_document\" size=\"10\" value=\"\"  onblur=\"if (this.value.length==4) {this.value='31/12/'+this.value;} calcul_nb_resultat_filtre($num_filtre,true);\"  ></td></tr>";

	print "<tr><td>".get_translation('DOCUMENT_LAST_NB_DAYS','Documents sur les')." :</td><td><input type=\"text\" id=\"id_input_filtre_document_last_nb_days_$num_filtre\" name=\"document_last_nb_days_$num_filtre\" class=\"filtre_date_document\" size=\"5\" value=\"\" > ".get_translation('LAST_DAYS','derniers jours')."</td></tr>";

	print "<tr><td>".get_translation('SEJOUR_LENGTH',"Dans un séjour d'une durée")."</td><td>".get_translation('OF','de')." <input type=\"text\" id=\"id_input_filtre_stay_length_min_$num_filtre\" name=\"stay_length_min_$num_filtre\" class=\"filtre_date_document\" size=\"4\" value=\"\" > 
	               ".get_translation('DATE_TO','au')." <input type=\"text\" id=\"id_input_filtre_stay_length_max_$num_filtre\" name=\"stay_length_max_$num_filtre\" class=\"filtre_date_document\" size=\"4\" value=\"\" onblur=\"calcul_nb_resultat_filtre($num_filtre,true);\"  > ".get_translation('DAYS','jours')."</td></tr>";


	print "<tr><td>".get_translation('INFORMATION_AVALAIBLE_PERIOD_AT_LEAST',"Information présente sur une période d'au moins")." : </td><td> <input type=\"text\" id=\"id_input_filtre_periode_document_$num_filtre\" name=\"periode_document_$num_filtre\" class=\"filtre_date_document\" size=\"3\" value=\"\"> ".get_translation('YEARS','ans')." </td></tr>";
	print "<tr><td>".get_translation('CONTEXT','Contexte')." : </td><td><select id=\"id_select_filtre_contexte_$num_filtre\" name=\"context_$num_filtre\"  class=\"filtre_contexte\"  onchange=\"calcul_nb_resultat_filtre($num_filtre,true);\"><option value=''></option>";
        foreach ($tableau_global_contexte as $context => $libelle_contexte) {
                print "<option value=\"$context\">$libelle_contexte</option>";
        }               
        print "</select></td></tr>
                 <tr><td>".get_translation('NEGATION','Négation :')." </td><td><select id=\"id_select_filtre_certitude_$num_filtre\" name=\"certainty_$num_filtre\"  class=\"filtre_contexte\"  onchange=\"calcul_nb_resultat_filtre($num_filtre,true);\">";
                print "<option value=\"\"></option>";
                print "<option value=\"1\">".get_translation('EXCLUDED_SINGULAR','Exclu')."</option>";
                print "<option value=\"0\">".get_translation('INCLUDED_SINGULAR','Inclu')."</option>";
        print "</select></td></tr></table>
                        </div>
                        <input type=\"hidden\" id=\"id_input_nbresult_atomique_$num_filtre\" name=\"texte_nbresult_$num_filtre\" value=\"\">
                        <input type=\"hidden\" id=\"id_query_key_$num_filtre\" name=\"query_key_$num_filtre\" value=\"\">
                        <input type=\"hidden\" id=\"id_input_filtre_num_filtre_$num_filtre\" name=\"num_filtre_$num_filtre\" value=\"$num_filtre\">
                        <input type=\"hidden\" id=\"id_query_type_$num_filtre\" name=\"query_type_$num_filtre\" value=\"$query_type\">
        </div>";
}

function get_list_departments ($filter_master, $user_num) {
	global $dbh;
	$req_department_master="";
	if ($filter_master=='master') {
		$req_department_master="and department_master=1";
	}
	if ($user_num!='') {
		$req_user_num="and department_num in (select department_num from dwh_user_department where user_num=$user_num)";
	}
	$table_department=array();
        $sel = oci_parse($dbh,"select department_num,department_str,department_code,department_master from  dwh_thesaurus_department where 1=1 $req_department_master $req_user_num order by department_str" );   
        oci_execute($sel);
        while ($row = oci_fetch_array($sel, OCI_ASSOC)) {
                $department_num=$row['DEPARTMENT_NUM'];
                $department_str=$row['DEPARTMENT_STR'];
                $department_code=$row['DEPARTMENT_CODE'];
                $department_master=$row['DEPARTMENT_MASTER'];
                $table_department[]=array('department_num'=>$department_num,'department_str'=>$department_str,'department_code'=>$department_code,'department_master'=>$department_master);
        }
        return $table_department;
}

function get_list_units ($department_num) {
	global $dbh;
	$table_units=array();
        $sel = oci_parse($dbh,"select unit_num,unit_str,unit_code,to_char(unit_start_date,'DD/MM/YYYY') as unit_start_date , to_char(unit_end_date,'DD/MM/YYYY') as unit_end_date 
         from  dwh_thesaurus_unit where department_num=$department_num  order by unit_str" );   
        oci_execute($sel);
        while ($row = oci_fetch_array($sel, OCI_ASSOC)) {
                $unit_num=$row['UNIT_NUM'];
                $unit_str=$row['UNIT_STR'];
                $unit_code=$row['UNIT_CODE'];
                $unit_start_date=$row['UNIT_START_DATE'];
                $unit_end_date=$row['UNIT_END_DATE'];
                $table_units[]=array(
                'unit_num'=>$unit_num,
                'unit_str'=>$unit_str,
                'unit_code'=>$unit_code,
                'unit_start_date'=>$unit_start_date,
                'unit_end_date'=>$unit_end_date
                );
        }
        return $table_units;
}

function add_atomic_query_mvt ($num_filtre) {
        global $dbh, $tableau_global_document_origin_code, $tableau_global_contexte,$liste_service_session,$user_num_session,$thesaurus_code_labo,$thesaurus_code_pmsi;
        print "
        <div id=\"id_div_filtre_mvt_$num_filtre\" class=\"div_filtre\">";
	print "<div class=\"circle\">$num_filtre</div>";
        if ($num_filtre>1) {
	        print "<div style=\"position:absolute;left:400px;z-index:22;top:-6;color:grey;cursor:pointer;\" onclick=\"delete_atomic_query_mvt($num_filtre);\">x</div>";
	}

	print " <span  onclick=\"calcul_nb_resultat_filtre_mvt ($num_filtre,true);\">".get_translation('QUERY_CARE_PATH','Rercherche sur le parcours')."</span>";

	print " <span id=\"id_span_nbresult_atomique_$num_filtre\" class=\"filtre_texte_nbresult\" style=\"display:inline;cursor:pointer;\" onclick=\"calcul_nb_resultat_filtre_mvt ($num_filtre,true);\">?</span><span id=\"id_span_nbresult_atomique_chargement_$num_filtre\" style=\"display:inline\"></span>";

		
	print "<table>";
        print "<tr><td>".get_translation('HOSPITAL_DEPARTMENT','Service')." : </td>
        <td><select id=\"id_select_filtre_mvt_department_$num_filtre\" name=\"mvt_department_".$num_filtre."\" class=\"chosen-select form\"  data-placeholder=\"".get_translation('CHOOSE_1_HOSPITAL_DEPARTMENT','Choisissez un service')."\">
        <option value=''></option>";
        $table_department=get_list_departments('master','');
        foreach ($table_department as $department) {
                $department_num=$department['department_num'];
                $department_str=$department['department_str'];
                print "<option value=\"$department_num\" id=\"id_option_filtre_mvt_department_".$num_filtre."_".$department_num."\">$department_str</option>";
        }
	print "</select></td></tr>";
	
        print "<tr><td>".get_translation('HOSPITAL_UNIT','unité')." : </td>
        <td><select id=\"id_select_filtre_mvt_unit_$num_filtre\" name=\"mvt_unit_".$num_filtre."\" class=\"chosen-select form\"  data-placeholder=\"".get_translation('CHOOSE_1_HOSPITAL_UNIT','Choisissez une unité')."\" >
        <option value=''></option>";
        $table_department=get_list_departments('master','');
        foreach ($table_department as $department) {
                $department_num=$department['department_num'];
                $department_str=$department['department_str'];
                $table_units=get_list_units ($department_num);
                print "<optgroup label=\"$department_str\">";
        	foreach ($table_units as $unit) {
	                $unit_num=$unit['unit_num'];
	                $unit_str=$unit['unit_str'];
	                $unit_code=$unit['unit_code'];
	                $unit_start_date=$unit['unit_start_date'];
	                $unit_end_date=$unit['unit_end_date'];
                	print "<option value=\"$unit_num\" id=\"id_option_filtre_mvt_unit_".$num_filtre."_".$unit_num."\">$unit_code $unit_str ($unit_start_date - $unit_end_date)</option>";
        	}
                print "</optgroup>";
                
        }
	print "</select></td></tr>";
	
	
        print "<tr><td>".get_translation('TYPE_MVT','Type de mouvement')." : </td>
        <td><select id=\"id_select_filtre_type_mvt_$num_filtre\" name=\"type_mvt_".$num_filtre."\" class=\"chosen-select form\"  data-placeholder=\"".get_translation('CHOOSE_1_TYPE','Choisissez un type')."\" >
        <option value=''></option>";
       print "<option value=\"H\" id=\"id_option_filtre_type_mvt_".$num_filtre."_H\">".get_translation('HOSPITALISATION','Hospitalisation')."</option>";
       print "<option value=\"C\" id=\"id_option_filtre_type_mvt_".$num_filtre."_C\">".get_translation('CONSULTATION','Consultation')."</option>";
       print "<option value=\"J\" id=\"id_option_filtre_type_mvt_".$num_filtre."_J\">".get_translation('HDJ','HDJ')."</option>";
       print "<option value=\"U\" id=\"id_option_filtre_type_mvt_".$num_filtre."_U\">".get_translation('URGENCE','Urgence')."</option>";
	print "</select></td></tr>";
	
        print "<tr>
        <td>".get_translation('DUREE_SEJOUR_COMPLET','Durée totale du séjour')." :</td>
        <td>".get_translation('FROM','de')." <input type=\"text\" id=\"id_input_filtre_encounter_duration_min_$num_filtre\" name=\"encounter_duration_min_$num_filtre\" class=\"filtre_date_document\" size=\"2\" value=\"\"> ".get_translation('DAYS','jours')." 
            ".get_translation('TO_YEAR','à')." <input type=\"text\" id=\"id_input_filtre_encounter_duration_max_$num_filtre\" name=\"encounter_duration_max_$num_filtre\" class=\"filtre_date_document\" size=\"2\" value=\"\"> ".get_translation('DAYS','jours')."</td></tr>";
 	
	
        print "<tr>
        <td>".get_translation('DUREE_MOVMENT','Durée du mouvement')." :</td>
        <td>".get_translation('FROM','de')." <input type=\"text\" id=\"id_input_filtre_mvt_duration_min_$num_filtre\" name=\"mvt_duration_min_$num_filtre\" class=\"filtre_date_document\" size=\"2\" value=\"\"> ".get_translation('DAYS','jours')." 
            ".get_translation('TO_YEAR','à')." <input type=\"text\" id=\"id_input_filtre_mvt_duration_max_$num_filtre\" name=\"mvt_duration_max_$num_filtre\" class=\"filtre_date_document\" size=\"2\" value=\"\"> ".get_translation('DAYS','jours')."</td></tr>";
 	
	
	/* RECHERCHE AVANCEE */
	print "<tr><td rowspan=2 style=\"vertical-align:top\">".get_translation('PATIENT_AGE_AT_MVT_DATE','Age du patient à la date du mouvement')." :</td><td>".get_translation('FROM_AGE','de')." <input type=\"text\" id=\"id_input_filtre_mvt_ageyear_start_$num_filtre\" name=\"mvt_ageyear_start_$num_filtre\" class=\"filtre_date_document\" size=\"2\" value=\"\"> ".get_translation('YEARS','ans')." ".get_translation('TO_YEAR','à')." <input type=\"text\" id=\"id_input_filtre_mvt_ageyear_end_$num_filtre\" name=\"mvt_ageyear_end_$num_filtre\" class=\"filtre_date_document\" size=\"2\" value=\"\" onblur=\"verif_chaine_modifiee_avant_calcul($num_filtre,'id_input_filtre_mvt_ageyear_end_$num_filtre','');\"> ".get_translation('YEARS','ans')."</td></tr>
	 <tr><td>".get_translation('FROM_AGE','de')." <input type=\"text\" id=\"id_input_filtre_mvt_agemonth_start_$num_filtre\" name=\"mvt_agemonth_start_$num_filtre\" class=\"filtre_date_document\" size=\"2\" value=\"\"> ".get_translation('MONTH','mois')." ".get_translation('TO_YEAR','à')." <input type=\"text\" id=\"id_input_filtre_mvt_agemonth_end_$num_filtre\" name=\"mvt_agemonth_end_$num_filtre\" class=\"filtre_date_document\" size=\"2\" value=\"\" onblur=\"verif_chaine_modifiee_avant_calcul($num_filtre,'id_input_filtre_mvt_agemonth_end_$num_filtre','');\"> ".get_translation('MONTH','mois')."</td></tr>";
               			 
	print "<tr><td>".get_translation('MVT_DATE','Date du mouvement')." :</td><td>".get_translation('DATE_FROM','du')." <input type=\"text\" id=\"id_input_filtre_mvt_date_start_$num_filtre\" name=\"mvt_date_start_$num_filtre\" class=\"filtre_date_document\" size=\"10\" value=\"\"  onblur=\"if (this.value.length==4) {this.value='01/01/'+this.value;}\"> 
	               ".get_translation('DATE_TO','au')." <input type=\"text\" id=\"id_input_filtre_mvt_date_end_$num_filtre\" name=\"mvt_date_end_$num_filtre\" class=\"filtre_date_document\" size=\"10\" value=\"\"  onblur=\"if (this.value.length==4) {this.value='31/12/'+this.value;} calcul_nb_resultat_filtre_mvt($num_filtre,true);\"  ></td></tr>";

	print "<tr><td>".get_translation('MVT_LAST_NB_DAYS','Mouvement sur les N derniers jours')." :</td><td><input type=\"text\" id=\"id_input_filtre_mvt_last_nb_days_$num_filtre\" name=\"mvt_last_nb_days_$num_filtre\" class=\"filtre_date_document\" size=\"5\" value=\"\" > ".get_translation('DAYS','jours')."</td></tr>";
	
      print "<tr>
        <td>".get_translation('EVENT_REPEATED','Mouvement répété')." :</td>
        <td>".get_translation('FROM','de')." <input type=\"text\" id=\"id_input_filtre_mvt_nb_min_$num_filtre\" name=\"mvt_nb_min_$num_filtre\" class=\"filtre_date_document\" size=\"2\" value=\"\"> ".get_translation('TIMES','fois')." 
            ".get_translation('TO','à')." <input type=\"text\" id=\"id_input_filtre_mvt_nb_max_$num_filtre\" name=\"mvt_nb_max_$num_filtre\" class=\"filtre_date_document\" size=\"2\" value=\"\"> ".get_translation('TIMES','fois')."</td></tr>";
 	
      print "<tr>
        <td>".get_translation('STAY_REPEATED','Séjour répété')." :</td>
        <td>".get_translation('FROM','de')." <input type=\"text\" id=\"id_input_filtre_stay_nb_min_$num_filtre\" name=\"stay_nb_min_$num_filtre\" class=\"filtre_date_document\" size=\"2\" value=\"\"> ".get_translation('TIMES','fois')." 
            ".get_translation('TO','à')." <input type=\"text\" id=\"id_input_filtre_stay_nb_max_$num_filtre\" name=\"stay_nb_max_$num_filtre\" class=\"filtre_date_document\" size=\"2\" value=\"\"> ".get_translation('TIMES','fois')."</td></tr>";
 	
	
        print "<tr><td>".get_translation('EXCLUDE','Exclure')." : </td><td><input type=\"checkbox\" id=\"id_input_filtre_exclure_$num_filtre\" name=\"exclure_$num_filtre\" value=\"1\"> </td></tr>";
        
        print "</table>
        <input type=\"hidden\" id=\"id_input_nbresult_atomique_$num_filtre\" name=\"texte_nbresult_$num_filtre\" value=\"\">
        <input type=\"hidden\" id=\"id_query_key_$num_filtre\" name=\"query_key_$num_filtre\" value=\"\">
        <input type=\"hidden\" id=\"id_input_filtre_num_filtre_$num_filtre\" name=\"num_filtre_$num_filtre\" value=\"$num_filtre\">
        <input type=\"hidden\" id=\"id_query_type_$num_filtre\" name=\"query_type_$num_filtre\" value=\"mvt\">
        </div>";
}

function ajouter_formulaire_code ($num_filtre,$thesaurus_data_num) {
        global $dbh,$tableau_couleur;

	$sel=oci_parse($dbh,"select concept_str,measuring_unit,info_complement,value_type,list_values,thesaurus_parent_num from dwh_thesaurus_data where thesaurus_data_num=$thesaurus_data_num ");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$concept_str=$r['CONCEPT_STR'];
	$measuring_unit=$r['MEASURING_UNIT'];
	$info_complement=$r['INFO_COMPLEMENT'];
	$value_type=$r['VALUE_TYPE'];
	$list_values=$r['LIST_VALUES'];
	
	print "<table><tr><td  class=\"filtre_date_document\"><strong style=\"font-size:13px;\">$concept_str ";
	if ($measuring_unit!='') {
		print " ($measuring_unit) ";
	}
	print " $info_complement</strong> </td></tr><tr>";
	if ($value_type=='numeric') {
		print "<td  class=\"filtre_date_document\">
		Valeur <select id=\"id_select_hors_borne_$num_filtre\" name=\"hors_borne_$num_filtre\"  class=\"filtre_date_document\" onchange=\"vider_items_code ($num_filtre,'hors_borne');creer_chaine_requete_code($num_filtre);\">
		 	<option value=''></option>
		 	<option value='hors_borne'> ".get_translation('OUT_OF_BOUNDS','hors borne')." </option>
		 	<option value='sup_borne'> &gt; ".get_translation('UPPER_LIMIT_FULL','borne supérieure')." </option>
		 	<option value='inf_borne'> &lt; ".get_translation('LOWER_LIMIT_FULL','borne inférieure')." </option>
		 </select><br>
		".get_translation('OR_VALUE','ou Valeur')." <select id=\"id_select_operateur_$num_filtre\" name=\"operateur_$num_filtre\"  class=\"filtre_date_document\" onchange=\"vider_items_code ($num_filtre,'operateur');creer_chaine_requete_code($num_filtre);\">
		 	<option value=''></option>
		 	<option value='sup'>  &gt; </option>
		 	<option value='sup_equal'> &ge; </option>
		 	<option value='inf'>  &lt; </option>
		 	<option value='inf_equal'> &le; </option>
		 	<option value='equal'> = </option>
		 </select> ".get_translation('TO_DATE','à')." <input type=\"text\" size=\"3\" class=\"filtre_date_document\" id=\"id_input_valeur_$num_filtre\" name=\"valeur_$num_filtre\" onkeyup=\"vider_items_code ($num_filtre,'operateur');verif_chaine_modifiee_avant_creer_chaine($num_filtre,'id_input_valeur_$num_filtre','');\"><br>
		 ".get_translation('OR_VALUE_BETWEEN','ou Valeur comprise entre')."  <input type=\"text\" size=\"3\" class=\"filtre_date_document\" id=\"id_input_valeur_deb_$num_filtre\" name=\"valeur_deb_$num_filtre\" onkeyup=\"vider_items_code ($num_filtre,'valeur');verif_chaine_modifiee_avant_creer_chaine($num_filtre,'id_input_valeur_deb_$num_filtre','');\"> 
		 ".get_translation('AND','et')."  <input type=\"text\" size=\"3\" class=\"filtre_date_document\" id=\"id_input_valeur_fin_$num_filtre\" name=\"valeur_fin_$num_filtre\" onkeyup=\"vider_items_code ($num_filtre,'valeur');verif_chaine_modifiee_avant_creer_chaine($num_filtre,'id_input_valeur_fin_$num_filtre','');\"><br>
		 ".get_translation('OR_VALUE_GREATER','ou Valeur supérieure à')." <input type=\"text\" size=\"3\" class=\"filtre_date_document\" id=\"id_input_valeur_sup_n_x_borne_sup_$num_filtre\" name=\"valeur_sup_n_x_borne_sup$num_filtre\" onkeyup=\"vider_items_code ($num_filtre,'n_x_borne');verif_chaine_modifiee_avant_creer_chaine($num_filtre,'id_input_valeur_sup_n_x_borne_sup_$num_filtre','');\">".get_translation('TIMES_UPPER_LIMIT','fois la borne supérieure')." <br>
		 ".get_translation('OR_VALUE_UNDER','ou Valeur inférieure à')." <input type=\"text\" size=\"3\" class=\"filtre_date_document\" id=\"id_input_valeur_inf_n_x_borne_inf_$num_filtre\" name=\"valeur_inf_n_x_borne_inf_$num_filtre\" onkeyup=\"vider_items_code ($num_filtre,'n_x_borne');verif_chaine_modifiee_avant_creer_chaine($num_filtre,'id_input_valeur_inf_n_x_borne_inf_$num_filtre','');\"> ".get_translation('TIMES_LOWER_LIMIT','fois la borne inférieure')."
		</td>";
	}
	if ($value_type=='liste') {
		$tableau_valeur=explode(';',$list_values);
		print "<td  class=\"filtre_date_document\"><div id=\"id_div_list_checkbox_thesaurus_data_$num_filtre\">";
		foreach ($tableau_valeur as $valeur) {
			print "<input type=checkbox class=\"class_input_checkbox_$numf_filtre\" value=\"$valeur\" onclick=\"creer_chaine_requete_code($num_filtre);\"> $valeur<br>";
		}
		print "</div></td>";
	}
	print "</tr></table>";
}



function ajouter_formulaire_patient () {
        global $dbh, $tableau_global_document_origin_code, $tableau_global_contexte,$liste_service_session,$user_num_session;
        print "
        <table border=\"0\" class=\"filtre_patient\">
                <tr>
                        <td>".get_translation('SEX','Sexe')." : 
                                <select name=\"sex\" id=\"id_sex\">
                                        <option value=''></value>
                                        <option value='F'>".get_translation('FEMALE','F')."</value>
                                        <option value='M'>".get_translation('MALE','M')."</value>
                                </select>
                        </td>
                </tr>
                <tr>
                        <td>".get_translation('PATIENT_AGE_TODAY',"Age du patient aujourd'hui (ans)")." : ".get_translation('FROM_AGE','de')." <input type=\"text\" size=\"2\" name=\"age_deb\" id=\"id_age_deb\"> ".get_translation('TO_AGE','à')." <input type=\"text\" size=\"2\" name=\"age_fin\" id=\"id_age_fin\">
                        </td>
                </tr>
                <tr>
                        <td>".get_translation('PATIENT_ALIVE_DECEASED','Patient vivant/Décédé') ." : <input type=\"radio\" name=\"vivant_dcd\" value='vivant' id=\"id_vivant\"> ".get_translation('ALIVE','vivant')."  <input type=\"radio\" name=\"vivant_dcd\" value='decede' id=\"id_decede\"> ".get_translation('DECEASED','décédé')." 
                        </td>
                </tr>
                <tr>
                        <td>".get_translation('PATIENT_AGE_DEATH','Age du patient au décés (ans)')." : ".get_translation('FROM_AGE','de')." <input type=\"text\" size=\"2\" name=\"age_dcd_deb\" id=\"id_age_dcd_deb\"> ".get_translation('TO_DEATH_AGE','à')." <input type=\"text\" size=\"2\" name=\"age_dcd_fin\" id=\"id_age_dcd_fin\">
                        </td>
                </tr>
                <tr>
                        <td>".get_translation('PERIOD_OF_1ST_VISIT','Période de 1ere venue').": ".get_translation('DATE_FROM','du')." <input type=\"text\" id=\"id_input_date_deb_1ervenue\" name=\"date_deb_1ervenue\" class=\"filtre_date_document\" size=\"10\" value=\"\" onblur=\"if (this.value.length==4) {this.value='01/01/'+this.value;}\"> 
                         	".get_translation('DATE_TO','au')." <input type=\"text\" id=\"id_input_date_fin_1ervenue\" name=\"date_fin_1ervenue\" class=\"filtre_date_document\" size=\"10\" value=\"\"  onblur=\"if (this.value.length==4) {this.value='31/12/'+this.value;}\">
                        </td>
                </tr>
                <tr>
                        <td>".get_translation('MINIMUM_FOLLOW_UP','Follow up de minimum')." : <input type=\"text\" id=\"id_input_duree_minimum_prise_en_charge\" name=\"duree_minimum_prise_en_charge\" class=\"filtre_date_document\" size=\"3\" value=\"\">  ".get_translation('YEARS','ans')."</td>
                </tr>
                <tr>
                        <td>".get_translation('EXCLUDE_PATIENTS_FROM_THESE_COHORTS','Exclure les patients de ces cohortes')." : <br>
                <select  id=\"id_select_filtre_cohorte_exclue\" name=\"cohorte_exclue[]\" class=\"chosen-select filtre_unite_heberg\"  data-placeholder=\"Choisissez une ou plusieurs cohortes\" multiple>";
                               display_user_cohorts_option ($user_num_session,'id_select_filtre_cohorte_exclue');
        print "
                  </select> </td>
                </tr>
        </table>
        ";
}

function selection_contrainte_temporelle () {
        global $dbh, $tableau_global_document_origin_code, $tableau_global_contexte,$liste_service_session,$user_num_session;
        print "
        <table class=\"filtre_patient\" border=\"0\">
		<tr><td colspan=\"2\">".get_translation('SELECT_2_SUBQUERIES_AND_CONSTRAINT','Sélectionnez 2 sous requêtes et la contrainte')." :</td></tr>
		<tr><td>".get_translation('FILTER','Filtre')." A</td><td> <select id=\"id_contrainte_select_liste_sous_requete_a\" onchange=\"document.getElementById('id_filtre_beforeafter_a').innerHTML=this.value;\"></select></td></tr>
		<tr><td>".get_translation('FILTER','Filtre')." B</td><td> <select id=\"id_contrainte_select_liste_sous_requete_b\" onchange=\"document.getElementById('id_filtre_beforeafter_b').innerHTML=this.value;\"></select></td></tr>
		<tr><td colspan=\"2\">".get_translation('CONSTRAINTS','Contraintes')." : </td></tr>
		<tr><td colspan=\"2\"><input type=\"radio\" name=\"contrainte_temporelle\" id=\"id_contrainte_type_contrainte_stay\" onclick=\"plier('id_div_detail_contrainte_periode');plier('id_div_detail_contrainte_beforeafter');\"> ".get_translation('SAME_STAY','Durant le même séjour')." </td></tr>
		<tr><td colspan=\"2\"><input type=\"radio\" name=\"contrainte_temporelle\" id=\"id_contrainte_type_contrainte_simultaneous\" onclick=\"plier('id_div_detail_contrainte_periode');plier('id_div_detail_contrainte_beforeafter');\"> ".get_translation('SIMULTANEOUS','simultanés')." </td></tr>
		<tr><td colspan=\"2\"><input type=\"radio\" name=\"contrainte_temporelle\" id=\"id_contrainte_type_contrainte_beforeafter\" onclick=\"deplier('id_div_detail_contrainte_beforeafter','block');plier('id_div_detail_contrainte_periode');\"> ".get_translation('BEFORE_AFTER','Avant / Après')." <br>
			<div id=\"id_div_detail_contrainte_beforeafter\" style=\"display:none\">
				 Filtre <span id=\"id_filtre_beforeafter_a\" class=\"circle_non_float\"></span> ".get_translation('PRECEDED_BY','précède de')." <select id=\"id_detail_contrainte_beforeafter_minmax\"><option value=\"minimum_exclusive\">".get_translation("MINIMUM_STRICT",'minimum strict')."</option><option value=\"minimum\">".get_translation('MINIMUM_LONG','minimum')."</option><option value=\"maximum\">".get_translation('MAXIMUM_LONG','maximum')."</option></select> 
				 <input type=\"text\" size=\"1\" id=\"id_detail_contrainte_beforeafter_duree\"> 
				 <select id=\"id_detail_contrainte_beforeafter_unite\"><option value=\"years\">".get_translation('YEAR','an')."</option><option value=\"months\">".get_translation('MONTH','mois')."</option><option value=\"days\">".get_translation('DAYS','jours')."</option></select>".get_translation('THE_FILTER','le filtre')." 
				  <span id=\"id_filtre_beforeafter_b\" class=\"circle_non_float\"></span>
			</div>
		</td></tr>
		<tr><td colspan=\"2\"><input type=\"radio\" name=\"contrainte_temporelle\" id=\"id_contrainte_type_contrainte_periode\" onclick=\"deplier('id_div_detail_contrainte_periode','block');plier('id_div_detail_contrainte_beforeafter');\"> ".get_translation('PERIOD','Période')." <br>
			<div id=\"id_div_detail_contrainte_periode\" style=\"display:none\">
				 ".get_translation('THE_2_FILTERS_ARE_SEPARATED_BY','Les 2 filtres sont éloignées de')." <select id=\"id_detail_contrainte_periode_minmax\"><option value=\"minimum_exclusive\">".get_translation('MINIMUM_STRICT','minimum strict')."</option><option value=\"minimum\">".get_translation('MINIMUM_LONG','minimum')."</option><option value=\"maximum\">".get_translation('MAXIMUM_LONG','maximum')."</option></select> 
				 <input type=\"text\" size=\"1\" id=\"id_detail_contrainte_periode_duree\"> 
				 <select id=\"id_detail_contrainte_periode_unite\"><option value=\"years\">".get_translation('YEAR','an')."</option><option value=\"months\">".get_translation('MONTH','mois')."</option><option value=\"days\">".get_translation('DAYS','jours')."</option></select>
			</div>
		</td></tr>
		<tr><td colspan=\"2\"><input type=\"button\" value=\"".get_translation('ADD','ajouter')."\" onclick=\"ajouter_contrainte_temporelle();\">&nbsp;&nbsp;&nbsp;<span class=\"erreur\" id=\"id_span_alerte\"></span></td></tr>
        </table>
        ";
}

function ajouter_contrainte_temporelle ($num_filtre,$num_filtre_a,$num_filtre_b,$type_contrainte,$minmax,$unite_contrainte,$duree_contrainte) {
        global $dbh, $tableau_global_document_origin_code, $tableau_global_contexte,$liste_service_session,$user_num_session;
        $res= "
        <div id=\"id_div_filtre_contrainte_temporelle_$num_filtre\" class=\"div_contrainte\">";
	$res.= "<div class=\"circle\">$num_filtre</div>";
	$res.= "<div style=\"position:absolute;left:400px;z-index:22;top:-6;color:grey;cursor:pointer;\" onclick=\"supprimer_formulaire_contrainte_temporelle($num_filtre);\">x</div>";
	$res.= "<div class=\"filtre_patient\"><strong>".get_translation('TEMPORAL_CONSTRAINT','Contrainte temporelle')."</strong><br>";
	
	if ($type_contrainte=='stay') {
		$res.= get_translation('THE_FILTERS','Les filtres')." <div class=\"circle_non_float\">$num_filtre_a</div> ".get_translation('AND','et')." <div class=\"circle_non_float\">$num_filtre_b</div> ".get_translation('ARE_IN_SAME_STAY','sont dans le même séjour')."";
	}
	if ($type_contrainte=='simultaneous') {
		$res.= get_translation('THE_FILTERS','Les filtres')." <div class=\"circle_non_float\">$num_filtre_a</div> ".get_translation('AND','et')." <div class=\"circle_non_float\">$num_filtre_b</div> ".get_translation('ARE_SIMULTANEOUS','sont simultanés')."";
	}
	if ($type_contrainte=='beforeafter') {
		$res.= get_translation('THE_FILTER','Le filtre')." <div class=\"circle_non_float\">$num_filtre_a</div> ".get_translation('PRECEDED_BY','précède de')." $minmax $duree_contrainte $unite_contrainte ".get_translation('THE_FILTER','le filtre')." <div class=\"circle_non_float\">$num_filtre_b</div>";
	}
	
	if ($type_contrainte=='periode') {
		$res.= get_translation('THE_FILTERS','Les filtres')." <div class=\"circle_non_float\">$num_filtre_a</div> ".get_translation('AND','et')." <div class=\"circle_non_float\">$num_filtre_b</div> ".get_translation('ARE_DISTANT_FROM','sont éloignées de')." $minmax $duree_contrainte $unite_contrainte";
	}
	$res.= "<br>
	<span id=\"id_span_nbresult_atomique_$num_filtre\" class=\"filtre_texte_nbresult\" style=\"display:inline;cursor:pointer;\" onclick=\"calcul_nb_resultat_contrainte_temporelle ($num_filtre,true);\">?</span><span id=\"id_span_nbresult_atomique_chargement_$num_filtre\" style=\"display:inline\"></span>
                        <input type=\"hidden\" id=\"id_query_key_contrainte_temporelle_$num_filtre\" name=\"query_key_contrainte_temporelle_$num_filtre\" value=\"$num_filtre;$num_filtre_a;$num_filtre_b;$type_contrainte;$minmax;$unite_contrainte;$duree_contrainte\">
                        <input type=\"hidden\" id=\"id_query_key_$num_filtre\" value=\"\">
                        <input type=\"hidden\" id=\"id_query_type_$num_filtre\" name=\"query_type_$num_filtre\" value=\"contrainte_temporelle\">
                        <input type=\"hidden\" id=\"id_input_nbresult_atomique_$num_filtre\" name=\"contrainte_nbresult_$num_filtre\" value=\"\">
        </div></div>";
        return $res;
}

function ajouter_filtre_texte ($xml) {  
        $info_xml = new SimpleXMLElement($xml);
        $i=0;
        foreach($info_xml->text_filter as $filtre_texte) {
                $num_filtre= trim($filtre_texte->filter_num);
                $query_type= trim($filtre_texte->query_type);
                add_atomic_query ($num_filtre,$query_type);
        }
        foreach($info_xml->mvt_filter as $filtre) {
                $num_filtre= trim($filtre->filter_num);
                $query_type= trim($filtre->query_type);
                add_atomic_query_mvt ($num_filtre,$query_type);
        }
}

function peupler_filtre_texte ($xml) {
	global $max_num_filtre;
        $info_xml = new SimpleXMLElement($xml);
        $i=0;
        foreach($info_xml->text_filter as $filtre_texte) {
                $text= trim($filtre_texte->text);
                $etendre_syno= trim($filtre_texte->synonym_expansion);
                $thesaurus_data_num= trim($filtre_texte->thesaurus_data_num);
                $chaine_requete_code= trim($filtre_texte->str_structured_query);
                $query_type= trim($filtre_texte->query_type);
                $exclure= trim($filtre_texte->exclude);
                $document_origin_code= trim($filtre_texte->document_origin_code);
                $title_document= trim($filtre_texte->title_document);
                $date_deb_document= trim($filtre_texte->document_date_start);
                $date_fin_document= trim($filtre_texte->document_date_end);
                $stay_length_min= trim($filtre_texte->stay_length_min);
                $stay_length_max= trim($filtre_texte->stay_length_max);
                $document_last_nb_days= trim($filtre_texte->document_last_nb_days);
                $periode_document= trim($filtre_texte->period_document);
                $age_deb_document= trim($filtre_texte->document_ageyear_start);
                $age_fin_document= trim($filtre_texte->document_ageyear_end);
                $agemois_deb_document= trim($filtre_texte->document_agemonth_start);
                $agemois_fin_document= trim($filtre_texte->document_agemonth_end);
                $hospital_department_list= trim($filtre_texte->hospital_department_list);
                $context=trim( $filtre_texte->context);
                $certainty=trim( $filtre_texte->certainty);
                $texte_nbresult=trim( $filtre_texte->count_result);
                $num_filtre= trim($filtre_texte->filter_num);
                if ($max_num_filtre<$num_filtre) {
                        $max_num_filtre=$num_filtre;
                }
                if ($query_type=='text' || $query_type=='texte' || $query_type=='') {
	                $javascript.="
	                        jQuery('#id_input_filtre_texte_$num_filtre').val(\"$text\"); ";
	        }
                if ($query_type=='code') {
	        	$javascript.="ajouter_formulaire_code($num_filtre,'$thesaurus_data_num');";
	                $javascript.="jQuery('#id_input_filtre_texte_$num_filtre').val(\"$text\"); ";
	        	
	        	$javascript.=analyse_chaine_requete_code ($thesaurus_data_num,$chaine_requete_code,'javascript',$num_filtre);
	        }
                        
                if ($exclure==1) {
                	//$javascript.="document.getElementById('id_input_filtre_exclure_$num_filtre').checked=true;";
                        $javascript.="jQuery('#id_input_filtre_exclure_$num_filtre').prop('checked',true);";
                } else {
                        $javascript.="jQuery('#id_input_filtre_exclure_$num_filtre').prop('checked',false);";
                }
                if ($etendre_syno==1) {
                	//$javascript.="document.getElementById('id_checkbox_etendre_syno_$num_filtre').checked=true;";
                        $javascript.="jQuery('#id_checkbox_etendre_syno_$num_filtre').prop('checked',true);";
                } else {
                        $javascript.="jQuery('#id_checkbox_etendre_syno_$num_filtre').prop('checked',false);";
                }
                if ($texte_nbresult=='') {
                	$span_nbresult='?';
                } else {
                	$span_nbresult=$texte_nbresult;
                }
                $javascript.="
                        jQuery('#id_input_filtre_title_document_$num_filtre').val(\"$title_document\");
                        jQuery('#id_input_filtre_date_deb_document_$num_filtre').val(\"$date_deb_document\");
                        jQuery('#id_input_filtre_date_fin_document_$num_filtre').val(\"$date_fin_document\");
                        jQuery('#id_input_filtre_stay_length_min_$num_filtre').val(\"$stay_length_min\");
                        jQuery('#id_input_filtre_stay_length_max_$num_filtre').val(\"$stay_length_max\");
                        jQuery('#id_input_filtre_document_last_nb_days_$num_filtre').val(\"$document_last_nb_days\");
                        jQuery('#id_input_filtre_periode_document_$num_filtre').val(\"$periode_document\");
                        jQuery('#id_input_filtre_age_deb_document_$num_filtre').val(\"$age_deb_document\");
                        jQuery('#id_input_filtre_age_fin_document_$num_filtre').val(\"$age_fin_document\");
                        jQuery('#id_input_filtre_agemois_deb_document_$num_filtre').val(\"$agemois_deb_document\");
                        jQuery('#id_input_filtre_agemois_fin_document_$num_filtre').val(\"$agemois_fin_document\");
                        jQuery('#id_select_filtre_contexte_$num_filtre').val(\"$context\");
                        jQuery('#id_select_filtre_certitude_$num_filtre').val(\"$certainty\");
                        jQuery('#id_input_filtre_num_filtre_$num_filtre').val(\"$num_filtre\");
                        jQuery('#id_input_nbresult_atomique_$num_filtre').val(\"$texte_nbresult\");
                        jQuery('#id_span_nbresult_atomique_$num_filtre').html(\"$span_nbresult\");
                ";
                
                $javascript.="jQuery('#id_select_filtre_unite_heberg_$num_filtre').val('');";
                $tableau_hospital_department_list=array();
                $tableau_hospital_department_list=explode(',',$hospital_department_list);
                foreach ($tableau_hospital_department_list as $department_num) {
                        if ($department_num!='') {
                                //$javascript.="document.getElementById('id_option_filtre_unite_heberg_".$num_filtre."_".$department_num."').selected=true;";
                                $javascript.="jQuery('#id_option_filtre_unite_heberg_".$num_filtre."_".$department_num."').prop('selected',true);";
                        }
                }
                
                $javascript.="jQuery('#id_select_filtre_document_origin_code_$num_filtre').val('');";
                $tableau_document_origin_code=array();
                $tableau_document_origin_code=explode(',',$document_origin_code);
                foreach ($tableau_document_origin_code as $document_origin_code) {
                        if ($document_origin_code!='') {
                                //$javascript.="document.getElementById('id_option_filtre_document_origin_code_".$num_filtre."_".$document_origin_code."').selected=true;";
                                $javascript.="jQuery('#id_option_filtre_document_origin_code_".$num_filtre."_".$document_origin_code."').prop('selected',true);";
                        }
                }
                
                if ($query_type=='code') {
	        	$javascript.="creer_chaine_requete_code ($num_filtre);";
	        }
	        
	        if ($exclure!='' ||
		        $age_deb_document!='' ||
		        $age_fin_document!='' ||
		        $agemois_deb_document!='' ||
		        $agemois_fin_document!='' ||
		        $title_document!='' ||
		        $date_deb_document!='' ||
		        $date_fin_document!='' ||
		        $stay_length_min!='' ||
		        $stay_length_max!='' ||
		        $document_last_nb_days!='' ||
		        $periode_document!='' ||
		        $document_origin_code!='' ||
		        $context!='' ||
		        $hospital_department_list!='' ||
		        $certainty!='1'
		        ) {
	        	$javascript.="jQuery('#id_a_filtre_texte_ouvrir_avance_".$num_filtre."').css('color','red');";
	        }
	        
	        //NG 2019 11 23  because already executed in creer_chaine_requete_code //
                if ($query_type!='code') {
        		$javascript.="calcul_nb_resultat_filtre($num_filtre,true);";
        	}
                
        }
        
        print "$javascript";
}



function peupler_filtre_mvt ($xml) {
	global $max_num_filtre;
        $info_xml = new SimpleXMLElement($xml);
        $i=0;
        foreach($info_xml->mvt_filter as $mvt_filter) {
        
                $query_type= trim($mvt_filter->query_type);
                $mvt_department= trim($mvt_filter->mvt_department);
                $mvt_unit= trim($mvt_filter->mvt_unit);
                $type_mvt= trim($mvt_filter->type_mvt);
                $encounter_duration_min= trim($mvt_filter->encounter_duration_min);
                $encounter_duration_max= trim($mvt_filter->encounter_duration_max);
                $mvt_duration_min= trim($mvt_filter->mvt_duration_min);
                $mvt_duration_max= trim($mvt_filter->mvt_duration_max);
                
                $mvt_nb_min= trim($mvt_filter->mvt_nb_min);
                $mvt_nb_max= trim($mvt_filter->mvt_nb_max);
                
                $stay_nb_min= trim($mvt_filter->stay_nb_min);
                $stay_nb_max= trim($mvt_filter->stay_nb_max);
                
                $exclure= trim($mvt_filter->exclude);
                
                $mvt_last_nb_days= trim($mvt_filter->mvt_last_nb_days);
                
                $mvt_date_start= trim($mvt_filter->mvt_date_start);
                $mvt_date_end= trim($mvt_filter->mvt_date_end);
                $mvt_ageyear_start= trim($mvt_filter->mvt_ageyear_start);
                $mvt_ageyear_end= trim($mvt_filter->mvt_ageyear_end);
                $mvt_agemonth_start= trim($mvt_filter->mvt_agemonth_start);
                $mvt_agemonth_end= trim($mvt_filter->mvt_agemonth_end);
                $num_filtre= trim($mvt_filter->filter_num);
                if ($max_num_filtre<$num_filtre) {
                        $max_num_filtre=$num_filtre;
                }
                
                $mvt_nbresult=trim( $mvt_filter->count_result);
        
                if ($exclure==1) {
                        $javascript.="jQuery('#id_input_filtre_exclure_$num_filtre').prop('checked',true);";
                } else {
                        $javascript.="jQuery('#id_input_filtre_exclure_$num_filtre').prop('checked',false);";
                }
                if ($mvt_nbresult=='') {
                	$span_nbresult='?';
                } else {
                	$span_nbresult=$mvt_nbresult;
                }
                $javascript.="
                        jQuery('#id_select_filtre_mvt_department_$num_filtre').val(\"$mvt_department\");
                        jQuery('#id_select_filtre_mvt_unit_$num_filtre').val(\"$mvt_unit\");
                        jQuery('#id_select_filtre_type_mvt_$num_filtre').val(\"$type_mvt\");
                        jQuery('#id_input_filtre_encounter_duration_min_$num_filtre').val(\"$encounter_duration_min\");
                        jQuery('#id_input_filtre_encounter_duration_max_$num_filtre').val(\"$encounter_duration_max\");
                        jQuery('#id_input_filtre_mvt_duration_min_$num_filtre').val(\"$mvt_duration_min\");
                        jQuery('#id_input_filtre_mvt_duration_max_$num_filtre').val(\"$mvt_duration_max\");
                        jQuery('#id_input_filtre_mvt_nb_min_$num_filtre').val(\"$mvt_nb_min\");
                        jQuery('#id_input_filtre_mvt_nb_max_$num_filtre').val(\"$mvt_nb_max\");
                        jQuery('#id_input_filtre_stay_nb_min_$num_filtre').val(\"$stay_nb_min\");
                        jQuery('#id_input_filtre_stay_nb_max_$num_filtre').val(\"$stay_nb_max\");
                        jQuery('#id_input_filtre_mvt_last_nb_days_$num_filtre').val(\"$mvt_last_nb_days\");
                        jQuery('#id_input_filtre_mvt_date_start_$num_filtre').val(\"$mvt_date_start\");
                        jQuery('#id_input_filtre_mvt_date_end_$num_filtre').val(\"$mvt_date_end\");
                        jQuery('#id_input_filtre_mvt_ageyear_start_$num_filtre').val(\"$mvt_ageyear_start\");
                        jQuery('#id_input_filtre_mvt_ageyear_end_$num_filtre').val(\"$mvt_ageyear_end\");
                        jQuery('#id_input_filtre_mvt_agemonth_start_$num_filtre').val(\"$mvt_agemonth_start\");
                        jQuery('#id_input_filtre_mvt_agemonth_end_$num_filtre').val(\"$mvt_agemonth_end\");
                        
                        jQuery('#id_input_filtre_num_filtre_$num_filtre').val(\"$num_filtre\");
                        
                        jQuery('#id_input_nbresult_atomique_$num_filtre').val(\"$mvt_nbresult\");
                        jQuery('#id_span_nbresult_atomique_$num_filtre').html(\"$span_nbresult\");
                ";
       		$javascript.="calcul_nb_resultat_filtre_mvt($num_filtre,true);";
        }
        
        print "
	$javascript
	$('.chosen-select').chosen({width: '250px',max_selected_options: 50,allow_single_deselect: true,search_contains:true}); 
	$('.autosizejs').autosize();   
	$('.chosen-select').trigger('chosen:updated');
	";
}


function peupler_contrainte_temporelle ($xml) {
	global $max_num_filtre;
        $info_xml = new SimpleXMLElement($xml);
        $i=0;
        foreach($info_xml->time_constraint as $contrainte_temporelle) {
                $num_filtre= trim($contrainte_temporelle->filter_num);
                $num_filtre_a= trim($contrainte_temporelle->time_filter_num_a);
                $num_filtre_b= trim($contrainte_temporelle->time_filter_num_b);
                $type_contrainte= trim($contrainte_temporelle->time_constraint_type);
                $minmax= trim($contrainte_temporelle->minmax);
                $unite_contrainte= trim($contrainte_temporelle->time_constraint_unit);
                $duree_contrainte= trim($contrainte_temporelle->time_constraint_duration);
                if ($max_num_filtre<$num_filtre) {
                        $max_num_filtre=$num_filtre;
                }
	        print "ajouter_contrainte_temporelle_auto ('$num_filtre','$num_filtre_a','$num_filtre_b','$type_contrainte','$minmax','$unite_contrainte','$duree_contrainte');";
        	print "calcul_nb_resultat_contrainte_temporelle($num_filtre,true);";
        }
}

function peupler_filtre_patient ($xml) {
        $info_xml = new SimpleXMLElement($xml);
        $javascript="";
        $filtre_patient='';
        $javascript.="jQuery('#id_sex').val('');";
        $javascript.="jQuery('#id_age_deb').val('');";
        $javascript.="jQuery('#id_age_fin').val('');";
        $javascript.="jQuery('#id_vivant').prop('checked',false);";
        $javascript.="jQuery('#id_decede').prop('checked',false);";
	$javascript.="jQuery('#id_age_dcd_deb').val('');";
	$javascript.="jQuery('#id_age_dcd_fin').val('');";
	$javascript.="jQuery('#id_input_date_deb_1ervenue').val('');";
	$javascript.="jQuery('#id_input_date_fin_1ervenue').val('');";
	$javascript.="jQuery('#id_input_duree_minimum_prise_en_charge').val('');";
	$javascript.="jQuery('#id_select_filtre_cohorte_exclue').val('');";
	
        if ($info_xml->sex) {
                $sex=$info_xml->sex;
       		if ($sex!='') {
                	$javascript.="jQuery('#id_sex').val(\"$sex\");";
	                $filtre_patient='ok';
	        }
        }
        
        if ($info_xml->age_start) {
                $age_deb=$info_xml->age_start;
       		if ($age_deb!='') {
	                $javascript.="jQuery('#id_age_deb').val(\"$age_deb\");";
	                $filtre_patient='ok';
	        }
        }
        
        if ($info_xml->age_end) {
                $age_fin=$info_xml->age_end;
       		if ($age_fin!='') {
	                $javascript.="jQuery('#id_age_fin').val(\"$age_fin\");";
	                $filtre_patient='ok';
	        }
        }
        
        
        if ($info_xml->alive_death) {
                $vivant_dcd=$info_xml->alive_death;
       		if ($vivant_dcd=='vivant') {
                        $javascript.="jQuery('#id_vivant').prop('checked',true);";
	                $filtre_patient='ok';
	        }
       		if ($vivant_dcd=='decede') {
                        $javascript.="jQuery('#id_decede').prop('checked',true);";
	                $filtre_patient='ok';
	        }
        }
        if ($info_xml->age_death_start) {
                $age_dcd_deb=$info_xml->age_death_start;
       		if ($age_dcd_deb!='') {
	                $javascript.="jQuery('#id_age_dcd_deb').val(\"$age_dcd_deb\");";
	                $filtre_patient='ok';
	        }
        }
        if ($info_xml->age_death_end) {
                $age_dcd_fin=$info_xml->age_death_end;
       		if ($age_dcd_fin!='') {
	                $javascript.="jQuery('#id_age_dcd_fin').val(\"$age_dcd_fin\");";
	                $filtre_patient='ok';
	        }
        }
        
        
        if ($info_xml->first_stay_date_start) {
                $date_deb_1ervenue=$info_xml->first_stay_date_start;
       		if ($date_deb_1ervenue!='') {
	                $javascript.="jQuery('#id_input_date_deb_1ervenue').val(\"$date_deb_1ervenue\");";
	                $filtre_patient='ok';
	        }
        }
        if ($info_xml->first_stay_date_end) {
                $date_fin_1ervenue=$info_xml->first_stay_date_end;
       		if ($date_fin_1ervenue!='') {
	                $javascript.="jQuery('#id_input_date_fin_1ervenue').val(\"$date_fin_1ervenue\");";
	                $filtre_patient='ok';
	        }
        }
        if ($info_xml->minimum_period_folloup) {
                $duree_minimum_prise_en_charge=$info_xml->minimum_period_folloup;
       		if ($duree_minimum_prise_en_charge!='') {
	                $javascript.="jQuery('#id_input_duree_minimum_prise_en_charge').val(\"$duree_minimum_prise_en_charge\");";
	                $filtre_patient='ok';
	        }
        }
        
        
        if ($info_xml->list_excluded_cohort) {
		$liste_cohorte_exclue= trim($info_xml->list_excluded_cohort);
	        $tableau_cohorte_exclue=array();
	        $tableau_cohorte_exclue=explode(',',$liste_cohorte_exclue);
	        foreach ($tableau_cohorte_exclue as $cohorte_exclue) {
	                if ($cohorte_exclue!='') {
                       		$javascript.="jQuery('#id_select_filtre_cohorte_exclue_$cohorte_exclue').prop('selected',true);";
	                        //$javascript.="document.getElementById('id_select_filtre_cohorte_exclue_$cohorte_exclue').selected=true;";
             			$filtre_patient='ok';
	                }
	        }
	}
	
	if ($filtre_patient=='ok') {
		$javascript.="deplier('id_div_formulaire_patient','block');";
	}
        print "
	$javascript
	$('.chosen-select').chosen({width: '250px',max_selected_options: 50,allow_single_deselect: true,search_contains:true}); 
	$('.autosizejs').autosize();   
	$('.chosen-select').trigger('chosen:updated');
	";
}


function creer_requete_sql_filtre_passthru ($xml) {
        global $dbh,$liste_uf_session,$user_num_session,$CHEMIN_GLOBAL_LOG;
        $requete_sql='';
        if ($xml!='') {
		list($query_key,$datamart_num,$select_sql)=creer_requete_sql ($xml);
		if ($query_key!='') {
			//$sel = oci_parse($dbh,"select count(*) as  NB from DWH_TMP_PRERESULT_$user_num_session  where query_key='$query_key' and trunc(tmpresult_date)=trunc(sysdate) and datamart_num=$datamart_num ");   
			$sel = oci_parse($dbh,"select count(*) as  NB from dwh_tmp_query where  query_key='$query_key' and user_num=$user_num_session and datamart_num=$datamart_num ");   
			oci_execute($sel);
			$r = oci_fetch_array($sel, OCI_ASSOC);
			$nb=$r['NB'];
	                if ($nb==0) {
				/// sauvegarder la requete
	                        $requeteins="insert into dwh_tmp_query (query_key, sql_clob ,datamart_num , user_num , status_calculate,query_date) values ('$query_key', :select_sql ,'$datamart_num' , '$user_num_session' , 0,sysdate)";
	                        $stmt = ociparse($dbh,$requeteins);
	                        $rowid = ocinewdescriptor($dbh, OCI_D_LOB);
	                        ocibindbyname($stmt, ":select_sql",$select_sql);
	                        $execState = ociexecute($stmt);
	                        ocifreestatement($stmt);
	                        
				passthru( "php passthru_result_temp.php $user_num_session $datamart_num \"$query_key\"  >> $CHEMIN_GLOBAL_LOG/log_chargement_result_temp.$user_num_session.$datamart_num.txt 2>&1 &");
	                } else {
	                        $sel = oci_parse($dbh,"select count(distinct patient_num) as  NB from DWH_TMP_PRERESULT_$user_num_session where query_key='$query_key' and trunc(tmpresult_date)=trunc(sysdate) and datamart_num=$datamart_num");   
	                        oci_execute($sel);
	                        $r = oci_fetch_array($sel, OCI_ASSOC);
	                        $nb_patient=$r['NB'];
	                        
	                        $sel = oci_parse($dbh,"select count(*) as  NB from dwh_tmp_query where query_key='$query_key' and trunc(query_date)=trunc(sysdate) and datamart_num=$datamart_num and user_num=$user_num_session");   
	                        oci_execute($sel);
	                        $r = oci_fetch_array($sel, OCI_ASSOC);
	                        $test_requete=$r['NB'];
	                        if ($test_requete==0) {
		                        $requeteins="insert into dwh_tmp_query (query_key, sql_clob ,datamart_num , user_num , status_calculate,query_date,count_patient) values ('$query_key', :select_sql ,'$datamart_num' , '$user_num_session', 1,sysdate,$nb_patient)";
		                        $stmt = ociparse($dbh,$requeteins);
		                        $rowid = ocinewdescriptor($dbh, OCI_D_LOB);
		                        ocibindbyname($stmt, ":select_sql",$select_sql);
		                        $execState = ociexecute($stmt);
		                        ocifreestatement($stmt);
	                        }
	                }
	        }
        }
        return ($query_key);
}


function creer_requete_sql_filtre_mvt_passthru ($xml) {
        global $dbh,$liste_uf_session,$user_num_session,$CHEMIN_GLOBAL_LOG;
        $requete_sql='';
        if ($xml!='') {
		list($query_key,$datamart_num,$select_sql)=creer_requete_sql_mvt ($xml);
		if ($query_key!='') {
			//$sel = oci_parse($dbh,"select count(*) as  NB from DWH_TMP_PRERESULT_$user_num_session  where query_key='$query_key' and trunc(tmpresult_date)=trunc(sysdate) and datamart_num=$datamart_num ");   
			$sel = oci_parse($dbh,"select count(*) as  NB from dwh_tmp_query where  query_key='$query_key' and user_num=$user_num_session and datamart_num=$datamart_num ");   
			oci_execute($sel);
			$r = oci_fetch_array($sel, OCI_ASSOC);
			$nb=$r['NB'];
	                if ($nb==0) {
				/// sauvegarder la requete
	                        $requeteins="insert into dwh_tmp_query (query_key, sql_clob ,datamart_num , user_num , status_calculate,query_date) values ('$query_key', :select_sql ,'$datamart_num' , '$user_num_session' , 0,sysdate)";
	                        $stmt = ociparse($dbh,$requeteins);
	                        $rowid = ocinewdescriptor($dbh, OCI_D_LOB);
	                        ocibindbyname($stmt, ":select_sql",$select_sql);
	                        $execState = ociexecute($stmt);
	                        ocifreestatement($stmt);
	                        
				passthru( "php passthru_result_temp.php $user_num_session $datamart_num \"$query_key\"  >> $CHEMIN_GLOBAL_LOG/log_chargement_result_temp.$user_num_session.$datamart_num.txt 2>&1 &");
	                } else {
	                        $sel = oci_parse($dbh,"select count(distinct patient_num) as  NB from DWH_TMP_PRERESULT_$user_num_session where query_key='$query_key' and trunc(tmpresult_date)=trunc(sysdate) and datamart_num=$datamart_num");   
	                        oci_execute($sel);
	                        $r = oci_fetch_array($sel, OCI_ASSOC);
	                        $nb_patient=$r['NB'];
	                        
	                        $sel = oci_parse($dbh,"select count(*) as  NB from dwh_tmp_query where query_key='$query_key' and trunc(query_date)=trunc(sysdate) and datamart_num=$datamart_num and user_num=$user_num_session");   
	                        oci_execute($sel);
	                        $r = oci_fetch_array($sel, OCI_ASSOC);
	                        $test_requete=$r['NB'];
	                        if ($test_requete==0) {
		                        $requeteins="insert into dwh_tmp_query (query_key, sql_clob ,datamart_num , user_num , status_calculate,query_date,count_patient) values ('$query_key', :select_sql ,'$datamart_num' , '$user_num_session', 1,sysdate,$nb_patient)";
		                        $stmt = ociparse($dbh,$requeteins);
		                        $rowid = ocinewdescriptor($dbh, OCI_D_LOB);
		                        ocibindbyname($stmt, ":select_sql",$select_sql);
		                        $execState = ociexecute($stmt);
		                        ocifreestatement($stmt);
	                        }
	                }
	        }
        }
        return ($query_key);
}




function creer_requete_sql_filtre_mvt ($xml,$option) {
        global $dbh,$liste_uf_session,$user_num_session;
        $requete_sql='';
        if ($xml!='') {
		list($query_key,$datamart_num,$select_sql)=creer_requete_sql_mvt ($xml);
                if ($query_key!='') {
                        $sel = oci_parse($dbh,"select count(*) as  NB from DWH_TMP_PRERESULT_$user_num_session where query_key='$query_key' and trunc(tmpresult_date)=trunc(sysdate) and datamart_num=$datamart_num ");   
                        oci_execute($sel);
                        $r = oci_fetch_array($sel, OCI_ASSOC);
                        $nb=$r['NB'];
                        
                        if ($nb==0) {
				$select_sql=preg_replace("/to_char\(entry_date,'DD\/MM\/YYYY HH24:MI'\) *as *document_date/","entry_date",$select_sql);
				$sel = oci_parse($dbh, " insert  /*+ APPEND */ into DWH_TMP_PRERESULT_$user_num_session (document_num ,query_key , tmpresult_date,patient_num,user_num,datamart_num,document_origin_code,document_date,object_type)  $select_sql  ");   
				oci_execute($sel);
                        }
                        
                        if ($option=='patient_num') {
                                $requete_sql=" query_key='$query_key'  "; // a voir si on supprime le filtre sur user_num ... garcelon
                        }
                }
                if ($datamart_num==0) {
                        
                } else {
                        if ($option=='patient_num') {
                                 $requete_sql.=" and datamart_num=$datamart_num  ";
				$select_sql=preg_replace("/to_char\(entry_date,'DD\/MM\/YYYY HH24:MI'\) *as *document_date/i","entry_date",$select_sql);
				$sel = oci_parse($dbh, " insert  /*+ APPEND */ into DWH_TMP_PRERESULT_$user_num_session (document_num ,query_key , tmpresult_date,patient_num,user_num,datamart_num,document_origin_code,document_date,object_type)  $select_sql  ");   
				oci_execute($sel);
                        }
                }
        }
        return $requete_sql;
}


function creer_requete_sql ($xml) {
        global $dbh,$liste_uf_session,$user_num_session;
        $requete_sql='';
        $query_key='';
        $datamart_num='';
        if ($xml!='') {
                $info_xml = new SimpleXMLElement($xml);
                $text= trim($info_xml->text);
                $query_type= trim($info_xml->query_type);
                $thesaurus_data_num= trim($info_xml->thesaurus_data_num);
                $chaine_requete_code= trim($info_xml->str_structured_query);
                $etendre_syno= trim($info_xml->synonym_expansion);
                
                $exclure= trim($info_xml->exclude);
                $document_origin_code= trim($info_xml->document_origin_code);
                $title_document= trim($info_xml->title_document);
                $date_deb_document= trim($info_xml->document_date_start);
                $date_fin_document= trim($info_xml->document_date_end);
	        $stay_length_min= trim($info_xml->stay_length_min);
	        $stay_length_max= trim($info_xml->stay_length_max);
                $document_last_nb_days= trim($info_xml->document_last_nb_days);
                $periode_document= trim($info_xml->period_document);
                $age_deb_document= trim($info_xml->document_ageyear_start);
                $age_fin_document= trim($info_xml->document_ageyear_end);
                $agemois_deb_document= trim($info_xml->document_agemonth_start);
                $agemois_fin_document= trim($info_xml->document_agemonth_end);
                $hospital_department_list= trim($info_xml->hospital_department_list);
                $context= trim($info_xml->context);
                $certainty= trim($info_xml->certainty);
                $datamart_num= trim($info_xml->datamart_text_num);
                if ($certainty=='') {
                        $certainty=1;
                }
                $text=nettoyer_pour_requete($text);
		$filtre_sql='';
                
		if ($datamart_num=='') {
			$datamart_num=0;
		}
                if ($context=='') {
                        $context='patient_text';
                }
		$query_key=recup_query_key_depuis_xml ($xml);
		$query_select_base="select  document_num,'$query_key' as query_key,sysdate as query_date,patient_num,$user_num_session as user_num,$datamart_num as datamart_num,document_origin_code,to_char(document_date,'DD/MM/YYYY HH24:MI') as document_date,'document' as object_type";
                if ($text!='' ||  $chaine_requete_code!='') {
                        if ($hospital_department_list!='') {
                                $filtre_sql.=" and department_num in ($hospital_department_list)  ";
                        }
                        if ($document_origin_code!='') {
                        	$req_document_origin_code=str_replace(",","','",$document_origin_code);
                                $filtre_sql.=" and document_origin_code in ('$req_document_origin_code') ";
                        }
                        if ($title_document!='') {
                        	$req_title_document=str_replace("'"," ",$title_document);
                        	$req_title_document=str_replace("\""," ",$title_document);
                        	$req_title_document=trim($req_title_document);
                        	if (preg_match("/ /i",$req)) {
                                $filtre_sql.=" and  contains(title,'$req_title_document')>0 ";
                        	} else {
                                $filtre_sql.=" and  CONVERT(upper(title), 'US7ASCII')  like '%'|| CONVERT(upper('$req_title_document'), 'US7ASCII') || '%' ";
                        	}
                        }
                        if ($date_deb_document!='') {
                                $filtre_sql.=" and document_date>=to_date('$date_deb_document','DD/MM/YYYY') ";
                        }
                        if ($date_fin_document!='') {
                                $filtre_sql.=" and document_date<=to_date('$date_fin_document','DD/MM/YYYY') ";
                        }
                        if ($stay_length_min!='' || $stay_length_max!='') {
                        	if ($stay_length_min!='' &&  $stay_length_max=='') {
                                	$filtre_sql.=" and encounter_num is not null and encounter_num in (select encounter_num from dwh_patient_stay where out_date-entry_date>=$stay_length_min and out_date is not null and entry_date is not null)  ";
                        	} 
                        	if ($stay_length_min=='' &&  $stay_length_max!='') {
					$filtre_sql.=" and encounter_num is not null and encounter_num in (select encounter_num from dwh_patient_stay where out_date-entry_date<=$stay_length_max  and out_date is not null and entry_date is not null)  ";
                        	} 
                        	if ($stay_length_min!='' &&  $stay_length_max!='') {
                                	$filtre_sql.=" and encounter_num is not null and encounter_num in (select encounter_num from dwh_patient_stay where out_date-entry_date>=$stay_length_min and  out_date-entry_date<=$stay_length_max and out_date is not null and entry_date is not null)  ";
                        	} 
                        }
                        
                        if ($document_last_nb_days!='') {
                                $filtre_sql.=" and document_date>=sysdate-$document_last_nb_days";
                        }
                        if ($age_deb_document!='') {
                                $filtre_sql.=" and trunc(age_patient)>='$age_deb_document' ";
                        }
                        if ($age_fin_document!='') {
                                $filtre_sql.=" and trunc(age_patient)<='$age_fin_document' ";
                        }
                        if ($agemois_deb_document!='') {
                                $filtre_sql.=" and trunc(age_patient*12)>=$agemois_deb_document ";
                        }
                        if ($agemois_fin_document!='') {
                                $filtre_sql.=" and trunc(age_patient*12)<=$agemois_fin_document ";
                        }
                        
                        
			if ($text!='') {

				if ($context=='text') {
					$requete_contexte="";
				} else {
					$requete_contexte=" and dwh_text.context='$context'";
				}
				if ($certainty=='0') {
					//$requete_certitude=" and dwh_text.certainty>=-1 ";
					$requete_certitude="";
				} else {
					$requete_certitude=" and dwh_text.certainty=$certainty";
				}
				if ($datamart_num!=0) {
					$query_datamart=" exists (select patient_num from dwh_datamart_result where datamart_num = $datamart_num and dwh_datamart_result.patient_num=dwh_text.patient_num) and ";
				}
       				$where='';
	                        if ($filtre_sql!='') {
	                        	$where="where 1=1 ";
	                        }
				if ($etendre_syno==1) {
	               			if ($datamart_num==0) {
	               				$select_sql="$query_select_base from 
	               				(select  document_num,patient_num,document_origin_code,document_date,age_patient,title,department_num,encounter_num from dwh_text  where contains(dwh_text.enrich_text,'$text')>0 $requete_contexte $requete_certitude) t $where $filtre_sql   ";
	               				 $select_sql_id_document="select document_num from (select document_num from dwh_text where contains(dwh_text.enrich_text,'$text')>0 $requete_contexte $requete_certitude)  $where $filtre_sql   ";
		                        } else {
	               				$select_sql="$query_select_base from 
	               				(select  document_num,patient_num,document_origin_code,document_date,age_patient,title,department_num,encounter_num from dwh_text where $query_datamart contains(dwh_text.enrich_text,'$text')>0  $requete_contexte $requete_certitude ) t $where  $filtre_sql   ";
                                                
	               				$select_sql_id_document="select document_num from (select document_num from dwh_text where $query_datamart contains(dwh_text.enrich_text,'$text')>0 $requete_contexte $requete_certitude) t $where  $filtre_sql";
		                        }
		                } else {
	               			if ($datamart_num==0) {
	               				$select_sql="$query_select_base from 
	               				(select  document_num,patient_num,document_origin_code,document_date,age_patient,title,department_num,encounter_num from dwh_text  where contains(dwh_text.text,'$text')>0 $requete_contexte $requete_certitude) t $where $filtre_sql   ";
	               				 $select_sql_id_document="select document_num from (select document_num from dwh_text where contains(dwh_text.text,'$text')>0 $requete_contexte $requete_certitude) t $where $filtre_sql   ";
		                        } else {
	               				$select_sql="$query_select_base from 
	               				(select  document_num,patient_num,document_origin_code,document_date,age_patient,title,department_num,encounter_num from dwh_text  where $query_datamart contains(dwh_text.text,'$text')>0 $requete_contexte $requete_certitude) t $where   $filtre_sql   ";
	               				 $select_sql_id_document="select document_num from (select document_num from dwh_text where  $query_datamart contains(dwh_text.text,'$text')>0 $requete_contexte $requete_certitude) t $where   $filtre_sql   ";
		                        }
		                }
                        }
			if ($chaine_requete_code!='') {
				$requete_code=analyse_chaine_requete_code ($thesaurus_data_num,$chaine_requete_code,'sql','');
               			if ($datamart_num==0) {
		                        $select_sql="$query_select_base
	                                                from dwh_data
	                                                where $requete_code  $filtre_sql  ";
	               			$select_sql_id_document="select document_num from dwh_data where $requete_code  $filtre_sql ";
	                        } else {
	                                                
		                        $select_sql="$query_select_base
	                                                from dwh_data
	                                                where  exists (select patient_num from dwh_datamart_result where datamart_num = $datamart_num and dwh_datamart_result.patient_num=dwh_data.patient_num)  and $requete_code  $filtre_sql";
	               			$select_sql_id_document="select document_num from dwh_data  where  exists (select patient_num from dwh_datamart_result where datamart_num = $datamart_num and dwh_datamart_result.patient_num=dwh_data.patient_num)  and $requete_code  $filtre_sql ";
	                        }
			}
			if ($periode_document!='') {
       				$where='';
	                        if ($filtre_sql=='') {
	                        	$where="where ";
	                        } else {
	                        	$where="and ";
	                        }
				$periode_document=preg_replace("/\./",",","$periode_document");
				$select_sql="$select_sql $where  patient_num in  
                                                        (
                                                        select patient_num from dwh_document where  document_date is not null
                                                        and document_num in ($select_sql_id_document)
                                                         having(max(document_date)-min(document_date))>365*'$periode_document' group by patient_num
                                                        )";
			}
			
			//$select_sql.="and patient_num not in (select patient_num from DWH_PATIENT_OPPOSITION)";
        	} else {
			if ($datamart_num!='0') {
	                        $select_sql="$query_select_base from dwh_document where exists (select patient_num from dwh_datamart_result where datamart_num = $datamart_num  and dwh_datamart_result.patient_num=dwh_document.patient_num)  ";
			//	$select_sql.="and patient_num not in (select patient_num from DWH_PATIENT_OPPOSITION)";
			}
        	}
        }
        return array($query_key,$datamart_num,$select_sql);
}


function creer_requete_sql_mvt ($xml) {
        global $dbh,$liste_uf_session,$user_num_session;
        $requete_sql='';
        $query_key='';
        $datamart_num='';
        if ($xml!='') {
                $info_xml = new SimpleXMLElement($xml);
                             
                $query_type= trim($info_xml->query_type);
                $mvt_department= trim($info_xml->mvt_department);
                $mvt_unit= trim($info_xml->mvt_unit);
                $type_mvt= trim($info_xml->type_mvt);
                $encounter_duration_min= trim($info_xml->encounter_duration_min);
                $encounter_duration_max= trim($info_xml->encounter_duration_max);
                $mvt_duration_min= trim($info_xml->mvt_duration_min);
                $mvt_duration_max= trim($info_xml->mvt_duration_max);
                
                $mvt_nb_min= trim($info_xml->mvt_nb_min);
                $mvt_nb_max= trim($info_xml->mvt_nb_max);
                
                $stay_nb_min= trim($info_xml->stay_nb_min);
                $stay_nb_max= trim($info_xml->stay_nb_max);
                
                $exclure= trim($info_xml->exclude);
                
                $mvt_last_nb_days= trim($info_xml->mvt_last_nb_days);
                
                $mvt_date_start= trim($info_xml->mvt_date_start);
                $mvt_date_end= trim($info_xml->mvt_date_end);
                $mvt_ageyear_start= trim($info_xml->mvt_ageyear_start);
                $mvt_ageyear_end= trim($info_xml->mvt_ageyear_end);
                $mvt_agemonth_start= trim($info_xml->mvt_agemonth_start);
                $mvt_agemonth_end= trim($info_xml->mvt_agemonth_end);
                
                $datamart_num= trim($info_xml->datamart_text_num);

		$filtre_sql='';
                
		if ($datamart_num=='') {
			$datamart_num=0;
		}
		$query_key="$query_type;$mvt_department;$mvt_unit;$type_mvt;$encounter_duration_min;$encounter_duration_max;$mvt_duration_min;$mvt_duration_max;$mvt_date_start;$mvt_date_end;$mvt_ageyear_start;$mvt_ageyear_end;$mvt_agemonth_start;$mvt_agemonth_end;$mvt_nb_min;$mvt_nb_max;$stay_nb_min;$stay_nb_max;$mvt_last_nb_days";
		
                if ($mvt_department!='' ||  $mvt_unit!='' ||  $encounter_duration_min!='' ||  $encounter_duration_max!='' ||  $mvt_duration_min!='' ||  $mvt_duration_max!='' ||  $mvt_last_nb_days!='' ||  $mvt_date_start!='' ||  $mvt_date_end!='' ||  $stay_nb_min!='' ||  $stay_nb_max!='' ||  $mvt_nb_min!='' ||  $mvt_nb_max!='') {
			$query_select_base="select  mvt_num as document_num,'$query_key' as query_key,sysdate as query_date,patient_num,$user_num_session as user_num,$datamart_num as datamart_num,'MVT' as document_origin_code,to_char(entry_date,'DD/MM/YYYY HH24:MI') as document_date,'mvt' as object_type";
                         $filtre_sql_mvt="";
                        if ($mvt_department!='') {
                                $filtre_sql_mvt.=" and department_num ='$mvt_department' ";
                        }
                        if ($mvt_unit!='') {
                                $filtre_sql_mvt.=" and unit_num ='$mvt_unit' ";
                        }
                        if ($type_mvt!='') {
                                $filtre_sql_mvt.=" and type_mvt ='$type_mvt' ";
                        }
                        
                        if ($encounter_duration_min!='' && $encounter_duration_max!='') {
				$filtre_sql_mvt.=" and  encounter_num is not null and encounter_num in (select encounter_num from dwh_patient_stay where (out_date-entry_date)>='$encounter_duration_min' and  (out_date-entry_date)<='$encounter_duration_max'  and out_date is not null and entry_date is not null ) ";
                        } else {
	                        if ($encounter_duration_min!='') {
	                                $filtre_sql_mvt.=" and  encounter_num is not null and encounter_num in (select encounter_num from dwh_patient_stay where (out_date-entry_date)>='$encounter_duration_min' and out_date is not null and entry_date is not null ) ";
	                        }
	                        if ($encounter_duration_max!='') {
	                                $filtre_sql_mvt.=" and  encounter_num is not null and encounter_num in (select encounter_num from dwh_patient_stay where (out_date-entry_date)<='$encounter_duration_max'  and out_date is not null and entry_date is not null  ) ";
	                        }
	                }
	                        
                        if ($mvt_duration_min!='') {
                                $filtre_sql_mvt.=" and (out_date-entry_date)>='$mvt_duration_min' and out_date is not null and entry_date is not null ";
                        }
                        if ($mvt_duration_max!='') {
                                $filtre_sql_mvt.=" and (out_date-entry_date)<='$mvt_duration_max'  and out_date is not null and entry_date is not null  ";
                        }
                        
                        if ($mvt_last_nb_days!='') {
                                $filtre_sql_mvt.=" and entry_date>=sysdate-$mvt_last_nb_days ";
                        }
                        
                        if ($mvt_date_start!='') {
                                $filtre_sql_mvt.=" and entry_date>=to_date('$mvt_date_start','DD/MM/YYYY') ";
                        }
                        if ($mvt_date_end!='') {
                                $filtre_sql_mvt.=" and entry_date<=to_date('$mvt_date_end','DD/MM/YYYY') ";
                        }
                        
                        if ($mvt_ageyear_start!='' && $mvt_ageyear_end!='') {
                        	$filtre_sql_mvt.=" and mvt_num in (select mvt_num from dwh_patient a,dwh_patient_mvt b where a.patient_num=b.patient_num and  (b.entry_date-birth_date)/365>='$mvt_ageyear_start' and  (b.entry_date-birth_date)/365<='$mvt_ageyear_end'  and b.entry_date is not null ) ";
	                } else {
	                        if ($mvt_ageyear_start!='') {
	                                $filtre_sql_mvt.=" and mvt_num in (select mvt_num from dwh_patient a,dwh_patient_mvt b where a.patient_num=b.patient_num and  (b.entry_date-birth_date)/365>='$mvt_ageyear_start' and b.entry_date is not null ) ";
	                        }
	                        if ($mvt_ageyear_end!='') {
	                                $filtre_sql_mvt.=" and mvt_num in (select mvt_num from dwh_patient a,dwh_patient_mvt b where a.patient_num=b.patient_num and  (b.entry_date-birth_date)/365<='$mvt_ageyear_end' and b.entry_date is not null) ";
	                        }
	                
	                }
                        if ($mvt_agemonth_start!='' && $mvt_agemonth_end!='') {
	                                $filtre_sql_mvt.=" and mvt_num in (select mvt_num from dwh_patient a,dwh_patient_mvt b where a.patient_num=b.patient_num and  ((b.entry_date-birth_date)*12)/365>='$mvt_agemonth_start' and ((b.entry_date-birth_date)*12)/365<='$mvt_agemonth_end'  and b.entry_date is not null)";
	                } else {
                       		if ($mvt_agemonth_start!='') {
	                                $filtre_sql_mvt.=" and mvt_num in (select mvt_num from dwh_patient a,dwh_patient_mvt b where a.patient_num=b.patient_num and  ((b.entry_date-birth_date)*12)/365>='$mvt_agemonth_start' and b.entry_date is not null)";
	                        }
	                        if ($mvt_agemonth_end!='') {
	                                $filtre_sql_mvt.=" and mvt_num in (select mvt_num from dwh_patient a,dwh_patient_mvt b where a.patient_num=b.patient_num and  ((b.entry_date-birth_date)*12)/365<='$mvt_agemonth_end' and b.entry_date is not null)";
	                        }
	                }
                        
                        $filtre_sql.=$filtre_sql_mvt;
                        
                        if ($mvt_nb_min!='' && $mvt_nb_max!='') {
                                $filtre_sql.=" and  patient_num in (select patient_num from (select patient_num, count(*) nb_mvt from dwh_patient_mvt where 1=1 $filtre_sql_mvt group by patient_num) where nb_mvt>=$mvt_nb_min and nb_mvt<=$mvt_nb_max) ";
                        } else {
	                        if ($mvt_nb_max!='') {
        	                        $filtre_sql.=" and  patient_num in (select patient_num from (select patient_num, count(*) nb_mvt from dwh_patient_mvt where 1=1 $filtre_sql_mvt group by patient_num) where nb_mvt<=$mvt_nb_max) ";
                	        }
	                        if ($mvt_nb_min!='') {
        	                        $filtre_sql.=" and  patient_num in (select patient_num from (select patient_num, count(*) nb_mvt from dwh_patient_mvt where 1=1 $filtre_sql_mvt group by patient_num) where nb_mvt>=$mvt_nb_min) ";
                	        }
                        }
                        
                        if ($stay_nb_min!='' && $stay_nb_max!='') {
                                $filtre_sql.=" and  patient_num in (select patient_num from (select patient_num, count(distinct encounter_num) nb_stay from dwh_patient_mvt where 1=1 $filtre_sql_mvt group by patient_num) where nb_stay>=$stay_nb_min and nb_stay<=$stay_nb_max) ";
                        } else {
	                        if ($stay_nb_max!='') {
        	                        $filtre_sql.=" and  patient_num in (select patient_num from (select patient_num, count(distinct encounter_num) nb_stay from dwh_patient_mvt where 1=1 $filtre_sql_mvt group by patient_num) where nb_stay<=$stay_nb_max) ";
                	        }
	                        if ($stay_nb_min!='') {
        	                        $filtre_sql.=" and  patient_num in (select patient_num from (select patient_num, count(distinct encounter_num) nb_stay from dwh_patient_mvt where 1=1 $filtre_sql_mvt group by patient_num) where nb_stay>=$stay_nb_min) ";
                	        }
                        }
                        
			if ($datamart_num!=0) {
				$filtre_sql.=" and patient_num in  (select patient_num from dwh_datamart_result where datamart_num = $datamart_num )  ";
			}
       			$select_sql="$query_select_base from dwh_patient_mvt where 1=1 $filtre_sql  ";
		//	$select_sql.="and patient_num not in (select patient_num from DWH_PATIENT_OPPOSITION)";
        	} 
        }
        return array($query_key,$datamart_num,$select_sql);
}


function recup_query_key_depuis_xml ($xml) {
	if ($xml!='') {
	        $info_xml = new SimpleXMLElement($xml);
	        $text= trim($info_xml->text);
	        $query_type= trim($info_xml->query_type);
	        $thesaurus_data_num= trim($info_xml->thesaurus_data_num);
	        $chaine_requete_code= trim($info_xml->str_structured_query);
	        $etendre_syno= trim($info_xml->synonym_expansion);
	        
	        $exclure= trim($info_xml->exclude);
	        $document_origin_code= trim($info_xml->document_origin_code);
	        $title_document= trim($info_xml->title_document);
	        $date_deb_document= trim($info_xml->document_date_start);
	        $date_fin_document= trim($info_xml->document_date_end);
	        $stay_length_min= trim($info_xml->stay_length_min);
	        $stay_length_max= trim($info_xml->stay_length_max);
	        $document_last_nb_days= trim($info_xml->document_last_nb_days);
	        $periode_document= trim($info_xml->period_document);
	        $age_deb_document= trim($info_xml->document_ageyear_start);
	        $age_fin_document= trim($info_xml->document_ageyear_end);
	        $agemois_deb_document= trim($info_xml->document_agemonth_start);
	        $agemois_fin_document= trim($info_xml->document_agemonth_end);
	        $hospital_department_list= trim($info_xml->hospital_department_list);
	        $context= trim($info_xml->context);
	        $certainty= trim($info_xml->certainty);
	        $datamart_num= trim($info_xml->datamart_text_num);
	        if ($certainty=='') {
	                $certainty=1;
	        }
	        $text=nettoyer_pour_requete($text);
	        if ($context=='') {
	                $context='patient_text';
	        }
	        if ($datamart_num=='') {
	                $datamart_num='0';
	        }
		$query_key=strtolower(nettoyer_pour_insert("$text;$etendre_syno;$hospital_department_list;$document_origin_code;$title_document;$date_deb_document;$date_fin_document;$age_deb_document;$age_fin_document;$agemois_deb_document;$agemois_fin_document;$context;$certainty;$chaine_requete_code;$thesaurus_data_num;$datamart_num;$periode_document;$document_last_nb_days;$stay_length_min;$stay_length_max"));
	}
	return $query_key;

}




// contrainte temporelle //

function creer_requete_sql_contrainte_temporelle_passthru ($xml,$query_key_a,$query_key_b,$datamart_num) {
        global $dbh,$liste_uf_session,$user_num_session,$CHEMIN_GLOBAL_LOG;
        if ($datamart_num=='') {
	        $datamart_num=0;
	}
        $requete_sql='';
        if ($xml!='') {
		list($query_key,$select_sql)=creer_requete_sql_contrainte_temporelle ($xml,$query_key_a,$query_key_b,$datamart_num);
		if ($query_key!='') {
			$sel = oci_parse($dbh,"select count(*) as  NB from DWH_TMP_PRERESULT_$user_num_session where query_key='$query_key' and trunc(tmpresult_date)=trunc(sysdate) and datamart_num=$datamart_num");   
			oci_execute($sel);
			$r = oci_fetch_array($sel, OCI_ASSOC);
			$nb=$r['NB'];
	                if ($nb==0) {
				
	                        $requeteins="insert into dwh_tmp_query (query_key, sql_clob ,datamart_num , user_num , status_calculate,query_date) values ('$query_key', :select_sql ,'$datamart_num' , '$user_num_session' , 0,sysdate)";
	                        $stmt = ociparse($dbh,$requeteins);
	                        $rowid = ocinewdescriptor($dbh, OCI_D_LOB);
	                        ocibindbyname($stmt, ":select_sql",$select_sql);
	                        $execState = ociexecute($stmt);
	                        ocifreestatement($stmt);
	                        
				passthru( "php passthru_result_temp.php $user_num_session $datamart_num \"$query_key\"  >> $CHEMIN_GLOBAL_LOG/log_chargement_result_temp.$user_num_session.$datamart_num.txt 2>&1 &");
	                } else {
	                        $sel = oci_parse($dbh,"select count(distinct patient_num) as  NB from DWH_TMP_PRERESULT_$user_num_session where query_key='$query_key' and trunc(tmpresult_date)=trunc(sysdate) and datamart_num=$datamart_num ");   
	                        oci_execute($sel);
	                        $r = oci_fetch_array($sel, OCI_ASSOC);
	                        $nb_patient=$r['NB'];
	                        
	                        $sel = oci_parse($dbh,"select count(*) as  NB from dwh_tmp_query where query_key='$query_key' and trunc(query_date)=trunc(sysdate) and datamart_num=$datamart_num and user_num=$user_num_session");   
	                        oci_execute($sel);
	                        $r = oci_fetch_array($sel, OCI_ASSOC);
	                        $test_requete=$r['NB'];
	                        if ($test_requete==0) {
		                        $requeteins="insert into dwh_tmp_query (query_key, sql_clob ,datamart_num , user_num , status_calculate,query_date,count_patient) values ('$query_key', :select_sql ,'$datamart_num' , '$user_num_session', 1,sysdate,$nb_patient)";
		                        $stmt = ociparse($dbh,$requeteins);
		                        $rowid = ocinewdescriptor($dbh, OCI_D_LOB);
		                        ocibindbyname($stmt, ":select_sql",$select_sql);
		                        $execState = ociexecute($stmt);
		                        ocifreestatement($stmt);
	                        }
	                }
	        }
        }
        return ($query_key);
}



function creer_requete_sql_contrainte_temporelle ($xml,$query_key_a,$query_key_b,$datamart_num) {
        global $dbh,$liste_uf_session,$user_num_session;
        $requete_sql='';
        if ($xml!='') {
                $info_xml = new SimpleXMLElement($xml);
		$num_filtre= trim($info_xml->filter_num);
		$type_contrainte= trim($info_xml->time_constraint_type);
		$minmax= trim($info_xml->minmax);
		$unite_contrainte= trim($info_xml->time_constraint_unit);
		$duree_contrainte= trim($info_xml->time_constraint_duration);
		
	
		$query_key="$type_contrainte;$minmax;$unite_contrainte;$duree_contrainte;$query_key_a;$query_key_b";
		$filtre_sql='';
                
		if ($datamart_num=='') {
			$datamart_num=0;
		}
		
		if (preg_match("/;mvt;/",";$query_key_a;")) {
			$table_object_type_a='dwh_patient_mvt';
			$cle_object_type_a='mvt_num';
		} else {
			$table_object_type_a='dwh_document';
			$cle_object_type_a='document_num';
		}
		if (preg_match("/;mvt;/i",";$query_key_b;")) {
			$table_object_type_b='dwh_patient_mvt';
			$cle_object_type_b='mvt_num';
		} else {
			$table_object_type_b='dwh_document';
			$cle_object_type_b='document_num';
		}
                if ($query_key!='' && $query_key_a!='' && $query_key_b!='') {
			$select_sql="";
			if ($type_contrainte=='stay') {
			// modifier requete pour ajouter dwh_patient_mvt à l aplace de dwh_document ... (NG 5 10 2019)
				$select_sql= "
				select a.document_num,'$query_key' as query_key,sysdate as query_date ,a.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,a.document_origin_code,to_char(a.document_date,'DD/MM/YYYY HH24:MI') as document_date, a.object_type
               				 from DWH_TMP_PRERESULT_$user_num_session a, $table_object_type_a a1, DWH_TMP_PRERESULT_$user_num_session b, $table_object_type_b b1
               				 where a.query_key='$query_key_a' 
               				 and  b.query_key='$query_key_b' 
               				 and a.patient_num=b.patient_num
               				 and a.document_num=a1.$cle_object_type_a
               				 and b.document_num=b1.$cle_object_type_b
               				 and a1.encounter_num=b1.encounter_num and a1.encounter_num is not null and b1.encounter_num is not null 
	               			union
				select b.document_num,'$query_key' as query_key,sysdate as query_date ,b.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,b.document_origin_code,to_char(b.document_date,'DD/MM/YYYY HH24:MI') as document_date, b.object_type
	               			 from DWH_TMP_PRERESULT_$user_num_session a, $table_object_type_a a1, DWH_TMP_PRERESULT_$user_num_session b, $table_object_type_b b1
               				 where a.query_key='$query_key_a'
               				 and  b.query_key='$query_key_b'
               				 and a.patient_num=b.patient_num
               				 and a.document_num=a1.$cle_object_type_a
               				 and b.document_num=b1.$cle_object_type_b
               				 and a1.encounter_num=b1.encounter_num and a1.encounter_num is not null and b1.encounter_num is not null
				";
			}
			if ($type_contrainte=='simultaneous') {
				$select_sql= "
				select a.document_num,'$query_key' as query_key,sysdate as query_date ,a.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,a.document_origin_code,to_char(a.document_date,'DD/MM/YYYY HH24:MI') as document_date, a.object_type
               				 from DWH_TMP_PRERESULT_$user_num_session a, DWH_TMP_PRERESULT_$user_num_session b
               				 where a.query_key='$query_key_a' 
               				 and  b.query_key='$query_key_b' 
               				 and a.patient_num=b.patient_num
               				 and a.document_date=b.document_date
	               			union
				select b.document_num,'$query_key' as query_key,sysdate as query_date ,b.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,b.document_origin_code,to_char(b.document_date,'DD/MM/YYYY HH24:MI') as document_date, b.object_type
	               			 from DWH_TMP_PRERESULT_$user_num_session a, DWH_TMP_PRERESULT_$user_num_session b
               				 where a.query_key='$query_key_a' 
               				 and  b.query_key='$query_key_b'
               				 and a.patient_num=b.patient_num
               				 and a.document_date=b.document_date
				";
			}
			if ($type_contrainte=='beforeafter') {
				if ($unite_contrainte=='days') {
					$req_temps=" $duree_contrainte ";
				}
				if ($unite_contrainte=='months') {
					$req_temps=" $duree_contrainte*31 ";
				}
				if ($unite_contrainte=='years') {
					$req_temps=" $duree_contrainte*365 ";
				}
				if ($minmax=='minimum') {
					$select_sql= "
					select a.document_num,'$query_key' as query_key,sysdate as query_date ,a.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,a.document_origin_code,to_char(a.document_date,'DD/MM/YYYY HH24:MI') as document_date, a.object_type
	               				 from DWH_TMP_PRERESULT_$user_num_session a, DWH_TMP_PRERESULT_$user_num_session b
	               				 where a.query_key='$query_key_a'
	               				 and  b.query_key='$query_key_b' 
               				 and a.patient_num=b.patient_num
	               				 and a.document_date<b.document_date and b.document_date-a.document_date> $req_temps
	               			union
					select b.document_num,'$query_key' as query_key,sysdate as query_date ,b.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,b.document_origin_code,to_char(b.document_date,'DD/MM/YYYY HH24:MI') as document_date, b.object_type
	               				 from DWH_TMP_PRERESULT_$user_num_session a, DWH_TMP_PRERESULT_$user_num_session b
	               				 where a.query_key='$query_key_a' 
	               				 and  b.query_key='$query_key_b'
               				 and a.patient_num=b.patient_num
	               				 and a.document_date<b.document_date and b.document_date-a.document_date> $req_temps
					";
				}
				if ($minmax=='minimum_exclusive') {
						$select_sql= "
						select a.document_num,'$query_key' as query_key,sysdate as query_date ,a.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,a.document_origin_code,to_char(a.document_date,'DD/MM/YYYY HH24:MI') as document_date, a.object_type
		               				 from DWH_TMP_PRERESULT_$user_num_session a, DWH_TMP_PRERESULT_$user_num_session b
		               				 where a.query_key='$query_key_a' 
		               				 and  b.query_key='$query_key_b' 
	               				 and a.patient_num=b.patient_num
		               				 and a.document_date<b.document_date and b.document_date-a.document_date> $req_temps
		               				 and b.patient_num not in (select c.patient_num from DWH_TMP_PRERESULT_$user_num_session c where c.query_key='$query_key_b'  and c.document_date<b.document_date and c.document_date>a.document_date)
		               				 and a.patient_num not in (select c.patient_num from DWH_TMP_PRERESULT_$user_num_session c where c.query_key='$query_key_a'  and c.document_date<b.document_date and c.document_date>a.document_date)
		               			union
						select b.document_num,'$query_key' as query_key,sysdate as query_date ,b.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,b.document_origin_code,to_char(b.document_date,'DD/MM/YYYY HH24:MI') as document_date, b.object_type
		               				 from DWH_TMP_PRERESULT_$user_num_session a, DWH_TMP_PRERESULT_$user_num_session b
		               				 where a.query_key='$query_key_a' 
		               				 and  b.query_key='$query_key_b'
	               				 and a.patient_num=b.patient_num
		               				 and a.document_date<b.document_date and b.document_date-a.document_date> $req_temps
		               				 and b.patient_num not in (select c.patient_num from DWH_TMP_PRERESULT_$user_num_session c where c.query_key='$query_key_b'  and c.document_date<b.document_date and c.document_date>a.document_date)
		               				 and a.patient_num not in (select c.patient_num from DWH_TMP_PRERESULT_$user_num_session c where c.query_key='$query_key_a' and c.document_date<b.document_date and c.document_date>a.document_date)
						";
				}
				if ($minmax=='maximum') {
					$select_sql= "
					select a.document_num,'$query_key' as query_key,sysdate as query_date ,a.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,a.document_origin_code,to_char(a.document_date,'DD/MM/YYYY HH24:MI') as document_date, a.object_type
	               				 from DWH_TMP_PRERESULT_$user_num_session a, DWH_TMP_PRERESULT_$user_num_session b
	               				 where a.query_key='$query_key_a' 
	               				 and  b.query_key='$query_key_b'
               				 and a.patient_num=b.patient_num
	               				 and a.document_date<b.document_date and b.document_date-a.document_date< $req_temps
	               			union
					select b.document_num,'$query_key' as query_key,sysdate as query_date ,b.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,b.document_origin_code,to_char(b.document_date,'DD/MM/YYYY HH24:MI') as document_date, b.object_type
	               				 from DWH_TMP_PRERESULT_$user_num_session a, DWH_TMP_PRERESULT_$user_num_session b
	               				 where a.query_key='$query_key_a'
	               				 and  b.query_key='$query_key_b'
               				 and a.patient_num=b.patient_num
	               				 and a.document_date<b.document_date and b.document_date-a.document_date< $req_temps
					";
				}
			}
			if ($type_contrainte=='periode') {
				if ($unite_contrainte=='days') {
					$req_temps=" $duree_contrainte ";
				}
				if ($unite_contrainte=='months') {
					$req_temps=" $duree_contrainte*31 ";
				}
				if ($unite_contrainte=='years') {
					$req_temps=" $duree_contrainte*365 ";
				}
				if ($minmax=='minimum') {
					$select_sql= "
					select a.document_num,'$query_key' as query_key,sysdate as query_date ,a.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,a.document_origin_code,to_char(a.document_date,'DD/MM/YYYY HH24:MI') as document_date, a.object_type
	               				 from DWH_TMP_PRERESULT_$user_num_session a, DWH_TMP_PRERESULT_$user_num_session b
	               				 where a.query_key='$query_key_a' 
	               				 and  b.query_key='$query_key_b'
               				 and a.patient_num=b.patient_num
	               				 and abs(b.document_date-a.document_date)> $req_temps
	               			union
					select b.document_num,'$query_key' as query_key,sysdate as query_date ,b.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,b.document_origin_code,to_char(b.document_date,'DD/MM/YYYY HH24:MI') as document_date, b.object_type
	               				 from DWH_TMP_PRERESULT_$user_num_session a, DWH_TMP_PRERESULT_$user_num_session b
	               				 where a.query_key='$query_key_a'
	               				 and  b.query_key='$query_key_b'
               				 and a.patient_num=b.patient_num
	               				 and abs(b.document_date-a.document_date)> $req_temps
					";
				}
				if ($minmax=='minimum_exclusive') {
					$select_sql= "
					select a.document_num,'$query_key' as query_key,sysdate as query_date ,a.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,a.document_origin_code,to_char(a.document_date,'DD/MM/YYYY HH24:MI') as document_date, a.object_type
	               				 from DWH_TMP_PRERESULT_$user_num_session a, DWH_TMP_PRERESULT_$user_num_session b
	               				 where a.query_key='$query_key_a' 
	               				 and  b.query_key='$query_key_b' 
               				 and a.patient_num=b.patient_num
	               				 and abs(b.document_date-a.document_date)> $req_temps
	               				 and b.patient_num not in (select c.patient_num from DWH_TMP_PRERESULT_$user_num_session c where c.query_key='$query_key_b'  and abs(a.document_date-c.document_date)< $req_temps)
	               				 and a.patient_num not in (select c.patient_num from DWH_TMP_PRERESULT_$user_num_session c where c.query_key='$query_key_a'  and abs(b.document_date-c.document_date)< $req_temps)
	               			union
					select b.document_num,'$query_key' as query_key,sysdate as query_date ,b.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,b.document_origin_code,to_char(b.document_date,'DD/MM/YYYY HH24:MI') as document_date, b.object_type
	               				 from DWH_TMP_PRERESULT_$user_num_session a, DWH_TMP_PRERESULT_$user_num_session b
	               				 where a.query_key='$query_key_a' 
	               				 and  b.query_key='$query_key_b'
               				 and a.patient_num=b.patient_num
	               				 and abs(b.document_date-a.document_date)> $req_temps
	               				 and b.patient_num not in (select c.patient_num from DWH_TMP_PRERESULT_$user_num_session c where c.query_key='$query_key_b' and abs(a.document_date-c.document_date)< $req_temps)
	               				 and a.patient_num not in (select c.patient_num from DWH_TMP_PRERESULT_$user_num_session c where c.query_key='$query_key_a' and abs(b.document_date-c.document_date)< $req_temps)
					";
				}
				if ($minmax=='maximum') {
					$select_sql= "
					select a.document_num,'$query_key' as query_key,sysdate as query_date ,a.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,a.document_origin_code,to_char(a.document_date,'DD/MM/YYYY HH24:MI') as document_date, a.object_type
	               				 from DWH_TMP_PRERESULT_$user_num_session a, DWH_TMP_PRERESULT_$user_num_session b
	               				 where a.query_key='$query_key_a'
	               				 and  b.query_key='$query_key_b'
               				 and a.patient_num=b.patient_num
	               				 and abs(b.document_date-a.document_date)< $req_temps
	               			union
					select b.document_num,'$query_key' as query_key,sysdate as query_date ,b.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,b.document_origin_code,to_char(b.document_date,'DD/MM/YYYY HH24:MI') as document_date, b.object_type
	               				 from DWH_TMP_PRERESULT_$user_num_session a, DWH_TMP_PRERESULT_$user_num_session b
	               				 where a.query_key='$query_key_a'
	               				 and  b.query_key='$query_key_b' 
               				 and a.patient_num=b.patient_num
	               				 and abs(b.document_date-a.document_date)< $req_temps
					";
				}
			}
        	}
        }
        return array($query_key,$select_sql);
}

// fin contrainte temporelle //
















function creer_requete_sql_filtre ($xml,$option) {
        global $dbh,$liste_uf_session,$user_num_session;
        $requete_sql='';
        if ($xml!='') {
		list($query_key,$datamart_num,$select_sql)=creer_requete_sql ($xml);
                if ($query_key!='') {
                        $sel = oci_parse($dbh,"select count(*) as  NB from DWH_TMP_PRERESULT_$user_num_session where query_key='$query_key' and trunc(tmpresult_date)=trunc(sysdate) and datamart_num=$datamart_num ");   
                        oci_execute($sel);
                        $r = oci_fetch_array($sel, OCI_ASSOC);
                        $nb=$r['NB'];
                        
                        if ($nb==0) {
				$select_sql=preg_replace("/to_char\(document_date,'DD\/MM\/YYYY HH24:MI'\) *as *document_date/","document_date",$select_sql);
				$sel = oci_parse($dbh, " insert  /*+ APPEND */ into DWH_TMP_PRERESULT_$user_num_session (document_num ,query_key , tmpresult_date,patient_num,user_num,datamart_num,document_origin_code,document_date,object_type)  $select_sql  ");   
				oci_execute($sel);
                        }
                        
                        if ($option=='patient_num') {
                                $requete_sql=" query_key='$query_key'  "; // a voir si on supprimer le filtre sur user_num ... garcelon
                        }
                }
                if ($datamart_num==0) {
                        
                } else {
                        if ($option=='patient_num') {
                                 $requete_sql.=" and datamart_num=$datamart_num  ";
				$select_sql=preg_replace("/to_char\(document_date,'DD\/MM\/YYYY HH24:MI'\) *as *document_date/i","document_date",$select_sql);
				$sel = oci_parse($dbh, " insert  /*+ APPEND */ into DWH_TMP_PRERESULT_$user_num_session (document_num ,query_key , tmpresult_date,patient_num,user_num,datamart_num,document_origin_code,document_date,object_type)  $select_sql  ");   
				oci_execute($sel);
                        }
                }
        }
        return $requete_sql;
}


function list_query_history ($datamart_num,$user_num) {
        global $dbh;
        $liste_requete_claire="<table border=\"0\" class=\"tableau_liste_requete\" id=\"id_tableau_liste_requete\" width=\"400px\">
        <thead><th style=\"width:18px;padding:2px;\">&nbsp;</th><th>".get_translation('DATE','date')."</th><th>".get_translation('QUERIES','Requêtes')."</th></thead>
        <tbody>";
        $sel = oci_parse($dbh, " select query_num,xml_query, DATE_REQUETE_CHAR,pin from (select QUERY_NUM,xml_query , to_char(QUERY_DATE,'DD/MM/YYYY HH24:MI') as DATE_REQUETE_CHAR, QUERY_DATE,pin, rownum from dwh_query where datamart_num=$datamart_num and user_num=$user_num order by pin desc ,query_date desc) t where rownum<400");          
        oci_execute($sel);
        while ($r = oci_fetch_array($sel, OCI_ASSOC)) {
                $query_date=$r['DATE_REQUETE_CHAR'];
                $query_num=$r['QUERY_NUM'];
                $pin=$r['PIN'];
                if ($r['XML_QUERY']) {
                        $xml_query=$r['XML_QUERY']->load();
                }
		$readable_query=readable_query ($xml_query) ;
		$liste_requete_claire.="<tr onmouseover=\"this.style.backgroundColor='#B9C2C8';\" onmouseout=\"this.style.backgroundColor='#ffffff';\" >";
		$liste_requete_claire.="<td style=\"vertical-align:top;padding:2px;margin:0px;width:18px;\"><span id=\"id_img_pin_$query_num\" style=\"cursor:pointer\" onclick=\"punaiser_requete($query_num);\">";
		if ($pin==0) {
			$liste_requete_claire.="<img src=\"images/pin_off.png\" alt=\"Punaiser la requête\" title=\"Punaiser la requête\" style=\"border:0px;\">";
		} else {
			$liste_requete_claire.="<img src=\"images/pin.png\" alt=\"Dépunaiser la requête\" title=\"Dépunaiser la requête\" style=\"border:0px;\">";
		}
		$liste_requete_claire.="</span></td>
		<td style=\"vertical-align:top;cursor:pointer;\" onclick=\"charger_moteur_recherche($query_num);\" >$query_date </td>
		<td style=\"vertical-align:top;cursor:pointer;\" onclick=\"charger_moteur_recherche($query_num);\" > $readable_query </td>
		</tr>";
        }
        $liste_requete_claire.="</tbody></table>";
        return $liste_requete_claire;
}


function get_json_query_history ($datamart_num,$user_num,$start,$length,$draw,$search,$order_column,$order_dir) {
        global $dbh;
        
	$requete ="select count(*) recordsTotal from dwh_query where datamart_num=$datamart_num and user_num=$user_num ";
	$sel=oci_parse($dbh,$requete);
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$recordsTotal=$r['RECORDSTOTAL'];


        if ($search!='') {
        	$search=preg_replace("/'/","''",$search);
        	$req_filter="and lower(xml_query) like lower('%$search%') ";
        	
		$requete ="select count(*) recordsTotal from dwh_query where datamart_num=$datamart_num and user_num=$user_num $req_filter ";
		$sel=oci_parse($dbh,$requete);
		oci_execute($sel);
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$recordsFiltered=$r['RECORDSTOTAL'];
        	
        } else {
        	$req_filter="";
        	$recordsFiltered=$recordsTotal;
        }
        
	if ($order_dir=='') {
		$order_dir='desc';
	}
	if ($order_column=='') {
		$order_by=" pin desc, query_date $order_dir";
	}
	if ($order_column==1 ) {
		$order_by=" pin desc, query_date $order_dir";
	}
	
	$data='';
	$requete="select query_num,xml_query, DATE_REQUETE_CHAR,pin from  (select QUERY_NUM,xml_query , to_char(QUERY_DATE,'DD/MM/YYYY HH24:MI') as DATE_REQUETE_CHAR, QUERY_DATE,pin, ROW_NUMBER() OVER (order by $order_by) as numero from dwh_query where datamart_num=$datamart_num and user_num=$user_num $req_filter order by $order_by) t  where numero>$start and numero<=$start+$length ";
        $sel = oci_parse($dbh,$requete);          
        oci_execute($sel);
        while ($r = oci_fetch_array($sel, OCI_ASSOC)) {
                $query_date=$r['DATE_REQUETE_CHAR'];
                $query_num=$r['QUERY_NUM'];
                $pin=$r['PIN'];
                if ($r['XML_QUERY']) {
                        $xml_query=$r['XML_QUERY']->load();
                }
		$readable_query=readable_query ($xml_query) ;
		$readable_query=preg_replace("/\n/"," ",$readable_query);
		$readable_query=preg_replace("/\"/","'",$readable_query);
		if ($search!='') {
			$readable_query=preg_replace("/$search/i","<strong class='pattern_found'>$search</strong>",$readable_query);
		}
		$data.="[\"<span id='id_img_pin_$query_num' style='cursor:pointer' onclick='punaiser_requete($query_num);'>";
		if ($pin==0) {
			$data.="<img src='images/pin_off.png' alt='Punaiser la requête' title='Punaiser la requête' style='border:0px;'>";
		} else {
			$data.="<img src='images/pin.png' alt='Dépunaiser la requête' title='Dépunaiser la requête' style='border:0px;'>";
		}
		$data.="</span>\",\"$query_date\",\"$readable_query\",\"$query_num\"],";
        }
	$data=substr($data,0,-1);
	
	$json="{\"draw\":$draw,\"recordsTotal\":$recordsTotal,\"recordsFiltered\":$recordsFiltered,\"data\": [$data]}";
	$json=preg_replace("/	/"," ",$json);
        return $json;
}


function lister_requete_cohort ($cohort_num) {
        global $dbh,$user_num_session;
        $liste_requete_claire="";
        $resultat="";
        $sel = oci_parse($dbh, "select QUERY_NUM,xml_query , to_char(QUERY_DATE,'DD/MM/YYYY HH24:MI') as DATE_REQUETE_CHAR, QUERY_DATE,pin from dwh_query where datamart_num=$cohort_num and pin=1 order by pin desc ,query_date desc");          
        oci_execute($sel);
        while ($r = oci_fetch_array($sel, OCI_ASSOC)) {
                $query_date=$r['DATE_REQUETE_CHAR'];
                $query_num=$r['QUERY_NUM'];
                $pin=$r['PIN'];
                if ($r['XML_QUERY']) {
                        $xml_query=$r['XML_QUERY']->load();
                }
		$readable_query=readable_query ($xml_query) ;
		$liste_requete_claire.="<tr onmouseover=\"this.style.backgroundColor='#B9C2C8';\" onmouseout=\"this.style.backgroundColor='#ffffff';\" >
		<td>$query_date </td>
		<td>$readable_query </td>
		<td><a href=\"moteur.php?action=rechercher_dans_cohorte&cohort_num=$cohort_num&option=preremplir_requete&query_num=$query_num\" target=\"_blank\"><img src=\"images/search.png\" style=\"cursor:pointer;vertical-align:middle\" border=\"0\"></a></td>
		</tr>";
        }
        if ($liste_requete_claire!='') {
	        $resultat="<table border=\"0\" class=\"tableau_solid_black\" id=\"id_tableau_liste_requete\" width=\"400px\">
	        <thead><th>".get_translation('DATE','date')."</th><th>".get_translation('QUERIES','Requêtes')."</th><th>&nbsp;</th></thead>
	        <tbody>$liste_requete_claire</tbody></table>";
	}
        return $resultat;
}


function get_query_cohort($cohort_num) {
	global $dbh;
	$tableau_query=array();
	$sel=oci_parse($dbh,"select xml_query,query_date from dwh_query where datamart_num=$cohort_num and pin=1 order by query_date desc  ");
	oci_execute($sel) ;
	while ($r=oci_fetch_array($sel,OCI_ASSOC)) {
		$xml=$r['XML_QUERY']-> load();
		$text=preg_replace("/.*<text>(.*)<\/text>.*/","$1",$xml);
		$tableau_query[$text]=1;
	}
	return $tableau_query;	
}




function lister_requete_sauve ($datamart_num) {
        global $dbh,$user_num_session;
        $liste_requete_claire="<table border=\"0\" class=\"tableau_liste_requete_sauve\" id=\"id_tableau_liste_requete_sauve\"><thead><th>".get_translation('TITLE','Titre')."</th></thead><tbody>";
        $sel = oci_parse($dbh, " select QUERY_NUM,xml_query ,title_query, to_char(QUERY_DATE,'DD/MM/YYYY HH24:MI') as DATE_REQUETE_CHAR, QUERY_DATE from dwh_query where datamart_num=$datamart_num and user_num=$user_num_session and query_type='sauve' order by query_date desc");   
        oci_execute($sel);
        while ($r = oci_fetch_array($sel, OCI_ASSOC)) {
                $query_date=$r['DATE_REQUETE_CHAR'];
                $query_num=$r['QUERY_NUM'];
                $title_query=$r['TITLE_QUERY'];
                $liste_requete_claire.="<tr style=\"cursor:pointer\" onclick=\"charger_moteur_recherche($query_num);\" onmouseover=\"this.style.backgroundColor='#B9C2C8';\" onmouseout=\"this.style.backgroundColor='#ffffff';\" ><td style=\"vertical-align:top;\"> $title_query </td></tr>";
        }
        $liste_requete_claire.="</tbody></table>";
        return $liste_requete_claire;
}



function lister_requete_sauve_accueil () {
        global $dbh,$user_num_session;
        print "<table border=\"0\" class=\"tablefin\">
        	<thead>
        		<tr>
        			<th>".get_translation('TITLE','Titre')."</th>
        			<th>".get_translation('DATAMART','Datamart')."</th>";
       	$i=0;
        $sel = oci_parse($dbh, " select distinct to_number(to_char(load_date,'MM')) as mois_chargement,to_number(to_char(load_date,'YYYY')) as an_chargement from dwh_query_result where query_num in (select query_num from dwh_query where user_num=$user_num_session and query_type='sauve' and crontab_query=1) 
        and sysdate-load_date<120
        order by to_number(to_char(load_date,'YYYY'))  asc,to_number(to_char(load_date,'MM'))  asc");   
        oci_execute($sel);
        while ($r = oci_fetch_array($sel, OCI_ASSOC)) {
                $mois_chargement=$r['MOIS_CHARGEMENT'];	
                $an_chargement=$r['AN_CHARGEMENT'];	
        	if (strlen($mois_chargement)==1) {
        		$mois_chargement="0".$mois_chargement;
        	}
        	if ($i==0) {
        	print "<th nowrap=\"nowrap\">&lt;&nbsp;$mois_chargement/$an_chargement</th>";
        	}
        	print "<th>$mois_chargement/$an_chargement </th>";
        	$tableau_mois_an_chargement[$i]="$mois_chargement/$an_chargement";
        	$i++;
        }
        print "
        		</tr>
        	
        	</thead>
        	<tbody>
        ";
        $sel = oci_parse($dbh, " select QUERY_NUM,xml_query ,title_query, to_char(QUERY_DATE,'DD/MM/YYYY HH24:MI') as DATE_REQUETE_CHAR, QUERY_DATE, datamart_num from dwh_query where user_num=$user_num_session and query_type='sauve'  and crontab_query=1 order by query_date desc");   
        oci_execute($sel);
        while ($r = oci_fetch_array($sel, OCI_ASSOC)) {
                $query_date=$r['DATE_REQUETE_CHAR'];
                $query_num=$r['QUERY_NUM'];
                $title_query=$r['TITLE_QUERY'];
                $datamart_num=$r['DATAMART_NUM'];
                
		$debut_valeur='';
                if ($datamart_num==0) {
                	$title_datamart=get_translation('ON_THE_ENTIRE_DATAWAREHOUSE',"Tout l'entrepôt");
                } else {
			$sel_datamart = oci_parse($dbh, " select title_datamart from dwh_datamart where datamart_num=$datamart_num ");   
			oci_execute($sel_datamart);
			$r_datamart = oci_fetch_array($sel_datamart, OCI_ASSOC);
	                $title_datamart=$r_datamart['TITLE_DATAMART'];
                }
		print "<tr style=\"cursor:pointer\"  onmouseover=\"this.style.backgroundColor='#B9C2C8';\" onmouseout=\"this.style.backgroundColor='#ffffff';\" onclick=\"self.location='mes_requetes.php?query_num_voir=$query_num';\"><td style=\"vertical-align:top;\"> $title_query </td><td>$title_datamart</td>";
		
		$nb_patient_debut=0;
		$mois_an=$tableau_mois_an_chargement[0];
		$sel_chargement = oci_parse($dbh, " select  count(*) as nb_patient_debut from dwh_query_result  where query_num=$query_num and load_date<to_date('$mois_an','MM/YYYY') ");
		oci_execute($sel_chargement);
		$r_chargement = oci_fetch_array($sel_chargement, OCI_ASSOC);
		$nb_patient_debut=$r_chargement['NB_PATIENT_DEBUT'];
		
		print "<td>";
		print "$nb_patient_debut</td>";
		if ($nb_patient_debut!=0) {
			$debut_valeur='ok';
		}
		for ($i=0;$i<count($tableau_mois_an_chargement);$i++) {
			$mois_an=$tableau_mois_an_chargement[$i];
			$sel_chargement = oci_parse($dbh, " select  count(*) as nb_new_patient from dwh_query_result where query_num=$query_num and to_char(load_date,'MM/YYYY')='$mois_an' ");
			oci_execute($sel_chargement);
			$r_chargement = oci_fetch_array($sel_chargement, OCI_ASSOC);
			$nb_new_patient=$r_chargement['NB_NEW_PATIENT'];
			
			print "<td>";
			if ($debut_valeur=='ok') {
				print " + ";
			}
			print "$nb_new_patient</td>";
			if ($nb_new_patient!=0) {
				$debut_valeur='ok';
			}
		}
               print "</tr>";
        }
       print "</tbody></table>";
}


function lister_requete_detail ($query_num,$periodicity) {
        global $dbh,$user_num_session;
        
        if ($periodicity=='month') {
	        print "<strong>".get_translation('FOR_THE_LAST_6MONTHS','Sur les 6 derniers mois')." :</strong> <br><br>";
	        print "<table border=\"0\" class=\"tablefin\">
	        	<thead>
	        		<tr>";
		for ($i=-5;$i<=0;$i++) {
		        $sel = oci_parse($dbh, " select to_char( ADD_MONTHS(sysdate,$i) ,'MM/YYYY')  as MONTH_YEAR from dual");   
		        oci_execute($sel);
		      	$r = oci_fetch_array($sel, OCI_ASSOC);
	                $month_year=$r['MONTH_YEAR'];	
	        	if ($i==-5) {
		        	print "<th>&le; $month_year </th>";
	        	} else {
		        	print "<th>$month_year </th>";
		        }
	        	$tableau_mois_an_chargement[]="$month_year";
	        }
	        print "
	        		</tr>
	        	</thead>
	        	<tbody>
	        ";
		$debut_valeur='';
	       
		print "<tr>";
		for ($i=0;$i<count($tableau_mois_an_chargement);$i++) {
			$mois_an=$tableau_mois_an_chargement[$i];
			if ($i==0 && count($tableau_mois_an_chargement)>5) {
				$option="load_previous";
				$sel_chargement = oci_parse($dbh, " select  count(*) as nb_new_patient from dwh_query_result where query_num=$query_num and load_date<= last_day(to_date('01/$mois_an','DD/MM/YYYY'))  ");
			} else {
				$option="";
				$sel_chargement = oci_parse($dbh, " select  count(*) as nb_new_patient from dwh_query_result where query_num=$query_num and to_char(load_date,'MM/YYYY')='$mois_an' ");
			}
			oci_execute($sel_chargement);
			$r_chargement = oci_fetch_array($sel_chargement, OCI_ASSOC);
			$nb_new_patient=$r_chargement['NB_NEW_PATIENT'];
			print "<td style=\"cursor:pointer\"  onmouseover=\"this.style.backgroundColor='#B9C2C8';\" onmouseout=\"this.style.backgroundColor='#ffffff';\" >";
			if ($debut_valeur=='ok') {
				print " + ";
			}
			if ($debut_valeur=='' && $nb_new_patient==0) {
				print " ";
			} else {
				print "$nb_new_patient patients";
			}
			print " <a href=\"moteur.php?action=rechercher_dans_requete_sauvegardee&query_num=$query_num&load_date=$mois_an&option=$option\"><img src=\"images/search.png\" border=\"0\" style=\"cursor:pointer;vertical-align:middle\"></a></td>";
			if ($nb_new_patient!=0) {
				$debut_valeur='ok';
			}
		}
		print "</tr>";
	       
		print "<tr>";
		for ($i=0;$i<count($tableau_mois_an_chargement);$i++) {
			$mois_an=$tableau_mois_an_chargement[$i];
			$nb_patient_displayed=0;
			print "<td style=\"vertical-align:top;\">";
			print "<table id=\"id_tableau_patient_requete\" style=\"border: 0px solid black;\">";
			if ($i==0 && count($tableau_mois_an_chargement)>5) {
				$sel_chargement = oci_parse($dbh,"select dwh_query_result.patient_num, lastname from dwh_query_result, dwh_patient where dwh_patient.patient_num=dwh_query_result.patient_num and query_num=$query_num  and load_date<= last_day(to_date('01/$mois_an','DD/MM/YYYY'))  order by lastname");
			} else {
				$sel_chargement = oci_parse($dbh,"select dwh_query_result.patient_num, lastname from dwh_query_result, dwh_patient where dwh_patient.patient_num=dwh_query_result.patient_num and query_num=$query_num and to_char(load_date,'MM/YYYY')='$mois_an' order by lastname");
			}
			oci_execute($sel_chargement);
			while ($r_chargement = oci_fetch_array($sel_chargement, OCI_ASSOC)) {
				$patient_num=$r_chargement['PATIENT_NUM'];
				$nb_patient_displayed++;
				if ($nb_patient_displayed<=30) {
					$patient= afficher_patient($patient_num,'requete','','');
					if ($patient!='') {
						print "<tr id=\"id_tr_patient_cohorte_$patient_num\"  style=\"border: 0px solid black;\" onmouseover=\"this.style.backgroundColor='#dcdff5';\" onmouseout=\"this.style.backgroundColor='#ffffff';\"><td  style=\"border: 0px solid black;\">$patient</td></tr>";
			     	   	}
			        }
			}
			if ($nb_patient_displayed>30) {
				$nb_patient_left=$nb_patient_displayed-30;
				print "<tr><td  style=\"border: 0px solid black;font-weight:bold;color:fuchsia;\" >and $nb_patient_left patients more</td></tr>";
			}
			print "</table>";
			print "</td>";
		}
	       print "</tr>";
	       print "</tbody></table>";
       }
        if ($periodicity=='week') {
	        print "<strong>".get_translation('FOR_THE_LAST_6WEEKS','Sur les 6 dernieres semaines')." :</strong> <br><br>";
	        print "<table border=\"0\" class=\"tablefin\">
	        	<thead>
	        		<tr>";
		for ($i=-5;$i<=0;$i++) {
		        $sel = oci_parse($dbh, " select  to_char(trunc(sysdate,'IW')-1+$i*7,'DD/MM/YYYY') as SUNDAY_YEAR from dual");   
		        oci_execute($sel);
		      	$r = oci_fetch_array($sel, OCI_ASSOC);
	                $sunday_year=$r['SUNDAY_YEAR'];	
	        	if ($i==-5) {
		        	print "<th>&le; $sunday_year </th>";
	        	} else {
		        	print "<th>$sunday_year </th>";
		        }
	        	$table_sunday_year_load[]="$sunday_year";
	        }
	        print "</tr>
        	</thead>
        	<tbody>
	        ";
		$debut_valeur='';
	       
		print "<tr>";
		for ($i=0;$i<count($table_sunday_year_load);$i++) {
			$sunday_year=$table_sunday_year_load[$i];
			if ($i==0 && count($table_sunday_year_load)>5) {
				$option="load_previous";
				$req=" select  count(*) as nb_new_patient from dwh_query_result where query_num=$query_num and trunc(load_date)<= to_date('$sunday_year','DD/MM/YYYY')";
			} else if ($i==count($table_sunday_year_load)-1) {
				$option="";
				$req=" select  count(*) as nb_new_patient from dwh_query_result where query_num=$query_num and trunc(load_date)>= to_date('$sunday_year','DD/MM/YYYY')";
			} else {
				$option="";
				$req=" select  count(*) as nb_new_patient from dwh_query_result where query_num=$query_num and trunc(load_date)= to_date('$sunday_year','DD/MM/YYYY')";
			}
			$sel_chargement = oci_parse($dbh,$req);
			oci_execute($sel_chargement);
			$r_chargement = oci_fetch_array($sel_chargement, OCI_ASSOC);
			$nb_new_patient=$r_chargement['NB_NEW_PATIENT'];
			print "<td style=\"cursor:pointer\"  onmouseover=\"this.style.backgroundColor='#B9C2C8';\" onmouseout=\"this.style.backgroundColor='#ffffff';\" >";
			if ($debut_valeur=='ok') {
				print " + ";
			}
			if ($debut_valeur=='' && $nb_new_patient==0) {
				print " ";
			} else {
				print "$nb_new_patient patients";
			}
			print " <a href=\"moteur.php?action=rechercher_dans_requete_sauvegardee&query_num=$query_num&load_date=$sunday_year&option=$option\"><img src=\"images/search.png\" border=\"0\" style=\"cursor:pointer;vertical-align:middle\"></a></td>";
			if ($nb_new_patient!=0) {
				$debut_valeur='ok';
			}
		}
		print "</tr>";
	       
		print "<tr>";
		for ($i=0;$i<count($table_sunday_year_load);$i++) {
			$sunday_year=$table_sunday_year_load[$i];
			$nb_patient_displayed=0;
			print "<td style=\"vertical-align:top;\">";
			print "<table id=\"id_tableau_patient_requete\" style=\"border: 0px solid black;\">";
			if ($i==0 && count($table_sunday_year_load)>5) {
				$req="select dwh_query_result.patient_num, lastname from dwh_query_result, dwh_patient where dwh_patient.patient_num=dwh_query_result.patient_num and query_num=$query_num  and  trunc(load_date)<= to_date('$sunday_year','DD/MM/YYYY') order by lastname";
			} else if ($i==count($table_sunday_year_load)-1) {
				$req="select dwh_query_result.patient_num, lastname from dwh_query_result, dwh_patient where dwh_patient.patient_num=dwh_query_result.patient_num and query_num=$query_num  and  trunc(load_date)>= to_date('$sunday_year','DD/MM/YYYY') order by lastname";
			} else {
				$req="select dwh_query_result.patient_num, lastname from dwh_query_result, dwh_patient where dwh_patient.patient_num=dwh_query_result.patient_num and query_num=$query_num  and  trunc(load_date)= to_date('$sunday_year','DD/MM/YYYY') order by lastname";
			}
			$sel_chargement = oci_parse($dbh,$req);
			oci_execute($sel_chargement);
			while ($r_chargement = oci_fetch_array($sel_chargement, OCI_ASSOC)) {
				$patient_num=$r_chargement['PATIENT_NUM'];
				$nb_patient_displayed++;
				if ($nb_patient_displayed<=30) {
					$patient= afficher_patient($patient_num,'requete','','');
					if ($patient!='') {
						print "<tr id=\"id_tr_patient_cohorte_$patient_num\"  style=\"border: 0px solid black;\" onmouseover=\"this.style.backgroundColor='#dcdff5';\" onmouseout=\"this.style.backgroundColor='#ffffff';\"><td  style=\"border: 0px solid black;\">$patient</td></tr>";
			     	   	}
			        }
			}
			if ($nb_patient_displayed>30) {
				$nb_patient_left=$nb_patient_displayed-30;
				print "<tr><td  style=\"border: 0px solid black;font-weight:bold;color:fuchsia;\" >and $nb_patient_left patients more</td></tr>";
			}
			print "</table>";
			print "</td>";
		}
	       print "</tr>";
	       print "</tbody></table>";
       }
       
        if ($periodicity=='day') {
	        print "<strong>".get_translation('FOR_THE_LAST_6DAYS','Sur les 6 derniers jours')." :</strong> <br><br>";
	        print "<table border=\"0\" class=\"tablefin\">
	        	<thead>
	        		<tr>";
		for ($i=-5;$i<=0;$i++) {
		        $sel = oci_parse($dbh, " select  to_char(trunc(sysdate)+$i,'DD/MM/YYYY') as DAY from dual");   
		        oci_execute($sel);
		      	$r = oci_fetch_array($sel, OCI_ASSOC);
	                $day=$r['DAY'];	
	        	if ($i==-5) {
		        	print "<th>&le; $day </th>";
	        	} else {
		        	print "<th>$day </th>";
		        }
	        	$table_day_load[]="$day";
	        }
	        print "</tr>
        	</thead>
        	<tbody>
	        ";
		$debut_valeur='';
	       
		print "<tr>";
		for ($i=0;$i<count($table_day_load);$i++) {
			$day=$table_day_load[$i];
			if ($i==0 && count($table_day_load)>5) {
				$option="load_previous";
				$req=" select  count(*) as nb_new_patient from dwh_query_result where query_num=$query_num and trunc(load_date)<= to_date('$day','DD/MM/YYYY')";
			} else {
				$option="";
				$req=" select  count(*) as nb_new_patient from dwh_query_result where query_num=$query_num and trunc(load_date)= to_date('$day','DD/MM/YYYY')";
			}
			$sel_chargement = oci_parse($dbh,$req);
			oci_execute($sel_chargement);
			$r_chargement = oci_fetch_array($sel_chargement, OCI_ASSOC);
			$nb_new_patient=$r_chargement['NB_NEW_PATIENT'];
			print "<td style=\"cursor:pointer\"  onmouseover=\"this.style.backgroundColor='#B9C2C8';\" onmouseout=\"this.style.backgroundColor='#ffffff';\" >";
			if ($debut_valeur=='ok') {
				print " + ";
			}
			if ($debut_valeur=='' && $nb_new_patient==0) {
				print " ";
			} else {
				print "$nb_new_patient patients";
			}
			print " <a href=\"moteur.php?action=rechercher_dans_requete_sauvegardee&query_num=$query_num&load_date=$day&option=$option\"><img src=\"images/search.png\" border=\"0\" style=\"cursor:pointer;vertical-align:middle\"></a></td>";
			if ($nb_new_patient!=0) {
				$debut_valeur='ok';
			}
		}
		print "</tr>";
	       
		print "<tr>";
		for ($i=0;$i<count($table_day_load);$i++) {
			$day=$table_day_load[$i];
			$nb_patient_displayed=0;
			print "<td style=\"vertical-align:top;\">";
			print "<table id=\"id_tableau_patient_requete\" style=\"border: 0px solid black;\">";
			if ($i==0 && count($table_day_load)>5) {
				$req="select dwh_query_result.patient_num, lastname from dwh_query_result, dwh_patient where dwh_patient.patient_num=dwh_query_result.patient_num and query_num=$query_num  and  trunc(load_date)<= to_date('$day','DD/MM/YYYY') order by lastname";
			} else {
				$req="select dwh_query_result.patient_num, lastname from dwh_query_result, dwh_patient where dwh_patient.patient_num=dwh_query_result.patient_num and query_num=$query_num  and  trunc(load_date)= to_date('$day','DD/MM/YYYY') order by lastname";
			}
			$sel_chargement = oci_parse($dbh,$req);
			oci_execute($sel_chargement);
			while ($r_chargement = oci_fetch_array($sel_chargement, OCI_ASSOC)) {
				$patient_num=$r_chargement['PATIENT_NUM'];
				$nb_patient_displayed++;
				if ($nb_patient_displayed<=30) {
					$patient= afficher_patient($patient_num,'requete','','');
					if ($patient!='') {
						print "<tr id=\"id_tr_patient_cohorte_$patient_num\"  style=\"border: 0px solid black;\" onmouseover=\"this.style.backgroundColor='#dcdff5';\" onmouseout=\"this.style.backgroundColor='#ffffff';\"><td  style=\"border: 0px solid black;\">$patient</td></tr>";
			     	   	}
			        }
			}
			if ($nb_patient_displayed>30) {
				$nb_patient_left=$nb_patient_displayed-30;
				print "<tr><td  style=\"border: 0px solid black;font-weight:bold;color:fuchsia;\" >and $nb_patient_left patients more</td></tr>";
			}
			print "</table>";
			print "</td>";
		}
	       print "</tr>";
	       print "</tbody></table>";
       }
}



function get_my_queries ($user_num) {
        global $dbh;
	$sel=oci_parse($dbh,"select QUERY_NUM,xml_query ,title_query, to_char(QUERY_DATE,'DD/MM/YYYY HH24:MI') as DATE_REQUETE_CHAR, QUERY_DATE, datamart_num,crontab_query from dwh_query where user_num=$user_num and query_type='sauve' order by query_date desc");
        oci_execute($sel);
        while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $title_query=$r['TITLE_QUERY'];
                $query_num=$r['QUERY_NUM'];
                $crontab_query=$r['CRONTAB_QUERY'];
                if ($crontab_query==1) {
                	$auto=" (auto)";
                } else {
                	$auto="";
                }
         	print "<li class=\"cohorte\"><a href=\"mes_requetes.php?query_num_voir=$query_num\">$title_query $auto</a></li>";
        }
}

function anonymisation_document ($document_num,$text) {
        global $dbh;
        
	$document=get_document($document_num);
        $patient_num=$document['patient_num'];
        $age_mois_doc=$document['age_patient_month'];         
        $age_an_doc=$document['age_patient'];    
        
	$patient=get_patient($patient_num,'for_document_anonymization');         
        
#        $sel_patient= oci_parse($dbh,"select lastname,lower(firstname) as firstname,to_char(birth_date,'DD/MM/YYYY') as birth_date,sex,
#        to_char(birth_date,'DD') as jour_nais,
#        to_char(birth_date,'MM') as mois_nais,
#        to_char(birth_date,'YYYY') as an_nais ,
#        to_char(birth_date,'DD/MM/YY') as date_nais_yy,
#        residence_address,
#        zip_code,
#        residence_city,
#        maiden_name  from dwh_patient where patient_num=$patient_num  " );   
#        oci_execute($sel_patient);
#        $row_patient = oci_fetch_array($sel_patient, OCI_ASSOC);
        $lastname=$patient['LASTNAME'];               
        $firstname=ucfirst ($patient['FIRSTNAME']);               
        $birth_date=$patient['BIRTH_DATE'];             
        $sex=$patient['SEX'];     
        $jour_nais=$patient['JOUR_NAIS'];   
        $mois_nais=$patient['MOIS_NAIS'];
        $an_nais=$patient['AN_NAIS'];
        $date_nais_yy=$patient['DATE_NAIS_YY'];
        $jour_nais=preg_replace("/^0/","0?",$jour_nais);
        $residence_address=$patient['RESIDENCE_ADDRESS'];
        $zip_code=$patient['ZIP_CODE'];
        $residence_city=$patient['RESIDENCE_CITY'];
        $maiden_name=$patient['MAIDEN_NAME'];
        $hospital_patient_id=$patient['HOSPITAL_PATIENT_ID'];
        
        
        if ($lastname!='') {
                $text=preg_replace("/([^a-z0-9])$lastname([^a-z0-9])/i","$1 [LASTNAME] $2",$text);
        }           
        
        if ($hospital_patient_id!='') {
                $text=preg_replace("/([^a-z0-9])$hospital_patient_id([^a-z0-9])/i","$1 [HOSPITAL_PATIENT_ID] $2",$text);
        }
        
        if ($maiden_name!='') {
                $text=preg_replace("/([^a-z0-9])$maiden_name([^a-z0-9])/i","$1 [MAIDEN_NAME] $2",$text);
        }
        
        if ($firstname!='') {
                $text=preg_replace("/([^a-z0-9])$firstname([^a-z0-9])/i","$1 [FIRSTNAME] $2",$text);
        }
        if ($birth_date!='') {
        
                $text=preg_replace("/[^a-z]n[ée](e?) +le[^a-z]/i","age$1 de",$text);
                $birth_date=str_replace("/","\\/",$birth_date);
                $date_nais_yy=str_replace("/","\\/",$date_nais_yy);
                if ($age_an_doc<2) {
                        $text=preg_replace("/([^a-z0-9])$birth_date([^a-z0-9])/i","$1 [$age_mois_doc ".get_translation('MONTH','mois')."] $2",$text);
                        
                        $trad_mois_nais=trad_en_nombre_mois ($mois_nais);
                        $text=preg_replace("/$jour_nais +$trad_mois_nais +$an_nais/i"," [$age_mois_doc ".get_translation('MONTH','mois')."] ",$text);
                        
                        $text=preg_replace("/([^a-z0-9])$date_nais_yy([^a-z0-9])/i","$1 [$age_mois_doc ".get_translation('MONTH','mois')."] $2",$text);
                        
                } else {
                        $text=preg_replace("/([^a-z0-9])$birth_date([^a-z0-9])/i","$1 [$age_an_doc ans] $2",$text);
                        
                        $trad_mois_nais=trad_en_nombre_mois ($mois_nais);
                        $text=preg_replace("/".$jour_nais."[^a-z0-9]+".$trad_mois_nais."[^a-z0-9]+$an_nais/i"," [$age_an_doc ".get_translation('YEARS','ans')."] ",$text);
                        
                        $text=preg_replace("/([^a-z0-9])$date_nais_yy([^a-z0-9])/i","$1 [$age_an_doc ".get_translation('YEARS','ans')."] $2",$text);
                }
        }
        if ($residence_address!='') {
                $residence_address=str_replace("/","\\/",$residence_address);
                $text=preg_replace("/([^a-z0-9])$residence_address([^a-z0-9])/i","$1 [RESIDENCE_ADDRESS] $2",$text);
        }
        if ($zip_code!='') {
                $zip_code=str_replace("/","\\/",$zip_code);
                $text=preg_replace("/([^a-z0-9])$zip_code([^a-z0-9])/i","$1 [ZIP_CODE] $2",$text);
        }
        if ($residence_city!='') {
                $residence_city=str_replace("/","\\/",$residence_city);
                $text=preg_replace("/([^a-z0-9])$residence_city([^a-z0-9])/i","$1 [RESIDENCE_CITY] $2",$text);
        }
        
        $text=preg_replace("/(\|Residence_address[: ]+\|)[^|]+\|/i","$1 [RESIDENCE_ADDRESS] |",$text);
        
        $text=preg_replace("/[0-9][0-9][. -]?[0-9][0-9][. -]?[0-9][0-9][. -]?[0-9][0-9][. -]?[0-9][0-9]/i","$1 [PHONE_NUMBER] $2",$text);
        
        $text=preg_replace("/[0-9][0-9]\/[0-9][0-9]\/[0-9]*/","[DATE]",$text);
	$text=preg_replace("/[0-9][0-9]? (janvier|fevrier|mars|avril|mai|juin|juillet|aout|septembre|octobre|novembre|decembre) [0-9][0-9][0-9][0-9]/i","[DATE]",$text);
	$text=preg_replace("/(janvier|fevrier|mars|avril|mai|juin|juillet|aout|septembre|octobre|novembre|decembre) [0-9][0-9][0-9][0-9]/i","[DATE]",$text);
	
        return $text;
}

function nettoyer_float($nombre) {
	$nombre=trim($nombre);
	//$nombre=preg_replace("/[^0-9.,]/","",$nombre);
	$nombre=str_replace(".",",",$nombre);
	return $nombre;
	
}

function analyse_chaine_requete_code ($thesaurus_data_num,$chaine_requete_code,$option,$num_filtre) {
	global $dbh;
	
	$sel=oci_parse($dbh,"select concept_str,measuring_unit,info_complement,value_type,list_values,thesaurus_parent_num,thesaurus_code from dwh_thesaurus_data where thesaurus_data_num=$thesaurus_data_num ");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$concept_str=$r['CONCEPT_STR'];
	$measuring_unit=$r['MEASURING_UNIT'];
	$info_complement=$r['INFO_COMPLEMENT'];
	$thesaurus_code=$r['THESAURUS_CODE'];
	$list_values=$r['LIST_VALUES'];
	$value_type=$r['VALUE_TYPE'];
	
	if ($option=='clair') {
		$res="$concept_str ";
		if ($measuring_unit!='') {
			$res.= " ($measuring_unit) ";
		}
		$res.=" $info_complement : ";
	}
	$tableau_requete_code=explode(';',$chaine_requete_code);
	//hors_borne+";"+operateur+";"+valeur+";"+valeur_deb+";"+valeur_fin+";"+valeur_sup_n_x_borne_sup+";"+valeur_inf_n_x_borne_inf;
#$xml_chaine_requete="
#<requete_code>
#	<bound></bound>
#	<operator></operator>
#	<value></value>
#	<value_start></value_start>
#	<value_end></value_end>
#	<value_sup_n_x_borne_sup></value_sup_n_x_borne_sup>
#	<value_inf_n_x_borne_inf></value_inf_n_x_borne_inf>
#	<list_of_values></list_of_values>
#</requete_code>";
	$hors_borne=$tableau_requete_code[0];
	$operateur=$tableau_requete_code[1];
	$valeur=nettoyer_float($tableau_requete_code[2]);
	$valeur_deb=nettoyer_float($tableau_requete_code[3]);
	$valeur_fin=nettoyer_float($tableau_requete_code[4]);
	$valeur_sup_n_x_borne_sup=nettoyer_float($tableau_requete_code[5]);
	$valeur_inf_n_x_borne_inf=nettoyer_float($tableau_requete_code[6]);
	$liste_valeur_possible=nettoyer_float($tableau_requete_code[7]);
	

	if ($option=='sql') {
		$res.=" thesaurus_data_num=$thesaurus_data_num ";
	}
	
	if ($hors_borne=='hors_borne') {
		if ($option=='clair') {
			$res.=get_translation('VALUES_OUT_OF_BOUNDS','Valeurs hors borne')." ";
		}
		if ($option=='sql') {
			$res.="  and (val_numeric > upper_bound or val_numeric < lower_bound) ";
		}
	}
	if ($hors_borne=='sup_borne') {
		if ($option=='clair') {
			$res.=get_translation('VALUES','Valeurs')." &gt; ".get_translation('UPPER_LIMIT_FULL','borne supérieure')."  ";
		}
		if ($option=='sql') {
			$res.="  and val_numeric > upper_bound ";
		}
	}
	if ($hors_borne=='inf_borne') {
		if ($option=='clair') {
			$res.=get_translation('VALUES','Valeurs')." &lt; ".get_translation('LOWER_LIMIT_FULL','borne inférieure')."  ";
		}
		if ($option=='sql') {
			$res.="  and val_numeric < lower_bound ";
		}
	}
	if ($operateur!='' && $valeur!='') {
		if ($operateur=='sup') {
			$signe='>';
		}
		if ($operateur=='inf') {
			$signe='<';
		}
		if ($operateur=='sup_equal') {
			$signe='>=';
		}
		if ($operateur=='inf_equal') {
			$signe='<=';
		}
		if ($operateur=='equal') {
			$signe='=';
		}
		if ($option=='clair') {
			$res.=get_translation('VALUES','Valeurs')." $signe $valeur  ";
		}
		if ($option=='sql') {
			$res.="  and val_numeric $signe '$valeur' ";
		}
	}
	if ($valeur_deb!='' && $valeur_fin!='') {
		if ($option=='clair') {
			$res.=get_translation('VALUES_BETWEEN','Valeurs comprises entre')."  $valeur_deb $valeur_fin ";
		}
		if ($option=='sql') {
			$res.="  and val_numeric >= '$valeur_deb'  and val_numeric <= '$valeur_fin'  ";
		}
	}
	if ($valeur_sup_n_x_borne_sup!='') {
		if ($option=='clair') {
			$res.=get_translation('VALUES_GREATER_THAN','Valeurs supérieures à')." $valeur_sup_n_x_borne_sup ".get_translation('UPPER_LIMIT_FULL','la borne supérieure')." ";
		}
		if ($option=='sql') {
			$res.="  and val_numeric >= '$valeur_sup_n_x_borne_sup'*upper_bound  ";
		}
	}
	if ($valeur_inf_n_x_borne_inf!='') {
		if ($option=='clair') {
			$res.=get_translation('VALUES_UNDER_THAN','Valeurs inférieures à')." $valeur_inf_n_x_borne_inf ".get_translation('LOWER_LIMIT_FULL','la borne inférieure')." ";
		}
		if ($option=='sql') {
			$res.="  and '$valeur_inf_n_x_borne_inf'*val_numeric <= lower_bound  ";
		}
	}
	if ($list_values!='' || $value_type=='present'|| $value_type=='liste') {
		if ($liste_valeur_possible!='' && $liste_valeur_possible!=1) {
			$tab_valeur_possible=explode('^',$liste_valeur_possible);
			
			$liste_val="";
			foreach ($tab_valeur_possible as $valeur_possible) {
				if ($valeur_possible!='') {
					$liste_val.="$valeur_possible, ";
				}
			}
			$liste_val=substr($liste_val,0,-2);
			
			if ($option=='clair') {
				$res.=" ".get_translation('VALUE','Valeur')." : $liste_val ";
			}
			if ($option=='sql') {
				$liste_val_sql="";
				foreach ($tab_valeur_possible as $valeur_possible) {
					if ($valeur_possible!='') {
						$liste_val_sql.="'$valeur_possible',";
					}
				}
				$liste_val_sql=substr($liste_val_sql,0,-1);
				
				$res=" ( thesaurus_data_num=$thesaurus_data_num or thesaurus_data_num in (select thesaurus_data_son_num from dwh_thesaurus_data_graph where thesaurus_data_father_num=$thesaurus_data_num ) ) "; // on teste les fils //
				$res.="  and val_text in ($liste_val_sql) ";
				
			}
		} else {
			if ($option=='clair') {
				$res.=" ".get_translation('FOUND_PRESENT','Présent')." ";
			}
			if ($option=='sql') {
				$res=" ( thesaurus_data_num=$thesaurus_data_num or thesaurus_data_num in (select thesaurus_data_son_num from dwh_thesaurus_data_graph where thesaurus_data_father_num=$thesaurus_data_num ) ) "; // on teste les fils //
			}
		}
	}

	if ($option=='javascript') {
		$res.="if (document.getElementById('id_select_hors_borne_$num_filtre')) {jQuery('#id_select_hors_borne_$num_filtre').val('$hors_borne');}";
		$res.="if (document.getElementById('id_select_operateur_$num_filtre')) {jQuery('#id_select_operateur_$num_filtre').val('$operateur');}";
		$res.="if (document.getElementById('id_input_valeur_$num_filtre')) {jQuery('#id_input_valeur_$num_filtre').val('$valeur');}";
		$res.="if (document.getElementById('id_input_valeur_deb_$num_filtre')) {jQuery('#id_input_valeur_deb_$num_filtre').val('$valeur_deb');}";
		$res.="if (document.getElementById('id_input_valeur_fin_$num_filtre')) {jQuery('#id_input_valeur_fin_$num_filtre').val('$valeur_fin');}";
		$res.="if (document.getElementById('id_input_valeur_sup_n_x_borne_sup_$num_filtre')) {jQuery('#id_input_valeur_sup_n_x_borne_sup_$num_filtre').val('$valeur_sup_n_x_borne_sup');}";
		$res.="if (document.getElementById('id_input_valeur_inf_n_x_borne_inf_$num_filtre')) {jQuery('#id_input_valeur_inf_n_x_borne_inf_$num_filtre').val('$valeur_inf_n_x_borne_inf');}";
		
		
		$tab_valeur_possible=explode('^',$liste_valeur_possible);
		
		$liste_val="";
		foreach ($tab_valeur_possible as $valeur_possible) {
			if ($valeur_possible!='') {
				$liste_val.="'$valeur_possible',";
			}
		}
		$liste_val=substr($liste_val,0,-1);
		if ($liste_val!='') {
			$res.="$(\".class_input_checkbox_$numf_filtre\").val([ $liste_val ]);";
		}
	}
	return ($res);
	
}

function readable_query ($xml) {
        global $dbh;
        $readable_query='';
        if ($xml!='') {
        	$tableau_requete_filtre_texte=array();
        	$info_xml = new SimpleXMLElement($xml);
                foreach($info_xml->text_filter as $filtre_texte) {
                        $text= trim($filtre_texte->text);
                        $etendre_syno= trim($filtre_texte->synonym_expansion);
                        $thesaurus_data_num= trim($filtre_texte->thesaurus_data_num);
                        $chaine_requete_code= trim($filtre_texte->str_structured_query);
                        $query_type= trim($filtre_texte->query_type);
                        $exclure= trim($filtre_texte->exclude);
                        $document_origin_code= trim($filtre_texte->document_origin_code);
                        $title_document= trim($filtre_texte->title_document);
                        $date_deb_document= trim($filtre_texte->document_date_start);
                        $date_fin_document= trim($filtre_texte->document_date_end);
                        $stay_length_min= trim($filtre_texte->stay_length_min);
                        $stay_length_max= trim($filtre_texte->stay_length_max);
                        $document_last_nb_days= trim($filtre_texte->document_last_nb_days);
                        $periode_document= trim($filtre_texte->period_document);
                        $age_deb_document= trim($filtre_texte->document_ageyear_start);
                        $age_fin_document= trim($filtre_texte->document_ageyear_end);
                        
                        $agemois_deb_document= trim($filtre_texte->document_agemonth_start);
                        $agemois_fin_document= trim($filtre_texte->document_agemonth_end);
                        
                        $hospital_department_list= trim($filtre_texte->hospital_department_list);
                        $context= trim($filtre_texte->context);
                        $certainty= trim($filtre_texte->certainty);
                        
                        $num_filtre= trim($filtre_texte->filter_num);
                        if ($certainty=='') {
                                $certainty=1;
                        }
                        $requete_filtre_texte='';
                        if ($exclure==1) {
                        	 $requete_filtre_texte.=get_translation('EXCLUDE_PATIENT_WITH','Exclure les patients avec')." ";
                        }
                        if ($text!='' || $chaine_requete_code!='') {
				if ($text!='') {
	                        	$requete_filtre_texte.=get_translation('DOCUMENTS_CONTAINING','Documents contenant')." '$text' ";
	                      	}
				if ($etendre_syno!='') {
	                        	$requete_filtre_texte.=get_translation('EXPANDED_TO_SYNONYMS','étendu aux synonymes')." ";
	                      	}
				if ($chaine_requete_code!='') {
					$requete_filtre_texte.=analyse_chaine_requete_code ($thesaurus_data_num,$chaine_requete_code,'clair',$num_filtre) ;
	                      	}
	                      	
	                      	
                                if ($context=='' || $context=='patient_text' || $context=='text') {
                                        $requete_filtre_texte.='';
                                }
                                if ($document_origin_code!='') {
                                        $requete_filtre_texte.=", ".get_translation('FROM_ORIGIN',"d'origine")." $document_origin_code";
                                }
                                if ($title_document!='') {
                                        $requete_filtre_texte.=", ".get_translation('TITLE_CONTAINS','le titre contient')." $title_document ";
                                }
                                if ($date_deb_document!='') {
                                        $requete_filtre_texte.=", ".get_translation('DATED','datés du')." $date_deb_document ";
                                }
                                if ($date_fin_document!='') {
                                        $requete_filtre_texte.=get_translation('DATE_TO','au')." $date_fin_document ";
                                }
                                
                                if ($stay_length_min!='' || $stay_length_max!='') {
                                	if ($stay_length_min!='' && $stay_length_max=='') {
	                                        $requete_filtre_texte.=", ".get_translation('STAY_LENGTH','Durée de séjour')." &gt;= à $stay_length_min ".get_translation('DAYS','jours')." ";
	                                }
                                	if ($stay_length_min=='' && $stay_length_max!='') {
	                                        $requete_filtre_texte.=", ".get_translation('STAY_LENGTH','Durée de séjour')." &lt;= à $stay_length_max ".get_translation('DAYS','jours')." ";
	                                }
                                	if ($stay_length_min!='' && $stay_length_max!='') {
	                                        $requete_filtre_texte.=", ".get_translation('STAY_LENGTH','Durée de séjour')." ".get_translation('BETWEEN','entre')." $stay_length_min ".get_translation('AND','et')." $stay_length_max  ".get_translation('DAYS','jours')." ";
	                                }
                                }
                                if ($document_last_nb_days!='') {
                                        $requete_filtre_texte.=", ".get_translation('DOCUMENT_PRODUCED','Produits les ')." $document_last_nb_days ".get_translation('DAYS','Jours');
                                }
                                if ($periode_document!='') {
                                        $requete_filtre_texte.=", ".get_translation('OVER_A_MINIMUM_PERIOD','sur une période de minimum')." $periode_document ".get_translation('YEARS','ans')."";
                                }
                                
                                if ($age_deb_document!='') {
                                        $requete_filtre_texte.=", ".get_translation('AGED_FOLLOWED_BY_VALUE','agés de')." $age_deb_document ".get_translation('YEARS','ans')." ";
                                }
                                if ($age_fin_document!='') {
                                        $requete_filtre_texte.=get_translation('TO_YEAR','à')." $age_fin_document ".get_translation('YEARS','ans')." ";
                                }
                                if ($agemois_deb_document!='') {
                                        $requete_filtre_texte.=", ".get_translation('AGED_FOLLOWED_BY_VALUE','agés de')." $agemois_deb_document ".get_translation('MONTH','mois')." ";
                                }
                                if ($agemois_fin_document!='') {
                                        $requete_filtre_texte.=get_translation('TO_YEAR','à')." $agemois_fin_document ".get_translation('MONTH','mois')." ";
                                }
                                
                                if ($age_deb_document!='' || $age_fin_document!='' || $agemois_deb_document!='' || $agemois_fin_document!='') {
                                        $requete_filtre_texte.=get_translation('TO_DOCUMENT_DATE','à la date du document')."";
                                }
                                if ($context=='family_text' && $text!='') {
                                        $requete_filtre_texte.=", ".get_translation('ON_FAMILY_HISTORY','sur les antécédents familiaux')." ";
                                }
                                if ($certainty=='0' && $text!='') {
                                        $requete_filtre_texte.=", ".get_translation('NEGATION_INCLUDE','en prenant les négations')." ";
                                }
                                if ($certainty=='1' && $text!='') {
                                        $requete_filtre_texte.=", ".get_translation('NEGATION_EXCLUDE','en excluant les négations')." ";
                                }
                                $liste_libelle_service='';
                                if ($hospital_department_list!='') {
                                        $tableau_unite_heberg=explode(',',$hospital_department_list);
                                        if (is_array($tableau_unite_heberg)) {
                                                foreach ($tableau_unite_heberg as $department_num) {
                                                        if ($department_num!='') {
                                                        	$department_str=get_department_str ($department_num);                                                        
                                                                $liste_libelle_service.="$department_str, ";
                                                        }
                                                }
                                                $liste_libelle_service=substr($liste_libelle_service,0,-1);
                                        }
                                        if ($liste_libelle_service!='') {
                                                $requete_filtre_texte.=", ".get_translation('IN_HOSPITAL_DEPARTMENTS','dans les services')." : $liste_libelle_service ";
                                        }
                                }
                        }
                        $readable_query.=get_translation('FILTER','Filtre')." $num_filtre : $requete_filtre_texte<br>";
                }
                
                // FILTER MVT //

	        foreach($info_xml->mvt_filter as $mvt_filter) {
	                $query_type= trim($mvt_filter->query_type);
	                $mvt_department= trim($mvt_filter->mvt_department);
	                $mvt_unit= trim($mvt_filter->mvt_unit);
	                $type_mvt= trim($mvt_filter->type_mvt);
	                $encounter_duration_min= trim($mvt_filter->encounter_duration_min);
	                $encounter_duration_max= trim($mvt_filter->encounter_duration_max);
	                $mvt_duration_min= trim($mvt_filter->mvt_duration_min);
	                $mvt_duration_max= trim($mvt_filter->mvt_duration_max);
	                
	                $mvt_nb_min= trim($mvt_filter->mvt_nb_min);
	                $mvt_nb_max= trim($mvt_filter->mvt_nb_max);
	                
	                $stay_nb_min= trim($mvt_filter->stay_nb_min);
	                $stay_nb_max= trim($mvt_filter->stay_nb_max);
	                
	                $mvt_last_nb_days= trim($mvt_filter->mvt_last_nb_days);
	                
	                $mvt_date_start= trim($mvt_filter->mvt_date_start);
	                $mvt_date_end= trim($mvt_filter->mvt_date_end);
	                
	                $mvt_ageyear_start= trim($mvt_filter->mvt_ageyear_start);
	                $mvt_ageyear_end= trim($mvt_filter->mvt_ageyear_end);
	                
	                $mvt_agemonth_start= trim($mvt_filter->mvt_agemonth_start);
	                $mvt_agemonth_end= trim($mvt_filter->mvt_agemonth_end);
	                
	                $exclure= trim($mvt_filter->exclude);
	                $num_filtre= trim($mvt_filter->filter_num);
	                $mvt_nbresult=trim( $mvt_filter->count_result);
	                
                        $requete_filtre_texte_mvt='';
                        if ($exclure==1) {
                        	 $requete_filtre_texte_mvt.=get_translation('EXCLUDE_PATIENT_WITH','Exclure les patients avec')." ";
                        }
	                $requete_filtre_texte_mvt.=get_translation('MOVMENT_FILTER','Filtre sur le mouvement').":<br>";
			if ($mvt_department!='') {
                                 $department_str=get_department_str ($mvt_department);  
				$requete_filtre_texte_mvt.= ", ".get_translation('IN_THE_DEPARTMENT','dans le département')." $department_str ";
			}
			if ($mvt_unit!='') {
                                 $unit_str=get_unit_str ($mvt_unit);  
				$requete_filtre_texte_mvt.= ", ".get_translation('IN_THE_UNIT',"dans l'unité")." $unit_str ";
			}
			if ($type_mvt!='') {
				$requete_filtre_texte_mvt.= ", ".get_translation('TYPE_OF_MVT',"mouvement de type")." $type_mvt ";
			}
			
			if ($encounter_duration_min!='' || $encounter_duration_max!='') {
				$requete_filtre_texte_mvt.= ", ".get_translation('TIME_OF_STAY',"Durée de séjour ")." : ";
					
				if ($encounter_duration_min!='' && $encounter_duration_max!='') {
					$requete_filtre_texte_mvt.= get_translation('BETWEEN',"entre ")." $encounter_duration_min ". get_translation('AND',"et ")." $encounter_duration_max ". get_translation('DAYS',"jours");
				} else if ($encounter_duration_min!='') {
					$requete_filtre_texte_mvt.= get_translation('OVER',"supérieure à ")." $encounter_duration_min ". get_translation('DAYS',"jours");
				} else if ($encounter_duration_max!='') {
					$requete_filtre_texte_mvt.= get_translation('UNDER',"inférieure à  ")." $encounter_duration_max ". get_translation('DAYS',"jours");
				} 
			}
			
			if ($mvt_duration_min!='' || $mvt_duration_max!='') {
				$requete_filtre_texte_mvt.= ", ".get_translation('TIME_OF_STAY',"Durée de séjour ")." : ";
					
				if ($mvt_duration_min!='' && $mvt_duration_max!='') {
					$requete_filtre_texte_mvt.= get_translation('BETWEEN',"entre ")." $mvt_duration_min ". get_translation('AND',"et ")." $mvt_duration_max ". get_translation('DAYS',"jours");
				} else if ($mvt_duration_min!='') {
					$requete_filtre_texte_mvt.= get_translation('OVER',"supérieure à ")." $mvt_duration_min ". get_translation('DAYS',"jours");
				} else if ($mvt_duration_max!='') {
					$requete_filtre_texte_mvt.= get_translation('UNDER',"inférieure à  ")." $mvt_duration_max ". get_translation('DAYS',"jours");
				} 
			}
			
			
			if ($mvt_last_nb_days!='') {
				$requete_filtre_texte_mvt.= ", ".get_translation('MOVMENT_LAST_NB_DAYS'," sur les $mvt_last_nb_days derniers jours ")."";
				
			}
			
			
			if ($mvt_date_start!='' || $mvt_date_end!='') {
				$requete_filtre_texte_mvt.= ", ".get_translation('MOVMENT_DATE',"Date du mouvement ")." : ";
					
				if ($mvt_date_start!='' && $mvt_date_end!='') {
					$requete_filtre_texte_mvt.= get_translation('BETWEEN',"entre ")." $mvt_date_start ". get_translation('AND',"et ")." $mvt_date_end ";
				} else if ($mvt_date_start!='') {
					$requete_filtre_texte_mvt.= get_translation('AFTER',"après")." $mvt_date_start ";
				} else if ($mvt_date_end!='') {
					$requete_filtre_texte_mvt.= get_translation('BEFORE',"avant")." $mvt_date_end ";
				} 
			}
			
			
			if ($mvt_ageyear_start!='' || $mvt_ageyear_end!='') {
				$requete_filtre_texte_mvt.= ", ".get_translation('AGE_PATIENT',"Âge du patient ")." : ";
					
				if ($mvt_ageyear_start!='' && $mvt_ageyear_end!='') {
					$requete_filtre_texte_mvt.= get_translation('BETWEEN',"entre ")." $mvt_ageyear_start ". get_translation('AND',"et ")." $mvt_ageyear_end ". get_translation('YEARS',"ans");
				} else if ($mvt_ageyear_start!='') {
					$requete_filtre_texte_mvt.= get_translation('OVER',"supérieure à ")." $mvt_ageyear_start ". get_translation('YEARS',"ans");
				} else if ($mvt_ageyear_end!='') {
					$requete_filtre_texte_mvt.= get_translation('UNDER',"inférieure à  ")." $mvt_ageyear_end ". get_translation('YEARS',"ans");
				} 
			}
			
			
			if ($mvt_agemonth_start!='' || $mvt_agemonth_end!='') {
				$requete_filtre_texte_mvt.= ", ".get_translation('AGE_PATIENT',"Âge du patient ")." : ";
					
				if ($mvt_agemonth_start!='' && $mvt_agemonth_end!='') {
					$requete_filtre_texte_mvt.= get_translation('BETWEEN',"entre ")." $mvt_agemonth_start ". get_translation('AND',"et ")." $mvt_agemonth_end ". get_translation('MONTHS',"mois")."<br>";
				} else if ($mvt_agemonth_start!='') {
					$requete_filtre_texte_mvt.= get_translation('OVER',"supérieure à ")." $mvt_agemonth_start ". get_translation('MONTHS',"mois");
				} else if ($mvt_agemonth_end!='') {
					$requete_filtre_texte_mvt.= get_translation('UNDER',"inférieure à  ")." $mvt_agemonth_end ". get_translation('MONTHS',"mois");
				} 
			}
			
			
			if ($mvt_nb_min!='' || $mvt_nb_max!='') {
				$requete_filtre_texte_mvt.= ", ".get_translation('MVT_REPEATED',"Mouvement répété ")." : ";
					
				if ($mvt_nb_min!='' && $mvt_nb_max!='') {
					$requete_filtre_texte_mvt.= get_translation('BETWEEN',"entre ")." $mvt_nb_min ". get_translation('AND',"et ")." $mvt_nb_max ". get_translation('TIMES',"fois");
				} else if ($mvt_nb_min!='') {
					$requete_filtre_texte_mvt.= get_translation('OVER',"supérieure à ")." $mvt_nb_min ". get_translation('TIMES',"fois");
				} else if ($mvt_nb_max!='') {
					$requete_filtre_texte_mvt.= get_translation('UNDER',"inférieure à  ")." $mvt_nb_max ". get_translation('TIMES',"fois");
				} 
			}
			
			
			if ($stay_nb_min!='' || $stay_nb_max!='') {
				$requete_filtre_texte_mvt.= ", ".get_translation('STAY_REPEATED',"Séjour répété ")." : ";
					
				if ($stay_nb_min!='' && $stay_nb_max!='') {
					$requete_filtre_texte_mvt.= get_translation('BETWEEN',"entre ")." $stay_nb_min ". get_translation('AND',"et ")." $stay_nb_max ". get_translation('TIMES',"fois");
				} else if ($stay_nb_min!='') {
					$requete_filtre_texte_mvt.= get_translation('OVER',"supérieure à ")." $stay_nb_min ". get_translation('TIMES',"fois");
				} else if ($stay_nb_max!='') {
					$requete_filtre_texte_mvt.= get_translation('UNDER',"inférieure à  ")." $stay_nb_max ". get_translation('TIMES',"fois");
				} 
			}
			
                        $readable_query.=get_translation('FILTER','Filtre')." $num_filtre : $requete_filtre_texte_mvt<br>";
                }
                
                // CONTRAINTES TEMPORELLES //
                $requete_filtre_texte_contrainte='';
	        foreach($info_xml->time_constraint as $contrainte_temporelle) {
	                $num_filtre= trim($contrainte_temporelle->filter_num);
	                $num_filtre_a= trim($contrainte_temporelle->time_filter_num_a);
	                $num_filtre_b= trim($contrainte_temporelle->time_filter_num_b);
	                $type_contrainte= trim($contrainte_temporelle->time_constraint_type);
	                $minmax= trim($contrainte_temporelle->minmax);
	                $unite_contrainte= trim($contrainte_temporelle->time_constraint_unit);
	                $duree_contrainte= trim($contrainte_temporelle->time_constraint_duration);
	            
			if ($type_contrainte=='stay') {
				$requete_filtre_texte_contrainte.= get_translation('THE_FILTERS','Les filtres')." <div class=\"circle_non_float\">$num_filtre_a</div> ".get_translation('AND','et')." <div class=\"circle_non_float\">$num_filtre_b</div> ".get_translation('ARE_IN_SAME_STAY','sont dans le même séjour')."<br>";
			}
			if ($type_contrainte=='simultaneous') {
				$requete_filtre_texte_contrainte.= get_translation('THE_FILTERS','Les filtres')." <div class=\"circle_non_float\">$num_filtre_a</div> ".get_translation('AND','et')." <div class=\"circle_non_float\">$num_filtre_b</div> ".get_translation('ARE_SIMULTANEOUS','sont simultanés')."<br>";
			}
			if ($type_contrainte=='beforeafter') {
				$requete_filtre_texte_contrainte.= get_translation('THE_FILTER','Le filtre')." <div class=\"circle_non_float\">$num_filtre_a</div> ".get_translation('PRECEDED_BY','précède de')." $minmax $duree_contrainte $unite_contrainte ".get_translation('THE_FILTER','le filtre')." <div class=\"circle_non_float\">$num_filtre_b</div><br>";
			}
			
			if ($type_contrainte=='periode') {
				$requete_filtre_texte_contrainte.= get_translation('THE_FILTERS','Les filtres')." <div class=\"circle_non_float\">$num_filtre_a</div> ".get_translation('AND','et')." <div class=\"circle_non_float\">$num_filtre_b</div> ".get_translation('ARE_DISTANT_FROM','sont éloignées de')." $minmax $duree_contrainte $unite_contrainte<br>";
			}
		}                
                if ($requete_filtre_texte_contrainte!='') {
                	 $readable_query.=get_translation('TEMPORAL_CONSTRAINTS','Contraintes temporelles')." :<br>$requete_filtre_texte_contrainte";
                }
                
                
                // PATIENT //
                $test_patient="";
                if ($info_xml->sex) {
                        if ($info_xml->sex=='F') {
                                $test_patient='ok';
                                $readable_query.=get_translation('FOR_FEMALE_PATIENTS','Pour les patients de sexe féminin');
                        }
                        if ($info_xml->sex=='M') {
                                $test_patient='ok';
                                $readable_query.=get_translation('FOR_MALE_PATIENTS','Pour les patients de sexe masculin');
                        }
                }
                if ($info_xml->age_start) {
                        if ($info_xml->age_start!='' && $info_xml->age_end=='') {
                                if ($test_patient=='') {
                                        $readable_query.=get_translation('FOR_PATIENTS','Pour les patients')." ";
                                } else {
                                        $readable_query.=", ".get_translation('AND','et')." ";
                                }
                                $readable_query.=get_translation('AGE_TODAY_LOWER_LIMIT',"agés aujourd'hui de plus de")." ".$info_xml->age_start." ".get_translation('YEARS','ans');
                        }
                        if ($info_xml->age_start=='' && $info_xml->age_end!='') {
                                if ($test_patient=='') {
                                        $readable_query.=get_translation('FOR_PATIENTS','Pour les patients')." ";
                                } else {
                                        $readable_query.=", ".get_translation('AND','et')." ";
                                }
                                $readable_query.=get_translation('AGE_TODAY_UPPER_LIMIT',"agés aujourd'hui de moins de")." ".$info_xml->age_end." ".get_translation('YEARS','ans');
                        }
                        if ($info_xml->age_start!='' && $info_xml->age_end!='') {
                                if ($test_patient=='') {
                                        $readable_query.="".get_translation('FOR_PATIENTS','Pour les patients')." ";
                                } else {
                                        $readable_query.=", ".get_translation('AND','et')." ";
                                }
                                $readable_query.=get_translation('AGE_TODAY',"agés aujourd'hui de")." ".$info_xml->age_start." ".get_translation('TO_YEAR','à')." ".$info_xml->age_end." ".get_translation('YEARS','ans');
                        }
                }

		if ($info_xml->alive_death) {
	                $vivant_dcd=$info_xml->alive_death;
	       		if ($vivant_dcd=='vivant') {
                                        $readable_query.=get_translation('FOR_ALIVE_PATIENTS','Pour les patients vivants').", ";
		        }
	       		if ($vivant_dcd=='decede') {
                                        $readable_query.=get_translation('FOR_DECEASED_PATIENTS','Pour les patients décédés').", ";
		        }
		}
                
	        if ($info_xml->age_death_start) {
	                $age_dcd_deb=$info_xml->age_death_start;
	        }
	        if ($info_xml->age_death_end) {
	                $age_dcd_fin=$info_xml->age_death_end;
	        }
       		if ($age_dcd_deb!='' && $age_dcd_fin=='') {
			$readable_query.=get_translation('FOR_PATIENTS_DECEASED_AFTER_AGE',"Pour les patients décédés après l'âge de")." $age_dcd_deb ".get_translation('YEARS','ans').", ";
	        }
       		if ($age_dcd_deb=='' && $age_dcd_fin!='') {
			$readable_query.=get_translation('FOR_PATIENTS_DECEASED_BEFORE_AGE',"Pour les patients décédés avant l'âge de")." $age_dcd_fin ".get_translation('YEARS','ans').", ";
	        }
       		if ($age_dcd_deb!='' && $age_dcd_fin!='') {
			$readable_query.=get_translation('FOR_PATIENTS_DECEASED_BETWEEN_AGE',"Pour les patients décédés entre l'âge de")." $age_dcd_deb ".get_translation('ANS_ET','ans')." ".get_translation('AND','et')."  $age_dcd_fin ".get_translation('YEARS','ans').", ";
	        }
	        
	        
                if ($info_xml->first_stay_date_start) {
                        if ($info_xml->first_stay_date_start!='' && $info_xml->first_stay_date_end=='') {
                                if ($test_patient=='') {
                                        $readable_query.=get_translation('FOR_PATIENTS','Pour les patients')." ";
                                } else {
                                        $readable_query.=", ".get_translation('AND','et')." ";
                                }
                                $readable_query.=get_translation('CAME_FOR_THE_1ST_TIME_STARTING_FROM','venus pour la 1ere fois à partir de')." ".$info_xml->first_stay_date_start." ";
                        }
                        if ($info_xml->first_stay_date_start=='' && $info_xml->first_stay_date_end!='') {
                                if ($test_patient=='') {
                                        $readable_query.=get_translation('FOR_PATIENTS','Pour les patients')." ";
                                } else {
                                        $readable_query.=", ".get_translation('AND','et')." ";
                                }
                                $readable_query.=get_translation('CAME_FOR_THE_1ST_TIME_BEFORE','venus pour la 1ere fois avant')." ".$info_xml->first_stay_date_end." ";
                        }
                        if ($info_xml->first_stay_date_start!='' && $info_xml->first_stay_date_end!='') {
                                if ($test_patient=='') {
                                        $readable_query.=get_translation('FOR_PATIENTS','Pour les patients')." ";
                                } else {
                                        $readable_query.=", ".get_translation('AND','et')." ";
                                }
                                $readable_query.=get_translation('CAME_FOR_THE_1ST_TIME_BETWEEN','venus pour la 1ere fois entre')." ".$info_xml->first_stay_date_start." ".get_translation('AND','et')." ".$info_xml->first_stay_date_end." ";
                        }
                }
                
                
                if ($info_xml->minimum_period_folloup) {
                        if ($info_xml->minimum_period_folloup!='') {
                                if ($test_patient=='') {
                                        $readable_query.="".get_translation('FOR_PATIENTS','Pour les patients')." ";
                                } else {
                                        $readable_query.=", ".get_translation('AND','et')." ";
                                }
                                $readable_query.=get_translation('A_MINIMUM_FOLLOWUP_OF','un follow up de minimum')." ".$info_xml->minimum_period_folloup." ".get_translation('YEARS','ans')." ";
                        }
                }
                if ($info_xml->list_excluded_cohort) {
	                $liste_titre_cohorte='';
	                $liste_cohorte_exclue=trim($info_xml->list_excluded_cohort);
	                if ($liste_cohorte_exclue!='') {
	                        $tableau_cohorte_exclue=explode(',',$liste_cohorte_exclue);
	                        if (is_array($tableau_cohorte_exclue)) {
	                                foreach ($tableau_cohorte_exclue as $cohorte_exclue) {
	                                        if ($cohorte_exclue!='') {
	                                        	list($title_cohort,$nb_patient_cohorte)=recup_titre_cohorte ($cohorte_exclue);                                                        
	                                                $liste_titre_cohorte.=" $title_cohort,";
	                                        }
	                                }
	                                $liste_titre_cohorte=substr($liste_titre_cohorte,0,-1);
	                        }
	                        if ($liste_titre_cohorte!='') {
	                                $readable_query.=", ".get_translation('PATIENTS_EXCLUDED_FROM_COHORTS','patients exclus des cohortes')." : $liste_titre_cohorte ";
	                        }
	                }
	        }
        }
        $readable_query=preg_replace("/,([a-z])/i",", $1",$readable_query);
        return ($readable_query);
}
function calcul_nb_resultat_filtre ($requete_sql) {
        global $dbh,$liste_uf_session,$user_num_session,$datamart_num,$liste_document_origin_code_session,$liste_service_session;
        $tableau_resultat=array();
        $requete_service_intersect='';
        $requete_service_dead='';
        
        if ($requete_sql!='') {
                $requete_sql=preg_replace("/^ ?and /"," ",$requete_sql);
		$filter_query_user_right=filter_query_user_right("DWH_TMP_PRERESULT_$user_num_session",$user_num_session,$_SESSION['dwh_droit_all_departments'.$datamart_num],$liste_service_session,$liste_document_origin_code_session);
		$sel = oci_parse($dbh," select count(distinct patient_num) NB  FROM DWH_TMP_PRERESULT_$user_num_session WHERE $requete_sql  $filter_query_user_right ");   
		oci_execute($sel);
		$row = oci_fetch_array($sel, OCI_ASSOC);
		$nb=$row['NB'];
        } else {
                $nb='';
        }
        return $nb;
}

function creer_requete_sql_filtre_patient ($xml,$tmpresult_num) {
        global $dbh,$liste_uf_session,$user_num_session;
        $requete_sql="select dwh_tmp_result_$user_num_session.patient_num from dwh_patient,dwh_tmp_result_$user_num_session where dwh_patient.patient_num=dwh_tmp_result_$user_num_session.patient_num and tmpresult_num=$tmpresult_num ";
        $test_filtre_patient='';
        if ($xml!='') {
                $info_xml = new SimpleXMLElement($xml);
                
                if ($info_xml->sex) {
                        $sex=$info_xml->sex;
                        if ($sex!='') {
                                $test_filtre_patient='ok';
                                $requete_sql.="and sex='$sex' ";
                        }
                }
                if ($info_xml->age_start) {
                        $age_deb=$info_xml->age_start;
                        if ($age_deb!='') {
                                $test_filtre_patient='ok';
                                $age_deb=str_replace(".",",",$age_deb);
                                $requete_sql.="and trunc((sysdate-birth_date)/365)>='$age_deb' ";
                        }
                }
                if ($info_xml->age_end) {
                        $age_fin=$info_xml->age_end;
                        if ($age_fin!='') {
                                $test_filtre_patient='ok';
                                $age_fin=str_replace(".",",",$age_fin);
                                $requete_sql.="and trunc((sysdate-birth_date)/365)<='$age_fin' ";
                        }
                }
                
		if ($info_xml->alive_death) {
	                $vivant_dcd=$info_xml->alive_death;
	       		if ($vivant_dcd=='vivant') {
                                $test_filtre_patient='ok';
                                $requete_sql.="and exists (select patient_num from dwh_patient where death_code is null and dwh_tmp_result_$user_num_session.patient_num=dwh_patient.patient_num)";
		        }
	       		if ($vivant_dcd=='decede') {
                                $test_filtre_patient='ok';
                                $requete_sql.="and exists (select patient_num from dwh_patient where death_code is not null and dwh_tmp_result_$user_num_session.patient_num=dwh_patient.patient_num)";
		        }
		}
	        
	        if ($info_xml->age_death_start) {
	                $age_dcd_deb=$info_xml->age_death_start;
       			$age_dcd_deb=trim(str_replace(".",",",$age_dcd_deb));
	        }
	        if ($info_xml->age_death_end) {
	                $age_dcd_fin=$info_xml->age_death_end;
       			$age_dcd_fin=trim(str_replace(".",",",$age_dcd_fin));
	        }
       		if ($age_dcd_deb!='' && $age_dcd_fin=='') {
			$requete_sql.="and exists (select patient_num from dwh_patient where death_code is not null and (death_date-birth_date)>=$age_dcd_deb*365 and dwh_tmp_result_$user_num_session.patient_num=dwh_patient.patient_num)";
	        }
       		if ($age_dcd_deb=='' && $age_dcd_fin!='') {
			$requete_sql.="and exists (select patient_num from dwh_patient where death_code is not null and (death_date-birth_date)<=$age_dcd_fin*365 and dwh_tmp_result_$user_num_session.patient_num=dwh_patient.patient_num)";
	        }
       		if ($age_dcd_deb!='' && $age_dcd_fin!='') {
			$requete_sql.="and exists (select patient_num from dwh_patient where death_code is not null and (death_date-birth_date)>=$age_dcd_deb*365  and (death_date-birth_date)<=$age_dcd_fin*365 and dwh_tmp_result_$user_num_session.patient_num=dwh_patient.patient_num)";
	        }
                
                
                if ($info_xml->first_stay_date_start) {
                        $date_deb_1ervenue=$info_xml->first_stay_date_start;
                        $date_fin_1ervenue=$info_xml->first_stay_date_end;
                        if ($date_deb_1ervenue!='' &&  $date_fin_1ervenue=='') {
                                $test_filtre_patient='ok';
                                $requete_sql.="and exists (select patient_num from dwh_document where dwh_tmp_result_$user_num_session.patient_num=dwh_document.patient_num having  min(document_date)>=to_date('$date_deb_1ervenue','DD/MM/YYYY') group by patient_num)";
                        }
                        if ($date_deb_1ervenue=='' &&  $date_fin_1ervenue!='') {
                                $test_filtre_patient='ok';
                                $requete_sql.="and exists (select patient_num from dwh_document where dwh_tmp_result_$user_num_session.patient_num=dwh_document.patient_num having  min(document_date)<=to_date('$date_fin_1ervenue','DD/MM/YYYY') group by patient_num)";
                        }
                        if ($date_deb_1ervenue!='' &&  $date_fin_1ervenue!='') {
                                $test_filtre_patient='ok';
                                $requete_sql.="and exists (select patient_num from dwh_document where dwh_tmp_result_$user_num_session.patient_num=dwh_document.patient_num having  min(document_date) between to_date('$date_deb_1ervenue','DD/MM/YYYY')  and  to_date('$date_fin_1ervenue','DD/MM/YYYY')  group by patient_num)";
                        }
                }
                if ($info_xml->minimum_period_folloup) {
                        $duree_minimum_prise_en_charge=$info_xml->minimum_period_folloup;
                        if ($duree_minimum_prise_en_charge!='') {
                                $test_filtre_patient='ok';
                                $duree_minimum_prise_en_charge=str_replace(".",",",$duree_minimum_prise_en_charge);
                                $requete_sql.="and exists (select patient_num from dwh_document where dwh_document.patient_num=dwh_patient.patient_num and document_date is not null having max(document_date)-min(document_date)>365*'$duree_minimum_prise_en_charge' group by patient_num) ";
                        }
                }
                
                
                
                if ($info_xml->list_excluded_cohort) {
	                $liste_titre_cohorte='';
	                $liste_cohorte_exclue=trim($info_xml->list_excluded_cohort);
                        if ($liste_cohorte_exclue!='') {
                                $test_filtre_patient='ok';
	                        $requete_sql.="and dwh_tmp_result_$user_num_session.patient_num not in (select patient_num  from dwh_cohort_result where cohort_num in ($liste_cohorte_exclue))";
	                }
	        }
                
        }
        if ($test_filtre_patient=='ok') {
                return $requete_sql;
        }
}

function generer_resultat($xml,$tmpresult_num) {
        global $dbh,$liste_uf_session,$liste_document_origin_code_session,$liste_service_session,$user_num_session,$_SESSION;
        $tableau_requete=array();
        $tableau_requete_nbresult=array();
        $tableau_requete_intersect=array();
        $info_xml = new SimpleXMLElement($xml);
        foreach($info_xml->text_filter as $filtre_texte) {
                $text= $filtre_texte->text;
                $etendre_syno= $filtre_texte->synonym_expansion;
                $query_type= trim($filtre_texte->query_type);
                $thesaurus_data_num= trim($filtre_texte->thesaurus_data_num);
                $chaine_requete_code= trim($filtre_texte->str_structured_query);
                $exclure= trim($filtre_texte->exclude);
                $document_origin_code= trim($filtre_texte->document_origin_code);
                $title_document= trim($filtre_texte->title_document);
                $date_deb_document= trim($filtre_texte->document_date_start);
                $date_fin_document= trim($filtre_texte->document_date_end);
                $stay_length_min= trim($filtre_texte->stay_length_min);
                $stay_length_max= trim($filtre_texte->stay_length_max);
                $document_last_nb_days= trim($filtre_texte->document_last_nb_days);
                $periode_document= trim($filtre_texte->period_document);
                $age_deb_document= trim($filtre_texte->document_ageyear_start);
                $age_fin_document= trim($filtre_texte->document_ageyear_end);
                $agemois_deb_document= trim($filtre_texte->document_agemonth_start);
                $agemois_fin_document= trim($filtre_texte->document_agemonth_end);
                $hospital_department_list= trim($filtre_texte->hospital_department_list);
                $context= trim($filtre_texte->context);
                $certainty= trim($filtre_texte->certainty);
                $num_filtre= trim($filtre_texte->filter_num);
                $texte_nbresult= trim($filtre_texte->count_result);
                $datamart_num= trim($filtre_texte->datamart_text_num);
                
                $xml_unitaire="<text_filter>
				        <text>$text</text>
				        <synonym_expansion>$etendre_syno</synonym_expansion>
				        <query_type>$query_type</query_type>
				        <thesaurus_data_num>$thesaurus_data_num</thesaurus_data_num>
				        <str_structured_query>$chaine_requete_code</str_structured_query>
				        <title_document>$title_document</title_document>
				        <document_date_start>$date_deb_document</document_date_start>
				        <document_date_end>$date_fin_document</document_date_end>
				        <stay_length_min>$stay_length_min</stay_length_min>
				        <stay_length_max>$stay_length_max</stay_length_max>
				        <document_last_nb_days>$document_last_nb_days</document_last_nb_days>
				        <period_document>$periode_document</period_document>
				        <document_ageyear_start>$age_deb_document</document_ageyear_start>
				        <document_ageyear_end>$age_fin_document</document_ageyear_end>
				        <document_agemonth_start>$agemois_deb_document</document_agemonth_start>
				        <document_agemonth_end>$agemois_fin_document</document_agemonth_end>
				        <exclude>$exclure</exclude>
				        <document_origin_code>$document_origin_code</document_origin_code>
				        <context>$context</context>
				        <certainty>$certainty</certainty>
				        <hospital_department_list>$hospital_department_list</hospital_department_list>
				        <datamart_text_num>$datamart_num</datamart_text_num>
        		</text_filter>";
        	
                $tableau_requete_filtre_query_key["$num_filtre"]=recup_query_key_depuis_xml($xml_unitaire);
                $if_temporarytableisempty= creer_requete_sql_filtre ($xml_unitaire,'patient_num'); // NG , we have to keep it, pour l'option recherche sur résultat, ça permet de charger les résultats ...
               	$tableau_requete_notexist["$num_filtre"]= " query_key='".$tableau_requete_filtre_query_key["$num_filtre"]."' "; // NG 2019 09 02 
                
                $tableau_requete_nbresult["$num_filtre"]=$texte_nbresult;
                $tableau_exclure["$num_filtre"]=$exclure;
                if ($datamart_num=='') {
                	$datamart_num=0;
                }
        	$tableau_requete_filtre_num_datamart["$num_filtre"]=$datamart_num;
        	$tableau_requete_filtre_object_type["$num_filtre"]='document';
        }

        foreach($info_xml->mvt_filter as $mvt_filter) {
                $query_type= trim($mvt_filter->query_type);
                $mvt_department= trim($mvt_filter->mvt_department);
                $mvt_unit= trim($mvt_filter->mvt_unit);
                $type_mvt= trim($mvt_filter->type_mvt);
                $encounter_duration_min= trim($mvt_filter->encounter_duration_min);
                $encounter_duration_max= trim($mvt_filter->encounter_duration_max);
                $mvt_duration_min= trim($mvt_filter->mvt_duration_min);
                $mvt_duration_max= trim($mvt_filter->mvt_duration_max);
                
                $mvt_nb_min= trim($mvt_filter->mvt_nb_min);
                $mvt_nb_max= trim($mvt_filter->mvt_nb_max);
                
                $stay_nb_min= trim($mvt_filter->stay_nb_min);
                $stay_nb_max= trim($mvt_filter->stay_nb_max);
                
                $mvt_last_nb_days= trim($mvt_filter->mvt_last_nb_days);
                
                $mvt_date_start= trim($mvt_filter->mvt_date_start);
                $mvt_date_end= trim($mvt_filter->mvt_date_end);
                $mvt_ageyear_start= trim($mvt_filter->mvt_ageyear_start);
                $mvt_ageyear_end= trim($mvt_filter->mvt_ageyear_end);
                $mvt_agemonth_start= trim($mvt_filter->mvt_agemonth_start);
                $mvt_agemonth_end= trim($mvt_filter->mvt_agemonth_end);
                
                $exclure= trim($mvt_filter->exclude);
                $num_filtre= trim($mvt_filter->filter_num);
                $mvt_nbresult=trim( $mvt_filter->count_result);
                $datamart_num= trim($mvt_filter->datamart_text_num);
                
                $tableau_requete_filtre_query_key["$num_filtre"]="$query_type;$mvt_department;$mvt_unit;$type_mvt;$encounter_duration_min;$encounter_duration_max;$mvt_duration_min;$mvt_duration_max;$mvt_date_start;$mvt_date_end;$mvt_ageyear_start;$mvt_ageyear_end;$mvt_agemonth_start;$mvt_agemonth_end;$mvt_nb_min;$mvt_nb_max;$stay_nb_min;$stay_nb_max;$mvt_last_nb_days";
		$xml_unitaire="<mvt_filter>
<query_type>mvt</query_type>
<mvt_department>$mvt_department</mvt_department>
<mvt_unit>$mvt_unit</mvt_unit>
<type_mvt>$type_mvt</type_mvt>
<encounter_duration_min>$encounter_duration_min</encounter_duration_min>
<encounter_duration_max>$encounter_duration_max</encounter_duration_max>
<mvt_duration_min>$mvt_duration_min</mvt_duration_min>
<mvt_duration_max>$mvt_duration_max</mvt_duration_max>
<mvt_nb_min>$mvt_nb_min</mvt_nb_min>
<mvt_nb_max>$mvt_nb_max</mvt_nb_max>
<stay_nb_min>$stay_nb_min</stay_nb_min>
<stay_nb_max>$stay_nb_max</stay_nb_max>
<mvt_last_nb_days>$mvt_last_nb_days</mvt_last_nb_days>
<mvt_date_start>$mvt_date_start</mvt_date_start>
<mvt_date_end>$mvt_date_end</mvt_date_end>
<mvt_ageyear_start>$mvt_ageyear_start</mvt_ageyear_start>
<mvt_ageyear_end>$mvt_ageyear_end</mvt_ageyear_end>
<mvt_agemonth_start>$mvt_agemonth_start</mvt_agemonth_start>
<mvt_agemonth_end>$mvt_agemonth_end</mvt_agemonth_end>
<datamart_text_num>$datamart_num</datamart_text_num>
<filter_num>$num_filtre</filter_num>
<exclude>$exclude</exclude>
<count_result>$mvt_nbresult</count_result>
</mvt_filter>
";
                $if_temporarytableisempty= creer_requete_sql_filtre_mvt ($xml_unitaire,'patient_num'); // NG , we have to keep it, pour l'option recherche sur résultat, ça permet de charger les résultats ...
               	$tableau_requete_notexist["$num_filtre"]= " query_key='".$tableau_requete_filtre_query_key["$num_filtre"]."' "; // NG 2019 09 02 
               	
                $tableau_requete_nbresult["$num_filtre"]=$mvt_nbresult;
                $tableau_exclure["$num_filtre"]=$exclure;
                if ($datamart_num=='') {
                	$datamart_num=0;
                }
        	$tableau_requete_filtre_num_datamart["$num_filtre"]=$datamart_num;
        	$tableau_requete_filtre_object_type["$num_filtre"]='mvt';
        }
        
        foreach($info_xml->time_constraint as $contrainte_temporelle) {
                $num_filtre= $contrainte_temporelle->filter_num;
                $num_filtre_a= $contrainte_temporelle->time_filter_num_a;
                $num_filtre_b= $contrainte_temporelle->time_filter_num_b;
                $type_contrainte= $contrainte_temporelle->time_constraint_type;
                $minmax= $contrainte_temporelle->minmax;
                $unite_contrainte= $contrainte_temporelle->time_constraint_unit;
                $duree_contrainte= $contrainte_temporelle->time_constraint_duration;
                $contrainte_nbresult= $contrainte_temporelle->time_constraint_count_result;

        	$query_key_a=$tableau_requete_filtre_query_key["$num_filtre_a"];
        	$query_key_b=$tableau_requete_filtre_query_key["$num_filtre_b"];
                $tableau_requete_filtre_query_key["$num_filtre"]="$type_contrainte;$minmax;$unite_contrainte;$duree_contrainte;$query_key_a;$query_key_b";
                $tableau_requete_nbresult["$num_filtre"]=$contrainte_nbresult;
                $tableau_exclure["$num_filtre"]='';
                if ($datamart_num=='') {
                	$datamart_num=0;
                }
        	$tableau_requete_filtre_num_datamart["$num_filtre"]=$datamart_num;
                if ($tableau_exclure["$num_filtre_a"]==1) {
	        	unset($tableau_requete_nbresult["$num_filtre_a"]);
            		$tableau_exclure["$num_filtre"]=1;
               		$tableau_requete_notexist["$num_filtre"]= " query_key='".$tableau_requete_filtre_query_key["$num_filtre"]."' ";
	        }
        	
                if ($tableau_exclure["$num_filtre_b"]==1) {
	        	unset($tableau_requete_nbresult["$num_filtre_b"]);
            		$tableau_exclure["$num_filtre"]=1;
               		$tableau_requete_notexist["$num_filtre"]= " query_key='".$tableau_requete_filtre_query_key["$num_filtre"]."' ";
	        }
        	$tableau_requete_filtre_object_type["$num_filtre"]='temps';
        }
        // iF datamart unspecified, we don't display patients from other departments, even for agregated view !!
	if ($datamart_num==0) {
		$filter_query_user_right=filter_query_user_right("DWH_TMP_PRERESULT_$user_num_session",$user_num_session,$_SESSION['dwh_droit_all_departments'.$datamart_num],$liste_service_session,$liste_document_origin_code_session);       
	} else {
		$filter_query_user_right='';
	}
	if ($datamart_num==0) {
		$filter_query_user_right_all=filter_query_user_right("DWH_TMP_RESULTALL_$user_num_session",$user_num_session,$_SESSION['dwh_droit_all_departments'.$datamart_num],$liste_service_session,$liste_document_origin_code_session);       
	} else {
		$filter_query_user_right_all='';
	}
        
        asort($tableau_requete_nbresult);
        $i_nb_intersect=0;
        $liste_query_key='';
        foreach ($tableau_requete_nbresult as $num_filtre => $nb) {
        	if ($tableau_exclure["$num_filtre"]=='' || $tableau_exclure["$num_filtre"]==0) {
        		$query_key=$tableau_requete_filtre_query_key["$num_filtre"];
        		$datamart_num=$tableau_requete_filtre_num_datamart["$num_filtre"];
        		$liste_query_key.="'$query_key',";
	                if ($i_nb_intersect==0) {
	                	//$requete_intersect=" select patient_num from DWH_TMP_PRERESULT_$user_num_session WHERE query_key = '$query_key'  and datamart_num=$datamart_num $filter_query_user_right";
	                	$requete_intersect_all=" select patient_num from DWH_TMP_PRERESULT_$user_num_session WHERE query_key = '$query_key'  and datamart_num=$datamart_num ";
	                } else {
	                	//$requete_intersect.=" intersect select patient_num from DWH_TMP_PRERESULT_$user_num_session WHERE query_key = '$query_key' and datamart_num=$datamart_num $filter_query_user_right";
	                	$requete_intersect_all.=" intersect select patient_num from DWH_TMP_PRERESULT_$user_num_session WHERE query_key = '$query_key' and datamart_num=$datamart_num ";
	                }       
	                $i_nb_intersect++;
	                $last_num_filtre=$num_filtre;
	        }
        }
        $liste_query_key=substr($liste_query_key,0,-1);
        $req_final_all="";
        if ($i_nb_intersect==1) {
        	if ($tableau_requete_filtre_object_type["$last_num_filtre"]=='document') {
#		        $req_final="select  '$user_num_session' as user_num,
#		          dwh_document.patient_num,
#		          encounter_num,
#		          dwh_document.document_num,
#		          document_date,
#		          $tmpresult_num as tmpresult_num,
#		          document_origin_code,
#		          author,
#		          title,
#		          department_num,
#		          'document' as object_type
#		           from  dwh_document  
#		           WHERE  exists  (   select document_num  from DWH_TMP_PRERESULT_$user_num_session where DWH_TMP_PRERESULT_$user_num_session.document_num=dwh_document.document_num and query_key in ($liste_query_key)
#		                         and datamart_num=$datamart_num and object_type='document' $filter_query_user_right)";
		                         
		  //------------ POUR INSERT ALL ----------------//
		        $req_final_all="select  '$user_num_session' as user_num,
		          dwh_document.patient_num,
		          encounter_num,
		          dwh_document.document_num,
		          document_date,
		          $tmpresult_num as tmpresult_num,
		          document_origin_code,
		          author,
		          title,
		          department_num,
		          'document' as object_type
		           from  dwh_document  
		           WHERE  exists  (   select document_num  from DWH_TMP_PRERESULT_$user_num_session where DWH_TMP_PRERESULT_$user_num_session.document_num=dwh_document.document_num and query_key in ($liste_query_key)
		                         and datamart_num=$datamart_num and object_type='document' )";
		  
		}
        	if ($tableau_requete_filtre_object_type["$last_num_filtre"]=='mvt') {
#		        $req_final="
#	           select  '$user_num_session' as user_num,
#	          dwh_patient_mvt.patient_num,
#	          dwh_patient_mvt.encounter_num,
#	          dwh_patient_mvt.mvt_num as document_num,
#	          dwh_patient_mvt.entry_date as document_date,
#	          $tmpresult_num as tmpresult_num,
#	          dwh_patient_mvt.type_mvt as document_origin_code,
#	          '' as author,
#	          'Mouvement' as title,
#	          department_num,
#	          'mvt' as object_type
#	           from  DWH_PATIENT_MVT  
#	           WHERE  exists  (   select document_num  from DWH_TMP_PRERESULT_$user_num_session where DWH_TMP_PRERESULT_$user_num_session.document_num=DWH_PATIENT_MVT.mvt_num and query_key in ($liste_query_key)
#	                         and datamart_num=$datamart_num and object_type='mvt' $filter_query_user_right) ";
	                         
	          
		  //------------ POUR INSERT ALL ----------------//
		 $req_final_all="
	           select  '$user_num_session' as user_num,
	          dwh_patient_mvt.patient_num,
	          dwh_patient_mvt.encounter_num,
	          dwh_patient_mvt.mvt_num as document_num,
	          dwh_patient_mvt.entry_date as document_date,
	          $tmpresult_num as tmpresult_num,
	          'MVT' as document_origin_code,
	          '' as author,
	          'Mouvement' as title,
	          department_num,
	          'mvt' as object_type
	           from  DWH_PATIENT_MVT  
	           WHERE  exists  (   select document_num  from DWH_TMP_PRERESULT_$user_num_session where DWH_TMP_PRERESULT_$user_num_session.document_num=DWH_PATIENT_MVT.mvt_num and query_key in ($liste_query_key)
	                         and datamart_num=$datamart_num and object_type='mvt') ";
		        
		}
        } else {
#		        $req_final_document="select  '$user_num_session' as user_num,
#		          dwh_document.patient_num,
#		          encounter_num,
#		          dwh_document.document_num,
#		          document_date,
#		          $tmpresult_num as tmpresult_num,
#		          document_origin_code,
#		          author,
#		          title,
#		          department_num,
#	          	'document' as object_type
#		           from  dwh_document  WHERE
#		                          exists  (   select document_num  from DWH_TMP_PRERESULT_$user_num_session where DWH_TMP_PRERESULT_$user_num_session.document_num=dwh_document.document_num and query_key in ($liste_query_key)
#		                         and datamart_num=$datamart_num and object_type='document' $filter_query_user_right)
#		      		and patient_num in ($requete_intersect)";
#
#		        $req_final_mvt="select  '$user_num_session' as user_num,
#		          patient_num,
#		          encounter_num,
#		          mvt_num as document_num,
#		          entry_date as document_date,
#		          $tmpresult_num as tmpresult_num,
#		          TYPE_MVT as document_origin_code,
#		          '' as author,
#		          'Mouvement' as title,
#		          department_num,
#	          	'mvt' as object_type from  dwh_patient_mvt  WHERE
#		                          exists  (   select document_num  from DWH_TMP_PRERESULT_$user_num_session where DWH_TMP_PRERESULT_$user_num_session.document_num=dwh_patient_mvt.mvt_num and query_key in ($liste_query_key)
#		                         and datamart_num=$datamart_num and object_type='mvt' $filter_query_user_right)
#		        and  patient_num in ($requete_intersect)";
#		        
#			$req_final="$req_final_mvt union all $req_final_document";
		        
		        //------------ POUR INSERT ALL ----------------//
		        if ($requete_intersect_all!='') {
			        $req_final_document_all="select  '$user_num_session' as user_num,
						          dwh_document.patient_num,
						          encounter_num,
						          dwh_document.document_num,
						          document_date,
						          $tmpresult_num as tmpresult_num,
						          document_origin_code,
						          author,
						          title,
						          department_num,
					          	'document' as object_type
						           from  dwh_document  WHERE
						                          exists  (   select document_num  from DWH_TMP_PRERESULT_$user_num_session where DWH_TMP_PRERESULT_$user_num_session.document_num=dwh_document.document_num and query_key in ($liste_query_key)
						                         and datamart_num=$datamart_num and object_type='document')
						      		and patient_num in ($requete_intersect_all)";
	
			        $req_final_mvt_all="select  '$user_num_session' as user_num,
						          patient_num,
						          encounter_num,
						          mvt_num as document_num,
						          entry_date as document_date,
						          $tmpresult_num as tmpresult_num,
						          'MVT' as document_origin_code,
						          '' as author,
						          'Mouvement' as title,
						          department_num,
					          	'mvt' as object_type from  dwh_patient_mvt  WHERE
						                          exists  (   select document_num  from DWH_TMP_PRERESULT_$user_num_session where DWH_TMP_PRERESULT_$user_num_session.document_num=dwh_patient_mvt.mvt_num and query_key in ($liste_query_key)
						                         and datamart_num=$datamart_num and object_type='mvt')
						        and  patient_num in ($requete_intersect_all)";
		
				$req_final_all="$req_final_mvt_all union all $req_final_document_all";
			} 
	}
        $sel = oci_parse($dbh,"delete from dwh_tmp_resultall_$user_num_session where tmpresult_num=$tmpresult_num ");   
        oci_execute($sel);
        $sel = oci_parse($dbh,"delete from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num ");   
        oci_execute($sel);
        
        
        // dans le cadre d'un datamart, l'utilisateur peut rechercher uniquement avec ciruteres démographiques
        if ($req_final_all=='') {
                if ($info_xml->datamart_num) {
                        $datamart_num=$info_xml->datamart_num;
        		if ($datamart_num!='' && $datamart_num!=0) {
        			$req_final_all="select  '$user_num_session' as user_num,
						          dwh_document.patient_num,
						          encounter_num,
						          dwh_document.document_num,
						          document_date,
						          $tmpresult_num as tmpresult_num,
						          document_origin_code,
						          author,
						          title,
						          department_num,
					          	  'document' as object_type
						          from  dwh_document  WHERE patient_num in (select patient_num  from dwh_datamart_result where datamart_num=$datamart_num) ";
        		}
        	}
        }
      //  inserer_resultat($req_final);
        insert_result_all($req_final_all,$tmpresult_num,$filter_query_user_right_all);
        
        
        foreach ($tableau_requete_nbresult as $num_filtre => $nb) {
        	if ($tableau_exclure["$num_filtre"]==1) {
			$requete_sql_notexist=$tableau_requete_notexist["$num_filtre"];
			//print "delete from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num and patient_num in (select patient_num from DWH_TMP_PRERESULT_$user_num_session where $requete_sql_notexist)";
			$sel = oci_parse($dbh,"delete from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num and patient_num in (select patient_num from DWH_TMP_PRERESULT_$user_num_session where $requete_sql_notexist)");   
			oci_execute($sel);
			$sel = oci_parse($dbh,"delete from dwh_tmp_resultall_$user_num_session where tmpresult_num=$tmpresult_num and patient_num in (select patient_num from DWH_TMP_PRERESULT_$user_num_session where $requete_sql_notexist)");   
			oci_execute($sel);
	        }
        }
        
//      <query><datamart_num>13820754338</datamart_num><sex></sex><age_start></age_start><age_end></age_end><alive_death></alive_death><age_death_start></age_death_start><age_death_end></age_death_end><first_stay_date_start></first_stay_date_start><first_stay_date_end></first_stay_date_end><minimum_period_folloup></minimum_period_folloup><list_excluded_cohort></list_excluded_cohort></query>
        
        
        $requete_sql_filtre_patient= creer_requete_sql_filtre_patient ($xml,$tmpresult_num);
        if ($requete_sql_filtre_patient!='') {
                $sel = oci_parse($dbh,"delete from dwh_tmp_result_$user_num_session where patient_num not in ($requete_sql_filtre_patient) and tmpresult_num=$tmpresult_num");   
                oci_execute($sel);
                $sel = oci_parse($dbh,"delete from dwh_tmp_resultall_$user_num_session where patient_num not in ($requete_sql_filtre_patient) and tmpresult_num=$tmpresult_num");   
                oci_execute($sel);
        }
}

function get_nb_result($tmpresult_num,$datamart_num) {
        global $dbh,$liste_uf_session,$liste_document_origin_code_session,$liste_service_session,$user_num_session;
        if ($datamart_num==0) {
		$filter_query_user_right=filter_query_user_right("dwh_tmp_result_$user_num_session",$user_num_session,$_SESSION['dwh_droit_all_departments'.$datamart_num],$liste_service_session,$liste_document_origin_code_session);
	} else {
		$filter_query_user_right=filter_query_user_right("dwh_tmp_result_$user_num_session",$user_num_session,'all',$liste_service_session,$liste_document_origin_code_session);
	}
        $sel = oci_parse($dbh,"select count(distinct patient_num) nb from dwh_tmp_resultall_$user_num_session where tmpresult_num=$tmpresult_num ");   
        oci_execute($sel);
        $row = oci_fetch_array($sel, OCI_ASSOC);
        $nb_patient=$row['NB'];
        $sel = oci_parse($dbh,"select count(*) nb from dwh_tmp_resultall_$user_num_session where tmpresult_num=$tmpresult_num and object_type='document'");   
        oci_execute($sel);
        $row = oci_fetch_array($sel, OCI_ASSOC);
        $nb_document=$row['NB'];
        $sel = oci_parse($dbh,"select count(*) nb from dwh_tmp_resultall_$user_num_session where tmpresult_num=$tmpresult_num and object_type='mvt'");   
        oci_execute($sel);
        $row = oci_fetch_array($sel, OCI_ASSOC);
        $nb_mvt=$row['NB'];

	$filtre_sql='';
        $sel = oci_parse($dbh,"select count(distinct patient_num) nb from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num $filter_query_user_right");   
        oci_execute($sel);
        $row = oci_fetch_array($sel, OCI_ASSOC);
        $nb_patient_user=$row['NB'];
        $sel = oci_parse($dbh,"select count(*) nb from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num  and object_type='document' $filter_query_user_right");   
        oci_execute($sel);
        $row = oci_fetch_array($sel, OCI_ASSOC);
        $nb_document_user=$row['NB'];
        $sel = oci_parse($dbh,"select count(*) nb from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num  and object_type='mvt' $filter_query_user_right");   
        oci_execute($sel);
        $row = oci_fetch_array($sel, OCI_ASSOC);
        $nb_mvt_user=$row['NB'];
        
        return array("nb_patient"=>"$nb_patient","nb_document"=>$nb_document,"nb_mvt"=>$nb_mvt,"nb_patient_user"=>$nb_patient_user,"nb_document_user"=>$nb_document_user,"nb_mvt_user"=>$nb_mvt_user);
}


// fonction executer en crontab pour verifier si nouveaux patients !!
function generer_resultat_requete_sauve($query_num) {
        global $dbh,$user_num_session;
	$sel = oci_parse($dbh,"select dwh_temp_seq.nextval as tmpresult_num from  dual " );   
	oci_execute($sel);
	$row = oci_fetch_array($sel, OCI_ASSOC);
	$tmpresult_num=$row['TMPRESULT_NUM'];
	
	$sel = oci_parse($dbh, "select xml_query , to_char(QUERY_DATE,'DD/MM/YYYY HH24:MI') as DATE_REQUETE_CHAR, QUERY_DATE,user_num from dwh_query where query_num=$query_num");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	if ($r['XML_QUERY']) {
		$xml=$r['XML_QUERY']->load();
	}
	$user_num_session=$r['USER_NUM'];
	generer_resultat($xml,$tmpresult_num);

        $sel_var=oci_parse($dbh,"insert into dwh_query_result (patient_num,load_date,query_num) select distinct patient_num, sysdate, $query_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num and user_num=$user_num_session
        and patient_num not in (select patient_num from dwh_query_result where query_num=$query_num)");
	oci_execute($sel_var);

        $sel = oci_parse($dbh,"update dwh_query set last_load_date=sysdate where query_num=$query_num");   
        oci_execute($sel);
        
        $sel = oci_parse($dbh,"delete from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num");   
        oci_execute($sel);
        $sel = oci_parse($dbh,"delete from dwh_tmp_resultall_$user_num_session where tmpresult_num=$tmpresult_num");   
        oci_execute($sel);
}
	


function sauver_requete_temp($xml_query) {
        global $dbh,$user_num_session,$datamart_num;
        save_log_query($user_num_session,'engine',$xml_query,'');
        $xml_query_nettoye=preg_replace("/'/","''",$xml_query);
        if (strlen($xml_query_nettoye)<4000) {
	        $sel = oci_parse($dbh,"select query_num from dwh_query where user_num=$user_num_session and xml_query like '$xml_query_nettoye' and query_type='temp' and datamart_num=$datamart_num");   
	        oci_execute($sel);
	        $row = oci_fetch_array($sel, OCI_ASSOC);
	        $query_num=$row['QUERY_NUM'];
	} else {
		$xml_query_nettoye_sbustr=substr($xml_query_nettoye,0,3999);
	        $sel = oci_parse($dbh,"select query_num from dwh_query where user_num=$user_num_session and xml_query like '$xml_query_nettoye_sbustr%' and query_type='temp' and datamart_num=$datamart_num");   
	        oci_execute($sel);
	        $row = oci_fetch_array($sel, OCI_ASSOC);
	        $query_num=$row['QUERY_NUM'];
	}
        $a = simplexml_load_string($xml_query);
        if($a===FALSE) {
        } else {
                if ($query_num=='') {
                	
		        $query_num=get_uniqid();
                        $requeteins="insert into dwh_query (query_num,user_num,xml_query,query_date,title_query,datamart_num,query_type,pin) values ($query_num,$user_num_session,:xml_query,sysdate,'',$datamart_num,'temp',0)";
                        $stmt = ociparse($dbh,$requeteins);
                        $rowid = ocinewdescriptor($dbh, OCI_D_LOB);
                        ocibindbyname($stmt, ":xml_query",$xml_query);
                        $execState = ociexecute($stmt);
                        ocifreestatement($stmt);
                } else {
                        $requeteins="update dwh_query set query_date=sysdate where  user_num=$user_num_session and query_num=$query_num and query_type='temp' and datamart_num=$datamart_num";
                        $sel = oci_parse($dbh,$requeteins);   
                        oci_execute($sel);
                }
        }
        return $query_num;
}

function inserer_resultat ($requete_sql) {
        global $dbh,$login_session,$user_num_session;
        $requete_sql=preg_replace("/^ ?and /"," ",$requete_sql);
        if ($requete_sql !='') {
	        $sel = oci_parse($dbh,"insert /*+ APPEND */ into dwh_tmp_result_$user_num_session (user_num,patient_num, encounter_num,document_num,document_date,tmpresult_num,document_origin_code,author,title,department_num,object_type) select user_num,patient_num,encounter_num,document_num,document_date,tmpresult_num,document_origin_code,author,title,department_num,object_type from ( $requete_sql ) t " );   
	        oci_execute($sel);
	}
}

function insert_result_all ($requete_sql,$tmpresult_num,$filter_query_user_right) {
        global $dbh,$login_session,$user_num_session;
        $requete_sql=preg_replace("/^ ?and /"," ",$requete_sql);
        if ($requete_sql !='') {
	        $insall = oci_parse($dbh,"insert /*+ APPEND */ into dwh_tmp_resultall_$user_num_session (user_num,patient_num, encounter_num,document_num,document_date,tmpresult_num,document_origin_code,author,title,department_num,object_type) select user_num,patient_num,encounter_num,document_num,document_date,tmpresult_num,document_origin_code,author,title,department_num,object_type from ( $requete_sql ) t " );   
	        oci_execute($insall);
        
	        $ins = oci_parse($dbh,"insert /*+ APPEND */ into dwh_tmp_result_$user_num_session (user_num,patient_num, encounter_num,document_num,document_date,tmpresult_num,document_origin_code,author,title,department_num,object_type) select user_num,patient_num,encounter_num,document_num,document_date,tmpresult_num,document_origin_code,author,title,department_num,object_type from dwh_tmp_resultall_$user_num_session where tmpresult_num=$tmpresult_num $filter_query_user_right " );   
	        oci_execute($ins);
	}
}



function inserer_resultat_texte ($requete_sql,$tmpresult_num) {
        global $dbh,$login_session,$user_num_session;
        $requete_sql=preg_replace("/^ ?and /"," ",$requete_sql);
        $sel = oci_parse($dbh,"insert /*+ APPEND */ into dwh_tmp_result_$user_num_session (user_num,patient_num, encounter_num,document_num,document_date,tmpresult_num,document_origin_code,author,title,department_num,object_type) select '$user_num_session',patient_num,encounter_num,document_num,document_date,$tmpresult_num,document_origin_code,author,title,department_num,'document' from dwh_document where $requete_sql " );   
        oci_execute($sel);
}

function intersect_resultat_texte ($requete_sql,$tmpresult_num, $perimetre_intersect,$requete_sql_notexist) {
        global $dbh,$login_session,$user_num_session;
        if ($perimetre_intersect=='') {
                $perimetre_intersect='patient_num';
        }
        
        $sel = oci_parse($dbh,"delete from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num and not exists (select document_num from DWH_TMP_PRERESULT_$user_num_session where $requete_sql_notexist  and DWH_TMP_PRERESULT_$user_num_session.$perimetre_intersect=dwh_tmp_result_$user_num_session.$perimetre_intersect ) " );   
        oci_execute($sel);
        
	$requete_sql_completee=$requete_sql." and DWH_TMP_PRERESULT_$user_num_session.$perimetre_intersect = dwh_tmp_result_$user_num_session.$perimetre_intersect and tmpresult_num = $tmpresult_num )";
	inserer_resultat_texte ("$requete_sql_completee   AND dwh_document.document_num NOT IN (SELECT dwh_tmp_result_$user_num_session.document_num FROM dwh_tmp_result_$user_num_session WHERE tmpresult_num = $tmpresult_num  and object_type='document') ",$tmpresult_num);
}

function recuperer_resultat ($tmpresult_num,$full_text_query,$i_deb,$filtre_sql) {
        global $dbh,$modulo_ligne_ajoute,$liste_uf_session,$datamart_num,$liste_document_origin_code_session,$liste_service_session,$login_session,$user_num_session;
        
        //pour les datamart, les droits sont sur tous les services , cf verif_droit.php// 
	$filter_query_user_right=filter_query_user_right("dwh_tmp_result_$user_num_session",$user_num_session,$_SESSION['dwh_droit_all_departments'.$datamart_num],$liste_service_session,$liste_document_origin_code_session);
        
	//$liste_synonyme=recupere_liste_concept_full_texte ($full_text_query);
	$tableau_liste_synonyme=recupere_liste_concept_full_texte ($full_text_query,50);
        $tableau_resultat=array();
        $i_fin=$i_deb+$modulo_ligne_ajoute;
        $sel = oci_parse($dbh,"select patient_num from (select rownum i, patient_num from dwh_patient where patient_num in (select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num $filtre_sql $filter_query_user_right) and rownum<=$i_fin  order by patient_num desc ) t where i>$i_deb and i<=$i_fin  order by i asc" );   
        oci_execute($sel);
        while ($row = oci_fetch_array($sel, OCI_ASSOC)) {
                $patient_num=$row['PATIENT_NUM'];
                $nb_doc_par_patient=0;
                $tableau_document_origin_code_deja_fait=array();
                 $objects=array();
                $list_documents_in_result=get_table_objects_in_result ($tmpresult_num,$user_num_session, "and patient_num=$patient_num $filtre_sql $filter_query_user_right");
		foreach ($list_documents_in_result as $object_num  => $objects) {
		        $nb_doc_par_patient++;
			if ($objects['object_type']=='document') {
		                if ($nb_doc_par_patient<4) {
		                        $document_num=$object_num;
		                        $encounter_num=$objects['encounter_num'];
		                        $title=$objects['title'];
		                        $document_date=$objects['document_date'];
		                        $document_origin_code=$objects['document_origin_code'];
		                        $author=$objects['author'];
		                        $department_num=$objects['department_num'];
		                        $text=$objects['text'];   
		               		$tableau_document_origin_code_deja_fait[$document_origin_code]++;
					$department_str=get_department_str ($department_num);
		
		                        $tableau_resultat[$patient_num][$document_num]['title']=$title;
		                        $tableau_resultat[$patient_num][$document_num]['document_date']=$document_date;
		                        $tableau_resultat[$patient_num][$document_num]['document_origin_code']=$document_origin_code;
		                        $tableau_resultat[$patient_num][$document_num]['department_str']=$department_str;
		                        $tableau_resultat[$patient_num][$document_num]['author']=$author;
		                        $tableau_resultat[$patient_num][$document_num]['appercu']=resumer_resultat($text,"$full_text_query",$tableau_liste_synonyme,'moteur');
		                        $tableau_resultat[$patient_num][$document_num]['affiche_direct']="ok";
		                        $tableau_resultat[$patient_num][$document_num]['object_type']="document";
				} else {
		                        $document_num=$object_num;
		                        $encounter_num=$objects['encounter_num'];
		                        $title=$objects['title'];
		                        $document_date=$objects['document_date'];
		                        $document_origin_code=$objects['document_origin_code'];
		                        $author=$objects['author'];
		                        
		                        $tableau_resultat[$patient_num][$document_num]['object_type']="document";
		                        $tableau_resultat[$patient_num][$document_num]['title']=$title;
		                        $tableau_resultat[$patient_num][$document_num]['document_date']=$document_date;
		                        $tableau_resultat[$patient_num][$document_num]['document_origin_code']=$document_origin_code;
		                        $tableau_resultat[$patient_num][$document_num]['author']=$author;
		                        $tableau_resultat[$patient_num][$document_num]['appercu']="";
		                        $tableau_resultat[$patient_num][$document_num]['affiche_direct']="";
				}
			} 
			if ($objects['object_type']=='mvt') {
	                        if ($nb_doc_par_patient<4) {
		                        $mvt_num=$object_num;
		                        $entry_date=$objects['entry_date'];
		                        $out_date=$objects['out_date'];
		                        $document_origin_code=$objects['document_origin_code'];
		                        $department_num=$objects['department_num'];
		                        $encounter_num=$objects['encounter_num'];
		                        $type_mvt=$objects['type_mvt'];
		                        $unit_num=$objects['unit_num'];
		                        $unit_code=$objects['unit_code'];
		                        
		               		$tableau_document_origin_code_deja_fait[$document_origin_code]++;
					$department_str=get_department_str ($department_num);
					$unit_str=get_unit_str ($unit_num);
		
			                $appercu="";
			                if ($encounter_num!='') {
			                        $appercu.=" ".get_translation('ENCOUNTER','Séjour')." N° $encounter_num<br>";
			                }
			                $appercu.="Mouvement de type $type_mvt dans l'unité $unit_code-$unit_str ";
			                
			                
		                        $tableau_resultat[$patient_num][$mvt_num]['title']="Mouvement";
		                        $tableau_resultat[$patient_num][$mvt_num]['document_date']="du $entry_date au $out_date";
		                        $tableau_resultat[$patient_num][$mvt_num]['document_origin_code']=$document_origin_code;
		                        $tableau_resultat[$patient_num][$mvt_num]['department_str']=$department_str;
		                        $tableau_resultat[$patient_num][$mvt_num]['appercu']=$appercu;
		                        $tableau_resultat[$patient_num][$mvt_num]['affiche_direct']="ok";
		                        $tableau_resultat[$patient_num][$mvt_num]['object_type']="mvt";
		                        
		                        
		                } else {
		                        $mvt_num=$object_num;
		                        $entry_date=$objects['entry_date'];
		                        $out_date=$objects['out_date'];
		                        $document_origin_code=$objects['document_origin_code'];
		                        $department_num=$objects['department_num'];
		                        
		                        $tableau_resultat[$patient_num][$mvt_num]['object_type']="mvt";
		                        $tableau_resultat[$patient_num][$mvt_num]['title']="Mouvement";
		                        $tableau_resultat[$patient_num][$mvt_num]['document_date']="du $entry_date au $out_date";
		                        $tableau_resultat[$patient_num][$mvt_num]['document_origin_code']=$document_origin_code;
		                        $tableau_resultat[$patient_num][$mvt_num]['appercu']="";
		                        $tableau_resultat[$patient_num][$mvt_num]['affiche_direct']="";
		                }
			}
                }
        }
        return $tableau_resultat;
}

function ouvrir_plus_document ($tmpresult_num,$liste_object,$full_text_query,$tableau_liste_synonyme) {
        global $dbh,$modulo_ligne_ajoute,$liste_uf_session,$datamart_num,$liste_document_origin_code_session,$user_num_session;
        $res="";
        if ($liste_object!='') {
        	$req_filtre_doc="and document_num in ($liste_object)";
                $list_documents_in_result=get_table_objects_in_result ($tmpresult_num,$user_num_session,$req_filtre_doc);
		foreach ($list_documents_in_result as $object_num  => $objects) {
			if ($objects['object_type']=='document') {
	                        $document_num=$object_num;
	                        $encounter_num=$objects['encounter_num'];
	                        $title=$objects['title'];
	                        $document_date=$objects['document_date'];
	                        $document_origin_code=$objects['document_origin_code'];
	                        $author=$objects['author'];
	                        $department_num=$objects['department_num'];
	                        $text=$objects['text'];   
		                
				$department_str=get_department_str ($department_num);
	                
				$appercu=resumer_resultat($text,"$full_text_query",$tableau_liste_synonyme,'moteur');
	                
		                if ($_SESSION['dwh_droit_fuzzy_display']=='ok') {
		                	$document_date='[DATE]';
		                	$author='[AUTHOR]';
		                }
	                
		                $res.= "<div id=\"id_tr_document_$document_num\" class=\"document_resultat\">";
			                $res.= "<div id=\"id_button_$document_num\" class=\"div_voir_document\"><a class=\"voir_document\" href=\"#\" onclick=\"afficher_document('$document_num','$datamart_num');return false;\">";
			                
				                $res.= "$title ".get_translation('FROM_DATE','par')." $document_date ($document_origin_code)";
				                if ($author!='') {
				                        $res.=" ".get_translation('BY_FOLLOWED_BY_NAME','par')." $author";
				                }
				                if ($department_str!='') {
				                        $res.=" - $department_str";
				                }
				                $res.=" :</a>";
			                $res.= "</div>";
			                $res.= "<div class=\"appercu\">$appercu</div>";
		                $res.= "</div>";
		        }
			if ($objects['object_type']=='mvt') {
	                        $mvt_num=$object_num;
	                        $entry_date=$objects['entry_date'];
	                        $out_date=$objects['out_date'];
	                        $document_origin_code=$objects['document_origin_code'];
	                        $department_num=$objects['department_num'];
	                        $encounter_num=$objects['encounter_num'];
	                        $type_mvt=$objects['type_mvt'];
	                        $unit_num=$objects['unit_num'];
	                        $unit_code=$objects['unit_code'];
	                        
	                        
				$department_str=get_department_str ($department_num);
				$unit_str=get_unit_str ($unit_num);

		                $appercu="";
		                if ($encounter_num!='') {
		                        $appercu.=" ".get_translation('ENCOUNTER','Séjour')." N° $encounter_num<br>";
		                }
			        $appercu.="Mouvement de type $type_mvt dans l'unité $unit_code-$unit_str ";
		                $res.= "<div id=\"id_tr_document_$document_num\" class=\"document_resultat\">";
			                $res.= "<div id=\"id_button_$document_num\" class=\"div_voir_document\"><a class=\"voir_document\" href=\"#\" onclick=\"afficher_mvt('$document_num','$datamart_num');return false;\">";
			                
				                $res.= "Mouvement du $entry_date au $out_date - $unit_str";
				                $res.=" :</a>";
			                $res.= "</div>";
			                $res.= "<div class=\"appercu\">$appercu</div>";
		                $res.= "</div>";
			}
		        
	        }
	}
        return $res;
}

function recuperer_resultat_ancien ($tmpresult_num,$full_text_query,$i_deb,$filtre_sql) {
        global $dbh,$modulo_ligne_ajoute,$liste_uf_session,$datamart_num,$liste_document_origin_code_session,$liste_service_session,$login_session,$user_num_session;
        
        //pour les datamart, les droits sont sur tous les services , cf verif_droit.php// 
	$filter_query_user_right=filter_query_user_right("dwh_tmp_result_$user_num_session",$user_num_session,$_SESSION['dwh_droit_all_departments'.$datamart_num],$liste_service_session,$liste_document_origin_code_session);
        
	//$liste_synonyme=recupere_liste_concept_full_texte ($full_text_query);
	$tableau_liste_synonyme=recupere_liste_concept_full_texte ($full_text_query,50);
        $tableau_resultat=array();
        $i_fin=$i_deb+$modulo_ligne_ajoute;
        $sel = oci_parse($dbh,"select patient_num from (select rownum i, patient_num from dwh_patient where patient_num in (select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num $filtre_sql $filter_query_user_right) and rownum<=$i_fin  order by patient_num desc ) t where i>$i_deb and i<=$i_fin  order by i asc" );   
        oci_execute($sel);
        while ($row = oci_fetch_array($sel, OCI_ASSOC)) {
                $patient_num=$row['PATIENT_NUM'];
                $nb_doc_par_patient=0;
                $tableau_document_origin_code_deja_fait=array();
                
                $list_documents_in_result=get_table_objects_in_result ($tmpresult_num,$user_num_session, "and patient_num=$patient_num $filtre_sql $filter_query_user_right");
		foreach ($list_documents_in_result as $document_num => $object_type) {
			//$document_tmp_result=get_object_in_result($tmpresult_num,$user_num_session,$document_num);
                        //$object_type=$document_tmp_result['object_type'];
		        $nb_doc_par_patient++;
			if ( $object_type=='document') {
	                        if ($nb_doc_par_patient<4) {
					$document=get_document($document_num);
		                        $document_num=$document['document_num'];
		                        $encounter_num=$document['encounter_num'];
		                        $title=$document['title'];
		                        $document_date=$document['document_date'];
		                        $document_origin_code=$document['document_origin_code'];
		                        $author=$document['author'];
		                        $department_num=$document['department_num'];
		                        $text=$document['text'];   
		               		$tableau_document_origin_code_deja_fait[$document_origin_code]++;
					$department_str=get_department_str ($department_num);
		
		                        $tableau_resultat[$patient_num][$document_num]['title']=$title;
		                        $tableau_resultat[$patient_num][$document_num]['document_date']=$document_date;
		                        $tableau_resultat[$patient_num][$document_num]['document_origin_code']=$document_origin_code;
		                        $tableau_resultat[$patient_num][$document_num]['department_str']=$department_str;
		                        $tableau_resultat[$patient_num][$document_num]['author']=$author;
		                        $tableau_resultat[$patient_num][$document_num]['appercu']=resumer_resultat($text,"$full_text_query",$tableau_liste_synonyme,'moteur');
		                        $tableau_resultat[$patient_num][$document_num]['affiche_direct']="ok";
		                        $tableau_resultat[$patient_num][$document_num]['object_type']="document";
		                } else {
		                        $tableau_resultat[$patient_num][$document_num]['title']=$title;
		                        $tableau_resultat[$patient_num][$document_num]['document_date']=$document_date;
		                        $tableau_resultat[$patient_num][$document_num]['document_origin_code']=$document_origin_code;
		                        $tableau_resultat[$patient_num][$document_num]['author']=$author;
		                        $tableau_resultat[$patient_num][$document_num]['appercu']="";
		                        $tableau_resultat[$patient_num][$document_num]['affiche_direct']="";
		                }
			}
			if ( $object_type=='mvt') {
	                        
	                        if ($nb_doc_par_patient<4) {
	                       // if ($tableau_document_origin_code_deja_fait[$document_origin_code]=='' || $nb_doc_par_patient<3) {
					$mvt=get_mvt($document_num);
		                        $entry_date=$mvt['ENTRY_DATE'];
		                        $out_date=$mvt['OUT_DATE'];
					if ($_SESSION['dwh_droit_fuzzy_display']=='ok') {
						$entry_date='[entry_date]';
						$out_date='[out_date]';
					}
		                        $document_origin_code='MVT';
		                        $department_num=$mvt['DEPARTMENT_NUM'];
		                        $encounter_num=$mvt['ENCOUNTER_NUM'];
		                        $type_mvt=$mvt['TYPE_MVT'];   
		                        $unit_num=$mvt['UNIT_NUM'];    
		                        $unit_code=$mvt['UNIT_CODE'];  
		                        
		               		$tableau_document_origin_code_deja_fait[$document_origin_code]++;
					$department_str=get_department_str ($department_num);
					$unit_str=get_unit_str ($unit_num);
		
			                $appercu="";
			                if ($encounter_num!='') {
			                        $appercu.=" ".get_translation('ENCOUNTER','Séjour')." N° $encounter_num<br>";
			                }
			                $appercu.="Mouvement de type $type_mvt dans l'unité $unit_code-$unit_str ";
			                
			                
		                        $tableau_resultat[$patient_num][$document_num]['title']="Mouvement";
		                        $tableau_resultat[$patient_num][$document_num]['document_date']="du $entry_date au $out_date";
		                        $tableau_resultat[$patient_num][$document_num]['document_origin_code']=$document_origin_code;
		                        $tableau_resultat[$patient_num][$document_num]['department_str']=$department_str;
		                        $tableau_resultat[$patient_num][$document_num]['appercu']=$appercu;
		                        $tableau_resultat[$patient_num][$document_num]['affiche_direct']="ok";
		                        $tableau_resultat[$patient_num][$document_num]['object_type']="mvt";
		                } else {
		                        $tableau_resultat[$patient_num][$document_num]['title']="Mouvement";
		                        $tableau_resultat[$patient_num][$document_num]['document_date']="du $entry_date au $out_date";
		                        $tableau_resultat[$patient_num][$document_num]['document_origin_code']=$document_origin_code;
		                        $tableau_resultat[$patient_num][$document_num]['department_str']=$department_str;
		                        $tableau_resultat[$patient_num][$document_num]['appercu']="";
		                        $tableau_resultat[$patient_num][$document_num]['affiche_direct']="";
		                }
			}
                }
        }
        return $tableau_resultat;
}
function ouvrir_plus_document_ancien ($tmpresult_num,$liste_document,$full_text_query,$tableau_liste_synonyme) {
        global $dbh,$modulo_ligne_ajoute,$liste_uf_session,$datamart_num,$liste_document_origin_code_session,$user_num_session;
        $res="";
        if ($liste_document!='') {
                $list_documents_in_result=get_table_objects_in_result_ancien ($tmpresult_num,$user_num_session, "and document_num in ($liste_document)");
		foreach ($list_documents_in_result as $document_num => $object_type) {
			//$document_tmp_result=get_object_in_result($tmpresult_num,$user_num_session,$document_num);
                       // $object_type=$document_tmp_result['object_type'];
			if ( $object_type=='document') {
				$document=get_document($document_num);
	                        $document_num=$document['document_num'];
	                        $encounter_num=$document['encounter_num'];
	                        $title=$document['title'];
	                        $document_date=$document['document_date'];
	                        $document_origin_code=$document['document_origin_code'];
	                        $author=$document['author'];
	                        $department_num=$document['department_num'];
	                        $text=$document['text'];   
		                
				$department_str=get_department_str ($department_num);
	                
				$appercu=resumer_resultat($text,"$full_text_query",$tableau_liste_synonyme,'moteur');
	                
		                if ($_SESSION['dwh_droit_fuzzy_display']=='ok') {
		                	$document_date='[DATE]';
		                	$author='[AUTHOR]';
		                }
	                
		                $res.= "<div id=\"id_tr_document_$document_num\" class=\"document_resultat\">";
			                $res.= "<div id=\"id_button_$document_num\" class=\"div_voir_document\"><a class=\"voir_document\" href=\"#\" onclick=\"afficher_document('$document_num','$datamart_num');return false;\">";
			                
				                $res.= "$title ".get_translation('FROM_DATE','par')." $document_date ($document_origin_code)";
				                if ($author!='') {
				                        $res.=" ".get_translation('BY_FOLLOWED_BY_NAME','par')." $author";
				                }
				                if ($department_str!='') {
				                        $res.=" - $department_str";
				                }
				                $res.=" :</a>";
			                $res.= "</div>";
			                $res.= "<div class=\"appercu\">$appercu</div>";
		                $res.= "</div>";
		        }
			if ( $object_type=='mvt') {
				$mvt=get_mvt($document_num);
	                        
	                        $entry_date=$mvt['ENTRY_DATE'];
	                        $out_date=$mvt['OUT_DATE'];
	                        
	                        $document_origin_code='MVT';
	                        $department_num=$mvt['DEPARTMENT_NUM'];
	                        $encounter_num=$mvt['ENCOUNTER_NUM'];
	                        $type_mvt=$mvt['TYPE_MVT'];   
	                        $unit_num=$mvt['UNIT_NUM'];    
	                        $unit_code=$mvt['UNIT_CODE'];  
	                        
				if ($_SESSION['dwh_droit_fuzzy_display']=='ok') {
					$entry_date='[entry_date]';
					$out_date='[out_date]';
				}
				$department_str=get_department_str ($department_num);
				$unit_str=get_unit_str ($unit_num);

		                $appercu="";
		                if ($encounter_num!='') {
		                        $appercu.=" ".get_translation('ENCOUNTER','Séjour')." N° $encounter_num<br>";
		                }
			        $appercu.="Mouvement de type $type_mvt dans l'unité $unit_code-$unit_str ";
		                $res.= "<div id=\"id_tr_document_$document_num\" class=\"document_resultat\">";
			                $res.= "<div id=\"id_button_$document_num\" class=\"div_voir_document\"><a class=\"voir_document\" href=\"#\" onclick=\"afficher_mvt('$document_num','$datamart_num');return false;\">";
			                
				                $res.= "Mouvement du $entry_date au $out_date - $unit_str";
				                $res.=" :</a>";
			                $res.= "</div>";
			                $res.= "<div class=\"appercu\">$appercu</div>";
		                $res.= "</div>";
			}
		        
	        }
	}
        return $res;
}


function appercu_liste_document ($liste_document,$full_text_query) {
        global $dbh,$modulo_ligne_ajoute,$liste_uf_session,$datamart_num,$liste_document_origin_code_session,$login_session;
        $res="";
        $i=0;
	$tableau_liste_synonyme=recupere_liste_concept_full_texte ($full_text_query,50);
	$id_full_text_query=preg_replace("/[^a-z0-9]/i","",$full_text_query);
	
        if ($liste_document!='') {
	        $sel_doc = oci_parse($dbh,"select  document_num,patient_num,encounter_num, title,author,document_date,document_origin_code,text,department_num from dwh_text where document_num in ($liste_document) and context='text' and certainty=0 order by  document_date desc " );   
	        oci_execute($sel_doc);
	        while ($row_doc = oci_fetch_array($sel_doc, OCI_ASSOC)) {
	                $i++;
	                $document_num=$row_doc['DOCUMENT_NUM'];
	                $encounter_num=$row_doc['ENCOUNTER_NUM'];
	                $title=$row_doc['TITLE'];
	                $document_date=$row_doc['DOCUMENT_DATE'];
	                $document_origin_code=$row_doc['DOCUMENT_ORIGIN_CODE'];
	                $author=$row_doc['AUTHOR'];
	                $department_num=$row_doc['DEPARTMENT_NUM'];
	                $id_cle=$document_num.$id_full_text_query;
	                if ($i<=5) {
		                if ($row_doc['TEXT']!='') {
		                        $text=$row_doc['TEXT']->load();             
		                }
				$department_str=get_department_str ($department_num);
		                $appercu=resumer_resultat($text,"$full_text_query",$tableau_liste_synonyme,'moteur');
		                
		                if ($_SESSION['dwh_droit_fuzzy_display']=='ok') {
		                	$document_date='[DATE]';
		                	$author='[AUTHOR]';
		                }
		                
		                $res.= "<div id=\"id_tr_document_$id_cle\" class=\"document_resultat\">";
			                $res.= "<div id=\"id_button_".$id_cle."\" class=\"div_voir_document\"><a class=\"voir_document\" href=\"#\" onclick=\"afficher_document_patient_popup('$document_num','$full_text_query','$datamart_num','$id_cle');return false;\">";
				                $res.= "$title ".get_translation('FROM_DATE','par')." $document_date ($document_origin_code)";
				                if ($author!='') {
				                        $res.=" ".get_translation('BY_FOLLOWED_BY_NAME','par')." $author";
				                }
				                if ($department_str!='') {
				                        $res.=" - $department_str";
				                }
				                $res.=" :</a>";
			                $res.= "</div>";
			                $res.= "<div class=\"appercu\">$appercu</div>";
		                $res.= "</div>";
		                
		        }
	        }
	}
        return $res;
}

$cohort_num_encours='';


function get_patient ($patient_num,$option='') {
	global $dbh,$user_num_session;
	$tab_patient=array();
	if ($patient_num!='') {
     		$autorisation_voir_patient=autorisation_voir_patient ($patient_num,$user_num_session) ;
     		if ($autorisation_voir_patient=='ok') {
			$sel_patient= oci_parse($dbh,"select 
				patient_num,
				lastname,
				lower(firstname) as firstname,
				to_char(birth_date,'DD/MM/YYYY') as birth_date,
				sex,
				decode(birth_date,null,null,trunc((sysdate-birth_date)/365)) as age_an,
		                decode(birth_date,null,null,trunc((sysdate-birth_date)*12/365)) as age_mois,
		                trunc((death_date-birth_date)/365) as age_an_deces,
		                trunc((death_date-birth_date)*12/365) as age_mois_deces,
		                to_char(death_date,'DD/MM/YYYY') as death_date,
		                zip_code,
		                maiden_name,
		                residence_address,
		                residence_city,
		                phone_number,
		                residence_country,
		                residence_latitude,
		                residence_longitude,
		                birth_country,
		                birth_city,
		                birth_zip_code,
			        to_char(birth_date,'DD') as jour_nais,
			        to_char(birth_date,'MM') as mois_nais,
			        to_char(birth_date,'YYYY') as an_nais ,
			        to_char(birth_date,'DD/MM/YY') as date_nais_yy
		                 from dwh_patient where patient_num=$patient_num  " );   
	                oci_execute($sel_patient);
	                $tab_patient = oci_fetch_array($sel_patient, OCI_ASSOC);   
	                
       			$tab_patient['HOSPITAL_PATIENT_ID']=get_master_patient_id ($patient_num);
	                $autorisation_voir_patient_nominative=autorisation_voir_patient_nominative ($patient_num,$user_num_session);
	                if ($autorisation_voir_patient_nominative!='ok' && $option!='for_document_anonymization') {
                                $tab_patient['LASTNAME']=get_translation('PATIENT','Patient');
                                $tab_patient['FIRSTNAME']='';
                                $tab_patient['BIRTH_DATE']='';
                                $tab_patient['RESIDENCE_ADDRESS']='';
                                $tab_patient['RESIDENCE_CITY']='';
                                $tab_patient['PHONE_NUMBER']='';
                                $tab_patient['ZIP_CODE']='';
                                $tab_patient['MAIDEN_NAME']='';
                                $tab_patient['DEATH_DATE']='';
                                $tab_patient['BIRTH_ZIP_CODE']='';
	       		}
	       	}
        }
        return $tab_patient;
}


function afficher_patient ($patient_num,$option,$document_num,$cohort_num,$log_context='') {
        global $dbh,$datamart_num,$user_num_session;
        $nominative=0;
        $acces=0;
        $autorisation_voir_patient=autorisation_voir_patient ($patient_num,$user_num_session) ;
        if ($patient_num!='' && $autorisation_voir_patient=='ok') {
       		$tab_patient=get_patient ($patient_num);
                $hospital_patient_id=$tab_patient['HOSPITAL_PATIENT_ID'];         
                $lastname=$tab_patient['LASTNAME']; 
                if ($option=='patient' && $tab_patient['MAIDEN_NAME']!='' && $tab_patient['MAIDEN_NAME']!=$lastname) {
	                $lastname.=" (".$tab_patient['MAIDEN_NAME'].")";               
	        	}
                $firstname=ucfirst ($tab_patient['FIRSTNAME']);               
                $birth_date=$tab_patient['BIRTH_DATE'];             
                $sex=$tab_patient['SEX'];
                $age_an=$tab_patient['AGE_AN']; 
                $age_mois=$tab_patient['AGE_MOIS'];
                $age_an_deces=$tab_patient['AGE_AN_DECES']; 
                $age_mois_deces=$tab_patient['AGE_MOIS_DECES'];            
                $death_date=$tab_patient['DEATH_DATE'];                  
                $zip_code=$tab_patient['ZIP_CODE'];               
                $phone_number=$tab_patient['PHONE_NUMBER'];         
                if ($option=='resultat') {
        		if ($log_context=='') {
        			$log_context='resultat';
        		}
        		$acces=1;
                        if ($_SESSION['dwh_droit_nominative'.$datamart_num]=='ok' && $_SESSION['dwh_droit_anonymized'.$datamart_num]=='') {
                        	$nominative=1;
                                $res= "$lastname $firstname $birth_date $sex ($hospital_patient_id)";
                                if ($death_date!='') { 
                                	$res.=", ".get_translation('DEATH_THE_DATE','décès le')." $death_date ";
                                }
                        } else {
                                $res= get_translation('PATIENT','Patient')." $sex, ";
                        }
                        if ($age_an>=0) {
                        	if ($age_an_deces!='') {
	                                if ($age_an_deces<2) {
	                                        $res.="<br> ".get_translation('DECEASED_AT','décèdé à')." $age_mois_deces ".get_translation('MONTH','mois')." ";
	                                } else {
	                                        $res.="<br> ".get_translation('DECEASED_AT','décèdé à')." $age_an_deces ".get_translation('YEARS','ans')." ";
	                                }
                        	} else {
	                                if ($age_an<2) {
	                                        $res.="<br> $age_mois ".get_translation('MONTH','mois')." ";
	                                } else {
	                                        $res.="<br> $age_an ".get_translation('YEARS','ans')." ";
	                                }
	                        }
                        }
                        $res.="<br><a href=\"patient.php?patient_num=$patient_num\" target=\"_blank\"><img src=\"images/dossier_patient.png\" border=\"0\" alt=\"Dossier du patient\" title=\"Dossier du patient\"></a>";
                        $res.="<span class=\"icone_cohorte\"><img src=\"images/inclure_patient_cohorte.png\" alt=\"Inclure\" title=\"Inclure\" border=\"0\" width=\"25px\" onclick=\"inclure_patient_cohorte('$patient_num',1);\"></span>";
                        $res.="<span class=\"icone_cohorte\"><img src=\"images/noninclure_patient_cohorte.png\" alt=\"Exclure\" title=\"Exclure\" border=\"0\" width=\"25px\" onclick=\"inclure_patient_cohorte('$patient_num',0);\"></span>";
                        $res.="<span class=\"icone_cohorte\"><img src=\"images/doute_patient_cohorte.png\" alt=\"Doute\" title=\"Doute\" border=\"0\" width=\"25px\" onclick=\"inclure_patient_cohorte('$patient_num',2);\"></span>";
                        $res.="<span class=\"icone_cohorte\"><img class=\"img_pencil_resultat\" id=\"id_img_pencil_resultat_$patient_num\" src=\"images/pencil.png\" alt=\"Commenter\" title=\"Commenter\" border=\"0\" width=\"25px\" onclick=\"commenter_patient_cohorte('$patient_num','resultat');\"></span>";
                        
                        $res.="<div id=\"id_div_lister_commentaire_patient_cohorte_resultat_$patient_num\" class=\"div_lister_commentaire_patient_cohorte\" style=\"display:none;\">
                        	<table border=\"0\" width=\"100%\"><tr><td nowrap=nowrap><strong>".get_translation('COMMENTS_IN_COHORT','Commentaires dans la cohorte')." :</strong></td><td style=\"text-align:right;cursor:pointer\" onclick=\"plier_deplier('id_div_lister_commentaire_patient_cohorte_resultat_$patient_num');\">x</td></tr></table>
	                        <hr>
	                        <span id=\"id_div_lister_commentaire_patient_cohorte_contenu_resultat_$patient_num\"></span>
	                        <hr>
		                <span>
		                        <textarea cols=33 rows=3 id=\"id_textarea_ajouter_commentaire_patient_cohorte_resultat_$patient_num\" class=\"input_texte autosizejs\"></textarea><br>
		                        <input type=\"button\" value=\"".get_translation('ADD','ajouter')."\" onclick=\"sauver_commenter_patient_cohorte($patient_num,'resultat');\">                        
		                </span>
                        </div> ";
                        
                }
                if ($option=='document') {
        		if ($log_context=='') {
        			$log_context='document';
        		}
        		$acces=1;
        		$document=get_document($document_num);
		        $age_an_doc=$document['age_patient_year'];
		        $age_mois_doc=$document['age_patient_month'];
        		
                        if ($_SESSION['dwh_droit_nominative'.$datamart_num]=='ok' && $_SESSION['dwh_droit_anonymized'.$datamart_num]=='') {
                        	$nominative=1;
                                $res= "$lastname $firstname $birth_date $sex";
                        } else {
                                $res= get_translation('PATIENT','Patient')." $sex, ";
                        }
                        if ($age_an_doc!='' && $age_an_doc>=0) {
                                if ($age_an_doc<2) {
                                        $res.=" $age_mois_doc ".get_translation('MONTH','mois')." ";
                                } else {
                                        $res.=" $age_an_doc ".get_translation('YEARS','ans')." ";
                                }
                        }
                        $res.="<br><a href=\"patient.php?patient_num=$patient_num\" target=\"_blank\"><img src=\"images/dossier_patient.png\" border=\"0\" height=\"20px\"></a>";
                }
                if ($option=='mvt') {
        		if ($log_context=='') {
        			$log_context='document_mvt';
        		}
        		$acces=1;
        		$mvt=get_mvt($document_num);
        		
		        $age_an_doc=$mvt['age_patient_year'];
		        $age_mois_doc=$mvt['age_patient_month'];
        		
                        if ($_SESSION['dwh_droit_nominative'.$datamart_num]=='ok' && $_SESSION['dwh_droit_anonymized'.$datamart_num]=='') {
                        	$nominative=1;
                                $res= "$lastname $firstname $birth_date $sex";
                        } else {
                                $res= get_translation('PATIENT','Patient')." $sex, ";
                        }
                        if ($age_an_doc!='' && $age_an_doc>=0) {
                                if ($age_an_doc<2) {
                                        $res.=" $age_mois_doc ".get_translation('MONTH','mois')." ";
                                } else {
                                        $res.=" $age_an_doc ".get_translation('YEARS','ans')." ";
                                }
                        }
                        $res.="<br><a href=\"patient.php?patient_num=$patient_num\" target=\"_blank\"><img src=\"images/dossier_patient.png\" border=\"0\" height=\"20px\"></a>";
                }
                if ($option=='patient') {
        		if ($log_context=='') {
        			$log_context='dossier';
        		}
        		$acces=1;
	                $autorisation_voir_patient_nominative=autorisation_voir_patient_nominative ($patient_num,$user_num_session);
                        if ($autorisation_voir_patient_nominative=='ok') {
                        	$nominative=1;
                                $res= "$lastname $firstname $birth_date $sex ($hospital_patient_id)";
                                if ($death_date!='') { 
                                	$res.=", ".get_translation('DEATH_THE_DATE','décès le')." $death_date ";
                                }
                        } else {
                                $res= get_translation('PATIENT','Patient')." $sex, ";
                        }
                	if ($age_an_deces!='') {
                                if ($age_an_deces<2) {
                                        $res.="<br> ".get_translation('DEATH_AT_AGE','décès à')." $age_mois_deces ".get_translation('MONTH','mois')." ";
                                } else {
                                        $res.="<br> ".get_translation('DEATH_AT_AGE','décès à')." $age_an_deces ".get_translation('YEARS','ans')." ";
                                }
                	} else {
	                        if ($age_an>=0) {
	                                if ($age_an<2) {
	                                        $res.="<br> $age_mois ".get_translation('MONTH','mois')." ";
	                                } else {
	                                        $res.="<br> $age_an ".get_translation('YEARS','ans')." ";
	                                }
	                        }
                        }
                }
                if ($option=='cohorte') {
        		if ($log_context=='') {
        			$log_context='cohorte';
        		}
        		$acces=1;
        		$autorisation_cohorte_voir_patient_nominative_precis=autorisation_cohorte_voir_patient_nominative_precis($cohort_num,$user_num_session,$patient_num);
                	if ($autorisation_cohorte_voir_patient_nominative_precis=='ok') {
                		$nominative=1;
                                $res= "$lastname $firstname $birth_date $sex ($hospital_patient_id)";
                                if ($death_date!='') { 
                                	$res.=", ".get_translation('DEATH_THE_DATE','décès le')." $death_date ";
                                }
                        } else {
                                $res= get_translation('PATIENT','Patient')." $sex, ";
                        }
                        
                	if ($age_an_deces!='') {
                                if ($age_an_deces<2) {
                                        $res.=", ".get_translation('DEATH_AT_AGE','décès à')." $age_mois_deces ".get_translation('MONTH','mois')." ";
                                } else {
                                        $res.=", ".get_translation('DEATH_AT_AGE','décès à')." $age_an_deces ".get_translation('YEARS','ans')." ";
                                }
                	} else {
	                        if ($age_an>=0) {
	                                if ($age_an<2) {
	                                        $res.=", $age_mois ".get_translation('MONTH','mois')." ";
	                                } else {
	                                        $res.=", $age_an ".get_translation('YEARS','ans')." ";
	                                }
	                        }
	                }
	        	$autorisation_cohorte_ajouter_patient=autorisation_cohorte_ajouter_patient($cohort_num,$user_num_session);
	        	list($add_date,$num_user_ajout,$user_name_ajout)=lister_info_cohorte_patient ($patient_num,$cohort_num);
#	        	$query_num_inclusion=get_query_inclusion ($cohort_num, $patient_num);
#	        	$query_inclusion_patient=get_query_clear($query_num_inclusion);
	        	
	        	$res.= " </td><td>(le $add_date par $user_name_ajout)</td> ";
                        $res.="<td><a href=\"patient.php?patient_num=$patient_num&cohort_num_patient=$cohort_num&datamart_num=$datamart_num\" target=\"_blank\"><img src=\"images/dossier_patient.png\" alt=\"Dossier du patient\" title=\"Dossier du patient\" border=\"0\" height=\"15px\"></a>&nbsp;&nbsp;";
	        	if ($autorisation_cohorte_ajouter_patient=='ok') {
	                        $res.="<img src=\"images/inclure_patient_cohorte.png\" border=\"0\" width=\"15px\" onclick=\"inclure_patient_cohorte('$patient_num',1);\" style=\"cursor:pointer\" alt=\"Inclure\" title=\"Inclure\">&nbsp;&nbsp;";
	                        $res.="<img src=\"images/noninclure_patient_cohorte.png\" border=\"0\" width=\"15px\" onclick=\"inclure_patient_cohorte('$patient_num',0);\" style=\"cursor:pointer\" alt=\"Exclure\" title=\"Exclure\">&nbsp;&nbsp;";
	                        $res.="<img src=\"images/doute_patient_cohorte.png\" border=\"0\" width=\"15px\" onclick=\"inclure_patient_cohorte('$patient_num',2);\" style=\"cursor:pointer\" alt=\"Doute\" title=\"Doute\">&nbsp;&nbsp;";
	                        
	                        $nb_comment_cohorte=calcul_nb_comment_cohorte($patient_num,$cohort_num);
	                        if ($nb_comment_cohorte>0) {
	                        	$icone_pencil="pencil_red";
	                        } else {
	                        	$icone_pencil="pencil";
	                        }
                       		$res.="<img id=\"id_img_pencil_cohorte_$patient_num\" src=\"images/$icone_pencil.png\" border=\"0\" width=\"15px\" onclick=\"commenter_patient_cohorte('$patient_num','cohorte');\" style=\"cursor:pointer\" alt=\"Commenter\" title=\"Commenter\">&nbsp;&nbsp;";
                        	#if ($query_num_inclusion!='') {
	                       		$res.="<img src=\"images/search.png\" border=\"0\" width=\"18px\" onclick=\"display_query_inclusion('$patient_num','$cohort_num','$query_num_inclusion');plier_deplier('id_div_display_query_inclusion_cohort_$patient_num');\" style=\"cursor:pointer\" alt=\"Inclusion Query\" title=\"Inclusion Query\">";
	                       	#}
	                       	
	                        $res.="<div id=\"id_div_lister_commentaire_patient_cohorte_cohorte_$patient_num\" class=\"div_lister_commentaire_patient_cohorte\" style=\"display:none;\">
                        	<table border=\"0\" width=\"100%\"><tr><td nowrap=nowrap><strong>".get_translation('COMMENTS_IN_COHORT','Commentaires dans la cohorte')." :</strong></td><td style=\"text-align:right;cursor:pointer\" onclick=\"plier_deplier('id_div_lister_commentaire_patient_cohorte_cohorte_$patient_num');\">x</td></tr></table>
	                        <hr>
	                        <span id=\"id_div_lister_commentaire_patient_cohorte_contenu_cohorte_$patient_num\"></span>
	                        <hr>
		                <span>
		                        <textarea cols=33 rows=3 id=\"id_textarea_ajouter_commentaire_patient_cohorte_cohorte_$patient_num\" class=\"input_texte autosizejs\"></textarea><br>
		                        <input type=\"button\" value=\"".get_translation('ADD','ajouter')."\" onclick=\"sauver_commenter_patient_cohorte($patient_num,'cohorte');\">                        
		                </span>
                        	</div> ";
                        	#if ($query_num_inclusion!='') {
		                        $res.="<div id=\"id_div_display_query_inclusion_cohort_$patient_num\" class=\"div_lister_commentaire_patient_cohorte\" style=\"display:none;\">
                        	<table border=\"0\" width=\"100%\"><tr><td nowrap=nowrap><strong>".get_translation('QUERY_USED_FOR_INCLUSION','Requête pour inclure ce patient')." :</strong></td><td style=\"text-align:right;cursor:pointer\" onclick=\"plier_deplier('id_div_display_query_inclusion_cohort_$patient_num');\">x</td></tr></table><span id='id_span_display_query_inclusion_cohort_$patient_num'>$query_inclusion_patient</span></div>";
                        	#}
	                }
                }
                
                if ($option=='demande_acces') {
        		if ($log_context=='') {
        			$log_context='demande_acces';
        		}
                        $nominative=1;
        		$acces=1;
                        $res= "$lastname $firstname $birth_date $sex ($hospital_patient_id)";
                        if ($death_date!='') { 
                        	$res.=", ".get_translation('DEATH_THE_DATE','décès le')." $death_date ";
                        }
                	if ($age_an_deces!='') {
                                if ($age_an_deces<2) {
                                        $res.="<br> ".get_translation('DEATH_AT_AGE','décès à')." $age_mois_deces ".get_translation('MONTH','mois')." ";
                                } else {
                                        $res.="<br> ".get_translation('DEATH_AT_AGE','décès à')." $age_an_deces ".get_translation('YEARS','ans')." ";
                                }
                	} else {
	                        if ($age_an>=0) {
	                                if ($age_an<2) {
	                                        $res.=", $age_mois ".get_translation('MONTH','mois')." ";
	                                } else {
	                                        $res.=", $age_an ".get_translation('YEARS','ans')." ";
	                                }
	                        }
	                }
                        $res.="</td><td><a href=\"patient.php?patient_num=$patient_num&cohort_num_patient=$cohort_num&datamart_num=$datamart_num\" target=\"_blank\"><img src=\"images/dossier_patient.png\" border=\"0\" height=\"15px\"></a>&nbsp;&nbsp;";
                }
                
                if ($option=='demande_acces_copier_coller') {
        		if ($log_context=='') {
        			$log_context='demande_acces';
        		}
                        $nominative=1;
        		$acces=1;
                        $res= "$hospital_patient_id	$initiales	$lastname	$firstname	$birth_date	$death_date\n";
                }
                
                if ($option=='demande_acces_excel') {
        		if ($log_context=='') {
        			$log_context='demande_acces';
        		}
        		$autorisation_voir_patient_nominative=autorisation_voir_patient_nominative ($patient_num,$user_num_session);
                	if ($autorisation_voir_patient_nominative=='ok') {
                		$nominative=1;
        			$acces=1;
                        	$initiales='';
				$tableau_lastname=explode(' ',"$lastname $firstname");
				foreach ($tableau_lastname as $in) {
					$initiales.=substr($in,0,1); 
				}
				$initiales=strtoupper($initiales);
                                $res= "<tr><td class=\"text\">$hospital_patient_id</td><td class=\"text\">$initiales</td><td class=\"text\">$lastname</td><td class=\"text\">$firstname</td><td class=\"text\">$birth_date</td><td class=\"text\">$death_date</td><td class=\"text\">$zip_code</td></tr>";
                        }
                }
                
                if ($option=='cohorte_textarea') {
        		if ($log_context=='') {
        			$log_context='cohorte';
        		}
        		$autorisation_cohorte_voir_patient_nominative_precis=autorisation_cohorte_voir_patient_nominative_precis($cohort_num,$user_num_session,$patient_num);
                	if ($autorisation_cohorte_voir_patient_nominative_precis=='ok') {
                		$nominative=1;
				$acces=1;
                        	$initiales='';
				$tableau_lastname=explode(' ',"$lastname $firstname");
				foreach ($tableau_lastname as $in) {
					$initiales.=substr($in,0,1); 
				}
				$initiales=strtoupper($initiales);
                                $res= "$hospital_patient_id	$initiales	$lastname	$firstname	$birth_date	$death_date\n";
                        }
                }
                if ($option=='cohorte_excel') {
        		if ($log_context=='') {
        			$log_context='cohorte';
        		}
        		$autorisation_cohorte_voir_patient_nominative_precis=autorisation_cohorte_voir_patient_nominative_precis($cohort_num,$user_num_session,$patient_num);
                	if ($autorisation_cohorte_voir_patient_nominative_precis=='ok') {
                		$nominative=1;
				$acces=1;
                        	$initiales='';
				$tableau_lastname=explode(' ',"$lastname $firstname");
				foreach ($tableau_lastname as $in) {
					$initiales.=substr($in,0,1); 
				}
				$initiales=strtoupper($initiales);
        			list($add_date,$num_user_ajout,$user_name_ajout)=lister_info_cohorte_patient ($patient_num,$cohort_num);
                     		$res= "<td class=\"text\">$hospital_patient_id</td><td class=\"text\">$initiales</td><td class=\"text\">$lastname</td><td class=\"text\">$firstname</td><td class=\"text\">$birth_date</td><td class=\"text\">$death_date</td><td class=\"text\">$zip_code</td><td class=\"text\">$phone_number</td><td class=\"text\">$add_date</td><td class=\"text\">$user_name_ajout</td>";
                        }
                }
                if ($option=='result_excel') {
	        		if ($log_context=='') {
	        			$log_context='result_excel';
	        		}
	        		$nominative=1;
					$acces=1;
					$initiales='';
					$tableau_lastname=explode(' ',"$lastname $firstname");
					foreach ($tableau_lastname as $in) {
						$initiales.=substr($in,0,1); 
					}
					$initiales=strtoupper($initiales);
             		$res= "<td class=\"text\">$hospital_patient_id</td><td class=\"text\">$initiales</td><td class=\"text\">$lastname</td><td class=\"text\">$firstname</td><td class=\"text\">$birth_date</td><td class=\"text\">$death_date</td><td class=\"text\">$zip_code</td><td class=\"text\">$phone_number</td>";
                }
                if ($option=='requete') {
	        		if ($log_context=='') {
	        			$log_context='requete';
	        		}
	                $autorisation_voir_patient=autorisation_voir_patient ($patient_num,$user_num_session);
                        if ($autorisation_voir_patient=='ok') {
        			$acces=1;
		                $autorisation_voir_patient_nominative=autorisation_voir_patient_nominative ($patient_num,$user_num_session);
	                        if ($autorisation_voir_patient_nominative=='ok') {
                        		$nominative=1;
	                                $res= "$lastname $firstname $birth_date $sex ($hospital_patient_id)";
	                                if ($death_date!='') { 
	                                	$res.=", ".get_translation('DEATH_THE_DATE','décès le')." $death_date ";
	                                }
	                        } else {
	                                $res= get_translation('PATIENT','Patient')." $sex, ";
	                        }
	                	if ($age_an_deces!='') {
	                                if ($age_an_deces<2) {
	                                        $res.="<br> ".get_translation('DEATH_AT_AGE','décès à')." $age_mois_deces ".get_translation('MONTH','mois')." ";
	                                } else {
	                                        $res.="<br> ".get_translation('DEATH_AT_AGE','décès à')." $age_an_deces ".get_translation('YEARS','ans')." ";
	                                }
	                	} else {
		                        if ($age_an>=0) {
		                                if ($age_an<2) {
		                                        $res.=", $age_mois ".get_translation('MONTH','mois')." ";
		                                } else {
		                                        $res.=", $age_an ".get_translation('YEARS','ans')." ";
		                                }
		                        }
		                }
	                        $res.="</td><td  style=\"border: 0px solid black;\"><a href=\"patient.php?patient_num=$patient_num\" target=\"_blank\"><img src=\"images/dossier_patient.png\" border=\"0\" height=\"15px\"></a>";
	                } else {
	                	//$res.="Non autorisé</td><td>";
	                }
                }
                if ($option=='basique') {
        		if ($log_context=='') {
        			$log_context='basique';
        		}
              		$autorisation_voir_patient_nominative=autorisation_voir_patient_nominative ($patient_num,$user_num_session);
                	if ($autorisation_voir_patient_nominative=='ok') {
                		$nominative=1;
				$acces=1;
                                $res= "$lastname $firstname $birth_date";
                        } else {
                                $res= "lastname firstname birth_date";
                        }
                }
                if ($option=='lastname firstname') {
        		if ($log_context=='') {
        			$log_context='lastname firstname';
        		}
              		$autorisation_voir_patient_nominative=autorisation_voir_patient_nominative ($patient_num,$user_num_session);
                	if ($autorisation_voir_patient_nominative=='ok') {
                		$nominative=1;
				$acces=1;
                                $res= "$lastname $firstname";
                        } else {
                                $res= "lastname firstname";
                        }
                }
                if ($option=='lastname firstname ddn') {
        		if ($log_context=='') {
        			$log_context='lastname firstname ddn';
        		}
              		$autorisation_voir_patient_nominative=autorisation_voir_patient_nominative ($patient_num,$user_num_session);
                	if ($autorisation_voir_patient_nominative=='ok') {
                		$nominative=1;
				$acces=1;
                                $res= "$lastname $firstname $birth_date";
                        } else {
                                $res= "lastname firstname birth_date";
                        }
                }
                
                if ($option=='textarea') {
        		if ($log_context=='') {
        			$log_context='textarea';
        		}
              		$autorisation_voir_patient_nominative=autorisation_voir_patient_nominative ($patient_num,$user_num_session);
                	if ($autorisation_voir_patient_nominative=='ok') {
                		$nominative=1;
				$acces=1;
                        	$initiales='';
				$tableau_lastname=explode(' ',"$lastname $firstname");
				foreach ($tableau_lastname as $in) {
					$initiales.=substr($in,0,1); 
				}
				$initiales=strtoupper($initiales);
                                $res= "$hospital_patient_id	$initiales	$lastname	$firstname	$birth_date\n";
                        }
                }
                if ($acces==1) {
	                log_acces_patient ($patient_num,$nominative,$log_context);
	        }
                
        } else {
		if ($option=='cohorte_excel') {
			$res= "<td class=\"text\"></td><td class=\"text\"></td><td class=\"text\"></td><td class=\"text\"></td><td class=\"text\"></td><td class=\"text\"></td><td class=\"text\"></td><td class=\"text\"></td><td class=\"text\"></td><td class=\"text\"></td>";
		}
                if ($option=='result_excel') {
             		$res= "<td class=\"text\"></td><td class=\"text\"></td><td class=\"text\"></td><td class=\"text\"></td><td class=\"text\"></td><td class=\"text\"></td><td class=\"text\"></td><td class=\"text\"></td>";
                }    
        }
        return $res;
}
$limite_document=5;


function log_acces_patient ($patient_num,$nominative,$log_context) {
	global $dbh,$user_num_session;
	if ($nominative=='') {
		$nominative=0;
	}
	if ($log_context=='') {
		$log_context='non déterminé';
	}
        if ($user_num_session!='') {
	        $sel_texte = oci_parse($dbh,"insert into dwh_log_patient (user_num ,patient_num ,log_date , nominative,log_context ) values ($user_num_session ,'$patient_num' ,sysdate , $nominative,'$log_context') " );   
	        oci_execute($sel_texte);
	}
}



function afficher_resultat ($tmpresult_num,$tableau_resultat,$i_deb,$cohort_num_encours) {
        global $limite_document,$datamart_num;
        
        $res="";
        $i=0;
	$numero_patient=$i_deb;
        foreach ($tableau_resultat as $patient_num => $tab_id_document) {
                $numero_patient=$numero_patient+1;
                if ($i%2==0) {
                        $color="#E6EEF0";
                        $class="pair";
                } else {
                        $color="#FFFFFF";
                        $class="impair";
                }
                $nb_document_patient=count($tableau_resultat[$patient_num]);
                $res.= "<tr id=\"id_tr_patient_$patient_num\" class=\"$class\" style=\"background-color:$color;\"><td class=\"patient_resultat\">
                <div class=\"numero_patient\">$numero_patient</div>
               <div style=\"position:relative;min-height:75px;\">";
                $res.=afficher_patient($patient_num,'resultat','',$cohort_num_encours);
                $res.="</div>
               </td><td>";
                $n=0;
                $liste_document='';
                foreach ($tab_id_document as $document_num => $sous_tab) {
                        $n++;
                        if ($tableau_resultat[$patient_num][$document_num]['affiche_direct']=="ok") {
                        
	                        if ($_SESSION['dwh_droit_fuzzy_display']=='ok') {
	                        	$tableau_resultat[$patient_num][$document_num]['document_date']='[DATE]';
	                        	$tableau_resultat[$patient_num][$document_num]['author']='[AUTHOR]';
	                        }
                        
	                        $res.= "<div id=\"id_tr_document_$document_num\" class=\"document_resultat\">";
	                        $res.= "<div id=\"id_button_$document_num\" class=\"div_voir_document\">";
	                        if ($tableau_resultat[$patient_num][$document_num]['object_type']=='document') {
	                    	    $res.= "<a class=\"voir_document\" href=\"#\" onclick=\"afficher_document('$document_num','$datamart_num');return false;\">";
	                    	} else if ($tableau_resultat[$patient_num][$document_num]['object_type']=='mvt') {
	                    	    $res.= "<a class=\"voir_document\" href=\"#\" onclick=\"afficher_mvt('$document_num','$datamart_num');return false;\">";
	                    	}
	                        $res.= $tableau_resultat[$patient_num][$document_num]['title'];
	                        $res.=" ".get_translation('DATE_FROM','du')." ".$tableau_resultat[$patient_num][$document_num]['document_date'];
	                        $res.=" (".$tableau_resultat[$patient_num][$document_num]['document_origin_code'].")";
	                        if ($tableau_resultat[$patient_num][$document_num]['author']!='') {
		                        $res.=" ".get_translation('BY_FOLLOWED_BY_NAME','par')." ".$tableau_resultat[$patient_num][$document_num]['author'];
		                }
		                
	                        if ($tableau_resultat[$patient_num][$document_num]['department_str']!='') {
		                        $res.=" - ".$tableau_resultat[$patient_num][$document_num]['department_str'];
		                }
	                        $res.=" :</a>";
	                        $res.= "</div>";
	                        $res.= "<div class=\"appercu\">".$tableau_resultat[$patient_num][$document_num]['appercu']."</div>";
	                        $res.= "</div>";
	                } else {
		               $liste_document.="$document_num,";
	                }
                }
                if ($liste_document!='') {
	                $liste_document=substr($liste_document,0,-1);
                	$res.= "<input type=\"hidden\" value=\"$liste_document\" id=\"id_input_liste_document_plus_$patient_num\">";
                        $res.= "<span id=\"id_span_ouvrir_plus_document_$patient_num\" class=\"filtre_texte_ouvrir_avance\"><a onclick=\"ouvrir_plus_document('$patient_num','$tmpresult_num');\"><span id=\"plus_id_div_ouvrir_plus_document_$patient_num\">+</span> ".get_translation('DISPLAY_OTHER_DOCUMENTS','afficher les autres documents')."</a></span>";
                        $res.= "<div style=\"display:none;\" id=\"id_div_ouvrir_plus_document_$patient_num\"></div>";
                }
                $res.= "</td></tr>";
                $i++;
        }
        return $res;
}

function supprimer_html ($text) {
	
	$text = preg_replace("/<script[^>]*?>.*?<\/script>/i", " ", $text);
	$text = preg_replace("/<style[^>]*?>.*?<\/style>/i", " ", $text);
	$text = preg_replace("/<head[^>]*?>.*?<\/head>/i", " ", $text);
	$text = preg_replace("/<img [^>]*>/i", " ", $text);
	
	$text = preg_replace("/<strong[^>]*?>/i", "", $text);
	$text = preg_replace("/<\/strong>/i", " ", $text);
	$text = preg_replace("/<div[^>]*?>/i", " ", $text);
	$text = preg_replace("/<td[^>]*?>/i", " ", $text);
	$text = preg_replace("/<th[^>]*?>/i", " ", $text);
	$text = preg_replace("/<\/td>/i", ".", $text);
	$text = preg_replace("/<\/div>/i", ".", $text);
	$text = preg_replace("/<\/th>/i", ".", $text);
	$text=preg_replace("/<tr[^>]*?>/",".\n",$text);
	$text = preg_replace("/<\/tr>/i", ".", $text);
	
	$text = preg_replace("/<table[^>]*?>/i", " ", $text);
	$text = preg_replace("/<\/table>/i", " ", $text);
	
	$text = preg_replace("/<thead>/i", " ", $text);
	$text = preg_replace("/<\/thead>/i", " ", $text);
	
	$text = preg_replace("/<tbody>/i", " ", $text);
	$text = preg_replace("/<\/tbody>/i", " ", $text);
	
	$text = preg_replace("/<body>/i", " ", $text);
	$text = preg_replace("/<\/body>/i", " ", $text);
	$text = preg_replace("/<body [^>]+>/i", " ", $text);
	
	$text=preg_replace("/<br\/?>/i",".\n",$text);
	
	$text = preg_replace("/<b ?[^>]*?>/i", "", $text);
	$text = preg_replace("/<\/b>/i", " ", $text);
	
	$text = preg_replace("/<legend>/i", " ", $text);
	$text = preg_replace("/<\/legend>/i", ".", $text);
	
	$text = preg_replace("/<h[0-9]>/i", " .", $text);
	$text = preg_replace("/<\/h[0-9]>/i", ".", $text);
	
	$text = preg_replace("/<a>/i", " ", $text);
	$text = preg_replace("/<\/a>/i", "", $text);
	$text = preg_replace("/<a [^>]+>/i", "", $text);
	
	$text = preg_replace("/<u[^>]*?>/i", "", $text);
	$text = preg_replace("/<\/u>/i", "", $text);
	
	$text = preg_replace("/<html>/i", " ", $text);
	$text = preg_replace("/<\/html>/i", " ", $text);
	
	$text = preg_replace("/<title>/i", " ", $text);
	$text = preg_replace("/<\/title>/i", " ", $text);
	
	$text = preg_replace("/<fieldset [^>]+>/i", " ", $text);
	$text = preg_replace("/<fieldset>/i", " ", $text);
	$text = preg_replace("/<\/fieldset>/i", ".", $text);
	
	
	$text = preg_replace("/<[a-z][^>]+>/i", " ", $text);
	$text = preg_replace("/<\/<[a-z][^>]+>/i", " ", $text);
		
	$text = preg_replace("/</"," <",$text);
	$text = preg_replace("/>/","> ",$text); 
	
	$text = preg_replace("/&#160;/i", " ", $text);
	
	//$text= strip_tags($text);
	$text=html_entity_decode ($text ,0,"ISO8859-1");
	
	return $text;
}

function resumer_resultat($text,$full_text_query,$tableau_liste_synonyme,$type_affichage) {
	global $login_session;
	// si requete est une expression reguliere
//	$text=supprimer_html ($text); // peut etre supprimer ce passage ... 2019 06 14 
	$tableau_texte=array();
	$test_expression_reguliere='';
	if (preg_match("/[[|?+{]/",$full_text_query)) {
		$test_expression_reguliere='ok';
	}
	if ($type_affichage=='patient' && $test_expression_reguliere=='ok') {
		$text=surligner_resultat_exp_reguliere ($text,$full_text_query,'non');
	} else {
		$text=surligner_resultat ($text,$full_text_query,'','non',$tableau_liste_synonyme,'');
		if (!preg_match("/highlight/i"," $text ")) {
			$text=surligner_resultat ($text,$full_text_query,'maxdistance','non',$tableau_liste_synonyme,'');
		}
		if (!preg_match("/highlight/i"," $text ")) {
			$text=surligner_resultat ($text,$full_text_query,'unitaire','non',$tableau_liste_synonyme,'');
		}
		
		// NG 2019 06 13
		//$tableau_texte=preg_split("/[.!?\n]/",$text);
		
	}
	$texte_appercu="";
	$text=preg_replace("/[\n\r]/"," ",$text);
	//preg_match_all("/[^>]{0,30}<b.*?highlight.*?<\/b>[^<]{0,30}/i","                                  $text                                  ",$matches,PREG_PATTERN_ORDER);
	preg_match_all("/[^>]{0,30}<b.*?highlight.*?<\/b>[^<]{0,30}/i","$text",$matches,PREG_PATTERN_ORDER); // NG 2019 07 22 
	foreach ($matches[0] as $ligne) {
		$ligne=trim($ligne);
		$texte_appercu.=$ligne." [...] ";
	} 
	
	if ($texte_appercu=='' && $test_expression_reguliere=='') {
		$tableau_texte=preg_split("/[.!?\n]/",$text);
		$texte_appercu='';
		foreach ($tableau_texte as $ligne) {
			$ligne=trim($ligne);
			if ($ligne!='') {
				if (preg_match("/highlight/i"," $ligne ")) {
					$texte_appercu.=$ligne." [...] ";
				}
			}
		}
	}
	return $texte_appercu;
}

function afficher_document($document_num,$full_text_query,$tableau_liste_synonyme) {
        global $dbh,$datamart_num,$user_num_session;
        $document=get_document ($document_num);
        //$sel_texte = oci_parse($dbh,"select displayed_text,title,patient_num,to_char(document_date,'DD/MM/YYYY') as char_date_document from dwh_document where document_num=$document_num" );   
        //oci_execute($sel_texte);
        //$row_texte = oci_fetch_array($sel_texte, OCI_ASSOC);
        //if ($row_texte['DISPLAYED_TEXT']!='') {
        //        $displayed_text=$row_texte['DISPLAYED_TEXT']->load();         
        //}
        
	$displayed_text=$document['displayed_text'];     
        $title=$document['title'];     
        $patient_num=$document['patient_num']; 
        $document_date=$document['document_date']; 
        if ($_SESSION['dwh_droit_see_debug']=='ok') {
	       $displayed_text= afficher_dans_document_tal($document_num,$user_num_session);
	}
	$nominative='oui';
        if ($_SESSION['dwh_droit_nominative'.$datamart_num]=='' || $_SESSION['dwh_droit_anonymized'.$datamart_num]=='ok') {
                $displayed_text=anonymisation_document ($document_num,$displayed_text);
                $nominative='non';
                $document_date='[DATE]';
        }
        
        $displayed_text=nettoyer_pour_afficher ($displayed_text);
        $displayed_text=surligner_resultat ($displayed_text,$full_text_query,'','oui',$tableau_liste_synonyme,'');
        if (!preg_match("/highlight/",$displayed_text) ) {
                $displayed_text=surligner_resultat ($displayed_text,$full_text_query,'maxdistance','oui',$tableau_liste_synonyme,'');
        }
        if (!preg_match("/highlight/",$displayed_text) ) {
                $displayed_text=surligner_resultat ($displayed_text,$full_text_query,'unitaire','oui',$tableau_liste_synonyme,'');
        }
	$displayed_text=display_image_in_document ($patient_num,$document_num,$user_num_session,$displayed_text);
        
	$display_list_file="<br><br>".display_list_file ($patient_num,$document_num,$user_num_session);

    $res= "
    <div class=\"ui-widget ui-widget-content ui-corner-all ui-front ui-draggable ui-resizable class_document\" style=\"position: absolute; height: 350px; width: 650px; display: none;\" tabindex=\"-1\" id=\"id_enveloppe_document_$document_num\">
            <div class=\"ui-draggable-handle titre_document_bandeau\" id=\"id_bandeau_$document_num\">
                    <table border=\"0\" width=\"100%\">
                            <tr>
                                    <td >
                                            <span class=\"entete_document_patient\">".afficher_patient($patient_num,'document',$document_num,'')."</span><br>
                                            <span class=\"titre_document\">$title - $document_date</span>
                                    </td>
                                    <td style=\"text-align:right;\">
                                            <img src=\"images/printer.png\" onclick=\"ouvrir_document_print('$document_num');return false;\" style=\"cursor:pointer\" border=\"0\">
                                            <img src=\"images/close.gif\" onclick=\"fermer_document('$document_num');\" style=\"cursor:pointer\" border=\"0\">
                                    </td>
                            </tr>
                    </table>
            </div>
            <div id=\"id_document_$document_num\" class=\"afficher_document\">";
	if (preg_match("/(<style[^>]*>|<br[^>]?>)/i",$displayed_text)) {
		$res.="$displayed_text";
	} else {
		$res.="<pre>$displayed_text</pre>";
	}
#	if (preg_match("/(<style[^>]*>|<i>)/i",$displayed_text)) {
#		$res.="$displayed_text";
#	} else {
#		$res.="<pre>$displayed_text</pre>";
#	}
    $res.="$display_list_file</div>
    </div>
    ";
	save_log_document($document_num,$user_num_session,$nominative);
    return $res;
}


function afficher_mvt($mvt_num) {
	global $dbh,$datamart_num,$user_num_session;
	$mvt=get_mvt ($mvt_num);
	
	$patient_num=$mvt['PATIENT_NUM'];
	$entry_date=$mvt['ENTRY_DATE'];
	$out_date=$mvt['OUT_DATE'];
	
	$department_num=$mvt['DEPARTMENT_NUM'];
	$encounter_num=$mvt['ENCOUNTER_NUM'];
	$type_mvt=$mvt['TYPE_MVT'];   
	$unit_num=$mvt['UNIT_NUM'];    
	$unit_code=$mvt['UNIT_CODE'];  
	
	
	$department_str=get_department_str ($department_num);
	$unit_str=get_unit_str ($unit_num);
	$title= "Mouvement du $entry_date au $out_date - $unit_str";
	$displayed_text="Mouvement de type $type_mvt dans l'unité $unit_code-$unit_str <br><br>";
	
	// detail du sejour : //
	if ($encounter_num!='') {
		$encounter=get_encounter_info ($encounter_num);
		$entry_date_encounter=$encounter['ENTRY_DATE'];
		$out_date_encounter=$encounter['OUT_DATE'];
		$entry_mode_encounter=$encounter['ENTRY_MODE'];
		$out_mode_encounter=$encounter['OUT_MODE'];
		$encounter_length=$encounter['ENCOUNTER_LENGTH'];
	        $displayed_text.="".get_translation('ENCOUNTER_DETAIL','Détail séjour')." N° $encounter_num - $entry_date_encounter ($entry_mode_encounter) - $out_date_encounter ($out_mode_encounter)<br><br>";
	        $displayed_text.="".get_translation('DURATION_ENCOUNTER','Durée séjour')." $encounter_length ".get_translation('DAYS','jours')."<br><br>";
		$list_mvt_encounter=get_mvt_info_by_encounter ($encounter_num,'asc');
	        foreach ($list_mvt_encounter as $mvt) {
			$entry_date=$mvt['ENTRY_DATE'];
			$out_date=$mvt['OUT_DATE'];
			$department_num=$mvt['DEPARTMENT_NUM'];
			$unit_num=$mvt['UNIT_NUM'];
			$type_mvt=$mvt['TYPE_MVT'];   
			$mvt_length=$mvt['MVT_LENGTH'];   
			$department_str=get_department_str ($department_num);
			$unit_str=get_unit_str ($unit_num);
			$displayed_text.= "Du $entry_date au $out_date ($mvt_length ".get_translation('DAYS','jours').") ($type_mvt) - $unit_str / $department_str <br>";
	        }
	}
	
	
	
	$res= "
	<div class=\"ui-widget ui-widget-content ui-corner-all ui-front ui-draggable ui-resizable class_document\" style=\"position: absolute; height: 350px; width: 650px; display: none;\" tabindex=\"-1\" id=\"id_enveloppe_document_$mvt_num\">
	    <div class=\"ui-draggable-handle titre_document_bandeau\" id=\"id_bandeau_$mvt_num\">
	            <table border=\"0\" width=\"100%\">
	                    <tr>
	                            <td >
	                                    <span class=\"entete_document_patient\">".afficher_patient($patient_num,'mvt',$mvt_num,'')."</span><br>
	                                    <span class=\"titre_document\">$title</span>
	                            </td>
	                            <td style=\"text-align:right;\">
	                                    <img src=\"images/close.gif\" onclick=\"fermer_document('$mvt_num');\" style=\"cursor:pointer\" border=\"0\">
	                            </td>
	                    </tr>
	            </table>
	    </div>
	    <div id=\"id_document_$mvt_num\" class=\"afficher_document\"><br>$displayed_text</div>
	</div>
	";
	save_log_document($mvt_num,$user_num_session,$nominative);
	return $res;
}


function afficher_document_patient_popup($document_num,$full_text_query,$tableau_liste_synonyme,$id_cle) {
        global $dbh,$datamart_num,$user_num_session;
        $document=get_document ($document_num);
	$displayed_text=$document['displayed_text'];     
        $title=$document['title'];     
        $patient_num=$document['patient_num']; 
        $document_date=$document['document_date']; 
#        $sel_texte = oci_parse($dbh,"select displayed_text,title,patient_num from dwh_document where document_num=$document_num" );   
#        oci_execute($sel_texte);
#        $row_texte = oci_fetch_array($sel_texte, OCI_ASSOC);
#        if ($row_texte['DISPLAYED_TEXT']!='') {
#                $displayed_text=$row_texte['DISPLAYED_TEXT']->load();         
#        }
#        $title=$row_texte['TITLE'];     
#        $patient_num=$row_texte['PATIENT_NUM']; 
        if ($_SESSION['dwh_droit_see_debug']=='ok') {
	       $displayed_text= afficher_dans_document_tal($document_num,$user_num_session);
	}
	$nominative='oui';
    if ($_SESSION['dwh_droit_nominative'.$datamart_num]=='' || $_SESSION['dwh_droit_anonymized'.$datamart_num]=='ok') {
            $displayed_text=anonymisation_document ($document_num,$displayed_text);
            $nominative='non';
    }
    
    $displayed_text=nettoyer_pour_afficher ($displayed_text);
    $displayed_text=surligner_resultat ($displayed_text,$full_text_query,'','oui',$tableau_liste_synonyme,'');
    if (!preg_match("/highlight/",$displayed_text) ) {
            $displayed_text=surligner_resultat ($displayed_text,$full_text_query,'maxdistance','oui',$tableau_liste_synonyme,'');
    }
    if (!preg_match("/highlight/",$displayed_text) ) {
            //$displayed_text=surligner_resultat ($displayed_text,$full_text_query,'unitaire','oui');
            $displayed_text=surligner_resultat ($displayed_text,$full_text_query,'unitaire','oui',$tableau_liste_synonyme,'');
    }
    
    
    $res= "
    <div class=\"ui-widget ui-widget-content ui-corner-all ui-front ui-draggable ui-resizable class_document\" style=\"position: absolute; height: 350px; width: 650px; display: none;\" tabindex=\"-1\" id=\"id_enveloppe_document_$id_cle\">
            <div class=\"ui-draggable-handle titre_document_bandeau\" id=\"id_bandeau_$id_cle\">
                    <table border=\"0\" width=\"100%\">
                            <tr>
                                    <td >
                                            <span class=\"entete_document_patient\">".afficher_patient($patient_num,'document',$document_num,'')."</span><br>
                                            <span class=\"titre_document\">$title</span>
                                    </td>
                                    <td style=\"text-align:right;\">
                                            <img src=\"images/close.gif\" onclick=\"fermer_document('$id_cle');\" style=\"cursor:pointer\" border=\"0\">
                                    </td>
                            </tr>
                    </table>
            </div>
            <div id=\"id_document_$id_cle\" class=\"afficher_document\">";
    if (preg_match("/(<style[^>]*>|<br[^>]?>)/i",$displayed_text)) {
    	$res.="$displayed_text";
    } else {
       	$res.="<pre>$displayed_text</pre>";
    }
    $res.="</div>
    </div>
    ";
     save_log_document($document_num,$user_num_session,$nominative);

    return $res;
}

function recupere_liste_concept_full_texte ($full_text_query,$limite_syno=50) {
	global $dbh,$user_num_session,$login_session;
	$tableau_code_libelle_deja=array();
	$tableau_etendre_requete_unitaire_deja_fait=array();
	$tableau_etendre_requete_unitaire=array();
	$tableau_requete_unitaire=array_values(array_unique(preg_split("/ or | and | not |;requete_unitaire;/i"," $full_text_query ")));
        foreach ($tableau_requete_unitaire as $requete_unitaire) {
                $requete_unitaire=strtolower(trim($requete_unitaire));
		 if ($requete_unitaire!='' && strlen($requete_unitaire)>1 && $tableau_etendre_requete_unitaire_deja_fait[trim($requete_unitaire)]=='') {
		 	$tableau_code_libelle_deja[$requete_unitaire]='ok';
		 	$requete_unitaire=preg_replace("/'/"," ",$requete_unitaire);
		 	$requete_unitaire=preg_replace("/[(),]/"," ",$requete_unitaire);
		 	$requete_unitaire=preg_replace("/[^a-z]near[^a-z]/"," "," $requete_unitaire ");
			$i=0;
			$liste_code_libelle='';
		        $sel = oci_parse($dbh,"select  concept_str,length(concept_str),count_doc_concept_str from dwh_thesaurus_enrsem where length(concept_str)>2 and length(concept_str)<50 and concept_code in (select  concept_code from dwh_thesaurus_enrsem where contains(concept_str,'$requete_unitaire')>0 ) order by count_doc_concept_str desc, length(concept_str) desc " );   
		        oci_execute($sel);
		        while ($row = oci_fetch_array($sel, OCI_ASSOC)) {
			        $concept_str=strtolower($row['CONCEPT_STR']);
			        if ($tableau_code_libelle_deja[$concept_str]=='' && $i<=$limite_syno) {
			        	$i++;
					$liste_code_libelle.=";requete_unitaire;".$concept_str;
			 		$tableau_code_libelle_deja[$concept_str]='ok';
			 	}
		        }
			$tableau_etendre_requete_unitaire[trim($requete_unitaire)]['synonyme']=$liste_code_libelle;
		        
			$i=0;
			$liste_code_libelle='';
		        $sel = oci_parse($dbh,"
		        select  fils.concept_str,length(fils.concept_str),count_doc_concept_str from dwh_thesaurus_enrsem fils,dwh_thesaurus_enrsem_graph graph where
		        graph.concept_code_son=fils.concept_code and
		         graph.concept_code_father in (select  concept_code from dwh_thesaurus_enrsem where contains(concept_str,'$requete_unitaire')>0 )
		      order by count_doc_concept_str desc, length(fils.concept_str) desc  " ); 
		        oci_execute($sel);
		        while ($row = oci_fetch_array($sel, OCI_ASSOC)) {
			        $concept_str=strtolower($row['CONCEPT_STR']);
			        if ($tableau_code_libelle_deja[$concept_str]=='' && $i<=$limite_syno) {
		        		$i++;
					$liste_code_libelle.=";requete_unitaire;".$concept_str;
		 			$tableau_code_libelle_deja[$concept_str]='ok';
				}
		        }
		        $tableau_etendre_requete_unitaire[trim($requete_unitaire)]['fils']=$liste_code_libelle;
		        
		        $tableau_etendre_requete_unitaire_deja_fait[trim($requete_unitaire)]='ok';
	        }
	}
       return $tableau_etendre_requete_unitaire;
}


function surligner_resultat_exp_reguliere ($text,$pattern,$colorer) {
        global $dbh,$tableau_couleur;
        
	$pattern_clean=trim(clean_for_regexp ($pattern));
        if (preg_match("/<div/",$text)) {
		$nb_parenthese=3+count(explode("(",$pattern_clean))-1;// if parenthesis in the regexp
		if ($colorer=='oui') {
			$couleur=$tableau_couleur[0];
			$text=preg_replace("/(>[^<]*?)($pattern_clean)([^>]*?<)/i","$1<b style='background:$couleur;color:black;' class='highlight'><u>$2</u></b>$".$nb_parenthese," $text ");
		} else {
			$text=preg_replace("/($pattern_clean)/i","<b style='color:black;' class='highlight'><u>$1</u></b>",$text);
		}
        } else {
		if ($colorer=='oui') {
			$couleur=$tableau_couleur[0];
			$text=preg_replace("/($pattern_clean)/i","<b style='background:$couleur;color:black;' class='highlight'><u>$1</u></b>",$text);
		} else {
			$text=preg_replace("/($pattern_clean)/i","<b style='color:black;' class='highlight'><u>$1</u></b>",$text);
		}
	}
	return $text;

}


function surligner_resultat ($text,$full_text_query,$option,$colorer,$tableau_liste_synonyme,$test_unique) {
        global $dbh,$tableau_couleur,$login_session;
        global $stop_words;
        $num_couleur=-1;
        
        $tableau_liste_texte_query=array();
        
        $expression_deja_trouve='';
        $expression_bloc_deja_trouve='';
        $text=str_replace("\n","\n ",$text);
        $text_final=" $text ";
        $tableau_bloc_requete=array_values(array_unique(explode(";requete_unitaire;",$full_text_query)));
        foreach ($tableau_bloc_requete as $full_text_query_bloc) {
	        $tableau_requete_unitaire=array_values(array_unique(preg_split("/(near|[\(\),]| and | or | not )/i"," $full_text_query_bloc ")));
	        $full_text_query_join_pointvirgule=preg_replace("/(near|[\(\),]| and | or | not )/i","; $1",trim($full_text_query_bloc));
	        
	        // s'il n'y a pas de 'and' on ne fait les synonymes que s'il n'a rien trouve dans la sous requete unitaire //
		$test_presence_and='';
		if (preg_match("/ and /i",$full_text_query_bloc)) {
			$test_presence_and='ok';
		}
		$tableau_liste_texte_query=array();
	        foreach ($tableau_requete_unitaire as $requete_unitaire) {
	                $requete_unitaire=strtolower(trim($requete_unitaire));
	                if ($requete_unitaire!='' && strlen($requete_unitaire)>1) {
	                	
		                $requete_unitaire_normalise=nettoyer_pour_surligner($requete_unitaire);
	                        if ($requete_tableau_couleur[$requete_unitaire]=='') {
	                                $num_couleur++;
	                                if ($num_couleur==count($tableau_couleur)) {
	                                	$num_couleur=0;
	                                }
	                                if ($colorer=='oui') {
	                                        $requete_tableau_couleur[$requete_unitaire]=$tableau_couleur[$num_couleur];
	                                } else {
	                                        $requete_tableau_couleur[$requete_unitaire]='transparent';
	                                }
	                                $tableau_liste_texte_query[$requete_unitaire]=$requete_unitaire_normalise;
	                                
	                                if ($option=='maxdistance') {
						$requete_unitaire_normalise_max_distance=nettoyer_pour_surligner_maxdistance($requete_unitaire);
						$tableau_liste_texte_query[$requete_unitaire_normalise_max_distance]=$requete_unitaire_normalise_max_distance;
	                                
		                                $num_couleur++;
		                                if ($num_couleur==count($tableau_couleur)) {
		                                	$num_couleur=0;
		                                }
		                                if ($colorer=='oui') {
		                                        $requete_tableau_couleur[$requete_unitaire_normalise_max_distance]=$tableau_couleur[$num_couleur];
		                                } else {
		                                        $requete_tableau_couleur[$requete_unitaire_normalise_max_distance]='transparent';
		                                }
	                                }
	                                if ($option=='unitaire') {
	                                        $tableau_sous_requete_unitaire=array_values(array_unique(explode(" ",$requete_unitaire)));
	                                        foreach ($tableau_sous_requete_unitaire as $sous_requete_unitaire) {
	                                                $sous_requete_unitaire=trim($sous_requete_unitaire);
	                                                if ($sous_requete_unitaire!='' && strlen($sous_requete_unitaire)>1 && !in_array("$sous_requete_unitaire", $stop_words)) {
	                                                        $sous_requete_unitaire_normalise=nettoyer_pour_surligner($sous_requete_unitaire);
	                                                        if ($requete_tableau_couleur[$sous_requete_unitaire]=='') {
	                                                                if ($colorer=='oui') {
	                                                                        $requete_tableau_couleur[$sous_requete_unitaire]=$tableau_couleur[$num_couleur];
	                                                                } else {
	                                                                        $requete_tableau_couleur[$sous_requete_unitaire]='transparent';
	                                                                }
	                                                                $tableau_liste_texte_query[$sous_requete_unitaire]=$sous_requete_unitaire_normalise;
	                                                        }
	                                                }
	                                        }
	                                }
	                        }
	                }
	        }

	        uksort($tableau_liste_texte_query, 'compareStrlenInverse');        // on trie par longueur de chaine decroissante //
	        foreach ($tableau_liste_texte_query as $sous_requete_unitaire => $sous_requete_unitaire_normalise) {
	                $deja_fait=0;
			if (!preg_match("/[^a-z]not\s+$sous_requete_unitaire *;/i","; $full_text_query_join_pointvirgule ;")) {
				
		                if (preg_match("/[^a-z0-9]".$sous_requete_unitaire."[^a-z0-9]/i"," $expression_deja_trouve ")) {
		                        $deja_fait=1;
		                }
		                if ($test_unique=='' && $deja_fait==0 || $test_unique==1 && $expression_deja_trouve=='') {
		                        $nb_parenthese=3+count(explode("(",$sous_requete_unitaire_normalise))-1;// if parenthesis in the regexp, we need the number to add them for the replace 
		                        if (preg_match("/%/",$sous_requete_unitaire_normalise)) {
		                                $sous_requete_unitaire_normalise=preg_replace("/%/","",$sous_requete_unitaire_normalise);
		                                $couleur=$requete_tableau_couleur[$sous_requete_unitaire];
		                                $pattern="#([^a-z0-9])(".$sous_requete_unitaire_normalise."[a-z0-9]*)([^a-z0-9])#i";
		                                $text=preg_replace($pattern,"$1<b style='background:$couleur;color:black;' class='highlight'><u>$2</u></b>$".$nb_parenthese,$text,-1,$count);
		                                $text_final=preg_replace($pattern,"$1<b style='background:$couleur;color:black;' class='highlight'><u>$2</u></b>$".$nb_parenthese,$text_final);
		                        } else {
		                                $couleur=$requete_tableau_couleur[$sous_requete_unitaire];
		                                $pattern="#([^a-z0-9])(".$sous_requete_unitaire_normalise.")(s?[^a-z0-9])#i";
		                                $text=preg_replace($pattern,"$1<b style='background:$couleur;color:black;' class='highlight'><u>$2</u></b>$".$nb_parenthese,$text,-1,$count);
		                                $text_final=preg_replace($pattern,"$1<b style='background:$couleur;color:black;' class='highlight'><u>$2</u></b>$".$nb_parenthese,$text_final);
		                        }
		                        if ($count>0) {
		                                $expression_deja_trouve.= " $sous_requete_unitaire ";
		                                $expression_bloc_deja_trouve='ok';
		                        } else {
	                			if ($option!='synonyme' && $option!='fils' && ($expression_bloc_deja_trouve=='' ||  $test_presence_and=='ok' )) {
	                			//if ($expression_bloc_deja_trouve=='' && $test_presence_and=='ok' || $expression_bloc_deja_trouve!='' && $test_presence_and=='ok' || $expression_bloc_deja_trouve=='' && $test_presence_and=='') {
							$nb_highligth_before_synonyme=preg_match_all("/highlight/",$text_final, $matches);
							if ($tableau_liste_synonyme[$sous_requete_unitaire]['synonyme']!='') {
								$text_final=surligner_resultat ($text_final,$tableau_liste_synonyme[$sous_requete_unitaire]['synonyme'],"synonyme",$colorer,array(),1);
							}
							$nb_highligth_after_synonyme=preg_match_all("/highlight/",$text_final, $matches);
							if ($nb_highligth_before_synonyme==$nb_highligth_after_synonyme && $tableau_liste_synonyme[$sous_requete_unitaire]['fils']!='') {
								$text_final=surligner_resultat ($text_final,$tableau_liste_synonyme[$sous_requete_unitaire]['fils'],"fils",$colorer,array(),1);
							}
						}

		                        }
		                }
		        }
	        }
	}
        return $text_final;
}





function nettoyer_pour_requete ($text) {
        $text=str_replace(";plus;","+",$text);
        //$text=preg_replace("/;antislash;/","\\",$text);
        
	// si ce n'est pas une epxression reguliere on supprimer les trucs qui ne marche pas avec oracle text, sinon on les gardes
	$test_expression_reguliere='';
	if (preg_match("/[[|?+{]/",$text)) {
		$test_expression_reguliere='ok';
	}
	
	if ($test_expression_reguliere=='') {
       		 $text=preg_replace("/\*/","%",$text);
	        $text=preg_replace("/\-/"," ",$text);
	        $text=preg_replace("/[.:;]/"," ",$text);
      		$text=preg_replace("/'/"," ",$text);
	        $text=preg_replace("/ et /i"," and ",$text);
	        $text=preg_replace("/ ou /i"," or ",$text);
        }
        
        $text=preg_replace("/\"/","",$text);
        
        $text=preg_replace("/\n/"," ",$text);
        $text=preg_replace("/\r/"," ",$text);
        
        $text=preg_replace("/[âà]/","a",$text);
        $text=preg_replace("/[éèêë]/","e",$text);
        $text=preg_replace("/[ïî]/","i",$text);
        $text=preg_replace("/[ôö]/","o",$text);
        $text=preg_replace("/[ûü]/","u",$text);
        $text=preg_replace("/[ç]/","c",$text);
        
        return $text;
}




function nettoyer_pour_requete_automatique ($text) {
	// si ce n'est pas une epxression reguliere on supprimer les trucs qui ne marche pas avec oracle text, sinon on les gardes
        
	$text=preg_replace("/ not /"," ",$text);
	$text=preg_replace("/ and /"," ",$text);
	$text=preg_replace("/ or /"," ",$text);
        $text=preg_replace("/[âà]/","a",$text);
        $text=preg_replace("/[éèêë]/","e",$text);
        $text=preg_replace("/[ïî]/","i",$text);
        $text=preg_replace("/[ôö]/","o",$text);
        $text=preg_replace("/[ûü]/","u",$text);
        $text=preg_replace("/[ç]/","c",$text);
        
        $text=preg_replace("/\n/"," ",$text);
        $text=preg_replace("/\r/"," ",$text);
        $text=preg_replace("/[^a-z0-9]/i"," ",$text);
        
	$text=preg_replace("/\s+/"," ",$text);
	$text=trim($text);
	$text=preg_replace("/\s/"," and ",$text);
        return $text;
}

function clean_for_regexp  ($text) {
        $text=str_replace(";plus;","+",$text);
        
        //$text=preg_replace("/;plus;/","+",$text);
        //$text=preg_replace("/;antislash;/","\\",$text);
        
       // $text=preg_replace("/\"/"," ",$text); // on supprime, cette fonction n'est pas utilisé pour les regexp en sql
      //  $text=preg_replace("/'/","''",$text); // on supprime, cette fonction n'est pas utilisé pour les regexp en sql
      	$text=str_replace("/","\\/",$text);
        return $text;
}

function clean_for_regular_expression ($text) {
	// si ce n'est pas une epxression reguliere on supprimer les trucs qui ne marche pas avec oracle text, sinon on les gardes
        
        $text=preg_replace("/[âà]/","a",$text);
        $text=preg_replace("/[éèêë]/","e",$text);
        $text=preg_replace("/[ïî]/","i",$text);
        $text=preg_replace("/[ôö]/","o",$text);
        $text=preg_replace("/[ûü]/","u",$text);
        $text=preg_replace("/[ç]/","c",$text);
        
        $text=preg_replace("/[^a-z0-9]/i"," ",$text);
        $text=preg_replace("/\n/"," ",$text);
        $text=preg_replace("/\r/"," ",$text);
        
        
        return $text;
}




function nettoyer_pour_requete_patient ($text) {
	// si ce n'est pas une epxression reguliere on supprimer les trucs qui ne marche pas avec oracle text, sinon on les gardes
        $text=str_replace(";plus;","+",$text);
        //$text=preg_replace("/;plus;/","+",$text);
        //$text=preg_replace("/;antislash;/","\\",$text);
        
	$test_expression_reguliere='';
	if (preg_match("/[[|?+{(]/",$text)) {
		$test_expression_reguliere='ok';
	}
        
	if ($test_expression_reguliere=='ok') {
      		$text=preg_replace("/'/","''",$text);
      		$text=str_replace("/","\\/",$text);
        } else {
       		$text=preg_replace("/\*/","%",$text);
	        $text=preg_replace("/\-/"," ",$text);
	        $text=preg_replace("/[.:;]/"," ",$text);
      		$text=preg_replace("/'/"," ",$text);
	        $text=preg_replace("/ et /i"," and ",$text);
	        $text=preg_replace("/ ou /i"," or ",$text);
        }
        $text=preg_replace("/\"/","",$text);
        
        $text=preg_replace("/\n/"," ",$text);
        $text=preg_replace("/\r/"," ",$text);
        
        $text=preg_replace("/[âà]/","a",$text);
        $text=preg_replace("/[éèêë]/","e",$text);
        $text=preg_replace("/[ïî]/","i",$text);
        $text=preg_replace("/[ôö]/","o",$text);
        $text=preg_replace("/[ûü]/","u",$text);
        $text=preg_replace("/[ç]/","c",$text);
        
        return $text;
}


function nettoyer_pour_insert ($text) {
        $text=preg_replace("/'/","''",$text);
        $text=preg_replace("/\"/","",$text);
        return $text;
}
function nettoyer_pour_surligner ($full_text_query) {
        global $stop_words;
        
        //ATTENTION ORDRE TRES IMPORTANT ... SINON, CA CASSE TOUT 
        
        // on supprime tous les signes ou termes qui ne servent à rien
        $full_text_query=trim($full_text_query);
        $full_text_query=preg_replace("/  /"," ","$full_text_query");
        $full_text_query=preg_replace("/  /"," ","$full_text_query");
        $full_text_query=str_replace("near(","","$full_text_query");
        $full_text_query=str_replace("fuzzy(","","$full_text_query");
        $full_text_query=preg_replace("/,[0-9]+\)/","","$full_text_query");
        $full_text_query=preg_replace("/,[0-9]+,[a-z]+\)/","","$full_text_query");
        $full_text_query=preg_replace("/~[0-9]/","",$full_text_query);
        $full_text_query=preg_replace("/\(/","",$full_text_query);
        $full_text_query=preg_replace("/\)/","",$full_text_query);

        // add 2018 05 25 NG //
        $full_text_query=preg_replace("/[eèêé]/","[eéèê]",$full_text_query);
        $full_text_query=preg_replace("/[ôöo]/","[ôöo]",$full_text_query);
        $full_text_query=preg_replace("/[âàa]/","[âàa]",$full_text_query);
        $full_text_query=preg_replace("/[ûüu]/","[ûüu]",$full_text_query);
        $full_text_query=preg_replace("/[çc]/","[çc]",$full_text_query);
        /////////////////
        
        
        $full_text_query=preg_replace("/[^a-z0-9éeêèîïàââçùûüôö%;\[\]-]/i","[^a-z0-9]+",$full_text_query);

        //ad NG 2019 02 08
        $full_text_query=preg_replace("/%/","[a-z0-9éeêèîïàââçùûüôö]*",$full_text_query);

        foreach ($stop_words as $word) {
              $full_text_query=preg_replace("/([^a-z0-9])$word([^a-z0-9])/i","$1[A-Za-z]+$2"," $full_text_query ");
        }
        $full_text_query=trim($full_text_query);
        
#        $full_text_query=preg_replace("/[éêè]/i","([éeêè]|&eacute;|&egrave;|&ecirc;|&euml;|&Egrave;|&Eacute;|&Ecirc;|&Euml;)",$full_text_query);
#        $full_text_query=preg_replace("/[îï]/i","([i]|&iuml;|&icirc;|&Iuml;|&Icirc;)",$full_text_query);
#        $full_text_query=preg_replace("/[àââ]([^-][^z])/i","(a|à|â|&aacute;|&agrave;|&auml;|&acirc;|&Agrave;|&Aacute;|&Auml;|&Acirc;)$1",$full_text_query);
#        $full_text_query=preg_replace("/[ç]/i","(c|&ccedil;)",$full_text_query);
#        $full_text_query=preg_replace("/[ùûü]/i","(u|&uuml;|&uacute;|ugrave;)",$full_text_query);
#        $full_text_query=preg_replace("/[ôö]/i","(o|ô|ö|&ouml;|&ocirc;|&Ouml;|&Ocirc;)","$full_text_query");
        
        $full_text_query=preg_replace("/e([^a-z0-9éèê])/i","es?$1","$full_text_query");
        $full_text_query=preg_replace("/e$/i","es?","$full_text_query");
	
        return $full_text_query;
}


function nettoyer_pour_surligner_maxdistance ($full_text_query) {
        global $stop_words;
        
        
        // on supprime tous les signes ou termes qui ne servent à rien
        $full_text_query=trim($full_text_query);
        $full_text_query=preg_replace("/  /"," ","$full_text_query");
        $full_text_query=preg_replace("/  /"," ","$full_text_query");
        $full_text_query=str_replace("near(","","$full_text_query");
        $full_text_query=str_replace("fuzzy(","","$full_text_query");
        $full_text_query=preg_replace("/,[0-9]+\)/","","$full_text_query");
        $full_text_query=preg_replace("/,[0-9]+,[a-z]+\)/","","$full_text_query");
        $full_text_query=preg_replace("/~[0-9]/","",$full_text_query);
        $full_text_query=preg_replace("/\(/","",$full_text_query);
        $full_text_query=preg_replace("/\)/","",$full_text_query);
        

        // add 2018 05 25 NG //
        $full_text_query=preg_replace("/[eèêé]/","[eéèê]",$full_text_query);
        $full_text_query=preg_replace("/[ôöo]/","[ôöo]",$full_text_query);
        $full_text_query=preg_replace("/[âàa]/","[âàa]",$full_text_query);
        $full_text_query=preg_replace("/[ûüu]/","[ûüu]",$full_text_query);
        $full_text_query=preg_replace("/[çc]/","[çc]",$full_text_query);
        /////////////////
        
        
        $full_text_query=preg_replace("/[^a-z0-9éeêèîïàââçùûüôö%;\[\]-\s]/i","[^a-z0-9]+",$full_text_query);
        
        // add 2019 06 13 NG //
        $full_text_query=preg_replace("/\s/i",".{0,5}",$full_text_query);

        //ad NG 2019 02 08
        $full_text_query=preg_replace("/%/","[a-z0-9éeêèîïàââçùûüôö]*",$full_text_query);

        foreach ($stop_words as $word) {
              $full_text_query=preg_replace("/([^a-z0-9])$word([^a-z0-9])/i","$1[A-Za-z]*$2"," $full_text_query ");
        }
        $full_text_query=trim($full_text_query);
        
        $full_text_query=preg_replace("/e([^a-z0-9éèê])/i","es?$1","$full_text_query");
        $full_text_query=preg_replace("/e$/i","es?","$full_text_query");
	
        return $full_text_query;
}

function nettoyer_pour_afficher ($text) {
        $text=preg_replace("/<title>[^>]+<\/title>/","","$text");
        return $text;
        
}

function compareStrlen($x, $y){
     if(strlen($x) < strlen($y)) return 1;
     return -1;
} 

function compareStrlenInverse($x, $y){
     if(strlen($x) < strlen($y)) return 1;
     return -1;
} 

function nettoyer_pour_inserer ($text) {
        $text=preg_replace("/'/"," ","$text");
        $text=preg_replace("/\"/"," ","$text");
        return $text;
}

function verif_connexion($login,$passwd,$option) {
    global $dbh,$dbannuaire;
    if ($login!='' && $passwd!='') {
	$check_user_attempt=check_user_attempt ($login);
	if ($check_user_attempt=='ok') {
            $result=valid_login_ldap($login,$passwd);
            if ($result=='nothing') {
                $sel=oci_parse($dbh,"select count(*) as verif_passwd from dwh_user where login='$login' and passwd='".md5($passwd)."' ");
                oci_execute($sel);
				$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
                $verif_passwd=$r['VERIF_PASSWD'];
                if ($verif_passwd==0) {
                	$result='nothing';
                } else {
                	$result='ok';
                }
            }
            if ($result!='nothing') {
                $sel=oci_parse($dbh,"select user_profile,user_num from dwh_user_profile where user_num in (select user_num from dwh_user where login='$login')");
                oci_execute($sel);
                while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
                        $user_profile=$r['USER_PROFILE'];
                        $user_num=$r['USER_NUM'];
                        $_SESSION['dwh_profil_'.$user_profile]='ok';
                        $_SESSION['dwh_login']=$login;
                        $_SESSION['dwh_user_num']=$user_num;
                }
                if ($_SESSION['dwh_login']!='') {
                        $erreur='ok';
                } else {
                        $erreur='right';
                }
            } else {
				$erreur='passwd';
            }
            
            if ($option=='page_connexion') {
                if ($erreur=='ok') {
                        $erreur='ok';
                        update_user_attempt($login,$user_num,'reinit');
                } else if ($erreur=='right') {
                        $erreur="<strong style=\"color:#990000;\">".get_translation('YOU_CANNOT_ACCESS_DATAWAREHOUSE',"Vous n'avez pas les autorisations pour accéder à l'entrepôt de données")." !</strong>";
                } else if ($erreur=='passwd') {
                        $erreur="<strong style=\"color:#990000;\">".get_translation('LOGIN_OR_PASSWORD_INCORRECT','Identifiant ou mot de passe incorrect')."</strong>";
                        update_user_attempt($login,'','increment');
    					$time_left=get_timelimit_connexion ($login);
						if ($time_left<-1) {
							$erreur.= "<br><font style='color:#990000'>You have to wait ".abs($time_left)." seconds before retrying</font>";
						}
                }
            }
        } else {
			$time_left=get_timelimit_connexion ($login);
			if ($time_left<-1) {
				$erreur= "<br><font style='color:#990000'>You have to wait ".abs($time_left)." seconds before retrying</font>";
			}
        }
    } else {
            $erreur='passwd';
    }
    return $erreur;
        
}
function update_user_attempt ($login,$user_num,$action) {
    global $dbh;
    if ($user_num=='' && $login!='') {
        $sel=oci_parse($dbh,"select user_num from dwh_user where login='$login' ");
        oci_execute($sel);
        $r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
        $user_num=$r['USER_NUM'];
    }

	if ($user_num!='') {
		if ($action=='reinit') {
            $upd=oci_parse($dbh,"update dwh_user set  count_attempt=0, attempt_date=null where user_num='$user_num' ");
            oci_execute($upd);
		}
		if ($action=='increment') {
            $upd=oci_parse($dbh,"update dwh_user set  count_attempt=count_attempt+1, attempt_date=sysdate where user_num='$user_num' ");
            oci_execute($upd);
		}
	}
}

function get_timelimit_connexion ($login) {
    global $dbh;
    $timeleft='';
    if ($login!='') {
	    $sel=oci_parse($dbh,"select round(86400*(sysdate-(attempt_date+POWER(5,count_attempt)/(86400*1000)))) as timelimit from dwh_user  where login='$login' ");
	    oci_execute($sel);
	    $r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	    $timeleft=$r['TIMELIMIT'];
	}
   	return $timeleft;
}

function check_user_attempt ($login) {
    global $dbh;
    if ($login!='') {
        $sel=oci_parse($dbh,"select count_attempt from dwh_user where login='$login' ");
        oci_execute($sel);
        $r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
        $count_attempt=$r['COUNT_ATTEMPT'];
    	if ($count_attempt>0) {

	        $sel=oci_parse($dbh,"select round(86400*1000*(sysdate-(attempt_date+POWER(5,count_attempt)/(86400*1000)))) TIMELIMIT from dwh_user where  login='$login' ");
	        oci_execute($sel);
	        $r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	        $timelimit=$r['TIMELIMIT'];
	        if ($timelimit>=0) {
    			return 'ok';
	        } else {
	        	return 'error';
	        }
    	} else {
    		return 'ok';
    	}
    }
}

function check_user_as_department_manager ($department_num,$user_num) {
        global $dbh;
        $sel_var=oci_parse($dbh,"select manager_department from dwh_user_department where department_num=$department_num and user_num='$user_num'");
        oci_execute($sel_var);
        $zselcommentaire=oci_fetch_array($sel_var);
        $verif_manager_department=$zselcommentaire[0];
        return $verif_manager_department;
}



function affiche_mes_services() {
        global $dbh,$login_session,$user_num_session;
        
        if ($user_num_session!='') {
	        $sel_service=oci_parse($dbh,"select department_num,manager_department from dwh_user_department where user_num=$user_num_session");
	        oci_execute($sel_service);
	        while ($r=oci_fetch_array($sel_service)) {
		        $department_num=$r[0];
		        $verif_manager_department=$r[1];
		        
		        $department_str=get_department_str ($department_num);
		        
		        //////////////// LE SERVICE
		        print "<div id=\"id_div_service_$department_num\" >";
		        print "<strong>$department_str</strong><br>";
		        
		        /////////////////// LES UF 
		        print "<a href=\"#\" onclick=\"plier_deplier('id_div_uf_$department_num');return false;\" class=\"admin_lien\"><span id=\"plus_id_div_uf_$department_num\">+</span> ".get_translation('DISPLAY_UNITS','Afficher les unités')."</a>
		        <div id=\"id_div_uf_$department_num\" style=\"display:none\">";
		        print "<table border=\"0\" id=\"id_tableau_uf_$department_num\" width=\"100%\" class=\"noborder\">";
#		        $req_uf="select dwh_thesaurus_unit.unit_num, unit_str ,unit_code,to_char(unit_start_date,'DD/MM/YYYY') as unit_start_date,to_char(unit_end_date,'DD/MM/YYYY') as unit_end_date
#		        from dwh_thesaurus_unit,dwh_thesaurus_department 
#		        where dwh_thesaurus_unit.department_num=dwh_thesaurus_department.department_num and dwh_thesaurus_department.department_num=$department_num  and department_master=1 order by unit_str ";
		        $req_uf="select dwh_thesaurus_unit.unit_num, unit_str ,unit_code,to_char(unit_start_date,'DD/MM/YYYY') as unit_start_date,to_char(unit_end_date,'DD/MM/YYYY') as unit_end_date
		        from dwh_thesaurus_unit where department_num=$department_num  order by unit_str ";
		        $sel_uf = oci_parse($dbh,$req_uf);
		        oci_execute($sel_uf);
		        while ($ligne_uf = oci_fetch_array($sel_uf)) {
		                $unit_num = $ligne_uf['UNIT_NUM'];
		                $unit_str = $ligne_uf['UNIT_STR'];
		                $unit_code = $ligne_uf['UNIT_CODE'];
		                $unit_start_date = $ligne_uf['UNIT_START_DATE'];
		                $unit_end_date = $ligne_uf['UNIT_END_DATE'];
		                print "<tr id=\"id_tr_uf_".$department_num."_".$unit_num."\" onmouseover=\"this.style.backgroundColor='#B9C2C8';\" onmouseout=\"this.style.backgroundColor='#ffffff';\" class=\"admin_texte\">
		                        <td>$unit_code ".ucfirst(strtolower($unit_str))." </td><td> $unit_start_date</td><td>$unit_end_date</td>";
		                
		                if ($_SESSION['dwh_droit_admin']=='ok' ) {
		                        print "<td><a onclick=\"supprimer_uf('$unit_num','$department_num');return false;\" href=\"#\" class=\"admin_lien\">X</a></td>";
		                } else {
		                        print "<td></td>";
		                }
		                print "</tr>";
		        }
		        print "</table></div><br>";
		        
		        /////////////////// LES PERSONNES
		        print "<a href=\"#\" onclick=\"plier_deplier('id_div_service_user_$department_num');return false;\" class=\"admin_lien\"><span id=\"plus_id_div_service_user_$department_num\">+</span> ".get_translation('DISPLAY_USERS','Afficher les personnes')."</a>
		        <div id=\"id_div_service_user_$department_num\" style=\"display:none\">
		        ";
		        print "<table border=\"0\" id=\"id_tableau_user_$department_num\" width=\"100%\" class=\"noborder\">";
		        $req_user="select lastname,firstname,manager_department,dwh_user.user_num
		        from dwh_user,dwh_user_department 
		        where dwh_user_department.user_num=dwh_user.user_num and dwh_user_department.department_num=$department_num order by manager_department desc, lastname ";
		        $sel_user = oci_parse($dbh,$req_user);
		        oci_execute($sel_user);
		        while ($ligne_user = oci_fetch_array($sel_user)) {
		                $lastname = $ligne_user['LASTNAME'];
		                $manager_department = $ligne_user['MANAGER_DEPARTMENT'];
		                $firstname = $ligne_user['FIRSTNAME'];
		                $user_num = $ligne_user['USER_NUM'];
		                
		                if ($manager_department==1) {
		                        $texte_manager_department=" *";
		                } else {
		                        $texte_manager_department="";
		                }
		                print "<tr id=\"id_tr_user_".$department_num."_".$user_num."\" onmouseover=\"this.style.backgroundColor='#B9C2C8';\" onmouseout=\"this.style.backgroundColor='#ffffff';\" class=\"admin_texte\">
		                        <td>".strtoupper($lastname)." ".ucfirst(strtolower($firstname))." 
		                 <span  id=\"id_span_user_".$department_num."_".$user_num."\"  style=\"color:#990000;font-size:18px;font-weight:bold;\">$texte_manager_department</span> </td>";
		                
		                if ($_SESSION['dwh_droit_admin']=='ok' || $verif_manager_department==1) {
		                        print "<td><a onclick=\"supprimer_user('$user_num','$department_num');return false;\" href=\"#\" class=\"admin_lien\">X</a></td>";
		                } else {
		                        print "<td></td>";
		                }
		                print "</tr>";
		        }
		        print "</table></div><br>";
		        
		        if ($_SESSION['dwh_droit_admin']=='ok' || $verif_manager_department==1) {
		                print "<a onclick=\"plier_deplier('id_div_ajouter_user_$department_num');return false;\"  href=\"#\" class=\"admin_lien\"><span id=\"plus_id_div_ajouter_user_$department_num\">+</span> ".get_translation('ADD_USERS','Ajouter des utilisateurs')."</a><br><br>
		                        <div id=\"id_div_ajouter_user_$department_num\" style=\"display:none;\" onmouseover=\"autocomplete_service($department_num);\">
		                                <strong class=\"admin_strong\">".get_translation('SEARCH_USER_IN_LDAP','Rechercher un code APH (nom ou prenom.nom)')." :</strong> <div class=\"ui-widget\" style=\"padding-left: 10px;width:260px;font-size:10px;\">
		                                        <span class=\"ui-helper-hidden-accessible\" aria-live=\"polite\" role=\"status\"></span>
		                                        <span class=\"ui-helper-hidden-accessible\" role=\"status\" aria-live=\"polite\"></span>
		                                        <input id=\"id_champs_recherche_rapide_utilisateur_$department_num\" class=\"form ui-autocomplete-input\" type=\"text\" autocomplete=\"off\" style=\"font-size:12px;\" value=\"".get_translation('LOGIN','Identifiant')."\" name=\"LOGIN\" size=\"15\" onclick=\"if (this.value=='".get_translation('LOGIN','Identifiant')."') {this.value='';}\">
		                                        <span id=\"id_chargement_$department_num\" style=\"display:none;\"><img src=\"images/chargement_mac.gif\" width=\"15px\"></span>
		                                </div>
		                                <br>
		                                <strong class=\"admin_strong\">".get_translation('LOGIN_LIST',"Liste d'identifiants")." </strong>
		                                <br><i class=\"admin_i\">".get_translation('ADD_ASTERISK_BEFORE_OR_AFTER_CODE','Ajoutez une * avant ou après le code APH pour lui donner le rôle de responsable')."</i> : <br>";
		                print "<table border=0 class=\"noborder\">
		                                <tr><td>
		                                        <textarea cols=30 rows=4 id=\"id_textarea_ajouter_user_$department_num\"  class=\"form ui-autocomplete-input\" onclick=\"document.getElementById('id_div_reponse_service_$department_num').innerHTML='';\"></textarea>
		                                </td>
		                                <td valign=\"top\">
		                                        <div id=\"id_div_reponse_service_$department_num\" style=\"color:green;font-weight:bold;\"></div>
		                                </td></tr>
		                                </table>
		                                <br>
		                                <i>".get_translation('ALL_WILL_HAVE_MD_PROFILE',"Ils auront tous le profil medecin")."</i><br>
		                                <input type=\"button\" onclick=\"ajouter_user('$department_num');\" value=\"".get_translation('ADD','ajouter')."\">
		                                <br>";
		                print "
		                        </div>";
		        }
		        print "</div>";
		}
	}
}


function ajouter_user ($login,$lastname,$firstname,$mail,$expiration_date,$liste_profils,$liste_services,$log) {
        global $dbh;
        $sel=oci_parse($dbh,"select user_num from dwh_user where lower(login)=lower('$login')   ");
        oci_execute($sel);
        $r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
        $user_num=$r['USER_NUM'];
        if ($user_num!='') {
                if ($log=='ok') {
                        print "<strong style=\"color:red\">".get_translation('USER_ALREADY_REGISTERD','utilisateur déjà enregistré')."</strong>";
                }
                $res=$user_num;
        } else {
                if ($lastname!='') {
                        $user_num=get_uniqid();
                        
                        $req="insert into dwh_user  (user_num , lastname ,firstname ,mail ,login,passwd,creation_date,expiration_date) values ($user_num,'$lastname','$firstname','$mail','$login','',sysdate,to_date('$expiration_date','DD/MM/YYYY'))";
                        $ins=oci_parse($dbh,$req);
                        oci_execute($ins) || die ("<strong style=\"color:red\">".get_translation('ERROR','erreur')." : ".get_translation('PATIENT_NOT_SAVED','patient non sauvé')."</strong>");
                        
                        $tableau_profils=explode(',',$liste_profils);
                        foreach ($tableau_profils as $user_profile) {
                                if ($user_profile!='') {
                                        $req="insert into dwh_user_profile  (user_num ,user_profile) values ($user_num,'$user_profile')";
                                        $ins=oci_parse($dbh,$req);
                                        oci_execute($ins) ||die ("<strong style=\"color:red\">".get_translation('ERROR','erreur')." : ".get_translation('UNSAVED_PROFILES','profils non sauvés')."</strong>");
                                }
                        }
                        
                        $tableau_services=explode(',',$liste_services);
                        foreach ($tableau_services as $department_num) {
                                if ($department_num!='') {
                                        $req="insert into dwh_user_department  (user_num ,department_num) values ($user_num,'$department_num')";
                                        $ins=oci_parse($dbh,$req);
                                        oci_execute($ins) ||die ("<strong style=\"color:red\">".get_translation('ERROR','erreur')." : ".get_translation('UNSAVED_HOSPITAL_DEPARTMENTS','services non sauvés')."</strong>");
                                }
                        }
                        if ($log=='ok') {
                                print "<strong style=\"color:green\">".get_translation('USER_SUCESSFULY_REGISTERED','utilisateur enregistré avec succès')."</strong>";
                        }
                        $res=$user_num;
                }
        }
        return $res;
}




function trad_en_nombre_mois ($mois) {
        if ($mois=='01') {
                $mois_lettre='janvier';
        }
        if ($mois=='02' ) {
                $mois_lettre='f[ée]vrier';
        }
        if ($mois=='03') {
                $mois_lettre='mars';
        }
        if ($mois=='04') {
                $mois_lettre='avril';
        }
        if ($mois=='05' ) {
                $mois_lettre='mai';
        }
        if ($mois=='06') {
                $mois_lettre='juin';
        }
        if ($mois=='07') {
                $mois_lettre='juillet';
        }
        if ($mois=='08' ) {
                $mois_lettre='ao[ûu]t';
        }
        if ($mois=='septembre') {
                $mois_lettre='septembre';
        }
        if ($mois=='10') {
                $mois_lettre='octobre';
        }
        if ($mois=='11') {
                $mois_lettre='novembre';
        }
        if ($mois=='12') {
                $mois_lettre='d[eé]cembre';
        }
        return $mois_lettre;
}

function afficher_datamart_ligne($datamart_num) {
        global $dbh;
        if ($datamart_num!='') {
                $req=" and datamart_num=$datamart_num";
        }
        $sel_vardroit=oci_parse($dbh,"select title_datamart,description_datamart, to_char(date_start,'DD/MM/YYYY') as date_start,to_char(end_date,'DD/MM/YYYY') as end_date,datamart_num from dwh_datamart where temporary_status is null $req order by datamart_num desc");
        oci_execute($sel_vardroit);
        while ($r_droit=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $title_datamart=$r_droit['TITLE_DATAMART'];
                $description_datamart=$r_droit['DESCRIPTION_DATAMART'];
                $date_start=$r_droit['DATE_START'];
                $end_date=$r_droit['END_DATE'];
                $datamart_num=$r_droit['DATAMART_NUM'];
                
                $sel_pat=oci_parse($dbh,"select count(*) as nb_patient from dwh_datamart_result where datamart_num=$datamart_num");
                oci_execute($sel_pat);
                $r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC);
                $nb_patient_datamart=$r_pat['NB_PATIENT'];
                
                $liste_droit='';
                $sel_pat=oci_parse($dbh,"select distinct right from dwh_datamart_user_right where datamart_num=$datamart_num");
                oci_execute($sel_pat);
                while ($r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC)) {
                        $liste_droit.=$r_pat['RIGHT'].", ";
                }
                $liste_droit=substr($liste_droit,0,-2);
                
                $liste_document_origin_code='';
                $sel_pat=oci_parse($dbh,"select distinct document_origin_code from dwh_datamart_doc_origin where datamart_num=$datamart_num");
                oci_execute($sel_pat);
                while ($r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC)) {
                        $liste_document_origin_code.=$r_pat['DOCUMENT_ORIGIN_CODE'].", ";
                }
                $liste_document_origin_code=substr($liste_document_origin_code,0,-2);
                
                
                $liste_user='';
                $liste_user_detail='';
                $sel_pat=oci_parse($dbh,"select distinct lastname,firstname from dwh_datamart_user_right,dwh_user where datamart_num=$datamart_num and dwh_user.user_num=dwh_datamart_user_right.user_num");
                oci_execute($sel_pat);
                while ($r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC)) {
                        $liste_user_detail.=$r_pat['LASTNAME']." ".$r_pat['FIRSTNAME']."<br>";
                        $liste_user.=$r_pat['LASTNAME']." ".$r_pat['FIRSTNAME'].",";
                }
                $liste_user_detail=substr($liste_user_detail,0,-4);
                $liste_user=substr($liste_user,0,-1);
                
                print "<tr id=\"id_tr_datamart_$datamart_num\">
                        <td>$title_datamart</td>
                        <td>$date_start</td>
                        <td>$end_date</td>
                        <td>$liste_droit</td>
                        <td>$liste_document_origin_code</td>
                        <td>".substr($liste_user,0,20);
                if (substr($liste_user,0,20)!=$liste_user) {
                        print "...
                                <a class=\"infobulle\" onclick=\"return false;\" href=\"#\">
                                        <img width=\"20\" border=\"0\" src=\"images/infobulle.gif\">
                                        <div id=\"id_span_liste_user_infobulle_$datamart_num\">$liste_user_detail</div>
                                </a>";
                }
                print "</td>
                        <td>".substr($description_datamart,0,20);
                if (substr($description_datamart,0,20)!=$description_datamart) {
                        print "...
                                <a class=\"infobulle\" onclick=\"return false;\" href=\"#\">
                                        <img width=\"20\" border=\"0\" src=\"images/infobulle.gif\">
                                        <div id=\"id_span_description_datamart_infobulle_$datamart_num\">$description_datamart</div>
                                </a>";
                }
                print "</td>
                        <td>$nb_patient_datamart</td>
                        ";
                print "<td><a href=\"#\" onclick=\"afficher_formulaire_modifier_datamart($datamart_num);return false;\">".get_translation('MODIFY_SHORT','M')."</a></td>";
                print "<td><a href=\"#\" onclick=\"supprimer_datamart($datamart_num);return false;\">X</a></td>";
                print "</tr>";
        }
}


function afficher_datamart_select ($id_select_num_datamart) {
        global $dbh;
        print "<select name='datamart' id=\"$id_select_num_datamart\">
                <option value=''></option>";
        $sel_vardroit=oci_parse($dbh,"select title_datamart,description_datamart, to_char(date_start,'DD/MM/YYYY') as date_start,to_char(end_date,'DD/MM/YYYY') as end_date,datamart_num from dwh_datamart  where temporary_status is null order by datamart_num desc");
        oci_execute($sel_vardroit);
        while ($r_droit=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $title_datamart=$r_droit['TITLE_DATAMART'];
                $description_datamart=$r_droit['DESCRIPTION_DATAMART'];
                $date_start=$r_droit['DATE_START'];
                $end_date=$r_droit['END_DATE'];
                $datamart_num=$r_droit['DATAMART_NUM'];
                
                $sel_pat=oci_parse($dbh,"select count(*) as nb_patient from dwh_datamart_result where datamart_num=$datamart_num");
                oci_execute($sel_pat);
                $r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC);
                $nb_patient_datamart=$r_pat['NB_PATIENT'];
                
                print "<option value=\"$datamart_num\">$title_datamart ($date_start - $end_date) $nb_patient_datamart patients</option>";
        }
        print "</select>";
}


function afficher_datamart_accueil () {
        global $dbh,$user_num_session;
        $res_datamart='';
         $sel_vardroit=oci_parse($dbh,"select title_datamart,description_datamart, to_char(date_start,'DD/MM/YYYY') as date_start,to_char(end_date,'DD/MM/YYYY') as end_date,datamart_num from dwh_datamart where
         datamart_num in (select datamart_num from dwh_datamart_user_right where user_num=$user_num_session)
         and end_date > sysdate and date_start < sysdate  and temporary_status is null order by datamart_num desc");
        oci_execute($sel_vardroit);
        while ($r_droit=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $title_datamart=$r_droit['TITLE_DATAMART'];
                $description_datamart=$r_droit['DESCRIPTION_DATAMART'];
                $date_start=$r_droit['DATE_START'];
                $end_date=$r_droit['END_DATE'];
                $datamart_num=$r_droit['DATAMART_NUM'];
                
                $sel_pat=oci_parse($dbh,"select count(*) as nb_patient from dwh_datamart_result where datamart_num=$datamart_num");
                oci_execute($sel_pat);
                $r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC);
                $nb_patient_datamart=$r_pat['NB_PATIENT'];
                
                
                 $res_datamart.= "<tr id=\"id_tr_datamart_$datamart_num\" style=\"cursor: pointer; background-color: rgb(255, 255, 255);\" onclick=\"self.location='moteur.php?datamart_num=$datamart_num';\" onmouseout=\"this.style.backgroundColor='#ffffff';\" onmouseover=\"this.style.backgroundColor='#dcdff5';\">
                        <td>$title_datamart</td>
                        <td>$date_start</td>
                        <td>$end_date</td>
                        <td>".substr($description_datamart,0,20);
                if (substr($description_datamart,0,20)!=$description_datamart) {
                         $res_datamart.= "...
                                <a class=\"infobulle\" onclick=\"return false;\" href=\"#\">
                                        <img width=\"20\" border=\"0\" src=\"images/infobulle.gif\">
                                        <div id=\"id_span_description_datamart_infobulle_$datamart_num\">$description_datamart</div>
                                </a>";
                }
                 $res_datamart.= "</td>
                        <td>$nb_patient_datamart</td>
                        ";
                 $res_datamart.= "</tr>";
        }
        if ($res_datamart!='') {
        	print "<table id=\"id_tableau_liste_datamart\" class=\"tablefin\">
			<thead>
				<tr>
					<th>".get_translation('TITLE','Titre')."</th>
					<th>".get_translation('DATE_START','Date de début')."</th>
					<th>".get_translation('DATE_END','Date fin')."</th>
					<th>".get_translation('DESCRIPTION','Description')."</th>
					<th>".get_translation('COUNT_PATIENT_NUMBER_SHORT','Nb patients')."</th>
				</tr>
			</thead>
			<tbody>$res_datamart</tbody></table>";
	} 
}

function recup_titre_cohorte($cohort_num) {
        global $dbh,$user_num_session;
        $sel_pat=oci_parse($dbh,"select title_cohort from dwh_cohort where cohort_num=$cohort_num");
        oci_execute($sel_pat);
        $r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC);
        $title_cohort=$r_pat['TITLE_COHORT'];
        

        $sel_pat=oci_parse($dbh,"select count(distinct patient_num) as nb_patient from dwh_cohort_result where cohort_num=$cohort_num and status=1");
        oci_execute($sel_pat);
        $r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC);
        $nb_patient_cohorte=$r_pat['NB_PATIENT'];
        
        return array($title_cohort,$nb_patient_cohorte);
}

function afficher_cohorte_ligne($cohort_num_filtre) {
        global $dbh,$user_num_session,$datamart_num;
        print "<table id=\"id_tableau_cohorte_select\" class=\"tablefin\">
                <thead>
                        <tr>
                                <tr>
                                        <th>".get_translation('TITLE','Titre')."</th>
                                        <th width=\"600\">".get_translation('DESCRIPTION','Description')."</th>
                                        <th>".get_translation('USERS','Utilisateurs')."</th>
                                        <th>".get_translation('COUNT_INCLUDED','Nb inclus')."</th>
                                        <th>".get_translation('COUNT_DOUBTS','Nb Doutes')."</th>
                        </tr>
                </thead>
                <tbody>";
        $sel_vardroit=oci_parse($dbh,"select title_cohort,description_cohort, cohort_num from dwh_cohort where 
         (user_num=$user_num_session or cohort_num in (select cohort_num from dwh_cohort_user_right where user_num=$user_num_session and right='add_patient'))
         order by cohort_num desc");
        oci_execute($sel_vardroit);
        while ($r_droit=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $title_cohort=$r_droit['TITLE_COHORT'];
                $description_cohort=$r_droit['DESCRIPTION_COHORT'];
                $cohort_num=$r_droit['COHORT_NUM'];
                
                $sel_pat=oci_parse($dbh,"select count(*) as nb_patient from dwh_cohort_result where cohort_num=$cohort_num and status=1");
                oci_execute($sel_pat);
                $r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC);
                $nb_patient_cohorte=$r_pat['NB_PATIENT'];
                
              
                $sel_pat=oci_parse($dbh,"select count(*) as nb_patient from dwh_cohort_result where cohort_num=$cohort_num and status=2");
                oci_execute($sel_pat);
                $r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC);
                $nb_patient_cohorte_doute=$r_pat['NB_PATIENT'];
                
                
                $liste_user='';
                $liste_user_detail='';
                $sel_pat=oci_parse($dbh,"select distinct lastname,firstname from dwh_cohort_user_right,dwh_user where cohort_num=$cohort_num and dwh_user.user_num=dwh_cohort_user_right.user_num");
                oci_execute($sel_pat);
                while ($r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC)) {
                        $liste_user_detail.=$r_pat['LASTNAME']." ".$r_pat['FIRSTNAME']."<br>";
                        $liste_user.=$r_pat['LASTNAME']." ".$r_pat['FIRSTNAME'].",";
                }
                $liste_user_detail=substr($liste_user_detail,0,-4);
                $liste_user=substr($liste_user,0,-1);
                
                print "<tr id=\"id_tr_cohorte_$cohort_num\" onmouseover=\"this.style.backgroundColor='#dcdff5';\" onmouseout=\"this.style.backgroundColor='#ffffff';\" onclick=\"select_cohorte($cohort_num);voir_detail_dwh('cohorte_encours');\" style=\"cursor:pointer;\">
                        <td nowrap><strong>$title_cohort</strong></td>
                        <td>".substr($description_cohort,0,400);
                if (substr($description_cohort,0,400)!=$description_cohort) {
                        print "...
                                <a class=\"infobulle\" onclick=\"return false;\" href=\"#\">
                                        <img width=\"20\" border=\"0\" src=\"images/infobulle.gif\">
                                        <div id=\"id_span_description_cohort_infobulle_$cohort_num\">$description_cohort</div>
                                </a>";
                }
                print "</td>
                        <td>".substr($liste_user,0,150);
                if (substr($liste_user,0,150)!=$liste_user) {
                        print "...
                                <a class=\"infobulle\" onclick=\"return false;\" href=\"#\">
                                        <img width=\"20\" border=\"0\" src=\"images/infobulle.gif\">
                                        <div id=\"id_span_liste_user_infobulle_$cohort_num\">$liste_user_detail</div>
                                </a>";
                }
                print "</td>
                        
                        <td>$nb_patient_cohorte</td>
                        <td>$nb_patient_cohorte_doute</td>
                        ";
               
                print "</tr>";
        }
        print "
                </tbody>
        </table>";
}




function get_list_cohort_patient($user_num,$patient_num) {
	global $dbh;
	$liste_cohort=array();
	$sel=oci_parse($dbh,"select title_cohort,description_cohort, cohort_num from dwh_cohort where 
	(user_num=$user_num or cohort_num in (select cohort_num from dwh_cohort_user_right where user_num=$user_num ))
	and cohort_num in (select cohort_num from dwh_cohort_result where patient_num=$patient_num and status=1)
	order by cohort_num desc");
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$cohort_num=$r['COHORT_NUM'];
		$liste_cohort[]= $cohort_num;
	}
	return  $liste_cohort;
}



function get_list_patient_in_cohort($cohort_num,$status) {
	global $dbh;
	$liste_patient=array();
        $sel=oci_parse($dbh,"select distinct dwh_cohort_result.patient_num,lastname,firstname from dwh_cohort_result,dwh_patient where cohort_num=$cohort_num and dwh_cohort_result.status=$status and dwh_cohort_result.patient_num=dwh_patient.patient_num order by lastname, firstname");
        oci_execute($sel);
        while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$patient_num=$r['PATIENT_NUM'];
		$liste_patient[]= $patient_num;
	}
	return  $liste_patient;
}


function get_user_cohorts ($user_num,$option_filter_right) {
	global $dbh;
	$tab_user_cohorts=array();
	$sel_cohort=oci_parse($dbh,"select cohort_num from dwh_cohort where 
         (user_num=$user_num or cohort_num in (select cohort_num from dwh_cohort_user_right where user_num=$user_num $option_filter_right) )
        order by title_cohort ");
        oci_execute($sel_cohort);
        while ($r_cohort=oci_fetch_array($sel_cohort,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$cohort_num=$r_cohort['COHORT_NUM'];
         	$tab_user_cohorts[]=$cohort_num;
        }
        return $tab_user_cohorts;
}

function get_a_patient_in_a_cohort ($cohort_num,$patient_num,$option) {
	global $dbh;
	$patient_num_res='';
	if ($option=='first') {
		$tab_patient_num=get_list_patient_in_cohort($cohort_num,1);
		$patient_num_res=$tab_patient_num[0];
	}
	if ($option=='last') {
		$tab_patient_num=get_list_patient_in_cohort($cohort_num,1);
		$patient_num_res=$tab_patient_num[count($tab_patient_num)-1];
	}
	if ($option=='next') {
		$tab_patient_num=get_list_patient_in_cohort($cohort_num,1);
		$i_patient_num = array_search($patient_num, $tab_patient_num); 
		$patient_num_res=$tab_patient_num[$i_patient_num+1];
	}
	
        return $patient_num_res;
}



function get_cohort($cohort_num,$option_nb) {
	global $dbh;
	$r=array();
	$sel=oci_parse($dbh,"select 
		title_cohort,
		description_cohort, 
		cohort_num,
		user_num ,
		datamart_num ,
		to_char(cohort_date,'DD/MM/YYYY') as cohort_date 
	from dwh_cohort where cohort_num=$cohort_num ");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	
	if ($option_nb=='nb_patients') {
	        $sel_pat=oci_parse($dbh,"select count(*) as nb_patient from dwh_cohort_result where cohort_num=".$r['COHORT_NUM']." and status=1");
	        oci_execute($sel_pat);
	        $r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC);
		$r['NB_PATIENT_COHORTE']=$r_pat['NB_PATIENT'];
		
		$sel_pat=oci_parse($dbh,"select count(*) as nb_patient from dwh_cohort_result where cohort_num=".$r['COHORT_NUM']." and status=2");
		oci_execute($sel_pat);
		$r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC);
		$r['NB_PATIENT_COHORTE_DOUTE']=$r_pat['NB_PATIENT'];
	}
	
	return  $r;
}


function afficher_cohorte_ligne_accueil() {
        global $dbh,$user_num_session;
        print "<table id=\"id_tableau_cohorte_select\" class=\"tablefin\">
                <thead>
                        <tr>
                                <tr>
                                        <th></th>
                                        <th>".get_translation('TITLE','Titre')."</th>
                                        <th>".get_translation('COUNT_INCLUDED','Nb inclus')."</th>
                                        <th>".get_translation('COUNT_DOUBTS','Nb Doutes')."</th>
                        </tr>
                </thead>
                <tbody>";
                
        $tab_user_cohorts=get_user_cohorts($user_num_session," and right='add_patient'");
	foreach ($tab_user_cohorts as $cohort_num) {
		$cohort=get_cohort($cohort_num,'nb_patients');
		$title_cohort=$cohort['TITLE_COHORT'];
                $title_cohort=$cohort['TITLE_COHORT'];
                $description_cohort=$cohort['DESCRIPTION_COHORT'];
                $cohort_num=$cohort['COHORT_NUM'];
                $user_num_creation=$cohort['USER_NUM'];
                $nb_patient_cohorte=$cohort['NB_PATIENT_COHORTE'];
                $nb_patient_cohorte_doute=$cohort['NB_PATIENT_COHORTE_DOUTE'];
                
		if ($user_num_creation==$user_num_session) {
			$img="<img src=\"images/user_grey_small.png\" alt=\"créateur\" title=\"créateur\">";
		} else {
			$img="";
		}
                print "<tr id=\"id_tr_cohorte_$cohort_num\" class=\"over_color\" onclick=\"self.location='mes_cohortes.php?cohort_num_voir=$cohort_num';\" style=\"cursor:pointer;\">
                        <td>$img</td>
                        <td><strong>$title_cohort</strong></td>
                        <td>$nb_patient_cohorte</td>
                        <td>$nb_patient_cohorte_doute</td>
                </tr>";
        }
        print "
                </tbody>
        </table>";
}


function liste_patient_cohorte_encours ($cohort_num,$status) {
        global $dbh,$user_num_session,$datamart_num;
        $res='';
        $limite_nb_patient=1000;
        if ( $cohort_num!='') {
	        $i=0;
	        $autorisation_voir_patient_cohorte=verif_autorisation_voir_patient_cohorte($cohort_num,$user_num_session);
	        if ( $autorisation_voir_patient_cohorte=='ok') {
			$res.= "<a href=\"export_excel.php?cohort_num=$cohort_num&status=$status\"><img src=\"images/excel_noir.png\" style=\"cursor:pointer;width:25px;\" title=\"Export Excel\" alt=\"Export Excel\" border=\"0\"></a> ";
			$res.= "<img src=\"images/copier_coller.png\" onclick=\"plier_deplier('id_div_tableau_patient_cohorte_encours$status');plier_deplier('id_div_textarea_patient_cohorte_encours$status');fnSelect('id_div_textarea_patient_cohorte_encours$status');\" style=\"cursor:pointer;\" title=\"Copier Coller pour exporter dans Gecko\" alt=\"Copier Coller pour exporter dans Gecko\"> ";
			$res.= "<div  id=\"id_div_tableau_patient_cohorte_encours$status\" style=\"display:block;\">";
		        $res.= "<table id=\"id_tableau_patient_cohorte_encours$status\" class=\"tableau_cohorte\">";
		        $tab_num_patient=get_list_patient_in_cohort($cohort_num,$status);
		        foreach ($tab_num_patient as $patient_num) {
#		        $sel=oci_parse($dbh,"select distinct dwh_cohort_result.patient_num,lastname,firstname from dwh_cohort_result,dwh_patient where cohort_num=$cohort_num and dwh_cohort_result.status=$status and dwh_cohort_result.patient_num=dwh_patient.patient_num order by lastname, firstname");
#		        oci_execute($sel);
#		        while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
#			        $patient_num=$r['PATIENT_NUM'];
		        	if ($i<=$limite_nb_patient) {
			                $res.= "<tr id=\"id_tr_patient_cohorte_$patient_num\" class=\"over_color\"><td>";
			                $res.=afficher_patient($patient_num,'cohorte','',$cohort_num);
			                $res_cohorte_textarea.=afficher_patient($patient_num,'cohorte_textarea','',$cohort_num);
			                $res.="</td></tr>";
			        }
			        $i++;
		        }
		        $res.= "</table>";
		        if ($i>$limite_nb_patient) {
		        	$res.= "seulement les 1000 premiers patients";
		        }
		        $res.= "</div>";
			$res.= "<pre id=\"id_div_textarea_patient_cohorte_encours$status\" style=\"display:none;\" >$res_cohorte_textarea</pre>";
		}
	}
    return $res;
}



function affiche_liste_document_patient($patient_num,$requete) {
	global $dbh,$datamart_num,$user_num_session,$document_origin_code_labo;
	$autorisation_voir_patient=autorisation_voir_patient ($patient_num,$user_num_session);
	if ($autorisation_voir_patient=='ok') {
		$req="";
		if ($document_origin_code_labo!='') {
			$req_option=" and (document_origin_code!='$document_origin_code_labo' or document_origin_code is null)  ";
		}
		$test_expression_reguliere='';
		if (preg_match("/[[|?+{]/",$requete)) {
			$test_expression_reguliere='ok';
		}
		if ($test_expression_reguliere=='ok') {
			$cellspacing="cellspacing=0 cellpadding=2";
			print "<a href=\"#\" onclick=\"fnSelect('id_tableau_liste_document');return false;\">".get_translation('SELECT_TABLE','Sélectionner le tableau')."</a><br>";
			
		} else {
			$cellspacing="";
		}
	        $tableau_liste_synonyme=array();
		if ($requete!='') {
			if ($test_expression_reguliere=='ok') {
				//$req="and document_num in (select document_num from dwh_text where patient_num=$patient_num and (REGEXP_LIKE(text,'$requete','i') or REGEXP_LIKE(title,'$requete','i'))) ";
				$req=""; // NG 2019 07 22 : car oracle ne gère pas bien les regexp like : pas de lookahead, notamment pour exclure des mots (?!mot) ; on met le test dans le php 
			} else {
				//$req="and document_num in (select document_num from dwh_text where patient_num=$patient_num and (contains(enrich_text,'$requete')>0 or contains(title,'$requete')>0) ) ";
				$req="and document_num in (select document_num from dwh_text where patient_num=$patient_num and (contains(text,'$requete')>0 or contains(title,'$requete')>0) ) ";
				$tableau_liste_synonyme=recupere_liste_concept_full_texte ($requete,400);
			}
		}
		$liste_document_origin_code=list_authorized_document_origin_code_for_one_patient($patient_num,$user_num_session);
	        if ($liste_document_origin_code!='') {
	        	if (!preg_match("/'tout'/i","$liste_document_origin_code")) {
				$req.="and document_origin_code in ($liste_document_origin_code) ";
	        	}
	        } else {
	              $req.=" and 1=2";
	        }
	        $nb_document=0;
	        $nb_encounter_num=0;
	        $encounter_num_before='';
	        $color_not_encounter_num='#ffffff';
	        $color_odd_encounter_num='#dde9f0';
	        $color_even_encounter_num='#ede6ed';
		$res= "<table class=\"tableau_document\" $cellspacing id=\"id_tableau_liste_document\">";
		$table_document=get_document_for_a_patient($patient_num,"$req  $req_option");
		foreach ($table_document as $document_num) {
			$document=get_document ($document_num);
			$encounter_num=$document['encounter_num'];
			$title=$document['title'];
			$document_date=$document['document_date'];
			$document_origin_code=$document['document_origin_code'];
			$author=$document['author'];
			$text=$document['text'];    
			if ($document['displayed_text']!='') {
				if ($_SESSION['dwh_droit_fuzzy_display']=='ok') {
					$author='[AUTHOR]';
					$document_date='[DATE]';
				}
				if ($encounter_num=='') {
					$backgroundColor=$color_not_encounter_num;
				} else {
					if ($encounter_num!=$encounter_num_before) {
						$nb_encounter_num++;
					}
					if ($nb_encounter_num % 2 ==0) {
						$backgroundColor=$color_odd_encounter_num;
					} else {
						$backgroundColor=$color_even_encounter_num;
					}
				}
				$tr= "<tr onmouseout=\"this.style.backgroundColor='$backgroundColor';\" onmouseover=\"this.style.backgroundColor='#B9C2C8';\" onclick=\"afficher_document_patient($document_num,'id_div_voir_document','id_input_filtre_patient_text');\" style=\"cursor: pointer; background-color:$backgroundColor;\" id=\"id_document_patient_$document_num\" class=\"tr_document_patient id_document_patient_$document_num\" sousgroupe=\"text\">";
				if ($test_expression_reguliere=='ok') {
					$text=preg_replace("/\n/"," ",$text);
					if (preg_match_all("/$requete/i","$text",$out, PREG_SET_ORDER)) {
						$nb_document++;
						$res.= $tr;
						$res.= "<td style=\"border:1px solid black; border-collapse: collapse;\">$document_date</td>";
						foreach ($out[0] as $val) {
							$res.= "<td style=\"border:1px solid black; border-collapse: collapse;\">".$val."</td>";
						}
					}
				} else {
					$nb_document++;
					$res.= $tr;
					$res.= "
					<th style=\"text-align:left;\">$document_origin_code</th><th style=\"text-align:left;\"> $title $author</td>
					<td>$document_date</td>";
					$res.= "</tr>";
					if ($requete!='') {
						$appercu=resumer_resultat($text,$requete,$tableau_liste_synonyme,'patient');
						$res.= "<tr><td colspan=\"4\" class=\"appercu\"><i>$appercu</i></td><tr>";
					}
					$res.= "<tr><td colspan=\"4\"><hr  style=\"height:1px;border-top:0px;padding:0px;margin:0px;\"></td>";
				}
				$res.= "</tr>";
			}
			if ($encounter_num!='') {
				$encounter_num_before=$encounter_num;
			}
		}
		$res.= "</table>";
		
		if ($nb_document==0) {
			print get_translation('NO_DOCUMENT_FOUND','aucun document trouvé');
		} else {
			print "$nb_document ".get_translation('DOCUMENTS_FOUND','documents trouvés')." <img border=\"0\" align=\"absmiddle\" style=\"cursor:pointer;width:18px;\" onclick=\"ouvrir_liste_document_print('$patient_num');return false;\" src=\"images/printer.png\"><br><br>";
			print $res;
		}
	}
}


function affiche_liste_document_biologie($patient_num,$requete) {
	global $dbh,$datamart_num,$user_num_session,$document_origin_code_labo;
	$autorisation_voir_patient=autorisation_voir_patient ($patient_num,$user_num_session);
	if ($autorisation_voir_patient=='ok') {
		$req="";
		if ($document_origin_code_labo!='') {
			$req_option=" and document_origin_code='$document_origin_code_labo' ";
			$test_expression_reguliere='';
			if (preg_match("/[[|?+{]/",$requete)) {
				$test_expression_reguliere='ok';
			}
			if ($test_expression_reguliere=='ok') {
				$cellspacing="cellspacing=0 cellpadding=2";
				print "<a href=\"#\" onclick=\"fnSelect('id_tableau_liste_document_biologie');return false;\">".get_translation('SELECT_TABLE','Sélectionner le tableau')."</a>";
			} else {
				$cellspacing="";
			}
			
			if ($requete!='') {
				if ($test_expression_reguliere=='ok') {
					//$req="and document_num in (select document_num from dwh_text where patient_num=$patient_num and (REGEXP_LIKE(text,'$requete','i') or REGEXP_LIKE(title,'$requete','i'))) ";
					$req=""; // NG 2019 07 22 : car oracle ne gère pas bien les regexp like : pas de lookahead, notamment pour exclure des mots (?!mot) ; on met le test dans le php 
				} else {
					$req="and document_num in (select document_num from dwh_text where patient_num=$patient_num and (contains(text,'$requete')>0 or contains(title,'$requete')>0) ) ";
				}
			}
			$liste_document_origin_code=list_authorized_document_origin_code_for_one_patient($patient_num,$user_num_session);
		        if ($liste_document_origin_code!='') {
		        	if (!preg_match("/'tout'/i","$liste_document_origin_code")) {
					$req.="and document_origin_code in ($liste_document_origin_code) ";
		        	}
		        } else {
		              $req.=" and 1=2";
		        }
		        $nb_document=0;
			$res="<table class=\"tableau_document\" $cellspacing id=\"id_tableau_liste_document_biologie\">";
			
			$table_document=get_document_for_a_patient($patient_num,"$req  $req_option");
			foreach ($table_document as $document_num) {
				$document=get_document ($document_num);
				$encounter_num=$document['encounter_num'];
				$title=$document['title'];
				$document_date=$document['document_date'];
				$document_origin_code=$document['document_origin_code'];
				$author=$document['author'];
				$text=$document['text'];    
				if ($document['displayed_text']!='') {
					$tr= "<tr onmouseout=\"this.style.backgroundColor='#ffffff';\" onmouseover=\"this.style.backgroundColor='#B9C2C8';\" onclick=\"afficher_document_patient_biologie($document_num);\" style=\"cursor: pointer; background-color:#ffffff;\" id=\"id_document_patient_$document_num\" class=\"tr_document_patient\" sousgroupe=\"biologie\">";
					
					if ($test_expression_reguliere=='ok') {
						$text=preg_replace("/\n/"," ",$text);
						if (preg_match_all("/$requete/i","$text",$out, PREG_SET_ORDER)) {
			                		$nb_document++;
			                		$res.= $tr;
							$res.= "<td style=\"border:1px solid black; border-collapse: collapse;\">$document_date</td>";
							foreach ($out[0] as $val) {
									$res.= "<td style=\"border:1px solid black; border-collapse: collapse;\">".$val."</td>";
							}
						}
			                } else {
		                		$nb_document++;
		                		$res.= $tr;
						$res.= "<th style=\"text-align:left;\">$title</th>
							<td>$document_date</td>
							<td>$document_origin_code</td>
							<td>$author</td>
							</tr>";
						$appercu=resumer_resultat($text,$requete,$tableau_liste_synonyme,'patient');
						$res.= "<tr><td colspan=\"4\"><i>$appercu</i></td><tr>";
						$res.= "<tr><td colspan=\"4\"><hr  style=\"height:1px;border-top:0px;padding:0px;margin:0px;\"></td>";
					}
					$res.= "</tr>";
			        }
		        }
		        $res.= "</table>";
		        
		        if ($nb_document==0) {
		        	print get_translation('NO_DOCUMENT_FOUND','aucun document trouvé');
		        } else {
		        	print "$nb_document ".get_translation('DOCUMENTS_FOUND','documents trouvés')."<br><br>";
		        	print $res;
		        }
		}
	}
}



function affiche_liste_id_document_patient($patient_num,$requete) {
	global $dbh,$datamart_num,$user_num_session,$document_origin_code_labo;
	$liste_id_document='';
	$autorisation_voir_patient=autorisation_voir_patient ($patient_num,$user_num_session);
	if ($autorisation_voir_patient=='ok') {
		$req="";
		if ($document_origin_code_labo!='') {
			$req_option=" and document_origin_code!='$document_origin_code_labo' ";
		}
		$test_expression_reguliere='';
		if (preg_match("/[[|?+{]/",$requete)) {
			$test_expression_reguliere='ok';
		}
	        $tableau_liste_synonyme=array();
		if ($requete!='') {
			if ($test_expression_reguliere=='ok') {
				//$req="and document_num in (select document_num from dwh_text where (REGEXP_LIKE(text,'$requete','i') or REGEXP_LIKE(title,'$requete','i')) and patient_num=$patient_num) ";
				$req=""; // NG 2019 07 22
			} else {
				//$req="and document_num in (select document_num from dwh_text where (contains(enrich_text,'$requete')>0 or contains(title,'$requete')>0) and patient_num=$patient_num) ";
				$req="and document_num in (select document_num from dwh_text where (contains(text,'$requete')>0 or contains(title,'$requete')>0) and patient_num=$patient_num) ";
				$tableau_liste_synonyme=recupere_liste_concept_full_texte ($requete,400);
			}
		}
		$liste_document_origin_code=list_authorized_document_origin_code_for_one_patient($patient_num,$user_num_session);
	        if ($liste_document_origin_code!='') {
	        	if (!preg_match("/'tout'/i","$liste_document_origin_code")) {
				$req.="and document_origin_code in ($liste_document_origin_code) ";
	        	}
	        } else {
	              $req.=" and 1=2";
	        }
		$table_document=get_document_for_a_patient($patient_num,"$req  $req_option");
		$table_document_res=array();
		foreach ($table_document as $document_num) {
			$document=get_document ($document_num);
			$text=$document['text'];    
			if ($test_expression_reguliere=='ok') {
				$text=preg_replace("/\n/"," ",$text);
				if (preg_match_all("/$requete/i","$text",$out, PREG_SET_ORDER)) {
	                		$table_document_res[]=$document_num;
				}
	                } else {
	                	$table_document_res[]=$document_num;
	                }
		}
	        return $table_document_res;
	}
}


function afficher_document_patient($document_num,$full_text_query,$user_num) {
        global $dbh;

        
	$patient_num=get_num_patient_from_id_document($document_num);
	$autorisation_voir_patient=autorisation_voir_patient ($patient_num,$user_num);
	
	if ($autorisation_voir_patient=='ok') {
	
	        $document=get_document ($document_num);
		$displayed_text=$document['displayed_text'];     
	        $title=$document['title'];     
	        $patient_num=$document['patient_num']; 
	        $document_date=$document['document_date']; 
	        $author=$document['author']; 
	        $document_date=$document['document_date']; 
	        $patient_num=$document['patient_num']; 
	        
#	        $sel_texte = oci_parse($dbh,"select displayed_text,title,patient_num,author,to_char(document_date,'DD/MM/YYYY') as document_date from dwh_document where document_num=$document_num" );   
#	        oci_execute($sel_texte);
#	        $row_texte = oci_fetch_array($sel_texte, OCI_ASSOC);
#	        if ($row_texte['DISPLAYED_TEXT']!='') {
#	                $displayed_text=$row_texte['DISPLAYED_TEXT']->load();         
#	        }
#	        $title=$row_texte['TITLE'];     
#	        $author=$row_texte['AUTHOR'];     
#	        $document_date=$row_texte['DOCUMENT_DATE'];     
#	        $patient_num=$row_texte['PATIENT_NUM']; 
	        
	        if ($_SESSION['dwh_droit_fuzzy_display']=='ok') {
	        	$author='[AUTHOR]';
	                $document_date='[DATE]';
	        }
	        
	        $tableau_liste_synonyme=array();
		if (!preg_match("/[[|?+]/",$full_text_query)) {
			$tableau_liste_synonyme=recupere_liste_concept_full_texte ($full_text_query,400);
		}
	        $autorisation_voir_patient_nominative=autorisation_voir_patient_nominative ($patient_num,$user_num);
	        if ($autorisation_voir_patient_nominative=='') {
	                $displayed_text=anonymisation_document ($document_num,$displayed_text);
	        }
	        if ($_SESSION['dwh_droit_see_debug']=='ok') {
		       $displayed_text= afficher_dans_document_tal($document_num,$user_num);
		}
	        $displayed_text=nettoyer_pour_afficher ($displayed_text);
	        
		if (preg_match("/[[|?+{]/",$full_text_query)) {
			$displayed_text=surligner_resultat_exp_reguliere ($displayed_text,$full_text_query,'oui');
		} else {
		        if ($full_text_query!='') {
			        $displayed_text=surligner_resultat ($displayed_text,$full_text_query,'','oui',$tableau_liste_synonyme,'');
			}
		        if (!preg_match("/highlight/",$displayed_text) ) {
		                $displayed_text=surligner_resultat ($displayed_text,$full_text_query,'maxdistance','oui',$tableau_liste_synonyme,'');
		        }
		        if (!preg_match("/highlight/",$displayed_text) ) {
		                $displayed_text=surligner_resultat ($displayed_text,$full_text_query,'unitaire','oui',$tableau_liste_synonyme,'');
		        }
		}
		$displayed_text=display_image_in_document ($patient_num,$document_num,$user_num,$displayed_text);

        $document= "<h2>$title,";
        if ($author!='') {
        	$document.=" ".get_translation('BY_FOLLOWED_BY_NAME','par')." $author,";
        }
	$document.=" ".get_translation('THE_DATE','le')." $document_date <img align=\"absmiddle\" src=\"images/printer.png\" onclick=\"ouvrir_document_print('$document_num');return false;\" style=\"cursor:pointer;width:18px;\" border=\"0\"></h2>";
	if (preg_match("/(<style[^>]*>|<br[^>]?>)/i",$displayed_text)) {
		$document.="$displayed_text";
	} else {
		$document.="<pre>$displayed_text</pre>";
	}   
        $document.="<br><br>".display_list_file ($patient_num,$document_num,$user_num);
        
        save_log_document($document_num,$user_num,'oui');
        return $document;
	}
}

function affiche_contenu_liste_document_patient($patient_num,$requete,$user_num) {
	global $dbh,$datamart_num,$document_origin_code_labo;
	$autorisation_voir_patient=autorisation_voir_patient ($patient_num,$user_num);
	$texte_final="";
	if ($autorisation_voir_patient=='ok') {
		$req="";
		if ($document_origin_code_labo!='') {
			$req_option=" and document_origin_code!='$document_origin_code_labo' ";
		}
		
	        $tableau_liste_synonyme=array();
		if ($requete!='') {
			if (preg_match("/[[|?+{]/",$requete)) {
				$req="and document_num in (select document_num from dwh_text where patient_num=$patient_num and (REGEXP_LIKE(text,'$requete','i') or REGEXP_LIKE(title,'$requete','i'))) ";
			} else {
				#$req="and document_num in (select document_num from dwh_text where patient_num=$patient_num and (contains(enrich_text,'$requete')>0 or contains(title,'$requete')>0) ) ";
				$req="and document_num in (select document_num from dwh_text where patient_num=$patient_num and (contains(text,'$requete')>0 or contains(title,'$requete')>0) ) ";
				$tableau_liste_synonyme=recupere_liste_concept_full_texte ($requete,400);
			}
		}
		$liste_document_origin_code=list_authorized_document_origin_code_for_one_patient($patient_num,$user_num);
	        if ($liste_document_origin_code!='') {
	        	if (!preg_match("/'tout'/i","$liste_document_origin_code")) {
				$req.="and document_origin_code in ($liste_document_origin_code) ";
	        	}
	        } else {
	              $req.=" and 1=2";
	        }
	        $nb_document=0;
	        
		$table_document=get_document_for_a_patient($patient_num,"$req  $req_option");
		foreach ($table_document as $document_num) {
			$document=get_document ($document_num);
			$encounter_num=$document['encounter_num'];
			$title=$document['title'];
			$document_date=$document['document_date'];
			$document_origin_code=$document['document_origin_code'];
			$author=$document['author'];
			$text=$document['text'];    
			$displayed_text=$document['displayed_text'];    
	                if ($displayed_text!='') {
			        $tableau_liste_synonyme=array();
			        $autorisation_voir_patient_nominative=autorisation_voir_patient_nominative ($patient_num,$user_num);
			        if ($autorisation_voir_patient_nominative=='') {
			                $displayed_text=anonymisation_document ($document_num,$displayed_text);
			        }
			        if ($_SESSION['dwh_droit_see_debug']=='ok') {
				      $displayed_text= afficher_dans_document_tal($document_num,$user_num);
				}
			        $displayed_text=nettoyer_pour_afficher ($displayed_text);
			        
				if (preg_match("/[[|?+{]/",$requete)) {
					$displayed_text=surligner_resultat_exp_reguliere ($displayed_text,$requete,'oui');
				} else {
					$tableau_liste_synonyme=recupere_liste_concept_full_texte ($requete,400);
				        if ($requete!='') {
					        $displayed_text=surligner_resultat ($displayed_text,$requete,'','oui',$tableau_liste_synonyme,1);
					}
				        if (!preg_match("/highlight/",$displayed_text) ) {
				                $displayed_text=surligner_resultat ($displayed_text,$requete,'unitaire','oui',$tableau_liste_synonyme,1);
				        }
				}
			        $texte_final.= "<h2>$title,";
			        if ($author!='') {
			        	$texte_final.=" par $author,";
			        }
			        $texte_final.=" ".get_translation('THE_DATE','le')." $document_date </h2>";
   				 	if (preg_match("/(<style[^>]*>|<br[^>]?>)/i",$displayed_text)) {
				    	$texte_final.="$displayed_text";
				    } else {
				       	$texte_final.="<pre>$displayed_text</pre>";
				    }   
  					$texte_final.="<br><p style=\"page-break-after: always;\" class=\"noprint\">----------------------------------------------------------</p>";

		        }
			
	        }
	}
	return $texte_final;
}


function afficher_dans_document_tal($document_num,$user_num) {
        global $dbh;
	$patient_num=get_num_patient_from_id_document($document_num);
	$autorisation_voir_patient=autorisation_voir_patient ($patient_num,$user_num);
	if ($autorisation_voir_patient=='ok') {
	        $sel_texte = oci_parse($dbh,"select displayed_text from dwh_document where document_num=$document_num" );   
	        oci_execute($sel_texte);
	        $row_texte = oci_fetch_array($sel_texte, OCI_ASSOC);
	        if ($row_texte['DISPLAYED_TEXT']!='') {
	                $texte_affichage_origine=$row_texte['DISPLAYED_TEXT']->load();         
	        }
	         $displayed_text= $texte_affichage_origine;
	        
	        $sel_texte = oci_parse($dbh,"select text,context,certainty from dwh_text where document_num=$document_num and context not in ('text') and certainty!=0" );   
	        oci_execute($sel_texte);
	        while ($row_texte = oci_fetch_array($sel_texte, OCI_ASSOC)) {
		        if ($row_texte['TEXT']!='') {
		                $text=$row_texte['TEXT']->load();         
		        }
			$context=$row_texte['CONTEXT'];
			$certainty=$row_texte['CERTAINTY'];
			$balise_o='';
			$balise_f='';
			if ($certainty==-1) {
				if ($context=='family_text') {
					$balise_o='[DEBUT_FAMILLE]<strike>';
					$balise_f='</strike>[FIN_FAMILLE]';
				} else {
					$balise_o='<strike>';
					$balise_f='</strike>';
				}
			}
			if ($certainty==1) {
				if ($context=='family_text') {
					$balise_o='[DEBUT_FAMILLE]';
					$balise_f='[FIN_FAMILLE]';
				} else {
					$balise_o='';
					$balise_f='';
				}
			}
			
			if ($context=='patient_text' && $certainty==-1 || $context=='family_text' && $certainty==1) {
				$tableau_phrase=explode('   ',$text);
				foreach ($tableau_phrase as $phrase) {
					$phrase=trim($phrase);
					if (preg_match("/[a-z0-9][a-z0-9]+/i",$phrase)) {
						$phrase=preg_replace("/[^a-z0-9]/i","[^a-z0-9]*",$phrase);
						if (preg_match("/($phrase)/i",$displayed_text)) {
							$displayed_text=preg_replace("/($phrase)/i","$balise_o$1$balise_f",$displayed_text);
						}
					}
				}
			}
			
		}  
		
		
	        $sel_texte = oci_parse($dbh,"select concept_str_found from dwh_enrsem where document_num=$document_num and context='patient_text' and certainty=1" );   
	        oci_execute($sel_texte);
	        while ($row_texte = oci_fetch_array($sel_texte, OCI_ASSOC)) {
			$concept_str_found=$row_texte['CONCEPT_STR_FOUND'];
			$concept_str_found=preg_replace("/[^a-z0-9]/i","[^a-z0-9]*",$concept_str_found);
			if (preg_match("/($concept_str_found)/i",$displayed_text)) {
				$displayed_text=preg_replace("/($concept_str_found)/i","<strong style='color:red;'>$1</strong>",$displayed_text);
			}
		}
	}
	
	if ($displayed_text=='') {
		$displayed_text=$texte_affichage_origine;
	}
   return "$displayed_text";
}

function mois_en_3lettre ($mois) {
	if ($mois=='01') {
		$mois='Jan';
	}
	if ($mois=='02') {
		$mois='Feb';
	}
	if ($mois=='03') {
		$mois='Mar';
	}
	if ($mois=='04') {
		$mois='Apr';
	}
	if ($mois=='05') {
		$mois='May';
	}
	if ($mois=='06') {
		$mois='Jun';
	}
	if ($mois=='07') {
		$mois='Jul';
	}
	if ($mois=='08') {
		$mois='Aug';
	}
	if ($mois=='09') {
		$mois='Sep';
	}
	if ($mois=='10') {
		$mois='Oct';
	}
	if ($mois=='11') {
		$mois='Nov';
	}
	if ($mois=='12') {
		$mois='Dec';
	}
	return $mois;

}

function is_ascii( $string = '' ) {
    return ( bool ) ! preg_match( '/[\\x80-\\xff]+/' , $string );
}

function nettoyer_accent_timeline ($text) {
	$text=preg_replace("/&/","&amp;",$text);
	$text=preg_replace("/µ/","micro",$text);
	$text=preg_replace("/</","&lt;",$text);
	$text=preg_replace("/>/","&gt;",$text);
	$text=preg_replace("/&beta;/","Beta",$text);
	$text=preg_replace("/&micro;/","micro",$text);
	$text=preg_replace("/\n/","&lt;br&gt;",$text);
	
	$text=replace_accent($text);
#	$text=preg_replace("/[âà]/i","a",$text);
#	$text=preg_replace("/[éèêë]/i","e",$text);
#	$text=preg_replace("/[îï]/i","i",$text);
#	$text=preg_replace("/[ôö]/i","i",$text);
#	$text=preg_replace("/[ùû]/i","u",$text);
#	$text=preg_replace("/[ç]/i","c",$text);
#	$text=preg_replace("/[ÂÀ]/i","a",$text);
#	$text=preg_replace("/[ÉÈÊË]/i","e",$text);
#	$text=preg_replace("/[ÎÏ]/i","i",$text);
#	$text=preg_replace("/[ÔÖ]/i","i",$text);
#	$text=preg_replace("/[ÙÛ]/i","u",$text);
#	$text=preg_replace("/[Ç]/i","c",$text);
	$text=preg_replace("/\*/","etoile",$text);
	$text=preg_replace("//"," ",$text);
	$text=preg_replace("/\r/"," ",$text);
	$text=preg_replace("/²/","2",$text);
	$text= str_replace("Æ","ae",$text);
	$text= str_replace("Œ","oe",$text);
	$text= str_replace("æ","ae",$text);
	$text= str_replace("œ","oe",$text);
	$text=preg_replace("/ [\x3F] /"," ",$text);
	$text=preg_replace("/[\xBF]/"," ",$text);
	$text=preg_replace("/±/","+",$text);
	$text=preg_replace("/°/","o",$text);
	$text=preg_replace("/&lt;tr&gt;&lt;td&gt;[^a-z0-9]+&lt;\/td&gt;&lt;\/tr&gt;/i","&lt;tr&gt;&lt;td&gt; &lt;/td&gt;&lt;/tr&gt;",$text);
	return $text;
}

function replace_accent($text) {
    $accent=  'ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËéèêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ';
    $noaccent='AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn';
    $text = strtr($text,$accent,$noaccent);
    return $text;
}

function affiche_liste_user_cohorte($cohort_num,$user_num) {
	global $dbh,$tableau_cohorte_droit;
	if ($cohort_num!='') {
		$autorisation_cohorte_modifier=autorisation_cohorte_modifier ($cohort_num,$user_num);
		$autorisation_cohorte_supprimer=autorisation_cohorte_supprimer ($cohort_num,$user_num);
		$autorisation_cohorte_voir_patient_nominative_global=autorisation_cohorte_voir_patient_nominative_global ($cohort_num,$user_num);
		
		
		$sel_datamart=oci_parse($dbh,"select datamart_num as num_datamart_cohort, user_num as user_num_creation from dwh_cohort where cohort_num=$cohort_num ");
	        oci_execute($sel_datamart);
		$r=oci_fetch_array($sel_datamart,OCI_RETURN_NULLS+OCI_ASSOC);
	        $num_datamart_cohort=$r['NUM_DATAMART_COHORT'];
	        $user_num_creation=$r['USER_NUM_CREATION'];
		
		if ($num_datamart_cohort=='') {
			$num_datamart_cohort=0;
		}
		print "<table border=\"0\" id=\"id_tableau_user_cohorte\" class=\"tablefin\" cellpadding=\"3\">
			<tr><th></th>
			";
			if ($num_datamart_cohort==0) {
				foreach ($tableau_cohorte_droit as $right ) {
					if ($_SESSION['dwh_droit_'.$right.'0']=='ok') {
						if ($right=='nominative' && $autorisation_cohorte_voir_patient_nominative_global=='ok' || $right!='nominative') {
							print "<th>$right</th>";
						}
					}
				}
			} else {
			
				$sel=oci_parse($dbh,"select right from dwh_datamart_user_right where datamart_num=$num_datamart_cohort and user_num=$user_num  ");
				oci_execute($sel);
				while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
					$right=$r['RIGHT'];
					if ($right=='nominative' && $autorisation_cohorte_voir_patient_nominative_global=='ok' || $right!='nominative') {
						print "<th>$right</th>";
					}
				}
			}
			if ($autorisation_cohorte_modifier=='ok') {
				print "<th>X</th>";
			}
			print "</tr>";
		
	        $sel_user=oci_parse($dbh,"select distinct dwh_cohort_user_right.user_num,lastname,firstname from dwh_cohort_user_right,dwh_user where cohort_num=$cohort_num and dwh_user.user_num=dwh_cohort_user_right.user_num");
	        oci_execute($sel_user);
	        while ($r_user=oci_fetch_array($sel_user,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$lastname_user=$r_user['LASTNAME'];
			$firstname_user=$r_user['FIRSTNAME'];
			$user_num_cohort=$r_user['USER_NUM'];
			$tableau_droit_user=array();
	                $sel_droit=oci_parse($dbh,"select distinct right from dwh_cohort_user_right  where cohort_num=$cohort_num and user_num=$user_num_cohort");
	                oci_execute($sel_droit);
	                while ($r_droit=oci_fetch_array($sel_droit,OCI_RETURN_NULLS+OCI_ASSOC)) {
				$tableau_droit_user[$r_droit['RIGHT']]='ok';
				
	               	}
			print "<tr><td><strong> $lastname_user $firstname_user</strong></td>";
			
			if ($num_datamart_cohort==0) {
				foreach ($tableau_cohorte_droit as $right ) {
					if ($_SESSION['dwh_droit_'.$right.'0']=='ok') {
						if ($right=='nominative' && $autorisation_cohorte_voir_patient_nominative_global=='ok' || $right!='nominative') {
							if ($autorisation_cohorte_modifier=='ok') {
								if ($tableau_droit_user[$right]=='ok') {
									$checked='checked';
								} else {
									$checked='';
								}
								print "<td><input id=\"id_droit_".$user_num_cohort."_".$right."\" type=checkbox onclick=\"ajouter_droit_cohorte('$cohort_num','$user_num_cohort','$right');\" $checked></td>";
							} else {
								if ($tableau_droit_user[$right]=='ok') {
									print "<td>x</td>";
								} else {
									print "<td>-</td>";
								}
							}
						}
					}
				}
			} else {
			
				$sel_right=oci_parse($dbh,"select right from dwh_datamart_user_right where datamart_num=$num_datamart_cohort and user_num=$user_num  ");
				oci_execute($sel_right);
				while ($r=oci_fetch_array($sel_right,OCI_RETURN_NULLS+OCI_ASSOC)) {
					$right=$r['RIGHT'];
					if ($right=='nominative' && $autorisation_cohorte_voir_patient_nominative_global=='ok' || $right!='nominative') {
						if ($autorisation_cohorte_modifier=='ok') {
							if ($tableau_droit_user[$right]=='ok') {
								$checked='checked';
							} else {
								$checked='';
							}
							print "<td><input id=\"id_droit_".$user_num_cohort."_".$right."\" type=checkbox onclick=\"ajouter_droit_cohorte('$cohort_num','$user_num_cohort','$right');\" $checked></td>";
						} else {
							if ($tableau_droit_user[$right]=='ok') {
								print "<td>x</td>";
							} else {
								print "<td>-</td>";
							}
						}
					}
				}
			}
			
			
			if ($autorisation_cohorte_modifier=='ok'  && $user_num_creation!=$user_num_cohort) {
				print "<td><img src=\"images/mini_poubelle.png\" onclick=\"supprimer_user_cohorte($user_num_cohort,$cohort_num);\" style=\"cursor:pointer;\"></td>";
			}
			print "</tr>";
	        }
		print "	</table>";
	}
}

function display_user_cohorts_list ($user_num) {
	global $dbh;
	$tab_user_cohorts=get_user_cohorts ($user_num,"");
	foreach ($tab_user_cohorts as $cohort_num) {
		$cohort=get_cohort($cohort_num,'');
		$title_cohort=$cohort['TITLE_COHORT'];
         	print "<li class=\"cohorte\"><a href=\"mes_cohortes.php?cohort_num_voir=$cohort_num\">$title_cohort</a></li>";
        }
}

function display_user_cohorts_table ($user_num) {
	global $dbh;
	print "<table border=\"0\" class=\"dataTable\" id=\"id_tableau_liste_cohortes\"><thead><tr><td></td><td>Title</td><td>Date</td><td>Inclus</td></tr></thead><tbody>";
	$tab_user_cohorts=get_user_cohorts ($user_num,"");
	foreach ($tab_user_cohorts as $cohort_num) {
		$cohort=get_cohort($cohort_num,'nb_patients');
		$title_cohort=$cohort['TITLE_COHORT'];
		$cohort_date=$cohort['COHORT_DATE'];
		$nb_inclu=$cohort['NB_PATIENT_COHORTE'];
         	print "<tr>
	         	<td><img src=\"images/mini_cohorte.png\"></td><td class=\"cohorte lien\" onclick=\"document.location.href='mes_cohortes.php?cohort_num_voir=$cohort_num'\" style=\"cursor:pointer\">$title_cohort</td>
	         	<td class=\"cohorte lien\" onclick=\"document.location.href='mes_cohortes.php?cohort_num_voir=$cohort_num'\" style=\"cursor:pointer\">$cohort_date</td>
	         	<td class=\"cohorte lien\" onclick=\"document.location.href='mes_cohortes.php?cohort_num_voir=$cohort_num'\" style=\"cursor:pointer\">$nb_inclu</td>
         	</tr>";
        }
        print "</tbody></table>";
}


function display_user_cohorts_option ($user_num,$id_option) {
#function lister_mes_cohortes_option ($user_num,$id_option) {
	global $dbh;
	print "<option value=''></option>";
	
	$tab_user_cohorts=get_user_cohorts ($user_num,"");
	foreach ($tab_user_cohorts as $cohort_num) {
		$cohort=get_cohort($cohort_num,'');
		$title_cohort=$cohort['TITLE_COHORT'];
         	print "<option value=\"$cohort_num\" id=\"".$id_option."_$cohort_num\">$title_cohort</option>";
        }
}

function display_user_cohort_javascript ($user_num) {
	global $dbh;
	$tab_user_cohorts=get_user_cohorts ($user_num,"");
	foreach ($tab_user_cohorts as $cohort_num) {
		$cohort=get_cohort($cohort_num,'');
		$title_cohort=$cohort['TITLE_COHORT'];
         	print "$cohort_num;$title_cohort;separateur;";
        }
}

function display_user_cohorts_option_addright ($user_num) {
#function lister_mes_cohortes_ajouter_patient ($user_num) {
	global $dbh;
	print "<option value=''></option>";
	$tab_user_cohorts=get_user_cohorts ($user_num," and right='add_patient'");
	foreach ($tab_user_cohorts as $cohort_num) {
		$cohort=get_cohort($cohort_num,'');
		$title_cohort=$cohort['TITLE_COHORT'];
         	print "<option value=\"$cohort_num\">$title_cohort</option>";
        }
}



function lister_cohorte_un_patient ($patient_num) {
	global $dbh;
	print "<ul>";
        $sel_pat=oci_parse($dbh,"select distinct cohort_num,status,add_date from dwh_cohort_result where patient_num=$patient_num and status!=3 order by add_date desc");
        oci_execute($sel_pat);
        while ($r=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC)) {
        	$status=$r['STATUS'];	
        	$cohort_num=$r['COHORT_NUM'];
        	if ($status=='0') {
        		$statut_libelle=get_translation('EXCLUDED_SINGULAR','Exclu');
        		$img="";
        	}
        	if ($status=='1') {
        		$statut_libelle=get_translation('INCLUDED_SINGULAR','Inclu');
        	}			    		
        	if ($status=='2') {
        		$statut_libelle=get_translation('AWAITING','En attente');
        	}
        	if ($status=='3') {
        		$statut_libelle=get_translation('IMPORTED','Importé');
        	}
        	$r_cohort=get_cohort($cohort_num,'');
		$title_cohort=$r_cohort['TITLE_COHORT'];
		$user_num_creation=$r_cohort['USER_NUM'];
		if ($user_num_creation!='') {
			$createur=get_user_information ($user_num_creation,'pn');
		}
		list($add_date,$num_user_ajout,$user_name_ajout)=lister_info_cohorte_patient ($patient_num,$cohort_num);
		print "<li class=\"cohorte_$status\"> ".get_translation('PATIENT','Patient')." <strong>$statut_libelle</strong> ".get_translation('THE_DATE','le')." $add_date ".get_translation('BY_FOLLOWED_BY_NAME','par')." $user_name_ajout ".get_translation('IN_THE_COHORT','Dans la cohorte')." <strong> $title_cohort</strong> ".get_translation('COHORT_CREATED_BY','crée par')." $createur</li>";
        }
	print "</ul>";
}



function lister_info_cohorte_patient ($patient_num,$cohort_num) {
	global $dbh;
	if ($cohort_num!='') {
	        $sel_pat=oci_parse($dbh,"select to_char(add_date,'DD/MM/YYYY HH24:MI') as add_date,user_num_add from dwh_cohort_result where patient_num=$patient_num and cohort_num=$cohort_num");
	        oci_execute($sel_pat);
		$r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC);
		$add_date=$r_pat['ADD_DATE'];	
		$user_ajout=$r_pat['USER_NUM_ADD'];
		$user_name_ajout=get_user_information($user_ajout,'pn');
		return array($add_date,$user_ajout,$user_name_ajout);
	}
}

function afficher_cohorte_nb_patient_statut ($cohort_num,$user_num) {
	global $dbh;
	 $autorisation_voir_patient_cohorte=verif_autorisation_voir_patient_cohorte($cohort_num,$user_num);
	$sel=oci_parse($dbh,"select count(*) as nb_inclu from dwh_cohort_result where   cohort_num=$cohort_num and  status=1 ");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb_inclu=$r['NB_INCLU'];
	$sel=oci_parse($dbh,"select count(*) as nb_exclu from dwh_cohort_result where   cohort_num=$cohort_num and  status=0 ");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb_exclu=$r['NB_EXCLU'];
	$sel=oci_parse($dbh,"select count(*) as nb_doute from dwh_cohort_result where   cohort_num=$cohort_num and  status=2 ");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb_doute=$r['NB_DOUTE'];
	$sel=oci_parse($dbh,"select count(*) as nb_import from dwh_cohort_result where   cohort_num=$cohort_num and  status=3 ");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb_import=$r['NB_IMPORT'];
	return "$nb_inclu;$nb_exclu;$nb_doute;$nb_import";
}


function supprimer_apost($text) {
 	$text=preg_replace("/'/","''",$text);
 	$text=preg_replace("/\"/"," ",$text);
	return $text;
}
 

function get_user_information ($user_num,$ordre) {
	global $dbh;
	if ($user_num!='') {
		$sel_user=oci_parse($dbh,"select lastname,firstname from dwh_user where user_num=$user_num");
		oci_execute($sel_user);
		$r_user=oci_fetch_array($sel_user,OCI_RETURN_NULLS+OCI_ASSOC);
		$lastname=$r_user['LASTNAME'];
		$firstname=$r_user['FIRSTNAME'];
		if ($ordre=='pn') {
		        return "$firstname $lastname";
		}
		if ($ordre=='np') {
		        return "$lastname $firstname";
		}
	}
}

           
 function rechercher_examen_thesaurus ($requete_texte,$num_filtre,$thesaurus_code,$thesaurus_data_father_num,$sans_filtre) {
	global $dbh;
	$requete_texte=nettoyer_pour_requete(trim($requete_texte));
	if ($requete_texte!='') {
		$requete_texte=preg_replace("/\s+/"," ",$requete_texte);
		$requete_texte_sans_pourcent=preg_replace("/\s/"," and ",$requete_texte);
		$requete_texte_avec_pourcent=preg_replace("/\s/","% and ",$requete_texte);
		
		$compteur=0;
		
		if (preg_match("/^[0-9]+$/",$requete_texte)) {
			$req_num_thesaurus=" or thesaurus_data_num='$requete_texte' ";
		} else {
			$req_num_thesaurus="";
		}
		
		if ($sans_filtre=='') {
			$req="select distinct a.thesaurus_data_num,concept_code,concept_str,info_complement,measuring_unit,count_data_used ,value_type
			from dwh_thesaurus_data a,
	           dwh_thesaurus_data_graph b
	           where 
	            a.thesaurus_code='$thesaurus_code' and  
	           a.thesaurus_code=b.thesaurus_code and 
	           a.thesaurus_data_num=b.thesaurus_data_son_num and 
	           b.thesaurus_data_father_num=$thesaurus_data_father_num  and 
	           distance=1  and 
	           ( ( contains(description,'$requete_texte_avec_pourcent%')>0 or contains(description,'$requete_texte')>0    $req_num_thesaurus )
	            or a.concept_code='$requete_texte'
	           or a.thesaurus_data_num in (select thesaurus_data_father_num from dwh_thesaurus_data a, dwh_thesaurus_data_graph b where    a.thesaurus_code='$thesaurus_code' and  
	           a.thesaurus_code=b.thesaurus_code and a.thesaurus_data_num=b.thesaurus_data_son_num and    ( contains(description,'$requete_texte_avec_pourcent%')>0 or contains(description,'$requete_texte')>0    or a.concept_code='$requete_texte' $req_num_thesaurus ))
	          )
			";
			$selobx=oci_parse($dbh,"$req");
		} else {
			$selobx=oci_parse($dbh,"select distinct a.thesaurus_data_num,concept_code,concept_str,info_complement,measuring_unit,count_data_used ,value_type
			from dwh_thesaurus_data a,
	           dwh_thesaurus_data_graph b
	           where 
	            a.thesaurus_code='$thesaurus_code' and  
	           a.thesaurus_code=b.thesaurus_code and 
	           a.thesaurus_data_num=b.thesaurus_data_son_num and 
	           b.thesaurus_data_father_num=$thesaurus_data_father_num  and 
	           distance=1 ");
		}
		oci_execute($selobx);
		while ($r_varobx=oci_fetch_array($selobx)) {
			if ($r_varobx) {
				foreach ($r_varobx as $var => $val) {
					$var=strtolower($var);
					$$var=$val;
				}
			}
			
			$compteur++;
			
			// verif si libelle contient fils
			$sel=oci_parse($dbh,"select 1 as test_fils from dwh_thesaurus_data where thesaurus_parent_num=$thesaurus_data_num");
			oci_execute($sel);
			$r=oci_fetch_array($sel);
			$test_fils=$r['TEST_FILS'];
			
			if ($test_fils==1) {
				// verif si libelle contient element recherche. Si oui, on affiche tous les fils de l element, sans filtre
				
				if ($sans_filtre=='') {
					$sel=oci_parse($dbh,"select 1 as test_fils from dwh_thesaurus_data where thesaurus_data_num=$thesaurus_data_num and (contains(description,'$requete_texte_avec_pourcent%')>0 or contains(description,'$requete_texte_sans_pourcent%')>0  or concept_code='$requete_texte' )");
					oci_execute($sel);
					$r=oci_fetch_array($sel);
					$test_terme_rech_dans_libelle=$r['TEST_FILS'];
				}
				
				if ($test_terme_rech_dans_libelle==1 || $sans_filtre!='') {
					print "<div id=\"id_div_thesaurus_data_concept_$thesaurus_data_num\" style=\"display:block;cursor:pointer;font-size: 12px;\" onclick=\"rechercher_code_sous_thesaurus ($num_filtre,$thesaurus_data_num,'sans_filtre');\">";
				} else {
					print "<div id=\"id_div_thesaurus_data_concept_$thesaurus_data_num\" style=\"display:block;cursor:pointer;font-size: 12px;\" onclick=\"rechercher_code_sous_thesaurus ($num_filtre,$thesaurus_data_num,'');\">";
				}
				
				print "<span id=\"plus_id_div_thesaurus_sous_data_".$num_filtre."_".$thesaurus_data_num."\">+</span>";
				if ($value_type!='parent' && $value_type!='') {
					print " <input type=\"radio\" name=\"radio_ajouter_formulaire_code_$num_filtre\" onclick=\"ajouter_formulaire_code($num_filtre,$thesaurus_data_num);\">";
				} 
				print" $concept_str";
				if ($measuring_unit!='') { 
					print" ($measuring_unit)";
				}
				if ($info_complement!='') { 
					print"$info_complement";
				}
				print " ($count_data_used) ";
				print "
				</div>
				<div id=\"id_div_thesaurus_sous_data_".$num_filtre."_".$thesaurus_data_num."\" style=\"display:none;padding-left:15px;\">
				</div>";
			} else {
				print "
				<div id=\"id_div_thesaurus_data_concept_$thesaurus_data_num\" style=\"display:block;font-size: 12px;\"> ";
				if ($value_type!='parent' && $value_type!='') {
					print "<input type=\"radio\" name=\"radio_ajouter_formulaire_code_$num_filtre\" onclick=\"ajouter_formulaire_code($num_filtre,$thesaurus_data_num);\">";
				} 
				print " $concept_str";
				if ($measuring_unit!='') { 
					print" ($measuring_unit)";
				}
				if ($info_complement!='') { 
					print"$info_complement";
				}
				print " ($count_data_used) ";
				print "
				</div>
				";
			}
			
		}
		
		if ($compteur==0) {
			print "Aucun résultat trouvé";
		}
	}
}

function get_data_concept_str($thesaurus_data_num) {
	global $dbh;
	
	$requete="select concept_str from dwh_thesaurus_data where thesaurus_data_num=$thesaurus_data_num  ";
	$sel=oci_parse($dbh,$requete);
	oci_execute($sel) || die ("erreur requete $requete\n");
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$concept_str=$r['CONCEPT_STR'];
	
	return $concept_str;
}


function afficher_etat_entrepot($option,$width,$req_document_origin_code,$an_mois_min,$an_mois_max) {
	global $dbh,$tableau_global_document_origin_code,$tableau_couleur;
	if ($option=='document_origin_code') {
		$requete="select sum(count_patient) count_patient,sum(count_document) count_document from  dwh_info_load where document_origin_code is null";
		$sel=oci_parse($dbh,$requete);
		oci_execute($sel) || die ("erreur requete $requete\n");
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$nb_documents_total= number_format($r['COUNT_DOCUMENT'], 0, ',', ' ');
		$count_patient_total=number_format($r['COUNT_PATIENT'], 0, ',', ' ');
		
		$liste_nb_doc_document_origin_code="";
		foreach ($tableau_global_document_origin_code as $document_origin_code => $document_origin_str) {
			$requete="select document_origin_code,sum(count_patient) count_patient,sum(count_document) count_document from  dwh_info_load where document_origin_code='$document_origin_code' and month is null group by document_origin_code";
			$sel=oci_parse($dbh,$requete);
			oci_execute($sel) || die ("erreur requete $requete\n");
			while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
				$document_origin_code=$r['DOCUMENT_ORIGIN_CODE'];
				$tab_count_patient[$document_origin_code]=$r['COUNT_PATIENT'];
				$tab_nb_documents[$document_origin_code]=$r['COUNT_DOCUMENT'];
				$count_document=$r['COUNT_DOCUMENT'];
				$liste_nb_doc_document_origin_code.="['$document_origin_str',   $count_document],";
			}
		}
		$liste_nb_doc_document_origin_code=substr($liste_nb_doc_document_origin_code,0,-1);
		
		print get_translation('A_TOTAL_OF','Un total de')." $count_patient_total ".get_translation('PATIENTS','patients')." ".get_translation('AND','et')." $nb_documents_total ".get_translation('DOCUMENTS','documents');
		print "<div id=\"id_afficher_etat_entrepot_document_origin_code\" style=\"width:$width;height:300px;\"></div>
		<script language=\"javascript\">
		$(function () {
		    $('#id_afficher_etat_entrepot_document_origin_code').highcharts({
		        chart: {
		            plotBackgroundColor: null,
		            plotBorderWidth: null,
		            plotShadow: false
		        },
			credits: {
				enabled: false
			},
		        title: {
		            text: '".get_translation('JS_COUNT_DOCUMENTS_PER_ORIGIN','Nombre de documents par origine du document')."'
		        },
		        tooltip: {
		            pointFormat: '{series.name}: <b>{point.y}</b>'
		        },
		        plotOptions: {
		            pie: {
		                allowPointSelect: true,
		                cursor: 'pointer',
		                dataLabels: {
		                    enabled: true,
		                    format: '<b>{point.name}</b>: {point.y}',
		                    style: {
		                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
		                    }
		                }
		            }
		        },
		        series: [{
		            type: 'pie',
		            name: 'Nb docs',
		            data: [
		                $liste_nb_doc_document_origin_code
		            ]
		        }]
		    });
		});
		</script>
		";
	}
	
	if ($option=='document_origin_code_an') {
		$liste_nb_doc=array();
		$requete="select year,document_origin_code,sum(count_document) count_document from  dwh_info_load where document_origin_code is not null and year is not null and year>1995  and year <=to_char(sysdate,'YYYY') and month is null group by year,document_origin_code order by document_origin_code,year";
		$sel=oci_parse($dbh,$requete);
		oci_execute($sel) || die ("erreur requete $requete\n");
		while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$document_origin_code=$r['DOCUMENT_ORIGIN_CODE'];
			$year=$r['YEAR'];
			$count_document=$r['COUNT_DOCUMENT'];
			$liste_nb_doc[$document_origin_code].="[Date.UTC($year,0,1), $count_document],";
		}
		foreach ($liste_nb_doc as $document_origin_code => $liste) {
			$liste=substr($liste,0,-1);
			$series.="{yAxis: 0,
				name: \"$document_origin_code\",
				type: 'column',
				data: [$liste]
				},";
		}
		$series=substr($series,0,-1);
		print "
		<div id=\"id_afficher_etat_entrepot_document_origin_code_par_an\" style=\"width:$width;height:600px;\"></div>
		<script language=\"javascript\">
		jQuery(function () {
			var chart;
			jQuery(document).ready(function() {
		    	$('#id_afficher_etat_entrepot_document_origin_code_par_an').highcharts({
					chart: {
						type: 'column',
						polar: false,
						zoomType: 'x'
					},
					credits: {
						enabled: false
					}
					,plotOptions: {
						series: {
							stacking: 'normal'
						}
					},
					title: {
						text: \"".get_translation('JS_COUNT_DOCUMENTS_PER_YEAR','Nb de documents par an')."\"
					},
					subtitle: {
						text: ''
					},
					xAxis: {
						type: 'datetime',
						dateTimeLabelFormats: {
							day: '%Y'
						},
						labels: {
							rotation: 90,
							align: 'left'
						}
					},
					yAxis : [{
						labels: {
							formatter: function() {
								return this.value +'Nb';
							},
							style: {
								color: '#2F7ED8'
							}
						},
						title: {
							text: \"".get_translation('JS_COUNT_DOCUMENTS','Nb documents')."\",
							style: {
								color: '#2F7ED8'
							}
						},
						opposite: false
					}],
					
					series: [$series]
				});
			});
		});
		</script>
		";
	}
	if ($option=='document_origin_code_an_mois') {
		$liste_nb_doc=array();
		$requete="select year,month,document_origin_code,sum(count_document) count_document from  dwh_info_load where document_origin_code is not null and year is not null and year>1995  and year <=to_char(sysdate,'YYYY') and month is not null group by year,month,document_origin_code order by document_origin_code,year";
		$sel=oci_parse($dbh,$requete);
		oci_execute($sel) || die ("erreur requete $requete\n");
		while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$document_origin_code=$r['DOCUMENT_ORIGIN_CODE'];
			$year=$r['YEAR'];
			$month=$r['MONTH']-1;
			$count_document=$r['COUNT_DOCUMENT'];
			$liste_nb_doc[$document_origin_code].="[Date.UTC($year,$month), $count_document],";
		}
		
		foreach ($liste_nb_doc as $document_origin_code => $liste) {
			$liste=substr($liste,0,-1);
			$series.="{yAxis: 0,
				name: \"$document_origin_code\",
				type: 'column',
				data: [$liste]
				},";
		}
		$series=substr($series,0,-1);
		print "
		<div id=\"id_afficher_etat_entrepot_document_origin_code_par_an_mois\" style=\"width:$width;height:600px;\"></div>
		<script language=\"javascript\">
		jQuery(function () {
			var chart;
			jQuery(document).ready(function() {
		    	$('#id_afficher_etat_entrepot_document_origin_code_par_an_mois').highcharts({
					chart: {
						type: 'column',
						polar: false,
						zoomType: 'x'
					},
					credits: {
						enabled: false
					}
					,plotOptions: {
						series: {
							stacking: 'normal'
						}
					},
					title: {
						text: \"".get_translation('JS_COUNT_DOCUMENTS_PER_MONTH','Nb de documents par mois')."\"
					},
					subtitle: {
						text: ''
					},
					xAxis: {
						type: 'datetime',
						dateTimeLabelFormats: {
							day: '%Y'
						},
						labels: {
							rotation: 90,
							align: 'left'
						}
					},
					yAxis : [{
						labels: {
							formatter: function() {
								return this.value +'Nb';
							},
							style: {
								color: '#2F7ED8'
							}
						},
						title: {
							text: \"".get_translation('JS_COUNT_DOCUMENTS','Nb documents')."\",
							style: {
								color: '#2F7ED8'
							}
						},
						opposite: false
					}],
					
					series: [$series]
				});
			});
		});
		</script>
		";
	}
	
	if ($option=='document_origin_code_an_mois_unitaire' && $req_document_origin_code!='') {
		$liste_nb_doc='';
		$ajouter_min='';
		$ajouter_max='';
		$requete="select year,month,sum(count_document) count_document from  dwh_info_load where document_origin_code ='$req_document_origin_code' and year is not null and year>1995  and year <=to_char(sysdate,'YYYY') and month is not null group by year,month,document_origin_code order by year,month";
		$sel=oci_parse($dbh,$requete);
		oci_execute($sel) || die ("erreur requete $requete\n");
		while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$year=$r['YEAR'];
			$month=$r['MONTH']-1;
			$count_document=$r['COUNT_DOCUMENT'];
			$liste_nb_doc.="[Date.UTC($year,$month), $count_document],";
			if ($an_mois_min!="$year,$month" && $an_mois_min!='') {
				$ajouter_min='ok';
			}
			if ($an_mois_max!="$year,$month" && $an_mois_max!='') {
				$ajouter_max='ok';
			}
		}
		if ($ajouter_min=='ok') {
			$liste_nb_doc.="[Date.UTC($an_mois_min), 0],";
		}
		if ($ajouter_max=='ok') {
			$liste_nb_doc.="[Date.UTC($an_mois_max), 0],";
		}
		$document_origin_str=$tableau_global_document_origin_code[$req_document_origin_code];
		$liste_nb_doc=substr($liste_nb_doc,0,-1);
		$series="{yAxis: 0,
			name: \"$document_origin_str\",
			type: 'column',
			data: [$liste_nb_doc]
			}";
		$document_origin_code=preg_replace ("/ /","_",$req_document_origin_code);
		print "<hr>
		<div id=\"id_afficher_etat_entrepot_document_origin_code_par_an_$document_origin_code\" style=\"width:$width;height:600px;\"></div>
		<script language=\"javascript\">
		jQuery(function () {
			var chart;
			jQuery(document).ready(function() {
		    	$('#id_afficher_etat_entrepot_document_origin_code_par_an_$document_origin_code').highcharts({
					chart: {
						type: 'column',
						polar: false,
						zoomType: 'x'
					},
					credits: {
						enabled: false
					}
					,plotOptions: {
						series: {
							stacking: 'normal'
						}
					},
					legend: {
						enabled: false
					},
					title: {
						text: \"".get_translation('JS_COUNT_DOCUMENTS_PER_MONTH','Nb de documents par mois')." - $document_origin_str\"
					},
					subtitle: {
						text: ''
					},
					xAxis: {
						type: 'datetime',
						dateTimeLabelFormats: {
							day: '%Y'
						},
						labels: {
							rotation: 90,
							align: 'left'
						}
					},
					yAxis : [{
						labels: {
							formatter: function() {
								return this.value +'Nb';
							},
							style: {
								color: '#2F7ED8'
							}
						},
						title: {
							text: \"".get_translation('JS_COUNT_DOCUMENTS','Nb documents')."\",
							style: {
								color: '#2F7ED8'
							}
						},
						opposite: false
					}],
					
					series: [$series]
				});
			});
		});
		</script>
		";
	}
	
	
	if ($option=='document_origin_code_an_mois_presence') {
		$max_annee=2010;
		$min_annee=2010;
		$tableau_document_origin_code_nb=array();
		$requete="select year,month,document_origin_code,count_document from  dwh_info_load where document_origin_code is not null and year is not null and year>1995 and year <=to_char(sysdate,'YYYY') and month is not null order by year,month";
		$sel=oci_parse($dbh,$requete);
		oci_execute($sel) || die ("erreur requete $requete\n");
		while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$document_origin_code=$r['DOCUMENT_ORIGIN_CODE'];
			$year=$r['YEAR'];
			$month=$r['MONTH'];
			$count_document=$r['COUNT_DOCUMENT'];
			$tableau_document_origin_code[$document_origin_code][$year][$month]='ok';
			$tableau_document_origin_code_nb[$document_origin_code]+=0+$count_document;
			if ($min_annee>$year) {
				$min_annee=$year;
			}
			if ($max_annee<$year) {
				$max_annee=$year;
			}
		}
		$requete="select year,month,document_origin_code,count_document from  dwh_info_load where document_origin_code is not null and year is not null and year>1995  and year <=to_char(sysdate,'YYYY') and month is not null order by year,month";
		$sel=oci_parse($dbh,$requete);
		oci_execute($sel) || die ("erreur requete $requete\n");
		while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$document_origin_code=$r['DOCUMENT_ORIGIN_CODE'];
			$year=$r['YEAR'];
			$month=$r['MONTH'];
			$count_document=$r['COUNT_DOCUMENT'];
			$tableau_document_origin_code[$document_origin_code][$year][$month]='ok';
			if ($min_annee>$year) {
				$min_annee=$year;
			}
			if ($max_annee<$year) {
				$max_annee=$year;
			}
		}
		$i=0;
		print "<h3 style=\"color: #333333;fill: #333333;font-size: 18px;font-family:Lucida Sans Unicode;font-weight:normal;\">".get_translation('THE_PRESENT_DOCUMENTS','Les origines des documents présents')."</h3>";
		print "<table border=0 cellspacing=0 cellpadding=0>";
		print "<tr><th style=\"border:0px;background-color:white\"></th>";
		for ($an=$min_annee;$an<=$max_annee;$an++) {
			print "<td colspan=12 style=\"font-size:8px;\" class=\"vertical_text\">$an</td>";
		}
		print "<td style=\"font-size:10px;\">Total</td>";
		print "</tr>";
		foreach ($tableau_global_document_origin_code as $document_origin_code => $document_origin_str) {
			
		
			print "<tr>
				<td style=\"border:0px;background-color:white\" nowrap=\"nowrap\">$document_origin_str</td>
				";
			$color=$tableau_couleur[$i];
			if ($color=='') {
				$i=0;
				$color=$tableau_couleur[$i];
			}
			$i++;
			for ($an=$min_annee;$an<=$max_annee;$an++) {
				for ($mois=1;$mois<=12;$mois++) {
					if ($mois==12) {
						$style_border="border-right:1px grey dotted;";
					} else {
						$style_border="border-right:0px;";
					}
					
					if ($tableau_document_origin_code[$document_origin_code][$an][$mois]=='ok') {
						print "<td alt=\"$an\" title=\"$an\" style=\"width:1px;border:0px;background-color:$color;$style_border\"></td>";
					} else {
						print "<td alt=\"$an\" title=\"$an\" style=\"width:1px;border:0px;background-color:white;$style_border\"></td>";
					}
				}
			}
			print "<td style=\"font-size:10px;\">".$tableau_document_origin_code_nb[$document_origin_code]."</td>";
			print "</tr>";
		}
		print "</table>";
	}
	
	
	if ($option=='last_chargement') {
		$tableau_document_origin_code=array();
		print "<table>";
		$requete="select  document_origin_code,to_char(last_execution_date,'DD/MM/YYYY') as char_last_execution_date,count_document,last_execution_date from dwh_etl_script where count_document>0 and document_origin_code is not null order by last_execution_date desc";
		$sel=oci_parse($dbh,$requete);
		oci_execute($sel) || die ("erreur requete $requete\n");
		while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$document_origin_code=$r['DOCUMENT_ORIGIN_CODE'];
			$count_document=$r['COUNT_DOCUMENT'];
			$char_last_execution_date=$r['CHAR_LAST_EXECUTION_DATE'];
			if ($tableau_document_origin_code[$document_origin_code]=='') {
				$tableau_document_origin_code[$document_origin_code]='ok';
				$document_origin_str=$tableau_global_document_origin_code[$document_origin_code];
				if ($document_origin_str=='') {
					$document_origin_str=$document_origin_code;
				}
				print "<tr><td>$char_last_execution_date</td><td>$document_origin_str</td><td>$count_document ".get_translation('DOCUMENTS_LOADED','documents chargés')."</td></tr>";
			}
		}
		print "</table>";
	}
	
	
	
	
}


function last_connexion ($user_num) {
	global $dbh,$login_session;
	
	print "<b>".get_translation('4_LAST_CONNEXION','4 dernières connexions')." :</b><br>";
	$sel=oci_parse($dbh,"select to_char(log_date,'DD/MM/YYYY HH24:MI') as log_date_char,rownum from (select log_date from dwh_log_page where page='connexion' and user_num=$user_num order by log_date desc) where rownum<5");
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$log_date_char=$r['LOG_DATE_CHAR'];
		print "Le $log_date_char<br>";
	}
	print "<br>";
}


function last_connexion_date_user ($user_num) {
	global $dbh;
	
	$sel=oci_parse($dbh,"select to_char(log_date,'DD/MM/YYYY HH24:MI') as log_date_char,log_date from dwh_log_page where page='connexion' and user_num=$user_num order by log_date desc ");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$log_date_char=$r['LOG_DATE_CHAR'];
	return  "$log_date_char";
}


function last_click_date_user ($user_num) {
	global $dbh;
	
	$sel=oci_parse($dbh,"select to_char(log_date,'DD/MM/YYYY HH24:MI') as log_date_char,log_date from dwh_log_page where user_num=$user_num order by log_date desc ");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$log_date_char=$r['LOG_DATE_CHAR'];
	return  "$log_date_char";
}

function afficher_mes_droits ($user_num) {
	global $dbh;

	$table_label_rights['all_departments']= get_translation('RIGHT_ALL_HOSPITAL_DEPARTEMENTS',"Tous les services");
	$table_label_rights['nominative']=get_translation('RIGHT_NOMINATIVE_DATA',"Données nominatives");
	$table_label_rights['anonymized']=get_translation('RIGHTS_ANONYMIZED_DATA',"Données anonymisées");
	$table_label_rights['admin_directory']=get_translation('RIGHTS_DIRECTORY_ADMINISTRATION',"L'administration de l'annuaire");
	$table_label_rights['admin_datamart']=get_translation('RIGHTS_DATAMART_ADMINISTRATION',"L'administration des datamart");
	$table_label_rights['see_detailed']=get_translation('RIGHTS_RESULT_TAB',"Onglet résultat");
	$table_label_rights['see_genetic']=get_translation('RIGHTS_GENETIC_TAB',"Onglet génétique");
	$table_label_rights['see_stat']=get_translation('RIGHTS_STAT_TAB',"Onglet statistiques"); 
	$table_label_rights['see_concept']=get_translation('RIGHTS_CONCEPTS_TAB',"Onglet concepts"); 
	$table_label_rights['see_drg']=get_translation('RIGHTS_DRG_TAB',"Onglet PMSI");
	$table_label_rights['see_biology']=get_translation('RIGHTS_BIOLOGY_TAB',"Onglet Biologie");
	$table_label_rights['see_map']=get_translation('RIGHTS_MAP_TAB',"Onglet carte");
	$table_label_rights['patient_quick_access']=get_translation('RIGHTS_PATIENT_ACCESS',"Accès rapide au patient");
	$table_label_rights['see_debug']=get_translation('RIGHTS_DEBUG_MODE',"Visualisation du Debug");
	$table_label_rights['search_engine']=get_translation('RIGHTS_SEARCH_ENGINE',"Acces moteur de recherche");
	$table_label_rights['see_clustering']=get_translation('RIGHTS_CLUSTERING_TAB',"Onglet clustering");
	$table_label_rights['modify_patient']=get_translation('RIGHTS_MODIFY_PATIENT_ID',"Modifier patient"); 
	
	print "<b>".get_translation('YOUR_PROFILE','Votre profil')." :</b>";
	$sel=oci_parse($dbh,"select user_profile from dwh_user_profile where user_num='$user_num' ");
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$user_profile=$r['USER_PROFILE'];
		print " $user_profile<br>".get_translation('YOUR_PROFIL_YOUR_RIGHT','Ce profil vous donne accès à')." : <br>";
		$sel_vardroit=oci_parse($dbh,"select right from dwh_profile_right where user_profile='$user_profile'");
		oci_execute($sel_vardroit);
		while ($r_droit=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$right=$r_droit['RIGHT'];
			if ($table_label_rights[$right]!='') {
				print "- ".$table_label_rights[$right]."<br>";
			
			}
			if ($right=='all_departments') {
				$all_departments='ok';
			}
		}
		
	}
	
	if ($all_departments=='') {
		print "<br>";
		//// LES SERVICES DE L'UTILISATEUR //////////
		$liste_service='';
		$table_list_department=get_list_departments('master',$user_num);
		foreach ($table_list_department as $department) {
			$department_str=$department['department_str'];
			$liste_service.="$department_str, ";
		}
		$liste_service=substr($liste_service,0,-2);
		print "<b>".get_translation('AUTHORIZED_HOSPITAL_DEPARTMENTS','Services auxquels vous avez accès')." :</b>$liste_service<br>";
	}
	
	//// LES TYPES DOC DE L'UTILISATEUR //////////
	$liste_document_origin_code='';
	$sel=oci_parse($dbh,"select distinct document_origin_code from dwh_profile_document_origin, dwh_user_profile where user_num='$user_num' and dwh_profile_document_origin.user_profile= dwh_user_profile.user_profile");
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$document_origin_code=$r['DOCUMENT_ORIGIN_CODE'];
		$liste_document_origin_code.="$document_origin_code, ";
	}
	$liste_document_origin_code=substr($liste_document_origin_code,0,-2);
	print "<br><b>".get_translation('AUTHORIZED_DOCUMENT_ORIGINS','Les documents autorisés')." :</b><br>$liste_document_origin_code";
}


function parcours_complet($type_affichage,$tmpresult_num,$cohort_num,$patient_num_local,$unit_or_department,$nb_mini) {
	global $dbh,$CHEMIN_GLOBAL,$CHEMIN_GLOBAL_UPLOAD,$CHEMIN_GLOBAL_LOG,$CHEMIN_GRAPHVIZ,$user_num_session;
	$max_nb=1;
	$max_width=20;
	$patient_num_preced='';
	$tableau_service_distinct=array();
	$tableau_nb_passage_service_service=array();
	$tableau_delais_total_service_service=array();
	if ($unit_or_department=='department') {
		$cle_unit_or_department="department_num";
	}
	if ($unit_or_department=='unit') {
		$cle_unit_or_department="unit_num";
	}
	
	if ($tmpresult_num!='') {
		$req_filtre="  patient_num in (select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num) and ";
	}
	if ($cohort_num!='') {
		$req_filtre="  patient_num in (select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1) and ";
	}
	if ($patient_num_local!='') {
		$req_filtre="  patient_num='$patient_num_local' and ";
	}
	
	$req="select 
			patient_num,
			$cle_unit_or_department as DEPARTMENT_NUM,
			to_char(entry_date,'YYYY-MM-DD') as entry_date_char,
			entry_date,to_char(out_date,'YYYY-MM-DD') as out_date_char,
			out_date,type_mvt,
			to_char(entry_date,'HH24:MI') as ENTRY_HOUR,
			to_char(out_date,'HH24:MI') as OUT_HOUR
		from dwh_patient_mvt 
		where $req_filtre department_num is not null and entry_date is not null
		order by patient_num,entry_date,out_date";
	$sel=oci_parse($dbh,"$req");
	oci_execute($sel) ;
	while ($res=oci_fetch_array($sel,OCI_ASSOC)) {
		$patient_num=$res['PATIENT_NUM'];
		$department_num=$res['TYPE_MVT']."_".$res['DEPARTMENT_NUM'];
		$entry_date=$res['ENTRY_DATE_CHAR'];
		$out_date=$res['OUT_DATE_CHAR'];
		$entry_hour=$res['ENTRY_HOUR_CHAR'];
		$out_hour=$res['OUT_HOUR_CHAR'];
		$type_mvt=$res['TYPE_MVT'];
		if ($type_mv=='U') {
			$type_mv='H';
		}
		if ($entry_hour=='') {
			$entry_hour='12:00';
		}
		if ($out_hour=='') {
			$out_hour='12:00';
		}
		$entry_date="$entry_date $entry_hour:00";
		$out_date="$out_date $out_hour:00";
		
		//$source=$res['SOURCE'];
		if ($patient_num==$patient_num_preced && $department_num_preced!='') {
		
			$d1 = new DateTime("$entry_date");
			$d2 = new DateTime("$out_date_preced");
			$diff = $d2->diff($d1);
			
			$delais_total = $diff->days;
			$tableau_nb_passage_service_service[$department_num_preced.';'.$department_num]++;
			$tableau_service_distinct[$department_num]++;
			if ($delais_total>0) {
				$tableau_delais_total_service_service[$department_num_preced.';'.$department_num]+=$delais_total;
			} else {
				// si le delais est negatif, c'est qu'il y a des consultation au sein du sejour dans un service... radio, reeduc etc...
				// du coup on calcul le delais à partir de la date d'entree dans le service precedent///
				$d1 = new DateTime("$entry_date");
				$d2 = new DateTime("$entry_date_preced");
				$diff = $d2->diff($d1);
				$delais_total = $diff->days;
				if ($delais_total>0) {
					$tableau_delais_total_service_service[$department_num_preced.';'.$department_num]+=$delais_total;
				}
				
				// et retour dans le service d'origine
				$tableau_nb_passage_service_service[$department_num.';'.$department_num_preced]++;
				$department_num=$department_num_preced;
				$out_date=$out_date_preced;
				$entry_date_preced=$entry_date; // on repart d'une date de service à la date de la consultation
			}
		}
		if ($patient_num!=$patient_num_preced && $patient_num_preced!='') {
			$department_num_preced='';
			$entry_date_preced='';
			$out_date_preced='';
		} else {
			$department_num_preced=$department_num;
			$entry_date_preced=$entry_date;
			$out_date_preced=$out_date;
		}
		$patient_num_preced=$patient_num;
	}


	$tableau_service_conserve=array();
	foreach ($tableau_nb_passage_service_service as $service_service => $nb_passage) {
		if ($nb_passage>=$nb_mini) {
			list($department_num_1,$department_num_2)=explode(';',$service_service);
	
			$tableau_service_conserve[$department_num_1]='ok';
			$tableau_service_conserve[$department_num_2]='ok';
			if ($max_nb<$nb_passage) {
				$max_nb=$nb_passage;
			}
		} else {
			unset($tableau_nb_passage_service_service[$service_service]);
		}
	}
	
	if (count($tableau_service_conserve)>0) {
		if ($type_affichage=="dot") {
			$fichier= "	
			digraph G { 
			        bgcolor=white; 
			        node [shape=box]; 
			         node [ fontname=Arial, fontsize=9];
			         edge [ fontname=Helvetica,  fontsize=9 ];
			
			        ";
			foreach ($tableau_service_conserve as $department_num => $ok) {
				$nb_dans_service=$tableau_service_distinct[$department_num];
				
				list($type_mvt,$department_num_reel)=explode('_',$department_num);
				if ($unit_or_department=='department') {
		                	$department_str=get_department_str ($department_num_reel);   
		                }
				if ($unit_or_department=='unit') {
		                	$department_str=get_unit_str ($department_num_reel);   
		                }
				$department_str=str_replace("<"," inf ",$department_str);
				$department_str=str_replace(">"," sup ",$department_str);
				$fichier.= "\"$department_num\" [label=<$department_str ($type_mvt)<BR/>$nb_dans_service passages>];\n";
			}
			
			
			
			foreach ($tableau_nb_passage_service_service as $service_service => $nb_passage) {
				list($department_num_1,$department_num_2)=explode(';',$service_service);
				$delai_total=$tableau_delais_total_service_service[$service_service];
				$delai_moyen=round($delai_total/$nb_passage,0);
		
				$new_nb_passage=round($nb_passage*$max_width/$max_nb,0);
				if ($new_nb_passage==0) {
					$new_nb_passage=1;
				}
		
				$fichier.= "	\"$department_num_1\" -> \"$department_num_2\" [weight=1, penwidth=$new_nb_passage, label=\"$nb_passage, delai moyen $delai_moyen jours \",fontcolor=\"red\"] ;\n";
			}
			$fichier.= "
			}";
			$num_file=$tmpresult_num.$patient_num_local.$unit_or_department.$nb_mini;
			$inF = fopen("$CHEMIN_GLOBAL_UPLOAD/tmp_graphviz_parcours_complet_$num_file.dot","w");
			fputs( $inF,"$fichier");
			fclose($inF);
			
			exec("$CHEMIN_GRAPHVIZ \"$CHEMIN_GLOBAL_UPLOAD/tmp_graphviz_parcours_complet_$num_file.dot\" -Gcharset=latin1 -Tpng -o  \"$CHEMIN_GLOBAL_UPLOAD/tmp_graphviz_parcours_complet_$num_file.png\"");
			$file='';
			if (is_file("$CHEMIN_GLOBAL_UPLOAD/tmp_graphviz_parcours_complet_$num_file.png")) {
				$file=file_get_contents ("$CHEMIN_GLOBAL_UPLOAD/tmp_graphviz_parcours_complet_$num_file.png");
			}
			return $file;
		}
		
		
		if ($type_affichage=="json") {
			$nb_noeud=-1;
			$tableau_list_node=array();
			$tableau_node_deja=array();
			$fichier= "{\"nodes\":[";
			foreach ($tableau_service_conserve as $department_num => $ok) {
				$nb_dans_service=$tableau_service_distinct[$department_num];
				
				list($source,$department_num_reel)=explode('_',$department_num);
				if ($unit_or_department=='department') {
		                	$department_str=get_department_str ($department_num_reel);   
		                }
				if ($unit_or_department=='unit') {
		                	$department_str=get_unit_str ($department_num_reel);   
		                }
				
				$department_str=str_replace("<"," inf ",$department_str);
				$department_str=str_replace(">"," sup ",$department_str);
				if ($tableau_node_deja[$department_num]=='') {
					$nb_noeud++;
					$fichier.= "{\"name\":\"$department_str $source\"},";
					$tableau_list_node[$department_num]=strval($nb_noeud);
					$tableau_node_deja[$department_num]='ok';
				}
				
			}
			$fichier=substr($fichier,0,-1);
			$fichier.= "],\"links\":[";
			
			foreach ($tableau_nb_passage_service_service as $service_service => $nb_passage) {
				list($department_num_1,$department_num_2)=explode(';',$service_service);
				$delai_total=$tableau_delais_total_service_service[$service_service];
				$delai_moyen=round($delai_total/$nb_passage,0);
		
				$new_nb_passage=round($nb_passage*$max_width/$max_nb,0);
				if ($new_nb_passage==0) {
					$new_nb_passage=1;
				}
		
				$t1=$t[0];
				$t2=$t[1];
				$source=$tableau_list_node[$department_num_1];
				$target=$tableau_list_node[$department_num_2];
				if ($source!='' && $target!='') {
					$fichier.= "{\"source\":$source,\"target\":$target,\"value\":$nb_passage},";
				}
			}
			$fichier=substr($fichier,0,-1);
			$fichier.= "]}";	
#			$num_file=$tmpresult_num.$patient_num_local.$unit_or_department.$nb_mini;
#			$inF = fopen("$CHEMIN_GLOBAL_UPLOAD/tmp_d3_complet_json_$num_file.json","w");
#			fputs( $inF,"$fichier");
#			fclose($inF);
			return $fichier;
		}
	}
}

function parcours_sejour_uf ($tmpresult_num,$cohort_num,$patient_num,$patient_num_encounter_num,$nb_mini) {
	global $dbh,$CHEMIN_GLOBAL,$CHEMIN_GLOBAL_UPLOAD,$CHEMIN_GRAPHVIZ,$user_num_session;

	$max_nb=1;
	$max_width=20;

	$liste_total_num_uf='';

	$fichier= "	
	digraph G { 
        bgcolor=white; 
        node [shape=box]; 
         node [ fontname=Arial, fontsize=9];
         edge [ fontname=Helvetica,  fontsize=9 ];

        ";
	$max_nb=1;
	$max_width=20;
	$tableau_parcours=array();
	$tableau_uf_display=array();
	$tableau_mode_display=array();

	$liste_total_num_uf='';
	
	if ($tmpresult_num!='') {
		$req_filtre_a="exists (select encounter_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num and dwh_tmp_result_$user_num_session.$patient_num_encounter_num= a.$patient_num_encounter_num and dwh_tmp_result_$user_num_session.encounter_num is not null) and";
	}
	if ($cohort_num!='') {
		$req_filtre_a="a.patient_num in (select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1) and";
	}
	if ($patient_num!='') {
		$req_filtre_a="a.patient_num=$patient_num and ";
	}
	
	$req="select mvt_entry_mode,  unit_num,count(*) as nb 
		from dwh_patient_mvt a where $req_filtre_a  entry=1 and encounter_num is not null and mvt_entry_mode is not null 
                and type_mvt in ('H' ,'U') and unit_num is not null
		group by  mvt_entry_mode,  unit_num ";
	$sel=oci_parse($dbh,$req);
	oci_execute($sel) ;
	while ($res=oci_fetch_array($sel,OCI_ASSOC)) {
		$entry_mode=$res['MVT_ENTRY_MODE'];
		$nb=$res['NB'];
		$unit_num=$res['UNIT_NUM'];
		
		if ($nb>=$nb_mini) {
			$tableau_parcours["\"$entry_mode\" -> $unit_num"]=$nb;
			if ($tableau_mode_display[$entry_mode]=='') {
				$fichier.= " \"$entry_mode\" [color=\"blue\"];\n";
				$tableau_mode_display[$entry_mode]="ok";
			}
			if ($tableau_uf_display[$unit_num]=='') {
				$tableau_uf_display[$unit_num]=get_unit_str($unit_num,'s');
				$liste_total_num_uf.="$unit_num,";
			}
			if ($max_nb<$nb) {
				$max_nb=$nb;
			}
		}
	}
	
	$req="select mvt_exit_mode,  unit_num,count(*) as nb 
		from dwh_patient_mvt a where $req_filtre_a  out=1 and encounter_num is not null and mvt_exit_mode is not null  
                and type_mvt in ('H' ,'U') and unit_num is not null
		group by  mvt_exit_mode,  unit_num";
	$sel=oci_parse($dbh,$req);
	oci_execute($sel) ;
	while ($res=oci_fetch_array($sel,OCI_ASSOC)) {
		$out_mode=$res['MVT_EXIT_MODE'];
		$nb=$res['NB'];
		$unit_num=$res['UNIT_NUM'];
		
		
		if ($nb>=$nb_mini) {
			$tableau_parcours[" $unit_num -> \"$out_mode\""]=$nb;
			if ($tableau_mode_display[$out_mode]=='') {
				$fichier.= " \"$out_mode\" [color=\"red\"];\n";
				$tableau_mode_display[$out_mode]="ok";
			}
			if ($tableau_uf_display[$unit_num]=='') {
				$tableau_uf_display[$unit_num]=get_unit_str($unit_num,'s');
				$liste_total_num_uf.="$unit_num,";
			}
			if ($max_nb<$nb) {
				$max_nb=$nb;
			}
		}
	}
	
	$req="select a.unit_num as entry_unit_num,b.unit_num as out_unit_num  from dwh_patient_mvt a,dwh_patient_mvt b where
		$req_filtre_a 
                a.patient_num=b.patient_num  and
                a.encounter_num=b.encounter_num and
                a.mvt_order=b.mvt_order-1
                and b.out_date is not null
                and a.out_date is not null
                and a.encounter_num is not null 
                and b.encounter_num is not null 
                and a.unit_num is not null 
                and b.unit_num is not null 
                and a.type_mvt in ('H' ,'U')
                and b.type_mvt in ('H' ,'U')
                ";
	$sel=oci_parse($dbh,$req);
	oci_execute($sel) ;
	while ($res=oci_fetch_array($sel,OCI_ASSOC)) {
		$entry_unit_num=$res['ENTRY_UNIT_NUM'];
		$out_unit_num=$res['OUT_UNIT_NUM'];
		
		$tableau_parcours["$entry_unit_num -> $out_unit_num"]++;
		if ($tableau_parcours["$entry_unit_num -> $out_unit_num"]>=$nb_mini) {
			if ($tableau_uf_display[$entry_unit_num]=='') {
				$tableau_uf_display[$entry_unit_num]=get_unit_str($entry_unit_num,'s');
				$liste_total_num_uf.="$entry_unit_num,";
			}
			if ($tableau_uf_display[$out_unit_num]=='') {
				$tableau_uf_display[$out_unit_num]=get_unit_str($out_unit_num,'s');
				$liste_total_num_uf.="$out_unit_num,";
			}
		}
		if ($max_nb<$tableau_parcours["$entry_unit_num -> $out_unit_num"]) {
			$max_nb=$tableau_parcours["$entry_unit_num -> $out_unit_num"];
		}
	}

	$liste_total_num_uf=substr($liste_total_num_uf,0,-1);
	if ($liste_total_num_uf!='') {
		
		$req="select unit_num ,round(avg(out_date-entry_date),1) as dms from dwh_patient_mvt a where
                $req_filtre_a 
                out_date is not null and
                out_date>=entry_date and
                type_mvt in ('H' ,'U') and
                unit_num in ($liste_total_num_uf) group by unit_num ";
		$sel=oci_parse($dbh,$req);
		oci_execute($sel) ;
		while ($res=oci_fetch_array($sel,OCI_ASSOC)) {
			$unit_num=$res['UNIT_NUM'];
			$dms=$res['DMS'];
			if (preg_match("/^,/",$dms)) {
				$dms="0".$dms;
			}
			$tableau_uf_dms[$unit_num]=$dms;
		}
		foreach ($tableau_uf_display as $unit_num => $unit_str) {
			$unit_str=str_replace("<"," inf ",$unit_str);
			$unit_str=str_replace(">"," sup ",$unit_str);
			$fichier.= "$unit_num [label=<$unit_str, dms ".$tableau_uf_dms[$unit_num].">];\n";
		}

		foreach ($tableau_parcours as $lien => $poids) {
			if ($poids>=$nb_mini) {
				$new_poids=round($poids*$max_width/$max_nb,0);
				if ($new_poids==0) {
					$new_poids=1;
				}
				$fichier.= "	$lien [weight=1, penwidth=$new_poids, label=\"$poids\",fontcolor=\"red\"] ;\n";
			}
		}
		$fichier.= "
		}";	
		$num_file=$tmpresult_num.$patient_num;
		$inF = fopen("$CHEMIN_GLOBAL_UPLOAD/tmp_graphviz_parcours_sejour_uf_$num_file.dot","w");
		fputs( $inF,"$fichier");
		fclose($inF);
		
		exec("$CHEMIN_GRAPHVIZ \"$CHEMIN_GLOBAL_UPLOAD/tmp_graphviz_parcours_sejour_uf_$num_file.dot\" -Gcharset=latin1 -Tpng -o  \"$CHEMIN_GLOBAL_UPLOAD/tmp_graphviz_parcours_sejour_uf_$num_file.png\"");

		$file='';
		if (is_file("$CHEMIN_GLOBAL_UPLOAD/tmp_graphviz_parcours_sejour_uf_$num_file.png")) {
			$file=file_get_contents ("$CHEMIN_GLOBAL_UPLOAD/tmp_graphviz_parcours_sejour_uf_$num_file.png");
		}
		return $file;
	}
}

function parcours_sejour_uf_json ($tmpresult_num,$cohort_num,$patient_num,$patient_num_encounter_num,$nb_mini) {
        global $dbh,$CHEMIN_GLOBAL,$CHEMIN_GLOBAL_UPLOAD,$CHEMIN_GRAPHVIZ,$user_num_session;
        $tableau_parcours=array();
        $tableau_noeud_present=array();
        $fichier= "{\"nodes\":[";
        $liste_total_service='';
        $tableau_list_node=array();
        $tableau_node_deja=array();
        $nb_noeud=-1;

	if ($tmpresult_num!='') {
		$req_filtre_a="exists (select encounter_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num and dwh_tmp_result_$user_num_session.$patient_num_encounter_num= a.$patient_num_encounter_num and dwh_tmp_result_$user_num_session.encounter_num is not null) and";
	}
	if ($cohort_num!='') {
		$req_filtre_a="a.patient_num in (select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1) and";
	}
	if ($patient_num!='') {
		$req_filtre_a="a.patient_num='$patient_num' and ";
	}
	
	$req=oci_parse($dbh,"select mvt_entry_mode,  unit_num,count(*) as nb 
		from dwh_patient_mvt a where $req_filtre_a  entry=1 and encounter_num is not null and mvt_entry_mode is not null   and type_mvt in ('H' ,'U') and unit_num is not null
		group by  mvt_entry_mode,  unit_num ");
        oci_execute($req) ;
        while ($res=oci_fetch_array($req,OCI_ASSOC)) {
                $entry_mode=$res['MVT_ENTRY_MODE'];
                $unit_num=$res['UNIT_NUM'];
                $nb=$res['NB'];

                if ($tableau_node_deja[$entry_mode]=='') {
                        $nb_noeud++;
                        $fichier.= "{\"name\":\"$entry_mode\"},";
                        $tableau_list_node[$entry_mode]=strval($nb_noeud); // because value 0 as an integer is considered as null
                        $tableau_node_deja[$entry_mode]='ok';
                }

                $tableau_parcours[$entry_mode.';'.$unit_num]=$nb;
                $tableau_noeud_present[$unit_num]='ok';
        }

  	$req=oci_parse($dbh,"select mvt_exit_mode,  unit_num,count(*) as nb 
  		from dwh_patient_mvt a where $req_filtre_a  out=1 and encounter_num is not null and mvt_entry_mode is not null  and type_mvt in ('H' ,'U')   and unit_num is not null
  		group by  mvt_exit_mode,  unit_num ");
        oci_execute($req) ;
        while ($res=oci_fetch_array($req,OCI_ASSOC)) {
                $out_mode=$res['MVT_EXIT_MODE'];
                $unit_num=$res['UNIT_NUM'];
                $nb=$res['NB'];

                if ($tableau_node_deja[$out_mode]=='') {
                        $nb_noeud++;
                        $fichier.= "{\"name\":\"$out_mode\"},";
                        $tableau_list_node[$out_mode]=strval($nb_noeud);
                        $tableau_node_deja[$out_mode]='ok';
                }
                $tableau_parcours[$unit_num.';'.$out_mode]=$nb;
                $tableau_noeud_present[$unit_num]='ok';
        }


	
	$req=oci_parse($dbh,"select a.unit_num as entry_unit_num,b.unit_num as out_unit_num  from dwh_patient_mvt a,dwh_patient_mvt b where
		$req_filtre_a 
                a.patient_num=b.patient_num  and
                a.encounter_num=b.encounter_num and
                a.mvt_order=b.mvt_order-1 and
                a.type_mvt in ('H' ,'U') and b.type_mvt in ('H' ,'U')
                and b.out_date is not null
                and a.out_date is not null
                and a.encounter_num is not null 
                and b.encounter_num is not null 
                and a.unit_num is not null 
                and b.unit_num is not null 
                ");
        oci_execute($req) ;
        while ($res=oci_fetch_array($req,OCI_ASSOC)) {
                $entry_unit_num=$res['ENTRY_UNIT_NUM'];
                $out_unit_num=$res['OUT_UNIT_NUM'];

                $tableau_parcours[$entry_unit_num.';'.$out_unit_num]++;
                $tableau_noeud_present[$entry_unit_num]='ok';
                $tableau_noeud_present[$out_unit_num]='ok';
        }

	$req=oci_parse($dbh,"select unit_num ,round(avg(out_date-entry_date),1) as dms from dwh_patient_mvt a where
        $req_filtre_a 
        out_date is not null and
        out_date>=entry_date and
        type_mvt in ('H' ,'U') and unit_num is not null group by unit_num ");
	oci_execute($req) ;
	while ($res=oci_fetch_array($req,OCI_ASSOC)) {
                $unit_num=$res['UNIT_NUM'];
                $dms=$res['DMS'];
                if (preg_match("/^,/",$dms)) {
                        $dms="0".$dms;
                }
                if ($tableau_node_deja[$unit_num]=='' && $tableau_noeud_present[$unit_num]=='ok') {
                        $nb_noeud++;
			$lib_uf=get_unit_str($unit_num,'s');
	                $lib_uf=str_replace("<"," inf ",$lib_uf);
	                $lib_uf=str_replace(">"," sup ",$lib_uf);
                        $fichier.= "{\"name\":\"$lib_uf\", \"dms\":\"$dms\"},";
                        $tableau_list_node[$unit_num]=strval($nb_noeud);
                        $tableau_node_deja[$unit_num]='ok';
                }
        }
        $fichier=substr($fichier,0,-1);
        $fichier.= "],\"links\":[";

        foreach ($tableau_parcours as $lien => $poids) {
                $t= explode(';',$lien);
                $t1=$t[0];
                $t2=$t[1];
                $source=$tableau_list_node[$t1];
                $target=$tableau_list_node[$t2];
                if ($source!='' && $target!='') {
                	$fichier.= "{\"source\":$source,\"target\":$target,\"value\":$poids},";
                }
        }
        $fichier=substr($fichier,0,-1);
        $fichier.= "]}";
        $fichier=preg_replace("/[âà]/i","a",$fichier);
        $fichier=preg_replace("/[éèêë]/i","e",$fichier);
        $fichier=preg_replace("/[îï]/i","i",$fichier);
        $fichier=preg_replace("/[ôö]/i","i",$fichier);
        $fichier=preg_replace("/[ùû]/i","u",$fichier);
        $fichier=preg_replace("/[ç]/i","c",$fichier);
        $fichier=preg_replace("/[ÂÀ]/i","a",$fichier);
        $fichier=preg_replace("/[ÉÈÊË]/i","e",$fichier);
        $fichier=preg_replace("/[ÎÏ]/i","i",$fichier);
        $fichier=preg_replace("/[ÔÖ]/i","i",$fichier);
        $fichier=preg_replace("/[ÙÛ]/i","u",$fichier);
        $fichier=preg_replace("/[Ç]/i","c",$fichier);
        #$num_file=$tmpresult_num.$patient_num;
        #$inF = fopen("$CHEMIN_GLOBAL_UPLOAD/tmp_d3_uf_json_$num_file.json","w");
        #fputs( $inF,"$fichier");
        #fclose($inF);
	return $fichier;


}



function parcours_sejour_service($tmpresult_num,$cohort_num,$patient_num,$patient_num_encounter_num,$nb_mini) {
	global $dbh,$CHEMIN_GLOBAL,$CHEMIN_GRAPHVIZ,$CHEMIN_GLOBAL_UPLOAD,$user_num_session;

	$max_nb=1;
	$max_width=20;

	$fichier= "	
	digraph G { 
        bgcolor=white; 
        node [shape=box]; 
         node [ fontname=Arial, fontsize=9];
         edge [ fontname=Helvetica,  fontsize=9 ];

        ";
	$max_nb=1;
	$max_width=20;
	$tableau_parcours=array();
	$table_department_display=array();
	$tableau_mode_display=array();

	$liste_total_department_num='';
	
	if ($tmpresult_num!='') {
		$req_filtre_a="exists ( select encounter_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num and dwh_tmp_result_$user_num_session.$patient_num_encounter_num= a.$patient_num_encounter_num and dwh_tmp_result_$user_num_session.encounter_num is not null) and";
	}
	if ($cohort_num!='') {
		$req_filtre_a="a.patient_num in (select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1) and";
	}
	if ($patient_num!='') {
		$req_filtre_a="a.patient_num='$patient_num' and ";
	}
	$req=oci_parse($dbh,"select mvt_entry_mode,  department_num, type_mvt,count(*) as nb 
		from dwh_patient_mvt a 
		where $req_filtre_a  entry=1 and encounter_num is not null and mvt_entry_mode is not null 
                and type_mvt in ('H' ,'U') and department_num is not null
		group by  mvt_entry_mode,  department_num, type_mvt having count(*)>=$nb_mini ");
	oci_execute($req) ;
	while ($res=oci_fetch_array($req,OCI_ASSOC)) {
		$entry_mode=$res['MVT_ENTRY_MODE'];
		$nb=$res['NB'];
		$department_num=$res['DEPARTMENT_NUM'];
		$type_mvt=$res['TYPE_MVT'];
		if ($type_mvt=='U') {
			$type_mvt='H';
		}
		if ($tableau_mode_display[$entry_mode]=='') {
			$fichier.= " \"$entry_mode\" [color=\"blue\"];\n";
			$tableau_mode_display[$entry_mode]="ok";
		}
		$tableau_parcours["\"$entry_mode\" -> \"$department_num$type_mvt\""]=$nb;
		if ($max_nb<$nb) {
			$max_nb=$nb;
		}
		
		if ($table_department_display[$department_num.$type_mvt]=='') {
			$table_department_display[$department_num.$type_mvt]=get_department_str($department_num,'s')." ($type_mvt)";
			$liste_total_department_num.="$department_num,";
		}
	}
	$req=oci_parse($dbh,"select mvt_exit_mode,  department_num, type_mvt,count(*) as nb 
		from dwh_patient_mvt a 
		where $req_filtre_a  out=1 and encounter_num is not null and mvt_exit_mode is not null  
                and type_mvt in ('H' ,'U') and department_num is not null
		group by  mvt_exit_mode,  department_num, type_mvt having count(*)>=$nb_mini ");
	oci_execute($req) ;
	while ($res=oci_fetch_array($req,OCI_ASSOC)) {
		$out_mode=$res['MVT_EXIT_MODE'];
		$nb=$res['NB'];
		$department_num=$res['DEPARTMENT_NUM'];
		if ($type_mvt=='U') {
			$type_mvt='H';
		}
		
		if ($tableau_mode_display[$out_mode]=='') {
			$fichier.= " \"$out_mode\" [color=\"red\"];\n";
			$tableau_mode_display[$out_mode]="ok";
		}
		
		$tableau_parcours[" \"$department_num$type_mvt\" -> \"$out_mode\""]=$nb;
		if ($max_nb<$nb) {
			$max_nb=$nb;
		}
		
		if ($table_department_display[$department_num.$type_mvt]=='') {
			$table_department_display[$department_num.$type_mvt]=get_department_str($department_num,'s')." ($type_mvt)";
			$liste_total_department_num.="$department_num,";
		}
	}
	$req=oci_parse($dbh,"select a.department_num as entry_department_num, a.type_mvt,b.department_num as out_department_num  from dwh_patient_mvt a,dwh_patient_mvt b where
		$req_filtre_a 
                a.patient_num=b.patient_num  and
                a.encounter_num=b.encounter_num and
                a.mvt_order=b.mvt_order-1
                and b.out_date is not null
                and a.out_date is not null
                and a.encounter_num is not null 
                and b.encounter_num is not null 
                and a.department_num is not null 
                and b.department_num is not null 
                and a.type_mvt in ('H' ,'U')
                and b.type_mvt in ('H' ,'U')
                ");
	oci_execute($req) ;
	while ($res=oci_fetch_array($req,OCI_ASSOC)) {
		$entry_department_num=$res['ENTRY_DEPARTMENT_NUM'];
		$out_department_num=$res['OUT_DEPARTMENT_NUM'];
		$type_mvt=$res['TYPE_MVT'];
		if ($type_mvt=='U') {
			$type_mvt='H';
		}
		
		$tableau_parcours["\"$entry_department_num$type_mvt\" -> \"$out_department_num$type_mvt\""]++;
		
		if ($tableau_parcours["\"$entry_department_num$type_mvt\" -> \"$out_department_num$type_mvt\""]>=$nb_mini) {
			if ($table_department_display[$entry_department_num.$type_mvt]=='') {
				$table_department_display[$entry_department_num.$type_mvt]=get_department_str($entry_department_num,'s')." ($type_mvt)";
				$liste_total_department_num.="$entry_department_num,";
			}
			if ($table_department_display[$out_department_num.$type_mvt]=='') {
				$table_department_display[$out_department_num.$type_mvt]=get_department_str($out_department_num,'s')." ($type_mvt)";
				$liste_total_department_num.="$out_department_num,";
			}
		}
		if ($max_nb<$tableau_parcours["\"$entry_department_num$type_mvt\" -> \"$out_department_num$type_mvt\""]) {
			$max_nb=$tableau_parcours["\"$entry_department_num$type_mvt\" -> \"$out_department_num$type_mvt\""];
		}
	}
	
	
	$liste_total_department_num=substr($liste_total_department_num,0,-1);
	if ($liste_total_department_num!='') {
		
		$req=oci_parse($dbh,"select department_num ,round(avg(out_date-entry_date),1) as dms from dwh_patient_mvt a where
                $req_filtre_a 
                out_date is not null and
                out_date>=entry_date and
                type_mvt in ('H' ,'U') and
                department_num in ($liste_total_department_num) group by department_num ");
		oci_execute($req) ;
		while ($res=oci_fetch_array($req,OCI_ASSOC)) {
			$department_num=$res['DEPARTMENT_NUM'];
			$dms=$res['DMS'];
			if (preg_match("/^,/",$dms)) {
				$dms="0".$dms;
			}
			$tableau_uf_dms[$department_num."H"]=$dms;
		}
		foreach ($table_department_display as $department_num => $unit_str) {
			$unit_str=str_replace("<"," inf ",$unit_str);
			$unit_str=str_replace(">"," sup ",$unit_str);
			$fichier.= "\"$department_num\" [label=<$unit_str, dms ".$tableau_uf_dms[$department_num].">];\n";
		}

		foreach ($tableau_parcours as $lien => $poids) {
			if ($poids>=$nb_mini) {
				$new_poids=round($poids*$max_width/$max_nb,0);
				if ($new_poids==0) {
					$new_poids=1;
				}
				$fichier.= "	$lien [weight=1, penwidth=$new_poids, label=\"$poids\",fontcolor=\"red\"] ;\n";
			}
		}
		$fichier.= "
		}";	
		$num_file=$tmpresult_num.$patient_num;
		
		$inF = fopen("$CHEMIN_GLOBAL_UPLOAD/tmp_graphviz_parcours_sejour_service_$num_file.dot","w");
		fputs( $inF,"$fichier");
		fclose($inF);
		
		exec("$CHEMIN_GRAPHVIZ \"$CHEMIN_GLOBAL_UPLOAD/tmp_graphviz_parcours_sejour_service_$num_file.dot\" -Gcharset=latin1 -Tpng -o  \"$CHEMIN_GLOBAL_UPLOAD/tmp_graphviz_parcours_sejour_service_$num_file.png\"");
		$file='';
		if (is_file("$CHEMIN_GLOBAL_UPLOAD/tmp_graphviz_parcours_sejour_service_$num_file.png")) {
			$file=file_get_contents ("$CHEMIN_GLOBAL_UPLOAD/tmp_graphviz_parcours_sejour_service_$num_file.png");
		}
		return $file;
	}
}


function parcours_sejour_service_json ($tmpresult_num,$cohort_num,$patient_num,$patient_num_encounter_num,$nb_mini) {
        global $dbh,$CHEMIN_GLOBAL,$CHEMIN_GLOBAL_UPLOAD,$user_num_session;
        $tableau_parcours=array();
        $tableau_noeud_present=array();
        $fichier= "{\"nodes\":[";
        $liste_total_service='';
        $tableau_list_node=array();
        $tableau_node_deja=array();
        $nb_noeud=-1;
        
        
	if ($tmpresult_num!='') {
		$req_filtre_a="exists ( select encounter_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num and dwh_tmp_result_$user_num_session.$patient_num_encounter_num= a.$patient_num_encounter_num and dwh_tmp_result_$user_num_session.encounter_num is not null) and";
	}
	if ($cohort_num!='') {
		$req_filtre_a="a.patient_num in (select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1) and";
	}
	if ($patient_num!='') {
		$req_filtre_a="a.patient_num='$patient_num' and ";
	}
	
	$req=oci_parse($dbh,"select mvt_entry_mode,  department_num,count(*) as nb 
		from dwh_patient_mvt a where $req_filtre_a  entry=1 and encounter_num is not null and mvt_entry_mode is not null and type_mvt in ('H' ,'U') and department_num is not null 
		group by  mvt_entry_mode,  department_num ");
	oci_execute($req) ;
	while ($res=oci_fetch_array($req,OCI_ASSOC)) {
                $entry_mode=$res['MVT_ENTRY_MODE'];
                $department_num=$res['DEPARTMENT_NUM'];
                $nb=$res['NB'];

                if ($tableau_node_deja[$entry_mode]=='') {
                        $nb_noeud++;
                        $fichier.= "{\"name\":\"$entry_mode\"},";
                        $tableau_list_node[$entry_mode]=strval($nb_noeud);
                        $tableau_node_deja[$entry_mode]='ok';
                }

                $tableau_parcours[$entry_mode.';'.$department_num]=$nb;
                $tableau_noeud_present[$department_num]='ok';
        }
	
	$req=oci_parse($dbh,"select mvt_exit_mode,  department_num,count(*) as nb 
		from dwh_patient_mvt a where $req_filtre_a  out=1 and encounter_num is not null and mvt_entry_mode is not null and type_mvt in ('H' ,'U') and department_num is not null 
		group by  mvt_exit_mode,  department_num ");
        oci_execute($req) ;
        while ($res=oci_fetch_array($req,OCI_ASSOC)) {
                $out_mode=$res['MVT_EXIT_MODE'];
                $department_num=$res['DEPARTMENT_NUM'];
                $nb=$res['NB'];

                if ($tableau_node_deja[$out_mode]=='') {
                        $nb_noeud++;
                        $fichier.= "{\"name\":\"$out_mode\"},";
                        $tableau_list_node[$out_mode]=strval($nb_noeud);
                        $tableau_node_deja[$out_mode]='ok';
                }
                $tableau_parcours[$department_num.';'.$out_mode]=$nb;
                $tableau_noeud_present[$department_num]='ok';
        }

	$req=oci_parse($dbh,"select a.department_num as SERVICE_E,b.department_num as SERVICE_S  
		from dwh_patient_mvt a,dwh_patient_mvt b where
		$req_filtre_a 
                a.patient_num=b.patient_num  and
                a.encounter_num=b.encounter_num and
                a.mvt_order=b.mvt_order-1
                and b.out_date is not null
                and a.out_date is not null
                and a.encounter_num is not null 
                and b.encounter_num is not null 
                and a.department_num is not null 
                and b.department_num is not null 
                and a.type_mvt in ('H' ,'U')
                and b.type_mvt in ('H' ,'U')
                ");

        oci_execute($req) ;
        while ($res=oci_fetch_array($req,OCI_ASSOC)) {
                $service_e=$res['SERVICE_E'];
                $service_s=$res['SERVICE_S'];
                $tableau_parcours[$service_e.';'.$service_s]++;
                $tableau_noeud_present[$service_e]='ok';
                $tableau_noeud_present[$service_s]='ok';
        }


	$req=oci_parse($dbh,"select department_num ,round(avg(out_date-entry_date),1) as dms from dwh_patient_mvt a where
        $req_filtre_a 
        out_date is not null and
        out_date>=entry_date and
        type_mvt in ('H' ,'U')  and department_num is not null group by department_num ");
        oci_execute($req) ;
        while ($res=oci_fetch_array($req,OCI_ASSOC)) {
                $department_num=$res['DEPARTMENT_NUM'];
                $dms=$res['DMS'];
                
                $department_str=get_department_str($department_num);
                if (preg_match("/^,/",$dms)) {
                        $dms="0".$dms;
                }
                $department_str=str_replace("<"," inf ",$department_str);
                $department_str=str_replace(">"," sup ",$department_str);

                if ($tableau_node_deja[$department_num]=='' && $tableau_noeud_present[$department_num]=='ok') {
                        $nb_noeud++;
                        $fichier.= "{\"name\":\"$department_str\" ,\"dms\":\"$dms\"},";
                        $tableau_list_node[$department_num]=strval($nb_noeud);
                        $tableau_node_deja[$department_num]='ok';
                }
        }
        $fichier=substr($fichier,0,-1);
        $fichier.= "],\"links\":[";

        foreach ($tableau_parcours as $lien => $poids) {
                $t=explode(';',$lien);
                $t1=$t[0];
                $t2=$t[1];
                $source=$tableau_list_node[$t1];
                $target=$tableau_list_node[$t2];
		if ($source!='' && $target!='') {
			$fichier.= "{\"source\":$source,\"target\":$target,\"value\":$poids},";
                }
        }
        $fichier=substr($fichier,0,-1);
        $fichier.= "]}";
        $fichier=preg_replace("/[âà]/i","a",$fichier);
        $fichier=preg_replace("/[éèêë]/i","e",$fichier);
        $fichier=preg_replace("/[îï]/i","i",$fichier);
        $fichier=preg_replace("/[ôö]/i","i",$fichier);
        $fichier=preg_replace("/[ùû]/i","u",$fichier);
        $fichier=preg_replace("/[ç]/i","c",$fichier);
        $fichier=preg_replace("/[ÂÀ]/i","a",$fichier);
        $fichier=preg_replace("/[ÉÈÊË]/i","e",$fichier);
        $fichier=preg_replace("/[ÎÏ]/i","i",$fichier);
        $fichier=preg_replace("/[ÔÖ]/i","i",$fichier);
        $fichier=preg_replace("/[ÙÛ]/i","u",$fichier);
        $fichier=preg_replace("/[Ç]/i","c",$fichier);
        #$num_file=$tmpresult_num.$patient_num.$cohort_num;
        #$inF = fopen("$CHEMIN_GLOBAL_UPLOAD/tmp_d3_service_json_$num_file.json","w");
        #fputs( $inF,"$fichier");
        #fclose($inF);
	return $fichier;
}


/*******************************************************************************
* @desc récupère l'horloge interne (en microsecondes)
*******************************************************************************/
function getmicrotime() {
    // découpe le tableau de microsecondes selon les espaces
    list($usec, $sec) = explode(" ",microtime());

    // replace dans l'ordre
    return ((float)$usec + (float)$sec);
}




/*******************************************************************************
* @desc Affiche le temps écoulé (en microsecondes) depuis la dernière étape.
* L'argument $nom_etape permet de spécifier ce qui est mesuré
* (ex. "page de stats" ou "requête numéro 7")
********************************************************************************/
function benchmark ( $nom_etape )
{
	global $etape_prec;
	$temps_ecoule = ($etape_prec) ? round((getmicrotime() - $etape_prec)*1000) : 0;
	$retour = '<p class="alerte">' . $nom_etape . ' : ' . $temps_ecoule . 'ms</p>';
	$etape_prec = getmicrotime();
	return $retour;
}


/*******************************************************************************
* @desc Affiche le temps écoulé (en microsecondes) depuis la dernière étape.
* @author NG
* @param nom_etape Paramètre non utilisé pour l'instant
* @param format : paramètre à ajouter
* @return une chaine HTML contenant le nom de l'etape et le temps écoulé
*******************************************************************************/
function benchmarktotal ( $nom_etape )
{
	global $etape_debut;
	$temps_ecoule = ($etape_debut) ? round((getmicrotime() - $etape_debut)*1000) : 0;
	$retour = '<p class="alerte">' . $nom_etape . ' : ' . $temps_ecoule . 'ms</p>';
	$etape_debut = getmicrotime();
	return $retour;
}



function verif_process($match) {
    $test='';
    if($match=='') return 'no pattern specified';
    $match = escapeshellarg($match);
    exec("ps -ef|grep $match|grep -v grep|awk '{print $2}'", $output, $ret);
    if($ret) return 'you need ps, grep, and awk installed for this to work';
    while(list(,$t) = each($output)) {
        if(preg_match('/^([0-9]+)/', $t, $r)) {
        	$test='notend';
        }
    }
    if($test=='notend') {
        return '0'; // process pasfini
    } else {
        return '1'; // process fini
    }
}

function get_department_str ($department_num) {
	global $dbh;
	$department_str='';
	if ($department_num!='') {
		$sel=oci_parse($dbh,"select department_str from dwh_thesaurus_department where department_num=$department_num  and department_master=1");
		oci_execute($sel) ;
		$r=oci_fetch_array($sel,OCI_ASSOC);
		$department_str=$r['DEPARTMENT_STR'];
		$department_str=str_replace("&"," ET ",$department_str);
		ocifreestatement($sel);
	} 
	return $department_str;
}

function get_unit_str ($unit_num,$option='cs') {
	global $dbh;
	$unit_str='';
	if ($unit_num!='') {
		$sel=oci_parse($dbh,"select unit_code, unit_str from dwh_thesaurus_unit where unit_num=$unit_num ");
		oci_execute($sel) ;
		$r=oci_fetch_array($sel,OCI_ASSOC);
		$unit_code=$r['UNIT_CODE'];
		$unit_str=$r['UNIT_STR'];
		$unit_str=str_replace("&"," ET ",$unit_str);
		ocifreestatement($sel);
		if ($option=='cs') {
			return "$unit_code $unit_str";
		}
		if ($option=='s') {
			return "$unit_str";
		}
		if ($option=='c') {
			return "$unit_code";
		}
	} else {
		return "";
	}
}


function modify_hospital_patient_id ($patient_num,$hospital_patient_id_ancien,$hospital_patient_id_nouveau) {
	global $dbh,$dbh_etl,$URL_SIH_PATIENT_API;
	$patient_num_a_garder='';
	$patient_num_a_supprimer='';
	$hospital_patient_id_master='';
	if ($patient_num!='' && $hospital_patient_id_nouveau!='' &&  $hospital_patient_id_nouveau!=$hospital_patient_id_ancien) {
		// check if hospital_patient_id_nouveau already exist in the datawarehouse
		$patient_num_nouveau=get_patient_num($hospital_patient_id_nouveau,'SIH');
		print "patient_num_nouveau : $patient_num_nouveau<br>";
		// if patient_num_nouveau does not exist
		if ($patient_num_nouveau=='') {
			// test if hospital_patient_id_nouveau is SIH or not 
			if ($URL_SIH_PATIENT_API!='') {
				print "$URL_SIH_PATIENT_API?action=test_ipp_sih&ipp=$hospital_patient_id_nouveau<br>";
				$file=file ("$URL_SIH_PATIENT_API?action=test_ipp_sih&ipp=$hospital_patient_id_nouveau");
				$verif_ipp_sih=implode('',$file);
				if ($verif_ipp_sih>0) {
					print "test 1 insert_hospital_patient_id ($patient_num,$hospital_patient_id_nouveau,'SIH',0);<br>";
					insert_hospital_patient_id ($patient_num,$hospital_patient_id_nouveau,'SIH',1);
					print "update_master_patient_id ($patient_num,$hospital_patient_id_nouveau);<br>";
					update_master_patient_id ($patient_num,$hospital_patient_id_nouveau);
					$patient_num_nouveau=$patient_num; // there is no new patient ... 
				}
			}
			
			// if no patient found in SIH, we search in other source in Dr Warehouse
			// but too dangerous !! we have to ask for the origin patient id
#			if ($patient_num_nouveau=='') {
#				$patient_num_nouveau=get_patient_num($hospital_patient_id_nouveau,'');
#				$patient_num_a_garder=$patient_num_nouveau;
#				$patient_num_a_supprimer=$patient_num;
#			
#				if ($patient_num_a_garder!='' && $patient_num_a_supprimer!='' && $patient_num_a_supprimer!=$patient_num_a_garder) {
#					print "test 2 merge_patient ($patient_num_a_garder,$patient_num_a_supprimer);<br>";
#					merge_patient ($patient_num_a_garder,$patient_num_a_supprimer);
#				}
#			}
			$patient_num_a_garder=$patient_num;
		} else {
			// if patient_num_nouveau exists
			if ($patient_num_nouveau!=$patient_num) {
				$hospital_patient_id_master='';
				if ($URL_SIH_PATIENT_API!='') {
					$file=file ("$URL_SIH_PATIENT_API?action=get_ipp_maitre&ipp=$hospital_patient_id_nouveau");
					$hospital_patient_id_master=implode('',$file);
					print "test 3 hospital_patient_id_master :$hospital_patient_id_master<br>";
				}
				if ($hospital_patient_id_master!='') {
					if ($hospital_patient_id_nouveau==$hospital_patient_id_master) {
						$patient_num_a_garder=$patient_num_nouveau;
						$patient_num_a_supprimer=$patient_num;
					} else if ($hospital_patient_id_ancien==$hospital_patient_id_master) {
						$patient_num_a_garder=$patient_num;
						$patient_num_a_supprimer=$patient_num_nouveau;
					} else {
						$patient_num_a_garder=$patient_num_nouveau;
						$patient_num_a_supprimer=$patient_num;
					}
					
					if ($patient_num_a_garder!='' && $patient_num_a_supprimer!='' && $patient_num_a_supprimer!=$patient_num_a_garder) {
						print "test 4 merge_patient ($patient_num_a_garder,$patient_num_a_supprimer);<br>";
						merge_patient ($patient_num_a_garder,$patient_num_a_supprimer);
					}
					if ($hospital_patient_id_master!=$hospital_patient_id_nouveau && $hospital_patient_id_master!=$hospital_patient_id_ancien) {
						$patient_num_maitre=get_patient_num($hospital_patient_id_master,'SIH');
						print "patient_num_maitre : $patient_num_maitre<br>";
						if ($patient_num_maitre!='' && $patient_num_maitre!=$patient_num_nouveau && $patient_num_maitre!=$patient_num) {
							print "test 5  modify_hospital_patient_id ($patient_num_a_garder,$hospital_patient_id_nouveau,$hospital_patient_id_master);<br>";
							#modify_hospital_patient_id ($patient_num_a_garder,$hospital_patient_id_nouveau,$hospital_patient_id_master);
						} else if ($patient_num_maitre=='') {
							print "test 6 insert_hospital_patient_id ($patient_num_a_garder,$hospital_patient_id_master,'SIH',1);<br>";
							insert_hospital_patient_id ($patient_num_a_garder,$hospital_patient_id_master,'SIH',1);
						}
					}
					print "test 7 update_master_patient_id ($patient_num_a_garder,$hospital_patient_id_master);<br>";
					update_master_patient_id ($patient_num_a_garder,$hospital_patient_id_master);
				} else {
					print "NO MASTER IPP<br>";
				}
			} else {
				$patient_num_a_garder=$patient_num;
				print "update_master_patient_id ($patient_num_a_garder,$hospital_patient_id_nouveau);<br>";
				update_master_patient_id ($patient_num_a_garder,$hospital_patient_id_nouveau);
			}
		}
	} else {
		$patient_num_a_garder=$patient_num;
	}

	return $patient_num_a_garder;
}

function merge_patient ($patient_num_to_keep,$patient_num_to_delete) {
	global $dbh,$dbh_etl;
	$liste_table_patient_num=array(
					'DWH_COHORT_RESULT',
					'DWH_COHORT_RESULT_COMMENT',
					'DWH_DATA',
					'DWH_DATAMART_RESULT',
					'DWH_ECRF_ANSWER',
					'DWH_REQUEST_ACCESS_PATIENT',
					'DWH_DOCUMENT',
					'DWH_FILE',
					'DWH_ENRSEM',
					'DWH_PATIENT_IPPHIST',
					'DWH_LOG_PATIENT',
					'DWH_PATIENT_STAY',
					'DWH_PATIENT_MVT',
					'DWH_PATIENT_DEPARTMENT',
					'DWH_PATIENT_STAT',
					'DWH_PROCESS_PATIENT',
					'DWH_QUERY_RESULT',
					'DWH_TEXT'
	);
	if ($patient_num_to_keep!='' && $patient_num_to_delete!='' && $patient_num_to_keep!=$patient_num_to_delete) {
		$master_patient_id_to_keep=get_master_patient_id_sih($patient_num_to_keep); // uniquement SIH
		$master_patient_id_to_delete=get_master_patient_id_sih($patient_num_to_delete); // uniquement SIH
		$master_patient_id='';
		if ($master_patient_id_to_keep!='') {
			$master_patient_id=$master_patient_id_to_keep;
		} else if ($master_patient_id_to_delete!='') {
			$master_patient_id=$master_patient_id_to_delete;
		} else {
			$master_patient_id=get_master_patient_id($patient_num_to_keep); // pas forcement SIH
		}
		
		foreach ($liste_table_patient_num as $table_name) {
			$req_dwh="update $table_name set patient_num='$patient_num_to_keep' where patient_num='$patient_num_to_delete' ";
			print "$req_dwh<br>";
			$sel_dwh=oci_parse($dbh_etl,$req_dwh);
			oci_execute($sel_dwh);
		}

		$req_dwh="update dwh_patient_rel set patient_num_1='$patient_num_to_keep' where patient_num_1='$patient_num_to_delete' ";
		print "$req_dwh<br>";
		$sel_dwh=oci_parse($dbh_etl,$req_dwh);
		oci_execute($sel_dwh);
		
		$req_dwh="update dwh_patient_rel set patient_num_2='$patient_num_to_keep' where patient_num_2='$patient_num_to_delete' ";
		print "$req_dwh<br>";
		$sel_dwh=oci_parse($dbh_etl,$req_dwh);
		oci_execute($sel_dwh);
		
		$req_dwh="delete from  dwh_patient where patient_num=$patient_num_to_delete";
		print "$req_dwh<br>";
		$sel_dwh=oci_parse($dbh_etl,$req_dwh);
		oci_execute($sel_dwh) ;
		
		print "update_master_patient_id ($patient_num_to_keep,$master_patient_id);<br>";
		update_master_patient_id ($patient_num_to_keep,$hospital_patient_id_nouveau);
	}
}


function modify_hospital_patient_id_ancien ($patient_num,$hospital_patient_id_ancien,$hospital_patient_id_nouveau) {
	global $dbh,$dbh_etl,$URL_SIH_PATIENT_API;
	$hospital_patient_id_new_maitre='';
	$patient_num_new_maitre='';
	$master_patient_id='';
	$liste_table_patient_num=array(
					'DWH_COHORT_RESULT',
					'DWH_COHORT_RESULT_COMMENT',
					'DWH_DATA',
					'DWH_DATAMART_RESULT',
					'DWH_ECRF_ANSWER',
					'DWH_REQUEST_ACCESS_PATIENT',
					'DWH_DOCUMENT',
					'DWH_FILE',
					'DWH_ENRSEM',
					'DWH_PATIENT_IPPHIST',
					'DWH_LOG_PATIENT',
					'DWH_PATIENT_STAY',
					'DWH_PATIENT_MVT',
					'DWH_PATIENT_DEPARTMENT',
					'DWH_PATIENT_STAT',
					'DWH_PROCESS_PATIENT',
					'DWH_QUERY_RESULT',
					'DWH_TEXT'
	);
	if ($patient_num!='' && $hospital_patient_id_nouveau!='' && $hospital_patient_id_ancien!='' && $hospital_patient_id_nouveau!=$hospital_patient_id_ancien) {
	
		// on recherche l'IPP maitre ///
		if ($URL_SIH_PATIENT_API!='') {
			$file=file ("$URL_SIH_PATIENT_API?action=get_ipp_maitre&ipp=$hospital_patient_id_nouveau");
			$hospital_patient_id_master=implode('',$file);
		}
		
		if ($hospital_patient_id_master=='') {
			$sel=oci_parse($dbh,"select hospital_patient_id from dwh_patient_ipphist  where hospital_patient_id='$hospital_patient_id_nouveau' ");
			oci_execute($sel) ;
			$r=oci_fetch_array($sel,OCI_ASSOC);
			$hospital_patient_id_master=$r['HOSPITAL_PATIENT_ID'];
		}
		
		if ($hospital_patient_id_master!='') {
			$patient_num_maitre=get_patient_num($hospital_patient_id_master,'SIH');
			$patient_num_nouveau=get_patient_num($hospital_patient_id_nouveau,'SIH');
			
			if ($patient_num_maitre=='') {
				$patient_num_maitre=$patient_num;
				insert_hospital_patient_id ($patient_num,$hospital_patient_id_master,'SIH',1);
			}
			
			if ($patient_num_nouveau=='') {
				$patient_num_nouveau=$patient_num_maitre;
				insert_hospital_patient_id($patient_num_maitre,$hospital_patient_id_nouveau,'SIH',0);
			}
			
			if ($patient_num_nouveau!=$patient_num_maitre) {
				foreach ($liste_table_patient_num as $table_name) {
					$req_dwh="update $table_name set patient_num='$patient_num_maitre' where patient_num='$patient_num_nouveau' ";
					$sel_dwh=oci_parse($dbh_etl,$req_dwh);
					oci_execute($sel_dwh);
				}

				$req_dwh="update dwh_patient_rel set patient_num_1='$patient_num_maitre' where patient_num_1='$patient_num_nouveau' ";
				$sel_dwh=oci_parse($dbh_etl,$req_dwh);
				oci_execute($sel_dwh);
				
				$req_dwh="update dwh_patient_rel set patient_num_2='$patient_num_maitre' where patient_num_2='$patient_num_nouveau' ";
				$sel_dwh=oci_parse($dbh_etl,$req_dwh);
				oci_execute($sel_dwh);
				
				$req_dwh="delete from  dwh_patient where patient_num=$patient_num_nouveau";
				$sel_dwh=oci_parse($dbh_etl,$req_dwh);
				oci_execute($sel_dwh) ;
			}
			if ($patient_num!=$patient_num_maitre) {
				
				foreach ($liste_table_patient_num as $table_name) {
					$req_dwh="update $table_name set patient_num='$patient_num_maitre' where patient_num='$patient_num' ";
					$sel_dwh=oci_parse($dbh_etl,$req_dwh);
					oci_execute($sel_dwh);
				}
				$req_dwh="update dwh_patient_rel set patient_num_1='$patient_num_maitre' where patient_num_1='$patient_num' ";
				$sel_dwh=oci_parse($dbh_etl,$req_dwh);
				oci_execute($sel_dwh);
				
				$req_dwh="update dwh_patient_rel set patient_num_2='$patient_num_maitre' where patient_num_2='$patient_num' ";
				$sel_dwh=oci_parse($dbh_etl,$req_dwh);
				oci_execute($sel_dwh);
				
				$req_dwh="delete from  dwh_patient where patient_num=$patient_num ";
				$sel_dwh=oci_parse($dbh_etl,$req_dwh);
				oci_execute($sel_dwh) ;
			}
			
			update_master_patient_id ($patient_num_maitre,$hospital_patient_id_master);
			update_patient ($patient_num_maitre);
		} 
	} 
	return $patient_num_maitre;
}

function update_patient ($patient_num) {
	global $dbh,$URL_SIH_PATIENT_API;
	$req="select zip_code,to_char(birth_date,'DD/MM/YYYY') as birth_date,residence_latitude,birth_country,patient_num from dwh_patient where patient_num='$patient_num'";
	$sel=oci_parse($dbh,$req);
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC); 
	$datenais_base=$r['BIRTH_DATE'];
	$cp_base=$r['ZIP_CODE'];
	$residence_latitude=$r['RESIDENCE_LATITUDE'];
	$birth_country=$r['BIRTH_COUNTRY'];
	$patient_num=$r['PATIENT_NUM'];

	$lastname_patient='';
	$zip_code='';
	$hospital_patient_id=get_master_patient_id($patient_num);
	if ($URL_SIH_PATIENT_API!='') {
		$file=file ("$URL_SIH_PATIENT_API?action=recup_depuis_ipp.php?ipp=$hospital_patient_id");
		$ligne=implode('',$file);
		$ligne=str_replace("'","''","$ligne");
		$ligne=str_replace("\""," ","$ligne");
		$tableau=explode(";separateur;",$ligne);
		$master_patient_id=$tableau[0]; // MASTER_PATIENT_ID
		$lastname_patient=$tableau[1];
		$firstname_patient=$tableau[2];
		$birth_date=$tableau[3];
		$sex=$tableau[4];
		$zip_code=$tableau[5];
		$residence_city=$tableau[6];
		$residence_address=$tableau[7];
		$phone_number=$tableau[8];
		$maiden_name=$tableau[9];
		$residence_country=$tableau[10];
		$dcd=$tableau[11];
		$death_date=$tableau[12];
		$code_insee_residence_city_naissance=$tableau[13];
		$birth_city=$tableau[14];
		$code_insee_pays_naissance=$tableau[15];
	}
	
	if ($lastname_patient!='') {
		$req_upd="update dwh_patient set lastname='$lastname_patient',
				firstname='$firstname_patient',
				birth_date=to_date('$birth_date','DD/MM/YYYY'),
				sex='$sex',
				maiden_name='$maiden_name',
				death_date=to_date('$death_date','DD/MM/YYYY'),
				update_date=sysdate,
				death_code='$dcd' where patient_num=$patient_num ";
		$stmt_upd=oci_parse($dbh,$req_upd);
		oci_execute($stmt_upd) || die ("$req_upd \n");
	}
	if ($zip_code!=$cp_base) {
		$req_upd="update dwh_patient set
			residence_country='$residence_country',
			residence_address='$residence_address',
			phone_number='$phone_number',
			zip_code='$zip_code',
			residence_city='$residence_city',
			residence_latitude = null,
			residence_longitude = null where  patient_num=$patient_num ";
		$stmt_upd=oci_parse($dbh,$req_upd);
		oci_execute($stmt_upd) || die ("$req_upd \n");
		enregistrer_coordonnees_residence ($patient_num,$zip_code,$residence_city,$residence_country);
	} else {
		if ($residence_latitude=='' && $zip_code!='') {
			enregistrer_coordonnees_residence ($patient_num,$zip_code,$residence_city,$residence_country);
		}
	}
	if ($datenais_base!=$birth_date && $birth_date!='') {
		update_age($patient_num,'');
	}
	if ($birth_country=='') {
		enregistrer_lieu_naissance ($patient_num,$code_insee_ville_naissance,$birth_city,$code_insee_pays_naissance,'pasforce');
	}
}


function update_age($patient_num,$document_num) {
	global $dbh;
	
	$sel_datenais=oci_parse($dbh,"select to_char(birth_date,'DD/MM/YYYY') as birth_date  from dwh_patient where patient_num=$patient_num");
	oci_execute($sel_datenais);
	$r_datenais=oci_fetch_array($sel_datenais);
	$birth_date=$r_datenais['BIRTH_DATE'];
	$req_id_document='';
	if ($birth_date!='') {
		if ($document_num!='') {
			$req_id_document="and document_num=$document_num ";
		}
	
		$requete="select document_num from dwh_document where patient_num=$patient_num and document_date is not null $req_id_document";
		$i=0;
		$sel=oci_parse($dbh,$requete);
		oci_execute($sel);
		while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)){
			$id_document_sel=$r['DOCUMENT_NUM'];
			
			
			$requete_upd="update dwh_text set age_patient=round( (document_date-to_date('$birth_date','DD/MM/YYYY'))/365 ,2) where document_num=$id_document_sel";
			$stmt_upd=oci_parse($dbh,$requete_upd);
			oci_execute($stmt_upd) || die ("$requete_upd \n");
			
			$requete_upd="update dwh_data set age_patient=round( (document_date-to_date('$birth_date','DD/MM/YYYY'))/365 ,2) where document_num=$id_document_sel";
			$stmt_upd=oci_parse($dbh,$requete_upd);
			oci_execute($stmt_upd) || die ("$requete_upd \n");
			
			$requete_upd="update dwh_enrsem set age_patient=round( (document_date-to_date('$birth_date','DD/MM/YYYY'))/365 ,2) where document_num=$id_document_sel";
			$stmt_upd=oci_parse($dbh,$requete_upd);
			oci_execute($stmt_upd) || die ("$requete_upd \n");
		}
	} else {
		if ($document_num!='') {
			$req_id_document="and document_num=$document_num ";
		}
		
		$requete="select document_num from dwh_document where patient_num=$patient_num and document_date is not null $req_id_document";
		$i=0;
		$sel=oci_parse($dbh,$requete);
		oci_execute($sel);
		while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)){
			$id_document_sel=$r['DOCUMENT_NUM'];
			$requete_upd="update dwh_text set age_patient=null where document_num=$id_document_sel";
			print "update dwh_text set age_patient=null where document_num=$id_document_sel\n";
			$stmt_upd=oci_parse($dbh,$requete_upd);
			oci_execute($stmt_upd) || die ("$requete_upd \n");
			
			$requete_upd="update dwh_data set age_patient=null where document_num=$id_document_sel";
			$stmt_upd=oci_parse($dbh,$requete_upd);
			oci_execute($stmt_upd) || die ("$requete_upd \n");
			
			$requete_upd="update dwh_enrsem set age_patient=null where document_num=$id_document_sel";
			$stmt_upd=oci_parse($dbh,$requete_upd);
			oci_execute($stmt_upd) || die ("$requete_upd \n");
		}
	}
}

function enregistrer_coordonnees_residence ($patient_num,$zip_code,$city,$country) {
	global $dbh;
	$cp_origine=$zip_code;
	$city_modified='';
	$country_modified='';
	$latitude='';
	$longitude='';
	$pays_origine_depart=$country;
	list($latitude,$longitude,$country_modified)=trouver_coordonnees ($zip_code,$city,$country);
	if (preg_match("/-?[0-9]+,?[0-9]?/",$longitude) && preg_match("/-?[0-9]+,?[0-9]?/",$latitude)) {
		$req_upd="update dwh_patient set residence_latitude='$latitude',residence_longitude='$longitude' where patient_num=$patient_num ";
		$upd=oci_parse($dbh,$req_upd);
		oci_execute($upd) || die ("erreur : $req_upd\n");
	}  else {
		$req_upd="update dwh_patient set residence_latitude=null,residence_longitude=null where patient_num=$patient_num ";
		$upd=oci_parse($dbh,$req_upd);
		oci_execute($upd) || die ("erreur : $req_upd\n");
	}
}

function enregistrer_lieu_naissance ($patient_num,$code_insee_ville_naissance,$birth_city,$code_insee_pays_naissance,$force_maj) {
	global $dbh;
	$zip_code_naissance='';
	$birth_country='';
	if ($patient_num!='') {
		$requete="select birth_country  from dwh_patient where patient_num=$patient_num ";
		$stmt=oci_parse($dbh,$requete);
		oci_execute($stmt);
		$row_stmt=oci_fetch_array($stmt,OCI_RETURN_NULLS+OCI_ASSOC);
		$pays_naissance_base=$row_stmt['BIRTH_COUNTRY'];
		if ($code_insee_pays_naissance!='' ) {
			if ($force_maj=='force' || $pays_naissance_base=='') {
				if ($code_insee_pays_naissance=='100') {
						$birth_country='FRANCE';
						$requete="select city_norm_name,LONGITUDE,LATITUDE,zip_code from dwh_thesaurus_city where insee_code='$code_insee_ville_naissance'  and country='FRANCE' ";
						$stmt=oci_parse($dbh,$requete);
						oci_execute($stmt) || die("$requete");
						$row_stmt=oci_fetch_array($stmt,OCI_RETURN_NULLS+OCI_ASSOC);
						$zip_code_naissance=$row_stmt['ZIP_CODE'];
						$birth_city=$row_stmt['CITY_NORM_NAME'];
						$longitude=$row_stmt['LONGITUDE'];
						$latitude=$row_stmt['LATITUDE'];
				}
				if ($code_insee_pays_naissance!='' && $code_insee_pays_naissance!='100') {
					$requete="select CAPITAL_CITY_STR_NORM_FR from dwh_thesaurus_country where insee_code='$code_insee_pays_naissance' ";
					$stmt=oci_parse($dbh,$requete);
					oci_execute($stmt);
					$row_stmt=oci_fetch_array($stmt,OCI_RETURN_NULLS+OCI_ASSOC);
					$birth_country=$row_stmt['CAPITAL_CITY_STR_NORM_FR'];
					$ville_naissance_req=str_replace("'","''",$birth_city);
					$pays_naissance_req=str_replace("'","''",$birth_country);
					$requete="select LONGITUDE,LATITUDE from dwh_thesaurus_city where city_norm_name='$ville_naissance_req'  and country='$pays_naissance_req' ";
					$stmt=oci_parse($dbh,$requete);
					oci_execute($stmt) || die("$requete");
					$row_stmt=oci_fetch_array($stmt,OCI_RETURN_NULLS+OCI_ASSOC);
					$longitude=$row_stmt['LONGITUDE'];
					$latitude=$row_stmt['LATITUDE'];	
				}
				if ($longitude=='') {
					list($latitude,$longitude,$pays_naissance_modifie)=trouver_coordonnees ($zip_code_naissance,$birth_city,$birth_country); 
				}
				$birth_city=str_replace("'","''",$birth_city);
				$birth_country=str_replace("'","''",$birth_country);
				if (!preg_match("/-?[0-9]+,?[0-9]?/",$longitude) || !preg_match("/-?[0-9]+,?[0-9]?/",$latitude)) {
					$latitude='';
					$longitude='';
				}
				$coordonnees="SDO_GEOMETRY(2001, 4326, SDO_POINT_TYPE('$longitude','$latitude',NULL), NULL, NULL)";
				$requete="update dwh_patient set birth_latitude='$latitude',birth_longitude='$longitude',birth_zip_code='$zip_code_naissance',birth_city='$birth_city',birth_country='$birth_country'  where patient_num=$patient_num";
				$stmt=oci_parse($dbh,$requete);
				oci_execute($stmt) || die("$requete");
			}
		}
	}
}

function trouver_coordonnees ($zip_code,$city,$country) {
	global $dbh;
	$country=str_replace("'","''",$country);
	$city_norme=str_replace("'"," ",$city);
	$city=str_replace("'","''",$city);
	$city_norme=str_replace("-"," ",$city_norme);
	if (preg_match("/^0/",$zip_code)) {
		$zip_code=substr($zip_code,1);
	}
	if (strlen($zip_code)==4) {
		$cpdeb='0'.substr($zip_code,0,1);
	} else {
		$cpdeb=substr($zip_code,0,2);
	}
	if ($cpdeb!='99' && preg_match("/^[0-9][0-9]$/",$cpdeb)) {
		if ($country!='') {
			$requete_ville="select longitude,latitude from dwh_thesaurus_city where zip_code='$zip_code' and (city_norm_name='$city' or city_norm_name='$city_norme') and longitude is not null and latitude is not null and country='$country' ";
			$stmt_test_ville=oci_parse($dbh,$requete_ville);
			oci_execute($stmt_test_ville);
			$r=oci_fetch_array($stmt_test_ville,OCI_RETURN_NULLS+OCI_ASSOC);
			$longitude=$r['LONGITUDE'];
			$latitude=$r['LATITUDE'];
			if ($longitude=='' && $latitude=='' && preg_match("/ST /i",$city)) {
				$city_modified=preg_replace("/ST /i","SAINT ",$city);
				$city_norme_modified=str_replace("'"," ",$city_modified);
				$city_norme_modified=str_replace("-"," ",$city_norme_modified);
				$requete_ville="select longitude,latitude from dwh_thesaurus_city where zip_code='$zip_code' and (city_norm_name='$city_modified' or city_norm_name='$city_norme_modified' ) and longitude is not null and latitude is not null and country='$country' ";
				$stmt_test_ville=oci_parse($dbh,$requete_ville);
				oci_execute($stmt_test_ville);
				$r=oci_fetch_array($stmt_test_ville,OCI_RETURN_NULLS+OCI_ASSOC);
				$longitude=$r['LONGITUDE'];
				$latitude=$r['LATITUDE'];
			}
			if ($longitude=='' && $latitude=='' && preg_match("/STE /i",$city)) {
				$city_modified=preg_replace("/STE /i","SAINTE ",$city_modified);
				$city_norme_modified=str_replace("'"," ",$city_modified);
				$city_norme_modified=str_replace("-"," ",$city_norme_modified);
				$requete_ville="select longitude,latitude from dwh_thesaurus_city where zip_code='$zip_code' and (city_norm_name='$city_modified' or city_norm_name='$city_norme_modified' ) and longitude is not null and latitude is not null and country='$country' ";
				$stmt_test_ville=oci_parse($dbh,$requete_ville);
				oci_execute($stmt_test_ville);
				$r=oci_fetch_array($stmt_test_ville,OCI_RETURN_NULLS+OCI_ASSOC);
				$longitude=$r['LONGITUDE'];
				$latitude=$r['LATITUDE'];
			}
		}
		if ($longitude=='' && $latitude=='') {
			$requete_ville="select longitude,latitude,country from dwh_thesaurus_city where zip_code='$zip_code' and (city_norm_name='$city' or city_norm_name='$ville_norme') and longitude is not null and latitude is not null";
			$stmt_test_ville=oci_parse($dbh,$requete_ville);
			oci_execute($stmt_test_ville);
			$r=oci_fetch_array($stmt_test_ville,OCI_RETURN_NULLS+OCI_ASSOC);
			$longitude=$r['LONGITUDE'];
			$latitude=$r['LATITUDE'];
			$country_modified=$r['COUNTRY'];
		}
		if ($longitude=='' && $latitude=='') {
			$city_modified=preg_replace("/[0-9]/i","",$city);
			$city_modified=trim($city_modified);
			$city_norme_modified=str_replace("'"," ",$city_modified);
			$city_norme_modified=str_replace("-"," ",$city_norme_modified);
			$requete_ville="select longitude,latitude,country from dwh_thesaurus_city where zip_code='$zip_code' and (city_norm_name='$city_modified' or city_norm_name='$city_norme_modified' ) and longitude is not null and latitude is not null";
			$stmt_test_ville=oci_parse($dbh,$requete_ville);
			oci_execute($stmt_test_ville);
			$r=oci_fetch_array($stmt_test_ville,OCI_RETURN_NULLS+OCI_ASSOC);
			$longitude=$r['LONGITUDE'];
			$latitude=$r['LATITUDE'];
			$country_modified=$r['COUNTRY'];
		}
		if ($longitude=='' && $latitude=='' && preg_match("/ST /i",$city)) {
			$city_modified=preg_replace("/ST /i","SAINT ",$city);
			$city_norme_modified=str_replace("'"," ",$city_modified);
			$city_norme_modified=str_replace("-"," ",$city_norme_modified);
			$requete_ville="select longitude,latitude,country from dwh_thesaurus_city where zip_code='$zip_code' and (city_norm_name='$city_modified' or city_norm_name='$city_norme_modified' )  and longitude is not null and latitude is not null";
			$stmt_test_ville=oci_parse($dbh,$requete_ville);
			oci_execute($stmt_test_ville);
			$r=oci_fetch_array($stmt_test_ville,OCI_RETURN_NULLS+OCI_ASSOC);
			$longitude=$r['LONGITUDE'];
			$latitude=$r['LATITUDE'];
			$country_modified=$r['COUNTRY'];
		}
		if ($longitude=='' && $latitude=='' && preg_match("/STE /i",$city)) {
			$city_modified=preg_replace("/STE /i","SAINTE ",$city_modified);
			$city_norme_modified=str_replace("'"," ",$city_modified);
			$city_norme_modified=str_replace("-"," ",$city_norme_modified);
			$requete_ville="select longitude,latitude,country from dwh_thesaurus_city where zip_code='$zip_code' and (city_norm_name='$city_modified' or city_norm_name='$city_norme_modified' ) and longitude is not null and latitude is not null";
			$stmt_test_ville=oci_parse($dbh,$requete_ville);
			oci_execute($stmt_test_ville);
			$r=oci_fetch_array($stmt_test_ville,OCI_RETURN_NULLS+OCI_ASSOC);
			$longitude=$r['LONGITUDE'];
			$latitude=$r['LATITUDE'];
			$country_modified=$r['COUNTRY'];
		}
		if ($longitude=='' && $latitude=='') {
			$city_norme_modified=str_replace("'"," ",$city_modified);
			$city_norme_modified=str_replace("-"," ",$city_norme_modified);
			$requete_ville="select longitude,latitude,country from dwh_thesaurus_city where zip_code='$zip_code' and (soundex(city_norm_name) = soundex('$city_modified') or soundex(city_norm_name) = soundex('$city_norme_modified') ) 
			and UTL_MATCH.edit_distance(city_norm_name, '$city_modified') <=3  and longitude is not null and latitude is not null";
			$stmt_test_ville=oci_parse($dbh,$requete_ville);
			oci_execute($stmt_test_ville);
			$r=oci_fetch_array($stmt_test_ville,OCI_RETURN_NULLS+OCI_ASSOC);
			$longitude=$r['LONGITUDE'];
			$latitude=$r['LATITUDE'];
			$country_modified=$r['COUNTRY'];
		}
	} else {
		if ($country!='') {
			if ($longitude=='' && $latitude=='') {
				$requete_ville="select longitude,latitude from dwh_thesaurus_city where zip_code='$zip_code' and longitude is not null and latitude is not null and country='$country' ";
				$stmt_test_ville=oci_parse($dbh,$requete_ville);
				oci_execute($stmt_test_ville);
				$r=oci_fetch_array($stmt_test_ville,OCI_RETURN_NULLS+OCI_ASSOC);
				$longitude=$r['LONGITUDE'];
				$latitude=$r['LATITUDE'];
			}
			if ($longitude=='' && $latitude=='') {
				$city_modified=preg_replace("/ST /i","SAINT ",$city);
				$city_modified=preg_replace("/STE /i","SAINTE ",$city_modified);
				$city_modified=preg_replace("/[0-9]/i","",$city_modified);
				$city_modified=trim($city_modified);
				$city_norme_modified=str_replace("'"," ",$city_modified);
				$city_norme_modified=str_replace("-"," ",$city_norme_modified);
				$requete_ville="select longitude,latitude from dwh_thesaurus_city where (city_norm_name='$city_modified' or city_norm_name='$city_norme_modified' ) and longitude is not null and latitude is not null and country='$country' ";
				$stmt_test_ville=oci_parse($dbh,$requete_ville);
				oci_execute($stmt_test_ville);
				$r=oci_fetch_array($stmt_test_ville,OCI_RETURN_NULLS+OCI_ASSOC);
				$longitude=$r['LONGITUDE'];
				$latitude=$r['LATITUDE'];
			}
			if ($longitude=='' && $latitude=='') {
				$city_modified=preg_replace("/ST /i","SAINT ",$city);
				$city_modified=preg_replace("/STE /i","SAINTE ",$city_modified);
				$city_modified=preg_replace("/[0-9]/i","",$city_modified);
				$city_modified=trim($city_modified);
				$city_norme_modified=str_replace("'"," ",$city_modified);
				$city_norme_modified=str_replace("-"," ",$city_norme_modified);
				$requete_ville="select longitude,latitude from dwh_thesaurus_city where soundex(city_norm_name) = soundex('$city_modified') and UTL_MATCH.edit_distance(city_norm_name, '$city_modified') <=1 and longitude is not null and latitude is not null and country='$country' ";
				$stmt_test_ville=oci_parse($dbh,$requete_ville);
				oci_execute($stmt_test_ville);
				$r=oci_fetch_array($stmt_test_ville,OCI_RETURN_NULLS+OCI_ASSOC);
				$longitude=$r['LONGITUDE'];
				$latitude=$r['LATITUDE'];
			}
		}
	}
	return array($latitude,$longitude,$country_modified);
}



function update_column_enrich_text($document_num,$context,$certainty) {
	global $dbh;
	
	$text='';
	$requete_cui="select text from dwh_text where  document_num=$document_num and context='$context' and certainty=$certainty";
	$stmt=oci_parse($dbh,$requete_cui);
	oci_execute($stmt);
	$row=oci_fetch_array($stmt,OCI_RETURN_NULLS+OCI_ASSOC);
	if ($row['TEXT']!='') {
		$text=$row['TEXT']->load();
	}
	
	$liste_concepts="";
	$requete_cui="select distinct concept_str from dwh_enrsem, dwh_thesaurus_enrsem where context='$context' and certainty=$certainty and  dwh_enrsem.concept_code=dwh_thesaurus_enrsem.concept_code and dwh_enrsem.document_num=$document_num  ";
	$stmt=oci_parse($dbh,$requete_cui);
	oci_execute($stmt);
	while ($row=oci_fetch_array($stmt,OCI_RETURN_NULLS+OCI_ASSOC)){
		$concept_str=$row['CONCEPT_STR'];
		$liste_concepts.=" $concept_str .";
	}
	
	$liste_concepts_peres="";
	if ($certainty==1) {
		$requete_cui="select distinct concept_str from dwh_enrsem, dwh_thesaurus_enrsem,dwh_thesaurus_enrsem_graph graph where 
			  context='$context' and certainty=$certainty and  graph.concept_code_father=dwh_thesaurus_enrsem.concept_code 
			and  dwh_enrsem.concept_code = graph.concept_code_son  and dwh_enrsem.document_num=$document_num ";
		$stmt=oci_parse($dbh,$requete_cui);
		oci_execute($stmt);
		while ($row=oci_fetch_array($stmt,OCI_RETURN_NULLS+OCI_ASSOC)){
			$concept_str=$row['CONCEPT_STR'];
			$liste_concepts_peres.=" $concept_str .";
		}
	}
	
	$enrich_text="$text . $liste_concepts . $liste_concepts_peres";
	$requeteins="update dwh_text set enrich_text=:enrich_text where document_num=$document_num and context='$context' and certainty=$certainty  ";
	$stmtupd = ociparse($dbh,$requeteins);
	$rowid = ocinewdescriptor($dbh, OCI_D_LOB);
	ocibindbyname($stmtupd, ":enrich_text",$enrich_text );
	$execState = ociexecute($stmtupd);
	ocifreestatement($stmtupd);
}



function update_preferred_term ($code_filtre) {
	global $dbh;
	
	if ($code_filtre!='') {
		$req1=" where concept_code='$code_filtre' ";
		$req2=" and concept_code='$code_filtre' ";
	} else {
		$req1="";
		$req2="";
	}
	
	$req="update dwh_thesaurus_enrsem set count_doc_concept_str=0, count_patient=0,count_patient_subsumption=0,PREF= NULL $req1";
	$stmtreq=oci_parse($dbh,$req);
	oci_execute($stmtreq);
	
	$requete_cui="select  lower(concept_str_found) concept_str_found, count (distinct document_num) nb_document ,concept_code from dwh_enrsem where context='patient_text' and certainty=1 $req2 group by lower(concept_str_found),concept_code";
	$stmt=oci_parse($dbh,$requete_cui);
	oci_execute($stmt);
	while ($row=oci_fetch_array($stmt,OCI_RETURN_NULLS+OCI_ASSOC)){
		$concept_str_found=$row['CONCEPT_STR_FOUND'];
		$concept_code=$row['CONCEPT_CODE'];
		$nb_document=$row['NB_DOCUMENT'];
		$concept_str_found=preg_replace("/'/","''",$concept_str_found);
		
		$req="update dwh_thesaurus_enrsem set count_doc_concept_str=$nb_document where concept_code='$concept_code' and lower(concept_str)='$concept_str_found'";
		$stmtreq=oci_parse($dbh,$req);
		oci_execute($stmtreq);
	}

	
	$requete_cui="select distinct concept_code from dwh_thesaurus_enrsem  $req1";
	$stmt=oci_parse($dbh,$requete_cui);
	oci_execute($stmt);
	while ($row=oci_fetch_array($stmt,OCI_RETURN_NULLS+OCI_ASSOC)){
		$concept_code=$row['CONCEPT_CODE'];
		$req=" update dwh_thesaurus_enrsem set PREF='Y' where thesaurus_enrsem_num in (
			          select max(thesaurus_enrsem_num) from dwh_thesaurus_enrsem where concept_code= '$concept_code' and count_doc_concept_str in (          
			          select max(count_doc_concept_str) from dwh_thesaurus_enrsem where concept_code= '$concept_code')) and concept_code= '$concept_code' ";
		$stmtreq=oci_parse($dbh,$req);
		oci_execute($stmtreq);
		
		$requeteupd="update dwh_thesaurus_enrsem set count_patient=( select count(distinct patient_num) from  dwh_enrsem where context='patient_text' and certainty=1 and dwh_enrsem.concept_code='$concept_code' ) where concept_code='$concept_code'";
		$stmtupd = ociparse($dbh,$requeteupd);
		$execState = ociexecute($stmtupd) || die (get_translation('ERROR','erreur')." : ".get_translation('INSERT','insert')." : $requeteupd\n");
		ocifreestatement($stmtupd);
		
		$requeteupd="update dwh_thesaurus_enrsem set count_patient_subsumption=( select count(distinct patient_num) from (select patient_num from  dwh_enrsem where context='patient_text' and certainty=1 and dwh_enrsem.concept_code='$concept_code'
                                               union 
                                               select patient_num from  dwh_enrsem,dwh_thesaurus_enrsem_graph  where concept_code_father='$concept_code' and context='patient_text' and certainty=1 and concept_code=concept_code_son )
                                               t ) where concept_code='$concept_code'";
		$stmtupd = ociparse($dbh,$requeteupd);
		$execState = ociexecute($stmtupd) || die (get_translation('ERROR','erreur')." : ".get_translation('INSERT','insert')." : $requeteupd\n");
		ocifreestatement($stmtupd);
	}
}



function lister_services_nbpatient_manager_department ($tmpresult_num,$query_num) {
	global $dbh,$user_num_session;
	
	$tableau_patient_num_deja=array();
	print "<strong> ".get_translation('PATIENTS_NOT_SEEN_IN_YOUR_HOSPITAL_DEPARTMENT','Patients non vus dans vos services')." :</strong>";
	print "
	<table cellspacing=2 cellpadding=0 border=0 class=tablefin>";
	print "<thead>
	<th>Service</th>
	<th>".get_translation('COUNT_PATIENTS_IN_HOSPITAL_DEPARTMENT','Nb patients avec comptes rendus')."</th>
	<th>".get_translation('CONTACT','Contact')."</th>
	</thead>
	<tbody>";
	$req=" SELECT department_num,count( distinct patient_num) as NB_PATIENT from  dwh_tmp_resultall_$user_num_session WHERE tmpresult_num = $tmpresult_num and department_num is not null
    and patient_num not in (SELECT patient_num from  dwh_tmp_result_$user_num_session WHERE tmpresult_num = $tmpresult_num ) GROUP BY department_num order by count(distinct patient_num) desc";
	$sel=oci_parse($dbh,"$req");
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)){
		$department_num=$r['DEPARTMENT_NUM'];
		$nb_patient=$r['NB_PATIENT'];
		$department_str=get_department_str ($department_num);
		print "<tr><td>$department_str</td><td>$nb_patient</td><td><table border=\"0\">";
		$list_department_managers=get_list_department_managers ($department_num);
		foreach ($list_department_managers as $department_manager) {
			$user_num=$department_manager['USER_NUM'];
               		$firstname_lastname=get_user_information ($user_num,'pn');
			print "<tr><td border=\"0\">$firstname_lastname</td><td border=\"0\">";
			print " <span id=\"id_span_action_demande_acces_".$department_num."_".$user_num."\"><a href=\"#\" onclick=\"demande_acces_patient('$department_num','$tmpresult_num','$user_num',$query_num);return false;\">".get_translation('REQUEST','Demander')."</a></span>";
			print " </td></tr>";
		}
		
		print "</table></td>";
		
		print "</tr>";
	}
	print "</tbody></table><br><br>";



	if(1==2) {
	
	$req="
		SELECT patient_num,department_num from  dwh_tmp_result_$user_num_session
		                          WHERE tmpresult_num = $tmpresult_num and department_num is not null and not exists (select patient_num 
		                           FROM dwh_patient_department
		                          WHERE dwh_patient_department.patient_num=dwh_tmp_result_$user_num_session.patient_num and department_num IN (SELECT department_num
		                                          FROM dwh_user_department
		                                         WHERE user_num = $user_num_session))
		union all
		SELECT patient_num,department_num from dwh_patient_department where patient_num in (select patient_num 
		                           FROM dwh_tmp_result_$user_num_session
		                          WHERE tmpresult_num = $tmpresult_num and department_num is  null and not exists (select patient_num 
		                           FROM dwh_patient_department
		                          WHERE dwh_patient_department.patient_num=dwh_tmp_result_$user_num_session.patient_num and department_num IN (SELECT department_num
		                                          FROM dwh_user_department
		                                         WHERE user_num = $user_num_session))
		                          )
                        ";

		$sel=oci_parse($dbh,"$req");
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)){
		$department_num=$r['DEPARTMENT_NUM'];
		$patient_num=$r['PATIENT_NUM'];
		$tableau_patient_service[$department_num][$patient_num]='ok';
	}






	$tableau_patient_num_deja=array();
	print "<strong style=\"cursor:pointer;\" onclick=\"plier_deplier('id_patient_hors_service')\"><span id=\"plus_id_patient_hors_service\">+</span> ".get_translation('PATIENTS_NOT_SEEN_IN_YOUR_HOSPITAL_DEPARTMENT','Patients non vus dans vos services')." :</strong>";
	print "
	<div id=\"id_patient_hors_service\" style=\"display:none;\">
	<table cellspacing=2 cellpadding=0 border=0 class=tablefin>";
	print "<thead>
	<th>Service</th>
	<th>".get_translation('COUNT_NEW_PATIENTS_IN_HOSPITAL_DEPARTMENT','Nb patients nouveaux dans le service')."</th>
	<th>".get_translation('COUNT_PATIENT_ALREADY_INCLUDED_IN_ABOVE_HOSPITAL_DEPARTMENT','Nb patients déjà inclus dans un service au dessus')."</th>
	<th>".get_translation('CONTACT','Contact')."</th>
	</thead>
	<tbody>";
	$req="SELECT department_num, COUNT (*) AS nb_patient
    FROM  (
    (
SELECT patient_num,department_num from  dwh_tmp_result_$user_num_session
                          WHERE tmpresult_num = $tmpresult_num and department_num is not null and not exists (select patient_num 
                           FROM dwh_patient_department
                          WHERE dwh_patient_department.patient_num=dwh_tmp_result_$user_num_session.patient_num and department_num IN (SELECT department_num
                                          FROM dwh_user_department
                                         WHERE user_num = $user_num_session))
union
SELECT patient_num,department_num from dwh_patient_department where patient_num in (select patient_num 
                           FROM dwh_tmp_result_$user_num_session
                          WHERE tmpresult_num = $tmpresult_num and department_num is  null and not exists (select patient_num 
                           FROM dwh_patient_department
                          WHERE dwh_patient_department.patient_num=dwh_tmp_result_$user_num_session.patient_num and department_num IN (SELECT department_num
                                          FROM dwh_user_department
                                         WHERE user_num = $user_num_session))
                          )
                          )
                        ) t
GROUP BY department_num
order by count(*) desc";
		$sel=oci_parse($dbh,"$req");
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)){
		$department_num=$r['DEPARTMENT_NUM'];
		$nb_patient=$r['NB_PATIENT'];
		
		$nb_patient_deja=0;
		$nb_patient_deja_pas=0;
		foreach ($tableau_patient_service[$department_num] as $num_patient => $ok) {
			if ($tableau_patient_num_deja[$num_patient]=='ok') {
				$nb_patient_deja++;
			} else {
				$nb_patient_deja_pas++;
			}	
		}
		foreach ($tableau_patient_service[$department_num] as $num_patient => $ok) {
			$tableau_patient_num_deja[$num_patient]='ok';
		}
		
		$department_str=get_department_str ($department_num);
		
		print "<tr><td>$department_str</td><td>$nb_patient_deja_pas</td><td>$nb_patient_deja</td><td><table border=\"0\">";
		$sel_service=oci_parse($dbh,"select  lastname,firstname,mail,dwh_user_department.user_num from dwh_user_department,dwh_user where dwh_user_department.department_num=$department_num and dwh_user_department.user_num=dwh_user.user_num and manager_department=1");
		oci_execute($sel_service);
		while ($r_service=oci_fetch_array($sel_service,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$lastname=strtoupper($r_service['LASTNAME']);
			$firstname=$r_service['FIRSTNAME'];
			$mail=$r_service['MAIL'];
			$num_user_manager_department=$r_service['USER_NUM'];
			print "<tr><td border=\"0\">$lastname $firstname</td><td border=\"0\">";
			if ($num_user_manager_department!='') {
				print " <span id=\"id_span_action_demande_acces_".$department_num."_".$num_user_manager_department."\"><a href=\"#\" onclick=\"demande_acces_patient('$department_num','$tmpresult_num','$num_user_manager_department',$query_num);return false;\">".get_translation('REQUEST','Demander')."</a></span>";
			} else {
			}
			print " </td></tr>";
		}
		print "</table></td>";
		
		print "</tr>";
	}
	print "</tbody></table></div><br><br>";
	}
}


function get_list_department_managers ($department_num) {
        global $dbh;
	$sel_service=oci_parse($dbh,"select user_num from dwh_user_department where department_num=$department_num and manager_department=1");
	oci_execute($sel_service);
	$nb=oci_fetch_all($sel_service,$res, null, null, OCI_FETCHSTATEMENT_BY_ROW); 
	return $res;
}
function lister_mes_demandes ($user_num,$option,$action) {
        global $dbh;
        if ($action=='mes_demandes') {
        	$filtre="user_num_request=$user_num ";
        }
        if ($action=='a_traiter') {
        	$filtre="nuser_num_department_manager=$user_num ";
        }
        if ($option=='attente') {
        	$filtre.=" and manager_agreement is null   ";
        }
        if ($option=='ok') {
        	$filtre.=" and manager_agreement=1  ";
        }
        if ($option=='pasok') {
        	$filtre.=" and manager_agreement=-1  ";
        }
        print "<table border=0>";
	$sel=oci_parse($dbh,"select  request_access_num,user_num_request,department_num,nuser_num_department_manager,readable_query,to_char(request_access_date,'DD/MM/YYYY HH24:MI') as request_access_date_char, request_access_date,viewed_by_manager_date,viewed_by_manager_ok_date,viewed_by_manager_notok_date  from dwh_request_access where $filtre order by request_access_date desc");
        oci_execute($sel);
        while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $request_access_num=$r['REQUEST_ACCESS_NUM'];
                $user_num_request=$r['USER_NUM_REQUEST'];
                $department_num=$r['DEPARTMENT_NUM'];
                $nuser_num_department_manager=$r['NUSER_NUM_DEPARTMENT_MANAGER'];
                $readable_query=$r['READABLE_QUERY']->load();
                $request_access_date_char=$r['REQUEST_ACCESS_DATE_CHAR'];
                $viewed_by_manager_notok_date=$r['VIEWED_BY_MANAGER_NOTOK_DATE'];
                $viewed_by_manager_ok_date=$r['VIEWED_BY_MANAGER_OK_DATE'];
                $viewed_by_manager_date=$r['VIEWED_BY_MANAGER_DATE'];
                $user_name_manager_department=get_user_information ($nuser_num_department_manager,'pn');
                $user_name_demandeur=get_user_information ($user_num_request,'pn');
                $new='';
	        if ($action=='mes_demandes') {
	        	$lastname_user="$user_name_manager_department";
		        if ($option=='pasok' && $viewed_by_manager_notok_date=='') {
		        	$new="<strong style=\"color:red\">*</strong>";
		        }
		        if ($option=='ok' && $viewed_by_manager_ok_date=='') {
		        	$new="<strong style=\"color:red\">*</strong>";
		        }
	        }
	        if ($action=='a_traiter') {
	        	$lastname_user="$user_name_demandeur";
		        if ($viewed_by_manager_date=='') {
		        	$new="<strong style=\"color:red\">*</strong>";
		        }
	        }
         	print "<tr><td><a href=\"mes_demandes.php?action=$action&request_access_num=$request_access_num\">$request_access_date_char</a></td><td><a href=\"mes_demandes.php?action=$action&request_access_num=$request_access_num\">$lastname_user</a>$new</td></tr>";
        }
	print "</table>";
}

function update_access_request ($request_access_num) {
        global $dbh,$user_num_session;
	$autorisation_demande_voir=autorisation_demande_voir ($request_access_num,$user_num_session);
	if ($autorisation_demande_voir=='ok') {
		$sel=oci_parse($dbh,"select  user_num_request,nuser_num_department_manager, manager_agreement from dwh_request_access where request_access_num=$request_access_num");
	        oci_execute($sel);
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$user_num_request=$r['USER_NUM_REQUEST'];
		$nuser_num_department_manager=$r['NUSER_NUM_DEPARTMENT_MANAGER'];
		$manager_agreement=$r['MANAGER_AGREEMENT'];

		if ($nuser_num_department_manager==$user_num_session) {
			$req="update   dwh_request_access set viewed_by_manager_date=sysdate where request_access_num=$request_access_num ";
			$upd=oci_parse($dbh,$req);
			oci_execute($upd) || die (get_translation('ERROR','erreur')." : ".get_translation('ACCESS_REQUEST_NOT_DELETED',"Demande d'accès non supprimée")."<br>");
		}
		
		// sauve la date pour laquelle le demandeur a vu que l'accord avait été donné
		if ($user_num_request==$user_num_session && $manager_agreement==1 ) {
			$req="update   dwh_request_access set viewed_by_manager_ok_date=sysdate where request_access_num=$request_access_num ";
			$upd=oci_parse($dbh,$req);
			oci_execute($upd) || die (get_translation('ERROR','erreur')." : ".get_translation('ACCESS_REQUEST_NOT_DELETED',"Demande d'accès non supprimée")."<br>");
		}
		if ($user_num_request==$user_num_session && $manager_agreement==-1 ) {
			$req="update dwh_request_access set viewed_by_manager_notok_date=sysdate where request_access_num=$request_access_num ";
			$upd=oci_parse($dbh,$req);
			oci_execute($upd) || die (get_translation('ERROR','erreur')." : ".get_translation('ACCESS_REQUEST_NOT_DELETED',"Demande d'accès non supprimée")."<br>");
		}
	}
}


function display_a_request ($request_access_num) {
        global $dbh,$user_num_session;
	$autorisation_demande_voir=autorisation_demande_voir ($request_access_num,$user_num_session);
	if ($autorisation_demande_voir=='ok') {
		$sel=oci_parse($dbh,"select  request_access_num,user_num_request,department_num,nuser_num_department_manager,readable_query,to_char(request_access_date,'DD/MM/YYYY HH24:MI') as request_access_date_char, manager_agreement,to_char(manager_agreement_date,'DD/MM/YYYY HH24:MI') as manager_agreement_date_char  from dwh_request_access where request_access_num=$request_access_num");
	        oci_execute($sel);
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$request_access_num=$r['REQUEST_ACCESS_NUM'];
		$user_num_request=$r['USER_NUM_REQUEST'];
		$department_num=$r['DEPARTMENT_NUM'];
		$nuser_num_department_manager=$r['NUSER_NUM_DEPARTMENT_MANAGER'];
		$readable_query=$r['READABLE_QUERY']->load();
		$request_access_date_char=$r['REQUEST_ACCESS_DATE_CHAR'];
		$manager_agreement=$r['MANAGER_AGREEMENT'];
		$manager_agreement_date_char=$r['MANAGER_AGREEMENT_DATE_CHAR'];
		
		$user_name_manager_department=get_user_information ($nuser_num_department_manager,'pn');
		$user_name_demandeur=get_user_information ($user_num_request,'pn');
		$department_str=get_department_str ($department_num);
		
		print "<h1>".get_translation('REQUEST_DATED','Demande du')." $request_access_date_char";
		if ($user_num_request==$user_num_session) {
			print "<img border=\"0\" style=\"cursor:pointer;vertical-align:middle\" onclick=\"supprimer_demande_acces($request_access_num);\" src=\"images/poubelle_moyenne.png\">";
		}
		print "</h1>
		<table border=0>";
		print "<tr><td><strong>".get_translation('QUERY','Requête')." : </strong></td><td>$readable_query</td></tr>";
		print "<tr><td><strong>".get_translation('REQUEST_APPLICANT','Demandeur')." : </strong></td><td>$user_name_demandeur</td></tr>";
		print "<tr><td><strong>".get_translation('HOSPITAL_DEPARTMENT','Service')." : </strong></td><td>$department_str</td></tr>";
		print "<tr><td><strong>".get_translation('PERSON_IN_CHARGE','Responsable')." : </strong></td><td>$user_name_manager_department</td></tr>";
	        $sel=oci_parse($dbh,"select  count(*) NB_PATIENT from dwh_request_access_patient where request_access_num=$request_access_num");
	        oci_execute($sel);
	        $r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	        $nb_patient=$r['NB_PATIENT'];
		print "<tr><td><strong>".get_translation('PATIENTS_NUMBER','Nombre de patients')." : </strong></td><td> $nb_patient </td></tr>";
		
		if ($manager_agreement=='') {
			print "<tr><td><strong>".get_translation('APPROVAL','Autorisation')." : </strong></td><td> ".get_translation('AWAITING','En attente')."</td></tr>";
			$check_en_attente='checked';
		}
		
		if ($manager_agreement==-1) {
			print "<tr><td><strong>".get_translation('APPROVAL','Autorisation')." : </strong></td><td><font style=\"color:red\">".get_translation('NOT_AUTHORIZED','Non autorisée')." (le $manager_agreement_date_char)</font></td></tr>";
			$check_non_autorise='checked';
		}
		if ($manager_agreement==1) {
			print "<tr><td><strong>".get_translation('APPROVAL','Autorisation')." : </strong></td><td><font style=\"color:green\">".get_translation('APPROVED','Autorisée')." (le $manager_agreement_date_char)</font></td></tr>";
			$check_autorise='checked';
		}
		
		if ($nuser_num_department_manager==$user_num_session) {
			print "<tr><td ><strong>".get_translation('DECISION','Décision')." : </strong></td>
			<td> 
			<input name=manager_agreement type=radio onclick=\"autoriser_demande_acces($request_access_num,'1');\" $check_autorise> ".get_translation('APPROVE','Autoriser')."  <br>
			<input name=manager_agreement type=radio onclick=\"autoriser_demande_acces($request_access_num,'-1');\" $check_non_autorise> ".get_translation('DO_NOT_AUTHORIZE','Ne pas autoriser')." <br>
			<input name=manager_agreement type=radio onclick=\"autoriser_demande_acces($request_access_num,'');\" $check_en_attente> ".get_translation('AWAITING','En attente')." </td></tr>
			";
		}
		print "</table>";
		
		$autorisation_demande_voir_patient=autorisation_demande_voir_patient ($request_access_num,$user_num_session);
		//if ($manager_agreement==1 || $nuser_num_department_manager==$user_num_session) {
		if ($autorisation_demande_voir_patient=='ok') {
			$res_copier_coller='';
			print "<h2>".get_translation('PATIENTS_LIST','Liste des patients')." : </h2>";
			
			print "<a href=\"export_excel.php?request_access_num=$request_access_num\"><img src=\"images/excel_noir.png\" style=\"cursor:pointer;width:25px;\" title=\"Export Excel\" alt=\"Export Excel\" border=\"0\"></a> ";
	 		print " <img src=\"images/copier_coller.png\" onclick=\"plier_deplier('id_div_tableau_patient_demande_acces_encours');plier_deplier('id_div_textarea_patient_demande_acces_encours');fnSelect('id_div_textarea_patient_demande_acces_encours');\" style=\"cursor:pointer;\" title=\"Copier Coller pour importer dans une cohorte\" alt=\"Copier Coller pour importer dans une cohorte\"> ";
			print "<div  id=\"id_div_tableau_patient_demande_acces_encours\" style=\"display:block;\">";

			print "<table id=\"id_tableau_patient_demande\" class=\"tableau_cohorte\">";
		        $sel=oci_parse($dbh,"select dwh_request_access_patient.patient_num,lastname from dwh_request_access_patient,dwh_patient where request_access_num=$request_access_num and  dwh_request_access_patient.patient_num=dwh_patient.patient_num order by lastname");
		        oci_execute($sel);
		        while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		                $patient_num=$r['PATIENT_NUM'];
				print "<tr id=\"id_tr_patient_demande_$patient_num\" onmouseover=\"this.style.backgroundColor='#dcdff5';\" onmouseout=\"this.style.backgroundColor='#ffffff';\"><td>";
				print afficher_patient($patient_num,'demande_acces','','');
				$res_copier_coller.=afficher_patient($patient_num,'demande_acces_copier_coller','','');
				if ($user_num_request==$user_num_session) {
					print "</td><td><img src=\"images/noninclure_patient_cohorte.png\" border=\"0\" width=\"15px\" onclick=\"supprimer_patient_demande_acces($request_access_num,$patient_num);\" style=\"cursor:pointer\" alt=\"Supprimer de la liste\" title=\"Supprimer de la liste\">";
				}
				print "</td></tr>";
		        }
		        print "</table>";
		        print "</div>";
		        
			print "<pre id=\"id_div_textarea_patient_demande_acces_encours\" style=\"display:none;\" >";
		        print "$res_copier_coller";
		        print "</pre>";
		        
		}
	}
}

function calcul_nb_comment_cohorte($patient_num,$cohort_num) {
        global $dbh,$user_num_session;
        $nb_comment_cohorte='';
	if ($patient_num!='' && $cohort_num!='') {
		$sel=oci_parse($dbh,"select  count(*) as nb_comment_cohorte  from dwh_cohort_result_comment where cohort_num=$cohort_num and patient_num=$patient_num");
	        oci_execute($sel);
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$nb_comment_cohorte=$r['NB_COMMENT_COHORTE'];
	}
	return $nb_comment_cohorte;
}

function lister_commentaire_patient_cohorte ($cohort_num,$patient_num,$context) {
	global $dbh,$user_num_session;
	$res='';
	$sel=oci_parse($dbh,"select cohort_result_comment_num, cohort_num, patient_num, to_char(comment_date,'DD/MM/YYYY') date_comment_char, user_num, commentary,comment_date 
				from dwh_cohort_result_comment where cohort_num=$cohort_num and patient_num=$patient_num order by cohort_result_comment_num asc");
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$cohort_result_comment_num=$r['COHORT_RESULT_COMMENT_NUM'];
		$cohort_num=$r['COHORT_NUM'];
		$patient_num=$r['PATIENT_NUM'];
		$date_comment_char=$r['DATE_COMMENT_CHAR'];
		$user_num=$r['USER_NUM'];
		$commentary=$r['COMMENTARY'];
		$user_name_comment=get_user_information($user_num,'pn');
		if ($context=='excel') {
			$commentary =preg_replace("/\n|<br>/",".",$commentary );
			$res.= get_translation('THE_DATE','Le')." $date_comment_char, $user_name_comment : $commentary | ";
		} else {
			if ($user_num==$user_num_session && $context!='lister_commentaire') {
				$action_supprimer="<img border=\"0\" style=\"vertical-align:middle;cursor:pointer;\" src=\"images/poubelle_moyenne.png\" width=\"13px\" onclick=\"supprimer_commentaire_cohorte('$cohort_result_comment_num','$context','$patient_num','$cohort_num');\">";
			} else {
				$action_supprimer="";
			}
			$res.= "
			<div id=\"id_commentaire_cohorte_".$context."_$cohort_result_comment_num\">
				<table border=\"0\" width=\"100%\"><tr><td nowrap=nowrap>".get_translation('THE_DATE','Le')." $date_comment_char, $user_name_comment</td><td style=\"text-align:right;\">$action_supprimer</td></tr></table>
				$commentary
				
			</div><hr>";
		}
	}
	$res=substr($res,0,-4);
	return $res;
}




function lister_tous_les_commentaires_patient_cohorte ($cohort_num) {
        global $dbh,$user_num_session,$datamart_num;
        $res='';
        if ($cohort_num!='') {
	        $autorisation_voir_patient_cohorte=verif_autorisation_voir_patient_cohorte($cohort_num,$user_num_session);
	        if ( $autorisation_voir_patient_cohorte=='ok') {
		        $tableau_patient_comment_deja_affiche='';
		        $res.= "<table id=\"id_tableau_patient_cohorte_commentaire\" class=\"tableau_cohorte\">";
		        $sel=oci_parse($dbh,"select dwh_cohort_result.patient_num,user_num from dwh_cohort_result,dwh_cohort_result_comment where dwh_cohort_result.cohort_num=$cohort_num and dwh_cohort_result_comment.cohort_num=$cohort_num and dwh_cohort_result.patient_num=dwh_cohort_result_comment.patient_num order by user_num desc");
		        oci_execute($sel);
		        while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		                $patient_num=$r['PATIENT_NUM'];
		                if ($tableau_patient_comment_deja_affiche[$patient_num]=='') {
		                	$tableau_patient_comment_deja_affiche[$patient_num]='ok';
			                $info_patient=afficher_patient($patient_num,'basique','',$cohort_num);
			                $res.= "<tr id=\"id_tr_patient_cohorte_commentaire_$patient_num\" onmouseover=\"this.style.backgroundColor='#dcdff5';\" onmouseout=\"this.style.backgroundColor='#ffffff';\"><td>$info_patient</td>";
			                $res.="<td>";
			                
				        $res.=lister_commentaire_patient_cohorte ($cohort_num,$patient_num,'lister_commentaire');
			                $res.="</td></tr>
			                <tr><td colspan=\"2\"><hr></td></tr>";
		                }
		        }
		        $res.= "</table>";
		}
	}
        return $res;
}


function save_log_query($user_num,$query_context,$xml_query,$patient_num) {
        global $dbh;
        if ($xml_query!='') {
		$requeteins="insert into dwh_log_query (user_num , log_date , query_context ,xml_query,patient_num ) values ($user_num,sysdate,'$query_context',:xml_query,'$patient_num') ";
		$ins = ociparse($dbh,$requeteins);
		$rowid = ocinewdescriptor($dbh, OCI_D_LOB);
		ocibindbyname($ins, ":xml_query",$xml_query );
		$execState = ociexecute($ins);
		ocifreestatement($ins);
	}
}


function save_log_document($document_num,$user_num,$nominative) {
        global $dbh;
	$requeteins="insert into dwh_log_document (document_num , user_num , log_date ,nominative ) values ($document_num,$user_num,sysdate,'$nominative') ";
	$ins = ociparse($dbh,$requeteins);
	$execState = ociexecute($ins);
	ocifreestatement($ins);
}


function save_log_page($user_num,$page) {
        global $dbh;
	$requeteins="insert into dwh_log_page ( user_num , log_date ,page ) values ($user_num,sysdate,'$page') ";
	$ins = ociparse($dbh,$requeteins);
	$execState = ociexecute($ins);
	ocifreestatement($ins);
}

function sauver_notification ($user_num_sent,$user_num_receiver,$notification_type,$notification_text,$id_event) {
	global $dbh;
        
	$notification_text=preg_replace ("/'/","''",$notification_text);
	$notification_text=preg_replace ("/\"/"," ",$notification_text);
	$req="insert into dwh_notification (notification_num,user_num_sent ,user_num_receiver ,notification_type ,notification_text ,shared_element_num ,notification_date) 
				values (dwh_seq.nextval,$user_num_sent,$user_num_receiver,'$notification_type','$notification_text','$id_event',sysdate)";
	$ins=oci_parse($dbh,$req);
	oci_execute($ins) || die ("erreur :  notification non ajoutee<br>");
}

function get_num_patient_from_id_document ($document_num) {
        global $dbh;
	if ($document_num!='') {
		$sel=oci_parse($dbh,"select patient_num from  dwh_document where document_num=$document_num");
	        oci_execute($sel);
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$patient_num=$r['PATIENT_NUM'];
	}
	return $patient_num;
}

function get_master_patient_id ($patient_num) {
        global $dbh;
        $hospital_patient_id='';
        $patient_num=trim($patient_num);
	if ($patient_num!='') {
		$sel=oci_parse($dbh,"select hospital_patient_id from  dwh_patient_ipphist where patient_num=$patient_num and master_patient_id=1");
	        oci_execute($sel);
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$hospital_patient_id=$r['HOSPITAL_PATIENT_ID'];
	}
	return $hospital_patient_id;
}

function get_master_patient_id_sih ($patient_num) {
        global $dbh;
        $hospital_patient_id='';
        $patient_num=trim($patient_num);
	if ($patient_num!='') {
		$sel=oci_parse($dbh,"select hospital_patient_id from  dwh_patient_ipphist where patient_num=$patient_num and master_patient_id=1 and origin_patient_id='SIH'");
	        oci_execute($sel);
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$hospital_patient_id=$r['HOSPITAL_PATIENT_ID'];
	}
	return $hospital_patient_id;
}
function get_patient_num ($hospital_patient_id,$origin_patient_id='') {
        global $dbh;
        $patient_num='';
        $hospital_patient_id=trim($hospital_patient_id);
        if ($origin_patient_id!='') {
		$req_origin_patient_id="and origin_patient_id='$origin_patient_id' ";
        } else {
		$req_origin_patient_id="";
        }
	if ($hospital_patient_id!='') {
		$sel=oci_parse($dbh,"select patient_num from dwh_patient_ipphist where hospital_patient_id='$hospital_patient_id' $req_origin_patient_id");
	        oci_execute($sel);
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$patient_num=$r['PATIENT_NUM'];
		
		if ($patient_num=='' && !preg_match("/^0/",$hospital_patient_id)) {
			$patient_num=get_patient_num ('0'.$hospital_patient_id,$origin_patient_id);
		}
	}
	return $patient_num;
}
		
function get_encounter_info ($encounter_num) {
	global $dbh;
	if ($encounter_num!='') {
		$requete="select 
			to_char(entry_date,'DD/MM/YYYY HH24:MI') entry_date,
			to_char(out_date,'DD/MM/YYYY HH24:MI') out_date  ,
			entry_mode,
			out_mode,
			round(out_date-entry_date) as encounter_length
			from dwh_patient_stay 
			where encounter_num='$encounter_num'";
		$sel=oci_parse($dbh,$requete);
		oci_execute($sel);
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC); 
		return $r;
	}
}
	
function get_mvt_info_by_encounter ($encounter_num,$order='asc') {
	global $dbh;
	$r=array();
	if ($encounter_num!='') {
		$requete="select 
			mvt_num,
			unit_num,
			department_num,
			to_char(entry_date,'DD/MM/YYYY HH24:MI') entry_date,
			to_char(out_date,'DD/MM/YYYY HH24:MI') out_date  ,
			mvt_entry_mode,
			mvt_exit_mode,
			type_mvt,
			mvt_order,
			round(out_date-entry_date) as mvt_length
			from dwh_patient_mvt 
			where encounter_num='$encounter_num' order by mvt_order $order";
		$sel=oci_parse($dbh,$requete);
		oci_execute($sel);
		$nb=oci_fetch_all($sel,$r, null, null, OCI_FETCHSTATEMENT_BY_ROW); 
	}
	return $r;
}
	
	
function get_mvt ($mvt_num) {
	global $dbh;
	$r=array();
	if ($mvt_num!='') {
		$requete="select encounter_num, 
		unit_code, 
		to_char(entry_date,'DD/MM/YYYY HH24:MI') entry_date,
		 to_char(out_date,'DD/MM/YYYY HH24:MI') out_date , 
		 department_num, unit_num, patient_num, mvt_num, 
		 mvt_entry_mode, mvt_exit_mode, type_mvt, 
		entry, out, mvt_order,
		round(out_date-entry_date) as mvt_length
			from dwh_patient_mvt 
			where mvt_num=$mvt_num ";
		$sel=oci_parse($dbh,$requete);
		oci_execute($sel);
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC); 
	}
	return $r;
}
	
function get_mvt_info_by_patient ($patient_num,$order='asc') {
	global $dbh;
	$r=array();
	if ($patient_num!='') {
		$requete="select 
			unit_num,
			department_num,
			encounter_num,
			to_char(entry_date,'DD/MM/YYYY HH24:MI') entry_date_ymdh24,
			to_char(out_date,'DD/MM/YYYY HH24:MI') out_date_ymdh24  ,
			mvt_entry_mode,
			mvt_exit_mode,
			type_mvt,
           		 entry_date as entry_date_order,
           		 out, 
           		 entry
			from dwh_patient_mvt 
			where patient_num='$patient_num'  order by entry_date $order, mvt_order $order ";
		$sel=oci_parse($dbh,$requete);
		oci_execute($sel);
		$nb=oci_fetch_all($sel,$r, null, null, OCI_FETCHSTATEMENT_BY_ROW); 
	}
	return $r;
}

function display_patient_mvt ($patient_num) {
	global $dbh;
	$res="<table class=\"tablefin\">
	<thead>
		<th>encounter_num</th>
		<th>entry_date_encounter</th>
		<th>entry_mode_encounter</th>
		<th>out_date_encounter</th>
		<th>out_mode_encounter</th>
		<th>type_mvt</th>
		<th>entry_date</th>
		<th>out_date</th>
		<th>unit_str</th>
		<th>type_mvt</th>
		<th>department_str</th>
	</thead>
	<tbody>";
	$list_mvt=get_mvt_info_by_patient ($patient_num,"asc");
	foreach ($list_mvt as $mvt) {
		$entry_date=$mvt['ENTRY_DATE_YMDH24'];
		$out_date=$mvt['OUT_DATE_YMDH24'];
		$department_num=$mvt['DEPARTMENT_NUM'];
		$encounter_num=$mvt['ENCOUNTER_NUM'];
		$type_mvt=$mvt['TYPE_MVT'];   
		$unit_num=$mvt['UNIT_NUM'];    
		$unit_code=$mvt['UNIT_CODE'];  
		$out=$mvt['OUT'];    
		$entry=$mvt['ENTRY'];  
		$department_str=get_department_str ($department_num);
		$unit_str=get_unit_str ($unit_num);
		
		if ($encounter_num!='' && $encounter_num!=$encounter_num_avant) {
			$encounter=get_encounter_info ($encounter_num);
			$entry_date_encounter=$encounter['ENTRY_DATE'];
			$out_date_encounter=$encounter['OUT_DATE'];
			$entry_mode_encounter=$encounter['ENTRY_MODE'];
			$out_mode_encounter=$encounter['OUT_MODE'];
		        $res.="<tr><td>$encounter_num</td><td>$entry_date_encounter</td><td>$entry_mode_encounter</td><td>$out_date_encounter</td><td>$out_mode_encounter</td>";
		} else {
		        $res.="<tr><td></td><td></td><td></td><td></td><td></td>";
		}
		if ($type_mvt=='C') {
			$type_mvt='Consultation';
		}
		if ($type_mvt=='J') {
			$type_mvt='HDJ';
		}
		if ($type_mvt=='U') {
			$type_mvt='Urgence';
		}
		if ($type_mvt=='H') {
			$type_mvt='Hospitalisation';
		}
		$res.="<td>$type_mvt</td><td>$entry_date</td><td>$out_date</td><td>$unit_str</td><td>$type_mvt</td><td>$department_str</td></tr>";
		$encounter_num_avant=$encounter_num;
	}
	$res.="</tbody></table>";
	return $res;
}


function get_encounter_info_by_patient ($patient_num,$order='asc') {
	global $dbh;
	$r=array();
	if ($patient_num!='') {
		$requete="select 
			encounter_num,
			to_char(entry_date,'DD/MM/YYYY HH24:MI') entry_date_YMDH24,
			to_char(out_date,'DD/MM/YYYY HH24:MI') out_date_YMDH24  ,
			to_char(entry_date,'DD/MM/YYYY') entry_date_dmy,
			to_char(out_date,'DD/MM/YYYY') out_date_dmy  ,
			entry_mode,
			out_mode, 
			entry_date
			from dwh_patient_stay 
			where patient_num='$patient_num'  order by entry_date $order";
		$sel=oci_parse($dbh,$requete);
		oci_execute($sel);
		$nb=oci_fetch_all($sel,$r, null, null, OCI_FETCHSTATEMENT_BY_ROW); 
	}
	return $r;
}


function insert_hospital_patient_id ($patient_num,$hospital_patient_id,$origin_patient_id,$master_patient_id) {
        global $dbh_etl;
	if ($hospital_patient_id!='') {
		$sel=oci_parse($dbh,"select hospital_patient_id from  dwh_patient_ipphist where hospital_patient_id='$hospital_patient_id' and patient_num=$patient_num ");
	        oci_execute($sel);
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$test_hospital_patient_id=$r['HOSPITAL_PATIENT_ID'];
		
		if ($test_hospital_patient_id=='') {
			$ins=oci_parse($dbh_etl,"insert into dwh_patient_ipphist (patient_num,hospital_patient_id,master_patient_id,origin_patient_id) values ($patient_num,'$hospital_patient_id',$master_patient_id,'$origin_patient_id')");
		        oci_execute($ins);
		} else {
			if ($master_patient_id==1) {
				$upd=oci_parse($dbh_etl,"update dwh_patient_ipphist set master_patient_id=0 where patient_num=$patient_num ");
			        oci_execute($upd);
				$upd=oci_parse($dbh_etl,"update dwh_patient_ipphist set master_patient_id=1 where hospital_patient_id='$hospital_patient_id' and patient_num=$patient_num ");
			        oci_execute($upd);
			}
		}
	}
}

function update_master_patient_id ($patient_num,$master_patient_id) {
        global $dbh_etl;
	if ($master_patient_id!='') {
		$upd=oci_parse($dbh_etl,"update dwh_patient_ipphist set master_patient_id=0 where patient_num=$patient_num ");
	        oci_execute($upd);
		$upd=oci_parse($dbh_etl,"update dwh_patient_ipphist set master_patient_id=1 where hospital_patient_id='$master_patient_id' and patient_num=$patient_num ");
	        oci_execute($upd);
	}
}



function maj_info_chargement ($document_origin_code) {
	global $dbh_etl,$upload_id;
	$req='';
	if ($document_origin_code!='') {
		$req="where document_origin_code='$document_origin_code'";
	}
	$requete_upd="delete from dwh_info_load where document_origin_code is null";
	$stmt_upd=oci_parse($dbh,$requete_upd);
	oci_execute($stmt_upd) || die ("$requete_upd \n");
	
	$requete_upd="delete from dwh_info_load $req";
	$stmt_upd=oci_parse($dbh,$requete_upd);
	oci_execute($stmt_upd) || die ("$requete_upd \n");
	
	$requete_upd="  insert into dwh_info_load 
	                     select count(distinct patient_num), count(*),null, null,null from dwh_document group by  null,null,null";
	$stmt_upd=oci_parse($dbh,$requete_upd);
	oci_execute($stmt_upd) || die ("$requete_upd \n");
	
	$requete_upd=" insert into dwh_info_load 
	                     select count(distinct patient_num), count(*), to_char(document_date,'YYYY'), document_origin_code,NULL from 
	                     dwh_document $req group by  to_char(document_date,'YYYY'), document_origin_code";
	$stmt_upd=oci_parse($dbh,$requete_upd);
	oci_execute($stmt_upd) || die ("$requete_upd \n");
	
	$requete_upd=" insert into dwh_info_load 
	                     select count(distinct patient_num), count(*), to_char(document_date,'YYYY'), document_origin_code, to_char(document_date,'MM') from 
	                     dwh_document $req group by  to_char(document_date,'YYYY'), document_origin_code, to_char(document_date,'MM')";
	$stmt_upd=oci_parse($dbh,$requete_upd);
	oci_execute($stmt_upd) || die ("$requete_upd \n");
	 
	$requete_upd="delete from dwh_info_enrsem";
	$stmt_upd=oci_parse($dbh,$requete_upd);
	oci_execute($stmt_upd) || die ("$requete_upd \n");
	
	$requete_upd="insert into dwh_info_enrsem select count(distinct patient_num) as NB_PATIENT from dwh_enrsem";
	$stmt_upd=oci_parse($dbh,$requete_upd);
	oci_execute($stmt_upd) || die ("$requete_upd \n");
	
		
	$requete="insert into dwh_etl_script (script , last_execution_date ,commentary ,count_document,document_origin_code ,upload_id) values ('maj_infoglobal.php' , sysdate ,'Mise à jour du nombre de docs par an et source' ,0,'$document_origin_code','$upload_id')  ";
	$ins=oci_parse($dbh,$requete);
	oci_execute($ins);
}  

function get_user_info ($user_num) {
	global $dbh;
	$tableau_user=array();
	
	/* lastname firstname login */

	
	$sel=oci_parse($dbh,"select  lastname,firstname,mail,login from dwh_user where user_num=$user_num");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$lastname=strtoupper($r['LASTNAME']);
	$firstname=$r['FIRSTNAME'];
	$mail=$r['MAIL'];
	$login=$r['LOGIN'];
	
	$tableau_user['lastname']=$lastname;
	$tableau_user['firstname']=$firstname;
	$tableau_user['login']=$login;
	$tableau_user['mail']=$mail;
	
	$table_department=get_list_departments('master',$user_num);
	foreach ($table_department as $department) {
		$department_str=$department['department_str'];
		$department_num=$department['department_num'];
		$tableau_user['liste_libelle_service'].="$department_str, ";
		$tableau_user['liste_department_num'].="$department_num,";
	}
	
	$sel=oci_parse($dbh,"select  user_profile  from dwh_user_profile  where user_num=$user_num");
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$user_profile=$r['USER_PROFILE'];
		$tableau_user['liste_user_profile'].="$user_profile, ";
	}
	
	$tableau_user['liste_libelle_service']=substr($tableau_user['liste_libelle_service'],0,-2);
	$tableau_user['liste_user_profile']=substr($tableau_user['liste_user_profile'],0,-2);
	$tableau_user['liste_department_num']=substr($tableau_user['liste_department_num'],0,-1);
	
	return $tableau_user;
	
}

function create_process ($process_num,$user_num,$status,$commentary,$result,$process_end_date,$category_process) {
	global $dbh;
	$commentary=supprimer_apost($commentary);
	$req="insert into dwh_process (process_num,status,user_num,commentary,result,process_end_date,category_process) values ('$process_num','$status','$user_num','$commentary',:result,$process_end_date,'$category_process')";
	$ins = ociparse($dbh,$req);
	$rowid = ocinewdescriptor($dbh, OCI_D_LOB);
	ocibindbyname($ins, ":result",$result);
	$execState = ociexecute($ins)||die ("ERreur  $req");
	ocifreestatement($ins);
}

function update_process ($process_num,$status,$commentary,$result,$user_num,$type_result) {
	global $dbh;
	if ($result=='NULL') { // on vide result
		$result='';
		$req="update dwh_process set status='$status',commentary=:commentary,result=:result,type_result=:type_result where process_num='$process_num' and user_num=$user_num";
	} else { // on concatene result, cela permet de vider le cache si nécessaire 
	
		$process=get_process($process_num);
		$result=$process['RESULT'].$result;
		$req="update dwh_process set status='$status',commentary=:commentary,result=:result,type_result=:type_result where process_num='$process_num' and user_num=$user_num";
	}
	$upd = ociparse($dbh,$req);
	$rowid = ocinewdescriptor($dbh, OCI_D_LOB);
	ocibindbyname($upd, ":commentary",$commentary);
	ocibindbyname($upd, ":result",$result);
	ocibindbyname($upd, ":type_result",$type_result);
	$execState = ociexecute($upd)||die ("ERreur  $req");
	ocifreestatement($upd);

}


function update_process_end_date ($process_num,$process_end_date,$user_num) {
	global $dbh;
	$commentary=supprimer_apost($commentary);
	$req="update dwh_process set process_end_date=$process_end_date where process_num='$process_num' and user_num=$user_num";
	$upd=oci_parse($dbh, $req);
	oci_execute($upd) ||die ("ERreur  $req");
	ocifreestatement($upd);
}

function get_process  ($process_num) {
	global $dbh;
	$req= "select status,commentary,result,user_num,process_num,to_char(process_end_date,'DD/MM/YYYY HH24:MI') as process_end_date_char,category_process,type_result from dwh_process where process_num='$process_num' ";
	$sel=oci_parse($dbh, $req);
	oci_execute($sel) ||die ("ERreur  $req");
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$process_num = $r['PROCESS_NUM'];
	$status = $r['STATUS'];
	$commentary = $r['COMMENTARY'];
	$user_num = $r['USER_NUM'];
	$process_end_date = $r['PROCESS_END_DATE_CHAR'];
	$category_process = $r['CATEGORY_PROCESS'];
	$type_result = $r['TYPE_RESULT'];
	$result='';
	if ($r['RESULT']) {
		$result = $r['RESULT']-> load();
	}
	
	$tableau['PROCESS_NUM']=$process_num;
	$tableau['USER_NUM']=$user_num;
	$tableau['STATUS']=$status;
	$tableau['COMMENTARY']=$commentary;
	$tableau['RESULT']=$result;
	$tableau['PROCESS_END_DATE']=$process_end_date;
	$tableau['CATEGORY_PROCESS']=$category_process;
	$tableau['TYPE_RESULT']=$type_result;
	return $tableau;
}

function get_all_my_process ($user_num,$category_process) {
	global $dbh;
	$req='';
	$tableau=array();
	$req_category="";
	if ($category_process!='') {
		$req_tmp="";
		$tab_category_process=explode(',',$category_process);
		foreach ($tab_category_process as $category_process) {
			$category_process=trim($category_process);
			if ($category_process!='') {
				if ($req_tmp=="") {
					$req_tmp.=" category_process='$category_process' ";
				} else {
					$req_tmp.=" or category_process='$category_process' ";
				}
			}
		}
		if ($req_tmp!='') {
			$req_category=" and ( $req_tmp)";
		}
	}
	$req= "select process_num,process_end_date from dwh_process where user_num=$user_num $req_category order by process_end_date desc";
	$sel=oci_parse($dbh, $req);
	oci_execute($sel) ||die ("ERreur  $req");
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$process_num = $r['PROCESS_NUM'];
		$tableau[]=$process_num;
	}
	return $tableau;
}

function get_outil ($tool_num) {
	global $dbh;
	if ($tool_num!='') {
		$req= "select tool_num, title,authors,description,url from dwh_tool where tool_num=$tool_num";
		$sel=oci_parse($dbh, $req);
		oci_execute($sel) ||die ("ERreur  $req");
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$tableau['TOOL_NUM']=$r['TOOL_NUM'];
		$tableau['TITLE']=$r['TITLE'];
		$tableau['AUTHORS']=$r['AUTHORS'];
		$tableau['DESCRIPTION']=$r['DESCRIPTION'];
		$tableau['URL']=$r['URL'];
		return $tableau;
	}
}

function insert_outil ($tableau) {
	global $dbh;
	
	$title=trim(supprimer_apost($tableau['TITLE']));
	$authors=trim(supprimer_apost($tableau['AUTHORS']));
	$description=trim(supprimer_apost($tableau['DESCRIPTION']));
	$url=trim(supprimer_apost($tableau['URL']));
	
        $tool_num=get_uniqid();
        
	$req= "insert into dwh_tool (tool_num, title,authors,description,url) values ($tool_num, '$title','$authors','$description','$url') ";
	$ins=oci_parse($dbh, $req);
	oci_execute($ins) ||die ("ERreur  $req");
	return $tool_num;
}


function update_outil ($tableau) {
	global $dbh;
	
	$tool_num=trim($tableau['TOOL_NUM']);
	$title=trim(supprimer_apost($tableau['TITLE']));
	$authors=trim(supprimer_apost($tableau['AUTHORS']));
	$description=trim(supprimer_apost($tableau['DESCRIPTION']));
	$url=trim(supprimer_apost($tableau['URL']));
	
	if ($tool_num!='') {
		$req= "update  dwh_tool set title='$title',authors='$authors',description='$description',url='$url' where tool_num=$tool_num";
		$ins=oci_parse($dbh, $req);
		oci_execute($ins) ||die ("ERreur  $req");
		
	}
}


function delete_outil ($tool_num) {
	global $dbh;
	if ($tool_num!='') {
		$del=oci_parse($dbh,"delete from dwh_tool where tool_num=$tool_num");
		oci_execute($del);
	}
}
function admin_lister_outil () {
	global $dbh;
	print "<table class=\"tablefin\">
	<thead>
		<tr>
		<td width=\"213\">".get_translation('TITLE','Titre')."</<td>
		<td width=\"213\">".get_translation('AUTHORS','Auteurs')."</td>
		<td width=\"332\">".get_translation('DESCRIPTION','Description')."</td>
		<td width=\"332\">".get_translation('URL','URL')."</td>
		<td width=\"10\"></td>
	</thead>
	<tbody>";
	$req= "select tool_num from dwh_tool order by tool_num";
	$sel=oci_parse($dbh, $req);
	oci_execute($sel) ||die ("ERreur  $req");
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$tool_num = $r['TOOL_NUM'];
		$outil=get_outil ($tool_num);
		$title = $outil['TITLE'];
		$authors = $outil['AUTHORS'];
		$description = $outil['DESCRIPTION'];
		$url = $outil['URL'];
		
		$description_br=preg_replace("/\n/","<br>",$description);
		$description_n=preg_replace("/<br>/","\n",$description);
		print "<tr id=\"id_tr_outil_$tool_num\">
			<td><span id=\"id_span_outil_titre_$tool_num\" onclick=\"plier('id_span_outil_titre_$tool_num');deplier('id_span_form_outil_titre_$tool_num','block');focus_form('id_input_outil_titre_$tool_num');\">$title</span><span style=\"display:none\" id=\"id_span_form_outil_titre_$tool_num\"><input type=\"text\" size=\"30\" id=\"id_input_outil_titre_$tool_num\" value=\"$title\" onblur=\"update_outil('$tool_num','title');\"></span></td>
			<td><span id=\"id_span_outil_authors_$tool_num\" onclick=\"plier('id_span_outil_authors_$tool_num');deplier('id_span_form_outil_authors_$tool_num','block');focus_form('id_input_outil_authors_$tool_num');\">$authors</span><span  style=\"display:none\" id=\"id_span_form_outil_authors_$tool_num\"><input type=\"text\" size=\"30\" id=\"id_input_outil_authors_$tool_num\" value=\"$authors\" onblur=\"update_outil('$tool_num','authors');\"></span></td>
			<td><span id=\"id_span_outil_description_$tool_num\" onclick=\"plier('id_span_outil_description_$tool_num');deplier('id_span_form_outil_description_$tool_num','block');focus_form('id_textarea_outil_description_$tool_num');\">$description_br</span><span  style=\"display:none\" id=\"id_span_form_outil_description_$tool_num\"><textarea  id=\"id_textarea_outil_description_$tool_num\" rows=\"5\" cols=\"50\" onblur=\"update_outil('$tool_num','description');\">$description_n</textarea></span></td>
			<td><span id=\"id_span_outil_url_$tool_num\" onclick=\"plier('id_span_outil_url_$tool_num');deplier('id_span_form_outil_url_$tool_num','block');focus_form('id_input_outil_url_$tool_num');\">$url</span><span  style=\"display:none\" id=\"id_span_form_outil_url_$tool_num\"><input type=\"text\" size=\"50\" id=\"id_input_outil_url_$tool_num\" value=\"$url\" onblur=\"update_outil('$tool_num','url');\"></span></td>
			<td><img src=\"images/poubelle_moyenne.png\" border=\"0\" onclick=\"delete_outil('$tool_num');\" style=\"cursor:pointer;\"></td>
			</tr>";
			
	}
	print "</tbody></table>";
}

function list_outil () {
	global $dbh;
	$req= "select tool_num from dwh_tool order by tool_num";
	$sel=oci_parse($dbh, $req);
	oci_execute($sel) ||die ("ERreur  $req");
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$tool_num = $r['TOOL_NUM'];
		$outil=get_outil ($tool_num);
		$title = $outil['TITLE'];
		$authors = $outil['AUTHORS'];
		$description = $outil['DESCRIPTION'];
		$url = $outil['URL'];
		print "<h2><a href=\"#\" onclick=\"afficher_outil($tool_num);\">$title</a></h2>";
	}
}

function afficher_outil ($tool_num) {
	$outil=get_outil ($tool_num);
	$title = $outil['TITLE'];
	$authors = $outil['AUTHORS'];
	$description = preg_replace("/\n/","<br>",$outil['DESCRIPTION']);
	$url = $outil['URL'];
	if (preg_match("/\?/",$url)) {
		$url_token=$url."&token=".$_SESSION['dwh_jwt_key_session'];
	} else {
		$url_token=$url."?token=".$_SESSION['dwh_jwt_key_session'];
	}
	print "<h1>$title</h1>
	<h3>".get_translation('DEVELOPPED_BY','Développé par')." $authors</h3>
	<strong>".get_translation('DESCRIPTIONS','Descriptions')."</strong><br><span class=\"description\">$description</span><br><br>
	<strong>".get_translation('TO_ACCESS_THIS','Pour y accéder')." :</strong> <a href=\"$url_token\" target=\"_blank\">$url</a>";
}


function get_translation($code,$defaut) {
	global $JSON_TRANSLATION_FILE,$CHARSET,$user_num_session,$CHEMIN_GLOBAL,$CHEMIN_GLOBAL_LOG;
	$memory_get_usage=memory_get_usage();
	if (is_file($JSON_TRANSLATION_FILE)) {
		$file=file_get_contents($JSON_TRANSLATION_FILE);
		$table_translation=json_decode($file,true);
		if (json_last_error()!=0) { // si erreur sur fichier json on essaie de le parser 
			$file=preg_replace("/[{}\n]/","",$file);
			$t=explode("\",\"",$file);
			foreach ($t as $p) {
				list($c,$l)=explode('":"',$p);
				$c=str_replace("\"","",$c);
				$l=str_replace("\"","",$l);
				$table_translation[$c]=$l;
			}
		} else {
			if (strtoupper($CHARSET)!='UTF8') {
				$table_translation[$code]=utf8_decode($table_translation[$code]);
			}
		}
		if ($table_translation[$code]!='') {
			$translation=$table_translation[$code];
		} else {
		        $inF = fopen("$CHEMIN_GLOBAL_LOG/CODE_UNTRANSLATE.translate_alert","a+");
		        fputs( $inF,"$code;$defaut\n");
		        fclose($inF);
		        
			if ($defaut=='') {
				$translation="[$code]";
			} else {
				$translation=$defaut;
			}
		}
		unset($table_translation); 
	} else {
		$translation=$defaut;
	}
	if (preg_match("/^JS_/",$code)) { // penser à supprimer les apostrophes pour javascript
		$translation=preg_replace("/['\"]/"," ",$translation);
	}
	return "$translation";
}

function insert_datamart_user_droit ($datamart_num,$user_num,$right) {
	global $dbh;
	$req="insert into dwh_datamart_user_right  (datamart_num , user_num ,right) values ($datamart_num,$user_num,'$right')";
	$ins=oci_parse($dbh,$req);
	oci_execute($ins) || die ("<strong style=\"color:red\">erreur : user et droit non sauvé</strong><br>");
}

function insert_datamart ($datamart_num,$title,$description_datamart,$datamart_date,$date_start,$end_date,$temporaire,$datamart_num_origin) {
	global $dbh;
	
	if ($datamart_date=='') {
		$datamart_date="sysdate";
	} else if ( preg_match("/sysdate/i",$datamart_date)) {
		$datamart_date="$datamart_date";
	} else {
		$datamart_date="to_date('$datamart_date','DD/MM/YYYY')";
	}
	
	if ($date_start=='') {
		$date_start="sysdate";
	} else if ( preg_match("/sysdate/i",$date_start)) {
		$date_start="$date_start";
	} else {
		$date_start="to_date('$date_start','DD/MM/YYYY')";
	}
	
	if ($end_date=='') {
		$end_date="sysdate";
	} else if ( preg_match("/sysdate/i",$end_date)) {
		$end_date="$end_date";
	} else {
		$end_date="to_date('$end_date','DD/MM/YYYY')";
	}
	
	$req="insert into dwh_datamart  (datamart_num , title_datamart ,description_datamart ,datamart_date ,date_start,end_date,temporary_status,datamart_num_origin) 
				values ($datamart_num,:title,:description_datamart,$datamart_date,$date_start,$end_date,$temporaire,$datamart_num_origin)";
	$ins = ociparse($dbh,$req);
	$rowid = ocinewdescriptor($dbh, OCI_D_LOB);
	ocibindbyname($ins, ":title",$title);
	ocibindbyname($ins, ":description_datamart",$description_datamart);
	$execState = ociexecute($ins) || die ("<strong style=\"color:red\">".get_translation('ERROR','erreur')." : datamart ".get_translation('NOT_SAVED','non sauvé')."</strong><br>");
	ocifreestatement($ins);
}

function insert_datamart_document_origin_code ($datamart_num,$document_origin_code) {
	global $dbh;
	$req="insert into dwh_datamart_doc_origin  (datamart_num , document_origin_code) values ($datamart_num,'$document_origin_code')";
	$ins=oci_parse($dbh,$req);
	oci_execute($ins) || die ("<strong style=\"color:red\">erreur : origin code ".get_translation('NOT_SAVED','non sauvé')."</strong><br>");
}


function insert_datamart_resultat ($sql) {
	global $dbh;
	if ($sql!='') {
		$req="insert into dwh_datamart_result  (datamart_num , patient_num) $sql ";
		$ins=oci_parse($dbh,$req);
		oci_execute($ins) || die ("<strong style=\"color:red\">".get_translation('ERROR','erreur')." : datamart ".get_translation('NOT_SAVED','non sauvé')."</strong><br>");
	}
}

function delete_datamart_resultat ($datamart_num,$sql) {
	global $dbh;
	$req="delete from dwh_datamart_result where datamart_num=$datamart_num $sql";
	$del=oci_parse($dbh,$req);
	oci_execute($del) || die ("<strong style=\"color:red\">erreur :  non supprimé</strong><br>");
}

function delete_datamart ($datamart_num) {
	global $dbh;
	$req="delete from dwh_datamart where datamart_num=$datamart_num";
	$del=oci_parse($dbh,$req);
	oci_execute($del) || die ("<strong style=\"color:red\">erreur :  non supprimé</strong><br>");
}


function partager_requete_en_cours ($user_num_sent,$liste_num_user_partage,$notification_text,$query_num) {
	global $dbh;
	$sel=oci_parse($dbh,"select xml_query,datamart_num from dwh_query where query_num=$query_num and user_num=$user_num_sent");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$xml_query=$r['XML_QUERY']->load();
	$datamart_num=$r['DATAMART_NUM'];
	
	$tab_num_user_partage=explode(",",$liste_num_user_partage);
	foreach ($tab_num_user_partage as $num_user_partage) {
		if ($num_user_partage!='') {
			$query_num_insert=get_uniqid();
			
			$requeteins="insert into dwh_query ( query_num,user_num,xml_query,query_date,datamart_num,query_type,pin) 
					values ($query_num_insert,$num_user_partage,:xml_query,sysdate,$datamart_num,'temp',0)";
			$stmtupd = ociparse($dbh,$requeteins);
			$rowid = ocinewdescriptor($dbh, OCI_D_LOB);
			ocibindbyname($stmtupd, ":xml_query",$xml_query );
			$execState = ociexecute($stmtupd);
			sauver_notification ($user_num_sent,$num_user_partage,'requete',$notification_text,$query_num_insert);
		}
	}
}


function get_my_export_lists ($user_num) {
	global $dbh;
	print "<table border=\"0\" class=\"dataTable\" id=\"id_tableau_liste_concepts\"><thead><tr><td></td><td></td><td></td><td></td></tr></thead><tbody>";
	$sel_export=oci_parse($dbh,"select EXPORT_DATA_NUM, title,creation_date,share_list from dwh_export_data where user_num=$user_num order by title ");
        oci_execute($sel_export);
        while ($r_export=oci_fetch_array($sel_export,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $export_data_num=$r_export['EXPORT_DATA_NUM'];
                $title=$r_export['TITLE'];
                $creation_date=$r_export['CREATION_DATE'];
                $share_list=$r_export['SHARE_LIST'];
                if($share_list==0){
			$share='shared';		
		}else{
			$share='not shared';
		}
		              
         	print "<tr>
	         	<td><img src=\"images/poubelle_moyenne.png\" onclick=\"supprimer_list('$export_data_num,$share_list');\" border=\"0\" style=\"cursor:pointer;vertical-align:middle\" ></td>
			<td>$title</td>
	         	<td>$creation_date</td>
			<td  style=\"cursor:pointer\" onclick=\"change_share($export_data_num,$share_list)\">$share</td>
         	</tr>";
        }
        print "</tbody></table>";
}


function total_patients_per_concept($thesaurus_data_num,$tmpresult_num) {
	global $dbh,$user_num_session;
	
	$query="select count(*) as counter from dwh_data where thesaurus_data_num='$thesaurus_data_num'
	and exists  (select patient_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num and dwh_data.patient_num=dwh_tmp_result_$user_num_session.patient_num)";
	$sel = oci_parse($dbh,$query); 
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$counter_concept=$r['COUNTER'];
	return $counter_concept;
		
}

function convert_decimal_for_export ($number) {
	global $EXCEL_DECIMAL;
	if ($EXCEL_DECIMAL=='') {
		$EXCEL_DECIMAL=",";
	}
	$number=preg_replace("/[,.]/",$EXCEL_DECIMAL,$number);
	return $number;
}

function ajouter_query_demo ($user_num) {
	global $tableau_query_demo;
	if (is_array($tableau_query_demo)) {
		foreach ($tableau_query_demo as $query_num => $libelle) {
			partager_requete_en_cours (1,$user_num,$libelle,$query_num);
		}
		$private_message="Bienvenue sur Dr Warehouse ! Vous pouvez tester les requêtes ci-dessous. Cliquez sur la notification pour afficher la requête puis sur 'lancer la rechercher' pour voir le résultat.";
		sauver_notification (1,$user_num,'message_prive',$private_message,'');
		sauver_notification ($user_num,1,'recipisse_message_prive',$private_message,'');
	}
}

$tableau_file_already_in_text=array();
function display_image_in_document ($patient_num,$document_num,$user_num,$displayed_text) {

	global $dbh,$tableau_file_already_in_text;
	$tableau_ext_image=array('jpg', 'jpeg', 'gif', 'png','JPG', 'JPEG', 'GIF', 'PNG');

	$autorisation_voir_patient_nominative=autorisation_voir_patient_nominative ($patient_num,$user_num);
	if ($autorisation_voir_patient_nominative=='ok') {
		$liste_document_origin_code=list_authorized_document_origin_code_for_one_patient($patient_num,$user_num);
		$req_filtre_document='';
	        if ($liste_document_origin_code!='') {
	        	if (!preg_match("/'tout'/i","$liste_document_origin_code")) {
					$req_filtre_document.="and document_num in (select document_num from dwh_document where document_origin_code in ($liste_document_origin_code)) ";
	        	}
	        } else {
	            $req_filtre_document.=" and 1=2";
	        }
	        
		$selval=oci_parse($dbh,"select count(*) NB from dwh_file where document_num=$document_num $req_filtre_document");
		oci_execute($selval);
		$res=oci_fetch_array($selval,OCI_RETURN_NULLS+OCI_ASSOC);
		$nb_files=$res['NB'];
		if ($nb_files>0) {
			if ($nb_files<6) {
				$selval=oci_parse($dbh,"select file_num ,file_title ,lower(file_mime_type) file_mime_type,file_order  from dwh_file where document_num=$document_num  order by file_order");
				oci_execute($selval);
				while ($res=oci_fetch_array($selval,OCI_RETURN_NULLS+OCI_ASSOC)) {
					$file_mime_type=$res['FILE_MIME_TYPE'];
					$file_title=$res['FILE_TITLE'];
					$file_num=$res['FILE_NUM'];
					if (in_array($file_mime_type,$tableau_ext_image)) {
						if (preg_match( "/<img [^>]*src=\"".$file_title."\"[^>]*>/",$displayed_text)) {
							$tableau_file_already_in_text[$file_num]='ok';
							$displayed_text=preg_replace("/(<img [^>]*src=\")".$file_title."(\"[^>]*>)/","$1"."ajax.php?action=load_file&file_num=$file_num"."$2",$displayed_text);
						}
					}
				}
			} 
		}
	}
	$displayed_text=preg_replace("/(<img [^>]+src=\"[^.]+\.(png|jpg|gif|jpeg)\"[^>]+>)/","",$displayed_text);
	return $displayed_text;

}

function display_list_file ($patient_num,$document_num,$user_num) {
	global $dbh,$tableau_file_already_in_text;
	$tableau_ext_image=array('jpg', 'jpeg', 'gif', 'png','JPG', 'JPEG', 'GIF', 'PNG');
	$display_list_file='';
	$autorisation_voir_patient_nominative=autorisation_voir_patient_nominative ($patient_num,$user_num);
	
	if ($autorisation_voir_patient_nominative=='ok' && $document_num!='' && $patient_num!='') {
		$liste_document_origin_code=list_authorized_document_origin_code_for_one_patient($patient_num,$user_num);
		$req_filtre_document='';
	        if ($liste_document_origin_code!='') {
	        	if (!preg_match("/'tout'/i","$liste_document_origin_code")) {
					$req_filtre_document.="and document_num in (select document_num from dwh_document where document_origin_code in ($liste_document_origin_code)) ";
	        	}
	        } else {
	            $req_filtre_document.=" and 1=2";
	        }
	        
		$selval=oci_parse($dbh,"select count(*) NB from dwh_file where document_num=$document_num $req_filtre_document");
		oci_execute($selval);
		$res=oci_fetch_array($selval,OCI_RETURN_NULLS+OCI_ASSOC);
		$nb_files=$res['NB'];
		if ($nb_files>0) {
			if ($nb_files<6) {
				$selval=oci_parse($dbh,"select file_num ,file_title ,lower(file_mime_type) file_mime_type,file_order  from dwh_file where document_num=$document_num  order by file_order");
				oci_execute($selval);
				while ($res=oci_fetch_array($selval,OCI_RETURN_NULLS+OCI_ASSOC)) {
					$file_mime_type=$res['FILE_MIME_TYPE'];
					$file_title=$res['FILE_TITLE'];
					$file_num=$res['FILE_NUM'];
					if ($tableau_file_already_in_text[$file_num]=='') {
						if (in_array($file_mime_type,$tableau_ext_image)) {
							$width_image=get_width_image ($file_num);
							$max_width="";
							if ($width_image!='') {
								$max_width="max-width:".$width_image."px;";
							}
							$display_list_file.= "$file_title :<br><a href=\"file_viewer.php?patient_num=$patient_num&document_num=$document_num&file_num_click=$file_num\" target=\"_blank\"><img src=\"ajax.php?action=load_file&file_num=$file_num\" style=\"width:100%;border:0px;$max_width\"></a><br><br>";
						} else {
							$display_list_file.= "<a href=\"ajax.php?action=load_file&file_num=$file_num\" target=\"_blank\">$file_title</a><br><br>";
						}
					}
				}
			} else {
				$selval=oci_parse($dbh,"select file_num ,file_title ,lower(file_mime_type) file_mime_type,file_order  from dwh_file where document_num=$document_num  order by file_order");
				oci_execute($selval);
				while ($res=oci_fetch_array($selval,OCI_RETURN_NULLS+OCI_ASSOC)) {
					$file_mime_type=$res['FILE_MIME_TYPE'];
					$file_title=$res['FILE_TITLE'];
					$file_num=$res['FILE_NUM'];
					if ($tableau_file_already_in_text[$file_num]=='') {
						if (in_array($file_mime_type,$tableau_ext_image)) {
							$display_list_file.= "<div style=\"float:left;font-size:9px;padding:10px;text-align:center;cursor:pointer;\"><a href=\"file_viewer.php?patient_num=$patient_num&document_num=$document_num&file_num_click=$file_num\" target=\"_blank\">$file_title<br><img src=\"ajax.php?action=load_file&file_num=$file_num&preview=preview\" border=\"0\"></a></div>";
						} else {
							$display_list_file.= "<div style=\"float:left;font-size:9px;padding:10px;text-align:center;\"><a href=\"ajax.php?action=load_file&file_num=$file_num\" target=\"_blank\">$file_title</a></div>";
						}
					}
				}
			}
		}
	}
	return $display_list_file;
}

function load_file ($file_num,$preview) {
	global $dbh;
	$tableau_ext_image=array('jpg', 'jpeg', 'gif', 'png','JPG', 'JPEG', 'GIF', 'PNG');
	
	$selval=oci_parse($dbh,"select patient_num from dwh_file where file_num=$file_num ");
	oci_execute($selval);
	$res=oci_fetch_array($selval,OCI_RETURN_NULLS+OCI_ASSOC);
	$patient_num=$res['PATIENT_NUM'];
	
	
	$selval=oci_parse($dbh,"select file_num ,file_title ,file_content,lower(file_mime_type) file_mime_type  from dwh_file where file_num=$file_num ");
	oci_execute($selval);
	$res=oci_fetch_array($selval,OCI_RETURN_NULLS+OCI_ASSOC);
	$file_mime_type=$res['FILE_MIME_TYPE'];
	$file_title=$res['FILE_TITLE'];
	$file_num=$res['FILE_NUM'];
	
	if ($res['FILE_CONTENT']!='') {
		$file_content=$res['FILE_CONTENT']->load();
		if (in_array($file_mime_type,$tableau_ext_image)) {
			if ($preview=='preview') { 
				$largeur_cible = 40;
				$hauteur_cible = 40;
				if ($image_origine = imagecreatefromstring($file_content)) {
					$largeur_origine = imagesx($image_origine);
					$hauteur_origine = imagesy($image_origine);
					
					if ($largeur_origine > $largeur_cible || $hauteur_origine > $hauteur_cible) {
						list($largeur_cible, $hauteur_cible) = redim_image($largeur_origine, $hauteur_origine, $largeur_cible, $hauteur_cible);
						$image_cible = imagecreatetruecolor($largeur_cible, $hauteur_cible);
						imagecopyresampled($image_cible, $image_origine, 0, 0, 0, 0, $largeur_cible, $hauteur_cible, $largeur_origine, $hauteur_origine);
					} else {
						$image_cible = $image_origine;
					}
					
					header("Content-type: image/$file_mime_type");
					header("Content-Disposition: inline; filename=\"$nom_doc\"");
					if ($file_mime_type=='jpg' || $file_mime_type=='jpeg') {
						imagejpeg($image_cible, null, 100);
						imagedestroy($image_cible);
					} else if ($file_mime_type=='gif') {
						imagegif($image_cible, null, 100);
						imagedestroy($image_cible);
					} else if ($file_mime_type=='png') {
						imagepng($image_cible, null, 9);
						imagedestroy($image_cible);
					} else {
						print $file_content;
					}
					imagedestroy($image_origine);
				} else {
					print $file_content;
				}
			} else {
				header("Content-type: image/$file_mime_type");
				header("Content-Disposition: inline; filename=\"$file_title\"");
			}
		} else {
			header("Content-Disposition: attachment; filename=\"$file_title\"");
			header("Content-type: application/$file_mime_type");
		}
		return $file_content;
	}
	
}

function redim_image($largeur_origine, $hauteur_origine, $largeur_cible, $hauteur_cible) {
	$test_h = round(($largeur_cible / $largeur_origine) * $hauteur_origine);
	$test_w = round(($hauteur_cible / $hauteur_origine) * $largeur_origine);
	if(!$hauteur_cible) {
		$hauteur_cible = $test_h;
	} else if (!$largeur_cible) {
		$largeur_cible = $test_w;
	} else if ($test_h > $hauteur_cible) {
		$largeur_cible = $test_w;
	} else {
		$hauteur_cible = $test_h;
	}
	return array($largeur_cible, $hauteur_cible);
}

function get_width_image ($file_num) {
	global $dbh;
	$largeur_origine='';
	if ($file_num!='') {
		$tableau_ext_image=array('jpg', 'jpeg', 'gif', 'png','JPG', 'JPEG', 'GIF', 'PNG');
		$largeur_origine='';
		$selval=oci_parse($dbh,"select file_num ,file_title ,file_content,lower(file_mime_type) file_mime_type  from dwh_file where file_num=$file_num ");
		oci_execute($selval);
		$res=oci_fetch_array($selval,OCI_RETURN_NULLS+OCI_ASSOC);
		$file_mime_type=$res['FILE_MIME_TYPE'];
		
		if ($res['FILE_CONTENT']!='') {
			$file_content=$res['FILE_CONTENT']->load();
			if (in_array($file_mime_type,$tableau_ext_image)) {
				if ($image_origine = imagecreatefromstring($file_content)) {
					$largeur_origine = imagesx($image_origine);
				}
			}
		}
	}
	return $largeur_origine;
}

function get_query ($query_num) {
	global $dbh;
	$query=array();
	$sel=oci_parse($dbh,"select 
			user_num, xml_query, to_char(query_date,'DD/MM/YYYY HH24:MI') as query_date, title_query, datamart_num, query_type, crontab_query,to_char( last_load_date,'DD/MM/YYYY HH24:MI') as last_load_date,crontab_periode, pin 
			from dwh_query 
			where  query_num=$query_num");
	oci_execute($sel);
	$query=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	if ($query['XML_QUERY']) {
		$query['XML_QUERY']=$query['XML_QUERY']->load();
	}
	return $query;
}

function get_user_queries ($user_num) {
	global $dbh;
	$query=array();
	$sel=oci_parse($dbh,"select user_num from dwh_query where  user_num=$user_num");
	oci_execute($sel);
	$query=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	if ($query['XML_QUERY']!='') {
		$query['XML_QUERY']=$query['XML_QUERY']->load();
	}
	return $query;
}

function get_query_inclusion ($cohort_num, $patient_num) {
	global $dbh;
	$sel=oci_parse($dbh,"select query_num from dwh_cohort_result where cohort_num=$cohort_num and patient_num=$patient_num");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
        $query_num=$r['QUERY_NUM'];
	return $query_num;
}

function insert_etl_error ($error_date, $file_name, $message) {
	global $dbh;
	$req="insert into dwh_etl_error (process_date,error_date,file_name,message) values (sysdate,to_date('$error_date','DD/MM/YYYY HH24:MI') ,'$file_name',:message)";
	$ins = ociparse($dbh,$req);
	$rowid = ocinewdescriptor($dbh, OCI_D_LOB);
	ocibindbyname($ins, ":message",$message);
	$execState = ociexecute($ins)||die ("ERreur  $req");
	ocifreestatement($ins);
}


function get_query_clear ($query_num) {
	global $dbh;
	$readable_query='';
	if ($query_num!='') {
		$sel=oci_parse($dbh,"select xml_query from dwh_query where query_num=$query_num");
		oci_execute($sel);
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		if ($r['XML_QUERY']) {
		        $xml_query=$r['XML_QUERY']->load();
			$readable_query=readable_query ($xml_query) ;
		}
	}
	return $readable_query;
}


function get_document ($document_num) {
	global $dbh;
	$tableau_document=array();
	$sel=oci_parse($dbh,"select 
		patient_num,document_num,displayed_text,title,patient_num,
		author,to_char(document_date,'DD/MM/YYYY') as document_date_char,
		document_origin_code,
		department_num , encounter_num
	from dwh_document where document_num=$document_num " );   
        oci_execute($sel);
        $row_doc = oci_fetch_array($sel, OCI_ASSOC);
        $tableau_document['patient_num']=$row_doc['PATIENT_NUM'];
        $tableau_document['document_num']=$row_doc['DOCUMENT_NUM'];
        $tableau_document['title']=$row_doc['TITLE'];
        $tableau_document['document_date']=$row_doc['DOCUMENT_DATE_CHAR'];
        $tableau_document['author']=$row_doc['AUTHOR'];
        $tableau_document['document_origin_code']=$row_doc['DOCUMENT_ORIGIN_CODE'];
        $tableau_document['department_num']=$row_doc['DEPARTMENT_NUM'];
        $tableau_document['unit_num']=$row_doc['UNI_NUM'];
        $tableau_document['unit_code']=$row_doc['UNIT_CODE'];
        $tableau_document['encounter_num']=$row_doc['ENCOUNTER_NUM'];
        
        if ($row_doc['DISPLAYED_TEXT']!='') {
		$tableau_document['displayed_text']=$row_doc['DISPLAYED_TEXT']->load();
        }
        
	$sel=oci_parse($dbh,"select text ,trunc(age_patient) as age_patient, trunc(age_patient*12) as age_patient_month from dwh_text where  document_num=$document_num and context='text' and certainty=0" );   
        oci_execute($sel);
        $row_doc = oci_fetch_array($sel, OCI_ASSOC);
        if ($row_doc['TEXT']!='') {
		$tableau_document['text']=$row_doc['TEXT']->load();
        }
        
	if ($_SESSION['dwh_droit_fuzzy_display']=='ok') {
		$tableau_document['author']='[AUTHOR]';
		$tableau_document['document_date']='[DATE]';
	}
	
        $tableau_document['age_patient']=$row_doc['AGE_PATIENT'];
        $tableau_document['age_patient_year']=$row_doc['AGE_PATIENT'];
        $tableau_document['age_patient_month']=$row_doc['AGE_PATIENT_MONTH'];
        
	return  $tableau_document;
}

function get_data_out_of_range ($patient_num,$upper_lower,$filter='') {
	global $dbh;
	$data_out_of_range=array();
	if ($upper_lower=='lower') {
		$filter.=" and val_numeric<lower_bound";
		$sel_val_numeric="min(val_numeric)";
	}
	
	if ($upper_lower=='upper') {
		$filter.=" and val_numeric>upper_bound";
		$sel_val_numeric="max(val_numeric)";
	}

	$sel=oci_parse($dbh,"select 
					a.thesaurus_data_num, 
					concept_str, 
					info_complement, 
					measuring_unit, 
					$sel_val_numeric as val_numeric, 
					lower_bound, 
					upper_bound,
					count(*) as nb_out
				from 
					dwh_data a, 
					dwh_thesaurus_data b
				where 
					a.thesaurus_data_num=b.thesaurus_data_num and 
					patient_num=$patient_num $filter  and 
					val_numeric is not null 
				group by  
					a.thesaurus_data_num, 
					concept_str, 
					info_complement, 
					measuring_unit, 
					lower_bound, 
					upper_bound
					" );   
        oci_execute($sel);
        while ($row_doc = oci_fetch_array($sel, OCI_ASSOC)) {
    	    $data_out_of_range[]=$row_doc;
        }
	return  $data_out_of_range;


}

function get_data ($patient_num,$thesaurus_code,$filter='') {
	global $dbh;
	$data=array();
	
	$sel=oci_parse($dbh,"select 
					a.thesaurus_data_num, 
					document_num,
					encounter_num,
					concept_code,
					concept_str, 
					info_complement, 
					measuring_unit, 
					val_numeric, 
					val_text, 
					lower_bound, 
					upper_bound,
					department_num,
					to_char(document_date,'DD/MM/YYYY') as document_date_ddmmyyyy,
					to_char(document_date,'DD/MM/YYYY HH24:MI') as document_date_ddmmyyyyhh24mi
				from 
					dwh_data a, 
					dwh_thesaurus_data b
				where 
					a.thesaurus_data_num=b.thesaurus_data_num and 
					patient_num=$patient_num  and a.thesaurus_code='$thesaurus_code' $filter  
				
					" );   
        oci_execute($sel);
        while ($row_doc = oci_fetch_array($sel, OCI_ASSOC)) {
    	    $data[]=$row_doc;
        }
	return  $data;


}

function get_concept_patient ($patient_num,$filter='') {
	global $dbh;
	$table_concept=array();
	$sel=oci_parse($dbh,"select 	concept_code,
					concept_str_found,  
					LISTAGG(document_num, ',')  WITHIN GROUP (order by document_num desc) as list_document_num, 
					count(*) as nb_str
				from 
					dwh_enrsem 
				where 
					patient_num=$patient_num and certainty=1 and context='patient_text' $filter  and concept_code in (select concept_code from dwh_thesaurus_enrsem where phenotype=1)
				group by concept_code,CONCEPT_STR_FOUND
				order by count(*) desc
					" );   
        oci_execute($sel);
        while ($row_doc = oci_fetch_array($sel, OCI_ASSOC)) {
    	    $table_concept[]=$row_doc;
        }
	return  $table_concept;
}

function display_sentence_with_term ($patient_num,$list_document_num,$concept_str) {
	global $dbh;
	$tab_document_num=explode(',',$list_document_num);
	$document=get_document ($tab_document_num[0]);
	$title=$document['title'];
	$document_date=$document['document_date'];
	$document_origin_code=$document['document_origin_code'];
	$author=$document['author'];
	$text=$document['text'];
	
	$appercu=resumer_resultat($text,$concept_str,array(),'patient');
	return  "$title le $document_date : $appercu";
}


function get_document_for_a_patient($patient_num,$filter='') {
	global $dbh;
	$tableau_document=array();
	$sel=oci_parse($dbh,"select document_num from dwh_document where patient_num=$patient_num $filter order by  document_date desc" );   
        oci_execute($sel);
        while ($row_doc = oci_fetch_array($sel, OCI_ASSOC)) {
    	    $tableau_document[]=$row_doc['DOCUMENT_NUM'];
        }
	return  $tableau_document;
}

function get_list_objects_in_result ($tmpresult_num,$user_num,$filter='') {
	global $dbh;
	$tableau_document=array();
	$sel=oci_parse($dbh,"SELECT distinct document_num,document_date FROM dwh_tmp_result_$user_num WHERE tmpresult_num = $tmpresult_num $filter  order by  document_date desc" );   
        oci_execute($sel);
        while ($row_doc = oci_fetch_array($sel, OCI_ASSOC)) {
    	    $tableau_document[]=$row_doc['DOCUMENT_NUM'];
        }
	return  $tableau_document;
}

function get_table_objects_in_result_ancien ($tmpresult_num,$user_num,$filter='') {
	global $dbh;
	$tableau_document=array();


	$sel=oci_parse($dbh,"SELECT distinct document_num,document_date,object_type FROM dwh_tmp_result_$user_num WHERE tmpresult_num = $tmpresult_num $filter  order by  document_date desc" );   
        oci_execute($sel);
        while ($row_doc = oci_fetch_array($sel, OCI_ASSOC)) {
    	    $tableau_document[$row_doc['DOCUMENT_NUM']]=$row_doc['OBJECT_TYPE'];
        }
	return  $tableau_document;
}

function get_table_objects_in_result ($tmpresult_num,$user_num,$filter='') {
	global $dbh;
	$tableau_document=array();

        $req="SELECT patient_num,document_num,to_char(document_date,'DD/MM/YYYY') as DOCUMENT_DATE_CHAR,encounter_num,title,document_origin_code,author,department_num,text,document_date FROM  dwh_text 
where  context='text' and certainty=0 and document_num in (SELECT  document_num FROM dwh_tmp_result_$user_num WHERE tmpresult_num = $tmpresult_num    $filter  and object_type='document')
 order by  document_date desc";
	$sel=oci_parse($dbh,$req );   
        oci_execute($sel);
        while ($row_doc = oci_fetch_array($sel, OCI_ASSOC)) {
        	$document_num=$row_doc['DOCUMENT_NUM'];
		$tableau_document[$document_num]['object_type']='document';
	        $tableau_document[$document_num]['patient_num']=$row_doc['PATIENT_NUM'];
	        $tableau_document[$document_num]['document_num']=$row_doc['DOCUMENT_NUM'];
	        $tableau_document[$document_num]['title']=$row_doc['TITLE'];
	        $tableau_document[$document_num]['document_date']=$row_doc['DOCUMENT_DATE_CHAR'];
	        $tableau_document[$document_num]['author']=$row_doc['AUTHOR'];
	        $tableau_document[$document_num]['document_origin_code']=$row_doc['DOCUMENT_ORIGIN_CODE'];
	        
	        if ($row_doc['TEXT']!='') {
			$tableau_document[$document_num]['text']=$row_doc['TEXT']->load();
	        }
	        
		if ($_SESSION['dwh_droit_fuzzy_display']=='ok') {
			$tableau_document[$document_num]['author']='[AUTHOR]';
			$tableau_document[$document_num]['document_date']='[DATE]';
		}
        }

        $req="select  
        		mvt_num,
        		encounter_num, 
			unit_code, 
			to_char(entry_date,'DD/MM/YYYY HH24:MI') as  entry_date_char,
			to_char(out_date,'DD/MM/YYYY HH24:MI') out_date , 
			department_num, unit_num, patient_num, mvt_num, 
			mvt_entry_mode, mvt_exit_mode, type_mvt, 
			entry, out, mvt_order,
			round(out_date-entry_date) as mvt_length,
			entry_date
			from dwh_patient_mvt 
			where mvt_num in (SELECT  document_num FROM dwh_tmp_result_$user_num WHERE tmpresult_num = $tmpresult_num    $filter   and object_type='mvt') 
			order by entry_date desc ";
	$sel=oci_parse($dbh,$req);   
        oci_execute($sel);
        while ($row_doc = oci_fetch_array($sel, OCI_ASSOC)) {
        	$mvt_num=$row_doc['MVT_NUM'];
		$tableau_document[$mvt_num]['object_type']='mvt';
		$tableau_document[$mvt_num]['entry_date']=$row_doc['ENTRY_DATE_CHAR'];
		$tableau_document[$mvt_num]['out_date']=$row_doc['OUT_DATE'];
		if ($_SESSION['dwh_droit_fuzzy_display']=='ok') {
			$tableau_document[$mvt_num]['entry_date']='[entry_date]';
			$tableau_document[$mvt_num]['out_date']='[out_date]';
		}
		$tableau_document[$mvt_num]['document_origin_code']='MVT';
	
		$tableau_document[$mvt_num]['department_num']=$row_doc['DEPARTMENT_NUM'];
		$tableau_document[$mvt_num]['encounter_num']=$row_doc['ENCOUNTER_NUM'];
		$tableau_document[$mvt_num]['type_mvt']=$row_doc['TYPE_MVT'];
		$tableau_document[$mvt_num]['unit_num']=$row_doc['UNIT_NUM'];
		$tableau_document[$mvt_num]['unit_code']=$row_doc['UNIT_CODE'];
        
        }
        
	return  $tableau_document;
}
function get_object_in_result ($tmpresult_num,$user_num,$document_num) {
	global $dbh;
	$document_result=array();
	$sel=oci_parse($dbh,"SELECT document_num,patient_num,encounter_num, title,author,to_char(document_date,'DD/MM/YYYY HH24') as document_date,document_origin_code,department_num, object_type  FROM dwh_tmp_result_$user_num WHERE tmpresult_num=$tmpresult_num and document_num = $document_num " );   
        oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	$document_result['document_num']=$r['DOCUMENT_NUM'];
	$document_result['patient_num']=$r['PATIENT_NUM'];
	$document_result['encounter_num']=$r['ENCOUNTER_NUM'];
	$document_result['title']=$r['TITLE'];
	$document_result['author']=$r['AUTHOR'];
	$document_result['document_date']=$r['DOCUMENT_DATE'];
	$document_result['document_origin_code']=$r['DOCUMENT_ORIGIN_CODE'];
	$document_result['department_num']=$r['DEPARTMENT_NUM'];
	$document_result['object_type']=$r['OBJECT_TYPE'];
	return  $document_result;
}

function get_list_patients_in_result ($tmpresult_num,$user_num,$filter='') {
	global $dbh;
	$tableau_patient=array();
	$sel=oci_parse($dbh,"select distinct patient_num FROM dwh_tmp_result_$user_num WHERE tmpresult_num = $tmpresult_num $filter" );   
        oci_execute($sel);
        while ($row_doc = oci_fetch_array($sel, OCI_ASSOC)) {
    	    $tableau_patient[]=$row_doc['PATIENT_NUM'];
        }
	return  $tableau_patient;
}

function search_patient_document ($patient_num,$type_search,$str,$certainty,$context,$contains,$filter,$period) {
	global $dbh;
	$tableau_result=array();
	if ($patient_num!='') {
		$i=0;
		if ($type_search=='text') {
			$str_query=trim(nettoyer_pour_requete_automatique ($str));
			$req_text='';
			if ($str_query!='') {
				$req_text="and context='$context' and certainty=$certainty and contains($contains,'$str_query')>0";
			} 
		        $sel=oci_parse($dbh,"select document_num, document_date from dwh_text where patient_num=$patient_num $req_text   $filter order by document_date desc");
		        oci_execute($sel);
		        while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
				$document_num=$r['DOCUMENT_NUM'];
				$tableau_result[]=array('document_num'=>$document_num);
			}
			if (count($tableau_result)==0 && $contains=='text') {
			//	$tableau_result=search_patient_document_full_text ($patient_num,$str,$certainty,$context,'enrich_text');
			}
		}
		if ($type_search=='regexp') {
			$str_regexp=trim(clean_for_regexp ($str));
			//$req_text='';
			//if ($str!='') {
				//$req_text=" and context='$context' and certainty=$certainty and REGEXP_LIKE(text,'$str','i') ";
				//$req_text=" and context='$context' and certainty=$certainty";
			//}  
		        #$sel=oci_parse($dbh,"select document_num, document_date from dwh_text where patient_num=$patient_num $req_text   $filter order by document_date desc");
		        #oci_execute($sel);
		        #while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
			#	$document_num=$r['DOCUMENT_NUM'];
			$table_document=get_document_for_a_patient($patient_num,"");
			foreach ($table_document as $document_num) {
				$document=get_document ($document_num);
				$text=$document['text'];    
				if (preg_match_all("/$str_regexp/i","$text",$out, PREG_SET_ORDER)) {
					$tableau_result[]=array('document_num'=>$document_num);
				}
			}
		}
		if ($type_search=='data') {
			$tableau_result=array();
		
			$str_query=trim(nettoyer_pour_requete_automatique ($str));
			$str_query=preg_replace("/([a-z]) and /","$1% and ",$str_query);
			$str_query=preg_replace("/([a-z])$/","$1%",$str_query);
		        $sel=oci_parse($dbh,"select document_num, document_date, val_numeric|| val_text as value, concept_str, decode(measuring_unit,null,null,'('||measuring_unit  ||') ') || info_complement  as info ,concept_code  from dwh_data, dwh_thesaurus_data where patient_num=$patient_num and dwh_data.thesaurus_data_num=dwh_thesaurus_data.thesaurus_data_num and contains(description,'$str_query')>0   $filter order by document_date desc");
		        oci_execute($sel);
		        while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
				$document_num=$r['DOCUMENT_NUM'];
				$value=$r['VALUE'];
				$info=$r['INFO'];
				$concept_str=$r['CONCEPT_STR'];
				$concept_code=$r['CONCEPT_CODE'];
				$tableau_result[]=array('document_num'=>$document_num,'value'=>$value,'concept_str'=>$concept_str,'info'=>$info,'concept_code'=>$concept_code);
			}
		}
		if ($type_search=='data_code') { 
			$tableau_result=array();
		        $sel=oci_parse($dbh,"select document_num, document_date, val_numeric|| val_text as value,concept_str , decode(measuring_unit,null,null,'('||measuring_unit  ||') ') || info_complement  as info ,concept_code from dwh_data, dwh_thesaurus_data where patient_num=$patient_num and dwh_data.thesaurus_data_num=dwh_thesaurus_data.thesaurus_data_num and concept_code='$str'   $filter order by document_date desc");
		        oci_execute($sel);
		        while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
				$document_num=$r['DOCUMENT_NUM'];
				$value=$r['VALUE'];
				$info=$r['INFO'];
				$concept_str=$r['CONCEPT_STR'];
				$concept_code=$r['CONCEPT_CODE'];
				$tableau_result[]=array('document_num'=>$document_num,'value'=>$value,'concept_str'=>$concept_str,'info'=>$info,'concept_code'=>$concept_code);
			}
		}
	}
	$tableau_result_final=array();
	if ($period=='last' && $tableau_result[0]!='') {
		$tableau_result_final[0]=$tableau_result[0];
	}
	if ($period=='first' && $tableau_result[count($tableau_result)-1]!='') {
		$tableau_result_final[0]=$tableau_result[count($tableau_result)-1];
	}
	if ($period=='all' || $period=='') {
		$tableau_result_final=$tableau_result;
	}
	return $tableau_result_final;
}


function calculate_nb_insert ($nb_jour,$type_distribution){
	global $dbh;
	$tableau_result=array();
	if ($type_distribution=='upload_id') {
		$query_day_doc="substr(upload_id,1,8)";
		$query_day_mvt="substr(upload_id,1,8)";
	} else {
		$query_day_doc="to_char(document_date,'yyyymmdd')";
		$query_day_mvt="to_char(entry_date,'yyyymmdd')";
	}
	
	//patient
	$sel=oci_parse($dbh,"select substr(upload_id,1,8) as day,count(*) nb from dwh_patient 
			where substr(upload_id,1,8)>= to_char(sysdate-$nb_jour,'YYYYMMDD') 
			group by substr(upload_id,1,8) order by substr(upload_id,1,8)");
	oci_execute($sel);
	while ($res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$document_origin_code='patient';
		$day=$res['DAY'];
		$nb=$res['NB'];
		$tableau_result['patient'][$day]=$nb;
	}

	$sel=oci_parse($dbh,"select  count(*) nb from dwh_patient 
			where substr(upload_id,1,8)< to_char(sysdate-$nb_jour,'YYYYMMDD')");
	oci_execute($sel);
	while ($res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$document_origin_code='patient';
		$day=0;
		$nb=$res['NB'];
		$tableau_result['patient'][$day]=$nb;
	}


	//mvt
	$sel=oci_parse($dbh,"select $query_day_mvt as day,count(*) nb from dwh_patient_mvt
			where $query_day_mvt>= to_char(sysdate-$nb_jour,'YYYYMMDD') 
			group by $query_day_mvt order by $query_day_mvt");
	oci_execute($sel);
	while ($res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$document_origin_code='mvt';
		$day=$res['DAY'];
		$nb=$res['NB'];
		$tableau_result['mvt'][$day]=$nb;
	}

	$sel=oci_parse($dbh,"select  count(*) nb from dwh_patient_mvt 
			where $query_day_mvt< to_char(sysdate-$nb_jour,'YYYYMMDD')");
	oci_execute($sel);
	while ($res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$document_origin_code='mvt';
		$day=0;
		$nb=$res['NB'];
		$tableau_result['mvt'][$day]=$nb;
	}

	// document
	$sel=oci_parse($dbh,"select document_origin_code, $query_day_doc as day ,count(*) as nb from dwh_document
			where $query_day_doc>= to_char(sysdate-$nb_jour,'YYYYMMDD') 
			group by  document_origin_code,$query_day_doc
			order by $query_day_doc desc, document_origin_code desc");
	oci_execute($sel);
	while ($res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$document_origin_code=$res['DOCUMENT_ORIGIN_CODE'];
		$day=$res['DAY'];
		$nb=$res['NB'];
		$tableau_result[$document_origin_code][$day]=$nb;
	}

	$sel=oci_parse($dbh,"select  document_origin_code, count(*) nb from dwh_document 
			where $query_day_doc< to_char(sysdate-$nb_jour,'YYYYMMDD') group by document_origin_code");
	oci_execute($sel);
	while ($res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$document_origin_code=$res['DOCUMENT_ORIGIN_CODE'];
		$day=0;
		$nb=$res['NB'];
		$tableau_result[$document_origin_code][$day]=$nb;
	}

	return $tableau_result;
}


function get_concept_str ($concept_code,$thesaurus_code) {
	global $dbh;
	$tableau_result=array();
	$thesaurus_code='';
	if ($thesaurus_code!='') {
		$req_thesaurus_code=" and thesaurus_code='$thesaurus_code' ";
	}
	$sel=oci_parse($dbh,"select concept_str from dwh_thesaurus_enrsem where concept_code='$concept_code' and pref='Y' $req_thesaurus_code");
	oci_execute($sel);
	$res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$concept_str=$res['CONCEPT_STR'];
	return $concept_str;
}


function verif_format_date ($date,$format) {
	global $dbh;
	//on verifier que la date du document est au bon format
	$requete_date_test="select to_char(to_date('$date','$format'),'$format') date_test  from dual";
	$sel=oci_parse($dbh,$requete_date_test);
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC); 
	$date_test=$r['DATE_TEST'];
	if ($date_test=='') {
		$date_test='';
	}
	return $date_test;
}


function create_query_name ($term,$option) {
	$term=preg_replace("/\t+/"," ",$term);// on remplace tab par espace
	$term=preg_replace("/\s+/"," ",$term);// on remplace plusieurs espaces par espace
	$query_name="(";
	$tab_term=explode(' ',$term);
	
	if ($option=='' || count($tab_term)==1) {
		$query_name="(upper(lastname) like upper('$term%') or upper(maiden_name) like upper('$term%')  or upper(firstname) like upper('$term%') ";
	} else {
		$query_name="(1=2";
	}
	if (count($tab_term)==2) {
		$query_name.=" or (";
		// GARCELON NICOLAS
		$query_name.="(upper(lastname) like upper('".$tab_term[0]."%') or upper(maiden_name) like upper('".$tab_term[0]."%')) and upper(firstname) like upper('".$tab_term[1]."%') ";
		$query_name.=" or ";
		// NICOLAS GARCELON
		$query_name.="(upper(lastname) like upper('".$tab_term[1]."%') or upper(maiden_name) like upper('".$tab_term[1]."%')) and upper(firstname) like upper('".$tab_term[0]."%') ";
		$query_name.=")";
	}
	if (count($tab_term)==3) {
		// prenom1 prenom2 Nom|nomjf
		$query_name.=" or (";
		
		$query_name.="(upper(lastname) like upper('".$tab_term[2]."%') or upper(maiden_name) like upper('".$tab_term[2]."%')) and upper(firstname) like upper('".$tab_term[0]." ".$tab_term[1]."%') ";
		$query_name.=" or ";
		
		// Nom|nomjf prenom1 prenom2 
		$query_name.="(upper(lastname) like upper('".$tab_term[0]."%') or upper(maiden_name) like upper('".$tab_term[0]."%')) and upper(firstname) like upper('".$tab_term[1]." ".$tab_term[2]."%') ";
		$query_name.=" or ";
		
		//  prenom nom nomjf 
		$query_name.="(upper(lastname) like upper('".$tab_term[1]."%') or upper(maiden_name) like upper('".$tab_term[2]."%')) and upper(firstname) like upper('".$tab_term[0]."%') ";
		$query_name.=" or ";
		
		//  prenom nomjf nom
		$query_name.="(upper(lastname) like upper('".$tab_term[2]."%') or upper(maiden_name) like upper('".$tab_term[1]."%')) and upper(firstname) like upper('".$tab_term[0]."%') ";
		$query_name.=" or ";
		
		//  prenom (nom1 nom2)|(nomjf1 nomjf2)
		$query_name.="(upper(lastname) like upper('".$tab_term[1]." ".$tab_term[2]."%') or upper(maiden_name) like upper('".$tab_term[1]." ".$tab_term[2]."%')) and upper(firstname) like upper('".$tab_term[0]."%') ";
		
		$query_name.=")";
	}
	if (count($tab_term)==4) {
		$query_name.=" or (";
		$query_name.="(upper(lastname) like upper('".$tab_term[2]." ".$tab_term[3]."%') or upper(maiden_name) like upper('".$tab_term[2]." ".$tab_term[3]."%')) and upper(firstname) like upper('".$tab_term[0]." ".$tab_term[1]."%') ";
		$query_name.=" or ";
		
		$query_name.="(upper(lastname) like upper('".$tab_term[0]." ".$tab_term[1]."%') or upper(maiden_name) like upper('".$tab_term[0]." ".$tab_term[1]."%')) and upper(firstname) like upper('".$tab_term[2]." ".$tab_term[3]."%') ";
		$query_name.=" or ";
		
		// Nom|nomjf prenom1 prenom2 
		$query_name.="(upper(lastname) like upper('".$tab_term[0]."%') or upper(maiden_name) like upper('".$tab_term[1]."%')) and upper(firstname) like upper('".$tab_term[2]." ".$tab_term[3]."%') ";
		$query_name.=" or ";
		
		// Nom|nomjf prenom1 prenom2 
		$query_name.="(upper(lastname) like upper('".$tab_term[1]."%') or upper(maiden_name) like upper('".$tab_term[0]."%')) and upper(firstname) like upper('".$tab_term[2]." ".$tab_term[3]."%') ";
		$query_name.=" or ";
		
		// Nom|nomjf prenom1 prenom2 
		$query_name.="(upper(lastname) like upper('".$tab_term[2]."%') or upper(maiden_name) like upper('".$tab_term[3]."%')) and upper(firstname) like upper('".$tab_term[0]." ".$tab_term[1]."%') ";
		$query_name.=" or ";
		
		// Nom|nomjf prenom1 prenom2 
		$query_name.="(upper(lastname) like upper('".$tab_term[3]."%') or upper(maiden_name) like upper('".$tab_term[2]."%')) and upper(firstname) like upper('".$tab_term[0]." ".$tab_term[1]."%') ";
		
		$query_name.=")";
	}
	$query_name.=")";
	
	if ($option=='soundex') {
		$query_name=str_replace("%","",$query_name);
		$query_name_soundex=str_replace("upper","soundex",$query_name);
		if ( count($tab_term)==1) {
			$query_name_ortho=preg_replace("/upper\(([a-z0-9_]+)\) like upper\('([a-zA-z ]+)'\)/i", "utl_match.jaro_winkler(CONVERT(upper($1), 'US7ASCII'), CONVERT(upper('$2'), 'US7ASCII')) >'0,9'",$query_name);
			$query_name="$query_name_soundex and $query_name_ortho";
		} else {
			$query_name="$query_name_soundex";
		}
	}
	
	if ($option=='ortho') {
		$query_name=str_replace("%","",$query_name);
		$query_name=preg_replace("/upper\(([a-z0-9_]+)\) like upper\('([a-zA-z ]+)'\)/i", "utl_match.jaro_winkler(CONVERT(upper($1), 'US7ASCII'), CONVERT(upper('$2'), 'US7ASCII')) >'0,9'",$query_name);
	}

	return $query_name;

}


function display_my_process ($user_num) {
	global $dbh;
	$all_process=get_all_my_process ($user_num,"export_data,mapper_patient,regexp,similarite_cohorte");
	
	print "<table class=\"tablefin\">
	<thead>
	<th>".get_translation('CATEGORY_PROCESS',"Catégorie")."</th>
	<th>".get_translation('STATUS',"Statut")."</th>
	<th>".get_translation('COMMENT',"Commentaire")."</th>
	<th>".get_translation('DATE_OF_DELETION',"Date de suppression")."</th>
	<th>".get_translation('TELECHARGER',"Télécharger")."</th>
	<th>".get_translation('DELETE',"Suppr")."</th>
	</thead>
	<tbody>";
	foreach ($all_process as $process_num) {
		$process=get_process  ($process_num);
		$user_num=$process['USER_NUM'];
		$status=$process['STATUS'];
		$commentary=$process['COMMENTARY'];
		$process_end_date=$process['PROCESS_END_DATE'];
		$category_process=$process['CATEGORY_PROCESS'];
		
		if ($status=='1') { // end
			$telecharger="<a href='export_process.php?process_num=$process_num' target='_blank'>".get_translation('TELECHARGER',"Télécharger")."</a>"; 
			$status=get_translation('TERMINATED',"Terminé");
		} else {
			$telecharger='';
			$status=get_translation('IN_PROGRESS',"En cours");
		}
		print "<tr id=\"id_tr_process_$process_num\"><td>$category_process</td><td>$status</td><td>$commentary</td><td>$process_end_date</td><td>$telecharger</td><td><img src=\"images/poubelle_moyenne.png\" width=\"20\" onclick=\"delete_process($process_num);\" style=\"cursor:pointer\"></td></tr>";
	}
	print "</tbody>";
	print "</table>";

}

function get_uniqid() {
	global $dbh;
	
	$sel=oci_parse($dbh,"select dwh_seq.nextval as UNIQID from dual ");
	oci_execute($sel);
	$res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$uniqid=$res['UNIQID'];
	return $uniqid;
}

function get_list_users () {
	global $dbh;
	$table=array();
	$sel=oci_parse($dbh,"select user_num,lastname,firstname from dwh_user order by lastname,firstname ");
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$user_num=$r['USER_NUM'];
		$table[]=$user_num;
	}
	return $table;
}

function get_list_thesaurus_data () {
	global $dbh;
	$table=array();
	$query="select distinct thesaurus_code from dwh_thesaurus_data";	
	$sel=oci_parse($dbh,$query);
	oci_execute($sel);					
	while($r=oci_fetch_array($sel, OCI_RETURN_NULLS+OCI_ASSOC)){
		$thesaurus_code=$r['THESAURUS_CODE'];	
		$table[]=$thesaurus_code;
	}	
	return $table;
}

function get_min_max_date_used_data($thesaurus_data_num) {
	global $dbh;

	$sel=oci_parse($dbh,"select min(to_number(to_char(document_date,'YYYY'))) as min_date, max(to_number(to_char(document_date,'YYYY'))) as max_date  from dwh_data where thesaurus_data_num=$thesaurus_data_num");
	oci_execute($sel);
	$res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$min_date=$res['MIN_DATE'];
	$max_date=$res['MAX_DATE'];
	return array($min_date,$max_date);
}


function get_last_cgu_published () {
	global $dbh;
	$sel=oci_parse($dbh," select cgu_num, cgu_text ,to_char(cgu_date,'DD/MM/YYYY') as char_cgu_date, cgu_date , published,to_char(published_date,'DD/MM/YYYY') as char_published_date,to_char(unpublished_date,'DD/MM/YYYY') as char_unpublished_date 
	 from DWH_ADMIN_CGU where published=1 order by cgu_date desc");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$cgu_num=$r['CGU_NUM'];
	if ($r['CGU_TEXT']!='') {
		$cgu_text=$r['CGU_TEXT']->load();
		$char_cgu_date=$r['CHAR_CGU_DATE'];
		$published=$r['PUBLISHED'];
		$published_date=$r['CHAR_PUBLISHED_DATE'];
		$unpublished_date=$r['CHAR_UNPUBLISHED_DATE'];
		$tableau_result=array('cgu_num'=>$cgu_num,'cgu_text'=>$cgu_text,'cgu_date'=>$char_cgu_date,'published'=>$published,'published_date'=>$published_date,'unpublished_date'=>$unpublished_date);
	}
	return $tableau_result;
}

function verif_cgu_signed($user_num) {
	global $dbh;
	$cgu=get_last_cgu_published ();
	$cgu_num=$cgu['cgu_num'];
	if ($cgu_num!='') {
		$sel=oci_parse($dbh,"select count(*) as nb from DWH_ADMIN_CGU_user where cgu_num=$cgu_num  and user_num=$user_num");
		oci_execute($sel);
		$res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$nb=$res['NB'];
	} else {
		$nb=1;
	}
	return $nb;
}

function signed_cgu($user_num) {
	global $dbh;
	$cgu=get_last_cgu_published ();
	$cgu_num=$cgu['cgu_num'];
	if ($cgu_num!='') {
		$ins=oci_parse($dbh,"insert into DWH_ADMIN_CGU_user (cgu_num,user_num,date_signature) values ( $cgu_num ,$user_num,sysdate)");
		oci_execute($ins);
	}
}

function check_format_date ($date,$format) {
	if (strtolower($format)=='dd/mm/yyyy') {
		$test=preg_match("/^[0-9][0-9]?\/[0-9][0-9]?\/[0-9][0-9][0-9][0-9]$/",$date);
	}
	if (strtolower($format)=='yyyy/mm/dd') {
		$test=preg_match("/^[0-9][0-9][0-9][0-9]\/[0-9][0-9]?\/[0-9][0-9]?$/",$date);
	}
	if (strtolower($format)=='dd/mm/yyyy hh24:mi') {
		$test=preg_match("/^[0-9][0-9]?\/[0-9][0-9]?\/[0-9][0-9][0-9][0-9] [0-9][0-9]?:[0-9][0-9]?$/",$date);
	}
	return $test;
}

function get_thesaurus_data_concept ($data_search,$thesaurus_code,$thesaurus_data_father_num) {
	global $dbh;
	$query_data='';
	$query_thesaurus='';

	if (preg_match("/^[0-9]+$/",$data_search)) {
		$req_num_thesaurus=" or thesaurus_data_num='$requete_texte' ";
	} else {
		$req_num_thesaurus="";
	}
	
	$data_search=preg_replace("/\s+/"," ",$data_search);
	$data_search_sans_pourcent=preg_replace("/\s/"," and ",$data_search);
	$data_search_avec_pourcent=preg_replace("/\s/","% and ",$data_search);
	if ($data_search!='') {
		$query_data=" and  ( contains(description,'$data_search_avec_pourcent%')>0 or contains(description,'$data_search')>0  or concept_code='$data_search' $req_num_thesaurus) ";
	}
	if ($thesaurus_code!='') {
		$query_thesaurus=" and dwh_thesaurus_data.thesaurus_code='$thesaurus_code' ";
	}
	if ($thesaurus_data_father_num=='') {
		$sel=oci_parse($dbh," select thesaurus_data_num,thesaurus_code,concept_code,concept_str,info_complement, measuring_unit, value_type, list_values, thesaurus_parent_num, description, count_data_used
			 from dwh_thesaurus_data where 1=1 $query_data $query_thesaurus 
			  order by thesaurus_code,concept_code");
		oci_execute($sel);
		$nb=oci_fetch_all($sel,$r, null, null, OCI_FETCHSTATEMENT_BY_ROW); 
	} else {
		if ($data_search!='') {
			$req="select thesaurus_data_num,a.thesaurus_code,concept_code,concept_str,info_complement, measuring_unit, value_type, list_values, thesaurus_parent_num, description, count_data_used
			from dwh_thesaurus_data a,
	           dwh_thesaurus_data_graph b
	           where 
	           a.thesaurus_code=b.thesaurus_code and 
	            a.thesaurus_code='$thesaurus_code' and  
	           a.thesaurus_data_num=b.thesaurus_data_son_num and 
	           b.thesaurus_data_father_num=$thesaurus_data_father_num  and 
	           distance=1  and 
	           ( ( contains(description,'$data_search_avec_pourcent%')>0 or contains(description,'$data_search_sans_pourcent')>0    $req_num_thesaurus )
	            or a.concept_code='$data_search'
	           or a.thesaurus_data_num in 
	           	(select thesaurus_data_father_num from dwh_thesaurus_data a, dwh_thesaurus_data_graph b where    a.thesaurus_code='$thesaurus_code' and  
	           a.thesaurus_code=b.thesaurus_code and a.thesaurus_data_num=b.thesaurus_data_son_num and    ( contains(description,'$data_search_avec_pourcent%')>0 or contains(description,'$data_search_sans_pourcent')>0    or a.concept_code='$data_search' $req_num_thesaurus ))
	          )
			order by concept_code";
		} else {
			$req="select thesaurus_data_num,a.thesaurus_code,concept_code,concept_str,info_complement, measuring_unit, value_type, list_values, thesaurus_parent_num, description, count_data_used
			from dwh_thesaurus_data a,
	           dwh_thesaurus_data_graph b
	           where 
	            a.thesaurus_code='$thesaurus_code' and  
	           a.thesaurus_code=b.thesaurus_code and 
	           a.thesaurus_data_num=b.thesaurus_data_son_num and 
	           b.thesaurus_data_father_num=$thesaurus_data_father_num  and 
	           distance=1
	           order by concept_code ";
		}
		$sel=oci_parse($dbh,$req);
		oci_execute($sel);
		$nb=oci_fetch_all($sel,$r, null, null, OCI_FETCHSTATEMENT_BY_ROW); 
	}
	return $r;
}




function get_list_actu ($filtre_sql) {
	global $dbh;
	$tableau_result=array();
	
	$sel=oci_parse($dbh," select actu_num, actu_text ,to_char(actu_date,'DD/MM/YYYY') as char_actu_date, actu_date , published,to_char(published_date,'DD/MM/YYYY') as char_published_date, alert  from DWH_ADMIN_ACTU where 1=1 $filtre_sql order by actu_date desc");
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$actu_num=$r['ACTU_NUM'];
		if ($r['ACTU_TEXT']!='') {
			$actu_text=$r['ACTU_TEXT']->load();
		}
		$char_actu_date=$r['CHAR_ACTU_DATE'];
		$published=$r['PUBLISHED'];
		$published_date=$r['CHAR_PUBLISHED_DATE'];
		$alert=$r['ALERT'];
		$tableau_result[]=array('actu_num'=>$actu_num,'actu_text'=>$actu_text,'actu_date'=>$char_actu_date,'published'=>$published,'published_date'=>$published_date,'alert'=>$alert);
	}
	return $tableau_result;
}

function get_parameters() {
	global $dbh;
	$tableau_result=array();
	$sel=oci_parse($dbh," select contact from DWH_ADMIN_PARAMETERS");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	if ($r['CONTACT']!='') {
		$contact=$r['CONTACT']->load();
	}
	$tableau_result['contact']=$contact;
	return $tableau_result;

}

?>