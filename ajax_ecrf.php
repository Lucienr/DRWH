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
include_once("fonctions_ecrf.php");

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

$tableau_ecrf_right=array('utiliser','modifier');


if ($_POST['action']=='ajouter_user_ecrf') {
	$user_num_ecrf=$_POST['user_num_ecrf'];
	$ecrf_num=$_POST['ecrf_num'];
	
	$autorisation_ecrf_modifier=autorisation_ecrf_modifier ($ecrf_num,$user_num_session);
	if ($autorisation_ecrf_modifier=='ok' && $user_num_ecrf!='' &&  $ecrf_num!='') {
	
		delete_ecrf_user_right ($ecrf_num,$user_num_ecrf,'all');
		
		foreach ($tableau_ecrf_right as $right ) {
			insert_ecrf_user_right ($ecrf_num,$user_num_ecrf,$right);
		}
		$ecrf=get_ecrf($ecrf_num);
		$title_ecrf=$ecrf['title_ecrf'];
		sauver_notification ($user_num_session,$user_num_ecrf,'ecrf',$title_ecrf,$ecrf_num);		
	}
	affiche_liste_user_ecrf($ecrf_num,$user_num_session);
}

if ($_POST['action']=='ajouter_droit_ecrf') {
	$user_num_ecrf=$_POST['user_num_ecrf'];
	$ecrf_num=$_POST['ecrf_num'];
	$right=$_POST['right'];
	$option=$_POST['option'];
	$autorisation_ecrf_modifier=autorisation_ecrf_modifier ($ecrf_num,$user_num_session);
	if ($autorisation_ecrf_modifier=='ok' && $user_num_ecrf!='' &&  $ecrf_num!='') {
		delete_ecrf_user_right ($ecrf_num,$user_num_ecrf,$right);
		if ($option=='ajouter') {
			insert_ecrf_user_right ($ecrf_num,$user_num_ecrf,$right);
		}
	}
	affiche_liste_user_ecrf($ecrf_num,$user_num_session);
}

if ($_POST['action']=='supprimer_user_ecrf') {
	$user_num_ecrf=$_POST['user_num_ecrf'];
	$ecrf_num=$_POST['ecrf_num'];
	
	$autorisation_ecrf_modifier=autorisation_ecrf_modifier ($ecrf_num,$user_num_session);
	if ($autorisation_ecrf_modifier=='ok' && $user_num_ecrf!='' &&  $ecrf_num!='') {
		delete_ecrf_user_right ($ecrf_num,$user_num_ecrf,'all');
	}
	 affiche_liste_user_ecrf($ecrf_num,$user_num_session);
}

if ($_POST['action']=='supprimer_ecrf') {
	$ecrf_num=$_POST['ecrf_num'];
	
	$autorisation_ecrf_modifier=autorisation_ecrf_modifier ($ecrf_num,$user_num_session);
	if ($autorisation_ecrf_modifier=='ok' && $ecrf_num!='') {
		delete_ecrf($ecrf_num);
	}
	lister_mes_ecrf_tableau($user_num_session);
}

if ($_POST['action']=='modifier_titre_ecrf') {
	$ecrf_num=$_POST['ecrf_num'];
	$title_ecrf=nettoyer_pour_insert(urldecode($_POST['title_ecrf']));
	
	$autorisation_ecrf_modifier=autorisation_ecrf_modifier ($ecrf_num,$user_num_session);
	if ($autorisation_ecrf_modifier=='ok' && $ecrf_num!='') {
		update_ecrf($ecrf_num,'title_ecrf',$title_ecrf);
	}
	lister_mes_ecrf_tableau($user_num_session);
}

if ($_POST['action']=='modifier_description_ecrf') {
	$ecrf_num=$_POST['ecrf_num'];
	$description_ecrf=trim(nettoyer_pour_insert(urldecode($_POST['description_ecrf'])));
	
	$autorisation_ecrf_modifier=autorisation_ecrf_modifier ($ecrf_num,$user_num_session);
	if ($description_ecrf==get_translation('ADD_ECRF_DESCRIPTION_CLIC_HERE',"Ajouter une description en cliquant ici")) {
		$description_ecrf='';
	}
	if ($autorisation_ecrf_modifier=='ok' && $ecrf_num!='') {
		update_ecrf($ecrf_num,'description_ecrf',$description_ecrf);
	}
	if (trim($description_ecrf)=='' && $autorisation_ecrf_modifier=='ok') {
		$description_ecrf= get_translation('ADD_ECRF_DESCRIPTION_CLIC_HERE',"Ajouter une description en cliquant ici");
	}

	$description_ecrf_voir=preg_replace("/\n/","<br>",$description_ecrf);
	$description_ecrf_voir=preg_replace("/''/","'",$description_ecrf_voir);
	print $description_ecrf_voir;
}


if ($_POST['action']=='modifier_ecrf_period') {
	$ecrf_num=$_POST['ecrf_num'];
	$ecrf_start_date=trim($_POST['ecrf_start_date']);
	$ecrf_end_date=trim($_POST['ecrf_end_date']);
	$ecrf_start_age=trim($_POST['ecrf_start_age']);
	$ecrf_end_age=trim($_POST['ecrf_end_age']);
	$ecrf_start_date=verif_format_date ($ecrf_start_date,'dd/mm/yyyy');
	$ecrf_end_date=verif_format_date ($ecrf_end_date,'dd/mm/yyyy');
	$autorisation_ecrf_modifier=autorisation_ecrf_modifier ($ecrf_num,$user_num_session);
	if ($autorisation_ecrf_modifier=='ok' && $ecrf_num!='') {
	
		update_ecrf($ecrf_num,'ecrf_start_date',$ecrf_start_date);
		update_ecrf($ecrf_num,'ecrf_end_date',$ecrf_end_date);
		update_ecrf($ecrf_num,'ecrf_start_age',$ecrf_start_age);
		update_ecrf($ecrf_num,'ecrf_end_age',$ecrf_end_age);
	}
}

if ($_POST['action']=='modifier_token_ecrf') {
	$ecrf_num=$_POST['ecrf_num'];
	$token_ecrf=trim($_POST['token_ecrf']);
	
	$autorisation_ecrf_voir=autorisation_ecrf_voir ($ecrf_num,$user_num_session);
	if ($autorisation_ecrf_voir=='ok' && $ecrf_num!='') {
		$tab_token=get_ecrf_token ($user_num_session, $ecrf_num);
		if ($tab_token['ecrf_num']=='') {
			insert_ecrf_token ($user_num_session, $ecrf_num,$token_ecrf);
		} else {
			update_ecrf_token ($user_num_session, $ecrf_num,$token_ecrf);
		}
	}
	print $token_ecrf;
}


if ($_POST['action']=='modifier_url_ecrf') {
	$ecrf_num=$_POST['ecrf_num'];
	$url_ecrf=trim($_POST['url_ecrf']);
	
	$autorisation_ecrf_modifier=autorisation_ecrf_modifier ($ecrf_num,$user_num_session);
	if ($autorisation_ecrf_modifier=='ok' && $ecrf_num!='') {
		update_ecrf($ecrf_num,'url_ecrf',$url_ecrf);
	}
	print $url_ecrf;
}




if ($_POST['action']=='importer_item_ecrf') {
	$list_item=urldecode($_POST['list_item']);
	$ecrf_num=$_POST['ecrf_num'];

	$regexp='';
	$item_local_code='';
	$regexp_index='';
	$period='';
	$ecrf_function='';
	$document_origin_code='';
	$autorisation_ecrf_modifier=autorisation_ecrf_modifier ($ecrf_num,$user_num_session);
	if ($autorisation_ecrf_modifier=='ok') {
  		$tableau_item=preg_split("/[\n\r]/",$list_item);
  		foreach ($tableau_item as $item) {
	  		$tab=preg_split("/[\t]/",$item);
  			$item_str=nettoyer_pour_insert(trim($tab[0]));
  			$item_type=trim($tab[1]);
  			$list_sub_item_str=nettoyer_pour_insert(trim($tab[2]));
  			$item_ext_name=nettoyer_pour_insert(trim($tab[3]));
  			$item_ext_code=nettoyer_pour_insert(trim($tab[4]));
  			if ($list_sub_item_str!='') {
  				$tab_sub_item_str=explode(";",$list_sub_item_str);
  				$tab_sub_item_ext_code=explode(";",$item_ext_code);
  				$item_ext_code='';
  				$max_item_order=get_max_ecrf_item_order ($ecrf_num);
  				$item_order=$max_item_order+1;
				$ecrf_item_num=insert_ecrf_item ($ecrf_num ,$item_str,$item_type,'',$item_ext_name,$item_ext_code,$regexp, $item_local_code,$regexp_index,$period,$item_order,$ecrf_function,$document_origin_code) ;
				for ($i=0;$i<count($tab_sub_item_str);$i++) {
					$sub_item_local_str=$tab_sub_item_str[$i];
					$sub_item_local_code='';
					$sub_item_ext_code=$tab_sub_item_ext_code[$i];
					$sub_item_regexp='';
					insert_ecrf_sub_item ($ecrf_item_num ,$sub_item_local_str,$sub_item_local_code, $sub_item_ext_code,$sub_item_regexp);
				}
  			}  else {
				if ($item_str!='' && $item_type!='' ) {
	  				$max_item_order=get_max_ecrf_item_order ($ecrf_num);
	  				$item_order=$max_item_order+1;
					insert_ecrf_item ($ecrf_num ,$item_str,$item_type,'',$item_ext_name,$item_ext_code,$regexp, $item_local_code,$regexp_index,$period,$item_order,$ecrf_function,$document_origin_code) ;
		  		}
		  	}
	  	}
  	}
  	$list_ecrf_item=list_ecrf_item($ecrf_num,$user_num_session);
  	print "$list_ecrf_item";
}

if ($_POST['action']=='save_ecrf_item_all') {
	$ecrf_num=$_POST['ecrf_num'];
	$ecrf_item_num=$_POST['ecrf_item_num'];
	$item_str=nettoyer_pour_insert(trim(urldecode($_POST['item_str'])));
        $item_str=preg_replace("/;plus;/","+",$item_str);
	$item_type=nettoyer_pour_insert(trim(urldecode($_POST['item_type'])));
        $item_type=preg_replace("/;plus;/","+",$item_type);
	$item_list=nettoyer_pour_insert(trim(urldecode($_POST['item_list'])));
        $item_list=preg_replace("/;plus;/","+",$item_list);
	$regexp=nettoyer_pour_insert(trim(urldecode($_POST['regexp'])));
        $regexp=preg_replace("/;plus;/","+",$regexp);
	$regexp_index=nettoyer_pour_insert(trim(urldecode($_POST['regexp_index'])));
        $regexp_index=preg_replace("/;plus;/","+",$regexp_index);
	$item_ext_name=nettoyer_pour_insert(trim(urldecode($_POST['item_ext_name'])));
        $item_ext_name=preg_replace("/;plus;/","+",$item_ext_name);
	$item_ext_code=nettoyer_pour_insert(trim(urldecode($_POST['item_ext_code'])));
        $item_ext_code=preg_replace("/;plus;/","+",$item_ext_code);
	$item_local_code=nettoyer_pour_insert(trim(urldecode($_POST['item_local_code'])));
        $item_local_code=preg_replace("/;plus;/","+",$item_local_code);
	$period=nettoyer_pour_insert(trim(urldecode($_POST['period'])));
        $period=preg_replace("/;plus;/","+",$period);
	$item_order=nettoyer_pour_insert(trim(urldecode($_POST['item_order'])));
        $item_order=preg_replace("/;plus;/","+",$item_order);
	$ecrf_function=nettoyer_pour_insert(trim(urldecode($_POST['ecrf_function'])));
	$document_origin_code=nettoyer_pour_insert(trim(urldecode($_POST['document_origin_code'])));
        
        //$valeur=preg_replace("/;antislash;/","\\",$valeur);

	$autorisation_ecrf_modifier=autorisation_ecrf_modifier ($ecrf_num,$user_num_session);
	if ($autorisation_ecrf_modifier=='ok' && $ecrf_num!='' && $ecrf_item_num!='') {
		if ($item_order!='') {
			$ecrf_item_num_test=test_ecrf_item_order($ecrf_num,$item_order);
			if ($ecrf_item_num_test!='' && $ecrf_item_num_test!=$ecrf_item_num) {
				update_ecrf_item_order($ecrf_num,$item_order);
			}
		}
		if ($item_order=='') {
			$max_item_order=get_max_ecrf_item_order($ecrf_num);
			$item_order=$max_item_order+1;
		}
		update_ecrf_item_all ($ecrf_num,$ecrf_item_num,$item_str,$item_type,$item_list,$regexp,$regexp_index,$item_ext_name,$item_ext_code,$item_local_code,$period,$item_order,$ecrf_function,$document_origin_code);
	}
}

if ($_POST['action']=='save_ecrf_item') {
	$ecrf_num=$_POST['ecrf_num'];
	$ecrf_item_num=$_POST['ecrf_item_num'];
	$variable=$_POST['variable'];
	$valeur=nettoyer_pour_insert(trim(urldecode($_POST['valeur'])));
        $valeur=preg_replace("/;plus;/","+",$valeur);
        //$valeur=preg_replace("/;antislash;/","\\",$valeur);

	$autorisation_ecrf_modifier=autorisation_ecrf_modifier ($ecrf_num,$user_num_session);
	if ($autorisation_ecrf_modifier=='ok' && $ecrf_num!='' && $ecrf_item_num!='' && ($variable=='item_ext_name' or $variable=='item_ext_code'  or $variable=='item_list' or $variable=='item_type' or $variable=='item_str' or $variable=='regexp' or $variable=='regexp_index' or $variable=='item_local_code' or $variable=='period' or $variable=='item_order')) {
		if ($variable=='item_order' && $valeur!='') {
			$ecrf_item_num_test=test_ecrf_item_order($ecrf_num,$valeur);
			if ($ecrf_item_num_test!='' && $ecrf_item_num_test!=$ecrf_item_num) {
				update_ecrf_item_order($ecrf_num,$valeur);
			}
		}
		if ($variable=='item_order' && $valeur=='') {
			$max_item_order=get_max_ecrf_item_order($ecrf_num);
			$valeur=$max_item_order+1;
		}
	
		update_ecrf_item($ecrf_num,$ecrf_item_num,$variable,$valeur);
	}
}
if ($_POST['action']=='save_ecrf_sub_item') {
	$ecrf_num=$_POST['ecrf_num'];
	$ecrf_sub_item_num=$_POST['ecrf_sub_item_num'];
	$ecrf_item_num=$_POST['ecrf_item_num'];
	$variable=$_POST['variable'];
	$valeur=nettoyer_pour_insert(trim(urldecode($_POST['valeur'])));
        $valeur=preg_replace("/;plus;/","+",$valeur);
        //$valeur=preg_replace("/;antislash;/","\\",$valeur);

	$autorisation_ecrf_modifier=autorisation_ecrf_modifier ($ecrf_num,$user_num_session);
	if ($autorisation_ecrf_modifier=='ok' && $ecrf_num!='' && $ecrf_sub_item_num!='' ) {
		update_ecrf_sub_item($ecrf_sub_item_num,$variable,$valeur);
	}
}
if ($_POST['action']=='save_ecrf_sub_item_all') {
	$ecrf_num=$_POST['ecrf_num'];
	$ecrf_sub_item_num=$_POST['ecrf_sub_item_num'];
	$ecrf_item_num=$_POST['ecrf_item_num'];
	$sub_item_local_str=nettoyer_pour_insert(trim(urldecode($_POST['sub_item_local_str'])));
        $sub_item_local_str=preg_replace("/;plus;/","+",$sub_item_local_str);
	$sub_item_local_code=nettoyer_pour_insert(trim(urldecode($_POST['sub_item_local_code'])));
        $sub_item_local_code=preg_replace("/;plus;/","+",$sub_item_local_code);
	$sub_item_ext_code=nettoyer_pour_insert(trim(urldecode($_POST['sub_item_ext_code'])));
        $sub_item_ext_code=preg_replace("/;plus;/","+",$sub_item_ext_code);
	$sub_item_regexp=nettoyer_pour_insert(trim(urldecode($_POST['sub_item_regexp'])));
        $sub_item_regexp=preg_replace("/;plus;/","+",$sub_item_regexp);
        
	$autorisation_ecrf_modifier=autorisation_ecrf_modifier ($ecrf_num,$user_num_session);
	if ($autorisation_ecrf_modifier=='ok' && $ecrf_num!='' && $ecrf_sub_item_num!='' ) {
		update_ecrf_sub_item_all($ecrf_sub_item_num,$sub_item_local_str,$sub_item_local_code,$sub_item_ext_code,$sub_item_regexp);
	}
}

if ($_POST['action']=='delete_item_ecrf') {
	$ecrf_num=$_POST['ecrf_num'];
	$ecrf_item_num=$_POST['ecrf_item_num'];
	$autorisation_ecrf_modifier=autorisation_ecrf_modifier ($ecrf_num,$user_num_session);
	if ($autorisation_ecrf_modifier=='ok') {
		delete_ecrf_item ($ecrf_num ,$ecrf_item_num);
	}
  	$list_ecrf_item=list_ecrf_item($ecrf_num,$user_num_session);
  	print "$list_ecrf_item";
}

if ($_POST['action']=='delete_patient_ecrf') {
	$ecrf_num=$_POST['ecrf_num'];
	$patient_num=$_POST['patient_num'];
	delete_patient_ecrf ($ecrf_num ,$patient_num,$user_num_session);
}

if ($_POST['action']=='delete_ecrf_sub_item') {
	$ecrf_num=$_POST['ecrf_num'];
	$ecrf_item_num=$_POST['ecrf_item_num'];
	$ecrf_sub_item_num=$_POST['ecrf_sub_item_num'];
	$autorisation_ecrf_modifier=autorisation_ecrf_modifier ($ecrf_num,$user_num_session);
	if ($autorisation_ecrf_modifier=='ok') {
		delete_ecrf_sub_item ($ecrf_sub_item_num);
	}
  	$list_ecrf_item=list_ecrf_item($ecrf_num,$user_num_session);
  	print "$list_ecrf_item";
}

if ($_POST['action']=='add_new_item') {
	$ecrf_num=$_POST['ecrf_num'];
	$ecrf_num_item='';
	$item_str='';
	$item_type='';
	$item_list='';
	$item_ext_name='';
	$item_ext_code='';
	$regexp='';
	$item_local_code='';
	$regexp_index='';
	$period='';
	$ecrf_function='';
	$document_origin_code='';
	$autorisation_ecrf_modifier=autorisation_ecrf_modifier ($ecrf_num,$user_num_session);
	if ($autorisation_ecrf_modifier=='ok') {
		$max_item_order=get_max_ecrf_item_order($ecrf_num);
		$item_order=$max_item_order+1;
		$ecrf_item_num=insert_ecrf_item ($ecrf_num ,$item_str,$item_type,$item_list,$item_ext_name,$item_ext_code,$regexp,$item_local_code,$regexp_index,$period,$item_order,$ecrf_function,$document_origin_code) ;
  	}
  	print "$ecrf_item_num";
}

if ($_POST['action']=='add_sub_item') {
	$ecrf_item_num=$_POST['ecrf_item_num'];
	$ecrf_num=$_POST['ecrf_num'];
	$ecrf_sub_item_num='';
	$autorisation_ecrf_modifier=autorisation_ecrf_modifier ($ecrf_num,$user_num_session);
	if ($autorisation_ecrf_modifier=='ok') {
		$ecrf_sub_item_num=insert_ecrf_sub_item ($ecrf_item_num ,'','','','');
  	}
  	print "$ecrf_sub_item_num";
}

if ($_POST['action']=='get_list_ecrf_sub_item_num') {
	$ecrf_num=$_POST['ecrf_num'];
	$ecrf_item_num=$_POST['ecrf_item_num'];
	$autorisation_ecrf_voir=autorisation_ecrf_voir ($ecrf_num,$user_num_session);
	if ($autorisation_ecrf_voir=='ok') {
		$list_ecrf_sub_item_num="";
		$list_ecrf_sub_items=get_list_ecrf_sub_items($ecrf_item_num);
		foreach ($list_ecrf_sub_items as $sub_item) {
			$list_ecrf_sub_item_num.=$sub_item['ecrf_sub_item_num'].";";
		}
		
	}
  	print "$list_ecrf_sub_item_num";
}

if ($_POST['action']=='refresh_ecrf_sub_item') {
	$ecrf_num=$_POST['ecrf_num'];
	$ecrf_item_num=$_POST['ecrf_item_num'];
	$variable=$_POST['variable'];
	$display=$_POST['display'];
	$list_sub_item="";
	$autorisation_ecrf_voir=autorisation_ecrf_voir ($ecrf_num,$user_num_session);
	if ($autorisation_ecrf_voir=='ok') {
		$list_sub_item=display_list_sub_item($ecrf_item_num,$variable,$display);
	}
  	print "$list_sub_item";
}


if ($_POST['action']=='refresh_ecrf_sub_item_all') {
	$ecrf_num=$_POST['ecrf_num'];
	$ecrf_item_num=$_POST['ecrf_item_num'];
	$list_sub_item="";
	$autorisation_ecrf_voir=autorisation_ecrf_voir ($ecrf_num,$user_num_session);
	$tab_variable=array('sub_item_local_str','sub_item_local_code','sub_item_ext_code','sub_item_regexp');
	if ($autorisation_ecrf_voir=='ok') {
		foreach ($tab_variable as $variable ) {
			$list_sub_item_see=display_list_sub_item($ecrf_item_num,$variable,'see');
			$list_sub_item_input=display_list_sub_item($ecrf_item_num,$variable,'input');
			$list_sub_item_see=str_replace('"','\\"',$list_sub_item_see);
			$list_sub_item_input=str_replace('"','\\"',$list_sub_item_input);
			$javascript.="jQuery('#id_span_".$variable."_$ecrf_item_num').html(\"$list_sub_item_see\"); ";
			$javascript.="jQuery('#id_div_modif_".$variable."_$ecrf_item_num').html(\"$list_sub_item_input\"); ";
		}
	}
  	print "$javascript";
}

if ($_POST['action']=='display_ecrf_item') {
	$ecrf_num=$_POST['ecrf_num'];
	$list_ecrf_item='';
	$autorisation_ecrf_voir=autorisation_ecrf_voir ($ecrf_num,$user_num_session);
	if ($autorisation_ecrf_voir=='ok') {
	  	$list_ecrf_item=list_ecrf_item($ecrf_num,$user_num_session);
	}
  	print "$list_ecrf_item";
}

if ($_POST['action']=='afficher_onglet_ecrf_patient') {
	$patient_num=$_POST['patient_num'];
	$tableau_list_ecrf=get_list_ecrf ($user_num_session);
	print "<select id=\"id_select_ecrf\" class=\"form chosen-select\"  data-placeholder=\"".get_translation('SECLECT_AN_ECRF','Sélectionnez un ecrf ')."\" >";
        print "<option value=\"\"></option>";
	foreach ($tableau_list_ecrf as $tab_ecrf) {
                $ecrf_num=$tab_ecrf['ecrf_num'];
                $title_ecrf=$tab_ecrf['title_ecrf'];
                $description_ecrf=$tab_ecrf['description_ecrf'];
	        $nb_patients=$tab_ecrf['nb_patients'];
	        $user_num=$tab_ecrf['user_num'];
         	print "<option value=\"$ecrf_num\">$title_ecrf</option>";
	}
	print "</select>";
	print "<input type=\"button\" onclick=\"start_process_extract_information_ecrf($patient_num);\" value=\"Extract\"><br>
		<div id=\"id_div_result_map_ecrf_process\"></div>
		<div id=\"id_div_result_map_ecrf\"></div>";
}

if ($_POST['action']=='start_process_information_ecrf') {
	$process_num=get_uniqid();
	create_process ($process_num,$user_num_session,0,get_translation('PROCESS_ONGOING','process en cours'),'',"sysdate + 20","ecrf");
	print "$process_num";
}

if ($_POST['action']=='get_process_extract_information_ecrf') {
	$process_num=$_POST['process_num'];
	$tableau=get_process ($process_num);
	print $tableau['STATUS'].";".$tableau['COMMENTARY'];
}

if ($_POST['action']=='validate_ecrf_item') {
	$patient_num=$_POST['patient_num'];
	$ecrf_num=$_POST['ecrf_num'];
	$ecrf_item_num=$_POST['ecrf_item_num'];
	$autorisation_ecrf_voir=autorisation_ecrf_voir ($ecrf_num,$user_num_session);
	if ($autorisation_ecrf_voir=='ok') {
		$ecrf_patient_validation_answer=get_ecrf_patient_validation_answer ($patient_num,$ecrf_num,$ecrf_item_num,$user_num_session);
		if ($ecrf_patient_validation_answer['user_validation']==1) {
			update_ecrf_validate_ecrf_item($patient_num,$ecrf_num,$ecrf_item_num,$user_num_session,0);
			print 'notvalidate';
		} else {
			update_ecrf_validate_ecrf_item($patient_num,$ecrf_num,$ecrf_item_num,$user_num_session,1);
			$json=urldecode($_POST['json']);
			$tab_json=json_decode ($json);
			foreach ($tab_json as $object) {
				foreach ($object as $name => $val) {
					$item_num=preg_replace("/item_num_([0-9]+).*/","$1",$name);
					if (preg_match("/sub_item_num/",$name)) {
						$sub_item_num=preg_replace("/.*sub_item_num_([0-9]+)/","$1",$name);
						$sub_item=get_ecrf_sub_item ($sub_item_num);
						$tab_val[$item_num].=$sub_item['sub_item_local_str'].";";
					} else {  
						$tab_val[$item_num]=$val;
					}
				}
			}
			$tableau_list_ecrf_items=get_list_ecrf_items ($ecrf_num);
			$value=$tab_val[$ecrf_item_num];
			$value=preg_replace("/;$/","",$value);
			update_result_manual_ecrf($patient_num,$ecrf_num,$ecrf_item_num,$user_num_session,$value);
			
			print 'validate';
		}
	}
}


if ($_POST['action']=='extract_information_ecrf') {
	$patient_num=$_POST['patient_num'];
	$ecrf_num=$_POST['ecrf_num'];
	$process_num=$_POST['process_num'];
	$next_patient_num=$_POST['next_patient_num'];
	$cohort_num_patient=$_POST['cohort_num_patient'];
	$k_for_css=0;
	$autorisation_ecrf_voir=autorisation_ecrf_voir ($ecrf_num,$user_num_session);
	if ($autorisation_ecrf_voir=='ok') {
		// GET PATIENT DATA FROM EXT DATABASE (REDCAP) or ALREADY VALIDATED DATA -> DR WAREHOUSE
		$patient_ecrf_data_ext=get_patient_ecrf_data_from_ext_database($user_num_session,$ecrf_num,$patient_num);
		
		$patient_ecrf_data_manual=get_patient_ecrf_data_from_manual($user_num_session,$ecrf_num,$patient_num);
		

		if ($next_patient_num!='') {
			print "<a href=\"patient.php?patient_num=$next_patient_num&cohort_num_patient=$cohort_num_patient&ecrf_num_open=$ecrf_num\">Next patient of the cohort</a> ";
		}
		print "<form action=\"#\" method=\"post\" id=\"id_ecrf_form\">";
		print "<table class=\"tablefin\">";
		// TODO improve security CAREFUL THE TOKEN IS SEND IN THE HTML PAGE
		$tableau_list_ecrf_items=get_list_ecrf_items ($ecrf_num);
		foreach ($tableau_list_ecrf_items as $item) {
	                $ecrf_item_num=$item['ecrf_item_num'];
		        $item_str=$item['item_str'];
		        $item_type=$item['item_type'];
		        $item_ext_name=$item['item_ext_name'];
		        $item_ext_code=$item['item_ext_code'];
		        $regexp=$item['regexp'];
		        $regexp_index=$item['regexp_index'];
		        $item_local_code=$item['item_local_code'];
		        $ecrf_function=$item['ecrf_function'];
		        $document_origin_code=$item['document_origin_code'];
	 		$final_automatic_value="";
	                
	                $ecrf_patient_validation_answer=get_ecrf_patient_validation_answer ($patient_num,$ecrf_num,$ecrf_item_num,$user_num_session);
	                if ($ecrf_patient_validation_answer['user_validation']==1) {
	                	$backgroundcolor='#bfe6bf';
	                } else {
	                	$backgroundcolor='white';
	                }
	                
			update_process ($process_num,0,"Extraction : $item_str","",$user_num_session,'') ;
			list($table_value,$table_appercu)=extract_information_ecrf($patient_num,$ecrf_num,$ecrf_item_num,'');

	              	print " <tr id=\"id_tr_ecrf_item_$ecrf_item_num\" style=\"background-color:$backgroundcolor\"><td>$item_str</td><td nowrap style=\"vertical-align:top\">";

	              	// TO BE REFACTORED (CF RADIO BELOW)
	              	if ($item_type=='list' || $item_type=='radio') {
	              		if ($item_type=='list') {
	              			$input_type='checkbox';
	              		}
	              		if ($item_type=='radio') {
	              			$input_type='radio';
	              		}

				$tab_sub_items=get_list_ecrf_sub_items ($ecrf_item_num);

		 		foreach ($tab_sub_items as $sub_item) {
					$sub_item_local_str=$sub_item['sub_item_local_str'];
					$sub_item_local_code=$sub_item['sub_item_local_code'];
					$sub_item_ext_code=$sub_item['sub_item_ext_code'];
		 			$ecrf_sub_item_num=$sub_item['ecrf_sub_item_num'];
		 			$method='';
		 			//print "<input class=\"class_ecrf_input_$ecrf_item_num\" type=$input_type value=\"1\" name=\"".$item_ext_name."___".$sub_item_ext_code."\" ";
		 			print "<input class=\"class_ecrf_input_$ecrf_item_num\" type=$input_type value=\"1\" name=\"item_num_$ecrf_item_num"."_sub_item_num_".$ecrf_sub_item_num."\" ";
		 			
		 			if ($ecrf_patient_validation_answer['user_validation']==1) {
			 			if($patient_ecrf_data_manual[$ecrf_item_num.".".$ecrf_sub_item_num]==1) {
			 				print " checked ";
			 				$method='(manual)';
			 			}
		 			} else {
			 			if($patient_ecrf_data_ext[$ecrf_item_num.".".$ecrf_sub_item_num]==1) {
			 				print " checked ";
			 				$method='(external database)';
			 			} else if($patient_ecrf_data_manual[$ecrf_item_num.".".$ecrf_sub_item_num]==1) {
			 				print " checked ";
			 				$method='(manual)';
			 			} else {
			 				//'sous_item_value'=>$val,'concept_str'=>$concept_str,'concept_code'=>$concept_code,'info'=>$info,'document_date'
		 					foreach ($table_value as $tab) {
		 						$val=$tab['sous_item_value'];
		 						$concept_str=$tab['concept_str'];
		 						$concept_code=$tab['concept_code'];
		 						$info=$tab['info'];
		 						$document_date=$tab['document_date'];
		 						
		 						if (
		 						// if code == code du sous item
		 						($sub_item_local_code==$concept_code && $concept_code!='' && $sub_item_local_code!='') 
		 						// or sub label = value of data or label + sub label and value of data 
		 						|| ((trim(strtolower("$sub_item_local_str"))==trim(strtolower($val))  || trim(strtolower("$item_str $sub_item_local_str"))==trim(strtolower($val))) && trim($val)!='' && trim($sub_item_local_str)!='')
		 						// or sub label = label of data
		 						|| ( trim(strtolower("$sub_item_local_str"))==trim(strtolower($concept_str)) && trim($concept_str)!='' && trim($sub_item_local_str)!='')
		 						) {
		 							print " checked ";
			 						$method='(auto)';
		 							$final_automatic_value.="$sub_item_local_str;";
		 						}
		 					}
		 				}
		 			}
		 			print "> $sub_item_local_str $method<br>";
 				}
	 			$final_automatic_value=substr($final_automatic_value,0,-1);
 			}

 			if ($item_type=='text') {
	              		$val='';
		 		$method='';
		 		if ($ecrf_patient_validation_answer['user_validation']==1) {
		 			$val=$patient_ecrf_data_manual[$ecrf_item_num];
		 			$method='(manual)';
		 		} else {	
					if($patient_ecrf_data_ext[$ecrf_item_num]!='') {
			 			$val=$patient_ecrf_data_ext[$ecrf_item_num];
			 			$method='(external database)';
			 		} else if($patient_ecrf_data_manual[$ecrf_item_num]!='') {
			 			$val=$patient_ecrf_data_manual[$ecrf_item_num];
			 			$method='(manual)';
			 		} else {
						foreach ($table_value as $tab) {
							$val=$tab['sous_item_value'];
							$concept_str=$tab['concept_str'];
							$concept_code=$tab['concept_code'];
							$info=$tab['info'];
							$document_date=$tab['document_date'];
				 			$method='(auto)';
							print "<span onclick=\"jQuery('.class_ecrf_input_$ecrf_item_num').val('$val');\" style=\"cursor:pointer;\">$document_date : $concept_str $info $val</span><br>";
						}
			 		}
			 	}
 				print "<input class=\"class_ecrf_input_$ecrf_item_num\"  class='form' type=\"text\" value=\"$val\" name=\"item_num_$ecrf_item_num\"> $method<br>";
 				$final_automatic_value="$val";
 			}
 			
 			if ($item_type=='date') {
	              		$val='';
		 		$method='';
		 		if ($ecrf_patient_validation_answer['user_validation']==1) {
		 			$val=$patient_ecrf_data_manual[$ecrf_item_num];
		 			$method='(manual)';
		 		} else {	
					if($patient_ecrf_data_ext[$ecrf_item_num]!='') {
			 			$val=$patient_ecrf_data_ext[$ecrf_item_num];
			 			$method='(external database)';
			 		} else if($patient_ecrf_data_manual[$ecrf_item_num]!='') {
			 			$val=$patient_ecrf_data_manual[$ecrf_item_num];
			 			$method='(manual)';
			 		} else {
						foreach ($table_value as $tab) {
							$val=$tab['sous_item_value'];
							$concept_str=$tab['concept_str'];
							$concept_code=$tab['concept_code'];
							$info=$tab['info'];
							$document_date=$tab['document_date'];
				 			$method='(auto)';
							print "<span onclick=\"jQuery('.class_ecrf_input_$ecrf_item_num').val('$val');\" style=\"cursor:pointer;\">$document_date : $concept_str $info $val</span><br>";
						}
			 		}
			 	}
				print "<input class=\"class_ecrf_input_$ecrf_item_num\"  class='form' type=\"text\" value=\"$val\" name=\"item_num_$ecrf_item_num\"> $method<br>";
 				$final_automatic_value="$val";
 			}
 			
	              	if ($item_type=='numeric' || $item_type=='number') { // Numbers are managed as text in redcap, no need here
	              		$val='';
		 		$method='';
		 		if ($ecrf_patient_validation_answer['user_validation']==1) {
		 			$val=$patient_ecrf_data_manual[$ecrf_item_num];
		 			$method='(manual)';
		 		} else {	
					if($patient_ecrf_data_ext[$ecrf_item_num]!='') {
			 			$val=$patient_ecrf_data_ext[$ecrf_item_num];
			 			$method='(external database)';
			 		} else if($patient_ecrf_data_manual[$ecrf_item_num]!='') {
			 			$val=$patient_ecrf_data_manual[$ecrf_item_num];
			 			$method='(manual)';
			 		} else {
						foreach ($table_value as $tab) {
							$val=$tab['sous_item_value'];
							$concept_str=$tab['concept_str'];
							$concept_code=$tab['concept_code'];
							$info=$tab['info'];
							$document_date=$tab['document_date'];
				 			$method='(auto)';
							print "<span onclick=\"jQuery('.class_ecrf_input_$ecrf_item_num').val('$val');\" style=\"cursor:pointer;\">$document_date : $concept_str $info $val</span><br>";
						}
					}
			 	}
 				print "<input class=\"class_ecrf_input_$ecrf_item_num\"  class='form' type=\"text\" value=\"$val\" name=\"item_num_$ecrf_item_num\"> $method<br>";
	 			$final_automatic_value="$val";
 			}
 			
			insert_result_auto_ecrf($patient_num,$ecrf_num,$ecrf_item_num,$user_num_session,$table_value,$table_appercu,$final_automatic_value);
 			
		 	print "</td>";
		 	print "<td style=\"vertical-align:top\"><img src=\"images/checked.png\" onclick=\"validate_ecrf_item('$ecrf_num','$patient_num','$ecrf_item_num');\" style=\"cursor:pointer\"></td>";
		 	print "<td style=\"vertical-align:top\"><div onmouseup=\"ecrf_justify_my_choice();\" class=\"ecrf_list_doc_found\" id=\"id_ecrf_list_doc_found_$ecrf_item_num\">";
	              	print "<table border=\"0\" width=\"100%\">";
	              	foreach ($table_appercu as $doc_found) {
	              		$k_for_css++;
				if (!preg_match("/[[|?+]/",$doc_found['query_highlight'])) {
		              		$doc_found['query_highlight']=preg_replace("/ /"," or ",trim($doc_found['query_highlight']));
		              	}
		              	$doc_found['query_highlight']=str_replace("\\","\\\\",$doc_found['query_highlight']);
	              		print "<tr id=\"id_ancre_document_$k_for_css\">
	              		<td onclick=\"afficher_document_patient_ecrf($ecrf_item_num,".$doc_found['document_num'].",'id_afficher_document_ecrf','".$doc_found['query_highlight']."',$k_for_css);\" style=\"width:20px;cursor:pointer;border:0px black solid;border-bottom:1px black solid\" class=\"tr_document_patient id_document_patient_".$doc_found['document_num']."\" >".$doc_found['document_date']."</td>
	              		<td onclick=\"afficher_document_patient_ecrf($ecrf_item_num,".$doc_found['document_num'].",'id_afficher_document_ecrf','".$doc_found['query_highlight']."',$k_for_css);\" style=\"cursor:pointer;border:0px black solid;border-bottom:1px black solid\" class=\"tr_document_patient id_document_patient_".$doc_found['document_num']."\" >".$doc_found['appercu']."</td>
	              		</tr>";
	              	}	   
			print "</div></table>";
	              	print "</td></tr>";
		}
		print "</table>";
		print "<input type=\"button\" value=\"Save your modification\" class=\"btn btn-primary btn-block\" onclick=\"save_ecrf_form($patient_num);\"/> ";
		if ($next_patient_num!='') {
			print "<a href=\"patient.php?patient_num=$next_patient_num&cohort_num_patient=$cohort_num_patient&ecrf_num_open=$ecrf_num\">Next patient of the cohort</a> ";
		}
		print "</form>";
		print "<p id=\"id_ecrf_loading_message\"></p>";
		
	        update_process ($process_num,1,"end","",$user_num_session,'') ;
		save_log_page($user_num_session,"extract_information_ecrf");
	}
}



if ($_POST['action']=='filtre_patient_text_ecrf') {
	$patient_num=$_POST['patient_num'];
	$requete=trim(nettoyer_pour_requete_patient(urldecode($_POST['requete'])));
	affiche_liste_document_patient_ecrf($patient_num,$requete);
	save_log_query($user_num_session,'patient',$requete,$patient_num);
		
}


if ($_POST['action']=='list_patient_ecrf') {
	$ecrf_num=$_POST['ecrf_num'];
	$autorisation_ecrf_voir=autorisation_ecrf_voir ($ecrf_num,$user_num_session);
	if ($autorisation_ecrf_voir=='ok') {
		print "<a href=\"export_excel.php?ecrf_num=$ecrf_num&option=ecrf_patient_answer\" target=\"_blank\">".get_translation('DOWNLOAD_IN_EXCEL','Télécharger sur Excel')."</a><br><br>";
		display_table_ecrf_patient_answer ($ecrf_num,$user_num_session,'');

	}
	save_log_page($user_num_session,"list_patient_ecrf");
}

if ($_POST['action']=='save_ecrf_form') {
	$ecrf_num=$_POST['ecrf_num'];
	$patient_num=$_POST['patient_num'];
	$autorisation_ecrf_voir=autorisation_ecrf_voir ($ecrf_num,$user_num_session);
	if ($autorisation_ecrf_voir=='ok') {
		$json=urldecode($_POST['json']);
		$tab_json=json_decode ($json);
		foreach ($tab_json as $object) {
			foreach ($object as $name => $val) {
				$item_num=preg_replace("/item_num_([0-9]+).*/","$1",$name);
				if (preg_match("/sub_item_num/",$name)) {
					$sub_item_num=preg_replace("/.*sub_item_num_([0-9]+)/","$1",$name);
					$sub_item=get_ecrf_sub_item ($sub_item_num);
					$tab_val[$item_num].=$sub_item['sub_item_local_str'].";";
				} else {  
					$tab_val[$item_num]=$val;
				}
			}
		}
		$tableau_list_ecrf_items=get_list_ecrf_items ($ecrf_num);
		foreach ($tableau_list_ecrf_items as $ecrf_item) {
			$ecrf_item_num=$ecrf_item['ecrf_item_num'];
			$value=$tab_val[$ecrf_item_num];
			$value=preg_replace("/;$/","",$value);
			update_result_manual_ecrf($patient_num,$ecrf_num,$ecrf_item_num,$user_num_session,$value);
		}
		save_log_page($user_num_session,"save_ecrf_form");
	}
}


oci_close ($dbh);
oci_close ($dbh_etl);
?>