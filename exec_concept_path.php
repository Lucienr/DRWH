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
date_default_timezone_set('Europe/Paris');
ini_set("memory_limit","200M");
putenv("NLS_LANG=French");
putenv("NLS_LANGUAGE=FRENCH_FRANCE.WE8MSWIN1252");
include_once("../../parametrage.php");
include_once("../../connexion_bdd.php");



#date_default_timezone_set('Europe/Paris');
#ini_set("memory_limit","200M");
#putenv("NLS_LANG=French");
#putenv("NLS_LANGUAGE=FRENCH_FRANCE.WE8MSWIN1252");
#$dbh = oci_connect('dwh','fuji11','//nck-imagine.nck.aphp.fr/imagine','WE8MSWIN1252') ;

function create_path($thesaurus_data_num){
	global $dbh;
	

	$query="select concept_str from dwh_thesaurus_data where thesaurus_data_num=$thesaurus_data_num";
	$sel = oci_parse($dbh,$query); 
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$concept_str=$r['CONCEPT_STR'];	
		
#		
	$concept_path='';	
	$query="select concept_str from dwh_thesaurus_data_graph,dwh_thesaurus_data where thesaurus_data_son_num=$thesaurus_data_num 
	and dwh_thesaurus_data_graph.thesaurus_data_father_num=dwh_thesaurus_data.thesaurus_data_num order by distance desc" ;
				
	$sel=oci_parse($dbh,$query);
	oci_execute($sel);
	$concept_path='';
		while($r=oci_fetch_array($sel, OCI_RETURN_NULLS+OCI_ASSOC)){
				$concept_parent_str=$r['CONCEPT_STR'];
				if ($concept_path==''){
					$concept_path.=$concept_parent_str;
				}else{
					$concept_path.=' / '.$concept_parent_str;
				}
		}
	if ($concept_path==''){
		$concept_path.=$concept_str;
	}else{
		$concept_path.=' / '.$concept_str;
	}	
			
	return "$concept_path";
}


function nettoyer_avant_insert_sql ($text) {
	$text=preg_replace("/'/","''",$text);
	$text=preg_replace("/\"/"," ",$text);
	return $text;
}

$query="select thesaurus_data_num from dwh_thesaurus_data" ;		
$sel=oci_parse($dbh,$query);
oci_execute($sel);
$concept_path='';
$i=0;
while($r=oci_fetch_array($sel, OCI_RETURN_NULLS+OCI_ASSOC)){
		$thesaurus_data_num=utf8_encode($r['THESAURUS_DATA_NUM']);
		$concept_path=create_path($thesaurus_data_num);
		$concept_path=nettoyer_avant_insert_sql ($concept_path);
		$req="update dwh_thesaurus_data set concept_path='$concept_path' where thesaurus_data_num=$thesaurus_data_num" ;	
		print "req $req \n";	
		$up=oci_parse($dbh,$req);
		oci_execute($up);
		$i++;
#		if($i==20){exit;}
}




oci_close ($dbh);
oci_close ($dbh_etl);
?>





