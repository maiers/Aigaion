<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Rss extends CI_Controller {

	function __construct()
	{
		parent::__construct();
	}
	
  /**  
  
  */
  function index()
	{
    redirect('rss/publicstream');
	}

  /** Public RSS: 
  No login filter (most rss readers cannot handle the associated redirects); only return content if anon login is enabled and an anon user exists; return RSS data for the 'n' most recent publications
  3rd segment: n  */
  function publicstream() 
  {
    $n = $this->uri->segment(3,50);

    $publications = array();

    //check: is there public access?
    if (getConfigurationSetting("LOGIN_ENABLE_ANON")!="TRUE")
    {
      die ("No public access");
    }
    //check: is there an anon user (no reason to actually get the data of this user...)
    $user_id = getConfigurationSetting("LOGIN_DEFAULT_ANON");
    $Q = $this->db->get_where('users',array('user_id'=>$user_id,'type'=>'anon'));
    if ($Q->num_rows()==0) {
        die ("No default anonymous user");
    }    
    
    //get public publications
    $this->db->distinct();
    $this->db->order_by("pub_id","desc");
    $this->db->limit($n);
    $Q = $this->db->get_where("publication",array("derived_read_access_level"=>"public"));

    foreach ($Q->result() as $row)
    {
      $next = $this->publication_db->getFromRow($row);
      if ($next != null)
      {
        $publications[] = $next;
      }
    }

    //build RSS for these publications
    $output = $this->load->view('rss/public', array("publications"=>$publications), true);
    
    //set output
    $this->output->set_output($output);
    
  }
}
