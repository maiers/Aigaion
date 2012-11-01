<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Users extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
	}
	
	/** Pass control to the users/edit/(logged user) controller */
	function index()
	{
	  $userlogin = getUserLogin();
	  redirect('users/edit/'.$userlogin->userId());
	}

    /** 
    users/manage
    
    Entry point for managing user accounts.
    
	Fails with error message when one of:
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    none
	         
    Returns:
        A full HTML page with all a list of all users and groups
    */
    function manage() {
	    //check rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('user_edit_all'))
            ) 
        {
	        appendErrorMessage(__('Manage accounts').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }
	    
	    //get output
        $headerdata = array();
        $headerdata['title'] = __('User');
        $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
        
        $output = $this->load->view('header', $headerdata, true);
        
        $output .= '<div class="optionbox">['.anchor('users/add',__('add a new user'))."]</div>\n";
        $output .= "
            <div class='header'>".__("Users")."</div>
            <ul>
            ";
        $users = $this->user_db->getAllNormalUsers();
         
        foreach ($users as $user) {
            $output .= "<li>".$this->load->view('users/summary',
                                          array('user'   => $user),  
                                          true)."</li>";
        }
 
        $users = $this->user_db->getAllExternalUsers();
         
        foreach ($users as $user) {
            $output .= "<li>".$this->load->view('users/summary',
                                          array('user'   => $user),  
                                          true)."</li>";
        }
        
        $users = $this->user_db->getAllAnonUsers();
        
        foreach ($users as $user) {
            $output .= "<li>".$this->load->view('users/summary',
                                          array('user'   => $user),  
                                          true)."</li>";
        }
        $output .= "</ul><br/><br/>\n";

        $output .= '<div class="optionbox">['.anchor('groups/add',__('add a new group'))."]</div>\n";
        $output .= "
            <div class='header'>".__("Groups")."</div>
            <ul>
            ";
        $groups = $this->group_db->getAllGroups();
        
        foreach ($groups as $group) {
            $output .= "<li>".$this->load->view('groups/summary',
                                          array('group'   => $group),  
                                          true)."</li>";
        }
        $output .= "</ul><br/><br/>\n";

        $output .= '<div class="optionbox">['.anchor('rightsprofiles/add',__('add a new rightsprofile'))."]</div>\n";

        $output .= "
            <div class='header'>".__("Rights profiles")."</div>
            <ul>
            ";
        $rightsprofiles = $this->rightsprofile_db->getAllRightsprofiles();
        
        foreach ($rightsprofiles as $rightsprofile) {
            $output .= "<li>".$this->load->view('rightsprofiles/summary',
                                          array('rightsprofile'   => $rightsprofile),  
                                          true)."</li>";
        }
        $output .= "</ul>\n";

        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);        
    }
    
    /** 
    users/single
    
    Entry point for viewing one user account.
    
	Fails with error message when one of:
	    a non-existing user_id requested
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: user_id, the id of the user to be viewed
	         
    Returns:
        A full HTML page with all information about the user
    */
    function single()	{
	    $user_id = $this->uri->segment(3,-1);
	    $user = $this->user_db->getByID($user_id);
	    if ($user==null) {
	        appendErrorMessage(__("View user").": ".__("non-existing id passed").".<br/>");
	        redirect('');
	    }

        //no additional rights check. Only, in the view the edit links may be suppressed depending on the user rights
	    	    
        //get output
        $headerdata = array();
        $headerdata['title'] = __('User');
        $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= $this->load->view('users/full',
                                      array('user'   => $user),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}
	
	
    /** 
    users/add
    
    Entry point for adding a user account.
    
	Fails with error message when one of:
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    none
	         
    Returns:
        A full HTML page with an 'add user' form
    */
    function add()
	{
	    //check rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('user_edit_all'))
            ) 
        {
	        appendErrorMessage(__('Add user').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }
	    
	    $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="errormessage">'.__('Changes not committed').': ', '</div>');

        //get output
        $headerdata = array();
        $headerdata['title'] = __('User');
        $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js','rightsprofiles.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= $this->load->view('users/edit',
                                      array(),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}
	
    /** 
    users/edit
    
    Entry point for editing a user account.
    
	Fails with error message when one of:
	    non-existing user_id requested
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: user_id, the id of the user to be edited
	         
    Returns:
        A full HTML page with an 'edit user' form
    */
    function edit()
	{
        $this->load->library('Form_validation');
        $this->form_validation->set_error_delimiters('<div class="errormessage">'.__('Changes not committed').': ', '</div>');

	    $user_id = $this->uri->segment(3,-1);
	    $user = $this->user_db->getByID($user_id);
	    if ($user==null) {
	        appendErrorMessage(__("Edit user").": ".__("non-existing id passed").".<br/>");
	        redirect('');
	    }

	    //check user rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('user_edit_all'))
             && 
                (!$userlogin->hasRights('user_edit_self') || ($userlogin->userId() != $user->user_id))
            ) 
        {
	        appendErrorMessage(__('Edit account').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }
	    	    
	    
        //get output
        $headerdata = array();
        $headerdata['title'] = __('User');
        $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js','rightsprofiles.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= $this->load->view('users/edit',
                                      array('user'=>$user),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}

  /**
  users/setpassword
	Entry point for changing password.
	Depending on whether 'commit' is specified in the url, edit form is shown or password is changed
	
	Fails with error message when one of:
	    setpassword requested for non-existing user
	    insufficient user rights
	    wrong old password
	    
	Parameters passed via URL segments:
	    3rd: user_id, the id of the to-be-deleted-user
	    4th: if the 4th segment is the string 'commit', password is set, otherwise, form is shown
	         
  */
  function setpassword()
  {
	    $user_id = $this->uri->segment(3);
	    $user = $this->user_db->getByID($user_id);
	    $commit = $this->uri->segment(4,'');

	    if ($user==null) {
	        appendErrorMessage(__('Set password').': '.__('non-existing id passed').'.<br/>');
	        redirect('');
	    }

	    //check user rights:
	    // either own pwd and user_edit_self, or user_edit_all
      $userlogin = getUserLogin();
      if (   (!($userlogin->hasRights('user_edit_self') && $user_id==$userlogin->userId()))
          &&   
             (!$userlogin->hasRights('user_edit_all')) )
      {
        appendErrorMessage(__('Set password').': '.__('insufficient rights').'.<br/>');
        redirect('');
      }

        if ($commit=='commit') {
            //-verify password for current user using userlogin (not: error+redirect)
            if (!$userlogin->checkPassword($this->input->post('password_old')))
            {
              appendErrorMessage(__('Set password').': '.__('error: please type your current password correctly').'.<br/>');
              redirect('users/setpassword/'.$user->user_id);
            } 
            //-verify matching of two new paswords (not: error+redirect)
            if ($this->input->post('password')!=$this->input->post('password_check'))
            {
              appendErrorMessage(__('Set password').': '.__('error: the two new passwords do not match').'.<br/>');
              redirect('users/setpassword/'.$user->user_id);
            } 
            //-set password and redirect to front page
            $user->setPassword($this->input->post('password'));
            redirect('');
        } else {
            //get output
            $headerdata = array();
            $headerdata['title'] = __('Set password');
            $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
            
            $output = $this->load->view('header', $headerdata, true);
    
            $output .= $this->load->view('users/setpassword',
                                          array('user'=>$user),  
                                          true);
            
            $output .= $this->load->view('footer','', true);
    
            //set output
            $this->output->set_output($output);
        }
    }
        
	/** 
	users/delete
	
	Entry point for deleting a user.
	Depending on whether 'commit' is specified in the url, confirmation may be requested before actually
	deleting. 
	
	Fails with error message when one of:
	    delete requested for non-existing user
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: user_id, the id of the to-be-deleted-user
	    4th: if the 4th segment is the string 'commit', no confirmation is requested.
	         if not, a confirmation form is shown; upon choosing 'confirm' this same controller will be 
	         called with 'commit' specified
	         
    Returns:
        A full HTML page showing a 'request confirmation' form for the delete action, if no 'commit' was specified
        Redirects somewhere (?) after deleting, if 'commit' was specified
	*/
	function delete()
	{
	    $user_id = $this->uri->segment(3);
	    $user = $this->user_db->getByID($user_id);
	    $commit = $this->uri->segment(4,'');

	    if ($user==null) {
	        appendErrorMessage(__('Delete user').': '.__('non-existing id passed').'.<br/>');
	        redirect('');
	    }

	    //check user rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('user_edit_all')) )
        {
	        appendErrorMessage(__('Delete account').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }

        if ($commit=='commit') {
            //do delete, redirect somewhere
            $user->delete();
            redirect('users/manage');
        } else {
            //get output
            $headerdata = array();
            $headerdata['title'] = __('User');
            $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
            
            $output = $this->load->view('header', $headerdata, true);
    
            $output .= $this->load->view('users/delete',
                                          array('user'=>$user),  
                                          true);
            
            $output .= $this->load->view('footer','', true);
    
            //set output
            $this->output->set_output($output);
        }
    }
    
    /**
    users/commit
    
	Fails with error message when one of:
	    edit-commit requested for non-existing user
	    insufficient user rights
	    
	Parameters passed via POST:
	    action = (add|edit)
        and a lot others...
	         
    Redirects to somewhere (?) if the commit was successfull
    Redirects to the edit or add form if the validation of the form values failed
    */
    function commit() {
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="errormessage">'.__('Changes not committed').': ', '</div>');

        //get data from POST
        $user = $this->user_db->getFromPost();

        //check if fail needed: was all data present in POST?
        if ($user == null) {
            appendErrorMEssage(__("Commit user").": ".__("no data to commit").".<br/>");
            redirect ('');
        }

	    //check user rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('user_edit_all'))
             && 
                (!$userlogin->hasRights('user_edit_self') || ($userlogin->userId() != $user->user_id))
            ) 
        {
	        appendErrorMessage(__('Edit account').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }
        
        
        //validate form values; 
        //validation rules: 
        //  -no user with the same login and a different ID can exist
        //  -login is required (non-empty)
        //  -password should match password_check
        $rules = array( 'login'    => 'required',
                        'password' => 'matches[password_check]',
                        'password_check' => 'matches[password]'
                       );
        if (    ($this->input->post('action')=='add') 
             && ($this->input->post('type')=='normal') 
             && ($this->input->post('disableaccount') != 'disableaccount')) {
            $rules['password'] = 'required';
        }
    	$this->form_validation->set_rules($rules);
    	/*
    	$this->form_validation->set_fields(array( 'login'    => __('Login Name'),
    	                                     'password' => __('First Password'),
    	                                     'password_check' => __('Second Password')
                                           )
                                     );*/ // update ci 2.2
    		
    	if ($this->form_validation->run() == FALSE) {
            //return to add/edit form if validation failed
            //get output
            $headerdata = array();
            $headerdata['title'] = __('User');
            $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
            
            $output = $this->load->view('header', $headerdata, true);
    
            $output .= $this->load->view('users/edit',
                                          array('user'         => $user,
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
                $success = $user->update();
            } else {
                //do add
                $success = $user->add();
            }
            if (!$success) {
                //this is quite unexpected, I think this should not happen if we have no bugs.
                appendErrorMessage(__("Commit user").": ".sprintf(__("an error occurred at '%s'"),$this->input->post('action')).". ".__("Please contact your Aigaion administrator.")."<br/>");
                redirect ('');
            }
            //redirect somewhere if commit was successfull
            redirect('users/edit/'.$user->user_id);
        }
        
    }
    
    /** 
    users/topicreview
    
    Entry point for editing the topic subscriptions for a user
    
	Fails with error message when one of:
	    non-existing user_id requested
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: optional user_id of the user to be edited (default: logged user)
	         
    Returns:
        A full HTML page with a 'topic subscription tree'
    */
    function topicreview() {
	    $userlogin  = getUserLogin();
      $user_id = $this->uri->segment(3,$userlogin->userId());
	    $user = $this->user_db->getByID($user_id);
	    
	    if ($user==null) {
	        appendErrorMessage(__('Topic review').': '.__('non-existing id passed').'.<br/>');
	        redirect('');
	    }
	    
	    //check user rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('topic_subscription'))
             ||
                (  !$userlogin->hasRights('user_edit_all')
                    && 
                   ($userlogin->userId() != $user->user_id)
                 )
            ) 
        {
	        appendErrorMessage(__('Topic subscription').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }
        
	    
	    
        //get output
        $headerdata = array();
        $headerdata['title'] = __('Topic subscription');
        $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $user = $this->user_db->getByID($user_id);
        $config = array('user'=>$user,'includeGroupSubscriptions'=>True);
        $root = $this->topic_db->getByID(1,$config);
        $this->load->vars(array('subviews'  => array('topics/usersubscriptiontreerow'=>array('allCollapsed'=>True))));
        $output .= "<p class='header'>".sprintf(__("Topic subscription for %s (%s)"),$user->login,$user->firstname." ".$user->betweenname." ".$user->surname)."</p>";
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
    users/subscribe
    
    Susbcribes a user to a topic. Is normally called async, without processing the
    returned partial, by clicking a subscribe link in a topic tree rendered by 
    subview 'usersubscriptiontreerow' 
    
	Fails with error message when one of:
	    susbcribe requested for non-existing topic or user
	    insufficient user rights
	    
	Parameters passed via URL:
	    3rd segment: topic_id
	    4rd segment: optional user_id (default: logged user)
	         
    Returns a partial html fragment:
        an empty div if successful
        an div containing an error message, otherwise
    
    */
    function subscribe() {    
      $userlogin = getUserLogin();
        $topic_id = $this->uri->segment(3,-1);
        $user_id = $this->uri->segment(4,$userlogin->userId());
        
        $user = $this->user_db->getByID($user_id);
        if ($user == null) {
            echo "<div class='errormessage'>".__("Subscribe topic").": ".__("non-existing id passed").".</div>";
            return;
        } 

	    
	    //check user rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('topic_subscription') )
             ||
                (  !$userlogin->hasRights('user_edit_all')
                    && 
                   ($userlogin->userId() != $user->user_id)
                 )
            ) 
        {
	        echo __('Topic subscription').': '.__('insufficient rights').'.<br/>';
	        return;
        }

        $config = array('user'=>$user);

        $topic = $this->topic_db->getByID($topic_id,$config);
        
        if ($topic == null) {
            echo "<div class='errormessage'>".__("Subscribe topic").": ".__("non-existing id passed").".</div>";
        }
        //do subscribe
        $topic->subscribeUser();

        echo "<div/>";
    }    
    
    
    /**
    users/unsubscribe
    
    Unsusbcribes a user to a topic. Is normally called async, without processing the
    returned partial, by clicking an unsubscribe link in a topic tree rendered by 
    subview 'usersubscriptiontreerow' 
    
	Fails with error message when one of:
	    unsusbcribe requested for non-existing topic or user
	    insufficient user rights
	    
	Parameters passed via URL:
	    3rd segment: topic_id
	    4rd segment: optional user_id (default: logged user)
	         
    Returns a partial html fragment:
        an empty div if successful
        an div containing an error message, otherwise
    
    */
    function unsubscribe() {
      $userlogin = getUserLogin();
        $topic_id = $this->uri->segment(3,-1);
        $user_id = $this->uri->segment(4,$userlogin->userId());
        
        $user = $this->user_db->getByID($user_id);
        if ($user == null) {
            echo "<div class='errormessage'>".__("Unsubscribe topic").": ".__("non-existing id passed").".</div>";
            return;
        }


	    
	    //check user rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('topic_subscription') )
             ||
                (  !$userlogin->hasRights('user_edit_all')
                    && 
                   ($userlogin->userId() != $user->user_id)
                 )
            ) 
        {
	        echo __('Topic subscription').': '.__('insufficient rights').'.<br/>';
	        return;
        }
        
        $config = array('user'=>$user);
        $topic = $this->topic_db->getByID($topic_id,$config);
        
        if ($topic == null) {
            echo "<div class='errormessage'>".__("Unsubscribe topic").": ".__("non-existing id passed")."</div>";
        }
        //do unsubscribe
        $topic->unsubscribeUser();

        echo "<div/>";
    }    
    
}
?>