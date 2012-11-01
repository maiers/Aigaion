<?php

/*
Web based document management system
Copyright (C) 2003,2004 Hendri Hondorp, Dennis Reidsma, Arthur van Bunningen

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
This file offers several functions to handle publications in Aigaion

	getPublicationArrayFromSubmit(&$publicationArray)
		gets all elements in the publication form and stores it in the input array

	getPublicationArrayFromDB(&$publicationArray)
		gets all elements in the publication form and stores it in the input array

	checkSubmittedPublicationFields(&$publicationArray)
		checks the submitted fields for errors

	publicationRepost(&$publicationArray, $targetPage=publication)
		repost the submitted fields to the selected targetPage

	mergeCrossrefPublication(&$publicationRow)
		merges the data that are present in a crossref but not in the publication array
		returns the pub_id from the crossref if present, ontherwise zero.

	getActualYearFromPublication($publication_table_row)
		return the actual year from the publication. If the publication has crossref, and that crossref
		has a year value (not '0000'), then that value is used, otherwise this returns the year of the
		publication itself

*/

function getPublicationArrayFromSubmit(&$publicationArray)
{
	include_once("bibtexfunctions.php");
	include_once("specialcharfunctions.php");

	$varnames = array (
			'return',  'pub_id', 'action',
			'submittype', 'entered_by',
			'authors', 'editors', 'userfields');
	foreach ($varnames as $varname) {
		$publicationArray[$varname] = getIfSet ($_REQUEST[$varname]);
	}
	unset ($varnames);

	$supportedFields = bibGetSupportedFields();

	foreach ($supportedFields as $supportedField) {
		$publicationArray[$supportedField] = "";
		if (isset($_REQUEST[$supportedField])) {
			$publicationArray[$supportedField] = $_REQUEST[$supportedField];
		}
	}
  $publicationArray['namekey'] = $publicationArray['key'];
  
	if (get_magic_quotes_gpc()==1) {
		stripSlashesFromArray($publicationArray);
	}
	quotesToHTMLFromArray($publicationArray);
}


function getPublicationArrayFromDB($pub_id, &$publicationArray)
{
	include_once("bibtexfunctions.php");
	include_once("specialcharfunctions.php");

	$Q = mysql_query("SELECT * FROM publication WHERE pub_id = ".$pub_id);

	if (mysql_num_rows($Q) > 0)
	{
		$publicationArray = mysql_fetch_array($Q);
	}
	//not all fields have the same name as in the database

	$publicationArray['bibtexEntryType']	= $publicationArray['type'];
	$publicationArray['bibtexCitation']	  = $publicationArray['bibtex_id'];
	$publicationArray['type']						  = $publicationArray['pub_type'];
	$publicationArray['entered_by']				= $publicationArray['entered_by'];
  $publicationArray['key']              = $publicationArray['namekey'];
	quotesToHTMLFromArray($publicationArray);
}

function checkSubmittedPublicationFields($publicationArray)
{
	//error codes:
	//-1: Double bibtex cite id. choose other
	//-2: Title is empty
	//-3: Bibtex ID changed while other publications refer to this bibtex id
	$error = 0;
	if (trim($publicationArray['bibtexCitation']) != "") {
		$pub_id = $publicationArray['pub_id'];
		if ($pub_id != "") {
			$Q = mysql_query("SELECT bibtex_id FROM publication WHERE bibtex_id='".$publicationArray['bibtexCitation']."' AND pub_id!=".$pub_id."");
		} else {
			$Q = mysql_query("SELECT bibtex_id FROM publication WHERE bibtex_id='".$publicationArray['bibtexCitation']."'");
		}

		if ($Q) {
			if (mysql_numrows($Q)>0) { //apparently, there is already an article with the same bibtex ID
				$_SESSION["aigaionerror"] .= "The bibtex ID is already in use, please choose another ID.<br/>\n";
				$_SESSION["aigaionerror"] .= "Similar bibtex IDs:<br/>\n<ul>\n";

				$bibCiteID = substr($publicationArray['bibtexCitation'],0,5);
				$Q = mysql_query("SELECT bibtex_id FROM publication WHERE bibtex_id LIKE '".$bibCiteID."%'");
				while ($R = mysql_fetch_array($Q))
				{
					$_SESSION["aigaionerror"] .= "<li>".$R['bibtex_id']."</li>\n";
				}
				$_SESSION["aigaionerror"] .= "</ul>\n";

				$error = -1;
			}
		}
	}
	if ($publicationArray['title'] == "") {
		$_SESSION["aigaionerror"] .= "The title field is empty, please enter a title.<br/>";
		$error = -2;
	}

	if (($pub_id != "") && ($error == 0)) {
		$Q = mysql_query("SELECT publication.bibtex_id FROM publication, publication as copy WHERE publication.pub_id=".$pub_id." AND publication.bibtex_id=copy.crossref AND publication.bibtex_id!=''");
		if (mysql_num_rows($Q) > 0) {
			$R = mysql_fetch_array($Q);
			if ($R['bibtex_id']!= $publicationArray['bibtexCitation']) {
				$_SESSION["aigaionerror"] .= "Bibtex cite ID changed.<br/>";
				$error = -3;
			}
		}
	}


	return $error;
}


function publicationRepost(&$publicationArray, $targetPage="publication")
{
	$form = "";
	$form .= "<form name='publicationform' enctype='multipart/form-data' method='post' action='?page=".$targetPage."&kind=".$publicationArray['action']."'>\n";

	$keys = array_keys($publicationArray);
	foreach ($keys as $key) {
		$form .= "<input type='hidden' name=".$key." value='".$publicationArray[$key]."'>\n";
	}
	$form .= "</form>\n";
	echo $form;
	?>
	
	<script type="text/javascript">
		document.publicationform.submit();
	</script>
	<?php
}


function mergeCrossrefPublication(&$publicationRow)
{
	if (trim($publicationRow['crossref']) != "") {
		$Q = mysql_query(
				"SELECT *
				FROM publication
				WHERE bibtex_id='".addslashes(trim($publicationRow['crossref']))."'");

		if (mysql_num_rows($Q) > 0) {
			$R = mysql_fetch_array($Q);
		}
		$keys = array_keys($publicationRow);
		$keys = array_keys($publicationRow);
		foreach ($keys as $key) {
			if ($key == "year") {
				if ($publicationRow["actualyear"] == "0000")
					$publicationRow["actualyear"] = "";
			}

			if ($key == "specialchars") {
				if ($R[$key] == 'TRUE')
					$publicationRow[$key]='TRUE';
			}

			if ((trim($publicationRow[$key]) == "") && (trim($R[$key]) != "")) {
				$publicationRow[$key] = $R[$key];
			}
		}
		return $R['pub_id'];
	}
	return 0;
}

function getActualYearFromPublicationRow($publication_table_row) {
  if (($publication_table_row['year'] == '') || ($publication_table_row['year'] == '0000'))
  {
  	if ($publication_table_row["crossref"]) {
  		$crossres=mysql_query("SELECT * FROM publication WHERE bibtex_id='".addslashes($publication_table_row["crossref"])."'");
  		if ($crossres && $crossrow=mysql_fetch_array($crossres)) {
  			if (($crossrow["year"]!= "" ) && ($crossrow["year"]!= "0000")) {
  				return $crossrow["year"];
  			}
  		}
  	}
  	else
  	  return '0000';
  }
  else
  {
    return $publication_table_row['year'];
  }
	return $publication_table_row["year"];
}


?>
