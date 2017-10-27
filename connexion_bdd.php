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
// connexion aux bases de donnees
$dbh = oci_connect($GLOBALS['USER_DB_DBH'],$GLOBALS['PASSWD_DB_DBH'],$GLOBALS['SID_DB_DBH'],'WE8MSWIN1252') ;
if (!$dbh) {
    $e = oci_error();  
    print_r($e);
    die();
}

$set=oci_parse($dbh,"alter session set NLS_NUMERIC_CHARACTERS = ', '"); // parameter for float number, separator is comma. and space for thousands
oci_execute($set);
?>