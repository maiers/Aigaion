<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for filenames. Takes any string, and removes characters that are
|   problematic in filenames
| -------------------------------------------------------------------
|
|       
*/

    function toCleanName($string) {
        $CI = &get_instance();
        $CI->load->helper('utf8_to_ascii');
        return str_replace(array(',','"','\'','/',"\\",':',';','+'),'',utf8_to_ascii($string));
    }

?>