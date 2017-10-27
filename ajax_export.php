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
?>
<?
session_start();

ini_set("memory_limit","100M");
putenv("NLS_LANG=French");

include_once "parametrage.php";
include_once "connexion_bdd.php";
include_once("fonctions_dwh.php");


$user_num_session=$_SESSION['dwh_user_num'];


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




if ($_POST['action']=='get_concept_data') {
	$tmpresult_num=$_POST['tmpresult_num'];
	$concepts=array();
	$concepts['items']=array();
	
	$text=trim(replace_accent(utf8_decode($_POST['q'])));

	$text=trim(preg_replace("/([a-z])\s/i","$1% ","$text "));
	$text=preg_replace("/([^\s])\s+([^\s])/","$1 and $2",$text);
	$query="select thesaurus_data_num, concept_code,concept_str,info_complement,measuring_unit from dwh_thesaurus_data where contains(description,'$text')>0";	
	$sel=oci_parse($dbh,$query);
	oci_execute($sel);
	
	while($r=oci_fetch_array($sel, OCI_RETURN_NULLS+OCI_ASSOC)){
			
		$thesaurus_data_num=utf8_encode($r['THESAURUS_DATA_NUM']);
		$concept_str=utf8_encode($r['CONCEPT_STR']);
		$concept_code=utf8_encode($r['CONCEPT_CODE']);
		$info_complement=utf8_encode($r['INFO_COMPLEMENT']);
		$measuring_unit=utf8_encode($r['MEASURING_UNIT']);
		$total_patients= total_patients_per_concept($thesaurus_data_num,$tmpresult_num);
		if($total_patients>0){
			$string=$concept_str;
			if ($info_complement!=''){
				$string.=' - '.$info_complement;
			}
			if ($info_complement!=''){
				$string.=' ('.$measuring_unit.')';
			}
			$string.=" (nb $total_patients) ";
			
			array_push($concepts['items'],array('id'=>$concept_code, 'text'=>$string));
		}
	}
	print json_encode($concepts);	
}

if ($_POST['action']=='save_curent_selection') {
	$tmpresult_num=$_POST['tmpresult_num'];
	$selected_concepts=$_POST['selected_concepts'];
	$title_concept_query=$_POST['title_concept_query'];
	$share_concepts=$_POST['share_concepts'];
	$list_concepts = implode(",", $selected_concepts);
	
	if ($title_concept_query!='' && $list_concepts!='') {
	       		
		$sel = oci_parse($dbh,"select dwh_temp_seq.nextval as export_data_num from  dual " );   
		oci_execute($sel);
		$row = oci_fetch_array($sel, OCI_ASSOC);
		$export_data_num=$row['EXPORT_DATA_NUM'];
				
		
	        $sel_var=oci_parse($dbh,"insert into dwh_export_data (EXPORT_DATA_NUM,USER_NUM,TITLE,LIST_CONCEPT,CREATION_DATE,SHARE_LIST) values ($export_data_num,$user_num_session,'$title_concept_query','$list_concepts',sysdate,$share_concepts)");
		oci_execute($sel_var);
		get_my_export_lists($user_num_session);
				
	} else {
		print "error";
	}
	save_log_page($user_num_session,'export_data_save');

}

if ($_POST['action']=='extract_concepts_from_list') {
	$list_of_concepts=$_POST['list_c'];

	$tmpresult_num=$_POST['tmpresult_num'];
	$concepts=array();
	foreach ($list_of_concepts as $title){
		$result=array();
      		$query="select list_concept from dwh_export_data where title='$title'";	
      		$sel=oci_parse($dbh,$query);
      		oci_execute($sel);	
		$r=oci_fetch_array($sel, OCI_RETURN_NULLS+OCI_ASSOC);
		$concepts_list=$r['LIST_CONCEPT'];
		$concepts_array=explode(",",$concepts_list);
		
		foreach ($concepts_array as $concept_item){
			$query="select thesaurus_data_num, concept_code,concept_str,info_complement,measuring_unit from dwh_thesaurus_data where concept_code='$concept_item' ";
			$sel=oci_parse($dbh,$query);
			oci_execute($sel);
			$r=oci_fetch_array($sel, OCI_RETURN_NULLS+OCI_ASSOC);	
			$thesaurus_data_num=utf8_encode($r['THESAURUS_DATA_NUM']);
			$concept_str=utf8_encode($r['CONCEPT_STR']);
			$concept_code=utf8_encode($r['CONCEPT_CODE']);
			$info_complement=utf8_encode($r['INFO_COMPLEMENT']);
			$measuring_unit=utf8_encode($r['MEASURING_UNIT']);
			$total_patients= total_patients_per_concept($thesaurus_data_num,$tmpresult_num);
			$string=$concept_str;
				if ($info_complement!=''){
					$string.=' - '.$info_complement;
				}
				if ($info_complement!=''){
					$string.=' ('.$measuring_unit.')';
				}
			$string.=" (nb $total_patients)";		
			array_push($concepts,array('id'=>$concept_code, 'text'=>$string));			
		}	
	}
	echo json_encode($concepts);
}


if ($_POST['action']=='delete_list') {
	$export_data_num=$_POST['export_data_num'];
	$req="delete from  dwh_export_data where  export_data_num='$export_data_num' and user_num=$user_num_session";
	$sel=oci_parse($dbh,$req);
	oci_execute($sel) || die ("error :list not deleted <br>");
	get_my_export_lists($user_num_session);
}

if ($_POST['action']=='share_list') {
	$export_data_num=$_POST['export_data_num'];
	$share_list=$_POST['share_list'];
	if($share_list==1){ 
		$share_change=0;
	}else if ($share_list==0) {
		$share_change=1;
	}
	$req="update dwh_export_data set share_list='$share_change' where export_data_num='$export_data_num' and user_num=$user_num_session";
	$sel=oci_parse($dbh,$req);
	oci_execute($sel) || die ("error in changing share option <br>");
	get_my_export_lists($user_num_session);
}


	
?>