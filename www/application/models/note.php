<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/** This class holds the data structure of a Note.

Database access for Notes is done through the Note_db library */
class Note extends CI_Model {

    #ID
    var $note_id            = '';
    #content variables; to be changed by user when necessary
    var $text               = '';
    var $read_access_level  = 'intern';
    var $edit_access_level  = 'intern';
    var $derived_read_access_level  = 'intern';
    var $derived_edit_access_level  = 'intern';
    #system variables, not to be changed by user
    var $user_id            = -1;
    var $group_id           = 0; //group to which access is restricted
    var $pub_id             = -1;
    var $xref_ids       = array();
    
    function __construct()
    {
      parent::__construct();
    }
    
    /** tries to add this note to the database. may give error message if unsuccessful, e.g. due
    insufficient rights. */
    function add() 
    {
        $CI = &get_instance();
        $result_id = $CI->note_db->add($this);
        return ($result_id > 0);
    }
    /** tries to commit this note to the database. Returns TRUE or FALSE depending 
    on whether the operation was operation was successfull. */
    function update() 
    {
        $CI = &get_instance();
        return $CI->note_db->update($this);
    }
    /** Deletes this note. Returns TRUE or FALSE depending on whether the operation was
    successful. */
    function delete() {
        $CI = &get_instance();
        return $CI->note_db->delete($this);
    }
    

}
?>