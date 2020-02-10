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
include "JWTforDWH/generate_jwt.php";

$_SESSION['dwh_login']='';
foreach ($_SESSION as $lib => $val) {
	if (preg_match("/^dwh/",$lib)) {
		$_SESSION[$lib]='';
	}
}

$phrase_time_left='';
if ($_POST['action']=='connexion' && $_POST['passwd']!='' && $_POST['login']!='') {
	$verif_connexion=verif_connexion($_POST['login'],$_POST['passwd'],'page_connexion');
	if ($verif_connexion=='ok') {
		if ($_POST['script_appel']=='connexion_user.php' || $_POST['script_appel']=='') {
			$script_appel='index.php';
		} else {
			$script_appel=preg_replace('/ETCOMMERCIAL/','&',$_POST['script_appel']);
		}
		$_SESSION['dwh_jwt_key_session']=generate_jwt($_SESSION['dwh_user_num'], 18000);

		save_log_page($_SESSION['dwh_user_num'],'connexion');
		header("Location: $script_appel");
		exit;
	} 

	if ($verif_connexion=='modify') {
		save_log_page($_SESSION['dwh_user_num'],'modify_passwd');
		header("Location: modify_passwd.php");
		exit;
	} 
}
session_write_close();
include "menu.php";
?>

<div class="div_connexion" >
	<h1>Welcome on Dr Warehouse</h1> 
	<p>
	Dr Warehouse is an open source biomedical data warehouse.<br>
	Please visit this website for more information : <a href="https://imagine-plateforme-bdd.fr/dwh" target="_blank">https://imagine-plateforme-bdd.fr/dwh</a><br><br>
	</p>
</div>

<div id="id_div_sign_in" class="div_connexion" style="display:block;width:900px;" >
	<h1>Sign in ! </h1>
	<? print "$verif_connexion"; ?>
		<form method="post" action="connexion_user.php">
				<table border="0">
					<tbody><tr><th><? print get_translation('INPUT_YOUR_LOGIN','Saisissez votre identifiant'); ?> : </th><td><input type="text" size="30" value="" name="login" class="form">
					</td></tr><tr><th><? print get_translation('INPUT_YOUR_PASSWORD','Saisissez votre mot de passe'); ?> : </th><td><input type="password" size="30" value="" name="passwd" class="form">
					</td></tr><tr><td></td><td><input class="form_submit" type="submit" value="<? print get_translation('CONNEXION','Connexion'); ?>"></td>
				</td></tr></tbody></table>
				<input type="hidden" value="connexion" name="action">
				<input type="hidden" name="script_appel" value="<? print $_GET['script_appel']; ?>">
		</form>
			<br>
</div>

<? include "foot.php"; ?>


