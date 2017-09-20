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


ini_set("memory_limit","800M");
putenv("NLS_LANG=French");

include_once "parametrage.php";
include_once "connexion_bdd.php";
include_once("ldap.php");
include_once("fonctions_droit.php");
include_once("fonctions_dwh.php");

$user_num_session='';

$periode=$argv[1];

print "select query_num , user_num from dwh_query where crontab_query=1 and crontab_periode='$periode'";
$sel = oci_parse($dbh, "select query_num , user_num from dwh_query where crontab_query=1 and crontab_periode='$periode'");   
oci_execute($sel);
while ($r = oci_fetch_array($sel, OCI_ASSOC)) { 
	$query_num=$r['QUERY_NUM'];
	$user_num_session=$r['USER_NUM'];
	generer_resultat_requete_sauve($query_num);
}
?>
