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
This file offers several functions to handle authors in Aigaion

	getNameFromRow($row, $toInitials)
		returns a name from $row. When $initials == 'Y', the first name
		is converted to initials.

	getCleanNameFromRow($row, $toInitials)
		returns a "clean" name from $row. When $initials == 'Y', the first name
		is converted to initials. When special chars occur, they are stripped off.

	getPrettyNameFromRow($row, $toInitials)
		returns a name from $row. When $initials == 'Y', the first name
		is converted to initials. When special chars occur, they are converted to HTML.

	getAuthorArrayFormSubmit(&$authorArray)
		Gets all the author fields that were posted into an array.

	getAuthorArrayFromDB($ID, &$authorArray)
		Gets all the author fields form the database into an array.

	checkSubmittedAuthorFields(&$authorArray)
		Checks whether there are invalid entries in the author field. Also checks for double entries.

	authorRepost(&$authorArray)
		Reposts all the fields in the authorArray.

	getNrPubForAuthor($id)
		Returns the number of publications of the specified author

	getNrPubReadForAuthor($id)
		Returns the number of publications read of the specified author

	getSimilarAuthorList($author_id)
		Returns an array with IDs of similar authors.

*/
include_once("bibtexfunctions.php");
include_once("specialcharfunctions.php");

function getNameFromRow($row, $toInitials = 'N')
{
	$surname = $row['surname'];

	$von = ltrim (trim ($row['von']) . ' ');
	## 'trim' removes any leading OR trailing spaces.
	## If "$row['von']" ends up empty, 'ltrim' further takes care of that by removing also the space we added.
	## sesc79/061106

	if ($toInitials == 'N') {
		$firstname = bibParseFirstname($row['firstname']);
		//return htmlentities(urldecode($surname.", ".$row['firstname']),ENT_QUOTES);
		return $von.$surname.", ".$firstname;
	} else {
		return $von.$surname.", ".bibParseInitials($row['firstname']);
	}
}


function getCleanNameFromRow($row, $toInitials = 'N')
{
	$surname = $row['surname'];
	$von = ltrim (trim ($row['von']) . ' ');
	## 'trim' removes any leading OR trailing spaces.
	## If "$row['von']" ends up empty, 'ltrim' further takes care of that by removing also the space we added.
	## sesc79/061106

	if ($toInitials == 'N') {
		$name = $von.$surname.", ".$row['firstname'];
	} else {
		$name = $von.$surname.", ".bibParseInitials($row['firstname']);
	}

	if ($row['specialchars'] == "TRUE") {
		stripBibCharsFromString($name);
	}
	return $name;
}


function getPrettyNameFromRow($row, $toInitials = 'N')
{
	$surname = $row['surname'];
	$von = ltrim (trim ($row['von']) . ' ');
	## 'trim' removes any leading OR trailing spaces.
	## If "$row['von']" ends up empty, 'ltrim' further takes care of that by removing also the space we added.
	## sesc79/061106

  $displaystyle= $_SESSION["USER"]->getPreference("authordisplaystyle");
    if ($displaystyle=="vlf") {
    	if ($toInitials == 'N') {
    		$name = $von.$surname.", ".$row['firstname'];
    	} else {
    		$name = $von.$surname.", ".bibParseInitials($row['firstname']);
    	}
    } else if ($displaystyle == "vl") {
    	$name = $von.$surname;
    } else {
    	if ($toInitials == 'N') {
    		$name = $row['firstname']." ".$von.$surname;
    	} else {
    		$name = bibParseInitials($row['firstname'])." ".$von.$surname;
    	}
    }

	if ($row['specialchars'] == "TRUE") {
		prettyPrintBibCharsFromString($name);
	}

	quotesToHTMLFromString($name);
	return $name;
}


function getAuthorArrayFromSubmit(&$authorArray)
{
	$fieldlist = array ('return', 'action', 'submittype', 'ID', 'surname', 'firstname', 'von', 'email', 'institute', 'url', 'specialchars');
	foreach ($fieldlist as $field):
		$authorArray[$field]  = getIfSet ($_REQUEST[$field]);
	endforeach;
	$authorArray['confirm'] = getIfSet ($_REQUEST['confirm'], '', '', 'FALSE');

	if (get_magic_quotes_gpc() == 1) {
		stripSlashesFromArray($authorArray);
	}
	quotesToHTMLFromArray($authorArray);
}


function getAuthorArrayFromDB($ID, &$authorArray)
{
	$Q = mysql_query("SELECT * FROM author WHERE ID = {$ID}");

	if (mysql_num_rows($Q) > 0) {
		$authorArray = mysql_fetch_array($Q);
	}
	$authorArray['confirm'] = "FALSE";

	quotesToHTMLFromArray($authorArray);
}


function checkSubmittedAuthorFields(&$authorArray)
{
	//check for similar authors
	if ((trim($authorArray['surname']) == "") && (trim($authorArray['firstname']) == "")) {
		$_SESSION["aigaionerror"] .= "Both first and lastname are empty, please correct your input.<br/>\n";
		return -1;
	}

	if ($authorArray['confirm'] != "TRUE") {
		$ID = $authorArray['ID'];
		$surname = html_entity_decode($authorArray['surname'], ENT_QUOTES);
		stripBibCharsFromString($surname);
		$authorfound = false;

		$Q = mysql_query("SELECT ID, surname, von, firstname, specialchars FROM author");
		while ($R = mysql_fetch_array($Q)):
			$rowSurname = $R['surname'];
			if ($R['specialchars'] == 'TRUE'):
				stripBibCharsFromString($rowSurname);
			endif;
			if ((levenshtein($surname, $rowSurname) < 3) && ($ID != $R['ID'])):
				$_SESSION['aigaionerror'] .= "Similar author found: <br/><b>".getPrettyNameFromRow($R)."</b><br/>\n<br/>\n";
				$authorfound = true;
			endif;
		endwhile;
		if ($authorfound):
			$_SESSION['aigaionerror'] .= "Update?";
			return -2;
		endif;
	}
	return 0;
}


function authorRepost(&$authorArray)
{
	$form  = "";
	$form .= "<form name='authorform' enctype='multipart/form-data' method='post' ";
	$form .= "action='?page=author&kind={$authorArray['action']}'>\n";

	$keys = array_keys($authorArray);
	foreach ($keys as $key) {
		$form .= "<input type='hidden' name='{$key}' value='{$authorArray[$key]}'>\n";
	}
	$form .= "</form>\n";
	echo $form;
	?>
	<script type="text/javascript">document.authorform.submit();</script>
	<?php
}


function getNrPubForAuthor($id)
{
	$query="SELECT COUNT(DISTINCT pub_id) FROM publicationauthor WHERE author='{$id}' ";
	$Q = mysql_query($query);
	$R = mysql_fetch_array($Q);
	return $R[0];
}


/*
Returns number of publications which the logged person read for the author $id...
*/
function getNrPubReadForAuthor($id)
{
	$person_id = $_SESSION["USER"]->userId();
	$query = "SELECT COUNT(DISTINCT publicationauthor.pub_id)
			FROM publicationauthor,personpublicationmark
			WHERE author = '{$id}'
				AND personpublicationmark.person_id = {$person_id}
				AND personpublicationmark.pub_id = publicationauthor.pub_id
				AND personpublicationmark.read = 'y' ";
	$Q = mysql_query($query);
	$R = mysql_fetch_array($Q);
	return $R[0];
}


function getSimilarAuthorList($author_id)
{
	include_once("specialcharfunctions.php");

	$Q = mysql_query("SELECT * FROM author WHERE ID = '{$author_id}' ");
	$R = mysql_fetch_array($Q);

	$authorSurname = $R['surname'];

	if ($R['specialchars'] == "TRUE") {
		stripBibCharsFromString($authorSurname);
	}
	$authorSurname = strtolower($authorSurname);

	$similarAuthors = array();
	if (trim($authorSurname) == "") {
		return $similarAuthors;
	}

	$Q = mysql_query("SELECT * FROM author ORDER BY surname, firstname");
	if (mysql_num_rows($Q) > 0) {
		while ($R = mysql_fetch_array($Q)) {
			$surname = $R['surname'];

			if ($R['specialchars'] == "TRUE") {
				stripBibCharsFromString($surname);
				stripBibCharsFromString($name);
			}
			$surname = strtolower($surname);

			if ((levenshtein($authorSurname, $surname) < 2) && ($R['ID'] != $author_id))
				$similarAuthors[] = $R['ID'];
		}
	}

	return $similarAuthors;
}

?>
