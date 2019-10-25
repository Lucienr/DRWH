<?

/*--------------- REGEXP ---------------------------*/
function get_regexp ($regexp_num,$user_num_creation) {
	global $dbh;
	$sel=oci_parse($dbh,"select  TITLE,description, regexp, user_num_creation , creation_date , shared from dwh_regexp where regexp_num=$regexp_num and (shared=1 or user_num_creation=$user_num_creation)");
	oci_execute($sel);
	$regexp=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC);
	return $regexp;
}

function insert_regexp ($title,$description, $regexp, $user_num_creation , $shared) {
	global $dbh;
	
	$title=trim(supprimer_apost($title));
	$description=trim(supprimer_apost($description));
	$regexp=supprimer_apost($regexp);
	
        $regexp_num=get_uniqid();
        
	$req= "insert into dwh_regexp ( regexp_num, title, description, regexp, user_num_creation , creation_date , shared) values ($regexp_num, '$title', '$description', '$regexp', '$user_num_creation' , sysdate , '$shared') ";
	$ins=oci_parse($dbh, $req);
	oci_execute($ins) ||die ("ERreur  $req");
	return $regexp_num;
}

function update_regexp ($regexp_num, $title,$description, $regexp, $user_num_creation , $shared) {
	global $dbh;
	$title=trim(supprimer_apost($title));
	$description=trim(supprimer_apost($description));
	$regexp=supprimer_apost($regexp);
        
	$req= "update  dwh_regexp set title='$title', description='$description', regexp='$regexp', shared='$shared' where regexp_num=$regexp_num and user_num_creation=$user_num_creation";
	$ins=oci_parse($dbh, $req);
	oci_execute($ins) ||die ("ERreur  $req");
	return $regexp_num;
}

function delete_regexp ($regexp_num, $user_num_creation) {
	global $dbh;
	$req= "delete from dwh_regexp where regexp_num=$regexp_num and user_num_creation=$user_num_creation";
	$ins=oci_parse($dbh, $req);
	oci_execute($ins) ||die ("ERreur  $req");
}

function get_list_regexp_user (   $user_num_creation ) {
	global $dbh;
	$tableregexp=array();
	$sel=oci_parse($dbh,"select regexp_num,title, description, regexp, user_num_creation , creation_date , shared from dwh_regexp where user_num_creation=$user_num_creation");
	oci_execute($sel);
	while ($regexp=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$tableregexp[]=$regexp;
	}
	return $tableregexp;
}

function get_list_regexp_shared_not_mine ($user_num_creation) {
	global $dbh;
	$tableregexp=array();
	$sel=oci_parse($dbh,"select regexp_num,title, description, regexp, user_num_creation , creation_date , shared from dwh_regexp where shared=1 and  user_num_creation!=$user_num_creation");
	oci_execute($sel);
	while ($regexp=oci_fetch_array($sel,OCI_RETURN_NULLS+OCI_ASSOC)) {
		$tableregexp[]=$regexp;
	}
	return $tableregexp;
}


function afficher_document_regexp($document_num,$full_text_query) {
        global $dbh,$datamart_num,$user_num_session;
        $document=get_document ($document_num);
	$displayed_text=$document['displayed_text'];     
        $title=$document['title'];     
        $patient_num=$document['patient_num']; 
        $document_date=$document['document_date']; 
        if ($_SESSION['dwh_droit_see_debug']=='ok') {
	       $displayed_text= afficher_dans_document_tal($document_num,$user_num_session);
	}
	$nominative='oui';
        if ($_SESSION['dwh_droit_nominative'.$datamart_num]=='' || $_SESSION['dwh_droit_anonymized'.$datamart_num]=='ok') {
                $displayed_text=anonymisation_document ($document_num,$displayed_text);
                $nominative='non';
                $document_date='[DATE]';
        }
        
        $displayed_text=nettoyer_pour_afficher ($displayed_text);
	$displayed_text=surligner_resultat_exp_reguliere ($displayed_text,$full_text_query,'oui');
      
	$displayed_text=display_image_in_document ($patient_num,$document_num,$user_num_session,$displayed_text);
        
	$display_list_file="<br><br>".display_list_file ($patient_num,$document_num,$user_num_session);

    $res= "
    <div class=\"ui-widget ui-widget-content ui-corner-all ui-front ui-draggable ui-resizable class_document\" style=\"position: absolute; height: 350px; width: 650px; display: none;\" tabindex=\"-1\" id=\"id_enveloppe_document_regexp_$document_num\">
            <div class=\"ui-draggable-handle titre_document_bandeau\" id=\"id_bandeau_regexp_$document_num\">
                    <table border=\"0\" width=\"100%\">
                            <tr>
                                    <td >
                                            <span class=\"entete_document_patient\">".afficher_patient($patient_num,'document',$document_num,'')."</span><br>
                                            <span class=\"titre_document\">$title - $document_date</span>
                                    </td>
                                    <td style=\"text-align:right;\">
                                            <img src=\"images/close.gif\" onclick=\"fermer_document_regexp('$document_num');\" style=\"cursor:pointer\" border=\"0\">
                                    </td>
                            </tr>
                    </table>
            </div>
            <div id=\"id_document_regexp_$document_num\" class=\"afficher_document\">";
	if (preg_match("/(<style[^>]*>|<br[^>]?>)/i",$displayed_text)) {
		$res.="$displayed_text";
	} else {
		$res.="<pre>$displayed_text</pre>";
	}
    $res.="$display_list_file</div>
    </div>
    ";
	save_log_document($document_num,$user_num_session,$nominative);
    return $res;
}



?>