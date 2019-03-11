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

ini_set("memory_limit","100M");
putenv("NLS_LANG=French");

include_once "parametrage.php";
include_once "connexion_bdd.php";
include_once("ldap.php");
include_once("fonctions_droit.php");
include_once("fonctions_dwh.php");
include_once("fonctions_ecrf.php");

if ($_POST['action']=='connexion') {
	$erreur=verif_connexion($_POST['login'],$_POST['passwd'],'page_ajax');
	print "$erreur";
	exit;
}

if ($_SESSION['dwh_login']=='') {
	print "deconnexion";
	exit;
} else {
	include_once("verif_droit.php");
	if ($erreur_droit!='') {
		print "$erreur_droit";
		exit;
	}
}
session_write_close();

if ($_POST['action']=='rechercher_regexp') {
	$tmpresult_num=$_POST['tmpresult_num'];
	//$regexp=nettoyer_pour_requete(urldecode($_POST['regexp']));
	$regexp=trim(nettoyer_pour_requete_patient(urldecode($_POST['regexp'])));

	$datamart_num=$_POST['datamart_num'];
	$filter_query_user_right=filter_query_user_right('tmpresult_num',$user_num_session,$_SESSION['dwh_droit_all_departments'.$datamart_num],$liste_service_session,$liste_document_origin_code_session);

	$process_num=uniqid();
	passthru( "php exec_regexp.php $user_num_session $tmpresult_num $process_num $datamart_num \"$regexp\" \"$filter_query_user_right\">> $CHEMIN_GLOBAL_LOG/log_regexp_$process_num.txt 2>&1 &");
	print "$process_num";
}


if ($_POST['action']=='verif_process_execute_regexp') {
	$process_num=$_POST['process_num'];
	$process=get_process ($process_num);
	$status=$process['STATUS'];
	$commentary=$process['COMMENTARY'];
	print "$status;$commentary";
}



if ($_POST['action']=='get_regexp_result') {
	$process_num=$_POST['process_num'];
	$process=get_process ($process_num);
	$status=$process['STATUS'];
	$commentary=$process['COMMENTARY'];
	print $process['RESULT'];
}
