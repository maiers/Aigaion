<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php

/** Using LDAP for password checking. */
class Passwordchecker_ldap extends Passwordchecker {
    /** 
    Check password using LDAP; get as much info as possible from the LDAP server.
    NEED TESTING
    */
    function checkPassword($uname, $password,$pwdInMd5) {
        if ($pwdInMd5) {
            return array('uname'=>'','notice'=>sprintf(__('The %s password checker cannot handle md5 passwords'),'LDAP'));
        }
        $CI = &get_instance();
        $CI->load->library('authldap');
        //now try to login from LDAP 
        $serverType = "";
        if (getConfigurationSetting('LDAP_IS_ACTIVE_DIRECTORY') != 'FALSE') {
                $serverType = "ActiveDirectory";
        }
        $ldap = new Authldap(getConfigurationSetting('LDAP_SERVER'),
                             getConfigurationSetting('LDAP_BASE_DN'),
                             $serverType, 
                             getConfigurationSetting('LDAP_DOMAIN'),
                             "", "");
        //$ldap->dn = getConfigurationSetting('LDAP_BASE_DN');
        //$ldap->server = getConfigurationSetting('LDAP_SERVER');
    	/*
    	$ldap = new Authldap(
    	getConfigurationSetting('LDAP_SERVER'), 
    	getConfigurationSetting('LDAP_BASE_DN'), 
    	"ActiveDirectory",  $sDomain =  "", 
    	$postloginName, $postloginPwd);
        */
    	$ds = $ldap->connect();
    	if (!$ds) {
      		appendErrorMessage(__("LDAP auth: There was a problem.")."<br/>");
      		appendErrorMessage( __("Error code")." : " . $ldap->ldapErrorCode . "<br/>");
      		appendErrorMessage( __("Error text")." : " . $ldap->ldapErrorText . "<br/>");
    	} else {
   	    
    	    if ($ldap->checkPass($uname,$password)) {
        		//get groups...
        		//get other personal info...
        		return array('uname'=>$uname);//,'groups'=>$groups);
        	} else {
        	    appendErrorMessage($ldap->ldapErrorText);
        	    return array('uname'=>'');
        	}
    	    
    	}
      		//appendErrorMessage( "LDAP auth: Password check failed.<br/>");
      	//	appendErrorMessage( "Error code : " . $ldap->ldapErrorCode . "<br/>");
      		//appendErrorMessage( "Error text : " . $ldap->ldapErrorText . "<br/>");
        
    }

}
?>