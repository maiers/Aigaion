<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/** This controller contains a number of unit test like methods. Generally, 
 * you'll just access the test controller. Its main method will call a number of 
 * different tests, such as the test for the simple character conversions, the 
 * test for correct import of full bibtex entries, etc.
 * 
 * The controller has more or less been designed to only give output on FAILED 
 * tests.
 * 
 * You can extend the tests in many ways. Most of the test methods contain some 
 * indications of how to extend them with new cases. For example, with the bibtex
 * character conversions one can quite simply add some conversions that have gone
 * wrong in the past by adding them to the well structured list of 'correct conversions'.  
 */      
class Test extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
	}
	
	function verbose() {
	  $this->index(true);
  }
	/** A test controller. */
	function index($debug=false)
	{
	  header("Content-Type: text/html; charset=UTF-8");
    $content = " 
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
  <head>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
    <title>Aigaion ".__("Unit Testing Output")."</title>
    </head>
    <body>
    <h1>".__("Unit Testing Output")."</h1>
    ".__("See the controller \"test.php\" for the unit testing functions. From its main method, call each test method with \$debug parameter = true to get more output.")."
    ";    
    
    $content .= $this->_testbibtex($debug);
    
    $content .= "</body></html>";
    
    //set output
    $this->output->set_output($content);
	}
	
	function _testbibtex($debug = false) 
	{
	  $result = "";
    $result .= $this->_testbibtex_singleimport($debug);
	  $result .= $this->_testbibtex_charconversion($debug);
    //todo: tests for the conversions between internal and external format of months and unknown macros and stuff
    //todo: tests for bibtex export in many ways; tests for crossref support; and whichever class of test we find we need because of recurring bugs!
    return $result;
	  
  }
  
  function _testbibtex_charconversion($debug = false) 
  {
    $result = "";
    $result .= "<h2>".__("Bibtex character conversions")."</h2>";
    //bibtex characters - expected conversion vs actual conversion
    $bibtextests = array(
      array ('thoseThatCurrentlyGoWrong', /* IF  you find conversions that go 
      wrong, please add them here at this point. This will remind us to fix 
      them. Also, it's kind of a 'prevent this error recurring after it was 
      solved check', so DO NOT REMOVE THOSE CONVERSION CHECKS FROM HERE WHEN YOU 
      FIXED THEM! Rather, start a new array in which people can again add their 
      found conversion errors :) */
             "Polish: \\c{a} \\c{e} \\'{c} \\l{} \\'{n} \\'{s} \\.{z} \\'{z} \\c{A} \\c{E} \\'{C} \\L{} \\'{N} \\'{S} \\.{Z} \\'{Z} French: {\\oe} {\\OE} Other: {\\TH} {\\th} {\\v{s}}", //input on this line...
             "Polish: (missing) È© Ä‡ Å‚ Å„ Å› Å¼ Åº (missing) È¨ Ä† Å� Åƒ Åš Å» Å¹ French: Å“ Å’ Other: Ãž Ã¾ Å¡" //expected output on conversion from bibtex to utf8 characters on this line...
      ),
      array ('test-Latin-1-misc-specialones',
             "!` \\c{} ?`",
             "Â¡Â¸Â¿"
      ),
      array ('test-Latin-1-misc',
             "\\pounds \\S \\textcopyright \\textordfeminine \\textregistered \\P \\textperiodcentered \\textordmasculine",
             "Â£Â§Â©ÂªÂ®Â¶Â·Âº"
      ),
      array ('test-ASCII-chars',
            "! \\# \\$ \\% \\& ' ( ) * + , - . / 0-9 : ; = ? @ A-Z [ ] \\_ ` a-z \\{ \\}",
            "! \\# \\$ % \\& ' ( ) * + , - . / 0-9 : ; = ? @ A-Z [ ] \\_ ` a-z \\{ \\}"
      ),
      array ('test-Latin-1-lower-braces1', //this one is supposedly the most complete test of the brace and case variations (concerning number of letters tested) -- the other variations of braces may miss one or two characters...
             "{\\`a} {\\'a} {\\^a} {\\~a} {\\=a} {\\\"a} {\\aa} {\\ae} {\\c c} {\\`e} {\\'e} {\\^e} {\\~e} {\\=e} {\\\"e} {\\i} {\\`\\i} {\\'\\i} {\\^\\i} {\\~\\i} {\\=\\i} {\\\"\\i} {\\`i} {\\'i} {\\^i} {\\~i} {\\=i} {\\\"i} {\\~n} {\\`o} {\\'o} {\\^o} {\\~o} {\\=o} {\\\"o} {\\o} {\\`u} {\\'u} {\\^u} {\\~u} {\\=u} {\\\"u} {\\'y} {\\\"y} {\\ss}",
             "Ã  Ã¡ Ã¢ Ã£ Ä� Ã¤ Ã¥ Ã¦ Ã§ Ã¨ Ã© Ãª áº½ Ä“ Ã« Ä± Ã¬ Ã­ Ã® Ä© Ä« Ã¯ Ã¬ Ã­ Ã® Ä© Ä« Ã¯ Ã± Ã² Ã³ Ã´ Ãµ Å� Ã¶ Ã¸ Ã¹ Ãº Ã» Å© Å« Ã¼ Ã½ Ã¿ ÃŸ"
      ),
      array ('test-Latin-1-lower-braces2', 
             "{\\`{a}} {\\'{a}} {\\^{a}} {\\~{a}} {\\\"{a}} {\\c{c}} {\\`{e}} {\\'{e}} {\\^{e}} {\\\"{e}} {\\`{\\i}} {\\'{\\i}} {\\^{\\i}} {\\\"{\\i}} {\\`{i}} {\\'{i}} {\\^{i}} {\\\"{i}} {\\~{n}} {\\`{o}} {\\'{o}} {\\^{o}} {\\~{o}} {\\\"{o}} {\\`{u}} {\\'{u}} {\\^{u}} {\\\"{u}} {\\'{y}} {\\\"{y}}",
             "Ã  Ã¡ Ã¢ Ã£ Ã¤ Ã§ Ã¨ Ã© Ãª Ã« Ã¬ Ã­ Ã® Ã¯ Ã¬ Ã­ Ã® Ã¯ Ã± Ã² Ã³ Ã´ Ãµ Ã¶ Ã¹ Ãº Ã» Ã¼ Ã½ Ã¿"
      ),
      array ('test-Latin-1-lower-braces3', 
             "\\`a \\'a \\^a \\~a \\\"a \\aa \\ae \\c c \\`e \\'e \\^e \\\"e \\i \\`\\i \\'\\i \\^\\i \\\"\\i \\`i \\'i \\^i \\\"i \\~n \\`o \\'o \\^o \\~o \\\"o \\o \\`u \\'u \\^u \\\"u \\'y \\\"y \\ss",
             "Ã  Ã¡ Ã¢ Ã£ Ã¤ Ã¥Ã¦Ã§ Ã¨ Ã© Ãª Ã« Ä±Ã¬ Ã­ Ã® Ã¯ Ã¬ Ã­ Ã® Ã¯ Ã± Ã² Ã³ Ã´ Ãµ Ã¶ Ã¸Ã¹ Ãº Ã» Ã¼ Ã½ Ã¿ ÃŸ" //note how spaces afer unbraced \aa and \ae get removed (see also next test)
      ),
      array ('test-Latin-1-lower-spacesAfterUnbracedSymbols', 
             "~\\aa~\\ae~\\ss~\\o~\\i~",
             "~Ã¥~Ã¦~ÃŸ~Ã¸~Ä±~" 
      ),
      array ('test-Latin-1-lower-braces4', 
             "\\`{a} \\'{a} \\^{a} \\~{a} \\\"{a} \\c{c} \\`{e} \\'{e} \\^{e} \\~{e} \\\"{e} \\`{\\i} \\'{\\i} \\^{\\i} \\~{\\i} \\\"{\\i} \\`{i} \\'{i} \\^{i} \\~{i} \\\"{i} \\~{n} \\`{o} \\'{o} \\^{o} \\~{o} \\\"{o} \\`{u} \\'{u} \\^{u} \\\"{u} \\'{y} \\\"{y}",
             "Ã  Ã¡ Ã¢ Ã£ Ã¤ Ã§ Ã¨ Ã© Ãª áº½ Ã« Ã¬ Ã­ Ã® Ä© Ã¯ Ã¬ Ã­ Ã® Ä© Ã¯ Ã± Ã² Ã³ Ã´ Ãµ Ã¶ Ã¹ Ãº Ã» Ã¼ Ã½ Ã¿"
      ),
      array ('test-Latin-1-upper-braces1', 
             "{\\`A} {\\'A} {\\^A} {\\~A} {\\\"A} {\\AA} {\\AE} {\\c C} {\\`E} {\\'E} {\\^E} {\\\"E} {\\`\\I} {\\'\\I} {\\^\\I} {\\\"\\I} {\\`I} {\\'I} {\\^I} {\\\"I} {\\~N} {\\`O} {\\'O} {\\^O} {\\~O} {\\\"O} {\\O} {\\`U} {\\'U} {\\^U} {\\\"U} {\\'Y} {\\\"Y} {\\SS}",
             "Ã€ Ã� Ã‚ Ãƒ Ã„ Ã… Ã† Ã‡ Ãˆ Ã‰ ÃŠ Ã‹ ÃŒ Ã� ÃŽ Ã� ÃŒ Ã� ÃŽ Ã� Ã‘ Ã’ Ã“ Ã” Ã• Ã– Ã˜ Ã™ Ãš Ã› Ãœ Ã� Å¸ {\\SS}"
      ),
      array ('test-Latin-1-upper-braces2', 
             "{\\`{A}} {\\'{A}} {\\^{A}} {\\~{A}} {\\\"{A}} {\\c{C}} {\\`{E}} {\\'{E}} {\\^{E}} {\\\"{E}} {\\`{\\I}} {\\'{\\I}} {\\^{\\I}} {\\\"{\\I}} {\\`{I}} {\\'{I}} {\\^{I}} {\\\"{I}} {\\~{N}} {\\`{O}} {\\'{O}} {\\^{O}} {\\~{O}} {\\\"{O}} {\\`{U}} {\\'{U}} {\\^{U}} {\\\"{U}} {\\'{Y}} {\\\"{Y}}",
             "Ã€ Ã� Ã‚ Ãƒ Ã„ Ã‡ Ãˆ Ã‰ ÃŠ Ã‹ ÃŒ Ã� ÃŽ Ã� ÃŒ Ã� ÃŽ Ã� Ã‘ Ã’ Ã“ Ã” Ã• Ã– Ã™ Ãš Ã› Ãœ Ã� Å¸"
      ),
      array ('test-Latin-1-upper-braces3', 
             "\\`A \\'A \\^A \\~A \\\"A \\AA \\AE \\c C \\`E \\'E \\^E \\\"E \\`\\I \\'\\I \\^\\I \\\"\\I \\`I \\'I \\^I \\\"I \\~N \\`O \\'O \\^O \\~O \\\"O \\O \\`U \\'U \\^U \\\"U \\'Y \\\"Y \\SS",
             "Ã€ Ã� Ã‚ Ãƒ Ã„ Ã…Ã†Ã‡ Ãˆ Ã‰ ÃŠ Ã‹ ÃŒ Ã� ÃŽ Ã� ÃŒ Ã� ÃŽ Ã� Ã‘ Ã’ Ã“ Ã” Ã• Ã– Ã˜Ã™ Ãš Ã› Ãœ Ã� Å¸ \\SS" //NOTE HOW SPACES AFER UNBRACED \AA AND \AE GET REMOVED (SEE ALSO NEXT TEST)
      ),
      array ('test-Latin-1-upper-spacesAfterUnbracedSymbols', 
             "~\\AA~\\AE~\\SS~\\O~",
             "~Ã…~Ã†~\\SS~Ã˜~" 
      ),
      array ('test-Latin-1-upper-braces4', 
             "\\`{A} \\'{A} \\^{A} \\~{A} \\\"{A} \\c{C} \\`{E} \\'{E} \\^{E} \\\"{E} \\`{\\I} \\'{\\I} \\^{\\I} \\\"{\\I} \\`{I} \\'{I} \\^{I} \\\"{I} \\~{N} \\`{O} \\'{O} \\^{O} \\~{O} \\\"{O} \\`{U} \\'{U} \\^{U} \\\"{U} \\'{Y} \\\"{Y}",
             "Ã€ Ã� Ã‚ Ãƒ Ã„ Ã‡ Ãˆ Ã‰ ÃŠ Ã‹ ÃŒ Ã� ÃŽ Ã� ÃŒ Ã� ÃŽ Ã� Ã‘ Ã’ Ã“ Ã” Ã• Ã– Ã™ Ãš Ã› Ãœ Ã� Å¸"
      )
    );
    //perform all these tests above
	  $this->load->library('bibtex2utf8');
    foreach ($bibtextests as $test) {
      $debugout ="&nbsp;&nbsp;".$test[1]."<br>should be<br>&nbsp;&nbsp;".$test[2]."<br>but rather becomes<br>&nbsp;&nbsp;".$this->bibtex2utf8->bibCharsToUtf8FromString($test[1])."<br>";
      if ($this->bibtex2utf8->bibCharsToUtf8FromString($test[1])!=$test[2]) 
      {
          $result .= __("Test").": ".$test[0]."<br>";
          $result .= " ".utf8_strtoupper(__("Failed")).": <br>";
          if ($debug) $result .= $debugout."<br>";
      } 
      else 
      {
        if ($debug) 
        {
          $result .= __("Test").": ".$test[0]."<br>";
          $result .= " ".utf8_strtoupper(__("Passed"))."<br>";
        } 
          
      }
      $result .= "<br>";
    }
    return $result;
  }
  
  function _testbibtex_singleimport($debug = false)
  {
    $result = "";
    $result .= "<h2>".__("Bibtex single entry imports")."</h2>".__("Note: these tests do not take the review and database result into account, only the result of the bibtex parsing.")."<br><br>";
    //bibtex single entry imports: bibtex code, and a list of expected attributes of the resulting publication 
    $bibtexsingleentrytests = array(
      array ('example', /* IF  you find imports that go 
      wrong, please add them here at this point. This will remind us to fix 
      them. Also, it's kind of a 'prevent this error recurring after it was 
      solved check', so DO NOT REMOVE THOSE IMPORT RESULT CHECKS FROM HERE WHEN YOU 
      FIXED THEM! Rather, start a new array in which people can again add their 
      found errors :) */
             //input:
             "@article{some-bibtex-id,title={The title},month=apr#{~1st},author={Fst von Last and Last2, Fst2}}",
             //an array of pairs for all fields except author and editor: what are the expected values of the fields? 
             array(
               "pub_type"=>"Article",
               "bibtex_id"=>"some-bibtex-id",
               "title"=>"The title",
               "month"=>"\"apr\"~1st"
             ),
             //the expected author result: enter, for each expected author, all values for all four name-parts
             array(
                 array(
                   "firstname" => "Fst",
                   "von"=>"von",
                   "surname"=>"Last",
                   "jr"=>"" 
                 ),
                 array(
                   "firstname" => "Fst2",
                   "von"=>"",
                   "surname"=>"Last2",
                   "jr"=>""
                 )
             ),
             //the expected editor result: enter, for each expected editor, all values for all four name-parts
             array(
             
             )
      ),
      array ('FieldContentThatLooksLikeAListOfFields', /* The entry below contains one field ('note') the content of which looks actually like a list of fields (= characters; komma separation). The brace matching should cause the field to be imported as one field, but it was somehow split wrongly somewhere */
             //input:
             "@TECHREPORT{reidsma2004a,
                    author = {Reidsma, Dennis and Jovanovi{\\'{c}}, Nata{\\v s}a and Hofs, Dennis H. W.},
                     title = {Designing Annotation Tools based on Properties of Annotation Problems},
                      type = {Technical Report},
                    number = {04-45},
                      year = {2004},
               institution = {CTIT},
                   address = {Enschede, NL},
                      note = {ISBN=1381-3625, publisher=CTIT, number of pages=13}
              }",
             //an array of pairs for all fields except author and editor: what are the expected values of the fields? 
             array(
               "pub_type"=>"Techreport",
               "bibtex_id"=>"reidsma2004a",
               "title"=>"Designing Annotation Tools based on Properties of Annotation Problems",
               "type"=>"Technical Report",
               "number"=>"04-45",
               "year"=>"2004",
               "institution"=>"CTIT",
               "address"=>"Enschede, NL",
               "note"=>"ISBN=1381-3625, publisher=CTIT, number of pages=13"
             ),
             //the expected author result: enter, for each expected author, all values for all four name-parts
             array(
                 array(
                   "firstname" => "Dennis",
                   "von"=>"",
                   "surname"=>"Reidsma",
                   "jr"=>"" 
                 ),
                 array(
                   "firstname" => "NataÅ¡a",
                   "von"=>"",
                   "surname"=>"JovanoviÄ‡",
                   "jr"=>""
                 ),
                 array(
                   "firstname" => "Dennis H. W.",
                   "von"=>"",
                   "surname"=>"Hofs",
                   "jr"=>""
                 )
             ),
             //the expected editor result: enter, for each expected editor, all values for all four name-parts
             array(
             
             )
      ),      
      array('macroBug54',
            "
            @String{chap = \"Chap.\"}
            
            @InCollection{Mosses2000CCU,
            author = \"Peter D. Mosses\",
            title = \"{CASL} for {CafeOBJ} Users\",
            chapter = \"6\",
            type = chap,
            pages = \"121--144\",
            booktitle = \"{CAFE}: An Industrial-Strength Algebraic Formal
            Method\",
            publisher = \"Elsevier\",
            year = \"2000\",
            }",
             array(
               "pub_type"=>"Incollection",
               "bibtex_id"=>"Mosses2000CCU",
               "title"=>"{CASL} for {CafeOBJ} Users",
               "chapter"=>"6",
               "type"=>"Chap.",
               "pages"=>"121--144",
               "booktitle" => "{CAFE}: An Industrial-Strength Algebraic Formal Method",
               "publisher" => "Elsevier",
               "year" =>"2000"
             ),
             array(
                 array(
                   "firstname" => "Peter D.",
                   "von"=>"",
                   "surname"=>"Mosses",
                   "jr"=>"" 
                 )
             ),
             array(
             
             )             
      ),
      array('extraBracesBugPDM', //reported through mail by PDM, 2008/11/23
            "@ARTICLE{test-braces, title = {Test braces}, journal = {Test}, year = {2009} }",
             array(
               "pub_type"=>"Article",
               "bibtex_id"=>"test-braces",
               "title"=>"Test braces",
               "journal"=>"Test",
               "year"=>"2009",
               "month"=>""
             ),
             array(),
             array()
      )      
      //next test case:
    );
    $this->load->library('parser_import');
    $this->load->library('parseentries');
    foreach ($bibtexsingleentrytests as $test) {
    $success = true;
      $debugout ="&nbsp;&nbsp;<pre>".$test[1]."</pre><br>";
      //reset parser for next test
      $this->parseentries->reset();
      //parse the $test[1]
      $this->parser_import->loadData($test[1]);
      $this->parser_import->parse($this->parseentries);
      $publications = $this->parser_import->getPublications();
      //inspect the resulting publication object
      //- should be one pub long
      if (count($publications)!=1) 
      {
        $success = false;
        $debugout .= "&nbsp;&nbsp;".sprintf(__("Import should return 1 publication, but returned %s publications."),count($publications))."<br>";
      }
      else 
      {
        $pub = $publications[0];
        //test fields
        foreach ($test[2] as $field=>$value) 
        {
          if ($pub->$field!=$value) 
          {
            $success = false;
            $debugout .= "&nbsp;&nbsp;".sprintf(__("\"%s\" should have been \"%s\" but is \"%s\""),$field,$value,$pub->$field)."<br>";
          }
        }
        //test authors
        $iAuth = 0;
        if (count($pub->authors)!=count($test[3])) 
        {
          $success = false;
          $debugout .= sprintf(__("Import should contain %s authors, but does contain %s authors."),count($test[3]),count($pub->authors))."<br>";
        } 
        else foreach ($pub->authors as $author) 
        {
          $authOk = true;
          $debugout .= __("Author")." ".$iAuth.":<br>";
          if ($author->firstname != $test[3][$iAuth]["firstname"])
          {
            $success = false;
            $authOk = false;
            $debugout .= "&nbsp;&nbsp;".sprintf(__("First name(s) should have been \"%s\" but is \"%s\"."),$test[3][$iAuth]["firstname"],$author->firstname)."<br>";
          }
          if ($author->von != $test[3][$iAuth]["von"])
          {
            $success = false;
            $authOk = false;
            $debugout .= "&nbsp;&nbsp;".sprintf(__("von-part should have been \"%s\" but is \"%s\"."),$test[3][$iAuth]["von"],$author->von)."<br>";
          }
          if ($author->surname != $test[3][$iAuth]["surname"])
          {
            $success = false;
            $authOk = false;
            $debugout .= "&nbsp;&nbsp;".sprintf(__("Last name(s) should have been \"%s\" but is \"%s\"."),$test[3][$iAuth]["surname"],$author->surname)."<br>";
          }
          if ($author->jr != $test[3][$iAuth]["jr"])
          {
            $success = false;
            $authOk = false;
            $debugout .= "&nbsp;&nbsp;".sprintf(__("jr-part should have been \"%s\" but is \"%s\"."),$test[3][$iAuth]["jr"],$author->jr)."<br>";
          }
          if ($authOk) $debugout .= "&nbsp;&nbsp;".__("OK")."<br>";
          $iAuth++;
        }
        //test editors
        $iEd = 0;
        if (count($pub->editors)!=count($test[4])) 
        {
          $success = false;
          $debugout .= "Import should contain ".count($test[4])." editors, but does contain ".count($pub->editors)." editors<br>";
        } 
        else foreach ($pub->editors as $editor) 
        {
          $edOk = true;
          $debugout .= "Editor ".$iEd.":<br>";
          if ($editor->firstname != $test[4][$iEd]["firstname"])
          {
            $success = false;
            $edOk = false;
            $debugout .= "&nbsp;&nbsp;First name(s) should have been \"".$test[4][$iEd]["firstname"]."\" but is \"".$editor->firstname."\"<br>";
          }
          if ($editor->von != $test[4][$iEd]["von"])
          {
            $success = false;
            $edOk = false;
            $debugout .= "&nbsp;&nbsp;von-part should have been \"".$test[4][$iEd]["von"]."\" but is \"".$editor->von."\"<br>";
          }
          if ($editor->surname != $test[4][$iEd]["surname"])
          {
            $success = false;
            $edOk = false;
            $debugout .= "&nbsp;&nbsp;Last name(s) should have been \"".$test[4][$iEd]["surname"]."\" but is \"".$editor->surname."\"<br>";
          }
          if ($editor->jr != $test[4][$iEd]["jr"])
          {
            $success = false;
            $edOk = false;
            $debugout .= "&nbsp;&nbsp;jr-part should have been \"".$test[4][$iEd]["jr"]."\" but is \"".$editor->jr."\"<br>";
          }
          if ($edOk) $debugout .= "&nbsp;&nbsp;OK<br>";
          $iEd++;
        }
      }
      //report result (if debug, or if test failed)
      if ($success==false) 
      {
          $result .= __("Test").": ".$test[0]."<br>";
          $result .= " ".utf8_strtoupper(__("Failed")).": <br>".$debugout."<br>";
      } 
      else 
      {
        if ($debug) 
        {
          $result .= __("Test").": ".$test[0]."<br>";
          $result .= " ".utf8_strtoupper(__("Passed"))."<br>";
        } 
          
      }
      $result .= "<br>";
    }
    return $result;
        
  }

}
?>