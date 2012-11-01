<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/*
Released through http://bibliophile.sourceforge.net under the GPL licence.
Do whatever you like with this -- some credit to the author(s) would be appreciated.

A collection of PHP classes to manipulate bibtex files.

If you make improvements, please consider contacting the administrators at bibliophile.sourceforge.net so that your improvements can be added to the release package.

Mark Grimshaw 2004/2005
http://bibliophile.sourceforge.net

28/04/2005 - Mark Grimshaw.
	Efficiency improvements.

11/02/2006 - Dennis Reidsma.
	Changes to preg_matching to account for Latex characters in names such as {\"{o}}
	
15/09/2006 - Wietse Balkema
	Rewrite of entire function. Testsuite from Xaivier Decoret now runs fine
		http://artis.imag.fr/~Xavier.Decoret/resources/xdkbibtex/bibtex_summary.html#splitting_examples
*/
// For a quick command-line test (php -f PARSECREATORS.php) after installation, uncomment these lines:

/***********************
	$authors = "Mark \~N. Grimshaw and Bush III, G.W. & M. C. {H{\\'a}mmer Jr.} and von Frankenstein, Ferdinand Cecil, P.H. & Charles Louis Xavier Joseph de la Vallee P{\\\"{o}}ussin";
	$creator = new PARSECREATORS();
	$creator->separateInitials = true;
	$creatorArray = $creator->parse($authors);
	print_r($creatorArray);
***********************/
require_once(APPPATH."include/utf8/trim.php");

class Parsecreators
{
	function Parsecreators()
	{
		//if true, separate initials from firstname
		$this->separateInitials = false;
	}
	
	/* Create writer arrays from bibtex input.
	'author field can be (delimiters between authors are 'and' or '&'):
	 There are three possible cases:
	 1: First von Last
	 2: von Last, First
	 3: von Last, Jr, First
*/
	function parse($input)
	{
		$input = utf8_trim($input);
		
		//remove linebreaks
		$input = preg_replace('/[\r\n\t]/', ' ', $input);
		
		if (preg_match('/\s&\s/', $input))
		{
			$authorArray = $this->explodeString(" & ", $input);
			$input = implode(" and ", $authorArray);
		}
		// split on ' and '
		$authorArray = $this->explodeString(" and ", $input);
		
		foreach($authorArray as $value)
		{
			$firstname = $initials = $von = $surname = $jr = "";
			$this->prefix = array();
	
			//get rid of multiple spaces
			$value = preg_replace("/\s{2,}/", ' ', trim($value));

			$commaAuthor = $this->explodeString(",", $value);
			$size = sizeof($commaAuthor);

			if ($size == 1) //First von Last
			{
				// First: longest sequence of white-space separated words starting with an uppercase and that is not the whole string.
				// von: longest sequence of whitespace separated words whose last word starts with lower case and that is not the whole string.
				// Then Last is everything else.
				// Lastname cannot be empty

				$author = $this->explodeString(" ", $value);
				if (count($author) == 1)
					$surname = $author[0];

				else
				{
					$tempFirst = array();

					$case = $this->getStringCase($author[0]);
					while ((($case == "upper") || ($case == "none")) && (count($author) > 0))
					{
					    $tempFirst[] = array_shift($author);
					    if(!empty($author))
					        $case = $this->getStringCase($author[0]);
					} 
					
					list($von, $surname) = $this->getVonLast($author);

					if ($surname == "")
					{
						$surname = array_pop($tempFirst);
					}
					$firstname = implode(" ", $tempFirst);
				}
			}
			elseif ($size == 2)
			{
				// we deal with von Last, First
				// First: Everything after the comma
				// von: longest sequence of whitespace separated words whose last word starts with lower case and that is not the whole string.
				// Then Last is everything else.
				// Lastname cannot be empty
				$author = $this->explodeString(" ", $commaAuthor[0]);
				if (count($author) == 1)
				$surname = $author[0];

				else
				{
					list($von, $surname) = $this->getVonLast($author);
				}
				$firstname = $commaAuthor[1];
			}
			else
			{
				// we deal with von Last, Jr, First
				// First: Everything after the comma
				// von: longest sequence of whitespace separated words whose last word starts with lower case and that is not the whole string.
				// Then Last is everything else.
				// Lastname cannot be empty
				$author = $this->explodeString(" ", $commaAuthor[0]);
				if (count($author) == 1)
				$surname = $author[0];

				else
				{
					list($von, $surname) = $this->getVonLast($author);
				}
				$jr = $commaAuthor[1];
				$firstname = $commaAuthor[2];
			}

			$firstname = trim($firstname);
			$von = trim($von);
			$surname = trim($surname);
			$jr = trim($jr);
			
			$firstname = $this->formatFirstname($firstname);
			if($this->separateInitials)
				list($firstname, $initials) = $this->separateInitials($firstname);
			
			$creators[] = array('firstname' => utf8_trim($firstname), 
			                    'initials'  => utf8_trim($initials), 
			                    'surname'   => utf8_trim($surname), 
			                    'jr'        => utf8_trim($jr), 
			                    'von'       => utf8_trim($von));
		}
		if(isset($creators))
			return $creators;
		return FALSE;
	}

	//gets the "von" and "last" part from the author array
	function getVonLast($author)
	{
		$surname = $von = "";
		$tempVon = array();
		$count = 0;
		$bVon = false;
		foreach ($author as $part)
		{
			$case = $this->getStringCase($part);
			if ($count == 0)
			{
				if ($case == "lower")
				{
					$bVon = true;
					if ($case == "none")
					$count--;
				}
			}

			if ($bVon)
			$tempVon[] = $part;

			else
			$surname = $surname." ".$part;

			$count++;
		}

		if (count($tempVon) > 0)
		{
			//find the first lowercase von starting from the end
			for ($i = (count($tempVon)-1); $i > 0; $i--)
			{
				if ($this->getStringCase($tempVon[$i]) == "lower")
					break;
				else
				$surname = array_pop($tempVon)." ".$surname;
			}

			if ($surname == "") // von part was all lower chars, the last entry is surname
				$surname = array_pop($tempVon);

			$von = implode(" ", $tempVon);
		}
		return array(trim($von), trim($surname));
	}

	// Explodes a string but not when the delimiter occurs within a pair of braces
	function explodeString($delimiter, $val)
	{
		$bracelevel = $i = $j = 0;
		$len = utf8_strlen($val);
		if (utf8_strlen($delimiter) > 1)
		{
			$long = true;
			$dlen = utf8_strlen($delimiter);
		}
		else
			$long = false;
			
		$strings = array();
		while ($i < $len)
		{
			if ($val[$i] == '{')
			$bracelevel++;
			elseif ($val[$i] == '}')
			$bracelevel--;
			elseif (!$bracelevel)
			{
				if ($long)
				{
					if (substr($val, $i, $dlen) == $delimiter)
					{
						$strings[] = substr($val,$j,$i-$j);
						$j=$i+$dlen;
						$i += ($dlen - 1);
					}						
				}
				else
				{
					if ($val[$i] == $delimiter)
					{
						$strings[] = substr($val,$j,$i-$j);
						$j=$i+1;
					}
				}
			}
			$i++;
		}
		$strings[] = substr($val,$j);
		return $strings;
	}

	// returns the case of a string
	// Case determination:
	// non-alphabetic chars are caseless
	// the first alphabetic char determines case
	// if a string is caseless, it is grouped to its neighbour string.
	function getStringCase($string)
	{
		$caseChar = "";
		$string = preg_replace("/\d/", "", $string);
		if (preg_match("/{/", $string))
		$string = preg_replace("/({[^\\\\.]*})/", "", $string);

		if (preg_match("/\w/", $string, $caseChar))
		{
			if (is_array($caseChar))
			$caseChar = $caseChar[0];

			if (preg_match("/[a-z]/", $caseChar))
			return "lower";

			else if (preg_match("/[A-Z]/", $caseChar))
			return "upper";

			else
			return "none";

		}
		else
		return "none";
	}

	//converts a first name to initials
	function getInitials($firstname)
	{
		$initials = '';
		$name = explode(' ', $firstname);
		foreach ($name as $part)
		{
			$size = utf8_strlen($part);
			if (($part{($size-1)} == ".") && ($size < 4))
				$initials .= $part;
			elseif (preg_match("/([A-Z])/", $part, $firstChar))
				$initials .= $firstChar[0].". ";
		}
		return utf8_trim($initials);
	}
	
	//separates initials form a firstname
	function separateInitials($firstname)
	{
		$name = $this->explodeString(" ", $firstname);
		$initials = array();
		$remain = array();
/*
		//bibtex-conform
		foreach ($name as $part)
		{
			if (($part{($size-1)} == ".") && ($size < 4))
				$initials[] = $part;
			else
				$remain[] = $part;
		}
*/
		//old-parser conform
		foreach ($name as $part)
		{
			$size = utf8_strlen($part);
			
			if(preg_match("/[a-zA-Z]{2,}/", utf8_trim($part)))
				$remain[] = utf8_trim($part);
			else
				$initials[] = str_replace(".", " ", utf8_trim($part));
		}
		if(isset($initials))
		{
			$initials_ = '';
			foreach($initials as $initial)
				$initials_ .= ' ' . utf8_trim($initial);
				
			$initials = $initials_;
		}
		//end of old-parser conform
		
		//enable for new-parser conform
		//$initials = implode(" ", $initials);
		$firstname = str_replace('.', '', implode(" ", $remain));
		return array($firstname, $initials);
	}
	
	function formatFirstname($firstname)
	{
		if ($firstname == "")
			return "";
		$name = $this->explodeString(".", $firstname);
		$formatName = "";
		$count = 1;
		$size = count($name);
		foreach ($name as $part)
		{
			$part = utf8_trim($part);
			
			if ($part != "")
			{
				$formatName .= $part;
				
				if ($count < $size)
				{
				  //if the end of part contains an escape character (either just \ or \{, we do not add the extra space
				  if (($part{utf8_strlen($part)-1} == "\\") || ($part{utf8_strlen($part)-1} == "{"))
				    $formatName.=".";
				  else
					  $formatName.=". ";
				}
			}
			$count++;
		}
		return $formatName;
	}
}
?>