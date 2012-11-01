<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php

class Embeddingtest extends CI_Controller {

	function __construct()
	{
		parent::__construct();
	}
	
	function index()
	{
	  $this->embed();
	}

  /** 
  embeddingtest/embed
  
  This controller is a test for the cross-subdomain embedding.
  
  parameter: an author ID
  
  Right now, this function does not even use proper views, but, well... who knows what will happen in the future :)
  
  NOTE: to understand this embedding controller, you *really* need to look at 
  and think about the clientside_embedding_cross_domain_example.php!
  */
  function embed()
  {
    //retrieve author ID
    $author_id   = $this->uri->segment(3);
    $order   = $this->uri->segment(4,'year');
    if (!in_array($order,array('year','type','recent','title','author'))) {
      $order='year';
    }
    $page   = $this->uri->segment(5,0);
    
    //load author
    $author = $this->author_db->getByID($author_id);
    if ($author == null)
    {
      appendErrorMessage(__("View Author").": ".__("non-existing id passed"));
      redirect('');
    }
    
    $this->load->helper('publication');
    
    $userlogin = getUserLogin();
    
    //set header data
    //$header ['title']       = $author->getName();
    //$header ['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
    
    //set data
    $content['author']            = $author;
    $content['publications'] = $this->publication_db->getForAuthor($author_id,$order);
    
    //get output
    $output = "<div id='embeddingcontent' name='embeddingcontent'>";
    //THIS is the actual line of data that will end up inside the embedding DIV
    //(called )
    $output .= $this->load->view('embeddingtest/test',      $content, true);
    $output .= "</div>
    
<script type='text/javascript' src='".AIGAION_WEBCONTENT_URL."javascript/prototype.js'></script>
<script type='text/javascript' src='".AIGAION_WEBCONTENT_URL."javascript/scriptaculous.js'></script>
<script type='text/javascript' src='".AIGAION_WEBCONTENT_URL."javascript/builder.js'></script>
<script type='text/javascript' src='".AIGAION_WEBCONTENT_URL."javascript/externallinks.js'></script>
<script type='text/javascript' src='".AIGAION_WEBCONTENT_URL."javascript/YAHOO.js'></script>
<script type='text/javascript' src='".AIGAION_WEBCONTENT_URL."javascript/connection.js'></script>
<script language='javascript'>
  //this needs to be changed to your own domain:
  document.domain=\"aigaion.nl\";
  window.parent.doEmbedding($('embeddingcontent').innerHTML);
</script>
";
    //set output
    echo ($output);  
  }  
  
}
?>