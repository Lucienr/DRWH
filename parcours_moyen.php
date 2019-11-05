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
include_once("fonctions_droit.php");
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


$max_nb_mvt_patient_num=$_GET['max_nb_mvt_patient_num'];
$max_nb_mvt_encounter_num=$_GET['max_nb_mvt_encounter_num'];

$max_nb_mvt=$_GET['max_nb_mvt_'.$patient_num_encounter_num];

if ($max_nb_mvt=='') {
	$requete="select max(NB) as MAX_NB_MVT from (select a.department_num,b.department_num, count(*)  nb  from dwh_patient_mvt a,dwh_patient_mvt b where
				exists ( select * from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num and dwh_tmp_result_$user_num_session.$patient_num_encounter_num= a.$patient_num_encounter_num and dwh_tmp_result_$user_num_session.encounter_num is not null) and
                a.patient_num=b.patient_num  and
                a.encounter_num=b.encounter_num and
                a.mvt_order=b.mvt_order-1
                and b.out_date is not null
                and a.out_date is not null
                and a.encounter_num is not null 
                and b.encounter_num is not null 
                and a.type_mvt in ('H' ,'U')
                and b.type_mvt in ('H' ,'U')
                group by a.department_num,b.department_num )";
	$req=oci_parse($dbh,$requete);
	oci_execute($req) ;
	$res=oci_fetch_array($req,OCI_ASSOC);
	$max_nb_mvt=$res['MAX_NB_MVT'];
	if ($patient_num_encounter_num=='encounter_num') {
		$max_nb_mvt_encounter_num=$max_nb_mvt;
	}
	if ($patient_num_encounter_num=='patient_num') {
		$max_nb_mvt_patient_num=$max_nb_mvt;
	}
}

if ($_GET['nb_mini']!='') {
	$nb_mini=$_GET['nb_mini'];
	if ($max_nb_mvt<$nb_mini) {
		$nb_mini=$max_nb_mvt;
	}
} else {
	$req=oci_parse($dbh,"select round($max_nb_mvt/2) nb_encounter_num_total from dual ");
	oci_execute($req) ;
	$res=oci_fetch_array($req,OCI_ASSOC);
	$nb_encounter_num_total=$res['NB_ENCOUNTER_NUM_TOTAL'];
	$nb_mini=$nb_encounter_num_total;
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
		<input type=\"hidden\" name=\"max_nb_mvt_encounter_num\" value=\"$max_nb_mvt_encounter_num\">
		<input type=\"hidden\" name=\"max_nb_mvt_patient_num\" value=\"$max_nb_mvt_patient_num\">

	</form>";
}

if ($nb_mini!='') {
	if ($tmpresult_num!='' && $unit_or_department=='unit') {
		$parcours_sejour_uf=parcours_sejour_uf ($tmpresult_num,'','',$patient_num_encounter_num,$nb_mini);
		print "<img src=\"data: image/x-png;base64,".base64_encode($parcours_sejour_uf)."\">";
	}
	if ($tmpresult_num!='' && $unit_or_department=='department') {
		$parcours_sejour_service=parcours_sejour_service ($tmpresult_num,'','',$patient_num_encounter_num,$nb_mini);
		print "<img src=\"data: image/x-png;base64,".base64_encode($parcours_sejour_service)."\">";
	}
} else {
	print "Pas de séjours trouvés";
}




?>
