<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for accessing the UserLogin object
| -------------------------------------------------------------------
|
|   Provides access to the UserLogin object
|
|	Usage:
|       $this->load->helper('login'); //load this helper
|       $val = getUserLogin(); //retrieve UserLogin object
|
|   Implementation:
|       The UserLogin object will not be created until requested for the first time.
|       When it is requested for the first time it is created and stored in the session.
|       
*/

    function getUserLogin() {
        $CI = &get_instance();
    
        $userlogin = $CI->latesession->get('USERLOGIN');
        if (!isset($userlogin)||($userlogin==null)) {
            $userlogin = new UserLogin();
            $CI->latesession->set('USERLOGIN',$userlogin);
        }
        return $userlogin;
    }

?>