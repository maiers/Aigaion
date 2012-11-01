<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php


class Parseentries_refer
{
  //Parseentries_refer(): initialize the class
  function Parseentries_refer()
  {
    $this->preamble = $this->strings = $this->entries = array();
    $this->currentLine=1;
    $this->referString="1";
    $this->count=0;
  }

  //getLine(): returns a line if there are more lines to parse, otherwise returns false
  function getLine()
  {
			do
			{
				$sLine = trim($this->referString[$this->currentLine]);
				$this->currentLine++;
			} while ($this->currentLine < count($this->referString) && !$sLine);
			return $sLine;
  }

  //loadImportString($refer_string): loads the inputdata to the referString
  function loadImportString($refer_string)
  {
    if (is_string($refer_string))
    {
		  $this->referString = explode("\n",$refer_string);
		  $risArray = explode("\r", $refer_string);
		  if (count($risArray) > count($this->referString))
		    $this->referString = $risArray;
    }
    else
    {
      $this->referString = $refer_string;
    }
    $this->currentLine = 0;
  }

  function extractEntries()
  {
    $currentEntry = array();
    
    //Following elements can appear more than once in a single entry
    $referAuthorElements = array(
    '%A', //Author
    '%E', //Editor
    '%Y'  //Series editor
    );
    
    $BibTeXAuthorElements = array(
    'referauthor',
    'refereditor',
    'referserieseditor'
    );
    
    $referElements = array (
    '%0', //Type of Reference (Element start)
    '%T', //Title of article or book
    '%S', //Title of the series
    '%J', //Title of journal
    '%B', //Title of book containing article / Journal title
    '%R', //Report type: Report, Paper or Thesis
    '%V', //Volume
    '%N', //Number with volume
    '%P', //Page numbers
    '%I', //Issuer -> publisher
    '%C', //City of publisher
    '%D', //Date of publication
    '%O', //Ohter information
    '%K', //Keywords
    '%L', //label
    '%X', //Abstract
    '%@', //ISSN
    '%+', //Affiliation (presumably for the corresponding author / all
    '%1', //DOI number
    '%U', //URL
    '%6', //Number of volumes
    '%7', //Edition
    '%8', //date of conference
    '%9'  //howpublished
    );
    
    $BibTeXElements = array(
    'referEntryType', //Type of Reference (Element start)
    'title', //Title of article or book
    'refertitle', //Title of the series
    'refertitle', //Title of journal
    'refertitle', //Title of book containing article / Journal title
    'type', //Report type: Report, Paper or Thesis
    'volume', //Volume
    'volume', //Number with volume
    'pages', //Page numbers
    'publisher', //Issuer -> publisher
    'address', //City of publisher
    'referyear', //Date of publication
    'note', //Ohter information
    'keywords', //Keywords
    'bibtexCitation', //label
    'abstract', //Abstract
    'ISSNISBN', // ISSN
    'organization', // Affiliation (presumably for the corresponding author / all
    'doi', // DOI number
    'url', // URL
    'volumes', //Number of volumes
    'edition', //Edition
    'referyear', //date of conference
    'howpublished'  //howpublished
    );
    
    //process entire input per line
    while ($this->currentLine < count($this->referString))
    {    
 
      $sLine = $this->getLine();
      $matchArray = explode(" ", $sLine); // split the line and store in array $matchArray
      if (count($matchArray) > 1) // there was a " ", so there seems to be an item. Check if it really is
      {
        $tmpElementName = trim($matchArray[0]);
        if (in_array($tmpElementName, $referElements) ||
            in_array($tmpElementName, $referAuthorElements)) //we found a valid item
        {
          $elementName = $tmpElementName;
        }
        array_shift($matchArray);
      }
      
      //When we are here, $elementName as either a new value, or still its old value (no new element on this line)
      
      if (count($matchArray) > 1) //there was a " " in the text, but it was no valid split
      {
        $elementValue = implode(" ", $matchArray);
      }
      else
      {
        $elementValue = $matchArray[0];
      }
      
      $elementName = str_replace($referElements, $BibTeXElements, $elementName);
      $elementName = str_replace($referAuthorElements, $BibTeXAuthorElements, $elementName);
      
      if (substr($elementName,0,5) == "refer")
      {
        //treat all tags that require special attention
        $elementName = substr($elementName, 5);
        
        //ENTRYTYPE
        if ($elementName == 'EntryType')
        {
          //check if there already is one, if so, we completed one entry.
          if (isset($currentEntry['bibtexEntryType']))
          {
            $this->addEntry($currentEntry);
            unset($currentEntry);
            $currentEntry = array();
          }
          
          switch (trim(strtolower($elementValue))) {
            case "book":
            case "edited book":
              $elementValue = "book";
              break;
            case "book section":
              $elementValue = "inbook";
              break;
            case "conference proceedings":
              $elementValue = "inproceedings";
              break;
            case "journal article":
            case "magazine article":
            case "newspaper article":
              $elementValue = "article";
            case "report":
            case "thesis":
              //do nothing, these are valid also in bibtex
              break;
            default:
              $elementValue = "misc";
              break;
          }
          $currentEntry['bibtexEntryType'] = $elementValue;
        }
        
        //AUTHOR / EDITOR
        else if (($elementName == 'author') || ($elementName == 'editor'))
        {
          if (empty($currentEntry[$elementName]))
          {
            $currentEntry[$elementName] = $elementValue;
          }
          else
          {
            $currentEntry[$elementName] .= " and ".$elementValue;
          }
        }
        //KEYWORDS
        else if ($elementName == 'keywords')
        {
          if (empty($currentEntry[$elementName]))
          {
            $currentEntry[$elementName] = $elementValue;
          }
          else
          {
            $currentEntry[$elementName] .= ", ".$elementValue;
          }
        }
        //YEAR
        else if ($elementName == 'year')
        {
          $refYear  = "";
          $refMonth = "";
          //extract year and month
          if (preg_match("/(\d{4})/", $elementValue, $year) == 1)
            $refYear = $year[1];
          
          $monthArray = array('jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec');
          foreach ($monthArray as $mth) {
            if (preg_match("/(".$mth.")/i", $elementValue, $month) == 1)
              $refMonth = strtolower($month[1]);
          }
          
          if ($year != "")
          {
            $currentEntry['year'] = $refYear;
          }
          if ($refMonth != "")
          {
            $currentEntry['month'] = $refMonth;
          }
          
          /* RIS date parse
          $date = explode("/", $elementValue);
          $currentEntry['year'] = $date[0];
          
          if (count($date) > 1)
          { 
            $month = $date[1];
            switch ($month)
            {
              case 1: $currentEntry["month"]="jan";break;
              case 2: $currentEntry["month"]="feb";break;
              case 3: $currentEntry["month"]="mar";break;
              case 4: $currentEntry["month"]="apr";break;
              case 5: $currentEntry["month"]="may";break;
              case 6: $currentEntry["month"]="jun";break;
              case 7: $currentEntry["month"]="jul";break;
              case 8: $currentEntry["month"]="aug";break;
              case 9: $currentEntry["month"]="sep";break;
              case 10: $currentEntry["month"]="oct";break;
              case 11: $currentEntry["month"]="nov";break;
              case 12: $currentEntry["month"]="dec";break;
            }
          }
          */
        }
      }
      else if (empty($currentEntry[$elementName]))
      {
        $currentEntry[$elementName] = trim($elementValue);
      }
      else
      {
        $currentEntry[$elementName] .= " ".trim($elementValue);
      }
    } //end of input
    
    //check if there still is something in the $currentEnty
    if (count($currentEntry) > 0)
    {
      $this->addEntry($currentEntry);
      unset($currentEntry);
      $currentEntry = array();
    }
    
  }

  function addEntry($entry)
  {
    //BOOKTITLE
    $entryType = $entry['bibtexEntryType'];
    if (!empty($entry['booktitle']))
    {
      if (($entryType == 'book') ||
          ($entryType == 'unpublished') ||
          empty($entry['title']))
      {
        $entry['title'] = $entry['booktitle'];
      }
    }
    //ISSN/ISBN
    if (!empty($entry['ISSNISBN']))
    {
      if (($entryType == 'book') ||
          ($entryType == 'inbook') ||
          ($entryType == 'inproceedings'))
      {
        $entry['isbn'] = $entry['ISSNISBN'];
      }
      else
      {
        $entry['issn'] = $entry['ISSNISBN'];
      }
      unset($entry['ISSNISBN']);
    }
    //PAGES
    if (!empty($entry['pages']))
    {
      $entry['pages'] = preg_replace("/(-+?)/", "-", $entry['pages']);
      $entry['pages'] = preg_replace("/-/", "--", $entry['pages']);
    }
    
    //COPY TO OUTPUT ENTRIES ARRAY
    $entryNr = $this->count;
    foreach ($entry as $key=>$value)
    {
      $this->entries[$entryNr][$key] = $value;
    }
    $this->count++;
  }

  function returnArrays()
  {
    if (empty($this->preamble))
      $this->preamble = FALSE;
    if (empty($this->strings))
      $this->strings = FALSE;
    if (empty($this->entries))
      $this->entries = FALSE;
    
    return array($this->preamble, $this->strings, $this->entries);
  }
}

?>