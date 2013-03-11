<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Topics extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
	}
	
	/** Pass control to the topics/browse/ controller */
	function index()
	{
	  $this->browse();
	}

    /** Simple browse page for Topics. 
        This controller returns a full web page of the subscribed topics
        Third parameter selects root topic_id for tree (default:1) */
	function browse()
	{
	    $root_id = $this->uri->segment(3,1);
	    
	    //no rights check here: anyone can (try) to browse topics (though not all topics may be visible)
        //get output
        $headerdata = array();
        $headerdata['title'] = __('Browse topic tree');
        $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js','externallinks.js');
        
        $output = $this->load->view('header', $headerdata, true);
        
        $userlogin = getUserLogin();
        $user = $this->user_db->getByID($userlogin->userId());
        $config = array('onlyIfUserSubscribed'=>True,
                         'flagCollapsed'=>True,
                         'user'=>$user,
                         'includeGroupSubscriptions'=>True
                        );
        $root = $this->topic_db->getByID($root_id, $config);
        if ($root == null) {
            appendErrorMessage( __("Browse topics").": ".__("non-existing id passed").".<br/>");
            redirect('');
        }
        $output .= "<div style='border:1px solid black;padding:0.2em;float:right;clear:right;'>"
                    .$this->load->view('site/stats',
                                      array(),  
                                      true)."</div>\n";
        $this->load->vars(array('subviews'  => array('topics/maintreerow'=>array('useCollapseCallback'=>True))));
        $output .= "<div id='topictree-holder'>\n<ul class='topictree-list'>\n"
                    .$this->load->view('topics/tree',
                                      array('topics'   => $root->getChildren(),
                                            'showroot'  => True,
                                            'depth'     => -1
                                            ),  
                                      true)."</ul>\n</div>\n";
        
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}
    /** Simple browse page for Topics. 
        This controller returns a full web page of ALL available topics
        Third parameter selects root topic_id for tree (default:1) */
	function all()
	{
	    $root_id = $this->uri->segment(3,1);
	    
	    //no rights check here: anyone can (try) to browse topics (though not all topics may be visible)
        //get output
        $headerdata = array();
        $headerdata['title'] = __('Browse topic tree').' ('.__('include all topics').')';
        $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js','externallinks.js');
        
        $output = $this->load->view('header', $headerdata, true);
        
        $userlogin = getUserLogin();
        $user = $this->user_db->getByID($userlogin->userId());
        $config = array('onlyIfUserSubscribed'=>False,
                         'flagCollapsed'=>True,
                         'user'=>$user,
                         'includeGroupSubscriptions'=>True
                        );
        $root = $this->topic_db->getByID($root_id, $config);
        if ($root == null) {
            appendErrorMessage( __("Browse topics").": ".__("non-existing id passed").".<br/>");
            redirect('');
        }
        $output .= "<div style='border:1px solid black;padding:0.2em;float:right;clear:right;'>"
                    .$this->load->view('site/stats',
                                      array(),  
                                      true)."</div>\n";
        $this->load->vars(array('subviews'  => array('topics/maintreerow'=>array('useCollapseCallback'=>True))));
        $output .= "<div id='topictree-holder'>\n<ul class='topictree-list'>\n"
                    .$this->load->view('topics/tree',
                                      array('topics'   => $root->getChildren(),
                                            'showroot'  => True,
                                            'depth'     => -1
                                            ),  
                                      true)."</ul>\n</div>\n";
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}
	/** 
	topics/delete
	
	Entry point for deleting a topic.
	Depending on whether 'commit' is specified in the url, confirmation may be requested before actually
	deleting. 
	
	Fails with error message when one of:
	    delete requested for non-existing topic
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: topic_id, the id of the to-be-deleted-topic
	    4th: if the 4th segment is the string 'commit', no confirmation is requested.
	         if not, a confirmation form is shown; upon choosing 'confirm' this same controller will be 
	         called with 'commit' specified
	         
    Returns:
        A full HTML page showing a 'request confirmation' form for the delete action, if no 'commit' was specified
        Redirects somewhere (?) after deleting, if 'commit' was specified
	*/
	function delete()
	{
	    $topic_id = $this->uri->segment(3);
        $config=array();
	    $topic = $this->topic_db->getByID($topic_id,$config);
	    $commit = $this->uri->segment(4,'');

	    if ($topic==null) {
	        appendErrorMessage(__('Delete topic').': '.__('non-existing id passed').'.<br/>');
	        redirect('');
	    }

	    //besides the rights needed to READ this topic, checked by topic_db->getByID, we need to check:
	    //edit_access_level and the user edit rights
            $userlogin  = getUserLogin();
            $user       = $this->user_db->getByID($userlogin->userID());
            if (    (!$userlogin->hasRights('topic_edit'))
             || 
                !$this->accesslevels_lib->canEditObject($topic)
            ) 
        {
	        appendErrorMessage(__('Delete topic').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }

        if ($commit=='commit') {
            //do delete, redirect somewhere
            $topic->delete();
            redirect('');
        } else {
            //get output
            $headerdata = array();
            $headerdata['title'] = __('Delete topic');
            $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js','externallinks.js');
            
            $output = $this->load->view('header', $headerdata, true);
    
            $output .= $this->load->view('topics/delete',
                                          array('topic'=>$topic),  
                                          true);
            
            $output .= $this->load->view('footer','', true);
    
            //set output
            $this->output->set_output($output);
        }
    }
    
	/** Entrypoint for adding a topic. Shows the necessary form. */
	function add()
	{
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="errormessage">'.__('Changes not committed').': ', '</div>');
        $parent_id = $this->uri->segment(3,-1);
        $config = array();
        $parent = $this->topic_db->getByID($parent_id,$config);
	    //edit_access_level and the user edit rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('topic_edit'))
            ) 
        {
	        appendErrorMessage(__('Add topic').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }
        
        //get output
        $headerdata = array();
        $headerdata['title'] = __('Add topic');
        $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js','externallinks.js');
        
        $output  = $this->load->view('header', $headerdata, true);
        $output .= $this->load->view('topics/edit' , array('parent'=>$parent),  true);
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
    }
    
	/** Entrypoint for editing a category. Shows the necessary form. */
	function edit()
	{
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="errormessage">'.__('Changes not committed').': ', '</div>');

	    $topic_id = $this->uri->segment(3,1);
	    if ($topic_id==1) {
	        redirect('topics/browse');
	    }
        $config=array();
      $topic = $this->topic_db->getByID($topic_id,$config);
      $topic->getCustomFields();

	    if ($topic==null) {
	        appendErrorMessage(__("Edit topic").": ".__("non-existing id passed").".<br/>");
	        redirect('');
	    }
        	    

	    //besides the rights needed to READ this topic, checked by topic_db->getByID, we need to check:
	    //edit_access_level and the user edit rights
        $userlogin  = getUserLogin();
        $user       = $this->user_db->getByID($userlogin->userID());
    
        if (    (!$userlogin->hasRights('topic_edit'))
             || 
                !$this->accesslevels_lib->canEditObject($topic)
            ) 
        {
	        appendErrorMessage(__('Edit topic').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }

        //get output
        $headerdata = array();
        $headerdata['title'] = __('Edit topic');
        $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js','externallinks.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output  .= $this->load->view('topics/edit' , array('topic'=>$topic),  true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);

    }
    
    /** Simple view page for single topic. 
        This controller returns a full web page.
        Third parameter selects topic_id (default:1)
        or topic name path... for the latter, also see libraries/topic_db#getTopicIDFromNames()
        If topic 1 is chosen, user is redirected to browse/ controller */
	function single()
	{
	    	$topic_structure = array();
	    	$url_segment = 3;
	    	$topic_name = $this->uri->segment($url_segment,1);

	    	//Checks if the controller is given a topic_id or a topic structure
	    	if(is_numeric($topic_name))
	    	{
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
						appendErrorMessage(__("Topic structure is not unique in Aigaion.")." <br/>");
						redirect('');
					}
					else
					{
						appendErrorMessage(__("Topic structure does not exist in Aigaion.")." <br/>");
						redirect('');
					}
				}

				//if ($topic_id==1) {
					//redirect('topics/browse');
				//}

				$order   = $this->uri->segment($url_segment+1,'year');
				if (!in_array($order,array('year','type','recent','title','author'))) {
					$order='year';
				}
				$page   = $this->uri->segment($url_segment+2,0);

	    if ($topic_id==1) {
	        redirect('topics/browse');
	    }
        $config=array();
        $topic = $this->topic_db->getByID($topic_id,$config);
        $userlogin=getUserLogin();
	    if ($topic==null) {
	        appendErrorMessage(__('Show topic').': '.__('non-existing id passed').'.<br/>');
	        redirect('topics/browse');
	    }
	    
	    //no additional rights check beyond those in the topic_db->getbyID, as anyone can view topics as long
	    // as he has the right access levels
	    
	      $this->load->helper('publication');
    
        //get output
        $headerdata                 = array();
        $headerdata['title']        = __('Topic').': '.$topic->name;
        $headerdata['javascripts']  = array('tree.js','prototype.js','scriptaculous.js','builder.js','externallinks.js');//, 'publicationlists.js');
        $headerdata['sortPrefix']        = 'topics/single/'.$topic->topic_id.'/';
        $headerdata['exportCommand']        = 'export/topic/'.$topic->topic_id.'/';
        $headerdata['exportName']    = __('Export topic');
        
        $content['topic']           = $topic;
        $content['header']          = sprintf(__("Publications for topic \"%s\""),$topic->name);
        switch ($order) {
            case 'type':
                $content['header']          = sprintf(__("Publications for topic \"%s\""),$topic->name).' '.__('sorted by journal and type');
                break;
            case 'recent':
                $content['header']          = sprintf(__("Publications for topic \"%s\""),$topic->name).' '.__('sorted by recency');
                break;
            case 'title':
                $content['header']          = sprintf(__("Publications for topic \"%s\""),$topic->name).' '.__('sorted by title');
                break;
            case 'author':
                $content['header']          = sprintf(__("Publications for topic \"%s\""),$topic->name).' '.__('sorted by first author');
                break;
        }
        
        $output = $this->load->view('header', $headerdata, true);

        $output  .= $this->load->view('topics/full', $content,  true);
        
        //get keyword list
        if ($userlogin->getPreference('liststyle')>0) {
            //set these parameters when you want to get a good multipublication list display
            $content['multipage']       = True;
            $content['pubCount']        = $this->topic_db->getVisiblePublicationCountForTopic($topic_id);
            $content['currentpage']     = $page;
            $content['multipageprefix'] = 'topics/single/'.$topic_id.'/'.$order.'/';
        }
        $content['publications']    = $this->publication_db->getVisibleForTopic($topic_id,$order,$page);
        $content['order'] = $order;
        
        $output .= $this->load->view('publications/list', $content, true);
        
        $output .= $this->load->view('footer','', true);
        
        //set output
        $this->output->set_output($output);
	}    
	/*
		This controller takes the topic either (1) as url containing the names of the topics and subtopics
		as for instance [aigion_root]/index.php/topics/embedClean/top/subtopic_1/.../subtopic_n
		or (2) the topicID, for instance: [aigion_root]/index.php/topics/embedClean/18

		Topics CANNOT be named: year, type, recent, title, author, msc or any number. The controller could fail if these topic names are present.

		The method topic_db->getTopicID is used to translate this url into a unique topic ID.
		
		Contribution by {\O}yvind
	*/
	function embedClean()
	{
	    	$topic_structure = array();
	    	$url_segment = 3;
	    	$topic_name = $this->uri->segment($url_segment,1);

	    	//Checks if the controller is given a topic_id or a topic structure
	    	if(is_numeric($topic_name))
	    	{
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
						exit(__("Topic structure is not unique in Aigaion.")." <br/>");
					}
					else
					{
						exit(__("Topic structure does not exist in Aigaion.")." <br/>");
					}
				}

				//if ($topic_id==1) {
					//redirect('topics/browse');
				//}

				$order   = $this->uri->segment($url_segment,'year');
				if (!in_array($order,array('year','type','recent','title','author'))) {
					$order='year';
				}
				$page   = $this->uri->segment($url_segment+1,0);
				$config=array();
				$topic = $this->topic_db->getByID($topic_id,$config);
				$userlogin=getUserLogin();
				if ($topic==null) {
					exit(__("Embed topic").": ".__("non-existing id passed").".<br/>");
				}

				$this->load->helper('publication');

				$topiccontent['topic']           = $topic;
				$content['header']          = sprintf(__("Publications for topic \"%s\""),$topic->name);
				switch ($order) {
						case 'type':
								$content['header']          = sprintf(__("Publications for topic \"%s\""),$topic->name).' '.__('sorted by journal and type');
								break;
						case 'recent':
								$content['header']          = sprintf(__("Publications for topic \"%s\""),$topic->name).' '.__('sorted by recency');
								break;
						case 'title':
								$content['header']          = sprintf(__("Publications for topic \"%s\""),$topic->name).' '.__('sorted by title');
								break;
						case 'author':
								$content['header']          = sprintf(__("Publications for topic \"%s\""),$topic->name).' '.__('sorted by first author');
								break;
				}
				$content['publications']    = $this->publication_db->getVisibleForTopic($topic_id,$order);
				$content['order'] = $order;


				$output = $this->load->view('topics/clean', $topiccontent, true);
				$output .= $this->load->view('publications/listClean', $content, true);

				$this->output->set_output($output);

	}    
    
    /**
    topics/commit
    
	Fails with error message when one of:
	    edit-commit requested for non-existing topic
	    insufficient user rights
	    
	Parameters passed via POST:
	    action = (add|edit)
	    topic_id
	    name
	    description
	    url
	    parent_id
	         
    Redirects to somewhere (?) if the commit was successfull
    Redirects to the edit or add form if the validation of the form values failed
    */
    function commit() {
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="errormessage">'.__('Changes not committed').': ', '</div>');

        //get data from POST
        $topic = $this->topic_db->getFromPost();
        
        //check if fail needed: was all data present in POST?
        if ($topic == null) {
            appendErrorMessage(__("Commit topic").": ".__("no data to commit").".<br/>");
            redirect ('');
        }

//             the access level checks are of course not tested here,
//             but in the commit action, as the client can have sent 'wrong' form data        
        
        //validate form values; 
        //validation rules: 
        //  -no topic with the same name and a different ID can exist
        //  -name is required (non-empty)
    	$this->form_validation->set_rules(array( 'name' => 'required'
                                           )
                                     );
    	
    		
    	if ($this->form_validation->run() == FALSE) {
            //return to add/edit form if validation failed
            //get output
            $headerdata = array();
            $headerdata['title'] = __('Topic');
            $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js','externallinks.js');
            
            $output = $this->load->view('header', $headerdata, true);
    
            $output .= $this->load->view('topics/edit',
                                          array('topic'         => $topic,
                                                'action'        => $this->input->post('action')),
                                          true);
            
            $output .= $this->load->view('footer','', true);
    
            //set output
            $this->output->set_output($output);
            
        } else {    
            //if validation was successfull: add or change.
            $success = False;
            if ($this->input->post('action') == 'edit') {
                //do edit
                $success = $topic->update();
            } else {
                //do add
                $success = $topic->add();
            }
            if (!$success) {
                //this is quite unexpected, I think this should not happen if we have no bugs.
                appendErrorMessage(__("Commit topic").": ".__("an error occurred").". ".__("Please contact your Aigaion administrator.")."<br/>");
                redirect('topics/single/'.$topic->topic_id);
            }
            //redirect somewhere if commit was successfull
            redirect('topics/single/'.$topic->topic_id);

        }
        
    }
    
    /**
    topics/collapse
    
    Collapses or expands a topic for the logged user. Is normally called async, without processing the
    returned partial, by clicking one of the collapse or expand buttons in a topic tree rendered by 
    subview 'maintreerow' with argument 'useCollapseCallback'=>True
    
	Fails with error message when one of:
	    collapse requested for non-existing topic
	    insufficient user rights
	    
	Parameters passed via URL:
	    3rd segment: topic_id
	    4rd segment: collapse status (0|1), default 1 (collapsed)
	         
    Returns a partial html fragment:
        an empty div if successful
        an div containing an error message, otherwise
    
    */
    function collapse() {    
        $topic_id = $this->uri->segment(3,-1);
        $collapse = $this->uri->segment(4,1);
        
        $config=array();
        $topic = $this->topic_db->getByID($topic_id,$config);
        
        if ($topic == null) {
            echo "<div class='errormessage'>".__("Collapse topic").": ".__("non-existing id passed").".</div>";
        } else {
            //do collapse
            if ($collapse==1) {
                $topic->collapse();
            } else {
                $topic->expand();
            }
    
            echo "<div/>";
        }
    }

    /**
			topics/exportEmail

			Sends the publications for the selected topic to the spesified email address(es).

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
				$topic_id   = $this->uri->segment(3,-1);
				$publications = $this->publication_db->getVisibleForTopic($topic_id);


				if (!isset($topic_id) || $topic_id == -1)
				{
					appendErrorMessage(__("Export topic").": ".__("non-existing id passed").".<br />");
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
					$content['controller']	='topics/exportEmail/'.$topic_id;
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
					$headerdata['title'] = __('Topic export');
					$headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
					$headerdata['exportCommand']    = 'topics/exportEmail/';
					$headerdata['exportName']    = __('Export topic');

					$content['header']          = __('Export by email');
					$output = $this->load->view('header', $headerdata, true);
					$content['publications']    = $publications;

					$content['order'] = $order;




					$messageBody = __('Export from Aigaion');

					if($email_formatted || $email_bibtex)
					{
						$this->publication_db->enforceMerge = True;
						$publicationMap = $this->publication_db->getForTopicAsMap($topic_id);
						$splitpubs = $this->publication_db->resolveXref($publicationMap,false);
						$pubs = $splitpubs[0];
						$xrefpubs = $splitpubs[1];

						$exportdata['nonxrefs'] = $pubs;
						$exportdata['xrefs']    = $xrefpubs;
						$exportdata['header']   = __('Exported from topic');
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
						$messageBody .= 'BibTeX';
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
						$publicationMap = $this->publication_db->getForTopicAsMap($topic_id);
						$splitpubs = $this->publication_db->resolveXref($publicationMap,false);
						$pubs = $splitpubs[0];
						$xrefpubs = $splitpubs[1];

						#send to right export view
						$exportdata['nonxrefs'] = $pubs;
						$exportdata['xrefs']    = $xrefpubs;
						$exportdata['header']   = __('Exported from topic');
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
						appendErrorMessage(__('Something went wrong when exporting the publications. Did you input a correct email address?').' <br />');
						redirect('');
					}

					$output .= $this->load->view('footer','', true);

					//set output
					$this->output->set_output($output);
				}
	}

}
?>