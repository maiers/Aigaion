<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php

/**  */
class Passwordchecker {
    /** 
    Given a username/password combination, a PasswordChecker will return an array with information about the user that can log in using that uname/pwd combination. The returned array may contain:

    * uname (mandatory, needed to find corresponding Aigaion account. If login combination was not valid, this field will be empty "")
    * firstname
    * surname
    * email
    * institute
    * etc 
    * notice

    Any array value that is not null or empty can be used by Aigaion to update the corresponding Aigaion account. 
    */
    function checkPassword($uname, $password,$pwdInMd5) {
        return array('uname'=>'','notice'=>'No real password checking delegate specified'); //this abstract class cannot allow any login
    }

}
?>