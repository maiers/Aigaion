<?php	## if this script is not called from within one of the base pages, redirect to frontpage:
/*
Web based document management system
Copyright (C) 2003,2004 Hendri Hondorp, Dennis Reidsma, Arthur van Bunningen, Wietse Balkema

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA	02111-1307, USA.
*/
/*
This file offers several functions to import publications into Aigaion
Each function returns a string that contains (parts of) a form.

	bibParse($bibtexData)
		Creates an instance of the bibliophile bibtexparser to parse bibtex data.
		returns an array with parsed entries

	bibParseAuthors($authors)
		Creates an instance of the bibliophile creatorsparser to parse bibtex authors.
		returns an array with parsed authors

	bibParseFirstname($firstName)
		Creates an instance of the bibliophile creatorsparser to parse a first name.
		returns a string with a correctly formatted name

	bibParseInitials($firstName)
		Creates an instance of the bibliophile creatorsparser to parse the initials from
		an authors first name.
		returns a string with initials (eg. "I. M.")

	bibParsePages($pages)
		Creates an instance of the bibliophile pageparser to parse bibtex pages.
		returns a list with beginning and ending page.

	bibParseMonth($month)
		Creates an instance of the bibliophile monthparser to parse bibtex months.
		returns a list ($month, $day)

	bibCheckRequired($type, $field)
		returns a span environment indicating whether the $field is required for $type

	bibGetFieldArray($type)
		returns an array with supported fields of the publication type $type.

	bibGetSummaryFieldArray($type)
		returns a smaller array with supported fields of the publication type $type.

	bibGetSupportedEntries
		returns an array with supported entry types

	bibGetSupportedFields
		returns an array with supported entry fields
*/

include_once("PARSEENTRIES.php");
include_once("PARSECREATORS.php");
include_once("PARSEMONTH.php");
include_once("PARSEPAGE.php");

function bibParse($bibtexData)
{
	$bibtexData = $_SESSION["SITE"]->getConfigurationSetting('BIBTEX_STRINGS_IN')."\n".$bibtexData;
	$cParseEntries = NEW PARSEENTRIES();
	$cParseEntries->expandMacro = TRUE;

	$cParseEntries->loadBibtexString($bibtexData);

	$cParseEntries->extractEntries();

	list($preamble, $strings, $entries) = $cParseEntries->returnArrays();

	return $entries;
}


function bibParseAuthors($authors) {
	$cParseCreators = NEW PARSECREATORS();

	return $cParseCreators->parse($authors);
}


function bibParseFirstname($firstname)
{
	$cParseCreators = new PARSECREATORS();
	return $cParseCreators->formatFirstname($firstname);
}


function bibParseInitials($firstNames)
{
	$cParseCreators = new PARSECREATORS();
	return $cParseCreators->getInitials($firstNames);

	/* 17-09-2006 - Code not needed anymore with new creators parser
	$parsedNames = $cParseCreators->grabFirstnameInitials($firstNames);
	//returns first names in [0] and initials in [1]

	$firstNameArray = split(" ", trim($parsedNames[0]));
	$initials = "";
	foreach ($firstNameArray as $firstName) {
		$firstName = trim($firstName);
		if ($firstName != "") {
			$initials .= $firstName[0].".";
		}
	}

	$initialsFromParser = trim($parsedNames[1]);
	if ($initialsFromParser != "") {
		$initialsFromParser .= ".";
	}
	$initials .= $initialsFromParser;

	return $initials;
	*/
}


function bibParsePages($pages)
{
	$cParsePage = new PARSEPAGE();
	return $cParsePage->init($pages);
}


function bibParseMonth($month)
{
	$cParseMonth = new PARSEMONTH();
	return $cParseMonth->init($month);
}


function bibCheckRequired($type, $field)
{
	$type = ucfirst(strtolower($type));
	$retarray = array();
	switch ($type) {
		case "Article":
			$retarray = array('journal', 'title', 'year', 'author');
		break;
		case "Book":
			$retarray = array('publisher', 'title', 'year', 'author', 'editor');//author and/or editor
		break;
		case "Booklet":
			$retarray = array('title');
		break;
		case "Inbook":
			$retarray = array('chapter', 'firstpage', 'lastpage', 'publisher', 'title', 'year', 'author', 'editor');//author and/or editor chapter and/or firstpage', 'lastpage
		break;
		case "Incollection":
			$retarray = array('booktitle', 'title', 'publisher', 'year', 'author');
		break;
		case "Inproceedings":
			$retarray = array('title', 'booktitle', 'year', 'author');
		break;
		case "Manual":
			$retarray = array('title');
		break;
		case "Mastersthesis":
			$retarray = array('title', 'school', 'year', 'author');
		break;
		case "Misc":
			$retarray = array();
		break;
		case "Phdthesis":
			$retarray = array('title', 'school', 'year', 'author');
		break;
		case "Proceedings":
			$retarray = array('title', 'year');
		break;
		case "Techreport":
			$retarray = array('title', 'institution', 'year', 'author');
		break;
		case "Unpublished":
			$retarray = array('author', 'title', 'note');
		break;
		default:
		break;
	}

	if (in_array($field,$retarray)) {
		//handle conditional required fields
		if ((($type == "Book") || ($type == "Inbook")) && (($field == "author") || ($field == "editor"))) {  # author and/or editor
			return "<span alt='Bibtex required field: Author or Editor' title='Bibtex required field: Author or Editor'><a>*</a></span>";
		} elseif (($type == "Inbook")&& (($field == "chapter") || ($field == "firstpage") || ($field == "lastpage"))) {  # chapter and/or firstpage', 'lastpage
			return "<span alt='Bibtex required field: Chapter or Pages' title='Bibtex required field: Chapter or Pages'><a>*</a></span>";
		}
//		else if ((($type == "Book") || ($type == "Inbook") || ($type == "Incollection") || ($type == "Inproceedings") || ($type == "Proceedings")) && (($field == "volume") || ($field == "number")))
//		{
//			return "<span alt='Bibtex required field: Volume or Number' title='Bibtex required field: Volume or Number'><a>*</a></span>";
//		}
		else
			return "<span alt='Bibtex required field' title='Bibtex required field'><a>*</a></span>";
	} else {
		return "";
	}
}


function bibGetFieldArray($type)
{
	$type = ucfirst(strtolower($type));
	$retarray = array();
	switch ($type) {
		case "Article":
		$retarray = array('crossref', 'journal', 'key', 'month', 'note', 'url', 'number', 'pages', 'title', 'volume', 'year', 'abstract', 'issn', 'keywords', 'aigaionnote');
		break;
		case "Book":
		$retarray = array('address', 'booktitle', 'crossref', 'edition', 'key', 'month', 'note', 'url', 'number',
				'publisher', 'series', 'title', 'volume', 'year', 'abstract', 'isbn', 'keywords', 'aigaionnote');
		break;
		case "Booklet":
		$retarray = array('address', 'crossref', 'howpublished', 'key', 'month', 'note', 'url', 'title', 'year', 'abstract', 'keywords', 'aigaionnote');
		break;
		case "Inbook":
		$retarray = array('address', 'chapter', 'crossref', 'edition', 'key', 'month', 'note', 'url', 'number',	'pages',
				'publisher', 'series', 'title', 'type', 'volume', 'year', 'abstract', 'isbn', 'keywords', 'aigaionnote');
		break;
		case "Incollection":
		$retarray = array('address', 'booktitle', 'chapter', 'crossref', 'key', 'month', 'note', 'url', 'number', 'organization', 'pages',
				'publisher', 'series', 'title', 'type', 'volume', 'year', 'abstract', 'isbn', 'keywords', 'aigaionnote');
		break;
		case "Inproceedings":
		$retarray = array('address', 'booktitle', 'crossref', 'key', 'month', 'note', 'url', 'number', 'organization', 'pages',
				'publisher', 'series', 'title', 'volume', 'year', 'abstract', 'keywords', 'location', 'aigaionnote');
		break;
		case "Manual":
		$retarray = array('address', 'crossref', 'edition', 'key', 'month', 'note', 'url', 'organization', 'title', 'year', 'abstract', 'keywords', 'aigaionnote');
		break;
		case "Mastersthesis":
		$retarray = array('address', 'crossref', 'key', 'month', 'note', 'url', 'school', 'title', 'type', 'year', 'abstract', 'keywords', 'aigaionnote');
		break;
		case "Misc":
		$retarray = array('crossref', 'howpublished', 'key', 'month', 'note', 'url', 'title', 'year', 'abstract', 'keywords', 'aigaionnote');
		break;
		case "Phdthesis":
		$retarray = array('address', 'crossref', 'key', 'month', 'note', 'url', 'school', 'title', 'type', 'year', 'abstract', 'keywords', 'aigaionnote');
		break;
		case "Proceedings":
		$retarray = array('address', 'booktitle', 'crossref', 'key', 'month', 'note', 'url', 'number', 'organization', 'publisher',
				'series', 'title', 'volume', 'year', 'abstract', 'issn', 'isbn', 'keywords', 'aigaionnote');
		break;
		case "Techreport":
		$retarray = array('address', 'crossref', 'institution', 'key', 'month', 'note', 'url', 'number', 'title', 'type', 'year', 'abstract', 'keywords', 'aigaionnote');
		break;
		case "Unpublished":
		$retarray = array('crossref', 'key', 'month', 'note', 'url', 'title', 'year', 'abstract', 'keywords', 'aigaionnote');
		break;
		default:
		break;
	}
	//a doi can be used for anything... (if used in this site)
	if ($_SESSION["SITE"]->getConfigurationSetting("USE_DOI")=="TRUE") {
		$retarray[] = 'doi';
	}
	return $retarray;
}


function bibGetSummaryFieldArray($type)
{
	$type = ucfirst(strtolower($type));
	$retarray = array();
	switch ($type) {
		case "Article":
			$retarray = array('journal', 'number', 'pages', 'volume', 'year', 'issn');
		break;
		case "Book":
			$retarray = array('publisher', 'series', 'volume', 'year', 'isbn');
		break;
		case "Booklet":
			$retarray = array('howpublished', 'year');
		break;
		case "Inbook":
			$retarray = array('chapter', 'pages', 'publisher', 'series', 'volume', 'year', 'isbn');
		break;
		case "Incollection":
			$retarray = array('booktitle', 'organization', 'pages', 'publisher', 'year', 'isbn');
		break;
		case "Inproceedings":
			$retarray = array('booktitle', 'organization', 'pages', 'publisher', 'year', 'location');
		break;
		case "Manual":
			$retarray = array('edition', 'organization', 'year');
		break;
		case "Mastersthesis":
			$retarray = array('school', 'year');
		break;
		case "Misc":
			$retarray = array('howpublished', 'year');
		break;
		case "Phdthesis":
			$retarray = array('school', 'year');
		break;
		case "Proceedings":
			$retarray = array('organization', 'publisher', 'year');
		break;
		case "Techreport":
			$retarray = array('institution', 'number', 'type', 'year');
		break;
		case "Unpublished":
			$retarray = array('year');
		break;
		default:
		break;
	}
	return $retarray;
}


function bibGetSupportedEntries()
{
	return array('Article', 'Book', 'Booklet', 'Inbook', 'Incollection', 'Inproceedings', 'Manual', 'Mastersthesis', 'Misc', 'Phdthesis', 'Proceedings', 'Techreport', 'Unpublished');
}


function bibGetSupportedFields()
{
	$result = array('bibtexEntryType', 'bibtexCitation', 'mark', 'survey', 'firstpage', 'lastpage', 'address', 'author', 'booktitle', 'chapter', 'crossref', 'edition', 'editor', 'howpublished', 'institution', 'journal', 'key', 'namekey', 'month', 'note', 'url', 'number', 'organization', 'pages', 'publisher', 'school', 'series', 'title', 'type', 'volume', 'year', 'abstract', 'isbn', 'issn', 'keywords', 'location', 'aigaionnote');
	if ($_SESSION["SITE"]->getConfigurationSetting("USE_DOI") == "TRUE") {
		$result[] = 'doi';
	}
	return $result;
}

?>
