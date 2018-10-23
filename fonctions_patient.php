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


function parcours_patient ($patient_num) {
	global $dbh,$CHEMIN_GLOBAL,$CHEMIN_GLOBAL_UPLOAD,$URL_UPLOAD;
	
	parcours_sejour_uf ('','',$patient_num,'patient_num',0);
	parcours_sejour_service ('','',$patient_num,'patient_num',0);
	parcours_complet('dot','','',$patient_num,'department',0);
	parcours_complet('dot','','',$patient_num,'unit',0);
	
	if (is_file("$CHEMIN_GLOBAL_UPLOAD/tmp_graphviz_parcours_sejour_service_$patient_num.png")) {
		$parcours_sejour_service='ok';
	}
	if (is_file("$CHEMIN_GLOBAL_UPLOAD/tmp_graphviz_parcours_sejour_uf_$patient_num.png")) {
		$parcours_sejour_uf='ok';
	}
	if (is_file("$CHEMIN_GLOBAL_UPLOAD/tmp_graphviz_parcours_complet_$patient_num"."department0.png")) {
		$parcours_complet_department='ok';
	}
	if (is_file("$CHEMIN_GLOBAL_UPLOAD/tmp_graphviz_parcours_complet_$patient_num"."unit0.png")) {
		$parcours_complet_unit='ok';
	}
	print "
	<script language=javascript>
		function afficher_parcours (id) {";
			if ($parcours_sejour_service=='ok') {print "$('#id_lien_department').css('font-weight','normal');";}
			if ($parcours_sejour_uf=='ok') {print "$('#id_lien_unit').css('font-weight','normal');";}
			if ($parcours_complet_department=='ok') {print "$('#id_lien_complet_department').css('font-weight','normal');";}
			if ($parcours_complet_unit=='ok') {print "$('#id_lien_complet_unit').css('font-weight','normal');";}
			
			print "$('#id_lien_'+id).css('font-weight','bold');";
			
			if ($parcours_sejour_service=='ok') {print "$('#id_div_img_parcours_department').css('display','none');";}
			if ($parcours_sejour_uf=='ok') {print "$('#id_div_img_parcours_unit').css('display','none');";}
			if ($parcours_complet_department=='ok') {print "$('#id_div_img_parcours_complet_department').css('display','none');";}
			if ($parcours_complet_unit=='ok') {print "$('#id_div_img_parcours_complet_unit').css('display','none');";}
			print "$('#id_div_img_parcours_'+id).css('display','block');";
	print "}
	</script>";
	if ($parcours_sejour_service=='ok') {print "- <span id=\"id_lien_department\" style=\"font-weight:bold;\"><a  href=\"#\" onclick=\"afficher_parcours('department');\">".get_translation('DISPLAY_JOURNEY_STAY_BY_HOSPITAL_DEPARTMENT','Afficher les hospitalisations (niveau service)')."</a></span><br>";}
	if ($parcours_sejour_uf=='ok') {print "- <span id=\"id_lien_unit\" style=\"font-weight:normal;\"><a  href=\"#\"  onclick=\"afficher_parcours('unit');\">".get_translation('DISPLAY_JOURNEY_STAY_BY_HOSPITAL_UNIT','Afficher les hospitalisations (niveau unité)')."</a></span><br>";}
	if ($parcours_complet_department=='ok') {print "- <span id=\"id_lien_complet_department\" style=\"font-weight:normal;\"><a  href=\"#\"  onclick=\"afficher_parcours('complet_department');\">".get_translation('DISPLAY_ALL_MOVEMENT_INCLUDING_CONSULT_DEPARTMENT','Afficher tous les passages (niveau service)')."</a></span><br>";}
	if ($parcours_complet_unit=='ok') {print "- <span id=\"id_lien_complet_unit\" style=\"font-weight:normal;\"><a  href=\"#\"  onclick=\"afficher_parcours('complet_unit');\">".get_translation('DISPLAY_ALL_MOVEMENT_INCLUDING_CONSULT_UNIT','Afficher tous les passages (niveau unité)')."</a></span><br>";}
	print "<br> ";
	if ($parcours_sejour_service=='ok') {print "<div id=\"id_div_img_parcours_department\" style=\"display:block\"><img src=\"$URL_UPLOAD/tmp_graphviz_parcours_sejour_service_$patient_num.png\"></div> ";}
	if ($parcours_sejour_uf=='ok') {print "<div id=\"id_div_img_parcours_unit\" style=\"display:none\"><img src=\"$URL_UPLOAD/tmp_graphviz_parcours_sejour_uf_$patient_num.png\"></div> ";}
	if ($parcours_complet_department=='ok') {print "<div id=\"id_div_img_parcours_complet_department\" style=\"display:none\"><img src=\"$URL_UPLOAD/tmp_graphviz_parcours_complet_$patient_num"."department0.png\"></div> ";}
	if ($parcours_complet_unit=='ok') {print "<div id=\"id_div_img_parcours_complet_unit\" style=\"display:none\"><img src=\"$URL_UPLOAD/tmp_graphviz_parcours_complet_$patient_num"."unit0.png\"></div> ";}

}


$tableau_patient_num_deja_traite=array();
$tableau_patient_num_lien_deja_traite=array();



function famille_patient_bis ($patient_num) {
	global $dbh,$tableau_patient_num_deja_traite,$tableau_patient_num_lien_deja_traite;
	global $CHEMIN_GLOBAL;
	global $API_FAMILY_TREE;
	
	$tableau_patient_num_deja_traite=array();
	$tableau_patient_num_lien_deja_traite=array();
	$famille="";
	$test_famille='';
	$req=oci_parse($dbh,"select patient_num_1,patient_num_2,relation from dwh_patient_rel where patient_num_1=$patient_num or patient_num_2=$patient_num");
	oci_execute($req) ;
	while ($res=oci_fetch_array($req,OCI_ASSOC)) {
		$test_famille='ok';
	}
	if ($test_famille!='') {
		
		#/$num_patient|$nom_patient|$firstname_patient|$date_nais_patient|$sex|$relation_parente|$relation_refparente|$description-PATIENT-
		$tab_patient=get_patient ($patient_num);
		if ($tab_patient['BIRTH_DATE']=='') {
			if ($tab_patient['AGE_AN_DECES']=='') {
				$tab_patient['BIRTH_DATE']=$tab_patient['AGE_AN']." ".get_translation('YEARS','Ans');
			} else {
				$tab_patient['BIRTH_DATE']=$tab_patient['AGE_AN_DECES']." ".get_translation('YEARS','Ans');
			}
		}
		$arbre="$patient_num|".$tab_patient['LASTNAME']."|".$tab_patient['FIRSTNAME']."|".$tab_patient['BIRTH_DATE']."|".$tab_patient['SEX']."|".$tab_patient['DEATH_DATE']."|prop||-PATIENT-";
		$arbre.=genealogie_construire_bis ($patient_num);
		
		$famille=join('',file ("$API_FAMILY_TREE?cle_unique=$patient_num&liste_personne=".urlencode($arbre)));
	}
	return $famille;
}



function genealogie_construire_bis ($patient_num) {
	global $tableau_patient_num_deja_traite,$tableau_patient_num_lien_deja_traite;
	if ($tableau_patient_num_deja_traite[$patient_num]=='') {
		$tableau_patient_num_deja_traite[$patient_num]='ok';
		$arbre.=genealogie_trouver_parent_bis($patient_num);
		$arbre.=genealogie_trouver_enfant_bis($patient_num);
	}
	return $arbre;
}

function genealogie_trouver_parent_bis($patient_num) {
	global $dbh, $tableau_patient_num_deja_traite,$tableau_patient_num_lien_deja_traite;
	$arbre='';
	$sel=oci_parse($dbh,"select patient_num_2 from  dwh_patient_rel where patient_num_1='$patient_num' and relation='is_child' ");
        oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$patient_num_2=$r['PATIENT_NUM_2'];
		if ($tableau_patient_num_lien_deja_traite["$patient_num_2;$patient_num"]=='' && $tableau_patient_num_lien_deja_traite["$patient_num;$patient_num_2"]=='') {
			$tableau_patient_num_lien_deja_traite["$patient_num_2;$patient_num"]='ok';
			$tab_patient=get_patient ($patient_num_2);
			if ($tab_patient['BIRTH_DATE']=='') {
				if ($tab_patient['AGE_AN_DECES']=='') {
					$tab_patient['BIRTH_DATE']=$tab_patient['AGE_AN']." ".get_translation('YEARS','Ans');
				} else {
					$tab_patient['BIRTH_DATE']=$tab_patient['AGE_AN_DECES']." ".get_translation('YEARS','Ans');
				}
			}
			if ($tab_patient['SEX']=='F') {
				$rela='m';
			} else {
				$rela='p';
			}
			//$arbre.="$patient_num_2|".$tab_patient['LASTNAME']."|".$tab_patient['FIRSTNAME']."|".$tab_patient['SEX']."|".$tab_patient['BIRTH_DATE']."|$rela|$patient_num|-PATIENT-";
			$arbre.="$patient_num_2|".$tab_patient['LASTNAME']."|".$tab_patient['FIRSTNAME']."|".$tab_patient['BIRTH_DATE']."|".$tab_patient['SEX']."|".$tab_patient['DEATH_DATE']."|$rela|$patient_num|-PATIENT-";
			$arbre.=genealogie_construire_bis ($patient_num_2);
		}
	}
	return $arbre;
}

function genealogie_trouver_enfant_bis ($patient_num) {
	global $dbh, $tableau_patient_num_deja_traite,$tableau_patient_num_lien_deja_traite;
	$arbre='';
	$sel=oci_parse($dbh,"select patient_num_1 from  dwh_patient_rel where patient_num_2='$patient_num' and relation='is_child' ");
        oci_execute($sel);
	while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$patient_num_1=$r['PATIENT_NUM_1'];
		if ($tableau_patient_num_lien_deja_traite["$patient_num;$patient_num_1"]=='' && $tableau_patient_num_lien_deja_traite["$patient_num_1;$patient_num"]=='') {
			$tableau_patient_num_lien_deja_traite["$patient_num;$patient_num_1"]='ok';
			//$arbre.="$patient_num -> $patient_num_1;";
			$tab_patient=get_patient ($patient_num_1);
			if ($tab_patient['BIRTH_DATE']=='') {
				if ($tab_patient['AGE_AN_DECES']=='') {
					$tab_patient['BIRTH_DATE']=$tab_patient['AGE_AN']." ".get_translation('YEARS','Ans');
				} else {
					$tab_patient['BIRTH_DATE']=$tab_patient['AGE_AN_DECES']." ".get_translation('YEARS','Ans');
				}
			}
			if ($tab_patient['SEX']=='F') {
				$rela='fil';
			} else {
				$rela='fi';
			}
			//$arbre.="$patient_num_1|".$tab_patient['LASTNAME']."|".$tab_patient['FIRSTNAME']."|".$tab_patient['SEX']."|".$tab_patient['BIRTH_DATE']."|$rela|$patient_num|-PATIENT-";
			$arbre.="$patient_num_1|".$tab_patient['LASTNAME']."|".$tab_patient['FIRSTNAME']."|".$tab_patient['BIRTH_DATE']."|".$tab_patient['SEX']."|".$tab_patient['DEATH_DATE']."|$rela|$patient_num|-PATIENT-";
			$arbre.=genealogie_construire_bis ($patient_num_1);
		}
	}
	return $arbre;
}

function affiche_tableau_biologie($patient_num) {
	global $dbh,$thesaurus_code_labo;
	
	$i=0;
	$tableau_date_ordre=array();
	$liste_date=array();
	$tableau_year=array();
	$tableau_month=array();
	$tableau_day=array();
	$req=oci_parse($dbh,"select thesaurus_data_num,lower_bound,upper_bound,to_char(document_date,'DD/MM/YYYY HH24:MI') date_document_char, document_date, val_numeric
	,to_char(document_date,'YYYY') year
	,to_char(document_date,'MM') month
	,to_char(document_date,'DD') day
	 from dwh_data where  patient_num=$patient_num and thesaurus_code='$thesaurus_code_labo' and val_numeric is not null order by document_date,thesaurus_data_num");
	oci_execute($req) ;
	while ($res=oci_fetch_array($req,OCI_ASSOC)) {
		$thesaurus_data_num=$res['THESAURUS_DATA_NUM'];
		$borne_inf=str_replace(",",".",$res['LOWER_BOUND']);
		$borne_sup=str_replace(",",".",$res['UPPER_BOUND']);
		$date_document_char=$res['DATE_DOCUMENT_CHAR'];
		$val_numeric=$res['VAL_NUMERIC'];
		$val_numeric_point=str_replace(",",".",$res['VAL_NUMERIC']);
		$year=$res['YEAR'];
		$month=$res['MONTH'];
		$day=$res['DAY'];
		if (preg_match("/^,/",$val_numeric)) {
			$val_numeric="0".$val_numeric;
		}
		if ($liste_date[$date_document_char]=='') {
			$liste_date[$date_document_char]='ok';
			$tableau_date_ordre[$i]=$date_document_char;
			$i++;
			
			$tableau_year[$year]++;
			$tableau_month["$month-$year"]++;
			$tableau_day["$year-$month-$day"]++;
		}
		$color='#ffffff';
		if ($borne_inf!='') {
			if ($val_numeric_point<$borne_inf) {
				$color='#C8D7F2';
			}
		}
		if ($borne_sup!='') {
			if ($val_numeric_point>$borne_sup) {
				$color='#FFAAB0';
			}
		}
		$tableau_date_data_valeur[$date_document_char][$thesaurus_data_num]=$val_numeric;
		$tableau_date_data_color[$date_document_char][$thesaurus_data_num]=$color;
	}
	
	
	
	print "
<table id=\"id_tableau_bilan_biologie\" class=\"tablefin\" style=\"margin:0px;\">";
	print "<thead>";
		print "<tr>";
			print "<th></th>";
			foreach ($tableau_month as $year_month => $colspan) {
				print "<th colspan=\"$colspan\">$year_month</th>";
			}
		print "</tr>";
		print "<tr>";
			print "<th></th>";
			foreach ($tableau_day as $year_month_day => $colspan) {
				$t=explode('-',$year_month_day);
				$day=$t[2];
				print "<th colspan=\"$colspan\">$day</th>";
			}
		print "</tr>";
		print "<tr>";
			print "<th id=\"id_colonne_examen\">".get_translation('EXAMS','Examens')."</th>";
			foreach ($tableau_date_ordre as $date_document_char) {
				$t=explode(' ',$date_document_char);
				$heure=$t[1];
				print "<th>$heure</th>";
			}
		print "</tr>";
	print "</thead>";
	print "<tbody>";
	
	//test la presence de hierarchie dans le thesaurus labo
	$sel_code=oci_parse($dbh,"select count(*) from dwh_thesaurus_data where  thesaurus_parent_num is not null and thesaurus_parent_num!=0  and thesaurus_code='$thesaurus_code_labo'");
	oci_execute($sel_code) ;
	$r_code=oci_fetch_array($sel_code,OCI_ASSOC);
	$test_verif_hierarchy=$r_code[0];

	if ($test_verif_hierarchy>0) {
		$sel_code=oci_parse($dbh,"select concept_str,thesaurus_data_num from dwh_thesaurus_data where thesaurus_data_num in (select thesaurus_parent_num from dwh_thesaurus_data where thesaurus_data_num in (select thesaurus_data_num from dwh_data where patient_num=$patient_num) and thesaurus_code='$thesaurus_code_labo' and thesaurus_parent_num is not null and thesaurus_parent_num!=0 ) order by concept_str ");
		oci_execute($sel_code) ;
		while ($r_code=oci_fetch_array($sel_code,OCI_ASSOC)) {
			$code_libelle_parent=$r_code['CONCEPT_STR'];
			$thesaurus_parent_num=$r_code['THESAURUS_DATA_NUM'];
			
				print "<tr style=\"background-color:black;color:white;\">
					<td nowrap=\"nowrap\" class=\"class_libelle_examen\"><strong>$code_libelle_parent</strong></td>";
				foreach ($tableau_date_ordre as $date_document_char) {
					print "<td></td>";
				}
				print "</tr>";
			$req=oci_parse($dbh,"select thesaurus_data_num,concept_str,info_complement,measuring_unit,value_type from dwh_thesaurus_data where thesaurus_parent_num=$thesaurus_parent_num and thesaurus_data_num in (select thesaurus_data_num from dwh_data where patient_num=$patient_num
			and thesaurus_code='$thesaurus_code_labo' and val_numeric is not null) and thesaurus_code='$thesaurus_code_labo' order by concept_str");
			oci_execute($req) ;
			while ($res=oci_fetch_array($req,OCI_ASSOC)) {
				$thesaurus_data_num=$res['THESAURUS_DATA_NUM'];
				$concept_str=$res['CONCEPT_STR'];
				$info_complement=$res['INFO_COMPLEMENT'];
				$measuring_unit=$res['MEASURING_UNIT'];
				$value_type=$res['VALUE_TYPE'];
				print "<tr>
					<td nowrap=\"nowrap\" class=\"class_libelle_examen\">$concept_str ";
				if ($measuring_unit!='') { 
					print" ($measuring_unit)";
				}
				print " $info_complement</td>";
				foreach ($tableau_date_ordre as $date_document_char) {
					print "<td bgcolor=\"".$tableau_date_data_color[$date_document_char][$thesaurus_data_num]."\">".$tableau_date_data_valeur[$date_document_char][$thesaurus_data_num]."</td>";
				}
				print "</tr>";
			}
		}
	} else {
		$req=oci_parse($dbh,"select thesaurus_data_num,concept_str,info_complement,measuring_unit,value_type from dwh_thesaurus_data where thesaurus_data_num in (select thesaurus_data_num from dwh_data where patient_num=$patient_num
		and thesaurus_code='$thesaurus_code_labo' and val_numeric is not null) and thesaurus_code='$thesaurus_code_labo' order by concept_str");
		oci_execute($req) ;
		while ($res=oci_fetch_array($req,OCI_ASSOC)) {
			$thesaurus_data_num=$res['THESAURUS_DATA_NUM'];
			$concept_str=$res['CONCEPT_STR'];
			$info_complement=$res['INFO_COMPLEMENT'];
			$measuring_unit=$res['MEASURING_UNIT'];
			$value_type=$res['VALUE_TYPE'];
			print "<tr>
				<td nowrap=\"nowrap\" class=\"class_libelle_examen\">$concept_str ";
			if ($measuring_unit!='') { 
				print" ($measuring_unit)";
			}
			print " $info_complement</td>";
			foreach ($tableau_date_ordre as $date_document_char) {
				print "<td bgcolor=\"".$tableau_date_data_color[$date_document_char][$thesaurus_data_num]."\">".$tableau_date_data_valeur[$date_document_char][$thesaurus_data_num]."</td>";
			}
			print "</tr>";
		}
	}
	print "</tbody>";
	print "</table>";
	
}

function affiche_liste_code_biologie($patient_num) {
	global $dbh,$thesaurus_code_labo;
	
	print "<table id=\"id_tableau_liste_code_biologie\" class=\"tablefin\">";
	$sel_code=oci_parse($dbh,"select concept_str,thesaurus_data_num from dwh_thesaurus_data where thesaurus_data_num in (select thesaurus_parent_num from dwh_thesaurus_data where thesaurus_data_num in (select thesaurus_data_num from dwh_data where patient_num=$patient_num) and thesaurus_code='$thesaurus_code_labo' and thesaurus_parent_num is not null) order by concept_str ");
	oci_execute($sel_code) ;
	while ($r_code=oci_fetch_array($sel_code,OCI_ASSOC)) {
		$code_libelle_parent=$r_code['CONCEPT_STR'];
		$thesaurus_parent_num=$r_code['THESAURUS_DATA_NUM'];
		
			print "<tr>
				<td><strong>$code_libelle_parent</strong></td>
				</tr>";
		$req=oci_parse($dbh,"select concept_str,info_complement,measuring_unit,value_type,thesaurus_parent_num from dwh_thesaurus_data where thesaurus_parent_num=$thesaurus_parent_num and thesaurus_data_num in (select thesaurus_data_num from dwh_data where patient_num=$patient_num) and thesaurus_code='$thesaurus_code_labo' order by concept_str");
		oci_execute($req) ;
		while ($res=oci_fetch_array($req,OCI_ASSOC)) {
			$concept_str=$res['CONCEPT_STR'];
			$info_complement=$res['INFO_COMPLEMENT'];
			$measuring_unit=$res['MEASURING_UNIT'];
			$value_type=$res['VALUE_TYPE'];
			$thesaurus_parent_num=$res['THESAURUS_PARENT_NUM'];
			print "<tr>
				<td>$concept_str ($measuring_unit) $info_complement</td>
			</tr>";
		}
	}
	print "</table>";
	
}

?>