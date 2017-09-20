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

ini_set("memory_limit","100M");
putenv("NLS_LANG=French");

include_once "parametrage.php";
include_once "connexion_bdd.php";
include_once("fonctions_droit.php");
include_once("fonctions_dwh.php");
include_once "fonctions_stat.php"; 
include_once "fonctions_concepts.php"; 
include_once "fonctions_pmsi.php"; 
include_once "fonctions_labo.php"; 
include_once "similarite_fonction.php"; 


$cohort_num=$argv[1];
$process_num=$argv[2];
$requete=nettoyer_pour_requete($argv[3]);

$tab_patient_num_deja=array();

update_process ($process_num,'0',get_translation('PROCESS_START','debut du process'),'');

$ins= oci_parse($dbh,"delete from dwh_process_patient where process_num='$process_num' " );   
oci_execute($ins);

$sel=oci_parse($dbh," select  patient_num from dwh_text where contains (enrich_text,'$requete')>0 and certainty=1  and context='patient_text' ");
oci_execute($sel) || die ("erreur");
while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
	$patient_num=$r['PATIENT_NUM'];
	if ($tab_patient_num_deja[$patient_num]=='') {
        	$ins= oci_parse($dbh,"insert into dwh_process_patient (process_num,patient_num) values ('$process_num','$patient_num') " );   
	        oci_execute($ins);
		$tab_patient_num_deja[$patient_num]='ok';        
	}
}

update_process ($process_num,'1',get_translation('PROCESS_END','process fini'),'');
?>