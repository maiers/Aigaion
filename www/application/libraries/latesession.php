<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/** 
A simple PHP Session class that 
1) names the session (based on the AIGAION_SITEID setting in the inndex.php configuration)
2) is started as late as possible. */
class LateSession {
    var $bSessionStarted = False;

    // constructor
    function LateSession() {
    }
    
    /** Save a session variable. */
    function set($var, $val) {
        if (!$this->bSessionStarted) {
            $this->_initSession();
            //appendMessage('start session in set '.$var.' for '.$this);
        }
        $_SESSION[$var] = $val;
    }
    
    /** Get the value of a session variabe */
    function get($var) {
        if (!$this->bSessionStarted) {
            $this->_initSession();
            //appendMessage('start session in get '.$var.' for '.$this);
        }
        if (!isset($_SESSION[$var]))return null; 
        return $_SESSION[$var];
    }
    
    /** Init the session. This is postponed till the first get
        or set is called, to make sure that as many as possible 
        libraries and classes have been loaded. 
        This is for the following reason:
        If you have saved an object in session, you must define 
        the class of the object before the session_start().
        If you don't do that, php will not know the class of the 
        object, and his type will be "__PHP_Incomplete_Class". */
    function _initSession() {
        //determine sessionname from config...
        //this is done to keep the sessions of two instances of this
        //system separated.
        if (session_name()!=AIGAION_SITEID)session_name(AIGAION_SITEID);
        session_start();
        $this->bSessionStarted = True;
    }
}
?>