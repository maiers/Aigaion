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
Version 0.14 introduces the 'url' field for publications, due to manifold requests
*/
function checkDatabaseV0_14($bSilent = false, $AIGAION2_DB_PREFIX = "") {
  if (checkVersion("V0.14",$bSilent, $AIGAION2_DB_PREFIX)) return true;
  if (!checkDatabaseV0_13($bSilent, $AIGAION2_DB_PREFIX)) return false;

  if (!$bSilent)
  	echo ("Updating version V0.14... "); //for debug

  //insert public field for preference. Default is 'FALSE'
  $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication` ADD `url` VARCHAR(255) NOT NULL default '' AFTER `abstract`");

  if (!$res) {
    dbError(mysql_error());
    return false;
  }

  if (!setVersion("V0.14",$bSilent, $AIGAION2_DB_PREFIX))  return false;

  return true;

}


/*
Version 0.13 sets the default theme to 'darkdefault', and checks all publications and author to see whether they contain any special chars,
and if so, sets the column 'specialchars' to true
*/
function checkDatabaseV0_13($bSilent = false, $AIGAION2_DB_PREFIX = "") {
  if (checkVersion("V0.13",$bSilent, $AIGAION2_DB_PREFIX)) return true;
  if (!checkDatabaseV0_12($bSilent, $AIGAION2_DB_PREFIX)) return false;
  if (!$bSilent)
  	echo ("Updating version V0.13... "); //for debug

  $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."person` ALTER `theme` SET DEFAULT 'darkdefault'");

  if (!$res) {
    dbError(mysql_error());
    return false;
  }

  #set specialchars for publications
  include_once("specialcharfunctions.php");
  $res = mysql_query("SELECT * FROM ".$AIGAION2_DB_PREFIX."publication");
  if ($res) {
    while ($row=mysql_fetch_array($res)) {
        if (findSpecialCharsInArray($row)) {
            $res2 = mysql_query("UPDATE ".$AIGAION2_DB_PREFIX."publication SET specialchars='TRUE' WHERE pub_id=".$row["pub_id"]);
            if (!$res2) {
              dbError(mysql_error());
              return false;
            }
        }
    }
  }

  #set specialchars for authors
  $res = mysql_query("SELECT * FROM ".$AIGAION2_DB_PREFIX."author");
  if ($res) {
    while ($row=mysql_fetch_array($res)) {
        if (findSpecialCharsInArray($row)) {
            $res2 = mysql_query("UPDATE ".$AIGAION2_DB_PREFIX."author SET specialchars='TRUE' WHERE ID=".$row["ID"]);
            if (!$res2) {
              dbError(mysql_error());
              return false;
            }
        }
    }
  }

  if (!setVersion("V0.13", $bSilent, $AIGAION2_DB_PREFIX))  return false;

  return true;
}


/*
Version 0.12 introduces a field in the persons table for storing preference for opening attachments. Default is "not in a new browser window".
*/
function checkDatabaseV0_12($bSilent = false, $AIGAION2_DB_PREFIX = "") {
  if (checkVersion("V0.12",$bSilent, $AIGAION2_DB_PREFIX)) return true;
  if (!checkDatabaseV0_11($bSilent, $AIGAION2_DB_PREFIX)) return false;
  if (!$bSilent)
  	echo ("Updating version V0.12... "); //for debug

  //insert public field for preference. Default is 'FALSE'
  $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."person` ADD `newwindowforatt` enum('TRUE','FALSE') NOT NULL default 'FALSE' AFTER `theme`");

  if (!$res) {
    dbError(mysql_error());
    return false;
  }

  if (!setVersion("V0.12",$bSilent, $AIGAION2_DB_PREFIX))  return false;

  return true;

}


/*
Version 0.11 introduces a field in the persons table for storing personal summary displaystyle preference. Default is "author".
*/
function checkDatabaseV0_11($bSilent = false, $AIGAION2_DB_PREFIX = "") {
  if (checkVersion("V0.11",$bSilent, $AIGAION2_DB_PREFIX)) return true;
  if (!checkDatabaseV0_10($bSilent, $AIGAION2_DB_PREFIX)) return false;
  if (!$bSilent)
  	echo ("Updating version V0.11... "); //for debug

  //insert public field for style. Default is 'author'
  $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."person` ADD `summarystyle` VARCHAR( 255 ) NOT NULL DEFAULT 'author' AFTER `theme`");

  if (!$res) {
    dbError(mysql_error());
    return false;
  }

  if (!setVersion("V0.11",$bSilent, $AIGAION2_DB_PREFIX))  return false;

  return true;

}

/*
Version 0.10 introduces a field in the persons table for storing personal theme-preferences. Default is 'darkdefault'.
*/
function checkDatabaseV0_10($bSilent = false, $AIGAION2_DB_PREFIX = "") {
  if (checkVersion("V0.10",$bSilent, $AIGAION2_DB_PREFIX)) return true;
  if (!checkDatabaseV0_9($bSilent, $AIGAION2_DB_PREFIX)) return false;
  if (!$bSilent)
  	echo ("Updating version V0.10... "); //for debug

  //insert public field for themes. Default is 'default'
  $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."person` ADD `theme` VARCHAR( 255 ) NOT NULL DEFAULT 'darkdefault' AFTER `ID`");

  //don't forget to update current theme, otherwise site will look stupid until next refresh...
    $_SESSION["usertheme"] = "darkdefault";
    global $THEME;
    $THEME="themes/darkdefault/";
  if (!$res) {
    dbError(mysql_error());
    return false;
  }

  if (!setVersion("V0.10",$bSilent, $AIGAION2_DB_PREFIX))  return false;

  return true;

}


function checkDatabaseV0_9($bSilent = false, $AIGAION2_DB_PREFIX = "") {
  if (checkVersion("V0.9",$bSilent, $AIGAION2_DB_PREFIX)) return true;
  if (!checkDatabaseV0_8($bSilent, $AIGAION2_DB_PREFIX)) return false;
  if (!$bSilent)
  	echo ("Updating version V0.9... "); //for debug

  //we want to change the 'type' column
  //first get the entire data, then convert the column,
  //than place back the renewed data
  $data = mysql_query("SELECT pub_id, type FROM ".$AIGAION2_DB_PREFIX."publication");

  $alter = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication` CHANGE `type` `type` ENUM( 'Article', 'Book', 'Booklet', 'Inbook', 'Incollection', 'Inproceedings', 'Manual', 'Mastersthesis', 'Misc', 'Phdthesis', 'Proceedings', 'Techreport', 'Unpublished', 'Journal', 'BookSection', 'Report', 'Dissertation' )");
  if (!$alter) {
    dbError(mysql_error());
    //echo "Error at action 1";
    return false;
  }

  while ($row = mysql_fetch_array($data)) {
    $newType = convertPublicationType($row['type']);
    $result = mysql_query("UPDATE ".$AIGAION2_DB_PREFIX."publication SET type='".$newType."' WHERE pub_id=".$row['pub_id']);
  }

  //and add new field: crossref, namekey and userfields
  $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication` ADD `crossref` VARCHAR( 255 ) NOT NULL");
  if (!$res) {
    dbError(mysql_error());
    //echo "Error at action 2";
    return false;
  }

  $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication` ADD `namekey` VARCHAR( 255 ) NOT NULL");
  if (!$res) {
    dbError(mysql_error());
    //echo "Error at action 3";
    return false;
  }

  $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication` ADD `userfields` TEXT NOT NULL");
  if (!$res) {
    dbError(mysql_error());
    //echo "Error at action 4";
    return false;
  }

  //and we add the 'specialchars' indication.
  $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication` ADD `specialchars` ENUM( 'FALSE', 'TRUE' ) NOT NULL DEFAULT 'FALSE'");
  if (!$res) {
    dbError(mysql_error());
    //echo "Error at action 5";
    return false;
  }

  $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."author` ADD `specialchars` ENUM( 'FALSE', 'TRUE' ) NOT NULL DEFAULT 'FALSE'");
  if (!$res) {
    dbError(mysql_error());
    //echo "Error at action 5";
    return false;
  }

  $alter = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication` CHANGE `type` `type` ENUM( 'Article', 'Book', 'Booklet', 'Inbook', 'Incollection', 'Inproceedings', 'Manual', 'Mastersthesis', 'Misc', 'Phdthesis', 'Proceedings', 'Techreport', 'Unpublished')");
  if (!$alter) {
    dbError(mysql_error());
    //echo "Error at action 6";
    return false;
  }

  if (!setVersion("V0.9",$bSilent, $AIGAION2_DB_PREFIX))  return false;

  return true;

}
function convertPublicationType($oldType)
{
  $oldType = ucfirst(strtolower($oldType));
  switch($oldType) {
    case "Journal":
    $newType = "Article";
    break;
    case "Booksection":
    $newType = "Inbook";
    break;
    case "Proceedings":
    $newType = "Inproceedings";
    break;
    case "Report":
    $newType = "Techreport";
    break;
    case "Dissertation":
    $newType = "Phdthesis";
    break;
    default:
    $newType = $oldType;
    break;
  }
  //echo "old: ".$oldType." new: ".$newType."<br>";
return $newType;
}

/*
Version 0.8 introduces the new structures for uploading and storing attachments.
new columns isremote, name & mime created in publicationfile, and initialised for existing
publications
*/
function checkDatabaseV0_8($bSilent = false, $AIGAION2_DB_PREFIX = "") {
  if (checkVersion("V0.8",$bSilent, $AIGAION2_DB_PREFIX)) return true;
  if (!checkDatabaseV0_7($bSilent, $AIGAION2_DB_PREFIX)) return false;
  if (!$bSilent)
  	echo ("Updating version V0.8... "); //for debug

  //new columns isremote, name & mime for publicationfile
  $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publicationfile` ADD `isremote` ENUM( 'TRUE', 'FALSE' ) NOT NULL DEFAULT 'FALSE' AFTER `person_id`");
  if (!$res) {
    dbError(mysql_error());
    return false;
  }
  $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publicationfile` ADD `name` VARCHAR(255) NOT NULL DEFAULT '' AFTER `person_id`");
  if (!$res) {
    dbError(mysql_error());
    return false;
  }
  $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publicationfile` ADD `mime` VARCHAR(100) NOT NULL DEFAULT '' AFTER `person_id`");
  if (!$res) {
    dbError(mysql_error());
    return false;
  }

  $my_upload = new file_upload;

  //set name and mime for all existing attachments...
  $res = mysql_query("SELECT * FROM ".$AIGAION2_DB_PREFIX."publicationfile");
  if ($res) {
    while ($row = mysql_fetch_array($res)) {
        //exposedname == location
        //mime = ext for now (dat klopt niet!)
        $mime=$my_upload->get_extension($row["location"]);
        //collect as many correct mime types as possible - without resorting to the only-sometimes-available-library mimemagic
        if ($mime==".pdf") {
            $mime="application/pdf";
        }
        if ($mime==".doc") {
            $mime="application/msword";
        }
        if ($mime==".txt") {
            $mime="text/plain";
        }

        $res2 = mysql_query("UPDATE `".$AIGAION2_DB_PREFIX."publicationfile` SET name='".addslashes($row["location"])."', mime='".addslashes($mime)."' WHERE pub_id=".$row["pub_id"]." AND location='".addslashes($row["location"])."'");
        if (!$res2) {
          dbError(mysql_error());
          return false;
        }
    }
  }


  //set all notes that are in the database to public
  //$Q1 = mysql_query("UPDATE `personpublicationnote` SET `rights` = 'public');
  //if (!q1) {
  //  dbError(mysql_error());
  //  return false;
  //}
  if (!setVersion("V0.8",$bSilent, $AIGAION2_DB_PREFIX))  return false;

  return true;

}


/*
Version 0.7 introduces a field in the notes table where you can set whether the note is public (readable for all users) or not
(readable only for user who entered it). Default value for new notes is, of course, 'public'.
*/
function checkDatabaseV0_7($bSilent = false, $AIGAION2_DB_PREFIX = "") {
  if (checkVersion("V0.7",$bSilent, $AIGAION2_DB_PREFIX)) return true;
  if (!checkDatabaseV0_6($bSilent, $AIGAION2_DB_PREFIX)) return false;
  if (!$bSilent)
  	echo ("Updating version V0.7... "); //for debug

  //insert public field for notes on a publication. Default is public
  $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."personpublicationnote` ADD `rights` ENUM( 'public', 'private' ) NOT NULL DEFAULT 'public' AFTER `person_id`");

  if (!$res) {
    dbError(mysql_error());
    return false;
  }

  //set all notes that are in the database to public
  //$Q1 = mysql_query("UPDATE `personpublicationnote` SET `rights` = 'public');
  //if (!q1) {
  //  dbError(mysql_error());
  //  return false;
  //}
  if (!setVersion("V0.7",$bSilent, $AIGAION2_DB_PREFIX))  return false;

  return true;

}

/*
Version 0.6 introduces fields in the database for keeping track of which publications have been read by a user.
You are assumed to have read a publication if - you entered it - you marked it - you made a note of it.
*/
function checkDatabaseV0_6($bSilent = false, $AIGAION2_DB_PREFIX = "") {
    if (checkVersion("V0.6",$bSilent, $AIGAION2_DB_PREFIX))return true;
    if (!checkDatabaseV0_5($bSilent, $AIGAION2_DB_PREFIX))  return false;
    if (!$bSilent)
    	echo ("Updating version V0.6... "); //for debug

    // Default mark of publication is 5
    $res = mysql_query("ALTER TABLE `".$AIGAION2_DB_PREFIX."publication` CHANGE `mark` `mark` INT( 11 ) DEFAULT '5' NOT NULL");
    if (!$res) {
        dbError(mysql_error());
        return false;
    }

    //All publications which someone entered become read
    $Q1 = mysql_query("SELECT * FROM ".$AIGAION2_DB_PREFIX."publication");
    if (!$Q1) {
        dbError(mysql_error());
        return false;
    }
    while ($R1 = mysql_fetch_array($Q1))
    {
        $entered_by_person = $R1['entered_by'];
        $pub_id = $R1['pub_id'];
        //first assure that every pub has an entry for the entered_by person
        $Q2 = mysql_query("INSERT IGNORE INTO ".$AIGAION2_DB_PREFIX."personpublicationmark (pub_id,person_id,`mark`,`read`) VALUES ($pub_id, $entered_by_person,-1, 'y') ");
        if (!$Q2) {
            dbError(mysql_error());
            return false;
        }
        //next: make sure this entry has its read-attribute on 1
        $Q3 = mysql_query("UPDATE ".$AIGAION2_DB_PREFIX."personpublicationmark SET `read`='y' WHERE pub_id=$pub_id AND person_id=$entered_by_person");
        if (!$Q3) {
            dbError(mysql_error());
            return false;
        }
    }
    if (!setVersion("V0.6",$bSilent, $AIGAION2_DB_PREFIX))  return false;
    return true;
}

/*
CODE : v0.5
Summary:
- Extend bibtex field so it can store longer IDs (default 10 was way too short)
- extend name, surname and email fields for authors, allowing longer names/addresses
- introduce 'institute' field for authors
*/
function checkDatabaseV0_5($bSilent = false, $AIGAION2_DB_PREFIX = "") {
    if (checkVersion("V0.5",$bSilent, $AIGAION2_DB_PREFIX))return true;
    if (!checkDatabaseV0_4($bSilent, $AIGAION2_DB_PREFIX))  return false;
    if (!$bSilent)
    	echo ("Updating version V0.5... "); //for debug

    // extend bibtex attribute of pub table
    $res = mysql_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."publication MODIFY `bibtex_id` VARCHAR( 255 ) NOT NULL;");
    if (!$res) {
        dbError(mysql_error());
        return false;
    }

    // extend name attribute of author table
    $res = mysql_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."author MODIFY `name` VARCHAR( 255 ) NOT NULL;");
    if (!$res) {
        dbError(mysql_error());
        return false;
    }

    // extend surname attribute of author table
    $res = mysql_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."author MODIFY `surname` VARCHAR( 255 ) NOT NULL;");
    if (!$res) {
        dbError(mysql_error());
        return false;
    }

    // extend email attribute of author table
    $res = mysql_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."author MODIFY `email` VARCHAR( 255 ) NOT NULL;");
    if (!$res) {
        dbError(mysql_error());
        return false;
    }

    // Add institute for author
    $res = mysql_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."author ADD institute VARCHAR( 255 ) NOT NULL;");
    if (!$res) {
        dbError(mysql_error());
        return false;
    }

    if (!setVersion("V0.5",$bSilent, $AIGAION2_DB_PREFIX))  return false;
    return true;
}


/*
CODE : v0.4
Summary: Add abstract field
*/
function checkDatabaseV0_4($bSilent = false, $AIGAION2_DB_PREFIX = "") {

    if (checkVersion("V0.4",$bSilent, $AIGAION2_DB_PREFIX))return true;
    if (!checkDatabaseV0_3($bSilent, $AIGAION2_DB_PREFIX))  return false;
    if (!$bSilent)
    	echo ("Updating version V0.4... "); //for debug

    // add column 'abstract', text, to table 'publication'
    $res = mysql_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."publication ADD abstract TEXT NOT NULL;");
    if (!$res) {
        dbError(mysql_error());
        return false;
    }

    if (!setVersion("V0.4",$bSilent, $AIGAION2_DB_PREFIX))  return false;
    return true;
}

/*
CODE    : V0.3
Summary : Change of topic subscription mechanism
Changes in database structure:
    V0.3 migrates the database to the new topic subscription mechanism.
    It removes the 'subscribed' column again, and removes all persontopic links for which 'subscribed' is 0.
    It adds a column to 'person', called 'lastreviewedtopic', which indicates the highest topic_id
    that this person is supposed to have seen. Any topic with a higher ID is counted 'new', and indicated as
    such on the front page / topic review page.
    Initially, this ID is set for each person to the highest topic ID present for that person
    in persontopic.
*/
function checkDatabaseV0_3($bSilent = false, $AIGAION2_DB_PREFIX = "") {

    if (checkVersion("V0.3",$bSilent, $AIGAION2_DB_PREFIX))return true;
    if (!checkDatabaseV0_2($bSilent, $AIGAION2_DB_PREFIX)) return false;
    if (!$bSilent)
    	echo ("Updating version V0.3... "); //for debug


    //  if checkDatabaseV0_2 returned true, make modifications for transfer from V0.2 to V0.3
    //  and echo feedback, set version to "V0.3"

    // ==1==
    // add column 'lastreviewedtopic', int(10), to table 'person'
    $res = mysql_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."person ADD lastreviewedtopic INT(10) NOT NULL default '1';");
    if (!$res) {
        dbError(mysql_error());
        return false;
    }
    // ==2==
    // for each person in 'person', get maximum int-value topicID in persontopic,
    //      set that maximum in column 'lastreviewedtopic'
    $res = mysql_query("SELECT person_id, MAX(topic_id) FROM ".$AIGAION2_DB_PREFIX."persontopic GROUP BY person_id");
    if (!$res) {
        dbError(mysql_error());
        return false;
    }
    while ($row=mysql_fetch_array($res)) {
        $res2 = mysql_query("UPDATE ".$AIGAION2_DB_PREFIX."person SET lastreviewedtopic='".$row["MAX(topic_id)"]."' WHERE ID='".$row["person_id"]."'");
        if (!$res2) {
            dbError(mysql_error());
            return false;
        }
    }

    // ==3==
    // remove all rows from persontopic where subscribed != '1'
    $res = mysql_query("DELETE FROM ".$AIGAION2_DB_PREFIX."persontopic WHERE subscribed!='1';");
    if (!$res) {
        dbError(mysql_error());
        return false;
    }

    // ==4==
    // remove column 'subscribed' from table persontopic
    $res = mysql_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."persontopic DROP subscribed;");
    if (!$res) {
        dbError(mysql_error());
        return false;
    }

    if (!setVersion("V0.3",$bSilent, $AIGAION2_DB_PREFIX))  return false;
    return true;

}


/*
CODE    : V0.2
Summary : Addition of topic subscription mechanism
Changes in database structure:
    V0.2 adds to V0.1 the topic subscription mechanism, which includes a column 'subscribed:INT(2)'
    in the table 'persontopic'. Default value is 1.
    'subscribed == 1' means that the person wants to see this topic in his/her personal tree.
    'subscribed == 0' means that the person does NOT want to see this topic in his/her personal tree.
    Absence of this value for a certain topic means that the person has not decided yet upon this topic,
    e.g. because it was only very recently added.
    Default value is 1 means that after this change, everybody is still subscribed to the same current
    topics, and counted 'undecided' on all other topics.
*/
function checkDatabaseV0_2($bSilent = false, $AIGAION2_DB_PREFIX = "") {
    if (checkVersion("V0.2",$bSilent, $AIGAION2_DB_PREFIX))return true;
    if (!checkDatabaseV0_1($bSilent, $AIGAION2_DB_PREFIX)) return false;
    if (!$bSilent)
    	echo ("Updating version V0.2... "); //for debug


    //  if checkDatabaseV0_1 returned true, make modifications for transfer from V0.1 to V0.2
    //  and echo feedback, set version to "V0.2"
    $res = mysql_query("ALTER TABLE ".$AIGAION2_DB_PREFIX."persontopic ADD subscribed INT(2) NOT NULL default '1';");
    if (!$res) {
        dbError(mysql_error());
        return false;
    }

    if (!setVersion("V0.2",$bSilent, $AIGAION2_DB_PREFIX))  return false;
    return true;
}

/*
CODE    : V0.1
Summary : Addition of database version management mechanism
Changes in database structure:
    V0.1 adds to V0 the table 'agaiongeneral', with a column 'version'. This is necessary for the
    database version management mechanism. The table will be initialised with one row, containing the
    value 'V0.1' in the column 'version'.
*/
function checkDatabaseV0_1($bSilent = false, $AIGAION2_DB_PREFIX = "") {
    if (checkVersion("V0.1", $bSilent, $AIGAION2_DB_PREFIX))return true;
    if (!checkDatabaseV0($bSilent, $AIGAION2_DB_PREFIX)) return false;
    if (!$bSilent)
    	echo ("Updating version V0.1... "); //for debug


    //otherwise, try to make that table and column, and init with one row. Set version to "V0.1"
    //  if successfull, return true, else false
    $res = mysql_query("CREATE TABLE `".$AIGAION2_DB_PREFIX."aigaiongeneral` (`version` varchar(10) NOT NULL, PRIMARY KEY  (`version`)) TYPE=MyISAM;");
    if (!$res) {
        dbError(mysql_error());
        return false;
    }
    //The versioning field will not yet be initialized, create a row with empty version field
    $res = mysql_query("INSERT INTO `".$AIGAION2_DB_PREFIX."aigaiongeneral` (`version`) VALUES ('')");
    if (!$res) {
        dbError(mysql_error());
        return false;
    }

    if (!setVersion("V0.1",$bSilent, $AIGAION2_DB_PREFIX))  return false;
    return true;
}

/*
CODE    : V0
Summary : init-version.
Changes in database structure:
A database which is in version V0 contains exactly the database structure as it
was just before introducing the database version management mechanism.

Since the checkDatabase won't even be called before login was succesfull
(and therefore the basic table set exists), this check always returns true.
*/
function checkDatabaseV0() {
    //Since the checkDatabase won't even be called before login was succesfull
    //(and therefore the basic table set exists), this check always returns true.

    return true;


    //echo ("Checking version V0<br>"); //for debug
    //check if there are any tables
    //  if true, return
    //  otherwise, create tables (see schema.sql...)
    //    if successful, return true
    //    otherwise,return false
}

?>
