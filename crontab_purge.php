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

$truncate = oci_parse($dbh, "truncate table dwh_tmp_preresult");   
oci_execute($truncate);

$truncate = oci_parse($dbh, "truncate table dwh_tmp_result");   
oci_execute($truncate);

$truncate = oci_parse($dbh, "truncate table dwh_tmp_query");   
oci_execute($truncate);

$truncate = oci_parse($dbh, "delete from  dwh_process where process_end_date<sysdate or process_end_date is null");   
oci_execute($truncate);

$truncate = oci_parse($dbh, "delete from dwh_process_patient where process_num not in (select process_num from dwh_process) ");   
oci_execute($truncate);

system("rm /pub/dwh/upload/*.png");
system("rm /pub/dwh/upload/*.html");
system("rm /pub/dwh/upload/*.map");
system("rm /pub/dwh/upload/*.dot");
system("rm /pub/dwh/upload/*.txt");
system("rm /pub/dwh/timeline/xml/*.xml");

?>