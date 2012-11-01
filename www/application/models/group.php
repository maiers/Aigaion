<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/** This class holds the data structure of a group. 

Groups are structurally very similar to Users, and they even use the same tables. 
However, conceptually they are very different, which is why we made separate classes for them. */
class Group extends CI_Model {
  
    #ID
    var $group_id           = '';
    var $user_id           = ''; //user_id == group_id, but some functions treat groups and users equally, and expect the user_id to be there
    #content variables; to be changed directly when necessary
    //name
    var $name               = '';
    //other info
    var $abbreviation       = '';
    #system variables, not to be changed *directly* by user
    var $rightsprofile_ids  = array();
    //link to the CI base object

    function __construct()
    {
      parent::__construct();
    }


    /** Add a new Group with the given data. Returns TRUE or FALSE depending on whether the operation was
    successfull. After a successfull 'add', $this->group_id contains the new group_id. */
    function add() {
        $CI = &get_instance();
        $this->group_id = $CI->group_db->add($this);
        return ($this->group_id > 0);
    }

    /** Commit the changes in the data of this group. Returns TRUE or FALSE depending on whether the operation was
    operation was successfull. */
    function update() {
        $CI = &get_instance();
        return $CI->group_db->update($this);
    }
    /** Deletes this group. Returns TRUE or FALSE depending on whether the operation was
    successful. */
    function delete() {
        $CI = &get_instance();
        return $CI->group_db->delete($this);
    }
}
?>