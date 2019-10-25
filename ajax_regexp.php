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
include_once("fonctions_regexp.php");

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

if ($_POST['action']=='rechercher_regexp') {
	$tmpresult_num=$_POST['tmpresult_num'];
	//$regexp=nettoyer_pour_requete(urldecode($_POST['regexp']));
	//$regexp=trim(nettoyer_pour_requete_patient(urldecode($_POST['regexp'])));
	$regexp=trim(clean_for_regexp(urldecode($_POST['regexp'])));

	$datamart_num=$_POST['datamart_num'];
	$filter_query_user_right=filter_query_user_right("dwh_tmp_result_$user_num_session",$user_num_session,$_SESSION['dwh_droit_all_departments'.$datamart_num],$liste_service_session,$liste_document_origin_code_session);

	$process_num=get_uniqid();
	passthru( "php exec_regexp.php $user_num_session $tmpresult_num $process_num $datamart_num \"$regexp\" \"$filter_query_user_right\">> $CHEMIN_GLOBAL_LOG/log_regexp_$process_num.txt 2>&1 &");
	print "$process_num";
	save_log_query($user_num_session,'regexp',$regexp,'');
}

if ($_POST['action']=='afficher_document_regexp') {
	$document_num=$_POST['document_num'];
	$datamart_num=$_POST['datamart_num'];
	$regexp=urldecode($_POST['regexp']);
        $regexp=str_replace(";plus;","+",$regexp);
        //$regexp=preg_replace("/;plus;/","+",$regexp);
        //$regexp=preg_replace("/;antislash;/","\\",$regexp);

	$afficher_document=afficher_document_regexp ($document_num,$regexp);
	print "$afficher_document";
}



if ($_POST['action']=='verif_process_execute_regexp') {
	$process_num=$_POST['process_num'];
	$process=get_process ($process_num);
	$status=$process['STATUS'];
	$commentary=$process['COMMENTARY'];
	print "$status;$commentary";
}



if ($_POST['action']=='get_regexp_result') {
	$process_num=$_POST['process_num'];
	$process=get_process ($process_num);
	$status=$process['STATUS'];
	$commentary=$process['COMMENTARY'];
	print $process['RESULT'];
}

if ($_POST['action']=='save_regexp') {
	$regexp=urldecode($_POST['regexp']) ;
        $regexp=str_replace(";plus;","+",$regexp);
        //$regexp=preg_replace("/;plus;/","+",$regexp);
        //$regexp=preg_replace("/;antislash;/","\\",$regexp);
	
	$title=urldecode($_POST['title']);
	$description=urldecode($_POST['description']);
	$shared=$_POST['shared'];
	$regexp_num=insert_regexp ($title, $description, $regexp, $user_num_session , $shared);
}

if ($_POST['action']=='modify_regexp') {
	$regexp_num=$_POST['regexp_num'];
	$regexp=urldecode($_POST['regexp']) ;
        $regexp=str_replace(";plus;","+",$regexp);
        //$regexp=preg_replace("/;plus;/","+",$regexp);
        //$regexp=preg_replace("/;antislash;/","\\",$regexp);
	
	$title=urldecode($_POST['title']);
	$description=urldecode($_POST['description']);
	$shared=$_POST['shared'];
	$regexp_num=update_regexp ($regexp_num,$title, $description, $regexp, $user_num_session , $shared);
}

if ($_POST['action']=='delete_regexp') {
	$regexp_num=$_POST['regexp_num'];
	delete_regexp ($regexp_num, $user_num_session);
}

if ($_POST['action']=='list_regexp_select') {
	$list_regexp=get_list_regexp_user($user_num_session);
	$list_regexp_select="<option></option><optgroup label='My Patterns'>";
	foreach ($list_regexp as $regexp)  {
		$description=preg_replace("/\n\r/"," ",$regexp['DESCRIPTION']);
		$description=preg_replace("/\"/"," ",$description);
		$title=preg_replace("/\"/"," ",$regexp['TITLE']);
		$regexp_num=$regexp['REGEXP_NUM'];
		$list_regexp_select.="<option value='$regexp_num'>$title : $description</option>";
	}
	$list_regexp_select.="</optgroup>";
	$list_regexp_select.="<optgroup label='Other Patterns'>";
	$list_regexp_not_mine=get_list_regexp_shared_not_mine($user_num_session);
	foreach ($list_regexp_not_mine as $regexp)  {
		$regexp_num=$regexp['REGEXP_NUM'];
		$description=preg_replace("/\n\r/"," ",$regexp['DESCRIPTION']);
		$description=preg_replace("/\"/"," ",$description);
		$title=preg_replace("/\"/"," ",$regexp['TITLE']);
		$user=get_user_info ($regexp['USER_NUM_CREATION']);
		$nom_prenom_creation=$user['lastname']." ".$user['firstname'];
		$list_regexp_select.="<option value='$regexp_num'>$title ($nom_prenom_creation) : $description</option>";
	}
	$list_regexp_select.="</optgroup>";
	
	
	print "jQuery('#id_select_list_regexp').append(\"$list_regexp_select\");";
}

if ($_POST['action']=='list_regexp_selectold') {
	$list_regexp=get_list_regexp_user($user_num_session);
	print "jQuery('#id_select_list_regexp').append(jQuery('<option></option>').val(\"\").html(\"My Patterns\"));";
	foreach ($list_regexp as $regexp)  {
		$regexp['DESCRIPTION']=preg_replace("/\n\r/"," ",$regexp['DESCRIPTION']);
		print "jQuery('#id_select_list_regexp').append(jQuery('<option></option>').val(\"".$regexp['REGEXP_NUM']."\").html(\"".$regexp['TITLE']." :".$regexp['DESCRIPTION']."\"));";
	}
	print "jQuery('#id_select_list_regexp').append(jQuery('<option></option>').val(\"\").html(\"Other Patterns\"));";
	$list_regexp_not_mine=get_list_regexp_shared_not_mine($user_num_session);
	foreach ($list_regexp_not_mine as $regexp)  {
		$regexp['DESCRIPTION']=preg_replace("/\n\r/"," ",$regexp['DESCRIPTION']);
		$user=get_user_info ($regexp['USER_NUM_CREATION']);
		$nom_prenom_creation=$user['lastname']." ".$user['firstname'];
		print "jQuery('#id_select_list_regexp').append(jQuery('<option></option>').val(\"".$regexp['REGEXP_NUM']."\").html(\"".$regexp['TITLE']." ($nom_prenom_creation) :".$regexp['DESCRIPTION']."\"));";
	}
}

if ($_POST['action']=='select_regexp') {
	$regexp_num=urldecode($_POST['regexp_num']) ;
	$regexp=get_regexp($regexp_num,$user_num_session);
	print $regexp['REGEXP'];
}

if ($_POST['action']=='manage_regexp') {
	$list_regexp_user=get_list_regexp_user($user_num_session);
	print "<table  class=\"tableau_solid_black\">
	<thead>
		<tr><th>Title</th>
		<th>Pattern</th>
		<th>Description</th>
		<th>Shared</th>
		<th>M</th>
		<th>Delete</th>
	</thead>
	";
	foreach ($list_regexp_user as $regexp)  {
		$description=preg_replace("/\n\r/","<br>",$regexp['DESCRIPTION']);
		$title=$regexp['TITLE'];
		$regexp_num=$regexp['REGEXP_NUM'];
		$shared=$regexp['SHARED'];
		$regexp=$regexp['REGEXP'];
		if ($shared==1) {
			 $checked_shared="checked";
		} else {
			 $checked_shared="";
		}
		print "<tr id=\"id_tr_regexp_$regexp_num\">
			<td>
				<span class=\"regexp_$regexp_num\" id=\"id_regexp_title_$regexp_num\" onclick=\"edit_modify_regexp($regexp_num)\">$title</span>
				<span class=\"regexp_modify_$regexp_num\" style=\"display:none\" ><input id=\"id_regexp_input_modify_title_$regexp_num\" type=\"text\" size=\"30\" value=\"$title\"></span>
			</td>
			<td>
				<span class=\"regexp_$regexp_num\" id=\"id_regexp_regexp_$regexp_num\" onclick=\"edit_modify_regexp($regexp_num)\" class=\"filtre_regexp\">$regexp</span>
				<span class=\"regexp_modify_$regexp_num\"  style=\"display:none\">
				<textarea id=\"id_regexp_input_modify_regexp_$regexp_num\" cols=\"40\" rows=\"5\" class=\"filtre_regexp\">$regexp</textarea>
			</td>
			<td>
				<span class=\"regexp_$regexp_num\" id=\"id_regexp_description_$regexp_num\" onclick=\"edit_modify_regexp($regexp_num)\" class=\"filtre_regexp\">$description</span>
				<span class=\"regexp_modify_$regexp_num\"  style=\"display:none\"><textarea id=\"id_regexp_input_modify_description_$regexp_num\" cols=\"40\" rows=\"5\">$description</textarea></span>
			</td>
			<td>
				<span class=\"regexp_$regexp_num\" id=\"id_regexp_shared_$regexp_num\" onclick=\"edit_modify_regexp($regexp_num)\">$shared</span>
				<span class=\"regexp_modify_$regexp_num\" style=\"display:none\"><input id=\"id_regexp_input_modify_shared_$regexp_num\" type=\"checkbox\" value=\"1\" $checked_shared></span>
			</td>
			<td>
				<span class=\"regexp_modify_$regexp_num\" style=\"display:none\"><input type=\"button\" value=\"save\" onclick=\"modify_regexp($regexp_num)\"></span>
			
			</td>
			<td>
				<img src=\"images/poubelle_moyenne.png\" onclick=\"delete_regexp($regexp_num)\" style=\"cursor:pointer\">
			
			</td>
			</tr>";
			
	}
	print "</table>";
}

?>