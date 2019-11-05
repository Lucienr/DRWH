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
session_start();

ini_set("memory_limit","100M");
putenv("NLS_LANG=French");

include_once "parametrage.php";
include_once "connexion_bdd.php";


$sel=oci_parse($dbh,"select firstname,lastname, mail from dwh_user where expiration_date > sysdate ");
oci_execute($sel);
while ($r=oci_fetch_array($sel,OCI_ASSOC+OCI_RETURN_NULLS)) {
	$firstname=$r['FIRSTNAME'];
	$lastname=$r['LASTNAME'];
	$mail=$r['MAIL'];
	
	$message="
Bonjour, 

Nous vous proposons un atelier de formation le mercredi 17 juillet après midi. 
Il y a 3 sessions d'1h30 avec 12 places disponibles pour chaque session. 
La formation aura lieu dans la salle de formation informatique au 1er étage du bâtiment Robert Debré, tout au fond du couloir en travaux.

Voici le lien pour vous inscrire : <a href=\"https://doodle.com/poll/bny9886p9hwa465t\">https://doodle.com/poll/bny9886p9hwa465t</a>

L'objectif n'est pas tant de faire une formation formelle, mais de vous aider à réaliser vos recherches avec Dr Warehouse. 
Venez avec vos questions / projets de recherche, on vous aidera à y répondre.

A la fin de l'atelier, vous devriez tous savoir :
1- Réaliser une recherche plus ou moins complexe
2- Créer une cohorte
3- Inclure des patients dans la cohorte
4- Quitter Dr Warehouse puis vous reconnecter et ajouter de nouveaux patients dans la même cohorte
6- exporter des patients depuis la cohorte

En extra on pourra voir comment sauver des requêtes, rechercher dans une cohorte, exporter des data, importer des patients dans une cohorte à partir d'un fichier excel etc.

Je m'adapterai en fonction de vos besoins et questions.

Amicalement

Nicolas Garcelon
01 42 75 44 57
Institut imagine - Plateforme Data Science

";


$message=str_replace("\n","<br>",$message);
$message=utf8_encode($message);
		
#$mail_from=$argv[1];
#$mail_dest=$argv[2];
#$mail_cc=$argv[3];
#$reply_to=$argv[4];
#$message=$argv[5];
#$sujet=$argv[6];
	
	$mail_dest=$mail;
	$mail_from="nicolas.garcelon@institutimagine.org";
	$reply_to="nicolas.garcelon@institutimagine.org";
	$mail_cc="";
	$sujet="Atelier Dr Warehouse";

	system ("cd /var/www/sites/envoimail; php /var/www/sites/envoimail/envoi_mail_script.php '$mail_from' '$mail_dest' '$mail_cc' '$reply_to' \"$message\" \"$sujet\"");
}


