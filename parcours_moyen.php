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
session_start();

date_default_timezone_set('Europe/Paris');
putenv("NLS_LANG=French");
putenv("NLS_LANG=FRENCH");
putenv("NLS_LANGUAGE=FRENCH_FRANCE.WE8MSWIN1252");
error_reporting(E_ALL ^ E_NOTICE);


include_once("parametrage.php");
include_once("connexion_bdd.php");
include_once("ldap.php");
include_once("fonctions_dwh.php");
include_once("fonctions_stat.php");
include_once("verif_droit.php");
$date_today_unique=date("dmYHis");
session_write_close();

$tmpresult_num=$_GET['tmpresult_num'];
$unit_or_department=$_GET['unit_or_department'];
$patient_num_encounter_num=$_GET['patient_num_encounter_num'];

if ($unit_or_department=='') {
	$unit_or_department='department';
}
if ($patient_num_encounter_num=='') {
	$patient_num_encounter_num='encounter_num';
}

if ($unit_or_department=='department') {
	$coef_limite='1';
} else {
	$coef_limite='2';
}
if ($patient_num_encounter_num=='') {
	$req=oci_parse($dbh,"select round(count(distinct encounter_num)*$coef_limite/100) nb_encounter_num_total from dwh_tmp_result where tmpresult_num=$tmpresult_num and encounter_num is not null ");
} else {
	$req=oci_parse($dbh,"select round(count(distinct encounter_num)*$coef_limite/100) nb_encounter_num_total from dwh_patient_stay where patient_num in (select patient_num from dwh_tmp_result where tmpresult_num=$tmpresult_num and patient_num is not null ) and out_date is not null ");
}
oci_execute($req) ;
$res=oci_fetch_array($req,OCI_ASSOC);
$nb_encounter_num_total=$res['NB_ENCOUNTER_NUM_TOTAL'];

if ($_GET['nb_mini']=='') {
	$nb_mini=$nb_encounter_num_total;
} else {
	$nb_mini=$_GET['nb_mini'];
}

if ($tmpresult_num!='') {
	if ($unit_or_department=='unit') {
		$select_unit='selected';
		$select_department='';
	}
	if ($unit_or_department=='department') {
		$select_unit='';
		$select_department='selected';
	}
	if ($patient_num_encounter_num=='encounter_num') {
		$select_patient_num='';
		$select_encounter_num='selected';
	}
	if ($patient_num_encounter_num=='patient_num') {
		$select_patient_num='selected';
		$select_encounter_num='';
	}
	print "<form method=\"get\">
		".get_translation('MINIMUM_NUMBER_OF_STAYS','Nombre minimum de passages')." : <input type=\"text\" name=\"nb_mini\" value=\"$nb_mini\" size=\"3\"> 
		<select name=\"unit_or_department\">
			<option value='unit' $select_unit>".get_translation('HOSPITAL_HOSPITAL_UNIT','UF')."</option>
			<option value='department' $select_department>".get_translation('HOSPITAL_DEPARTMENT','Service')."</option>
		</select>
		<select name=\"patient_num_encounter_num\">
			<option value='encounter_num' $select_encounter_num>".get_translation('STAYS_FOUND','Séjours trouvés')."</option>
			<option value='patient_num' $select_patient_num>".get_translation('ALL_STAYS','Tous les séjours')."</option>
		</select>
		<input type=\"submit\">
		<input type=\"hidden\" name=\"tmpresult_num\" value=\"$tmpresult_num\">
	</form>";
}

if ($tmpresult_num!='' && $unit_or_department=='unit') {
	parcours_sejour_uf ($tmpresult_num,'','',$patient_num_encounter_num,$nb_mini);
	print "<img src=\"upload/tmp_graphviz_parcours_sejour_uf_$tmpresult_num.png?".uniqid()."\">";
}


if ($tmpresult_num!='' && $unit_or_department=='department') {
	parcours_sejour_service ($tmpresult_num,'','',$patient_num_encounter_num,$nb_mini);
	parcours_sejour_service_json ($tmpresult_num, '','', $patient_num_encounter_num, $nb_mini);
	print "<img src=\"upload/tmp_graphviz_parcours_sejour_service_$tmpresult_num.png?".uniqid()."\">";
}




?>
