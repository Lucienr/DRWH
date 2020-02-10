<?

function get_table_of_ecrf_functions () {
	$table_of_ecrf_functions=array(
		"sex_detection" => get_translation('SEX_PATIENT',"Sexe du patient"),
		"today_age_years" => get_translation('AGE_PATIENT',"Age du patient aujourd'hui (ans)"),
		"birth_date" => get_translation('BIRTH_DATE_PATIENT',"Date de naissance du patient (dd/mm/yyyy)"),
		"last_news" => get_translation('NB_DAYS_SINCE_LAST_VISIT',"Nb jours depuis la dernière visite")		
	);
	return $table_of_ecrf_functions;
}
function execute_ecrf_functions ($ecrf_function, $patient_num,$table_value) {
	global $dbh;
	if ($ecrf_function=='sex_detection') {
		$sel_ecrf=oci_parse($dbh,"select sex from DWH_PATIENT where patient_num=$patient_num ");
		oci_execute($sel_ecrf);
		$r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
		$sex=$r['SEX'];
		$result='';
		if (is_array($table_value)) {
			foreach ($table_value as $val) {
				if (preg_match("/^([FW]|Girl)/i",$val) && $sex=='F') {
					$result=$val;
				}
				if (preg_match("/^([MH]|boy)/i",$val) && $sex=='M') {
					$result=$val;
				}
				if (preg_match("/^([IU])/i",$val) && $sex=='') {
					$result=$val;
				}
			}
		} else {
			$result=$sex;
		}
		return $result;
	}
	if ($ecrf_function=='today_age_years') {
		$sel_ecrf=oci_parse($dbh,"select round((sysdate-birth_date)/365) as birth_date from DWH_PATIENT where patient_num=$patient_num ");
		oci_execute($sel_ecrf);
		$r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
		$birth_date=$r['BIRTH_DATE'];
		return $birth_date;
	}
	if ($ecrf_function=='birth_date') {
		global $dbh;
		$sel_ecrf=oci_parse($dbh,"select to_char(birth_date,'DD/MM/YYYY') as birth_date from DWH_PATIENT where patient_num=$patient_num ");
		oci_execute($sel_ecrf);
		$r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
		$birth_date=$r['BIRTH_DATE'];
		return $birth_date;
	}
	if ($ecrf_function=='last_news') {
		global $dbh;
		$sel_ecrf=oci_parse($dbh,"
		select round(min(nb_last_news)) as NB_LAST_NEWS from (select sysdate-document_date as  nb_last_news  from dwh_document where patient_num=$patient_num and document_date is not null
	        union
	        select sysdate-entry_date as  nb_last_news from dwh_patient_mvt where patient_num=$patient_num and entry_date is not null) t
		
		 ");
		oci_execute($sel_ecrf);
		$r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
		$nb_last_news=$r['NB_LAST_NEWS'];
		return $nb_last_news;
	}
}




?>