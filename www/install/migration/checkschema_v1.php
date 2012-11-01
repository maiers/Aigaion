<?php
/*
Web based document management system
Copyright (C) 2003-2006 (in alphabetical order):
 Wietse Balkema, Gerbert ten Brinke, Arthur van Bunningen, Hendri Hondorp, Dennis Reidsma

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

/*
Version 1.19
- update to release 1.3.3
*/
function checkDatabaseV1_19($bSilent = false, $AIGAION2_DB_PREFIX = "") {
 if (checkVersion("V1.19",$bSilent, $AIGAION2_DB_PREFIX)) return true;
 if (!checkDatabaseV1_18($bSilent, $AIGAION2_DB_PREFIX)) return false;

 
 if (!$bSilent)
     echo ("Updating version V1.19... "); //for debug
 
 if (!setVersion("V1.19", $bSilent, $AIGAION2_DB_PREFIX))  return false;

 setReleaseVersion("1.3.4", $bSilent, $AIGAION2_DB_PREFIX);

 return true;

} 

/*
Version 1.18
- update to release 1.3.3
*/
function checkDatabaseV1_18($bSilent = false, $AIGAION2_DB_PREFIX = "") {
 if (checkVersion("V1.18",$bSilent, $AIGAION2_DB_PREFIX)) return true;
 if (!checkDatabaseV1_17($bSilent, $AIGAION2_DB_PREFIX)) return false;

 
 if (!$bSilent)
     echo ("Updating version V1.18... "); //for debug
 
 if (!setVersion("V1.18", $bSilent, $AIGAION2_DB_PREFIX))  return false;

 setReleaseVersion("1.3.3", $bSilent, $AIGAION2_DB_PREFIX);

 return true;

} 

/*
Version 1.17
- reinit actualyear field
- update to release 1.3.2
*/
function checkDatabaseV1_17($bSilent = false, $AIGAION2_DB_PREFIX = "") {
 if (checkVersion("V1.17",$bSilent,$AIGAION2_DB_PREFIX)) return true;
 if (!checkDatabaseV1_16($bSilent,$AIGAION2_DB_PREFIX)) return false;

 include_once("publicationfunctions.php");

 if (!$bSilent)
     echo ("Updating version V1.17... "); //for debug
 
 $Q = mysql_query("SELECT * FROM ".$AIGAION2_DB_PREFIX."publication");
 while ($R = mysql_fetch_array($Q))
 {
   $Q2 = mysql_query("UPDATE ".$AIGAION2_DB_PREFIX."publication SET actualyear='".getActualYearFromPublicationRow($R)."'
                                              WHERE pub_id='".$R["pub_id"]."'");
 }
 
 if (!setVersion("V1.17", $bSilent, $AIGAION2_DB_PREFIX))  return false;

 setReleaseVersion("1.3.2", $bSilent, $AIGAION2_DB_PREFIX);

 return true;

} 
/*
Version 1.16
- change publication.title field to text
- run trim() on major fields in publication table
- update to release 1.3.1
- the author cleanname is reinitialised.
*/
function checkDatabaseV1_16($bSilent = false, $AIGAION2_DB_PREFIX = "") {
 if (checkVersion("V1.16",$bSilent,$AIGAION2_DB_PREFIX)) return true;
 if (!checkDatabaseV1_15($bSilent,$AIGAION2_DB_PREFIX)) return false;

 include_once("authorfunctions.php");

 if (!$bSilent)
     echo ("Updating version V1.16... "); //for debug
 $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication` CHANGE `title` `title` TEXT NOT NULL ");

 if (!$res) {
   dbError(mysql_error());
   return false;
 }

 $Q = mysql_query("SELECT pub_id, year, actualyear, title, cleantitle, journal, cleanjournal FROM ".$AIGAION2_DB_PREFIX."publication");
 while ($R = mysql_fetch_array($Q))
 {
   $cleantitle = $R['title'];
   $cleanjournal = $R['journal'];
   stripBibCharsFromString($cleantitle);
   stripBibCharsFromString($cleanjournal);
   $cleantitle = stripQuotesFromString($cleantitle);
   $cleanjournal = stripQuotesFromString($cleanjournal);
   $Q2 = mysql_query("UPDATE ".$AIGAION2_DB_PREFIX."publication SET year='".trim(addslashes($R['year']))."',
                                             actualyear='".trim(addslashes($R['actualyear']))."'
                                             title='".trim(addslashes($R['title']))."'
                                             cleantitle='".trim(addslashes($cleantitle))."'
                                             journal='".trim(addslashes($R['journal']))."'
                                             cleanjournal='".trim(addslashes($cleanjournal))."'
                                              WHERE pub_id='".$R["pub_id"]."'");
 }
 
 #for each author: (re)init the cleanname
 $res = mysql_query("SELECT * FROM ".$AIGAION2_DB_PREFIX."author");
 if ($res) {
   while ($row=mysql_fetch_array($res)) {
     mysql_query("UPDATE ".$AIGAION2_DB_PREFIX."author set cleanname='".addslashes(getCleanNameFromRow($row))."' WHERE ID=".$row["ID"]);
   }
 }

 if (!setVersion("V1.16", $bSilent, $AIGAION2_DB_PREFIX))  return false;

 setReleaseVersion("1.3.1", $bSilent, $AIGAION2_DB_PREFIX);

 return true;

} 

/*
Version 1.15 changes the config.value field from varchar to text and upgrades to release 1.3
*/
function checkDatabaseV1_15($bSilent = false, $AIGAION2_DB_PREFIX = "") {
  if (checkVersion("V1.15",$bSilent, $AIGAION2_DB_PREFIX)) return true;
  if (!checkDatabaseV1_14($bSilent, $AIGAION2_DB_PREFIX)) return false;

  if (!$bSilent)
  	echo ("Updating version V1.15... "); //for debug
  $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."config` CHANGE `value` `value` TEXT NOT NULL ");
  
  if (!$res) {
    dbError(mysql_error());
    return false;
  }

  if (!setVersion("V1.15", $bSilent, $AIGAION2_DB_PREFIX))  return false;
  
  setReleaseVersion("1.3", $bSilent, $AIGAION2_DB_PREFIX);
  
  return true;

}


/*
Version 1.14 introduces the new user rights mechanisms:
    - a table containing the names of the individual rights, and their description
    - a table containing for each user the corresponding rights.
Information about the rights of the currently logged-in user can be obtained through
the hasRights method of the $_SESSION["USER"] object.
*/
function checkDatabaseV1_14($bSilent = false, $AIGAION2_DB_PREFIX = "") {
  if (checkVersion("V1.14",$bSilent, $AIGAION2_DB_PREFIX)) return true;
  if (!checkDatabaseV1_13($bSilent, $AIGAION2_DB_PREFIX)) return false;

  if (!$bSilent)
  	echo ("Updating version V1.14... "); //for debug

  //introduce table that will contain the descriptions of all available rights, and fill it with the
  //initial list of rights
  $res = mysql_query("CREATE TABLE `".$AIGAION2_DB_PREFIX."availablerights` (`name` varchar(20) NOT NULL, `description` varchar(255) NOT NULL, PRIMARY KEY  (`name`)) TYPE=MyISAM;");
  if (!$res) {
      dbError(mysql_error());
      return false;
  }
  
  $query = "INSERT INTO `".$AIGAION2_DB_PREFIX."availablerights` (`name`, `description`) VALUES ";
  $query .= "('attachment_read','read attachments'),";
  $query .= "('attachment_edit','add, edit and delete attachments'),";
  $query .= "('database_manage','manage the database'),";
  $query .= "('note_read','read comments'),";
  $query .= "('note_edit_self','add, edit and delete own comments'),";
  $query .= "('note_edit_all','add, edit and delete all comments'),";
  $query .= "('publication_edit','add, edit and delete publications'),";
  $query .= "('topic_subscription','change own topic subscriptions'),";
  $query .= "('topic_edit','add, edit and delete topics'),";
  $query .= "('user_edit_self','edit own profile (user rights not included)'),";
  $query .= "('user_edit_all','edit all profiles (user rights not included)'),";
  $query .= "('user_assign_rights','assign user rights')";

  $res = mysql_query($query);
  if (!$res) {
    dbError(mysql_error());
    return false;
  }
  
  //introduce table that stores the rights assigned to each aigaion user, and fill it with the appropriate 
  //rights depending on that user's original rights-level.
  $res = mysql_query("CREATE TABLE `".$AIGAION2_DB_PREFIX."userrights` (`user_id` INT(10) NOT NULL, `right_name` VARCHAR(20) NOT NULL, PRIMARY KEY (`right_name`,`user_id`)) TYPE=MyISAM;");
  if (!$res) {
    dbError(mysql_error());
    return false;
  }
  
  //why not take these from the global definitions? -- because the definitions in _global.php will be removed!
    $U_DBADMIN		= 6;
    $U_ADMIN			= 5;
    $U_EDITOR			= 4;
    $U_COMMENTER  = 3;
    $U_READONLY	  = 2;
    $U_NOPDF			= 1;
    $U_NONOTES    = 0;
    
  $rightmap = array();
  ;

  $rightmap[$U_DBADMIN] = array('attachment_read','attachment_edit',
   'database_manage','note_read','note_edit_self','note_edit_all','publication_edit',
   'topic_subscription','topic_edit','user_edit_self','user_edit_all','user_assign_rights');
  $rightmap[$U_ADMIN] = array('attachment_read','attachment_edit',
                     'note_read','note_edit_self','note_edit_all','publication_edit',
   'topic_subscription','topic_edit','user_edit_self','user_edit_all','user_assign_rights');
  $rightmap[$U_EDITOR] = array('attachment_read','attachment_edit',
                     'note_read','note_edit_self',                'publication_edit',
   'topic_subscription','topic_edit','user_edit_self'                                     );
  $rightmap[$U_COMMENTER] = array('attachment_read',
                     'note_read','note_edit_self',
   'topic_subscription',             'user_edit_self'                                     );
  $rightmap[$U_READONLY] = array('attachment_read',
                     'note_read',
   'topic_subscription'                                                                   );
  $rightmap[$U_NOPDF] = array(
                     'note_read',
   'topic_subscription'                                                                   );
  $rightmap[$U_NONOTES] = array(
                     
   'topic_subscription'                                                                   );
  
  $res = mysql_query("SELECT * FROM person");
  if ($res) {
    while ($row = mysql_fetch_array($res)) {
      foreach ($rightmap[$row["u_rights"]] as $right) {
        $res2 = mysql_query("INSERT INTO `".$AIGAION2_DB_PREFIX."userrights` (`user_id`, `right_name`) VALUES ({$row['ID']},'{$right}')");
        if (!$res2) {
          dbError(mysql_error());
          return false;
        }
      }
    }
  }
  
  if (!setVersion("V1.14", $bSilent, $AIGAION2_DB_PREFIX))  return false;

  return true;

}

/*
Version 1.13 introduces the author display styles (First von Last | Last, von, First)
*/
function checkDatabaseV1_13($bSilent = false, $AIGAION2_DB_PREFIX = "") {
  if (checkVersion("V1.13",$bSilent, $AIGAION2_DB_PREFIX)) return true;
  if (!checkDatabaseV1_12($bSilent, $AIGAION2_DB_PREFIX)) return false;

  if (!$bSilent)
  	echo ("Updating version V1.13... "); //for debug

  $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."person` ADD `authordisplaystyle` VARCHAR(5) NOT NULL DEFAULT 'vlf' AFTER `summarystyle`");

  if (!$res) {
    dbError(mysql_error());
    return false;
  }

  if (!setVersion("V1.13", $bSilent, $AIGAION2_DB_PREFIX))  return false;

  return true;

}

/* Version 1.12
	- rename field "author.name" to "author.firstname"
	- introduce person email field
*/
function checkDatabaseV1_12($bSilent = false, $AIGAION2_DB_PREFIX = "")
{
	$dbversion = "V1.12";
	if (checkVersion($dbversion, $bSilent, $AIGAION2_DB_PREFIX)) return true;
	if (!checkDatabaseV1_11($bSilent, $AIGAION2_DB_PREFIX)) return false;

	if (!$bSilent) echo ("Updating to database version {$dbversion}... "); //for debug

	if (!( mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."author` CHANGE name firstname VARCHAR(255) NOT NULL") )) {
		dbError(mysql_error());
		return false;
	}

	if (!( mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."person` ADD `email` VARCHAR(30) NOT NULL default '' AFTER `abbreviation`") )) {
		dbError(mysql_error());
		return false;
	}

	if (!setVersion($dbversion, $bSilent, $AIGAION2_DB_PREFIX))  return false;

	return true;
}

/*
	Version 1.11
	- remove 'database' field in person table
	- set release version to 1.2.1
*/
function checkDatabaseV1_11($bSilent = false, $AIGAION2_DB_PREFIX = "")
{
	if (checkVersion("V1.11", $bSilent, $AIGAION2_DB_PREFIX)) return true;
	if (!checkDatabaseV1_10($bSilent, $AIGAION2_DB_PREFIX)) return false;

	if (!$bSilent)
  	echo ("Updating version V1.11... "); //for debug

  $Q = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."person` DROP `database`;");
  if (!$Q)
  {
    dbError(mysql_error());
    return false;
  }

  setReleaseVersion("1.2.1", $bSilent, $AIGAION2_DB_PREFIX);

  if (!setVersion("V1.11", $bSilent, $AIGAION2_DB_PREFIX))  return false;

  return true;
}

/*
	Version 1.10 introduces:
	- the note-id / cite-id link table
	- Site setting whether to merge publications in single publication display or not
*/
function checkDatabaseV1_10($bSilent = false, $AIGAION2_DB_PREFIX = "")
{
  include_once("specialcharfunctions.php");

  if (checkVersion("V1.10",$bSilent, $AIGAION2_DB_PREFIX)) return true;
  if (!checkDatabaseV1_9($bSilent, $AIGAION2_DB_PREFIX)) return false;

  if (!$bSilent)
  	echo ("Updating version V1.10... "); //for debug

  $Q = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."personpublicationnote` DROP `xrefs`;");
  if (!$Q)
  {
    dbError(mysql_error());
    return false;
  }

  $Q = mysql_query("CREATE TABLE `".$AIGAION2_DB_PREFIX."notecrossrefid` (`note_id` INT( 10 ) NOT NULL ,`xref_id` INT( 10 ) NOT NULL);");
  if (!$Q)
  {
    dbError(mysql_error());
    return false;
  }

  //here the hard work begins: trace all cite_ids in the notes and add the link in the newly created table
  $Q = mysql_query("SELECT pub_id, bibtex_id FROM ".$AIGAION2_DB_PREFIX."publication WHERE bibtex_id != ''");
  if (!$Q)
  {
    dbError(mysql_error());
    return false;
  }
  else
  {
  	while ($R = mysql_fetch_array($Q))
  	{
  		$Q2 = mysql_query("SELECT note_id FROM ".$AIGAION2_DB_PREFIX."personpublicationnote WHERE text LIKE '%".addslashes($R['bibtex_id'])."%'");
  		if (mysql_num_rows($Q2) > 0)
  		{
  			while ($R2 = mysql_fetch_array($Q2))
  			{
	  			$Q3 = mysql_query("INSERT INTO ".$AIGAION2_DB_PREFIX."notecrossrefid (`note_id`, `xref_id`) VALUES ('".$R2['note_id']."', '".$R['pub_id']."')");
	  			if (!$Q3)
				  {
				  	echo "INSERT INTO ".$AIGAION2_DB_PREFIX."notecrossrefid (`note_id`, `xref_id`) VALUES ('".$R2['note_id']."', '".$R['pub_id']."')";
				    dbError(mysql_error());
				    return false;
				  }
				}
			}
  	}
  }

  //there was a bug in the cleantitle field, so reinit
  $Q = mysql_query("SELECT title, journal FROM ".$AIGAION2_DB_PREFIX."publication");
  if ($Q)
  {
    while ($R=mysql_fetch_array($Q))
    {
      $cleantitle = $R["title"];
      $cleanjournal = $R["journal"];
      if ($R["specialchars"] == "TRUE")
      {
        stripBibCharsFromString($cleantitle);
        stripBibCharsFromString($cleanjournal);
      }
      mysql_query("UPDATE ".$AIGAION2_DB_PREFIX."publication SET cleantitle='".addslashes(stripQuotesFromString($cleantitle))."', cleanjournal='".addslashes(stripQuotesFromString($cleanjournal))."' WHERE pub_id=".$R["pub_id"]);
    }
  }

  if (!setVersion("V1.10", $bSilent, $AIGAION2_DB_PREFIX))  return false;

  return true;
}

/*
Version 1.9 sets a default value for xrefs
*/
function checkDatabaseV1_9($bSilent = false, $AIGAION2_DB_PREFIX = "") {

  if (checkVersion("V1.9",$bSilent, $AIGAION2_DB_PREFIX)) return true;
  if (!checkDatabaseV1_8($bSilent, $AIGAION2_DB_PREFIX)) return false;

  if (!$bSilent)
  	echo ("Updating version V1.9... "); //for debug

  //Add xrefs field for reference speed up.
  $Q = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."personpublicationnote` CHANGE `xrefs` `xrefs` VARCHAR( 255 ) DEFAULT '' NOT NULL");
  if (!$Q)
  {
    dbError(mysql_error());
    return false;
  }

  if (!setVersion("V1.9", $bSilent, $AIGAION2_DB_PREFIX))  return false;

  return true;
}


/*
Version 1.8 introduces the following schema changes:
 = the cleanname column for authors is reinitialsed: the old version of checkDatabasV1_5
   contained an error leading to not all authors being correctly updated for that column.
 = The column 'actualyear' is introduced in the publication table. This column will be used to store the year
   of a publication as it is determined by either the year field, or, in case of the presence of a crossref,
   by the year field of the crossref publication
 = the title field is allowed to be longer now, cf RFE 1562026

*/
function checkDatabaseV1_8($bSilent = false, $AIGAION2_DB_PREFIX = "") {
  include_once("publicationfunctions.php");
  include_once("authorfunctions.php");

  if (checkVersion("V1.8",$bSilent, $AIGAION2_DB_PREFIX)) return true;
  if (!checkDatabaseV1_7($bSilent, $AIGAION2_DB_PREFIX)) return false;

  if (!$bSilent)
  	echo ("Updating version V1.8... "); //for debug

  #for each author: (re)init the cleanname
  $res = mysql_query("SELECT * FROM ".$AIGAION2_DB_PREFIX."author");
  if ($res) {
    while ($row=mysql_fetch_array($res)) {
      mysql_query("UPDATE ".$AIGAION2_DB_PREFIX."author set cleanname='".addslashes(getCleanNameFromRow($row))."' WHERE ID=".$row["ID"]);
    }
  }

  #add column 'actualyear' for publications
  $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication` ADD `actualyear` VARCHAR(12) NOT NULL DEFAULT '0000' AFTER `year`");
  if (!$res) {
    dbError(mysql_error());
    return false;
  }

  #allow title to be longer
  $Q = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication` CHANGE `title` `title` TEXT NOT NULL ");
  if (!$Q)
  {
    dbError(mysql_error());
    return false;
  }

  #for all publications: init actualyear
  $res = mysql_query("SELECT * FROM ".$AIGAION2_DB_PREFIX."publication");
  if ($res) {
    while ($row=mysql_fetch_array($res)) {
      $actualyear = getActualYearFromPublicationRow($row);
      mysql_query("UPDATE ".$AIGAION2_DB_PREFIX."publication SET actualyear='".addslashes($actualyear)."' WHERE pub_id=".$row["pub_id"]);
    }
  }


  if (!setVersion("V1.8", $bSilent, $AIGAION2_DB_PREFIX))  return false;

  return true;
}

/*
 set release version 1.2
*/
function checkDatabaseV1_7($bSilent = false, $AIGAION2_DB_PREFIX = "")
{
  if (checkVersion("V1.7",$bSilent, $AIGAION2_DB_PREFIX)) return true;
  if (!checkDatabaseV1_6($bSilent,$AIGAION2_DB_PREFIX)) return false;

  if (!$bSilent)
  	echo ("Updating version V1.7... "); //for debug

  setReleaseVersion("1.2", $bSilent, $AIGAION2_DB_PREFIX);

  if (!setVersion("V1.7", $bSilent,$AIGAION2_DB_PREFIX))  return false;

  return true;

}
/*
Version 1.6
 - Converts the first and lastpage fields from int(10) to varchar (10)
 - Converts the chapter field from int(10) to varchar (10)
 - Adds the 'xrefs' varchar for speeding up cite referencing
*/
function checkDatabaseV1_6($bSilent = false, $AIGAION2_DB_PREFIX = "")
{
  if (checkVersion("V1.6",$bSilent, $AIGAION2_DB_PREFIX)) return true;
  if (!checkDatabaseV1_5($bSilent, $AIGAION2_DB_PREFIX)) return false;

  if (!$bSilent)
  	echo ("Updating version V1.6... "); //for debug

	//Convert firstpage from int to varchar
  $Q = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication` CHANGE `firstpage` `firstpage` VARCHAR( 10 ) DEFAULT '0' NOT NULL");
  if (!$Q)
  {
    dbError(mysql_error());
    return false;
  }

  //Convert lastpage from int to varchar
  $Q = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication` CHANGE `lastpage` `lastpage` VARCHAR( 10 ) DEFAULT '0' NOT NULL");
  if (!$Q)
  {
    dbError(mysql_error());
    return false;
  }

  //Convert lastpage from int to varchar
  $Q = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication` CHANGE `chapter` `chapter` VARCHAR( 10 ) NOT NULL DEFAULT '0'");
  if (!$Q)
  {
    dbError(mysql_error());
    return false;
  }

  //Add xrefs field for reference speed up.
  $Q = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."personpublicationnote` ADD `xrefs` VARCHAR( 255 ) NOT NULL");
  if (!$Q)
  {
    dbError(mysql_error());
    return false;
  }

  if (!setVersion("V1.6", $bSilent, $AIGAION2_DB_PREFIX))  return false;

  return true;

}


/*
Version 1.5 introduces several new things.
==author information gets an additional 'von_part' column.
==the year field is transformed from year(4) to varchar(12), in order to allow not only years
  before 1900, but also allow year spans such as "1900--1939"
==for several database elements, columns are introduced that contain 'clean' versions of the element,
  to facilitate sorting and searching.
    - for author: clean_author contains the full name, in the form (surname, firstname), with all
      specialcharacters stripped
    - for publication: cleanTitle, cleanJournal,

*/
function checkDatabaseV1_5($bSilent = false, $AIGAION2_DB_PREFIX = "") {
  include_once("authorfunctions.php");
  include_once("specialcharfunctions.php");
  include_once("publicationfunctions.php");

  if (checkVersion("V1.5",$bSilent, $AIGAION2_DB_PREFIX)) return true;
  if (!checkDatabaseV1_4($bSilent,$AIGAION2_DB_PREFIX)) return false;

  if (!$bSilent)
  	echo ("Updating version V1.5... "); //for debug


  #add column 'von'
  $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."author` ADD `von` VARCHAR(255) NOT NULL default '' AFTER `surname`");
  if (!$res) {
    dbError(mysql_error());
    return false;
  }

  #change year column
  $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication` CHANGE `year` `year` VARCHAR(12) NOT NULL DEFAULT '0000'");
  if (!$res) {
    dbError(mysql_error());
    return false;
  }

  #add column 'cleanname' for authors
  $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."author` ADD `cleanname` VARCHAR(255) NOT NULL default '' AFTER `specialchars`");
  if (!$res) {
    dbError(mysql_error());
    return false;
  }
  #for each author: init the cleanname
  $res = mysql_query("SELECT * FROM ".$AIGAION2_DB_PREFIX."author");
  if ($res) {
    while ($row=mysql_fetch_array($res)) {
      mysql_query("UPDATE ".$AIGAION2_DB_PREFIX."author set cleanname='".addslashes(getCleanNameFromRow($row))."' WHERE ID=".$row["ID"]);
    }
  }

  #add columns 'cleantitle' and 'cleanjournal' for publications
  $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication` ADD `cleantitle` VARCHAR(255) NOT NULL default '' AFTER `specialchars`");
  if (!$res) {
    dbError(mysql_error());
    return false;
  }
  $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication` ADD `cleanjournal` VARCHAR(255) NOT NULL default '' AFTER `specialchars`");
  if (!$res) {
    dbError(mysql_error());
    return false;
  }
  #for each publication: init the cleantitle and cleanjournal
  $res = mysql_query("SELECT * FROM ".$AIGAION2_DB_PREFIX."publication");
  if ($res) {
    while ($row=mysql_fetch_array($res)) {
        $cleantitle = $row["title"];
        $cleanjournal = $row["journal"];
      if ($row["specialchars"] == "TRUE")
      {
        stripBibCharsFromString($cleantitle);
        stripBibCharsFromString($cleanjournal);
      }
      mysql_query("UPDATE ".$AIGAION2_DB_PREFIX."publication SET cleantitle='".addslashes(stripQuotesFromString($cleantitle))."', cleanjournal='".addslashes(stripQuotesFromString($cleanjournal))."' WHERE pub_id=".$row["pub_id"]);
    }
  }

  if (!setVersion("V1.5", $bSilent, $AIGAION2_DB_PREFIX))  return false;

  return true;

}

/*
Version 1.4 introduces a DOI field in the publication table, and initialises
it from the userfields if those contain a doi field
*/
function checkDatabaseV1_4($bSilent = false, $AIGAION2_DB_PREFIX = "") {
  include_once("PARSEENTRIES.php");
  include_once("doifunctions.php");

  if (checkVersion("V1.4",$bSilent, $AIGAION2_DB_PREFIX)) return true;
  if (!checkDatabaseV1_3($bSilent, $AIGAION2_DB_PREFIX)) return false;

  if (!$bSilent)
  	echo ("Updating version V1.4... "); //for debug

  #add column
  $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication` ADD `doi` VARCHAR(255) NOT NULL default '' AFTER `url`");
  if (!$res) {
    dbError(mysql_error());
    return false;
  }

  #init column from userfields... for all publications, if userfield contains some doi, try to grab it and
  #store it in the doi field.
  $res = mysql_query("SELECT * FROM ".$AIGAION2_DB_PREFIX."publication");
  if (!$res) {
    dbError(mysql_error());
    return false;
  }
  //parseentries is used to simply parse the userfields

  while ($row = mysql_fetch_array($res)) {
    //for all publications
    $userfields = $row["userfields"];
    if (trim($userfields)!="") {
      //if userfields not empty
      $parse = NEW PARSEENTRIES();
      $parse->loadBibtexString("@ARTICLE{a,\n".$userfields."\n}");
      $parse->extractEntries();
	  list($preamble, $strings, $entries, $undefinedStrings) = $parse->returnArrays();
	  //echo "<pre>";
	  //print_r($entries[0]);
	  //echo "</pre>";
	  if (isset($entries[0]["doi"])) {
        //try to get doi field
        $doi = $entries[0]["doi"];
        //strip doi
        $doi = stripDoi($doi);
        if (trim($doi) != "") {
          //add doi to table if not empty
          $res2 = mysql_query("UPDATE ".$AIGAION2_DB_PREFIX."publication SET doi='".addslashes($doi)."' WHERE pub_id=".$row["pub_id"]);
          if (!$res2) {
          dbError(mysql_error());
            return false;
          }
        }
      }
    }
  }
  //NOTE: the doi s are also left in the userfields! If somebody does not want privileged dois in the database,
  //these fields may need to be used again.

  if (!setVersion("V1.4", $bSilent, $AIGAION2_DB_PREFIX))  return false;

  return true;

}

/*
Version 1.3 changes the notefield to "text" and introduces a release version field.
*/
function checkDatabaseV1_3($bSilent = false, $AIGAION2_DB_PREFIX = "") {
  if (checkVersion("V1.3",$bSilent, $AIGAION2_DB_PREFIX)) return true;
  if (!checkDatabaseV1_2($bSilent, $AIGAION2_DB_PREFIX)) return false;

  if (!$bSilent)
  	echo ("Updating version V1.3... "); //for debug

  $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication` CHANGE `note` `note` TEXT NOT NULL");

  if (!$res) {
    dbError(mysql_error());
    return false;
  }

 	$res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."aigaiongeneral` ADD `releaseversion` VARCHAR( 10 ) NOT NULL");

  if (!$res) {
    dbError(mysql_error());
    return false;
  }

  if (!setVersion("V1.3", $bSilent, $AIGAION2_DB_PREFIX))  return false;

  setReleaseVersion("1.1", $bSilent, $AIGAION2_DB_PREFIX);

  return true;

}

/*
Version 1.2 introduces the latin character conversion and multipage publication lists
*/
function checkDatabaseV1_2($bSilent = false, $AIGAION2_DB_PREFIX = "") {
  if (checkVersion("V1.2",$bSilent, $AIGAION2_DB_PREFIX)) return true;
  if (!checkDatabaseV1_1($bSilent, $AIGAION2_DB_PREFIX)) return false;

  if (!$bSilent)
  	echo ("Updating version V1.2... "); //for debug

  $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."person` ADD `liststyle` SMALLINT NOT NULL DEFAULT '0' AFTER `summarystyle`");

  if (!$res) {
    dbError(mysql_error());
    return false;
  }

 	$_SESSION['liststyle'] = '0';

  if (!setVersion("V1.2", $bSilent, $AIGAION2_DB_PREFIX))  return false;

  return true;

}
/*
Version 1.1 introduces the new table for site-config-settings, and fills it with default values, possibly taken from the config file.
*/
function checkDatabaseV1_1($bSilent = false, $AIGAION2_DB_PREFIX = "") {
  if (checkVersion("V1.1",$bSilent, $AIGAION2_DB_PREFIX)) return true;
  if (!checkDatabaseV1_0($bSilent, $AIGAION2_DB_PREFIX)) return false;
  if (!$bSilent)
  	echo ("Updating version V1.1... "); //for debug

  #create new table for config settings. Columns: 'setting' / 'value' ; primkey 'setting'
  $res = mysql_query("CREATE TABLE `".$AIGAION2_DB_PREFIX."config` (`setting` varchar(255) NOT NULL, `value` varchar(255) NOT NULL, PRIMARY KEY  (`setting`)) TYPE=MyISAM;");
  if (!$res) {
      dbError(mysql_error());
      return false;
  }

  #fill table with settings from config file: getConfigSetting("CFG_ADMIN"), getConfigSetting("CFG_ADMINMAIL"), $DIR, $URL, $FILEDIR, $ALLOWED_ATTACHMENT_EXTENSIONS, $ALLOW_ALL_EXTERNAL_ATTACHMENTS, $server_not_writable, $WINDOW_TITLE, $ALWAYS_INCLUDE_PAPERS_FOR_TOPIC, $SHOW_TOPICS_ON_FRONTPAGE=True $SHOW_TOPICS_ON_FRONTPAGE_LIMIT=5;
  global $CFG_ADMIN, $CFG_ADMINMAIL, $DIR, $URL, $FILEDIR, $ALLOWED_ATTACHMENT_EXTENSIONS, $ALLOW_ALL_EXTERNAL_ATTACHMENTS, $WINDOW_TITLE, $ALWAYS_INCLUDE_PAPERS_FOR_TOPIC, $SHOW_TOPICS_ON_FRONTPAGE, $SHOW_TOPICS_ON_FRONTPAGE_LIMIT;
  if (!isset($CFG_ADMIN)) {
    $CFG_ADMIN = "Administrator";
  }
  if (!isset($CFG_ADMINMAIL)) {
    $CFG_ADMINMAIL = "Administrator@some.where";
  }
  if (!isset($ALLOWED_ATTACHMENT_EXTENSIONS)) {
    $ALLOWED_ATTACHMENT_EXTENSIONS_STR = ".doc,.gif,.htm,.html,.jpeg,.jpg,.pdf,.png,.tif,.tiff,.txt,.zip";
  } else {
    $ALLOWED_ATTACHMENT_EXTENSIONS_STR = implode(",", $ALLOWED_ATTACHMENT_EXTENSIONS);
  }
  if (isset($ALLOW_ALL_EXTERNAL_ATTACHMENTS) && ($ALLOW_ALL_EXTERNAL_ATTACHMENTS)) {
    $ALLOW_ALL_EXTERNAL_ATTACHMENTS = "TRUE";
  } else {
    $ALLOW_ALL_EXTERNAL_ATTACHMENTS = "FALSE";
  }
  if (!isset($WINDOW_TITLE)) {
    $WINDOW_TITLE = "Aigaion: A multi user annotated bibliography";
  }
  if (isset($ALWAYS_INCLUDE_PAPERS_FOR_TOPIC) && $ALWAYS_INCLUDE_PAPERS_FOR_TOPIC) {
    $ALWAYS_INCLUDE_PAPERS_FOR_TOPIC = "TRUE";
  } else {
    $ALWAYS_INCLUDE_PAPERS_FOR_TOPIC = "FALSE";
  }

  if (isset($SHOW_TOPICS_ON_FRONTPAGE) && $SHOW_TOPICS_ON_FRONTPAGE) {
    $SHOW_TOPICS_ON_FRONTPAGE = "TRUE";
  } else {
    $SHOW_TOPICS_ON_FRONTPAGE = "FALSE";
  }
  if (!isset($SHOW_TOPICS_ON_FRONTPAGE_LIMIT)) {
    $SHOW_TOPICS_ON_FRONTPAGE_LIMIT = 5;
  }
  if (isset($SERVER_NOT_WRITABLE) && $SERVER_NOT_WRITABLE) {
    $SERVER_NOT_WRITABLE = "TRUE";
  } else {
    $SERVER_NOT_WRITABLE = "FALSE";
  }

  $query = "INSERT INTO ".$AIGAION2_DB_PREFIX."config (`setting`,`value`) VALUES ";
  $query .= "('CFG_ADMIN','".addslashes($CFG_ADMIN)."'),";
  $query .= "('CFG_ADMINMAIL','".addslashes($CFG_ADMINMAIL)."'),";
  $query .= "('ALLOWED_ATTACHMENT_EXTENSIONS','".addslashes($ALLOWED_ATTACHMENT_EXTENSIONS_STR)."'),";
  $query .= "('ALLOW_ALL_EXTERNAL_ATTACHMENTS','".addslashes($ALLOW_ALL_EXTERNAL_ATTACHMENTS)."'),";
  $query .= "('WINDOW_TITLE','".addslashes($WINDOW_TITLE)."'),";
  $query .= "('ALWAYS_INCLUDE_PAPERS_FOR_TOPIC','".addslashes($ALWAYS_INCLUDE_PAPERS_FOR_TOPIC)."'),";
  $query .= "('SHOW_TOPICS_ON_FRONTPAGE','".addslashes($SHOW_TOPICS_ON_FRONTPAGE)."'),";
  $query .= "('SHOW_TOPICS_ON_FRONTPAGE_LIMIT','".addslashes($SHOW_TOPICS_ON_FRONTPAGE_LIMIT)."'),";
  $query .= "('SERVER_NOT_WRITABLE','".addslashes($SERVER_NOT_WRITABLE)."');";

  $res = mysql_query($query);
  if (!$res) {
      dbError(mysql_error());
      return false;
  }

  if (!setVersion("V1.1",$bSilent, $AIGAION2_DB_PREFIX))  return false;
  return true;
}

/*
Version 1.0 checks whether the updates for the previous major release (V0.xx) was performed
*/
function checkDatabaseV1_0($bSilent = false, $AIGAION2_DB_PREFIX = "") {
  if (checkVersion("V1.0",$bSilent, $AIGAION2_DB_PREFIX)) return true;
  include("checkschema_v0.php");
  if (!checkDatabaseV0_14($bSilent, $AIGAION2_DB_PREFIX)) return false;
  if (!$bSilent)
  	echo ("Updating version V1.0... "); //for debug
  if (!setVersion("V1.0",$bSilent, $AIGAION2_DB_PREFIX))  return false;
  return true;
}
?>
