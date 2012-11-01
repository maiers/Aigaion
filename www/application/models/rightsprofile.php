<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/** A Rightsprofile is simply a named collection of rights. Such rightsprofiles can be used to determine which rights are assigned by default 
to new group members, or to assign a collection of rights to a user with one action. */
class Rightsprofile extends CI_Model {
  
    #ID
    var $rightsprofile_id            = '';
    #content variables; to be changed directly when necessary
    //name
    var $name           = '';
    //rights
    var $rights     = array(); //an array of ($right_name)
    //link to the CI base object

    function __construct()
    {
      parent::__construct();
    }
    
    /** Add a new Rightsprofile with the given data. Returns TRUE or FALSE depending on whether the operation was
    successfull. After a successfull 'add', $this->rightsprofile_id contains the new rightsprofile_id. */
    function add() {
        $CI = &get_instance();
        $this->rightsprofile_id = $CI->rightsprofile_db->add($this);
        return ($this->rightsprofile_id > 0);
    }

    /** Commit the changes in the data of this rightsprofile. Returns TRUE or FALSE depending on whether the operation was
    operation was successfull. */
    function update() {
        $CI = &get_instance();
        return $CI->rightsprofile_db->update($this);
    }
    /** Deletes this rightsprofile. Returns TRUE or FALSE depending on whether the operation was
    successful. */
    function delete() {
        $CI = &get_instance();
        return $CI->rightsprofile_db->delete($this);
    }
}
?>