<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/** Controller that provides functionality for certain Login integration
 * approaches that can be used to integration Aigaion login with CMSses.
 * 
 * Detailed documentation will be put on the Wiki.  
 */ 
class Logintegration extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
	}
	
	/** no default */
	function index()
	{
		redirect('');
	}

  /** 
   * Return a unique token that will be valid for a given site for a limited 
   * time (15 sec).
   * A CMS or other site will provide its name and get this token.
   * When the CMS later wants to login a user, it will accompany the
   * request with its name and the token to show that the source of the
   * login request is this CMS.      
   * 1st segment: the site name that should be associated with the token.
   * 2st segment: serial number of the request from the CMS
   * 3rd segment: if set to 'keepchecking', the login that will be associated with the token may be logged outby invalidating the token through this controller's logout function    
   * Returns: a unique token.
   */           
  function gettoken() {
    if (getConfigurationSetting('LOGINTEGRATION_SECRETWORD')=='')
    {
      exit(__('Aigaion not configured for this kind of access'));
    }
    $CI = &get_instance();
    $sitename = trim($this->uri->segment(3,''));
    if ($sitename=='') {
      exit("");
    }
    $serial = trim($this->uri->segment(4,''));
    if ($serial=='') {
      exit("");
    }
    $keepchecking = 'FALSE';
    $fifth = trim($this->uri->segment(5,''));
    if ($fifth == 'keepchecking') $keepchecking='TRUE';
    //generate token
    $token = "";
    for ($i = 0; $i < 30; $i++) {
      $rnd = mt_rand(0,15);
      $token .= dechex($rnd); 
    }
    //store token in database, associated with time stamp and site name
    //delete old token for sitename/serial
    $CI->db->delete('logintegration',array('sitename'=>$sitename,'serial'=>$serial));
    $CI->db->insert('logintegration',array('token'=>$token,'time'=>time(),'sitename'=>$sitename,'serial'=>$serial,'keepchecking'=>$keepchecking));
    //return token
    exit($token);
  }
  /**
   * Using a lot of information posted through the segments, log the given 
   * user in. To be accessed client-side.
   * segment 1: site name
   * segment 2: md5 hash made from token (see gettoken), user name and a 
   * secret word shared between the CMS and this installation of Aigaion.  
   * segment 3: the serial number of the request from the CMS            
   * segment 4: user name of an internal account in plain
   */   
  function login() {
    if (getConfigurationSetting('LOGINTEGRATION_SECRETWORD')=='')
    {
      exit(__('Aigaion not configured for this kind of access'));
    }
    //this is a good moment to clean out the logintegration table. Remove all tokens that are expired and were not used.
    $this->db->delete('logintegration',array('status'=>'active','time <'=>(time()-16)));
    $sitename = trim($this->uri->segment(3,''));
    if ($sitename=='') {
      exit("");
    }
    $hash = trim($this->uri->segment(4,''));
    if ($hash=='') {
      exit("");
    }
    $serial = trim($this->uri->segment(5,''));
    if ($serial=='') {
      exit("");
    }
    $username = trim($this->uri->segment(6,''));
    if ($username=='') {
      exit("");
    }
    //get token for sitename+serial
    $res = $this->db->get_where('logintegration',array('sitename'=>$sitename,'serial'=>$serial));
    if ($res->num_rows() ==0) exit (__("No token available"));
    $tokenrow = $res->row();
    $correcttoken = $tokenrow->token;
    $time = $tokenrow->time;
    if ($tokenrow->status=='loggedin') exit(__("Token already logged in"));
    if ($time + 15 < time()) exit (__("Token timed out"));
    $this->load->helper('logintegration');
    if (logintegrationHash($username,$correcttoken) == $hash) {
      $userlogin = getUserLogin();
      $Q = $this->db->get_where('users',array('login'=>$username,'type'=>'normal'));
      if ($Q->num_rows()>0) {
          $userrow = $Q->row();
          $loginPwd = $userrow->password;
          $userlogin->logout();
          if ($userlogin->_login($username,$loginPwd,False,True,True)==0) { 
              //store token in userlogin
              $userlogin->loginToken = $correcttoken; 
              //set keepchecking variable
              if ($tokenrow->keepchecking=='TRUE')
              {
                $userlogin->checkToken = True;
              }
              $this->db->where('token',$correcttoken);
              $this->db->update('logintegration',array('status'=>'loggedin'));
              exit(sprintf(__("Logged in as %s"),$username));
          }
      } else {
          exit(__("Non-existing account"));
      }      
    }
    else 
    {
      exit (__("Fail token"));
    }
    exit(__("Unknown fail"));
  }
  
  /** Log out the user associated to a particular token. Note that this works 
   *  by invalidating the token, and is only effective if the token was 
   *  obtained with the 'keepchecking' parameter set, to tell the userlogin
   *  library that the login token needs to be regularly checked.
   *  3rd segment: sitename
   *  4rth segment: serial
   *  5th segment: hash of sitename, serial and token   
   */              
  function logout()
  {
    if (getConfigurationSetting('LOGINTEGRATION_SECRETWORD')=='')
    {
      exit(__('Aigaion not configured for this kind of access'));
    }
    $sitename = trim($this->uri->segment(3,''));
    if ($sitename=='') {
      exit("a");
    }
    $serial = trim($this->uri->segment(4,''));
    if ($serial=='') {
      exit("b");
    }
    $hash = trim($this->uri->segment(5,''));
    if ($hash=='') {
      exit("c");
    }
    //get token for sitename+serial
    $res = $this->db->get_where('logintegration',array('sitename'=>$sitename,'serial'=>$serial));
    if ($res->num_rows() ==0) exit (__("No token available"));
    $row = $res->row();
    $correcttoken = $row->token;
    $this->load->helper('logintegration');
    if (logintegrationLogoutHash($sitename, $serial, $correcttoken) == $hash) 
    {
      $this->db->update('logintegration',array('status'=>'loggedout'),array('token'=>$correcttoken));
      exit(__("User logged out"));
    }
    exit(__("Wrong parameters for logout"));
  }
}
?>