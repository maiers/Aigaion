<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Keywords extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		
		$this->load->helper('publication');
	}
	
  /** Default function: list publications */
  function index()
	{
    $this->_keywordlist();
	}

  /** List all keywords of one topic in a keyword cloud.
    When no parameter is given, display all publications
  **/
  function _keywordlist()
  {
    $this->load->helper('form');

//    $topic_id = $this->uri->segment(3,-1);
//    if ($topic_id == -1)
//      $topic_id = 1; //no topic? display all
//    $config = array();
//    $topic = $this->topic_db->getByID($topic_id,$config);
//
//    if ($topic==null) {
//        appendErrorMessage(__('Keywords for topic').': '.__('non-existing id passed').'<br/>');
//        redirect('');
//    }
//    
//    $keywordList = $topic->getKeywords();
    $keywordList = $this->keyword_db->getAllKeywords();
    
    //set header data
    $header ['title']         = __('Keywords');
    $header ['javascripts']   = array('prototype.js');
    //$content['header']        = sprintf(__("Keywords for topic %s"),anchor('topics/single/'.$topic->topic_id,$topic->name));
    $content['header']        = "All keywords in the database";
    $content['keywordList']   = $keywordList;
    $content['searchbox']     = True;
    
    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('keywords/list',        $content, true);
    
    $output .= $this->load->view('footer',              '',       true);
    
    //set output
    $this->output->set_output($output);
  }
    
  function searchlist()
  {
    $keyword = $this->input->post('keyword_search');
    if ($keyword) // user pressed show, so redirect to single keyword page
    {
      $keywordList     = $this->keyword_db->getKeywordsLike($keyword);
      if (sizeof($keywordList) > 0)
      {
        $this->single($keywordList[0]->keyword_id);
      }     
    }
    else
    {
      $keyword                = $this->uri->segment(3);
      
      $content['keywordList'] = $this->keyword_db->getKeywordsLike($keyword);
      $content['useHeaders']  = true;
      
      echo $this->load->view('keywords/list_items', $content, true);
    }
  }
  
  function li_keywords($fieldname = "")
  {
    if ($fieldname == "")
      $fieldname = 'keywords';
    
    $keyword = $this->input->post($fieldname);
    if ($keyword != "")
    {
      $content['keywordList'] = $this->keyword_db->getKeywordsLike($keyword);
      $content['useHeaders']  = false;
      
      echo $this->load->view('keywords/list_items', $content, true);
    }
  }
  
  /** 
  single
  
  Entry point for showing a list of publications that have been assigned the given keyword
  
  fails with error message when one of:
    non existing keyword_id
	    
  Parameters passed via segments:
      3rd:  keyword_id
      4rth: sort order
	  5th:  page number
	         
  Returns:
      A full HTML page with all a list of all publications that have been assigned the given keyword
  */
  function single($keyword_id)
  {
    $order = '';
    if (!is_numeric($keyword_id))
    {
      $keyword_id   = $this->uri->segment(3);
      $order   = $this->uri->segment(4,'year');
    }
    if (!in_array($order,array('year','type','recent','title','author'))) {
      $order='year';
    }
    $page   = $this->uri->segment(5,0);
    
    //load keyword
    $keyword = $this->keyword_db->getByID($keyword_id);
    //$keyword = $keywordResult[$keyword_id];
    if ($keyword == null)
    {
      appendErrorMessage(__("View publications for keyword").": ".__("non-existing id passed").".<br/>");
      redirect('');
    }
    $keywordContent ['keyword'] = $keyword;
    
    //load related keywords
    $relatedKeywords = $this->keyword_db->getRelatedKeywords($keyword);
    if (count($relatedKeywords) > 0)
      $keywordContent ['relatedKeywords'] = $relatedKeywords;
    
    $this->load->helper('publication');
    
    $userlogin = getUserLogin();
    
    //set header data
    $header ['title']       = __('Keyword').': "'.$keyword->keyword.'"';
    $header ['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
    $header ['sortPrefix']       = 'keywords/single/'.$keyword->keyword_id.'/';
    $header ['exportCommand']    = '';//'export/keyword/'.$keyword_id.'/';
    $header ['exportName']    = __('Export for keyword');

    //set data
    $publicationContent['header']       = sprintf(__('Publications for keyword "%s"'),$keyword->keyword);
    switch ($order) {
        case 'type':
            $publicationContent['header']          = sprintf(__('Publications for keyword "%s"'),$keyword->keyword).' '.__('sorted by journal and type');
            break;
        case 'recent':
            $publicationContent['header']          = sprintf(__('Publications for keyword "%s"'),$keyword->keyword).' '.__('sorted by recency');
            break;
        case 'title':
            $publicationContent['header']          = sprintf(__('Publications for keyword "%s"'),$keyword->keyword).' '.__('sorted by title');
            break;
        case 'author':
            $publicationContent['header']          = sprintf(__('Publications for keyword "%s"'),$keyword->keyword).' '.__('sorted by first author');
            break;
    }
    if ($userlogin->getPreference('liststyle')>0) {
        //set these parameters when you want to get a good multipublication list display
        $publicationContent['multipage']       = True;
        $publicationContent['currentpage']     = $page;
        $publicationContent['pubCount']        = $this->keyword_db->getPublicationCount($keyword_id);
        $publicationContent['multipageprefix'] = $header['sortPrefix'].'/'.$order.'/';
    }    
    $publicationContent['publications'] = $this->publication_db->getForKeyword($keyword,$order,$page);
    $publicationContent['order'] = $order;

    
    //get output
    $output  = $this->load->view('header',              $header,              true);
    $output .= $this->load->view('keywords/single',     $keywordContent,      true);
    if ($publicationContent['publications'] != null) {
      $output .= $this->load->view('publications/list', $publicationContent,  true);
    }
    else
      $output .= "<div class='messagebox'>".__("No publications found using this keyword.")."</div>";
    
    $output .= $this->load->view('footer',              '',             true);
    
    //set output
    $this->output->set_output($output);  
  }
  
  //edit() - Call keyword edit form.
  function edit($keyword = "")
  {
    if (is_numeric($keyword))
    {
      $keyword_id = $keyword;
      $keyword = $this->keyword_db->getByID($keyword_id);
      
      //set header data
      $edit_type = "edit";
    }
    else if (empty($keyword_id))
    {
      redirect('');
    }
    else
    {
      //there was a keyword post, retrieve the edit type from the post.
      $edit_type = $this->input->post('edit_type');
    }
    
    $userlogin = getUserLogin();
    if (!$userlogin->hasRights('publication_edit'))
    {
      appendErrorMessage(__('Edit keyword').": ".__('insufficient rights').".<br/>");
      redirect('');
    }

    $header ['title']       = sprintf(__("%s keyword"), $edit_type);
    $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js');
    $content['edit_type']   = $edit_type;
    $content['keyword']      = $keyword;
    
    //get output
    $output  = $this->load->view('header',        $header,  true);
    $output .= $this->load->view('keywords/edit',  $content, true);
    $output .= $this->load->view('footer',        '',       true);
    
    //set output
    $this->output->set_output($output);
  }
  
  //merge() - Call keyword merge form. 
  function merge()
  {
    $keyword_id = $this->uri->segment(3);
    $simkeyword_id = $this->uri->segment(4);
    $keyword = $this->keyword_db->getByID($keyword_id);
    $simkeyword = $this->keyword_db->getByID($simkeyword_id);
    if ($keyword==null || $simkeyword==null) {
        appendErrorMessage(__("Cannot merge keywords").": ".__('missing parameters').".<br/>");
        redirect('');
    }
    

    $userlogin = getUserLogin();
    if (!$userlogin->hasRights('publication_edit'))
    {
      appendErrorMessage(__('Cannot merge keywords').": ".__('insufficient rights').'.<br/>');
      redirect('');
    }

    $header ['title']       = __("Keywords").": ".__('Merge');
    $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js');
    $content['keyword']      = $keyword;
    $content['simkeyword']      = $simkeyword;
    
    //get output
    $output  = $this->load->view('header',        $header,  true);
    $output .= $this->load->view('keywords/merge',  $content, true);
    $output .= $this->load->view('footer',        '',       true);
    
    //set output
    $this->output->set_output($output);
  }  
  
  //commit() - Commit the posted keyword to the database
  function commit()
  {
    $userlogin = getUserLogin();
    if (!$userlogin->hasRights('publication_edit'))
    {
      appendErrorMessage(__('Edit keyword').": ".__('insufficient rights').'.<br/>');
      redirect('');
    }

    
    $keyword = $this->keyword_db->getFromPost();
    //check the submit type, if 'type_change', we redirect to the edit form
    $submit_type = $this->input->post('submit_type');
    
      $bReview = false;
      if ($submit_type != 'review')
      {
        //review keyword for similar keywords
        $review_message = $this->keyword_db->review(array($keyword), $keyword->keyword_id);
        
        if ($review_message != null)
        {
          $bReview = true;
          $this->review($keyword, $review_message);
        }
      }
      if (!$bReview)
      {
        //do actual commit, depending on the edit_type, choose add or update
        //
        $edit_type = $this->input->post('edit_type');
        if ($edit_type == 'new') {
          //note: the keyword_db review method will not give an error if ONE EXACT MATCH EXISTS
          //so we should still check that here
//WB: TODO!!!
          if ($this->keyword_db->getByKeyword($keyword->keyword) != null) {
            appendMessage(sprintf(__('Keyword "%s" already exists in the database.'), $keyword->keyword).'<br/>');
            redirect('keywords/add/'.$keyword->keyword);
          } else {
            $keyword = $this->keyword_db->add($keyword);
          }
        } else
          $keyword = $this->keyword_db->update($keyword->keyword_id, $keyword->keyword);
              
        //show publication
        redirect('keywords/single/'.$keyword->keyword_id);
      }
  }
  
  function review($keyword, $review_message)
  {
    $userlogin = getUserLogin();
    if (!$userlogin->hasRights('publication_edit'))
    {
      appendErrorMessage(__('Edit keyword').": ".__('insufficient rights').'.<br/>');
      redirect('');
    }

    $header ['title']       = __("Review keyword");
    $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js');
    $content['edit_type']   = $this->input->post('edit_type');
    $content['keyword']      = $keyword;
    $content['review']      = $review_message;
    
    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('keywords/edit',       $content, true);
    $output .= $this->load->view('footer',              '',       true);
    
    //set output
    $this->output->set_output($output);
  }
  
  //mergecommit() - Do merge commit
  function mergecommit()
  {
    $keyword_id = $this->input->post('keyword_id');
    $newkeyword = $this->input->post('keyword');
    $keyword = $this->keyword_db->getByID($keyword_id);
    $simkeyword_id = $this->input->post('simkeyword_id');
    $simkeyword = $this->keyword_db->getByID($simkeyword_id);
    if ($keyword==null || $simkeyword==null) {
        appendErrorMessage(__("Cannot merge keywords").": ".__('missing parameters').".<br/>");
        redirect('');
    }

    $userlogin = getUserLogin();
    if (!$userlogin->hasRights('publication_edit'))
    {
      appendErrorMessage(__('Cannot merge keywords').": ".__('insufficient rights').'.<br/>');
      redirect('');
    }
    //echo "replace source ".$keyword_id." by target ".$simkeyword_id."<br/>";
    //so... actually, we should now test whether the user has edit access on all involved publications!!!
    $this->keyword_db->replaceSourceTarget($simkeyword_id, $keyword_id);
    $this->keyword_db->delete($simkeyword_id);
    
    if ($keyword->keyword != $newkeyword)
    {
      //echo "update keyword ".$keyword_id.": ".$newkeyword."</br>";
      $this->keyword_db->update($keyword_id, $newkeyword);
    }   
    
    redirect ('keywords/single/'.$keyword->keyword_id);
  }  
  
	/** 
	keywords/delete
	
	Entry point for deleting a keyword.
	Depending on whether 'commit' is specified in the url, confirmation may be requested before actually
	deleting. 
	
	Fails with error message when one of:
	    delete requested for non-existing author
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: keyword_id, the id of the to-be-deleted-keyword
	    4th: if the 4th segment is the string 'commit', no confirmation is requested.
	         if not, a confirmation form is shown; upon choosing 'confirm' this same controller will be 
	         called with 'commit' specified
	         
    Returns:
        A full HTML page showing a 'request confirmation' form for the delete action, if no 'commit' was specified
        Redirects somewhere (?) after deleting, if 'commit' was specified
	*/
	function delete()
	{
	    $keyword_id = $this->uri->segment(3);
	    $keyword = $this->keyword_db->getByID($keyword_id);
	    $commit = $this->uri->segment(4,'');

	    if ($keyword==null) {
	        appendErrorMessage(__('Delete keyword').": ".__('keyword does not exist').'.<br/>');
	        redirect('');
	    }

        $userlogin  = getUserLogin();
        if (    (!$userlogin->hasRights('publication_edit'))
            ) 
        {
	        appendErrorMessage(__('Delete keyword').": ".__('insufficient rights').'.<br/>');
	        redirect('');
        }

        if ($commit=='commit') {
            //do delete, redirect somewhere
            $this->keyword_db->delete($keyword->keyword_id);
            redirect('keywords');
        } else {
            //get output: a full web page with a 'confirm delete' form
            $headerdata = array();
            $headerdata['title'] = __('Keyword').": ".__('delete');
            
            $output = $this->load->view('header', $headerdata, true);
    
            $output .= $this->load->view('keywords/delete',
                                         array('keyword'=>$keyword),  
                                         true);
            
            $output .= $this->load->view('footer','', true);
    
            //set output
            $this->output->set_output($output);	
        }
    }
}
?>