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
putenv("NLS_LANG=FRENCH");
putenv("NLS_LANGUAGE=FRENCH_FRANCE.WE8MSWIN1252");

//ini_set("memory_limit","800M");

include_once("parametrage.php");
include_once("connexion_bdd.php");
include_once("ldap.php");
include_once("fonctions_dwh.php");
include_once("fonctions_droit.php");
include_once("fonctions_stat.php");
include_once("verif_droit.php");

if ($maintenance=='ok' && $_SESSION['dwh_droit_admin']==''  && $_SESSION['dwh_login']!='' && !preg_match("/maintenance\.php/i",$_SERVER['REQUEST_URI'])) {
	header("Location: maintenance.php");
	exit;
}


$date_today_unique=date("dmYHis");



?>
<!DOCTYPE html>
<html>
<head>
	<title><? print get_translation('TITLE_APPLICATION','Dr Warehouse'); ?></title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<script src="jquery.js" type="text/javascript"></script>
	<link rel="stylesheet" href="jquery-ui.css">
	<script src="jquery-ui.js"></script>
	<link rel="icon" href="favicon.ico?v=2" />
	<script src="highstock/highstock.js"></script>
	<script src="highstock/modules/exporting.js"></script>
	<script src="DataTables/media/js/jquery.dataTables.js" language="javascript" type="text/javascript"></script>
	<script src="DataTables/extensions/FixedColumns/js/dataTables.fixedColumns.js" language="javascript" type="text/javascript"></script>
	<script src="moment/moment.min.js" language="javascript" type="text/javascript"></script>
	<script src="moment/datetime-moment.js" language="javascript" type="text/javascript"></script>
	
	<script type="text/javascript" language="javascript" src="select2/js/select2.full.js"></script>
	<script type="text/javascript" language="javascript" src="select2/js/i18n/fr.js" charset="UTF-8"></script>
	<link rel="stylesheet" href="select2/css/select2.css">


	<link rel="stylesheet" href="introjs/introjs.css" />
	<style>
	.introjs-tooltip {
		max-width:600px;
	}
	</style>
	
	<link href="DataTables/media/css/jquery.dataTables.css" type="text/css" rel="StyleSheet"></link>
	<link href="jquery-ui.css" type="text/css" rel="StyleSheet"></link>
	
	<link href="chosen/docsupport/prism.css" rel="stylesheet"></link>
	<link href="chosen/chosen.css" rel="stylesheet"></link>
	<script type="text/javascript" src="chosen/chosen.jquery.js?v2"></script>
	<script charset="utf-8" type="text/javascript" src="chosen/docsupport/prism.js"></script>
	<script type="text/javascript" src="jquery.autosize.js"></script>
	<script src="javascript.js?<? print "v=$date_today_unique"; ?>" type="text/javascript"></script>
	
	<link href="style.css?<? print "v=$date_today_unique"; ?>" type="text/css" rel="StyleSheet"></link>
	<link href="style_local.css?<? print "v=$date_today_unique"; ?>" type="text/css" rel="StyleSheet"></link>
	<? if ($_SESSION['dwh_droit_fuzzy_display']=='ok') { ?>
		<link href="style_capture_fuzzy.css?<? print "v=$date_today_unique"; ?>" type="text/css" rel="StyleSheet"></link>
	<? } ?>
</head>
<body>
	<table border="0" cellspacing="0" cellpadding="0" style="background-color:#8F94B1" width="100%" height=113 id="id_tableau_titre_application">
		<tr><td class="geant" style="text-align:center;"><span style="padding-left:60px;"><? print get_translation('DRWAREHOUSE_LOCAL_TITLE','Dr WareHouse'); ?> <sup style="font-size:12px">&copy;<? print get_translation('IMAGINE_INSTITUTE','Imagine'); ?></sup></span><br><span style="font-size: 16px; color:#DDDFE8; line-height: 40px;">Entrepôt de données</span></td></tr>
	</table>
	<div id="id_div_corps">