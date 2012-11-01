<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for some rights-related functions. 
| -------------------------------------------------------------------
|
|   Provides access to some rights related functions, such as the list of all 
|   available rights. Eventually, this functionality will probably move elsewhere. 
|
|	Usage:
|       //load this helper:
|       $this->load->helper('rigths'); 
|       //get available rights by name:
|       $list = getAvailableRights(); 
|       
*/

    /* Return a list of available rights as pairs ($name=>$description). */
    function getAvailableRights() {
        $availableRights = array();
        $CI=&get_instance();
        $CI->db->order_by('name','ASC');
        $Q = $CI->db->get('availablerights');
        foreach ($Q->result() as $R) {
            $availableRights[$R->name] = $R->description;
        }
        return $availableRights;
    }
    
?>