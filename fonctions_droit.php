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
function verif_autorisation_voir_patient_cohorte($cohort_num,$user_num) {
	global $dbh,$datamart_num;
	
        if ($cohort_num!='' && $user_num!='') {
		$sel_vardroit=oci_parse($dbh,"select count(*) as verif  from dwh_cohort where cohort_num=$cohort_num and (user_num=$user_num or cohort_num in (select cohort_num from dwh_cohort_user_right where user_num=$user_num and right='see_detailed'))");
	        oci_execute($sel_vardroit);
		$r_droit=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
	        $verif=$r_droit['VERIF'];
	        if ($verif==1) {
			$autorisation='ok';
		} else {
			$autorisation='';
		}
	}
	return ($autorisation);
}

function autorisation_cohorte_modifier ($cohort_num,$user_num) {
	global $dbh;
	$verif='';
        if ($cohort_num!='' && $user_num!='') {
		$sel_vardroit=oci_parse($dbh,"select user_num from dwh_cohort where cohort_num=$cohort_num");
	        oci_execute($sel_vardroit);
	        $r=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
	        $user_num_creation=$r['USER_NUM'];
		if ($user_num_creation==$user_num) {
			$verif='ok';
		}
		$sel_vardroit=oci_parse($dbh,"select user_num from dwh_cohort_user_right where user_num=$user_num and right='modifier_cohorte' and cohort_num=$cohort_num");
	        oci_execute($sel_vardroit);
	        $r=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
	        $user_num_cohort=$r['USER_NUM'];
		if ($user_num_cohort==$user_num) {
			$verif='ok';
		}
	}
	return ($verif);

}

function autorisation_cohorte_voir ($cohort_num,$user_num) {
	global $dbh;
	$verif='';
        if ($cohort_num!='' && $user_num!='') {
		$sel_vardroit=oci_parse($dbh,"select user_num from dwh_cohort where cohort_num=$cohort_num");
	        oci_execute($sel_vardroit);
	        $r=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
	        $user_num_creation=$r['USER_NUM'];
		if ($user_num_creation==$user_num) {
			$verif='ok';
		}
		$sel_vardroit=oci_parse($dbh,"select user_num from dwh_cohort_user_right where user_num=$user_num and cohort_num=$cohort_num");
	        oci_execute($sel_vardroit);
	        $r=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
	        $user_num_cohort=$r['USER_NUM'];
		if ($user_num_cohort==$user_num) {
			$verif='ok';
		}
	}
	return ($verif);
}
function autorisation_cohorte_supprimer ($cohort_num,$user_num) {
	global $dbh;
	$verif='';
        if ($cohort_num!='' && $user_num!='') {
		$sel_vardroit=oci_parse($dbh,"select user_num from dwh_cohort where cohort_num=$cohort_num");
	        oci_execute($sel_vardroit);
	        $r=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
	        $user_num_creation=$r['USER_NUM'];
		if ($user_num_creation==$user_num) {
			$verif='ok';
		}
	}
	return ($verif);
}
function autorisation_cohorte_ajouter_patient ($cohort_num,$user_num) {
	global $dbh;
	$verif='';
        if ($cohort_num!='' && $user_num!='') {
		$sel_vardroit=oci_parse($dbh,"select user_num from dwh_cohort where cohort_num=$cohort_num");
	        oci_execute($sel_vardroit);
	        $r=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
	        $user_num_creation=$r['USER_NUM'];
		if ($user_num_creation==$user_num) {
			$verif='ok';
		}
		$sel_vardroit=oci_parse($dbh,"select user_num from dwh_cohort_user_right where user_num=$user_num and right='add_patient' and cohort_num=$cohort_num");
	        oci_execute($sel_vardroit);
	        $r=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
	        $user_num_cohort=$r['USER_NUM'];
		if ($user_num_cohort==$user_num) {
			$verif='ok';
		}
	}
	return ($verif);
}

function autorisation_cohorte_voir_patient_nominative_global ($cohort_num,$user_num) {
	global $dbh;
	$verif='';
        if ($cohort_num!='') {
		$sel_vardroit=oci_parse($dbh,"select datamart_num from dwh_cohort where cohort_num=$cohort_num");
	        oci_execute($sel_vardroit);
	        $r=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
	        $num_datamart_cohorte=$r['DATAMART_NUM'];
		if ($num_datamart_cohorte==0) {
			
			$sel_vardroit=oci_parse($dbh,"select user_num from dwh_cohort where cohort_num=$cohort_num");
		        oci_execute($sel_vardroit);
		        $r=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
		        $user_num_creation=$r['USER_NUM'];
			if ($user_num_creation==$user_num) {
				$verif='ok';
			}
		
			$sel_vardroit=oci_parse($dbh,"select user_num from dwh_cohort_user_right where user_num=$user_num and right='nominative' and cohort_num=$cohort_num");
		        oci_execute($sel_vardroit);
		        $r=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
		        $user_num_cohort=$r['USER_NUM'];
			if ($user_num_cohort==$user_num) {
				$verif='ok';
			}
		} else {
			
			$sel_vardroit=oci_parse($dbh,"select USER_NUM from dwh_datamart_user_right where user_num=$user_num and right='nominative' and datamart_num=$num_datamart_cohorte");
		        oci_execute($sel_vardroit);
		        $r=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
	                $user_num_datamart=$r['USER_NUM'];
			if ($user_num_datamart==$user_num) {
				$verif='ok';
			} else {
				$sel_vardroit=oci_parse($dbh,"select user_num from dwh_cohort_user_right where user_num=$user_num and right='nominative' and cohort_num=$cohort_num");
			        oci_execute($sel_vardroit);
			        $r=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
			        $user_num_cohort=$r['USER_NUM'];
				if ($user_num_cohort==$user_num) {
					$verif='ok';
				}
			}
		}
	}
	return ($verif);
}

function autorisation_cohorte_voir_patient_nominative_precis ($cohort_num,$user_num,$patient_num) {
	global $dbh;
	$autorisation_voir_patient_nominative=autorisation_voir_patient_nominative ($patient_num,$user_num);
        if ($autorisation_voir_patient_nominative=='') {
        	if ($cohort_num!='') {
			$sel_vardroit=oci_parse($dbh,"select user_num from dwh_cohort where cohort_num=$cohort_num");
		        oci_execute($sel_vardroit);
		        $r=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
		        $user_num_creation=$r['USER_NUM'];
		        if ($user_num_creation!=$user_num) {
				$autorisation_voir_patient_nominative=autorisation_voir_patient_nominative ($patient_num,$user_num_creation);
			}
		}
        }
	return ($autorisation_voir_patient_nominative);
}


function autorisation_requete_voir ($query_num,$user_num) {
	global $dbh;
	$verif='';
	if ($user_num!='' && $query_num!='') {
		$sel_vardroit=oci_parse($dbh,"select user_num from dwh_query where query_num=$query_num");
	        oci_execute($sel_vardroit);
	        $r=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
	        $num_user_requete=$r['USER_NUM'];
		if ($user_num==$num_user_requete) {
			$verif='ok';
		}
	}
	return ($verif);
}

function autorisation_demande_proprietaire ($request_access_num,$user_num) {
	global $dbh;
	$verif='';
	if ($user_num!='' && $request_access_num!='') {
		$sel_vardroit=oci_parse($dbh,"select user_num_request from dwh_request_access where request_access_num=$request_access_num");
	        oci_execute($sel_vardroit);
	        $r=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
	        $user_num_request=$r['USER_NUM_REQUEST'];
		if ($user_num==$user_num_request) {
			$verif='ok';
		}
	}
	return ($verif);
}


function autorisation_demande_voir ($request_access_num,$user_num) {
	global $dbh;
	$verif='';
	if ($user_num!='' && $request_access_num!='') {
		$sel_vardroit=oci_parse($dbh,"select user_num_request,nuser_num_department_manager from dwh_request_access where request_access_num=$request_access_num");
	        oci_execute($sel_vardroit);
	        $r=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
	        $user_num_request=$r['USER_NUM_REQUEST'];
	        $nuser_num_department_manager=$r['NUSER_NUM_DEPARTMENT_MANAGER'];
		if ($user_num==$user_num_request || $user_num==$nuser_num_department_manager) {
			$verif='ok';
		}
	}
	return ($verif);
}

function autorisation_demande_voir_patient ($request_access_num,$user_num) {
	global $dbh;
	$verif='';
	if ($user_num!='' && $request_access_num!='') {
		$sel_vardroit=oci_parse($dbh,"select user_num_request,nuser_num_department_manager,manager_agreement from dwh_request_access where request_access_num=$request_access_num");
	        oci_execute($sel_vardroit);
	        $r=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
	        $user_num_request=$r['USER_NUM_REQUEST'];
	        $nuser_num_department_manager=$r['NUSER_NUM_DEPARTMENT_MANAGER'];
	        $manager_agreement=$r['MANAGER_AGREEMENT'];
		if ($user_num==$nuser_num_department_manager) {
			$verif='ok';
		}
		if ($user_num==$user_num_request && $manager_agreement==1) {
			$verif='ok';
		}
	}
	return ($verif);
}


function autorisation_demande_manager_department ($request_access_num,$user_num) {
	global $dbh;
	$verif='';
	if ($user_num!='' && $request_access_num!='') {
		$sel_vardroit=oci_parse($dbh,"select user_num_request,nuser_num_department_manager from dwh_request_access where request_access_num=$request_access_num");
	        oci_execute($sel_vardroit);
	        $r=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
	        $user_num_request=$r['USER_NUM_REQUEST'];
	        $nuser_num_department_manager=$r['NUSER_NUM_DEPARTMENT_MANAGER'];
		if ($user_num==$nuser_num_department_manager) {
			$verif='ok';
		}
	}
	return ($verif);
}


function autorisation_voir_patient_nominative ($patient_num,$user_num) {
	global $dbh;
	
	$verif='';
	if ($user_num!='') {
		$sel_var1=oci_parse($dbh,"select right from dwh_user_profile,dwh_profile_right  where dwh_user_profile.user_profile=dwh_profile_right.user_profile and user_num=$user_num");
		oci_execute($sel_var1);
		while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$right=$r['RIGHT'];
			$tableau_droit[$right]='ok';
		}
		
		if ($tableau_droit['all_departments']=='ok' && $tableau_droit['nominative']=='ok') {
			$verif='ok';
		} else {
			// s'il n'y a pas les droits de voir les noms, on bloc
			if ( $tableau_droit['anonymized']=='ok') {
				 $nb_verif=0;
			} else {
				// si on a les droits de voir le lastname, on vérifie si le patient est bien dans le service du user
				$sel_vardroit=oci_parse($dbh,"select count(*) as verif  from dwh_patient_department , dwh_user_department where user_num=$user_num and dwh_user_department.department_num= dwh_patient_department.department_num and patient_num=$patient_num");
			        oci_execute($sel_vardroit);
				$r_droit=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
			        $nb_verif=$r_droit['VERIF'];
			}
		        
			if ($nb_verif>0) {
				$verif='ok';
			} else {
				// si non on verifie s il est autorise dans une cohorte ?? à voir ...
				// si non, on verifie s il est dans une cohorte autorisée pour le user
				$sel_vardroit=oci_parse($dbh,"select count(*) as verif from dwh_cohort, dwh_cohort_result, dwh_cohort_user_right where dwh_cohort.cohort_num= dwh_cohort_user_right.cohort_num and  dwh_cohort_result.cohort_num= dwh_cohort_user_right.cohort_num and dwh_cohort_user_right.user_num=$user_num and right='nominative' and patient_num=$patient_num");
			        oci_execute($sel_vardroit);
			        $r_droit=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
		       		 $nb_verif=$r_droit['VERIF'];
				if ($nb_verif>0) {
					$verif='ok';
				} else {
					// si non, on verifie s il l'utilisateur a une autorisation speciale par le chef de service
					$sel_vardroit=oci_parse($dbh,"select count(*) as verif from dwh_request_access , dwh_request_access_patient where user_num_request=$user_num and dwh_request_access.request_access_num= dwh_request_access_patient.request_access_num and patient_num=$patient_num and manager_agreement=1");
				        oci_execute($sel_vardroit);
				        $r_droit=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
			       		 $nb_verif=$r_droit['VERIF'];
					if ($nb_verif>0) {
						$verif='ok';
					} else {
						// si non, on verifie s il est dans un datamart autorise pour le user
						$sel_vardroit=oci_parse($dbh,"select count(*) as verif from dwh_datamart, dwh_datamart_result, dwh_datamart_user_right where dwh_datamart.datamart_num=dwh_datamart_user_right.datamart_num and  dwh_datamart_result.datamart_num= dwh_datamart_user_right.datamart_num and dwh_datamart_user_right.user_num=$user_num and right='nominative' and temporary_status is null  and patient_num=$patient_num");
					        oci_execute($sel_vardroit);
					        $r_droit=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
				       		 $nb_verif=$r_droit['VERIF'];
						if ($nb_verif>0) {
							$verif='ok';
						}
					}
				}
			}
		} 
	}
	return ($verif);
}

function autorisation_voir_patient ($patient_num,$user_num) {

	global $dbh;
	
	if ($_SESSION['dwh_droit_all_departments0']=='') {
		$sel_var1=oci_parse($dbh,"select right from dwh_user_profile a, dwh_profile_right b where user_num=$user_num and a.user_profile=b.user_profile");
		oci_execute($sel_var1);
		while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$right=$r['RIGHT'];
			$_SESSION['dwh_droit_'.$right.'0']='ok';
		}
	}
	
	$verif='';
	if ($user_num!='' && $patient_num!='') {
		if ($_SESSION['dwh_droit_all_departments0']!='') {
			$verif='ok';
		} else {
			// on  verifie si le patient est passe dans le service du user
			$sel_vardroit=oci_parse($dbh,"select count(*) as verif  from dwh_patient_department , dwh_user_department where user_num=$user_num and dwh_user_department.department_num= dwh_patient_department.department_num and patient_num=$patient_num");
			//$sel_vardroit=oci_parse($dbh,"select count(*) as verif  from dwh_document , dwh_user_department where user_num=$user_num and dwh_user_department.department_num= dwh_document.department_num and patient_num=$patient_num");
		        oci_execute($sel_vardroit);
			$r_droit=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
		        $nb_verif=$r_droit['VERIF'];
		        
			if ($nb_verif>0) {
				$verif='ok';
			} else {
				
				// si non, on verifie s il est dans une cohorte autorisée pour le user
				$sel_vardroit=oci_parse($dbh,"select count(*) as verif from dwh_cohort, dwh_cohort_result, dwh_cohort_user_right where dwh_cohort.cohort_num= dwh_cohort_user_right.cohort_num and  dwh_cohort_result.cohort_num= dwh_cohort_user_right.cohort_num and dwh_cohort_user_right.user_num=$user_num and right='see_detailed' and patient_num=$patient_num");
			        oci_execute($sel_vardroit);
			        $r_droit=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
		       		 $nb_verif=$r_droit['VERIF'];
				if ($nb_verif>0) {
					$verif='ok';
				} else {
					// si non, on verifie s il l'utilisateur a une autorisation speciale par le chef de service
					$sel_vardroit=oci_parse($dbh,"select count(*) as verif from dwh_request_access , dwh_request_access_patient where user_num_request=$user_num and dwh_request_access.request_access_num= dwh_request_access_patient.request_access_num and patient_num=$patient_num and manager_agreement=1");
				        oci_execute($sel_vardroit);
				        $r_droit=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
			       		 $nb_verif=$r_droit['VERIF'];
					if ($nb_verif>0) {
						$verif='ok';
					} else {
						// si non, on verifie s il est dans un datamart autorise pour le user
						$sel_vardroit=oci_parse($dbh,"select count(*) as verif from dwh_datamart, dwh_datamart_result, dwh_datamart_user_right where dwh_datamart.datamart_num= dwh_datamart_user_right.datamart_num and  dwh_datamart_result.datamart_num= dwh_datamart_user_right.datamart_num and dwh_datamart_user_right.user_num=$user_num and right='see_detailed' and temporary_status is null and patient_num=$patient_num");
					        oci_execute($sel_vardroit);
					        $r_droit=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC);
				       		 $nb_verif=$r_droit['VERIF'];
						if ($nb_verif>0) {
							$verif='ok';
						} 
					}
				}
			}
		} 
	}
	return ($verif);
}

function liste_document_origin_code_tout_compris($patient_num,$user_num) {
	global $dbh;
	
	$liste_document_origin_code_session='';
	if ($user_num!='') {
		$sel_var1=oci_parse($dbh,"select distinct document_origin_code from dwh_profile_document_origin, dwh_user_profile where user_num='$user_num' and dwh_profile_document_origin.user_profile= dwh_user_profile.user_profile");
		oci_execute($sel_var1);
		while ($r=oci_fetch_array($sel_var1,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$document_origin_code=$r['DOCUMENT_ORIGIN_CODE'];
			$liste_document_origin_code_session.="'$document_origin_code',";
		}
		
		$sel_vardroit=oci_parse($dbh,"select distinct document_origin_code from dwh_datamart_doc_origin where datamart_num in (
						select distinct dwh_datamart_result.datamart_num from dwh_datamart_user_right,dwh_datamart_result,dwh_datamart
						where dwh_datamart_user_right.datamart_num=dwh_datamart_result.datamart_num
						and  dwh_datamart_user_right.datamart_num=dwh_datamart.datamart_num
						and patient_num=$patient_num and dwh_datamart_user_right.user_num=$user_num  and temporary_status is null)");
		oci_execute($sel_vardroit);
		while ($r=oci_fetch_array($sel_vardroit,OCI_RETURN_NULLS+OCI_ASSOC)) {
			$liste_document_origin_code_session.="'".$r['DOCUMENT_ORIGIN_CODE']."',";
		}
		$liste_document_origin_code_session=substr($liste_document_origin_code_session,0,-1);
	}
	return $liste_document_origin_code_session;
}
?>