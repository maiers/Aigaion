<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php

/** contributed by snoretrash on the AIgaion forums */
class Passwordchecker_pwauth extends Passwordchecker {
    /** 
    pwauth password check: check via apache-mod_authnz_externals
    */
        function checkPassword($uname, $password,$pwdInMd5) {

                if ($pwdInMd5) {
                     return array('uname'=>'','notice'=>sprintf(__('The %s password checker cannot handle md5 passwords'),'pwauth'));
                }

                $handle = popen("/usr/bin/pwauth", "w");
                fwrite($handle, "$uname\n$password\n");
                if (pclose($handle) == 0) {
                        return array('uname'=>$uname);
                }
                return array('uname'=>'','notice'=>__('Not a valid login!'));
        }
}
?>