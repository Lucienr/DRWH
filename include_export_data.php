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

<form style="display:inline" method="post" action="export_data_excel.php" id="id_form_concept_quick_access" style="margin-bottom:pointer;">
	<input type="hidden" name="tmpresult_num" id="id_num_temp" value="<? print $tmpresult_num; ?>">

	<h2><? print get_translation('SELECT_DATA_TO_EXPORT','Sélectionner des données pour l\'export'); ?>:</h2>	

	<div>		
		<? print get_translation('SELECT_DATA','Sélectionner des données'); ?>:<br>	
		<select class="js-concept-select" id="concept-select" style="width:40%" name="concepts[]" multiple="multiple" lang="fr" >   
		</select>
	</div>
	<br>
      	<div>		
      		<? print get_translation('SELECT_DATA_LIST','Sélectionner une liste des données'); ?>:<br>	
      		<select class="js-list-concept-select" style="width:40%" name="list[]" multiple="multiple" lang="fr" ">   		
      		<?
      		$query="select title,user_num from dwh_export_data where user_num='$user_num_session' or share_list=1";	
      		$sel=oci_parse($dbh,$query);
      		oci_execute($sel);					
      			while($r=oci_fetch_array($sel, OCI_RETURN_NULLS+OCI_ASSOC)){
      				$title=$r['TITLE'];
      				$user_query_num=$r['USER_NUM'];
				$createur=get_user_information($user_query_num,'pn');
    				      					
      				print "<option value=\"$title\">$title ".get_translation('CREATED_BY','créer par')." $createur</option>";	
      			}	
      		?>
      		</select>										
      	</div>	
      	<br>
	<div id="buttons_export_data">
		<span id="export_concepts" class="bouton_sauver" onclick="plier_deplier('id_div_export_concept');"><? print "Export";?></span>	
      		<span id="save-concepts" class="bouton_sauver" onclick="plier_deplier('id_div_save_concept');"><? print get_translation('SAVE_DATA_LIST','Save Data list');?></span> 
		<span id="manage-lists" class="bouton_sauver" onclick="plier_deplier('id_div_data_list');"><? print get_translation('MANAGE_DATA_LIST','Manage Data list');?></span>
		<span id="id_reset_selected_data" class="bouton_sauver"><? print get_translation('RESET','Reset');?></span>
		
	</div>


	<div id="id_div_save_concept" style="width:550px;display:none;font-size:13px">
  		<h3><? print get_translation('SAVE_SELECTED_DATA','Enregistrer les données selectionnées');?> </h3>
  		<? print get_translation('TITLE','Titre');?>: <input type="text" size="40" id="id_title_saved_list" class="input_texte"><br>
  		<br><? print get_translation('SHARE_SAVED_LIST','Partager la liste enregistrée');?>: <input type="checkbox" id="id_crontab_share_concept" name="crontab_share_concept" value="1"> 
  		<br>
  		<br> <input type=button value='Save' onclick="save_curent_selection();"> 
  		
  		<input type="hidden" id="query_num_sauver" value="<? print $query_num; ?>">
  		<br>
  		<br>
	</div>
									      	
      	<br><br>
	
	
	<div id="id_div_export_concept" style="width:550px;display:none;font-size:13px">
  		<h3><? print get_translation('EXPORT_DATA','Export data'); ?> </h3>
  		<? print get_translation('EXPORT_FILE_NAME','Nom du fichier d\'export'); ?> : <input type="text" size="40" id="id_export_file_name" name="file_name" class="input_texte"><br>   		
  		<br> 
  		<? print get_translation('EXPORT_TYPE','Format d\'export'); ?> :
  		 <input type="radio" name="export_type" value="xls" checked>xls
 		 <input type="radio" name="export_type" value="txt">txt<br>
		  <br> 
  		<input type="submit" value='<? print get_translation('EXPORT','Export'); ?>' class="bouton_sauver"  onclick="plier('id_div_export_concept');"> 
		
  		<input type="hidden" id="query_num_sauver" value="<? print $query_num; ?>"> 		
	</div>

	<div id="id_div_data_list" style="width:550px;display:none">
		<h2><? print get_translation('MY_SAVED_LISTS','Mes listes sauvegardées'); ?>:</h2>
		<div id="id_div_liste_tableau">
			<?php
			get_my_export_lists($user_num_session);
			?>
		</div>
	</div>			
</form>
	
<form style="display:inline" method="post" action="export_data_excel.php" id="id_form_export_data_thesaurus" style="margin-bottom:pointer;">

	<h2><? print get_translation('EXPORT_DATA_COMPLETE_THESAURUS','Export data d\'un thesaursus complet'); ?>:</h2>				
        <div>	
      		<? print get_translation('SELECT_THESAURUS','Sélectionner un thesaurus'); ?> :<br>
      		<select class="js-thesaurus-select" style="width:40%" name="thesaurus[]" multiple="multiple" lang="fr">   		
      		<?php
      		global $dbh;
      		$query="select distinct thesaurus_code from dwh_thesaurus_data";	
      		$sel=oci_parse($dbh,$query);
      		oci_execute($sel);					
      			while($r=oci_fetch_array($sel, OCI_RETURN_NULLS+OCI_ASSOC)){
      				$thesaurus_code=$r['THESAURUS_CODE'];	
      				print"<option value=\"$thesaurus_code\">$thesaurus_code</option>";	
      			}	
      		?>
      		</select>										
      	</div>	
	<br><br>		
	<div id="buttons_export_thesaurus" >
		<span id="export_data_thesaurus" class="bouton_sauver" onclick="plier_deplier('id_div_export_thesaurus'); "><? print get_translation('EXPORT','Export'); ?></span>			
		<span id="id_reset_selected_thesaurus" class="bouton_sauver"><? print get_translation('RESET','Reset'); ?></span> 
	
		<input type="hidden" name="tmpresult_num" id="id_num_temp" value="<? print $tmpresult_num; ?>">								
	</div>	
		
	<div id="id_div_export_thesaurus" style="width:550px;display:none;font-size:13px">

		<h3><? print get_translation('EXPORT_DATA','Export data'); ?> </h3>
  		<? print get_translation('EXPORT_FILE_NAME','Nom du fichier d\'export'); ?> : <input type="text" size="40" id="id_export_file_name" name="file_name" class="input_texte"><br>   		
  		<br> 
  		<? print get_translation('EXPORT_TYPE','Format d\'export'); ?> :
  		 <input type="radio" name="export_type" value="xls" checked>xls
 		 <input type="radio" name="export_type" value="txt">txt<br>
		  <br> 
  		<input type="submit" value='<? print get_translation('EXPORT','Export'); ?>' class="bouton_sauver"  onclick="plier('id_div_export_concept');"> 		
  		<input type="hidden" id="query_num_sauver" value="<? print $query_num; ?>"> 		
	</div>		
</form>


<script type="application/javascript">
	jQuery(document).ready(function(){
	    jQuery('.autosizejs').autosize();   
	});

tmpresult_num=$('#id_num_temp').val(); 

/* select2 concepts*/

$(".js-concept-select").select2({
	placeholder:"search concept",
	language: "fr",
    	minimumInputLength: 3,
    	allowClear: true,
	ajax: {
	      url: "ajax_export.php",
	      dataType: 'json',
	      type: "POST",
	      delay: 250,
	      data: function (params) {
	      	return {
	      		q: params.term, 
	      		action : 'get_concept_data',
	      		tmpresult_num
	        	};
	      },
	      processResults: function (data) {
	          return {
	              results: data.items
	          };
	      },
	}

});

/* select2 thesaurus*/

$(".js-thesaurus-select").select2({
	placeholder:"search thesaurus",
	allowClear: true,
	language: "fr"
	});

/* select2 list*/

$(".js-list-concept-select").select2({
	placeholder:"search list",
	allowClear: true,
	language: "fr"
	});
	
$("#id_reset_selected_thesaurus").on("click", function (e) { 								
		$(".js-thesaurus-select").val('').trigger('change');		
		plier('id_div_save_concept');
		plier('id_div_export_concept');
		plier('id_div_data_list');
});

	
$("#id_reset_selected_data").on("click", function (e) { 
	
		selected_concepts='';
		list_c='';		
		$(".js-concept-select").empty().trigger('change');			
		//$(".js-thesaurus-select").val('').trigger('change');
		$(".js-list-concept-select").val('').trigger('change');

		plier('id_div_save_concept');
		plier('id_div_export_concept');
		plier('id_div_data_list');
});

$(".js-list-concept-select").on("change", function (e) {			
	var list_c = $(".js-list-concept-select").val();
	console.log(list_c);	
	
	
		jQuery.ajax({
			type:"POST",
			url:"ajax_export.php",
			async:false,
			dataType: 'json',
			data: {action:'extract_concepts_from_list',list_c,tmpresult_num},
			beforeSend: function(requester){
			},
			
			success: function(requester){
				var concepts=requester;
				//console.log(requester);
				for (i in concepts)
				{
					libelle_concept=concepts[i].text;
					id_concept=concepts[i].id;
					var newConcept = new Option(libelle_concept,id_concept, true, true);
				        // Append it to the select
					test_concept=check_if_concepts_exist(concepts[i].id);
					if (test_concept != true){
				     		$(".js-concept-select").append(newConcept).trigger('change');
					}

				}
			},
			complete: function(requester){
			},
			error: function(){
					}
		});	
	
});
	

			


function save_curent_selection() {
	title_concept_query=document.getElementById('id_title_saved_list').value;
	var selected_concepts= $(".js-concept-select").val(); 
	var share_concepts='';
		
	if (document.getElementById('id_crontab_share_concept').checked==true) {
		share_concepts=1;
	} else {
		share_concepts=0;
	}
		
	jQuery.ajax({
			type:"POST",
			url:"ajax_export.php",
			async:false,
			data: { action:'save_curent_selection',title_concept_query,selected_concepts,share_concepts},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='error'){
					alert(requester);
				}else{
					document.getElementById('id_div_liste_tableau').innerHTML=requester;
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	/* cacher le bloc apres la sauvegarde */
	//$("#").css("display","none");		
	plier('id_div_save_concept')
}

function check_if_concepts_exist(concept_code){
	var selected_concepts = $(".js-concept-select").val();
	//console.log('item deja'+selected_concepts);
	if(selected_concepts!= null) {
	var check= selected_concepts.includes(concept_code); 
	//console.log('check '+ check);
	}else {
	 check= false; 
	} 
	return check;

}



function supprimer_list(export_data_num) {
	if (confirm ("<? print get_translation('JS_REMOVE_LIST_CONFIRMATION','Êtes-vous sûr de vouloir supprimer cette liste?'); ?>")) {
		jQuery.ajax({
			type:"POST",
			url:"ajax_export.php",
			async:true,
			data: { action:'delete_list',export_data_num:export_data_num},
			beforeSend: function(requester){
			},
			success: function(requester){
				document.getElementById('id_div_liste_tableau').innerHTML=requester;
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
}

function change_share(export_data_num,share_list) {
	
		jQuery.ajax({
			type:"POST",
			url:"ajax_export.php",
			async:true,
			data: { action:'share_list',export_data_num:export_data_num,share_list:share_list},
			beforeSend: function(requester){
			},
			success: function(requester){
				document.getElementById('id_div_liste_tableau').innerHTML=requester;
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	
}

</script>