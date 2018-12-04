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




if ($_POST['action']=='affiche_patient_opposition' && $_SESSION['dwh_droit_admin']=='ok') {
    $hospital_patient_id=trim($_POST['hospital_patient_id']);
    if ($hospital_patient_id!='') {
        $sel = oci_parse($dbh, "select  patient_num  from dwh_patient_ipphist  where hospital_patient_id ='$hospital_patient_id' and origin_patient_id='SIH' ");   
        oci_execute($sel);
        while ($r = oci_fetch_array($sel, OCI_ASSOC)) {
            $patient_num=$r['PATIENT_NUM'];
            $tab_patient=get_patient ($patient_num);
            print "
    <div id=\"id_div_opposition_patient_$patient_num\">Patient N° $patient_num (<a href=\"patient.php?patient_num=$patient_num\">Accéder au dossier</a>)<br>
    IPP :  ".$tab_patient['HOSPITAL_PATIENT_ID']."<br>
    Nom :  ".$tab_patient['LASTNAME']."<br>
    Prénom :  ".$tab_patient['FIRSTNAME']."<br>
    Date naissance :  ".$tab_patient['BIRTH_DATE']."<br>
    <input type=\"button\" onclick=\"valider_opposition_patient($patient_num);\" value=\"Confirmer opposition\">
    <br>
    </div><br>
    ";
        }
    }
} 

if ($_POST['action']=='list_patients_opposed' && $_SESSION['dwh_droit_admin']=='ok') {
    $tableau_list_patients_opposed=get_list_patients_opposed();
    print "<table class=\"tablefin\"><thead><th>IPP</th><th>Origine</th><th>Date</th></thead><tbody>";
    foreach ($tableau_list_patients_opposed as $tab) {
        print "<tr>";
        print "<td>".$tab['hospital_patient_id']."</td>";
        print "<td>".$tab['origin_patient_id']."</td>";
        print "<td>".$tab['opposition_date_char']."</td>";
        print "</tr>";
    }
    print "</tbody></table>";
}

if ($_POST['action']=='valider_opposition_patient' && $_SESSION['dwh_droit_admin']=='ok') {
    $patient_num=trim($_POST['patient_num']);
    if ($patient_num!='') {
            $result_validate=validate_opposition($patient_num);
            print "Suppression du patient faite, son IPP est stocké dans la table DWH_PATIENT_OPPOSITION<br>";
    }
} 
    
    

if ($_POST['action']=='insert_outil' && $_SESSION['dwh_droit_admin']=='ok') {
    $tableau['TITLE']=urldecode($_POST['title']);
    $tableau['DESCRIPTION']=urldecode($_POST['description']);
    $tableau['AUTHORS']=urldecode($_POST['authors']);
    $tableau['URL']=urldecode($_POST['url']);
    insert_outil($tableau);
    admin_lister_outil () ;
}

if ($_POST['action']=='update_outil' && $_SESSION['dwh_droit_admin']=='ok') {
    $tableau['TOOL_NUM']=urldecode($_POST['tool_num']);
    $tableau['TITLE']=urldecode($_POST['title']);
    $tableau['DESCRIPTION']=urldecode($_POST['description']);
    $tableau['AUTHORS']=urldecode($_POST['authors']);
    $tableau['URL']=urldecode($_POST['url']);
    update_outil($tableau);
}

if ($_POST['action']=='delete_outil' && $_SESSION['dwh_droit_admin']=='ok') {
    $tool_num=$_POST['tool_num'];
    delete_outil($tool_num);
}

if ($_POST['action']=='calculate_nb_insert' && $_SESSION['dwh_droit_admin']=='ok') {
    $nb_jours=$_POST['nb_jours'];
    $tableau_calculate_nb_insert=calculate_nb_insert($nb_jours);
    print"<table class=\"tablefin_small\">
    <thead>
        <tr>
        <th>Source</th>
        <th>&lt; $nb_jours days</th>";
    $tab_date_yyyymmdd=array();
    for ($i=$nb_jours;$i>=0;$i--) {
        $sel=oci_parse($dbh,"select to_char(sysdate-$i,'DD/MM/YY') as date_ddmmyy,to_char(sysdate-$i,'YYYYMMDD') as date_yyyymmdd  from dual");
        oci_execute($sel);
        $r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
        $date_ddmmyy=$r['DATE_DDMMYY'];
        $date_yyyymmdd=$r['DATE_YYYYMMDD'];
        $tab_date_yyyymmdd[]=$date_yyyymmdd;
        print "<th>$date_ddmmyy</th>";
    }
    print "</tr></thead>";
    print "<tbody>";
    $class_alert="style=\"background-color:red;\"";
    $class_normal="style=\"background-color:transparent;\"";
    $onmouse=" onmouseover=\"this.style='background-color:pink;'\"  onmouseout=\"this.style='background-color:transparent;'\" ";
        print "<tr $onmouse><td>Patient</td>";
        print "<td>".$tableau_calculate_nb_insert['patient'][0]."</td>";
        foreach ($tab_date_yyyymmdd as $date_yyyymmdd) {
            $nb=$tableau_calculate_nb_insert['patient'][$date_yyyymmdd];
            if ($nb=='') {
                $nb='0';
                $class_td=$class_alert;
            } else {
                $class_td=$class_normal;
            }
            print "<td $class_td>$nb</td>";
        }
        print "</tr>";

        print "<tr $onmouse ><td>Mouvement</td>";
        print "<td>".$tableau_calculate_nb_insert['mvt'][0]."</td>";
        foreach ($tab_date_yyyymmdd as $date_yyyymmdd) {
            $nb=$tableau_calculate_nb_insert['mvt'][$date_yyyymmdd];
            if ($nb=='') {
                $nb='0';
                $class_td=$class_alert;
            } else {
                $class_td=$class_normal;
            }
            print "<td $class_td>$nb</td>";
        }
        print "</tr>";

        foreach ($tableau_global_document_origin_code as $document_origin_code => $document_origin_str) {
            print "<tr $onmouse><td>$document_origin_str</td>";
            print "<td>".$tableau_calculate_nb_insert[$document_origin_code][0]."</td>";
            foreach ($tab_date_yyyymmdd as $date_yyyymmdd) {
                $nb=$tableau_calculate_nb_insert[$document_origin_code][$date_yyyymmdd];
                if ($nb=='') {
                    $nb='0';
                    $class_td=$class_alert;
                } else {
                    $class_td=$class_normal;
                }
                print "<td $class_td>$nb</td>";
            }
            print "</tr>";
        }

    print "</tbody>
    </table>";
}

?>