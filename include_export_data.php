

<div style="display:inline" style="margin-bottom:pointer;">
	
	<h2><? print get_translation('PREVIOUS_EXPORT',"Les exports précédents encore accessibles"); ?>:</h2>	
	<div id="id_previous_export_data">
	</div>
</div>
<div style="display:inline" style="margin-bottom:pointer;">
	<input type="hidden" name="tmpresult_num" id="id_num_temp" value="<? print $tmpresult_num; ?>">

	<h2><? print get_translation('SELECT_DATA_TO_EXPORT','Sélectionner des données pour l\'export'); ?>:</h2>	

	<div>		
		<? print get_translation('SELECT_DATA','Sélectionner des données'); ?>:<br>	
		<select class="js-concept-select" id="concept-select" style="width:40%" name="concepts[]" multiple="multiple" lang="fr" >   
		</select>
	</div>
	<br>
      	<div>		
      		<? print get_translation('EXPORT_DATA_COMPLETE_THESAURUS',"Export data d'un thesaursus complet"); ?>:<br>	
      		<select class="js-thesaurus-select" style="width:40%" name="thesaurus[]" multiple="multiple" lang="fr">   		
      		<?php
      		$thesaurus=get_list_thesaurus_data();
      		foreach ($thesaurus as $thesaurus_code) {
      				print"<option value=\"$thesaurus_code\">$thesaurus_code</option>";	
      		}	
      		?>
      		</select>
	</div>	
	<br>	
      	<div>		
      		<? print get_translation('SELECT_DATA_LIST','Sélectionner une liste des données'); ?>:<br>	
      		<select class="js-list-concept-select" style="width:40%" name="list[]" multiple="multiple" lang="fr" ">   		
      		<?
      		$query="select title,user_num,export_data_num from dwh_export_data where user_num='$user_num_session' or share_list=1";	
      		$sel=oci_parse($dbh,$query);
      		oci_execute($sel);					
      			while($r=oci_fetch_array($sel, OCI_RETURN_NULLS+OCI_ASSOC)){
      				$title=$r['TITLE'];
      				$user_num=$r['USER_NUM'];
      				$export_data_num=$r['EXPORT_DATA_NUM'];
				$createur=get_user_information($user_num,'pn');
    				      					
      				print "<option value=\"$export_data_num\">$title ".get_translation('CREATED_BY','créer par')." $createur</option>";	
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
  		<br>
  		<br>
	</div>
						      	
      	<br><br>
	<div id="id_div_export_concept" style="width:550px;display:none;font-size:13px">
  		<h3><? print get_translation('EXPORT_DATA','Export data'); ?> </h3>
  		<? print get_translation('EXPORT_FILE_NAME','Nom du fichier d\'export'); ?> : <input type="text" size="40" id="id_export_file_name" name="file_name" class="input_texte"><br>   		
  		<br> 
  		<? print get_translation('TYPE_FICHIER',"Format du fichier"); ?> :
  		 <input type="radio" name="file_type" id="id_radio_file_type_xls" value="xls" checked>xls
 		 <input type="radio" name="file_type" id="id_radio_file_type_txt" value="txt">txt<br>
		  <br> 
  		<? print get_translation('EXPORT_TYPE',"Type d'export"); ?> :
  		 <input type="radio" name="export_type" id="id_radio_export_type_row" value="row" checked>ligne (i2b2 like)
 		 <input type="radio" name="export_type" id="id_radio_export_type_col" value="col">colonnes (une ligne par date)<br>
		  <br> 
  		<? print get_translation('EXPORT',"Exporter"); ?> :
  		 <input type="radio" name="patient_or_document" id="id_radio_patordoc_patient" value="patient" checked> Sur les patients trouvés<br>
 		 <input type="radio" name="patient_or_document" id="id_radio_patordoc_document" value="document"> Uniquement sur les documents trouvés<br>
		  <br> 
  		<input type="button" value='<? print get_translation('EXPORT','Export'); ?>' class="bouton_sauver" onclick="plier('id_div_export_concept');execute_process_export_data ();"> 	
	</div>
	<div id="id_div_data_list" style="width:550px;display:none">
		<h2><? print get_translation('MY_SAVED_LISTS','Mes listes sauvegardées'); ?>:</h2>
		<div id="id_div_liste_tableau">
			<?php
			get_my_export_lists($user_num_session);
			?>
		</div>
	</div>		
	<div id="id_div_export_data_chargement"></div>	
</div>

<script type="application/javascript">
	jQuery(document).ready(function(){
	    jQuery('.autosizejs').autosize();   
	    get_all_export_data();
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
	      		tmpresult_num : tmpresult_num
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
		$(".js-thesaurus-select").val('').trigger('change');
		$(".js-list-concept-select").val('').trigger('change');

		plier('id_div_save_concept');
		plier('id_div_export_concept');
		plier('id_div_data_list');
});

$(".js-list-concept-select").on("change", function (e) {		
	$(".js-thesaurus-select").val('').trigger('change');		
	var list_c = $(".js-list-concept-select").val();	
	jQuery.ajax({
		type:"POST",
		url:"ajax_export.php",
		async:false,
		dataType: 'json',
		data: {action:'extract_concepts_data_from_list',list_c:list_c,tmpresult_num:tmpresult_num},
		beforeSend: function(requester){
		},
		
		success: function(requester){
			var concepts=requester;
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
			data: { action:'save_curent_selection',title_concept_query:title_concept_query,selected_concepts:selected_concepts,share_concepts:share_concepts},
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
	var selected_concepts = ","+$(".js-concept-select").val()+",";
	var check=false;
	i=selected_concepts.indexOf(','+concept_code+',');
	if(i>-1) {
		check= true; 
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

function get_all_export_data() {
	jQuery.ajax({
		type:"POST",
		url:"ajax_export.php",
		async:true,
		data: { action:'get_all_export_data'},
		beforeSend: function(requester){
		},
		success: function(requester){
			jQuery("#id_previous_export_data").html(requester); 
		},
		complete: function(requester){
		},
		error: function(){
		}
	});
}

function execute_process_export_data () {
	var process_num;
	var selected_thesaurus=jQuery(".js-thesaurus-select").val(); 
	var selected_concepts=jQuery(".js-concept-select").val(); 
	var tmpresult_num=jQuery('#id_num_temp').val(); 
	var file_name=jQuery('#id_export_file_name').val(); 
	if(jQuery('#id_radio_file_type_xls').is(':checked')) {
		file_type='xls';
	} else {
		file_type='txt';
	}
	if(jQuery('#id_radio_export_type_row').is(':checked')) {
		export_type='row';
	} else {
		export_type='col';
	}
	if(jQuery('#id_radio_patordoc_patient').is(':checked')) {
		patient_or_document='patient_num';
	} else {
		patient_or_document='document_num';
	}
	
	jQuery.ajax({
		type:"POST",
		url:"ajax_export.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'execute_process_export_data',selected_thesaurus:selected_thesaurus,selected_concepts:selected_concepts,tmpresult_num:tmpresult_num,file_type:file_type,export_type:export_type,file_name:escape(file_name),patient_or_document:patient_or_document},
		beforeSend: function(requester){
			jQuery("#id_div_export_data_chargement").html("<img src='images/chargement_mac.gif'>"); 
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("execute_process_export_data()");
			} else {
				process_num=contenu;
				if (contenu=='erreur') {
					jQuery("#id_div_export_data_chargement").html(contenu); 
				} else {
					setTimeout("verif_process_execute_process_export_data('"+process_num+"')",1000);
				}
			}
		}
	});
}

function verif_process_execute_process_export_data (process_num) {
	jQuery.ajax({
		type:"POST",
		url:"ajax_export.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'verif_process_execute_process_export_data',process_num:process_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			tab=contenu.split(';');
			status=tab[0];
			message=tab[1];
			if (status=='1') { // end
				jQuery("#id_div_export_data_chargement").html("<a href='export_process.php?process_num="+process_num+"' target='_blank'>Telecharger "+message+"</a>"); 
				get_all_export_data();
			} else {
				if (status=='erreur') {
					jQuery("#id_div_export_data_chargement").html(message); 
				} else {
					jQuery("#id_div_export_data_chargement").html(message+" <img src='images/chargement_mac.gif'>"); 
					setTimeout("verif_process_execute_process_export_data('"+process_num+"')",1000);
				}
			}
		}
	});
}

</script>