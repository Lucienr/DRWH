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
include_once "fonctions_admin.php";

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


if ($_POST['action']=='add_table_line_profil' && $_SESSION['dwh_droit_admin']!='') {
	$user_profile=$_POST['user_profile'];
	$line=get_line_profile_admin ($user_profile,$_POST['option']) ;		
	print $line;
}


if ($_POST['action']=='check_all_patient_features' && $_SESSION['dwh_droit_admin']!='') {
	$patient_features=$_POST['patient_features'];

	$sel_profile=oci_parse($dbh,"select distinct user_profile from dwh_profile_right  order by user_profile ");
	oci_execute($sel_profile);
	while ($r=oci_fetch_array($sel_profile,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$user_profile=$r['USER_PROFILE'];
		print "document.getElementById('id_checkbox_".$user_profile."_".$patient_features."').checked=true;modifier_droit_profil('$user_profile','$patient_features');";
	}
}

if ($_POST['action']=='ajouter_user_admin' && $_SESSION['dwh_droit_admin']!='') {
	$login=trim($_POST['login']);
	$lastname=nettoyer_pour_inserer(trim(urldecode($_POST['lastname'])));
	$firstname=nettoyer_pour_inserer(trim(urldecode($_POST['firstname'])));
	$mail=trim(urldecode($_POST['mail']));
	$expiration_date=trim(urldecode($_POST['expiration_date']));
	$liste_profils=trim(urldecode($_POST['liste_profils']));
	$liste_services=trim(urldecode($_POST['liste_services']));
	$passwd=trim(urldecode($_POST['passwd']));

	if ($login!='') {
		$user_num=ajouter_user ($login,$lastname,$firstname,$mail,$expiration_date,$liste_profils,$liste_services,'ok') ;
		ajouter_query_demo($user_num);

		if ($passwd!='') {
			$req="update dwh_user set  passwd='".md5($passwd)."'  where user_num=$user_num";
			$sel=oci_parse($dbh,$req);
			oci_execute($sel) || die ("");
		}

		print $user_num;
	}
}

if ($_POST['action']=='ajouter_liste_user_admin' && $_SESSION['dwh_droit_admin']!='') {
	$list_user=$_POST['list_user'];
	$liste_profils=trim(urldecode($_POST['liste_profils']));
	$liste_services=trim(urldecode($_POST['department_num']));
	$tableau_users=explode("\n",$list_user);
	
	foreach ($tableau_users as $user) {
		if ($user!='') {
			$tableau_user=preg_split("/[,;\t]/",$user);
		
			$login=trim($tableau_user[0]);
			$lastname=nettoyer_pour_inserer(trim(urldecode($tableau_user[1])));
			$firstname=nettoyer_pour_inserer(trim(urldecode($tableau_user[2])));
			$mail=trim(urldecode($tableau_user[3]));
			$expiration_date=trim(urldecode($tableau_user[4]));
			if ($login!='') {
				
				$sel_var1=oci_parse($dbh,"select user_num from dwh_user where lower(login)=lower('$login')   ");
				oci_execute($sel_var1);
				$r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC);
				$user_num=$r['USER_NUM'];
				if ($user_num!='') {
					print "<strong style=\"color:red\">".get_translation('USER','Utilisateur')." $login ".get_translation('ALREADY_REGISTERED','déjà enregistré')."</strong>";
				
				} else {
					if ($lastname=='') {
						$ident=ldap_user_name($login);
						$ident=preg_split("/,/",$ident);
						$lastname=$ident[0];
						$firstname=$ident[1];
						$mail=$ident[2];
					}
				
					$sel_var1=oci_parse($dbh,"select dwh_seq.nextval user_num from dual  ");
					oci_execute($sel_var1);
					$r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC);
					$user_num=$r['USER_NUM'];
					
					$req="insert into dwh_user  (user_num , lastname ,firstname ,mail ,login,passwd,creation_date,expiration_date) values ($user_num,'$lastname','$firstname','$mail','$login','',sysdate,to_date('$expiration_date','DD/MM/YYYY'))";
					$sel_var1=oci_parse($dbh,$req);
					oci_execute($sel_var1) || die ("<strong style=\"color:red\">erreur : $login patient non sauvé</strong><br>");
					
					$tableau_profils=explode(',',$liste_profils);
					foreach ($tableau_profils as $user_profile) {
						if ($user_profile!='') {
							$req="insert into dwh_user_profile  (user_num ,user_profile) values ($user_num,'$user_profile')";
							$sel_var1=oci_parse($dbh,$req);
							oci_execute($sel_var1) ||die ("<strong style=\"color:red\">erreur : $login profils non sauvés</strong><br>");
						}
					}
					
					$tableau_services=explode(',',$liste_services);
					foreach ($tableau_services as $department_num) {
						if ($department_num!='') {
							$req="insert into dwh_user_department  (user_num ,department_num) values ($user_num,'$department_num')";
							$sel_var1=oci_parse($dbh,$req);
							oci_execute($sel_var1) ||die ("<strong style=\"color:red\">erreur : $login services non sauvés</strong><br>");
						}
					}
					print "<strong style=\"color:green\">".get_translation('USER','Utilisateur')." $login ".get_translation('REGISTERED','enregistré')."</strong>";
				}
			}
		}
	}
}



if ($_POST['action']=='supprimer_user_admin' && $_SESSION['dwh_droit_admin']!='') {
	$user_num=trim($_POST['user_num']);
	$del=oci_parse($dbh,"delete from dwh_user where user_num=$user_num");
	oci_execute($del);
	$del=oci_parse($dbh,"delete from dwh_user_department where user_num=$user_num");
	oci_execute($del);
	$del=oci_parse($dbh,"delete from dwh_user_profile where user_num=$user_num");
	oci_execute($del);
	$del=oci_parse($dbh,"delete from dwh_query where user_num=$user_num");
	oci_execute($del);
	$del=oci_parse($dbh,"delete from dwh_tmp_result_$user_num_session where user_num=$user_num");
	oci_execute($del);
}

if ($_POST['action']=='modifier_user_admin' && $_SESSION['dwh_droit_admin']!='') {
	$user_num=trim($_POST['user_num']);
	$login=trim($_POST['login']);
	$lastname=nettoyer_pour_inserer(trim(urldecode($_POST['lastname'])));
	$firstname=nettoyer_pour_inserer(trim(urldecode($_POST['firstname'])));
	$mail=trim(urldecode($_POST['mail']));
	$expiration_date=trim(urldecode($_POST['expiration_date']));
	$passwd=trim(urldecode($_POST['passwd']));
	$liste_profils=trim(urldecode($_POST['liste_profils']));
	$liste_services=trim(urldecode($_POST['liste_services']));
	if ($login!='' && $user_num!='') {
		$req="update dwh_user set  lastname='$lastname',firstname='$firstname',mail='$mail',login='$login',expiration_date=to_date('$expiration_date','DD/MM/YYYY') where user_num=$user_num";
		$sel_var1=oci_parse($dbh,$req);
		oci_execute($sel_var1) || die ("<strong style=\"color:red\">erreur : $login $lastname $firstname patient non modifié</strong>");
		
		if ($passwd!='') {
			update_user_attempt('',$user_num,'reinit');
			$req="update dwh_user set  passwd='".md5($passwd)."'  where user_num=$user_num";
			$sel_var1=oci_parse($dbh,$req);
			oci_execute($sel_var1) || die ("<strong style=\"color:red\">erreur : $login $lastname $firstname patient non modifié</strong>");
		}
		
		$req="delete from dwh_user_profile   where user_num=$user_num";
		$sel_var1=oci_parse($dbh,$req);
		oci_execute($sel_var1) ||die ("<strong style=\"color:red\">erreur : profils non sauvés</strong>");
				
		$tableau_profils=explode(',',$liste_profils);
		foreach ($tableau_profils as $user_profile) {
			if ($user_profile!='') {
				$req="insert into dwh_user_profile  (user_num ,user_profilE) values ($user_num,'$user_profile')";
				$sel_var1=oci_parse($dbh,$req);
				oci_execute($sel_var1) ||die ("<strong style=\"color:red\">erreur : $user_profile profils non sauvés</strong>");
			}
		}
		
		$req="delete from dwh_user_department   where user_num=$user_num";
		$sel_var1=oci_parse($dbh,$req);
		oci_execute($sel_var1) ||die ("<strong style=\"color:red\">erreur : profils non sauvés</strong>");
		
		$tableau_services=explode(',',$liste_services);
		foreach ($tableau_services as $department_num) {
			if ($department_num!='') {
				$req="insert into dwh_user_department  (user_num ,department_num) values ($user_num,'$department_num')";
				$sel_var1=oci_parse($dbh,$req);
				oci_execute($sel_var1) ||die ("<strong style=\"color:red\">erreur : services non sauvés</strong>");
			}
		}
		print "<strong style=\"color:green\">".get_translation('USER_SUCESSFULY_REGISTERED','utilisateur enregistré avec succès')."</strong>";
	}
}

if ($_POST['action']=='rafraichir_tableau_users' && $_SESSION['dwh_droit_admin']!='') {
		print "<table  class=\"tableau_bord_fin\" id=\"id_tableau_users\">
				<thead>
				<tr>
					<th class=\"question_user\">Login</th>
					<th class=\"question_user\">Firstname Lastname</th>
					<th class=\"question_user\">Mail</th>
					<th class=\"question_user\">Profils</th>
					<th class=\"question_user\">Departments</th>
					<th class=\"question_user\">Creation date</th>
					<th class=\"question_user\">Expiration date</th>
					<th class=\"question_user\">Modify</th>
					<th class=\"question_user\">Delete</th>
				</tr>
				</thead>
				
				 <tbody>";
		$sel=oci_parse($dbh,"select user_num,lastname ,firstname ,mail ,login,to_char(creation_date,'DD/MM/YYYY') as creation_date,to_char(expiration_date,'DD/MM/YYYY') as expiration_date from dwh_user order by lastname,firstname ");
		oci_execute($sel);
		while ($r_p=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$user_num=$r_p['USER_NUM'];
			$lastname=$r_p['LASTNAME'];
			$firstname=$r_p['FIRSTNAME'];
			$mail=$r_p['MAIL'];
			$login=$r_p['LOGIN'];
			$creation_date=$r_p['CREATION_DATE'];
			$expiration_date=$r_p['EXPIRATION_DATE'];
			
			print "<tr id=\"id_tr_user_$user_num\" onmouseover=\"this.style.backgroundColor='#dcdff5';\" onmouseout=\"this.style.backgroundColor='#ffffff';\">
				<td>$login</td>
				<td>$lastname $firstname</td>
				<td>$mail</td>
				<td>";
			$sel_var1=oci_parse($dbh,"select user_profile from dwh_user_profile  where user_num=$user_num");
			oci_execute($sel_var1);
			while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
				$user_profile=$r['USER_PROFILE'];
				print "$user_profile<br>";
			}
				print "</td>
				<td>";
			$sel_var1=oci_parse($dbh,"select department_str,dwh_user_department.department_num,manager_department from dwh_user_department, dwh_thesaurus_department  where dwh_user_department.department_num= dwh_thesaurus_department.department_num and user_num=$user_num");
			oci_execute($sel_var1);
			while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
				$department_str=$r['DEPARTMENT_STR'];
				$department_num=$r['DEPARTMENT_NUM'];
				$manager_department=$r['MANAGER_DEPARTMENT'];
				if ($manager_department==1) {
					$checked='checked';
				} else {
					$checked='';
				}
				print "$department_str <input type=checkbox value=1 id=\"id_manager_department_service_".$department_num."_".$user_num."\" onclick=\"affecter_manager_department_service($department_num,$user_num);\" $checked><br>";
			}
			print "</td>";
			print "<td>$creation_date</td>";
			print "<td>$expiration_date</td>";
			print "<td><span style=\"cursor:pointer\" class=\"action\" onclick=\"deplier('id_admin_modifier_user','block');plier('id_admin_ajouter_liste_user');plier('id_admin_ajouter_user');afficher_modif_user($user_num);\">Modifier</span></td>";
			print "<td><span style=\"cursor:pointer\" class=\"action\" onclick=\"supprimer_user_admin($user_num);\">X</span></td>";
			print "</tr>";
		}
		print "
		 </tbody>
	</table>";
}

if ($_POST['action']=='afficher_modif_user') {
	$user_num=$_POST['user_num'];
	if ($user_num!='') {
		$i=0;
		$sel_var1=oci_parse($dbh,"select login,lastname,firstname,mail,dwh_user.user_num,to_char(expiration_date,'DD/MM/YYYY') as expiration_date from dwh_user where user_num=$user_num");
		oci_execute($sel_var1);
		$r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC);
		$login=$r['LOGIN'];
		$lastname=$r['LASTNAME'];
		$firstname=$r['FIRSTNAME'];
		$mail=$r['MAIL'];
		$user_num=$r['USER_NUM'];
		$expiration_date=$r['EXPIRATION_DATE'];
		$res="$lastname,$firstname,$mail,$login,$expiration_date";
		print "$res";
	}
}

if ($_POST['action']=='recup_profils') {
	$user_num=$_POST['user_num'];
	$sel_var1=oci_parse($dbh,"select distinct user_profile from  dwh_user_profile   ");
	oci_execute($sel_var1);
	while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$user_profile=$r['USER_PROFILE'];
		if ($user_profile!='') {
			print "document.getElementById('id_modifier_user_profile_$user_profile').checked=false;";
		}
	}
	$sel_var1=oci_parse($dbh,"select distinct user_profile from  dwh_user_profile where user_num=$user_num ");
	oci_execute($sel_var1);
	while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$user_profile=$r['USER_PROFILE'];
		if ($user_profile!='') {
			print "document.getElementById('id_modifier_user_profile_$user_profile').checked=true;";
		}
	}
}

if ($_POST['action']=='recup_services') {
	$user_num=$_POST['user_num'];
	$sel_var1=oci_parse($dbh,"select distinct department_num from  dwh_user_department  ");
	oci_execute($sel_var1);
	while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$department_num=$r['DEPARTMENT_NUM'];
		if ($department_num!='') {
			print "document.getElementById('id_modifier_select_department_num_multiple_$department_num').selected=false;";
		}
	}
	$sel_var1=oci_parse($dbh,"select distinct department_num from  dwh_user_department where user_num=$user_num ");
	oci_execute($sel_var1);
	while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$department_num=$r['DEPARTMENT_NUM'];
		if ($department_num!='') {
			print "document.getElementById('id_modifier_select_department_num_multiple_$department_num').selected=true;";
		}
	}
}


if ($_POST['action']=='affecter_manager_department_service' && $_SESSION['dwh_droit_admin']!='') {
	$user_num=trim($_POST['user_num']);
	$department_num=trim($_POST['department_num']);
	$manager_department=trim($_POST['manager_department']);
	$sel_var1=oci_parse($dbh,"update dwh_user_department set manager_department='$manager_department' where user_num=$user_num and  department_num=$department_num ");
	oci_execute($sel_var1);
}


if ($_POST['action']=='ajouter_droit_profil'  && $_SESSION['dwh_droit_admin']=='ok') {
	$user_profile=$_POST['user_profile'];
	$right=$_POST['right'];
	
	$req="insert into dwh_profile_right  (user_profile ,right) values ('$user_profile','$right')";
	$ins=oci_parse($dbh,$req);
	oci_execute($ins) ||die ("<strong style=\"color:red\">erreur profil non modifié</strong><br>");
	
}

if ($_POST['action']=='ajouter_droit_profil_document_origin_code'  && $_SESSION['dwh_droit_admin']=='ok') {
	$user_profile=$_POST['user_profile'];
	$document_origin_code=$_POST['document_origin_code'];
	
	$req="insert into dwh_profile_document_origin  (user_profile ,document_origin_code) values ('$user_profile','$document_origin_code')";
	$ins=oci_parse($dbh,$req);
	oci_execute($ins) ||die ("<strong style=\"color:red\">erreur profil non modifié</strong><br>");
}

if ($_POST['action']=='supprimer_profil'  && $_SESSION['dwh_droit_admin']=='ok') {
	$user_profile=$_POST['user_profile'];
	$req="delete from dwh_profile_right  where user_profile='$user_profile'";
	$del=oci_parse($dbh,$req);
	oci_execute($del) ||die ("<strong style=\"color:red\">erreur profil non supprimé</strong><br>");
}

if ($_POST['action']=='supprimer_droit_profil'   && $_SESSION['dwh_droit_admin']=='ok') {
	$user_profile=$_POST['user_profile'];
	$right=$_POST['right'];
	
	$req="delete from  dwh_profile_right where user_profile='$user_profile' and right='$right'";
	$del=oci_parse($dbh,$req);
	oci_execute($del) ||die ("<strong style=\"color:red\">erreur profil non supprimé</strong><br>");
}

if ($_POST['action']=='supprimer_droit_profil_document_origin_code'   && $_SESSION['dwh_droit_admin']=='ok') {
	$user_profile=$_POST['user_profile'];
	$document_origin_code=$_POST['document_origin_code'];
	
	$req="delete from  dwh_profile_document_origin where user_profile='$user_profile' and document_origin_code='$document_origin_code'";
	$del=oci_parse($dbh,$req);
	oci_execute($del) ||die ("<strong style=\"color:red\">erreur document_origin_code non supprimé</strong><br>");
}

if ($_POST['action']=='ajouter_nouveau_profil' && $_SESSION['dwh_droit_admin']!='') {
	$user_profile=trim(nettoyer_pour_inserer(urldecode($_POST['user_profile'])));
	if ($user_profile!='') {
		$sel=oci_parse($dbh,"select count(*) NB from dwh_profile_right where user_profile='$user_profile'  ");
		oci_execute($sel);
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$verif=$r['NB'];
		if ($verif==0) {
			$req="insert into dwh_profile_right  (user_profile ,right) values ('$user_profile','')";
			print "$req";
			$ins=oci_parse($dbh,$req);
			oci_execute($ins) ||die ("<strong style=\"color:red\">erreur profil non ajouté</strong><br>");
			foreach ($tableau_user_droit_default as $right) { 
				$req="insert into dwh_profile_right  (user_profile ,right) values ('$user_profile','$right')";
				$ins=oci_parse($dbh,$req);
				oci_execute($ins) ||die ("<strong style=\"color:red\">erreur profil non ajouté</strong><br>");
			}
			foreach ($tableau_patient_droit_default as $right) { 
				$req="insert into dwh_profile_right  (user_profile ,right) values ('$user_profile','$right')";
				$ins=oci_parse($dbh,$req);
				oci_execute($ins) ||die ("<strong style=\"color:red\">erreur profil non ajouté</strong><br>");
			}
		}
	}
}


if ($_POST['action']=='ajouter_uf' && $_SESSION['dwh_droit_admin']=='ok') {
	$unit_str=nettoyer_pour_inserer(urldecode($_POST['unit_str']));
	$unit_code=urldecode($_POST['unit_code']);
	$unit_start_date=trim(urldecode($_POST['unit_start_date']));
	$unit_end_date=trim(urldecode($_POST['unit_end_date']));
	$department_num=$_POST['department_num'];

        $sel_var=oci_parse($dbh,"select manager_department from dwh_user_department where department_num=$department_num and user_num=$user_num_session");
	oci_execute($sel_var);
	$r=oci_fetch_array($sel_var);
	$manager_department_groupe=$r[0];
	if ($_SESSION['dwh_droit_admin']=='ok' || $manager_department_groupe==1) {
		if ($unit_code!='' && $unit_start_date!='' && $unit_end_date!='') {
		        $sel_var=oci_parse($dbh,"select unit_num from dwh_thesaurus_unit where unit_code='$unit_code' and unit_start_date=to_date('$unit_start_date','DD/MM/YYYY') and unit_end_date=to_date('$unit_end_date','DD/MM/YYYY') ");
			oci_execute($sel_var);
			$r=oci_fetch_array($sel_var);
			$unit_num=$r[0];
			if ($unit_num=='') {
			        $sel_var=oci_parse($dbh,"select dwh_seq.nextval from dual");
				oci_execute($sel_var);
				$r=oci_fetch_array($sel_var);
				$unit_num=$r[0];
			        $sel_var=oci_parse($dbh,"insert into   dwh_thesaurus_unit (unit_num,unit_code, unit_str, department_num,unit_start_date,unit_end_date) values ($unit_num,'$unit_code','$unit_str',$department_num,to_date('$unit_start_date','DD/MM/YYYY'),to_date('$unit_end_date','DD/MM/YYYY') )");
				oci_execute($sel_var);
			
				print "<tr id=\"id_tr_uf_".$department_num."_".$unit_num."\" style=\"background-color:#B9C2C8;\" onmouseover=\"this.style.backgroundColor='#B9C2C8';\" onmouseout=\"this.style.backgroundColor='#F5F5F5';\" class=\"admin_texte\">
					<td>$unit_code ".ucfirst(strtolower($unit_str))." </td>
					<td>$unit_start_date</td>
					<td>$unit_end_date</td>";
				
				if ($_SESSION['dwh_droit_admin']=='ok' || $verif_manager_department==1) {
					print "<td><a onclick=\"supprimer_uf('$unit_num','$department_num');return false;\" href=\"#\" class=\"admin_lien\">X</a></td>";
				} else {
					print "<td></td>";
				}
				print "</tr>";
			}
		}
	} else {
		print  get_translation('YOU_CANNOT_ADD_USER_TO_GROUP',"Vous n'avez pas le droit d'ajouter un utilisateur dans ce groupe");
	}
}

if ($_POST['action']=='supprimer_uf' && $_SESSION['dwh_droit_admin']=='ok') {
	$unit_num=$_POST['unit_num'];
	$department_num=$_POST['department_num'];
	$req_uf="delete dwh_thesaurus_unit where department_num=$department_num and unit_num=$unit_num ";
	$sel_uf = oci_parse($dbh,$req_uf);
	oci_execute($sel_uf);
}


if ($_POST['action']=='ajouter_service' && $_SESSION['dwh_droit_admin']=='ok') {
	$department_str=nettoyer_pour_inserer(urldecode($_POST['department_str']));

        $sel_var=oci_parse($dbh,"select department_num from dwh_thesaurus_department where upper(department_str)=upper('$department_str')");
	oci_execute($sel_var);
	$r=oci_fetch_array($sel_var);
	$department_num=$r[0];
	if ($department_num=='') {
	        $sel_var=oci_parse($dbh,"select dwh_seq.nextval from dual");
		oci_execute($sel_var);
		$r=oci_fetch_array($sel_var);
		$department_num=$r[0];
	        $sel_var=oci_parse($dbh,"insert into dwh_thesaurus_department (department_num, department_str) values ($department_num,'$department_str')");
		oci_execute($sel_var);
	
		affiche_service($department_num,$department_str,'');
	} 

}


if ($_POST['action']=='supprimer_service'  && $_SESSION['dwh_droit_admin']=='ok') {
	$department_num=$_POST['department_num'];
	$req_user="delete dwh_user_department where department_num=$department_num";
	$sel_user = oci_parse($dbh,$req_user);
	oci_execute($sel_user);
	
	$req_user="delete dwh_thesaurus_department where department_num=$department_num";
	$sel_user = oci_parse($dbh,$req_user);
	oci_execute($sel_user);
}


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





if ($_POST['action']=='afficher_concepts' && $_SESSION['dwh_droit_admin']=='ok') {
	$liste_concept=supprimer_apost(trim(urldecode($_POST['concept'])));
	$tableau_concept=explode(';',$liste_concept);
	if ($liste_concept!='') {
		print "<table class=tablefin>
			<thead>
				<tr>
					<td>Concept code</td>
					<td>concept_str</td>
					<td>semantic_type</td>
					<td>Excluded</td>
					<td>Used</td>
					<td>Count used (str)</td>
					<td>Pref</td>
					<td>count patients (concept)</td>
					<td>count patients (concept + son)</td>
					<td>Exclude</td>
					<td>chemin</td>
				</tr>
			</thead>
			<tbody>";
			
		foreach ($tableau_concept as $concept) {
			if ($concept!='') {
				if (preg_match("/^C[0-9][0-9][0-9][0-9][0-9]+$/i",$concept)) {
					$sel=oci_parse($dbh," select thesaurus_tal_num,concept_code,concept_str,excluded,path_str from dwh_thesaurus_tal where  concept_code='$concept' order by concept_code");
				} else {
					$sel=oci_parse($dbh,"select thesaurus_tal_num,concept_code,concept_str,excluded,path_str from dwh_thesaurus_tal where   concept_code in ( select concept_code from dwh_thesaurus_tal where contains(CONCEPT_STR,'$concept')>0)  order by concept_code");
				}
				oci_execute($sel);
				while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
					$id_thesaurus_tal=$r['THESAURUS_TAL_NUM'];
					$concept_code=$r['CONCEPT_CODE'];
					$concept_str=$r['CONCEPT_STR'];
					$excluded=$r['EXCLUDED'];
					$path_str=$r['PATH_STR'];
					if ($excluded==1) {
						$style_barre="text-decoration: line-through;";
					} else {
						$style_barre="";
					}
					$code_libelle_req=preg_replace("/'/","''",$concept_str);
					
					$utilise='non';
					$sel_enrsem=oci_parse($dbh," select thesaurus_enrsem_num,pref,count_patient,count_patient_subsumption,count_doc_concept_str from dwh_thesaurus_enrsem where concept_code='$concept_code' and lower(concept_str)=lower('$code_libelle_req')");
					oci_execute($sel_enrsem);
					$var=oci_fetch_array($sel_enrsem,OCI_RETURN_NULLS+OCI_ASSOC);
					$id_thesaurus_enrsem=$var['THESAURUS_ENRSEM_NUM'];
					$pref=$var['PREF'];
					$nb_patient=$var['COUNT_PATIENT'];
					$count_patient_subsumption=$var['COUNT_PATIENT_SUBSUMPTION'];
					$count_doc_concept_str=$var['COUNT_DOC_CONCEPT_STR'];
					if ($id_thesaurus_enrsem!='') {
						$utilise='oui';
					}
					
					$selts=oci_parse($dbh,"select listagg(semantic_type,',') within group (order by semantic_type)  as liste_type_semantic  from dwh_thesaurus_typesemantic where   concept_code ='$concept_code'");
					oci_execute($selts);
					$r_ts=oci_fetch_array($selts,OCI_RETURN_NULLS+OCI_ASSOC);
					$liste_type_semantic=$r_ts['LISTE_TYPE_SEMANTIC'];
						
					if (strtolower($concept)==strtolower($concept_str) || strtolower($concept_code)==strtolower($concept)) {
						$backgroundColor='#FFC8C8';
					} else {
						$backgroundColor='#ffffff';
					}
					print "<tr style=\"background-color:$backgroundColor;$style_barre\" onmouseout=\"this.style.backgroundColor='$backgroundColor';\" onmouseover=\"this.style.backgroundColor='#dcdff5';\">
						<td>$concept_code</td>
						<td>$concept_str</td>
						<td>$liste_type_semantic</td>
						<td>$excluded</td>
						<td>$utilise</td>
						<td>$count_doc_concept_str</td>
						<td>$pref</td>
						<td>$nb_patient</td>
						<td>$count_patient_subsumption</td>
						<td><input id=\"id_concept_$id_thesaurus_tal\" class=\"concept_a_exclure\" type=\"checkbox\" value=\"$id_thesaurus_tal\"></td>
						<td>$path_str</td>
						</tr>";
				}
			}
		}
		
		print "</tbody></table>";
		
		print "<input type=button value=exclure onclick=\"exclure_concepts();\">";
	}
}



if ($_POST['action']=='exclure_concepts' && $_SESSION['dwh_droit_admin']=='ok') {
	$liste_val=supprimer_apost(trim(urldecode($_POST['liste_val'])));
	$process_num=uniqid();
	create_process ($process_num,$user_num_session,0,get_translation('PROCESS_ONGOING','process en cours'),'',"sysdate + 20","admin_concepts");
	passthru( "php $CHEMIN_GLOBAL/exec_admin_exclure_enrsem.php \"$liste_val\"  \"$process_num\" \"$user_num_session\">> $CHEMIN_GLOBAL_LOG/log_exec_admin_exclure_enrsem.txt 2>&1 &");
	print "$process_num";
}



if ($_POST['action']=='verif_process_exclure_concepts' && $_SESSION['dwh_droit_admin']=='ok') {
	$process_num=$_POST['process_num'];
	$tableau_process=get_process($process_num);
	$status=$tableau_process['STATUS'];
	$commentary=$tableau_process['COMMENTARY'];
	print "$status;$commentary";
}


if ($_POST['action']=='ajouter_concepts' && $_SESSION['dwh_droit_admin']=='ok') {
	$concept_str=supprimer_apost(trim(urldecode($_POST['concept_libelle_new'])));
	$concept_code=trim($_POST['concept_code']);
	$semantic_type=supprimer_apost(trim(urldecode($_POST['semantic_type'])));
	if ($concept_str!='' && $concept_code!='') {
	
		$sel=oci_parse($dbh,"select thesaurus_code,thesaurus_str,path_code,path_str from dwh_thesaurus_tal where concept_code='$concept_code'");
		oci_execute($sel);
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$thesaurus_code=$r['THESAURUS_CODE'];
		$thesaurus_str=$r['THESAURUS_STR'];
		$path_code=$r['PATH_CODE'];
		$path_str=supprimer_apost($r['PATH_STR']);
		
		if ($thesaurus_code=='') {
			$thesaurus_code='UMLSMAIN';
			$thesaurus_str='UMLS';
			$path_code=$concept_code;
			$path_str=$concept_str." /";
		}
		
		$sel_var1=oci_parse($dbh,"select dwh_seq.nextval as THESAURUS_TAL_NUM from dual  ");
		oci_execute($sel_var1);
		$r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC);
		$thesaurus_tal_num=$r['THESAURUS_TAL_NUM'];
		$sel_var1=oci_parse($dbh,"insert into dwh_thesaurus_tal (thesaurus_tal_num,thesaurus_code,thesaurus_str,path_code,path_str,concept_code,concept_str,add_date,new_code_str,mode_ajout) 
								values ($thesaurus_tal_num,'$thesaurus_code','$thesaurus_str','$path_code','$path_str','$concept_code','$concept_str',sysdate,1,'manuel')");
		oci_execute($sel_var1) || die ("erreur ajout");
		
		$sel_var1=oci_parse($dbh,"insert into dwh_thesaurus_typesemantic (concept_code,semantic_type) values ('$concept_code','$semantic_type')");
		oci_execute($sel_var1) || die ("erreur ajout");
		
		$cridx=oci_parse($dbh,"begin ctx_ddl.sync_index('DWH_THESAURUS_TAL_IDX', '200M'); end;");
		oci_execute($cridx);
		
		print "ajout ok";
	}
}



?>