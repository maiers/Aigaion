<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for reposting forms: when a user submits a form, but in the 
|   mean time had been logged out, the form would be lost as soon as 
|   the login filter redirects to the login controller. 
|   What we do to circumvent this is to temporarily store the form while 
|   a new login is in progress, then allow the user to submit this stored 
|   form after all.
|   
|   See e.g. filters/login.php and controllers/login.php
| -------------------------------------------------------------------
|       
*/
    /** Store the following things in the session:
         - $_POST info 
         - the target URL for the form 
         - the name of the form */
    function storeForm() {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        $CI->latesession->set('FORMREPOST', True);
        $CI->latesession->set('FORMREPOST_formname', $CI->input->post('formname'));
        $CI->latesession->set('FORMREPOST_uri', implode('/',$CI->uri->segment_array()));
        $CI->latesession->set('FORMREPOST_post', $_POST);
    }

    /** Attempt to re-submit the form and clear the stored form */
    function resubmitForm() {
        redirect('repostform');
    }
?>