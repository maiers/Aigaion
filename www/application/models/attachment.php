<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/** This class holds the data structure of an Attachment.

Database access for Attachments is done through the Attachment_db library */
class Attachment extends CI_Model {

    #ID
    var $att_id             = '';
    #content variables; to be changed by user when necessary
    var $name               = '';
    var $note               = '';
    var $read_access_level  = 'intern';
    var $edit_access_level  = 'intern';
    var $derived_read_access_level  = 'intern';
    var $derived_edit_access_level  = 'intern';
    #system variables, not to be changed by user
    var $mime               = '';
    var $location           = '';
    var $isremote           = False;
    var $ismain             = False;
    var $user_id            = -1;
    var $group_id            = 0; //group to which access is restricted
    var $pub_id             = -1;
    
    function __construct()
    {
      parent::__construct();
    }
    
    /** tries to add this publication to the database. may give error message if unsuccessful, e.g. due
    to illegal extension, upload error, etc. */
    function add() {
        $CI = &get_instance();
        $result_id = $CI->attachment_db->add($this);
        return ($result_id > 0);
    }
    /** tries to commit this attachment to the database. Note: not all fields are supposed to be edited.
    Generally, only the note and the name are considered to be editable! Returns TRUE or FALSE depending 
    on whether the operation was operation was successfull. */
    function update() {
        $CI = &get_instance();
        return $CI->attachment_db->update($this);
    }
    /** Deletes this attachment. Returns TRUE or FALSE depending on whether the operation was
    successful. */
    function delete() {
        $CI = &get_instance();
        return $CI->attachment_db->delete($this);
    }
   
}
?>