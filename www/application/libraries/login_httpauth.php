<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/** Login management from httpauth. All this class needs to do is provide on request a login name or groups
of somebody currently login according to the external login module (httpauth in this case...). 
Extension for digest auth by Stephan g.A. */
class Login_httpauth {
    /** Returns an associative array containing the login name of the user and all groups that this user 
    belongs to... (the same names that are stored in aigaion in the abbreviation) */
    function getLoginInfo() {
		$CI = &get_instance();
		$siteconfig = $CI->siteconfig_db->getSiteConfig();
		$group = $siteconfig->getConfigSetting('LOGIN_HTTPAUTH_GROUP');
		$groups = array();
		if ($group)
			$groups[] = $group;

        //return the proper name
        if(isset($_SERVER['PHP_AUTH_USER'])) {
            // Basic authentication information can be retrieved from these server variables
            return array('login'=>$_SERVER['PHP_AUTH_USER'],'groups'=>$groups);
        }
        //requires PHP > 4.1.0
        if(isset($_SERVER['PHP_AUTH_DIGEST'])) {
            //Digest authentification
            $data = $this->http_digest_parse($_SERVER['PHP_AUTH_DIGEST']);
            return array('login'=>$data['username'], 'groups'=>$groups);
        }
        
        //fail
        return array('login'=>'','groups'=>array());
    }

    // function to parse the http auth header into an array of attribute value pairs
    function http_digest_parse($txt) {
        $values = array();
        //this could cause trouble with usernames containing a ','
        $pairs = explode(", ", $txt);
        foreach ($pairs as $pair) {
            $pairValue = explode("=", $pair);
            $values[$pairValue[0]] = str_replace("\"", "" ,$pairValue[1]);
        }
        return $values;
    }
}
?>