<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/** This class regulates the database access for Notes. Several accessors are present that return a Note or 
array of Note's. */
class Note_db {
  
  
    function Note_db()
    {
    }
    
    /** Return the Note object with the given id, or null if insufficient rights */
    function getByID($note_id)
    {
        $CI = &get_instance();
        $Q = $CI->db->get_where('notes', array('note_id' => $note_id));
        if ($Q->num_rows() > 0)
        {
            return $this->getFromRow($Q->row());
        }  else {
            return null;
        }
    }
   
    /** Return the Note object stored in the given database row, or null if insufficient rights. */
    function getFromRow($R)
    {
        $CI = &get_instance();
        $userlogin  = getUserLogin();
        
        $note = new Note;
        foreach ($R as $key => $value)
        {
            $note->$key = $value;
        }
        //check rights; if fail: return null
        if ( !$userlogin->hasRights('note_read') || !$CI->accesslevels_lib->canReadObject($note))return null;
        
        //read the crossref_ids as they were cached in the database
        $CI->db->select('xref_id');
        $Q = $CI->db->get_where('notecrossrefid',array('note_id'=>$note->note_id));
    	foreach ($Q->result() as $R) {
            $note->xref_ids[] = $R->xref_id;
    	}        
        return $note;
    }

    /** Construct a note from the POST data present in the note/edit or add view. 
    Return null if the POST data was not present. */
    function getFromPost()
    {
        $CI = &get_instance();
        $note = new Note;
        //correct form?
        if ($CI->input->post('formname')!='note') {
            return null;
        }
        //get basic data
        $note->note_id            = $CI->input->post('note_id');
        $note->text               = $CI->input->post('text');
        $note->pub_id             = $CI->input->post('pub_id');
        $note->user_id            = $CI->input->post('user_id');

        return $note;
    }
        
    /** Return an array of Note object for the given publication. */
    function getNotesForPublication($pub_id) {
        $CI = &get_instance();
        $result = array();
        $Q = $CI->db->get_where('notes', array('pub_id' => $pub_id));
        foreach ($Q->result() as $row) {
            $next  = $this->getFromRow($row);
            if ($next != null) {
                $result[] = $next;
            }
        }
        return $result;
    }
    
    /** Return an array of Note objects that crossref the given publication in their text. 
    Will return only accessible notes (i.e. wrt access_levels). This method can therefore
    not be used to e.g. update note texts for crossref changes due to a changed bibtex id. */
    function getXRefNotesForPublication($pub_id) {
        $CI = &get_instance();
        $result = array();
        $Q = $CI->db->get_where('notecrossrefid', array('xref_id' => $pub_id));
        foreach ($Q->result() as $row) {
            $next  =$this->getByID($row->note_id);
            if ($next != null) {
                $result[] = $next;
            }
        }
        return $result;
    }

    /** Add a new note with the given data. Returns the new note_id, or -1 on failure. */
    function add($note) {
        $CI = &get_instance();
        //check access rights (!)
        $userlogin    = getUserLogin();
        $user         = $CI->user_db->getByID($userlogin->userID());
        $publication  = $CI->publication_db->getByID($note->pub_id);
        if (    ($publication == null) 
             ||
                (!$userlogin->hasRights('note_edit'))
             || 
                (!$CI->accesslevels_lib->canEditObject($publication))
            ) 
        {
	        appendErrorMessage(__('Add note').': '.__('insufficient rights').'.<br/>');
	        return;
        }        
        //add new note
        $CI->db->insert("notes", array('text'              => $note->text,
                                             'pub_id'            => $note->pub_id,
                                             'user_id'           => $userlogin->userId()));
        $new_id = $CI->db->insert_id();
        $note->note_id = $new_id;
        $CI->accesslevels_lib->initNoteAccessLevels($note);        
        //set crossref ids
        $xref_ids = getCrossrefIDsForText($note->text);
        foreach ($xref_ids as $xref_id) {
            $CI->db->insert("notecrossrefid", array('xref_id'=>$xref_id, 'note_id'=>$note->note_id));
        }
                             
        return $new_id;
    }

    /** Commit the changes in the data of the given note. Returns TRUE or FALSE depending on 
    whether the operation was successful. */
    function update($note) {
        $CI = &get_instance();
        //check access rights (by looking at the original note in the database, as the POST
        //data might have been rigged!)
        $userlogin  = getUserLogin();
        $user       = $CI->user_db->getByID($userlogin->userID());
        $note_testrights = $CI->note_db->getByID($note->note_id);
        if (    ($note_testrights == null) 
             ||
                (!$userlogin->hasRights('note_edit'))
             || 
                (!$CI->accesslevels_lib->canEditObject($note_testrights))
            )
        {
	        appendErrorMessage(__('Edit note').': '.__('insufficient rights').'.<br/>');
	        return;
        }
        
        //start update
        $updatefields =  array('text'=>$note->text);

        $CI->db->update("notes", $updatefields,array('note_id'=>$note->note_id));
        

        //remove old xref ids
        $CI->db->delete('notecrossrefid', array('note_id' => $note->note_id)); 
        //set crossref ids
        $xref_ids = getCrossrefIDsForText($note->text);
        foreach ($xref_ids as $xref_id) {
            $CI->db->insert('notecrossrefid', array('xref_id'=>$xref_id,'note_id'=>$note->note_id));
        }
                                                       
        return True;
    }
    /** delete given object. where necessary cascade. Checks for edit and read rights on this object and all cascades
    in the _db class before actually deleting. */
    function delete($note) {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        //collect all cascaded to-be-deleted-id's: none
        //check rights
        //check, all through the cascade, whether you can read AND edit that object
        if (!$userlogin->hasRights('note_edit')
            ||
            !$CI->accesslevels_lib->canEditObject($note)
            ) {
            //if not, for any of them, give error message and return
            appendErrorMessage(__('Cannot delete note').': '.__('insufficient rights').'.<br/>');
            return false;
        }
        if (empty($note->note_id)) {
            appendErrorMessage(__('Cannot delete note').': '.__('erroneous ID').'.<br/>');
            return false;
        }
        //otherwise, delete all dependent objects by directly accessing the rows in the table 
        $CI->db->delete('notes',array('note_id'=>$note->note_id));
        //delete links
        $CI->db->delete('notecrossrefid',array('note_id'=>$note->note_id));
        $CI->db->delete('notecrossrefid',array('xref_id'=>$note->note_id));
        //add the information of the deleted rows to trashcan(time, data), in such a way that at least manual reconstruction will be possible
        return true;
    }  

    /** change the text of all affected notes to reflect a change of the bibtex_id of the given publication.
    Note: this method does NOT make use of getByID($note_id), because one should also change the referring 
    text of all notes that are inaccessible through getByID($note_id) due to access level limitations. */
    function changeAllCrossrefs($pub_id, $new_bibtex_id) 
    {
        $CI = &get_instance();
		$bibtexidlinks = getBibtexIdLinks();
        $Q = $CI->db->get_where('notecrossrefid',array('xref_id'=>$pub_id));
        foreach ($Q->result() as $R) {
            $noteQ = $CI->db->get_where('notes',array('note_id'=>$R->note_id));
            if ($noteQ->num_rows()>0) {
              $R = $noteQ->row();
        		  $text = preg_replace($bibtexidlinks[$pub_id][1], $new_bibtex_id, $R->text);
              //update is done here, instead of using the update function, as some of the affected notes may not be accessible for this user
              $updatefields =  array('text'=>$text);
              $CI->db->update('notes',$updatefields,array('note_id'=>$R->note_id));
      		    if (mysql_error()) {
      		      appendErrorMessage(sprintf(__("Failed to update the BibTeX-id in note %s."),$R->note_id)."<br/>");
          	  }
            }
        }
    }
  

}
?>