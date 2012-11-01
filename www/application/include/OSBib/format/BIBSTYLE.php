<?php
/**********************************************************************************
WIKINDX: Bibliographic Management system.
Copyright (C)

This program is free software; you can redistribute it and/or modify it under the terms
of the GNU General Public License as published by the Free Software Foundation; either
version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program;
if not, write to the
Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA

The WIKINDX Team 2006
sirfragalot@users.sourceforge.net
**********************************************************************************/
/*****
*	BIBLIOGRAPHY STYLE class
*	Format a resource for a bibliographic style.
*
*	$Header: /cvsroot/aigaion/webinterface/includes/OSBib/format/BIBSTYLE.php,v 1.3 2006/12/01 14:27:49 reidsma Exp $
*****/
class BIBSTYLE
{
// Constructor
	function BIBSTYLE($db, $output, $export = FALSE, $style = FALSE)
	{
		$this->db = $db;
		if($output == 'html')
		{
			include_once(OSBIB__FORMMISC);
			$this->formmisc = new FORMMISC();
		}
		include_once(OSBIB__SESSION);
		$this->session = new SESSION();
		include_once(OSBIB__BIBFORMAT);
// '$this->bibformat' is used elsewhere in other classes so should not change
		$this->bibformat = new BIBFORMAT();
		$this->output = $output;
		$this->bibformat->output = $output;
// WIKINDX-specific
		$this->bibformat->wikindx = FALSE;
/**
* CSS class for highlighting search terms
*/
		$this->bibformat->patternHighlight = "highlight";
		include_once(OSBIB__MISC);
// get the bibliographic style
		if($style)
			$this->setupStyle = $style;
		else
		{
			if($export)
			{
				if($output == 'rtf')
					$this->setupStyle = $this->session->getVar("exportPaper_style");
			}
			else
			{
				if($output == 'rtf')
					$this->setupStyle = $this->session->getVar("exportRtf_style");
			}
			if(!isset($this->setupStyle))
				$this->setupStyle = $this->session->getVar("setup_style");
		}
/**
* If our style arrays do not exist in session, parse the style file and write to session.  Loading and 
* parsing the XML file takes about 0.1 second (P4 system) and so is a significant slowdown.  
* Try to do this only once every time we use a style.  NB. these are saved in session with 'cite_' and 'style_' 
* prefixes - creating/copying or editing a bibliographic style clears these arrays from the session which will 
* force a reload of the style here.
*/
		$styleInfo = $this->session->getVar("style_name");
		$styleCommon = unserialize(base64_decode($this->session->getVar("style_common")));
		$footnote = unserialize(base64_decode($this->session->getVar("cite_footnote")));
		$styleTypes = unserialize(base64_decode($this->session->getVar("style_types")));
// File not yet parsed or user's choice of style has changed so need to 
// load, parse and store to session
		if((!$styleInfo || !$styleCommon || !$styleTypes) 
			|| (strtolower($styleInfo) != strtolower($this->setupStyle)))
		{
			list($info, $citation, $footnote, $styleCommon, $styleTypes) = 
				$this->bibformat->loadStyle("styles/bibliography/", $this->setupStyle);
			$this->session->setVar("style_name", $info['name']);
			$this->session->setVar("cite_citation", base64_encode(serialize($citation)));
			$this->session->setVar("cite_footnote", base64_encode(serialize($footnote)));
			$this->session->setVar("style_common", base64_encode(serialize($styleCommon)));
			$this->session->setVar("style_types", base64_encode(serialize($styleTypes)));
			$this->session->delVar("style_edited");
		}
		unset($this->session, $info, $citation);
		$this->bibformat->getStyle($styleCommon, $styleTypes, $footnote);
		unset($styleCommon, $styleTypes, $footnote);
//print_r($this->bibformat->style); print "<P>";
	}
// Accept a SQL result row of raw bibliographic data and process it.
// We build up the $bibformat->item array with formatted parts from the raw $row
	function process($row, $shortOutput = FALSE)
	{
// Do we need to remove slashes from the $row elements? If not, comment this line out.
		$row = array_map(array($this, "removeSlashes"), $row);
		$this->row = $row;
		$this->shortOutput = $shortOutput;
		$type = $row['type']; // WIKINDX type
		unset($row);
// For WIKINDX, if type == book or book article and there exists both 'year1' and 'year2' in $row (entered as 
// publication year and reprint year respectively), then switch these around as 'year1' is 
// entered in the style template as 'originalPublicationYear' and 'year2' should be 'publicationYear'.
		if(($type == 'book') || ($type == 'book_article'))
		{
			$year2 = $this->row['year2'];
			if($year2 && !$this->row['year1'])
			{
				$this->row['year1'] = $year2;
				unset($this->row['year2']);
			}
			else if($year2 && $this->row['year1'])
			{
				$this->row['year2'] = $this->row['year1'];
				$this->row['year1'] = $year2;
			}
		}
		$this->row = $this->bibformat->preProcess($type, $this->row);
// Return $type is the OSBib resource type ($this->book, $this->web_article etc.) as used in STYLEMAP
		$type = $this->bibformat->type;
		$this->preProcess($type);
		if($this->shortOutput)
		{
			$pString = '';
			if($this->row['creator1'])
				$pString .= $this->bibformat->item[$this->bibformat->styleMap->{$type}['creator1']] . " ";
			if($this->row['year1'])
				$pString .= $this->row['year1'] . " ";
			$pString .= $this->row['title'];
			if($this->row['subtitle'])
				$pString .= ": " . $this->row['subtitle'];
			$pString .= " [$type]";
			return preg_replace("/{(.*)}/U", "$1", $pString);
		}
// We now have an array for this item where the keys match the key names of $this->styleMap->$type 
// where $type is book, journal_article, thesis etc. and are now ready to map this against the defined 
// bibliographic style for each resource ($this->book, $this->book_article etc.).
// This bibliographic style array not only provides the formatting and punctuation for each field but also 
// provides the order. If a field name does not exist in this style array, we print nothing.
		$pString = $this->bibformat->map();
// bibTeX ordinals such as 5$^{th}$
		$pString = preg_replace_callback("/(\d+)\\$\^\{(.*)\}\\$/", array($this, "ordinals"), $pString);
// remove extraneous {...}
		return preg_replace("/{(.*)}/U", "$1", $pString);
	}
// Perform some pre-processing
	function preProcess($type)
	{
// Various types of creator
		for($index = 1; $index <= 5; $index++)
		{
			if($this->shortOutput && ($index > 1))
				break;
			if(!$this->row['creator' . $index] || 
				!array_key_exists('creator' . $index, $this->bibformat->styleMap->$type))
				continue;
			if(array_key_exists('creator' . $index, $this->bibformat->styleMap->$type))
				$this->grabNames('creator' . $index);
		}
// The title of the resource
		$this->createTitle();
		if(!$this->shortOutput)
		{
// edition
			if($editionKey = array_search('edition', $this->bibformat->styleMap->$type))
				$this->createEdition($editionKey);
// pageStart and pageEnd
			$this->pages = FALSE; // indicates not yet created pages for articles
			if(array_key_exists('pages', $this->bibformat->styleMap->$type))
				$this->createPages();
// Date
			if(array_key_exists('date', $this->bibformat->styleMap->$type))
				$this->createDate();
// runningTime for film/broadcast
			if(array_key_exists('runningTime', $this->bibformat->styleMap->$type))
				$this->createRunningTime();
// web_article URL
			if(array_key_exists('URL', $this->bibformat->styleMap->$type) && 
				($itemElement = $this->createUrl()))
				$this->bibformat->addItem($itemElement, 'URL', FALSE);
// proceedings_article can have publisher as well as organiser/location. Publisher is in 'miscField1'
			if(($type == 'proceedings_article') && $this->row['miscField1'])
			{
				$recordset = $this->db->select(array("WKX_publisher"), 
					array("publisherName", "publisherLocation"), 
					" WHERE " . $this->db->formatField('id') . "=" . 
					$this->db->tidyInput($this->row['miscField1']));
				$pubRow = $this->db->fetchRow($recordset);
				if($pubRow['publisherName'])
					$this->bibformat->addItem($pubRow['publisherName'], 'publisher');
				if($pubRow['publisherLocation'])
					$this->bibformat->addItem($pubRow['publisherLocation'], 'location');
			}
// books and book_articles can have a translated work's original publisher's details in `miscField1`
			else if((($type == 'book') || ($type == 'book_article')) && $this->row['miscField1'])
			{
				$recordset = $this->db->select(array("WKX_publisher"), 
					array("publisherName", "publisherLocation"), 
					" WHERE " . $this->db->formatField('id') . "=" . 
					$this->db->tidyInput($this->row['miscField1']));
				$pubRow = $this->db->fetchRow($recordset);
				if($pubRow['publisherName'])
					$this->bibformat->addItem($pubRow['publisherName'], 'transPublisherName');
				if($pubRow['publisherLocation'])
					$this->bibformat->addItem($pubRow['publisherLocation'], 'transPublisherLocation');
			}
// For WIKINDX, resources of type thesis, have the thesis type stored as integers in $row['field1'] and the label stored in $row['field2']
			else if($type == 'thesis')
			{
				$field1 = array(
					0	=>	"UNKNOWN", 
					1	=>	"master's",
					2	=>	"doctoral",
					3	=>	"PhD",
					4	=>	"diploma",
					5	=>	"EdD");
				$field2 = array(
					1	=>	"thesis",
					2	=>	"dissertation",);
				$this->row['field1'] = $field1[$this->row['field1']];
				$this->row['field2'] = $field2[$this->row['field2']];
			}
// the rest...  All other database resource fields that do not require special formatting/conversion.
			$this->bibformat->addAllOtherItems($this->row);
		}
// Add the publication year for short output.
		else if(array_key_exists('year1', $this->bibformat->styleMap->$type) && $this->row['year1'])
				$this->bibformat->addItem($this->row['year1'], 'year1', FALSE);
	}
// callback for ordinals above
	function ordinals($matches)
	{
		if($this->output == 'html')
			return $matches[1] . "<sup>" . $matches[2] . "</sup>";
		else if($this->output == 'rtf')
			return $matches[1] . "{{\up5 " . $matches[2] . "}}";
		else
			return $matches[1] . $matches[2];
	}
// Create the resource title
	function createTitle()
	{
		$pString = $this->row['noSort'] . ' ' . $this->row['title'];
// If title ends in a sentence-end marker, don't add titleSubtitleSeparator
		if($this->row['subtitle'] && preg_match("/[?!��.]$/", $this->row['title'], $null))
		{
			if($this->output == 'html')
				$pString .= "&nbsp;&nbsp;";
			else
				$pString .= '  ';
		}
		else if($this->row['subtitle'])
			$pString .= $this->bibformat->style['titleSubtitleSeparator'];
// anything enclosed in {...} is to be left as is 
		$this->bibformat->formatTitle($pString, "{", "}"); // title
		if($this->row['subtitle'])
			$this->bibformat->formatTitle($this->row['subtitle'], "{", "}"); // subTitle
// Title of the original work from which a translation has been made.
		$pString = $this->row['transNoSort'] . ' ' . $this->row['transTitle'];
		if($this->row['transSubtitle'] && preg_match("/[?!��.]$/", $this->row['transTitle'], $null))
		{
			if($this->output == 'html')
				$pString .= "&nbsp;&nbsp;";
			else
				$pString .= '  ';
		}
		else if($this->row['transSubtitle'])
			$pString .= $this->bibformat->style['titleSubtitleSeparator'];
// anything enclosed in {...} is to be left as is 
		$this->bibformat->formatTransTitle($pString, "{", "}");
		if($this->row['transSubtitle'])
			$this->bibformat->formatTransTitle($this->row['transSubtitle'], "{", "}");
		if($this->row['shortTitle'])
// anything enclosed in {...} is to be left as is 
			$this->bibformat->formatShortTitle($this->row['shortTitle'], "{", "}");
	}
// Create the URL
	function createUrl()
	{
		if(!$this->row['url'])
			return FALSE;
		$url = ($this->output == 'html') ? htmlspecialchars($this->row['url']) : 
			$this->row['url'];
		unset($this->row['url']);
		if($this->output == 'html')
		{
			$label = $this->formmisc->reduceLongText($url, 80);
			return MISC::a('rLink', $label, $url, "_blank");
		}
		else
			return $url;
	}
// Create date
	function createDate()
	{
		$startDay = isset($this->row['miscField2']) ? $this->row['miscField2'] : FALSE;
		$startMonth = isset($this->row['miscField3']) ? $this->row['miscField3'] : FALSE;
		unset($this->row['miscField2']);
		unset($this->row['miscField3']);
		$endDay = isset($this->row['miscField5']) ? $this->row['miscField5'] : FALSE;
		$endMonth = isset($this->row['miscField6']) ? $this->row['miscField6'] : FALSE;
		unset($this->row['miscField5']);
		unset($this->row['miscField6']);
		$startDay = ($startDay == 0) ? FALSE : $startDay;
		$startMonth = ($startMonth == 0) ? FALSE : $startMonth;
		if(!$startMonth)
			return;
		$endDay = ($endDay == 0) ? FALSE : $endDay;
		$endMonth = ($endMonth == 0) ? FALSE : $endMonth;
		$this->bibformat->formatDate($startDay, $startMonth, $endDay, $endMonth);
	}
// Create runningTime for film/broadcast
	function createRunningTime()
	{
		$minutes = $this->row['miscField1'];
		$hours = $this->row['miscField4'];
		if(!$hours && !$minutes)
			return;
		if(!$hours)
			$hours = 0;
		$this->bibformat->formatRunningTime($minutes, $hours);
	}
// Create the edition number
	function createEdition($editionKey)
	{
		if(!$this->row[$editionKey])
			return FALSE;
		$edition = $this->row[$editionKey];
		$this->bibformat->formatEdition($edition);
	}
// Create page start and page end
	function createPages()
	{
		if(!$this->row['pageStart'] || $this->pages) // empty field or page format already done
		{
			$this->pages = TRUE;
			return;
		}
		$this->pages = TRUE;
		$start = trim($this->row['pageStart']);
		$end = $this->row['pageEnd'] ? trim($this->row['pageEnd']) : FALSE;
		$this->bibformat->formatPages($start, $end);
	}
// get names from database for creator, editor, translator etc.
	function grabNames($nameType)
	{
		$nameIds = explode(",", $this->row[$nameType]);
		foreach($nameIds as $nameId)
			$conditions[] = $this->db->formatField("id") . "=" . $this->db->tidyInput($nameId);
		$recordset = $this->db->select(array("WKX_creator"), array("surname", "firstname", 
			"initials", "prefix", "id"), 
			" WHERE " . join(" OR ", $conditions));
		$numNames = $this->db->numRows($recordset);
// Reorder $row so that creator order is correct and not that returned by SQL
		$ids = explode(",", $this->row[$nameType]);
		while($row = $this->db->loopRecordSet($recordset))
			$rowSql[$row['id']] = array_map(array($this, "removeSlashes"), $row);
		if(!isset($rowSql))
			return FALSE;
		foreach($ids as $id)
			$rowTemp[] = $rowSql[$id];
		$this->bibformat->formatNames($rowTemp, $nameType);
	}
	function removeSlashes($element)
	{
		$element = stripslashes($element);
		if($this->output == 'rtf')
			$element = str_replace('\\', '\\\\', $element);
		return $element;
	}
// bad Input function
	function badInput($error)
	{
		include_once(OSBIB__CLOSE);
		new CLOSE($this->db, $error);
	}
}
?>