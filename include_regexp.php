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
    
    include_once ("fonctions_ecrf.php");
?>
<script src="javascript_regexp.js?<? print "v=$date_today_unique"; ?>" type="text/javascript"></script>

<script language=javascript>

jQuery(document).ready(function() {
	list_regexp_select ();
})

</script>
<div>
	<strong><? print get_translation('SEARCH_A_PATTERN','Rechercher un pattern'); ?> <? print get_translation('ON_DOCUMENT_FOUND','sur les documents trouvés'); ?>:</strong><br>
	 <input id="id_rechercher_regexp" class="filtre_regexp" size="80" type="text" onkeypress="if(event.keyCode==13) {rechercher_regexp();}"> 
	 <input type="button" id="id_button_rechercher_regexp" onclick="rechercher_regexp();" value="Go" class="form_submit"> 
	 <input type="button" id="id_button_open_save_regexp" onclick="open_save_regexp();" value="<? print get_translation('SAVE_REGEXP',"Sauver le pattern"); ?>" class="form_submit">
</div>
<div id="id_div_save_regexp" style="display:none;">
	<table border=0>
	<tr><td><? print get_translation('TITLE','Titre'); ?></td><td><input type="text" id="id_input_regexp_title" value="" size="40"></td></tr>
	<tr><td><? print get_translation('DESCRIPTION','Description'); ?></td><td><textarea  id="id_input_regexp_description" cols=40 rows=5></textarea></td></tr>
	<tr><td><? print get_translation('SHARED','Partager'); ?></td><td><input type="checkbox" id="id_input_regexp_shared" value="1"></td></tr>
	</table><br>
	<input type="button" onclick="save_regexp();" value="<? print get_translation('SAVE',"Sauver"); ?>" class="form_submit">
</div>
<div id="id_div_save_regexp_log" style="display:none;" class="log_result_div">
</div>
<br>
<div id="id_div_list_regexp">
	<span class="link"><? print get_translation('LIST_OF_PATTERN','Liste des patterns existants'); ?> :</span>
	<select id="id_select_list_regexp" class="form chosen-select" data-placeholder="<? print get_translation('SELECT_A_PATTERN','Choisissez un pattern'); ?>" ></select> 
	<input type="button" onclick="select_regexp();" value="Select" class="form_submit"> 
	<input type="button" id="id_button_manage_regexp" onclick="manage_regexp();" value="Manage" class="form_submit"> 
</div>
<div id="id_div_manage_regexp" style="display:none;"></div>

<div>
	<span class="link" onclick="plier_deplier('id_div_regexp_example')"><span id="plus_id_div_regexp_example">+</span> <? print get_translation('EXAMPLE','Exemples'); ?></span>
	<div  id="id_div_regexp_example" style="display:none">
		Extraction de la taille : <span class="link" onclick="document.getElementById('id_rechercher_regexp').value=this.innerHTML;">taille[^a-z0-9A-Z]*([0-9]+[.,]?[0-9]*)</span><br>
		Extraction de la taille : <span class="link" onclick="document.getElementById('id_rechercher_regexp').value=this.innerHTML;">taille[^a-z0-9A-Z]*(de|est|est a|a)?[^a-z0-9A-Z]*([0-9]+[.,m ]*[0-9]*)</span><br>
		Extraction du poids : <span class="link"  onclick="document.getElementById('id_rechercher_regexp').value=this.innerHTML;">[^a-z0-9A-Z]*([0-9]+[.,]?[0-9]*)[^a-z0-9A-Z]*kg</span><br><br>
		Vous pouvez tester vos expressions régulières ici : <a href="https://regex101.com/" target="_blank">https://regex101.com/</a><br><br>
	</div>
</div>

 <br>
<div id="id_div_list_div_affichage_regexp"></div>
<div id="id_div_resultat_recherche_regexp"></div>
