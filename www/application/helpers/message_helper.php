<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for error and other messages 
| -------------------------------------------------------------------
|
|   Allows one to append error messages and normal user messages,
|   retrieve the current message and clean the stored messages.
|
|	Usage:
|       //load this helper:
|       $this->load->helper('message'); 
|       //append an error message:
|       appendErrorMessage($msg); 
|       //append a normal message:
|       appendMessage($msg); 
|       //retrieve full error message:
|       $err = getErrorMessage(); 
|       //retrieve full normal message:
|       $msg = getMessage(); 
|       //clear error message:
|       clearErrorMessage();
|       //clear normal message:
|       clearMessage();
| 
|   Implementation:
|       Currently this is implemented by storing two messages in the session
|       named "errormessage" and "message".
*/


    /** append an error message */
    function appendErrorMessage($msg) {
        $CI = &get_instance();
        $CI->latesession->set('errormessage',$CI->latesession->get('errormessage').$msg);
    }
    
    /** append a normal message */
    function appendMessage($msg) {
        $CI = &get_instance();
        $CI->latesession->set('message',$CI->latesession->get('message').$msg);
    }
    
    /** retrieve full error message */
    function getErrorMessage() {
        $CI = &get_instance();
        return $CI->latesession->get('errormessage');
    }
    
    /** retrieve full normal message */
    function getMessage() {
        $CI = &get_instance();
        return $CI->latesession->get('message');
    }
    
    /** clear error message */
    function clearErrorMessage() {
        $CI = &get_instance();
        $CI->latesession->set('errormessage','');
    }
    
    /** clear normal message */
    function clearMessage() {
        $CI = &get_instance();
        $CI->latesession->set('message','');
    }

?>