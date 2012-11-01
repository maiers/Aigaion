<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Help extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
	}
	
	/**  */
	function index()
	{
	    $this->viewhelp();
	}
	

	function viewhelp() {
        //get output
        $headerdata = array();
        $headerdata['title'] = __('Help');
        $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
        
        $output = $this->load->view('header', $headerdata, true);

        
        $output .= $this->load->view('help/header',
                                      array(),  
                                      true);
        $output .= $this->load->view('help/'.$this->uri->segment(3,'front'),
                                      array(),  
                                      true);
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);

	}

	
}
?>