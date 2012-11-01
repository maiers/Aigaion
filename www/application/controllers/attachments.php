<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Attachments extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
	}
	
	/** There is no default controller for attachments. */
	function index()
	{
		redirect('');
	}

    /** 
    attachments/single
    
    Entry point for viewing (i.e. downloading) one attachment.
    
	Fails with error message when one of:
	    a non-existing attachment requested
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: att_id, the id of the attachment to be downloaded
	         
    Returns:
        The attachment file in proper format
    */
	function single() {
	    $att_id = $this->uri->segment(3);
	    $attachment = $this->attachment_db->getByID($att_id);
	    
	    if ($attachment==null) {
	        appendErrorMessage(__('Download attachment').': '.__('non-existing id passed').'.<br/>');
	        redirect('');
	    }
	    
        $output = $this->load->view('attachments/download',
                              array('attachment'   => $attachment
                                    ),  
                              true);
                              
        //set output
        $this->output->set_output($output);
	}
	
	function render() {
		
		$att_id = $this->uri->segment(3);
		$attachment = $this->attachment_db->getByID($att_id);
		 
		if ($attachment==null) {
			appendErrorMessage(__('Download attachment').': '.__('non-existing id passed').'.<br/>');
			redirect('');
		}
		
	}

	/** 
	attachments/delete
	
	Entry point for deleting an attachment.
	Depending on whether 'commit' is specified in the url, confirmation may be requested before actually
	deleting. 
	
	Fails with error message when one of:
	    delete requested for non-existing attachment
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: att_id, the id of the to-be-deleted-attachment
	    4th: if the 4th segment is the string 'commit', no confirmation is requested.
	         if not, a confirmation form is shown; upon choosing 'confirm' this same controller will be 
	         called with 'commit' specified
	         
    Returns:
        A full HTML page showing a 'request confirmation' form for the delete action, if no 'commit' was specified
        Redirects somewhere (?) after deleting, if 'commit' was specified
	*/
	function delete()
	{
	    $att_id = $this->uri->segment(3);
	    $attachment = $this->attachment_db->getByID($att_id);
	    $commit = $this->uri->segment(4,'');

	    if ($attachment==null) {
	        appendErrorMessage(__('Delete attachment').': '.__('non-existing id passed').'.<br/>');
	        redirect('');
	    }

	    //besides the rights needed to READ this attachment, checked by attachment_db->getByID, we need to check:
	    //edit_access_level and the user edit rights
        $userlogin  = getUserLogin();
        if (    (!$userlogin->hasRights('attachment_edit'))
             || 
                !$this->accesslevels_lib->canEditObject($attachment)
            ) 
        {
	        appendErrorMessage(__('Delete attachment').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }

        if ($commit=='commit') {
            //do delete, redirect somewhere
            $attachment->delete();
            redirect('publications/show/'.$attachment->pub_id);
        } else {
            //get output: a full web page with a 'confirm delete' form
            $headerdata = array();
            $headerdata['title'] = __('Delete attachment');
            
            $output = $this->load->view('header', $headerdata, true);
    
            $output .= $this->load->view('attachments/delete',
                                         array('attachment'=>$attachment),  
                                         true);
            
            $output .= $this->load->view('footer','', true);
    
            //set output
            $this->output->set_output($output);	
        }
    }


	/** 
	attachments/add
	
	Entry point for adding an attachment.
	Shows the form needed for uploading.
	The actual upload is processed in the 'attachments/commit' controller.
	
	Fails with error message when one of:
	    add attachment requested for non-existing publication
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: pub_id, the id of the publication to which the attachment will be added
	         
    Returns:
        A full HTML page showing an 'upload attachment' form for the given publication
	*/
	function add() {
	    $pub_id = $this->uri->segment(3);
        
        $publication = $this->publication_db->getByID($pub_id);
        if ($publication == null) {
            appendErrorMessage("<div class='errormessage'>".__("Add attachment").": ".__("non-existing id passed").".</div>");
            redirect('');
        }


	    //besides the rights needed to READ this attachment, checked by attachment_db->getByID, we need to check:
	    //edit_access_level and the user edit rights
	    //in this case it's mostly the rights on the publication that determine access
        $userlogin  = getUserLogin();
        $user       = $this->user_db->getByID($userlogin->userID());
        if (    (!$userlogin->hasRights('attachment_edit'))
             || 
                !$this->accesslevels_lib->canEditObject($publication)    
            ) 
        {
	        appendErrorMessage(__('Add attachment').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }

        //get output: a full web page with a 'attachment add' form
        $headerdata = array();
        $headerdata['title'] = __('Add attachment');
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= $this->load->view('attachments/add',
                                     array('publication'=>$publication),  
                                     true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);	        
    }
    
    /** 
    attachments/edit
    
    Entry point for editing an attachment.
    
	Fails with error message when one of:
	    non-existing att_id requested
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: att_id, the id of the attachment to be edited
	         
    Returns:
        A full HTML page with an 'edit attachment' form
    */
    function edit()
	{
	    $att_id = $this->uri->segment(3,-1);
	    $attachment = $this->attachment_db->getByID($att_id);
	    if ($attachment==null) {
	        appendErrorMessage(__("Edit attachment").": ".__("non-existing id passed").".<br/>");
	        redirect('');
	    }
	    
	    //besides the rights needed to READ this attachment, checked by attachment_db->getByID, we need to check:
	    //edit_access_level and the user edit rights
        $userlogin  = getUserLogin();
        $user       = $this->user_db->getByID($userlogin->userID());
        if (    (!$userlogin->hasRights('attachment_edit'))
             || 
                !$this->accesslevels_lib->canEditObject($attachment)
         ) 
        {
	        appendErrorMessage(__('Edit attachment').': '.__('insufficient rights').'.<br/>');
	        redirect('publications/show/'.$attachment->pub_id);
        }
	    
        //get output
        $headerdata = array();
        $headerdata['title'] = __('Attachment');
        $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= $this->load->view('attachments/edit',
                                      array('attachment'=>$attachment),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}
    
    /** 
    attachments/commit
    
    Entry point for committing an attachment (add or edit).
    
	Fails with error message when one of:
	    non-existing att_id requested
	    insufficient user rights
	    problem uploading file
	    
	Parameters passed via POST:
	    all info from the add or edit form
	    $action = (add|edit)
	         
    Returns:
        Somewhere
    */
    function commit()
	{
        //get data from POST
        $attachment = $this->attachment_db->getFromPost();

	    if ($attachment==null) {
	        appendErrorMessage(__("Commit attachment").": ".__("no data to commit").".<br/>");
	        redirect('');
	    }

//             the checks on the attachment itself are of course not tested here,
//             but in the commit action, as the client can have sent 'wrong' form data        

    
        //if validation was successfull: add or change.
        $success = False;
        if ($this->input->post('action') == 'edit') {
            //do edit
            $success = $attachment->update();
        } else {
            //do add
            $success = $attachment->add();
        }
        if (!$success) {
            //might happen, e.g. if upload fails due to post size limits, upload size limits, etc.
            //or illegal attachment extensions etc.
            appendErrorMessage(__("Commit attachment").": ".__("an error occurred").". ".__("Please contact your Aigaion administrator.")."<br/>"); 
            redirect('publications/show/'.$attachment->pub_id);
        }
        //redirect somewhere if commit was successfull
        redirect('publications/show/'.$attachment->pub_id);

	}
    
    /** 
    attachments/setmain
    
    Entry point for setting attachment as main.
    
	Fails with error message when one of:
	    non-existing att_id requested
	    
	Fails silently when insufficient user rights
	    
	Parameters passed via url segments:
	    3rd: $att_id 
	         
    Returns:
        redirects to publications/show
    */
    function setmain() {
	    $att_id = $this->uri->segment(3,-1);
	    $attachment = $this->attachment_db->getByID($att_id);
	    if ($attachment==null) {
	        appendErrorMessage(__("Edit attachment").": ".__("non-existing id passed").".<br/>");
	        redirect('');
	    }
	    $attachment->ismain=true;
	    $attachment->update();
	    redirect('publications/show/'.$attachment->pub_id);
    }
    
    /** 
    attachments/unsetmain
    
    Entry point for unsetting attachment as main.
    
	Fails with error message when one of:
	    non-existing att_id requested

	Fails silently when insufficient user rights
	    
	Parameters passed via url segments:
	    3rd: $att_id 
	         
    Returns:
        redirects to publications/show
    */
    function unsetmain() {
	    $att_id = $this->uri->segment(3,-1);
	    $attachment = $this->attachment_db->getByID($att_id);
	    if ($attachment==null) {
	        appendErrorMessage(__("Edit attachment").": ".__("non-existing id passed").".<br/>");
	        redirect('');
	    }
	    $attachment->ismain=false;
	    $attachment->update();
	    redirect('publications/show/'.$attachment->pub_id);
    }
}


?>