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

putenv("NLS_LANG=French");

include_once "parametrage.php";
include_once "connexion_bdd.php";
include_once("fonctions_dwh.php");

if ($argv[1]!='') {

	$liste_val=$argv[1];
	$process_num=$argv[2];
	$user_num=$argv[3];
	
	
	$tableau_val=explode(';',$liste_val);
	foreach ($tableau_val as $thesaurus_tal_num) {
		if ($thesaurus_tal_num!='') {
			print "thesaurus_tal_num : $thesaurus_tal_num\n";
			$sel=oci_parse($dbh,"select concept_code,concept_str from dwh_thesaurus_tal where thesaurus_tal_num=$thesaurus_tal_num ");
			oci_execute($sel);
			$r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
			$concept_code=$r['CONCEPT_CODE'];
			$concept_str=$r['CONCEPT_STR'];
			$concept_str=supprimer_apost($concept_str);
			
			$reqdel="delete from dwh_etl_thesaurus_enrsem  where concept_code='$concept_code'  and  lower( concept_str)=lower('$concept_str')";
			$del=oci_parse($dbh,$reqdel);
			oci_execute($del) || die("erreur $reqdel\n");
			
			$reqdel="delete from dwh_etl_thesaurus_gene  where concept_code='$concept_code'  and  lower( concept_str)=lower('$concept_str')";
			$del=oci_parse($dbh,$reqdel);
			oci_execute($del) || die("erreur $reqdel\n");
			
			if ($concept_code!='') {
				$nb_doc=0;
				$req="update  dwh_thesaurus_tal set excluded=1,exclusion_date=sysdate where concept_code='$concept_code' and lower( concept_str)=lower('$concept_str')";
				$sel=oci_parse($dbh,$req);
				oci_execute($sel) || die("erreur $req\n");
				update_process ($process_num,0,get_translation('PROCESS_DELETE_CONCEPTS_UPD_ENRTEXTE','Suppression des concepts et mise à jour des textes enrichis'),'');
				$sel=oci_parse($dbh,"select document_num,context,certainty from dwh_enrsem where concept_code='$concept_code'  and  lower( concept_str_found)=lower('$concept_str')");
				oci_execute($sel) || die("erreur select document_num,context,certainty from dwh_enrsem where concept_code='$concept_code'  and  lower( concept_str_found)=lower('$concept_str') \n");
				while ($r=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
					$document_num=$r['DOCUMENT_NUM'];
					$context=$r['CONTEXT'];
					$certainty=$r['CERTAINTY'];
					$nb_doc++;
					if ($nb_doc % 100==0) {
						update_process ($process_num,0,"$nb_doc ".get_translation('PROCESS_N_TEXT_UPDATED','textes mis à jour'),'');
					}
					$req_doc="delete from dwh_enrsem  where document_num=$document_num and context='$context' and certainty=$certainty and concept_code='$concept_code' and   lower( concept_str_found)=lower('$concept_str')";
					$selreq_doc=oci_parse($dbh,$req_doc);
					oci_execute($selreq_doc) || die("erreur $req_doc\n");
					
					//$req_doc="update from dwh_document set enrichtext_done_flag=null  where document_num=$document_num  ";
					//$selreq_doc=oci_parse($dbh,$req_doc);
					//oci_execute($selreq_doc) || die("erreur $req_doc\n");
					update_column_enrich_text($document_num,$context,$certainty);
				}			
				
				update_process ($process_num,0,get_translation('PROCESS_UPDATE_THESAURUS','mise à jour du thesaurus'),'');
				print "update_preferred_term ($concept_code);<br>";
				update_preferred_term ($concept_code);
			}
		}
	}
	update_process ($process_num,0,get_translation('PROCESS_UPDATE_INDEX','mise à jour des index'),'');
	update_process ($process_num,1,'end','');
	$cridx=oci_parse($dbh,"begin ctx_ddl.sync_index('DWH_TEXT_ENRICHI_IDX', '200M'); end;");
	oci_execute($cridx);
}