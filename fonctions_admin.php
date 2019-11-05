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

function get_list_user_profil() {
	global $dbh;
	$table_user_profil=array();
	$sel_profile=oci_parse($dbh,"select distinct user_profile from dwh_profile_right order by user_profile ");
	oci_execute($sel_profile);
	while ($r=oci_fetch_array($sel_profile,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$table_user_profil[]=$r['USER_PROFILE'];
		
	}
	return $table_user_profil;
}
						

function count_departement($department_num) {
        global $dbh;
        
	$tab_count_departement=array();

	$sel=oci_parse($dbh,"select  count(*) nb from dwh_document where department_num=$department_num");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$tab_count_departement['nb_document']['total']=$r['NB'];
	
	$sel=oci_parse($dbh,"select  count(*) nb from dwh_patient_mvt where department_num=$department_num");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$tab_count_departement['nb_mvt']['total']=$r['NB'];
	
	$sel=oci_parse($dbh,"select  unit_num,count(*) nb from dwh_document where department_num=$department_num  and unit_num is not null group by unit_num");
	oci_execute($sel);
	while($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		if ($r['UNIT_NUM']!='') {
			$tab_count_departement['nb_document'][$r['UNIT_NUM']]=$r['NB'];
		}
	}
	
	$sel=oci_parse($dbh,"select  unit_num,count(*) nb from dwh_patient_mvt where department_num=$department_num  and unit_num is not null  group by unit_num");
	oci_execute($sel);
	while($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		if ($r['UNIT_NUM']!='') {
			$tab_count_departement['nb_document'][$r['UNIT_NUM']]=$r['NB'];
		}
	}
	return $tab_count_departement;
}

			
function count_departement_and_unit() {
        global $dbh;
        
	$tab_count_departement=array();
	
	$sel=oci_parse($dbh,"select  department_num,count(*) nb from dwh_patient_department where department_num is not null group by department_num");
	oci_execute($sel);
	while($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		if ($r['DEPARTMENT_NUM']!='') {
			$tab_count_departement['nb_patient'][$r['DEPARTMENT_NUM']]=$r['NB'];
		}
	}
	
	$sel=oci_parse($dbh,"select  department_num,count(*) nb from dwh_document where department_num is not null group by department_num");
	oci_execute($sel);
	while($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		if ($r['DEPARTMENT_NUM']!='') {
			$tab_count_departement['nb_document'][$r['DEPARTMENT_NUM']]=$r['NB'];
		}
	}
	
	$sel=oci_parse($dbh,"select  department_num,count(*) nb from dwh_patient_mvt where department_num is not null group by department_num");
	oci_execute($sel);
	while($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		if ($r['DEPARTMENT_NUM']!='') {
			$tab_count_departement['nb_mvt'][$r['DEPARTMENT_NUM']]=$r['NB'];
		}
	}
	
	$sel=oci_parse($dbh,"select  unit_num,count(*) nb from dwh_document where unit_num is not null group by unit_num");
	oci_execute($sel);
	while($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		if ($r['UNIT_NUM']!='') {
			$tab_count_departement['nb_document'][$r['UNIT_NUM']]=$r['NB'];
		}
	}
	
	$sel=oci_parse($dbh,"select  unit_num,count(*) nb from dwh_patient_mvt where unit_num is not null  group by unit_num");
	oci_execute($sel);
	while($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		if ($r['UNIT_NUM']!='') {
			$tab_count_departement['nb_mvt'][$r['UNIT_NUM']]=$r['NB'];
		}
	}
	return $tab_count_departement;
}

			

function display_department($department_num,$department_str,$department_code,$department_master) {
        global $dbh,$login_session,$user_num_session,$table_count_departement_and_unit;
        
        $verif_manager_department=check_user_as_department_manager ($department_num,$user_num_session);
       // $tab_count_departement=count_departement($department_num);
        
        $nb_doc_total=$table_count_departement_and_unit['nb_document'][$department_num];
        $nb_mvt_total=$table_count_departement_and_unit['nb_mvt'][$department_num];
        $nb_patient_total=$table_count_departement_and_unit['nb_patient'][$department_num];
        
        if ($department_master==1) {
        	$check_department_master='checked';
        } else {
        	$check_department_master='';
        }
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
                        ".get_translation('MASTER_DEPARTMENT',"Departement maitre")." <input onclick=\"set_department_master('$department_code','$department_num');\" type=\"checkbox\" value=\"1\" id=\"id_checbox_".$department_code."_".$department_num."\" $check_department_master>
                ";
        } else {
                print "<strong>$department_str</strong>";
        }
        print "</td>";
        print "<td valign=\"top\">$nb_patient_total</td>";
        print "<td valign=\"top\">$nb_doc_total</td>";
        print "<td valign=\"top\">$nb_mvt_total</td>";
        
        print "<td valign=\"top\" width=\"482\">";
        
        /////////////////// LES UF 
        print "<a href=\"#\" onclick=\"plier_deplier('id_div_uf_$department_num');return false;\" class=\"admin_lien\"><span id=\"plus_id_div_uf_$department_num\">+</span> ".get_translation('DISPLAY_UNITS','Afficher les unités')."</a>
        <div id=\"id_div_uf_$department_num\" style=\"display:none\">";
        
        print "<table border=\"0\" id=\"id_tableau_uf_$department_num\" width=\"100%\" class=\"noborder\">";
        $req_uf="select dwh_thesaurus_unit.unit_num, unit_str ,unit_code,to_char(unit_start_date,'DD/MM/YYYY') as unit_start_date,to_char(unit_end_date,'DD/MM/YYYY') as unit_end_date
        from dwh_thesaurus_unit,dwh_thesaurus_department 
        where dwh_thesaurus_unit.department_num=dwh_thesaurus_department.department_num and dwh_thesaurus_department.department_num=$department_num order by unit_str ";
        $sel_uf = oci_parse($dbh,$req_uf);
        oci_execute($sel_uf);
        while ($ligne_uf = oci_fetch_array($sel_uf)) {
                $unit_num = $ligne_uf['UNIT_NUM'];
                $unit_str = $ligne_uf['UNIT_STR'];
                $unit_code = $ligne_uf['UNIT_CODE'];
                $unit_start_date = $ligne_uf['UNIT_START_DATE'];
                $unit_end_date = $ligne_uf['UNIT_END_DATE'];
                
	        $nb_doc=$table_count_departement_and_unit['nb_document'][$unit_num];
	        $nb_mvt=$table_count_departement_and_unit['nb_mvt'][$unit_num];
	        
                print "<tr id=\"id_tr_uf_".$department_num."_".$unit_num."\" onmouseover=\"this.style.backgroundColor='#B9C2C8';\" onmouseout=\"this.style.backgroundColor='#ffffff';\" class=\"admin_texte\">
                        <td>$unit_code ".ucfirst(strtolower($unit_str))." </td><td>$nb_doc docs</td><td>$nb_mvt mvt</td><td> $unit_start_date</td><td>$unit_end_date</td>";
                
                if ($_SESSION['dwh_droit_admin']=='ok') {
                        print "<td><a onclick=\"supprimer_uf('$unit_num','$department_num');return false;\" href=\"#\" class=\"admin_lien\">X</a></td>";
                } else {
                        print "<td></td>";
                }
                print "</tr>";
        }
        print "</table></div><br>";
        
        
        if ($_SESSION['dwh_droit_admin']=='ok') {
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
        /*
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
        */
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


						
function get_line_profile_admin ($user_profile,$option) {
	global $dbh,$tableau_user_droit,$tableau_patient_droit;
	if ($option=='global_features') {
		$line= "<tr id=\"id_tr_global_features_$user_profile\" class=\"over_color\"><td>$user_profile</td>";
		
		$sel=oci_parse($dbh," select count(distinct user_num) as NB from dwh_user_profile  where user_profile='$user_profile'
               and user_num in (select user_num from dwh_user where expiration_date is null or expiration_date>sysdate)");
		oci_execute($sel);
		$res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$nb_user=$res['NB'];
		$line.= "<td>$nb_user</td>";
		foreach ($tableau_user_droit as $right) { 
			$sel=oci_parse($dbh,"select count(*) as NB from dwh_profile_right where user_profile='$user_profile' and right='$right'");
			oci_execute($sel);
			$res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
			$nb=$res['NB'];
			if ($nb>0) {
				$check='checked';
				$bgcolor='#ffccff';
			} else {
				$check='';
				$bgcolor='#ffffff';
			}
			$line.= "<td id=\"id_td_".$user_profile."_".$right."\" style=\"background-color:$bgcolor;\"><input type=checkbox id=\"id_checkbox_".$user_profile."_".$right."\" onclick=\"modifier_droit_profil('$user_profile','$right');\" $check></td>";
		}
		$line.= "<td><a href=\"#\" onclick=\"supprimer_profil('$user_profile');return false;\"><img src=\"images/poubelle_moyenne.png\" border=\"0\" width=\"15px\"></a></td>";
		$line.= "<td>$user_profile</td>";
		$line.= "</tr>";
	}
	if ($option=='patient_features') {
		$line= "<tr id=\"id_tr_patient_features_$user_profile\" class=\"over_color\"><td>$user_profile</td>";
		
		foreach ($tableau_patient_droit as $patient_features) { 
			$sel=oci_parse($dbh,"select count(*) as NB from dwh_profile_right where user_profile='$user_profile' and right='$patient_features'");
			oci_execute($sel);
			$res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
			$nb=$res['NB'];
			if ($nb>0) {
				$check='checked';
				$bgcolor='#ffccff';
			} else {
				$check='';
				$bgcolor='#ffffff';
			}
			$line.= "<td id=\"id_td_".$user_profile."_".$patient_features."\" style=\"background-color:$bgcolor;\"><input type=checkbox id=\"id_checkbox_".$user_profile."_".$patient_features."\" onclick=\"modifier_droit_profil('$user_profile','$patient_features');\" $check></td>";
		}
		$line.= "<td>$user_profile</td>";
		$line.= "</tr>";
	
	}
	if ($option=='document_origin_code') {
	
		$sel=oci_parse($dbh,"select  distinct document_origin_code from dwh_info_load where document_origin_code is not null order by document_origin_code");
		oci_execute($sel);
		while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$document_origin_code=$r['DOCUMENT_ORIGIN_CODE'];
			$tableau_document_origin_code[$document_origin_code]=$document_origin_code;
		}
		
		$line= "<tr id=\"id_tr_document_origin_code_$user_profile\" class=\"over_color\"><td>$user_profile</td>";
		
		$sel=oci_parse($dbh,"select count(*) as NB from dwh_profile_document_origin where user_profile='$user_profile' and document_origin_code='tout'");
		oci_execute($sel);
		$res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$nb=$res['NB'];
		if ($nb>0) {
			$check='checked';
			$bgcolor='#ffccff';
		} else {
			$check='';
			$bgcolor='#ffffff';
		}
		$line.= "<td id=\"id_td_".$user_profile."_tout\"  style=\"background-color:$bgcolor;\"><input type=checkbox id=\"id_checkbox_document_origin_code_".$user_profile."_tout\" onclick=\"modifier_droit_profil_document_origin_code('$user_profile','tout','tout');\" $check></td>";
		foreach ($tableau_document_origin_code as $document_origin_code) { 
			$sel=oci_parse($dbh,"select count(*) as NB from dwh_profile_document_origin where user_profile='$user_profile' and document_origin_code='$document_origin_code'");
			oci_execute($sel);
			$res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
			$nb=$res['NB'];
			if ($nb>0) {
				$check='checked';
				$bgcolor='#ffccff';
			} else {
				$check='';
				$bgcolor='#ffffff';
			}
			$id_document_origin_code=preg_replace("/[^a-z]/i","_",$document_origin_code);
			$line.= "<td id=\"id_td_".$user_profile."_".$id_document_origin_code."\" style=\"background-color:$bgcolor;\"><input type=checkbox id=\"id_checkbox_document_origin_code_".$user_profile."_".$id_document_origin_code."\" onclick=\"modifier_droit_profil_document_origin_code('$user_profile','$document_origin_code','$id_document_origin_code');\" $check></td>";
		}
		$line.= "<td>$user_profile</td>";
		$line.= "</tr>";
	}
	
	return $line;
}



function get_list_patients_opposed () {
	global $dbh;
	$tableau_result=array();
	$sel=oci_parse($dbh,"select patient_num, hospital_patient_id , origin_patient_id , patient_num, opposition_date , to_char(opposition_date,'DD/MM/YYYY HH24:MI') as  opposition_date_char from DWH_PATIENT_OPPOSITION  order by opposition_date desc");
	oci_execute($sel);
	while ($res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$patient_num=$res['PATIENT_NUM'];
		$hospital_patient_id=$res['HOSPITAL_PATIENT_ID'];
		$origin_patient_id=$res['ORIGIN_PATIENT_ID'];
		$opposition_date_char=$res['OPPOSITION_DATE_CHAR'];
		$tableau_result[]=array('patient_num'=>$patient_num,'hospital_patient_id'=>$hospital_patient_id,'origin_patient_id'=>$origin_patient_id,'opposition_date_char'=>$opposition_date_char);
	}
	return $tableau_result;
}
function cancel_opposition($patient_num) {
	global $dbh_etl;
	$req="delete from DWH_PATIENT_OPPOSITION where patient_num=$patient_num  ";
	$sel=oci_parse($dbh_etl,$req);
	oci_execute($sel) || die ("erreur req $req\n");
}

function validate_opposition($patient_num) {
	global $dbh_etl;
	$req="insert into DWH_PATIENT_OPPOSITION (hospital_patient_id , origin_patient_id , patient_num, opposition_date) select hospital_patient_id , origin_patient_id , patient_num,sysdate from dwh_patient_ipphist where patient_num=$patient_num ";
	$sel=oci_parse($dbh_etl,$req);
	oci_execute($sel) || die ("erreur req $req\n");
	    
	$list_table_to_delete=array('DWH_COHORT_RESULT',
					'DWH_COHORT_RESULT_COMMENT',
					'DWH_DATAMART_RESULT',
					'DWH_ECRF_ANSWER',
					'DWH_LOG_PATIENT',
					'DWH_LOG_QUERY',
					'DWH_PATIENT_STAT',
					'DWH_PROCESS_PATIENT',
					'DWH_QUERY_RESULT',
					'DWH_REQUEST_ACCESS_PATIENT');
	foreach ($list_table_to_delete as $table_name) {
	    $req="delete from $table_name where patient_num=$patient_num ";
	    //$sel=oci_parse($dbh,$req);
	   //oci_execute($sel) || die ("erreur req $req\n");
	}
	
/*	TABLE WITH DATA IMPORTED : 

Creation of view : create view dwh_patient_view as select * from dwh_patient where patient_num not in (select patient_num from dwh_patient_opposition) ?
DWH_PATIENT
DWH_PATIENT_IPPHIST
DWH_PATIENT_REL
DWH_PATIENT_MVT
DWH_PATIENT_STAY
DWH_DOCUMENT
DWH_TEXT
DWH_DATA
DWH_ENRSEM
DWH_FILE
DWH_PATIENT_DEPARTMENT
*/
}

function get_list_cgu () {
	global $dbh;
	$tableau_result=array();
	$sel=oci_parse($dbh," select cgu_num, cgu_text ,to_char(cgu_date,'DD/MM/YYYY') as char_cgu_date, cgu_date , published,to_char(published_date,'DD/MM/YYYY') as char_published_date,to_char(unpublished_date,'DD/MM/YYYY') as char_unpublished_date  from DWH_ADMIN_CGU order by cgu_date desc");
	oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$cgu_num=$r['CGU_NUM'];
		if ($r['CGU_TEXT']!='') {
			$cgu_text=$r['CGU_TEXT']->load();
		}
		$char_cgu_date=$r['CHAR_CGU_DATE'];
		$published=$r['PUBLISHED'];
		$published_date=$r['CHAR_PUBLISHED_DATE'];
		$unpublished_date=$r['CHAR_UNPUBLISHED_DATE'];
		$tableau_result[]=array('cgu_num'=>$cgu_num,'cgu_text'=>$cgu_text,'cgu_date'=>$char_cgu_date,'published'=>$published,'published_date'=>$published_date,'unpublished_date'=>$unpublished_date);
	}
	return $tableau_result;

}




function get_last_cgu () {
	global $dbh;
	$sel=oci_parse($dbh," select cgu_num, cgu_text ,to_char(cgu_date,'DD/MM/YYYY') as char_cgu_date, cgu_date , published,to_char(published_date,'DD/MM/YYYY') as char_published_date,to_char(unpublished_date,'DD/MM/YYYY') as char_unpublished_date 
	 from DWH_ADMIN_CGU order by cgu_date desc");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$cgu_num=$r['CGU_NUM'];
	if ($r['CGU_TEXT']!='') {
		$cgu_text=$r['CGU_TEXT']->load();
	}
	$char_cgu_date=$r['CHAR_CGU_DATE'];
	$published=$r['PUBLISHED'];
	$published_date=$r['CHAR_PUBLISHED_DATE'];
	$unpublished_date=$r['CHAR_UNPUBLISHED_DATE'];
	$tableau_result=array('cgu_num'=>$cgu_num,'cgu_text'=>$cgu_text,'cgu_date'=>$char_cgu_date,'published'=>$published,'published_date'=>$published_date,'unpublished_date'=>$unpublished_date);
	return $tableau_result;
}

function get_list_cgu_user ($cgu_num) {
	global $dbh;
	$tableau_result=array();
	$sel=oci_parse($dbh,"select user_num,to_char(date_signature,'DD/MM/YYYY') as char_date_signature,date_signature from DWH_ADMIN_CGU_user where cgu_num=$cgu_num order by date_signature desc");
	oci_execute($sel);
	while ($res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$user_num=$res['USER_NUM'];
		$date_signature=$res['CHAR_DATE_SIGNATURE'];
		$tableau_result[]=array('user_num'=>$user_num,'date_signature'=>$date_signature);
	}
	return $tableau_result;

}

function get_nb_cgu_user ($cgu_num) {
	global $dbh;
	
	$sel=oci_parse($dbh,"select count(*) as nb  from DWH_ADMIN_CGU_user where cgu_num=$cgu_num ");
	oci_execute($sel);
	$res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb=$res['NB'];
	return $nb;

}

function insert_cgu ($cgu_text) {
	global $dbh;
	if ($cgu_text!='') {
		$cgu_num=get_uniqid();
                $requeteins="insert into dwh_admin_cgu  (cgu_num, cgu_text ,cgu_date , published ) values ('$cgu_num', :cgu_text ,sysdate , 0)";
                $stmt = ociparse($dbh,$requeteins);
                $rowid = ocinewdescriptor($dbh, OCI_D_LOB);
                ocibindbyname($stmt, ":cgu_text",$cgu_text);
                $execState = ociexecute($stmt);
                ocifreestatement($stmt);
	}
}


function delete_cgu ($cgu_num) {
	global $dbh;
	if ($cgu_num!='') {
	        $req="delete from dwh_admin_cgu where cgu_num=$cgu_num and published=0"; // delete cascade on dwh_admin_cgu_user
		$del=oci_parse($dbh,$req);
		oci_execute($del);
		
	       
	}
}


function update_cgu ($cgu_num,$published) {
	global $dbh;
	if ($cgu_num!='') {
		if ($published==1) {
	                $req="update dwh_admin_cgu set published=1,published_date=sysdate where cgu_num=$cgu_num ";
	  		$del=oci_parse($dbh,$req);
			oci_execute($del);
		} else {
	                $req="update dwh_admin_cgu set published=0,unpublished_date=sysdate where cgu_num=$cgu_num ";
	  		$del=oci_parse($dbh,$req);
			oci_execute($del);
		}
	}
}

function delete_cgu_user ($cgu_num) {
	global $dbh;
	
	$sel=oci_parse($dbh,"select count(*) as nb  from DWH_ADMIN_CGU_user where cgu_num=$cgu_num ");
	oci_execute($sel);
	$res=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$nb=$res['NB'];
	return $nb;

}




function insert_actu ($actu_text) {
	global $dbh;
	if ($actu_text!='') {
		$actu_num=get_uniqid();
                $requeteins="insert into dwh_admin_actu  (actu_num, actu_text ,actu_date , published ,alert) values ('$actu_num', :actu_text ,sysdate , 0 ,0)";
                $stmt = ociparse($dbh,$requeteins);
                $rowid = ocinewdescriptor($dbh, OCI_D_LOB);
                ocibindbyname($stmt, ":actu_text",$actu_text);
                $execState = ociexecute($stmt);
                ocifreestatement($stmt);
	}
}


function modify_actu ($actu_num,$actu_text) {
	global $dbh;
	if ($actu_num!='') {
                $requeteins="update  dwh_admin_actu set actu_text=:actu_text where actu_num=$actu_num"; 
                $stmt = ociparse($dbh,$requeteins);
                $rowid = ocinewdescriptor($dbh, OCI_D_LOB);
                ocibindbyname($stmt, ":actu_text",$actu_text);
                $execState = ociexecute($stmt);
                ocifreestatement($stmt);
	}
}


function delete_actu ($actu_num) {
	global $dbh;
	if ($actu_num!='') {
	        $req="delete from dwh_admin_actu where actu_num=$actu_num"; 
		$del=oci_parse($dbh,$req);
		oci_execute($del);
		
	       
	}
}


function update_actu ($actu_num,$published) {
	global $dbh;
	if ($actu_num!='') {
		if ($published==1) {
	                $req="update dwh_admin_actu set published=1,published_date=sysdate where actu_num=$actu_num ";
	  		$del=oci_parse($dbh,$req);
			oci_execute($del);
		} else {
	                $req="update dwh_admin_actu set published=0 where actu_num=$actu_num ";
	  		$del=oci_parse($dbh,$req);
			oci_execute($del);
		}
	}
}


function update_actu_alert ($actu_num,$alert) {
	global $dbh;
	if ($actu_num!='') {
                $req="update dwh_admin_actu set alert='$alert' where actu_num=$actu_num ";
  		$del=oci_parse($dbh,$req);
		oci_execute($del);
	}
}

?>