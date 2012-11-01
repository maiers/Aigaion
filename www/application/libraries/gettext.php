<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once (APPPATH."libraries/gettext/gettext.inc");

/**
 * This class was originally contributed by Manuel Strehl.
 *
 * This class is intended as an convenience interface between Aigaion and 
 * the php gettext library of Steven Armstrong.
 *
 * This class uses the variables $AIGAION_SUPPORTED_LANGUAGES and AIGAION_DEFAULT_LANGUAGE
 * (defined in index.php) to initialize gettext (set the locales, etc).  It also initializes
 * the userlanguage library, for future use.
 */
class Gettext {

  function __construct () {
    return $this->Gettext ();
  }
  
  function Gettext () {
    global $AIGAION_SUPPORTED_LANGUAGES;
    $CI =& get_instance ();
    
    // user login for language preferences
    $userlogin = getUserLogin ();
    $lang = $userlogin->getPreference('language');
    //appendMessage("setting language: ".$lang."<br>");
      //init userlanguage library:
      // construct a string of supported languages. We assume, that the first one is the prefered.
      // these supported languages are defined in aigaionengine/config/config.php and can be overridden
      // in index.php
      $s = '';
      for ($i = 0; $i < count ($AIGAION_SUPPORTED_LANGUAGES); $i++) {
        $s .= $AIGAION_SUPPORTED_LANGUAGES[$i];
        if ($i > 0) { $s .= ";q=".round ((count ($AIGAION_SUPPORTED_LANGUAGES)-$i)/count ($AIGAION_SUPPORTED_LANGUAGES), 2); }
        if ($i < count ($AIGAION_SUPPORTED_LANGUAGES)-1) { $s .= ","; }
      }
      $CI->userlanguage->setSupported ($s);
      $CI->userlanguage->setAccept ($lang);
      //this is still needed, because we are not *quite* sure that the language exists. Better to init the right locale :)
      $lang = $CI->userlanguage->get();
      //appendMessage("available: ".$lang."<br>");
      
      #uncomment this line if you have all languages installed for CodeIgniter under their correct short name
      #to make CodeIgniter follow the same language switches:
      //$CI->config->set_item ("language", $lang);
      
    // do the gettext stuff here
    !defined ('LC_MESSAGES') ? define ('LC_MESSAGES', 5) : false;
    _bind_textdomain_codeset ('messages', "UTF-8");
    _bindtextdomain ('messages', APPPATH.'language/locale');
    $lang = _setlocale (LC_MESSAGES, $lang);
    //appendMessage("actually set: ".$lang."<br>");
 
    _textdomain ('messages');
    return true;
  }
  
  function debug ($switch = true) {
    // should here be any code?
  }
}

// "__" is already defined. Like Wordpress we define "_e" as shortcut for "echo __"
if (!function_exists ("_e")) {
  function _e ($msgid) {
    echo __($msgid);
  }
}

?>
