<?

$chemin="/var/www/xxxx/xxx";

ini_set("memory_limit","800M");
putenv("NLS_LANG=French");

include_once("$chemin/parametrage.php");
include_once("$chemin/connexion_bdd.php");
include_once("$chemin/ldap.php");
include_once("$chemin/fonctions_droit.php");
include_once("$chemin/fonctions_dwh.php");

$user_num_session='';
$_SESSION='';
$liste_service_session='';
$liste_uf_session='';
$liste_document_origin_code_session='';


$periode=$argv[1];

if ($periode=='') {
	#if we are the last day of the month
	$sel = oci_parse($dbh, "SELECT trunc(LAST_DAY(SYSDATE)) - trunc(sysdate)  days_left FROM DUAL");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	if ($r['DAYS_LEFT']==0) {
		execute_query_cron('month');
	}
	
	#if we are sunday
	$sel = oci_parse($dbh, " select TO_CHAR (sysdate, 'DY', 'NLS_DATE_LANGUAGE=ENGLISH') as day from dual");   
	oci_execute($sel);
	$r = oci_fetch_array($sel, OCI_ASSOC);
	if ($r['DAY']=='SUN') {
		execute_query_cron('week');
	}
	
	#every day
	execute_query_cron('day');
} else {
	execute_query_cron($periode);
}

function execute_query_cron ($periode) {
	global $dbh,$_SESSION,$liste_service_session,$liste_uf_session,$liste_document_origin_code_session;
	$sel = oci_parse($dbh, "select query_num , user_num from dwh_query where crontab_query=1 and crontab_periode='$periode' ");   
	oci_execute($sel);
	while ($r = oci_fetch_array($sel, OCI_ASSOC)) { 
		$query_num=$r['QUERY_NUM'];
		$user_num_session=$r['USER_NUM'];
		$_SESSION=array();
		$liste_service_session='';
		$liste_uf_session='';
		$liste_document_origin_code_session='';
		
			
		//// LES DROITS GLOBAUX DE L'UTILISATEUR //////////
		$sel_var1=oci_parse($dbh,"select user_profile from dwh_user_profile where user_num =$user_num_session");
		oci_execute($sel_var1);
		while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$user_profile=$r['USER_PROFILE'];
			$_SESSION['dwh_profil_'.$user_profile]='ok';
		
			$sel_vardroit=oci_parse($dbh,"select right from dwh_profile_right where user_profile='$user_profile'");
			oci_execute($sel_vardroit);
			while ($r_droit=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC)) {
				$right=$r_droit['RIGHT'];
				$_SESSION['dwh_droit_'.$right.'0']='ok';
				$_SESSION['dwh_droit_'.$right]='ok';
			}
			
		}
		
		//// LES SERVICES DE L'UTILISATEUR //////////
		$liste_service_session='';
		$liste_uf_session='';
		$sel_var1=oci_parse($dbh,"select department_num from dwh_user_department where user_num='$user_num_session' ");
		oci_execute($sel_var1);
		while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$department_num_session=$r['DEPARTMENT_NUM'];
			$liste_service_session.="$department_num_session,";
			$sel_var_uf=oci_parse($dbh,"select unit_code from dwh_thesaurus_unit where department_num='$department_num_session' ");
			oci_execute($sel_var_uf);
			while ($r_uf=oci_fetch_array($sel_var_uf,OCI_RETURN_NULLS+OCI_ASSOC)) {
				$unit_code=$r_uf['UNIT_CODE'];
				$liste_uf_session.="'$unit_code',";
			}
		}
		$liste_service_session=substr($liste_service_session,0,-1);
		$liste_uf_session=substr($liste_uf_session,0,-1);
		
		//// LES TYPES DOC DE L'UTILISATEUR //////////
		$liste_document_origin_code_session=list_authorized_document_origin_code_for_one_datamart(0,$user_num_session,'sql');
		
		create_table_temp_query ($user_num_session);
		
		print "generer_resultat_requete_sauve($query_num);\n";
		generer_resultat_requete_sauve($query_num);
	}
}
?>
