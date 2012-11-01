<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Language extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
	}
	
	/** There is no default controller . */
	function index()
	{
		redirect('');
	}



    /** 
    setlanguage/set
    
    access point to *temporarily* set the language (for this login session)

    Fails (with error message) when one of: 
        non existing language

    Information passed through segments:
        3rd: language
        
    Returns:
        redirects somewhere
            
    */    
    function set() {
      $language = $this->uri->segment(3); 
      $userlogin = getUserLogin();
      //is language in supported list?
      global $AIGAION_SUPPORTED_LANGUAGES;
      if (!in_array($language,$AIGAION_SUPPORTED_LANGUAGES)) 
      {
        appendErrorMessage(__("Unknown language").": \"".$language."\"<br/>");
      }
      else
      {
        $userlogin->effectivePreferences['language'] = $language;
        $this->latesession->set('USERLOGIN',$userlogin);
      }
      $segments = $this->uri->segment_array();
      //remove first three elements
      array_shift($segments);
      array_shift($segments);
      array_shift($segments);
      redirect(implode('/',$segments));
    }  
    
    function choose() {
      //get output
      $headerdata = array();
      $headerdata['title'] = __('Select language');
      $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
      
      $output = $this->load->view('header', $headerdata, true);

      $output .= $this->load->view('language/choose',
                                    array(),  
                                    true);
      
      $output .= $this->load->view('footer','', true);

      //set output
      $this->output->set_output($output);
    }

}


?>