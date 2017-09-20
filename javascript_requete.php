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
?><script language="javascript">

	function modifier_requete(query_num) {
		title_query=document.getElementById('id_input_titre_requete').value;
		crontab_query='';
		if (document.getElementById('id_crontab_requete').checked==true) {
			crontab_query=1;
		}
		crontab_periode=document.getElementById('id_crontab_periode').value;
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:true,
			data: { action:'modifier_requete',query_num:query_num,title_query:escape(title_query),crontab_periode:crontab_periode,crontab_query:crontab_query},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion();
				} else {
					document.getElementById('id_div_liste_requete').innerHTML=requester;
					document.getElementById('id_sous_span_titre_requete').innerHTML=title_query;
					plier('id_titre_requete_modifier');
					deplier('id_titre_requete','inline');
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
	function supprimer_requete(query_num) {
		if (confirm ('<? print get_translation('JS_CONFIRM_QUERY_SUPPRESS','Etes vous sûr de vouloir supprimer cette requete')." ?"; ?> ')) {
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:true,
				data: { action:'supprimer_requete',query_num:query_num},
				beforeSend: function(requester){
				},
				success: function(requester){
					if (requester=='deconnexion') {
						afficher_connexion();
					} else {
						document.getElementById('id_div_liste_requete').innerHTML=requester;
						document.getElementById('id_div_ma_requete').innerHTML='';
						
					}
				},
				complete: function(requester){
				},
				error: function(){
				}
			});
		}
	}
</script>