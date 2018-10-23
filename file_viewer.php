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
putenv("NLS_LANG=French");
include_once "parametrage.php";
include_once "connexion_bdd.php";
include_once("fonctions_droit.php");
include_once("fonctions_dwh.php");

$patient_num=$_GET['patient_num'];
$document_num=$_GET['document_num'];
$file_num_click=$_GET['file_num_click'];


$autorisation_voir_patient_nominative=autorisation_voir_patient_nominative ($patient_num,$user_num_session);
	
if ($autorisation_voir_patient_nominative!='ok') {
	exit;
}

$preview=$_GET['preview'];
$tableau_ext_image=array('jpg', 'jpeg', 'gif', 'png','JPG', 'JPEG', 'GIF', 'PNG');
$liste_ext_image="'jpg', 'jpeg', 'gif', 'png','JPG', 'JPEG', 'GIF', 'PNG'";

$i=0;
$i_click=0;
print "<script src=\"jquery.js\" type=\"text/javascript\"></script>";

print "<table border=0 height=100%><tr><td width=130  style=\"vertical-align:top;\">
<div  style=\"height: 600px; overflow-y: auto;overflow-x: none;\" id=\"id_scroller\">";

$selval=oci_parse($dbh,"select file_num ,file_title ,file_content,lower(file_mime_type) file_mime_type  from dwh_file where document_num=$document_num  and file_mime_type in ($liste_ext_image) order by file_order");
oci_execute($selval);
while ($res=oci_fetch_array($selval,OCI_RETURN_NULLS+OCI_ASSOC)) {
	$file_mime_type=$res['FILE_MIME_TYPE'];
	$file_title=$res['FILE_TITLE'];
	$file_num=$res['FILE_NUM'];
	if ($file_num_click=='' && $i==0) {
		$file_num_click=$file_num;
	} 
	if ($file_num_click==$file_num) {
		$i_click=$i;
		$style="style=\"border:medium solid red;\"";
	} else {
		$style='';
	}
	
	print "<div><img $style id=\"img_$i\" src=\"ajax.php?action=load_file&file_num=$file_num\" width=100 height=100 onclick=\"forcer_img($i);\"></a></div><div style=\"height:10px\"></div>";
	
	$file_content=$res['FILE_CONTENT']->load();
	if ($image_origine = imagecreatefromstring($file_content)) {
		$largeur_origine = imagesx($image_origine);
		$hauteur_origine = imagesy($image_origine);
		print "<input type=hidden id=height_$i value=\"$hauteur_origine\">";
		print "<input type=hidden id=width_$i value=\"$largeur_origine\">";
		imagedestroy($image_origine);
	}
	$i++;
}

$imax=$i-1;

print "</div></td><td valign=top>";
print "<img  id=\"img_principale\" src=\"ajax.php?action=load_file&file_num=$file_num_click\" border=0></a>";
print "</td><td valign=top><div id=\"id_dicom_principal\" style=\"display:block;overflow:auto;\">$liste_dicom</div></td></tr></table>
<script type=\"text/javascript\">
	if (document.body) {
		var hauteur_cible = (document.body.clientHeight)-30;
	} else {
		var hauteur_cible = (window.innerHeight)-10;
	}
	document.getElementById(\"id_scroller\").style.height=hauteur_cible+'px';
	
	var slideshow=document.getElementById(\"img_principale\");
	var nextslideindex=$i_click;
	 
	dim_img(nextslideindex);
		
	function forcer_img (i) {
	    	document.getElementById('img_'+nextslideindex).style.border='';
	 	document.getElementById('img_principale').src=document.getElementById('img_'+i).src;
	 	//document.getElementById('id_dicom_principal').innerHTML=document.getElementById('id_dicom_'+i).innerHTML;
	    	document.getElementById('img_'+i).style.border='medium solid red';
	 	nextslideindex=i;
	}
	
	function rotateimage(e){
		document.getElementById('img_'+nextslideindex).style.border='';
		var evt=window.event || e ; //equalize event object
		var delta=evt.detail? evt.detail*(-120) : evt.wheelDelta ; //delta returns +120 when wheel is scrolled up, -120 when scrolled down
		nextslideindex=(delta<=-120)? nextslideindex+1 : nextslideindex-1; //move image index forward or back, depending on whether wheel is scrolled down or up
		if (nextslideindex<0) {
			//nextslideindex=$imax;
			nextslideindex=0;
		} else {
			if (nextslideindex>$imax) {
				//nextslideindex=0;
				nextslideindex=$imax;
			} 
		}
		slideshow.src=document.getElementById('img_'+nextslideindex).src;
	 	//document.getElementById('id_dicom_principal').innerHTML=document.getElementById('id_dicom_'+nextslideindex).innerHTML;
		document.getElementById('img_'+nextslideindex).style.border='medium solid red';
		$('#img_'+nextslideindex).get(0).scrollIntoView();
	
		dim_img(nextslideindex);
		if (evt.preventDefault) { //disable default wheel action of scrolling page
			evt.preventDefault();
		} else {
			return false;
		}
	}
	 
	var mousewheelevt=(/Firefox/i.test(navigator.userAgent))? \"DOMMouseScroll\" : \"mousewheel\" ;//FF doesn't recognize mousewheel as of FF3.x
	if (slideshow.attachEvent) { //if IE (and Opera depending on user setting)
		slideshow.attachEvent(\"on\"+mousewheelevt, rotateimage);
	} else if (slideshow.addEventListener) { //WC3 browsers
		slideshow.addEventListener(mousewheelevt, rotateimage, false);
	}
	
	function dim_img (i) {
		if (document.body) {
			var largeur_cible = (document.body.clientWidth)-30;
			var hauteur_cible = (document.body.clientHeight)-30;
		} else {
			var largeur_cible = (window.innerWidth)-10;
			var hauteur_cible = (window.innerHeight)-10;
		}
		if (document.getElementById('height_'+i)) {
			hauteur_origine=document.getElementById('height_'+i).value;
			largeur_origine=document.getElementById('width_'+i).value;
			
			test_h = Math.round((largeur_cible / largeur_origine) * hauteur_origine);
			test_w = Math.round((hauteur_cible / hauteur_origine) * largeur_origine);
			
			if(hauteur_cible=='') {
				hauteur_cible = test_h;
			} else if (largeur_cible=='') {
				largeur_cible = test_w;
			} else if (test_h > hauteur_cible) {
				largeur_cible = test_w;
			} else {
				hauteur_cible = test_h;
			}
			document.getElementById('img_principale').style.height=hauteur_cible;
			document.getElementById('img_principale').style.width=largeur_cible;
		//	document.getElementById('id_dicom_principal').style.height=hauteur_cible-30;
		}
	}
</script>
";
?>