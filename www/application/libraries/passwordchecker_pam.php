<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php

/** Using PAM for password checking. Contributed by questmaster on the Aigaion forums. Requires php_pam_auth module */
class Passwordchecker_pam {
    /** 
    Check password using PAM;
    */
    function checkPassword($uname, $password,$pwdInMd5) {
        if ($pwdInMd5) {
            return array('uname'=>'','notice'=>sprintf(__('The %s password checker cannot handle md5 passwords'),'PAM'));
        }

        //now try to login from PAM
        if (pam_auth($uname,$password, &$error)) {
            return array('uname'=>$uname);
        } else {
            appendErrorMessage($error);
            return array('uname'=>'');
        }
    }
}
?>