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
    
?>

<script language=javascript>
function extract_data_ecrf_on_result (tmpresult_num,datamart_num) {
	ecrf_num=jQuery("#id_select_ecrf").val(); 
	if (jQuery("#id_ecrf_option_une_ligne_par_patient").prop("checked")) {
		option_une_ligne='patient';
	} else {
		option_une_ligne='document';
	}
	
	if (jQuery("#id_ecrf_option_perimetre_document_trouve").prop("checked")) {
		option_perimetre='document';
	} else {
		option_perimetre='patient';
	}
	jQuery("#id_div_result_extract_data_ecrf_on_result").html(" <img src='images/chargement_mac.gif'>"); 
	if (tmpresult_num!='') {
		jQuery.ajax({
			type:"POST",
			url:"ajax_ecrf_on_result.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'extract_data_ecrf_on_result',tmpresult_num:tmpresult_num,ecrf_num:ecrf_num,datamart_num:datamart_num,option_une_ligne:option_une_ligne,option_perimetre:option_perimetre},
			beforeSend: function(requester){},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion("extract_data_ecrf_on_result ('"+tmpresult_num+"','"+datamart_num+"')");
				} else { 
					 $('#id_button_extract_ecrf').addClass("form_submit_activated"); 
					 $('#id_button_extract_ecrf').removeClass("form_submit"); 
					verif_process_execute_extract_data_ecrf_on_result (requester);
				}
			},
			complete: function(requester){},
			error: function(){}
		});
	}

}

function verif_process_execute_extract_data_ecrf_on_result (process_num) {
	jQuery.ajax({
		type:"POST",
		url:"ajax_ecrf_on_result.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'verif_process_execute_extract_data_ecrf_on_result',process_num:process_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			tab=contenu.split(';');
			status=tab[0];
			message=tab[1];
			if (status=='1') { // end
				get_data_ecrf_on_result (process_num);
				 $('#id_button_extract_ecrf').addClass("form_submit"); 
				 $('#id_button_extract_ecrf').removeClass("form_submit_activated"); 
			} else {
				if (status=='erreur') {
					jQuery("#id_div_result_extract_data_ecrf_on_result").html(message); 
				} else {
					jQuery("#id_div_result_extract_data_ecrf_on_result").html(message+" <img src='images/chargement_mac.gif'>"); 
					setTimeout("verif_process_execute_extract_data_ecrf_on_result('"+process_num+"')",1000);
				}
			}
		}
	});
}

function get_data_ecrf_on_result (process_num) {
	jQuery.ajax({
		type:"POST",
		url:"ajax_ecrf_on_result.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'get_data_ecrf_on_result',process_num:process_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			jQuery("#id_div_result_extract_data_ecrf_on_result").html(contenu); 
		}
	});
}

function afficher_document_ecrf(document_num) {
	
}

</script>


<div>
	<strong><? print get_translation('EXTRACT_DATA_ECRF','Extraction de données pour un Ecrf'); ?> <? print get_translation('ON_PATIENT_FOUND','sur les patients trouvés'); ?>:</strong><br>
<?	
	$tableau_list_ecrf=get_list_ecrf ($user_num_session);
	print get_translation('SELECT_ECRF','Choisissez un Ecrf')." : ";
	print "<select id=\"id_select_ecrf\">";
        print "<option value=\"\"></option>";
	foreach ($tableau_list_ecrf as $tab_ecrf) {
                $ecrf_num=$tab_ecrf['ecrf_num'];
                $title_ecrf=$tab_ecrf['title_ecrf'];
                $description_ecrf=$tab_ecrf['description_ecrf'];
	        $nb_patients=$tab_ecrf['nb_patients'];
	        $user_num=$tab_ecrf['user_num'];
         	print "<option value=\"$ecrf_num\">$title_ecrf</option>";
	}
	print "</select><br>";
?>

	Résultat, avec une ligne par :<br>
	<input type=radio value='patient' name='ecrf_option_une_ligne' id='id_ecrf_option_une_ligne_par_patient' checked> Patient<br>
	<input type=radio value='document' name='ecrf_option_une_ligne' id='id_ecrf_option_une_ligne_par_document'> Document<br>
	<br>
	Résultat sur les documents trouvés ou tous les documents des patiens trouvés :<br>
	<input type=radio value='document_tmpresult'  name='ecrf_option_perimetre' id='id_ecrf_option_perimetre_document_trouve' checked> Documents trouvés<br>
	<input type=radio value='patient_tmpresult'  name='ecrf_option_perimetre' id='id_ecrf_option_perimetre_patient_trouve'> Tous les documents<br>
	
	<input class="form_submit" id="id_button_extract_ecrf" type="button" value="<? print get_translation('EXTRACT','EXTRAIRE'); ?>" onclick="extract_data_ecrf_on_result('<? print $tmpresult_num; ?>','<? print $datamart_num; ?>');">
</div>
 <br>
 
<div id="id_div_list_div_affichage_ecrf"></div>
<div id="id_div_result_extract_data_ecrf_on_result"></div>
