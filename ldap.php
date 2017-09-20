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

define("LDAP_SERVER", $GLOBALS['LDAP_SERVER']);
define("LDAP_BASE", $GLOBALS['LDAP_BASE']);
define("LDAP_PROTOCOLE_VERSION",$GLOBALS['LDAP_PROTOCOLE_VERSION']);
define("LDAP_REFERRALS",$GLOBALS['LDAP_REFERRALS']);
define("LDAP_USER", $GLOBALS['LDAP_USER']);
define("LDAP_PASSWD", $GLOBALS['LDAP_PASSWD']);
define("SUFFIXE_MAIL", $GLOBALS['SUFFIXE_MAIL']);

function valid_login_ldap($login,$passwd) {
######################################################################################
#
# Fonction rvalid_login_ldap : bvalider un login passwd 
#   Description : verifier le login passwd sur l'active directory
#   Paramètres : 
# 		login : nom de connexion
#		passwd : mot de passe
# PB ne FONCTIONNE PAS retourne roujours $login !!!
######################################################################################


#	$cLdap = connectLdap();
#	$sResLdap = bindLdap($cLdap, $login, $passwd);
#	disconnectLdap($cLdap);
	
	$sResLdap='nothing';
	return($sResLdap);

# Fin fonction proc_xx----------------------------------------------------------------
######################################################################################
	}	

function connectLdap(){
######################################################################################
## Fonction connectLdap 
#   Description : fonction permettant de se connecter au serveur ldap
#
######################################################################################
		$conLdap = ldap_connect(LDAP_SERVER)
		or die(get_translation('CONNEXION_TO_LDAP_IMPOSSIBLE',"Impossible d'établir la connexion avec le serveur ldap"));
		ldap_set_option($conLdap, LDAP_OPT_PROTOCOL_VERSION, LDAP_PROTOCOLE_VERSION);
		ldap_set_option($conLdap, LDAP_OPT_REFERRALS, LDAP_REFERRALS);
		return $conLdap;
# Fin fonction proc_xx----------------------------------------------------------------
######################################################################################
}
function bindLdapAdmin($conLdap){
######################################################################################
## Fonction bindLdap  
#   Description : Fonction permettant de s'identifier auprès du serveur ldap
#
######################################################################################
	if(!ldap_bind($conLdap,LDAP_USER,LDAP_PASSWD)){
		die(get_translation('LDAP_AUTH_DENIED','Authentification impossible au serveur ldap'));
	}
}
function bindLdap($conLdap,$sUser,$sPwd){
######################################################################################
# Fonction bindLdap  
#   Description :  verifier un login passwd
#
######################################################################################
$sDnUser = "";
		if(@ldap_bind($conLdap,utf8_encode($sUser."@".SUFFIXE_MAIL),utf8_encode($sPwd))){
			return $sUser;
		}else{
			if(@ldap_bind($conLdap,utf8_encode($sUser."@".SUFFIXE_MAIL),utf8_encode($sPwd))){
				return $sUser;
			}else{
				return "nothing";
			}
			return "nothing";
		}

# Fin fonction proc_xx----------------------------------------------------------------
######################################################################################
}

function ldap_user_name($sUser,&$res) {

	$conLdap = connectLdap();

	bindLdapAdmin($conLdap);

	$objRes = ldap_search($conLdap,LDAP_BASE,"(&(sAMAccountName=".$sUser.")(objectCategory=User))");

	if(ldap_count_entries($conLdap,$objRes) == 1){
		$aResult = ldap_get_entries($conLdap,$objRes);
		$sUtiConnecte = $aResult[0]["cn"][0];
		$res[lastname] = $aResult[0]["sn"][0];
		$res[firstname] = $aResult[0]["givenname"][0];
	 	$res[mail]=$aResult[0]["mail"][0];
	} else {
	}
	return($res[lastname].",".$res[firstname].",".$res[mail]);
}


function rechercher_ldap_user_name($sUser,&$res,$grp) {
	global $liste_login;
	$conLdap = connectLdap();
	$sUser=trim ($sUser);
	ldap_bind($conLdap,LDAP_USER,LDAP_PASSWD);
	$objRes = ldap_search($conLdap,LDAP_BASE,"(&(|(sn=".$sUser."*)(cn=".$sUser.")(mail=".$sUser."*))(objectCategory=Person))");
	$aResult = ldap_get_entries($conLdap,$objRes);
	$nbresult=$aResult['count'];
	if($nbresult == 1){
		$aResult = ldap_get_entries($conLdap,$objRes);
		$sUtiConnecte = $aResult[0]["cn"][0];
		$res[lastname] = $aResult[0]["sn"][0];
		$res[firstname] = $aResult[0]["givenname"][0];
	 	$lastname=utf8_decode($aResult[0]["sn"][0]);
	 	$firstname=utf8_decode($aResult[0]["givenname"][0]);
	 	$mail=$aResult[0]["mail"][0];
		$login_ldap=$aResult[0]["samaccountname"][0];
	 	if ($mail!=' ' && $mail!='') {
			$resultat.=  "<a href=\"#\" onclick=\"recup_pers_ldap('$lastname','$firstname','$mail','$login_ldap','$grp','ldap');return false;\"> $lastname  $firstname</a> (ldap)<br>";
		} else {
			$resultat.=  "<a href=\"#\" onclick=\"recup_pers_ldap('$lastname','$firstname','','$login_ldap','$grp','ldap');return false;\"> $lastname  $firstname</a> (pas de mail) (ldap)<br>";
		}
		$liste_login.="'$login_ldap',";
	} else if  ($nbresult>0) {
		if ($nbresult<50 && $nbresult>0) {
			$aResult = ldap_get_entries($conLdap,$objRes);
			 for($i=0;$i<=$nbresult;$i++) {
			 	$lastname=utf8_decode($aResult[$i]["sn"][0]);
			 	$firstname=utf8_decode($aResult[$i]["givenname"][0]);
			 	$mail=$aResult[$i]["mail"][0];
			 	$login_ldap=$aResult[$i]["samaccountname"][0];
			 	if ($lastname!='') {
					$liste_login.="'$login_ldap',";
				 	if ($mail!=' ' && $mail!='') {
						$resultat.=  "<a href=\"#\" onclick=\"recup_pers_ldap('$lastname','$firstname','$mail','$login_ldap','$grp','ldap');return false;\"> $lastname  $firstname</a> (ldap)<br>";
					} else {
						$resultat.= "<a href=\"#\" onclick=\"recup_pers_ldap('$lastname','$firstname','','$login_ldap','$grp','ldap');return false;\"> $lastname  $firstname</a> (pas de mail) (ldap)<br>";
					}
				}
			 }
		}
	}
	$liste_login=substr($liste_login,0,-1);
	return $resultat;
}

function rechercher_ldap_user_name_tableau($sUser,&$res,$grp) {
	global $LDAP_USER,$LDAP_PASSWD,$LDAP_BASE;
	$conLdap = connectLdap();
	$sUser=trim ($sUser);
	ldap_bind($conLdap,$LDAP_USER,$LDAP_PASSWD);
	$objRes = ldap_search($conLdap,$LDAP_BASE,"(&(|(sn=".$sUser."*)(cn=".$sUser.")(mail=".$sUser."*))(objectCategory=Person))");
	$aResult = ldap_get_entries($conLdap,$objRes);
	$nbresult=$aResult['count'];
	if($nbresult == 1){
		$aResult = ldap_get_entries($conLdap,$objRes);
		$sUtiConnecte = $aResult[0]["cn"][0];
		$res[lastname] = $aResult[0]["sn"][0];
		$res[firstname] = $aResult[0]["givenname"][0];
	 	$lastname=utf8_decode($aResult[0]["sn"][0]);
	 	$firstname=utf8_decode($aResult[0]["givenname"][0]);
	 	$mail=$aResult[0]["mail"][0];
		$login_ldap=$aResult[0]["samaccountname"][0];
		if ($login_ldap!='') {
			$resultat.=  "$login_ldap;$firstname $lastname $login_ldap;$lastname,$firstname,$mail-separateur-";
		}
	} else if  ($nbresult>0) {
		if ($nbresult<50 && $nbresult>0) {
			$aResult = ldap_get_entries($conLdap,$objRes);
			 for($i=0;$i<=$nbresult;$i++) {
			 	$lastname=utf8_decode($aResult[$i]["sn"][0]);
			 	$firstname=utf8_decode($aResult[$i]["givenname"][0]);
			 	$mail=$aResult[$i]["mail"][0];
			 	$login_ldap=$aResult[$i]["samaccountname"][0];
			 	if ($login_ldap!='') {
					$resultat.=  "$login_ldap;$firstname $lastname $login_ldap;$lastname,$firstname,$mail-separateur-";
				}
			 }
		}
	}
	return $resultat;
}



function disconnectLdap($conLdap){
######################################################################################
## Fonction disconnectLdap  
#   Description : Fonction permettant de terminer la connexion au serveur ldap
#
######################################################################################
	@ldap_close($conLdap);
# Fin fonction proc_xx----------------------------------------------------------------
######################################################################################
}








?>