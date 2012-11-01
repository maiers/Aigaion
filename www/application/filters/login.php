<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
| -------------------------------------------------------------------
|  Login Filter
| -------------------------------------------------------------------
|
|   This filter will check whether a user is logged in.
|   If so, nothing is done. 
|   If not, one of two actions is taken:
|       a) this filter will redirect the system  through the login/dologin controller
|    or b) this filter will return an empty div.
|   The choice between the two actions is determined by the filter config parameter
|   'action', which can have one of two values ('redirect','fail')
|*/
//echo 'login filter loaded';
class Login_filter extends Filter {
    
  function before() {
    $CI = &get_instance();
    //get login object
    $userlogin = getUserLogin();

    //if not logged in: redirect to login/dologin
    if (!$userlogin->isLoggedIn()) {
      $segments = $CI->uri->segment_array();
      if ($this->config['action']=='fail') {
        redirect('/login/fail/');
      }
      //Now we need to check for form data before redirecting...
      if ($CI->input->post('formname')!==false) {
        //We have a form...
        //how do we remember the form in the redirect? 
        //maybe serialize the $_POST variable to be resubmitted into the session
        //together with the intended target uri and the name of the form
        //then show a message in the login form 'note: submitted data can be resubmitted after new login'
        //then after login, ask 'resubmit form ... of date ... which was interrupted due to a logout?'
        //
        //so, call 'storeform'
        $CI->load->helper('formrepost');
        storeForm();
        //and then continue logging in again...
      }
      if ($this->config['action']=='redirect') 
      {
        redirect('/login/dologin/'.str_replace(' ','%20',implode('/',$segments)));//note: if we don't replace this %20 / space, we sometimes get truncated dfata after some redirects, so e.g. "readapi/link/topic/Emergent games" in the end (after some login redirects) tries to link to "Emergent"
      } 
      else //action = redirectnoform
      {
        redirect('/login/dologinnoform/'.str_replace(' ','%20',implode('/',$segments)));//note: if we don't replace this %20 / space, we sometimes get truncated dfata after some redirects, so e.g. "readapi/link/topic/Emergent games" in the end (after some login redirects) tries to link to "Emergent"
      }
    } else {
        if ($CI->latesession->get('FORMREPOST')==True) {
            if ($CI->input->post('form_reposted')!==false) {
                //if you are logged in, and you were reposting a form, and this is the reposted form, 
                //then reset the repost variables
                $CI->latesession->set('FORMREPOST', False);
                $CI->latesession->set('FORMREPOST_formname', '');
                $CI->latesession->set('FORMREPOST_uri', '');
                $CI->latesession->set('FORMREPOST_post', '');
            }
        }
    }
  }
}
?>