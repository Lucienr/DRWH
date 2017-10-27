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
$user_num_session=$_SESSION['dwh_user_num'];
if ($user_num_session!='') {
	$sel=oci_parse($dbh,"delete from dwh_user_online where user_num='$user_num_session' and database='DrWH'");
	oci_execute($sel);
	$user_name=get_user_information ($user_num_session,'pn');
	$sel=oci_parse($dbh,"insert into dwh_user_online (user_num,last_update_date,database,user_name) values ('$user_num_session',sysdate,'DrWH','$user_name')");
	oci_execute($sel);
	
}

$sel_var1=oci_parse($dbh,"select  document_origin_str,document_origin_code from dwh_admin_document_origin where document_origin_str is not null order by document_origin_str ");
oci_execute($sel_var1);
while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
	$document_origin_code=$r['DOCUMENT_ORIGIN_CODE'];
	$document_origin_str=$r['DOCUMENT_ORIGIN_STR'];
	$tableau_global_document_origin_code[$document_origin_code]=$document_origin_str;
}



$stop_words=array ('des','les','du','de','la','avec','une','un','mais','or','and','et','ne','le');

function ajouter_formulaire_texte_vierge ($num_filtre,$query_type) {
        global $dbh, $tableau_global_document_origin_code, $tableau_global_contexte,$liste_service_session,$user_num_session,$thesaurus_code_labo,$thesaurus_code_pmsi;
        print "
        <div id=\"id_div_filtre_texte_$num_filtre\" class=\"div_filtre\">";
	print "<div class=\"circle\">$num_filtre</div>";
        if ($num_filtre>1) {
	        print "<div style=\"position:absolute;left:400px;z-index:22;top:-6;color:grey;cursor:pointer;\" onclick=\"supprimer_formulaire_texte_vierge($num_filtre);\">x</div>";
	}

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
		</div>
		<br><span id=\"id_span_filtre_texte_ouvrir_avance_$num_filtre\" class=\"filtre_texte_ouvrir_avance\"  style=\"display:inline;\">
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
        $sel = oci_parse($dbh,"select department_num,department_str from  dwh_thesaurus_department order by department_str" );   
        oci_execute($sel);
        while ($row = oci_fetch_array($sel, OCI_ASSOC)) {
                $department_num=$row['DEPARTMENT_NUM'];
                $department_str=$row['DEPARTMENT_STR'];
                print "<option value=\"$department_num\" id=\"id_option_filtre_unite_heberg_".$num_filtre."_".$department_num."\">$department_str</option>";
        }
        
        print "</select></td></tr>
                <tr><td>".get_translation('DOCUMENT_DATE','Date du document')." :</td><td>".get_translation('DATE_FROM','du')." <input type=\"text\" id=\"id_input_filtre_date_deb_document_$num_filtre\" name=\"date_deb_document_$num_filtre\" class=\"filtre_date_document\" size=\"10\" value=\"\"> 
                                                ".get_translation('DATE_TO','au')." <input type=\"text\" id=\"id_input_filtre_date_fin_document_$num_filtre\" name=\"date_fin_document_$num_filtre\" class=\"filtre_date_document\" size=\"10\" value=\"\"  onblur=\"calcul_nb_resultat_filtre($num_filtre,true);\"></td></tr>
                <tr><td>".get_translation('INFORMATION_AVALAIBLE_PERIOD_AT_LEAST',"Information présente sur une période d'au moins")." : </td><td> <input type=\"text\" id=\"id_input_filtre_periode_document_$num_filtre\" name=\"periode_document_$num_filtre\" class=\"filtre_date_document\" size=\"3\" value=\"\"> ".get_translation('YEARS','ans')." </td></tr>
                 <tr><td>".get_translation('CONTEXT','Contexte')." : </td><td><select id=\"id_select_filtre_contexte_$num_filtre\" name=\"context_$num_filtre\"  class=\"filtre_contexte\"  onchange=\"calcul_nb_resultat_filtre($num_filtre,true);\"><option value=''></option>";
        foreach ($tableau_global_contexte as $context => $libelle_contexte) {
                print "<option value=\"$context\">$libelle_contexte</option>";
        }               
        print "</select></td></tr>
                 <tr><td>".get_translation('NEGATION','Négation :')." </td><td><select id=\"id_select_filtre_certitude_$num_filtre\" name=\"certainty_$num_filtre\"  class=\"filtre_contexte\"  onchange=\"calcul_nb_resultat_filtre($num_filtre,true);\">";
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
                        <td>".get_translation('PERIOD_OF_1ST_VISIT','Période de 1ere venue').": ".get_translation('DATE_FROM','du')." <input type=\"text\" id=\"id_input_date_deb_1ervenue\" name=\"date_deb_1ervenue\" class=\"filtre_date_document\" size=\"10\" value=\"\"> 
                         	".get_translation('DATE_TO','au')." <input type=\"text\" id=\"id_input_date_fin_1ervenue\" name=\"date_fin_1ervenue\" class=\"filtre_date_document\" size=\"10\" value=\"\">
                        </td>
                </tr>
                <tr>
                        <td>".get_translation('MINIMUM_FOLLOW_UP','Follow up de minimum')." : <input type=\"text\" id=\"id_input_duree_minimum_prise_en_charge\" name=\"duree_minimum_prise_en_charge\" class=\"filtre_date_document\" size=\"3\" value=\"\">  ".get_translation('YEARS','ans')."</td>
                </tr>
                <tr>
                        <td>".get_translation('EXCLUDE_PATIENTS_FROM_THESE_COHORTS','Exclure les patients de ces cohortes')." : <br>
                <select  id=\"id_select_filtre_cohorte_exclue\" name=\"cohorte_exclue[]\" class=\"chosen-select filtre_unite_heberg\"  data-placeholder=\"Choisissez une ou plusieurs cohortes\" multiple>";
                               lister_mes_cohortes_option ($user_num_session,'id_select_filtre_cohorte_exclue');
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
                ajouter_formulaire_texte_vierge ($num_filtre,$query_type);
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
                $date_deb_document= trim($filtre_texte->document_date_start);
                $date_fin_document= trim($filtre_texte->document_date_end);
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
	                        document.getElementById('id_input_filtre_texte_$num_filtre').value=\"$text\"; ";
	        }
                if ($query_type=='code') {
	        	$javascript.="ajouter_formulaire_code($num_filtre,'$thesaurus_data_num');";
	                $javascript.="document.getElementById('id_input_filtre_texte_$num_filtre').value=\"$text\"; ";
	        	
	        	$javascript.=analyse_chaine_requete_code ($thesaurus_data_num,$chaine_requete_code,'javascript',$num_filtre);
	        }
                        
                if ($exclure==1) {
                	$javascript.="document.getElementById('id_input_filtre_exclure_$num_filtre').checked=true;";
                }
                if ($etendre_syno==1) {
                	$javascript.="document.getElementById('id_checkbox_etendre_syno_$num_filtre').checked=true;";
                }
                $javascript.="
                        document.getElementById('id_input_filtre_date_deb_document_$num_filtre').value=\"$date_deb_document\";
                        document.getElementById('id_input_filtre_date_fin_document_$num_filtre').value=\"$date_fin_document\";
                        document.getElementById('id_input_filtre_periode_document_$num_filtre').value=\"$periode_document\";
                        document.getElementById('id_input_filtre_age_deb_document_$num_filtre').value=\"$age_deb_document\";
                        document.getElementById('id_input_filtre_age_fin_document_$num_filtre').value=\"$age_fin_document\";
                        document.getElementById('id_input_filtre_agemois_deb_document_$num_filtre').value=\"$agemois_deb_document\";
                        document.getElementById('id_input_filtre_agemois_fin_document_$num_filtre').value=\"$agemois_fin_document\";
                        document.getElementById('id_select_filtre_contexte_$num_filtre').value=\"$context\";
                        document.getElementById('id_select_filtre_certitude_$num_filtre').value=\"$certainty\";
                        document.getElementById('id_input_filtre_num_filtre_$num_filtre').value=\"$num_filtre\";
                        document.getElementById('id_input_nbresult_atomique_$num_filtre').value=\"$texte_nbresult\";
                        document.getElementById('id_span_nbresult_atomique_$num_filtre').innerHTML=\"$texte_nbresult\";
                ";
                $tableau_hospital_department_list=array();
                $tableau_hospital_department_list=explode(',',$hospital_department_list);
                foreach ($tableau_hospital_department_list as $department_num) {
                        if ($department_num!='') {
                                $javascript.="document.getElementById('id_option_filtre_unite_heberg_".$num_filtre."_".$department_num."').selected=true;";
                        }
                }
                
                $tableau_document_origin_code=array();
                $tableau_document_origin_code=explode(',',$document_origin_code);
                foreach ($tableau_document_origin_code as $document_origin_code) {
                        if ($document_origin_code!='') {
                                $javascript.="document.getElementById('id_option_filtre_document_origin_code_".$num_filtre."_".$document_origin_code."').selected=true;";
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
		        $date_deb_document!='' ||
		        $date_fin_document!='' ||
		        $periode_document!='' ||
		        $document_origin_code!='' ||
		        $context!='' ||
		        $hospital_department_list!='' ||
		        $certainty!='1'
		        ) {
	        	$javascript.="document.getElementById('id_a_filtre_texte_ouvrir_avance_".$num_filtre."').style.color='red';";
	        }
                
        }
        $javascript.="calcul_nb_resultat_filtre($num_filtre,true);";
        
        print "$javascript";
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
        if ($info_xml->sex) {
                $sex=$info_xml->sex;
       		if ($sex!='') {
                	$javascript.="document.getElementById('id_sex').value=\"$sex\";";
	                $filtre_patient='ok';
	        }
        }
        if ($info_xml->age_start) {
                $age_deb=$info_xml->age_start;
       		if ($age_deb!='') {
	                $javascript.="document.getElementById('id_age_deb').value=\"$age_deb\";";
	                $filtre_patient='ok';
	        }
        }
        if ($info_xml->age_end) {
                $age_fin=$info_xml->age_end;
       		if ($age_fin!='') {
	                $javascript.="document.getElementById('id_age_fin').value=\"$age_fin\";";
	                $filtre_patient='ok';
	        }
        }
        
        
        if ($info_xml->alive_death) {
                $vivant_dcd=$info_xml->alive_death;
       		if ($vivant_dcd=='vivant') {
	                $javascript.="document.getElementById('id_vivant').checked=true;";
	                $javascript.="document.getElementById('id_decede').checked=false;";
	                $filtre_patient='ok';
	        }
       		if ($vivant_dcd=='decede') {
	                $javascript.="document.getElementById('id_vivant').checked=false;";
	                $javascript.="document.getElementById('id_decede').checked=true;";
	                $filtre_patient='ok';
	        }
        }
        if ($info_xml->age_death_start) {
                $age_dcd_deb=$info_xml->age_death_start;
       		if ($age_dcd_deb!='') {
	                $javascript.="document.getElementById('id_age_dcd_deb').value=\"$age_dcd_deb\";";
	                $filtre_patient='ok';
	        }
        }
        if ($info_xml->age_death_end) {
                $age_dcd_fin=$info_xml->age_death_end;
       		if ($age_dcd_fin!='') {
	                $javascript.="document.getElementById('id_age_dcd_fin').value=\"$age_dcd_fin\";";
	                $filtre_patient='ok';
	        }
        }
        
        
        if ($info_xml->first_stay_date_start) {
                $date_deb_1ervenue=$info_xml->first_stay_date_start;
       		if ($date_deb_1ervenue!='') {
	                $javascript.="document.getElementById('id_input_date_deb_1ervenue').value=\"$date_deb_1ervenue\";";
	                $filtre_patient='ok';
	        }
        }
        if ($info_xml->first_stay_date_end) {
                $date_fin_1ervenue=$info_xml->first_stay_date_end;
       		if ($date_fin_1ervenue!='') {
	                $javascript.="document.getElementById('id_input_date_fin_1ervenue').value=\"$date_fin_1ervenue\";";
	                $filtre_patient='ok';
	        }
        }
        if ($info_xml->minimum_period_folloup) {
                $duree_minimum_prise_en_charge=$info_xml->minimum_period_folloup;
       		if ($duree_minimum_prise_en_charge!='') {
	                $javascript.="document.getElementById('id_input_duree_minimum_prise_en_charge').value=\"$duree_minimum_prise_en_charge\";";
	                $filtre_patient='ok';
	        }
        }
        
        
        if ($info_xml->list_excluded_cohort) {
		$liste_cohorte_exclue= trim($info_xml->list_excluded_cohort);
	        $tableau_cohorte_exclue=array();
	        $tableau_cohorte_exclue=explode(',',$liste_cohorte_exclue);
	        foreach ($tableau_cohorte_exclue as $cohorte_exclue) {
	                if ($cohorte_exclue!='') {
	                        $javascript.="document.getElementById('id_select_filtre_cohorte_exclue_$cohorte_exclue').selected=true;";
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
        global $dbh,$liste_uf_session,$user_num_session;
        $requete_sql='';
        if ($xml!='') {
		list($query_key,$datamart_num,$select_sql)=creer_requete_sql ($xml);
		if ($query_key!='') {
			$sel = oci_parse($dbh,"select count(*) as  NB from dwh_tmp_preresult where query_key='$query_key' and trunc(tmpresult_date)=trunc(sysdate) and datamart_num=$datamart_num and user_num=$user_num_session");   
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
	                        
				passthru( "php passthru_result_temp.php $user_num_session $datamart_num \"$query_key\"  >> upload/log_chargement_result_temp.$user_num_session.$datamart_num.txt 2>&1 &");
	                } else {
	                        $sel = oci_parse($dbh,"select count(distinct patient_num) as  NB from dwh_tmp_preresult where query_key='$query_key' and trunc(tmpresult_date)=trunc(sysdate) and datamart_num=$datamart_num and user_num=$user_num_session");   
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
                $date_deb_document= trim($info_xml->document_date_start);
                $date_fin_document= trim($info_xml->document_date_end);
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
                if ($text!='' ||  $chaine_requete_code!='') {
                        if ($hospital_department_list!='') {
                                $filtre_sql.=" and department_num in ($hospital_department_list)  ";
                        }
                        if ($document_origin_code!='') {
                        	$req_document_origin_code=str_replace(",","','",$document_origin_code);
                                $filtre_sql.=" and document_origin_code in ('$req_document_origin_code') ";
                        }
                        if ($date_deb_document!='') {
                                $filtre_sql.=" and document_date>=to_date('$date_deb_document','DD/MM/YYYY') ";
                        }
                        if ($date_fin_document!='') {
                                $filtre_sql.=" and document_date<=to_date('$date_fin_document','DD/MM/YYYY') ";
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
				$select_sql="";
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
				if ($etendre_syno==1) {
	               			if ($datamart_num==0) {
	               				$select_sql="select document_num,'$query_key' as query_key,sysdate as query_date ,patient_num,$user_num_session as user_num,$datamart_num as datamart_num,document_origin_code,to_char(document_date,'DD/MM/YYYY HH24:MI') as document_date
	               				 from dwh_text 
	               				 where contains(dwh_text.enrich_text,'$text')>0 $requete_certitude $requete_contexte $filtre_sql   ";
	               				 $select_sql_id_document="select document_num from dwh_text where contains(dwh_text.enrich_text,'$text')>0 $requete_certitude $requete_contexte $filtre_sql   ";
		                        } else {
                                                
			                        $select_sql="select document_num,'$query_key' as query_key,sysdate as query_date ,patient_num,$user_num_session as user_num,$datamart_num as datamart_num,document_origin_code,to_char(document_date,'DD/MM/YYYY HH24:MI') as document_date
                                                 from dwh_text
                                                where exists (select patient_num from dwh_datamart_result where datamart_num = $datamart_num and dwh_datamart_result.patient_num=dwh_text.patient_num)  and contains(dwh_text.enrich_text,'$text')>0   $requete_certitude $requete_contexte $filtre_sql   ";
  
                                                
	               				$select_sql_id_document="select document_num from dwh_text where exists (select patient_num from dwh_datamart_result where datamart_num = $datamart_num and dwh_datamart_result.patient_num=dwh_text.patient_num)  and contains(dwh_text.enrich_text,'$text')>0   $requete_certitude $requete_contexte $filtre_sql";
		                        }
		                } else {
	               			if ($datamart_num==0) {
			                        $select_sql="select  document_num,'$query_key' as query_key,sysdate as query_date ,patient_num,$user_num_session as user_num,$datamart_num as datamart_num,document_origin_code,to_char(document_date,'DD/MM/YYYY HH24:MI') as document_date
		                                                from  dwh_text
		                                                where contains(dwh_text.text,'$text')>0 $requete_certitude $requete_contexte $filtre_sql ";
	               				 $select_sql_id_document="select document_num from dwh_text where contains(dwh_text.text,'$text')>0 $requete_certitude $requete_contexte $filtre_sql   ";
		                        } else {
		                                                
			                        $select_sql="select document_num,'$query_key' as query_key,sysdate as query_date ,patient_num,$user_num_session as user_num,$datamart_num as datamart_num,document_origin_code,to_char(document_date,'DD/MM/YYYY HH24:MI') as document_date
		                                                from  dwh_text
		                                                where  exists (select patient_num from dwh_datamart_result where datamart_num = $datamart_num and dwh_datamart_result.patient_num=dwh_text.patient_num)  and  contains(dwh_text.text,'$text')>0   $requete_certitude $requete_contexte $filtre_sql   ";
	               				 $select_sql_id_document="select document_num from dwh_text where  exists (select patient_num from dwh_datamart_result where datamart_num = $datamart_num and dwh_datamart_result.patient_num=dwh_text.patient_num)  and  contains(dwh_text.text,'$text')>0   $requete_certitude $requete_contexte $filtre_sql   ";
		                        }
		                }
                        }
			if ($chaine_requete_code!='') {
				$requete_code=analyse_chaine_requete_code ($thesaurus_data_num,$chaine_requete_code,'sql','');
               			if ($datamart_num==0) {
		                        $select_sql="select  document_num,'$query_key' as query_key,sysdate as query_date,patient_num,$user_num_session as user_num,$datamart_num as datamart_num,document_origin_code,to_char(document_date,'DD/MM/YYYY HH24:MI') as document_date
	                                                from dwh_data
	                                                where $requete_code  $filtre_sql  ";
	               			$select_sql_id_document="select document_num from dwh_data where $requete_code  $filtre_sql ";
	                        } else {
	                                                
		                        $select_sql="select  document_num,'$query_key' as query_key,sysdate as query_date,patient_num,$user_num_session as user_num,$datamart_num as datamart_num,document_origin_code,to_char(document_date,'DD/MM/YYYY HH24:MI') as document_date
	                                                from dwh_data
	                                                where  exists (select patient_num from dwh_datamart_result where datamart_num = $datamart_num and dwh_datamart_result.patient_num=dwh_data.patient_num)  and $requete_code  $filtre_sql";
	               			$select_sql_id_document="select document_num from dwh_data  where  exists (select patient_num from dwh_datamart_result where datamart_num = $datamart_num and dwh_datamart_result.patient_num=dwh_data.patient_num)  and $requete_code  $filtre_sql ";
	                        }
			}
			if ($periode_document!='') {
				$periode_document=preg_replace("/\./",",","$periode_document");
				$select_sql="$select_sql and  patient_num in  
                                                        (
                                                        select patient_num from dwh_document where  document_date is not null
                                                        and document_num in ($select_sql_id_document)
                                                         having(max(document_date)-min(document_date))>365*'$periode_document' group by patient_num
                                                        )";
			}
			
			
        	} else {
			if ($datamart_num!='0') {
                                                
	                        $select_sql="select  document_num,'$query_key' as query_key,sysdate as query_date,patient_num,$user_num_session as user_num,$datamart_num as datamart_num,document_origin_code,to_char(document_date,'DD/MM/YYYY HH24:MI') as document_date
                                                from dwh_document
                                                where exists (select patient_num from dwh_datamart_result where datamart_num = $datamart_num  and dwh_datamart_result.patient_num=dwh_document.patient_num)  ";
			}
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
	        $date_deb_document= trim($info_xml->document_date_start);
	        $date_fin_document= trim($info_xml->document_date_end);
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
		$query_key=strtolower(nettoyer_pour_insert("$text;$etendre_syno;$hospital_department_list;$document_origin_code;$date_deb_document;$date_fin_document;$age_deb_document;$age_fin_document;$agemois_deb_document;$agemois_fin_document;$context;$certainty;$chaine_requete_code;$thesaurus_data_num;$datamart_num;$periode_document"));
	}
	return $query_key;

}




// contrainte temporelle //

function creer_requete_sql_contrainte_temporelle_passthru ($xml,$query_key_a,$query_key_b,$datamart_num) {
        global $dbh,$liste_uf_session,$user_num_session;
        if ($datamart_num=='') {
	        $datamart_num=0;
	}
        $requete_sql='';
        if ($xml!='') {
		list($query_key,$select_sql)=creer_requete_sql_contrainte_temporelle ($xml,$query_key_a,$query_key_b,$datamart_num);
		if ($query_key!='') {
			$sel = oci_parse($dbh,"select count(*) as  NB from dwh_tmp_preresult where query_key='$query_key' and trunc(tmpresult_date)=trunc(sysdate) and datamart_num=$datamart_num and user_num=$user_num_session");   
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
	                        
				passthru( "php passthru_result_temp.php $user_num_session $datamart_num \"$query_key\"  >> upload/log_chargement_result_temp.$user_num_session.$datamart_num.txt 2>&1 &");
	                } else {
	                        $sel = oci_parse($dbh,"select count(distinct patient_num) as  NB from dwh_tmp_preresult where query_key='$query_key' and trunc(tmpresult_date)=trunc(sysdate) and datamart_num=$datamart_num and user_num=$user_num_session");   
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
		
                if ($query_key!='' && $query_key_a!='' && $query_key_b!='') {
			$select_sql="";
			if ($type_contrainte=='simultaneous') {
				$select_sql= "
				select a.document_num,'$query_key' as query_key,sysdate as query_date ,a.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,a.document_origin_code,to_char(a.document_date,'DD/MM/YYYY HH24:MI') as document_date
               				 from dwh_tmp_preresult a, dwh_tmp_preresult b
               				 where a.query_key='$query_key_a' and a.user_num=$user_num_session
               				 and  b.query_key='$query_key_b' and b.user_num=$user_num_session
               				 and a.patient_num=b.patient_num
               				 and a.document_date=b.document_date
	               			union
				select b.document_num,'$query_key' as query_key,sysdate as query_date ,b.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,b.document_origin_code,to_char(b.document_date,'DD/MM/YYYY HH24:MI') as document_date
	               			 from dwh_tmp_preresult a, dwh_tmp_preresult b
               				 where a.query_key='$query_key_a' and a.user_num=$user_num_session
               				 and  b.query_key='$query_key_b' and b.user_num=$user_num_session 
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
					select a.document_num,'$query_key' as query_key,sysdate as query_date ,a.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,a.document_origin_code,to_char(a.document_date,'DD/MM/YYYY HH24:MI') as document_date
	               				 from dwh_tmp_preresult a, dwh_tmp_preresult b
	               				 where a.query_key='$query_key_a' and a.user_num=$user_num_session 
	               				 and  b.query_key='$query_key_b' and b.user_num=$user_num_session 
               				 and a.patient_num=b.patient_num
	               				 and a.document_date<b.document_date and b.document_date-a.document_date> $req_temps
	               			union
					select b.document_num,'$query_key' as query_key,sysdate as query_date ,b.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,b.document_origin_code,to_char(b.document_date,'DD/MM/YYYY HH24:MI') as document_date
	               				 from dwh_tmp_preresult a, dwh_tmp_preresult b
	               				 where a.query_key='$query_key_a' and a.user_num=$user_num_session 
	               				 and  b.query_key='$query_key_b' and b.user_num=$user_num_session 
               				 and a.patient_num=b.patient_num
	               				 and a.document_date<b.document_date and b.document_date-a.document_date> $req_temps
					";
				}
				if ($minmax=='minimum_exclusive') {
						$select_sql= "
						select a.document_num,'$query_key' as query_key,sysdate as query_date ,a.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,a.document_origin_code,to_char(a.document_date,'DD/MM/YYYY HH24:MI') as document_date
		               				 from dwh_tmp_preresult a, dwh_tmp_preresult b
		               				 where a.query_key='$query_key_a' and a.user_num=$user_num_session 
		               				 and  b.query_key='$query_key_b' and b.user_num=$user_num_session 
	               				 and a.patient_num=b.patient_num
		               				 and a.document_date<b.document_date and b.document_date-a.document_date> $req_temps
		               				 and b.patient_num not in (select c.patient_num from dwh_tmp_preresult c where c.query_key='$query_key_b' and c.user_num=$user_num_session and c.document_date<b.document_date and c.document_date>a.document_date)
		               				 and a.patient_num not in (select c.patient_num from dwh_tmp_preresult c where c.query_key='$query_key_a' and c.user_num=$user_num_session and c.document_date<b.document_date and c.document_date>a.document_date)
		               			union
						select b.document_num,'$query_key' as query_key,sysdate as query_date ,b.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,b.document_origin_code,to_char(b.document_date,'DD/MM/YYYY HH24:MI') as document_date
		               				 from dwh_tmp_preresult a, dwh_tmp_preresult b
		               				 where a.query_key='$query_key_a' and a.user_num=$user_num_session 
		               				 and  b.query_key='$query_key_b' and b.user_num=$user_num_session 
	               				 and a.patient_num=b.patient_num
		               				 and a.document_date<b.document_date and b.document_date-a.document_date> $req_temps
		               				 and b.patient_num not in (select c.patient_num from dwh_tmp_preresult c where c.query_key='$query_key_b' and c.user_num=$user_num_session and c.document_date<b.document_date and c.document_date>a.document_date)
		               				 and a.patient_num not in (select c.patient_num from dwh_tmp_preresult c where c.query_key='$query_key_a' and c.user_num=$user_num_session and c.document_date<b.document_date and c.document_date>a.document_date)
						";
				}
				if ($minmax=='maximum') {
					$select_sql= "
					select a.document_num,'$query_key' as query_key,sysdate as query_date ,a.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,a.document_origin_code,to_char(a.document_date,'DD/MM/YYYY HH24:MI') as document_date
	               				 from dwh_tmp_preresult a, dwh_tmp_preresult b
	               				 where a.query_key='$query_key_a' and a.user_num=$user_num_session 
	               				 and  b.query_key='$query_key_b' and b.user_num=$user_num_session 
               				 and a.patient_num=b.patient_num
	               				 and a.document_date<b.document_date and b.document_date-a.document_date< $req_temps
	               			union
					select b.document_num,'$query_key' as query_key,sysdate as query_date ,b.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,b.document_origin_code,to_char(b.document_date,'DD/MM/YYYY HH24:MI') as document_date
	               				 from dwh_tmp_preresult a, dwh_tmp_preresult b
	               				 where a.query_key='$query_key_a' and a.user_num=$user_num_session 
	               				 and  b.query_key='$query_key_b' and b.user_num=$user_num_session 
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
					select a.document_num,'$query_key' as query_key,sysdate as query_date ,a.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,a.document_origin_code,to_char(a.document_date,'DD/MM/YYYY HH24:MI') as document_date
	               				 from dwh_tmp_preresult a, dwh_tmp_preresult b
	               				 where a.query_key='$query_key_a' and a.user_num=$user_num_session 
	               				 and  b.query_key='$query_key_b' and b.user_num=$user_num_session
               				 and a.patient_num=b.patient_num
	               				 and abs(b.document_date-a.document_date)> $req_temps
	               			union
					select b.document_num,'$query_key' as query_key,sysdate as query_date ,b.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,b.document_origin_code,to_char(b.document_date,'DD/MM/YYYY HH24:MI') as document_date
	               				 from dwh_tmp_preresult a, dwh_tmp_preresult b
	               				 where a.query_key='$query_key_a' and a.user_num=$user_num_session 
	               				 and  b.query_key='$query_key_b' and b.user_num=$user_num_session 
               				 and a.patient_num=b.patient_num
	               				 and abs(b.document_date-a.document_date)> $req_temps
					";
				}
				if ($minmax=='minimum_exclusive') {
					$select_sql= "
					select a.document_num,'$query_key' as query_key,sysdate as query_date ,a.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,a.document_origin_code,to_char(a.document_date,'DD/MM/YYYY HH24:MI') as document_date
	               				 from dwh_tmp_preresult a, dwh_tmp_preresult b
	               				 where a.query_key='$query_key_a' and a.user_num=$user_num_session 
	               				 and  b.query_key='$query_key_b' and b.user_num=$user_num_session
               				 and a.patient_num=b.patient_num
	               				 and abs(b.document_date-a.document_date)> $req_temps
	               				 and b.patient_num not in (select c.patient_num from dwh_tmp_preresult c where c.query_key='$query_key_b' and c.user_num=$user_num_session and abs(a.document_date-c.document_date)< $req_temps)
	               				 and a.patient_num not in (select c.patient_num from dwh_tmp_preresult c where c.query_key='$query_key_a' and c.user_num=$user_num_session and abs(b.document_date-c.document_date)< $req_temps)
	               			union
					select b.document_num,'$query_key' as query_key,sysdate as query_date ,b.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,b.document_origin_code,to_char(b.document_date,'DD/MM/YYYY HH24:MI') as document_date
	               				 from dwh_tmp_preresult a, dwh_tmp_preresult b
	               				 where a.query_key='$query_key_a' and a.user_num=$user_num_session 
	               				 and  b.query_key='$query_key_b' and b.user_num=$user_num_session 
               				 and a.patient_num=b.patient_num
	               				 and abs(b.document_date-a.document_date)> $req_temps
	               				 and b.patient_num not in (select c.patient_num from dwh_tmp_preresult c where c.query_key='$query_key_b' and c.user_num=$user_num_session and abs(a.document_date-c.document_date)< $req_temps)
	               				 and a.patient_num not in (select c.patient_num from dwh_tmp_preresult c where c.query_key='$query_key_a' and c.user_num=$user_num_session and abs(b.document_date-c.document_date)< $req_temps)
					";
				}
				if ($minmax=='maximum') {
					$select_sql= "
					select a.document_num,'$query_key' as query_key,sysdate as query_date ,a.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,a.document_origin_code,to_char(a.document_date,'DD/MM/YYYY HH24:MI') as document_date
	               				 from dwh_tmp_preresult a, dwh_tmp_preresult b
	               				 where a.query_key='$query_key_a' and a.user_num=$user_num_session
	               				 and  b.query_key='$query_key_b' and b.user_num=$user_num_session
               				 and a.patient_num=b.patient_num
	               				 and abs(b.document_date-a.document_date)< $req_temps
	               			union
					select b.document_num,'$query_key' as query_key,sysdate as query_date ,b.patient_num,$user_num_session as user_num,$datamart_num as datamart_num,b.document_origin_code,to_char(b.document_date,'DD/MM/YYYY HH24:MI') as document_date
	               				 from dwh_tmp_preresult a, dwh_tmp_preresult b
	               				 where a.query_key='$query_key_a' and a.user_num=$user_num_session
	               				 and  b.query_key='$query_key_b' and b.user_num=$user_num_session
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
                        $sel = oci_parse($dbh,"select count(*) as  NB from dwh_tmp_preresult where query_key='$query_key' and trunc(tmpresult_date)=trunc(sysdate) and datamart_num=$datamart_num and user_num=$user_num_session");   
                        oci_execute($sel);
                        $r = oci_fetch_array($sel, OCI_ASSOC);
                        $nb=$r['NB'];
                        
                        if ($nb==0) {
				$select_sql=preg_replace("/to_char\(document_date,'DD\/MM\/YYYY HH24:MI'\) *as *document_date/","document_date",$select_sql);
				$sel = oci_parse($dbh, " insert  /*+ APPEND */ into dwh_tmp_preresult (document_num ,query_key , tmpresult_date,patient_num,user_num,datamart_num,document_origin_code,document_date)  $select_sql  ");   
				oci_execute($sel);
                        }
                        
                        if ($option=='patient_num') {
                                $requete_sql=" query_key='$query_key' and user_num=$user_num_session ";
                        }
                }
                if ($datamart_num==0) {
                        
                } else {
                        if ($option=='patient_num') {
                                 $requete_sql.=" and datamart_num=$datamart_num  ";
				$select_sql=preg_replace("/to_char\(document_date,'DD\/MM\/YYYY HH24:MI'\) *as *document_date/i","document_date",$select_sql);
				$sel = oci_parse($dbh, " insert  /*+ APPEND */ into dwh_tmp_preresult (document_num ,query_key , tmpresult_date,patient_num,user_num,datamart_num,document_origin_code,document_date)  $select_sql  ");   
				oci_execute($sel);
                        }
                }
        }
        return $requete_sql;
}


function lister_requete_temp ($datamart_num) {
        global $dbh,$user_num_session;
        $liste_requete_claire="<table border=\"0\" class=\"tableau_liste_requete\" id=\"id_tableau_liste_requete\" width=\"400px\">
        <thead><th style=\"width:18px;padding:2px;\">&nbsp;</th><th>".get_translation('DATE','date')."</th><th>".get_translation('QUERIES','Requêtes')."</th></thead>
        <tbody>";
        $sel = oci_parse($dbh, " select query_num,xml_query, DATE_REQUETE_CHAR,pin from (select QUERY_NUM,xml_query , to_char(QUERY_DATE,'DD/MM/YYYY HH24:MI') as DATE_REQUETE_CHAR, QUERY_DATE,pin, rownum from dwh_query where datamart_num=$datamart_num and user_num=$user_num_session order by pin desc ,query_date desc) t where rownum<400");          
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
        and sysdate-load_date<150
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


function lister_requete_detail ($query_num) {
        global $dbh,$user_num_session;
        print "<table border=\"0\" class=\"tablefin\">
        	<thead>
        		<tr>";
       	$i=0;
        $sel = oci_parse($dbh, " select distinct to_number(to_char(load_date,'MM')) as mois_chargement,to_number(to_char(load_date,'YYYY')) as an_chargement from dwh_query_result where query_num=$query_num order by to_number(to_char(load_date,'YYYY'))  asc,to_number(to_char(load_date,'MM'))  asc");   
        oci_execute($sel);
        while ($r = oci_fetch_array($sel, OCI_ASSOC)) {
                $mois_chargement=$r['MOIS_CHARGEMENT'];	
                $an_chargement=$r['AN_CHARGEMENT'];	
        	if (strlen($mois_chargement)==1) {
        		$mois_chargement="0".$mois_chargement;
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
      
	$debut_valeur='';
       
	print "<tr>";
	for ($i=0;$i<count($tableau_mois_an_chargement);$i++) {
		$mois_an=$tableau_mois_an_chargement[$i];
		$sel_chargement = oci_parse($dbh, " select  count(*) as nb_new_patient from dwh_query_result where query_num=$query_num and to_char(load_date,'MM/YYYY')='$mois_an' ");
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
		print " <a href=\"moteur.php?action=rechercher_dans_requete_sauvegardee&query_num=$query_num&load_date=$mois_an\"><img src=\"images/search.png\" border=\"0\" style=\"cursor:pointer;vertical-align:middle\"></a></td>";
		if ($nb_new_patient!=0) {
			$debut_valeur='ok';
		}
	}
       print "</tr>";
       
	print "<tr>";
	for ($i=0;$i<count($tableau_mois_an_chargement);$i++) {
		$mois_an=$tableau_mois_an_chargement[$i];
		print "<td style=\"vertical-align:top;\">";
		print "<table id=\"id_tableau_patient_requete\" style=\"border: 0px solid black;\">";
		$sel_chargement = oci_parse($dbh,"select dwh_query_result.patient_num, lastname from dwh_query_result, dwh_patient where dwh_patient.patient_num=dwh_query_result.patient_num and query_num=$query_num and to_char(load_date,'MM/YYYY')='$mois_an' order by lastname");
		oci_execute($sel_chargement);
		while ($r_chargement = oci_fetch_array($sel_chargement, OCI_ASSOC)) {
			$patient_num=$r_chargement['PATIENT_NUM'];
			$patient= afficher_patient($patient_num,'requete','','');
			if ($patient!='') {
				print "<tr id=\"id_tr_patient_cohorte_$patient_num\"  style=\"border: 0px solid black;\" onmouseover=\"this.style.backgroundColor='#dcdff5';\" onmouseout=\"this.style.backgroundColor='#ffffff';\"><td  style=\"border: 0px solid black;\">$patient</td></tr>";
		        }
		}
		print "</table>";
		print "</td>";
	}
       print "</tr>";
       print "</tbody></table>";
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
        $sel_texte = oci_parse($dbh,"select patient_num from dwh_document where document_num=$document_num" );   
        oci_execute($sel_texte);
        $r= oci_fetch_array($sel_texte, OCI_ASSOC);
        $patient_num=$r['PATIENT_NUM'];
        
        $sel_patient= oci_parse($dbh,"select lastname,lower(firstname) as firstname,to_char(birth_date,'DD/MM/YYYY') as birth_date,sex,
        to_char(birth_date,'DD') as jour_nais,
        to_char(birth_date,'MM') as mois_nais,
        to_char(birth_date,'YYYY') as an_nais ,
        to_char(birth_date,'DD/MM/YY') as date_nais_yy,
        residence_address,
        zip_code,
        residence_city,
        maiden_name  from dwh_patient where patient_num=$patient_num  " );   
        oci_execute($sel_patient);
        $row_patient = oci_fetch_array($sel_patient, OCI_ASSOC);
        $lastname=$row_patient['LASTNAME'];               
        $firstname=ucfirst ($row_patient['FIRSTNAME']);               
        $birth_date=$row_patient['BIRTH_DATE'];             
        $sex=$row_patient['SEX'];     
        $jour_nais=$row_patient['JOUR_NAIS'];   
        $mois_nais=$row_patient['MOIS_NAIS'];
        $an_nais=$row_patient['AN_NAIS'];
        $date_nais_yy=$row_patient['DATE_NAIS_YY'];
        $jour_nais=preg_replace("/^0/","0?",$jour_nais);
        $residence_address=$row_patient['RESIDENCE_ADDRESS'];
        $zip_code=$row_patient['ZIP_CODE'];
        $residence_city=$row_patient['RESIDENCE_CITY'];
        $maiden_name=$row_patient['MAIDEN_NAME'];
        $hospital_patient_id=get_master_patient_id ($patient_num);
        
        $sel_patient= oci_parse($dbh,"select  trunc((document_date-to_date('$birth_date','DD/MM/YYYY'))/365) as age_an, trunc((document_date-to_date('$birth_date','DD/MM/YYYY'))*12/365) as age_mois from dwh_document where document_num=$document_num  " );   
        oci_execute($sel_patient);
        $row_patient = oci_fetch_array($sel_patient, OCI_ASSOC);
        $age_mois_doc=$row_patient['AGE_MOIS'];         
        $age_an_doc=$row_patient['AGE_AN'];             
        
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
	
	if ($option=='clair') {
		$res="$concept_str ";
		if ($measuring_unit!='') {
			$res.= " ($measuring_unit) ";
		}
		$res.=" $info_complement : ";
	}
	$tableau_requete_code=explode(';',$chaine_requete_code);
	//hors_borne+";"+operateur+";"+valeur+";"+valeur_deb+";"+valeur_fin+";"+valeur_sup_n_x_borne_sup+";"+valeur_inf_n_x_borne_inf;
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
	if ($list_values!='') {
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
		$res.="if (document.getElementById('id_select_hors_borne_$num_filtre')) {document.getElementById('id_select_hors_borne_$num_filtre').value='$hors_borne';}";
		$res.="if (document.getElementById('id_select_operateur_$num_filtre')) {document.getElementById('id_select_operateur_$num_filtre').value='$operateur';}";
		$res.="if (document.getElementById('id_input_valeur_$num_filtre')) {document.getElementById('id_input_valeur_$num_filtre').value='$valeur';}";
		$res.="if (document.getElementById('id_input_valeur_deb_$num_filtre')) {document.getElementById('id_input_valeur_deb_$num_filtre').value='$valeur_deb';}";
		$res.="if (document.getElementById('id_input_valeur_fin_$num_filtre')) {document.getElementById('id_input_valeur_fin_$num_filtre').value='$valeur_fin';}";
		$res.="if (document.getElementById('id_input_valeur_sup_n_x_borne_sup_$num_filtre')) {document.getElementById('id_input_valeur_sup_n_x_borne_sup_$num_filtre').value='$valeur_sup_n_x_borne_sup';}";
		$res.="if (document.getElementById('id_input_valeur_inf_n_x_borne_inf_$num_filtre')) {document.getElementById('id_input_valeur_inf_n_x_borne_inf_$num_filtre').value='$valeur_inf_n_x_borne_inf';}";
		
		
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
                        $date_deb_document= trim($filtre_texte->document_date_start);
                        $date_fin_document= trim($filtre_texte->document_date_end);
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
                                if ($date_deb_document!='') {
                                        $requete_filtre_texte.=", ".get_translation('DATED','datés du')." $date_deb_document ";
                                }
                                if ($date_fin_document!='') {
                                        $requete_filtre_texte.=get_translation('DATE_TO','au')." $date_fin_document ";
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
                $requete_service='';
                if ($_SESSION['dwh_droit_all_departments'.$datamart_num]=='') {
                        if ($liste_service_session!='') {
                                $requete_service_intersect=" intersect select patient_num from dwh_patient_department where department_num in ($liste_service_session) ";
                        } else {
                                $requete_service_dead=" and 1=2";
                        }
                }
                $requete_document_origin_code='';
                if ($liste_document_origin_code_session!='') {
                	if (!preg_match("/'tout'/i","$liste_document_origin_code_session")) {
	                        $requete_document_origin_code=" and document_origin_code in ($liste_document_origin_code_session)  ";
                	}
                } else {
                        $requete_document_origin_code=" and 1=2";
                }

		if ($requete_service_intersect!='') {
			$sel = oci_parse($dbh," select count(*) NB  FROM (select patient_num from dwh_tmp_preresult WHERE $requete_sql  $requete_document_origin_code $requete_service_dead  $requete_service_intersect) t");   
			oci_execute($sel);
			$row = oci_fetch_array($sel, OCI_ASSOC);
			$nb=$row['NB'];
		} else {
			$sel = oci_parse($dbh," select count(distinct patient_num) NB  FROM dwh_tmp_preresult WHERE $requete_sql  $requete_document_origin_code  $requete_service_dead ");   
			oci_execute($sel);
			$row = oci_fetch_array($sel, OCI_ASSOC);
			$nb=$row['NB'];
		}
        } else {
                $nb='';
        }
        return $nb;
}
function calcul_nb_resultat_filtre_ancien ($requete_sql) {
        global $dbh,$liste_uf_session,$user_num_session,$datamart_num,$liste_document_origin_code_session,$liste_service_session;
        $tableau_resultat=array();
        if ($requete_sql!='') {
                $requete_sql=preg_replace("/^ ?and /"," ",$requete_sql);
                $requete_service='';
                if ($_SESSION['dwh_droit_all_departments'.$datamart_num]=='') {
                        if ($liste_service_session!='') {
                                $requete_service=" and dwh_tmp_preresult.patient_num in (select patient_num from dwh_patient_department where department_num in ($liste_service_session) )";
                        } else {
                                $requete_service=" and 1=2";
                        }
                }
                $requete_document_origin_code='';
                if ($liste_document_origin_code_session!='') {
                	if (!preg_match("/'tout'/i","$liste_document_origin_code_session")) {
	                        $requete_document_origin_code=" and dwh_tmp_preresult.document_num in (select document_num from dwh_document where  document_origin_code in ($liste_document_origin_code_session) ) ";
                	}
                } else {
                        $requete_document_origin_code=" and 1=2";
                }

                $tableau_patient_num=array();
		$sel = oci_parse($dbh,"select patient_num from dwh_tmp_preresult where $requete_sql $requete_service $requete_document_origin_code");   
		oci_execute($sel);
		while ( $row = oci_fetch_array($sel, OCI_ASSOC)) {
	                $patient_num=$row['PATIENT_NUM'];
	                $tableau_patient_num[$patient_num]=$patient_num;
		}
                $nb=count($tableau_patient_num);
        } else {
                $nb='';
        }
        return $nb;
}

function creer_requete_sql_filtre_patient ($xml,$tmpresult_num) {
        global $dbh,$liste_uf_session;
        $requete_sql="select dwh_tmp_result.patient_num from dwh_patient,dwh_tmp_result where dwh_patient.patient_num=dwh_tmp_result.patient_num and tmpresult_num=$tmpresult_num ";
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
                                $requete_sql.="and exists (select patient_num from dwh_patient where death_code is null and dwh_tmp_result.patient_num=dwh_patient.patient_num)";
		        }
	       		if ($vivant_dcd=='decede') {
                                $test_filtre_patient='ok';
                                $requete_sql.="and exists (select patient_num from dwh_patient where death_code is not null and dwh_tmp_result.patient_num=dwh_patient.patient_num)";
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
			$requete_sql.="and exists (select patient_num from dwh_patient where death_code is not null and (death_date-birth_date)>=$age_dcd_deb*365 and dwh_tmp_result.patient_num=dwh_patient.patient_num)";
	        }
       		if ($age_dcd_deb=='' && $age_dcd_fin!='') {
			$requete_sql.="and exists (select patient_num from dwh_patient where death_code is not null and (death_date-birth_date)<=$age_dcd_fin*365 and dwh_tmp_result.patient_num=dwh_patient.patient_num)";
	        }
       		if ($age_dcd_deb!='' && $age_dcd_fin!='') {
			$requete_sql.="and exists (select patient_num from dwh_patient where death_code is not null and (death_date-birth_date)>=$age_dcd_deb*365  and (death_date-birth_date)<=$age_dcd_fin*365 and dwh_tmp_result.patient_num=dwh_patient.patient_num)";
	        }
                
                
                if ($info_xml->first_stay_date_start) {
                        $date_deb_1ervenue=$info_xml->first_stay_date_start;
                        $date_fin_1ervenue=$info_xml->first_stay_date_end;
                        if ($date_deb_1ervenue!='' &&  $date_fin_1ervenue=='') {
                                $test_filtre_patient='ok';
                                $requete_sql.="and exists (select patient_num from dwh_document where dwh_tmp_result.patient_num=dwh_document.patient_num having  min(document_date)>=to_date('$date_deb_1ervenue','DD/MM/YYYY') group by patient_num)";
                        }
                        if ($date_deb_1ervenue=='' &&  $date_fin_1ervenue!='') {
                                $test_filtre_patient='ok';
                                $requete_sql.="and exists (select patient_num from dwh_document where dwh_tmp_result.patient_num=dwh_document.patient_num having  min(document_date)<=to_date('$date_fin_1ervenue','DD/MM/YYYY') group by patient_num)";
                        }
                        if ($date_deb_1ervenue!='' &&  $date_fin_1ervenue!='') {
                                $test_filtre_patient='ok';
                                $requete_sql.="and exists (select patient_num from dwh_document where dwh_tmp_result.patient_num=dwh_document.patient_num having  min(document_date) between to_date('$date_deb_1ervenue','DD/MM/YYYY')  and  to_date('$date_fin_1ervenue','DD/MM/YYYY')  group by patient_num)";
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
	                        $requete_sql.="and dwh_tmp_result.patient_num not in (select patient_num  from dwh_cohort_result where cohort_num in ($liste_cohorte_exclue))";
	                }
	        }
                
        }
        if ($test_filtre_patient=='ok') {
                return $requete_sql;
        }
}

function generer_resultat($xml,$tmpresult_num) {
        global $dbh,$liste_uf_session,$liste_document_origin_code_session,$liste_service_session,$user_num_session;
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
                $date_deb_document= trim($filtre_texte->document_date_start);
                $date_fin_document= trim($filtre_texte->document_date_end);
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
				        <document_date_start>$date_deb_document</document_date_start>
				        <document_date_end>$date_fin_document</document_date_end>
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
        	
                $tableau_requete_filtre_query_key[$num_filtre]=recup_query_key_depuis_xml($xml_unitaire);
                $tableau_requete_notexist[$num_filtre]= creer_requete_sql_filtre ($xml_unitaire,'patient_num');
                
                $tableau_requete_nbresult[$num_filtre]=$texte_nbresult;
                $tableau_exclure[$num_filtre]=$exclure;
                if ($datamart_num=='') {
                	$datamart_num=0;
                }
        	$tableau_requete_filtre_num_datamart[$num_filtre]=$datamart_num;
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
        }
        
        
        asort($tableau_requete_nbresult);
        $i_nb_intersect=0;
        $liste_query_key='';
        foreach ($tableau_requete_nbresult as $num_filtre => $nb) {
        	if ($tableau_exclure[$num_filtre]=='' || $tableau_exclure[$num_filtre]==0) {
        		$query_key=$tableau_requete_filtre_query_key["$num_filtre"];
        		$datamart_num=$tableau_requete_filtre_num_datamart["$num_filtre"];
        		$liste_query_key.="'$query_key',";
	                if ($i_nb_intersect==0) {
	                	$requete_intersect=" select patient_num from dwh_tmp_preresult WHERE query_key = '$query_key' AND user_num = $user_num_session and datamart_num=$datamart_num";
	                } else {
	                	$requete_intersect.=" intersect select patient_num from dwh_tmp_preresult WHERE query_key = '$query_key' AND user_num = $user_num_session  and datamart_num=$datamart_num";
	                }       
	                $i_nb_intersect++;
	        }
        }
        $liste_query_key=substr($liste_query_key,0,-1);
        if ($i_nb_intersect==1) {
	        $req_final="select  '$user_num_session' as user_num,
	          dwh_document.patient_num,
	          encounter_num,
	          dwh_document.document_num,
	          document_date,
	          $tmpresult_num as tmpresult_num,
	          document_origin_code,
	          author,
	          title,
	          department_num from  dwh_document  WHERE
	                          exists  (   select document_num  from dwh_tmp_preresult where dwh_tmp_preresult.document_num=dwh_document.document_num and query_key in ($liste_query_key)
	                        AND user_num = $user_num_session  and datamart_num=$datamart_num)
	        ";
        } else {
	        $req_final="select  '$user_num_session' as user_num,
	          dwh_document.patient_num,
	          encounter_num,
	          dwh_document.document_num,
	          document_date,
	          $tmpresult_num as tmpresult_num,
	          document_origin_code,
	          author,
	          title,
	          department_num from  dwh_document  WHERE
	                          exists  (   select document_num  from dwh_tmp_preresult where dwh_tmp_preresult.document_num=dwh_document.document_num and query_key in ($liste_query_key)
	                        AND user_num = $user_num_session  and datamart_num=$datamart_num)
	        intersect
	                        select  '$user_num_session' as user_num,
	          dwh_document.patient_num,
	          encounter_num,
	          dwh_document.document_num,
	          document_date,
	          $tmpresult_num as tmpresult_num,
	          document_origin_code,
	          author,
	          title,
	          department_num from  dwh_document  WHERE
	                          patient_num in ($requete_intersect)";
	}
	
        $sel = oci_parse($dbh,"delete from dwh_tmp_result where tmpresult_num=$tmpresult_num ");   
        oci_execute($sel);
        
        inserer_resultat($req_final);
        
        foreach ($tableau_requete_nbresult as $num_filtre => $nb) {
        	if ($tableau_exclure[$num_filtre]==1) {
			$requete_sql_notexist=$tableau_requete_notexist["$num_filtre"];
			$sel = oci_parse($dbh,"delete from dwh_tmp_result where tmpresult_num=$tmpresult_num and patient_num in (select patient_num from dwh_tmp_preresult where $requete_sql_notexist)");   
			oci_execute($sel);
	        }
        }
        
        $requete_sql_filtre_patient= creer_requete_sql_filtre_patient ($xml,$tmpresult_num);
        if ($requete_sql_filtre_patient!='') {
                $sel = oci_parse($dbh,"delete from dwh_tmp_result where patient_num not in ($requete_sql_filtre_patient) and tmpresult_num=$tmpresult_num");   
                oci_execute($sel);
        }
        
        
        $requete_document_origin_code='';
        if ($liste_document_origin_code_session!='') {
        	if (!preg_match("/'tout'/i","$liste_document_origin_code_session")) {
                        $requete_document_origin_code=" and document_origin_code in ($liste_document_origin_code_session) ";
        	}
        } else {
                $requete_document_origin_code=" and 1=2";
        }
        
        $sel = oci_parse($dbh,"select count(distinct patient_num) nb from dwh_tmp_result where tmpresult_num=$tmpresult_num ");   
        oci_execute($sel);
        $row = oci_fetch_array($sel, OCI_ASSOC);
        $nb_patient=$row['NB'];
        $sel = oci_parse($dbh,"select count(*) nb from dwh_tmp_result where tmpresult_num=$tmpresult_num ");   
        oci_execute($sel);
        $row = oci_fetch_array($sel, OCI_ASSOC);
        $nb_document=$row['NB'];

	$filtre_sql='';
        if ($_SESSION['dwh_droit_all_departments'.$datamart_num]=='') {
                if ($liste_service_session!='') {
                        $filtre_sql.=" and  exists ( select patient_num from dwh_patient_department where department_num in ($liste_service_session) and  dwh_tmp_result.patient_num=dwh_patient_department.patient_num)";
                } else {
                        $filtre_sql.=" and 1=2";
                }
                $sel = oci_parse($dbh,"select count(distinct patient_num) nb from dwh_tmp_result where tmpresult_num=$tmpresult_num $filtre_sql  $requete_document_origin_code");   
                oci_execute($sel);
                $row = oci_fetch_array($sel, OCI_ASSOC);
                $nb_patient_user=$row['NB'];
                $sel = oci_parse($dbh,"select count(*) nb from dwh_tmp_result where tmpresult_num=$tmpresult_num $filtre_sql  $requete_document_origin_code");   
                oci_execute($sel);
                $row = oci_fetch_array($sel, OCI_ASSOC);
                $nb_document_user=$row['NB'];
        } else {
                $sel = oci_parse($dbh,"select count(distinct patient_num) nb from dwh_tmp_result where tmpresult_num=$tmpresult_num $requete_document_origin_code");   
                oci_execute($sel);
                $row = oci_fetch_array($sel, OCI_ASSOC);
                $nb_patient_user=$row['NB'];
                $sel = oci_parse($dbh,"select count(*) nb from dwh_tmp_result where tmpresult_num=$tmpresult_num $requete_document_origin_code");   
                oci_execute($sel);
                $row = oci_fetch_array($sel, OCI_ASSOC);
                $nb_document_user=$row['NB'];
        }
        
        return array($nb_patient,$nb_document,$nb_patient_user,$nb_document_user);
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

        $sel_var=oci_parse($dbh,"insert into dwh_query_result (patient_num,load_date,query_num) select distinct patient_num, sysdate, $query_num from dwh_tmp_result where tmpresult_num=$tmpresult_num and user_num=$user_num_session
        and patient_num not in (select patient_num from dwh_query_result where query_num=$query_num)");
	oci_execute($sel_var);

        $sel = oci_parse($dbh,"update dwh_query set last_load_date=sysdate where query_num=$query_num");   
        oci_execute($sel);
        
        $sel = oci_parse($dbh,"delete from dwh_tmp_result where tmpresult_num=$tmpresult_num");   
        oci_execute($sel);
}
	


function sauver_requete_temp($xml_query) {
        global $dbh,$user_num_session,$datamart_num;
        save_log_query($user_num_session,'engine',$xml_query);
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
                	
                	$sel = oci_parse($dbh,"select dwh_seq.nextval as query_num from dual");   
		        oci_execute($sel);
		        $row = oci_fetch_array($sel, OCI_ASSOC);
		        $query_num=$row['QUERY_NUM'];
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
        $sel = oci_parse($dbh,"insert /*+ APPEND */ into dwh_tmp_result (user_num,patient_num, encounter_num,document_num,document_date,tmpresult_num,document_origin_code,author,title,department_num) select user_num,patient_num,encounter_num,document_num,document_date,tmpresult_num,document_origin_code,author,title,department_num from ( $requete_sql ) t " );   
        oci_execute($sel);
}

function inserer_resultat_texte ($requete_sql,$tmpresult_num) {
        global $dbh,$login_session,$user_num_session;
        $requete_sql=preg_replace("/^ ?and /"," ",$requete_sql);
        $sel = oci_parse($dbh,"insert /*+ APPEND */ into dwh_tmp_result (user_num,patient_num, encounter_num,document_num,document_date,tmpresult_num,document_origin_code,author,title,department_num) select '$user_num_session',patient_num,encounter_num,document_num,document_date,$tmpresult_num,document_origin_code,author,title,department_num from dwh_document where $requete_sql " );   
        oci_execute($sel);
}

function intersect_resultat_texte ($requete_sql,$tmpresult_num, $perimetre_intersect,$requete_sql_notexist) {
        global $dbh,$login_session;
        if ($perimetre_intersect=='') {
                $perimetre_intersect='patient_num';
        }
        
        $sel = oci_parse($dbh,"delete from dwh_tmp_result where tmpresult_num=$tmpresult_num and not exists (select document_num from dwh_tmp_preresult where $requete_sql_notexist  and dwh_tmp_preresult.$perimetre_intersect=dwh_tmp_result.$perimetre_intersect ) " );   
        oci_execute($sel);
        
	$requete_sql_completee=$requete_sql." and dwh_tmp_preresult.$perimetre_intersect = dwh_tmp_result.$perimetre_intersect and tmpresult_num = $tmpresult_num )";
	inserer_resultat_texte ("$requete_sql_completee   AND dwh_document.document_num NOT IN (SELECT dwh_tmp_result.document_num FROM dwh_tmp_result WHERE tmpresult_num = $tmpresult_num ) ",$tmpresult_num);
        
 //       inserer_resultat_texte ("$requete_sql and dwh_document.$perimetre_intersect in (select dwh_tmp_result.$perimetre_intersect from dwh_tmp_result where tmpresult_num=$tmpresult_num)  and dwh_document.document_num not in (select dwh_tmp_result.document_num from dwh_tmp_result where tmpresult_num=$tmpresult_num) ",$tmpresult_num);
}

function recuperer_resultat ($tmpresult_num,$full_text_query,$i_deb,$filtre_sql) {
        global $dbh,$modulo_ligne_ajoute,$liste_uf_session,$datamart_num,$liste_document_origin_code_session,$liste_service_session,$login_session;
        
        //pour les datamart, les droits sont sur tous les services , cf verif_droit.php// 
        if ($_SESSION['dwh_droit_all_departments'.$datamart_num]=='') {
#         
                if ($liste_uf_session!='') {
                        $filtre_sql.=" and exists ( select patient_num from dwh_patient_department where department_num in ($liste_service_session) and  dwh_tmp_result.patient_num=dwh_patient_department.patient_num ) ";
                } else {
                        $filtre_sql.=" and 1=2";
                }
        }
	$filtre_sql_document_origin_code='';
        if ($liste_document_origin_code_session!='') {
        	if (!preg_match("/'tout'/i","$liste_document_origin_code_session")) {
                        $filtre_sql_document_origin_code.=" and document_origin_code in ($liste_document_origin_code_session) ";
        	}
        } else {
                $filtre_sql_document_origin_code.=" and 1=2"; 
        }
        
	//$liste_synonyme=recupere_liste_concept_full_texte ($full_text_query);
	$tableau_liste_synonyme=recupere_liste_concept_full_texte ($full_text_query);
        $tableau_resultat=array();
        $i_fin=$i_deb+$modulo_ligne_ajoute;
        $sel = oci_parse($dbh,"select patient_num from (select rownum i, patient_num from dwh_patient where patient_num in (select patient_num from dwh_tmp_result where tmpresult_num=$tmpresult_num $filtre_sql $filtre_sql_document_origin_code ) and rownum<=$i_fin  order by patient_num desc ) t where i>$i_deb and i<=$i_fin  order by i asc" );   
        oci_execute($sel);
        while ($row = oci_fetch_array($sel, OCI_ASSOC)) {
                $patient_num=$row['PATIENT_NUM'];
                $nb_doc_par_patient=0;
                $tableau_document_origin_code_deja_fait=array();
                $sel_doc = oci_parse($dbh,"select dwh_tmp_result.document_num,dwh_tmp_result.patient_num,dwh_tmp_result.encounter_num, title,author,document_date,document_origin_code,department_num from dwh_tmp_result where dwh_tmp_result.patient_num=$patient_num and tmpresult_num=$tmpresult_num  $filtre_sql  $filtre_sql_document_origin_code order by  dwh_tmp_result.document_date desc " );   
                oci_execute($sel_doc);
                while ($row_doc = oci_fetch_array($sel_doc, OCI_ASSOC)) {
                        $document_num=$row_doc['DOCUMENT_NUM'];
                        $encounter_num=$row_doc['ENCOUNTER_NUM'];
                        $title=$row_doc['TITLE'];
                        $document_date=$row_doc['DOCUMENT_DATE'];
                        $document_origin_code=$row_doc['DOCUMENT_ORIGIN_CODE'];
                        $author=$row_doc['AUTHOR'];
                        $department_num=$row_doc['DEPARTMENT_NUM'];
                        
                        if ($tableau_document_origin_code_deja_fait[$document_origin_code]<1 && $nb_doc_par_patient<6) {
	               		$tableau_document_origin_code_deja_fait[$document_origin_code]++;
	               		$nb_doc_par_patient++;
				$sel_texte = oci_parse($dbh,"select text from dwh_text where document_num=$document_num and certainty=0 and context='text' " );   
	                        oci_execute($sel_texte);
				$row_texte = oci_fetch_array($sel_texte, OCI_ASSOC);
	                        if ($row_texte['TEXT']!='') {
	                                $text=$row_texte['TEXT']->load();             
	                        }
				$department_str=get_department_str ($department_num);
	
	                        $tableau_resultat[$patient_num][$document_num]['title']=$title;
	                        $tableau_resultat[$patient_num][$document_num]['document_date']=$document_date;
	                        $tableau_resultat[$patient_num][$document_num]['document_origin_code']=$document_origin_code;
	                        $tableau_resultat[$patient_num][$document_num]['department_str']=$department_str;
	                        $tableau_resultat[$patient_num][$document_num]['author']=$author;
	                        $tableau_resultat[$patient_num][$document_num]['appercu']=resumer_resultat($text,"$full_text_query",$tableau_liste_synonyme,'moteur');
	                        $tableau_resultat[$patient_num][$document_num]['affiche_direct']="ok";
	                } else {
	                        $tableau_resultat[$patient_num][$document_num]['title']=$title;
	                        $tableau_resultat[$patient_num][$document_num]['document_date']=$document_date;
	                        $tableau_resultat[$patient_num][$document_num]['document_origin_code']=$document_origin_code;
	                        $tableau_resultat[$patient_num][$document_num]['author']=$author;
	                        $tableau_resultat[$patient_num][$document_num]['appercu']="";
	                        $tableau_resultat[$patient_num][$document_num]['affiche_direct']="";
	                }
                }
        }
        return $tableau_resultat;
}

function ouvrir_plus_document ($liste_document,$full_text_query,$tableau_liste_synonyme) {
        global $dbh,$modulo_ligne_ajoute,$liste_uf_session,$datamart_num,$liste_document_origin_code_session;
        $res="";
        if ($liste_document!='') {
	        $sel_doc = oci_parse($dbh,"select distinct dwh_tmp_result.document_num,dwh_tmp_result.patient_num,dwh_tmp_result.encounter_num, title,author,document_date,document_origin_code,department_num from dwh_tmp_result where dwh_tmp_result.document_num in ($liste_document) order by  dwh_tmp_result.document_date desc " );   
	        oci_execute($sel_doc);
	        while ($row_doc = oci_fetch_array($sel_doc, OCI_ASSOC)) {
	                $document_num=$row_doc['DOCUMENT_NUM'];
	                $encounter_num=$row_doc['ENCOUNTER_NUM'];
	                $title=$row_doc['TITLE'];
	                $document_date=$row_doc['DOCUMENT_DATE'];
	                $document_origin_code=$row_doc['DOCUMENT_ORIGIN_CODE'];
	                $author=$row_doc['AUTHOR'];
	                $department_num=$row_doc['DEPARTMENT_NUM'];
			$department_str=get_department_str ($department_num);
	                
	                $sel_texte = oci_parse($dbh,"select text from dwh_text where document_num=$document_num and certainty=0 and context='text' " );   
	                oci_execute($sel_texte);
	                $row_texte = oci_fetch_array($sel_texte, OCI_ASSOC);
	                if ($row_texte['TEXT']!='') {
	                        $text=$row_texte['TEXT']->load();             
	                }
	                
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
	}
        return $res;
}


function appercu_liste_document ($liste_document,$full_text_query) {
        global $dbh,$modulo_ligne_ajoute,$liste_uf_session,$datamart_num,$liste_document_origin_code_session,$login_session;
        $res="";
        $i=0;
	$tableau_liste_synonyme=recupere_liste_concept_full_texte ($full_text_query);
	$id_full_text_query=preg_replace("/[^a-z0-9]/i","",$full_text_query);
	
        if ($liste_document!='') {
	        $sel_doc = oci_parse($dbh,"select  document_num,patient_num,encounter_num, title,author,document_date,document_origin_code,text,department_num from dwh_text where document_num in ($liste_document) and certainty=0 and context='text' order by  document_date desc " );   
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


function get_patient ($patient_num) {
	global $dbh,$user_num_session;
	$tab_patient=array();
	if ($patient_num!='') {
     		$autorisation_voir_patient=autorisation_voir_patient ($patient_num,$user_num_session) ;
     		if ($autorisation_voir_patient=='ok') {
			$sel_patient= oci_parse($dbh,"select lastname,
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
		                birth_zip_code
		                 from dwh_patient where patient_num=$patient_num  " );   
	                oci_execute($sel_patient);
	                $tab_patient = oci_fetch_array($sel_patient, OCI_ASSOC);   
	                
       			$tab_patient['HOSPITAL_PATIENT_ID']=get_master_patient_id ($patient_num);
	                $autorisation_voir_patient_nominative=autorisation_voir_patient_nominative ($patient_num,$user_num_session);
	                if ($autorisation_voir_patient_nominative!='ok') {
                                $tab_patient['LASTNAME']=get_translation('PATIENT','Patient');
                                $tab_patient['FIRSTNAME']='';
                                $tab_patient['BIRTH_DATE']='';
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
                        $sel_patient= oci_parse($dbh,"select  trunc((document_date-to_date('$birth_date','DD/MM/YYYY'))/365) as age_an, trunc((document_date-to_date('$birth_date','DD/MM/YYYY'))*12/365) as age_mois from dwh_document where document_num=$document_num  " );   
                        oci_execute($sel_patient);
                        $row_patient = oci_fetch_array($sel_patient, OCI_ASSOC);
                        $age_mois_doc=$row_patient['AGE_MOIS'];         
                        $age_an_doc=$row_patient['AGE_AN'];             
                        
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
	        	$res.= " </td><td>(le $add_date par $user_name_ajout)</td> ";
                        $res.="<td><a href=\"patient.php?patient_num=$patient_num&cohort_num_encours=$cohort_num&datamart_num=$datamart_num\" target=\"_blank\"><img src=\"images/dossier_patient.png\" alt=\"Dossier du patient\" title=\"Dossier du patient\" border=\"0\" height=\"15px\"></a>&nbsp;&nbsp;";
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
                       		$res.="<img id=\"id_img_pencil_cohorte_$patient_num\" src=\"images/$icone_pencil.png\" border=\"0\" width=\"15px\" onclick=\"commenter_patient_cohorte('$patient_num','cohorte');\" style=\"cursor:pointer\" alt=\"Commenter\" title=\"Commenter\">";
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
                        $res.="</td><td><a href=\"patient.php?patient_num=$patient_num&cohort_num_encours=$cohort_num&datamart_num=$datamart_num\" target=\"_blank\"><img src=\"images/dossier_patient.png\" border=\"0\" height=\"15px\"></a>&nbsp;&nbsp;";
                }
                
                if ($option=='demande_acces_copier_coller') {
        		if ($log_context=='') {
        			$log_context='demande_acces';
        		}
                        $nominative=1;
        		$acces=1;
                        $res= "$hospital_patient_id	$initiales	$lastname	$firstname	$birth_date\n";
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
                                $res= "$hospital_patient_id	$initiales	$lastname	$firstname	$birth_date\n";
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
                     		$res= "<tr><td class=\"text\">$hospital_patient_id</td><td class=\"text\">$initiales</td><td class=\"text\">$lastname</td><td class=\"text\">$firstname</td><td class=\"text\">$birth_date</td><td class=\"text\">$death_date</td><td class=\"text\">$zip_code</td><td class=\"text\">$phone_number</td><td class=\"text\">$add_date</td><td class=\"text\">$user_name_ajout</td></tr>";
                        }
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
               <div style=\"position:relative\">";
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
	                        $res.= "<div id=\"id_button_$document_num\" class=\"div_voir_document\"><a class=\"voir_document\" href=\"#\" onclick=\"afficher_document('$document_num','$datamart_num');return false;\">";
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
                        $res.= "<span id=\"id_span_ouvrir_plus_document_$patient_num\" class=\"filtre_texte_ouvrir_avance\"><a onclick=\"ouvrir_plus_document('$patient_num');\"><span id=\"plus_id_div_ouvrir_plus_document_$patient_num\">+</span> ".get_translation('DISPLAY_OTHER_DOCUMENTS','afficher les autres documents')."</a></span>";
                        $res.= "<div style=\"display:none;\" id=\"id_div_ouvrir_plus_document_$patient_num\"></div>";
                }
                $res.= "</td></tr>";
                $i++;
        }
        return $res;
}


function resumer_resultat($text,$full_text_query,$tableau_liste_synonyme,$type_affichage) {
	global $login_session;
        // si requete est une expression reguliere
        $tableau_texte=array();
        if ($type_affichage=='patient' && preg_match("/\[/",$full_text_query)) {
		$text=surligner_resultat_exp_reguliere ($text,$full_text_query,'non');
		$text=preg_replace("/\n/"," ",$text);
		$i=0;
		while (preg_match("/highlight/i",$text) && $i<10) {
			$tableau_texte[$i]=preg_replace("/.*([^a-z][a-z]*.{30}<b.*?highlight.*?<\/b>.{30}[a-z]*[^a-z]).*/i","$1","text                              $text                              text");
			$text=preg_replace("/<b.*?highlight.*?<\/b>/i","",$text);
			$i++;
		}
	} else {
        	$text=surligner_resultat ($text,$full_text_query,'','non',$tableau_liste_synonyme,'');
		$tableau_texte=preg_split("/[.!?\n]/",$text);
	}

        $texte_appercu='';
        foreach ($tableau_texte as $ligne) {
                $ligne=trim($ligne);
                if ($ligne!='') {
                        if (preg_match("/highlight/i"," $ligne ")) {
                                $texte_appercu.=$ligne." [...] ";
                        }
                }
        }
        if ($texte_appercu=='' && !preg_match("/\[/",$full_text_query)) {
		$text=surligner_resultat ($text,$full_text_query,'unitaire','non',$tableau_liste_synonyme,'');
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
        $sel_texte = oci_parse($dbh,"select displayed_text,title,patient_num,to_char(document_date,'DD/MM/YYYY') as char_date_document from dwh_document where document_num=$document_num" );   
        oci_execute($sel_texte);
        $row_texte = oci_fetch_array($sel_texte, OCI_ASSOC);
        if ($row_texte['DISPLAYED_TEXT']!='') {
                $displayed_text=$row_texte['DISPLAYED_TEXT']->load();         
        }
        $title=$row_texte['TITLE'];     
        $patient_num=$row_texte['PATIENT_NUM']; 
        $char_date_document=$row_texte['CHAR_DATE_DOCUMENT']; 
        if ($_SESSION['dwh_droit_see_debug']=='ok') {
	       $displayed_text= afficher_dans_document_tal($document_num);
	}
	$nominative='oui';
        if ($_SESSION['dwh_droit_nominative'.$datamart_num]=='' || $_SESSION['dwh_droit_anonymized'.$datamart_num]=='ok') {
                $displayed_text=anonymisation_document ($document_num,$displayed_text);
                $nominative='non';
                $char_date_document='[DATE]';
        }
        
        $displayed_text=nettoyer_pour_afficher ($displayed_text);
        $displayed_text=surligner_resultat ($displayed_text,$full_text_query,'','oui',$tableau_liste_synonyme,'');
        if (!preg_match("/highlight/",$displayed_text) ) {
                $displayed_text=surligner_resultat ($displayed_text,$full_text_query,'unitaire','oui',$tableau_liste_synonyme,'');
        }
        
        
        $res= "
        <div class=\"ui-widget ui-widget-content ui-corner-all ui-front ui-draggable ui-resizable class_document\" style=\"position: absolute; height: 350px; width: 650px; display: none;\" tabindex=\"-1\" id=\"id_enveloppe_document_$document_num\">
                <div class=\"ui-draggable-handle titre_document_bandeau\" id=\"id_bandeau_$document_num\">
                        <table border=\"0\" width=\"100%\">
                                <tr>
                                        <td >
                                                <span class=\"entete_document_patient\">".afficher_patient($patient_num,'document',$document_num,'')."</span><br>
                                                <span class=\"titre_document\">$title - $char_date_document</span>
                                        </td>
                                        <td style=\"text-align:right;\">
                                                <img src=\"images/printer.png\" onclick=\"ouvrir_document_print('$document_num');return false;\" style=\"cursor:pointer\" border=\"0\">
                                                <img src=\"images/close.gif\" onclick=\"fermer_document('$document_num');\" style=\"cursor:pointer\" border=\"0\">
                                        </td>
                                </tr>
                        </table>
                </div>
                <div id=\"id_document_$document_num\" class=\"afficher_document\">
                        <pre>$displayed_text</pre>
                </div>
        </div>
        ";
	save_log_document($document_num,$user_num_session,$nominative);
        return $res;
}


function afficher_document_patient_popup($document_num,$full_text_query,$tableau_liste_synonyme,$id_cle) {
        global $dbh,$datamart_num,$user_num_session;
        $sel_texte = oci_parse($dbh,"select displayed_text,title,patient_num from dwh_document where document_num=$document_num" );   
        oci_execute($sel_texte);
        $row_texte = oci_fetch_array($sel_texte, OCI_ASSOC);
        if ($row_texte['DISPLAYED_TEXT']!='') {
                $displayed_text=$row_texte['DISPLAYED_TEXT']->load();         
        }
        $title=$row_texte['TITLE'];     
        $patient_num=$row_texte['PATIENT_NUM']; 
        if ($_SESSION['dwh_droit_see_debug']=='ok') {
	       $displayed_text= afficher_dans_document_tal($document_num);
	}
	$nominative='oui';
        if ($_SESSION['dwh_droit_nominative'.$datamart_num]=='' || $_SESSION['dwh_droit_anonymized'.$datamart_num]=='ok') {
                $displayed_text=anonymisation_document ($document_num,$displayed_text);
                $nominative='non';
        }
        
        $displayed_text=nettoyer_pour_afficher ($displayed_text);
        //$displayed_text=surligner_resultat ($displayed_text,$full_text_query,'','oui');
        $displayed_text=surligner_resultat ($displayed_text,$full_text_query,'','oui',$tableau_liste_synonyme,'');
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
                <div id=\"id_document_$id_cle\" class=\"afficher_document\">
                        <pre>$displayed_text</pre>
                </div>
        </div>
        ";
         save_log_document($document_num,$user_num_session,$nominative);

        return $res;
}

function recupere_liste_concept_full_texte ($full_text_query) {
	global $dbh,$user_num_session,$login_session;
	$tableau_code_libelle_deja=array();
	$tableau_etendre_requete_unitaire_deja_fait=array();
	$tableau_etendre_requete_unitaire=array();
	$tableau_requete_unitaire=array_values(array_unique(preg_split("/ or | and | not |;requete_unitaire;/i"," $full_text_query ")));
	$limite_syno=200;
        foreach ($tableau_requete_unitaire as $requete_unitaire) {
                $requete_unitaire=strtolower(trim($requete_unitaire));
		 if ($requete_unitaire!='' && strlen($requete_unitaire)>1 && $tableau_etendre_requete_unitaire_deja_fait[trim($requete_unitaire)]=='') {
		 	$tableau_code_libelle_deja[$requete_unitaire]='ok';
		 	$requete_unitaire=preg_replace("/'/"," ",$requete_unitaire);
		 	$requete_unitaire=preg_replace("/[(),]/"," ",$requete_unitaire);
		 	$requete_unitaire=preg_replace("/[^a-z]near[^a-z]/"," "," $requete_unitaire ");
			$i=0;
			$liste_code_libelle='';
		        $sel = oci_parse($dbh,"select  concept_str,length(concept_str) from dwh_thesaurus_enrsem where length(concept_str)>2 and length(concept_str)<50 and concept_code in (select  concept_code from dwh_thesaurus_enrsem where contains(concept_str,'$requete_unitaire')>0 ) order by count_doc_concept_str desc, length(concept_str) desc " );   
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
		        select  fils.concept_str,length(fils.concept_str) from dwh_thesaurus_enrsem fils,dwh_thesaurus_enrsem_graph graph where
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
	if ($colorer=='oui') {
		$couleur=$tableau_couleur[0];
		$text=preg_replace("/($pattern)/i","<b style='background:$couleur;color:black;' class='highlight'><u>$1</u></b>",$text);
	} else {
		$text=preg_replace("/($pattern)/i","<b style='color:black;' class='highlight'><u>$1</u></b>",$text);
	}
	return $text;

}


function surligner_resultat ($text,$full_text_query,$option,$colorer,$tableau_liste_synonyme,$test_unique) {
        global $dbh,$tableau_couleur,$login_session;
        global $stop_words;
        $num_couleur=-1;
        
        $tableau_liste_texte_query=array();
        
        $expression_deja_trouve='';
        $expression_deja_trouve_bloc='';
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
				
				
		                if (preg_match("/[^a-z0-9]".$sous_requete_unitaire_normalise."[^a-z0-9]/i"," $expression_deja_trouve ")) {
		                        $deja_fait=1;
		                }
		                if ($test_unique=='' && $deja_fait==0 || $test_unique==1 && $expression_deja_trouve=='') {
		                        $nb_parenthese=3+count(explode("(",$sous_requete_unitaire_normalise))-1;
		                        if (preg_match("/%/",$sous_requete_unitaire_normalise)) {
		                                $sous_requete_unitaire_normalise=preg_replace("/%/","",$sous_requete_unitaire_normalise);
		                                $couleur=$requete_tableau_couleur[$sous_requete_unitaire];
		                                $pattern="#([^a-z0-9])(".$sous_requete_unitaire_normalise."[a-z0-9]*)([^a-z0-9])#i";
		                                $text=preg_replace($pattern,"$1<b style='background:$couleur;color:black;' class='highlight'><u>$2</u></b>$".$nb_parenthese,$text,-1,$count);
		                                $text_final=preg_replace($pattern,"$1<b style='background:$couleur;color:black;' class='highlight'><u>$2</u></b>$".$nb_parenthese,$text_final);
		                        } else {
		                        	
		                                $couleur=$requete_tableau_couleur[$sous_requete_unitaire];
		                                $pattern="#([^a-z0-9])(".$sous_requete_unitaire_normalise.")([^a-z0-9])#i";
		                                $text=preg_replace($pattern,"$1<b style='background:$couleur;color:black;' class='highlight'><u>$2</u></b>$".$nb_parenthese,$text,-1,$count);
		                                $text_final=preg_replace($pattern,"$1<b style='background:$couleur;color:black;' class='highlight'><u>$2</u></b>$".$nb_parenthese,$text_final);
		                        }
		                        if ($count>0) {
		                                $expression_deja_trouve.= " $sous_requete_unitaire ";
		                                $expression_deja_trouve_bloc='ok';
		                                
		                        } else {
	                			if ($expression_deja_trouve_bloc=='' && $test_presence_and=='ok' || $expression_deja_trouve_bloc!='' && $test_presence_and=='ok' || $expression_deja_trouve_bloc=='' && $test_presence_and=='') {
							if ($tableau_liste_synonyme[$sous_requete_unitaire]['synonyme']!='') {
								$text_final=surligner_resultat ($text_final,$tableau_liste_synonyme[$sous_requete_unitaire]['synonyme'],"synonyme",$colorer,array(),1);
							}
							if (!preg_match("/highlight/",$text_final) && $tableau_liste_synonyme[$sous_requete_unitaire]['fils']!='') {
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
	// si ce n'est pas une epxression reguliere on supprimer les trucs qui ne marche pas avec oracle text, sinon on les gardes
        $text=preg_replace("/;plus;/","+",$text);
	if (!preg_match("/\[/",$text)) {
       		 $text=preg_replace("/\*/","%",$text);
	        $text=preg_replace("/\-/"," ",$text);
	        $text=preg_replace("/[.:;]/"," ",$text);
      		$text=preg_replace("/'/"," ",$text);
	        $text=preg_replace("/ et /i"," and ",$text);
	        $text=preg_replace("/ ou /i"," or ",$text);
        } else {
      		$text=preg_replace("/'/","''",$text);
      		$text=str_replace("/","\\/",$text);
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
        $full_text_query=preg_replace("/[^a-z0-9éeêèîïàââçùûüôö%;]/i","[^a-z0-9]+",$full_text_query);
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
        
        $full_text_query=preg_replace("/e([^a-z])/i","es?$1","$full_text_query");
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
                $sel_var1=oci_parse($dbh,"select count(*) as verif_passwd from dwh_user where login='$login' and passwd='".md5($passwd)."' ");
                oci_execute($sel_var1);
				$r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC);
                $verif_passwd=$r['VERIF_PASSWD'];
                if ($verif_passwd==0) {
                	$result='nothing';
                } else {
                	$result='ok';
                }
            }
            
            if ($result!='nothing') {
                $sel_var1=oci_parse($dbh,"select user_profile,user_num from dwh_user_profile where user_num in (select user_num from dwh_user where login='$login')");
                oci_execute($sel_var1);
                while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
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

function affiche_service($department_num,$department_str,$department_code) {
        global $dbh,$login_session,$user_num_session;
        
        $verif_manager_department=check_user_as_department_manager ($department_num,$user_num_session);
        
        //////////////// LE SERVICE
        print "<tr id=\"id_tr_service_$department_num\" >
                        <td valign=\"top\" nowrap=\"nowrap\">";
        if ($_SESSION['dwh_droit_admin']=='ok') {
                print "<div id=\"id_div_libelle_service_$department_num\" onclick=\"afficher_modifier_service($department_num);\" class=\"admin_libelle_service\"><strong nowrap=\"nowrap\">$department_code $department_str</strong></div>
                        <div id=\"id_div_libelle_service_modifier_$department_num\" style=\"display:none;\">
                                ".get_translation('HOSPITAL_DEPARTMENT_NAME','Nom du service')." <input type=\"text\" size=\"50\" id=\"id_input_libelle_service_$department_num\" value=\"$department_str\" class=\"admin_input\"><br>
                                ".get_translation('HOSPITAL_DEPARTMENT_CODE','Code service')." <input type=\"text\" size=\"3\" id=\"id_input_code_service_$department_num\" value=\"$department_code\"><br>
                                <input type=\"button\" onclick=\"modifier_libelle_service('$department_num');\" value=\"".get_translation('MODIFY','modifier')."\" class=\"admin_button\">
                        </div>
                ";
        } else {
                print "<strong>$department_str</strong>";
        }
        print "</td>";
        print "<td valign=\"top\" width=\"482\">";
        
        /////////////////// LES UF 
        print "<a href=\"#\" onclick=\"plier_deplier('id_div_uf_$department_num');return false;\" class=\"admin_lien\"><span id=\"plus_id_div_uf_$department_num\">+</span> ".get_translation('DISPLAY_UNITS','Afficher les unités')."</a>
        <div id=\"id_div_uf_$department_num\" style=\"display:none\">";
        
        print "<table border=\"0\" id=\"id_tableau_uf_$department_num\" width=\"100%\" class=\"noborder\">";
        $req_uf="select dwh_thesaurus_unit.unit_num, unit_str ,unit_code,to_char(date_start_unit,'DD/MM/YYYY') as date_start_unit,to_char(unit_end_date,'DD/MM/YYYY') as unit_end_date
        from dwh_thesaurus_unit,dwh_thesaurus_department 
        where dwh_thesaurus_unit.department_num=dwh_thesaurus_department.department_num and dwh_thesaurus_department.department_num=$department_num order by unit_str ";
        $sel_uf = oci_parse($dbh,$req_uf);
        oci_execute($sel_uf);
        while ($ligne_uf = oci_fetch_array($sel_uf)) {
                $unit_num = $ligne_uf['UNIT_NUM'];
                $unit_str = $ligne_uf['UNIT_STR'];
                $unit_code = $ligne_uf['UNIT_CODE'];
                $date_start_unit = $ligne_uf['DATE_START_UNIT'];
                $unit_end_date = $ligne_uf['UNIT_END_DATE'];
                print "<tr id=\"id_tr_uf_".$department_num."_".$unit_num."\" onmouseover=\"this.style.backgroundColor='#B9C2C8';\" onmouseout=\"this.style.backgroundColor='#ffffff';\" class=\"admin_texte\">
                        <td>$unit_code ".ucfirst(strtolower($unit_str))." </td><td> $date_start_unit</td><td>$unit_end_date</td>";
                
                if ($_SESSION['dwh_droit_admin']=='ok' || $verif_manager_department==1) {
                        print "<td><a onclick=\"supprimer_uf('$unit_num','$department_num');return false;\" href=\"#\" class=\"admin_lien\">X</a></td>";
                } else {
                        print "<td></td>";
                }
                print "</tr>";
        }
        print "</table></div><br>";
        
        
        if ($_SESSION['dwh_droit_admin']=='ok' || $verif_manager_department==1) {
                print "
                        <a onclick=\"plier_deplier('id_div_ajouter_uf_$department_num');return false;\"  href=\"#\" class=\"admin_lien\"><span id=\"plus_id_div_ajouter_uf_$department_num\">+</span> ".get_translation('ADD_HOSPITAL_UNIT','Ajouter une UF')."</a><br><br>
                        <div id=\"id_div_ajouter_uf_$department_num\" style=\"display:none;\" onmouseover=\"autocomplete_service($department_num);\">
                                <span class=\"admin_texte\">".get_translation('HOSPITAL_DEPARTMENT_CODE','Code service')." </span><input type=\"text\" size=\"3\" id=\"id_input_unit_code_$department_num\" value=\"\" class=\"admin_input\"><br>
                                <span class=\"admin_texte\">".get_translation('HOSPITAL_DEPARTMENT_NAME','Nom du service')." </span> <input type=\"text\" size=\"50\" id=\"id_input_unit_str_$department_num\" value=\"\" class=\"admin_input\" class=\"admin_input\"><br>
                                <span class=\"admin_texte\">".get_translation('DATE_START','Date debut')." </span><input type=\"text\" size=\"11\" id=\"id_input_date_deb_uf_$department_num\" value=\"\" class=\"admin_input\"><br>
                                <span class=\"admin_texte\">".get_translation('DATE_END','Date fin')." </span><input type=\"text\" size=\"11\" id=\"id_input_date_fin_uf_$department_num\" value=\"01/01/3000\" class=\"admin_input\"><br>
                                <input type=\"button\" onclick=\"ajouter_uf('$department_num');\" value=\"".get_translation('ADD','ajouter')."\">
                                <br>
                                <br>
                                <br>
                        </div>";
        }
        
        /////////////////// LES PERSONNES
        print "<a href=\"#\" onclick=\"plier_deplier('id_div_service_$department_num');return false;\" class=\"admin_lien\"><span id=\"plus_id_div_service_$department_num\">+</span> ".get_translation('DISPLAY_USERS','Afficher les personnes')."</a>
        <div id=\"id_div_service_$department_num\" style=\"display:none\">
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
                        $texte_manager_department="*";
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
                print "
                        <a onclick=\"plier_deplier('id_div_ajouter_user_$department_num');return false;\"  href=\"#\" class=\"admin_lien\"><span id=\"plus_id_div_ajouter_user_$department_num\">+</span> ".get_translation('ADD_USERS','Ajouter des utilisateurs')."</a><br><br>
                        <div id=\"id_div_ajouter_user_$department_num\" style=\"display:none;\" onmouseover=\"autocomplete_service($department_num);\">
                                <strong class=\"admin_strong\">".get_translation('SEARCH_USER_IN_LDAP','Rechercher un code APH (nom ou prenom.nom)')." :</strong> <div class=\"ui-widget\" style=\"padding-left: 10px;width:260px;font-size:10px;\">
                                        <span class=\"ui-helper-hidden-accessible\" aria-live=\"polite\" role=\"status\"></span>
                                        <span class=\"ui-helper-hidden-accessible\" role=\"status\" aria-live=\"polite\"></span>
                                        <input id=\"id_champs_recherche_rapide_utilisateur_$department_num\" class=\"form ui-autocomplete-input\" type=\"text\" autocomplete=\"off\" style=\"font-size:12px;\" value=\"".get_translation('LOGIN','Identifiant')."\" name=\"LOGIN\" size=\"15\" onclick=\"if (this.value=='".get_translation('LOGIN','Identifiant')."') {this.value='';}\">
                                        <span id=\"id_chargement_$department_num\" style=\"display:none;\"><img src=\"images/chargement_mac.gif\" width=\"15px\"></span>
                                </div>
                                <br>
                                <strong class=\"admin_strong\">".get_translation('LOGIN_LIST',"Liste d'identifiant")." </strong>
                                <br><i class=\"admin_i\">".get_translation('ADD_ASTERISK_BEFORE_OR_AFTER_CODE','Ajoutez une * avant ou après le code APH pour lui donner le rôle de responsable')."</i> : <br>
                                
                        	<table border=0 class=\"noborder\">
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
        print "</td>";
        if ($_SESSION['dwh_droit_admin']=='ok') {
                print "
                        <td valign=\"top\" >
                                <input type=\"button\" onclick=\"supprimer_service('$department_num');\" value='S'>
                        </td>";
        } else {
                print "<td></td>";
        }
        print "</tr>";
}




function affiche_mes_services() {
        global $dbh,$login_session,$user_num_session;
        
        if ($user_num_session!='') {
	        $sel_service=oci_parse($dbh,"select department_num,manager_department from dwh_user_department where user_num=$user_num_session");
	        oci_execute($sel_service);
	        while ($r=oci_fetch_array($sel_service)) {
		        $department_num=$r[0];
		        $verif_manager_department=$r[1];
		        
		        $sel=oci_parse($dbh,"select department_str from dwh_thesaurus_department where department_num=$department_num");
		        oci_execute($sel);
			$r_service=oci_fetch_array($sel);
		        $department_str=$r_service[0];
		        
		        //////////////// LE SERVICE
		        print "<div id=\"id_div_service_$department_num\" >";
		        print "<strong>$department_str</strong><br>";
		        
		        /////////////////// LES UF 
		        print "<a href=\"#\" onclick=\"plier_deplier('id_div_uf_$department_num');return false;\" class=\"admin_lien\"><span id=\"plus_id_div_uf_$department_num\">+</span> ".get_translation('DISPLAY_UNITS','Afficher les unités')."</a>
		        <div id=\"id_div_uf_$department_num\" style=\"display:none\">";
		        print "<table border=\"0\" id=\"id_tableau_uf_$department_num\" width=\"100%\" class=\"noborder\">";
		        $req_uf="select dwh_thesaurus_unit.unit_num, unit_str ,unit_code,to_char(date_start_unit,'DD/MM/YYYY') as date_start_unit,to_char(unit_end_date,'DD/MM/YYYY') as unit_end_date
		        from dwh_thesaurus_unit,dwh_thesaurus_department 
		        where dwh_thesaurus_unit.department_num=dwh_thesaurus_department.department_num and dwh_thesaurus_department.department_num=$department_num order by unit_str ";
		        $sel_uf = oci_parse($dbh,$req_uf);
		        oci_execute($sel_uf);
		        while ($ligne_uf = oci_fetch_array($sel_uf)) {
		                $unit_num = $ligne_uf['UNIT_NUM'];
		                $unit_str = $ligne_uf['UNIT_STR'];
		                $unit_code = $ligne_uf['UNIT_CODE'];
		                $date_start_unit = $ligne_uf['DATE_START_UNIT'];
		                $unit_end_date = $ligne_uf['UNIT_END_DATE'];
		                print "<tr id=\"id_tr_uf_".$department_num."_".$unit_num."\" onmouseover=\"this.style.backgroundColor='#B9C2C8';\" onmouseout=\"this.style.backgroundColor='#ffffff';\" class=\"admin_texte\">
		                        <td>$unit_code ".ucfirst(strtolower($unit_str))." </td><td> $date_start_unit</td><td>$unit_end_date</td>";
		                
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
        $sel_var1=oci_parse($dbh,"select user_num from dwh_user where lower(login)=lower('$login')   ");
        oci_execute($sel_var1);
        $r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC);
        $user_num=$r['USER_NUM'];
        if ($user_num!='') {
                if ($log=='ok') {
                        print "<strong style=\"color:red\">".get_translation('USER_ALREADY_REGISTERD','utilisateur déjà enregistré')."</strong>";
                }
                $res=$user_num;
        } else {
                if ($lastname!='') {
                        $sel_var1=oci_parse($dbh,"select dwh_seq.nextval user_num from dual  ");
                        oci_execute($sel_var1);
                        $r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC);
                        $user_num=$r['USER_NUM'];
                        
                        $req="insert into dwh_user  (user_num , lastname ,firstname ,mail ,login,passwd,creation_date,expiration_date) values ($user_num,'$lastname','$firstname','$mail','$login','',sysdate,to_date('$expiration_date','DD/MM/YYYY'))";
                        $sel_var1=oci_parse($dbh,$req);
                        oci_execute($sel_var1) || die ("<strong style=\"color:red\">".get_translation('ERROR','erreur')." : ".get_translation('PATIENT_NOT_SAVED','patient non sauvé')."</strong>");
                        
                        $tableau_profils=explode(',',$liste_profils);
                        foreach ($tableau_profils as $user_profile) {
                                if ($user_profile!='') {
                                        $req="insert into dwh_user_profile  (user_num ,user_profile) values ($user_num,'$user_profile')";
                                        $sel_var1=oci_parse($dbh,$req);
                                        oci_execute($sel_var1) ||die ("<strong style=\"color:red\">".get_translation('ERROR','erreur')." : ".get_translation('UNSAVED_PROFILES','profils non sauvés')."</strong>");
                                }
                        }
                        
                        $tableau_services=explode(',',$liste_services);
                        foreach ($tableau_services as $department_num) {
                                if ($department_num!='') {
                                        $req="insert into dwh_user_department  (user_num ,department_num) values ($user_num,'$department_num')";
                                        $sel_var1=oci_parse($dbh,$req);
                                        oci_execute($sel_var1) ||die ("<strong style=\"color:red\">".get_translation('ERROR','erreur')." : ".get_translation('UNSAVED_HOSPITAL_DEPARTMENTS','services non sauvés')."</strong>");
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
         $req order by cohort_num desc");
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
        $sel_vardroit=oci_parse($dbh,"select title_cohort,description_cohort, cohort_num,user_num from dwh_cohort where 
         (user_num=$user_num_session or cohort_num in (select cohort_num from dwh_cohort_user_right where user_num=$user_num_session and right='add_patient'))
         $req order by cohort_num desc");
        oci_execute($sel_vardroit);
        while ($r_droit=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $title_cohort=$r_droit['TITLE_COHORT'];
                $description_cohort=$r_droit['DESCRIPTION_COHORT'];
                $cohort_num=$r_droit['COHORT_NUM'];
                $user_num_creation=$r_droit['USER_NUM'];
                
                $sel_pat=oci_parse($dbh,"select count(*) as nb_patient from dwh_cohort_result where cohort_num=$cohort_num and status=1");
                oci_execute($sel_pat);
                $r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC);
                $nb_patient_cohorte=$r_pat['NB_PATIENT'];
                
                $sel_pat=oci_parse($dbh,"select count(*) as nb_patient from dwh_cohort_result where cohort_num=$cohort_num and status=2");
                oci_execute($sel_pat);
                $r_pat=oci_fetch_array($sel_pat,OCI_RETURN_NULLS+OCI_ASSOC);
                $nb_patient_cohorte_doute=$r_pat['NB_PATIENT'];
                
              
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
        if ( $cohort_num!='') {
	        $autorisation_voir_patient_cohorte=verif_autorisation_voir_patient_cohorte($cohort_num,$user_num_session);
	        if ( $autorisation_voir_patient_cohorte=='ok') {
			$res.= "<a href=\"export_excel.php?cohort_num=$cohort_num&status=$status\"><img src=\"images/excel_noir.png\" style=\"cursor:pointer;width:25px;\" title=\"Export Excel\" alt=\"Export Excel\" border=\"0\"></a> ";
			$res.= "<img src=\"images/copier_coller.png\" onclick=\"plier_deplier('id_div_tableau_patient_cohorte_encours$status');plier_deplier('id_div_textarea_patient_cohorte_encours$status');fnSelect('id_div_textarea_patient_cohorte_encours$status');\" style=\"cursor:pointer;\" title=\"Copier Coller pour exporter dans Gecko\" alt=\"Copier Coller pour exporter dans Gecko\"> ";
			$res.= "<div  id=\"id_div_tableau_patient_cohorte_encours$status\" style=\"display:block;\">";
		        $res.= "<table id=\"id_tableau_patient_cohorte_encours$status\" class=\"tableau_cohorte\">";
		        $sel=oci_parse($dbh,"select distinct dwh_cohort_result.patient_num,lastname from dwh_cohort_result,dwh_patient where cohort_num=$cohort_num and dwh_cohort_result.status=$status and dwh_cohort_result.patient_num=dwh_patient.patient_num order by lastname");
		        oci_execute($sel);
		        while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		                $patient_num=$r['PATIENT_NUM'];
		                $res.= "<tr id=\"id_tr_patient_cohorte_$patient_num\" class=\"over_color\"><td>";
		                $res.=afficher_patient($patient_num,'cohorte','',$cohort_num);
		                $res_cohorte_textarea.=afficher_patient($patient_num,'cohorte_textarea','',$cohort_num);
		                $res.="</td></tr>";
		        }
		        $res.= "</table>";
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
		$req_option=" and document_origin_code!='$document_origin_code_labo' ";
		if (preg_match("/\[/",$requete) && preg_match("/\(/",$requete)) {
			$affichage_tableau_expression_regulier='ok';
			$cellspacing="cellspacing=0 cellpadding=2";
			print "<a href=\"#\" onclick=\"fnSelect('id_tableau_liste_document');return false;\">".get_translation('SELECT_TABLE','Sélectionner le tableau')."</a>";
			
		} else {
			$affichage_tableau_expression_regulier='';
			$cellspacing="";
		}
		
	        $tableau_liste_synonyme=array();
		if ($requete!='') {
			if (preg_match("/\[/",$requete)) {
				$req="and document_num in (select document_num from dwh_text where patient_num=$patient_num and (REGEXP_LIKE(text,'$requete','i') or REGEXP_LIKE(title,'$requete','i'))) ";
			} else {
				$req="and document_num in (select document_num from dwh_text where patient_num=$patient_num and (contains(enrich_text,'$requete')>0 or contains(title,'$requete')>0) ) ";
				$tableau_liste_synonyme=recupere_liste_concept_full_texte ($requete);
			}
		}
		$liste_document_origin_code=liste_document_origin_code_tout_compris($patient_num,$user_num_session);
        if ($liste_document_origin_code!='') {
        	if (!preg_match("/'tout'/i","$liste_document_origin_code")) {
			$req.="and document_origin_code in ($liste_document_origin_code) ";
        	}
        } else {
              $req.=" and 1=2";
        }
        $nb_document=0;
		$res= "<table class=\"tableau_document\" $cellspacing id=\"id_tableau_liste_document\">";
        $sel_doc = oci_parse($dbh,"select document_num,encounter_num, title,author,document_date,document_origin_code,displayed_text,to_char(document_date,'DD/MM/YYYY') as date_document_char from dwh_document where patient_num=$patient_num $req  $req_option order by  document_date desc " );   
        oci_execute($sel_doc);
        while ($row_doc = oci_fetch_array($sel_doc, OCI_ASSOC)) {
			$document_num=$row_doc['DOCUMENT_NUM'];
			$encounter_num=$row_doc['ENCOUNTER_NUM'];
			$title=$row_doc['TITLE'];
			$date_document_char=$row_doc['DATE_DOCUMENT_CHAR'];
			$document_origin_code=$row_doc['DOCUMENT_ORIGIN_CODE'];
			$author=$row_doc['AUTHOR'];
			$displayed_text=$row_doc['DISPLAYED_TEXT']->load();
			if ($_SESSION['dwh_droit_fuzzy_display']=='ok') {
				$author='[AUTHOR]';
				$date_document_char='[DATE]';
			}
			
			$nb_document++;
			$res.= "<tr onmouseout=\"this.style.backgroundColor='#ffffff';\" onmouseover=\"this.style.backgroundColor='#B9C2C8';\" onclick=\"afficher_document_patient($document_num);\" style=\"cursor: pointer; background-color:#ffffff;\" id=\"id_document_patient_$document_num\" class=\"tr_document_patient\" sousgroupe=\"text\">";
			
			if ($affichage_tableau_expression_regulier=='ok') {
				$sel_texte = oci_parse($dbh,"select TEXT from dwh_text where document_num=$document_num and certainty=0 and context='text'" );   
				oci_execute($sel_texte);
				$row_texte = oci_fetch_array($sel_texte, OCI_ASSOC);
				if ($row_texte['TEXT']!='') {
					$text=$row_texte['TEXT']->load();         
				}
				$text=preg_replace("/\n/"," ",$text);
				preg_match_all("/$requete/i","$text",$out, PREG_SET_ORDER);
				$res.= "<td style=\"border:1px solid black; border-collapse: collapse;\">$date_document_char</td>";
				foreach ($out[0] as $val) {
					$res.= "<td style=\"border:1px solid black; border-collapse: collapse;\">".$val."</td>";
				}
			} else {
				$res.= "
				<th style=\"text-align:left;\">$document_origin_code</th><th style=\"text-align:left;\"> $title $author</td>
				<td>$date_document_char</td>";
				$res.= "</tr>";
				if ($requete!='') {
					$appercu=resumer_resultat($displayed_text,$requete,$tableau_liste_synonyme,'patient');
					$res.= "<tr><td colspan=\"4\" class=\"appercu\"><i>$appercu</i></td><tr>";
				}
				$res.= "<tr><td colspan=\"4\"><hr  style=\"height:1px;border-top:0px;padding:0px;margin:0px;\"></td>";
			}
			$res.= "</tr>";
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
		$req_option=" and document_origin_code='$document_origin_code_labo' ";
		if (preg_match("/\[/",$requete) && preg_match("/\(/",$requete)) {
			$affichage_tableau_expression_regulier='ok';
			$cellspacing="cellspacing=0 cellpadding=2";
			print "<a href=\"#\" onclick=\"fnSelect('id_tableau_liste_document_biologie');return false;\">".get_translation('SELECT_TABLE','Sélectionner le tableau')."</a>";
		} else {
			$affichage_tableau_expression_regulier='';
			$cellspacing="";
		}
		
		if ($requete!='') {
			if (preg_match("/\[/",$requete)) {
				$req="and document_num in (select document_num from dwh_text where patient_num=$patient_num and (REGEXP_LIKE(text,'$requete','i') or REGEXP_LIKE(title,'$requete','i'))) ";
			} else {
				$req="and document_num in (select document_num from dwh_text where patient_num=$patient_num and (contains(text,'$requete')>0 or contains(title,'$requete')>0) ) ";
			}
		}
		$liste_document_origin_code=liste_document_origin_code_tout_compris($patient_num,$user_num_session);
	        if ($liste_document_origin_code!='') {
	        	if (!preg_match("/'tout'/i","$liste_document_origin_code")) {
				$req.="and document_origin_code in ($liste_document_origin_code) ";
	        	}
	        } else {
	              $req.=" and 1=2";
	        }
	        $nb_document=0;
		$res="<table class=\"tableau_document\" $cellspacing id=\"id_tableau_liste_document_biologie\">";
	        $sel_doc = oci_parse($dbh,"select document_num,encounter_num, title,author,document_date,document_origin_code,displayed_text,to_char(document_date,'DD/MM/YYYY') as date_document_char from dwh_document where patient_num=$patient_num $req  $req_option order by  document_date desc " );   
	        oci_execute($sel_doc);
	        while ($row_doc = oci_fetch_array($sel_doc, OCI_ASSOC)) {
	                $document_num=$row_doc['DOCUMENT_NUM'];
	                $encounter_num=$row_doc['ENCOUNTER_NUM'];
	                $title=$row_doc['TITLE'];
	                $date_document_char=$row_doc['DATE_DOCUMENT_CHAR'];
	                $document_origin_code=$row_doc['DOCUMENT_ORIGIN_CODE'];
	                $author=$row_doc['AUTHOR'];
	                if ($row_doc['DISPLAYED_TEXT']!='') {
	                	$nb_document++;
		                $displayed_text=$row_doc['DISPLAYED_TEXT']->load();
				$res.= "<tr onmouseout=\"this.style.backgroundColor='#ffffff';\" onmouseover=\"this.style.backgroundColor='#B9C2C8';\" onclick=\"afficher_document_patient_biologie($document_num);\" style=\"cursor: pointer; background-color:#ffffff;\" id=\"id_document_patient_$document_num\" class=\"tr_document_patient\" sousgroupe=\"biologie\">";
				
				if ($affichage_tableau_expression_regulier=='ok') {
				        $sel_texte = oci_parse($dbh,"select TEXT from dwh_text where document_num=$document_num and certainty=0 and context='text'" );   
				        oci_execute($sel_texte);
				        $row_texte = oci_fetch_array($sel_texte, OCI_ASSOC);
				        if ($row_texte['TEXT']!='') {
				                $text=$row_texte['TEXT']->load();         
				        }
					$text=preg_replace("/\n/"," ",$text);
					preg_match_all("/$requete/i","$text",$out, PREG_SET_ORDER);
					$res.= "<td style=\"border:1px solid black; border-collapse: collapse;\">$date_document_char</td>";
					foreach ($out[0] as $val) {
							$res.= "<td style=\"border:1px solid black; border-collapse: collapse;\">".$val."</td>";
					}
		                } else {
					$res.= "<th style=\"text-align:left;\">$title</th>
						<td>$date_document_char</td>
						<td>$document_origin_code</td>
						<td>$author</td>
						</tr>";
					$appercu=resumer_resultat($displayed_text,$requete,$tableau_liste_synonyme,'patient');
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



function affiche_liste_id_document_patient($patient_num,$requete) {
	global $dbh,$datamart_num,$user_num_session,$document_origin_code_labo;
	$liste_id_document='';
	$autorisation_voir_patient=autorisation_voir_patient ($patient_num,$user_num_session);
	if ($autorisation_voir_patient=='ok') {
		$req="";
		$req_option=" and document_origin_code!='$document_origin_code_labo' ";
		
	        $tableau_liste_synonyme=array();
		if ($requete!='') {
			if (preg_match("/\[/",$requete)) {
				$req="and document_num in (select document_num from dwh_text where (REGEXP_LIKE(text,'$requete','i') or REGEXP_LIKE(title,'$requete','i')) and patient_num=$patient_num) ";
			} else {
				$req="and document_num in (select document_num from dwh_text where (contains(enrich_text,'$requete')>0 or contains(title,'$requete')>0) and patient_num=$patient_num) ";
				$tableau_liste_synonyme=recupere_liste_concept_full_texte ($requete);
			}
		}
		$liste_document_origin_code=liste_document_origin_code_tout_compris($patient_num,$user_num_session);
	        if ($liste_document_origin_code!='') {
	        	if (!preg_match("/'tout'/i","$liste_document_origin_code")) {
				$req.="and document_origin_code in ($liste_document_origin_code) ";
	        	}
	        } else {
	              $req.=" and 1=2";
	        }
	        $sel_doc = oci_parse($dbh,"select document_num,encounter_num, title,author,document_date,document_origin_code,displayed_text,to_char(document_date,'DD/MM/YYYY') as date_document_char from dwh_document where patient_num=$patient_num $req  $req_option order by  document_date desc " );   
	        oci_execute($sel_doc);
	        while ($row_doc = oci_fetch_array($sel_doc, OCI_ASSOC)) {
	                $document_num=$row_doc['DOCUMENT_NUM'];
	                $encounter_num=$row_doc['ENCOUNTER_NUM'];
	                $title=$row_doc['TITLE'];
	                $date_document_char=$row_doc['DATE_DOCUMENT_CHAR'];
	                $document_origin_code=$row_doc['DOCUMENT_ORIGIN_CODE'];
	                $author=$row_doc['AUTHOR'];
			
			$liste_id_document.="$document_num;";
			
	        }
	        return $liste_id_document;
	}
}


function afficher_document_patient($document_num,$full_text_query) {
        global $dbh,$user_num_session;

        
	$patient_num=get_num_patient_from_id_document($document_num);
	$autorisation_voir_patient=autorisation_voir_patient ($patient_num,$user_num_session);
	
	if ($autorisation_voir_patient=='ok') {
	        $sel_texte = oci_parse($dbh,"select displayed_text,title,patient_num,author,to_char(document_date,'DD/MM/YYYY') as document_date from dwh_document where document_num=$document_num" );   
	        oci_execute($sel_texte);
	        $row_texte = oci_fetch_array($sel_texte, OCI_ASSOC);
	        if ($row_texte['DISPLAYED_TEXT']!='') {
	                $displayed_text=$row_texte['DISPLAYED_TEXT']->load();         
	        }
	        $title=$row_texte['TITLE'];     
	        $author=$row_texte['AUTHOR'];     
	        $document_date=$row_texte['DOCUMENT_DATE'];     
	        $patient_num=$row_texte['PATIENT_NUM']; 
	        
	        if ($_SESSION['dwh_droit_fuzzy_display']=='ok') {
	        	$author='[AUTHOR]';
	                $document_date='[DATE]';
	        }
	        
	        $tableau_liste_synonyme=array();
		if (!preg_match("/\[/",$full_text_query)) {
			$tableau_liste_synonyme=recupere_liste_concept_full_texte ($full_text_query);
		}
	        $autorisation_voir_patient_nominative=autorisation_voir_patient_nominative ($patient_num,$user_num_session);
	        if ($autorisation_voir_patient_nominative=='') {
	                $displayed_text=anonymisation_document ($document_num,$displayed_text);
	        }
	        if ($_SESSION['dwh_droit_see_debug']=='ok') {
		       $displayed_text= afficher_dans_document_tal($document_num);
		}
	        $displayed_text=nettoyer_pour_afficher ($displayed_text);
	        
		if (preg_match("/\[/",$full_text_query)) {
			$displayed_text=surligner_resultat_exp_reguliere ($displayed_text,$full_text_query,'oui');
		} else {
		        if ($full_text_query!='') {
			        $displayed_text=surligner_resultat ($displayed_text,$full_text_query,'','oui',$tableau_liste_synonyme,1);
			}
		        if (!preg_match("/highlight/",$displayed_text) ) {
		                $displayed_text=surligner_resultat ($displayed_text,$full_text_query,'unitaire','oui',$tableau_liste_synonyme,1);
		        }
		}
	        $entete= "<h2>$title,";
	        if ($author!='') {
	        	$entete.=" ".get_translation('BY_FOLLOWED_BY_NAME','par')." $author,";
	        }
	        $entete.=" ".get_translation('THE_DATE','le')." $document_date <img align=\"absmiddle\" src=\"images/printer.png\" onclick=\"ouvrir_document_print('$document_num');return false;\" style=\"cursor:pointer;width:18px;\" border=\"0\"></h2><pre>$displayed_text</pre>";
	        save_log_document($document_num,$user_num_session,'oui');
	        return $entete;
	}
}

function affiche_contenu_liste_document_patient($patient_num,$requete) {
	global $dbh,$datamart_num,$user_num_session,$document_origin_code_labo;
	$autorisation_voir_patient=autorisation_voir_patient ($patient_num,$user_num_session);
	$texte_final="";
	if ($autorisation_voir_patient=='ok') {
		$req="";
		$req_option=" and document_origin_code!='$document_origin_code_labo' ";
		
	        $tableau_liste_synonyme=array();
		if ($requete!='') {
			if (preg_match("/\[/",$requete)) {
				$req="and document_num in (select document_num from dwh_text where patient_num=$patient_num and (REGEXP_LIKE(text,'$requete','i') or REGEXP_LIKE(title,'$requete','i'))) ";
			} else {
				$req="and document_num in (select document_num from dwh_text where patient_num=$patient_num and (contains(enrich_text,'$requete')>0 or contains(title,'$requete')>0) ) ";
				$tableau_liste_synonyme=recupere_liste_concept_full_texte ($requete);
			}
		}
		$liste_document_origin_code=liste_document_origin_code_tout_compris($patient_num,$user_num_session);
	        if ($liste_document_origin_code!='') {
	        	if (!preg_match("/'tout'/i","$liste_document_origin_code")) {
				$req.="and document_origin_code in ($liste_document_origin_code) ";
	        	}
	        } else {
	              $req.=" and 1=2";
	        }
	        $nb_document=0;
	        $sel_doc = oci_parse($dbh,"select document_num,displayed_text,title,patient_num,author,to_char(document_date,'DD/MM/YYYY') as date_document_char,document_date from dwh_document where patient_num=$patient_num $req  $req_option order by  document_date desc " );   
	        oci_execute($sel_doc);
	        while ($row_doc = oci_fetch_array($sel_doc, OCI_ASSOC)) {
	                $document_num=$row_doc['DOCUMENT_NUM'];
	                $title=$row_doc['TITLE'];
	                $date_document_char=$row_doc['DATE_DOCUMENT_CHAR'];
	                $author=$row_doc['AUTHOR'];
	                if ($row_doc['DISPLAYED_TEXT']!='') {
		                $displayed_text=$row_doc['DISPLAYED_TEXT']->load();
			        $tableau_liste_synonyme=array();
				if (!preg_match("/\[/",$requete)) {
					$tableau_liste_synonyme=recupere_liste_concept_full_texte ($requete);
				}
			        $autorisation_voir_patient_nominative=autorisation_voir_patient_nominative ($patient_num,$user_num_session);
			        if ($autorisation_voir_patient_nominative=='') {
			                $displayed_text=anonymisation_document ($document_num,$displayed_text);
			        }
			        if ($_SESSION['dwh_droit_see_debug']=='ok') {
				      $displayed_text= afficher_dans_document_tal($document_num);
				}
			        $displayed_text=nettoyer_pour_afficher ($displayed_text);
			        
				if (preg_match("/\[/",$requete)) {
					$displayed_text=surligner_resultat_exp_reguliere ($displayed_text,$requete,'oui');
				} else {
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
			        $texte_final.=" ".get_translation('THE_DATE','le')." $date_document_char </h2><pre>$displayed_text</pre><br><p style=\"page-break-after: always;\" class=\"noprint\">----------------------------------------------------------</p>";

		        }
			
	        }
	}
	return $texte_final;
}


function afficher_dans_document_tal($document_num) {
        global $dbh,$user_num_session;
	$patient_num=get_num_patient_from_id_document($document_num);
	$autorisation_voir_patient=autorisation_voir_patient ($patient_num,$user_num_session);
	if ($autorisation_voir_patient=='ok') {
	        $sel_texte = oci_parse($dbh,"select displayed_text from dwh_document where document_num=$document_num" );   
	        oci_execute($sel_texte);
	        $row_texte = oci_fetch_array($sel_texte, OCI_ASSOC);
	        if ($row_texte['DISPLAYED_TEXT']!='') {
	                $texte_affichage_origine=$row_texte['DISPLAYED_TEXT']->load();         
	        }
	         $displayed_text= $texte_affichage_origine;
	        
	        $sel_texte = oci_parse($dbh,"select text,context,certainty from dwh_text where document_num=$document_num and certainty!=0 and context in ('patient_text','family_text')" );   
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
		
		
		$sel_datamart=oci_parse($dbh,"select datamart_num as num_datamart_cohorte from dwh_cohort where cohort_num=$cohort_num ");
	        oci_execute($sel_datamart);
		$r=oci_fetch_array($sel_datamart,OCI_RETURN_NULLS+OCI_ASSOC);
	        $num_datamart_cohorte=$r['NUM_DATAMART_COHORTE'];
		
		if ($num_datamart_cohorte=='') {
			$num_datamart_cohorte=0;
		}
		print "<table border=\"0\" id=\"id_tableau_user_cohorte\" class=\"tablefin\" cellpadding=\"3\">
			<tr><th></th>
			";
			if ($num_datamart_cohorte==0) {
				foreach ($tableau_cohorte_droit as $right ) {
					if ($_SESSION['dwh_droit_'.$right.'0']=='ok') {
						if ($right=='nominative' && $autorisation_cohorte_voir_patient_nominative_global=='ok' || $right!='nominative') {
							print "<th>$right</th>";
						}
					}
				}
			} else {
			
				$sel_var1=oci_parse($dbh,"select right from dwh_datamart_user_right where datamart_num=$num_datamart_cohorte and user_num=$user_num_session  ");
				oci_execute($sel_var1);
				while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
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
			
			if ($num_datamart_cohorte==0) {
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
			
				$sel_var1=oci_parse($dbh,"select right from dwh_datamart_user_right where datamart_num=$num_datamart_cohorte and user_num=$user_num_session  ");
				oci_execute($sel_var1);
				while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
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
			
			
			if ($autorisation_cohorte_modifier=='ok') {
				print "<td><img src=\"images/mini_poubelle.png\" onclick=\"supprimer_user_cohorte($user_num_cohort,$cohort_num);\" style=\"cursor:pointer;\"></td>";
			}
			print "</tr>";
	        }
		print "	</table>";
	}
}

function lister_mes_cohortes ($user_num) {
	global $dbh;
	$sel_cohort=oci_parse($dbh,"select title_cohort,description_cohort, cohort_num from dwh_cohort where 
         (user_num=$user_num or cohort_num in (select cohort_num from dwh_cohort_user_right where user_num=$user_num) )
        order by title_cohort ");
        oci_execute($sel_cohort);
        while ($r_cohort=oci_fetch_array($sel_cohort,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $title_cohort=$r_cohort['TITLE_COHORT'];
                $description_cohort=$r_cohort['DESCRIPTION_COHORT'];
                $cohort_num=$r_cohort['COHORT_NUM'];
         	print "<li class=\"cohorte\"><a href=\"mes_cohortes.php?cohort_num_voir=$cohort_num\">$title_cohort</a></li>";
        }
}

function lister_mes_cohortes_tableau ($user_num) {
	global $dbh;
	print "<table border=\"0\" class=\"dataTable\" id=\"id_tableau_liste_cohortes\"><thead><tr><td></td><td></td><td></td></tr></thead><tbody>";
	$sel_cohort=oci_parse($dbh,"select title_cohort,description_cohort, cohort_num from dwh_cohort where 
         (user_num=$user_num or cohort_num in (select cohort_num from dwh_cohort_user_right where user_num=$user_num) )
        order by title_cohort ");
        oci_execute($sel_cohort);
        while ($r_cohort=oci_fetch_array($sel_cohort,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $title_cohort=$r_cohort['TITLE_COHORT'];
                $description_cohort=$r_cohort['DESCRIPTION_COHORT'];
                $cohort_num=$r_cohort['COHORT_NUM'];
                
		$sel=oci_parse($dbh,"select count(*) as NB_INCLU from dwh_cohort_result where cohort_num=$cohort_num and status=1");
		oci_execute($sel);
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$nb_inclu=$r['NB_INCLU'];
              
         	print "<tr>
	         	<td><img src=\"images/mini_cohorte.png\"></td><td class=\"cohorte lien\" onclick=\"document.location.href='mes_cohortes.php?cohort_num_voir=$cohort_num'\" style=\"cursor:pointer\">$title_cohort</td>
	         	<td class=\"cohorte lien\" onclick=\"document.location.href='mes_cohortes.php?cohort_num_voir=$cohort_num'\" style=\"cursor:pointer\">$nb_inclu</td>
         	</tr>";
        }
        print "</tbody></table>";
}

function lister_mes_cohortes_option ($user_num,$id_option) {
	global $dbh;
	print "<option value=''></option>";
	$sel_cohort=oci_parse($dbh,"select title_cohort,description_cohort, cohort_num from dwh_cohort where 
         (user_num=$user_num or cohort_num in (select cohort_num from dwh_cohort_user_right where user_num=$user_num) )
        order by title_cohort ");
        oci_execute($sel_cohort);
        while ($r_cohort=oci_fetch_array($sel_cohort,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $title_cohort=$r_cohort['TITLE_COHORT'];
                $description_cohort=$r_cohort['DESCRIPTION_COHORT'];
                $cohort_num=$r_cohort['COHORT_NUM'];
         	print "<option value=\"$cohort_num\" id=\"".$id_option."_$cohort_num\">$title_cohort</option>";
        }
}

function lister_mes_cohortes_cohort_num_titre ($user_num) {
	global $dbh;
	$sel_cohort=oci_parse($dbh,"select title_cohort,description_cohort, cohort_num from dwh_cohort where 
         (user_num=$user_num or cohort_num in (select cohort_num from dwh_cohort_user_right where user_num=$user_num) )
        order by title_cohort ");
        oci_execute($sel_cohort);
        while ($r_cohort=oci_fetch_array($sel_cohort,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $title_cohort=$r_cohort['TITLE_COHORT'];
                $description_cohort=$r_cohort['DESCRIPTION_COHORT'];
                $cohort_num=$r_cohort['COHORT_NUM'];
         	print "$cohort_num;$title_cohort;separateur;";
        }
}

function lister_mes_cohortes_ajouter_patient ($user_num) {
	global $dbh;
	print "<option value=''></option>";
	$sel_cohort=oci_parse($dbh,"select title_cohort,description_cohort, cohort_num from dwh_cohort where 
         (user_num=$user_num or cohort_num in (select cohort_num from dwh_cohort_user_right where user_num=$user_num and right='add_patient'))
        order by title_cohort ");
        oci_execute($sel_cohort);
        while ($r_cohort=oci_fetch_array($sel_cohort,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $title_cohort=$r_cohort['TITLE_COHORT'];
                $cohort_num=$r_cohort['COHORT_NUM'];
         	print "<option value=\"$cohort_num\">$title_cohort</option>";
        }
}

function lister_cohorte_un_patient ($patient_num) {
	global $dbh;
	print "<ul>";
        $sel_pat=oci_parse($dbh,"select cohort_num,status from dwh_cohort_result where patient_num=$patient_num");
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
        	
		$sel_cohort=oci_parse($dbh,"select title_cohort,description_cohort, user_num from dwh_cohort where cohort_num=$cohort_num");
	        oci_execute($sel_cohort);
	        $r_cohort=oci_fetch_array($sel_cohort,OCI_RETURN_NULLS+OCI_ASSOC);
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

function afficher_cohorte_nb_patient_statut ($cohort_num) {
	global $dbh,$user_num_session;
	 $autorisation_voir_patient_cohorte=verif_autorisation_voir_patient_cohorte($cohort_num,$user_num_session);
	$sel_var1=oci_parse($dbh,"select count(*) as nb_inclu from dwh_cohort_result where   cohort_num=$cohort_num and  status=1 ");
	oci_execute($sel_var1);
	$r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb_inclu=$r['NB_INCLU'];
	$sel_var1=oci_parse($dbh,"select count(*) as nb_exclu from dwh_cohort_result where   cohort_num=$cohort_num and  status=0 ");
	oci_execute($sel_var1);
	$r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb_exclu=$r['NB_EXCLU'];
	$sel_var1=oci_parse($dbh,"select count(*) as nb_doute from dwh_cohort_result where   cohort_num=$cohort_num and  status=2 ");
	oci_execute($sel_var1);
	$r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb_doute=$r['NB_DOUTE'];
	$sel_var1=oci_parse($dbh,"select count(*) as nb_import from dwh_cohort_result where   cohort_num=$cohort_num and  status=3 ");
	oci_execute($sel_var1);
	$r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC);
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
		$requete="select year,document_origin_code,sum(count_document) count_document from  dwh_info_load where document_origin_code is not null and year is not null and year>1995  and month is null group by year,document_origin_code order by document_origin_code,year";
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
		$requete="select year,month,document_origin_code,sum(count_document) count_document from  dwh_info_load where document_origin_code is not null and year is not null and year>1995  and month is not null group by year,month,document_origin_code order by document_origin_code,year";
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
		$requete="select year,month,sum(count_document) count_document from  dwh_info_load where document_origin_code ='$req_document_origin_code' and year is not null and year>1995  and month is not null group by year,month,document_origin_code order by year,month";
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
		$requete="select year,month,document_origin_code from  dwh_info_load where document_origin_code is not null and year is not null and year>1995  and month is not null order by year,month";
		$sel=oci_parse($dbh,$requete);
		oci_execute($sel) || die ("erreur requete $requete\n");
		while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$document_origin_code=$r['DOCUMENT_ORIGIN_CODE'];
			$year=$r['YEAR'];
			$month=$r['MONTH'];
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



function afficher_mes_droits () {
	global $dbh,$login_session,$user_num_session;

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
	$sel_var1=oci_parse($dbh,"select user_profile from dwh_user_profile where user_num in (select user_num from dwh_user where login='$login_session')");
	oci_execute($sel_var1);
	while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
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
		$sel_var1=oci_parse($dbh,"select department_str from dwh_user_department, dwh_thesaurus_department where dwh_user_department.department_num=dwh_thesaurus_department.department_num and user_num='$user_num_session' ");
		oci_execute($sel_var1);
		while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$department_str=$r['DEPARTMENT_STR'];
			$liste_service.="$department_str, ";
		}
		$liste_service=substr($liste_service,0,-2);
		print "<b>".get_translation('AUTHORIZED_HOSPITAL_DEPARTMENTS','Services auxquels vous avez accès')." :</b>$liste_service<br>";
	}
	
	//// LES TYPES DOC DE L'UTILISATEUR //////////
	$liste_document_origin_code='';
	$sel_var1=oci_parse($dbh,"select distinct document_origin_code from dwh_profile_document_origin, dwh_user_profile where user_num='$user_num_session' and dwh_profile_document_origin.user_profile= dwh_user_profile.user_profile");
	oci_execute($sel_var1);
	while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$document_origin_code=$r['DOCUMENT_ORIGIN_CODE'];
		$liste_document_origin_code.="$document_origin_code, ";
	}
	$liste_document_origin_code=substr($liste_document_origin_code,0,-2);
	print "<br><b>".get_translation('AUTHORIZED_DOCUMENT_ORIGINS','Les documents autorisés')." :</b><br>$liste_document_origin_code";
}


function parcours_complet($tmpresult_num,$cohort_num,$patient_num_local,$unit_or_department,$nb_mini) {
	global $dbh,$CHEMIN_GLOBAL;
	$max_nb=1;
	$max_width=20;
	$patient_num_preced='';
	$tableau_service_distinct=array();
	$tableau_nb_passage_service_service=array();
	$tableau_delais_total_service_service=array();
	if ($tmpresult_num!='' && $unit_or_department=='department') {
		$req="select patient_num,department_num,to_char(date_service,'YYYY-MM-DD') as date_service_char,date_service,to_char(out_date,'YYYY-MM-DD') as date_sortie_char,out_date,source
					from ( select distinct  department_num,entry_date as date_service,patient_num,out_date,'hospit' as source from dwh_patient_mvt where patient_num in (select patient_num from dwh_tmp_result where tmpresult_num=$tmpresult_num)
					union
					select department_num,consultation_date as date_service,patient_num,consultation_date as out_date,'consult' as source  from dwh_patient_consultation where patient_num in (select patient_num from dwh_tmp_result where tmpresult_num=$tmpresult_num)
					) t where department_num is not null and date_service is not null
					order by patient_num,date_service,out_date";
	}
	if ($cohort_num!='' && $unit_or_department=='department') {
		$req="select patient_num,department_num,to_char(date_service,'YYYY-MM-DD') as date_service_char,date_service,to_char(out_date,'YYYY-MM-DD') as date_sortie_char,out_date,source
					from ( select distinct  department_num,entry_date as date_service,patient_num,out_date,'hospit' as source from dwh_patient_mvt where patient_num in (select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1)
					union
					select department_num,consultation_date as date_service,patient_num,consultation_date as out_date,'consult' as source  from dwh_patient_consultation where patient_num in (select patient_num from dwh_tmp_result where tmpresult_num=$tmpresult_num)
					) t where department_num is not null and date_service is not null
					order by patient_num,date_service,out_date";
	}
	if ($patient_num_local!='' && $unit_or_department=='department') {
		$req="select patient_num,department_num,to_char(date_service,'YYYY-MM-DD') as date_service_char,date_service,to_char(out_date,'YYYY-MM-DD') as date_sortie_char,out_date,source
					from ( select distinct  department_num,entry_date as date_service,patient_num,out_date,'hospit' as source from dwh_patient_mvt where patient_num='$patient_num_local'
					union 
					select department_num,consultation_date as date_service,patient_num,consultation_date as out_date,'consult' as source  from dwh_patient_consultation where patient_num='$patient_num_local'
					) t where department_num is not null and date_service is not null
					order by patient_num,date_service,out_date";
	}
	$sel=oci_parse($dbh,"$req");
	oci_execute($sel) ;
	while ($res=oci_fetch_array($sel,OCI_ASSOC)) {
		$patient_num=$res['PATIENT_NUM'];
		$department_num=$res['SOURCE']."_".$res['DEPARTMENT_NUM'];
		$date_service=$res['DATE_SERVICE_CHAR'];
		$out_date=$res['DATE_SORTIE_CHAR'];
		//$source=$res['SOURCE'];
		if ($patient_num==$patient_num_preced && $department_num_preced!='') {
		
			$d1 = new DateTime("$date_service 12:00:00");
			$d2 = new DateTime("$date_sortie_preced 12:00:00");
			$diff = $d2->diff($d1);
			
			$delais_total = $diff->days;
			$tableau_nb_passage_service_service[$department_num_preced.';'.$department_num]++;
			$tableau_service_distinct[$department_num]++;
			if ($delais_total>0) {
				$tableau_delais_total_service_service[$department_num_preced.';'.$department_num]+=$delais_total;
			} else {
				// si le delais est negatif, c'est qu'il y a des consultation au sein du sejour dans un service... radio, reeduc etc...
				// du coup on calcul le delais à partir de la date d'entree dans le service precedent///
				$d1 = new DateTime("$date_service 12:00:00");
				$d2 = new DateTime("$date_service_preced 12:00:00");
				$diff = $d2->diff($d1);
				$delais_total = $diff->days;
				if ($delais_total>0) {
					$tableau_delais_total_service_service[$department_num_preced.';'.$department_num]+=$delais_total;
				}
				
				// et retour dans le service d'origine
				$tableau_nb_passage_service_service[$department_num.';'.$department_num_preced]++;
				$department_num=$department_num_preced;
				$out_date=$date_sortie_preced;
				$date_service_preced=$date_service; // on repart d'une date de service à la date de la consultation
			}
		}
		if ($patient_num!=$patient_num_preced && $patient_num_preced!='') {
			$department_num_preced='';
			$date_service_preced='';
			$date_sortie_preced='';
		} else {
			$department_num_preced=$department_num;
			$date_service_preced=$date_service;
			$date_sortie_preced=$out_date;
		}
		$patient_num_preced=$patient_num;
	}


	$tableau_service_conserve=array();
	foreach ($tableau_nb_passage_service_service as $service_service => $nb_passage) {
		if ($nb_passage>=$nb_mini) {
			list($department_num_1,$department_num_2)=explode(';',$service_service);
	
			//$tableau_service_distinct[$department_num_1]+=$nb_passage;
			//$tableau_service_distinct[$department_num_2]+=$nb_passage;
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
		$fichier= "	
		digraph G { 
		        bgcolor=white; 
		        node [shape=box]; 
		         node [ fontname=Arial, fontsize=9];
		         edge [ fontname=Helvetica,  fontsize=9 ];
		
		        ";
		foreach ($tableau_service_conserve as $department_num => $ok) {
			$nb_dans_service=$tableau_service_distinct[$department_num];
			
			list($source,$department_num_reel)=explode('_',$department_num);
	                $department_str=get_department_str ($department_num_reel);    
			$department_str=str_replace("<"," inf ",$department_str);
			$department_str=str_replace(">"," sup ",$department_str);
			$fichier.= "$department_num [label=<$department_str<BR/>$source<BR/>$nb_dans_service passages>];\n";
		}
		
		foreach ($tableau_nb_passage_service_service as $service_service => $nb_passage) {
			list($department_num_1,$department_num_2)=explode(';',$service_service);
			$delai_total=$tableau_delais_total_service_service[$service_service];
			$delai_moyen=round($delai_total/$nb_passage,0);
	
	#		$log_max_nb=log($max_nb);
	#		$log_nb_passage=log($nb_passage);
	#		$new_nb_passage=round($log_nb_passage/$log_max_nb,0);
			$new_nb_passage=round($nb_passage*$max_width/$max_nb,0);
			if ($new_nb_passage==0) {
				$new_nb_passage=1;
			}
	
			//print "$department_num_1 -> $department_num_2 delai_total $delai_total [weight=1, penwidth=$new_nb_passage, label=\"$nb_passage, delai moyen $delai_moyen jours \",fontcolor=\"black\"] ;\n<br>";
			$fichier.= "	$department_num_1 -> $department_num_2 [weight=1, penwidth=$new_nb_passage, label=\"$nb_passage, delai moyen $delai_moyen jours \",fontcolor=\"red\"] ;\n";
		}
		$fichier.= "
		}";
		$num_file=$tmpresult_num.$patient_num_local;
		$inF = fopen("$CHEMIN_GLOBAL/upload/tmp_graphviz_parcours_complet_$num_file.dot","w");
		fputs( $inF,"$fichier");
		fclose($inF);
		
		exec("/usr/bin/dot \"$CHEMIN_GLOBAL/upload/tmp_graphviz_parcours_complet_$num_file.dot\" -Gcharset=latin1 -Tpng -o  \"$CHEMIN_GLOBAL/upload/tmp_graphviz_parcours_complet_$num_file.png\"");
	}
}


function parcours_sejour_uf_json ($tmpresult_num,$cohort_num,$patient_num,$patient_num_encounter_num,$nb_mini) {
        global $dbh,$CHEMIN_GLOBAL;
        $max_nb=1;
        $max_width=20;
        $tableau_parcours=array();
        $tableau_noeud_present=array();
        $fichier= "{\"nodes\":[";
        $liste_total_service='';
        $tableau_list_node=array();
        $tableau_node_deja=array();
        $nb_noeud=-1;
        if ($tmpresult_num!='') {
                $req=oci_parse($dbh,"select
			entry_mode,unit_code_in,unit_num_in,count(*) nb from dwh_patient_stay  where  exists ( select encounter_num from dwh_tmp_result where tmpresult_num=$tmpresult_num and $patient_num_encounter_num= dwh_patient_stay.$patient_num_encounter_num and encounter_num is not null)
			 and unit_num_in is not null
			 and  out_date is not null
			 group by entry_mode, unit_code_in,unit_num_in having count(*)>=$nb_mini ");
        }
        if ($cohort_num!='') {
                $req=oci_parse($dbh,"select
			entry_mode,unit_code_in,unit_num_in,count(*) nb from dwh_patient_stay  where  patient_num in  ( select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1)
			 and unit_num_in is not null
			 and  out_date is not null
			 group by entry_mode, unit_code_in,unit_num_in having count(*)>=$nb_mini ");
        }
        if ($patient_num!='') {
                $req=oci_parse($dbh,"select
			entry_mode,unit_code_in,unit_num_in,count(*) nb from dwh_patient_stay  where patient_num=$patient_num
			 and unit_num_in is not null
			 and  out_date is not null
			 group by unit_code_in,unit_num_in ");
        }
        oci_execute($req) ;
        while ($res=oci_fetch_array($req,OCI_ASSOC)) {
                $entry_mode=$res['ENTRY_MODE'];
                $unit_code_in=$res['UNIT_CODE_IN'];
                $unit_num_in=$res['UNIT_NUM_IN'];
                $nb=$res['NB'];

                if ($tableau_node_deja[$entry_mode]=='') {
                        $nb_noeud++;
                        $fichier.= "{\"name\":\"$entry_mode\"},";
                        $tableau_list_node[$entry_mode]=strval($nb_noeud); // because value 0 as an integer is considered as null
                        $tableau_node_deja[$entry_mode]='ok';
                }

                $tableau_parcours[$entry_mode.';'.$unit_num_in]=$nb;
                $tableau_noeud_present[$unit_num_in]='ok';
                if ($max_nb<$nb) {
                        $max_nb=$nb;
                }
        }

        if ($tmpresult_num!='') {
                $req=oci_parse($dbh,"select
			out_mode,unit_code_out,unit_num_out,count(*) nb from dwh_patient_stay  where  exists ( select encounter_num from dwh_tmp_result where tmpresult_num=$tmpresult_num and $patient_num_encounter_num= dwh_patient_stay.$patient_num_encounter_num and encounter_num is not null)  and unit_num_out is not null
			and  out_date is not null
			group by out_mode,unit_code_out,unit_num_out having count(*)>=$nb_mini ");
        }
        if ($cohort_num!='') {
                $req=oci_parse($dbh,"select
			out_mode,unit_code_out,unit_num_out,count(*) nb from dwh_patient_stay  where  patient_num in  ( select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1) 
			and unit_num_out is not null
			and  out_date is not null
			group by out_mode,unit_code_out,unit_num_out having count(*)>=$nb_mini ");
        }
        if ($patient_num!='') {
                $req=oci_parse($dbh,"select
			out_mode,unit_code_out,unit_num_out,count(*) nb from dwh_patient_stay,dwh_thesaurus_unit  where   patient_num=$patient_num  and unit_num_out is not null
			and  out_date is not null
			group by out_mode,unit_code_out,unit_num_out ");
        }
        oci_execute($req) ;
        while ($res=oci_fetch_array($req,OCI_ASSOC)) {
                $out_mode=$res['OUT_MODE'];
                $unit_code_out=$res['UNIT_CODE_OUT'];
                $unit_num_out=$res['UNIT_NUM_OUT'];
                $nb=$res['NB'];

                if ($tableau_node_deja[$out_mode]=='') {
                        $nb_noeud++;
                        $fichier.= "{\"name\":\"$out_mode\"},";
                        $tableau_list_node[$out_mode]=strval($nb_noeud);
                        $tableau_node_deja[$out_mode]='ok';
                }
                $tableau_parcours[$unit_num_out.';'.$out_mode]=$nb;
                $tableau_noeud_present[$unit_num_out]='ok';
                if ($max_nb<$nb) {
                        $max_nb=$nb;
                }
        }


        if ($tmpresult_num!='') {
                $req=oci_parse($dbh,"select
	                    a.unit_num UF_E,
	                    b.unit_num UF_S,
	                    count(*) as NB
	        from
	            dwh_patient_mvt a,
	            dwh_patient_mvt b
	        where
	                a.patient_num=b.patient_num 
	                and  a.encounter_num=b.encounter_num and  a.unit_num!=b.unit_num and
	                b.entry_date=a.out_date
	                and  a.out_date is not null
	                and  b.out_date is not null
	                and  a.encounter_num is not null
	                and  b.encounter_num is not null

	              and exists ( select encounter_num from dwh_tmp_result where tmpresult_num=$tmpresult_num and $patient_num_encounter_num= a.$patient_num_encounter_num and encounter_num is not null)
	                and  exists ( select encounter_num from dwh_tmp_result where tmpresult_num=$tmpresult_num and $patient_num_encounter_num= b.$patient_num_encounter_num and encounter_num is not null) group by a.unit_num ,b.unit_num
	                    having count(*)>=$nb_mini
	                     ");
        }

        if ($cohort_num!='') {
                $req=oci_parse($dbh,"select
	                    a.unit_num UF_E,
	                    b.unit_num UF_S,
	                    count(*) as NB
	        from
	            dwh_patient_mvt a,
	            dwh_patient_mvt b
	        where
	                a.patient_num=b.patient_num 
	             	and  a.patient_num in  ( select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1) 
		        and  b.patient_num in  ( select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1) 
	                and  a.encounter_num=b.encounter_num and  a.unit_num!=b.unit_num 
	                and  b.entry_date=a.out_date
	                and  a.out_date is not null
	                and  b.out_date is not null
	                and  a.encounter_num is not null
	                and  b.encounter_num is not null
	                group by a.unit_num ,b.unit_num
	                    having count(*)>=$nb_mini
	                     ");
        }
        if ($patient_num!='') {
                $req=oci_parse($dbh,"select
	                    a.unit_num UF_E,
	                    b.unit_num UF_S,
	                    count(*) as NB
	        from
	            dwh_patient_mvt a,
	            dwh_patient_mvt b
	        where
			a.patient_num=b.patient_num 
			and a.encounter_num=b.encounter_num and  a.unit_num!=b.unit_num
			and b.entry_date=a.out_date
			and a.out_date is not null
			and b.out_date is not null
			and a.encounter_num is not null
			and b.encounter_num is not null
	            and    a.patient_num=$patient_num and b.patient_num=$patient_num group by   a.unit_num ,b.unit_num
	                     ");
        }
        oci_execute($req) ;
        while ($res=oci_fetch_array($req,OCI_ASSOC)) {
                $num_uf_e=$res['UF_E'];
                $num_uf_s=$res['UF_S'];
                $nb=$res['NB'];

                $tableau_parcours[$num_uf_e.';'.$num_uf_s]=$nb;
                $tableau_noeud_present[$num_uf_e]='ok';
                $tableau_noeud_present[$num_uf_s]='ok';
                if ($max_nb<$nb) {
                        $max_nb=$nb;
                }
        }


        if ($tmpresult_num!='') {
                $req=oci_parse($dbh,"select unit_num ,round(median(out_date-entry_date),0) as dms from dwh_patient_mvt  where
	                 exists ( select encounter_num from dwh_tmp_result where tmpresult_num=$tmpresult_num and $patient_num_encounter_num= dwh_patient_mvt.$patient_num_encounter_num and encounter_num is not null)
	                and  out_date is not null
			and out_date is not null group by  unit_num  ");
        }
        if ($cohort_num!='') {
                $req=oci_parse($dbh,"select unit_num ,round(median(out_date-entry_date),0) as dms from dwh_patient_mvt  where
	                 patient_num in ( select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1 )
	                and  out_date is not null
	                and encounter_num is not null
			and out_date is not null group by  unit_num  ");
        }
        if ($patient_num!='') {
                $req=oci_parse($dbh,"select unit_num ,round(median(out_date-entry_date),0) as dms from dwh_patient_mvt  where
	                patient_num=$patient_num
	                and  out_date is not null
			and out_date is not null group by unit_num ");
        }
        oci_execute($req) ;
        while ($res=oci_fetch_array($req,OCI_ASSOC)) {
                $unit_num=$res['UNIT_NUM'];
                $dms=$res['DMS'];
                $req_uf=oci_parse($dbh,"select unit_str,unit_code from dwh_thesaurus_unit where unit_num='$unit_num' ");
                oci_execute($req_uf) ;
                $r=oci_fetch_array($req_uf,OCI_ASSOC);
                $lib_uf=$r['UNIT_STR'];
                $unit_code=$r['UNIT_CODE'];
                if (preg_match("/^,/",$dms)) {
                        $dms="0".$dms;
                }
                $lib_uf=str_replace("<"," inf ",$lib_uf);
                $lib_uf=str_replace(">"," sup ",$lib_uf);

                if ($tableau_node_deja[$unit_num]=='' && $tableau_noeud_present[$unit_num]=='ok') {
                        $nb_noeud++;
                        $fichier.= "{\"name\":\"$lib_uf\", \"dms\":\"$dms\"},";
                        $tableau_list_node[$unit_num]=strval($nb_noeud);
                        $tableau_node_deja[$unit_num]='ok';
                }
        }
        $fichier=substr($fichier,0,-1);
        $fichier.= "],
	\"links\":[";

        foreach ($tableau_parcours as $lien => $poids) {
                //$new_poids=round($poids*$max_width/$max_nb,0);
                //if ($new_poids==0) {
                //	$new_poids=1;
                //}
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
        $fichier.= "
		]}";
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
        $num_file=$tmpresult_num.$patient_num;
        $inF = fopen("$CHEMIN_GLOBAL/upload/tmp_d3_uf_json_$num_file.json","w");
        fputs( $inF,"$fichier");
        fclose($inF);



}


function parcours_sejour_uf ($tmpresult_num,$cohort_num,$patient_num,$patient_num_encounter_num,$nb_mini) {
	global $dbh,$CHEMIN_GLOBAL;

	$max_nb=1;
	$max_width=20;
	$tableau_parcours=array();

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

	$liste_total_num_uf='';
	if ($tmpresult_num!='') {
		$req=oci_parse($dbh,"select 
			entry_mode,unit_code_in,unit_num_in,count(*) nb from dwh_patient_stay  where  exists ( select encounter_num from dwh_tmp_result where tmpresult_num=$tmpresult_num and $patient_num_encounter_num= dwh_patient_stay.$patient_num_encounter_num and encounter_num is not null) 
			 and unit_num_in is not null  and out_date is not null
			 group by entry_mode, unit_code_in,unit_num_in having count(*)>=$nb_mini ");
	}
	if ($cohort_num!='') {
		$req=oci_parse($dbh,"select 
			entry_mode,unit_code_in,unit_num_in,count(*) nb from dwh_patient_stay  where  patient_num in ( select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1 ) 
			 and unit_num_in is not null  and out_date is not null
			 and encounter_num is not null
			 group by entry_mode, unit_code_in,unit_num_in having count(*)>=$nb_mini ");
	}
	if ($patient_num!='') {
		$req=oci_parse($dbh,"select 
			entry_mode,unit_code_in,unit_num_in,count(*) nb from dwh_patient_stay  where  patient_num=$patient_num 
			 and unit_code_in is not null  and out_date is not null
			 group by entry_mode, unit_code_in,unit_num_in");
	}
	
	oci_execute($req) ;
	while ($res=oci_fetch_array($req,OCI_ASSOC)) {
		$entry_mode=$res['ENTRY_MODE'];
		$unit_code_in=$res['UNIT_CODE_IN'];
		$nb=$res['NB'];
		$unit_num_in=$res['UNIT_NUM_IN'];
		
		
		
		$fichier.= " \"$entry_mode\" [color=\"blue\"];\n";
		$tableau_parcours["\"$entry_mode\" -> $unit_num_in"]=$nb;
		if ($max_nb<$nb) {
			$max_nb=$nb;
		}
		$liste_total_num_uf.="'$unit_num_in',";
		$tableau_uf_display[$unit_num_in]='ok';
	}

	if ($tmpresult_num!='') {
		$req=oci_parse($dbh,"select 
		out_mode,unit_code_out,unit_num_out,count(*) nb from dwh_patient_stay  where  exists ( select encounter_num from dwh_tmp_result where tmpresult_num=$tmpresult_num and $patient_num_encounter_num= dwh_patient_stay.$patient_num_encounter_num and encounter_num is not null)  and unit_code_out is not null  and out_date is not null group by out_mode, unit_code_out,unit_num_out having count(*)>=$nb_mini ");
	}
	if ($cohort_num!='') {
		$req=oci_parse($dbh,"select 
		out_mode,unit_code_out,unit_num_out,count(*) nb from dwh_patient_stay  where patient_num in ( select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1 ) 
		 and unit_code_out is not null  and out_date is not null and encounter_num is not null group by out_mode, unit_code_out,unit_num_out having count(*)>=$nb_mini ");
	}
	if ($patient_num!='') {
		$req=oci_parse($dbh,"select 
		out_mode,unit_code_out,unit_num_out,count(*) nb from dwh_patient_stay  where  patient_num=$patient_num and unit_code_out is not null  and out_date is not null group by out_mode, unit_code_out,unit_num_out");
	}
	oci_execute($req) ;
	while ($res=oci_fetch_array($req,OCI_ASSOC)) {
		$out_mode=$res['OUT_MODE'];
		$unit_code_out=$res['UNIT_CODE_OUT'];
		$nb=$res['NB'];
		$unit_num_out=$res['UNIT_NUM_OUT'];
		
		
		$fichier.= " \"$out_mode\" [color=\"red\"];\n";
		$tableau_parcours["$unit_num_out -> \"$out_mode\""]=$nb;
		if ($max_nb<$nb) {
			$max_nb=$nb;
		}
		$liste_total_num_uf.="'$unit_num_out',";
		$tableau_uf_display[$unit_num_out]='ok';
	}


	if ($tmpresult_num!='') {
		$req=oci_parse($dbh,"select a.unit_num UF_E,b.unit_num UF_S,count(*) as NB  from dwh_patient_mvt a,dwh_patient_mvt b where
	                a.patient_num=b.patient_num  
	                and a.encounter_num=b.encounter_num 
	                and  a.unit_num!=b.unit_num and
	                b.entry_date=a.out_date
	                and   b.out_date is not null
	                and   a.out_date is not null
	                and a.encounter_num is not null 
	                and b.encounter_num is not null 
	                and exists ( select encounter_num from dwh_tmp_result where tmpresult_num=$tmpresult_num and $patient_num_encounter_num= a.$patient_num_encounter_num and encounter_num is not null)
	                and  exists ( select encounter_num from dwh_tmp_result where tmpresult_num=$tmpresult_num and $patient_num_encounter_num= b.$patient_num_encounter_num and encounter_num is not null) group by  a.unit_num ,b.unit_num having count(*)>=$nb_mini ");
	}
	if ($cohort_num!='') {
		$req=oci_parse($dbh,"select a.unit_num UF_E,b.unit_num UF_S,count(*) as NB  from dwh_patient_mvt a,dwh_patient_mvt b where
	                a.patient_num=b.patient_num  
	                and a.encounter_num=b.encounter_num 
	                and  a.unit_num!=b.unit_num 
	                and b.entry_date=a.out_date
	                and   b.out_date is not null
	                and   a.out_date is not null
	                and a.patient_num in ( select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1 ) 
	                and b.patient_num in ( select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1 ) 
	                and a.encounter_num is not null 
	                and b.encounter_num is not null 
	                group by  a.unit_num ,b.unit_num having count(*)>=$nb_mini ");
	}
	if ($patient_num!='') {
		$req=oci_parse($dbh,"select a.unit_num UF_E,b.unit_num UF_S,count(*) as NB  from dwh_patient_mvt a,dwh_patient_mvt b where
	                a.patient_num=b.patient_num  
	                and a.encounter_num=b.encounter_num 
	                and  a.unit_num!=b.unit_num and
	                b.entry_date=a.out_date
	                and   b.out_date is not null
	                and   a.out_date is not null
	                and a.encounter_num is not null 
	                and b.encounter_num is not null 
	                and a.patient_num=$patient_num
	                and b.patient_num=$patient_num group by  a.unit_num ,b.unit_num ");
	}
	oci_execute($req) ;
	while ($res=oci_fetch_array($req,OCI_ASSOC)) {
		$num_uf_e=$res['UF_E'];
		$num_uf_s=$res['UF_S'];
		$nb=$res['NB'];
		
		
		
		$tableau_parcours["$num_uf_e -> $num_uf_s"]=$nb;
		if ($max_nb<$nb) {
			$max_nb=$nb;
		}
		$liste_total_num_uf.="'$num_uf_e',";
		$liste_total_num_uf.="'$num_uf_s',";
		$tableau_uf_display[$num_uf_e]='ok';
		$tableau_uf_display[$num_uf_s]='ok';
	}

	$liste_total_num_uf=substr($liste_total_num_uf,0,-1);
	if ($liste_total_num_uf!='') {
		if ($tmpresult_num!='') {
			$req=oci_parse($dbh,"select unit_num ,round(avg(out_date-entry_date),1) as dms from dwh_patient_mvt where
	                exists ( select encounter_num from dwh_tmp_result where tmpresult_num=$tmpresult_num and $patient_num_encounter_num= dwh_patient_mvt.$patient_num_encounter_num and encounter_num is not null) 
	                and  out_date is not null
	                and  out_date>=entry_date
	                and unit_num in ($liste_total_num_uf) group by  unit_num  ");
		}
		if ($cohort_num!='') {
			$req=oci_parse($dbh,"select unit_num ,round(avg(out_date-entry_date),1) as dms from dwh_patient_mvt where
	             	  patient_num in ( select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1 ) 
	                and  out_date is not null
	                and  out_date>=entry_date
	                and encounter_num is not null
	                and unit_num in ($liste_total_num_uf) group by  unit_num  ");
		}
		
		if ($patient_num!='') {
			$req=oci_parse($dbh,"select unit_num ,round(avg(out_date-entry_date),1) as dms from dwh_patient_mvt where
	                patient_num=$patient_num
	                and  out_date is not null
	                and  out_date>=entry_date
	                and unit_num in ($liste_total_num_uf) group by  unit_num  ");
		}
		oci_execute($req) ;
		while ($res=oci_fetch_array($req,OCI_ASSOC)) {
			$unit_num=$res['UNIT_NUM'];
			$dms=$res['DMS'];
			if ($tableau_uf_display[$unit_num]=='ok') {
				$req_uf=oci_parse($dbh,"select unit_str,unit_code from dwh_thesaurus_unit where unit_num='$unit_num' ");
				oci_execute($req_uf) ;
				$r=oci_fetch_array($req_uf,OCI_ASSOC);
				$lib_uf=$r['UNIT_STR'];
				$unit_code=$r['UNIT_CODE'];
				if (preg_match("/^,/",$dms)) {
					$dms="0".$dms;
				}
				$lib_uf=str_replace("<"," inf ",$lib_uf);
				$lib_uf=str_replace(">"," sup ",$lib_uf);
				$fichier.= "$unit_num [label=<$unit_code-$lib_uf<BR/>DMS : $dms jours>];\n";
				$tableau_uf_display_libelle[$unit_num]='ok';
			}
		}
		foreach ($tableau_uf_display as $unit_num => $ok) {
			$req_uf=oci_parse($dbh,"select unit_str,unit_code from dwh_thesaurus_unit where unit_num='$unit_num' ");
			oci_execute($req_uf) ;
			$r=oci_fetch_array($req_uf,OCI_ASSOC);
			$lib_uf=$r['UNIT_STR'];
			$lib_uf=str_replace("<"," inf ",$lib_uf);
			$lib_uf=str_replace(">"," sup ",$lib_uf);
			$fichier.= "$unit_num [label=<$lib_uf>];\n";
			$tableau_uf_display_libelle[$unit_num]='ok';
		}
	
		foreach ($tableau_parcours as $lien => $poids) {
			$new_poids=round($poids*$max_width/$max_nb,0);
			if ($new_poids==0) {
				$new_poids=1;
			}
			$fichier.= "	$lien [weight=1, penwidth=$new_poids, label=\"$poids\",fontcolor=\"red\"] ;\n";
		}
		$fichier.= "
		}";	
		$num_file=$tmpresult_num.$patient_num;
		$inF = fopen("$CHEMIN_GLOBAL/upload/tmp_graphviz_parcours_sejour_uf_$num_file.dot","w");
		fputs( $inF,"$fichier");
		fclose($inF);
		
		exec("/usr/bin/dot \"$CHEMIN_GLOBAL/upload/tmp_graphviz_parcours_sejour_uf_$num_file.dot\" -Gcharset=latin1 -Tpng -o  \"$CHEMIN_GLOBAL/upload/tmp_graphviz_parcours_sejour_uf_$num_file.png\"");
	}
}


function parcours_sejour_service ($tmpresult_num,$cohort_num,$patient_num,$patient_num_encounter_num,$nb_mini) {
	global $dbh,$CHEMIN_GLOBAL;
	$max_nb=1;
	$max_width=20;
	$tableau_parcours=array();

        $dot_lines='';
	$liste_total_service='';
	$tableau_service_display=array();
	if ($tmpresult_num!='') {
		$req=oci_parse($dbh,"select 
			entry_mode,department_num,count(*) nb from dwh_patient_stay,dwh_thesaurus_unit  where  exists ( select encounter_num from dwh_tmp_result where tmpresult_num=$tmpresult_num and $patient_num_encounter_num= dwh_patient_stay.$patient_num_encounter_num and encounter_num is not null) 
			 and unit_num_in is not null 
			 and unit_num_in=unit_num
	                and  out_date is not null
			 group by entry_mode, department_num having count(*)>=$nb_mini ");
	}
	if ($cohort_num!='') {
		$req=oci_parse($dbh,"select 
			entry_mode,department_num,count(*) nb from dwh_patient_stay,dwh_thesaurus_unit  where  patient_num in ( select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1 ) 
			 and unit_num_in is not null 
			 and unit_num_in=unit_num
	                and  out_date is not null
	                and encounter_num is not null
			 group by entry_mode, department_num having count(*)>=$nb_mini ");
	}
	if ($patient_num!='') {
		$req=oci_parse($dbh,"select 
			entry_mode,department_num,count(*) nb from dwh_patient_stay,dwh_thesaurus_unit  where patient_num=$patient_num
			 and unit_num_in is not null 
			 and unit_num_in=unit_num
	                and  out_date is not null
			 group by entry_mode, department_num ");
	}
	oci_execute($req) ;
	while ($res=oci_fetch_array($req,OCI_ASSOC)) {
		$entry_mode=$res['ENTRY_MODE'];
		$department_num=$res['DEPARTMENT_NUM'];
		$nb=$res['NB'];

		$dot_lines.= " \"$entry_mode\" [color=\"blue\"];\n";
		$tableau_parcours["\"$entry_mode\" -> $department_num"]=$nb;
		if ($max_nb<$nb) {
			$max_nb=$nb;
		}
		$liste_total_service.="'$department_num',";
		$tableau_service_display[$department_num]='ok';
	}
	if ($tmpresult_num!='') {
		$req=oci_parse($dbh,"select 
			out_mode,department_num,count(*) nb from dwh_patient_stay,dwh_thesaurus_unit  where  exists ( select encounter_num from dwh_tmp_result where tmpresult_num=$tmpresult_num and $patient_num_encounter_num= dwh_patient_stay.$patient_num_encounter_num and encounter_num is not null)  and unit_num_out is not null 
			 and unit_num_out=unit_num
	                and  out_date is not null
			 group by out_mode, department_num having count(*)>=$nb_mini ");
	}
	if ($cohort_num!='') {
		$req=oci_parse($dbh,"select 
			out_mode,department_num,count(*) nb from dwh_patient_stay,dwh_thesaurus_unit  where   patient_num in ( select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1 ) 
			  and unit_num_out is not null 
			 and unit_num_out=unit_num
	                and  out_date is not null
	                and encounter_num is not null
			 group by out_mode, department_num having count(*)>=$nb_mini ");
	}
	if ($patient_num!='') {
		$req=oci_parse($dbh,"select 
			out_mode,department_num,count(*) nb from dwh_patient_stay,dwh_thesaurus_unit  where   patient_num=$patient_num  and unit_num_out is not null 
			 and unit_num_out=unit_num
	                and  out_date is not null
			 group by out_mode, department_num ");
	}
	oci_execute($req) ;
	while ($res=oci_fetch_array($req,OCI_ASSOC)) {
		$out_mode=$res['OUT_MODE'];
		$department_num=$res['DEPARTMENT_NUM'];
		$nb=$res['NB'];


		$dot_lines.= " \"$out_mode\" [color=\"red\"];\n";
		$tableau_parcours["$department_num -> \"$out_mode\""]=$nb;
		if ($max_nb<$nb) {
			$max_nb=$nb;
		}
		$liste_total_service.="'$department_num',";
		$tableau_service_display[$department_num]='ok';
	}



	if ($tmpresult_num!='') {
		$req=oci_parse($dbh,"select 
	                    a_service.department_num SERVICE_E,
	                    b_service.department_num SERVICE_S,
	                    count(*) as NB  
	        from 
	            dwh_patient_mvt a, dwh_thesaurus_unit a_service,
	            dwh_patient_mvt b , dwh_thesaurus_unit b_service
	        where
	                a.encounter_num=b.encounter_num and  a.unit_code!=b.unit_code and
	                b.entry_date=a.out_date
	                and  a.out_date is not null
	                and  b.out_date is not null
	        and a.unit_num=a_service.unit_num
	        and b.unit_num=b_service.unit_num
	              and exists ( select encounter_num from dwh_tmp_result where tmpresult_num=$tmpresult_num and $patient_num_encounter_num= a.$patient_num_encounter_num and encounter_num is not null)
	                and  exists ( select encounter_num from dwh_tmp_result where tmpresult_num=$tmpresult_num and $patient_num_encounter_num= b.$patient_num_encounter_num and encounter_num is not null) group by   a_service.department_num ,
	                    b_service.department_num  having count(*)>=$nb_mini 
	                     ");
	}
	if ($cohort_num!='') {
		$req=oci_parse($dbh,"select 
	                    a_service.department_num SERVICE_E,
	                    b_service.department_num SERVICE_S,
	                    count(*) as NB  
	        from 
	            dwh_patient_mvt a, dwh_thesaurus_unit a_service,
	            dwh_patient_mvt b , dwh_thesaurus_unit b_service
	        where
	                a.patient_num=b.patient_num  
	                and a.encounter_num=b.encounter_num 
	                and  a.unit_code!=b.unit_code 
	                and	b.entry_date=a.out_date
	                and  a.out_date is not null
	                and  b.out_date is not null
	                and  a.encounter_num is not null
	                and  b.encounter_num is not null
		        and a.unit_num=a_service.unit_num
		        and b.unit_num=b_service.unit_num
	                and a.patient_num in ( select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1 ) 
	                and b.patient_num in ( select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1 )  group by   a_service.department_num ,
	                    b_service.department_num  having count(*)>=$nb_mini 
	                     ");
	}
	if ($patient_num!='') {
		$req=oci_parse($dbh,"select 
	                    a_service.department_num SERVICE_E,
	                    b_service.department_num SERVICE_S,
	                    count(*) as NB  
	        from 
	            dwh_patient_mvt a, dwh_thesaurus_unit a_service,
	            dwh_patient_mvt b , dwh_thesaurus_unit b_service
	        where
	                a.encounter_num=b.encounter_num and  a.unit_code!=b.unit_code and
	                b.entry_date=a.out_date
	                and  a.out_date is not null
	                and  b.out_date is not null
	        and a.unit_num=a_service.unit_num
	        and b.unit_num=b_service.unit_num
	            and    a.patient_num=$patient_num and b.patient_num=$patient_num group by   a_service.department_num ,
	                    b_service.department_num 
	                     ");
	}
	oci_execute($req) ;
	while ($res=oci_fetch_array($req,OCI_ASSOC)) {
		$service_e=$res['SERVICE_E'];
		$service_s=$res['SERVICE_S'];
		$nb=$res['NB'];



		$tableau_parcours["$service_e -> $service_s"]=$nb;
		if ($max_nb<$nb) {
			$max_nb=$nb;
		}
		$tableau_service_display[$service_e]='ok';
		$tableau_service_display[$service_s]='ok';
	}

	$liste_total_service=substr($liste_total_service,0,-1);

	if ($tmpresult_num!='') {
		$req=oci_parse($dbh,"select dwh_thesaurus_unit.department_num ,round(avg(out_date-entry_date),1) as dms from dwh_patient_mvt , dwh_thesaurus_unit where
	                 exists ( select encounter_num from dwh_tmp_result where tmpresult_num=$tmpresult_num and $patient_num_encounter_num=dwh_patient_mvt.$patient_num_encounter_num and encounter_num is not null) 
			and dwh_patient_mvt.unit_num= dwh_thesaurus_unit.unit_num 
	                and out_date>=entry_date
			and out_date is not null group by  dwh_thesaurus_unit.department_num  ");
	}
	if ($cohort_num!='') {
		$req=oci_parse($dbh,"select dwh_thesaurus_unit.department_num ,round(avg(out_date-entry_date),1) as dms from dwh_patient_mvt , dwh_thesaurus_unit where
	                patient_num in ( select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1 ) 
			and dwh_patient_mvt.unit_num= dwh_thesaurus_unit.unit_num 
	                and out_date>=entry_date
	                and encounter_num is not null
			and out_date is not null group by  dwh_thesaurus_unit.department_num  ");
	}
	if ($patient_num!='') {
		$req=oci_parse($dbh,"select dwh_thesaurus_unit.department_num ,round(avg(out_date-entry_date),1) as dms from dwh_patient_mvt , dwh_thesaurus_unit where
	                patient_num=$patient_num
			and dwh_patient_mvt.unit_num= dwh_thesaurus_unit.unit_num 
	                and out_date>=entry_date
			and out_date is not null group by dwh_thesaurus_unit.department_num ");
	}
	oci_execute($req) ;
	while ($res=oci_fetch_array($req,OCI_ASSOC)) {
		$department_num=$res['DEPARTMENT_NUM'];
		$dms=$res['DMS'];
		if ($tableau_service_display[$department_num]=='ok') {
			$department_str=get_department_str ($department_num);
			if (preg_match("/^,/",$dms)) {
				$dms="0".$dms;
			}
			$department_str=str_replace("<"," inf ",$department_str);
			$department_str=str_replace(">"," sup ",$department_str);
			$dot_lines.= "$department_num [label=<$department_str<BR/>DMS : $dms jours>];\n";
			$tableau_service_display_libelle[$department_num]='ok';
		}
	}
	foreach ($tableau_service_display as $department_num => $ok) {
		$department_str=get_department_str ($department_num);
		if (preg_match("/^,/",$dms)) {
			$dms="0".$dms;
		}
		$department_str=str_replace("<"," inf ",$department_str);
		$department_str=str_replace(">"," sup ",$department_str);
		$dot_lines.= "$department_num [label=<$department_str>];\n";
		$tableau_service_display_libelle[$department_num]='ok';
	}
	
	
	foreach ($tableau_parcours as $lien => $poids) {
		$new_poids=round($poids*$max_width/$max_nb,0);
		if ($new_poids==0) {
			$new_poids=1;
		}
		$dot_lines.= "	$lien [weight=1, penwidth=$new_poids, label=\"$poids\",fontcolor=\"red\"] ;\n";
	}
	$num_file=$tmpresult_num.$patient_num;
	
	if (is_file("$CHEMIN_GLOBAL/upload/tmp_graphviz_parcours_sejour_service_$num_file.png")) {
		system("rm $CHEMIN_GLOBAL/upload/tmp_graphviz_parcours_sejour_service_$num_file.png");
	}
	
	if ($dot_lines!='') {
		
		$fichier= "	
		digraph G { 
	        bgcolor=white; 
	        node [shape=box]; 
	         node [ fontname=Arial, fontsize=9];
	         edge [ fontname=Helvetica,  fontsize=9 ];
			     $dot_lines
		}";	
		$inF = fopen("$CHEMIN_GLOBAL/upload/tmp_graphviz_parcours_sejour_service_$num_file.dot","w");
		fputs( $inF,"$fichier");
		fclose($inF);
		
		exec("/usr/bin/dot \"$CHEMIN_GLOBAL/upload/tmp_graphviz_parcours_sejour_service_$num_file.dot\" -Gcharset=latin1 -Tpng -o  \"$CHEMIN_GLOBAL/upload/tmp_graphviz_parcours_sejour_service_$num_file.png\"");
	}
}







function parcours_sejour_service_json ($tmpresult_num,$cohort_num,$patient_num,$patient_num_encounter_num,$nb_mini) {
        global $dbh,$CHEMIN_GLOBAL;
        $max_nb=1;
        $max_width=20;
        $tableau_parcours=array();
        $tableau_noeud_present=array();
        $fichier= "{\"nodes\":[";
        $liste_total_service='';
        $tableau_list_node=array();
        $tableau_node_deja=array();
        $nb_noeud=-1;
        if ($tmpresult_num!='') {
                $req=oci_parse($dbh,"select
			entry_mode,department_num,count(*) nb from dwh_patient_stay,dwh_thesaurus_unit  where  exists ( select encounter_num from dwh_tmp_result where tmpresult_num=$tmpresult_num and $patient_num_encounter_num= dwh_patient_stay.$patient_num_encounter_num and encounter_num is not null)
			 and unit_num_in is not null
			 and unit_num_in=unit_num
	                and  out_date is not null
			 group by entry_mode, department_num having count(*)>=$nb_mini order by count(*) desc ");
        }
        if ($cohort_num!='') {
                $req=oci_parse($dbh,"select
			entry_mode,department_num,count(*) nb from dwh_patient_stay,dwh_thesaurus_unit  where  
	                patient_num in ( select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1 ) 
			 and unit_num_in is not null
			 and unit_num_in=unit_num
	                and  out_date is not null
	                and encounter_num is not null
			 group by entry_mode, department_num having count(*)>=$nb_mini order by count(*) desc ");
        }
        if ($patient_num!='') {
                $req=oci_parse($dbh,"select
			entry_mode,department_num,count(*) nb from dwh_patient_stay,dwh_thesaurus_unit  where patient_num=$patient_num
			 and unit_num_in is not null
			 and unit_num_in=unit_num
	                and  out_date is not null
			 group by entry_mode, department_num ");
        }
        oci_execute($req) ;
        while ($res=oci_fetch_array($req,OCI_ASSOC)) {
                $entry_mode=$res['ENTRY_MODE'];
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
                if ($max_nb<$nb) {
                        $max_nb=$nb;
                }
        }

        if ($tmpresult_num!='') {
                $req=oci_parse($dbh,"select
			out_mode,department_num,count(*) nb from dwh_patient_stay,dwh_thesaurus_unit  where  exists ( select encounter_num from dwh_tmp_result where tmpresult_num=$tmpresult_num and $patient_num_encounter_num= dwh_patient_stay.$patient_num_encounter_num and encounter_num is not null)  and unit_num_out is not null
			 and unit_num_out=unit_num
	                and  out_date is not null
			 group by out_mode, department_num having count(*)>=$nb_mini order by count(*) desc ");
        }
        if ($cohort_num!='') {
                $req=oci_parse($dbh,"select
			out_mode,department_num,count(*) nb from dwh_patient_stay,dwh_thesaurus_unit  where 
	                patient_num in ( select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1 ) 
			  and unit_num_out is not null
			 and unit_num_out=unit_num
	                and  out_date is not null
	                and encounter_num is not null
			 group by out_mode, department_num having count(*)>=$nb_mini order by count(*) desc ");
        }
        if ($patient_num!='') {
                $req=oci_parse($dbh,"select
			out_mode,department_num,count(*) nb from dwh_patient_stay,dwh_thesaurus_unit  where   patient_num=$patient_num  and unit_num_out is not null
			 and unit_num_out=unit_num
	                and  out_date is not null
			 group by out_mode, department_num ");
        }
        oci_execute($req) ;
        while ($res=oci_fetch_array($req,OCI_ASSOC)) {
                $out_mode=$res['OUT_MODE'];
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
                if ($max_nb<$nb) {
                        $max_nb=$nb;
                }
        }


        if ($tmpresult_num!='') {
                $req=oci_parse($dbh,"select
	                    a_service.department_num SERVICE_E,
	                    b_service.department_num SERVICE_S,
	                    count(*) as NB
	        from
	            dwh_patient_mvt a, dwh_thesaurus_unit a_service,
	            dwh_patient_mvt b , dwh_thesaurus_unit b_service
	        where
	                a.encounter_num=b.encounter_num and  a.unit_code!=b.unit_code and
	                b.entry_date=a.out_date
	                and  a.out_date is not null
	                and  b.out_date is not null
	        and a.unit_num=a_service.unit_num
	        and b.unit_num=b_service.unit_num
	              and exists ( select encounter_num from dwh_tmp_result where tmpresult_num=$tmpresult_num and $patient_num_encounter_num= a.$patient_num_encounter_num and encounter_num is not null)
	                and  exists ( select encounter_num from dwh_tmp_result where tmpresult_num=$tmpresult_num and $patient_num_encounter_num= b.$patient_num_encounter_num and encounter_num is not null) group by   a_service.department_num ,
	                    b_service.department_num  having count(*)>=$nb_mini order by count(*) desc
	                     ");
        }
        if ($cohort_num!='') {
                $req=oci_parse($dbh,"select
	                    a_service.department_num SERVICE_E,
	                    b_service.department_num SERVICE_S,
	                    count(*) as NB
	        from
	            dwh_patient_mvt a, dwh_thesaurus_unit a_service,
	            dwh_patient_mvt b , dwh_thesaurus_unit b_service
	        where
	                a.patient_num=b.patient_num  
	                and a.encounter_num=b.encounter_num 
	                and  a.unit_code!=b.unit_code 
	                b.entry_date=a.out_date
	                and  a.out_date is not null
	                and  b.out_date is not null
	                and  a.encounter_num is not null
	                and  b.encounter_num is not null
		        and a.unit_num=a_service.unit_num
		        and b.unit_num=b_service.unit_num
	                and a.patient_num in ( select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1 ) 
	                and b.patient_num in ( select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1 ) 
	                group by   a_service.department_num ,
	                    b_service.department_num  having count(*)>=$nb_mini order by count(*) desc
	                     ");
        }
        if ($patient_num!='') {
                $req=oci_parse($dbh,"select
	                    a_service.department_num SERVICE_E,
	                    b_service.department_num SERVICE_S,
	                    count(*) as NB
	        from
	            dwh_patient_mvt a, dwh_thesaurus_unit a_service,
	            dwh_patient_mvt b , dwh_thesaurus_unit b_service
	        where
	                a.encounter_num=b.encounter_num and  a.unit_code!=b.unit_code and
	                b.entry_date=a.out_date
	                and  a.out_date is not null
	                and  b.out_date is not null
	        and a.unit_num=a_service.unit_num
	        and b.unit_num=b_service.unit_num
	            and    a.patient_num=$patient_num and b.patient_num=$patient_num group by   a_service.department_num ,
	                    b_service.department_num
	                     ");
        }
        oci_execute($req) ;
        while ($res=oci_fetch_array($req,OCI_ASSOC)) {
                $service_e=$res['SERVICE_E'];
                $service_s=$res['SERVICE_S'];
                $nb=$res['NB'];
                $tableau_parcours[$service_e.';'.$service_s]=$nb;
                $tableau_noeud_present[$service_e]='ok';
                $tableau_noeud_present[$service_s]='ok';
                if ($max_nb<$nb) {
                        $max_nb=$nb;
                }
        }


        if ($tmpresult_num!='') {
                $req=oci_parse($dbh,"select dwh_thesaurus_unit.department_num ,round(median(out_date-entry_date),1) as dms from dwh_patient_mvt , dwh_thesaurus_unit where
	                 exists ( select encounter_num from dwh_tmp_result where tmpresult_num=$tmpresult_num and $patient_num_encounter_num= dwh_patient_mvt.$patient_num_encounter_num and encounter_num is not null)
			and dwh_patient_mvt.unit_num= dwh_thesaurus_unit.unit_num
	                and  out_date is not null
			and out_date is not null group by  dwh_thesaurus_unit.department_num  ");
        }
        if ($cohort_num!='') {
                $req=oci_parse($dbh,"select dwh_thesaurus_unit.department_num ,round(median(out_date-entry_date),1) as dms from dwh_patient_mvt , dwh_thesaurus_unit where
	                patient_num in ( select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1 ) 
			and dwh_patient_mvt.unit_num= dwh_thesaurus_unit.unit_num
	                and  out_date is not null
	                and encounter_num is not null
			and out_date is not null group by  dwh_thesaurus_unit.department_num  ");
        }
        if ($patient_num!='') {
                $req=oci_parse($dbh,"select dwh_thesaurus_unit.department_num ,round(median(out_date-entry_date),1) as dms from dwh_patient_mvt , dwh_thesaurus_unit where
	                patient_num=$patient_num
			and dwh_patient_mvt.unit_num= dwh_thesaurus_unit.unit_num
	                and  out_date is not null
			and out_date is not null group by dwh_thesaurus_unit.department_num ");
        }
        oci_execute($req) ;
        while ($res=oci_fetch_array($req,OCI_ASSOC)) {
                $department_num=$res['DEPARTMENT_NUM'];
                $dms=$res['DMS'];
                $req_service=oci_parse($dbh,"select department_str from dwh_thesaurus_department where department_num='$department_num' ");
                oci_execute($req_service) ;
                $r=oci_fetch_array($req_service,OCI_ASSOC);
                $department_str=$r['DEPARTMENT_STR'];
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
        $fichier.= "],
	\"links\":[";

        foreach ($tableau_parcours as $lien => $poids) {
                //$new_poids=round($poids*$max_width/$max_nb,0);
                //if ($new_poids==0) {
                //	$new_poids=1;
                //}
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
        $fichier.= "
		]}";
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
        $num_file=$tmpresult_num.$patient_num.$cohort_num;
        $inF = fopen("$CHEMIN_GLOBAL/upload/tmp_d3_service_json_$num_file.json","w");
        fputs( $inF,"$fichier");
        fclose($inF);



}



function parcours_complet_json($tmpresult_num,$cohort_num,$patient_num_local,$unit_or_department,$nb_mini) {
	global $dbh,$CHEMIN_GLOBAL;
	$max_nb=1;
	$max_width=20;
	$tableau_service_distinct=array();
	$tableau_nb_passage_service_service=array();
	$tableau_delais_total_service_service=array();

	if ($tmpresult_num!='' && $unit_or_department=='department') {
		$req=oci_parse($dbh,"select patient_num,department_num,to_char(date_service,'YYYY-MM-DD') as date_service_char,date_service,to_char(out_date,'YYYY-MM-DD') as date_sortie_char,out_date,source
					from ( select distinct  department_num,entry_date as date_service,patient_num,out_date,'hospit' as source from dwh_patient_mvt where patient_num in (select patient_num from dwh_tmp_result where tmpresult_num=$tmpresult_num)
					union
					select department_num,consultation_date as date_service,patient_num,consultation_date as out_date,'consult' as source  from dwh_patient_consultation where patient_num in (select patient_num from dwh_tmp_result where tmpresult_num=$tmpresult_num)
					) t where department_num is not null and date_service is not null
					order by patient_num,date_service,out_date");
	}
	if ($cohort_num!='' && $unit_or_department=='department') {
		$req=oci_parse($dbh,"select patient_num,department_num,to_char(date_service,'YYYY-MM-DD') as date_service_char,date_service,to_char(out_date,'YYYY-MM-DD') as date_sortie_char,out_date,source
					from ( select distinct  department_num,entry_date as date_service,patient_num,out_date,'hospit' as source from dwh_patient_mvt where patient_num in (select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1)
					union
					select department_num,consultation_date as date_service,patient_num,consultation_date as out_date,'consult' as source  from dwh_patient_consultation where patient_num in (select patient_num from dwh_tmp_result where tmpresult_num=$tmpresult_num)
					) t where department_num is not null and date_service is not null
					order by patient_num,date_service,out_date");
	}

	if ($patient_num_local!='' && $unit_or_department=='department') {
		$req=oci_parse($dbh,"select patient_num,department_num,to_char(date_service,'YYYY-MM-DD') as date_service_char,date_service,to_char(out_date,'YYYY-MM-DD') as date_sortie_char,out_date,source
					from ( select distinct  department_num,entry_date as date_service,patient_num,out_date,'hospit' as source from dwh_patient_mvt where patient_num='$patient_num_local'
					union
					select department_num,consultation_date as date_service,patient_num,consultation_date as out_date,'consult' as source  from dwh_patient_consultation where patient_num='$patient_num_local'
					) t where department_num is not null and date_service is not null
					order by patient_num,date_service,out_date");
	}

        if ($tmpresult_num!='' && $unit_or_department=='unit') {
                $req=oci_parse($dbh,"select patient_num,department_num,to_char(date_service,'YYYY-MM-DD') as date_service_char,date_service,to_char(out_date,'YYYY-MM-DD') as date_sortie_char,out_date,source
					from ( select distinct  unit_num as department_num,entry_date as date_service,patient_num,out_date,'hospit' as source from dwh_patient_mvt where patient_num in (select patient_num from dwh_tmp_result where tmpresult_num=$tmpresult_num)
					union
					select unit_num as department_num,consultation_date as date_service,patient_num,consultation_date as out_date,'consult' as source  from dwh_patient_consultation where patient_num in (select patient_num from dwh_tmp_result where tmpresult_num=$tmpresult_num)

					) t where department_num is not null and date_service is not null
					order by patient_num,date_service,out_date");
        }
        if ($patient_num_local!='' && $unit_or_department=='unit') {
                $req=oci_parse($dbh,"select patient_num,department_num,to_char(date_service,'YYYY-MM-DD') as date_service_char,date_service,to_char(out_date,'YYYY-MM-DD') as date_sortie_char,out_date,source
					from ( select distinct  unit_num as department_num,entry_date as date_service,patient_num,out_date,'hospit' as source from dwh_patient_mvt where patient_num='$patient_num_local'
					union
					select unit_num as department_num,consultation_date as date_service,patient_num,consultation_date as out_date,'consult' as source  from dwh_patient_consultation where patient_num='$patient_num_local'
					) t where department_num is not null and date_service is not null
					order by patient_num,date_service,out_date");
        }
	if ($cohort_num!='' && $unit_or_department=='unit') {
                $req=oci_parse($dbh,"select patient_num,department_num,to_char(date_service,'YYYY-MM-DD') as date_service_char,date_service,to_char(out_date,'YYYY-MM-DD') as date_sortie_char,out_date,source
					from ( select distinct  unit_num as department_num,entry_date as date_service,patient_num,out_date,'hospit' as source from dwh_patient_mvt where patient_num in (select patient_num from dwh_cohort_result where cohort_num=$cohort_num and status=1)
					union
					select unit_num as department_num,consultation_date as date_service,patient_num,consultation_date as out_date,'consult' as source  from dwh_patient_consultation where patient_num in (select patient_num from dwh_tmp_result where tmpresult_num=$tmpresult_num)
					) t where department_num is not null and date_service is not null
					order by patient_num,date_service,out_date");
	}

	oci_execute($req) ;
	while ($res=oci_fetch_array($req,OCI_ASSOC)) {
		$patient_num=$res['PATIENT_NUM'];
		$department_num=$res['SOURCE']."_".$res['DEPARTMENT_NUM'];
		$date_service=$res['DATE_SERVICE_CHAR'];
		$out_date=$res['DATE_SORTIE_CHAR'];
		//$source=$res['SOURCE'];
		if ($patient_num==$patient_num_preced && $department_num_preced!='') {
		
			$d1 = new DateTime("$date_service 12:00:00");
			$d2 = new DateTime("$date_sortie_preced 12:00:00");
			$diff = $d2->diff($d1);
			
			$delais_total = $diff->days;
			$tableau_nb_passage_service_service[$department_num_preced.';'.$department_num]++;
			$tableau_service_distinct[$department_num]++;
			if ($delais_total>0) {
				$tableau_delais_total_service_service[$department_num_preced.';'.$department_num]+=$delais_total;
			} else {
				// si le delais est negatif, c'est qu'il y a des consultation au sein du sejour dans un service... radio, reeduc etc...
				// du coup on calcul le delais à partir de la date d'entree dans le service precedent///
				$d1 = new DateTime("$date_service 12:00:00");
				$d2 = new DateTime("$date_service_preced 12:00:00");
				$diff = $d2->diff($d1);
				$delais_total = $diff->days;
				if ($delais_total>0) {
					$tableau_delais_total_service_service[$department_num_preced.';'.$department_num]+=$delais_total;
				}
				
				// et retour dans le service d'origine
				$tableau_nb_passage_service_service[$department_num.';'.$department_num_preced]++;
				$department_num=$department_num_preced;
				$out_date=$date_sortie_preced;
				$date_service_preced=$date_service; // on repart d'une date de service à la date de la consultation
			}
		}
		if ($patient_num!=$patient_num_preced && $patient_num_preced!='') {
			$department_num_preced='';
			$date_service_preced='';
			$date_sortie_preced='';
		} else {
			$department_num_preced=$department_num;
			$date_service_preced=$date_service;
			$date_sortie_preced=$out_date;
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
	
	
	$nb_noeud=-1;
	$tableau_list_node=array();
	$tableau_node_deja=array();
	$fichier= "{\"nodes\":[";
	foreach ($tableau_service_conserve as $department_num => $ok) {
		$nb_dans_service=$tableau_service_distinct[$department_num];
		
		list($source,$department_num_reel)=explode('_',$department_num);
		if ($unit_or_department == 'department') {
                $sel=oci_parse($dbh,"select department_str from dwh_thesaurus_department where department_num='$department_num_reel' ");
        } else {
                $sel=oci_parse($dbh,"select unit_str as department_str from dwh_thesaurus_unit where unit_num='$department_num_reel' ");
        }

		oci_execute($sel) ;
		$r=oci_fetch_array($sel,OCI_ASSOC);
		$department_str=$r['DEPARTMENT_STR'];
		ocifreestatement($sel);
		$department_str=str_replace("<"," inf ",$department_str);
		$department_str=str_replace(">"," sup ",$department_str);
		if ($tableau_node_deja[$department_num]=='') {
			$nb_noeud++;
			$fichier.= "{\"name\":\"$department_str $source\"},";
			$tableau_list_node[$department_num]=strval($nb_noeud);
			$tableau_node_deja[$department_num]='ok';
			$tableau_libelle_service[$department_num]=$department_str;
		}
	}
	$fichier=substr($fichier,0,-1);
	$fichier.= "],
	\"links\":[";
	
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
	$fichier.= "
		]}";	
	$num_file=$tmpresult_num.$patient_num_local.$cohort_num;
	$inF = fopen("$CHEMIN_GLOBAL/upload/tmp_d3_complet_json_$num_file.json","w");
	fputs( $inF,"$fichier");
	fclose($inF);
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
        	$test='pasfini';
        }
    }
    if($test=='pasfini') {
        return '0'; // process pasfini
    } else {
        return '1'; // process fini
    }
}

function get_department_str ($department_num) {
	global $dbh;
	$department_str='';
	if ($department_num!='') {
		$sel=oci_parse($dbh,"select department_str from dwh_thesaurus_department where department_num=$department_num ");
		oci_execute($sel) ;
		$r=oci_fetch_array($sel,OCI_ASSOC);
		$department_str=$r['DEPARTMENT_STR'];
		$department_str=str_replace("&"," ET ",$department_str);
		ocifreestatement($sel);
	} 
	return $department_str;
}



function modify_hospital_patient_id ($patient_num,$hospital_patient_id_ancien,$hospital_patient_id_nouveau) {
	global $dbh;
	$hospital_patient_id_new_maitre='';
	$patient_num_new_maitre='';
	if ($patient_num!='' && $hospital_patient_id_nouveau!='' && $hospital_patient_id_ancien!='') {
	
		// on recherche l'IPP maitre ///
		$file=file ("http://h61-entrepot.nck.aphp.fr/sites/api_patient/dwh_get_ipp_maitre.php?ipp=$hospital_patient_id_nouveau");
		$master_patient_id=implode('',$file);
		
		if ($master_patient_id=='') {
			$sel=oci_parse($dbh,"select hospital_patient_id from dwh_patient_ipphist  where hospital_patient_id='$hospital_patient_id_nouveau' ");
			oci_execute($sel) ;
			$r=oci_fetch_array($sel,OCI_ASSOC);
			$master_patient_id=$r['HOSPITAL_PATIENT_ID'];
		}
		
		if ($master_patient_id!='') {
			$patient_num_maitre=get_patient_num($master_patient_id);
			$patient_num_nouveau=get_patient_num($hospital_patient_id_nouveau);
			
			
			if ($patient_num_maitre=='') {
				$patient_num_maitre=$patient_num;
				insert_hospital_patient_id ($patient_num,$master_patient_id,'SIH',1);
			}
			
			if ($patient_num_nouveau=='') {
				$patient_num_nouveau=$patient_num_maitre;
				insert_hospital_patient_id($patient_num_maitre,$hospital_patient_id_nouveau,'SIH',0);
			}
			
			if ($patient_num_nouveau!=$patient_num_maitre) {
				
				$liste_table_patient_num=array(
					'DWH_COHORT_RESULT',
					'DWH_COHORT_RESULT_COMMENT',
					'DWH_DATA',
					'DWH_DATAMART_RESULT',
					'DWH_REQUEST_ACCESS_PATIENT',
					'DWH_DOCUMENT',
					'DWH_ENRSEM',
					'DWH_PATIENT_CONSULTATION',
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
				foreach ($liste_table_patient_num as $table_name) {
					$req_dwh="update $table_name set patient_num='$patient_num_maitre' where patient_num='$patient_num_nouveau' ";
					$sel_dwh=oci_parse($dbh,$req_dwh);
					oci_execute($sel_dwh);
				}

				$req_dwh="update dwh_patient_rel set patient_num_1='$patient_num_maitre' where patient_num_1='$patient_num_nouveau' ";
				$sel_dwh=oci_parse($dbh,$req_dwh);
				oci_execute($sel_dwh);
				
				$req_dwh="update dwh_patient_rel set patient_num_2='$patient_num_maitre' where patient_num_2='$patient_num_nouveau' ";
				$sel_dwh=oci_parse($dbh,$req_dwh);
				oci_execute($sel_dwh);
				
				$req_dwh="delete from  dwh_patient where patient_num=$patient_num_nouveau";
				$sel_dwh=oci_parse($dbh,$req_dwh);
				oci_execute($sel_dwh) ;
			}
			if ($patient_num!=$patient_num_maitre) {
				
				$liste_table_patient_num=array(
					'DWH_COHORT_RESULT',
					'DWH_COHORT_RESULT_COMMENT',
					'DWH_DATA',
					'DWH_DATAMART_RESULT',
					'DWH_REQUEST_ACCESS_PATIENT',
					'DWH_DOCUMENT',
					'DWH_ENRSEM',
					'DWH_PATIENT_CONSULTATION',
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
				foreach ($liste_table_patient_num as $table_name) {
					$req_dwh="update $table_name set patient_num='$patient_num_maitre' where patient_num='$patient_num' ";
					$sel_dwh=oci_parse($dbh,$req_dwh);
					oci_execute($sel_dwh);
				}
				$req_dwh="update dwh_patient_rel set patient_num_1='$patient_num_maitre' where patient_num_1='$patient_num' ";
				$sel_dwh=oci_parse($dbh,$req_dwh);
				oci_execute($sel_dwh);
				
				$req_dwh="update dwh_patient_rel set patient_num_2='$patient_num_maitre' where patient_num_2='$patient_num' ";
				$sel_dwh=oci_parse($dbh,$req_dwh);
				oci_execute($sel_dwh);
				
				$req_dwh="delete from  dwh_patient where patient_num=$patient_num ";
				$sel_dwh=oci_parse($dbh,$req_dwh);
				oci_execute($sel_dwh) ;
			}
			
			update_master_patient_id ($patient_num_maitre,$master_patient_id);
			update_patient ($patient_num_maitre);
		} 
	} 
	return $patient_num_maitre;
}

function update_patient ($patient_num) {
	global $dbh;
	$req="select zip_code,to_char(birth_date,'DD/MM/YYYY') as birth_date,residence_latitude,birth_country,patient_num from dwh_patient where patient_num='$patient_num'";
	$sel=oci_parse($dbh,$req);
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC); 
	$datenais_base=$r['BIRTH_DATE'];
	$cp_base=$r['ZIP_CODE'];
	$residence_latitude=$r['RESIDENCE_LATITUDE'];
	$birth_country=$r['BIRTH_COUNTRY'];
	$patient_num=$r['PATIENT_NUM'];
	
	$hospital_patient_id=get_master_patient_id($patient_num);
	
	$file=file ("http://h61-entrepot.nck.aphp.fr/sites/api_patient/dwh_recup_depuis_ipp.php?ipp=$hospital_patient_id");
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
		if ($residence_latitude=='') {
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
	}
}

function enregistrer_coordonnees_residence ($patient_num,$zip_code,$city,$country,$substr='non') {
	global $dbh;
	$cp_origine=$zip_code;
	$city_modified='';
	$country_modified='';
	$latitude='';
	$longitude='';
	$pays_origine_depart=$country;
	list($latitude,$longitude,$country_modified)=trouver_coordonnees ($zip_code,$city,$country);
	$country=str_replace("'","''",$country);
	$country_modified=str_replace("'","''",$country_modified);
	$city_modified=str_replace("'","''",$city);
	$req_patient_num="";
	if ($patient_num!='') {
		$req_patient_num=" and patient_num=$patient_num ";
	}
	if (preg_match("/-?[0-9]+,?[0-9]?/",$longitude) && preg_match("/-?[0-9]+,?[0-9]?/",$latitude)) {
		$coordonnees="SDO_GEOMETRY(2001, 4326, SDO_POINT_TYPE('$longitude','$latitude',NULL), NULL, NULL)";
		if ($substr=='non') {
			if ($country!='' && $country_modified!='') {
				$requete_ville="update dwh_patient set residence_latitude='$latitude',residence_longitude='$longitude' where  zip_code='$cp_origine' and residence_city='$city_modified' and (upper(residence_country)='$country' or  upper(residence_country)='$country_modified') $req_patient_num";
			} else if ($country!='') {
				$requete_ville="update dwh_patient set residence_latitude='$latitude',residence_longitude='$longitude' where zip_code='$cp_origine' and residence_city='$city_modified' and upper(residence_country)='$country' $req_patient_num ";
			} else {
				$requete_ville="update dwh_patient set residence_latitude='$latitude',residence_longitude='$longitude' where zip_code='$cp_origine' and residence_city='$city_modified' and residence_country is null $req_patient_num";
			}
		} else {
			if ($country!='' && $country_modified!='') {
				$requete_ville="update dwh_patient set residence_latitude='$latitude',residence_longitude='$longitude' where  substr(zip_code,1,2)=substr('$cp_origine',1,2) and residence_city='$city_modified' and (upper(residence_country)='$country' or  upper(residence_country)='$country_modified') $req_patient_num";
			} else if ($country!='') {
				$requete_ville="update dwh_patient set residence_latitude='$latitude',residence_longitude='$longitude' where substr(zip_code,1,2)=substr('$cp_origine',1,2) and residence_city='$city_modified' and upper(residence_country)='$country' $req_patient_num ";
			} else {
				$requete_ville="update dwh_patient set residence_latitude='$latitude',residence_longitude='$longitude' where zip_code='$cp_origine' and residence_city='$city_modified' and residence_country is null $req_patient_num";
			}
		}
		$stmt_test_ville=oci_parse($dbh,$requete_ville);
		oci_execute($stmt_test_ville) || die ("erreur : $requete_ville\n");
		if ($country_modified!='') {
			$requete_ville="update dwh_patient set residence_country='$country_modified' where residence_latitude='$latitude' and residence_longitude='$longitude' $req_patient_num";
			$stmt_test_ville=oci_parse($dbh,$requete_ville);
			oci_execute($stmt_test_ville) || die ("erreur : $requete_ville\n");
		}
	} else {
		if ($substr=='non') {
			enregistrer_coordonnees_residence ($patient_num,$zip_code,$city,$pays_origine_depart,'oui');
		}
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
	$requete_cui="select text from dwh_text where  document_num=$document_num and certainty=$certainty and context='$context'";
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
	$requeteins="update dwh_text set enrich_text=:enrich_text where context='$context' and certainty=$certainty and document_num=$document_num";
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
	$req="
		SELECT patient_num,department_num from  dwh_tmp_result
		                          WHERE tmpresult_num = $tmpresult_num and department_num is not null and not exists (select patient_num 
		                           FROM dwh_patient_department
		                          WHERE dwh_patient_department.patient_num=dwh_tmp_result.patient_num and department_num IN (SELECT department_num
		                                          FROM dwh_user_department
		                                         WHERE user_num = $user_num_session))
		union all
		SELECT patient_num,department_num from dwh_patient_department where patient_num in (select patient_num 
		                           FROM dwh_tmp_result
		                          WHERE tmpresult_num = $tmpresult_num and department_num is  null and not exists (select patient_num 
		                           FROM dwh_patient_department
		                          WHERE dwh_patient_department.patient_num=dwh_tmp_result.patient_num and department_num IN (SELECT department_num
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
SELECT patient_num,department_num from  dwh_tmp_result
                          WHERE tmpresult_num = $tmpresult_num and department_num is not null and not exists (select patient_num 
                           FROM dwh_patient_department
                          WHERE dwh_patient_department.patient_num=dwh_tmp_result.patient_num and department_num IN (SELECT department_num
                                          FROM dwh_user_department
                                         WHERE user_num = $user_num_session))
union
SELECT patient_num,department_num from dwh_patient_department where patient_num in (select patient_num 
                           FROM dwh_tmp_result
                          WHERE tmpresult_num = $tmpresult_num and department_num is  null and not exists (select patient_num 
                           FROM dwh_patient_department
                          WHERE dwh_patient_department.patient_num=dwh_tmp_result.patient_num and department_num IN (SELECT department_num
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
		
		
#		$sel_service=oci_parse($dbh,"select distinct patient_num from dwh_tmp_result  where tmpresult_num=$tmpresult_num order by patient_num");
#		oci_execute($sel_service);
#		while ($r_service=oci_fetch_array($sel_service,OCI_RETURN_NULLS+OCI_ASSOC)) {
#			$patient_num=$r_service['PATIENT_NUM'];
#			if ($tableau_patient_service[$department_num][$patient_num]=='ok') {
#				print "<td style=\"background-color:red;width:1px\"></td>";
#			} else {
#				print "<td style=\"background-color:white;width:1px\"></td>";
#			}
#		}
		print "</tr>";
	}
	print "</tbody></table></div><br><br>";
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
			$sel_var1=oci_parse($dbh,$req);
			oci_execute($sel_var1) || die (get_translation('ERROR','erreur')." : ".get_translation('ACCESS_REQUEST_NOT_DELETED',"Demande d'accès non supprimée")."<br>");
		}
		
		// sauve la date pour laquelle le demandeur a vu que l'accord avait été donné
		if ($user_num_request==$user_num_session && $manager_agreement==1 ) {
			$req="update   dwh_request_access set viewed_by_manager_ok_date=sysdate where request_access_num=$request_access_num ";
			$sel_var1=oci_parse($dbh,$req);
			oci_execute($sel_var1) || die (get_translation('ERROR','erreur')." : ".get_translation('ACCESS_REQUEST_NOT_DELETED',"Demande d'accès non supprimée")."<br>");
		}
		if ($user_num_request==$user_num_session && $manager_agreement==-1 ) {
			$req="update dwh_request_access set viewed_by_manager_notok_date=sysdate where request_access_num=$request_access_num ";
			$sel_var1=oci_parse($dbh,$req);
			oci_execute($sel_var1) || die (get_translation('ERROR','erreur')." : ".get_translation('ACCESS_REQUEST_NOT_DELETED',"Demande d'accès non supprimée")."<br>");
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


function save_log_query($user_num,$query_context,$xml_query) {
        global $dbh;
        if ($xml_query!='') {
		$requeteins="insert into dwh_log_query (user_num , log_date , query_context ,xml_query ) values ($user_num,sysdate,'$query_context',:xml_query) ";
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
function get_patient_num ($hospital_patient_id) {
        global $dbh;
        $patient_num='';
        $hospital_patient_id=trim($hospital_patient_id);
	if ($hospital_patient_id!='') {
		$sel=oci_parse($dbh,"select patient_num from dwh_patient_ipphist where hospital_patient_id='$hospital_patient_id'");
	        oci_execute($sel);
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$patient_num=$r['PATIENT_NUM'];
	}
	return $patient_num;
}


function insert_hospital_patient_id ($patient_num,$hospital_patient_id,$origin_patient_id,$master_patient_id) {
        global $dbh;
	if ($hospital_patient_id!='') {
		$sel=oci_parse($dbh,"select hospital_patient_id from  dwh_patient_ipphist where hospital_patient_id='$hospital_patient_id' and patient_num=$patient_num ");
	        oci_execute($sel);
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$test_hospital_patient_id=$r['HOSPITAL_PATIENT_ID'];
		
		if ($test_hospital_patient_id=='') {
			$ins=oci_parse($dbh,"insert into dwh_patient_ipphist (patient_num,hospital_patient_id,master_patient_id,origin_patient_id) values ($patient_num,'$hospital_patient_id',$master_patient_id,'$origin_patient_id')");
		        oci_execute($ins);
		} else {
			if ($master_patient_id==1) {
				$upd=oci_parse($dbh,"update dwh_patient_ipphist set master_patient_id=0 where patient_num=$patient_num ");
			        oci_execute($upd);
				$upd=oci_parse($dbh,"update dwh_patient_ipphist set master_patient_id=1 where hospital_patient_id='$hospital_patient_id' and patient_num=$patient_num ");
			        oci_execute($upd);
			}
		}
	}
}

function update_master_patient_id ($patient_num,$master_patient_id) {
        global $dbh;
	if ($master_patient_id!='') {
		$upd=oci_parse($dbh,"update dwh_patient_ipphist set master_patient_id=0 where patient_num=$patient_num ");
	        oci_execute($upd);
		$upd=oci_parse($dbh,"update dwh_patient_ipphist set master_patient_id=1 where hospital_patient_id='$master_patient_id' and patient_num=$patient_num ");
	        oci_execute($upd);
	}
}



function maj_info_chargement ($document_origin_code) {
	global $dbh,$upload_id;
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
	
	$sel=oci_parse($dbh,"select  dwh_user_department.department_num,department_str from dwh_user_department,dwh_thesaurus_department  where dwh_user_department.department_num=dwh_thesaurus_department.department_num and dwh_user_department.user_num=$user_num");
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$department_str=$r['DEPARTMENT_STR'];
		$department_num=$r['DEPARTMENT_NUM'];
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
function create_process ($process_num,$user_num,$status,$commentary,$result,$process_end_date) {
	global $dbh;
	$commentary=supprimer_apost($commentary);
	$req="insert into dwh_process (process_num,status,user_num,commentary,result,process_end_date) values ('$process_num','$status','$user_num','$commentary',:result,$process_end_date)";
	$ins = ociparse($dbh,$req);
	$rowid = ocinewdescriptor($dbh, OCI_D_LOB);
	ocibindbyname($ins, ":result",$result);
	$execState = ociexecute($ins)||die ("ERreur  $req");
	ocifreestatement($ins);

}



function update_process ($process_num,$status,$commentary,$result) {
	global $dbh;
	$commentary=supprimer_apost($commentary);
	$req="update dwh_process set status='$status',commentary='$commentary',result=:result where process_num='$process_num'";
	$upd = ociparse($dbh,$req);
	$rowid = ocinewdescriptor($dbh, OCI_D_LOB);
	ocibindbyname($upd, ":result",$result);
	$execState = ociexecute($upd)||die ("ERreur  $req");
	ocifreestatement($upd);

}

function get_process ($process_num) {
	global $dbh;
	$req= "select status,commentary,result,user_num,process_num from dwh_process where process_num='$process_num' ";
	$sel=oci_parse($dbh, $req);
	oci_execute($sel) ||die ("ERreur  $req");
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$process_num = $r['PROCESS_NUM'];
	$status = $r['STATUS'];
	$commentary = $r['COMMENTARY'];
	$user_num = $r['USER_NUM'];
	$result='';
	if ($r['RESULT']) {
		$result = $r['RESULT']-> load();
	}
	
	$tableau['PROCESS_NUM']=$process_num;
	$tableau['USER_NUM']=$user_num;
	$tableau['STATUS']=$status;
	$tableau['COMMENTARY']=$commentary;
	$tableau['RESULT']=$result;
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
	
        $sel_var1=oci_parse($dbh,"select dwh_seq.nextval tool_num from dual  ");
        oci_execute($sel_var1);
        $r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC);
        $tool_num=$r['TOOL_NUM'];
        
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
		$sel_var1=oci_parse($dbh,"delete from dwh_tool where tool_num=$tool_num");
		oci_execute($sel_var1);
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
	global $JSON_TRANSLATION_FILE,$CHARSET,$user_num_session;
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
			if ($defaut=='') {
				$translation="[$code]";
			} else {
				$translation=$defaut;
			}
		}
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
			$sel_var1=oci_parse($dbh,"select dwh_seq.nextval as query_num_insert from dual  ");
			oci_execute($sel_var1);
			$r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC);
			$query_num_insert=$r['QUERY_NUM_INSERT'];
			
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
	global $dbh;
	
	$query="select count(*) as counter from dwh_data where thesaurus_data_num='$thesaurus_data_num'
	and exists  (select patient_num from dwh_tmp_result where tmpresult_num=$tmpresult_num and dwh_data.patient_num=dwh_tmp_result.patient_num)";
	$sel = oci_parse($dbh,$query); 
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$counter_concept=$r['COUNTER'];
	return $counter_concept;
		
}
?>
