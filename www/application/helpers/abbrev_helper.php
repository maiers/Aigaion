<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for accessing the abbreviated names of users....
| -------------------------------------------------------------------
|
|   Provides access to the abbreviated names of users, by user_id
|
|	Usage:
|       $this->load->helper('abbrev'); //load this helper
|       $val = getAbbrevForUser($user_id); 
|
*/

$abbrevs = null;

    /** Return the Abbreviation of a certain user. */
    function getAbbrevForUser($user_id) {
        global $abbrevs;
        if ($abbrevs == null) {
            $abbrevs = array();
            $CI = &get_instance();
            foreach ($CI->user_db->getAllUsers() as $user) {
                $abbrevs[$user->user_id+0]=$user->abbreviation;
            }
        }
        if (!array_key_exists($user_id+0,$abbrevs))return"UNK";
        return $abbrevs[$user_id+0];
    }

?>