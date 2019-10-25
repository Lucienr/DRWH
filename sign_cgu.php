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

if ($_POST['action']=='sign_cgu' && $_SESSION['dwh_user_num']!='' ) {
	signed_cgu($_SESSION['dwh_user_num']);
	$_SESSION['dwh_sign_cgu']='ok';
	save_log_page($_SESSION['dwh_user_num'],'cgu');
	header("Location: index.php");
	exit;
}

?>
<style>
.div_connexion { 
border-radius: 5px;
    margin: 1em 0;
    padding: 1em 0 3.5em;
    position: relative;
    width: 900px;
    padding: 5px;
    border-color: #d6e3e7;
    border-width:2px;
    background-color: white;
    border-style: solid;
}
</style>

<?
session_write_close();
$cgu=get_last_cgu_published();
?>

<div id="id_menu_flottant"  align="center" class="noprint" >
	<table id="id_tableau_menu_flottant" width="100%" height="25" border="0" cellspacing="0" cellpadding="0" bgcolor="#1e2a63" style="border-top:1px white solid;border-bottom:1px white solid;">
		<tr>
			<td class="connexion" width="1">  </td>
			<td></td>
	</tr></table>
</div>
<div class="div_connexion" >
	<h1>Welcome on Dr Warehouse</h1> 
	<p>
	Merci de Signer les CGU datés du <? print $cgu['cgu_date']; ?>.<br>
	</p>
</div>

<div id="id_div_sign_in" class="div_connexion" style="display:block;width:900px;" >
	<h1>CGU </h1>
	<? print  $cgu['cgu_text']; ?><br>
	<br>
		<form method="post" action="sign_cgu.php">
			<input class="form_submit" type="submit" value="<? print get_translation('SIGN','Signer'); ?>"></td>
			<input type="hidden" value="sign_cgu" name="action">
		</form>
	<br>
</div>

<? include "foot.php"; ?>


