<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php

class Authors extends CI_Controller {

	function __construct()
	{
		parent::__construct();
	}
	
	function index()
	{
	  $this->_authorlist();
	}

  //show() - Call single author overview
  function show($author_id)
  {
    if (!is_numeric($author_id))
    {
      //retrieve author ID
      $author_id   = $this->uri->segment(3);
    }
    $order   = $this->uri->segment(4,'year');
    if (!in_array($order,array('year','type','recent','title','author'))) {
      $order='year';
    }
    $page   = $this->uri->segment(5,0);
    
    //load author
    $author = $this->author_db->getByID($author_id);
    if ($author == null)
    {
      appendErrorMessage(__('View Author').": ".__('non-existing id passed').".<br/>");
      redirect('');
    }
    
    $this->load->helper('publication');
    
    $userlogin = getUserLogin();
    
    //set header data
    $header ['title']       = $author->getName();
    $header ['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js','externallinks.js');
    $header ['sortPrefix']       = 'authors/show/'.$author->author_id.'/';
    $header ['exportCommand']    = 'export/author/'.$author->author_id.'/';
    $header ['exportName']    = __('Export author');

    //set data
    $authorContent['author']            = $author;
    $publicationContent['header']       = sprintf(__('Publications of %s'),$author->getName());
    switch ($order) {
        case 'type':
            $publicationContent['header']          = sprintf(__('Publications of %s sorted by journal and type'), $author->getName());
            break;
        case 'recent':
            $publicationContent['header']          = sprintf(__('Publications of %s sorted by recency'), $author->getName());
            break;
        case 'title':
            $publicationContent['header']          = sprintf(__('Publications of %s sorted by title'), $author->getName());
            break;
        case 'author':
            $publicationContent['header']          = sprintf(__('Publications of %s sorted by first author'), $author->getName());
            break;
    }
    if ($userlogin->getPreference('liststyle')>0) {
        //set these parameters when you want to get a good multipublication list display
        $publicationContent['multipage']       = True;
        $publicationContent['currentpage']     = $page;
        $publicationContent['pubCount']        = $this->author_db->getPublicationCount($author_id);
        $publicationContent['multipageprefix'] = 'authors/show/'.$author_id.'/'.$order.'/';
    }    
    $include_synonyms = false;
    if ($author->hasSynonyms()) $include_synonyms = true;
    $publicationContent['publications'] = $this->publication_db->getForAuthor($author_id,$order,$page,$include_synonyms);
    $publicationContent['order'] = $order;

    
    //get output
    $output  = $this->load->view('header',              $header,        true);
    $output .= $this->load->view('authors/single',      $authorContent, true);
    
    if ($publicationContent['publications'] != null) {
      $output .= $this->load->view('publications/list', $publicationContent, true);
    }
    
    $output .= $this->load->view('footer',              '',             true);
    
    //set output
    $this->output->set_output($output);  
  }

  /** 
  authors/embed
  
  A controller that should return only the basic contents of the single author publication listing.
  Can be used to embed a few of your own publications into acompletely different page. Note that in that case
  you need to 
  a) have anonymous access enabled
  b) have the publications of the requested author that you want to show embedded in another page set as being public
     (that concerns the access levels)
  c) from this surrounding page (presumably your own web page?), somehow read
     the file <aigaion2_root>/index.php/authors/embed/<author_id>/type
     and past the resulting html on screen :)
     (the php function readfile might works on your server)
  d) have a proper stylesheet included in that surrounding page (?)
  
  takes as arguments: 
    3rd: author_id
    4rth: sort order
    5th: page number 
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
      appendErrorMessage(__('View Author').": ".__('non-existing id passed').".<br/>");
      redirect('');
    }
    
    $this->load->helper('publication');
    
    $userlogin = getUserLogin();
    
    //set header data
    //$header ['title']       = $author->getName();
    //$header ['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
    
    //set data
    $authorContent['author']            = $author;
    $publicationContent['header']       = sprintf(__('Publications of %s'),$author->getName());
    switch ($order) {
        case 'type':
            $publicationContent['header']          = sprintf(__('Publications of %s sorted by journal and type'), $author->getName());
            break;
        case 'recent':
            $publicationContent['header']          = sprintf(__('Publications of %s sorted by recency'), $author->getName());
            break;
        case 'title':
            $publicationContent['header']          = sprintf(__('Publications of %s sorted by title'), $author->getName());
            break;
        case 'author':
            $publicationContent['header']          = sprintf(__('Publications of %s sorted by first author'), $author->getName());
            break;
    }
    if ($userlogin->getPreference('liststyle')>0) {
        //set these parameters when you want to get a good multipublication list display
        $publicationContent['multipage']       = True;
        $publicationContent['currentpage']     = $page;
        $publicationContent['multipageprefix'] = 'authors/embed/'.$author_id.'/'.$order.'/';
    }    
    $include_synonyms = false;
    if ($author->hasSynonyms()) $include_synonyms = true;
    $publicationContent['publications'] = $this->publication_db->getForAuthor($author_id,$order,-1,$include_synonyms);
    $publicationContent['order'] = $order;
    $publicationContent['noBookmarkList'] = True;

    
    //get output
    $output = $this->load->view('authors/embed',      $authorContent, true);
    
    if ($publicationContent['publications'] != null) {
      $output .= $this->load->view('publications/list', $publicationContent, true);
    }
    
    //set output
    $this->output->set_output($output);  
  }  

  /**
		  authors/embedClean

		  A controller that should return only the basic contents of the single author publication listing.
		  Can be used to embed a few of your own publications into a completely different page. Note that in that case
		  you need to
		  a) have anonymous access enabled
		  b) have the publications of the requested author that you want to show embedded in another page set as being public
		     (that concerns the access levels)
		  c) from this surrounding page (presumably your own web page?), somehow read
		     the file <aigaion2_root>/index.php/authors/embed/<author_id>/type
		     and past the resulting html on screen :)
		     (the php function readfile might works on your server)
		  d) have a proper stylesheet included in that surrounding page (?)

		  takes as arguments:
		    3rd: author_id
		    4rth: sort order
		    
		  Contribution by {\O}yvind
		  */
		  function embedClean()
		  {
		    //retrieve author ID
		    $author_id   = $this->uri->segment(3);
		    $order   = $this->uri->segment(4,'year');
		    if (!in_array($order,array('year','type','recent','title','author','msc'))) {
		      $order='year';
		    }

		    //load author
		    $author = $this->author_db->getByID($author_id);
		    if ($author == null)
		    {
		      appendErrorMessage(__('View Author').": ".__('non-existing id passed').".<br/>");
		      redirect('');
		    }

		    $this->load->helper('publication');

		    $userlogin = getUserLogin();

		    //set header data
		    //$header ['title']       = $author->getName();
		    //$header ['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');

		    //set data
		    $authorContent['author']            = $author;
		    $publicationContent['header']       = sprintf(__('Publications of %s'), $author->getName());
		    switch ($order) {
        case 'type':
            $publicationContent['header']          = sprintf(__('Publications of %s sorted by journal and type'), $author->getName());
            break;
        case 'recent':
            $publicationContent['header']          = sprintf(__('Publications of %s sorted by recency'), $author->getName());
            break;
        case 'title':
            $publicationContent['header']          = sprintf(__('Publications of %s sorted by title'), $author->getName());
            break;
        case 'author':
            $publicationContent['header']          = sprintf(__('Publications of %s sorted by first author'), $author->getName());
            break;
        }

        $include_synonyms = false;
        if ($author->hasSynonyms()) $include_synonyms = true;
		    $publicationContent['publications'] = $this->publication_db->getForAuthor($author_id,$order,-1,$include_synonyms);
		    $publicationContent['order'] = $order;

				$output = __("No publications found.");
		    if ($publicationContent['publications'] != null) {
		      $output = $this->load->view('publications/listClean', $publicationContent, true);
		    }
		    //set output
		    $this->output->set_output($output);
	  }
      
  //Calls an empty author edit form
  function add()
  {
    $this->edit();
  }

  /** add a new author as synonym for the given author_id (3rd segment)*/
  function addsynonym()
  {
    $prim_id   = $this->uri->segment(3,'0');
    $this->edit("",$prim_id);
  }
    
  //edit() - Call author edit form. When no ID is given: new authorform
  function edit($author = "", $synonym_of='0')
  {
    if (is_numeric($author))
    {
      $author_id  = $author;
      $author     = $this->author_db->getByID($author_id);
      if ($author==null)
      {
        appendErrorMessage(__("Edit author").": ".__('non-existing id passed').".<br/>");
        redirect('');
      }
      $author->getCustomFields();
      
      //set header data
      $edit_type = "edit";
    }
    else if (empty($author))
    {
      //php4 compatiblity: new $this->author won't work
      $author     = $this->author;
      $edit_type  = "new";
    }
    else
    {
      //there was a author post, retrieve the edit type from the post.
      $edit_type = $this->input->post('edit_type');
    }
    if ($edit_type=="new" && $synonym_of != '0')
    {
      $prim = $this->author_db->getByID($synonym_of);
      if ($prim->synonym_of!='0') $synonym_of = $prim->synonym_of;
      $author->synonym_of = $synonym_of;
    }
    $userlogin = getUserLogin();
    if (!$userlogin->hasRights('publication_edit'))
    {
      appendErrorMessage(__('Edit author').": ".__('insufficient rights').".<br/>");
      redirect('');
    }

    $header ['title']       = sprintf(__("%s author"), $edit_type);
    $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js');
    $content['edit_type']   = $edit_type;
    $content['author']      = $author;
    
    //get output
    $output  = $this->load->view('header',        $header,  true);
    $output .= $this->load->view('authors/edit',  $content, true);
    $output .= $this->load->view('footer',        '',       true);
    
    //set output
    $this->output->set_output($output);
  }
  
  //merge() - Call author merge form. 
  function merge()
  {
    $author_id = $this->uri->segment(3);
    $simauthor_id = $this->uri->segment(4);
    $author = $this->author_db->getByID($author_id);
    $simauthor = $this->author_db->getByID($simauthor_id);
    if ($author==null || $simauthor==null) {
        appendErrorMessage(__("Cannot merge authors").": ".__('missing parameters').".<br/>");
        redirect('');
    }
    

    $userlogin = getUserLogin();
    if (!$userlogin->hasRights('publication_edit'))
    {
      appendErrorMessage(__('Cannot merge authors').": ".__('insufficient rights').'.<br/>');
      redirect('');
    }

    if (($simauthor->synonym_of != '0') && ($simauthor->synonym_of != $author->author_id))
    {
      appendErrorMessage(__("An author synonym can only be merged with the corresponding primary author").'.<br/>');
      return;
    }
    if ($author->synonym_of != '0')
    {
        appendErrorMessage(__("You cannot merge authors with a synonym author as target").'.<br/>');
        redirect('');
    }

    
    $header ['title']       = __("Authors").": ".__('Merge');
    $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js');
    $content['author']      = $author;
    $content['simauthor']      = $simauthor;
    
    //get output
    $output  = $this->load->view('header',        $header,  true);
    $output .= $this->load->view('authors/merge',  $content, true);
    $output .= $this->load->view('footer',        '',       true);
    
    //set output
    $this->output->set_output($output);
  }  
  
  //mergecommit() - Do merge commit
  function mergecommit()
  {
    $author = $this->author_db->getFromPost();
    $simauthor_id = $this->input->post('simauthor_id');
    $simauthor = $this->author_db->getByID($simauthor_id);
    if ($author==null || $simauthor==null) {
        appendErrorMessage(__("Cannot merge authors").": ".__('missing parameters').".<br/>");
        redirect('');
    }

    $userlogin = getUserLogin();
    if (!$userlogin->hasRights('publication_edit'))
    {
      appendErrorMessage(__('Cannot merge authors').": ".__('insufficient rights').'.<br/>');
      redirect('');
    }
    
    //so... actually, we should now test whether the user has edit access on all involved publications!!!
    $author->update(); //this updates the new name info into the author
    $author->merge($simauthor_id);
    redirect ('authors/show/'.$author->author_id);
  }  
  
	/** 
	authors/delete
	
	Entry point for deleting an author.
	Depending on whether 'commit' is specified in the url, confirmation may be requested before actually
	deleting. 
	
	Fails with error message when one of:
	    delete requested for non-existing author
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: author_id, the id of the to-be-deleted-author
	    4th: if the 4th segment is the string 'commit', no confirmation is requested.
	         if not, a confirmation form is shown; upon choosing 'confirm' this same controller will be 
	         called with 'commit' specified
	         
    Returns:
        A full HTML page showing a 'request confirmation' form for the delete action, if no 'commit' was specified
        Redirects somewhere (?) after deleting, if 'commit' was specified
	*/
	function delete()
	{
	    $author_id = $this->uri->segment(3);
	    $author = $this->author_db->getByID($author_id);
	    $commit = $this->uri->segment(4,'');

	    if ($author==null) {
	        appendErrorMessage(__('Delete author').": ".__('author does not exist').'.<br/>');
	        redirect('');
	    }

        $userlogin  = getUserLogin();
        if (    (!$userlogin->hasRights('publication_edit'))
            ) 
        {
	        appendErrorMessage(__('Delete author').": ".__('insufficient rights').'.<br/>');
	        redirect('');
        }

        if ($commit=='commit') {
            //do delete, redirect somewhere
            $author->delete();
            redirect('authors');
        } else {
            //get output: a full web page with a 'confirm delete' form
            $headerdata = array();
            $headerdata['title'] = __('Author').": ".__('delete');
            
            $output = $this->load->view('header', $headerdata, true);
    
            $output .= $this->load->view('authors/delete',
                                         array('author'=>$author),  
                                         true);
            
            $output .= $this->load->view('footer','', true);
    
            //set output
            $this->output->set_output($output);	
        }
    }
  
  //commit() - Commit the posted author to the database
  function commit()
  {
    $userlogin = getUserLogin();
    if (!$userlogin->hasRights('publication_edit'))
    {
      appendErrorMessage(__('Edit author').": ".__('insufficient rights').'.<br/>');
      redirect('');
    }

    
    $author = $this->author_db->getFromPost();
    //check the submit type, if 'type_change', we redirect to the edit form
    $submit_type = $this->input->post('submit_type');
    
    if ($this->author_db->validate($author))
    {
      $bReview = false;
      if ($submit_type != 'review')
      {

        //review author for similar authors
        list($review['author'],$similar_ids)   = $this->author_db->review(array($author));
        
        if ($review['author'] != null)
        {
          $bReview = true;
          $this->review($author, $review);
        }
      }
      if (!$bReview)
      {
        //do actual commit, depending on the edit_type, choose add or update
        //
        $edit_type = $this->input->post('edit_type');
        if ($edit_type == 'new') {
          //note: the author_db review method will not give an error if ONE EXACT MATCH EXISTS
          //so we should still check that here
          if ($this->author_db->getByExactName($author->firstname,$author->von,$author->surname,$author->jr) != null) {
            appendMessage(sprintf(__('Author "%s" already exists in the database.'), $author->getName('lvf')).'<br/>');
            redirect('authors/add');
          } else {
            $author = $this->author_db->add($author);
          }
        } else
          $author = $this->author_db->update($author);
              
        //show publication
        redirect('authors/show/'.$author->author_id);
      }
    }
    else //there were validation errors
    {
      //edit the publication once again
      $this->edit($author);
    }
  }

  function review($author, $review_message)
  {
    $userlogin = getUserLogin();
    if (!$userlogin->hasRights('publication_edit'))
    {
      appendErrorMessage(__('Edit author').": ".__('insufficient rights').'.<br/>');
      redirect('');
    }

    $header ['title']       = __("Review author");
    $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js');
    $content['edit_type']   = $this->input->post('edit_type');
    $content['author']      = $author;
    $content['review']      = $review_message;
    
    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('authors/edit',        $content, true);
    $output .= $this->load->view('footer',              '',       true);
    
    //set output
    $this->output->set_output($output);
  }

  /** set primary.
  3rd segment is author_id
  */
  function setPrimary()
  {
    $author_id = $this->uri->segment(3,-1);
    $author = $this->author_db->getByID($author_id);
    if ($author == null)
    {
        appendErrorMessage(__('Set author as primary').': '.__('non-existing id passed').'.<br/>');
        redirect('');
    }
    $author->setPrimary();
    redirect('authors/show/'.$author_id);
  }

  function fortopic()
  {
    $this->load->helper('form');

    $topic_id = $this->uri->segment(3,-1);
    $config = array();
    $topic = $this->topic_db->getByID($topic_id,$config);

    if ($topic==null) {
        appendErrorMessage(__('Authors for topic').': '.__('non-existing id passed').'.<br/>');
        redirect('');
    }
    
    $authorList = $topic->getAuthors();
    
    
    
    //set header data
    $header ['title']         = __('Authors');
    $header ['javascripts']   = array('prototype.js');
    $content['header']        = sprintf(__("Authors for topic %s"),anchor('topics/single/'.$topic->topic_id,$topic->name));
    $content['authorlist']    = $authorList;
    
    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('authors/list',        $content, true);
    
    $output .= $this->load->view('footer',              '',       true);
    
    //set output
    $this->output->set_output($output);

  }
  
  function _authorlist()
  {
    $this->load->helper('form');
    
    //$authorList = $this->author_db->getAllAuthors();
    $authorList = $this->author_db->getAllVisibleAuthors();
    
    
    //set header data
    $header ['title']         = __('Authors');
    $header ['javascripts']   = array('prototype.js');
    $content['header']        = __("All authors in the database");
    $content['authorlist']    = $authorList;
    $content['searchbox']     = True;
    
    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('authors/list',        $content, true);
    $output .= $this->load->view('footer',              '',       true);
    
    //set output
    $this->output->set_output($output);

  }
  
  function searchlist()
  {
    $author_search = $this->input->post('author_search');
    if ($author_search) // user pressed show, so redirect to single author page
    {
      //$authorList     = $this->author_db->getAuthorsLike($author_search);
      $authorList     = $this->author_db->getVisibleAuthorsLike($author_search);
      if (sizeof($authorList) > 0)
      {
        $this->show($authorList[0]->author_id);
      }     
    }
    else
    {
      $author_search  = $this->uri->segment(3);
      //$authorList     = $this->author_db->getAuthorsLike($author_search);
      $authorList     = $this->author_db->getVisibleAuthorsLike($author_search);
      echo $this->load->view('authors/list_items', array('authorlist' => $authorList), true);
    }
  }
  
  function li_authors($fieldname = "")
  {
    if ($fieldname == "")
      $fieldname = 'authors';
      
    $author = $this->input->post($fieldname);
    
    if ($author != "")
    {
      $authors = $this->author_db->getAuthorsLike($author);
      echo $this->load->view('authors/li_authors', array('authors' => $authors), true);
    }
  }
  
  /** create a new author from the text in the post value 'authorname'.
  Used e.g. for quick author creation in the publication edit page.
  Returns ID */
  function quickcreate() 
  {
    require_once(APPPATH."include/utf8/trim.php");
    $this->load->helper('encode');
    $name = $this->input->post('authorname');
    if (utf8_trim($name)=='') 
    {
        echo '';
        return;
    }
    $authors_array    = $this->parsecreators->parse($name);
    if (count($authors_array)>0) {
      $existingauthor = $this->author_db->getByExactName($authors_array[0]['firstname'], $authors_array[0]['von'], $authors_array[0]['surname'], $authors_array[0]['jr']);
      if ($existingauthor==null) {
        $newauthor = $this->author_db->setByName($authors_array[0]['firstname'], $authors_array[0]['von'], $authors_array[0]['surname'], $authors_array[0]['jr']);
        $result = $this->author_db->add($newauthor);
        echo $result->author_id;
      } else {
        echo '';
      }
    } else {
      echo '';
    }
  }
  
  /**
		author/exportEmail

		Sends the publications for the selected author to the specified email address(es).

		Fails with error message when one of:
			no topic selected

		Parameters passed via POST segments:
			email_pdf
			email_bibtex
			email_ris
			email_address
			email_formatted

			topic_id 					by url segment 3
			recipientaddress 	by url segment 4 (OPTIONAL)


		*/
		function exportEmail()
		{
  	  $userlogin = getUserLogin();
			if (!$userlogin->hasRights('export_email')) {
  	    appendErrorMessage(__('Export through email').': '.__('insufficient rights').'.<br/>');
  	    redirect('');
      }
      $this->load->library('email_export');
			$email_pdf = $this->input->post('email_pdf');
			$email_bibtex = $this->input->post('email_bibtex');
			$email_ris = $this->input->post('email_ris');
			$email_address = $this->input->post('email_address');
			$email_formatted = $this->input->post('email_formatted');
			$order='year';

			$recipientaddress   = $this->uri->segment(4,-1);
			$author_id   = $this->uri->segment(3,-1);
			$publications = $this->publication_db->getForAuthor($author_id);


			if (!isset($author_id))
			{
				appendErrorMessage(__("No author selected for export").".<br />");
				redirect('');
			}




			/*
				IF the recipient's address is missing or if none of the data formats are selected THEN show the format selection form.
			*/
			if(!(($email_pdf !='' || $email_bibtex !='' || $email_ris!='' || $email_formatted!='') && $email_address != ''))
			{
				$header ['title']       = __("Select export format");
				$header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js','externallinks.js');

				$content['attachmentsize']  = $this->email_export->attachmentSize($publications);
				$content['controller']	='authors/exportEmail/'.$author_id;
				if(isset($recipientaddress))
				{
					$replace = array("AROBA", "KOMMA");
					$with   = array("@", ",");
					$content['recipientaddress'] = str_replace($replace, $with, $recipientaddress);;
				}

				//get output
				$output = $this->load->view('header',        $header,  true);
				$output .= $this->load->view('export/chooseformatEmail', $content, true);
				$output .= $this->load->view('footer',        '',       true);

				//set output
				$this->output->set_output($output);
				return;
			}
			/*
				ELSE process the request and send the email.
			*/
			else
			{
				//get output
				$this->load->helper('publication');

				$headerdata = array();
				$headerdata['title'] = __('Author export');
				$headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
				$headerdata['exportCommand']    = 'author/exportEmail';
				$headerdata['exportName']    = __('Export author');

				$content['header']          = __('Export by email');
				$output = $this->load->view('header', $headerdata, true);
				$content['publications']    = $publications;

				$content['order'] = $order;




				$messageBody = __('Export from Aigaion');

				if($email_formatted || $email_bibtex)
				{
					$this->publication_db->enforceMerge = True;
					$publicationMap = $this->publication_db->getForAuthorAsMap($author_id,true);
					$splitpubs = $this->publication_db->resolveXref($publicationMap,false);
					$pubs = $splitpubs[0];
					$xrefpubs = $splitpubs[1];

					$exportdata['nonxrefs'] = $pubs;
					$exportdata['xrefs']    = $xrefpubs;
					$exportdata['header']   = __('Exported for author');
					$exportdata['exportEmail']   = true;
				}


				/*
					FORMATTED text is added first. HTML format is selected because this gave nice readable text without having to change or make any views.
				*/
				if($email_formatted)
				{
					$messageBody .= "\n";
					$messageBody .= __('Formatted');
					$messageBody .= "\n";

					$exportdata['format'] = 'html';
					$exportdata['sort'] = $this->input->post('sort');
					$exportdata['style'] = $this->input->post('style');
					$messageBody .= strip_tags($this->load->view('export/'.'formattedEmail', $exportdata, True));
				}

				/*
					BIBTEX added.
				*/
				if($email_bibtex)
				{
					$messageBody .= "\n";
					$messageBody .= 'BibTex';
					$messageBody .= "\n";
					$messageBody .= strip_tags($this->load->view('export/'.'bibtexEmail', $exportdata, True));
				}
				/*
					RIS added.
				*/
				if($email_ris)
				{
					$messageBody .= "\n";
					$messageBody .= 'RIS';
					$messageBody .= "\n";

					$this->publication_db->suppressMerge = False;
					$publicationMap = $this->publication_db->getForAuthorAsMap($author_id,true);
					$splitpubs = $this->publication_db->resolveXref($publicationMap,false);
					$pubs = $splitpubs[0];
					$xrefpubs = $splitpubs[1];

					#send to right export view
					$exportdata['nonxrefs'] = $pubs;
					$exportdata['xrefs']    = $xrefpubs;
					$exportdata['header']   = __('Exported for author');
					$exportdata['exportEmail']   = true;

					$messageBody .= strip_tags($this->load->view('export/'.'risEmail', $exportdata, True));

				}


				/*
					If PDFs are not selected the publication array is removed and no attachments will be added.
				*/
				if(!$email_pdf)
				{
					$publications = array();
				}

				/*
					Sending MAIL.
				*/
				if($this->email_export->sendEmail($email_address, $messageBody, $publications))
				{
					$output .= __('Mail sent successfully');
				}
				else
				{
					appendErrorMessage(__('Something went wrong when exporting the publications. Did you input a correct email address?').'<br />');
					redirect('');
				}

				$output .= $this->load->view('footer','', true);

				//set output
				$this->output->set_output($output);
			}
		}
}
?>