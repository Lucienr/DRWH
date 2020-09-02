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
<? if ($_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_droit_see_detailed'.$datamart_num]=='ok') { ?>
	<style>
		.sous_onglet_cohorte {
			border:1px black solid;
			padding: 3px;
			cursor:pointer;
		}
		.sous_onglet_cohorte_inclu {
			background-color:#D0EAD1;
			font-weight:bold;
		}
		.sous_onglet_cohorte_exclu {
			background-color:#E9CDCE;
		}
		.sous_onglet_cohorte_doute {
			background-color:#E1E1DF;
		}
		.sous_onglet_cohorte_attente {
			background-color:#D7D7D7;
		}
		.tableau_cohorte {
			font-size:13px;
		}
	</style>
	<script language="javascript">
		function plier_deplier_cohorte (id) {
			$(".div_cohorte").css("display","none");
			$(".sous_onglet_cohorte").css("fontWeight","normal");
			
			$('#'+id).css("display","block");
			$("#span_"+id).css("fontWeight","bold");
		}
	</script>
	
	<span id="span_id_liste_patient_cohorte_encours1" class="sous_onglet_cohorte sous_onglet_cohorte_inclu" onclick="plier_deplier_cohorte('id_liste_patient_cohorte_encours1');"><span id="id_nb_patient_cohorte_inclu_bis">0</span> <? print get_translation('INCLUDED_PATIENTS','Patients inclus'); ?></span> 
	<span id="span_id_liste_patient_cohorte_encours0" class="sous_onglet_cohorte sous_onglet_cohorte_exclu" onclick="plier_deplier_cohorte('id_liste_patient_cohorte_encours0');"><span id="id_nb_patient_cohorte_exclu">0</span> <? print get_translation('PATIENTS_EXCLUDED','Patients exclus'); ?></span>
	<span id="span_id_liste_patient_cohorte_encours2" class="sous_onglet_cohorte sous_onglet_cohorte_doute" onclick="plier_deplier_cohorte('id_liste_patient_cohorte_encours2')"><span id="id_nb_patient_cohorte_doute">0</span> <? print get_translation('PATIENTS_WITH_DOUBT','Patients en doute'); ?></span>
	<span id="span_id_liste_patient_cohorte_encours3" class="sous_onglet_cohorte sous_onglet_cohorte_attente" onclick="plier_deplier_cohorte('id_liste_patient_cohorte_encours3')"><span id="id_nb_patient_cohorte_import">0</span> <? print get_translation('PATIENTS_AWAITING','Patients en attente'); ?></span>
	<br>
	<br>
	<div id="id_liste_patient_cohorte_encours1" class="div_cohorte" style="display:block;">
	</div>
	<input type="hidden" id="id_input_nb_patient_displayed_1" value="0">
	<div id="id_liste_patient_cohorte_encours1_loading" style="display:block;"></div>
	
	<div id="id_liste_patient_cohorte_encours0" class="div_cohorte" style="display:none;">
	</div>
	<input type="hidden" id="id_input_nb_patient_displayed_0" value="0">
	<div id="id_liste_patient_cohorte_encours0_loading" style="display:none;"></div>
	
	<div id="id_liste_patient_cohorte_encours2" class="div_cohorte" style="display:none;">
	</div>
	<input type="hidden" id="id_input_nb_patient_displayed_2" value="0">
	<div id="id_liste_patient_cohorte_encours2_loading" style="display:none;"></div>
	
	<div id="id_liste_patient_cohorte_encours3" class="div_cohorte" style="display:none;">
	</div>
	<input type="hidden" id="id_input_nb_patient_displayed_3" value="0">
	<div id="id_liste_patient_cohorte_encours3_loading" style="display:none;"></div>
<? } ?>