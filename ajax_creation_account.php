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

include_once "parametrage.php";
include_once "connexion_bdd.php";


if ($_POST['action']=='check_login_exists') {

	$login_create=strtolower(trim($_POST['login_create']));
	if ($login_create!='') {
		$sel = oci_parse($dbh, "select count(*) as nb_login from dwh_user where lower(login)='$login_create' ");   
		oci_execute($sel);
		$r = oci_fetch_array($sel, OCI_ASSOC);
		$nb_login=$r['NB_LOGIN'];
		if ($nb_login==0) {
			print "ok";
		} else {
			print "notok";
		}
	}
}
	
?>