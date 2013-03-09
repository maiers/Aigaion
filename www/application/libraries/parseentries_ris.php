<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php


class Parseentries_ris
{
  //Parseentries_ris(): initialize the class
  function Parseentries_ris()
  {
    $this->preamble = $this->strings = $this->entries = array();
    $this->currentLine=1;
    $this->risString="1";
    $this->count=0;
  }

  //getLine(): returns a line if there are more lines to parse, otherwise returns false
  function getLine()
  {
			do
			{
				$sLine = trim($this->risString[$this->currentLine]);
				$this->currentLine++;
			} while ($this->currentLine < count($this->risString) && !$sLine);
			return $sLine;
  }

  //loadImportString($ris_string): loads the inputdata to the risString
  function loadImportString($ris_string)
  {
    if (is_string($ris_string))
    {
		  $this->risString = explode("\n",$ris_string);
		  $risArray = explode("\r", $ris_string);
		  if (count($risArray) > count($this->risString))
		    $this->risString = $risArray;
    }
    else
    {
      $this->risString = $ris_string;
    }
    $this->currentLine = 0;
  }

  function extractEntries()
  {
    $currentEntry = array();
    
    //Following elements can appear more than once in a single entry
    $risAuthorElements = array(
    'AU', //Author primary
    'A1', //Author primary
    'A2', //Author secondary
    'ED', //Author secondary
    'A3'  //Author series
    );
    
    $BibTeXAuthorElements = array(
    'risauthor',
    'risauthor',
    'riseditor',
    'riseditor',
    'riseditor'
    );
    
    $risElements = array (
    'TY', //Type of Reference (Element start)
    'ID', //Element ID
    'T1', //Title primary
    'TI',
    'CT',
    'BT', //Booktitle, maps to primary title if TY == (BOOK || UNPB), else to booktitle
    'T2', //Title secondary, maps to booktitle for inproceedings, incollection
    'T3', //Title series
    'Y1', //Date primary
    'PY',
    'Y2', //Date secondary
    'N1', //Note
    'N2',
    'AB', //Abstract
    'KW', //Keyword, can have multiple per entry
    'RP', //Reprint status
    'JF', //Periodical name, full format
    'JO',
    'JA', //Periodical name, abbreviation
    'J1', //Periodical name, user abbreviation
    'J2',
    'VL', //Volume number
    'IS', //Issue
    'BP', //Startpage
    'SP', //Startpage
    'EP', //Endpage
    'CP', //City of publication
    'CY',
    'PB', //Publisher
    'SN', //ISSN/ISBN, depends on publication type
    'AD', //Address
    'AV', //Availability
    'UR', //Web/URL
    'M1', //User field
    'M2', //DOI (aigaion export)
    'ER'  //End of Reference
    );
    //Please note that the 'Miscellaneous 1-3, User definable 1-5 and Link 1-4 are not supported in this parser
    
    $BibTeXElements = array(
    'risEntryType', //Type of Reference (Element start)
    'bibtexCitation', //Element ID
    'title', //Title primary
    'title',
    'title',
    'booktitle', //Booktitle, maps to primary title if TY == (BOOK || UNPB), else to booktitle
    'booktitle', //Title secondary, maps to booktitle for inproceedings, incollection
    'series', //Title series
    'risyear', //Date primary
    'risyear',
    'risyear', //Date secondary
    'note', //Note
    'note',
    'abstract', //Abstract
    'riskeywords', //Keyword, can have multiple per entry
    'note', //Reprint status
    'journal', //Periodical name, full format
    'journal',
    'journal', //Periodical name, abbreviation
    'journal', //Periodical name, user abbreviation
    'journal',
    'volume', //Volume number
    'number', //Issue
    'startpage', //Startpage
    'startpage', //Startpage
    'endpage', //Endpage
    'address', //City of publication
    'address',
    'publisher', //Publisher
    'ISSNISBN', //ISSN/ISBN, depends on publication type
    'address', //Address
    'note', //Availability
    'url', //Web/URL
    'risuserfield', //ris user field (aigaion export)
    'doi', //DOI (aigaion export)
    'risEOR'
    );
    
    /* Nontrivial items, these need to be handled separately
    'risEntryType', //Needs conversion to bibtexEntryType
    'booktitle',    //Booktitle, maps to primary title if TY == (BOOK || UNPB), else to Title secondary
    'booktitle',    //Title secondary, maps to booktitle for inproceedings, incollection
    'riskeywords',  //Keyword, can have multiple per entry
    'ISSNISBN',     //ISSN/ISBN, depends on publication type
    'year',         //RIS year
    'risEOR',       //RIS End of Reference
    */
    

    //process entire input per line
    while ($this->currentLine < count($this->risString))
    {    
 
      $sLine = $this->getLine();
      $elementName = ''; // NEW LINE
      $matchArray = explode(" -", $sLine); // split the line and store in array $matchArray

      if (count($matchArray) > 1) // there was a " - ", so there seems to be an item. Check if it really is
      {
        $tmpElementName = trim($matchArray[0]);
        if (in_array($tmpElementName, $risElements) ||
            in_array($tmpElementName, $risAuthorElements)) //we found a valid item
        {
          $elementName = trim($tmpElementName);
          $elementName = str_replace($risElements, $BibTeXElements, $elementName);
          $elementName = str_replace($risAuthorElements, $BibTeXAuthorElements, $elementName);

        }
        array_shift($matchArray);
      }
      
      if (count($matchArray) > 1) //there was a " -" in the text, but it was no valid split
      {
        $elementValue = trim(implode(" -", $matchArray));
      }
      else
      {
        $elementValue = trim($matchArray[0]);
      }
      
      
      if (substr($elementName,0,3) == "ris")
      {
        //treat all tags that require special attention
        $elementName = substr($elementName, 3);
        
        //ENTRYTYPE
        if ($elementName == 'EntryType')
        {
          switch (trim($elementValue)) {
            case "JOUR":
              $elementValue = "article";
              break;
            case "JFULL":
              $elementValue = "article";
              break;
            case "BOOK":
              $elementValue = "book";
              break;
            case "PAMP":
              $elementValue = "booklet";
              break;
            case "CHAP":
              $elementValue = "inbook";
              break;
            case "CONF":
              $elementValue = "inproceedings";
              break;
            case "THES":
              $elementValue = "PHDThesis";
              break;
            case "RPRT":
            	$elementValue = "techreport";
            	break;
            case "UNPB":
            	$elementValue = "unpublished";
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
          $date = explode("/", $elementValue);
          $currentEntry['year'] = trim($date[0]);
          
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
        }
        //USERFIELD
        else if ($elementName == 'userfield')
        {
          //check if we can split on '=', if so: there is a fieldname
          $userfield_array = explode("=", $elementValue);
          if (count($userfield_array) == 2) //valid split
          {
            $elementName = trim($userfield_array[0]);
            $elementValue = trim($userfield_array[1]);
          }
          if (!empty($currentEntry[$elementName]))
            $currentEntry[$elementName] .= $elementValue;
          else
            $currentEntry[$elementName] = $elementValue;
        }
        //EOR
        else if ($elementName == 'EOR')
        {
          //BOOKTITLE
          $entryType = $currentEntry['bibtexEntryType'];
          if (!empty($currentEntry['booktitle']))
          {
            if (($entryType == 'book') ||
                ($entryType == 'unpublished') ||
                empty($currentEntry['title']))
            {
              $currentEntry['title'] = $currentEntry['booktitle'];
            }
          }
          //ISSN/ISBN
          if (!empty($currentEntry['ISSNISBN']))
          {
            if (($entryType == 'book') ||
                ($entryType == 'inbook') ||
                ($entryType == 'inproceedings'))
            {
              $currentEntry['isbn'] = $currentEntry['ISSNISBN'];
            }
            else
            {
              $currentEntry['issn'] = $currentEntry['ISSNISBN'];
            }
            unset($currentEntry['ISSNISBN']);
          }
          //PAGES
          $currentEntry['pages'] = '';
          if (!empty($currentEntry['startpage']))
          {
            $currentEntry['pages'] = $currentEntry['startpage'];
            unset($currentEntry['startpage']);
          }
          if (!empty($currentEntry['endpage']))
          {
            $currentEntry['pages'].= "--".$currentEntry['endpage'];
            unset($currentEntry['endpage']);
          }
          
          //COPY TO OUTPUT ENTRIES ARRAY
          $entryNr = $this->count;
          foreach ($currentEntry as $key=>$value)
          {
            $this->entries[$entryNr][$key] = $value;
          }

          $currentEntry = array();
          $this->count++;
        }
        
      }
      else if (empty($currentEntry[$elementName]))
      {
        $currentEntry[$elementName] = $elementValue;
      }
      else
      {
        if (strtolower($currentEntry[$elementName]) != strtolower($elementValue))
          $currentEntry[$elementName] .= ", ".$elementValue;
      }
    }
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