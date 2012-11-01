<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Rightsprofiles extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
	}
	
	/** No default controller */
	function index()
	{
		redirect('');
	}

    /** 
    rightsprofiles/single
    
    Entry point for viewing one rightsprofile.
    
	Fails with error message when one of:
	    a non-existing rightsprofile rightsprofile_id requested
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: rightsprofile_id, the id of the rightsprofile to be viewed
	         
    Returns:
        A full HTML page with all information about the rightsprofile
    */
    function single()	{
	    $rightsprofile_id = $this->uri->segment(3,-1);
	    $rightsprofile = $this->rightsprofile_db->getByID($rightsprofile_id);
	    if ($rightsprofile==null) {
	        appendErrorMessage(__("View rights profile").": ".__("non-existing id passed").".<br/>");
	        redirect('');
	    }

        //no additional rights check. Only, in the view the edit links may be suppressed depending on the user rights
	    	    
        //get output
        $headerdata = array();
        $headerdata['title'] = __('Rightsprofile');
        $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= $this->load->view('rightsprofiles/full',
                                      array('rightsprofile'   => $rightsprofile),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}
	
	
    /** 
    rightsprofiles/add
    
    Entry point for adding a rightsprofile.
    
	Fails with error message when one of:
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    none
	         
    Returns:
        A full HTML page with an 'add rightsprofile' form
    */
    function add()
	{
	    //check rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('user_edit_all')||!$userlogin->hasRights('user_assign_rights'))
            ) 
        {
	        appendErrorMessage(__('Add rights profile').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }

	    $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="errormessage">'.__('Changes not committed').': ', '</div>');

        //get output
        $headerdata = array();
        $headerdata['title'] = __('Rightsprofile');
        $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= $this->load->view('rightsprofiles/edit',
                                      array(),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}
	
    /** 
    rightsprofiles/edit
    
    Entry point for editing a rightsprofile.
    
	Fails with error message when one of:
	    non-existing rightsprofile id requested
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: rightsprofile_id, the id of the rightsprofile to be edited
	         
    Returns:
        A full HTML page with an 'edit rightsprofile' form
    */
    function edit()
	{
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="errormessage">'.__('Changes not committed').': ', '</div>');

	    $rightsprofile_id = $this->uri->segment(3,-1);
	    $rightsprofile = $this->rightsprofile_db->getByID($rightsprofile_id);
	    if ($rightsprofile==null) {
	        appendErrorMessage(__("Edit rights profile").": ".__("non-existing id passed").".<br/>");
	        redirect('');
	    }
	    
	    //check user rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('user_edit_all'))
             ||
                !$userlogin->hasRights('user_assign_rights')
            ) 
        {
	        appendErrorMessage(__('Edit rights profile').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }
	    
        //get output
        $headerdata = array();
        $headerdata['title'] = __('Rightsprofile');
        $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= $this->load->view('rightsprofiles/edit',
                                      array('rightsprofile'=>$rightsprofile),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}

	/** 
	rightsprofiles/delete
	
	Entry point for deleting a rightsprofile.
	Depending on whether 'commit' is specified in the url, confirmation may be requested before actually
	deleting. 
	
	Fails with error message when one of:
	    delete requested for non-existing rightsprofile
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: rightsprofile_id, the id of the to-be-deleted-rightsprofile
	    4th: if the 4th segment is the string 'commit', no confirmation is requested.
	         if not, a confirmation form is shown; upon choosing 'confirm' this same controller will be 
	         called with 'commit' specified
	         
    Returns:
        A full HTML page showing a 'request confirmation' form for the delete action, if no 'commit' was specified
        Redirects somewhere (?) after deleting, if 'commit' was specified
	*/
	function delete()
	{
	    $rightsprofile_id = $this->uri->segment(3,-1);
	    $rightsprofile = $this->rightsprofile_db->getByID($rightsprofile_id);
	    $commit = $this->uri->segment(4,'');

	    if ($rightsprofile==null) {
	        appendErrorMessage(__('Delete rights profile').': '.__('non-existing id passed').'.<br/>');
	        redirect('');
	    }
	    //check user rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('user_edit_all')) || !$userlogin->hasRights('user_assign_rights'))
        {
	        appendErrorMessage(__('Delete rights profile').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }

        if ($commit=='commit') {
            //do delete, redirect somewhere
            $rightsprofile->delete();
            redirect('users/manage');
        } else {
            //get output
            $headerdata = array();
            $headerdata['title'] = __('Rightsprofile');
            $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
            
            $output = $this->load->view('header', $headerdata, true);
    
            $output .= $this->load->view('rightsprofiles/delete',
                                          array('rightsprofile'=>$rightsprofile),  
                                          true);
            
            $output .= $this->load->view('footer','', true);
    
            //set output
            $this->output->set_output($output);
        }
    }
    
    /**
    rightsprofiles/commit
    
	Fails with error message when one of:
	    edit-commit requested for non-existing rightsprofile
	    insufficient user rights
	    
	Parameters passed via POST:
	    action = (add|edit)
	    rightsprofile_id
	    name
	    a list of set right names for all checked rights
	         
    Redirects to somewhere (?) if the commit was successfull
    Redirects to the edit or add form if the validation of the form values failed
    */
    function commit() {
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="errormessage">Changes not committed: ', '</div>');

        //get data from POST
        $rightsprofile = $this->rightsprofile_db->getFromPost();
        
        //check if fail needed: was all data present in POST?
        if ($rightsprofile == null) {
            appendErrorMEssage(__("Commit rights profile").": ".__("no data to commit").".<br/>");
            redirect ('');
        }

	    //check user rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('user_edit_all'))
            ||
                !$userlogin->hasRights('user_assign_rights')
            ) 
        {
	        appendErrorMessage(__('Edit rights profile').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }
        
        //validate form values.
        //validation rules: 
        //  -no rights profile with the same name and a different ID can exist
        //  -name is required (non-empty)
    	$this->form_validation->set_rules(array( 'name' => 'required'
                                           )
                                     );
    	$this->form_validation->set_fields(array( 'name' => __('Profile Name')
                                           )
                                     );
    		
    	if ($this->form_validation->run() == FALSE) {
            //return to add/edit form if validation failed
            //get output
            $headerdata = array();
            $headerdata['title'] = __('Rightsprofile');
            $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
            
            $output = $this->load->view('header', $headerdata, true);
    
            $output .= $this->load->view('rightsprofiles/edit',
                                          array('rightsprofile' => $rightsprofile,
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
                $success = $rightsprofile->update();
            } else {
                //do add
                $success = $rightsprofile->add();
            }
            if (!$success) {
                //this is quite unexpected, I think this should not happen if we have no bugs.
                appendErrorMessage(__("Commit rights profile").": ".__("an error occurred").". ".__("Please contact your Aigaion administrator.")."<br/>");
                redirect('users/manage');
            }
            //redirect somewhere if commit was successfull
            redirect('users/manage');
        }
        
    }
}
?>