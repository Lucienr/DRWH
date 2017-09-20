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


if ($_GET['action']=='') { 
	$_GET['action']='mes_demandes';
}

if ($_GET['request_access_num']!='') {
	$request_access_num=$_GET['request_access_num'];
	update_access_request ($request_access_num);
}

?>
<script language="javascript">
	function supprimer_demande_acces(request_access_num) {
		if (confirm ('<? print get_translation('JS_CONFIRM_REQUEST_SUPPRESS','Etes vous sûr de vouloir supprimer cette demande ?'); ?> ')) {
			jQuery.ajax({
				type:"POST",
				url:"ajax.php",
				async:true,
				data: { action:'supprimer_demande_acces',request_access_num:request_access_num},
				beforeSend: function(requester){
				},
				success: function(requester){
					if (requester=='deconnexion') {
						afficher_connexion("supprimer_demande_acces("+request_access_num+")");
					} else {
						document.getElementById('id_div_liste_demande').innerHTML=requester;
						document.getElementById('id_div_ma_demande').innerHTML='';
						
					}
				},
				complete: function(requester){
				},
				error: function(){
				}
			});
		}
	}
	
	function autoriser_demande_acces (request_access_num,manager_agreement) {
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:true,
			data: { action:'autoriser_demande_acces',request_access_num:request_access_num,manager_agreement:manager_agreement},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion("autoriser_demande_acces("+request_access_num+","+manager_agreement+")");
				} else {
					document.getElementById('id_div_liste_demande').innerHTML=requester;
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
	
	
	function supprimer_patient_demande_acces (request_access_num,patient_num) {
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:true,
			data: { action:'supprimer_patient_demande_acces',request_access_num:request_access_num,patient_num:patient_num},
			beforeSend: function(requester){
			},
			success: function(requester){
				if (requester=='deconnexion') {
					afficher_connexion("supprimer_patient_demande_acces("+request_access_num+","+patient_num+")");
				} else {
					$("tr#id_tr_patient_demande_"+patient_num).remove();
				}
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
	
	
	
</script>

<div id="id_sous_menu_flottant"  align="center"  >
	<table id="id_tableau_sous_menu_flottant" width="100%" height="25" border="0" cellspacing="0" cellpadding="0" bgcolor="#5F6589" style="border-top:0px white solid;border-bottom:1px white solid;">
		<tr>
			<td nowrap="nowrap" style="padding:0px 10px;" onmouseover="this.style.backgroundColor='#D1D0D5';" onmouseout="this.style.backgroundColor='#5F6589';">&nbsp;&nbsp;&nbsp;&nbsp;
				<span><a class="connexion" href="mes_demandes.php?action=mes_demandes" nowrap=nowrap><? print get_translation('MY_REQUESTS','Mes demandes'); ?></a></span>
				<span id="id_alerte_demande_acces_mes_demandes" class="span_alerte" style="display:none;"></span>
			</td>
			<td class="connexion" width="1"> | </td>
			<td nowrap="nowrap" style="padding:0px 10px;" onmouseover="this.style.backgroundColor='#D1D0D5';" onmouseout="this.style.backgroundColor='#5F6589';">&nbsp;&nbsp;&nbsp;&nbsp;
				<span><a class="connexion" href="mes_demandes.php?action=a_traiter" nowrap=nowrap><? print get_translation('THE_REQUESTS_TO_BE_PROCESSED','Les demandes à traiter'); ?></a></span>
				<span id="id_alerte_demande_acces_a_traiter" class="span_alerte" style="display:none;"></span>
			</td>
			<td width="100%">&nbsp;</td>
		</tr>
	</table>
</div>



<? if ($_GET['action']=='mes_demandes') { ?>
	<h1><? print get_translation('MY_ACCESS_REQUESTS',"Mes demandes d'accès"); ?></h1>
	<table border="0">
		<tr>
			<td style="vertical-align:top;width:450px;">
				<h2><? print get_translation('MY_ACCESS_REQUESTS',"Mes demandes d'accès"); ?> :</h2>
				<div id="id_div_liste_demande">
					<ul>
						<li><? 
							print get_translation('REQUEST_AWAITING','Demandes en attente')
							  lister_mes_demandes($user_num_session,'attente','mes_demandes');
							?>		
						</li>
						<li><? 
							print get_translation('REQUEST_APPROVED','Demandes acceptées');
							  lister_mes_demandes($user_num_session,'ok','mes_demandes');
							?>		
						</li>
						<li><? 
							print get_translation('REQUEST_DENIED','Demandes refusées'); 
							  lister_mes_demandes($user_num_session,'pasok','mes_demandes');
							?>		
						</li>
					</ul>
				</div>
				
			</td>
			<td style="vertical-align:top;">
				<div id="id_div_ma_demande" style="display:block;">
				
				<? 
				$request_access_num=$_GET['request_access_num'];
				if ($request_access_num!='') {
					display_a_request ($request_access_num);
				}
				?>
				</div>
			</td>
		</tr>
	</table>

<? } ?>


<? if ($_GET['action']=='a_traiter') { ?>

	<h1><? print get_translation('MY_REQUESTS_TO_BE_PROCESSED','Mes demandes à traiter'); ?></h1>
	<table border="0">
		<tr>
			<td style="vertical-align:top;width:450px;">
				<h2><? print get_translation('MY_ACCESS_REQUESTS',"Mes demandes d'accès"); ?> :</h2>
				<div id="id_div_liste_demande">
					<ul>
						<li><? 
							print get_translation('REQUEST_AWAITING','Demandes en attente');
							  lister_mes_demandes($user_num_session,'attente','a_traiter');
							?>		
						</li>
						<li><? 
							print get_translation('REQUEST_APPROVED','Demandes acceptées');
							  lister_mes_demandes($user_num_session,'ok','a_traiter');
							?>		
						</li>
						<li><? 
							print get_translation('REQUEST_DENIED','Demandes refusées');
							  lister_mes_demandes($user_num_session,'pasok','a_traiter');
							?>		
						</li>
					</ul>
				</div>
				
			</td>
			<td style="vertical-align:top;">
				<div id="id_div_ma_demande" style="display:block;">
				
				<? 
				$request_access_num=$_GET['request_access_num'];
				if ($request_access_num!='') {
					display_a_request ($request_access_num);
				}
				?>
				</div>
			</td>
		</tr>
	</table>
<? } ?>

<? save_log_page($user_num_session,'my_requests'); ?>
<? include "foot.php"; ?>