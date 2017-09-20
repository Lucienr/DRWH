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

	<div style="padding-left:3px;">
		<h1><? print get_translation('CONTACT','Contact'); ?> :</h1>
		<h2><? print get_translation('TO_CONTACT_DATAWAREHOUSE_TEAM',"Pour contacter l'équipe Entrepôt de données"); ?> :</h2>
		<br>
		Nicolas Garcelon
		<br>
		<img src="images/picto-phone.png">
		01 42 75 44 57
		<br>
		<img src="images/picto-mail.png">
		<a href="mailto:nicolas.garcelon@institutimagine.org"> nicolas.garcelon@institutimagine.org</a>
		<br>
		Bâtiment Imagine
		<br>
		2ième étage - bureau 241 
		<br>
		<br>
		<br>
		Vincent Benoit
		<br>
		<img src="images/picto-phone.png">
		01 42 75 44 62
		<br>
		<img src="images/picto-mail.png">
		<a href="mailto:vincent.benoit@institutimagine.org"> vincent.benoit@institutimagine.org</a>
		<br>
		Bâtiment Imagine
		<br>
		2ième étage - bureau 238 
	</div>
	
<? include "foot.php"; ?>