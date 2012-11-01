<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
//Code started in a very early version from org.variorum.services.bibtex.UTF8Converter.java, but has been completely rewritten since
//see https://variorum.htmlweb.com/trac/browser/webappDemo/trunk/src/org/variorum/services/bibtex/UTF8Converter.java

/*
| -------------------------------------------------------------------
|  Library for special character conversion (bibtex<->utf8)
| -------------------------------------------------------------------
|
|   Provides several functions for special character conversion (bibtex<->utf8)
|
|   Though based upon the old specialcharfunctions of aigaion 1.x,
|   this library ONLY concerns itself with converting certain UTF-8 encoded
|   characters to BibTeX equivalents and vice versa. No more library functions 
|   are available for conversion to html entities and such - as we use utf-8
|   this is no longer needed in most cases; and for the quote replacement, other 
|   possibilities exist.
|
|   This library is of course woefully incomplete - we can never capture ALL bibtex codes 
|   and their utf8 equivalents. We use a number of codes hardcoded in this file.
|   Do you find missing codes there? Just suggest the additions to the Aigaion developers.
|
|   We expect that this library is only loaded on export of bibtex, and on adding new data (import, or form)
|
| Note:
| A string containing math code will not be converted from bibtex to utf8 -- the
| risks of making a mistake are currently too large, we need some more time for
| extensive coding for that :)
| 
|
|    Usage:
|       //load this library:
|       $this->load->library('bibtex2utf8');
|       //...or load this library in any other php context (this library is not dependent on CodeIgniter)
|       require_once("aigaionengine/libraries/bibtex2utf8.php");
|       $this->bibtex2utf8 = new Bibtex2utf8();
|
|    Then, to convert from bibtex to utf8 and vice versa, use these functions:

    $this->bibtex2utf8->utf8ToBibCharsFromArray(&$array)
        converts utf8 chars to bibtex special chars from an array

    $this->bibtex2utf8->utf8ToBibCharsFromString(&$array)
        converts utf8 chars to bibtex special chars from a string

    $this->bibtex2utf8->bibCharsToUtf8FromArray(&$array)
        converts bibtex special chars to utf8 chars from an array

    $this->bibtex2utf8->bibCharsToUtf8FromString(&$string)
        converts bibtex special chars to utf8 chars from a string

If you want to add extra character conversions:
  check which group it belongs to 
  add its entry for conversion and reverse conversion
  don't forget to take care of escapes needed for PHP as well as those needed for regexps!
  add testing entries to the unit test controller (!)
  
TODO:
 extend the test controller with more bibtex2utf8 conversion testing, including weird and 
    slightly erroneous brace usage (such as that of DBLP)
 add some of the polish charset
 add yet more common characters
*/

class Bibtex2utf8 {

    var $accentedLetters = array();
    var $combinedLetters = array();
    var $stringsAndCommands = array();
    var $specialChars = array();
    var $specialCharsBack = array();
    
    function Bibtex2utf8()
    {
      $this->init();
    }

    function utf8ToBibCharsFromArray($array)
    {
        $keys = array_keys($array);
        foreach ($keys as $key)
        {
            $array[$key] = $this->utf8ToBibCharsFromString($array[$key]);
        }
        return $array;
    }
    
    function utf8ToBibCharsFromString($string)
    {
        //DR: if string contains math, don't convert at all, as it only leads to problems... 
        if (preg_match("/(^\\$|[^\\\\]\\$)/i", $string) ==1) return $string;
        if (preg_match("/\\\\ensuremath(\\s)*\\{/i", $string) ==1) return $string;
        if (preg_match("/\\\\\\(/i", $string) ==1) return $string;
        if (preg_match("/\\\\begin(\\s)*\\{math\\}/i", $string) ==1) return $string;
        
        foreach ($this->combinedLetters as $cl) 
        {
          $char1 = $cl[0];
          $char2 = $cl[1];
          $utf8char = $cl[2];
          $string = preg_replace("/".$utf8char."/", "{\\".$char1." ".$char2."}", $string);
        }
        foreach ($this->accentedLetters as $al) 
        {
          $accent = $al[0];
          $char = $al[1];
          $utf8char = $al[2];
          $string = preg_replace("/".$utf8char."/", "{\\".$accent."{".$char."}}", $string);
          $accent = utf8_strtoupper($al[0]);
          $char = utf8_strtoupper($al[1]);
          $utf8char = utf8_strtoupper($al[2]);
          $string = preg_replace("/".$utf8char."/", "{\\".$accent."{".$char."}}", $string);
        }
        //restore {\I}
        $string = preg_replace("/\\{\\\\I\\}/", "{I}", $string);
        foreach ($this->stringsAndCommands as $sac) 
        {
          $command = $sac[0];
          $utf8char = $sac[1];
          $string = preg_replace("/".$utf8char."/", "{\\".$command."}", $string);
        }
        foreach ($this->specialCharsBack as $sc) 
        {
          $command = $sc[0];
          $utf8char = $sc[1];
          $string = preg_replace("/".$utf8char."/", $command, $string);
        }
        return $string;
    }
    
    //        converts bibtex special chars to utf8 chars from an array
    function bibCharsToUtf8FromArray($array) {
        $keys = array_keys($array);
        foreach ($keys as $key)
        {
            $array[$key] = $this->bibCharsToUtf8FromString($array[$key]);
        }
        return $array;
    }
    
    //        converts bibtex to utf8 chars special chars from a string
    function bibCharsToUtf8FromString($string) {
        //DR: if string contains math, don't convert at all, as it only leads to problems... 
        if (preg_match("/(^\\$|[^\\\\]\\$)/i", $string) ==1) return $string;
        if (preg_match("/\\\\ensuremath(\\s)*\\{/i", $string) ==1) return $string;
        if (preg_match("/\\\\\\(/i", $string) ==1) return $string;
        if (preg_match("/\\\\begin(\\s)*\\{math\\}/i", $string) ==1) return $string;
        
        foreach ($this->accentedLetters as $al) 
        {
          $accent = $al[0];
          $char = $al[1];
          $utf8char = $al[2];
          $regexp = "/(\\\\".$accent."(".$char."|\\{".$char."\\})|\\{\\\\".$accent."(".$char."|\\{".$char."\\})\\})/";
          $string = preg_replace($regexp, $utf8char, $string);
          $accent = utf8_strtoupper($al[0]);
          $char = utf8_strtoupper($al[1]);
          $utf8char = utf8_strtoupper($al[2]);
          $regexp = "/(\\\\".$accent."(".$char."|\\{".$char."\\})|\\{\\\\".$accent."(".$char."|\\{".$char."\\})\\})/";
          $string = preg_replace($regexp, $utf8char, $string);
        }
        foreach ($this->combinedLetters as $cl) 
        {
          $char1 = $cl[0];
          $char2 = $cl[1];
          $utf8char = $cl[2];
          $regexp = "/(\\\\".$char1."(\\s".$char2."|\\{".$char2."\\})|\\{\\\\".$char1."(\\s".$char2."|\\{".$char2."\\})\\})/";
          $string = preg_replace($regexp, $utf8char, $string);
        }
        foreach ($this->stringsAndCommands as $sac) 
        {
          $command = $sac[0];
          $utf8char = $sac[1];
          $regexp = "/\\{\\\\".$command."(\\{\\})?\\}/";
          $string = preg_replace($regexp, $utf8char, $string);
          $regexp = "/\\\\".$command."(\\{\\})/";
          $string = preg_replace($regexp, $utf8char, $string);
          $regexp = "/\\\\".$command."((\\s)|$)/"; //remove that whitespace!
          $string = preg_replace($regexp, $utf8char, $string);
          $regexp = "/\\\\".$command."~/"; //keep the space command!
          $string = preg_replace($regexp, $utf8char.'~', $string);
        }
        foreach ($this->specialChars as $sc) 
        {
          $command = $sc[0];
          $utf8char = $sc[1];
          $regexp = "/(\\\\".$command."|\\{\\\\".$command."\\})/";
          $string = preg_replace($regexp, $utf8char, $string);
        }
        return $string;
    }

    function init()
    {
      $CL = &get_instance();
      $CL->load->helper('utf8');
    
      /*
      \'e (for any type of accent) with any type of braces
        backslash accent letter
        needed info: 
          which accent, 
          which letter (smallcap, uppercap version will be made automatically)
          which output
        note whether the accent needs to be escaped for php, or for regexp!
      */
      
      $this->accentedLetters = array(
        array("`",'a',"à"),
        array("'",'a',"á"),
        array("\\^",'a',"â"),
        array("~",'a',"ã"),
        array("=",'a',"ā"),
        array("\"",'a',"ä"),        
        
        array("`",'e',"è"),
        array("'",'e',"é"),
        array("\\^",'e',"ê"),
        array("~",'e',"ẽ"),
        array("=",'e',"ē"),
        array("\"",'e',"ë"),
        
        array("`","\\\\i","ì"),
        array("'","\\\\i","í"),
        array("\\^","\\\\i","î"),
        array("~","\\\\i","ĩ"),
        array("=","\\\\i","ī"),
        array("\"","\\\\i","ï"), 

        array("`",'i',"ì"),
        array("'",'i',"í"),
        array("\\^",'i',"î"),
        array("~",'i',"ĩ"),
        array("=",'i',"ī"),
        array("\"",'i',"ï"),         
                
        array("`",'o',"ò"),
        array("'",'o',"ó"),
        array("\\^",'o',"ô"),
        array("~",'o',"õ"),
        array("=",'o',"ō"),
        array("\"",'o',"ö"), 
        
        array("`",'u',"ù"),
        array("'",'u',"ú"),
        array("\\^",'u',"û"),
        array("~",'u',"ũ"),
        array("=",'u',"ū"),
        array("\"",'u',"ü"), 
        
        array("'",'y',"ý"),
        array("\"",'y',"ÿ"), 
        
        array("~",'n','ñ'),
        
        /*
        {"\\^w", "ŵ"},
        
        */  

        /* some Polish and eastern european characters */
        
        //array(".","z","ż"), //why does this one not work?
        //array("'","z","ź"), //why does this one not work?
        
        array("'","s","ś"),
        array("'","n","ń"),
        array("'","c","ć")
       
        /* add more of those! */
       
      ); //did you put the comma's right? the last entry without comma!
      
      /*
        \v s For any combination of two single letters: a space after the first, 
        or braces around the second. outside braces optional. the expressions 
        cannot just be converted to uppercase: take directly from list below
        
        backslash letter letter (add any small and large cap explicitly)
        needed info: first and second letter
      */
      
      $this->combinedLetters = array ( 
            array("c","c","ç"),
            array("c","C","Ç"),
            //array("c","a","ą"),  // ĄąĘę I think these are wrong -- the little curly thing should be in the other direction, like with the c and e
            //array("c","A","Ą"),
            array("c","e","ȩ"),
            array("c","E","Ȩ"),
            
            array("d","o","ọ"),
            array("d","o","Ọ"),
            
            array("v","o","ŏ"),
            array("v","O","Ŏ"),
            array("v","c","č"),
            array("v","C","Č"),
            array("v","s","š"),
            array("v","S","Š")
            
            /* This list is never complete... extensions are welcome, if someone wants to structurally add a list of eastern european diacritic usages, it would be quite welcome */
                  
      ); //did you put the comma's right? the last entry without comma!
      
      /*
      \ae single expressions. space afterwards, or braces around total expression.
        backslash letters (add any small and large cap explicitly)
        needed info: the string
      */
      
      $this->stringsAndCommands = array(
            array("oe","œ"),
            array("OE", "Œ"),
 	          array("ae", "æ"),
 	          array("AE", "Æ"),
 	          array("aa", "å"),
 	          array("AA", "Å"), 
 	          array("ss", "ß"), 
 	          array("o", "ø"),
            array("O", "Ø"),
            array("i", "ı"),
            array("TH", "Þ"),
            array("th", "þ"),
            
            //a number of special latin-1 chars:
            array("pounds", "£"),
            array("S", "§"),
            array("textcopyright", "©"),
            array("textordfeminine", "ª"),
            array("textregistered", "®"),
            array("P", "¶"),
            array("textperiodcentered", "·"),
            array("textordmasculine", "º"),
            
 	                //{"\\\\gal" ,"α"}, ??? never new that encoding? was in the file from variothingy... 

 	          array("l", "ł"),   
 	          array("L", "Ł")   
 	          
 	    ); //did you put the comma's right? the last entry without comma!
      
      
      /*
      \& single special characters. braces optional (inner and outer)
        backslash char
        needed info: the special character
      */

      $this->specialChars = array(
      //The reason why most of these have now been switched off, is the following: 
      //(see mail PDM on 2008/11/25
      //upon import from bibtex, all \_ would be converted to _, and then all _ would on export be converted to \_
      //But: the _ may also have occurred in the orginal imported bibtex without backslash, and then these 
      //occurrences would be seriously mangled upon export... so, better to leave the bibtex imported intact.
            //array("#","#"),
            //array("\\?", "?"), //not neccesary, according to PDM
            //array("\\&", "&"),
 	        //  array("\\$", "$"),
 	          //array("\\{", "{"),//these two play havoc with all other expressions :( but the old A|igaion converters didn't have it either
 	          //array("\\}", "}"), //these two play havoc with all other expressions :( but the old A|igaion converters didn't have it either
 	          array("%", "%"), 
 	          //array("_", "_")
            //array("SS", "SS") ecause we cannot symmetrycally export all SS as \SS, better to leave them unconverted. Anyhow, I don't understand really why this one exists in LaTeX [DR]
            
            
        //{"\\\\~?", "¡"}, not sure whether this one exists
 	      //{"\\\\?? ", "¿"},   not sure whether this one exists
 	          
 	    ); //did you put the comma's right? the last entry without comma!

/* for utf82bibtex conversion! */

      $this->specialCharsBack = array(
            //array("\\#","#"),
            //array("\\&","&"),
            //array("\\?", "\\?"), //not neccesary, according to PDM
 	         // array("\\\\$", "\\$"), //why do we need the extra slashes here to e4xport $ as \$ ?
 	          //array("\\{", "\\{"), //these two play havoc with all other expressions :( but the old A|igaion converters didn't have it either
 	          //array("\\}", "\\}"),  //these two play havoc with all other expressions :( but the old A|igaion converters didn't have it either
 	          array("\\%", "%"), 
 	          //array("\\_", "_")
            
            
        //{"\\\\~?", "¡"},
 	      //{"\\\\?? ", "¿"},   
 	          
 	    ); //did you put the comma's right? the last entry without comma!
    }
}
?>