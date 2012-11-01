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
/**
*	Interface messages
*
*	@author Mark Grimshaw
*
*	$Header: /cvsroot/aigaion/webinterface/includes/OSBib/create/MESSAGES.php,v 1.3 2006/12/01 14:27:49 reidsma Exp $
*/
class MESSAGES
{
// Constructor
	function MESSAGES()
	{
	}
/**
* Print the message
*/
	function text($arrayName, $indexName, $extra = FALSE)
	{
		include_once(OSBIB__MISC);
		include_once(OSBIB__UTF8);
		$utf8 = new UTF8();
		$arrays = $this->loadArrays();
		$string = $arrays[$arrayName][$indexName];
		$string = $extra ?	preg_replace("/###/", $utf8->smartUtf8_decode($extra), $string) :
			preg_replace("/###/", "", $string);
// Display hints as per the CSS hint class.
		if($arrayName == 'hint')
			$string = MISC::span($string, "hint");
		return $utf8->encodeUtf8($string);
	}
// English messages
	function loadArrays()
	{
		return array(
		"heading" => array(
				"styles"		=>	"Styles###",
				"helpStyles"	=>	"Bibliographic Style Creation and Editing",
			),
// Hint messages
		"hint" => array(
				"styleShortName"	=>	"(No spaces)",
				"caseSensitive"	=>	"(Fields are case-sensitive)",
				"integer"	=>	"Integer",
				"multiples"	=>	"(Multiples can be chosen)",
			),
// Miscellaneous items that don't fit anywhere else
		"misc" => array(
// In select boxes - when it is not necessary to choose an existing selection.  WIKINDX will skip over this one. 
// Could be '---'
				'ignore'	=>	"IGNORE",
// This next one is required in BIBTEXPARSE - whatever the language, NEITHER THE KEY NOR THE VALUE SHOULD BE CHANGED!
// Leave as is!
// Leave as is!
// Leave as is!
				'IGNORE'	=>	"ignore",
// continue....
				"edited"	=>	"edited",
				"added"		=>	"added",
				"deleted"	=>	"deleted",
				"add"		=>	"add",
// Used in SUCCESS.php when a user chooses a user bibliography to browse.  The message is "Successfully set Bibliography".
				"set"		=>	"set",
				"top"		=>	"Top",
			),
// Mapping WKX_resource.type to description.
		"resourceType" => array(
				'book'			=>	"Book",
				'book_article'		=>	"Book Chapter",
				'web_article'		=>	"Internet",
				'journal_article'	=>	"Journal Article",
				'newspaper_article'	=>	"Newspaper Article",
				'thesis'		=>	"Thesis/Dissertation",
				'proceedings_article'	=>	"Proceedings Article",
// TV or Radio broadcast
				'broadcast'		=>	'Broadcast',
				'film'			=>	"Film",
// Legal Ruling or Regulation
				'legal_ruling'		=>	"Legal Rule/Regulation",
// Computer software
				"software"	=>	"Software",
// Art etc.
				"artwork"	=>	"Artwork",
// Audiovisual material
				"audiovisual"	=>	"Audiovisual",
// Legal cases
				"case"		=>	"Legal Case",
// Parliamentary bill (law)
				"bill"		=>	"Bill",
// Classical (historical) work
				"classical"	=>	"Classical Work",
				"conference_paper"	=>	"Conference Paper",
// Reports or documentation
				"report"	=>	"Report/Documentation",
// Government report or documentation
				"government_report"	=>	"Government Report/Documentation",
// Legal/Government Hearing
				"hearing"	=>	"Hearing",
// Online databases
				"database"	=>	"Online Database",
				"magazine_article"	=>	"Magazine Article",
				"manuscript"	=>	"Manuscript",
// Maps
				"map"		=>	"Map",
// Charts/images
				"chart"		=>	"Chart/Image",
// Statute
				"statute"	=>	"Statute",
// Patents
				"patent"	=>	"Patent",
// Personal Communication
				"personal"	=>	"Personal Communication",
// Unpublished work
				"unpublished"	=>	"Unpublished Work",
// Conference proceedings (complete set)
				"proceedings"	=>	"Proceedings",
// Music
				"music_album"	=>	"Recorded Music Album",
				"music_track"	=>	"Recorded Music Track",
				"music_score"	=>	"Music Score",
// For anything else that does not fit into the above categories.
				'miscellaneous'		=>	"Miscellaneous",
// Similar to miscellaneous but a part of something else
				'miscellaneous_section'		=>	"Miscellaneous Section",
// Generic resource types used when creating bibliographic styles.
				"genericBook"		=>	"Generic book-type",
				"genericArticle"	=>	"Generic article-type",
				"genericMisc"		=>	"Generic miscellaneous",
			),
// Form submit button text
		"submit" => array(
				"Submit"		=>	"Submit",
				"Add"			=>	"Add",
				"Delete"		=>	"Delete",
				"Confirm"		=>	"Confirm",
				"Edit"			=>	"Edit",
				"Proceed to Confirm"	=>	"Proceed to Confirm",
				"List"			=>	"List",
				"Proceed"		=>	"Proceed",
				"Search"		=>	"Search",
				"Select"		=>	"Select",
// Add citation 
				"Cite"			=>	"Cite",
// Reset button for forms
				"reset"			=>	"Reset",
			),
// Messages for adding citations to quotes, notes, musings , comments etc. and for administration of 
// citation templates within bibliographic style creation/editing
		"cite" => array(
// The displayed hyperlink next to the textarea form input
				"cite"			=>	"Cite",
				"citationFormat"	=>	"Citation Formatting",
// In-text citation style as opposed to footnote style citations.
				"citationFormatInText"	=>	"In-text style",
				"citationFormatEndnote"	=>	"Endnote style",
				"citationFormatFootnote"	=>	"Footnote style",
				"creatorList"		=>	"Creator list abbreviation",
				"creatorListSubsequent"	=>	"Creator list abbreviation (subsequent appearances)",
				"creatorSep"		=>	"Creator delimiters",
				"creatorStyle" 		=>	"Creator style",
				"lastName"		=>	"Last name only",
// 'Last name only' is a choice in a select box and should not be translated
				"useInitials"		=>	"If 'Last name only', use initials to differentiate between creators with the same surname",
				"consecutiveCreator"	=>	"For consecutive citations by the same creator(s) use the following template:",
				"consecutiveCreatorSep"	=>	"and separate citations with:",
// The template is something like '(author|, year)' that the user is asked to enter
				"template"		=>	"Template",
				"consecutiveCitationSep" =>	"Separate consecutive citations with",
// Formatting of years
				"yearFormat"		=>	"Year format",
// Normal, superscript or subscript of citation
				"normal"		=>	"Normal text",
				"superscript"		=>	"Superscript",
				"subscript"		=>	"Subscript",
// Ambiguous citations
				"ambiguous"		=>	"Ambiguous citations",
				"ambiguousTitle"	=>	"Use the following template",
				"ambiguousYear"		=>	"Add a letter after the year",
				"ambiguousUnchanged"	=>	"Leave citation unchanged",
// For footnote-style citations
				"footnoteStyleBib"	=>	"Format like bibliography",
				"footnoteStyleInText"	=>	"Format like in-text citations",
				"ibid"		=>	"Replace consecutive citations for the same resource and the same page with",
				"idem"		=>	"Replace consecutive citations for the same resource but a different page with",
				"opCit"		=>	"Replace previously cited resources with",
// How to format the citation pages in footnote-style citations
				"footnoteCitationPageFormat" => "Format the citation page(s)",
				"footnoteCitationPageFormatNever" => "Never print citation page(s)",
				"footnoteCitationPageFormatBib"	=>	"Same as the bibliographic templates",
				"footnoteCitationPageFormatTemplate" => "Use the template below",
				"enclosingCharacters"	=>	"Parentheses or other characters enclosing the citation",
				"followCreatorTemplate"	=>	"Use template below if a single citation is in the same sentence as the first creator's surname",
				"endnoteStyle"	=>	"Endnote/Footnote",
				"endnoteStyle1"	=>	"Endnotes: incrementing",
				"endnoteStyle2"	=>	"Endnotes: same ID for same resource",
				"endnoteStyle3"	=>	"Footnotes: incrementing",
// Ordering of the appended bibliography for in-text citations and endnote-style citations using the same id number for each cited resource
				"orderBib1"	=>	"Bibliography ordering",
				"orderBib2"	=>	"(For in-text citations and endnote-style citations using the same ID number)",
				"orderBib3"	=>	"Use this order for endnote-style citations using the same ID number:",
// For a particular resource type (personal communication for example), replace the in-text citation template with another template
				"typeReplace"	=>	"For in-text citations, replace the citation template with this template",
				"endnoteFormat1" => "Format of the citation in the text",
				"endnoteFormat2" => "Format of the citation in the endnotes",
				"idFormat"	=>	"Use the same ID number for citations from the same resource",
// Formatting of the id number in the endnotes for endnote-style citations
				"endnoteIDEnclose"	=>	"Parentheses or other characters enclosing the ID number",
				"endnoteIDFormat"	=>	"Format of the ID number",
// This follows on from sentence above....  Split the pages from the main citation placing the main citation immediately after the creator names in the text and 
// the pages immediately following the quote.  e.g. if the citation is in the form:
// Grimshaw states:  "WIKINDX is wonderful" [cite]123:25[/cite], 
//  the result will be 
// Grimshaw (2005) states:  "WIKINDX is wonderful" (p.25) rather than 
// Grimshaw states:  "WIKINDX is wonderful" (2005, p.25).
				"followCreatorPageSplit" => "and split the citation placing the main citation after the creator names and the page number after the quote:",
// For subsequent citations from the same resource
				"subsequentCreator"	=>	"For subsequent citations from the same resource use the following template:",
// This follows on from the text in 'subsequentCreator'
				"subsequentFields"	=>	"only if the sentence containing the citation has the creator surname, title or shortTitle in it:",
// If no year for in-text citations, replace year field
				"replaceYear"	=>	"If no year, replace year field with the following",
// When compiling the appended bibliography for in-text citations, certain resources (e.g. APA personal communication) are not added.
				"notInBibliography"	=>	"Do not add to the bibliography when cited:",
// When using endnote-style citations and defining templates using fields such as 'creator', 'pages', 'year' or 'title'.  Don't translate 'creator', 'pages' or 'citation'.
				"endnoteFieldFormat" => "Fields are formatted as defined in in-text citation formatting above unless using footnotes in which case the 'creator' field is defined below and the 'pages' field format is defined in the footnote template in the bibliography section. If the 'citation' field is used, it should be by itself and it refers to the bibliographic/footnote templates below.",
				"footnoteTemplate"	=>	"Footnote template",
// Set the range within which the subsequent creator template (for in-text citations) is used
				"subsequentCreatorRange"	=>	"Range within which subsequent citations are searched for",
				"subsequentCreatorRange1"	=>	"Entire text",
				"subsequentCreatorRange2"	=>	"Paragraph",
				"removeTitle"	=>	"Remove title and shortTitle fields from the citation if either of those fields is in the same sentence:",
			),
// Administration of bibliographic styles
		"style" => array(
				"addLabel"		=>	"Add a Style",
				"copyLabel"		=>	"Copy a Style",
				"editLabel"		=>	"Edit Styles",
				"shortName"		=>	"Short Name",
				"longName"		=>	"Long Name",
				"primaryCreatorSep"	=>	"Primary creator delimiters",
				"otherCreatorSep"	=>	"Other creator delimiters",
				"ifOnlyTwoCreators"	=>	"If only two creators",
				"creatorSepBetween"	=>	"between",
				"creatorSepLast"	=>	"before last",
				"sepCreatorsFirst"	=>	"Between first two creators",
				"sepCreatorsNext"	=>	"Between following creators",
				"primaryCreatorStyle" 	=>	"Primary creator style",
				"otherCreatorStyle"	=>	"Other creator styles",
				"creatorFirstStyle" 	=>	"First",
				"creatorOthers"		=>	"Others",
				"creatorInitials"	=>	"Initials",
				"creatorFirstName"	=>	"First name",
				"creatorFirstNameFull"	=>	"Full",
				"creatorFirstNameInitials"	=>	"Initial",
				"primaryCreatorList"	=>	"Primary creator list abbreviation",
				"otherCreatorList"	=>	"Other creator list abbreviation",
				"creatorListFull"	=>	"Full list",
				"creatorListLimit"	=>	"Limit list",
// The next 3 surround form text boxes:
// "If xx or more creators, list the first xx and abbreviate with xx".  For example:
// "If 4 or more creators, list the first 1 and abbreviate with ,et. al"
				"creatorListIf"		=>	"If",
				"creatorListOrMore"		=>	"or more creators, list the first",
				"creatorListAbbreviation"	=>	"and abbreviate with",
				"titleCapitalization"	=>	"Title capitalization",
// Title as entered with no changes to capitalization
				"titleAsEntered"	=>	"As entered",
				"availableFields"	=>	"Available fields:",
				"editionFormat"		=>	"Edition format",
				"monthFormat"		=>	"Month format",
				"dateFormat"		=>	"Date format",
				"dayFormat"		=>	"Day format",
// Add a leading zero to day if less than 10.
				"dayLeadingZero"	=>	"Add leading zero",
				"pageFormat"		=>	"Page format",
// Length of film, broadcast etc.
				"runningTimeFormat"	=>	"Running time format",
// When displaying a book that has no author but has an editor, do we put the editor in the position occupied 
// by the author?
				"editorSwitchHead"		=>	"Editor switch",
				"editorSwitch"		=>	"For books with no author but an editor, put editor in author position",
				"yes"			=>	"Yes",
				"no"			=>	"No",
				"editorSwitchIfYes"	=>	"If 'Yes', replace editor field in style definitions with",
// Uppercase creator names?
				"uppercaseCreator"	=>	"Uppercase all names",
// For repeated creator names in next bibliographic item
				"repeatCreators"	=>	"For works immediately following by the same creators",
				"repeatCreators1"	=>	"Print the creator list",
				"repeatCreators2"	=>	"Do not print the creator list",
				"repeatCreators3"	=>	"Replace creator list with text below",
// Fallback formatting style when a specific resource type has none defined
				"fallback"		=>	"Fallback style",
				"bibFormat"		=>	"Bibliography Formatting",
// Italic font
				"italics"		=>	"Italics",
// For user specific month naming
				"userMonthSelect"	=>	"Use month names defined below",
				"userMonths"	=>	"User-defined month names (all fields must be completed if selected above)",
// Date ranges for e.g. conferences
				"dateRange"		=>	"Date range",
				"dateRangeDelimit1"	=>	"Delimiter between start and end dates if day and month given",
				"dateRangeDelimit2"	=>	"Delimiter between start and end dates if month only given",
				"dateRangeSameMonth"	=>	"If start and end months are equal",
				"dateRangeSameMonth1"	=>	"Print both months",
				"dateRangeSameMonth2"	=>	"Print start month only",
// Different puncutation may be required if a month is given with no day.
				"dateMonthNoDay"	=>	"If a date has a month but no day",
				"dateMonthNoDay1"	=>	"Use style definition unchanged",
				"dateMonthNoDay2"	=>	"Replace date field in template with:",
// Don't translate 'date'
				"dateMonthNoDayHint"	=>	"(Use 'date' as the field)",
// Re-write creator(s) portion of templates to handle styles such as DIN 1505.
				"rewriteCreator1"	=>	"Split creator lists and add strings",
				"rewriteCreator2"	=>	"Add string to first name in list",
				"rewriteCreator3"	=>	"Add string to remaining names",
				"rewriteCreator4"	=>	"Before name:",
				"rewriteCreator5"	=>	"To each name:",
				"bibTemplate"	=>	"Bibliography template",
				"footnotePageField"	=> "(Footnote template may also use 'pages')",
// Some general help for using templates displayed in the Admin|Style page
				"templateHelp1"	=>	"1. The three generic bibliography templates are required and will be used if a displayed resource has no bibliographic template.",
				"templateHelp2"	=>	"2. The footnote templates are only required for those styles that use footnotes for citations.  In all cases, the complete bibliography ('works cited') for footnote styles, as well as for endnote and in-text styles, uses the bibliography template.",
// don't translate 'citation'
				"templateHelp3"	=>	"3. For footnote citations, the 'citation' field above refers to the footnote template or, if that does not exist, to the bibliography template or, if that does not exist, to the fallback style.",
// don't translate 'pages'
				"templateHelp4"	=>	"4. The 'pages' field in the bibliography template refers to the complete article page range; in the footnote template, it refers to the specific citation page(s).",
				"templateHelp5"	=>	"5. If you define a footnote template for a resource you must also define the bibliography template for that resource.",
				"templateHelp6"	=>	"6. If a resource is missing the first field in the bibliography template you may replace that field with the fields in the partial template (this allows a reordering of the initial fields).",
// Characters separating title and subtitle
				"titleSubtitleSeparator"	=>	"Title/subtitle separator",
// See "templateHelp6" above
				"partialTemplate"	=>	"Partial template",
// Use the partial template to replace all of the bibliography template.
				"partialReplace"	=>	"Replace all of original template with partial template",
// For template previewing, allow the use to preview by turning various fields on and off.
				"previewFields"	=>	"Preview with the following fields",
			),
		"creators" => array(
				"author"	=>	"Authors",
				"editor"	=>	"Editors",
				"translator"	=>	"Translators",
				"reviser"	=>	"Revisers",
				"seriesEditor"	=>	"Series Editors",
// For films etc.
				"director"	=>	"Director",
				"producer"	=>	"Producer",
// For artwork
				"artist"	=>	"Artist",
				"performer"	=>	"Performer",
// For legal cases
				"counsel"	=>	"Counsel",
// For classical works of doubtful provenance
				"attributedTo"	=>	"Attributed to",
// Map makers
				"cartographer"	=>	"Cartographer",
// Charts/images
				"creator"	=>	"Creator",
// For patents
				"inventor"	=>	"Inventor",
				"issuingOrganisation"	=>	"Issuing Organisation",
				"agent"		=>	"Agent/Attorney",
// International patent author
				"intAuthor"	=>	"International Author",
// Personal Communication
				"recipient"	=>	"Recipient",
// For Musical works
				"composer"	=>	"Composer",
				"conductor"	=>	"Conductor",
// Advice on what to do when editing a creator name and the new name already exists in the database.
				"creatorExists"	=>	"If you proceed, this edited creator will be deleted and all references in the database to it will be replaced by references to the pre-existing creator.",
			),
		"powerSearch" => array(
				"ascending"	=>	"Ascending",
				"descending"	=>	"Descending",
				"order1"	=>	"1st. sort by",
				"order2"	=>	"2nd. sort by",
				"order3"	=>	"3rd. sort by",
			),
		"list" => array(
				'creator'	=>	"First Creator",
				'title'		=>	"Title",
				'year'		=>	"Publication Year",
			),
		);
	}
}
?>