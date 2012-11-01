<?php
/********************************
OSBib:
A collection of PHP classes to create and manage bibliographic formatting for OS bibliography software 
using the OSBib standard.  Originally developed for WIKINDX (http://wikindx.sourceforge.net)

Released through http://bibliophile.sourceforge.net under the GPL licence.
Do whatever you like with this -- some credit to the author(s) would be appreciated.

If you make improvements, please consider contacting the administrators at bibliophile.sourceforge.net 
so that your improvements can be added to the release package.

Mark Grimshaw 2006
http://bibliophile.sourceforge.net
********************************/

/** 
* Class TESTOSBIBBIBTEX
* Test suite for OSBIB's BIBFORMAT (bibliographic formatting) using BibTeX.
* This is not part of OSBIB but is here to provide an example of usage and to test data input and output for a non-BibTeX based system.
* It is intended to be a quick introduction to the main usage of OSBIB.  For more detailed explanation including various parameters that can be 
* set, see WIKINDX's usage of OSBIB in the example BIBSTYLE.php.
* 
* @author	Mark Grimshaw
* @version	1
*/

/*
* Start the ball rolling
*
* The first parameter to TESTOSBIBBIBTEX is the bibliographic style.  This can be any of the OSBIB supplied styles in ../styles/bibliography.
*/
define("OSBIB__BIBSTYLE", "BIBSTYLE.php");
define("OSBIB__BIBFORMAT", "BIBFORMAT.php");
define("OSBIB__CITESTYLE", "CITESTYLE.php");
define("OSBIB__CITEFORMAT", "CITEFORMAT.php");
define("OSBIB__STYLEMAP", "../STYLEMAP.php");
define("OSBIB__UTF8", "../UTF8.php");
define("OSBIB__PARSEXML", "../PARSEXML.php");
define("OSBIB__LOADSTYLE", "../LOADSTYLE.php");
define("OSBIB__PARSESTYLE", "PARSESTYLE.php");
define("OSBIB__EXPORTFILTER", "EXPORTFILTER.php");
define("OSBIB_STYLE_DIR", "../styles/bibliography");
define("OSBIB__STYLEMAPBIBTEX", "../STYLEMAPBIBTEX.php");
define("OSBIB__PARSECREATORS", "bibtexParse/PARSECREATORS.php");
define("OSBIB__PARSEMONTH", "bibtexParse/PARSEMONTH.php");
define("OSBIB__PARSEPAGE", "bibtexParse/PARSEPAGE.php");

$useStyle = loadStyle();
$testosbib = new TESTOSBIBBIBTEX($useStyle);
$testosbib->execute();
die; // exit

class TESTOSBIBBIBTEX
{
/**
* Constructor.
*/
	function TESTOSBIBBIBTEX($style)
	{
		$this->style = $style;
	}
/*
* function execute()
*
* Start the whole process
*/
	function execute()
	{
// Load the test data
		$this->getData();
		include_once(OSBIB__BIBFORMAT);
		$this->bibformat = new BIBFORMAT(FALSE, TRUE); // 'TRUE' == set for BibTeX
// Load the bibliographic style
		list($info, $citation, $footnote, $styleCommon, $styleTypes) = 
			$this->bibformat->loadStyle("../styles/bibliography/", $this->style);
		$this->bibformat->getStyle($styleCommon, $styleTypes, $footnote);
		unset($info, $citation, $footnote, $styleCommon, $styleTypes); // no longer required here so conserve memory
// Cycle through the resources we want to format
		foreach($this->row as $row)
			$this->bibResult[] = $this->processBib($row);
// For citation formatting, BIBFORMAT must be reinitialised.
		include_once(OSBIB__BIBFORMAT);
		$this->bibformat = new BIBFORMAT(FALSE, TRUE);
		include_once(OSBIB__CITEFORMAT);
// Pass the bibstyle object to CITEFORMAT() as the first argument.
// The second argument is the name of the method within the bibstyle object that starts the formatting of a bibliographic item.
		$this->citeformat = new CITEFORMAT($this, "processBib", "../");
		$this->bibformat->output = $this->citeformat->output = 'html'; // output format (default)
		list($info, $citation, $footnote, $styleCommon, $styleTypes) = 
			$this->bibformat->loadStyle(OSBIB_STYLE_DIR, $this->style);
		$this->bibformat->getStyle($styleCommon, $styleTypes, $footnote);
		$this->citeformat->getStyle($citation, $footnote);
		unset($info, $citation, $footnote, $styleCommon, $styleTypes); // no longer required here so conserve memory
		$this->formattedText = $this->processCite();
		$this->printToScreen();
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
// Add creator names.  Up to 5 types are allowed - for mappings depending on resource type see STYLEMAP.php
		if(!$this->bibformat->bibtex)
		{
			$this->bibformat->formatTitle($row['title'], "{", "}");
			for($index = 1; $index <= 5; $index++)
			{
				$creatorSlot = 'creator' . $index;
	// If we have creator name in this slot OR that creator slot is not defined in STYLEMAP.php, do nothing
				if(array_key_exists($creatorSlot, $row) && 
				array_key_exists($creatorSlot, $this->bibformat->styleMap->$type))
					$this->bibformat->formatNames($row[$creatorSlot], $creatorSlot);
			}
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
/*
* function processCite()
*
* Parse $this->text for citation tags and format accordingly.
*/
	function processCite()
	{
// Must be initialised.
		$this->pageStart = $this->pageEnd = $this->preText = $this->postText = $this->citeIds = array();
// Parse $this->text
// Capture any text after last [cite]...[/cite] tag
		$explode = explode("]etic/[", strrev($this->text), 2);
		$this->tailText = strrev($explode[0]);
		$text = strrev("]etic/[" . $explode[1]);
		preg_match_all("/(.*)\s*\[cite\](.*)\[\/cite\]/Uis", $this->text, $match);
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
		$this->citeformat->processEndnoteBibliography($this->row, $this->citeIds);
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
//		$this->row = array();
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
			$this->createPrePostText(array_shift($this->preText), array_shift($this->postText));
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
// Endnote-style citations so add the endnotes bibliography
		if($this->citeformat->style['citationStyle'])
		{
			$pString = $this->citeformat->printEndnoteBibliography($pString);
			if($this->citeformat->style['endnoteStyle'] != 2) // Not footnotes.
				return $pString;
		}
// In-text citations and footnotes - output the appended bibliography
		$bib = $this->printBibliography($this->row);
		return $pString . $bib;
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
		if(!$this->bibformat->bibtex)
			$this->citeformat->formatNames($this->rowSingle['creator1'], $id); // Use 'creator1' array -- primary creators.
// The title of the resource
		$this->citeformat->formatTitle($this->rowSingle['title'], "{", "}");
// Publication year of resource.  If no publication year, we create a dummy key entry so that CITEFORMAT can provide a replacement string if required by the style.
		if(!array_key_exists('year1', $this->rowSingle))
			$this->rowSingle['year1'] = FALSE;
		$this->citeformat->formatYear(stripslashes($this->rowSingle['year1']));
	}
/* Create preText and postText.  This is for in-text citations where a string of citations tags:
[cite]1:24|See ` for example[/cite][cite]33:54[/cite]
might result in:
(See Grimshaw et al., 2005 p. 24 and Grimshaw, 1999 p. 54 for example).
*/
	function createPrePostText($preText, $postText)
	{
		if(!$preText && !$postText) // empty field
			return;
		$this->citeformat->formatPrePostText($preText, $postText);
	}
// Process bibliography array into string for output -- used for in-text citations and appended bibliographies for footnotes
	function printBibliography($bibliography)
	{
		foreach($bibliography as $id => $row)
		{
			$row['id'] = $id;
// Do not add if cited resource type shouldn't be in the appended bibliography
			if(array_key_exists($row['type'] . "_notInBibliography", $this->citeformat->style))
				continue;
// If we're disambiguating citations by adding a letter after the year, we need to insert the yearLetter into $row before formatting the bibliography.
			if($this->citeformat->style['ambiguous'] && 
				array_key_exists($id, $this->citeformat->yearsDisambiguated))
				$row['year1'] = $this->citeformat->yearsDisambiguated[$id];
			$this->citeformat->processIntextBibliography($row);
		}
		return $this->citeformat->collateIntextBibliography();
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
		$this->row[33]['booktitle']	=	'Guide to Open Source Software';
		$this->row[33]['pages']		=	'51 --  59';
/*
* For creators (authors, editors, composers, inventors, performers, translators etc.), the initial key element here must be 
* one of 'creator1', 'creator2', 'creator3', 'creator4' or 'creator5' where 'creator1' is usually the primary creator.  These are mapped 
* to appropriate roles for the resource type in STYLEMAP.php.  Only 'creator1' is used in CITEFORMAT and it must have a unique 'id' key.
* The second integer key element (keyed from 0) gives the descending order of printing the creator names for the particular creator type.
*/
// Primary creator, first in list.
		$this->row[33]['author']	=	'Grimshaw, Mark';
// Second creator, first in list.  In STYLEMAP, 'creator2' for resource type 'book' is mapped to 'editor'
		$this->row[33]['editor']	=	'de Mouse, Mickey';
// Publication year
		$this->row[33]['year']		=	'2003';
// Publisher name
		$this->row[33]['publisher']	=	'Tsetswana Books';
// Publisher location
		$this->row[33]['address']	=	'Gabarone';

// Second resource
		$this->row[1]['id']			=	1; // Unique ID for this resource
		$this->row[1]['type']		= 	'book';	// The type of resource which must be one of the types in STYLEMAP.php
		$this->row[1]['title']		=	'{OSBIB}: Open Source Bibliographic Formatting'; // Braces protect uppercase
		$this->row[1]['edition']	=	'3';
/*
* For creators (authors, editors, composers, inventors, performers, translators etc.), the initial key element here must be 
* one of 'creator1', 'creator2', 'creator3', 'creator4' or 'creator5' where 'creator1' is usually the primary creator.  These are mapped 
* to appropriate roles for the resource type in STYLEMAP.php.
* The second integer key element (keyed from 0) gives the descending order of printing the creator names for the particular creator type.
*/
// Primary creator, first in list.
		$this->row[1]['author']	=	'Grimshaw, Mark and Boulanger, Christian and Gardey, Guillaume';
// Publication year
		$this->row[1]['year']		=	'2005';
// Publisher name
		$this->row[1]['publisher']	=	'Botswana Press';
// Publisher location
		$this->row[1]['address']	=	'Selebi Phikwe';
// Some text input with citation markup relating to the ID in $this->row above.
		$this->text = "Grimshaw says \"blah blah blah\" [cite]33:52-53[/cite].  He further says \"blah blah blah\" [cite]33:58[/cite].  However, Grimshaw et al. later contradict this by stating:  \"blah blah blah\" [cite]1:101[/cite] and \"blah blah blah\" [cite]1:104[/cite].  This latter contradiction is qualified with \"blah blah blah\" [cite]1:113[/cite].";
	}
/*
* function printToScreen()
*
* Print to the browser
*/
	function printToScreen()
	{
		$string = "<p><strong>Bibliographic style:</strong><br />" . $this->style . "</p>";
		$string .= "<h3><font color='red'>Bibliography Formatting</font></h3>";
		$string .= "<p><strong>Bibliographic input data:</strong><br />";
		foreach($this->row as $row)
			$string .= print_r($row, TRUE) . "<br />";
		print "</p>";
		$string .= "<hr>";
		$string .= "<h3><font color='blue'>Output from BIBFORMAT</font></h3><p>";
		foreach($this->bibResult as $result)
			$string .= "$result<br />";
		print "$string</p>";
		$string = "<hr>";
		$string .= "<hr>";
		$string .= "<hr>";
		$string .= "<h3><font color='red'>Citation Formatting</font></h3>";
		$string .= "<p><strong>Text input:</strong><br />" . $this->text . "</p>";
		$string .= "<hr>";
		$string .= "<h3><font color='blue'>Output from CITEFORMAT</font></h3>";
		$string .= "<p>" . $this->formattedText . "</p>";
		print "$string";
	}
}

// External to class

// Load styles and print select box.
function loadStyle()
{
	include_once(OSBIB__LOADSTYLE);
	$styles = LOADSTYLE::loadDir(OSBIB_STYLE_DIR);
	$styleKeys = array_keys($styles);
	print "<h2><font color='red'>OSBIB Bibliographic Formatting (Quick Test)</font></h2>";
	print "<table width=\"100%\" border=\"0\"><tr><td>";
	print "<form method = \"POST\">";
	print "<select name=\"style\" id=\"style\" size=\"10\">";
	if(array_key_exists('style', $_POST))
		$useStyle = $_POST['style'];
	else
		$useStyle = $styleKeys[0];
	foreach($styles as $style => $value)
	{
		if($style == $useStyle)
			print "<option value=\"$style\" selected = \"selected\">$value</option>";
		else
			print "<option value=\"$style\">$value</option>";
	}
	print "</select>";
	print "<br /><input type=\"submit\" value=\"SUBMIT\" />";
	print "</form><td>";
	print "<td align=\"right\" valign=\"top\"><a href=\"http://bibliophile.sourceforge.net\">
		<img src=\"../create/bibliophile.gif\" alt=\"Bibliophile\" border=\"0\"></a></td></tr></table>";
	return $useStyle;
}
