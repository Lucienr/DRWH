<?

function get_table_of_ecrf_functions () {
	$table_of_ecrf_functions=array(
		"sex_detection" => get_translation('SEX_PATIENT',"Sexe du patient")." (".get_translation('SEX_CHOICE_M_F',"H/F").")",
		"today_age_years" => get_translation('AGE_PATIENT',"Age du patient aujourd'hui (ans)"),
		"birth_date" => get_translation('BIRTH_DATE_PATIENT',"Date de naissance du patient (dd/mm/yyyy)"),
		"death_date" => get_translation('DEATH_DATE_PATIENT',"Date de décès du patient (dd/mm/yyyy)"),
		"death" => get_translation('DEATH_PATIENT',"Patient décédé (yes,no)"),
		"last_news" => get_translation('NB_DAYS_SINCE_LAST_VISIT',"Nb jours depuis le dernier document / mouvement"),
		"date_last_news" => get_translation('DATE_SINCE_LAST_VISIT',"Date depuis le dernier document / mouvement"),
		"date_last_document" => get_translation('DATE_LAST_DOCUMENT',"Date du dernier document"),
		"date_last_movement" => get_translation('DATE_LAST_MOVEMENT',"Date du dernier mouvement"),
		"zip_code" => get_translation('ZIP_CODE',"Code postal"),
		"phone_number" => get_translation('PHONE_NUMBER',"Numero de téléphone"),
		"birth_country" => get_translation('BIRTH_COUNTRY',"Pays de naissance"),
		"residence_country" => get_translation('RESIDENCE_COUNTRY',"Pays de résidence"),
		"birth_zip_code" => get_translation('BIRTH_ZIP_CODE',"Code postal de naissance")
		
	);
	return $table_of_ecrf_functions;
}

function get_automatic_javascript_ecrf_functions () {
	$table=get_table_of_ecrf_functions ();
	foreach ($table as $function => $libelle) {
		$javascript_libelle.="
		table_lib['$function']=\"$libelle\";
		";
	}
	$javascript=" function get_automatic_javascript_ecrf_functions (ecrf_num,ecrf_item_num) {
		ecrf_function=jQuery('#id_select_ecrf_function_'+ecrf_item_num).val();
		table_lib=new Array;
		if (ecrf_function=='sex_detection') {
			jQuery('#id_input_item_type_'+ecrf_item_num).val('radio');
			sexe_f='';
			sexe_m='';
			jQuery('#id_div_modif_sub_item_local_str_'+ecrf_item_num+' input').each(function() {
			   if (jQuery(this).val()=='F') {
			   	sexe_f='ok';
			   }
			   if (jQuery(this).val()=='M') {
			   	sexe_m='ok';
			   }
			});
			if (sexe_f=='') {
				ecrf_sub_item_num=add_sub_item(ecrf_num,ecrf_item_num,'F');
			}
			if (sexe_m=='') {
				ecrf_sub_item_num=add_sub_item(ecrf_num,ecrf_item_num,'M');
			}
			
		}
		if (ecrf_function=='today_age_years') {
			jQuery('#id_input_item_type_'+ecrf_item_num).val('numeric');
		}
		if (ecrf_function=='birth_date') {
			jQuery('#id_input_item_type_'+ecrf_item_num).val('date');
		}
		if (ecrf_function=='death_date') {
			jQuery('#id_input_item_type_'+ecrf_item_num).val('date');
		}
		if (ecrf_function=='death') {
			jQuery('#id_input_item_type_'+ecrf_item_num).val('radio');
			death_y='';
			death_n='';
			jQuery('#id_div_modif_sub_item_local_str_'+ecrf_item_num+' input').each(function() {
			   if (jQuery(this).val()=='yes') {
			   	death_y='ok';
			   }
			   if (jQuery(this).val()=='no') {
			   	death_n='ok';
			   }
			});
			if (death_y=='') {
				ecrf_sub_item_num=add_sub_item(ecrf_num,ecrf_item_num,'yes');
			}
			if (death_n=='') {
				ecrf_sub_item_num=add_sub_item(ecrf_num,ecrf_item_num,'no');
			}
		}
		if (ecrf_function=='last_news') {
			jQuery('#id_input_item_type_'+ecrf_item_num).val('numeric');
		}
		if (ecrf_function=='date_last_news') {
			jQuery('#id_input_item_type_'+ecrf_item_num).val('date');
		}
		if (ecrf_function=='date_last_document') {
			jQuery('#id_input_item_type_'+ecrf_item_num).val('date');
		}
		if (ecrf_function=='date_last_movement') {
			jQuery('#id_input_item_type_'+ecrf_item_num).val('date');
		}
		if (ecrf_function=='zip_code') {
			jQuery('#id_input_item_type_'+ecrf_item_num).val('text');
		}
		if (ecrf_function=='phone_number') {
			jQuery('#id_input_item_type_'+ecrf_item_num).val('text');
		}
		if (ecrf_function=='birth_country') {
			jQuery('#id_input_item_type_'+ecrf_item_num).val('text');
		}
		if (ecrf_function=='residence_country') {
			jQuery('#id_input_item_type_'+ecrf_item_num).val('text');
		}
		if (ecrf_function=='birth_zip_code') {
			jQuery('#id_input_item_type_'+ecrf_item_num).val('text');
		}
		
		$javascript_libelle
		jQuery('#id_input_item_str_'+ecrf_item_num).val(table_lib[ecrf_function]);
		adapt_ecrf_form(ecrf_item_num);
		
		if (ecrf_function!='') {
			
			jQuery('.tr_ecrf_item_document_search').css('visibility','collapse');
			jQuery('.tr_ecrf_item_document_search').css('opacity','0');
			jQuery('.tr_ecrf_item_type').css('visibility','collapse');
			jQuery('.tr_ecrf_item_type').css('opacity','0');
			jQuery('.tr_ecrf_item_pattern').css('visibility','collapse');
			jQuery('.tr_ecrf_item_pattern').css('opacity','0');
			jQuery('.tr_ecrf_item_pattern_index').css('visibility','collapse');
			jQuery('.tr_ecrf_item_pattern_index').css('opacity','0');
			jQuery('.tr_ecrf_item_document_source').css('visibility','collapse');
			jQuery('.tr_ecrf_item_document_source').css('opacity','0');
			jQuery('.tr_ecrf_item_local_code').css('visibility','collapse');
			jQuery('.tr_ecrf_item_local_code').css('opacity','0');
			jQuery('.tr_ecrf_item_period').css('visibility','collapse');
			jQuery('.tr_ecrf_item_period').css('opacity','0');
			jQuery('.tr_ecrf_item_existing_pattern').css('visibility','collapse');
			jQuery('.tr_ecrf_item_existing_pattern').css('opacity','0');
			
			jQuery('.td_ecrf_sub_item_pattern').css('visibility','collapse');
			jQuery('.td_ecrf_sub_item_pattern').css('opacity','0');
			jQuery('.td_ecrf_sub_item_local_codes').css('visibility','collapse');
			jQuery('.td_ecrf_sub_item_local_codes').css('opacity','0');
			
			jQuery('.span_ecrf_link_add_subitem').css('visibility','collapse');
			jQuery('.span_ecrf_link_add_subitem').css('opacity','0');
		}
	}
	";
	
	return $javascript;
}

function execute_ecrf_functions ($ecrf_function, $patient_num,$table_value) {
	global $dbh;
	if ($ecrf_function=='sex_detection') {
		$sel_ecrf=oci_parse($dbh,"select sex from DWH_PATIENT where patient_num=$patient_num ");
		oci_execute($sel_ecrf);
		$r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
		$sex=$r['SEX'];
		$result='';
		if (is_array($table_value) && count($table_value)>0) {
			foreach ($table_value as $val) {
				if (preg_match("/^([FW]|Girl)/i",$val) && $sex=='F') {
					$result=$val;
				}
				if (preg_match("/^([MH]|boy|Garçon|Garcon)/i",$val) && $sex=='M') {
					$result=$val;
				}
				if (preg_match("/^([IU])/i",$val) && ($sex=='' || $sex=='I')) {
					$result=$val;
				}
			}
		} else {
			$result=$sex;
		}
		return $result;
	}
	if ($ecrf_function=='today_age_years') {
		$sel_ecrf=oci_parse($dbh,"select decode(death_date,null,round((sysdate-birth_date)/365),null) as birth_date from DWH_PATIENT where patient_num=$patient_num ");
		oci_execute($sel_ecrf);
		$r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
		$birth_date=$r['BIRTH_DATE'];
		return $birth_date;
	}
	if ($ecrf_function=='birth_date') {
		$sel_ecrf=oci_parse($dbh,"select to_char(birth_date,'DD/MM/YYYY') as birth_date from DWH_PATIENT where patient_num=$patient_num ");
		oci_execute($sel_ecrf);
		$r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
		$birth_date=$r['BIRTH_DATE'];
		return $birth_date;
	}
	if ($ecrf_function=='death_date') {
		$sel_ecrf=oci_parse($dbh,"select to_char(death_date,'DD/MM/YYYY') as death_date from DWH_PATIENT where patient_num=$patient_num ");
		oci_execute($sel_ecrf);
		$r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
		$death_date=$r['DEATH_DATE'];
		return $death_date;
	}
	if ($ecrf_function=='death') {
		$sel_ecrf=oci_parse($dbh,"select to_char(death_date,'DD/MM/YYYY') as death_date from DWH_PATIENT where patient_num=$patient_num  ");
		oci_execute($sel_ecrf);
		$r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
		$death_date=$r['DEATH_DATE'];
		if ($death_date!='') {
			$death='yes';
		} else {
			$death='no';
		}
		if (is_array($table_value) && count($table_value)>0) {
			foreach ($table_value as $val) {
				if (preg_match("/^(oui|yes|mort|décédé|dead)/i",$val) && $death=='yes') {
					$result=$val;
				}
				if (preg_match("/^(no|non|vivant|alive)/i",$val) && $death=='no') {
					$result=$val;
				}
				
			}
		} else {
			$result=$sex;
		}
		return $result;
	}
	if ($ecrf_function=='last_news') {
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
	if ($ecrf_function=='date_last_news') {
		$sel_ecrf=oci_parse($dbh,"
		select to_char(max(date_last_news),'DD/MM/YYYY') as date_last_news from (select document_date as  date_last_news  from dwh_document where patient_num=$patient_num and document_date is not null
	        union
	        select entry_date as  date_last_news from dwh_patient_mvt where patient_num=$patient_num and entry_date is not null) t
		 ");
		oci_execute($sel_ecrf);
		$r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
		$nb_last_news=$r['NB_LAST_NEWS'];
		return $nb_last_news;
	}
	if ($ecrf_function=='date_last_document') {
		$sel_ecrf=oci_parse($dbh,"select to_char(max(document_date),'DD/MM/YYYY') as last_document_date  from dwh_document where patient_num=$patient_num and document_date is not null ");
		oci_execute($sel_ecrf);
		$r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
		$last_document_date=$r['LAST_DOCUMENT_DATE'];
		return $last_document_date;
	}
	if ($ecrf_function=='date_last_movement') {
		$sel_ecrf=oci_parse($dbh,"select to_char(max(entry_date),'DD/MM/YYYY') as last_mvt_date  from dwh_patient_mvt where patient_num=$patient_num and entry_date is not null ");
		oci_execute($sel_ecrf);
		$r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
		$last_mvt_date=$r['LAST_MVT_DATE'];
		return $last_mvt_date;
	}
	if ($ecrf_function=='zip_code') {
		$sel_ecrf=oci_parse($dbh,"select zip_code  from dwh_patient where patient_num=$patient_num  ");
		oci_execute($sel_ecrf);
		$r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
		$zip_code=$r['ZIP_CODE'];
		return $zip_code;
	}
	if ($ecrf_function=='phone_number') {
		$sel_ecrf=oci_parse($dbh,"select PHONE_NUMBER  from dwh_patient where patient_num=$patient_num  ");
		oci_execute($sel_ecrf);
		$r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
		$phone_number=$r['PHONE_NUMBER'];
		return $phone_number;
	}
	if ($ecrf_function=='birth_country') {
		$sel_ecrf=oci_parse($dbh,"select BIRTH_COUNTRY  from dwh_patient where patient_num=$patient_num  ");
		oci_execute($sel_ecrf);
		$r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
		$birth_country=$r['BIRTH_COUNTRY'];
		return $birth_country;
	}
	if ($ecrf_function=='residence_country') {
		$sel_ecrf=oci_parse($dbh,"select RESIDENCE_COUNTRY  from dwh_patient where patient_num=$patient_num  ");
		oci_execute($sel_ecrf);
		$r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
		$residence_country=$r['RESIDENCE_COUNTRY'];
		return $residence_country;
	}
	if ($ecrf_function=='birth_zip_code') {
		$sel_ecrf=oci_parse($dbh,"select BIRTH_ZIP_CODE  from dwh_patient where patient_num=$patient_num  ");
		oci_execute($sel_ecrf);
		$r=oci_fetch_array($sel_ecrf,OCI_RETURN_NULLS+OCI_ASSOC);
		$birth_zip_code=$r['BIRTH_ZIP_CODE'];
		return $birth_zip_code;
	}
}




?>