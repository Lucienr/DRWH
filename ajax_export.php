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
include_once("fonctions_droit.php");
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

	$text=preg_replace("/\s+/"," ",$text);

	$text=nettoyer_pour_requete(trim($text));
	$text=trim(preg_replace("/([a-z])\s/i","$1% ","$text "));
	$text=preg_replace("/([^\s])\s+([^\s])/","$1 and $2",$text);

	$query="select thesaurus_data_num, concept_code,concept_str,info_complement,measuring_unit from dwh_thesaurus_data where contains(description,'$text')>0";	
	$sel=oci_parse($dbh,$query);
	oci_execute($sel);
	while($r=oci_fetch_array($sel, OCI_RETURN_NULLS+OCI_ASSOC)){
		$thesaurus_data_num=$r['THESAURUS_DATA_NUM'];
		$concept_str=utf8_encode($r['CONCEPT_STR']);
		$concept_code=utf8_encode($r['CONCEPT_CODE']);
		$info_complement=utf8_encode($r['INFO_COMPLEMENT']);
		$measuring_unit=utf8_encode($r['MEASURING_UNIT']);
		$total_patients= total_patients_per_concept($thesaurus_data_num,$tmpresult_num);
		if ($total_patients>0){
			$string=$concept_str;
			if ($info_complement!=''){
				$string.=' - '.$info_complement;
			}
			if ($info_complement!=''){
				$string.=' ('.$measuring_unit.')';
			}
			$string.=" (nb $total_patients) ";
			
			array_push($concepts['items'],array('id'=>$thesaurus_data_num, 'text'=>$string));
		}
	}
	print json_encode($concepts);	
}

if ($_POST['action']=='save_curent_selection') {
	$tmpresult_num=$_POST['tmpresult_num'];
	$selected_concepts=$_POST['selected_concepts'];
	$title_concept_query=$_POST['title_concept_query'];
	$share_concepts=$_POST['share_concepts'];
	if (is_array($selected_concepts)) {
		$list_concepts = implode(",", $selected_concepts);
	}
	
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

if ($_POST['action']=='extract_concepts_data_from_list') {
	$list_of_concepts=$_POST['list_c'];

	$tmpresult_num=$_POST['tmpresult_num'];
	$concepts=array();
	foreach ($list_of_concepts as $export_data_num){
		$result=array();
		if ($export_data_num!='') {
	      		$query="select list_concept from dwh_export_data where export_data_num='$export_data_num'";	
	      		$sel=oci_parse($dbh,$query);
	      		oci_execute($sel);	
			$r=oci_fetch_array($sel, OCI_RETURN_NULLS+OCI_ASSOC);
			$concepts_list=$r['LIST_CONCEPT'];
			$concepts_array=explode(",",$concepts_list);
			
			foreach ($concepts_array as $thesaurus_data_num){
				$query="select thesaurus_data_num, concept_code,concept_str,info_complement,measuring_unit from dwh_thesaurus_data where thesaurus_data_num='$thesaurus_data_num' ";
				$sel=oci_parse($dbh,$query);
				oci_execute($sel);
				$r=oci_fetch_array($sel, OCI_RETURN_NULLS+OCI_ASSOC);	
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
				array_push($concepts,array('id'=>$thesaurus_data_num, 'text'=>$string));			
			}	
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


if ($_POST['action']=='execute_process_export_data') {
	$tmpresult_num=$_POST['tmpresult_num'];
	$selected_thesaurus=$_POST['selected_thesaurus'];
	$selected_concepts=$_POST['selected_concepts'];
	$file_type=$_POST['file_type'];
	$file_name=urldecode($_POST['file_name']);
	$export_type=$_POST['export_type'];
	$patient_or_document=$_POST['patient_or_document'];
	if (is_array($selected_concepts)) {
		$list_concepts = implode(",", $selected_concepts);
	}
	if (is_array($selected_thesaurus)) {
		$list_thesaurus = implode(",", $selected_thesaurus);
	}
	$process_num=get_uniqid();
	if ($list_thesaurus!='' || $list_concepts!='') {
		passthru( "php export_data_excel.php $user_num_session $tmpresult_num $process_num $file_type $export_type \"$list_thesaurus\" \"$list_concepts\" \"$file_name\" \"$patient_or_document\">> $CHEMIN_GLOBAL_LOG/log_export_data_excel_$process_num.txt 2>&1 &");
		print "$process_num";
	} else {
		print "erreur";
	}
}

if ($_POST['action']=='verif_process_execute_process_export_data') {
	$process_num=$_POST['process_num'];
	$process=get_process ($process_num);
	$status=$process['STATUS'];
	$commentary=$process['COMMENTARY'];
	print "$status;$commentary";
}

if ($_POST['action']=='get_all_export_data') {
	$all_process=get_all_my_process ($user_num_session,'export_data');
	print "<table class=\"tablefin\">
	<thead>
	<th>".get_translation('STATUS',"Statut")."</th>
	<th>".get_translation('COMMENT',"Commentaire")."</th>
	<th>".get_translation('DATE_OF_DELETION',"Date de suppression")." Date of deletion</th>
	<th>".get_translation('TELECHARGER',"Télécharger")."</th>
	</thead>
	<tbody>";
	foreach ($all_process as $process_num) {
	
		$process=get_process  ($process_num);
		$user_num=$process['USER_NUM'];
		$status=$process['STATUS'];
		$commentary=$process['COMMENTARY'];
		$process_end_date=$process['PROCESS_END_DATE'];
		$category_process=$process['CATEGORY_PROCESS'];
		
		if ($status=='1') { // end
			$telecharger="<a href='export_process.php?process_num=$process_num' target='_blank'>".get_translation('TELECHARGER',"Télécharger")."</a>"; 
			$status=get_translation('TERMINATED',"Terminé");
		} else {
			$telecharger='';
			$status=get_translation('IN_PROGRESS',"En cours");
		}
		
		print "<tr><td>$status</td><td>$commentary</td><td>$process_end_date</td><td>$telecharger</td></tr>";
	}
	print "</tbody>";
	print "</table>";
}
?>