<?
if ($_SESSION[$GLOBALS['PREFIX_INSTANCE_DWH'].'_dwh_droit_admin']!='ok') {
	print "<br><br><br>Vous n'avez pas les droits d'administration<br>";
	include "foot.php"; 
	exit;
}
session_write_close();

if (count($tableau_admin_features)==0) {
	$tableau_admin_features=array('admin_department','admin_profil','admin_user','opposition','admin_concepts','admin_thesaurus','admin_etl','analyse_requete','admin_outils','admin_cgu','admin_actu','admin_datamart','admin_contact', 'tokens');
} 


$class_admin_department='admin_menu';
$class_admin_profil='admin_menu';
$class_admin_user='admin_menu';
$class_opposition='admin_menu';
$class_admin_concepts='admin_menu';
$class_admin_thesaurus='admin_menu';
$class_admin_etl='admin_menu';
$class_analyse_requete='admin_menu';
$class_admin_outils='admin_menu';
$class_admin_cgu='admin_menu';
$class_admin_actu='admin_menu';
if ($_GET['action']=='admin_department') {
	$class_admin_department='admin_menu_activated';
}
if ($_GET['action']=='admin_profil') {
	$class_admin_profil='admin_menu_activated';
}
if ($_GET['action']=='admin_user') {
	$class_admin_user='admin_menu_activated';
}
if ($_GET['action']=='opposition') {
	$class_opposition='admin_menu_activated';
}
if ($_GET['action']=='admin_concepts') {
	$class_admin_concepts='admin_menu_activated';
}
if ($_GET['action']=='admin_thesaurus') {
	$class_admin_thesaurus='admin_menu_activated';
}
if ($_GET['action']=='admin_etl') {
	$class_admin_etl='admin_menu_activated';
}
if ($_GET['action']=='analyse_requete') {
	$class_analyse_requete='admin_menu_activated';
}
if ($_GET['action']=='admin_outils') {
	$class_admin_outils='admin_menu_activated';
}
if ($_GET['action']=='admin_cgu') {
	$class_admin_cgu='admin_menu_activated';
}
if ($_GET['action']=='admin_actu') {
	$class_admin_actu='admin_menu_activated';
}
if ($_GET['action']=='admin_contact') {
	$class_admin_contact='admin_menu_activated';
}
?>

<script src="javascript_admin.js?<? print "v=$date_today_unique"; ?>" type="text/javascript"></script>
<style>
.admin_menu {
	padding:0px 5px;
	background-color: #5F6589;
}
.admin_menu:hover {
	background-color: #D1D0D5;
}
.admin_menu_activated {
	padding:0px 5px;
	background-color: #D1D0D5;
}
</style>

<div id="id_sous_menu_flottant"  align="center"  >
	<table id="id_tableau_sous_menu_flottant" width="100%" height="25" border="0" cellspacing="0" cellpadding="0" bgcolor="#5F6589" style="border-top:0px white solid;border-bottom:1px white solid;">
		<tr>
		<? if (in_array('admin_department',$tableau_admin_features)) {?>
			<td nowrap="nowrap" class="<? print $class_admin_department; ?>"><a class="connexion" href="admin.php?action=admin_department" nowrap=nowrap><? print get_translation('THE_HOSPITAL_DEPARTMENTS','Services'); ?></a></td>
			<td class="connexion" width="1"> | </td>
		<? } ?>
		<? if (in_array('admin_profil',$tableau_admin_features)) {?>
			<td nowrap="nowrap" class="<? print $class_admin_profil; ?>"><a class="connexion" href="admin.php?action=admin_profil" nowrap=nowrap><? print get_translation('THE_PROFILES','Profils'); ?></a></td>
			<td class="connexion" width="1"> | </td>
		<? } ?>
			
		<? if (in_array('admin_user',$tableau_admin_features)) {?>
			<td nowrap="nowrap" class="<? print $class_admin_user; ?>"><a class="connexion" href="admin.php?action=admin_user" nowrap=nowrap><? print get_translation('THE_USERS','Utilisateurs'); ?></a></td>
			<td class="connexion" width="1"> | </td>
		<? } ?>
		<? if (in_array('admin_datamart',$tableau_admin_features)) {?>
<!--
			<td nowrap="nowrap"><a class="connexion" href="admin.php?action=admin_datamart" nowrap=nowrap><? print get_translation('THE_DATAMART','Datamart'); ?></a></td>
			<td class="connexion" width="1"> | </td>
-->
		<? } ?>
		<? if (in_array('opposition',$tableau_admin_features)) {?>
			<? if ($module_arno_active) { ?>
					<td  nowrap="nowrap" class="admin_menu">
            <a href="oppositions.php" class="connexion" nowrap=nowrap><?print get_translation('OPPOSITION','Opposition'); ?></a>
          </td>
					<td class="connexion" width="1"> | </td>
			<? } else { ?>
				<td nowrap="nowrap" class="<? print $class_opposition; ?>">
          <a class="connexion" href="admin.php?action=opposition" nowrap=nowrap><? print get_translation('OPPOSITION','Opposition'); ?></a>
        </td>
				<td class="connexion" width="1"> | </td>
			<? } ?>
		<? } ?>
		<? if (in_array('admin_concepts',$tableau_admin_features)) {?>
			<td nowrap="nowrap" class="<? print $class_admin_concepts; ?>"><a class="connexion" href="admin.php?action=admin_concepts" nowrap=nowrap><? print get_translation('THE_CONCEPTS','Concepts'); ?></a></td>
			<td class="connexion" width="1"> | </td>

		<? } ?>
		<? if (in_array('admin_thesaurus',$tableau_admin_features)) {?>
			<td nowrap="nowrap" class="<? print $class_admin_thesaurus; ?>"><a class="connexion" href="admin.php?action=admin_thesaurus" nowrap=nowrap><? print get_translation('THE_THESAURUS','Thesaurus'); ?></a></td>
			<td class="connexion" width="1"> | </td>
			
		<? } ?>
		<? if (in_array('admin_etl',$tableau_admin_features)) {?>
			<td nowrap="nowrap" class="<? print $class_admin_etl; ?>"><a class="connexion" href="admin.php?action=admin_etl" nowrap=nowrap><? print get_translation('ETL','ETL'); ?></a></td>
			<td class="connexion" width="1"> | </td>
			
		<? } ?>
		<? if (in_array('analyse_requete',$tableau_admin_features)) {?>
  			<td nowrap="nowrap" class="<? print $class_analyse_requete; ?>"><a class="connexion" href="admin.php?action=analyse_requete" nowrap=nowrap><? print get_translation('QUERIES','Requêtes'); ?></a></td>
			<td class="connexion" width="1"> | </td>
			
		<? } ?>
		<? if (in_array('admin_outils',$tableau_admin_features)) {?>
			<td nowrap="nowrap" class="<? print $class_admin_outils; ?>"><a class="connexion" href="admin.php?action=admin_outils" nowrap=nowrap><? print get_translation('THE_TOOLS','Outils'); ?></a></td>
			<td class="connexion" width="1"> | </td>
			
		<? } ?>
		<? if (in_array('admin_cgu',$tableau_admin_features)) {?>
			<td nowrap="nowrap" class="<? print $class_admin_cgu; ?>"><a class="connexion" href="admin.php?action=admin_cgu" nowrap=nowrap><? print get_translation('CGU','CGU'); ?></a></td>
			<td class="connexion" width="1"> | </td>
		<? } ?>
		<? if (in_array('admin_actu',$tableau_admin_features)) {?>
			<td nowrap="nowrap" class="<? print $class_admin_actu; ?>"><a class="connexion" href="admin.php?action=admin_actu" nowrap=nowrap><? print get_translation('NEWS','Actus'); ?></a></td>
			<td class="connexion" width="1"> | </td>
		<? } ?>
		<td nowrap="nowrap" class="<? print $class_admin_contact; ?>"><a class="connexion" href="admin.php?action=admin_contact" nowrap=nowrap><? print get_translation('CONTACT','Contacts'); ?></a></td>
		<td class="connexion" width="1"> | </td>
			
    <? if (in_array('tokens', $tableau_admin_features)) {?>
      <? if ($module_arno_active) { ?>
      <td  nowrap="nowrap" class="admin_menu">
        <a href="generate_token.php" class="connexion" nowrap=nowrap><?print get_translation('TOKEN','Token'); ?></a>
      </td>
      <td class="connexion" width="1"> | </td>
      <? } ?>
    <? } ?>

    <td width="100%">&nbsp;</td>
		</tr>
	</table>
</div>