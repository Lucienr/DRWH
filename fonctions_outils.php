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

function get_mapping_patient ($lastname,$firstname,$birth_date,$option_limite) {
	global $dbh;
	
	$lastname_q=nettoyer_pour_insert(trim($lastname));
	$firstname_q=nettoyer_pour_insert(trim($firstname));
	$birth_date=verif_format_date($birth_date,'DD/MM/YYYY'); // if bad format, date = null;
	
	$method='';
	$sql_sih="and patient_num in (select patient_num from dwh_patient_ipphist where origin_patient_id='SIH') ";
	if ($lastname!='' && $firstname!='' && $birth_date!='') {
		$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
		(
		regexp_replace(upper( CONVERT(lastname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') or 
		regexp_replace(upper( CONVERT(maiden_name, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') 
		)
		and 
		regexp_replace(upper( CONVERT(firstname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$firstname_q', 'US7ASCII') ),'[^A-Z]','') and
		lastname is not null and 
		firstname is not null and
		birth_date is not null and
		birth_date=to_date('$birth_date','DD/MM/YYYY')
		$sql_sih ");
		oci_execute($sel);
		$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
		$patient_num=$r['PATIENT_NUM'];
		$method='exact_match';
		
		// is error on encodage date in excel ... 1462 days 
  		if ($patient_num=='') {
			$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
			(
			regexp_replace(upper( CONVERT(lastname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') or 
			regexp_replace(upper( CONVERT(maiden_name, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') 
			)
			and 
			regexp_replace(upper( CONVERT(firstname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$firstname_q', 'US7ASCII') ),'[^A-Z]','') and
			lastname is not null and 
			firstname is not null and
			birth_date is not null and
			abs(to_date('$birth_date','DD/MM/YYYY')-birth_date) =1462
			$sql_sih ");
			oci_execute($sel);
			$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
			$patient_num=$r['PATIENT_NUM'];
			$method='lastname and firstname exact match, birth date 1462 days : excel convert';
		}
		
		
  		if ($patient_num=='' && preg_match("/ /",$lastname_q)) {
  			$tab_lastname=explode(" ",$lastname_q);
  			$new_lastname_q=$tab_lastname[1]." ".$tab_lastname[0];
			$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
			(
			regexp_replace(upper( CONVERT(lastname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$new_lastname_q', 'US7ASCII') ),'[^A-Z]','') or 
			regexp_replace(upper( CONVERT(maiden_name, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$new_lastname_q', 'US7ASCII') ),'[^A-Z]','') 
			)
			and 
			regexp_replace(upper( CONVERT(firstname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$firstname_q', 'US7ASCII') ),'[^A-Z]','') and
			lastname is not null and 
			firstname is not null and
			birth_date is not null and
			birth_date=to_date('$birth_date','DD/MM/YYYY')
			$sql_sih ");
			oci_execute($sel);
			$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
			$patient_num=$r['PATIENT_NUM'];
			$method='exact_match - inversion nom compose';
  		
  		}
  		
  		
  		if ($patient_num=='') {
			$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
				(
				INSTR(regexp_replace(upper( CONVERT(lastname, 'US7ASCII') ),'[^A-Z]','') ,regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]',''))>0 or 
				INSTR(regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') ,regexp_replace(upper( CONVERT(lastname, 'US7ASCII') ),'[^A-Z]',''))>0 or 
				INSTR(regexp_replace(upper( CONVERT(maiden_name, 'US7ASCII') ),'[^A-Z]',''), regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]',''))>0 or 
				INSTR(regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') ,regexp_replace(upper( CONVERT(maiden_name, 'US7ASCII') ),'[^A-Z]',''))>0 
				) and 
			regexp_replace(upper( CONVERT(firstname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$firstname_q', 'US7ASCII') ),'[^A-Z]','') and
			lastname is not null and 
			firstname is not null and
			birth_date is not null and
			birth_date=to_date('$birth_date','DD/MM/YYYY')
			$sql_sih ");
			oci_execute($sel);
			$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
			$patient_num=$r['PATIENT_NUM'];
			$method='inclusion nom-exact prenom et ddn';
  		}	
		
  		if ($patient_num=='') {
			$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
				(
					regexp_replace(upper( CONVERT(lastname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') or 
					regexp_replace(upper( CONVERT(maiden_name, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') 
				)
				and
				(
			instr(regexp_replace(upper( CONVERT(firstname, 'US7ASCII') ),'[^A-Z]','') ,regexp_replace(upper( CONVERT('$firstname_q', 'US7ASCII') ),'[^A-Z]',''))>0 or
			instr(regexp_replace(upper( CONVERT('$firstname_q', 'US7ASCII') ),'[^A-Z]','') ,regexp_replace(upper( CONVERT(firstname, 'US7ASCII') ),'[^A-Z]',''))>0
			)
			and
			lastname is not null and 
			firstname is not null and
			birth_date is not null and
			birth_date=to_date('$birth_date','DD/MM/YYYY')
			$sql_sih ");
			oci_execute($sel);
			$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
			$patient_num=$r['PATIENT_NUM'];
			$method='inclusion prenom-exact nom et ddn';
  		}
  		if ($patient_num=='') {
  			$month_year=preg_replace("/^[0-9]+\/([0-9]+)\/([0-9]+)$/","$1/$2",$birth_date);
			$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
				(
				regexp_replace(upper( CONVERT(lastname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') or 
				regexp_replace(upper( CONVERT(maiden_name, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') 
				)
				and 
				regexp_replace(upper( CONVERT(firstname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$firstname_q', 'US7ASCII') ),'[^A-Z]','') and
				lastname is not null and 
				firstname is not null and
				birth_date is not null and
				to_char(birth_date,'MM/YYYY')='$month_year'
				$sql_sih ");
			oci_execute($sel);
			$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
			$patient_num=$r['PATIENT_NUM'];
			$method='Erreur jour de naissance';
		}
  		if ($patient_num=='') {
  			$day_year=preg_replace("/^([0-9]+)\/[0-9]+\/([0-9]+)$/","$1/$2",$birth_date);
			$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
				(
				regexp_replace(upper( CONVERT(lastname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') or 
				regexp_replace(upper( CONVERT(maiden_name, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') 
				)
				and 
				regexp_replace(upper( CONVERT(firstname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$firstname_q', 'US7ASCII') ),'[^A-Z]','') and
				lastname is not null and 
				firstname is not null and
				birth_date is not null and
				to_char(birth_date,'DD/YYYY')='$day_year'
				$sql_sih ");
			oci_execute($sel);
			$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
			$patient_num=$r['PATIENT_NUM'];
			$method='Erreur mois de naissance';
		}
  		
  		
  		if ($patient_num=='') {
			$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
			(soundex(lastname) =soundex('$lastname_q') or soundex(maiden_name) =soundex('$lastname_q')) and 
			regexp_replace(upper( CONVERT(firstname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$firstname_q', 'US7ASCII') ),'[^A-Z]','') and
			lastname is not null and 
			firstname is not null and
			birth_date is not null and
			birth_date=to_date('$birth_date','DD/MM/YYYY')
			$sql_sih ");
			oci_execute($sel);
			$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
			$patient_num=$r['PATIENT_NUM'];
			$method='phonetique lastname-exact prenom et ddn';
  		}
  		
  		if ($patient_num=='') {
			$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
			(
			regexp_replace(upper( CONVERT(lastname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') or 
			regexp_replace(upper( CONVERT(maiden_name, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') 
			)
			and
			soundex(firstname) =soundex('$firstname_q') and 
			lastname is not null and 
			firstname is not null and
			birth_date is not null and
			birth_date=to_date('$birth_date','DD/MM/YYYY')
			$sql_sih ");
			oci_execute($sel);
			$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
			$patient_num=$r['PATIENT_NUM'];
			$method='phonetique prenom-exact nom et ddn';
  		}
  		
  		if ($patient_num=='') {
			$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
			(soundex(lastname) =soundex('$lastname_q') or soundex(maiden_name) =soundex('$lastname_q'))  
			and
			soundex(firstname) =soundex('$firstname_q') and 
			lastname is not null and 
			firstname is not null and
			birth_date is not null and
			birth_date=to_date('$birth_date','DD/MM/YYYY')
			$sql_sih ");
			oci_execute($sel);
			$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
			$patient_num=$r['PATIENT_NUM'];
			$method='phonetique lastname et prenom-exact ddn';
  		}
  		
  		if ($patient_num=='') {
			$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
			(
				regexp_replace(upper( CONVERT(lastname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') or 
				regexp_replace(upper( CONVERT(maiden_name, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') 
			)
			and
			utl_match.jaro_winkler(regexp_replace(upper( CONVERT(firstname, 'US7ASCII') ),'[^A-Z]',''), regexp_replace(upper( CONVERT('$firstname_q', 'US7ASCII') ),'[^A-Z]',''))>'0,8'  
			and 
			lastname is not null and 
			firstname is not null and
			birth_date is not null and
			birth_date=to_date('$birth_date','DD/MM/YYYY')
			$sql_sih ");
			oci_execute($sel);
			$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
			$patient_num=$r['PATIENT_NUM'];
			$method='orthographe proche prenom-exact nom et ddn';
  		}
  		
  		if ($patient_num=='') {
			$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
			(
				utl_match.jaro_winkler(regexp_replace(upper( CONVERT(lastname, 'US7ASCII') ),'[^A-Z]','') ,regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]',''))>'0,8' or 
				utl_match.jaro_winkler(regexp_replace(upper( CONVERT(maiden_name, 'US7ASCII') ),'[^A-Z]','') ,regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]',''))>'0,8' and maiden_name is not null
			)
			and
			regexp_replace(upper( CONVERT(firstname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$firstname_q', 'US7ASCII') ),'[^A-Z]','')  
			and 
			lastname is not null and 
			firstname is not null and
			birth_date is not null and
			birth_date=to_date('$birth_date','DD/MM/YYYY')
			$sql_sih ");
			oci_execute($sel);
			$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
			$patient_num=$r['PATIENT_NUM'];
			$method='orthographe proche nom-exact prenom et ddn';
  		}
  		if ($patient_num=='') {
			$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
			(
			regexp_replace(upper( CONVERT(lastname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') or 
			regexp_replace(upper( CONVERT(maiden_name, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') 
			)
			and 
			regexp_replace(upper( CONVERT(firstname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$firstname_q', 'US7ASCII') ),'[^A-Z]','') and
			lastname is not null and 
			firstname is not null 
			$sql_sih ");
			oci_execute($sel);
			$nrows = oci_fetch_all($sel, $r);
			if ($nrows==1) {
				$patient_num=$r['PATIENT_NUM'][0];
				$method='lastname and firstname exact match, NO match for birth date';
			} else if ($nrows>1 && $option_limite>1) {
				$patient_num=implode(",",$r['PATIENT_NUM']);
				$method="lastname and firstname exact match, NO match for birth date , $nrows patients trouves";
			}
		}
	
	} else if ($lastname!='' && $firstname!='') {
  		if ($patient_num=='') {
			$sel=oci_parse($dbh,"select patient_num from dwh_patient where 
			(
			regexp_replace(upper( CONVERT(lastname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') or 
			regexp_replace(upper( CONVERT(maiden_name, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$lastname_q', 'US7ASCII') ),'[^A-Z]','') 
			)
			and 
			regexp_replace(upper( CONVERT(firstname, 'US7ASCII') ),'[^A-Z]','') =regexp_replace(upper( CONVERT('$firstname_q', 'US7ASCII') ),'[^A-Z]','') and
			lastname is not null and 
			firstname is not null 
			$sql_sih ");
			oci_execute($sel);
			$nrows = oci_fetch_all($sel, $r);
			if ($nrows==1) {
				$patient_num=$r['PATIENT_NUM'][0];
				$method='lastname and firstname exact match, NO match for birth date';
			} else if ($nrows>1 && $option_limite>1) {
				$patient_num=implode(",",$r['PATIENT_NUM']);
				$method="lastname and firstname exact match, NO match for birth date , $nrows patients trouves";
			}
		}
	
  	} else if (preg_match("/^[0-9]+$/",$lastname)) {
		$hospital_patient_id=$lastname;
		$patient_num=get_patient_num($hospital_patient_id,'SIH');
		$method='IPP';
		if ($patient_num=='') {
			$patient_num=get_patient_num('0'.$hospital_patient_id,'SIH');
			$method='0+IPP';
		}
	}
  	return array($patient_num, $method);
}