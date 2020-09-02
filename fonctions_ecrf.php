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

include "ecrf_manual_functions.php";

include "fonctions_regexp.php";

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

function update_ecrf_item_all ($ecrf_num,$ecrf_item_num,$item_str,$item_type,$document_search,$regexp,$regexp_index,$item_ext_name,$item_ext_code,$item_local_code,$period,$item_order,$ecrf_function,$document_origin_code) {
	global $dbh;
	$req="update  dwh_ecrf_item  set item_str='$item_str',item_type='$item_type',document_search='$document_search',regexp='$regexp',regexp_index='$regexp_index',item_ext_name='$item_ext_name',item_ext_code='$item_ext_code',item_local_code='$item_local_code',period='$period',item_order='$item_order',ecrf_function='$ecrf_function',document_origin_code='$document_origin_code' where ecrf_item_num=$ecrf_item_num and ecrf_num=$ecrf_num ";
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

function update_ecrf_sub_item_all ($ecrf_sub_item_num,$sub_item_local_str,$sub_item_local_code,$sub_item_ext_code,$sub_item_regexp) {
	global $dbh;
	$req="update  dwh_ecrf_sub_item  set sub_item_local_str='$sub_item_local_str' ,sub_item_local_code='$sub_item_local_code',sub_item_ext_code='$sub_item_ext_code', sub_item_regexp='$sub_item_regexp' where ecrf_sub_item_num=$ecrf_sub_item_num  ";
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

function insert_ecrf_item ($ecrf_num ,$item_str,$item_type,$document_search,$item_ext_name, $item_ext_code,$regexp, $item_local_code,$regexp_index,$period,$item_order,$ecrf_function,$document_origin_code) {
	global $dbh;
	$ecrf_item_num='';
	if ($ecrf_num!='') {
		$ecrf_item_num=get_uniqid();
		$req="insert into dwh_ecrf_item  (ecrf_item_num , ecrf_num ,item_str,item_type,document_search,item_ext_name,item_ext_code,regexp,item_local_code,regexp_index,period,item_order,ecrf_function,document_origin_code) 
					values ($ecrf_item_num,$ecrf_num,'$item_str','$item_type','$document_search','$item_ext_name','$item_ext_code','$regexp','$item_local_code','$regexp_index','$period','$item_order','$ecrf_function','$document_origin_code')";
		$ins=oci_parse($dbh,$req);
		oci_execute($ins) || die ("<strong style=\"color:red\">erreur : item non ajouté à l ecrf</strong><br>");
	}
	return $ecrf_item_num;
}

function get_max_event_id($ecrf_num ,$patient_num,$user_num_session) {
	global $dbh;
	$req="select max(event_id) as max_event_id from  dwh_ecrf_patient_event where  ecrf_num=$ecrf_num and patient_num=$patient_num ";
	$sel = oci_parse($dbh,$req);   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	$max_event_id=$r['MAX_EVENT_ID'];
	return $max_event_id;

}

function insert_ecrf_patient_event ($ecrf_num ,$patient_num,$user_num,$event_id,$date_patient_ecrf,$nb_days_before, $nb_days_after ) {
	global $dbh;
	$ecrf_patient_event_num='';
	if ($ecrf_num!='' && $patient_num!=''  ) {
		$ecrf_patient_event_num=get_uniqid();
		$req="insert into dwh_ecrf_patient_event  (ecrf_patient_event_num ,ecrf_num, patient_num ,user_num, event_id,date_patient_ecrf,nb_days_before, nb_days_after) 
					values ($ecrf_patient_event_num ,$ecrf_num, $patient_num ,$user_num, '$event_id',to_date('$date_patient_ecrf','DD/MM/YYYY'),'$nb_days_before', '$nb_days_after')";
		$ins=oci_parse($dbh,$req);
		oci_execute($ins) || die ("<strong style=\"color:red\">erreur : event non ajouté à l ecrf</strong><br>");
	}
	return $ecrf_patient_event_num;
}

function update_ecrf_patient_event ($ecrf_patient_event_num,$event_id,$date_patient_ecrf,$nb_days_before,$nb_days_after){
	global $dbh;
	if ($ecrf_patient_event_num!=''  ) {
		$req="update  dwh_ecrf_patient_event set 
		event_id='$event_id',
		date_patient_ecrf=to_date('$date_patient_ecrf','DD/MM/YYYY'),
		nb_days_before='$nb_days_before',
		nb_days_after='$nb_days_after' where ecrf_patient_event_num=$ecrf_patient_event_num
		";
		$ins=oci_parse($dbh,$req);
		oci_execute($ins) || die ("<strong style=\"color:red\">erreur : event non modifie</strong><br>");
	}
}

function delete_evrf_patient_event ($ecrf_patient_event_num,$user_num) {
	global $dbh;
	if ($ecrf_patient_event_num!=''  ) {
		$req="delete from  dwh_ecrf_patient_event where ecrf_patient_event_num=$ecrf_patient_event_num and user_num=$user_num";
		$ins=oci_parse($dbh,$req);
		oci_execute($ins) || die ("<strong style=\"color:red\">erreur : event non supprime</strong><br>");
	}
}

function get_ecrf_patient_event ($ecrf_num ,$patient_num,$ecrf_patient_event_num) {
	global $dbh;
	
	$tableau_ecrf_patient_event = array();
	$req="";
	if ($ecrf_patient_event_num!='') {
		$req.=" and ecrf_patient_event_num=$ecrf_patient_event_num";
	} else {
		$req_event=" and ecrf_patient_event_num is null ";
	}
	if ($patient_num!='') {
		$req.=" and patient_num=$patient_num";
	}
	if ($ecrf_num!='') {
		$req.=" and ecrf_num=$ecrf_num";
	}
	$sel_ecrf=oci_parse($dbh,"select ecrf_patient_event_num , ecrf_num, patient_num ,event_id, to_char(date_patient_ecrf,'DD/MM/YYYY') as date_patient_ecrf,nb_days_before, nb_days_after
			from dwh_ecrf_patient_event where 1=1 $req order by event_id asc");
	oci_execute($sel_ecrf);
	while ($r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$ecrf_patient_event_num=$r['ECRF_PATIENT_EVENT_NUM'];
		$ecrf_num=$r['ECRF_NUM'];
		$patient_num=$r['PATIENT_NUM'];
		$event_id=$r['EVENT_ID'];
		$date_patient_ecrf=$r['DATE_PATIENT_ECRF'];
		$nb_days_before=$r['NB_DAYS_BEFORE'];
		$nb_days_after=$r['NB_DAYS_AFTER'];
		$tableau_ecrf_patient_event[] =array(
	            'ecrf_patient_event_num'=> $ecrf_patient_event_num,
	            'ecrf_num' =>  $ecrf_num,
	            'patient_num' =>  $patient_num,
	            'event_id' =>  $event_id,
	            'date_patient_ecrf' =>  $date_patient_ecrf,
	            'nb_days_before' =>  $nb_days_before,
	            'nb_days_after' =>  $nb_days_after
	        );
	}
      return  $tableau_ecrf_patient_event;
}


function insert_ecrf_item_from_other_ecrf ($user_num, $ecrf_num_add,$ecrf_num_duplicate) {
	global $dbh;
	global $dbh;
	if ($ecrf_num_add!='' && $ecrf_num_duplicate !='' && $user_num!='') {
		$tab_ecrf_items=get_list_ecrf_items ($ecrf_num_duplicate,"");
		foreach ($tab_ecrf_items as $ecrf_item) {
			$ecrf_item_num= $ecrf_item['ecrf_item_num'];
			$ecrf_item_num_add=get_uniqid();
			$req="insert into dwh_ecrf_item  (ecrf_item_num , ecrf_num ,item_str,item_type,document_search,item_ext_name,item_ext_code,regexp,item_local_code,regexp_index,period,item_order,ecrf_function,document_origin_code) 
			select $ecrf_item_num_add, $ecrf_num_add,item_str,item_type,document_search,item_ext_name,item_ext_code,regexp,item_local_code,regexp_index,period,item_order,ecrf_function,document_origin_code
			 from dwh_ecrf_item where ecrf_num=$ecrf_num_duplicate and ecrf_item_num=$ecrf_item_num";
			$ins=oci_parse($dbh,$req);
			oci_execute($ins) || die ("<strong style=\"color:red\">erreur : item non ajouté à l ecrf</strong><br>");
			
			$req="insert into dwh_ecrf_sub_item  (ecrf_sub_item_num,ecrf_item_num ,sub_item_local_str,sub_item_local_code, sub_item_ext_code,sub_item_regexp) 
				select dwh_seq.nextval,$ecrf_item_num_add, sub_item_local_str,sub_item_local_code, sub_item_ext_code,sub_item_regexp from dwh_ecrf_sub_item 
				where ecrf_item_num=$ecrf_item_num
						";
			$ins=oci_parse($dbh,$req);
			oci_execute($ins) || die ("<strong style=\"color:red\">erreur : sub item non ajouté à l ecrf</strong><br>");
		}
	}
}

function insert_ecrf_item_from_other_ecrf_item ($ecrf_num,$ecrf_item_num_import) {
	global $dbh;
	if ($ecrf_num!='' && $ecrf_item_num_import !='') {
		$ecrf_item_num_add=get_uniqid();
		
  		$max_item_order=get_max_ecrf_item_order ($ecrf_num)+1;
		$req="insert into dwh_ecrf_item  (ecrf_item_num , ecrf_num ,item_str,item_type,document_search,item_ext_name,item_ext_code,regexp,item_local_code,regexp_index,period,item_order,ecrf_function,document_origin_code) 
		select $ecrf_item_num_add, $ecrf_num,item_str,item_type,document_search,item_ext_name,item_ext_code,regexp,item_local_code,regexp_index,period,$max_item_order,ecrf_function,document_origin_code
		 from dwh_ecrf_item where  ecrf_item_num=$ecrf_item_num_import";
		$ins=oci_parse($dbh,$req);
		oci_execute($ins) || die ("<strong style=\"color:red\">erreur : item non ajouté à l ecrf</strong><br>");
		
		$req="insert into dwh_ecrf_sub_item  (ecrf_sub_item_num,ecrf_item_num ,sub_item_local_str,sub_item_local_code, sub_item_ext_code,sub_item_regexp) 
			select dwh_seq.nextval,$ecrf_item_num_add, sub_item_local_str,sub_item_local_code, sub_item_ext_code,sub_item_regexp from dwh_ecrf_sub_item 
			where ecrf_item_num=$ecrf_item_num_import
					";
		$ins=oci_parse($dbh,$req);
		oci_execute($ins) || die ("<strong style=\"color:red\">erreur : sub item non ajouté à l ecrf</strong><br>");
	}
}


function delete_ecrf_item ($ecrf_num ,$ecrf_item_num) {
	global $dbh;
	if ($ecrf_num!='') {
		$req="delete from dwh_ecrf_item where ecrf_item_num=$ecrf_item_num and ecrf_num=$ecrf_num ";
		$del=oci_parse($dbh,$req);
		oci_execute($del) || die ("<strong style=\"color:red\">erreur : item non supprime</strong><br>");
	}
}

function delete_patient_ecrf ($ecrf_num ,$patient_num,$user_num,$ecrf_patient_event_num) {
	global $dbh;
	if ($ecrf_num!='' && $patient_num!='' && $user_num!='') {
		if ($ecrf_patient_event_num!='') {
			$req_event=" and ecrf_patient_event_num=$ecrf_patient_event_num";
		} else {
			$req_event=" and ecrf_patient_event_num is null ";
		}
		$req="delete from DWH_ECRF_ANSWER where patient_num=$patient_num and ecrf_num=$ecrf_num  and user_num=$user_num $req_event";
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
		oci_execute($ins) || die ("<strong style=\"color:red\">erreur : sub item non ajouté à l ecrf</strong><br>");
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

function delete_ecrf_all_sub_items ($ecrf_item_num) {
	global $dbh;
	if ($ecrf_item_num!='') {
		$req="delete from dwh_ecrf_sub_item where ecrf_item_num=$ecrf_item_num ";
		$del=oci_parse($dbh,$req);
		oci_execute($del) || die ("<strong style=\"color:red\">erreur : item non supprime</strong><br>");
	}
}

function update_result_manual_ecrf ($ecrf_answer_num,$user_num,$final_manual_value) {
	global $dbh;
	$requeteins="update  DWH_ECRF_ANSWER set user_value=:final_manual_value, user_value_date=sysdate where ecrf_answer_num=$ecrf_answer_num and user_num=$user_num";
	$stmt = ociparse($dbh,$requeteins);
	$rowid = ocinewdescriptor($dbh, OCI_D_LOB);
	ocibindbyname($stmt, ":final_manual_value",$final_manual_value);
	$execState = ociexecute($stmt);
	ocifreestatement($stmt);
}

function update_ecrf_validate_ecrf_item ($ecrf_answer_num,$user_num,$validate) {
	global $dbh;
	$req="update  DWH_ECRF_ANSWER set user_validation='$validate', user_validation_date=sysdate where ecrf_answer_num=$ecrf_answer_num  and user_num=$user_num";
	$upd=oci_parse($dbh,$req);
	oci_execute($upd) || die ("<strong style=\"color:red\">erreur : item non supprime</strong><br>");
}

function get_ecrf_answer_num($user_num,$ecrf_num,$patient_num,$ecrf_patient_event_num,$ecrf_item_num) {
	global $dbh;
	$req_event="";
	if ($ecrf_patient_event_num!='') {
		$req_event=" and ecrf_patient_event_num=$ecrf_patient_event_num ";
	} else {
		$req_event=" and ecrf_patient_event_num is null ";
	}
	$sel_ecrf=oci_parse($dbh,"select ecrf_answer_num from DWH_ECRF_ANSWER where ecrf_num=$ecrf_num and patient_num=$patient_num and user_num=$user_num and ecrf_item_num=$ecrf_item_num $req_event order by ecrf_item_num ");
	oci_execute($sel_ecrf);
	$r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
	$ecrf_answer_num=$r['ECRF_ANSWER_NUM'];
	
      return  $ecrf_answer_num;

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
	$tableau_ecrf = array();
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

function get_list_ecrf_item_share ($user_num) {
	global $dbh;
	$list_ecrf=get_list_ecrf ($user_num);
	foreach ($list_ecrf as $ecrf) {
		$title_ecrf=$ecrf['title_ecrf'];
		$ecrf_num=$ecrf['ecrf_num'];
		$list_ecrf_items=get_list_ecrf_items ($ecrf_num,"");
		foreach ($list_ecrf_items as $ecrf_item) {
			$ecrf_item_num=$ecrf_item['ecrf_item_num'];
			$item_str=$ecrf_item['item_str'];
			$item_type=$ecrf_item['item_type'];
			$result[]=array("ecrf_item_num"=>$ecrf_item_num,"title_ecrf"=>$title_ecrf,"item_str_type"=>"$item_str ($item_type)");
		}
	}
	return $result;
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
	$sel_ecrf=oci_parse($dbh,"select ecrf_item_num  ,item_str,item_type,document_search,item_ext_name,item_ext_code,regexp,item_local_code,regexp_index,period,item_order,ecrf_function,document_origin_code
	 from dwh_ecrf_item where ecrf_item_num=$ecrf_item_num");
        oci_execute($sel_ecrf);
        $r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
        $ecrf_item_num=$r['ECRF_ITEM_NUM'];
        $item_str=$r['ITEM_STR'];
        $item_type=$r['ITEM_TYPE'];
        $document_search=$r['DOCUMENT_SEARCH'];
        $item_ext_name=$r['ITEM_EXT_NAME'];
        $item_ext_code=$r['ITEM_EXT_CODE'];
        $regexp=$r['REGEXP'];
        $regexp_index=$r['REGEXP_INDEX'];
        $item_local_code=$r['ITEM_LOCAL_CODE'];
        $period=$r['PERIOD'];
        $item_order=$r['ITEM_ORDER'];
        $ecrf_function=$r['ECRF_FUNCTION'];
        $document_origin_code=$r['DOCUMENT_ORIGIN_CODE'];
        $tableau_list_ecrf_items =array(
            'ecrf_item_num'=> $ecrf_item_num,
            'item_str' =>  $item_str,
            'item_type' =>  $item_type,
            'document_search' =>  $document_search,
            'item_ext_name' =>  $item_ext_name,
            'item_ext_code'=>  $item_ext_code,
            'regexp'=>  $regexp,
            'regexp_index'=>  $regexp_index,
            'item_local_code'=>  $item_local_code,
            'period'=>  $period,
            'item_order'=>  $item_order,
            'ecrf_function'=>  $ecrf_function,
            'document_origin_code'=>  $document_origin_code
        );
      return  $tableau_list_ecrf_items;
}

function get_list_ecrf_items ($ecrf_num,$ecrf_item_num) {
	global $dbh;
	$tableau_list_ecrf_items = array();
	if ($ecrf_item_num!='') {
		$req_ecrf_item_num=" and ecrf_item_num=$ecrf_item_num ";
	} else {
		$req_ecrf_item_num="";
	}
	$sel_ecrf=oci_parse($dbh,"select  ecrf_item_num  ,item_str,item_type,document_search,item_ext_name,item_ext_code,regexp,item_local_code,regexp_index,period,item_order,ecrf_function,document_origin_code from dwh_ecrf_item where ecrf_num=$ecrf_num $req_ecrf_item_num order by item_order,ecrf_item_num ");
        oci_execute($sel_ecrf);
        while ($r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC)) {
               //$ecrf_item_num=$r['ECRF_ITEM_NUM'];
               //$tableau_list_ecrf_items[]=get_ecrf_item ($ecrf_item_num);
                       $ecrf_item_num=$r['ECRF_ITEM_NUM'];
	        $item_str=$r['ITEM_STR'];
	        $item_type=$r['ITEM_TYPE'];
	        $document_search=$r['DOCUMENT_SEARCH'];
	        $item_ext_name=$r['ITEM_EXT_NAME'];
	        $item_ext_code=$r['ITEM_EXT_CODE'];
	        $regexp=$r['REGEXP'];
	        $regexp_index=$r['REGEXP_INDEX'];
	        $item_local_code=$r['ITEM_LOCAL_CODE'];
	        $period=$r['PERIOD'];
	        $item_order=$r['ITEM_ORDER'];
	        $ecrf_function=$r['ECRF_FUNCTION'];
	        $document_origin_code=$r['DOCUMENT_ORIGIN_CODE'];
	        $tableau =array(
	            'ecrf_item_num'=> $ecrf_item_num,
	            'item_str' =>  $item_str,
	            'item_type' =>  $item_type,
	            'document_search' =>  $document_search,
	            'item_ext_name' =>  $item_ext_name,
	            'item_ext_code'=>  $item_ext_code,
	            'regexp'=>  $regexp,
	            'regexp_index'=>  $regexp_index,
	            'item_local_code'=>  $item_local_code,
	            'period'=>  $period,
	            'item_order'=>  $item_order,
	            'ecrf_function'=>  $ecrf_function,
	            'document_origin_code'=>  $document_origin_code
	        );
               $tableau_list_ecrf_items[]=$tableau;
        }
	return  $tableau_list_ecrf_items;
}

function get_list_ecrf_sub_items ($ecrf_item_num) {

	global $dbh;
	$tableau_list_ecrf_sub_items = array();
	$sel_ecrf=oci_parse($dbh,"select ecrf_sub_item_num from dwh_ecrf_sub_item where ecrf_item_num=:ecrf_item_num  order by ecrf_sub_item_num ");
	ocibindbyname($sel_ecrf, ":ecrf_item_num",$ecrf_item_num);
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
	$sel_ecrf=oci_parse($dbh,"select ecrf_sub_item_num,ecrf_item_num ,sub_item_local_str,sub_item_local_code, sub_item_ext_code,sub_item_regexp from dwh_ecrf_sub_item where ecrf_sub_item_num=:ecrf_sub_item_num  ");
	ocibindbyname($sel_ecrf, ":ecrf_sub_item_num",$ecrf_sub_item_num);
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
		ecrf_num,
		ecrf_patient_event_num,
		patient_num,
		user_num,
		to_char(max(user_value_date),'DD/MM/YYYY HH24:MI') as  user_value_date_char,
		to_char(max(automated_value_date),'DD/MM/YYYY HH24:MI') as  automated_value_date_char 
	from dwh_ecrf_answer 
	where ecrf_num=$ecrf_num $req_user_num 
	group by ecrf_num, ecrf_patient_event_num,patient_num, user_num
	order by patient_num asc,ecrf_patient_event_num desc ");
        oci_execute($sel_ecrf);
        while ($r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC)) {
                $patient_num=$r['PATIENT_NUM'];
                $ecrf_num=$r['ECRF_NUM'];
                $ecrf_patient_event_num=$r['ECRF_PATIENT_EVENT_NUM'];
                $user_num=$r['USER_NUM'];
                $user_value_date=$r['USER_VALUE_DATE_CHAR'];
                $automated_value_date=$r['AUTOMATED_VALUE_DATE_CHAR'];
             
	        $tableau_list_ecrf_patients[] =array(
	            'patient_num'=> $patient_num,
	            'ecrf_num'=> $ecrf_num,
	            'ecrf_patient_event_num'=> $ecrf_patient_event_num,
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


/*function insert_ecrf_item ($ecrf_num ,$item_str,$item_type,$document_search) {
	global $dbh;
	if ($ecrf_num!='') {
		$req="insert into dwh_ecrf_item  (ecrf_item_num , ecrf_num ,item_str,item_type,document_search) 
					values (dwh_seq.nextval,$ecrf_num,'$item_str','$item_type','$document_search')";
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


function extract_information_ecrf ($patient_num,$ecrf_num,$ecrf_item_num,$filter_query,$ecrf_patient_event_num,$table_document_all_aff,$table_document_all_neg) {
	global $dbh,$user_num_session,$liste_service_session,$liste_document_origin_code_session,$datamart_num,$CHEMIN_GLOBAL_LOG;
	$nb_doc_found_limite=10;
	
	$ecrf=get_ecrf ($ecrf_num);
        $ecrf_start_date=$ecrf['ecrf_start_date'];
        $ecrf_end_date=$ecrf['ecrf_end_date'];
        $ecrf_start_age=$ecrf['ecrf_start_age'];
        $ecrf_end_age=$ecrf['ecrf_end_age'];
        
	$query_filter_ecrf='';
        
        if ($ecrf_patient_event_num!='') {
		$ecrf_patient_event=get_ecrf_patient_event ($ecrf_num ,$patient_num,$ecrf_patient_event_num);
		$date_patient_ecrf=$ecrf_patient_event[0]['date_patient_ecrf'];
		$nb_days_before=$ecrf_patient_event[0]['nb_days_before'];
		$nb_days_after=$ecrf_patient_event[0]['nb_days_after'];
		
		if ($date_patient_ecrf!='' && $nb_days_before!='' && $nb_days_after!='') {
			$query_filter_ecrf.=" and document_date >= to_date('$date_patient_ecrf','DD/MM/YYYY')-$nb_days_before and  document_date <= to_date('$date_patient_ecrf','DD/MM/YYYY')+$nb_days_after  ";
		}
	}

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
        $document_search=$item['document_search'];
        $item_ext_name=$item['item_ext_name'];
        $item_ext_code=$item['item_ext_code'];
        $regexp=$item['regexp'];
        $regexp_index=$item['regexp_index'];
        $item_local_code=$item['item_local_code'];
        $period=$item['period'];
        $ecrf_function=$item['ecrf_function'];
        $document_origin_code=$item['document_origin_code'];
        
	$regexp=replace_accent($regexp);
	$document_search=replace_accent($document_search);
	
	$array_list_yes=array('oui','yes','present','presente');
	$array_list_no=array('non','no','absent','absente');
	$array_list_unknown=array('inconnu','inconnue','non fait','non faite','ne sait pas','ne sais pas','nsp','na');
	$array_list_other=array('anormal','atteint','anormale','atteinte','non atteint','normal','non atteinte','normale','primaire','secondaire','tertiaire','primitif','associe');

	$array_list_research_on_question=array_merge($array_list_yes, $array_list_no,$array_list_unknown,$array_list_other);

	$table_appercu=array();
	$table_value=array(); 
	$tableau_result=array();
	$tableau_result_str=array();
	$option_add_item_str='';
	
	if ($document_origin_code!='') {
		$query_filter_ecrf.=" and document_origin_code='$document_origin_code' ";
		$table_document_all_aff=array();
		$table_document_all_neg=array();
	}
	if ($document_search!='') {
		$document_search_fulltext=$document_search;
		if (preg_match("/[[|?+{]/",$document_search_fulltext)) {
       			$document_search_fulltext=str_replace("|"," or ",$document_search_fulltext);
       			$document_search_fulltext=preg_replace("/[a-z]\?/","%",$document_search_fulltext);
       			$document_search_fulltext=preg_replace("/[\[\]+}{]/","",$document_search_fulltext);
       			$document_search_fulltext=str_replace("\s"," ",$document_search_fulltext);
       			$document_search_fulltext=str_replace("\w"," ",$document_search_fulltext);
       			$document_search_fulltext=str_replace("\d"," ",$document_search_fulltext);
		}
		$document_search_fulltext=nettoyer_pour_requete ($document_search_fulltext);
		$query_filter_ecrf.=" and document_num in (select document_num from dwh_text where contains(text,'$document_search_fulltext')>0 )";
		$table_document_all_aff=array();
		$table_document_all_neg=array();
	}

	
	if ($ecrf_function!='') {
		$tab_sub_items=get_list_ecrf_sub_items ($ecrf_item_num);
		$sub_item_local_str=array();
		foreach ($tab_sub_items as $sub_item) {
			$sub_item_local_str[]=trim(replace_accent($sub_item['sub_item_local_str']));
		}
		$res=execute_ecrf_functions($ecrf_function, $patient_num,$sub_item_local_str);
		$table_value[]=array('sous_item_value'=>$res);
	} else if ($item_str!='') {
		// si c'est une liste d'item à cocher 
		if ($item_type=='list' || $item_type=='radio') {
			$sub_item_regexp_for_this_item='';
			$tab_sub_items=get_list_ecrf_sub_items ($ecrf_item_num);
			foreach ($tab_sub_items as $sub_item) {
				$sub_item_regexp=$sub_item['sub_item_regexp'];
				if ($sub_item_regexp!='') {
					$sub_item_regexp_for_this_item='yes';
				}
			}
			foreach ($tab_sub_items as $sub_item) {
				$sub_item_local_str=strtolower(trim(replace_accent($sub_item['sub_item_local_str'])));
				$sub_item_local_code=$sub_item['sub_item_local_code'];
				$sub_item_regexp=$sub_item['sub_item_regexp'];
				if ($sub_item_regexp!='') {
					// NG Sophie 2020 03 11
					$tableau_result=array();
					if (count($table_document_all_aff)>0) {
						$tableau_result_temp=array();
						$str_regexp=trim(clean_for_regexp ($sub_item_regexp));
						foreach ($table_document_all_aff as $document_num => $document) {
							$text=$document['text'];
							$text=clean_for_regular_expression($text);
							if (preg_match_all("/$str_regexp/i","$text",$out, PREG_SET_ORDER)) {
								$tableau_result_temp[]=$document;
							}
						}
						if ($period=='last' && $tableau_result_temp[0]!='') {
							$tableau_result[0]=$tableau_result_temp[0];
						}
						if ($period=='first' && $tableau_result_temp[count($tableau_result_temp)-1]!='') {
							$tableau_result[0]=$tableau_result_temp[count($tableau_result_temp)-1];
						}
						if ($period=='all' || $period=='') {
							$tableau_result=$tableau_result_temp;
						}
						
					} else {
				 		$tableau_result=search_patient_document_new ($patient_num,'regexp',$sub_item_regexp,1,'patient_text','text',$query_filter_ecrf,$period);
				 	}
				 	$presence='';
				 	$nb_doc_found=0;
					foreach ($tableau_result as $document) {
					 	$document_num=$document['document_num'];
					 	if ($document_num!='') {
				 			$nb_doc_found++;
				 			if ($nb_doc_found<$nb_doc_found_limite) {
								$text=$document['text'];
								$text=clean_for_regular_expression($text);
								if ($text!='') {		
									$text=preg_replace("/\n/"," ",$text);
									$out=array();
									$str_regexp=trim(clean_for_regexp ($sub_item_regexp));
									if (preg_match_all("/.{0,10}($str_regexp).{0,10}/i","$text",$out,  PREG_PATTERN_ORDER )) {
										$j=0;
										$presence='ok';
										$ap=$out[0];
										$table_appercu[]=array('document_date'=>$document['document_date'], 'appercu'=>$ap[$j], 'document_num'=>$document_num, 'query_highlight'=>$str_regexp,'certainty'=>'1');
									}
								}
							}
						}
					}
					if ($presence=='ok') {
						$table_value[]=array('sous_item_value'=>$sub_item['sub_item_local_str']);
					}
					
					// negation detection for information 
					$tableau_result=array();
					if (count($table_document_all_neg)>0) {
						$tableau_result_temp=array();
						$str_regexp=trim(clean_for_regexp ($sub_item_regexp));
						foreach ($table_document_all_neg as $document_num => $document) {
							$text=$document['text'];
							$text=clean_for_regular_expression($text);
							if (preg_match_all("/$str_regexp/i","$text",$out, PREG_SET_ORDER)) {
								$tableau_result_temp[]=$document;
							}
						}
						if ($period=='last' && $tableau_result_temp[0]!='') {
							$tableau_result[0]=$tableau_result_temp[0];
						}
						if ($period=='first' && $tableau_result_temp[count($tableau_result_temp)-1]!='') {
							$tableau_result[0]=$tableau_result_temp[count($tableau_result_temp)-1];
						}
						if ($period=='all' || $period=='') {
							$tableau_result=$tableau_result_temp;
						}
					} else {
				 		$tableau_result=search_patient_document_new ($patient_num,'regexp',$sub_item_regexp,-1,'patient_text','text',$query_filter_ecrf,$period);
				 	}
				 	$nb_doc_found=0;
					foreach ($tableau_result as $document) {
					 	$document_num=$document['document_num'];
					 	if ($document_num!='') {
				 			$nb_doc_found++;
				 			if ($nb_doc_found<$nb_doc_found_limite) {
								$text=$document['text'];
								if ($text!='') {		
									$text=preg_replace("/\n/"," ",$text);
									$out=array();
									$str_regexp=trim(clean_for_regexp ($sub_item_regexp));
									if (preg_match_all("/.{0,10}($str_regexp).{0,10}/i","$text",$out,  PREG_PATTERN_ORDER )) {
										$j=0;
										$ap=$out[0];
										$table_appercu[]=array('document_date'=>$document['document_date'], 'appercu'=>$ap[$j], 'document_num'=>$document_num, 'query_highlight'=>$str_regexp,'certainty'=>'-1');
									}
								}
							}
						}
					}
				}
				if ($sub_item_local_code!='') {
					$tableau_result=array();
			 		$tab_sub_item_local_code=explode(";",$sub_item_local_code);
				 	foreach ($tab_sub_item_local_code as $concept_code) {
						$t=search_patient_document_new ($patient_num,'data_code',$concept_code,'','','',$query_filter_ecrf,$period);
						$tableau_result=array_merge($t,$tableau_result);
					}
				 	$nb_doc_found=0;
				 	foreach ($tableau_result as $document) {
				 		$document_num=$document['document_num'];
				 		if ($document_num!='') {
				 			$nb_doc_found++;
				 			if ($nb_doc_found<$nb_doc_found_limite) {
						 		$val=$document['value'];
						 		$info=$document['info'];
						 		$concept_str=$document['concept_str'];
						 		$concept_code=$document['concept_code'];
						 		$concept_code=$document['concept_code'];
								//$document=get_document($document_num,'') ;
								$table_value[]=array('sous_item_value'=>$val,'concept_str'=>$concept_str,'concept_code'=>$concept_code,'info'=>$info,'document_date'=>$document['document_date']);
								$table_appercu[]=array('document_date'=>$document['document_date'], 'appercu'=>"$concept_str $info:$val", 'document_num'=>$document_num, 'query_highlight'=>$val);
							}
						}
				 	}
				}
				
				if ($sub_item_regexp=='' && $sub_item_local_code=='') {
					// If sub item like yes / no / other etc...
					// et il n y a pas eu de regexp sur menu precedens (yes) 
					if (in_array($sub_item_local_str,$array_list_research_on_question)) {
						if ($sub_item_regexp_for_this_item=='') {
							$item_str_for_query=nettoyer_pour_requete_patient($item_str) ;
						 	$tableau_result_aff=search_patient_document_new ($patient_num,'text',$item_str_for_query,1,'patient_text','text',$query_filter_ecrf,$period);
						 	if (count($tableau_result_aff)>0) {
					 			$nb_doc_found=0;
							 	foreach ($tableau_result_aff as $document) {
								 	$document_num=$document['document_num'];
							 		if ($document_num!='') {
							 			$nb_doc_found++;
							 			if ($nb_doc_found<$nb_doc_found_limite) {
											//$document=get_document($document_num,'patient_text_affirmation') ;
											$value='';
											$requete_json=nettoyer_pour_inserer ($item_str_for_query);
											$requete_json=replace_accent($requete_json);
											$appercu=resumer_resultat($document['text'],"{'query':'$requete_json','type':'fulltext','synonym':''}",array(),'ecrf');
											$query_highlight=$item_str_for_query;
											if ($appercu=='') {
												$appercu=resumer_resultat($document['text'],"{'query':'$requete_json','type':'fulltext','synonym':''}",array(),'ecrf');
												$query_highlight=$item_str_for_query;
											}
											$table_appercu[]=array('document_date'=>$document['document_date'], 'appercu'=>$appercu, 'document_num'=>$document_num, 'query_highlight'=>$query_highlight,'certainty'=>'1');
											$presence='ok';
										}
									}
								}
								if (count($tableau_result_aff)>0) {
									if (in_array($sub_item_local_str,$array_list_yes) || in_array($sub_item_local_str,$array_list_other)) {
										$table_value[]=array('sous_item_value'=>$sub_item['sub_item_local_str']);
									}
								}
							}
							if (count($tableau_result_aff)==0) {
							 	$tableau_result_neg=search_patient_document_new ($patient_num,'text',$item_str_for_query,-1,'patient_text','text',$query_filter_ecrf,$period);
					 			$nb_doc_found=0;
							 	foreach ($tableau_result_neg as $document) {
								 	$document_num=$document['document_num'];
							 		if ($document_num!='') {
							 			$nb_doc_found++;
							 			if ($nb_doc_found<$nb_doc_found_limite) {
											//$document=get_document($document_num,'patient_text_negation') ;
											$value='';
											$requete_json=nettoyer_pour_inserer ($item_str_for_query);
											$requete_json=replace_accent($requete_json);
											$appercu=resumer_resultat($document['text'],"{'query':'$requete_json','type':'fulltext','synonym':''}",array(),'ecrf');
											$query_highlight=$item_str_for_query;
											if ($appercu=='') {
												$appercu=resumer_resultat($document['text'],"{'query':'$requete_json','type':'fulltext','synonym':''}",array(),'ecrf');
												$query_highlight=$item_str_for_query;
											}
											$table_appercu[]=array('document_date'=>$document['document_date'], 'appercu'=>$appercu, 'document_num'=>$document_num, 'query_highlight'=>$query_highlight,'certainty'=>'-1');
										}
									}
								}
							}
						}
					} else {
						if (count($tableau_result_str)==0) {
							$item_str_for_query=nettoyer_pour_requete_patient($item_str) ;
					// NG Sophie 2020 03 11
							$tableau_result_str=search_patient_document_new ($patient_num,'text',$item_str_for_query,1,'patient_text','text',$query_filter_ecrf,$period);
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
					// NG Sophie 2020 03 11
						$tableau_result=array();
					 	$tableau_result=search_patient_document_new ($patient_num,'text',$item_str_for_query,1,'patient_text','text',$query_filter_ecrf,$period);
					 	if (count($tableau_result)==0 && $option=='add_item_str') {
							$item_str_for_query=nettoyer_pour_requete_patient($sub_item_local_str) ;
						 	$tableau_result=$tableau_result_str;
					 	}
				 		$nb_doc_found=0;
					 	foreach ($tableau_result as $document) {
					 		$document_num=$document['document_num'];
					 		if ($document_num!='') {
					 			$nb_doc_found++;
					 			if ($nb_doc_found<$nb_doc_found_limite) {
									//$document=get_document($document_num,'patient_text_affirmation') ;
									$requete_json=nettoyer_pour_inserer ($item_str_for_query);
									$requete_json=replace_accent($requete_json);
									$appercu=resumer_resultat($document['text'],"{'query':'$requete_json','type':'fulltext','synonym':''}",array(),'ecrf');
									$query_highlight=$item_str_for_query;
									if ($appercu=='') {
										$appercu=resumer_resultat($document['text'],"{'query':'$requete_json','type':'fulltext','synonym':''}",array(),'ecrf');
										$query_highlight=$item_str_for_query;
									}
									$table_appercu[]=array('document_date'=>$document['document_date'], 'appercu'=>$appercu, 'document_num'=>$document_num, 'query_highlight'=>$query_highlight,'certainty'=>'1');
								}
							}
						}
						if (count($tableau_result)>0) {
							$table_value[]=array('sous_item_value'=>$sub_item['sub_item_local_str']);
						}
						
						
						$tableau_result=array();
					 	$tableau_result=search_patient_document_new ($patient_num,'text',$item_str_for_query,-1,'patient_text','text',$query_filter_ecrf,$period);
				 		$nb_doc_found=0;
					 	foreach ($tableau_result as $document) {
					 		$document_num=$document['document_num'];
					 		if ($document_num!='') {
					 			$nb_doc_found++;
					 			if ($nb_doc_found<$nb_doc_found_limite) {
									//$document=get_document($document_num,'patient_text_negation') ;
									$requete_json=nettoyer_pour_inserer ($item_str_for_query);
									$requete_json=replace_accent($requete_json);
									$appercu=resumer_resultat($document['text'],"{'query':'$requete_json','type':'fulltext','synonym':''}",array(),'ecrf');
									$query_highlight=$item_str_for_query;
									if ($appercu=='') {
										$appercu=resumer_resultat($document['text'],"{'query':'$requete_json','type':'fulltext','synonym':''}",array(),'ecrf');
										$query_highlight=$item_str_for_query;
									}
									$table_appercu[]=array('document_date'=>$document['document_date'], 'appercu'=>$appercu, 'document_num'=>$document_num, 'query_highlight'=>$query_highlight,'certainty'=>'-1');
								}
							}
						}
					}
				}
			}
			// si aucune valeur trouvée
			if (count($table_value)==0) {
				// on  essaie de cocher Ne sait pas / inconnu etc.
				foreach ($tab_sub_items as $sub_item) {
					$sub_item_local_str=strtolower(trim(replace_accent($sub_item['sub_item_local_str'])));
					if (in_array($sub_item_local_str,$array_list_unknown)) {
						$table_value[]=array('sous_item_value'=>$sub_item['sub_item_local_str']);
					}
				}
				// si pas de champs NSP 
				// on  essaie de cocher  NON, absence etc.
				if (count($table_value)==0) {
					foreach ($tab_sub_items as $sub_item) {
						$sub_item_local_str=strtolower(trim(replace_accent($sub_item['sub_item_local_str'])));
						if (in_array($sub_item_local_str,$array_list_no)) {
							$table_value[]=array('sous_item_value'=>$sub_item['sub_item_local_str']);
						}
					}
				}
			}		
		} else {
			$value_global='';
			
		 	$i=0;
			if ($item_local_code!='') {
				$tableau_result=array();
		 		$tab_item_local_code=explode(";",$item_local_code);
			 	foreach ($tab_item_local_code as $concept_code) {
					$t=search_patient_document_new ($patient_num,'data_code',$concept_code,'','','',$query_filter_ecrf,$period);
					$tableau_result=array_merge($t,$tableau_result);
				}
				$nb_doc_found=0;
			 	foreach ($tableau_result as $document) {
				 	$document_num=$document['document_num'];
				 	if ($document_num!='') {
			 			$nb_doc_found++;
			 			if ($nb_doc_found<$nb_doc_found_limite) {
					 		$val=$document['value'];
					 		$info=$document['info'];
					 		$concept_str=$document['concept_str'];
					 		$concept_code=$document['concept_code'];
							//$document=get_document($document_num,'') ;
							if ($item_type=='date') {
								$table_value[]=array('sous_item_value'=>$document['document_date'],'document_date'=>$document['document_date']);
							} else if (($item_type=='numeric' || $item_type=='number') && preg_match("/[^a-z][aâ]ge[^a-z]/i"," $item_str ")) {
								if ($document['document_date']!='') {
									$age_patient=get_age_patient ($patient_num, $document['document_date'],'years');
									$table_value[]=array('sous_item_value'=>$age_patient,'document_date'=>$document['document_date']);
								}
							} else {
								$table_value[]=array('sous_item_value'=>$val,'concept_str'=>$concept_str,'concept_code'=>$concept_code,'info'=>$info,'document_date'=>$document['document_date']);
							}
							$table_appercu[]=array('document_date'=>$document['document_date'], 'appercu'=>"$concept_str $info:$val", 'document_num'=>$document_num, 'query_highlight'=>$concept_str);
						}
					}
			 	}
			}
			

			if ($item_local_code=='' && $regexp=='' && $document_search!='' && preg_match("/[[|?+{]/",$document_search)) {
				$regexp=$document_search;
		       	}
			if ($regexp!='') {
					// NG Sophie 2020 03 11
				$tableau_result=array();
				if (count($table_document_all_aff)>0) {
					$tableau_result_temp=array();
					$str_regexp=trim(clean_for_regexp ($regexp));
					foreach ($table_document_all_aff as $document_num => $document) {
						$text=$document['text'];
						$text=clean_for_regular_expression($text);
						if (preg_match_all("/$str_regexp/i","$text",$out, PREG_SET_ORDER)) {
							$tableau_result_temp[]=$document;
						}
					}
					if ($period=='last' && $tableau_result_temp[0]!='') {
						$tableau_result[0]=$tableau_result_temp[0];
					}
					if ($period=='first' && $tableau_result_temp[count($tableau_result_temp)-1]!='') {
						$tableau_result[0]=$tableau_result_temp[count($tableau_result_temp)-1];
					}
					if ($period=='all' || $period=='') {
						$tableau_result=$tableau_result_temp;
					}
				} else {
			 		$tableau_result=search_patient_document_new ($patient_num,'regexp',$regexp,1,'patient_text','text',$query_filter_ecrf,$period);
			 	}
				
				$nb_doc_found=0;
				foreach ($tableau_result as $document) {
				 	$document_num=$document['document_num'];
				 	if ($document_num!='') {
			 			$nb_doc_found++;
			 			if ($nb_doc_found<$nb_doc_found_limite) {
							$text=$document['text'];
							$text=clean_for_regular_expression($text);
							if ($text!='') {		
								$text=preg_replace("/\n/"," ",$text);
								$out=array();
								
								$regexp_clean=trim(clean_for_regexp ($regexp));
								if (preg_match_all("/.{0,10}$regexp_clean.{0,10}/i","$text",$out,  PREG_PATTERN_ORDER )) {
									$j=0;
									if ($regexp_index!='') {
										foreach ($out[$regexp_index] as $value) {
											$table_value[]=array('sous_item_value'=>$value,'document_date'=>$document['document_date']);
											$table_appercu[]=array('document_date'=>$document['document_date'], 'appercu'=>$out[0][$j], 'document_num'=>$document_num, 'query_highlight'=>$regexp,'certainty'=>'1');
											$j++;
										}
									} else {
										//foreach ($out as $regexp_index => $value) {
											if ($item_type=='date') {
												$table_value[]=array('sous_item_value'=>$document['document_date'],'document_date'=>$document['document_date']);
											} else if (($item_type=='numeric' || $item_type=='number') && preg_match("/[^a-z][aâ]ge[^a-z]/i"," $item_str ")) {
												if ($document['document_date']!='') {
													$age_patient=get_age_patient ($patient_num, $document['document_date'],'years');
													$table_value[]=array('sous_item_value'=>$age_patient,'document_date'=>$document['document_date']);
												}
											} 
											$table_appercu[]=array('document_date'=>$document['document_date'], 'appercu'=>$out[0][0], 'document_num'=>$document_num, 'query_highlight'=>$regexp,'certainty'=>'1');
										//}
									}
								}
							}
						}
					}
				}
				
				// negation 
				$tableau_result=array();
				if (count($table_document_all_neg)>0) {
					$tableau_result_temp=array();
					$str_regexp=trim(clean_for_regexp ($regexp));
					foreach ($table_document_all_neg as $document_num => $document) {
						$text=$document['text'];
						$text=clean_for_regular_expression($text);
						if (preg_match_all("/$str_regexp/i","$text",$out, PREG_SET_ORDER)) {
							$tableau_result_temp[]=$document;
						}
					}
					if ($period=='last' && $tableau_result_temp[0]!='') {
						$tableau_result[0]=$tableau_result_temp[0];
					}
					if ($period=='first' && $tableau_result_temp[count($tableau_result_temp)-1]!='') {
						$tableau_result[0]=$tableau_result_temp[count($tableau_result_temp)-1];
					}
					if ($period=='all' || $period=='') {
						$tableau_result=$tableau_result_temp;
					}
				} else {
			 		$tableau_result=search_patient_document_new ($patient_num,'regexp',$regexp,-1,'patient_text','text',$query_filter_ecrf,$period);
			 	}
				$nb_doc_found=0;
				foreach ($tableau_result as $document) {
				 	$document_num=$document['document_num'];
				 	if ($document_num!='') {
			 			$nb_doc_found++;
			 			if ($nb_doc_found<$nb_doc_found_limite) {
							//$document=get_document($document_num,'patient_text_negation');
							$text=$document['text'];
							$text=clean_for_regular_expression($text);
							if ($text!='') {		
								$text=preg_replace("/\n/"," ",$text);
								$out=array();
								
								$regexp_clean=trim(clean_for_regexp ($regexp));
								if (preg_match_all("/.{0,10}$regexp_clean.{0,10}/i","$text",$out,  PREG_PATTERN_ORDER )) {
									$j=0;
									if ($regexp_index!='') {
										foreach ($out[$regexp_index] as $value) {
											$table_appercu[]=array('document_date'=>$document['document_date'], 'appercu'=>$out[0][$j], 'document_num'=>$document_num, 'query_highlight'=>$regexp,'certainty'=>'-1');
											$j++;
										}
									} else {
										$table_appercu[]=array('document_date'=>$document['document_date'], 'appercu'=>$out[0][0], 'document_num'=>$document_num, 'query_highlight'=>$regexp,'certainty'=>'-1');
									}
								}
							}
						}
					}
				}
		 	}

			if ($item_local_code=='' && $regexp=='' && $document_search!='') {
					// NG Sophie 2020 03 11
				$tableau_result=array();
			 	$tableau_result=search_patient_document_new ($patient_num,'text_non_auto',$document_search,1,'patient_text','text',$query_filter_ecrf,$period);
				$nb_doc_found=0;
				foreach ($tableau_result as $document) {
				 	$document_num=$document['document_num'];
				 	if ($document_num!='') {
			 			$nb_doc_found++;
			 			if ($nb_doc_found<$nb_doc_found_limite) {
							//$document=get_document($document_num,'patient_text_affirmation');
							$text=$document['text'];
							if ($text!='') {		
								$text=preg_replace("/\n/"," ",$text);
								$out=array();
								
								$j=0;
								if ($item_type=='date') {
									$table_value[]=array('sous_item_value'=>$document['document_date'],'document_date'=>$document['document_date']);
								} else if (($item_type=='numeric' || $item_type=='number') && preg_match("/[^a-z]age[^a-z]/i"," $item_str ")) {
									if ($document['document_date']!='') {
										$age_patient=get_age_patient ($patient_num, $document['document_date'],'years');
										$table_value[]=array('sous_item_value'=>$age_patient,'document_date'=>$document['document_date']);
									}
								} 
								$table_appercu[]=array('document_date'=>$document['document_date'], 'appercu'=>$out[0][0], 'document_num'=>$document_num, 'query_highlight'=>$document_search,'certainty'=>'1');
							}
						}
					}
				}
				
				
				$tableau_result=array();
			 	$tableau_result=search_patient_document_new ($patient_num,'text_non_auto',$document_search,-1,'patient_text','text',$query_filter_ecrf,$period);
				$nb_doc_found=0;
				foreach ($tableau_result as $document) {
				 	$document_num=$document['document_num'];
				 	if ($document_num!='') {
			 			$nb_doc_found++;
			 			if ($nb_doc_found<$nb_doc_found_limite) {
							$text=$document['text'];
							if ($text!='') {		
								$text=preg_replace("/\n/"," ",$text);
								$out=array();
								
								$table_appercu[]=array('document_date'=>$document['document_date'], 'appercu'=>$out[0][0], 'document_num'=>$document_num, 'query_highlight'=>$document_search,'certainty'=>'-1');
							}
						}
					}
				}
			}
		 	
		 	if ($period=='last') {
		 		if (count($table_value)==2) {
		 			$doc_date_0=$table_value[0]['document_date'];
		 			$doc_date_1=$table_value[1]['document_date'];
					date_default_timezone_set('Europe/Paris');
					$date_create_from_format0 =date_create_from_format ('d/m/Y',$doc_date_0);
					$date_create_from_format1 =date_create_from_format ('d/m/Y',$doc_date_1);
					if ($date_create_from_format0 > $date_create_from_format1) {
						$table_tmp=array();
						$table_tmp[0]=$table_value[0];
						$table_tmp[1]=$table_value[1];
						$table_tmp_appercu=array();
						$table_tmp_appercu[0]=$table_appercu[0];
						$table_tmp_appercu[1]=$table_appercu[1];
						$table_value=array();
						$table_value[1]=$table_tmp[0];
						$table_value[0]=$table_tmp[1];
						$table_tmp_appercu=array();
						$table_tmp_appercu[1]=$table_appercu[0];
						$table_tmp_appercu[0]=$table_appercu[1];
					}
		 		}
		 	}
		 	
		 	if ($period=='first') {
		 		if (count($table_value)==2) {
		 			$doc_date_0=$table_value[0]['document_date'];
		 			$doc_date_1=$table_value[1]['document_date'];
					date_default_timezone_set('Europe/Paris');
					$date_create_from_format0 =date_create_from_format ('d/m/Y',$doc_date_0);
					$date_create_from_format1 =date_create_from_format ('d/m/Y',$doc_date_1);
					if ($date_create_from_format0 < $date_create_from_format1) {
						$table_tmp=array();
						$table_tmp[0]=$table_value[0];
						$table_tmp[1]=$table_value[1];
						$table_tmp_appercu=array();
						$table_tmp_appercu[0]=$table_appercu[0];
						$table_tmp_appercu[1]=$table_appercu[1];
						$table_value=array();
						$table_value[1]=$table_tmp[0];
						$table_value[0]=$table_tmp[1];
						$table_tmp_appercu=array();
						$table_tmp_appercu[1]=$table_appercu[0];
						$table_tmp_appercu[0]=$table_appercu[1];
					}
		 		}
		 	}
			
			if (count($table_value)==0) {
				$tableau_data_struct=search_patient_document_new ($patient_num,'data',$item_str,'','','',$query_filter_ecrf,$period);
				$nb_doc_found=0;
			 	foreach ($tableau_data_struct as $document) {
				 	$document_num=$document['document_num'];
				 	if ($document_num!='') {
			 			$nb_doc_found++;
			 			if ($nb_doc_found<$nb_doc_found_limite) {
							//$document=get_document($document_num,'') ;
					 		$value=$document['value'];
					 		$concept_str=$document['concept_str'];
					 		$concept_code=$document['concept_code'];
					 		$info=$document['info'];
							//$value_global.="data:".$document['document_date'].":$info:$val;";
							if ($item_type=='date') {
								$table_value[]=array('sous_item_value'=>$document['document_date'],'document_date'=>$document['document_date']);
							} else if (($item_type=='numeric' || $item_type=='number') && preg_match("/[^a-z]age[^a-z]/i"," $item_str ")) {
								if ($document['document_date']!='') {
									$age_patient=get_age_patient ($patient_num, $document['document_date'],'years');
									$table_value[]=array('sous_item_value'=>$age_patient,'document_date'=>$document['document_date']);
								}
							} else {
								$table_value[]=array('sous_item_value'=>$value,'concept_str'=>$concept_str,'concept_code'=>$concept_code,'info'=>$info,'document_date'=>$document['document_date']);
							}
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
			$value=html_replace_supinf($value);
			$list_sub_item.="<li class='li_ecrf'>$value</li>";
		}
	}
	if ($display!='input') {
		$list_sub_item.="</ul>";
	}
	return $list_sub_item;
}
function html_replace_supinf($str) {
	$str=str_replace(">","&gt",$str);
	$str=str_replace("<","&lt",$str);
	return $str;
}

function list_ecrf_item ($ecrf_num,$ecrf_item_num_specific,$user_num,$option) {
	global $dbh,$tableau_global_document_origin_code;
	$table_of_ecrf_functions=get_table_of_ecrf_functions ();

	$list_regexp=get_list_regexp_user($user_num);
	$list_regexp_select="<option>".get_translation('SELECT_A_PATTERN','Selectionner un pattern')."</option><optgroup label='My Patterns'>";
	foreach ($list_regexp as $regexp)  {
		$description=preg_replace("/\n\r/"," ",$regexp['DESCRIPTION']);
		$description=preg_replace("/\"/"," ",$description);
		$title=preg_replace("/\"/"," ",$regexp['TITLE']);
		$regexp_num=$regexp['REGEXP_NUM'];
		$list_regexp_select.="<option value='$regexp_num'>$title </option>";
	}
	$list_regexp_select.="</optgroup>";
	$list_regexp_select.="<optgroup label='Other Patterns'>";
	$list_regexp_not_mine=get_list_regexp_shared_not_mine($user_num);
	foreach ($list_regexp_not_mine as $regexp)  {
		$regexp_num=$regexp['REGEXP_NUM'];
		$description=preg_replace("/\n\r/"," ",$regexp['DESCRIPTION']);
		$description=preg_replace("/\"/"," ",$description);
		$title=preg_replace("/\"/"," ",$regexp['TITLE']);
		$user=get_user_info ($regexp['USER_NUM_CREATION']);
		$nom_prenom_creation=$user['lastname']." ".$user['firstname'];
		$list_regexp_select.="<option value='$regexp_num'>$title ($nom_prenom_creation)</option>";
	}
	$list_regexp_select.="</optgroup>";
	
	
	$list_ecrf_item_div="";
	$list_ecrf_item="";
	if ($ecrf_num!='') {
		$autorisation_ecrf_voir=autorisation_ecrf_voir ($ecrf_num,$user_num);
		$autorisation_ecrf_modifier=autorisation_ecrf_modifier ($ecrf_num,$user_num);
		
		if ($autorisation_ecrf_voir=='ok') {
			if ($option=='display_ecrf_all_items') {
				$list_ecrf_item.= "<table border=\"0\" id=\"id_tableau_ecrf_list_item\" class=\"tablefin\" cellpadding=\"3\">
				<thead>
				<tr>
				<th>".get_translation('ORDER','Ordre')."</th>
				<th>".get_translation('ITEM_NAME',"Nom de l'item")."</th>
				<th>".get_translation('TYPE','Type')."</th>
				<th>".get_translation('FILTER_DOCUMENT','Filtre document')."</th>
				<th>".get_translation('VALUE','Valeurs')."</th>
				<th>".get_translation('PATTERN','Pattern')."</th>
				<th>".get_translation('PATTERN_INDEX','Pattern Index')."</th>
				<th>".get_translation('EXISTING_FUNCTIONS','Fonctions existantes')."</th>
				<th>".get_translation('DOCUMENT_SOURCE','Source de document')."</th>
				<th>".get_translation('LOCAL_CODES','Local Codes')."<br><a href=\"outils.php?action=outil_thesaurus\" target=\"_blank\">".get_translation('SEARCH_FOR_A_CODE','Rechercher un code')."</a></th>
				<th>".get_translation('EXT_NAME','Noms Externes')."</th>
				<th>".get_translation('EXT_CODES','Codes Externes')."</th>
				<th>".get_translation('PERIOD','Période')."</th>
				";
				if ($autorisation_ecrf_modifier=='ok') {
					$list_ecrf_item.= "<th>X</th>";
				}
				$list_ecrf_item.= "</tr></thead><tbody>";
			}
		
			$list_ecrf_items=get_list_ecrf_items($ecrf_num,$ecrf_item_num_specific);
			foreach ($list_ecrf_items as $item) {
				$ecrf_item_num=$item['ecrf_item_num'];
				$item_str=$item['item_str'];
				$item_type=$item['item_type'];
				$document_search=$item['document_search'];
				$item_ext_name=$item['item_ext_name'];
				$item_ext_code=$item['item_ext_code'];
				$regexp=$item['regexp'];
				$regexp_index=$item['regexp_index'];
				$item_local_code=$item['item_local_code'];
				$period=$item['period'];
				$item_order=$item['item_order'];
				$ecrf_function=$item['ecrf_function'];
				$ecrf_function_label=$table_of_ecrf_functions[$ecrf_function];
				$document_origin_code=$item['document_origin_code'];
				$document_origin_code_label=$tableau_global_document_origin_code[$document_origin_code];
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
				$list_select_ecrf_function="<select id=\"id_select_ecrf_function_$ecrf_item_num\" name=\"ecrf_function\" onchange=\"delete_ecrf_all_sub_items('$ecrf_num','$ecrf_item_num');get_automatic_javascript_ecrf_functions('$ecrf_num','$ecrf_item_num');
		\"><option value=''>".get_translation('NO_FUNCTION','Pas de fonction')."</option>";	
				foreach ($table_of_ecrf_functions as $function => $libelle) {
					if ($ecrf_function==$function) {
						$select='selected';
					} else {
						$select='';
					}
					$list_select_ecrf_function.="<option value='$function' $select>$libelle</option>";
				}
				$list_select_ecrf_function.="</select>";
				
				
				$list_select_document_origin_code="<select id=\"id_select_document_origin_code_$ecrf_item_num\" name=\"document_origin_code\"><option value=''>".get_translation('ALL_SOURCES','Toutes les sources')."</option>";	
				foreach ($tableau_global_document_origin_code as $origin_code => $libelle) {
					if ($document_origin_code==$origin_code) {
						$select='selected';
					} else {
						$select='';
					}
					$list_select_document_origin_code.="<option value='$origin_code' $select>$libelle</option>";
				}
				$list_select_document_origin_code.="</select>";
				
				
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

				$regexp_encode_html=html_replace_supinf($regexp);
				$option_display='under';
				if ($autorisation_ecrf_modifier=='ok' && $option_display=='inside') {
					$list_ecrf_item.= "<tr id=\"id_tr_ecrf_item_$ecrf_item_num\">";
					
					$list_ecrf_item.= "<td  style=\"vertical-align:top;\" onclick=\"modify_ecrf_item($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_item_order_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$item_order</span>
							<span id=\"id_span_modif_item_order_$ecrf_item_num\" style=\"display:none;\" class=\"id_span_modif_ecrf_item_$ecrf_item_num\">
								<input type='text' class='form' size=\"2\" value=\"$item_order\" id=\"id_input_item_order_$ecrf_item_num\" onkeypress=\"if(event.keyCode==13) {save_ecrf_item_all($ecrf_num,$ecrf_item_num);return false;}\" />
							</span>
						</td>";
						
					// item_str 
					$list_ecrf_item.= "<td style=\"width:265px;vertical-align:top;\" onclick=\"modify_ecrf_item($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_item_str_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$item_str</span>
							<span id=\"id_span_modif_item_str_$ecrf_item_num\" style=\"display:none;\" class=\"id_span_modif_ecrf_item_$ecrf_item_num\">
								<textarea class='form' cols=\"30\" rows=\"2\" id=\"id_input_item_str_$ecrf_item_num\" onkeypress=\"if(event.keyCode==13) {save_ecrf_item_all($ecrf_num,$ecrf_item_num);return false;} \">$item_str</textarea>
							</span>
						</td>";
					

					// item_type 
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
					
					// document_search
					$list_ecrf_item.= "<td style=\"width:265px;vertical-align:top;\"  onclick=\"modify_ecrf_item($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_document_search_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$document_search</span>
							<span id=\"id_span_modif_document_search_$ecrf_item_num\" style=\"display:none;\" class=\"id_span_modif_ecrf_item_$ecrf_item_num\">
								<textarea class='form' cols=\"30\" rows=\"2\" id=\"id_input_document_search_$ecrf_item_num\" onkeypress=\"if(event.keyCode==13) {save_ecrf_item_all($ecrf_num,$ecrf_item_num);return false;}\">$document_search</textarea>
							</span>
						</td>";		
						
					// sub_item_local_str 
					$list_ecrf_item.= "<td style=\"width:265px;vertical-align:top;\"  onclick=\"modify_ecrf_item($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_sub_item_local_str_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$list_sub_item_local_str</span>
							<span id=\"id_span_modif_sub_item_local_str_$ecrf_item_num\" style=\"display:none;\" class=\"id_span_modif_ecrf_item_$ecrf_item_num\">
								<div id=\"id_div_modif_sub_item_local_str_$ecrf_item_num\">
									$list_input_sub_item_local_str
								</div>
								<span onclick=\"add_sub_item('$ecrf_num','$ecrf_item_num','');\" class=\"link span_ecrf_link_add_subitem\" id=\"id_span_link_ecrf_$ecrf_item_num\">+ Ajouter un sous item</span>
							</span>
						</td>";		
						
					// regexp / sub_item_regexp
					$list_ecrf_item.= "<td style=\"width:265px;vertical-align:top;\"  onclick=\"modify_ecrf_item($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_regexp_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$regexp_encode_html</span>
							<span id=\"id_span_modif_regexp_$ecrf_item_num\" style=\"display:none;\" class=\"id_span_modif_ecrf_item_$ecrf_item_num\">
								<textarea class='form' cols=\"30\" rows=\"2\" id=\"id_input_regexp_$ecrf_item_num\" onkeypress=\"if(event.keyCode==13) {save_ecrf_item_all($ecrf_num,$ecrf_item_num);return false;}\">$regexp</textarea>
							</span>
							
							<span id=\"id_span_sub_item_regexp_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$list_sub_item_regexp</span>
							<span id=\"id_span_modif_sub_item_regexp_$ecrf_item_num\" style=\"display:none;\" class=\"id_span_modif_ecrf_item_$ecrf_item_num\">
								<div id=\"id_div_modif_sub_item_regexp_$ecrf_item_num\">
									$list_input_sub_item_regexp
								</div>
							</span>
						</td>";
						
					// regexp_index
					$list_ecrf_item.= "<td  style=\"vertical-align:top;\" onclick=\"modify_ecrf_item($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_regexp_index_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$regexp_index</span>
							<span id=\"id_span_modif_regexp_index_$ecrf_item_num\" style=\"display:none;\" class=\"id_span_modif_ecrf_item_$ecrf_item_num\">
								<input type='text' class='form' size=\"2\" value=\"$regexp_index\" id=\"id_input_regexp_index_$ecrf_item_num\" onkeypress=\"if(event.keyCode==13) {save_ecrf_item_all($ecrf_num,$ecrf_item_num);return false;}\" />
							</span>
						</td>";
						
					// ecrf_function
					$list_ecrf_item.= "<td style=\"width:265px;vertical-align:top;\"  onclick=\"modify_ecrf_item($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_ecrf_function_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$ecrf_function_label</span>
							<span id=\"id_span_modif_ecrf_function_$ecrf_item_num\" style=\"display:none;\" class=\"id_span_modif_ecrf_item_$ecrf_item_num\">
								<div id=\"id_div_modif_ecrf_function_$ecrf_item_num\">
									$list_select_ecrf_function
								</div>
							</span>
						</td>";
						
					// document_origin_code
					$list_ecrf_item.= "<td style=\"width:265px;vertical-align:top;\"  onclick=\"modify_ecrf_item($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_document_origin_code_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$document_origin_code_label</span>
							<span id=\"id_span_modif_document_origin_code_$ecrf_item_num\" style=\"display:none;\" class=\"id_span_modif_ecrf_item_$ecrf_item_num\">
								<div id=\"id_div_modif_document_origin_code_$ecrf_item_num\">
									$list_select_document_origin_code
								</div>
							</span>
						</td>";
								
					// sub_item_local_code
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
						
					// item_ext_name						
					$list_ecrf_item.= "<td style=\"width:265px;vertical-align:top;\"  onclick=\"modify_ecrf_item($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_item_ext_name_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$item_ext_name</span>
							<span id=\"id_span_modif_item_ext_name_$ecrf_item_num\" style=\"display:none;\" class=\"id_span_modif_ecrf_item_$ecrf_item_num\">
								<input type=\"text\" class='form' cols=\"15\" value=\"$item_ext_name\" id=\"id_input_item_ext_name_$ecrf_item_num\" onkeypress=\"if(event.keyCode==13) {save_ecrf_item_all($ecrf_num,$ecrf_item_num);return false;}\">
							</span>
						</td>";

					// item_ext_code		
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
						
					// period	
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
				} else 	if ($autorisation_ecrf_modifier=='ok' && $option_display=='under') {
				
				
					$list_ecrf_item.= "<tr id=\"id_tr_ecrf_item_$ecrf_item_num\" class=\"ecrf_list_item\">";
					$list_ecrf_item.= "<td class=\"ecrf_list_item\" onclick=\"modify_ecrf_item_under($ecrf_num,$ecrf_item_num);\">
							<a name=\"anchor_ecrf_item_$ecrf_item_num\"></a>
							<div id=\"id_span_item_order_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$item_order</div>
							</td>";
						
					// item_str 
					$list_ecrf_item.= "<td class=\"ecrf_list_item\" style=\"width:265px;\" onclick=\"modify_ecrf_item_under($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_item_str_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$item_str</span>
						</td>";
					
					$item_type_str="";
					// item_type 
					if ($item_type=='radio') {
						$item_type_str=get_translation('UNIQUE_CHOICE','Choix unique');
					}
					if ($item_type=='numeric') {
						$item_type_str=get_translation('NUMBER','Nombre');
					}
					if ($item_type=='text') {
						$item_type_str=get_translation('TEXT','Texte libre');
					}
					if ($item_type=='list') {
						$item_type_str=get_translation('MULTIPLE_CHOICE','Choix multiple');
					}
					if ($item_type=='date') {
						$item_type_str=get_translation('DATE','Date');
					}
					$list_ecrf_item.= "<td class=\"ecrf_list_item\" style=\"width:147px;\" onclick=\"modify_ecrf_item_under($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_item_type_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$item_type_str</span>
						</td>";
					
					// document_search
					$list_ecrf_item.= "<td class=\"ecrf_list_item\" style=\"width:265px;\" onclick=\"modify_ecrf_item_under($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_document_search_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$document_search</span>
						</td>";		
					
					// sub_item_local_str 
					$list_ecrf_item.= "<td class=\"ecrf_list_item\" style=\"width:265px;\" onclick=\"modify_ecrf_item_under($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_sub_item_local_str_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$list_sub_item_local_str</span>
						</td>";		
						
					// regexp / sub_item_regexp
					$list_ecrf_item.= "<td class=\"ecrf_list_item\" style=\"width:265px;\" onclick=\"modify_ecrf_item_under($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_regexp_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$regexp_encode_html</span>
							<span id=\"id_span_sub_item_regexp_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$list_sub_item_regexp</span>
						</td>";
						
					// regexp_index
					$list_ecrf_item.= "<td class=\"ecrf_list_item\" onclick=\"modify_ecrf_item_under($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_regexp_index_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$regexp_index</span>
						</td>";
						
					// ecrf_function
					$list_ecrf_item.= "<td class=\"ecrf_list_item\" style=\"width:265px;\" onclick=\"modify_ecrf_item_under($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_ecrf_function_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$ecrf_function_label</span>
						</td>";
						
					// document_origin_code
					$list_ecrf_item.= "<td class=\"ecrf_list_item\" style=\"width:265px;\" onclick=\"modify_ecrf_item_under($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_document_origin_code_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$document_origin_code_label</span>
						</td>";
								
					// sub_item_local_code
					$list_ecrf_item.= "<td class=\"ecrf_list_item\" style=\"width:265px;\" onclick=\"modify_ecrf_item_under($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_item_local_code_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$item_local_code</span>
							<span id=\"id_span_sub_item_local_code_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$list_sub_item_local_code</span>
						</td>";
						
					// item_ext_name						
					$list_ecrf_item.= "<td class=\"ecrf_list_item\" style=\"width:265px;\" onclick=\"modify_ecrf_item_under($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_item_ext_name_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$item_ext_name</span>
						</td>";

					// item_ext_code		
					$list_ecrf_item.= "<td class=\"ecrf_list_item\" style=\"width:265px;\" onclick=\"modify_ecrf_item_under($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_item_ext_code_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$item_ext_code</span>
							<span id=\"id_span_sub_item_ext_code_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$list_sub_item_ext_code</span>
						</td>";
						
					// period	
					$list_ecrf_item.= "<td class=\"ecrf_list_item\" style=\"width:265px;\" onclick=\"modify_ecrf_item_under($ecrf_num,$ecrf_item_num);\">
							<span id=\"id_span_period_$ecrf_item_num\" class=\"id_span_ecrf_item_$ecrf_item_num\">$period</span>
						</td>";
					
					$list_ecrf_item.= "<td class=\"ecrf_list_item\"><img src=\"images/mini_poubelle.png\" onclick=\"delete_item_ecrf($ecrf_num,$ecrf_item_num);\" style=\"cursor:pointer;\"></td>";
					$list_ecrf_item.= "</tr>";	
					
					
					$list_ecrf_item_div.= "
					<div id=\"id_div_ecrf_item_modify_$ecrf_item_num\" class=\"class_div_ecrf_item_modify\"> 
						<table border='0' class=\"form_ecrf\">
							<tr><td><strong>".get_translation('ORDER','Ordre')."</strong></td><td><input type='text' class='form' size=\"2\" value=\"$item_order\" id=\"id_input_item_order_$ecrf_item_num\" /></td></tr>
							
							<tr class=\"tr_ecrf_item_existing_functions\"><td><strong>".get_translation('EXISTING_FUNCTIONS','Fonctions existantes')."</strong></td><td>$list_select_ecrf_function
							</td></tr>
							
							<tr class=\"tr_ecrf_item_name\"><td><strong>".get_translation('ITEM_NAME',"Nom de l'item")."</strong></td><td>
							<textarea class='form' cols=\"60\" rows=\"2\" id=\"id_input_item_str_$ecrf_item_num\">$item_str</textarea>
							</td></tr>
		
							<tr class=\"tr_ecrf_item_type\"><td><strong>".get_translation('ITEM_TYPE',"Type de l'item")."</strong> </td><td>
									<select id=\"id_input_item_type_$ecrf_item_num\" class=\"form\" onchange=\"adapt_ecrf_form($ecrf_item_num);\">
										<option value='numeric' ".$select_item_type['numeric'].">".get_translation('NUMBER','Nombre')."</option>
										<option value='text' ".$select_item_type['text'].">".get_translation('TEXT','Texte libre')."</option>
										<option value='list' ".$select_item_type['list'].">".get_translation('MULTIPLE_CHOICE','Choix multiple')."</option>
										<option value='radio' ".$select_item_type['radio'].">".get_translation('UNIQUE_CHOICE','Choix unique')."</option>
										<option value='date' ".$select_item_type['date'].">".get_translation('DATE','Date')."</option>
									</select>
							</td></tr>
							
							<tr class=\"tr_ecrf_item_document_search\"><td><strong>".get_translation('DOCUMENT_SEARCH','Dans les documents contenant')."</strong>
							<br><i>".get_translation('SEARCH_ENGINE_SYNTAX','Syntaxe du moteur de recherche')."</i></td><td>
									<span id=\"id_span_modif_document_search_$ecrf_item_num\">
										<textarea class='form' cols=\"60\" rows=\"2\" id=\"id_input_document_search_$ecrf_item_num\">$document_search</textarea>
									</span>
									</td>
							</tr>
							<tr class=\"tr_ecrf_item_value tr_ecrf_item_multiple\"><td><strong>".get_translation('VALUE','Valeurs')."</strong></td><td>
									<span id=\"id_span_modif_sub_item_local_str_$ecrf_item_num\">
									<table border=0>
										<tr>
										<td>".get_translation('ITEMS','Items')."<br>
											<div id=\"id_div_modif_sub_item_local_str_$ecrf_item_num\">
												$list_input_sub_item_local_str
											</div>
											<span onclick=\"add_sub_item('$ecrf_num','$ecrf_item_num','');\" class=\"link span_ecrf_link_add_subitem\" id=\"id_span_link_ecrf_$ecrf_item_num\">+ Ajouter un sous item</span>
										</td>
										<td class=\"td_ecrf_sub_item_pattern\">".get_translation('PATTERN','Pattern')."<br>
											<span id=\"id_span_modif_sub_item_regexp_$ecrf_item_num\">
												<div id=\"id_div_modif_sub_item_regexp_$ecrf_item_num\">
													$list_input_sub_item_regexp
												</div>
											</span>
										</td>
										<td class=\"td_ecrf_sub_item_local_codes\">".get_translation('LOCAL_CODES','Local Codes')." (<a href=\"outils.php?action=outil_thesaurus\" target=\"_blank\">".get_translation('SEARCH_FOR_A_CODE','Rechercher un code')."</a>)<br>
											<span id=\"id_span_modif_sub_item_local_code_$ecrf_item_num\">
												<div id=\"id_div_modif_sub_item_local_code_$ecrf_item_num\">
													$list_input_sub_item_local_code
												</div>
											</span>
										</td>
										<td class=\"td_ecrf_sub_item_ext_codes\">
											".get_translation('EXT_CODES','Codes Externes')."<br>
											<span id=\"id_span_modif_sub_item_ext_code_$ecrf_item_num\">
												<div id=\"id_div_modif_sub_item_ext_code_$ecrf_item_num\">
													$list_input_sub_item_ext_code
												</div>
											</span>
										</td>
										</tr>
									</table>
									</span>
									
							</td></tr>
							
							<tr class=\"tr_ecrf_item_pattern tr_ecrf_item_notmultiple\"><td><strong>".get_translation('PATTERN','Pattern')."</strong></td><td>
									<span id=\"id_span_modif_regexp_$ecrf_item_num\">
										<textarea class='form' cols=\"60\" rows=\"2\" id=\"id_input_regexp_$ecrf_item_num\">$regexp</textarea>
									</span>
							</td></tr>
							
							<tr class=\"tr_ecrf_item_pattern_index tr_ecrf_item_notmultiple\"><td><strong>".get_translation('PATTERN_INDEX','Pattern Index')."</strong></td><td>
								<span id=\"id_span_modif_regexp_index_$ecrf_item_num\">
									<input type='text' class='form' size=\"2\" value=\"$regexp_index\" id=\"id_input_regexp_index_$ecrf_item_num\" />
								</span> (Numéro de parenthèse contenant la valeur à extraire)
							</td></tr>
							
							<tr class=\"tr_ecrf_item_document_source\"><td><strong>".get_translation('DOCUMENT_SOURCE','Source de document')."</strong></td><td>$list_select_document_origin_code
							</td></tr>
							
							<tr class=\"tr_ecrf_item_local_code tr_ecrf_item_notmultiple\"><td><strong>".get_translation('LOCAL_CODES','Local Codes')."</strong> <a href=\"outils.php?action=outil_thesaurus\" target=\"_blank\">".get_translation('SEARCH_FOR_A_CODE','Rechercher un code')."</a>
								</td><td><span id=\"id_span_modif_item_local_code_$ecrf_item_num\">
									<input type=\"text\" class='form' cols=\"15\" value=\"$item_local_code\" id=\"id_input_item_local_code_$ecrf_item_num\">
								</span>
							</td></tr>
							
							<tr class=\"tr_ecrf_item_extern_name\"><td><strong>".get_translation('EXT_NAME','Noms Externes')."</strong></td><td><input type=\"text\" class='form' cols=\"15\" value=\"$item_ext_name\" id=\"id_input_item_ext_name_$ecrf_item_num\">
							</td></tr>
									
							<tr class=\"tr_ecrf_item_notmultiple\"><td><strong>".get_translation('EXT_CODES','Codes Externes')."</strong>
								</td><td><span id=\"id_span_modif_item_ext_code_$ecrf_item_num\">
									<input type=\"text\" class='form' cols=\"15\" value=\"$item_ext_code\" id=\"id_input_item_ext_code_$ecrf_item_num\" >
								</span>
							</td></tr>
							<tr class=\"tr_ecrf_item_period\"><td><strong>".get_translation('PERIOD','Période')."</strong> </td><td>
							<select id=\"id_input_period_$ecrf_item_num\" class='form'>
								<option value='last' ".$select_period['last'].">".get_translation('LAST_VALUE','Dernière valeur')."</option>
								<option value='first' ".$select_period['first'].">".get_translation('FIRST_VALUE','Première valeur')."</option>
								<option value='all' ".$select_period['all'].">".get_translation('ALL_VALUES','Toutes les valeurs')."</option>
							</select>
							</td></tr>
							
							<tr><td colspan=2><hr></td></tr>
							<tr class=\"tr_ecrf_item_existing_pattern\"><td><strong>".get_translation('COPY_PAST_EXISTING_PATTERN','Copier-coller un pattern existant')."</strong> </td><td>
									<select onchange=\"put_regexp_in_field('id_copy_past_regexp_$ecrf_item_num',this.value);\">$list_regexp_select</select><br>
									<span id=\"id_span_copy_past_regexp_$ecrf_item_num\">
										<textarea class='form' cols=\"60\" rows=\"2\" id=\"id_copy_past_regexp_$ecrf_item_num\"></textarea><br>
										".get_translation('SENTENCE_COPY_PAST_PATTERN',"Vous pouvez copier coller la regexp ci dessus dans un des patterns de votre ecrf").". <br>
										".get_translation('SENTENCE_TEST_REGEXP_ON_PATIENT',"Vous pouvez aussi la tester directement dans le moteur de recherche dans le dossier d'un patient").".
									</span>
							</td></tr>
							
						</table>
					<br>
					<input class=\"form_submit\" type=\"button\" value=\"".get_translation('SAVE','Sauver')."\" onclick=\"save_ecrf_item_all($ecrf_num,$ecrf_item_num);\"> 
					<input class=\"form_cancel\" type=\"button\" value=\"".get_translation('CANCEL','Annuler')."\" onclick=\"modify_ecrf_item_under($ecrf_num,$ecrf_item_num);\">
					</div>
					";
				
				} else {
					$item_type_str="";
					// item_type 
					if ($item_type=='radio') {
						$item_type_str=get_translation('UNIQUE_CHOICE','Choix unique');
					}
					if ($item_type=='numeric') {
						$item_type_str=get_translation('NUMBER','Nombre');
					}
					if ($item_type=='text') {
						$item_type_str=get_translation('TEXT','Texte libre');
					}
					if ($item_type=='list') {
						$item_type_str=get_translation('MULTIPLE_CHOICE','Choix multiple');
					}
					if ($item_type=='date') {
						$item_type_str=get_translation('DATE','Date');
					}
					$list_ecrf_item.= "<tr>
						<td>$item_order</td>
						<td>$item_str</td>
						<td>$item_type_str</td>
						<td>$document_search $list_sub_item_local_str</td>
						<td>$regexp_encode_html $list_sub_item_regexp</td>
						<td>$regexp_index</td>
						<td>$item_local_code</td>
						<td>$item_ext_name</td>
						<td>$item_ext_code</td>
						<td>$period</td>
					</tr>";	
				}			
			}
			if ($option=='display_ecrf_all_items') {
				$list_ecrf_item.= "</tbody></table>";
			}
		}
	}
	if ($option=='display_ecrf_all_items') {
		$res="<div id=\"id_div_ecrf_item_modify\">$list_ecrf_item_div</div>$list_ecrf_item";
	}
	if ($option=='get_ecrf_item_tr') {
		$res="$list_ecrf_item";
	}
	if ($option=='get_ecrf_item_div_modify') {
		$res="$list_ecrf_item_div";
	}
	
	if ($ecrf_item_num_specific!='') {
	//	$res=$list_ecrf_item;
	}
	return $res;
}

function get_patient_ecrf_data_from_ext_database ($user_num,$ecrf_num,$patient_num) {
	global $dbh;
	$res_patient_ecrf=array();
	$tableau_list_ecrf_items=get_list_ecrf_items ($ecrf_num,"");
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

function get_patient_ecrf_data_from_manual ($user_num,$ecrf_num,$patient_num,$ecrf_patient_event_num) {
	global $dbh;
	$res_patient_ecrf=array();
	$tableau_list_ecrf_items=get_list_ecrf_items ($ecrf_num,"");
	
	$patient_ecrf_data_manual = get_ecrf_patient_manual_data($user_num,$ecrf_num, $patient_num,$ecrf_patient_event_num);
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

function get_ecrf_patient_manual_data($user_num,$ecrf_num, $patient_num,$ecrf_patient_event_num) {
	global $dbh;
	$patient_ecrf_data_manual = array();
	$req_event="";
	if ($ecrf_patient_event_num!='') {
		$req_event=" and ecrf_patient_event_num=$ecrf_patient_event_num ";
	} else {
		$req_event=" and ecrf_patient_event_num is null ";
	}
	$sel_ecrf=oci_parse($dbh,"select ecrf_answer_num,ecrf_item_num,user_num,user_value from DWH_ECRF_ANSWER where ecrf_num=$ecrf_num and patient_num=$patient_num and user_num=$user_num $req_event order by ecrf_item_num ");
	oci_execute($sel_ecrf);
	while ($r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$ecrf_answer_num=$r['ECRF_ANSWER_NUM'];
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

function get_ecrf_patient_validation_answer ($ecrf_answer_num) {
	global $dbh;
	$res = array();
	if ($ecrf_answer_num!='') {
		$sel_ecrf=oci_parse($dbh,"select ecrf_answer_num,ecrf_item_num,user_num,user_value,user_validation,to_char(user_validation_date,'DD/MM/YYYY HH24:MI') as user_validation_date from DWH_ECRF_ANSWER where ecrf_answer_num=$ecrf_answer_num  ");
		oci_execute($sel_ecrf);
		$r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
		$ecrf_answer_num=$r['ECRF_ANSWER_NUM'];
		$ecrf_item_num=$r['ECRF_ITEM_NUM'];
		$user_value=$r['USER_VALUE'];
		$res['user_validation']=$r['USER_VALIDATION'];
		$res['user_validation_date']=$r['USER_VALIDATION_DATE'];
	}
	return  $res;
}


function get_ecrf_patient_automated_data($user_num,$ecrf_num, $patient_num,$ecrf_patient_event_num) {
	global $dbh;
	$patient_ecrf_data_auto = array();
	$req_event="";
	if ($ecrf_patient_event_num!='') {
		$req_event=" and ecrf_patient_event_num=$ecrf_patient_event_num ";
	} else {
		$req_event=" and ecrf_patient_event_num is null ";
	}
	$sel_ecrf=oci_parse($dbh,"select ecrf_item_num,user_num,automated_value,ecrf_item_extract from DWH_ECRF_ANSWER where ecrf_num=$ecrf_num and patient_num=$patient_num and user_num=$user_num $req_event order by ecrf_item_num ");
	oci_execute($sel_ecrf);
	while ($r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$ecrf_item_num=$r['ECRF_ITEM_NUM'];
		if ($r['AUTOMATED_VALUE']!='') {
			$patient_ecrf_data_auto['automated_value'][$ecrf_item_num]=$r['AUTOMATED_VALUE'];
		}
		if ($r['ECRF_ITEM_EXTRACT']) {
			$patient_ecrf_data_auto['ecrf_item_extract'][$ecrf_item_num]=$r['ECRF_ITEM_EXTRACT']->load();
		}
	}
      return  $patient_ecrf_data_auto;
}

 function insert_result_auto_ecrf ($patient_num,$ecrf_num,$ecrf_item_num,$user_num,$ecrf_patient_event_num,$table_value,$table_appercu,$final_automatic_value) {
	global $dbh;

	$table_final=array("value"=>$table_value,"overview"=>$table_appercu);
	$json_final=serialize($table_final);
	
	$already_done=test_ecrf_result_already_done($ecrf_num,$patient_num,$user_num,$ecrf_patient_event_num,$ecrf_item_num);
	if ($already_done=='false') {
		$ecrf_answer_num=get_uniqid();
		$requeteins="insert into DWH_ECRF_ANSWER (ecrf_answer_num,patient_num,ecrf_num,ecrf_item_num,user_num,ecrf_patient_event_num,automated_value, ecrf_item_extract ,automated_value_date) values 
					($ecrf_answer_num,$patient_num,$ecrf_num,$ecrf_item_num,$user_num,'$ecrf_patient_event_num',:final_automatic_value,:json_final,sysdate) ";
                $stmt = ociparse($dbh,$requeteins);
                $rowid = ocinewdescriptor($dbh, OCI_D_LOB);
                ocibindbyname($stmt, ":json_final",$json_final);
                ocibindbyname($stmt, ":final_automatic_value",$final_automatic_value);
                $execState = ociexecute($stmt);
                ocifreestatement($stmt);
	} else {
		$ecrf_answer_num=get_ecrf_answer_num($user_num,$ecrf_num,$patient_num,$ecrf_patient_event_num,$ecrf_item_num);
		$requeteins="update  DWH_ECRF_ANSWER set automated_value=:final_automatic_value,ecrf_item_extract=:json_final ,automated_value_date=sysdate where ecrf_answer_num=$ecrf_answer_num ";
                $stmt = ociparse($dbh,$requeteins);
                $rowid = ocinewdescriptor($dbh, OCI_D_LOB);
                ocibindbyname($stmt, ":json_final",$json_final);
                ocibindbyname($stmt, ":final_automatic_value",$final_automatic_value);
                $execState = ociexecute($stmt);
                ocifreestatement($stmt);
	}
        return $ecrf_answer_num;
}

function test_ecrf_result_already_done($ecrf_num,$patient_num,$user_num,$ecrf_patient_event_num,$ecrf_item_num) {
	global $dbh;

	$req_event='';
	if ($ecrf_patient_event_num!='') {
		$req_event=" and ecrf_patient_event_num=$ecrf_patient_event_num";
	}  else {
		$req_event=" and ecrf_patient_event_num is null ";
	}
	
	if ($ecrf_item_num!='') {
		$req_event_item_num=" and ecrf_item_num=$ecrf_item_num";
	}  else {
		$req_event_item_num="";
	}
	
	$sel_ecrf=oci_parse($dbh,"select count(*) as verif from DWH_ECRF_ANSWER where ecrf_num=$ecrf_num and patient_num=$patient_num and user_num=$user_num $req_event $req_event_item_num");
	oci_execute($sel_ecrf);
	$r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
	$verif=$r['VERIF'];
	if ($verif>0) {
		$already_done='true';
	} else {
		$already_done='false';
	}
        return $already_done;
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
				if ($_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_droit_fuzzy_display']=='ok') {
					$author='[AUTHOR]';
					$document_date='[DATE]';
				}
				if ($_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_droit_anonymized']=='ok') {
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
	$tableau_list_ecrf_patients_user=get_list_ecrf_patients ($ecrf_num,'');
	$border='';
	if ($option_excel=='') {
		$thead_link="<th>Link</th><th>Del</th>";
		$border=" border='1' ";
	}

	print "<table class=\"tablefin\" $border><thead>
	<tr>
	$thead_link
        <th>".get_translation('HOSPITAL_PATIENT_ID','IPP')."</th>
        <th>".get_translation('LASTNAME','Lastname')."</th>
        <th>".get_translation('FIRSTNAME','Prénom')."</th>
        <th>".get_translation('DATE_OF_BIRTH_SHORT','DDN')."</th>
	<th>".get_translation('SEX','Sexe')."</th>
	<th>User</th>
	<th>Suivi</th>
	";
	
	$tableau_list_ecrf_items=get_list_ecrf_items ($ecrf_num,"");
	foreach ($tableau_list_ecrf_items as $item) {
                $ecrf_item_num=$item['ecrf_item_num'];
	        $item_str=$item['item_str'];
	        $item_type=$item['item_type'];
	        print "<th>$item_str</th>";
	}
	
	print "
	</thead>
	<tbody>";
	$tab_patient=get_list_patient ("select  patient_num from dwh_ecrf_answer  where ecrf_num=$ecrf_num");
	foreach ($tableau_list_ecrf_patients_user as $ecrf_patient) {
		$patient_num=$ecrf_patient['patient_num'];
		$user_value_date=$ecrf_patient['user_value_date'];
		$automated_value_date=$ecrf_patient['automated_value_date'];
		$user_num_ecrf=$ecrf_patient['user_num'];
		$ecrf_patient_event_num=$ecrf_patient['ecrf_patient_event_num'];
		
		$event="";
		if ($ecrf_patient_event_num!='') {
			$ecrf_patient_event=get_ecrf_patient_event ($ecrf_num ,$patient_num,$ecrf_patient_event_num);
			if (is_array($ecrf_patient_event[0])) {
				$date_patient_ecrf=$ecrf_patient_event[0]['date_patient_ecrf'];
				$nb_days_before=$ecrf_patient_event[0]['nb_days_before'];
				$nb_days_after=$ecrf_patient_event[0]['nb_days_after'];
				$event_id=$ecrf_patient_event[0]['event_id'];
				$event="Suivi $event_id ($date_patient_ecrf -$nb_days_before j/+$nb_days_after)";
			}
		}
		
		//$tab_patient=get_patient ($patient_num,'');
		$lastname=$tab_patient[$patient_num]['LASTNAME'];
		$firstname=$tab_patient[$patient_num]['FIRSTNAME'];
		$birth_date=$tab_patient[$patient_num]['BIRTH_DATE'];
		$hospital_patient_id=$tab_patient[$patient_num]['HOSPITAL_PATIENT_ID'];
		$sex=$tab_patient[$patient_num]['SEX'];
		
		$user_firstname_lastname= get_user_information ($user_num_ecrf,'pn');
		
		print "<tr id=\"id_tr_ecrf_patient_".$ecrf_num."_".$patient_num."_".$user_num_ecrf."_$ecrf_patient_event_num\">";
		if ($option_excel=='') {
			print "<th><a href=\"patient.php?patient_num=$patient_num&ecrf_num_open=$ecrf_num&ecrf_patient_event_num_open=$ecrf_patient_event_num\" target=\"_blank\"><img src=\"images/dossier_patient.png\" alt=\"Dossier du patient\" title=\"Dossier du patient\" height=\"15px\" border=\"0\"></a></th>";
			print "<th>";
			if ($user_num_ecrf==$user_num) {
				print "<img src=\"images/mini_poubelle.png\" onclick=\"delete_patient_ecrf($ecrf_num,$patient_num,$user_num_ecrf,'$ecrf_patient_event_num');\" style=\"cursor:pointer;\">";
			}
			print "</th>";
		}
		print "$tbody_link";
		print "<td>$hospital_patient_id</td><td>$lastname</td><td>$firstname </td><td>$birth_date</td><td>$sex</td>";
		print "<td>$user_firstname_lastname</td>";
		print "<td>$event</td>";
		
		//$max_user_value_date=get_ecrf_patient_max_user_date($user_num,$ecrf_num, $patient_num);
		//if ($max_user_value_date!='') {
			$patient_ecrf_data_manual=get_ecrf_patient_manual_data($user_num_ecrf,$ecrf_num, $patient_num,$ecrf_patient_event_num);
			$backgroundcolor_global='#f0dea2';
		//} else {
			$patient_ecrf_data_auto=get_ecrf_patient_automated_data($user_num_ecrf,$ecrf_num, $patient_num,$ecrf_patient_event_num);
			$backgroundcolor_global='#ffcbcb';
		//}
		foreach ($tableau_list_ecrf_items as $item) {
	                $ecrf_item_num=$item['ecrf_item_num'];
	                //if ($patient_ecrf_data_manual[$ecrf_item_num]!='') {
	                //	$value=$patient_ecrf_data_manual[$ecrf_item_num];
	                //} else {
	                //	$value=$patient_ecrf_data_auto[$ecrf_item_num];
	                //}
			$ecrf_answer_num=get_ecrf_answer_num($user_num_ecrf,$ecrf_num,$patient_num,$ecrf_patient_event_num,$ecrf_item_num);
	                $ecrf_patient_validation_answer=get_ecrf_patient_validation_answer ($ecrf_answer_num);
	                if ($ecrf_patient_validation_answer['user_validation']==1) {
	                	$backgroundcolor='#bfe6bf';
	                	$value=$patient_ecrf_data_manual[$ecrf_item_num];
	                } else {
	                	$backgroundcolor=$backgroundcolor_global;
	                	$value=$patient_ecrf_data_auto['automated_value'][$ecrf_item_num];
	                	if ($option_excel=='excel_validated') {
	                		$value='';
	                	}
	                }
			 print "<td style=\"background-color:$backgroundcolor\">$value</td>";
		}
		print "</tr>";
	}
	print "</tbody></table>";
}

?>