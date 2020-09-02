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
include_once("fonctions_droit.php");
include_once("verif_droit.php");
include_once("fonctions_stat.php");

$dwh_droit_all_departments=$_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_droit_all_departments'.$datamart_num];

session_write_close();

$date_today=date("d-m-Y-H-i");
$process_num=$_GET['process_num'];

$style= "<style>.num {  mso-number-format:General;} .text{  mso-number-format:\"\@\";/*force text*/ } </style> ";

if ( $process_num!='') {
	$tableau_process=get_process($process_num,'get_result');
	if ($user_num_session==$tableau_process['USER_NUM']) {
		$result=$tableau_process['RESULT'];
		$type_result=$tableau_process['TYPE_RESULT'];
		$commentary=$tableau_process['COMMENTARY'];
		$category_process=$tableau_process['CATEGORY_PROCESS'];
		if (preg_match("/.*\.[a-z][a-z][a-z][a-z]?$/i",$commentary)) {
			$filename=$commentary;
		} else {
			if ($type_result=='xls') {
				$filename=$category_process."_$date_today.xls";
			} else {
				if ($type_result!='') {
					$filename=$category_process."_$date_today.$type_result";
				} else {
					$filename=$category_process."_$date_today.txt";
				}
			}
		}
		if ($type_result=='xls') {
			header ("Content-Type: application/excel"); 
			header ("Content-Disposition: attachment; filename=$filename");
        		print "$style";
		} else {
			header ("Content-Disposition: attachment; filename=$filename");
		}
		print $result;
	} else {
		print " Vous n'êtes pas autorisé à voir le contenu de ce process";
	}
}
	
?>