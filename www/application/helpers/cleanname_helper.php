<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for searchable cleannames (authors, titles, journals, etc). 
| -------------------------------------------------------------------
|
|       
*/

//if there is no math in the name, curly braces should be omitted
    function authorCleanName($author) {
        $CI = &get_instance();
        $CI->load->helper('utf8_to_ascii');
        $result = utf8_to_ascii($author->getName('vlf'));
        //omit braces except when math environment in title
        if (strpos($result,'$')===false) {
            $result = str_replace(array('{','}'),'',$result);
        }
        return $result;
    }
//if there is no math in the name, curly braces should be omitted
    function cleanTitle($title) {
        $CI = &get_instance();
        $CI->load->helper('utf8_to_ascii');
        $result =  utf8_to_ascii($title);
        //omit braces except when math environment in title
        if (strpos($result,'$')===false) {
            $result = str_replace(array('{','}'),'',$result);
        }
        return $result;
    }

?>