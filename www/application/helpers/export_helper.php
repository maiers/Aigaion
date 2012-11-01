<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for export functions.
| -------------------------------------------------------------------
|
|   This helper contains functions to export to BibTeX and RIS
|
|	Usage:
|       //load this helper:
|       $this->load->helper('export'); 
|       //get export text for an entry
|       $bibtex = getBibtexForPublication($publication);
|       $ris    = getRISForPublication($publication);
|       $formattedExport = getOSBibFormattingForPublication($publication,$bibformat,$style = "apa",  $format = "html")
|
|   note the following: if a field is set to "" in the database (two double quotes), those double quotes 
|   will be stripped during export, resulting in an explicitly empty field.
*/

require_once(APPPATH."include/utf8/trim.php");
    
    /** returns formatted bibtex for this publication object. Does not do any crossref merging. */
    function getBibtexForPublication($publication) {
        $CI = &get_instance();
        $CI->load->library('bibtex2utf8');
        $CI->load->helper('string');
        $CI->load->helper('publication');
        $userlogin = getUserLogin();
        $fields = array();
        $maxfieldname=0;
        //open entry
        $result = '@'.strtoupper($publication->pub_type).'{'.$publication->bibtex_id.",\n";
        //collect authors
        $authors = "";
        $first = true;
        foreach ($publication->authors as $author) {
            if (!$first) $authors .= " and ";
            $first = false;
            $authors .= $author->getName('vlf');
        }
        $fields['author']=$authors;
        //collect editors
        $editors = "";
        $first = true;
        foreach ($publication->editors as $editor) {
            if (!$first) $editors .= " and ";
            $first = false;
            $editors .= $editor->getName('vlf');
        }
        $fields['editor']=$editors;
        //collect keywords
        $keywords = "";
        $first = true;
        foreach ($publication->getKeywords() as $keyword) {
            if (!$first) $keywords .= ", ";
            $first = false;
            $keywords .= $keyword->keyword;
        }
        $fields['keywords']=$keywords;
        //key is named 'namekey' in the database, because key can be a reserved word
        $fields['key'] = $publication->namekey;
        $fields['month'] = $publication->month;
        //initial maxfieldname: the longest of the above collected fields
        $maxfieldname = 8;
        //process user fields
        //see old bibtex export for how to export userfields? Directly DUMP user fields? (but what about layout :( )
        $done = array('author',
                      'editor',
                      'keywords',
                      'firstpage',
                      'lastpage',
                      'pub_type',
                      'bibtex_id',
                      'userfields',
                      'namekey',
                      'month');
        //a list of all fields that can be converted to bibtex codes...
        $utf8ConvertFields = array(
                  'author'          ,
                  'editor'          ,
                  'title'          ,
                  'journal'        ,
                  'booktitle'      ,
                  'edition'        ,
                  'series'         ,
                  'volume'         ,
                  'number'         ,
                  'chapter'        ,
                  'year'           ,
                  'month'          ,
                  'pages'      ,
                  'pages'		   ,
                  'publisher'      ,
                  'location'       ,
                  'institution'    ,
                  'organization'   ,
                  'school'         ,
                  'address'        ,
                  'howpublished'   ,
                  'note'           ,
                  'keywords'       ,
                  'abstract'       ,
                  'issn'           ,
                  'isbn'           ,
                  'namekey'        );
        $omitifzero = array('chapter','year');
        //now add all other fields that are relevant for exporting
        foreach (getFullFieldArray() as $field) {
            if (!in_array($field,$done) && (utf8_trim($publication->$field)!='')) {
                if (!in_array($field,$omitifzero)||($publication->$field!='0'&&$publication->$field!='0000')) {
                    $fields[$field]=$publication->$field;
                    $maxfieldname = max(strlen($field),$maxfieldname);
                }
            }
        }
        //add all customfields
        $customfields = $publication->getCustomFields();
        if (is_array($customfields))
        {
          foreach ($customfields as $customfield)
          {
            if (utf8_trim($customfield['value'])!='') {
              $fields[$customfield['fieldname']]=$customfield['value'];
              $maxfieldname = max(strlen($customfield['fieldname']),$maxfieldname);
            }
          }
        }
        
        
        
        //process fields array, converting to bibtex special chars as you go along.
        //maxfieldname determines the adjustment of the field names
        $spaces = repeater(' ',$maxfieldname);
        $first = True;
        foreach ($fields as $name=>$value) {
            if ($value!='') {
                if ($first) {
                    $first = False;
                } else {
                    $result .= ",\n";
                }
                //note: fields containing "" are exported as explicitly empty fields....
                if (utf8_trim($value)=='""') $value='';
                if (($userlogin->getPreference('utf8bibtex')=='TRUE')||!in_array($name,$utf8ConvertFields)) {
                    if($name=="month")
                    {
                      $printval = "{".formatMonthBibtex($value)."}";
                      $printval = preg_replace("/^\\{\\}\\#/","",$printval);
                      $printval = preg_replace("/\\#\\{\\}\z/","",$printval);
                      $result .= "  ".substr($spaces.$name,-$maxfieldname)." = ".$printval;
                    } 
                    else 
                    {
                      $result .= "  ".substr($spaces.$name,-$maxfieldname)." = {".$value."}";
                    }
                } else {
                  if($name=="month")
                  {
                    $printval = "{".formatMonthBibtex($CI->bibtex2utf8->utf8ToBibCharsFromString($value))."}";
                    $printval = preg_replace("/^\\{\\}\\#/","",$printval);
                    $printval = preg_replace("/\\#\\{\\}\z/","",$printval);
                    $result .= "  ".substr($spaces.$name,-$maxfieldname)." = ".$printval;
                  } 
                  else 
                  { 
                    $result .= "  ".substr($spaces.$name,-$maxfieldname)." = {".$CI->bibtex2utf8->utf8ToBibCharsFromString($value)."}";
                  }
                }
            }
        }
        
        //hmmm -- could have done better layout here for userfields
        if (utf8_trim($publication->userfields)!='') {
            if (!$first) {
                $result .= ",\n";
            }
            if ($userlogin->getPreference('utf8bibtex')=='TRUE') {
                $result .= $publication->userfields;
            } else {
                $result .= $CI->bibtex2utf8->utf8ToBibCharsFromString($publication->userfields);
            }
        }
        
        
        //close entry
        $result .= "\n}\n";    
        return $result;
    }  

    /** returns formatted RIS for this publication object. Does not do any crossref merging. */
    function getRISForPublication($publication) {
        $CI = &get_instance();
        $CI->load->helper('string');
        $CI->load->helper('publication');
        
        $result = '';
       	$result .= "TY  - ".getRISEntryType($publication->pub_type)."\n";
	    if ($publication->bibtex_id != "") 
		    $result .= "ID  - ".$publication->bibtex_id."\n";
	
        $result .= getRISExportLine('T1',$publication->title);
    	foreach ($publication->authors as $author) 
	    	$result .= getRISExportLine('A1', $author->getName('vlf'));
    	foreach ($publication->editors as $editor) 
	    	$result .= getRISExportLine('ED', $editor->getName('vlf'));
        $result .= getRISExportLine('JA',$publication->journal);
        $result .= getRISExportLine('TI',$publication->booktitle);
        $result .= getRISExportLine('T3',$publication->series);
        if ($publication->year!='0000')
            $result .= getRISExportLine('Y1',$publication->year);
        $result .= getRISExportLine('VL',$publication->volume);
        $result .= getRISExportLine('M1',$publication->type);
        $result .= getRISExportLine('IS',$publication->number);
        $CI->load->library('parsepage');
        list($start, $end) = $CI->parsepage->init($publication->pages);
        if (isset($start)&&($start!=''))
    		$result .= getRISExportLine("SP", $start);
        if (isset($end)&&($end!=''))
    		$result .= getRISExportLine("EP", $end);
        $result .= getRISExportLine('U1',$publication->edition);
        if ($publication->chapter != 0)
            $result .= getRISExportLine('U2',$publication->chapter);
        $result .= getRISExportLine('PB',$publication->publisher);
        $result .= getRISExportLine('T2',$publication->school);
        $result .= getRISExportLine('T2',$publication->organization);
        $result .= getRISExportLine('T2',$publication->institution);
        $result .= getRISExportLine('CY',$publication->location);
        $result .= getRISExportLine('AD',$publication->address);
        $result .= getRISExportLine('SN',$publication->isbn);
        $result .= getRISExportLine('SN',$publication->issn);
        $result .= getRISExportLine('N1',$publication->note);
        $result .= getRISExportLine('UR',$publication->url);
        $result .= getRISExportLine('M2',$publication->doi);
        foreach($publication->getKeywords() as $keyword)
          $result .= getRISExportLine("KW", utf8_trim($keyword->keyword));
        $result .= getRISExportLine('N2',$publication->abstract);

        //add all customfields
        $customfields = $publication->getCustomFields();
        if (is_array($customfields))
        {
          foreach ($customfields as $customfield)
          {
            if (utf8_trim($customfield['value'])!='') {
              $result .= "M1  - ".$customfield['fieldname'].' = '.$customfield['value']."\n";
            }
          }
        }
        
        //DR: note: the below is not correct! if a userfield contains a comma we have a problem!
      	if (utf8_trim($publication->userfields) != "") {
      		$field = strtok($publication->userfields,",");
      		while (strlen($field) > 0)
      		{
      			$result .= "M1  - ".$field."\n";
      			$field = strtok(",");
      		}
      	}
        
        //close entry
        $result .= "ER  -\n";
        return $result;
    }          
    function getRISExportLine($field,$value) {
    	//U1: edition
    	//U2: chapter
    	//M2: used here as 'DOI'
    	$result = "";
    	if (utf8_trim($value) != "")
    	{
          //note: fields containing "" are exported as explicitly empty fields....
          if (utf8_trim($value)=='""') $value='';
    	  if ($field == "U1") $value = "Edition: ".$value;
    	  if ($field == "U2") $value = "Chapter: ".$value;
    	  if ($field == "M2") $value = "doi: ".$value;
    	  $result .= $field."  - ".urldecode($value)."\n";
    	}
    	return $result;
    }
        
    function getRISEntryType($bibEntryType)
    {
    	$return = "";
    	switch($bibEntryType) {
    		case "Article":
    			$return = "JOUR";
    		break;
    		case "Book":
    			$return = "BOOK";
    		break;
    		case "Booklet":
    			$return = "PAMP";
    		break;
    		case "Inbook":
    			$return = "CHAP";
    		break;
    		case "Incollection":
    			$return = "CHAP";
    		break;
    		case "Inproceedings":
    			$return = "CONF";
    		break;
    		case "Manual":
    			$return = "GEN ";
    		break;
    		case "Mastersthesis":
    			$return = "RPRT";
    		break;
    		case "Misc":
    			$return = "GEN ";
    		break;
    		case "Phdthesis":
    			$return = "THES";
    		break;
    		case "Proceedings":
    			$return = "CONF";
    		break;
    		case "Techreport":
    			$return = "RPRT";
    		break;
    		case "Unpublished":
    			$return = "UNPB";
    		break;
    		default:
    		break;
    	}
    	return $return;
    }
    
    function getOSBibFormattingForPublication($publication,$bibformat,$style = "apa",  $format = "html") {
        $CI = &get_instance();
        $CI->load->library('bibtex2utf8');
        $CI->load->helper('string');
        $CI->load->helper('publication');
    	$bibformat->output=$format;
    	//$bibformat->cleanEntry=TRUE; //-- If TRUE, convert BibTeX (and LaTeX) special characters to UTF-8. Default is FALSE.
    	$bibformat->bibtexParsePath = APPPATH."libraries";
    
    	list($info, $citation, $footnote, $styleCommon, $styleTypes) = $bibformat->loadStyle(APPPATH."include/OSBib/styles/bibliography/", $style);
    	$bibformat->getStyle($styleCommon, $styleTypes, $footnote);
    
    	# $resourceArray must be an array of all the elements in the resource where the key names are valid, lowercase BibTeX field names. 
    	//start collecting info from publication...
        $userlogin = getUserLogin();
        $fields = array();
        //collect authors
        $authors = "";
        $first = true;
        foreach ($publication->authors as $author) {
            if (!$first) $authors .= " and ";
            $first = false;
            $authors .= $author->getName('vlf');
        }
        $fields['author']=$authors;
        //collect editors
        $editors = "";
        $first = true;
        foreach ($publication->editors as $editor) {
            if (!$first) $editors .= " and ";
            $first = false;
            $editors .= $editor->getName('vlf');
        }
        $fields['editor']=$editors;
        //collect keywords
        $keywords = "";
        $first = true;
        foreach ($publication->getKeywords() as $keyword) {
            if (!$first) $keywords .= ",";
            $first = false;
            $keywords .= $keyword->keyword;
        }
        $fields['keywords']=$keywords;

        //key is named 'namekey' in the database, because key can be a reserved word
        $fields['key'] = $publication->namekey;
        //month is a number in the database...
        $fields['month'] = formatMonthText($publication->month);
        //process user fields?
        $done = array('author',
                      'editor',
                      'keywords',
                      'pub_type',
                      'bibtex_id',
                      'userfields',
                      'firstpage',
                      'lastpage',
                      'namekey',
                      'month');
        $omitifzero = array('chapter','year');
        //now add all other fields that are relevant for exporting
        foreach (getFullFieldArray() as $field) {
            if (!in_array($field,$done) && (utf8_trim($publication->$field)!='')) {
                if (!in_array($field,$omitifzero)||($publication->$field!='0'&&$publication->$field!='0000')) {
                    $fields[$field]=$publication->$field;
                }
            }
        }
        $resourceArray=array();
        foreach ($fields as $field=>$value) {
            //note: fields containing "" are exported as explicitly empty fields....
            if (utf8_trim($value)=='""') $value='';
            $resourceArray[$field] = $value;
        }
        $resourceType = strtolower($publication->pub_type);
        $resourceArray['bibtexEntryType'] = $resourceType;
    
    	//$resourceType = "dfgh";
    	//print_r($resourceArray);
    	//echo $resourceType;
    	//echo $resourceArray['bibtexEntryType'];
    
    	# FIX types for preprocess :(
    	switch ($resourceType) {
    		case 'mastersthesis':
    			$resourceType = 'thesis';
    			$resourceArray['type'] = "Master's Dissertation";
    		break;
    		case 'phdthesis':
    			$resourceType = 'thesis';
    			$resourceArray['type'] = "PhD Thesis";
    		break;
    		case 'booklet':
    			$resourceType = 'miscellaneous';
    		break;
    		case 'conference':
    			$resourceType = 'proceedings_article';
    		break;
    		// case 'incollection':
    			// $resourceType = 'book_article';
    		// break;
    		case 'manual':
    			$resourceType = 'report';
    		break;
    	}
    
    	// In this case, BIBFORMAT::preProcess() adds all the resource elements automatically to the BIBFORMAT::item array...
    	if ($bibformat->preProcess($resourceType, $resourceArray)) {
    		// Finally, get the formatted resource string ready for printing to the web browser or exporting to RTF, OpenOffice or plain text
    		if ($format=="rtf") {
    			$CI->load->helper('rtf');
    			$rtf = new MINIMALRTF();
    			return $rtf->utf8_2_unicode($bibformat->map());
    		} else {
    			return $bibformat->map(); //$utf8->decodeUtf8($bibformat->map());
    		}
    		// process loop ends here
    	}
    	return __("No format possible").".<br>";
    }
?>