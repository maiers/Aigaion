<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php

class Readapi extends CI_Controller {

	function __construct()
	{
		parent::__construct();
	}
	
	function index()
	{
	  exit(__('no command'));
	}

  function link()
  {
    $type = $this->uri->segment(3,'');
    if ($type == '') exit(__('no type'));
    switch ($type) {
      case "publication":
        $pub_id = $this->uri->segment(4,'');
        if ($pub_id == '') exit(__('no id'));
        //load publication
        $publication = $this->publication_db->getByID($pub_id);
        if ($publication == null)
        {
          //attempt to retrieve by bibtex_id
          $publication = $this->publication_db->getByBibtexID($pub_id);
            
          if ($publication == null)
          {
            exit(sprintf(__("Unknown id or bibtex_id: %s"),$pub_id));
          }
        }
        echo anchor('publications/show/'.$publication->pub_id,$publication->title,array('target'=>'_blank', 'title'=>__('Go to this publication in the Aigaion database')));
        break;
      case "topic":
	    	$topic_structure = array();
	    	$url_segment = 4;
	    	$topic_name = $this->uri->segment($url_segment,1);

	    	//Checks if the controller is given a topic_id or a topic structure
	    	if(is_numeric($topic_name))
	    	{
	    	  $topic_structure[] = $topic_name;
	    		$topic_id = $topic_name;
	    	}
	    	else
	    	{
	    		//breaks down parts of the url into an array of topics and sub topics. STOPS when either the order (e.g. year, type etc) is reached or when the whole url is parsed.
					while($topic_name != '' && $topic_name != 'year' && 
                $topic_name != 'type' && $topic_name != 'recent' && 
                $topic_name != 'title' && $topic_name != 'author') {
							$topic_structure[] = $topic_name;
							$url_segment++;
							$topic_name = $this->uri->segment($url_segment,'');
					}

					//gets the topicID(s) and checks if it exists, is unique or is a duplicate.
					//If it is a duplicate or if it does not exist, the method fails and outputs and error message.
					$config = array();
					$topic_ids = $this->topic_db->getTopicIDFromNames($topic_structure, $config);
					if(count($topic_ids) == 1)
					{
						$topic_id = $topic_ids[0];
					}
					elseif(count($topic_ids) > 1)
					{
						exit(__("Topic structure is not unique in Aigaion.")." ".implode('/',$topic_structure)."<br/>");
					}
					else
					{
						exit(__("Topic structure does not exist in Aigaion.")." ".implode('/',$topic_structure)."<br/>");
					}
				}
        if ($topic_id == '') exit(__('no topic id'));
        
        //load topic
        $config = array();
        $topic = $this->topic_db->getByID($topic_id,$config);
        if ($topic == null)
        {
          //attempt to retrieve by path
          //$topic = $this->topic_db->getByPath('');
            
          if ($topic == null)
          {
            exit(__("Unknown topic id or topic path").": ".implode('/',$topic_structure));
          }
        }
        echo anchor('topics/single/'.$topic->topic_id,$topic->name,array('target'=>'_blank', 'title'=>__('Go to this topic in the Aigaion database')));
        break;
   case "author":
        $author_id = $this->uri->segment(4,'');
        if ($author_id == '') exit(__('no id'));
        //load author
        $author = $this->author_db->getByID($author_id);
        if ($author == null)
        {
         $firstname = "";
         $von= "";
         $surname = "";
         $jr= "";
          //attempt to retrieve by name
          //$author = $this->author_db->getByExactName($firstname, $von, $surname, $jr);
            
          if ($author == null)
          {
            exit(__("CANNOT INTERPRET FULL AUTHOR NAMES YET.")." ".__("Unknown author id or name").": ".$author_id);
          }
        }
        echo anchor('authors/show/'.$author->author_id,$author->getName(),array('target'=>'_blank', 'title'=>__('Go to this author in the Aigaion database')));
        break;
    default:
        exit(__('Unknown type').': '.$type);
        break;    
    }
	}
	
	function embed()
	{
    $target = $this->uri->segment(3,'');
    $type = $this->uri->segment(4,'');
    if ($type == '') $this->_embed(__('no type'),$target);
    switch ($type) {
      case "publication":
        $pub_id = $this->uri->segment(5,'');
        if ($pub_id == '') $this->_embed(__('no id'),$target);
        //load publication
        $publication = $this->publication_db->getByID($pub_id);
        if ($publication == null)
        {
          //attempt to retrieve by bibtex_id
          $publication = $this->publication_db->getByBibtexID($pub_id);
            
          if ($publication == null)
          {
            $this->_embed(__("Unknown id or bibtex_id").": ".$pub_id,$target);
          }
        }
        $this->_embed( __("Embedding for %s")." ".$publication->title ,$target);
        break;
      case "topic":
	    	$topic_structure = array();
	    	$url_segment = 5;
	    	$topic_name = $this->uri->segment($url_segment,1);

	    	//Checks if the controller is given a topic_id or a topic structure
	    	if(is_numeric($topic_name))
	    	{
	    	  $topic_structure[] = $topic_name;
	    		$topic_id = $topic_name;
	    	}
	    	else
	    	{
	    		//breaks down parts of the url into an array of topics and sub topics. STOPS when either the order (e.g. year, type etc) is reached or when the whole url is parsed.
					while($topic_name != '' && $topic_name != 'year' && 
                $topic_name != 'type' && $topic_name != 'recent' && 
                $topic_name != 'title' && $topic_name != 'author') {
							$topic_structure[] = $topic_name;
							$url_segment++;
							$topic_name = $this->uri->segment($url_segment,'');
					}

					//gets the topicID(s) and checks if it exists, is unique or is a duplicate.
					//If it is a duplicate or if it does not exist, the method fails and outputs and error message.
					$config = array();
					$topic_ids = $this->topic_db->getTopicIDFromNames($topic_structure, $config);
					if(count($topic_ids) == 1)
					{
						$topic_id = $topic_ids[0];
					}
					elseif(count($topic_ids) > 1)
					{
						$this->_embed(__("Topic structure is not unique in Aigaion.")." <br/>");
					}
					else
					{
						$this->_embed(__("Topic structure does not exist in Aigaion.")." <br/>");
					}
				}
        if ($topic_id == '') $this->_embed(__('no topic id'),$target);
        //load topic
        $config = array();
        $topic = $this->topic_db->getByID($topic_id,$config);
        if ($topic == null)
        {
          //attempt to retrieve by path
          //$topic = $this->topic_db->getByPath('');
            
          if ($topic == null)
          {
            $this->_embed(__("Unknown topic id or topic path").": ".implode('/',$topic_structure),$target);
          }
        }
        $this->_embed( __("Embedding for %s")." ".$topic->name,$target); //use a view for this
        break;
   case "author":
        $author_id = $this->uri->segment(5,'');
        if ($author_id == '') $this->_embed(__('no id'),$target);
        //load author
        $author = $this->author_db->getByID($author_id);
        if ($author == null)
        {
         $firstname = "";
         $von= "";
         $surname = "";
         $jr= "";
          //attempt to retrieve by name
          //$author = $this->author_db->getByExactName($firstname, $von, $surname, $jr);
            
          if ($author == null)
          {
            $this->_embed(__("Unknown author id or name").": ".$author_id,$target);
          }
        }
        $this->load->helper('publication');
        $data['author']            = $author;
        $data['publications'] = $this->publication_db->getForAuthor($author_id,'year');
        $view = $this->load->view('readapi/author', $data, true);
        if ($data['publications'] != null) {
          $view .= $this->load->view('readapi/publist', $data, true);
        }
        $this->_embed(  "<div style='border:1px solid grey'>".$view."</div>",$target); 
        break;
    default:
        $this->_embed(__('Unknown type').': '.$type,$target);
        break;    
    }
  }
  
  function _embed($content,$target) 
  {
      $shareddomain = getConfigurationSetting('EMBEDDING_SHAREDDOMAIN');
      if ($shareddomain == '') exit (__("Aigaion error: no shared domain configured in the Aigaion database"));
      header("Content-Type: text/html; charset=UTF-8");
    $output = "<html><head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
    </head><body><div id='embeddingcontent' name='embeddingcontent'>";
    $output .= $content;
    $output .= "</div>
<script type='text/javascript' src='".AIGAION_WEBCONTENT_URL."javascript/prototype.js'></script>
<script type='text/javascript' src='".AIGAION_WEBCONTENT_URL."javascript/scriptaculous.js'></script>
<script type='text/javascript' src='".AIGAION_WEBCONTENT_URL."javascript/builder.js'></script>
<script type='text/javascript' src='".AIGAION_WEBCONTENT_URL."javascript/externallinks.js'></script>
<script type='text/javascript' src='".AIGAION_WEBCONTENT_URL."javascript/YAHOO.js'></script>
<script type='text/javascript' src='".AIGAION_WEBCONTENT_URL."javascript/connection.js'></script>
<script language='javascript'>
  //this needs to be changed to your own domain:
  document.domain='".$shareddomain."';
  window.parent.doEmbedding($('embeddingcontent').innerHTML,'".$target."');
</script></body></html>
";
      exit ($output);
      
  }
}
?>