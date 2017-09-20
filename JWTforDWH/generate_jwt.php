<?php

require_once 'libraries/JWT.php';

use Restserver\Libraries\JWT;
    
function generate_jwt($username, $exp = 18000) {
	$conf = parse_ini_file('config.ini');
        $jwtkey = base64_decode($conf['jwtkey']);
        

        $token['username'] = $username;
        $date = new DateTime();
        $token['iat'] = $date->getTimestamp();
        $token['exp'] = $date->getTimestamp() + $exp;
        $output['id_token'] = JWT::encode($token, $jwtkey);
        return($output['id_token']);

}

?>