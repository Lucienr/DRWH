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
<br><br><br><br><br><br><br><br>
</div> 
<!-- fin div id_div_corps -->
<br>
<div style="width:100%;text-align:right;clear: left;padding-top:50px;">
	<hr style="width:100%;border-top-width: 0;">
	<a href="contact.php"><? print get_translation('CONTACT','Contact');?></a> | 
	<a href="log.php"><? print get_translation('LOGS','Logs');?></a> | 
	<a href="etat_etl.php"><? print get_translation('ETL','ETL');?></a> | 
	<a href="credit.php"><? print get_translation('CREDITS','Credits');?></a>
</div>
	<div style="display:none;position:fixed;background-color:white;border:1px black solid;  padding:30px;top:300px;left:400px;" id="id_div_connexion">
		<h1><? print get_translation('PLEASE_RECONNECT','Veuillez vous reconnecter :');?> </h1>
		<h2 id="id_message_erreur"></h2>
		<table border="0">
			<tbody>
			<tr><th><? print get_translation('LOGIN','Identifiant');?> : </th><td><input type="text" size="30" value="" name="login" class="form" id="id_login"></td></tr>
			<tr><th><? print get_translation('PASSWORD','Mot de passe');?> : </th><td><input type="password" size="30" value="" name="passwd" class="form" id="id_passwd"></td></tr>
			</tbody>
		</table>
		<input type="hidden" id="id_commande_a_rejouer">
		<input type="button" onclick="connecter();" value="<? print get_translation('CONNEXION','Connexion');?>">
	</div>
	
	<script language="javascript">
		jQuery(document).ready(function() { 
			<? 
			if ($cohort_num_encours!='') { 
				print "select_cohorte($cohort_num_encours);";
			} 
			?>
			$(".chosen-select").chosen({width: "250px",max_selected_options: 50,allow_single_deselect: true,search_contains:true}); 
		//	crontab_alerte_demande_acces () ;
			crontab_alerte_notification () ;
			
		});
		$(document).click(function(event) { 
			if(!$(event.target).closest('#id_div_menu_notification').length) {
				if (document.getElementById('id_div_notification')) { 
					if (document.getElementById('id_div_notification').style.display=='block') { 
						plier('id_div_notification');
					}
				}
			}        
		})
	</script>
   	<script type="text/javascript" src="javascript_aide.js"></script>
   	<script type="text/javascript" src="introjs/intro.js"></script>
</body>

</html> 