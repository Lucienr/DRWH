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
	$req=oci_parse($dbh,"select thesaurus_data_num,lower_bound,upper_bound,to_char(document_date,'DD/MM/YYYY HH24:MI') document_date_char, document_date, val_numeric
	,to_char(document_date,'YYYY') year
	,to_char(document_date,'MM') month
	,to_char(document_date,'DD') day
	 from dwh_data where  patient_num=$patient_num and thesaurus_code='$thesaurus_code_labo' and val_numeric is not null order by document_date,thesaurus_data_num");
	oci_execute($req) ;
	while ($res=oci_fetch_array($req,OCI_ASSOC)) {
		$thesaurus_data_num=$res['THESAURUS_DATA_NUM'];
		$borne_inf=str_replace(",",".",$res['LOWER_BOUND']);
		$borne_sup=str_replace(",",".",$res['UPPER_BOUND']);
		$document_date_char=$res['DOCUMENT_DATE_CHAR'];
		$val_numeric=$res['VAL_NUMERIC'];
		$val_numeric_point=str_replace(",",".",$res['VAL_NUMERIC']);
		$year=$res['YEAR'];
		$month=$res['MONTH'];
		$day=$res['DAY'];
		if (preg_match("/^,/",$val_numeric)) {
			$val_numeric="0".$val_numeric;
		}
		if ($liste_date[$document_date_char]=='') {
			$liste_date[$document_date_char]='ok';
			$tableau_date_ordre[$i]=$document_date_char;
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
		$tableau_date_data_valeur[$document_date_char][$thesaurus_data_num]=$val_numeric;
		$tableau_date_data_color[$document_date_char][$thesaurus_data_num]=$color;
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
			foreach ($tableau_date_ordre as $document_date_char) {
				$t=explode(' ',$document_date_char);
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
				foreach ($tableau_date_ordre as $document_date_char) {
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
				foreach ($tableau_date_ordre as $document_date_char) {
					print "<td bgcolor=\"".$tableau_date_data_color[$document_date_char][$thesaurus_data_num]."\">".$tableau_date_data_valeur[$document_date_char][$thesaurus_data_num]."</td>";
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
			foreach ($tableau_date_ordre as $document_date_char) {
				print "<td bgcolor=\"".$tableau_date_data_color[$document_date_char][$thesaurus_data_num]."\">".$tableau_date_data_valeur[$document_date_char][$thesaurus_data_num]."</td>";
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

function get_query_patient_user($patient_num,$user_num) {
	global $dbh;
	$tableau_query=array();
	if ($patient_num!='') {
		$req_patient=" and patient_num=$patient_num";
	} else {
		$req_patient="";
	}
	$sel=oci_parse($dbh,"select  xml_query,log_date from dwh_log_query where query_context='patient' and user_num=$user_num $req_patient and xml_query is not null order by log_date desc");
	oci_execute($sel) ;
	while ($r=oci_fetch_array($sel,OCI_ASSOC)) {
		$tableau_query[$r['XML_QUERY']-> load()]=1;
	}
	return $tableau_query;	
}


function display_pmsi_patient ($patient_num) {
	global $dbh,$thesaurus_code_pmsi;
	
        $nb_encounter_num=0;
        $encounter_num_before='';
        $color_odd_encounter_num='#dde9f0';
        $color_even_encounter_num='#ede6ed';
	
	$tab_encounter=get_encounter_info_by_patient ($patient_num,'desc') ;
	foreach ($tab_encounter as $encounter) {
		$encounter_num=$encounter['ENCOUNTER_NUM'];
		$entry_mode=$encounter['ENTRY_MODE'];
		$out_mode=$encounter['OUT_MODE'];
		$entry_date=$encounter['ENTRY_DATE_DMY'];
		$out_date=$encounter['OUT_DATE_DMY'];
		
		if ($encounter_num!=$encounter_num_before) {
			$nb_encounter_num++;
		}
		if ($nb_encounter_num % 2 ==0) {
			$backgroundColor=$color_odd_encounter_num;
		} else {
			$backgroundColor=$color_even_encounter_num;
		}
		
		$tableau_document=get_document_for_a_patient($patient_num," and encounter_num='$encounter_num' ");
		
		$data_upper=get_data_out_of_range ($patient_num,'upper'," and encounter_num='$encounter_num' ");
		$data_lower=get_data_out_of_range ($patient_num,'lower'," and encounter_num='$encounter_num' ");
		
		$data_cim10=get_data ($patient_num,$thesaurus_code_pmsi," and encounter_num='$encounter_num' order by document_date desc");
		
		$table_concept=get_concept_patient ($patient_num," and document_num in (select document_num from dwh_document where patient_num=$patient_num and encounter_num='$encounter_num') ") ;
		$table_mvt=get_mvt_info_by_encounter ($encounter_num,'desc');
		print "<div class=\"patient_stay\" style=\"background-color:$backgroundColor;\">
		
		<h2 style=\"cursor:pointer\" onclick=\"plier_deplier('id_div_stay_$encounter_num');\"><span id=\"plus_id_div_stay_$encounter_num\">+</span> Séjour $encounter_num du $entry_date au $out_date</h2>
		<div id=\"id_div_stay_$encounter_num\" style=\"display:none;\">";
		print "<br><strong>Mouvement dans le séjour</strong><br>";
		print "<table>
		<thead>
			<tr>
				<th>Departement</th>
				<th>Unité</th>
				<th>Entrée</th>
				<th>Sortie</th>
				<th>Type</th>
				<th>Durée (jours)</th>
			</tr>
		</thead>
		<tbody>";
		foreach ($table_mvt as $i => $mvt) {
			$unit_num=$mvt['UNIT_NUM'];
			$department_num=$mvt['DEPARTMENT_NUM'];
			$entry_date=$mvt['ENTRY_DATE'];
			$out_date=$mvt['OUT_DATE'];
			$mvt_entry_mode=$mvt['MVT_ENTRY_MODE'];
			$mvt_exit_mode=$mvt['MVT_EXIT_MODE'];
			$type_mvt=$mvt['TYPE_MVT'];
			$mvt_length=$mvt['MVT_LENGTH'];
			$department_str=get_department_str($department_num);
			$unit_str=get_unit_str($unit_num);
			print"<tr>
				<td>$department_str</td>
				<td>$unit_str</td>
				<td>$entry_date</td>
				<td>$out_date</td>
				<td>$type_mvt</td>
				<td>$mvt_length</td>
				</tr>
			";
		}
		print "</tbody></table>";
		
		if (count($data_cim10)>0) {
			print "<br><strong>Cim10</strong><br>";
			print "<table>
			<thead>
			<tr><th>Code</th>
			<th>Libellé</th>
			<th>type</th>
			<th>Departement</th>
			<th>Date</th>
			</tr>
			</thead>
			<tbody>";
			foreach ($data_cim10 as $i => $data) {
				$concept_code=$data['CONCEPT_CODE'];
				$concept_str=$data['CONCEPT_STR'];
				$val_text=$data['VAL_TEXT'];
				$document_date_ddmmyyyy=$data['DOCUMENT_DATE_DDMMYYYY'];
				$department_num=$data['DEPARTMENT_NUM'];
				$department_str=get_department_str($department_num);
				print "<tr>
					<td>$concept_code</td>
					<td>$concept_str</td>
					<td>$val_text</td>
					<td>$department_str</td>
					<td>$document_date_ddmmyyyy</td>
					</tr>";
			}
			print "</tbody></table>";
		}
		
		if (count($data_upper)>0) {
			print "<br><strong>Data au dessus de la borne</strong><br>";
			print "<table>
			<thead>
			<tr><th>Examen</th><th>Nb val > borne sup</th><th>Max val</th></tr>
			</thead>
			<tbody>";
			foreach ($data_upper as $i => $data) {
				$val_numeric=$data['VAL_NUMERIC'];
				$concept_str=$data['CONCEPT_STR'];
				$info_complement=$data['INFO_COMPLEMENT'];
				$measuring_unit=$data['MEASURING_UNIT'];
				$lower_bound=$data['LOWER_BOUND'];
				$upper_bound=$data['UPPER_BOUND'];
				$nb_out=$data['NB_OUT'];
				print "<tr><td>$concept_str $measuring_unit ($info_complement)</td><td>$nb_out x > $upper_bound $measuring_unit</td><td>$val_numeric $measuring_unit</td></td>";
			}
			print "</tbody></table>";
		}
		if (count($data_lower)>0) {
			print "<br><strong>Data en dessous de la borne</strong><br>";
			print "<table>
			<thead>
			<tr><th>Examen</th><th>Nb val < borne inf</th><th>Min val</th></tr>
			</thead>
			<tbody>";
			foreach ($data_lower as $i => $data) {
				$val_numeric=$data['VAL_NUMERIC'];
				$concept_str=$data['CONCEPT_STR'];
				$info_complement=$data['INFO_COMPLEMENT'];
				$measuring_unit=$data['MEASURING_UNIT'];
				$lower_bound=$data['LOWER_BOUND'];
				$upper_bound=$data['UPPER_BOUND'];
				$nb_out=$data['NB_OUT'];
				print "<tr><td>$concept_str $measuring_unit ($info_complement)</td><td>$nb_out x < $lower_bound $measuring_unit</td><td>$val_numeric $measuring_unit</td></td>";
			}
			print "</tbody></table>";
		}

#		print "<br><strong>Documents</strong><br>";
#		print "<table>
#		<thead>
#		<tr><th>Titre</th><th>Date</th><th>Auteur</th><th>Source</th></tr>
#		</thead>
#		<tbody>";
#		foreach ($tableau_document as $document_num) {
#			$document=get_document ($document_num);
#			$title= $document['title'];
#			$document_date= $document['document_date'];
#			$author= $document['author'];
#			$document_origin_code= $document['document_origin_code'];
#			$department_num= $document['department_num'];
#			print "<tr><td>$title</td><td>$document_date</td><td>$author</td><td>$document_origin_code</td></tr>";
#			
#		}
#		print "</tbody></table>";
		
		if (count($table_concept)>0) {
			print "<br><strong>Concepts extracted</strong><br>";
			print "<table>
			<thead>
			<tr><th>Termes</th><th>Extrait</th></tr>
			</thead>
			<tbody>";
			foreach ($table_concept as $i => $concept) {
				$concept_str=$concept['CONCEPT_STR_FOUND'];
				$nb_str=$concept['NB_STR'];
				$list_document_num=$concept['LIST_DOCUMENT_NUM'];
				$concept_code=$concept['CONCEPT_CODE'];
				$summary=display_sentence_with_term ($patient_num,$list_document_num,$concept_str);
				#print "<span onmouseover=\"display_sentence_with_term($patient_num,'$list_document_num','$concept_str','id_span_sentence_with_term_$concept_code');\">$concept_str x $nb_str</span><span id=\"id_span_sentence_with_term_$concept_code\"></span> <br>";
				print "<tr><td>$concept_str x $nb_str</td><td>$summary</td></tr>";
			}
			print "</tbody></table>";
		}
		print "</div>";
		print "</div>";
		$encounter_num_before=$encounter_num;
	}
	return $tableau_query;	
}
?>