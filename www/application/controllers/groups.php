<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Groups extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
	}
	
	/** no default controller */
	function index()
	{
		redirect('');
	}


    
    /** 
    groups/single
    
    Entry point for viewing one group.
    
	Fails with error message when one of:
	    a non-existing group_id requested
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: group_id, the id of the group to be viewed
	         
    Returns:
        A full HTML page with all information about the group
    */
    function single()	{
	    $group_id = $this->uri->segment(3,-1);
	    $group = $this->group_db->getByID($group_id);
	    if ($group==null) {
	        appendErrorMessage(__("View group").": ".__("non-existing id passed").".<br/>");
	        redirect('');
	    }

        //no additional rights check. Only, in the view the edit links may be suppressed depending on the user rights
	    
        //get output
        $headerdata = array();
        $headerdata['title'] = __('Group');
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= $this->load->view('groups/full',
                                      array('group'   => $group),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}
	
	
    /** 
    groups/add
    
    Entry point for adding a group.
    
	Fails with error message when one of:
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    none
	         
    Returns:
        A full HTML page with an 'add group' form
    */
    function add()
	{
	    //check rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('user_edit_all'))
            ) 
        {
	        appendErrorMessage(__('Add group').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }

        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="errormessage">'.__('Changes not committed').': ', '</div>');

        //get output
        $headerdata = array();
        $headerdata['title'] = __('Group');
        $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= $this->load->view('groups/edit',
                                      array(),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}
	
    /** 
    groups/edit
    
    Entry point for editing a group.
    
	Fails with error message when one of:
	    non-existing group_id requested
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: group_id, the id of the group to be edited
	         
    Returns:
        A full HTML page with an 'edit group' form
    */
    function edit()
	{
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="errormessage">'.__('Changes not committed').': ', '</div>');

	    $group_id = $this->uri->segment(3,-1);
	    $group = $this->group_db->getByID($group_id);
	    if ($group==null) {
	        appendErrorMessage(__("Edit group").": ".__("non-existing id passed").".<br/>");
	        redirect('');
	    }
	    
	    //check user rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('user_edit_all'))
             
            ) 
        {
	        appendErrorMessage(__('Edit group').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }
	    
        //get output
        $headerdata = array();
        $headerdata['title'] = __('Group');
        $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= $this->load->view('groups/edit',
                                      array('group'=>$group),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}

	/** 
	groups/delete
	
	Entry point for deleting a group.
	Depending on whether 'commit' is specified in the url, confirmation may be requested before actually
	deleting. 
	
	Fails with error message when one of:
	    delete requested for non-existing group
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: group_id, the id of the to-be-deleted-group
	    4th: if the 4th segment is the string 'commit', no confirmation is requested.
	         if not, a confirmation form is shown; upon choosing 'confirm' this same controller will be 
	         called with 'commit' specified
	         
    Returns:
        A full HTML page showing a 'request confirmation' form for the delete action, if no 'commit' was specified
        Redirects somewhere (?) after deleting, if 'commit' was specified
	*/
	function delete()
	{
	    $group_id = $this->uri->segment(3);
	    $group = $this->group_db->getByID($group_id);
	    $commit = $this->uri->segment(4,'');

	    if ($group==null) {
	        appendErrorMessage(__('Delete group').': '.__('non-existing group specified').'.<br/>');
	        redirect('');
	    }
	    //check user rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('user_edit_all')) )
        {
	        appendErrorMessage(__('Delete group').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }


        if ($commit=='commit') {
            //do delete, redirect somewhere
            $group->delete();
            redirect('users/manage');
        } else {
            //get output
            $headerdata = array();
            $headerdata['title'] = __('Group');
            $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
            
            $output = $this->load->view('header', $headerdata, true);
    
            $output .= $this->load->view('groups/delete',
                                          array('group'=>$group),  
                                          true);
            
            $output .= $this->load->view('footer','', true);
    
            //set output
            $this->output->set_output($output);
        }
    }
    
    /**
    groups/commit
    
	Fails with error message when one of:
	    edit-commit requested for non-existing group
	    insufficient user rights
	    
	Parameters passed via POST:
	    action = (add|edit)
	    group_id
	    name
	    abbreviation
	    (assigned rightsprofiles)
	         
    Redirects to somewhere (?) if the commit was successfull
    Redirects to the edit or add form if the validation of the form values failed
    */
    function commit() {
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="errormessage">'.__('Changes not committed').': ', '</div>');

        //get data from POST
        $group = $this->group_db->getFromPost();
        
        //check if fail needed: was all data present in POST?
        if ($group == null) {
            appendErrorMEssage(__("Commit group").": ".__("no data to commit").".<br/>");
            redirect ('');
        }

	    //check user rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('user_edit_all'))
            ) 
        {
	        appendErrorMessage(__('Edit group').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }
        
        //validate form values; 
        //validation rules: 
        //  -no group with the same name and a different ID can exist
        //  -name is required (non-empty)
    	$this->form_validation->set_rules(array( 'name' => 'required'
                                           )
                                     );
    	$this->form_validation->set_fields(array( 'name' => __('Group Name')
                                           )
                                     );
    		
    	if ($this->form_validation->run() == FALSE) {
            //return to add/edit form if validation failed
            //get output
            $headerdata = array();
            $headerdata['title'] = __('Group');
            $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
            
            $output = $this->load->view('header', $headerdata, true);
    
            $output .= $this->load->view('groups/edit',
                                          array('group'         => $group,
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
                $success = $group->update();
            } else {
                //do add
                $success = $group->add();
            }
            if (!$success) {
                //this is quite unexpected, I think this should not happen if we have no bugs.
                appendErrorMessage(__("Commit group").": ".__("an error occurred").". ".__("Please contact your Aigaion administrator.")."<br/>");
                redirect ('users/manage');
            }
            //redirect somewhere if commit was successfull
            redirect('users/manage');
        }
        
    }

    
    /** 
    groups/topicreview
    
    Entry point for editing the topic subscriptions for a group
    
	Fails with error message when one of:
	    non-existing group_id requested
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: group_id of the group to be edited 
	         
    Returns:
        A full HTML page with a 'topic subscription tree'
    */
    function topicreview() {
	    $group_id = $this->uri->segment(3,-1);
	    $group = $this->group_db->getByID($group_id);
	    
	    if ($group==null) {
	        appendErrorMessage(__('Topic review').': '.__('invalid group_id specified').'.<br/>');
	        redirect('');
	    }
	    
	    //check user rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('topic_subscription'))
             ||
                (  !$userlogin->hasRights('user_edit_all') )
            ) 
        {
	        appendErrorMessage(__('Topic subscription').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }
	    
        //get output
        $headerdata = array();
        $headerdata['title'] = __('Topic subscription for groups');
        $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
        
        $output = $this->load->view('header', $headerdata, true);

        
        $config = array('user'=>$group);
        $root = $this->topic_db->getByID(1, $config);
        $this->load->vars(array('subviews'  => array('topics/groupsubscriptiontreerow'=>array('allCollapsed'=>True))));
        $output .= "<p class='header'>".sprintf(__("Topic subscription for %s"),$group->name)."</p>";
        $output .= "<div class='message'>".__("Subscribed topics are highlighted in boldface.")."<br/>".__("To subscribe or unsubscribe a topic and its descendants, click on the topic.")."</div>";
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
    groups/subscribe
    
    Susbcribes a group to a topic. Is normally called async, without processing the
    returned partial, by clicking a subscribe link in a topic tree rendered by 
    subview 'groupsubscriptiontreerow' 
    
	Fails with error message when one of:
	    susbcribe requested for non-existing topic or group
	    insufficient user rights
	    
	Parameters passed via URL:
	    3rd segment: topic_id
	    4rd segment: group_id
	         
    Returns a partial html fragment:
        an empty div if successful
        an div containing an error message, otherwise
    
    */
    function subscribe() {    
        $topic_id = $this->uri->segment(3,-1);
        $group_id = $this->uri->segment(4,-1);
        
        $group = $this->group_db->getByID($group_id);
        if ($group == null) {
            echo "<div class='errormessage'>".__("Subscribe topic").": ".__("invalid group_id specified").".</div>";
        }

	    
	    //check user rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('topic_subscription') )
             ||
                (  !$userlogin->hasRights('user_edit_all') )
            ) 
        {
	        appendErrorMessage(__('Topic subscription').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }

        $config = array('user'=>$group);

        $topic = $this->topic_db->getByID($topic_id,$config);
        
        if ($topic == null) {
            echo "<div class='errormessage'>".__("Subscribe topic").": ".__("invalid group_id specified")."</div>";
        }
        //do subscribe
        $topic->subscribeUser();

        echo "<div/>";
    }    
    
    
    /**
    groups/unsubscribe
    
    Unsusbcribes a group to a topic. Is normally called async, without processing the
    returned partial, by clicking an unsubscribe link in a topic tree rendered by 
    subview 'groupsubscriptiontreerow' 
    
	Fails with error message when one of:
	    unsusbcribe requested for non-existing topic or group
	    insufficient user rights
	    
	Parameters passed via URL:
	    3rd segment: topic_id
	    4rd segment: group_id
	         
    Returns a partial html fragment:
        an empty div if successful
        an div containing an error message, otherwise
    
    */
    function unsubscribe() {    
        $topic_id = $this->uri->segment(3,-1);
        $group_id = $this->uri->segment(4,-1);
        
        $group = $this->user_db->getByID($group_id);
        if ($group == null) {
            echo "<div class='errormessage'>".__("Unsubscribe topic").": ".__("invalid group_id specified")."</div>";
        }

	    //check user rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('topic_subscription') )
             ||
                (  !$userlogin->hasRights('user_edit_all') )
            ) 
        {
	        appendErrorMessage(__('Topic subscription').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }

        $config = array('user'=>$group);

        $topic = $this->topic_db->getByID($topic_id,$config);
        
        if ($topic == null) {
            echo "<div class='errormessage'>".__("Unsubscribe topic").": ".__("invalid group_id specified")."</div>";
        }
        //do unsubscribe
        $topic->unsubscribeUser();

        echo "<div/>";
    }    
      
}
?>