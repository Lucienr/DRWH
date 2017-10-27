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


$document_num=$_GET['document_num'];
$requete=trim(nettoyer_pour_requete(urldecode($_GET['requete'])));


if ($document_num!='') {
	if ($document_num=='tout') {
		$patient_num=$_GET['patient_num'];
	        $document=affiche_contenu_liste_document_patient($patient_num,$requete);
	} else {
		$document=afficher_document_patient($document_num,$requete);
	}
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
?>
</html>