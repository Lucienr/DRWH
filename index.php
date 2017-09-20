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

include "head.php";
include "menu.php";
session_write_close();
?>


<!-- Mon profil -->
<div class="div_accueil">
	<h1><img src="images/health7.png" style="vertical-align: middle"> <? print get_translation('MY_PROFILE','Mon profile'); ?></h1>
		<? afficher_mes_droits (); ?>
	<h1><? print get_translation('USER_GUIDE','Guide utilisateur'); ?></h1>
	<a href="users_guide.pdf" target="_blank"><? print get_translation('DOWNLOAD_USER_GUIDE','Télécharger le guide utilisateur en cliquant ici'); ?></a>
	<h1><? print get_translation('TO_CITE_FOR_PUBLICATION','A citer pour une publication'); ?></h1>
	Garcelon N, Neuraz A, Benoit V, Salomon R, Burgun A. Improving a full-text search engine: the importance of negation detection and family history context to identify cases in a biomedical data warehouse. 
	J Am Med Inform Assoc. 2016 Oct 20. doi: 10.1093/jamia/ocw144. [Epub ahead of print] PubMed PMID: 28339516. <a href="https://doi.org/10.1093/jamia/ocw144" target="_blank">https://doi.org/10.1093/jamia/ocw144</a> <a href="https://www.ncbi.nlm.nih.gov/pubmed/28339516" target="_blank">pubmed</a>.<br><br>
	
	Nicolas Garcelon, Antoine Neuraz, Vincent Benoit, Rémi Salomon, Sven Kracker, Felipe Suarez, Nadia Bahi-Buisson, Smail Hadj-Rabia, Alain Fischer, Arnold Munnich, Anita Burgun. Finding patients using similarity measures in a rare diseases-oriented clinical data warehouse: Dr. Warehouse and the needle in the needle stack, Journal of Biomedical Informatics, Volume 73, 2017, Pages 51-61, ISSN 1532-0464 <a href="http://dx.doi.org/10.1016/j.jbi.2017.07.016" target="_blank">http://dx.doi.org/10.1016/j.jbi.2017.07.016</a>.
	<h1><? print get_translation('LAST_DATA_LOAD','Dernier chargement'); ?></h1>
	<? afficher_etat_entrepot('last_chargement','600px','','',''); ?>
</div>

<!-- MES COHORTES -->
<div class="div_accueil" style="width:400px;font-size:12px;">
	<h1><img src="images/mine2.png" style="vertical-align: middle"> <? print get_translation('MY_COHORTS','Mes Cohortes'); ?></h1>
	<? afficher_cohorte_ligne_accueil();
	 ?>
</div>




<!-- MES REQUETES SAUVES -->
<div class="div_accueil">
	<h1><? print get_translation('MY_SAVED_SEARCH_QUERIES','Mes Requêtes Sauvegardées'); ?></h1>
	<? lister_requete_sauve_accueil(); ?>
</div>



<!-- ETAT ENTREPOT -->
<div class="div_accueil">
	<h1><? print get_translation('DATAWAREHOUSE_DATA_LOADED',"Etat de l'entrepôt"); ?></h1>
	<a href="etat_etl.php"><? print get_translation('DISPLAY_DETAILS_AFTER_CLICK','Afficher le détail en cliquand ici'); ?></a><br>
	<? afficher_etat_entrepot('document_origin_code','600px','','',''); ?>
</div>
<div class="div_accueil" style="max-width:800px">
	<? afficher_etat_entrepot('document_origin_code_an_mois_presence','600px','','',''); ?>
</div>

	
<!-- MES DATAMARTS -->
<div class="div_accueil" style="width:400px;font-size:12px;">
	<h1><? print get_translation('MY_DATAMARTS','Mes Datamart'); ?></h1>
	<? print get_translation('CONTACT_ADMIN_TO_ACCESS_NEW_DATAMART',"Vous devez contacter l'administrateur de l'entrepôt si vous souhaitez accéder à un nouveau datamart, ou modifier un datamart existant."); ?>
	<br><br>
	<? afficher_datamart_accueil(); ?>
</div>
	


<span style="clear:left;display:block"></span>


<? include "foot.php"; ?>