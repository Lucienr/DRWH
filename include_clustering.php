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
<script language="javascript">
function executer_clustering (tmpresult_num) {
	limite_similarite=document.getElementById("id_input_minimum_similarite").value;
	jQuery.ajax({
		type:"POST",
		url:"clustering_ajax.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'executer_clustering',tmpresult_num:tmpresult_num,limite_similarite:limite_similarite},
		beforeSend: function(requester){
			jQuery("#id_div_resultat_clustering").html("<img src='images/chargement_mac.gif'> <strong><? print get_translation('JS_COMPUTING_CAN_LAST_MINUTES','le calcul peut prendre plusieurs minutes'); ?>. <? get_translation('YOU_CANNOT_NAVIGATE_WHILE_WAITING','Vous pouvez naviguer sur les autres onglets en attendant ...'); ?> "); 
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("executer_clustering('"+tmpresult_num+"')");
			} else {
				if (contenu=='erreur') {
					jQuery("#id_div_resultat_clustering").html('erreur'); 
				} else {
					process_num=contenu;
					if (process_num!='') {
						document.getElementById("id_clustering_process_num").value=process_num;
						setTimeout("verifier_process_fini_executer_clustering('"+process_num+"','"+tmpresult_num+"')",1000);
					}
				}
			}
		}
	});
}



function verifier_process_fini_executer_clustering (process_num,tmpresult_num) {
	jQuery.ajax({
		type:"POST",
		url:"clustering_ajax.php",	
		async:true,
		encoding: 'latin1',
		data:{ action:'verifier_process_fini_executer_clustering',process_num:process_num,tmpresult_num:tmpresult_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("verifier_process_fini_executer_clustering('"+process_num+"','"+tmpresult_num+"')");
			} else {
				tab=contenu.split(';');
				status=tab[0];
				message=tab[1];
				if (status=='1') { //end of process
					afficher_resultat_clustering (process_num,tmpresult_num);
				} else {
					if (status=='erreur') {
						jQuery("#id_div_resultat_clustering").html(message); 
					} else {
						jQuery("#id_div_resultat_clustering").html(message+" <img src='images/chargement_mac.gif'>"); 
						if (document.getElementById("id_clustering_process_num").value==process_num) { // on verifie que le contenu n a pas change pour ne pas lancer plein de process en parallele //
							setTimeout("verifier_process_fini_executer_clustering('"+process_num+"','"+tmpresult_num+"')",1000);
						}
					}
				}
			}
		}
	});
}

function afficher_resultat_clustering (process_num,tmpresult_num) {
	jQuery.ajax({
		type:"POST",
		url:"clustering_ajax.php",
		async:true,
		encoding: 'latin1',
		data:{ action:'afficher_resultat_clustering',process_num:process_num,tmpresult_num:tmpresult_num},
		beforeSend: function(requester){
		},
		success: function(requester){
			var contenu=requester;
			if (contenu=='deconnexion') {
				afficher_connexion("verifier_process_fini_executer_clustering('"+process_num+"','"+tmpresult_num+"')");
			} else {
				jQuery("#id_div_resultat_clustering").html(contenu); 
				$("#id_tableau_similarite_patient").dataTable( {paging: false});
			}
		}
	});
}

	
					
					
	var mouseX;
	var mouseY;
	$(document).mousedown( function(e) {
		mouseX = e.pageX; 
		mouseY = e.pageY;
	});  
	function affiche_intersect(patient_num_1,patient_num_2,id) {
		jQuery.ajax({
			type:"POST",
			url:"similarite_ajax.php",
			async:true,
			encoding: 'latin1',
			data:{ action:'affiche_intersect',patient_num_1:patient_num_1,patient_num_2:patient_num_2,distance:9},
			beforeSend: function(requester){
					document.getElementById('id_affiche_intersect').style.display='block';
					document.getElementById('id_affiche_intersect').innerHTML='<img src="images/chargement_mac.gif">';
					$('#id_affiche_intersect').css({'top':mouseY,'left':mouseX}).fadeIn('slow');
			},
			success: function(requester){
				var contenu=requester;
				if (contenu=='deconnexion') {
					afficher_connexion();
				} else {
					document.getElementById('id_affiche_intersect').innerHTML=contenu;
				}
			},
			complete: function(requester){
				
			},
			error: function(){
			}
		});
	}
</script>

<h2><? print get_translation('PARAMETERS','Paramétrage'); ?></h2>

<? print get_translation('MINIMUM_PERCENT_SIMILARITY','Similarité minimum (en %)'); ?> <input type="text" size="3" value="70" id="id_input_minimum_similarite">
<input type="hidden" id="id_clustering_process_num">
<input type="button" value="<? print get_translation('COMPUTE','calculer'); ?>" onclick="executer_clustering('<? print $tmpresult_num; ?>');">
<br>
<h2><? print get_translation('RESULT','Résultat'); ?></h2>
<div id="id_div_resultat_clustering"></div>

<div id="id_affiche_intersect" style="width:300px;height:500px;border:1px black solid;display:none;overflow-y: auto;position:absolute;background-color:white;"></div>
<?

?>