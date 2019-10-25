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

include_once("parametrage.php");
include_once("connexion_bdd.php");
include_once("ldap.php");
include_once("fonctions_droit.php");
include_once("fonctions_dwh.php");
include_once("fonctions_stat.php");


include("verif_droit.php");

$date_today_unique=date("dmYHis");
#session_start();
#
#
#putenv("NLS_LANG=French");
#putenv("NLS_LANG=FRENCH");
#putenv("NLS_LANGUAGE=FRENCH_FRANCE.WE8MSWIN1252");
#error_reporting(E_ALL ^ E_NOTICE);
#
#
#include_once("parametrage.php");
#include_once("connexion_bdd.php");
#include_once("ldap.php");
#include_once("verif_droit.php");
#include_once("fonctions_dwh.php");
#include_once("fonctions_stat.php");

#$date_today_unique=date("dmYHis");
#session_write_close();



$tmpresult_num=$_GET['tmpresult_num'];
$unit_or_department=$_GET['unit_or_department'];

if ($unit_or_department=='') {
	$unit_or_department='department';
}

if ($unit_or_department=='department') {
	$coef_limite='20';
} else {
	$coef_limite='40';
}

if ($_GET['nb_mini']=='') {
	$nb_mini=$coef_limite;
} else {
	$nb_mini=$_GET['nb_mini'];
}


if ($tmpresult_num!='' && $unit_or_department=='unit') {
	parcours_complet('dot',$tmpresult_num,'','',$unit_or_department,$nb_mini);
	parcours_complet('json',$tmpresult_num,'','',$unit_or_department,$nb_mini);
}



if ($tmpresult_num!='' && $unit_or_department=='department') {
	parcours_complet('dot',$tmpresult_num,'','',$unit_or_department,$nb_mini);
	parcours_complet('json',$tmpresult_num,'','',$unit_or_department,$nb_mini);
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
	print "<form method=\"get\">
		".get_translation('MINIMUM_NUMBER_OF_STAYS','Nombre minimum de passages')." : <input type=\"text\" name=\"nb_mini\" value=\"$nb_mini\" size=\"3\"> 
		<select name=\"unit_or_department\">
			<option value='unit' $select_unit>".get_translation('HOSPITAL_HOSPITAL_UNIT','UF')."</option>
			<option value='department' $select_department>".get_translation('HOSPITAL_DEPARTMENT','Service')."</option>
		</select>
		<input type=\"submit\">
		<input type=\"hidden\" name=\"tmpresult_num\" value=\"$tmpresult_num\">
	</form>";
}

print "<a href=\"$URL_UPLOAD/tmp_d3_complet_json_$tmpresult_num$unit_or_department$nb_mini.json\" target=_blank>fichier json</a>";

print "<img src=\"$URL_UPLOAD/tmp_graphviz_parcours_complet_$tmpresult_num$unit_or_department$nb_mini.png\">";


?>