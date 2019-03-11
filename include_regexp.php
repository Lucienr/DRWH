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
function rechercher_regexp() {
	regexp=document.getElementById('id_rechercher_regexp').value;
	regexp=regexp.replace(/\+/g,';plus;');
	tmpresult_num=document.getElementById('id_num_temp').value;
	datamart_num='<? print $datamart_num; ?>';
	document.getElementById('id_div_resultat_recherche_regexp').innerHTML="<img src=images/chargement_mac.gif>";
	if (regexp!='') {
		jQuery.ajax({
			type:"POST",
			url:"ajax_regexp.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'rechercher_regexp',regexp:escape(regexp),tmpresult_num:tmpresult_num,datamart_num:datamart_num},
			beforeSend: function(requester){},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion("rechercher_regexp ()");
				} else { 
					verif_process_execute_regexp (requester);
				}
			},
			complete: function(requester){},
			error: function(){}
		});
	} else {
		document.getElementById('id_div_resultat_recherche_regexp').innerHTML="";
	}

}

function verif_process_execute_regexp (process_num) {
	jQuery.ajax({
		type:"POST",
		url:"ajax_regexp.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'verif_process_execute_regexp',process_num:process_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			tab=contenu.split(';');
			status=tab[0];
			message=tab[1];
			if (status=='1') { // end
				get_regexp_result (process_num);
			} else {
				if (status=='erreur') {
					jQuery("#id_div_resultat_recherche_regexp").html(message); 
				} else {
					jQuery("#id_div_resultat_recherche_regexp").html(message+" <img src='images/chargement_mac.gif'>"); 
					setTimeout("verif_process_execute_regexp('"+process_num+"')",1000);
				}
			}
		}
	});
}

function get_regexp_result (process_num) {
	jQuery.ajax({
		type:"POST",
		url:"ajax_regexp.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'get_regexp_result',process_num:process_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			jQuery("#id_div_resultat_recherche_regexp").html(contenu); 
		}
	});
}
</script>
<div>
<strong><? print get_translation('SEARCH_A_PATTERN','Rechercher un pattern'); ?> <? print get_translation('ON_DOCUMENT_FOUND','sur les documents trouvés'); ?>:</strong><br>
 <input id="id_rechercher_regexp" class="filtre_regexp" size="80" type="text" onkeypress="if(event.keyCode==13) {rechercher_regexp();}"> <input type="button" onclick="rechercher_regexp();" value="Go" class="form_submit">
</div>
<div>
Exemples : <br>
Extraction de la taille : <span class="link" onclick="document.getElementById('id_rechercher_regexp').value=this.innerHTML;">taille[^a-z0-9A-Z]*([0-9]+[.,]?[0-9]*)</span><br>
Extraction de la taille : <span class="link" onclick="document.getElementById('id_rechercher_regexp').value=this.innerHTML;">taille[^a-z0-9A-Z]*(de|est|est a|a)?[^a-z0-9A-Z]*([0-9]+[.,m ]*[0-9]*)</span><br>
Extraction du poids : <span class="link"  onclick="document.getElementById('id_rechercher_regexp').value=this.innerHTML;">[^a-z0-9A-Z]*([0-9]+[.,]?[0-9]*)[^a-z0-9A-Z]*kg</span><br>
</div>
 <br>
<div id="id_div_resultat_recherche_regexp"></div>
