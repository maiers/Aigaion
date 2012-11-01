<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/** This class holds the data structure of a user. 

This User class is now mostly used for managing users and profiles.
Later on, this class will also be used in the login library.

Database access for Users is done through the User_db library */
class User extends CI_Model {
  
    #ID
    var $user_id            = '';
    #content variables; to be changed directly when necessary
    //name
    var $initials           = '';
    var $firstname          = '';
    var $betweenname        = '';
    var $surname            = '';
    //other info
    var $email              = '';
    var $lastreviewedtopic  = 0;
    var $lastupdatecheck    = 0;
    //login info
    var $abbreviation       = '';
    var $type               = 'normal';
    var $login              = '';
    var $password           = '';
    var $password_invalidated = 'FALSE';
    #system variables, not to be changed *directly* by user
    //preferences. Directly filled with default values, but that will change in the future
    var $preferences        = array('theme'=>'default',
                                    'language'=>'default',
                                    'summarystyle'=>'default',
                                    'authordisplaystyle'=>'default',
                                    'liststyle'=>'default',
                                    'similar_author_test'=>'default',
                                    'newwindowforatt'=>'FALSE',
                                    'exportinbrowser'=>'FALSE',
                                    'utf8bibtex'=>'FALSE'
                                    ); //an array of ($preferencename=>preferencevalue)
                              
    //assigned rights
    var $assignedrights     = array(); //an array of ($assignedright)
    //the ids of all groups that the user is a part of
    var $group_ids          = array();
    //the cached subscription tree (including group subscriptions!)
    //only has a value for the logged user!
    var $fullSubscriptionTree = null;
    var $toBeDisabled = false;
    
    /** The class-tree (Category object) of  only those classes to which the user is subscribed */
    //var $personal_subscribed_tree    = null; //this is the tree as it is only filled with the topics for this individual user, i.e. the 'extra' subscribed topics
    //var $full_subscribed_tree    = null; //this is the tree as it is also filled with the topics from the group!
    //or dow we want to store the topics as a list of IDs?
    
    function __construct()
    {
      parent::__construct();
    }
    
    /** Add a new user with the given data. Returns TRUE or FALSE depending on whether the operation was
    successfull. After a successfull 'add', $this->user_id contains the new user_id. */
    function add() {
        $CI = &get_instance();
        $this->user_id = $CI->user_db->add($this);
        if ($this->user_id > 0) {
            return True;
        }
        return False;
    }

    /** Commit the changes in the data of this user. Returns TRUE or FALSE depending on whether the operation was
    successfull. */
    function update() {
        $CI = &get_instance();
        return $CI->user_db->update($this);
    }
    
    /** Set a new password for this user, and, if needed, enable it */
    function setPassword($pwd)
    {
        $CI = &get_instance();
        $CI->user_db->setPassword($this,$pwd);
    }
    
    /** Deletes this user. Returns TRUE or FALSE depending on whether the operation was
    successful. */
    function delete() {
        $CI = &get_instance();
        return $CI->user_db->delete($this);
    }
}
?>