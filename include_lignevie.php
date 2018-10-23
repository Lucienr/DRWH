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
include_once("ldap.php");
include_once("fonctions_droit.php");
include_once("fonctions_dwh.php");


if ($_POST['action']=='connexion') {
	$erreur=verif_connexion($_POST['login'],$_POST['passwd'],'page_ajax');
	print "$erreur";
	exit;
}

if ($_SESSION['dwh_login']=='') {
	print "deconnexion";
	exit;
} else {
	include_once("verif_droit.php");
	if ($erreur_droit!='') {
		print "$erreur_droit";
		exit;
	}
}

$patient_num=$_GET['patient_num'];

?>
<html>
<head>
<script src="jquery.js" type="text/javascript"></script>
<script src="jquery-ui.js"></script>
<link rel='stylesheet' href='timeline/styles.css' type='text/css' />
    <style>
        .timeline-default {
            margin: 2em;
        }
        .timeline-event-label {
        	padding-left: 5px;
   		 padding-top: 4px;
        }
        .timeline-horizontal .timeline-date-label {
        	color:black;
        }
        .timeline-event-bubble-time {
        	display: none;
        }
        #body {
	   height:100%;
	}
	 .timeline-event-icon img {
		width:15px;
	}
    </style>

<script type="text/javascript">
Timeline_ajax_url="<? print $URL_DWH; ?>/timeline/timeline_ajax/simile-ajax-api.js";
Timeline_urlPrefix="<? print $URL_DWH; ?>/timeline/timeline_js/";
Timeline_parameters="bundle=true";
</script>
<script src="<? print $URL_DWH; ?>/timeline/timeline_js/timeline-api.js" type="text/javascript"></script>
<style>
	.class_patient {
		padding:10px;
	}
	.class_sejour {
		padding:3px 0px;
	}
	.class_consult {
		
	}
	.class_consult img {
		width:10px;
		height:10px;
	}
	.timeline-event-icon {
		margin: 30px 0px 45px 0px;
	}
	
	.tape-class_sejour {
		margin: 30px 0px 45px 0px;
	}
	.tape-class_patient {
		margin: 40px 0px 45px 0px;
	}
	.tape-class_consult {
		margin: 30px 0px 40px 0px;
	}
	.tape-class_uf {
		margin: 30px 0px 40px 0px;
	}
	
	.label-class_sejour {
		margin:35px 0px 15px 0px;
	}
	
	.label-class_patient  {
		margin:30px 0px 15px 0px;
	}
	.label-class_consult  {
		margin:30px 0px 15px 0px;
	}
	.label-class_uf  {
		margin:30px 0px 15px 0px;
	}
</style>
	
<?

include "ajax_lignevie.php";

	
$selval=oci_parse($dbh,"select to_char (sysdate, 'mm') as sysdatemois,to_char (sysdate, 'yyyy') as sysdatean,to_char (sysdate, 'dd') as sysdatejour from dual");
oci_execute($selval);
$res=oci_fetch_array($selval,OCI_RETURN_NULLS+OCI_ASSOC);
$sysdatemois=mois_en_3lettre($res['SYSDATEMOIS']);
$sysdatejour=$res['SYSDATEJOUR'];
$sysdatean=$res['SYSDATEAN'];
$sysdateheure='00:00:00 GMT';
$sysdate="$sysdatemois $sysdatejour $sysdatean $sysdateheure";

if ($last_date=='') {
	$last_date=$sysdate;
}

$selval=oci_parse($dbh,"select 
	patient_num,encounter_num, 
	to_char (entry_date,  'yyyy') as an_deb,
	to_char (entry_date,  'mm') as mois_deb,
	to_char (entry_date,  'dd') as jour_deb,
	to_char (entry_date,  'HH24:MI') as heure_deb,
	to_char (out_date,  'yyyy') as an_sortie,
	to_char (out_date,  'mm') as mois_sortie,
	to_char (out_date,  'dd') as jour_sortie,
	to_char (out_date,  'HH24:MI') as heure_sortie,
	out_mode, 
	entry_mode,
	entry_date
 from dwh_patient_stay where patient_num=$patient_num order by entry_date asc ");
oci_execute($selval);
while ($res=oci_fetch_array($selval,OCI_RETURN_NULLS+OCI_ASSOC)) {
	$mois_deb=mois_en_3lettre($res['MOIS_DEB']);
	$jour_deb=$res['JOUR_DEB'];
	$an_deb=$res['AN_DEB'];
	$heure_deb=$res['HEURE_DEB'];
	//$date_deb="$mois_deb $jour_deb $an_deb $heure_deb:00 GMT";
	$date_deb="$mois_deb $jour_deb $an_deb $heure_deb:00 GMT";
	
	$mois_sortie=mois_en_3lettre($res['MOIS_SORTIE']);
	$jour_sortie=$res['JOUR_SORTIE'];
	$an_sortie=$res['AN_SORTIE'];
	$heure_sortie=$res['HEURE_SORTIE'];
	//$out_date="$mois_sortie $jour_sortie $an_sortie $heure_sortie:00 GMT";
	$out_date="$mois_sortie $jour_sortie $an_sortie $heure_sortie:00 GMT";
	

	$entry_mode=nettoyer_accent_timeline ($res['ENTRY_MODE']);
	$out_mode=nettoyer_accent_timeline ($res['OUT_MODE']);

	if ($res['MOIS_SORTIE']=='') {
		
	} else {
		$javascript_higlight.= "new Timeline.SpanHighlightDecorator({
						                        startDate:  \"$date_deb\",
						                        endDate:    \"$out_date\",
						                        color:      \"#990000\",
						                        opacity:    20,
						                       cssClass: 't-highlight1'
						                    }),";
	}
}


$javascript_higlight=substr($javascript_higlight,0,-1);
?>


<script language=javascript>
var timelines = [];
var bandInfos=new Array();
function createBandInfosPatient(eventSource) {
	var themedwh = Timeline.ClassicTheme.create(); // create the theme

	bandInfos = [
        Timeline.createBandInfo({
            date:           "<? print $last_date; ?>",
            width:          "80%",
            intervalUnit:   Timeline.DateTime.MONTH,
            intervalPixels: 100,
            eventSource:    eventSource,
            zoomIndex:      10,
            zoomSteps:      new Array(
              {pixelsPerInterval: 280,  unit: Timeline.DateTime.HOUR},
              {pixelsPerInterval: 140,  unit: Timeline.DateTime.HOUR},
              {pixelsPerInterval:  70,  unit: Timeline.DateTime.HOUR},
              {pixelsPerInterval:  35,  unit: Timeline.DateTime.HOUR},
              {pixelsPerInterval: 400,  unit: Timeline.DateTime.DAY},
              {pixelsPerInterval: 200,  unit: Timeline.DateTime.DAY},
              {pixelsPerInterval: 100,  unit: Timeline.DateTime.DAY},
              {pixelsPerInterval:  50,  unit: Timeline.DateTime.DAY},
              {pixelsPerInterval: 400,  unit: Timeline.DateTime.MONTH},
              {pixelsPerInterval: 200,  unit: Timeline.DateTime.MONTH},
              {pixelsPerInterval: 100,  unit: Timeline.DateTime.MONTH},
              {pixelsPerInterval: 50,  unit: Timeline.DateTime.MONTH},
              {pixelsPerInterval: 400,  unit: Timeline.DateTime.YEAR},
              {pixelsPerInterval: 200,  unit: Timeline.DateTime.YEAR},
              {pixelsPerInterval: 100,  unit: Timeline.DateTime.YEAR},
              {pixelsPerInterval: 400,  unit: Timeline.DateTime.DECADE},
              {pixelsPerInterval: 200,  unit: Timeline.DateTime.DECADE},
              {pixelsPerInterval: 100,  unit: Timeline.DateTime.DECADE}
            )
        }),
        Timeline.createBandInfo({
            date:           "Jun 28 2006 00:00:00 GMT",
            width:          "10%",
            intervalUnit:   Timeline.DateTime.YEAR,
            intervalPixels: 200,
            showEventText:  false,
            trackHeight:    0.5,
            trackGap:       0.2,
            eventSource:    eventSource,
            overview:       true
        }),
        Timeline.createBandInfo({
           date:           "Jun 28 2006 00:00:00 GMT",
            width:          "10%",
            intervalUnit:   Timeline.DateTime.DECADE,
           intervalPixels: 200,
           showEventText:  false,
            trackHeight:    0.5,
           trackGap:       0.2,
           eventSource:    eventSource,
           overview:       true,
           theme:	themedwh
      })
    ];
    bandInfos[1].syncWith = 0;
    bandInfos[1].highlight = true;
    bandInfos[2].syncWith = 0;
    bandInfos[2].highlight = true;
    
    
	for (var i = 0; i < bandInfos.length; i++) {
		bandInfos[i].decorators = [
		<? print $javascript_higlight; ?>
		];
	}
    
    return bandInfos;
}
var liste_class_color='';

function onLoad() {
	var isIE = (document.all && !window.opera)?true:false;
	height=isIE  ? window.document.compatMode == "CSS1Compat" ? document.documentElement.clientHeight : document.body.clientHeight : window.innerHeight;
	height=height-70;
	document.getElementById('timeline_patient').style.height=height;
	var eventSource2 = new Timeline.DefaultEventSource();
	
	var bandInfosPatient = createBandInfosPatient(eventSource2);
	timelines[0] = Timeline.create(document.getElementById("timeline_patient"), bandInfosPatient);
	
	Timeline.loadXML("./timeline/xml/<? print $patient_num."_timeline.xml?v=$date_today_unique"; ?>",
	function(xml, url) { eventSource2.loadXML(xml, url); });
}

var resizeTimerID = null;
function onResize() {
	if (resizeTimerID == null) {
		resizeTimerID = window.setTimeout(function() {
			resizeTimerID = null;
			for (var i = 0; i < timelines.length; i++) {
			timelines[i].layout();
		}
		}, 500);
	}
	
	var isIE = (document.all && !window.opera)?true:false;
	//document.getElementById('timeline_patient').style.height=window.innerHeight-20;
	height=isIE  ? window.document.compatMode == "CSS1Compat" ? document.documentElement.clientHeight : document.body.clientHeight : window.innerHeight;
	height=height-70;
	document.getElementById('timeline_patient').style.height=height;
	
	var eventSource2 = new Timeline.DefaultEventSource();
	
	var bandInfosPatient = createBandInfosPatient(eventSource2);
	timelines[0] = Timeline.create(document.getElementById("timeline_patient"), bandInfosPatient);
	
	Timeline.loadXML("timeline/xml/<? print $patient_num."_timeline.xml?v=$date_today_unique"; ?>",
	function(xml, url) { eventSource2.loadXML(xml, url); });
}

function filtre_timeline () {
	requete =document.getElementById('id_input_filtrer_timeline').value;
	liste_class_color='';
	if (requete=='') {
		jQuery(".class_consult").css({ opacity: 1 });
		jQuery(".class_sejour").css({ opacity: 1 });
		jQuery(".class_uf").css({ opacity: 1 });
	} else {
		jQuery.ajax({
			type:"POST",
			url:"ajax.php",
			async:true,
			contentType: 'application/x-www-form-urlencoded',
			encoding: 'latin1',
			data: { action:'filtre_patient_texte_timeline',patient_num:'<? print $patient_num; ?>',requete:escape(requete)},
			beforeSend: function(requester){
				document.getElementById('id_chargement').innerHTML="<img src='images/chargement_mac.gif' width=20>";
				jQuery(".class_consult").css({ opacity: 0.3 });
				jQuery(".class_sejour").css({ opacity: 0.3 });
				jQuery(".class_uf").css({ opacity: 0.3 });
				jQuery(".class_doc").css('color','black');
			},
			success: function(requester){
				var result=requester;
				liste_class_color=result;
				eval(result);
				document.getElementById('id_chargement').innerHTML="<input type=button onclick='filtre_timeline();' value='ok'>";
			},
			complete: function(requester){
			},
			error: function(){
			}
		});
	}
}
var popupdoc=new Array();
function ouvrir_document_timeline (document_num) {
	requete =document.getElementById('id_input_filtrer_timeline').value;
	popupdoc[document_num] = window.open('afficher_document.php?document_num='+document_num+'&requete='+escape(requete),'doc'+document_num,'height=400,width=570,scrollbars=yes,resizable=yes');
	popupdoc[document_num].focus();
}

function remettre_opacite_apres_zoom() {
	if (liste_class_color!='') {
		jQuery(".class_consult").css({ opacity: 0.3 });
		jQuery(".class_sejour").css({ opacity: 0.3 });
		jQuery(".class_uf").css({ opacity: 0.3 });
		jQuery(".class_doc").css('color','black');
		eval(liste_class_color);
	}
}
</script>
</head>
<body onload="onLoad();" onresize="onResize();" style="overflow-y: hidden;">
<? if ($_GET['iframe']!='non') { ?>
	<a href="include_lignevie.php?iframe=non&patient_num=<? print $patient_num; ?>" target="_blank"><? print get_translation('DISPLAY_IN_NEW_TAB','Afficher dans une nouvelle fenêtre'); ?></a>
<? } print get_translation('SEARCH','Rechercher'); ?> : <input type=text size=30  id=id_input_filtrer_timeline onkeyup="if(event.keyCode==13) {filtre_timeline();}"><span id="id_chargement"><input type=button onclick="filtre_timeline();" value='ok'></span>
   <div id="timeline_patient" class="timeline-default" style="overflow: hidden;"  onmouseover="remettre_opacite_apres_zoom ();"   onclick="remettre_opacite_apres_zoom ();" ></div>
</body>
</html>