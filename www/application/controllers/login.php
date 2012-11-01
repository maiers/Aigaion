<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller {
 
    function __construct()
    {
        parent::__construct();    
    }
    
    /** The main controller function will of course show the login form.
        Note that one may pass a specification of a target page where
        the user should be redirected after a successful login by 
        appending a path: 'login/index/path/to/redirect/page' */
    function index()
    {
        $segments = $this->uri->segment_array();
        //remove first two elements
        array_shift($segments);
        array_shift($segments);
        //IF ALREADY LOGGED IN: LINK ON.... TO ANOTHER PAGE
        //get login object
        $userlogin = getUserLogin();
        if ($userlogin->isLoggedIn()) {
            redirect(str_replace(' ','%20',implode('/',$segments)));
        }
        $data = array('segments' => $segments);
  
        //set header data
        $header ['title']       = __('Please login');
        $header ['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js','externallinks.js');
        
        //get output
        $output  = $this->load->view('header_clean',        $header,  true);
        $output .= $this->load->view('login/form',          $data,    true);
        $output .= $this->load->view('footer_clean',        '',       true);
        
        //set output
        $this->output->set_output($output);
    }

    // Same as dologin, but if login fails, show login/fail view instead of login form
    function dologinnoform()
    {
      $this->dologin(false);
    }

    /** This controller will perform the login. The login may be submitted 
        from the login form, or the login may be attempted in one of the numerous 
        other ways (public access, external login, etc). This controller is also
        called when a page is requested that is protected by login while the user 
        is not yet logged in.
        
        When login succeeds, the user is redirected 
        to the front page, or, if specified, the page passed with the original
        request for the login form. 
        
        When login fails, the user is directed back to the login form if showForm==true, and to the login/fail view, otherwise 
        */
    function dologin($showForm = true)
    {
        //get login object
        $userlogin = getUserLogin();

        //try to login
        $userlogin->login();
        if ($userlogin->isLoggedIn()) {
            //if success: redirect
            $this->latesession->set('USERLOGIN', $userlogin);
            $segments = $this->uri->segment_array();
            //attempt to repost submitted form, if neccesary
            if ($this->latesession->get('FORMREPOST')==True) {
                $this->load->helper('formrepost');
                resubmitForm(); 
                //the resubmit also does a redirect.... so the following return is never reached:
                return;
            }
            //remove first two elements
            array_shift($segments);
            array_shift($segments);
            redirect(str_replace(' ','%20',implode('/',$segments))); //note: if we don't replace this %20 / space, we sometimes get truncated dfata after some redirects, so e.g. "readapi/link/topic/Emergent games" in the end (after some login redirects) tries to link to "Emergent" 
        } else {
            //if failure: redirect
            $segments = $this->uri->segment_array();
            //remove first two elements
            array_shift($segments);
            array_shift($segments);
            //note: if cookies are enabled, and we still could not log in here for some reason, we must log out
            //because otherwise we get eternal redirects
            $userlogin->logout(); //it SHOULD be the case that an error message has been set already.
            if ($showForm)
            {
              redirect('/login/index/'.str_replace(' ','%20',implode('/',$segments))); //note: if we don't replace this %20 / space, we sometimes get truncated dfata after some redirects, so e.g. "readapi/link/topic/Emergent games" in the end (after some login redirects) tries to link to "Emergent"
              //note: the 'remembered form', if any was present, is not forgotten; the session info is still there.
            }
            else
            {
              redirect('/login/fail/');
            }
        }
    }
    
    /** This controller will log the currently logged-in user out, then redirect 
        to the dologin controller to allow the system to login an anon account
        (if allowed and posssible). */
    function dologout()
    {
        //get login object
        $userlogin = getUserLogin();
        //logout
        $userlogin->logout();
        $this->latesession->set('USERLOGIN', $userlogin);
        //redirect
        redirect('');

    }
    
    /** 
    login/anonymous
    
    This controller attempts to login one of the guest accounts. Any other currently
    logged user is logged out.
    
    Fails when one of the following:
        the given guest account does not exist or is not anonymous
        no anonymous access is allowed
        
    Parameters passed by segment:
        3rd: user_id of the guest account. default taken from config setting 'ANONYMOUS_USER'
        
    Redirects to the front page
    */
    function anonymous() {
        if (getConfigurationSetting('LOGIN_ENABLE_ANON')!='TRUE') {
            appendErrorMessage(__('Anonymous accounts are not enabled').'.<br/>');
            redirect('');
        }
        $segments = $this->uri->segment_array();
        $user_id = $this->uri->segment(3,getConfigurationSetting('LOGIN_DEFAULT_ANON'));
        $user = $this->user_db->getByID($user_id);
        if (($user==null)||($user->type!='anon')) {
            $user = $this->user_db->getByLogin($user_id);
        }
        if (($user==null)||($user->type!='anon')) {
            appendErrorMessage(__('Anonymous login: no existing anonymous user_id provided').'.<br/>');
            redirect('');
        }
        
        //get login object
        $userlogin = getUserLogin();
        //logout
        $userlogin->logout();
        //login given anonymous user
        $result = $userlogin->loginAnonymous($user->user_id);
        $this->latesession->set('USERLOGIN', $userlogin);
        if (($result==1)||($result==2)) {
            appendErrorMessage(__('Error logging in anonymous account').'<br/>');
        }
        array_shift($segments);
        array_shift($segments);
        array_shift($segments);
        redirect(implode('/',$segments));
    }
    /** This controller function displays a failure message in a div. no surrounding 
        HTML is included. This can be used for controllers that in themselves do not
        show a full html page but rather a sub-view, and where failure to login should
        not redirect the user to a login page. */
    function fail() {
        $this->load->view('login/fail');
    }
}
?>