<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Notes extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
	}
	
	/** no default */
	function index()
	{
		redirect('');
	}


	/** 
	notes/delete
	
	Entry point for deleting a note.
	Depending on whether 'commit' is specified in the url, confirmation may be requested before actually
	deleting. 
	
	Fails with error message when one of:
	    delete requested for non-existing note
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: note_id, the id of the to-be-deleted-note
	    4th: if the 4th segment is the string 'commit', no confirmation is requested.
	         if not, a confirmation form is shown; upon choosing 'confirm' this same controller will be 
	         called with 'commit' specified
	         
    Returns:
        A full HTML page showing a 'request confirmation' form for the delete action, if no 'commit' was specified
        Redirects somewhere (?) after deleting, if 'commit' was specified
	*/
	function delete()
	{
	    $note_id = $this->uri->segment(3);
	    $note = $this->note_db->getByID($note_id);
	    $commit = $this->uri->segment(4,'');

	    if ($note==null) {
	        appendErrorMessage(__('Delete note').': '.__('non-existing id passed').'.<br/>');
	        redirect('');
	    }

	    //besides the rights needed to READ this note, checked by note_db->getByID, we need to check:
	    //edit_access_level and the user edit rights
        $userlogin  = getUserLogin();

        if (    (!$userlogin->hasRights('note_edit'))
             || 
                !$this->accesslevels_lib->canEditObject($note)        
            ) 
        {
	        appendErrorMessage(__('Delete note').': '.__('insufficient rights').'.<br/>');
	        redirect('publications/show/'.$note->pub_id);
        }
        
        if ($commit=='commit') {
            //do delete, redirect somewhere
            $note->delete();
            redirect('publications/show/'.$note->pub_id);
        } else {
            //get output
            $headerdata = array();
            $headerdata['title'] = __('Delete note');
            $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
            
            $output = $this->load->view('header', $headerdata, true);
    
            $output .= $this->load->view('notes/delete',
                                          array('note'=>$note),  
                                          true);
            
            $output .= $this->load->view('footer','', true);
    
            //set output
            $this->output->set_output($output);
        }
    }
    
	/** Entrypoint for adding a note. Shows the necessary form. 3rd segment is pub_id */
	function add()
	{
	    $pub_id = $this->uri->segment(3);
        
        $publication = $this->publication_db->getByID($pub_id);
        
        if ($publication == null) {
            appendErrorMessage( "<div class='errormessage'>".__("Add note").": ".__("non-existing id passed").".</div>");
            redirect('');
        }

	    //edit_access_level and the user edit rights
	    //in this case it's mostly the rights on the publication that determine access
        $userlogin  = getUserLogin();
        if (    (!$userlogin->hasRights('note_edit'))
             || 
                !$this->accesslevels_lib->canEditObject($publication)
            ) 
        {
	        appendErrorMessage(__('Add note').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }
        
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="errormessage">'.__('Changes not committed').': ', '</div>');

        //get output
        $headerdata = array();
        $headerdata['title'] = __('Add note');
        $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output  .= $this->load->view('notes/edit' , array('pub_id' => $pub_id),  true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
    }
    
	/** Entrypoint for editing a note. Shows the necessary form. */
	function edit()
	{
	  $this->load->helper('publication_helper');
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="errormessage">'.__('Changes not committed').': ', '</div>');

	    $note_id = $this->uri->segment(3,1);
        $note = $this->note_db->getByID($note_id);

	    if ($note==null) {
	        appendErrorMessage(__('Edit note').': '.__('non-existing id passed').'.<br/>');
	        redirect('');
	    }

	    //besides the rights needed to READ this note, checked by note_db->getByID, we need to check:
	    //edit_access_level and the user edit rights
        $userlogin  = getUserLogin();
        if (    (!$userlogin->hasRights('note_edit'))
             || 
                !$this->accesslevels_lib->canEditObject($note)
            ) 
        {
	        appendErrorMessage(__('Edit note').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }
        
        $publication = $this->publication_db->getByID($note->pub_id);
        
                	    
        //get output
        $headerdata = array();
        $headerdata['title'] = __('Edit note');
        $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js','tinymce/tiny_mce.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output  .= $this->load->view('notes/edit' , array('note' => $note),  true);
        $output  .= $this->load->view('publications/list', array('publications' => array($publication), 'header' => __('Publication belonging to note').':', 'noNotes' => true, 'noBookmarkList' => true, 'order' => 'none'), true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);

    }
    
    /**
    notes/commit
    
	Fails with error message when one of:
	    edit-commit requested for non-existing note
	    insufficient user rights
	    
	Parameters passed via POST:
	    action = (add|edit)
	         
    Redirects to somewhere (?) if the commit was successfull
    Redirects to the edit or add form if the validation of the form values failed
    */
    function commit() {
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="errormessage">'.__('Changes not committed').': ', '</div>');

        //get data from POST
        $note = $this->note_db->getFromPost();
        
        //check if fail needed: was all data present in POST?
        if ($note == null) {
            appendErrorMEssage(__("Commit note").": ".__("no data to commit").".<br/>");
            redirect ('');
        }
        
//             the checks on the rights of the note itself are of course not tested here,
//             but in the commit action, as the client can have sent 'wrong' form data        
        
        
        //validate form values; 
        //validation rules: 
    	$this->form_validation->set_rules(array( 'pub_id' => 'required'
                                           )
                                     );
//     	$this->form_validation->set_fields(array( 'pub_id' => __('Publication id')
//                                            )
//                                      );
    		
    	if ($this->form_validation->run() == FALSE) {
            //return to add/edit form if validation failed
            //get output
            $headerdata = array();
            $headerdata['title'] = __('Note');
            $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
            
            $output = $this->load->view('header', $headerdata, true);
    
            $output .= $this->load->view('notes/edit',
                                          array('note'         => $note,
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
                $success = $note->update();
            } else {
                //do add
                $success = $note->add();
            }
            if (!$success) {
                //this is quite unexpected, I think this should not happen if we have no bugs.
                appendErrorMessage(__("Commit note").": ".__("an error occurred").". ".__("Please contact your Aigaion administrator.")."<br/>");
                redirect ('publications/show/'.$note->pub_id);
            }
            //redirect somewhere if commit was successfull
            redirect ('publications/show/'.$note->pub_id);

        }
        
    }
    
}
?>