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

putenv("NLS_LANG=French");

include_once "parametrage.php";
include_once "connexion_bdd.php";
include_once("fonctions_droit.php");
include_once("fonctions_dwh.php");
include_once ("fonctions_outils.php");

//$user_num_session $tmpresult_num $process_num $datamart_num \"$regexp
$user_num_session=$argv[1];
$process_num=$argv[2];
$option_limite=$argv[3];

$process=get_process ($process_num);
$liste_patient=$process['RESULT'];

update_process ($process_num,"0","$i patients traités","NULL",$user_num_session,"xls");

$tableau_ligne=preg_split("/[\n\r]/",$liste_patient);	
$resultat_mapping=  "<table class=\"tablefin\"><tr>
<td>i</td>
<td>lastname</td>
<td>firstname</td>
<td>birth_date</td>
<td>comment</td>
<td>method</td>
<td>IPP</td>
<td>LASTNAME</td>
<td>LASTNAME 2</td>
<td>FIRSTNAME</td>
<td>BIRTH_DATE</td>
<td>SEX</td>
<td>DWH ID</td>
<td>Link</td>
</tr>";
foreach ($tableau_ligne as $ligne) {
	$i++;
	$tab=preg_split("/[;,\t]/",$ligne);
	$lastname=$tab[0];
	$firstname=$tab[1];
	$birth_date=$tab[2];
	$comment=$tab[3];
	$list_patient_num='';
	$hospital_patient_id_sih='';
	list($list_patient_num,$method)=get_mapping_patient ($lastname,$firstname,$birth_date,$option_limite);
	
	$tab_patient_num=explode(",",$list_patient_num);
	foreach ($tab_patient_num as $patient_num) {
		$patient=array();
		if ($patient_num!='') {
	  		$autorisation_voir_patient=autorisation_voir_patient($patient_num,$user_num_session);
	  		if ($autorisation_voir_patient=='ok') {
				$patient=get_patient($patient_num);	
				$hospital_patient_id_sih=get_master_patient_id_sih($patient_num);	
				if ($hospital_patient_id_sih=='') { 
					$method='non trouve';
					$patient=array();
				} 		
	  		} else {				
				$method='non autorise';
	  		}
	  		
	  	} else {		
			$method='non trouve';		
	  	}
		$resultat_mapping.= "<tr><td>$i</td>
			<td>$lastname</td>
			<td>$firstname</td>
			<td>$birth_date</td>
			<td>$comment</td>
			<td>$method</td>
			<td>$hospital_patient_id_sih</td>
			<td>".$patient['LASTNAME']."</td>
			<td>".$patient['MAIDEN_NAME']."</td>
			<td>".$patient['FIRSTNAME']."</td>
			<td>".$patient['BIRTH_DATE']."</td>
			<td>".$patient['SEX']."</td>
			<td>".$patient['PATIENT_NUM']."</td>";
		if ($patient['PATIENT_NUM']!='') {
			$resultat_mapping.= "<td><a href=\"patient.php?patient_num=".$patient['PATIENT_NUM']."\" target=_blank>dossier</a></td>";
		} else {
			$resultat_mapping.= "<td></td>";
		}
			$resultat_mapping.= "</tr>
		";
	}
	if ($i % 2 ==0) {
		update_process ($process_num,"0","$i patients traités","",$user_num_session,"xls");
	}
}	
$resultat_mapping.=  "</table>";

update_process ($process_num,"1","mapping.xls",$resultat_mapping,$user_num_session,"xls");
sauver_notification ($user_num_session,$user_num_session,'process',"",$process_num);


oci_close ($dbh);
oci_close ($dbh_etl);
?>