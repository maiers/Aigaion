<?php
/********************************
OSBib:
A collection of PHP classes to create and manage bibliographic formatting for OS bibliography software 
using the OSBib standard.

Released through http://bibliophile.sourceforge.net under the GPL licence.
Do whatever you like with this -- some credit to the author(s) would be appreciated.

If you make improvements, please consider contacting the administrators at bibliophile.sourceforge.net 
so that your improvements can be added to the release package.

Adapted from WIKINDX: http://wikindx.sourceforge.net

Mark Grimshaw 2006
http://bibliophile.sourceforge.net
********************************/
/*****
*	PREVIEWCITE class for in-text citations.
*
*	$Header: /cvsroot/aigaion/webinterface/includes/OSBib/create/PREVIEWCITE.php,v 1.3 2006/12/01 14:27:49 reidsma Exp $
*****/
class PREVIEWCITE
{
	function PREVIEWCITE($vars)
	{
		$this->vars = $vars;
		include_once(OSBIB__MISC);
		include_once(OSBIB__MESSAGES);
		$this->messages = new MESSAGES();
		include_once(OSBIB__ERRORS);
		$this->errors = new ERRORS();
		include_once(OSBIB__CITEFORMAT);
		$this->footnotePages = FALSE;
	}
/**
* display
*
* @param BOOLEAN - FALSE for initial display without user-selected fields, TRUE for user-selected fields from within preview window
* @author Mark Grimshaw
*/
	function display($fieldSelect = FALSE)
	{
		$cite= unserialize(stripslashes(urldecode($this->vars['cite'])));
// Load the test data
		$this->getData();
		include_once(OSBIB__BIBFORMAT);
		$this->bibformat = new BIBFORMAT();
		$this->loadCiteformat($cite);
		return $this->processCite($this->text);
	}
/**
* function loadCiteformat()
*/
	function loadCiteformat($cite)
	{
		$this->citeformat = new CITEFORMAT($this, "processBib", "", "../");
		$this->citeformat->wikindx = FALSE;
		if(!$cite['cite_template'])
			return $this->errors->text("inputError", "missing");
		$this->citeformat->loadArrays();
		foreach($cite as $key => $value)
		{
// Convert javascript unicode e.g. %u2014 to HTML entities
			$value = preg_replace("/%u(\d+)/", "&#x$1;", str_replace('__WIKINDX__SPACE__', ' ', $value)); 
			$this->citeformat->style[str_replace("cite_", "", $key)] = $value;
		}
		$this->citeformat->style['citationStyle'] = FALSE; // Set to in-text citations.
		$this->citeformat->citationToArrayInTextStyle();
	}
/*
* function processBib()
*
* Produce a bibliography from $this->row.
*/
	function processBib($row)
	{
		$id = $row['id'];
// Add the resource type
		$this->bibformat->type = $type = $row['type'];
		$row = $this->bibformat->preProcess($type, $row);
		$type = $this->bibformat->type; // may have been changed in preProcess so reset.
// Add the title.  The 2nd and 3rd parameters indicate what bracketing system is used to conserve uppercase characters in titles.
		$this->createTitleBib();
// Add creator names.  Up to 5 types are allowed - for mappings depending on resource type see STYLEMAP.php
		for($index = 1; $index <= 5; $index++)
		{
			$creatorSlot = 'creator' . $index;
// If we have creator name in this slot OR that creator slot is not defined in STYLEMAP.php, do nothing
			if(array_key_exists($creatorSlot, $row) && 
			array_key_exists($creatorSlot, $this->bibformat->styleMap->$type))
				$this->bibformat->formatNames($row[$creatorSlot], $creatorSlot);
		}
// Edition
		if(array_key_exists('edition', $row) && array_search('edition', $this->bibformat->styleMap->$type))
			$this->bibformat->formatEdition($row['edition']);
// Pages
		if(array_key_exists('pageStart', $row) && array_search('pages', $this->bibformat->styleMap->$type))
		{
			$end = array_key_exists('pageEnd', $row) ? $row['pageEnd'] : FALSE;
			$this->bibformat->formatPages($row['pageStart'], $end);
		}
// All other database resource fields that do not require special formatting/conversion.
		$this->bibformat->addAllOtherItems($row);
// For citation formatting later on, get the placeholder to deal with ambiguous in-text citations.  Must be keyed by unique resource identifier.
		$this->citeformat->bibliographyIds[$id] = FALSE;
// Return the result of the bibliographic formatting removing any extraneous braces
		return preg_replace("/{(.*)}/U", "$1", $this->bibformat->map());
	}
// Create the resource title
	function createTitleBib()
	{
		$pString = stripslashes($this->row['title']);
		if(isset($this->row['subtitle']))
			$pString .= $this->citeformat->style['titleSubtitleSeparator'] . 
			stripslashes($this->row['subtitle']);
// anything enclosed in {...} is to be left as is 
		$this->bibformat->formatTitle($pString, "{", "}");
	}
// Create the resource title
	function createTitleCite()
	{
		$pString = stripslashes($this->rowSingle['title']);
		if(isset($this->rowSingle['subtitle']))
			$pString .= $this->citeformat->style['titleSubtitleSeparator'] . 
			stripslashes($this->rowSingle['subtitle']);
// anything enclosed in {...} is to be left as is 
		$this->citeformat->formatTitle($pString, "{", "}");
		if($this->rowSingle['shortTitle'])
// anything enclosed in {...} is to be left as is 
			$this->citeformat->formatShortTitle($this->rowSingle['shortTitle'], "{", "}");
	}
/*
* function processCite()
*
* Parse $this->text for citation tags and format accordingly.
*/
	function processCite($text)
	{
// Must be initialised.
		$this->pageStart = $this->pageEnd = $this->preText = $this->postText = $this->citeIds = array();
// Parse $this->text
// Capture any text after last [cite]...[/cite] tag
		$explode = explode("]etic/[", strrev($text), 2);
		$this->tailText = strrev($explode[0]);
		$text = strrev("]etic/[" . $explode[1]);
		preg_match_all("/(.*)\s*\[cite\](.*)\[\/cite\]/Uis", $text, $match);
		foreach($match[1] as $value)
			$this->matches[1][] = $value;
		$this->citeformat->count = 0;
		foreach($match[2] as $index => $value)
		{
			++$this->citeformat->count;
			if($id = $this->parseCiteTag($index, $value))
				$this->citeIds[] = $id;
		}
// If empty($this->citeIds), there are no citations to scan for (or user has entered invalid IDs) so return $text unchanged.
		if(empty($this->citeIds))
			return $text;
//		$this->citeformat->processEndnoteBibliography($this->row, $this->citeIds);
/*
* $matches[1]is an array of $1 above
* $matches[2] is an array of $2 (the citation references)
* e.g. 
* [1] => Array ( [0] => First [1] => [2] => [3] => [4] => blah blah see ) [2] => Array ( [0] => 1 [1] => 2 [2] => 3 [3] => 4 [4] => 2 )
* might represent:
* First [cite]1[/cite] [cite]2[/cite] [cite]3[/cite]
* [cite]1[/cite] blah blah see[cite]2[/cite]
*
* Note that having both [1][0] and [2][0] populated means that the citation reference [2][0] _follows_ the text in [1][0].
* Any unpopulated elements of matches[1] indicates multiple citations at that point.  e.g., in the example above, 
* there are multiple citations (references 1, 2, 3 and 4) following the text 'First' and preceeding the text 'blah blah see'.
*
* N.B. the preg_match_all() above does not capture any text after the final citation so this must be handled manually and appended to any final output - 
* this is $this->tailText above.
*/
		$this->citeformat->count = 0;
		$citeIndex = 0;
		while(!empty($this->matches[1]))
		{
			$this->citeformat->item = array(); // must be reset each time.
			$id = $this->citeIds[$citeIndex];
			++$citeIndex;
			++$this->citeformat->count;
			$text = array_shift($this->matches[1]);
			$this->citeformat->items[$this->citeformat->count]['id'] = $id;
//			$this->createPrePostText(array_shift($this->preText), array_shift($this->postText));
// For each element of $bibliography, process title, creator names etc.
			if(array_key_exists($id, $this->row))
				$this->processCitations($this->row[$id], $id);
// $this->rowSingle is set in $this->processCitations().
// 'type' is the type of resource (book, journal article etc.).  In WIKINDX, this is part of the row returned by SQL:  you may 
// need to set this manually if this is not the case for your system.  'type' is used in CITEFORMAT::prependAppend() to add any special strings to the citation within 
// the text (e.g. the XML style file might state that 'Personal communication: ' needs to be appended to any in-text citations for resources of type 'email'.
// CITEFORMAT::prependAppend() will map 'type' against the $types array in STYLEMAP as used in BIBFORMAT.
			$this->citeformat->items[$this->citeformat->count]['type'] = $this->rowSingle['type'];
			$this->citeformat->items[$this->citeformat->count]['text'] = $text;
		}
		$pString = $this->citeformat->process() . $this->tailText;
		return $pString;
	}
/**
* Parse the cite tag by extracting resource ID and any page numbers. Check ID is valid
* PreText and postText can also be encoded: e.g. (see Grimshaw 2003; Boulanger 2004 for example)
* [cite]23:34-35|see ` for example[/cite].  For multiple citations, only the first encountered preText and postText will be used to enclose the citations.
*/
	function parseCiteTag($matchIndex, $tag)
	{
// When a user cut's 'n' pastes in HTML design mode, superfluous HTML tags (usually <style lang=xx></span>) are inserted.  Remove anything that looks like HTML
		$tag = preg_replace("/<.*?>/si", "", $tag);
		$rawCitation = explode("|", $tag);
		$idPart = explode(":", $rawCitation[0]);
		$id = $idPart[0];
		if(array_key_exists('1', $idPart))
		{
			$pages = explode("-", $idPart[1]);
			$pageStart = $pages[0];
			$pageEnd = array_key_exists('1', $pages) ? $pages[1] : FALSE;
		}
		else
			$pageStart = $pageEnd = FALSE;
		$this->citeformat->formatPages($pageStart, $pageEnd);
		if(array_key_exists('1', $rawCitation))
		{
			$text = explode("`", $rawCitation[1]);
			$this->preText[] = $text[0];
			$this->postText[] = array_key_exists('1', $text) ? $text[1] : FALSE;
		}
		else
			$this->preText[] = $this->postText[] = FALSE;
		return $id;
	}
// Accept a SQL result row of raw bibliographic data and process it.
// We build up the $citeformat->item array with formatted parts from the raw $row
	function processCitations($row, $id)
	{
		$this->rowSingle = $row;
		unset($row);
		$this->citeformat->formatNames($this->rowSingle['creator1'], $id); // Use 'creator1' array -- primary creators.
// The title of the resource
		$this->createTitleCite();
// Publication year of resource.  If no publication year, we create a dummy key entry so that CITEFORMAT can provide a replacement string if required by the style.
		if(!array_key_exists('year1', $this->rowSingle))
			$this->rowSingle['year1'] = FALSE;
		$this->citeformat->formatYear(stripslashes($this->rowSingle['year1']));
	}
/*
* function getData()
*
* Load some test data.
* $this->row is how BIBFORMAT expects bibliographic input to be formatted and passed to its classes.  It is 
* your responsibility to get your data in a format usable by OSBIB (bibtex-based databases should use STYLEMAPBIBTEX.php) -- see docs/ for details.
*/
	function getData()
	{
// First resource
		$this->row[33]['id']		=	33; // Unique ID for this resource
		$this->row[33]['type']		= 	'book_article';	// The type of resource which must be one of the types in STYLEMAP.php
		$this->row[33]['title']		=	'{WIKINDX}'; // Braces protect uppercase
		$this->row[33]['subtitle']	= 	'Bibliographic & Quotations Manager';
		$this->row[33]['collectionTitle']	=	'Guide to Open Source Software';
		$this->row[33]['pageStart']	=	'51';
		$this->row[33]['pageEnd']	=	'59';
		$this->row[33]['shortTitle']	=	'WIK'; // Braces protect uppercase
/*
* For creators (authors, editors, composers, inventors, performers, translators etc.), the initial key element here must be 
* one of 'creator1', 'creator2', 'creator3', 'creator4' or 'creator5' where 'creator1' is usually the primary creator.  These are mapped 
* to appropriate roles for the resource type in STYLEMAP.php.  Only 'creator1' is used in CITEFORMAT and it must have a unique 'id' key.
* The second integer key element (keyed from 0) gives the descending order of printing the creator names for the particular creator type.
*/
// Primary creator, first in list.
		$this->row[33]['creator1'][0]['surname']	=	'Grimshaw';
		$this->row[33]['creator1'][0]['firstname']	=	'Mark';
		$this->row[33]['creator1'][0]['initials']	=	'N'; // Full stops are added by the bibliographic style if required.
		$this->row[33]['creator1'][0]['prefix']		=	''; // 'de', 'von', 'della' etc.  Array element must be present.
		$this->row[33]['creator1'][0]['id']			=	4; // unique ID for this author (presumably the database table ID for this creator).
// Publication year
		$this->row[33]['year1']		=	'2003';
// Original publication year
		$this->row[33]['year2']		=	'1999';
// Publisher name
		$this->row[33]['publisherName']	=	'Tsetswana Books';
// Publisher location
		$this->row[33]['publisherLocation']	=	'Gabarone';

// Second resource
		$this->row[1]['id']			=	1; // Unique ID for this resource
		$this->row[1]['type']		= 	'book';	// The type of resource which must be one of the types in STYLEMAP.php
		$this->row[1]['title']		=	'{OSBIB}'; // Braces protect uppercase
		$this->row[1]['subtitle']	= 	'Open Source Bibliographic Formatting';
		$this->row[1]['edition']	=	'3';
		$this->row[1]['shortTitle']	=	'OSB';
/*
* For creators (authors, editors, composers, inventors, performers, translators etc.), the initial key element here must be 
* one of 'creator1', 'creator2', 'creator3', 'creator4' or 'creator5' where 'creator1' is usually the primary creator.  These are mapped 
* to appropriate roles for the resource type in STYLEMAP.php.
* The second integer key element (keyed from 0) gives the descending order of printing the creator names for the particular creator type.
*/
// Primary creator, first in list.
		$this->row[1]['creator1'][0]['surname']		=	'Grimshaw';
		$this->row[1]['creator1'][0]['firstname']	=	'Mark';
		$this->row[1]['creator1'][0]['initials']	=	'N'; // Full stops are added by the bibliographic style if required.
		$this->row[1]['creator1'][0]['prefix']		=	''; // 'de', 'von', 'della' etc.  Array element must be present.
		$this->row[1]['creator1'][0]['id']			=	4; // unique ID for this author (same author as above)
// Primary creator, second in list.
		$this->row[1]['creator1'][1]['surname']		=	'Boulanger';
		$this->row[1]['creator1'][1]['firstname']	=	'Christian';
		$this->row[1]['creator1'][1]['initials']	=	'';
		$this->row[1]['creator1'][1]['prefix']		=	'';
		$this->row[1]['creator1'][1]['id']			=	10; // unique ID for this author
// Primary creator, third in list.
		$this->row[1]['creator1'][2]['surname']		=	'Gardey';
		$this->row[1]['creator1'][2]['firstname']	=	'Guillaume';
		$this->row[1]['creator1'][2]['initials']	=	'';
		$this->row[1]['creator1'][2]['prefix']		=	'';
		$this->row[1]['creator1'][2]['id']			=	24; // unique ID for this author
// Second creator, first in list.  In STYLEMAP, 'creator2' for resource type 'book' is mapped to 'editor'
		$this->row[1]['creator2'][0]['surname']		=	'Rossatto';
		$this->row[1]['creator2'][0]['firstname']	=	'Andrea';
		$this->row[1]['creator2'][0]['initials']	=	'';
		$this->row[1]['creator2'][0]['prefix']		=	'';
		$this->row[1]['creator2'][0]['id']			=	101; // unique ID for this author
// Publication year
		$this->row[1]['year1']		=	'2005';
// Original publication year
		$this->row[1]['year2']		=	'2004';
// Publisher name
		$this->row[1]['publisherName']	=	'Botswana Press';
// Publisher location
		$this->row[1]['publisherLocation']	=	'Selebi Phikwe';
		
// Third resource
		$this->row[44]['id']		=	44; // Unique ID for this resource
		$this->row[44]['type']		= 	'book';	// The type of resource which must be one of the types in STYLEMAP.php
		$this->row[44]['title']		=	'Bibliophile'; // Braces protect uppercase
		$this->row[44]['shortTitle']	=	'BIBLIO';
/*
* For creators (authors, editors, composers, inventors, performers, translators etc.), the initial key element here must be 
* one of 'creator1', 'creator2', 'creator3', 'creator4' or 'creator5' where 'creator1' is usually the primary creator.  These are mapped 
* to appropriate roles for the resource type in STYLEMAP.php.  Only 'creator1' is used in CITEFORMAT and it must have a unique 'id' key.
* The second integer key element (keyed from 0) gives the descending order of printing the creator names for the particular creator type.
*/
// Primary creator, first in list.
		$this->row[44]['creator1'][0]['surname']	=	'Grimshaw';
		$this->row[44]['creator1'][0]['firstname']	=	'Mark';
		$this->row[44]['creator1'][0]['initials']	=	'N'; // Full stops are added by the bibliographic style if required.
		$this->row[44]['creator1'][0]['prefix']		=	''; // 'de', 'von', 'della' etc.  Array element must be present.
		$this->row[44]['creator1'][0]['id']			=	4; // unique ID for this author (presumably the database table ID for this creator).
// Publication year
		$this->row[44]['year1']		=	'2003';
// Publisher name
		$this->row[44]['publisherName']	=	'Marula Publishers';
// Publisher location
		$this->row[44]['publisherLocation']	=	'Gabarone';

// Some text input with citation markup relating to the ID in $this->row above.

		$this->text = "It has long been said that \"blah blah blah\" [cite]33:52-53[/cite].";
		$this->text .= "  Grimshaw et al. say \"blah blah blah\" [cite]1:101[/cite].";
		$this->text .= "  A number of writers agree with this [cite]33[/cite][cite]44[/cite].";
		$this->text .= "  However, this same author later states:  \"Blah blah blah\" [cite]33:71[/cite].";
		$this->text .= "  This is further contradicted when Grimshaw says \"blah blah blah\" [cite]44:302[/cite].";
	}
}
?>