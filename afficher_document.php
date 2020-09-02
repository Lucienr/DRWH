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

putenv("NLS_LANG=French");

include_once "parametrage.php";
include_once "connexion_bdd.php";
include_once("ldap.php");
include_once("fonctions_droit.php");
include_once("fonctions_dwh.php");


if ($_POST['action']=='connexion') {
	$erreur=verif_connexion($_POST['login'],$_POST['passwd'],'page_ajax');
	print "$erreur";
	exit;
}
if ($_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_login']=='') {
	print "deconnexion";
	exit;
} else {
	include_once("verif_droit.php");
	if ($erreur_droit!='') {
		print "$erreur_droit";
		exit;
	}
}

$dwh_droit_all_departments=$_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_droit_all_departments'.$datamart_num];
session_write_close();


$document_num=$_GET['document_num'];
$requete=trim(nettoyer_pour_requete(urldecode($_GET['requete'])));
$tmpresult_num=$_GET['tmpresult_num'];


if ($document_num!='') {
	if ($document_num=='tout') {
		$patient_num=$_GET['patient_num'];
	        $document=affiche_contenu_liste_document_patient($patient_num,$requete,$user_num_session);
	} else {
		$document=afficher_document_patient($document_num,$requete,$user_num_session);
	}
}

if ($tmpresult_num!='') {
	$filtre_sql_resultat=filter_query_user_right("dwh_tmp_result_$user_num_session",$user_num_session,$_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_droit_all_departments'.$datamart_num],$liste_service_session,$liste_document_origin_code_session);
   
        $document='';
        $patient_num_before='';
        $sel=oci_parse($dbh,"select patient_num, document_num from dwh_tmp_result_$user_num_session where tmpresult_num=$tmpresult_num and user_num=$user_num_session and object_type='document'  $filtre_sql_resultat order by patient_num, document_num");
        oci_execute($sel);
        while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $patient_num=$r['PATIENT_NUM'];
                $document_num=$r['DOCUMENT_NUM'];
                if ($patient_num!=$patient_num_before) {
	                $patient=afficher_patient ($patient_num,'basique',$document_num,'','export_liste_documents_recherche');
	                $document.= "<br><strong>$patient</strong><br><br>";
	        }
		$document.=afficher_document_patient($document_num,$requete,$user_num_session);
		$document.="<br><p style=\"page-break-after: always;\" class=\"noprint\">----------------------------------------------------------</p>";
		$patient_num_before=$patient_num;
        }
	save_log_page($user_num_session,'export_liste_documents_recherche');
}

?>

<html>
<head>
<title><? print get_translation('DOCUMENT','Document'); ?></title>
<link rel='stylesheet' href='style.css' type='text/css' />
</head>
<body>
	<? 
	print "$document";
	?>
</body>

<?
if ($_GET['option']=='print') {
	print "<script type=\"text/javascript\"> window.print();</script>";
}

oci_close ($dbh);
oci_close ($dbh_etl);
?>
</html>