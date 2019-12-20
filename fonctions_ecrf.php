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
function insert_ecrf ($ecrf_num_ajout,$title_ecrf,$description_ecrf,$ecrf_url,$user_num) {
	global $dbh;
	$ecrf_num_ajout=get_uniqid();
	
	$req="insert into dwh_ecrf  (ecrf_num , title_ecrf ,description_ecrf,ecrf_date, url_ecrf,user_num) 
				values ($ecrf_num_ajout,'$title_ecrf','$description_ecrf',sysdate, '$ecrf_url',$user_num)";
	$ins=oci_parse($dbh,$req);
	oci_execute($ins) || die ("<strong style=\"color:red\">".get_translation('ERROR','erreur')." : $title_ecrf ".get_translation('NOT_SAVED','non sauvé')." $req</strong><br>");
	return $ecrf_num_ajout;
		
}

function delete_ecrf_user_right ($ecrf_num,$user_num,$right) {
	global $dbh;
	$req_right='';
	if ($right=='all') {
		$req_right="";
	} else {
		$req_right="  and right='$right' ";
	}
	$req="delete from   dwh_ecrf_user_right where  ecrf_num=$ecrf_num and user_num=$user_num $req_right";
	$del=oci_parse($dbh,$req);
	oci_execute($del) || die ("erreur : user et right non sauvé<br>");
}

function insert_ecrf_user_right ($ecrf_num,$user_num,$right) {
	global $dbh;
	$req="insert into dwh_ecrf_user_right  (ecrf_num , user_num ,right) values ($ecrf_num,$user_num,'$right')";
	$ins=oci_parse($dbh,$req);
	oci_execute($ins) || die ("<strong style=\"color:red\">erreur : user et right non sauvé</strong><br>");
}

function update_ecrf_item ($ecrf_num,$ecrf_item_num,$variable,$valeur) {
	global $dbh;
	$req="update  dwh_ecrf_item  set $variable='$valeur' where ecrf_item_num=$ecrf_item_num and ecrf_num=$ecrf_num ";
	$upd=oci_parse($dbh,$req);
	oci_execute($upd) || die ("<strong style=\"color:red\">erreur : item non modifie</strong><br>");
}

function update_ecrf_item_order ($ecrf_num,$valeur) {
	global $dbh;
	$req="update  dwh_ecrf_item  set item_order=item_order+1 where  ecrf_num=$ecrf_num and item_order>=$valeur and item_order is not null";
	$upd=oci_parse($dbh,$req);
	oci_execute($upd) || die ("<strong style=\"color:red\">erreur : item non modifie</strong><br>");
}

function update_ecrf_sub_item ($ecrf_sub_item_num,$variable,$valeur) {
	global $dbh;
	$req="update  dwh_ecrf_sub_item  set $variable='$valeur' where ecrf_sub_item_num=$ecrf_sub_item_num  ";
	$upd=oci_parse($dbh,$req);
	oci_execute($upd) || die ("<strong style=\"color:red\">erreur : item non modifie</strong><br>");
}

function delete_ecrf ($ecrf_num) {
	global $dbh;
	$req="delete from   dwh_ecrf where  ecrf_num=$ecrf_num ";
	$del=oci_parse($dbh,$req);
	oci_execute($del) || die ("erreur :  ecrf non supprimée<br>");
}

function update_ecrf ($ecrf_num,$variable,$valeur) {
	global $dbh;
	if (preg_match("/date/", $variable) ) {
		$req="update dwh_ecrf set $variable=to_date('$valeur','DD/MM/YYYY') where  ecrf_num=$ecrf_num ";
		$upd=oci_parse($dbh,$req);
		oci_execute($upd) || die ("erreur :  ecrf non modifiée<br>");
	} else {
		$req="update dwh_ecrf set $variable='$valeur' where  ecrf_num=$ecrf_num ";
		$upd=oci_parse($dbh,$req);
		oci_execute($upd) || die ("erreur :  ecrf non modifiée<br>");
	}
}

function insert_ecrf_token ($user_num, $ecrf_num,$token_ecrf) {
	global $dbh;
	$req="insert into dwh_user_ecrf (ecrf_num,user_num,token_ecrf) values ($ecrf_num,$user_num,'$token_ecrf') ";
	$upd=oci_parse($dbh,$req);
	oci_execute($upd) || die ("erreur :  ecrf non modifiée<br>");
}

function update_ecrf_token ($user_num, $ecrf_num,$token_ecrf) {
	global $dbh;
	$req="update dwh_user_ecrf set token_ecrf='$token_ecrf' where  ecrf_num=$ecrf_num and user_num=$user_num";
	$upd=oci_parse($dbh,$req);
	oci_execute($upd) || die ("erreur :  ecrf non modifiée<br>");
}

function get_max_ecrf_item_order ($ecrf_num) {
	global $dbh;
	$req="select max(item_order) as max_item_order from  dwh_ecrf_item where  ecrf_num=$ecrf_num and item_order is not null";
	$sel = oci_parse($dbh,$req);   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	$max_item_order=$r['MAX_ITEM_ORDER'];
	return $max_item_order;
}

function insert_ecrf_item ($ecrf_num ,$item_str,$item_type,$item_list,$item_ext_name, $item_ext_code,$regexp, $item_local_code,$regexp_index,$period,$item_order) {
	global $dbh;
	if ($ecrf_num!='') {
		$ecrf_item_num=get_uniqid();
	
		$req="insert into dwh_ecrf_item  (ecrf_item_num , ecrf_num ,item_str,item_type,item_list,item_ext_name,item_ext_code,regexp,item_local_code,regexp_index,period,item_order) 
					values ($ecrf_item_num,$ecrf_num,'$item_str','$item_type','$item_list','$item_ext_name','$item_ext_code','$regexp','$item_local_code','$regexp_index','$period','$item_order')";
		$ins=oci_parse($dbh,$req);
		oci_execute($ins) || die ("<strong style=\"color:red\">erreur : item non ajouté au formulaire</strong><br>");
	}
	return $ecrf_item_num;
}

function delete_ecrf_item ($ecrf_num ,$ecrf_item_num) {
	global $dbh;
	if ($ecrf_num!='') {
		$req="delete from dwh_ecrf_item where ecrf_item_num=$ecrf_item_num and ecrf_num=$ecrf_num ";
		$del=oci_parse($dbh,$req);
		oci_execute($del) || die ("<strong style=\"color:red\">erreur : item non supprime</strong><br>");
	}
}

function delete_patient_ecrf ($ecrf_num ,$patient_num,$user_num) {
	global $dbh;
	if ($ecrf_num!='' && $patient_num!='' && $user_num!='') {
		$req="delete from DWH_ECRF_ANSWER where patient_num=$patient_num and ecrf_num=$ecrf_num  and user_num=$user_num ";
		$del=oci_parse($dbh,$req);
		oci_execute($del) || die ("<strong style=\"color:red\">erreur : item non supprime</strong><br>");
	}
}

function insert_ecrf_sub_item ($ecrf_item_num ,$sub_item_local_str,$sub_item_local_code, $sub_item_ext_code,$sub_item_regexp) {
	global $dbh;
	if ($ecrf_item_num!='') {
		$ecrf_sub_item_num=get_uniqid();
	
		$req="insert into dwh_ecrf_sub_item  (ecrf_sub_item_num,ecrf_item_num ,sub_item_local_str,sub_item_local_code, sub_item_ext_code,sub_item_regexp) 
					values ($ecrf_sub_item_num,$ecrf_item_num ,'$sub_item_local_str','$sub_item_local_code', '$sub_item_ext_code','$sub_item_regexp')";
		$ins=oci_parse($dbh,$req);
		oci_execute($ins) || die ("<strong style=\"color:red\">erreur : sub item non ajouté au formulaire</strong><br>");
	}
	return $ecrf_sub_item_num;
}

function delete_ecrf_sub_item ($ecrf_sub_item_num) {
	global $dbh;
	if ($ecrf_sub_item_num!='') {
		$req="delete from dwh_ecrf_sub_item where ecrf_sub_item_num=$ecrf_sub_item_num ";
		$del=oci_parse($dbh,$req);
		oci_execute($del) || die ("<strong style=\"color:red\">erreur : item non supprime</strong><br>");
	}
}

function update_result_manual_ecrf ($patient_num,$ecrf_num,$ecrf_item_num,$user_num,$final_manual_value) {
	global $dbh;
	$requeteins="update  DWH_ECRF_ANSWER set user_value=:final_manual_value, user_value_date=sysdate where 
		patient_num=$patient_num and ecrf_num=$ecrf_num and ecrf_item_num=$ecrf_item_num and user_num=$user_num ";
	$stmt = ociparse($dbh,$requeteins);
	$rowid = ocinewdescriptor($dbh, OCI_D_LOB);
	ocibindbyname($stmt, ":final_manual_value",$final_manual_value);
	$execState = ociexecute($stmt);
	ocifreestatement($stmt);
}

function update_ecrf_validate_ecrf_item ($patient_num,$ecrf_num,$ecrf_item_num,$user_num,$validate) {
	global $dbh;
	$req="update  DWH_ECRF_ANSWER set user_validation='$validate', user_validation_date=sysdate where 
		patient_num=$patient_num and ecrf_num=$ecrf_num and ecrf_item_num=$ecrf_item_num and user_num=$user_num ";
	$upd=oci_parse($dbh,$req);
	oci_execute($upd) || die ("<strong style=\"color:red\">erreur : item non supprime</strong><br>");
}

function test_ecrf_item_order($ecrf_num,$item_order) {
	global $dbh;
	$sel=oci_parse($dbh,"select ecrf_item_num from dwh_ecrf_item where ecrf_num=$ecrf_num and item_order=$item_order ");
	oci_execute($sel);
	$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	$ecrf_item_num=$r['ECRF_ITEM_NUM'];
	return $ecrf_item_num;
}

function get_ecrf ($ecrf_num) {
	global $dbh;
	$tableau_list_ecrf = array();
	$sel_ecrf=oci_parse($dbh,"select ecrf_num,title_ecrf,description_ecrf, user_num,url_ecrf,
				to_char(ecrf_start_date,'DD/MM/YYYY') as ecrf_start_date, 
				to_char(ecrf_end_date,'DD/MM/YYYY') as ecrf_end_date,
				ecrf_start_age,
				ecrf_end_age  
			from dwh_ecrf where ecrf_num=$ecrf_num  ");
	oci_execute($sel_ecrf);
	$r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
	$ecrf_num=$r['ECRF_NUM'];
	$title_ecrf=$r['TITLE_ECRF'];
	$description_ecrf=$r['DESCRIPTION_ECRF'];
	$user_num=$r['USER_NUM'];
	$url_ecrf=$r['URL_ECRF'];
	$ecrf_start_date=$r['ECRF_START_DATE'];
	$ecrf_end_date=$r['ECRF_END_DATE'];
	$ecrf_start_age=$r['ECRF_START_AGE'];
	$ecrf_end_age=$r['ECRF_END_AGE'];
	$nb_patients=get_ecrf_nbpatients ($ecrf_num);
	$tableau_ecrf =array(
            'ecrf_num'=> $ecrf_num,
            'title_ecrf' =>  $title_ecrf,
            'description_ecrf' =>  $description_ecrf,
            'user_num' =>  $user_num,
            'url_ecrf' =>  $url_ecrf,
            'nb_patients' =>  $nb_patients,
            'ecrf_start_date' =>  $ecrf_start_date,
            'ecrf_end_date' =>  $ecrf_end_date,
            'ecrf_start_age' =>  $ecrf_start_age,
            'ecrf_end_age' =>  $ecrf_end_age
        );
      return  $tableau_ecrf;
}

function get_list_ecrf ($user_num) {
	global $dbh;
	$tableau_list_ecrf = array();
	$sel_ecrf=oci_parse($dbh,"select ecrf_num  from dwh_ecrf where 
         (user_num=$user_num or ecrf_num in (select ecrf_num from dwh_ecrf_user_right where user_num=$user_num) )
        order by title_ecrf ");
        oci_execute($sel_ecrf);
        while ($r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $ecrf_num=$r['ECRF_NUM'];
	        $tableau_list_ecrf[] =get_ecrf ($ecrf_num);
        }
      return  $tableau_list_ecrf;
}

function get_ecrf_user_right ($ecrf_num) {
	global $dbh;
	$tableau_droit_user=array();
        $sel_user=oci_parse($dbh,"select  user_num,right from dwh_ecrf_user_right  where ecrf_num=$ecrf_num  ");
        oci_execute($sel_user);
        while ($r_user=oci_fetch_array($sel_user,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$user_num_ecrf=$r_user['USER_NUM'];
		$right=$r_user['RIGHT'];
		$tableau_droit_user[$user_num_ecrf][$right]='ok';		
	}
	return $tableau_droit_user;
}


function get_ecrf_item ($ecrf_item_num) {
	global $dbh;
	$tableau_list_ecrf_items = array();
	$sel_ecrf=oci_parse($dbh,"select ecrf_item_num  ,item_str,item_type,item_list,item_ext_name,item_ext_code,regexp,item_local_code,regexp_index,period,item_order
	 from dwh_ecrf_item where ecrf_item_num=$ecrf_item_num");
        oci_execute($sel_ecrf);
        $r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
        $ecrf_item_num=$r['ECRF_ITEM_NUM'];
        $item_str=$r['ITEM_STR'];
        $item_type=$r['ITEM_TYPE'];
        $item_list=$r['ITEM_LIST'];
        $item_ext_name=$r['ITEM_EXT_NAME'];
        $item_ext_code=$r['ITEM_EXT_CODE'];
        $regexp=$r['REGEXP'];
        $regexp_index=$r['REGEXP_INDEX'];
        $item_local_code=$r['ITEM_LOCAL_CODE'];
        $period=$r['PERIOD'];
        $item_order=$r['ITEM_ORDER'];
        $tableau_list_ecrf_items =array(
            'ecrf_item_num'=> $ecrf_item_num,
            'item_str' =>  $item_str,
            'item_type' =>  $item_type,
            'item_list' =>  $item_list,
            'item_ext_name' =>  $item_ext_name,
            'item_ext_code'=>  $item_ext_code,
            'regexp'=>  $regexp,
            'regexp_index'=>  $regexp_index,
            'item_local_code'=>  $item_local_code,
            'period'=>  $period,
            'item_order'=>  $item_order
        );
      return  $tableau_list_ecrf_items;
}

function get_list_ecrf_items ($ecrf_num) {
	global $dbh;
	$tableau_list_ecrf_items = array();
	$sel_ecrf=oci_parse($dbh,"select ecrf_item_num ,item_order from dwh_ecrf_item where ecrf_num=$ecrf_num  order by item_order,ecrf_item_num ");
        oci_execute($sel_ecrf);
        while ($r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $ecrf_item_num=$r['ECRF_ITEM_NUM'];
                $tableau_list_ecrf_items[]=get_ecrf_item ($ecrf_item_num);
        }
      return  $tableau_list_ecrf_items;
}

function get_list_ecrf_sub_items ($ecrf_item_num) {

	global $dbh;
	$tableau_list_ecrf_sub_items = array();
	$sel_ecrf=oci_parse($dbh,"select ecrf_sub_item_num from dwh_ecrf_sub_item where ecrf_item_num=$ecrf_item_num  order by ecrf_sub_item_num ");
        oci_execute($sel_ecrf);
        while ($r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $ecrf_sub_item_num=$r['ECRF_SUB_ITEM_NUM'];
                $tableau_list_ecrf_sub_items[]=get_ecrf_sub_item ($ecrf_sub_item_num);
        }
      return  $tableau_list_ecrf_sub_items;
}

function get_ecrf_sub_item ($ecrf_sub_item_num) {
	global $dbh;
	$sub_item = array();
	$sel_ecrf=oci_parse($dbh,"select ecrf_sub_item_num,ecrf_item_num ,sub_item_local_str,sub_item_local_code, sub_item_ext_code,sub_item_regexp from dwh_ecrf_sub_item where ecrf_sub_item_num=$ecrf_sub_item_num  ");
        oci_execute($sel_ecrf);
        $r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
        $ecrf_sub_item_num=$r['ECRF_SUB_ITEM_NUM'];
        $sub_item_local_str=$r['SUB_ITEM_LOCAL_STR'];
        $sub_item_local_code=$r['SUB_ITEM_LOCAL_CODE'];
        $sub_item_ext_code=$r['SUB_ITEM_EXT_CODE'];
        $sub_item_regexp=$r['SUB_ITEM_REGEXP'];
     
        $sub_item =array(
            'ecrf_item_num'=> $ecrf_item_num,
            'ecrf_sub_item_num'=> $ecrf_sub_item_num,
            'sub_item_local_str' =>  $sub_item_local_str,
            'sub_item_local_code' =>  $sub_item_local_code,
            'sub_item_ext_code' =>  $sub_item_ext_code,
            'sub_item_regexp' =>  $sub_item_regexp
        );
      return  $sub_item;
}

function get_list_ecrf_patients ($ecrf_num,$user_num) {
	global $dbh;
	$req_user_num='';
	if ($user_num!='') {
		$req_user_num="and user_num=$user_num ";
	}
	$tableau_list_ecrf_patients = array();
	$sel_ecrf=oci_parse($dbh," select  
	patient_num,
	user_num,
	to_char(max(user_value_date),'DD/MM/YYYY HH24:MI') as  user_value_date_char,
	to_char(max(automated_value_date),'DD/MM/YYYY HH24:MI') as  automated_value_date_char,
	max(user_value_date) from dwh_ecrf_answer 
	where ecrf_num=$ecrf_num $req_user_num 
	group by patient_num, user_num
	order by max(user_value_date) desc ");
        oci_execute($sel_ecrf);
        while ($r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $patient_num=$r['PATIENT_NUM'];
                $user_num=$r['USER_NUM'];
                $user_value_date=$r['USER_VALUE_DATE_CHAR'];
                $automated_value_date=$r['AUTOMATED_VALUE_DATE_CHAR'];
             
	        $tableau_list_ecrf_patients[] =array(
	            'patient_num'=> $patient_num,
	            'user_num'=> $user_num,
	            'user_value_date' =>  $user_value_date,
	            'automated_value_date' =>  $automated_value_date
	        );
        }
      return  $tableau_list_ecrf_patients;
}



function get_ecrf_nbpatients ($ecrf_num) {
	global $dbh;
	$sel=oci_parse($dbh,"select count(distinct patient_num) as nb_patients from dwh_ecrf_answer where ecrf_num =$ecrf_num  ");
        oci_execute($sel);
        $r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
        $nb_patients=$r['NB_PATIENTS'];
	return $nb_patients;
}


/*function insert_ecrf_item ($ecrf_num ,$item_str,$item_type,$item_list) {
	global $dbh;
	if ($ecrf_num!='') {
		$req="insert into dwh_ecrf_item  (ecrf_item_num , ecrf_num ,item_str,item_type,item_list) 
					values (dwh_seq.nextval,$ecrf_num,'$item_str','$item_type','$item_list')";
		$ins=oci_parse($dbh,$req);
		oci_execute($ins) || die ("<strong style=\"color:red\">erreur : item non ajouté au formulaire</strong><br>");
	}
}*/


function get_ecrf_token ($user_num, $ecrf_num) {
	global $dbh;
	$tableau_ecrf = array();
	$sel_ecrf=oci_parse($dbh,"select ECRF_NUM, TOKEN_ECRF from dwh_user_ecrf where ecrf_num =$ecrf_num AND user_num=$user_num");
	oci_execute($sel_ecrf);
	$r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
	$ecrf_num=$r['ECRF_NUM'];
	$token_ecrf=$r['TOKEN_ECRF'];
	$tableau_ecrf =array(
	'ecrf_num'=> $ecrf_num,
	'token_ecrf' =>  $token_ecrf
	);
	return  $tableau_ecrf;
}



function lister_mes_ecrf_tableau ($user_num) {
	global $dbh;
	print "<table border=\"0\" class=\"dataTable\" id=\"id_tableau_liste_ecrf\"><thead><tr><td></td><td></td><td></td></tr></thead><tbody>";
	$tableau_list_ecrf=get_list_ecrf ($user_num);
	foreach ($tableau_list_ecrf as $tab_ecrf) {
                $ecrf_num=$tab_ecrf['ecrf_num'];
                $title_ecrf=$tab_ecrf['title_ecrf'];
                $description_ecrf=$tab_ecrf['description_ecrf'];
	        $nb_patients=$tab_ecrf['nb_patients'];
         	print "<tr>
	         	<td><img src=\"images/mini_ecrf.png\"></td><td class=\"ecrf lien\" onclick=\"document.location.href='mes_ecrf.php?ecrf_num_voir=$ecrf_num'\" style=\"cursor:pointer\">$title_ecrf</td>
	         	<td class=\"ecrf lien\" onclick=\"document.location.href='mes_ecrf.php?ecrf_num_voir=$ecrf_num'\" style=\"cursor:pointer\">$nb_patients</td>
         	</tr>";
		
	}
#	$sel_ecrf=oci_parse($dbh,"select title_ecrf,description_ecrf, ecrf_num from dwh_ecrf where 
#         (user_num=$user_num or ecrf_num in (select ecrf_num from dwh_ecrf_user_right where user_num=$user_num) )
#        order by title_ecrf ");
#        oci_execute($sel_ecrf);
#        while ($r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC)) {
#                $title_ecrf=$r['TITLE_ECRF'];
#                $description_ecrf=$r['DESCRIPTION_ECRF'];
#                $ecrf_num=$r['ECRF_NUM'];
#	        $nb_patients=get_ecrf_nbpatients ($ecrf_num);
#         	print "<tr>
#	         	<td><img src=\"images/mini_ecrf.png\"></td><td class=\"ecrf lien\" onclick=\"document.location.href='mes_ecrf.php?ecrf_num_voir=$ecrf_num'\" style=\"cursor:pointer\">$title_ecrf</td>
#	         	<td class=\"ecrf lien\" onclick=\"document.location.href='mes_ecrf.php?ecrf_num_voir=$ecrf_num'\" style=\"cursor:pointer\">$nb_patients</td>
#         	</tr>";
#        }
        print "</tbody></table>";
}


function extract_information_ecrf ($patient_num,$ecrf_num,$ecrf_item_num,$filter_query) {
	global $dbh;
	$i_limite=1000;
	
	$ecrf=get_ecrf ($ecrf_num);
        $ecrf_start_date=$ecrf['ecrf_start_date'];
        $ecrf_end_date=$ecrf['ecrf_end_date'];
        $ecrf_start_age=$ecrf['ecrf_start_age'];
        $ecrf_end_age=$ecrf['ecrf_end_age'];

	$query_filter_ecrf='';
	if ($ecrf_start_date!='') {
		$query_filter_ecrf.=" and document_date >= to_date('$ecrf_start_date','DD/MM/YYYY') ";
	}
	if ($ecrf_end_date!='') {
		$query_filter_ecrf.=" and document_date <= to_date('$ecrf_end_date','DD/MM/YYYY') ";
	}
	if ($ecrf_start_age!='') {
		$query_filter_ecrf.=" and age_patient >= '$ecrf_start_age' ";
	}
	if ($ecrf_end_age!='') {
		$query_filter_ecrf.=" and age_patient <= '$ecrf_end_age' ";
	}
	if ($filter_query!='') {
		$query_filter_ecrf.=" $filter_query  ";
	}
	
	$item=get_ecrf_item ($ecrf_item_num);
        $item_str=$item['item_str'];
        $item_type=$item['item_type'];
        //$item_list=$item['item_list'];
        $item_ext_name=$item['item_ext_name'];
        $item_ext_code=$item['item_ext_code'];
        $regexp=$item['regexp'];
        $regexp_index=$item['regexp_index'];
        $item_local_code=$item['item_local_code'];
        $period=$item['period'];
        
        
        
	$regexp=replace_accent($regexp);
	
	$array_list_yes=array('oui','yes','present','presente');
	$array_list_no=array('non','no','absent','absente');
	$array_list_unknown=array('inconnu','inconnue','non fait','non faite','ne sait pas','ne sais pas','nsp','na');
	$array_list_other=array('anormal','atteint','anormale','atteinte','non atteint','normal','non atteinte','normale','primaire','secondaire','tertiaire','primitif','associe');

	$array_list_research_on_question=array_merge($array_list_yes, $array_list_no,$array_list_unknown,$array_list_other);

	//$item_list_lower=strtolower($item_list);
	//$tab_item_list_lower=array_filter(explode(";",$item_list_lower)); //array_filter deletes empty cells
	$table_appercu=array();
	$table_value=array();
	$tableau_result=array();
	$tableau_result_str=array();
	$option_add_item_str='';
	if ($item_str!='') {
		if ($item_type=='list' || $item_type=='radio') {
			$tab_sub_items=get_list_ecrf_sub_items ($ecrf_item_num);
			foreach ($tab_sub_items as $sub_item) {
				$sub_item_local_str=strtolower(trim(replace_accent($sub_item['sub_item_local_str'])));
				$sub_item_local_code=$sub_item['sub_item_local_code'];
				$sub_item_regexp=$sub_item['sub_item_regexp'];
				if ($sub_item_regexp!='') {
				 	$tableau_result=search_patient_document ($patient_num,'regexp',$sub_item_regexp,0,'text','text',$query_filter_ecrf,$period);
				 	$presence='';
					foreach ($tableau_result as $tableau_result_document) {
					 	$document_num=$tableau_result_document['document_num'];
					 	if ($document_num!='') {
							$document=get_document($document_num,'text');
							$text=$document['text'];
							if ($text!='') {		
								$text=preg_replace("/\n/"," ",$text);
								$out=array();
								if (preg_match_all("/.{0,10}($sub_item_regexp).{0,10}/i","$text",$out,  PREG_PATTERN_ORDER )) {
									$j=0;
									$presence='ok';
									$ap=$out[0];
									$table_appercu[]=array('document_date'=>$document['document_date'], 'appercu'=>$ap[$j], 'document_num'=>$document_num, 'query_highlight'=>$sub_item_regexp);
								}
							}
						}
					}
					if ($presence=='ok') {
						$table_value[]=array('sous_item_value'=>$sub_item_local_str);
					}
				}
				if ($sub_item_local_code!='') {
			 		$tab_sub_item_local_code=explode(";",$sub_item_local_code);
				 	foreach ($tab_sub_item_local_code as $concept_code) {
						$t=search_patient_document ($patient_num,'data_code',$concept_code,'','','',$query_filter_ecrf,$period);
						$tableau_result=array_merge($t,$tableau_result);
					}
				 	foreach ($tableau_result as $tableau_result_document) {
				 		$document_num=$tableau_result_document['document_num'];
				 		if ($document_num!='') {
				 			$i++;
				 			if ($i<$i_limite) {
						 		$val=$tableau_result_document['value'];
						 		$info=$tableau_result_document['info'];
						 		$concept_str=$tableau_result_document['concept_str'];
						 		$concept_code=$tableau_result_document['concept_code'];
								$document=get_document($document_num,'') ;
								$table_value[]=array('sous_item_value'=>$val,'concept_str'=>$concept_str,'concept_code'=>$concept_code,'info'=>$info,'document_date'=>$document['document_date']);
								$table_appercu[]=array('document_date'=>$document['document_date'], 'appercu'=>"$concept_str $info:$val", 'document_num'=>$document_num, 'query_highlight'=>$val);
							}
						}
				 	}
				}
				
				if ($sub_item_regexp=='' && $sub_item_local_code=='') {
					// If sub item like yes / no / other etc...
					if (in_array($sub_item_local_str,$array_list_research_on_question)) {
						$item_str_for_query=nettoyer_pour_requete_patient($item_str) ;
					 	$tableau_result_aff=search_patient_document ($patient_num,'text',$item_str_for_query,1,'patient_text','text',$query_filter_ecrf,$period);
					 	if (count($tableau_result_aff)>0) {
						 	foreach ($tableau_result_aff as $tableau_result_document) {
							 	$document_num=$tableau_result_document['document_num'];
						 		if ($document_num!='') {
						 			$i++;
						 			if ($i<$i_limite) {
										$document=get_document($document_num,'text') ;
										$value='';
										$requete_json=nettoyer_pour_inserer ($item_str_for_query);
										$requete_json=replace_accent($requete_json);
										$appercu=resumer_resultat($document['text'],"{'query':'$requete_json','type':'fulltext','synonym':''}",array(),'ecrf');
										$query_highlight=$item_str_for_query;
										if ($appercu=='') {
											$appercu=resumer_resultat($document['text'],"{'query':'$requete_json','type':'fulltext','synonym':''}",array(),'ecrf');
											$query_highlight=$item_str_for_query;
										}
										$table_appercu[]=array('document_date'=>$document['document_date'], 'appercu'=>$appercu, 'document_num'=>$document_num, 'query_highlight'=>$query_highlight);
										$presence='ok';
									}
								}
							}
							if (count($tableau_result_aff)>0) {
								if (in_array($sub_item_local_str,$array_list_yes) || in_array($sub_item_local_str,$array_list_other)) {
									$table_value[]=array('sous_item_value'=>$sub_item_local_str);
								}
							}
						}
						if (count($tableau_result_aff)==0) {
						 	$tableau_result_neg=search_patient_document ($patient_num,'text',$item_str_for_query,-1,'patient_text','text',$query_filter_ecrf,$period);
						 	foreach ($tableau_result_neg as $tableau_result_document) {
							 	$document_num=$tableau_result_document['document_num'];
						 		if ($document_num!='') {
						 			$i++;
						 			if ($i<$i_limite) {
										$document=get_document($document_num,'text') ;
										$value='';
										$requete_json=nettoyer_pour_inserer ($item_str_for_query);
										$requete_json=replace_accent($requete_json);
										$appercu=resumer_resultat($document['text'],"{'query':'$requete_json','type':'fulltext','synonym':''}",array(),'ecrf');
										$query_highlight=$item_str_for_query;
										if ($appercu=='') {
											$appercu=resumer_resultat($document['text'],"{'query':'$requete_json','type':'fulltext','synonym':''}",array(),'ecrf');
											$query_highlight=$item_str_for_query;
										}
										$table_appercu[]=array('document_date'=>$document['document_date'], 'appercu'=>$appercu, 'document_num'=>$document_num, 'query_highlight'=>$query_highlight);
										$presence='ok';
									}
								}
							}
							if (count($tableau_result_neg)>0) {
								if (in_array($sub_item_local_str,$array_list_no)) {
									$table_value[]=array('sous_item_value'=>$sub_item_local_str);
								}
							}
						}
					} else {
						if (count($tableau_result_str)==0) {
							$item_str_for_query=nettoyer_pour_requete_patient($item_str) ;
							$tableau_result_str=search_patient_document ($patient_num,'text',$item_str_for_query,0,'text','text',$query_filter_ecrf,$period);
							if (count($tableau_result_str)>0) {
								$option_add_item_str='add_item_str';
							} else {
								$option_add_item_str='not_add_item_str';
							}
						}
					 	if ($option_add_item_str=='add_item_str') {
					 		$item_str_refait="$item_str $sub_item_local_str";
					 	} else {
					 		$item_str_refait="$sub_item_local_str";
					 	}
				 		$i=0;
						$item_str_for_query=nettoyer_pour_requete_patient($item_str_refait) ;
					 	$tableau_result=search_patient_document ($patient_num,'text',$item_str_for_query,0,'text','text',$query_filter_ecrf,$period);
					 	if (count($tableau_result)==0 && $option=='add_item_str') {
							$item_str_for_query=nettoyer_pour_requete_patient($sub_item_local_str) ;
						 	$tableau_result=search_patient_document ($patient_num,'text',$item_str_for_query,0,'text','text',$query_filter_ecrf,$period);
					 	}
					 	foreach ($tableau_result as $tableau_result_document) {
					 		$document_num=$tableau_result_document['document_num'];
					 		if ($document_num!='') {
					 			$i++;
					 			if ($i<$i_limite) {
									$document=get_document($document_num,'text') ;
									$requete_json=nettoyer_pour_inserer ($item_str_for_query);
									$requete_json=replace_accent($requete_json);
									$appercu=resumer_resultat($document['text'],"{'query':'$requete_json','type':'fulltext','synonym':''}",array(),'ecrf');
									$query_highlight=$item_str_for_query;
									if ($appercu=='') {
										$appercu=resumer_resultat($document['text'],"{'query':'$requete_json','type':'fulltext','synonym':''}",array(),'ecrf');
										$query_highlight=$item_str_for_query;
									}
									$table_appercu[]=array('document_date'=>$document['document_date'], 'appercu'=>$appercu, 'document_num'=>$document_num, 'query_highlight'=>$query_highlight);
								}
							}
						}
						if (count($tableau_result)>0) {
							$table_value[]=array('sous_item_value'=>$sub_item_local_str);
						}
					}
				}
			}
			

		
		} else {
			$value_global='';
			
		 	$i=0;
			if ($item_local_code!='') {
		 		$tab_item_local_code=explode(";",$item_local_code);
			 	foreach ($tab_item_local_code as $concept_code) {
					$t=search_patient_document ($patient_num,'data_code',$concept_code,'','','',$query_filter_ecrf,$period);
					$tableau_result=array_merge($t,$tableau_result);
				}
			 	foreach ($tableau_result as $tableau_result_document) {
				 	$document_num=$tableau_result_document['document_num'];
				 	if ($document_num!='') {
			 			$i++;
			 			if ($i<$i_limite) {
					 		$val=$tableau_result_document['value'];
					 		$info=$tableau_result_document['info'];
					 		$concept_str=$tableau_result_document['concept_str'];
					 		$concept_code=$tableau_result_document['concept_code'];
							$document=get_document($document_num,'') ;
							$table_value[]=array('sous_item_value'=>$val,'concept_str'=>$concept_str,'concept_code'=>$concept_code,'info'=>$info,'document_date'=>$document['document_date']);
							$table_appercu[]=array('document_date'=>$document['document_date'], 'appercu'=>"$concept_str $info:$val", 'document_num'=>$document_num, 'query_highlight'=>$concept_str);
						}
					}
			 	}
			}
			if (count($table_value)==0 && $regexp!='') {
			 	$tableau_result=search_patient_document ($patient_num,'regexp',$regexp,0,'text','text',$query_filter_ecrf,$period);
				foreach ($tableau_result as $tableau_result_document) {
				 	$document_num=$tableau_result_document['document_num'];
				 	if ($document_num!='') {
						$document=get_document($document_num,'text');
						$text=$document['text'];
						if ($text!='') {		
							$text=preg_replace("/\n/"," ",$text);
							$out=array();
							
							$regexp_clean=trim(clean_for_regexp ($regexp));
							if (preg_match_all("/.{0,10}$regexp_clean.{0,10}/i","$text",$out,  PREG_PATTERN_ORDER )) {
								$j=0;
								if ($regexp_index!='') {
									foreach ($out[$regexp_index] as $value) {
										$ap=$out[0];
										$table_value[]=array('sous_item_value'=>$value,'document_date'=>$document['document_date']);
										$table_appercu[]=array('document_date'=>$document['document_date'], 'appercu'=>$ap[$j], 'document_num'=>$document_num, 'query_highlight'=>$regexp);
										$j++;
									}
								} else {
									foreach ($out as $regexp_index => $value) {
										$ap=$out[0];
										//$table_value[]=array('sous_item_value'=>"",'document_date'=>$document['document_date']);
										$table_appercu[]=array('document_date'=>$document['document_date'], 'appercu'=>$ap[$j], 'document_num'=>$document_num, 'query_highlight'=>$regexp);
										$j++;
									}
								}
							}
						}
					}
				}
		 	}
			
			if (count($table_value)==0) {
				$tableau_data_struct=search_patient_document ($patient_num,'data',$item_str,'','','',$query_filter_ecrf,$period);
			 	foreach ($tableau_data_struct as $tableau_result_document) {
				 	$document_num=$tableau_result_document['document_num'];
				 	if ($document_num!='') {
			 			$i++;
			 			if ($i<$i_limite) {
							$document=get_document($document_num,'') ;
					 		$value=$tableau_result_document['value'];
					 		$concept_str=$tableau_result_document['concept_str'];
					 		$concept_code=$tableau_result_document['concept_code'];
					 		$info=$tableau_result_document['info'];
							//$value_global.="data:".$document['document_date'].":$info:$val;";
							$table_value[]=array('sous_item_value'=>$value,'concept_str'=>$concept_str,'concept_code'=>$concept_code,'info'=>$info,'document_date'=>$document['document_date']);
							$table_appercu[]=array('document_date'=>$document['document_date'], 'appercu'=>"$concept_str $info:$value", 'document_num'=>$document_num, 'query_highlight'=>$val);
						}
					}
			 	}
			}
		}
	}
	return array($table_value,$table_appercu);
	
}

function affiche_liste_user_ecrf($ecrf_num,$user_num) {
	global $dbh,$tableau_ecrf_right;
	if ($ecrf_num!='') {
		$autorisation_ecrf_modifier=autorisation_ecrf_modifier ($ecrf_num,$user_num);
		$autorisation_ecrf_supprimer=autorisation_ecrf_supprimer ($ecrf_num,$user_num);
		
		
		print "<table border=\"0\" id=\"id_tableau_user_ecrf\" class=\"tablefin\" cellpadding=\"3\">
			<tr><th></th><th>Utiliser</th><th>Modifier</th>";
			if ($autorisation_ecrf_modifier=='ok') {
				print "<th>X</th>";
			}
			print "</tr>";
		$tableau_droit_all_user=get_ecrf_user_right ($ecrf_num);
		foreach ($tableau_droit_all_user as $user_num_ecrf => $tab_user_ecrf) {
			$firstname_lastname_createur= get_user_information ($user_num_ecrf,'pn');
			$tableau_droit_user=array();
			foreach ($tableau_droit_all_user[$user_num_ecrf] as $right => $ok) {
				$tableau_droit_user[$right]='ok';
			}
			
			print "<tr><td><strong> $firstname_lastname_createur</strong></td>";
			foreach ($tableau_ecrf_right as $right) {
				if ($autorisation_ecrf_modifier=='ok') {
					if ($tableau_droit_user[$right]=='ok') {
						$checked='checked';
					} else {
						$checked='';
					}
					print "<td><input id=\"id_droit_".$user_num_ecrf."_".$right."\" type=checkbox onclick=\"ajouter_droit_ecrf('$ecrf_num','$user_num_ecrf','$right');\" $checked></td>";
				} else {
					if ($tableau_droit_user[$right]=='ok') {
						print "<td>x</td>";
					} else {
						print "<td>-</td>";
					}
				}
			}
			if ($autorisation_ecrf_modifier=='ok') {
				print "<td><img src=\"images/mini_poubelle.png\" onclick=\"supprimer_user_ecrf($user_num_ecrf,$ecrf_num);\" style=\"cursor:pointer;\"></td>";
			}
			print "</tr>";
	        }
		print "	</table>";
	}
}


function display_list_sub_item($ecrf_item_num,$variable,$display) {
	$list_sub_item='';
	if ($display!='input') {
		$list_sub_item="<ul class='li_ecrf'>";
	}
	$list_ecrf_sub_items=get_list_ecrf_sub_items($ecrf_item_num);
	foreach ($list_ecrf_sub_items as $sub_item) {
		$ecrf_sub_item_num=$sub_item['ecrf_sub_item_num'];
		$value=$sub_item[$variable];
		if ($display=='input') {
			if ($variable=='sub_item_regexp') {
				$size=50;
			} else {
				$size=30;
			}
			$list_sub_item.="<input type='text' size='$size' class='form' value=\"$value\" id='id_input_".$variable."_".$ecrf_sub_item_num."'>"."<br>";
		} else {
			$list_sub_item.="<li class='li_ecrf'>$value</li>";
		
		}
	}
	if ($display!='input') {
		$list_sub_item.="</ul>";
	}
	return $list_sub_item;
}

function list_ecrf_item ($ecrf_num,$user_num) {
	global $dbh;
	
	$list_ecrf_item="";
	if ($ecrf_num!='') {
		$autorisation_ecrf_voir=autorisation_ecrf_voir ($ecrf_num,$user_num);
		$autorisation_ecrf_modifier=autorisation_ecrf_modifier ($ecrf_num,$user_num);
		
		if ($autorisation_ecrf_voir=='ok') {
			$list_ecrf_item.= "<table border=\"0\" id=\"id_tableau_user_ecrf\" class=\"tablefin\" cellpadding=\"3\">
			<thead>
			<tr>
			<th>".get_translation('ORDER','Ordre')."</th>
			<th>".get_translation('QUESTION','Question')."</th>
			<th>".get_translation('TYPE','Type')."</th>
			<th>".get_translation('VALUE','Valeurs')."</th>
			<th>".get_translation('PATTERN','Pattern')."</th>
			<th>".get_translation('PATTERN_INDEX','Pattern Index')."</th>
			<th>".get_translation('LOCAL_CODES','Local Codes')."</th>
			<th>".get_translation('EXT_NAME','Noms Externes')."</th>
			<th>".get_translation('EXT_CODES','Codes Externes')."</th>
			<th>".get_translation('PERIOD','Période')."</th>
			";
			if ($autorisation_ecrf_modifier=='ok') {
				$list_ecrf_item.= "<th>Save</th>";
				$list_ecrf_item.= "<th>X</th>";
			}
			$list_ecrf_item.= "</tr></thead>";
		
		
			$list_ecrf_items=get_list_ecrf_items($ecrf_num);
			foreach ($list_ecrf_items as $item) {
				$ecrf_item_num=$item['ecrf_item_num'];
				$item_str=$item['item_str'];
				$item_type=$item['item_type'];
				$item_list=$item['item_list'];
				$item_ext_name=$item['item_ext_name'];
				$item_ext_code=$item['item_ext_code'];
				$regexp=$item['regexp'];
				$regexp_index=$item['regexp_index'];
				$item_local_code=$item['item_local_code'];
				$period=$item['period'];
				$item_order=$item['item_order'];
				if ($period=='') {
					$period='all';
				}
				
				$list_sub_item_local_str='';
				$list_sub_item_local_code='';
				$list_sub_item_regexp='';
				$list_sub_item_ext_code='';
				
				$list_input_sub_item_local_str='';
				$list_input_sub_item_local_code='';
				$list_input_sub_item_regexp='';
				$list_input_sub_item_ext_code='';
				
				$list_sub_item_local_str=display_list_sub_item($ecrf_item_num,'sub_item_local_str','see');
				$list_sub_item_local_code=display_list_sub_item($ecrf_item_num,'sub_item_local_code','see');
				$list_sub_item_regexp=display_list_sub_item($ecrf_item_num,'sub_item_regexp','see');
				$ecrf_sub_item_num=display_list_sub_item($ecrf_item_num,'ecrf_sub_item_num','see');
				$list_sub_item_ext_code=display_list_sub_item($ecrf_item_num,'sub_item_ext_code','see');
				
				$list_input_sub_item_local_str=display_list_sub_item($ecrf_item_num,'sub_item_local_str','input');
				$list_input_sub_item_local_code=display_list_sub_item($ecrf_item_num,'sub_item_local_code','input');
				$list_input_sub_item_regexp=display_list_sub_item($ecrf_item_num,'sub_item_regexp','input');
				$list_input_sub_item_ext_code=display_list_sub_item($ecrf_item_num,'sub_item_ext_code','input');
				
				$select_period=array();
				$select_period[$period]="selected";
				
				$select_item_type=array();
				$select_item_type[$item_type]="selected";

				if ($autorisation_ecrf_modifier=='ok') {
					$list_ecrf_item.= "<tr id=\"id_tr_ecrf_item_$ecrf_item_num\">";
					
					$list_ecrf_item.= "<td  style=\"vertical-align:top;\" onclick=\"modify_ecrf_item($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_item_order_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$item_order</span>
							<span id=\"id_span_modif_item_order_$ecrf_item_num\" style=\"display:none;\" class=\"id_span_modif_ecrf_item_$ecrf_item_num\">
								<input type='text' class='form' size=\"2\" value=\"$item_order\" id=\"id_input_item_order_$ecrf_item_num\" onkeypress=\"if(event.keyCode==13) {save_ecrf_item_all($ecrf_num,$ecrf_item_num);return false;}\" />
							</span>
						</td>";
						
					$list_ecrf_item.= "<td style=\"width:265px;vertical-align:top;\" onclick=\"modify_ecrf_item($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_item_str_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$item_str</span>
							<span id=\"id_span_modif_item_str_$ecrf_item_num\" style=\"display:none;\" class=\"id_span_modif_ecrf_item_$ecrf_item_num\">
								<textarea class='form' cols=\"30\" rows=\"2\" id=\"id_input_item_str_$ecrf_item_num\" onkeypress=\"if(event.keyCode==13) {save_ecrf_item_all($ecrf_num,$ecrf_item_num);return false;} \">$item_str</textarea>
							</span>
						</td>";
					
					//<input type=text size=\"12\" id=\"id_input_item_type_$ecrf_item_num\" onblur=\"save_ecrf_item($ecrf_num,$ecrf_item_num,'item_type');\" onkeypress=\"if(event.keyCode==13) {blur();return false;}\" value=\"$item_type\">

					$list_ecrf_item.= "<td style=\"width:147px;vertical-align:top;\" onclick=\"modify_ecrf_item($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_item_type_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$item_type</span>
							<span id=\"id_span_modif_item_type_$ecrf_item_num\" style=\"display:none;\" class=\"id_span_modif_ecrf_item_$ecrf_item_num\">
								<select id=\"id_input_item_type_$ecrf_item_num\" class=\"form\" onchange=\"adapt_ecrf_form($ecrf_item_num);\">
									<option value='numeric' ".$select_item_type['numeric'].">".get_translation('NUMBER','Nombre')."</option>
									<option value='text' ".$select_item_type['text'].">".get_translation('TEXT','Texte libre')."</option>
									<option value='list' ".$select_item_type['list'].">".get_translation('MULTIPLE_CHOICE','Choix multiple')."</option>
									<option value='radio' ".$select_item_type['radio'].">".get_translation('UNIQUE_CHOICE','Choix unique')."</option>
									<option value='date' ".$select_item_type['date'].">".get_translation('DATE','Date')."</option>
								</select>
							</span>
						</td>";
						
					$list_ecrf_item.= "<td style=\"width:265px;vertical-align:top;\"  onclick=\"modify_ecrf_item($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_item_list_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$item_list</span>
							<span id=\"id_span_sub_item_local_str_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$list_sub_item_local_str</span>
							
							<span id=\"id_span_modif_item_list_$ecrf_item_num\" style=\"display:none;\" class=\"id_span_modif_ecrf_item_$ecrf_item_num\">
								<textarea class='form' cols=\"30\" rows=\"2\" id=\"id_input_item_list_$ecrf_item_num\" onkeypress=\"if(event.keyCode==13) {save_ecrf_item_all($ecrf_num,$ecrf_item_num);return false;}\">$item_list</textarea>
							</span>
							<span id=\"id_span_modif_sub_item_local_str_$ecrf_item_num\" style=\"display:none;\" class=\"id_span_modif_ecrf_item_$ecrf_item_num\">
								<div id=\"id_div_modif_sub_item_local_str_$ecrf_item_num\">
									$list_input_sub_item_local_str
								</div>
								<span onclick=\"add_sub_item('$ecrf_num','$ecrf_item_num');\" class=\"link\">+ Ajouter un sous item</span>
							</span>
						</td>";			
						
					$list_ecrf_item.= "<td style=\"width:265px;vertical-align:top;\"  onclick=\"modify_ecrf_item($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_regexp_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$regexp</span>
							<span id=\"id_span_sub_item_regexp_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$list_sub_item_regexp</span>
							
							<span id=\"id_span_modif_regexp_$ecrf_item_num\" style=\"display:none;\" class=\"id_span_modif_ecrf_item_$ecrf_item_num\">
								<textarea class='form' cols=\"30\" rows=\"2\" id=\"id_input_regexp_$ecrf_item_num\" onkeypress=\"if(event.keyCode==13) {save_ecrf_item_all($ecrf_num,$ecrf_item_num);return false;}\">$regexp</textarea>
							</span>
							<span id=\"id_span_modif_sub_item_regexp_$ecrf_item_num\" style=\"display:none;\" class=\"id_span_modif_ecrf_item_$ecrf_item_num\">
								<div id=\"id_div_modif_sub_item_regexp_$ecrf_item_num\">
									$list_input_sub_item_regexp
								</div>
							</span>
						</td>";
						
					$list_ecrf_item.= "<td  style=\"vertical-align:top;\" onclick=\"modify_ecrf_item($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_regexp_index_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$regexp_index</span>
							<span id=\"id_span_modif_regexp_index_$ecrf_item_num\" style=\"display:none;\" class=\"id_span_modif_ecrf_item_$ecrf_item_num\">
								<input type='text' class='form' size=\"2\" value=\"$regexp_index\" id=\"id_input_regexp_index_$ecrf_item_num\" onkeypress=\"if(event.keyCode==13) {save_ecrf_item_all($ecrf_num,$ecrf_item_num);return false;}\" />
							</span>
						</td>";
						
					$list_ecrf_item.= "<td style=\"width:265px;vertical-align:top;\"  onclick=\"modify_ecrf_item($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_item_local_code_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$item_local_code</span>
							<span id=\"id_span_sub_item_local_code_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$list_sub_item_local_code</span>
							<span id=\"id_span_modif_item_local_code_$ecrf_item_num\" style=\"display:none;\" class=\"id_span_modif_ecrf_item_$ecrf_item_num\">
								<input type=\"text\" class='form' cols=\"15\" value=\"$item_local_code\" id=\"id_input_item_local_code_$ecrf_item_num\" onkeypress=\"if(event.keyCode==13) {save_ecrf_item_all($ecrf_num,$ecrf_item_num);return false;}\">
							</span>
							<span id=\"id_span_modif_sub_item_local_code_$ecrf_item_num\" style=\"display:none;\" class=\"id_span_modif_ecrf_item_$ecrf_item_num\">
								<div id=\"id_div_modif_sub_item_local_code_$ecrf_item_num\">
									$list_input_sub_item_local_code
								</div>
							</span>
						</td>";
						
					$list_ecrf_item.= "<td style=\"width:265px;vertical-align:top;\"  onclick=\"modify_ecrf_item($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_item_ext_name_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$item_ext_name</span>
							<span id=\"id_span_modif_item_ext_name_$ecrf_item_num\" style=\"display:none;\" class=\"id_span_modif_ecrf_item_$ecrf_item_num\">
								<input type=\"text\" class='form' cols=\"15\" value=\"$item_ext_name\" id=\"id_input_item_ext_name_$ecrf_item_num\" onkeypress=\"if(event.keyCode==13) {save_ecrf_item_all($ecrf_num,$ecrf_item_num);return false;}\">
							</span>
						</td>";

					$list_ecrf_item.= "<td style=\"width:265px;vertical-align:top;\"  onclick=\"modify_ecrf_item($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_item_ext_code_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$item_ext_code</span>
							<span id=\"id_span_sub_item_ext_code_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$list_sub_item_ext_code</span>
							<span id=\"id_span_modif_item_ext_code_$ecrf_item_num\" style=\"display:none;\" class=\"id_span_modif_ecrf_item_$ecrf_item_num\">
								<input type=\"text\" class='form' cols=\"15\" value=\"$item_ext_code\" id=\"id_input_item_ext_code_$ecrf_item_num\" onkeypress=\"if(event.keyCode==13) {save_ecrf_item_all($ecrf_num,$ecrf_item_num);return false;}\">
							</span>
							<span id=\"id_span_modif_sub_item_ext_code_$ecrf_item_num\" style=\"display:none;\" class=\"id_span_modif_ecrf_item_$ecrf_item_num\">
								<div id=\"id_div_modif_sub_item_ext_code_$ecrf_item_num\">
									$list_input_sub_item_ext_code
								</div>
							</span>
						</td>";
						
					$list_ecrf_item.= "<td style=\"width:265px;vertical-align:top;\"  onclick=\"modify_ecrf_item($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_period_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$period</span>
							<span id=\"id_span_modif_period_$ecrf_item_num\" style=\"display:none;\" class=\"id_span_modif_ecrf_item_$ecrf_item_num\">
								<select id=\"id_input_period_$ecrf_item_num\" onblur=\"save_ecrf_item($ecrf_num,$ecrf_item_num,'period');\" class='form'>
									<option value='last' ".$select_period['last'].">".get_translation('LAST_VALUE','Dernière valeur')."</option>
									<option value='first' ".$select_period['first'].">".get_translation('FIRST_VALUE','Première valeur')."</option>
									<option value='all' ".$select_period['all'].">".get_translation('ALL_VALUES','Toutes les valeurs')."</option>
								</select>
							</span>
						</td>";
						
					$list_ecrf_item.= "<td><span style=\"display:none;\" class=\"id_span_modif_ecrf_item_$ecrf_item_num\"><input type=\"button\" value=\"save\" onclick=\"save_ecrf_item_all($ecrf_num,$ecrf_item_num);\"></span></td>";
					
					$list_ecrf_item.= "<td><img src=\"images/mini_poubelle.png\" onclick=\"delete_item_ecrf($ecrf_num,$ecrf_item_num);\" style=\"cursor:pointer;\"></td>";
					$list_ecrf_item.= "</tr>";	
				} else {
					$list_ecrf_item.= "<tr>
						<td>$item_order</td>
						<td>$item_str</td>
						<td>$item_type</td>
						<td>$item_list</td>
						<td>$regexp</td>
						<td>$regexp_index</td>
						<td>$item_local_code</td>
						<td>$item_ext_name</td>
						<td>$item_ext_code</td>
						<td>$period</td>
					</tr>";	
				}			
			}
			$list_ecrf_item.= "</table>";
		}
	}
	return $list_ecrf_item;
}

function get_patient_ecrf_data_from_ext_database ($user_num,$ecrf_num,$patient_num) {
	global $dbh;
	$res_patient_ecrf=array();
	$tableau_list_ecrf_items=get_list_ecrf_items ($ecrf_num);
	$tab_token = get_ecrf_token($user_num,$ecrf_num);
	$token_ecrf=$tab_token['token_ecrf'];
	if ($token_ecrf!='') {
		$patient=get_patient ($patient_num,'');
		$patient_ecrf_id = getEcrfId_from_Inclusion($user_num, $patient['HOSPITAL_PATIENT_ID'], $ecrf_num);
		$patient_ecrf_data_redcap = getPatientDataFromREDCap($user_num,$ecrf_num,$patient_ecrf_id['ecrf_id']);
		
		foreach ($tableau_list_ecrf_items as $item) {
	                $ecrf_item_num=$item['ecrf_item_num'];
		        $item_type=$item['item_type'];
		        $item_ext_name=$item['item_ext_name'];
		        $item_ext_code=$item['item_ext_code'];
      			if ($item_type=='list' || $item_type=='radio') {
				$tab_sub_items=get_list_ecrf_sub_items ($ecrf_item_num);
		 		foreach ($tab_sub_items as $sub_item) {
					$sub_item_ext_code=$sub_item['sub_item_ext_code'];
		 			$ecrf_sub_item_num=$sub_item['ecrf_sub_item_num'];
	              			if(isset($patient_ecrf_data_redcap[0][$item_ext_name."___".$sub_item_ext_code]) && $patient_ecrf_data_redcap[0][$item_ext_name."___".$sub_item_ext_code] != "") {
		 				if ($patient_ecrf_data_redcap[0][$item_ext_name."___".$sub_item_ext_code] == '1') {
		 					$res_patient_ecrf[$ecrf_item_num.".".$ecrf_sub_item_num]=1;
		 				}
		 			}
		 		}
      			} else {
      				if(isset($patient_ecrf_data_redcap[0][$item_ext_name])) {
		 			$res_patient_ecrf[$ecrf_item_num]=$patient_ecrf_data_redcap[0][$item_ext_name];
      				}
      			}
		}
	} 
	return $res_patient_ecrf;
}

function get_patient_ecrf_data_from_manual ($user_num,$ecrf_num,$patient_num) {
	global $dbh;
	$res_patient_ecrf=array();
	$tableau_list_ecrf_items=get_list_ecrf_items ($ecrf_num);
	
	$patient_ecrf_data_manual = get_ecrf_patient_manual_data($user_num,$ecrf_num, $patient_num);
	foreach ($tableau_list_ecrf_items as $item) {
                $ecrf_item_num=$item['ecrf_item_num'];
	        $item_type=$item['item_type'];
	        $item_ext_name=$item['item_ext_name'];
	        $item_ext_code=$item['item_ext_code'];
		if ($item_type=='list' || $item_type=='radio') {
			$tab_sub_items=get_list_ecrf_sub_items ($ecrf_item_num);
	 		foreach ($tab_sub_items as $sub_item) {
				$sub_item_ext_code=$sub_item['sub_item_ext_code'];
	 			$ecrf_sub_item_num=$sub_item['ecrf_sub_item_num'];
	 			$sub_item_local_str=$sub_item['sub_item_local_str'];
	 			if (strpos (strtolower(" ".$patient_ecrf_data_manual[$ecrf_item_num])." ",strtolower($sub_item_local_str))) {
	 				$res_patient_ecrf[$ecrf_item_num.".".$ecrf_sub_item_num]=1;
	 			}
	 		}
		} else {
	 		$res_patient_ecrf[$ecrf_item_num]=$patient_ecrf_data_manual[$ecrf_item_num];
		}
	}
	return $res_patient_ecrf;
}

function getPatientDataFromREDCap($user_num, $ecrf_num, $ecrf_id) {
	$ecrf_info = get_ecrf_token($user_num, $ecrf_num);
	$ercf = get_ecrf($user_num);
	$token_ecrf = $ecrf_info{'token_ecrf'};
	$url_ecrf = $ercf{'url_ecrf'};
	if ($url_ecrf!='') {
		$records = [];
		array_push($records, $ecrf_id);
		$data = array(
		    'token' => $token_ecrf,
		    'content' => 'record',
		    'records' => $records,
		    'format' => 'json',
		    'returnFormat' => 'json'
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_ecrf); // TODO UPDATE FROM DB
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
		$output = curl_exec($ch);
		$obj = json_decode($output, true);
		curl_close($ch);
		return $obj;
	}
}

function get_ecrf_patient_manual_data($user_num,$ecrf_num, $patient_num) {
	global $dbh;
	$patient_ecrf_data_manual = array();
	$sel_ecrf=oci_parse($dbh,"select ecrf_item_num,user_num,user_value from DWH_ECRF_ANSWER where ecrf_num=$ecrf_num and patient_num=$patient_num and user_num=$user_num order by ecrf_item_num ");
	oci_execute($sel_ecrf);
	while ($r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$ecrf_item_num=$r['ECRF_ITEM_NUM'];
		$user_value=$r['USER_VALUE'];
		$user_validation=$r['USER_VALIDATION'];
		$user_validation_date=$r['USER_VALIDATION_DATE'];
		if ($user_value!='') {
			$patient_ecrf_data_manual[$ecrf_item_num]=$user_value;
		}
	}
      return  $patient_ecrf_data_manual;
}

function get_ecrf_patient_validation_answer ($patient_num,$ecrf_num,$ecrf_item_num,$user_num) {
	global $dbh;
	$res = array();
	$sel_ecrf=oci_parse($dbh,"select ecrf_item_num,user_num,user_value,user_validation,to_char(user_validation_date,'DD/MM/YYYY HH24:MI') as user_validation_date from DWH_ECRF_ANSWER where ecrf_num=$ecrf_num and ecrf_item_num=$ecrf_item_num and patient_num=$patient_num and user_num=$user_num  ");
	oci_execute($sel_ecrf);
	$r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
	$ecrf_item_num=$r['ECRF_ITEM_NUM'];
	$user_value=$r['USER_VALUE'];
	$res['user_validation']=$r['USER_VALIDATION'];
	$res['user_validation_date']=$r['USER_VALIDATION_DATE'];
	return  $res;
}

function get_ecrf_patient_max_user_date($user_num,$ecrf_num, $patient_num) {
	global $dbh;
	$sel_ecrf=oci_parse($dbh,"select to_char(max(user_value_date),'DD/MM/YYYY HH24:MI') as max_date from DWH_ECRF_ANSWER where ecrf_num=$ecrf_num and patient_num=$patient_num and user_num=$user_num  and user_value_date is not null ");
	oci_execute($sel_ecrf);
	$r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
	$max_date=$r['MAX_DATE'];
	return  $max_date;
}

function get_ecrf_patient_automated_data($user_num,$ecrf_num, $patient_num) {
	global $dbh;
	$patient_ecrf_data_auto = array();
	$sel_ecrf=oci_parse($dbh,"select ecrf_item_num,user_num,automated_value from DWH_ECRF_ANSWER where ecrf_num=$ecrf_num and patient_num=$patient_num and user_num=$user_num order by ecrf_item_num ");
	oci_execute($sel_ecrf);
	while ($r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$ecrf_item_num=$r['ECRF_ITEM_NUM'];
		$automated_value=$r['AUTOMATED_VALUE'];
		if ($automated_value!='') {
			$patient_ecrf_data_auto[$ecrf_item_num]=$automated_value;
		}
	}
      return  $patient_ecrf_data_auto;
}

 function insert_result_auto_ecrf ($patient_num,$ecrf_num,$ecrf_item_num,$user_num,$table_value,$table_appercu,$final_automatic_value) {
	global $dbh;

	$table_final=array("value"=>$table_value,"overview"=>$table_appercu);
	$json_final=serialize($table_final);
	
	$sel_ecrf=oci_parse($dbh,"select count(*) as verif from DWH_ECRF_ANSWER where ecrf_num=$ecrf_num and patient_num=$patient_num and ecrf_item_num=$ecrf_item_num and user_num=$user_num");
	oci_execute($sel_ecrf);
	$r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
	$verif=$r['VERIF'];
	if ($verif==0) {
		$requeteins="insert into DWH_ECRF_ANSWER (patient_num,ecrf_num,ecrf_item_num,user_num,automated_value, ecrf_item_extract ,automated_value_date) values 
					($patient_num,$ecrf_num,$ecrf_item_num,$user_num,:final_automatic_value,:json_final,sysdate) ";
                $stmt = ociparse($dbh,$requeteins);
                $rowid = ocinewdescriptor($dbh, OCI_D_LOB);
                ocibindbyname($stmt, ":json_final",$json_final);
                ocibindbyname($stmt, ":final_automatic_value",$final_automatic_value);
                $execState = ociexecute($stmt);
                ocifreestatement($stmt);
	} else {
		$requeteins="update  DWH_ECRF_ANSWER set automated_value=:final_automatic_value,ecrf_item_extract=:json_final ,automated_value_date=sysdate where 
			patient_num=$patient_num and ecrf_num=$ecrf_num and ecrf_item_num=$ecrf_item_num and user_num=$user_num ";
                $stmt = ociparse($dbh,$requeteins);
                $rowid = ocinewdescriptor($dbh, OCI_D_LOB);
                ocibindbyname($stmt, ":json_final",$json_final);
                ocibindbyname($stmt, ":final_automatic_value",$final_automatic_value);
                $execState = ociexecute($stmt);
                ocifreestatement($stmt);
	}
}


function affiche_liste_document_patient_ecrf($patient_num,$requete) {
	global $dbh,$datamart_num,$user_num_session,$document_origin_code_labo;
	$autorisation_voir_patient=autorisation_voir_patient ($patient_num,$user_num_session);
	if ($autorisation_voir_patient=='ok') {
		$req="";
		$req_option="";
		if (preg_match("/[[|?+]/",$requete)) {
			$affichage_tableau_expression_regulier='ok';
			$cellspacing="cellspacing=0 cellpadding=2";
			print "<a href=\"#\" onclick=\"fnSelect('id_tableau_liste_document');return false;\">".get_translation('SELECT_TABLE','Sélectionner le tableau')."</a><br>";
			
		} else {
			$affichage_tableau_expression_regulier='';
			$cellspacing="";
		}
	        $tableau_liste_synonyme=array();
		if ($requete!='') {
			if (preg_match("/[[|?+]/",$requete)) {
				//$req="and document_num in (select document_num from dwh_text where patient_num=$patient_num and (REGEXP_LIKE(text,'$requete','i') or REGEXP_LIKE(title,'$requete','i'))) ";
				$req=""; // NG 2019 07 22 : car oracle ne gère pas bien les regexp like : pas de lookahead, notamment pour exclure des mots (?!mot) ; on met le test dans le php 
			} else {
				//$req="and document_num in (select document_num from dwh_text where patient_num=$patient_num and (contains(enrich_text,'$requete')>0 or contains(title,'$requete')>0) ) ";
				$req="and document_num in (select document_num from dwh_text where patient_num=$patient_num and (contains(text,'$requete')>0 or contains(title,'$requete')>0) ) ";
				$requete_json=nettoyer_pour_inserer ($requete);
				$requete_json=replace_accent($requete_json);
				$tableau_liste_synonyme=recupere_liste_concept_full_texte ("{'query':'$requete_json','type':'fulltext','synonym':''}",50);
			}
		}
		$liste_document_origin_code=list_authorized_document_origin_code_for_one_patient($patient_num,$user_num_session);
	        if ($liste_document_origin_code!='') {
	        	if (!preg_match("/'tout'/i","$liste_document_origin_code")) {
				$req.="and document_origin_code in ($liste_document_origin_code) ";
	        	}
	        } else {
	              $req.=" and 1=2";
	        }
	        $nb_document=0;
		$res= "<table class=\"tableau_document\" $cellspacing id=\"id_tableau_liste_document\">";
		
		
		//$table_document=get_document_for_a_patient($patient_num,"$req  $req_option");
		//foreach ($table_document as $document_num) {
		//	$document=get_document ($document_num);
			
		$table_document=get_dwh_text('',$patient_num,'','',$user_num_session,'text',0,$req,'');
		foreach ($table_document as $document_num => $document) {
			$encounter_num=$document['encounter_num'];
			$title=$document['title'];
			$document_date=$document['document_date'];
			$document_origin_code=$document['document_origin_code'];
			$author=$document['author'];
			$text=$document['text'];    
	                if ($text!='') {
				if ($_SESSION['dwh_droit_fuzzy_display']=='ok') {
					$author='[AUTHOR]';
					$document_date='[DATE]';
				}
				if ($affichage_tableau_expression_regulier=='ok') {
					$text=preg_replace("/\n/"," ",$text);
					if (preg_match_all("/$requete/i","$text",$out, PREG_SET_ORDER)) {
						$nb_document++;
						$res.= "<tr onmouseout=\"this.style.backgroundColor='#ffffff';\" onmouseover=\"this.style.backgroundColor='#B9C2C8';\" onclick=\"afficher_document_patient($document_num,'id_afficher_document_ecrf','id_input_ecrf_filtre_patient_text');\" style=\"cursor: pointer; background-color:#ffffff;\" id=\"id_document_patient_$document_num\" class=\"tr_document_patient id_document_patient_$document_num\" sousgroupe=\"text\">";
						$res.= "<td style=\"border:1px solid black; border-collapse: collapse;\">$document_date</td>";
						foreach ($out[0] as $val) {
							$res.= "<td style=\"border:1px solid black; border-collapse: collapse;\">".$val."</td>";
						}
					}
				} else {
					$nb_document++;
					$res.= "<tr onmouseout=\"this.style.backgroundColor='#ffffff';\" onmouseover=\"this.style.backgroundColor='#B9C2C8';\" onclick=\"afficher_document_patient($document_num,'id_afficher_document_ecrf','id_input_ecrf_filtre_patient_text');\" style=\"cursor: pointer; background-color:#ffffff;\" id=\"id_document_patient_$document_num\" class=\"tr_document_patient id_document_patient_$document_num\" sousgroupe=\"text\">";
					$res.= "
					<th style=\"text-align:left;\">$document_origin_code</th><th style=\"text-align:left;\"> $title $author</td>
					<td>$document_date</td>";
					$res.= "</tr>";
					if ($requete!='') {
						$requete_json=nettoyer_pour_inserer ($requete);
						$requete_json=replace_accent($requete_json);
						$appercu=resumer_resultat($text,"{'query':'$requete_json','type':'fulltext','synonym':''}",$tableau_liste_synonyme,'patient');
						$res.= "<tr><td colspan=\"4\" class=\"appercu\"><i>$appercu</i></td><tr>";
					}
					$res.= "<tr><td colspan=\"4\"><hr  style=\"height:1px;border-top:0px;padding:0px;margin:0px;\"></td>";
					$res.= "</tr>";
				}
			}
		}
		$res.= "</table>";
		
		if ($nb_document==0) {
			print get_translation('NO_DOCUMENT_FOUND','aucun document trouvé');
		} else {
			print "$nb_document ".get_translation('DOCUMENTS_FOUND','documents trouvés')." <img border=\"0\" align=\"absmiddle\" style=\"cursor:pointer;width:18px;\" onclick=\"ouvrir_liste_document_print('$patient_num');return false;\" src=\"images/printer.png\"><br><br>";
			print $res;
		}
	}
}

// TODO: Check if the user is autorized to see the patient, if not do not get the data
function getEcrfId_from_Inclusion($user_num, $hospital_patient_id, $ecrf_num) {
	global $dbh;
	$tableau_ecrf = array();
	$qu_mapping=oci_parse($dbh,"select ecrf_id from eincl_mapping where ecrf_num =$ecrf_num AND hospital_patient_id = '$hospital_patient_id' ");
	//print("select ecrf_id from eincl_mapping where ecrf_num =$ecrf_num AND hospital_patient_id = '$hospital_patient_id' ");
	oci_execute($qu_mapping);
	$r=oci_fetch_array($qu_mapping,OCI_RETURN_NULLS+OCI_ASSOC);
	$ecrf_id=$r['ECRF_ID'];
	$tableau_ecrf =array(
		'ecrf_id'=> $ecrf_id
	);
	return  $tableau_ecrf;
}

// TODO: Check if the user is autorized to see the patient, if not do not get the data
function get_hospital_patient_id($user_num, $patient_num) {
	global $dbh;
	$tableau_ecrf = array();
	$qu_mapping=oci_parse($dbh,"select HOSPITAL_PATIENT_ID from dwh_patient_ipphist where patient_num = $patient_num  AND MASTER_PATIENT_ID = 1");
	oci_execute($qu_mapping);
	$r=oci_fetch_array($qu_mapping,OCI_RETURN_NULLS+OCI_ASSOC);
	$hospital_patient_id=$r['HOSPITAL_PATIENT_ID'];
	$tableau_ecrf =array(
		'hospital_patient_id'=> $hospital_patient_id
	);
	return  $tableau_ecrf;
}

function display_table_ecrf_patient_answer ($ecrf_num,$user_num,$option_excel) {
	$tableau_list_ecrf_patients_user=get_list_ecrf_patients ($ecrf_num,$user_num);
	if ($option_excel=='') {
		$thead_link="<th>Link</th><th>Del</th>";
	}

	print "<table class=\"tablefin\"><thead>
	<tr>
	$thead_link
	<th>IPP</th>
	<th>Lastname</th>
	<th>Firstname</th>
	<th>Birth date</th>";
	
	$tableau_list_ecrf_items=get_list_ecrf_items ($ecrf_num);
	foreach ($tableau_list_ecrf_items as $item) {
                $ecrf_item_num=$item['ecrf_item_num'];
	        $item_str=$item['item_str'];
	        $item_type=$item['item_type'];
	        print "<th>$item_str</th>";
	}
	
	print "
	</thead>
	<tbody>";		
	foreach ($tableau_list_ecrf_patients_user as $ecrf_patient) {
		$patient_num=$ecrf_patient['patient_num'];
		$user_value_date=$ecrf_patient['user_value_date'];
		$automated_value_date=$ecrf_patient['automated_value_date'];
		$tab_patient=get_patient ($patient_num,'');
		$lastname=$tab_patient['LASTNAME'];
		$firstname=$tab_patient['FIRSTNAME'];
		$birth_date=$tab_patient['BIRTH_DATE'];
		$hospital_patient_id=$tab_patient['HOSPITAL_PATIENT_ID'];
		
		print "<tr id=\"id_tr_ecrf_patient_".$ecrf_num."_".$patient_num."\">";
		if ($option_excel=='') {
			print "<th><a href=\"patient.php?patient_num=$patient_num&ecrf_num=$ecrf_num\" target=\"blank\"><img src=\"images/dossier_patient.png\" alt=\"Dossier du patient\" title=\"Dossier du patient\" height=\"15px\" border=\"0\"></a></th>";
			print "<th><img src=\"images/mini_poubelle.png\" onclick=\"delete_patient_ecrf($ecrf_num,$patient_num);\" style=\"cursor:pointer;\"></th>";
		}
		print "$tbody_link";
		print "<td>$hospital_patient_id</td><td>$lastname</td><td>$firstname </td><td>$birth_date</td>";
		$max_user_value_date=get_ecrf_patient_max_user_date($user_num,$ecrf_num, $patient_num);
		if ($max_user_value_date!='') {
			$patient_ecrf_data=get_ecrf_patient_manual_data($user_num,$ecrf_num, $patient_num);
			$backgroundcolor_global='#f0dea2';
		} else {
			$patient_ecrf_data=get_ecrf_patient_automated_data($user_num,$ecrf_num, $patient_num);
			$backgroundcolor_global='#ffcbcb';
		}
		foreach ($tableau_list_ecrf_items as $item) {
	                $ecrf_item_num=$item['ecrf_item_num'];
	                $ecrf_patient_validation_answer=get_ecrf_patient_validation_answer ($patient_num,$ecrf_num,$ecrf_item_num,$user_num);
	                if ($ecrf_patient_validation_answer['user_validation']==1) {
	                	$backgroundcolor='#bfe6bf';
	                } else {
	                	$backgroundcolor=$backgroundcolor_global;
	                }
			 print "<td style=\"background-color:$backgroundcolor\">".$patient_ecrf_data[$ecrf_item_num]."</td>";
		}
		print "</tr>";
	}
	print "</tbody></table>";
}

?>