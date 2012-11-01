<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/*
Released through http://bibliophile.sourceforge.net under the GPL licence.
Do whatever you like with this -- some credit to the author(s) would be appreciated.

A collection of PHP classes to manipulate bibtex files.

If you make improvements, please consider contacting the administrators at bibliophile.sourceforge.net 
so that your improvements can be added to the release package.

Mark Grimshaw 2005
http://bibliophile.sourceforge.net

2/10/2006 - Wietse Balkema
	changed parsing behaviour if no valid split is possible:
	- if completeField = TRUE, the entire item is returned as firstpage
	- if completeField = FALSE, the first valid number is returned as firstpage

*/
/*****
*	PARSEPAGE: BibTeX PAGES import class
*****/

/* We now correctly parse:
"77--99", "3 - 5", "IV -- XI","73", "73+", "-73", "73,89,103","034524-1 -- 034524-4";
*/
class Parsepage
{
	// Constructor
	function Parsepage()
	{
		$this->completeField = TRUE;
	}

	//calls the page parser and returns an array with first- and lastpage
	function init($item)
	{
		$item = trim($item);
		if ($this->parsePages($item))
			return $this->return;
		elseif ($this->completeField) //if true, return the complete item, else return only the first number found.
			return array($item, FALSE);
		elseif (preg_match("/([\divxIVX]+)/i", $item, $array))
			return array($array[1], FALSE);
		else
			return array(FALSE, FALSE);
	}

	//parsePages tries to split on '--' or '-' (in case no valid split on '--' is possible.
	//if the split results in 2 elements, the split is considered valid.
	function parsePages($pages)
	{
		$start = $end = FALSE;
		$elements = preg_split("/--/", $pages);
		//first split on the valid bibtex page separator
		if (count($elements) == 1) 
		{
			//no '--' found, try on single '-'
			$elements = preg_split("/-/", $pages);
		}
		if (count($elements) == 2)
		{	
			//found valid pages that are separated by '--' or by '-'
			$start = trim($elements[0]);
			$end = trim($elements[1]);
			$this->return = array($start, $end);
			return TRUE;
		}
		return FALSE;
	}
}
?>